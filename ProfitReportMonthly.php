<?php
include('sessionCheck.php');
include('connection.php');
include('functions.php');
$branch_id=$_SESSION['branch_id'];
?>
<!DOCTYPE html>
<html>
<head>
<title>Monthly Profit Report </title>
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
						 <div style=" margin-left:0%; text-align:center;"><b style="font-size:20px;">Monthly Profit Report</b><br/></div>
                         
<?php
$query ="SELECT date_format(sd_Date,'%Y') as date_year, date_format(sd_Date,'%m-%Y') as month_year, sum(item_NetPrice) as total_sales, sum(item_Qty*item_CostPrice) as total_cost, sum(item_Qty) as total_qty
FROM cust_sale_detail
INNER JOIN cust_sale ON cust_sale.s_id=cust_sale_detail.s_id
WHERE cust_sale.branch_id=$branch_id
GROUP BY date_year, month_year
ORDER BY  date_year,month_year";
					//echo '<pre>'.$query.'</pre>';
					$results = mysqli_query($con,$query);
					if(mysqli_num_rows($results)<1)
					{
						echo "There is no record found";
						die();
					}
					$year1='my year';
					
					while($row=mysqli_fetch_assoc($results))
					{
						$total_expenses=0;
						$total_sales=$row['total_sales'];
						$total_cost=$row['total_cost'];
						$total_qty=$row['total_qty'];
						$total_profit=($total_sales-$total_cost);
						$total_profit_per_item=number_format($total_profit/$total_qty,2);
						$month_year=date('M-Y',strtotime('01-'.$row['month_year']));
						$month_year_for_expense=$row['month_year'];
						$year=$row['date_year'];
						
						//if($year!=$year1)
						//{		
							//echo '<br style="clear:both;">';	
						//}
$expense_Q="SELECT expense_amount, expense_month
FROM 
(
SELECT SUM(expense_amount) as expense_amount, date_format(expense_date,'%m-%Y') as expense_month
FROM adm_expenses
WHERE branch_id=$branch_id
GROUP BY expense_month
) as abc
WHERE expense_month='$month_year_for_expense'";
$total_expensesQ=mysqli_query($con, $expense_Q);
$expense_Row=mysqli_fetch_assoc($total_expensesQ);
$total_expenses=$expense_Row['expense_amount'];


$salereturn_Q="SELECT sum(sr_NetAmount) as sr_NetAmount
FROM `cust_salereturn`
WHERE s_id in (SELECT s_id from cust_sale WHERE date_format(s_Date,'%m-%Y')='$month_year_for_expense' and branch_id=$branch_id)";
$total_salereturnsQ=mysqli_query($con, $salereturn_Q);
$salereturn_Row=mysqli_fetch_assoc($total_salereturnsQ);
$total_salereturns=$salereturn_Row['sr_NetAmount'];

$total_profit=$total_profit-$total_salereturns;
						?>
                        <table class="table table-bordered table-condensed" style="width:20%; margin-left:10px; float:left;">
                        	<tr><th colspan="2" style="font-size:15px;"><strong><?=$month_year?></strong></th></tr>
                            <tr>
                            	<td>
									<strong>Total Sales:</strong> </td><td><?=$currency_symbol.$total_sales?></td>
                                </tr>
                                
                                <tr>
                            	<td>
									<strong>Total Sales Return:</strong> </td><td><?=$currency_symbol.number_format($total_salereturns,2)?></td>
                                </tr>
                                <tr>
                                <td>    
                                    <strong>Total Profit:</strong></td><td><?=$currency_symbol.number_format($total_profit,2)?></td>
                                 </tr>
                                <tr>
                                <td>   
                                    <strong>Total Items Sold:</strong></td><td><?=$total_qty?></td>
                                 </tr>
                                <tr>
                                <td>   
                                    <strong>Avg Profit Per Item:</strong></td><td><?=$currency_symbol.number_format($total_profit_per_item,2)?>
                                </td>
                            </tr>
                            <tr>
                            	<td>   
                                    <strong>Total Expenses:</strong></td><td><?=$currency_symbol.number_format($total_expenses,0)?>
                                </td>
                            </tr>
                            <tr>
                            	<td>   
                                    <strong>Profit After Expenses:</strong></td><td><?=$currency_symbol.($total_profit-$total_expenses)?>
                                </td>
                            </tr>
                        </table>
                        
                        <?php
						$year1=date('Y',strtotime('01-'.$row['month_year']));
					}

					?>	
					
		</div>
	</div>
</body>
</html>

<script type="text/javascript">
	 window.print();
	
	setTimeout(function(){
	window.close();
	}, 500);
</script>