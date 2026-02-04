<?php
include('sessionCheck.php');
include 'connection.php';
include('functions.php');

$branch_id = intval($_SESSION['branch_id']);
$u_id      = intval($_SESSION['u_id']);

/**
 * Helper: recreate single canonical voucher for a sale payment (collection).
 * - Removes existing voucher headers/details that match this collection by related_sp_id (if column exists)
 *   or by voucher_no / common patterns (covers legacy formats).
 * - Inserts exactly one accounts_voucher header and two accounts_voucher_detail rows:
 *     - debit = cash account
 *     - credit = customer account
 *
 * Params:
 *   $con, $sp_id, $sp_SrNo, $sp_Type ('S'|'SR'), $sp_Date (YYYY-MM-DD),
 *   $voucher_no (string), $voucher_desc (string),
 *   $cash_account_id, $client_account_id, $u_id, $sp_Amount (decimal)
 *
 * Throws Exception on DB error.
 */
function recreate_sale_voucher($con, $sp_id, $sp_SrNo, $sp_Type, $sp_Date, $voucher_no, $voucher_desc, $cash_account_id, $client_account_id, $u_id, $sp_Amount) {
    $sp_id = $sp_id ? intval($sp_id) : 0;
    $sp_SrNo = intval($sp_SrNo);
    $sp_Type = mysqli_real_escape_string($con,$sp_Type);
    $sp_Date = mysqli_real_escape_string($con,$sp_Date);
    $voucher_no = mysqli_real_escape_string($con,$voucher_no);
    $voucher_desc = mysqli_real_escape_string($con,$voucher_desc);
    $cash_account_id = intval($cash_account_id);
    $client_account_id = intval($client_account_id);
    $u_id = intval($u_id);
    $debit = floatval($sp_Amount);

    // patterns to catch legacy and new voucher_no formats:
    $sr_padded = str_pad($sp_SrNo,4,'0',STR_PAD_LEFT);
    $like1 = "CL-{$sr_padded}%"; // CL-0017-REC
    $like2 = "{$sp_SrNo}-%";     // 17-REC (if that existed)
    $like3 = "%{$sr_padded}%";

    // voucher_type string
    $voucher_type = ($sp_Type === 'SR') ? 'Sale Return' : 'Receipt';
    $escVoucherType = mysqli_real_escape_string($con,$voucher_type);

    // detect related column name (common variants)
    $related_col = null;
    $resCols = mysqli_query($con, "SHOW COLUMNS FROM accounts_voucher LIKE 'related_sp_id'");
    if($resCols && mysqli_num_rows($resCols) > 0) $related_col = 'related_sp_id';
    else {
        // fallbacks used earlier in purchase code
        $resCols2 = mysqli_query($con, "SHOW COLUMNS FROM accounts_voucher LIKE 'related_pp_id'");
        if($resCols2 && mysqli_num_rows($resCols2) > 0) $related_col = 'related_pp_id';
    }

    // collect voucher_ids to delete
    $voucherIds = [];

    if($sp_id && $related_col){
        $r = mysqli_query($con, "SELECT voucher_id FROM accounts_voucher WHERE {$related_col} = " . intval($sp_id));
        while($row = mysqli_fetch_assoc($r)) $voucherIds[] = intval($row['voucher_id']);
    }

    // also find by voucher_no / patterns
    $q2 = "SELECT voucher_id FROM accounts_voucher
           WHERE voucher_type='{$escVoucherType}' AND (
             voucher_no = '{$voucher_no}'
             OR voucher_no LIKE '".mysqli_real_escape_string($con,$like1)."'
             OR voucher_no LIKE '".mysqli_real_escape_string($con,$like2)."'
             OR voucher_no LIKE '".mysqli_real_escape_string($con,$like3)."'
           )";
    $r2 = mysqli_query($con, $q2);
    while($row = mysqli_fetch_assoc($r2)) $voucherIds[] = intval($row['voucher_id']);

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

    // insert canonical voucher header (include related_col if available)
    if($related_col && $sp_id){
        $vIns = "INSERT INTO accounts_voucher (entry_date,voucher_type,voucher_no,description,created_by,{$related_col})
                 VALUES('{$sp_Date}','{$escVoucherType}','{$voucher_no}','{$voucher_desc}',{$u_id},".intval($sp_id).")";
    } else {
        $vIns = "INSERT INTO accounts_voucher (entry_date,voucher_type,voucher_no,description,created_by)
                 VALUES('{$sp_Date}','{$escVoucherType}','{$voucher_no}','{$voucher_desc}',{$u_id})";
    }
    if(!mysqli_query($con, $vIns)) {
        throw new Exception("Failed creating accounts_voucher: " . mysqli_error($con) . " -- SQL: " . $vIns);
    }
    $vid = intval(mysqli_insert_id($con));
    if($vid <= 0) throw new Exception("Failed to obtain voucher id after insert.");

    // supplier debit (cash), customer credit
    $ins1 = "INSERT INTO accounts_voucher_detail (voucher_id,account_id,description,debit,credit)
             VALUES ($vid, ".intval($cash_account_id).", '{$voucher_desc}', {$debit}, 0)";
    if(!mysqli_query($con, $ins1)) {
        throw new Exception("Failed inserting cash voucher detail: " . mysqli_error($con));
    }
    $ins2 = "INSERT INTO accounts_voucher_detail (voucher_id,account_id,description,debit,credit)
             VALUES ($vid, ".intval($client_account_id).", '{$voucher_desc}', 0, {$debit})";
    if(!mysqli_query($con, $ins2)) {
        throw new Exception("Failed inserting customer voucher detail: " . mysqli_error($con));
    }

    return $vid;
}

/* ===================== GENERATE NEXT SR (display only for add) ===================== */
mysqli_begin_transaction($con);
try {
    $srNoRes = mysqli_query($con, "SELECT IFNULL(MAX(sp_SrNo),0) AS lastSrNo FROM adm_sale_payment WHERE branch_id='".intval($branch_id)."' FOR UPDATE");
    $srRow   = mysqli_fetch_assoc($srNoRes);
    $nextSrNoBase = intval($srRow['lastSrNo']) + 1;
    mysqli_commit($con);
} catch(Exception $e){
    mysqli_rollback($con);
    $nextSrNoBase = 1;
}

/* ===================== EDIT / DELETE HANDLERS ===================== */
$edit_id   = isset($_GET['edit_id']) ? intval($_GET['edit_id']) : 0;
$delete_id = isset($_GET['delete_id']) ? intval($_GET['delete_id']) : 0;

if($delete_id){
    $payQ = mysqli_query($con,"SELECT voucher_no, sp_Type FROM adm_sale_payment WHERE sp_id=".intval($delete_id)." AND branch_id='".intval($branch_id)."'");
    $payRow = mysqli_fetch_assoc($payQ);
    if($payRow){
        $voucher_no   = mysqli_real_escape_string($con,$payRow['voucher_no']);
        $voucher_type = ($payRow['sp_Type']=='SR') ? 'Sale Return' : 'Receipt';

        // delete by related_sp_id if exists
        $resCols = mysqli_query($con, "SHOW COLUMNS FROM accounts_voucher LIKE 'related_sp_id'");
        $hasRelatedCol = ($resCols && mysqli_num_rows($resCols) > 0);
        if($hasRelatedCol){
            mysqli_query($con,"DELETE avd FROM accounts_voucher_detail avd
                                INNER JOIN accounts_voucher av ON av.voucher_id = avd.voucher_id
                                WHERE av.related_sp_id = ".intval($delete_id));
            mysqli_query($con,"DELETE FROM accounts_voucher WHERE related_sp_id = ".intval($delete_id));
        }

        // also delete vouchers that match exact voucher_no + type (legacy safety)
        $escType = mysqli_real_escape_string($con,$voucher_type);
        $escNo = mysqli_real_escape_string($con,$voucher_no);
        mysqli_query($con,"DELETE avd FROM accounts_voucher_detail avd
                           INNER JOIN accounts_voucher av ON av.voucher_id = avd.voucher_id
                           WHERE av.voucher_type='{$escType}' AND av.voucher_no = '{$escNo}'");
        mysqli_query($con,"DELETE FROM accounts_voucher WHERE voucher_type='{$escType}' AND voucher_no = '{$escNo}'");
    }
    mysqli_query($con,"DELETE FROM adm_sale_payment WHERE sp_id=".intval($delete_id)." AND branch_id='".intval($branch_id)."'");
    $_SESSION['flash_msg'] = "<div class='alert alert-success'>Collection deleted successfully.</div>";
    header("Location: sale_payment.php");
    exit;
}

/* ===================== FLASH ===================== */
$msg="";
if(isset($_SESSION['flash_msg'])){
    $msg=$_SESSION['flash_msg'];
    unset($_SESSION['flash_msg']);
}

/* ===================== EDIT LOAD ===================== */
$edit_row=[];
if($edit_id){
    $er = mysqli_query($con,"SELECT * FROM adm_sale_payment WHERE sp_id=".intval($edit_id)." AND branch_id='".intval($branch_id)."' LIMIT 1");
    $edit_row = mysqli_fetch_assoc($er);
    if($edit_row){
        $nextSrNoBase = $edit_row['sp_SrNo'];
    }
}

/* ===================== SAVE / UPDATE ===================== */
if(isset($_POST['submit'])){
    $client_id      = validate_input($_POST['client_id']);
    $sp_Amount      = floatval(str_replace(',','',validate_input($_POST['sp_Amount'])));
    $sp_Date        = validate_date_sql($_POST['sp_Date']);
    $sp_Description = validate_input($_POST['sp_Description']);
    $sp_Type        = validate_input($_POST['sp_Type']); // hidden S/SR

    // find client account (asset) and not cash/bank/etc.
    $client_acc_row = mysqli_fetch_assoc(mysqli_query($con,"
        SELECT account_id FROM accounts_chart
        WHERE account_id='".intval($client_id)."' AND branch_id='".intval($branch_id)."' AND account_type='Asset'
          AND status='active'
          AND account_title NOT LIKE '%Cash%'
          AND account_title NOT LIKE '%Bank%'
          AND account_title NOT LIKE '%Sales%'
          AND account_title NOT LIKE '%Purchase%'
          AND account_title NOT LIKE '%Expense%'
          AND account_title NOT LIKE '%Return%'
        LIMIT 1
    "));
    $client_account_id = $client_acc_row ? intval($client_acc_row['account_id']) : 0;

    // find cash account
    $cash_acc_row = mysqli_fetch_assoc(mysqli_query($con,"
        SELECT account_id FROM accounts_chart
        WHERE branch_id='".intval($branch_id)."'
          AND (
               account_title='Cash' OR account_title='CASH'
            OR account_title='Cash in Hand' OR account_title='CASH IN HAND'
            OR account_title='Cash A/C' OR account_title LIKE '%CASH IN HAND%'
            OR (account_title LIKE '%CASH%' AND account_title NOT LIKE '%BANK%')
          )
        LIMIT 1
    "));
    $cash_account_id = $cash_acc_row ? intval($cash_acc_row['account_id']) : 0;

    $errorMsg='';
    if(!$client_account_id) $errorMsg.="Invalid Customer Selected.<br>";
    if(!$cash_account_id)   $errorMsg.="Cash Account not found.<br>";
    if($sp_Amount <= 0)      $errorMsg.="Please enter a valid Amount.<br>";

    if($errorMsg!=''){
        $msg="<div class='alert alert-danger'>$errorMsg</div>";
    } else {
        if($edit_id){
            // update payment and recreate voucher(s)
            mysqli_begin_transaction($con);
            try {
                $q = "UPDATE adm_sale_payment SET
                        client_id='".intval($client_id)."',
                        sp_Amount='".mysqli_real_escape_string($con,$sp_Amount)."',
                        sp_Date='".mysqli_real_escape_string($con,$sp_Date)."',
                        sp_Description='".mysqli_real_escape_string($con,$sp_Description)."',
                        sp_Type='".mysqli_real_escape_string($con,$sp_Type)."'
                      WHERE sp_id=".intval($edit_id)." AND branch_id='".intval($branch_id)."'";
                $ok = mysqli_query($con,$q);
                if($ok === false) throw new Exception("Failed updating collection: " . mysqli_error($con));

                $storedVoucherNo = isset($edit_row['voucher_no']) ? $edit_row['voucher_no'] : '';
                $sr = isset($edit_row['sp_SrNo']) ? intval($edit_row['sp_SrNo']) : $nextSrNoBase;
                $sr_padded = str_pad($sr,4,'0',STR_PAD_LEFT);
                $canonicalVoucherNo = $storedVoucherNo ? $storedVoucherNo : ('CL-'.$sr_padded.(($sp_Type=='SR')?'-SR':'-REC'));
                $voucher_desc = ($sp_Description!='') ? $sp_Description : 'Customer Collection';

                // recreate voucher (delete duplicates and insert single canonical)
                recreate_sale_voucher($con, $edit_id, $sr, $sp_Type, $sp_Date, $canonicalVoucherNo, $voucher_desc, $cash_account_id, $client_account_id, $u_id, $sp_Amount);

                mysqli_commit($con);
                $_SESSION['flash_msg']=$ok?"<div class='alert alert-success'>Collection updated successfully.</div>":"<div class='alert alert-danger'>Problem updating Collection</div>";
                header("Location: sale_payment.php");
                exit;
            } catch(Exception $e){
                mysqli_rollback($con);
                $msg="<div class='alert alert-danger'>Transaction Failed: ".$e->getMessage()."</div>";
            }
        } else {
            // insert new payment and create voucher (wrapped in transaction)
            mysqli_begin_transaction($con);
            try {
                $r = mysqli_query($con,"SELECT IFNULL(MAX(sp_SrNo),0) AS lastSrNo FROM adm_sale_payment WHERE branch_id='".intval($branch_id)."' FOR UPDATE");
                $row = mysqli_fetch_assoc($r);
                $newSr = intval($row['lastSrNo']) + 1;
                $alphaSr = 'CL-'.str_pad($newSr,4,'0',STR_PAD_LEFT);
                $voucher_no = $alphaSr.(($sp_Type=='SR')?'-SR':'-REC');

                $ins = "INSERT INTO adm_sale_payment
                        (sp_SrNo, client_id, sp_Amount, sp_Date, sp_Description, sp_CreatedOn, sp_Type, u_id, branch_id, voucher_no)
                        VALUES (".intval($newSr).",".intval($client_id).",'".mysqli_real_escape_string($con,$sp_Amount)."','".mysqli_real_escape_string($con,$sp_Date)."','".mysqli_real_escape_string($con,$sp_Description)."',NOW(),'".mysqli_real_escape_string($con,$sp_Type)."',".intval($u_id).",".intval($branch_id).",'".mysqli_real_escape_string($con,$voucher_no)."')";
                $ok = mysqli_query($con,$ins);
                if(!$ok) throw new Exception("Insert failed: " . mysqli_error($con));

                $sp_id_new = intval(mysqli_insert_id($con));
                if($sp_id_new <= 0) throw new Exception("Failed to obtain new collection id.");

                // create canonical voucher and link by related_sp_id if available
                recreate_sale_voucher($con, $sp_id_new, $newSr, $sp_Type, $sp_Date, $voucher_no, ($sp_Description!='' ? $sp_Description : 'Customer Collection'), $cash_account_id, $client_account_id, $u_id, $sp_Amount);

                mysqli_commit($con);
                $_SESSION['flash_msg']="<div class='alert alert-success'>Collection Saved Successfully.</div>";
                header("Location: sale_payment.php");
                exit;
            } catch(Exception $e){
                mysqli_rollback($con);
                $msg="<div class='alert alert-danger'>Transaction Failed: ".$e->getMessage()."</div>";
            }
        }
    }
}

/* ===================== FILTERS & PAGINATION ===================== */
$filter_client   = isset($_GET['f_client']) ? trim($_GET['f_client']) : '';
$filter_voucher  = isset($_GET['f_voucher']) ? trim($_GET['f_voucher']) : '';
$filter_colno    = isset($_GET['f_colno']) ? trim($_GET['f_colno']) : '';
$filter_from     = isset($_GET['f_from']) ? $_GET['f_from'] : '';
$filter_to       = isset($_GET['f_to']) ? $_GET['f_to'] : '';
$filter_min_amt  = isset($_GET['f_min']) ? trim($_GET['f_min']) : '';
$filter_max_amt  = isset($_GET['f_max']) ? trim($_GET['f_max']) : '';
$filter_remarks  = isset($_GET['f_remarks']) ? trim($_GET['f_remarks']) : '';

$where = " SP.branch_id='".intval($branch_id)."' ";
if($filter_client !== '')   $where .= " AND SP.client_id='".intval($filter_client)."' ";
if($filter_voucher !== '')  $where .= " AND SP.voucher_no LIKE '%".mysqli_real_escape_string($con,$filter_voucher)."%' ";
if($filter_colno !== '') {
    if(preg_match('/^\d+$/',$filter_colno)){
        $where .= " AND SP.sp_SrNo = '".intval($filter_colno)."' ";
    } else {
        if(preg_match('/CL\-0*(\d+)/i',$filter_colno,$m)){
            $where .= " AND SP.sp_SrNo = '".intval($m[1])."' ";
        }
    }
}
if($filter_from !== '')      $where .= " AND SP.sp_Date >= '".mysqli_real_escape_string($con,$filter_from)."' ";
if($filter_to !== '')        $where .= " AND SP.sp_Date <= '".mysqli_real_escape_string($con,$filter_to)."' ";
if($filter_min_amt!=='')     $where .= " AND SP.sp_Amount >= '".floatval($filter_min_amt)."' ";
if($filter_max_amt!=='')     $where .= " AND SP.sp_Amount <= '".floatval($filter_max_amt)."' ";
if($filter_remarks!=='')     $where .= " AND SP.sp_Description LIKE '%".mysqli_real_escape_string($con,$filter_remarks)."%' ";

$page    = max(1, intval(isset($_GET['page'])?$_GET['page']:1));
$perPage = 20;
$offset  = ($page - 1)*$perPage;

$countSql = "SELECT COUNT(*) AS cnt FROM adm_sale_payment SP WHERE $where";
$countRes = mysqli_query($con,$countSql);
$totalRows = mysqli_fetch_assoc($countRes)['cnt'];
$totalPages = max(1,ceil($totalRows / $perPage));

$listSql = "SELECT SP.sp_id, SP.sp_SrNo, SP.client_id, SP.sp_Amount, SP.sp_Date, SP.sp_Description,
                   SP.sp_Type, SP.voucher_no, AC.account_title
            FROM adm_sale_payment SP
            LEFT JOIN accounts_chart AC ON AC.account_id=SP.client_id
            WHERE $where
            ORDER BY SP.sp_id DESC
            LIMIT $offset,$perPage";
$listRun = mysqli_query($con,$listSql);

/* ===================== UI START ===================== */
require_once ("inc/init.php");
require_once ("inc/config.ui.php");
$page_title = "Customer Collection";
include ("inc/header.php");
include ("inc/nav.php");
?>
<!-- MAIN PANEL -->
<div id="main" role="main">
<?php $breadcrumbs["Customer Collection"] = ""; include("inc/ribbon.php"); ?>
<style>
body { background:#f8fafc; }
.form-control{border-radius:5px!important;font-size:13px;}
.custom-card{background:#fff;border-radius:8px;box-shadow:0 2px 10px #e4e4e4;padding:24px;margin-top:18px;}
.custom-table th{background:#f1f5f9;font-weight:700;font-size:12px;}
.custom-table td{font-size:12px;}
.custom-title{font-size:20px;font-weight:600;color:#444;}
.filter-label{font-size:11px;font-weight:600;margin-bottom:2px;}
.pagination{margin:10px 0;}
.pagination a,.pagination span{display:inline-block;padding:4px 9px;margin:0 2px;border:1px solid #ccc;border-radius:4px;font-size:12px;text-decoration:none;color:#333;}
.pagination .active{background:#2566a0;color:#fff;border-color:#2566a0;}
.pagination .disabled{color:#aaa;background:#eee;border-color:#ddd;cursor:not-allowed;}
.small-note{color:#888;font-size:11px;}
</style>

<div id="content">
<section id="widget-grid">
<div class="row">
<article class="col-sm-12 col-md-10 col-lg-9 col-lg-offset-1">
<!-- FORM CARD -->
<div class="custom-card">
    <div class="custom-header">
        <span class="custom-title"><i class="fa fa-money"></i> Customer Collection</span>
    </div>
    <?php if($msg) echo $msg; ?>
    <form method="post" action="sale_payment.php<?php echo $edit_id?'?edit_id='.$edit_id:'';?>" onsubmit="return checkParameters();" autocomplete="off">
        <fieldset>
            <?php
                $displaySr = isset($edit_row['sp_SrNo']) ? $edit_row['sp_SrNo'] : $nextSrNoBase;
                $displayAlpha = 'CL-'.str_pad($displaySr,4,'0',STR_PAD_LEFT);
            ?>
            <div class="row mb-2">
                <div class="col-sm-3"><label>Collection No.</label></div>
                <div class="col-sm-9">
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($displayAlpha);?>" readonly style="background:#f4f7fa;font-weight:bold;">
                </div>
            </div>
            <div class="row mb-2">
                <div class="col-sm-3"><label>Customer <span style="color:red">*</span></label></div>
                <div class="col-sm-9">
                    <select class="select2 form-control" name="client_id" id="client_id" required>
                        <option value="">Select Customer</option>
                        <?php
                        $cq = mysqli_query($con,"
                            SELECT account_id, account_title
                            FROM accounts_chart
                            WHERE branch_id='".intval($branch_id)."' AND account_type='Asset' AND status='active'
                              AND account_title NOT LIKE '%Cash%'
                              AND account_title NOT LIKE '%Bank%'
                              AND account_title NOT LIKE '%Sales%'
                              AND account_title NOT LIKE '%Purchase%'
                              AND account_title NOT LIKE '%Expense%'
                              AND account_title NOT LIKE '%Return%'
                            ORDER BY account_title");
                        while($cr = mysqli_fetch_assoc($cq)){
                            $sel = (isset($edit_row['client_id']) && $edit_row['client_id']==$cr['account_id'])?'selected':'';
                            echo "<option value='".intval($cr['account_id'])."' $sel>".htmlspecialchars($cr['account_title'])."</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="row mb-2">
                <div class="col-sm-3"><label>Date <span style="color:red">*</span></label></div>
                <div class="col-sm-9">
                    <input type="text" name="sp_Date" class="form-control datepicker" data-dateformat="dd-mm-yy" value="<?php echo isset($edit_row['sp_Date'])?date('d-m-Y',strtotime($edit_row['sp_Date'])):date('d-m-Y');?>" required>
                </div>
            </div>
            <div class="row mb-2">
                <div class="col-sm-3"><label>Amount <span style="color:red">*</span></label></div>
                <div class="col-sm-9">
                    <input type="number" step="0.01" name="sp_Amount" id="sp_Amount" class="form-control" value="<?php echo isset($edit_row['sp_Amount'])?$edit_row['sp_Amount']:'';?>" required>
                </div>
            </div>
            <div class="row mb-2">
                <div class="col-sm-3"><label>Remarks</label></div>
                <div class="col-sm-9">
                    <textarea name="sp_Description" id="sp_Description" class="form-control" rows="2"><?php echo isset($edit_row['sp_Description'])?htmlspecialchars($edit_row['sp_Description']):'';?></textarea>
                </div>
            </div>
            <div style="display:none;">
                <select name="sp_Type">
                    <option value="S" <?php if(isset($edit_row['sp_Type']) && $edit_row['sp_Type']=='S') echo 'selected';?>>Sale Payment</option>
                    <option value="SR" <?php if(isset($edit_row['sp_Type']) && $edit_row['sp_Type']=='SR') echo 'selected';?>>Sale Return</option>
                </select>
            </div>
        </fieldset>
        <footer>
            <input type="submit" name="submit" class="btn btn-success" value="<?php echo $edit_id?'Update Collection':'Save Collection';?>">
            <?php if($edit_id){ ?><a href="sale_payment.php" class="btn btn-default">Cancel</a><?php } ?>
        </footer>
    </form>
</div>

<!-- FILTER CARD -->
<div class="custom-card">
    <h4 style="margin-top:0;font-size:16px;font-weight:600;">Filters</h4>
    <form method="get" action="sale_payment.php" class="row g-2">
        <div class="col-sm-2">
            <label class="filter-label">Collection No.</label>
            <input type="text" name="f_colno" value="<?php echo htmlspecialchars($filter_colno);?>" class="form-control" placeholder="CL-0005">
        </div>
        <div class="col-sm-2">
            <label class="filter-label">Voucher</label>
            <input type="text" name="f_voucher" value="<?php echo htmlspecialchars($filter_voucher);?>" class="form-control">
        </div>
        <div class="col-sm-2">
            <label class="filter-label">From Date</label>
            <input type="date" name="f_from" value="<?php echo htmlspecialchars($filter_from);?>" class="form-control">
        </div>
        <div class="col-sm-2">
            <label class="filter-label">To Date</label>
            <input type="date" name="f_to" value="<?php echo htmlspecialchars($filter_to);?>" class="form-control">
        </div>
        <div class="col-sm-2">
            <label class="filter-label">Min Amt</label>
            <input type="text" name="f_min" value="<?php echo htmlspecialchars($filter_min_amt);?>" class="form-control">
        </div>
        <div class="col-sm-2">
            <label class="filter-label">Max Amt</label>
            <input type="text" name="f_max" value="<?php echo htmlspecialchars($filter_max_amt);?>" class="form-control">
        </div>
        <div class="col-sm-3" style="margin-top:8px;">
            <label class="filter-label">Customer</label>
            <select name="f_client" class="form-control select2">
                <option value="">All</option>
                <?php
                $fcq=mysqli_query($con,"SELECT account_id, account_title FROM accounts_chart WHERE branch_id='".intval($branch_id)."' ORDER BY account_title");
                while($fc=mysqli_fetch_assoc($fcq)){
                    $sel = ($filter_client!='' && $filter_client==$fc['account_id'])?'selected':'';
                    echo "<option value='".intval($fc['account_id'])."' $sel>".htmlspecialchars($fc['account_title'])."</option>";
                }
                ?>
            </select>
        </div>
        <div class="col-sm-5" style="margin-top:8px;">
            <label class="filter-label">Remarks Contains</label>
            <input type="text" name="f_remarks" value="<?php echo htmlspecialchars($filter_remarks);?>" class="form-control">
        </div>
        <div class="col-sm-4 d-flex align-items-end" style="margin-top:28px;">
            <button type="submit" class="btn btn-primary btn-sm" style="margin-right:5px;">Apply</button>
            <a href="sale_payment.php" class="btn btn-secondary btn-sm">Reset</a>
        </div>
    </form>
    <div class="small-note">Total Records: <?php echo $totalRows;?> | Page <?php echo $page;?> of <?php echo $totalPages;?></div>
</div>

<!-- LIST CARD -->
<div class="custom-card">
    <h4 style="margin-top:0;font-size:16px;font-weight:600;"><i class="fa fa-list"></i> Collection List</h4>
    <div class="table-responsive">
        <table class="table table-bordered custom-table" id="collection_table">
            <thead>
                <tr>
                    <th>Collection No.</th>
                    <th>Voucher No.</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th>Amount</th>
                    <th>Remarks</th>
                    <th style="width:80px;">Action</th>
                </tr>
            </thead>
            <tbody>
            <?php
            while($r = mysqli_fetch_assoc($listRun)){
                $alpha = 'CL-'.str_pad($r['sp_SrNo'],4,'0',STR_PAD_LEFT);
                echo "<tr>
                    <td class='text-center'>".htmlspecialchars($alpha)."</td>
                    <td>".htmlspecialchars($r['voucher_no'])."</td>
                    <td>".validate_date_display($r['sp_Date'])."</td>
                    <td>".htmlspecialchars($r['account_title'])."</td>
                    <td class='text-end'>".number_format($r['sp_Amount'],2)."</td>
                    <td>".htmlspecialchars($r['sp_Description'])."</td>
                    <td class='text-center'>
                        <a href='sale_payment.php?edit_id=".intval($r['sp_id'])."' class='btn btn-xs btn-warning'><i class='fa fa-edit'></i></a>
                        <a href='sale_payment.php?delete_id=".intval($r['sp_id'])."' onclick=\"return confirm('Delete this collection?');\" class='btn btn-xs btn-danger'><i class='fa fa-trash'></i></a>
                    </td>
                </tr>";
            }
            if($totalRows==0){
                echo "<tr><td colspan='7' class='text-center text-muted'>No records found.</td></tr>";
            }
            ?>
            </tbody>
        </table>
    </div>
    <!-- Pagination -->
    <div class="pagination">
        <?php
        $qs = $_GET; unset($qs['page']);
        $baseQS = http_build_query($qs);
        $linkBase = 'sale_payment.php'.($baseQS ? '?'.$baseQS.'&' : '?');
        if($page>1){
            echo "<a href='".$linkBase."page=".($page-1)."'>« Prev</a>";
        } else {
            echo "<span class='disabled'>« Prev</span>";
        }
        $window = 5;
        $start = max(1, $page-$window);
        $end   = min($totalPages, $page+$window);
        for($p=$start;$p<=$end;$p++){
            if($p==$page){
                echo "<span class='active'>$p</span>";
            } else {
                echo "<a href='".$linkBase."page=$p'>$p</a>";
            }
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
function checkParameters(){
    if($.trim($('#sp_Amount').val())=='' || $.trim($('#client_id').val())==''){
        alert('Please fill required fields');
        return false;
    }
}
</script>