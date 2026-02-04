<?php
include('sessionCheck.php');
include('connection.php');
include('functions.php');
$branch_id=$_SESSION['branch_id'];

if(!isset($_POST['client_id']) || empty($_POST['client_id']))
{
    echo "Invalid Request";
    die();
}
else
{
	$from_date=$to_date='';
	$balance=0;
	$openingbalance=0;
    $client_id='';
	$client_id_post=0;
    if(!empty($_POST['from_date']))
    {
    	$from_date_post=date('Y-m-d',strtotime($_POST['from_date']));
    	$from_date=" AND s_Date>='".$from_date_post."'";
    }
    if(!empty($_POST['to_date']))
    {
    	$to_date_post=date('Y-m-d',strtotime($_POST["to_date"]));
    	$to_date=" AND s_Date<='".$to_date_post."'";
    }
	if(!empty($_POST['client_id']))
    {
    	$client_id_post=$_POST['client_id'];
    	$client_id=" AND client_id='".$client_id_post."'";
    }

	$client_Name="SELECT * FROM `adm_client` WHERE client_id=$client_id_post";
	$client_Res=mysqli_fetch_assoc(mysqli_query($con,$client_Name));
	$client_Name=$client_Res['client_Name'];
	//$openingbalance=$openingbalance+($client_Res['chart_openingpayment']-$client_Res['chart_openingreceive']);
	if(!empty($from_date))
	{
		$balanceQ="
					SELECT ifnull(SUM(s_NetAmount-s_PaidAmount),0) as opening
					FROM
					(
						SELECT s.s_id, s_Date, s.s_Number, s.s_NetAmount,
							ifnull((SELECT SUM(sp_Amount) FROM adm_sale_payment WHERE adm_sale_payment.s_id=s.s_id and client_id=$client_id_post AND sp_Type='S'),0) as s_PaidAmount
						FROM cust_sale as s
						WHERE s.client_id=$client_id_post AND s.s_Date<'$from_date_post'
					) as abc";
		//echo '<pre>'.$balanceQ.'</pre>';
		$balanceRes=mysqli_query($con,$balanceQ);
		$balanceRes=mysqli_fetch_assoc($balanceRes);
		$openingbalance=$openingbalance+$balanceRes['opening'];
	}
?>
<!DOCTYPE html>
<html><head><meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
<title>Customer Ledger</title>
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
	
	<tr><td style="text-align:center;"><h2><?php echo $branchRow['branch_Name'];?></h2><?php echo $branchRow['branch_Address'];?><br /><?php echo $branchRow['branch_Phone1'];?><br/><h1>Customer Ledger</strong></td></h1>
</table>
<div style="width:80%; text-align:right; margin:0 auto;"><span class="btn btn-primary" onClick="callExcelButton();">Export to XLS</span></div>
<br>

                <br>
                <table style="width:80%; margin:0 auto;" class="table table-bordered table-condensed table-hover table-stripped table2excel" data-tableName="Test Table 2">
                    <tr>
                            <?php
                            $from_Date= ($from_date_post);
                            $to_Date= ($to_date_post);
                              ?>
                            <td  ><b>Date From:</b></td><td colspan="5"> <?php echo validate_date_display($from_Date); ?>
                          </td>
                         </tr>
                         <tr>
                          <td ><b>Date To:</b></td><td colspan="5"> <?php echo validate_date_display($to_Date); ?></td>
                        </tr>
                        <tr>
                            <td><b>Customer Name:</b></td><td colspan="5"><?php echo $client_Name; ?></td>
                        </tr>
                    
                    <tr style="background:#009DFF; font-size:16px;">
                    	<td colspan="5">Opening Balance</td>
                        <td style="text-align:right"><?=$openingbalance?></td>
                    </tr>
                    <tr><td colspan="6">&nbsp;</td></tr>
                    <tr  style="background:#d65252; font-size:16px; text-align:center;">
                        <th width="17%" style="color:#fff; text-align:center;"><strong>Date</strong></th>
                        <th width="8%" style="color:#fff; text-align:center;"><strong>Bill No.</strong></th>
                        <th width="25%" style="color:#fff; text-align:center;"><strong>Description</strong></th>
                        <th width="12%" style="color:#fff; text-align:center;"><strong>Debit</strong></th>
                        <th width="15%" style="color:#fff; text-align:center;"><strong>Credit</strong></th>
                        <th width="20%" style="color:#fff; text-align:center;"><strong>Pending Amount</strong></th>
                    </tr>
                    
                    <?php
	                $pQ="SELECT s_id, s_Date, s_Number, client_id, s_NetAmount as dr, s_CreatedOn,s_Remarks
						FROM `cust_sale` WHERE 1 AND branch_id=$branch_id $from_date $to_date $client_id ORDER BY s_CreatedOn ASC";
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
							$s_id=$clRow['s_id'];
							$s_Number=$clRow['s_Number'];
							$rowEndBalance = $rowEndBalance + $clRow['dr'];
                            $endBalance=$endBalance+$clRow['dr'];
							$count++;
                              ?>
                              	<tr><td colspan="6">&nbsp;</td></tr>
                                <tr style="font-size:16px; background:aliceblue;">
                                     <td style="text-align:center;"><?php echo date("d-m-Y h:i s", strtotime($clRow['s_CreatedOn'])); ?></td>
                                    <td style="text-align:center;"><?php echo  $s_Number; ?></td>
                                    <td style="font-size:12px;"><?php echo  ($clRow['s_Remarks']); ?></td>
                                    <td style="text-align: right;"><?php echo  $clRow['dr']; ?></td>
                                    <td style="text-align: right;"><?php echo  '0.00'; ?></td>
                                    <td style="text-align: right;font-weight:bold;"><?php echo $currency_symbol.number_format($rowEndBalance,2); ?></td>
                                </tr>
                             <?php
							 
							 //sale payemnt records against sale id
							 $payQ="
SELECT trn_datetime,remarks, trn_dr, trn_cr 
FROM
(
	SELECT sp_CreatedOn as trn_datetime, sp_Description as remarks, '0' as trn_dr, sp_Amount as trn_cr 
	FROM `adm_sale_payment`
	WHERE sp_Type='S' and s_id=$s_id and branch_id=$branch_id $client_id
	
	UNION ALL
	SELECT sr_CreatedOn as trn_datetime, 'Sale Return' as remarks, '0' as trn_dr, sr_NetAmount as trn_cr  
	FROM cust_salereturn
	WHERE 1 and s_id=$s_id and branch_id=$branch_id $client_id
	
	UNION ALL
	SELECT sp_CreatedOn as trn_datetime, sp_Description as remarks, sp_Amount as trn_dr, '0' as trn_cr
	FROM `adm_sale_payment`
	WHERE sp_Type='SR' and s_id=(SELECT sr_id FROM `cust_salereturn` WHERE s_id=$s_id) and branch_id=$branch_id $client_id
) as abc
";
//echo '<pre>'.$payQ.'</pre>';

							 $payQR=mysqli_query($con,$payQ);
							 $payRows=mysqli_num_rows($payQR);
							 if($payRows>0)
							 {
								 while($prows=mysqli_fetch_assoc($payQR))
								 {
									$rowEndBalance = $rowEndBalance - $prows['trn_cr'] + $prows['trn_dr'];
									$endBalance=$endBalance+$prows['trn_dr'];
									$endBalance=$endBalance-$prows['trn_cr'];
									?>
									<tr style="font-size:16px;">
										<td style="text-align:center;"><?php echo date("d-m-Y h:i s", strtotime($prows['trn_datetime'])); ?></td>
										<td style="text-align:center;"><?php echo  $s_Number; ?></td>
										<td style="font-size:12px;"><?php echo  $prows['remarks']; ?></td>
										<td style="text-align: right;"><?php echo  $prows['trn_dr']; ?></td>
										<td style="text-align: right;"><?php echo  $prows['trn_cr']; ?></td>
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
    
		<script>
 
function callExcelButton()
{
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
							filename: "CustomerBlanaceHistory" + new Date().toISOString().replace(/[\-\:\.]/g, "") + ".xls",
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


<?php  } ?>