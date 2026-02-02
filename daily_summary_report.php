<?php
include('sessionCheck.php');
include('connection.php');
include('functions.php');

$page_title = "Daily Summary Report";

$branch_id = isset($_SESSION['branch_id']) ? (int)$_SESSION['branch_id'] : 0;
$currency_symbol = isset($_SESSION['currency_symbol']) && $_SESSION['currency_symbol'] !== '' ? $_SESSION['currency_symbol'] : 'Rs';

// Helpers
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

// Filter (defaults to today)
$the_date = isset($_GET['date']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Flags for optional tables
$has_sr  = table_exists_local($con, 'cust_salereturn');
$has_srd = table_exists_local($con, 'cust_salereturn_detail');
$has_pr  = table_exists_local($con, 'adm_purchasereturn');
$has_exp = table_exists_local($con, 'adm_expenses');

// SALES (header-level)
$sales_total_amount = qone($con, "SELECT IFNULL(SUM(s_TotalAmount),0) FROM cust_sale WHERE branch_id={$branch_id} AND s_Date='{$the_date}'", 0);
$sales_discount     = qone($con, "SELECT IFNULL(SUM(s_DiscountAmount),0) FROM cust_sale WHERE branch_id={$branch_id} AND s_Date='{$the_date}'", 0);
$sales_tax          = qone($con, "SELECT IFNULL(SUM(s_TaxAmount),0) FROM cust_sale WHERE branch_id={$branch_id} AND s_Date='{$the_date}'", 0);
$sales_net          = qone($con, "SELECT IFNULL(SUM(s_NetAmount),0) FROM cust_sale WHERE branch_id={$branch_id} AND s_Date='{$the_date}'", 0);
$sales_paid_hdr     = qone($con, "SELECT IFNULL(SUM(s_PaidAmount),0) FROM cust_sale WHERE branch_id={$branch_id} AND s_Date='{$the_date}'", 0);
$sales_invoices     = qone($con, "SELECT COUNT(*) FROM cust_sale WHERE branch_id={$branch_id} AND s_Date='{$the_date}'", 0);

// SALES RETURNS (header-level)
$sales_return_net   = $has_sr ? qone($con, "SELECT IFNULL(SUM(sr_NetAmount),0) FROM cust_salereturn WHERE branch_id={$branch_id} AND sr_Date='{$the_date}'", 0) : 0;
$sales_return_cnt   = $has_sr ? qone($con, "SELECT COUNT(*) FROM cust_salereturn WHERE branch_id={$branch_id} AND sr_Date='{$the_date}'", 0) : 0;

// PURCHASES (header-level)
$purchase_net       = qone($con, "SELECT IFNULL(SUM(p_NetAmount),0) FROM adm_purchase WHERE branch_id={$branch_id} AND p_Date='{$the_date}'", 0);
$purchase_invoices  = qone($con, "SELECT COUNT(*) FROM adm_purchase WHERE branch_id={$branch_id} AND p_Date='{$the_date}'", 0);

// PURCHASE RETURNS (header-level)
$purchase_return_net = $has_pr ? qone($con, "SELECT IFNULL(SUM(pr_NetAmount),0) FROM adm_purchasereturn WHERE branch_id={$branch_id} AND pr_Date='{$the_date}'", 0) : 0;

// COLLECTIONS & PAYMENTS
$customer_receipts  = qone($con, "SELECT IFNULL(SUM(sp_Amount),0) FROM adm_sale_payment WHERE branch_id={$branch_id} AND sp_Date='{$the_date}'", 0);
$supplier_payments  = qone($con, "SELECT IFNULL(SUM(pp_Amount),0) FROM adm_purchase_payment WHERE branch_id={$branch_id} AND pp_Date='{$the_date}'", 0);

// EXPENSES
$expenses_amount    = $has_exp ? qone($con, "SELECT IFNULL(SUM(expense_amount),0) FROM adm_expenses WHERE branch_id={$branch_id} AND expense_date='{$the_date}'", 0) : 0;

// GROSS PROFIT (detail-based for the day): (Sales Rev - COGS) - (Return Rev - Return COGS)
$sales_rev_lines = qone($con, "
  SELECT IFNULL(SUM(d.item_NetPrice),0)
  FROM cust_sale_detail d
  INNER JOIN cust_sale s ON s.s_id = d.s_id
  WHERE s.branch_id={$branch_id} AND s.s_Date='{$the_date}'
", 0);
$sales_cogs_lines = qone($con, "
  SELECT IFNULL(SUM(d.item_CostPrice * d.item_Qty),0)
  FROM cust_sale_detail d
  INNER JOIN cust_sale s ON s.s_id = d.s_id
  WHERE s.branch_id={$branch_id} AND s.s_Date='{$the_date}'
", 0);
$return_rev_lines = ($has_srd && $has_sr) ? qone($con, "
  SELECT IFNULL(SUM(rd.item_NetPrice),0)
  FROM cust_salereturn_detail rd
  INNER JOIN cust_salereturn r ON r.sr_id = rd.sr_id
  WHERE r.branch_id={$branch_id} AND r.sr_Date='{$the_date}'
", 0) : 0;
$return_cogs_lines = ($has_srd && $has_sr) ? qone($con, "
  SELECT IFNULL(SUM(rd.item_CostPrice * rd.item_Qty),0)
  FROM cust_salereturn_detail rd
  INNER JOIN cust_salereturn r ON r.sr_id = rd.sr_id
  WHERE r.branch_id={$branch_id} AND r.sr_Date='{$the_date}'
", 0) : 0;

$gross_profit = ($sales_rev_lines - $sales_cogs_lines) - ($return_rev_lines - $return_cogs_lines);
$gp_margin_pct = ($sales_rev_lines > 0) ? ($gross_profit / $sales_rev_lines * 100.0) : 0.0;

// Payment mix from customer receipts today (by sp_Type if used) — optional simple group by
$payment_mix = qall($con, "
  SELECT COALESCE(sp_Type,'N/A') AS mode, SUM(sp_Amount) AS amount
  FROM adm_sale_payment
  WHERE branch_id={$branch_id} AND sp_Date='{$the_date}'
  GROUP BY sp_Type
  ORDER BY amount DESC
");

// Top items sold today
$top_items = qall($con, "
  SELECT COALESCE(i.item_Name, CONCAT('Item #', d.item_id)) AS item_name, SUM(d.item_Qty) AS qty
  FROM cust_sale_detail d
  INNER JOIN cust_sale s ON s.s_id = d.s_id
  LEFT JOIN adm_item i ON i.item_id = d.item_id
  WHERE s.branch_id={$branch_id} AND s.s_Date='{$the_date}'
  GROUP BY d.item_id
  ORDER BY qty DESC
  LIMIT 10
");

// Today's invoices list
$today_sales = qall($con, "
  SELECT s.s_id, s.s_Number, s.client_id, s.s_NetAmount, s.s_PaidAmount, s.s_Date,
         COALESCE(c.account_title,'') AS customer_name
  FROM cust_sale s
  LEFT JOIN accounts_chart c ON c.account_id = s.client_id
  WHERE s.branch_id={$branch_id} AND s.s_Date='{$the_date}'
  ORDER BY s.s_id DESC
");

// Today's purchases list
$today_purchases = qall($con, "
  SELECT p.p_id, p.p_Number, p.sup_id, p.p_NetAmount, p.p_Date,
         COALESCE(c.account_title,'') AS supplier_name
  FROM adm_purchase p
  LEFT JOIN accounts_chart c ON c.account_id = p.sup_id
  WHERE p.branch_id={$branch_id} AND p.p_Date='{$the_date}'
  ORDER BY p.p_id DESC
");

// CSV export (summary)
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    $fname = "daily_summary_{$the_date}_" . date('Ymd_His') . ".csv";
    header('Content-Disposition: attachment; filename="'.$fname.'"');
    $out = fopen('php://output', 'w');

    fputcsv($out, ['Daily Summary', $the_date, 'Branch', $branch_id]);
    fputcsv($out, []);
    fputcsv($out, ['Sales']);
    fputcsv($out, ['Invoices', (int)$sales_invoices]);
    fputcsv($out, ['Total Amount', number_format($sales_total_amount,2,'.','')]);
    fputcsv($out, ['Discount', number_format($sales_discount,2,'.','')]);
    fputcsv($out, ['Tax', number_format($sales_tax,2,'.','')]);
    fputcsv($out, ['Net Sales', number_format($sales_net,2,'.','')]);
    fputcsv($out, ['Sales Paid (header)', number_format($sales_paid_hdr,2,'.','')]);
    if ($has_sr) {
        fputcsv($out, ['Sale Returns (Net)', number_format($sales_return_net,2,'.','')]);
    }
    fputcsv($out, []);
    fputcsv($out, ['Purchases']);
    fputcsv($out, ['Purchase Bills', (int)$purchase_invoices]);
    fputcsv($out, ['Purchase Net', number_format($purchase_net,2,'.','')]);
    if ($has_pr) {
        fputcsv($out, ['Purchase Returns (Net)', number_format($purchase_return_net,2,'.','')]);
    }
    fputcsv($out, []);
    fputcsv($out, ['Collections & Payments']);
    fputcsv($out, ['Customer Receipts', number_format($customer_receipts,2,'.','')]);
    fputcsv($out, ['Supplier Payments', number_format($supplier_payments,2,'.','')]);
    if ($has_exp) {
        fputcsv($out, ['Expenses', number_format($expenses_amount,2,'.','')]);
    }
    fputcsv($out, []);
    fputcsv($out, ['Gross Profit']);
    fputcsv($out, ['Sales Revenue (lines)', number_format($sales_rev_lines,2,'.','')]);
    fputcsv($out, ['COGS (lines)', number_format($sales_cogs_lines,2,'.','')]);
    if ($has_srd && $has_sr) {
        fputcsv($out, ['Return Revenue (lines)', number_format($return_rev_lines,2,'.','')]);
        fputcsv($out, ['Return COGS (lines)', number_format($return_cogs_lines,2,'.','')]);
    }
    fputcsv($out, ['Gross Profit', number_format($gross_profit,2,'.','')]);
    fputcsv($out, ['GP %', number_format($gp_margin_pct,2,'.','')]);

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
<?php $breadcrumbs["Reports"] = ""; $breadcrumbs["Daily Summary"] = ""; include("inc/ribbon.php"); ?>

<style>
body{background:#f6f8fb;}
.form-control{border-radius:6px!important;font-size:13px}
.report-card{background:#fff;border:1px solid #eef2f7;border-radius:12px;box-shadow:0 2px 12px rgba(0,0,0,.06);padding:18px;margin-top:16px;}
.report-title{font-size:18px;font-weight:800;color:#374151;margin-bottom:12px;}
.kpi{display:flex;flex-wrap:wrap;gap:10px}
.kpi .box{flex:1 1 220px;background:#f8fafc;border:1px solid #e5e7eb;border-radius:10px;padding:12px}
.kpi .box h5{margin:0 0 6px 0;font-size:12px;color:#6b7280;text-transform:uppercase;letter-spacing:.3px}
.kpi .box div{font-size:18px;font-weight:800;color:#111827}
.chart-card{background:#fff;border-radius:12px;padding:16px;box-shadow:0 2px 12px rgba(0,0,0,.06);border:1px solid #eef2f7;margin-top:16px;}
.section-title{font-size:16px;font-weight:700;color:#374151;margin:0 0 8px 0}
.table thead th{background:#f1f5f9;font-weight:700;font-size:12px}
.table td{font-size:12px;vertical-align:middle}
.btn-sm2{padding:6px 10px;font-size:12px;border-radius:6px}
.meta{font-size:12px;color:#6b7280;margin-bottom:8px}
.text-green{color:#059669}
.text-red{color:#b91c1c}
.text-blue{color:#2563eb}
</style>

<div id="content">
  <section id="widget-grid">
    <div class="row">
      <article class="col-sm-12 col-md-10 col-lg-10 col-lg-offset-1">
        <div class="report-card">
          <div class="report-title"><i class="fa fa-calendar-check-o"></i> Daily Summary</div>
          <div class="meta">Date: <strong><?php echo h($the_date); ?></strong> • Printed: <strong><?php echo date('d-m-Y h:i A'); ?></strong></div>
          <form class="row" method="get" action="daily_summary_report.php" style="margin-bottom:10px;">
            <div class="col-sm-3">
              <label class="small text-muted">Select Date</label>
              <input type="date" name="date" value="<?php echo h($the_date); ?>" class="form-control">
            </div>
            <div class="col-sm-3" style="display:flex;align-items:flex-end;gap:6px;">
              <button type="submit" class="btn btn-primary btn-sm2"><i class="fa fa-filter"></i> Apply</button>
              <a href="daily_summary_report.php?<?php $qs=$_GET; $qs['export']='csv'; echo h(http_build_query($qs)); ?>" class="btn btn-success btn-sm2"><i class="fa fa-download"></i> CSV</a>
              <button type="button" onclick="window.print();" class="btn btn-info btn-sm2"><i class="fa fa-print"></i> Print</button>
            </div>
          </form>

          <!-- KPI ROW 1 -->
          <div class="kpi">
            <div class="box">
              <h5>Sales (Net)</h5>
              <div class="text-blue"><?php echo h($currency_symbol)." ".number_format($sales_net,2); ?></div>
              <div class="small text-muted"><?php echo number_format($sales_invoices); ?> invoices</div>
            </div>
            <div class="box">
              <h5>Sale Returns (Net)</h5>
              <div><?php echo h($currency_symbol)." ".number_format($sales_return_net,2); ?></div>
              <div class="small text-muted"><?php echo number_format($sales_return_cnt); ?> returns</div>
            </div>
            <div class="box">
              <h5>Purchases (Net)</h5>
              <div><?php echo h($currency_symbol)." ".number_format($purchase_net,2); ?></div>
              <div class="small text-muted"><?php echo number_format($purchase_invoices); ?> bills</div>
            </div>
            <div class="box">
              <h5>Purchase Returns (Net)</h5>
              <div><?php echo h($currency_symbol)." ".number_format($purchase_return_net,2); ?></div>
            </div>
          </div>

          <!-- KPI ROW 2 -->
          <div class="kpi" style="margin-top:8px;">
            <div class="box">
              <h5>Customer Receipts</h5>
              <div class="text-green"><?php echo h($currency_symbol)." ".number_format($customer_receipts,2); ?></div>
            </div>
            <div class="box">
              <h5>Supplier Payments</h5>
              <div class="text-red"><?php echo h($currency_symbol)." ".number_format($supplier_payments,2); ?></div>
            </div>
            <div class="box">
              <h5>Expenses</h5>
              <div class="text-red"><?php echo h($currency_symbol)." ".number_format($expenses_amount,2); ?></div>
            </div>
            <div class="box">
              <h5>Sales Paid (Header)</h5>
              <div><?php echo h($currency_symbol)." ".number_format($sales_paid_hdr,2); ?></div>
            </div>
          </div>

          <!-- KPI ROW 3: PROFIT -->
          <div class="kpi" style="margin-top:8px;">
            <div class="box">
              <h5>Gross Profit</h5>
              <div class="text-blue"><?php echo h($currency_symbol)." ".number_format($gross_profit,2); ?></div>
              <div class="small text-muted">GP%: <?php echo number_format($gp_margin_pct,2); ?>%</div>
            </div>
            <div class="box">
              <h5>Sales Revenue (Lines)</h5>
              <div><?php echo h($currency_symbol)." ".number_format($sales_rev_lines,2); ?></div>
            </div>
            <div class="box">
              <h5>COGS (Lines)</h5>
              <div><?php echo h($currency_symbol)." ".number_format($sales_cogs_lines,2); ?></div>
            </div>
            <div class="box">
              <h5>Return Impact</h5>
              <div><?php echo h($currency_symbol)." ".number_format(($return_rev_lines - $return_cogs_lines),2); ?></div>
            </div>
          </div>

          <!-- Payment Mix -->
          <div class="chart-card">
            <div class="section-title">Customer Receipts Mix (Today)</div>
            <div class="table-responsive">
              <table class="table table-bordered table-sm">
                <thead>
                  <tr>
                    <th>Mode</th>
                    <th class="text-right" style="width:160px;">Amount</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  if (count($payment_mix) > 0) {
                      $mix_total = 0.0;
                      foreach ($payment_mix as $pm) {
                          $mix_total += (float)$pm['amount'];
                          echo "<tr>
                            <td>".h($pm['mode'])."</td>
                            <td class='text-right'>".h($currency_symbol)." ".number_format((float)$pm['amount'],2)."</td>
                          </tr>";
                      }
                      echo "<tr>
                        <td class='text-right'><strong>Total</strong></td>
                        <td class='text-right'><strong>".h($currency_symbol)." ".number_format($mix_total,2)."</strong></td>
                      </tr>";
                  } else {
                      echo "<tr><td colspan='2' class='text-center text-muted'>No receipts recorded today.</td></tr>";
                  }
                  ?>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Top Items + Today's Invoices/Purchases -->
          <div class="row">
            <div class="col-sm-6">
              <div class="chart-card">
                <div class="section-title">Top Items Sold Today</div>
                <div class="table-responsive">
                  <table class="table table-bordered table-sm">
                    <thead>
                      <tr>
                        <th>Item</th>
                        <th class="text-right" style="width:120px;">Qty</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                      if (count($top_items) > 0) {
                          foreach ($top_items as $ti) {
                              echo "<tr>
                                <td>".h($ti['item_name'])."</td>
                                <td class='text-right'>".number_format((float)$ti['qty'],0)."</td>
                              </tr>";
                          }
                      } else {
                          echo "<tr><td colspan='2' class='text-center text-muted'>No items sold today.</td></tr>";
                      }
                      ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>

            <div class="col-sm-6">
              <div class="chart-card">
                <div class="section-title">Today's Invoices</div>
                <div class="table-responsive">
                  <table class="table table-bordered table-sm">
                    <thead>
                      <tr>
                        <th>Date</th>
                        <th>No.</th>
                        <th>Customer</th>
                        <th class="text-right" style="width:140px;">Net</th>
                        <th class="text-right" style="width:120px;">Paid</th>
                        <th style="width:80px;">Action</th>
                      </tr>
                    </thead>
                    <tbody>
                    <?php
                    if (count($today_sales) > 0) {
                        $ts_net = $ts_paid = 0.0;
                        foreach ($today_sales as $s) {
                            $ts_net  += (float)$s['s_NetAmount'];
                            $ts_paid += (float)$s['s_PaidAmount'];
                            echo "<tr>
                              <td>".h(sum_date_formate($s['s_Date']))."</td>
                              <td>".h($s['s_Number'])."</td>
                              <td>".h($s['customer_name'])."</td>
                              <td class='text-right'>".h($currency_symbol)." ".number_format((float)$s['s_NetAmount'],2)."</td>
                              <td class='text-right'>".h($currency_symbol)." ".number_format((float)$s['s_PaidAmount'],2)."</td>
                              <td><a class='btn btn-xs btn-primary' href='sale_add?id=".(int)$s['s_id']."'><i class='fa fa-eye'></i></a></td>
                            </tr>";
                        }
                        echo "<tr>
                          <td colspan='3' class='text-right'><strong>Totals</strong></td>
                          <td class='text-right'><strong>".h($currency_symbol)." ".number_format($ts_net,2)."</strong></td>
                          <td class='text-right'><strong>".h($currency_symbol)." ".number_format($ts_paid,2)."</strong></td>
                          <td></td>
                        </tr>";
                    } else {
                        echo "<tr><td colspan='6' class='text-center text-muted'>No invoices today.</td></tr>";
                    }
                    ?>
                    </tbody>
                  </table>
                </div>
              </div>

              <div class="chart-card">
                <div class="section-title">Today's Purchases</div>
                <div class="table-responsive">
                  <table class="table table-bordered table-sm">
                    <thead>
                      <tr>
                        <th>Date</th>
                        <th>No.</th>
                        <th>Supplier</th>
                        <th class="text-right" style="width:140px;">Net</th>
                        <th style="width:80px;">Action</th>
                      </tr>
                    </thead>
                    <tbody>
                    <?php
                    if (count($today_purchases) > 0) {
                        $tp_net = 0.0;
                        foreach ($today_purchases as $p) {
                            $tp_net += (float)$p['p_NetAmount'];
                            echo "<tr>
                              <td>".h(sum_date_formate($p['p_Date']))."</td>
                              <td>".h($p['p_Number'])."</td>
                              <td>".h($p['supplier_name'])."</td>
                              <td class='text-right'>".h($currency_symbol)." ".number_format((float)$p['p_NetAmount'],2)."</td>
                              <td><a class='btn btn-xs btn-primary' href='purchase_add?id=".(int)$p['p_id']."'><i class='fa fa-eye'></i></a></td>
                            </tr>";
                        }
                        echo "<tr>
                          <td colspan='3' class='text-right'><strong>Total</strong></td>
                          <td class='text-right'><strong>".h($currency_symbol)." ".number_format($tp_net,2)."</strong></td>
                          <td></td>
                        </tr>";
                    } else {
                        echo "<tr><td colspan='5' class='text-center text-muted'>No purchases today.</td></tr>";
                    }
                    ?>
                    </tbody>
                  </table>
                </div>
              </div>

            </div>
          </div>

        </div>
      </article>
    </div>
  </section>
</div>
</div>

<?php include ("inc/footer.php"); include ("inc/scripts.php"); ?>