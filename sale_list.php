<?php
include('sessionCheck.php');
include('connection.php');
include('functions.php');
require_once ("inc/init.php");
require_once ("inc/config.ui.php");
$page_title = "Invoice List";
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
				<h2>Invoice List</h2>
			</header>

			<!-- widget div-->
			<div>		


				<!-- widget content -->
				<div class="widget-body no-padding">
	<br>

<div class="tab-content" >

<form method="post" action="">	
			<div class="row">
				<div class="col col-lg-2" style="text-align:right;     line-height: 30px;">
					Date From :
				</div>
				<div class="col col-lg-3">
                    <input type="text" name="from_date" value="<?php echo date('01-m-Y');?>" placeholder="Select a date" class="form-control datepicker" data-dateformat="dd-mm-yy">
				</div>
				<div class="col col-lg-2" style="text-align:right;     line-height: 30px;">
					Date To :
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

$limit='LIMIT 50 ';

if(isset($_POST['submit']))
{
	$where='';
	$limit='';
	if(!empty($_POST['from_date']))
	{
		$from_date=date("Y-m-d", strtotime($_POST['from_date']));
		$where.=" AND S.s_Date>='".$from_date."'";
	}
	if(!empty($_POST['to_date']))
	{
		$to_date=date("Y-m-d", strtotime($_POST['to_date']));
		$where.=" AND S.s_Date<='".$to_date."'";
	}
}


$pQ = "SELECT S.s_id, S.s_Date, S.s_CreatedOn, S.s_Number, S.client_id, S.s_SaleMode, S.s_PaidAmount, S.s_TotalAmount, S.s_DiscountPrice, S.s_NetAmount, S.s_Remarks, COUNT(SD.sd_id) AS TotalItems, C.account_title AS client_Name
	FROM cust_sale AS S
	LEFT JOIN cust_sale_detail AS SD ON SD.s_id = S.s_id
	LEFT JOIN accounts_chart AS C ON C.account_id = S.client_id
	WHERE S.branch_id = $branch_id $where
	GROUP BY S.s_id DESC
	$limit
";
	// die();
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
					    <th></th></th>
						<th class="hasinput" style="width:8%">
							<input type="text" class="form-control" placeholder="Invoice No." />
						</th>
                        <th class="hasinput" style="width:14%">
							<input type="text" placeholder="Filter Date" class="form-control">
						</th>
						<th class="hasinput" style="width:19%">
							<input type="text" class="form-control" placeholder="Customer Name" />
						</th>
						
					
							<th class="hasinput" style="width:8%">
								<input type="text" class="form-control" placeholder="Sale Mode" />
							</th>
                            <th class="hasinput" style="width:10%">
								<input type="text" class="form-control" placeholder="Invoice Amount" />
							</th>
							<th class="hasinput" style="width:11%">
								<input type="text" class="form-control" placeholder="Received Amount" />
							</th>
					        
                            <th class="hasinput" style="width:10%">
                            	<input type="text" class="form-control" placeholder="Remain Amount" />
                            </th>
                            <th class="hasinput" style="width:7%">
                            	<input type="text" class="form-control" placeholder="Change" />
                            </th>
					        
						<th style="width:13%;">
							
						</th>
					</tr>
		            <tr>
		                <th style="text-align:center;">Sr.</th>
	                    <th style="text-align:center;">Invoice No.</th>
                        <th data-class="expand" style="text-align:center;">Date & Time</th>
	                    <th style="text-align:center;">Customer Name</th>
	                   <th style="text-align:center;">Sale Mode</th>
	                    <th data-hide="phone" style="text-align:center;">Invoice Amount</th>
	                    <th data-hide="phone,tablet" style="text-align:center;">Received Amount</th>
	                    <th data-hide="phone,tablet" style="text-align:center;">Remain Amount</th>
	                    <th data-hide="phone,tablet" style="text-align:center;">Change</th>
	                    <th>
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
		            <?php echo "<tr id='row".$r['s_id']."'>";?>
		                <td style="text-align:center;"><?php echo $i; ?></td>
		                <td style="text-align:center;"><?php echo $r['s_Number']; ?></td>
                        <td style="text-align:center;">
    <?php echo !empty($r['s_Date']) ? date('d-m-Y', strtotime($r['s_Date'])) : ''; ?>
</td>
		                <td style="text-align:center;"><?php echo $r['client_Name']; ?></td>
		                <td style="text-align:center;"><?=$r['s_SaleMode']?></td>
                        <td style="text-align:center;"><?=$currency_symbol.$r['s_NetAmount']?></td>
		                <td style="text-align:center;"><?=$currency_symbol.$r['s_PaidAmount']?></td>
		                <td style="text-align:center;"><?php if(($r['s_SaleMode']=='cash' || $r['s_SaleMode']=='credit') && $r['s_PaidAmount']<$r['s_NetAmount']){ echo $currency_symbol.number_format($r['s_NetAmount']-$r['s_PaidAmount'],2); }?></td>
		                <td style="text-align:center;"><?php if($r['s_SaleMode']=='cash' && $r['s_PaidAmount']>=$r['s_NetAmount']){ echo $currency_symbol.number_format($r['s_PaidAmount']-$r['s_NetAmount'],2); }?></td>
		                <td>
    <a href="sale_add?id=<?= $r['s_id']; ?>" target="_blank" class="btn btn-success btn-sm">Edit</a>
    <a href="sale_list_detail.php?s_id=<?= $r['s_id']; ?>" target="_blank" class="btn btn-primary btn-sm">Detail</a>
    <a href="invoice_print.php?s_id=<?= $r['s_id']; ?>" target="_blank" class="btn btn-success btn-sm">Print</a>

    <!-- New: Download PDF -->
    <a href="invoice_pdf.php?s_id=<?= $r['s_id']; ?>&print_header=yes&show_prebalance=yes" target="_blank" class="btn btn-danger btn-sm">
        <i class="fa fa-file-pdf-o"></i> PDF
    </a>
    <a href="javascript:del(<?= $r['s_id']; ?>)" class="btn btn-danger btn-xs">Delete</a>
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
     "order": [],
 //	"order": [[ 0, 'desc' ]],
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
 s_id : val, 
 },
 function(data,status){ 
 if(data.trim()!="")
 {
	 document.getElementById('row'+val).style.display= "none";

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