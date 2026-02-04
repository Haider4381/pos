<?php
include('sessionCheck.php');
include('connection.php');
include('functions.php');
require_once ("inc/init.php");
require_once ("inc/config.ui.php");
$page_title = "Sale Return Report";
include ("inc/header.php");
//$page_nav["Settings"]["sub"]["Brands"]["active"] = true;
include ("inc/nav.php");
$branch_id=$_SESSION['branch_id'];
?>
<!-- ==========================CONTENT STARTS HERE ========================== -->
<!-- MAIN PANEL -->
<div id="main" role="main">

	<?php
		//configure ribbon (breadcrumbs) array("name"=>"url"), leave url empty if no url
		//$breadcrumbs["New Crumb"] => "http://url.com"
		$breadcrumbs["Sale Return Report"] = "";
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
				<span class="small_icon"><i class="fa fa-tags"></i>	</span>	
				<h2>Sale Return Report</h2>
			</header>

			<!-- widget div-->
			<div>


<form id="checkout-form" class="smart-form" novalidate="novalidate" method="post" action="saleReturnReport" target="_blank">	
			<fieldset>
				<div class="row" style="margin-bottom: 5px;">
					<div class="col col-lg-2">
						From Date:
					</div>
					<div class="col col-lg-4">
						<div class="input-group">
							<input type="text" name="from_date" value="<?php echo date('d-m-Y'); ?>" placeholder="Select a date" class="form-control datepicker" data-dateformat="dd-mm-yy">
							<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
						</div>
					</div>
				</div><!--End of row-->
				<div class="row" style="margin-bottom: 5px;">
					<div class="col col-lg-2">
						To Date:
					</div>
					<div class="col col-lg-4">
						<div class="input-group">
							<input type="text" name="to_date" value="<?php echo date('d-m-Y'); ?>" placeholder="Select a date" class="form-control datepicker" data-dateformat="dd-mm-yy">
							<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
						</div>
					</div>
				</div><!--End of row-->
				
                <div class="row" style="margin-bottom: 5px;">
					<div class="col col-lg-2">
						Customer:
					</div>
					<div class="col col-lg-4">
						<select class="select2" name="client_id">
						<option value="ALL">ALL</option>
						<?php 
								 $clientArray=get_ActiveClient();
								 foreach ($clientArray as $key => $clilentRow) { 
								?>
								<option value="<?php echo $clilentRow['client_id'];?>"><?=$clilentRow['client_Name']. ' / '. $clilentRow['client_Phone'];?></option>
								<?php } 
								?>
					</select>
					</div>
				</div><!--End of row-->
                
                <div class="row" style="margin-bottom: 5px;">
					<div class="col col-lg-2">
						Item:
					</div>
					<div class="col col-lg-4">
						<select class="select2" name="item_id">
						<option value="ALL">ALL</option>
						<?php 
                                $itemsArray=get_ActiveItems();
                                foreach ($itemsArray as $key => $itemRow) 
                                {
                                ?>
                                <option value="<?php echo $itemRow['item_id'];?>"><?php echo $itemRow['item_Name'].' / '.$itemRow['item_Code'];?></option>
                                <?php
                                }
                                ?>
					</select>
					</div>
				</div><!--End of row-->
                
				<div class="row" style="margin-bottom: 5px; visibility:hidden">
					<div class="col col-lg-2">
						Order by:
					</div>
					<div class="col col-lg-3">
						<input type="radio" name="orderby" value="date" checked="checked"> Date
						<input type="radio" name="orderby" value="item"> Item
						<input type="radio" name="orderby" value="salenumber"> Sale Number
					</div>
				</div><!--End of row-->
			</fieldset>
			<footer>
				<button type="submit" class="btn btn-primary" name="submit">Search </button>
			</footer>
		</form>


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
<?php include ("inc/footer.php");
 
?>
<!-- END PAGE FOOTER -->
<?php include ("inc/scripts.php");?>