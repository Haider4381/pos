<?php
include('sessionCheck.php');
include('connection.php');
include('functions.php');
require_once ("inc/init.php");
require_once ("inc/config.ui.php");
$page_title = "Quotation";
include ("inc/header.php");


if(isset($_GET['id']))
{
	$id=(int)mysqli_real_escape_string($con,$_GET['id']);
	$Q="SELECT * FROM adm_quotation WHERE q_id='".$id."'";
	
	$Qry=mysqli_query($con,$Q);
	$Rows=mysqli_num_rows($Qry);
	if($Rows!=1) { ?> <script> window.location.href='<?=$base_file?>';</script><?php die();}
	$Result=mysqli_fetch_object($Qry);


	$q_Number = $Result->q_Number;
	$q_NumberSr = $Result->q_NumberSr;
	$q_Date = $Result->q_Date;
	$client_id = $Result->client_id;
	$q_TotalAmount = $Result->q_TotalAmount;
	$q_Discount = $Result->q_Discount;
	$q_DiscountAmount = $Result->q_DiscountAmount;
	$q_Tax = $Result->q_Tax;
	$q_TaxAmount = $Result->q_TaxAmount;
	$q_SaleMode = $Result->q_SaleMode;
	$q_PaidAmount = $Result->q_PaidAmount;
	$q_DiscountPrice = $Result->q_DiscountPrice;
	$q_NetAmount = $Result->q_NetAmount;
	$q_Remarks = $Result->q_Remarks;
	$q_RemarksExternal = $Result->q_RemarksExternal;
	$q_CreatedOn = $Result->q_CreatedOn;
	$q_TotalItems = $Result->q_TotalItems;
	$u_id = $Result->u_id;
	$branch_id = $Result->branch_id;
	$q_PaymentType = $Result->q_PaymentType;
}




include ("inc/nav.php");
$branch_id=$_SESSION['branch_id'];
$u_id=$_SESSION['u_id'];
$q_NumberPrefix='Quo-';
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
				<h2>Quotation Invoice</h2>
			</header>

			<!-- widget div-->
			<div>		


<!-- widget content -->
<div class="widget-body no-padding">

<?php if(!empty($_SESSION['msg'])){ echo $_SESSION['msg']; unset($_SESSION['msg']);} ?>


<?php

if(isset($_POST['submit_new_customer']))
{
	$client_Name=validate_input($_POST['client_Name']);
	$client_Email=validate_input($_POST['client_Email']);
	$client_Phone=validate_input($_POST['client_Phone']);
	$client_Address=validate_input($_POST['client_Address']);
	$client_Status='A';
	$client_Remarks=validate_input($_POST['client_Remarks']);
	
	$q="INSERT INTO adm_client (client_Name,client_Email,client_Phone,client_Address,client_Status,client_Remarks, u_id, branch_id) VALUES('$client_Name','$client_Email','$client_Phone','$client_Address','$client_Status','$client_Remarks', '$u_id', '$branch_id')";
	//die();
	if(mysqli_query($con,$q))
	{
		//$_SESSION['msg']='<div class="alert alert-success">New Customer Saved Successfully</div>';
		?>
		<script>
        	alert('New Customer "<?=$client_Name?>" Saved Successfully');
			window.location='';
        </script>
	<?php
		die();
    }	
	else
	{
		//$_SESSION['msg']='<div class="alert alert-danger">Problem Creating New Customer</div>';
	?>
		<script>
        	alert('Problem Creating New Customer');
			window.location='';
        </script>
	<?php
		die();
	}
}



if(isset($_POST['post_form']))
{
	$id=validate_input($_POST['id']);
	$error=0;

	$location_after_save='window.location.href="quotation"';
	$save_value=$_POST['save_value'];
	$client_id=$_POST['client_id'];
	//$q_PaymentType=$_POST['q_PaymentType'];
	//$q_SaleMode=$_POST['s_SaleMode'];
	$q_Remarks=validate_input($_POST['q_Remarks']);
	$q_RemarksExternal=$_POST['q_RemarksExternal'];
	
	$q_TotalItems=$_POST['q_TotalItems'];
	$q_TotalAmount=$_POST['q_TotalAmount'];
	$q_TaxAmount=$_POST['q_TaxAmount']+0;
	$q_Tax=$_POST['q_Tax']+0;
	$q_DiscountAmount=$_POST['q_DiscountAmount']+0;
	$q_Discount=$_POST['q_Discount']+0;
	$q_DiscountPrice=0;
	$q_NetAmount=$_POST['q_NetAmount'];
	$q_Date=date('Y-m-d');
	$item_idArray=($_POST['item_id']);
	//die();
	//$item_idArray=array_filter($_POST['item_id']);
	
	//$item_idArray=($_POST['item_id']);
	$item_BarCodeArray=$_POST['item_Code'];
	$item_IMEIArray=isset($_POST['item_IMEI']) ? $_POST['item_IMEI'] : 0;
	$item_NameArray=$_POST['item_Name'];
	$item_SalePriceArray=$_POST['item_Rate'];
	
	$item_DiscountPercentageArray=$_POST['item_DiscountPercentage'];
	$item_DiscountPriceArray=$_POST['item_DiscountPrice'];
	$item_QtyArray=array_filter($_POST['item_Qty']);
	$item_CostPriceArray=$_POST['item_CostPrice'];
	$item_NetPriceArray=$_POST['item_NetPrice'];
	//$qp_Amount=$_POST['sp_Amount'];


	$show_prebalance=$_POST['show_prebalance'];
	$print_header=$_POST['print_header'];
	$print_size=$_POST['print_size'];
	
	if($save_value=='save_and_close')
	{
		$location_after_save='window.location.href="dashboard"';
	}
	
	if(empty($item_idArray) && empty($item_IMEIArray))
	{
		echo '<script> alert("Atleast 1 item must be selected");
				window.location="";</script>';
	die();
	}

	if(empty($id))
	{
		$NumberCheckQ="SELECT MAX(q_NumberSr) as q_Number FROM adm_quotation WHERE 1 AND branch_id='$branch_id' ";
		$NumberCheckRes=mysqli_query($con,$NumberCheckQ);
		if(mysqli_num_rows($NumberCheckRes)<1)
		{
			$q_Number=$q_NumberPrefix.'1';
			$q_NumberSr=1;
		}
		else
		{
			$r=mysqli_fetch_assoc($NumberCheckRes);
			$q_Number=$q_NumberPrefix.($r['q_Number']+1);
			$q_NumberSr=$r['q_Number']+1;
		}

		///start inserting data in sale master table
		$sQ="INSERT INTO adm_quotation(q_Number,q_NumberSr, q_Date, client_id, q_TotalAmount, q_Discount,q_DiscountAmount, q_NetAmount, q_TotalItems, q_Remarks,q_RemarksExternal, q_CreatedOn, u_id, branch_id,q_Tax, q_TaxAmount) 
		VALUES ('$q_Number','$q_NumberSr','$q_Date','$client_id','$q_TotalAmount','$q_Discount','$q_DiscountAmount','$q_NetAmount', '$q_TotalItems', '$q_Remarks','$q_RemarksExternal','$current_datetime_sql', '$u_id', '$branch_id','$q_Tax', '$q_TaxAmount')";

		$inserted=mysqli_query($con,$sQ);
		if($inserted)
		{
			$id=mysqli_insert_id($con);
			if(!empty($sp_Amount))
			{
				if($s_SaleMode='cash' && $sp_Amount>$s_NetAmount) {$sp_Amount=$s_NetAmount;}
				$spQ="INSERT INTO adm_sale_payment(client_id, sp_Amount, sp_Date, s_id, sp_Description,sp_Type, sp_CreatedOn,branch_id) VALUES ($client_id,'$sp_Amount','$s_Date',$id,'Sale Payment','S','$current_datetime_sql','$branch_id')";
				if(mysqli_query($con,$spQ))
				{
					//do somehitng
				}
				else
				{
					$error++;
				}
			}
		}
		else
		{
			$error++;
		}
	}

	else
	{
		$updated = mysqli_query($con,"
			
			UPDATE `adm_quotation` SET

			client_id = '$client_id',
			q_TotalAmount = '$q_TotalAmount',
			q_Discount = '$q_Discount',
			q_DiscountAmount = '$q_DiscountAmount',
			q_Tax = '$q_Tax',
			q_TaxAmount = '$q_TaxAmount',
			q_SaleMode = '$q_SaleMode',
			q_PaidAmount = '$qp_Amount',
			q_DiscountPrice = '$q_DiscountPrice',
			q_NetAmount = '$q_NetAmount',
			q_Remarks = '$q_Remarks',
			q_RemarksExternal = '$q_RemarksExternal',
			q_TotalItems = '$q_TotalItems',
			q_PaymentType = '$q_PaymentType'

				WHERE q_id = '".$id."'");
		if($updated)
		{
			///// do nothig
		}
		else
		{
			$error++;
		}
	}
	
	

	if(empty($error))
	{
		mysqli_query($con,"delete from adm_quotation_detail WHERE q_id=$id");
		
		foreach ($item_QtyArray as $key => $item_Qty)
		{
			$item_BarCode=$item_BarCodeArray[$key];
			$item_IMEI=$item_IMEIArray[$key];
			$item_SalePrice=$item_SalePriceArray[$key];
			
			$item_DiscountPercentage=$item_DiscountPercentageArray[$key];
			$item_DiscountPrice=$item_DiscountPriceArray[$key];
			
			$item_Name=$item_NameArray[$key];
			$item_CostPrice=$item_CostPriceArray[$key];
			//$item_Qty=$item_QtyArray[$key];
			$item_id=$item_idArray[$key];
			$item_InvoiceAmount=0;
			$item_SaleScheme=0;
			$item_SaleExtraAmount=0;
			$item_NetPrice=$item_NetPriceArray[$key];

			$item_discount_amount_per_item = $item_NetPrice/$item_Qty;



			
			if(empty($item_id) || $item_id==0)
			{
				/////query for get other item id - if not exists other item then add in item table
				$oiidQ="select item_id from adm_item where item_Name='$item_Name' and branch_id=$branch_id";
				$oiidQr=mysqli_query($con, $oiidQ);
				$oiid_rows=mysqli_num_rows($oiidQr);
				if($oiid_rows!==1)
				{
					$item_Code_Q=mysqli_fetch_assoc(mysqli_query($con, "select (ifnull(MAX(item_CodeSr),0)+1) as item_CodeSr from adm_item where branch_id=$branch_id"));
					$item_CodeSr=$item_Code_Q['item_CodeSr'];
					$item_CodeSrPAD = str_pad($item_CodeSr, 3, "0", STR_PAD_LEFT);
					$code_prefix='PROC';
					$item_Code=$code_prefix.$item_CodeSrPAD;
					
					$add_other_itemQ="INSERT INTO adm_item (item_Name, 		item_Code,		item_CodeSr,			item_Status,	item_Remarks,							u_id,	branch_id)
													VALUES('$item_Name',	'$item_Code', 	'$item_CodeSr',	'A',	'Mannualy added', '$u_id', '$branch_id')";
					$add_other_item=mysqli_query($con,$add_other_itemQ);
					$item_id_other=mysqli_insert_id($con);
					
				}
				else
				{
					$other_item_result=mysqli_fetch_assoc($oiidQr);
					$item_id_other=$other_item_result['item_id'];
				}
				$item_id=$item_id_other;
			}
			
			
			$sdQ="INSERT INTO adm_quotation_detail (q_id, qd_Date, item_id, item_BarCode,		item_IMEI, item_Qty,		item_SalePrice, item_DiscountPercentage, item_DiscountPrice,	item_discount_amount_per_item,	item_CostPrice,		item_NetPrice,sd_CreatedOn,client_id)
										VALUES ($id,'$q_Date',$item_id,'$item_BarCode',	'$item_IMEI', '$item_Qty',	'$item_SalePrice', '$item_DiscountPercentage',	'$item_DiscountPrice',	$item_discount_amount_per_item,'$item_CostPrice',	'$item_NetPrice','$current_datetime_sql',$client_id)";
			if(mysqli_query($con,$sdQ))
			{
				//do something
			}
			else
			{
				$error++;
			}
		}
}
else
{
	$error++;
}
//echo $current_datetime_sql;
// die();
if(empty($error))
{
	//$_SESSION['msg']='<div class="alert alert-success">Invoice Saved Successfully.</div>';
	
?>
	
		<script type="text/javascript">
		x = confirm("Invoice Saved Successfully.");
		if(x)
		{
			window.open('quotation_print.php?q_id=<?=$id?>&show_prebalance=<?=$show_prebalance?>&print_size=<?=$print_size?>&print_header=<?=$print_header?>','popUpWindow','resizable=yes,scrollbars=yes,toolbar=no,menubar=no,location=no,directories=no, status=no');
			//window.location.href=''
			<?=$location_after_save;?>
		}
		else
		{
			<?=$location_after_save?>
		}
		</script>
		
<?php
	//die();
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
                	    <th style="line-height: 24px;">Invoice No.</th>
                    	<th colspan="4" style="line-height: 24px;">
                        	Search Customer By Phone or Name
                            
                            <?php
							if($branch_SalesBtnShow==1)
							{
							?>
                            	<a href="total_sales"><button type="button" style="float:right;" class="btn btn-xs btn-primary"><strong><i class="fa fa-signal"></i> Check Store Sale</strong></button></a>
							<?php } ?>
                            <button type="button" onclick="NewCustomerModal()" style="float:right; margin-right:10px;" class="btn btn-xs btn-success"><strong><i class="fa fa-child" style="font-size: 14px;"></i> Add New Customer</strong></button>
                         </th>
                         
                    </tr>
                    <tr>
                        <td>
                        	<?php
								$qNumberQ=mysqli_fetch_assoc(mysqli_query($con, "select (ifnull(MAX(q_NumberSr),0)+1) as q_Number from adm_quotation where branch_id=$branch_id"));
								$q_Number=$qNumberQ['q_Number'];
							?>
                            <span style="font-size:20px;  color: #d65252;"><?=$q_NumberPrefix.$q_Number?></span>
                        </td>
                        <td colspan="4">
                        	<input type="hidden" name="client_id" id="client_id" value="<?=isset($client_id) ? $client_id : 0?>" />
                        	<input list="client_list"  name="client_Name" id="client_Name" class="form-control" placeholder="Search Customer By Phone or Name" required autocomplete="off" style="width: 99%;"/>
                            <datalist id="client_list">
                            	
                                <?php $clientArray=get_ActiveClient();
								 foreach ($clientArray as $key => $clilentRow) { 
								?>
								<option value="<?php echo $clilentRow['client_Name']. ' / '. $clilentRow['client_Phone'];?>" data-value="<?=$clilentRow['client_id'];?>">
								<?php } 
								?>
                            	
                            </datalist>

                        </td>
                        
					</tr>
                    <tr>
                        <td style="width:20%;" ></td>
                       
                        <td style="width:10%;" ></td>
                        <td style=" font-size: 12px; font-weight: 600;"></td>
                        <td style="width:70%; font-size: 12px; font-weight: 600; text-align:right; color: #d65252;"> Store Credit : <?=$currency_symbol?><span id="previous_storecredit">0</span> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;  Account : <?=$currency_symbol?><span id="previous_balance">0</span></td>
                        
                        
                        
                    </tr>
                </table>
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
            <?php

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
                    <th style="width:6%;">Stock</th>
                    <th style="width:7%;">Quantity</th>
                    <th style="width:8%;">Unit Price</th>
                    <th style="width:6%;">Discount%</th>
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
                            	
                                <?php $itemsArray=get_ActiveItems();
								 foreach ($itemsArray as $key => $itemRow) 
								 { 
								?>
								<option value="<?php echo $itemRow['item_Name'];?>" data-item-id="<?php echo $itemRow['item_id'];?>" data-item-name="<?php echo $itemRow['item_Name'];?>">
								<?php 
								 } 
								?>
                            	
                            </datalist>	
                    </td>
                    <td><input type="number" id="ex_stock" class="form-control" style="text-align:center;" readonly="readonly"></td>
                    <td><input type="number" id="ex_qty" class="form-control" style="text-align:center;" onchange="calculate_netamount_row()" onkeyup="calculate_netamount_row()" autocomplete="off"></td>
                    <td><input type="number" id="ex_rate" class="form-control"  style="text-align:right" onchange="calculate_netamount_row()" onkeyup="calculate_netamount_row()" autocomplete="off"></td>
                    <td><input type="number" id="ex_discount_percentage" class="form-control"  style="text-align:right" onchange="calculate_netamount_row()" onkeyup="calculate_netamount_row()" autocomplete="off"></td>
                    <td><input type="number" id="ex_netamount" class="form-control" style="text-align:right" readonly="readonly" ></td>
                    <td><input type="text" id="ex_costprice" class="form-control" ></td>
                    <input type="hidden" id="ex_discount_amount">
                </tr>
            </table>
            </div>
            </div>
			
			<table class="table table-bordered" style=" width:100%;margin-top:10px;" id="u_tbl">
				<tr>
					<!--<th style="width:15%;">Product IMEI</th>-->
                    <th style="width:13%;">Product Code</th>
                    <th style="width:20%;">Product Name</th>
					<th style="width:9%">Quantity</th>
					<th style="width:9%;">Unit Price</th>
					<th style="width:8%;">Discount%</th>
					<th style="width:8%;">Discount Amt</th>
					<th style="width:8%">Cost Price</th>
					<th style="width:9%">Ext Amount</th>
					<th style="width:9%;">Action</th>
				</tr>



<?php
if(isset($id) && $id>0)
{
	$detailQ="
	SELECT adm_quotation_detail.*, adm_item.item_Name, adm_item.item_Code
	FROM adm_quotation_detail
	LEFT OUTER JOIN adm_item ON adm_item.item_id=adm_quotation_detail.item_id

	WHERE adm_quotation_detail.q_id=$id";
	$detailR=mysqli_query($con, $detailQ);
	while($editDrows=mysqli_fetch_assoc($detailR))
	{
	?>
		<tr>
			<td><?=$editDrows['item_Code']?></td>
            <td><?=$editDrows['item_Name']?></td>
            <td><?=$editDrows['item_Qty']?></td>
            <td><?=$editDrows['item_SalePrice']?></td>
            <td><?=$editDrows['item_DiscountPercentage']?></td>
            <td><?=$editDrows['item_DiscountPrice']?></td>
            <td><?=$editDrows['item_CostPrice']?></td>
            <td><?=$editDrows['item_NetPrice']?></td>

			<td style="width:9%;">
				<p class="btn btn-danger" onclick="delRow(this)">Delete</p> 
			</td>
			<input type="hidden" name="item_id[]" value="<?=$editDrows['item_id']?>">
			<!-- <input type="hidden" name="item_BarCode[]" id="item_BarCode"> -->
			<!--<input type="hidden" name="item_IMEI[]" id="item_IMEI">-->
            <input type="hidden" name="item_Code[]"  value="<?=$editDrows['item_Code']?>">
            <input type="hidden" name="item_Name[]"  value="<?=$editDrows['item_Name']?>">
			<input type="hidden" name="item_CostPrice[]"  value="<?=$editDrows['item_CostPrice']?>">
			<input type="hidden" name="item_Rate[]"  value="<?=$editDrows['item_SalePrice']?>">
			<input type="hidden" name="item_DiscountPercentage[]"   value="<?=$editDrows['item_DiscountPercentage']?>">
			<input type="hidden" name="item_DiscountPrice[]"   value="<?=$editDrows['item_DiscountPrice']?>">
			<input type="hidden" name="item_Qty[]"   value="<?=$editDrows['item_Qty']?>">
			<input type="hidden" name="item_NetPrice[]"  value="<?=$editDrows['item_NetPrice']?>">
		</tr>

	<?php
	}
}
?>









			</table>
        
        
            
            
			<div style="height:150px;overflow:auto"><!--A wrapper div to control the height of table-->
				<table class="table table-bordered table-condensed" >
					<tr id="u_row" style="display: none;">
						<!--<td id="show_imei" style="width:15%;"></td>-->
                        <td id="show_itemcode" style="width:13%;"></td>
                        <td id="show_item" style="width:20%;"></td>
						<th id="show_qty" style="width: 9%;"></th>
						<th id="show_rate" style="width: 9%;"></th>
						<th id="show_discount_percentage" style="width: 8%;"></th>
						<th id="show_discount_amount" style="width: 8%;"></th>
						<th id="show_costprice" style="width: 8%;"></th>
						<th id="show_netprice" style="width: 9%;"></th>
						<td style="width:9%;">
							<p class="btn btn-danger" onclick="delRow(this)">Delete</p> 
						</td>
						<input type="hidden" name="item_id[]" id="item_id">
						<!-- <input type="hidden" name="item_BarCode[]" id="item_BarCode"> -->
						<!--<input type="hidden" name="item_IMEI[]" id="item_IMEI">-->
                        <input type="hidden" name="item_Code[]" id="item_Code">
                        <input type="hidden" name="item_Name[]" id="item_Name">
						<input type="hidden" name="item_CostPrice[]" id="item_CostPrice" value="0">
						<input type="hidden" name="item_Rate[]" id="item_Rate" value="0">
						<input type="hidden" name="item_DiscountPercentage[]" id="item_DiscountPercentage" value="0">
						<input type="hidden" name="item_DiscountPrice[]" id="item_DiscountPrice" value="0">
						<input type="hidden" name="item_Qty[]" id="item_Qty" value="0">
						<input type="hidden" name="item_NetPrice[]" id="item_NetPrice" value="0">
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
                                        	<td style="width:23%;">Discount(%)<br /><input type="number" name="q_Discount" id="q_Discount" placeholder="Enter Disc. %" class="form-control" onkeyup="calculate()"  onchange="calculate();" style="text-align:right;width: 98%;"  min="0" value="<?=isset($q_Discount) ? $q_Discount : '0'?>"></td>
                                        	<td style="width:1%;">&nbsp;</td>
                                            <td style="width:21%;">VAT(%)<br /><input type="number" name="q_Tax"  placeholder="Enter Tax %" id="q_Tax" class="form-control" style="text-align:right;width: 98%;" min="0" onkeyup="calculate()" onchange="calculate();" value="<?=isset($q_Tax) ? $q_Tax : '0'?>"></td>
                                            <td style="width:1%;">&nbsp;</td>
                                           <!-- <td style="width:30%;">
                                            	Payment Method<br />
                                            	<select name="q_PaymentType" style="width:100%; font-size: 15px; margin-top: 3px; height: 33px;">
                                                    <option value="cash" <?=isset($q_PaymentType) && $q_PaymentType=='cash' ? 'selected' : ''?>>Cash Payment</option>
                                                    <option value="bank" <?=isset($q_PaymentType) && $q_PaymentType=='bank' ? 'selected' : ''?>>Bank Payment</option>
                                                    <option value="creditcard" <?=isset($q_PaymentType) && $q_PaymentType=='creditcard' ? 'selected' : ''?>>Credit Card</option>
                                                </select>
                                            </td>
                                            <td style="width:1%;">&nbsp;</td>
                                            <td style="width:23%;">
                                            	Sale Mode<br />
                                            	<select name="s_SaleMode" id="s_SaleMode" style="width:100%; font-size: 15px; margin-top: 3px; height: 33px;">
                                                    <option value="cash"  <?=isset($s_SaleMode) && $s_SaleMode=='cash' ? 'selected' : ''?>>Cash</option>
                                                    <option value="credit"  <?=isset($s_SaleMode) && $s_SaleMode=='credit' ? 'selected' : ''?>>Credit</option>
                                                </select>
                                            </td>-->
                                        </tr>
                                        <tr>
                                        	<td>
                                            Disc. (Amount)<br />
                                            <h1 style="    color: black; padding:5px;font-size: 20px; text-align: right;background: lightgray;"><?=$currency_symbol?><span id="q_DiscountAmountShow"><?=isset($q_DiscountAmount) ? $q_DiscountAmount : '0'?></span></h1>
                                            <input type="hidden" name="q_DiscountAmount" id="q_DiscountAmount" class="form-control" style="text-align:right; width: 90%; font-size: 14px;" min="0" readonly="readonly" value="<?=isset($q_DiscountAmount) ? $q_DiscountAmount : '0'?>" >
                                            </td>
                                            <td>&nbsp;</td>
                                            <td>
                                            VAT (Amount)<br />
                                            <h1 style="    color: black;font-size: 20px; padding:5px; text-align: right;background: lightgray;"><?=$currency_symbol?><span id="q_TaxAmountShow"><?=isset($q_TaxAmount) ? $q_TaxAmount : '0'?></span></h1>
                                            <input type="hidden" name="q_TaxAmount" id="q_TaxAmount" class="form-control" style="text-align:right; width: 90%; font-size: 14px;" min="0" readonly="readonly" value="<?=isset($q_TaxAmount) ? $q_TaxAmount : '0'?>">
                                            </td>
                                            <td>&nbsp;</td>
                                            <!--<td colspan="3"> 
                                            	Bill Paid (Amount)<br /><input type="number" name="sp_Amount" id="sp_Amount" placeholder="Enter Received Amount" autocomplete="off" class="form-control" style="text-align:right;width: 98%; font-size: 20px;font-weight: 500;" min="0" value="<?=isset($s_PaidAmount) ? $s_PaidAmount : '0'?>">
                                            </td>-->
                                            
                                        </tr>
                                    </table>
                                	
                                    
                                    
                                </th>
                                <th style="width:30%; background:#09F !important;">
                                    <p style="padding:0; float:left; color:#FFF;">Due Amount</p><p style="padding:0; margin:0; float:right; color:#FFF;">Total Items: <span id="totalItems"></span></p>
                                    <br />
                                    <h1 style=" color:#FFF; font-size:40px; text-align:center;"><?=$currency_symbol?><span id="q_NetAmountShow">0</span></h1>
                                    <input type="hidden" name="q_NetAmount" id="q_NetAmount" value="0">
                                    <input type="hidden" name="q_TotalAmount" id="q_TotalAmount" value="0">
                                    
                                    <input type="hidden" name="q_TotalItems" id="q_TotalItems" value="0">
                                    
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
                        		<th style="width: 20%;">Internal Notes</th>
                        		<th style="width: 20%;">Pre Balance</th>
                        		<th style="width: 20%;">Print Header</th>
                        		<th style="width: 20%;">Print Size</th>
                        		<th style="width:10%">External Notes</th>
                        	</tr>
                        	<tr>
                        		<th>
                        			<textarea class="form-control" name="q_Remarks" id="q_Remarks" style="width:230px;max-width:230px; min-width:230px; min-height:64px;height:64px; max-height:80px;"><?=isset($q_Remarks) ? $q_Remarks : '0'?></textarea>
                        		</th>
                        		<th>
                        			<select class="form-control" name="show_prebalance">
                        				<option value="no" selected="selected">No</option>
                        				<option value="yes">Yes</option>
                        				
                        			</select>
                        		</th>
                        		<th>
                        			<select class="form-control" name="print_header">
                        				<option value="no" selected="selected">No</option>
                        				<option value="yes">Yes</option>
                        				
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
                        		<th>
                        			<textarea class="form-control" name="q_RemarksExternal" id="q_RemarksExternal" style="width:230px;max-width:230px; min-width:230px; min-height:64px;height:64px; max-height:80px;"><?=isset($q_RemarksExternal) ? $q_RemarksExternal : '0'?></textarea>
                        		</th>

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












<script type="text/javascript">
window.item_Name=window.item_BarCode=window.item_id=0;

$(document).ready(function(){
	$("#client_Name").focus();
})

function saveForm(val)
{
	var save_value=val;
	$('#save_value').val(save_value);
	$("#checkout-form").submit();
}

$("#ex_itemcode").keypress(function(e){
	if(e.keyCode==13)
	{
		getDBDataItemCodeBlur();

	}
});

$("#ex_imei").keypress(function(e){
	if(e.keyCode==13)
	{
		getDBData();

	}
});

$("#ex_item").keypress(function(e){
	if(e.keyCode==13)
	{
		$("#ex_qty").focus();

	}
});

$("#ex_saleprice").keypress(function(e){
	if(e.keyCode==13)
	{
		$("#ex_discountpercentage").focus();

	}
});

$("#ex_discountpercentage").keypress(function(e){
	if(e.keyCode==13)
	{
		addToTable();

	}
});







function postDataForCustomerDsiplay()
{
	//alert('customerdisplay');
	var item_CostPrice=$("#item_CostPrice").val();
	//alert(item_CostPrice);
	var item_Rate=$("#item_Rate").val();
	//alert(ex_imei);
	var allVars="item_CostPrice="+item_CostPrice+"&item_Rate="+item_Rate;
	$.ajax
	({
	 type: "POST",
	 url: "sale_add_json_customer_display.php",
	 dataType: 'json',
	 data:allVars,
	 cache: false,
	 success: function(data)
	 { 
	 	if(data['msg']=='Y')
	 	{
			alert(data.msg);
	 		//console.log(data.query);
	 	}	
	 	else
	 	{
	 		alert(data.msg);
	 	}
	 }
	});
}






function getDBData()
{
	var ex_imei=$("#ex_imei").val();
	if(ex_imei=='')
	{
		alert('Please Choose Item First or Scan Item IMEI');
		$("#ex_imei").focus();
		return false;
	}
	var client_id=$("#client_id").val();
	//alert(ex_imei);
	var allVars="item_IMEI="+ex_imei+"&client_id="+client_id;
	$.ajax
	({
	 type: "POST",
	 url: "sale_add_json.php",
	 dataType: 'json',
	 data:allVars,
	 cache: false,
	 success: function(data)
	 { 
	 	if(data['msg']=='Y')
	 	{
	 		console.log(data.query);
	 		$("#ex_qty").val('1');
	 		$("#ex_rate").val(data.item_SalePrice);
	 		$("#ex_netamount").val(data.item_SalePrice);
	 		$("#ex_costprice").val(data.item_PurchasePrice);
			//alert(data.item_Name);
			$("#ex_itemname").val(data.item_Name);
			$("#ex_itemex_item").val(data.item_Name);
			$("#ex_itemcode").val(data.item_Code);
			$("#ex_item_id_from_imei").val(data.item_id_from_imei);
			$("#ex_item_stock").val(data.item_CurrentStock);
			$("#ex_stock").val(data.item_CurrentStock);
			
			
	 		$("#ex_qty").focus();

			window.newtest=data.item_NetAmount
	 		window.item_Name=data.item_Name;
	 		window.item_BarCode=data.item_BarCode;
	 		window.item_id=data.item_id_from_imei;
			
			//addToTable();
	 	}	
	 	else
	 	{
	 		alert(data.msg);
	 	}
	 }
	});
}


function getDBDataItemCodeBlur()
{
	var ex_itemcode=$("#ex_itemcode").val();
	if(ex_itemcode=='')
	{
		alert('Please Give Product Code');
		$("#ex_itemcode").focus();
		return false;
	}
	var client_id=$("#client_id").val();
	//alert(ex_imei);
	var allVars="item_Code="+ex_itemcode+"&client_id="+client_id;
	$.ajax
	({
	 type: "POST",
	 url: "sale_add_json.php",
	 dataType: 'json',
	 data:allVars,
	 cache: false,
	 success: function(data)
	 { 
	 	if(data['msg']=='Y')
	 	{
	 		console.log(data.query);
	 		$("#ex_qty").val('1');
	 		$("#ex_rate").val(data.item_SalePrice);
	 		$("#ex_netamount").val(data.item_SalePrice);
	 		$("#ex_costprice").val(data.item_PurchasePrice);
			//alert(data.item_Name);
			$("#ex_itemname").val(data.item_Name);
			//$("#ex_itemcode").val(data.item_Code);
			$("#ex_item_id_from_imei").val(data.item_id_from_imei);
			$("#ex_item_stock").val(data.item_CurrentStock);
			$("#ex_stock").val(data.item_CurrentStock);
			
			get_Last_Records('itemcode', ex_itemcode);
			
			
	 		$("#ex_qty").focus();

			window.newtest=data.item_NetAmount
	 		window.item_Name=data.item_Name;
	 		window.item_BarCode=data.item_BarCode;
	 		window.item_id=data.item_id_from_imei;
			
			//addToTable();
	 	}	
	 	else
	 	{
	 		alert(data.msg);
	 	}
	 }
	});
}



function get_Last_Records(type, value)
{

	item_code=item_id=0;
	if(type=='itemcode')
	{
		item_code=value;
	}

	if(type=='item_id')
	{
		item_id=value;
	}

	var client_id=$("#client_id").val();
	var allVars="item_Code="+item_code+"&client_id="+client_id+"&item_id="+item_id+"&where_type="+type+"&get_last_records=1";
	$.ajax
	({
	 type: "POST",
	 url: "sale_add_json_lastrecord.php",
	 dataType: 'json',
	 data:allVars,
	 cache: false,
	 success: function(data)
	 { 
	 	if(data['msg']=='Y')
	 	{
	 		$("#show_last_record_sales").html(data.last_sale_records);
	 	}	
	 	else
	 	{
	 		alert(data.msg);
	 	}
	 }
	});
}



	function reCalculate(e)
	{
		var extra=$(e).val();
		var thisTr=$(e).closest('tr');
		var net_amount=$(thisTr).find("#item_NetPriceDuplicate").val();
		var net_total=net_amount;
		if(isNaN(extra) || extra=='' || extra==0)
		{
		}
		else
		{	
				extra=parseFloat(extra);
				net_amount=parseFloat(net_amount);
				net_total=extra+net_amount
		}
		$(thisTr).find('#show_netprice').text(net_total);
		$(thisTr).find('#item_NetPrice').val(net_total);
		calculate();	
	}
function addToTable()
{
	var check_stock=1;
	var ex_saleprice=$("#ex_rate").val();
	var ex_discount_percentage=$("#ex_discount_percentage").val();
	var ex_discount_amount=$("#ex_discount_amount").val();
	var ex_costprice=$("#ex_costprice").val();
	var ex_qty=$("#ex_qty").val();
	var ex_rate=$("#ex_rate").val();
	var ex_netamount=$("#ex_netamount").val();
	var ex_imei=$("#ex_imei").val();
	var ex_itemcode=$("#ex_itemcode").val();
	var ex_itemname=$("#ex_itemname").val();
	var ex_itemid=$("#ex_item_id_from_imei").val();
	var ex_stock=$("#ex_item_stock").val();
	
	if(ex_itemid==0) { check_stock=0;}
	
	ex_rate=parseFloat(ex_rate);
	ex_discount_percentage= ex_discount_percentage ? parseFloat(ex_discount_percentage) : '0';
	ex_discount_amount= ex_discount_amount ? parseFloat(ex_discount_amount) : '0';
	ex_costprice=parseFloat(ex_costprice);
	
	
	if(ex_itemid==0)
	{
		//alert('Please choose item or give manual item code/name in search product box');
		$.smallBox({
			title : "Error",
			content : "<i class='fa fa-clock-o'></i> <i>Please choose item.</i>",
			color : "#C46A69",
			iconSmall : "fa fa-times fa-2x fadeInRight animated",
			timeout : 4000
			});
		$("#ex_item").focus();
		return false;
	}	
	
	
	if(ex_rate < ex_costprice)
	{
		//alert('Sale Price is less then Purchase Price.');
		$.smallBox({
			title : "Error",
			content : "<i class='fa fa-clock-o'></i> <i>Sale Price is less then Purchase Price.</i>",
			color : "#C46A69",
			iconSmall : "fa fa-times fa-2x fadeInRight animated",
			timeout : 4000
			});
		$("#ex_rate").focus();
		return false;
	}
	else
	{ 
		// do nothing
	}
	//alert(ex_saleprice);
	//alert(item_id);
	if(ex_rate=='' || ex_qty=='')
	{
		alert('Please Give Qty and Rate..');
		$("#ex_qty").focus();
		return false;
	}
	else
	{
		if(checkDuplicate() && checkDuplicateItem())
		{
			if(ex_itemid=='') { ex_itemid=0;}
			
			//check if item already in table then revoke
			//if(ex_itemid!==0) { checkDuplicateItem();}
			
			var newRow=$("#u_row").clone().show();
			$(newRow).find("#show_item").text(ex_itemname);
			$(newRow).find("#show_qty").text(ex_qty);
			$(newRow).find("#show_rate").text(ex_rate);
			$(newRow).find("#show_discount_percentage").text(ex_discount_percentage);
			$(newRow).find("#show_discount_amount").text(ex_discount_amount);
			$(newRow).find("#show_costprice").text(ex_costprice);
			//alert(ex_costprice);
			$(newRow).find("#show_imei").text(ex_imei);
			$(newRow).find("#show_itemcode").text(ex_itemcode);
			$(newRow).find("#show_netprice").text(ex_netamount);
			
	
			$(newRow).find("#item_id").val(ex_itemid);
			$(newRow).find("#item_IMEI").val(ex_imei);
			$(newRow).find("#item_Code").val(ex_itemcode);
			$(newRow).find("#item_Name").val(ex_itemname);
			$(newRow).find("#item_Qty").val(ex_qty);
			$(newRow).find("#item_Rate").val(ex_rate);
			$(newRow).find("#item_DiscountPercentage").val(ex_discount_percentage);
			$(newRow).find("#item_DiscountPrice").val(ex_discount_amount);
			$(newRow).find("#item_NetPrice").val(ex_netamount);
			$(newRow).find("#item_CostPrice").val(ex_costprice);
	
			$("#sale_netamount").val('');
			$("#u_tbl").append(newRow);
			
			calculate();
			totalItems();

			$("#ex_imei").val("");
			$("#ex_itemcode").val("");
			$("#ex_item").val("");
			$("#ex_qty").val("");
			$("#ex_rate").val("");
			$("#ex_costprice").val("");
			$("#ex_netamount").val("");
			$("#ex_item").focus();
			
			$("#ex_itemname").val("");
			$("#ex_item_id_from_imei").val("");
			$("#ex_item_stock").val("");
			$("#ex_stock").val("");

			$("#ex_discount_percentage").val("");
			$("#ex_discount_amount").val("");

			$('#show_last_record_sales').html('');
		}
	}
}



function delRow(e)
{
	$(e).closest('td').closest('tr').remove();
	calculate();
	totalItems();
}
function totalItems()
{

	var totalItems=$("#u_tbl tr").length;
	$("#totalItems").html(totalItems-1);
	$("#q_TotalItems").val(totalItems-1);
}
function calculate()
{
	var item_NetPrice = document.getElementsByName("item_InvoiceAmount[]");
	var sum=price=taxamount=discountamount=0;
	for( var i = 0; i < item_NetPrice.length; i ++ )
	{
	 if(!item_NetPrice[i].value=="")
	 {
	 		if(!isNaN(item_NetPrice[i].value))
	 		{
	 	 	var price = parseFloat(item_NetPrice[i].value);

	 	}
	 }
	 sum = sum+price;
	}
	sum=sum.toFixed(2);

	var item_NetPrice=document.getElementsByName('item_NetPrice[]');
	var net_sum=price=0;
	$(item_NetPrice).each(function(index,elem){
		if(!isNaN(elem.value))
		{
			price=parseFloat(elem.value);
			net_sum+=price;
		}
	});
	
	$("#q_TotalAmount").val(net_sum);
	
	var q_Discount=$("#q_Discount").val();
	
	if(q_Discount!='' && q_Discount!==0)
	{
		discountamount=q_Discount/100*net_sum;
		net_sum=parseFloat(net_sum) - parseFloat(discountamount);
	}
	
	
	var q_Tax=$("#q_Tax").val();
	if(q_Tax!='' && q_Tax!==0)
	{
		taxamount=q_Tax/100*net_sum;
		net_sum=parseFloat(net_sum) +parseFloat(taxamount);
	}
	
	
	discountamount=parseFloat(discountamount).toFixed(2);
	taxamount=parseFloat(taxamount).toFixed(2);
	net_sum=parseFloat(net_sum).toFixed(2);	
	
	$("#q_NetAmount").val(net_sum);
	$("#q_NetAmountShow").html(net_sum);
	
	$("#q_DiscountAmount").val(discountamount);
	$("#q_DiscountAmountShow").html(discountamount);
	
	$("#q_TaxAmount").val(taxamount);
	$("#q_TaxAmountShow").html(taxamount);
	
}

function checkDuplicate()
{
	var error=0;
	var ex_imei=$("#ex_imei").val();
	if(ex_imei!=='')
	{
		$("#u_tbl tr #item_IMEI").each(function(index,elem){
			
			
			if(ex_imei==elem.value)
			{
				alert("Duplicate Entry For this IMEI ..");
				$("#ex_imei").val('');
				error++;
			}
		});
	}
	if(error)
	{
		return false;
	}
	else
	{
		return true;
	}
}

function checkDuplicateItem()
{
	var error=0;
	var ex_item_id=$("#ex_item_id_from_imei").val();
	if(ex_item_id!=='' && ex_item_id!==0)
	{
		$("#u_tbl tr #item_id").each(function(index,elem){
			
			
			if(ex_item_id==elem.value)
			{
				alert("Selected item Is Already In Cart ..");
				$("#ex_item").focus();
				error++;
			}
		});
	}
	if(error)
	{
		return false;
	}
	else
	{
		return true;
	}
}

</script>
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


<?php include ("inc/scripts.php");
 
?>

<!-- PAGE RELATED PLUGIN(S) -->
<script src="<?php echo ASSETS_URL;?>/js/plugin/datatables/jquery.dataTables.min.js"></script>
<script src="<?php echo ASSETS_URL;?>/js/plugin/datatables/dataTables.colVis.min.js"></script>
<script src="<?php echo ASSETS_URL;?>/js/plugin/datatables/dataTables.tableTools.min.js"></script>
<script src="<?php echo ASSETS_URL;?>/js/plugin/datatables/dataTables.bootstrap.min.js"></script>
<script src="<?php echo ASSETS_URL;?>/js/plugin/datatable-responsive/datatables.responsive.min.js"></script>

<script type="text/javascript">

// DO NOT REMOVE : GLOBAL FUNCTIONS!
function NewCustomerModal()
{
  //document.getElementById("current_row").value=val;
  $("#NewCustomerModal").modal("show");
}



$(document).ready(function() {


	<?=isset($id) && $id >0 ? 'calculate();' : ''?>
	<?=isset($id) && $id >0 ? 'totalItems();' : ''?>
	/* // DOM Position key index //
		
	l - Length changing (dropdown)
	f - Filtering input (search)
	t - The Table! (datatable)
	i - Information (records)
	p - Pagination (paging)
	r - pRocessing 
	< and > - div elements
	<"#id" and > - div with an id
	<"class" and > - div with a class
	<"#id.class" and > - div with an id and class
	
	Also see: http://legacy.datatables.net/usage/features
	*/	

	/* BASIC ;
*/
		var responsiveHelper_dt_basic = undefined;

		var responsiveHelper_datatable_fixed_column = undefined;

		var responsiveHelper_datatable_col_reorder = undefined;

		var responsiveHelper_datatable_tabletools = undefined;

		
		var breakpointDefinition = {
			tablet : 1024,
			phone : 480
		};


		$('#dt_basic').dataTable({
			"sDom": "<'dt-toolbar'<'col-xs-12 col-sm-6'f><'col-sm-6 col-xs-12 hidden-xs'l>r>"+
				"t"+
				"<'dt-toolbar-footer'<'col-sm-6 col-xs-12 hidden-xs'i><'col-xs-12 col-sm-6'p>>",
			"autoWidth" : true,
			"preDrawCallback" : function() {
				// Initialize the responsive datatables helper once.
				if (!responsiveHelper_dt_basic) {
					responsiveHelper_dt_basic = new ResponsiveDatatablesHelper($('#dt_basic'), breakpointDefinition);

				}
			},
			"rowCallback" : function(nRow) {
				responsiveHelper_dt_basic.createExpandIcon(nRow);

			},
			"drawCallback" : function(oSettings) {
				responsiveHelper_dt_basic.respond();

			}
		});


	/* END BASIC */
	
	/* COLUMN FILTER */
 var otable = $('#datatable_fixed_column').DataTable({
 	//"bFilter": false,
 	//"bInfo": false,
 	//"bLengthChange": false
 	//"bAutoWidth": false,
 	//"bPaginate": false,
 	//"bStateSave": true // saves sort state using localStorage
		"sDom": "<'dt-toolbar'<'col-xs-12 col-sm-6 hidden-xs'f><'col-sm-6 col-xs-12 hidden-xs'<'toolbar'>>r>"+
				"t"+
				"<'dt-toolbar-footer'<'col-sm-6 col-xs-12 hidden-xs'i><'col-xs-12 col-sm-6'p>>",
		"autoWidth" : true,
		"preDrawCallback" : function() {
			// Initialize the responsive datatables helper once.
			if (!responsiveHelper_datatable_fixed_column) {
				responsiveHelper_datatable_fixed_column = new ResponsiveDatatablesHelper($('#datatable_fixed_column'), breakpointDefinition);

			}
		},
		"rowCallback" : function(nRow) {
			responsiveHelper_datatable_fixed_column.createExpandIcon(nRow);

		},
		"drawCallback" : function(oSettings) {
			responsiveHelper_datatable_fixed_column.respond();

		}		
	
 });

 
 // custom toolbar
 /* $("div.toolbar").html('<div class="text-right"><img src="img/logo.png" alt="SmartAdmin" style="width: 111px;
 margin-top: 3px;
 margin-right: 10px;
"></div>');
*/
 	 
 // Apply the filter
 $("#datatable_fixed_column thead th input[type=text]").on( 'keyup change', function () {
 	
 otable
 .column( $(this).parent().index()+':visible' )
 .search( this.value )
 .draw();

 
 } );

 /* END COLUMN FILTER */ 

})


function checkParameters(){
	var q_TotalItems = $.trim($("#q_TotalItems").val());
	var client_id = $.trim($("#client_id").val());
	var sp_Amount = $.trim($("#sp_Amount").val());
	var s_SaleMode = $.trim($("#s_SaleMode").val());
	//alert(client_id);
	if (client_id == 0)
	{
		$.smallBox({
		title : "Error",
		content : "<i class='fa fa-clock-o'></i> <i>Please Give Customer Information.</i>",
		color : "#C46A69",
		iconSmall : "fa fa-times fa-2x fadeInRight animated",
		timeout : 4000
		});
	$("#client_Name").focus();
	return false;
	}
	
	if (q_TotalItems == 0)
	{
		$.smallBox({
		title : "Error",
		content : "<i class='fa fa-clock-o'></i> <i>Please choose atlease one item.</i>",
		color : "#C46A69",
		iconSmall : "fa fa-times fa-2x fadeInRight animated",
		timeout : 4000
		});
		
	$("#ex_imei").focus();
	return false;

	}
	
	
	
	if (s_SaleMode=='cash' && (sp_Amount == 0 || sp_Amount==''))
	{
		$.smallBox({
		title : "Error",
		content : "<i class='fa fa-clock-o'></i> <i>Your sale mode is cash please fill received cash feild.</i>",
		color : "#C46A69",
		iconSmall : "fa fa-times fa-2x fadeInRight animated",
		timeout : 4000
		});
	$("#client_Name").focus();
	return false;
	}
}





</script>
<script type="text/javascript">
$("#client_Name").blur(function(){
	var selectedOption = $('option[value="'+$("#client_Name").val()+'"]');
	if(selectedOption.attr("value")==undefined)
	{
		//alert('client name must be selected');
	}
	else
	{
		
		var client_id = selectedOption.attr("data-value");
		var allVars="client_id="+client_id;
		$.ajax
		({
			type: "POST",
			url: "getAjaxClientBalance.php",
			dataType: 'json',
			data:allVars,
			cache: false,
			success: function(data)
			{ 
				
				$("#previous_storecredit").html(data['client_storecredit']);		
				$("#previous_balance").html(data['client_balance']);
				$("#client_id").val(client_id);
			}
			
		});
	  
	}
});


function getItemDetail(val)
{
	var sup_id=0;
	
	item_name=$('#ex_item').val();
	if(item_name!=='')
	{
		var selectedOption = $('option[value="'+$("#ex_item").val()+'"]');
		item_id=selectedOption.attr("data-item-id");
		if(item_id==undefined)
		{
			item_name=$('#ex_item').val();
			$('#ex_itemname').val(item_name);
			$('#ex_item_id_from_imei').val(0);
		}
		else
		{
			var allVars="item_id="+item_id+"&sup_id="+sup_id;
			$.ajax
				({
				type: "post",
				 url: "purchase_add_json.php",
				 dataType: 'json',
				 data:allVars,
				 cache: false,
				 success: function(data)
				 { 
						
						$("#ex_item_id_from_imei").val(item_id);
						$("#ex_itemname").val(data.item_Name);
						$("#ex_itemcode").val(data.item_Code);
						$("#ex_costprice").val(data.item_PurchasePrice);
						$("#ex_qty").val('1');
						$("#ex_rate").val(data.item_SalePrice);
						$("#ex_netamount").val(data.item_SalePrice);
						//$("#ex_imei").val(data.item_Code);
						$("#ex_item_stock").val(data.item_CurrentStock);
						$("#ex_stock").val(data.item_CurrentStock);

						get_Last_Records('item_id', item_id);
						
						$("#ex_qty").focus();
						$("#ex_qty").select();
				 },
				 error:function(data)
				 {
					alert("Pleasee Choose An Item First.");
		
				 }
				});
		}
	}
}
function calculate_netamount_row(val)
{
	ex_netamount=ex_discountpercentage=0;
	var ex_qty=$("#ex_qty").val();
	var ex_rate=$("#ex_rate").val();
	var ex_discount_percentage=$("#ex_discount_percentage").val();

	ex_netamount=(ex_qty*ex_rate);

	var discountAmt=(ex_netamount*(ex_discount_percentage/100));
	
	ex_netamount   = parseInt(ex_netamount-discountAmt);	

	$("#ex_discount_amount").val(discountAmt);
	$("#ex_netamount").val(ex_netamount);
}

$("#client_Name").keypress(function(e){
	if(e.keyCode==13)
	{
		$("#ex_imei").focus();
		$("#ex_imei").select();
	}
});
$("#ex_qty").keypress(function(e){
	if(e.keyCode==13)
	{
		$("#ex_rate").focus();
		$("#ex_rate").select();
	}
});
$("#ex_rate").keypress(function(e){
	if(e.keyCode==13)
	{
		$("#ex_discount_percentage").focus();
	}
});
$("#ex_discount_percentage").keypress(function(e){
	if(e.keyCode==13)
	{
		$("#ex_costprice").focus();
	}
});
$("#ex_costprice").keypress(function(e){
	if(e.keyCode==13)
	{
		addToTable();
	}
});

function checkParameters_NewCustomer(){
	var new_client_Name = $.trim($("#new_client_Name").val());
	if (new_client_Name == '')
	{
		$.smallBox({
		title : "Error",
		content : "<i class='fa fa-clock-o'></i> <i>Please Fill Customer Name.</i>",
		color : "#C46A69",
		iconSmall : "fa fa-times fa-2x fadeInRight animated",
		timeout : 4000
		});
	$("#new_client_Name").focus();
	return false;
	}
}
</script>


<script type="text/javascript">
function del(val){

 $.SmartMessageBox({
 title : "Attention required!",
 content : "This is a confirmation box. Do you want to delete the Record?",
 buttons : '[No][Yes]'
 }, function(ButtonPressed) {
 if (ButtonPressed === "Yes") {


$.post("ajax/delAjax.php",
 {
 s_id : val, 
 },
 function(data,status){ 
 if(data.trim()!="")
 {
 		$.smallBox({
		 title : "Delete Status",
		 content : "<i class='fa fa-clock-o'></i> <i>Record Deleted successfully...</i>",
		 color : "#659265",
		 iconSmall : "fa fa-check fa-2x fadeInRight animated",
		 timeout : 4000
		 });

 		window.location="sale_add"
 }
 else
 {
 	 $.smallBox({
		 title : "Delete Status",
		 content : "<i class='fa fa-clock-o'></i> <i>Problem Deleting Record...</i>",
		 color : "#C46A69",
		 iconSmall : "fa fa-times fa-2x fadeInRight animated",
		 timeout : 4000
		 });

 }
 });
 
 }
 if (ButtonPressed === "No") {
 $.smallBox({
 title : "Delete Status",
 content : "<i class='fa fa-clock-o'></i> <i>You pressed No...</i>",
 color : "#C46A69",
 iconSmall : "fa fa-times fa-2x fadeInRight animated",
 timeout : 4000
 });

 }
 });
 e.preventDefault();
 }
</script>