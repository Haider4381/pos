<?php include('connection.php');
include('sessionCheck.php'); 
$branch_id=$_SESSION['branch_id'];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html;
 charset=utf-8" />
<title>Print Repair Receipt</title>
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

if(!isset($_GET['r_id']))
{
	echo "Invalid Request";
 	die();
}

$r_id=$_GET['r_id'];
$sQ="SELECT
	R.rep_id, R.rep_Number,  R.rep_CreatedAt,R.rep_Notes, C.client_Name,C.client_Phone, R.item_Code, R.item_id as item_Name, R.rep_AmountCheck, R.rep_AmountRepair, R.rep_AmountBalance, R.rep_Date, R.rep_DateDelivery,
	type.rtype_Name,
    rstatus.rstatus_Name,
	u_FullName as username,
	adm_branch.branch_CustomerPaymentPolicy, adm_branch.branch_ShowPolicy

FROM rep_repairs AS R
LEFT JOIN adm_client AS C ON C.client_id=R.client_id
INNER JOIN adm_branch ON adm_branch.branch_id=R.branch_id
LEFT OUTER JOIN rep_type as type ON type.rtype_id=R.rtype_id
LEFT OUTER JOIN rep_status as rstatus ON rstatus.rstatus_id=R.rstatus_id
LEFT JOIN u_user  ON u_user.u_id=R.u_id
WHERE R.rep_id=$r_id and R.branch_id=$branch_id";
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
$balance=0;

?>
<body style="width:3.1in; margin:0 auto;">

<?php
$branchQ="SELECT * FROM adm_branch WHERE branch_id=$branch_id";
$branchRow=mysqli_fetch_assoc(mysqli_query($con,$branchQ)); 
//$logo_source='<img src="img/weblogo.png" height="70">';
$logo_source='';
$logo_url=$base_url_branchlogo.$branchRow['branch_Logo'];
if(file_exists($logo_url))
{
	$logo_source='<img src="'.$logo_url.'" height="70" style="width:100%;">';
}

?>
<table style="width:100%;" align="center" border="0">
	<tr><td><?=$logo_source?></td></tr>
	<tr><td style="text-align:center;"><h3><?php echo $branchRow['branch_Name'];?></h3><?php echo $branchRow['branch_Address'];?><br /><?php echo $branchRow['branch_Phone1'];?></td></tr>
</table>
<hr />
<table style="width:100%; font-size:11px;"  align="center" border="0">
<tr>

		<td style="width:50%;">Store ID</td>
        <td style="width:50%; text-align:right;"><?php echo $branchRow['branch_Code'];?></td>
	</tr>
<tr><td colspan="2" style="text-align:center;font-size:30px;"><strong>Repair Receipt</strong></td></tr>


</table>
<table style="width:100%; font-size:11px;"  align="center" border="0">

	<tr>
		<td style="width:50%;"><?php echo date('M d, Y',strtotime($sRow[0]['rep_CreatedAt']));?></td>
		<td style=" width:50%;text-align:right;"><?php echo date('h:i A',strtotime($sRow[0]['rep_CreatedAt']));?></td>
	</tr>
    <tr>
    	<td>Transaction#</td>
		<td style="text-align:right;"><?php echo $sRow[0]['rep_Number'];?></td>
	</tr>
    <tr>
		<td colspan="2">&nbsp;</td>
	</tr>
    <tr>
		<td colspan="2"><strong>Customer: &nbsp;&nbsp;<?php echo $sRow[0]['client_Name'];?></strong></td>
	</tr>
    <tr>
		<td colspan="2"><strong>Phone#: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $sRow[0]['client_Phone'];?></strong></td>
	</tr>
	
</table>
<br />
<table align="center" style="width:100%;border:0px solid #666;border-collapse:collapse;font-size:11px;font-family:Verdana, Geneva, sans-serif;" cellpadding="2">
 <tr style="border-bottom:1px solid #666;">
 <td style="text-align:left;width:60%;border-bottom:1px solid #666;"><strong>Item Detail</strong></td>	</tr>
<!--</table>
<table align="center" style="width:8in;">-->


<?php
$total_items=0;
foreach($sRow as $key => $r)
{
?>
<tr style="border-bottom:0px solid #666;">
	<td style="border:0px solid #666;">
 	<?php echo $r['item_Name'];?>
    <?php if(!empty($r['item_Code'])) {echo '<br>'.$r['item_Code'];} ?>
    <?php if(!empty($r['rep_Notes'])) {echo '<br>'.$r['rep_Notes'];} ?>
	</td>
</tr>

<?php } 
?>
</table>
<br />


<table style="width:3.125in;border-collapse: collapse;border-top: 1px solid #000; font-size:14px;" align="center" border="0"> 
 <tr>
 <td style="width:60%;padding:3px; text-align:right;"><strong>Repair Type: </strong></td>
 <td style="width:40%;border-bottom:1px solid #000;padding:3px;text-align:right;"><?php echo $r['rtype_Name'];?></td>
 </tr>
 
 <tr>
 <td style=" padding:3px; text-align:right;"><strong>Delivery Date: </strong></td>
 <td style=" border-bottom:1px solid #000;padding:3px;text-align:right;"><?php echo date("d-m-Y", strtotime($r['rep_DateDelivery']));?></td>
 </tr>
 
 <tr>
 <td style=" padding:3px; text-align:right;"><strong>Diagnosis Fee: </strong></td>
 <td style=" border-bottom:1px solid #000;padding:3px;text-align:right;"><?php echo $currency_symbol.number_format($sRow[0]['rep_AmountCheck'],2);?></td>
 </tr>
 
 <tr>
 <td style=" padding:3px; text-align:right;"><strong>Repair Fee: </strong></td>
 <td style=" border-bottom:1px solid #000;padding:3px;text-align:right;"><?php echo $currency_symbol.number_format($sRow[0]['rep_AmountRepair'],2);?></td>
 </tr>
 
<tr>
 <td style=" padding:3px; text-align:right;"><strong>Amount Due: </strong></td>
 <td style=" border-bottom:1px solid #000;padding:3px;text-align:right;"><?php echo $currency_symbol.number_format($sRow[0]['rep_AmountBalance'],2);?></td>
 </tr>
</table>
<br />

<table style="width:3.125in;font-size:13px;" align="center" border="0"> 
 <tr>
 <td style="width:30%;padding:3px;"><strong>Prepare By: </strong></td>
 <td style="width:70%;border-bottom:1px solid #000;padding:3px;"><?=$sRow[0]['username']?></td>
 </tr>
</table>
<br />
<?php
if($sRow[0]['branch_ShowPolicy']==1)
{
?>



<table style="width:100%;" align="center">
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
</table>
<?php } ?> 
<br />
<strong style="text-align:center;display: block;font-size: 13px;">Powered By: eposdaddy.com</strong>



</body>
</html>
<script type="text/javascript">
 //window.print();
 //setTimeout(function(){window.close();}, 300);

</script>