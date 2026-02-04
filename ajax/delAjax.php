<?php 
include('../connection.php');

//print_r($_REQUEST);
//exit;
if(isset($_POST['quotation_id']))
{
	$q_id=$_POST['quotation_id'];
	$error=0;
	$delQ="DELETE FROM adm_quotation WHERE q_id=$q_id";
	if(mysqli_query($con,$delQ))
	{
		$delDetailQ="DELETE FROM adm_quotation_detail WHERE q_id=$q_id";
		if(mysqli_query($con,$delDetailQ))
		{
			echo "DELETED";
		}
		else
		{
			$error++;
		}
	}
	else
	{
		$error++;
	}
	if($error)
	{
		echo "";
	}
	else
	{
		echo "DELETED";
	}
}

if(isset($_POST['p_id']))
{
    $p_id = (int)$_POST['p_id'];
    $error = 0;

    // Get the purchase invoice number to find related vouchers
    $purQ = mysqli_query($con, "SELECT p_Number FROM adm_purchase WHERE p_id = $p_id");
    $purRow = mysqli_fetch_assoc($purQ);
    $p_number = $purRow ? $purRow['p_Number'] : '';

    // Delete related accounts_voucher and details (purchase voucher)
    if($p_number) {
        // Main voucher
        mysqli_query($con, "DELETE avd FROM accounts_voucher_detail avd
                            JOIN accounts_voucher av ON avd.voucher_id = av.voucher_id
                            WHERE av.voucher_no = '$p_number'");
        mysqli_query($con, "DELETE FROM accounts_voucher WHERE voucher_no = '$p_number'");
        // Payment voucher (if any)
        $p_pay_voucher = $p_number . '-PAY';
        mysqli_query($con, "DELETE avd FROM accounts_voucher_detail avd
                            JOIN accounts_voucher av ON avd.voucher_id = av.voucher_id
                            WHERE av.voucher_no = '$p_pay_voucher'");
        mysqli_query($con, "DELETE FROM accounts_voucher WHERE voucher_no = '$p_pay_voucher'");
    }

    $delQ = "DELETE FROM adm_purchase WHERE p_id=$p_id";
    if(mysqli_query($con,$delQ))
    {
        $delDetailQ = "DELETE FROM adm_purchase_detail WHERE p_id=$p_id";
        if(mysqli_query($con,$delDetailQ))
        {
            $delPaymentQ = "DELETE FROM adm_purchase_payment WHERE p_id=$p_id";
            if(mysqli_query($con,$delPaymentQ))
            {
                // Done
            }
            else
            {
                $error++;
            }
        }
        else
        {
            $error++;
        }
    }
    else
    {
        $error++;
    }
    echo $error ? "" : "DELETED";
}


if(isset($_POST['pr_id']))
{
    $pr_id = (int)$_POST['pr_id'];
    $error = 0;

    // Get the purchase return number to find related vouchers
    $prQ = mysqli_query($con, "SELECT pr_Number FROM adm_purchasereturn WHERE pr_id = $pr_id");
    $prRow = mysqli_fetch_assoc($prQ);
    $pr_number = $prRow ? $prRow['pr_Number'] : '';

    // Delete related accounts_voucher and details (purchase return voucher)
    if($pr_number) {
        // Main voucher
        mysqli_query($con, "DELETE avd FROM accounts_voucher_detail avd
                            JOIN accounts_voucher av ON avd.voucher_id = av.voucher_id
                            WHERE av.voucher_no = '$pr_number'");
        mysqli_query($con, "DELETE FROM accounts_voucher WHERE voucher_no = '$pr_number'");
        // Payment voucher (if any)
        $pr_pay_voucher = $pr_number . '-PAY';
        mysqli_query($con, "DELETE avd FROM accounts_voucher_detail avd
                            JOIN accounts_voucher av ON avd.voucher_id = av.voucher_id
                            WHERE av.voucher_no = '$pr_pay_voucher'");
        mysqli_query($con, "DELETE FROM accounts_voucher WHERE voucher_no = '$pr_pay_voucher'");
    }

    $delQ = "DELETE FROM adm_purchasereturn WHERE pr_id=$pr_id";
    if(mysqli_query($con,$delQ))
    {
        $delDetailQ = "DELETE FROM adm_purchasereturn_detail WHERE pr_id=$pr_id";
        if(mysqli_query($con,$delDetailQ))
        {
            // Done
        }
        else
        {
            $error++;
        }
    }
    else
    {
        $error++;
    }
    echo $error ? "" : "DELETED";
}


if(isset($_POST['s_id']))
{
    $s_id = (int)$_POST['s_id'];
    $error = 0;

    // Find the sale invoice number (voucher_no)
    $invQ = mysqli_query($con, "SELECT s_Number FROM cust_sale WHERE s_id = $s_id");
    $invRow = mysqli_fetch_assoc($invQ);
    $invoice_no = $invRow ? $invRow['s_Number'] : '';

    // DELETE Sale Voucher from accounts_voucher and accounts_voucher_detail
    if($invoice_no) {
        // Delete voucher detail
        mysqli_query($con, "DELETE avd FROM accounts_voucher_detail avd
                            JOIN accounts_voucher av ON avd.voucher_id = av.voucher_id
                            WHERE av.voucher_no = '$invoice_no'");

        // Delete voucher
        mysqli_query($con, "DELETE FROM accounts_voucher WHERE voucher_no = '$invoice_no'");
        
        // Payment voucher (if any, like INV1-PAY)
        $payment_voucher_no = $invoice_no . '-PAY';
        mysqli_query($con, "DELETE avd FROM accounts_voucher_detail avd
                            JOIN accounts_voucher av ON avd.voucher_id = av.voucher_id
                            WHERE av.voucher_no = '$payment_voucher_no'");
        mysqli_query($con, "DELETE FROM accounts_voucher WHERE voucher_no = '$payment_voucher_no'");
    }

    // Now delete sale, detail, and payment as before
    $delQ = "DELETE FROM cust_sale WHERE s_id=$s_id";
    if(mysqli_query($con,$delQ))
    {
        $delDetailQ = "DELETE FROM cust_sale_detail WHERE s_id=$s_id";
        if(mysqli_query($con,$delDetailQ))
        {
            $delPaymentQ = "DELETE FROM adm_sale_payment WHERE s_id=$s_id";
            if(mysqli_query($con,$delPaymentQ))
            {
                // All deleted successfully
            }
            else
            {
                $error++;
            }
        }
        else
        {
            $error++;
        }
    }
    else
    {
        $error++;
    }
    if($error)
    {
        echo "";
    }
    else
    {
        echo "DELETED";
    }
}

if(isset($_POST['sr_id']))
{
    $sr_id = (int)$_POST['sr_id'];
    $error = 0;

    // Get the sale return number to find related vouchers
    $srQ = mysqli_query($con, "SELECT sr_Number FROM cust_salereturn WHERE sr_id = $sr_id");
    $srRow = mysqli_fetch_assoc($srQ);
    $sr_number = $srRow ? $srRow['sr_Number'] : '';

    // Delete related accounts_voucher and details (sale return voucher)
    if($sr_number) {
        // Main voucher
        mysqli_query($con, "DELETE avd FROM accounts_voucher_detail avd
                            JOIN accounts_voucher av ON avd.voucher_id = av.voucher_id
                            WHERE av.voucher_no = '$sr_number'");
        mysqli_query($con, "DELETE FROM accounts_voucher WHERE voucher_no = '$sr_number'");
        // Payment voucher (if any)
        $sr_pay_voucher = $sr_number . '-PAY';
        mysqli_query($con, "DELETE avd FROM accounts_voucher_detail avd
                            JOIN accounts_voucher av ON avd.voucher_id = av.voucher_id
                            WHERE av.voucher_no = '$sr_pay_voucher'");
        mysqli_query($con, "DELETE FROM accounts_voucher WHERE voucher_no = '$sr_pay_voucher'");
    }

    $delQ = "DELETE FROM cust_salereturn WHERE sr_id=$sr_id";
    if(mysqli_query($con,$delQ))
    {
        $delDetailQ = "DELETE FROM cust_salereturn_detail WHERE sr_id=$sr_id";
        if(mysqli_query($con,$delDetailQ))
        {
            $delPaymentQ = "DELETE FROM adm_sale_payment WHERE s_id=$sr_id AND sp_Type='SR'";
            if(mysqli_query($con,$delPaymentQ))
            {
                // Done
            }
            else
            {
                $error++;
            }
        }
        else
        {
            $error++;
        }
    }
    else
    {
        $error++;
    }
    echo $error ? "" : "DELETED";
}


if(isset($_POST['sp_id']))
{
    $sp_id = (int)$_POST['sp_id'];
    $error = 0;

    // Get sale payment info for voucher deletion
    $payQ = mysqli_query($con, "SELECT sp_Description FROM adm_sale_payment WHERE sp_id = $sp_id");
    $payRow = mysqli_fetch_assoc($payQ);
    if($payRow) {
        $voucher_desc = mysqli_real_escape_string($con, $payRow['sp_Description']);
        // Delete related voucher(s)
        $vouchQ = mysqli_query($con, "SELECT voucher_id FROM accounts_voucher WHERE description = '$voucher_desc'");
        while($vrow = mysqli_fetch_assoc($vouchQ)) {
            $vid = $vrow['voucher_id'];
            mysqli_query($con, "DELETE FROM accounts_voucher_detail WHERE voucher_id = $vid");
            mysqli_query($con, "DELETE FROM accounts_voucher WHERE voucher_id = $vid");
        }
    }

    $delQ = "DELETE FROM adm_sale_payment WHERE sp_id = $sp_id";
    if(!mysqli_query($con, $delQ)){
        $error++;
    }

    echo $error ? "" : "DELETED";
    exit;
}

if(isset($_POST['pp_id']))
{
    $pp_id = (int)$_POST['pp_id'];
    $error = 0;

    // Get purchase payment info for voucher deletion
    $payQ = mysqli_query($con, "SELECT pp_Description FROM adm_purchase_payment WHERE pp_id = $pp_id");
    $payRow = mysqli_fetch_assoc($payQ);
    if($payRow) {
        $voucher_desc = mysqli_real_escape_string($con, $payRow['pp_Description']);
        // Delete related voucher(s)
        $vouchQ = mysqli_query($con, "SELECT voucher_id FROM accounts_voucher WHERE description = '$voucher_desc'");
        while($vrow = mysqli_fetch_assoc($vouchQ)) {
            $vid = $vrow['voucher_id'];
            mysqli_query($con, "DELETE FROM accounts_voucher_detail WHERE voucher_id = $vid");
            mysqli_query($con, "DELETE FROM accounts_voucher WHERE voucher_id = $vid");
        }
    }

    $delQ = "DELETE FROM adm_purchase_payment WHERE pp_id = $pp_id";
    if(!mysqli_query($con, $delQ)){
        $error++;
    }

    echo $error ? "" : "DELETED";
    exit;
}

if(isset($_POST['icat_id']))
{
	$icat_id=$_POST['icat_id'];
	$error=0;
	$delQ="DELETE FROM adm_itemcategory WHERE icat_id=$icat_id";
	if(mysqli_query($con,$delQ)){
		
	}else{
			$error++;
		}
		
		
	if($error){
		echo "";
	}
	else{
		echo "DELETED";
	}
}


if(isset($_POST['unit_id']))
{
	$unit_id=$_POST['unit_id'];
	$error=0;
	$delQ="DELETE FROM adm_itemunit WHERE unit_id=$unit_id";
	if(mysqli_query($con,$delQ)){
		
	}else{
			$error++;
		}
		
		
	if($error){
		echo "";
	}
	else{
		echo "DELETED";
	}
}

if(isset($_POST['isubcat_id']))
{
	$isubcat_id=$_POST['isubcat_id'];
	$error=0;
	$delQ="DELETE FROM adm_itemsubcategory WHERE isubcat_id=$isubcat_id";
	if(mysqli_query($con,$delQ)){
		
	}else{
			$error++;
		}
		
		
	if($error){
		echo "";
	}
	else{
		echo "DELETED";
	}
}


if(isset($_POST['pp_id']))
{
	$pp_id=$_POST['pp_id'];
	$error=0;
	$delQ="DELETE FROM adm_purchase_payment WHERE pp_id=$pp_id";
	if(mysqli_query($con,$delQ)){
		
	}else{
			$error++;
		}
		
		
	if($error){
		echo "";
	}
	else{
		echo "DELETED";
	}
}

if(isset($_POST['item_id']))
{
	$item_id=$_POST['item_id'];
	$error=0;
	$delQ="DELETE FROM adm_item WHERE item_id=$item_id";
	if(mysqli_query($con,$delQ)){
		
	}else{
			$error++;
		}
		
		
	if($error){
		echo "";
	}
	else{
		echo "DELETED";
	}
}
if(isset($_POST['w_id']))
{
	$w_id=$_POST['w_id'];
	$error=0;
	$delQ="DELETE FROM adm_work WHERE w_id=$w_id";
	if(mysqli_query($con,$delQ)){
		
	}else{
			$error++;
		}
		
		
	if($error){
		echo "";
	}
	else{
		echo "DELETED";
	}
}

if(isset($_POST['k_id']))
{
	$w_id=$_POST['k_id'];
	$error=0;
	$delQ="DELETE FROM adm_worker_kharcha WHERE k_id=$k_id";
	if(mysqli_query($con,$delQ)){
		
	}else{
			$error++;
		}
		
		
	if($error){
		echo "";
	}
	else{
		echo "DELETED";
	}
}


if(isset($_POST['wb_id']))
{
	$wb_id=$_POST['wb_id'];
	$error=0;
	$delQ="DELETE FROM adm_worker_borrow WHERE wb_id=$wb_id";
	if(mysqli_query($con,$delQ)){
		
	}else{
			$error++;
		}
		
		
	if($error){
		echo "";
	}
	else{
		echo "DELETED";
	}
}

if(isset($_POST['pro_id']))
{
	$pro_id=$_POST['pro_id'];
	$error=0;
	$delQ="DELETE FROM adm_production WHERE pro_id=$pro_id";
	if(mysqli_query($con,$delQ)){
		
	}else{
			$error++;
		}
		
		
	if($error){
		echo "";
	}
	else{
		echo "DELETED";
	}
}

?>