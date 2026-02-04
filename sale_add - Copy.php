<?php  
include('sessionCheck.php');
include('connection.php');
include('functions.php');
require_once ("inc/init.php");
require_once ("inc/config.ui.php");
$page_title = "Billing";
include ("inc/header.php");
include('lib/lib_quotation1.php');

if(isset($_GET['id']))
{
	$id=(int)mysqli_real_escape_string($con,$_GET['id']);
	$Q="SELECT * FROM cust_sale WHERE s_id='".$id."'";
	
	$Qry=mysqli_query($con,$Q);
	$Rows=mysqli_num_rows($Qry);
	if($Rows!=1) { ?> <script> window.location.href='<?=$base_file?>';</script><?php die();}
	$Result=mysqli_fetch_object($Qry);


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


	$Q="SELECT * FROM adm_client WHERE client_id='".$client_id."'";	
	$Qry=mysqli_query($con,$Q);
	$Result=mysqli_fetch_object($Qry);

	$ex_client_name=$Result->client_Name;
	$ex_client_phone=$Result->client_Phone;
}




include ("inc/nav.php");
$branch_id=$_SESSION['branch_id'];
$u_id=$_SESSION['u_id'];
$s_NumberPrefix='INV';
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


if(isset($_POST['post_form']))
{
    $id = validate_input($_POST['id']);
    $error = 0;

    $location_after_save = 'window.location.href="sale_add"';
    $save_value = $_POST['save_value'];
    
    // Now using Chart of Accounts customer
    $client_account_id = isset($_POST['client_account_id']) ? intval($_POST['client_account_id']) : 0;
    $client_name = isset($_POST['client_name']) ? mysqli_real_escape_string($con, $_POST['client_name']) : '';

    $s_PaymentType = $_POST['s_PaymentType'];
    $s_SaleMode = $_POST['s_SaleMode'];
    $s_Remarks = validate_input($_POST['s_Remarks']);
    $s_RemarksExternal = $_POST['s_RemarksExternal'];
    
    $s_TotalItems = $_POST['s_TotalItems'];
    $s_TotalAmount = $_POST['s_TotalAmount'];
    $s_TaxAmount = $_POST['s_TaxAmount']+0;
    $s_Tax = $_POST['s_Tax']+0;
    $s_DiscountAmount = $_POST['s_DiscountAmount']+0;
    $s_Discount = $_POST['s_Discount']+0;
    $s_DiscountPrice = 0;
    $s_NetAmount = $_POST['s_NetAmount'];
    $s_Date = date('Y-m-d');
    $current_datetime_sql = date('Y-m-d H:i:s');
    $item_idArray = $_POST['item_id'];
    $item_BarCodeArray = $_POST['item_Code'];
    $item_IMEIArray = isset($_POST['item_IMEI']) ? $_POST['item_IMEI'] : 0;
    $item_NameArray = $_POST['item_Name'];
    $item_SalePriceArray = $_POST['item_Rate'];
    $item_DiscountPercentageArray = $_POST['item_DiscountPercentage'];
    $item_DiscountPriceArray = $_POST['item_DiscountPrice'];
    $item_QtyArray = array_filter($_POST['item_Qty']);
    $item_CostPriceArray = $_POST['item_CostPrice'];
    $item_NetPriceArray = $_POST['item_NetPrice'];
    $sp_Amount = $_POST['sp_Amount'];
    $show_prebalance = $_POST['show_prebalance'];
    $print_header = $_POST['print_header'];
    $print_size = $_POST['print_size'];
    $branch_id = $_SESSION['branch_id'];
    $u_id = $_SESSION['u_id'];
    $s_NumberPrefix = 'INV';

    if($save_value=='save_and_close') {
        $location_after_save = 'window.location.href="dashboard"';
    }
    
    if(empty($item_idArray) && empty($item_IMEIArray))
    {
        echo '<script> alert("Atleast 1 item must be selected");
                window.location="";</script>';
        die();
    }

    // Generate invoice number
    if(empty($id))
    {
        $NumberCheckQ = "SELECT MAX(s_NumberSr) as s_Number FROM cust_sale WHERE branch_id='$branch_id'";
        $NumberCheckRes = mysqli_query($con, $NumberCheckQ);
        $r = mysqli_fetch_assoc($NumberCheckRes);
        $s_NumberSr = $r['s_Number'] + 1;
        $s_Number = $s_NumberPrefix . $s_NumberSr;

        // Insert into sale master table
        $sQ = "INSERT INTO cust_sale(s_Number, s_NumberSr, s_Date, client_id, s_TotalAmount, s_Discount, s_DiscountAmount, s_NetAmount, s_TotalItems, s_Remarks, s_RemarksExternal, s_CreatedOn, u_id, branch_id, s_PaymentType, s_SaleMode, s_PaidAmount, s_Tax, s_TaxAmount) 
               VALUES ('$s_Number','$s_NumberSr','$s_Date','$client_account_id','$s_TotalAmount','$s_Discount','$s_DiscountAmount','$s_NetAmount', '$s_TotalItems', '$s_Remarks','$s_RemarksExternal','$current_datetime_sql', '$u_id', '$branch_id', '$s_PaymentType', '$s_SaleMode', '$sp_Amount','$s_Tax', '$s_TaxAmount')";
        $inserted = mysqli_query($con, $sQ);
        if($inserted) {
            $id = mysqli_insert_id($con);
        } else {
            $error++;
        }
    }
    else
    {
        $updated = mysqli_query($con,"
            UPDATE `cust_sale` SET
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
            WHERE s_id = '".$id."'");
        if(!$updated) {
            $error++;
        }
    }

    mysqli_query($con, "DELETE FROM adm_sale_payment WHERE s_id2=$id");
    if(!empty($sp_Amount))
    {
        if($s_SaleMode=='cash' && $sp_Amount>$s_NetAmount) {$sp_Amount=$s_NetAmount;}
        $spQ="INSERT INTO adm_sale_payment(client_id, sp_Amount, sp_Date, s_id, s_id2, sp_Description,sp_Type, sp_CreatedOn,branch_id) VALUES ($client_account_id,'$sp_Amount','$s_Date','$id', '$id', 'Sale Payment', 'S', '$current_datetime_sql','$branch_id')";
        if(!mysqli_query($con,$spQ)) {
            $error++;
        }
    }

    if(empty($error))
    {
        mysqli_query($con,"DELETE FROM cust_sale_detail WHERE s_id=$id");
        foreach ($item_QtyArray as $key => $item_Qty)
        {
            $item_BarCode = isset($item_BarCodeArray[$key]) ? trim($item_BarCodeArray[$key]) : '';
            $item_IMEI = isset($item_IMEIArray[$key]) ? trim($item_IMEIArray[$key]) : '';
            $item_SalePrice = isset($item_SalePriceArray[$key]) ? floatval($item_SalePriceArray[$key]) : 0;
            $item_DiscountPercentage = isset($item_DiscountPercentageArray[$key]) ? floatval($item_DiscountPercentageArray[$key]) : 0;
            $item_DiscountPrice = isset($item_DiscountPriceArray[$key]) ? floatval($item_DiscountPriceArray[$key]) : 0;
            $item_Name = isset($item_NameArray[$key]) ? trim($item_NameArray[$key]) : '';
            $item_CostPrice = isset($item_CostPriceArray[$key]) ? floatval($item_CostPriceArray[$key]) : 0;
            $item_id = isset($item_idArray[$key]) ? $item_idArray[$key] : 0;
            $item_NetPrice = isset($item_NetPriceArray[$key]) ? floatval($item_NetPriceArray[$key]) : 0;

            if ((empty($item_BarCode) && empty($item_Name)) || empty($item_Qty) || $item_Qty <= 0) {
                continue;
            }

            $item_discount_amount_per_item = $item_Qty > 0 ? ($item_NetPrice / $item_Qty) : 0;

            if (empty($item_id) || $item_id == 0) {
                if (!empty($item_Name)) {
                    $oiidQ = "SELECT item_id FROM adm_item WHERE item_Name='" . mysqli_real_escape_string($con, $item_Name) . "' AND branch_id = $branch_id";
                    $oiidQr = mysqli_query($con, $oiidQ);
                    $oiid_rows = mysqli_num_rows($oiidQr);

                    if ($oiid_rows !== 1) {
                        $item_Code_Q = mysqli_fetch_assoc(mysqli_query($con, "SELECT (IFNULL(MAX(item_CodeSr),0)+1) AS item_CodeSr FROM adm_item WHERE branch_id=$branch_id"));
                        $item_CodeSr = $item_Code_Q['item_CodeSr'];
                        $item_CodeSrPAD = str_pad($item_CodeSr, 3, "0", STR_PAD_LEFT);
                        $code_prefix = 'PROC';
                        $item_Code = $code_prefix . $item_CodeSrPAD;

                        $add_other_itemQ = "INSERT INTO adm_item (
                                item_Name, item_Code, item_CodeSr, item_Status, item_Remarks, u_id, branch_id
                            ) VALUES (
                                '" . mysqli_real_escape_string($con, $item_Name) . "',
                                '$item_Code',
                                '$item_CodeSr',
                                'A',
                                'Mannualy added',
                                '$u_id',
                                '$branch_id'
                            )";
                        $add_other_item = mysqli_query($con, $add_other_itemQ);
                        $item_id_other = mysqli_insert_id($con);
                    } else {
                        $other_item_result = mysqli_fetch_assoc($oiidQr);
                        $item_id_other = $other_item_result['item_id'];
                    }
                    $item_id = $item_id_other;
                } else {
                    continue;
                }
            }

            $sdQ = "INSERT INTO cust_sale_detail (
                        s_id, sd_Date, item_id, item_BarCode, item_IMEI, item_Qty, item_SalePrice, 
                        item_DiscountPercentage, item_DiscountPrice, item_discount_amount_per_item, 
                        item_CostPrice, item_NetPrice, sd_CreatedOn, client_id
                    ) VALUES (
                        $id,
                        '$s_Date',
                        $item_id,
                        '" . mysqli_real_escape_string($con, $item_BarCode) . "',
                        '" . mysqli_real_escape_string($con, $item_IMEI) . "',
                        '$item_Qty',
                        '$item_SalePrice',
                        '$item_DiscountPercentage',
                        '$item_DiscountPrice',
                        '$item_discount_amount_per_item',
                        '$item_CostPrice',
                        '$item_NetPrice',
                        '$current_datetime_sql',
                        $client_account_id
                    )";
            if(!mysqli_query($con, $sdQ)) {
                $error++;
            }
        }

        // --- ACCOUNTING VOUCHER POSTING ---
        // 1. Voucher Header
        $voucher_type = 'Sale';
        $voucher_no = isset($s_Number)?$s_Number:'';
        $voucher_desc = 'Sale Invoice #'.$voucher_no . ' (' . $client_name . ')';
        $q_voucher = "INSERT INTO accounts_voucher (entry_date, voucher_type, voucher_no, description, created_by)
                      VALUES ('$s_Date', '$voucher_type', '$voucher_no', '".mysqli_real_escape_string($con,$voucher_desc)."', '$u_id')";
        mysqli_query($con, $q_voucher);
        $voucher_id = mysqli_insert_id($con);

        // 2. Voucher Detail (Double Entry)
        // Note: Set your sales income account_id as per your chart
       // Find Sales Income Account dynamically (by type and/or title)
			$sales_account_id = 0;
			$res = mysqli_query($con, "SELECT account_id FROM accounts_chart WHERE (account_type='Income' OR account_type='Sales') AND (account_title LIKE '%Sales%' OR account_title LIKE '%Sale%') LIMIT 1");
			if($row = mysqli_fetch_assoc($res)) {
				$sales_account_id = $row['account_id'];
			}

				if(!$sales_account_id) {
					// fallback: show error or use a default, but best to alert!
					die("Sales account not found in Chart of Accounts! Please create a Sales Income account.");
				}

        // Debit Customer (Receivable)
        mysqli_query($con, "INSERT INTO accounts_voucher_detail (voucher_id, account_id, description, debit, credit)
                            VALUES ($voucher_id, $client_account_id, 'Sale Invoice #$voucher_no', $s_NetAmount, 0)");
        // Credit Sales Income
        mysqli_query($con, "INSERT INTO accounts_voucher_detail (voucher_id, account_id, description, debit, credit)
                            VALUES ($voucher_id, $sales_account_id, 'Sale Invoice #$voucher_no', 0, $s_NetAmount)");
    }
    else
    {
        $error++;
    }

    if(empty($error))
    {
?>
        <script>
        window.open('invoice_print.php?s_id=<?=$id?>&show_prebalance=<?=$show_prebalance?>&print_size=<?=$print_size?>&print_header=<?=$print_header?>','popUpWindow','resizable=yes,scrollbars=yes,toolbar=no,menubar=no,location=no,directories=no, status=no');
        <?=$location_after_save;?>
        </script>
<?php
    }
    else
    {
        $_SESSION['msg']='<div class="alert alert-danger">Problem Saving Sale.</div>';
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
                <table style="width:55%; background:#f1f1f1; float:left;" class="table table-condensed">
    <tr>
        <th style="width: 20%; line-height: 24px;">Invoice No.</th>
        <th style="width: 60%; line-height: 24px;">Search Customer</th>
        <th style="width: 20%; line-height: 24px;">Add Customer</th>
    </tr>
    <tr>
        <td>
            <?php
                $sNumberQ = mysqli_fetch_assoc(mysqli_query($con, "SELECT (IFNULL(MAX(s_NumberSr),0)+1) AS s_Number FROM cust_sale WHERE branch_id=$branch_id"));
                $s_Number = $sNumberQ['s_Number'];
            ?>
            <span style="font-size:20px; color: #d65252;"><?=$s_NumberPrefix.$s_Number?></span>
        </td>
       <td>
    <input type="hidden" class="form-control" name="client_id" id="client_id" value="<?=isset($client_id) ? $client_id : '1'?>">
    
    <input list="client_name_list"  name="ex_client_name" id="ex_client_name" class="form-control" value="<?=isset($ex_client_name) ? $ex_client_name : 'Walking Customer'?>" placeholder="Search By Name OR Enter New" required autocomplete="off"  onblur="select_clilent_name()" style="padding-left:8px;">
    <datalist id="client_name_list">
        <?php
        // Yahan pe get_ClientList() ki jagah naya function banaen/ya update karen jo accounts_chart se data lae
        // Example: get_AccountClientList()
        $itemsArray = get_AccountClientList();
        foreach ($itemsArray as $key => $itemRow) {
            $selected_client = isset($client_id) && $client_id == $itemRow['account_id'] ? 'selected' : '';
        ?>
        <option value="<?php echo $itemRow['account_title']; ?>" data-client-id="<?php echo $itemRow['account_id']; ?>" data-client-phone="<?php echo $itemRow['phone']; ?>">
        <?php } ?>
    </datalist>	
</td>
<td>
    <input list="client_phone_list"  name="ex_client_phone" id="ex_client_phone" class="form-control" value="<?=isset($ex_client_phone) ? $ex_client_phone : '0000000000000'?>" placeholder="Search by Phone OR Enter Name" required autocomplete="off"  onblur="select_clilent_phone()" style="padding-left:8px;">
    <datalist id="client_phone_list">
        <?php
        foreach ($itemsArray as $key => $itemRow) {
            $selected_client = isset($client_id) && $client_id == $itemRow['account_id'] ? 'selected' : '';
        ?>
        <option value="<?php echo $itemRow['phone']; ?>" data-client-id="<?php echo $itemRow['account_id']; ?>" data-client-name="<?php echo $itemRow['account_title']; ?>">
        <?php } ?>
    </datalist>	
</td>
        <td>
            <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#NewCustomerModal" style="margin-top:3px;">Add New Customer</button>
        </td>
    </tr>
    <tr>
        <td style="width:20%;"></td>
        <td style="width:60%;"></td>
        <td style="width:20%;"></td>
    </tr>
</table>

<script>
document.getElementById('client_name').addEventListener('input', function() {
    var val = this.value;
    var opts = document.getElementById('client_name_list').options;
    for (var i = 0; i < opts.length; i++) {
        if (opts[i].value === val) {
            document.getElementById('client_account_id').value = opts[i].getAttribute('data-account-id');
            break;
        }
    }
});
</script>
<!-- Modal for new customer (direct to accounts_chart) -->
<div class="modal fade" id="NewCustomerModal"  role="dialog">
  <div class="modal-dialog  modal-lg">
    <div class="modal-content">
      <div class="modal-header alert alert-success">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title"><strong>Add New Customer</strong></h4>
      </div>
      <div class="modal-body ">
        <form class="smart-form" method="post" action="">
          <fieldset>
            <div class="row" style="margin-bottom: 5px;">
                <div class="col col-lg-3"><label>Customer Name:</label></div>
                <div class="col col-lg-6"><input type="text" name="account_title" placeholder="Customer Name" required class="form-control"></div>
            </div>
            <div class="row" style="margin-bottom: 5px;">
                <div class="col col-lg-3"><label>Phone:</label></div>
                <div class="col col-lg-6"><input type="text" name="phone" placeholder="Phone Number" class="form-control"></div>
            </div>
            <div class="row" style="margin-bottom: 5px;">
                <div class="col col-lg-3"><label>Email:</label></div>
                <div class="col col-lg-6"><input type="email" name="email" placeholder="Email" class="form-control"></div>
            </div>
            <div class="row" style="margin-bottom: 5px;">
                <div class="col col-lg-3"><label>Address:</label></div>
                <div class="col col-lg-6"><input type="text" name="address" placeholder="Address" class="form-control"></div>
            </div>
            <div class="row" style="margin-bottom: 5px;">
                <div class="col col-lg-3"><label>Opening Debit (DR):</label></div>
                <div class="col col-lg-3"><input type="number" step="0.01" min="0" name="opening_debit" placeholder="Opening Debit" class="form-control"></div>
                <div class="col col-lg-3"><label>Opening Credit (CR):</label></div>
                <div class="col col-lg-3"><input type="number" step="0.01" min="0" name="opening_credit" placeholder="Opening Credit" class="form-control"></div>
            </div>
            <div class="row" style="margin-bottom: 5px;">
                <div class="col col-lg-3"><label>Remarks:</label></div>
                <div class="col col-lg-6"><input type="text" name="remarks" placeholder="Remarks" class="form-control"></div>
            </div>
          </fieldset>
          <div class="form-actions">
            <button type="submit" class="btn btn-primary btn-sm" name="submit_new_customer">Save</button>
            <button type="button" data-dismiss="modal" class="btn btn-danger btn-sm">Cancel</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
                <table style="width: 25%; float: left; margin-left: 1%;" class="table-bordered">
                	<thead>
                		<tr><th colspan="5" style="text-align: center;background: #f1f1f1">Last Records</th></tr>
                		<tr>
                			<th>#</th>
                			<th>Date</th>
                			<th>Rate</th>
                			<th>Qty</th>
                			<th>Net</th>
                		</tr>
                	</thead>
                	<tbody id="show_last_record_sales"></tbody>
                </table>

                <table style="width: 10%; float: left; margin-left: 1%;" class="table-bordered">
                	<thead>
                		<tr><th>
                			<?php
							if($branch_SalesBtnShow==1)
							{
							?>
                            	<a href="total_sales"><button type="button" style="float:right;" class="btn btn-xs btn-primary"><strong><i class="fa fa-signal"></i> Check Store Sale</strong></button></a>
							<?php } ?>
                		</th></tr>
                	</thead>
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
						<th style="width:6%;">Qty Pack</th>
						<th style="width:6%;">Stock</th>
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
						<!--<td>
							<input type="text"  id="ex_imei" placeholder="Product IMEI" class="form-control" style="font-size: 12px;padding-left: 8px;" >
							
							<input type="hidden"  id="ex_itemname" class="form-control" >
							<input type="hidden"  id="ex_item_id_from_imei" class="form-control" >
							<input type="hidden"  id="ex_item_stock" class="form-control" >
						</td>-->
						<td><input type="text"  id="ex_itemcode" placeholder="Enter Product Code" class="form-control" style="font-size: 12px;padding-left: 8px;" ></td>
						<td>
							<input list="item_list"  name="" id="ex_item" class="form-control" placeholder="Search Product by Name OR Enter Product Name" required autocomplete="off"  onblur="getItemDetail()" style="padding-left:8px;">
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
						<td><input type="number" id="ex_qtyinpack" class="form-control" style="text-align:center;" readonly="readonly"></td>
						<td><input type="number" id="ex_stock" class="form-control" style="text-align:center;" readonly="readonly"></td>
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
						<tr>
							<th style="width:13%;">Product Code</th>
							<th style="width:20%;">Product Name</th>
							<th>Stock</th>
							<th style="width:9%">Quantity</th>
							<th style="width:9%;">Unit Price</th>
							<th style="width:8%;">Discount Amt</th>
							<th style="width:8%">Cost Price</th>
							<th style="width:9%">Ext Amount</th>
							<th style="width:9%;">Action</th>
						</tr>
						<?php
						if(isset($id) && $id>0)
						{
							$detailQ="
							SELECT cust_sale_detail.*, adm_item.item_Name, adm_item.item_Code
							FROM cust_sale_detail
							LEFT OUTER JOIN adm_item ON adm_item.item_id=cust_sale_detail.item_id
							WHERE cust_sale_detail.s_id=$id";
							$detailR=mysqli_query($con, $detailQ);
							while($editDrows=mysqli_fetch_assoc($detailR)){
						?>
							<tr>
								<td><?=$editDrows['item_Code']?></td>
								<td><?=$editDrows['item_Name']?></td>
								<td></td>
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
			</table>
			<div style="height:150px; overflow:auto;"><!--A wrapper div to control the height of table-->
				<table class="table table-bordered table-condensed" >
					<tr id="u_row" style="display: none;">
						<td id="item_row_id" style="display: none;"></td>
						<td id="show_itemcode"></td>
						<td id="show_item"></td>
						<td id="show_stock"></td>
						<td id="show_qty" class="item_Qty_show">
							<input type="number" name="item_Qty[]" class="item_Qty" min="1" value="1" oninput="rowInputChanged(this)">
						</td>
						<td id="show_rate">
							<input type="number" name="item_Rate[]" class="item_Rate" min="0" value="0" oninput="rowInputChanged(this)">
						</td>
						<!-- Remove Discount (Rs) column completely -->
						<td id="show_discount_amount">
							<input type="number" name="item_DiscountPrice[]" class="item_DiscountPrice" min="0" value="0" oninput="rowInputChanged(this)">
						</td>
						<td id="show_costprice" style="text-align:right;">
							<span class="item_CostPrice_show">0</span>
							<input type="hidden" name="item_CostPrice[]" class="item_CostPrice" value="0">
						</td>
						<td id="show_netprice" class="item_NetPrice_show">
							<span class="item_NetPrice_show">0.00</span>
							<input type="hidden" name="item_NetPrice[]" class="item_NetPrice" value="0">
						</td>
						<td>
							<p class="btn btn-danger" onclick="delRow(this)">Delete</p> 
						</td>
							<input type="hidden" name="item_id[]" class="item_id">
							<input type="hidden" name="item_Code[]">
							<input type="hidden" name="item_Name[]">
					</tr>
				</table>
			</div><!--End of wrappe div-->
    
                <div class="" style="margin-top:10px">
                    <div class="col-lg-12 col-md-12 col-xs-12" >
                        <table style="width:100%; background:#f1f1f1;" class="table table-condensed" border="0">
                            <tr>
                                <!--<th style="width:9%">Internal Note</th>
                                <th style="width:9%">External Note</th>-->
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
                                                    <option value="cash"  <?=isset($s_SaleMode) && $s_SaleMode=='cash' ? 'selected' : ''?>>Cash</option>
                                                    <option value="credit"  <?=isset($s_SaleMode) && $s_SaleMode=='credit' ? 'selected' : ''?>>Credit</option>
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
                                            <!--<td>
                                            VAT (Amount)<br />
                                            <h1 style="    color: black;font-size: 20px; padding:5px; text-align: right;background: lightgray;"><?=$currency_symbol?><span id="s_TaxAmountShow"><?=isset($s_TaxAmount) ? $s_TaxAmount : '0'?></span></h1>
                                            <input type="hidden" name="s_TaxAmount" id="s_TaxAmount" class="form-control" style="text-align:right; width: 90%; font-size: 14px;" min="0" readonly="readonly" value="<?=isset($s_TaxAmount) ? $s_TaxAmount : '0'?>">
                                            </td>-->
                                            <td>&nbsp;</td>
                                            <td colspan="4"> 
                                            	Bill Paid (Amount)<br /><input type="number" name="sp_Amount" id="sp_Amount" placeholder="Enter Received Amount" autocomplete="off" class="form-control" style="text-align:right;width: 98%; font-size: 20px;font-weight: 500;" min="0" value="<?=isset($s_PaidAmount) ? $s_PaidAmount : '0'?>">
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
                        		<!--<th style="width: 20%;">Internal Notes</th>-->
                        		<th style="width: 20%;">Pre Balance</th>
                        		<th style="width: 20%;">Print Header</th>
                        		<th style="width: 20%;">Print Size</th>
                        		<!--<th style="width:10%">External Notes</th>-->
                        	</tr>
                        	<tr>
                        		<!--<th>
                        			<textarea class="form-control" name="s_Remarks" id="s_Remarks" style="width:230px;max-width:230px; min-width:230px; min-height:64px;height:64px; max-height:80px;"><?=isset($s_Remarks) ? $s_Remarks : ''?></textarea>
                        		</th>-->
                        		<th>
                        			<select class="form-control" name="show_prebalance">
									<option value="yes" >Yes</option>
                        				<option value="no" selected="selected">No</option>
                        				
                        				
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
                        				<option value="thermal" selected="selected">Thermal</option>
                        				<option value="a4">A4</option>
                        				<option value="a4half">A4 Half</option>
                        				<option value="a5">A5</option>
                        				
                        			</select>
                        		</th>
                        		<!--<th>
                        			<textarea class="form-control" name="s_RemarksExternal" id="s_RemarksExternal" style="width:230px;max-width:230px; min-width:230px; min-height:64px;height:64px; max-height:80px;"><?=isset($s_RemarksExternal) ? $s_RemarksExternal : ''?></textarea>
                        		</th>-->

                        	</tr>
                        </table>
                    </div>
                </div>

		</fieldset>
	<input type="hidden" name="post_form" />
	</form>

</div>




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
    // Name -> Phone
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

    // Phone -> Name
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
});
</script> 