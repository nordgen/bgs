<?php

// Include the main TCPDF library (search for installation path).
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
require_once("../vendor/autoload.php"); // Lazy load libraries through composer
require_once("./config.php");
require_once("./initdb.php");
require_once("./common.php");

use Knp\Snappy\Pdf;

function url_origin($s, $use_forwarded_host = false)
{
    $ssl = (!empty($s['HTTPS']) && $s['HTTPS'] == 'on');
    $sp = strtolower($s['SERVER_PROTOCOL']);
    $protocol = substr($sp, 0, strpos($sp, '/')) . (($ssl) ? 's' : '');
    $port = $s['SERVER_PORT'];
    $port = ((!$ssl && $port == '80') || ($ssl && $port == '443')) ? '' : ':' . $port;
    $host = ($use_forwarded_host && isset($s['HTTP_X_FORWARDED_HOST'])) ? $s['HTTP_X_FORWARDED_HOST'] : (isset($s['HTTP_HOST']) ? $s['HTTP_HOST'] : null);
    $host = isset($host) ? $host : $s['SERVER_NAME'] . $port;
    return $protocol . '://' . $host;
}


$myProjectDirectory = dirname(dirname(__FILE__));


$snappy = new Pdf($myProjectDirectory . '/libraries/vendor/h4cc/wkhtmltopdf-amd64/bin/wkhtmltopdf-amd64');

$serverurl = url_origin($_SERVER);
$url_dir = dirname($_SERVER['PHP_SELF']);
$bgs = $_REQUEST['bgs'];
$input_url = $serverurl . $url_dir . '/pdf/content_bgs.php?bgs=' . $bgs;
$input2_url = $serverurl . $url_dir . '/pdf/content_images_bgs.php?bgs=' . $bgs;
$header_url = $serverurl . $url_dir . '/pdf/header.html';
$footer_url = $serverurl . $url_dir . '/pdf/footer.html';

// Set properties like magins, header and footers before calling the content
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="bgs' . $bgs . '.pdf"');
echo $snappy->getOutput(
    [$input_url, $input2_url],
    [
        'footer-html' => $footer_url,
        'header-html' => $header_url,
        'page-size' => 'A4',
        'margin-right' => 15,
        'margin-left' => 15,
    ]
);
//============================================================+
// END OF FILE
//============================================================+
