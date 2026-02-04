<?php
// Show errors for debugging (remove in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

include('../connection.php');

// Accept POST or GET
$lot_no = $_POST['lot_no'] ?? $_GET['lot_no'] ?? '';
$unit_id = $_POST['unit_id'] ?? $_GET['unit_id'] ?? '';

// Validate inputs
if (!$lot_no || !$unit_id) {
    echo json_encode(['issued_suits' => 0, 'issued_mtrs' => "0.00"]);
    exit;
}

// Query: Get SUM of suits and mtr_per_suit for this lot_no and embroidery_unit
$res = mysqli_query($con, "
    SELECT 
        SUM(pd.suits) as issued_suits,
        pd.mtr_per_suit as mtr_per_suit
    FROM production_embroidery_issue_detail pd
    JOIN production_embroidery_issue pi ON pd.embroidery_issue_id = pi.id
    WHERE pd.lot_no = '$lot_no'
    AND pi.embroidery_unit_id = '$unit_id'
    LIMIT 1
");
$row = mysqli_fetch_assoc($res);

// issued_suits: Sum of all suits (may be multiple rows)
// issued_mtrs: Just show mtr_per_suit (green box) of the first matching row
$issued_suits = intval($row['issued_suits'] ?? 0);
$issued_mtrs = number_format(floatval($row['mtr_per_suit'] ?? 0), 2, '.', '');

// Return: issued_suits, issued_mtrs (as green box value, not the calculated total)
echo json_encode([
    'issued_suits' => $issued_suits,
    'issued_mtrs' => $issued_mtrs
]);
?>