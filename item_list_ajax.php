<?php 
include('connection.php');

if(isset($_POST['rate_type']))
{
	$rate_type = $_POST['rate_type'];
	$item_id = $_POST['item_id'];
	$rate = $_POST['rate'];

	$Q="
	UPDATE adm_item
	SET $rate_type='$rate'
	WHERE item_id='$item_id'";
	$QQ=mysqli_query($con,$Q);
	if(!$QQ)
	{
		$row['msg']='data not select';
	}
	else
	{
		$row['msg']='Y';
	}
	echo json_encode($row);
}


?>