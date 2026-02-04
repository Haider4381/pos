<?php
include('sessionCheck.php');
include 'connection.php';
include 'functions.php';
 
?>

<?php require_once ("inc/init.php");
 require_once ("inc/config.ui.php");
 
 $page_title = "Credit Note";
 include ("inc/header.php");
 //$page_nav["Payments"]["sub"]["Sale Payment"]["active"] = true;
 include ("inc/nav.php");
$u_id=$_SESSION['u_id'];
$branch_id=$_SESSION['branch_id'];
?>
<!-- ==========================CONTENT STARTS HERE ========================== -->
<!-- MAIN PANEL -->

<style type="text/css">

 .jarviswidget{

 margin-bottom: -2px !important;

 }

</style>
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
<div id="main" role="main">

 
<?php $breadcrumbs["New"] = "";
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
<span class="small_icon"><i class="fa fa-file-text-o"></i>	</span>	
<h2>Credit Note</h2>					
</header>

<!-- widget div-->
<div role="content">			
<!-- widget content -->
<div class="widget-body no-padding">

<br />
<ul class="nav nav-tabs" role="tablist" style="margin-left: 2px;">
<li role="presentation" class="active"><a href="#add" aria-controls="add" role="tab" data-toggle="tab" style="color:black !important">Add New</a></li>
<li role="presentation"><a href="#list" aria-controls="list" role="tab" data-toggle="tab" style="color:black !important">Lists</a></li>
</ul>
<div class="tab-content" >
<div role="tabpanel" class="tab-pane active" id="add" >
<br />

<?php $msg="";
 if(isset($_POST['submit'])) { $client_id=$_POST['client_id'];
 $sp_Amount=$_POST['sp_Amount'];
 $sp_Date=$_POST['sp_Date'];
 $sp_Description=$_POST['sp_Description'];
 $sp_Type='SC';
 $query_Insert="INSERT INTO adm_sale_payment (client_id,sp_Amount,sp_Date,sp_Description,sp_CreatedOn,sp_Type, u_id, branch_id) VALUES ($client_id,'$sp_Amount','$sp_Date','$sp_Description',now(),'$sp_Type', '$u_id', '$branch_id')";
 $query_Run= mysqli_query($con, $query_Insert);
	if($query_Run)
	{
		$sp_id=mysqli_insert_id($con);
		$_SESSION['msg']='<div class="alert alert-success">Credit Note Saved Successfully</div>';
		echo '<script>window.location="";</script>';
	
 } else { $_SESSION['msg']='<div class="alert alert-danger">Problem Saving Credit Note</div>';
 } } 
?>

<?php
if(!empty($_SESSION['msg'])){ echo $_SESSION['msg']; unset($_SESSION['msg']); } 
?>

<form id="checkout-form" name="checkout-form" method="post" onsubmit="return checkParameters();" action="" class="smart-form">	
<fieldset>
	<input type="hidden" name="sp_Date" class="form-control" value="<?php echo date('Y-m-d');?>">
 
			<table style="width:40%; background:#f1f1f1; float:left;" class="table table-condensed">
                	<tr><th colspan="4">Search Customer By Phone or Name</th></tr>
                    <tr>
                        <td colspan="4">
                            <select class="select2" name="client_id" id="client_id" >
                                <option selected value="0">Search Customer By Phone or Name</optio>					
							<?php $clientArray=get_ActiveClient();
                             foreach ($clientArray as $key => $clilentRow) { 
                            ?>
                            <option value="<?php echo $clilentRow['client_id'];?>"> <?php echo $clilentRow['client_Name']; ?> </option>					
                            <?php } 
                            ?>
                            </select>
                        </td>
					</tr>
                    <tr>
                    	<td>Credit Amount</td>
                        <td><input type="text" name="sp_Amount" class="form-control" placeholder="Enter Credit Amount"></td>
                    </tr>
                    <tr>
                    	<td>Description</td>
                        <td><input type="text" name="sp_Description" id="sp_Description" class="form-control" placeholder="Description About Credit"></td>
                    </tr>
                    
              </table>
              		 
 <div class="row" style="margin-bottom: 5px; visibility:hidden;">
 <div class="col col-lg-2">
 Payment Type
 </div>
 <div class="col col-lg-4">
 <select class="form-control" name="sp_Type">
 <option value="S">Sale Payment</option>
 <option value="SR">Sale Return Payment</option>
 </select>
 </div>
 </div>
</fieldset>
<footer>
 <!--<input type="submit" class="btn btn-primary" name="submit">Save </p>-->
 <input type="submit" name="submit" class="btn btn-sm btn-primary" value="Save">
</footer>
</form>
</div><!--End of div id="add"-->

<div role="tabpanel" class="tab-pane" id="list">
<!-- Widget ID (each widget will need unique ID)-->
<div class="jarviswidget jarviswidget-color-blueDark" id="wid-id-1" data-widget-editbutton="false">

<!-- widget div-->
<div>
<!-- widget content -->
<div class="widget-body no-padding">

<table id="datatable_fixed_column" class="table table-striped table-bordered" width="100%">

<thead>
<tr>
<th class="hasinput" style="width:17%">
<input type="text" class="form-control" placeholder="Customer" />
</th>
<th class="hasinput" style="width:18%">
<input class="form-control" placeholder="Credit" type="text">
</th>
<th class="hasinput" style="width:16%">
<input type="text" class="form-control" placeholder="Date" />
</th>
<th class="hasinput" style="width:17%">
<input type="text" class="form-control" placeholder="Description" />
</th>
</tr>
<tr>
<th data-class="expand">Customer</th>
<th>Credit</th>
<th data-hide="phone">Date</th>
<th data-hide="phone">Description</th>

</tr>
</thead>
<tbody>

<?php
$select_All="SELECT SP.sp_id, SP.client_id, SP.sp_Amount, SP.s_id, SP.sp_Date, SP.sp_Description, SP.sp_CreatedOn,C.client_Name
 FROM adm_sale_payment AS SP
 LEFT JOIN adm_client AS C on C.client_id=SP.client_id
 WHERE SP.sp_Type='SC' AND SP.branch_id=$branch_id";
 $select_All_Run= mysqli_query($con, $select_All);
 while ($row_report = mysqli_fetch_array($select_All_Run)) { 
?>
 <tr>
<td><?php echo $row_report['client_Name']?></td>
<td><?php echo $row_report['sp_Amount']?></td>
<td><?php echo $row_report['sp_Date']?></td>
<td><?php echo $row_report['sp_Description']?></td>

</tr>

<?php } 
?>
	
</tbody>
</table>

</div> 	<!-- end widget body -->
</div>
</div><!-- end div id="wid-id-1" -->
</div><!--End of div id="list"-->

</div> <!--End of tab content-->
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

<?php include ("inc/footer.php");
 
?>
<!-- END PAGE FOOTER -->


<?php include ("inc/scripts.php");
 
?>

<!-- PAGE RELATED PLUGIN(S) -->
<script src="<?php echo ASSETS_URL;?>/js/plugin/datatables/jquery.dataTables.min.js"></script>
<script src="<?php echo ASSETS_URL;?>/js/plugin/datatables/dataTables.colVis.min.js"></script>
<script src="<?php echo ASSETS_URL;?>/js/plugin/datatables/dataTables.tableTools.min.js"></script>
<script src="<?php echo ASSETS_URL;?>/js/plugin/datatables/dataTables.bootstrap.min.js"></script>
<script src="<?php echo ASSETS_URL;?>/js/plugin/datatable-responsive/datatables.responsive.min.js"></script>
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
tablet: 1024,
phone: 480
};




/* END BASIC */

/* COLUMN FILTER */
var otable = $('#datatable_fixed_column').DataTable({
//"bFilter": false,
//"bInfo": false,
//"bLengthChange": false
//"bAutoWidth": false,
//"bPaginate": false,
//"bStateSave": true // saves sort state using localStorage
"sDom": "<'dt-toolbar'<'col-xs-12 col-sm-6 hidden-xs'f><'col-sm-6 col-xs-12 hidden-xs'<'toolbar'>>r>" +
"t" +
"<'dt-toolbar-footer'<'col-sm-6 col-xs-12 hidden-xs'i><'col-xs-12 col-sm-6'p>>",
"autoWidth": true,
"preDrawCallback": function() {
// Initialize the responsive datatables helper once.
if (!responsiveHelper_datatable_fixed_column) {
responsiveHelper_datatable_fixed_column = new ResponsiveDatatablesHelper($('#datatable_fixed_column'), breakpointDefinition);

}
},
"rowCallback": function(nRow) {
responsiveHelper_datatable_fixed_column.createExpandIcon(nRow);

},
"drawCallback": function(oSettings) {
responsiveHelper_datatable_fixed_column.respond();

}

});


// custom toolbar
$("div.toolbar").html('<div class="text-right"></div>');


// Apply the filter
$("#datatable_fixed_column thead th input[type=text]").on('keyup change', function() {

otable
.column($(this).parent().index() + ':visible')
.search(this.value)
.draw();


});

/* END COLUMN FILTER */


})

function checkParameters(){
	var sp_Description = $.trim($("#sp_Description").val());

	if (sp_Description == "")
	{
		$.smallBox({
		title : "Error",
		content : "<i class='fa fa-clock-o'></i> <i>Description is mandatory field.</i>",
		color : "#C46A69",
		iconSmall : "fa fa-times fa-2x fadeInRight animated",
		timeout : 4000
		});
		
	$("#sp_Description").focus();

	return false;

	}
}
</script>