<?php
include('sessionCheck.php');
include('connection.php');
include('functions.php');
$branch_id=$_SESSION['branch_id'];

if(!isset($_POST['sup_id']) || empty($_POST['sup_id']))
{
    echo "Invalid Request";
    die();
}
else
{
	$from_date=$to_date='';
	$balance=0;
	$openingbalance=0;
    $sup_id='';
    if(!empty($_POST['from_date']))
    {
    	$from_date_post=date('Y-m-d',strtotime($_POST['from_date']));
    	$from_date=" AND p_Date>='".$from_date_post."'";
    }
    if(!empty($_POST['to_date']))
    {
    	$to_date_post=date('Y-m-d',strtotime($_POST["to_date"]));
    	$to_date=" AND p_Date<='".$to_date_post."'";
    }
	if(!empty($_POST['sup_id']))
    {
    	$sup_id_post=$_POST['sup_id'];
    	$sup_id=" AND sup_id='".$sup_id_post."'";
    }

	$client_Name="SELECT * FROM `adm_supplier` WHERE sup_id=$sup_id_post";
	$client_Res=mysqli_fetch_assoc(mysqli_query($con,$client_Name));
	$client_Name=$client_Res['sup_Name'];
	//$openingbalance=$openingbalance+($client_Res['chart_openingpayment']-$client_Res['chart_openingreceive']);
	if(!empty($from_date))
	{
		$balanceQ="
					SELECT ifnull(SUM(p_NetAmount-p_PaidAmount),0) as opening
					FROM
					(
						SELECT p.p_id, p_Date, p.p_Number, p.p_NetAmount, 
							ifnull((SELECT SUM(pp_Amount) FROM adm_purchase_payment WHERE adm_purchase_payment.p_id=p.p_id and sup_id=$sup_id_post AND pp_Type='P'),0) as p_PaidAmount
						FROM adm_purchase as p
						WHERE p.sup_id=$sup_id_post AND p.p_Date<'$from_date_post'
					) as abc";
		//echo '<pre>'.$balanceQ.'</pre>';
		$balanceRes=mysqli_query($con,$balanceQ);
		$balanceRes=mysqli_fetch_assoc($balanceRes);
		$openingbalance=$openingbalance+$balanceRes['opening'];
	}
?>
<!DOCTYPE html>
<html><head><meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
<title>Vendor Ledger</title>
<link href="css/bootstrap.min.css" type="text/css" rel="stylesheet">

<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
<script src="js/jquery.table2excel.js"></script>

<style>
* { font-family:"Trebuchet MS", Arial, Helvetica, sans-serif;}
</style>

    </head>
    <body>
        <div id="main_container">
            <div id="main_centerBody">

<?php
$branchQ="SELECT * FROM adm_branch WHERE branch_id=$branch_id";
$branchRow=mysqli_fetch_assoc(mysqli_query($con,$branchQ)); 
//$logo_source='<img src="img/weblogo.png" height="70">';
?>
<table style="width:100%;" align="center" border="0">
	
	<tr><td style="text-align:center;"><h2><?php echo $branchRow['branch_Name'];?></h2><?php echo $branchRow['branch_Address'];?><br /><?php echo $branchRow['branch_Phone1'];?><br/><h1>Vendor Ledger</strong></td></h1>
</table>
<br>
<div style="width:80%; text-align:right; margin:0 auto;"><span class="btn btn-primary" onClick="callExcelButton();">Export to XLS</span></div>
                
                <br>
                <table style="width:80%; margin:0 auto;" class="table table-bordered table-condensed table-hover table-stripped table2excel" data-tableName="Test Table 2">
                    
                    <tr>
                            <?php
                            $from_Date= ($from_date_post);
                            $to_Date= ($to_date_post);
                              ?>
                            <td><b>Date From:</b></td>
                            <td colspan="5"><?php echo validate_date_display($from_Date); ?>
                            </td>
                            </tr>
                            <tr><td><b>Date To:</b></td><td colspan="5"><?php echo validate_date_display($to_Date); ?></td>
                        </tr>
                        <tr>
                            <td><b>Vendor Name:</b></td><td colspan="5"><?php echo $client_Name; ?></td>
                        </tr>
                    
                    <tr style="background:#009DFF; font-size:16px;">
                    	<td colspan="5">Opening Balance</td>
                        <td style="text-align:right"><?=$openingbalance?></td>
                    </tr>
                    <tr><td colspan="6">&nbsp;</td></tr>
                    <tr  style="background:#d65252; font-size:16px; text-align:center;">
                        <th width="17%" style="color:#fff; text-align:center;">Date</th>
                        <th width="8%" style="color:#fff; text-align:center;">Bill No.</th>
                        <th width="25%" style="color:#fff; text-align:center;">Description</th>
                        <th width="12%" style="color:#fff; text-align:center;">Bill Amount</th>
                        <th width="15%" style="color:#fff; text-align:center;">Paid to Vendor</th>
                        <th width="20%" style="color:#fff; text-align:center;">Remaining Amount</th>
                    </tr>
                    
                    <?php
	                $pQ="SELECT p_id, p_Date, p_Number, sup_id, p_NetAmount as dr, p_CreatedOn,p_Remarks  FROM `adm_purchase` WHERE 1 AND branch_id=$branch_id $from_date $to_date $sup_id ORDER BY p_CreatedOn ASC";
					//echo '<pre>'.$pQ.'</pre>';
                    $pQRes=mysqli_query($con,$pQ);
                  
                    if(mysqli_num_rows($pQRes)<1)
                    {
                    ?>
                        <tr>
                            <td colspan="6" style="text-align:center">No Record Found</td>
                        </tr>
                    <?php
                    }
                    else
                    {
                        $count=$currentDate=$bgColor=0;
						$rowEndBalance=0;
						$endBalance=$openingbalance;
						$total_dr=0;
						$total_cr=0;
						$total_type_name='';
                        while($clRow=mysqli_fetch_assoc($pQRes))
                        {							
							$p_id=$clRow['p_id'];
							$p_Number=$clRow['p_Number'];
							$rowEndBalance = $rowEndBalance + $clRow['dr'];
                            $endBalance=$endBalance+$clRow['dr'];
							$count++;
                              ?>
                              	<tr><td colspan="6">&nbsp;</td></tr>
                                <tr style="font-size:16px; background:aliceblue;">
                                     <td style="text-align:center;"><?php echo date("d-m-Y h:i s", strtotime($clRow['p_CreatedOn'])); ?></td>
                                    <td style="text-align:center;"><?php echo  $p_Number; ?></td>
                                    <td style="font-size:12px;"><?php echo  ($clRow['p_Remarks']); ?></td>
                                    <td style="text-align: right;"><?php echo  $clRow['dr']; ?></td>
                                    <td style="text-align: right;"><?php echo  '0.00'; ?></td>
                                    <td style="text-align: right;font-weight:bold;"><?php echo $currency_symbol.number_format($rowEndBalance,2); ?></td>
                                </tr>
                             <?php
							 $payQ="select * from adm_purchase_payment where 1 and p_id=$p_id and branch_id=$branch_id $sup_id";
							 $payQR=mysqli_query($con,$payQ);
							 $payRows=mysqli_num_rows($payQR);
							 if($payRows>0)
							 {
								 while($prows=mysqli_fetch_assoc($payQR))
								 {
									$rowEndBalance = $rowEndBalance - $prows['pp_Amount'];
									$endBalance=$endBalance-$prows['pp_Amount']
									?>
									<tr style="font-size:16px;">
										<td style="text-align:center;"><?php echo date("d-m-Y h:i s", strtotime($prows['pp_CreatedOn'])); ?></td>
										<td style="text-align:center;"><?php echo  $p_Number; ?></td>
										<td style="font-size:12px;"><?php echo  $prows['pp_Description']; ?></td>
										<td style="text-align: right;"><?php echo  '0.00'; ?></td>
										<td style="text-align: right;"><?php echo  $prows['pp_Amount']; ?></td>
										<td style="text-align: right; font-weight:bold;"><?php echo $currency_symbol.number_format($rowEndBalance,2); ?></td>
									</tr>
										
									<?php
								 }
							 }
							 $rowEndBalance=0;
                         }
						 ?>
						 
                         		
                                
                                <tr><td colspan="6">&nbsp;</td></tr>
                                <tr style="font-size:20px;">
                                    <th colspan="5" style="text-align:right;">Closing Balance</th>
                                    <th style="text-align:right;"><?php echo  $currency_symbol.number_format($endBalance,2); ?></th>
                                </tr>
                         
                         
						 
					<?php	 
                    }
                    ?>

              </table>
<button class="exportToExcel btn btn-primary" style="display:none;" id="exportToExcel">Export to XLS</button>
            </div>
        </div>
     </body>
    </html>
    


<?php 
}

function return_type_name($date)
{
	if($date=='purchase') { $return_data='Purchase';}
	else if($date=='purchasepayment') { $return_data='Purchase Payment';}
	else {$return_data='';}
	return $return_data;
}

?>

		<script>
 
function callExcelButton()
{
	alert();
	document.getElementById('exportToExcel').click();
}


$(document).ready(function () {
 
 
 $(".exportToExcel").click(function(e){
					var table = $(this).prev('.table2excel');
					if(table && table.length){
						var preserveColors = (table.hasClass('table2excel_with_colors') ? true : false);
						$(table).table2excel({
							exclude: ".noExl",
							name: "Excel Document Name",
							filename: "VendorBlanaceHistory" + new Date().toISOString().replace(/[\-\:\.]/g, "") + ".xls",
							fileext: ".xls",
							exclude_img: true,
							exclude_links: true,
							exclude_inputs: true,
							preserveColors: preserveColors
						});
					}
				});
 
 
 
});

		</script>