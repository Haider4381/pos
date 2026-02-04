<?php
include('sessionCheck.php');
include('connection.php');
include('functions.php');
$branch_id=$_SESSION['branch_id'];
?>
<!DOCTYPE html>
<html>
<head>
<title>Sale Report </title>
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
						 <div style=" margin-left:0%; text-align:center;"><b style="font-size:20px;">Sale Report</b><br/></div>
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
<?php if($_POST['orderby']=='item')
{
?>
		  			<tr style="background-color:#808080;color:#FFFFFF;">
		  				<th width="100px">S.Date</th>
						<th width="100px">Qty</th>
						<th width="100px">Total Amt</th>
						<th width="100px">Disc. Price</th>
						<th width="100px">Net Price </th>
					</tr>
					<?php 
					$where=" WHERE 1";
					if(!empty($_POST['from_date']))
					{
						$from_date=date('Y-m-d',strtotime($_POST['from_date']));
						$where.=" AND s.s_Date>='$from_date'";
					}
					if(!empty($_POST['to_date']))
					{
						$to_date=date('Y-m-d',strtotime($_POST['to_date']));
						$where.=" AND s.s_Date<='$to_date'";
					}
					if($_POST['item_id']!='ALL')
					{
						$item_id=$_POST['item_id'];
						$where.=" AND sd.item_id=$item_id";
					}
					if($_POST['wh_id']!='ALL')
					{
						$wh_id=$_POST['wh_id'];
						$where.=" AND s.wh_id=$wh_id";
					}

$query ="SELECT
	s.s_Date as sale_Date, sd.item_id, sum(sd.item_Qty) as item_Qty, sum(sd.item_Rate) as item_SalePrice, sum(sd.item_Qty*sd.item_Rate) as item_TotalAmt, sum(sd.item_DiscountPercentage) as item_DiscountPercentage, sum(sd.item_DiscountPrice) as item_DiscountPrice, sum(sd.item_NetPrice) as item_DiscountedAmt,
	i.item_Name, c.cmp_Name
FROM cust_sale as s
INNER JOIN cust_sale_detail as sd ON s.s_id=sd.s_id
INNER JOIN adm_item as i ON sd.item_id=i.item_id

LEFT OUTER JOIN adm_company as c ON i.cmp_id=c.cmp_id
						$where
						GROUP BY s.s_Date,sd.item_id
						ORDER BY c.cmp_Name ASC, i.item_Name ASC, s.s_Date ASC";
					//echo '<pre>'.$query.'</pre>';
					$results = mysqli_query($con,$query);
					if(mysqli_num_rows($results)<1)
					{
						echo "<tr><td colspan='7'>There is no record found</td></tr>";
						die();
					}
					$item='';
					$firstCount=1;
					$qty=$total=$discprice=$net=0;
					while($row=mysqli_fetch_assoc($results))
					{
						if($row['item_id']!=$item)
						{		
							if($firstCount!=1):
						?>
							<tr bgcolor='#f0fbff' style=" text-align:right;font-weight: bold;">
								<td></td>
								<td ><?php echo $qty; ?></td>
								<td ><?php echo $total; ?></td>
								<td ><?php echo $discprice; ?></td>
								<td ><?php echo $net; ?></td>
							<tr>
						<?php 
							endif;
							$firstCount=0;
							$qty=$total=$discprice=$net=0;
						}
						if($row['item_id']!=$item)
						{
							 $item=$row['item_id'];
						?>
							<tr>
								<td colspan='8' style="background:#CCC; font-weight:bold;">Item: &nbsp;&nbsp; <?=$row['item_Name'].' / '.$row['cmp_Name'];?></td>
							<tr>
						<?php 
						}

						?>
					<tr style="text-align:right;">
						<td style="text-align:left;"><?= date('d-m-Y',strtotime($row['sale_Date']));?></td>
						<td><?= number_format($row['item_Qty'],0);?></td>
						<td><?= number_format($row['item_TotalAmt'],0);?></td>
						
						<td><?= number_format($row['item_DiscountPrice'],0);?></td>
						<td><?= number_format($row['item_DiscountedAmt'],0);?></td>	
					</tr>
					<?php
					
					$qty+=$row['item_Qty'];
					$total+=$row['item_TotalAmt'];
					$discprice+=$row['item_DiscountPrice'];
					$net+=$row['item_DiscountedAmt'];
					}

					?>	
					<tr bgcolor='#f0fbff' style="font-weight: bold; text-align:right;">
						<td></td>
						<td ><?php echo $qty; ?></td>
						<td ><?php echo $total; ?></td>
						<td ><?php echo $discprice; ?></td>
						<td ><?php echo $net; ?></td>

					<tr>
<?php
}
elseif($_POST['orderby']=='date')
{
?>	
					<tr style="background-color:#808080;color:#FFFFFF;">
						<th style="width:15%;">Category</th> 
                        <th style="width:15%;">Sub Category</th> 
                        
                        <th style="width:20%;">Item Name</th>  
						<th style="width:20%;">Customer Name</th>
                        <th style="width:10%;">Qty</th>
						<th style="width:10%;">Rate</th>
						<th style="width:10%;">Value</th>
					</tr>
					<?php 
					$where=" WHERE 1";
					if(!empty($_POST['from_date']))
					{
						$from_date=date('Y-m-d',strtotime($_POST['from_date']));
						$where.=" AND s.s_Date>='$from_date'";
					}
					if(!empty($_POST['to_date']))
					{
						$to_date=date('Y-m-d',strtotime($_POST['to_date']));
						$where.=" AND s.s_Date<='$to_date'";
					}
					if($_POST['item_id']!='ALL')
					{
						$item_id=$_POST['item_id'];
						$where.=" AND sd.item_id=$item_id";
					}
					if($_POST['client_id']!='ALL')
					{
						$client_id=$_POST['client_id'];
						$where.=" AND s.client_id=$client_id";
					}
					
					
					
					$query ="SELECT
	s.s_Date as sale_Date, sd.item_id,  (sd.item_Qty) as item_Qty,  (sd.item_SalePrice) as item_SalePrice, item_NetPrice as item_TotalAmt,
	i.item_Name, icat_name, isubcat_name, client_Name
FROM cust_sale as s
INNER JOIN cust_sale_detail as sd ON s.s_id=sd.s_id
INNER JOIN adm_item as i ON sd.item_id=i.item_id
INNER JOIN adm_client on adm_client.client_id=s.client_id
LEFT OUTER JOIN adm_itemcategory as cat ON i.icat_id=cat.icat_id
LEFT OUTER JOIN adm_itemsubcategory as subcat ON i.isubcat_id=subcat.isubcat_id
						$where AND s.branch_id=$branch_id
						ORDER BY s.s_Date ASC, icat_name ASC,isubcat_name, i.item_Name ASC";
					//echo '<pre>'.$query.'</pre>';
					$results = mysqli_query($con,$query);
					if(mysqli_num_rows($results)<1)
					{
						echo "<tr><td colspan='7'>There is no record found</td></tr>";
						die();
					}
					$date='';
					$firstCount=1;
					$qty=$total=$discprice=$net=0;
					$total_qty=$total_value=0;
					while($row=mysqli_fetch_assoc($results))
					{
						$total_qty=$total_qty+$row['item_Qty'];
						$total_value=$total_value+$row['item_TotalAmt'];
						if($row['sale_Date']!=$date)
						{		
							if($firstCount!=1):
						?>
							<tr bgcolor='#f0fbff' style="font-weight: bold; text-align:right;">
								<td colspan="4">&nbsp;</td>
								<td ><?php echo $qty; ?></td>
                                <td></td>
								<td ><?php echo $currency_symbol.$total; ?></td>
							<tr>
						<?php 
							endif;
							$firstCount=0;
							$qty=$total=$discprice=$net=0;
						}
						if($row['sale_Date']!=$date)
						{
							$date=$row['sale_Date'];
						?>
							<tr>
								<td colspan='7' style="background:#CCC; font-weight:bold;">Date: &nbsp;&nbsp; <?=validate_date_display($row['sale_Date']);?></td>
							<tr>
						<?php 
						}
						?>
					<tr style="text-align:right;">
						<td style="text-align:left;"><?= $row['icat_name'];?></td>
						<td style="text-align:left;"><?= $row['isubcat_name'];?></td>
						<td style="text-align:left;"><?= $row['item_Name'];?></td>
                        <td style="text-align:left;"><?= $row['client_Name'];?></td>
						<td><?= number_format($row['item_Qty'],0);?></td>
						<td><?= number_format($row['item_SalePrice'],0);?></td>
						<td><?= $currency_symbol.number_format($row['item_TotalAmt'],0);?></td>
					</tr>
						<?php
					
					$qty+=$row['item_Qty'];
					$total+=$row['item_TotalAmt'];
					}

					?>	
					<tr bgcolor='#f0fbff' style="font-weight: bold; text-align:right;">
						<td colspan="4"></td>
						<td ><?php echo $qty; ?></td>
                        <td></td>
						<td ><?php echo $currency_symbol.$total; ?></td>
					<tr>
                    <tr style="background-color:burlywood;color:#000; font-size:18px; text-align:right; font-weight:bold;">
						<td colspan="4">Total</td> 
                        <td><?=$total_qty?></td> 
                        <td></td> 
						<td><?=$currency_symbol.$total_value?></td> 
						
					</tr>
                    

<?php 
}
elseif($_POST['orderby']=='salenumber')
{
		$where=" WHERE 1";
		if(!empty($_POST['from_date']))
		{
			$from_date=date('Y-m-d',strtotime($_POST['from_date']));
			$where.=" AND s.s_Date>='$from_date'";
		}
		if(!empty($_POST['to_date']))
		{
			$to_date=date('Y-m-d',strtotime($_POST['to_date']));
			$where.=" AND s.s_Date<='$to_date'";
		}
		/*if($_POST['item_id']!='ALL')
		{
			$item_id=$_POST['item_id'];
			$where.=" AND SD.item_id=$item_id";
		}*/
	$query="SELECT S.s_id, S.s_Number, chart.chart_Name as s_CustomerName, S.s_Date, S.s_CreatedOn as s_DateTime, S.s_TotalAmount as s_TotalAmt, S.s_DiscountPrice, '0' as s_DsicountPercentage, '0' as s_SpecialDiscount, s.s_NetAmount as s_NetAmt,
SD.item_id, SD.item_Qty, SD.item_Cost as item_CostPrice, SD.item_Rate as item_SalePrice, (SD.item_Qty*SD.item_Rate) as item_TotalAmt, SD.item_DiscountPercentage, SD.item_DiscountPrice, SD.item_NetPrice as item_DiscountedAmt,
I.item_Name,
U.unit_Name,
C.cmp_Name
	 FROM cust_sale AS S
	 LEFT JOIN cust_sale_detail AS SD ON SD.s_id=S.s_id
     INNER JOIN adm_chart as chart ON S.client_id=chart.chart_id
	 INNER JOIN adm_item AS I ON I.item_id=SD.item_id
	 LEFT JOIN adm_unit AS U ON U.unit_id=I.item_id
     LEFT OUTER JOIN adm_company as C ON c.cmp_id=I.cmp_id
	  $where
	 ORDER BY S.s_id, C.cmp_Name, I.item_Name
	 ";
	 //echo '<pre>'.$query.'</pre>';
	 $res=mysqli_query($con,$query);
	if(mysqli_num_rows($res)<1)
	{
		echo "<tr><td colspan='7'>There is no record found</td></tr>";
		die();
	}
?>
			<tr style="background-color:#808080;color:#FFFFFF;">
  				<!-- <td width="100px">S.Date</td> -->
				<td width="100px">Brand</td>
				<td width="150px">Item</td> 
				<td width="80px">Qty</td>
				<td width="100px">Sale Price</td>
				<td width="100px">Total Amt</td>
				<td width="100px">Disc. %</td>
				<td width="100px">Disc. Price</td>
				<td width="100px">Net Price </td>
			</tr>
<?php 
$s_id=0;
$firstCount=1;
	while($row=mysqli_fetch_assoc($res))
	{
		if($s_id!=$row['s_id'])
		{
			$s_id=$row['s_id'];
			?>
			<tr style="background-color:#CCCCCC;">
				 
				<td colspan="8">
					<table  border="0" bordercolor="#E6E6E6" align="center" style="font-size:14px;width: 100%;text-align:left;" >
						<tr>
                        	<td style="width:200px;">Party: <b><?php echo $row['s_CustomerName']; ?></b> &nbsp&nbsp&nbsp&nbsp&nbsp</td>
							<td style="width:100px;">Date:<b><?php echo date('d-m-Y',strtotime($row['s_Date'])); ?></b></td>
							<td style="width:100px;"> Sale#: <b><?php echo $row['s_Number']; ?></b></td>
							<td style="width:100px;" >Total: <b><?php echo $row['s_TotalAmt']; ?></b></td>
							<td style="width:100px;" >Dsicount: <b><?php echo $row['s_DiscountPrice']; ?></b></td>
							<td style="width:100px;">Net Amount: <b><?php echo $row['s_NetAmt']; ?></b></td>
						</tr>
					</table>
				</td>
			</tr>
			
			<?php
		}
		?>
			

		<tr style="text-align:right;">
			<td style="text-align:left;"><?php echo $row['cmp_Name']; ?></td>
			<td style="text-align:left;"><?php echo $row['item_Name']; ?></td> 
			<td><?php echo number_format($row['item_Qty'],0); ?></td>
			<td><?php echo number_format($row['item_SalePrice'],0); ?></td>
			<td><?php echo number_format($row['item_TotalAmt'],0); ?></td>
			<td><?php echo number_format($row['item_DiscountPercentage'],0); ?></td>
			<td><?php echo number_format($row['item_DiscountPrice'],0); ?></td>
			<td><?php echo number_format($row['item_DiscountedAmt'],0); ?> </td>
		</tr>

	<?php
	}
}
	?>			
		</table>
		</div>
	</div>
</body>
</html>