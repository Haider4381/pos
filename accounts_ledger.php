<?php
include('connection.php');
include('sessionCheck.php');

$account_id = isset($_GET['account_id']) ? intval($_GET['account_id']) : 0;
$from_date = isset($_GET['from_date']) ? $_GET['from_date'] : '';
$to_date = isset($_GET['to_date']) ? $_GET['to_date'] : '';

// Date parsing and format for SQL
function sql_date($date) {
    return date('Y-m-d', strtotime(str_replace('/', '-', $date)));
}

// ENUM Voucher Types for display
$allowed_types = [
    'Journal' => 'Journal',
    'Payment' => 'Payment',
    'Receipt' => 'Receipt',
    'Sale' => 'Sale',
    'Purchase' => 'Purchase',
    'Sale Return' => 'Sale Return',
    'Purchase Return' => 'Purchase Return',
    'Opening' => 'Opening'
];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Account Ledger / اکاؤنٹ لیجر</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .urdu { font-family: 'Noto Nastaliq Urdu', 'Jameel Noori Nastaleeq', serif; font-size: 15px; }
        .total-row { background: #f4f8ff; font-weight: bold; }
        .opening-row, .closing-row { background: #e6f7e6; font-weight: bold; }
        .table th, .table td { vertical-align: middle !important; }
        .form-inline label { font-weight:bold; }
        .text-urdu-label { font-size:13px; color:#333; }
        .datepicker { min-width:120px; }
        .srno-col { width: 60px; text-align: center; }
        .back-btn { margin-bottom: 1.5rem; }
    </style>
</head>
<body>
<div class="container mt-4">

    <!-- Back Button (Top) -->
    <a href="javascript:history.back()" class="btn btn-secondary back-btn">
        &larr; Back
    </a>

    <h3 class="mb-3">Account Ledger / <span class="urdu">اکاؤنٹ لیجر</span></h3>
    <form method="get" class="form-inline mb-3">
        <label>
            Select Account
           
        </label>
        <select name="account_id" class="form-control mx-2" required>
            <option value="">--Select--</option>
            <?php
            $qacc = mysqli_query($con,"SELECT account_id,account_title,account_code FROM accounts_chart ORDER BY account_title");
            while($racc = mysqli_fetch_assoc($qacc)){
                $sel = ($account_id == $racc['account_id']) ? 'selected' : '';
                echo "<option value='{$racc['account_id']}' $sel>{$racc['account_title']} ({$racc['account_code']})</option>";
            }
            ?>
        </select>
        <label class="ml-3">
            From Date 
        </label>
        <input type="date" name="from_date" class="form-control mx-2 datepicker" value="<?php echo htmlspecialchars($from_date); ?>">
        <label>
            To Date 
        </label>
        <input type="date" name="to_date" class="form-control mx-2 datepicker" value="<?php echo htmlspecialchars($to_date); ?>">
        <button class="btn btn-primary ml-3">Show </button>
    </form>
    <?php
    if($account_id):
        // Get account info
        $acc = mysqli_fetch_assoc(mysqli_query($con,"SELECT * FROM accounts_chart WHERE account_id=$account_id"));
        // Set date range
        $date_filter = '';
        $from_sql = $from_date ? sql_date($from_date) : '';
        $to_sql = $to_date ? sql_date($to_date) : '';
        if($from_sql && $to_sql) {
            $date_filter = " AND v.entry_date BETWEEN '$from_sql' AND '$to_sql' ";
        } elseif($from_sql) {
            $date_filter = " AND v.entry_date >= '$from_sql' ";
        } elseif($to_sql) {
            $date_filter = " AND v.entry_date <= '$to_sql' ";
        }

        // Calculate Opening Balance (before from_date)
        $opening_balance = $acc['opening_debit'] - $acc['opening_credit'];
        $opening_debit = $acc['opening_debit'];
        $opening_credit = $acc['opening_credit'];
        if($from_sql) {
            $qry_open = mysqli_query($con,"
                SELECT 
                    SUM(d.debit) as total_debit, 
                    SUM(d.credit) as total_credit 
                FROM accounts_voucher_detail d
                JOIN accounts_voucher v ON v.voucher_id = d.voucher_id
                WHERE d.account_id=$account_id AND v.entry_date < '$from_sql'
            ");
            $r_open = mysqli_fetch_assoc($qry_open);
            $opening_debit += floatval($r_open['total_debit']);
            $opening_credit += floatval($r_open['total_credit']);
            $opening_balance = $opening_debit - $opening_credit;
        }

        // Ledger Entries (in date range)
        $qry = mysqli_query($con,"
            SELECT v.entry_date,v.voucher_type,v.voucher_no,v.voucher_id,d.description,d.debit,d.credit, d.vd_id
            FROM accounts_voucher_detail d
            JOIN accounts_voucher v ON v.voucher_id = d.voucher_id
            WHERE d.account_id=$account_id $date_filter
            ORDER BY v.entry_date, v.voucher_id, d.vd_id
        ");

        // Prepare display rows in correct order
        $display_rows = [];
        while($row = mysqli_fetch_assoc($qry)){
            // --- SKIP Opening Balance Voucher Entry ---
            // If voucher_no starts with "OPEN-" and description starts with "Opening Balance for account"
            if (
                isset($row['voucher_no']) &&
                strpos(strtoupper($row['voucher_no']), 'OPEN-') === 0 &&
                isset($row['description']) &&
                stripos($row['description'], 'Opening Balance for account') === 0
            ) {
                continue; // skip this entry!
            }
            $display_rows[] = $row;
        }

        // Totals for period
        $period_debit = 0;
        $period_credit = 0;
        foreach($display_rows as $row){
            $period_debit += floatval($row['debit']);
            $period_credit += floatval($row['credit']);
        }

        // Closing balance calculation
        $closing_balance = $opening_balance + $period_debit - $period_credit;
    ?>
    <table class="table table-bordered table-sm">
        <thead>
            <tr>
                <th class="srno-col" style="width: 6%;">Sr No.<br><span class="urdu">نمبر شمار</span></th>
                <th style="width: 9%; text-align: center;">Date<br><span class="urdu" >تاریخ</span></th>
                <th style="width: 11%; text-align: center;">Voucher Type<br><span class="urdu">واؤچر کی قسم</span></th>
                <th style="width: 12%; text-align: center;">Voucher No<br><span class="urdu">واؤچر نمبر</span></th>
                <th style="text-align: center;">Description<br><span class="urdu">تفصیل</span></th>
                <th style="text-align: center;">Debit<br><span class="urdu">ڈیبٹ</span></th>
                <th style="text-align: center;">Credit<br><span class="urdu">کریڈٹ</span></th>
                <th style="text-align: center;">Balance<br><span class="urdu">بیلنس</span></th>
            </tr>
        </thead>
        <tbody>
        <!-- Opening Balance Row -->
        <tr class="opening-row">
            <td colspan="7"><b>Opening Balance / <span class="urdu">اوپننگ بیلنس</span></b></td>
            <td><b><?php echo number_format($opening_balance,2); ?></b></td>
        </tr>
        <?php
        $balance = $opening_balance;
        $srNo = 1;
        foreach($display_rows as $row):
            $type = isset($row['voucher_type']) ? trim($row['voucher_type']) : '';
            if ($type === '' || !isset($allowed_types[$type])) {
                $voucher_type_display = 'Other';
            } else {
                $voucher_type_display = $allowed_types[$type];
            }
            $balance += $row['debit'] - $row['credit'];
        ?>
            <tr>
                <td style="text-align: center;"><?php echo $srNo++; ?></td>
                <td style="text-align: center;"><?php echo date('d-m-Y',strtotime($row['entry_date'])); ?></td>
                <td style="text-align: center;"><?php echo htmlspecialchars($voucher_type_display); ?></td>
                <td style="text-align: center;"><?php echo htmlspecialchars($row['voucher_no']); ?></td>
                <td><?php echo htmlspecialchars($row['description']); ?></td>
                <td class="text-right"><?php echo number_format($row['debit'],2); ?></td>
                <td class="text-right"><?php echo number_format($row['credit'],2); ?></td>
                <td class="text-right"><?php echo number_format($balance,2); ?></td>
            </tr>
        <?php endforeach; ?>
        <!-- Period Totals -->
        <tr class="total-row">
            <td colspan="5">Period Total / <span class="urdu">مجموعی</span></td>
            <td class="text-right"><?php echo number_format($period_debit,2); ?></td>
            <td class="text-right"><?php echo number_format($period_credit,2); ?></td>
            <td></td>
        </tr>
        <!-- Closing Balance -->
        <tr class="closing-row">
            <td colspan="7"><b>Closing Balance / <span class="urdu">کلوزنگ بیلنس</span></b></td>
            <td><b><?php echo number_format($closing_balance,2); ?></b></td>
        </tr>
        </tbody>
    </table>
    <?php endif; ?>

    <!-- Back Button (Bottom) -->
    <div class="text-end mt-4">
        <a href="javascript:history.back()" class="btn btn-secondary">
            &larr; Back
        </a>
    </div>
</div>
</body>
</html>