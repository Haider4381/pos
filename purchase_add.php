<?php
include('sessionCheck.php');
include('connection.php');
include('functions.php');
require_once ("inc/init.php");
require_once ("inc/config.ui.php");
$page_title = "Purchase";
include ("inc/header.php");
include ("inc/nav.php");
$u_id=$_SESSION['u_id'];
$branch_id=$_SESSION['branch_id'];
$p_NumberPrefix='PUR';
$currency_symbol = 'RS';
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" />
<style>
body { background: #f7fbff; }
.purchase-card { background: #fff; border-radius: 10px; box-shadow: 0 2px 16px #0001; margin: 40px auto 0; padding: 32px 24px; max-width: 1250px; }
.purchase-section-title { font-weight: 600; color: #1b3556; font-size: 1.6rem !important; border-bottom: 2px solid #e4e9f2; margin-bottom: 18px; padding-bottom: 8px; }
.form-label { font-weight: 500; color: #36587d; margin-bottom: 3px; font-size: 1.13rem; }
.form-control, .table, .table th, .table td {
    font-size: 18px !important;
}
.table th, .table td { vertical-align: middle !important; }
.table thead { background: #e9f3fa; }
.table th { color: #1b3556; font-weight: 600; }
.table-scroll { max-height: 210px; overflow-y: auto; border-radius: 8px; border: 1px solid #dde2e6; margin-bottom: 16px; background: #fff; }
.summary-box { background: #f1f6fb; border-radius: 8px; padding: 16px 25px; margin-top: 10px; box-shadow: 0 1px 4px #0091ff0d; }
.summary-label { font-weight: 600; color: #36587d; font-size: 1.2rem !important;}
.summary-value { font-weight: 700; color: #007bff; font-size: 1.7rem !important;}
@media (max-width: 800px) {
  .purchase-card { padding: 18px 8px; max-width: 100vw; }
  .summary-box { font-size: 0.99rem; padding: 10px 10px; }
}

/* Make table body font smaller */
#u_tbl tbody tr:not(#u_row) td,
#u_tbl tbody tr:not(#u_row) .btn {
    font-size: 14px !important;
}
#u_tbl thead th {
    font-size: 18px !important;
}

/* Wider input fields for Qty, Rate, Disc Amt, Total */
#ex_qty, #ex_rate, #ex_discount_amount, #ex_netamount,
#u_tbl .table-qty, #u_tbl .table-rate, #u_tbl .table-disc {
    min-width: 110px !important;
    width: 140px !important;
    max-width: 170px;
    display: inline-block;
}

/* Table-row inputs look neat & compact */
#u_tbl tbody input.form-control-sm {
    padding: 2px 6px;
    font-size: 15px;
    height: 32px;
}
/* Increase width of Qty, Unit Price, Disc. %, Total fields */
#ex_qty, #ex_rate, #ex_discount_percentage, #ex_netamount {
    min-width: 110px !important;
    width: 140px !important;
    max-width: 170px;
    display: inline-block;
}
@media (max-width: 700px) {
    #ex_qty, #ex_rate, #ex_discount_percentage, #ex_netamount {
        min-width: 80px !important;
        width: 100px !important;
        max-width: 130px;
    }
}
/* Make products table row font smaller, but keep header large */
#u_tbl tbody tr:not(#u_row) td,
#u_tbl tbody tr:not(#u_row) .btn {
    font-size: 14px !important;
}
#u_tbl thead th {
    font-size: 18px !important;
}

/* Make totals box font larger and bold */
.summary-box .summary-value,
.summary-box .summary-label {
    font-size: 1.6rem !important;
    font-weight: 700 !important;
}
.summary-box .summary-label {
    font-size: 1.2rem !important;
    font-weight: 600 !important;
}
/* Make purchase table header and body font smaller */
#u_tbl thead th,
#u_tbl tbody td {
    font-size: 13px !important;
}

/* Fix action button style in table & summary buttons */
#u_tbl .btn-danger,
.btn-purchase {
    font-size: 13px !important;
    padding: 4px 16px !important;
    border-radius: 4px !important;
    color: #fff !important;
    background-color: #dc3545 !important;
    border: none !important;
    box-shadow: none !important;
    line-height: 1.2 !important;
}

#u_tbl .btn-danger:hover,
.btn-purchase:hover {
    background-color: #b91d2b !important;
    color: #fff !important;
}

.btn-purchase {
    background: linear-gradient(90deg,#005fa3 0%,#0099ff 100%) !important;
    color: #fff !important;
    font-weight: 600 !important;
    margin: 0 8px 0 0 !important;
}

.btn-purchase:hover {
    background: linear-gradient(90deg,#007bff 0%,#00c6ff 100%) !important;
    color: #fff !important;
}
</style>
<?php
if(isset($_POST['save']))
{
    $error=0;
    $location_after_save='window.location.href=""';
    $save_value=$_POST['save_value'];
    $sup_id=$_POST['sup_id'];
    $p_Date=$_POST['p_Date'];
    $p_BillNo=$_POST['p_BillNo'];
    $p_Remarks=$_POST['p_Remarks'];
    $p_VendorRemarks=$_POST['p_VendorRemarks'];
    $p_TotalAmount=$_POST['p_TotalAmount'];
    $p_TaxAmount = isset($_POST['p_TaxAmount']) ? $_POST['p_TaxAmount']+0 : 0;
    $p_Tax=$_POST['p_Tax']+0;
    $p_DiscountAmount = isset($_POST['p_DiscountAmount']) ? $_POST['p_DiscountAmount']+0 : 0;
    $p_Discount=$_POST['p_Discount']+0;
    $p_NetAmount=$_POST['p_NetAmount'];
    $item_IMEI=isset($_POST['item_IMEI']) ? $_POST['item_IMEI'] : '0';
    $item_Qty=$_POST['item_Qty'];
    $item_Rate=$_POST['item_Rate'];
    $item_TotalAmount = isset($_POST['item_TotalAmount']) ? $_POST['item_TotalAmount'] : array();
    $item_DiscountPercentageArray = isset($_POST['item_DiscountPercentage']) ? $_POST['item_DiscountPercentage'] : array();
    $item_DiscountAmount=$_POST['item_DiscountAmount'];
    $item_NetAmount=$_POST['item_NetAmount'];
    $item_InvoiceAmountArray=$_POST['item_InvoiceAmount'];
    $item_idArray=array_filter($_POST['item_id']);
    $pp_Amount=$_POST['pp_Amount'];
    if($save_value=='save_and_close') {
        $location_after_save='window.location.href="dashboard"';
    }

    $pNumberQ=mysqli_fetch_assoc(mysqli_query($con, "select (ifnull(MAX(p_NumberSr),0)+1) as p_Number from adm_purchase where branch_id=$branch_id"));
    $p_NumberSr=$pNumberQ['p_Number'];
    $p_Number=$p_NumberPrefix.$pNumberQ['p_Number'];

    // --- Updated: Validate Purchase Account before anything ---
    $inventory_acc_row = mysqli_fetch_assoc(mysqli_query($con, "
        SELECT account_id 
        FROM accounts_chart 
        WHERE branch_id='$branch_id'
          AND (
            account_title = 'purchases' OR
            account_title = 'PURCHASES' OR
            account_title = 'Purchases' OR
            account_title = 'Purchase' OR
            account_title = 'purchase' OR
            account_title = 'Purchase Account' OR
            account_title = 'PURCHASE ACCOUNT' OR
            account_title = 'PURCHASE' OR
            account_title = 'purchase account' OR
            account_title = 'purchase a/c'
          )
        LIMIT 1
    "));
    $inventory_account_id = $inventory_acc_row ? $inventory_acc_row['account_id'] : 0;
    if ($inventory_account_id == 0) {
        $_SESSION['msg'] = '<div class="alert alert-danger">Purchase Account not found in Chart of Accounts. Please create a Purchase Account first!</div>';
        ?>
        <script type="text/javascript">
            alert("Purchase Account not found in Chart of Accounts. Please create a Purchase Account first!");
            window.location.href = "purchase_add.php";
        </script>
        <?php
        exit;
    }

    if(!empty($item_idArray))
    {
        $purchaseQ="INSERT INTO adm_purchase(
            p_Date,p_Number,p_NumberSr, p_BillNo, sup_id, p_TotalAmount, p_NetAmount, p_Remarks, p_VendorRemarks, p_CreatedOn,u_id, branch_id
        ) VALUES (
            '$p_Date', '$p_Number','$p_NumberSr','$p_BillNo','$sup_id','$p_TotalAmount','$p_NetAmount','$p_Remarks','$p_VendorRemarks',now(),'$u_id', '$branch_id'
        )";
        if(mysqli_query($con,$purchaseQ))
        {
            $p_id=mysqli_insert_id($con);
            foreach ($item_idArray as $key => $itemRow)
            {
                $item_id=$itemRow;
                $imei=$item_IMEI[$key];
                $item_qty=$item_Qty[$key];
                $item_rate=$item_Rate[$key];
                $totalamount = isset($item_TotalAmount[$key]) ? $item_TotalAmount[$key] : 0;
                $item_DiscountPercentage = isset($item_DiscountPercentageArray[$key]) ? $item_DiscountPercentageArray[$key] : 0;
                $item_DiscountAmount=$item_DiscountAmount[$key];
                $netamount=$item_NetAmount[$key];
                $invoiceamount=$item_InvoiceAmountArray[$key];
                $pdQ="INSERT INTO adm_purchase_detail (
                        p_id, pd_Date, sup_id, item_id, item_IMEI, item_TotalAmount, item_NetAmount, pd_CreatedOn,
                        item_InvoiceAmount, item_Qty, item_Rate, u_id, branch_id, item_DiscountPercentage, item_DiscountAmount
                    ) VALUES (
                        '$p_id','$p_Date','$sup_id','$item_id', '$imei', '$totalamount', '$netamount', now(), '$invoiceamount',
                        '$item_qty', '$item_rate','$u_id', '$branch_id', '$item_DiscountPercentage','$item_DiscountAmount'
                    )";
                if(!mysqli_query($con,$pdQ)) $error++;
            }

            // ---------- 1. ACCOUNTS VOUCHER ENTRY: Purchase Voucher with Product Details Description ----------
            $supplier_acc_row = mysqli_fetch_assoc(mysqli_query($con, "SELECT account_id, account_title FROM accounts_chart WHERE account_id = '$sup_id' LIMIT 1"));
            $supplier_account_id = $supplier_acc_row['account_id'];
            $supplier_name = $supplier_acc_row['account_title'];

            // --- Get product detail string for description ---
            $products_descriptions = [];
            $productQ = mysqli_query($con, "SELECT PD.item_Qty, PD.item_Rate, I.item_Name FROM adm_purchase_detail PD
                LEFT JOIN adm_item I ON I.item_id = PD.item_id
                WHERE PD.p_id = '$p_id'");
            while($pdRow = mysqli_fetch_assoc($productQ)) {
                $products_descriptions[] = $pdRow['item_Name'] . " - " . $pdRow['item_Qty'] . " @ " . number_format($pdRow['item_Rate']);
            }
            $products_str = implode(', ', $products_descriptions);

            // Voucher Header Insert with Product Details
            $voucher_type = 'Purchase';
            $voucher_no = $p_Number;
            $voucher_desc = $products_str;
            $q_voucher = "INSERT INTO accounts_voucher (entry_date, voucher_type, voucher_no, description, created_by)
                          VALUES ('$p_Date', '$voucher_type', '$voucher_no', '".mysqli_real_escape_string($con,$voucher_desc)."', '$u_id')";
            mysqli_query($con, $q_voucher);
            $purchase_voucher_id = mysqli_insert_id($con);

            // Double Entry
            if($supplier_account_id && $inventory_account_id && $purchase_voucher_id) {
                // Debit Inventory/Purchases
                mysqli_query($con, "INSERT INTO accounts_voucher_detail (voucher_id, account_id, description, debit, credit)
                                    VALUES ($purchase_voucher_id, $inventory_account_id, '$voucher_desc', $p_NetAmount, 0)");
                // Credit Supplier
                mysqli_query($con, "INSERT INTO accounts_voucher_detail (voucher_id, account_id, description, debit, credit)
                                    VALUES ($purchase_voucher_id, $supplier_account_id, '$voucher_desc', 0, $p_NetAmount)");
            }
            // ----------------------------------------------------------

            // ---------- 2. PAYMENT VOUCHER ENTRY (after purchase voucher) ----------
            if(!empty($pp_Amount)) {
                $pp_Description = "Paid against Bill# $p_Number";
                $ppQ = "INSERT INTO adm_purchase_payment(
                    pp_Date, sup_id, pp_Amount, p_id, pp_Description, pp_Type, pp_CreatedOn, u_id, branch_id
                ) VALUES (
                    '$p_Date', $sup_id, '$pp_Amount', $p_id, '$pp_Description', 'P', now(), '$u_id', '$branch_id'
                )";
                if(mysqli_query($con, $ppQ)) {
                    $supplier_acc_row = mysqli_fetch_assoc(mysqli_query($con, "SELECT account_id FROM accounts_chart WHERE account_id = '$sup_id' LIMIT 1"));
                    $supplier_account_id = isset($supplier_acc_row['account_id']) ? $supplier_acc_row['account_id'] : 0;

                    $cash_acc_row = mysqli_fetch_assoc(mysqli_query($con, "
    SELECT account_id FROM accounts_chart WHERE branch_id='$branch_id' AND (
        account_title = 'Cash' OR
        account_title = 'CASH' OR
        account_title = 'Cash Account' OR
        account_title = 'CASH ACCOUNT' OR
        account_title = 'cash account' OR
        account_title = 'Cash in Hand' OR
        account_title = 'CASH IN HAND' OR
        account_title = 'cash in hand' OR
        account_title = 'Cash In Hand' OR
        account_title = 'Cash A/C' OR
        account_title = 'CASH A/C' OR
        account_title = 'cash a/c' OR
        account_title = 'Petty Cash' OR
        account_title = 'PETTY CASH' OR
        account_title = 'petty cash' OR
        account_title = 'Cash on Hand' OR
        account_title = 'CASH ON HAND' OR
        account_title = 'Cash Balance' OR
        account_title = 'CASH BALANCE' OR
        account_title = 'Cash in Office' OR
        account_title = 'CASH IN OFFICE' OR
        account_title = 'Cash at Hand' OR
        account_title = 'CASH AT HAND' OR
        account_title = 'Cash at Counter' OR
        account_title = 'CASH AT COUNTER' OR
        account_title = 'Cash Drawer' OR
        account_title = 'CASH DRAWER' OR
        account_title = 'Cash Box' OR
        account_title = 'CASH BOX' OR
        account_title = 'Cash Fund' OR
        account_title = 'CASH FUND' OR
        account_title = 'Cash Float' OR
        account_title = 'CASH FLOAT' OR
        account_title LIKE '%Cash%' AND account_title NOT LIKE '%Bank%' AND account_title NOT LIKE '%BANK%' OR
        account_title LIKE '%CASH%' AND account_title NOT LIKE '%Bank%' AND account_title NOT LIKE '%BANK%'
    ) LIMIT 1
"));
                    $cash_account_id = isset($cash_acc_row['account_id']) ? $cash_acc_row['account_id'] : 0;

                    $errorMsg = '';
                    if(!$supplier_account_id) {
                        $errorMsg = 'Supplier account not found in Chart of Accounts. Please create supplier account first!';
                    }
                    if(!$cash_account_id) {
                        $errorMsg = 'Cash Account not found in Chart of Accounts. Please create a "Cash" account first!';
                    }
                    if(!empty($errorMsg)){
                        $_SESSION['msg'] = '<div class="alert alert-danger">' . $errorMsg . '</div>';
                        ?>
                        <script type="text/javascript">
                            alert("<?php echo $errorMsg; ?>");
                            window.location.href="purchase_add.php";
                        </script>
                        <?php
                        exit;
                    } else {
                        $voucher_type = 'Payment';
                        $voucher_no = $p_Number . '-PAY';
                        $voucher_desc = "Payment against Purchase Bill# $p_Number";
                        $q_voucher = "INSERT INTO accounts_voucher (entry_date, voucher_type, voucher_no, description, created_by)
                                      VALUES ('$p_Date', '$voucher_type', '$voucher_no', '".mysqli_real_escape_string($con, $voucher_desc)."', '$u_id')";
                        mysqli_query($con, $q_voucher);
                        $payment_voucher_id = mysqli_insert_id($con);

                        // Double Entry
                        mysqli_query($con, "INSERT INTO accounts_voucher_detail (voucher_id, account_id, description, debit, credit)
                                            VALUES ($payment_voucher_id, $supplier_account_id, '$voucher_desc', $pp_Amount, 0)");
                        mysqli_query($con, "INSERT INTO accounts_voucher_detail (voucher_id, account_id, description, debit, credit)
                                            VALUES ($payment_voucher_id, $cash_account_id, '$voucher_desc', 0, $pp_Amount)");
                    }
                } else {
                    $_SESSION['ppMsg'] = '<div class="alert alert-danger">Problem Saving Purchase Payment. </div>';
                }
            }
            // ----------------------------------------------------------
        }
        else
        {
            $error++;
        }
    }
    else
    {
        $error++;
    }
    if(empty($error))
    {
        $_SESSION['msg']="<div class='alert alert-success'>Purchase Saved Successfully</div>";
    }
    else
    {
        $_SESSION['msg']="<div class='alert alert-danger'>Problem Saving Purchase</div>";
    }
    
    if(empty($error))
    {
    ?>
        <script type="text/javascript">
        x = confirm("Purchase Saved Successfully.");
        if(x)
        {
            window.open('purchase_print.php?p_id=<?php echo $p_id;?>','popUpWindow','height=588,width=402,left=320,top=30,resizable=yes,scrollbars=yes,toolbar=no,menubar=no,location=no,directories=no, status=no');
            <?=$location_after_save;?>
        }
        else
        {
            <?=$location_after_save;?>
        }
        </script>
    <?php
    }
    else
    {
        $_SESSION['msg']='<div class="alert alert-danger">Problem Saving Sale.</div>';
    }
}
?>
<div id="main" role="main">
<div id="content">
  <div class="purchase-card">
    <div class="purchase-section-title mb-4"><i class="fa fa-tags"></i> Purchase Entry</div>
    <?php if(!empty($_SESSION['msg'])){ echo $_SESSION['msg'];unset($_SESSION['msg']);} ?>
    <form id="checkout-form" method="post" action="" onsubmit="return checkParameters();">
      <input type="hidden" name="p_Date" value="<?php echo date('Y-m-d');?>">
      <div class="row">
        <div class="col-md-4 mb-2">
          <label class="form-label">Vendor</label>
          <select class="form-control" name="sup_id" id="sup_id" required>
			<option value="">Select Vendor</option>
			<?php $supArray=get_Supplier();
			foreach ($supArray as $supRow) { ?>
				<option value="<?php echo $supRow['account_id'];?>"><?php echo $supRow['account_title'];?></option>
			<?php } ?>
			</select>
        </div>
        <div class="col-md-2 mb-2">
          <label class="form-label">Bill No.</label>
          <input type="text" name="p_BillNo" class="form-control" required placeholder="Reference No.">
        </div>
        <div class="col-md-2 mb-2">
          <label class="form-label">Purchase No.</label>
          <input type="text" class="form-control" value="<?php
            $pNumberQ=mysqli_fetch_assoc(mysqli_query($con, "select (ifnull(MAX(p_NumberSr),0)+1) as p_Number from adm_purchase where branch_id=$branch_id"));
            echo $p_Number=$p_NumberPrefix.$pNumberQ['p_Number'];
          ?>" readonly>
        </div>
      </div>
      <div class="row align-items-end">
        <div class="col-md-2 mb-2">
          <label class="form-label">Product Code</label>
          <input type="text" id="ex_itemcode" class="form-control" placeholder="Product Code">
        </div>
        <div class="col-md-4 mb-2">
          <label class="form-label">Search Product</label>
          <input list="item_list" id="ex_item" class="form-control" placeholder="Type product name" autocomplete="off">
          <datalist id="item_list">
            <?php
            $itemsArray = get_ActiveItems();
            foreach ($itemsArray as $itemRow) {
                echo '<option value="'.htmlspecialchars($itemRow['item_Name']).'"></option>';
            }
            $setsQ = mysqli_query($con,"SELECT set_id, set_name FROM adm_itemset");
            while($setRow = mysqli_fetch_assoc($setsQ)) {
                echo '<option value="[SET] '.htmlspecialchars($setRow['set_name']).'"></option>';
            }
            ?>
          </datalist>
        </div>
        <div class="col-md-1 mb-2">
          <label class="form-label">Qty</label>
          <input type="number" id="ex_qty" class="form-control" min="1" placeholder="Qty">
        </div>
        <div class="col-md-1 mb-2">
          <label class="form-label">Unit Price</label>
          <input type="number" id="ex_rate" class="form-control" min="0" placeholder="Rate">
        </div>
        <div class="col-md-1 mb-2">
          <label class="form-label">Disc. Amt</label>
          <input type="number" id="ex_discount_amount" class="form-control" min="0" placeholder="Disc Amt">
        </div>
        <div class="col-md-1 mb-2">
          <label class="form-label">Total</label>
          <input type="number" id="ex_netamount" class="form-control" placeholder="Total" readonly style="min-width: 191px !important;">
        </div>
        <div class="col-md-1 mb-2">
          <button type="button" class="btn btn-purchase" onclick="addToTable();" id="cart_add_btn" style="margin-top: 2px; margin-left: 100px !important; padding: 11px !important;"><i class="fa fa-shopping-cart"></i> Add</button>
        </div>
        <input type="hidden" id="ex_imei">
      </div>
      <div class="table-scroll">
        <table class="table table-bordered mb-0" id="u_tbl">
          <thead>
            <tr>
              <th style="width:15%;">Product Code</th>
              <th style="width:25%;">Product Name</th>
              <th style="width:12%;">Quantity</th>
              <th style="width:12%;">Cost Price</th>
              <th style="width:10%;">Disc Amt</th>
              <th style="width:8%;">Net Amount</th>
              <th style="width:10%;">Action</th>
            </tr>
          </thead>
          <tbody>
            <!-- Template row (hidden, for cloning) -->
            <tr id="u_row" style="display: none;">
              <td class="show_itemcode"></td>
              <td class="show_item"></td>
              <td>
                <input type="number" class="table-qty form-control form-control-sm" min="1" value="1" style="width:140px">
              </td>
              <td>
                <input type="number" class="table-rate form-control form-control-sm" min="0" value="0" style="width:140px">
              </td>
              <td>
                <input type="number" class="table-disc form-control form-control-sm" min="0" value="0" style="width:140px">
              </td>
              <td class="show_netamount">0.00</td>
              <td>
                <button type="button" class="btn btn-danger btn-sm" onclick="delRow(this)">Delete</button>
              </td>
              <input type="hidden" name="item_id[]">
              <input type="hidden" name="item_IMEI[]">
              <input type="hidden" name="item_TotalAmount[]">
              <input type="hidden" name="item_InvoiceAmount[]">
              <input type="hidden" name="item_Qty[]">
              <input type="hidden" name="item_Rate[]">
              <input type="hidden" name="item_DiscountAmount[]">
              <input type="hidden" name="item_NetAmount[]">
            </tr>
          </tbody>
        </table>
      </div>
      <div class="row">
        <div class="col-md-8">
          <div class="row">
            <div class="col-md-4 mb-2">
              <label class="form-label">Discount (Amt)</label>
              <input type="number" name="p_Discount" id="p_Discount" class="form-control" min="0" value="0" oninput="calculate()">
            </div>
            <div class="col-md-4 mb-2">
              <label class="form-label">VAT (%)</label>
              <input type="number" name="p_Tax" id="p_Tax" class="form-control" min="0" onkeyup="calculate()" onchange="calculate();" value="0">
            </div>
            <div class="col-md-4 mb-2">
              <label class="form-label">Paid To Vendor</label>
              <input type="number" name="pp_Amount" class="form-control" style="font-size: 1.2rem; font-weight: 500;" min="0" placeholder="Enter Amount">
            </div>
          </div>
          <div class="row">
            <div class="col-md-6 mb-2">
              <label class="form-label">Purchase Notes</label>
              <textarea class="form-control" name="p_Remarks" id="p_Remarks" style="height:42px;" placeholder="Purchase Note"></textarea>
            </div>
            <div class="col-md-6 mb-2">
              <label class="form-label">Vendor Notes</label>
              <textarea class="form-control" name="p_VendorRemarks" id="p_VendorRemarks" style="height:42px;" placeholder="Vendor Note"></textarea>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="summary-box">
            <div class="d-flex justify-content-between align-items-center mb-1"><span class="summary-label">Gross Amount</span><span class="summary-value" id="p_TotalAmountShow">RS0.00</span></div>
            <div class="d-flex justify-content-between align-items-center mb-1"><span class="summary-label">Discount</span><span class="summary-value" id="p_DiscountAmountShow">RS0.00</span></div>
            <div class="d-flex justify-content-between align-items-center mb-1"><span class="summary-label">VAT</span><span class="summary-value" id="p_TaxAmountShow">RS0.00</span></div>
            <div class="d-flex justify-content-between align-items-center mb-2"><span class="summary-label">Net Payable</span><span class="summary-value" id="p_NetAmountShow">RS0.00</span></div>
            <div class="d-flex justify-content-between align-items-center"><b class="summary-label">Total Items</b><span class="summary-value" id="totalItems">0</span></div>
          </div>
          <input type="hidden" name="p_NetAmount" id="p_NetAmount" value="0">
          <input type="hidden" name="p_TotalAmount" id="p_TotalAmount" value="0">
          <input type="hidden" name="p_TotalItems" id="p_TotalItems" value="0">
          <input type="hidden" name="save" value="1">
          <input type="hidden" name="save_value" id="save_value" value="save">
          <div class="mt-3 text-right">
            <button type="button" class="btn btn-purchase" onclick="saveForm('save');" style="height: 50px;">Save</button>
            <button type="button" class="btn btn-purchase" onclick="saveForm('save_and_close');" style="height: 50px;">Save & Close</button>
          </div>
        </div>
      </div>
    </form>
  </div>
</div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
var productMap = <?php
    $itemsArray = get_ActiveItems();
    $jsMap = [];
    foreach ($itemsArray as $itemRow) {
        $jsMap[strtolower($itemRow['item_Name'])] = $itemRow['item_id'];
    }
    $setsQ = mysqli_query($con,"SELECT set_id, set_name FROM adm_itemset");
    while($setRow = mysqli_fetch_assoc($setsQ)) {
        $jsMap['[set] '.strtolower($setRow['set_name'])] = 'set_'.$setRow['set_id'];
    }
    echo json_encode($jsMap);
?>;

// --- Keyboard navigation for entry form ---
$("#ex_item").on('keydown', function(e){
    if(e.key === "Enter"){ e.preventDefault(); $("#ex_qty").focus(); }
});
$("#ex_qty").on('keydown', function(e){
    if(e.key === "Enter"){ e.preventDefault(); $("#ex_rate").focus(); }
});
$("#ex_rate").on('keydown', function(e){
    if(e.key === "Enter"){ e.preventDefault(); $("#ex_discount_amount").focus(); }
});
$("#ex_discount_amount").on('keydown', function(e){
    if(e.key === "Enter"){ e.preventDefault(); $("#cart_add_btn").focus(); }
});
$("#cart_add_btn").on('keydown', function(e){
    if(e.key === "Enter"){ e.preventDefault(); addToTable(); }
});

// --- Product Detail on Select ---
function getItemDetail(){
    var sup_id=$("#sup_id").val();
    var item_name=$("#ex_item").val();
    var item_id = productMap[item_name.toLowerCase()];
    if(!item_id) return false;
    if(item_id.toString().startsWith('set_')) return false;
    var allVars="item_id="+item_id+"&sup_id="+sup_id;
    $.ajax({
        type: "post",
        url: "purchase_add_json.php",
        dataType: 'json',
        data:allVars,
        cache: false,
        success: function(data){
            $("#ex_itemcode").val(data.item_Code);
            $("#ex_qty").val('1');
            $("#ex_rate").val(data.item_PurchasePrice);
            $("#ex_netamount").val(data.item_PurchasePrice);
            $("#ex_discount_amount").val("0");
            $("#ex_imei").focus();
        },
        error:function(){
            alert("Please Choose An Item First");
        }
    });
}
$("#ex_item").on('blur', getItemDetail);

// --- Row Calculation (amount based discount) ---
function calculate_netamount_row(){
    var ex_qty=parseInt($("#ex_qty").val()) || 0;
    var ex_rate=parseFloat($("#ex_rate").val()) || 0;
    var ex_discount_amount=parseFloat($("#ex_discount_amount").val()) || 0;
    var ex_netamount=(ex_qty*ex_rate)-ex_discount_amount;
    if(ex_netamount < 0) ex_netamount = 0;
    $("#ex_netamount").val(ex_netamount.toFixed(2));
}

$("#ex_qty,#ex_rate,#ex_discount_amount").on('input', calculate_netamount_row);

// --- Update row values on input change (amount based discount) ---
function updateRowTotals($row) {
    var qty = parseFloat($row.find('.table-qty').val()) || 0;
    var rate = parseFloat($row.find('.table-rate').val()) || 0;
    var discAmt = parseFloat($row.find('.table-disc').val()) || 0;

    var gross = qty * rate;
    var net = gross - discAmt;

    if(net < 0) {
        net = 0; // Prevent negative net
        $row.find('.table-disc').val(gross.toFixed(2));
        discAmt = gross;
    }

    $row.find('.show_netamount').text(net.toFixed(2));

    // Update hidden fields for form submit
    $row.find('input[name="item_Qty[]"]').val(qty);
    $row.find('input[name="item_Rate[]"]').val(rate.toFixed(2));
    $row.find('input[name="item_DiscountAmount[]"]').val(discAmt.toFixed(2));
    $row.find('input[name="item_NetAmount[]"]').val(net.toFixed(2));
    $row.find('input[name="item_TotalAmount[]"]').val(net.toFixed(2));
    $row.find('input[name="item_InvoiceAmount[]"]').val(net.toFixed(2));
}

// --- Add Product/Set to Table ---
function addToTable() {
    var ex_item_name = $("#ex_item").val().trim();
    var item_id = productMap[ex_item_name.toLowerCase()];
    if (!item_id) { alert('Invalid product selected!'); $("#ex_item").focus(); return false; }

    // --- SET logic ---
    if(item_id.toString().startsWith('set_')) {
        var set_id = item_id.replace('set_', '');
        var set_qty = $("#ex_qty").val() || 1;
        $.post('get_set_items.php', { set_id: set_id }, function(data){
            var items = [];
            try { items = JSON.parse(data); } catch(e) { alert('Invalid set data!'); return; }
            if(!Array.isArray(items) || items.length == 0) {
                alert("Yeh set khali hai ya item nahi mila!");
                return;
            }
            items.forEach(function(row){
                if(!row.item_id || !row.item_Name) return;
                var newRow = $("#u_row").clone().show();
                newRow.removeAttr('id');
                newRow.find(".show_itemcode").text(row.item_Code || "");
                newRow.find(".show_item").text(row.item_Name || "");
                var qty = parseInt(row.quantity) * parseInt(set_qty);
                var rate = parseFloat(row.item_Price) || 0;
                newRow.find(".table-qty").val(qty);
                newRow.find(".table-rate").val(rate.toFixed(2));
                newRow.find(".table-disc").val("0");

                updateRowTotals(newRow);
                newRow.find('.table-qty, .table-rate, .table-disc').on('input', function() {
                    updateRowTotals(newRow);
                    calculate();
                    totalItems();
                });

                newRow.find('input[name="item_id[]"]').val(row.item_id);
                newRow.find('input[name="item_IMEI[]"]').val("");
                $("#u_tbl tbody").prepend(newRow);
            });
            // Reset controls after set add
            $("#ex_itemcode").val("");
            $("#ex_item").val("");
            $("#ex_qty").val("");
            $("#ex_rate").val("");
            $("#ex_discount_amount").val("");
            $("#ex_netamount").val("");
            $("#ex_item").focus();
            calculate();
            totalItems();
        });
        return; // Don't add anything else for sets
    }

    // --- Single Product Logic ---
    var item_text = ex_item_name;
    var ex_itemcode = $("#ex_itemcode").val();
    var ex_qty = $("#ex_qty").val();
    var ex_rate = $("#ex_rate").val();
    var ex_discount_amount = $("#ex_discount_amount").val();
    var ex_netamount = $("#ex_netamount").val();
    var ex_imei = $("#ex_imei").val();

    ex_discount_amount = ex_discount_amount ? parseFloat(ex_discount_amount) : 0;

    if(ex_qty == 0 || ex_qty == '') { alert('Please give Item Qty'); $("#ex_qty").focus(); return false; }
    if(ex_rate == 0 || ex_rate == '') { alert('Please give Item Rate'); $("#ex_rate").focus(); return false; }
    if(ex_netamount == '' || ex_netamount == 0) { alert('Invalid Selection Please Select An Item First'); $("#ex_netamount").focus(); return false; }

    if(checkDuplicate()) {
        var newRow = $("#u_row").clone().show();
        newRow.removeAttr('id');
        newRow.find(".show_itemcode").text(ex_itemcode);
        newRow.find(".show_item").text(item_text);
        newRow.find(".table-qty").val(ex_qty);
        newRow.find(".table-rate").val(parseFloat(ex_rate).toFixed(2));
        newRow.find(".table-disc").val(ex_discount_amount);

        updateRowTotals(newRow);
        newRow.find('.table-qty, .table-rate, .table-disc').on('input', function() {
            updateRowTotals(newRow);
            calculate();
            totalItems();
        });

        newRow.find('input[name="item_id[]"]').val(item_id);
        newRow.find('input[name="item_IMEI[]"]').val(ex_imei);
        $("#u_tbl tbody").prepend(newRow);

        // Reset controls
        $("#ex_itemcode").val("");
        $("#ex_item").val("");
        $("#ex_qty").val("");
        $("#ex_rate").val("");
        $("#ex_discount_amount").val("");
        $("#ex_netamount").val("");
        $("#ex_item").focus();
        $("#ex_imei").val("");
        calculate();
        totalItems();
    }
}

function delRow(e){
    $(e).closest('tr').remove();
    calculate();
    totalItems();
}

function totalItems(){
    var total = $("#u_tbl tbody tr").not("#u_row").filter(function(){
        return $(this).css('display') !== 'none';
    }).length;
    $("#totalItems").html(total);
    $("#p_TotalItems").val(total);
}

function calculate(){
    var sum=0;
    $("#u_tbl tbody tr").not("#u_row").each(function(){
        var net = parseFloat($(this).find('.show_netamount').text()) || 0;
        sum += net;
    });
    sum=sum.toFixed(2);
    $("#p_TotalAmount").val(sum);
    $("#p_TotalAmountShow").html("RS"+sum);

    var net_sum = parseFloat(sum);

    // --- Discount (Amount) ---
    var p_Discount = parseFloat($("#p_Discount").val()) || 0;
    var discountamount = p_Discount;
    if(discountamount > net_sum) discountamount = net_sum;
    var after_discount = net_sum - discountamount;

    // --- VAT as percent of (after discount) ---
    var p_Tax = parseFloat($("#p_Tax").val()) || 0;
    var taxamount = 0;
    if(p_Tax > 0) {
        taxamount = p_Tax/100 * after_discount;
    }
    var net_payable = after_discount + taxamount;

    // --- Set values everywhere ---
    discountamount = parseFloat(discountamount).toFixed(2);
    taxamount = parseFloat(taxamount).toFixed(2);
    net_payable = parseFloat(net_payable).toFixed(2);

    $("#p_NetAmount").val(net_payable);
    $("#p_NetAmountShow").html("RS"+net_payable);
    $("#p_DiscountAmount").val(discountamount);
    $("#p_DiscountAmountShow").html("RS"+discountamount);
    $("#p_TaxAmount").val(taxamount);
    $("#p_TaxAmountShow").html("RS"+taxamount);
}
function checkDuplicate(){
    var error=0;
    var ex_imei=$("#ex_imei").val();
    if(ex_imei!=='')
    {
        $("#u_tbl tbody tr input[name='item_IMEI[]']").each(function(index,elem){
            if(ex_imei==elem.value)
            {
                alert("Duplicate Entry For this IMEI..");
                error++;
                $("#ex_imei").val('');
            }
        });
    }
    if(error){
        return false;
    }
    else{
        return true;
    }
}

function saveForm(val){
    var save_value=val;
    $('#save_value').val(save_value);
    $("#checkout-form").submit();
}

function checkParameters(){
    var p_Remarks = $.trim($("#p_Remarks").val());
    var sup_id = $.trim($("#sup_id").val());
    var p_TotalItems = $.trim($("#p_TotalItems").val());
    if (sup_id == 0 || sup_id == '')
    {
        alert("Please Give Supplier Information.");
        $("#sup_id").focus();
        return false;
    }
    if (p_TotalItems == 0)
    {
        alert("Please choose at least one item.");
        $("#ex_item").focus();
        return false;
    }
    return true;
}
</script>