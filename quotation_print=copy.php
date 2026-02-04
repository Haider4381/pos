<?php
include('sessionCheck.php'); 
include('connection.php');
$branch_id=$_SESSION['branch_id'];

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<title>Print Quotation Invoice</title>
<style>
body, *
{
	margin:0;
	padding:0;
	font-family:Arial, Helvetica, sans-serif;
}

</style>
</head>

<?php

$page_print_width='7.1in';
$print_header='yes';
$show_prebalance='yes';

if(isset($_GET['show_prebalance']))
{
	$show_prebalance=$_GET['show_prebalance'];
}

if(isset($_GET['print_size']))
{
	$print_size=$_GET['print_size'];
	if($print_size=='a4') { $page_print_width='8.5in';}
	if($print_size=='a4half') { $page_print_width='4.2in';}
	if($print_size=='a5') { $page_print_width='8.5in';}
	if($print_size=='thermal') { $page_print_width='3in';}

}


if(isset($_GET['print_header']))
{
	$print_header=$_GET['print_header'];
}



if(!isset($_GET['id']))
{
	echo "Invalid Request";
 	die();
}

$sr_id=$_GET['id'];
$sQ="SELECT
	S.sr_id,S.branch_id,S.sr_Number,S.sr_TotalAmount,S.sr_NetAmount, S.sr_CreatedOn,S.sr_totalitems,
	C.client_id,C.client_Name,C.client_Phone,
	SD.item_IMEI,SD.item_Qty, SD.item_SalePrice,SD.item_DiscountPrice,SD.item_NetPrice,SD.item_id,
	I.item_Name,I.item_Code,
	u_FullName as username

FROM adm_quotation AS S
INNER JOIN adm_quotation_detail AS SD ON SD.sr_id=S.sr_id
INNER JOIN adm_item AS I ON I.item_id=SD.item_id
INNER JOIN adm_client AS C ON C.client_id=S.client_id
LEFT JOIN u_user  ON u_user.u_id=S.u_id
WHERE S.sr_id=$sr_id AND S.branch_id=$branch_id
";
//echo '<pre>'.$sQ.'</pre>';
$sRes=mysqli_query($con,$sQ);
if(mysqli_num_rows($sRes)<1)
{
	echo "No Record Found";
 	die();
}
else
{
	while($r=mysqli_fetch_assoc($sRes)) { $sRow[]=$r;}
}
$branch_id_sale=$sRow[0]['branch_id']; 
?>

<?php
$client_id=$sRow[0]['client_id'];
 $balanceQ='SELECT(
 ifnull((SELECT sum(s_NetAmount) FROM cust_sale WHERE client_id='.$client_id.'),0)
 -
 ifnull((SELECT SUM(sr_NetAmount) FROM cust_salereturn WHERE client_id='.$client_id.'),0)
 -
 ifnull((SELECT SUM(sp_Amount) FROM adm_sale_payment WHERE client_id='.$client_id.' AND sp_Type="S"),0)
 +
 ifnull((SELECT SUM(sp_Amount) FROM adm_sale_payment WHERE client_id='.$client_id.' AND sp_Type="SR"),0)
 ) AS balance';
 $balanceRes=mysqli_query($con,$balanceQ);
 $balance=0;
if(mysqli_num_rows($balanceRes)>0) { $balanceRow=mysqli_fetch_assoc($balanceRes); $balance=$balanceRow['balance'];} 
?>
<body style="width:<?=$page_print_width?>; margin:0 auto;">
<table style="width:100%;" align="center" border="0">
	<tbody>
		<tr>
			<td style="font-size:11px; text-align:right; color: black;">Software Develope By 0300-7537538</td>
		</tr>
	</tbody>
</table>    


<?php

if($print_header=='yes')
{
	$branchQ="SELECT * FROM adm_branch WHERE branch_id=$branch_id_sale";
	$branchRow=mysqli_fetch_assoc(mysqli_query($con,$branchQ)); 
	//$logo_source='<img src="img/weblogo.png" height="70">';
	$logo_source='';
	$logo_url=$base_url_branchlogo.$branchRow['branch_Logo'];
	if(file_exists($logo_url))
	{
		$logo_source='<img src="'.$logo_url.'" height="70" style="width:100%;">';
	}

	?>
	<table style="width:100%;     font-size: 15px;" align="center" border="0" >
		<tr><td><?=$logo_source?></td></tr>
		<tr><td style="text-align:center;"><h1><?php echo $branchRow['branch_Name'];?></h1><?php echo $branchRow['branch_Address'];?><br /><?php echo $branchRow['branch_Phone1'];?></td></tr>
	</table>
	<hr />
	<table style="width:100%; font-size:11px;"  align="center" border="0">

	<tr><td colspan="2" style="text-align:center;font-size:30px;"><strong>Quotation Invoice</strong></td></tr>
	</table>
<?php
}else
{
	echo '<p style="padding-top:100px;">&nbsp;</p>';
}

?>


<table style="width:100%; font-size:11px;"  align="center" border="0">

	<tr>
		<td style="width:50%;"><?php echo date('M d, Y',strtotime($sRow[0]['sr_CreatedOn']));?></td>
		<td style=" width:50%;text-align:right;"><?php echo date('h:i A',strtotime($sRow[0]['sr_CreatedOn']));?></td>
	</tr>
    <tr>
    	<td>Quotaion#</td>
		<td style="text-align:right;"><?php echo $sRow[0]['sr_Number'];?></td>
	</tr>
	<tr>
		<td>Attension: &nbsp;&nbsp;<?php echo $sRow[0]['client_Name'];?> </td>
		<td style="text-align:right;">Phone#: <?php echo $sRow[0]['client_Phone'];?></td>
	</tr>
    <tr>
		<td colspan="2">&nbsp;</td>
	</tr>
    <tr>
		<td colspan="2"><strong></strong></td>
	</tr>
    
	
</table>

<table align="center" class="table table-bordered" style="width:100%;border:0px solid #666;border-collapse:collapse;font-size:12px;font-family:Verdana, Geneva, sans-serif;" cellpadding="2">
 <tr style="border-bottom:1px solid #666;">
 	<td style="width:40%;border:2px solid #666;"><strong>Item Name</strong></td>
 	<td style="text-align:center;width:20%; border:2px solid #666;"><strong>Qty</strong></td>
 	<td style="text-align:center;width:20%; border:2px solid #666;"><strong>Rate</strong></td>
 	<td style="text-align:center;width:20%; border:2px solid #666;"><strong>Discount</strong></td>
 	<td style="text-align:center;width:20%; border:2px solid #666;"><strong>Value</strong></td>
 </tr>
<!--</table>
<table align="center" style="width:8in;">-->


<?php
$total_items=0;
foreach($sRow as $key => $r)
{
?>
<tr style="border-bottom:0px solid #666;">
	<td style="border:1px solid #666; padding:2px; "><?php echo $r['item_Name'];?></td>
	<td style="border:1px solid #666; padding:2px; text-align: right;"><?php echo $r['item_Qty'];?></td>
	<td style="border:1px solid #666; padding:2px; text-align: right;"><?php echo $r['item_SalePrice'];?></td>
	<td style="border:1px solid #666; padding:2px; text-align: right;"><?php echo $r['item_DiscountPrice'];?></td>
	<td style="border:1px solid #666; padding:2px; text-align: right;"><?php echo $r['item_NetPrice'];?></td>
</tr>

<?php } 
?>
</table>
<br />
<table style="width:100%;border-collapse: collapse;border-top: 1px solid #000; font-size:14px;" align="center" border="0"> 
 <tr>
 <td style="width:60%;padding:3px; text-align:right;"><strong>Item Count: </strong></td>
 <td style="width:40%;border-bottom:1px solid #000;padding:3px;text-align:right;"><?php echo $sRow[0]['sr_totalitems'];?></td>
 </tr>
 
 <tr>
 <td style=" padding:3px; text-align:right;"><strong>Sub Total: </strong></td>
 <td style=" border-bottom:1px solid #000;padding:3px;text-align:right; ;"><?php echo $currency_symbol.number_format($sRow[0]['sr_TotalAmount'],2);?></td>
 </tr>
 

</table>
<br />

<table style="width:100%;font-size:13px;" align="center" border="0"> 


 <tr>
 <td style="width:25%;padding:3px;"><strong>Quotation By: </strong></td>
 <td style="width:75%;border-bottom:1px solid #000;padding:3px;">Admin</td>
 </tr>
 
 
</table>
<br />

<strong style="text-align:center;display: block;font-size: 13px;">Powered By: websofthouse.net</strong>



</body>
</html>
<script type="text/javascript">
 //window.print();
 //setTimeout(function(){window.close();}, 3000);
</script>