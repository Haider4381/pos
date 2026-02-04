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

$page_title = "Customer Payment Policy";
$u_id=$_SESSION['u_id'];
$branch_id=$_SESSION['branch_id'];
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
<style type="text/css">
	td{
		padding: 3px !important;
	}
</style>
	<?php
		//configure ribbon (breadcrumbs) array("name"=>"url"), leave url empty if no url
		//$breadcrumbs["New Crumb"] => "http://url.com"
		$breadcrumbs["Admin Tool"] = "";
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
				<span class="small_icon"><i class="fa fa-flash"></i>	</span>	
				<h2>Customer Payment Policy</h2>
			</header>

			<!-- widget div-->
			<div>		


				<!-- widget content -->
				<div class="widget-body no-padding">

		<div class="tab-content" >
			<div role="tabpanel" class="tab-pane active" id="add" >
<?php 
/************************************
DATA INSERTIONS STARTS
************************************/
		 
/************************************
END OF DATA INSERTIONS
************************************/
/************************************
DATA INSERTIONS STARTS
************************************/
if(isset($_POST['update'])){
			$branch_CustomerPaymentPolicy=validate_input($_POST['policy']);
			if(isset($_POST['branch_ShowPolicy'])) {$branch_ShowPolicy=1;} else {$branch_ShowPolicy=0;}
			 $q="UPDATE adm_branch
			 	SET
					branch_CustomerPaymentPolicy='$branch_CustomerPaymentPolicy',
					branch_ShowPolicy='$branch_ShowPolicy'
			 WHERE branch_id=$branch_id";
			if(mysqli_query($con,$q))
			{
				$_SESSION['msg']="Payment Policy Updated successfully";
				echo "<script>window.location='customer_payment_policy.php'; </script>";
				die();
			}	

			else
			{
				$_SESSION['msg']="Problem Updating Payment Policy";
			}
				
		}
/************************************
END OF DATA INSERTIONS
************************************/
?>
<?php if(!empty($_SESSION['msg'])){?>   <div class="alert alert-info"><?php echo $_SESSION['msg']; ?> </div> <?php unset($_SESSION['msg']); } ?>
		<?php 

$Q="SELECT branch_ShowPolicy, branch_CustomerPaymentPolicy FROM adm_branch WHERE branch_id=$branch_id";

$Qr=mysqli_query($con,$Q);
$Qcount=mysqli_num_rows($Qr);
if($Qcount!==1)
{
	echo 'not noudddd';
?>

<?php
}
else
{
	$row=mysqli_fetch_assoc($Qr);
		?>
		<form  class="smart-form" method="post" id="client_form">	
			<fieldset>
			<div class="row" style="margin-bottom: 5px;">
            <table style="width:80%; background:#f1f1f1;" class="table table-bordered">
            	<tr>
                	<th style="width:70%;"><strong>Description</strong></th>
                    <th style="width:20%;"><strong>Show policy on Receipt</strong></th>
                    <th style="width:10%;"><input type="checkbox" name="branch_ShowPolicy"  class="form-control" style="height:20px;" <?php if($row['branch_ShowPolicy']==1) echo 'checked';;?> /></th>
                </tr>
                <tr>
                	<td colspan="3"><textarea name="policy" style="max-width:1000px; width:1000px; max-height:200px; height:200px;"><?=$row['branch_CustomerPaymentPolicy'];?></textarea></td>
                </tr>
            </table>
            </div>
	</fieldset>
			<footer>
				<input type="hidden" name="branch_id" value="<?php echo $branch_id; ?>">
				<input type="hidden" name="update" value="update">
				<p class="btn btn-primary" onclick="save_form();">Save</p>
			</footer>
		</form>
		<?php
		}
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

<?php
//include footer
include ("inc/google-analytics.php");
?>
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