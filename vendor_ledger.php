<?php 
include('sessionCheck.php');
include('connection.php');
include('functions.php');
//initilize the page
require_once ("inc/init.php");

//require UI configuration (nav, ribbon, etc.)
require_once ("inc/config.ui.php");

/*---------------- PHP Custom Scripts ---------

 YOU CAN SET CONFIGURATION VARIABLES HERE BEFORE IT GOES TO NAV, RIBBON, ETC.
 E.G. $page_title = "Custom Title" */

$page_title = "Vendor Payment History";

/* ---------------- END PHP Custom Scripts ------------- */

//include header

include ("inc/header.php");

//include left panel (navigation)
//follow the tree in inc/config.ui.php
$page_nav["Vendor"]["sub"]["Vendor Payment History"]["active"] = true;
include ("inc/nav.php");
?>
<!-- ==========================CONTENT STARTS HERE ========================== -->
<!-- MAIN PANEL -->

<style type="text/css">
	
.jarviswidget{

	margin-bottom: -2px !important;
}
  .form-control {
    border-radius: 5px !important;
    box-shadow: none!important;
    -webkit-box-shadow: none!important;
    -moz-box-shadow: none!important;
    font-size: 12px;
    padding-left: 6px;
    width: 395px;

     }
     .select2-container .select2-choice {
   
    border-radius: 5px;
   
}
.smart-form .col {
    float: left;
    min-height: 1px;
    padding-right: 15px;
    padding-left: 15px;
    font-size: 14px;
    box-sizing: border-box;
    -moz-box-sizing: border-box;
}
</style>

<div id="main" role="main">

	<?php
		//configure ribbon (breadcrumbs) array("name"=>"url"), leave url empty if no url
		//$breadcrumbs["New Crumb"] => "http://url.com"
		$breadcrumbs["Vendor"] = "";
		include("inc/ribbon.php");
	?>

	<!-- MAIN CONTENT -->
	<div id="content">

		
		<!-- widget grid -->
		<section id="widget-grid" class="">
		
			<!-- row -->
			<div class="row">
<article class="col-sm-12 col-md-12 col-lg-12 sortable-grid ui-sortable">

		<div class="jarviswidget" id="wid-id-0">
			<header>
            	<span class="small_icon"><i class="fa fa-signal"></i>	</span>
				<h2>Vendor Payment History</h2>					
			</header>

						<!-- widget div-->
<div role="content">			
							<!-- widget content -->
	<div class="widget-body no-padding">
									
						<br />
				<form id="checkout-form" class="smart-form" novalidate="novalidate" method="post" action="vendor_ledger_report.php" target="_blank"> 	
					<fieldset>
					<div class="row" style="margin-bottom: 5px;">
						<div class="col col-lg-2">
							Select Vendor:
						</div>
						<div class="col col-lg-4">
							<select class="select2" name="sup_id" id="sup_id" >
							<?php $supArray=get_Supplier();
                            foreach ($supArray as $key => $supRow) { ?>
                                <option value="<?php echo $supRow['sup_id'];?>"> <?php echo $supRow['sup_Name'];?> </option>	
                            <?php } ?>
                            </select>
						</div>
					</div><!--End of row-->	
					<div class="row">
						<div class="col col-lg-2">
							Select Date From:
						</div>
						<div class="col col-lg-4">
                        	<input type="text" name="from_date" value="<?php echo date('01-m-Y');?>" placeholder="Select a date" class="form-control datepicker" data-dateformat="dd-mm-yy">
						</div>
					</div>	<!--End of row-->	
					<div class="row" style="margin-top:5px;">
						<div class="col col-lg-2">
							Select Date To:
						</div>
						<div class="col col-lg-4">
                            <input type="text" name="to_date" value="<?php echo date('d-m-Y');?>" placeholder="Select a date" class="form-control datepicker" data-dateformat="dd-mm-yy">
						</div>
					</div>	<!--End of row-->					
					</fieldset>
					<footer>
						<button type="submit" class="btn btn-primary" name="submit">Show</button>
					</footer>
				</form>
	
	</div>	<!-- end widget body -->					
							
</div><!--Div role="content"-->
						
					</div><!-- End of Div wid-id-1-->
</article>
		
			</div><!--End of div row-->
		
		</section>
		<!-- end widget grid -->
	</div>
	<!-- END MAIN CONTENT -->

</div>
<!-- END MAIN PANEL -->
<!-- ==========================CONTENT ENDS HERE ========================== -->

<!-- PAGE FOOTER -->
<?php // include page footer
include ("inc/footer.php");
?>
<!-- END PAGE FOOTER -->

<?php //include required scripts
include ("inc/scripts.php");
?>

<!-- PAGE RELATED PLUGIN(S) -->
<script src="<?php echo ASSETS_URL; ?>/js/plugin/datatables/jquery.dataTables.min.js"></script>
<script src="<?php echo ASSETS_URL; ?>/js/plugin/datatables/dataTables.colVis.min.js"></script>
<script src="<?php echo ASSETS_URL; ?>/js/plugin/datatables/dataTables.tableTools.min.js"></script>
<script src="<?php echo ASSETS_URL; ?>/js/plugin/datatables/dataTables.bootstrap.min.js"></script>
<script src="<?php echo ASSETS_URL; ?>/js/plugin/datatable-responsive/datatables.responsive.min.js"></script>

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

	/* BASIC ;*/
		var responsiveHelper_dt_basic = undefined;
		var responsiveHelper_datatable_fixed_column = undefined;
		var responsiveHelper_datatable_col_reorder = undefined;
		var responsiveHelper_datatable_tabletools = undefined;
		
		var breakpointDefinition = {
			tablet : 1024,
			phone : 480
		};



	/* END BASIC */
	
	/* COLUMN FILTER  */
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
    $("div.toolbar").html('<div class="text-right"></div>');
    	   
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
<?php
//include footer
include ("inc/google-analytics.php");
?>