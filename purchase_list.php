<?php
include('sessionCheck.php');
include('connection.php');
include('functions.php');
require_once ("inc/init.php");
require_once ("inc/config.ui.php");
$page_title = "Purchase List";
include ("inc/header.php");
//$page_nav["Settings"]["sub"]["Brands"]["active"] = true;
include ("inc/nav.php");
$branch_id=$_SESSION['branch_id'];
?>
<!-- ==========================CONTENT STARTS HERE ========================== -->
<!-- MAIN PANEL -->
<div id="main" role="main">

	<?php
		$breadcrumbs["Purchase List"] = "";
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
.dataTables_filter .input-group-addon+.form-control {
    display: none;
}
.dataTables_filter .input-group-addon {
    display: none;
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
				<h2>Purchase List</h2>
			</header>

			<!-- widget div-->
			<div>

<form method="post" action="">	
			<div class="row">
				<div class="col col-lg-2" style="text-align: right; line-height: 30px;">
					From Date
				</div>
				<div class="col col-lg-3">
                    <input type="text" name="from_date" value="<?php echo date('01-m-Y');?>" placeholder="Select a date" class="form-control datepicker" data-dateformat="dd-mm-yy">
				</div>
				<div class="col col-lg-2" style="text-align: right; line-height: 30px;">
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
$limit='LIMIT 0, 10';

if(isset($_POST['submit']))
{
	$limit='';
	if(!empty($_POST['from_date']))
	{
		$from_date=date("Y-m-d", strtotime($_POST['from_date']));
		$where.=" AND P.p_Date>='".$from_date."'";
	}
	if(!empty($_POST['to_date']))
	{
		$to_date=date("Y-m-d", strtotime($_POST['to_date']));
		$where.=" AND P.p_Date<='".$to_date."'";
	}
}

// --- Updated Query: Use accounts_chart for vendor/supplier info ---
$pQ="
	SELECT 
		P.p_id,
		P.p_Number,
		P.pp_Amount, 
		P.p_CreatedOn as p_Date, 
		P.p_BillNo, 
		P.sup_id, 
		P.p_TotalAmount, 
		P.p_DiscountPrice, 
		P.p_NetAmount, 
		P.p_Remarks,
		sum(PD.item_Qty) as item_Qty, 
		COUNT(PD.pd_id) AS TotalItems,
		AC.account_title AS supplier_name,
		AC.phone AS supplier_phone,
		AC.address AS supplier_address
	FROM adm_purchase AS P
	INNER JOIN adm_purchase_detail  AS PD ON PD.p_id=P.p_id
	INNER JOIN accounts_chart AS AC ON AC.account_id=P.sup_id
	WHERE 1 AND P.branch_id=$branch_id $where
	GROUP BY P.p_id
	ORDER BY P.p_id DESC
	$limit
";
$pRes=mysqli_query($con,$pQ);
if(mysqli_num_rows($pRes)<1)
{
	echo "<div class='alert alert-danger'>No record found. <div><br style='clear:both;'><br style='clear:both;'>";
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
			<table id="datatable_fixed_column" class="table table-striped table-bordered" style="width:100%;">
		        <thead>
					<tr>
						<th class="hasinput" style="width:6%">
							<input type="text" class="form-control" placeholder="Bill No." />
						</th>
                        <th class="hasinput icon-addon" style="width:93%;">
							<input id="dateselect_filter" type="text" placeholder="Filter Date" class="form-control datepicker" data-dateformat="dd-mm-yy">
							<label for="dateselect_filter" class="glyphicon glyphicon-calendar no-margin padding-top-15" rel="tooltip" title="" data-original-title="Filter Date"></label>
						</th>
						<th class="hasinput" style="width:12%;">
							<input type="text" class="form-control" placeholder="Supplier Name" />
						</th>
						<th class="hasinput" style="width:9%;">
							<input type="text" class="form-control" placeholder="Reference No." />
						</th>
						<th class="hasinput" style="width:8%;">
							<input type="text" class="form-control" placeholder="T Items" />
						</th>
                        <th class="hasinput" style="width:9%;">
							<input type="text" class="form-control" placeholder="T Quantity" />
						</th>
						<th class="hasinput" style="width:9%;">
								<input type="text" class="form-control" placeholder="Bill Amount" />
						</th>
                        <th class="hasinput" style="width:10%;">
								<input type="text" class="form-control" placeholder="Paid Amount" />
						</th>					
						 <th class="hasinput" style="width:11%;">
								<input type="text" class="form-control" placeholder="Remain Amount" />
						</th>
						<th class="hasinput" style="width:13%;"></th>
					</tr>
		            <tr>
	                    <th>Bill No.</th>
                        <th data-class="expand" style="text-align:center;">Date & Time</th>
	                    <th style="text-align:center;">Supplier Name</th>
	                    <th data-hide="phone" style="text-align:center;">Reference No.</th>
                        <th data-hide="phone,tablet" style="text-align:center;">T Items</th>
                        <th data-hide="phone,tablet" style="text-align:center;">T Quantity</th>
                        <th data-hide="phone" style="text-align:center;">Bill Amount</th>
	                    <th data-hide="phone" style="text-align:center;">Paid Amount</th>
	                    <th data-hide="phone" style="text-align:center;">Remain Amount</th>
	                    <th>Action</th>
		            </tr>
		        </thead>
		        <tbody>
		        <?php 
		        foreach ($row as $key => $r) 
		        {
		     	?>
		            <?php echo "<tr id='row".$r['p_id']."'>";?>
		                <td style="text-align:center;"><?php echo $r['p_Number']; ?></td>
                        <td style="text-align:center;"><?php echo date('d-m-Y  h:i s',strtotime($r['p_Date'])); ?></td>
		                <td style="text-align:center;">
							<?php echo $r['supplier_name']; ?>
							<?php if(!empty($r['supplier_phone'])) echo '<br><small>'.$r['supplier_phone'].'</small>'; ?>
							<?php if(!empty($r['supplier_address'])) echo '<br><small>'.$r['supplier_address'].'</small>'; ?>
						</td>
		                <td style="text-align:center;"><?php echo $r['p_BillNo']; ?></td>
                        <td style="text-align:center;"><?php echo $r['TotalItems']; ?></td>
                        <td style="text-align:center;"><?php echo $r['item_Qty']; ?></td>
                        <td style="text-align:center;"><?php echo $currency_symbol.$r['p_NetAmount']; ?></td>
		                <td style="text-align:center;"><?php echo $currency_symbol.$r['pp_Amount']; ?></td>
		                 <td style="text-align:center;"><?php echo $currency_symbol.number_format($r['p_NetAmount']-$r['pp_Amount'],2); ?></td>
		                <td>
                        <a href="purchase_list_detail?p_id=<?=$r['p_id'];?>" target="_blank" class="btn btn-primary btn-xs">Detail</a>
                        <a href="purchase_edit.php?p_id=<?=$r['p_id'];?>" class="btn btn-warning btn-xs">Edit</a>
                        <a href="javascript:del(<?php echo $r['p_id'];?>)" class="btn btn-danger btn-xs">Delete</a>
                        <a href="purchase_print.php?p_id=<?=$r['p_id'];?>" target="_blank" class="btn btn-success btn-xs">Print</a>
		                </td>
		            </tr>
		        <?php
		        }
		        ?>
		        </tbody>
			</table>
</div>
			</div>
			<!-- end widget div -->
		</div>
		<!-- end widget -->
	</article>
	<!-- WIDGET END -->
</div>
<!-- end row -->	
		</section>
		<!-- end widget grid -->
	</div>
	<!-- END MAIN CONTENT -->
</div>
<!-- END MAIN PANEL -->
<!-- ==========================CONTENT ENDS HERE ========================== -->

<!-- PAGE FOOTER -->
<?php include ("inc/footer.php"); ?>
<!-- END PAGE FOOTER -->
<?php include ("inc/scripts.php"); ?>
<!-- PAGE RELATED PLUGIN(S) -->
<script src="<?php echo ASSETS_URL;?>/js/plugin/datatables/jquery.dataTables.min.js"></script>
<script src="<?php echo ASSETS_URL;?>/js/plugin/datatables/dataTables.colVis.min.js"></script>
<script src="<?php echo ASSETS_URL;?>/js/plugin/datatables/dataTables.tableTools.min.js"></script>
<script src="<?php echo ASSETS_URL;?>/js/plugin/datatables/dataTables.bootstrap.min.js"></script>
<script src="<?php echo ASSETS_URL;?>/js/plugin/datatable-responsive/datatables.responsive.min.js"></script>
<script type="text/javascript">
$(document).ready(function() {
	var responsiveHelper_dt_basic = undefined;
	var responsiveHelper_datatable_fixed_column = undefined;
	var responsiveHelper_datatable_col_reorder = undefined;
	var responsiveHelper_datatable_tabletools = undefined;
	var breakpointDefinition = {
		tablet : 1024,
		phone : 480
	};
	var otable = $('#datatable_fixed_column').DataTable({
	"order": [[ 0, 'desc' ]],
		"sDom": "<'dt-toolbar'<'col-xs-12 col-sm-6 hidden-xs'f><'col-sm-6 col-xs-12 hidden-xs'<'toolbar'>>r>"+
				"t"+
				"<'dt-toolbar-footer'<'col-sm-6 col-xs-12 hidden-xs'i><'col-xs-12 col-sm-6'p>>",
		"autoWidth" : true,
		"preDrawCallback" : function() {
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
 $("#datatable_fixed_column thead th input[type=text]").on( 'keyup change', function () {
 	otable
 	.column( $(this).parent().index()+':visible' )
 	.search( this.value )
 	.draw();
 } );
})
function del(val){
 $.SmartMessageBox({
 title : "Attention required!",
 content : "This is a confirmation box. Do you want to delete the Record?",
 buttons : '[No][Yes]'
 }, function(ButtonPressed) {
 if (ButtonPressed === "Yes") {
	$.post("ajax/delAjax.php",{ p_id : val },function(data,status){ 
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
}
</script>