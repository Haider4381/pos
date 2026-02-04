<?php
include('connection.php');
include('functions.php');
if(isset($_POST['item_Rate']))
{
	$returnArray=array();
	$item_SalePrice=$_POST['item_Rate'];
	$item_CostPrice=$_POST['item_CostPrice'];
	
	$pdQ="INSERT INTO cust_sale_customerdisplay (item_SalePrice,item_CostPrice[])";
	$pdRes=mysqli_query($con,$pdQ);
	$pdResCount=mysqli_num_rows($pdRes);
	
	if($pdRes)
	{
		$returnArray['msg']='YYY';
	}
	else
	{
		$returnArray['msg']='NNN';
	}
	echo json_encode($returnArray);
}
?>