<?php
session_start();
include 'connection.php';

$data = json_decode(file_get_contents('php://input'), true);
$barcode = mysqli_real_escape_string($conn, $data['barcode']);

// Ambil data produk berdasarkan barcode
$query = "SELECT * FROM produk WHERE barcode = '$barcode' AND stok > 0";
$result = mysqli_query($conn, $query);
$produk = mysqli_fetch_assoc($result);

if ($produk) {
    // Simpan ke keranjang (bisa session atau ke DB)
    $_SESSION['keranjang'][] = [
        'id_produk' => $produk['id_produk'],
        'nama_produk' => $produk['nama_produk'],
        'harga' => $produk['harga_jual'],
        'gambar' => $produk['gambar']
    ];

    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Produk tidak ditemukan atau stok habis.']);
}

mysqli_close($conn);
