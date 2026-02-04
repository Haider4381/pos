<?php
include('sessionCheck.php');
include 'connection.php';
include('functions.php');

$branch_id = intval($_SESSION['branch_id']);
$u_id      = intval($_SESSION['u_id']);

/**
 * Helper: recreate single canonical voucher for a payment.
 * - Deletes any existing voucher headers/details that match the payment (by related_pp_id if available
 *   or by voucher_no / common patterns).
 * - Inserts one accounts_voucher header and two accounts_voucher_detail rows.
 *
 * Params:
 *  $con, $pp_id (int|null), $pp_SrNo (int), $pp_Type ('P'|'PR'), $pp_Date (YYYY-MM-DD),
 *  $voucher_no (string), $voucher_desc (string), $supplier_account_id (int), $cash_account_id (int),
 *  $u_id (int), $pp_Amount (decimal)
 *
 * Throws Exception on DB error.
 */
function recreate_payment_voucher($con, $pp_id, $pp_SrNo, $pp_Type, $pp_Date, $voucher_no, $voucher_desc, $supplier_account_id, $cash_account_id, $u_id, $pp_Amount) {
    // sanitize
    $pp_id = $pp_id ? intval($pp_id) : 0;
    $pp_SrNo = intval($pp_SrNo);
    $pp_Type = mysqli_real_escape_string($con, $pp_Type);
    $pp_Date = mysqli_real_escape_string($con, $pp_Date);
    $voucher_no = mysqli_real_escape_string($con, $voucher_no);
    $voucher_desc = mysqli_real_escape_string($con, $voucher_desc);
    $supplier_account_id = intval($supplier_account_id);
    $cash_account_id = intval($cash_account_id);
    $u_id = intval($u_id);
    $debit = floatval($pp_Amount);

    // Build patterns that might match legacy and current voucher numbers:
    $sr_padded = str_pad($pp_SrNo,4,'0',STR_PAD_LEFT);
    $like1 = "PY-{$sr_padded}%"; // PY-0017-PAY ...
    $like2 = "{$pp_SrNo}-%";     // 17-PAY ...
    $like3 = "%{$sr_padded}%";   // any containing 0017

    // Determine voucher_type text
    $voucher_type = ($pp_Type === 'PR') ? 'Purchase Return' : 'Payment';
    $escVoucherType = mysqli_real_escape_string($con, $voucher_type);

    // 1) Find existing voucher_ids to remove:
    $voucherIds = [];

    // If accounts_voucher has related_pp_id, prefer using it for exact matches
    $hasRelatedCol = false;
    $resCols = mysqli_query($con, "SHOW COLUMNS FROM accounts_voucher LIKE 'related_pp_id'");
    if($resCols && mysqli_num_rows($resCols) > 0) $hasRelatedCol = true;

    if($pp_id && $hasRelatedCol){
        $q = "SELECT voucher_id FROM accounts_voucher WHERE related_pp_id=" . intval($pp_id);
        $r = mysqli_query($con, $q);
        while($row = mysqli_fetch_assoc($r)) $voucherIds[] = intval($row['voucher_id']);
    }

    // Also find vouchers by voucher_no and patterns (covers legacy formats)
    $q2 = "SELECT voucher_id FROM accounts_voucher
           WHERE voucher_type='{$escVoucherType}' AND (
                voucher_no = '{$voucher_no}'
                OR voucher_no LIKE '".mysqli_real_escape_string($con,$like1)."'
                OR voucher_no LIKE '".mysqli_real_escape_string($con,$like2)."'
                OR voucher_no LIKE '".mysqli_real_escape_string($con,$like3)."'
           )";
    $r2 = mysqli_query($con, $q2);
    while($row = mysqli_fetch_assoc($r2)) $voucherIds[] = intval($row['voucher_id']);

    // Unique ids
    $voucherIds = array_values(array_unique($voucherIds));

    if(count($voucherIds) > 0){
        $idList = implode(',', $voucherIds);
        if(!mysqli_query($con, "DELETE FROM accounts_voucher_detail WHERE voucher_id IN ($idList)")) {
            throw new Exception("Failed deleting existing voucher details: " . mysqli_error($con));
        }
        if(!mysqli_query($con, "DELETE FROM accounts_voucher WHERE voucher_id IN ($idList)")) {
            throw new Exception("Failed deleting existing vouchers: " . mysqli_error($con));
        }
    }

    // 2) Insert canonical voucher header + details
    // Build INSERT depending on related_pp_id existence
    if($hasRelatedCol && $pp_id){
        $vIns = "INSERT INTO accounts_voucher (entry_date,voucher_type,voucher_no,description,created_by,related_pp_id)
                 VALUES('{$pp_Date}','{$escVoucherType}','{$voucher_no}','{$voucher_desc}',{$u_id},".intval($pp_id).")";
    } else {
        $vIns = "INSERT INTO accounts_voucher (entry_date,voucher_type,voucher_no,description,created_by)
                 VALUES('{$pp_Date}','{$escVoucherType}','{$voucher_no}','{$voucher_desc}',{$u_id})";
    }
    if(!mysqli_query($con, $vIns)) {
        throw new Exception("Failed creating accounts_voucher: " . mysqli_error($con) . " -- SQL: " . $vIns);
    }
    $vid = intval(mysqli_insert_id($con));
    if($vid <= 0) throw new Exception("Failed to obtain voucher id after insert.");

    // Insert supplier debit
    $ins1 = "INSERT INTO accounts_voucher_detail (voucher_id,account_id,description,debit,credit)
             VALUES ($vid, ".intval($supplier_account_id).", '{$voucher_desc}', {$debit}, 0)";
    if(!mysqli_query($con, $ins1)) {
        throw new Exception("Failed inserting supplier voucher detail: " . mysqli_error($con));
    }

    // Insert cash credit
    $ins2 = "INSERT INTO accounts_voucher_detail (voucher_id,account_id,description,debit,credit)
             VALUES ($vid, ".intval($cash_account_id).", '{$voucher_desc}', 0, {$debit})";
    if(!mysqli_query($con, $ins2)) {
        throw new Exception("Failed inserting cash voucher detail: " . mysqli_error($con));
    }

    return $vid;
}

/* ====== NEXT SR (display) ====== */
$nextSrBase = 1;
mysqli_begin_transaction($con);
try {
    $srRes = mysqli_query($con,"SELECT IFNULL(MAX(pp_SrNo),0) AS lastSrNo FROM adm_purchase_payment WHERE branch_id='".intval($branch_id)."' FOR UPDATE");
    $srRow = mysqli_fetch_assoc($srRes);
    $nextSrBase = intval($srRow['lastSrNo']) + 1;
    mysqli_commit($con);
} catch(Exception $e){
    mysqli_rollback($con);
    $nextSrBase = 1;
}

$edit_id   = isset($_GET['edit_id']) ? intval($_GET['edit_id']) : 0;
$delete_id = isset($_GET['delete_id']) ? intval($_GET['delete_id']) : 0;

if($delete_id){
    // delete payment and any linked vouchers
    $pQ = mysqli_query($con,"SELECT voucher_no, pp_Type FROM adm_purchase_payment WHERE pp_id=".intval($delete_id)." AND branch_id='".intval($branch_id)."'");
    $pRow = mysqli_fetch_assoc($pQ);
    if($pRow){
        $voucher_no = mysqli_real_escape_string($con,$pRow['voucher_no']);
        $voucher_type = ($pRow['pp_Type']=='PR') ? 'Purchase Return' : 'Payment';

        // Try delete by related_pp_id if column exists
        $resCols = mysqli_query($con, "SHOW COLUMNS FROM accounts_voucher LIKE 'related_pp_id'");
        $hasRelatedCol = ($resCols && mysqli_num_rows($resCols) > 0);

        if($hasRelatedCol){
            mysqli_query($con,"DELETE avd FROM accounts_voucher_detail avd
                                INNER JOIN accounts_voucher av ON av.voucher_id = avd.voucher_id
                                WHERE av.related_pp_id = ".intval($delete_id));
            mysqli_query($con,"DELETE FROM accounts_voucher WHERE related_pp_id = ".intval($delete_id));
        }

        // Also delete any voucher rows that match voucher_no exactly (legacy safety)
        $escVoucherType = mysqli_real_escape_string($con, $voucher_type);
        $escVoucherNo = mysqli_real_escape_string($con, $voucher_no);
        mysqli_query($con,"DELETE avd FROM accounts_voucher_detail avd
                           INNER JOIN accounts_voucher av ON av.voucher_id = avd.voucher_id
                           WHERE av.voucher_type='{$escVoucherType}' AND av.voucher_no = '{$escVoucherNo}'");
        mysqli_query($con,"DELETE FROM accounts_voucher WHERE voucher_type='{$escVoucherType}' AND voucher_no = '{$escVoucherNo}'");
    }
    mysqli_query($con,"DELETE FROM adm_purchase_payment WHERE pp_id=".intval($delete_id)." AND branch_id='".intval($branch_id)."'");
    $_SESSION['flash_msg'] = "<div class='alert alert-success'>Payment deleted successfully.</div>";
    header("Location: purchase_payment.php");
    exit;
}

/* Flash */
$msg="";
if(isset($_SESSION['flash_msg'])){
    $msg=$_SESSION['flash_msg'];
    unset($_SESSION['flash_msg']);
}

/* EDIT LOAD */
$edit_row = [];
if($edit_id){
    $er = mysqli_query($con,"SELECT * FROM adm_purchase_payment WHERE pp_id=".intval($edit_id)." AND branch_id='".intval($branch_id)."' LIMIT 1");
    $edit_row = mysqli_fetch_assoc($er);
    if($edit_row){
        $nextSrBase = $edit_row['pp_SrNo'];
    } else {
        $_SESSION['flash_msg'] = "<div class='alert alert-danger'>Record not found for editing.</div>";
        header("Location: purchase_payment.php");
        exit;
    }
}

/* SAVE / UPDATE */
if(isset($_POST['submit'])){
    $sup_id         = validate_input($_POST['sup_id']);
    $pp_Amount      = floatval(str_replace(',','',$_POST['pp_Amount']));
    $pp_Date        = validate_date_sql($_POST['pp_Date']);
    $pp_Description = validate_input($_POST['pp_Description']);
    $pp_Type        = validate_input($_POST['pp_Type']); // P or PR
    $p_id=0; $pr_id=0;

    // get supplier account id (accounts_chart)
    $supplier_acc_row = mysqli_fetch_assoc(mysqli_query($con,"SELECT account_id FROM accounts_chart WHERE account_id='".intval($sup_id)."' AND branch_id='".intval($branch_id)."' LIMIT 1"));
    $supplier_account_id = $supplier_acc_row ? intval($supplier_acc_row['account_id']) : 0;

    // Find a cash account (existing logic)
    $cash_acc_row = mysqli_fetch_assoc(mysqli_query($con,"
        SELECT account_id FROM accounts_chart
         WHERE branch_id='".intval($branch_id)."'
           AND (
                account_title='Cash' OR account_title='CASH'
             OR account_title='Cash in Hand' OR account_title='CASH IN HAND'
             OR account_title='Cash A/C'
             OR (account_title LIKE '%CASH%' AND account_title NOT LIKE '%BANK%')
           )
         LIMIT 1
    "));
    $cash_account_id = $cash_acc_row ? intval($cash_acc_row['account_id']) : 0;

    $error = '';
    if(!$supplier_account_id) $error .= "Invalid Supplier Selected.<br>";
    if(!$cash_account_id)     $error .= "Cash Account not found.<br>";
    if($pp_Amount <= 0)        $error .= "Please provide a valid Amount.<br>";
    if(!$pp_Date)              $error .= "Invalid Date.<br>";

    if($error != ''){
        $msg = "<div class='alert alert-danger'>{$error}</div>";
    } else {
        if($edit_id){
            // EDIT flow: update payment and recreate voucher(s) so ledger reflects only single canonical entry
            mysqli_begin_transaction($con);
            try {
                // update payment
                $q = "UPDATE adm_purchase_payment SET
                        sup_id='".intval($sup_id)."',
                        p_id='".intval($p_id)."',
                        pr_id='".intval($pr_id)."',
                        pp_Amount='".mysqli_real_escape_string($con,$pp_Amount)."',
                        pp_Date='".mysqli_real_escape_string($con,$pp_Date)."',
                        pp_Description='".mysqli_real_escape_string($con,$pp_Description)."',
                        pp_Type='".mysqli_real_escape_string($con,$pp_Type)."'
                      WHERE pp_id=".intval($edit_id)." AND branch_id='".intval($branch_id)."'";
                $ok = mysqli_query($con, $q);
                if($ok === false) throw new Exception("Failed updating purchase payment: " . mysqli_error($con));

                // Determine canonical voucher_no to use:
                $storedVoucherNo = isset($edit_row['voucher_no']) ? $edit_row['voucher_no'] : '';
                $sr = isset($edit_row['pp_SrNo']) ? intval($edit_row['pp_SrNo']) : $nextSrBase;
                $sr_padded = str_pad($sr,4,'0',STR_PAD_LEFT);
                // prefer stored voucher no, else generate canonical form
                $canonicalVoucherNo = $storedVoucherNo ? $storedVoucherNo : ('PY-'.$sr_padded.(($pp_Type=='PR')?'-PR':'-PAY'));

                // Recreate canonical voucher (this will delete duplicates/old ones)
                recreate_payment_voucher($con, $edit_id, $sr, $pp_Type, $pp_Date, $canonicalVoucherNo, ($pp_Description!='' ? $pp_Description : 'Payment to Supplier'), $supplier_account_id, $cash_account_id, $u_id, $pp_Amount);

                mysqli_commit($con);
                $_SESSION['flash_msg'] = "<div class='alert alert-success'>Payment updated successfully.</div>";
                header("Location: purchase_payment.php");
                exit;
            } catch(Exception $e){
                mysqli_rollback($con);
                $msg = "<div class='alert alert-danger'>Transaction Failed: ".$e->getMessage()."</div>";
            }

        } else {
            // INSERT NEW payment
            mysqli_begin_transaction($con);
            try {
                $r = mysqli_query($con,"SELECT IFNULL(MAX(pp_SrNo),0) AS lastSrNo FROM adm_purchase_payment WHERE branch_id='".intval($branch_id)."' FOR UPDATE");
                $row = mysqli_fetch_assoc($r);
                $newSr = intval($row['lastSrNo']) + 1;
                $alpha = 'PY-'.str_pad($newSr,4,'0',STR_PAD_LEFT);
                $voucher_no = $alpha.(($pp_Type=='PR')?'-PR':'-PAY');

                $ins = "INSERT INTO adm_purchase_payment
                        (pp_Date,sup_id,pp_Amount,p_id,pr_id,pp_Description,pp_Type,pp_CreatedOn,u_id,branch_id,pp_SrNo,voucher_no)
                        VALUES('".mysqli_real_escape_string($con,$pp_Date)."','".intval($sup_id)."','".mysqli_real_escape_string($con,$pp_Amount)."','".intval($p_id)."','".intval($pr_id)."','".mysqli_real_escape_string($con,$pp_Description)."','".mysqli_real_escape_string($con,$pp_Type)."',NOW(),".intval($u_id).",".intval($branch_id).",".intval($newSr).",'".mysqli_real_escape_string($con,$voucher_no)."')";
                $ok = mysqli_query($con, $ins);
                if(!$ok) throw new Exception("Insert adm_purchase_payment failed: " . mysqli_error($con));

                $pp_id_new = intval(mysqli_insert_id($con));
                if($pp_id_new <= 0) throw new Exception("Failed to obtain newly inserted payment id.");

                // Create canonical voucher and link it with related_pp_id if possible
                recreate_payment_voucher($con, $pp_id_new, $newSr, $pp_Type, $pp_Date, $voucher_no, ($pp_Description!='' ? $pp_Description : 'Payment against Purchase'), $supplier_account_id, $cash_account_id, $u_id, $pp_Amount);

                mysqli_commit($con);
                $_SESSION['flash_msg'] = "<div class='alert alert-success'>Payment Saved Successfully.</div>";
                header("Location: purchase_payment.php");
                exit;
            } catch(Exception $e){
                mysqli_rollback($con);
                $msg = "<div class='alert alert-danger'>Transaction Failed: ".$e->getMessage()."</div>";
            }
        }
    }
}

/* ================= FILTERS & PAGINATION ================= */
$f_sup       = isset($_GET['f_sup']) ? trim($_GET['f_sup']) : '';
$f_voucher   = isset($_GET['f_voucher']) ? trim($_GET['f_voucher']) : '';
$f_payno     = isset($_GET['f_payno']) ? trim($_GET['f_payno']) : '';
$f_from      = isset($_GET['f_from']) ? $_GET['f_from'] : '';
$f_to        = isset($_GET['f_to']) ? $_GET['f_to'] : '';
$f_min       = isset($_GET['f_min']) ? trim($_GET['f_min']) : '';
$f_max       = isset($_GET['f_max']) ? trim($_GET['f_max']) : '';
$f_remarks   = isset($_GET['f_remarks']) ? trim($_GET['f_remarks']) : '';

$where = " PP.branch_id='".intval($branch_id)."' ";
if($f_sup!=='')      $where.=" AND PP.sup_id='".intval($f_sup)."' ";
if($f_voucher!=='')  $where.=" AND PP.voucher_no LIKE '%".mysqli_real_escape_string($con,$f_voucher)."%' ";
if($f_payno!==''){
    if(preg_match('/^\d+$/',$f_payno)){
        $where.=" AND PP.pp_SrNo='".intval($f_payno)."' ";
    } else {
        if(preg_match('/PY\-0*(\d+)/i',$f_payno,$m)){
            $where.=" AND PP.pp_SrNo='".intval($m[1])."' ";
        }
    }
}
if($f_from!=='')     $where.=" AND PP.pp_Date>='".mysqli_real_escape_string($con,$f_from)."' ";
if($f_to!=='')       $where.=" AND PP.pp_Date<='".mysqli_real_escape_string($con,$f_to)."' ";
if($f_min!=='')      $where.=" AND PP.pp_Amount>='".floatval($f_min)."' ";
if($f_max!=='')      $where.=" AND PP.pp_Amount<='".floatval($f_max)."' ";
if($f_remarks!=='')  $where.=" AND PP.pp_Description LIKE '%".mysqli_real_escape_string($con,$f_remarks)."%' ";

$page    = max(1,intval(isset($_GET['page'])?$_GET['page']:1));
$perPage = 20;
$offset  = ($page-1)*$perPage;

$countSql = "SELECT COUNT(*) AS cnt
             FROM adm_purchase_payment PP
             WHERE $where";
$countRes = mysqli_query($con,$countSql);
$totalRows = mysqli_fetch_assoc($countRes)['cnt'];
$totalPages = max(1,ceil($totalRows/$perPage));

$listSql = "SELECT PP.pp_id, PP.pp_SrNo, PP.sup_id, PP.pp_Amount, PP.pp_Date,
                   PP.pp_Description, PP.pp_Type, PP.voucher_no, AC.account_title
            FROM adm_purchase_payment PP
            LEFT JOIN accounts_chart AC ON AC.account_id=PP.sup_id
            WHERE $where
            ORDER BY PP.pp_id DESC
            LIMIT $offset,$perPage";
$listRun = mysqli_query($con,$listSql);

require_once ("inc/init.php");
require_once ("inc/config.ui.php");
$page_title = "Supplier Payments";
include ("inc/header.php");
include ("inc/nav.php");
?>
<div id="main" role="main">
<?php $breadcrumbs["Supplier Payments"] = ""; include("inc/ribbon.php"); ?>
<style>
body{background:#f8fafc;}
.form-control{border-radius:5px!important;font-size:13px;}
.custom-card{background:#fff;border-radius:8px;box-shadow:0 2px 10px #e4e4e4;padding:24px;margin-top:18px;}
.custom-table th{background:#f1f5f9;font-weight:700;font-size:12px;}
.custom-table td{font-size:12px;}
.custom-title{font-size:20px;font-weight:600;color:#444;}
.filter-label{font-size:11px;font-weight:600;margin-bottom:2px;}
.pagination a,.pagination span{display:inline-block;padding:4px 9px;margin:0 2px;border:1px solid #ccc;border-radius:4px;font-size:12px;text-decoration:none;color:#333;}
.pagination .active{background:#2566a0;color:#fff;border-color:#2566a0;}
.pagination .disabled{color:#aaa;background:#eee;border-color:#ddd;cursor:not-allowed;}
.small-note{color:#888;font-size:11px;}
</style>

<div id="content">
<section id="widget-grid">
<div class="row">
<article class="col-sm-12 col-md-10 col-lg-9 col-lg-offset-1">

<div class="custom-card">
    <div class="custom-header">
        <span class="custom-title"><i class="fa fa-money"></i> Supplier Payment</span>
    </div>
    <?php if($msg) echo $msg; ?>
    <form method="post" action="purchase_payment.php<?php echo $edit_id ? '?edit_id='.$edit_id : '';?>" onsubmit="return checkPay();" autocomplete="off">
        <fieldset>
            <?php
              $dispSr = isset($edit_row['pp_SrNo']) ? $edit_row['pp_SrNo'] : $nextSrBase;
              $alpha  = 'PY-'.str_pad($dispSr,4,'0',STR_PAD_LEFT);
            ?>
            <div class="row mb-2">
                <div class="col-sm-3"><label>Payment No.</label></div>
                <div class="col-sm-9">
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($alpha);?>" readonly style="background:#f4f7fa;font-weight:bold;">
                </div>
            </div>
            <div class="row mb-2">
                <div class="col-sm-3"><label>Supplier <span style="color:red">*</span></label></div>
                <div class="col-sm-9">
                    <select class="select2 form-control" name="sup_id" id="sup_id" required>
                        <option value="">Select Supplier</option>
                        <?php
                        $supArray = get_Supplier();
                        foreach($supArray as $s){
                            $sel = (isset($edit_row['sup_id']) && $edit_row['sup_id']==$s['account_id']) ? 'selected' : '';
                            echo "<option value='".intval($s['account_id'])."' $sel>".htmlspecialchars($s['account_title'])."</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="row mb-2">
                <div class="col-sm-3"><label>Date <span style="color:red">*</span></label></div>
                <div class="col-sm-9">
                    <input type="text" name="pp_Date" class="form-control datepicker" data-dateformat="dd-mm-yy"
                           value="<?php echo isset($edit_row['pp_Date']) ? date('d-m-Y',strtotime($edit_row['pp_Date'])) : date('d-m-Y');?>" required>
                </div>
            </div>
            <div class="row mb-2">
                <div class="col-sm-3"><label>Amount <span style="color:red">*</span></label></div>
                <div class="col-sm-9">
                    <input type="number" step="0.01" name="pp_Amount" id="pp_Amount" class="form-control" value="<?php echo isset($edit_row['pp_Amount']) ? $edit_row['pp_Amount'] : '';?>" required>
                </div>
            </div>
            <div class="row mb-2">
                <div class="col-sm-3"><label>Remarks</label></div>
                <div class="col-sm-9">
                    <textarea name="pp_Description" id="pp_Description" class="form-control" rows="2"><?php echo isset($edit_row['pp_Description']) ? htmlspecialchars($edit_row['pp_Description']) : '';?></textarea>
                </div>
            </div>
            <div style="display:none;">
                <select name="pp_Type">
                    <option value="P" <?php if(isset($edit_row['pp_Type']) && $edit_row['pp_Type']=='P') echo 'selected';?>>Purchase Payment</option>
                    <option value="PR" <?php if(isset($edit_row['pp_Type']) && $edit_row['pp_Type']=='PR') echo 'selected';?>>Return Payment</option>
                </select>
            </div>
        </fieldset>
        <footer>
            <input type="submit" name="submit" class="btn btn-success" value="<?php echo $edit_id ? 'Update Payment' : 'Save Payment';?>">
            <?php if($edit_id){ ?><a href="purchase_payment.php" class="btn btn-default">Cancel</a><?php } ?>
        </footer>
    </form>
</div>

<!-- FILTERS -->
<div class="custom-card">
    <h4 style="margin-top:0;font-size:16px;font-weight:600;">Filters</h4>
    <form method="get" action="purchase_payment.php" class="row g-2">
        <div class="col-sm-2">
            <label class="filter-label">Payment No.</label>
            <input type="text" name="f_payno" value="<?php echo htmlspecialchars($f_payno);?>" class="form-control" placeholder="PY-0005">
        </div>
        <div class="col-sm-2">
            <label class="filter-label">Voucher</label>
            <input type="text" name="f_voucher" value="<?php echo htmlspecialchars($f_voucher);?>" class="form-control">
        </div>
        <div class="col-sm-2">
            <label class="filter-label">From Date</label>
            <input type="date" name="f_from" value="<?php echo htmlspecialchars($f_from);?>" class="form-control">
        </div>
        <div class="col-sm-2">
            <label class="filter-label">To Date</label>
            <input type="date" name="f_to" value="<?php echo htmlspecialchars($f_to);?>" class="form-control">
        </div>
        <div class="col-sm-2">
            <label class="filter-label">Min Amt</label>
            <input type="text" name="f_min" value="<?php echo htmlspecialchars($f_min);?>" class="form-control">
        </div>
        <div class="col-sm-2">
            <label class="filter-label">Max Amt</label>
            <input type="text" name="f_max" value="<?php echo htmlspecialchars($f_max);?>" class="form-control">
        </div>
        <div class="col-sm-3" style="margin-top:8px;">
            <label class="filter-label">Supplier</label>
            <select name="f_sup" class="form-control select2">
                <option value="">All</option>
                <?php
                $fsq = mysqli_query($con,"SELECT account_id, account_title FROM accounts_chart WHERE branch_id='".intval($branch_id)."' ORDER BY account_title");
                while($fs = mysqli_fetch_assoc($fsq)){
                    $sel = ($f_sup !== '' && $f_sup == $fs['account_id']) ? 'selected' : '';
                    echo "<option value='".intval($fs['account_id'])."' $sel>".htmlspecialchars($fs['account_title'])."</option>";
                }
                ?>
            </select>
        </div>
        <div class="col-sm-5" style="margin-top:8px;">
            <label class="filter-label">Remarks Contains</label>
            <input type="text" name="f_remarks" value="<?php echo htmlspecialchars($f_remarks);?>" class="form-control">
        </div>
        <div class="col-sm-4 d-flex align-items-end" style="margin-top:28px;">
            <button type="submit" class="btn btn-primary btn-sm" style="margin-right:5px;">Apply</button>
            <a href="purchase_payment.php" class="btn btn-secondary btn-sm">Reset</a>
        </div>
    </form>
    <div class="small-note">Total Records: <?php echo $totalRows;?> | Page <?php echo $page;?> of <?php echo $totalPages;?></div>
</div>

<!-- LIST -->
<div class="custom-card">
    <h4 style="margin-top:0;font-size:16px;font-weight:600;"><i class="fa fa-list"></i> Payment List</h4>
    <div class="table-responsive">
        <table class="table table-bordered custom-table" id="payment_table">
            <thead>
                <tr>
                    <th>Payment No.</th>
                    <th>Voucher No.</th>
                    <th>Date</th>
                    <th>Supplier</th>
                    <th>Amount</th>
                    <th>Remarks</th>
                    <th style="width:80px;">Action</th>
                </tr>
            </thead>
            <tbody>
            <?php
            while($r = mysqli_fetch_assoc($listRun)){
                $alpha = 'PY-'.str_pad($r['pp_SrNo'],4,'0',STR_PAD_LEFT);
                echo "<tr>
                        <td class='text-center'>".htmlspecialchars($alpha)."</td>
                        <td>".htmlspecialchars($r['voucher_no'])."</td>
                        <td>".validate_date_display($r['pp_Date'])."</td>
                        <td>".htmlspecialchars($r['account_title'])."</td>
                        <td class='text-end'>".number_format($r['pp_Amount'],2)."</td>
                        <td>".htmlspecialchars($r['pp_Description'])."</td>
                        <td class='text-center'>
                            <a href='purchase_payment.php?edit_id=".intval($r['pp_id'])."' class='btn btn-xs btn-warning'><i class='fa fa-edit'></i></a>
                            <a href='purchase_payment.php?delete_id=".intval($r['pp_id'])."' onclick=\"return confirm('Delete this payment?');\" class='btn btn-xs btn-danger'><i class='fa fa-trash'></i></a>
                        </td>
                    </tr>";
            }
            if($totalRows == 0){
                echo "<tr><td colspan='7' class='text-center text-muted'>No records found.</td></tr>";
            }
            ?>
            </tbody>
        </table>
    </div>
    <div class="pagination">
        <?php
        $qs = $_GET; unset($qs['page']);
        $baseQS = http_build_query($qs);
        $linkBase = 'purchase_payment.php'.($baseQS ? '?'.$baseQS.'&' : '?');
        if($page>1){
            echo "<a href='".$linkBase."page=".($page-1)."'>« Prev</a>";
        } else {
            echo "<span class='disabled'>« Prev</span>";
        }
        $window=5;
        $start=max(1,$page-$window);
        $end=min($totalPages,$page+$window);
        for($p=$start;$p<=$end;$p++){
            if($p==$page) echo "<span class='active'>$p</span>";
            else echo "<a href='".$linkBase."page=$p'>$p</a>";
        }
        if($page<$totalPages){
            echo "<a href='".$linkBase."page=".($page+1)."'>Next »</a>";
        } else {
            echo "<span class='disabled'>Next »</span>";
        }
        ?>
    </div>
</div>

</article>
</div>
</section>
</div>
</div>
<?php include ("inc/footer.php"); include ("inc/scripts.php"); ?>
<script src="<?php echo ASSETS_URL;?>/js/plugin/select2/select2.min.js"></script>
<script>
$(function(){
    $('.select2').select2({width:'100%'});
});
function checkPay(){
    if($.trim($('#pp_Amount').val())=='' || $.trim($('#sup_id').val())==''){
        alert('Please fill required fields');
        return false;
    }
}
</script>