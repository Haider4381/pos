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

$page_title = "Supplier List";

/* ---------------- END PHP Custom Scripts ------------- */

//include header
include ("inc/header.php");

//include left panel (navigation)
//follow the tree in inc/config.ui.php
//$page_nav["Settings"]["sub"]["Customers"]["active"] = true;
include ("inc/nav.php");
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
    padding-left: 10px;
    
}
label {
   
    margin-top: 8px !important;
}
textarea.form-control {
    height: 70px;
}
.select2-container .select2-choice {
    display: block;
    height: 32px;
    padding: 0 0 0 8px;
    overflow: hidden;
    position: relative;
    border: 1px solid #ccc;
    white-space: nowrap;
    line-height: 32px;
    color: #444;
    text-decoration: none;
    background-clip: padding-box;
    -webkit-touch-callout: none;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
    background-color: #fff;
    border-radius: 5px;
    width: 225px;
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
				<span class="small_icon"><i class="fa fa-life-ring"></i>	</span>	
				<h2>Vendor list</h2>
			</header>

			<!-- widget div-->
			<div>		

				<!-- widget content -->
				<div class="widget-body no-padding">
	
	
					<table id="datatable_fixed_column" class="table table-striped table-bordered" width="100%">

				 <thead>
							<tr>
								
								<th class="hasinput" style="width:4%">
									<input type="text" class="form-control" placeholder="Sr No." />
								</th>
								<th class="hasinput" style="width:10%">
									<input type="text" class="form-control" placeholder="Vendor Name" />
								</th>
								<th class="hasinput" style="width:8%">
									<input type="text" class="form-control" placeholder="Vendor Email" />
								</th>
								<th class="hasinput" style="width:8%" >
									<input type="text" placeholder="Vendor Phone" class="form-control">
								</th>
								<th class="hasinput" style="width:12%">
									<input type="text" class="form-control" placeholder="Vendor Address" />
								</th>
								<th class="hasinput" style="width:12%">
									<input type="text" class="form-control" placeholder="Vendor Note" />
								</th>
								<th class="hasinput" style="width:1%" >
									<input type="text" placeholder="Status" class="form-control">
								</th>
							
								<th class="hasinput" style="width:8%">
									
								</th>
							</tr>	
							<tr>
							    <th style="text-align:center;">Sr No.</th>
								<th style="text-align:center;">Vendor Name</th>
								<th style="text-align:center;">Vendor Email</th>
								<th style="text-align:center;">Vendor Phone</th>
								<th style="text-align:center;">Vendor Address</th>
								<th style="text-align:center;">Vendor Note</th>
								<th style="text-align:center;">Status</th>
								<th style="text-align:center;">Action</th>
							</tr>
						</thead>
						<tbody>
							
<?php
$serial=1;
$supArray=get_SupplierList();
foreach ($supArray as $key => $supRow) { 
?>		
						<?php echo "<tr id='row".$supRow['sup_id']."'>";?>
							<td style="text-align:center;"><?=$serial;?></td>
						 	<td style="text-align:center;"><?php echo $supRow['sup_Name'];?> </td>
						 	<td style="text-align:center;"><?php echo $supRow['sup_Email'];?> </td>
						 	<td style="text-align:center;"><?php echo $supRow['sup_Phone'];?> </td>
						 	<td style="text-align:center;"><?php echo $supRow['sup_Address'];?> </td>
						 	<td style="text-align:left;"><?php echo $supRow['sup_Remarks'];?> </td>
						 	<td style="text-align:center;"><?php echo get_StatusName($supRow['sup_Status']);?> </td>
							<td> 
								<a href="supplier_add.php?id=<?=$supRow['sup_id']?>" class="btn btn-primary">Edit</a> 
							</td>
				 			</tr>
			 		
							
<?php
$serial++;
} 
?>

				 </tbody>
				
					</table>

				</div>
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
  /*  $("div.toolbar").html('<div class="text-right"><img src="img/logo.png" alt="SmartAdmin" style="width: 111px; margin-top: 3px; margin-right: 10px;"></div>');*/
    	   
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
 $("#discount").keypress(function(e){
    if(e.keyCode==13){
        addToTable();
    }
});
var count=1;
	function save_form(){
		$("#client_form").submit();
	}

	function addToTable()
	{
		var item_text=$("#item option:selected").text();
		var brand_id=$("#item  option:selected").val();
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
			$(newRow).find('.acs_DiscountPercentage').val(discount_percentage);
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