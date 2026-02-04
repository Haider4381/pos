<?php
include("../connection.php");

function get_live_stock($item_id, $con) {
    $sql = "
        SELECT IFNULL(SUM(item_qty),0) AS item_stock
        FROM (
            SELECT 0-item_Qty AS item_qty FROM cust_sale_detail WHERE item_id=$item_id
            UNION ALL
            SELECT item_Qty AS item_qty FROM adm_purchase_detail WHERE item_id=$item_id
            UNION ALL
            SELECT item_Qty AS item_qty FROM cust_salereturn_detail WHERE item_id=$item_id
            UNION ALL
            SELECT 0-item_Qty AS item_qty FROM adm_purchasereturn_detail WHERE item_id=$item_id
        ) as c
    ";
    $result = mysqli_query($con, $sql);
    $row = mysqli_fetch_assoc($result);
    return $row['item_stock'];
}

// Accept item_id, item_name, or item_code for lookup
$item_id   = isset($_GET['item_id'])   ? intval($_GET['item_id'])   : 0;
$item_name = isset($_GET['item_name']) ? trim($_GET['item_name'])   : '';
$item_code = isset($_GET['item_code']) ? trim($_GET['item_code'])   : '';

$response = [];

// Product Search
if($item_id > 0) {
    $q = mysqli_query($con, "SELECT * FROM adm_item WHERE item_id=$item_id LIMIT 1");
} elseif($item_name != '') {
    $q = mysqli_query($con, "SELECT * FROM adm_item WHERE item_Name='".mysqli_real_escape_string($con, $item_name)."' LIMIT 1");
} elseif($item_code != '') {
    $q = mysqli_query($con, "SELECT * FROM adm_item WHERE item_Code='".mysqli_real_escape_string($con, $item_code)."' LIMIT 1");
    // If product not found, try set code in adm_itemset
    if($q && mysqli_num_rows($q) === 0) {
        $setQ = mysqli_query($con, "SELECT * FROM adm_itemset WHERE set_code='".mysqli_real_escape_string($con, $item_code)."' LIMIT 1");
        if($setQ && mysqli_num_rows($setQ) > 0) {
            $setRow = mysqli_fetch_assoc($setQ);
            $response = [
                'set_id'    => $setRow['set_id'],
                'set_name'  => $setRow['set_name'],
                'set_code'  => $setRow['set_code'],
                'is_set'    => 1
            ];
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        }
    }
} else {
    $q = false;
}

if($q && mysqli_num_rows($q) > 0) {
    $row = mysqli_fetch_assoc($q);
    $stock = get_live_stock($row['item_id'], $con);
    $response = [
        'item_id'      => $row['item_id'],
        'item_Name'    => $row['item_Name'],
        'item_Code'    => $row['item_Code'],
        'qty_pack'     => isset($row['item_QtyInPack']) ? $row['item_QtyInPack'] : '',
        'stock'        => $stock,
        'sale_price'   => $row['item_SalePrice'],
        'cost_price'   => $row['item_PurchasePrice'],
        'discount'     => isset($row['item_Discount']) ? $row['item_Discount'] : '',
    ];
}

header('Content-Type: application/json');
echo json_encode($response);
exit;
?>