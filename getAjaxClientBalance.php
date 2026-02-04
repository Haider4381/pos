<?php
include('connection.php');
$returnArray=[];
if(isset($_POST['client_id'])){
	$client_id=$_POST['client_id'];
	$balance=0;
	$storecredit=0;
	
	$clientBQ="SELECT
							C.client_id, C.client_Name,
							(SELECT ifnull(SUM(CS.s_NetAmount),0) FROM cust_sale AS CS WHERE C.client_id=CS.client_id) AS s_NetAmount,
							(SELECT ifnull(SUM(SR.sr_NetAmount),0) FROM cust_salereturn AS SR WHERE C.client_id=SR.client_id) AS sr_NetAmount,
							(SELECT ifnull(SUM(SP1.sp_Amount),0) FROM adm_sale_payment AS SP1 WHERE C.client_id=SP1.client_id AND SP1.sp_Type='S') AS sale_Payment,
							(SELECT ifnull(SUM(SP2.sp_Amount),0) FROM adm_sale_payment AS SP2 WHERE C.client_id=SP2.client_id AND SP2.sp_Type='SR') AS sale_ReturnPayment
						FROM adm_client AS C
						WHERE C.client_id=$client_id
						ORDER BY C.client_id";
	$clientBQR=mysqli_query($con,$clientBQ);
	$balance_rows=mysqli_num_rows($clientBQR);
	
	if($balance_rows==1)
	{
		$row = mysqli_fetch_assoc($clientBQR);	
		$balance=$row['s_NetAmount']-$row['sr_NetAmount']-$row['sale_Payment']+$row['sale_ReturnPayment'];
		$returnArray['status']='Y';
		$returnArray['client_balance']=number_format($balance,2);
		$client_payment_history='<tr style=" background:#06F; color:#FFF; font-size:15px;"><th colspan="3" style="padding:2px;">Payment History</th></tr><tr style=" background:#333; color:#FFF; font-size:15px;"><td>Payment Date</td><td>Bill Amount</td><td>Notes</td></tr>';
		$clientHistoryQ="SELECT * FROM adm_sale_payment WHERE client_id=$client_id AND sp_Type='S' ORDER BY sp_Date";
		$clientHistoryQR=mysqli_query($con,$clientHistoryQ);
		while($rows=mysqli_fetch_assoc($clientHistoryQR))
		{
			$client_payment_history.='<tr><td style="border:1px solid #333; padding:2px;">'.$rows["sp_Date"].'</td><td style="border:1px solid #333; padding:2px;">'.$rows["sp_Amount"].'</td><td style="border:1px solid #333; padding:2px;">'.$rows["sp_Description"].'</td></tr>';
		}
		$returnArray['client_payment_history']=$client_payment_history;		
	}
	else
	{
		$returnArray['status']='N';
 		$returnArray['client_balance']=0;
	}
	
	
	$scBQ="SELECT ifnull(SUM(SC.sp_Amount),0) as storecredit FROM adm_sale_payment AS SC WHERE SC.client_id=$client_id AND SC.sp_Type='SC'";
	$scBQR=mysqli_query($con,$scBQ);
	$sc_balance_rows=mysqli_num_rows($clientBQR);
	$sc_row = mysqli_fetch_assoc($scBQR);
	$returnArray['client_storecredit']=$sc_row['storecredit'];	
	
	echo json_encode($returnArray);
}
?>