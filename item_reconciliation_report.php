<?php
include('sessionCheck.php');
include('connection.php');
include('functions.php');

$page_title = "Item Reconciliation";

$branch_id = isset($_SESSION['branch_id']) ? (int)$_SESSION['branch_id'] : 0;
$currency_symbol = isset($_SESSION['currency_symbol']) && $_SESSION['currency_symbol'] !== '' ? $_SESSION['currency_symbol'] : 'Rs';

// Helpers
function qall($con, $sql){
    $res = mysqli_query($con, $sql);
    if ($res === false) { error_log("SQL error: ".mysqli_error($con)." -- ".$sql); return []; }
    $rows=[]; while($r=mysqli_fetch_assoc($res)){ $rows[]=$r; } mysqli_free_result($res); return $rows;
}
function h($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }
function nz($v){ return (float)$v; }

// Filters
$from = isset($_GET['from']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['from']) ? $_GET['from'] : date('Y-m-01');
$to   = isset($_GET['to'])   && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['to'])   ? $_GET['to']   : date('Y-m-d');
$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$show_zero = isset($_GET['show_zero']) ? (int)$_GET['show_zero'] : 0;

$open_date = date('Y-m-d', strtotime($from.' -1 day'));

// Build optional search WHERE for items
$itemWhere = "i.branch_id={$branch_id}";
if ($search !== '') {
    $esc = mysqli_real_escape_string($con, $search);
    $itemWhere .= " AND (i.item_Code LIKE '%{$esc}%' OR i.item_Name LIKE '%{$esc}%')";
}

// Subqueries for opening (<= open_date)
$SQ_pb = "
  SELECT pd.item_id, SUM(pd.item_Qty) qty
  FROM adm_purchase_detail pd
  INNER JOIN adm_purchase p ON p.p_id = pd.p_id
  WHERE p.branch_id={$branch_id} AND p.p_Date <= '{$open_date}'
  GROUP BY pd.item_id
";
$SQ_sb = "
  SELECT sd.item_id, SUM(sd.item_Qty) qty
  FROM cust_sale_detail sd
  INNER JOIN cust_sale s ON s.s_id = sd.s_id
  WHERE s.branch_id={$branch_id} AND s.s_Date <= '{$open_date}'
  GROUP BY sd.item_id
";
$SQ_srb = "
  SELECT rd.item_id, SUM(rd.item_Qty) qty
  FROM cust_salereturn_detail rd
  INNER JOIN cust_salereturn r ON r.sr_id = rd.sr_id
  WHERE r.branch_id={$branch_id} AND r.sr_Date <= '{$open_date}'
  GROUP BY rd.item_id
";
$SQ_prb = "
  SELECT prd.item_id, SUM(prd.item_Qty) qty
  FROM adm_purchasereturn_detail prd
  INNER JOIN adm_purchasereturn pr ON pr.pr_id = prd.pr_id
  WHERE pr.branch_id={$branch_id} AND pr.pr_Date <= '{$open_date}'
  GROUP BY prd.item_id
";

// Subqueries for period (between from..to)
$SQ_pi = "
  SELECT pd.item_id, SUM(pd.item_Qty) qty
  FROM adm_purchase_detail pd
  INNER JOIN adm_purchase p ON p.p_id = pd.p_id
  WHERE p.branch_id={$branch_id} AND p.p_Date BETWEEN '{$from}' AND '{$to}'
  GROUP BY pd.item_id
";
$SQ_si = "
  SELECT sd.item_id, SUM(sd.item_Qty) qty
  FROM cust_sale_detail sd
  INNER JOIN cust_sale s ON s.s_id = sd.s_id
  WHERE s.branch_id={$branch_id} AND s.s_Date BETWEEN '{$from}' AND '{$to}'
  GROUP BY sd.item_id
";
$SQ_sri = "
  SELECT rd.item_id, SUM(rd.item_Qty) qty
  FROM cust_salereturn_detail rd
  INNER JOIN cust_salereturn r ON r.sr_id = rd.sr_id
  WHERE r.branch_id={$branch_id} AND r.sr_Date BETWEEN '{$from}' AND '{$to}'
  GROUP BY rd.item_id
";
$SQ_pri = "
  SELECT prd.item_id, SUM(prd.item_Qty) qty
  FROM adm_purchasereturn_detail prd
  INNER JOIN adm_purchasereturn pr ON pr.pr_id = prd.pr_id
  WHERE pr.branch_id={$branch_id} AND pr.pr_Date BETWEEN '{$from}' AND '{$to}'
  GROUP BY prd.item_id
";

// Final query joining aggregates to items
$sql = "
SELECT
  i.item_id,
  i.item_Code,
  i.item_Name,
  COALESCE(u.unit_name,'') AS unit_name,

  -- Quantities
  COALESCE(pb.qty,0) AS open_purch,
  COALESCE(srb.qty,0) AS open_sale_ret,
  COALESCE(sb.qty,0) AS open_sale,
  COALESCE(prb.qty,0) AS open_purch_ret,

  COALESCE(pi.qty,0)  AS in_purchase,
  COALESCE(pri.qty,0) AS out_purch_return,
  COALESCE(si.qty,0)  AS out_sale,
  COALESCE(sri.qty,0) AS in_sale_return

FROM adm_item i
LEFT JOIN adm_itemunit u ON u.unit_id = i.unit_id

LEFT JOIN ({$SQ_pb})  pb  ON pb.item_id  = i.item_id
LEFT JOIN ({$SQ_sb})  sb  ON sb.item_id  = i.item_id
LEFT JOIN ({$SQ_srb}) srb ON srb.item_id = i.item_id
LEFT JOIN ({$SQ_prb}) prb ON prb.item_id = i.item_id

LEFT JOIN ({$SQ_pi})  pi  ON pi.item_id  = i.item_id
LEFT JOIN ({$SQ_pri}) pri ON pri.item_id = i.item_id
LEFT JOIN ({$SQ_si})  si  ON si.item_id  = i.item_id
LEFT JOIN ({$SQ_sri}) sri ON sri.item_id = i.item_id

WHERE {$itemWhere}
ORDER BY i.item_Name ASC, i.item_Code ASC
";

$rows = qall($con, $sql);

// Compute opening, closing, and prune zero rows if needed
$data = [];
$tot_open = $tot_pur = $tot_pr = $tot_sale = $tot_sr = $tot_close = 0.0;

foreach ($rows as $r) {
    $opening = nz($r['open_purch']) + nz($r['open_sale_ret']) - nz($r['open_sale']) - nz($r['open_purch_ret']);
    $purchase = nz($r['in_purchase']);
    $purchase_return = nz($r['out_purch_return']);
    $sale = nz($r['out_sale']);
    $sale_return = nz($r['in_sale_return']);
    $closing = $opening + $purchase + $sale_return - $sale - $purchase_return;

    $is_zero = abs($opening) < 0.00001
            && abs($purchase) < 0.00001
            && abs($purchase_return) < 0.00001
            && abs($sale) < 0.00001
            && abs($sale_return) < 0.00001
            && abs($closing) < 0.00001;

    if (!$show_zero && $is_zero) continue;

    $data[] = [
        'item_id' => (int)$r['item_id'],
        'item_Code' => $r['item_Code'],
        'item_Name' => $r['item_Name'],
        'unit_name' => $r['unit_name'],
        'opening' => $opening,
        'purchase' => $purchase,
        'purchase_return' => $purchase_return,
        'sale' => $sale,
        'sale_return' => $sale_return,
        'closing' => $closing,
    ];

    $tot_open  += $opening;
    $tot_pur   += $purchase;
    $tot_pr    += $purchase_return;
    $tot_sale  += $sale;
    $tot_sr    += $sale_return;
    $tot_close += $closing;
}

// CSV export
if (isset($_GET['export']) && $_GET['export']==='csv') {
    header('Content-Type: text/csv; charset=utf-8');
    $fname = "item_reconciliation_".date('Ymd_His').".csv";
    header('Content-Disposition: attachment; filename="'.$fname.'"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['From Date',$from,'To Date',$to,'Branch',$branch_id]);
    fputcsv($out, []);
    fputcsv($out, ['Item Code','Item Name','Unit','Opening','Purchase','Purchase Return','Sale','Sale Return','Current Stock']);
    foreach ($data as $d) {
        fputcsv($out, [
            $d['item_Code'],
            $d['item_Name'],
            $d['unit_name'],
            number_format($d['opening'],2,'.',''),
            number_format($d['purchase'],2,'.',''),
            number_format($d['purchase_return'],2,'.',''),
            number_format($d['sale'],2,'.',''),
            number_format($d['sale_return'],2,'.',''),
            number_format($d['closing'],2,'.',''),
        ]);
    }
    fputcsv($out, []);
    fputcsv($out, ['Totals','','',
        number_format($tot_open,2,'.',''),
        number_format($tot_pur,2,'.',''),
        number_format($tot_pr,2,'.',''),
        number_format($tot_sale,2,'.',''),
        number_format($tot_sr,2,'.',''),
        number_format($tot_close,2,'.','')
    ]);
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
<?php $breadcrumbs["Reports"] = ""; $breadcrumbs["Item Reconciliation"] = ""; include("inc/ribbon.php"); ?>

<style>
body{background:#f6f8fb;}
.form-control{border-radius:6px!important;font-size:13px}
.report-card{background:#fff;border-radius:10px;box-shadow:0 2px 12px rgba(0,0,0,.06);padding:18px;margin-top:16px;border:1px solid #eef2f7;}
.report-title{font-size:18px;font-weight:800;color:#374151;margin-bottom:4px;}
.submeta{font-size:12px;color:#6b7280;margin-bottom:12px}
.table thead th{background:#e5e7eb;font-weight:700;font-size:12px}
.table td{font-size:12px;vertical-align:middle}
.btn-sm2{padding:6px 10px;font-size:12px;border-radius:6px}
.filters .col-sm-2,.filters .col-sm-3,.filters .col-sm-4{margin-bottom:8px}
</style>

<div id="content">
<section id="widget-grid">
<div class="row">
<article class="col-sm-12 col-md-11 col-lg-10 col-lg-offset-1">
  <div class="report-card">
    <div class="report-title">Item Reconciliation</div>
    <div class="submeta">
      From Date: <strong><?php echo h($from);?></strong> &nbsp;&nbsp;
      To Date: <strong><?php echo h($to);?></strong> &nbsp;&nbsp;
      Print Date Time: <strong><?php echo date('d-m-Y h:i A');?></strong>
    </div>

    <form class="row filters" method="get" action="item_reconciliation_report.php">
      <div class="col-sm-2">
        <label class="small text-muted">From</label>
        <input type="date" name="from" value="<?php echo h($from);?>" class="form-control">
      </div>
      <div class="col-sm-2">
        <label class="small text-muted">To</label>
        <input type="date" name="to" value="<?php echo h($to);?>" class="form-control">
      </div>
      <div class="col-sm-4">
        <label class="small text-muted">Item Search</label>
        <input type="text" name="q" value="<?php echo h($search);?>" class="form-control" placeholder="Code or name contains...">
      </div>
      <div class="col-sm-2">
        <label class="small text-muted">Show Zero Rows</label>
        <select name="show_zero" class="form-control">
          <option value="0" <?php echo $show_zero? '' : 'selected';?>>No</option>
          <option value="1" <?php echo $show_zero? 'selected' : '';?>>Yes</option>
        </select>
      </div>
      <div class="col-sm-2" style="display:flex;gap:6px;align-items:flex-end;">
        <button type="submit" class="btn btn-primary btn-sm2"><i class="fa fa-filter"></i> Apply</button>
        <a href="item_reconciliation_report.php?<?php $qs=$_GET; $qs['export']='csv'; echo h(http_build_query($qs)); ?>" class="btn btn-success btn-sm2"><i class="fa fa-download"></i> CSV</a>
        <button type="button" onclick="window.print();" class="btn btn-info btn-sm2"><i class="fa fa-print"></i> Print</button>
      </div>
    </form>

    <div class="table-responsive" style="margin-top:8px;">
      <table class="table table-bordered">
        <thead>
          <tr>
            <th>Item Name</th>
            <th style="width:80px;">Unit</th>
            <th class="text-right" style="width:110px;">Opening</th>
            <th class="text-right" style="width:110px;">Purchase</th>
            <th class="text-right" style="width:130px;">Purchase Return</th>
            <th class="text-right" style="width:110px;">Sale</th>
            <th class="text-right" style="width:110px;">Sale Return</th>
            <th class="text-right" style="width:130px;">Current Stock</th>
          </tr>
        </thead>
        <tbody>
        <?php
        if (count($data) > 0) {
            foreach ($data as $d) {
                echo "<tr>
                    <td>".h($d['item_Name'])."</td>
                    <td>".h($d['unit_name'])."</td>
                    <td class='text-right'>".number_format($d['opening'],0)."</td>
                    <td class='text-right'>".number_format($d['purchase'],0)."</td>
                    <td class='text-right'>".number_format($d['purchase_return'],0)."</td>
                    <td class='text-right'>".number_format($d['sale'],0)."</td>
                    <td class='text-right'>".number_format($d['sale_return'],0)."</td>
                    <td class='text-right'>".number_format($d['closing'],0)."</td>
                </tr>";
            }
            echo "<tr>
                <td colspan='2' class='text-right'><strong>Totals</strong></td>
                <td class='text-right'><strong>".number_format($tot_open,0)."</strong></td>
                <td class='text-right'><strong>".number_format($tot_pur,0)."</strong></td>
                <td class='text-right'><strong>".number_format($tot_pr,0)."</strong></td>
                <td class='text-right'><strong>".number_format($tot_sale,0)."</strong></td>
                <td class='text-right'><strong>".number_format($tot_sr,0)."</strong></td>
                <td class='text-right'><strong>".number_format($tot_close,0)."</strong></td>
            </tr>";
        } else {
            echo "<tr><td colspan='8' class='text-center text-muted'>No records for the selected criteria.</td></tr>";
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