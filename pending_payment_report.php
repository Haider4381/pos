<?php include('connection.php');?>
<!DOCTYPE html>
<html>
<head>
<title>Pending Payemnts</title>

<link href="https://cdn.datatables.net/1.10.20/css/jquery.dataTables.min.css" type="text/css" rel="stylesheet">
<link href="https://cdn.datatables.net/buttons/1.6.1/css/buttons.dataTables.min.css" type="text/css" rel="stylesheet">
</head>
<body>

<?php
$branchQ="SELECT branch_id, branch_Name, branch_Address, branch_Phone1, branch_Phone2, branch_Email,branch_Web FROM adm_branch WHERE 1";
$branchRow=mysqli_fetch_assoc(mysqli_query($con,$branchQ));
 
?>
<table style="width:3.125in;" align="center" border="0">
	<tr><td style="text-align:center;"><h2><?php echo $branchRow['branch_Name'];?></h2><?php echo $branchRow['branch_Address'];?><br />Phone: <?php echo $branchRow['branch_Phone1'];?></td></tr>
	<tr><td style="text-align:center;font-size:23px;"><strong>Pending Payments</strong></td></tr>
</table>

<table id="example" class="display" style="width:100%">
		 			
                    <thead>
                    <tr >
		 	
						<td width="400px" style="text-align: left;">Customer Name</td>
						<td width="200px" style="text-align: center;">Sales</td>
						<td width="200px" style="text-align: center;">Sales Return</td>
						<td width="200px" style="text-align: center;">Sales Paymemts</td>
						<td width="200px" style="text-align: center;">Sales Return Paymemts</td>
						<td width="200px" style="text-align: center;">Balance</td>
					</tr>
                    <thead>
                    <tbody>							
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
					<tr>
				
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
		</tbody>
        <tfoot>
        	
            <tr >
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td style="text-align:right;">Total</td>
						<td width="200px" style="text-align: right;font-weight:bold;font-size:25px;"><?php echo number_format($total_client_balance,0);?></td>
			</tr>
            
            
        </tfoot>
		</table>

<?php include('include_datatables_files_js.php'); ?>
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