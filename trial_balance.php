<?php
include 'connection.php';

// Date filter
$from_date = isset($_GET['from']) ? $_GET['from'] : date('Y-01-01');
$to_date = isset($_GET['to']) ? $_GET['to'] : date('Y-m-d');

// --- Trial Balance Query: Opening from vouchers, not from accounts_chart
$q = "
SELECT 
    ac.account_id,
    ac.account_code,
    ac.account_title,
    ac.account_type,

    IFNULL(opening_tbl.opening_debit, 0) AS opening_debit,
    IFNULL(opening_tbl.opening_credit, 0) AS opening_credit,

    IFNULL(period_tbl.period_debit, 0) AS period_debit,
    IFNULL(period_tbl.period_credit, 0) AS period_credit,

    (IFNULL(opening_tbl.opening_debit, 0) - IFNULL(opening_tbl.opening_credit, 0)) AS opening_balance,
    (IFNULL(opening_tbl.opening_debit, 0) - IFNULL(opening_tbl.opening_credit, 0)
        + IFNULL(period_tbl.period_debit, 0)
        - IFNULL(period_tbl.period_credit, 0)
    ) AS closing_balance

FROM accounts_chart ac

-- Opening: All vouchers up to (including) from_date, so opening vouchers are included even if their date is same as from_date
LEFT JOIN (
    SELECT 
        avd.account_id,
        SUM(avd.debit) AS opening_debit,
        SUM(avd.credit) AS opening_credit
    FROM accounts_voucher_detail avd
    JOIN accounts_voucher av ON av.voucher_id = avd.voucher_id
    WHERE av.entry_date < '$from_date'
        OR (av.voucher_no LIKE 'OPEN-%' AND avd.description LIKE 'Opening Balance for account%')
    GROUP BY avd.account_id
) AS opening_tbl ON ac.account_id = opening_tbl.account_id

-- Period: from from_date to to_date, excluding opening vouchers
LEFT JOIN (
    SELECT 
        avd.account_id,
        SUM(CASE 
            WHEN NOT (av.voucher_no LIKE 'OPEN-%' AND avd.description LIKE 'Opening Balance for account%')
            THEN avd.debit ELSE 0 END
        ) AS period_debit,
        SUM(CASE 
            WHEN NOT (av.voucher_no LIKE 'OPEN-%' AND avd.description LIKE 'Opening Balance for account%')
            THEN avd.credit ELSE 0 END
        ) AS period_credit
    FROM accounts_voucher_detail avd
    JOIN accounts_voucher av ON av.voucher_id = avd.voucher_id
    WHERE av.entry_date >= '$from_date' AND av.entry_date <= '$to_date'
    GROUP BY avd.account_id
) AS period_tbl ON ac.account_id = period_tbl.account_id

WHERE ac.status='active'
ORDER BY ac.account_code
";

$res = mysqli_query($con, $q);

$total_opening_debit = 0;
$total_opening_credit = 0;
$total_debit = 0;
$total_credit = 0;
$total_closing_debit = 0;
$total_closing_credit = 0;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Trial Balance</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .table thead th, .table thead tr:first-child th {
            background: #2566a0;
            color: #fff;
            font-weight: bold;
            vertical-align: middle;
            text-align: center;
            font-size: 0.82rem;
            padding: 0.4rem 0.4rem;
        }
        .table thead tr:nth-child(2) th {
            background: #fff;
            color: #222;
            font-weight: bold;
            text-align: center;
            border-bottom: 2px solid #2566a0;
            font-size: 0.82rem;
            padding: 0.3rem 0.2rem;
        }
        .table thead th[colspan] {
            background: #fff;
            color: #000;
            font-weight: bold;
            border-bottom: 1px solid #2566a0;
            font-size: 0.82rem;
        }
        .table tfoot th { background: #eaf2fb; color: #222; font-size: 0.95rem; }
        .table-striped > tbody > tr:nth-of-type(odd) { background-color: #f7fafd; }
        .form-label { font-weight: 500; }
        .container { max-width: 1100px; }
        .back-btn { margin-bottom: 1.5rem; }
        .table td, .table th { font-size: 0.95rem; padding: 0.4rem 0.3rem; }
        @media (max-width: 767px) {
            .table thead, .table tfoot, .table td, .table th { font-size: 0.85rem !important; }
            .form-label { font-size: 0.95rem; }
        }
    </style>
</head>
<body>
<div class="container mt-4 mb-4">

    <!-- Back Button (Top) -->
    <a href="javascript:history.back()" class="btn btn-secondary back-btn">
        &larr; Back
    </a>

    <h2 class="mb-4 text-primary">Trial Balance</h2>
    <form method="get" class="row g-3 align-items-end mb-4">
        <div class="col-auto">
            <label for="from" class="form-label">From:</label>
            <input type="date" name="from" id="from" class="form-control" value="<?php echo htmlspecialchars($from_date); ?>">
        </div>
        <div class="col-auto">
            <label for="to" class="form-label">To:</label>
            <input type="date" name="to" id="to" class="form-control" value="<?php echo htmlspecialchars($to_date); ?>">
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary">Show</button>
        </div>
    </form>
    <div class="table-responsive">
        <table class="table table-bordered table-striped table-hover align-middle">
            <thead>
                <tr>
                    <th rowspan="2">Account Code</th>
                    <th rowspan="2">Account Title</th>
                    <th rowspan="2">Type</th>
                    <th colspan="2">Opening</th>
                    <th colspan="2">Period</th>
                    <th colspan="2">Closing</th>
                </tr>
                <tr>
                    <th>Debit</th>
                    <th>Credit</th>
                    <th>Debit</th>
                    <th>Credit</th>
                    <th>Debit</th>
                    <th>Credit</th>
                </tr>
            </thead>
            <tbody>
            <?php
            while($row = mysqli_fetch_assoc($res)) {
                $opening_balance = $row['opening_balance'];
                $closing_balance = $row['closing_balance'];

                // Opening Debit/Credit split
                $opening_debit = 0;
                $opening_credit = 0;
                if ($opening_balance > 0) {
                    $opening_debit = $opening_balance;
                } elseif ($opening_balance < 0) {
                    $opening_credit = abs($opening_balance);
                }

                // Closing Debit/Credit split
                $closing_debit = 0;
                $closing_credit = 0;
                if ($closing_balance > 0) {
                    $closing_debit = $closing_balance;
                } elseif ($closing_balance < 0) {
                    $closing_credit = abs($closing_balance);
                }

                if($opening_debit != 0 || $opening_credit != 0 || $row['period_debit'] != 0 || $row['period_credit'] != 0 || $closing_debit != 0 || $closing_credit != 0) {
                    echo "<tr>
                        <td style='text-align:center;'>".htmlspecialchars($row['account_code'])."</td>
                        <td>".htmlspecialchars($row['account_title'])."</td>
                        <td>".htmlspecialchars($row['account_type'])."</td>
                        <td class='text-end'>".($opening_debit != 0 ? number_format($opening_debit,2) : '')."</td>
                        <td class='text-end'>".($opening_credit != 0 ? number_format($opening_credit,2) : '')."</td>
                        <td class='text-end'>".($row['period_debit'] != 0 ? number_format($row['period_debit'],2) : '')."</td>
                        <td class='text-end'>".($row['period_credit'] != 0 ? number_format($row['period_credit'],2) : '')."</td>
                        <td class='text-end'>".($closing_debit != 0 ? number_format($closing_debit,2) : '')."</td>
                        <td class='text-end'>".($closing_credit != 0 ? number_format($closing_credit,2) : '')."</td>
                    </tr>";
                    $total_opening_debit += $opening_debit;
                    $total_opening_credit += $opening_credit;
                    $total_debit += $row['period_debit'];
                    $total_credit += $row['period_credit'];
                    $total_closing_debit += $closing_debit;
                    $total_closing_credit += $closing_credit;
                }
            }
            ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="3" class="text-end">Total</th>
                    <th class="text-end"><?php echo number_format($total_opening_debit,2); ?></th>
                    <th class="text-end"><?php echo number_format($total_opening_credit,2); ?></th>
                    <th class="text-end"><?php echo number_format($total_debit,2); ?></th>
                    <th class="text-end"><?php echo number_format($total_credit,2); ?></th>
                    <th class="text-end"><?php echo number_format($total_closing_debit,2); ?></th>
                    <th class="text-end"><?php echo number_format($total_closing_credit,2); ?></th>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Back Button (Bottom) -->
    <div class="text-end mt-4">
        <a href="javascript:history.back()" class="btn btn-secondary">
            &larr; Back
        </a>
    </div>

</div>
<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>