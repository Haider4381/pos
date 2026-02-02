<?php
include('sessionCheck.php');
include('connection.php');
include('functions.php');

$branch_id = (int)($_SESSION['branch_id'] ?? 0);
$s_id = isset($_GET['s_id']) ? (int)$_GET['s_id'] : 0;

function h($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }
function qall($con,$sql){ $res=mysqli_query($con,$sql); $rows=[]; if($res){ while($r=mysqli_fetch_assoc($res)) $rows[]=$r; mysqli_free_result($res);} return $rows; }

$hdr = null;
$r = qall($con, "
SELECT s.s_id, s.s_Number, s.s_Date, s.s_SaleMode, s.client_id, ac.account_title AS customer, s.s_Remarks
FROM cust_sale s
LEFT JOIN accounts_chart ac ON ac.account_id = s.client_id
WHERE s.s_id={$s_id} AND s.branch_id={$branch_id} AND s.s_SaleMode IN ('sample','claim')
LIMIT 1
");
if ($r) $hdr = $r[0];
if (!$hdr) { die('Invalid document.'); }

$rows = qall($con, "
SELECT d.item_id, d.item_Qty, i.item_Code, i.item_Name
FROM cust_sale_detail d
LEFT JOIN adm_item i ON i.item_id = d.item_id
WHERE d.s_id={$s_id}
ORDER BY d.sd_id
");

$br = null;
$b = qall($con, "SELECT branch_Name, branch_Address, branch_Phone1 FROM adm_branch WHERE branch_id={$branch_id} LIMIT 1");
if ($b) $br = $b[0];
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Print - <?=h($hdr['s_Number'])?></title>
<style>
body{ font-family: Arial, sans-serif; margin:20px; }
.header{ text-align:center; }
.header h2{ margin:0 0 4px 0; }
.meta{ margin-top:10px; font-size:13px; }
.table{ width:100%; border-collapse:collapse; margin-top:12px; }
.table th, .table td{ border:1px solid #444; padding:6px; font-size:13px; }
.footer{ margin-top:16px; font-size:12px; }
.badge{ display:inline-block; padding:2px 6px; border:1px solid #444; border-radius:6px; font-size:12px; }
</style>
</head>
<body onload="window.print();">
  <div class="header">
    <h2><?= h($br['branch_Name'] ?? 'Store') ?></h2>
    <div><?= h($br['branch_Address'] ?? '') ?></div>
    <div><?= h($br['branch_Phone1'] ?? '') ?></div>
    <hr>
    <div class="badge"><?= $hdr['s_SaleMode']==='sample' ? 'Sample' : 'Claim' ?></div>
  </div>
  <div class="meta">
    <strong>Doc #:</strong> <?=h($hdr['s_Number'])?> &nbsp;&nbsp;
    <strong>Date:</strong> <?=h($hdr['s_Date'])?> &nbsp;&nbsp;
    <strong>Customer:</strong> <?=h($hdr['customer'])?>
  </div>
  <table class="table">
    <thead>
      <tr>
        <th style="width:20%;">Code</th>
        <th>Product</th>
        <th style="width:15%; text-align:right;">Qty</th>
      </tr>
    </thead>
    <tbody>
      <?php $tq=0; foreach($rows as $r): $tq += (float)$r['item_Qty']; ?>
      <tr>
        <td><?=h($r['item_Code'])?></td>
        <td><?=h($r['item_Name'])?></td>
        <td style="text-align:right;"><?=number_format((float)$r['item_Qty'],2)?></td>
      </tr>
      <?php endforeach; ?>
      <tr>
        <td colspan="2" style="text-align:right;"><strong>Total Qty</strong></td>
        <td style="text-align:right;"><strong><?=number_format($tq,2)?></strong></td>
      </tr>
    </tbody>
  </table>
  <?php if(!empty($hdr['s_Remarks'])): ?>
  <div class="footer"><strong>Remarks:</strong> <?=h($hdr['s_Remarks'])?></div>
  <?php endif; ?>
</body>
</html>