<?php
include('sessionCheck.php');
include('connection.php');
include('functions.php');

$page_title = "Purchase Report";

function is_valid_ymd($d){
    return (bool)preg_match('/^\d{4}-\d{2}-\d{2}$/', $d);
}

$branch_id   = isset($_SESSION['branch_id']) ? intval($_SESSION['branch_id']) : 0;
$supplier_id = isset($_GET['supplier_id']) ? intval($_GET['supplier_id']) : 0;
$from        = isset($_GET['from']) ? trim($_GET['from']) : '';
$to          = isset($_GET['to'])   ? trim($_GET['to'])   : '';

if ($from !== '' && !is_valid_ymd($from)) $from = '';
if ($to   !== '' && !is_valid_ymd($to))   $to   = '';
if ($from !== '' && $to !== '' && $from > $to) { $tmp=$from; $from=$to; $to=$tmp; }

$where = "p.branch_id=".intval($branch_id);
if ($supplier_id > 0) {
    $where .= " AND p.sup_id=".intval($supplier_id);
}
if ($from !== '') {
    $where .= " AND p.p_Date>='".mysqli_real_escape_string($con, $from)."'";
}
if ($to !== '') {
    $where .= " AND p.p_Date<='".mysqli_real_escape_string($con, $to)."'";
}

/* Aggregate payments per purchase for paid amount */
$paidJoin = "
    LEFT JOIN (
        SELECT p_id, branch_id, SUM(COALESCE(pp_Amount,0)) AS paid_amount
        FROM adm_purchase_payment
        GROUP BY p_id, branch_id
    ) pay ON pay.p_id = p.p_id AND pay.branch_id = p.branch_id
";

/* CSV Export */
if (isset($_GET['export']) && $_GET['export']==='csv') {
    $csvSql = "
        SELECT
            p.p_Date,
            p.p_Number,
            COALESCE(ac.account_title,'') AS supplier_name,
            COALESCE(p.p_TotalAmount,0)   AS total_amount,
            COALESCE(p.p_DiscountPrice,0) AS discount_amount,
            COALESCE(p.p_NetAmount,0)     AS net_amount,
            COALESCE(pay.paid_amount,0)   AS paid_amount
        FROM adm_purchase p
        LEFT JOIN accounts_chart ac ON ac.account_id = p.sup_id
        $paidJoin
        WHERE $where
        ORDER BY p.p_Date DESC, p.p_id DESC
    ";
    $res = mysqli_query($con, $csvSql);
    header('Content-Type: text/csv; charset=utf-8');
    $fname = "purchase_report_".date('Ymd_His').".csv";
    header('Content-Disposition: attachment; filename="'.$fname.'"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Date','Purchase No.','Supplier','Total Amount','Discount','Net Amount','Paid Amount']);
    if ($res) {
        while($row = mysqli_fetch_assoc($res)){
            fputcsv($out, [
                $row['p_Date'],
                $row['p_Number'],
                $row['supplier_name'],
                number_format((float)$row['total_amount'],2,'.',''),
                number_format((float)$row['discount_amount'],2,'.',''),
                number_format((float)$row['net_amount'],2,'.',''),
                number_format((float)$row['paid_amount'],2,'.',''),
            ]);
        }
    }
    fclose($out);
    exit;
}

/* Totals */
$totSql = "
    SELECT
        COUNT(*) AS total_invoices,
        SUM(COALESCE(p.p_TotalAmount,0))   AS total_total,
        SUM(COALESCE(p.p_DiscountPrice,0)) AS total_discount,
        SUM(COALESCE(p.p_NetAmount,0))     AS total_net,
        SUM(COALESCE(pay.paid_amount,0))   AS total_paid
    FROM adm_purchase p
    $paidJoin
    WHERE $where
";
$totRes = mysqli_query($con, $totSql);
$totRow = $totRes ? mysqli_fetch_assoc($totRes) : ['total_invoices'=>0,'total_total'=>0,'total_discount'=>0,'total_net'=>0,'total_paid'=>0];

/* List */
$listSql = "
    SELECT
        p.p_id,
        p.p_Number,
        p.p_Date,
        COALESCE(ac.account_title,'') AS supplier_name,
        COALESCE(p.p_TotalAmount,0)   AS total_amount,
        COALESCE(p.p_DiscountPrice,0) AS discount_amount,
        COALESCE(p.p_NetAmount,0)     AS net_amount,
        COALESCE(pay.paid_amount,0)   AS paid_amount
    FROM adm_purchase p
    LEFT JOIN accounts_chart ac ON ac.account_id = p.sup_id
    $paidJoin
    WHERE $where
    ORDER BY p.p_Date DESC, p.p_id DESC
";
$listRes = mysqli_query($con, $listSql);

/* Suppliers dropdown */
$suppliers = [];
$supQ = "
    SELECT account_id, account_title
    FROM accounts_chart
    WHERE branch_id=".intval($branch_id)." AND (account_type='Liability' OR account_type='Supplier')
    ORDER BY account_title
";
$supRes = mysqli_query($con, $supQ);
if ($supRes) { while($r = mysqli_fetch_assoc($supRes)) $suppliers[] = $r; }

/* UI includes */
require_once ("inc/init.php");
require_once ("inc/config.ui.php");
include ("inc/header.php");
include ("inc/nav.php");
?>
<div id="main" role="main">
<?php $breadcrumbs["Purchase"] = "purchase_list"; $breadcrumbs["Purchase Report"] = ""; include("inc/ribbon.php"); ?>

<style>
body{background:#f6f8fb;}
.form-control{border-radius:6px!important;font-size:13px;}
.report-card{background:#fff;border-radius:10px;box-shadow:0 2px 12px rgba(0,0,0,.06);padding:18px;margin-top:16px;}
.report-title{font-size:18px;font-weight:700;color:#374151;margin-bottom:12px;}
.summary{display:flex;flex-wrap:wrap;gap:10px;margin-top:10px}
.summary .box{flex:1 1 150px;background:#f8fafc;border:1px solid #e5e7eb;border-radius:8px;padding:10px}
.summary .box h5{margin:0 0 4px 0;font-size:12px;color:#6b7280;text-transform:uppercase;letter-spacing:.3px}
.summary .box div{font-size:16px;font-weight:700;color:#111827}
.table thead th{background:#f1f5f9;font-weight:700;font-size:12px;}
.table td{font-size:12px;vertical-align:middle;}
.btn-sm2{padding:6px 10px;font-size:12px;border-radius:6px}
.filters .col-sm-2, .filters .col-sm-3{margin-bottom:8px}
</style>

<div id="content">
<section id="widget-grid">
<div class="row">
<article class="col-sm-12 col-md-10 col-lg-10 col-lg-offset-1">

<div class="report-card">
  <div class="report-title"><i class="fa fa-file-text-o"></i> Purchase Report</div>
  <form class="row filters" method="get" action="purchase_report.php">
      <div class="col-sm-3">
          <label class="small text-muted">Supplier</label>
          <select name="supplier_id" class="form-control select2" style="width:100%">
              <option value="0">All Suppliers</option>
              <?php foreach($suppliers as $s){
                $sel = ($supplier_id==intval($s['account_id'])) ? 'selected' : '';
                echo "<option value='".intval($s['account_id'])."' $sel>".htmlspecialchars($s['account_title'])."</option>";
              } ?>
          </select>
      </div>
      <div class="col-sm-2">
          <label class="small text-muted">From Date</label>
          <input type="date" name="from" value="<?php echo htmlspecialchars($from);?>" class="form-control">
      </div>
      <div class="col-sm-2">
          <label class="small text-muted">To Date</label>
          <input type="date" name="to" value="<?php echo htmlspecialchars($to);?>" class="form-control">
      </div>
      <div class="col-sm-5" style="display:flex;gap:6px;align-items:flex-end;">
          <button type="submit" class="btn btn-primary btn-sm2"><i class="fa fa-filter"></i> Apply</button>
          <a href="purchase_report.php" class="btn btn-default btn-sm2">Reset</a>
          <a href="purchase_report.php?<?php $qs=$_GET; $qs['export']='csv'; echo http_build_query($qs); ?>" class="btn btn-success btn-sm2"><i class="fa fa-download"></i> Export CSV</a>
          <button type="button" onclick="window.print();" class="btn btn-info btn-sm2"><i class="fa fa-print"></i> Print</button>
      </div>
  </form>

  <div class="summary">
      <div class="box">
        <h5>Invoices</h5>
        <div><?php echo number_format((int)($totRow['total_invoices']??0));?></div>
      </div>
      <div class="box">
        <h5>Total Amount</h5>
        <div><?php echo number_format((float)($totRow['total_total']??0),2);?></div>
      </div>
      <div class="box">
        <h5>Discount</h5>
        <div><?php echo number_format((float)($totRow['total_discount']??0),2);?></div>
      </div>
      <div class="box">
        <h5>Net Amount</h5>
        <div><?php echo number_format((float)($totRow['total_net']??0),2);?></div>
      </div>
      <div class="box">
        <h5>Paid</h5>
        <div><?php echo number_format((float)($totRow['total_paid']??0),2);?></div>
      </div>
  </div>
</div>

<div class="report-card">
  <div class="table-responsive">
    <table class="table table-bordered">
      <thead>
        <tr>
          <th style="width:110px;">Date</th>
          <th style="width:140px;">Purchase No.</th>
          <th>Supplier</th>
          <th class="text-end" style="width:130px;">Total</th>
          <th class="text-end" style="width:110px;">Discount</th>
          <th class="text-end" style="width:130px;">Net</th>
          <th class="text-end" style="width:130px;">Paid</th>
          <th style="width:90px;">Action</th>
        </tr>
      </thead>
      <tbody>
      <?php
      if ($listRes && mysqli_num_rows($listRes)>0) {
        while($r = mysqli_fetch_assoc($listRes)){
            $dateShow = sum_date_formate($r['p_Date']);
            echo "<tr>
                <td>".htmlspecialchars($dateShow)."</td>
                <td>".htmlspecialchars($r['p_Number'])."</td>
                <td>".htmlspecialchars($r['supplier_name'])."</td>
                <td class='text-end'>".number_format((float)$r['total_amount'],2)."</td>
                <td class='text-end'>".number_format((float)$r['discount_amount'],2)."</td>
                <td class='text-end'>".number_format((float)$r['net_amount'],2)."</td>
                <td class='text-end'>".number_format((float)$r['paid_amount'],2)."</td>
                <td class='text-center'>
                    <a class='btn btn-xs btn-primary' href='purchase_add?id=".intval($r['p_id'])."'><i class='fa fa-eye'></i></a>
                </td>
            </tr>";
        }
      } else {
        echo "<tr><td colspan='8' class='text-center text-muted'>No records found.</td></tr>";
      }
      ?>
      </tbody>
    </table>
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
$(function(){ $('.select2').select2({width:'100%'}); });
</script>