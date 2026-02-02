<?php
include('sessionCheck.php');
include('connection.php');
include('functions.php');
require_once ("inc/init.php");
require_once ("inc/config.ui.php");
$page_title = "Purchase Return";
include ("inc/header.php");
//$page_nav["Purchases"]["active"] = true;
include ("inc/nav.php");
$u_id=$_SESSION['u_id'];
$branch_id=$_SESSION['branch_id']; 
$pr_NumberPrefix='PR';
?>
<!-- ==========================CONTENT STARTS HERE ========================== -->
<!-- MAIN PANEL -->
<div id="main" role="main">

	
<?php $breadcrumbs["Purchases"] = "";
include("inc/ribbon.php");
 
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
				<span class="small_icon"><i class="fa fa-tags"></i>	</span>	
				<h2>Purchase Return</h2>
			</header>

			<!-- widget div-->
			<div>		


<!-- widget content -->
<div class="widget-body no-padding">

<?php
if(isset($_POST['save']))
{
    $error=0;
    $location_after_save='window.location.href=""';
    $save_value=$_POST['save_value'];
    $sup_id=$_POST['sup_id'];
    $pr_Date=$_POST['pr_Date'];
    $pr_BillNo=$_POST['pr_BillNo'];
    $pr_Remarks=$_POST['pr_Remarks'];
    $pr_VendorRemarks=$_POST['pr_VendorRemarks'];
    $pr_TotalAmount=$_POST['pr_TotalAmount'];

    $pr_TaxAmount=$_POST['pr_TaxAmount']+0;
    $pr_Tax=$_POST['pr_Tax']+0;
    $pr_DiscountAmount=$_POST['pr_DiscountAmount']+0;
    $pr_Discount=$_POST['pr_Discount']+0;

    $pr_NetAmount=$_POST['pr_NetAmount'];
    $item_IMEI=0; //$_POST['item_IMEI'];
    $item_Qty=$_POST['item_Qty'];
    $item_Rate=$_POST['item_Rate'];
    $item_TotalAmount=$_POST['item_TotalAmount'];
    $item_DiscountPercentageArray=$_POST['item_DiscountPercentage'] ?? [];
    $item_DiscountAmount=$_POST['item_DiscountAmount'];
    $item_NetAmount=$_POST['item_NetAmount'];
    $item_InvoiceAmountArray=$_POST['item_InvoiceAmount'];
    $item_idArray=array_filter($_POST['item_id']);
    $pp_Amount=$_POST['pp_Amount'] ?? 0;
    $branch_id = $_SESSION['branch_id'] ?? 1;
    $u_id = $_SESSION['u_id'] ?? 1;
    $pr_NumberPrefix = 'PRT';

    if($save_value=='save_and_close')
    {
        $location_after_save='window.location.href="dashboard"';
    }

    $pNumberQ=mysqli_fetch_assoc(mysqli_query($con, "select (ifnull(MAX(pr_NumberSr),0)+1) as pr_Number from adm_purchasereturn where branch_id=$branch_id"));
    $pr_NumberSr=$pNumberQ['pr_Number'];
    $pr_Number=$pr_NumberPrefix.$pNumberQ['pr_Number'];

    // --- Purchase Return Account Query (STRICT MATCH) ---
    $pr_acc_row = mysqli_fetch_assoc(mysqli_query($con, "
        SELECT account_id FROM accounts_chart WHERE branch_id='$branch_id' AND (
            account_title = 'Purchase Return' OR
            account_title = 'purchase return' OR
            account_title = 'Purchase Return Account' OR
            account_title = 'purchase return account' OR
            account_title = 'PURCHASE RETURN ACCOUNT' OR
            account_title = 'PURCHASE RETURN' OR
            account_title = 'purchase return a/c'
        ) LIMIT 1
    "));
    $pr_account_id = isset($pr_acc_row['account_id']) ? $pr_acc_row['account_id'] : 0;

    // --- Supplier Account Query ---
    $supplier_acc_row = mysqli_fetch_assoc(mysqli_query($con, "SELECT account_id FROM accounts_chart WHERE account_id = '$sup_id' AND branch_id='$branch_id' LIMIT 1"));
    $supplier_account_id = isset($supplier_acc_row['account_id']) ? $supplier_acc_row['account_id'] : 0;

    $errorMsg = '';
    if(!$supplier_account_id){
        $errorMsg .= 'Supplier account not found in Chart of Accounts. Please create/select supplier account first!<br>';
    }
    if(!$pr_account_id){
        $errorMsg .= 'Purchase Return account not found in Chart of Accounts. Please create/select a Purchase Return account!<br>';
    }
    if(!empty($errorMsg)){
        $_SESSION['msg'] = '<div class="alert alert-danger">' . $errorMsg . '</div>';
        ?>
        <script type="text/javascript">
            alert("<?php echo strip_tags($errorMsg); ?>");
            window.location.href="purchase_return_add.php";
        </script>
        <?php
        exit;
    }

    if(!empty($item_idArray))
    {
        $purchaseQ="INSERT INTO adm_purchasereturn(pr_Date,pr_Number,pr_NumberSr, pr_BillNo, sup_id, pr_TotalAmount, pr_NetAmount, pr_Remarks, pr_VendorRemarks, pr_CreatedOn,u_id, branch_id,pp_Amount, pr_Discount,pr_DiscountAmount, pr_Tax, pr_TaxAmount) VALUES ('$pr_Date', '$pr_Number','$pr_NumberSr','$pr_BillNo','$sup_id','$pr_TotalAmount','$pr_NetAmount','$pr_Remarks','$pr_VendorRemarks',now(),'$u_id', '$branch_id','$pp_Amount', '$pr_Discount','$pr_DiscountAmount', '$pr_Tax', '$pr_TaxAmount')";
        if(mysqli_query($con,$purchaseQ))
        {
            $pr_id=mysqli_insert_id($con);
            foreach ($item_idArray as $key => $itemRow)
            {
                $item_id=$itemRow;
                $imei=is_array($item_IMEI) ? $item_IMEI[$key] : '';
                $item_qty=$item_Qty[$key];
                $item_rate=$item_Rate[$key];
                $totalamount=$item_TotalAmount[$key];
                $item_DiscountPercentage=$item_DiscountPercentageArray[$key] ?? 0;
                $item_DiscountAmountRow=$item_DiscountAmount[$key];
                $netamount=$item_NetAmount[$key];
                $invoiceamount=$item_InvoiceAmountArray[$key];
                $pdQ="INSERT INTO adm_purchasereturn_detail (pr_id, prd_Date, sup_id, item_id, item_IMEI, item_TotalAmount, item_NetAmount, prd_CreatedOn, item_InvoiceAmount, item_Qty, item_Rate, u_id, branch_id, item_DiscountPercentage, item_DiscountAmount) 
                            VALUES ('$pr_id','$pr_Date','$sup_id','$item_id','$imei','$totalamount','$netamount',now(),'$invoiceamount','$item_qty','$item_rate','$u_id','$branch_id','$item_DiscountPercentage','$item_DiscountAmountRow')";
                if(!mysqli_query($con,$pdQ))
                {
                    $error++;
                }
            }

            // --- Voucher Header ---
            $voucher_type = 'Purchase Return';
            $voucher_no = $pr_Number . '-RET';
            $voucher_desc = "Purchase Return Invoice #$pr_Number";
            $q_voucher = "INSERT INTO accounts_voucher (entry_date, voucher_type, voucher_no, description, created_by)
                          VALUES ('$pr_Date', '$voucher_type', '$voucher_no', '".mysqli_real_escape_string($con,$voucher_desc)."', '$u_id')";
            mysqli_query($con, $q_voucher);
            $voucher_id = mysqli_insert_id($con);

            // --- Double Entry ---
            // Debit Supplier (Liability decrease)
            mysqli_query($con, "INSERT INTO accounts_voucher_detail (voucher_id, account_id, description, debit, credit)
                                VALUES ($voucher_id, $supplier_account_id, '$voucher_desc', $pr_NetAmount, 0)");

            // Credit Purchase Return Account
            mysqli_query($con, "INSERT INTO accounts_voucher_detail (voucher_id, account_id, description, debit, credit)
                                VALUES ($voucher_id, $pr_account_id, '$voucher_desc', 0, $pr_NetAmount)");
        }
        else
        {
            $error++;
        }
    }
    else
    {
        $error++;
    }
    if(empty($error))
    {
        $_SESSION['msg']="<div class='alert alert-success'>Purchase Return Saved Successfully</div>";
    }
    else
    {
        $_SESSION['msg']="<div class='alert alert-danger'>Problem Saving Purchase Return</div>";
    }

    if(empty($error))
    {
    ?>
        <script type="text/javascript">
        x = confirm("Record Saved Successfully.");
        if(x)
        {
            <?=$location_after_save;?>
        }
        else
        {
            <?=$location_after_save;?>
        }
        </script>
    <?php
    }
    else
    {
        $_SESSION['msg']='<div class="alert alert-danger">Problem Saving Purchase Return.</div>';
    }
}
?>

<?php if(!empty($_SESSION['ppMsg'])){ echo $_SESSION['msg'];unset($_SESSION['ppMsg']);} ?>
<?php if(!empty($_SESSION['msg'])){ echo $_SESSION['msg'];unset($_SESSION['msg']);} ?>

	<form id="checkout-form" class="smart-form" method="post" action="" onsubmit="return checkParameters();">	
		<fieldset style="padding:0px !important;">
        	<input type="hidden" name="pr_Date" value="<?php echo date('Y-m-d');?>" placeholder="Select a date" class="form-control datepicker" data-dateformat="yy-mm-dd">
			<div class="row" style="margin-top:10px">
				<div class="col col-lg-12 col-md-12 col-xs-12">
                <table style="width:35%; background:#f1f1f1; float:left;" class="table table-condensed">
                	<tr><th colspan="2">Vendor Information</th></tr>
                    <tr>
                    	<td style="width:10%;">Vendor</td>
                        <td>
							<select class="form-control" name="sup_id" id="sup_id" required>
								<option value="">Select Vendor</option>
								<?php $supArray=get_Supplier();
								foreach ($supArray as $supRow) { ?>
								<option value="<?php echo $supRow['account_id'];?>"><?php echo $supRow['account_title'];?></option>
								<?php } ?>
							</select>
                        </td>
                    </tr>
                </table>
				<table style="width:35%; background:#f1f1f1; float:right;" class="table table-condensed">
                	<tr><th>Bill No.</th><th colspan="2" style="display: none;">Invoice Reference No.</th></tr>
                    <tr>
                    	<td style=" width:20%;font-size: 20px; color: #d65252;"><?php
                        	$pNumberQ=mysqli_fetch_assoc(mysqli_query($con, "select (ifnull(MAX(pr_NumberSr),0)+1) as pr_Number from adm_purchasereturn where branch_id=$branch_id"));
							echo $pr_Number=$pr_NumberPrefix.$pNumberQ['pr_Number'];
							?>
                        </td>
                    	
                        <td style=" width:50%; display: none;" ><input type="text" name="pr_BillNo" class="form-control" required="required" placeholder="Enter Reference Number"></td>
                    </tr>
                </table>
                </div>
			</div><!--End of row-->
            
			<div class="row" style="margin-top:5px">
            <div class="col col-lg-12 col-xs-12 col-md-12">
            <table style="width:100%; background:#f1f1f1;" class="table table-condensed">
            	<tr>
                	<th style="width:15%;">Product Code</th>
                    <th style="width:30%;">Search Product</th>
                    <th style="width:10%;">Quantity</th>
                    <th style="width:10%;">Unit Cost Price</th>
                    <th style="width:10%;">Discount</th>
                    <th style="width:10%;">Total</th>
                   <!-- <th style="width:20%;">IMEI Number</th>-->
                    <th rowspan="2" style="width:5%;">
                    	<p class="btn btn-primary" style="background:#09F; border:none; padding:12px; font-size:25px;" onclick="addToTable();"><i class="fa fa-shopping-cart"></i></p>
                    </th>
                </tr>
                <tr>
                	<td><input type="text" id="ex_itemcode" class="form-control" placeholder="Product Code" ></td>
                    <td>
                    	<select class="select2" name="" id="ex_item" onchange="getItemDetail()">
                        <option value="0">Search Product </option>
                        <?php $itemsArray=get_ActiveItems();
                         foreach ($itemsArray as $key => $itemRow) { 
                        ?>
                       <!-- <option value="<?php echo $itemRow['item_id'];?>"><?php echo $itemRow['item_Name'].' / '.$itemRow['item_Code'];?></option>-->
                       <option value="<?php echo $itemRow['item_id'];?>"><?php echo $itemRow['item_Name'];?></option>
                        <?php } ?>
                        </select>	
                    </td>
                    <td><input type="number" id="ex_qty" class="form-control"  onkeyup="calculate_netamount_row()" placeholder="Quantity"></td>
                    <td><input type="number" id="ex_rate" class="form-control"  onkeyup="calculate_netamount_row()" placeholder="Unit Cost Price"></td>
                    <td><input type="number" id="ex_discount_amount" class="form-control" style="text-align:right" onchange="calculate_netamount_row()" onkeyup="calculate_netamount_row()" autocomplete="off" placeholder="Discount (Rupees)"></td>
                    <td><input type="number" id="ex_netamount" class="form-control" placeholder="Total Amount" readonly="readonly"></td>
                    <!-- Hidden IMEI placeholder to avoid undefined access in JS -->
                    <input type="hidden" id="ex_imei" value="">
                    <!--<td><input type="text" id="ex_imei" class="form-control" placeholder="IMEI Number"></td>-->
                    
                </tr>
            </table>
            </div>
            </div>
			
			<table class="table table-bordered" style=" width:100%;margin-top:10px;">
				<tr>
					<th style="width:15%;">Product Code</th>
                    <th style="width:25%;">Product Name</th>
					<th style="width:10%">Quantity</th>
					<th style="width:10%">Cost Price</th>
					<th style="width:8%;">Discount Amt</th>
					<th style="width:10%">Ext Amount</th>
                    <!--<th style="width:20%;">IMEI Number</th>-->					
					<th style="width:10%;">Action</th>
				</tr>
			</table>

			<div style="height:152px;overflow:auto; border:1px solid #cccccc;"><!--A wrapper div to control the height of table-->
				<table class="table table-bordered" id="u_tbl">
					<tr id="u_row" style="display: none;">
						<td id="show_itemcode" style="width:15%"></td>
                        <td id="show_item" style="width:25%"></td>
                        <td id="show_qty" style="width:10%"></td>
						<td id="show_rate" style="width: 10%"></td>
						<td id="show_discount_amount" style="width:8%;"></td>
                        <!--<td id="show_imei" style="width:20%;">-->
						<td id="show_netamount" style="width: 10%"></td>
						<!--<td style="width:20%;">
                        	<input type="text" name="item_IMEI[]" id="item_IMEI" style="width:100%;">
                        </td>-->
                        <td style="width:10%;">
							<p id="del" data-stateName="" class="btn btn-danger" onclick="delRow(this)">Delete</p> 
						</td>

						<input type="hidden" name="item_id[]" id="item_id">
						
						<input type="hidden" name="item_TotalAmount[]" id="item_TotalAmount" value="0">
						<input type="hidden" name="item_InvoiceAmount[]" id="item_InvoiceAmount">
						<input type="hidden" name="item_Qty[]" id="item_Qty" value="0">
						<input type="hidden" name="item_Rate[]" id="item_Rate" value="0">
						<input type="hidden" name="item_DiscountAmount[]" id="item_DiscountAmount" value="0">
						<input type="hidden" name="item_NetAmount[]" id="item_NetAmount" value="0">
					</tr>
				</table>
			</div><!--End of wrappe div-->

			<div class="" style="margin-top:10px">
            	<div class="col-lg-12 col-md-12 col-xs-12">
                	<table style="width:100%; background:#f1f1f1;" class="table table-condensed">
                    	<tr>
                    		<th style="width: 30%;" rowspan="2">
                    			
                    			<table>
                        			<tr>
                        				<td style="width:23%;">Discount (Rupees)<br /><input type="number" name="pr_Discount" id="pr_Discount" placeholder="Enter Disc. Amount" class="form-control" onkeyup="calculate()"  onchange="calculate();" style="text-align:right;width: 90%;"  min="0" value="<?=isset($pr_Discount) ? $pr_Discount : '0'?>"></td>
                                        <td style="width:1%;">&nbsp;</td>
                                        <td style="width:21%;">VAT(%)<br /><input type="number" name="pr_Tax"  placeholder="Enter Tax %" id="pr_Tax" class="form-control" style="text-align:right;width: 90%;" min="0" onkeyup="calculate()" onchange="calculate();" value="<?=isset($pr_Tax) ? $pr_Tax : '0'?>"></td>
                                    </tr>
                                    <tr>
                                    	<td>
                                            Disc. (Amount)<br />
                                            <h1 style="    color: black; padding:5px;font-size: 20px; text-align: right;background: lightgray;"><?=$currency_symbol?><span id="pr_DiscountAmountShow"><?=isset($pr_DiscountAmount) ? $pr_DiscountAmount : '0'?></span></h1>
                                            <input type="hidden" name="pr_DiscountAmount" id="pr_DiscountAmount" class="form-control" style="text-align:right; width: 90%; font-size: 14px;" min="0" readonly="readonly" value="<?=isset($pr_DiscountAmount) ? $pr_DiscountAmount : '0'?>" >
                                        </td>
                                        <td>&nbsp;</td>
                                        <td>
                                            VAT (Amount)<br />
                                            <h1 style="    color: black;font-size: 20px; padding:5px; text-align: right;background: lightgray;"><?=$currency_symbol?><span id="pr_TaxAmountShow"><?=isset($pr_TaxAmount) ? $pr_TaxAmount : '0'?></span></h1>
                                            <input type="hidden" name="pr_TaxAmount" id="pr_TaxAmount" class="form-control" style="text-align:right; width: 90%; font-size: 14px;" min="0" readonly="readonly" value="<?=isset($pr_TaxAmount) ? $pr_TaxAmount : '0'?>">
                                        </td>
                                       </tr>
                        		</table>

                    		</th>
                        	<th style="width:15%">Purchase Return Notes</th>
                            <th style="width:15%">Vendor Notes</th>
                            <th style="width:15%; font-size: 16px; display: none;" rowspan="2">Paid To Vendor<br /><input type="hidden" name="pp_Amount" class="form-control" style="text-align:right;text-align: right;font-size: 22px;font-weight: 500;" min="0" placeholder="Enter Amount"></th>
                            <th style="width:25%; background:#09F !important;" rowspan="2">
                            	<p style="padding:0; float:left; color:#FFF;">Due Amount</p><p style="padding:0; margin:0; float:right; color:#FFF;">Total Items: <span id="totalItems"></span></p>
                                <br />
                                <h1 id="pr_NetAmountShow" style=" color:#FFF; font-size:40px; text-align:center;"><?=$currency_symbol?>0</h1>
                            	<input type="hidden" name="pr_NetAmount" id="pr_NetAmount" value="0" class="form-control" style="text-align:right" min="0" readonly="readonly">
                                <input type="hidden" name="pr_TotalAmount" id="pr_TotalAmount" value="0" class="form-control" style="text-align:right" min="0" readonly="readonly">
                                
                                <input type="hidden" name="pr_TotalItems" id="pr_TotalItems" value="0">
								<input type="hidden" name="save" value="1">
                                <input type="hidden" name="save_value" id="save_value" value="save">
								<p type="submit" class="btn btn-warning" style="font-weight: bold;padding: 5px 30px; background:orange;" id="submit" name="submit" onclick="saveForm('save');">Save </p>
                                <p type="submit" class="btn btn-warning" style="font-weight: bold;padding: 5px 30px; background:orange;" id="submit" name="submit" onclick="saveForm('save_and_close');">Save & close</p>
								 
							</th>
                        </tr>
                        <tr>
                        	<td><textarea class="form-control" name="pr_Remarks" id="pr_Remarks" style=" padding-top: 6px;width:100%; height:55px;max-width:315px; max-height:55px;" placeholder="Purchase Return Note"></textarea></td>
                            <td><textarea class="form-control" name="pr_VendorRemarks" id="pr_VendorRemarks" style=" padding-top: 6px; width:100%; height:55px; max-width:315px; max-height:55px;" placeholder="Vendor Note About Vendor"></textarea></td>
                        </tr>
                    </table>
                </div>
            </div>
		</fieldset>
	</form>

</div>
<script type="text/javascript">
function calculate_netamount_row(val)
{
    var ex_qty = $("#ex_qty").val();
    var ex_rate = $("#ex_rate").val();
    var ex_discount_amount = $("#ex_discount_amount").val();

    ex_qty = ex_qty ? parseFloat(ex_qty) : 0;
    ex_rate = ex_rate ? parseFloat(ex_rate) : 0;
    ex_discount_amount = ex_discount_amount ? parseFloat(ex_discount_amount) : 0;

    var ex_netamount = (ex_qty * ex_rate) - ex_discount_amount;
    if(ex_netamount<0) ex_netamount = 0;

    $("#ex_discount_amount").val(ex_discount_amount);
    $("#ex_netamount").val(ex_netamount);
}



function getItemDetail(val)
{
	var sup_id=$("#sup_id").val();
	var item_id=$("#ex_item").val();
	var allVars="item_id="+item_id+"&sup_id="+sup_id;
	//var allVars='item_id='+val;
	//alert(allVars);
	$.ajax
		({
			type: "post",
		 url: "purchase_add_json.php",
		 dataType: 'json',
		 data:allVars,
		 cache: false,
		 success: function(data)
		 { 
				
				$("#ex_itemcode").val(data.item_Code);
				$("#ex_qty").val('1');
				$("#ex_rate").val(data.item_PurchasePrice);
				$("#ex_netamount").val(data.item_PurchasePrice);
				$("#ex_imei").focus();
		 },
		 error:function(data)
		 {
		 	alert("Pleasee Choose An Item First");

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
	 url: "purchase_add_json.php",
	 dataType: 'json',
	 data:allVars,
	 cache: false,
	 success: function(data)
	 { 
	 	if(data['msg']=='Y')
	 	{
	 		console.log(data.query);
	 		$("#ex_qty").val('1');

			$("#ex_rate").val(data.item_PurchasePrice);
			$("#ex_netamount").val(data.item_PurchasePrice);
			$("select#ex_item").select2("val",data.item_id);
			$("#ex_qty").focus();
			// addToTable();
	 	}	
	 	else
	 	{
	 		alert(data.msg);
	 	}
	 }
	});
}


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


$("#ex_barcode").keypress(function(e){
	if(e.keyCode==13)
	{
		$("#ex_imei").focus();

	}
});

$("#ex_qty").keypress(function(e){
	if(e.keyCode==13)
	{
		addToTable();

	}
});

$("#ex_imei").keypress(function(e){
	if(e.keyCode==13)
	{
		addToTable();

	}
});

function addToTable()
{
    var item_id=$("#ex_item option:selected").val();
    if(item_id!=0)
    {
        var item_text=$("#ex_item option:selected").text();
        var ex_itemcode=$("#ex_itemcode").val();
        var ex_qty=$("#ex_qty").val();
        var ex_rate=$("#ex_rate").val();
        var ex_discount_amount=$("#ex_discount_amount").val() ? parseFloat($("#ex_discount_amount").val()) : 0;
        var ex_netamount=$("#ex_netamount").val();
        var ex_imei=$("#ex_imei").val(); // can be blank for purchase return

        if(ex_qty==0 || ex_qty=='')
        {
            alert('Please give Item Qty');
            $("#ex_qty").focus();
            return false;
        }
        if(ex_rate==0 || ex_rate=='')
        {
            alert('Please give Item Rate');
            $("#ex_rate").focus();
            return false;
        }
        if(ex_netamount=='' || ex_netamount==0)
        {
            alert('Invalid Selection Please Select An Item First');
            $("#ex_netamount").focus();
            return false;
        }
        else
        {
            if(checkDuplicate())
            {
                // If IMEI is not used, skip server validation and add directly
                if (!ex_imei) {
                    var newRow=$("#u_row").clone().show();
                    $(newRow).find("#show_itemcode").text(ex_itemcode);
                    $(newRow).find("#show_item").text(item_text);
                    $(newRow).find("#show_qty").text(ex_qty);
                    $(newRow).find("#show_rate").text(ex_rate);
                    $(newRow).find("#show_discount_amount").text(ex_discount_amount);
                    $(newRow).find("#show_netamount").text(ex_netamount);

                    $(newRow).find("#item_id").val(item_id);
                    // $(newRow).find("#item_IMEI").val(ex_imei); // not present / not used
                    $(newRow).find("#item_TotalAmount").val(ex_netamount);
                    $(newRow).find("#item_InvoiceAmount").val(ex_netamount);
                    $(newRow).find("#item_Qty").val(ex_qty);
                    $(newRow).find("#item_Rate").val(ex_rate);
                    $(newRow).find("#item_DiscountAmount").val(ex_discount_amount);
                    $(newRow).find("#item_NetAmount").val(ex_netamount);
                    $("#u_tbl").prepend(newRow);

                    // clear form row
                    $("#ex_itemcode").val("");
                    $("#ex_item").val("0").trigger('change');
                    $("#ex_qty").val("");
                    $("#ex_rate").val("");
                    $("#ex_discount_amount").val("");
                    $("#ex_netamount").val("");
                    $("#ex_imei").val("");

                    calculate();
                    totalItems();
                    $("#ex_itemcode").focus();
                    return;
                }

                // If IMEI exists and you still want to validate on server
                var allVars='ex_imei='+encodeURIComponent(ex_imei);
                $.ajax
                ({
                    type: "post",
                    url: "purchase_add_json.php",
                    dataType: 'json',
                    data:allVars,
                    cache: false,
                    success: function(data)
                    { 
                        if(data.status=='Y')
                        {
                            var newRow=$("#u_row").clone().show();
                            $(newRow).find("#show_itemcode").text(ex_itemcode);
                            $(newRow).find("#show_item").text(item_text);
                            $(newRow).find("#show_qty").text(ex_qty);
                            $(newRow).find("#show_rate").text(ex_rate);
                            $(newRow).find("#show_discount_amount").text(ex_discount_amount);
                            $(newRow).find("#show_netamount").text(ex_netamount);

                            $(newRow).find("#item_id").val(item_id);
                            // $(newRow).find("#item_IMEI").val(ex_imei);
                            $(newRow).find("#item_TotalAmount").val(ex_netamount);
                            $(newRow).find("#item_InvoiceAmount").val(ex_netamount);
                            $(newRow).find("#item_Qty").val(ex_qty);
                            $(newRow).find("#item_Rate").val(ex_rate);
                            $(newRow).find("#item_DiscountAmount").val(ex_discount_amount);
                            $(newRow).find("#item_NetAmount").val(ex_netamount);
                            $("#u_tbl").prepend(newRow);
                        }
                        else if(data.status=='N')
                        {
                            alert(data.msg);
                        }
                        $("#ex_imei").val("");
                        $("#ex_imei").focus();
                        calculate();
                        totalItems();

                        $("#ex_itemcode").val("");
                        $("#ex_item").val("0").trigger('change');
                        $("#ex_qty").val("");
                        $("#ex_rate").val("");
                        $("#ex_costprice").val("");
                        $("#ex_discount_amount").val("");
                        $("#ex_netamount").val("");
                        $("#ex_itemcode").focus();
                    },
                    error:function(data)
                    {
                        alert("Server validation error. Row not added.");
                    }
                });
            }
        }
    }
    else
    {
        alert('Select an item please');
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
	$("#pr_TotalItems").val(totalItems-1);
}
function calculate()
{
    var item_NetAmount = document.getElementsByName("item_InvoiceAmount[]");
    var sum=price=taxamount=discountamount=0;
    $(item_NetAmount).each(function(index,elem){
            var price = parseFloat(elem.value);
            if(!isNaN(price))
            {
                sum = sum+price;
            }
    });
    sum=sum.toFixed(2);
    $("#pr_TotalAmount").val(sum);
    var item_NetAmount2 = document.getElementsByName("item_NetAmount[]");
    var sum_netamount=0;
    $(item_NetAmount2).each(function(index,elem){
            var price2 = parseFloat(elem.value);
            if(!isNaN(price2))
            {
                sum_netamount = sum_netamount+price2;
            }
    });

    var net_sum=sum_netamount.toFixed(2);

    // --- Invoice Discount (Amount, not %) ---
    var p_Discount=$("#pr_Discount").val();
    if(p_Discount!='' && p_Discount!==0)
    {
        discountamount=parseFloat(p_Discount);
        net_sum=parseFloat(net_sum) - discountamount;
    }
    
    var p_Tax=$("#pr_Tax").val();
    if(p_Tax!='' && p_Tax!==0)
    {
        taxamount=p_Tax/100*net_sum;
        net_sum=parseFloat(net_sum) +parseFloat(taxamount);
    }
    
    discountamount=parseFloat(discountamount).toFixed(2);
    taxamount=parseFloat(taxamount).toFixed(2);
    net_sum=parseFloat(net_sum).toFixed(2);

    $("#pr_NetAmount").val(net_sum);
    $("#pr_NetAmountShow").html(net_sum);

    $("#pr_DiscountAmount").val(discountamount);
    $("#pr_DiscountAmountShow").html(discountamount);
    
    $("#pr_TaxAmount").val(taxamount);
    $("#pr_TaxAmountShow").html(taxamount);
}

function checkDuplicate()
{
	var error=0;
	var ex_imei=$("#ex_imei").val();
	if(ex_imei){ // only when IMEI is actually provided
		$("#u_tbl tr #item_IMEI").each(function(index,elem){
			if(ex_imei==elem.value)
			{
				alert("Duplicate Entry For this IMEI..");
				error++;
				$("#ex_imei").val('');
			}
		});
	}
	if(error){
		return false;
	}
	else{
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
	var pr_Remarks = $.trim($("#pr_Remarks").val());
	var sup_id = $.trim($("#sup_id").val());
	var pr_TotalItems = $.trim($("#pr_TotalItems").val());
	
	if (sup_id == 0)
	{
		$.smallBox({
		title : "Error",
		content : "<i class='fa fa-clock-o'></i> <i>Please Give Supplier Information.</i>",
		color : "#C46A69",
		iconSmall : "fa fa-times fa-2x fadeInRight animated",
		timeout : 4000
		});
	$("#client_Name").focus();
	return false;
	}
	
	if (pr_TotalItems == 0)
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
}
</script>