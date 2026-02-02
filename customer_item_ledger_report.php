<?php
include('sessionCheck.php');
include('connection.php');
include('functions.php');

$page_title = "Customer Wise Item Ledger";
$debug = isset($_GET['debug']) && $_GET['debug']=='1';

// ---------- Helpers ----------
function qone($con,$sql,$def=0){
    $res=mysqli_query($con,$sql);
    if(!$res) return $def;
    $r=mysqli_fetch_row($res);
    mysqli_free_result($res);
    return $r? (float)$r[0] : (float)$def;
}
function qall($con,$sql){
    $res=mysqli_query($con,$sql);
    if(!$res) return [];
    $rows=[];
    while($r=mysqli_fetch_assoc($res)) $rows[]=$r;
    mysqli_free_result($res);
    return $rows;
}
function h($s){ return htmlspecialchars($s??'',ENT_QUOTES,'UTF-8'); }
function table_exists_local($con,$name){
    $n=mysqli_real_escape_string($con,$name);
    $r=mysqli_query($con,"SHOW TABLES LIKE '{$n}'");
    if(!$r) return false;
    $ok=mysqli_num_rows($r)>0;
    mysqli_free_result($r);
    return $ok;
}

// ---------- Filters ----------
$from  = isset($_GET['from'])  && preg_match('/^\d{4}-\d{2}-\d{2}$/',$_GET['from']) ? $_GET['from'] : date('Y-m-01');
$to    = isset($_GET['to'])    && preg_match('/^\d{4}-\d{2}-\d{2}$/',$_GET['to'])   ? $_GET['to']   : date('Y-m-d');
$cust  = isset($_GET['cust'])  ? (int)$_GET['cust'] : 0;
$item_id = isset($_GET['item_id']) ? (int)$_GET['item_id'] : 0;
$view  = isset($_GET['view']) && in_array($_GET['view'],['summary','detail']) ? $_GET['view'] : 'summary';
$export= isset($_GET['export']) && $_GET['export']=='csv';

// Customer required
$errors=[];
if(isset($_GET['apply']) && $cust<=0){
    $errors[] = "Customer selection required.";
}

// Dropdown Data
$customers = qall($con,"
    SELECT account_id, account_title
    FROM accounts_chart
    WHERE status='active'
    ORDER BY account_title
");
$items_all = qall($con,"SELECT item_id,item_Code,item_Name FROM adm_item ORDER BY item_Name,item_Code");

// Sale return table check
$has_sale_return = table_exists_local($con,'cust_salereturn') && table_exists_local($con,'cust_salereturn_detail');

// Data holders
$data_summary = [];
$data_detail = [];
$open_date = date('Y-m-d', strtotime($from.' -1 day'));

if($cust>0 && empty($errors)){
    // Items in scope (keep earlier logic so future expansion easy)
    $rangeItems = qall($con,"
        SELECT DISTINCT sd.item_id
        FROM cust_sale_detail sd
        INNER JOIN cust_sale s ON s.s_id=sd.s_id
        WHERE s.client_id={$cust}
          AND COALESCE(s.s_Date, sd.sd_Date) BETWEEN '{$from}' AND '{$to}'
          ".($item_id>0?" AND sd.item_id={$item_id} ":"")."
          AND sd.item_id IS NOT NULL
    ");
    $rangeIds = array_column($rangeItems,'item_id');

    $openItems = qall($con,"
        SELECT DISTINCT a.item_id FROM (
           SELECT pd.item_id
           FROM adm_purchase_detail pd
           INNER JOIN adm_purchase p ON p.p_id=pd.p_id
           WHERE COALESCE(p.p_Date, pd.pd_Date) <= '{$open_date}'
             ".($item_id>0?" AND pd.item_id={$item_id} ":"")."
           UNION
           SELECT sd.item_id
           FROM cust_sale_detail sd
           INNER JOIN cust_sale s ON s.s_id=sd.s_id
           WHERE s.client_id={$cust}
             AND COALESCE(s.s_Date, sd.sd_Date) <= '{$open_date}'
             ".($item_id>0?" AND sd.item_id={$item_id} ":"")."
           ".($has_sale_return ? "
           UNION
           SELECT rd.item_id
           FROM cust_salereturn_detail rd
           INNER JOIN cust_salereturn r ON r.sr_id=rd.sr_id
           WHERE r.client_id={$cust}
             AND COALESCE(r.sr_Date, rd.srd_Date) <= '{$open_date}'
             ".($item_id>0?" AND rd.item_id={$item_id} ":"")."
           " : "")."
        ) a
        WHERE a.item_id IS NOT NULL
    ");
    $openIds = array_column($openItems,'item_id');

    $allItemIds = array_unique(array_merge($rangeIds,$openIds));
    if($item_id>0 && !in_array($item_id,$allItemIds)) $allItemIds[]=$item_id;

    if($view==='summary'){
        foreach($allItemIds as $iid){
            if(!$iid) continue;

            // (Opening / Purchase kept internal—unused in display—so you can restore later)
            $purch_before = qone($con,"
                SELECT IFNULL(SUM(pd.item_Qty),0)
                FROM adm_purchase_detail pd
                INNER JOIN adm_purchase p ON p.p_id=pd.p_id
                WHERE pd.item_id={$iid}
                  AND COALESCE(p.p_Date,pd.pd_Date) <= '{$open_date}'
            ",0);

            $sales_before_all = qone($con,"
                SELECT IFNULL(SUM(sd.item_Qty),0)
                FROM cust_sale_detail sd
                INNER JOIN cust_sale s ON s.s_id=sd.s_id
                WHERE sd.item_id={$iid}
                  AND s.client_id={$cust}
                  AND COALESCE(s.s_Date, sd.sd_Date) <= '{$open_date}'
            ",0);

            $returns_before = ($has_sale_return)? qone($con,"
                SELECT IFNULL(SUM(rd.item_Qty),0)
                FROM cust_salereturn_detail rd
                INNER JOIN cust_salereturn r ON r.sr_id=rd.sr_id
                WHERE rd.item_id={$iid}
                  AND r.client_id={$cust}
                  AND COALESCE(r.sr_Date, rd.srd_Date) <= '{$open_date}'
            ",0) : 0;

            $opening_internal = $purch_before + $returns_before - $sales_before_all; // not shown

            // In-range categories
            $sale_out_normal = qone($con,"
                SELECT IFNULL(SUM(sd.item_Qty),0)
                FROM cust_sale_detail sd
                INNER JOIN cust_sale s ON s.s_id=sd.s_id
                WHERE sd.item_id={$iid}
                  AND s.client_id={$cust}
                  AND s.s_SaleMode NOT IN ('sample','claim')
                  AND COALESCE(s.s_Date, sd.sd_Date) BETWEEN '{$from}' AND '{$to}'
            ",0);

            $sample_out = qone($con,"
                SELECT IFNULL(SUM(sd.item_Qty),0)
                FROM cust_sale_detail sd
                INNER JOIN cust_sale s ON s.s_id=sd.s_id
                WHERE sd.item_id={$iid}
                  AND s.client_id={$cust}
                  AND s.s_SaleMode='sample'
                  AND COALESCE(s.s_Date, sd.sd_Date) BETWEEN '{$from}' AND '{$to}'
            ",0);

            $claim_out = qone($con,"
                SELECT IFNULL(SUM(sd.item_Qty),0)
                FROM cust_sale_detail sd
                INNER JOIN cust_sale s ON s.s_id=sd.s_id
                WHERE sd.item_id={$iid}
                  AND s.client_id={$cust}
                  AND s.s_SaleMode='claim'
                  AND COALESCE(s.s_Date, sd.sd_Date) BETWEEN '{$from}' AND '{$to}'
            ",0);

            $sale_return_range = ($has_sale_return)? qone($con,"
                SELECT IFNULL(SUM(rd.item_Qty),0)
                FROM cust_salereturn_detail rd
                INNER JOIN cust_salereturn r ON r.sr_id=rd.sr_id
                WHERE rd.item_id={$iid}
                  AND r.client_id={$cust}
                  AND COALESCE(r.sr_Date, rd.srd_Date) BETWEEN '{$from}' AND '{$to}'
            ",0) : 0;

            // Net Movement (relationship for selected period only)
            $net_movement = $sale_return_range - ($sale_out_normal + $sample_out + $claim_out);

            // Item info
            $rowItem = qall($con,"SELECT item_Code,item_Name FROM adm_item WHERE item_id={$iid} LIMIT 1");
            $code = $rowItem? $rowItem[0]['item_Code'] : '';
            $name = $rowItem? $rowItem[0]['item_Name'] : '';

            $data_summary[] = [
                'item_id'=>$iid,
                'item_Code'=>$code,
                'item_Name'=>$name,
                'sale_out'=>$sale_out_normal,
                'sample_out'=>$sample_out,
                'claim_out'=>$claim_out,
                'sale_return_in'=>$sale_return_range,
                'net_movement'=>$net_movement
            ];
        }
        usort($data_summary,function($a,$b){
            return strcasecmp($a['item_Name'],$b['item_Name']);
        });

    } else { // detail view unchanged
        $idList = $allItemIds ? implode(',',array_map('intval',$allItemIds)) : '0';
        $uParts=[];
        if($idList!==''){
            $uParts[]="
              SELECT
                COALESCE(p.p_Date,pd.pd_Date) AS t_date,
                'Purchase' AS t_type,
                p.p_Number AS ref_no,
                pd.item_id,
                pd.item_Qty AS qty_in,
                0 AS qty_out,
                pd.pd_id AS sort_id,
                1 AS t_rank,
                '' AS mode
              FROM adm_purchase_detail pd
              INNER JOIN adm_purchase p ON p.p_id=pd.p_id
              WHERE pd.item_id IN ({$idList})
                AND COALESCE(p.p_Date,pd.pd_Date) BETWEEN '{$from}' AND '{$to}'
            ";
            $uParts[]="
              SELECT
                COALESCE(s.s_Date,sd.sd_Date) AS t_date,
                CASE s.s_SaleMode
                  WHEN 'sample' THEN 'Sample'
                  WHEN 'claim'  THEN 'Claim'
                  ELSE 'Sale'
                END AS t_type,
                s.s_Number AS ref_no,
                sd.item_id,
                0 AS qty_in,
                sd.item_Qty AS qty_out,
                sd.sd_id AS sort_id,
                3 AS t_rank,
                s.s_SaleMode AS mode
              FROM cust_sale_detail sd
              INNER JOIN cust_sale s ON s.s_id=sd.s_id
              WHERE s.client_id={$cust}
                AND sd.item_id IN ({$idList})
                AND COALESCE(s.s_Date,sd.sd_Date) BETWEEN '{$from}' AND '{$to}'
            ";
            if($has_sale_return){
                $uParts[]="
                  SELECT
                    COALESCE(r.sr_Date,rd.srd_Date) AS t_date,
                    'Sale Return' AS t_type,
                    r.sr_Number AS ref_no,
                    rd.item_id,
                    rd.item_Qty AS qty_in,
                    0 AS qty_out,
                    rd.srd_id AS sort_id,
                    4 AS t_rank,
                    'salereturn' AS mode
                  FROM cust_salereturn_detail rd
                  INNER JOIN cust_salereturn r ON r.sr_id=rd.sr_id
                  WHERE r.client_id={$cust}
                    AND rd.item_id IN ({$idList})
                    AND COALESCE(r.sr_Date, rd.srd_Date) BETWEEN '{$from}' AND '{$to}'
                ";
            }
        }
        $union_sql='';
        if($uParts){
            $union_sql = implode("\nUNION ALL\n",$uParts)."\nORDER BY item_id ASC, t_date ASC, t_rank ASC, sort_id ASC";
            $res = mysqli_query($con,$union_sql);
            if($res){
                while($r=mysqli_fetch_assoc($res)) $data_detail[]=$r;
                mysqli_free_result($res);
            }
        }
        // Opening map for running
        $opening_map=[];
        foreach($allItemIds as $iid){
            if(!$iid) continue;
            $purch_before = qone($con,"
                SELECT IFNULL(SUM(pd.item_Qty),0)
                FROM adm_purchase_detail pd
                INNER JOIN adm_purchase p ON p.p_id=pd.p_id
                WHERE pd.item_id={$iid}
                  AND COALESCE(p.p_Date,pd.pd_Date) <= '{$open_date}'
            ",0);
            $sales_before_all = qone($con,"
                SELECT IFNULL(SUM(sd.item_Qty),0)
                FROM cust_sale_detail sd
                INNER JOIN cust_sale s ON s.s_id=sd.s_id
                WHERE sd.item_id={$iid}
                  AND s.client_id={$cust}
                  AND COALESCE(s.s_Date, sd.sd_Date) <= '{$open_date}'
            ",0);
            $returns_before = ($has_sale_return)? qone($con,"
                SELECT IFNULL(SUM(rd.item_Qty),0)
                FROM cust_salereturn_detail rd
                INNER JOIN cust_salereturn r ON r.sr_id=rd.sr_id
                WHERE rd.item_id={$iid}
                  AND r.client_id={$cust}
                  AND COALESCE(r.sr_Date, rd.srd_Date) <= '{$open_date}'
            ",0) : 0;
            $opening_map[$iid] = $purch_before + $returns_before - $sales_before_all;
        }
        $run_map=$opening_map;
        foreach($data_detail as &$row){
            $iid=(int)$row['item_id'];
            if(!isset($run_map[$iid])) $run_map[$iid]=0;
            $run_map[$iid]+= (float)$row['qty_in'] - (float)$row['qty_out'];
            $row['_running']=$run_map[$iid];
        }
        unset($row);
    }
}

// CSV export (summary view modified)
if($export && $cust>0){
    header('Content-Type: text/csv; charset=utf-8');
    $fname = "customer_item_ledger_{$cust}_".date('Ymd_His').".csv";
    header('Content-Disposition: attachment; filename="'.$fname.'"');
    $out = fopen('php://output','w');
    fputcsv($out,['Customer Wise Item Ledger (No Opening/Purchase/Closing Columns)']);
    fputcsv($out,['Customer',$cust,'From',$from,'To',$to,'View',$view]);
    fputcsv($out,[]);
    if($view==='summary'){
        fputcsv($out,['Code','Item','Sale Out','Sample Out','Claim Out','Sale Return In','Net Movement']);
        foreach($data_summary as $r){
            fputcsv($out,[
                $r['item_Code'],
                $r['item_Name'],
                number_format($r['sale_out'],2,'.',''),
                number_format($r['sample_out'],2,'.',''),
                number_format($r['claim_out'],2,'.',''),
                number_format($r['sale_return_in'],2,'.',''),
                number_format($r['net_movement'],2,'.','')
            ]);
        }
    } else {
        fputcsv($out,['Item ID','Date','Type','Ref #','Qty In','Qty Out','Running (Customer)']);
        foreach($data_detail as $r){
            fputcsv($out,[
                $r['item_id'],
                $r['t_date'],
                $r['t_type'],
                $r['ref_no'],
                number_format($r['qty_in'],2,'.',''),
                number_format($r['qty_out'],2,'.',''),
                number_format($r['_running'],2,'.','')
            ]);
        }
    }
    fclose($out);
    exit;
}

// UI includes
require_once("inc/init.php");
require_once("inc/config.ui.php");
include("inc/header.php");
include("inc/nav.php");
?>
<div id="main" role="main">
<?php $breadcrumbs["Reports"]=''; $breadcrumbs["Customer Wise Item Ledger"]=''; include("inc/ribbon.php"); ?>
<style>
body{background:#f6f8fb;}
.report-card{background:#fff;border-radius:12px;box-shadow:0 2px 12px rgba(0,0,0,.06);padding:18px;margin-top:16px;border:1px solid #eef2f7;}
.report-title{font-size:18px;font-weight:800;color:#374151;margin-bottom:12px;}
.form-control{border-radius:6px!important;font-size:13px}
.table thead th{background:#f1f5f9;font-weight:700;font-size:12px}
.table td{font-size:12px;vertical-align:middle}
.btn-sm2{padding:6px 10px;font-size:12px;border-radius:6px}
.filters .col-sm-2,.filters .col-sm-3,.filters .col-sm-4{margin-bottom:8px}
.note{font-size:11px;color:#b45309;background:#fff7ed;border:1px solid #fcd9b6;padding:6px 9px;border-radius:6px;margin:6px 0}
.debug{white-space:pre-wrap;background:#fff7ed;border:1px dashed #f59e0b;padding:8px;border-radius:6px;font-size:11px;margin-top:10px}
.badge-type{display:inline-block;padding:2px 6px;font-size:11px;border-radius:6px;border:1px solid #ddd}
.badge-out{background:#ffecec;color:#b10000;border-color:#ffb5b5}
.badge-in{background:#e9fff0;color:#0b7a2d;border-color:#b9e8c9}
</style>

<div id="content">
<section id="widget-grid">
<div class="row">
<article class="col-sm-12 col-md-11 col-lg-10 col-lg-offset-1">
<div class="report-card">
  <div class="report-title"><i class="fa fa-exchange"></i> Customer Wise Item Ledger</div>

  <?php if($errors){ echo "<div class='alert alert-danger' style='padding:8px 12px;margin-bottom:10px;'>".implode("<br>",array_map('h',$errors))."</div>"; } ?>

  <form class="row filters" method="get" action="customer_item_ledger_report.php">
    <input type="hidden" name="apply" value="1">
    <div class="col-sm-2">
      <label class="small text-muted">From</label>
      <input type="date" name="from" value="<?=h($from)?>" class="form-control" required>
    </div>
    <div class="col-sm-2">
      <label class="small text-muted">To</label>
      <input type="date" name="to" value="<?=h($to)?>" class="form-control" required>
    </div>
    <div class="col-sm-3">
      <label class="small text-muted">Customer</label>
      <select name="cust" class="form-control" required>
        <option value="">Select...</option>
        <?php foreach($customers as $cRow){
            $sel = $cust==$cRow['account_id']?'selected':'';
            echo "<option value='{$cRow['account_id']}' {$sel}>".h($cRow['account_title'])."</option>";
        } ?>
      </select>
    </div>
    <div class="col-sm-3">
      <label class="small text-muted">Item (Optional)</label>
      <select name="item_id" class="form-control">
        <option value="0">All Items</option>
        <?php foreach($items_all as $it){
            $sel = $item_id==$it['item_id'] ? 'selected':'';
            echo "<option value='{$it['item_id']}' {$sel}>".h($it['item_Code'].' - '.$it['item_Name'])."</option>";
        } ?>
      </select>
    </div>
    <div class="col-sm-2">
      <label class="small text-muted">View</label>
      <select name="view" class="form-control">
        <option value="summary" <?=$view==='summary'?'selected':''?>>Summary</option>
        <option value="detail" <?=$view==='detail'?'selected':''?>>Detail</option>
      </select>
    </div>
    <div class="col-sm-12" style="margin-top:8px;display:flex;gap:6px;justify-content:flex-end;">
      <button type="submit" class="btn btn-primary btn-sm2"><i class="fa fa-filter"></i> Apply</button>
      <?php if($cust>0 && empty($errors)){ $qs=$_GET; $qs['export']='csv'; ?>
        <a href="customer_item_ledger_report.php?<?=h(http_build_query($qs))?>" class="btn btn-success btn-sm2"><i class="fa fa-download"></i> CSV</a>
        <button type="button" class="btn btn-info btn-sm2" onclick="window.print();"><i class="fa fa-print"></i> Print</button>
      <?php } ?>
    </div>
  </form>

  <?php if($cust>0 && empty($errors)){ ?>
    <?php if($view==='summary'){ ?>
      <div class="note">
        Columns simplified: Opening / Purchase In / Closing removed as requested.
        Net Movement = Sale Return In − (Sale Out + Sample Out + Claim Out) for the selected period only.
      </div>
      <div class="table-responsive" style="margin-top:8px;">
        <table class="table table-bordered">
          <thead>
            <tr>
              <th style="width:140px;">Code</th>
              <th>Item</th>
              <th class="text-center" style="width:110px;">Sale Out</th>
              <th class="text-center" style="width:110px;">Sample Out</th>
              <th class="text-center" style="width:110px;">Claim Out</th>
              <th class="text-center" style="width:120px;">Sale Return In</th>
              <th class="text-center" style="width:110px;">Net Movement</th>
            </tr>
          </thead>
          <tbody>
            <?php if(!$data_summary){ ?>
              <tr><td colspan="7" class="text-center text-muted">No data for selected filters.</td></tr>
            <?php } else {
                $tot_sale=$tot_sample=$tot_claim=$tot_ret=$tot_net=0;
                foreach($data_summary as $r){
                    $tot_sale   += $r['sale_out'];
                    $tot_sample += $r['sample_out'];
                    $tot_claim  += $r['claim_out'];
                    $tot_ret    += $r['sale_return_in'];
                    $tot_net    += $r['net_movement'];
                    echo "<tr>
                      <td>".h($r['item_Code'])."</td>
                      <td>".h($r['item_Name'])."</td>
                      <td class='text-center text-danger'>".number_format($r['sale_out'],2)."</td>
                      <td class='text-center text-danger'>".number_format($r['sample_out'],2)."</td>
                      <td class='text-center text-danger'>".number_format($r['claim_out'],2)."</td>
                      <td class='text-center text-success'>".number_format($r['sale_return_in'],2)."</td>
                      <td class='text-center ".($r['net_movement']<0?'text-danger':'text-success')."'>".number_format($r['net_movement'],2)."</td>
                    </tr>";
                }
                echo "<tr style='font-weight:700;background:#f1f5f9'>
                  <td colspan='2' class='text-right'>Totals</td>
                  <td class='text-center'>".number_format($tot_sale,2)."</td>
                  <td class='text-center'>".number_format($tot_sample,2)."</td>
                  <td class='text-center'>".number_format($tot_claim,2)."</td>
                  <td class='text-center'>".number_format($tot_ret,2)."</td>
                  <td class='text-center ".($tot_net<0?'text-danger':'text-success')."'>".number_format($tot_net,2)."</td>
                </tr>";
            } ?>
          </tbody>
        </table>
      </div>
    <?php } else { // detail ?>
      <div class="note">
        Detail view unchanged. It still shows each movement with running (relationship) balance per item.
      </div>
      <div class="table-responsive" style="margin-top:8px;">
        <table class="table table-bordered">
          <thead>
            <tr>
              <th style="width:70px;">Item ID</th>
              <th style="width:110px;">Date</th>
              <th style="width:100px;">Type</th>
              <th style="width:120px;">Ref #</th>
              <th class="text-center" style="width:90px;">Qty In</th>
              <th class="text-center" style="width:90px;">Qty Out</th>
              <th class="text-center" style="width:110px;">Running</th>
            </tr>
          </thead>
          <tbody>
            <?php
            if(!$data_detail){
                echo "<tr><td colspan='7' class='text-center text-muted'>No movements for selected filters.</td></tr>";
            } else {
                $printedOpening = [];
                foreach($data_detail as $r){
                    $iid=(int)$r['item_id'];
                    if(!isset($printedOpening[$iid])){
                        echo "<tr style='background:#fafafa'>
                          <td>".h($iid)."</td>
                          <td colspan='5' class='text-right'><strong>Opening (Internal)</strong></td>
                          <td class='text-center'><strong>".number_format($r['_running'] - ($r['qty_in'] - $r['qty_out']),2)."</strong></td>
                        </tr>";
                        $printedOpening[$iid]=true;
                    }
                    $cls = $r['qty_out']>0 ? 'badge-out' : ($r['qty_in']>0?'badge-in':'');
                    echo "<tr>
                      <td>".h($iid)."</td>
                      <td>".h($r['t_date'])."</td>
                      <td><span class='badge-type {$cls}'>".h($r['t_type'])."</span></td>
                      <td>".h($r['ref_no'])."</td>
                      <td class='text-center'>".number_format($r['qty_in'],2)."</td>
                      <td class='text-center'>".number_format($r['qty_out'],2)."</td>
                      <td class='text-center'>".number_format($r['_running'],2)."</td>
                    </tr>";
                }
            }
            ?>
          </tbody>
        </table>
      </div>
    <?php } ?>

    <?php if($debug){
        echo "<div class='debug'><strong>DEBUG</strong>";
        echo "\nOpen Date: {$open_date}";
        echo "\nApplied Customer: {$cust}";
        echo "\nView: {$view}";
        echo "\nItem Filter: ".($item_id>0?$item_id:'ALL');
        echo "\nSummary Count: ".count($data_summary);
        echo "\nDetail Count: ".count($data_detail);
        echo "</div>";
    } ?>
  <?php } else { ?>
    <div class="text-muted" style="margin-top:8px;">Select a customer, date range (and optionally item) then click Apply.</div>
  <?php } ?>
</div>
</article>
</div>
</section>
</div>

<?php include("inc/footer.php"); ?>
<?php include("inc/scripts.php"); ?>