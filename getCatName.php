<?php
include('connection.php');
include('functions.php');
include('sessionCheck.php');
//$branch_id=$_SESSION['branch_id'];

if(isset($_POST['icat_name']))
{
	$branch_id=$GLOBALS['branch_id'];
	$returnArray=array();
	$icat_name=$_POST['icat_name'];
	
	$pdQ="SELECT * FROM adm_itemcategory
		WHERE icat_name='$icat_name'";
	$pdRes=mysqli_query($con,$pdQ);
	$pdResCount=mysqli_num_rows($pdRes);
	
	if($pdResCount>=1)
	{
		//$itemRow=mysqli_fetch_assoc($pdRes);
		$returnArray['msg']='Category Name already Exists';
	}
	else
	{
		$returnArray['msg']='N';
		
	}

	echo json_encode($returnArray);
}
?>