<?php
include('sessionCheck.php');
include('connection.php');
include('functions.php');
require_once ("inc/init.php");
require_once ("inc/config.ui.php");

$page_title = "Edit Sample / Claim";
include ("inc/header.php");
include ("inc/nav.php");

$u_id      = (int)($_SESSION['u_id'] ?? 0);
$branch_id = (int)($_SESSION['branch_id'] ?? 0);

function h($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }
function qall($con,$sql){ $res=mysqli_query($con,$sql); $rows=[]; if($res){ while($r=mysqli_fetch_assoc($res)) $rows[]=$r; mysqli_free_result($res);} return $rows; }

$s_id = isset($_GET['s_id']) ? (int)$_GET['s_id'] : 0;
if ($s_id <= 0) { echo "<div class='alert alert-danger'>Invalid document.</div>"; include ("inc/footer.php"); include ("inc/scripts.php"); exit; }

// Load header
$hdr = null;
$r = qall($con, "SELECT s_id, s_Number, s_Date, s_SaleMode, client_id, s_Remarks FROM cust_sale WHERE s_id={$s_id} AND branch_id={$branch_id} AND s_SaleMode IN ('sample','claim') LIMIT 1");
if ($r) $hdr = $r[0];
if (!$hdr) { echo "<div class='alert alert-danger'>Document not found.</div>"; include ("inc/footer.php"); include ("inc/scripts.php"); exit; }

// Load details
$rows = qall($con, "
SELECT d.sd_id, d.item_id, d.item_Qty, i.item_Code, i.item_Name
FROM cust_sale_detail d
LEFT JOIN adm_item i ON i.item_id = d.item_id
WHERE d.s_id={$s_id}
ORDER BY d.sd_id
");

// Dropdowns
$customers = qall($con, "SELECT account_id, account_title FROM accounts_chart WHERE status='active' ORDER BY account_title");
$items = get_ActiveItems();

$msg = '';

// Save changes
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['save'])) {
    $doc_type   = $_POST['doc_type'] === 'claim' ? 'claim' : 'sample';
    $client_id  = (int)($_POST['client_id'] ?? 0);
    $s_Date     = preg_match('/^\d{4}-\d{2}-\d{2}$/', ($_POST['s_Date'] ?? '')) ? $_POST['s_Date'] : date('Y-m-d');
    $remarks    = trim($_POST['s_Remarks'] ?? '');
    $items_ids  = $_POST['item_id'] ?? [];
    $qtys       = $_POST['item_Qty'] ?? [];

    $newRows = [];
    foreach ($items_ids as $k => $iid) {
        $iid = (int)$iid;
        $q   = (float)($qtys[$k] ?? 0);
        if ($iid > 0 && $q > 0) $newRows[] = ['item_id'=>$iid, 'qty'=>$q];
    }

    if ($client_id <= 0) {
        $msg = "<div class='alert alert-danger'>Please select a Customer.</div>";
    } elseif (count($newRows) === 0) {
        $msg = "<div class='alert alert-danger'>Please add at least one item with quantity.</div>";
    } else {
        // Update header (keep s_Number as-is)
        $sqlU = sprintf(
            "UPDATE cust_sale SET s_Date='%s', client_id=%d, s_SaleMode='%s', s_Remarks='%s', s_TotalItems=%d, u_id=%d WHERE s_id=%d AND branch_id=%d",
            mysqli_real_escape_string($con,$s_Date),
            $client_id,
            mysqli_real_escape_string($con,$doc_type),
            mysqli_real_escape_string($con,$remarks),
            count($newRows),
            $u_id,
            $s_id,
            $branch_id
        );
        if (!mysqli_query($con,$sqlU)) {
            $msg = "<div class='alert alert-danger'>Failed to update header: ".h(mysqli_error($con))."</div>";
        } else {
            // Replace details
            mysqli_query($con, "DELETE FROM cust_sale_detail WHERE s_id={$s_id}");
            $ok = true;
            foreach ($newRows as $r1) {
                $iid = (int)$r1['item_id']; $qty = (float)$r1['qty'];
                $sqlD = sprintf(
                    "INSERT INTO cust_sale_detail (s_id, client_id, sd_Date, item_id, item_Packings, item_BarCode, item_IMEI, item_Qty, item_SalePrice, item_CostPrice, item_InvoiceAmount, item_SaleScheme, item_DiscountPercentage, item_DiscountPrice, item_SaleExtraAmount, item_NetPrice, sd_CreatedOn, item_discount_amount_per_item)
                     VALUES (%d, %d, '%s', %d, 0, NULL, '', %s, 0, 0, 0, 0, 0, 0, 0, 0, NOW(), 0)",
                    $s_id, $client_id, mysqli_real_escape_string($con,$s_Date), $iid, $qty
                );
                if (!mysqli_query($con,$sqlD)) { $ok=false; $msg="<div class='alert alert-danger'>Failed to save detail: ".h(mysqli_error($con))."</div>"; break; }
            }
            if ($ok) {
                $_SESSION['msg'] = "<div class='alert alert-success'>Document updated successfully.</div>";
                header("Location: claim_sample_add.php");
                exit;
            }
        }
    }

    // Reload rows for display if errors
    $rows = qall($con, "
    SELECT d.sd_id, d.item_id, d.item_Qty, i.item_Code, i.item_Name
    FROM cust_sale_detail d
    LEFT JOIN adm_item i ON i.item_id = d.item_id
    WHERE d.s_id={$s_id}
    ORDER BY d.sd_id
    ");
}
?>
<div id="main" role="main">
<?php $breadcrumbs["Sales"] = ""; $breadcrumbs["Edit Sample / Claim"] = ""; include("inc/ribbon.php"); ?>

<style>
body { background:#f6f8fb; }
.card { background:#fff; border-radius:10px; box-shadow:0 2px 12px rgba(0,0,0,.06); padding:18px; margin-top:18px; border:1px solid #eef2f7; }
.section-title { font-size:18px; font-weight:800; color:#1b3556; border-bottom:2px solid #e8edf4; padding-bottom:6px; margin-bottom:12px; }
.table thead th { background:#e9f3fa; font-weight:700; }
.actions .btn{ margin: 0 2px 4px 0; }
</style>

<div id="content">
<section id="widget-grid">
<article class="col-sm-12 col-md-10 col-lg-10 col-lg-offset-1">
  <div class="card">
    <div class="section-title"><i class="fa fa-pencil"></i> Edit Sample / Claim</div>
    <?php if(!empty($msg)) echo $msg; if(!empty($_SESSION['msg'])){ echo $_SESSION['msg']; unset($_SESSION['msg']); } ?>
    <form id="edit-form" method="post" action="">
      <input type="hidden" name="save" value="1">
      <div class="row">
        <div class="col-sm-3">
          <label class="form-label">Document Type</label>
          <select name="doc_type" id="doc_type" class="form-control" required>
            <option value="sample" <?= $hdr['s_SaleMode']==='sample'?'selected':''; ?>>Sample</option>
            <option value="claim" <?= $hdr['s_SaleMode']==='claim'?'selected':''; ?>>Claim</option>
          </select>
        </div>
        <div class="col-sm-4">
          <label class="form-label">Customer</label>
          <select name="client_id" id="client_id" class="form-control" required>
            <?php foreach($customers as $c): ?>
              <option value="<?=$c['account_id']?>" <?= $hdr['client_id']==$c['account_id']?'selected':''; ?>><?=h($c['account_title'])?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-sm-3">
          <label class="form-label">Date</label>
          <input type="date" name="s_Date" id="s_Date" class="form-control" value="<?=h($hdr['s_Date'])?>" required>
        </div>
        <div class="col-sm-2">
          <label class="form-label">Doc #</label>
          <input type="text" class="form-control" value="<?=h($hdr['s_Number'])?>" readonly>
        </div>
      </div>

      <div class="row" style="margin-top:10px;">
        <div class="col-sm-4">
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
          <label class="form-label" style="visibility:hidden;">add</label>
          <button type="button" class="btn btn-primary btn-sm" id="add_btn" onclick="addToTable()"><i class="fa fa-plus"></i> Add</button>
        </div>
      </div>

      <div class="table-responsive" style="margin-top:10px;">
        <table class="table table-bordered" id="tbl">
          <thead>
            <tr>
              <th style="width:18%;">Code</th>
              <th>Product</th>
              <th style="width:12%;">Qty</th>
              <th style="width:12%;">Action</th>
            </tr>
          </thead>
          <tbody>
            <tr id="tpl" style="display:none;">
              <td class="c_code"></td>
              <td class="c_name"></td>
              <td><input type="number" class="c_qty form-control" min="1" value="1"></td>
              <td><button type="button" class="btn btn-danger btn-sm" onclick="delRow(this)">Delete</button></td>
              <input type="hidden" name="item_id[]">
              <input type="hidden" name="item_Qty[]">
            </tr>
            <?php foreach ($rows as $r): ?>
              <tr>
                <td class="c_code"><?=h($r['item_Code'])?></td>
                <td class="c_name"><?=h($r['item_Name'])?></td>
                <td><input type="number" class="c_qty form-control" min="1" value="<?=h($r['item_Qty'])?>" oninput="syncRow(this)"></td>
                <td><button type="button" class="btn btn-danger btn-sm" onclick="delRow(this)">Delete</button></td>
                <input type="hidden" name="item_id[]" value="<?=$r['item_id']?>">
                <input type="hidden" name="item_Qty[]" value="<?=h($r['item_Qty'])?>">
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <div class="row">
        <div class="col-sm-8">
          <label class="form-label">Remarks</label>
          <textarea name="s_Remarks" id="s_Remarks" class="form-control" rows="2" placeholder="Optional note"><?=h($hdr['s_Remarks'])?></textarea>
        </div>
        <div class="col-sm-4" style="display:flex; align-items:flex-end; justify-content:flex-end; gap:8px;">
          <button type="button" class="btn btn-success btn-sm" onclick="saveForm()"><i class="fa fa-save"></i> Update</button>
          <a class="btn btn-default btn-sm" href="claim_sample_add.php"><i class="fa fa-arrow-left"></i> Back</a>
        </div>
      </div>
    </form>
  </div>
</article>
</section>
</div>
</div>

<?php include ("inc/footer.php"); ?>
<?php include ("inc/scripts.php"); ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
// Build maps
var productMap = <?php
  $nameToId = []; $codeById = [];
  foreach ($items as $it) { $nameToId[strtolower($it['item_Name'])]=(int)$it['item_id']; $codeById[(int)$it['item_id']]=$it['item_Code']; }
  echo json_encode($nameToId, JSON_UNESCAPED_UNICODE);
?>;
var productCodeById = <?php echo json_encode($codeById, JSON_UNESCAPED_UNICODE); ?>;

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
  row.find('.c_qty').val(qty).on('input', function(){ syncRow(this); });
  row.find('input[name="item_id[]"]').val(id);
  row.find('input[name="item_Qty[]"]').val(qty);
  $("#tbl tbody").prepend(row);

  $("#ex_item, #ex_itemcode, #ex_qty").val('');
  $("#ex_item").focus();
}

function delRow(btn){ $(btn).closest('tr').remove(); }
function syncRow(inp){
  var q = parseFloat($(inp).val() || '0'); if (q < 1) { q = 1; $(inp).val(1); }
  $(inp).closest('tr').find('input[name="item_Qty[]"]').val(q);
}
function saveForm(){
  var count = $("#tbl tbody tr").not("#tpl").length;
  if (count < 1) { alert('Please add at least one item.'); return; }
  $("#tbl tbody tr").not("#tpl").each(function(){
    var q = parseFloat($(this).find('.c_qty').val() || '0');
    $(this).find('input[name="item_Qty[]"]').val(q);
  });
  if (!$("#client_id").val()) { alert('Please select a customer.'); $("#client_id").focus(); return; }
  if (!$("#s_Date").val()) { alert('Please select a date.'); $("#s_Date").focus(); return; }
  $("#edit-form").submit();
}
</script>