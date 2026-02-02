<?php
include('sessionCheck.php');
include('connection.php');
include('functions.php');

$page_title = "Item Ledger Report";
$currency_symbol = isset($_SESSION['currency_symbol']) && $_SESSION['currency_symbol'] !== '' ? $_SESSION['currency_symbol'] : 'Rs';
$debug = isset($_GET['debug']) && $_GET['debug'] == '1';

// Helpers
function qone($con, $sql, $default = 0){
    $res = mysqli_query($con, $sql);
    if ($res === false) { return $default; }
    $row = mysqli_fetch_row($res);
    mysqli_free_result($res);
    return ($row && isset($row[0])) ? (float)$row[0] : (float)$default;
}
function qstr($con, $sql, $default = ''){
    $res = mysqli_query($con, $sql);
    if ($res === false) { return $default; }
    $row = mysqli_fetch_row($res);
    mysqli_free_result($res);
    return ($row && isset($row[0])) ? (string)$row[0] : (string)$default;
}
function qall($con, $sql){
    $res = mysqli_query($con, $sql);
    if ($res === false) { return []; }
    $rows = [];
    while ($r = mysqli_fetch_assoc($res)) { $rows[] = $r; }
    mysqli_free_result($res);
    return $rows;
}
function table_exists_local($con, $name){
    $name = mysqli_real_escape_string($con, $name);
    $res = mysqli_query($con, "SHOW TABLES LIKE '{$name}'");
    if ($res === false) return false;
    $ok = mysqli_num_rows($res) > 0;
    mysqli_free_result($res);
    return $ok;
}
function h($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

// Filters (defaults)
$from = isset($_GET['from']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['from']) ? $_GET['from'] : date('Y-m-01');
$to   = isset($_GET['to'])   && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['to'])   ? $_GET['to']   : date('Y-m-d');
$item_id = isset($_GET['item_id']) ? (int)$_GET['item_id'] : 0;

// Items dropdown
$items = qall($con, "SELECT item_id, item_Code, item_Name FROM adm_item ORDER BY item_Name, item_Code");

// Opening date (day before From)
$open_date = date('Y-m-d', strtotime($from.' -1 day'));

// Opening qty components (<= open_date) using COALESCE(header_date, detail_date)
$qty_purch_before = ($item_id>0) ? qone($con, "
    SELECT IFNULL(SUM(pd.item_Qty),0)
    FROM adm_purchase_detail pd
    INNER JOIN adm_purchase p ON p.p_id = pd.p_id
    WHERE pd.item_id = {$item_id} AND COALESCE(p.p_Date, pd.pd_Date) <= '{$open_date}'
", 0) : 0;

$qty_sale_before = ($item_id>0) ? qone($con, "
    SELECT IFNULL(SUM(sd.item_Qty),0)
    FROM cust_sale_detail sd
    INNER JOIN cust_sale s ON s.s_id = sd.s_id
    WHERE sd.item_id = {$item_id} AND COALESCE(s.s_Date, sd.sd_Date) <= '{$open_date}'
", 0) : 0;

$opening_qty = $qty_purch_before - $qty_sale_before;

// Build UNION for in-range rows (only Purchases and Sales first)
$unionParts = [];
if ($item_id > 0) {
    // Purchases (In)
    $unionParts[] = "
    SELECT
      COALESCE(p.p_Date, pd.pd_Date) AS t_date,
      'Purchase' AS t_type,
      p.p_Number AS ref_no,
      COALESCE(ac.account_title,'') AS party,
      pd.item_Qty AS qty_in,
      0 AS qty_out,
      pd.item_Rate AS rate,
      COALESCE(pd.item_NetAmount, pd.item_Rate * pd.item_Qty) AS amount,
      pd.pd_id AS sort_id,
      1 AS t_rank
    FROM adm_purchase_detail pd
    INNER JOIN adm_purchase p ON p.p_id = pd.p_id
    LEFT JOIN accounts_chart ac ON ac.account_id = p.sup_id
    WHERE pd.item_id={$item_id} AND COALESCE(p.p_Date, pd.pd_Date) BETWEEN '{$from}' AND '{$to}'
    ";

    // Sales (Out)
    $unionParts[] = "
    SELECT
      COALESCE(s.s_Date, sd.sd_Date) AS t_date,
      'Sale' AS t_type,
      s.s_Number AS ref_no,
      COALESCE(ac.account_title,'') AS party,
      0 AS qty_in,
      sd.item_Qty AS qty_out,
      CASE WHEN sd.item_Qty<>0 THEN COALESCE(sd.item_NetPrice,
           (COALESCE(sd.item_SalePrice,0)*sd.item_Qty) - COALESCE(sd.item_discount_amount_per_item, COALESCE(sd.item_DiscountPrice,0))*sd.item_Qty + COALESCE(sd.item_SaleExtraAmount,0)
      )/sd.item_Qty ELSE 0 END AS rate,
      COALESCE(sd.item_NetPrice,
           (COALESCE(sd.item_SalePrice,0)*sd.item_Qty) - COALESCE(sd.item_discount_amount_per_item, COALESCE(sd.item_DiscountPrice,0))*sd.item_Qty + COALESCE(sd.item_SaleExtraAmount,0)
      ) AS amount,
      sd.sd_id AS sort_id,
      3 AS t_rank
    FROM cust_sale_detail sd
    INNER JOIN cust_sale s ON s.s_id = sd.s_id
    LEFT JOIN accounts_chart ac ON ac.account_id = s.client_id
    WHERE sd.item_id={$item_id} AND COALESCE(s.s_Date, sd.sd_Date) BETWEEN '{$from}' AND '{$to}'
    ";
}

$ledgerRows = [];
$last_union_sql = '';
$union_err = '';

if ($item_id > 0 && count($unionParts) > 0) {
    $last_union_sql = implode("\nUNION ALL\n", $unionParts) . "\nORDER BY t_date ASC, t_rank ASC, sort_id ASC";
    $res = mysqli_query($con, $last_union_sql);
    if ($res === false) {
        $union_err = mysqli_error($con);
    } else {
        while($r = mysqli_fetch_assoc($res)){ $ledgerRows[] = $r; }
        mysqli_free_result($res);
    }
}

// Debug stats
$dbg = [];
if ($debug && $item_id > 0) {
    $dbg['active_db'] = qstr($con, "SELECT DATABASE()");
    $dbg['opening'] = [
        'purchases_before' => $qty_purch_before,
        'sales_before'     => $qty_sale_before,
    ];
    $dbg['rows_in_range'] = [];
    $dbg['rows_in_range']['purchases'] = (int)qone($con, "SELECT COUNT(*) FROM adm_purchase_detail pd INNER JOIN adm_purchase p ON p.p_id=pd.p_id WHERE pd.item_id={$item_id} AND COALESCE(p.p_Date, pd.pd_Date) BETWEEN '{$from}' AND '{$to}'", 0);
    $dbg['rows_in_range']['sales']     = (int)qone($con, "SELECT COUNT(*) FROM cust_sale_detail sd INNER JOIN cust_sale s ON s.s_id=sd.s_id WHERE sd.item_id={$item_id} AND COALESCE(s.s_Date, sd.sd_Date) BETWEEN '{$from}' AND '{$to}'", 0);
    $dbg['union_error'] = $union_err;
}

// Closing qty
$running_qty = $opening_qty;
foreach ($ledgerRows as $r) {
    $running_qty += (float)$r['qty_in'] - (float)$r['qty_out'];
}
$closing_qty = $running_qty;

// Item header info
$item_info = null;
if ($item_id > 0) {
    $r = qall($con, "SELECT item_Code, item_Name FROM adm_item WHERE item_id={$item_id} LIMIT 1");
    if ($r) $item_info = $r[0];
}

// CSV export
if (isset($_GET['export']) && $_GET['export']==='csv' && $item_id > 0) {
    header('Content-Type: text/csv; charset=utf-8');
    $fname = "item_ledger_{$item_id}_".date('Ymd_His').".csv";
    header('Content-Disposition: attachment; filename="'.$fname.'"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Item', ($item_info ? $item_info['item_Code'].' - '.$item_info['item_Name'] : $item_id)]);
    fputcsv($out, ['From',$from,'To',$to,'Branch','All']);
    fputcsv($out, ['Opening Qty',number_format($opening_qty,2,'.','')]);
    fputcsv($out, []);
    fputcsv($out, ['Date','Type','Ref #','Party','Qty In','Qty Out','Rate','Amount','Running Qty']);
    $rq = $opening_qty;
    foreach ($ledgerRows as $r) {
        $rq += (float)$r['qty_in'] - (float)$r['qty_out'];
        fputcsv($out, [
            $r['t_date'], $r['t_type'], $r['ref_no'], $r['party'],
            number_format((float)$r['qty_in'],2,'.',''),
            number_format((float)$r['qty_out'],2,'.',''),
            number_format((float)$r['rate'],2,'.',''),
            number_format((float)$r['amount'],2,'.',''),
            number_format((float)$rq,2,'.','')
        ]);
    }
    fputcsv($out, []);
    fputcsv($out, ['Closing Qty',number_format($rq,2,'.','')]);
    fclose($out);
    exit;
}

// UI includes
require_once ("inc/init.php");
require_once ("inc/config.ui.php");
include ("inc/header.php");
include ("inc/nav.php");
?>
<div id="main" role="main">
<?php $breadcrumbs["Inventory"] = ""; $breadcrumbs["Item Ledger"] = ""; include("inc/ribbon.php"); ?>

<style>
body{background:#f6f8fb;}
.form-control{border-radius:6px!important;font-size:13px}
.report-card{background:#fff;border-radius:12px;box-shadow:0 2px 12px rgba(0,0,0,.06);padding:18px;margin-top:16px;border:1px solid #eef2f7;}
.report-title{font-size:18px;font-weight:800;color:#374151;margin-bottom:12px;}
.kpi{display:flex;flex-wrap:wrap;gap:10px;margin:10px 0}
.kpi .box{flex:1 1 200px;background:#f8fafc;border:1px solid #e5e7eb;border-radius:10px;padding:12px}
.kpi .box h5{margin:0 0 4px 0;font-size:12px;color:#6b7280;text-transform:uppercase;letter-spacing:.3px}
.kpi .box div{font-size:18px;font-weight:800;color:#111827}
.table thead th{background:#f1f5f9;font-weight:700;font-size:12px}
.table td{font-size:12px;vertical-align:middle}
.btn-sm2{padding:6px 10px;font-size:12px;border-radius:6px}
.filters .col-sm-2, .filters .col-sm-3, .filters .col-sm-4{margin-bottom:8px}
.debug{white-space:pre-wrap;background:#fff7ed;border:1px dashed #f59e0b;padding:8px;border-radius:6px;margin-top:8px}
</style>

<div id="content">
<section id="widget-grid">
<div class="row">
<article class="col-sm-12 col-md-10 col-lg-10 col-lg-offset-1">
<div class="report-card">
  <div class="report-title"><i class="fa fa-list-alt"></i> Item Ledger</div>
  <form class="row filters" method="get" action="item_ledger_report.php">
      <div class="col-sm-4">
        <label class="small text-muted">Item</label>
        <select name="item_id" class="form-control" required>
          <option value="">Select item...</option>
          <?php foreach($items as $it){ $sel = ($item_id==(int)$it['item_id'])?'selected':''; ?>
            <option value="<?=(int)$it['item_id']?>" <?=$sel?>><?=h($it['item_Code'].' - '.$it['item_Name'])?></option>
          <?php } ?>
        </select>
      </div>
      <div class="col-sm-2">
        <label class="small text-muted">From</label>
        <input type="date" name="from" value="<?=h($from)?>" class="form-control" required>
      </div>
      <div class="col-sm-2">
        <label class="small text-muted">To</label>
        <input type="date" name="to" value="<?=h($to)?>" class="form-control" required>
      </div>
      <div class="col-sm-4" style="display:flex;gap:6px;align-items:flex-end;justify-content:flex-end;">
        <button type="submit" class="btn btn-primary btn-sm2"><i class="fa fa-filter"></i> Apply</button>
        <?php if($item_id>0){ ?>
          <a href="item_ledger_report.php?<?php $qs=$_GET; $qs['export']='csv'; echo h(http_build_query($qs)); ?>" class="btn btn-success btn-sm2"><i class="fa fa-download"></i> CSV</a>
          <button type="button" onclick="window.print();" class="btn btn-info btn-sm2"><i class="fa fa-print"></i> Print</button>
        <?php } ?>
      </div>
  </form>

  <?php if ($item_id > 0) { ?>
    <div class="kpi">
      <div class="box">
        <h5>Item</h5>
        <div><?= h(($item_info ? $item_info['item_Code'].' - '.$item_info['item_Name'] : '')) ?></div>
      </div>
      <div class="box">
        <h5>Opening Qty</h5>
        <div><?= number_format($opening_qty,2) ?></div>
      </div>
      <div class="box">
        <h5>Closing Qty</h5>
        <div><?= number_format($closing_qty,2) ?></div>
      </div>
    </div>

    <div class="table-responsive" style="margin-top:8px;">
      <table class="table table-bordered">
        <thead>
          <tr>
            <th style="width:110px;">Date</th>
            <th style="width:130px;">Type</th>
            <th style="width:140px;">Ref #</th>
            <th>Party</th>
            <th class="text-center" style="width:100px;">Qty In</th>
            <th class="text-center" style="width:100px;">Qty Out</th>
            <th class="text-right" style="width:110px;">Rate</th>
            <th class="text-right" style="width:130px;">Amount</th>
            <th class="text-center" style="width:120px;">Running Qty</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td colspan="8" class="text-right"><strong>Opening Balance</strong></td>
            <td class="text-center"><strong><?= number_format($opening_qty,2) ?></strong></td>
          </tr>
          <?php
          $rq = $opening_qty;
          if (count($ledgerRows) > 0){
              foreach ($ledgerRows as $r){
                  $rq += (float)$r['qty_in'] - (float)$r['qty_out'];
                  echo "<tr>
                      <td>".h($r['t_date'])."</td>
                      <td>".h($r['t_type'])."</td>
                      <td>".h($r['ref_no'])."</td>
                      <td>".h($r['party'])."</td>
                      <td class='text-center'>".number_format((float)$r['qty_in'],2)."</td>
                      <td class='text-center'>".number_format((float)$r['qty_out'],2)."</td>
                      <td class='text-right'>".number_format((float)$r['rate'],2)."</td>
                      <td class='text-right'>".number_format((float)$r['amount'],2)."</td>
                      <td class='text-center'>".number_format((float)$rq,2)."</td>
                  </tr>";
              }
              echo "<tr>
                  <td colspan='8' class='text-right'><strong>Closing Balance</strong></td>
                  <td class='text-center'><strong>".number_format((float)$rq,2)."</strong></td>
              </tr>";
          } else {
              echo "<tr><td colspan='9' class='text-center text-muted'>
                  No movement in selected period.
                  <br>Tip: From = 2025-09-01, To = 2025-09-22 for PROC001; 2025-09-01..2025-09-30 for PROC002.
              </td></tr>";
          }
          ?>
        </tbody>
      </table>

      <?php if ($debug) { ?>
        <div class="debug">
          <strong>DEBUG</strong>
          <?php
            echo "\\nActive DB: ".h($dbg['active_db'] ?? '');
            echo "\\nOpening up to {$open_date}: ".h(json_encode($dbg['opening'] ?? [], JSON_PRETTY_PRINT));
            echo "\\nRows in range {$from}..{$to}: ".h(json_encode($dbg['rows_in_range'] ?? [], JSON_PRETTY_PRINT));
            if (!empty($union_err)) {
                echo "\\nUNION ERROR: ".h($union_err);
            }
            if (!empty($last_union_sql)) {
                echo "\\n\\nUNION SQL:\\n".h($last_union_sql);
            }
          ?>
        </div>
      <?php } ?>
    </div>
  <?php } else { ?>
    <div class="text-muted" style="margin-top:8px;">Please select an item and date range, then click Apply.</div>
  <?php } ?>
</div>
</article>
</div>
</section>
</div>

<?php include ("inc/footer.php"); ?>
<?php include ("inc/scripts.php"); ?>