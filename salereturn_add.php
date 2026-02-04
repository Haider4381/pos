<?php
include('sessionCheck.php');
include('connection.php');
include('functions.php');
require_once ("inc/init.php");
require_once ("inc/config.ui.php");
$page_title = "Sale Return";
include ("inc/header.php");
include ("inc/nav.php");
$branch_id=$_SESSION['branch_id'];
$u_id=$_SESSION['u_id'];
?>
<!-- ==========================CONTENT STARTS HERE ========================== -->
<!-- MAIN PANEL --> 
<div id="main" role="main">

<?php
$breadcrumbs["Billing"] = "";
$sr_NumberPrefix='SR';
?>

<style>
.form-control {
    border-radius: 5px !important;
    box-shadow: none!important;
    -webkit-box-shadow: none!important;
    -moz-box-shadow: none!important;
    font-size: 12px;
    padding-left: 6px;
}
.select2-container .select2-choice {
    border-radius: 5px;
}
label {
    margin-top: 8px !important;
}
textarea.form-control {
    height: 70px;
}
select {
    display: block;
    height: 32px;
    padding: 0 0 0 8px;
    overflow: hidden;
    position: relative;
    border: 1px solid #ccc;
    white-space: nowrap;
    line-height: 32px;
    color: #444;
    text-decoration: none;
    background-clip: padding-box;
    -webkit-touch-callout: none;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
    background-color: #fff;
    border-radius: 5px !important;
    width: 400px;
}
b, strong {
    font-weight: 500;
}
.btn-group-xs>.btn, .btn-xs {
    padding: 1px 5px;
    font-size: 12px;
    line-height: 2.0;
    border-radius: 2px;
}
</style>
<!-- MAIN CONTENT -->
<div id="content">
    <section id="widget-grid" class="">
        <div class="row">
            <article class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <div class="jarviswidget jarviswidget-color-purity" id="wid-id-1" data-widget-editbutton="false">
                    <header>
                        <span class="small_icon"><i class="fa fa-file-text-o"></i></span>
                        <h2>Sale Return</h2>
                    </header>
                    <div>
                        <div class="widget-body no-padding">
<?php

if(isset($_POST['save']))
{
    $error=0;
    $client_id = $_POST['client_id'];
    $sale_id = $_POST['s_id'];
    $s_PaymentType = $_POST['s_PaymentType'];
    $s_Remarks = $_POST['s_Remarks'];
    $s_RemarksExternal = $_POST['s_RemarksExternal'];

    $s_TotalItems = $_POST['s_TotalItems'];
    $s_TotalAmount = $_POST['s_TotalAmount'];
    $s_TaxAmount = is_numeric($_POST['s_TaxAmount']) ? floatval($_POST['s_TaxAmount']) : 0;
    $s_Tax = is_numeric($_POST['s_Tax']) ? floatval($_POST['s_Tax']) : 0;
    $s_DiscountAmount = is_numeric($_POST['s_DiscountAmount']) ? floatval($_POST['s_DiscountAmount']) : 0;
    $s_Discount = is_numeric($_POST['s_Discount']) ? floatval($_POST['s_Discount']) : 0;
    $s_DiscountPrice = 0;
    $s_NetAmount = $_POST['s_NetAmount'];
    $s_Date = date('Y-m-d');
    $item_idArray = isset($_POST['item_id']) ? array_filter($_POST['item_id']) : [];
    $item_BarCodeArray = $_POST['item_BarCode'] ?? [];
    $item_IMEIArray = $_POST['item_IMEI'] ?? [];
    $item_SalePriceArray = $_POST['item_Rate'] ?? [];
    $item_DiscountAmountArray = $_POST['item_DiscountAmount'] ?? [];
    $item_QtyArray = $_POST['item_Qty'] ?? [];
    $item_CostPriceArray = $_POST['item_CostPrice'] ?? [];
    $item_NetPriceArray = $_POST['item_NetPrice'] ?? [];
    $branch_id = $_SESSION['branch_id'] ?? 1;
    $u_id = $_SESSION['u_id'] ?? 1;

    if(empty($item_idArray))
    {
        echo '<script> alert("Atleast 1 item must be selected"); window.location="";</script>';
        die();
    }

    // Generate Sale Return Number
    $NumberCheckQ = "SELECT MAX(sr_NumberSr) as sr_Number FROM cust_salereturn WHERE branch_id='$branch_id'";
    $NumberCheckRes = mysqli_query($con, $NumberCheckQ);
    $prefix = 'SR';
    if(mysqli_num_rows($NumberCheckRes)<1)
    {
        $s_NumberSr = 1;
        $s_Number = $prefix.'1';
    }
    else
    {
        $r = mysqli_fetch_assoc($NumberCheckRes);
        $s_NumberSr = $r['sr_Number']+1;
        $s_Number = $prefix.$s_NumberSr;
    }

    // Insert Sale Return Master
    $sQ = "INSERT INTO cust_salereturn(s_id, sr_Number, sr_NumberSr, sr_Date, client_id, sr_TotalAmount, sr_Discount, sr_DiscountAmount, sr_NetAmount, sr_TotalItems, sr_Remarks, sr_RemarksExternal, sr_CreatedOn, u_id, branch_id, sr_PaymentType, sr_Tax, sr_TaxAmount)
        VALUES ('$sale_id','$s_Number','$s_NumberSr','$s_Date','$client_id','$s_TotalAmount','$s_Discount','$s_DiscountAmount','$s_NetAmount','$s_TotalItems','$s_Remarks','$s_RemarksExternal',now(),'$u_id','$branch_id','$s_PaymentType','$s_Tax','$s_TaxAmount')";
    if(mysqli_query($con,$sQ))
    {
        $s_id = mysqli_insert_id($con);
        foreach ($item_idArray as $key => $item_id)
        {
            $item_BarCode = isset($item_BarCodeArray[$key]) ? $item_BarCodeArray[$key] : '';
            $item_IMEI = $item_IMEIArray[$key] ?? '';
            $item_SalePrice = $item_SalePriceArray[$key] ?? 0;
            $item_DiscountAmount = $item_DiscountAmountArray[$key] ?? 0;
            $item_CostPrice = $item_CostPriceArray[$key] ?? 0;
            $item_Qty = $item_QtyArray[$key] ?? 0;
            $item_NetPrice = $item_NetPriceArray[$key] ?? 0;
            $item_discount_amount_per_item = floatval($item_Qty) > 0 ? (floatval($item_DiscountAmount) / floatval($item_Qty)) : 0;

            $sdQ = "INSERT INTO cust_salereturn_detail (sr_id, srd_Date, item_id, item_BarCode, item_IMEI, item_Qty, item_SalePrice, item_DiscountPercentage, item_DiscountPrice, item_CostPrice, item_NetPrice, srd_CreatedOn, client_id, item_discount_amount_per_item)
                    VALUES ($s_id, '$s_Date', $item_id, '$item_BarCode', '$item_IMEI', '$item_Qty', '$item_SalePrice', 0, '$item_DiscountAmount', '$item_CostPrice', '$item_NetPrice', now(), $client_id, '$item_discount_amount_per_item')";
            if(!mysqli_query($con,$sdQ))
            {
                $error++;
            }
        }

        // Sale Return Payment
        if(!empty($_POST['sp_Amount']))
        {
            $sp_Amount = $_POST['sp_Amount'];
            $spQ = "INSERT INTO adm_sale_payment(client_id, sp_Amount, sp_Date, s_id, sp_Description, sp_Type, sp_CreatedOn, u_id, branch_id)
                    VALUES ($client_id, '$sp_Amount', '$s_Date', $s_id, 'Sale Return Payment', 'SR', now(), '$u_id', '$branch_id')";
            if(!mysqli_query($con,$spQ))
            {
                $error++;
            }
        }

        // --- ACCOUNTING VOUCHER POSTING FOR SALE RETURN ---
        // Find Client Account (Receivable)
        $client_acc_row = mysqli_fetch_assoc(mysqli_query($con, "SELECT account_id FROM accounts_chart WHERE account_id = '$client_id' AND branch_id='$branch_id' LIMIT 1"));
        $client_account_id = isset($client_acc_row['account_id']) ? $client_acc_row['account_id'] : 0;

        // Find Sale Return Account (Expense/Contra Income/Whatever logic you use)
        $pr_acc_row = mysqli_fetch_assoc(mysqli_query($con, "
            SELECT account_id FROM accounts_chart WHERE branch_id='$branch_id' AND (
                account_title = 'Sale Return' OR
                account_title = 'sale return' OR
                account_title = 'Sale Return Account' OR
                account_title = 'sale return account' OR
                account_title = 'SALE RETURN ACCOUNT' OR
                account_title = 'SALE RETURN' OR
                account_title = 'sale return a/c'
            ) LIMIT 1
        "));
        $sr_account_id = isset($sr_acc_row['account_id']) ? $sr_acc_row['account_id'] : 0;

        $errorMsg = '';
        if(!$client_account_id){
            $errorMsg .= 'Client account not found in Chart of Accounts. Please create/select client account first!<br>';
        }
        if(!$sr_account_id){
            $errorMsg .= 'Sale Return account not found in Chart of Accounts. Please create/select a Sale Return account!<br>';
        }
        if(!empty($errorMsg)){
            $_SESSION['msg'] = '<div class="alert alert-danger">' . $errorMsg . '</div>';
            ?>
            <script type="text/javascript">
                alert("<?php echo strip_tags($errorMsg); ?>");
                window.location.href="sale_return_add.php";
            </script>
            <?php
            exit;
        }

        // Voucher Header
        $voucher_type = 'Sale Return';
        $voucher_no = $s_Number;
        $voucher_desc = "Sale Return Invoice #$s_Number";
        $q_voucher = "INSERT INTO accounts_voucher (entry_date, voucher_type, voucher_no, description, created_by)
                      VALUES ('$s_Date', '$voucher_type', '$voucher_no', '".mysqli_real_escape_string($con,$voucher_desc)."', '$u_id')";
        mysqli_query($con, $q_voucher);
        $voucher_id = mysqli_insert_id($con);

        // Double Entry
        // Debit Sale Return Account (Expense/Contra Income)
        mysqli_query($con, "INSERT INTO accounts_voucher_detail (voucher_id, account_id, description, debit, credit)
                            VALUES ($voucher_id, $sr_account_id, '$voucher_desc', $s_NetAmount, 0)");

        // Credit Client (Receivable decrease)
        mysqli_query($con, "INSERT INTO accounts_voucher_detail (voucher_id, account_id, description, debit, credit)
                            VALUES ($voucher_id, $client_account_id, '$voucher_desc', 0, $s_NetAmount)");
    }
    else
    {
        $error++;
    }

    if(empty($error))
    {
        ?>
        <script type="text/javascript">
        x = confirm("Sale Return Saved Successfully.");
        if(x) window.location="";
        else window.location="";
        </script>
        <?php
    }
    else
    {
        $_SESSION['msg']='<div class="alert alert-danger">Problem Saving Sale Return.</div>';
    }
}
?>

<?php if(!empty($_SESSION['msg'])){ echo $_SESSION['msg'];unset($_SESSION['msg']);} ?>

<br style="clear:both;" />
<form id="checkout-form" class="smart-form" novalidate="novalidate" method="post" action="" onsubmit="return checkParameters();">    
    <fieldset style="padding:0px 10px 10px 10px !important;">
    <input type="hidden" value="0" name="s_id" id="s_id" />
        <div class="row" style="margin-bottom: 5px;">
            <div class="col col-lg-1">Customer</div>
            <div class="col col-lg-3">
            <select class="form-control" name="client_id" id="client_id" required>
                <option value="">Select Customer</option>
                <?php $supArray=get_AccountClientList();
                foreach ($supArray as $supRow) { ?>
                <option value="<?php echo $supRow['account_id'];?>"><?php echo $supRow['account_title'];?></option>
                <?php } ?>
            </select>
            </div>
            <div class="col col-lg-3">
                <?php
                $srNumberQ=mysqli_fetch_assoc(mysqli_query($con, "select (ifnull(MAX(sr_NumberSr),0)+1) as sr_Number from cust_salereturn where branch_id=$branch_id"));
                $sr_Number=$srNumberQ['sr_Number'];
                ?>
                <span style="font-size:20px;  color: #d65252;"><?=$sr_NumberPrefix.$sr_Number?></span>
            </div>
        </div>
        <div class="row" style="margin-top:5px">
            <div class="col col-lg-12 col-xs-12 col-md-12">
            <table style="width:100%; background:#f1f1f1;" class="table table-condensed">
                <tr>
                    <th style="width:40%;">Product Name</th>
                    <th style="width:10%;">Quantity</th>
                    <th style="width:10%;">Unit Price</th>
                    <th style="width:10%;">Discount Amt</th>
                    <th style="width:10%;">Total</th>
                    <th style="width:10%;">Cost Price</th>
                    <th rowspan="2" style="width:5%;">
                        <p class="btn btn-primary" style="background:#09F; border:none; padding:12px; font-size:25px;" onclick="addToTable();"><i class="fa fa-shopping-cart"></i></p>
                    </th>
                </tr>
                <tr>
                    <td>
                        <input list="item_list"  name="" id="ex_item" class="form-control" placeholder="Search Product by Name" required autocomplete="off"  oninput="getItemDetail()" onblur="getItemDetail()" onkeydown="if(event.keyCode==13){event.preventDefault();getItemDetail(true);}" style="padding-left:8px;">
                        <datalist id="item_list">
                            <?php $itemsArray=get_ActiveItems();
                             foreach ($itemsArray as $key => $itemRow) 
                             { 
                            ?>
                            <option value="<?php echo $itemRow['item_Name'];?>" data-item-id="<?php echo $itemRow['item_id'];?>" data-item-name="<?php echo $itemRow['item_Name'];?>">
                            <?php 
                             } 
                            ?>
                        </datalist>
                        <input type="hidden" id="ex_itemname">
                        <input type="hidden" id="ex_item_id_from_imei">
                        <input type="hidden" id="ex_itemcode">
                        <input type="hidden" id="ex_item_stock">
                        <input type="hidden" id="ex_stock">
                    </td>
                    <td><input type="number" id="ex_qty" class="form-control" style="text-align:center;" onkeyup="calculate_netamount_row()" onkeydown="if(event.keyCode==13){event.preventDefault();$('#ex_rate').focus();}"></td>
                    <td><input type="number" id="ex_rate" class="form-control"  style="text-align:right" onkeyup="calculate_netamount_row()" onkeydown="if(event.keyCode==13){event.preventDefault();$('#ex_costprice').focus();}"></td>
                    <td><input type="number" id="ex_discount_amount" class="form-control"  min="0" style="text-align:right" onkeyup="calculate_netamount_row()" autocomplete="off" onkeydown="if(event.keyCode==13){event.preventDefault();$('#ex_netamount').focus();}"></td>
                    <td><input type="number" id="ex_netamount" readonly="readonly" class="form-control" style="text-align:right" onkeydown="if(event.keyCode==13){event.preventDefault();$('#ex_costprice').focus();}"></td>
                    <td><input type="text" id="ex_costprice" class="form-control" onkeydown="if(event.keyCode==13){event.preventDefault();addToTable();}"></td>
                </tr>
            </table>
            </div>
            </div>
            <div style=" min-height: 200px;">
            <table id="u_tbl" class="table table-bordered" style=" width:100%;margin-top:10px;">
                <tr>
                    <th style="width:30%;">Product Name</th>
                    <th style="width:10%">Quantity</th>
                    <th style="width:10%;">Unit Price</th>
                    <th style="width:13%;">Discount Amt</th>
                    <th style="width:10%; ">Cost Price</th>
                    <th style="width:10%">Ext Amount</th>
                    <th style="width:10%;">Action</th>
                </tr>
                <tr id="u_row" style="display: none;">
                    <td class="show_item" style="width:30%;"></td>
                    <td class="show_qty" style="width: 10%;"></td>
                    <td class="show_rate" style="width: 10%;"></td>
                    <td class="show_discount_amount" style="width: 13%;"></td>
                    <td class="show_costprice" style="width: 10%; "></td>
                    <td class="show_netprice" style="width: 10%;"></td>
                    <td style="width:10%;">
                        <p class="btn btn-danger" onclick="delRow(this)">Delete</p> 
                    </td>
                    <input type="hidden" name="item_id[]">
                    <input type="hidden" name="item_IMEI[]">
                    <input type="hidden" name="item_CostPrice[]">
                    <input type="hidden" name="item_Rate[]">
                    <input type="hidden" name="item_DiscountAmount[]">
                    <input type="hidden" name="item_Qty[]">
                    <input type="hidden" name="item_NetPrice[]">
                </tr>
            </table>
            </div>
            <div class="" style="margin-top:10px">
                <div class="col-lg-12 col-md-12 col-xs-12">
                    <table style="width:100%; background:#f1f1f1;" class="table table-condensed">
                        <tr>
                            <th style="width:10%">Internal Notes</th>
                            <th style="width:10%">External Notes</th>
                            <th style="width:50%" rowspan="2">
                                <table style="width:100%;">
                                    <tr>
                                        <td style="width: 30%;">Discount(Amount)<br /><input type="number" name="s_Discount" id="s_Discount" placeholder="Enter Diss. Amount" class="form-control" onkeyup="calculate()"  onchange="calculate();" style="text-align:right"  min="0"></td>
                                        <td style="width: 3%;">&nbsp;</td>
                                        <td style="width: 30%;">VAT(Amount)<br /><input type="number" name="s_Tax"  placeholder="Enter Tax Amount" id="s_Tax" class="form-control" style="text-align:right" min="0" onkeyup="calculate()" onchange="calculate();"></td>
                                        <td style="width: 3%;">&nbsp;</td>
                                        <td style="width: 30%;">Bill Return (Amount)<br /><input type="number" name="sp_Amount" placeholder="Enter Received Amount" value="0" class="form-control" style="text-align:right" min="0"></td>
                                    </tr>
                                    <tr>
                                        <td>
                                        Diss.(Amount)<br />
                                        <h1 style="    color: black; padding:5px;font-size: 20px; text-align: right;background: lightgray;"><?=$currency_symbol?><span id="s_DiscountAmountShow">0</span></h1>
                                        <input type="hidden" name="s_DiscountAmount" id="s_DiscountAmount" class="form-control" style="text-align:right" min="0" readonly="readonly">
                                        </td>
                                        <td>&nbsp;</td>
                                        <td>
                                        VAT(Amount)<br />
                                        <h1 style="    color: black;font-size: 20px; padding:5px; text-align: right;background: lightgray;"><?=$currency_symbol?><span id="s_TaxAmountShow">0</span></h1>
                                        <input type="hidden" name="s_TaxAmount" id="s_TaxAmount" class="form-control" style="text-align:right" min="0" readonly="readonly">
                                        </td>
                                        <td>&nbsp;</td>
                                        <td>
                                            Payment Method<br />
                                            <select name="s_PaymentType" style="width:100%; font-size: 15px; margin-top: 3px; height: 33px;">
                                                <option value="cash">Cash Payment</option>
                                                <option value="bank">Bank Payment</option>
                                                <option value="creditcard">Credit Card</option>
                                            </select>
                                        </td>
                                    </tr>
                                </table>
                            </th>
                            <th style="width:30%; background:#09F !important;" rowspan="2">
                                <p style="padding:0; float:left; color:#FFF;">Due Amount</p><p style="padding:0; margin:0; float:right; color:#FFF;">Total Items: <span id="totalItems"></span></p>
                                <br />
                                <h1 style=" color:#FFF; font-size:40px; text-align:center;"><?=$currency_symbol?><span id="s_NetAmountShow">0</span></h1>
                                <input type="hidden" name="s_NetAmount" id="s_NetAmount" value="0">
                                <input type="hidden" name="s_TotalAmount" id="s_TotalAmount" value="0">
                                <input type="hidden" name="s_TotalItems" id="s_TotalItems" value="0">
                                <input type="hidden" name="save" value="1">
                                <p type="submit" class="btn btn-warning" style="font-weight: bold;padding: 5px 30px; background:orange;" id="submit" name="submit" onclick="saveForm();">Save </p>
                            </th>
                        </tr>
                        <tr>
                            <td><textarea class="form-control" name="s_Remarks" id="s_Remarks" style="width:230px;max-width:230px; min-width:230px; min-height:80px;height:80px; max-height:130px;"></textarea></td>
                            <td><textarea class="form-control" name="s_RemarksExternal" id="s_RemarksExternal" style="width:230px;max-width:230px; min-width:230px; min-height:80px;height:80px; max-height:130px;"></textarea></td>
                        </tr>
                    </table>
                </div>
            </div>
        </fieldset>
    </form>
</div>
<!-- end widget div -->
                    </div>
                </div>
            </article>
        </div>
    </section>
</div>
<!-- END MAIN CONTENT -->
</div>
<!-- END MAIN PANEL -->

<?php include ("inc/footer.php"); ?>
<?php include ("inc/scripts.php"); ?>

<script type="text/javascript">
// -- Enter par product autoload --
$("#ex_item").on("keydown", function(e){
    if(e.keyCode==13){
        e.preventDefault();
        getItemDetail(true); // true means enter pressed
    }
});

function saveForm()
{
    $("#checkout-form").submit();
}

function addToTable()
{
    var ex_itemname = $("#ex_item").val();
    var ex_itemid = $("#ex_item_id_from_imei").val();
    var ex_imei = $("#ex_imei").val() || '';
    var ex_qty = $("#ex_qty").val();
    var ex_rate = $("#ex_rate").val();
    var ex_discount_amount = $("#ex_discount_amount").val();
    var ex_netamount = $("#ex_netamount").val();
    var ex_costprice = $("#ex_costprice").val();

    if(ex_itemid==0 || !ex_itemid)
    {
        alert("Please choose item.");
        $("#ex_item").focus();
        return false;
    }

    var newRow = $("#u_row").clone().removeAttr("id").show();
    newRow.find(".show_item").text(ex_itemname);
    newRow.find(".show_qty").text(ex_qty);
    newRow.find(".show_rate").text(ex_rate);
    newRow.find(".show_discount_amount").text(ex_discount_amount);
    newRow.find(".show_costprice").text(ex_costprice);
    newRow.find(".show_netprice").text(ex_netamount);

    newRow.find('input[name="item_id[]"]').val(ex_itemid);
    newRow.find('input[name="item_IMEI[]"]').val(ex_imei);
    newRow.find('input[name="item_CostPrice[]"]').val(ex_costprice);
    newRow.find('input[name="item_Rate[]"]').val(ex_rate);
    newRow.find('input[name="item_DiscountAmount[]"]').val(ex_discount_amount);
    newRow.find('input[name="item_Qty[]"]').val(ex_qty);
    newRow.find('input[name="item_NetPrice[]"]').val(ex_netamount);

    $("#u_tbl").append(newRow);

    $("#ex_item").val("");
    $("#ex_imei").val("");
    $("#ex_qty").val("");
    $("#ex_rate").val("");
    $("#ex_costprice").val("");
    $("#ex_netamount").val("");
    $("#ex_discount_amount").val("");
    $("#ex_item").focus();
    calculate();
    totalItems();
}

function delRow(e)
{
    $(e).closest('tr').remove();
    calculate();
    totalItems();
}
function totalItems()
{
    var totalItems = $("#u_tbl tr:visible").length - 1;
    $("#totalItems").html(totalItems);
    $("#s_TotalItems").val(totalItems);
}

function calculate() {
    var item_NetPrice = document.getElementsByName('item_NetPrice[]');
    var net_sum = 0;
    $(item_NetPrice).each(function(index, elem) {
        var price = parseFloat(elem.value);
        if (!isNaN(price)) {
            net_sum += price;
        }
    });

    $("#s_TotalAmount").val(net_sum.toFixed(2));

    var s_Discount = parseFloat($("#s_Discount").val()) || 0;
    var s_Tax = parseFloat($("#s_Tax").val()) || 0;

    var net_amount = net_sum - s_Discount + s_Tax;

    $("#s_NetAmount").val(net_amount.toFixed(2));
    $("#s_NetAmountShow").html(net_amount.toFixed(2));

    $("#s_DiscountAmount").val(s_Discount.toFixed(2));
    $("#s_DiscountAmountShow").html(s_Discount.toFixed(2));

    $("#s_TaxAmount").val(s_Tax.toFixed(2));
    $("#s_TaxAmountShow").html(s_Tax.toFixed(2));
}

function calculate_netamount_row(val)
{
    var ex_qty = $("#ex_qty").val();
    var ex_rate = $("#ex_rate").val();
    var ex_discount_amount = $("#ex_discount_amount").val();

    ex_qty = ex_qty && !isNaN(ex_qty) ? parseFloat(ex_qty) : 0;
    ex_rate = ex_rate && !isNaN(ex_rate) ? parseFloat(ex_rate) : 0;
    ex_discount_amount = ex_discount_amount && !isNaN(ex_discount_amount) ? parseFloat(ex_discount_amount) : 0;

    var ex_netamount = (ex_qty * ex_rate) - ex_discount_amount;

    if(isNaN(ex_netamount) || ex_netamount < 0) ex_netamount = 0;

    $("#ex_netamount").val(ex_netamount.toString());
}

function checkParameters(){
    var s_TotalItems = $.trim($("#s_TotalItems").val());
    var client_id = $.trim($("#client_id").val());

    if (s_TotalItems == 0)
    {
        alert("Please choose at least one item.");
        $("#ex_imei").focus();
        return false;
    }
    
    if (client_id == 0)
    {
        alert("Please Give Customer Information.");
        $("#client_Name").focus();
        return false;
    }
}

// Update product fields on item select/enter immediately
function getItemDetail(enter)
{
    var sup_id=0;
    item_name=$('#ex_item').val();
    if(item_name!=='')
    {
        var selectedOption = $('option[value="'+$("#ex_item").val()+'"]');
        item_id=selectedOption.attr("data-item-id");
        if(item_id==undefined)
        {
            item_name=$('#ex_item').val();
            $('#ex_itemname').val(item_name);
            $('#ex_item_id_from_imei').val(0);
        }
        else
        {
            var allVars="item_id="+item_id+"&sup_id="+sup_id;
            $.ajax({
                type: "post",
                url: "purchase_add_json.php",
                dataType: 'json',
                data:allVars,
                cache: false,
                success: function(data)
                { 
                    $("#ex_item_id_from_imei").val(item_id);
                    $("#ex_itemname").val(data.item_Name);
                    $("#ex_itemcode").val(data.item_Code);
                    $("#ex_costprice").val(data.item_PurchasePrice);
                    $("#ex_qty").val('1');
                    $("#ex_rate").val(data.item_SalePrice);
                    $("#ex_netamount").val(data.item_SalePrice);
                    $("#ex_item_stock").val(data.item_CurrentStock);
                    $("#ex_stock").val(data.item_CurrentStock);
                    // Jab bhi enter ya input par yeh function chale, saari fields update ho jayengi
                    if (enter) {
                        // Focus to Qty for fast entry
                        setTimeout(function(){
                            $("#ex_qty").focus().select();
                        }, 20);
                    }
                },
                error:function(data)
                {
                    alert("Please Choose An Item First.");
                }
            });
        }
    }
}
</script>