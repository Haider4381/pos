<?php
include('sessionCheck.php');
include('connection.php');
include('functions.php');
require_once ("inc/init.php");
require_once ("inc/config.ui.php");
$page_title = "Quotation";
include ("inc/header.php");
//$page_nav["Sales"]["active"] = true;
include ("inc/nav.php");
$branch_id=$_SESSION['branch_id'];
$u_id=$_SESSION['u_id'];
?>
<!-- ==========================CONTENT STARTS HERE ========================== -->
<!-- MAIN PANEL -->
<div id="main" role="main">

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
    width: 400px;
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
				<h2>Quotation</h2>
			</header>

			<!-- widget div-->
			<div>		


<!-- widget content -->
<div class="widget-body no-padding">

<?php
if(isset($_POST['save']))
{
	$error=0;
	$client_id=$_POST['client_id'];
	$sale_id=$_POST['s_id'];
	$s_PaymentType=$_POST['s_PaymentType'];
	$s_Remarks=$_POST['s_Remarks'];
	$s_RemarksExternal=$_POST['s_RemarksExternal'];
	
	$s_TotalItems=$_POST['s_TotalItems'];
	$s_TotalAmount=$_POST['s_TotalAmount'];
	$s_TaxAmount=$_POST['s_TaxAmount']+0;
	$s_Tax=$_POST['s_Tax']+0;
	$s_DiscountAmount=$_POST['s_DiscountAmount']+0;
	$s_Discount=$_POST['s_Discount']+0;
	$s_DiscountPrice=0;
	$s_NetAmount=$_POST['s_NetAmount'];
	$s_Date=date('Y-m-d');
	$item_idArray=array_filter($_POST['item_id']);
	$item_BarCodeArray=$_POST['item_BarCode']=0;
	$item_IMEIArray=$_POST['item_IMEI'];
	$item_SalePriceArray=$_POST['item_Rate'];
	$item_DiscountPercentageArray=$_POST['item_DiscountPercentage'];
	$item_DiscountPriceArray=$_POST['item_DiscountPrice'];
	$item_QtyArray=$_POST['item_Qty'];
	$item_CostPriceArray=$_POST['item_CostPrice'];
	$item_NetPriceArray=$_POST['item_NetPrice'];
	
	if(empty($item_idArray))
	{
		echo '<script> alert("Atleast 1 item must be selected");
				window.location="";</script>';
	die();
	}
	$NumberCheckQ="SELECT MAX(sr_NumberSr) as sr_Number FROM adm_quotation WHERE 1 AND branch_id='$branch_id' ";
	$NumberCheckRes=mysqli_query($con,$NumberCheckQ);
	$prefix='Q';
	if(mysqli_num_rows($NumberCheckRes)<1)
	{
		$s_NumberSr=1;
		$s_Number=$prefix.'1';
	}
	else
	{
		$r=mysqli_fetch_assoc($NumberCheckRes);
		$s_NumberSr=$r['sr_Number']+1;
		$s_Number=$prefix.$s_NumberSr;
		//$s_Number=$s_Number.'-'.$client_id;
	}
	
	 $sQ="INSERT INTO adm_quotation(s_id, sr_Number,sr_NumberSr, sr_Date, client_id, sr_TotalAmount, sr_Discount,sr_DiscountAmount, sr_NetAmount, sr_TotalItems, sr_Remarks,sr_RemarksExternal, sr_CreatedOn, u_id, branch_id,sr_PaymentType,sr_Tax, sr_TaxAmount) 
		VALUES ('$sale_id','$s_Number','$s_NumberSr','$s_Date','$client_id','$s_TotalAmount','$s_Discount','$s_DiscountAmount','$s_NetAmount', '$s_TotalItems', '$s_Remarks','$s_RemarksExternal',now(), '$u_id', '$branch_id','$s_PaymentType','$s_Tax', '$s_TaxAmount')";
	if(mysqli_query($con,$sQ))
	{
		$s_id=mysqli_insert_id($con);
		foreach ($item_idArray as $key => $item_id)
		{
			$item_BarCode=0;
			$item_IMEI=$item_IMEIArray[$key];
			$item_SalePrice=$item_SalePriceArray[$key];
			$item_DiscountPercentage=$item_DiscountPercentageArray[$key];
			$item_DiscountPrice=$item_DiscountPriceArray[$key];
			$item_CostPrice=$item_CostPriceArray[$key];
			$item_Qty=$item_QtyArray[$key];
			$item_InvoiceAmount=0;
			$item_SaleScheme=0;
			$item_SaleExtraAmount=0;
			$item_NetPrice=$item_NetPriceArray[$key];
			
	 		$sdQ="INSERT INTO adm_quotation_detail (sr_id, srd_Date, item_id, item_BarCode,		item_IMEI, item_Qty,		item_SalePrice,		item_CostPrice,		item_NetPrice,srd_CreatedOn,client_id,item_DiscountPercentage,item_DiscountPrice)
										VALUES ($s_id,'$s_Date',$item_id,'$item_BarCode',	'$item_IMEI', '$item_Qty',	'$item_SalePrice',	'$item_CostPrice',	'$item_NetPrice',now(),$client_id,'$item_DiscountPercentage','$item_DiscountPrice')";
			if(mysqli_query($con,$sdQ))
			{
				//do something
			}
			else
			{
				$error++;
			}
		}
	if(!empty($_POST['sp_Amount']))
	{
		$sp_Amount=$_POST['sp_Amount'];
		$spQ="INSERT INTO adm_sale_payment(client_id, sp_Amount, sp_Date, s_id, sp_Description,sp_Type, sp_CreatedOn, u_id, branch_id) VALUES ($client_id,'$sp_Amount','$s_Date',$s_id,'Quotation Payment','SR',now(), '$u_id', '$branch_id')";
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
 
if(empty($error))
{
	//$_SESSION['msg']='<div class="alert alert-success">Invoice Saved Successfully.</div>';
	
?>
	
		<script type="text/javascript">
		x = confirm("Quotation Saved Successfully.");
		if(x)
		{
			//return true;
			//window.open('invoice_print.php?s_id=<?php echo $s_id;?>','popUpWindow','height=488,width=1022,left=120,top=200,resizable=yes,scrollbars=yes,toolbar=no,menubar=no,location=no,directories=no, status=no');
			window.location="";
			//window.location.href=''
		}
		else
		{
			window.location="";
		}
		</script>
		
<?php
	//die();
}
else
{
	$_SESSION['msg']='<div class="alert alert-danger">Problem Saving Quotation.</div>';
}
} 
?>

<?php if(!empty($_SESSION['msg'])){ echo $_SESSION['msg'];unset($_SESSION['msg']);} ?>


<br style="clear:both;" />
<form id="checkout-form" class="smart-form" novalidate="novalidate" method="post" action="" onsubmit="return checkParameters();">	
	<fieldset style="padding:0px 10px 10px 10px !important;">
    <input type="hidden" value="0" name="s_id" id="s_id" />
		<div class="row" style="margin-bottom: 5px;">
			<div class="col col-lg-1">Customer</div>
			<div class="col col-lg-3">
				<select class="select2" name="client_id" id="client_id">
				<option value="0">Search Customer</option>
				<?php
				$client_query = "SELECT client_id,client_Name,client_Phone FROM adm_client WHERE client_Status='A' AND branch_id=$branch_id";
				$client_run = mysqli_query($con, $client_query);
				while ($clientRow = mysqli_fetch_assoc($client_run))
				{ ?>
				<option value="<?php echo $clientRow['client_id'] ?>"><?php echo $clientRow['client_Name'].' / '.$clientRow['client_Phone'] ?></option>
				<?php } ?>

				</select>
			</div>
		</div>
		<div class="row" style="margin-top:5px">
            <div class="col col-lg-12 col-xs-12 col-md-12">
            <table style="width:100%; background:#f1f1f1;" class="table table-condensed">
            	<tr>
                	<th style="width:15%; display:none;">Search Product</th>
                    <th style="width:40%;">Product Name</th>
                    <th style="width:10%;">Quantity</th>
                    <th style="width:10%;">Unit Price</th>
                    <th style="width:10%;">Discount%</th>
                    <th style="width:10%;">Total</th>
                    <th style="width:10%; display:none;">Cost Price</th>
                    <th rowspan="2" style="width:5%;">
                    	<p class="btn btn-primary" style="background:#09F; border:none; padding:12px; font-size:25px;" onclick="addToTable();"><i class="fa fa-shopping-cart"></i></p>
                    </th>
                </tr>
                <tr>
                	<td style="display:none;">
                    	<input type="text"  id="ex_imei" class="form-control" >
                        <input type="hidden"  id="ex_itemname" class="form-control" >
                        <input type="hidden"  id="ex_item_id_from_imei" class="form-control" >
                    </td>
                    <td>
                    	<input list="item_list"  name="" id="ex_item" class="form-control" placeholder="Search Product by Name" required autocomplete="off"  onblur="getItemDetail()" style="padding-left:8px;">
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
                    
                    <td><input type="number" id="ex_qty" class="form-control" style="text-align:center;" onkeyup="calculate_netamount_row()"></td>
                    <td><input type="number" id="ex_rate" class="form-control"  style="text-align:right" onkeyup="calculate_netamount_row()"></td>
                    <td><input type="number" id="ex_discount_percentage" class="form-control"  style="text-align:right" onchange="calculate_netamount_row()" onkeyup="calculate_netamount_row()" autocomplete="off"></td>
                    <td><input type="number" id="ex_netamount" readonly="readonly" class="form-control" style="text-align:right" ></td>
                    <td style="display:none;"><input type="text" id="ex_costprice" class="form-control" ></td>
                    <input type="hidden" id="ex_discount_amount">
                </tr>
            </table>
            </div>
            </div>
			
			<table class="table table-bordered" style=" width:100%;margin-top:10px;">
				<tr>
					<th style="width:15%; display:none;">Item Code</th>
                    <th style="width:35%;">Product Name</th>
					<th style="width:10%">Quantity</th>
					<th style="width:10%;">Unit Price</th>					
					<th style="width:10%;">Discount%</th>
					<th style="width:8%;">Discount Amt</th>					
					<th style="width:10%; display:none;">Cost Price</th>
					<th style="width:10%">Ext Amount</th>
					<th style="width:10%;">Action</th>
				</tr>
			</table>
        
        
            
            
			<div style="min-height:100px;overflow:auto"><!--A wrapper div to control the height of table-->
				<table class="table table-bordered table-condensed" id="u_tbl">
					<tr id="u_row" style="display: none;">
						<td id="show_imei" style="width:15%; display:none;"></td>
                        <td id="show_item" style="width:35%;"></td>
						<th id="show_qty" style="width: 10%;"></th>
						<th id="show_rate" style="width: 10%;"></th>
						<th id="show_discount_percentage" style="width: 8%;"></th>
						<th id="show_discount_amount" style="width: 8%;"></th>
						<th id="show_costprice" style="width: 10%; display:none;"></th>
						<th id="show_netprice" style="width: 10%;"></th>
						<td style="width:10%;">
							<p class="btn btn-danger" onclick="delRow(this)">Delete</p> 
						</td>

						<input type="hidden" name="item_id[]" id="item_id">
						<!-- <input type="hidden" name="item_BarCode[]" id="item_BarCode"> -->
						<input type="hidden" name="item_IMEI[]" id="item_IMEI">
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
                    <div class="col-lg-12 col-md-12 col-xs-12">
                        <table style="width:100%; background:#f1f1f1;" class="table table-condensed">
                            <tr>
                                <th style="width:10%">Internal Notes</th>
                                <th style="width:10%">External Notes</th>
                                <th style="width:50%" rowspan="2">
                                	<table style="width:100%;">
                                    	<tr>
                                        	<td style="width: 33%;">Discount(%)<br /><input type="number" name="s_Discount" id="s_Discount" placeholder="Enter Diss. %" class="form-control" onkeyup="calculate()"  onchange="calculate();" style="text-align:right"  min="0"></td>
                                        	<td style="width: 3%;">&nbsp;</td>
                                            <td style="width: 33%;">VAT(%)<br /><input type="number" name="s_Tax"  placeholder="Enter Tax %" id="s_Tax" class="form-control" style="text-align:right" min="0" onkeyup="calculate()" onchange="calculate();"></td>
                                            <td style="width: 3%;">&nbsp;</td>
                                            
                                            <td style="width: 33%;">
                                            	Payment Method<br />
                                            	<select name="s_PaymentType" style="width:100%; font-size: 15px; margin-top: 3px; height: 33px;">
                                                    <option value="cash">Cash Payment</option>
                                                    <option value="bank">Bank Payment</option>
                                                    <option value="creditcard">Credit Card</option>
                                                </select>
                                            </td>
                                            
                                        </tr>
                                        <tr>
                                        	<td>
                                            Diss.(Amount)<br />
                                            <h1 style="    color: black; padding:5px;font-size: 20px; text-align: right;background: lightgray;"><?=$currency_symbol?><span id="s_DiscountAmountShow">0</span></h1>
                                            <input type="hidden" name="s_DiscountAmount" id="s_DiscountAmount" class="form-control" style="text-align:right" min="0" readonly="readonly">
                                            </td>
                                            <td>&nbsp;</td>
                                            <td>
                                            VAT(Amount)<br />
                                            <h1 style="    color: black;font-size: 20px; padding:5px; text-align: right;background: lightgray;"><?=$currency_symbol?><span id="s_TaxAmountShow">0</span></h1>
                                            <input type="hidden" name="s_TaxAmount" id="s_TaxAmount" class="form-control" style="text-align:right" min="0" readonly="readonly">
                                            </td>
                                            <td>&nbsp;</td>
                                            <td>
                                            	
                                            </td>
                                            
                                        </tr>
                                    </table>
                                	
                                    
                                    
                                </th>
                                <th style="width:30%; background:#09F !important;" rowspan="2">
                                    <p style="padding:0; float:left; color:#FFF;">Due Amount</p><p style="padding:0; margin:0; float:right; color:#FFF;">Total Items: <span id="totalItems"></span></p>
                                    <br />
                                    <h1 style=" color:#FFF; font-size:40px; text-align:center;"><?=$currency_symbol?><span id="s_NetAmountShow">0</span></h1>
                                    <input type="hidden" name="s_NetAmount" id="s_NetAmount" value="0">
                                    <input type="hidden" name="s_TotalAmount" id="s_TotalAmount" value="0">
                                    
                                    <input type="hidden" name="s_TotalItems" id="s_TotalItems" value="0">
                                    
                                    <input type="hidden" name="save" value="1">
                                    <p type="submit" class="btn btn-warning" style="font-weight: bold;padding: 5px 30px; background:orange;" id="submit" name="submit" onclick="saveForm();">Save </p>
                                </th>
                            </tr>
                            <tr>
                                <td><textarea class="form-control" name="s_Remarks" id="s_Remarks" style="width:230px;max-width:230px; min-width:230px; min-height:80px;height:80px; max-height:130px;"></textarea></td>
                                <td><textarea class="form-control" name="s_RemarksExternal" id="s_RemarksExternal" style="width:230px;max-width:230px; min-width:230px; min-height:80px;height:80px; max-height:130px;"></textarea></td>
                            </tr>
                        </table>
                    </div>
                </div>
                <table style="background:#09F; padding:10px; width:40%; float:right; display: none;">
                	<tr>
                    	<td style="padding:10px;display: none;">
                        <strong style="color:#FFF;">Bill Return (Amount)</strong><br /><input type="number" name="sp_Amount" placeholder="Enter Received Amount" value="0" class="form-control" style="text-align:right" min="0">
						</td>
                        <td><br>
                        	<input type="hidden" name="save" value="1">
                        </td>
                    </tr>
                </table>
		</fieldset>
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
		<form id="checkout-form" class="smart-form" novalidate="novalidate" method="post" action=""  onsubmit="return checkParameters_NewCustomer();">	
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
function saveForm()
{
	$("#checkout-form").submit();

}

$("#ex_imei").keypress(function(e){
	if(e.keyCode==13)
	{
		getDBData();

	}
});

$("#ex_rate").keypress(function(e){
	if(e.keyCode==13)
	{
		addToTable();

	}
});

$("#ex_discountpercentage").keypress(function(e){
	if(e.keyCode==13)
	{
		addToTable();

	}
});

$("#ex_item").keypress(function(e){
	if(e.keyCode==13)
	{
		$("#ex_qty").focus();

	}
});





function postDataForCustomerDsiplay()
{
	alert('customerdisplay');
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
		alert('Please Choose Item First or Scan Item Code/IMEI');
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
			$("#ex_item_id_from_imei").val(data.item_id_from_imei);
			
			
	 		$("#ex_saleprice").focus();

			window.newtest=data.item_NetAmount
	 		window.item_Name=data.item_Name;
	 		window.item_BarCode=data.item_BarCode;
	 		window.item_id=data.item_id_from_imei;
			
			addToTable();
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
	var ex_saleprice=$("#ex_rate").val();
	var ex_costprice=$("#ex_costprice").val();
	var ex_qty=$("#ex_qty").val();
	var ex_rate=$("#ex_rate").val();
	var ex_discount_percentage=$("#ex_discount_percentage").val();
	var ex_discount_amount=$("#ex_discount_amount").val();
	var ex_netamount=$("#ex_netamount").val();
	var ex_imei=$("#ex_imei").val();
	var ex_itemname=$("#ex_itemname").val();
	var ex_itemid=$("#ex_item_id_from_imei").val();

	ex_discount_percentage= ex_discount_percentage ? parseFloat(ex_discount_percentage) : '0';
	ex_discount_amount= ex_discount_amount ? parseFloat(ex_discount_amount) : '0';

	var opt 				= $('option[value="'+$("#ex_item").val()+'"]');
	var max_qty				= opt.attr("data-item-qty");

	if(ex_qty>max_qty)
	{
		alert("You can't Return more Quantity than Customer Purchased");
		$('#ex_qty').focus();
		$('#ex_qty').select();
		return false;
	}
	
	//alert(ex_saleprice);
	//alert(item_id);
	if(ex_rate=='' || ex_itemid=='')
	{
		alert('Please choose item first..');
		$("#ex_imei").focus();

	}
	else 
	{
		if(checkDuplicate())
		{
			var newRow=$("#u_row").clone().show();
			$(newRow).find("#show_item").text(ex_itemname);
			$(newRow).find("#show_qty").text(ex_qty);
			$(newRow).find("#show_rate").text(ex_rate);
			$(newRow).find("#show_costprice").text(ex_costprice);
			$(newRow).find("#show_discount_percentage").text(ex_discount_percentage);
			$(newRow).find("#show_discount_amount").text(ex_discount_amount);
			//alert(ex_costprice);
			$(newRow).find("#show_imei").text(ex_imei);
			$(newRow).find("#show_netprice").text(ex_netamount);
			
	
			$(newRow).find("#item_id").val(ex_itemid);
			$(newRow).find("#item_IMEI").val(ex_imei);
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
		}
		
		$("#ex_item").val("");
		$("#ex_imei").val("");
		$("#ex_qty").val("");
		$("#ex_rate").val("");
		$("#ex_discount_percentage").val("");
		$("#ex_discount_amount").val("");
		$("#ex_costprice").val("");
		$("#ex_netamount").val("");
		$("#ex_item").focus();
		calculate();
		totalItems();
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
	$("#s_TotalItems").val(totalItems-1);
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
	
	$("#s_TotalAmount").val(net_sum);
	
	var s_Discount=$("#s_Discount").val();
	
	if(s_Discount!='' && s_Discount!==0)
	{
		discountamount=s_Discount/100*net_sum;
		net_sum=parseFloat(net_sum) - parseFloat(discountamount);
	}
	
	
	var s_Tax=$("#s_Tax").val();
	if(s_Tax!='' && s_Tax!==0)
	{
		taxamount=s_Tax/100*net_sum;
		net_sum=parseFloat(net_sum) +parseFloat(taxamount);
	}
	
	
	discountamount=parseFloat(discountamount).toFixed(2);
	taxamount=parseFloat(taxamount).toFixed(2);
	net_sum=parseFloat(net_sum).toFixed(2);	
	
	$("#s_NetAmount").val(net_sum);
	$("#s_NetAmountShow").html(net_sum);
	
	$("#s_DiscountAmount").val(discountamount);
	$("#s_DiscountAmountShow").html(discountamount);
	
	$("#s_TaxAmount").val(taxamount);
	$("#s_TaxAmountShow").html(taxamount);
	
	
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
	var s_TotalItems = $.trim($("#s_TotalItems").val());
	var client_id = $.trim($("#client_id").val());

	if (s_TotalItems == 0)
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
}





</script>
<script type="text/javascript">

function getItemDetail(val)
{
	item_name=$('#ex_item').val();
	if(item_name!=='')
	{
		var selectedOption = $('option[value="'+$("#ex_item").val()+'"]');
		item_id=selectedOption.attr("data-item-id");
		item_qty=selectedOption.attr("data-item-qty");
		item_name=selectedOption.attr("data-item-name");
		item_saleprice=selectedOption.attr("data-item-saleprice");
		item_netprice=selectedOption.attr("data-item-netprice");
		item_code=selectedOption.attr("data-item-code");		
		if(item_id==undefined || item_id==0)
		{
			alert('Item Selection Error');
			return false;
		}
		else
		{
			$('#ex_qty').val(item_qty);
			$('#ex_rate').val(item_saleprice);
			$('#ex_netamount').val(item_netprice);
			$("#ex_imei").val(item_code);
			$("#ex_itemname").val(item_name);
			$("#ex_item_id_from_imei").val(item_id);
			
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