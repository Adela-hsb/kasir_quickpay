<?php
session_start();
include 'connection.php';

// Ambil semua kategori dari database
$kategori_result = mysqli_query($conn, "SELECT kategori FROM kategori");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_produk = mysqli_real_escape_string($conn, $_POST['nama_produk']);
    $barcode = !empty($_POST['barcode']) ? mysqli_real_escape_string($conn, $_POST['barcode']) : md5($nama_produk);
    $stok = isset($_POST['stok']) ? (int)$_POST['stok'] : 0;
    $modal = isset($_POST['modal']) ? (float)$_POST['modal'] : 0;
    $harga_jual = isset($_POST['harga_jual']) ? (float)$_POST['harga_jual'] : 0;
    $keuntungan = isset($_POST['keuntungan']) ? (float)$_POST['keuntungan'] : 0;
    $fid_kategori = isset($_POST['fid_kategori']) ? (int)$_POST['fid_kategori'] : 0;
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi'] ?? '');

    // ❗Cek apakah nama produk sudah ada
    $cek_nama = mysqli_query($conn, "SELECT * FROM produk WHERE nama_produk = '$nama_produk'");
    if (mysqli_num_rows($cek_nama) > 0) {
        echo "<script>
            alert('Nama produk sudah terdaftar!');
            window.location='produk.php';
        </script>";
        exit;
    }
    

    // Upload Gambar ke Folder "uploads"
    $gambar = "";
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

    // Insert ke Database
    $query = "INSERT INTO produk (nama_produk, barcode, stok, modal, harga_jual, keuntungan, fid_kategori, deskripsi, gambar) 
              VALUES ('$nama_produk', '$barcode', '$stok', '$modal', '$harga_jual', '$keuntungan', '$fid_kategori', '$deskripsi', '$gambar')";

    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Produk berhasil ditambahkan!'); window.location='produk.php';</script>";
    } else {
        die("<script>alert('Gagal menambahkan produk: " . mysqli_error($conn) . "'); window.history.back();</script>");
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Tambah Produk</title>
</head>
<body class="bg-[#A0C878] flex justify-center items-center h-screen">
    <form action="tambah_produk.php" method="POST" enctype="multipart/form-data" class="bg-white p-8 rounded-lg shadow-md w-[600px]">
        <div class="text-center mb-6">
            <h1 class="text-green-500 text-2xl font-bold">QuickPay</h1>
            <p class="text-sm text-gray-600">Tambah Produk Baru</p>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-gray-700 mb-1">Nama Produk</label>
                <input type="text" name="nama_produk" required class="w-full p-2 bg-[#CBE0A5] rounded-md">
                
                <label class="block text-gray-700 mb-1">Barcode Produk (Opsional)</label>
                <input type="text" name="barcode" class="w-full p-2 bg-[#CBE0A5] rounded-md" placeholder="Otomatis jika dikosongkan">
                
                <label class="block text-gray-700 mb-1">Stok</label>
                <input type="number" name="stok" required class="w-full p-2 bg-[#CBE0A5] rounded-md">
                
                <label class="block text-gray-700 mb-1">Modal</label>
                <input type="text" name="modal" required class="w-full p-2 bg-[#CBE0A5] rounded-md">
            </div>
            <div>
                <label class="block text-gray-700 mb-1">Harga Jual</label>
                <input type="text" name="harga_jual" required class="w-full p-2 bg-[#CBE0A5] rounded-md">
                
                <label class="block text-gray-700 mb-1">Keuntungan</label>
                <input type="text" name="keuntungan" required class="w-full p-2 bg-[#CBE0A5] rounded-md">
                
                <label class="block text-gray-700 mb-1">Kategori</label>
<select name="fid_kategori" required class="w-full p-2 rounded-3xl bg-[#CBE0A5] border-none">
                                <option value="">-- Pilih Kategori --</option>
                                <?php
                                $result = $conn->query("SELECT * FROM kategori");
                                while ($row = $result->fetch_assoc()) {
                                    echo "<option value='" . $row['id'] . "'>" . $row['kategori'] . "</option>";
                                }
                                ?>
                            </select>

                <label class="block text-gray-700 mb-1">Deskripsi</label>
                <input type="text" name="deskripsi" required class="w-full p-2 bg-[#CBE0A5] rounded-md">
            </div>
        </div>

        <div class="mt-4">
            <label class="block text-gray-700 mb-1">Gambar (JPG/PNG)</label>
            <input type="file" name="gambar" class="w-full p-2 bg-[#CBE0A5] rounded-md">
        </div>

        <div class="text-center mt-4">
            <button type="submit" class="bg-[#88AA5B] text-white px-6 py-2 rounded-md shadow-md hover:bg-[#7B964F]">
                Confirm
            </button>
            <a href="produk.php" class="bg-gray-300 text-gray-800 px-6 py-2 rounded-md shadow-md hover:bg-gray-400">Kembali</a>
        </div>
    </form>
</body>
</html>
