<?php
include('sessionCheck.php');
include('connection.php');
include('functions.php');

$page_title = "Payable Report";

$branch_id = isset($_SESSION['branch_id']) ? (int)$_SESSION['branch_id'] : 0;
$currency_symbol = isset($_SESSION['currency_symbol']) && $_SESSION['currency_symbol'] !== '' ? $_SESSION['currency_symbol'] : 'Rs';

// Safe helpers
function qone($con, $sql, $default = 0){
    $res = mysqli_query($con, $sql);
    if ($res === false) { error_log("SQL error: ".mysqli_error($con)." -- ".$sql); return $default; }
    $row = mysqli_fetch_row($res);
    mysqli_free_result($res);
    return $row && isset($row[0]) ? (float)$row[0] : (float)$default;
}
function qall($con, $sql){
    $res = mysqli_query($con, $sql);
    if ($res === false) { error_log("SQL error: ".mysqli_error($con)." -- ".$sql); return []; }
    $rows = []; while($r = mysqli_fetch_assoc($res)){ $rows[] = $r; } mysqli_free_result($res); return $rows;
}
function h($s){ return htmlspecialchars((string)($s ?? ''), ENT_QUOTES, 'UTF-8'); }

// Filters
$as_of       = isset($_GET['as_of']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['as_of']) ? $_GET['as_of'] : date('Y-m-d');
$supplier_id = isset($_GET['supplier_id']) ? (int)$_GET['supplier_id'] : 0;
$min_balance = isset($_GET['min_balance']) && is_numeric($_GET['min_balance']) ? (float)$_GET['min_balance'] : 0;
$show_zero   = isset($_GET['show_zero']) ? (int)$_GET['show_zero'] : 0;

// Suppliers dropdown (Liability accounts act as suppliers)
$suppliers = qall($con, "
    SELECT account_id, account_title
    FROM accounts_chart
    WHERE branch_id={$branch_id} AND account_type='Liability'
    ORDER BY account_title
");

// Summary (as-of) per supplier
$sum_sql = "
SELECT
  c.account_id,
  c.account_title,
  COALESCE(pu.purchases,0)   AS purchases,
  COALESCE(pr.returns,0)     AS returns,
  COALESCE(py.paid,0)        AS paid,
  (COALESCE(pu.purchases,0) - COALESCE(pr.returns,0) - COALESCE(py.paid,0)) AS balance
FROM accounts_chart c
LEFT JOIN (
  SELECT sup_id, SUM(p_NetAmount) AS purchases
  FROM adm_purchase
  WHERE branch_id={$branch_id} AND p_Date <= '{$as_of}'
  GROUP BY sup_id
) pu ON pu.sup_id = c.account_id
LEFT JOIN (
  SELECT sup_id, SUM(pr_NetAmount) AS returns
  FROM adm_purchasereturn
  WHERE branch_id={$branch_id} AND pr_Date <= '{$as_of}'
  GROUP BY sup_id
) pr ON pr.sup_id = c.account_id
LEFT JOIN (
  SELECT sup_id, SUM(pp_Amount) AS paid
  FROM adm_purchase_payment
  WHERE branch_id={$branch_id} AND pp_Date <= '{$as_of}'
  GROUP BY sup_id
) py ON py.sup_id = c.account_id
WHERE c.branch_id={$branch_id} AND c.account_type='Liability'
";

if ($supplier_id > 0) {
    $sum_sql .= " AND c.account_id = {$supplier_id}";
}
$sum_sql .= " ORDER BY balance DESC, c.account_title ASC";

$summary_rows = qall($con, $sum_sql);

// Apply min_balance and zero-balance filter
$summary_rows = array_values(array_filter($summary_rows, function($r) use ($min_balance, $show_zero) {
    $bal = (float)$r['balance'];
    if (!$show_zero && abs($bal) < 0.00001) return false;
    if ($min_balance > 0 && $bal < $min_balance) return false;
    return true;
}));

// If a supplier is selected, prepare invoice-wise outstanding
$invoice_rows = [];
$unallocated_payments = 0.0;
$returns_asof = 0.0;
$note_unallocated = "";

if ($supplier_id > 0) {
    $inv_sql = "
        SELECT
            p.p_id,
            p.p_Number,
            p.p_Date,
            COALESCE(p.p_NetAmount,0) AS net_amount,
            (
                SELECT COALESCE(SUM(pp_Amount),0)
                FROM adm_purchase_payment pp
                WHERE pp.sup_id = p.sup_id
                  AND pp.branch_id = p.branch_id
                  AND pp.pp_Date <= '{$as_of}'
                  AND pp.p_id = p.p_id
            ) AS paid_linked
        FROM adm_purchase p
        WHERE p.branch_id={$branch_id}
          AND p.sup_id={$supplier_id}
          AND p.p_Date <= '{$as_of}'
        ORDER BY p.p_Date ASC, p.p_id ASC
    ";
    $invoice_rows = qall($con, $inv_sql);

    $sum_paid_linked = 0.0;
    foreach ($invoice_rows as &$r) {
        $net = (float)$r['net_amount'];
        $paid_linked = (float)$r['paid_linked'];
        $out = $net - $paid_linked;
        if ($out < 0) $out = 0;
        $r['outstanding'] = $out;
        $r['age_days'] = (int)((strtotime($as_of) - strtotime($r['p_Date'])) / 86400);
        $sum_paid_linked += $paid_linked;
    }
    unset($r);

    // Totals for payments and returns at supplier level (as-of)
    $total_paid_supplier = qone($con, "
        SELECT COALESCE(SUM(pp_Amount),0)
        FROM adm_purchase_payment
        WHERE branch_id={$branch_id}
          AND sup_id={$supplier_id}
          AND pp_Date <= '{$as_of}'
    ", 0);
    $returns_asof = qone($con, "
        SELECT COALESCE(SUM(pr_NetAmount),0)
        FROM adm_purchasereturn
        WHERE branch_id={$branch_id}
          AND sup_id={$supplier_id}
          AND pr_Date <= '{$as_of}'
    ", 0);

    $unallocated_payments = max($total_paid_supplier - $sum_paid_linked, 0.0);

    $note_unallocated = "";
    if ($unallocated_payments > 0.00001) {
        $note_unallocated .= "Note: ".h($currency_symbol)." ".number_format($unallocated_payments,2)." in payments are not linked to any specific purchase invoice; they reduce the supplier balance but are not shown against invoice-wise outstanding. ";
    }
    if ($returns_asof > 0.00001) {
        $note_unallocated .= "Also, purchase returns (".h($currency_symbol)." ".number_format($returns_asof,2).") are applied at supplier level and not displayed per invoice.";
    }
}

// CSV export (summary or invoice-wise when supplier selected)
if (isset($_GET['export']) && $_GET['export']==='csv') {
    header('Content-Type: text/csv; charset=utf-8');
    $fname = "payable_report_".date('Ymd_His').".csv";
    header('Content-Disposition: attachment; filename="'.$fname.'"');
    $out = fopen('php://output', 'w');

    if ($supplier_id > 0) {
        $sup_name = '';
        foreach ($suppliers as $s) { if ((int)$s['account_id'] === $supplier_id) { $sup_name = $s['account_title']; break; } }
        fputcsv($out, ['Supplier', $sup_name, 'As of', $as_of]);
        fputcsv($out, []);
        fputcsv($out, ['Date','Purchase #','Net Amount','Linked Paid','Outstanding','Age (days)']);
        foreach ($invoice_rows as $r) {
            fputcsv($out, [
                $r['p_Date'],
                $r['p_Number'],
                number_format((float)$r['net_amount'],2,'.',''),
                number_format((float)$r['paid_linked'],2,'.',''),
                number_format((float)$r['outstanding'],2,'.',''),
                (int)$r['age_days']
            ]);
        }
        if ($note_unallocated !== '') {
            fputcsv($out, []);
            fputcsv($out, ['Info', $note_unallocated]);
        }
    } else {
        fputcsv($out, ['As of', $as_of]);
        fputcsv($out, []);
        fputcsv($out, ['Supplier','Purchases','Returns','Payments','Balance']);
        foreach ($summary_rows as $r) {
            fputcsv($out, [
                $r['account_title'],
                number_format((float)$r['purchases'],2,'.',''),
                number_format((float)$r['returns'],2,'.',''),
                number_format((float)$r['paid'],2,'.',''),
                number_format((float)$r['balance'],2,'.','')
            ]);
        }
    }
    fclose($out);
    exit;
}

// UI includes
require_once ("inc/init.php");
require_once ("inc/config.ui.php");
include ("inc/header.php");
include ("inc/nav.php");
?>
<!-- ==========================CONTENT STARTS HERE ========================== -->
<div id="main" role="main">
<?php $breadcrumbs["Reports"] = ""; $breadcrumbs["Payable Report"] = ""; include("inc/ribbon.php"); ?>

<style>
body{background:#f6f8fb;}
.form-control{border-radius:6px!important;font-size:13px}
.report-card{background:#fff;border-radius:12px;box-shadow:0 2px 12px rgba(0,0,0,.06);padding:18px;margin-top:16px;border:1px solid #eef2f7;}
.report-title{font-size:18px;font-weight:800;color:#374151;margin-bottom:12px;}
.table thead th{background:#f1f5f9;font-weight:700;font-size:12px}
.table td{font-size:12px;vertical-align:middle}
.btn-sm2{padding:6px 10px;font-size:12px;border-radius:6px}
.filters .col-sm-2, .filters .col-sm-3, .filters .col-sm-4{margin-bottom:8px}
.kpi{display:flex;flex-wrap:wrap;gap:10px;margin:10px 0}
.kpi .box{flex:1 1 200px;background:#f8fafc;border:1px solid #e5e7eb;border-radius:10px;padding:12px}
.kpi .box h5{margin:0 0 4px 0;font-size:12px;color:#6b7280;text-transform:uppercase;letter-spacing:.3px}
.kpi .box div{font-size:18px;font-weight:800;color:#111827}
.note{background:#fff7ed;border:1px solid #fed7aa;color:#7c2d12;border-radius:8px;padding:10px;font-size:12px;margin-top:10px}
</style>

<div id="content">
<section id="widget-grid">
<div class="row">
<article class="col-sm-12 col-md-10 col-lg-10 col-lg-offset-1">

<div class="report-card">
  <div class="report-title"><i class="fa fa-credit-card"></i> Payable Report</div>
  <form class="row filters" method="get" action="payable_report.php">
      <div class="col-sm-4">
        <label class="small text-muted">Supplier</label>
        <select name="supplier_id" class="form-control select2" style="width:100%">
          <option value="0">All Suppliers</option>
          <?php foreach($suppliers as $s){
              $sel = ($supplier_id==(int)$s['account_id'])?'selected':'';
              echo "<option value='".(int)$s['account_id']."' {$sel}>".h($s['account_title'])."</option>";
          } ?>
        </select>
      </div>
      <div class="col-sm-2">
        <label class="small text-muted">As of Date</label>
        <input type="date" name="as_of" value="<?php echo h($as_of);?>" class="form-control">
      </div>
      <div class="col-sm-2">
        <label class="small text-muted">Min Balance</label>
        <input type="number" step="0.01" name="min_balance" value="<?php echo h($min_balance);?>" class="form-control" placeholder="e.g. 1000">
      </div>
      <div class="col-sm-2">
        <label class="small text-muted">Show Zero</label>
        <select name="show_zero" class="form-control">
          <option value="0" <?php echo $show_zero? '':'selected'; ?>>No</option>
          <option value="1" <?php echo $show_zero? 'selected':''; ?>>Yes</option>
        </select>
      </div>
      <div class="col-sm-2" style="display:flex;gap:6px;align-items:flex-end;">
        <button type="submit" class="btn btn-primary btn-sm2"><i class="fa fa-filter"></i> Apply</button>
        <a href="payable_report.php?<?php $qs=$_GET; $qs['export']='csv'; echo h(http_build_query($qs)); ?>" class="btn btn-success btn-sm2"><i class="fa fa-download"></i> CSV</a>
        <button type="button" onclick="window.print();" class="btn btn-info btn-sm2"><i class="fa fa-print"></i> Print</button>
      </div>
  </form>

  <?php if ($supplier_id === 0) { ?>
    <div class="table-responsive" style="margin-top:8px;">
      <table class="table table-bordered">
        <thead>
          <tr>
            <th>Supplier</th>
            <th class="text-right" style="width:140px;">Purchases</th>
            <th class="text-right" style="width:140px;">Returns</th>
            <th class="text-right" style="width:140px;">Payments</th>
            <th class="text-right" style="width:160px;">Balance</th>
          </tr>
        </thead>
        <tbody>
          <?php
          if (count($summary_rows) > 0) {
              $tot_pur = $tot_ret = $tot_paid = $tot_bal = 0.0;
              foreach ($summary_rows as $r) {
                  $tot_pur  += (float)$r['purchases'];
                  $tot_ret  += (float)$r['returns'];
                  $tot_paid += (float)$r['paid'];
                  $tot_bal  += (float)$r['balance'];
                  echo "<tr>
                      <td>".h($r['account_title'])."</td>
                      <td class='text-right'>".h($currency_symbol)." ".number_format((float)$r['purchases'],2)."</td>
                      <td class='text-right'>".h($currency_symbol)." ".number_format((float)$r['returns'],2)."</td>
                      <td class='text-right'>".h($currency_symbol)." ".number_format((float)$r['paid'],2)."</td>
                      <td class='text-right'><strong>".h($currency_symbol)." ".number_format((float)$r['balance'],2)."</strong></td>
                  </tr>";
              }
              echo "<tr>
                  <td class='text-right'><strong>Totals</strong></td>
                  <td class='text-right'><strong>".h($currency_symbol)." ".number_format($tot_pur,2)."</strong></td>
                  <td class='text-right'><strong>".h($currency_symbol)." ".number_format($tot_ret,2)."</strong></td>
                  <td class='text-right'><strong>".h($currency_symbol)." ".number_format($tot_paid,2)."</strong></td>
                  <td class='text-right'><strong>".h($currency_symbol)." ".number_format($tot_bal,2)."</strong></td>
              </tr>";
          } else {
              echo "<tr><td colspan='5' class='text-center text-muted'>No suppliers found for the selected criteria.</td></tr>";
          }
          ?>
        </tbody>
      </table>
    </div>
  <?php } else {
      $sum_row = count($summary_rows) ? $summary_rows[0] : ['purchases'=>0,'returns'=>0,'paid'=>0,'balance'=>0,'account_title'=>''];
  ?>
    <div class="kpi">
      <div class="box">
        <h5>Supplier</h5>
        <div><?php echo h($sum_row['account_title']); ?></div>
      </div>
      <div class="box">
        <h5>Purchases (as of)</h5>
        <div><?php echo h($currency_symbol)." ".number_format((float)$sum_row['purchases'],2); ?></div>
      </div>
      <div class="box">
        <h5>Payments (as of)</h5>
        <div><?php echo h($currency_symbol)." ".number_format((float)$sum_row['paid'],2); ?></div>
      </div>
      <div class="box">
        <h5>Returns (as of)</h5>
        <div><?php echo h($currency_symbol)." ".number_format((float)$sum_row['returns'],2); ?></div>
      </div>
      <div class="box">
        <h5>Balance (as of)</h5>
        <div><?php echo h($currency_symbol)." ".number_format((float)$sum_row['balance'],2); ?></div>
      </div>
    </div>

    <?php if ($note_unallocated !== '') { ?>
      <div class="note"><?php echo h($note_unallocated); ?></div>
    <?php } ?>

    <div class="table-responsive" style="margin-top:8px;">
      <table class="table table-bordered">
        <thead>
          <tr>
            <th style="width:110px;">Date</th>
            <th style="width:150px;">Purchase #</th>
            <th class="text-right" style="width:140px;">Net Amount</th>
            <th class="text-right" style="width:130px;">Linked Paid</th>
            <th class="text-right" style="width:150px;">Outstanding</th>
            <th class="text-center" style="width:100px;">Age (days)</th>
            <th style="width:90px;">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php
          if (count($invoice_rows) > 0) {
              $tot_net = $tot_paid = $tot_out = 0.0;
              foreach ($invoice_rows as $r) {
                  $tot_net  += (float)$r['net_amount'];
                  $tot_paid += (float)$r['paid_linked'];
                  $tot_out  += (float)$r['outstanding'];
                  echo "<tr>
                      <td>".h(sum_date_formate($r['p_Date']))."</td>
                      <td>".h($r['p_Number'])."</td>
                      <td class='text-right'>".h($currency_symbol)." ".number_format((float)$r['net_amount'],2)."</td>
                      <td class='text-right'>".h($currency_symbol)." ".number_format((float)$r['paid_linked'],2)."</td>
                      <td class='text-right'><strong>".h($currency_symbol)." ".number_format((float)$r['outstanding'],2)."</strong></td>
                      <td class='text-center'>".(int)$r['age_days']."</td>
                      <td><a class='btn btn-xs btn-primary' href='purchase_add?id=".(int)$r['p_id']."'><i class='fa fa-eye'></i></a></td>
                  </tr>";
              }
              echo "<tr>
                  <td colspan='2' class='text-right'><strong>Totals</strong></td>
                  <td class='text-right'><strong>".h($currency_symbol)." ".number_format($tot_net,2)."</strong></td>
                  <td class='text-right'><strong>".h($currency_symbol)." ".number_format($tot_paid,2)."</strong></td>
                  <td class='text-right'><strong>".h($currency_symbol)." ".number_format($tot_out,2)."</strong></td>
                  <td class='text-center' colspan='2'>&nbsp;</td>
              </tr>";
          } else {
              echo "<tr><td colspan='7' class='text-center text-muted'>No purchase invoices found up to the selected As-of date.</td></tr>";
          }
          ?>
        </tbody>
      </table>
    </div>
  <?php } ?>
</div>

</article>
</div>
</section>
</div>
</div>

<?php include ("inc/footer.php"); ?>
<?php include ("inc/scripts.php"); ?>
<script src="<?php echo ASSETS_URL;?>/js/plugin/select2/select2.min.js"></script>
<script>
$(function(){ $('.select2').select2({width:'100%'}); });
</script>