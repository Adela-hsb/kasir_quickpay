<?php
session_start();
include 'connection.php';

//function sanitize($data) {
  //  return htmlspecialchars(strip_tags(trim($data)));
//}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tanggal = date('Y-m-d');
    $produk = $_POST['produk'];
    $total_harga = $_POST['total_harga'];
    $admin = $_POST['admin'];
    $uang_dibayar = $_POST['nominal'];
    $metode = isset($_POST['metode_pembayaran']) ? $_POST['metode_pembayaran'] : 'cash';

    $no_telp = isset($_POST['no_telp']) ? sanitize($_POST['no_telp']) : null;
    $diskon_persen = isset($_POST['diskon_persen']) ? (int)$_POST['diskon_persen'] : 0;
    $potongan_diskon = isset($_POST['potongan_diskon']) ? (int)$_POST['potongan_diskon'] : 0;
    $total_setelah_diskon = isset($_POST['total_setelah_diskon_input']) ? (int)$_POST['total_setelah_diskon_input'] : $total_harga;

    // Validasi uang dibayar
    if ($metode === 'cash' && $uang_dibayar < $total_setelah_diskon) {
        echo "<script>alert('Uang yang dibayarkan kurang!'); window.location.href='konfirmasi_pembayaran.php';</script>";
        exit;
    }


    $kembalian = $uang_dibayar - $total_setelah_diskon;
    $id_transaksi = "INV-" . date("Ymd-His") . "-" . rand(100, 999);

    // Ambil ID admin dari username
    $queryAdmin = mysqli_query($conn, "SELECT id FROM admin WHERE username = '$admin' LIMIT 1");
    $id_admin = ($data = mysqli_fetch_assoc($queryAdmin)) ? $data['id'] : null;

    // Ambil ID member dari no telepon
    $id_member = null;
    if (!empty($no_telp)) {
        $queryMember = mysqli_query($conn, "SELECT id_member FROM member WHERE no_telp = '$no_telp' LIMIT 1");
        if ($data = mysqli_fetch_assoc($queryMember)) {
            $id_member = $data['id_member'];
        }
    }
// Simpan ke tabel transaksi
if (isset($_SESSION['keranjang']) && is_array($_SESSION['keranjang'])) {
    foreach ($_SESSION['keranjang'] as $id_produk => $item) {
        $jumlah = $item['jumlah'];
        $harga = $item['harga'];
        $total = $jumlah * $harga;
        $keuntungan = $total * 0.2; // Misal: keuntungan 20%
        $detail = "{$item['nama_produk']} ({$jumlah}x)";

        // Simpan transaksi
        $query = "INSERT INTO transaksi (tanggal_pembelian, total_harga, id_admin, id_produk, detail, id_member, total_keuntungan, jumlah)
                  VALUES ('$tanggal', '$total', '$id_admin', '$id_produk', '$detail', " . ($id_member ? "'$id_member'" : "NULL") . ", '$keuntungan', '$jumlah')";
        mysqli_query($conn, $query);

        $now = date('Y-m-d H:i:s');
mysqli_query($conn, "UPDATE member SET last_activity = '$now' WHERE id_member = '$id_member'");


        // Kurangi stok produk
        $update_stok = "UPDATE produk SET stok = stok - $jumlah WHERE id_produk = $id_produk";
        mysqli_query($conn, $update_stok);
    }
}


    // Simpan invoice ke session
    $_SESSION['invoice'] = [
        'id_transaksi' => $id_transaksi,
        'tanggal' => $tanggal,
        'produk' => $produk,
        'total_harga' => $total_harga,
        'admin' => $admin,
        'uang_dibayar' => $uang_dibayar,
        'kembalian' => $kembalian,
        'no_telp' => $no_telp,
        'diskon_persen' => $diskon_persen,
        'potongan_diskon' => $potongan_diskon,
        'total_setelah_diskon' => $total_setelah_diskon,
        'metode_pembayaran' => $metode
    ];

    header("Location: invoice.php");
    exit;
} else {
    header("Location: konfirmasi_pembayaran.php");
    exit;
}
?>
