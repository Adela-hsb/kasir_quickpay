<?php
include 'connection.php';

$id_produk = $_GET['id'] ?? '';
$query = mysqli_query($conn, "SELECT * FROM produk WHERE id_produk = '$id_produk'");
$produk = mysqli_fetch_assoc($query);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_produk = $_POST['nama_produk'];
    $barcode_produk = $_POST['barcode'] ?? '';
    $stok_produk = $_POST['stok'];
    $modal_produk = $_POST['modal'];
    $harga_jual = $_POST['harga_jual'];
    $keuntungan = $_POST['keuntungan'];
    $id_kategori = $_POST['fid_kategori'];
    $deskripsi = $_POST['deskripsi'];

    $check_duplikat = mysqli_query($conn, "SELECT * FROM produk WHERE nama_produk = '$nama_produk' AND id_produk != '$id_produk'");
    if (mysqli_num_rows($check_duplikat) > 0) {
        echo "<script>alert('Nama produk sudah ada, silakan pilih nama produk yang lain.'); window.location='produk.php';</script>";
        exit;
    }

    $gambar = $produk['gambar']; // default ke gambar lama
    if (!empty($_FILES['gambar']['name'])) {
        $target_dir = "uploads/";
        $file_extension = strtolower(pathinfo($_FILES["gambar"]["name"], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png'];

        if (in_array($file_extension, $allowed_extensions)) {
            $gambar = time() . "_" . uniqid() . "." . $file_extension;
            $target_file = $target_dir . $gambar;

            if (!move_uploaded_file($_FILES["gambar"]["tmp_name"], $target_file)) {
                die("<script>alert('Gagal mengupload gambar!'); window.history.back();</script>");
            }
        } else {
            die("<script>alert('Format gambar tidak didukung! Gunakan JPG atau PNG.'); window.history.back();</script>");
        }
    }

    $update = mysqli_query($conn, "UPDATE produk SET 
        nama_produk = '$nama_produk', 
        barcode = '$barcode_produk', 
        stok = '$stok_produk', 
        modal = '$modal_produk', 
        harga_jual = '$harga_jual', 
        keuntungan = '$keuntungan', 
        fid_kategori = '$id_kategori', 
        deskripsi = '$deskripsi', 
        gambar = '$gambar' 
        WHERE id_produk = '$id_produk'");

    if ($update) {
        echo "<script>alert('Produk berhasil diperbarui!'); window.location='produk.php';</script>";
    } else {
        echo "<script>alert('Gagal memperbarui produk: " . mysqli_error($conn) . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Edit Produk</title>
</head>
<body class="bg-[#A0C878] flex justify-center items-center min-h-screen">
    <form action="" method="POST" enctype="multipart/form-data" class="bg-white p-8 rounded-lg shadow-md w-[600px]">
        <div class="text-center mb-6">
            <h1 class="text-green-500 text-2xl font-bold">QuickPay</h1>
            <p class="text-sm text-gray-600">Edit Produk</p>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-gray-700 mb-1">Nama Produk</label>
                <input type="text" name="nama_produk" value="<?= $produk['nama_produk'] ?>" required class="w-full p-2 bg-[#CBE0A5] rounded-md">
                
                <label class="block text-gray-700 mb-1">Barcode Produk (Opsional)</label>
                <input type="text" name="barcode" value="<?= $produk['barcode'] ?>" class="w-full p-2 bg-[#CBE0A5] rounded-md" placeholder="Otomatis jika dikosongkan">
                
                <label class="block text-gray-700 mb-1">Stok</label>
                <input type="number" name="stok" value="<?= $produk['stok'] ?>" required class="w-full p-2 bg-[#CBE0A5] rounded-md">
                
                <label class="block text-gray-700 mb-1">Modal</label>
                <input type="text" name="modal" value="<?= $produk['modal'] ?>" required class="w-full p-2 bg-[#CBE0A5] rounded-md">
            </div>
            <div>
                <label class="block text-gray-700 mb-1">Harga Jual</label>
                <input type="text" name="harga_jual" value="<?= $produk['harga_jual'] ?>" required class="w-full p-2 bg-[#CBE0A5] rounded-md">
                
                <label class="block text-gray-700 mb-1">Keuntungan</label>
                <input type="text" name="keuntungan" value="<?= $produk['keuntungan'] ?>" required class="w-full p-2 bg-[#CBE0A5] rounded-md">
                
                <label class="block text-gray-700 mb-1">ID Kategori</label>
                <input type="number" name="fid_kategori" value="<?= $produk['fid_kategori'] ?>" required class="w-full p-2 bg-[#CBE0A5] rounded-md">
                
                <label class="block text-gray-700 mb-1">Deskripsi</label>
                <input type="text" name="deskripsi" value="<?= $produk['deskripsi'] ?>" required class="w-full p-2 bg-[#CBE0A5] rounded-md">
            </div>
        </div>

        <div class="mt-4">
            <label class="block text-gray-700 mb-1">Gambar (JPG/PNG)</label>
            <input type="file" name="gambar" class="w-full p-2 bg-[#CBE0A5] rounded-md">
            <?php if (!empty($produk['gambar'])): ?>
                <img src="<?= $produk['gambar'] ?>" class="mt-2 w-24 h-24 object-cover rounded-md" alt="Gambar Produk">
            <?php endif; ?>
        </div>

        <div class="text-center mt-4">
            <button type="submit" class="bg-[#88AA5B] text-white px-6 py-2 rounded-md shadow-md hover:bg-[#7B964F]">
                Confirm
            </button>
            <a href="produk.php" class="bg-gray-300 text-gray-800 px-6 py-2 rounded-md shadow-md hover:bg-gray-400 ml-2">Kembali</a>
        </div>
    </form>
</body>
</html>
