<?php
include('sessionCheck.php');
include('connection.php');
include('functions.php');
require_once ("inc/init.php");
require_once ("inc/config.ui.php");
$page_title = "Employee";
include ("inc/header.php");
//$page_nav["Settings"]["sub"]["Brands"]["active"] = true;
include ("inc/nav.php");
$branch_id=$_SESSION['branch_id'];
?>
<!-- ==========================CONTENT STARTS HERE ========================== -->
<!-- MAIN PANEL -->
<div id="main" role="main">

	
<?php $breadcrumbs["Admin Tool"] = "";
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
    
}
label {
   
    margin-top: 8px !important;
}
textarea.form-control {
    height: 70px;
}
.select2-container .select2-choice {
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
    border-radius: 5px;
    width: 100%;
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
				<span class="small_icon"><i class="fa fa-steam"></i>	</span>	
				<h2>Employee</h2>
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
	$u_FullName=validate_input($_POST['u_FullName']);
	$u_Cell=validate_input($_POST['u_Cell']);
	$u_Email=validate_input($_POST['u_Email']);
	$u_Password=md5(validate_input($_POST['u_Password']));
	$u_Password2=validate_input($_POST['u_Password']);
	$u_Gender='';
	$u_Address=validate_input($_POST['u_Address']);
	$u_LoginDateFrom='2010-01-01';
	$u_LoginDateTo='2050-12-31';
	$u_Remarks=validate_input($_POST['u_Remarks']);
	if(isset($_POST['u_Status'])) {$u_Status=1;} else {$u_Status=0;}
	if(isset($_POST['u_CommissionAllow'])) {$u_CommissionAllow=1;} else {$u_CommissionAllow=0;}
	if(isset($_POST['u_PerformanceMonitoring'])) {$u_PerformanceMonitoring=1;} else {$u_PerformanceMonitoring=0;}
	$u_CommissionOnSales=validate_input($_POST['u_CommissionOnSales']);
	$u_CommissionPercentage=validate_input($_POST['u_CommissionPercentage']);
	
                    
	$u_PerformanceAmountFrom=validate_input($_POST['u_PerformanceAmountFrom']);
	$u_PerformanceAmountTo=validate_input($_POST['u_PerformanceAmountTo']);
	$role_id=validate_input($_POST['role_id']);
	

	$email_Already=mysqli_query($con,"select u_Email from u_user where u_Email='$u_Email'");
	$email_AlreadyRows=mysqli_num_rows($email_Already);
	if($email_AlreadyRows>0)
	{
	?>
		<script type="text/javascript">
			alert('Given Email <?php echo $u_Email?> Already Exists.');
			window.history.back();
		</script>
	<?php
	die();
    }
	 
	
	
	$fcQuery = "INSERT INTO `u_user`(`u_id`, `u_FullName`, `u_Cell`, `u_Email`,		`u_Password`,	`u_Password2`, `u_Gender`, `u_Address`,			`u_LoginDateFrom`, `u_LoginDateTo`, `u_CreatedAt`, `u_Remarks`, 	`branch_id`, 	`branch_admin`, `u_CommissionAllow`, `u_CommissionOnSales`,	u_CommissionPercentage,		`u_PerformanceMonitoring`, `u_PerformanceAmountFrom`, `u_PerformanceAmountTo`,role_id,	u_Status)
						VALUES 		('',	'$u_FullName',	'$u_Cell',	'$u_Email',	'$u_Password',	'$u_Password2',	'$u_Gender',	'$u_Address',	'$u_LoginDateFrom',	'$u_LoginDateTo',	now(),				'$u_Remarks',	'$branch_id',	'0',			'$u_CommissionAllow',	'$u_CommissionOnSales',	'$u_CommissionPercentage', '$u_PerformanceMonitoring',	'$u_PerformanceAmountFrom',	'$u_PerformanceAmountTo',	'$role_id','$u_Status')";
	if(mysqli_query($con,$fcQuery))
	{
		$inserted_u_id=mysqli_insert_id($con);
		
		mysqli_query($con, "Delete from u_userd where u_id=$inserted_u_id");
		mysqli_query($con, "Insert into u_userd (u_id, role_id, branch_id) values ('$inserted_u_id', '$role_id', '$branch_id')");
		$_SESSION['msg']= "<div class='alert alert-info'>Employee Created Successfully</div>";
		
		$to=$u_Email;
				//$to='asad.general@gmail.com';

				// To send HTML mail, the Content-type header must be set
				$headers  = 'MIME-Version: 1.0' . "\r\n";
				$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
									
				// Additional headers
				//$headers .= 'To: Mary <mary@example.com>, Kelly <kelly@example.com>' . "\r\n";
				$headers .= 'From: ePOS Daddy <admin@eposdaddy.com>' . "\r\n";
				//$headers .= 'Cc: asad.general@gmail.com' . "\r\n";
				//$headers .= 'Bcc: birthdaycheck@example.com' . "\r\n";				


				$email_content='
				<h2 style="color:#03C; color:#c44735; font-size:28px; text-shadow:1px 1px 1px #000; font-family:Georgia, "Times New Roman", Times, serif">ePOS Daddy</h2>
				<b style="font-family:Arial, Helvetica, sans-serif; color:#999; font-weight:normal;">(That Was Easy)</b>
				<div style="width:100%; height:20px; font-family:Arial, Helvetica, sans-serif; font-size:16px; padding:6px 0px; background-color:#c44735; font-weight:normal; color:#FFF; margin:4px 0px; text-shadow:2px 2px 2px #000">
				&nbsp; Registration Successfull
				</div>
				<span style="font-family:Arial, Helvetica, sans-serif; font-size:13px; line-height:25px; font-weight:normal">
				<b>Dear Member,</b><br /><br />

				Your Registration detail as below:-<br />';

				$email_content.='
				<table align="left" style="border:solid 1px #CCC;border-collapse: collapse;font-family:arial;font-size:14px;">
					<thead>
					<tr>
						<th style="border:solid 1px #CCC;background-color:#f2f2f2;padding:6px;">Login Email</th>
						<th style="border:solid 1px #CCC;padding:6px; text-align:left;">'.$to.'</th>
					</tr>
					
					<tr>
						<th style="border:solid 1px #CCC;background-color:#f2f2f2;padding:6px;">Password</th>
						<th style="border:solid 1px #CCC;padding:6px; text-align:left;">'.$u_Password2.'</th>
					</tr>
					
					</thead>
					<tbody>
				</table>
				<br style="clear:both;" >
				<br style="clear:both;" >
				<br style="clear:both;" >
					<a href="https://www.eposdaddy.com/epos/login"><button style="padding:8px 20px;float:left;background-color:#c44735; cursor:pointer;font-weight:normal; color:#FFF;">Click here to Login</button></a>
				<br style="clear:both;" >
				</span><br />
					';
				
				$sendEmail=mail($to, 'Welcome - ePOS Daddy', $email_content, $headers);
		
		

	?>
	<script type="text/javascript">window.location="";</script>
	<?php
	die();
	}
	else
	{
		$_SESSION['msg']="<div class='alert alert-info'>Problem creating the Employee</div>";
	}
}


if(isset($_SESSION['msg'])){ echo $_SESSION['msg']; unset($_SESSION['msg']);} ?>
			
<?php
if(!isset($_GET['id']))
{
?>
				<form id="checkout-form" class="smart-form" novalidate="novalidate" method="post" action=""  onsubmit="return checkParameters();">
					<fieldset>
					<div class="row">
						<div class="col col-lg-1 col-xs-12">
							<label>Full Name</label>
						</div>
						<div class="col col-lg-3 col-xs-12">
							<input type="text" name="u_FullName" id="u_FullName" class="form-control">
						</div>
					
					
						<div class="col col-lg-1 col-xs-12">
							<label> Status</label>
						</div>
						<div class="col col-lg-3 col-xs-12">
							<input type="checkbox" name="u_Status" id="u_Status" checked="checked" class="form-control">
						</div>
					</div><!--End of row-->
                    
                    <div class="row" style="margin-top:5px;">
						<div class="col col-lg-1 col-xs-12">
							<label>Email Address</label>
						</div>
						<div class="col col-lg-3 col-xs-12">
                        
							<input type="text" name="u_Email" id="u_Email" class="form-control">
						</div>
					
					
						<div class="col col-lg-1 col-xs-12">
							<label> Password</label>
						</div>
						<div class="col col-lg-3 col-xs-12">
							<input type="text" name="u_Password" id="u_Password" class="form-control">
						</div>
					</div><!--End of row-->
                    
                    <div class="row" style="margin-top:5px;">
						<div class="col col-lg-1 col-xs-12">
							<label>Cell Number</label>
						</div>
						<div class="col col-lg-3 col-xs-12">
                        
							<input type="text" name="u_Cell" id="u_Cell" class="form-control">
						</div>
					
					
						<div class="col col-lg-1 col-xs-12">
							<label> Address</label>
						</div>
						<div class="col col-lg-3 col-xs-12">
							<input type="text" name="u_Address" id="u_Address" class="form-control">
						</div>
					</div><!--End of row-->
                    
                    <div class="row" style="margin-top:5px;">
						<div class="col col-lg-1 col-xs-12">
							<label>Notes</label>
						</div>
						<div class="col col-lg-3 col-xs-12">
                        
							<input type="text" name="u_Remarks" id="u_Remarks" class="form-control">
						</div>
                        
                        <div class="col col-lg-1 col-xs-12">
							<label>Select Role</label>
						</div>
						<div class="col col-lg-3 col-xs-12">
                        	<select class="select2" name="role_id">
							<?php
							$roleArray=get_RolesBranch();
							foreach ($roleArray as $key => $roleRow)
							{
							?>
								<option value="<?php echo $roleRow['role_id'];?>"><?php echo $roleRow['role_name']; ?></option>
							<?php
							}
							?>
							</select>
						</div>
                        
                        
                        
					
					</div><!--End of row-->
                    
                    
                    <div class="row" style="margin-top:30px;">
						<div class="col col-lg-8 col-xs-12">
							<div class="alert alert-success"><span class="small_icon-inner "><i class="fa fa-money"></i>	</span><strong>Employee Comission</strong></div>
						</div>
					</div><!--End of row-->
                    
                    
                    <div class="row" style="margin-top:5px;">
						<div class="col col-lg-1 col-xs-12">
							<label>Allow Commission</label>
						</div>
						<div class="col col-lg-3 col-xs-12">
                        
							<input type="checkbox" name="u_CommissionAllow" id="u_CommissionAllow" class="form-control">
						</div>
					
					
					</div><!--End of row-->
                 <div class="row" style="margin-top:5px;">
						<div class="col col-lg-1 col-xs-12">
							<label>Commission Percentage</label>
						</div>
						<div class="col col-lg-3 col-xs-12">
                        
							<input type="number" name="u_CommissionPercentage" id="u_CommissionPercentage" class="form-control">
						</div>
					
						<div class="col col-lg-1 col-xs-12">
							<label> On Sales Amount</label>
						</div>
						<div class="col col-lg-3 col-xs-12">
							<input type="number" name="u_CommissionOnSales" id="u_CommissionOnSales" class="form-control">
						</div>
					</div><!--End of row-->
                    
                    <div class="row" style="margin-top:30px;">
						<div class="col col-lg-8 col-xs-12">
							<div class="alert alert-success"><span class="small_icon-inner"><i class="fa fa-dashboard"></i>	</span><strong>Employee Performance</strong></div>
						</div>
					</div><!--End of row-->
                    
                    
                    <div class="row" style="margin-top:5px;">
						<div class="col col-lg-1 col-xs-12">
							<label>Performance Monitoring</label>
						</div>
						<div class="col col-lg-3 col-xs-12">
                        
							<input type="checkbox" name="u_PerformanceMonitoring" id="u_PerformanceMonitoring" class="form-control">
						</div>
					
					
					</div><!--End of row-->
                    
                    <div class="row" style="margin-top:25px; margin-bottom:25px;">
						<div class="col col-lg-1 col-xs-12">
							<label>Target From</label>
						</div>
						<div class="col col-lg-3 col-xs-12">
                        
							<input type="number" name="u_PerformanceAmountFrom" id="u_PerformanceAmountFrom" class="form-control">
						</div>
					
					<div class="col col-lg-1 col-xs-12">
							<label>Target To</label>
						</div>
						<div class="col col-lg-3 col-xs-12">
                        
							<input type="number" name="u_PerformanceAmountTo" id="u_PerformanceAmountTo" class="form-control">
						</div>
						
                        
					</div><!--End of row-->
                    
                    
					</fieldset>
					<footer>
						<input type="submit" class="btn btn-primary" name="submit" id="submit" value="Save">
					</footer>
				</form>
			
<?php
}
else
{
	$u_id= (int) $_GET['id'];
	$u_idQ= "SELECT * WHERE u_id=$u_id";
	$u_idRes=mysqli_query($con,$u_idQ);
	if(mysqli_num_rows($u_idRes)!=1)
	{
		echo "Invalid";
		die();
	}
	else
	{
		$u_idRow = mysqli_fetch_assoc($u_idRes);
?>
				<form id="checkout-form" class="smart-form" novalidate="novalidate" method="post" action="" onsubmit="return checkParameters();">	
					<fieldset>
					<div class="row">
						<div class="col col-lg-2 col-xs-12">
							<label> Full Name</label>
						</div>
						<div class="col col-lg-4 col-xs-12">
							<input type="text" name="brand_Name" id="brand_Name" class="form-control" value="<?php echo $u_idRow['brand_Name'];?>">
						</div>
					</div><!--End of row-->
					<div class="row" style="margin-top: 5px;">
						<div class="col col-lg-2 col-xs-12">
							<label> Status</label>
						</div>
						<div class="col col-lg-4 col-xs-12">
							<select class="form-control" name="brand_Status">
								<option value="A" <?php if($brandRow['brand_Status']=='A'){ echo "selected='selected'";}?>>Active</option>
								<option value="I" <?php if($brandRow['brand_Status']=='I'){ echo "selected='selected'";}?>>In-Active</option>
							</select>
						</div>
					</div><!--End of row-->
					</fieldset>
					<footer>
						<input type="hidden" name="brand_id" value="<?php echo $brandRow['brand_id'];?>">
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
 
 
function checkParameters(){
	var u_Email = $.trim($("#u_Email").val());
	var u_Password = $.trim($("#u_Password").val());

	if (u_Email == "")
	{
		$.smallBox({
		title : "Error",
		content : "<i class='fa fa-clock-o'></i> <i>Please Fill Email Address.</i>",
		color : "#C46A69",
		iconSmall : "fa fa-times fa-2x fadeInRight animated",
		timeout : 4000
		});
	$("#u_Email").focus();
	return false;
	}
	
	if (u_Password == '')
	{
		$.smallBox({
		title : "Error",
		content : "<i class='fa fa-clock-o'></i> <i>Please Fill Password.</i>",
		color : "#C46A69",
		iconSmall : "fa fa-times fa-2x fadeInRight animated",
		timeout : 4000
		});
	$("#u_Password").focus();
	return false;
	}
} 
</script>