<?php include('connection.php');
 
?>
<!DOCTYPE html>
<html>
<head>
	<title>Payment Reconciliation</title>
<style type="text/css">
*{
	margin:0px auto;
	padding:0px;
}
body
{
	width:100%;
	height:600px;
	margin:0px auto;
	padding:0px;
	border:solid red 0px;
}
tr{ font-size:18px;}
tr:nth-child(even){background-color: #E9FaB4}
#main_container
{
	width:100%;
	min-width:1024px;
	margin:0px auto;
	padding:0px;
}
#main_centerBody
{
	width:100%;

	margin:0px auto;

	padding:0px;

	border:solid yellow 0px;

}
#main_centerBody tr:nth-child(n+3):hover{
	background:#ccff33;

}

</style>
</head>
<body>

<?php $branchQ="SELECT branch_id, branch_Name, branch_Address, branch_Phone1, branch_Phone2, branch_Email,branch_Web FROM adm_branch WHERE 1";
 $branchRow=mysqli_fetch_assoc(mysqli_query($con,$branchQ));
 
?>
<table style="width:3.125in;" align="center" border="0">
	<tr><td style="text-align:center;"><h2><?php echo $branchRow['branch_Name'];?></h2><?php echo $branchRow['branch_Address'];?><br />Phone: <?php echo $branchRow['branch_Phone1'];?></td></tr>
    <tr><td style="text-align:center;font-size:23px;"><strong>Payment Reconciliation</strong></td></tr>
</table>



	<div id="main_container">
		<div id="main_centerBody">
			 <table border="0" bordercolor="#E6E6E6" align="center" style="font-size:14px;">

		 			<tr style="background-color:#808080;color:#FFFFFF;">
		 				<td width="20px" style="text-align: left;">#</td>
						<td width="400px" style="text-align: left;">Customer Name</td>
						<td width="200px" style="text-align: center;">Sales</td>
						<td width="200px" style="text-align: center;">Sales Return</td>
						<td width="200px" style="text-align: center;">Sales Paymemts</td>
						<td width="200px" style="text-align: center;">Sales Return Paymemts</td>
						<td width="200px" style="text-align: center;">Balance</td>
					</tr>
					
			
<?php
$clientBQ="SELECT
							C.client_id, C.client_Name,
							(SELECT ifnull(SUM(CS.s_NetAmount),0) FROM cust_sale AS CS WHERE C.client_id=CS.client_id) AS s_NetAmount,
							(SELECT ifnull(SUM(SR.sr_NetAmount),0) FROM cust_salereturn AS SR WHERE C.client_id=SR.client_id) AS sr_NetAmount,
							(SELECT ifnull(SUM(SP1.sp_Amount),0) FROM adm_sale_payment AS SP1 WHERE C.client_id=SP1.client_id AND SP1.sp_Type='S') AS sale_Payment,
							(SELECT ifnull(SUM(SP2.sp_Amount),0) FROM adm_sale_payment AS SP2 WHERE C.client_id=SP2.client_id AND SP2.sp_Type='SR') AS sale_ReturnPayment
						FROM adm_client AS C
						WHERE 1
						ORDER BY C.client_id";
	$clientBQR=mysqli_query($con,$clientBQ);
	$count=$total_client_balance=0;
	while($row = mysqli_fetch_assoc($clientBQR))
	{
		$count++;
		$balance=0;
		$balance=$row['s_NetAmount']-$row['sr_NetAmount']-$row['sale_Payment']+$row['sale_ReturnPayment'];
?>
					<tr bgcolor=#F5F5F5>
						<td ><?php echo $count;?></td>
						<td style="text-align: leftt;"><?php echo $row['client_Name'];?></td>
						<td style="text-align: right;"><?php echo $row['s_NetAmount'];?></td>
						<td style="text-align: right;"><?php echo $row['sr_NetAmount'];?></td>
						<td style="text-align: right;"><?php echo $row['sale_Payment']; ?></td>
						<td style="text-align: right;"><?php echo $row['sale_ReturnPayment'];?></td>
						<td style="text-align: right;"><?php echo number_format($balance,0);?></td>
					</tr>
<?php
$total_client_balance+=$balance;
} 
?>
	<tr style="background:#333 !important;color:#FFF !important">
						<td colspan="6" style="text-align:right;">Total</td>
						<td width="200px" style="text-align: right;font-weight:bold;font-size:25px;"><?php echo number_format($total_client_balance,0);?></td>
			</tr>
		</table>
 
 
<br>
<br>

<table style="width:3.125in;" align="center" border="0">
 <tr><td style="text-align:center;font-size:23px;"><strong>Supplier Balance Report</strong></td></tr>
</table>
 
 
 <table border="0" bordercolor="#E6E6E6" align="center" style="font-size:14px;">

		 			<tr style="background-color:#808080;color:#FFFFFF;">
		 				<td width="20px" style="text-align: left;">#</td>
						<td width="400px" style="text-align: left;">Supplier Name</td>
						<td width="200px" style="text-align: center;">Purchases</td>
						<td width="200px" style="text-align: center;">Purchases Return</td>
						<td width="200px" style="text-align: center;">Purhcases Paymemts</td>
						<td width="200px" style="text-align: center;">Purhcases Return Paymemts</td>
						<td width="200px" style="text-align: center;">Balance</td>
					</tr>
					
			
<?php $supplierBQ="SELECT S.sup_id,S.sup_Name,(SELECT ifnull(SUM(P.p_NetAmount),0) FROM adm_purchase AS P WHERE P.sup_id=S.sup_id) AS p_NetAmount,(SELECT ifnull(SUM(PR.pr_NetAmount),0) FROM adm_purchasereturn AS PR WHERE PR.sup_id=S.sup_id) AS pr_NetAmount,(SELECT ifnull(SUM(PAY1.pp_Amount),0) FROM adm_purchase_payment AS PAY1 WHERE PAY1.sup_id=S.sup_id AND PAY1.pp_Type='P') AS purcahse_Payment,(SELECT ifnull(SUM(PAY2.pp_Amount),0) FROM adm_purchase_payment AS PAY2 WHERE PAY2.sup_id=S.sup_id AND PAY2.pp_Type='PR') AS purcahse_ReturnPayment
			FROM adm_supplier AS S 
			WHERE 1
			ORDER BY S.sup_id";
 $supplierBQR=mysqli_query($con,$supplierBQ);
 $count=0;
 $total_supplier_balance=0;
 while($row = mysqli_fetch_assoc($supplierBQR))
 {
	$count++;
	$balance=0;
	$balance=$row['p_NetAmount']-$row['pr_NetAmount']-$row['purcahse_Payment']+$row['purcahse_ReturnPayment'];
 
?>
					<tr bgcolor=#F5F5F5>
						<td ><?php echo $count;?></td>
						<td style="text-align: leftt;"><?php echo $row['sup_Name'];?></td>
						<td style="text-align: right;"><?php echo $row['p_NetAmount'];?></td>
						<td style="text-align: right;"><?php echo $row['pr_NetAmount'];?></td>
						<td style="text-align: right;"><?php echo $row['purcahse_Payment'];?></td>
						<td style="text-align: right;"><?php echo $row['purcahse_ReturnPayment'];?></td>
						<td style="text-align: right;"><?php echo number_format($balance,0);?></td>
					</tr>
				
<?php $total_supplier_balance+=$balance;
 } 
?>
 <tr style="background:#333 !important; color:#FFF !important">
						<td colspan="6" style="text-align:right;">Total</td>
						<td width="200px" style="text-align: right;font-weight:bold;font-size:25px;"><?php echo number_format($total_supplier_balance,0);?></td>
			</tr>
		</table>
 
		</div>
	</div>
    
<script>
$(document).ready(function() {
    $('#example').DataTable( {
        dom: 'Bfrtip',
        buttons: [
            'copyHtml5',
            'excelHtml5',
            'csvHtml5',
            'pdfHtml5'
        ]
    } );
} );
</script>
    
    
</body>
</html>