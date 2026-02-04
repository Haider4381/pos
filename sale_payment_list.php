<?php
include('sessionCheck.php');
include 'connection.php';
require_once ("inc/init.php");
require_once ("inc/config.ui.php");
$page_title = "Bill Payment";
include ("inc/header.php");
//$page_nav["Payments"]["sub"]["Sale Payment"]["active"] = true;
 include ("inc/nav.php");
 
?>
<!-- ==========================CONTENT STARTS HERE ========================== -->
<!-- MAIN PANEL -->

<style type="text/css">
.jarviswidget{ margin-bottom: -2px !important;}
.payment_history_table
{
	width:100%;
}
.payment_history_table tr {border:1px solid #666; border-collapse:collapse;}
.payment_history_table tr th {border:1px solid #666; border-collapse:collapse; padding:2px; color:#FFF; font-size:15px;}
.payment_history_table tr td {border:1px solid #666; border-collapse:collapse; padding:2px;}

</style>
<div id="main" role="main">

 
<?php $breadcrumbs["Bill Payment"] = "";
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
<article class="col-sm-12 col-md-12 col-lg-12 sortable-grid ui-sortable">

<div class="jarviswidget" id="wid-id-0">
<header>
<span class="small_icon"><i class="fa fa-money"></i>	</span>	
<h2>Bill Payment List</h2>					
</header>

<!-- widget div-->
<div role="content">			
<!-- widget content -->
<div class="widget-body no-padding">

<div class="tab-content" >
<!-- Widget ID (each widget will need unique ID)-->
<div class="jarviswidget jarviswidget-color-blueDark" id="wid-id-1" data-widget-editbutton="false">

<!-- widget div-->
<div>
<!-- widget content -->
<div class="widget-body no-padding">

<table id="datatable_fixed_column" class="table table-striped table-bordered" width="100%">

<thead>
<tr>
<th class="hasinput" style="width:16%">
<input type="text" class="form-control datepicker" data-dateformat="dd-mm-yy" placeholder="Pay Amount Date" />
</th>
<th class="hasinput" style="width:17%">
<input type="text" class="form-control" placeholder="Bill#" />
</th>
<th class="hasinput" style="width:17%">
<input type="text" class="form-control" placeholder="Client Name" />
</th>
<th class="hasinput" style="width:18%">
<input class="form-control" placeholder="Pay Amount" type="text">
</th>

<th class="hasinput" style="width:17%">
<input type="text" class="form-control" placeholder="Description" />
</th>
<th></th>
</tr>
<tr>
<th data-hide="phone">Date</th>
<th data-class="expand">Bill#</th>
<th data-class="expand">Customer Name</th>
<th>Pay Amount</th>
<th data-hide="phone">Description</th>
<th>Action</th>
</tr>
</thead>
<tbody>

<?php 
$select_All="SELECT S.s_Number, SP.sp_id, SP.client_id,SP.sp_Type, SP.sp_Amount, SP.s_id, SP.sp_Date, SP.sp_Description, SP.sp_CreatedOn,C.client_Name
 FROM adm_sale_payment AS SP
 LEFT JOIN adm_client AS C on C.client_id=SP.client_id
 left outer JOIN cust_sale as S ON S.s_id=SP.s_id
 WHERE SP.branch_id=$branch_id";
 $select_All_Run= mysqli_query($con, $select_All);
while ($row_report = mysqli_fetch_array($select_All_Run))
{
	$s_id= $row_report['s_id'];
?>
 <?php echo "<tr id='row".$row_report['sp_id']."'>";?>
<td><?php echo validate_date_display($row_report['sp_Date'])?></td>
<td><?php if($row_report['sp_Type']=='S') { echo $row_report['s_Number']; }
else
{
	$SSQ=mysqli_query($con,"SELECT sr_Number FROM `cust_salereturn` WHERE sr_id='$s_id'");
	$SSQQ=mysqli_fetch_assoc($SSQ);
	echo $salereturn_number=$SSQQ['sr_Number'];
}?></td>
<td><?php echo $row_report['client_Name']?></td>
<td><?php echo $currency_symbol.$row_report['sp_Amount']?></td>
<td><?php echo $row_report['sp_Description']?></td>
<td><a href="javascript:del(<?php echo $row_report['sp_id'];?>)" class="btn btn-danger btn-xs">Delete</a></td>
</tr>

<?php } 
?>
	
</tbody>
</table>

</div> 	<!-- end widget body -->
</div>
</div><!-- end div id="wid-id-1" -->

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

<?php include ("inc/footer.php");?>
<!-- END PAGE FOOTER -->


<?php include ("inc/scripts.php");?>

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
"order": [[ 0, 'desc' ]],
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

function calculateEndBill()
{
	var sp_Amount = $.trim($("#sp_Amount").val());
	var current_balance = $.trim($("#show_client_balance_input").val());
	var end_balance=parseInt(current_balance) - parseInt(sp_Amount);
	$("#show_client_billamount").html(sp_Amount);
	$("#show_client_endbalance").html(end_balance);
	//$("#sp_Description").focus();

	return false;
}




function getClientBalance(val)
{
	var client_id=$("#client_id").val();
	var allVars="client_id="+client_id;
	//var allVars='item_id='+val;
	//alert(client_id);

	$.ajax
		({
		type: "post",
		 url: "getAjaxClientBalance.php",
		 dataType: 'json',
		 data:allVars,
		 cache: false,
		 success: function(data)
		 {
			 $("#show_client_balance").html(data.client_balance);
			 $("#show_client_balance_input").val(data.client_balance);
			 $("#payment_history_table11").html(data.client_payment_history);
		 },
		 error:function(data)
		 {
		 	alert("Please Choose Customer");

		 }
		});
}

function del(val){

 $.SmartMessageBox({
 title : "Attention required!",
 content : "This is a confirmation box. Do you want to delete the Record?",
 buttons : '[No][Yes]'
 }, function(ButtonPressed) {
 if (ButtonPressed === "Yes") {


$.post("ajax/delAjax.php",
 {
 sp_id : val, 
 },
 function(data,status){ 
 if(data.trim()!="")
 {
	 document.getElementById('row'+val).style.display= "none";

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