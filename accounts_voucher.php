<?php
include('sessionCheck.php');
include('connection.php');
include('functions.php');

require_once ("inc/init.php");
require_once ("inc/config.ui.php");
$page_title = "Accounts Voucher";
include ("inc/header.php");
include ("inc/nav.php");

$msg = "";
if (!isset($_SESSION['user_id']) && !isset($_SESSION['u_id'])) {
    die("User not logged in or session expired. Please login again.");
}
$created_by = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : $_SESSION['u_id'];

// Only date filter
$from_date = isset($_GET['from_date']) ? $_GET['from_date'] : '';
$to_date = isset($_GET['to_date']) ? $_GET['to_date'] : '';
$where = "WHERE 1 ";
if ($from_date != '') {
    $where .= "AND entry_date >= '" . mysqli_real_escape_string($con, $from_date) . "' ";
}
if ($to_date != '') {
    $where .= "AND entry_date <= '" . mysqli_real_escape_string($con, $to_date) . "' ";
}
// Only show records where voucher_list_display is NOT NULL and NOT 0/empty
$where .= "AND voucher_list_display IS NOT NULL AND voucher_list_display != '' AND voucher_list_display != 0 ";

// Next Voucher No (for Journal)
$qLastNo = mysqli_query($con, "SELECT MAX(CAST(voucher_no AS UNSIGNED)) as last_no FROM accounts_voucher WHERE voucher_type='Journal' AND voucher_no REGEXP '^[0-9]+$'");
$rLastNo = mysqli_fetch_assoc($qLastNo);
$nextVoucherNo = $rLastNo['last_no'] ? ($rLastNo['last_no'] + 1) : 1;

// Save Voucher
if(isset($_POST['save_voucher'])) {
    $entry_date    = $_POST['entry_date'];
    $voucher_type  = $_POST['voucher_type'];
    $voucher_no    = $voucher_type == 'Journal' ? $nextVoucherNo : mysqli_real_escape_string($con, $_POST['voucher_no']);
    $description   = mysqli_real_escape_string($con, $_POST['description']);

    $lines = json_decode($_POST['voucher_lines_json'], true);
    $totalDebit = 0; $totalCredit = 0;
    $validLines = [];
    foreach($lines as $line) {
        $aid = intval($line['account_id']);
        $debit = floatval($line['debit']);
        $credit = floatval($line['credit']);
        $row_desc = mysqli_real_escape_string($con, $line['row_desc']);
        if ($aid && (($debit > 0 && $credit == 0) || ($credit > 0 && $debit == 0))) {
            $validLines[] = [
                'account_id' => $aid,
                'debit' => $debit,
                'credit' => $credit,
                'description' => $row_desc
            ];
            $totalDebit += $debit;
            $totalCredit += $credit;
        }
    }
    if (empty($validLines)) {
        $msg = "<div class='alert alert-danger error-highlight'>No valid voucher lines entered. Voucher was not saved.</div>";
    } elseif (abs($totalDebit - $totalCredit) > 0.0001) {
        $msg = "<div class='alert alert-danger error-highlight'>Debit and Credit totals must be equal!</div>";
    } else {
        $q = "INSERT INTO accounts_voucher (entry_date, voucher_type, voucher_no, description, created_by,voucher_list_display) VALUES ('$entry_date', '$voucher_type', '$voucher_no', '$description', '$created_by',1)";
        if(mysqli_query($con, $q)) {
            $voucher_id = mysqli_insert_id($con);
            $lineCount = 0;
            foreach($validLines as $line) {
                $aid = $line['account_id'];
                $debit = $line['debit'];
                $credit = $line['credit'];
                $row_desc = $line['description'];
                $line_sql = "INSERT INTO accounts_voucher_detail (voucher_id, account_id, description, debit, credit) VALUES ($voucher_id, $aid, '$row_desc', $debit, $credit)";
                if(mysqli_query($con,$line_sql)){
                    $lineCount++;
                }
            }
            if($lineCount == 0) {
                mysqli_query($con, "DELETE FROM accounts_voucher WHERE voucher_id=$voucher_id");
                $msg = "<div class='alert alert-danger error-highlight'>No voucher details were saved. Voucher was not saved.</div>";
            } else {
                $msg = "<div class='alert alert-success'>Voucher has been saved successfully!</div>";
                if ($voucher_type == 'Journal') $nextVoucherNo++;
            }
        } else {
            $msg = "<div class='alert alert-danger error-highlight'>Error saving voucher header: ".mysqli_error($con)."</div>";
        }
    }
}

// Delete Voucher
if(isset($_POST['delete_voucher_id'])) {
    $vid = (int)$_POST['delete_voucher_id'];
    mysqli_query($con, "DELETE FROM accounts_voucher WHERE voucher_id=$vid");
    $msg = "<div class='alert alert-success'>Voucher deleted successfully!</div>";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Voucher Entry</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body { background: #f6f8fb; }
        .form-section { margin-bottom: 24px; background: #f7fbff; border-radius: 7px; box-shadow: 0 1px 6px #b9d6f5; padding: 18px 10px 6px 10px;}
        .voucher-lines-heading { color: #101010 !important; font-weight: 700; font-size: 18px; margin-bottom: 8px;}
        .alert { margin-top: 12px; }
        .error-highlight { background: #ffd7d7 !important; color: #b30303 !important; border: 1.5px solid #b30303 !important; font-weight: 700; box-shadow: 0 2px 6px #e4b9b9;}
        .voucher-footer { text-align: right; margin-top: 8px;}
        #save-voucher-btn[disabled] { opacity: 0.6; cursor: not-allowed; }
        .table-responsive { margin-bottom: 0; }
        .filter-form .form-control { margin-bottom:5px; }
    </style>
</head>
<body>
<div class="container py-3" style="margin-top:90px;">
    <div class="custom-title fs-3 mb-3 fw-bold text-primary">Voucher Entry</div>
    <?php if($msg): echo $msg; endif; ?>

    <!-- Voucher Header -->
    <div class="form-section">
    <form method="post" autocomplete="off" id="voucher-form">
        <div class="row">
            <div class="col-md-2">
                <label>Date</label>
                <input type="date" name="entry_date" class="form-control" required value="<?php echo date('Y-m-d'); ?>">
            </div>
            <div class="col-md-2">
                <label>Type</label>
                <select name="voucher_type" class="form-control" id="voucher-type" required>
                    <option value="Journal">Journal</option>
                    <option value="Opening">Opening</option>
                    <option value="Payment">Payment</option>
                    <option value="Receipt">Receipt</option>
                </select>
            </div>
            <div class="col-md-2">
                <label>Voucher No</label>
                <input type="text" name="voucher_no" class="form-control" id="voucher-no">
            </div>
            <div class="col-md-6">
                <label>Description</label>
                <input type="text" name="description" class="form-control">
            </div>
        </div>
        <div class="row mt-4 mb-2" id="voucher-row-input">
            <div class="col-md-3">
                <select id="input-account" class="form-control">
                    <option value="">--Select Account--</option>
                    <?php
                    $qacc = mysqli_query($con,"SELECT account_id,account_title,account_code FROM accounts_chart WHERE status='active' ORDER BY account_title");
                    while($racc = mysqli_fetch_assoc($qacc)){
                        $text = $racc['account_title']." ({$racc['account_code']})";
                        echo "<option value='{$racc['account_id']}'>$text</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-3">
                <input type="text" id="input-desc" class="form-control" placeholder="Description">
            </div>
            <div class="col-md-2">
                <input type="number" id="input-debit" class="form-control" placeholder="Debit" step="0.01" min="0">
            </div>
            <div class="col-md-2">
                <input type="number" id="input-credit" class="form-control" placeholder="Credit" step="0.01" min="0">
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-info" onclick="addToTable()">Add to Table</button>
            </div>
        </div>
        <div class="table-responsive">
        <table class="table table-bordered table-striped voucher-lines" id="voucher-lines-table">
            <thead>
                <tr>
                    <th>Account</th>
                    <th>Description</th>
                    <th>Debit</th>
                    <th>Credit</th>
                    <th>Remove</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2" class="text-end fw-bold">Total</td>
                    <td id="total-debit" class="fw-bold text-end">0.00</td>
                    <td id="total-credit" class="fw-bold text-end">0.00</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
        </div>
        <input type="hidden" name="voucher_lines_json" id="voucher-lines-json">
        <div class="voucher-footer">
            <button type="submit" class="btn btn-primary" name="save_voucher" id="save-voucher-btn" disabled>Save Voucher</button>
        </div>
    </form>
    </div>

    <!-- Voucher List Filter (Date only) and DataTable -->
    <div class="form-section">
        <div class="voucher-lines-heading">Voucher List</div>
        <form method="get" class="filter-form mb-2 row gx-2 gy-1">
            <div class="col-md-2">
                <input type="date" name="from_date" class="form-control" value="<?php echo htmlspecialchars($from_date); ?>" placeholder="From Date">
            </div>
            <div class="col-md-2">
                <input type="date" name="to_date" class="form-control" value="<?php echo htmlspecialchars($to_date); ?>" placeholder="To Date">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-success">Filter</button>
                <a href="accounts_voucher.php" class="btn btn-secondary">Reset</a>
            </div>
        </form>
        <div class="table-responsive">
        <table class="table table-bordered table-striped" id="voucherListTable">
            <thead>
                <tr>
                    <th>Sr No.</th>
                    <th>Date</th>
                    <th>Type</th>
                    <th>No</th>
                    <th>Description</th>
                    <th>Lines</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $q = mysqli_query($con, "SELECT * FROM accounts_voucher $where ORDER BY entry_date DESC, voucher_id DESC");
            $sr = 1;
            while ($row = mysqli_fetch_assoc($q)):
                $lines = mysqli_num_rows(mysqli_query($con, "SELECT * FROM accounts_voucher_detail WHERE voucher_id=" . $row['voucher_id']));
            ?>
                <tr>
                    <td><?php echo $sr++; ?></td>
                    <td><?php echo $row['entry_date']; ?></td>
                    <td><?php echo $row['voucher_type']; ?></td>
                    <td><?php echo $row['voucher_no']; ?></td>
                    <td><?php echo $row['description']; ?></td>
                    <td><?php echo $lines; ?></td>
                    <td>
                        <a href="accounts_voucher_view.php?voucher_id=<?php echo $row['voucher_id']; ?>" class="btn btn-info btn-sm">View</a>
                        <a href="accounts_voucher_edit.php?voucher_id=<?php echo $row['voucher_id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                        <form method="post" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this voucher?')">
                            <input type="hidden" name="delete_voucher_id" value="<?php echo $row['voucher_id']; ?>">
                            <button class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>
<!-- DataTable JS -->
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.bootstrap5.min.js"></script>
<script>
var voucherLines = [];
function resetInputRow() {
    $("#input-account").val('');
    $("#input-desc").val('');
    $("#input-debit").val('');
    $("#input-credit").val('');
}
function addToTable() {
    var account_id = $("#input-account").val();
    var account_text = $("#input-account option:selected").text();
    var row_desc = $("#input-desc").val();
    var debit = parseFloat($("#input-debit").val()) || 0;
    var credit = parseFloat($("#input-credit").val()) || 0;
    if(!account_id) { alert("Please select an account."); return; }
    if(!row_desc) { alert("Please enter a description."); return; }
    if((debit > 0 && credit > 0) || (debit == 0 && credit == 0)) { alert("Only one side amount is allowed (either debit or credit)."); return; }
    voucherLines.push({
        account_id: account_id,
        account_text: account_text,
        row_desc: row_desc,
        debit: debit,
        credit: credit
    });
    renderVoucherLines();
    resetInputRow();
    updateTotals();
}
function removeLine(idx) {
    voucherLines.splice(idx, 1);
    renderVoucherLines();
    updateTotals();
}
function renderVoucherLines() {
    var tbody = $("#voucher-lines-table tbody");
    tbody.empty();
    voucherLines.forEach(function(line, idx){
        var tr = `<tr>
            <td>${line.account_text}<input type="hidden" name="account_id[]" value="${line.account_id}"></td>
            <td>${line.row_desc}<input type="hidden" name="row_desc[]" value="${line.row_desc}"></td>
            <td>${line.debit.toFixed(2)}<input type="hidden" name="debit[]" value="${line.debit}"></td>
            <td>${line.credit.toFixed(2)}<input type="hidden" name="credit[]" value="${line.credit}"></td>
            <td><button type="button" class="btn btn-remove-line btn-sm" onclick="removeLine(${idx})"><i class="fa fa-trash"></i></button></td>
        </tr>`;
        tbody.append(tr);
    });
}
function updateTotals() {
    var totalDebit = 0, totalCredit = 0;
    voucherLines.forEach(function(line){
        totalDebit += line.debit;
        totalCredit += line.credit;
    });
    $("#total-debit").text(totalDebit.toFixed(2));
    $("#total-credit").text(totalCredit.toFixed(2));
    if(voucherLines.length > 0 && Math.abs(totalDebit - totalCredit) < 0.0001) {
        $("#save-voucher-btn").prop("disabled", false);
    } else {
        $("#save-voucher-btn").prop("disabled", true);
    }
}
function setVoucherNoAuto() {
    var voucherType = $("#voucher-type").val();
    if(voucherType === 'Journal') {
        $("#voucher-no").val("<?php echo $nextVoucherNo; ?>").prop("readonly", true);
    } else {
        $("#voucher-no").val("").prop("readonly", false);
    }
}
$(document).ready(function(){
    setVoucherNoAuto();
    $("#voucher-type").change(setVoucherNoAuto);

    $("#voucher-form").submit(function(e){
        if(voucherLines.length == 0) {
            alert("No voucher lines have been added.");
            e.preventDefault();
            return false;
        }
        var totalDebit = 0, totalCredit = 0;
        voucherLines.forEach(function(line){
            totalDebit += line.debit;
            totalCredit += line.credit;
        });
        if(Math.abs(totalDebit - totalCredit) > 0.0001) {
            alert("Debit and Credit totals must be equal.");
            e.preventDefault();
            return false;
        }
        $("#voucher-lines-json").val(JSON.stringify(voucherLines));
    });

    // DataTables
    $('#voucherListTable').DataTable({
        "paging": true,
        "lengthChange": true,
        "pageLength": 10,
        "ordering": true,
        "order": [[1, 'desc']],
        "info": true,
        "searching": true,
        "autoWidth": false,
        "columnDefs": [
            { "orderable": false, "targets": 6 }
        ]
    });
});
</script>
</body>
</html>