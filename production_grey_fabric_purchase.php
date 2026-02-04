<?php
include('sessionCheck.php');
include 'connection.php';
include('functions.php');
$branch_id = $_SESSION['branch_id'];
$u_id = $_SESSION['u_id'];

// Sr No. logic
$srNoRes = mysqli_query($con, "SELECT IFNULL(MAX(id),0) as lastSrNo FROM production_grey_fabric_purchase");
$srNoRow = mysqli_fetch_assoc($srNoRes);
$currentSrNo = intval($srNoRow['lastSrNo']) + 1;

// Edit/Delete logic
$edit_id = isset($_GET['edit_id']) ? intval($_GET['edit_id']) : 0;
$delete_id = isset($_GET['delete_id']) ? intval($_GET['delete_id']) : 0;

// Handle delete
if($delete_id) {
    // Delete purchase record
    mysqli_query($con, "DELETE FROM production_grey_fabric_purchase WHERE id = $delete_id");
    // Delete related voucher from accounts (based on unique narration)
    $voucher_res = mysqli_query($con, "SELECT voucher_id FROM accounts_voucher WHERE description LIKE '%Grey Fabric Purchase [ID: $delete_id]%'");
    while($voucher_row = mysqli_fetch_assoc($voucher_res)) {
        $voucher_id = $voucher_row['voucher_id'];
        mysqli_query($con, "DELETE FROM accounts_voucher_detail WHERE voucher_id = $voucher_id");
        mysqli_query($con, "DELETE FROM accounts_voucher WHERE voucher_id = $voucher_id");
    }
    $_SESSION['flash_msg'] = "<div class='alert alert-success'>Record deleted successfully.</div>";
    header("Location: production_grey_fabric_purchase.php");
    exit;
}

// Flash message logic
$msg = "";
if(isset($_SESSION['flash_msg'])) {
    $msg = $_SESSION['flash_msg'];
    unset($_SESSION['flash_msg']);
}
// Show inventory account error message (if set in session)
if(isset($_SESSION['msg'])) {
    $msg .= $_SESSION['msg'];
    unset($_SESSION['msg']);
}

$edit_row = [];
if($edit_id) {
    $res = mysqli_query($con, "SELECT * FROM production_grey_fabric_purchase WHERE id = $edit_id");
    $edit_row = mysqli_fetch_assoc($res);
}

if(isset($_POST['submit'])){
    // Fetch Purchase Account (Chart of Accounts)
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
        header("Location: production_grey_fabric_purchase.php");
        exit;
    }

    $date = validate_date_sql($_POST['date']);
    $lot_no = validate_input($_POST['lot_no']);
    $supplier_id = intval($_POST['supplier_id']);
    $supplier_bill_no = validate_input($_POST['supplier_bill_no']);
    $quality_id = intval($_POST['quality_id']);
    $mtr_receive = floatval($_POST['mtr_receive']);
    $rejection = floatval($_POST['rejection']);
    $el_kami = floatval($_POST['el_kami']);
    $shortage = floatval($_POST['shortage']);
    $purchase_rate = floatval($_POST['purchase_rate']);
    $discount = floatval($_POST['discount']);
    $remarks = validate_input($_POST['remarks']);

    $net_mtr = $mtr_receive - ($rejection + $el_kami + $shortage);
    if($net_mtr < 0) $net_mtr = 0;
    $amount = $net_mtr * $purchase_rate;
    if($amount < 0) $amount = 0;
    $total_amount = $amount - $discount;
    if($total_amount < 0) $total_amount = 0;

    // Validate unique lot_no
    $lot_check = mysqli_query($con, "SELECT id FROM production_grey_fabric_purchase WHERE lot_no = '".mysqli_real_escape_string($con, $lot_no)."'".($edit_id ? " AND id!=$edit_id" : ""));
    if(mysqli_num_rows($lot_check)>0){
        $msg = "<div class='alert alert-danger'>This LOT NO. already exists. Please enter a unique LOT NO.</div>";
    } else {
        if($edit_id) {
            $query_Update = "UPDATE production_grey_fabric_purchase SET 
                date='$date',
                lot_no='$lot_no',
                supplier_id='$supplier_id',
                supplier_bill_no='$supplier_bill_no',
                quality_id='$quality_id',
                mtr_receive='$mtr_receive',
                rejection='$rejection',
                el_kami='$el_kami',
                shortage='$shortage',
                net_mtr='$net_mtr',
                purchase_rate='$purchase_rate',
                amount='$amount',
                discount='$discount',
                total_amount='$total_amount',
                remarks='$remarks',
                updated_at=NOW()
                WHERE id=$edit_id";
            $query_Run = mysqli_query($con, $query_Update);

            // Remove old voucher for this purchase
            $voucher_res = mysqli_query($con, "SELECT voucher_id FROM accounts_voucher WHERE description LIKE '%Grey Fabric Purchase [ID: $edit_id]%'");
            while($voucher_row = mysqli_fetch_assoc($voucher_res)) {
                $voucher_id = $voucher_row['voucher_id'];
                mysqli_query($con, "DELETE FROM accounts_voucher_detail WHERE voucher_id = $voucher_id");
                mysqli_query($con, "DELETE FROM accounts_voucher WHERE voucher_id = $voucher_id");
            }

            // Add new voucher for this purchase
            $supplier_account_title = '';
            $supplier_res = mysqli_query($con, "SELECT account_title FROM accounts_chart WHERE account_id='$supplier_id'");
            if($supplier_row = mysqli_fetch_assoc($supplier_res)) $supplier_account_title = $supplier_row['account_title'];
            $quality_title = '';
            $quality_res = mysqli_query($con, "SELECT quality_name FROM production_quality WHERE id='$quality_id'");
            if($quality_row = mysqli_fetch_assoc($quality_res)) $quality_title = $quality_row['quality_name'];

            $narration = "Grey Fabric Purchase [ID: $edit_id] - Supplier: $supplier_account_title, Lot No: $lot_no, Quality: $quality_title, Net Mtr: $net_mtr, Rate: $purchase_rate, Amount: $amount, Discount: $discount, Total: $total_amount";
            mysqli_query($con, "INSERT INTO accounts_voucher (entry_date, voucher_type, description, created_by) VALUES ('$date', 'Purchase', '$narration', '$u_id')");
            $voucher_id = mysqli_insert_id($con);
            // Debit purchase account
            mysqli_query($con, "INSERT INTO accounts_voucher_detail (voucher_id, account_id, description, debit, credit) VALUES ('$voucher_id', '$inventory_account_id', '$narration', '$total_amount', 0)");
            // Credit supplier account
            mysqli_query($con, "INSERT INTO accounts_voucher_detail (voucher_id, account_id, description, debit, credit) VALUES ('$voucher_id', '$supplier_id', '$narration', 0, '$total_amount')");

            $_SESSION['flash_msg'] = $query_Run ? "<div class='alert alert-success'>Purchase updated successfully.</div>" : "<div class='alert alert-danger'>Problem updating record</div>";
            header("Location: production_grey_fabric_purchase.php");
            exit;
        } else {
            $query_Insert = "INSERT INTO production_grey_fabric_purchase
                (date, lot_no, supplier_id, supplier_bill_no, quality_id, mtr_receive, rejection, el_kami, shortage, net_mtr, purchase_rate, amount, discount, total_amount, remarks, created_at)
                VALUES
                ('$date', '$lot_no', '$supplier_id', '$supplier_bill_no', '$quality_id', '$mtr_receive', '$rejection', '$el_kami', '$shortage', '$net_mtr', '$purchase_rate', '$amount', '$discount', '$total_amount', '$remarks', NOW())";
            $query_Run = mysqli_query($con, $query_Insert);
            $purchase_id = mysqli_insert_id($con);

            // Chart of account entry
            $supplier_account_title = '';
            $supplier_res = mysqli_query($con, "SELECT account_title FROM accounts_chart WHERE account_id='$supplier_id'");
            if($supplier_row = mysqli_fetch_assoc($supplier_res)) $supplier_account_title = $supplier_row['account_title'];
            $quality_title = '';
            $quality_res = mysqli_query($con, "SELECT quality_name FROM production_quality WHERE id='$quality_id'");
            if($quality_row = mysqli_fetch_assoc($quality_res)) $quality_title = $quality_row['quality_name'];

            $narration = "Grey Fabric Purchase [ID: $purchase_id] - Supplier: $supplier_account_title, Lot No: $lot_no, Quality: $quality_title, Net Mtr: $net_mtr, Rate: $purchase_rate, Amount: $amount, Discount: $discount, Total: $total_amount";
            mysqli_query($con, "INSERT INTO accounts_voucher (entry_date, voucher_type, description, created_by) VALUES ('$date', 'Purchase', '$narration', '$u_id')");
            $voucher_id = mysqli_insert_id($con);
            // Debit purchase account
            mysqli_query($con, "INSERT INTO accounts_voucher_detail (voucher_id, account_id, description, debit, credit) VALUES ('$voucher_id', '$inventory_account_id', '$narration', '$total_amount', 0)");
            // Credit supplier account
            mysqli_query($con, "INSERT INTO accounts_voucher_detail (voucher_id, account_id, description, debit, credit) VALUES ('$voucher_id', '$supplier_id', '$narration', 0, '$total_amount')");

            $_SESSION['flash_msg'] = $query_Run ? "<div class='alert alert-success'>Purchase saved successfully.</div>" : "<div class='alert alert-danger'>Problem saving record</div>";
            header("Location: production_grey_fabric_purchase.php");
            exit;
        }
    }
}

// Include HTML files
require_once ("inc/init.php");
require_once ("inc/config.ui.php");
$page_title = "Grey Fabric Purchase";
include ("inc/header.php");
include ("inc/nav.php");
?>

<!-- MAIN PANEL -->
<div id="main" role="main">
<?php $breadcrumbs["Grey Fabric Purchase"] = ""; include("inc/ribbon.php"); ?>
<style>
    body { background: #f4f7fb; }
    .main-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 6px 24px 0 #e1e8ee;
        margin: 30px auto 30px auto;
        max-width: 1100px;
        padding: 38px 42px 32px 42px;
        border: 1px solid #e7ecf3;
    }
    .main-header {
        margin-bottom: 32px;
        text-align: center;
        padding-bottom: 12px;
        border-bottom: 1px solid #e7ecf3;
    }
    .main-title {
        font-size: 2.4rem;
        font-weight: 800;
        color: #253053;
        letter-spacing: 1.5px;
    }
    .custom-form-row {
        display: flex;
        gap: 36px;
        flex-wrap: wrap;
        margin-bottom: 12px;
    }
    .custom-form-col {
        flex: 1 1 340px;
        min-width: 320px;
        display: flex;
        flex-direction: column;
        gap: 24px;
    }
    .form-label {
        font-weight: 700;
        color: #1d2b48;
        margin-bottom: 5px;
        letter-spacing: 0.4px;
    }
    .form-control, select.form-control {
        border-radius: 6px;
        border: 1.3px solid #cfd7e6;
        font-size: 16px;
        font-weight: 500;
        background: #f8fafc;
        transition: border-color .2s;
        margin-bottom: 2px;
    }
    .form-control:focus, select.form-control:focus {
        border-color: #5c9ded;
        box-shadow: 0 0 0 1px #5c9ded30;
        background: #fff;
    }
    textarea.form-control { min-height: 70px; }
    .main-footer {
        text-align: right;
        margin-top: 16px;
    }
    .btn {
        border-radius: 5px !important;
        font-weight: 700;
        font-size: 1.13rem;
        letter-spacing: 1px;
        padding: 10px 32px !important;
    }
    .btn-success {
        background: #31b857 !important;
        border: none !important;
        color: #fff !important;
        box-shadow: 0 2px 10px #0fad3c22;
    }
    .btn-default {
        background: #f9fafb !important;
        border: 1.2px solid #d2dbe9 !important;
        color: #5c6b86 !important;
    }
    .highlight-box {
        background: #ffe1f1;
        border-radius: 8px;
        padding: 19px 41px 27px 27px;
        margin-bottom: 16px;
    }
    .highlight-box label { color: #9c005d; font-weight: bold; }
    .table-section {
        margin-top: 50px;
        margin-bottom: 18px;
    }
    .custom-table {
        border-radius: 7px;
        overflow: hidden;
        box-shadow: 0 2px 16px #e9ecef;
        background: #fff;
    }
    .custom-table thead tr th {
        background: #eaeef4;
        font-size: 1rem;
        font-weight: 700;
        color: #2f415c;
        border-bottom: 2px solid #cfd8e8;
        padding: 11px 6px;
    }
    .custom-table tbody tr td {
        font-size: 0.97rem;
        color: #374158;
        padding: 10px 5px;
    }
    .custom-table tbody tr:nth-child(odd) {
        background: #f9fafb;
    }
    .table-actions .btn {
        font-size: 0.9rem !important;
        padding: 4px 18px !important;
        margin: 2px 0;
        border-radius: 4px !important;
    }
    .alert {
        margin-bottom: 18px;
        font-size: 1.02rem;
    }
    @media (max-width: 900px) {
        .main-card { padding: 18px 5vw 20px 5vw; }
        .custom-form-row { flex-direction: column; gap: 0; }
        .custom-form-col { min-width: 0; }
    }
</style>
<div id="content">
<section id="widget-grid">
<div class="row">
<article class="col-sm-12">
<div class="main-card">
    <div class="main-header">
        <span class="main-title"><i class="fa fa-suitcase"></i> PURCHASE GREY FABRIC</span>
    </div>
    <?php 
    if($msg) echo $msg; 
    ?>
   <form id="purchase-form" method="post" action="production_grey_fabric_purchase.php<?php echo $edit_id ? '?edit_id='.$edit_id : ''; ?>" autocomplete="off">
    <fieldset>
    <div class="custom-form-row">
        <div class="custom-form-col">
            <div>
                <label class="form-label">LOT # <span style="color:red">*</span></label>
                <input type="text" name="lot_no" class="form-control" value="<?php echo isset($edit_row['lot_no']) ? $edit_row['lot_no'] : ''; ?>" required autocomplete="off">
            </div>
            <div>
                <label class="form-label">Supplier <span style="color:red">*</span></label>
                <select name="supplier_id" class="form-control" required>
                    <option value="">Select Supplier</option>
                    <?php
                    $q = mysqli_query($con, "SELECT account_id, account_title FROM accounts_chart WHERE account_type='Liability' AND status='active' ORDER BY account_title");
                    while($row = mysqli_fetch_assoc($q)) {
                        $sel = (isset($edit_row['supplier_id']) && $edit_row['supplier_id'] == $row['account_id']) ? "selected" : "";
                        echo '<option value="'.$row['account_id'].'" '.$sel.'>'.$row['account_title'].'</option>';
                    }
                    ?>
                </select>
            </div>
            <div>
                <label class="form-label">Quality <span style="color:red">*</span></label>
                <select name="quality_id" class="form-control" required>
                    <option value="">Select Quality</option>
                    <?php
                    $q = mysqli_query($con, "SELECT id, quality_name FROM production_quality WHERE status=1 ORDER BY quality_name");
                    while($row = mysqli_fetch_assoc($q)) {
                        $sel = (isset($edit_row['quality_id']) && $edit_row['quality_id'] == $row['id']) ? "selected" : "";
                        echo '<option value="'.$row['id'].'" '.$sel.'>'.$row['quality_name'].'</option>';
                    }
                    ?>
                </select>
                <a href="production_quality.php" class="btn btn-xs btn-primary" style="margin-top: 3px;">Add Quality</a>
            </div>
            <div>
                <label class="form-label">Mtrs Rec <span style="color:red">*</span></label>
                <input type="number" step="0.01" name="mtr_receive" id="mtr_receive" class="form-control" value="<?php echo isset($edit_row['mtr_receive']) ? $edit_row['mtr_receive'] : ''; ?>" required>
            </div>
            <div>
                <label class="form-label">Net Mtrs</label>
                <input type="number" step="0.01" name="net_mtr" id="net_mtr" class="form-control" value="<?php echo isset($edit_row['net_mtr']) ? $edit_row['net_mtr'] : ''; ?>" readonly style="background:#f1f2f6; font-weight:700;">
            </div>
            <div class="highlight-box">
                <label>Rejection</label>
                <input type="number" step="0.01" name="rejection" id="rejection" class="form-control" value="<?php echo isset($edit_row['rejection']) ? $edit_row['rejection'] : '0'; ?>">
                <label style="margin-top:9px;">El Kami</label>
                <input type="number" step="0.01" name="el_kami" id="el_kami" class="form-control" value="<?php echo isset($edit_row['el_kami']) ? $edit_row['el_kami'] : '0'; ?>">
                <label style="margin-top:9px;">Shortage</label>
                <input type="number" step="0.01" name="shortage" id="shortage" class="form-control" value="<?php echo isset($edit_row['shortage']) ? $edit_row['shortage'] : '0'; ?>">
            </div>
        </div>
        <div class="custom-form-col">
            <div>
                <label class="form-label">Date <span style="color:red">*</span></label>
                <input type="text" name="date" value="<?php echo isset($edit_row['date']) ? date('d-m-Y', strtotime($edit_row['date'])) : date('d-m-Y'); ?>" placeholder="Select a date" class="form-control datepicker" data-dateformat="dd-mm-yy" required>
            </div>
            <div>
                <label class="form-label">Party Bill #</label>
                <input type="text" name="supplier_bill_no" class="form-control" value="<?php echo isset($edit_row['supplier_bill_no']) ? $edit_row['supplier_bill_no'] : ''; ?>">
            </div>
            <div>
                <label class="form-label">Purchase Rate <span style="color:red">*</span></label>
                <input type="number" step="0.01" name="purchase_rate" id="purchase_rate" class="form-control" value="<?php echo isset($edit_row['purchase_rate']) ? $edit_row['purchase_rate'] : ''; ?>" required>
            </div>
            <div>
                <label class="form-label">Amount</label>
                <input type="text" name="amount" id="amount" readonly class="form-control" value="<?php echo isset($edit_row['amount']) ? $edit_row['amount'] : ''; ?>">
            </div>
            <div>
                <label class="form-label">Discount</label>
                <input type="number" step="0.01" name="discount" id="discount" class="form-control" value="<?php echo isset($edit_row['discount']) ? $edit_row['discount'] : '0'; ?>">
            </div>
            <div>
                <label class="form-label" style="font-weight:700;">TOTAL AMOUNT</label>
                <input type="text" name="total_amount" id="total_amount" readonly class="form-control" style="background:#ffe1f1; font-weight:700;" value="<?php echo isset($edit_row['total_amount']) ? $edit_row['total_amount'] : ''; ?>">
            </div>
            <div>
                <label class="form-label">Remarks</label>
                <textarea name="remarks" class="form-control"><?php echo isset($edit_row['remarks']) ? htmlspecialchars($edit_row['remarks']) : ''; ?></textarea>
            </div>
        </div>
    </div>
    </fieldset>
    <div class="main-footer">
        <input type="submit" class="btn btn-success" name="submit" value="<?php echo $edit_id ? 'Update' : 'Save'; ?>">
        <?php if($edit_id) { ?>
            <a href="production_grey_fabric_purchase.php" class="btn btn-default">Cancel</a>
        <?php } ?>
    </div>
</form>
</div>

<!-- Purchase List -->
<div class="main-card table-section">
    <h3 style="margin-bottom:24px; color:#253053; font-weight:700; text-align:center"><i class="fa fa-list"></i> Grey Fabric Purchase List</h3>
    <div class="table-responsive">
    <table id="datatable_fixed_column" class="display custom-table" style="width:100%">
    <thead>
        <tr>
            <th>Sr #</th>
            <th>Date</th>
            <th>LOT NO.</th>
            <th>Supplier</th>
            <th>Quality</th>
            <th>Mtrs Rec</th>
            <th>Rejection</th>
            <th>El Kami</th>
            <th>Shortage</th>
            <th>Net Mtrs</th>
            <th>Rate</th>
            <th>Discount</th>
            <th>Total Amount</th>
            <th>Action</th>
        </tr>
        <tr>
            <th><input type="text" class="form-control input-sm" placeholder="Sr #"></th>
            <th><input type="text" class="form-control input-sm" placeholder="Date"></th>
            <th><input type="text" class="form-control input-sm" placeholder="LOT NO."></th>
            <th><input type="text" class="form-control input-sm" placeholder="Supplier"></th>
            <th><input type="text" class="form-control input-sm" placeholder="Quality"></th>
            <th><input type="text" class="form-control input-sm" placeholder="Mtrs"></th>
            <th><input type="text" class="form-control input-sm" placeholder="Rejection"></th>
            <th><input type="text" class="form-control input-sm" placeholder="El Kami"></th>
            <th><input type="text" class="form-control input-sm" placeholder="Shortage"></th>
            <th><input type="text" class="form-control input-sm" placeholder="Net"></th>
            <th><input type="text" class="form-control input-sm" placeholder="Rate"></th>
            <th><input type="text" class="form-control input-sm" placeholder="Discount"></th>
            <th><input type="text" class="form-control input-sm" placeholder="Total"></th>
            <th></th>
        </tr>
    </thead>
    <tbody>
    <?php
    $select_All = "SELECT 
        P.*, 
        AC.account_title as supplier_name, 
        Q.quality_name 
    FROM production_grey_fabric_purchase P
    LEFT JOIN accounts_chart AC ON AC.account_id = P.supplier_id
    LEFT JOIN production_quality Q ON Q.id = P.quality_id
    ORDER BY P.id DESC";
    $select_All_Run = mysqli_query($con, $select_All);
    $i = 1;
    while ($row = mysqli_fetch_assoc($select_All_Run)) {
    ?>
    <tr>
        <td style="text-align:center;"><?php echo $i++; ?></td>
        <td><?php echo validate_date_display($row['date']); ?></td>
        <td><?php echo htmlspecialchars($row['lot_no']); ?></td>
        <td><?php echo htmlspecialchars($row['supplier_name']); ?></td>
        <td><?php echo htmlspecialchars($row['quality_name']); ?></td>
        <td style="text-align:right;"><?php echo number_format($row['mtr_receive'],2); ?></td>
        <td style="text-align:right;"><?php echo number_format($row['rejection'],2); ?></td>
        <td style="text-align:right;"><?php echo number_format($row['el_kami'],2); ?></td>
        <td style="text-align:right;"><?php echo number_format($row['shortage'],2); ?></td>
        <td style="text-align:right;"><?php echo number_format($row['net_mtr'],2); ?></td>
        <td style="text-align:right;"><?php echo number_format($row['purchase_rate'],2); ?></td>
        <td style="text-align:right;"><?php echo number_format($row['discount'],2); ?></td>
        <td style="text-align:right;"><?php echo number_format($row['total_amount'],2); ?></td>
        <td class="table-actions" style="text-align:center;">
            <a href="production_grey_fabric_purchase.php?edit_id=<?php echo $row['id']; ?>" class="btn btn-xs btn-warning" title="Edit"><i class="fa fa-edit"></i> Edit</a>
            <a href="production_grey_fabric_purchase.php?delete_id=<?php echo $row['id']; ?>" class="btn btn-xs btn-danger" title="Delete" onclick="return confirm('Delete this record?');"><i class="fa fa-trash"></i> Delete</a>
        </td>
    </tr>
    <?php } ?>
    </tbody>
    </table>
    </div>
</div>
<!-- End Purchase List -->

</article>
</div><!--End of div row-->
</section>
</div><!-- END MAIN CONTENT -->
</div><!-- END MAIN PANEL -->

<?php include ("inc/footer.php"); ?>
<?php include ("inc/scripts.php"); ?>

<!-- Scripts: jQuery first, then DataTables, then your custom JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script>
$(document).ready(function() {
    // DataTable initialization
    var otable = $('#datatable_fixed_column').DataTable({
        "order": [[ 0, 'desc' ]]
    });

    // Column search
    $("#datatable_fixed_column thead tr:eq(1) th input").on('keyup change', function() {
        otable.column($(this).parent().index() + ':visible').search(this.value).draw();
    });

    // LIVE CALCULATION SCRIPT
    function calcAmounts() {
        var mtr_receive = parseFloat($("#mtr_receive").val()) || 0;
        var rejection = parseFloat($("#rejection").val()) || 0;
        var el_kami = parseFloat($("#el_kami").val()) || 0;
        var shortage = parseFloat($("#shortage").val()) || 0;
        var purchase_rate = parseFloat($("#purchase_rate").val()) || 0;
        var discount = parseFloat($("#discount").val()) || 0;

        var net_mtr = mtr_receive - rejection - el_kami - shortage;
        if (net_mtr < 0) net_mtr = 0;
        $("#net_mtr").val(net_mtr.toFixed(2));

        var amount = net_mtr * purchase_rate;
        if (amount < 0) amount = 0;

        var total_amount = amount - discount;
        if (total_amount < 0) total_amount = 0;

        $("#amount").val(amount.toFixed(2));
        $("#total_amount").val(total_amount.toFixed(2));
    }

    $("#mtr_receive, #rejection, #el_kami, #shortage, #purchase_rate, #discount").on('input change', function(){
        calcAmounts();
    });

    // Initial calculation (for edit mode)
    calcAmounts();

    // Datepicker (if you use jQuery UI)
    if ($('.datepicker').length && typeof $.fn.datepicker === 'function') {
        $('.datepicker').datepicker({ dateFormat: 'dd-mm-yy' });
    }
});
</script>