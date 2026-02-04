<?php
include('sessionCheck.php');
include('connection.php');
require_once ("inc/init.php");
require_once ("inc/config.ui.php");
$page_title = "Item Unit ";
include ("inc/header.php");
//$page_nav["Settings"]["sub"]["Brands"]["active"] = true;
include ("inc/nav.php");
$u_id=$_SESSION['u_id'];
$branch_id=$_SESSION['branch_id'];
?>
<!-- ==========================CONTENT STARTS HERE ========================== -->
<!-- MAIN PANEL -->
<div id="main" role="main">

	
<?php $breadcrumbs["Inventory"] = "";
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
    width: 406px;
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
				<span class="small_icon"><i class="fa fa-certificate"></i>	</span>	
				<h2>Item Unit</h2>
			</header>

			<!-- widget div-->
			<div>


				<!-- widget content -->
				<div class="widget-body no-padding">
<br>
<ul class="nav nav-tabs" role="tablist" style="margin-left: 2px;">
	<li role="presentation" class="active"><a href="#add" aria-controls="add" role="tab" data-toggle="tab" style="color:black !important">Add New</a></li>
	<li role="presentation"><a href="#list" aria-controls="list" role="tab" data-toggle="tab" style="color:black !important">Lists</a></li>
</ul>
		<div class="tab-content" >
			<div role="tabpanel" class="tab-pane active" id="add" >

<?php
if(isset($_POST['submit']))
{
	$unit_name = validate_input($_POST['unit_name']);
	$alreadyQ=mysqli_query($con, "select * from adm_itemunit where unit_name='$unit_name' AND branch_id=$branch_id");
	$alreadyRow=mysqli_num_rows($alreadyQ);
	if($alreadyRow>=1)
	{ ?>
    	
        <script type="text/javascript">alert('Unit "<?=$unit_name?>" Already Exists');window.location="";</script>
    
    <?php
		die();
	}
	
	$fcQuery = "INSERT INTO adm_itemunit (unit_name,u_id, branch_id, unit_createdat) VALUES('$unit_name','$u_id','$branch_id',now())";
	if(mysqli_query($con,$fcQuery))
	{
		$_SESSION['msg']= "<div class='alert alert-info'>Unit created successfully</div>";
	?>
			<script type="text/javascript"> window.location="";</script>
	<?php
	die();
	}
	else
	{
		$msg ="<div class='alert alert-info'>Problem creating the Unit</div>";
	}
}

if(isset($_POST['update']))
{
	$unit_id = $_POST['unit_id'];
	$unit_name = validate_input($_POST['unit_name']);
	$alreadyQ=mysqli_query($con, "select * from adm_itemunit where unit_name='$unit_name' AND branch_id=$branch_id AND unit_id!=$unit_id");
	$alreadyRow=mysqli_num_rows($alreadyQ);
	if($alreadyRow>=1)
	{ ?>
    	
        <script type="text/javascript">alert('Unit not change given name "<?=$unit_name?>" already exists');window.location="";</script>
    <?php
		die();
	}
	
	$fcQuery = "UPDATE adm_itemunit SET unit_name='$unit_name' WHERE unit_id=$unit_id";
	if(mysqli_query($con,$fcQuery)) { $_SESSION['msg']= "<div class='alert alert-info'>Unit updated successfully</div>";
 
	?>
	<script type="text/javascript"> window.location="item_unit.php";</script>	
	<?php die();
	}
	else
	{
		$msg ="<div class='alert alert-info'>Problem updating the Unit</div>";
	}
} 
?>


<?php if(isset($_SESSION['msg'])){ echo $_SESSION['msg'];
 unset($_SESSION['msg']);
 } 
?>

<?php if(!empty($msg)){ echo $msg; } ?>
			
<?php
if(!isset($_GET['id']))
{
	?>
				<form id="checkout-form" class="smart-form" novalidate="novalidate" method="post" action=""  onsubmit="return checkParameters();">	
					<fieldset>
					<div class="row">
						<div class="col col-lg-2 col-xs-12">
							<label>Unit Name</label>
						</div>
						<div class="col col-lg-4 col-xs-12">
							<input type="text" name="unit_name" id="unit_name" class="form-control" placeholder=" eg. Laptop, Mobile or Accessories">
						</div>
					</div><!--End of row-->
					</fieldset>
					<footer style="    margin-top: 26px;">
						<input type="submit" class="btn btn-primary" name="submit" id="submit" value="Save">
					</footer>
				</form>
			
<?php
}
else
{
	$unit_id= (int) $_GET['id'];
	$brandQ= "SELECT * FROM adm_itemunit WHERE unit_id=$unit_id";
	$brandRes=mysqli_query($con,$brandQ);
	if(mysqli_num_rows($brandRes)!=1)
	{
		echo "Invalid";
	}
	else
	{
		$brandRow = mysqli_fetch_assoc($brandRes);
?>
				<form id="checkout-form" class="smart-form" novalidate="novalidate" method="post" action="" onsubmit="return checkParameters();">	
					<fieldset>
					<div class="row">
						<div class="col col-lg-2 col-xs-12">
							<label> Name</label>
						</div>
						<div class="col col-lg-4 col-xs-12">
							<input type="text" name="unit_name" id="unit_name" class="form-control" value="<?php echo $brandRow['unit_name'];?>">
						</div>
					</div><!--End of row-->
 					</fieldset>
					<footer style="    margin-top: 26px;">
						<input type="hidden" name="unit_id" value="<?php echo $brandRow['unit_id'];?>">
						<input type="submit" class="btn btn-primary" name="update" id="submit" value="Save">
					</footer>
				</form>
				
<?php }
} 
?>
			</div><!--End of div id="add"-->

			<div role="tabpanel" class="tab-pane" id="list">


					<table id="datatable_fixed_column" class="table table-striped table-bordered" width="100%">

				 <thead>
							<tr>
								<th class="hasinput" style="width:70%">
									<input type="text" class="form-control" placeholder="Filter Name" />
								</th>
								<th class="hasinput" style="width:36%">
									
								</th>
								
							</tr>
				 <tr>
			 <th data-class="expand">Name</th>
			 <th data-hide="phone">Action</th>
			 
				 </tr>
				 </thead>
				 
<?php $brandQ = "SELECT * FROM adm_itemunit Where branch_id=$branch_id ";
$brandRes = mysqli_query($con,$brandQ);
while($brandRow = mysqli_fetch_assoc($brandRes))
{
?>
				 	<tr id="row<?php echo $brandRow['unit_id'];?>">
				 		<td><?php echo $brandRow['unit_name'];?></td>				 		
				 		<td>
				 			<a href="?id=<?php echo $brandRow['unit_id'];?>" class='btn btn-primary'>Edit</a>
                            <a href="javascript:del(<?php echo $brandRow['unit_id'];?>)" class="btn btn-danger">Delete</a>
				 		</td>
				 	</tr>
				 	
				 
<?php } 
?>

				 <tbody>
				 

				 </tbody>
				
					</table>

				</div>
				<!-- end widget content -->
			</div><!--End of list-->
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


$.post("ajax/delAjax.php",
 {
 unit_id : val, 
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
	var unit_name = $.trim($("#unit_name").val());
	if (unit_name == '')
	{
		$.smallBox({
		title : "Error",
		content : "<i class='fa fa-clock-o'></i> <i>Please Give Unit Name.</i>",
		color : "#C46A69",
		iconSmall : "fa fa-times fa-2x fadeInRight animated",
		timeout : 4000
		});
	$("#unit_name").focus();
	return false;
	}
}

</script>