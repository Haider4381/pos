<?php
include('sessionCheck.php');
include('connection.php');
include('functions.php');
require_once ("inc/init.php");
require_once ("inc/config.ui.php");
$page_title = "Monthly Profit Report";
include ("inc/header.php");
//$page_nav["Settings"]["sub"]["Brands"]["active"] = true;
include ("inc/nav.php");
$branch_id=$_SESSION['branch_id'];
?>
<!-- ==========================CONTENT STARTS HERE ========================== -->
<!-- MAIN PANEL -->
<div id="main" role="main">

	<?php
		//configure ribbon (breadcrumbs) array("name"=>"url"), leave url empty if no url
		//$breadcrumbs["New Crumb"] => "http://url.com"
		$breadcrumbs["Monthly Profit Report"] = "";
		include("inc/ribbon.php");
	?>
<style>
     .form-control {
    border-radius: 5px !important;
    box-shadow: none!important;
    -webkit-box-shadow: none!important;
    -moz-box-shadow: none!important;
    font-size: 12px;

     }
     .select2-container .select2-choice {
   
    border-radius: 5px;
   
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
				<span class="small_icon"><i class="fa fa-tags"></i>	</span>	
				<h2>Monthly Profit Report</h2>
			</header>

			<!-- widget div-->
			<div>
<?php

$query ="SELECT date_format(sd_Date,'%Y') as date_year, date_format(sd_Date,'%m-%Y') as month_year, sum(item_NetPrice) as total_sales, sum(item_Qty*item_CostPrice) as total_cost, sum(item_Qty) as total_qty
FROM cust_sale_detail
INNER JOIN cust_sale ON cust_sale.s_id=cust_sale_detail.s_id
WHERE cust_sale.branch_id=$branch_id
GROUP BY date_year, month_year
ORDER BY  date_year,month_year";
					//echo '<pre>'.$query.'</pre>';
					$results = mysqli_query($con,$query);
					if(mysqli_num_rows($results)<1)
					{
						echo "There is no record found";
						die();
					}
					$year1='my year';
					
					while($row=mysqli_fetch_assoc($results))
					{
						$total_expenses=0;
						$total_sales=$row['total_sales'];
						$total_cost=$row['total_cost'];
						$total_qty=$row['total_qty'];
						$total_profit=($total_sales-$total_cost);
						$total_profit_per_item=number_format($total_profit/$total_qty,2);
						$month_year=date('M-Y',strtotime('01-'.$row['month_year']));
						$month_year_for_expense=$row['month_year'];
						$year=$row['date_year'];
						
						//if($year!=$year1)
						//{		
							//echo '<br style="clear:both;">';	
						//}
$expense_Q="SELECT expense_amount, expense_month
FROM 
(
SELECT SUM(expense_amount) as expense_amount, date_format(expense_date,'%m-%Y') as expense_month
FROM adm_expenses
WHERE branch_id=$branch_id
GROUP BY expense_month
) as abc
WHERE expense_month='$month_year_for_expense'";
$total_expensesQ=mysqli_query($con, $expense_Q);
$expense_Row=mysqli_fetch_assoc($total_expensesQ);
$total_expenses=$expense_Row['expense_amount'];


$salereturn_Q="SELECT sum(sr_NetAmount) as sr_NetAmount
FROM `cust_salereturn`
WHERE s_id in (SELECT s_id from cust_sale WHERE date_format(s_Date,'%m-%Y')='$month_year_for_expense' and branch_id=$branch_id)";
$total_salereturnsQ=mysqli_query($con, $salereturn_Q);
$salereturn_Row=mysqli_fetch_assoc($total_salereturnsQ);
$total_salereturns=$salereturn_Row['sr_NetAmount'];

$total_profit=$total_profit-$total_salereturns;
						?>
                        <table class="table table-bordered table-condensed" style="width:20%; margin-left:10px; float:left;">
                        	<tr><th colspan="2" style="font-size:15px;"><strong><?=$month_year?></strong></th></tr>
                            <tr>
                            	<td>
									<strong>Total Sales:</strong> </td><td><?=$currency_symbol.$total_sales?></td>
                                </tr>
                                
                                <tr>
                            	<td>
									<strong>Total Sales Return:</strong> </td><td><?=$currency_symbol.number_format($total_salereturns,2)?></td>
                                </tr>
                                <tr>
                                <td>    
                                    <strong>Total Profit:</strong></td><td><?=$currency_symbol.number_format($total_profit,2)?></td>
                                 </tr>
                                <tr>
                                <td>   
                                    <strong>Total Items Sold:</strong></td><td><?=$total_qty?></td>
                                 </tr>
                                <tr>
                                <td>   
                                    <strong>Avg Profit Per Item:</strong></td><td><?=$currency_symbol.number_format($total_profit_per_item,2)?>
                                </td>
                            </tr>
                            <tr>
                            	<td>   
                                    <strong>Total Expenses:</strong></td><td><?=$currency_symbol.number_format($total_expenses,0)?>
                                </td>
                            </tr>
                            <tr>
                            	<td>   
                                    <strong>Profit After Expenses:</strong></td><td><?=$currency_symbol.($total_profit-$total_expenses)?>
                                </td>
                            </tr>
                        </table>
                        
                        <?php
						$year1=date('Y',strtotime('01-'.$row['month_year']));
					}

					?>


<br style=" clear:both;" />




<form id="checkout-form" class="smart-form" novalidate="novalidate" method="post" action="ProfitReportMonthly.php" target="_blank">	
			<fieldset>
				
                 
				
			</fieldset>
			<footer>
				<button type="submit" class="btn btn-primary" name="submit">Print </button>
			</footer>
		</form>


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