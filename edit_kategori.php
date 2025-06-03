<?php
include 'connection.php';

// Ambil ID kategori dari URL
$id_kategori = isset($_GET['id']) ? intval($_GET['id']) : 0;
$nama_kategori = '';

// Ambil data kategori lama
if ($id_kategori > 0) {
    $query = "SELECT * FROM kategori WHERE id = $id_kategori";
    $result = mysqli_query($conn, $query);
    if ($row = mysqli_fetch_assoc($result)) {
        $nama_kategori = $row['kategori'];
    } else {
        echo "<script>alert('Kategori tidak ditemukan.'); window.location='kategori.php';</script>";
        exit;
    }
}

// Proses update data jika form disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_baru = trim($_POST['kategori']);

    // Cek apakah nama kategori sudah ada (selain yang sekarang)
    $cek_nama = mysqli_query($conn, "SELECT id FROM kategori WHERE kategori = '$nama_baru' AND id != $id_kategori");
    if (mysqli_num_rows($cek_nama) > 0) {
        echo "<script>alert('Gagal: Nama Kategori sudah ada.'); window.location='kategori.php';</script>";
        exit;
    }

    // Update hanya nama kategori
    $update = mysqli_query($conn, "UPDATE kategori SET kategori = '$nama_baru' WHERE id = $id_kategori");

    if ($update) {
        echo "<script>alert('Kategori berhasil diperbarui!'); window.location='kategori.php';</script>";
    } else {
        echo "<script>alert('Gagal memperbarui kategori.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Edit Kategori</title>
</head>
<body class="flex items-center justify-center min-h-screen bg-[#A0C878]">
    <div class="bg-white p-8 rounded-lg shadow-md w-[600px]">
        <div class="text-center mb-6">
            <h1 class="text-green-500 text-2xl font-bold">QuickPay</h1>
            <p class="text-sm text-gray-600">Edit Kategori</p>
        </div>

        <form method="POST" action="edit_kategori.php">
            <div class="mb-4">
                <label class="block text-gray-700 mb-1">Nama Kategori</label>
                <input type="text" name="kategori" value="<?= htmlspecialchars($nama_kategori) ?>" class="w-full p-2 bg-[#CBE0A5] rounded-md" required>
            </div>

            <div class="text-center">
                <button type="submit" class="bg-[#88AA5B] text-white px-6 py-2 rounded-md shadow-md hover:bg-[#7B964F]">
                    Confirm
                </button>
                <a href="kategori.php" class="bg-gray-300 text-gray-800 px-6 py-2 rounded-md shadow-md hover:bg-gray-400">Kembali</a>
            </div>
        </form>
    </div>
</body>
</html>
