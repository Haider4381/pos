<?php
include('sessionCheck.php');
include 'connection.php';
include('functions.php');

// --- "item_id" column must exist in "production_embroidery_issue_detail" table ---
// ALTER TABLE `production_embroidery_issue_detail` ADD COLUMN `item_id` INT(11) NOT NULL DEFAULT 0 AFTER `stock`;

$msg = "";
$edit_id = isset($_GET['edit_id']) ? intval($_GET['edit_id']) : 0;
$delete_id = isset($_GET['delete_id']) ? intval($_GET['delete_id']) : 0;

// Handle delete
if($delete_id) {
    // Pehle child/detail records delete karo
    mysqli_query($con, "DELETE FROM production_embroidery_issue_detail WHERE embroidery_issue_id = $delete_id");
    // Phir parent/main record delete karo
    mysqli_query($con, "DELETE FROM production_embroidery_issue WHERE id = $delete_id");
    $_SESSION['flash_msg'] = "<div class='alert alert-success'>Record deleted successfully.</div>";
    header("Location: production_embroidery_issue.php");
    exit;
}
// Flash message logic
if(isset($_SESSION['flash_msg'])) {
    $msg = $_SESSION['flash_msg'];
    unset($_SESSION['flash_msg']);
}

$edit_row = [];
$details = [];
if($edit_id) {
    $res = mysqli_query($con, "SELECT * FROM production_embroidery_issue WHERE id = $edit_id");
    $edit_row = mysqli_fetch_assoc($res);
    $detail_res = mysqli_query($con, "SELECT * FROM production_embroidery_issue_detail WHERE embroidery_issue_id = $edit_id");
    while($row = mysqli_fetch_assoc($detail_res)) {
        $details[] = $row;
    }
}

// Fetch items for dropdown
$items = [];
$item_res = mysqli_query($con, "SELECT item_id, item_Name FROM adm_item WHERE item_Status='A' ORDER BY item_Name");
while($row = mysqli_fetch_assoc($item_res)) {
    $items[] = $row;
}

if(isset($_POST['submit'])){
    $date = validate_date_sql($_POST['date']);
    $embroidery_unit_id = intval($_POST['embroidery_unit_id']);
    $remarks = validate_input($_POST['remarks']);

    if($edit_id) {
        $query_Update = "UPDATE production_embroidery_issue SET 
            date='$date',
            embroidery_unit_id='$embroidery_unit_id',
            remarks='$remarks'
            WHERE id=$edit_id";
        mysqli_query($con, $query_Update);
        // Remove old details
        mysqli_query($con, "DELETE FROM production_embroidery_issue_detail WHERE embroidery_issue_id = $edit_id");
        $embroidery_issue_id = $edit_id;
    } else {
        $query_Insert = "INSERT INTO production_embroidery_issue
            (date, embroidery_unit_id, remarks, created_at)
            VALUES
            ('$date', '$embroidery_unit_id', '$remarks', NOW())";
        mysqli_query($con, $query_Insert);
        $embroidery_issue_id = mysqli_insert_id($con);
    }

    // Now insert details
    if(isset($_POST['rows']) && is_array($_POST['rows'])) {
        foreach($_POST['rows'] as $row) {
            $lot_no = validate_input($row['lot_no']);
            $item_id = intval($row['item_id']);
            $description = validate_input($row['description']);
            $suits = intval($row['suits']);
            $mtr_per_suit = floatval($row['mtr_per_suit']);
            $total_mtrs = floatval($row['total_mtrs']);
            $stock = floatval($row['stock']);

            $query_detail = "INSERT INTO production_embroidery_issue_detail
                (embroidery_issue_id, lot_no, stock, item_id, description, suits, mtr_per_suit, total_mtrs)
                VALUES
                ('$embroidery_issue_id', '$lot_no', '$stock', '$item_id', '$description', '$suits', '$mtr_per_suit', '$total_mtrs')";
            mysqli_query($con, $query_detail);
        }
    }

    $_SESSION['flash_msg'] = "<div class='alert alert-success'>Embroidery Issue saved successfully.</div>";
    header("Location: production_embroidery_issue.php");
    exit;
}

require_once ("inc/init.php");
require_once ("inc/config.ui.php");
$page_title = "Embroidery Fabric Issue";
include ("inc/header.php");
include ("inc/nav.php");
?>

<!-- MAIN PANEL -->
<div id="main" role="main">
<?php $breadcrumbs["Embroidery Fabric Issue"] = ""; include("inc/ribbon.php"); ?>
<style>
    body { background: #f4f7fb; }
    .main-card { background: #fff; border-radius: 12px; box-shadow: 0 6px 24px 0 #e1e8ee; margin: 30px auto 30px auto; max-width: 950px; padding: 38px 42px 32px 42px; border: 1px solid #e7ecf3; }
    .main-header { margin-bottom: 32px; text-align: center; padding-bottom: 12px; border-bottom: 1px solid #e7ecf3; }
    .main-title { font-size: 2.2rem; font-weight: 800; color: #253053; letter-spacing: 1.2px; }
    .custom-form-row { display: flex; gap: 28px; flex-wrap: wrap; margin-bottom: 12px; }
    .custom-form-col { flex: 1 1 280px; min-width: 260px; display: flex; flex-direction: column; gap: 22px; }
    .form-label { font-weight: 700; color: #1d2b48; margin-bottom: 5px; letter-spacing: 0.4px; }
    .form-control, select.form-control { border-radius: 6px; border: 1.3px solid #cfd7e6; font-size: 16px; font-weight: 500; background: #f8fafc; transition: border-color .2s; margin-bottom: 2px; }
    .form-control:focus, select.form-control:focus { border-color: #5c9ded; box-shadow: 0 0 0 1px #5c9ded30; background: #fff; }
    textarea.form-control { min-height: 70px; }
    .main-footer { text-align: right; margin-top: 16px; }
    .btn { border-radius: 5px !important; font-weight: 700; font-size: 1.13rem; letter-spacing: 1px; padding: 10px 32px !important; }
    .btn-success { background: #31b857 !important; border: none !important; color: #fff !important; box-shadow: 0 2px 10px #0fad3c22; }
    .btn-default { background: #f9fafb !important; border: 1.2px solid #d2dbe9 !important; color: #5c6b86 !important; }
    .alert { margin-bottom: 18px; font-size: 1.02rem; }
    .table-section { margin-top: 40px; margin-bottom: 18px; max-width: 1000px; margin-left:auto; margin-right:auto; }
    .custom-table { border-radius: 7px; overflow: hidden; box-shadow: 0 2px 16px #e9ecef; background: #fff; }
    .custom-table thead tr th { background: #eaeef4; font-size: 1rem; font-weight: 700; color: #2f415c; border-bottom: 2px solid #cfd8e8; padding: 11px 6px; }
    .custom-table tbody tr td { font-size: 0.97rem; color: #374158; padding: 10px 5px; }
    .custom-table tbody tr:nth-child(odd) { background: #f9fafb; }
    .table-actions .btn { font-size: 0.9rem !important; padding: 4px 18px !important; margin: 2px 0; border-radius: 4px !important; }
    #row-items-table input, #row-items-table select { min-width: 70px; }
    .row-btns { display:flex; gap:8px;}
    .table-responsive { overflow-x: auto; }
    @media (max-width: 900px) { .main-card { padding: 16px 3vw 18px 3vw; } .custom-form-row { flex-direction: column; gap: 0; } .custom-form-col { min-width: 0; } .table-section { padding: 0 2vw; } }
</style>
<div id="content">
<section id="widget-grid">
<div class="row">
<article class="col-sm-12">
<div class="main-card">
    <div class="main-header">
        <span class="main-title"><i class="fa fa-sign-in"></i> EMBROIDERY FABRIC ISSUE</span>
    </div>
    <?php if($msg) echo $msg; ?>

   <form id="issue-form" method="post" action="production_embroidery_issue.php<?php echo $edit_id ? '?edit_id='.$edit_id : ''; ?>" autocomplete="off">
    <fieldset>
    <div class="custom-form-row">
        <div class="custom-form-col">
            <div>
                <label class="form-label">Date <span style="color:red">*</span></label>
                <input type="text" name="date" value="<?php echo isset($edit_row['date']) ? date('d-m-Y', strtotime($edit_row['date'])) : date('d-m-Y'); ?>" placeholder="Select a date" class="form-control datepicker" data-dateformat="dd-mm-yy" required>
            </div>
            <div>
                <label class="form-label">Embroidery Unit <span style="color:red">*</span></label>
                <select name="embroidery_unit_id" class="form-control" required>
                    <option value="">Select Embroidery Unit</option>
                    <?php
                    $q = mysqli_query($con, "SELECT account_id, account_title FROM accounts_chart WHERE account_type='liability' AND status='active' ORDER BY account_title");
                    while($row = mysqli_fetch_assoc($q)) {
                        $sel = (isset($edit_row['embroidery_unit_id']) && $edit_row['embroidery_unit_id'] == $row['account_id']) ? "selected" : "";
                        echo '<option value="'.$row['account_id'].'" '.$sel.'>'.$row['account_title'].'</option>';
                    }
                    ?>
                </select>
            </div>
            <div>
                <label class="form-label">Remarks</label>
                <textarea name="remarks" class="form-control"><?php echo isset($edit_row['remarks']) ? htmlspecialchars($edit_row['remarks']) : ''; ?></textarea>
            </div>
        </div>
    </div>
    </fieldset>

    <fieldset>
    <legend style="font-size:1.18rem; font-weight:700; color:#253053; margin-bottom:14px">Fabric Issue Details</legend>
    <div class="table-responsive">
    <table id="row-items-table" class="custom-table" style="width:100%; min-width:1000px">
        <thead>
            <tr>
                <th>Lot No</th>
                <th>Stock</th>
                <th>Item Name</th>
                <th>Description</th>
                <th>Suits</th>
                <th>Mtr/Suit</th>
                <th>Total Mtrs</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        <?php
        // If editing, show all detail rows from DB, else show one blank row
        if($edit_id && !empty($details)) {
            foreach($details as $i => $row) {
        ?>
            <tr>
                <td>
                    <select name="rows[<?php echo $i; ?>][lot_no]" class="form-control lot-select" required>
                        <option value="">Lot</option>
                        <?php
                        $lotq = mysqli_query($con, "SELECT DISTINCT lot_no FROM production_grey_fabric_purchase ORDER BY lot_no");
                        while($lotrow = mysqli_fetch_assoc($lotq)) {
                            $sel = ($row['lot_no'] == $lotrow['lot_no']) ? "selected" : "";
                            echo '<option value="'.$lotrow['lot_no'].'" '.$sel.'>'.$lotrow['lot_no'].'</option>';
                        }
                        ?>
                    </select>
                </td>
                <td>
                    <input type="number" step="0.01" name="rows[<?php echo $i; ?>][stock]" class="form-control stock-field" value="<?php echo $row['stock']; ?>" readonly>
                </td>
                <td>
                    <select name="rows[<?php echo $i; ?>][item_id]" class="form-control item-select" required>
                        <option value="">Select Item</option>
                        <?php
                        foreach($items as $item) {
                            $sel = ($row['item_id'] == $item['item_id']) ? "selected" : "";
                            echo '<option value="'.$item['item_id'].'" '.$sel.'>'.$item['item_Name'].'</option>';
                        }
                        ?>
                    </select>
                </td>
                <td>
                    <input type="text" name="rows[<?php echo $i; ?>][description]" class="form-control" value="<?php echo htmlspecialchars($row['description']); ?>">
                </td>
                <td>
                    <input type="number" step="1" name="rows[<?php echo $i; ?>][suits]" class="form-control suits-field" value="<?php echo $row['suits']; ?>" required>
                </td>
                <td>
                    <input type="number" step="0.01" name="rows[<?php echo $i; ?>][mtr_per_suit]" class="form-control mtr-per-suit-field" value="<?php echo $row['mtr_per_suit']; ?>" required>
                </td>
                <td>
                    <input type="number" step="0.01" name="rows[<?php echo $i; ?>][total_mtrs]" class="form-control total-mtrs-field" value="<?php echo $row['total_mtrs']; ?>" readonly>
                </td>
                <td class="row-btns">
                    <button type="button" class="btn btn-xs btn-danger remove-row"><i class="fa fa-trash"></i></button>
                </td>
            </tr>
        <?php
            }
            $rowIdx = count($details);
        } else {
            $rowIdx = 1;
        ?>
        <tr>
            <td>
                <select name="rows[0][lot_no]" class="form-control lot-select" required>
                    <option value="">Select Lot</option>
                    <?php
                    $lotq = mysqli_query($con, "SELECT DISTINCT lot_no FROM production_grey_fabric_purchase ORDER BY lot_no");
                    while($lotrow = mysqli_fetch_assoc($lotq)) {
                        echo '<option value="'.$lotrow['lot_no'].'">'.$lotrow['lot_no'].'</option>';
                    }
                    ?>
                </select>
            </td>
            <td>
                <input type="number" step="0.01" name="rows[0][stock]" class="form-control stock-field" value="" readonly>
            </td>
            <td>
                <select name="rows[0][item_id]" class="form-control item-select" required>
                    <option value="">Select Item</option>
                    <?php
                    foreach($items as $item) {
                        echo '<option value="'.$item['item_id'].'">'.$item['item_Name'].'</option>';
                    }
                    ?>
                </select>
            </td>
            <td>
                <input type="text" name="rows[0][description]" class="form-control" value="">
            </td>
            <td>
                <input type="number" step="1" name="rows[0][suits]" class="form-control suits-field" value="" required>
            </td>
            <td>
                <input type="number" step="0.01" name="rows[0][mtr_per_suit]" class="form-control mtr-per-suit-field" value="" required>
            </td>
            <td>
                <input type="number" step="0.01" name="rows[0][total_mtrs]" class="form-control total-mtrs-field" value="" readonly>
            </td>
            <td class="row-btns">
                <button type="button" class="btn btn-xs btn-danger remove-row"><i class="fa fa-trash"></i></button>
            </td>
        </tr>
        <?php
        }
        ?>
        </tbody>
    </table>
    <div style="margin-top:12px;">
        <button type="button" class="btn btn-success" id="add-row-btn"><i class="fa fa-plus"></i> Add Row</button>
    </div>
    </div>
    </fieldset>
    <div class="main-footer">
        <input type="submit" class="btn btn-success" name="submit" value="<?php echo $edit_id ? 'Update' : 'Save'; ?>">
        <?php if($edit_id) { ?>
            <a href="production_embroidery_issue.php" class="btn btn-default">Cancel</a>
        <?php } ?>
    </div>
</form>
</div>

<!-- Issue List -->
<div class="main-card table-section">
    <h3 style="margin-bottom:24px; color:#253053; font-weight:700; text-align:center"><i class="fa fa-list"></i> Embroidery Issue List</h3>
    <div class="table-responsive">
    <table id="datatable_fixed_column" class="display custom-table" style="width:100%">
    <thead>
        <tr>
            <th>Sr #</th>
            <th>Date</th>
            <th>Embroidery Unit</th>
            <th>Remarks</th>
            <th>Action</th>
        </tr>
        <tr>
            <th><input type="text" class="form-control input-sm" placeholder="Sr #"></th>
            <th><input type="text" class="form-control input-sm" placeholder="Date"></th>
            <th><input type="text" class="form-control input-sm" placeholder="Embroidery Unit"></th>
            <th><input type="text" class="form-control input-sm" placeholder="Remarks"></th>
            <th></th>
        </tr>
    </thead>
    <tbody>
    <?php
    $select_All = "SELECT 
        EI.*, 
        AC.account_title as embroidery_unit
    FROM production_embroidery_issue EI
    LEFT JOIN accounts_chart AC ON AC.account_id = EI.embroidery_unit_id
    ORDER BY EI.id DESC";
    $i = 1;
    $select_All_Run = mysqli_query($con, $select_All);
    while ($row = mysqli_fetch_assoc($select_All_Run)) {
    ?>
    <tr>
        <td style="text-align:center;"><?php echo $i++; ?></td>
        <td><?php echo validate_date_display($row['date']); ?></td>
        <td><?php echo htmlspecialchars($row['embroidery_unit']); ?></td>
        <td><?php echo htmlspecialchars($row['remarks']); ?></td>
        <td class="table-actions" style="text-align:center;">
            <a href="production_embroidery_issue.php?edit_id=<?php echo $row['id']; ?>" class="btn btn-xs btn-warning" title="Edit"><i class="fa fa-edit"></i> Edit</a>
            <a href="production_embroidery_issue.php?delete_id=<?php echo $row['id']; ?>" class="btn btn-xs btn-danger" title="Delete" onclick="return confirm('Delete this record?');"><i class="fa fa-trash"></i> Delete</a>
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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script>
$(document).ready(function() {
    var otable = $('#datatable_fixed_column').DataTable({
        "order": [[ 0, 'desc' ]]
    });

    $("#datatable_fixed_column thead tr:eq(1) th input").on('keyup change', function() {
        otable.column($(this).parent().index() + ':visible').search(this.value).draw();
    });

    var rowIdx = <?php echo isset($rowIdx) ? $rowIdx : 1; ?>;

    $('#add-row-btn').on('click', function(){
        var newRow = `<tr>
            <td>
                <select name="rows[`+rowIdx+`][lot_no]" class="form-control lot-select" required>
                    <option value="">Select Lot</option>
                    <?php
                    $lotq = mysqli_query($con, "SELECT DISTINCT lot_no FROM production_grey_fabric_purchase ORDER BY lot_no");
                    while($lotrow = mysqli_fetch_assoc($lotq)) {
                        echo '<option value="'.$lotrow['lot_no'].'">'.$lotrow['lot_no'].'</option>';
                    }
                    ?>
                </select>
            </td>
            <td>
                <input type="number" step="0.01" name="rows[`+rowIdx+`][stock]" class="form-control stock-field" value="" readonly>
            </td>
            <td>
                <select name="rows[`+rowIdx+`][item_id]" class="form-control item-select" required>
                    <option value="">Select Item</option>
                    <?php
                    foreach($items as $item) {
                        echo '<option value="'.$item['item_id'].'">'.$item['item_Name'].'</option>';
                    }
                    ?>
                </select>
            </td>
            <td>
                <input type="text" name="rows[`+rowIdx+`][description]" class="form-control" value="">
            </td>
            <td>
                <input type="number" step="1" name="rows[`+rowIdx+`][suits]" class="form-control suits-field" value="" required>
            </td>
            <td>
                <input type="number" step="0.01" name="rows[`+rowIdx+`][mtr_per_suit]" class="form-control mtr-per-suit-field" value="" required>
            </td>
            <td>
                <input type="number" step="0.01" name="rows[`+rowIdx+`][total_mtrs]" class="form-control total-mtrs-field" value="" readonly>
            </td>
            <td class="row-btns">
                <button type="button" class="btn btn-xs btn-danger remove-row"><i class="fa fa-trash"></i></button>
            </td>
        </tr>`;
        $('#row-items-table tbody').append(newRow);
        rowIdx++;
    });

    $(document).on('click', '.remove-row', function(){
        $(this).closest('tr').remove();
    });

    $(document).on('input', '.suits-field, .mtr-per-suit-field', function(){
        var tr = $(this).closest('tr');
        var suits = parseFloat(tr.find('.suits-field').val()) || 0;
        var mtr_per_suit = parseFloat(tr.find('.mtr-per-suit-field').val()) || 0;
        tr.find('.total-mtrs-field').val((suits * mtr_per_suit).toFixed(2));
    });

    $(document).on('change', '.lot-select', function(){
        var tr = $(this).closest('tr');
        var lot_no = $(this).val();
        if(lot_no) {
            $.ajax({
                url: 'ajax/get_embroidery_lot_stock.php',
                type: 'POST',
                data: {lot_no: lot_no},
                success: function(res) {
                    tr.find('.stock-field').val(res);
                }
            });
        } else {
            tr.find('.stock-field').val('');
        }
    });

    if ($('.datepicker').length && typeof $.fn.datepicker === 'function') {
        $('.datepicker').datepicker({ dateFormat: 'dd-mm-yy' });
    }
});
</script>