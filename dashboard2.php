<?php
include('sessionCheck.php');
include('connection.php');
include('functions.php');

$page_title = "Dashboard";

$branch_id = isset($_SESSION['branch_id']) ? (int)$_SESSION['branch_id'] : 0;
$currency_symbol = isset($_SESSION['currency_symbol']) && $_SESSION['currency_symbol'] !== '' ? $_SESSION['currency_symbol'] : 'Rs';

// Safe helpers
function qall($con, $sql){
    $res = mysqli_query($con, $sql);
    if ($res === false) { error_log("SQL error: ".mysqli_error($con)." -- ".$sql); return []; }
    $rows=[]; while($r=mysqli_fetch_assoc($res)){ $rows[]=$r; } mysqli_free_result($res); return $rows;
}
function qone($con, $sql, $default=0){
    $res = mysqli_query($con, $sql);
    if ($res === false) { error_log("SQL error: ".mysqli_error($con)." -- ".$sql); return $default; }
    $row = mysqli_fetch_row($res);
    mysqli_free_result($res);
    return $row && isset($row[0]) ? $row[0] : $default;
}

// Dates
$today = date('Y-m-d');
$month_start = date('Y-m-01');
$thirty_days_ago = date('Y-m-d', strtotime('-30 days'));
$twelve_months_ago = date('Y-m-01', strtotime('-11 months')); // inclusive 12 months

// KPI totals
$total_today_sales = qone($con, "SELECT IFNULL(SUM(s_NetAmount),0) FROM cust_sale WHERE branch_id=$branch_id AND s_Date='$today'", 0);
$total_today_invoices = qone($con, "SELECT COUNT(*) FROM cust_sale WHERE branch_id=$branch_id AND s_Date='$today'", 0);

$total_month_sales = qone($con, "SELECT IFNULL(SUM(s_NetAmount),0) FROM cust_sale WHERE branch_id=$branch_id AND s_Date>='$month_start' AND s_Date<='$today'", 0);
$total_month_purchases = qone($con, "SELECT IFNULL(SUM(p_NetAmount),0) FROM adm_purchase WHERE branch_id=$branch_id AND p_Date>='$month_start' AND p_Date<='$today'", 0);

$total_customers = qone($con, "SELECT COUNT(*) FROM accounts_chart WHERE branch_id=$branch_id AND (account_type='Asset' OR account_type='Customer')", 0);
$total_items = qone($con, "SELECT COUNT(*) FROM adm_item WHERE branch_id=$branch_id", 0);

// Sales vs Purchases last 12 months
$salesMonthly = qall($con, "
    SELECT DATE_FORMAT(s_Date,'%Y-%m') ym, IFNULL(SUM(s_NetAmount),0) total
    FROM cust_sale
    WHERE branch_id=$branch_id AND s_Date>='$twelve_months_ago' AND s_Date<='$today'
    GROUP BY ym
    ORDER BY ym
");
$purchMonthly = qall($con, "
    SELECT DATE_FORMAT(p_Date,'%Y-%m') ym, IFNULL(SUM(p_NetAmount),0) total
    FROM adm_purchase
    WHERE branch_id=$branch_id AND p_Date>='$twelve_months_ago' AND p_Date<='$today'
    GROUP BY ym
    ORDER BY ym
");

// Build month labels (ensure missing months are zero)
$labels = [];
for ($i=11; $i>=0; $i--){
    $ym = date('Y-m', strtotime("-$i months", strtotime($today)));
    $labels[] = $ym;
}
$salesMap = [];
foreach ($salesMonthly as $r){ $salesMap[$r['ym']] = (float)$r['total']; }
$purchMap = [];
foreach ($purchMonthly as $r){ $purchMap[$r['ym']] = (float)$r['total']; }
$salesSeries = [];
$purchSeries = [];
foreach ($labels as $ym){
    $salesSeries[] = isset($salesMap[$ym]) ? $salesMap[$ym] : 0;
    $purchSeries[] = isset($purchMap[$ym]) ? $purchMap[$ym] : 0;
}
// Prettier labels MMM-YY
$labelsPretty = [];
foreach ($labels as $ym) {
    $labelsPretty[] = date('M y', strtotime($ym.'-01'));
}

// Top 10 products by qty (last 30 days)
$topItems = qall($con, "
    SELECT i.item_Name, SUM(d.item_Qty) qty
    FROM cust_sale_detail d
    INNER JOIN cust_sale s ON s.s_id = d.s_id
    LEFT JOIN adm_item i ON i.item_id = d.item_id
    WHERE s.branch_id=$branch_id AND s.s_Date>='$thirty_days_ago' AND s.s_Date<='$today'
    GROUP BY d.item_id
    ORDER BY qty DESC
    LIMIT 10
");
$topLabels = []; $topQty = [];
foreach ($topItems as $r){
    $label = $r['item_Name'] !== null && $r['item_Name'] !== '' ? $r['item_Name'] : 'Item #'.count($topLabels)+1;
    $topLabels[] = $label;
    $topQty[] = (float)$r['qty'];
}

// Payment mix (last 30 days)
$mixRow = qall($con, "
    SELECT
        IFNULL(SUM(s_NetAmount),0) AS net_sum,
        IFNULL(SUM(s_PaidAmount),0) AS paid_sum
    FROM cust_sale
    WHERE branch_id=$branch_id AND s_Date>='$thirty_days_ago' AND s_Date<='$today'
");
$mix_net = isset($mixRow[0]['net_sum']) ? (float)$mixRow[0]['net_sum'] : 0;
$mix_paid = isset($mixRow[0]['paid_sum']) ? (float)$mixRow[0]['paid_sum'] : 0;
$mix_due = max($mix_net - $mix_paid, 0);

// Recent sales and purchases
$recentSales = qall($con, "
    SELECT s_id, s_Number, s_Date, s_NetAmount
    FROM cust_sale
    WHERE branch_id=$branch_id
    ORDER BY s_Date DESC, s_id DESC
    LIMIT 8
");
$recentPurch = qall($con, "
    SELECT p_id, p_Number, p_Date, p_NetAmount
    FROM adm_purchase
    WHERE branch_id=$branch_id
    ORDER BY p_Date DESC, p_id DESC
    LIMIT 8
");

// UI includes
require_once ("inc/init.php");
require_once ("inc/config.ui.php");
include ("inc/header.php");
include ("inc/nav.php");
?>
<!-- ==========================CONTENT STARTS HERE ========================== -->
<div id="main" role="main">
<?php
$breadcrumbs["Dashboard"] = "";
include("inc/ribbon.php");
?>

<style>
/* Dashboard styles */
body{background:#f6f8fb;}
.kpi-card{
  background:#fff;border-radius:12px;padding:16px;
  box-shadow:0 2px 12px rgba(0,0,0,.06);height:100%;
  border:1px solid #eef2f7;
}
.kpi-title{font-size:12px;color:#6b7280;text-transform:uppercase;letter-spacing:.3px;margin-bottom:8px}
.kpi-value{font-size:24px;font-weight:800;color:#111827}
.kpi-sub{font-size:12px;color:#94a3b8}
.chart-card{
  background:#fff;border-radius:12px;padding:16px;
  box-shadow:0 2px 12px rgba(0,0,0,.06);border:1px solid #eef2f7;margin-top:16px;
}
.section-title{font-size:16px;font-weight:700;color:#374151;margin:0 0 8px 0}
.table thead th{background:#f1f5f9;font-weight:700;font-size:12px}
.table td{font-size:12px;vertical-align:middle}
</style>

<div id="content">
  <section id="widget-grid">
    <div class="row">
      <!-- KPI CARDS -->
      <article class="col-sm-6 col-md-4 col-lg-2">
        <div class="kpi-card">
          <div class="kpi-title">Today's Sales</div>
          <div class="kpi-value"><?php echo htmlspecialchars($currency_symbol)." ".number_format($total_today_sales,2); ?></div>
          <div class="kpi-sub"><?php echo number_format($total_today_invoices); ?> invoices</div>
        </div>
      </article>
      <article class="col-sm-6 col-md-4 col-lg-2">
        <div class="kpi-card">
          <div class="kpi-title">This Month Sales</div>
          <div class="kpi-value"><?php echo htmlspecialchars($currency_symbol)." ".number_format($total_month_sales,2); ?></div>
          <div class="kpi-sub"><?php echo date('M Y'); ?></div>
        </div>
      </article>
      <article class="col-sm-6 col-md-4 col-lg-2">
        <div class="kpi-card">
          <div class="kpi-title">Purchases (Month)</div>
          <div class="kpi-value"><?php echo htmlspecialchars($currency_symbol)." ".number_format($total_month_purchases,2); ?></div>
          <div class="kpi-sub"><?php echo date('M Y'); ?></div>
        </div>
      </article>
      <article class="col-sm-6 col-md-4 col-lg-2">
        <div class="kpi-card">
          <div class="kpi-title">Customers</div>
          <div class="kpi-value"><?php echo number_format($total_customers); ?></div>
          <div class="kpi-sub">Active on branch</div>
        </div>
      </article>
      <article class="col-sm-6 col-md-4 col-lg-2">
        <div class="kpi-card">
          <div class="kpi-title">Items</div>
          <div class="kpi-value"><?php echo number_format($total_items); ?></div>
          <div class="kpi-sub"><a href="item_list.php">View products</a></div>
        </div>
      </article>
    </div>

    <!-- CHARTS ROW -->
    <div class="row">
      <article class="col-md-8">
        <div class="chart-card">
          <div class="section-title">Sales vs Purchases (Last 12 Months)</div>
          <canvas id="chartSalesPurchases" height="110"></canvas>
        </div>
      </article>
      <article class="col-md-4">
        <div class="chart-card">
          <div class="section-title">Payment Mix (Last 30 Days)</div>
          <canvas id="chartMix" height="110"></canvas>
          <div class="kpi-sub" style="margin-top:8px">
            Paid: <?php echo htmlspecialchars($currency_symbol)." ".number_format($mix_paid,2); ?> |
            Due: <?php echo htmlspecialchars($currency_symbol)." ".number_format($mix_due,2); ?>
          </div>
        </div>
      </article>
    </div>

    <!-- TOP ITEMS + RECENT -->
    <div class="row">
      <article class="col-md-6">
        <div class="chart-card">
          <div class="section-title">Top 10 Products by Quantity (Last 30 Days)</div>
          <canvas id="chartTopItems" height="140"></canvas>
        </div>
      </article>

      <article class="col-md-6">
        <div class="chart-card">
          <div class="section-title">Recent Activity</div>
          <div class="row">
            <div class="col-sm-6">
              <h5 class="kpi-title" style="margin-top:4px;">Recent Sales</h5>
              <div class="table-responsive">
                <table class="table table-bordered table-sm">
                  <thead>
                    <tr><th>Date</th><th>No.</th><th class="text-right">Net</th></tr>
                  </thead>
                  <tbody>
                  <?php if (count($recentSales)>0){
                      foreach ($recentSales as $s){
                          echo "<tr>
                              <td>".htmlspecialchars(sum_date_formate($s['s_Date']))."</td>
                              <td><a href='sale_add?id=".intval($s['s_id'])."'>".htmlspecialchars($s['s_Number'])."</a></td>
                              <td class='text-right'>".htmlspecialchars($currency_symbol)." ".number_format((float)$s['s_NetAmount'],2)."</td>
                          </tr>";
                      }
                  } else {
                      echo "<tr><td colspan='3' class='text-center text-muted'>No recent sales</td></tr>";
                  } ?>
                  </tbody>
                </table>
              </div>
            </div>
            <div class="col-sm-6">
              <h5 class="kpi-title" style="margin-top:4px;">Recent Purchases</h5>
              <div class="table-responsive">
                <table class="table table-bordered table-sm">
                  <thead>
                    <tr><th>Date</th><th>No.</th><th class="text-right">Net</th></tr>
                  </thead>
                  <tbody>
                  <?php if (count($recentPurch)>0){
                      foreach ($recentPurch as $p){
                          echo "<tr>
                              <td>".htmlspecialchars(sum_date_formate($p['p_Date']))."</td>
                              <td><a href='purchase_add?id=".intval($p['p_id'])."'>".htmlspecialchars($p['p_Number'])."</a></td>
                              <td class='text-right'>".htmlspecialchars($currency_symbol)." ".number_format((float)$p['p_NetAmount'],2)."</td>
                          </tr>";
                      }
                  } else {
                      echo "<tr><td colspan='3' class='text-center text-muted'>No recent purchases</td></tr>";
                  } ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </article>
    </div>

  </section>
</div>
</div>
<!-- ==========================CONTENT ENDS HERE ========================== -->

<?php include ("inc/footer.php"); ?>
<?php include ("inc/scripts.php"); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function(){
  // PHP data to JS
  var labels = <?php echo json_encode($labelsPretty, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>;
  var salesData = <?php echo json_encode($salesSeries, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>;
  var purchData = <?php echo json_encode($purchSeries, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>;

  var topLabels = <?php echo json_encode($topLabels, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>;
  var topQty = <?php echo json_encode($topQty, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>;

  var mixPaid = <?php echo json_encode($mix_paid); ?>;
  var mixDue  = <?php echo json_encode($mix_due); ?>;

  // Sales vs Purchases
  var ctx1 = document.getElementById('chartSalesPurchases').getContext('2d');
  new Chart(ctx1, {
    type: 'line',
    data: {
      labels: labels,
      datasets: [
        { label: 'Sales', data: salesData, borderColor:'#2563eb', backgroundColor:'rgba(37,99,235,.12)', tension:.3, fill:true, borderWidth:2, pointRadius:2 },
        { label: 'Purchases', data: purchData, borderColor:'#10b981', backgroundColor:'rgba(16,185,129,.12)', tension:.3, fill:true, borderWidth:2, pointRadius:2 }
      ]
    },
    options: {
      responsive: true,
      plugins: { legend: { position:'top' } },
      scales: {
        y: { beginAtZero:true, ticks: { callback: function(v){ return v.toLocaleString(); } } },
        x: { ticks: { autoSkip: true, maxTicksLimit: 12 } }
      }
    }
  });

  // Payment Mix
  var ctx2 = document.getElementById('chartMix').getContext('2d');
  new Chart(ctx2, {
    type: 'doughnut',
    data: {
      labels: ['Paid','Due'],
      datasets: [{
        data: [mixPaid, mixDue],
        backgroundColor: ['#22c55e','#ef4444'],
        hoverBackgroundColor: ['#16a34a','#dc2626']
      }]
    },
    options: {
      plugins: { legend: { position:'bottom' } },
      cutout: '55%'
    }
  });

  // Top Items
  var ctx3 = document.getElementById('chartTopItems').getContext('2d');
  new Chart(ctx3, {
    type: 'bar',
    data: {
      labels: topLabels,
      datasets: [{
        label: 'Qty',
        data: topQty,
        backgroundColor: '#0ea5e9'
      }]
    },
    options: {
      indexAxis: 'y',
      plugins: { legend: { display:false } },
      scales: { x: { beginAtZero:true }, y: { ticks: { autoSkip:false } } }
    }
  });
})();
</script>