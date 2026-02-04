<?php
include('connection.php');
$returnArray=[];
if(isset($_POST['sup_id_for_purchase_history'])){
	$sup_id=$_POST['sup_id_for_purchase_history'];
	$balance=0;
	$bill_drop_down='<option value="">Select Bill No.</option>';
	$payment_history='<table style="width:100%; border: 6px solid silver;" class="table table-bordered">
			<tr style=" background:#009DFF; color:#FFF; font-size:16px;">
			    <th colspan="6" style="padding:12px; text-align:center;">Vendor Pending Payments</th>
			</tr>
			<tr style=" background:#333; color:#FFF; font-size:12px; text-align:center;">
			    <td>Purchased Date</td>
			    <td>Bill No.</td>
			    <td>Bill Ref No.</td>
			    <td>Bill Amount</td>
			    <td>Paid Amount</td>
			    <td>Remaining Amount</td>
			</tr>';
	
	$clientBQ="SELECT p.p_id, p_Date, p.p_Number,p.p_BillNo, p.p_NetAmount, 
					ifnull((SELECT SUM(pp_Amount) FROM adm_purchase_payment WHERE adm_purchase_payment.p_id=p.p_id and sup_id=$sup_id AND pp_Type='P'),0) as p_PaidAmount
				FROM adm_purchase as p
				WHERE p.sup_id=$sup_id
				ORDER BY p.p_Number";
	$clientBQR=mysqli_query($con,$clientBQ);
	$balance_rows=mysqli_num_rows($clientBQR);
	
	if($balance_rows>0)
	{
		while($rows=mysqli_fetch_assoc($clientBQR))
		{
			$row_balance=$rows['p_NetAmount']-$rows['p_PaidAmount'];
			$balance=$balance+$row_balance;
			if($row_balance>0)
			{
				$payment_history.='<tr>
					<td style="border:1px solid #9e9a9a; padding:2px; text-align:center;">'.date('d-m-Y', strtotime($rows["p_Date"])).'</td>
					<td style="border:1px solid #9e9a9a; padding:2px; text-align:center;">'.$rows["p_Number"].'</td>
					<td style="border:1px solid #9e9a9a; padding:2px; text-align:center;">'.$rows["p_BillNo"].'</td>
					<td style="border:1px solid #9e9a9a; padding:2px; text-align:center;">'.$rows["p_NetAmount"].'</td>
					<td style="border:1px solid #9e9a9a; padding:2px; text-align:center;">'.$rows["p_PaidAmount"].'</td>
					<td style="border:1px solid #9e9a9a; padding:2px; text-align:center;">'.number_format($rows["p_NetAmount"]-$rows["p_PaidAmount"],2).'</td></tr>';
				$bill_drop_down.="<option value='".$rows['p_id']."'>".$rows['p_Number']."</option>";
			}
		}
		//$payment_history.='</table>';
		$returnArray['status']='Y';
		$returnArray['supplier_balance']=number_format($balance,2);		
		$returnArray['payment_history']=$payment_history;
		$returnArray['supplier_bill_dropdown']=$bill_drop_down;
	}
	else
	{
		$returnArray['status']='Y';
 		$returnArray['supplier_balance']='';		
		$returnArray['payment_history'].='<tr><td colspan="6">No Pending Payments</td></tr></table>';
		$returnArray['supplier_bill_dropdown']=$bill_drop_down;
	}
	echo json_encode($returnArray);
}
?>