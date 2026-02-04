<?php
require_once('connection.php');

// Error reporting (dev only). Prod me off rakhna behtar hota hai.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Ensure session for branch_id
if (session_status() === PHP_SESSION_NONE) {
    // Do not start session forcibly if your app handles sessions elsewhere.
    // session_start();
}

$branch_id = isset($_SESSION['branch_id']) ? intval($_SESSION['branch_id']) : 0;

/* ==================== Utility / Safe Helpers ==================== */

/**
 * Check if a table exists in current database.
 */
function table_exists($con, $tableName) {
    $tableName = mysqli_real_escape_string($con, $tableName);
    $sql = "SHOW TABLES LIKE '{$tableName}'";
    $res = mysqli_query($con, $sql);
    if ($res === false) {
        error_log("table_exists SQL error: " . mysqli_error($con));
        return false;
    }
    $exists = mysqli_num_rows($res) > 0;
    mysqli_free_result($res);
    return $exists;
}

/**
 * Execute SQL and fetch all rows as associative array.
 * On error returns [] and logs error.
 */
function db_fetch_all($con, $sql) {
    $res = mysqli_query($con, $sql);
    if ($res === false) {
        error_log("SQL error: " . mysqli_error($con) . " -- SQL: " . $sql);
        return [];
    }
    $rows = [];
    while ($row = mysqli_fetch_assoc($res)) {
        $rows[] = $row;
    }
    mysqli_free_result($res);
    return $rows;
}

/**
 * Execute SQL that returns single scalar value (first column of first row).
 */
function db_scalar($con, $sql, $default = null) {
    $res = mysqli_query($con, $sql);
    if ($res === false) {
        error_log("SQL error: " . mysqli_error($con) . " -- SQL: " . $sql);
        return $default;
    }
    $row = mysqli_fetch_row($res);
    mysqli_free_result($res);
    if ($row && isset($row[0])) return $row[0];
    return $default;
}

/* ==================== Business Helpers ==================== */

function getNextSerial($con, $table, $colSerial, $branch_id){
    $table = preg_replace('/[^a-zA-Z0-9_]/','', $table);
    $colSerial = preg_replace('/[^a-zA-Z0-9_]/','', $colSerial);

    $sql = "SELECT IFNULL(MAX($colSerial),0) AS last_no FROM $table WHERE branch_id = ?";
    $stmt = mysqli_prepare($con, $sql);
    if (!$stmt) {
        error_log("getNextSerial prepare failed: " . mysqli_error($con));
        return 1;
    }
    mysqli_stmt_bind_param($stmt, "i", $branch_id);
    if (!mysqli_stmt_execute($stmt)) {
        error_log("getNextSerial execute failed: " . mysqli_error($con));
        mysqli_stmt_close($stmt);
        return 1;
    }
    $res = mysqli_stmt_get_result($stmt);
    $row = $res ? mysqli_fetch_assoc($res) : ['last_no' => 0];
    mysqli_stmt_close($stmt);
    return intval($row['last_no']) + 1;
}

function getItemName($item_id) {
    global $con;
    $item_id = intval($item_id);
    $sql = "SELECT item_Name FROM adm_item WHERE item_id = {$item_id} LIMIT 1";
    $res = mysqli_query($con, $sql);
    if ($res && ($row = mysqli_fetch_assoc($res))) {
        mysqli_free_result($res);
        return $row['item_Name'];
    } else if ($res) {
        mysqli_free_result($res);
    } else {
        error_log("getItemName SQL error: " . mysqli_error($con));
    }
    return '';
}

function get_live_stock($item_id, $con) {
    $item_id = intval($item_id);
    $sql = "
        SELECT IFNULL(SUM(item_qty),0) AS item_stock
        FROM (
            SELECT 0-item_Qty AS item_qty FROM cust_sale_detail WHERE item_id={$item_id}
            UNION ALL
            SELECT item_Qty AS item_qty FROM adm_purchase_detail WHERE item_id={$item_id}
            UNION ALL
            SELECT item_Qty AS item_qty FROM cust_salereturn_detail WHERE item_id={$item_id}
            UNION ALL
            SELECT 0-item_Qty AS item_qty FROM adm_purchasereturn_detail WHERE item_id={$item_id}
        ) AS c
    ";
    $res = mysqli_query($con, $sql);
    if ($res && ($row = mysqli_fetch_assoc($res))) {
        mysqli_free_result($res);
        return $row['item_stock'];
    } else if ($res) {
        mysqli_free_result($res);
    } else {
        error_log("get_live_stock SQL error: " . mysqli_error($con));
    }
    return 0;
}

function get_AccountClientList() {
    global $con, $branch_id;
    // accounts_chart exists; 'Customer' enum shayad na ho, is liye Asset include kiya hai
    $sql = "SELECT account_id, account_title, phone
            FROM accounts_chart
            WHERE (account_type='Asset' OR account_type='Customer')
              AND branch_id=".intval($branch_id);
    return db_fetch_all($con, $sql);
}

function get_Supplier() {
    global $con, $branch_id;
    $sql = "SELECT account_id, account_title, phone
            FROM accounts_chart
            WHERE (account_type='Liability' OR account_type='Supplier')
              AND branch_id=".intval($branch_id);
    return db_fetch_all($con, $sql);
}

function get_EmployeesBranch() {
    global $con;
    $branch_id = isset($_SESSION['branch_id']) ? intval($_SESSION['branch_id']) : 0;
    $sql = "SELECT * FROM u_user WHERE branch_id={$branch_id}";
    return db_fetch_all($con, $sql);
}

function get_RolesBranch() {
    global $con;
    $branch_id = isset($_SESSION['branch_id']) ? intval($_SESSION['branch_id']) : 0;
    $sql = "SELECT * FROM sys_role WHERE branch_id={$branch_id}";
    return db_fetch_all($con, $sql);
}

function get_ActiveClient() {
    global $con, $branch_id;
    if (!table_exists($con, 'adm_client')) return [];
    $sql = "SELECT client_id, client_Name, client_Phone, client_Email, client_Address, client_Status, client_Remarks
            FROM adm_client
            WHERE client_Status='A' AND branch_id=".intval($branch_id)."
            ORDER BY client_Name";
    return db_fetch_all($con, $sql);
}

function get_ActiveItems() {
    global $con, $branch_id;
    $brandJoin = "";
    $brandField = "'' AS brand_Name";
    if (table_exists($con, 'adm_brand')) {
        $brandJoin = "LEFT JOIN adm_brand AS B ON B.brand_id=I.brand_id";
        $brandField = "B.brand_Name";
    }
    $sql = "SELECT I.item_id,I.item_Code,I.item_Name,I.brand_id,I.item_Status,I.item_Remarks, {$brandField}
            FROM adm_item AS I
            {$brandJoin}
            WHERE I.item_Status='A' AND I.branch_id=".intval($branch_id);
    return db_fetch_all($con, $sql);
}

function get_ActiveBrands() {
    global $con;
    if (!table_exists($con, 'adm_brand')) return [];
    $sql = "SELECT brand_id, brand_Name, brand_Status FROM adm_brand WHERE brand_Status='A'";
    return db_fetch_all($con, $sql);
}

function get_ClientList() {
    global $con, $branch_id;
    if (!table_exists($con, 'adm_client')) return [];
    $sql = "SELECT client_id, client_Name, client_Phone, client_Email, client_Address, client_Status, client_Remarks
            FROM adm_client
            WHERE branch_id=".intval($branch_id);
    return db_fetch_all($con, $sql);
}

function get_SupplierList() {
    global $con, $branch_id;
    if (!table_exists($con, 'adm_supplier')) return [];
    $sql = "SELECT sup_id, sup_Name, sup_Phone, sup_Email, sup_Address, sup_Status, sup_Remarks
            FROM adm_supplier
            WHERE branch_id=".intval($branch_id);
    return db_fetch_all($con, $sql);
}

function get_StatusName($status) {
    if ($status=='A')      return "Active";
    elseif ($status=='L')  return "Left";
    elseif ($status=='I')  return "In-Active";
    return $status;
}

function get_Brands() {
    global $con;
    if (!table_exists($con, 'adm_brand')) return [];
    $sql = "SELECT brand_id, brand_Name, brand_Status FROM adm_brand WHERE 1";
    return db_fetch_all($con, $sql);
}

function get_Categories() {
    global $con, $branch_id;
    $sql = "SELECT * FROM adm_itemcategory WHERE branch_id=".intval($branch_id);
    return db_fetch_all($con, $sql);
}

function get_UnitList() {
    global $con, $branch_id;
    $sql = "SELECT * FROM adm_itemunit WHERE branch_id=".intval($branch_id);
    return db_fetch_all($con, $sql);
}

function get_SubCategories() {
    global $con, $branch_id;
    $sql = "SELECT * FROM adm_itemsubcategory WHERE branch_id=".intval($branch_id);
    return db_fetch_all($con, $sql);
}

function get_Items() {
    global $con, $branch_id;

    $brandJoin = "";
    $brandField = "'' AS brand_Name";
    if (table_exists($con, 'adm_brand')) {
        $brandJoin  = "LEFT JOIN adm_brand AS B ON B.brand_id=I.brand_id";
        $brandField = "B.brand_Name";
    }

    $sql = "SELECT
                I.item_id, I.item_Code, I.item_Name, I.brand_id, I.item_Status, I.item_Remarks,
                I.item_SalePrice, I.item_PurchasePrice, I.item_InvoicePrice, I.item_Percentage, I.item_Scheme,
                I.item_Image,
                {$brandField},
                IC.icat_name,
                ISC.isubcat_name
            FROM adm_item AS I
            {$brandJoin}
            LEFT JOIN adm_itemcategory   AS IC  ON IC.icat_id    = I.icat_id
            LEFT JOIN adm_itemsubcategory AS ISC ON ISC.isubcat_id = I.isubcat_id
            WHERE I.branch_id=".intval($branch_id)." AND I.item_other=0
            ORDER BY I.item_Name";
    return db_fetch_all($con, $sql);
}

function get_PhoneNumber() {
    global $con;
    if (!table_exists($con, 'rep_customer')) return [];
    $sql = "SELECT rcust_id, rcust_Phone FROM rep_customer WHERE 1";
    return db_fetch_all($con, $sql);
}

function sum_date_formate($date) {
    return $date ? date('d-m-Y', strtotime($date)) : '';
}

function get_Status($status) {
    if ($status=='I')  return "<label class='label label-primary'>In-process</label>";
    if ($status=='R')  return "<label class='label label-success'>Returned</label>";
    if ($status=='UR') return "<label class='label label-warning'>Return Without Repair</label>";
    if ($status=='FP') return "<label class='label label-danger'>Further Pending</label>";
    return "<label class='label label-default'>Unknown</label>";
}

function get_AmountDrCr($value) {
    if ($value > 0)  return "Dr";
    if ($value < 0)  return "Cr";
    return "-";
}

function get_LedgerTrnType($status) {
    if ($status=='sale')             return "Sale";
    if ($status=='salereturn')       return "Sale Return";
    if ($status=='salepayment')      return "Payment";
    if ($status=='salereturnpayment')return "Payment";
    return "-";
}

function get_LedgerTrnLink($trn_status, $trn_id) {
    $trn_id = urlencode($trn_id);
    if ($trn_status=='sale')             return "sale_add?id={$trn_id}";
    if ($trn_status=='salereturn')       return "salereturn_list";
    if ($trn_status=='salepayment')      return "sale_payment_list";
    if ($trn_status=='salereturnpayment')return "sale_payment_list";
    return "javascript:";
}

function get_SaleDetail_itemDate($data) {
    global $con;
    $data = mysqli_real_escape_string($con, $data);
    $sql = "SELECT GROUP_CONCAT(item_Name, ' @ ', cust_sale_detail.item_discount_amount_per_item,' x ', cust_sale_detail.item_Qty) AS item_data
            FROM cust_sale_detail
            LEFT JOIN adm_item ON adm_item.item_id = cust_sale_detail.item_id
            INNER JOIN cust_sale ON cust_sale.s_id = cust_sale_detail.s_id
            WHERE cust_sale.s_Number = '{$data}'";
    $res = mysqli_query($con, $sql);
    if ($res) {
        $row = mysqli_fetch_assoc($res);
        mysqli_free_result($res);
        return $row && isset($row['item_data']) ? $row['item_data'] : '';
    } else {
        error_log("get_SaleDetail_itemDate SQL error: " . mysqli_error($con));
        return '';
    }
}

function get_SaleReturnDetail_itemDate($data) {
    global $con;
    $data = mysqli_real_escape_string($con, $data);
    $sql = "SELECT GROUP_CONCAT(item_Name, ' @ ', cust_salereturn_detail.item_discount_amount_per_item,' x ', cust_salereturn_detail.item_Qty) AS item_data
            FROM cust_salereturn_detail
            LEFT JOIN adm_item ON adm_item.item_id = cust_salereturn_detail.item_id
            INNER JOIN cust_salereturn ON cust_salereturn.sr_id = cust_salereturn_detail.sr_id
            WHERE cust_salereturn.sr_Number = '{$data}'";
    $res = mysqli_query($con, $sql);
    if ($res) {
        $row = mysqli_fetch_assoc($res);
        mysqli_free_result($res);
        return $row && isset($row['item_data']) ? $row['item_data'] : '';
    } else {
        error_log("get_SaleReturnDetail_itemDate SQL error: " . mysqli_error($con));
        return '';
    }
}

?>