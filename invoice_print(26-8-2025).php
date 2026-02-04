<?php
include('sessionCheck.php');
include('connection.php');

$branch_id = $_SESSION['branch_id'] ?? 0;

error_reporting(E_ALL);
ini_set('display_errors', isset($_GET['debug']) && $_GET['debug'] ? '1' : '0');

$page_print_width  = '190mm'; // usable width inside margins for A4
$page_print_height = '297mm';
$print_header      = isset($_GET['print_header']) ? $_GET['print_header'] : 'yes';
$show_prebalance   = isset($_GET['show_prebalance']) ? $_GET['show_prebalance'] : 'yes';
$print_size        = isset($_GET['print_size']) ? $_GET['print_size'] : '';
if ($print_size === 'thermal') { $page_print_width = '3in'; }

// Number to Words ('.00' ko words me add nahi karta)
function numberToWords($number) {
    $hyphen      = '-';
    $conjunction = ' and ';
    $separator   = ', ';
    $decimal     = ' point ';
    $dictionary  = [
        0=>'zero',1=>'one',2=>'two',3=>'three',4=>'four',5=>'five',6=>'six',7=>'seven',8=>'eight',9=>'nine',
        10=>'ten',11=>'eleven',12=>'twelve',13=>'thirteen',14=>'fourteen',15=>'fifteen',16=>'sixteen',17=>'seventeen',
        18=>'eighteen',19=>'nineteen',20=>'twenty',30=>'thirty',40=>'forty',50=>'fifty',60=>'sixty',70=>'seventy',
        80=>'eighty',90=>'ninety',100=>'hundred',1000=>'thousand',1000000=>'million',1000000000=>'billion',
        1000000000000=>'trillion',1000000000000000=>'quadrillion',1000000000000000000=>'quintillion'
    ];
    if (!is_numeric($number)) { return false; }
    $number = (string)$number;
    if (strpos($number, '.') !== false) { [$integer, $fraction] = explode('.', $number); } else { $integer = $number; $fraction = null; }
    $integer = ltrim($integer, '0'); if ($integer === '') $integer = '0';

    switch (true) {
        case $integer < 21:
            $string = $dictionary[$integer]; break;
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

$sQ = "
SELECT
    S.s_id, S.branch_id, S.s_Number, S.s_TotalAmount, S.s_NetAmount, S.s_Tax, S.s_TaxAmount, S.s_SaleMode,
    S.s_PaymentType, S.s_Discount, S.s_DiscountAmount, S.s_PaidAmount, S.s_CreatedOn, S.s_totalitems, S.s_RemarksExternal,
    C.account_id AS client_id, C.account_title AS client_Name, C.phone AS client_Phone,
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
if ($sRes === false) { echo "<b>SQL Error:</b> " . mysqli_error($con) . "<br><b>Query:</b> $sQ"; exit; }
if (mysqli_num_rows($sRes) < 1) { echo "No Record Found"; exit; }
$sRow = [];
while ($r = mysqli_fetch_assoc($sRes)) { $sRow[] = $r; }
$branchRow = $sRow[0];

// Customer balance
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
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Print Sale Invoice</title>

<style>
/* Online Poppins font (TTF via CDN) */
@font-face { font-family:'Poppins'; src:url('https://cdn.jsdelivr.net/gh/google/fonts@main/ofl/poppins/Poppins-Regular.ttf') format('truetype'); font-weight:400; font-style:normal; }
@font-face { font-family:'Poppins'; src:url('https://cdn.jsdelivr.net/gh/google/fonts@main/ofl/poppins/Poppins-Bold.ttf')    format('truetype'); font-weight:700; font-style:normal; }

/* Page box: thinner margins so content fits comfortably */
@page { size: A4 portrait; margin: 10mm 10mm 12mm 10mm; }

html, body {
  margin:0; padding:0; background:#fff; color:#222;
  font-family:'Poppins','DejaVu Sans',Arial,sans-serif;
  font-size:12px; line-height:1.25;
}
* { box-sizing: border-box; }

/* Container width 190mm (inside @page margins) to avoid clipping/wrap */
.wrapper { width: <?= $page_print_width ?>; margin: 0 auto; }

hr { border:0; border-top:1.2px solid #000; margin: 10px 0 10px 0; }

/* Header: table-based (Dompdf safe) */
.header-table { width:100%; border-collapse:collapse; table-layout:fixed; }
.header-table td { vertical-align:top; }
.header-left, .header-right { width: 30%; }
.header-center { width: 40%; text-align:center; }

/* Logos */
.brand-left  { width:60px; height:auto; }
/* Lower the right logo a bit for PDF and browser */
.brand-right { width:140px; height:70px; object-fit:contain; margin-top:6mm; }

/* Phone text blocks */
.brand-phone { font-weight:700; margin-top:6px; display:block; }
/* Align requested: left number on left, right number on right */
.header-left .brand-phone  { text-align:left; }
.header-right .brand-phone { text-align:right; }

/* Title */
.invoice-title { text-align:center; font-size:20px; font-weight:700; letter-spacing:1px; margin: 6px 0 6px; }

/* Meta */
.meta-table { width:100%; border-collapse:collapse; table-layout:fixed; margin-top:6px; }
.meta-table td { padding:4px 3px; }

/* Items table with exact widths */
.items { width:100%; border-collapse:collapse; table-layout:fixed; margin-top:8px; }
.items thead { display: table-header-group; }
.items th, .items td { border:1px solid #c9c9c9; padding:6px 6px; font-size:12px; }
.items th { background:#ececec; color:#333; font-weight:700; }

/* Column widths via colgroup */
.items col.sr       { width: 6%; }
.items col.image    { width: 9%; }
.items col.item     { width: 30%; }
.items col.qty      { width: 12%; }
.items col.rate     { width: 14%; }
.items col.discount { width: 12%; }
.items col.amount   { width: 17%; }

.txt-left  { text-align:left; }
.txt-center{ text-align:center; }
.txt-right { text-align:right; }
.nowrap    { white-space:nowrap; }

/* Product image size */
.product-thumb-inv {
  width:50px; height:50px; object-fit:cover;
  border:1px solid #bdbdbd; border-radius:4px; background:#fff; display:block; margin:0 auto;
}

/* Totals */
.totals { width:100%; border-collapse:collapse; table-layout:fixed; margin-top:6px; }
.totals td { padding:5px 0; font-size:12px; }
.totals tr td:first-child { text-align:left; font-weight:500; }
.totals .bold { font-weight:700; font-size:13.5px; }
.totals .amount { font-weight:700; }
.item-discount { color:#c00; font-weight:700; }

/* Amount in words */
.amount-in-words-box {
  border:1.6px solid #444; border-radius:6px; padding:10px 10px; margin-top:10px;
  font-size:12px; font-weight:700; background:#f7f7f7; word-break:break-word;
}

/* Footer */
.footer { text-align:center; font-size:11px; margin-top:16px; letter-spacing:.4px; }
</style>
</head>
<body>
<div class="wrapper">

  <?php if ($print_header === "yes"): ?>
  <table class="header-table">
    <tr>
      <td class="header-left">
        <img class="brand-left" src="img/alif.jpg" alt="Left">
        <span class="brand-phone"><?= htmlspecialchars($branchRow['branch_Phone1'] ?? '', ENT_QUOTES, 'UTF-8'); ?></span>
      </td>
      <td class="header-center">
        <div style="font-size:42px; font-weight:700; margin-bottom:2px;"><?= htmlspecialchars($branchRow['branch_Name'] ?? 'ALIF PRET', ENT_QUOTES, 'UTF-8'); ?></div>
        <div style="font-weight:700;"><?= htmlspecialchars($branchRow['branch_Address'] ?? '', ENT_QUOTES, 'UTF-8'); ?></div>
      </td>
      <td class="header-right" style="text-align:right;">
        <img class="brand-right" src="img/abutarab.jpg" alt="Right">
        <span class="brand-phone"><?= htmlspecialchars($branchRow['branch_Phone2'] ?? '', ENT_QUOTES, 'UTF-8'); ?></span>
      </td>
    </tr>
  </table>
  <hr>
  <?php endif; ?>

  <div class="invoice-title">SALE INVOICE</div>

  <table class="meta-table">
    <tr>
      <td style="width:18%;">Date:</td>
      <td style="width:32%;"><?= date('d-M-Y', strtotime($branchRow['s_CreatedOn'])); ?></td>
      <td style="width:18%;">Time:</td>
      <td style="width:32%;"><?= date('h:i A', strtotime($branchRow['s_CreatedOn'])); ?></td>
    </tr>
    <tr>
      <td>Invoice#</td>
      <td colspan="3"><?= htmlspecialchars($branchRow['s_Number'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
    </tr>
    <tr>
      <td>Customer:</td>
      <td colspan="3">
        <?= htmlspecialchars($branchRow['client_Name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
        <?php if (!empty($branchRow['client_Phone'])): ?>
          &nbsp;<small><?= htmlspecialchars($branchRow['client_Phone'], ENT_QUOTES, 'UTF-8'); ?></small>
        <?php endif; ?>
      </td>
    </tr>
  </table>

  <hr>

  <table class="items">
    <colgroup>
      <col class="sr">
      <col class="image">
      <col class="item">
      <col class="qty">
      <col class="rate">
      <col class="discount">
      <col class="amount">
    </colgroup>
    <thead>
      <tr>
        <th class="txt-center">Sr</th>
        <th class="txt-center">Image</th>
        <th class="txt-left">Item</th>
        <th class="txt-center">Suits / Quantity</th>
        <th class="txt-right">Rate</th>
        <th class="txt-right">Discount</th>
        <th class="txt-right">Amount</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $sr=0;
      foreach($sRow as $r):
        $sr++;
        $imgPath = trim((string)($r['item_Image'] ?? ''));
        if ($imgPath === '') { $imgPath = 'img/demo-img.png'; }
        $item_discount = (isset($r['item_discount_amount_per_item']) && (float)$r['item_discount_amount_per_item'] > 0)
          ? (float)$r['item_discount_amount_per_item']
          : (float)($r['item_DiscountPrice'] ?? 0);
      ?>
      <tr>
        <td class="txt-center"><?= $sr; ?></td>
        <td class="txt-center"><img src="<?= htmlspecialchars($imgPath, ENT_QUOTES, 'UTF-8'); ?>" class="product-thumb-inv" alt="Product"></td>
        <td class="txt-left">
          <?= htmlspecialchars($r['item_Name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
          <?php $imei = trim((string)($r['item_IMEI'] ?? '')); if ($imei !== '' && $imei !== ($r['item_Name'] ?? '')) { echo '<br><small>'.htmlspecialchars($imei, ENT_QUOTES, 'UTF-8').'</small>'; } ?>
        </td>
        <td class="txt-center nowrap"><?= (int)($r['item_Qty'] ?? 0); ?></td>
        <td class="txt-right nowrap"><?= number_format((float)($r['item_SalePrice'] ?? 0), 2); ?></td>
        <td class="txt-right nowrap">
          <?php if ($item_discount > 0): ?>
            <span class="item-discount"><?= number_format($item_discount, 2); ?></span>
            <?php if ((float)($r['item_DiscountPercentage'] ?? 0) > 0): ?>
              <span style="font-size:11px;">(<?= (float)$r['item_DiscountPercentage']; ?>%)</span>
            <?php endif; ?>
          <?php else: ?>-<?php endif; ?>
        </td>
        <td class="txt-right nowrap"><?= number_format((float)($r['item_NetPrice'] ?? 0), 2); ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
    <tfoot>
      <tr>
        <td colspan="6" class="txt-right" style="font-weight:700;">Total Amount:</td>
        <td class="txt-right" style="font-weight:700;"><?= number_format((float)$branchRow['s_TotalAmount'], 2); ?></td>
      </tr>
    </tfoot>
  </table>

  <hr>

  <table class="totals" style="margin-top:0;">
    <tr>
      <td style="font-weight:700;">
        Total Suits / Quantity:
        <?php $total_qty=0; foreach ($sRow as $r){ $total_qty += (int)($r['item_Qty'] ?? 0); } echo (int)$total_qty; ?>
      </td>
      <td class="txt-right"></td>
    </tr>
  </table>

  <table class="totals">
    <?php if ((float)$branchRow['s_DiscountAmount'] > 0): ?>
    <tr>
      <td style="color:#c00; font-weight:700;">Total Invoice Discount:</td>
      <td class="txt-right" style="color:#c00; font-weight:700;"><?= number_format((float)$branchRow['s_DiscountAmount'], 2); ?></td>
    </tr>
    <?php endif; ?>

    <?php if ((float)$branchRow['s_TaxAmount'] > 0): ?>
    <tr>
      <td>Tax:</td>
      <td class="txt-right"><?= number_format((float)$branchRow['s_TaxAmount'], 2); ?></td>
    </tr>
    <?php endif; ?>

    <tr>
      <td class="bold">Bill Total:</td>
      <td class="bold amount txt-right"><?= number_format((float)$branchRow['s_NetAmount'], 2); ?></td>
    </tr>

    <tr>
      <td colspan="2">
        <div class="amount-in-words-box">Amount in Words: <?= numberToWords(number_format((float)$branchRow['s_NetAmount'], 2, '.', '')); ?> Only</div>
      </td>
    </tr>

    <?php if (($branchRow['s_SaleMode'] ?? '') === 'cash' && (float)$branchRow['s_PaidAmount'] > 0): ?>
    <tr>
      <td>Cash:</td>
      <td class="txt-right"><?= number_format((float)$branchRow['s_PaidAmount'], 2); ?></td>
    </tr>
    <tr>
      <td><?= ((float)$branchRow['s_PaidAmount'] >= (float)$branchRow['s_NetAmount']) ? 'Change:' : 'Balance:'; ?></td>
      <td class="txt-right"><?= number_format(abs((float)$branchRow['s_PaidAmount'] - (float)$branchRow['s_NetAmount']), 2); ?></td>
    </tr>
    <?php endif; ?>
  </table>

  <?php if ($show_prebalance === 'yes'): ?>
  <hr>
  <table class="totals">
    <tr>
      <td>Previous Balance:</td>
      <td class="txt-right"><?= number_format($balance - (float)$branchRow['s_NetAmount'] + (float)$branchRow['s_PaidAmount'], 2); ?></td>
    </tr>
    <tr>
      <td>Current Balance:</td>
      <td class="txt-right"><?= number_format($balance, 2); ?></td>
    </tr>
  </table>
  <?php endif; ?>

  <hr>
  <br><br>

  <table class="totals">
    <tr>
      <td></td>
      <td class="txt-right">Signature : ___________________</td>
    </tr>
  </table>

  <div class="footer">
    Powered by: <b>websofthouse.net</b><br>
    <span>Software Developed By 0300-7537538</span>
  </div>

</div>
</body>
</html>