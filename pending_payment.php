<?php include('sessionCheck.php');
include('connection.php');
include('functions.php');
require_once ("inc/init.php");
require_once ("inc/config.ui.php");
$page_title = "Pending Payment";
include ("inc/header.php");
//$page_nav["Settings"]["sub"]["Suppliers"]["active"] = true;
include ("inc/nav.php");
 $branch_id=$_SESSION['branch_id'];
?>
<link href="https://cdn.datatables.net/1.10.20/css/jquery.dataTables.min.css" type="text/css" rel="stylesheet">
<link href="https://cdn.datatables.net/buttons/1.6.1/css/buttons.dataTables.min.css" type="text/css" rel="stylesheet">
<!-- ==========================CONTENT STARTS HERE ========================== -->
<!-- MAIN PANEL -->
<div id="main" role="main">

	
<?php $breadcrumbs["Reports"] = "";
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
				<span class="small_icon"><i class="fa fa-money"></i>	</span>	
				<h2>Pending Payments</h2>
			</header>

			<!-- widget div-->
			<div>		


				<!-- widget content -->
				<div class="widget-body no-padding">
	<br>

		<div class="tab-content" >



<?php
$branchQ="SELECT branch_id, branch_Name, branch_Address, branch_Phone1, branch_Phone2, branch_Email,branch_Web FROM adm_branch WHERE 1 AND branch_id=$branch_id";
$branchRow=mysqli_fetch_assoc(mysqli_query($con,$branchQ));
 
?>
<table id="example" class="display" style="width:100%">
		 			
                    <thead style="font-size: 16px;
    font-weight: bold;">
                    <tr >
		 	
						<td width="400px" style="text-align: left; ">Customer Name</td>
						<td width="200px" style="text-align: center;">Phone</td>
                        <td width="200px" style="text-align: center;">Email</td>
                        <td width="200px" style="text-align: center;">Balance</td>
					</tr>
                    <thead>
                    <tbody>							
<?php
$clientBQ="SELECT
							C.client_id, C.client_Name, C.client_Phone, C.client_Email,
							(SELECT ifnull(SUM(CS.s_NetAmount),0) FROM cust_sale AS CS WHERE C.client_id=CS.client_id) AS s_NetAmount,
							(SELECT ifnull(SUM(SR.sr_NetAmount),0) FROM cust_salereturn AS SR WHERE C.client_id=SR.client_id) AS sr_NetAmount,
							(SELECT ifnull(SUM(SP1.sp_Amount),0) FROM adm_sale_payment AS SP1 WHERE C.client_id=SP1.client_id AND SP1.sp_Type='S') AS sale_Payment,
							(SELECT ifnull(SUM(SP2.sp_Amount),0) FROM adm_sale_payment AS SP2 WHERE C.client_id=SP2.client_id AND SP2.sp_Type='SR') AS sale_ReturnPayment
						FROM adm_client AS C
						WHERE 1 AND C.branch_id=$branch_id
						ORDER BY C.client_id";
	$clientBQR=mysqli_query($con,$clientBQ);
	$count=$total_client_balance=0;
	while($row = mysqli_fetch_assoc($clientBQR))
	{
		$count++;
		$balance=0;
		$balance=$row['s_NetAmount']-$row['sr_NetAmount']-$row['sale_Payment']+$row['sale_ReturnPayment'];
		if($balance>0)
		{
?>
					<tr>
				
						<td style="text-align: left; font-weight: bold;font-size: 14px;"><?php echo $row['client_Name'];?></td>
						<td style="text-align: center;"><?php echo $row['client_Phone'];?></td>
						<td style="text-align: center;"><?php echo $row['client_Email'];?></td>
						<td style="text-align: center; font-weight:bold;"><?php echo number_format($balance,0);?></td>
					</tr>
<?php
		}
$total_client_balance+=$balance;
} 
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
<script src="
<?php echo ASSETS_URL;
 
?>/js/plugin/datatables/jquery.dataTables.min.js"></script>
<script src="
<?php echo ASSETS_URL;
 
?>/js/plugin/datatables/dataTables.colVis.min.js"></script>
<script src="
<?php echo ASSETS_URL;
 
?>/js/plugin/datatables/dataTables.tableTools.min.js"></script>
<script src="
<?php echo ASSETS_URL;
 
?>/js/plugin/datatables/dataTables.bootstrap.min.js"></script>
<script src="
<?php echo ASSETS_URL;
 
?>/js/plugin/datatable-responsive/datatables.responsive.min.js"></script>

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


<?php include ("inc/google-analytics.php");
 
?>

<script type="text/javascript">
 $("#discount").keypress(function(e){
 if(e.keyCode==13){
 addToTable();

 }
});

var count=1;

	function save_form(){
		$("#supplier_form").submit();

	}

	function addToTable()
	{
		var item_text=$("#item option:selected").text();

		var brand_id=$("#item option:selected").val();

		var discount_percentage =$("#discount").val();

		if(discount_percentage=='' || discount_percentage==undefined)
		{
			alert("Discount Percentage Should not be empty");

		}
		else
		{
			var newRow=$("#copyRow").clone().show();

			$(newRow).find('.item').html(item_text);

			$(newRow).find('.brand_id').val(brand_id);

			$(newRow).find('.ass_DiscountPercentage').val(discount_percentage);

			$(newRow).attr('id','row'+count);

			$("#copyTable").append(newRow);

			count++;

			$('#discount').val('');

		}

	}

function removeTr(e)
{
	$(e).closest('tr').remove();

}	
</script>

<?php include('include_datatables_files_js.php'); ?>
<script>

$(document).ready(function() {
    $('#example').DataTable( {
        dom: 'Bfrtip',
        buttons: [
            'copyHtml5',
            'excelHtml5',
            'csvHtml5',
            'pdfHtml5'
        ]
    } );
} );

</script>