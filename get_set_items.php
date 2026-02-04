<?php
include('connection.php');

// Stock calculation function copied from get_item_detail.php
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

$set_id = (int)$_POST['set_id'];
$mode = isset($_POST['mode']) ? $_POST['mode'] : 'purchase';

if($mode == 'sale') {
    $price_field = 'i.item_SalePrice';
} else {
    $price_field = 'i.item_PurchasePrice';
}

$data = [];
$q = mysqli_query($con, "SELECT d.item_id, i.item_Name, i.item_Code, d.quantity, 
                                i.item_SalePrice, i.item_PurchasePrice, 
                                $price_field as item_Price 
                         FROM adm_itemset_detail d
                         JOIN adm_item i ON i.item_id = d.item_id
                         WHERE d.set_id = $set_id");
while($row = mysqli_fetch_assoc($q)){
    // Always send cost price (item_PurchasePrice) for each item
    $row['item_CostPrice'] = $row['item_PurchasePrice'];
    // Stock calculation for each item
    $row['stock'] = get_live_stock($row['item_id'], $con);
    $data[] = $row;
}
echo json_encode($data);
?>