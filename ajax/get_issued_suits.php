<?php
include('../connection.php');

file_put_contents('debug_ajax.txt', print_r($_POST, true));

// Safely fetch POST variables
$lot_no = isset($_POST['lot_no']) ? $_POST['lot_no'] : '';
$unit_id = isset($_POST['unit_id']) ? $_POST['unit_id'] : '';

$res = [
    'issued_suits' => '',
    'issued_mtrs' => '',
    'item_id' => '',
    'item_name' => '',
    'debug' => ''
];

// Debug: check incoming values
if($lot_no == '' || $unit_id == '') {
    $res['debug'] = 'Lot or Unit missing (Lot: '.$lot_no.', Unit: '.$unit_id.')';
    header('Content-Type: application/json');
    echo json_encode($res);
    exit;
}

// Find issued data
$q = mysqli_query($con, "SELECT 
    SUM(pd.suits) as issued_suits, 
    SUM(pd.total_mtrs) as issued_mtrs, 
    pd.item_id, 
    im.item_Name 
FROM production_embroidery_issue_detail pd
JOIN production_embroidery_issue pi ON pd.embroidery_issue_id=pi.id
LEFT JOIN adm_item im ON pd.item_id=im.item_id
WHERE pd.lot_no='".mysqli_real_escape_string($con, $lot_no)."' 
  AND pi.embroidery_unit_id='".mysqli_real_escape_string($con, $unit_id)."'
GROUP BY pd.item_id
LIMIT 1");

if($row = mysqli_fetch_assoc($q)){
    $res['issued_suits'] = $row['issued_suits'] ?: '';
    $res['issued_mtrs']  = $row['issued_mtrs'] ?: '';
    $res['item_id']      = $row['item_id'] ?: '';
    $res['item_name']    = $row['item_Name'] ?: '';
    $res['debug']        = 'Data found';
} else {
    $res['debug'] = 'No match in DB for Lot: '.$lot_no.', Unit: '.$unit_id;
}

header('Content-Type: application/json');
echo json_encode($res);