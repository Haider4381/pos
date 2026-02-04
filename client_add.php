<?php 
include('sessionCheck.php');
include('connection.php');
include('functions.php');

//initilize the page
require_once ("inc/init.php");

//require UI configuration (nav, ribbon, etc.)
require_once ("inc/config.ui.php");

/*---------------- PHP Custom Scripts ---------

 YOU CAN SET CONFIGURATION VARIABLES HERE BEFORE IT GOES TO NAV, RIBBON, ETC.
 E.G. $page_title = "Custom Title" */

$page_title = "Customers";
$u_id=$_SESSION['u_id'];
$branch_id=$_SESSION['branch_id'];
/* ---------------- END PHP Custom Scripts ------------- */

//include header
include ("inc/header.php");

//include left panel (navigation)
//follow the tree in inc/config.ui.php
//$page_nav["Settings"]["sub"]["Customers"]["active"] = true;
include ("inc/nav.php");
?>
<!-- ==========================CONTENT STARTS HERE ========================== -->
<!-- MAIN PANEL -->
<div id="main" role="main">
<style type="text/css">
	td{
		padding: 3px !important;
	}
</style>
	<?php
		//configure ribbon (breadcrumbs) array("name"=>"url"), leave url empty if no url
		//$breadcrumbs["New Crumb"] => "http://url.com"
		$breadcrumbs["New"] = "";
		include("inc/ribbon.php");
	?>

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
				<span class="small_icon"><i class="fa fa-child"></i>	</span>		
				<h2>Add New Customer</h2>
			</header>

			<!-- widget div-->
			<div>		


				<!-- widget content -->
				<div class="widget-body no-padding">

		<div class="tab-content" >
			<div role="tabpanel" class="tab-pane active" id="add" >
<?php 
/************************************
DATA INSERTIONS STARTS
************************************/
if(isset($_POST['save'])){
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
				$_SESSION['msg']="Customer Created successfully";
			}	
			else
			{
				$_SESSION['msg']="Problem creating Customer";
			}
			echo '<script> window.location=""; </script>';
			die();
		}
/************************************
END OF DATA INSERTIONS
************************************/
/************************************
DATA INSERTIONS STARTS
************************************/
		if(isset($_POST['update'])){
			$client_id=validate_input($_POST['client_id']);
			$client_Name=validate_input($_POST['client_Name']);
			$client_Email=validate_input($_POST['client_Email']);
			$client_Phone=validate_input($_POST['client_Phone']);
			$client_Address=validate_input($_POST['client_Address']);
			$client_Status=validate_input($_POST['client_Status']);
			$client_Remarks=validate_input($_POST['client_Remarks']);
			 $q="UPDATE adm_client
			 	SET
					client_Name='$client_Name',
					client_Phone='$client_Phone',
					client_Email='$client_Email',
					client_Status='$client_Status',
					client_Remarks='$client_Remarks',
					client_Address='$client_Address'
			 WHERE client_id=$client_id";
			if(mysqli_query($con,$q))
			{
				$_SESSION['msg']="Customer Updated successfully";
				echo "<script>window.location='client_add.php'; </script>";
				die();
			}	

			else
			{
				$_SESSION['msg']="Problem Updating Customer";
			}
				
		}
/************************************
END OF DATA INSERTIONS
************************************/
?>
<?php if(!empty($_SESSION['msg'])){?>   <div class="alert alert-info"><?php echo $_SESSION['msg']; ?> </div> <?php unset($_SESSION['msg']); } ?>
		<?php 
		if(!isset($_GET['id']))
		{
		?>
			<form  class="smart-form" method="post" id="client_form"  onsubmit="return checkParameters();">	
			<fieldset>
			<div class="row" style="margin-bottom: 5px;">
			<div class="col col-lg-2">
				<div class="col col-lg-12 col-sm-12 col-xs-12">
					<label>Full Name:</label>
				</div>
			</div>
			<div class="col col-lg-3">
				<div class="col col-lg-12 col-sm-12 col-xs-12">
					<input type="text" name="client_Name" id="client_Name" required="required" class="form-control">
				</div>
			</div>

			<div class="col col-lg-2">
				<div class="col col-lg-12 col-sm-12 col-xs-12">
					<label>Email Address:</label>
				</div>
			</div>
			<div class="col col-lg-3">
				<div class="col col-lg-12 col-sm-12 col-xs-12">
					<input type="text" name="client_Email"  class="form-control">
				</div>
			</div>
		</div>
			

		<!--2nd row start here-->

		<div class="row" style="margin-bottom: 5px;">
			<div class="col col-lg-2">
				<div class="col col-lg-12 col-sm-12 col-xs-12">
					<label>Phone Number:</label>
				</div>
			</div>
			<div class="col col-lg-3">
				<div class="col col-lg-12 col-sm-12 col-xs-12">
					<input type="text" name="client_Phone"  class="form-control">
				</div>
			</div>
			<div class="col col-lg-2">
				<div class="col col-lg-12 col-sm-12 col-xs-12">
					<label>Customer Status:</label>
				</div>
			</div>
			<div class="col col-lg-3">
				<div class="col col-lg-12 col-sm-12 col-xs-12">
					<select name="client_Status" class="form-control"> 
						<option value="A">Active</option>
						<option value="I">In-Active</option>
					</select>
				</div>
			</div>
		</div>

		


		<!--3rd row start here-->

		<div class="row" style="margin-bottom: 5px;">
			<div class="col col-lg-2">
				<div class="col col-lg-12 col-sm-12 col-xs-12">
					<label>Address:</label>
				</div>
			</div>
			<div class="col col-lg-8">
				<div class="col col-lg-12 col-sm-12 col-xs-12">
					<textarea name="client_Address"  class="form-control"></textarea> 
				</div>
			</div>
			<div class="col col-lg-3">
			
			</div>
			
		</div>
		
					<!--5th row start here-->

			<div class="row" style="margin-bottom: 5px;">
				<div class="col col-lg-2">
					<div class="col col-lg-12">
						<label>Notes:</label>
					</div>
				</div>
				<div class="col col-lg-8">
					<div class="col col-lg-12">
					<textarea name="client_Remarks" class="form-control"></textarea> 
					</div>
				</div>
			</div>
			</fieldset>
					<footer>
						<input type="hidden" name="save" value="save">
						<p class="btn btn-primary" onclick="save_form()">	Save </p>
					</footer>
				</form>
		<?php 
		}
		else
		{
			$client_id=(int) $_GET['id'];
			$clientQ="SELECT client_id, client_Name, client_Phone, client_Email, client_Status, client_Remarks, client_Address,client_SaleDiscountPercentage, client_oppo,client_huawei,client_samsung FROM adm_client WHERE client_id=$client_id";
			$clientRow=mysqli_fetch_assoc(mysqli_query($con,$clientQ));
		?>
		<form  class="smart-form" method="post" id="client_form" onsubmit="return checkParameters();">	
			<fieldset>
			<div class="row" style="margin-bottom: 5px;">
			<div class="col col-lg-2">
				<div class="col col-lg-12 col-sm-12 col-xs-12">
					<label>Full Name:</label>
				</div>
			</div>
			<div class="col col-lg-3">
				<div class="col col-lg-12 col-sm-12 col-xs-12">
					<input type="text" name="client_Name" id="client_Name" required="required" class="form-control" value="<?php echo $clientRow['client_Name']; ?>">
				</div>
			</div>

			<div class="col col-lg-2">
				<div class="col col-lg-12 col-sm-12 col-xs-12">
					<label>Email Address:</label>
				</div>
			</div>
			<div class="col col-lg-3">
				<div class="col col-lg-12 col-sm-12 col-xs-12">
					<input type="text" name="client_Email"  class="form-control" value="<?php echo $clientRow['client_Email']; ?>">
				</div>
			</div>
		</div>
			

		<!--2nd row start here-->

		<div class="row" style="margin-bottom: 5px;">
			<div class="col col-lg-2">
				<div class="col col-lg-12 col-sm-12 col-xs-12">
					<label>Phone Number:</label>
				</div>
			</div>
			<div class="col col-lg-3">
				<div class="col col-lg-12 col-sm-12 col-xs-12">
					<input type="text" name="client_Phone"  class="form-control" value="<?php echo $clientRow['client_Phone']; ?>">
				</div>
			</div>
			<div class="col col-lg-2">
				<div class="col col-lg-12 col-sm-12 col-xs-12">
					<label>Customer Status:</label>
				</div>
			</div>
			<div class="col col-lg-3">
				<div class="col col-lg-12 col-sm-12 col-xs-12">
					<select name="client_Status" class="form-control"> 
						<option value="A" <?php if($clientRow['client_Status']=='A'){ echo"selected='selected'"; } ?>>Active</option>
						<option value="I" <?php if($clientRow['client_Status']=='I'){ echo "selected='selected'";} ?>>In-Active</option>
					</select>
				</div>
			</div>
		</div>
		<!--4th row start here-->

		<div class="row" style="margin-bottom: 5px;">
			<div class="col col-lg-2">
				<div class="col col-lg-12 col-sm-12 col-xs-12">
					<label>Address:</label>
				</div>
			</div>
			<div class="col col-lg-8">
				<div class="col col-lg-12 col-sm-12 col-xs-12">
					<textarea name="client_Address"  class="form-control"><?php echo $clientRow['client_Address']; ?></textarea> 
				</div>
			</div>
			<div class="col col-lg-3">
			
			</div>
			
		</div>
		
					<!--5th row start here-->

			<div class="row" style="margin-bottom: 5px;">
				<div class="col col-lg-2">
					<div class="col col-lg-12">
						<label>Notes:</label>
					</div>
				</div>
				<div class="col col-lg-8">
					<div class="col col-lg-12">
					<textarea name="client_Remarks" class="form-control"><?php echo $clientRow['client_Remarks']; ?></textarea> 
					</div>
				</div>
			</div>	
	</fieldset>
			<footer>
				<input type="hidden" name="client_id" value="<?php echo $clientRow['client_id']; ?>">
				<input type="hidden" name="update" value="update">
				<p class="btn btn-primary" onclick="save_form();">Save</p>
			</footer>
		</form>
		<?php
		}
		?>
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
<?php // include page footer
include ("inc/footer.php");
?>
<!-- END PAGE FOOTER -->

<?php //include required scripts
include ("inc/scripts.php");
?>

<!-- PAGE RELATED PLUGIN(S) -->
<script src="<?php echo ASSETS_URL; ?>/js/plugin/datatables/jquery.dataTables.min.js"></script>
<script src="<?php echo ASSETS_URL; ?>/js/plugin/datatables/dataTables.colVis.min.js"></script>
<script src="<?php echo ASSETS_URL; ?>/js/plugin/datatables/dataTables.tableTools.min.js"></script>
<script src="<?php echo ASSETS_URL; ?>/js/plugin/datatables/dataTables.bootstrap.min.js"></script>
<script src="<?php echo ASSETS_URL; ?>/js/plugin/datatable-responsive/datatables.responsive.min.js"></script>

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

	/* BASIC ;*/
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
	
	/* COLUMN FILTER  */
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
  /*  $("div.toolbar").html('<div class="text-right"><img src="img/logo.png" alt="SmartAdmin" style="width: 111px; margin-top: 3px; margin-right: 10px;"></div>');*/
    	   
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
	var new_client_Name = $.trim($("#client_Name").val());
	if (new_client_Name == '')
	{
		$.smallBox({
		title : "Error",
		content : "<i class='fa fa-clock-o'></i> <i>Please Fill Customer Name.</i>",
		color : "#C46A69",
		iconSmall : "fa fa-times fa-2x fadeInRight animated",
		timeout : 4000
		});
	$("#client_Name").focus();
	return false;
	}
}

	function save_form(){
		$("#client_form").submit();
	}	
</script>