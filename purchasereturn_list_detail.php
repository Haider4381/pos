<?php
include('sessionCheck.php');
include('connection.php');
include('functions.php');
require_once ("inc/init.php");
require_once ("inc/config.ui.php");
$page_title = "Purchase Return List";
include ("inc/header.php");
include ("inc/nav.php");
$branch_id = $_SESSION['branch_id'];
?>
<!-- ==========================CONTENT STARTS HERE ========================== -->
<!-- MAIN PANEL -->
<div id="main" role="main">
	<?php
		$breadcrumbs["Purchase Return List"] = "";
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
		<section id="widget-grid" class="">
<div class="row">
	<article class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
		<div class="jarviswidget jarviswidget-color-purity" id="wid-id-1" data-widget-editbutton="false">
			<header>
            	<span class="small_icon"><i class="fa fa-tags"></i>	</span>					
				<h2>Purchase Return List Detail</h2>
			</header>
			<div>	
<?php 
$row = array();	

if(!isset($_GET['pr_id'])) {
    echo "No record found";
    die();
}
$pr_id = (int) $_GET['pr_id'];

$pQ = "SELECT 
    P.pr_id, P.pr_Number, P.pp_Amount, P.pr_CreatedOn as p_Date, P.pr_BillNo, P.pr_VendorRemarks, 
    P.sup_id, P.pr_TotalAmount, P.pr_DiscountPrice, P.pr_NetAmount, P.pr_Remarks,
    AC.account_title AS sup_Name,
    PD.item_id, PD.item_BarCode, PD.item_Qty, PD.item_Rate, PD.item_TotalAmount,
    PD.item_DiscountPercentage, PD.item_DiscountAmount, PD.item_NetAmount,
    I.item_Name, I.item_Code, B.brand_Name
    FROM adm_purchasereturn AS P
    INNER JOIN adm_purchasereturn_detail AS PD ON PD.pr_id=P.pr_id
    INNER JOIN accounts_chart AS AC ON AC.account_id=P.sup_id
    LEFT JOIN adm_item AS I ON I.item_id=PD.item_id
    LEFT JOIN adm_brand AS B ON B.brand_id=I.brand_id
    WHERE P.pr_id=$pr_id AND P.branch_id=$branch_id
    ORDER BY I.item_id
";
$pRes = mysqli_query($con, $pQ);
if(mysqli_num_rows($pRes) < 1) {
    echo "<div class='alert alert-danger'>No record found. <div>";
    die();
} else {
    while($r = mysqli_fetch_assoc($pRes)) {
        $row[] = $r;
    }
}
?>

<!-- Purchase Detail Header Table -->
<table class="table table-condensed table-bordered table-stripped">
    <tr style="background:#d65252; color:#fff;">
        <th colspan="7">Purchase Detail</th>
    </tr>
    <tr>
        <th style="text-align:center;">Bill No.</th>
        <th style="text-align:center;">Date & Time</th>
        <th style="text-align:center;">Supplier Name</th>
        <th style="text-align:center;">Reference No.</th>
        <th style="text-align:center;">Bill Amount</th>
        <th style="text-align:center;">Paid Amount</th>
        <th style="text-align:center;">Remaining Amount</th>
    </tr>
    <tr>
        <td style="text-align:center;"><?php echo $row[0]['pr_Number']; ?></td>
        <td style="text-align:center;"><?php echo date('d-m-Y  h:i s', strtotime($row[0]['p_Date'])); ?></td>
        <td style="text-align:center;"><?php echo $row[0]['sup_Name']; ?></td>
        <td style="text-align:center;"><?php echo $row[0]['pr_BillNo']; ?></td>
        <td style="text-align:center;"><?php echo $currency_symbol . number_format($row[0]['pr_NetAmount'],2); ?></td>
        <td style="text-align:center;"><?php echo $currency_symbol . number_format($row[0]['pp_Amount'],2); ?></td>
        <td style="text-align:center;"><?php echo $currency_symbol . number_format($row[0]['pr_NetAmount']-$row[0]['pp_Amount'],2); ?></td>
    </tr>
</table>
<br />
<table class="table table-condensed table-bordered table-stripped" style="width: 36%; float: right;">
    <tr>
        <th style="text-align:center; width:30%; height: 48px; line-height: 48px;">Purchase Return Note</th>
        <td style="text-align:left; width:70%; font-size: 12px;"><?php echo $row[0]['pr_Remarks']; ?></td>
    </tr>
    <tr>
        <th style="text-align:center; width:30%; height: 48px; line-height: 48px;">Vendor Note</th>
        <td style="text-align:left; width:70%; font-size: 12px;"><?php echo $row[0]['pr_VendorRemarks']; ?></td>
    </tr>
</table>	

<div class="widget-body no-padding">
    <table id="datatable_fixed_column" class="table table-striped table-bordered" width="100%">
        <thead>
            <tr>
                <th class="hasinput" style="width:7%">
                    <input type="text" class="form-control" placeholder="Sr No." />
                </th>
                <th class="hasinput" style="width:17%">
                    <input type="text" class="form-control" placeholder="Product Code" />
                </th>
                <th class="hasinput" style="width:17%">
                    <input type="text" class="form-control" placeholder="Product Name" />
                </th>
                <th class="hasinput" style="width:16%">
                    <input type="text" class="form-control" placeholder="Total Quantity" />
                </th>
                <th class="hasinput" style="width:16%">
                    <input type="text" class="form-control" placeholder="Unit Cost Price" />
                </th>
                <th class="hasinput" style="width:17%">
                    <input type="text" class="form-control" placeholder="Total Price" />
                </th>
            </tr>
            <tr>
                <th style="text-align:center;">Sr No.</th>
                <th data-hide="phone" style="text-align:center;">Product Code</th>
                <th data-class="expand" style="text-align:center;">Product Name</th>
                <th data-hide="phone" style="text-align:center;">Total Quantity</th>
                <th data-hide="phone" style="text-align:center;">Unit Cost Price</th>
                <th data-hide="phone" style="text-align:center;">Total Price</th>
            </tr>
        </thead>
        <tbody>
        <?php 
        foreach ($row as $key => $r) {
            ?>
            <tr>
                <td style="text-align:center;"><?php echo ($key+1); ?></td>
                <td style="text-align:center;"><?php echo $r['item_Code']; ?></td>
                <td style="text-align:center;"><?php echo $r['item_Name']; ?></td>
                <td style="text-align:center;"><?php echo $r['item_Qty']; ?></td>
                <td style="text-align:center;"><?php echo number_format($r['item_Rate'],2); ?></td>
                <td style="text-align:center;"><?php echo $currency_symbol . $r['item_NetAmount']; ?></td>
            </tr>
        <?php
        }
        ?>
        </tbody>
    </table>
</div>
			</div>
		</div>
	</article>
</div>
		</section>
	</div>
</div>
<!-- ==========================CONTENT ENDS HERE ========================== -->
<?php include ("inc/footer.php"); ?>
<?php include ("inc/scripts.php"); ?>
<script src="<?php echo ASSETS_URL; ?>/js/plugin/datatables/jquery.dataTables.min.js"></script>
<script src="<?php echo ASSETS_URL; ?>/js/plugin/datatables/dataTables.colVis.min.js"></script>
<script src="<?php echo ASSETS_URL; ?>/js/plugin/datatables/dataTables.tableTools.min.js"></script>
<script src="<?php echo ASSETS_URL; ?>/js/plugin/datatables/dataTables.bootstrap.min.js"></script>
<script src="<?php echo ASSETS_URL; ?>/js/plugin/datatable-responsive/datatables.responsive.min.js"></script>
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
	$('#dt_basic').dataTable({
		"sDom": "<'dt-toolbar'<'col-xs-12 col-sm-6'f><'col-sm-6 col-xs-12 hidden-xs'l>r>"+
			"t"+
			"<'dt-toolbar-footer'<'col-sm-6 col-xs-12 hidden-xs'i><'col-xs-12 col-sm-6'p>>",
		"autoWidth" : true,
		"preDrawCallback" : function() {
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
    var otable = $('#datatable_fixed_column').DataTable({
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
</script>
<?php include ("inc/google-analytics.php"); ?>