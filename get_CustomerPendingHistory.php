<?php
include('connection.php');
$returnArray=[];
if(isset($_POST['client_id_for_sale_history'])){
	$client_id=$_POST['client_id_for_sale_history'];
	$balance=0;
	$bill_drop_down='<option value="">Select Bill No.</option>';
	$payment_history='<table style="width:100%; border: 6px solid silver;" class="table table-bordered">
			<tr style=" background:#009DFF; color:#FFF; font-size:16px;">
			    <th colspan="6" style="padding:12px; text-align:center;">Customer Pending Payments</th>
			</tr>
			<tr style=" background:#333; color:#FFF; font-size:12px; text-align:center;">
			    <td>Sale Date</td>
			    <td>Invoice No.</td>
			    <td>Invoice Amount</td>
			    <td>Received Amount</td>
			    <td>Pending Amount</td>
			</tr>';
	
	 $clientBQ="SELECT
					s.s_id, s_Date, s_Number, s_NetAmount, 
					(SELECT ifnull(SUM(SR.sr_NetAmount),0) FROM cust_salereturn AS SR WHERE s.s_id=SR.s_id AND SR.client_id=$client_id) AS sr_NetAmount,
					(SELECT ifnull(SUM(SP1.sp_Amount),0) FROM adm_sale_payment AS SP1 WHERE SP1.client_id=$client_id AND s.s_id=SP1.s_id AND SP1.sp_Type='S') AS sale_Payment,
					(SELECT ifnull(SUM(SP2.sp_Amount),0) FROM adm_sale_payment AS SP2 WHERE SP2.client_id=$client_id AND SP2.s_id=(SELECT sr_id from cust_salereturn WHERE s_id=s.s_id) AND SP2.sp_Type='SR') AS sale_ReturnPayment
				FROM cust_sale as s
				WHERE s.client_id=$client_id
				ORDER BY s.s_Number";
	$clientBQR=mysqli_query($con,$clientBQ);
	$balance_rows=mysqli_num_rows($clientBQR);
	
	if($balance_rows>0)
	{
		while($rows=mysqli_fetch_assoc($clientBQR))
		{
			//$row_balance=$rows['s_NetAmount']-$rows['sr_NetAmount']-$rows['sale_Payment']+$rows['sale_ReturnPayment'];
			$row_balance=$rows['s_NetAmount']-$rows['sale_Payment'];
			$balance=$balance+$row_balance;
			if($row_balance>0)
			{
				$payment_history.='<tr>
					<td style="border:1px solid #9e9a9a; padding:2px; text-align:center;">'.date('d-m-Y', strtotime($rows["s_Date"])).'</td>
					<td style="border:1px solid #9e9a9a; padding:2px; text-align:center;">'.$rows["s_Number"].'</td>
					<td style="border:1px solid #9e9a9a; padding:2px; text-align:center;">'.$rows["s_NetAmount"].'</td>
					<td style="border:1px solid #9e9a9a; padding:2px; text-align:center;">'.($rows['sale_Payment']).'</td>
					<td style="border:1px solid #9e9a9a; padding:2px; text-align:center;">'.number_format($row_balance,2).'</td></tr>';
				$bill_drop_down.="<option value='".$rows['s_id']."'>".$rows['s_Number']."</option>";
			}
		}
		//$payment_history.='</table>';
		$returnArray['status']='Y';
		$returnArray['customer_balance']=number_format($balance,2);		
		$returnArray['payment_history']=$payment_history;
		$returnArray['customer_bill_dropdown']=$bill_drop_down;
	}
	else
	{
		$returnArray['status']='Y';
 		$returnArray['supplier_balance']='';		
		$returnArray['payment_history'].='<tr><td colspan="6">No Pending Payments</td></tr></table>';
		$returnArray['customer_bill_dropdown']=$bill_drop_down;
	}
	echo json_encode($returnArray);
}
?>