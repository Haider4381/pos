<?php
include('sessionCheck.php');
 include('connection.php');
 include('functions.php');
 require_once ("inc/init.php");
 require_once ("inc/config.ui.php");
 $page_title = "Vendor";
 include ("inc/header.php");
//$page_nav["Settings"]["sub"]["Suppliers"]["active"] = true;
 include ("inc/nav.php");
 
?>
<!-- ==========================CONTENT STARTS HERE ========================== -->
<!-- MAIN PANEL -->
<div id="main" role="main">

	
<?php $breadcrumbs["New"] = "";
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
				<h2>Vendor</h2>
			</header>

			<!-- widget div-->
			<div>		


				<!-- widget content -->
				<div class="widget-body no-padding">

<?php if(isset($_POST['save'])){ $sup_Name=test_input($_POST['sup_Name']);
 $sup_Email=test_input($_POST['sup_Email']);
 $sup_Phone=test_input($_POST['sup_Phone']);
 $sup_Address=test_input($_POST['sup_Address']);
 $sup_Status=test_input($_POST['sup_Status']);
 $sup_Remarks=test_input($_POST['sup_Remarks']);
 $brand_idArray=$_POST['brand_id'];
 $ass_DiscountPercentageArray=$_POST['ass_DiscountPercentage'];
 $ass_DiscountPercentage=array_filter($ass_DiscountPercentageArray);
 $q="INSERT INTO adm_supplier (sup_Name,sup_Email,sup_Phone,sup_Address,sup_Status,sup_Remarks,u_id, branch_id)
 VALUES('$sup_Name','$sup_Email','$sup_Phone','$sup_Address','$sup_Status','$sup_Remarks', '$u_id', '$branch_id')";
 if(mysqli_query($con,$q)) { $sup_id=mysqli_insert_id($con);
 foreach ($ass_DiscountPercentageArray as $key => $assRow) { $brand_id=$brand_idArray[$key];
 $ass_DiscountPercentage=$ass_DiscountPercentageArray[$key];
 if((!empty($brand_id)) && (!empty($ass_DiscountPercentage))) { $acsQ="INSERT INTO adm_supplier_scheme(sup_id, brand_id, ass_DiscountPercentage) VALUES ($sup_id,$brand_id,'$ass_DiscountPercentage')";
 mysqli_query($con,$acsQ);
 } } $_SESSION['msg']="Vendor Created successfully";
 } else { $_SESSION['msg']="Problem creating Vendor";
 } echo '<script> window.location="";
 </script>';
 die();
 } if(isset($_POST['update'])){ $sup_id=test_input($_POST['sup_id']);
 $sup_Name=test_input($_POST['sup_Name']);
 $sup_Email=test_input($_POST['sup_Email']);
 $sup_Phone=test_input($_POST['sup_Phone']);
 $sup_Address=test_input($_POST['sup_Address']);
 $sup_Status=test_input($_POST['sup_Status']);
 $sup_Remarks=test_input($_POST['sup_Remarks']);
 $brand_idArray=$_POST['brand_id'];
 $ass_DiscountPercentageArray=$_POST['ass_DiscountPercentage'];
 $ass_DiscountPercentage=array_filter($ass_DiscountPercentageArray);
 $q="UPDATE `adm_supplier` SET `sup_Name`='$sup_Name',`sup_Phone`='$sup_Phone',`sup_Email`='$sup_Email',`sup_Status`='$sup_Status',`sup_Remarks`='$sup_Remarks',`sup_Address`='$sup_Address' WHERE sup_id=$sup_id";
 if(mysqli_query($con,$q)) { $_SESSION['msg']="Vendor Updated successfully";
 $acsDelQ="DELETE FROM adm_supplier_scheme WHERE sup_id=$sup_id";
 mysqli_query($con,$acsDelQ);
 foreach ($ass_DiscountPercentageArray as $key => $assRow) { $brand_id=$brand_idArray[$key];
 $ass_DiscountPercentage=$ass_DiscountPercentageArray[$key];
 if((!empty($brand_id)) && (!empty($ass_DiscountPercentage))) { $acsQ="INSERT INTO adm_supplier_scheme(sup_id, brand_id, ass_DiscountPercentage) VALUES ($sup_id,$brand_id,'$ass_DiscountPercentage')";
 mysqli_query($con,$acsQ);
 } } echo "<script>window.location='supplier_add.php';
 </script>";
 die();
 } else { $_SESSION['msg']="Problem Updating Supplier";
 } } 
?>

<?php if(!empty($_SESSION['msg'])){?> <div class="alert alert-info"><?php echo $_SESSION['msg']; ?> </div> 
<?php unset($_SESSION['msg']); } ?>
		
<?php if(!isset($_GET['id'])) { 
?>
			<form id="supplier_form" class="smart-form" method="post"  onsubmit="return checkParameters();">	
			<fieldset>
			<div class="row" style="margin-bottom: 5px;">
			<div class="col col-lg-2">
				<div class="col col-lg-12 col-sm-12 col-xs-12">
					<label>Name:</label>
				</div>
			</div>
			<div class="col col-lg-3">
				<div class="col col-lg-12 col-sm-12 col-xs-12">
					<input type="text" name="sup_Name" id="sup_Name" class="form-control" placeholder="Vendor / Supplier Name">
				</div>
			</div>

			<div class="col col-lg-2">
				<div class="col col-lg-12 col-sm-12 col-xs-12">
					<label>Email:</label>
				</div>
			</div>
			<div class="col col-lg-3">
				<div class="col col-lg-12 col-sm-12 col-xs-12">
					<input type="text" name="sup_Email" class="form-control" placeholder="Vendor / Supplier Email">
				</div>
			</div>
		</div>
			

		<!--2nd row start here-->

		<div class="row" style="margin-bottom: 5px;">
			<div class="col col-lg-2">
				<div class="col col-lg-12 col-sm-12 col-xs-12">
					<label>Phone:</label>
				</div>
			</div>
			<div class="col col-lg-3">
				<div class="col col-lg-12 col-sm-12 col-xs-12">
					<input type="text" name="sup_Phone" class="form-control" placeholder="Vendor / Supplier Phone">
				</div>
			</div>
			<div class="col col-lg-2">
				<div class="col col-lg-12 col-sm-12 col-xs-12">
					<label>Status:</label>
				</div>
			</div>
			<div class="col col-lg-3">
				<div class="col col-lg-12 col-sm-12 col-xs-12">
					<select name="sup_Status" class="form-control"> 
						<option value="A">Active</option>
						<option value="I">In-Active</option>
					</select>
				</div>
			</div>
		</div>



		<!--3rd row start here-->

		<div class="row" style="margin-bottom: 5px;">
			<div class="col col-lg-2">
				<div class="col col-lg-12 col-sm-12 col-xs-12">
					<label>Address:</label>
				</div>
			</div>
			<div class="col col-lg-8">
				<div class="col col-lg-12 col-sm-12 col-xs-12">
					<textarea name="sup_Address" class="form-control" placeholder="Vendor / Supplier Address"></textarea> 
				</div>
			</div>
			<div class="col col-lg-3">
			</div>	
		</div>
					<!--5th row start here-->

			<div class="row" style="margin-bottom: 5px;">
				<div class="col col-lg-2">
					<div class="col col-lg-12">
						<label>Notes:</label>
					</div>
				</div>
				<div class="col col-lg-8">
					<div class="col col-lg-12">
					<textarea name="sup_Remarks" class="form-control" placeholder="Comments About Vendor / Supplier"></textarea> 
					</div>
				</div>
			</div>	
 
 
			<div class="row alert alert-info" style="margin:0; display:none;">
				<div class="col col-lg-3">
					Brand
					<br>
					<select class="select2" id="item">
						
<?php $brandArray=get_ActiveBrands();
 foreach ($brandArray as $key => $brandRow) { 
?>
						<option value="<?php echo $brandRow['brand_id'];?>"><?php echo $brandRow['brand_Name'];?></option><?php } ?>
					</select>
				</div>
				<div class="col col-lg-3">
					Discount % 
					<br>
					<input type="number" name="" id="discount" class="form-control">
				</div>
				<div class="col col-lg-2">
				<br>
					<p class="btn btn-primary" onclick="addToTable()">Add To Table</p>
				</div>
			</div><!--End of row-->
			<div class="row" style="margin: 0">
				<div class="col-lg-8">
				<table class="table table-bordered">
					<tr class="info" style="display:none;">
						<th>Brand</th>
						<th>Discount</th>
						<th>Action</th>
					</tr>
					<tr id="copyRow" style="display: none;">
						<td>
							<span class="item"></span>
							<input type="hidden" name="brand_id[]" class="brand_id">
						</td>
						<td class="discount">
							<input type="text" name="ass_DiscountPercentage[]" class="ass_DiscountPercentage form-control">
						</td>
						<td>
							<p class="btn btn-danger btn-xs" onclick="removeTr(this)">REMOVE</p>
						</td>
					</tr>
				</table>
				<table class="table table-bordered" id="copyTable">
					
				</table>
				</div>
			</div><!--End of row--> 


					</fieldset>
					<footer>
						<input type="hidden" name="save" value="save">
						<p class="btn btn-primary" onclick="save_form();">Save</p>
					</footer>
				</form>
		
<?php
} else {
	$sup_id=(int) $_GET['id'];
 $SupplierQ="SELECT `sup_id`, `sup_Name`, `sup_Phone`, `sup_Email`, `sup_Status`, `sup_Remarks`, `sup_Address` FROM `adm_supplier` WHERE sup_id=$sup_id";
 $SupplierRow=mysqli_fetch_assoc(mysqli_query($con,$SupplierQ));
 
?>
		<form id="supplier_form" class="smart-form" method="POST" onsubmit="return checkParameters();">	
			<fieldset>
			<div class="row" style="margin-bottom: 5px;">
			<div class="col col-lg-2">
				<div class="col col-lg-12 col-sm-12 col-xs-12">
					<label>Name:</label>
				</div>
			</div>
			<div class="col col-lg-3">
				<div class="col col-lg-12 col-sm-12 col-xs-12">
					<input type="text" name="sup_Name" id="sup_Name" class="form-control" value="<?php echo $SupplierRow['sup_Name'];?>">
				</div>
			</div>

			<div class="col col-lg-2">
				<div class="col col-lg-12 col-sm-12 col-xs-12">
					<label>Email:</label>
				</div>
			</div>
			<div class="col col-lg-3">
				<div class="col col-lg-12 col-sm-12 col-xs-12">
					<input type="text" name="sup_Email" class="form-control" value="<?php echo $SupplierRow['sup_Email'];?>">
				</div>
			</div>
		</div>
			

		<!--2nd row start here-->

		<div class="row" style="margin-bottom: 5px;">
			<div class="col col-lg-2">
				<div class="col col-lg-12 col-sm-12 col-xs-12">
					<label>Phone:</label>
				</div>
			</div>
			<div class="col col-lg-3">
				<div class="col col-lg-12 col-sm-12 col-xs-12">
					<input type="text" name="sup_Phone" class="form-control" value="<?php echo $SupplierRow['sup_Phone'];?>">
				</div>
			</div>
			<div class="col col-lg-2">
				<div class="col col-lg-12 col-sm-12 col-xs-12">
					<label>Status:</label>
				</div>
			</div>
			<div class="col col-lg-3">
				<div class="col col-lg-12 col-sm-12 col-xs-12">
					<select name="sup_Status" class="form-control"> 
						<option value="A" <?php if($SupplierRow['sup_Status']=='A'){ echo"selected='selected'"; } ?>>Active</option>
						<option value="I" <?php if($SupplierRow['sup_Status']=='I'){ echo "selected='selected'";}?>>In-Active</option>
					</select>
				</div>
			</div>
		</div>
		<!--4th row start here-->

		<div class="row" style="margin-bottom: 5px;">
			<div class="col col-lg-2">
				<div class="col col-lg-12 col-sm-12 col-xs-12">
					<label>Address:</label>
				</div>
			</div>
			<div class="col col-lg-8">
				<div class="col col-lg-12 col-sm-12 col-xs-12">
					<textarea name="sup_Address" class="form-control"><?php echo $SupplierRow['sup_Address'];?></textarea> 
				</div>
			</div>
			<div class="col col-lg-3">
			
			</div>
			
		</div>
		
					<!--5th row start here-->

			<div class="row" style="margin-bottom: 5px;">
				<div class="col col-lg-2">
					<div class="col col-lg-12">
						<label>Notes:</label>
					</div>
				</div>
				<div class="col col-lg-8">
					<div class="col col-lg-12">
					<textarea name="sup_Remarks" class="form-control"><?php echo $SupplierRow['sup_Remarks'];?></textarea> 
					</div>
				</div>
			</div>


			<div class="row alert alert-info" style="margin:0; display:none;">
				<div class="col col-lg-3">
					Brand
					<br>
					<select class="select2" id="item">
						
<?php $brandArray=get_ActiveBrands();
 foreach ($brandArray as $key => $brandRow) { 
?>
						<option value="<?php echo $brandRow['brand_id'];?>"><?php echo $brandRow['brand_Name'];?></option>
<?php } 
?>
					</select>
				</div>
				<div class="col col-lg-3">
					Discount % 
					<br>
					<input type="number" name="" id="discount" class="form-control">
				</div>
				<div class="col col-lg-2">
				<br>
					<p class="btn btn-primary" onclick="addToTable()">Add To Table</p>
				</div>
			</div><!--End of row-->
			<div class="row" style="margin: 0">
				<div class="col-lg-8">
				<table class="table table-bordered">
					<tr class="info" style="display:none;">
						<th>Brand</th>
						<th>Discount</th>
						<th>Action</th>
					</tr>
					<tr id="copyRow" style="display: none;">
						<td>
							<span class="item"></span>
							<input type="hidden" name="brand_id[]" class="brand_id">
						</td>
						<td class="discount">
							<input type="text" name="ass_DiscountPercentage[]" class="ass_DiscountPercentage form-control">
						</td>
						<td>
							<p class="btn btn-danger btn-xs" onclick="removeTr(this)">REMOVE</p>
						</td>
					</tr>
				</table>
				<table class="table table-bordered" id="copyTable">
					
<?php
$acsQ="SELECT ASS.ass_id, ASS.sup_id, ASS.brand_id, ASS.ass_DiscountPercentage ,B.brand_Name
						FROM adm_supplier_scheme AS ASS 
						LEFT JOIN adm_brand AS B ON B.brand_id=ASS.brand_id
						WHERE ASS.sup_id=$sup_id";
 $acsRes=mysqli_query($con,$acsQ);
 $acsRowArray=array();
 if(mysqli_num_rows($acsRes)>0) { while($r=mysqli_fetch_assoc($acsRes)) { $acsRowArray[]=$r;
 } } foreach ($acsRowArray as $key => $acsRow) { 
?>
						<tr>
							<td>
								<span class="item"><?php echo $acsRow['brand_Name'];?></span>
								<input type="hidden" name="brand_id[]" class="brand_id" value="<?php echo $acsRow['brand_id'];?>">
							</td>
							<td class="discount">
								<input type="text" name="ass_DiscountPercentage[]" class="ass_DiscountPercentage form-control" value="<?php echo $acsRow['ass_DiscountPercentage'];?>">
							</td>
							<td>
								<p class="btn btn-danger btn-xs" onclick="removeTr(this)">REMOVE</p>
							</td>
						</tr>
					
<?php } 
?>
				</table>
				</div>
			</div><!--End of row-->


			</fieldset>
			<footer>
				<input type="hidden" name="sup_id" value="<?php echo $SupplierRow['sup_id'];?>">
 <input type="hidden" name="update" value="update" />
				<p class="btn btn-primary" onclick="save_form();">Save</p>
			</footer>
		</form>
		
<?php } 
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

<?php include ("inc/footer.php");
 
?>
<!-- END PAGE FOOTER -->


<?php include ("inc/scripts.php");
 
?>

<!-- PAGE RELATED PLUGIN(S) -->
<script src="
<?php echo ASSETS_URL;
 
?>/js/plugin/datatables/jquery.dataTables.min.js"></script>
<script src="
<?php echo ASSETS_URL;
 
?>/js/plugin/datatables/dataTables.colVis.min.js"></script>
<script src="
<?php echo ASSETS_URL;
 
?>/js/plugin/datatables/dataTables.tableTools.min.js"></script>
<script src="
<?php echo ASSETS_URL;
 
?>/js/plugin/datatables/dataTables.bootstrap.min.js"></script>
<script src="
<?php echo ASSETS_URL;
 
?>/js/plugin/datatable-responsive/datatables.responsive.min.js"></script>

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

	/* BASIC ;
*/
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
	
	/* COLUMN FILTER */
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
 /* $("div.toolbar").html('<div class="text-right"><img src="img/logo.png" alt="SmartAdmin" style="width: 111px;
 margin-top: 3px;
 margin-right: 10px;
"></div>');
*/
 	 
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
function save_form()
{
	$("#supplier_form").submit();
}

function checkParameters(){
	var sup_Name = $.trim($("#sup_Name").val());
	if (sup_Name == '')
	{
		$.smallBox({
		title : "Error",
		content : "<i class='fa fa-clock-o'></i> <i>Please Fill Name.</i>",
		color : "#C46A69",
		iconSmall : "fa fa-times fa-2x fadeInRight animated",
		timeout : 4000
		});
	$("#sup_Name").focus();
	return false;
	}
}
</script>