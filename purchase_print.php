<?php
include('sessionCheck.php');
include('connection.php');

$branch_id = isset($_SESSION['branch_id']) ? intval($_SESSION['branch_id']) : 0;
$currency_symbol = isset($_SESSION['currency_symbol']) ? $_SESSION['currency_symbol'] : 'Rs';

// Basic guard
if (!isset($_GET['p_id'])) {
    echo "Invalid Request";
    die();
}
$p_id = intval($_GET['p_id']);

// Check if adm_brand table exists (some databases don't have it)
$hasBrand = false;
$chk = mysqli_query($con, "SHOW TABLES LIKE 'adm_brand'");
if ($chk) {
    $hasBrand = mysqli_num_rows($chk) > 0;
    mysqli_free_result($chk);
}

// Build dynamic brand join/field
$brandJoin  = $hasBrand ? "LEFT JOIN adm_brand AS B ON B.brand_id = I.brand_id" : "";
$brandField = $hasBrand ? "B.brand_Name" : "'' AS brand_Name";

// Query
$sQ = "SELECT
        P.p_id, P.branch_id, P.p_Number, P.p_Date, P.p_CreatedOn, P.p_BillNo, P.p_VendorRemarks,
        P.sup_id, P.p_TotalAmount, P.p_DiscountPrice, P.p_NetAmount, P.p_Remarks,
        AC.account_title AS supplier_name, AC.phone AS supplier_phone, AC.address AS supplier_address,
        PD.item_id, PD.item_BarCode, PD.item_IMEI, PD.item_Qty, PD.item_Rate AS item_SalePrice, PD.item_TotalAmount,
        PD.item_DiscountPercentage, PD.item_DiscountAmount, PD.item_NetAmount AS item_NetPrice,
        I.item_Name, I.item_Code AS item_Code, $brandField
      FROM adm_purchase AS P
      INNER JOIN adm_purchase_detail AS PD ON PD.p_id = P.p_id
      INNER JOIN accounts_chart AS AC ON AC.account_id = P.sup_id
      LEFT JOIN adm_item AS I ON I.item_id = PD.item_id
      $brandJoin
      WHERE P.p_id = $p_id AND P.branch_id = $branch_id
      ORDER BY I.item_id";

$sRes = mysqli_query($con, $sQ);
if ($sRes === false) {
    // Log exact SQL error for debugging
    error_log("purchase_print query error: " . mysqli_error($con) . " -- SQL: " . $sQ);
    echo "No Record Found";
    die();
}
if (mysqli_num_rows($sRes) < 1) {
    echo "No Record Found";
    die();
}
$sRow = [];
while ($r = mysqli_fetch_assoc($sRes)) {
    $sRow[] = $r;
}
mysqli_free_result($sRes);

$branch_id_sale = intval($sRow[0]['branch_id']);

// Paid amount
$paid_amount = 0.0;
$paidSql = "SELECT IFNULL(SUM(pp_Amount),0) AS paid FROM adm_purchase_payment WHERE p_id = $p_id";
$paidQ = mysqli_query($con, $paidSql);
if ($paidQ) {
    if ($paidRow = mysqli_fetch_assoc($paidQ)) {
        $paid_amount = floatval($paidRow['paid']);
    }
    mysqli_free_result($paidQ);
}

// Branch info
$branchQ = "SELECT * FROM adm_branch WHERE branch_id = $branch_id_sale";
$branchRes = mysqli_query($con, $branchQ);
$branchRow = $branchRes ? mysqli_fetch_assoc($branchRes) : null;
if ($branchRes) mysqli_free_result($branchRes);

// Logo rendering (safe)
$logo_source = '';
if ($branchRow && !empty($branchRow['branch_Logo'])) {
    // Try to resolve filesystem path for existence check
    $fsPath = $_SERVER['DOCUMENT_ROOT'] . '/' . ltrim($branchRow['branch_Logo'], '/');
    if (is_file($fsPath)) {
        $logo_src = '/' . ltrim($branchRow['branch_Logo'], '/'); // web path
        $logo_source = '<img src="' . htmlspecialchars($logo_src) . '" height="70" style="max-width:100%;">';
    } else {
        // If file not on disk but path/URL present, still show without is_file check
        $logo_source = '<img src="' . htmlspecialchars($branchRow['branch_Logo']) . '" height="70" style="max-width:100%;">';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Print Purchase Invoice</title>
<style>
body, * { margin:0; padding:0; font-family:Arial, Helvetica, sans-serif; }
table { border-collapse: collapse; }
</style>
</head>
<body style="width:7.1in; margin:0 auto;">

<table style="width:7.1in;" align="center" border="0">
  <tbody>
    <tr>
      <td style="width:33%"></td>
      <td style="text-align:center; font-size:16px; width:33%"></td>
      <td style="width:33%; font-size:11px; text-align:right; color:black;">Software Develope By 0300-7537538</td>
    </tr>
  </tbody>
</table>

<table style="width:100%; font-size:15px;" align="center" border="0">
  <tr><td><?= $logo_source ?></td></tr>
  <tr>
    <td style="text-align:center;">
      <h3><?= htmlspecialchars($branchRow['branch_Name'] ?? '') ?></h3>
      <?= htmlspecialchars($branchRow['branch_Address'] ?? '') ?><br />
      <?= htmlspecialchars($branchRow['branch_Phone1'] ?? '') ?>
    </td>
  </tr>
</table>
<hr />

<table style="width:100%; font-size:11px;" align="center" border="0">
  <tr><td colspan="2" style="text-align:center;font-size:30px;"><strong>Purchase Bill</strong></td></tr>
</table>

<table style="width:100%; font-size:11px;" align="center" border="0">
  <tr>
    <td style="width:50%;"><?= htmlspecialchars(date('M d, Y', strtotime($sRow[0]['p_CreatedOn']))) ?></td>
    <td style="width:50%; text-align:right;"><?= htmlspecialchars(date('h:i A', strtotime($sRow[0]['p_CreatedOn']))) ?></td>
  </tr>
  <tr>
    <td>Transaction#</td>
    <td style="text-align:right;"><?= htmlspecialchars($sRow[0]['p_Number']) ?></td>
  </tr>
  <tr>
    <td>Purchse Ref#</td>
    <td style="text-align:right;"><?= htmlspecialchars($sRow[0]['p_BillNo']) ?></td>
  </tr>
  <tr><td colspan="2">&nbsp;</td></tr>
  <tr>
    <td colspan="2"><strong>Supplier: &nbsp;&nbsp;<?= htmlspecialchars($sRow[0]['supplier_name']) ?></strong></td>
  </tr>
  <tr>
    <td colspan="2"><strong>Phone#: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?= htmlspecialchars($sRow[0]['supplier_phone']) ?></strong></td>
  </tr>
  <?php if (!empty($sRow[0]['supplier_address'])) { ?>
  <tr>
    <td colspan="2"><strong>Address: &nbsp;&nbsp;<?= htmlspecialchars($sRow[0]['supplier_address']) ?></strong></td>
  </tr>
  <?php } ?>
</table>

<table align="center" style="width:100%; border:0px solid #666; border-collapse:collapse; font-size:11px; font-family:Verdana, Geneva, sans-serif;" cellpadding="2">
  <tr style="border-bottom:1px solid #666;">
    <td style="text-align:left; width:10%; border-bottom:1px solid #666;"><strong>Qty</strong></td>
    <td style="text-align:center; width:50%; border-bottom:1px solid #666;"><strong>Item Name</strong></td>
    <td style="text-align:center; width:20%; border-bottom:1px solid #666;"><strong>Price</strong></td>
    <td style="text-align:right; width:20%; border-bottom:1px solid #666;"><strong>Total</strong></td>
  </tr>
  <?php
  foreach ($sRow as $r) {
      $qty   = (int)$r['item_Qty'];
      $iname = $r['item_Name'];
      $icode = $r['item_Code'];
      $imei  = $r['item_IMEI'];
      $brand = $r['brand_Name'];
      ?>
      <tr style="border-bottom:0px solid #666;">
        <td style="border:0px solid #666; display:block;"><?= $qty ?></td>
        <td style="border:0px solid #666; text-align:center;">
          <?= htmlspecialchars($iname) ?>
          <?php if (!empty($icode) && $imei !== $icode) { echo '<br>' . htmlspecialchars($icode); } ?>
          <?php if (!empty($imei)  && $imei !== $iname) { echo '<br>' . htmlspecialchars($imei); } ?>
          <?php if (!empty($brand)) { echo '<br><small>' . htmlspecialchars($brand) . '</small>'; } ?>
        </td>
        <td style="text-align:center; border:0px solid #666;"><?= number_format((float)$r['item_SalePrice']) ?></td>
        <td style="text-align:right; border:0px solid #666;"><?= htmlspecialchars($currency_symbol) . number_format((float)$r['item_NetPrice'], 0) ?></td>
      </tr>
  <?php } ?>
</table>

<br />
<table style="width:7.125in; border-collapse:collapse; border-top:1px solid #000; font-size:14px;" align="center" border="0">
  <tr>
    <td style="padding:3px; text-align:right;"><strong>Sub Total: </strong></td>
    <td style="border-bottom:1px solid #000; padding:3px; text-align:right;"><?= htmlspecialchars($currency_symbol) . number_format((float)$sRow[0]['p_TotalAmount'], 2) ?></td>
  </tr>
  <tr>
    <td style="padding:3px; text-align:right;"><strong>Amount Due: </strong></td>
    <td style="border-bottom:1px solid #000; padding:3px; text-align:right;"><?= htmlspecialchars($currency_symbol) . number_format((float)$sRow[0]['p_NetAmount'], 2) ?></td>
  </tr>
  <tr>
    <td style="padding:3px; text-align:right;"><strong>Paid: </strong></td>
    <td style="border-bottom:1px solid #000; padding:3px; text-align:right;"><?= htmlspecialchars($currency_symbol) . number_format((float)$paid_amount, 2) ?></td>
  </tr>
  <tr>
    <td style="padding:3px; text-align:right;"><strong>Remaining: </strong></td>
    <td style="border-bottom:1px solid #000; padding:3px; text-align:right;"><?= htmlspecialchars($currency_symbol) . number_format((float)$sRow[0]['p_NetAmount'] - (float)$paid_amount, 2) ?></td>
  </tr>
</table>

<br />
<strong style="text-align:center; display:block; font-size:13px;">Powered By: websofthouse.net</strong>

<script type="text/javascript">
// window.print();
// setTimeout(function(){window.close();}, 3000);
</script>
</body>
</html>