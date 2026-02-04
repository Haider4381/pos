<?php
/*
  Stable PDF generator for invoice (Roman Urdu):
  - Output buffering clean, sahi headers
  - Base href inject for relative assets
  - Remote assets enabled (fonts/images via CDN)
*/

if (function_exists('ini_set')) {
    ini_set('display_errors', isset($_GET['debug']) && $_GET['debug'] ? '1' : '0');
    ini_set('log_errors', '1');
    ini_set('zlib.output_compression', '0');
    ini_set('output_buffering', '0');
    if (!ini_get('safe_mode')) { @set_time_limit(120); }
}

// Optional session check (ensure no echo/print inside)
$sessionCheck = __DIR__ . '/sessionCheck.php';
if (file_exists($sessionCheck)) { require_once $sessionCheck; }

// Autoload
$autoloads = [
    __DIR__ . '/vendor/autoload.php',
    __DIR__ . '/../vendor/autoload.php',
    __DIR__ . '/dompdf/autoload.inc.php',
];
$autoloadFound = false;
foreach ($autoloads as $a) { if (file_exists($a)) { require_once $a; $autoloadFound = true; break; } }
if (!$autoloadFound) { http_response_code(500); echo "Error: Dompdf autoload nahi mila."; exit; }

use Dompdf\Dompdf;
use Dompdf\Options;

function cleanOutputBuffers(): void { while (ob_get_level() > 0) { @ob_end_clean(); } }
function sendHeaderSafe(string $h): void { if (!headers_sent()) { header($h, true); } }

// Inputs
$s_id = isset($_GET['s_id']) ? $_GET['s_id'] : null;
if ($s_id === null || !is_numeric($s_id)) { http_response_code(400); echo "Bad Request: s_id missing ya invalid."; exit; }
$s_id            = (int)$s_id;
$print_header    = $_GET['print_header']    ?? 'yes';
$show_prebalance = $_GET['show_prebalance'] ?? 'yes';
$paperSize       = $_GET['size']            ?? 'A4';
$paperOrientation= $_GET['orientation']     ?? 'portrait';
$download        = isset($_GET['download']) ? (int)$_GET['download'] : 1;
$debug           = isset($_GET['debug'])    ? (int)$_GET['debug'] : 0;

// Template
$template = __DIR__ . '/invoice_print.php';
if (!file_exists($template)) { http_response_code(500); echo "Error: invoice_print.php nahi mili."; exit; }

// HTML capture
ob_start();
$_GET['s_id']            = $s_id;
$_GET['print_header']    = $print_header;
$_GET['show_prebalance'] = $show_prebalance;
$_GET['pdf']             = 1; // PDF-specific CSS toggles
include $template;
$html = ob_get_clean();
if (!$html || trim($html) === '') { http_response_code(500); echo "Error: invoice_print.php se HTML nahi aayi."; exit; }

// Base href inject
$scheme = (!empty($_SERVER['REQUEST_SCHEME'])) ? $_SERVER['REQUEST_SCHEME'] : ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http');
$host    = $_SERVER['HTTP_HOST'] ?? '';
$baseUri = isset($_SERVER['REQUEST_URI']) ? rtrim(dirname($_SERVER['REQUEST_URI']), '/') . '/' : '/';
if ($host !== '') {
    $baseHref = $scheme . '://' . $host . $baseUri;
    $html = preg_replace('/<head([^>]*)>/i', '<head$1><base href="' . htmlspecialchars($baseHref, ENT_QUOTES, 'UTF-8') . '">', $html, 1);
}

// Debug: raw HTML
if ($debug) { cleanOutputBuffers(); sendHeaderSafe('Content-Type: text/html; charset=UTF-8'); echo $html; exit; }

// Dompdf options
$options = new Options();
$options->set('isRemoteEnabled', true);       // load CDN fonts/images
$options->set('isHtml5ParserEnabled', true);
$options->set('defaultFont', 'Poppins');      // fallback
$options->setChroot(__DIR__);                 // local file access

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper($paperSize, $paperOrientation);

// Render
try { $dompdf->render(); }
catch (Throwable $e) {
    cleanOutputBuffers();
    http_response_code(500);
    sendHeaderSafe('Content-Type: text/plain; charset=UTF-8');
    echo "PDF render error: " . $e->getMessage();
    exit;
}

// Output exact bytes
$pdfBytes = $dompdf->output();
if ($pdfBytes === '' || $pdfBytes === false) {
    cleanOutputBuffers();
    http_response_code(500);
    sendHeaderSafe('Content-Type: text/plain; charset=UTF-8');
    echo "Error: PDF bytes generate nahi huay.";
    exit;
}

$filename    = 'invoice-' . $s_id . '.pdf';
$disposition = $download ? 'attachment' : 'inline';

cleanOutputBuffers();
sendHeaderSafe('Content-Type: application/pdf');
sendHeaderSafe('Content-Disposition: ' . $disposition . '; filename="' . $filename . '"');
sendHeaderSafe('Content-Length: ' . strlen($pdfBytes));
sendHeaderSafe('Cache-Control: private, max-age=0, must-revalidate');
sendHeaderSafe('Pragma: public');
echo $pdfBytes;
exit;