<?php include('sessionCheck.php');
include('connection.php');
include('functions.php');
require_once ("inc/init.php");
require_once ("inc/config.ui.php");
$page_title = "Daily Sales";
include ("inc/header.php");
//$page_nav["Settings"]["sub"]["Suppliers"]["active"] = true;
include ("inc/nav.php");
$u_id=$_SESSION['u_id'];
$branch_id=$_SESSION['branch_id'];

$date_today='';
$from_date='';
$to_date='';



$date_today_purchase='';
$from_date_purchase='';
$to_date_purchase='';


$date_today_purchase_payment='';
$from_date_purchase_payment='';
$to_date_purchase_payment='';


$date_today_expenses='';
$from_date_expenses='';
$to_date_expenses='';



if(isset($_GET['from_date']) && !empty($_GET['from_date']))
{
	$from_date_post=validate_date_sql($_GET['from_date']);
	$from_date="AND s_Date>='$from_date_post'";
	$from_date_purchase="AND p_Date>='$from_date_post'";
	$from_date_purchase_payment="AND pp_Date>='$from_date_post'";
	$from_date_expenses="AND expense_date>='$from_date_post'";
}
if(isset($_GET['to_date']) && !empty($_GET['to_date']))
{
	$to_date_post=validate_date_sql($_GET['to_date']);
	$to_date="AND s_Date<='$to_date_post'";
	$to_date_purchase="AND p_Date<='$to_date_post'";
	$to_date_purchase_payment="AND pp_Date<='$to_date_post'";
	$to_date_expenses="AND expense_date<='$to_date_post'";
}
if(empty($from_date) && empty($to_date))
{
	$date_today_post=date('Y-m-d');
	$date_today="AND s_Date='$date_today_post'";
	$date_today_purchase="AND p_Date='$date_today_post'";
	$date_today_purchase_payment="AND pp_Date='$date_today_post'";
	$date_today_expenses="AND expense_date='$date_today_post'";
}
?>
<!-- ==========================CONTENT STARTS HERE ========================== -->
<!-- MAIN PANEL -->
<div id="main" role="main">

	
<?php $breadcrumbs["Reports"] = "";
include("inc/ribbon.php");
 
?>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
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
				<span class="small_icon"><i class="fa fa-signal"></i>	</span>	
				<h2>Daily Sales</h2>
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
<div style=" background:#f1f1f1; padding:20px;">
<form method="get" action="">	
			<div class="row">
				<div class="col col-lg-2" style="text-align:right;     line-height: 30px;">
					 Date From :
				</div>
				<div class="col col-lg-3">
                    <input type="text" name="from_date" value="<?php echo date('d-m-Y');?>" placeholder="Select a date" class="form-control datepicker" data-dateformat="dd-mm-yy" style="border-radius: 6px !important;">
				</div>
				<div class="col col-lg-2" style="text-align:right;     line-height: 30px;">
					Date To :
				</div>
				<div class="col col-lg-3">
                    <input type="text" name="to_date" value="<?php echo date('d-m-Y');?>" placeholder="Select a date" class="form-control datepicker" data-dateformat="dd-mm-yy" style="border-radius: 6px !important;">
				</div>
				<div class="col col-lg-1">	
					<input type="submit" name="submit" value="Search" class="btn btn-primary">
				</div>
			</div><!--End of row-->
			</form>
</div>

<table style="width:100%;">
	<tr>
    	<td style="width:50%;" ><h3 style="text-align:center;"><i class="fa fa-signal"></i> Sales History</h3></td>
        <td style="width:50%;" ><h3 style="text-align:center;"><i class="fa fa-dashboard"></i> Sales Stats</h3></td>
    </tr>
    <tr>
    	<td>
        		
                <table class="table table-bordered table-hover table-condensed" style="width:100%;">
                	<tr style="background-color: #009dff; color: #fff;">
                    	<th style="text-align:center;">Date & Time</th>
                        <th style="text-align:center;">Product Name</th>
                        <th style="text-align:center;">Quantity</th>
                        <th style="text-align:center;">Rate</th>
                        <th style="text-align:center;">Value</th>
                        <th style="text-align:center;">Employee</th>
                    </tr>
                    <?php
$Q="SELECT
	s_CreatedOn, item_Name, cust_sale_detail.item_IMEI, item_Qty, cust_sale_detail.item_SalePrice, item_NetPrice, u_FullName 
FROM `cust_sale_detail`
inner join cust_sale on cust_sale.s_id=cust_sale_detail.s_id
INNER JOIN adm_item ON adm_item.item_id=cust_sale_detail.item_id
Left outer join u_user on u_user.u_id=cust_sale.u_id

WHERE 1 AND cust_sale.branch_id=$branch_id $from_date $to_date $date_today
ORDER BY s_CreatedOn DESC
";
//echo '<pre>'.$Q.'</pre>';
$Qr=mysqli_query($con, $Q);
$Qrows=mysqli_num_rows($Qr);
if($Qrows>0)
{
	while($row=mysqli_fetch_assoc($Qr))
	{
	?>		
		<?php echo "<tr>"; ?>
			<td style="text-align:center;"> <?php echo $row['s_CreatedOn']; ?> </td>
			<td> <?php echo $row['item_Name'].' / '.$row['item_IMEI']; ?> </td>
			<td style="text-align:center;"> <?php echo $row['item_Qty']; ?> </td>
			<td style="text-align:right;"> <?php echo $row['item_SalePrice']; ?> </td>
			<td style="text-align:right;"> <?php echo $currency_symbol.$row['item_NetPrice']; ?> </td>
			<td style="text-align:center;"> <?php echo $row['u_FullName']; ?> </td>
		</tr>
	
	<?php  } 
}
else
{
	echo '<tr><td colspan="6" style="text-align: center;
    font-weight: bold;
    color: red;">No Record Found</td></tr>';
}
   ?>
	            </table>
            
            	
        </td>
        <td style="vertical-align: top;">
        	<div id="piechart"></div>
        </td>
    </tr>
</table>


<hr style="background-color: #009dff; height:2px;">
<br>





<table style="width:100%;">
	<tr>
    	<td style="width:50%;" ><h3 style="text-align:center;"><i class="fa fa-signal"></i> Purchase History</h3></td>
        <td style="width:50%;" ><h3 style="text-align:center;"><i class="fa fa-dashboard"></i> Purchase Stats</h3></td>
    </tr>
    <tr>
    	<td>
        		
                <table class="table table-bordered table-hover table-condensed" style="width:100%;">
                	<tr style="background-color: #009dff; color: #fff;">
                    	<th style="text-align:center;">Date & Time</th>
                        <th style="text-align:center;">Product Name</th>
                        <th style="text-align:center;">Quantity</th>
                        <th style="text-align:center;">Rate</th>
                        <th style="text-align:center;">Value</th>
                        <th style="text-align:center;">Employee</th>
                    </tr>
                    <?php
$Q="SELECT
	p_CreatedOn, item_Name, adm_purchase_detail.item_IMEI, item_Qty, adm_purchase_detail.item_Rate, adm_purchase_detail.item_NetAmount, u_FullName 
FROM `adm_purchase_detail`
inner join adm_purchase on adm_purchase.p_id=adm_purchase_detail.p_id
INNER JOIN adm_item ON adm_item.item_id=adm_purchase_detail.item_id
Left outer join u_user on u_user.u_id=adm_purchase.u_id

WHERE 1 AND adm_purchase.branch_id=$branch_id $from_date_purchase $to_date_purchase $date_today_purchase
ORDER BY p_CreatedOn DESC
";
//echo '<pre>'.$Q.'</pre>';
$Qr=mysqli_query($con, $Q);
$Qrows=mysqli_num_rows($Qr);
if($Qrows>0)
{
	while($row=mysqli_fetch_assoc($Qr))
	{
	?>		
		<?php echo "<tr>"; ?>
			<td style="text-align:center;"> <?php echo $row['p_CreatedOn']; ?> </td>
			<td> <?php echo $row['item_Name'].' / '.$row['item_IMEI']; ?> </td>
			<td style="text-align:center;"> <?php echo $row['item_Qty']; ?> </td>
			<td style="text-align:right;"> <?php echo $row['item_Rate']; ?> </td>
			<td style="text-align:right;"> <?php echo $currency_symbol.$row['item_NetAmount']; ?> </td>
			<td style="text-align:center;"> <?php echo $row['u_FullName']; ?> </td>
		</tr>
	
	<?php  } 
}
else
{
	echo '<tr><td colspan="6" style="text-align: center;
    font-weight: bold;
    color: red;">No Record Found</td></tr>';
}
   ?>
	            </table>
            
            	
        </td>
        <td style="vertical-align: top;">
        	<div id="piechart_purchase"></div>
        </td>
    </tr>
</table>








<hr style="background-color: #009dff; height:2px;">
<br>
<table class="table table-bordered table-hover table-condensed" style="width:100%;">
	<tr>
    	<td colspan="4" ><h3 style="text-align:center;"><i class="fa fa-signal"></i> Vendor Payment History</h3></td>
    </tr>

	<tr style="background-color: #009dff; color: #fff;">
    	<th style="text-align:center;">Date & Time</th>
        <th style="text-align:center;">Vendor Name</th>
        <th style="text-align:center;">Amount</th>
        <th style="text-align:center;">Remarks</th>
    </tr>
                    <?php
$Q="SELECT pp_Amount, pp_Date, pp_Description, pp_CreatedOn, sup_Name
FROM adm_purchase_payment
LEFT JOIN adm_supplier ON adm_supplier.sup_id=adm_purchase_payment.sup_id

WHERE 1 AND adm_purchase_payment.branch_id=$branch_id $from_date_purchase_payment $to_date_purchase_payment $date_today_purchase_payment
ORDER BY pp_CreatedOn DESC
";
//echo '<pre>'.$Q.'</pre>';
$Qr=mysqli_query($con, $Q);
$Qrows=mysqli_num_rows($Qr);
if($Qrows>0)
{
	while($row=mysqli_fetch_assoc($Qr))
	{
	?>		
		<?php echo "<tr>"; ?>
			<td style="text-align:center;"> <?php echo $row['pp_CreatedOn']; ?> </td>
			<td> <?php echo $row['sup_Name'];?> </td>
			<td style="text-align:center;"> <?php echo $row['pp_Amount']; ?> </td>
			<td style="text-align:right;"> <?php echo $row['pp_Description']; ?> </td>
		</tr>
	
	<?php  } 
}
else
{
	echo '<tr><td colspan="6" style="text-align: center;
    font-weight: bold;
    color: red;">No Record Found</td></tr>';
}
   ?>
</table>


<hr style="background-color: #009dff; height:2px;">
<br>
<table class="table table-bordered table-hover table-condensed" style="width:100%;">
	<tr>
    	<td colspan="4" ><h3 style="text-align:center;"><i class="fa fa-signal"></i> Expenses History</h3></td>
    </tr>

	<tr style="background-color: #009dff; color: #fff;">
    	<th style="text-align:center;">Date & Time</th>
        <th style="text-align:center;">Payee Name</th>
        <th style="text-align:center;">Amount</th>
        <th style="text-align:center;">Remarks</th>
    </tr>
                    <?php
$Q="SELECT expense_amount, expense_notes, expense_createdat, payee_name
FROM adm_expenses
LEFT JOIN adm_payee ON adm_payee.payee_id=adm_expenses.payee_id

WHERE 1 AND adm_expenses.branch_id=$branch_id $from_date_expenses $to_date_expenses $date_today_expenses
ORDER BY expense_createdat DESC
";
//echo '<pre>'.$Q.'</pre>';
$Qr=mysqli_query($con, $Q);
$Qrows=mysqli_num_rows($Qr);
if($Qrows>0)
{
	while($row=mysqli_fetch_assoc($Qr))
	{
	?>		
		<?php echo "<tr>"; ?>
			<td style="text-align:center;"> <?php echo $row['expense_createdat']; ?> </td>
			<td> <?php echo $row['payee_name'];?> </td>
			<td style="text-align:center;"> <?php echo $row['expense_amount']; ?> </td>
			<td style="text-align:right;"> <?php echo $row['expense_notes']; ?> </td>
		</tr>
	
	<?php  } 
}
else
{
	echo '<tr><td colspan="6" style="text-align: center;
    font-weight: bold;
    color: red;">No Record Found</td></tr>';
}
   ?>
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
// Load google charts
google.charts.load('current', {'packages':['corechart']});
google.charts.setOnLoadCallback(drawChart);

// Draw the chart and set the chart values
function drawChart() {
  var data = google.visualization.arrayToDataTable([
  ['Task', 'Hours per Day'],
 <?php
$Q="SELECT u_FullName, sum(item_NetPrice) as s_NetAmount
FROM u_user
LEFT OUTER JOIN cust_sale ON cust_sale.u_id=u_user.u_id
LEFT OUTER JOIN cust_sale_detail on cust_sale.s_id=cust_sale_detail.s_id

WHERE 1 AND cust_sale.branch_id=$branch_id $from_date $to_date $date_today
GROUP BY u_user.u_id";
$Qr=mysqli_query($con, $Q);
$searil_rows=mysqli_num_rows($Qr);
$searil=1;
$last_coma=',';
while($row=mysqli_fetch_assoc($Qr))
{
	//echo '<span>'.($searil_rows-$searil).'</span><span>'.$searil.'</span><br>';
	if(($searil_rows-$searil)==0) {$last_coma='';}
	echo "['".$row['u_FullName']."', ".$row['s_NetAmount']."]".$last_coma."";
	$searil++;
}
?>

]);

  // Optional; add a title and set the width and height of the chart
  var options = {'title':'Sales Stats', 'width':650, 'height':300};

  // Display the chart inside the <div> element with id="piechart"
  var chart = new google.visualization.PieChart(document.getElementById('piechart'));
  chart.draw(data, options);
}
</script>
<!-- DYGRAPH -->








<script type="text/javascript">
// Load google charts
google.charts.load('current', {'packages':['corechart']});
google.charts.setOnLoadCallback(drawChart);

// Draw the chart and set the chart values
function drawChart() {
  var data = google.visualization.arrayToDataTable([
  ['Task', 'Hours per Day'],
 <?php
$Q="SELECT u_FullName, sum(item_NetAmount) as s_NetAmount
FROM u_user
LEFT OUTER JOIN adm_purchase ON adm_purchase.u_id=u_user.u_id
LEFT OUTER JOIN adm_purchase_detail on adm_purchase.p_id=adm_purchase_detail.p_id

WHERE 1 AND adm_purchase.branch_id=$branch_id $from_date_purchase $to_date_purchase $date_today_purchase
GROUP BY u_user.u_id";
$Qr=mysqli_query($con, $Q);
$searil_rows=mysqli_num_rows($Qr);
$searil=1;
$last_coma=',';
while($row=mysqli_fetch_assoc($Qr))
{
	//echo '<span>'.($searil_rows-$searil).'</span><span>'.$searil.'</span><br>';
	if(($searil_rows-$searil)==0) {$last_coma='';}
	echo "['".$row['u_FullName']."', ".$row['s_NetAmount']."]".$last_coma."";
	$searil++;
}
?>

]);

  // Optional; add a title and set the width and height of the chart
  var options = {'title':'Purchase Stats', 'width':650, 'height':300};

  // Display the chart inside the <div> element with id="piechart"
  var chart = new google.visualization.PieChart(document.getElementById('piechart_purchase'));
  chart.draw(data, options);
}
</script>









<script src="<?php echo ASSETS_URL; ?>/js/plugin/chartjs/chart.min.js"></script>