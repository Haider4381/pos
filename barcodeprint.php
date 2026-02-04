<?php
ini_set('display_errors',1);
error_reporting(E_ALL);

include('connection.php');
require_once('tcpdf/tcpdf.php');

$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(0, 0, 0, 0);
$pdf->SetAutoPageBreak(TRUE);
$pdf->AddPage('L', array(55, 30)); // sticker size made a bit larger for bigger barcode

// Use a modern, professional font (e.g. Helvetica or DejaVu Sans)
$font_main = 'helvetica'; // or 'dejavusans'
$font_code = 'dejavusansmono'; // For product code, clean mono look

$pdf->SetFont($font_main, '', 10);

$Q = "SELECT item_id, item_Name, item_Code as item_Barcode, item_SalePrice
    FROM adm_item
    WHERE item_id='" . mysqli_real_escape_string($con, $_GET['item_id']) . "'";

$mQ = mysqli_query($con, $Q);
$rQ = mysqli_num_rows($mQ);

if (!empty($rQ)) {
    $result = mysqli_fetch_object($mQ);

    $item_name = $result->item_Name;
    $barcode = $result->item_Barcode;
    $item_saleprice = $result->item_SalePrice;

    $_records = (int)$_GET['r'];

    // Barcode image file path (make sure no spaces)
    $text = preg_replace('/\s+/', '', $barcode);

    // Barcode generation (make barcode image bigger)
    include('barcode_library/barcodelib.php');
    if (isset($image) && (is_resource($image) || (is_object($image) && get_class($image) === 'GdImage'))) {
        // Make the image bigger by resizing if needed
        $barcode_path = 'barcode_library/barcods/' . $text . '.png';
        imagepng($image, $barcode_path);
        imagedestroy($image);

        // Optional: Upscale the PNG for bigger barcode (if your barcodelib.php doesn't support size params)
        // You can use GD to resize here if needed
        $src = imagecreatefrompng($barcode_path);
        $orig_w = imagesx($src);
        $orig_h = imagesy($src);
        $new_w = $orig_w * 1.8;  // 80% larger
        $new_h = $orig_h * 1.8;
        $dst = imagecreatetruecolor($new_w, $new_h);
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $new_w, $new_h, $orig_w, $orig_h);
        imagepng($dst, $barcode_path); // overwrite with larger
        imagedestroy($src);
        imagedestroy($dst);
    }

    $txt = '';
    for ($r = 1; $r <= $_records; $r++) {
        $txt = '
        <table border="0" cellpadding="0" cellspacing="0" style="width:100%;font-family:'.$font_main.',Arial,sans-serif;">
          <tr>
            <td align="center" style="font-size:12px;font-weight:bold;line-height:14px;padding-bottom:2px;">'.htmlspecialchars($item_name).'</td>
          </tr>
          <tr>
            <td align="center" style="padding-bottom:2px;">
              <img src="barcode_library/barcods/' . $text . '.png" style="height:34px;" />
            </td>
          </tr>
          <tr>
            <td align="center" style="font-size:13px;font-family:'.$font_code.',monospace;font-weight:bold;letter-spacing:3px;padding-bottom:2px;color:#333;">'.htmlspecialchars($barcode).'</td>
          </tr>
          <tr>
            <td align="center" style="font-size:14px;font-family:'.$font_main.',Arial,sans-serif;font-weight:bold;color:#222;">Rs. ' . number_format($item_saleprice,0) . '</td>
          </tr>
        </table>
        ';
        if ($r > 1) {
            $pdf->AddPage('L', array(55, 30));
        }
        $pdf->writeHTML($txt, true, false, true, false, '');
    }
} else {
    $txt = '<table><tr><td>No Record...</td></tr></table>';
    $pdf->writeHTML($txt, true, false, true, false, '');
}

if (ob_get_level()) { ob_clean(); }
$pdf->Output('itembarcode.pdf', 'I');
?>