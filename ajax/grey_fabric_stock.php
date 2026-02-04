<?php
include('../connection.php');
$lot_no = $_GET['lot_no'] ?? '';
$quality_id = intval($_GET['quality_id'] ?? 0);

// Get purchased meters
$q = mysqli_query($con, "SELECT IFNULL(SUM(net_mtr),0) as mtr FROM production_grey_fabric_purchase WHERE lot_no='".mysqli_real_escape_string($con, $lot_no)."' AND quality_id=$quality_id");
$row = mysqli_fetch_assoc($q);
$purchased = floatval($row['mtr']);

// Get total issued meters (column name as per your table: issued_mtr)
$q = mysqli_query($con, "SELECT IFNULL(SUM(issued_mtr),0) as mtr FROM production_grey_fabric_issue WHERE lot_no='".mysqli_real_escape_string($con, $lot_no)."' AND quality_id=$quality_id");
$row = mysqli_fetch_assoc($q);
$issued = floatval($row['mtr']);

$stock = $purchased - $issued;
if($stock < 0) $stock = 0;

header('Content-Type: application/json');
echo json_encode(['stock'=>number_format($stock,2)]);