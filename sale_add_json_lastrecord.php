<?php
include('connection.php');
$returnArray=[];


if(isset($_POST['get_last_records']))
{
	$item_id=$_POST['item_id'];
	$client_id=$_POST['client_id'];
	$item_Code=$_POST['item_Code'];
	$type=$_POST['where_type'];

	if($type=='item_id')
	{
		$where="WHERE cust_sale_detail.item_id='$item_id'";
	}

	if($type=='itemcode')
	{
		$where="WHERE item_Code='$item_Code'";
	}
	


	$itemQ="SELECT s_Number, s_Date, cust_sale_detail.item_SalePrice, item_Qty, item_NetPrice
			FROM `cust_sale_detail`
			LEFT OUTER JOIN cust_sale ON cust_sale.s_id=cust_sale_detail.s_id
			LEFT OUTER JOIN adm_item ON adm_item.item_id=cust_sale_detail.item_id
			$where
			order by s_Number DESC
			LIMIT 5";

	$returnArray['last_sale_records']='';
	$itemRes=mysqli_query($con,$itemQ);
	
	while($itemRow=mysqli_fetch_assoc($itemRes))
	{
		$returnArray['last_sale_records'].='
		<tr>
			<td style="text-align:center;">'.$itemRow['s_Number'].'</td>
			<td style="text-align:center;">'.date('d-m-Y', strtotime($itemRow['s_Date'])).'</td>
			<td style="text-align:center;">'.$itemRow['item_SalePrice'].'</td>
			<td style="text-align:center;">'.$itemRow['item_Qty'].'</td>
			<td style="text-align:center;">'.$itemRow['item_NetPrice'].'</td>
		</tr>';
	}
	$returnArray['msg']='Y';
	echo json_encode($returnArray);
}





?>