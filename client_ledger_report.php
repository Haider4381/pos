<?php 
include('connection.php');
if(!isset($_POST['client_id']))
{
    echo "Invalid Request";
    die();
}
else
{
	$from_date=$to_date='';
	$andSaleDate=' ';
	$andSaleReturnDate=' ';
	$andSalePaymentDate=' ';
	$andSaleReturnPaymentDate=' ';
    $client_id=$_POST['client_id'];
    if(!empty($_POST['from_date']))
    {
    	$from_date=$_POST['from_date'];
    	$andSaleDate.=" AND s_Date>='".$from_date."'";
    	$andSaleReturnDate.=" AND sr_Date>='".$from_date."'";
    	$andSalePaymentDate.=" AND sp_Date>='".$from_date."'";
    	$andSaleReturnPaymentDate.=" AND sp_Date>='".$from_date."'";
    }
    if(!empty($_POST['to_date']))
    {
    	$to_date=$_POST["to_date"];
    	$andSaleDate.=" AND s_Date<='".$to_date."'";
    	$andSaleReturnDate.=" AND sr_Date<='".$to_date."'";
    	$andSalePaymentDate.=" AND sp_Date<='".$to_date."'";
    	$andSaleReturnPaymentDate.=" AND sp_Date<='".$to_date."'";

    }

   $clientRow=mysqli_fetch_assoc(mysqli_query($con,"SELECT client_Name From adm_client WHERE client_id=$client_id"));
   $client_Name=$clientRow['client_Name'];

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
    if(mysqli_num_rows($balanceRes)>0)
    {
        $balanceRow=mysqli_fetch_assoc($balanceRes);
        $balance=$balanceRow['balance'];
    }
?>
<!DOCTYPE html>
<!-- saved from url=(0068)http://localhost/Medical%20Store%20Management%20System/minmaxQty.php -->
<html><head><meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
        <title>Customer Ledger</title>
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
            #main_centerBody table tr:nth-child(n+3):hover{
            background:#193b59 !important;
            color: white !important;
            font-weight: bold;
            font-size: 20px;
            }
        </style>
    </head>
    <body>
        <div id="main_container">
            <div id="main_centerBody">
              <?php 
$branchQ="SELECT branch_id, branch_Name, branch_Address, branch_Phone1, branch_Phone2, branch_Email,branch_Web FROM adm_branch WHERE 1";
$branchRow=mysqli_fetch_assoc(mysqli_query($con,$branchQ));

?>
<table style="width:3.125in;" align="center" border="0">
    <tr><td style="text-align:center;"><h2><?=$branchRow['branch_Name']; ?></h2><?=$branchRow['branch_Address']; ?><br />Phone: <?=$branchRow['branch_Phone1']; ?></td></tr>
    <tr><td style="text-align:center; font-size:23px;"><strong>Customer Ledger</strong></td></tr>
</table>
                <table>
                    <thead>
                        <tr>
                            <?php
                            $from_Date=sum_check_empty($from_date);
                            $to_Date=sum_check_empty($to_date);
                              ?>
                            <td width="100px"> <b>From Date:</b> </td>
                            <td width="20px"> <span><?php echo sum_date_formate($from_Date); ?></span> </td>
                            <td width="100px"> <b>To Date:</b> </td>
                            <td width="20px"> <span><?php echo sum_date_formate($to_Date); ?></span> </td>
                        </tr>
                        <tr>
                            <td><b>Client:</b></td>
                            <td colspan="3"><?php echo $client_Name; ?></td>
                        </tr>
                        <tr>
                            <td><b><?php if($balance>0){echo 'Receivable';} else{ echo 'Payable'; }?>:</b></td>
                            <td style="font-size: 20px;font-weight: bold;"><?php echo $balance; ?></td>
                        </tr>
                    </thead>
                    <tr style="background-color:#808080;color:#FFFFFF;">
                        <th width="100px">#</th>
                        <th width="100px">Date</th>
                        <th width="150px">Sale</th>
                        <td width="150px">Sale Return</th>
                        <th width="150px">Sale Payment</th>
                        <th width="150px">Sale Return Payment</th>
                        <th width="150px">Balance</th>
                        <th width="300px">Description/Remarks</th>
                    </tr>           
                    <tr bgcolor="#F5F5F5"></tr>
                    <?php

	                  $clientLedgQ="SELECT s_Date,s_NetAmount,'0' AS sr_NetAmount,'0' AS sp_AmountSale,'0' AS sp_AmountReturn,s_Remarks AS remarks 
									FROM cust_sale WHERE client_id=$client_id $andSaleDate
									UNION ALL
								SELECT sr_Date,'0' AS s_NetAmount,sr_NetAmount,'0' AS sp_AmountSale,'0' AS sp_AmountReturn ,sr_Remarks AS remarks
									FROM cust_salereturn WHERE client_id=$client_id $andSaleReturnDate
									UNION ALL
								SELECT sp_Date,'0' AS s_NetAmount,'0' AS sr_NetAmount,sp_Amount AS sp_AmountSale,'0' AS sp_AmountReturn ,sp_Description AS remarks 
									FROM adm_sale_payment WHERE client_id=$client_id AND sp_Type='S'  $andSalePaymentDate
									UNION ALL
								SELECT sp_Date,'0' AS s_NetAmount,'0' AS sr_NetAmount,'0' AS sp_AmountSale,sp_Amount AS sp_AmountReturn,sp_Description AS remarks
									FROM adm_sale_payment WHERE client_id=$client_id AND sp_Type='SR' $andSaleReturnPaymentDate
								ORDER BY s_Date DESC
					";
                    $clientLedgRes=mysqli_query($con,$clientLedgQ);
                  
                    if(mysqli_num_rows($clientLedgRes)<1)
                    {
                    ?>
                        <tr>
                            <td colspan="6">No Record found</td>
                        </tr>
                    <?php
                    }
                    else
                    {

                        $count=$currentDate=$bgColor=0;
						$remaingin_balance=$balance;                       
                        while($clRow=mysqli_fetch_assoc($clientLedgRes))
                        {
							if(!empty($clRow['s_NetAmount'])) { $remaingin_balance=$remaingin_balance+$clRow['s_NetAmount'];}
							else if(!empty($clRow['sr_NetAmount'])) { $remaingin_balance=$remaingin_balance-$clRow['sr_NetAmount'];}
							else if(!empty($clRow['sp_AmountSale'])) { $remaingin_balance=$remaingin_balance-$clRow['sp_AmountSale'];}
							else if(!empty($clRow['sp_Amountreturn'])) { $remaingin_balance=$remaingin_balance+$clRow['sp_Amountreturn'];}
							
							
                            $count++;
                            $bgColor1='#f0f8ff';
                            $bgColor2='#9ACDCD';
                            if($currentDate!=$clRow['s_Date'])
                            {
                                if($bgColor!=$bgColor1){
                                    $bgColor=$bgColor1;
                                }
                                else{
                                    $bgColor=$bgColor2;
                                }
                                $currentDate=$clRow['s_Date'];
                            }
                              ?>
                                <tr bgcolor="<?php echo $bgColor; ?>" style="text-align: center;">
                                    <td width="100px"><?php echo $count; ?></td>
                                    <td width="100px"><?php echo sum_date_formate($clRow['s_Date']); ?></td>
                                    <td width="100px"><?php echo sum_check_empty($clRow['s_NetAmount']); ?></td>
                                    <td width="100px"><?php echo sum_check_empty($clRow['sr_NetAmount']); ?></td>
                                    <td width="100px"><?php echo sum_check_empty($clRow['sp_AmountSale']); ?></td>
                                    <td width="100px"><?php echo sum_check_empty($clRow['sp_AmountReturn']); ?></td>
                                    <td width="100px"><?php echo sum_check_empty($remaingin_balance); ?></td>
                                    <td width="100px">
                                        <?php 
                                        echo sum_check_empty($clRow['remarks']);
                                        ?>
                                    </td>
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
<?php 
}

function sum_date_formate($date)
{
    if(empty($date))
    {
        return "-";
    }
    else
    {
        return $date=date('d-m-Y',strtotime($date));
    }
    
}
function sum_check_empty($value)
{
    if(empty($value)){
        return "-";
    }
    else
    {
        return $value;
    }
}
?>