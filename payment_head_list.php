<?php
include('sessionCheck.php');
include('connection.php');
include('functions.php');
require_once ("inc/init.php");
require_once ("inc/config.ui.php");
$page_title = "Payee List";
include ("inc/header.php");
//$page_nav["Settings"]["sub"]["Brands"]["active"] = true;
include ("inc/nav.php");
$branch_id=$_SESSION['branch_id'];
?>
<!-- ==========================CONTENT STARTS HERE ========================== -->
<!-- MAIN PANEL -->
<div id="main" role="main">

	
<?php $breadcrumbs["List"] = "";
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
				<span class="small_icon"><i class="fa fa-university"></i>	</span>	
				<h2>Payee List</h2>
			</header>

			<!-- widget div-->
			<div>		


				<!-- widget content -->
				<div class="widget-body no-padding">
	<br>

<div class="tab-content" >
	<table id="datatable_fixed_column" class="table table-striped table-bordered" width="100%">
	<thead>
		<tr>
			<th class="hasinput" style="width:15%"> <input type="text" class="form-control" placeholder="Filter Name" /></th>
            <th class="hasinput" style="width:16%"><input type="text" class="form-control" placeholder="Filter Cell" /></th>
            <th class="hasinput" style="width:16%"><input type="text" class="form-control" placeholder="Filter Email" /></th>
            <th class="hasinput" style="width:16%"><input type="text" class="form-control" placeholder="Filter Address" /></th>
            <th class="hasinput" style="width:16%"><input type="text" class="form-control" placeholder="Filter Notes" /></th>
			<th class="hasinput" style="width:16%"></th>
		</tr>
		<tr>
			<th data-class="expand">Name</th>
            <th>Cell</th>
            <th>Email</th>
            <th>Address</th>
            <th>Notes</th>
			<th data-hide="phone">Action</th>
		</tr>
	</thead>
				 
<?php
$empQ = "SELECT * FROM adm_payee WHERE branch_id=$branch_id";
$empRes = mysqli_query($con,$empQ);
while($empRow = mysqli_fetch_assoc($empRes))
{
?>
				 	<tr id="row<?php echo $empRow['payee_id'];?>">
				 		<td><?php echo $empRow['payee_name'];?></td>
                        <td><?php echo $empRow['payee_cell'];?></td>
                        <td><?php echo $empRow['payee_email'];?></td>
                        <td><?php echo $empRow['payee_address'];?></td>
                        <td><?php echo $empRow['payee_notes'];?></td>				 		
                        
				 		<td>
				 			<a href="payment_head.php?id=<?php echo $empRow['payee_id'];?>" class='btn btn-primary'>Edit</a>
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