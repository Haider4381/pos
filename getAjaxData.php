<?php 
include('connection.php');


	$item_id = $_POST['item_id'];
	$item_idQ="SELECT item_PurchasePrice, item_SalePrice, item_WorkPrice, item_CostPrice FROM `ass_adm_item` WHERE item_id=$item_id";
	$item_idRes=mysqli_query($con,$item_idQ);
	if(!$item_idRes)
	{
		$row['msg']='data not selectee';	
	}
	$item_idRow=mysqli_fetch_assoc($item_idRes);
	
		$row['item_PurchasePrice']=$item_idRow['item_PurchasePrice'];
		$row['item_SalePrice']=$item_idRow['item_SalePrice'];
		$row['item_WorkPrice']=$item_idRow['item_WorkPrice'];
		$row['item_CostPrice']=$item_idRow['item_CostPrice'];

	$row['msg']='Y';
	echo json_encode($row);


?>