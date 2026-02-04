<?php
include('connection.php');
include('sessionCheck.php');


$branch_id=$_SESSION['branch_id'];
$employee_id=$u_id=$_SESSION['u_id'];

//initilize the page
require_once ("inc/init.php");
$p_rightstatus='active';
$p_rightpagename1=basename(__FILE__);
$file_name_no_extension=explode(".",$p_rightpagename1);
$p_rightpagename=$file_name_no_extension[0];

//require UI configuration (nav, ribbon, etc.)
require_once ("inc/config.ui.php");

/*---------------- PHP Custom Scripts ---------

 YOU CAN SET CONFIGURATION VARIABLES HERE BEFORE IT GOES TO NAV, RIBBON, ETC.
 E.G. $page_title = "Custom Title" */

$page_title = "Employee Rights Template";

/* ---------------- END PHP Custom Scripts ------------- */

//include header
//you can add your custom css in $page_css array.
//Note: all css files are inside css/ folder
$page_css[] = "your_style.css";
include ("inc/header.php");

//include left panel (navigation)
//follow the tree in inc/config.ui.php
//$page_nav["tables"]["sub"]["data"]["active"] = true;
include ("inc/nav.php");
?>
<!-- ==========================CONTENT STARTS HERE ========================== -->
<!-- MAIN PANEL -->

<style type="text/css">
	
.jarviswidget{

	margin-bottom: -2px !important;
}
</style>
<script type="text/javascript">
	var selectedRows=0;
function adRow(){

	var sorg=document.getElementById("sorg_id");
	var sorgvalue= sorg.options[sorg.selectedIndex].value;
	var sorgText = sorg.options[sorg.selectedIndex].text;
	
	tbl=document.getElementById('m_t');
	row=document.getElementById('m_r').innerHTML;
	newRow=tbl.insertRow();
	newRow.innerHTML=row;
	$(newRow).find(".m_sorgText").html(sorgText);
  $(newRow).find(".sorgHidden").val(sorgvalue);
  selectedRows=selectedRows+1;
  $(newRow).find(".del").attr("data-stateName", selectedRows);
  rowId="row"+selectedRows;
  newRow.setAttribute("name", selectedRows);
  newRow.setAttribute("id", rowId);
}
/*function delRow(thisrow)
 {
  //alert(thisrow);
  var rowId=$(thisrow).attr("data-stateName");
  $("#row"+rowId+"").html("");
  $("#row"+rowId+"").css.display= "none";
 
 }*/
</script>
<script>
function delRow(id)
 {
  
        var xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange = function() {
            if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                var responseMsg = xmlhttp.responseText;
                
            }
        };
        xmlhttp.open("GET", "ajax1/delmenu_delete_ajax.php?delmenu_id=" + id, true);
        xmlhttp.setRequestHeader("X-Requested-With", "roles_List.php");
        xmlhttp.send();
 
 }
</script>
<div id="main" role="main">

	<?php
		//configure ribbon (breadcrumbs) array("name"=>"url"), leave url empty if no url
		//$breadcrumbs["New Crumb"] => "http://url.com"
//		$breadcrumbs["Website"] = "";
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
    height: 65px;
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
    width: 406px;
}
</style>
	<!-- MAIN CONTENT -->
	<div id="content">


		<div class="modal fade" id="myMdl" role="dialog">
    <div class="modal-dialog">
    
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header alert alert-danger">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title"><strong>Delete</strong></h4>
        </div>
        <div class="modal-body">
          <p>Do You Want To Delete The Current Record</p>
        </div>
        <div class="modal-footer">
          <input type="hidden" id="v">
          <button type="button" data-dismiss="modal" onclick="del2()" class="btn btn-primary" id="delete">Delete</button>
                     <button type="button" data-dismiss="modal" class="btn">Cancel</button>
          
        </div>
      </div>
      
    </div>
  </div> 

		
		<!-- widget grid -->
		<section id="widget-grid" class="">
		
			<!-- row -->
			<div class="row">
<article class="col-sm-12 col-md-12 col-lg-12 sortable-grid ui-sortable">

							<div class="jarviswidget jarviswidget-sortable" id="wid-id-1" data-widget-editbutton="false" data-widget-custombutton="false" role="widget">
					
						<header>	
				<span class="small_icon"><i class="fa fa-share-alt"></i>	</span>	
				<h2>Employee Rights & Roles</h2>
			</header>

						<!-- widget div-->
<div role="content">			
							<!-- widget content -->
	<div class="widget-body no-padding" style="margin-left: 3px; margin-right: 3px; ">
									
						<br />
		<div class="tab-content" >
		<ul class="nav nav-tabs" role="tablist" style="margin-left: 2px; ">
		    <li role="presentation" class="active"><a href="#add" aria-controls="add" role="tab" data-toggle="tab" style="color:black !important">Role</a></li>
		    <li role="presentation"><a href="#list" aria-controls="list" role="tab" data-toggle="tab" style="color:black !important">Lists</a></li>
	  </ul>
		
			<div role="tabpanel" class="tab-pane active" id="add" >
				 <?php
				$msg="";
				/****************************************************
				If Data Has been Saved then insert data
				*****************************************************/
				if(isset($_POST['submit']))
				{
				$role_name=mysqli_real_escape_string($con,$_POST['role_name']);
					$role_datefrom=str_replace('/', '-', $_POST['role_datefrom']);
					$role_datefrom=date("Y-m-d", strtotime($role_datefrom));
					$role_dateto=str_replace('/', '-', $_POST['role_dateto']);
					$role_dateto=date("Y-m-d", strtotime($role_dateto));
					$role_remarks=mysqli_real_escape_string($con,$_POST['role_remarks']);
					
					$menu=$_POST['menu_id'];

					if(isset($_POST['role_active']))
					{
						$role_active=mysqli_real_escape_string($con,$_POST['role_active']);
					}
					else
					{
						$role_active=0;
					}
					if(isset($_POST['role_default']))
					{
						$role_default=mysqli_real_escape_string($con,$_POST['role_default']);
					}
					else
					{
						$role_default=0;
					}
				
				
					if(!empty($role_name)|| !empty($role_datefrom)|| !empty($role_dateto))
					{
					  if(1==1)
                      {
							
						$insertQuery="INSERT INTO `sys_role`(`role_name`,`role_active`,`role_datefrom`,`role_dateto`,`role_remarks`,`role_default`, branch_id, u_id) VALUES ('$role_name',$role_active,'$role_datefrom','$role_dateto','$role_remarks',$role_default, '$branch_id', '$employee_id');";
						if(exe_Query($insertQuery))
						{
							 
							 $role_id=mysqli_insert_id($con);
								 for ($i=0; $i <count($menu) ; $i++) 
								 {
								 	
									$role_id2= $menu[$i];
									$roled_edit=0;
									$roled_delete=0;

									if(isset($_POST[$role_id2.'_edit']))
									{
										$roled_edit=$_POST[$role_id2.'_edit'];
									}
									if(isset($_POST[$role_id2."_delete"]))
									{
										$roled_delete=$_POST[$role_id2.'_delete'];
									}
									$addmanu="INSERT INTO `sys_roled`(`role_id`, `menu_id`, `roled_edit`, `roled_delete`) VALUES ($role_id,$role_id2, '$roled_edit', '$roled_delete');";
									 	if(exe_Query($addmanu))
									 	{
									 		/*echo "done <Br>";*/
									 	}
								 									 
                                  }

							$msg="Record has created successfully.";
							?>
                            <script> setTimeout(function(){ window.location="employee_roles.php"; },3000);</script>
                            <?php
							}
							else
							{
								$msg=$msg."Prolem while creating Record";
							?>
                            <script> setTimeout(function(){ window.location="employee_roles.php"; },3000);</script>
                            <?php
							}
					}
						else 
						{
							$msg="You have no rights to Add Roles List";
							?>
                            <script> setTimeout(function(){ window.location="employee_roles.php"; },3000);</script>
                            <?php
						}

				  }
				
					else
					{
						$msg="Some fields are empty";
					}
				}
				/****************************************************
				END OF If Data Has been Saved then insert data
				*****************************************************/
				/****************************************************
				If Data Has been UPDATED 
				*****************************************************/
				if(isset($_POST['update']))
				{
				 $rolee_id=mysqli_real_escape_string($con, $_POST['rolee_id']);
					$role_name=mysqli_real_escape_string($con,$_POST['role_name']);
					$role_datefrom=str_replace('/', '-', $_POST['role_datefrom']);
					$role_datefrom=date("Y-m-d", strtotime($role_datefrom));
					$role_dateto=str_replace('/', '-', $_POST['role_dateto']);
					$role_dateto=date("Y-m-d", strtotime($role_dateto));
					$role_remarks=mysqli_real_escape_string($con,$_POST['role_remarks']);
					if(isset($_POST['role_active']))
					{
						$role_active=mysqli_real_escape_string($con,$_POST['role_active']);
					}
					else
					{
						$role_active=0;
					}
					if(isset($_POST['role_default']))
					{
						$role_default=mysqli_real_escape_string($con,$_POST['role_default']);
					}
					else
					{
						$role_default=0;
					}
				
				
					
				if(empty($role_name)||empty($role_datefrom)|| empty($role_dateto))
					 {
					 	$msg="Some fields are empty";
					 }
					 else
					 {
					 	if($resultQ->roled_edit==1)
                      	{
					 	
					 	 $updateQuery="UPDATE `sys_role` SET `role_name`= '$role_name', `role_active`='$role_active', role_datefrom='$role_datefrom',role_dateto='$role_dateto', role_remarks='$role_remarks',role_default='$role_default' WHERE role_id=$rolee_id;";
					 	if(exe_Query($updateQuery))
					 	{
					 		  
							 //$menu=mysqli_real_escape_string($con,$_POST['menu_id']);
							 $menu=  $_POST['menu_id'] ;
							 $delete="DELETE FROM sys_roled WHERE role_id='$rolee_id';";
							 if(exe_Query($delete))
							 {
								 for ($i=0; $i <count($menu) ; $i++) 
								 {
									$menu_id2= $menu[$i];
									$roled_edit=0;
									$roled_delete=0;

									if(isset($_POST[$menu_id2.'_edit']))
									{
										$roled_edit=$_POST[$menu_id2.'_edit'];
									}
									if(isset($_POST[$menu_id2."_delete"]))
									{
										$roled_delete=$_POST[$menu_id2.'_delete'];
									}
								 	/*$Query2="SELECT * FROM sys_roled WHERE menu_id=$menu_id2 AND role_id=$rolee_id;";

									$result2=mysqli_query($con,$Query2);
									if(mysqli_num_rows($result2)<1)*/
									
									 	$addmenu="INSERT INTO `sys_roled`(`role_id`, `menu_id`, `roled_edit`, `roled_delete`) VALUES ($rolee_id,$menu_id2, '$roled_edit', '$roled_delete');";
									 	if(exe_Query($addmenu))
									 	{
									 		/*echo "done <Br>";*/
									 	}
									 	else 
									 	{
									 		//echo mysqli_error($con);
									 	}
								 	
								 									 
                                  }
                              }
?>
<script> setTimeout(function(){ window.location="employee_roles.php"; },3000);</script>
<?php					 			
die();
					 	}
					 	else
					 	{
							$msg="Problem running Query";
					 	}
					 }
					 	else 
					 	{
					 		$msg="You have not rights to Edit Roles List";
					 	}
					 }
					 	
					  }
				
					 					
					 			/****************************************************
					 			END OF If Data Has been UPDATED 
					 			*****************************************************/
?>
<?php if(!empty($msg)){?> 	<div class="alert alert-info"><?php echo $msg; ?> </div> <?php } ?>
				 <?php
				if(!isset($_GET['id']))
				{ 
				?> 
				<form id="checkout-form" class="smart-form" novalidate="novalidate" method="post" action="" autocomplete="off">	
					<fieldset>
						<div class="row" style="margin-top: 5px">
											<div class="col col-lg-2 col-lg-2 col-md-2 col-sm-2 col-xs-12">
													<label class="input">Role Name<b style="color:#F00">:*</b></label>
											</div>
											<div class="col col-lg-3 col-md-3 col-sm-3 col-xs-12">
													<input type="text" name="role_name" class="form-control" placeholder="Enter Role Title eg. Sale Man">
											</div>
											<div class="col col-lg-1 col-lg-2 col-md-2 col-sm-2 col-xs-12">
													<label class="input">Active<b style="color:#F00">:*</b></label>
											</div>
											<div class="col col-lg-1 col-md-3 col-sm-3 col-xs-12">
													<input type="checkbox" name="role_active" checked="checked" id="role_active"   value="1">
											</div>
                                            
											<div class="col col-lg-1 col-lg-2 col-md-2 col-sm-2 col-xs-12"  style=" visibility:hidden;">
													<label class="input">Default<b style="color:#F00">:*</b></label>
											</div>
											<div class="col col-lg-1 col-md-3 col-sm-3 col-xs-12"  style=" visibility:hidden;">
													<input type="checkbox" name="role_default" id="role_default"    value="1">
											</div>
									</div>

									<div class="row" style="margin-top: 5px;">
											<div class="col col-lg-2 col-md-2 col-sm-2 col-xs-12">
													<label class="input">Contract From Date<b style="color:#F00">:*</b></label>
											</div>
											<div class="col col-lg-3 col-md-3 col-sm-3 col-xs-12">
												<div class="input-group">
													<input type="text" name="role_datefrom"  class="form-control datepicker" data-dateformat="dd/mm/yy" placeholder="Select Contract From Date">
													<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
												</div>
											</div>
												
									</div>
                                    
                                    
                                    <div class="row" style="margin-top: 5px;">
											
											<div class="col col-lg-2 col-md-2 col-sm-2 col-xs-12">
													<label class="input">Contract To Date<b style="color:#F00">:*</b></label>
											</div>
											<div class="col col-lg-3 col-md-3 col-sm-3 col-xs-12">
												<div class="input-group">
													<input type="text" name="role_dateto"  class="form-control datepicker" data-dateformat="dd/mm/yy" placeholder="Select Contract To Date">
													<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
												</div>
											</div>	
									</div>
                                    
                                    
										<div class="row" style="margin-top: 5px">
											<div class="col col-lg-2 col-md-2 col-sm-2 col-xs-12">
													<label class="input">Notes</label>
											</div>
											
											<div class="col col-lg-10 col-md-3 col-sm-3 col-xs-12">
												<textarea name="role_remarks" id="editor1" rows="2" cols="79" placeholder="Type Note About This Role"></textarea>
									             
											</div>
									
									</div>
										
						<div class="row" style="margin-top: 5px">
											<div class="col col-lg-2 col-md-2 col-sm-2 col-xs-6">
													<label class="input">Roles Menu</label>
											</div> 
											
											<div class="col col-lg-8 col-md-6 col-sm-6 col-xs-10" >

												<table class="table table-striped table-bordered">

                                            	<th  class="alert" style="background-color: #00aaff; color: #ffff; font-size: 14px;">Department Name >> Page Name</th>                                       	
                                            	<th  class="alert" style="background-color: #00aaff; color: #ffff; font-size: 14px;">Can Access <br><input type="checkbox" id="ckbCheckAll" /></th>
                                            	<th  class="alert" style=" visibility:hidden;">Edit <br><input type="checkbox" id="ckbCheckAll_edit" /></th>
                                            	<th  class="alert" style=" visibility:hidden;">Delete <br><input type="checkbox" id="ckbCheckAll_delete" /></th>
                                            	<?php 
											 	 $query1="SELECT sys_menu.menu_id, sys_menu.menu_name, m2.menu_position, m2.menu_name as parent_name
FROM sys_menu
LEFT OUTER JOIN sys_menu as m2 ON m2.menu_id=sys_menu.menu_parent
WHERE sys_menu.menu_submenu='0'
ORDER BY m2.menu_position, sys_menu.menu_position;";
											 	$row=mysqli_query($con,$query1);
												 while ($result=mysqli_fetch_assoc($row)) {
								?>
									<tr>
                                     	<td><?php if(!empty($result['parent_name'])) {echo $result['parent_name'].' >> ';}?> <?php echo $result['menu_name']?></td>
										<td><input type="checkbox" checked="checked" class="minimal" value="<?php echo $result['menu_id'];?>" name="menu_id[]"/>
										<td style="visibility:hidden;"><input type="checkbox" checked="checked" class="minimal_edit" value="1" name="<?= $result['menu_id'] ?>_edit"></td>
										<td style="visibility:hidden;"><input type="checkbox" checked="checked" class="minimal_delete" value="1" name="<?= $result['menu_id'] ?>_delete"></td>
			
								</td>

								</tr>
							<?php
							} 
							?>
						</table>

											</div>
										<!-- </div>
										<div class="row" style="margin-top: 5px"> -->
											<div class="col col-lg-2 col-md-2 col-sm-2 col-xs-12">
												<!-- <p class="btn btn-lg btn-primary " onclick="adRow()">Add </p> -->	
											</div>
											<div class="col col-lg-3 col-md-3 col-sm-3 col-xs-12">
												
											</div>
							</div>
							<div class="row">
							<div class="col-lg-12">
                                <div style="max-height: 210px; max-width: 300px; overflow: auto; margin-left:10px; margin-top:15px;">
 
								<table id="m_t" class="table table-striped" >

									<tr id="m_r" style="display:none;">
										<td class="m_sorgText">This is one</td>
										<input type="hidden" name="sorg_id[]" id="sorg_id[]" value="" class="sorgHidden">
										<td style="width: 80px; text-align: left;">
								          <p  class="del btn btn-primary btn-sm" data-stateName="" onclick="delRow(this)">Delete</>
								         </td>
									</tr>
								</table>
								</div>
							</div>	

							</div>
						
						
					</fieldset>
					<footer>

						<input type="submit" class="btn btn-primary" name="submit" id="submit" value="Save">
					</footer>
				</form>
				 <?php
				}
				else
				{
					$id=mysqli_real_escape_string($con,$_GET['id']);
					$dclRows=mysqli_fetch_assoc(mysqli_query($con,"SELECT `role_name`, `role_active`,`role_datefrom`,`role_dateto`,`role_remarks`, `role_default` FROM `sys_role` WHERE role_id=$id"));
				?> 
				<form id="checkout-form" class="smart-form" novalidate="novalidate" method="post" action="">	
					<fieldset>
						<div class="row" style="margin-top: 5px">
							<input type="hidden" value="<?php echo $id; ?>" name="rolee_id">
											<div class="col col-lg-2 col-lg-2 col-md-2 col-sm-2 col-xs-12">
													<label class="input">Name<b style="color:#F00">:*</b></label>
											</div>
											<div class="col col-lg-3 col-md-3 col-sm-3 col-xs-12">
													<input type="text" name="role_name" value="<?= $dclRows['role_name']; ?>" class="form-control">
											</div>
											<div class="col col-lg-1 col-lg-2 col-md-2 col-sm-2 col-xs-12">
													<label class="input">Active<b style="color:#F00">:*</b></label>
											</div>
											<div class="col col-lg-1 col-md-3 col-sm-3 col-xs-12">
													<input type="checkbox" name="role_active" id="role_active"  value="1" <?php if($dclRows['role_active']==1) echo "checked"; ?> >
											</div>
											<!-- <div class="col col-lg-1 col-sm-2 col-xs-12">
												<label>Buyers</label>
											</div>
											<div class="col col-lg-1 col-md-3 col-sm-3 col-xs-12">
												<input type="checkbox" name="byr_role" id="byr_role" value="1">
											</div> -->
											<div class="col col-lg-1 col-lg-2 col-md-2 col-sm-2 col-xs-12" style="visibility:hidden;">
													<label class="input">Default<b style="color:#F00">:*</b></label>
											</div>
											<div class="col col-lg-1 col-md-3 col-sm-3 col-xs-12" style="visibility:hidden;">
													<input type="checkbox" name="role_default" id="role_default" value="1" <?php if($dclRows['role_default']==1)echo "checked"; ?> >
											</div>
									</div>
									<div class="row" style="margin-top: 5px;">
											<div class="col col-lg-2 col-md-2 col-sm-2 col-xs-12">
													<label class="input">Rights Active From<b style="color:#F00">:*</b></label>
											</div>
											<div class="col col-lg-3 col-md-3 col-sm-3 col-xs-12">
												<div class="input-group">
													<input type="text" name="role_datefrom"  value="<?= $dclRows['role_datefrom']; ?>"  class="form-control datepicker" data-dateformat="dd/mm/yy">
													<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
												</div>
											</div>
											
									</div>
                                    
                                    <div class="row" style="margin-top: 5px;">
											<div class="col col-lg-2 col-md-2 col-sm-2 col-xs-12">
													<label class="input">Rights Active To<b style="color:#F00">:*</b></label>
											</div>
											<div class="col col-lg-3 col-md-3 col-sm-3 col-xs-12">
												<div class="input-group">
													<input type="text" name="role_dateto" value="<?= $dclRows['role_dateto']; ?>" class="form-control datepicker" data-dateformat="dd/mm/yy">
													<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
												</div>
											</div>	
									</div>
                                    
                                    
									<div class="row" style="margin-top: 5px">
											<div class="col col-lg-2 col-md-2 col-sm-2 col-xs-12">
													<label class="input">Notes</label>
											</div>
											
											<div class="col col-lg-10 col-md-8 col-sm-8 col-xs-12">
												<textarea name="role_remarks" id="editor1" rows="2" cols="78"><?= $dclRows['role_remarks']; ?></textarea>
											</div>
									
									</div>
					<div class="row" style="margin-top: 5px">
											<div class="col col-lg-2 col-md-2 col-sm-2 col-xs-6">
													<label class="input">Roles Menu</label>
											</div>
                                          <?php 
                                     $roles1=array();
                                     $Query3="SELECT menu_id, roled_edit, roled_delete FROM sys_roled WHERE role_id=$id;";
                                     $result2=mysqli_query($con,$Query3);

                                     while($row3=mysqli_fetch_assoc($result2))
                                     {
                                     	$roles1[]=$row3['menu_id'];

                                     }
                                   
                                     ?>
																				
											<div class="col col-lg-8 col-md-6 col-sm-6 col-xs-10" style="height:300px;overflow:auto;">
												<table class="table table-striped table-bordered">
                                            	<th class="alert alert-danger">Form Name</th>
                                            	<th  class="alert alert-danger">Can Access <br><input type="checkbox" id="ckbCheckAll" /></th>
                                            	<th class="alert alert-danger" style="visibility:hidden;">Edit <br><input type="checkbox" id="ckbCheckAll_edit" /></th>
                                            	<th  class="alert alert-danger" style="visibility:hidden;">Delete <br><input type="checkbox" id="ckbCheckAll_delete" /></th>
                                            	<?php 
											 	$query1="SELECT menu_id, menu_name FROM sys_menu WHERE menu_submenu='0';";
											 	$row=mysqli_query($con,$query1);
												while ($result=mysqli_fetch_assoc($row)) 
												{
												$result2=mysqli_fetch_assoc(mysqli_query($con, "SELECT menu_id, roled_edit, roled_delete FROM sys_roled WHERE menu_id='".$result['menu_id']."' AND role_id='$id';"));
												
													?>
														<tr>
					                                     	<td><?php echo $result['menu_name'];?></td>
															<td><input type="checkbox" class="minimal" value="<?php echo $result['menu_id'];?>" name="menu_id[]" <?php if($result['menu_id']==$result2['menu_id']) { echo "checked='checked'"; echo "onchange='delRow(".$result['menu_id'].")'"; } ?> /></td>
															<td style="visibility:hidden;"><input type="checkbox" value="1" class="minimal_edit" name="<?= $result['menu_id'] ?>_edit" <?php if($result2['roled_edit']==1) { echo "checked"; } ?> ></td>
															<td style="visibility:hidden;"><input type="checkbox" class="minimal_delete" value="1" name="<?= $result['menu_id'] ?>_delete" <?php if($result2['roled_delete']==1) { echo "checked"; } ?> ></td>

													</tr>
												<?php
												} 
												?>
						</table>

											</div>
										<!-- </div>
										<div class="row" style="margin-top: 5px"> -->
											<div class="col col-lg-2 col-md-2 col-sm-2 col-xs-12">
												<!-- <p class="btn btn-lg btn-primary " onclick="adRow()">Add </p>	 -->
											</div>
											<div class="col col-lg-3 col-md-3 col-sm-3 col-xs-12">
												
											</div>
							</div>
					<?php 
					$sqlQuota="SELECT sys_roled.roled_id, sys_roled.menu_id,sys_menu.menu_name  FROM sys_roled INNER JOIN sys_menu ON sys_roled.menu_id=sys_menu.menu_id WHERE role_id=$id";
					$queryQuota=mysqli_query($con,$sqlQuota);
				 ?>
					<div class="row">
							<div class="col-lg-12">
                                <div style="max-height: 210px; max-width: 700px; overflow: auto; margin-left:10px; margin-top:15px;">
					<table id="m_t" class="table table-striped" >

									<tr id="m_r" style="display:none;">
										<td class="m_sorgText">This is one</td>
										<input type="hidden" name="sorg_id[]" id="sorg_id[]" value="" class="sorgHidden">
										<td style="width: 80px; text-align: left;">
								          <!-- <p  class="del btn btn-primary btn-sm" data-stateName="" onclick="delRow(this)">Delete</> -->
								         </td>
									</tr>
								</table>
								</div>
							</div>
						</div><!--End of row-->
				<div class="row">
							<div class="col-lg-12">
                                <div style="max-height: 210px; max-width: 700px; overflow: auto; margin-left:10px; margin-top:15px;">
				<!-- <table class="table table-striped">
				<?php
				while($rowQuota=mysqli_fetch_assoc($queryQuota)){
				?>					
					<tr id="delRow<?= $rowQuota['roled_id']; ?>">
						<td><?= $rowQuota['menu_name']; ?></td>
						<td><p class="btn btn-sm btn-primary" onclick="delRoled(<?= $rowQuota['roled_id']; ?>)"> Delete</p></td>
					</tr>
				<?php
				}
				?>
				</table> -->
			</div>
				</div>					
					</div><!--End of row-->

					</fieldset>
					<footer>
						
						<input type="submit" class="btn btn-primary" name="update" id="update" value="Save">
					</footer>
				</form>
				 <?php
				}
				
				?>
			</div><!--End of div id="add"-->

			<div role="tabpanel" class="tab-pane" id="list">
			<!-- Widget id (each widget will need unique id)-->
				<div class="jarviswidget jarviswidget-color-blueDark" id="wid-id-1" data-widget-editbutton="false">

				<!-- widget div-->
				<div>
					<!-- widget content -->
					<div class="widget-body no-padding">
						<table id="datatable_fixed_column" class="table table-striped table-bordered" width="100%">
						<thead>
							<tr>
								<th class="hasinput" style="width:10%">
									<input type="text" class="form-control" placeholder="Name" />
								</th>
								<th class="hasinput" style="width:10%">
									<input type="text" class="form-control" placeholder=" Active" />
								</th>
								<th class="hasinput" style="width:15%">
									<input type="text" class="form-control" placeholder="Remarks" />
								</th>
								<th class="hasinput" style="width:15%">
									<input type="text" class="form-control" placeholder="RoleDate From" />
								</th>
								<th class="hasinput" style="width:14%">
									<input type="text" class="form-control" placeholder="RoleDate To" />
								</th>
								
								
								<th></th>
							</tr>
							<tr>
								<th data-class="expand">Name</th>
								
								<th data-class="expand">Active</th>
 								<th data-class="expand">Remarks</th>
								<th data-class="expand">Role Date From </th>
								<th data-class="expand">Role Date To</th>
								<th>Actions</th>
								
							</tr>
						</thead>
						<tbody>
						 <?php
						$dclRes=mysqli_query($con,"SELECT * FROM `sys_role` WHERE branch_id=$branch_id");
							while($dclRows=mysqli_fetch_assoc($dclRes))
							{
							?>
							<tr id="row<?php echo $dclRows['role_id'];?>">
								<td><?= $dclRows['role_name']; ?></td>
								
								<td><?php if($dclRows['role_active']==1){echo "Active";}else{ echo "Inactive"; } ?></td>
								<td><?= $dclRows['role_remarks']; ?></td>
								<td><?= $dclRows['role_datefrom']; ?></td>
								<td><?= $dclRows['role_dateto']; ?></td>			 
								<td style="text-align: center;"><a href="employee_roles.php?id=<?= $dclRows['role_id'];?>" id="smart-mod-eg1" class="btn btn-info">Edit</a></td>
							</tr>
							<?php
							}
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





	$("#ckbCheckAll").click(function () {
        $(".minimal").prop('checked', $(this).prop('checked'));
    });

    $("#ckbCheckAll_edit").click(function () {
        $(".minimal_edit").prop('checked', $(this).prop('checked'));
    });
    $("#ckbCheckAll_delete").click(function () {
        $(".minimal_delete").prop('checked', $(this).prop('checked'));
    });



	
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
    $("div.toolbar").html('<div class="text-right"></div>');
    	   
    // Apply the filter
    $("#datatable_fixed_column thead th input[type=text]").on( 'keyup change', function () {
    	
        otable
            .column( $(this).parent().index()+':visible' )
            .search( this.value )
            .draw();
            
    } );
    /* END COLUMN FILTER */   

	/* COLUMN SHOW - HidE */
	$('#datatable_col_reorder').dataTable({
		"sDom": "<'dt-toolbar'<'col-xs-12 col-sm-6'f><'col-sm-6 col-xs-6 hidden-xs'C>r>"+
				"t"+
				"<'dt-toolbar-footer'<'col-sm-6 col-xs-12 hidden-xs'i><'col-sm-6 col-xs-12'p>>",
		"autoWidth" : true,
		"preDrawCallback" : function() {
			// Initialize the responsive datatables helper once.
			if (!responsiveHelper_datatable_col_reorder) {
				responsiveHelper_datatable_col_reorder = new ResponsiveDatatablesHelper($('#datatable_col_reorder'), breakpointDefinition);
			}
		},
		"rowCallback" : function(nRow) {
			responsiveHelper_datatable_col_reorder.createExpandIcon(nRow);
		},
		"drawCallback" : function(oSettings) {
			responsiveHelper_datatable_col_reorder.respond();
		}			
	});
	
	/* END COLUMN SHOW - HidE */

	/* TABLETOOLS */
	$('#datatable_tabletools').dataTable({
		
		// Tabletools options: 
		//   https://datatables.net/extensions/tabletools/button_options
		"sDom": "<'dt-toolbar'<'col-xs-12 col-sm-6'f><'col-sm-6 col-xs-6 hidden-xs'T>r>"+
				"t"+
				"<'dt-toolbar-footer'<'col-sm-6 col-xs-12 hidden-xs'i><'col-sm-6 col-xs-12'p>>",
        "oTableTools": {
        	 "aButtons": [
             "copy",
             "csv",
             "xls",
                {
                    "sExtends": "pdf",
                    "sTitle": "SmartAdmin_PDF",
                    "sPdfMessage": "SmartAdmin PDF Export",
                    "sPdfSize": "letter"
                },
             	{
                	"sExtends": "print",
                	"sMessage": "Generated by SmartAdmin <i>(press Esc to close)</i>"
            	}
             ],
            "sSwfPath": "js/plugin/datatables/swf/copy_csv_xls_pdf.swf"
        },
		"autoWidth" : true,
		"preDrawCallback" : function() {
			// Initialize the responsive datatables helper once.
			if (!responsiveHelper_datatable_tabletools) {
				responsiveHelper_datatable_tabletools = new ResponsiveDatatablesHelper($('#datatable_tabletools'), breakpointDefinition);
			}
		},
		"rowCallback" : function(nRow) {
			responsiveHelper_datatable_tabletools.createExpandIcon(nRow);
		},
		"drawCallback" : function(oSettings) {
			responsiveHelper_datatable_tabletools.respond();
		}
	});
	
	/* END TABLETOOLS */




});

</script>
<?php
if($resultQ->roled_delete==1)
{
	?>
<script type="text/javascript">
	/*
		* SmartAlerts
		*/
		// With Callback
		function del(val){



          var browser = '';
          var browserVersion = 0;

          if (/Opera[\/\s](\d+\.\d+)/.test(navigator.userAgent)) {
              browser = 'Opera';
          } else if (/MSIE (\d+\.\d+);/.test(navigator.userAgent)) {
              browser = 'MSIE';
          } else if (/Navigator[\/\s](\d+\.\d+)/.test(navigator.userAgent)) {
              browser = 'Netscape';
          } else if (/Chrome[\/\s](\d+\.\d+)/.test(navigator.userAgent)) {
              browser = 'Chrome';
          } else if (/Safari[\/\s](\d+\.\d+)/.test(navigator.userAgent)) {
              browser = 'Safari';
              /Version[\/\s](\d+\.\d+)/.test(navigator.userAgent);
              browserVersion = new Number(RegExp.$1);
          } else if (/Firefox[\/\s](\d+\.\d+)/.test(navigator.userAgent)) {
              browser = 'Firefox';
          }
          if(browserVersion === 0){
              browserVersion = parseFloat(new Number(RegExp.$1));
          }
    
        if(browser=='Safari')
          {

             $("#myMdl").modal("show");
            var value=val;
            
            $('#v').val(value);
           
            

           
            
          }
          else
          {
			$.SmartMessageBox({
				title : "Attention required!",
				content : "This is a confirmation box. Do you want to delete the Record?",
				buttons : '[No][Yes]'
			}, function(ButtonPressed) {
				if (ButtonPressed === "Yes") {
				        var xmlhttp = new XMLHttpRequest();
				        xmlhttp.onreadystatechange = function() {
			            if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
			               var  responseMsg = xmlhttp.responseText;
			              33333222222222233
			               if(responseMsg!="")
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
			            }
			        };
			        xmlhttp.open("GET", "ajax1/roles_List_delete_ajax.php?role_id=" + val, true);
			        xmlhttp.setRequestHeader("X-Requested-With", "roles_List.php");
			        xmlhttp.send();						    				
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


		  }
function del2()
            {

         var Id=$('#v').val();
         var xmlhttp = new XMLHttpRequest();
 
        xmlhttp.onreadystatechange = function() {
            if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                var responseMsg = xmlhttp.responseText;
                  if(responseMsg!="")
                  {
                 document.getElementById("row"+Id).style.display='none';
                 }
                 else
                 {

                  alert("Problem During Deletion");
                }

            }
        };
         xmlhttp.open("GET", "ajax1/roles_List_delete_ajax.php?role_id=" + val, true);
		xmlhttp.setRequestHeader("X-Requested-With", "roles_List.php");
              xmlhttp.send();     
 

        }
</script>
<?php } ?>


<?php
//include footer
include ("inc/google-analytics.php");
?>