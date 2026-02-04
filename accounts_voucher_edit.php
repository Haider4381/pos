<?php
include('sessionCheck.php');
include('connection.php');
include('functions.php');

require_once ("inc/init.php");
require_once ("inc/config.ui.php");
$page_title = "Purchase";
include ("inc/header.php");
include ("inc/nav.php");

if (!isset($_SESSION['user_id']) && !isset($_SESSION['u_id'])) {
    die("User not logged in or session expired. Please login again.");
}

$voucher_id = isset($_GET['voucher_id']) ? intval($_GET['voucher_id']) : (isset($_POST['voucher_id']) ? intval($_POST['voucher_id']) : 0);
if (!$voucher_id) {
    die("Voucher ID is required.");
}

$msg = "";

// Fetch voucher header
$q_voucher = mysqli_query($con, "SELECT * FROM accounts_voucher WHERE voucher_id=$voucher_id");
if (!$q_voucher || mysqli_num_rows($q_voucher) == 0) {
    die("Voucher not found.");
}
$voucher = mysqli_fetch_assoc($q_voucher);

// Fetch voucher lines
$q_lines = mysqli_query($con, "SELECT d.*, a.account_title, a.account_code FROM accounts_voucher_detail d LEFT JOIN accounts_chart a ON d.account_id=a.account_id WHERE d.voucher_id=$voucher_id");
$voucher_lines = [];
while($line = mysqli_fetch_assoc($q_lines)){
    $voucher_lines[] = $line;
}

// Handle Save
if(isset($_POST['save_voucher'])) {
    $entry_date    = $_POST['entry_date'];
    $voucher_type  = $_POST['voucher_type'];
    $voucher_no    = mysqli_real_escape_string($con, $_POST['voucher_no']);
    $description   = mysqli_real_escape_string($con, $_POST['description']);
    $lines         = json_decode($_POST['voucher_lines_json'], true);

    $totalDebit = 0; $totalCredit = 0; $validLines = [];
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
        $msg = "<div class='alert alert-danger error-highlight'>No valid voucher lines entered. Nothing was saved.</div>";
    } elseif (abs($totalDebit - $totalCredit) > 0.0001) {
        $msg = "<div class='alert alert-danger error-highlight'>Debit and Credit totals must be equal!</div>";
    } else {
        // Update Voucher header
        $q = "UPDATE accounts_voucher SET entry_date='$entry_date', voucher_type='$voucher_type', voucher_no='$voucher_no', description='$description' WHERE voucher_id=$voucher_id";
        if(mysqli_query($con, $q)) {
            // Remove old lines
            mysqli_query($con, "DELETE FROM accounts_voucher_detail WHERE voucher_id=$voucher_id");
            // Insert new lines
            $lineCount = 0;
            foreach($validLines as $line) {
                $aid = $line['account_id'];
                $debit = $line['debit'];
                $credit = $line['credit'];
                $row_desc = $line['description'];
                $line_sql = "INSERT INTO accounts_voucher_detail (voucher_id, account_id, description, debit, credit) VALUES ($voucher_id, $aid, '$row_desc', $debit, $credit)";
                if(mysqli_query($con, $line_sql)){
                    $lineCount++;
                }
            }
            if($lineCount == 0) {
                $msg = "<div class='alert alert-danger error-highlight'>No voucher details were saved. Nothing was saved.</div>";
            } else {
                $msg = "<div class='alert alert-success'>Voucher has been updated successfully!</div>";
                // Reload voucher and lines for fresh UI
                $q_voucher = mysqli_query($con, "SELECT * FROM accounts_voucher WHERE voucher_id=$voucher_id");
                $voucher = mysqli_fetch_assoc($q_voucher);
                $q_lines = mysqli_query($con, "SELECT d.*, a.account_title, a.account_code FROM accounts_voucher_detail d LEFT JOIN accounts_chart a ON d.account_id=a.account_id WHERE d.voucher_id=$voucher_id");
                $voucher_lines = [];
                while($line = mysqli_fetch_assoc($q_lines)){
                    $voucher_lines[] = $line;
                }
            }
        } else {
            $msg = "<div class='alert alert-danger error-highlight'>Error updating voucher header: ".mysqli_error($con)."</div>";
        }
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Voucher</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body { background: #f6f8fb; }
        .form-section { margin-bottom: 24px; background: #f7fbff; border-radius: 7px; box-shadow: 0 1px 6px #b9d6f5; padding: 18px 10px 6px 10px;}
        .voucher-lines th, .voucher-lines td { vertical-align: middle;}
        .voucher-lines th { background: #2566a0; color: #fff;}
        .btn-remove-line { color: #fff; background: #dc3545; border: none; border-radius: 3px; font-size: 18px; line-height: 1; padding: 2px 10px;}
        .btn-remove-line:hover { background: #a71d2a; }
        .custom-title { font-size:22px; font-weight:600; color:#2566a0; margin-bottom: 12px;}
        .alert { margin-top: 12px; }
        .error-highlight { background: #ffd7d7 !important; color: #b30303 !important; border: 1.5px solid #b30303 !important; font-weight: 700; box-shadow: 0 2px 6px #e4b9b9;}
        .voucher-lines-heading { color: #101010 !important; font-weight: 700; font-size: 18px; margin-bottom: 8px;}
        .table-striped tbody tr:nth-of-type(odd) { background-color: #f8fbff; }
        .table-striped tbody tr:nth-of-type(even) { background-color: #f2f7fd; }
        .btn-sm { padding: 3px 12px; }
        .table-striped { border-radius: 7px; overflow: hidden; }
        .voucher-footer { text-align: right; margin-top: 8px;}
        #save-voucher-btn[disabled] { opacity: 0.6; cursor: not-allowed; }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    // JS array for voucher lines
    var voucherLines = <?php echo json_encode(array_map(function($l){
        return [
            "account_id"=>$l['account_id'],
            "account_text"=>$l['account_title']." ({$l['account_code']})",
            "row_desc"=>$l['description'],
            "debit"=>floatval($l['debit']),
            "credit"=>floatval($l['credit'])
        ];
    }, $voucher_lines)); ?>;

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

        // Validation
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
                <td>
                    <select class="form-control form-control-sm" onchange="changeAccount(${idx}, this.value)">
                        <option value="">--Select--</option>
                        <?php
                        $qacc = mysqli_query($con,"SELECT account_id,account_title,account_code FROM accounts_chart WHERE status='active' ORDER BY account_title");
                        while($racc = mysqli_fetch_assoc($qacc)){
                            $text = $racc['account_title']." ({$racc['account_code']})";
                            echo "<option value='{$racc['account_id']}'>$text</option>";
                        }
                        ?>
                    </select>
                </td>
                <td><input type="text" class="form-control form-control-sm" value="${line.row_desc}" onchange="changeDesc(${idx}, this.value)"></td>
                <td><input type="number" class="form-control form-control-sm" step="0.01" min="0" value="${line.debit}" onchange="changeDebit(${idx}, this.value)"></td>
                <td><input type="number" class="form-control form-control-sm" step="0.01" min="0" value="${line.credit}" onchange="changeCredit(${idx}, this.value)"></td>
                <td><button type="button" class="btn btn-remove-line btn-sm" onclick="removeLine(${idx})"><i class="fa fa-trash"></i></button></td>
            </tr>`;
            tbody.append(tr);
            // Set select value after append
            tbody.find("tr:last select").val(line.account_id);
        });
    }

    function changeAccount(idx, val){
        voucherLines[idx].account_id = val;
        var txt = $("#voucher-lines-table tbody tr").eq(idx).find("select option:selected").text();
        voucherLines[idx].account_text = txt;
        updateTotals();
    }
    function changeDesc(idx, val){
        voucherLines[idx].row_desc = val;
        updateTotals();
    }
    function changeDebit(idx, val){
        voucherLines[idx].debit = parseFloat(val) || 0;
        if(voucherLines[idx].debit > 0) voucherLines[idx].credit = 0;
        renderVoucherLines();
        updateTotals();
    }
    function changeCredit(idx, val){
        voucherLines[idx].credit = parseFloat(val) || 0;
        if(voucherLines[idx].credit > 0) voucherLines[idx].debit = 0;
        renderVoucherLines();
        updateTotals();
    }

    function updateTotals() {
        var totalDebit = 0, totalCredit = 0;
        voucherLines.forEach(function(line){
            totalDebit += parseFloat(line.debit) || 0;
            totalCredit += parseFloat(line.credit) || 0;
        });
        $("#total-debit").text(totalDebit.toFixed(2));
        $("#total-credit").text(totalCredit.toFixed(2));

        if(voucherLines.length > 0 && Math.abs(totalDebit - totalCredit) < 0.0001) {
            $("#save-voucher-btn").prop("disabled", false);
        } else {
            $("#save-voucher-btn").prop("disabled", true);
        }
    }

    $(document).ready(function(){
        renderVoucherLines();
        updateTotals();

        $("#voucher-form").submit(function(e){
            if(voucherLines.length == 0) {
                alert("No voucher lines have been added.");
                e.preventDefault();
                return false;
            }
            var totalDebit = 0, totalCredit = 0;
            voucherLines.forEach(function(line){
                totalDebit += parseFloat(line.debit) || 0;
                totalCredit += parseFloat(line.credit) || 0;
            });
            if(Math.abs(totalDebit - totalCredit) > 0.0001) {
                alert("Debit and Credit totals must be equal.");
                e.preventDefault();
                return false;
            }
            $("#voucher-lines-json").val(JSON.stringify(voucherLines));
        });
    });
    </script>
</head>
<body>
<div class="container py-3" style="margin-top:90px !important;">
    <div class="custom-title">Edit Voucher</div>
    <?php if($msg): echo $msg; endif; ?>

    <!-- Voucher Header -->
    <div class="form-section">
    <form method="post" autocomplete="off" id="voucher-form">
        <input type="hidden" name="voucher_id" value="<?php echo $voucher_id; ?>">
        <div class="row">
            <div class="col-md-2">
                <label>Date</label>
                <input type="date" name="entry_date" class="form-control" required value="<?php echo htmlspecialchars($voucher['entry_date']); ?>">
            </div>
            <div class="col-md-2">
                <label>Type</label>
                <select name="voucher_type" class="form-control" required>
                    <option value="Journal"<?php if($voucher['voucher_type']=='Journal') echo ' selected'; ?>>Journal</option>
                    <option value="Opening"<?php if($voucher['voucher_type']=='Opening') echo ' selected'; ?>>Opening</option>
                </select>
            </div>
            <div class="col-md-2">
                <label>Voucher No</label>
                <input type="text" name="voucher_no" class="form-control" value="<?php echo htmlspecialchars($voucher['voucher_no']); ?>">
            </div>
            <div class="col-md-6">
                <label>Description</label>
                <input type="text" name="description" class="form-control" value="<?php echo htmlspecialchars($voucher['description']); ?>">
            </div>
        </div>

        <!-- Voucher Row Input (add new line) -->
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

        <!-- Voucher Lines Table -->
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
                <!-- JS: Added rows -->
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
            <button type="submit" class="btn btn-primary" name="save_voucher" id="save-voucher-btn" disabled>Save Changes</button>
            <a href="accounts_voucher.php" class="btn btn-secondary">Back</a>
        </div>
    </form>
    </div>
</div>
</body>
</html>