<?php
include('sessionCheck.php');
include('connection.php');
include('functions.php');
$branch_id=$_SESSION['branch_id'];
?>
<!DOCTYPE html>
<html>
<head>
<title>Stock Report With Value </title>
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
						 <div style=" margin-left:0%; text-align:center;"><b style="font-size:20px;">Stock Report With Value</b><br/></div>

<table border="0" bordercolor="#E6E6E6" align="center" style="font-size:15px; width:90%;" class="table table-bordered table-condensed table-hovered">				    
	
					<tr style="background-color:#808080;color:#FFFFFF;">
                        <th style="text-align:center;">Sr.</th>
                        <th style="text-align:center;">Product Code</th>
                        <th style="text-align:center;">Product Name</th>
						<th style="text-align:center;">Main Category</th>
                        <th style="text-align:center;">Sub Category</th>
                        <th style="text-align:center;">Item Stock</th>
                        <th style="text-align:center;">Purchase Price</th>
                        <th style="text-align:center;">Purchase Value</th>
						<th style="text-align:center;">Sale Price</th>
						<th style="text-align:center;">Sale Value</th>
					</tr>
					<?php
$query="SELECT I.item_id,I.item_Code,I.item_Name,I.brand_id,I.item_Status,I.item_Remarks,I.item_SalePrice,I.item_PurchasePrice,I.item_InvoicePrice,I.item_Percentage,I.item_Scheme, I.item_Image,
			B.brand_Name,
			IC.icat_name,
			ISC.isubcat_name
			
		FROM adm_item AS I
		LEFT JOIN adm_brand AS  B ON B.brand_id=I.brand_id
		LEFT JOIN adm_itemcategory AS  IC ON IC.icat_id=I.icat_id
		LEFT JOIN adm_itemsubcategory AS  ISC ON ISC.isubcat_id=I.isubcat_id
		WHERE 1 AND I.branch_id=$branch_id AND item_other=0";

				// 	echo '<pre>'.$query.'</pre>';
					$results = mysqli_query($con,$query);
					if(mysqli_num_rows($results)<1)
					{
						echo "<tr><td colspan='6'>There is no record found</td></tr>";
						die();
					}
					
					$net_sales=0;
					$net_sales=0;
					$net_stock=0;
					
                    $serial=0;
					while($row=mysqli_fetch_assoc($results))
					{
					    $item_id=$row['item_id'];
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
                        $item_stock=$stock_row['item_stock'];
					    
					    
					    $serial++;
						$net_cost=$net_cost+($row['item_PurchasePrice']*$item_stock);
						$net_sales=$net_sales+($row['item_SalePrice']*$item_stock);
						$net_stock=$net_stock+$item_stock;
					
					?>
					<tr>
						<td style="text-align:center;"><?=$serial;?></td>
						<td><?=$row['item_Code'];?></td>
						<td><?=$row['item_Name'];?></td>
						<td><?=$row['icat_name'];?></td>
						<td><?=$row['isubcat_name'];?></td>
						<td style="text-align:center;"><?= number_format($item_stock,2);?></td>
                        <td style="text-align:right; background-color: #fcf2d2;"><?= number_format($row['item_PurchasePrice'],0);?></td>
                        <td style="text-align:right; background-color: #fcf2d2;"><?= number_format($row['item_PurchasePrice']*$item_stock,0);?></td>
                        <td style="text-align:right; background-color: #e3fcd9;"><?= number_format($row['item_SalePrice'],0);?></td>
                        <td style="text-align:right; background-color: #e3fcd9;"><?= number_format($row['item_SalePrice']*$item_stock,0);?></td>
					</tr>
						<?php	
					}
					?>
                    <tr style="background:#ddd; font-weight:bold;">
                    	<td style="text-align:left;" colspan="5"><strong>Total</strong></td>
						<td style="text-align:center;"><?= number_format($net_stock,0);?></td>
						<td style="text-align:right; background-color: #fcf2d2;" colspan="2"><?= number_format($net_cost,0);?></td>
                        <td style="text-align:right; background-color: #e3fcd9;" colspan="2"><?= number_format($net_sales,0);?></td>
                    </tr>
		</table>
		</div>
	</div>
</body>
</html>