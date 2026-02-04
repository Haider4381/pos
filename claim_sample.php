<?php
include('sessionCheck.php');
include('connection.php');
include('functions.php');
require_once ("inc/init.php");
require_once ("inc/config.ui.php");

$page_title = "Sample / Claim";
include ("inc/header.php");
include ("inc/nav.php");

$u_id      = (int)($_SESSION['u_id'] ?? 0);
$branch_id = (int)($_SESSION['branch_id'] ?? 0);

// Helpers
function h($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }
function qall($con,$sql){ $res=mysqli_query($con,$sql); $rows=[]; if($res){ while($r=mysqli_fetch_assoc($res)) $rows[]=$r; mysqli_free_result($res);} else { error_log('SQL ERR: '.mysqli_error($con).' -- '.$sql); } return $rows; }
function qone($con,$sql,$def=0){ $res=mysqli_query($con,$sql); if(!$res) { error_log('SQL ERR: '.mysqli_error($con).' -- '.$sql); return $def;} $r=mysqli_fetch_row($res); mysqli_free_result($res); return $r? (float)$r[0] : $def; }

// Dropdowns
$customers = qall($con, "
  SELECT account_id, account_title
  FROM accounts_chart
  WHERE status='active'
    AND account_title NOT IN ('Sale Account','Purchase Account')
  ORDER BY account_title
");

// Items (active)
$items = get_ActiveItems(); // your existing helper returns item rows

$msg = '';

// Save handler
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['save'])) {
    $doc_type   = $_POST['doc_type'] === 'claim' ? 'claim' : 'sample';
    $client_id  = (int)($_POST['client_id'] ?? 0);
    $s_Date     = preg_match('/^\d{4}-\d{2}-\d{2}$/', ($_POST['s_Date'] ?? '')) ? $_POST['s_Date'] : date('Y-m-d');
    $remarks    = trim($_POST['s_Remarks'] ?? '');
    $items_ids  = $_POST['item_id'] ?? [];
    $qtys       = $_POST['item_Qty'] ?? [];

    if ($client_id <= 0) {
        $msg = "<div class='alert alert-danger'>Please select a Customer.</div>";
    } else {
        $rows = [];
        foreach ($items_ids as $k => $iid) {
            $iid = (int)$iid;
            $q   = (float)($qtys[$k] ?? 0);
            if ($iid > 0 && $q > 0) $rows[] = ['item_id'=>$iid, 'qty'=>$q];
        }
        if (count($rows) === 0) {
            $msg = "<div class='alert alert-danger'>Please add at least one item with quantity.</div>";
        } else {
            // Generate doc number series per type
            $prefix = $doc_type === 'claim' ? 'CLM' : 'SMP';
            $nextSr = (int)qone($con, "SELECT IFNULL(MAX(s_NumberSr),0)+1 FROM cust_sale WHERE branch_id={$branch_id} AND s_SaleMode='{$doc_type}'", 1);
            $s_Number = $prefix.$nextSr;

            // Header: zero amounts, only qty movement
            $sqlH = sprintf(
                "INSERT INTO cust_sale (s_Number, s_NumberSr, s_Date, client_id, s_TotalAmount, s_Discount, s_DiscountAmount, s_Tax, s_TaxAmount, s_SaleMode, s_PaidAmount, s_NetAmount, s_Remarks, s_CreatedOn, s_TotalItems, u_id, branch_id, s_PaymentType)
                 VALUES ('%s', %d, '%s', %d, 0, 0, 0, 0, 0, '%s', 0, 0, '%s', NOW(), %d, %d, %d, 'none')",
                mysqli_real_escape_string($con,$s_Number),
                $nextSr,
                mysqli_real_escape_string($con,$s_Date),
                $client_id,
                mysqli_real_escape_string($con,$doc_type),
                mysqli_real_escape_string($con,$remarks),
                count($rows),
                $u_id,
                $branch_id
            );
            if (!mysqli_query($con, $sqlH)) {
                $msg = "<div class='alert alert-danger'>Failed to save document header: ".h(mysqli_error($con))."</div>";
            } else {
                $s_id = (int)mysqli_insert_id($con);
                $ok = true;
                foreach ($rows as $r) {
                    $iid = (int)$r['item_id'];
                    $qty = (float)$r['qty'];
                    $sqlD = sprintf(
                        "INSERT INTO cust_sale_detail (s_id, client_id, sd_Date, item_id, item_Packings, item_BarCode, item_IMEI, item_Qty, item_SalePrice, item_CostPrice, item_InvoiceAmount, item_SaleScheme, item_DiscountPercentage, item_DiscountPrice, item_SaleExtraAmount, item_NetPrice, sd_CreatedOn, item_discount_amount_per_item)
                         VALUES (%d, %d, '%s', %d, 0, NULL, '', %s, 0, 0, 0, 0, 0, 0, 0, 0, NOW(), 0)",
                        $s_id, $client_id, mysqli_real_escape_string($con,$s_Date), $iid, $qty
                    );
                    if (!mysqli_query($con, $sqlD)) { $ok = false; $msg = "<div class='alert alert-danger'>Failed to save detail row: ".h(mysqli_error($con))."</div>"; break; }
                }

                if ($ok) {
                    $_SESSION['msg'] = "<div class='alert alert-success'>".$prefix." document saved (No amount posted). Ref: ".h($s_Number)."</div>";
                    ?>
                    <script>
                        alert("Document saved successfully. Ref: <?=h($s_Number)?>");
                        window.location.href = "<?=h($_SERVER['PHP_SELF'])?>";
                    </script>
                    <?php
                    exit;
                }
            }
        }
    }
}

// Filters for list
$f_from = isset($_GET['from']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['from']) ? $_GET['from'] : date('Y-m-01');
$f_to   = isset($_GET['to'])   && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['to'])   ? $_GET['to']   : date('Y-m-d');
$f_type = isset($_GET['type']) && in_array($_GET['type'], ['all','sample','claim'], true) ? $_GET['type'] : 'all';
$f_cust = isset($_GET['cust']) ? (int)$_GET['cust'] : 0;

$where = "s.branch_id={$branch_id} AND s.s_SaleMode IN ('sample','claim') AND s.s_Date BETWEEN '{$f_from}' AND '{$f_to}'";
if ($f_type !== 'all') $where .= " AND s.s_SaleMode='{$f_type}'";
if ($f_cust > 0) $where .= " AND s.client_id={$f_cust}";

$listSql = "
SELECT
  s.s_id,
  s.s_Number,
  s.s_Date,
  s.s_SaleMode,
  s.client_id,
  ac.account_title AS customer,
  s.s_Remarks,
  s.s_TotalItems,
  u.u_FullName AS created_by,
  COALESCE(d.qty_total,0) AS qty_total
FROM cust_sale s
LEFT JOIN accounts_chart ac ON ac.account_id = s.client_id
LEFT JOIN u_user u ON u.u_id = s.u_id
LEFT JOIN (
  SELECT s_id, SUM(item_Qty) AS qty_total
  FROM cust_sale_detail
  GROUP BY s_id
) d ON d.s_id = s.s_id
WHERE {$where}
ORDER BY s.s_id DESC
LIMIT 300
";
$docs = qall($con, $listSql);
?>
<div id="main" role="main">
<?php $breadcrumbs["Sales"] = ""; $breadcrumbs["Sample / Claim"] = ""; include("inc/ribbon.php"); ?>

<style>
body { background:#f6f8fb; }
.card { background:#fff; border-radius:10px; box-shadow:0 2px 12px rgba(0,0,0,.06); padding:18px; margin-top:18px; border:1px solid #eef2f7; }
.section-title { font-size:18px; font-weight:800; color:#1b3556; border-bottom:2px solid #e8edf4; padding-bottom:6px; margin-bottom:12px; }
.form-label { font-weight:600; color:#36587d; }
.table thead th { background:#e9f3fa; font-weight:700; }
.table td { vertical-align:middle; }
.btn-sm2{ padding:6px 12px; border-radius:6px; }
.small-note { color:#6b7280; font-size:12px; }
.badge-type{display:inline-block;padding:3px 8px;border-radius:10px;font-size:12px}
.badge-sample{background:#eef6ff;color:#0b67c2;border:1px solid #cfe6ff}
.badge-claim{background:#fff4ed;color:#b03500;border:1px solid #ffd8bf}
</style>

<div id="content">
<section id="widget-grid">

<!-- Entry Card -->
<article class="col-sm-12 col-md-10 col-lg-10 col-lg-offset-1">
  <div class="card">
    <div class="section-title"><i class="fa fa-tag"></i> Sample / Claim Issue (Qty only, no amount)</div>
    <?php if(!empty($msg)) echo $msg; if(!empty($_SESSION['msg'])){ echo $_SESSION['msg']; unset($_SESSION['msg']); } ?>
    <form id="doc-form" method="post" action="">
      <input type="hidden" name="save" value="1">
      <div class="row">
        <div class="col-sm-3">
          <label class="form-label">Document Type</label>
          <select name="doc_type" id="doc_type" class="form-control" required>
            <option value="sample">Sample</option>
            <option value="claim">Claim</option>
          </select>
        </div>
        <div class="col-sm-4">
          <label class="form-label">Customer</label>
          <select name="client_id" id="client_id" class="form-control" required>
            <option value="">Select Customer</option>
            <?php foreach($customers as $c): ?>
              <option value="<?=$c['account_id']?>"><?=h($c['account_title'])?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-sm-3">
          <label class="form-label">Date</label>
          <input type="date" name="s_Date" id="s_Date" class="form-control" value="<?=date('Y-m-d')?>" required>
        </div>
      </div>

      <div class="row" style="margin-top:10px;">
        <div class="col-sm-3">
          <label class="form-label">Search Product</label>
          <input list="item_list" id="ex_item" class="form-control" placeholder="Type product name">
          <datalist id="item_list">
            <?php foreach ($items as $it) { echo '<option value="'.h($it['item_Name']).'"></option>'; } ?>
          </datalist>
        </div>
        <div class="col-sm-2">
          <label class="form-label">Product Code</label>
          <input type="text" id="ex_itemcode" class="form-control" placeholder="Auto" readonly>
        </div>
        <div class="col-sm-2">
          <label class="form-label">Qty</label>
          <input type="number" id="ex_qty" class="form-control" min="1" placeholder="Qty">
        </div>
        <div class="col-sm-2">
          <label class="form-label">Rate</label>
          <input type="number" id="ex_rate" class="form-control" value="0.00" step="0.01" readonly>
        </div>
        <div class="col-sm-2">
          <label class="form-label" style="visibility:hidden;">add</label>
          <button type="button" class="btn btn-primary btn-sm2" id="add_btn" onclick="addToTable()"><i class="fa fa-plus"></i> Add</button>
        </div>
      </div>

      <div class="table-responsive" style="margin-top:10px;">
        <table class="table table-bordered" id="tbl">
          <thead>
            <tr>
              <th style="width:18%;">Code</th>
              <th>Product</th>
              <th style="width:12%;">Qty</th>
              <th style="width:12%;">Rate</th>
              <th style="width:12%;">Amount</th>
              <th style="width:10%;">Action</th>
            </tr>
          </thead>
          <tbody>
            <tr id="tpl" style="display:none;">
              <td class="c_code"></td>
              <td class="c_name"></td>
              <td><input type="number" class="c_qty form-control" min="1" value="1"></td>
              <td class="c_rate">0.00</td>
              <td class="c_amt">0.00</td>
              <td><button type="button" class="btn btn-danger btn-sm" onclick="delRow(this)">Delete</button></td>
              <input type="hidden" name="item_id[]">
              <input type="hidden" name="item_Qty[]">
            </tr>
          </tbody>
        </table>
      </div>

      <div class="row">
        <div class="col-sm-8">
          <label class="form-label">Remarks</label>
          <textarea name="s_Remarks" id="s_Remarks" class="form-control" rows="2" placeholder="Optional note"></textarea>
          <div class="small-note" style="margin-top:6px;">
            Note: This document reduces stock only. No amount will be posted in accounts. Detail rows store Qty Out with zero prices.
          </div>
        </div>
        <div class="col-sm-4" style="display:flex; align-items:flex-end; justify-content:flex-end; gap:8px;">
          <button type="button" class="btn btn-success btn-sm2" onclick="saveForm()"><i class="fa fa-save"></i> Save</button>
        </div>
      </div>
    </form>
  </div>
</article>

<!-- List Card -->
<article class="col-sm-12 col-md-10 col-lg-10 col-lg-offset-1">
  <div class="card">
    <div class="section-title"><i class="fa fa-list"></i> Sample / Claim List</div>

    <form class="row" method="get" action="">
      <div class="col-sm-3">
        <label class="form-label">From</label>
        <input type="date" name="from" value="<?=h($f_from)?>" class="form-control">
      </div>
      <div class="col-sm-3">
        <label class="form-label">To</label>
        <input type="date" name="to" value="<?=h($f_to)?>" class="form-control">
      </div>
      <div class="col-sm-3">
        <label class="form-label">Type</label>
        <select name="type" class="form-control">
          <option value="all" <?= $f_type==='all'?'selected':''; ?>>All</option>
          <option value="sample" <?= $f_type==='sample'?'selected':''; ?>>Sample</option>
          <option value="claim" <?= $f_type==='claim'?'selected':''; ?>>Claim</option>
        </select>
      </div>
      <div class="col-sm-3">
        <label class="form-label">Customer</label>
        <select name="cust" class="form-control">
          <option value="0">All</option>
          <?php foreach($customers as $c): ?>
            <option value="<?=$c['account_id']?>" <?= $f_cust==$c['account_id']?'selected':''; ?>><?=h($c['account_title'])?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-sm-12" style="margin-top:8px; display:flex; gap:8px; justify-content:flex-end;">
        <button type="submit" class="btn btn-primary btn-sm2"><i class="fa fa-filter"></i> Apply</button>
      </div>
    </form>

    <div class="table-responsive" style="margin-top:10px;">
      <table class="table table-bordered">
        <thead>
          <tr>
            <th style="width:110px;">Date</th>
            <th style="width:120px;">Doc #</th>
            <th style="width:90px;">Type</th>
            <th>Customer</th>
            <th style="width:90px;" class="text-center">Items</th>
            <th style="width:100px;" class="text-center">Total Qty</th>
            <th>Remarks</th>
            <th style="width:120px;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (count($docs) === 0) { ?>
            <tr><td colspan="8" class="text-center text-muted">No documents found for selected filters.</td></tr>
          <?php } else { foreach ($docs as $d) { ?>
            <tr>
              <td><?= h($d['s_Date']) ?></td>
              <td><?= h($d['s_Number']) ?></td>
              <td>
                <?php if ($d['s_SaleMode']==='sample') { ?>
                  <span class="badge-type badge-sample">Sample</span>
                <?php } else { ?>
                  <span class="badge-type badge-claim">Claim</span>
                <?php } ?>
              </td>
              <td><?= h($d['customer']) ?></td>
              <td class="text-center"><?= (int)$d['s_TotalItems'] ?></td>
              <td class="text-center"><?= number_format((float)$d['qty_total'],2) ?></td>
              <td><?= h($d['s_Remarks']) ?></td>
              <td>
                <!-- Placeholder actions; wire up when print/view pages are added -->
                <!-- <a class="btn btn-info btn-xs" href="claim_sample_print.php?s_id=<?= (int)$d['s_id'] ?>" target="_blank"><i class="fa fa-print"></i> Print</a> -->
                <!-- <a class="btn btn-default btn-xs" href="claim_sample_view.php?s_id=<?= (int)$d['s_id'] ?>"><i class="fa fa-eye"></i> View</a> -->
                <span class="small-note">by <?= h($d['created_by'] ?? '') ?></span>
              </td>
            </tr>
          <?php } } ?>
        </tbody>
      </table>
      <div class="small-note">Showing max 300 recent documents. Use filters to narrow down.</div>
    </div>
  </div>
</article>

</section>
</div>
</div>


<?php include ("inc/scripts.php"); ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
// Build name->id + code map for items
var productMap = <?php
  $nameToId = []; $codeById = [];
  foreach ($items as $it) {
    $nameToId[strtolower($it['item_Name'])] = (int)$it['item_id'];
    $codeById[(int)$it['item_id']] = $it['item_Code'];
  }
  echo json_encode($nameToId, JSON_UNESCAPED_UNICODE);
?>;
var productCodeById = <?php echo json_encode($codeById, JSON_UNESCAPED_UNICODE); ?>;

// When selecting product, fetch code. Rate is always 0 here.
function onSelectItem(){
  var name = ($("#ex_item").val() || '').trim().toLowerCase();
  var id = productMap[name];
  if (!id) { $("#ex_itemcode").val(''); return; }
  $("#ex_itemcode").val(productCodeById[id] || '');
  if (!$("#ex_qty").val()) $("#ex_qty").val('1');
}
$("#ex_item").on('change input', onSelectItem);

function addToTable(){
  var name = ($("#ex_item").val() || '').trim();
  var id = productMap[name.toLowerCase()];
  if (!id) { alert('Select a valid product.'); $("#ex_item").focus(); return; }
  var code = $("#ex_itemcode").val() || '';
  var qty  = parseFloat($("#ex_qty").val() || '0');
  if (qty <= 0) { alert('Enter quantity.'); $("#ex_qty").focus(); return; }

  var row = $("#tpl").clone().show().removeAttr('id');
  row.find('.c_code').text(code);
  row.find('.c_name').text(name);
  row.find('.c_qty').val(qty).on('input', function(){ recalcRow(row); });
  row.find('.c_rate').text('0.00');
  row.find('.c_amt').text('0.00');
  row.find('input[name="item_id[]"]').val(id);
  row.find('input[name="item_Qty[]"]').val(qty);
  $("#tbl tbody").prepend(row);

  // Reset controls
  $("#ex_item, #ex_itemcode, #ex_qty").val('');
  $("#ex_item").focus();
}

function recalcRow(r){
  var q = parseFloat(r.find('.c_qty').val() || '0');
  if (q < 1) { q = 1; r.find('.c_qty').val(1); }
  r.find('input[name="item_Qty[]"]').val(q);
}

function delRow(btn){ $(btn).closest('tr').remove(); }

function saveForm(){
  var count = $("#tbl tbody tr").not("#tpl").length;
  if (count < 1) { alert('Please add at least one item.'); return; }
  $("#tbl tbody tr").not("#tpl").each(function(){
    var q = parseFloat($(this).find('.c_qty').val() || '0');
    $(this).find('input[name="item_Qty[]"]').val(q);
  });
  if (!$("#client_id").val()) { alert('Please select a customer.'); $("#client_id").focus(); return; }
  if (!$("#s_Date").val()) { alert('Please select a date.'); $("#s_Date").focus(); return; }
  $("#doc-form").submit();
}

// Keyboard flow
$("#ex_item").on('keydown', function(e){ if(e.key==='Enter'){ e.preventDefault(); $("#ex_qty").focus(); }});
$("#ex_qty").on('keydown',  function(e){ if(e.key==='Enter'){ e.preventDefault(); $("#add_btn").click(); }});
</script>
<?php //include ("inc/footer.php"); ?>