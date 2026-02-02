<?php
include('sessionCheck.php');
include('connection.php');

error_reporting(E_ALL);
ini_set('display_errors', isset($_GET['debug']) && $_GET['debug'] ? '1' : '0');

// Optional: thermal width (kept for compatibility)
$thermal_width = isset($_GET['thermal_width']) ? intval($_GET['thermal_width']) : 80;
if ($thermal_width <= 0) $thermal_width = 80;

// Font size (in px). Change via ?fs=9 etc.
$font_size = isset($_GET['fs']) ? intval($_GET['fs']) : 10;
if ($font_size < 7)  $font_size = 7;
if ($font_size > 16) $font_size = 16;

// Helper to convert possible logo path to data URI or return URL if absolute
function logo_data_uri($src_candidate) {
    if (!$src_candidate) return '';
    $src_candidate = trim($src_candidate);
    if (preg_match('#^https?://#i', $src_candidate)) {
        return $src_candidate; // absolute URL - return as-is
    }
    $paths_to_try = [
        $_SERVER['DOCUMENT_ROOT'] . '/' . ltrim($src_candidate, '/'),
        __DIR__ . '/' . ltrim($src_candidate, './'),
        __DIR__ . '/../' . ltrim($src_candidate, './'),
        __DIR__ . '/img/' . ltrim($src_candidate, './'),
    ];
    foreach ($paths_to_try as $p) {
        if (file_exists($p) && is_readable($p)) {
            $mime = mime_content_type($p) ?: 'image/png';
            $data = base64_encode(file_get_contents($p));
            return "data:{$mime};base64,{$data}";
        }
    }
    return '';
}

function numberToWords($number) {
    $hyphen='-'; $conjunction=' and '; $separator=', '; $decimal=' point ';
    $dictionary=[0=>'zero',1=>'one',2=>'two',3=>'three',4=>'four',5=>'five',6=>'six',7=>'seven',8=>'eight',9=>'nine',
        10=>'ten',11=>'eleven',12=>'twelve',13=>'thirteen',14=>'fourteen',15=>'fifteen',16=>'sixteen',17=>'seventeen',
        18=>'eighteen',19=>'nineteen',20=>'twenty',30=>'thirty',40=>'forty',50=>'fifty',60=>'sixty',70=>'seventy',
        80=>'eighty',90=>'ninety',100=>'hundred',1000=>'thousand',1000000=>'million',1000000000=>'billion'
    ];
    if (!is_numeric($number)) { return false; }
    $number = (string)$number;
    if (strpos($number, '.') !== false) { [$integer, $fraction] = explode('.', $number); } else { $integer = $number; $fraction = null; }
    $integer = ltrim($integer, '0'); if ($integer === '') $integer = '0';
    switch (true) {
        case $integer < 21: $string = $dictionary[$integer]; break;
        case $integer < 100:
            $tens = ((int)($integer/10))*10; $units = $integer % 10;
            $string = $dictionary[$tens]; if ($units) { $string .= $hyphen.$dictionary[$units]; } break;
        case $integer < 1000:
            $hundreds = floor($integer/100); $remainder = $integer % 100;
            $string = $dictionary[$hundreds].' '.$dictionary[100];
            if ($remainder) { $string .= $conjunction . numberToWords($remainder); }
            break;
        default:
            $baseUnit = pow(1000, floor(log($integer, 1000)));
            $numBaseUnits = floor($integer/$baseUnit);
            $remainder = $integer % $baseUnit;
            $string = numberToWords($numBaseUnits).' '.$dictionary[$baseUnit];
            if ($remainder) { $string .= $remainder < 100 ? $conjunction : $separator; $string .= numberToWords($remainder); }
            break;
    }
    if ($fraction !== null && is_numeric($fraction)) {
        if ((int)$fraction != 0) {
            $string .= $decimal;
            $words = [];
            foreach (str_split((string)$fraction) as $n) { $words[] = $dictionary[$n]; }
            $string .= implode(' ', $words);
        }
    }
    return ucwords($string);
}

if (!isset($_GET['s_id'])) { echo "Invalid Request"; exit; }
$s_id = (int) $_GET['s_id'];

// Fetch sale and details
$sQ = "
SELECT
    S.s_id, S.branch_id, S.s_Number, S.s_TotalAmount, S.s_NetAmount, S.s_Tax, S.s_TaxAmount, S.s_SaleMode,
    S.s_PaymentType, S.s_Discount, S.s_DiscountAmount, S.s_PaidAmount, S.s_CreatedOn, S.s_totalitems, S.s_RemarksExternal,
    C.account_id AS client_id, C.account_title AS client_Name, C.phone AS client_Phone, C.address AS client_Address,
    SD.item_IMEI, SD.item_Qty, SD.item_SalePrice, SD.item_NetPrice, SD.item_id, SD.item_DiscountPrice, SD.item_DiscountPercentage, SD.item_discount_amount_per_item,
    I.item_Name, I.item_Code, I.item_Image,
    u_user.u_FullName as username,
    B.branch_CustomerPaymentPolicy, B.branch_ShowPolicy,
    B.branch_Name, B.branch_Address, B.branch_Phone1, B.branch_Phone2, B.branch_Logo
FROM cust_sale AS S
INNER JOIN cust_sale_detail AS SD ON SD.s_id = S.s_id
LEFT OUTER JOIN adm_item AS I ON I.item_id = SD.item_id
LEFT OUTER JOIN accounts_chart AS C ON C.account_id = S.client_id
LEFT OUTER JOIN u_user ON u_user.u_id = S.u_id
LEFT OUTER JOIN adm_branch AS B ON B.branch_id = S.branch_id
WHERE S.s_id = $s_id
";
$sRes = mysqli_query($con, $sQ);
if ($sRes === false) { echo "<b>SQL Error:</b> " . mysqli_error($con); exit; }
if (mysqli_num_rows($sRes) < 1) { echo "No Record Found"; exit; }
$sRow = [];
while ($r = mysqli_fetch_assoc($sRes)) { $sRow[] = $r; }
$branchRow = $sRow[0];

// prepare logo data URI
$logo_src = '';
if (!empty($branchRow['branch_Logo'])) {
    $logo_src = logo_data_uri($branchRow['branch_Logo']);
}
if (empty($logo_src)) {
    $default_candidates = [
        $_SERVER['DOCUMENT_ROOT'].'/img/logo.png',
        __DIR__.'/img/logo.png',
        __DIR__.'/../img/logo.png'
    ];
    foreach ($default_candidates as $c) {
        if (file_exists($c) && is_readable($c)) {
            $mime = mime_content_type($c) ?: 'image/png';
            $logo_src = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($c));
            break;
        }
    }
}

// balance calc
$client_id = (int)($branchRow['client_id'] ?? 0);
$balanceQ = "
SELECT (
    IFNULL((SELECT opening_debit  FROM accounts_chart WHERE account_id=$client_id),0) -
    IFNULL((SELECT opening_credit FROM accounts_chart WHERE account_id=$client_id),0) +
    IFNULL((SELECT SUM(s_NetAmount)  FROM cust_sale       WHERE client_id=$client_id),0) -
    IFNULL((SELECT SUM(sr_NetAmount) FROM cust_salereturn WHERE client_id=$client_id),0) -
    IFNULL((SELECT SUM(sp_Amount)    FROM adm_sale_payment WHERE client_id=$client_id AND sp_Type='S'),0) +
    IFNULL((SELECT SUM(sp_Amount)    FROM adm_sale_payment WHERE client_id=$client_id AND sp_Type='SR'),0)
) AS balance
";
$balance = 0.0;
$balanceRes = mysqli_query($con, $balanceQ);
if ($balanceRes && mysqli_num_rows($balanceRes) > 0) {
    $balance = (float)mysqli_fetch_assoc($balanceRes)['balance'];
}

// Aggregate total quantity
$total_qty = 0;
foreach ($sRow as $rr) { $total_qty += (int)($rr['item_Qty'] ?? 0); }
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Receipt - <?= htmlspecialchars($branchRow['s_Number'] ?? '', ENT_QUOTES, 'UTF-8') ?></title>
</head>
<body onload="setTimeout(function(){window.print();},300)" style="font-size:<?= (int)$font_size ?>px">

<!-- HEADER -->
<table width="100%" border="0" cellspacing="0" cellpadding="2">
  <tr>
    <td align="center">
      <?php if (!empty($logo_src)): ?>
        <img src="<?= htmlspecialchars($logo_src, ENT_QUOTES, 'UTF-8') ?>" alt="Logo" style="max-width:80px;max-height:60px;">
      <?php endif; ?>
    </td>
  </tr>
  <tr>
    <td align="center"><h2><?= htmlspecialchars($branchRow['branch_Name'] ?? '', ENT_QUOTES, 'UTF-8') ?></h2></td>
  </tr>
  <?php if (!empty($branchRow['branch_Address'])): ?>
    <tr><td align="center"><?= nl2br(htmlspecialchars($branchRow['branch_Address'], ENT_QUOTES, 'UTF-8')) ?></td></tr>
  <?php endif; ?>
  <?php if (!empty($branchRow['branch_Phone1'])): ?>
    <tr><td align="center"><strong><?= htmlspecialchars($branchRow['branch_Phone1'], ENT_QUOTES, 'UTF-8') ?></strong></td></tr>
  <?php endif; ?>
</table>

<hr>

<!-- META (Invoice + Customer) -->
<table width="100%" border="0" cellspacing="0" cellpadding="2">
  <tr>
    <td valign="top" width="50%">
      <table width="100%" border="0" cellspacing="0" cellpadding="1">
        <tr>
          <td>Invoice:</td>
          <td><strong><?= htmlspecialchars($branchRow['s_Number'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong></td>
        </tr>
        <tr>
          <td>Date:</td>
          <td><strong><?= date('d-M-Y', strtotime($branchRow['s_CreatedOn'])) ?></strong></td>
        </tr>
        <tr>
          <td>Time:</td>
          <td><strong><?= date('h:i A', strtotime($branchRow['s_CreatedOn'])) ?></strong></td>
        </tr>
      </table>
    </td>
    <td valign="top" width="50%">
      <table width="100%" border="0" cellspacing="0" cellpadding="1">
        <tr>
          <td>Customer:</td>
          <td><strong><?= htmlspecialchars($branchRow['client_Name'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong></td>
        </tr>
        <?php if (!empty($branchRow['client_Phone'])): ?>
        <tr>
          <td>Phone:</td>
          <td><strong><?= htmlspecialchars($branchRow['client_Phone'], ENT_QUOTES, 'UTF-8') ?></strong></td>
        </tr>
        <?php endif; ?>
        <?php if (!empty($branchRow['client_Address'])): ?>
        <tr>
          <td valign="top">Address:</td>
          <td><strong><?= nl2br(htmlspecialchars($branchRow['client_Address'], ENT_QUOTES, 'UTF-8')) ?></strong></td>
        </tr>
        <?php endif; ?>
      </table>
    </td>
  </tr>
</table>

<hr>

<!-- ITEMS HEADER -->
<table width="100%" border="0" cellspacing="0" cellpadding="3">
  <tr>
    <td><strong>Item</strong></td>
    <td align="center" width="12%"><strong>Qty</strong></td>
    <td align="right" width="18%"><strong>Rate</strong></td>
    <td align="right" width="20%"><strong>Amount</strong></td>
  </tr>
  <tr><td colspan="4"><hr></td></tr>

  <!-- ITEMS ROWS -->
  <?php foreach ($sRow as $r): ?>
    <?php
      $qty = (int)($r['item_Qty'] ?? 0);
      $rate = number_format((float)($r['item_SalePrice'] ?? 0), 2);
      $amt  = number_format((float)($r['item_NetPrice'] ?? 0), 2);
      $name = htmlspecialchars($r['item_Name'] ?? '', ENT_QUOTES, 'UTF-8');
      $code = htmlspecialchars($r['item_Code'] ?? '', ENT_QUOTES, 'UTF-8');
      $imei = trim((string)($r['item_IMEI'] ?? ''));
    ?>
    <tr>
      <td valign="top">
        <?= $name ?>
        <?php if ($code !== ''): ?><br><span><?= $code ?></span><?php endif; ?>
        <?php if ($imei !== ''): ?><br><span>IMEI: <?= htmlspecialchars($imei, ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?>
      </td>
      <td align="center" valign="top"><?= $qty ?></td>
      <td align="right"  valign="top"><?= $rate ?></td>
      <td align="right"  valign="top"><?= $amt ?></td>
    </tr>
  <?php endforeach; ?>
</table>

<hr>

<!-- TOTALS -->
<table width="100%" border="0" cellspacing="0" cellpadding="2">
  <tr>
    <td>Total Qty:</td>
    <td align="right"><strong><?= (int)$total_qty ?></strong></td>
  </tr>

  <?php if ((float)$branchRow['s_DiscountAmount'] > 0): ?>
  <tr>
    <td>Invoice Discount:</td>
    <td align="right"><strong><?= number_format((float)$branchRow['s_DiscountAmount'],2) ?></strong></td>
  </tr>
  <?php endif; ?>

  <?php if ((float)$branchRow['s_TaxAmount'] > 0): ?>
  <tr>
    <td>Tax:</td>
    <td align="right"><?= number_format((float)$branchRow['s_TaxAmount'],2) ?></td>
  </tr>
  <?php endif; ?>

  <tr>
    <td><strong>Bill Total:</strong></td>
    <td align="right"><strong><?= number_format((float)$branchRow['s_NetAmount'],2) ?></strong></td>
  </tr>
</table>

<!-- AMOUNT IN WORDS -->
<table width="100%" border="1" cellspacing="0" cellpadding="4">
  <tr>
    <td>In Words: <strong><?= numberToWords(number_format((float)$branchRow['s_NetAmount'], 2, '.', '')) ?> Only</strong></td>
  </tr>
</table>

<!-- CASH / CHANGE (for cash mode) -->
<?php if (($branchRow['s_SaleMode'] ?? '') === 'cash' && (float)$branchRow['s_PaidAmount'] > 0): ?>
<table width="100%" border="0" cellspacing="0" cellpadding="2">
  <tr>
    <td>Cash:</td>
    <td align="right"><?= number_format((float)$branchRow['s_PaidAmount'],2) ?></td>
  </tr>
  <tr>
    <td><?= ((float)$branchRow['s_PaidAmount'] >= (float)$branchRow['s_NetAmount']) ? 'Change' : 'Balance' ?>:</td>
    <td align="right"><?= number_format(abs((float)$branchRow['s_PaidAmount'] - (float)$branchRow['s_NetAmount']), 2) ?></td>
  </tr>
</table>
<?php endif; ?>

<hr>

<!-- PREVIOUS/CURRENT BALANCE 
<table width="100%" border="0" cellspacing="0" cellpadding="2">
  <tr>
    <td>Previous Balance:</td>
    <td align="right"><?= number_format($balance - (float)$branchRow['s_NetAmount'] + (float)$branchRow['s_PaidAmount'],2) ?></td>
  </tr>
  <tr>
    <td>Current Balance:</td>
    <td align="right"><?= number_format($balance,2) ?></td>
  </tr>
</table>-->

<hr>

<!-- FOOTER -->
<table width="100%" border="0" cellspacing="0" cellpadding="2">
  <tr>
    <td align="center"><strong>Developed By Websofthouse (0300-7537538)</strong></td>
  </tr>
</table>

</body>
</html>