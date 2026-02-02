<?php
include('sessionCheck.php');
include('connection.php');
include('functions.php');

$page_title = "Sample / Claim Report";

// UI includes
require_once ("inc/init.php");
require_once ("inc/config.ui.php");
include ("inc/header.php");
include ("inc/nav.php");

// Session scope
$branch_id = (int)($_SESSION['branch_id'] ?? 0);

// Helpers
function h($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }
function qall($con,$sql){
    $res = mysqli_query($con,$sql);
    if ($res === false) { error_log('SQL ERR: '.mysqli_error($con).' -- '.$sql); return []; }
    $rows=[]; while($r=mysqli_fetch_assoc($res)){ $rows[]=$r; } mysqli_free_result($res); return $rows;
}
function qone($con,$sql,$def=0){
    $res = mysqli_query($con,$sql);
    if ($res === false) { error_log('SQL ERR: '.mysqli_error($con).' -- '.$sql); return $def; }
    $r = mysqli_fetch_row($res); mysqli_free_result($res);
    return $r ? (float)$r[0] : $def;
}
function qstr($con,$sql,$def=''){
    $res = mysqli_query($con,$sql);
    if ($res === false) { return $def; }
    $r = mysqli_fetch_row($res); mysqli_free_result($res);
    return $r ? (string)$r[0] : $def;
}

// Filters
$from = isset($_GET['from']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['from']) ? $_GET['from'] : date('Y-m-01');
$to   = isset($_GET['to'])   && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['to'])   ? $_GET['to']   : date('Y-m-d');
$type = isset($_GET['type']) && in_array($_GET['type'], ['all','sample','claim'], true) ? $_GET['type'] : 'all';
$cust = isset($_GET['cust']) ? (int)$_GET['cust'] : 0;
$item = isset($_GET['item']) ? (int)$_GET['item'] : 0;
$group= isset($_GET['group']) && in_array($_GET['group'], ['doc','customer','item'], true) ? $_GET['group'] : 'doc';
$export = isset($_GET['export']) && $_GET['export']==='csv';
$debug = isset($_GET['debug']) && $_GET['debug']=='1';

// Dropdowns
$customers = qall($con, "SELECT account_id, account_title FROM accounts_chart WHERE status='active' ORDER BY account_title");
$items = qall($con, "SELECT item_id, item_Code, item_Name FROM adm_item ORDER BY item_Name, item_Code");

// WHERE clause
$where = [];
$where[] = "s.branch_id={$branch_id}";
$where[] = "s.s_SaleMode IN ('sample','claim')";
$where[] = "s.s_Date BETWEEN '{$from}' AND '{$to}'";
if ($type !== 'all') $where[] = "s.s_SaleMode='{$type}'";
if ($cust > 0) $where[] = "s.client_id={$cust}";
if ($item > 0) $where[] = "d.item_id={$item}";
$WHERE = implode(' AND ', $where);

// Build SQL by grouping
$sql = '';
$title_suffix = '';
if ($group === 'doc') {
    $sql = "
    SELECT
      s.s_id,
      s.s_Number,
      s.s_Date,
      s.s_SaleMode,
      ac.account_title AS customer,
      s.s_Remarks,
      COUNT(d.sd_id) AS item_lines,
      COALESCE(SUM(d.item_Qty),0) AS qty_total
    FROM cust_sale s
    INNER JOIN cust_sale_detail d ON d.s_id = s.s_id
    LEFT JOIN accounts_chart ac ON ac.account_id = s.client_id
    WHERE {$WHERE}
    GROUP BY s.s_id, s.s_Number, s.s_Date, s.s_SaleMode, ac.account_title, s.s_Remarks
    ORDER BY s.s_Date DESC, s.s_id DESC
    ";
    $title_suffix = 'Grouped by Document';
} elseif ($group === 'customer') {
    $sql = "
    SELECT
      s.client_id,
      ac.account_title AS customer,
      COUNT(DISTINCT s.s_id) AS docs_count,
      SUM(CASE WHEN s.s_SaleMode='sample' THEN d.item_Qty ELSE 0 END) AS qty_sample,
      SUM(CASE WHEN s.s_SaleMode='claim'  THEN d.item_Qty ELSE 0 END) AS qty_claim,
      COALESCE(SUM(d.item_Qty),0) AS qty_total
    FROM cust_sale s
    INNER JOIN cust_sale_detail d ON d.s_id = s.s_id
    LEFT JOIN accounts_chart ac ON ac.account_id = s.client_id
    WHERE {$WHERE}
    GROUP BY s.client_id, ac.account_title
    ORDER BY ac.account_title ASC
    ";
    $title_suffix = 'Summary by Customer';
} else { // group === 'item'
    $sql = "
    SELECT
      d.item_id,
      i.item_Code,
      i.item_Name,
      COUNT(DISTINCT s.s_id) AS docs_count,
      SUM(CASE WHEN s.s_SaleMode='sample' THEN d.item_Qty ELSE 0 END) AS qty_sample,
      SUM(CASE WHEN s.s_SaleMode='claim'  THEN d.item_Qty ELSE 0 END) AS qty_claim,
      COALESCE(SUM(d.item_Qty),0) AS qty_total
    FROM cust_sale s
    INNER JOIN cust_sale_detail d ON d.s_id = s.s_id
    LEFT JOIN adm_item i ON i.item_id = d.item_id
    WHERE {$WHERE}
    GROUP BY d.item_id, i.item_Code, i.item_Name
    ORDER BY i.item_Name ASC, i.item_Code ASC
    ";
    $title_suffix = 'Summary by Item';
}

// Run query
$rows = qall($con, $sql);

// Totals for header KPIs
$total_docs = 0;
$total_qty = 0.0;
$total_sample = 0.0;
$total_claim = 0.0;

if ($group === 'doc') {
    $total_docs = count($rows);
    foreach ($rows as $r) {
        $total_qty += (float)$r['qty_total'];
    }
    // For sample/claim split we need quick side queries:
    $total_sample = qone($con, "
        SELECT COALESCE(SUM(d.item_Qty),0) 
        FROM cust_sale s INNER JOIN cust_sale_detail d ON d.s_id=s.s_id
        WHERE {$WHERE} AND s.s_SaleMode='sample'
    ", 0);
    $total_claim = qone($con, "
        SELECT COALESCE(SUM(d.item_Qty),0) 
        FROM cust_sale s INNER JOIN cust_sale_detail d ON d.s_id=s.s_id
        WHERE {$WHERE} AND s.s_SaleMode='claim'
    ", 0);
} else {
    // In grouped summaries we already have split columns
    $doc_seen = [];
    foreach ($rows as $r) {
        $total_qty += (float)$r['qty_total'];
        $total_sample += (float)$r['qty_sample'];
        $total_claim += (float)$r['qty_claim'];
        $total_docs += (int)$r['docs_count'];
    }
}

// CSV export
if ($export) {
    header('Content-Type: text/csv; charset=utf-8');
    $fname = "sample_claim_report_{$group}_".date('Ymd_His').".csv";
    header('Content-Disposition: attachment; filename="'.$fname.'"');
    $out = fopen('php://output', 'w');

    // Header
    fputcsv($out, ['Sample/Claim Report - '.$title_suffix]);
    fputcsv($out, ['From',$from,'To',$to,'Type',$type,'Customer',$cust>0?$cust:'All','Item',$item>0?$item:'All']);
    fputcsv($out, []);
    if ($group === 'doc') {
        fputcsv($out, ['Date','Doc #','Type','Customer','Item Lines','Total Qty','Remarks']);
        foreach ($rows as $r) {
            fputcsv($out, [
                $r['s_Date'],
                $r['s_Number'],
                $r['s_SaleMode']==='sample'?'Sample':'Claim',
                $r['customer'],
                (int)$r['item_lines'],
                number_format((float)$r['qty_total'],2,'.',''),
                $r['s_Remarks']
            ]);
        }
    } elseif ($group === 'customer') {
        fputcsv($out, ['Customer','Documents','Qty (Sample)','Qty (Claim)','Qty (Total)']);
        foreach ($rows as $r) {
            fputcsv($out, [
                $r['customer'],
                (int)$r['docs_count'],
                number_format((float)$r['qty_sample'],2,'.',''),
                number_format((float)$r['qty_claim'],2,'.',''),
                number_format((float)$r['qty_total'],2,'.',''),
            ]);
        }
    } else { // item
        fputcsv($out, ['Item Code','Item Name','Documents','Qty (Sample)','Qty (Claim)','Qty (Total)']);
        foreach ($rows as $r) {
            fputcsv($out, [
                $r['item_Code'],
                $r['item_Name'],
                (int)$r['docs_count'],
                number_format((float)$r['qty_sample'],2,'.',''),
                number_format((float)$r['qty_claim'],2,'.',''),
                number_format((float)$r['qty_total'],2,'.',''),
            ]);
        }
    }
    fputcsv($out, []);
    fputcsv($out, ['Totals','','Docs',$total_docs,'Sample Qty',number_format($total_sample,2,'.',''),'Claim Qty',number_format($total_claim,2,'.',''),'Total Qty',number_format($total_qty,2,'.','')]);
    fclose($out);
    exit;
}
?>
<div id="main" role="main">
<?php $breadcrumbs["Reports"] = ""; $breadcrumbs["Sample / Claim Report"] = ""; include("inc/ribbon.php"); ?>

<style>
body{background:#f6f8fb;}
.report-card{background:#fff;border-radius:12px;box-shadow:0 2px 12px rgba(0,0,0,.06);padding:18px;margin-top:16px;border:1px solid #eef2f7;}
.report-title{font-size:18px;font-weight:800;color:#374151;margin-bottom:12px;}
.kpi{display:flex;flex-wrap:wrap;gap:10px;margin:10px 0}
.kpi .box{flex:1 1 220px;background:#f8fafc;border:1px solid #e5e7eb;border-radius:10px;padding:12px}
.kpi .box h5{margin:0 0 4px 0;font-size:12px;color:#6b7280;text-transform:uppercase;letter-spacing:.3px}
.kpi .box div{font-size:18px;font-weight:800;color:#111827}
.table thead th{background:#f1f5f9;font-weight:700;font-size:12px}
.table td{font-size:12px;vertical-align:middle}
.btn-sm2{padding:6px 10px;font-size:12px;border-radius:6px}
.filters .col-sm-2, .filters .col-sm-3, .filters .col-sm-4{margin-bottom:8px}
.badge-type{display:inline-block;padding:3px 8px;border-radius:10px;font-size:12px}
.badge-sample{background:#eef6ff;color:#0b67c2;border:1px solid #cfe6ff}
.badge-claim{background:#fff4ed;color:#b03500;border:1px solid #ffd8bf}
@media print{
  .no-print{display:none!important}
  body{background:#fff}
  .report-card{box-shadow:none;border:none;margin:0;padding:0}
}
</style>

<div id="content">
<section id="widget-grid">
<div class="row">
<article class="col-sm-12 col-md-11 col-lg-10 col-lg-offset-1">
<div class="report-card">
  <div class="report-title"><i class="fa fa-file-text-o"></i> Sample / Claim Report <span style="font-weight:400;color:#6b7280;">(<?= h($title_suffix) ?>)</span></div>

  <form class="row filters no-print" method="get" action="claim_sample_report.php">
      <div class="col-sm-2">
        <label class="small text-muted">From</label>
        <input type="date" name="from" value="<?=h($from)?>" class="form-control" required>
      </div>
      <div class="col-sm-2">
        <label class="small text-muted">To</label>
        <input type="date" name="to" value="<?=h($to)?>" class="form-control" required>
      </div>
      <div class="col-sm-2">
        <label class="small text-muted">Type</label>
        <select name="type" class="form-control">
          <option value="all" <?= $type==='all'?'selected':''; ?>>All</option>
          <option value="sample" <?= $type==='sample'?'selected':''; ?>>Sample</option>
          <option value="claim" <?= $type==='claim'?'selected':''; ?>>Claim</option>
        </select>
      </div>
      <div class="col-sm-3">
        <label class="small text-muted">Customer</label>
        <select name="cust" class="form-control">
          <option value="0">All</option>
          <?php foreach($customers as $c): ?>
            <option value="<?=$c['account_id']?>" <?= $cust==$c['account_id']?'selected':''; ?>><?=h($c['account_title'])?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-sm-3">
        <label class="small text-muted">Item</label>
        <select name="item" class="form-control">
          <option value="0">All</option>
          <?php foreach($items as $it): ?>
            <option value="<?=$it['item_id']?>" <?= $item==$it['item_id']?'selected':''; ?>><?=h($it['item_Code'].' - '.$it['item_Name'])?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-sm-3">
        <label class="small text-muted">Group By</label>
        <select name="group" class="form-control">
          <option value="doc" <?= $group==='doc'?'selected':''; ?>>Document</option>
          <option value="customer" <?= $group==='customer'?'selected':''; ?>>Customer</option>
          <option value="item" <?= $group==='item'?'selected':''; ?>>Item</option>
        </select>
      </div>
      <div class="col-sm-9" style="display:flex;gap:6px;align-items:flex-end;justify-content:flex-end;">
        <button type="submit" class="btn btn-primary btn-sm2"><i class="fa fa-filter"></i> Apply</button>
        <?php $qs = $_GET; $qs['export']='csv'; ?>
        <a href="claim_sample_report.php?<?=h(http_build_query($qs))?>" class="btn btn-success btn-sm2"><i class="fa fa-download"></i> CSV</a>
        <button type="button" onclick="window.print();" class="btn btn-info btn-sm2"><i class="fa fa-print"></i> Print</button>
      </div>
  </form>

  <div class="kpi">
    <div class="box">
      <h5>Total Docs</h5>
      <div><?= number_format((float)$total_docs,0) ?></div>
    </div>
    <div class="box">
      <h5>Total Qty</h5>
      <div><?= number_format((float)$total_qty,2) ?></div>
    </div>
    <div class="box">
      <h5>Sample Qty</h5>
      <div><?= number_format((float)$total_sample,2) ?></div>
    </div>
    <div class="box">
      <h5>Claim Qty</h5>
      <div><?= number_format((float)$total_claim,2) ?></div>
    </div>
  </div>

  <div class="table-responsive" style="margin-top:8px;">
    <table class="table table-bordered">
      <thead>
        <?php if ($group==='doc') { ?>
          <tr>
            <th style="width:110px;">Date</th>
            <th style="width:130px;">Doc #</th>
            <th style="width:90px;">Type</th>
            <th>Customer</th>
            <th style="width:120px;" class="text-center">Item Lines</th>
            <th style="width:120px;" class="text-center">Total Qty</th>
            <th>Remarks</th>
          </tr>
        <?php } elseif ($group==='customer') { ?>
          <tr>
            <th>Customer</th>
            <th style="width:120px;" class="text-center">Documents</th>
            <th style="width:140px;" class="text-center">Qty (Sample)</th>
            <th style="width:140px;" class="text-center">Qty (Claim)</th>
            <th style="width:140px;" class="text-center">Qty (Total)</th>
          </tr>
        <?php } else { ?>
          <tr>
            <th style="width:160px;">Item Code</th>
            <th>Item Name</th>
            <th style="width:120px;" class="text-center">Documents</th>
            <th style="width:140px;" class="text-center">Qty (Sample)</th>
            <th style="width:140px;" class="text-center">Qty (Claim)</th>
            <th style="width:140px;" class="text-center">Qty (Total)</th>
          </tr>
        <?php } ?>
      </thead>
      <tbody>
        <?php if (count($rows)===0) { ?>
          <tr><td colspan="7" class="text-center text-muted">No data for selected filters.</td></tr>
        <?php } else { 
          if ($group==='doc') {
            foreach ($rows as $r) { ?>
              <tr>
                <td><?= h($r['s_Date']) ?></td>
                <td><?= h($r['s_Number']) ?></td>
                <td><?php if ($r['s_SaleMode']==='sample') { ?>
                    <span class="badge-type badge-sample">Sample</span>
                  <?php } else { ?>
                    <span class="badge-type badge-claim">Claim</span>
                  <?php } ?>
                </td>
                <td><?= h($r['customer']) ?></td>
                <td class="text-center"><?= (int)$r['item_lines'] ?></td>
                <td class="text-center"><?= number_format((float)$r['qty_total'],2) ?></td>
                <td><?= h($r['s_Remarks']) ?></td>
              </tr>
          <?php }
          } elseif ($group==='customer') {
            foreach ($rows as $r) { ?>
              <tr>
                <td><?= h($r['customer']) ?></td>
                <td class="text-center"><?= (int)$r['docs_count'] ?></td>
                <td class="text-center"><?= number_format((float)$r['qty_sample'],2) ?></td>
                <td class="text-center"><?= number_format((float)$r['qty_claim'],2) ?></td>
                <td class="text-center"><?= number_format((float)$r['qty_total'],2) ?></td>
              </tr>
          <?php }
          } else { // item ?>
            <?php foreach ($rows as $r) { ?>
              <tr>
                <td><?= h($r['item_Code']) ?></td>
                <td><?= h($r['item_Name']) ?></td>
                <td class="text-center"><?= (int)$r['docs_count'] ?></td>
                <td class="text-center"><?= number_format((float)$r['qty_sample'],2) ?></td>
                <td class="text-center"><?= number_format((float)$r['qty_claim'],2) ?></td>
                <td class="text-center"><?= number_format((float)$r['qty_total'],2) ?></td>
              </tr>
            <?php } ?>
          <?php } ?>
      <?php } ?>
      </tbody>
      <?php if (count($rows)>0) { ?>
        <tfoot>
          <tr>
            <?php if ($group==='doc') { ?>
              <th colspan="4" class="text-right">Totals</th>
              <th class="text-center"><?= number_format((float)$total_docs,0) ?></th>
              <th class="text-center"><?= number_format((float)$total_qty,2) ?></th>
              <th>
                Sample: <?= number_format((float)$total_sample,2) ?> | 
                Claim: <?= number_format((float)$total_claim,2) ?>
              </th>
            <?php } else { ?>
              <th class="text-right"><?= $group==='item'?'Totals':'Totals' ?></th>
              <th class="text-center"><?= number_format((float)$total_docs,0) ?></th>
              <th class="text-center"><?= number_format((float)$total_sample,2) ?></th>
              <th class="text-center"><?= number_format((float)$total_claim,2) ?></th>
              <th class="text-center"><?= number_format((float)$total_qty,2) ?></th>
            <?php } ?>
          </tr>
        </tfoot>
      <?php } ?>
    </table>
  </div>

  <?php if ($debug) { ?>
    <div style="white-space:pre-wrap;background:#fff7ed;border:1px dashed #f59e0b;padding:8px;border-radius:6px;margin-top:8px">
      <strong>DEBUG</strong>
      <?= "\nActive DB: ".h(qstr($con, "SELECT DATABASE()")) ?>
      <?= "\nWHERE: ".$WHERE ?>
      <?= "\n\nSQL:\n".$sql ?>
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