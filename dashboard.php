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

$page_title = "Dashboard";

/* ---------------- END PHP Custom Scripts ------------- */

//include header
include("inc/header.php");

//include left panel (navigation)
//follow the tree in inc/config.ui.php
$page_nav["Dashboard"]["active"] = true;
include("inc/nav.php");

?>
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
			<header> <span class="small_icon"><i class="fa fa-dashboard"></i>	</span>			
				<h2>Dashboard</h2>
			</header>

			<!-- widget div-->
			<div>		


				<!-- widget content -->
				<div class="widget-body no-padding">
<br>
<style>
.dashbord_block{
	display:block;
	width:100%;
	padding:30px 0px;
	/*background:#FFF;
	border:2px solid #00aaff;*/
	border-radius:5px;
	color:#58CCED;
	font-size:20px;
	transition: background-color 2s ease;
	background:#009DFF;
	color:#FFF;
}
.dashbord_block i{
	font-size:40px;
}
.dashbord_block:hover{
	background:#031F4B;
	color:#FFF;
	cursor:pointer;
}


.dashboard_tiles td{
	padding:8px;
}
</style>
 <div class="tab-content" style="text-align:center;">
	
    <table class="dashboard_tiles">
    	<tr>
        	<td style=" width:25%;" rowspan="2"><a href="sale_add"><span class="dashbord_block" style="padding:48px 0px;"><i class="fa fa-file-text-o"></i><br /><br /><br />Invoice</span></a></td>
            <td style=" width:25%;"><a href="purchase_add.php"><span class="dashbord_block"><i class="fa fa-suitcase"></i>&nbsp;&nbsp;&nbsp; Purchase</span></a></td>
            <td style=" width:25%;"><a href="accounts_chart"><span class="dashbord_block"><i class="fa fa-cubes"></i>&nbsp;&nbsp;&nbsp;Account Chart</span></a></td>
			<td style=" width:25%;" rowspan="2"><a href="item_list"><span class="dashbord_block" style="padding:48px 0px;"><i class="fa fa-recycle"></i><br /><br /><br />Inventory</span></a></td>
        </tr>
        <tr>
        	
            <td><a href="sale_payment"><span class="dashbord_block"><i class="fa fa-bar-chart-o"></i>&nbsp;&nbsp;&nbsp;Payment In</span></a></td>
			<td><a href="purchase_payment"><span class="dashbord_block"><i class="fa fa-child"></i>&nbsp;&nbsp;&nbsp;Payment Out</span></a></td>
			
        </tr>
		
		
    </table><br />
<br />
<!--
    <div class="col col-md-2"></div>
    <div class="col col-md-2"><a href="item_add.php"><span class="dashbord_block"><i class="fa fa-cubes"></i><br />Products</span></a></div>
    <div class="col col-md-2"><a href="supplier_add.php"><span class="dashbord_block"><i class="fa fa-paw"></i><br />Suppliers</span></a></div>
    <div class="col col-md-2"><a href="client_add.php"><span class="dashbord_block"><i class="fa fa-child"></i><br />Customers</span></a></div>
    <div class="col col-md-2"><a href="purchase_add.php"><span class="dashbord_block"><i class="fa fa-list"></i><br />Purchases</span></a></div>
    <div class="col col-md-2"></div>
    <br style="clear:both;" />
    <br style="clear:both;" />

	<div class="col col-md-2"></div>
    <div class="col col-md-2"><a href="sale_add.php"><span class="dashbord_block"><i class="fa fa-paper-plane"></i><br />Sales</span></a></div>
    <div class="col col-md-2"><a href="sale_list.php"><span class="dashbord_block"><i class="fa fa-bar-chart-o"></i><br />Invoices</span></a></div>
    <div class="col col-md-2"><a href="client_ledger_form.php"><span class="dashbord_block"><i class="fa fa-history"></i><br />Ledger</span></a></div>
    <div class="col col-md-2"><a href="logout.php"><span class="dashbord_block"><i class="fa fa-sign-out"></i><br />Exit</span></a></div>
    <div class="col col-md-2"></div>
<br style="clear:both;" />
<br /><br /><br />
-->    
    
    
    
    
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

		<!-- PAGE RELATED PLUGIN(S) -->

		<!-- DYGRAPH -->
		<script src="<?php echo ASSETS_URL; ?>/js/plugin/chartjs/chart.min.js"></script>
