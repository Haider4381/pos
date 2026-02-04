<?php
include('sessionCheck.php');
include('connection.php');

$branch_id = (int)($_SESSION['branch_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: claim_sample_add.php");
    exit;
}

$s_id = isset($_POST['s_id']) ? (int)$_POST['s_id'] : 0;
if ($s_id <= 0) {
    $_SESSION['msg'] = '<div class="alert alert-danger">Invalid request.</div>';
    header("Location: claim_sample_add.php");
    exit;
}

// Ensure doc belongs to this branch and is sample/claim
$sql = "SELECT s_id FROM cust_sale WHERE s_id={$s_id} AND branch_id={$branch_id} AND s_SaleMode IN ('sample','claim') LIMIT 1";
$res = mysqli_query($con, $sql);
if (!$res || mysqli_num_rows($res) === 0) {
    $_SESSION['msg'] = '<div class="alert alert-danger">Document not found or not allowed.</div>';
    header("Location: claim_sample_add.php");
    exit;
}
mysqli_free_result($res);

// Delete detail then header
mysqli_query($con, "DELETE FROM cust_sale_detail WHERE s_id={$s_id}");
mysqli_query($con, "DELETE FROM cust_sale WHERE s_id={$s_id}");

$_SESSION['msg'] = '<div class="alert alert-success">Document deleted successfully. Stock movement removed.</div>';
header("Location: claim_sample_add.php");
exit;