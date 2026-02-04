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

$page_title = "IMEI Finder";

/* ---------------- END PHP Custom Scripts ------------- */

//include header
include ("inc/header.php");

//include left panel (navigation)
//follow the tree in inc/config.ui.php
//$page_nav["Reports"]["sub"]["IMEI Finder"]["active"] = true;
include ("inc/nav.php");
?>
<!-- ==========================CONTENT STARTS HERE ========================== -->
<!-- MAIN PANEL -->
<div id="main" role="main">

	<?php
		//configure ribbon (breadcrumbs) array("name"=>"url"), leave url empty if no url
		//$breadcrumbs["New Crumb"] => "http://url.com"
		$breadcrumbs["Inventory"] = "";
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
				<h2> &nbsp;&nbsp;<i class="fa fa-signal"></i> IMEI Finder</h2>
			</header>

			<!-- widget div-->
			<div>		


				<!-- widget content -->
				<div class="widget-body no-padding">
	<br>
		<div class="tab-content" >
			<div role="tabpanel" class="tab-pane active" id="add" >

				<form id="checkout-form" class="smart-form" novalidate="novalidate" method="post" action="">	
					<fieldset>
					<div class="row">
						<div class="col col-lg-2 col-xs-12">
							<label> Enter IMEI</label>
						</div>
						<div class="col col-lg-4 col-xs-12">
							<input type="text" name="item_IMEI" id="item_IMEI" value="<?php if(isset($_POST['item_IMEI'])) { echo $_POST['item_IMEI'];}?>" class="form-control">
						</div>
                        <div class="col col-lg-4 col-xs-12">
							<input type="submit" class="btn btn-primary btn-sm" name="submit" id="submit" value="Find IMEI">
						</div>
                        	
					</div><!--End of row-->
					</fieldset>
				</form>
			
			</div><!--End of div id="add"-->

<br>
<br>

<?php 
if(isset($_POST['submit']))
{
	$item_IMEI = $_POST['item_IMEI'];
	$imeiQ="SELECT 'Purchase' as trn_type, p_Date as trn_date, pd_CreatedOn as trn_datetime, item_Qty as item_qty, item_Rate as item_rate, item_NetAmount as item_value, item_Name as item_name, sup_Name as party_name
			FROM adm_purchase_detail
			INNER JOIN adm_purchase ON adm_purchase.p_id=adm_purchase_detail.p_id
			INNER JOIN adm_supplier ON adm_supplier.sup_id=adm_purchase.sup_id
            INNER JOIN adm_item ON adm_item.item_id=adm_purchase_detail.item_id
			WHERE adm_purchase_detail.item_IMEI='$item_IMEI'

			UNION
			SELECT 'Sale' as trn_type, s_Date as trn_date, cust_sale.s_CreatedOn as trn_datetime, item_Qty as item_qty, cust_sale_detail.item_SalePrice as item_rate, item_NetPrice as item_value, item_Name as item_name, client_Name as party_name
			FROM cust_sale_detail
			INNER JOIN cust_sale  ON cust_sale.s_id=cust_sale_detail.s_id
			INNER JOIN adm_client ON adm_client.client_id=cust_sale.client_id
            INNER JOIN adm_item ON adm_item.item_id=cust_sale_detail.item_id
			WHERE cust_sale_detail.item_IMEI='$item_IMEI'

			UNION
			SELECT 'Sale Return' as trn_type, sr_Date as trn_date, sr_CreatedOn as trn_datetime, item_Qty as item_qty, cust_salereturn_detail.item_SalePrice as item_rate, item_NetPrice as item_value, item_Name as item_name, client_Name as party_name
			FROM cust_salereturn_detail
			INNER JOIN cust_salereturn  ON cust_salereturn.sr_id=cust_salereturn_detail.sr_id
			INNER JOIN adm_client ON adm_client.client_id=cust_salereturn.client_id
            INNER JOIN adm_item ON adm_item.item_id=cust_salereturn_detail.item_id
			WHERE cust_salereturn_detail.item_IMEI='$item_IMEI'
			ORDER BY 3";
	//echo '<pre>'.$imeiQ.'</pre>';
	$imeiQR=mysqli_query($con,$imeiQ);
	$rows_count=mysqli_num_rows($imeiQR);
	if($rows_count>0){
	?>
    
    <table class="table table-bordered table-hover table-striped">
    	
        <tr style="background:#009DFF; color:#fff; font-weight:bold; font-size:17px;">
        
            <td colspan="9" align="left">IMEI : <?php echo $item_IMEI; ?></td>
        </tr>
        <tr>
            <th style="text-align:center;">Date & Time</th>
            <th style="text-align:center;"style="text-align:center;"style="text-align:center;"style="text-align:center;"style="text-align:center;"style="text-align:center;">Vendor / Customer Name</th>
            <th style="text-align:center;"style="text-align:center;"style="text-align:center;"style="text-align:center;"style="text-align:center;">Transaction type</th>
            <th style="text-align:center;"style="text-align:center;"style="text-align:center;"style="text-align:center;">Product Name</th>
            <th style="text-align:center;"style="text-align:center;"style="text-align:center;">Quantity</th>
            <th style="text-align:center;"style="text-align:center;">Rate</th>
            <th style="text-align:center;">Value</th>
		</tr>
    	<?php
		while($imei_Row=mysqli_fetch_assoc($imeiQR))
		{
		?>
		<tr>
        	
        	<td style="text-align:center;"><?php echo date("d-m-Y h:i A", strtotime($imei_Row['trn_datetime'])); ?></td>
        	<td style="text-align:center;"><?php echo $imei_Row['party_name']; ?></td>
            <td style="text-align:center;"><?php echo $imei_Row['trn_type']; ?></td>
			<td><?php echo $imei_Row['item_name']; ?></td>
        	<td style="text-align:center;"><?php echo $imei_Row['item_qty']; ?></td>
        	<td style="text-align:center;"><?php echo $imei_Row['item_rate']; ?></td>
        	<td style="text-align:center;"><?php echo $imei_Row['item_value']; ?></td>

		</tr>
        <?php
		}
	}
	else
	{
		echo '<p style="text-align:center; font-size:20px;">No Record Found</p>';	
	}
		?>
    </table>
    
    
    <?php

}
/*
Brand INSERT Ends
*/

?>


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