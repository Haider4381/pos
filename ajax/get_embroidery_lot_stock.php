<?php
// Enable error display for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

include('../connection.php');

// Accept both POST and GET for easier testing
$lot_no = $_POST['lot_no'] ?? $_GET['lot_no'] ?? '';

if (!$lot_no) {
    echo "0.00";
    exit;
}

// Get total meters received from Dyeing unit for this lot
$res_dyeing = mysqli_query($con, "SELECT SUM(received_mtr) AS received FROM production_dyeing_receive WHERE lot_no='$lot_no'");
$row_dyeing = mysqli_fetch_assoc($res_dyeing);
$received = floatval($row_dyeing['received']);

// Get total meters already issued to embroidery for this lot
$res_issue = mysqli_query($con, "SELECT SUM(total_mtrs) as issued FROM production_embroidery_issue_detail WHERE lot_no='$lot_no'");
$row_issue = mysqli_fetch_assoc($res_issue);
$issued = floatval($row_issue['issued']);

$available = $received - $issued;
if ($available < 0) $available = 0;

// Output WITHOUT comma for input type="number"
echo number_format($available, 2, '.', ''); // Outputs like 982.00
?>