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

$page_title = "Quotation List";
$branch_id=$_SESSION['branch_id'];
/* ---------------- END PHP Custom Scripts ------------- */

//include header
include("inc/header.php");

//include left panel (navigation)
//follow the tree in inc/config.ui.php
$page_nav["Quotations List"]["active"] = true;
include("inc/nav.php");

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
<!-- ==========================CONTENT STARTS HERE ========================== -->
<!-- MAIN PANEL -->
<div id="main" role="main">

	<?php
		//configure ribbon (breadcrumbs) array("name"=>"url"), leave url empty if no url
		//$breadcrumbs["New Crumb"] => "http://url.com"
		//$breadcrumbs["Sale Related"] = "";
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
			    <span class="small_icon"><i class="fa fa-file-text-o"></i>	</span>						
				<h2>Quotation List Detail</h2>
			</header>

			<!-- widget div-->
			<div>	
<?php 
$row=array();	

	if(!isset($_GET['q_id']))
	{
		echo "No record found";
		die();
	}
	$q_id=(int)$_GET['q_id'];
	$sQ="SELECT  Q.q_Date, Q.q_CreatedOn, Q.q_Number, Q.q_id, Q.q_TotalAmount,Q.q_Discount, Q.q_DiscountAmount, Q.q_NetAmount, Q.q_PaidAmount, Q.q_SaleMode,Q.q_TaxAmount, Q.q_Remarks, Q.q_RemarksExternal, C.client_Name,QD.item_id, QD.item_Qty, QD.item_BarCode, QD.item_IMEI, QD.item_SalePrice,QD.item_DiscountPercentage,QD.item_DiscountPrice,QD.item_NetPrice,I.item_Name,I.item_Code,B.brand_Name
		FROM adm_quotation AS Q
		LEFT JOIN adm_quotation_detail  AS QD ON QD.q_id=Q.q_id
		LEFT JOIN adm_client AS C ON C.client_id=Q.client_id
		LEFT JOIN adm_item AS I ON I.item_id=QD.item_id
		LEFT JOIN adm_brand AS B ON B.brand_id=I.brand_id
		WHERE Q.q_id=$q_id AND Q.branch_id=$branch_id
		ORDER BY I.item_id
		";
		//echo '<pre>'.$sQ.'</pre>';
	$sRes=mysqli_query($con,$sQ);
	if(mysqli_num_rows($sRes)<1)
	{
		echo "<div class='alert alert-danger'>No record found. <div>";
		die();
	}
	else
	{
		while($r=mysqli_fetch_assoc($sRes))
		{
			$row[]=$r;
		}
	}
?>
		<!-- widget content -->
<table class="table table-condensed table-bordered table-stripped">
	<tr style="background: #d65252;color: #fff;">
		<!--<th colspan="<?php if($row[0]['s_SaleMode']=='cash') { echo '7'; } else {echo '7';} ?>">Invoice Detail</th>-->
		<th colspan="7">Quotation Detail</th>
	</tr>
	<tr>
    	<th style="text-align:center;">Date & Time</th>
        <th style="text-align:center;">Invoice No.</th>
        <th style="text-align:center;">Customer Name</th>
        <!--<th style="text-align:center;">Sale Mode</th>-->
        <th style="text-align:center;">Discount (%)</th>
        <th style="text-align:center;">Discount (Amount)</th>
        <th style="text-align:center;">VAT</th>
        <th style="text-align:center;">Invoice Amount</th>
      <!--  <?php if($row[0]['q_SaleMode']=='cash' && $row[0]['q_PaidAmount']>=$row[0]['q_NetAmount']) { ?>
        <th style="text-align:center;">Cash Received</th>
        <th style="text-align:center;">Change</th>
        <?php } ?>
        
        <?php if($row[0]['q_SaleMode']=='cash' && $row[0]['q_PaidAmount']<$row[0]['q_NetAmount']){ ?>
        <th style="text-align:center;">Cash Received</th>
       <th style="text-align:center;">Remaining Amount</th>
        <?php } ?>-->
         
    </tr>
    <tr>
    	<td style="text-align:center;"><?php echo date('d-m-Y h:i:s', strtotime($row[0]['q_CreatedOn'])); ?></td>
        <td style="text-align:center;"><?php echo $row[0]['q_Number']; ?></td>
		<td style="text-align:center;"><?php echo $row[0]['client_Name']; ?></td>
       <!--<td style="text-align:center;"><?php echo $row[0]['q_SaleMode']; ?></td>-->
        <td style="text-align:center;"><?php echo $row[0]['q_Discount']; ?></td>
        <td style="text-align:center;"><?php echo $row[0]['q_DiscountAmount']; ?></td>
        <td style="text-align:center;"><?php echo $row[0]['q_TaxAmount']; ?></td>
        <th style="text-align:center; font-size:18px;"><?php echo $row[0]['q_NetAmount']; ?></th>


       <!-- <?php if($row[0]['s_SaleMode']=='cash' && $row[0]['q_PaidAmount']>=$row[0]['q_NetAmount']){ ?>
        <td style="text-align:center;"><?php echo $row[0]['q_PaidAmount']; ?></td>
        <td style="text-align:center;"><?php echo $row[0]['q_PaidAmount']-$row[0]['q_NetAmount']; ?></td>
        <?php } ?>
        
        <?php if($row[0]['s_SaleMode']=='cash' && $row[0]['q_PaidAmount']<$row[0]['q_NetAmount']){ ?>
        <td style="text-align:center;"><?php echo $row[0]['q_PaidAmount']; ?></td>
        <td style="text-align:center;"><?php echo $row[0]['q_NetAmount']-$row[0]['q_PaidAmount']; ?></td>
        <?php } ?>--> 
        
    </tr>
</table>
<br />

<table class="table table-condensed table-bordered table-stripped" style="width: 36%;
    float: right;">
<tr>
		
        <th style="text-align:center; width:30% height: 48px; line-height: 48px;">Internal Note</th>
        <td style="text-align:left; width:70%;  font-size: 12px; "><?php echo $row[0]['q_Remarks']; ?></td>
    </tr>
	<tr>
		
        <th style="text-align:center; width:30% height: 48px; line-height: 48px;">External Note</th>
        <td style="text-align:left; width:70%;     font-size: 12px; "><?php echo $row[0]['q_RemarksExternal']; ?></td>
    </tr>
</table>	
	<div class="widget-body no-padding">
			<table id="datatable_fixed_column" class="table table-striped table-bordered" width="100%">

		        <thead>
					<tr>
						<th class="hasinput" style="width:12%">
							<input type="text" class="form-control" placeholder="Product Code" />
						</th>
						<th class="hasinput" style="width:22%">
							<input type="text" class="form-control" placeholder="Product Name" />
						</th>
 						<th class="hasinput" style="width:12%">
							<input type="text" class="form-control" placeholder="Quantity" />
						</th> 
                        <th class="hasinput" style="width:12%">
							<input type="text" class="form-control" placeholder="Discount (%)" />
						</th> 
						<th class="hasinput" style="width:17%">
							<input type="text" class="form-control" placeholder="Discount (Amount)" />
						</th>  
						<th class="hasinput" style="width:17%">
							<input type="text" class="form-control" placeholder="Unit" />
						</th>
						<th class="hasinput" style="width:22%">
							<input type="text" class="form-control" placeholder="Total" />
						</th>
					
					</tr>
		            <tr>
		                <th data-hide="phone" style="text-align:center;">Product Code</th>
	                    <th data-class="expand" style="text-align:center;">Product Name</th>
	                    <th data-hide="phone" style="text-align:center;">Quantity</th>
	                    <th style="text-align:center;">Discount(%)</th>
	                    <th style="text-align:center;">Discount(Amount)</th>
	                    <th data-hide="phone" style="text-align:center;">Unit Price</th>
	                    <th data-hide="phone" style="text-align:center;">Total</th>
	                   
		            </tr>
		        </thead>

		        <tbody>
		        <?php 
		        foreach ($row as $key => $r) 
		        {
		     	?>
		            <tr>
		                 <td style="text-align:center;"><?php echo $r['item_Code']; ?></td>
		                <td style="text-align:center;"><?php echo $r['item_Name']; ?></td>
		               <!-- <td style="text-align:center;"><?php echo $r['item_BarCode']; ?></td>-->
		                <td style="text-align:center;"><?php echo $r['item_Qty']; ?></td>
		                 <td style="text-align:center;"><?php echo $r['item_DiscountPercentage']; ?></td>
		                  <td style="text-align:center;"><?php echo $r['item_DiscountPrice']; ?></td>
		                <td style="text-align:center;"><?php echo $r['item_SalePrice']; ?></td>
		                <td style="text-align:center;"><?php echo $r['item_NetPrice']; ?></td>
		                
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

<?php
//include footer
include ("inc/google-analytics.php");
?>