<?php
require_once 'BarcodeGeneratorPNG.php';

use Picqer\Barcode\BarcodeGeneratorPNG;

$kode = isset($_GET['kode']) ? $_GET['kode'] : 'DEFAULT-001';

$generator = new BarcodeGeneratorPNG();

header('Content-Type: image/png');
echo $generator->getBarcode($kode, $generator::TYPE_CODE_128);
?>
