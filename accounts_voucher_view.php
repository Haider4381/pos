<?php
include('connection.php');
include('sessionCheck.php');
require_once ("inc/init.php");
require_once ("inc/config.ui.php");
$page_title = "Purchase";
include ("inc/header.php");
include ("inc/nav.php");

$voucher_id = intval($_GET['voucher_id']);
$voucher = mysqli_fetch_assoc(mysqli_query($con,"SELECT * FROM accounts_voucher WHERE voucher_id=$voucher_id"));
if(!$voucher) die("Voucher not found!");

?>
<!DOCTYPE html>
<html>
<head>
    <title>Voucher Details</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4" style="margin-top:90px !important">
    <h4>Voucher Details (ID: <?php echo $voucher['voucher_id']; ?>)</h4>
    <table class="table table-bordered">
        <tr><th>Date</th><td><?php echo $voucher['entry_date']; ?></td>
            <th>Type</th><td><?php echo $voucher['voucher_type']; ?></td></tr>
        <tr><th>Voucher No</th><td><?php echo $voucher['voucher_no']; ?></td>
            <th>Description</th><td><?php echo $voucher['description']; ?></td></tr>
    </table>
    <h5>Voucher Lines</h5>
    <table class="table table-bordered table-sm">
        <thead>
            <tr>
                <th>Account</th>
                <th>Description</th>
                <th>Debit</th>
                <th>Credit</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $q = mysqli_query($con,"SELECT d.*,a.account_title,a.account_code FROM accounts_voucher_detail d JOIN accounts_chart a ON d.account_id=a.account_id WHERE voucher_id=$voucher_id");
        while($row=mysqli_fetch_assoc($q)):
        ?>
            <tr>
                <td><?php echo htmlspecialchars($row['account_title']); ?> (<?php echo $row['account_code']; ?>)</td>
                <td><?php echo htmlspecialchars($row['description']); ?></td>
                <td><?php echo $row['debit']; ?></td>
                <td><?php echo $row['credit']; ?></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    <a href="accounts_voucher.php" class="btn btn-secondary">Back</a>
</div>
</body>
</html>