<?php
include('sessionCheck.php');
include('connection.php');
include('functions.php');

$lable_tafseel='تفصیل';
$lable_date='تاریخ';
$lable_newbill='نیو بل';
$lable_raqmwasool='رقم وصول';
$lable_baqyaraqam='بقایا رقم';
$lable_sabqa='سابقہ';
$lable_billwapsi='بل واپسی';


$page_print_width='7.1in';
$print_header='yes';



if(isset($_POST['print_size']))
{
    $print_size=$_POST['print_size'];
    if($print_size=='a4') { $page_print_width='8.5in';}
    if($print_size=='a5') { $page_print_width='8.5in';}
}


if(isset($_POST['print_header']))
{
    $print_header=$_POST['print_header'];
}


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
        $from_date=" AND trn_date>='".$from_date_post."'";
    }
    if(!empty($_POST['to_date']))
    {
        $to_date_post=date('Y-m-d',strtotime($_POST["to_date"]));
        $to_date=" AND trn_date<='".$to_date_post."'";
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
                    SELECT ifnull(SUM(trn_balance),0) as opening FROM  view_customerbalance2  WHERE client_id=$client_id_post AND trn_date<'$from_date_post'";
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
<link rel="preconnect" href="https://fonts.gstatic.com">
<link href="https://fonts.googleapis.com/css2?family=Noto+Serif&display=swap" rel="stylesheet">
<style>
* { font-family:Noto, Helvetica, sans-serif;}


.table-condensed>tbody>tr>td, .table-condensed>tbody>tr>th, .table-condensed>tfoot>tr>td, .table-condensed>tfoot>tr>th, .table-condensed>thead>tr>td, .table-condensed>thead>tr>th
{
    padding: 2px !important;
    border: 1px solid #3a3a3a;
}


#main_container{
    width: <?=$page_print_width?>;
    display: block;
    margin: 0 auto;
}


</style>



</head>
    <body>
        <div id="main_container">
            <div id="main_centerBody">

<?php
$branchQ="SELECT * FROM adm_branch WHERE branch_id=$branch_id";
$branchRow=mysqli_fetch_assoc(mysqli_query($con,$branchQ)); 
//$logo_source='<img src="img/weblogo.png" height="70">';


if($print_header=='yes')
{
?>
<table style="width:100%;" align="center" border="0">
    
    <tr><td style="text-align:center;"><h2><?php echo $branchRow['branch_Name'];?></h2><?php echo $branchRow['branch_Address'];?><br /><?php echo $branchRow['branch_Phone1'];?></td></tr>
</table>
<?php } ?>


<table style="width:100%;" align="center" border="0">
    
    <tr><td style="text-align:center;"><h1>Customer Ledger</h1></td></tr>
</table>
<div style="width:100%; text-align:right; margin:0 auto;"><span class="btn btn-primary" onClick="callExcelButton();">Export to XLS</span></div>
<br>

                <br>
                <table style="width:100%; font-weight: bold; font-size: 16px; margin:0 auto;" class="table table-bordered table-condensed table-hover table-striped table2excel" data-tableName="Test Table 2">
                    <tr>
                            <?php
                            $from_Date= ($from_date_post);
                            $to_Date= ($to_date_post);
                              ?>
                            <td ><b>Date From:</b></td><td colspan="7"> <?php echo validate_date_display($from_Date); ?></td>
                         </tr>
                         <tr>
                          <td ><b>Date To:</b></td><td colspan="7"> <?php echo validate_date_display($to_Date); ?></td>
                        </tr>
                        <tr>
                            <td ><b>Customer Name:</b></td><td colspan="7"><?php echo $client_Name; ?></td>
                        </tr>
                    <tr  style="background:pink; font-size:13px; text-align:center;">
                        <th width="25%" style="text-align:center;"><strong>Detail<br><span style="font-size: 20px;"><?=$lable_tafseel;?></span></strong></th> 
                        <th width="15%" style="text-align:center;"><strong>Date<br><span style="font-size: 20px;"><?=$lable_date;?></span></strong></th>
                        <th width="20%" style="text-align:center;"><strong>New Bill<br><span style="font-size: 20px;"><?=$lable_newbill;?></span></strong></th>
                        <th width="20%" style="text-align:center;"><strong>Cash Received<br><span style="font-size: 20px;"><?=$lable_raqmwasool;?></span></strong></th>
                        <th width="20%" style="text-align:center;"><strong>Remaining Balance<br><span style="font-size: 20px;"><?=$lable_baqyaraqam;?></span></strong></th>
                    </tr>
                    <tr style="font-size:14px; font-weight: bold;">
                        <td colspan="4" style="text-align: right; font-size: 20px;"><?=$lable_sabqa;?></td>
                        <td style="text-align:right"><?=number_format($openingbalance,2)?></td>
                        
                    </tr>
                    
                    <?php
                    $pQ="SELECT * FROM view_customerbalance2 WHERE 1 $from_date $to_date $client_id ORDER BY trn_date ASC, trn_orderby";
                    //echo '<pre>'.$pQ.'</pre>';
                    $pQRes=mysqli_query($con,$pQ);
                  
                    if(mysqli_num_rows($pQRes)<1)
                    {
                    ?>
                        <tr>
                            <td colspan="9" style="text-align:center">No Record Found</td>
                        </tr>
                    <?php
                    }
                    else
                    {
                        $count=$currentDate=$bgColor=0;
                        $rowEndBalance=$openingbalance;
                        $endBalance=$openingbalance;
                        $total_dr=0;
                        $total_cr=0;
                        $total_type_name='';
                        $serial=0;
                        while($clRow=mysqli_fetch_assoc($pQRes))
                        {
                            $serial++;
                            $rowEndBalance+=($clRow['dr']-$clRow['cr']);
                            $endBalance+=($clRow['dr']-$clRow['cr']);
                            $count++;
                              ?>
                                <tr style="font-size:14px;">
                                    <td style="text-align:center; font-size: 20px;">
                                        <?php
                                        if($clRow['trn_type']=='sale')
                                        {
                                            echo $lable_newbill;
                                        }
                                        elseif($clRow['trn_type']=='salereturn')
                                        {
                                            echo $lable_billwapsi;
                                        }
                                        elseif($clRow['trn_type']=='salecash')
                                        {
                                            echo $lable_raqmwasool;
                                        }
                                        else
                                        {
                                            echo '';
                                        }

                                        ?>
                                        
                                    </td>
                                    <td style="text-align:center;"><?=date("d-M-Y", strtotime($clRow['trn_date'])); ?></td>
                                    <td style="text-align: right;"><?=number_format($clRow['dr'],0); ?></td>
                                    <td style="text-align: right;"><?=number_format($clRow['cr'],0); ?></td>
                                    <td style="text-align: right;font-weight:bold;"><?=number_format(($rowEndBalance),0); ?></td>
                                </tr>
                        <?php
                        } ?>

                                <tr style="font-size:14px;">
                                    <th colspan="4" style="text-align:right;">Closing Balance</th>
                                    <th style="text-align:right;"><?=$currency_symbol. number_format(($endBalance),0); ?></th>
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