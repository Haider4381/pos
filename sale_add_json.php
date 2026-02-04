<?php
include('connection.php');
include('functions.php');
include('sessionCheck.php');
//$branch_id=$_SESSION['branch_id'];

if(isset($_POST['item_IMEI']))
{
	$branch_id=$GLOBALS['branch_id'];
	$returnArray=array();
	$item_IMEI=$_POST['item_IMEI'];
	
	$pdQ="SELECT * FROM adm_item
		WHERE
			(
				(item_Code='$item_IMEI' AND branch_id=$branch_id)
				OR
				(item_id=(Select item_id from adm_purchase_detail where branch_id=$branch_id AND item_IMEI='$item_IMEI' order by pd_id desc limit 1))
			)
			GROUP BY item_Code";
	$pdRes=mysqli_query($con,$pdQ);
	$pdResCount=mysqli_num_rows($pdRes);
	
	if($pdResCount==1)
	{
		$itemRow=mysqli_fetch_assoc($pdRes);
		$item_id=$itemRow['item_id'];
		$returnArray['item_Name']=$itemRow['item_Name'];
		$returnArray['item_id_from_imei']=$itemRow['item_id'];
		$returnArray['item_SalePrice']=$itemRow['item_SalePrice'];
		$returnArray['item_PurchasePrice']=$itemRow['item_PurchasePrice'];
		$returnArray['item_Code']=$itemRow['item_Code'];
		$returnArray['item_QtyInPack']=$itemRow['item_QtyInPack'];
		$returnArray['msg']='Y';
		
		$stockQ="
		SELECT ifnull(SUM(item_qty),0) as item_stock
FROM
(
    SELECT 0-item_Qty as item_qty FROM cust_sale_detail WHERE item_id=$item_id
    UNION ALL
    SELECT item_Qty as item_qty FROM adm_purchase_detail WHERE item_id=$item_id
    UNION ALL
    SELECT item_Qty as item_qty FROM cust_salereturn_detail WHERE item_id=$item_id
) as c";
		$stockQR=mysqli_query($con,$stockQ);
		$stock_row=mysqli_fetch_assoc($stockQR);
		$returnArray['item_CurrentStock']=$stock_row['item_stock'];
		
	}
	else
	{
		$returnArray['msg']='System Could Not Find IMEI.';
		
	}
	
	echo json_encode($returnArray);
}





if(isset($_POST['item_Code']))
{
	$branch_id=$GLOBALS['branch_id'];
	$returnArray=array();
	$item_Code=$_POST['item_Code'];
	
	$pdQ="SELECT * FROM adm_item WHERE item_Code='$item_Code' AND branch_id=$branch_id GROUP BY item_Code";
	$pdRes=mysqli_query($con,$pdQ);
	$pdResCount=mysqli_num_rows($pdRes);
	
	if($pdResCount==1)
	{
		$itemRow=mysqli_fetch_assoc($pdRes);
		$item_id=$itemRow['item_id'];
		$returnArray['item_Name']=$itemRow['item_Name'];
		$returnArray['item_id_from_imei']=$itemRow['item_id'];
		$returnArray['item_SalePrice']=$itemRow['item_SalePrice'];
		$returnArray['item_PurchasePrice']=$itemRow['item_PurchasePrice'];
		$returnArray['item_Code']=$itemRow['item_Code'];
		$returnArray['item_QtyInPack']=$itemRow['item_QtyInPack'];
		$returnArray['msg']='Y';
		
		$stockQ="
		SELECT ifnull(SUM(item_qty),0) as item_stock
FROM
(
    SELECT 0-item_Qty as item_qty FROM cust_sale_detail WHERE item_id=$item_id
    UNION ALL
    SELECT item_Qty as item_qty FROM adm_purchase_detail WHERE item_id=$item_id
    UNION ALL
    SELECT item_Qty as item_qty FROM cust_salereturn_detail WHERE item_id=$item_id
) as c";
		$stockQR=mysqli_query($con,$stockQ);
		$stock_row=mysqli_fetch_assoc($stockQR);
		$returnArray['item_CurrentStock']=$stock_row['item_stock'];
		
	}
	else
	{
		$returnArray['msg']='System Could Not Find Item With This Product Code.';
		
	}
	
	echo json_encode($returnArray);
}
?>