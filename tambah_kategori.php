<?php
include 'connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $kategori = mysqli_real_escape_string($conn, trim($_POST['kategori']));

    if (empty($kategori)) {
        echo "<script>
            alert('Nama kategori tidak boleh kosong.');
            window.location='tambah_kategori.php';
        </script>";
        exit;
    }

    $cek = mysqli_query($conn, "SELECT * FROM kategori WHERE kategori = '$kategori'");
    if (!$cek) {
        echo "<script>
            alert('Terjadi kesalahan saat mengecek data.');
            window.location='tambah_kategori.php';
        </script>";
        exit;
    }

    if (mysqli_num_rows($cek) > 0) {
        echo "<script>
            alert('Gagal: Nama Kategori sudah ada.');
            window.location='tambah_kategori.php';
        </script>";
    } else {
        $insert = mysqli_query($conn, "INSERT INTO kategori (kategori) VALUES ('$kategori')");
        if ($insert) {
            echo "<script>
                alert('Kategori berhasil ditambahkan!');
                window.location='kategori.php';
            </script>";
        } else {
            echo "<script>
                alert('Gagal menambahkan kategori.');
                window.location='tambah_kategori.php';
            </script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Kategori</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#A0C878] flex justify-center items-center min-h-screen">
    <div class="bg-white p-8 rounded-lg shadow-md w-[500px]">
        <div class="text-center mb-6">
            <h1 class="text-green-500 text-2xl font-bold">QuickPay</h1>
            <p class="text-sm text-gray-600">Tambah Kategori Produk</p>
        </div>

        <form method="POST">
            <div class="mb-4">
                <label for="kategori" class="block text-gray-700 mb-1">Nama Kategori</label>
                <input type="text" id="kategori" name="kategori" class="w-full p-2 bg-[#CBE0A5] border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-400" required>
            </div>

            <div class="flex justify-between mt-6">
                <a href="kategori.php" class="bg-gray-300 text-gray-700 px-6 py-2 rounded-md hover:bg-gray-400">
                    Kembali
                </a>
                <button type="submit" class="bg-[#88AA5B] text-white px-6 py-2 rounded-md shadow-md hover:bg-[#7B964F]">
                    Confirm
                </button>
            </div>
        </form>
    </div>
</body>
</html>
