<?php
include('sessionCheck.php'); 
include('connection.php');
$branch_id=$_SESSION['branch_id'];

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<title>Print Sale Invoice</title>
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



if(!isset($_GET['s_id']))
{
	echo "Invalid Request";
 	die();
}

$s_id=$_GET['s_id'];
$sQ="SELECT
	S.s_id,S.branch_id,S.s_Number,S.s_TotalAmount,S.s_NetAmount,S.s_Tax, S.s_TaxAmount, S.s_SaleMode, S.s_PaymentType, S.s_Discount, S.s_DiscountAmount,
	S.s_SaleMode, S.s_PaidAmount, S.s_CreatedOn,S.s_totalitems,
	C.client_id,C.client_Name,C.client_Phone,
	SD.item_IMEI,SD.item_Qty, SD.item_SalePrice,SD.item_NetPrice,SD.item_id,
	I.item_Name,I.item_Code,
	u_FullName as username,
	adm_branch.branch_CustomerPaymentPolicy, adm_branch.branch_ShowPolicy

FROM cust_sale AS S
INNER JOIN cust_sale_detail AS SD ON SD.s_id=S.s_id
INNER JOIN adm_item AS I ON I.item_id=SD.item_id
INNER JOIN adm_client AS C ON C.client_id=S.client_id
LEFT JOIN u_user  ON u_user.u_id=S.u_id
INNER JOIN adm_branch ON adm_branch.branch_id=S.branch_id
WHERE S.s_id=$s_id AND S.branch_id=$branch_id
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

	<tr><td colspan="2" style="text-align:center;font-size:30px;"><strong>Sale Receipt</strong></td></tr>
	</table>
<?php
}else
{
	echo '<p style="padding-top:100px;">&nbsp;</p>';
}

?>


<table style="width:100%; font-size:11px;"  align="center" border="0">

	<tr>
		<td style="width:50%;"><?php echo date('M d, Y',strtotime($sRow[0]['s_CreatedOn']));?></td>
		<td style=" width:50%;text-align:right;"><?php echo date('h:i A',strtotime($sRow[0]['s_CreatedOn']));?></td>
	</tr>
    <tr>
    	<td>Transaction#</td>
		<td style="text-align:right;"><?php echo $sRow[0]['s_Number'];?></td>
	</tr>
    <tr>
    	<td>Sale Mode</td>
		<td style="text-align:right;"><?php echo $sRow[0]['s_SaleMode'];?></td>
	</tr>
    <tr>
    	<td>Payment Type</td>
		<td style="text-align:right;"><?php echo $sRow[0]['s_PaymentType'];?></td>
	</tr>
	<tr>
		<td>Customer Name: &nbsp;&nbsp;<?php echo $sRow[0]['client_Name'];?> </td>
		<td style="text-align:right;">Phone#: <?php echo $sRow[0]['client_Phone'];?></td>
	</tr>
    <tr>
		<td colspan="2">&nbsp;</td>
	</tr>
    <tr>
		<td colspan="2"><strong></strong></td>
	</tr>
    
	
</table>

<table align="center" style="width:100%;border:0px solid #666;border-collapse:collapse;font-size:11px;font-family:Verdana, Geneva, sans-serif;" cellpadding="2">
 <tr style="border-bottom:1px solid #666;">
 <td style="text-align:left;width:30%; border-bottom:1px solid #666;"><strong>Qty</strong></td>
 <td style="text-align:center;width:70%;border-bottom:1px solid #666;"><strong>Item Name</strong></td>
	</tr>
<!--</table>
<table align="center" style="width:8in;">-->


<?php
$total_items=0;
foreach($sRow as $key => $r)
{
?>
<tr style="border-bottom:0px solid #666;">
	<td style="border:0px solid #666; display:block;  text-align: left;"><?php echo $r['item_Qty'];?></td>
	<td style="border:0px solid #666; width:50%; text-align: center;">
 	<?php echo $r['item_Name'];?>
    <?php if(!empty($r['item_IMEI']) && $r['item_IMEI']!==$r['item_Name'] ) {echo '<br>'.$r['item_IMEI'];} ?>
    <?php if(!empty($r['item_Code'])) {echo '<br>'.$r['item_Code'];} ?>
	</td>
</tr>

<?php } 
?>
</table>
<br />
<table style="width:100%;border-collapse: collapse;border-top: 1px solid #000; font-size:14px;" align="center" border="0"> 
 <tr>
 <td style="width:60%;padding:3px; text-align:right;"><strong>Item Count: </strong></td>
 <td style="width:40%;border-bottom:1px solid #000;padding:3px;text-align:right;"><?php echo $sRow[0]['s_totalitems'];?></td>
 </tr>
 
 <tr style="display: none;">
 <td style=" padding:3px; text-align:right;"><strong>Sub Total: </strong></td>
 <td style=" border-bottom:1px solid #000;padding:3px;text-align:right; ;"><?php echo $currency_symbol.number_format($sRow[0]['s_TotalAmount'],2);?></td>
 </tr>
 
 <tr style="display: none;">
 <td style=" padding:3px; text-align:right;"><strong>Discount: </strong></td>
 <td style=" border-bottom:1px solid #000;padding:3px;text-align:right;"><?php echo $currency_symbol.number_format($sRow[0]['s_DiscountAmount'],2);?></td>
 </tr>
 
 <tr style="display: none;">
 <td style=" padding:3px; text-align:right;"><strong>Total VAT: </strong></td>
 <td style=" border-bottom:1px solid #000;padding:3px;text-align:right;"><?php echo $currency_symbol.number_format($sRow[0]['s_TaxAmount'],2);?></td>
 </tr>
 
<tr style="display: none;">
 <td style=" padding:3px; text-align:right;"><strong>Amount Due: </strong></td>
 <td style=" border-bottom:1px solid #000;padding:3px;text-align:right;"><?php echo $currency_symbol.number_format($sRow[0]['s_NetAmount'],2);?></td>
 </tr>
<?php if($sRow[0]['s_SaleMode']=='cash' && $sRow[0]['s_PaidAmount']>=$sRow[0]['s_NetAmount']){ ?>
	
    <tr style="display: none;">
         <td style=" padding:3px; text-align:right;"><strong>Cash: </strong></td>
         <td style=" border-bottom:1px solid #000;padding:3px;text-align:right;"><?php echo $currency_symbol.number_format($sRow[0]['s_PaidAmount'],2);?></td>
         </tr>
         
         <tr style="display: none;">
         <td style=" padding:3px; text-align:right;"><strong>Change: </strong></td>
         <td style=" border-bottom:1px solid #000;padding:3px;text-align:right;"><?php echo $currency_symbol.number_format($sRow[0]['s_PaidAmount']-$sRow[0]['s_NetAmount'],2);?></td>
         </tr>

<?php } ?>

<?php if($sRow[0]['s_SaleMode']=='cash' && $sRow[0]['s_PaidAmount']<$sRow[0]['s_NetAmount']){ ?>
	
    <tr style="display: none;">
         <td style=" padding:3px; text-align:right;"><strong>Cash: </strong></td>
         <td style=" border-bottom:1px solid #000;padding:3px;text-align:right;"><?php echo $currency_symbol.number_format($sRow[0]['s_PaidAmount'],2);?></td>
         </tr>
         
         <tr style="display: none;">
         <td style=" padding:3px; text-align:right;"><strong>Remaining: </strong></td>
         <td style=" border-bottom:1px solid #000;padding:3px;text-align:right;"><?php echo $currency_symbol.number_format($sRow[0]['s_NetAmount']-$sRow[0]['s_PaidAmount'],2);?></td>
         </tr>

<?php } ?>


</table>
<br />

<table style="width:100%;font-size:13px;" align="center" border="0"> 


 <tr>
 <td style="width:25%;padding:3px;"><strong>Sales By: </strong></td>
 <td style="width:75%;border-bottom:1px solid #000;padding:3px;">Admin</td>
 </tr>
 
 
</table>
<br />
<?php
if($sRow[0]['branch_ShowPolicy']==1)
{
?>



<!--<table style="width:100%;" align="center">
 <tr>
 	<td style="line-height:8px;">
    	<h2 style="background: black;
    width: 100%;
    height: 35px;
    color: white;
    text-align: center;
    padding: 0px;
    line-height: 34px;">Sale Policy:</h2><br /><br />
    	<span style="word-break: break-all; line-height: 15px;"><?=$sRow[0]['branch_CustomerPaymentPolicy'];?></span>
		</td>
 </tr>
</table>-->
<?php } ?> 

<strong style="text-align:center;display: block;font-size: 13px;">Powered By: websofthouse.net</strong>



</body>
</html>
<script type="text/javascript">
 //window.print();
 //setTimeout(function(){window.close();}, 3000);
</script>