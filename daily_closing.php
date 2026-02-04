<?php
include('sessionCheck.php');
include('connection.php');
include('functions.php');

require_once("inc/init.php");
require_once("inc/config.ui.php");
$page_title = "Daily Closing";
include("inc/header.php");
include("inc/nav.php");

date_default_timezone_set('Asia/Karachi');

// Helper: Get Account IDs by title (must match accounts_chart titles)
function getAccountId($title) {
    global $con;
    $q = mysqli_query($con, "SELECT account_id FROM accounts_chart WHERE account_title='$title' LIMIT 1");
    if ($r = mysqli_fetch_assoc($q)) return $r['account_id'];
    return 0;
}

// Set your account titles here, exactly as in accounts_chart table
$sale_account             = getAccountId('Sales');
$cash_account             = getAccountId('Cash Account');
$purchase_account         = getAccountId('Purchase Account');
$sale_return_account      = getAccountId('Sale Return account');
$purchase_return_account  = getAccountId('Purchase Return');
$expense_account          = getAccountId('Expense');
$bank_account             = getAccountId('Bank Account');

$report_date = isset($_POST['report_date']) ? $_POST['report_date'] : date('Y-m-d');

// Opening Balances
$q_opening_cash = mysqli_query($con, "SELECT SUM(debit) as opening_cash FROM accounts_voucher_detail d INNER JOIN accounts_voucher v ON d.voucher_id=v.voucher_id WHERE d.account_id='$cash_account' AND v.entry_date='$report_date' AND v.voucher_type='Opening'");
$opening_cash = mysqli_fetch_assoc($q_opening_cash)['opening_cash'] ?? 0;

$q_opening_bank = mysqli_query($con, "SELECT SUM(debit) as opening_bank FROM accounts_voucher_detail d INNER JOIN accounts_voucher v ON d.voucher_id=v.voucher_id WHERE d.account_id='$bank_account' AND v.entry_date='$report_date' AND v.voucher_type='Opening'");
$opening_bank = mysqli_fetch_assoc($q_opening_bank)['opening_bank'] ?? 0;

// Sales
$q_total_sale = mysqli_query($con, "SELECT SUM(credit) as total_sale FROM accounts_voucher_detail d INNER JOIN accounts_voucher v ON d.voucher_id=v.voucher_id WHERE d.account_id='$sale_account' AND v.entry_date='$report_date'");
$total_sale = mysqli_fetch_assoc($q_total_sale)['total_sale'] ?? 0;

// Cash Sale: cash received from customers (Payment voucher, Cash Account, debit)
$q_cash_sale = mysqli_query($con,"
    SELECT SUM(d.debit) as cash_sale
    FROM accounts_voucher_detail d
    INNER JOIN accounts_voucher v ON d.voucher_id=v.voucher_id
    WHERE d.account_id='$cash_account'
    AND v.entry_date='$report_date'
    AND v.voucher_type='Payment'
");
$cash_sale = mysqli_fetch_assoc($q_cash_sale)['cash_sale'] ?? 0;

// Credit Sale (Outstanding)
$credit_sale = $total_sale - $cash_sale;

// Purchases
$q_total_purchase = mysqli_query($con, "SELECT SUM(debit) as total_purchase FROM accounts_voucher_detail d INNER JOIN accounts_voucher v ON d.voucher_id=v.voucher_id WHERE d.account_id='$purchase_account' AND v.entry_date='$report_date'");
$total_purchase = mysqli_fetch_assoc($q_total_purchase)['total_purchase'] ?? 0;

// Cash Purchase: cash paid to suppliers (Payment voucher, Cash Account, credit)
$q_cash_purchase = mysqli_query($con, "
    SELECT SUM(d.credit) as cash_purchase
    FROM accounts_voucher_detail d
    INNER JOIN accounts_voucher v ON d.voucher_id=v.voucher_id
    WHERE d.account_id='$cash_account'
    AND v.entry_date='$report_date'
    AND v.voucher_type='Payment'
");
$cash_purchase = mysqli_fetch_assoc($q_cash_purchase)['cash_purchase'] ?? 0;

// Credit Purchase (Outstanding)
$credit_purchase = $total_purchase - $cash_purchase;

// Sale Returns
$q_sale_return = mysqli_query($con, "SELECT SUM(debit) as sale_return FROM accounts_voucher_detail d INNER JOIN accounts_voucher v ON d.voucher_id=v.voucher_id WHERE d.account_id='$sale_return_account' AND v.entry_date='$report_date'");
$sale_return = mysqli_fetch_assoc($q_sale_return)['sale_return'] ?? 0;

// Cash Sale Return: if you issue cash against sale returns (Receipt voucher, Cash Account, credit)
$q_cash_sale_return = mysqli_query($con, "
    SELECT SUM(d.credit) as cash_sale_return
    FROM accounts_voucher_detail d
    INNER JOIN accounts_voucher v ON d.voucher_id=v.voucher_id
    WHERE d.account_id='$cash_account'
    AND v.entry_date='$report_date'
    AND v.voucher_type='Receipt'
");
$cash_sale_return = mysqli_fetch_assoc($q_cash_sale_return)['cash_sale_return'] ?? 0;

$credit_sale_return = $sale_return - $cash_sale_return;

// Collections/Receipts
$q_collections = mysqli_query($con, "SELECT SUM(d.debit) as collections FROM accounts_voucher_detail d INNER JOIN accounts_voucher v ON d.voucher_id=v.voucher_id WHERE d.account_id='$cash_account' AND v.voucher_type='Receipt' AND v.entry_date='$report_date'");
$total_collections = mysqli_fetch_assoc($q_collections)['collections'] ?? 0;

// Expenses/Payments
$q_expenses = mysqli_query($con, "SELECT SUM(d.debit) as expenses FROM accounts_voucher_detail d INNER JOIN accounts_voucher v ON d.voucher_id=v.voucher_id WHERE d.account_id='$expense_account' AND v.entry_date='$report_date'");
$total_expenses = mysqli_fetch_assoc($q_expenses)['expenses'] ?? 0;

$q_payments = mysqli_query($con, "SELECT SUM(d.credit) as payments FROM accounts_voucher_detail d INNER JOIN accounts_voucher v ON d.voucher_id=v.voucher_id WHERE d.account_id='$cash_account' AND v.voucher_type='Payment' AND v.entry_date='$report_date'");
$total_payments = mysqli_fetch_assoc($q_payments)['payments'] ?? 0;

// Sale Return, Credit Purchase, Purchase Return (for pending dues)
$q_purchase_return = mysqli_query($con, "SELECT SUM(debit) as purchase_return FROM accounts_voucher_detail d INNER JOIN accounts_voucher v ON d.voucher_id=v.voucher_id WHERE d.account_id='$purchase_return_account' AND v.entry_date='$report_date'");
$purchase_return = mysqli_fetch_assoc($q_purchase_return)['purchase_return'] ?? 0;

// Cash Purchase Return: if you receive cash for purchase returns (Receipt voucher, Cash Account, debit)
$q_cash_purchase_return = mysqli_query($con, "
    SELECT SUM(d.debit) as cash_purchase_return
    FROM accounts_voucher_detail d
    INNER JOIN accounts_voucher v ON d.voucher_id=v.voucher_id
    WHERE d.account_id='$cash_account'
    AND v.entry_date='$report_date'
    AND v.voucher_type='Receipt'
");
$cash_purchase_return = mysqli_fetch_assoc($q_cash_purchase_return)['cash_purchase_return'] ?? 0;

$credit_purchase_return = $purchase_return - $cash_purchase_return;

// Bank Receive (Receipts in bank)
$q_bank_receive = mysqli_query($con, "SELECT SUM(d.debit) as bank_receive FROM accounts_voucher_detail d INNER JOIN accounts_voucher v ON d.voucher_id=v.voucher_id WHERE d.account_id='$bank_account' AND v.voucher_type='Receipt' AND v.entry_date='$report_date'");
$bank_receive = mysqli_fetch_assoc($q_bank_receive)['bank_receive'] ?? 0;

// Closing Calculations
// Sum of all cash transactions (inflows - outflows)
$cash_in = $opening_cash + $cash_sale + $total_collections + $cash_purchase_return;
$cash_out = $cash_purchase + $cash_sale_return + $total_payments + $total_expenses;
$total_cash_flow = $cash_in - $cash_out;

// Net Profit/Loss (Sales + Sale Returns + Purchase Returns - Purchases - Expenses)
$income_side = $total_sale + $sale_return + $purchase_return;
$expense_side = $total_purchase + $total_expenses;
$net_profit = $income_side - $expense_side;

// System calculated closing cash
$system_closing_cash = $cash_in - $cash_out;

// User entered closing cash
$user_closing_cash = "";
$difference = "";
if (isset($_POST['closing_cash'])) {
    $user_closing_cash = floatval($_POST['closing_cash']);
    $difference = $user_closing_cash - $system_closing_cash;
}

// Remarks logic
$remarks = [];
if ($sale_return > 0 && $cash_sale_return == 0) {
    $remarks[] = "High sale returns (" . number_format($sale_return,2) . ") with no cash refunds → Check if this is correct.";
}
if ($total_sale == 0 && $total_purchase == 0) {
    $remarks[] = "No sales/purchases recorded → Verify if data was missed.";
}

// Prepare date for heading
$heading_date = date('d-M-Y', strtotime($report_date));
?>
<!DOCTYPE html>
<html>
<head>
    <title>Daily Khata / Closing Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f6f8fb; font-size: 1.10rem; }
        .khata-section { margin-bottom: 30px; background: #f7fbff; border-radius: 7px; box-shadow: 0 1px 6px #b9d6f5; padding: 18px; }
        .khata-table th, .khata-table td { vertical-align: middle; }
        .khata-table th { background: #2566a0; color: #fff; }
        .table-primary td { background: #e3f0ff !important; }
        .table-info td { background: #cbf4fc !important; }
        .table-danger td { background: #ffe1e1 !important; }
        .table-warning td { background: #fff8dc !important; }
        .summary-card { background:#e9f3ff; border-radius:10px; box-shadow:0 2px 8px #c3d6f6; padding:14px 18px; margin-bottom:16px;}
        .summary-value { font-size: 1.25rem; font-weight: bold; }
        .summary-label { font-size: 1.10rem; color: #2566a0;}
        .diff-short { color: #dc3545; font-weight: bold;}
        .diff-excess { color: #18a33a; font-weight: bold;}
        .alert { margin-top: 10px;}
        .totals-row { background: #2566a0; color: #fff; font-weight: bold;}
        @media print {
            .no-print, .no-print * { display: none !important; }
        }
        .category-title { font-weight: bold; }
        .section-header { font-size: 1.15rem; color: #2566a0; font-weight: bold; margin-bottom: 6px;}
        .closing-summary-label { font-weight: 500; }
        .closing-summary-value { font-weight: 700; font-size: 1.25rem;}
        .big-amount { font-size: 1.22rem !important; font-weight: bold; }
        .table td, .table th { font-size: 1.10rem !important; }
    </style>
</head>
<body>
<div class="container py-3" style="margin-top:90px;">
    <h2 class="mb-4 text-primary">Daily Khata / Closing Report (<?php echo $heading_date; ?>)</h2>
    <form method="post" class="mb-3 row no-print">
        <div class="col-md-2">
            <label><b>Date:</b></label>
            <input type="date" name="report_date" class="form-control" value="<?php echo htmlspecialchars($report_date); ?>" required>
        </div>
        <div class="col-md-2">
            <label><b>Enter Closing Cash in Hand:</b></label>
            <input type="number" step="0.01" min="0" name="closing_cash" class="form-control" value="<?php echo isset($_POST['closing_cash']) ? htmlspecialchars($_POST['closing_cash']) : ""; ?>" required>
        </div>
        <div class="col-md-2 align-self-end">
            <button class="btn btn-primary" type="submit">Show & Close</button>
            <button type="button" onclick="window.print()" class="btn btn-secondary ms-2">Print</button>
        </div>
    </form>
    <div class="row">
        <div class="col-md-4">
            <!-- 1. Opening Balance -->
            <div class="section-header">1. Opening Balance</div>
            <div class="mb-2 ms-2">
                <div>Cash in Hand: <span class="big-amount"><?php echo number_format($opening_cash,2); ?></span></div>
                <div>Bank Balance: <span class="big-amount"><?php echo number_format($opening_bank,2); ?></span></div>
            </div>
        </div>
        <div class="col-md-8">
            <!-- 2. Daily Transactions -->
            <div class="section-header">2. Daily Transactions</div>
            <div class="table-responsive">
                <table class="table table-bordered khata-table mb-2">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Cash</th>
                            <th>Credit</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Sales -->
                        <tr>
                            <td class="category-title">Sales</td>
                            <td><span class="big-amount"><?php echo number_format($cash_sale,2); ?></span></td>
                            <td><span class="big-amount"><?php echo number_format($credit_sale,2); ?></span></td>
                            <td><span class="big-amount"><?php echo number_format($total_sale,2); ?></span></td>
                        </tr>
                        <!-- Purchases -->
                        <tr>
                            <td class="category-title">Purchases</td>
                            <td><span class="big-amount"><?php echo number_format($cash_purchase,2); ?></span></td>
                            <td><span class="big-amount"><?php echo number_format($credit_purchase,2); ?></span></td>
                            <td><span class="big-amount"><?php echo number_format($total_purchase,2); ?></span></td>
                        </tr>
                        <!-- Sale Returns -->
                        <tr>
                            <td class="category-title">Sale Returns</td>
                            <td><span class="big-amount"><?php echo number_format($cash_sale_return,2); ?></span></td>
                            <td><span class="big-amount"><?php echo number_format($credit_sale_return,2); ?></span></td>
                            <td><span class="big-amount"><?php echo number_format($sale_return,2); ?></span></td>
                        </tr>
                        <!-- Collections (Receipts) -->
                        <tr>
                            <td class="category-title">Collections (Receipts)</td>
                            <td><span class="big-amount"><?php echo number_format($total_collections,2); ?></span></td>
                            <td>–</td>
                            <td><span class="big-amount"><?php echo number_format($total_collections,2); ?></span></td>
                        </tr>
                        <!-- Payments (Expenses) -->
                        <tr>
                            <td class="category-title">Payments (Expenses)</td>
                            <td><span class="big-amount"><?php echo number_format($total_payments + $total_expenses,2); ?></span></td>
                            <td>–</td>
                            <td><span class="big-amount"><?php echo number_format($total_payments + $total_expenses,2); ?></span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- 3. Closing Summary -->
    <div class="row mt-3">
        <div class="col-md-6">
            <div class="section-header">3. Closing Summary</div>
            <div class="mb-2 ms-2">
                <div><span class="closing-summary-label">Total Cash Flow:</span> <span class="closing-summary-value"><?php echo number_format($total_cash_flow,2); ?></span></div>
                <div><span class="closing-summary-label">Net Profit/Loss:</span> <span class="closing-summary-value"><?php echo number_format($net_profit,2); ?></span></div>
                <div><span class="closing-summary-label">Cash in Hand (Closing):</span> <span class="closing-summary-value"><?php echo number_format($system_closing_cash,2); ?></span></div>
                <div><span class="closing-summary-label">Pending Dues:</span>
                    <ul class="mb-0 ms-3">
                        <li>Credit Sales: <span class="big-amount"><?php echo number_format($credit_sale,2); ?></span></li>
                        <li>Credit Purchases: <span class="big-amount"><?php echo number_format($credit_purchase,2); ?></span></li>
                    </ul>
                </div>
                <?php if (isset($_POST['closing_cash'])): ?>
                <div><span class="closing-summary-label">Cash in Hand (User Entry):</span> <span class="closing-summary-value"><?php echo number_format($user_closing_cash,2); ?></span></div>
                <div><span class="closing-summary-label">Difference:</span>
                    <?php if (abs($difference) < 0.01): ?>
                        <span class="text-success">No Difference</span>
                    <?php elseif ($difference < 0): ?>
                        <span class="diff-short">Short by <?php echo number_format(abs($difference), 2); ?></span>
                    <?php else: ?>
                        <span class="diff-excess">Excess by <?php echo number_format($difference, 2); ?></span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <!-- 4. Remarks -->
        <div class="col-md-6">
            <div class="section-header">4. Remarks</div>
            <div class="mb-2 ms-2">
                <?php
                if (empty($remarks)) {
                    echo "<div>- No remarks.</div>";
                } else {
                    echo "<ul class='mb-0'>";
                    foreach ($remarks as $rem) echo "<li>$rem</li>";
                    echo "</ul>";
                }
                ?>
            </div>
        </div>
    </div>
</div>
</body>
</html>