<?php
include('connection.php');

/*
 Unified JSON responder:
 - ex_imei: duplicate check (currently always ok)
 - item_id: fetch item info + best rate (supplier-specific last rate -> any last rate -> base item_PurchasePrice)
 - item_Code: same as item_id path but by item code

 Always returns pure JSON. For backward compatibility, top-level fields are included
 (item_Code, item_PurchasePrice etc). item_PurchasePrice contains the CHOSEN rate.
*/

header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

function out($arr){ echo json_encode($arr, JSON_UNESCAPED_UNICODE); exit; }

function get_stock($con, $item_id){
    $item_id = (int)$item_id;
    $sql = "
        SELECT IFNULL(SUM(item_qty),0) AS item_stock
        FROM (
            SELECT 0-item_Qty AS item_qty FROM cust_sale_detail WHERE item_id={$item_id}
            UNION ALL
            SELECT item_Qty AS item_qty FROM adm_purchase_detail WHERE item_id={$item_id}
            UNION ALL
            SELECT item_Qty AS item_qty FROM cust_salereturn_detail WHERE item_id={$item_id}
        ) AS c
    ";
    if (!$res = mysqli_query($con,$sql)) return 0;
    $row = mysqli_fetch_assoc($res);
    mysqli_free_result($res);
    return isset($row['item_stock']) ? (float)$row['item_stock'] : 0.0;
}

function fetch_item_row($con, $whereSql){
    $sql = "SELECT item_id, item_Code, item_Name, item_PurchasePrice, item_SalePrice, item_QtyInPack
            FROM adm_item
            WHERE {$whereSql}
            LIMIT 1";
    $res = mysqli_query($con, $sql);
    if (!$res) return null;
    $row = mysqli_fetch_assoc($res);
    mysqli_free_result($res);
    return $row ?: null;
}

function fetch_last_rate($con, $item_id, $sup_id = 0){
    $item_id = (int)$item_id;
    $sup_id = (int)$sup_id;

    // 1) Supplier-specific last rate
    if ($sup_id > 0) {
        $q = "
            SELECT pd.item_Rate
            FROM adm_purchase_detail pd
            INNER JOIN adm_purchase p ON p.p_id = pd.p_id
            WHERE pd.item_id = {$item_id} AND (p.sup_id = {$sup_id} OR pd.sup_id = {$sup_id})
            ORDER BY pd.pd_id DESC
            LIMIT 1
        ";
        if ($rs = mysqli_query($con, $q)) {
            if ($r = mysqli_fetch_row($rs)) {
                mysqli_free_result($rs);
                $rate = (float)$r[0];
                if ($rate > 0) return $rate;
            } else {
                mysqli_free_result($rs);
            }
        }
    }

    // 2) Any last rate for this item
    $q2 = "
        SELECT pd.item_Rate
        FROM adm_purchase_detail pd
        WHERE pd.item_id = {$item_id}
        ORDER BY pd.pd_id DESC
        LIMIT 1
    ";
    if ($rs2 = mysqli_query($con, $q2)) {
        if ($r2 = mysqli_fetch_row($rs2)) {
            mysqli_free_result($rs2);
            $rate2 = (float)$r2[0];
            if ($rate2 > 0) return $rate2;
        } else {
            mysqli_free_result($rs2);
        }
    }

    return 0.0;
}

/* -------- IMEI CHECK (kept permissive as in your current app) -------- */
if (isset($_POST['ex_imei'])) {
    $ex_imei = trim((string)$_POST['ex_imei']);
    out([
        'status'  => 'ok',
        'message' => '',
        'ex_imei' => $ex_imei,
        'data'    => ['ex_imei' => $ex_imei]
    ]);
}

/* -------- FETCH BY item_id -------- */
if (isset($_POST['item_id'])) {
    $item_id = (int)$_POST['item_id'];
    $sup_id  = isset($_POST['sup_id']) ? (int)$_POST['sup_id'] : 0;

    if ($item_id <= 0) {
        out(['status'=>'error','message'=>'Invalid item_id','data'=>[]]);
    }

    $row = fetch_item_row($con, "item_id = {$item_id}");
    if (!$row) {
        out(['status'=>'not_found','message'=>'Item not found','data'=>[]]);
    }

    $base  = (float)$row['item_PurchasePrice'];
    $last  = fetch_last_rate($con, $row['item_id'], $sup_id);
    $chosen = $last > 0 ? $last : $base;

    $stock = get_stock($con, $row['item_id']);

    // Backward compatibility top-level, with chosen rate in item_PurchasePrice
    $payload = [
        'status'                => 'ok',
        'message'               => '',
        'item_id'               => (int)$row['item_id'],
        'item_Code'             => $row['item_Code'],
        'item_Name'             => $row['item_Name'],
        'item_PurchasePrice'    => number_format($chosen, 2, '.', ''), // chosen rate for UI auto-fill
        'base_purchase_price'   => number_format($base, 2, '.', ''),
        'last_rate'             => $last > 0 ? number_format($last, 2, '.', '') : null,
        'chosen_rate'           => number_format($chosen, 2, '.', ''),
        'item_SalePrice'        => number_format((float)$row['item_SalePrice'], 2, '.', ''),
        'item_QtyInPack'        => $row['item_QtyInPack'],
        'item_CurrentStock'     => $stock,
        // Also nested 'data' for newer clients
        'data' => [
            'item_id'               => (int)$row['item_id'],
            'item_Code'             => $row['item_Code'],
            'item_Name'             => $row['item_Name'],
            'item_PurchasePrice'    => number_format($chosen, 2, '.', ''),
            'base_purchase_price'   => number_format($base, 2, '.', ''),
            'last_rate'             => $last > 0 ? number_format($last, 2, '.', '') : null,
            'chosen_rate'           => number_format($chosen, 2, '.', ''),
            'item_SalePrice'        => number_format((float)$row['item_SalePrice'], 2, '.', ''),
            'item_QtyInPack'        => $row['item_QtyInPack'],
            'item_CurrentStock'     => $stock
        ]
    ];
    out($payload);
}

/* -------- FETCH BY item_Code -------- */
if (isset($_POST['item_Code'])) {
    $item_Code = trim((string)$_POST['item_Code']);
    $sup_id    = isset($_POST['sup_id']) ? (int)$_POST['sup_id'] : 0;
    if ($item_Code === '') {
        out(['status'=>'error','message'=>'Empty item_Code','data'=>[]]);
    }
    $esc = mysqli_real_escape_string($con, $item_Code);
    $row = fetch_item_row($con, "item_Code = '{$esc}'");
    if (!$row) {
        out(['status'=>'not_found','message'=>'Item code not found','data'=>[]]);
    }

    $base  = (float)$row['item_PurchasePrice'];
    $last  = fetch_last_rate($con, $row['item_id'], $sup_id);
    $chosen = $last > 0 ? $last : $base;
    $stock = get_stock($con, $row['item_id']);

    $payload = [
        'status'                => 'ok',
        'message'               => '',
        'item_id'               => (int)$row['item_id'],
        'item_Code'             => $row['item_Code'],
        'item_Name'             => $row['item_Name'],
        'item_PurchasePrice'    => number_format($chosen, 2, '.', ''), // chosen rate for UI
        'base_purchase_price'   => number_format($base, 2, '.', ''),
        'last_rate'             => $last > 0 ? number_format($last, 2, '.', '') : null,
        'chosen_rate'           => number_format($chosen, 2, '.', ''),
        'item_SalePrice'        => number_format((float)$row['item_SalePrice'], 2, '.', ''),
        'item_QtyInPack'        => $row['item_QtyInPack'],
        'item_CurrentStock'     => $stock,
        'data' => [
            'item_id'               => (int)$row['item_id'],
            'item_Code'             => $row['item_Code'],
            'item_Name'             => $row['item_Name'],
            'item_PurchasePrice'    => number_format($chosen, 2, '.', ''),
            'base_purchase_price'   => number_format($base, 2, '.', ''),
            'last_rate'             => $last > 0 ? number_format($last, 2, '.', '') : null,
            'chosen_rate'           => number_format($chosen, 2, '.', ''),
            'item_SalePrice'        => number_format((float)$row['item_SalePrice'], 2, '.', ''),
            'item_QtyInPack'        => $row['item_QtyInPack'],
            'item_CurrentStock'     => $stock
        ]
    ];
    out($payload);
}

/* -------- No recognized parameter -------- */
out([
    'status'  => 'error',
    'message' => 'No valid parameter provided. Use ex_imei, item_id or item_Code.',
    'data'    => []
]);