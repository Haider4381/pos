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

$page_title = "Login Pass Code";
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
				<h2>Login Pass Code</h2>
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
			$passcode=validate_input($_POST['passcode']);
			 $q="UPDATE adm_branch
			 	SET
					branch_passcode='$passcode'
			 WHERE branch_id=$branch_id";
			if(mysqli_query($con,$q))
			{
				$_SESSION['msg']="Record Updated successfully";
				echo "<script>window.location='update_passcode.php'; </script>";
				die();
			}	

			else
			{
				$_SESSION['msg']="Problem Updating Recod";
			}
				
		}
/************************************
END OF DATA INSERTIONS
************************************/
?>
<?php if(!empty($_SESSION['msg'])){?>   <div class="alert alert-info"><?php echo $_SESSION['msg']; ?> </div> <?php unset($_SESSION['msg']); } ?>
		<?php 

$Q="SELECT branch_passcode FROM adm_branch WHERE branch_id=$branch_id";

$Qr=mysqli_query($con,$Q);
$Qcount=mysqli_num_rows($Qr);
if($Qcount!==1)
{
	echo 'not allowed';
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
                	<th style="width:70%;"><strong>Passcode</strong></th>
                </tr>
                <tr>
                	<td><input type="text" class="form-control" name="passcode" value="<?=$row['branch_passcode']?>" style="font-size: 18px;"></td>
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


<script type="text/javascript">
function save_form(){
		$("#client_form").submit();
	}
</script>