<?php
session_start();
require 'vendor/autoload.php'; // pastikan ini ditambahkan

use Dompdf\Dompdf;
use Dompdf\Options;

if (!isset($_SESSION['invoice'])) {
    die("Data invoice tidak ditemukan.");
}

$invoice = $_SESSION['invoice'];

// Buat HTML untuk PDF
$html = '
<h2 style="color:green; text-align:center;">QuickPay</h2>
<p style="text-align:center;">Kp. Pulo Jahe Rt. 002 Rw.014<br>Jakarta Timur, Kec. Cakung<br>Kel. Jatinegara, 13930</p>
<hr>
<p><strong>ID Transaksi:</strong> ' . $invoice['id_transaksi'] . '</p>
<p><strong>Tanggal:</strong> ' . $invoice['tanggal'] . '</p>
<p><strong>Produk:</strong> ' . $invoice['produk'] . '</p>
<p><strong>Total:</strong> Rp' . number_format($invoice['total_harga'], 0, ',', '.') . '</p>
<p><strong>Uang:</strong> Rp' . number_format($invoice['uang_dibayar'], 0, ',', '.') . '</p>
<p><strong>Kembalian:</strong> Rp' . number_format($invoice['kembalian'], 0, ',', '.') . '</p>
<hr>
<p style="text-align:center;">=== Terima Kasih ===</p>
';

// Konfigurasi dompdf
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A6', 'portrait');
$dompdf->render();

// Output file PDF ke browser
$dompdf->stream('invoice_' . $invoice['id_transaksi'] . '.pdf', ["Attachment" => false]);
exit;
