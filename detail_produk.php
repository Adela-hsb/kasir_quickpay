<?php
// Koneksi ke database
$host = "localhost";
$user = "root";
$pass = "";
$db   = "kasir";
$conn = new mysqli($host, $user, $pass, $db);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Ambil ID produk dari URL (GET)
$id = isset($_GET['id']) ? $_GET['id'] : '';

// Validasi ID
if (!ctype_digit($id)) {
    echo "<script>alert('ID produk tidak valid!'); window.location='produk.php';</script>";
    exit;
}

// Ambil data produk
$sql = "SELECT * FROM produk WHERE id_produk = $id";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    $produk = $result->fetch_assoc();
} else {
    echo "<script>alert('Produk tidak ditemukan!'); window.location='produk.php';</script>";
    exit;
}

// Cek dan generate barcode jika kosong
if (empty($produk['barcode'])) {
    $barcode = substr(str_shuffle("0123456789"), 0, 12);
    $produk['barcode'] = $barcode;
    $update = "UPDATE produk SET barcode = '$barcode' WHERE id_produk = $id";
    $conn->query($update);
}

// QR URL
$qr_url = "http://localhost/kasir-digital/produk.php?id=" . $produk['id_produk'];

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Produk</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-green-50 p-4"> <!-- Warna latar belakang lebih soft seperti matcha -->
    <div class="max-w-4xl mx-auto bg-green-100 p-6 rounded-2xl shadow-md border border-green-200">
        <a href="produk.php" class="text-lg text-green-700 hover:text-green-900 transition">&larr; Kembali</a>
        <h1 class="text-center text-3xl font-bold mb-6 text-green-800">Detail Produk</h1>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <img src="uploads/<?= htmlspecialchars($produk['gambar']) ?>" alt="Gambar Produk" class="w-full rounded-xl shadow">
            </div>
            <div class="space-y-4 text-green-900">
                <p><strong>Nama Produk:</strong> <?= htmlspecialchars($produk['nama_produk']) ?></p>
                <p><strong>Harga Jual:</strong> Rp. <?= number_format($produk['harga_jual'], 0, ',', '.') ?></p>
                <p><strong>Stok:</strong> <?= $produk['stok'] ?></p>
                <p><strong>Modal:</strong> Rp. <?= number_format($produk['modal'], 0, ',', '.') ?></p>
                <p><strong>Keuntungan:</strong> Rp. <?= number_format($produk['keuntungan'], 0, ',', '.') ?></p>
                <p><strong>Expired:</strong> 
                    <?= (!empty($produk['tanggal_expired']) && $produk['tanggal_expired'] !== '0000-00-00') 
                        ? date("d-m-Y", strtotime($produk['tanggal_expired'])) 
                        : 'Tidak tersedia' ?>
                </p>
                <p><strong>Deskripsi:</strong></p>
                <div class="bg-green-50 p-2 rounded-md border border-green-200">
                    <?= isset($produk['deskripsi']) ? nl2br(htmlspecialchars($produk['deskripsi'])) : 'Tidak ada deskripsi' ?>
                </div>

                <!-- Barcode -->
                <div class="mt-6">
                    <p><strong>Barcode Produk:</strong></p>
                    <div class="bg-white p-3 rounded-lg shadow border w-fit">
                        <img src="https://barcode.tec-it.com/barcode.ashx?data=<?= $produk['barcode'] ?>&code=Code128&dpi=96" alt="Barcode Produk">
                        <p class="text-center font-mono text-green-800"><?= $produk['barcode'] ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
