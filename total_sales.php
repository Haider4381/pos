<?php
include "sessionCheck.php";
include "connection.php";
//initilize the page
require_once("inc/init.php");

//require UI configuration (nav, ribbon, etc.)
require_once("inc/config.ui.php");

/*---------------- PHP Custom Scripts ---------

YOU CAN SET CONFIGURATION VARIABLES HERE BEFORE IT GOES TO NAV, RIBBON, ETC.
E.G. $page_title = "Custom Title" */

$page_title = "Total Sales";

/* ---------------- END PHP Custom Scripts ------------- */

//include header
include("inc/header.php");

//include left panel (navigation)
//follow the tree in inc/config.ui.php
$page_nav["Total Sales"]["active"] = true;
include("inc/nav.php");
?>

<script src="https://www.amcharts.com/lib/4/core.js"></script>
<script src="https://www.amcharts.com/lib/4/charts.js"></script>
<script src="https://www.amcharts.com/lib/4/themes/kelly.js"></script>
<script src="https://www.amcharts.com/lib/4/themes/animated.js"></script>

<style>
#chartdiv_allsales_average{
  width: 100%;
  height: 400px;
}

#chartdiv_last15days{
  width: 95%;
  height: 400px;
}
</style>
<!-- ==========================CONTENT STARTS HERE ========================== -->

		<!-- MAIN PANEL -->
<div id="main" role="main">
<?php
//configure ribbon (breadcrumbs) array("name"=>"url"), leave url empty if no url
//$breadcrumbs["New Crumb"] => "http://url.com"
//$breadcrumbs["Setup"] = "";
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
				<span class="small_icon"><i class="fa fa-file-text-o"></i>	</span>		
				<h2>Total Sales</h2>
			</header>

			<!-- widget div-->
			<div>		


				<!-- widget content -->
				<div class="widget-body no-padding">
<br>


<?php
$saleQuery="
SELECT SUM(s_NetAmount) AS dailyNetAmt,
(SELECT SUM(s_NetAmount) FROM cust_sale WHERE s_Date > DATE_SUB(NOW(),INTERVAL 1 WEEK) AND branch_id=$branch_id) AS weeklyNetAmt,
(SELECT SUM(s_NetAmount) FROM cust_sale WHERE s_Date >DATE_SUB(NOW(),INTERVAL 1 MONTH) AND branch_id=$branch_id) AS monthlyNetAmt,
(SELECT SUM(s_NetAmount) FROM cust_sale WHERE s_Date >DATE_SUB(NOW(),INTERVAL 1 YEAR) AND branch_id=$branch_id) AS yearlyNetAmt
FROM cust_sale 
WHERE s_Date > DATE_SUB(NOW(), INTERVAL 1 DAY) AND branch_id=$branch_id;";
//echo '<pre>'.$saleQuery.'</pre>';
$saleRow=mysqli_fetch_assoc(mysqli_query($con,$saleQuery));

$dailyNetAmt=$weeklyNetAmt=$monthlyNetAmt=$yearlyNetAmt=$dayilyPAmt=$weeklyPAmt=$montlhyPAmt=$yearlyPAmt=$dailyPayAmt=$weeklyPayAmt=$monthlyPayAmt=$yearlyPayAmt=0;
if(!empty($saleRow['dailyNetAmt'])){
	$dailyNetAmt=$saleRow['dailyNetAmt'];
}
if(!empty($saleRow['weeklyNetAmt'])){
	$weeklyNetAmt=$saleRow['weeklyNetAmt'];
}
if(!empty($saleRow['monthlyNetAmt'])){
	$monthlyNetAmt=$saleRow['monthlyNetAmt'];
}
if(!empty($saleRow['yearlyNetAmt'])){
	$yearlyNetAmt=$saleRow['yearlyNetAmt'];
}





$purchaseQuery="
SELECT SUM(p_NetAmount) AS dailyNetAmt_p,
(SELECT SUM(p_NetAmount) FROM adm_purchase WHERE p_Date > DATE_SUB(NOW(),INTERVAL 1 WEEK) AND branch_id=$branch_id) AS weeklyNetAmt_p,
(SELECT SUM(p_NetAmount) FROM adm_purchase WHERE p_Date >DATE_SUB(NOW(),INTERVAL 1 MONTH) AND branch_id=$branch_id) AS monthlyNetAmt_p,
(SELECT SUM(p_NetAmount) FROM adm_purchase WHERE p_Date >DATE_SUB(NOW(),INTERVAL 1 YEAR) AND branch_id=$branch_id) AS yearlyNetAmt_p
FROM adm_purchase 
WHERE p_Date > DATE_SUB(NOW(), INTERVAL 1 DAY) AND branch_id=$branch_id;";
//echo '<pre>'.$purchaseQuery.'</pre>';
$purchaseRow=mysqli_fetch_assoc(mysqli_query($con,$purchaseQuery));

$dailyNetAmt_p=$weeklyNetAmt_p=$monthlyNetAmt_p=$yearlyNetAmt_p=0;
if(!empty($purchaseRow['dailyNetAmt_p'])){
	$dailyNetAmt_p=$purchaseRow['dailyNetAmt_p'];
}
if(!empty($purchaseRow['weeklyNetAmt_p'])){
	$weeklyNetAmt_p=$purchaseRow['weeklyNetAmt_p'];
}
if(!empty($purchaseRow['monthlyNetAmt_p'])){
	$monthlyNetAmt_p=$purchaseRow['monthlyNetAmt_p'];
}
if(!empty($purchaseRow['yearlyNetAmt_p'])){
	$yearlyNetAmt_p=$purchaseRow['yearlyNetAmt_p'];
}





$purchasepaymentQuery="
SELECT SUM(pp_Amount) AS dailyNetAmt_p_pay,
(SELECT SUM(pp_Amount) FROM adm_purchase_payment WHERE pp_Date > DATE_SUB(NOW(),INTERVAL 1 WEEK) AND branch_id=$branch_id) AS weeklyNetAmt_p_pay,
(SELECT SUM(pp_Amount) FROM adm_purchase_payment WHERE pp_Date >DATE_SUB(NOW(),INTERVAL 1 MONTH) AND branch_id=$branch_id) AS monthlyNetAmt_p_pay,
(SELECT SUM(pp_Amount) FROM adm_purchase_payment WHERE pp_Date >DATE_SUB(NOW(),INTERVAL 1 YEAR) AND branch_id=$branch_id) AS yearlyNetAmt_p_pay
FROM adm_purchase_payment 
WHERE pp_Date > DATE_SUB(NOW(), INTERVAL 1 DAY) AND branch_id=$branch_id;";
//echo '<pre>'.$purchasepaymentQuery.'</pre>';
$purchasepaymentRow=mysqli_fetch_assoc(mysqli_query($con,$purchasepaymentQuery));

$dailyNetAmt_p_pay=$weeklyNetAmt_p_pay=$monthlyNetAmt_p_pay=$yearlyNetAmt_p_pay=0;
if(!empty($purchasepaymentRow['dailyNetAmt_p_pay'])){
	$dailyNetAmt_p_pay=$purchasepaymentRow['dailyNetAmt_p_pay'];
}
if(!empty($purchasepaymentRow['weeklyNetAmt_p_pay'])){
	$weeklyNetAmt_p_pay=$purchasepaymentRow['weeklyNetAmt_p_pay'];
}
if(!empty($purchasepaymentRow['monthlyNetAmt_p_pay'])){
	$monthlyNetAmt_p_pay=$purchasepaymentRow['monthlyNetAmt_p_pay'];
}
if(!empty($purchasepaymentRow['yearlyNetAmt_p_pay'])){
	$yearlyNetAmt_p_pay=$purchasepaymentRow['yearlyNetAmt_p_pay'];
}




$expQuery="
SELECT SUM(expense_amount) AS dailyNetAmt_exp,
(SELECT SUM(expense_amount) FROM adm_expenses WHERE expense_date > DATE_SUB(NOW(),INTERVAL 1 WEEK) AND branch_id=$branch_id) AS weeklyNetAmt_exp,
(SELECT SUM(expense_amount) FROM adm_expenses WHERE expense_date >DATE_SUB(NOW(),INTERVAL 1 MONTH) AND branch_id=$branch_id) AS monthlyNetAmt_exp,
(SELECT SUM(expense_amount) FROM adm_expenses WHERE expense_date >DATE_SUB(NOW(),INTERVAL 1 YEAR) AND branch_id=$branch_id) AS yearlyNetAmt_exp
FROM adm_expenses 
WHERE expense_date > DATE_SUB(NOW(), INTERVAL 1 DAY) AND branch_id=$branch_id;";
//echo '<pre>'.$expQuery.'</pre>';
$purchasepaymentRow=mysqli_fetch_assoc(mysqli_query($con,$expQuery));

$dailyNetAmt_exp=$weeklyNetAmt_exp=$monthlyNetAmt_exp=$yearlyNetAmt_exp=0;
if(!empty($purchasepaymentRow['dailyNetAmt_exp'])){
	$dailyNetAmt_exp=$purchasepaymentRow['dailyNetAmt_exp'];
}
if(!empty($purchasepaymentRow['weeklyNetAmt_exp'])){
	$weeklyNetAmt_exp=$purchasepaymentRow['weeklyNetAmt_exp'];
}
if(!empty($purchasepaymentRow['monthlyNetAmt_exp'])){
	$monthlyNetAmt_exp=$purchasepaymentRow['monthlyNetAmt_exp'];
}
if(!empty($purchasepaymentRow['yearlyNetAmt_exp'])){
	$yearlyNetAmt_exp=$purchasepaymentRow['yearlyNetAmt_exp'];
}







?>



<div class="tab-content" style="text-align:center;">
	
    <table class="dashboard_tiles table table-hovered table-stripped table-responsive table-bordered" style="font-size:14px;">
    	<tr style="background:#e8e8e8; ">
        	<th style="text-align:center;">Today Sales (<?=date('d-m-Y')?>)</th>
            <th style="text-align:center;">This Week Sales</th>
            <th style="text-align:center;">This Month Sales</th>
            <th style="text-align:center;">This Year Sales</th>
            <th style="text-align:center;">Total Sales</th>
        </tr>
        <tr>
        	<td><?=$dailyNetAmt?></td>
            <td><?=$weeklyNetAmt?></td>
            <td><?=$monthlyNetAmt?></td>
            <td><?=$yearlyNetAmt?></td>
            <td><?=$yearlyNetAmt?></td>
        </tr>
    </table>
    <br />
	<br />

	<table class="dashboard_tiles table table-hovered table-stripped table-responsive table-bordered" style="font-size:14px;">
    	<tr style="background:#e8e8e8; ">
        	<th style="text-align:center;">Today Purchase (<?=date('d-m-Y')?>)</th>
            <th style="text-align:center;">This Week Purchase</th>
            <th style="text-align:center;">This Month Purchase</th>
            <th style="text-align:center;">This Year Purchase</th>
            <th style="text-align:center;">Total Purchase</th>
        </tr>
        <tr>
        	<td><?=$dailyNetAmt_p?></td>
            <td><?=$weeklyNetAmt_p?></td>
            <td><?=$monthlyNetAmt_p?></td>
            <td><?=$yearlyNetAmt_p?></td>
            <td><?=$yearlyNetAmt_p?></td>
        </tr>
    </table>
    <br />
	<br />

	<table class="dashboard_tiles table table-hovered table-stripped table-responsive table-bordered" style="font-size:14px;">
    	<tr style="background:#e8e8e8; ">
        	<th style="text-align:center;">Today Purchase Payment (<?=date('d-m-Y')?>)</th>
            <th style="text-align:center;">This Week Purchase Payment</th>
            <th style="text-align:center;">This Month Purchase Payment</th>
            <th style="text-align:center;">This Year Purchase Payment</th>
            <th style="text-align:center;">Total Purchase Payment</th>
        </tr>
        <tr>
        	<td><?=$dailyNetAmt_p_pay?></td>
            <td><?=$weeklyNetAmt_p_pay?></td>
            <td><?=$monthlyNetAmt_p_pay?></td>
            <td><?=$yearlyNetAmt_p_pay?></td>
            <td><?=$yearlyNetAmt_p_pay?></td>
        </tr>
    </table>
    <br />
	<br />


	<table class="dashboard_tiles table table-hovered table-stripped table-responsive table-bordered" style="font-size:14px;">
    	<tr style="background:#e8e8e8; ">
        	<th style="text-align:center;">Today Expenses (<?=date('d-m-Y')?>)</th>
            <th style="text-align:center;">This Week Expenses</th>
            <th style="text-align:center;">This Month Expenses</th>
            <th style="text-align:center;">This Year Expenses</th>
            <th style="text-align:center;">Total Expenses</th>
        </tr>
        <tr>
        	<td><?=$dailyNetAmt_exp?></td>
            <td><?=$weeklyNetAmt_exp?></td>
            <td><?=$monthlyNetAmt_exp?></td>
            <td><?=$yearlyNetAmt_exp?></td>
            <td><?=$yearlyNetAmt_exp?></td>
        </tr>
    </table>
    <br />
	<br />


    
    <table class="" style="font-size:14px; width:100% !important;">
	<tr>
    	<td style="width:48%;">
        	<h2 style="background:rgb(243, 195, 0); font-size:22px; font-weight:bold;">Last 15 Days Sale</h2>
			<div id="chartdiv_last15days"></div>      
        </td>
        <td style="width:4%;"></td>
        <td style="width:48%;">
                <h2 style="background:skyblue; font-size:22px; font-weight:bold;">Sales Stats</h2>
				<div id="chartdiv_allsales_average"></div>
        </td>
    </tr>
</table>

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
<?php
	// include page footer
	include("inc/footer.php");
?>
<!-- END PAGE FOOTER -->

<?php 
	//include required scripts
	include("inc/scripts.php"); 
?>
 

<script>
am4core.ready(function() {

// Themes begin
am4core.useTheme(am4themes_animated);
// Themes end



// Create chart instance
var chart = am4core.create("chartdiv_allsales_average", am4charts.XYChart);

// Add data
chart.data = [
<?php
$Q="SELECT
	s_Date, sum(s_NetAmount) as s_NetAmount
FROM `cust_sale`
WHERE cust_sale.branch_id=$branch_id
GROUP BY s_Date
ORDER BY s_Date DESC";
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
	echo "{'date': '".$row['s_Date']."' , 'price':" .$sales."}".$last_coma;
	$searil++;
}
?>


];

// Create axes
var dateAxis = chart.xAxes.push(new am4charts.DateAxis());
dateAxis.renderer.grid.template.location = 0;
dateAxis.renderer.minGridDistance = 50;

var valueAxis = chart.yAxes.push(new am4charts.ValueAxis());
valueAxis.logarithmic = true;
valueAxis.renderer.minGridDistance = 20;

// Create series
var series = chart.series.push(new am4charts.LineSeries());
series.dataFields.valueY = "price";
series.dataFields.dateX = "date";
series.tensionX = 0.8;
series.strokeWidth = 3;

var bullet = series.bullets.push(new am4charts.CircleBullet());
bullet.circle.fill = am4core.color("#fff");
bullet.circle.strokeWidth = 3;

// Add cursor
chart.cursor = new am4charts.XYCursor();
chart.cursor.fullWidthLineX = true;
chart.cursor.xAxis = dateAxis;
chart.cursor.lineX.strokeWidth = 0;
chart.cursor.lineX.fill = am4core.color("#000");
chart.cursor.lineX.fillOpacity = 0.1;

// Add scrollbar
chart.scrollbarX = new am4core.Scrollbar();

// Add a guide
let range = valueAxis.axisRanges.create();
range.value = 90.4;
range.grid.stroke = am4core.color("#396478");
range.grid.strokeWidth = 1;
range.grid.strokeOpacity = 1;
range.grid.strokeDasharray = "3,3";
range.label.inside = true;
range.label.text = "Average";
range.label.fill = range.grid.stroke;
range.label.verticalCenter = "bottom";

}); // end am4core.ready()
</script>


<script>
am4core.ready(function() {

// Themes begin
am4core.useTheme(am4themes_kelly);
am4core.useTheme(am4themes_animated);
// Themes end

// Create chart instance
var chart = am4core.create("chartdiv_last15days", am4charts.XYChart);

// Add data
chart.data = [

<?php
$Q="SELECT
	DATE_FORMAT(s_Date,'%d-%b') as days, s_Date, sum(s_NetAmount) as s_NetAmount
FROM `cust_sale`
WHERE cust_sale.branch_id=$branch_id
GROUP  BY s_Date
ORDER BY s_Date DESC
LIMIT 15";
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
	echo "{'country': '".$row['days']."' , 'visits':" .$sales."}".$last_coma;
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