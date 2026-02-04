<?php include('sessionCheck.php');
include('connection.php');
include('functions.php');
require_once ("inc/init.php");
require_once ("inc/config.ui.php");
$page_title = "Employee Commission";
include ("inc/header.php");
//$page_nav["Settings"]["sub"]["Suppliers"]["active"] = true;
include ("inc/nav.php");
$u_id=$_SESSION['branch_id'];
$branch_id=$_SESSION['branch_id']; 
?>
<!-- ==========================CONTENT STARTS HERE ========================== -->
<!-- MAIN PANEL -->
<div id="main" role="main">

	
<?php $breadcrumbs["Reports"] = "";
include("inc/ribbon.php");
 
?>
<style>
    .fc-border-separate thead tr, .table thead tr {
        font-size:16px;
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
				<span class="small_icon"><i class="fa fa-money"></i>	</span>	
				<h2>Employee Commision</h2>
			</header>

			<!-- widget div-->
			<div>		


				<!-- widget content -->
				<div class="widget-body no-padding">
	<br>

		<div class="tab-content" >
					<table id="datatable_fixed_column" class="table table-striped table-bordered" width="100%">

				 <thead style="font-size: 16px;
    font-weight: bold;">	
							<tr>
								<th>Employee Name</th>
								<th>Sales Target</th>
								<th>Commission Percentage</th>
								<th>Sales</th>
								<th>Employee Commision</th>
							</tr>
						</thead>
						<tbody>
							
<?php $empArray=get_EmployeesBranch();
foreach ($empArray as $key => $empRow)
{
	$targetlight_bg='grey';
	$u_id_row=$empRow['u_id'];
	$sales_target=$sales_percentage=$total_sales_of_month=0;
	$TargetQ="SELECT ifnull(sum(s_NetAmount),0) as total_sales_of_month
			FROM cust_sale
			WHERE branch_id=$branch_id AND u_id=$u_id_row";
	$TargetQr=mysqli_query($con,$TargetQ);
	$TargetQrow=mysqli_fetch_assoc($TargetQr);
	
	
	$u_CommissionOnSales=$empRow['u_CommissionOnSales'];
	$u_CommissionPercentage=$empRow['u_CommissionPercentage'];
	$total_sales_of_month=$TargetQrow['total_sales_of_month'];
	
	$employee_commission=0;	
	if($total_sales_of_month>$u_CommissionOnSales)
	{
		$employee_commission=(($u_CommissionPercentage/100)*$total_sales_of_month);
	}

?>
				 			
						<?php echo "<tr id='row".$empRow['u_id']."'>";?>
                                        <td><?php echo $empRow['u_FullName'];?> </td>
                                        <td><?php echo $currency_symbol.$empRow['u_CommissionOnSales'];?> </td>
                                        <td><?php echo $empRow['u_CommissionPercentage'];?>%</td>
                                        <td><?=$currency_symbol.$total_sales_of_month?></td>
                                        <td><?=$currency_symbol.$employee_commission?></td>
                                    </tr>
			 		
							
<?php } 
?>

				 </tbody>
				
					</table>

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

</script>