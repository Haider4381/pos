<?php include('sessionCheck.php');
include('connection.php');
include('functions.php');
require_once ("inc/init.php");
require_once ("inc/config.ui.php");
$page_title = "Employee Performance";
include ("inc/header.php");
//$page_nav["Settings"]["sub"]["Suppliers"]["active"] = true;
include ("inc/nav.php");
$u_id=$_SESSION['branch_id'];
$branch_id=$_SESSION['branch_id'];

$date_today=date('Y-m-d');
$date_startmonth=date('Y-m-01');
$date_startyear=date('Y-01-01');

?>
<!-- ==========================CONTENT STARTS HERE ========================== -->
<!-- MAIN PANEL -->
<div id="main" role="main">

	
<?php $breadcrumbs["Reports"] = "";
include("inc/ribbon.php");
 
?>
<script src="https://www.amcharts.com/lib/4/core.js"></script>
<script src="https://www.amcharts.com/lib/4/charts.js"></script>
<script src="https://www.amcharts.com/lib/4/themes/dataviz.js"></script>
<script src="https://www.amcharts.com/lib/4/themes/kelly.js"></script>
<script src="https://www.amcharts.com/lib/4/themes/animated.js"></script>
<script src="https://www.amcharts.com/lib/4/themes/material.js"></script>

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
				<span class="small_icon"><i class="fa fa-fire"></i>	</span>	
				<h2>Employee Performance</h2>
			</header>

			<!-- widget div-->
			<div>		


				<!-- widget content -->
				<div class="widget-body no-padding">
	<br>

		<div class="tab-content" >
<style>
#chartdiv_todaysales {
  width: 100%;
  height: 350px;
}
#chartdiv_monthsales {
  width: 100%;
  height: 350px;
}
#chartdiv_yearsales {
  width: 100%;
  height: 350px;
}
</style>

<table style="width:100%;">
	<tr>
    	<td style="width:30%;"><h3><i class="fa fa-signal"></i> Today Sales</h3><br /><div id="chartdiv_todaysales"></div></td>
        <td style="width:5%;">&nbsp;</td>
        <td style="width:30%;"><h3><i class="fa fa-signal"></i> This Month Sales</h3><br /><div id="chartdiv_monthsales"></div></td>
        <td style="width:5%;">&nbsp;</td>
        <td style="width:30%;"><h3><i class="fa fa-signal"></i> This Year Sales</h3><br /><div id="chartdiv_yearsales"></div></td>
    </tr>
</table>
  
<br />
<br />

        
					<table id="datatable_fixed_column" class="table table-striped table-bordered" width="100%">
				 <thead style="font-size: 16px;
    font-weight: bold;">	
							<tr>
								<th>Employee</th>
								<th>Sales Target From</th>
								<th>Sales Target To</th>
								<th>Sales</th>
								<th>Result</th>
							</tr>
						</thead>
						<tbody>
							
<?php $empArray=get_EmployeesBranch();
 foreach ($empArray as $key => $empRow) { 
		
				$targetlight_bg='grey';
				$u_id_row=$empRow['u_id'];
				$sales_target=$sales_percentage=$total_sales_of_month=0;
				$TargetQ="SELECT ifnull(sum(s_NetAmount),0) as total_sales_of_month
						FROM cust_sale
						WHERE branch_id=$branch_id AND u_id=$u_id_row";
				$TargetQr=mysqli_query($con,$TargetQ);
				$TargetQrow=mysqli_fetch_assoc($TargetQr);
				
				
				$from_amount=$empRow['u_PerformanceAmountFrom'];
				$to_amount=$empRow['u_PerformanceAmountTo'];
				$total_sales_of_month=$TargetQrow['total_sales_of_month'];

				if($total_sales_of_month!=0 && $from_amount!=0 && $to_amount!=0)
				{
					if($total_sales_of_month>=$from_amount && $total_sales_of_month<$to_amount ) {$targetlight_bg='yellow';} else if($total_sales_of_month>=$to_amount) { $targetlight_bg='green';}
				}
?>		
				 			
						<?php echo "<tr id='row".$empRow['u_id']."'>";?>
                                        <td><?php echo $empRow['u_FullName'];?> </td>
                                        <td><?php echo $empRow['u_PerformanceAmountFrom'];?> </td>
                                        <td><?php echo $empRow['u_PerformanceAmountTo'];?> </td>
                                        <td><?=$total_sales_of_month;?></td>
                                        <td><p style=" margin: 0;width: 50px; height: 50px; background: <?=$targetlight_bg;?>; display: block; border-radius: 5px;">&nbsp;</p></td>
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

<script type="text/javascript">

am4core.ready(function() {

// Themes begin
am4core.useTheme(am4themes_dataviz);
am4core.useTheme(am4themes_animated);
// Themes end

// Create chart instance
var chart = am4core.create("chartdiv_todaysales", am4charts.XYChart);

// Add data
chart.data = [

<?php
$Q="SELECT u_user.u_FullName as username, ( SELECT ifnull(SUM(s_NetAmount),0) as s_NetAmount from cust_sale WHERE cust_sale.u_id=u_user.u_id AND cust_sale.s_Date='$date_today') as s_NetAmount
FROM u_user
WHERE u_user.branch_id=$branch_id
ORDER BY u_user.u_FullName";
$Qr=mysqli_query($con, $Q);
$searil_rows=mysqli_num_rows($Qr);
$searil=1;
$last_coma=',';
while($row=mysqli_fetch_assoc($Qr))
{
	//echo '<span>'.($searil_rows-$searil).'</span><span>'.$searil.'</span><br>';
	if(($searil_rows-$searil)==0) {$last_coma='';}
	$sales=number_format($row['s_NetAmount'],0);
	$sales=str_replace(",","",$row['s_NetAmount']);
	echo "{'country': '".$row['username']."' , 'visits':" .$sales."}".$last_coma;
	$searil++;
}
?>

];

// Create axes

var categoryAxis = chart.xAxes.push(new am4charts.CategoryAxis());
categoryAxis.dataFields.category = "country";
categoryAxis.renderer.grid.template.location = 0;
categoryAxis.renderer.minGridDistance = 30;

categoryAxis.renderer.labels.template.adapter.add("dy", function(dy, target) {
  if (target.dataItem && target.dataItem.index & 2 == 2) {
    return dy + 25;
  }
  return dy;
});

var valueAxis = chart.yAxes.push(new am4charts.ValueAxis());

// Create series
var series = chart.series.push(new am4charts.ColumnSeries());
series.dataFields.valueY = "visits";
series.dataFields.categoryX = "country";
series.name = "Visits";
series.columns.template.tooltipText = "{categoryX}: [bold]{valueY}[/]";
series.columns.template.fillOpacity = .8;

var columnTemplate = series.columns.template;
columnTemplate.strokeWidth = 2;
columnTemplate.strokeOpacity = 1;

}); // end am4core.ready()
</script>



<script>
am4core.ready(function() {

// Themes begin
am4core.useTheme(am4themes_kelly);
am4core.useTheme(am4themes_animated);
// Themes end

// Create chart instance
var chart = am4core.create("chartdiv_monthsales", am4charts.XYChart);

// Add data
chart.data = [

<?php
$Q="SELECT u_user.u_FullName as username, ( SELECT ifnull(SUM(s_NetAmount),0) as s_NetAmount from cust_sale WHERE cust_sale.u_id=u_user.u_id AND cust_sale.s_Date>='$date_startmonth' AND cust_sale.s_Date<='$date_today') as s_NetAmount
FROM u_user
WHERE u_user.branch_id=$branch_id
ORDER BY u_user.u_FullName";
$Qr=mysqli_query($con, $Q);
$searil_rows=mysqli_num_rows($Qr);
$searil=1;
$last_coma=',';
while($row=mysqli_fetch_assoc($Qr))
{
	//echo '<span>'.($searil_rows-$searil).'</span><span>'.$searil.'</span><br>';
	if(($searil_rows-$searil)==0) {$last_coma='';}
	$sales=number_format($row['s_NetAmount'],0);
	$sales=str_replace(",","",$row['s_NetAmount']);
	echo "{'country': '".$row['username']."' , 'visits':" .$sales."}".$last_coma;
	$searil++;
}
?>


];

// Create axes

var categoryAxis = chart.xAxes.push(new am4charts.CategoryAxis());
categoryAxis.dataFields.category = "country";
categoryAxis.renderer.grid.template.location = 0;
categoryAxis.renderer.minGridDistance = 30;

categoryAxis.renderer.labels.template.adapter.add("dy", function(dy, target) {
  if (target.dataItem && target.dataItem.index & 2 == 2) {
    return dy + 25;
  }
  return dy;
});

var valueAxis = chart.yAxes.push(new am4charts.ValueAxis());

// Create series
var series = chart.series.push(new am4charts.ColumnSeries());
series.dataFields.valueY = "visits";
series.dataFields.categoryX = "country";
series.name = "Visits";
series.columns.template.tooltipText = "{categoryX}: [bold]{valueY}[/]";
series.columns.template.fillOpacity = .8;

var columnTemplate = series.columns.template;
columnTemplate.strokeWidth = 2;
columnTemplate.strokeOpacity = 1;

}); // end am4core.ready()
</script>



<script>
am4core.ready(function() {

// Themes begin
am4core.useTheme(am4themes_material);
am4core.useTheme(am4themes_animated);
// Themes end

// Create chart instance
var chart = am4core.create("chartdiv_yearsales", am4charts.XYChart);

// Add data
chart.data = [

<?php
$Q="SELECT u_user.u_FullName as username, ( SELECT ifnull(SUM(s_NetAmount),0) as s_NetAmount from cust_sale WHERE cust_sale.u_id=u_user.u_id AND cust_sale.s_Date>='$date_startyear' AND cust_sale.s_Date<='$date_today') as s_NetAmount
FROM u_user
WHERE u_user.branch_id=$branch_id
ORDER BY u_user.u_FullName";
$Qr=mysqli_query($con, $Q);
$searil_rows=mysqli_num_rows($Qr);
$searil=1;
$last_coma=',';
while($row=mysqli_fetch_assoc($Qr))
{
	//echo '<span>'.($searil_rows-$searil).'</span><span>'.$searil.'</span><br>';
	if(($searil_rows-$searil)==0) {$last_coma='';}
	$sales=number_format($row['s_NetAmount'],0);
	$sales=str_replace(",","",$row['s_NetAmount']);
	echo "{'country': '".$row['username']."' , 'visits':" .$sales."}".$last_coma;
	$searil++;
}
?>

];

// Create axes

var categoryAxis = chart.xAxes.push(new am4charts.CategoryAxis());
categoryAxis.dataFields.category = "country";
categoryAxis.renderer.grid.template.location = 0;
categoryAxis.renderer.minGridDistance = 30;

categoryAxis.renderer.labels.template.adapter.add("dy", function(dy, target) {
  if (target.dataItem && target.dataItem.index & 2 == 2) {
    return dy + 25;
  }
  return dy;
});

var valueAxis = chart.yAxes.push(new am4charts.ValueAxis());

// Create series
var series = chart.series.push(new am4charts.ColumnSeries());
series.dataFields.valueY = "visits";
series.dataFields.categoryX = "country";
series.name = "Visits";
series.columns.template.tooltipText = "{categoryX}: [bold]{valueY}[/]";
series.columns.template.fillOpacity = .8;

var columnTemplate = series.columns.template;
columnTemplate.strokeWidth = 2;
columnTemplate.strokeOpacity = 1;

}); // end am4core.ready()
</script>