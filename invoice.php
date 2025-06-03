<?php
session_start();
include 'connection.php';

// Pastikan ada data invoice
if (!isset($_SESSION['invoice']) || empty($_SESSION['invoice']['id_transaksi'])) {
    die("Error: Data invoice tidak ditemukan. Silakan lakukan pembayaran terlebih dahulu.");
}

$invoice = $_SESSION['invoice'];
$id_transaksi = $invoice['id_transaksi'];
$diskon = $_SESSION['diskon'] ?? 0; // Mengambil diskon dari session

// Ambil metode pembayaran
$metode_pembayaran = isset($invoice['metode_pembayaran']) ? $invoice['metode_pembayaran'] : 'cash';

// Cek dan set tanggal_pembelian jika belum ada
if (!isset($invoice['tanggal_pembelian'])) {
    $getTanggal = mysqli_query($conn, "SELECT tanggal_pembelian FROM transaksi WHERE id_transaksi = '$id_transaksi'");
    if ($row = mysqli_fetch_assoc($getTanggal)) {
        $invoice['tanggal_pembelian'] = $row['tanggal_pembelian'];
    } else {
        $invoice['tanggal_pembelian'] = date('Y-m-d H:i:s');
    }
}

// Cek status member
$status_member = 'Bukan Member';
if (!empty($invoice['no_telp'])) {
    $telp = $invoice['no_telp'];
    $cek_member = mysqli_query($conn, "SELECT status FROM member WHERE no_telp = '$telp' LIMIT 1");
    if ($cek_member && mysqli_num_rows($cek_member) > 0) {
        $data = mysqli_fetch_assoc($cek_member);
        $status_member = ($data['status'] === 'aktif') ? 'Member Aktif' : 'Member Tidak Aktif';
    }
}

// Hitung potongan dan total
$total_harga = $invoice['total_harga'];
$potongan = ($total_harga * $diskon) / 100;
$total_setelah_diskon = $total_harga - $potongan;

// Siapkan variabel tampilan berdasarkan metode
if ($metode_pembayaran === 'qris') {
    $display_total = 0;
    $display_uang = 0;
    $display_kembalian = 0;
    $display_potongan = 0;
} else {
    $display_total = $total_setelah_diskon;
    $display_uang = $invoice['uang_dibayar'];
    $display_kembalian = $invoice['kembalian'];
    $display_potongan = $potongan;
}

// Siapkan konten QR
$qr_content = "ID Transaksi: {$invoice['id_transaksi']}\nProduk: {$invoice['produk']}\nTotal: Rp" . number_format($display_total, 0, ',', '.');
$qr_image = 'https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=' . urlencode($qr_content);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Struk QuickPay</title>
  <style>
    body {
      background: #f4f4f4;
      font-family: 'Courier New', monospace;
      display: flex;
      flex-direction: column;
      align-items: center;
      padding: 30px;
    }

    .struk {
      width: 280px;
      background: white;
      border: 1px dashed #333;
      padding: 20px;
      box-shadow: 0 0 10px rgba(0,0,0,0.2);
    }

    .struk h2 {
      text-align: center;
      margin: 0 0 5px;
    }

    .struk .alamat, .struk .tanggal, .struk .kasir {
      text-align: center;
      font-size: 12px;
      margin-bottom: 5px;
    }

    .dashed {
      border-top: 1px dashed black;
      margin: 10px 0;
    }

    .info, .total {
      font-size: 13px;
    }

    .total {
      font-weight: bold;
    }

    .footer {
      text-align: center;
      font-size: 12px;
      margin-top: 10px;
    }

    .btn-print, .btn-save {
      margin: 10px 5px;
      padding: 8px;
      background: #222;
      color: white;
      border: none;
      cursor: pointer;
      width: 130px;
    }

    .qr {
      text-align: center;
      margin-top: 10px;
    }

    .qr img {
      display: block;
      margin: auto;
    }

    @media print {
      .btn-print, .btn-save {
        display: none;
      }
      body {
        background: white;
        padding: 0;
      }
    }
  </style>
</head>
<body>

  <div id="struk" class="struk">
    <h2>QUICKPAY</h2>
    <div class="alamat">Kp. Pulo Jahe RT 002 RW 014<br>Jatinegara, Cakung - Jaktim 13930</div>

    <div class="dashed"></div>

    <div class="tanggal">Tanggal: <?php echo $invoice['tanggal_pembelian']; ?></div>
    <div class="kasir">Admin: <?php echo $invoice['admin']; ?></div>

    <div class="dashed"></div>

    <div class="info">ID Transaksi:</div>
    <div class="info" style="margin-left: 10px;"><?php echo $invoice['id_transaksi']; ?></div>

    <div class="info">Metode Pembayaran: <?php echo strtoupper($metode_pembayaran); ?></div>

    <div class="dashed"></div>

    <div class="info">Status Member: <?php echo $status_member; ?></div>
    <?php if ($status_member === 'Member Aktif'): ?>
  <div class="info">No. Telp Member: <?php echo htmlspecialchars($invoice['no_telp']); ?></div>
<?php endif; ?>


    <div class="dashed"></div>

    <div class="info">Produk:</div>
    <div class="info" style="margin-left: 10px;">
      <?php
        $produk_lines = explode(",", $invoice['produk']);
        foreach ($produk_lines as $pr) {
            echo '- ' . trim($pr) . '<br>';
        }
      ?>
    </div>

    <div class="dashed"></div>

    <div class="info">Subtotal: Rp <?php echo number_format($total_harga, 0, ',', '.'); ?></div>
    <?php if ($metode_pembayaran !== 'qris'): ?>
    <div class="info">Diskon (<?php echo $diskon; ?>%): -Rp <?php echo number_format($display_potongan, 0, ',', '.'); ?></div>
    <?php endif; ?>
    <div class="total">TOTAL BAYAR: Rp <?php echo number_format($display_total, 0, ',', '.'); ?></div>
    <div class="info">Uang Dibayar: Rp <?php echo number_format($display_uang, 0, ',', '.'); ?></div>
    <div class="info">Kembalian: Rp <?php echo number_format($display_kembalian, 0, ',', '.'); ?></div>

    <div class="dashed"></div>

    <div class="qr">
      <img src="<?php echo $qr_image; ?>" alt="QR Code">
      <div style="font-size: 10px;">Scan untuk info struk</div>
    </div>

    <div class="dashed"></div>

    <div class="footer">*** TERIMA KASIH ***<br>Semoga hari Anda menyenangkan!</div>

    <?php if ($status_member === 'Member Aktif'): ?>
  <div style="text-align: center; margin-top: 10px;">
    <?php
      $telp_wa = preg_replace('/^0/', '62', $invoice['no_telp']); // Ubah 08xxx jadi 628xxx
      $pesan_wa = "*Struk QUICKPAY*\n"
        . "ID: {$invoice['id_transaksi']}\n"
        . "Tanggal: {$invoice['tanggal_pembelian']}\n"
        . "Admin: {$invoice['admin']}\n"
        . "Produk:\n";
      $produk_lines = explode(",", $invoice['produk']);
      foreach ($produk_lines as $pr) {
          $pesan_wa .= "- " . trim($pr) . "\n";
      }
      $pesan_wa .= "Subtotal: Rp " . number_format($total_harga, 0, ',', '.') . "\n";
      $pesan_wa .= "Diskon: Rp " . number_format($display_potongan, 0, ',', '.') . "\n";
      $pesan_wa .= "*Total: Rp " . number_format($display_total, 0, ',', '.') . "*\n";
      $pesan_wa .= "Dibayar: Rp " . number_format($display_uang, 0, ',', '.') . "\n";
      $pesan_wa .= "Kembalian: Rp " . number_format($display_kembalian, 0, ',', '.') . "\n\n";
      $pesan_wa .= "Terima kasih telah berbelanja di *QuickPay!* 🙏";
      $wa_url = "https://wa.me/{$telp_wa}?text=" . urlencode($pesan_wa);
    ?>
    <a href="<?php echo $wa_url; ?>" target="_blank" class="btn-save" style="background-color: #25D366;">
      Kirim ke WhatsApp
    </a>
  </div>
<?php endif; ?>

  </div>

  <!-- Tombol aksi -->
  <div style="display: flex;">
    <button class="btn-print" onclick="window.print()">🖨 Cetak Struk</button>
  </div>

  <!-- Script jsPDF + html2canvas -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
  <script>
    async function downloadPDF() {
      const { jsPDF } = window.jspdf;
      const struk = document.getElementById("struk");
      const canvas = await html2canvas(struk);
      const imgData = canvas.toDataURL("image/png");
      const pdf = new jsPDF({
        unit: "pt",
        format: [300, canvas.height + 40],
      });
      pdf.addImage(imgData, "PNG", 10, 10, 280, 0);
      pdf.save("struk-quickpay.pdf");

      // Redirect ke laporan.php setelah simpan
      setTimeout(() => {
        window.location.href = "laporan.php";
      }, 1000);
    }
  </script>
</body>
</html>
