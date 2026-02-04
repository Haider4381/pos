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

$page_title = "Bill Payment Reminder";
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
				<h2>Bill Payment Reminder</h2>
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
if(isset($_POST['submit'])){
	
			/*
			$pe_mailbody=validate_input($_POST['promo_message']);
			$pe_mailsubject=validate_input($_POST['promo_subject']);
			$q="INSERT INTO `adm_promotion_emails`(`pe_id`, `pe_mailsubject`, `pe_mailbody`, `u_id`, `branch_id`, `pe_createdat`)
			 								VALUES('', '$pe_mailsubject', '$pe_mailbody', '$u_id', '$branch_id', now())";
			if(mysqli_query($con,$q))
			{
				$pe_id=mysqli_insert_id($con);
				$_SESSION['msg']="Payment Policy Updated successfully";
				
				$emailQ="select client_id, client_Email from adm_client where branch_id=$branch_id";
				$emailQr=mysqli_query($emailQ);
				while($row=mysqli_fetch_assoc($emailQr))
				{
					$client_id=$row['client_id'];
					$client_Email=$row['client_Email'];
					if(!empty($client_Email))
					{
						$send_email=mail($client_Email,$pe_mailsubject,$pe_mailbody);
						if($send_email)
						{
							$mail_log="INSERT INTO `adm_promotion_emailsdetail`
							(`ped_id`, `pe_id`, `ped_mailsubject`, `ped_mailbody`, `u_id`, `branch_id`, `ped_createdat`, `client_id`, `client_email`, `ped_send`)
							VALUES
							('', '$pe_id', '$ped_mailsubject', '$ped_mailbody', '$u_id', '$branch_id', now(), '$client_id', '$client_email', 'yes')
							";
							mysqli_query($_con,$mail_log);
						}
						else
						{
							$mail_log="INSERT INTO `adm_promotion_emailsdetail`
							(`ped_id`, `pe_id`, `ped_mailsubject`, `ped_mailbody`, `u_id`, `branch_id`, `ped_createdat`, `client_id`, `client_email`, `ped_send`)
							VALUES
							('', '$pe_id', '$ped_mailsubject', '$ped_mailbody', '$u_id', '$branch_id', now(), '$client_id', '$client_email', 'no')
							";
							mysqli_query($_con,$mail_log);
						}
					}
				}
			}	

			else
			{
				$_SESSION['msg']="Problem In Sending Bill Payment Reminder";
			}*/
				
$_SESSION['msg']="Problem In Sending Bill Payment Reminder";
		}


/************************************
END OF DATA INSERTIONS
************************************/
?>
<?php if(!empty($_SESSION['msg'])){?>   <div class="alert alert-info"><?php echo $_SESSION['msg']; ?> </div> <?php unset($_SESSION['msg']); } ?>

<form id="checkout-form" name="checkout-form" method="post" onsubmit="return checkParameters();" action="" class="smart-form">
			<fieldset>
			<div class="row" style="margin-bottom: 5px;">
            <table style="width:80%; background:#f1f1f1;" class="table table-bordered">
            	<tr>
                	<th style="width:70%;"><strong>Select Customer</strong></th>
                    <th>
                    	<select class="select2" name="client_id" onchange="getClientBalance()" id="client_id">
                          <option value="0">Search Customer</option>
                          <?php
                          $client_query = "SELECT client_id,client_Name,client_Phone FROM adm_client WHERE client_Status='A' AND branch_id=$branch_id";
                          $client_run = mysqli_query($con, $client_query);
                          while ($clientRow = mysqli_fetch_assoc($client_run))
                          { ?>
                          <option value="<?php echo $clientRow['client_id'] ?>"><?php echo $clientRow['client_Name'].' / '.$clientRow['client_Phone'] ?></option>
                          <?php } ?>
                      
                       </select>
                    </th>
                </tr>
                <tr>
                	<th style="width:70%;"><strong>Description</strong></th>
                	<td colspan="3"><textarea name="promo_message" id="promo_message" style="max-width:1000px; width:1000px; max-height:200px; height:200px;" placeholder="Insert data here"></textarea></td>
                </tr>
                
                <tr>
                	<td colspan="2" style="text-align:right;">
               			<input type="submit" name="submit" class="btn btn-sm btn-primary"  style="margin:5px;" value="Send Payment Reminder">
                	</td>
                </tr>
            </table>
        	</div>
	</fieldset>
		
		</form>
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
	function save_form(){
		$("#client_form").submit();
	}

function checkParameters(){
	var promo_message = $.trim($("#promo_message").val());
	var promo_subject = $.trim($("#promo_subject").val());

	
	if (promo_message == "")
	{
		$.smallBox({
		title : "Error",
		content : "<i class='fa fa-clock-o'></i> <i>Please Fill Email Body.</i>",
		color : "#C46A69",
		iconSmall : "fa fa-times fa-2x fadeInRight animated",
		timeout : 4000
		});
	$("#promo_message").focus();
	return false;
	}
}
</script>