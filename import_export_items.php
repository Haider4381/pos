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

$page_title = "Import & Export Items";
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
				<h2>Import & Export Items</h2>
			</header>

			<!-- widget div-->
			<div>		

<?php if(!empty($_SESSION['msg'])){ echo $_SESSION['msg']; unset($_SESSION['msg']);} ?>
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
			
				
		}
/************************************
END OF DATA INSERTIONS
************************************/
?>

		
			
			<div class="row" style="margin-bottom: 5px;">
            <table style="width:100%; background:#f1f1f1;" class="table" border="0">
            	<tr>
            		<td>
            				<table style="width:70%; background:#f1f1f1;" class="table" border="0">
			            	<tr>
			                	<th style="width:50%;"><strong>Import Items</strong></th>
			                	<th style="width:50%;"><strong>Export Items</strong></th>
			                </tr>
			                <tr>
			                	<td>
			                		
			                                 <div id="upload">
			                                    <form enctype="multipart/form-data" action="csv.php?action=importitem" method="post">
			                                      <input type="file" data-filename-placement="inside" name="csv2">
			                                      <h3> <input type="submit" name="submit" value="Submit" class="btn btn-primary"/></h3>
			                                    </form>
			                                       
			                                 </div>
			                    </td>
			                    <td>
			                		
			                                <form action="csv.php?action=itemexport" method="post">       
			                                <h3> <input type="submit" href="db.csv" download="download" class="btn btn-success" value="Download"/></h3>
			                            </form>  
			                    </td>
			                </tr>
			            	</table>
			         </td>
			    
            		<td>
            				<table style="width:100%; background:#f1f1f1; margin-top: 55px;" class="" border="0">
			            	
			                <tr>
			                	<td>
			                		
			                	<?php 
			                	if(isset($_REQUEST['msg']))
			                	{
			                		if($_REQUEST['msg'] == "done")
			                		{
			                			echo "<h4>Items Imported Successfully</h4>";
			                		}
			                	}	 
			                      ?>          
			                    </td>
			                    
			                </tr>
			            	</table>
			         </td>
			    </tr>            

            </table>
           </div>
	
			
		
		
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


<script type="text/javascript">
function save_form(){
		$("#client_form").submit();
	}
</script>