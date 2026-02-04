<?php
include('sessionCheck.php');
include('connection.php');
include('functions.php');

$page_title = "Profit & Loss Report";

$session_branch_id = isset($_SESSION['branch_id']) ? (int)$_SESSION['branch_id'] : 0;
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
    $rows=[]; while($r=mysqli_fetch_assoc($res)){ $rows[]=$r; } mysqli_free_result($res); return $rows;
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

// Filters
$from = isset($_GET['from']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['from']) ? $_GET['from'] : date('Y-m-01');
$to   = isset($_GET['to'])   && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['to'])   ? $_GET['to']   : date('Y-m-d');
$selected_branch = isset($_GET['branch_id']) && (int)$_GET['branch_id'] > 0 ? (int)$_GET['branch_id'] : $session_branch_id;
if ($selected_branch <= 0) { $selected_branch = $session_branch_id; }

// Branches for dropdown (only show if more than one)
$branches = qall($con, "SELECT branch_id, branch_Name FROM adm_branch ORDER BY branch_Name");

// WHERE snippets
$where_sale_hdr = "branch_id={$selected_branch} AND s_Date>='{$from}' AND s_Date<='{$to}'";
$where_purch_hdr = "branch_id={$selected_branch} AND p_Date>='{$from}' AND p_Date<='{$to}'";
$where_sr_hdr = "branch_id={$selected_branch} AND sr_Date>='{$from}' AND sr_Date<='{$to}'";
$where_pr_hdr = "branch_id={$selected_branch} AND pr_Date>='{$from}' AND pr_Date<='{$to}'";
$where_exp = "branch_id={$selected_branch} AND expense_date>='{$from}' AND expense_date<='{$to}'";

// Summary (Accounting P&L)
$gross_sales     = qone($con, "SELECT IFNULL(SUM(s_TotalAmount),0) FROM cust_sale WHERE {$where_sale_hdr}", 0);
$sales_discount  = qone($con, "SELECT IFNULL(SUM(s_DiscountAmount),0) FROM cust_sale WHERE {$where_sale_hdr}", 0);
$sales_tax       = qone($con, "SELECT IFNULL(SUM(s_TaxAmount),0) FROM cust_sale WHERE {$where_sale_hdr}", 0);
$net_sales       = qone($con, "SELECT IFNULL(SUM(s_NetAmount),0) FROM cust_sale WHERE {$where_sale_hdr}", 0);

$sale_returns    = table_exists_local($con,'cust_salereturn') ? qone($con, "SELECT IFNULL(SUM(sr_NetAmount),0) FROM cust_salereturn WHERE {$where_sr_hdr}", 0) : 0;
$purchases       = qone($con, "SELECT IFNULL(SUM(p_NetAmount),0) FROM adm_purchase WHERE {$where_purch_hdr}", 0);
$purchase_returns= table_exists_local($con,'adm_purchasereturn') ? qone($con, "SELECT IFNULL(SUM(pr_NetAmount),0) FROM adm_purchasereturn WHERE {$where_pr_hdr}", 0) : 0;
$expenses        = table_exists_local($con,'adm_expenses') ? qone($con, "SELECT IFNULL(SUM(expense_amount),0) FROM adm_expenses WHERE {$where_exp}", 0) : 0;

// Accounting P&L logic (simple)
$gross_profit_acct = ($net_sales - $sale_returns) - ($purchases - $purchase_returns);
$net_profit_acct   = $gross_profit_acct - $expenses;

// Margin P&L using detail (COGS-based)
$detail_available = table_exists_local($con,'cust_sale_detail');
$margin_net_revenue = 0;
$margin_cogs = 0;
$margin_return_rev = 0;
$margin_return_cogs = 0;

if ($detail_available) {
    // Sales detail (net revenue from lines)
    $row = qone($con, "
        SELECT IFNULL(SUM(d.item_NetPrice),0)
        FROM cust_sale_detail d
        INNER JOIN cust_sale s ON s.s_id = d.s_id
        WHERE s.branch_id={$selected_branch} AND s.s_Date>='{$from}' AND s.s_Date<='{$to}'
    ", 0);
    $margin_net_revenue = $row;

    // COGS from sales detail
    $cogsRow = qone($con, "
        SELECT IFNULL(SUM(d.item_CostPrice * d.item_Qty),0)
        FROM cust_sale_detail d
        INNER JOIN cust_sale s ON s.s_id = d.s_id
        WHERE s.branch_id={$selected_branch} AND s.s_Date>='{$from}' AND s.s_Date<='{$to}'
    ", 0);
    $margin_cogs = $cogsRow;

    // Returns (if detail exists)
    if (table_exists_local($con,'cust_salereturn_detail') && table_exists_local($con,'cust_salereturn')) {
        $retRev = qone($con, "
            SELECT IFNULL(SUM(rd.item_NetAmount),0)
            FROM cust_salereturn_detail rd
            INNER JOIN cust_salereturn r ON r.sr_id = rd.sr_id
            WHERE r.branch_id={$selected_branch} AND r.sr_Date>='{$from}' AND r.sr_Date<='{$to}'
        ", 0);
        $margin_return_rev = $retRev;

        // Note: cust_salereturn_detail.item_CostPrice exists in your dump (int). Use it for return cost.
        $retCogs = qone($con, "
            SELECT IFNULL(SUM(rd.item_CostPrice * rd.item_Qty),0)
            FROM cust_salereturn_detail rd
            INNER JOIN cust_salereturn r ON r.sr_id = rd.sr_id
            WHERE r.branch_id={$selected_branch} AND r.sr_Date>='{$from}' AND r.sr_Date<='{$to}'
        ", 0);
        $margin_return_cogs = $retCogs;
    }
}

$gross_profit_margin = ($margin_net_revenue - $margin_return_rev) - ($margin_cogs - $margin_return_cogs);
// Expenses are not usually deducted in “gross” margin, but for a quick Net Profit (margin view):
$net_profit_margin = $gross_profit_margin - $expenses;

// CSV export (summary)
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    $fname = "profit_loss_".date('Ymd_His').".csv";
    header('Content-Disposition: attachment; filename="'.$fname.'"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['From',$from,'To',$to,'Branch',$selected_branch]);
    fputcsv($out, []);
    fputcsv($out, ['Accounting P&L']);
    fputcsv($out, ['Gross Sales',$gross_sales]);
    fputcsv($out, ['Sales Discount',$sales_discount]);
    fputcsv($out, ['Sales Tax',$sales_tax]);
    fputcsv($out, ['Net Sales',$net_sales]);
    fputcsv($out, ['Sale Returns', $sale_returns]);
    fputcsv($out, ['Purchases', $purchases]);
    fputcsv($out, ['Purchase Returns', $purchase_returns]);
    fputcsv($out, ['Expenses', $expenses]);
    fputcsv($out, ['Gross Profit (Accounting)', $gross_profit_acct]);
    fputcsv($out, ['Net Profit (Accounting)', $net_profit_acct]);
    fputcsv($out, []);
    fputcsv($out, ['Margin P&L (Detail-based)']);
    fputcsv($out, ['Line Net Revenue', $margin_net_revenue]);
    fputcsv($out, ['Return Revenue', $margin_return_rev]);
    fputcsv($out, ['COGS', $margin_cogs]);
    fputcsv($out, ['Return COGS', $margin_return_cogs]);
    fputcsv($out, ['Gross Profit (Margin)', $gross_profit_margin]);
    fputcsv($out, ['Net Profit (Margin)', $net_profit_margin]);
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
<?php $breadcrumbs["Reports"] = ""; $breadcrumbs["Profit & Loss"] = ""; include("inc/ribbon.php"); ?>

<style>
body{background:#f6f8fb;}
.report-card{background:#fff;border-radius:12px;box-shadow:0 2px 12px rgba(0,0,0,.06);padding:18px;margin-top:16px;border:1px solid #eef2f7;}
.report-title{font-size:18px;font-weight:800;color:#374151;margin-bottom:12px;}
.kpi{display:flex;flex-wrap:wrap;gap:10px}
.kpi .box{flex:1 1 220px;background:#f8fafc;border:1px solid #e5e7eb;border-radius:10px;padding:12px}
.kpi .box h5{margin:0 0 6px 0;font-size:12px;color:#6b7280;text-transform:uppercase;letter-spacing:.3px}
.kpi .box div{font-size:18px;font-weight:800;color:#111827}
.form-control{border-radius:6px!important;font-size:13px}
.btn-sm2{padding:6px 10px;font-size:12px;border-radius:6px}
.section-sub{font-size:13px;color:#6b7280;margin:2px 0 10px 0}
.hr{height:1px;background:#e5e7eb;margin:16px 0}
.text-green{color:#059669}
.text-blue{color:#2563eb}
.text-red{color:#b91c1c}
</style>

<div id="content">
<section id="widget-grid">
<div class="row">
<article class="col-sm-12 col-md-10 col-lg-8 col-lg-offset-2">

<div class="report-card">
  <div class="report-title"><i class="fa fa-balance-scale"></i> Profit &amp; Loss Report</div>
  <form class="row" method="get" action="profit_loss_report.php" style="margin-bottom:10px;">
      <div class="col-sm-3">
        <label class="small text-muted">From Date</label>
        <input type="date" name="from" value="<?php echo h($from);?>" class="form-control">
      </div>
      <div class="col-sm-3">
        <label class="small text-muted">To Date</label>
        <input type="date" name="to" value="<?php echo h($to);?>" class="form-control">
      </div>
      <?php if (count($branches) > 1) { ?>
      <div class="col-sm-4">
        <label class="small text-muted">Branch</label>
        <select name="branch_id" class="form-control">
          <?php foreach($branches as $b){
              $sel = ($selected_branch == (int)$b['branch_id']) ? 'selected' : '';
              echo "<option value='".(int)$b['branch_id']."' $sel>".h($b['branch_Name'])."</option>";
          } ?>
        </select>
      </div>
      <?php } ?>
      <div class="col-sm-2" style="display:flex;align-items:flex-end;gap:6px;">
        <button type="submit" class="btn btn-primary btn-sm2"><i class="fa fa-filter"></i> Apply</button>
        <a href="profit_loss_report.php?<?php $qs=$_GET; $qs['export']='csv'; echo h(http_build_query($qs)); ?>" class="btn btn-success btn-sm2"><i class="fa fa-download"></i> CSV</a>
        <button type="button" onclick="window.print();" class="btn btn-info btn-sm2"><i class="fa fa-print"></i> Print</button>
      </div>
  </form>

  <div class="section-sub">Accounting View</div>
  <div class="kpi">
    <div class="box"><h5>Gross Sales</h5><div><?php echo h($currency_symbol)." ".number_format($gross_sales,2);?></div></div>
    <div class="box"><h5>Sales Discount</h5><div><?php echo h($currency_symbol)." ".number_format($sales_discount,2);?></div></div>
    <div class="box"><h5>Sales Tax</h5><div><?php echo h($currency_symbol)." ".number_format($sales_tax,2);?></div></div>
    <div class="box"><h5>Net Sales</h5><div class="text-blue"><?php echo h($currency_symbol)." ".number_format($net_sales,2);?></div></div>
  </div>

  <div class="kpi" style="margin-top:10px">
    <div class="box"><h5>Sale Returns</h5><div><?php echo h($currency_symbol)." ".number_format($sale_returns,2);?></div></div>
    <div class="box"><h5>Purchases</h5><div><?php echo h($currency_symbol)." ".number_format($purchases,2);?></div></div>
    <div class="box"><h5>Purchase Returns</h5><div><?php echo h($currency_symbol)." ".number_format($purchase_returns,2);?></div></div>
    <div class="box"><h5>Expenses</h5><div class="text-red"><?php echo h($currency_symbol)." ".number_format($expenses,2);?></div></div>
  </div>

  <div class="hr"></div>

  <div class="kpi">
    <div class="box"><h5>Gross Profit (Accounting)</h5><div class="text-blue"><?php echo h($currency_symbol)." ".number_format($gross_profit_acct,2);?></div></div>
    <div class="box"><h5>Net Profit (Accounting)</h5><div class="text-green"><?php echo h($currency_symbol)." ".number_format($net_profit_acct,2);?></div></div>
  </div>

  <div class="hr"></div>

  <div class="section-sub">Margin View (Line-level, based on Cost)</div>
  <div class="kpi">
    <div class="box"><h5>Line Net Revenue</h5><div><?php echo h($currency_symbol)." ".number_format($margin_net_revenue,2);?></div></div>
    <div class="box"><h5>Return Revenue</h5><div><?php echo h($currency_symbol)." ".number_format($margin_return_rev,2);?></div></div>
    <div class="box"><h5>COGS</h5><div><?php echo h($currency_symbol)." ".number_format($margin_cogs,2);?></div></div>
    <div class="box"><h5>Return COGS</h5><div><?php echo h($currency_symbol)." ".number_format($margin_return_cogs,2);?></div></div>
  </div>

  <div class="kpi" style="margin-top:10px">
    <div class="box"><h5>Gross Profit (Margin)</h5><div class="text-blue"><?php echo h($currency_symbol)." ".number_format($gross_profit_margin,2);?></div></div>
    <div class="box"><h5>Net Profit (Margin)</h5><div class="text-green"><?php echo h($currency_symbol)." ".number_format($net_profit_margin,2);?></div></div>
  </div>

  <?php if (!$detail_available) { ?>
    <div class="section-sub" style="margin-top:12px;color:#9ca3af">
      Note: Line-level margin uses cust_sale_detail and cust_salereturn_detail. If these tables are empty or missing, only Accounting view is used.
    </div>
  <?php } ?>
</div>

</article>
</div>
</section>
</div>
</div>
<?php include ("inc/footer.php"); include ("inc/scripts.php"); ?>