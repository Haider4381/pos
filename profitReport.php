<?php
include('sessionCheck.php');
include('connection.php');
include('functions.php');
$branch_id=$_SESSION['branch_id'];

function get_ExpensesOfDate($date)
{
	global $con;
	$branch_id=$_SESSION['branch_id'];
	$Q="SELECT ifnull(SUM(expense_amount),0) as expense FROM `adm_expenses` WHERE branch_id=$branch_id and expense_date='$date';";
	$Res=mysqli_fetch_assoc(mysqli_query($con,$Q));
	$expense=$Res['expense'];
	return $expense;
}

function get_SalesReturnOfDate($date)
{
	global $con;
	$branch_id=$_SESSION['branch_id'];
	$Q="SELECT ifnull(SUM(sr_NetAmount),0) as salesreturn FROM `cust_salereturn` WHERE branch_id=1 and sr_Date='$date';";
	$Res=mysqli_fetch_assoc(mysqli_query($con,$Q));
	$salesreturn=$Res['salesreturn'];
	return $salesreturn;
}

?>
<!DOCTYPE html>
<html>
<head>
<title>Profit Report </title>
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
						 <div style=" margin-left:0%; text-align:center;"><b style="font-size:20px;">Profit Report</b><br/></div>
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
						<th style="width:10%;">Date</th> 
                        <th style="width:15%;">Total Sales</th>                         
                        <th style="width:15%;">Total Sales Return</th>
                        <th style="width:12%;">Total Cost</th>                         
                        <th style="width:12%;">Total Profit</th>  
                        <th style="width:12%;">Total Discount</th>
                        <th style="width:12%;">Total VAT</th>
                        <th style="width:12%;">Total Expenses</th>
                        
					</tr>
					<?php 
					$where=" WHERE 1";
					if(!empty($_POST['from_date']))
					{
						$from_date=date('Y-m-d',strtotime($_POST['from_date']));
						$where.=" AND s_Date>='$from_date'";
					}
					if(!empty($_POST['to_date']))
					{
						$to_date=date('Y-m-d',strtotime($_POST['to_date']));
						$where.=" AND s_Date<='$to_date'";
					}
					
					
					
					$query ="
SELECT cust_sale.s_Date,
IFNULL(sum(item_Qty*item_SalePrice),0) as total_sales,
       IFNULL(sum(item_Qty*item_CostPrice),0) as total_cost,
              IFNULL(sum(s_DiscountAmount),0) as total_discount,
                     IFNULL(sum(s_TaxAmount),0) as total_tax  
FROM cust_sale
INNER JOIN cust_sale_detail ON cust_sale.s_id=cust_sale_detail.s_id
$where AND branch_id=$branch_id
GROUP BY cust_sale.s_Date
ORDER BY `sd_Date`						
";
					//echo '<pre>'.$query.'</pre>';
					$results = mysqli_query($con,$query);
					if(mysqli_num_rows($results)<1)
					{
						echo "<tr><td colspan='6'>There is no record found</td></tr>";
						die();
					}
					$net_sales=0;
					$net_cost=0;
					$net_profit=0;
					$net_discount=0;
					$net_tax=0;
					$expens_row=0;
					$net_expens=0;
					
					$salereturn_row=0;
					$net_salereturn=0;
					while($row=mysqli_fetch_assoc($results))
					{
						$row_date=$row['s_Date'];
						$net_sales=$net_sales+$row['total_sales'];
						$net_cost=$net_cost+$row['total_cost'];
						$net_profit=$net_profit+($row['total_sales']-$row['total_cost']);
						$net_discount=$net_discount+$row['total_discount'];
						$net_tax=$net_tax+$row['total_tax'];
						
						$expens_row=get_ExpensesOfDate($row_date);
						$net_expens=$net_expens+$expens_row;
						
						$salereturn_row=get_SalesReturnOfDate($row_date);
						$net_salereturn=$net_salereturn+$salereturn_row;
					
					?>
					<tr style="text-align:right;">
						<td style="text-align:left;"><?= validate_date_display($row_date);?></td>
						<td><?= $currency_symbol.number_format($row['total_sales'],2);?></td>
						<td><?= $currency_symbol.number_format($salereturn_row,2);?></td>
                        <td><?= $currency_symbol.number_format($row['total_cost'],2);?></td>
                        <td><?= $currency_symbol.number_format($row['total_sales']-$row['total_cost'],2);?></td>
                        <td><?= $currency_symbol.number_format($row['total_discount'],2);?></td>
                        <td><?= $currency_symbol.number_format($row['total_tax'],2);?></td>
                        <td><?= $currency_symbol.number_format($expens_row,2);?></td>
					</tr>
						<?php	
					}
					?>
                    <tr style="background:#ddd; font-weight:bold;">
                    	<td style="text-align:left;"><strong>Total</strong></td>
						<td style="text-align:right;"><?= $currency_symbol.number_format($net_sales,2);?></td>
                        <td style="text-align:right;"><?= $currency_symbol.number_format($net_salereturn,2);?></td>
                        <td style="text-align:right;"><?= $currency_symbol.number_format($net_cost,2);?></td>
                        <td style="text-align:right;"><?= $currency_symbol.number_format($net_profit,2);?></td>
                        <td style="text-align:right;"><?= $currency_symbol.number_format($net_discount,2);?></td>
                        <td style="text-align:right;"><?= $currency_symbol.number_format($net_tax,2);?></td>
                        <td style="text-align:right;"><?= $currency_symbol.number_format($net_expens,2);?></td>
                    </tr>
		</table>
		</div>
	</div>
</body>
</html>