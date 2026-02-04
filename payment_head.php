<?php
include('sessionCheck.php');
include('connection.php');
include('functions.php');
require_once ("inc/init.php");
require_once ("inc/config.ui.php");
$page_title = "Payee";
include ("inc/header.php");
//$page_nav["Settings"]["sub"]["Brands"]["active"] = true;
include ("inc/nav.php");
$branch_id=$_SESSION['branch_id'];
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
    padding-left: 10px;
    margin-top:5px;
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
				<span class="small_icon"><i class="fa fa-slack"></i>	</span>	
				<h2>Payee</h2>
			</header>

			<!-- widget div-->
			<div>		


				<!-- widget content -->
				<div class="widget-body no-padding">
	<br>
		<div class="tab-content" >

<?php
if(isset($_POST['submit']))
{
	$payee_name=validate_input($_POST['payee_name']);
	$payee_email=validate_input($_POST['payee_email']);
	$payee_cell=validate_input($_POST['payee_cell']);
	$payee_address=validate_input($_POST['payee_address']);
	$payee_notes=validate_input($_POST['payee_notes']); 
	
	
	$fcQuery = "INSERT INTO `adm_payee`(`payee_id`, `payee_name`, `payee_email`, `payee_cell`, `payee_address`, `payee_notes`, `payee_createdat`, `payee_enterby`, `branch_id`) 
						VALUES 		('', '$payee_name', '$payee_email', '$payee_cell', '$payee_address', '$payee_notes', now(), '$u_id', '$branch_id')";
	if(mysqli_query($con,$fcQuery))
	{
		$_SESSION['msg']= "<div class='alert alert-info'>Payee Created Successfully</div>";
	?>
	<script type="text/javascript">window.location="";</script>
	<?php
	die();
	}
	else
	{
		$msg ="<div class='alert alert-info'>Problem creating the Payee</div>";
	}
}

if(isset($_POST['update']))
{
	$payee_id=validate_input($_POST['payee_id']);
	$payee_name=validate_input($_POST['payee_name']);
	$payee_email=validate_input($_POST['payee_email']);
	$payee_cell=validate_input($_POST['payee_cell']);
	$payee_address=validate_input($_POST['payee_address']);
	$payee_notes=validate_input($_POST['payee_notes']); 
	
	$fcQuery = "UPDATE adm_payee SET 
	
	payee_name='$payee_name',
	payee_email='$payee_email',
	payee_cell='$payee_cell',
	payee_address='$payee_address',
	payee_notes='$payee_notes'
	
	WHERE payee_id=$payee_id";
	if(mysqli_query($con,$fcQuery))
	{
		$_SESSION['msg']= "<div class='alert alert-info'>Payee updated successfully</div>";
	?>
	<script type="text/javascript"> window.location="payment_head.php";</script>
	<?php
	die();
	}
	else
	{
		$msg ="<div class='alert alert-info'>Problem updating the Payee</div>";
	}
} 
?>


<?php if(isset($_SESSION['msg'])){ echo $_SESSION['msg'];unset($_SESSION['msg']);} ?>

<?php if(!empty($msg)){ echo $msg;} ?>
			
<?php
if(!isset($_GET['id']))
{
?>
				<form id="checkout-form" class="smart-form" novalidate="novalidate" method="post" action="">	
					<fieldset>
					<div class="row">
						<div class="col col-lg-1 col-xs-12">
							<label>Name</label>
						</div>
						<div class="col col-lg-3 col-xs-12">
							<input type="text" name="payee_name" id="payee_name" class="form-control" placeholder="Enter Payee Name">
						</div>
                        
                        <div class="col col-lg-1 col-xs-12">
							<label>Email Address</label>
						</div>
						<div class="col col-lg-3 col-xs-12">
                        
							<input type="text" name="payee_email" id="payee_email" class="form-control" placeholder="Enter Payee Email">
						</div>
					</div><!--End of row-->
                    
                    <div class="row" style="margin-top:5px;">
						<div class="col col-lg-1 col-xs-12">
							<label>Cell Number</label>
						</div>
						<div class="col col-lg-3 col-xs-12">
                        
							<input type="text" name="payee_cell" id="payee_cell" class="form-control" Placeholder="Enter Payee Cell Phone">
						</div>
                        <div class="col col-lg-1 col-xs-12">
							<label> Address</label>
						</div>
						<div class="col col-lg-3 col-xs-12">
							<input type="text" name="payee_address" id="payee_address" class="form-control" Placeholder="Enter Payee Address">
						</div>
					
					</div><!--End of row-->
                    
                    <div class="row" style="margin-top:5px;">
						<div class="col col-lg-1 col-xs-12">
							<label>Notes</label>
						</div>
						<div class="col col-lg-7 col-xs-12">
                        
							<input type="text" name="payee_notes" id="payee_notes" class="form-control" placeholder="Enter Remarks About Payee">
						</div>
					</div><!--End of row-->
					</fieldset>
					<footer  style="margin-top: 94px;">
						<input type="submit" class="btn btn-primary" name="submit" id="submit" value="Save">
					</footer>
				</form>
			
<?php
}
else
{
	$payee_id= (int) $_GET['id'];
	$payeeQ= "SELECT * FROM adm_payee WHERE payee_id=$payee_id and branch_id=$branch_id";
	$payeeRes=mysqli_query($con,$payeeQ);
	if(mysqli_num_rows($payeeRes)!=1)
	{
		echo "Invalid";
		die();
	}
	else
	{
		$payeeRow = mysqli_fetch_assoc($payeeRes);
		
?>
				<form id="checkout-form" class="smart-form" novalidate="novalidate" method="post" action="">	
					<fieldset>
					<div class="row">
						<div class="col col-lg-1 col-xs-12">
							<label>Name</label>
						</div>
						<div class="col col-lg-3 col-xs-12">
							<input type="text" name="payee_name" id="payee_name" class="form-control" value="<?php echo $payeeRow['payee_name'];?>">
						</div>
                        
                        <div class="col col-lg-1 col-xs-12">
							<label>Email Address</label>
						</div>
						<div class="col col-lg-3 col-xs-12">
                        
							<input type="text" name="payee_email" id="payee_email" class="form-control" value="<?php echo $payeeRow['payee_email'];?>">
						</div>
					</div><!--End of row-->
                    
                    <div class="row" style="margin-top:5px;">
						<div class="col col-lg-1 col-xs-12">
							<label>Cell Number</label>
						</div>
						<div class="col col-lg-3 col-xs-12">
                        
							<input type="text" name="payee_cell" id="payee_cell" class="form-control" value="<?php echo $payeeRow['payee_cell'];?>">
						</div>
                        <div class="col col-lg-1 col-xs-12">
							<label> Address</label>
						</div>
						<div class="col col-lg-3 col-xs-12">
							<input type="text" name="payee_address" id="payee_address" class="form-control" value="<?php echo $payeeRow['payee_address'];?>">
						</div>
					
					</div><!--End of row-->
                    
                    <div class="row" style="margin-top:5px;">
						<div class="col col-lg-1 col-xs-12">
							<label>Notes</label>
						</div>
						<div class="col col-lg-7 col-xs-12">
                        
							<input type="text" name="payee_notes" id="payee_notes" class="form-control" value="<?php echo $payeeRow['payee_notes'];?>">
						</div>
					</div><!--End of row-->
                    
					</fieldset>
					<footer  style="margin-top: 94px;">
						<input type="hidden" name="payee_id" value="<?php echo $payeeRow['payee_id'];?>">
						<input type="submit" class="btn btn-primary" name="update" id="submit" value="Save">
					</footer>
				</form>
				
<?php }
} 
?>

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


<?php include ("inc/scripts.php");
 
?>

<!-- PAGE RELATED PLUGIN(S) -->
<script src="
<?php echo ASSETS_URL;?>/js/plugin/datatables/jquery.dataTables.min.js"></script>
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

</script>
<script type="text/javascript">
 /*
 * SmartAlerts
 */
 // With Callback
 function del(val){

 $.SmartMessageBox({
 title : "Attention required!",
 content : "This is a confirmation box. Do you want to delete the Record?",
 buttons : '[No][Yes]'
 }, function(ButtonPressed) {
 if (ButtonPressed === "Yes") {


		 $.post("delAjax.php",
 {
 brand_id : val, 
 },
 function(data,status){ 
 if(data.trim()!="")
 {
 	 $('#row'+val).remove();

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