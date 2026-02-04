<?php
include('sessionCheck.php');
include 'connection.php';
include('functions.php');
$branch_id = $_SESSION['branch_id'];
$u_id = $_SESSION['u_id'];

// Sr No. logic
$srNoRes = mysqli_query($con, "SELECT IFNULL(MAX(id),0) as lastSrNo FROM production_grey_fabric_issue");
$srNoRow = mysqli_fetch_assoc($srNoRes);
$currentSrNo = intval($srNoRow['lastSrNo']) + 1;

// Edit/Delete logic
$edit_id = isset($_GET['edit_id']) ? intval($_GET['edit_id']) : 0;
$delete_id = isset($_GET['delete_id']) ? intval($_GET['delete_id']) : 0;

// Handle delete
if($delete_id) {
    mysqli_query($con, "DELETE FROM production_grey_fabric_issue WHERE id = $delete_id");
    $_SESSION['flash_msg'] = "<div class='alert alert-success'>Record deleted successfully.</div>";
    header("Location: production_grey_fabric_issue.php");
    exit;
}

// Flash message logic
$msg = "";
if(isset($_SESSION['flash_msg'])) {
    $msg = $_SESSION['flash_msg'];
    unset($_SESSION['flash_msg']);
}

$edit_row = [];
if($edit_id) {
    $res = mysqli_query($con, "SELECT * FROM production_grey_fabric_issue WHERE id = $edit_id");
    $edit_row = mysqli_fetch_assoc($res);
}

if(isset($_POST['submit'])){
    $date = validate_date_sql($_POST['date']);
    $dyeing_unit_id = intval($_POST['dyeing_unit_id']);
    $lot_no = validate_input($_POST['lot_no']);
    $quality_id = intval($_POST['quality_id']);
    $issued_mtr = floatval($_POST['issued_mtr']);
    $remarks = validate_input($_POST['remarks']);

    // Stock check (for safety)
    $q = mysqli_query($con, "SELECT IFNULL(SUM(net_mtr),0) as mtr FROM production_grey_fabric_purchase WHERE lot_no='".mysqli_real_escape_string($con, $lot_no)."' AND quality_id=$quality_id");
    $r = mysqli_fetch_assoc($q);
    $purchased = floatval($r['mtr']);
    $q = mysqli_query($con, "SELECT IFNULL(SUM(issued_mtr),0) as mtr FROM production_grey_fabric_issue WHERE lot_no='".mysqli_real_escape_string($con, $lot_no)."' AND quality_id=$quality_id" . ($edit_id ? " AND id!=$edit_id" : ""));
    $r = mysqli_fetch_assoc($q);
    $issued = floatval($r['mtr']);
    $stock = $purchased - $issued;

    if($issued_mtr > $stock) {
        $msg = "<div class='alert alert-danger'>Issued meters exceed available stock ($stock).</div>";
    } else {
        if($edit_id) {
            $query_Update = "UPDATE production_grey_fabric_issue SET 
                date='$date',
                dyeing_unit_id='$dyeing_unit_id',
                lot_no='$lot_no',
                quality_id='$quality_id',
                issued_mtr='$issued_mtr',
                remarks='$remarks',
                updated_at=NOW()
                WHERE id=$edit_id";
            $query_Run = mysqli_query($con, $query_Update);
            $_SESSION['flash_msg'] = $query_Run ? "<div class='alert alert-success'>Issue updated successfully.</div>" : "<div class='alert alert-danger'>Problem updating record</div>";
            header("Location: production_grey_fabric_issue.php");
            exit;
        } else {
            $query_Insert = "INSERT INTO production_grey_fabric_issue
                (date, dyeing_unit_id, lot_no, quality_id, issued_mtr, remarks, created_at)
                VALUES
                ('$date', '$dyeing_unit_id', '$lot_no', '$quality_id', '$issued_mtr', '$remarks', NOW())";
            $query_Run = mysqli_query($con, $query_Insert);
            $_SESSION['flash_msg'] = $query_Run ? "<div class='alert alert-success'>Issue saved successfully.</div>" : "<div class='alert alert-danger'>Problem saving record</div>";
            header("Location: production_grey_fabric_issue.php");
            exit;
        }
    }
}

// Include HTML files
require_once ("inc/init.php");
require_once ("inc/config.ui.php");
$page_title = "Grey Fabric Issue";
include ("inc/header.php");
include ("inc/nav.php");
?>

<!-- MAIN PANEL -->
<div id="main" role="main">
<?php $breadcrumbs["Grey Fabric Issue"] = ""; include("inc/ribbon.php"); ?>
<style>
    body { background: #f4f7fb; }
    .main-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 6px 24px 0 #e1e8ee;
        margin: 30px auto 30px auto;
        max-width: 1050px;
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
    .stock-info {
        background: #fbe9e7;
        color: #d84315;
        font-weight: 700;
        border-radius:7px;
        padding: 7px 18px;
        margin: 8px 0 0 0;
        display: inline-block;
        font-size: 16px;
        letter-spacing: 0.2px;
        border: 1px solid #ffccbc;
    }
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
        <span class="main-title"><i class="fa fa-share"></i> ISSUE GREY FABRIC</span>
    </div>
    <?php if($msg) echo $msg; ?>

   <form id="issue-form" method="post" action="production_grey_fabric_issue.php<?php echo $edit_id ? '?edit_id='.$edit_id : ''; ?>" autocomplete="off">
    <fieldset>
    <div class="custom-form-row">
        <!-- Left col -->
        <div class="custom-form-col">
            <div>
                <label class="form-label">LOT # <span style="color:red">*</span></label>
                <select name="lot_no" id="lot_no" class="form-control" required>
                    <option value="">Select LOT</option>
                    <?php
                    $q = mysqli_query($con, "SELECT lot_no FROM production_grey_fabric_purchase ORDER BY lot_no");
                    while($row = mysqli_fetch_assoc($q)) {
                        $sel = (isset($edit_row['lot_no']) && $edit_row['lot_no'] == $row['lot_no']) ? "selected" : "";
                        echo '<option value="'.$row['lot_no'].'" '.$sel.'>'.$row['lot_no'].'</option>';
                    }
                    ?>
                </select>
            </div>
            <div>
                <label class="form-label">Quality <span style="color:red">*</span></label>
                <select name="quality_id" id="quality_id" class="form-control" required>
                    <option value="">Select Quality</option>
                    <?php
                    $q = mysqli_query($con, "SELECT id, quality_name FROM production_quality WHERE status=1 ORDER BY quality_name");
                    while($row = mysqli_fetch_assoc($q)) {
                        $sel = (isset($edit_row['quality_id']) && $edit_row['quality_id'] == $row['id']) ? "selected" : "";
                        echo '<option value="'.$row['id'].'" '.$sel.'>'.$row['quality_name'].'</option>';
                    }
                    ?>
                </select>
            </div>
            <div id="stockDiv">
                <!-- AJAX Stock Show Here -->
            </div>
            <div>
                <label class="form-label">Mtrs Issue <span style="color:red">*</span></label>
                <input type="number" step="0.01" name="issued_mtr" class="form-control" value="<?php echo isset($edit_row['issued_mtr']) ? $edit_row['issued_mtr'] : ''; ?>" required>
            </div>
        </div>
        <!-- Right col -->
        <div class="custom-form-col">
            <div class="form-inline-row">
                <div>
                    <label class="form-label">Date <span style="color:red">*</span></label>
                    <input type="text" name="date" value="<?php echo isset($edit_row['date']) ? date('d-m-Y', strtotime($edit_row['date'])) : date('d-m-Y'); ?>" placeholder="Select a date" class="form-control datepicker" data-dateformat="dd-mm-yy" required>
                </div>
                <div>
                    <label class="form-label">Dyeing Unit <span style="color:red">*</span></label>
                    <select name="dyeing_unit_id" id="dyeing_unit_id" class="form-control" required>
                        <option value="">Select Dyeing Unit</option>
                        <?php
                        // Only show accounts of type liability
                        $q = mysqli_query($con, "SELECT account_id, account_title FROM accounts_chart WHERE account_type='liability' AND status='active' ORDER BY account_title");
                        while($row = mysqli_fetch_assoc($q)) {
                            $sel = (isset($edit_row['dyeing_unit_id']) && $edit_row['dyeing_unit_id'] == $row['account_id']) ? "selected" : "";
                            echo '<option value="'.$row['account_id'].'" '.$sel.'>'.$row['account_title'].'</option>';
                        }
                        ?>
                    </select>
                </div>
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
            <a href="production_grey_fabric_issue.php" class="btn btn-default">Cancel</a>
        <?php } ?>
    </div>
</form>
</div>

<!-- Issue List -->
<div class="main-card table-section">
<h3 style="margin-bottom:24px; color:#253053; font-weight:700; text-align:center"><i class="fa fa-list"></i> Grey Fabric Issue List</h3>
<div class="table-responsive">
<table id="datatable_fixed_column" class="display custom-table" style="width:100%">
<thead>
    <tr>
        <th>Sr #</th>
        <th>Date</th>
        <th>LOT NO.</th>
        <th>Quality</th>
        <th>Mtrs Issue</th>
        <th>Dyeing Unit</th>
        <th>Remarks</th>
        <th>Action</th>
    </tr>
    <tr>
        <th><input type="text" class="form-control input-sm" placeholder="Sr #"></th>
        <th><input type="text" class="form-control input-sm" placeholder="Date"></th>
        <th><input type="text" class="form-control input-sm" placeholder="LOT NO."></th>
        <th><input type="text" class="form-control input-sm" placeholder="Quality"></th>
        <th><input type="text" class="form-control input-sm" placeholder="Mtrs"></th>
        <th><input type="text" class="form-control input-sm" placeholder="Dyeing Unit"></th>
        <th><input type="text" class="form-control input-sm" placeholder="Remarks"></th>
        <th></th>
    </tr>
</thead>
<tbody>
<?php
$select_All = "SELECT 
    I.*, 
    Q.quality_name,
    AC.account_title as dyeing_unit
FROM production_grey_fabric_issue I
LEFT JOIN production_quality Q ON Q.id = I.quality_id
LEFT JOIN accounts_chart AC ON AC.account_id = I.dyeing_unit_id
ORDER BY I.id DESC";
$select_All_Run = mysqli_query($con, $select_All);
$i = 1;
while ($row = mysqli_fetch_assoc($select_All_Run)) {
?>
<tr>
    <td style="text-align:center;"><?php echo $i++; ?></td>
    <td><?php echo validate_date_display($row['date']); ?></td>
    <td><?php echo htmlspecialchars($row['lot_no']); ?></td>
    <td><?php echo htmlspecialchars($row['quality_name']); ?></td>
    <td style="text-align:right;"><?php echo number_format($row['issued_mtr'],2); ?></td>
    <td><?php echo htmlspecialchars($row['dyeing_unit']); ?></td>
    <td><?php echo htmlspecialchars($row['remarks']); ?></td>
    <td class="table-actions" style="text-align:center;">
        <a href="production_grey_fabric_issue.php?edit_id=<?php echo $row['id']; ?>" class="btn btn-xs btn-warning" title="Edit"><i class="fa fa-edit"></i> Edit</a>
        <a href="production_grey_fabric_issue.php?delete_id=<?php echo $row['id']; ?>" class="btn btn-xs btn-danger" title="Delete" onclick="return confirm('Delete this record?');"><i class="fa fa-trash"></i> Delete</a>
    </td>
</tr>
<?php } ?>
</tbody>
</table>
</div>
</div>
<!-- End Issue List -->

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

    // LIVE STOCK AJAX
    function fetchStock() {
        var lot_no = $("#lot_no").val();
        var quality_id = $("#quality_id").val();
        if(lot_no && quality_id) {
            $.get('ajax/grey_fabric_stock.php', {lot_no: lot_no, quality_id: quality_id}, function(res){
                $("#stockDiv").html('<div class="stock-info">Available Meters: '+res.stock+'</div>');
            },'json').fail(function(){
                $("#stockDiv").html('<div class="stock-info" style="color:red;">Error fetching stock!</div>');
            });
        } else {
            $("#stockDiv").html('');
        }
    }
    $("#lot_no, #quality_id").on('change', fetchStock);
    fetchStock();

    // Datepicker (if you use jQuery UI)
    if ($('.datepicker').length && typeof $.fn.datepicker === 'function') {
        $('.datepicker').datepicker({ dateFormat: 'dd-mm-yy' });
    }
});
</script>