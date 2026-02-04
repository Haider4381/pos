<?php
include('sessionCheck.php');
include('connection.php');
include('functions.php');
require_once ("inc/init.php");
require_once ("inc/config.ui.php");
$page_title = "Repair";
include ("inc/header.php");
//$page_nav["Repair"]["active"] = true;
include ("inc/nav.php");

$base_file_full=basename(__FILE__);
$base_file_name_no_extension=explode(".",$base_file_full);
$base_file=$base_file_name_no_extension[0];

$u_id = $_SESSION['u_id'];
$branch_id = $_SESSION['branch_id'];

if(isset($_GET['id']))
{
	$id=(int)mysqli_real_escape_string($con,$_GET['id']);
	$Q="SELECT * FROM rep_repairs WHERE rep_id='".$id."' and branch_id=$branch_id";
	
	$Qry=mysqli_query($con,$Q);
	$Rows=mysqli_num_rows($Qry);
	if($Rows!=1) { header('Location: '); }
	$Result=mysqli_fetch_object($Qry);


	$client_id=$Result->client_id;
	$item_id=$Result->item_id;
	$item_Code=$Result->item_Code;
	$rep_Notes=$Result->rep_Notes;
	$rstatus_id=$Result->rstatus_id;
	$rtype_id=$Result->rtype_id;
	$rep_ItemCondition=$Result->rep_ItemCondition;
	$client_id=$Result->client_id;
	$rep_AmountCheck=$Result->rep_AmountCheck;
	$rep_AmountRepair=$Result->rep_AmountRepair;
	$rep_AmountPaid=$Result->rep_AmountPaid;
	$rep_AmountBalance=$Result->rep_AmountBalance;
	$rep_Date=$Result->rep_Date;
	$rep_Closed=$Result->rep_Closed;
	$rep_DateDelivery=$Result->rep_DateDelivery;
}
$rep_NumberPrefix='REP';
$rep_NumberQ=mysqli_fetch_assoc(mysqli_query($con, "select (ifnull(MAX(rep_NumberSr),0)+1) as rep_NumberSr from rep_repairs where branch_id=$branch_id"));
$rep_NumberSr=$rep_NumberQ['rep_NumberSr'];
$rep_Number=$rep_NumberPrefix.$rep_NumberQ['rep_NumberSr'];

?>
<!-- ==========================CONTENT STARTS HERE ========================== -->
<!-- MAIN PANEL -->
<div id="main" role="main">

	
<?php $breadcrumbs["New"] = "";
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
				<span class="small_icon"><i class="fa fa-recycle"></i>	</span>	
				<h2>Repair</h2>
			</header>

			<!-- widget div-->
			<div>		

 
<!-- widget content -->
<div class="widget-body no-padding">
		<div class="tab-content" >
<?php
if(isset($_POST['submit_new_item']))
{
	$item_Name=validate_input($_POST['item_Name']);
	$item_Code=validate_input($_POST['item_Code']);
	$icat_id=validate_input($_POST['icat_id']);
	$isubcat_id=validate_input($_POST['isubcat_id']);
	$item_Status='A';
	$item_Remarks=validate_input($_POST['item_Remarks']);
	$item_SalePrice=validate_input($_POST['item_SalePrice']);
	$item_PurchasePrice=test_input($_POST['item_PurchasePrice']);
	$item_MinQty=validate_input($_POST['item_MinQty']);
	$item_MaxQty=validate_input($_POST['item_MaxQty']);
	
	$q="INSERT INTO adm_item (item_Name,		item_Code,	item_Status,	item_Remarks,	item_PurchasePrice,		item_SalePrice, 	item_MinQty,	item_MaxQty,	icat_id,	isubcat_id, u_id, branch_id)
						VALUES('$item_Name',	'$item_Code','$item_Status','$item_Remarks','$item_PurchasePrice',	'$item_SalePrice',	'$item_MinQty',	'$item_MaxQty',		'$icat_id',	'$isubcat_id', '$u_id', '$branch_id')";

	if(mysqli_query($con,$q))
	{
		$_SESSION['msg']="Product Created successfully";
	}
	else
	{
		$_SESSION['msg']="Problem creating Product";
	}
	echo '<script> window.location="";</script>';
 	die();
}

if(isset($_POST['submit_new_customer']))
{
	$client_Name=validate_input($_POST['client_Name']);
	$client_Email=validate_input($_POST['client_Email']);
	$client_Phone=validate_input($_POST['client_Phone']);
	$client_Address=validate_input($_POST['client_Address']);
	$client_Status=validate_input($_POST['client_Status']);
	$client_Remarks=validate_input($_POST['client_Remarks']);
	
	$q="INSERT INTO adm_client (client_Name,client_Email,client_Phone,client_Address,client_Status,client_Remarks, u_id, branch_id) VALUES('$client_Name','$client_Email','$client_Phone','$client_Address','$client_Status','$client_Remarks', '$u_id', '$branch_id')";
	//die();
	if(mysqli_query($con,$q))
	{
		$client_id=mysqli_insert_id($con);
		$_SESSION['msg']='<div class="alert alert-success">New Customer Saved Successfully</div>';
	}	
	else
	{
		$_SESSION['msg']='<div class="alert alert-danger">Problem Creating New Customer</div>';
	}
	echo '<script> window.location=""; </script>';
	die();
}


if(isset($_POST['post_form']))
{
     
	$id=$_POST['id'];
	$client_id=validate_input($_POST['client_id']);
	$item_id=validate_input($_POST['item_id']);
	$item_Code=validate_input($_POST['item_Code']);
	$rep_Notes=validate_input($_POST['rep_Notes']); 
	$rstatus_id=validate_input($_POST['rstatus_id']);
	$rtype_id=validate_input($_POST['rtype_id']);
	isset($_POST['rep_Closed']) ? $rep_Closed=1 : $rep_Closed=0;
	$rep_AmountCheck=validate_input($_POST['rep_AmountCheck'])+0;
	$rep_AmountRepair=test_input($_POST['rep_AmountRepair'])+0;
	$rep_AmountBalance=validate_input($_POST['rep_AmountBalance'])+0;
	$rep_Date=date('Y-m-d',strtotime($_POST['rep_Date']));
	$rep_DateDelivery=date('Y-m-d',strtotime($_POST['rep_DateDelivery']));
	
	if (empty($client_id))
	{
		echo '<div class="col-lg-12" style="padding-top:20px;"><div class="alert alert-danger"><strong>Alert!</strong> All fields must be filled...</div></div>';
	}
	else
	{
		if(empty($id))
		{			
			$inserted=mysqli_query($con,"INSERT INTO `rep_repairs`(rep_Number, rep_NumberSr, `client_id`, `item_id`, `item_Code`, `rep_Notes`, `rstatus_id`, `rtype_id`, `rep_ItemCondition`, `rep_AmountCheck`, `rep_AmountRepair`, `rep_AmountPaid`, `u_id`, `branch_id`, `rep_CreatedAt`, `rep_AmountBalance`,rep_Date,rep_DateDelivery,rep_Closed)
														VALUES   ('$rep_Number', '$rep_NumberSr','$client_id', '$item_id', '$item_Code', '$rep_Notes', '$rstatus_id', '$rtype_id', '', '$rep_AmountCheck', '$rep_AmountRepair', '0', '$u_id', '$branch_id', '$current_datetime_sql', '$rep_AmountBalance','$rep_Date','$rep_DateDelivery','$rep_Closed')");

			if($inserted)
			{
				$r_id=mysqli_insert_id($con);
				$_SESSION['msg']="Repair Created successfully";
				?>
					<script type="text/javascript">
					window.open('repair_print?r_id=<?php echo $r_id;?>','popUpWindow','height=488,width=1022,left=120,top=200,resizable=yes,scrollbars=yes,toolbar=no,menubar=no,location=no,directories=no, status=no');
					window.location="";
				</script>
				<?php
				die();
			}
			else
			{
				$_SESSION['msg']="Problem creating Repair";
				echo '<script type="text/javascript">window.location="";</script>';
				die();
			}
		}
		else
		{
			$inserted = mysqli_query($con,"
					UPDATE `rep_repairs` SET
					
					`client_id` = '".$client_id."',
					`item_id` = '".$item_id."',
					`item_Code` = '".$item_Code."',
					`rep_Notes` = '".$rep_Notes."',
					`rstatus_id` = '".$rstatus_id."',
					`rtype_id` = '".$rtype_id."',
					`rep_Closed` = '".$rep_Closed."',
					`rep_AmountCheck` = '".$rep_AmountCheck."',
					`rep_AmountRepair` = '".$rep_AmountRepair."',
					`rep_AmountBalance` = '".$rep_AmountBalance."',
					`rep_Date` = '".$rep_Date."',
					`rep_DateDelivery` = '".$rep_DateDelivery."'
					
					WHERE rep_id = '".$id."'");


			if($inserted)
			{

				$_SESSION['msg']="Repair Updated successfully";
				echo '<script type="text/javascript">window.location="";</script>';
				die();
			}
			else
			{
				$_SESSION['msg']="Problem Updating Repair";
				echo '<script type="text/javascript">window.location="";</script>';
				die();
			}
		}
	}
}
?>





<?php if(!empty($_SESSION['msg'])){?> <div class="alert alert-info"><?php echo $_SESSION['msg'];?> </div> <?php unset($_SESSION['msg']);} ?>
		

			<form id="checkout-form" class="smart-form" method="POST">	
			<fieldset>
            <input type="hidden" name="id" id="id" value="<?=isset($id) ? $id : '0'?>" />
	<div class="row" style="margin-bottom: 5px;">
     	<table style="width:70%; background:#f1f1f1; float:left;" class="table table-condensed">
        	<tr>
            	<th style="width:45%; line-height: 28px;">Customer Name <button type="button" onclick="NewCustomerModal()" style="float:right;" class="btn btn-xs btn-success"><strong><i class="fa fa-child" style="font-size: 14px;"></i> Add New Customer</strong></button></th>
                <th style="width:2%;"></th>
                <th style="width:20%; line-height: 28px">Repair Status</th>
                <th style="width:20%; line-height: 28px;">Repair Type</th>
                <th style="width:13%; line-height: 28px;">Repair Closed</th>
            </tr>
            <tr>
            	<td>
                	<select class="select2" name="client_id" id="client_id">
                          <option value="0">Search Customer</option>
                          <?php
                          $client_query = "SELECT client_id,client_Name,client_Phone FROM adm_client WHERE client_Status='A' AND branch_id=$branch_id";
                          $client_run = mysqli_query($con, $client_query);
                          while ($clientRow = mysqli_fetch_assoc($client_run))
                          { ?>
                          <option value="<?php echo $clientRow['client_id'] ?>" <?= isset($client_id) && $client_id==$clientRow['client_id'] ? 'selected' : 's' ?> ><?php echo $clientRow['client_Name'].' / '.$clientRow['client_Phone'] ?></option>
                          <?php } ?>
                      
                       </select>
                </td>
                <td></td>
                <td>
                		<select class="select2" name="rstatus_id" id="rstatus_id">
                          <?php
                          $status_query = "SELECT * FROM rep_status WHERE 1";
                          $status_run = mysqli_query($con, $status_query);
                          while ($statusRow = mysqli_fetch_assoc($status_run))
                          { ?>
                          <option value="<?php echo $statusRow['rstatus_id'] ?>" <?= isset($rstatus_id) && $rstatus_id==$statusRow['rstatus_id'] ? 'selected' : '' ?>  ><?php echo $statusRow['rstatus_Name'];?></option>
                          <?php } ?>
                      
                       </select>
                </td>
                <td>
                		<select class="select2" name="rtype_id" id="rtype_id">
                          <?php
                          $rtype_query = "SELECT rtype_id,rtype_Name FROM rep_type WHERE 1";
                          $rtype_run = mysqli_query($con, $rtype_query);
                          while ($rtypeRow = mysqli_fetch_assoc($rtype_run))
                          { ?>
                          <option value="<?php echo $rtypeRow['rtype_id'] ?>" <?= isset($rtype_id) && $rtype_id==$rtypeRow['rtype_id'] ? 'selected' : '' ?> ><?php echo $rtypeRow['rtype_Name'];?></option>
                          <?php } ?>
                      
                       </select>
                </td>
                <td style="text-align:center; line-height: 34px;"><input type="checkbox" name="rep_Closed" <?= isset($rep_Closed) && $rep_Closed==1 ? 'checked' : '' ?>  style="height: 15px; width: 15px;" /></td>
            </tr>
            <tr>
            	<th style="line-height: 28px;">Product Name <!--<button type="button" onclick="NewItemModal()" style="float:right;" class="btn btn-xs btn-success"><strong>+ Add New</strong></button>--></th>
                <th></th>
                <th colspan="3" style="line-height: 28px;">Repair Note</th>
            </tr>
            <tr>
            	<th>
                        <input type="text" class="form-control" name="item_id" placeholder="Enter Product Name" value="<?= isset($item_id) ? $item_id : '' ?>" style="width: 99%;" />
                 </th>
                <th></th>
                <td colspan="3" rowspan="3" ><textarea name="rep_Notes" class="form-control" id="rep_Notes" style=" height: 113px; padding-top: 4px; width:99%; font-size:13px; margin-bottom: 22px;" placeholder="Reparing Note About Product"><?= isset($rep_Notes) ? $rep_Notes : '' ?></textarea></td>
            </tr>
            <tr>
            	<th style="line-height: 28px;">Product IMEI No.</th>
                <th></th>
                
            </tr>
            <tr>
            	<td><input type="text" class="form-control" name="item_Code" id="item_Code" placeholder="Enter IMEI Number" value="<?= isset($item_Code) ? $item_Code : '' ?>" style="width: 98%; margin-bottom: 17px;"></td>
                <td></td>
            </tr>
        </table>
        <table style="width:28%; background:#f1f1f1; float:right;" class="table table-condensed">
        	<tr>
            	<th style="width:35%; line-height: 34px;" >Repair Bill No.</th>
                <td style="width:65%;"><input type="text" name="rep_Number"  value="<?= isset($rep_Number) ? $rep_Number : ''; ?>" class="form-control" readonly="readonly" style="font-size: 21px; border: none; color:#d65252;"></th>
            </tr>
            <tr>
            	<th style="width:35%; line-height: 34px;" >Today Date</th>
                <td style="width:65%;"><input type="text" name="rep_Date"  value="<?= isset($rep_Date) ? date("d-m-Y",strtotime($rep_Date)) : date('d-m-Y'); ?>" placeholder="Select a date" class="form-control datepicker" data-dateformat="dd-mm-yy"></th>
            </tr>
            <tr>
            	<th style="width:35%; line-height: 34px;">Delivery Date</th>
                <td style="width:65%;"><input type="text" name="rep_DateDelivery"  value="<?= isset($rep_DateDelivery) ? date("d-m-Y",strtotime($rep_DateDelivery)) : date('d-m-Y'); ?>" placeholder="Select a date" class="form-control datepicker" data-dateformat="dd-mm-yy"></th>
            </tr>
            <tr>
            	<th style="line-height: 34px;">Diagnosis Fee</th>
                <td style=""><input type="number" class="form-control" name="rep_AmountCheck" id="rep_AmountCheck" onkeyup="calculate_netamount()" Placeholder="Enter Diagnosis Fee"  value="<?= isset($rep_AmountCheck) ? $rep_AmountCheck : '' ?>" style="text-align:right; font-size: 21px;" ></th>
            </tr>
            <tr>
            	<th style="line-height: 34px;">Repair Fee</th>
                <td><input type="number" class="form-control" name="rep_AmountRepair" id="rep_AmountRepair"   onkeyup="calculate_netamount()" Placeholder="Enter Repair Fee" value="<?= isset($rep_AmountRepair) ? $rep_AmountRepair : '' ?>" style="text-align:right; font-size: 21px;"></th>
            </tr>
            <tr style="background-color:#09F; color: #fff;">
            	<th style="line-height: 40px;text-align: right;">Due Amount</th>
                <td style="text-align:center;font-size: 28px;">
                	<p><?=$currency_symbol;?><span id="ex_netamountshow"><?= isset($rep_AmountBalance) ? $rep_AmountBalance : '0' ?></span></p>
                	<input type="hidden" class="form-control" name="rep_AmountBalance" id="ex_netamount" readonly value="<?= isset($rep_AmountBalance) ? $rep_AmountBalance : '0' ?>"  style="text-align:right;">
                </th>
            </tr>
		</table>
        
        
 
</div>
				</fieldset>
				<footer>
                	<input type="hidden" name="post_form" />
					<button type="submit" class="btn btn-primary" name="submit" style="background-color:orange">Save</button>
				</footer>
			</form>

				</div>
				<!-- end widget content -->
		</div><!--End of tab-content-->
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

<div class="modal fade" id="NewItemModal"  role="dialog">
  <div class="modal-dialog  modal-lg">
    <div class="modal-content">
      <div class="modal-header alert alert-success">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title"><strong>Add New Item</strong></h4>
      </div>
      <div class="modal-body ">
        <div id="msg2"></div>
		<form id="checkout-form" class="smart-form" novalidate="novalidate" method="post" action=""  onsubmit="return checkParameters_NewItem();">	
          <div class="row" style="margin-bottom: 5px;">
     	<div class="col col-md-11">       
            <div class="row" style="margin-bottom: 5px;">
                <div class="col col-md-2"><label>Item name:</label></div>
                <div class="col col-md-4"><input type="text" name="item_Name" id="item_Name" class="form-control"></div>
				<div class="col col-md-2"><label>SKU/UPC:</label></div>
            	<div class="col col-md-4"><input type="text" name="item_Code" class="form-control"></div>
			</div>
		<!--2nd row start here-->
		<div class="row" style="margin-bottom: 5px;">
			<div class="col col-md-2"><label>Main Category:</label></div>
			<div class="col col-md-4">
				<select class="select2" name="icat_id">
					<?php $catArray=get_Categories();
                     foreach ($catArray as $key => $catRow) { ?>
                                            <option value="<?php echo $catRow['icat_id'];?>"><?php echo $catRow['icat_name']; ?></option>                                            
                    <?php } 
                    ?>
				</select>
			</div>
            
            <div class="col col-md-2"><label>Category:</label></div>
			<div class="col col-md-4">
				<select class="select2" name="isubcat_id">
					<?php $subcatArray=get_SubCategories();
                     foreach ($subcatArray as $key => $subcatRow) { ?>
                                            <option value="<?php echo $subcatRow['isubcat_id'];?>"><?php echo $subcatRow['isubcat_name']; ?></option>                                            
                    <?php } 
                    ?>
				</select>
			</div>
		</div>
        
        
		<div class="row" style="margin-bottom: 5px;">
			<div class="col col-md-2"><label>Cost Price:</label></div>
			<div class="col col-md-4"><input type="number" class="form-control" name="item_PurchasePrice" value="0"></div>
			<div class="col col-md-2"><label>Sale Price:</label></div>
			<div class="col col-lg-4"><input type="number" class="form-control" name="item_SalePrice" value="0"></div>
		</div>
        
        
        <div class="row" style="margin-bottom: 5px;">
			<div class="col col-md-2"><label>Min Qty:</label></div>
			<div class="col col-md-4"><input type="number" class="form-control" name="item_MinQty" value="0"></div>
			<div class="col col-md-2"><label>Max Qty:</label></div>
			<div class="col col-lg-4"><input type="number" class="form-control" name="item_MaxQty" value="0"></div>
		</div> 
  
 
		<div class="row" style="margin-bottom: 5px;">
			<div class="col col-md-2"><label>Notes:</label></div>
			<div class="col col-md-10"><textarea name="item_Remarks" class="form-control" style="max-width:613px; max-height:55px;"></textarea> </div>
		</div>	
        </div>
        
        </div>
				</fieldset>
			    
          <div class="form-actions">
            <div class="row">
              <div class="col-md-12">
                <button type="submit" class="btn btn-primary btn-sm" name="submit_new_item">Save</button>
				<button type="button" data-dismiss="modal" class="btn btn-danger btn-sm">Cancel</button>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
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
				<div class="col col-lg-2"><label>Full Name:</label></div>
				<div class="col col-lg-3"><input type="text" name="client_Name" id="new_client_Name"  required="required" class="form-control"></div>
				<div class="col col-lg-2"><label>Email Address:</label></div>
				<div class="col col-lg-3"><input type="text" name="client_Email"  class="form-control"></div>
			</div>
            
            
        <div class="row" style="margin-bottom: 5px;">
			<div class="col col-lg-2">
 					<label>Phone Number:</label>
 			</div>
			<div class="col col-lg-3">
 					<input type="text" name="client_Phone"  class="form-control">
 			</div>
			<div class="col col-lg-2">
 					<label>Customer Status:</label>
 			</div>
			<div class="col col-lg-3">
 					<select name="client_Status" class="form-control"> 
						<option value="A">Active</option>
						<option value="I">In-Active</option>
					</select>
 			</div>
		</div>
        
        
        <!--3rd row start here-->

		<div class="row" style="margin-bottom: 5px;">
			<div class="col col-lg-2">
 					<label>Address:</label>
 			</div>
			<div class="col col-lg-8">
 					<textarea name="client_Address"  class="form-control"></textarea> 
 			</div>
			<div class="col col-lg-3">
			
			</div>
		</div>    
        
        <div class="row" style="margin-bottom: 5px;">
				<div class="col col-lg-2">
 						<label>Notes:</label>
 				</div>
				<div class="col col-lg-8">
 					<textarea name="client_Remarks" class="form-control"></textarea> 
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






<?php include ("inc/footer.php");?>
<!-- END PAGE FOOTER -->


<?php include ("inc/scripts.php");?>

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

function NewItemModal()
{
  //document.getElementById("current_row").value=val;
  $("#NewItemModal").modal("show");
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

function calculate_netamount(val)
{
	var rep_AmountCheck=$("#rep_AmountCheck").val();
	var rep_AmountRepair=$("#rep_AmountRepair").val();
	ex_netamount=parseFloat(rep_AmountCheck) + parseFloat(rep_AmountRepair);
	$("#ex_netamount").val(ex_netamount);
	$("#ex_netamountshow").html(ex_netamount);
}

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

function checkParameters_NewItem(){
	var new_item_Name = $.trim($("#item_Name").val());

	if (new_item_Name == '')
	{
		$.smallBox({
		title : "Error",
		content : "<i class='fa fa-clock-o'></i> <i>Please Fill Item Name.</i>",
		color : "#C46A69",
		iconSmall : "fa fa-times fa-2x fadeInRight animated",
		timeout : 4000
		});
	$("#item_Name").focus();
	return false;
	}
}


function del(val){
    
       $.SmartMessageBox({
        title : "Attention required!",
        content : "This is a confirmation box. Do you want to delete the Record?",
        buttons : '[No][Yes]'
       }, function(ButtonPressed) {
        if (ButtonPressed === "Yes") {
            $.post("ajax/delAjax.php",
            {
                //delete_builty:'Yes',
                rep_id : val,
            },
            function(data,status){ 
                if(data.trim()!="")
                {
                    //$('#row'+val).remove();
					document.getElementById('row'+val).style.display= "none";
                
                    $.smallBox({
                    title : "Delete Status",
                    content : "<i class='fa fa-clock-o'></i> <i>Record Deleted successfully...</i>",
                    color : "#659265",
                    iconSmall : "fa fa-check fa-2x fadeInRight animated",
                    timeout : 4000
                    });
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