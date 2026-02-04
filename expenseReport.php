<?php
include('sessionCheck.php');
include('connection.php');
include('functions.php');
$branch_id=$_SESSION['branch_id'];

?>
<!DOCTYPE html>
<html>
<head>
<title>Expens Report </title>
<link href="css/bootstrap.min.css" type="text/css" rel="stylesheet" />
<style type="text/css">
*
{
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
</style>
</head>
<body>
<?php 
if(!isset($_POST['submit']))
{
	echo "Bad page access";
	die();
}
?>
	<div id="main_container">
		<div id="main_centerBody">
<?php
$branchQ="SELECT * FROM adm_branch WHERE branch_id=$branch_id";
$branchRow=mysqli_fetch_assoc(mysqli_query($con,$branchQ)); 
//$logo_source='<img src="img/weblogo.png" height="70">';
?>
<table style="width:100%;" align="center" border="0">
	
	<tr><td style="text-align:center;"><h3><?php echo $branchRow['branch_Name'];?></h3><?php echo $branchRow['branch_Address'];?><br /><?php echo $branchRow['branch_Phone1'];?></td></tr>
</table>
						
						 <br></div>				
						 <div style=" margin-left:0%; text-align:center;"><b style="font-size:20px;">Expens Report</b><br/></div>
                         <table style="font-weight:bold; font-size:15px;" cellpadding="3">
                         	<tr>
                            	<td>From Date: </td>
                                <td><?=validate_date_display($_POST['from_date'])?></td>
                                <td>&nbsp;&nbsp;&nbsp;</td>
                                <td>To Date: </td>
                                <td><?=validate_date_display($_POST['to_date'])?></td>
                            </tr>
                         </table>

<table border="0" bordercolor="#E6E6E6" align="center" style="font-size:14px; width:90%;" class="table table-bordered table-condensed table-hovered">
	
					<tr style="background-color:#808080;color:#FFFFFF;">
                    	<th style="width:10%;">Sr.</th> 
						<th style="width:15%;">Date</th> 
                        <th style="width:30%;">Payee</th>                         
                        <th style="width:30%;">Description</th>
                        <th style="width:15%;">Amount</th>
					</tr>
					<?php 
					$where=" WHERE 1";
					if(!empty($_POST['from_date']))
					{
						$from_date=date('Y-m-d',strtotime($_POST['from_date']));
						$where.=" AND expense_date>='$from_date'";
					}
					if(!empty($_POST['to_date']))
					{
						$to_date=date('Y-m-d',strtotime($_POST['to_date']));
						$where.=" AND expense_date<='$to_date'";
					}
					if(!empty($_POST['payee_id']))
					{
						$payee_id=$_POST['payee_id'];
						$where.=" AND adm_expenses.payee_id='$payee_id'";
					}
					
					
					
					$query ="
SELECT expense_date, payee_name, expense_notes, expense_amount
FROM `adm_expenses`
LEFT OUTER JOIN adm_payee ON adm_payee.payee_id=adm_expenses.payee_id
$where AND adm_expenses.branch_id=$branch_id
ORDER BY expense_date
";
					//echo '<pre>'.$query.'</pre>';
					$results = mysqli_query($con,$query);
					if(mysqli_num_rows($results)<1)
					{
						echo "<tr><td colspan='5'>There is no record found</td></tr>";
						die();
					}
					$net_total=0;
					$serial=1;

					while($row=mysqli_fetch_assoc($results))
					{
						$net_total=$net_total+$row['expense_amount'];					
					?>
					<tr>
						<td><?=$serial?></td>
                        <td style="text-align:left;"><?= validate_date_display($row['expense_date']);?></td>
						<td><?= $row['payee_name'];?></td>
                        <td><?= $row['expense_notes'];?></td>
                        <td style="text-align:right;"><?= $currency_symbol.number_format($row['expense_amount'],0);?></td>
					</tr>
						<?php	
					$serial++;
					}
					?>
                    <tr style="background:#ddd; font-weight:bold;">
                    	<td style="text-align:left;" colspan="4"><strong>Total</strong></td>
						<td style="text-align:right;"><?= $currency_symbol.number_format($net_total,2);?></td>
					</tr>
		</table>
		</div>
	</div>
</body>
</html>