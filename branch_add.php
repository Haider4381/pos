<?php
include('connection.php');
include('sessionCheck.php');
include('functions.php');
require_once ("inc/init.php");
require_once ("inc/config.ui.php");
$page_title = "Store Opening";
include ("inc/header.php");
$page_nav["Settings"]["sub"]["Store Opening"]["active"] = true;
include ("inc/nav.php");
 
?>
<!-- ==========================CONTENT STARTS HERE ========================== -->
<!-- MAIN PANEL -->
<div id="main" role="main">

	
<?php $breadcrumbs["Store Opening"] = "";
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
				<h2>Store Opening</h2>
			</header>

			<!-- widget div-->
			<div>		


				<!-- widget content -->
				<div class="widget-body no-padding">
	<br>
<ul class="nav nav-tabs" role="tablist" style="margin-left: 2px;">
	<li role="presentation" class="active"><a href="#add" aria-controls="add" role="tab" data-toggle="tab" style="color:black !important">Add New</a></li>
	<li role="presentation"><a href="#list" aria-controls="list" role="tab" data-toggle="tab" style="color:black !important">Lists</a></li>
</ul>
		<div class="tab-content" >
			<div role="tabpanel" class="tab-pane active" id="add" >

<?php
if(isset($_POST['submit']))
{
	
	$branch_CodeNumber=validate_input($_POST['branch_CodeNumber']);
	$branch_Code=validate_input($_POST['branch_Code']);
	$branch_Name=validate_input($_POST['branch_Name']);
	$branch_Address=validate_input($_POST['branch_Address']);
	$branch_Phone1=validate_input($_POST['branch_Phone1']);
	$branch_Phone2=validate_input($_POST['branch_Phone2']);
	$branch_Email=validate_input($_POST['branch_Email']);
	$branch_EmailConfirm=validate_input($_POST['branch_EmailConfirm']);
	$branch_Password=md5(validate_input($_POST['branch_Password']));
	$branch_Password2=validate_input($_POST['branch_Password']);
	$branch_PasswordConfirm=validate_input($_POST['branch_PasswordConfirm']);
	$branch_Web=validate_input($_POST['branch_Web']);
	$country_id=validate_input($_POST['country_id']);
	$currency_id=validate_input($_POST['currency_id']);
	$branch_City=validate_input($_POST['branch_City']);
	$branch_SalesTarget=validate_input($_POST['branch_SalesTarget']);
	if(isset($_POST['branch_SalesTargetAllow'])) {$branch_SalesTargetAllow=1;} else {$branch_SalesTargetAllow=0;}
	
	$branch_TimeZone=validate_input($_POST['branch_TimeZone']);
	
	$q="INSERT INTO `adm_branch`	(branch_CodeNumber,branch_Code,`branch_Name`, `branch_Address`, `branch_Phone1`, `branch_Phone2`, `branch_Email`,branch_EmailConfirm,branch_Password,branch_Password2, currency_id, branch_SalesTarget, 					branch_SalesTargetAllow,		`branch_Web`, `branch_TimeZone`, `country_id`, `branch_City`, `branch_CreatedAt`)
							VALUES ('$branch_CodeNumber','$branch_Code','$branch_Name','$branch_Address','$branch_Phone1','$branch_Phone2','$branch_Email', '$branch_EmailConfirm','$branch_Password','$branch_Password2', '$currency_id', '$branch_SalesTarget','$branch_SalesTargetAllow',	'$branch_Web','$branch_TimeZone','$country_id','$branch_City',now())";
	if(mysqli_query($con,$q))
	{
		$inserted_branch_id=mysqli_insert_id($con);
		$_SESSION['msg']="Store Created successfully";
		$userQ="Insert into u_user (u_id, u_FullName,	u_Email,	u_Password,		u_Password2,					u_Remarks,				branch_id,				branch_admin,	role_id,	u_Status)
							Values ('', 'Branch Admin',	'$branch_Email', '$branch_Password', '$branch_Password2',	'Branch Admin',			'$inserted_branch_id',	'1',			'0',		'1')";
		mysqli_query($con,$userQ);
	}
	else
	{
		$_SESSION['msg']="Problem creating Store";
	}
	//die();
	echo '<script> window.location="";</script>';
	die();
}

if(isset($_POST['update']))
{
	$branch_id=validate_input($_POST['branch_id']);
	$branch_Name=validate_input($_POST['branch_Name']);
	$branch_Address=validate_input($_POST['branch_Address']);
	$branch_Phone1=validate_input($_POST['branch_Phone1']);
	$branch_Phone2=validate_input($_POST['branch_Phone2']);
	$branch_Email=validate_input($_POST['branch_Email']);
	$branch_EmailConfirm=validate_input($_POST['branch_EmailConfirm']);
	$branch_Password=md5(validate_input($_POST['branch_Password']));
	$branch_Password2=validate_input($_POST['branch_Password']);
	$branch_PasswordConfirm=validate_input($_POST['branch_PasswordConfirm']);
	$branch_Web=validate_input($_POST['branch_Web']);
	$country_id=validate_input($_POST['country_id']);
	$currency_id=validate_input($_POST['currency_id']);
	$branch_City=validate_input($_POST['branch_City']);
	$branch_SalesTarget=validate_input($_POST['branch_SalesTarget']);
	$branch_TimeZone=validate_input($_POST['branch_TimeZone']);
	if(isset($_POST['branch_SalesTargetAllow'])) {$branch_SalesTargetAllow=1;} else {$branch_SalesTargetAllow=0;}
	$q="
		UPDATE adm_branch
		SET
			branch_Name='$branch_Name',
			branch_Address='$branch_Address',
			branch_Phone1='$branch_Phone1',
			branch_Phone2='$branch_Phone2',
			branch_Email='$branch_Email',
			branch_EmailConfirm='$branch_EmailConfirm',
			branch_Password='$branch_Password',
			branch_Password2='$branch_Password2',
			branch_PasswordConfirm='$branch_PasswordConfirm',
			branch_Web='$branch_Web',
			country_id='$country_id',
			currency_id='$currency_id',
			branch_SalesTarget='$branch_SalesTarget',
			branch_TimeZone='$branch_TimeZone',
			branch_SalesTargetAllow='$branch_SalesTargetAllow',
			branch_City='$branch_City'
		WHERE branch_id=$branch_id";
	if(mysqli_query($con,$q))
	{
		mysqli_query($con,"UPDATE
						u_user set
							u_Email='$branch_Email',
							u_Password='$branch_Password',
							u_Password2='$branch_Password2'
							Where branch_id=$branch_id AND branch_Admin=1");
		$_SESSION['msg']="Store Updated successfully";
		
		echo "<script>window.location='branch_add.php';</script>";
		die();
	}
	else
	{
		$_SESSION['msg']="Problem Updating Store";
	}
} 
?>

<?php if(!empty($_SESSION['msg'])){?> <div class="alert alert-info"><?php echo $_SESSION['msg']; ?> </div> 
<?php unset($_SESSION['msg']);} ?>
		
<?php if(!isset($_GET['id'])) { ?>
			<form id="checkout-form" class="smart-form" method="POST" onsubmit="return checkParameters();" autocomplete="off">	
			<fieldset>
<?php 
$branch_CodeNumber = "SELECT (max(branch_CodeNumber)+1) AS branch_CodeNumber FROM adm_branch";
$branch_CodeNumber = mysqli_query($con,$branch_CodeNumber);
if(mysqli_num_rows($branch_CodeNumber))
{
	$branch_CodeNumber = mysqli_fetch_object($branch_CodeNumber);
	$branch_CodeNumber = $branch_CodeNumber->branch_CodeNumber;
	$branch_CodeNumber = str_pad($branch_CodeNumber, 3, "0", STR_PAD_LEFT);
	$branch_Code = 'MEP'.$branch_CodeNumber;
} 
else
{
	echo $branch_CodeNumber = 'Brancho Code Generating Error';
	die();
}
?>
            
            
            
			<div class="row" style="margin-bottom: 5px;">
                <div class="col col-lg-2">
                        <label>Branch Code:</label>
                </div>
                <div class="col col-lg-3">
                        <input type="text" name="branch_Code" value="<?=$branch_Code?>" class="form-control" readonly="readonly">
                </div>
    
                <div class="col col-lg-2">
                        <label>Branch Number:</label>
                </div>
                <div class="col col-lg-3">
                        <input type="text" name="branch_CodeNumber" value="<?=$branch_CodeNumber?>" class="form-control" readonly="readonly">
                </div>
            </div>
            
            <div class="row" style="margin-bottom: 5px;">
                <div class="col col-lg-2">
                        <label>Store Name:</label>
                </div>
                <div class="col col-lg-3">
                        <input type="text" name="branch_Name" id="branch_Name" placeholder="Enter Store Name" class="form-control">
                </div>
    
                <div class="col col-lg-2">
                        <label>Address:</label>
                </div>
                <div class="col col-lg-3">
                        <input type="text" name="branch_Address" class="form-control" placeholder="Enter Store Address">
                </div>
            </div>
			

		<!--2nd row start here-->
		<div class="row" style="margin-bottom: 5px;">
                <div class="col col-lg-2">
                        <label>Phone1:</label>
                </div>
                <div class="col col-lg-3">
                        <input type="text" name="branch_Phone1" class="form-control" placeholder="Enter Store Phone">
                </div>
    
                <div class="col col-lg-2">
                        <label>Phone2:</label>
                </div>
                <div class="col col-lg-3">
                        <input type="text" name="branch_Phone2" class="form-control" placeholder="Enter Store Other Phone If Any">
                </div>
         </div>
         
            
         <div class="row" style="margin-bottom: 5px;">
                <div class="col col-lg-2">
                        <label>Location (State):</label>
                </div>
                <div class="col col-lg-3">
                        <input type="text" name="branch_City" class="form-control" placeholder="Enter Store Location/City">
                </div>
    
                <div class="col col-lg-2">
                        <label>Web Address:</label>
                </div>
                <div class="col col-lg-3">
                        <input type="text" name="branch_Web" class="form-control" placeholder="Enter Store Web Address">
                </div>
         </div>
         <div class="row" style="margin-top:5px;">
						<div class="col col-lg-10 col-xs-12">
							<div class="alert alert-success"><strong>Branch Admin Login Detail</strong></div>
						</div>
					</div><!--End of row-->
         <div class="row" style="margin-bottom: 5px;">
                <div class="col col-lg-2">
                        <label>Email:</label>
                </div>
                <div class="col col-lg-3">
                        <input type="text" name="branch_Email" id="branch_Email" class="form-control"  placeholder="Enter Store Admin Email">
                </div>
    
                <div class="col col-lg-2">
                        <label>Confirm Email:</label>
                </div>
                <div class="col col-lg-3">
                        <input type="text" name="branch_EmailConfirm" id="branch_EmailConfirm" class="form-control"  placeholder="Re-Enter Store Admin Email" >
                </div>
                
         </div>
         
         <div class="row" style="margin-bottom: 5px;">
                <div class="col col-lg-2">
                        <label>Password:</label>
                </div>
                <div class="col col-lg-3">
                        <input type="password" name="branch_Password" id="branch_Password" class="form-control" placeholder="Enter Store Admin Loign Password">
                </div>
    
                <div class="col col-lg-2">
                        <label>Confirm Password:</label>
                </div>
                <div class="col col-lg-3">
                        <input type="password" name="branch_PasswordConfirm" id="branch_PasswordConfirm" class="form-control" placeholder="Re-Enter Store Admin Login Password">
                </div>
         </div>
         
         
         <div class="row" style="margin-top:5px;">
              <div class="col col-lg-10 col-xs-12">
                  <div class="alert alert-success"><strong>Branch Sales Target</strong></div>
              </div>
					</div><!--End of row-->
         <div class="row" style="margin-bottom: 5px;">
                <div class="col col-lg-2">
                        <label>Allow Target Light:</label>
                </div>
                <div class="col col-lg-3">
                        <input type="checkbox" name="branch_SalesTargetAllow"  checked >
                </div>
                
                <div class="col col-lg-2">
                        <label>Target Light:</label>
                </div>
                <div class="col col-lg-3">
                        <input type="text" name="branch_SalesTarget" class="form-control" placeholder="Enter Store Sales Target Amount">
                </div>
         </div>   
        
        
        <div class="row" style="margin-top:5px;">
						<div class="col col-lg-10 col-xs-12">
							<div class="alert alert-success"><strong>Branch Setting</strong></div>
						</div>
					</div><!--End of row-->
         <div class="row" style="margin-bottom: 5px;">
                <div class="col col-lg-2">
                        <label>Country:</label>
                </div>
                <div class="col col-lg-3">
                        <select class="select2" name="country_id">
                        	<?php $countryArray=get_Countries();
								 foreach ($countryArray as $key => $countryRow) { 
								?>
								<option value="<?php echo $countryRow['country_id'];?>"><?php echo $countryRow['country_name'];?></option>
								<?php } 
								?>
                        </select>
                </div>
    
                <div class="col col-lg-2">
                        <label>Currency:</label>
                </div>
                <div class="col col-lg-3">
                        <select class="select2" name="currency_id">
                        <option value="0">Select Currency</option>
                        	<?php $currencyArray=get_Currencies();
								 foreach ($currencyArray as $key => $currencyRow) { 
								?>
								<option value="<?php echo $currencyRow['currency_id'];?>"><?php echo $currencyRow['currency_Name']. ' '. $currencyRow['currency_Symbol'];?></option>
								<?php } 
								?>
                        </select>
                </div>
         </div>
         
         <div class="row" style="margin-bottom: 5px;">
                <div class="col col-lg-2">
                        <label>TimeZone:</label>
                </div>
                <div class="col col-lg-3">
                        <select class="select2" name="branch_TimeZone">
                        	<?php $timezoneArray=get_TimeZone();
								 foreach ($timezoneArray as $key => $timezoneRow) { 
								?>
								<option value="<?php echo $timezoneRow['timezone_name'];?>"><?php echo $timezoneRow['timezone_name'];?></option>
								<?php } 
								?>
                        </select>
                </div>
    
                
         </div>
 			</fieldset>
				<footer>
					<button type="submit" class="btn btn-primary" name="submit">	Save </button>
				</footer>
			</form>
		
<?php
}
else
{
	$branch_id=(int) $_GET['id'];
	$itemQ="SELECT * FROM adm_branch WHERE branch_id=$branch_id";	
	$itemRow=mysqli_fetch_assoc(mysqli_query($con,$itemQ));
 
?>
		<form id="checkout-form" class="smart-form" novalidate="novalidate" method="POST" onsubmit="return checkParameters();">	
			<fieldset>

		<div class="row" style="margin-bottom: 5px;">
                <div class="col col-lg-2">
                        <label>Branch Code:</label>
                </div>
                <div class="col col-lg-3">
                        <input type="text" name="branch_Code" value="<?php echo $itemRow['branch_Code'];?>" class="form-control" readonly="readonly">
                </div>
    
                <div class="col col-lg-2">
                        <label>Branch Number:</label>
                </div>
                <div class="col col-lg-3">
                        <input type="text" name="branch_CodeNumber" value="<?php echo $itemRow['branch_CodeNumber'];?>" class="form-control" readonly="readonly">
                </div>
            </div>
            
            <div class="row" style="margin-bottom: 5px;">
                <div class="col col-lg-2">
                        <label>Store Name:</label>
                </div>
                <div class="col col-lg-3">
                        <input type="text" name="branch_Name" id="branch_Name" class="form-control" value="<?php echo $itemRow['branch_Name'];?>" >
                </div>
    
                <div class="col col-lg-2">
                        <label>Address:</label>
                </div>
                <div class="col col-lg-3">
                        <input type="text" name="branch_Address" class="form-control" value="<?php echo $itemRow['branch_Address'];?>" >
                </div>
            </div>
			

		<!--2nd row start here-->
		<div class="row" style="margin-bottom: 5px;">
                <div class="col col-lg-2">
                        <label>Phone1:</label>
                </div>
                <div class="col col-lg-3">
                        <input type="text" name="branch_Phone1" class="form-control" value="<?php echo $itemRow['branch_Phone1'];?>" >
                </div>
    
                <div class="col col-lg-2">
                        <label>Phone2:</label>
                </div>
                <div class="col col-lg-3">
                        <input type="text" name="branch_Phone2" class="form-control" value="<?php echo $itemRow['branch_Phone2'];?>" >
                </div>
         </div>
         
            
         <div class="row" style="margin-bottom: 5px;">
                <div class="col col-lg-2">
                        <label>Location (State):</label>
                </div>
                <div class="col col-lg-3">
                        <input type="text" name="branch_City" class="form-control" value="<?php echo $itemRow['branch_City'];?>" >
                </div>
    
                <div class="col col-lg-2">
                        <label>Web Address:</label>
                </div>
                <div class="col col-lg-3">
                        <input type="text" name="branch_Web" class="form-control" value="<?php echo $itemRow['branch_Web'];?>" >
                </div>
         </div>
         <div class="row" style="margin-top:5px;">
						<div class="col col-lg-10 col-xs-12">
							<div class="alert alert-success"><strong>Branch Admin Login Detail</strong></div>
						</div>
					</div><!--End of row-->
         
         
         <div class="row" style="margin-bottom: 5px;">
                <div class="col col-lg-2">
                        <label>Email:</label>
                </div>
                <div class="col col-lg-3">
                        <input type="text" name="branch_Email" id="branch_Email" class="form-control" value="<?php echo $itemRow['branch_Email'];?>" >
                </div>
                <div class="col col-lg-2">
                        <label>Confirm Email:</label>
                </div>
                <div class="col col-lg-3">
                        <input type="text" name="branch_EmailConfirm" id="branch_EmailConfirm" class="form-control" value="<?php echo $itemRow['branch_EmailConfirm'];?>" >
                </div>
    </div>
    		<div class="row" style="margin-bottom: 5px;">
                <div class="col col-lg-2">
                        <label>Password:</label>
                </div>
                <div class="col col-lg-3">
                        <input type="password" name="branch_Password" id="branch_Password" class="form-control" value="<?php echo $itemRow['branch_Password2'];?>" >
                </div>
                <div class="col col-lg-2">
                        <label>Confirm Password:</label>
                </div>
                <div class="col col-lg-3">
                        <input type="password" name="branch_PasswordConfirm" id="branch_PasswordConfirm" class="form-control" value="<?php echo $itemRow['branch_Password2'];?>" >
                </div>
         </div>
         
         
         
         
         <div class="row" style="margin-top:5px;">
						<div class="col col-lg-10 col-xs-12">
							<div class="alert alert-success"><strong>Branch Sales Target</strong></div>
						</div>
					</div><!--End of row-->
         <div class="row" style="margin-bottom: 5px;">
                <div class="col col-lg-2">
                        <label>Allow Target Light:</label>
                </div>
                <div class="col col-lg-3">
                        <input type="checkbox" name="branch_SalesTargetAllow" class="form-control" <?php if($itemRow['branch_SalesTargetAllow']==1) {echo 'checked';}?> >
                </div>
                
                <div class="col col-lg-2">
                        <label>Target Light:</label>
                </div>
                <div class="col col-lg-3">
                        <input type="text" name="branch_SalesTarget" class="form-control" value="<?php echo $itemRow['branch_SalesTarget'];?>" >
                </div>
         </div>   
        
        
        <div class="row" style="margin-top:5px;">
						<div class="col col-lg-10 col-xs-12">
							<div class="alert alert-success"><strong>Branch Setting</strong></div>
						</div>
					</div><!--End of row-->
         <div class="row" style="margin-bottom: 5px;">
                <div class="col col-lg-2">
                        <label>Country:</label>
                </div>
                <div class="col col-lg-3">
                        <select class="select2" name="country_id">
                        	<?php $countryArray=get_Countries();
								 foreach ($countryArray as $key => $countryRow) { 
								?>
								<option value="<?php echo $countryRow['country_id'];?>" <?php if($countryRow['country_id']==$itemRow['country_id']){ echo "selected='selected'";} ?>  ><?php echo $countryRow['country_name'];?></option>
								<?php } 
								?>
                        </select>
                </div>
    
                <div class="col col-lg-2">
                        <label>Currency:</label>
                </div>
                <div class="col col-lg-3">
                        <select class="select2" name="currency_id">
                        <option value="0">Select Currency</option>
                        	<?php $currencyArray=get_Currencies();
								 foreach ($currencyArray as $key => $currencyRow) { 
								?>
								<option value="<?php echo $currencyRow['currency_id'];?>"  <?php if($currencyRow['currency_id']==$itemRow['currency_id']){ echo "selected='selected'";} ?>   ><?php echo $currencyRow['currency_Name']. ' '. $currencyRow['currency_Symbol'];?></option>
								<?php } 
								?>
                        </select>
                </div>
         </div>
         
         <div class="row" style="margin-bottom: 5px;">
                <div class="col col-lg-2">
                        <label>TimeZone:</label>
                </div>
                <div class="col col-lg-3">
                        <select class="select2" name="branch_TimeZone">
                        	<?php $timezoneArray=get_TimeZone();
								 foreach ($timezoneArray as $key => $timezoneRow) { 
								?>
								<option value="<?php echo $timezoneRow['timezone_name'];?>"  <?php if($timezoneRow['timezone_name']==$itemRow['branch_TimeZone']){ echo "selected='selected'";} ?>   ><?php echo $timezoneRow['timezone_name'];?></option>
								<?php } 
								?>
                        </select>
                </div>
    
                
         </div>
		</fieldset>
			<footer>
				<input type="hidden" name="branch_id" value="<?php echo $itemRow['branch_id'];?>">
				<button type="submit" class="btn btn-primary" name="update">Save</button>
			</footer>
		</form>
		
<?php } 
?>
			</div><!--End of div id="add"-->

			<div role="tabpanel" class="tab-pane" id="list">


					<table id="datatable_fixed_column" class="table table-striped table-bordered" width="100%">

				 <thead>
							<tr>
								
								
								<th class="hasinput" style="width:8%">
									<input type="text" class="form-control" placeholder="Name" />
								</th>
								<th class="hasinput" style="width:8%">
									<input type="text" class="form-control" placeholder="Code"  />
								</th>
								<th class="hasinput" style="width:8%" >
									<input type="text" placeholder="Address" class="form-control">
								</th>
								<th class="hasinput" style="width:8%" >
									<input type="text" placeholder="Email" class="form-control">
								</th>
								<th class="hasinput" style="width:8%" >
									<input type="text" placeholder="City" class="form-control">
								</th>
 								<th class="hasinput" style="width:8%">
									
								</th>
							</tr>	
							<tr>
								<th>Name</th>
								<th>Code</th>
								<th>Address</th>
								<th>Email</th>
								<th>City</th>
								<th>Action</th>
							</tr>
						</thead>
						<tbody>
							
<?php
$Q="SELECT * FROM `adm_branch` WHERE 1";
$Qr=mysqli_query($con, $Q);
while($row=mysqli_fetch_assoc($Qr))
{
	
?>		
				 			
<?php echo "<tr id='row".$row['branch_id']."'>";?>
						 	<td><?php echo $row['branch_Name'];?></td>
						 	<td><?php echo $row['branch_Code'];?> </td>
						 	<td><?php echo $row['branch_Address'];?> </td>
                            <td><?php echo $row['branch_Email'];?> </td>
						 	<td><?php echo $row['branch_City'];?></td>
							<td><a href="branch_add.php?id=<?=$row['branch_id']?>" class="btn btn-primary btn-sm">Edit</a></td>
				 			</tr>
			 		
							
<?php } 
?>

				 </tbody>
				
					</table>

				</div>
				<!-- end widget content -->
			</div><!--End of list-->
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

function checkParameters(){
	var branch_Email = $.trim($("#branch_Email").val());
	var branch_EmailConfirm = $.trim($("#branch_EmailConfirm").val());
	
	var branch_Password = $.trim($("#branch_Password").val());
	var branch_PasswordConfirm = $.trim($("#branch_PasswordConfirm").val());
	var branch_Name = $.trim($("#branch_Name").val());

	if (branch_Name == "")
	{
		$.smallBox({
		title : "Error",
		content : "<i class='fa fa-clock-o'></i> <i>Name is mandatory field.</i>",
		color : "#C46A69",
		iconSmall : "fa fa-times fa-2x fadeInRight animated",
		timeout : 4000
		});
	$("#branch_Name").focus();
	return false;
	}
	
	if (branch_Email == ""  || branch_Email!==branch_EmailConfirm )
	{
		$.smallBox({
		title : "Error",
		content : "<i class='fa fa-clock-o'></i> <i>Email and Confirm Email must be match.</i>",
		color : "#C46A69",
		iconSmall : "fa fa-times fa-2x fadeInRight animated",
		timeout : 4000
		});
	$("#branch_Email").focus();
	return false;
	}
	
	if (branch_Password == "")
	{
		$.smallBox({
		title : "Error",
		content : "<i class='fa fa-clock-o'></i> <i>Password Must be fill.</i>",
		color : "#C46A69",
		iconSmall : "fa fa-times fa-2x fadeInRight animated",
		timeout : 4000
		});
	$("#branch_Password").focus();
	return false;
	}
	if (branch_Password == ""  || branch_Password!==branch_PasswordConfirm )
	{
		$.smallBox({
		title : "Error",
		content : "<i class='fa fa-clock-o'></i> <i>Password and Confirm Password must be match.</i>",
		color : "#C46A69",
		iconSmall : "fa fa-times fa-2x fadeInRight animated",
		timeout : 4000
		});
	$("#branch_Email").focus();
	return false;
	}
	
	
}


</script>