<?php  

error_reporting(E_ALL);
ini_set('display_errors', 1);
//echo"<pre>";
//print_r($_REQUEST);
//exit;

include('sessionCheck.php');
include('connection.php');
include('functions.php');
require_once ("inc/init.php");
require_once ("inc/config.ui.php");
$page_title = "Billing";
include ("inc/header.php");
include('lib/lib_quotation1.php');
// Debug logging helper
function debug_log($msg) {
    file_put_contents(__DIR__.'/debug_sale_add.log', $msg.PHP_EOL, FILE_APPEND);
}

// ===== ZATCA (TLV QR) helpers for SALE SAVE TIME =====
function zatca_tlv($tag, $value) {
    $value = (string)$value;
    $len = strlen($value);
    return chr($tag) . chr($len) . $value;
}

function zatca_qr_base64($sellerName, $vatNo, $timestampIso, $totalWithVat, $vatAmount) {
    $tlv =
        zatca_tlv(1, $sellerName) .
        zatca_tlv(2, $vatNo) .
        zatca_tlv(3, $timestampIso) .
        zatca_tlv(4, $totalWithVat) .
        zatca_tlv(5, $vatAmount);
    return base64_encode($tlv);
}

// Helper to safely pick an existing stock column in adm_item, or fall back to 0
if (!function_exists('detect_item_stock_expr')) {
    function detect_item_stock_expr($con) {
        static $expr = null;
        if ($expr !== null) return $expr;

        // Try common stock column names
        $candidates = [
            'item_CurrentStock',
            'item_Stock',
            'current_stock',
            'stock',
            'qty_on_hand',
            'quantity',
            'item_quantity',
        ];

        foreach ($candidates as $col) {
            $colEsc = mysqli_real_escape_string($con, $col);
            $sql = "SHOW COLUMNS FROM adm_item LIKE '{$colEsc}'";
            $res = @mysqli_query($con, $sql);
            if ($res && mysqli_num_rows($res) > 0) {
                // Use this existing column and alias it later as item_CurrentStock
                $expr = "adm_item.`$col`";
                return $expr;
            }
        }

        // Nothing found: return constant 0
        $expr = "0";
        return $expr;
    }
}
//
if(isset($_GET['id']))
{
	$id=(int)mysqli_real_escape_string($con,$_GET['id']);
	$Q="SELECT * FROM cust_sale WHERE s_id='".$id."'";
	
	$Qry=mysqli_query($con,$Q);
	$Rows=mysqli_num_rows($Qry);
	if($Rows!=1) { ?> <script> window.location.href='<?=$base_file?>';</script><?php die();}
	$Result=mysqli_fetch_object($Qry);


        // ✅ BEST PLACE: Block edit if invoice is already REPORTED
    $z = mysqli_fetch_assoc(mysqli_query($con, "
        SELECT status
        FROM zatca_invoice
        WHERE invoice_id = $id
        LIMIT 1
    "));
    $st = strtoupper(trim($z['status'] ?? ''));
    if ($st === 'REPORTED') {
        $_SESSION['msg'] = '<div class="alert alert-danger">This invoice is REPORTED to ZATCA, so it cannot be edited. Please use Credit Note (Sale Return).</div>';
        echo '<script>
            alert("This invoice is REPORTED to ZATCA, so it cannot be edited. Please use Credit Note (Sale Return).");
            window.location.href="sale_list.php";
        </script>';
        exit;
    }


	$s_Number = $Result->s_Number;
	$s_NumberSr = $Result->s_NumberSr;
	$s_Date = $Result->s_Date;
	$client_id = $Result->client_id;
	$s_TotalAmount = $Result->s_TotalAmount;
	$s_Discount = $Result->s_Discount;
	$s_DiscountAmount = $Result->s_DiscountAmount;
	$s_Tax = $Result->s_Tax;
	$s_TaxAmount = $Result->s_TaxAmount;
	$s_SaleMode = $Result->s_SaleMode;
	$s_PaidAmount = $Result->s_PaidAmount;
	$s_DiscountPrice = $Result->s_DiscountPrice;
	$s_NetAmount = $Result->s_NetAmount;
	$s_Remarks = $Result->s_Remarks;
	$s_RemarksExternal = $Result->s_RemarksExternal;
	$s_CreatedOn = $Result->s_CreatedOn;
	$s_TotalItems = $Result->s_TotalItems;
	$u_id = $Result->u_id;
	$branch_id = $Result->branch_id;
	$s_PaymentType = $Result->s_PaymentType;


	


    // Get the exact customer for this invoice
$accQ = "SELECT account_id, account_title, phone
         FROM accounts_chart
         WHERE account_id = " . (int)$client_id . "
         LIMIT 1";
$accR = mysqli_query($con, $accQ);

if ($accR && mysqli_num_rows($accR) === 1) {
    $acc = mysqli_fetch_assoc($accR);
    $ex_client_name  = $acc['account_title'] ?? '';
    $ex_client_phone = $acc['phone'] ?? '';
}
}




include ("inc/nav.php");
$branch_id=$_SESSION['branch_id'];
$u_id=$_SESSION['u_id'];
$s_NumberPrefix='INV';

/* Default customer: Automatically select "Customer Counter Sale" on NEW invoice */
if ((!isset($id) || $id <= 0) && empty($client_id)) {
    $defQ = "SELECT account_id, account_title, phone
             FROM accounts_chart
             WHERE branch_id = ".(int)$branch_id."
               AND LOWER(account_title) = 'customer counter sale'
             LIMIT 1";
    $defR = mysqli_query($con, $defQ);
    if ($defR && mysqli_num_rows($defR) === 1) {
        $def = mysqli_fetch_assoc($defR);
        $client_id       = (int)$def['account_id'];
        $ex_client_name  = $def['account_title'];
        $ex_client_phone = $def['phone'] ?? '';
    }
}
?>
<!-- ==========================CONTENT STARTS HERE ========================== -->
<!-- MAIN PANEL -->
<div id="main" role="main">
<style>
.itemdropdown_show{display:block;}
.itemdropdown_hide{display:none;}
</style>
<?php
$breadcrumbs["Billing"] = "";
//include("inc/ribbon.php");
 
?>
<style>
     .form-control {
    border-radius: 5px !important;
    box-shadow: none!important;
    -webkit-box-shadow: none!important;
    -moz-box-shadow: none!important;
    font-size: 12px;
    padding-left: 6px;
    padding: 0px 0px 0px 6px !important;

     }
     .select2-container .select2-choice {
   
    border-radius: 5px;
   
}
label {
   
    margin-top: 8px !important;
}
textarea.form-control {
    height: 70px;
}
select {
    display: block;
    height: 32px;
    padding: 0 0 0 8px;
    overflow: hidden;
    position: relative;
    border: 1px solid #ccc;
    white-space: nowrap;
    line-height: 32px;
    color: #444;
    text-decoration: none;
    background-clip: padding-box;
    -webkit-touch-callout: none;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
    background-color: #fff;
    border-radius: 5px !important;
    /*width: 400px;*/
}
b, strong {
    font-weight: 500;
}
.btn-group-xs>.btn, .btn-xs {
    padding: 1px 5px;
    font-size: 12px;
    line-height: 2.0;
    border-radius: 2px;
}

.goog-te-gadget .goog-te-combo {margin:0px !important;}




		
	
</style>
	<!-- MAIN CONTENT -->
	<div id="content">
		
		<!-- widget grid -->
		<section id="widget-grid" class="">
		
<!-- row -->
<div class="row">

	<!-- NEW WIDGET START -->
	<article class="col-xs-12 col-sm-12 col-md-12 col-lg-12">

		<!-- Widget ID (each widget will need unique ID)-->
		<div class="jarviswidget jarviswidget-color-purity" id="wid-id-1" data-widget-editbutton="false">
			<header>	
				<span class="small_icon"><i class="fa fa-file-text-o"></i>	</span>	
				<h2>Sale Invoice</h2>
			</header>

			<!-- widget div-->
			<div>		


<!-- widget content -->
<div class="widget-body no-padding">

<?php if(!empty($_SESSION['msg'])){ echo $_SESSION['msg']; unset($_SESSION['msg']);} ?>


<?php

if(isset($_POST['submit_new_customer'])) {
    $account_title = mysqli_real_escape_string($con, $_POST['account_title']);
    $phone = mysqli_real_escape_string($con, $_POST['phone']);
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $address = mysqli_real_escape_string($con, $_POST['address']);
    $remarks = mysqli_real_escape_string($con, $_POST['remarks']);
	$opening_debit = floatval($_POST['opening_debit']);
	$opening_credit = floatval($_POST['opening_credit']);
    $account_type = 'Asset'; // For customer
    $status = 'active';

    $q = "INSERT INTO accounts_chart (account_title, account_type, phone, email, opening_debit, opening_credit , address, remarks, status)
          VALUES ('$account_title', '$account_type', '$phone', '$email', '$opening_debit', '$opening_credit', '$address', '$remarks', '$status')";
    if(mysqli_query($con, $q)) {
        echo "<script>alert('Customer added successfully!');window.location='';</script>";
        exit;
    } else {
        echo "<script>alert('Error adding customer!');</script>";
    }
}




// REPLACE your entire `if (isset($_POST['post_form'])) { ... }` block with this one.

if (isset($_POST['post_form'])) {

    // --- Step 0: Verify required accounts (Sales and Cash) ---

    $branch_id = $_SESSION['branch_id'] ?? 1;

    // Find Sales Account (case-insensitive list)
    $sale_acc_row = mysqli_fetch_assoc(mysqli_query($con, "
        SELECT account_id FROM accounts_chart
        WHERE branch_id = '$branch_id'
          AND LOWER(account_title) IN (
            'sales','sale','sale account','sale a/c','sales a/c','sales account',
            'credit sale','credit sales','cash sale','cash sales'
          )
        LIMIT 1
    "));
    $sales_account_id = $sale_acc_row['account_id'] ?? 0;

    // Find Cash Account (case-insensitive list)
    $cash_acc_row = mysqli_fetch_assoc(mysqli_query($con, "
        SELECT account_id FROM accounts_chart
        WHERE branch_id = '$branch_id'
          AND LOWER(account_title) IN (
            'cash','cash account','cash in hand','cash a/c','petty cash',
            'cash on hand','cash balance','cash at hand','cash drawer','cash box','cash fund','cash float'
          )
        LIMIT 1
    "));
    $cash_account_id = $cash_acc_row['account_id'] ?? 0;

    if (!$sales_account_id) {
        $_SESSION['msg'] = '<div class="alert alert-danger">Sale Account not found in Chart of Accounts. Please create/select a Sale Account!</div>';
        echo '<script>alert("Sale Account not found in Chart of Accounts. Please create/select a Sale Account!");window.location.href="sale_add.php";</script>';
        exit;
    }
    if (!$cash_account_id) {
        $_SESSION['msg'] = '<div class="alert alert-danger">Cash Account not found in Chart of Accounts. Please create/select a Cash Account!</div>';
        echo '<script>alert("Cash Account not found in Chart of Accounts. Please create/select a Cash Account!");window.location.href="sale_add.php";</script>';
        exit;
    }

    // --- Step 1: Read POST and detect edit vs new ---

    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $is_edit = $id > 0;

    $error = 0;

    $location_after_save = 'window.location.href="sale_add"';
    $save_value = $_POST['save_value'] ?? '';
    if ($save_value === 'save_and_close') {
        $location_after_save = 'window.location.href="dashboard"';
    }

    $client_account_id = isset($_POST['client_id']) ? (int)$_POST['client_id'] : 0;

    // Fallback: if client not chosen in form, default to "Customer Counter Sale"
    if ($client_account_id <= 0) {
        $defQ = "SELECT account_id FROM accounts_chart
                 WHERE branch_id = ".(int)$branch_id."
                   AND LOWER(account_title) = 'customer counter sale'
                 LIMIT 1";
        $defR = mysqli_query($con, $defQ);
        if ($defR && mysqli_num_rows($defR) === 1) {
            $def = mysqli_fetch_assoc($defR);
            $client_account_id = (int)$def['account_id'];
        }
    }

    // Client name for voucher description
    $client_name = '';
    if ($client_account_id > 0) {
        $cn = mysqli_fetch_assoc(mysqli_query($con, "SELECT account_title FROM accounts_chart WHERE account_id=$client_account_id LIMIT 1"));
        $client_name = $cn['account_title'] ?? '';
    }

    $s_PaymentType     = $_POST['s_PaymentType'] ?? '';
    $s_SaleMode        = $_POST['s_SaleMode'] ?? '';
    $s_Remarks         = isset($_POST['s_Remarks']) ? mysqli_real_escape_string($con, $_POST['s_Remarks']) : '';
    $s_RemarksExternal = isset($_POST['s_RemarksExternal']) ? mysqli_real_escape_string($con, $_POST['s_RemarksExternal']) : '';

    $s_TotalItems      = (int)($_POST['s_TotalItems'] ?? 0);
    $s_TotalAmount     = (float)($_POST['s_TotalAmount'] ?? 0);
    $s_TaxAmount       = isset($_POST['s_TaxAmount']) ? (float)$_POST['s_TaxAmount'] : 0.0;
    $s_Tax             = isset($_POST['s_Tax']) ? (float)$_POST['s_Tax'] : 0.0;
    $s_DiscountAmount  = isset($_POST['s_DiscountAmount']) ? (float)$_POST['s_DiscountAmount'] : 0.0;
    $s_Discount        = isset($_POST['s_Discount']) ? (float)$_POST['s_Discount'] : 0.0;
    $s_DiscountPrice   = 0.0;
    $s_NetAmount       = (float)($_POST['s_NetAmount'] ?? 0);

    // --- New: Accept user provided date (allow back-date, block future unless you remove the check) ---
    $raw_date = trim($_POST['s_Date'] ?? '');
    $today = date('Y-m-d');
    if ($raw_date !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $raw_date)) {
        if ($raw_date > $today) {
            $s_Date = $today;
        } else {
            $s_Date = $raw_date;
        }
    } else {
        $s_Date = $today;
    }

    $current_datetime_sql = date('Y-m-d H:i:s');

    $item_idArray                 = $_POST['item_id'] ?? [];
    $item_BarCodeArray            = $_POST['item_Code'] ?? [];
    $item_IMEIArray               = $_POST['item_IMEI'] ?? [];
    $item_NameArray               = $_POST['item_Name'] ?? [];
    $item_SalePriceArray          = $_POST['item_Rate'] ?? [];
    $item_DiscountPercentageArray = $_POST['item_DiscountPercentage'] ?? [];
    $item_DiscountPriceArray      = $_POST['item_DiscountPrice'] ?? [];
    $item_QtyArray                = $_POST['item_Qty'] ?? [];
    $item_CostPriceArray          = $_POST['item_CostPrice'] ?? [];
    $item_NetPriceArray           = $_POST['item_NetPrice'] ?? [];

    $sp_Amount       = (float)($_POST['sp_Amount'] ?? 0);
    $show_prebalance = $_POST['show_prebalance'] ?? 0;
    $print_header    = $_POST['print_header'] ?? '';
    $print_size      = $_POST['print_size'] ?? '';
    $u_id            = $_SESSION['u_id'] ?? 1;
    $s_NumberPrefix  = 'INV';

    if (empty($item_idArray) && empty($item_IMEIArray)) {
        echo '<script> alert("At least 1 item must be selected"); window.location="";</script>';
        exit;
    }

    // If editing, preload & preserve invoice no/series/branch/date
    if ($is_edit) {
        $oldSale = mysqli_fetch_assoc(mysqli_query($con, "SELECT s_Number, s_NumberSr, branch_id, s_Date AS old_date FROM cust_sale WHERE s_id=$id LIMIT 1"));
        if ($oldSale) {
            $s_Number   = $oldSale['s_Number'];
            $s_NumberSr = (int)$oldSale['s_NumberSr'];
            $branch_id  = (int)$oldSale['branch_id'];
            if (trim($_POST['s_Date'] ?? '') === '') {
                $s_Date = $oldSale['old_date'];
            }
        } else {
            $is_edit = false;
        }
    }

    mysqli_begin_transaction($con);

    // --- Step 2: Insert/Update master sale row ---

    if (!$is_edit) {
        $NumberCheckQ = "SELECT MAX(s_NumberSr) as s_Number FROM cust_sale WHERE branch_id='$branch_id'";
        $NumberCheckRes = mysqli_query($con, $NumberCheckQ);
        $r = mysqli_fetch_assoc($NumberCheckRes);
        $s_NumberSr = isset($r['s_Number']) ? ((int)$r['s_Number'] + 1) : 1;
        $s_Number   = $s_NumberPrefix . $s_NumberSr;

        $sQ = "INSERT INTO cust_sale
               (s_Number, s_NumberSr, s_Date, client_id, s_TotalAmount, s_Discount, s_DiscountAmount, s_NetAmount,
                s_TotalItems, s_Remarks, s_RemarksExternal, s_CreatedOn, u_id, branch_id, s_PaymentType, s_SaleMode,
                s_PaidAmount, s_Tax, s_TaxAmount) 
               VALUES
               ('$s_Number','$s_NumberSr','$s_Date','$client_account_id','$s_TotalAmount','$s_Discount',
                '$s_DiscountAmount','$s_NetAmount', '$s_TotalItems', '$s_Remarks','$s_RemarksExternal',
                '$current_datetime_sql', '$u_id', '$branch_id', '$s_PaymentType', '$s_SaleMode', '$sp_Amount',
                '$s_Tax', '$s_TaxAmount')";
        if (!mysqli_query($con, $sQ)) {
            $error++;
            debug_log("Insert cust_sale error: " . mysqli_error($con));
        } else {
            $id = (int)mysqli_insert_id($con);
        }
    } else {
        $uQ = "UPDATE cust_sale SET
                s_Date = '$s_Date',
                client_id = '$client_account_id',
                s_TotalAmount = '$s_TotalAmount',
                s_Discount = '$s_Discount',
                s_DiscountAmount = '$s_DiscountAmount',
                s_Tax = '$s_Tax',
                s_TaxAmount = '$s_TaxAmount',
                s_SaleMode = '$s_SaleMode',
                s_PaidAmount = '$sp_Amount',
                s_DiscountPrice = '$s_DiscountPrice',
                s_NetAmount = '$s_NetAmount',
                s_Remarks = '$s_Remarks',
                s_RemarksExternal = '$s_RemarksExternal',
                s_TotalItems = '$s_TotalItems',
                s_PaymentType = '$s_PaymentType'
              WHERE s_id = $id";
        if (!mysqli_query($con, $uQ)) {
            $error++;
            debug_log("Update cust_sale error: " . mysqli_error($con));
        }
    }

    // --- Step 3: If editing, purge old children (details, payments, vouchers) ---

    if ($error === 0 && $is_edit) {
        if (!mysqli_query($con, "DELETE FROM cust_sale_detail WHERE s_id=$id")) {
            $error++; debug_log("Delete cust_sale_detail error: " . mysqli_error($con));
        }
        if (!mysqli_query($con, "DELETE FROM adm_sale_payment WHERE s_id2=$id")) {
            $error++; debug_log("Delete adm_sale_payment error: " . mysqli_error($con));
        }
        $vn = mysqli_real_escape_string($con, $s_Number ?? '');
        $vp = mysqli_real_escape_string($con, ($s_Number ?? '') . '-PAY');
        if ($vn !== '') {
            $vd = mysqli_query($con, "SELECT voucher_id FROM accounts_voucher WHERE voucher_no IN ('$vn','$vp')");
            if ($vd) {
                while ($vr = mysqli_fetch_assoc($vd)) {
                    $vid = (int)$vr['voucher_id'];
                    mysqli_query($con, "DELETE FROM accounts_voucher_detail WHERE voucher_id=$vid");
                }
            }
            mysqli_query($con, "DELETE FROM accounts_voucher WHERE voucher_no IN ('$vn','$vp')");
        }
    }

    // --- Step 4: Insert sale detail rows (handle sets) ---

    $sale_items_desc_arr = [];
    $sale_detail_items_inserted = false;

    if ($error === 0) {
        $maxCount = max(count($item_NameArray), count($item_idArray), count($item_QtyArray));
        for ($key = 0; $key < $maxCount; $key++) {
            $product_name = isset($item_NameArray[$key]) ? trim($item_NameArray[$key]) : '';
            $itm_id       = isset($item_idArray[$key])   ? (int)$item_idArray[$key]   : 0;
            $qty          = isset($item_QtyArray[$key])  ? (int)$item_QtyArray[$key]  : 0;
            $item_BarCode = isset($item_BarCodeArray[$key]) ? mysqli_real_escape_string($con, (string)$item_BarCodeArray[$key]) : '';
            $item_IMEI    = isset($item_IMEIArray[$key]) ? mysqli_real_escape_string($con, (string)$item_IMEIArray[$key]) : '';
            $rate         = isset($item_SalePriceArray[$key]) ? (float)$item_SalePriceArray[$key] : 0.0;
            $cost_price   = isset($item_CostPriceArray[$key]) ? (float)$item_CostPriceArray[$key] : 0.0;
            $discount     = isset($item_DiscountPriceArray[$key]) ? (float)$item_DiscountPriceArray[$key] : 0.0;
            $net_price    = isset($item_NetPriceArray[$key]) ? (float)$item_NetPriceArray[$key] : ($qty * $rate);

            if ($product_name === '' || $itm_id <= 0 || $qty <= 0) continue;

            if (strpos($product_name, '[SET]') === 0) {
                $set_name = trim(str_replace('[SET]', '', $product_name));
                $set_row = mysqli_fetch_assoc(mysqli_query($con, "SELECT set_id FROM adm_itemset WHERE TRIM(set_name)='" . mysqli_real_escape_string($con, $set_name) . "' LIMIT 1"));
                if ($set_row) {
                    $set_id = (int)$set_row['set_id'];
                    $set_items_q = mysqli_query($con, "SELECT i.item_id, i.item_Name, s.quantity, i.item_Code, i.item_SalePrice
                                                       FROM adm_itemset_detail s
                                                       JOIN adm_item i ON s.item_id = i.item_id
                                                       WHERE s.set_id = $set_id");
                    while ($si = mysqli_fetch_assoc($set_items_q)) {
                        $rate_set_item = (float)($si['item_SalePrice'] ?? 0);
                        $sale_items_desc_arr[] = "{$si['item_Name']} - {$si['quantity']} pcs @ {$rate_set_item}";
                        $line_net = $si['quantity'] * $rate_set_item;

                        $sdQ = "INSERT INTO cust_sale_detail
                                (s_id, sd_Date, item_id, item_BarCode, item_IMEI, item_Qty, item_SalePrice,
                                 item_DiscountPercentage, item_DiscountPrice, item_discount_amount_per_item,
                                 item_CostPrice, item_NetPrice, sd_CreatedOn, client_id)
                                VALUES
                                ($id, '$s_Date', {$si['item_id']}, '" . mysqli_real_escape_string($con, $si['item_Code']) . "', '',
                                 '{$si['quantity']}', '$rate_set_item', '0', '0', '0', '0',
                                 '$line_net', '$current_datetime_sql', $client_account_id)";
                        if (!mysqli_query($con, $sdQ)) {
                            $error++; debug_log("Insert SET detail error: " . mysqli_error($con));
                            break;
                        }
                        $sale_detail_items_inserted = true;
                    }
                }
            } else {
                $sale_items_desc_arr[] = "{$product_name} - {$qty} pcs @ {$rate}";
                $sdQ = "INSERT INTO cust_sale_detail
                        (s_id, sd_Date, item_id, item_BarCode, item_IMEI, item_Qty, item_SalePrice,
                         item_DiscountPercentage, item_DiscountPrice, item_discount_amount_per_item,
                         item_CostPrice, item_NetPrice, sd_CreatedOn, client_id)
                        VALUES
                        ($id, '$s_Date', $itm_id, '$item_BarCode', '$item_IMEI', '$qty', '$rate',
                         '0', '$discount', '0', '$cost_price', '$net_price', '$current_datetime_sql', $client_account_id)";
                if (!mysqli_query($con, $sdQ)) {
                    $error++; debug_log("Insert detail error: " . mysqli_error($con));
                    break;
                }
                $sale_detail_items_inserted = true;
            }
        }
    }

    if ($error === 0) {
        $detailCountRow = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) AS c FROM cust_sale_detail WHERE s_id=$id"));
        $detailCount = (int)($detailCountRow['c'] ?? 0);
        if ($detailCount === 0) {
            $error++;
            $_SESSION['msg'] = '<div class="alert alert-danger">No Record Found: No products selected for invoice (single or set).</div>';
        }
    }

    // --- Step 5: Create accounting vouchers ---

    if ($error === 0) {
        $voucher_type = 'Sale';
        $voucher_no   = $s_Number ?? '';
        $voucher_desc = 'Sale Invoice #' . $voucher_no . ' (' . $client_name . ')';

        $q_voucher = "INSERT INTO accounts_voucher (entry_date, voucher_type, voucher_no, description, created_by)
                      VALUES ('$s_Date', '$voucher_type', '" . mysqli_real_escape_string($con, $voucher_no) . "', '" . mysqli_real_escape_string($con, $voucher_desc) . "', '$u_id')";
        if (!mysqli_query($con, $q_voucher)) {
            $error++; debug_log("Insert sale voucher error: " . mysqli_error($con));
        } else {
            $sale_voucher_id = (int)mysqli_insert_id($con);
            $sale_items_desc = mysqli_real_escape_string($con, implode(', ', $sale_items_desc_arr));
            if (!mysqli_query($con, "INSERT INTO accounts_voucher_detail (voucher_id, account_id, description, debit, credit)
                                     VALUES ($sale_voucher_id, $client_account_id, '$sale_items_desc', $s_NetAmount, 0)")) { $error++; }
            if (!mysqli_query($con, "INSERT INTO accounts_voucher_detail (voucher_id, account_id, description, debit, credit)
                                     VALUES ($sale_voucher_id, $sales_account_id, '$sale_items_desc', 0, $s_NetAmount)")) { $error++; }
        }

        if ($sp_Amount > 0 && $error === 0) {
            $sp_Description = "Received against Invoice# $s_Number";
            $spQ = "INSERT INTO adm_sale_payment(sp_Date, client_id, sp_Amount, s_id, s_id2, sp_Description, sp_Type, sp_CreatedOn, u_id, branch_id)
                    VALUES ('$s_Date', $client_account_id, '$sp_Amount', $id, $id, '" . mysqli_real_escape_string($con, $sp_Description) . "', 'S', NOW(), '$u_id', '$branch_id')";
            if (!mysqli_query($con, $spQ)) {
                $error++; debug_log("Insert sale payment error: " . mysqli_error($con));
            } else {
                $voucher_type = 'Payment';
                $voucher_no   = $s_Number . '-PAY';
                $voucher_desc = "Payment against Sale Invoice #$s_Number";
                $q_voucher = "INSERT INTO accounts_voucher (entry_date, voucher_type, voucher_no, description, created_by)
                              VALUES ('$s_Date', '$voucher_type', '" . mysqli_real_escape_string($con, $voucher_no) . "', '" . mysqli_real_escape_string($con, $voucher_desc) . "', '$u_id')";
                if (!mysqli_query($con, $q_voucher)) {
                    $error++; debug_log("Insert payment voucher error: " . mysqli_error($con));
                } else {
                    $pay_voucher_id = (int)mysqli_insert_id($con);
                    if (!mysqli_query($con, "INSERT INTO accounts_voucher_detail (voucher_id, account_id, description, debit, credit)
                                             VALUES ($pay_voucher_id, $cash_account_id, '" . mysqli_real_escape_string($con, $voucher_desc) . "', $sp_Amount, 0)")) { $error++; }
                    if (!mysqli_query($con, "INSERT INTO accounts_voucher_detail (voucher_id, account_id, description, debit, credit)
                                             VALUES ($pay_voucher_id, $client_account_id, '" . mysqli_real_escape_string($con, $voucher_desc) . "', 0, $sp_Amount)")) { $error++; }
                }
            }
        }
    }

    // ===== ZATCA: generate + store BEFORE commit (SALE SAVE TIME) =====
    if ($error === 0) {

        $brRes = mysqli_query($con, "SELECT branch_Name, zatca_seller_vat, zatca_seller_legal_name FROM adm_branch WHERE branch_id=$branch_id LIMIT 1");
        $br = $brRes ? mysqli_fetch_assoc($brRes) : null;

        $seller_name = trim((string)($br['zatca_seller_legal_name'] ?? ''));
        if ($seller_name === '') $seller_name = (string)($br['branch_Name'] ?? '');

        $vat_no = trim((string)($br['zatca_seller_vat'] ?? ''));

        $dt = new DateTime($current_datetime_sql ?? date('Y-m-d H:i:s'));
        $dt->setTimezone(new DateTimeZone('Asia/Riyadh'));
        $timestamp_iso = $dt->format('Y-m-d\TH:i:sP');

        $total_with_vat = number_format((float)$s_NetAmount, 2, '.', '');

        $vat_amount_val = (float)$s_TaxAmount;
        if ($vat_amount_val <= 0 && (float)$s_Tax > 0) {
            $vat_amount_val = ((float)$s_NetAmount * (float)$s_Tax) / 100.0;
        }
        $vat_amount = number_format($vat_amount_val, 2, '.', '');

        try {
            $uuid = bin2hex(random_bytes(16));
        } catch (Exception $e) {
            $uuid = md5(uniqid((string)$id, true));
        }

        $hash_input = ($s_Number ?? '') . '|' . $timestamp_iso . '|' . $total_with_vat . '|' . $vat_amount . '|' . $vat_no;
        $invoice_hash = hash('sha256', $hash_input);

        $qr_base64 = null;
        if ($seller_name !== '' && $vat_no !== '' && (float)$total_with_vat > 0) {
            $qr_base64 = zatca_qr_base64($seller_name, $vat_no, $timestamp_iso, $total_with_vat, $vat_amount);
        }

        $invNoEsc = mysqli_real_escape_string($con, (string)($s_Number ?? ''));
        $uuidEsc  = mysqli_real_escape_string($con, $uuid);
        $hashEsc  = mysqli_real_escape_string($con, $invoice_hash);
        $qrEsc    = $qr_base64 !== null ? ("'".mysqli_real_escape_string($con, $qr_base64)."'") : "NULL";

        if ($invNoEsc !== '') {
            $existsRes = mysqli_query($con, "SELECT id FROM zatca_invoice WHERE branch_id=$branch_id AND invoice_no='$invNoEsc' LIMIT 1");
            if ($existsRes && mysqli_num_rows($existsRes) > 0) {
                $row = mysqli_fetch_assoc($existsRes);
                $zid = (int)$row['id'];
                $zq = "
                    UPDATE zatca_invoice
                    SET invoice_id=$id, uuid='$uuidEsc', invoice_hash='$hashEsc', qr_base64=$qrEsc,
                        status='PENDING', updated_at=NOW()
                    WHERE id=$zid
                ";
                if (!mysqli_query($con, $zq)) { $error++; debug_log('ZATCA update error: ' . mysqli_error($con)); }
            } else {
                $zq = "
                    INSERT INTO zatca_invoice (branch_id, invoice_no, invoice_id, uuid, invoice_hash, qr_base64, status, created_at)
                    VALUES ($branch_id, '$invNoEsc', $id, '$uuidEsc', '$hashEsc', $qrEsc, 'PENDING', NOW())
                ";
                if (!mysqli_query($con, $zq)) { $error++; debug_log('ZATCA insert error: ' . mysqli_error($con)); }
            }
        } else {
            $error++;
            debug_log('ZATCA skipped: invoice_no empty');
        }
    }

    // --- Step 6: Commit / Rollback ---

    if ($error === 0) {
        mysqli_commit($con);
        ?>
        <script>
            window.open('invoice_print.php?s_id=<?=$id?>&show_prebalance=<?=$show_prebalance?>&print_size=<?=$print_size?>&print_header=<?=$print_header?>',
                'popUpWindow',
                'resizable=yes,scrollbars=yes,toolbar=no,menubar=no,location=no,directories=no,status=no');
            <?=$location_after_save;?>;
        </script>
        <?php
        exit;
    } else {
        mysqli_rollback($con);
        $_SESSION['msg'] = '<div class="alert alert-danger">Problem Saving Sale. Please try again.</div>';
    }
}
?>

<?php if(!empty($_SESSION['msg'])){ echo $_SESSION['msg'];unset($_SESSION['msg']);} ?>

<?php
$allowTarget = mysqli_fetch_assoc(mysqli_query($con, "SELECT branch_SalesTargetAllow, branch_SalesVAT, branch_SalesBtnShow FROM adm_branch WHERE branch_id = $branch_id"));
$allowTargetAllowed = $allowTarget['branch_SalesTargetAllow'];
$branch_SalesBtnShow = $allowTarget['branch_SalesBtnShow'];
$branch_SalesVAT = $allowTarget['branch_SalesVAT'];
?>
	<form id="checkout-form" class="smart-form" novalidate="novalidate" method="post" action="" onsubmit="return checkParameters();">
		<fieldset style="padding:0px 10px 10px 10px !important;">
		<input type="hidden" name="id" id="id" value="<?=isset($id) ? $id : '0'?>" />

		<div class="row" style="margin-top:10px">
				<div class="col col-lg-12 col-md-12 col-xs-12">
                <table style="width:65%; background:#f1f1f1; float:left;" class="table table-condensed">
    <tr>
        <th style="width:16%; line-height:24px;">Invoice No.</th>
        <th style="width:17%; line-height:24px;">Date</th>
        <th style="width:37%; line-height:24px;">Search Customer (Name)</th>
        <th style="width:30%; line-height:24px;">Search / Add (Phone)</th>
    </tr>
    <tr>
        <!-- Invoice No -->
        <td>
            <?php
            // Agar edit mode hai to $s_Number already set hoga (aapke upar ke code me SELECT * se)
            if (!isset($s_Number) || empty($s_Number)) {
                $nextSrRow = mysqli_fetch_assoc(mysqli_query(
                    $con,
                    "SELECT (IFNULL(MAX(s_NumberSr),0)+1) AS next_sr FROM cust_sale WHERE branch_id=$branch_id"
                ));
                $nextSr = $nextSrRow['next_sr'] ?? 1;
                $s_Number = $s_NumberPrefix . $nextSr;
            }
            ?>
            <span style="font-size:20px; color:#d65252; font-weight:600;"><?=$s_Number?></span>
        </td>

        <!-- Date -->
        <td>
            <?php
                // Edit mode me $s_Date DB se aaya; new mode me default today
                $invoice_date_value = isset($s_Date) && $s_Date ? $s_Date : date('Y-m-d');
            ?>
            <input type="date"
                   name="s_Date"
                   id="s_Date"
                   class="form-control"
                   value="<?=$invoice_date_value?>"
                   max="<?=date('Y-m-d');?>"
                   style="padding-left:6px;">
        </td>

        <!-- Search Customer by Name -->
        <td>
            <input type="hidden" class="form-control" name="client_id" id="client_id"
                   value="<?= isset($client_id) ? (int)$client_id : '' ?>">

            <input list="client_name_list"
                   name="ex_client_name"
                   id="ex_client_name"
                   class="form-control"
                   value="<?= isset($ex_client_name) ? htmlspecialchars($ex_client_name) : '' ?>"
                   placeholder="Search By Name OR Enter New"
                   required autocomplete="off"
                   style="padding-left:8px;">

            <datalist id="client_name_list">
                <?php
                $itemsArray = get_AccountClientList();
                if (!empty($itemsArray)) {
                    foreach ($itemsArray as $itemRow) {
                        $cid   = (int)$itemRow['account_id'];
                        $cname = htmlspecialchars($itemRow['account_title']);
                        $cphone= htmlspecialchars($itemRow['phone']);
                        echo "<option value=\"{$cname}\" data-client-id=\"{$cid}\" data-client-phone=\"{$cphone}\"></option>";
                    }
                }
                ?>
            </datalist>
        </td>

        <!-- Search/Add Customer by Phone -->
        <td>
    <input list="client_phone_list"
           name="ex_client_phone"
           id="ex_client_phone"
           class="form-control"
           value="<?= isset($ex_client_phone) ? htmlspecialchars($ex_client_phone) : '' ?>"
           placeholder="Search by Phone OR Enter Phone"
           autocomplete="off"
           style="padding-left:8px;">

    <datalist id="client_phone_list">
        <?php
        if (!empty($itemsArray)) {
            foreach ($itemsArray as $itemRow) {
                $cid   = (int)$itemRow['account_id'];
                $cname = htmlspecialchars($itemRow['account_title']);
                $cphone= htmlspecialchars($itemRow['phone']);
                echo "<option value=\"{$cphone}\" data-client-id=\"{$cid}\" data-client-name=\"{$cname}\"></option>";
            }
        }
        ?>
    </datalist>
</td>
    </tr>

    <!-- Optional empty spacer row (can remove if not needed) -->
    <tr>
        <td colspan="4" style="padding:0; border:none;"></td>
    </tr>
</table>
                

                
                


            <?php
            if(isset($id) && $id>0)    
            	{ ?>
                <table style="width: 10%; float: right; margin-right: 1%;" class="table-bordered"><tr><th><a href="javascript:del(<?=$id?>)" class="btn btn-danger btn-xs" style="width: 100%;">Delete</a></th></tr></table>
			<?php
				}
			if($allowTargetAllowed==1)
			{
				$current_date=date("Y-m-d");
				$month_first_date=date("01-m-d");
				$targetlight_bg='red';
				$sales_target=$sales_percentage=$total_sales_of_month=0;
				$TargetQ="SELECT branch_SalesTarget, sum(s_NetAmount) as total_sales_of_month
						FROM `adm_branch`
						INNER JOIN cust_sale on cust_sale.branch_id=adm_branch.branch_id
						WHERE adm_branch.branch_id=$branch_id AND s_Date>='$month_first_date' AND s_Date<='$current_date'";
				$TargetQr=mysqli_query($con,$TargetQ);
				$TargetQrow=mysqli_fetch_assoc($TargetQr);
				$sales_target=$TargetQrow['branch_SalesTarget'];
				$total_sales_of_month=$TargetQrow['total_sales_of_month'];

				if($sales_target!=0)
				{
					$sales_percentage=($total_sales_of_month/$sales_target*100);
					$sales_percentage=$sales_percentage;
					if($sales_percentage>=100) {$sales_percentage=100.00; $targetlight_bg='green';}
				}
				?>
				<table style="width:13%; float:right;" class="table table-condensed">
                	<tr>
                    	<th style="text-align:center;">
                        	<span style=" margin:0 auto; background-color:<?=$targetlight_bg?>; border-radius:50%; height:70px; width:70px; display:block;">&nbsp;</span>
                            % <?=number_format($sales_percentage,2);?>
                        </th>
                    </tr>
                    <tr>
                    	<td><span style="float:left">Target</span><span style="float:right;"><?php echo $currency_symbol. number_format($sales_target,2)?></span></td>
                    </tr>
                </table>
             <?php } ?>
                </div>
			</div><!--End of row-->
            
            <div class="row" style="margin-top:5px">
            <div class="col col-lg-12 col-xs-12 col-md-12">
				<table style="width:100%; background:#f1f1f1;" class="table table-condensed">
					<tr>
						<!--<th style="width:16%;">Product IMEI</th>-->
						<th style="width:13%;">Product Code</th>
						<th style="width:20%;">Search Product Name</th>
						<!--<th style="width:6%;">Qty Pack</th>-->
						<th style="width:6%;">Stock</th>
                        <th style="width:7%;">Set Qty</th>
						<th style="width:7%;">Quantity</th>
						<th style="width:8%;">Unit Price</th>
						<th style="width:6%;">Discount(Rs)</th>
						<th style="width:8%;">Total</th>
						<th style="width:8%;">Cost Price</th>
						<th rowspan="2" style="width:5%;">
							<p class="btn btn-primary" style="background:#09F; border:none; padding:12px; font-size:25px;" onclick="addToTable();"><i class="fa fa-shopping-cart"></i></p>
						</th>
					</tr>
					<tr>
						<input type="hidden"  id="ex_itemname" class="form-control" >
						<input type="hidden"  id="ex_item_stock" class="form-control" >
						<input type="hidden"  id="ex_item_id_from_imei" class="form-control" >
						<td><input type="text"  id="ex_itemcode" placeholder="Enter Product Code" class="form-control" style="font-size: 12px;padding-left: 8px;" ></td>
						<td>
							<input list="item_list"  name="" id="ex_item" class="form-control" placeholder="Search Product by Name OR Enter Product Name" required autocomplete="off"  style="padding-left:8px;">
							<datalist id="item_list">
									<?php 
									$itemsArray = get_ActiveItems();
									foreach ($itemsArray as $key => $itemRow) { ?>
										<option value="<?php echo $itemRow['item_Name'];?>" data-item-id="<?php echo $itemRow['item_id'];?>">
									<?php } 

									// Sets/Bundles show karein
									$setsQ = mysqli_query($con, "SELECT set_id, set_name FROM adm_itemset");
									while($setRow = mysqli_fetch_assoc($setsQ)) {
										echo '<option value="[SET] '.$setRow['set_name'].'" data-set-id="set_'.$setRow['set_id'].'">';
									}
									?>
								</datalist>
						</td>
						<td><input type="number" id="ex_stock" class="form-control" style="text-align:center;" readonly="readonly"></td>
                        <td><!-- set qty (entry row not needed) --></td>
						<td><input type="number" id="ex_qty" class="form-control" style="text-align:center;" onchange="calculate_netamount_row()" onkeyup="calculate_netamount_row()" autocomplete="off"></td>
						<td><input type="number" id="ex_rate" class="form-control"  style="text-align:right" onchange="calculate_netamount_row()" onkeyup="calculate_netamount_row()" autocomplete="off"></td>
						<td><input type="number" id="ex_discount_amount" class="form-control"  style="text-align:right" onchange="calculate_netamount_row()" onkeyup="calculate_netamount_row()" autocomplete="off"></td>
						<td><input type="number" id="ex_netamount" class="form-control" style="text-align:right" readonly="readonly" ></td>
						<td><input type="text" id="ex_costprice" class="form-control" ></td>
						<input type="hidden" id="ex_discount_percentage">
					</tr>
				</table>
            </div>
            </div>	
			<table class="table table-bordered" style="width:100%;margin-top:10px;" id="u_tbl">
    <thead>
        <tr>
            <th style="width:13%;">Product Code</th>
            <th style="width:20%;">Product Name</th>
            <th>Stock</th>
            <th style="width:7%">Set Qty</th>
            <th style="width:9%">Quantity</th>
            <th style="width:9%;">Unit Price</th>
            <th style="width:8%;">Discount Amt</th>
            <th style="width:8%">Cost Price</th>
            <th style="width:9%">Ext Amount</th>
            <th style="width:9%;">Action</th>
        </tr>
    </thead>
    <tbody>
        <?php
        if(isset($id) && $id>0)
        {
            $stockExpr = detect_item_stock_expr($con);

$detailQ = "
    SELECT
        cust_sale_detail.*,
        adm_item.item_Name,
        adm_item.item_Code,
        $stockExpr AS item_CurrentStock
    FROM cust_sale_detail
    LEFT OUTER JOIN adm_item
        ON adm_item.item_id = cust_sale_detail.item_id
    WHERE cust_sale_detail.s_id = $id
";
$detailR = mysqli_query($con, $detailQ);
            while($editDrows=mysqli_fetch_assoc($detailR)){
        ?>
            <tr>
                <td><?=$editDrows['item_Code']?></td>
                <td><?=$editDrows['item_Name']?></td>
                <td><?=$editDrows['item_CurrentStock']?></td>
                <td>-</td>
                <td>
                    <input type="number" name="item_Qty[]" class="item_Qty" value="<?=$editDrows['item_Qty']?>" min="1" oninput="rowInputChanged(this)">
                </td>
                <td>
                    <input type="number" name="item_Rate[]" class="item_Rate" value="<?=$editDrows['item_SalePrice']?>" min="0" oninput="rowInputChanged(this)">
                </td>
                <td>
                    <input type="number" name="item_DiscountPrice[]" class="item_DiscountPrice" value="<?=$editDrows['item_DiscountPrice']?>" min="0" oninput="rowInputChanged(this)">
                </td>
                <td style="text-align:right;">
                    <span class="item_CostPrice_show"><?=$editDrows['item_CostPrice']?></span>
                    <input type="hidden" name="item_CostPrice[]" class="item_CostPrice" value="<?=$editDrows['item_CostPrice']?>">
                </td>
                <td>
                    <span class="item_NetPrice_show"><?=number_format($editDrows['item_NetPrice'],2)?></span>
                    <input type="hidden" name="item_NetPrice[]" class="item_NetPrice" value="<?=$editDrows['item_NetPrice']?>">
                </td>
                <td>
                    <p class="btn btn-danger" onclick="delRow(this)">Delete</p>
                    <input type="hidden" name="item_id[]" class="item_id" value="<?=$editDrows['item_id']?>">
                    <input type="hidden" name="item_Code[]" value="<?=$editDrows['item_Code']?>">
                    <input type="hidden" name="item_Name[]" value="<?=$editDrows['item_Name']?>">
                </td>
            </tr>
        <?php
            }
        }
        ?>
    </tbody>
</table>

                <div class="" style="margin-top:10px">
                    <div class="col-lg-12 col-md-12 col-xs-12" >
                        <table style="width:100%; background:#f1f1f1;" class="table table-condensed" border="0">
                            <tr>
                                <th style="width:70%" rowspan="2">
                                	<table style="width:100%;" border="0">
                                    	<tr>
                                        	<td style="width:23%;">Discount(Rs)<br />
											<input type="number" name="s_DiscountAmount" id="s_DiscountAmount" placeholder="Enter Disc. %" class="form-control" onkeyup="calculate()"  onchange="calculate();" style="text-align:right;width: 90%;"  min="0" value="<?=isset($s_Discount) ? $s_Discount : '0'?>"></td>
                                        	<td style="width:1%;">&nbsp;</td>
                                           <td style="width:21%; display: none;">VAT(%)<br /><input type="number" name="s_Tax"  placeholder="Enter Tax %" id="s_Tax" class="form-control" style="text-align:right;width: 90%;" min="0" onkeyup="calculate()" onchange="calculate();" value="<?=isset($s_Tax) ? $s_Tax : '0'?>"></td>
                                            <td style="width:1%;">&nbsp;</td>
                                            <td style="width:30%;">
                                            	Payment Method<br />
                                            	<select name="s_PaymentType" style="width:100%; font-size: 15px; margin-top: 3px; height: 33px;">
                                                    <option value="cash" <?=isset($s_PaymentType) && $s_PaymentType=='cash' ? 'selected' : ''?>>Cash Payment</option>
                                                    <option value="bank" <?=isset($s_PaymentType) && $s_PaymentType=='bank' ? 'selected' : ''?>>Bank Payment</option>
                                                    <option value="creditcard" <?=isset($s_PaymentType) && $s_PaymentType=='creditcard' ? 'selected' : ''?>>Credit Card</option>
                                                </select>
                                            </td>
                                            <td style="width:1%;">&nbsp;</td>
                                            <td style="width:23%;">
                                            	Sale Mode<br />
                                            	<select name="s_SaleMode" id="s_SaleMode" style="width:100%; font-size: 15px; margin-top: 3px; height: 33px;">
                                                    <option value="cash"  <?=isset($s_SaleMode) && $s_SaleMode=='cash' ? 'selected' : ''?>selected>Cash</option>
                                                    <option value="credit"  <?=isset($s_SaleMode) && $s_SaleMode=='credit' ? 'selected' : ''?> >Credit</option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                        	<td style="display: none;">
                                            Disc. (Amount)<br />
                                            <h1 style="    color: black; padding:5px;font-size: 20px; text-align: right;background: lightgray;"><?=$currency_symbol?><span id="s_DiscountPriceShow"><?=isset($s_DiscountPrice) ? $s_DiscountPrice : '0'?></span></h1>
                                            <input type="hidden" name="s_DiscountPrice" id="s_DiscountPrice" class="form-control" style="text-align:right; width: 90%; font-size: 14px;" min="0" readonly="readonly" value="<?=isset($s_DiscountPrice) ? $s_DiscountPrice : '0'?>" >
                                            </td>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                            <td colspan="4"> 
                                            	Cash Received (Amount)<br /><input type="number" name="sp_Amount" id="sp_Amount" placeholder="Enter Received Amount" autocomplete="off" class="form-control" style="text-align:right;width: 98%; font-size: 20px;font-weight: 500;" min="0" value="<?=isset($s_PaidAmount) ? $s_PaidAmount : ''?>">
                                            </td>
                                            
                                        </tr>
                                    </table>
                                	
                                    
                                    
                                </th>
                                <th style="width:30%; background:#09F !important;">
                                    <p style="padding:0; float:left; color:#FFF;">Due Amount</p><p style="padding:0; margin:0; float:right; color:#FFF;">Total Items: <span id="totalItems"></span></p>
                                    <br />
                                    <h1 style=" color:#FFF; font-size:40px; text-align:center;"><?=$currency_symbol?> <span id="s_NetAmountShow">0</span></h1>
                                    <input type="hidden" name="s_NetAmount" id="s_NetAmount" value="0">
                                    <input type="hidden" name="s_TotalAmount" id="s_TotalAmount" value="0">
                                    
                                    <input type="hidden" name="s_TotalItems" id="s_TotalItems" value="0">
                                    
                                    <input type="hidden" name="save" value="1">
                                    <input type="hidden" name="save_value" id="save_value" value="save">
                                    <br />
                                    <p type="submit" class="btn btn-warning" style="font-weight: bold;
								    padding: 5px 30px;
								    background: orange;
								    border: none;
								    font-size: 15px;
								    color: saddlebrown;" id="submit" name="submit" onclick="saveForm('save');">Save </p>
								                                    <p type="submit" class="btn btn-warning" style="font-weight: bold;
								    padding: 5px 30px;
								    background: orange;
								    float: right;
								    border: none;
								    font-size: 15px;
								    color: saddlebrown;" id="submit" name="submit" onclick="saveForm('save_and_close');">Save & close</p>
                                </th>
                            </tr>
                           
                        </table>
                        <table style="width:100%; background:#f1f1f1;" class="table table-condensed" border="0">
                        	<tr>
                        		<th style="width: 20%;">Pre Balance</th>
                        		<th style="width: 20%;">Print Header</th>
                        		<th style="width: 20%;">Print Size</th>
                        	</tr>
                        	<tr>
                        		<th>
                        			<select class="form-control" name="show_prebalance">
									<option value="yes" selected="selected">Yes</option>
                        				<option value="no" >No</option>
                        			</select>
                        		</th>
                        		<th>
                        			<select class="form-control" name="print_header">
										<option value="yes" selected="selected" >Yes</option>
                        				<option value="no" >No</option>
                        			</select>
                        		</th>
                        		<th>
                        			<select class="form-control" name="print_size">
                        				<option value="thermal">Thermal</option>
                        				<option value="a4" selected="selected">A4</option>
                        				<option value="a4half">A4 Half</option>
                        				<option value="a5">A5</option>
                        			</select>
                        		</th>
                        	</tr>
                        </table>
                    </div>
                </div>

		</fieldset>
	<input type="hidden" name="post_form" />
	</form>

</div>

<audio id="product-add-sound" src="beep.mp3" preload="auto"></audio>


<div class="modal fade" id="NewCustomerModal"  role="dialog">
  <div class="modal-dialog  modal-lg">
    <div class="modal-content">
      <div class="modal-header alert alert-success">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title"><strong>Add New Customer</strong></h4>
      </div>
      <div class="modal-body ">
        <div id="msg2"></div>
		<form id="checkout-form1" class="smart-form" novalidate="novalidate" method="post" action=""  onsubmit="return checkParameters_NewCustomer();">	
          <fieldset>
            
            <div class="row" style="margin-bottom: 5px;">
				<div class="col col-lg-2"><label>Customer Name: <i class="fa fa-asterisk txt-color-red fa-xs" style="font-size: 6px;"></i></label></div>
				<div class="col col-lg-3"><input type="text" name="client_Name" placeholder="Customer Name" id="new_client_Name"  required="required" class="form-control input_field_popup one-edge-shadow"></div>
				<div class="col col-lg-2"><label>Customer Email:</label></div>
				<div class="col col-lg-3"><input type="text" name="client_Email" placeholder="Customer Email"  class="form-control input_field_popup one-edge-shadow"></div>
			</div>
            
            
        <div class="row" style="margin-bottom: 5px;">
			<div class="col col-lg-2">
 					<label>Phone Number:</label>
 			</div>
			<div class="col col-lg-3">
 					<input type="text" name="client_Phone" placeholder="Phone Number"  class="form-control input_field_popup one-edge-shadow">
 			</div>
			
		</div>
        
        
        <!--3rd row start here-->

		<div class="row" style="margin-bottom: 5px;">
			<div class="col col-lg-2">
 					<label>Address:</label>
 			</div>
			<div class="col col-lg-8">
 					<textarea name="client_Address" placeholder="Customer Address"  class="form-control input_field_popup one-edge-shadow" style="height: 60px;"></textarea> 
 			</div>
			<div class="col col-lg-3">
			
			</div>
		</div>    
        
        <div class="row" style="margin-bottom: 5px;">
				<div class="col col-lg-2">
 						<label> Notes:</label>
 				</div>
				<div class="col col-lg-8">
 					<textarea name="client_Remarks" placeholder="Customer Note" class="form-control input_field_popup one-edge-shadow" style="height: 60px;"></textarea> 
 				</div>
			</div>
            
            
            
            
          </fieldset>
          <div class="form-actions">
            <div class="row">
              <div class="col-md-12">
                <button type="submit" class="btn btn-primary btn-sm" name="submit_new_customer">Save</button>
				<button type="button" data-dismiss="modal" class="btn btn-danger btn-sm">Cancel</button>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>













<!-- end widget div -->

		</div>
		<!-- end widget -->


	</article>
	<!-- WIDGET END -->

</div>

<!-- end row -->
		
			<!-- end row -->
		
		</section>
		<!-- end widget grid -->


	</div>
	<!-- END MAIN CONTENT -->

</div>
<!-- END MAIN PANEL -->
<!-- ==========================CONTENT ENDS HERE ========================== -->

<!-- PAGE FOOTER -->

<?php include ("inc/footer.php");
 
?>
<!-- END PAGE FOOTER -->


<?php 
include ("inc/scripts.php");
 
?>

<!-- PAGE RELATED PLUGIN(S) -->
<script src="<?php echo ASSETS_URL;?>/js/plugin/datatables/jquery.dataTables.min.js"></script>
<script src="<?php echo ASSETS_URL;?>/js/plugin/datatables/dataTables.colVis.min.js"></script>
<script src="<?php echo ASSETS_URL;?>/js/plugin/datatables/dataTables.tableTools.min.js"></script>
<script src="<?php echo ASSETS_URL;?>/js/plugin/datatables/dataTables.bootstrap.min.js"></script>
<script src="<?php echo ASSETS_URL;?>/js/plugin/datatable-responsive/datatables.responsive.min.js"></script>
<script src="my_script.js"></script>


	<script>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById("ex_client_name").addEventListener("input", function() {
        var nameInput = this.value.toLowerCase();
        var dataList = document.getElementById("client_name_list");
        var phoneField = document.getElementById("ex_client_phone");
        var clientIdField = document.getElementById("client_id");
        var found = false;
        for (var i = 0; i < dataList.options.length; i++) {
            var option = dataList.options[i];
            if (option.value.toLowerCase() === nameInput) {
                phoneField.value = option.getAttribute("data-client-phone");
                clientIdField.value = option.getAttribute("data-client-id");
                found = true;
                break;
            }
        }
        if (!found) {
            phoneField.value = '';
            clientIdField.value = '';
        }
    });

    document.getElementById("ex_client_phone").addEventListener("input", function() {
        var phoneInput = this.value;
        var dataList = document.getElementById("client_phone_list");
        var nameField = document.getElementById("ex_client_name");
        var clientIdField = document.getElementById("client_id");
        var found = false;
        for (var i = 0; i < dataList.options.length; i++) {
            var option = dataList.options[i];
            if (option.value === phoneInput) {
                nameField.value = option.getAttribute("data-client-name");
                clientIdField.value = option.getAttribute("data-client-id");
                found = true;
                break;
            }
        }
        if (!found) {
            nameField.value = '';
            clientIdField.value = '';
        }
    });

    // Ensure hidden client_id is populated when the page opens with a prefilled client name (default Counter Sale)
    (function autoBindDefaultClient() {
        var nameField   = document.getElementById("ex_client_name");
        var phoneField  = document.getElementById("ex_client_phone");
        var clientIdFld = document.getElementById("client_id");
        var list        = document.getElementById("client_name_list");
        if (nameField && clientIdFld && list && nameField.value && !clientIdFld.value) {
            var target = nameField.value.toLowerCase();
            for (var i = 0; i < list.options.length; i++) {
                if (list.options[i].value.toLowerCase() === target) {
                    clientIdFld.value = list.options[i].getAttribute("data-client-id") || '';
                    if (!phoneField.value) {
                        phoneField.value = list.options[i].getAttribute("data-client-phone") || '';
                    }
                    break;
                }
            }
        }
    })();
});
</script> 
<script>
document.addEventListener("DOMContentLoaded", function() {
    var clientNameInput = document.getElementById('client_name');
    if(clientNameInput) {
        clientNameInput.addEventListener('input', function() {
            var val = this.value;
            var opts = document.getElementById('client_name_list').options;
            for (var i = 0; i < opts.length; i++) {
                if (opts[i].value === val) {
                    document.getElementById('client_account_id').value = opts[i].getAttribute('data-account-id');
                    break;
                }
            }
        });
    }
});
</script>