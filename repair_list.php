<?php
include('sessionCheck.php');
include('connection.php');
include('functions.php');
require_once ("inc/init.php");
require_once ("inc/config.ui.php");
$page_title = "Repair List";
include ("inc/header.php");
//$page_nav["Products"]["active"] = true;
include ("inc/nav.php");
$branch_id=$_SESSION['branch_id'];
?>
<!-- ==========================CONTENT STARTS HERE ========================== -->
<!-- MAIN PANEL -->
<div id="main" role="main">
<style>
.flip-clock {
  text-align: center;
  perspective: 600px;
  margin: 0 auto;

  *,
  *:before,
  *:after { box-sizing: border-box; }
}

.flip-clock__piece {
  display: inline-block;
  margin: 0 0.2vw;
  
  @media (min-width: 1000px) {
    margin: 0 5px;
  }
}

.flip-clock__slot {
  font-size: 1rem;
  line-height: 1.5;
  display: block;
/*
  //position: relative;
  //top: -1.6em;
  z-index: 10;
  //color: #FFF;
*/
}

@halfHeight: 0.72em;
@borderRadius: 0.15em;

.flip-card {
  display: block;
  position: relative;
  padding-bottom: @halfHeight;
  font-size: 2.25rem;
  line-height: 0.95;
}

@media (min-width: 1000px) {
  .flip-clock__slot { font-size: 1.2rem; }
  .flip-card { font-size: 3rem; }
}


/*////////////////////////////////////////*/


.flip-card__top,
.flip-card__bottom,
.flip-card__back-bottom,
.flip-card__back::before,
.flip-card__back::after {
  display: block;
  height: @halfHeight;
  color: #ccc;
  background: #222;
  padding: 0.23em 0.25em 0.4em;
  border-radius: @borderRadius @borderRadius 0 0;
  backface-visiblity: hidden;
  transform-style: preserve-3d;
  width: 1.8em;
}

.flip-card__bottom,
.flip-card__back-bottom {
  color: #FFF;
  position: absolute;
  top: 50%;
  left: 0;
  border-top: solid 1px #000;
  background: #393939;
  border-radius: 0 0 @borderRadius @borderRadius;
  pointer-events: none;
  overflow: hidden;
  z-index: 2;
}

.flip-card__back-bottom {
  z-index: 1;
}

.flip-card__bottom::after,
.flip-card__back-bottom::after {
  display: block;
  margin-top: -@halfHeight;
}

.flip-card__back::before,
.flip-card__bottom::after,
.flip-card__back-bottom::after {
  content: attr(data-value);
}

.flip-card__back {
  position: absolute;
  top: 0;
  height: 100%;
  left: 0%;
  pointer-events: none;
}

.flip-card__back::before {
  position: relative;
  overflow: hidden;
  z-index: -1;
}

.flip .flip-card__back::before {
  z-index: 1;
  animation: flipTop 0.3s cubic-bezier(.37,.01,.94,.35);
  animation-fill-mode: both;
  transform-origin: center bottom;
}

.flip .flip-card__bottom {
  transform-origin: center top;
  animation-fill-mode: both;
  animation: flipBottom 0.6s cubic-bezier(.15,.45,.28,1);// 0.3s;
}

@keyframes flipTop {
  0% {
    transform: rotateX(0deg);
    z-index: 2;
  }
  0%, 99% {
    opacity: 1;
  }
  100% {
    transform: rotateX(-90deg);
    opacity: 0;
  }
}

@keyframes flipBottom {
  0%, 50% {
    z-index: -1;
    transform: rotateX(90deg);
    opacity: 0;
  }
  51% {
    opacity: 1;
  }
  100% {
    opacity: 1;
    transform: rotateX(0deg);
    z-index: 5;
  }
}
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
textarea{
 border-radius: 5px !important;
}
 .dataTables_filter .input-group-addon+.form-control {
    display: none;
}
.dataTables_filter .input-group-addon {
    display: none;
    }
.btn{
    padding: 6px 9px;
}
</style>
	
<?php $breadcrumbs["List"] = "";
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
				<h2>Repair List</h2>
			</header>

			<!-- widget div-->
			<div>		




<!-- widget content -->
<div class="widget-body no-padding">

		<div class="tab-content" >

					<table id="datatable_fixed_column" class="table table-striped table-bordered" width="100%">

				 <thead>
							<tr>
								
								<th class="hasinput" style="width:7%">
									<input type="text" class="form-control" placeholder="Bill No."  />
								</th>
								<th class="hasinput" style="width:11%">
									<input type="text" class="form-control" placeholder="Customer Name"  />
								</th>
                                <th class="hasinput" style="width:8%">
									<input type="text" class="form-control" placeholder="Phone No." />
								</th>
								
								<th class="hasinput" style="width:9%" >
									<input type="text" placeholder="Repair Date" class="form-control datepicker" data-dateformat="dd-mm-yy">
								</th>
                                <th class="hasinput" style="width:9%" >
									<input type="text" placeholder="Deliver Date" class="form-control datepicker" data-dateformat="dd-mm-yy">
								</th>
								
								<th class="hasinput" style="width:8%" >
									<input type="text" placeholder="IMEI No." class="form-control">
								</th>
							
								<th class="hasinput" style="width:7%" >
									<input type="text" placeholder="Type" class="form-control">
								</th>
                                <th class="hasinput" style="width:8%" >
									<input type="text" placeholder="Bill Amount" class="form-control">
								</th>
                                <th class="hasinput" style="width:10%" >
									<input type="text" placeholder="employee" class="form-control">
								</th>
									<th class="hasinput" style="width:8%" >
									<input type="text" placeholder="Status" class="form-control">
								</th>
								<th class="hasinput" style="width:5%">
									
								</th>
                                <th class="hasinput" style="width:10%">
									
								</th>
							</tr>	
							<tr>
							    <th style="text-align:center;">Bill No.</th>
								<th style="text-align:center;">Customer Name</th>
                                <th style="text-align:center;">Phone No.</th>
								<th style="text-align:center;">Repair Date</th>
                                <th style="text-align:center;">Deliver Date</th>
                                <th style="text-align:center;">IMEI No.</th>
								
                                <th style="text-align:center;">Type</th>
                                <th style="text-align:center;">Bill Amount</th>
								<th style="text-align:center;">Employee</th>
								<th style="text-align:center;">Status</th>
                                <th style="text-align:center;">Closed</th>
                                <th style="text-align:center;">Action</th>
							</tr>
						</thead>
						<tbody>
							
<?php

$RQ="
SELECT `rep_id`, rep_Number,  rep_repairs.`item_Code`, `rep_Notes`, `rep_ItemCondition`, `rep_AmountCheck`, `rep_AmountRepair`, `rep_AmountPaid`,  `rep_CreatedAt`, `rep_AmountBalance`,rep_Date, rep_DateDelivery,rep_Closed,
rstatus_Name,rstatus_background, rtype_Name,
client_Name, client_Phone, u_FullName, rep_repairs.item_id as item_Name
FROM `rep_repairs`
INNER JOIN rep_status ON rep_status.rstatus_id=rep_repairs.rstatus_id
INNER JOIN rep_type ON rep_type.rtype_id=rep_repairs.rtype_id
INNER JOIN adm_client ON adm_client.client_id=rep_repairs.client_id
INNER JOIN u_user ON u_user.u_id=rep_repairs.u_id
WHERE rep_repairs.branch_id=$branch_id";
$RQR=mysqli_query($con,$RQ);
while($repair_row=mysqli_fetch_assoc($RQR))
{

?>		
				 			
<?php echo "<tr id='row".$repair_row['rep_id']."'>";?>
                            <td style="text-align:center;"><?php echo $repair_row['rep_Number'];?></td>
						 	<td style="text-align:center;"><?php echo $repair_row['client_Name'];?> </td>
                            <td style="text-align:center;"><?php echo $repair_row['client_Phone'];?></td>
						 	<td style="text-align:center;"><?php echo validate_date_display($repair_row['rep_Date']);?> </td>
                            <td style="text-align:center;"><?php echo validate_date_display($repair_row['rep_DateDelivery']);?> </td>
						 	
                            <td style="text-align:center;"><?php echo $repair_row['item_Code'];?></td>
                           
                            <td style="text-align:center;"><?php echo $repair_row['rtype_Name'];?></td>
                            <td style="text-align:center;"><?php echo $currency_symbol.$repair_row['rep_AmountBalance'];?></td>
                            <td style="text-align:center;"><?php echo $repair_row['u_FullName'];?></td> 
                             <td style="text-align:center;"><span style="background:<?=$repair_row['rstatus_background'];?>; border-radius: 3px; padding:3px; width:80px; text-align:center; color:#FFF; display:block;"><?php echo $repair_row['rstatus_Name'];?></span></td>
                            <td style="text-align:center;"><?php echo $repair_row['rep_Closed']==1 ? 'Yes' : 'No';?></td>
							<td style="text-align:center;">
                                <a href="repair_print?r_id=<?=$repair_row['rep_id']?>" class="btn btn-success" title="Print" target="_blank"><i class="fa fa-print"></i></a>
                                <a href="repair_add?id=<?=$repair_row['rep_id']?>" class="btn btn-primary" title="Edit"><i class="fa fa-edit"></i></a>
                                <a onclick="del(<?=$repair_row['rep_id']?>)" class="btn btn-danger" title="Delete"><i class="fa fa-remove"></i></a>
				 			</td>
				 			</tr>
			 		
							
<?php } 
?>

				 </tbody>
				
					</table>

				</div>
				<!-- end widget content -->
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
                rep_id : val,
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