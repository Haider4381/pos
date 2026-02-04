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

$page_title = "Software Backup";
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
		



		<?php

if($_REQUEST['action'] == "export")
{
$msg="";

$table = array("adm_brand",
			"adm_client",
			"adm_client_scheme",
			"adm_country",
			"adm_expenses",
			"adm_item",
			"adm_itemcategory",
			"adm_itemsubcategory",
			"adm_itemunit",
			"adm_packages",
			"adm_payee",
			"adm_promotion_emails",
			"adm_promotion_emailsdetail",
			"adm_purchase",
			"adm_purchasereturn",
			"adm_purchasereturn_detail",
			"adm_purchase_detail",
			"adm_purchase_payment",
			"adm_quotation",
			"adm_quotation_detail",
			"adm_sale_payment",
			"adm_supplier",
			"adm_supplier_scheme",
			"cust_sale",
			"cust_salereturn",
			"cust_salereturn_detail",
			"cust_sale_customerdisplay",
			"cust_sale_detail");
$result = mysqli_query($con,"SHOW TABLES");
while($row = mysqli_fetch_row($result)){
  $tables[] = $row[0];
}



/*foreach($table as $table)
{
	echo $table;
}
exit;
$return = '';*/
foreach($table as $table){
  $result = mysqli_query($con,"SELECT * FROM ".$table);
  $num_fields = mysqli_num_fields($result);
  
  $return .= 'DROP TABLE '.$table.';';
  $row2 = mysqli_fetch_row(mysqli_query($con,"SHOW CREATE TABLE ".$table));
  $return .= "\n\n".$row2[1].";\n\n";
  
  for($i=0;$i<$num_fields;$i++){
    while($row = mysqli_fetch_row($result)){
      $return .= "INSERT INTO ".$table." VALUES(";
      for($j=0;$j<$num_fields;$j++){
        $row[$j] = addslashes($row[$j]);
        if(isset($row[$j])){ $return .= '"'.$row[$j].'"';}
        else{ $return .= '""';}
        if($j<$num_fields-1){ $return .= ',';}
      }
      $return .= ");\n";
    }
  }
  $return .= "\n\n\n";
}
//echo "<pre>";
//echo $return;
//exit;


//save file
$file_url = 'software_backup/backup.sql';
$handle = fopen($file_url,"w+");
fwrite($handle,$return);
fclose($handle);
$msg =  "Successfully backed up (Please check Your BackUp Folder)";

}


if($_REQUEST['action'] == "restore")
{
$msg="";
$filename = 'software_backup/backup.sql';
$handle = fopen($filename,"r+");
$contents = fread($handle,filesize($filename));
$sql = explode(';',$contents);
foreach($sql as $query){
  $result = mysqli_query($con,$query);
  if($result){
       '<tr><td><br></td></tr>';
       '<tr><td>'.$query.' <b>SUCCESS</b></td></tr>';
       '<tr><td><br></td></tr>';
  }
}
fclose($handle);
$msg =  'Backup Successfully Restore';




}





?>
<!-- row -->
<div class="row">

	<!-- NEW WIDGET START -->
	<article class="col-xs-12 col-sm-12 col-md-12 col-lg-12">

		<!-- Widget ID (each widget will need unique ID)-->
		<div class="jarviswidget jarviswidget-color-purity" id="wid-id-1" data-widget-editbutton="false">
			<header>	
				<span class="small_icon"><i class="fa fa-flash"></i>	</span>	
				<h2>Backup & Restore Software</h2>
			</header>

			<!-- widget div-->
			<div>		


				<!-- widget content -->
				<div class="widget-body no-padding">

		<div class="tab-content" >
			<div role="tabpanel" class="tab-pane active" id="add" >

<table style="width:100%; background:#f1f1f1;" class="table" border="0">
            	<tr>
            		<td>
            				<table style="width:70%; background:#f1f1f1;" class="table" border="0">
			            	<tr>
			                	<th style="width:50%;"><strong>Restore</strong></th>
			                	<th style="width:50%;"><strong>Backup</strong></th>
			                </tr>
			                <tr>
			                	<td>
			                		
			                                 <div id="upload">
			                                    <form enctype="multipart/form-data" action="softwarebackup.php?action=restore" method="post">
			                                      
			                                      <h3> <input type="submit" name="submit" value="Restore" class="btn btn-primary"/></h3>
			                                    </form>
			                                       
			                                 </div>
			                    </td>
			                    <td>
			                		
			                                <form action="softwarebackup.php?action=export" method="post">       
			                                <h3> <input type="submit"  download="download" class="btn btn-success" value="Backup"/></h3>
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
			                	if(!empty($msg))
			                	{

			                		
			                			echo '<h4 id="msg">'.$msg.'</h4>';
			                		
			                	}	 
			                      ?>          
			                    </td>
			                    
			                </tr>
			            	</table>
			         </td>
			    </tr>            

            </table>

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