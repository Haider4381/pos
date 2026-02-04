<?php
/*
 * purchase_edit.php
 * ---------------------------------------------------------------
 * PURPOSE:
 *   Edit an existing Purchase:
 *     - Update header (vendor, bill no, remarks)
 *     - Add / remove / modify item detail rows
 *     - Rebuild Purchase Voucher (double-entry)
 *     - Optionally add OR replace payment(s)
 *
 * KEY POINTS:
 *   - Original p_Number, p_NumberSr, p_Date remain unchanged.
 *   - p_TotalAmount (sum of row net amounts BEFORE overall discount/tax)
 *   - p_NetAmount   (after discount + tax) is saved in adm_purchase.
 *   - Discount & VAT percentages are NOT persisted separately (same behavior as purchase_add.php).
 *
 * PAYMENT HANDLING MODES:
 *   $REPLACE_PAYMENTS = true  => delete all old payments + all payment vouchers for this purchase, insert one new (if provided).
 *   $REPLACE_PAYMENTS = false => keep old payments & their vouchers; if a new amount provided it appends a new payment & voucher
 *                                using incremental voucher_no like p_Number-PAY, p_Number-PAY2, p_Number-PAY3, ...
 *
 * SAFETY:
 *   - Transaction used to keep data in sync.
 *   - Basic validation only (existing style).
 *   - Not converted to prepared statements (to stay consistent with current project style),
 *     but recommended for production hardening.
 */

include('sessionCheck.php');
include('connection.php');
include('functions.php');
require_once("inc/init.php");
require_once("inc/config.ui.php");
$page_title = "Edit Purchase";
include("inc/header.php");
include("inc/nav.php");

$u_id      = $_SESSION['u_id'];
$branch_id = $_SESSION['branch_id'];
$currency_symbol = 'RS';

$REPLACE_PAYMENTS = false; // Toggle as described above

if(!isset($_GET['p_id']) || !is_numeric($_GET['p_id'])){
    echo "<div class='alert alert-danger'>Invalid Purchase ID.</div>";
    include("inc/footer.php");
    exit;
}
$p_id = (int)$_GET['p_id'];

/* ---------------- Fetch Purchase Header ---------------- */
$purchaseQ = mysqli_query($con, "SELECT * FROM adm_purchase WHERE p_id=$p_id AND branch_id=$branch_id LIMIT 1");
if(!$purchaseQ || mysqli_num_rows($purchaseQ)==0){
    echo "<div class='alert alert-danger'>Purchase not found OR access denied.</div>";
    include("inc/footer.php");
    exit;
}
$P = mysqli_fetch_assoc($purchaseQ); // Contains p_Number, p_NumberSr, p_Date, etc.

/* ---------------- Fetch Purchase Detail Rows ---------------- */
$details = [];
$detailQ = mysqli_query($con,"
    SELECT PD.*, I.item_Name, I.item_Code
    FROM adm_purchase_detail PD
    LEFT JOIN adm_item I ON I.item_id = PD.item_id
    WHERE PD.p_id = $p_id
");
while($d = mysqli_fetch_assoc($detailQ)){
    $details[] = $d;
}

/* ---------------- Sum of Existing Payments ---------------- */
$paySumRow = mysqli_fetch_assoc(mysqli_query($con,"
    SELECT IFNULL(SUM(pp_Amount),0) AS paid_total
    FROM adm_purchase_payment
    WHERE p_id = $p_id
"));
$already_paid_total = $paySumRow ? $paySumRow['paid_total'] : 0;

/* ---------------- Helper: Generate next payment voucher number ---------------- */
function get_next_payment_voucher_no($con, $purchaseNumber){
    // Find all voucher_no starting with purchaseNumber-PAY
    $safe = mysqli_real_escape_string($con, $purchaseNumber);
    $res = mysqli_query($con, "SELECT voucher_no FROM accounts_voucher WHERE voucher_no LIKE '{$safe}-PAY%'");
    $max = 0;
    $existsPlain = false;
    while($row = mysqli_fetch_assoc($res)){
        $vn = $row['voucher_no'];
        if($vn === "{$purchaseNumber}-PAY"){
            $existsPlain = true;
            $max = max($max, 1);
        } elseif(preg_match('/-PAY(\d+)$/', $vn, $m)){
            $num = (int)$m[1];
            if($num > $max) $max = $num;
        }
    }
    if(!$existsPlain && $max == 0){
        return "{$purchaseNumber}-PAY"; // first payment
    }
    return "{$purchaseNumber}-PAY".($max+1);
}

/* ---------------- POST: Update Purchase ---------------- */
if(isset($_POST['update'])){
    $error = 0;

    // Gather posted header fields
    $sup_id          = isset($_POST['sup_id']) ? (int)$_POST['sup_id'] : 0;
    $p_BillNo        = mysqli_real_escape_string($con, $_POST['p_BillNo']);
    $p_Remarks       = mysqli_real_escape_string($con, $_POST['p_Remarks']);
    $p_VendorRemarks = mysqli_real_escape_string($con, $_POST['p_VendorRemarks']);

    $p_TotalAmount   = isset($_POST['p_TotalAmount']) ? floatval($_POST['p_TotalAmount']) : 0;
    $p_NetAmount     = isset($_POST['p_NetAmount']) ? floatval($_POST['p_NetAmount']) : 0;

    // Display-only fields (not persisted separately)
    $p_Discount      = isset($_POST['p_Discount']) ? floatval($_POST['p_Discount']) : 0;
    $p_Tax           = isset($_POST['p_Tax']) ? floatval($_POST['p_Tax']) : 0;

    // Additional payment (append or replace based on $REPLACE_PAYMENTS)
    $pp_Amount       = isset($_POST['pp_Amount']) ? floatval($_POST['pp_Amount']) : 0;

    // Item arrays
    $item_idArray          = isset($_POST['item_id']) ? $_POST['item_id'] : [];
    $item_IMEI             = isset($_POST['item_IMEI']) ? $_POST['item_IMEI'] : [];
    $item_Qty              = isset($_POST['item_Qty']) ? $_POST['item_Qty'] : [];
    $item_Rate             = isset($_POST['item_Rate']) ? $_POST['item_Rate'] : [];
    $item_DiscountAmount   = isset($_POST['item_DiscountAmount']) ? $_POST['item_DiscountAmount'] : [];
    $item_NetAmountArray   = isset($_POST['item_NetAmount']) ? $_POST['item_NetAmount'] : [];
    $item_TotalAmountArray = isset($_POST['item_TotalAmount']) ? $_POST['item_TotalAmount'] : [];
    $item_InvoiceAmount    = isset($_POST['item_InvoiceAmount']) ? $_POST['item_InvoiceAmount'] : [];

    // Basic validation
    $valid_items = 0;
    foreach($item_idArray as $val){
        if(!empty($val)) $valid_items++;
    }
    if($valid_items == 0){
        $error++;
        $_SESSION['msg'] = "<div class='alert alert-danger'>No items submitted.</div>";
    }

    // Validate Purchase Account existence (like add)
    $inventory_acc_row = mysqli_fetch_assoc(mysqli_query($con, "
        SELECT account_id 
        FROM accounts_chart 
        WHERE branch_id='$branch_id'
          AND (
            account_title IN (
              'purchases','PURCHASES','Purchases','Purchase','purchase',
              'Purchase Account','PURCHASE ACCOUNT','PURCHASE','purchase account','purchase a/c'
            )
          )
        LIMIT 1
    "));
    $inventory_account_id = $inventory_acc_row ? $inventory_acc_row['account_id'] : 0;
    if($inventory_account_id == 0){
        $error++;
        $_SESSION['msg'] = "<div class='alert alert-danger'>Purchase Account not found in Chart of Accounts.</div>";
    }

    mysqli_begin_transaction($con);

    /* ------------ Update Purchase Header ------------ */
    if(!$error){
        $updQ = "
            UPDATE adm_purchase SET
                sup_id        = '$sup_id',
                p_BillNo      = '$p_BillNo',
                p_TotalAmount = '$p_TotalAmount',
                p_NetAmount   = '$p_NetAmount',
                p_Remarks     = '$p_Remarks',
                p_VendorRemarks = '$p_VendorRemarks'
            WHERE p_id = $p_id AND branch_id = $branch_id
            LIMIT 1
        ";
        if(!mysqli_query($con, $updQ)){
            $error++;
        }
    }

    /* ------------ Delete Old Details ------------ */
    if(!$error){
        if(!mysqli_query($con, "DELETE FROM adm_purchase_detail WHERE p_id=$p_id")){
            $error++;
        }
    }

    /* ------------ Insert New Detail Rows ------------ */
    if(!$error){
        foreach($item_idArray as $idx=>$item_id){
            $item_id = (int)$item_id;
            if(!$item_id) continue;

            $qty     = isset($item_Qty[$idx]) ? floatval($item_Qty[$idx]) : 0;
            $rate    = isset($item_Rate[$idx]) ? floatval($item_Rate[$idx]) : 0;
            $discAmt = isset($item_DiscountAmount[$idx]) ? floatval($item_DiscountAmount[$idx]) : 0;
            $netRow  = isset($item_NetAmountArray[$idx]) ? floatval($item_NetAmountArray[$idx]) : 0;
            $totRow  = isset($item_TotalAmountArray[$idx]) ? floatval($item_TotalAmountArray[$idx]) : $netRow;
            $invAmt  = isset($item_InvoiceAmount[$idx]) ? floatval($item_InvoiceAmount[$idx]) : $netRow;
            $imei    = isset($item_IMEI[$idx]) ? mysqli_real_escape_string($con, $item_IMEI[$idx]) : '';

            $pdQ = "INSERT INTO adm_purchase_detail(
                        p_id, pd_Date, sup_id, item_id, item_IMEI, item_TotalAmount, item_NetAmount,
                        pd_CreatedOn, item_InvoiceAmount, item_Qty, item_Rate, u_id, branch_id,
                        item_DiscountPercentage, item_DiscountAmount
                    ) VALUES (
                        '$p_id', '".$P['p_Date']."', '$sup_id', '$item_id', '$imei', '$totRow', '$netRow',
                        NOW(), '$invAmt', '$qty', '$rate', '$u_id', '$branch_id', '0', '$discAmt'
                    )";
            if(!mysqli_query($con, $pdQ)){
                $error++;
                break;
            }
        }
    }

    /* ------------ Build Product Description for Voucher ------------ */
    $product_desc = '';
    if(!$error){
        $pdDescQ = mysqli_query($con,"
            SELECT PD.item_Qty, PD.item_Rate, I.item_Name
            FROM adm_purchase_detail PD
            LEFT JOIN adm_item I ON I.item_id = PD.item_id
            WHERE PD.p_id = $p_id
        ");
        $descParts = [];
        while($row = mysqli_fetch_assoc($pdDescQ)){
            $descParts[] = $row['item_Name'].' - '.$row['item_Qty'].' @ '.number_format($row['item_Rate'],2);
        }
        $product_desc = mysqli_real_escape_string($con, implode(', ', $descParts));
    }

    /* ------------ Rebuild Purchase Voucher ------------ */
    if(!$error){
        // Always delete only the main Purchase voucher (NOT payment vouchers) to preserve payment history unless replacing.
        // Find purchase voucher(s) EXACTLY matching p_Number with type 'Purchase'
        $pNum = mysqli_real_escape_string($con, $P['p_Number']);
        $delPurchaseVoucherIds = [];
        $pvRes = mysqli_query($con,"
            SELECT voucher_id FROM accounts_voucher
            WHERE voucher_no = '$pNum' AND voucher_type='Purchase'
        ");
        while($pv = mysqli_fetch_assoc($pvRes)){
            $delPurchaseVoucherIds[] = $pv['voucher_id'];
        }
        if(!empty($delPurchaseVoucherIds)){
            $ids = implode(',', $delPurchaseVoucherIds);
            if(!mysqli_query($con,"DELETE FROM accounts_voucher_detail WHERE voucher_id IN ($ids)")) $error++;
            if(!mysqli_query($con,"DELETE FROM accounts_voucher WHERE voucher_id IN ($ids)")) $error++;
        }

        // If replacing payments, also delete payment vouchers & payment rows now
        if(!$error && $REPLACE_PAYMENTS){
            if(!mysqli_query($con,"DELETE FROM adm_purchase_payment WHERE p_id=$p_id")) $error++;
            if(!$error){
                $payVoucherIds = [];
                $pvr = mysqli_query($con,"
                    SELECT voucher_id FROM accounts_voucher
                    WHERE voucher_no LIKE '".$pNum."-PAY%' AND voucher_type='Payment'
                ");
                while($pvv = mysqli_fetch_assoc($pvr)){
                    $payVoucherIds[] = $pvv['voucher_id'];
                }
                if(!empty($payVoucherIds)){
                    $ids2 = implode(',', $payVoucherIds);
                    if(!mysqli_query($con,"DELETE FROM accounts_voucher_detail WHERE voucher_id IN ($ids2)")) $error++;
                    if(!mysqli_query($con,"DELETE FROM accounts_voucher WHERE voucher_id IN ($ids2)")) $error++;
                }
            }
        }

        // Re-insert Purchase voucher
        if(!$error){
            $supplier_acc = mysqli_fetch_assoc(mysqli_query($con,"SELECT account_id FROM accounts_chart WHERE account_id='$sup_id' LIMIT 1"));
            $supplier_account_id = $supplier_acc ? $supplier_acc['account_id'] : 0;

            $q_v = "INSERT INTO accounts_voucher(entry_date, voucher_type, voucher_no, description, created_by)
                    VALUES ('".$P['p_Date']."','Purchase','".$P['p_Number']."','$product_desc','$u_id')";
            if(mysqli_query($con, $q_v)){
                $purchase_voucher_id = mysqli_insert_id($con);
                if($purchase_voucher_id && $inventory_account_id && $supplier_account_id){
                    mysqli_query($con,"INSERT INTO accounts_voucher_detail (voucher_id, account_id, description, debit, credit)
                                       VALUES ($purchase_voucher_id, $inventory_account_id, '$product_desc', $p_NetAmount, 0)");
                    mysqli_query($con,"INSERT INTO accounts_voucher_detail (voucher_id, account_id, description, debit, credit)
                                       VALUES ($purchase_voucher_id, $supplier_account_id, '$product_desc', 0, $p_NetAmount)");
                }
            } else {
                $error++;
            }
        }
    }

    /* ------------ Additional Payment (optional) ------------ */
    if(!$error && $pp_Amount > 0){
        // Insert payment record
        $pp_desc = mysqli_real_escape_string($con, "Paid against Bill# ".$P['p_Number']." (edit)");
        $ppQ = "INSERT INTO adm_purchase_payment(
                    pp_Date, sup_id, pp_Amount, p_id, pp_Description, pp_Type, pp_CreatedOn, u_id, branch_id
                ) VALUES (
                    '".$P['p_Date']."', $sup_id, '$pp_Amount', $p_id, '$pp_desc', 'P', NOW(), '$u_id', '$branch_id'
                )";
        if(!mysqli_query($con,$ppQ)){
            $error++;
        } else {
            // Voucher for this new payment
            $supplier_acc = mysqli_fetch_assoc(mysqli_query($con,"SELECT account_id FROM accounts_chart WHERE account_id='$sup_id' LIMIT 1"));
            $supplier_account_id = $supplier_acc ? $supplier_acc['account_id'] : 0;

            // Get cash account
            $cash_acc = mysqli_fetch_assoc(mysqli_query($con,"
                SELECT account_id FROM accounts_chart
                WHERE branch_id='$branch_id' AND (
                    account_title='Cash' OR account_title='CASH' OR account_title='Cash Account' OR account_title='CASH ACCOUNT'
                    OR account_title='Cash in Hand' OR account_title='CASH IN HAND'
                    OR (account_title LIKE '%Cash%' AND account_title NOT LIKE '%Bank%')
                )
                LIMIT 1
            "));
            $cash_account_id = $cash_acc ? $cash_acc['account_id'] : 0;

            if($supplier_account_id && $cash_account_id){
                $payVoucherNo = $REPLACE_PAYMENTS
                                ? $P['p_Number']."-PAY"
                                : get_next_payment_voucher_no($con, $P['p_Number']);
                $voucher_desc = mysqli_real_escape_string($con,"Payment against Purchase Bill# ".$P['p_Number']." (edit)");
                $q_vp = "INSERT INTO accounts_voucher(entry_date, voucher_type, voucher_no, description, created_by)
                         VALUES ('".$P['p_Date']."','Payment','$payVoucherNo','$voucher_desc','$u_id')";
                if(mysqli_query($con,$q_vp)){
                    $pay_voucher_id = mysqli_insert_id($con);
                    mysqli_query($con,"INSERT INTO accounts_voucher_detail (voucher_id, account_id, description, debit, credit)
                                       VALUES ($pay_voucher_id, $supplier_account_id, '$voucher_desc', $pp_Amount, 0)");
                    mysqli_query($con,"INSERT INTO accounts_voucher_detail (voucher_id, account_id, description, debit, credit)
                                       VALUES ($pay_voucher_id, $cash_account_id, '$voucher_desc', 0, $pp_Amount)");
                } else {
                    $error++;
                }
            } else {
                $error++;
            }
        }
    }

    /* ------------ Commit / Rollback ------------ */
    if($error){
        mysqli_rollback($con);
        $_SESSION['msg'] = "<div class='alert alert-danger'>Problem updating Purchase.</div>";
    } else {
        mysqli_commit($con);
        $_SESSION['msg'] = "<div class='alert alert-success'>Purchase Updated Successfully.</div>";
        ?>
        <script>
            alert("Purchase Updated Successfully.");
            window.location.href="purchase_list.php";
        </script>
        <?php
        exit;
    }
}

/* (If we reach here after error, page is re-rendered with same $P and $details; if success we already exited) */
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" />
<style>
body { background:#f7fbff; }
.purchase-card { background:#fff; border-radius:10px; box-shadow:0 2px 16px #0001; margin:40px auto 0; padding:32px 24px; max-width:1250px; }
.purchase-section-title { font-weight:600; color:#1b3556; font-size:1.6rem!important; border-bottom:2px solid #e4e9f2; margin-bottom:18px; padding-bottom:8px; }
.form-label { font-weight:500; color:#36587d; margin-bottom:3px; font-size:1.05rem; }
.form-control, .table, .table th, .table td { font-size:15px!important; }
.table th, .table td { vertical-align:middle!important; }
.table thead { background:#e9f3fa; }
.table th { color:#1b3556; font-weight:600; }
.table-scroll { max-height:240px; overflow-y:auto; border-radius:8px; border:1px solid #dde2e6; margin-bottom:16px; background:#fff; }
.summary-box { background:#f1f6fb; border-radius:8px; padding:16px 22px; margin-top:10px; box-shadow:0 1px 4px #0091ff0d; }
.summary-label { font-weight:600; color:#36587d; font-size:1.05rem!important; }
.summary-value { font-weight:700; color:#007bff; font-size:1.3rem!important; }
#u_tbl tbody tr:not(#u_row) td, #u_tbl tbody tr:not(#u_row) .btn { font-size:13px!important; }
#u_tbl thead th { font-size:14px!important; }
#ex_qty,#ex_rate,#ex_discount_amount,#ex_netamount,
#u_tbl .table-qty,#u_tbl .table-rate,#u_tbl .table-disc {
  min-width:90px!important; width:110px!important; max-width:150px; display:inline-block;
}
#u_tbl tbody input.form-control-sm { padding:2px 6px; font-size:13px; height:30px; }
#u_tbl .btn-danger, .btn-purchase {
  font-size:12px!important; padding:4px 12px!important; border-radius:4px!important;
  color:#fff!important; background-color:#dc3545!important; border:none!important; line-height:1.1!important;
}
#u_tbl .btn-danger:hover, .btn-purchase:hover { background-color:#b91d2b!important; }
.btn-purchase {
  background:linear-gradient(90deg,#005fa3 0%,#0099ff 100%)!important; color:#fff!important;
  font-weight:600!important; margin:0 8px 0 0!important;
}
.btn-purchase:hover { background:linear-gradient(90deg,#007bff 0%,#00c6ff 100%)!important; }
small.note { display:block; margin-top:2px; font-size:11px; color:#666; }
</style>

<div id="main" role="main">
  <div id="content">
    <div class="purchase-card">
      <div class="purchase-section-title mb-4">
        <i class="fa fa-edit"></i> Edit Purchase (<?php echo htmlspecialchars($P['p_Number']); ?>)
      </div>
      <?php if(!empty($_SESSION['msg'])) { echo $_SESSION['msg']; unset($_SESSION['msg']); } ?>
      <form method="post" id="edit-form" onsubmit="return checkParameters();">
        <input type="hidden" name="update" value="1">
        <div class="row">
          <div class="col-md-3 mb-2">
            <label class="form-label">Vendor</label>
            <select class="form-control" name="sup_id" id="sup_id" required>
              <option value="">Select Vendor</option>
              <?php $supArray = get_Supplier();
              foreach($supArray as $supRow){ ?>
                <option value="<?php echo $supRow['account_id'];?>" <?php echo ($supRow['account_id']==$P['sup_id']?'selected':''); ?>>
                  <?php echo $supRow['account_title'];?>
                </option>
              <?php } ?>
            </select>
          </div>
          <div class="col-md-2 mb-2">
            <label class="form-label">Bill No.</label>
            <input type="text" name="p_BillNo" class="form-control" value="<?php echo htmlspecialchars($P['p_BillNo']);?>" required>
          </div>
          <div class="col-md-2 mb-2">
            <label class="form-label">Purchase No.</label>
            <input type="text" class="form-control" value="<?php echo htmlspecialchars($P['p_Number']);?>" readonly>
          </div>
            <div class="col-md-2 mb-2">
              <label class="form-label">Date</label>
              <input type="text" class="form-control" value="<?php echo htmlspecialchars($P['p_Date']);?>" readonly>
            </div>
          <div class="col-md-3 mb-2">
            <label class="form-label">Previously Paid</label>
            <input type="text" class="form-control" value="<?php echo $currency_symbol.number_format($already_paid_total,2);?>" readonly>
            <small class="note">
              Additional payment below will <?php echo $REPLACE_PAYMENTS ? 'REPLACE old payments' : 'be ADDED'; ?>.
            </small>
          </div>
        </div>

        <!-- Add Item Row -->
        <div class="row align-items-end">
          <div class="col-md-2 mb-2">
            <label class="form-label">Product Code</label>
            <input type="text" id="ex_itemcode" class="form-control" placeholder="Code">
          </div>
          <div class="col-md-4 mb-2">
            <label class="form-label">Search Product</label>
            <input list="item_list" id="ex_item" class="form-control" placeholder="Type product name" autocomplete="off">
            <datalist id="item_list">
              <?php
              $itemsArray = get_ActiveItems();
              foreach($itemsArray as $itemRow){
                  echo '<option value="'.htmlspecialchars($itemRow['item_Name']).'"></option>';
              }
              $setsQ = mysqli_query($con,"SELECT set_id,set_name FROM adm_itemset");
              while($setRow = mysqli_fetch_assoc($setsQ)){
                  echo '<option value="[SET] '.htmlspecialchars($setRow['set_name']).'"></option>';
              }
              ?>
            </datalist>
          </div>
          <div class="col-md-1 mb-2">
            <label class="form-label">Qty</label>
            <input type="number" id="ex_qty" class="form-control" min="1">
          </div>
          <div class="col-md-1 mb-2">
            <label class="form-label">Unit Price</label>
            <input type="number" id="ex_rate" class="form-control" min="0">
          </div>
          <div class="col-md-1 mb-2">
            <label class="form-label">Disc Amt</label>
            <input type="number" id="ex_discount_amount" class="form-control" min="0" value="0">
          </div>
          <div class="col-md-1 mb-2">
            <label class="form-label">Total</label>
            <input type="number" id="ex_netamount" class="form-control" readonly>
          </div>
          <div class="col-md-2 mb-2">
            <button type="button" class="btn btn-purchase" onclick="addToTable();" id="cart_add_btn" style="margin-top:2px;">
              <i class="fa fa-plus"></i> Add
            </button>
          </div>
          <input type="hidden" id="ex_imei">
        </div>

        <div class="table-scroll">
          <table class="table table-bordered mb-0" id="u_tbl">
            <thead>
              <tr>
                <th style="width:15%;">Product Code</th>
                <th style="width:25%;">Product Name</th>
                <th style="width:10%;">Quantity</th>
                <th style="width:12%;">Cost Price</th>
                <th style="width:10%;">Disc Amt</th>
                <th style="width:10%;">Net Amount</th>
                <th style="width:8%;">Action</th>
              </tr>
            </thead>
            <tbody>
              <!-- TEMPLATE ROW (hidden) -->
              <tr id="u_row" style="display:none;">
                <td class="show_itemcode"></td>
                <td class="show_item"></td>
                <td><input type="number" class="table-qty form-control form-control-sm" min="1" value="1"></td>
                <td><input type="number" class="table-rate form-control form-control-sm" min="0" value="0"></td>
                <td><input type="number" class="table-disc form-control form-control-sm" min="0" value="0"></td>
                <td class="show_netamount">0.00</td>
                <td><button type="button" class="btn btn-danger btn-sm" onclick="delRow(this)">Delete</button></td>
                <input type="hidden" name="item_id[]">
                <input type="hidden" name="item_IMEI[]">
                <input type="hidden" name="item_TotalAmount[]">
                <input type="hidden" name="item_InvoiceAmount[]">
                <input type="hidden" name="item_Qty[]">
                <input type="hidden" name="item_Rate[]">
                <input type="hidden" name="item_DiscountAmount[]">
                <input type="hidden" name="item_NetAmount[]">
              </tr>

              <?php foreach($details as $d):
                $netRow = number_format($d['item_NetAmount'],2,'.','');
                $rate   = number_format($d['item_Rate'],2,'.','');
                $disc   = number_format($d['item_DiscountAmount'],2,'.','');
              ?>
              <tr>
                <td class="show_itemcode"><?php echo htmlspecialchars($d['item_Code']);?></td>
                <td class="show_item"><?php echo htmlspecialchars($d['item_Name']);?></td>
                <td><input type="number" class="table-qty form-control form-control-sm" min="1" value="<?php echo $d['item_Qty'];?>"></td>
                <td><input type="number" class="table-rate form-control form-control-sm" min="0" value="<?php echo $rate;?>"></td>
                <td><input type="number" class="table-disc form-control form-control-sm" min="0" value="<?php echo $disc;?>"></td>
                <td class="show_netamount"><?php echo $netRow;?></td>
                <td><button type="button" class="btn btn-danger btn-sm" onclick="delRow(this)">Delete</button></td>
                <input type="hidden" name="item_id[]" value="<?php echo $d['item_id'];?>">
                <input type="hidden" name="item_IMEI[]" value="<?php echo htmlspecialchars($d['item_IMEI']);?>">
                <input type="hidden" name="item_TotalAmount[]" value="<?php echo $netRow;?>">
                <input type="hidden" name="item_InvoiceAmount[]" value="<?php echo $netRow;?>">
                <input type="hidden" name="item_Qty[]" value="<?php echo $d['item_Qty'];?>">
                <input type="hidden" name="item_Rate[]" value="<?php echo $rate;?>">
                <input type="hidden" name="item_DiscountAmount[]" value="<?php echo $disc;?>">
                <input type="hidden" name="item_NetAmount[]" value="<?php echo $netRow;?>">
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <div class="row">
          <div class="col-md-8">
            <div class="row">
              <div class="col-md-4 mb-2">
                <label class="form-label">Discount (Amt)</label>
                <input type="number" name="p_Discount" id="p_Discount" class="form-control" min="0" value="0" oninput="calculate();">
              </div>
              <div class="col-md-4 mb-2">
                <label class="form-label">VAT (%)</label>
                <input type="number" name="p_Tax" id="p_Tax" class="form-control" min="0" value="0" oninput="calculate();">
              </div>
              <div class="col-md-4 mb-2">
                <label class="form-label"><?php echo $REPLACE_PAYMENTS ? 'New Total Paid' : 'Additional Payment'; ?></label>
                <input type="number" name="pp_Amount" class="form-control" min="0" value="0" placeholder="0.00">
                <small class="note">
                  <?php echo $REPLACE_PAYMENTS
                        ? 'Will REPLACE previous payments & vouchers.'
                        : 'Will be added as NEW payment & voucher.';?>
                </small>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6 mb-2">
                <label class="form-label">Purchase Notes</label>
                <textarea class="form-control" name="p_Remarks" id="p_Remarks" style="height:48px;"><?php echo htmlspecialchars($P['p_Remarks']);?></textarea>
              </div>
              <div class="col-md-6 mb-2">
                <label class="form-label">Vendor Notes</label>
                <textarea class="form-control" name="p_VendorRemarks" id="p_VendorRemarks" style="height:48px;"><?php echo htmlspecialchars($P['p_VendorRemarks']);?></textarea>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="summary-box">
              <div class="d-flex justify-content-between mb-1">
                <span class="summary-label">Gross Amount</span>
                <span class="summary-value" id="p_TotalAmountShow"><?php echo $currency_symbol.number_format($P['p_TotalAmount'],2);?></span>
              </div>
              <div class="d-flex justify-content-between mb-1">
                <span class="summary-label">Discount</span>
                <span class="summary-value" id="p_DiscountAmountShow"><?php echo $currency_symbol;?>0.00</span>
              </div>
              <div class="d-flex justify-content-between mb-1">
                <span class="summary-label">VAT</span>
                <span class="summary-value" id="p_TaxAmountShow"><?php echo $currency_symbol;?>0.00</span>
              </div>
              <div class="d-flex justify-content-between mb-2">
                <span class="summary-label">Net Payable</span>
                <span class="summary-value" id="p_NetAmountShow"><?php echo $currency_symbol.number_format($P['p_NetAmount'],2);?></span>
              </div>
              <div class="d-flex justify-content-between">
                <b class="summary-label">Total Items</b>
                <span class="summary-value" id="totalItems">0</span>
              </div>
            </div>
            <input type="hidden" name="p_TotalAmount" id="p_TotalAmount" value="<?php echo number_format($P['p_TotalAmount'],2,'.','');?>">
            <input type="hidden" name="p_NetAmount" id="p_NetAmount" value="<?php echo number_format($P['p_NetAmount'],2,'.','');?>">
            <input type="hidden" name="p_TotalItems" id="p_TotalItems" value="0">
            <div class="mt-3 text-right">
              <button type="submit" class="btn btn-purchase" style="height:48px;">Update Purchase</button>
              <a href="purchase_list.php" class="btn btn-secondary" style="height:48px;">Cancel</a>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
// ---------- PRODUCT MAP (name -> id / set) ----------
var productMap = <?php
    $pm = [];
    $itemsArray = get_ActiveItems();
    foreach($itemsArray as $iRow){
        $pm[strtolower($iRow['item_Name'])] = $iRow['item_id'];
    }
    $setsQ = mysqli_query($con,"SELECT set_id,set_name FROM adm_itemset");
    while($s = mysqli_fetch_assoc($setsQ)){
        $pm['[set] '.strtolower($s['set_name'])] = 'set_'.$s['set_id'];
    }
    echo json_encode($pm);
?>;

// ---------- FETCH ITEM DETAIL ----------
function getItemDetail(){
    var item_name = $("#ex_item").val().trim();
    if(!item_name) return;
    var item_id = productMap[item_name.toLowerCase()];
    if(typeof item_id === 'undefined') return;
    if(item_id.toString().startsWith('set_')) return; // sets handled separately
    var sup_id = $("#sup_id").val() || 0;

    $.ajax({
        type:'POST',
        url:'purchase_add_json.php',
        dataType:'json',
        data:{ item_id:item_id, sup_id:sup_id },
        success:function(resp){
            // Support both new structured {status,data:{...}} OR legacy flat shape
            var data = resp.data ? resp.data : resp;
            if(resp.status && resp.status !== 'ok' && !resp.item_Code){
                alert(resp.message || 'Item fetch error');
                return;
            }
            if(!data.item_Code){
                alert('Invalid item data');
                return;
            }
            $("#ex_itemcode").val(data.item_Code);
            $("#ex_qty").val('1');
            $("#ex_rate").val(data.item_PurchasePrice);
            $("#ex_discount_amount").val('0');
            $("#ex_netamount").val(data.item_PurchasePrice);
        },
        error:function(xhr){
            console.error('Item detail error', xhr.responseText);
            alert('Error loading item detail');
        }
    });
}
$("#ex_item").on('blur', getItemDetail);

// ---------- ROW ENTRY CALC  ----------
function calculate_entry_net(){
    var q = parseFloat($("#ex_qty").val())||0;
    var r = parseFloat($("#ex_rate").val())||0;
    var d = parseFloat($("#ex_discount_amount").val())||0;
    var n = (q*r)-d;
    if(n<0){ n=0; }
    $("#ex_netamount").val(n.toFixed(2));
}
$("#ex_qty,#ex_rate,#ex_discount_amount").on('input', calculate_entry_net);

// ---------- UPDATE A TABLE ROW ----------
function updateRowTotals($row){
    var qty  = parseFloat($row.find('.table-qty').val())||0;
    var rate = parseFloat($row.find('.table-rate').val())||0;
    var disc = parseFloat($row.find('.table-disc').val())||0;
    var gross = qty*rate;
    var net = gross - disc;
    if(net < 0){
        net = 0;
        disc = gross;
        $row.find('.table-disc').val(gross.toFixed(2));
    }
    $row.find('.show_netamount').text(net.toFixed(2));

    $row.find("input[name='item_Qty[]']").val(qty);
    $row.find("input[name='item_Rate[]']").val(rate.toFixed(2));
    $row.find("input[name='item_DiscountAmount[]']").val(disc.toFixed(2));
    $row.find("input[name='item_NetAmount[]']").val(net.toFixed(2));
    $row.find("input[name='item_TotalAmount[]']").val(net.toFixed(2));
    $row.find("input[name='item_InvoiceAmount[]']").val(net.toFixed(2));
}

// ---------- RESET ENTRY FIELDS ----------
function resetEntry(){
    $("#ex_itemcode,#ex_item,#ex_qty,#ex_rate,#ex_discount_amount,#ex_netamount,#ex_imei").val('');
    $("#ex_item").focus();
}

// ---------- ADD ROW (single / set) ----------
function addToTable(){
    var name = $("#ex_item").val().trim();
    if(!name){ alert("Select product first"); return; }
    var item_id = productMap[name.toLowerCase()];
    if(typeof item_id === 'undefined'){ alert("Invalid product"); return; }

    // --- SET handling ---
    if(item_id.toString().startsWith('set_')){
        var set_id = item_id.replace('set_','');
        var set_qty = parseFloat($("#ex_qty").val())||1;
        $.post('get_set_items.php',{ set_id:set_id }, function(resp){
            var arr = [];
            try{ arr = JSON.parse(resp); } catch(e){ alert("Set parse error"); return; }
            if(!Array.isArray(arr) || arr.length===0){ alert("Empty set"); return; }
            arr.forEach(function(row){
                if(!row.item_id) return;
                var $new = $("#u_row").clone(true,true).show().removeAttr('id');
                $new.find('.show_itemcode').text(row.item_Code || '');
                $new.find('.show_item').text(row.item_Name || '');
                var qty = (parseFloat(row.quantity)||1)*set_qty;
                var rate= parseFloat(row.item_Price)||0;
                $new.find('.table-qty').val(qty);
                $new.find('.table-rate').val(rate.toFixed(2));
                $new.find('.table-disc').val('0');
                $new.find("input[name='item_id[]']").val(row.item_id);
                $new.find("input[name='item_IMEI[]']").val('');
                updateRowTotals($new);
                $new.find('.table-qty,.table-rate,.table-disc').on('input', function(){
                    updateRowTotals($new); calculate(); totalItems();
                });
                $("#u_tbl tbody").prepend($new);
            });
            resetEntry();
            calculate();
            totalItems();
        }).fail(function(e){
            alert("Set load error");
            console.error(e.responseText);
        });
        return;
    }

    // --- SINGLE product ---
    var code  = $("#ex_itemcode").val();
    var qty   = parseFloat($("#ex_qty").val());
    var rate  = parseFloat($("#ex_rate").val());
    var disc  = parseFloat($("#ex_discount_amount").val())||0;
    var net   = parseFloat($("#ex_netamount").val());
    var imei  = $("#ex_imei").val();

    if(!code){ alert("Item detail not loaded yet (blur from product field)."); return; }
    if(!qty || qty<=0){ alert("Enter valid Qty"); return; }
    if(!rate || rate<=0){ alert("Enter valid Rate"); return; }
    if(!net || net<=0){ alert("Invalid net amount"); return; }

    var $new = $("#u_row").clone(true,true).show().removeAttr('id');
    $new.find('.show_itemcode').text(code);
    $new.find('.show_item').text(name);
    $new.find('.table-qty').val(qty);
    $new.find('.table-rate').val(rate.toFixed(2));
    $new.find('.table-disc').val(disc.toFixed(2));
    $new.find("input[name='item_id[]']").val(item_id);
    $new.find("input[name='item_IMEI[]']").val(imei);
    updateRowTotals($new);
    $new.find('.table-qty,.table-rate,.table-disc').on('input', function(){
        updateRowTotals($new); calculate(); totalItems();
    });
    $("#u_tbl tbody").prepend($new);
    resetEntry();
    calculate();
    totalItems();
}

// Make accessible to inline onclick if any future changes
window.addToTable = addToTable;

// ---------- DELETE ROW ----------
function delRow(btn){
    var tr = btn.closest('tr');
    if(tr){
        tr.remove();
        calculate();
        totalItems();
    }
}
window.delRow = delRow;

// ---------- TOTAL ITEMS ----------
function totalItems(){
    var count = $("#u_tbl tbody tr").not("#u_row").length;
    $("#totalItems").text(count);
    $("#p_TotalItems").val(count);
}

// ---------- CALCULATE GRAND TOTALS ----------
function calculate(){
    var sum=0;
    $("#u_tbl tbody tr").not("#u_row").each(function(){
        var n = parseFloat($(this).find('.show_netamount').text())||0;
        sum += n;
    });
    sum = sum.toFixed(2);
    $("#p_TotalAmount").val(sum);
    $("#p_TotalAmountShow").text('<?php echo $currency_symbol;?>'+sum);

    var discount = parseFloat($("#p_Discount").val())||0;
    if(discount > parseFloat(sum)) discount = parseFloat(sum);
    var afterDiscount = parseFloat(sum) - discount;

    var taxPerc = parseFloat($("#p_Tax").val())||0;
    var taxAmt  = taxPerc > 0 ? afterDiscount * taxPerc / 100 : 0;
    var netPay  = afterDiscount + taxAmt;

    $("#p_DiscountAmountShow").text('<?php echo $currency_symbol;?>'+discount.toFixed(2));
    $("#p_TaxAmountShow").text('<?php echo $currency_symbol;?>'+taxAmt.toFixed(2));
    $("#p_NetAmount").val(netPay.toFixed(2));
    $("#p_NetAmountShow").text('<?php echo $currency_symbol;?>'+netPay.toFixed(2));
}

// ---------- FORM VALIDATION ----------
function checkParameters(){
    if($("#sup_id").val()==="" || $("#sup_id").val()=="0"){
        alert("Please select supplier.");
        $("#sup_id").focus();
        return false;
    }
    if($("#p_TotalItems").val()=="0"){
        alert("Please add at least one item.");
        return false;
    }
    return true;
}

// ---------- BIND EXISTING ROW EVENTS ----------
$("#u_tbl tbody tr").not("#u_row").each(function(){
    var $r = $(this);
    $r.find('.table-qty,.table-rate,.table-disc').on('input', function(){
        updateRowTotals($r); calculate(); totalItems();
    });
});

// ---------- INITIAL CALC ----------
$(function(){
    totalItems();
    calculate();
});
</script>

<?php include("inc/footer.php"); ?>