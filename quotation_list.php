<?php
include('sessionCheck.php');
include('connection.php');
include('functions.php');
require_once ("inc/init.php");
require_once ("inc/config.ui.php");
$page_title = "Quotation List";
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
textarea{
 border-radius: 5px !important;
}
.dataTables_filter .input-group-addon+.form-control {
    display: none;
}
.dataTables_filter .input-group-addon {
    display: none;
    }
.btn-sm {
    padding: 2px 4px 1px;
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
				<span class="small_icon"><i class="fa fa-file-text-o"></i>	</span>				
				<h2>Quotation List</h2>
			</header>

			<!-- widget div-->
			<div>		


				<!-- widget content -->
				<div class="widget-body no-padding">
	<br>

<div class="tab-content" >

<form method="post" action="">	
			<div class="row">
				<div class="col col-lg-2" style="text-align:right; line-height: 36px;">
					From Date
				</div>
				<div class="col col-lg-3">
                    <input type="text" name="from_date" value="<?php echo date('01-m-Y');?>" placeholder="Select a date" class="form-control datepicker" data-dateformat="dd-mm-yy">
				</div>
				<div class="col col-lg-2" style="text-align:right; line-height: 36px;">
					To Date
				</div>
				<div class="col col-lg-3">
                    <input type="text" name="to_date" value="<?php echo date('d-m-Y');?>" placeholder="Select a date" class="form-control datepicker" data-dateformat="dd-mm-yy">
				</div>
				<div class="col col-lg-1">	
					<input type="submit" name="submit" value="Search" class="btn btn-primary">
				</div>
			</div><!--End of row-->
			</form>
			<br>
<?php 
$row=array();	
$where='';
if(isset($_POST['submit']))
{

	if(!empty($_POST['from_date']))
	{
		$from_date=date("Y-m-d", strtotime($_POST['from_date']));
		$where.=" AND S.sr_Date>='".$from_date."'";
	}
	if(!empty($_POST['to_date']))
	{
		$to_date=date("Y-m-d", strtotime($_POST['to_date']));
		$where.=" AND S.sr_Date<='".$to_date."'";
	}
}


$pQ="SELECT Q.q_id, Q.q_Date, Q.q_Number, Q.client_id, Q.q_TotalAmount, Q.q_DiscountPrice, Q.q_NetAmount, Q.q_Remarks,COUNT(QD.qd_id) AS TotalItems, C.client_Name, sale.s_Number
	FROM adm_quotation AS Q
	LEFT JOIN adm_quotation_detail  AS QD ON QD.q_id=Q.q_id
	left outer join cust_sale as sale on sale.s_id=Q.q_id 
	LEFT JOIN adm_client AS C ON C.client_id=Q.client_id
	WHERE Q.branch_id=$branch_id $where
	GROUP BY Q.q_id
	";
$pRes=mysqli_query($con,$pQ);
if(mysqli_num_rows($pRes)<1)
{
	echo "<div class='alert alert-danger'>No record found. <div>";
}
else
{
	while($r=mysqli_fetch_assoc($pRes))
	{
		$row[]=$r;
	}
}

?>
		<!-- widget content -->
		<div class="widget-body no-padding">
			<table id="datatable_fixed_column" class="table table-striped table-bordered" width="100%">

		        <thead>
					<tr>
						<th class="hasinput" style="width:5%">
							<input type="text" class="form-control" placeholder="Sr No." />
						</th>
                        <th class="hasinput" style="width:10%">
							<input type="text" class="form-control" placeholder="Quotation No." />
						</th>
                        <th class="hasinput" style="width:10%;">
							<input id="" type="text" placeholder="Filter Date" class="form-control datepicker" data-dateformat="dd-mm-yy">
						</th>
						<th class="hasinput" style="width:25%">
							<input type="text" class="form-control" placeholder="Customer Name" />
						</th>
						 
							<th class="hasinput" style="width:10%">
								<input type="text" class="form-control" placeholder="Net Amount" />
							</th>
						<th class="hasinput" style="width:10%">
							<input type="text" class="form-control" placeholder="Total Items" />
						</th>
						
						<th style="width:30%;">
							
						</th>
					</tr>
		            <tr>
	                    <th style="text-align:center;">Sr No.</th>
                        <th style="text-align:center;">Quotation No.</th>
                        <th style="text-align:center;">Date & Time</th>
	                    <th style="text-align:center;">Customer Name</th>
	                    <th style="text-align:center;">Net Amount</th>
	                    <th style="text-align:center;">Total Items</th>
	                     <th style="text-align:center;">
	                    	Action
	                    </th>
		            </tr>
		        </thead>

		        <tbody>
		         <?php 
		        $i = 0;
		        foreach ($row as $key => $r) 
		        {
		            $i++;
		     	?>

		            <tr id="row<?=$r['q_id']?>">
		                <td style="text-align:center;"><?php echo $i; ?></td>
                        <td style="text-align:center;"><?php echo $r['q_Number']; ?></td>
                        <td style="text-align:center;"><?php echo sum_date_formate($r['q_Date']); ?></td>
		                <td style="text-align:center;"><?php echo $r['client_Name']; ?></td>
		                <td style="text-align:center;"><?php echo $r['q_NetAmount']; ?></td>
		                <td style="text-align:center;"><?php echo $r['TotalItems']; ?></td>
		                <td style="text-align:center;">
		                	<a href="quotation?id=<?=$r['q_id'];?>" class="btn btn-success btn-sm">Edit</a>
                            <a href="sale_add.php?q_id=<?=$r['q_id'];?>" class="btn btn-success btn-sm">Invoice</a>
                            <a href="quotation_print.php?q_id=<?=$r['q_id'];?>" target="_blank" class="btn btn-warning btn-sm">Print</a>
                            <a href="quotation_list_detail.php?q_id=<?=$r['q_id'];?>" target="_blank" class="btn btn-primary btn-sm">Detail</a>
                            <a href="#" onclick="del(<?=$r['q_id'];?>)" class="btn btn-danger btn-sm">Delete</a>
		                </td>
		            </tr>
		        <?php
		        }
		        ?>
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
	"order": [[ 0, 'desc' ]],
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
function del(val){

 $.SmartMessageBox({
 title : "Attention required!",
 content : "This is a confirmation box. Do you want to delete the Record?",
 buttons : '[No][Yes]'
 }, function(ButtonPressed) {
 if (ButtonPressed === "Yes") {


		 $.post("ajax/delAjax.php",
 {
 quotation_id : val, 
 },
 function(data,status){ 

 	//alert(data);
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

 }
</script>