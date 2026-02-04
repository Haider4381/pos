<?php
include('sessionCheck.php');
include('connection.php');
include('functions.php');
require_once ("inc/init.php");
require_once ("inc/config.ui.php");
$page_title = "Expenses List";
include ("inc/header.php");
//$page_nav["Settings"]["sub"]["Brands"]["active"] = true;
include ("inc/nav.php");
$branch_id=$_SESSION['branch_id'];
?>
<!-- ==========================CONTENT STARTS HERE ========================== -->
<!-- MAIN PANEL -->
<div id="main" role="main">

	
<?php $breadcrumbs["List"] = "";
 include("inc/ribbon.php");
 
?>

<style>
     .form-control {
    border-radius: 5px !important;
    box-shadow: none!important;
    -webkit-box-shadow: none!important;
    -moz-box-shadow: none!important;
    font-size: 12px;
    padding-left: 6px;

     }
     .select2-container .select2-choice {
   
    border-radius: 5px;
   
}
label {
   
    margin-top: 8px !important;
}
textarea.form-control {
    height: 70px;
}
select {
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
    border-radius: 5px !important;
    width: 400px;
}
b, strong {
    font-weight: 500;
}
.btn-group-xs>.btn, .btn-xs {
    padding: 1px 5px;
    font-size: 12px;
    line-height: 2.0;
    border-radius: 2px;
}
.dataTables_filter .input-group-addon+.form-control {
    display: none;
}
.dataTables_filter .input-group-addon {
    display: none;
    }
.btn-sm {
    padding: 2px 4px 1px;
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
			<span class="small_icon"><i class="fa fa-file-text-o"></i>	</span>				
				<h2>Expenses List</h2>
			</header>

			<!-- widget div-->
			<div>		


				<!-- widget content -->
				<div class="widget-body no-padding">
	<br>

<div class="tab-content" >
	<table id="datatable_fixed_column" class="table table-striped table-bordered" width="100%">
	<thead>
		<tr>
			<th class="hasinput" style="width:15%"> <input type="text" class="form-control datepicker" data-dateformat="dd-mm-yy" placeholder="Filter Date" /></th>
            <th class="hasinput" style="width:16%"><input type="text" class="form-control" placeholder="Payee Name" /></th>
            <th class="hasinput" style="width:16%"><input type="text" class="form-control" placeholder="Expense Amount" /></th>
            <th class="hasinput" style="width:16%"><input type="text" class="form-control" placeholder="Expense Note" /></th>
			<th class="hasinput" style="width:16%"></th>
		</tr>
		<tr>
			<th data-class="expand" style="text-align:center;">Date</th>
            <th style="text-align:center;">Payee Name</th>
            <th style="text-align:center;">Expense Amount</th>
            <th style="text-align:center;">Expense Note</th>
			<th data-hide="phone" style="text-align:center;">Action</th>
		</tr>
	</thead>
				 
<?php
$empQ = "SELECT `expense_id`, `expense_date`, `expense_amount`, `expense_notes`, `expense_createdat`, payee_name
FROM `adm_expenses`
INNER JOIN adm_payee on adm_payee.payee_id=adm_expenses.payee_id
WHERE adm_expenses.branch_id=$branch_id";
$empRes = mysqli_query($con,$empQ);
while($empRow = mysqli_fetch_assoc($empRes))
{
?>
				 	<tr id="row<?php echo $empRow['expense_id'];?>">
				 		<td style="text-align:center;"><?php echo validate_date_display($empRow['expense_date']);?></td>
                        <td style="text-align:center;"><?php echo $empRow['payee_name'];?></td>
                        <td style="text-align:center;"><?php echo $empRow['expense_amount'];?></td>
                        <td style="text-align:center;"><?php echo $empRow['expense_notes'];?></td>				 		
                        
				 		<td style="text-align:center;">
				 			<a href="expenses_add.php?id=<?php echo $empRow['expense_id'];?>" class='btn btn-primary'>Edit</a>
                            <a onclick="del(<?=$empRow['expense_id']?>)" class="btn btn-danger" title="Delete">Delete</a>
				 		</td>
				 	</tr>
				 	
				 
<?php } 
?>

				 <tbody>
				 

				 </tbody>
				
					</table>

				</div>
				<!-- end widget content -->
			</div><!--End of list-->
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
<?php echo ASSETS_URL;?>/js/plugin/datatables/jquery.dataTables.min.js"></script>
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

function del(val){
    
       $.SmartMessageBox({
        title : "Attention required!",
        content : "This is a confirmation box. Do you want to delete the Record?",
        buttons : '[No][Yes]'
       }, function(ButtonPressed) {
        if (ButtonPressed === "Yes") {
            $.post("ajax/delAjax.php",
            {
                //delete_builty:'Yes',
                expense_id : val,
            },
            function(data,status){ 
                if(data.trim()!="")
                {
                    //$('#row'+val).remove();
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