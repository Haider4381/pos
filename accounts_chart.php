<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('sessionCheck.php');
include('connection.php');
include('functions.php');
require_once ("inc/init.php");
require_once ("inc/config.ui.php");

// Edit Load
$page_title = "Chart of Accounts";
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $edit_data = mysqli_fetch_assoc(mysqli_query($con,"SELECT * FROM accounts_chart WHERE account_id=$edit_id"));
}

// For View Page
if (isset($_GET['view'])) {
    $view_id = (int)$_GET['view'];
    $view_data = mysqli_fetch_assoc(mysqli_query($con,"SELECT * FROM accounts_chart WHERE account_id=$view_id"));
    // Parent Title
    $parent_title = '';
    if($view_data && $view_data['parent_id']) {
        $parent_row = mysqli_fetch_assoc(mysqli_query($con,"SELECT account_title FROM accounts_chart WHERE account_id=" . ((int)$view_data['parent_id'])));
        $parent_title = $parent_row ? $parent_row['account_title'] : '';
    }
    include ("inc/header.php");
    include ("inc/nav.php");
    ?>
    <style>
        .account-view-container {
            max-width: 700px;
            margin: 42px auto 0 auto;
        }
        .account-details-card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px #c6d7e5;
            padding: 0;
        }
        .account-details-header {
            background: #2566a0;
            color: #fff;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
            padding: 18px 30px 15px 30px;
            font-size: 1.45rem;
            letter-spacing: 1px;
        }
        .account-details-body {
            padding: 25px 30px 20px 30px;
        }
        .details-table {
            width: 100%;
        }
        .details-table th {
            width: 32%;
            text-align: right;
            color: #2566a0;
            font-weight: 600;
            padding-right: 18px;
            vertical-align: top;
            background: none;
            border: none;
        }
        .details-table td {
            background: none;
            border: none;
            padding-bottom: 11px;
            font-size: 1.08rem;
            color: #333;
        }
        .back-btn {
            margin-top: 18px;
            font-weight: 500;
            letter-spacing: .5px;
        }
        .details-table tr:last-child td { padding-bottom: 0; }
        @media (max-width: 600px) {
            .account-view-container { max-width: 100%; margin-top: 18px;}
            .account-details-header { font-size: 1.1rem; padding:10px 10px;}
            .account-details-body { padding: 10px; }
            .details-table th { font-size: 1rem; padding-right: 6px;}
            .details-table td { font-size: 0.98rem;}
        }
       
    </style>
    <div class="account-view-container">
        <div class="account-details-card">
            <div class="account-details-header" style="margin-top: 88px;">
                <i class="fa fa-user-circle"></i> Account Details
            </div>
            <div class="account-details-body">
                <table class="details-table">
                    <tr>
                        <th>Account Code:</th>
                        <td><?php echo htmlspecialchars($view_data['account_code']); ?></td>
                    </tr>
                    <tr>
                        <th>Account Title:</th>
                        <td><?php echo htmlspecialchars($view_data['account_title']); ?></td>
                    </tr>
                    <tr>
                        <th>Account Type:</th>
                        <td><?php echo htmlspecialchars($view_data['account_type']); ?></td>
                    </tr>
                    <tr>
                        <th>Parent Account:</th>
                        <td><?php echo htmlspecialchars($parent_title); ?></td>
                    </tr>
                    <tr>
                        <th>Opening Debit:</th>
                        <td><?php echo number_format($view_data['opening_debit'], 2); ?></td>
                    </tr>
                    <tr>
                        <th>Opening Credit:</th>
                        <td><?php echo number_format($view_data['opening_credit'], 2); ?></td>
                    </tr>
                    <tr>
                        <th>Phone:</th>
                        <td><?php echo htmlspecialchars($view_data['phone']); ?></td>
                    </tr>
                    <tr>
                        <th>Email:</th>
                        <td><?php echo htmlspecialchars($view_data['email']); ?></td>
                    </tr>
                    <tr>
                        <th>Address:</th>
                        <td><?php echo htmlspecialchars($view_data['address']); ?></td>
                    </tr>
                    <tr>
                        <th>Status:</th>
                        <td>
                            <?php
                            if ($view_data['status'] === "active") {
                                echo '<span class="badge bg-success">Active</span>';
                            } else {
                                echo '<span class="badge bg-danger">Inactive</span>';
                            }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Remarks:</th>
                        <td><?php echo htmlspecialchars($view_data['remarks']); ?></td>
                    </tr>
                    <tr>
                        <th>Description:</th>
                        <td><?php echo htmlspecialchars($view_data['description']); ?></td>
                    </tr>
                </table>
                <a href="accounts_chart.php" class="btn btn-secondary back-btn"><i class="fa fa-arrow-left"></i> Back to List</a>
            </div>
        </div>
    </div>
    <?php
    include ("inc/footer.php");
    exit;
}

// Auto Increment Account Code (when adding new account)
function get_next_account_code($con) {
    $q = mysqli_query($con, "SELECT MAX(CAST(account_code AS UNSIGNED)) as max_code FROM accounts_chart");
    $row = mysqli_fetch_assoc($q);
    return $row['max_code'] ? ($row['max_code'] + 1) : 1;
}

// Helper: Get Opening Balance Equity account_id (must exist!)
function get_opening_equity_id($con) {
    $row = mysqli_fetch_assoc(mysqli_query($con, "
        SELECT account_id FROM accounts_chart
        WHERE LOWER(account_title) LIKE '%opening balance equity%'
           OR LOWER(account_title) LIKE '%opening equity%'
           OR LOWER(account_title) LIKE '%opening balance%'
           OR LOWER(account_title) LIKE '%opening%'
           OR LOWER(account_title) LIKE '%equity%'
        ORDER BY 
          CASE 
            WHEN LOWER(account_title) LIKE '%opening balance equity%' THEN 1
            WHEN LOWER(account_title) LIKE '%opening equity%' THEN 2
            WHEN LOWER(account_title) LIKE '%opening balance%' THEN 3
            ELSE 4
          END
        LIMIT 1
    "));
    return $row ? $row['account_id'] : 0;
}

// Handle Add/Edit Account (process BEFORE any output)
if (isset($_POST['save_account'])) {
    $account_title = mysqli_real_escape_string($con, $_POST['account_title']);
    if (isset($_POST['edit_id']) && $_POST['edit_id'] != '') {
        $account_code = mysqli_real_escape_string($con, $_POST['account_code']);
    } else {
        $account_code = get_next_account_code($con);
    }
    $parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : NULL;
    $account_type = mysqli_real_escape_string($con, $_POST['account_type']);
    $opening_debit = floatval($_POST['opening_debit']);
    $opening_credit = floatval($_POST['opening_credit']);
    $phone = mysqli_real_escape_string($con, $_POST['phone']);
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $address = mysqli_real_escape_string($con, $_POST['address']);
    $remarks = mysqli_real_escape_string($con, $_POST['remarks']);
    $status = mysqli_real_escape_string($con, $_POST['status']);
    $description = isset($_POST['description']) ? mysqli_real_escape_string($con, $_POST['description']) : '';
    $u_id = $_SESSION['u_id'];

    // --- Check for Opening Balance Equity account BEFORE adding opening voucher ---
    $need_opening = ($opening_debit > 0 || $opening_credit > 0);
    $opeq_id = get_opening_equity_id($con);

    

    if ($need_opening && $opeq_id == 0) {
        $_SESSION['msg'] = "<div class='alert alert-danger custom-alert' role='alert'><strong>Error:</strong> <b>Opening Balance Equity</b> or any similar account was not found in your Chart of Accounts.<br>
            Please create an <b>Opening Balance Equity</b> account (of type Equity) first, then try adding an account with an opening balance.</div>";
        header("Location: accounts_chart.php");
        exit;
    }
?>
<style>
/* Professional Custom Alert Styling */
.custom-alert {
    font-size: 1.12rem;
    border-radius: 7px;
    box-shadow: 0 1px 8px #e6eaf3;
    background: #fff;
    border: 1.5px solid #dee2e6;
    color: #842029 !important;
    padding: 1.15rem 1.5rem;
    margin-top: 18px;
}
.alert-danger.custom-alert {
    border-color: #f5c2c7 !important;
    background: #fff5f4 !important;
}
</style>

<?php
    // For edit, remove old opening balance voucher if opening amounts updated
    if (isset($_POST['edit_id']) && $_POST['edit_id'] != '') {
        $edit_id = (int)$_POST['edit_id'];
        $old = mysqli_fetch_assoc(mysqli_query($con, "SELECT opening_debit, opening_credit FROM accounts_chart WHERE account_id=$edit_id"));
        $q = "UPDATE accounts_chart SET account_title='$account_title', account_code='$account_code', parent_id=".($parent_id ? $parent_id : 'NULL').", account_type='$account_type', opening_debit='$opening_debit', opening_credit='$opening_credit', phone='$phone', email='$email', address='$address', remarks='$remarks', status='$status', description='$description' WHERE account_id=$edit_id";
        mysqli_query($con, $q);

        // Remove old opening voucher if any
        $old_voucher = mysqli_fetch_assoc(mysqli_query($con, "SELECT voucher_id FROM accounts_voucher WHERE voucher_no='OPEN-$edit_id'"));
        if ($old_voucher) {
            mysqli_query($con, "DELETE FROM accounts_voucher_detail WHERE voucher_id=".$old_voucher['voucher_id']);
            mysqli_query($con, "DELETE FROM accounts_voucher WHERE voucher_id=".$old_voucher['voucher_id']);
        }

        // Add new voucher if opening balance entered
        if ($need_opening) {
            $today = date('Y-m-d');
            $voucher_type = 'Opening Balance';
            $voucher_no = 'OPEN-'.$edit_id;
            $desc = 'Opening Balance for account '.$account_title;
            mysqli_query($con, "INSERT INTO accounts_voucher (entry_date, voucher_type, voucher_no, description, created_by) VALUES ('$today', '$voucher_type', '$voucher_no', '$desc', '$u_id')");
            $voucher_id = mysqli_insert_id($con);

            if ($opening_debit > 0) {
                mysqli_query($con, "INSERT INTO accounts_voucher_detail (voucher_id, account_id, description, debit, credit) VALUES ($voucher_id, $edit_id, '$desc', $opening_debit, 0)");
                mysqli_query($con, "INSERT INTO accounts_voucher_detail (voucher_id, account_id, description, debit, credit) VALUES ($voucher_id, $opeq_id, '$desc', 0, $opening_debit)");
            }
            if ($opening_credit > 0) {
                mysqli_query($con, "INSERT INTO accounts_voucher_detail (voucher_id, account_id, description, debit, credit) VALUES ($voucher_id, $edit_id, '$desc', 0, $opening_credit)");
                mysqli_query($con, "INSERT INTO accounts_voucher_detail (voucher_id, account_id, description, debit, credit) VALUES ($voucher_id, $opeq_id, '$desc', $opening_credit, 0)");
            }
        }

        $msg = "Account updated!";
    } else {
        // Insert account first
        $q = "INSERT INTO accounts_chart (account_title, account_code, parent_id, account_type, opening_debit, opening_credit, phone, email, address, remarks, status, description) VALUES ('$account_title', '$account_code', ".($parent_id ? $parent_id : 'NULL').", '$account_type', '$opening_debit', '$opening_credit', '$phone', '$email', '$address', '$remarks', '$status', '$description')";
        mysqli_query($con, $q);
        $new_account_id = mysqli_insert_id($con);

        // Add opening balance voucher if opening balance entered
        if ($need_opening) {
            $today = date('Y-m-d');
            $voucher_type = 'Opening Balance';
            $voucher_no = 'OPEN-'.$new_account_id;
            $desc = 'Opening Balance for account '.$account_title;
            mysqli_query($con, "INSERT INTO accounts_voucher (entry_date, voucher_type, voucher_no, description, created_by) VALUES ('$today', '$voucher_type', '$voucher_no', '$desc', '$u_id')");
            $voucher_id = mysqli_insert_id($con);

            if ($opening_debit > 0) {
                mysqli_query($con, "INSERT INTO accounts_voucher_detail (voucher_id, account_id, description, debit, credit) VALUES ($voucher_id, $new_account_id, '$desc', $opening_debit, 0)");
                mysqli_query($con, "INSERT INTO accounts_voucher_detail (voucher_id, account_id, description, debit, credit) VALUES ($voucher_id, $opeq_id, '$desc', 0, $opening_debit)");
            }
            if ($opening_credit > 0) {
                mysqli_query($con, "INSERT INTO accounts_voucher_detail (voucher_id, account_id, description, debit, credit) VALUES ($voucher_id, $new_account_id, '$desc', 0, $opening_credit)");
                mysqli_query($con, "INSERT INTO accounts_voucher_detail (voucher_id, account_id, description, debit, credit) VALUES ($voucher_id, $opeq_id, '$desc', $opening_credit, 0)");
            }
        }
        $msg = "Account created!";
    }
    $_SESSION['msg'] = $msg;
    header("Location: accounts_chart.php");
    exit;
}

// Delete (process BEFORE any output)
if (isset($_POST['delete_id'])) {
    $delete_id = (int)$_POST['delete_id'];
    $used_check = mysqli_query($con, "SELECT COUNT(*) as cnt FROM accounts_voucher_detail WHERE account_id=$delete_id");
    $used_row = mysqli_fetch_assoc($used_check);
    if ($used_row['cnt'] > 0) {
        $msg = "Cannot delete: This account is being used in vouchers/transactions!";
    } else {
        // Remove opening balance voucher if any
        $old_voucher = mysqli_fetch_assoc(mysqli_query($con, "SELECT voucher_id FROM accounts_voucher WHERE voucher_no='OPEN-$delete_id'"));
        if ($old_voucher) {
            mysqli_query($con, "DELETE FROM accounts_voucher_detail WHERE voucher_id=".$old_voucher['voucher_id']);
            mysqli_query($con, "DELETE FROM accounts_voucher WHERE voucher_id=".$old_voucher['voucher_id']);
        }
        mysqli_query($con,"DELETE FROM accounts_chart WHERE account_id=$delete_id");
        $msg = "Account deleted!";
    }
    $_SESSION['msg'] = $msg;
    header("Location: accounts_chart.php");
    exit;
}

// Parent Account options (optional, user can leave blank for simple structure)
function account_options($con, $selected = 0) {
    $sql = "SELECT account_id, account_title, account_code FROM accounts_chart ORDER BY account_title";
    $rs = mysqli_query($con, $sql);
    $s = '';
    while($row = mysqli_fetch_assoc($rs)) {
        $sel = ($selected == $row['account_id']) ? 'selected' : '';
        $s .= "<option value='{$row['account_id']}' $sel>{$row['account_title']} ({$row['account_code']})</option>";
    }
    return $s;
}

// ----------- HTML Output Start -----------
include ("inc/header.php");
include ("inc/nav.php");
?>
<div class="container" style="margin-top:40px;">
    <section id="widget-grid" class="">
        <div class="row">
            <article class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <div class="jarviswidget jarviswidget-color-purity" id="wid-id-1" data-widget-editbutton="false" style="margin-top:48px;">
                    <header>
                        <span class="small_icon"><i class="fa fa-circle-o-notch"></i></span>
                        <h2><?php echo isset($edit_data) ? 'Edit Account' : 'Create New Account'; ?></h2>
                    </header>
                    <?php if(isset($_SESSION['msg'])): ?>
                        <?php echo $_SESSION['msg']; unset($_SESSION['msg']); ?>
                    <?php endif; ?>

                    <div class="form-container mb-4">
                        <form method="post" action="">
                            <?php if(isset($edit_data)): ?>
                                <input type="hidden" name="edit_id" value="<?php echo $edit_data['account_id']; ?>">
                            <?php endif; ?>

                            <!-- First Row: Main account info -->
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Account Code:</label>
                                        <input type="text" class="form-control" name="account_code" value="<?php echo isset($edit_data) ? htmlspecialchars($edit_data['account_code']) : (get_next_account_code($con)); ?>" <?php echo isset($edit_data) ? '' : 'readonly'; ?> required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Account Title:</label>
                                        <input type="text" class="form-control" name="account_title" value="<?php echo isset($edit_data) ? htmlspecialchars($edit_data['account_title']) : ''; ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Account Type:</label>
                                        <select name="account_type" class="form-control" required>
                                            <option <?php if(@$edit_data['account_type']=='Asset') echo 'selected'; ?>>Asset</option>
                                            <option <?php if(@$edit_data['account_type']=='Liability') echo 'selected'; ?>>Liability</option>
                                            <option <?php if(@$edit_data['account_type']=='Equity') echo 'selected'; ?>>Equity</option>
                                            <option <?php if(@$edit_data['account_type']=='Income') echo 'selected'; ?>>Income</option>
                                            <option <?php if(@$edit_data['account_type']=='Expense') echo 'selected'; ?>>Expense</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Parent Account: <span style="font-weight:400;color:#888">(optional)</span></label>
                                        <select name="parent_id" class="form-control">
                                            <option value="">--None--</option>
                                            <?php echo account_options($con, @$edit_data['parent_id']); ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Second Row: Opening balances -->
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Opening Debit</label>
                                        <input type="number" step="0.01" name="opening_debit" class="form-control" value="<?php echo @$edit_data['opening_debit'] ?: 0; ?>">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Opening Credit</label>
                                        <input type="number" step="0.01" name="opening_credit" class="form-control" value="<?php echo @$edit_data['opening_credit'] ?: 0; ?>">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Phone:</label>
                                        <input type="text" class="form-control" name="phone" value="<?php echo isset($edit_data) ? htmlspecialchars($edit_data['phone']) : ''; ?>">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Email:</label>
                                        <input type="email" class="form-control" name="email" value="<?php echo isset($edit_data) ? htmlspecialchars($edit_data['email']) : ''; ?>">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Status:</label>
                                        <select name="status" class="form-control">
                                            <option value="active" <?php if(@$edit_data['status']=='active') echo 'selected'; ?>>Active</option>
                                            <option value="inactive" <?php if(@$edit_data['status']=='inactive') echo 'selected'; ?>>Inactive</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Third Row: Contact/Address -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Address:</label>
                                        <textarea class="form-control" name="address"><?php echo isset($edit_data) ? htmlspecialchars($edit_data['address']) : ''; ?></textarea>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Remarks:</label>
                                        <textarea class="form-control" name="remarks"><?php echo isset($edit_data) ? htmlspecialchars($edit_data['remarks']) : ''; ?></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Optional Description Field -->
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Description: <span style="font-weight:400;color:#888">(optional)</span></label>
                                        <textarea class="form-control" name="description" style="height: 100px;"><?php echo isset($edit_data['description']) ? htmlspecialchars($edit_data['description']) : ''; ?></textarea>
                                    </div>
                                </div>
                            </div>

                            <button class="btn btn-primary" name="save_account" style="margin-bottom:20px;"><?php echo isset($edit_data) ? 'Update' : 'Add'; ?></button>
                            <?php if(isset($edit_data)): ?>
                                <a href="accounts_chart.php" class="btn btn-secondary">Cancel</a>
                            <?php endif; ?>
                        </form>
                    </div>
                    <!-- Table Section -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="accountsTable">
                            <thead>
                                <tr>
                                    <th>Sr No.</th>
                                    <th>Code</th>
                                    <th>Account Name</th>
                                    <th>Type</th>
                                    <th>Op. DR</th>
                                    <th>Op. CR</th>
                                    <th>Phone</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $q = mysqli_query($con,"SELECT c.*,p.account_title as parent_title FROM accounts_chart c LEFT JOIN accounts_chart p ON c.parent_id = p.account_id ORDER BY c.account_type, c.account_title");
                                $srno = 1;
                                while($row=mysqli_fetch_assoc($q)):
                                ?>
                                <tr>
                                    <td><?php echo $srno++; ?></td>
                                    <td><?php echo htmlspecialchars($row['account_code']); ?></td>
                                    <td><?php echo htmlspecialchars($row['account_title']); ?></td>
                                    <td><?php echo htmlspecialchars($row['account_type']); ?></td>
                                    <td><?php echo htmlspecialchars($row['opening_debit']); ?></td>
                                    <td><?php echo htmlspecialchars($row['opening_credit']); ?></td>
                                    <td><?php echo htmlspecialchars($row['phone']); ?></td>
                                    <td><?php echo htmlspecialchars($row['status']); ?></td>
                                    <td>
                                        <a href="?view=<?php echo $row['account_id']; ?>" class="btn btn-success btn-sm">View</a>
                                        <a href="?edit=<?php echo $row['account_id']; ?>" class="btn btn-info btn-sm">Edit</a>
                                        <form method="post" style="display:inline;" onsubmit="return confirm('Delete this account?')">
                                            <input type="hidden" name="delete_id" value="<?php echo $row['account_id']; ?>">
                                            <button class="btn btn-danger btn-sm">Del</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div><!-- jarviswidget -->
            </article>
        </div>
    </section>
</div>
<!-- DataTables -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs5/dt-2.0.3/datatables.min.css"/>
<script type="text/javascript" src="https://cdn.datatables.net/v/bs5/dt-2.0.3/datatables.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var table = new DataTable('#accountsTable', {
            responsive: true,
            searching: true,
            paging: true,
            ordering: true,
            info: true,
            lengthMenu: [10, 25, 50, 100],
            language: {search: "Filter:"}
        });
    });
</script>
<div style="margin-top:52px;"></div>
<?php include ("inc/footer.php"); ?>