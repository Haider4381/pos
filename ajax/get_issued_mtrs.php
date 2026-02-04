<?php
include('../connection.php');
$lot_no = $_POST['lot_no'];
$quality_id = intval($_POST['quality_id']);
$dyeing_unit_id = intval($_POST['dyeing_unit_id']);

// Total issued meters
$res_issue = mysqli_query($con, "SELECT SUM(issued_mtr) as total_issue_mtr FROM production_grey_fabric_issue WHERE lot_no='$lot_no' AND quality_id=$quality_id AND dyeing_unit_id=$dyeing_unit_id");
$row_issue = mysqli_fetch_assoc($res_issue);
$total_issue_mtr = floatval($row_issue['total_issue_mtr']);

// Total received meters
$res_receive = mysqli_query($con, "SELECT SUM(received_mtr) as total_receive_mtr FROM production_dyeing_receive WHERE lot_no='$lot_no' AND quality_id=$quality_id AND dyeing_unit_id=$dyeing_unit_id");
$row_receive = mysqli_fetch_assoc($res_receive);
$total_receive_mtr = floatval($row_receive['total_receive_mtr']);

// Meters left
$remaining_mtr = $total_issue_mtr - $total_receive_mtr;
if ($remaining_mtr < 0) $remaining_mtr = 0;

echo number_format($remaining_mtr, 2);
?>