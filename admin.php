<?php
session_start();
include 'connection.php';

if (isset($_SESSION['notif'])) {
    echo "<script>alert('" . $_SESSION['notif'] . "');</script>";
    unset($_SESSION['notif']);
}

// Set semua admin menjadi "tidak aktif" terlebih dahulu
mysqli_query($conn, "UPDATE admin SET status='tidak aktif'");

// Jika ada admin yang sedang login, set statusnya menjadi "aktif"
if (isset($_SESSION['id'])) {
    $admin_id = $_SESSION['id'];
    mysqli_query($conn, "UPDATE admin SET status='aktif' WHERE id='$admin_id'");
}

// Ambil data admin dari database
$query = "SELECT * FROM admin";
$result = mysqli_query($conn, $query);

// Hapus admin jika ada request delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    // Cek status admin sebelum menghapus
    $check_query = mysqli_query($conn, "SELECT status FROM admin WHERE id='$id'");
    $data = mysqli_fetch_assoc($check_query);

    if ($data && $data['status'] == 'aktif') {
        echo "<script>alert('Akun sedang aktif, tidak bisa dihapus!'); window.location='admin.php';</script>";
    } else {
        mysqli_query($conn, "DELETE FROM admin WHERE id='$id'");

        // Jika admin yang sedang login dihapus, logout otomatis
        if ($id == $_SESSION['id']) {
            session_destroy();
            header("Location: login.php");
            exit();
        }

        header("Location: admin.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;600&display=swap" rel="stylesheet">
<style>
    body {
        font-family: 'Ubuntu', sans-serif;
    }
</style>
    <title>Data Admin</title>
</head>
<body class="bg-gray-100">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <!-- Sidebar -->
<div class="w-1/5 bg-[#DDEB9D] p-5">
    <!-- Logo QuickPay -->
    <div class="flex items-center space-x-3 mb-6">
        <!-- Logo SVG QuickPay -->
        <svg class="w-9 h-9 text-green-700" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="50" cy="50" r="40" stroke="currentColor" stroke-width="10" fill="none"/>
            <path d="M65 65 L85 85" stroke="currentColor" stroke-width="10" stroke-linecap="round"/>
            <path d="M40 50 h20 a10 10 0 0 0 0 -20 h-20 a10 10 0 0 0 0 20 z" fill="currentColor"/>
        </svg>
        <h1 class="text-green-700 text-2xl font-bold">QuickPay</h1>
    </div>

    <ul class="space-y-2">
    <li><a href="dashboard.php" class="flex items-center p-2 hover:bg-[#A0C878] text-black rounded"><span>🏠</span><span class="ml-2">Beranda</span></a></li>
        <li><a href="admin.php" class="flex items-center p-2 bg-[#A0C878] text-black hover:text-black rounded"><span>🧑‍💼</span><span class="ml-2">Admin</span></a></li>
        <li><a href="member.php" class="flex items-center p-2 hover:bg-[#A0C878] text-black hover:text-black rounded"><span>👥</span><span class="ml-2">Member</span></a></li>
        <li><a href="kategori.php" class="flex items-center p-2 hover:bg-[#A0C878] text-black hover:text-black rounded"><span>📂</span><span class="ml-2">Kategori</span></a></li>
        <li><a href="produk.php" class="flex items-center p-2 hover:bg-[#A0C878] text-black hover:text-black rounded"><span>📦</span><span class="ml-2">Produk</span></a></li>
        <li><a href="transaksi.php" class="flex items-center p-2 hover:bg-[#A0C878] text-black hover:text-black rounded"><span>💳</span><span class="ml-2">Transaksi</span></a></li>
        <li><a href="keranjang.php" class="flex items-center p-2 hover:bg-[#A0C878] text-black hover:text-black rounded"><span>🛒</span><span class="ml-2">Keranjang</span></a></li>
        <li><a href="laporan.php" class="flex items-center p-2 hover:bg-[#A0C878] text-black hover:text-black rounded"><span>📊</span><span class="ml-2">Laporan</span></a></li>
    </ul>
    <a href="logout.php" class="block bg-[#88AA5B] text-white text-center p-2 mt-6 rounded hover:bg-[#7B964F]"><span>📴</span> Logout</a>

</div>

        <!-- Konten -->
        <div class="w-3/4 p-10">
            <h2 class="text-3xl font-semibold mb-5">Data Admin</h2>
            <div class="mb-5 flex">
                <input type="text" placeholder="Tambah Admin" class="border p-2 rounded w-full">
                <a href="tambah admin.php" class="bg-[#A0C878] text-white px-4 py-2 ml-2 rounded">➕ Tambah</a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                <?php while ($admin = mysqli_fetch_assoc($result)) :
                    $gambar = "uploads/default.jpg"; // Default gambar jika tidak ada
                    if (!empty($admin['gambar'])) {
                        $gambar_path = "uploads/" . $admin['gambar'];
                        if (file_exists($gambar_path)) {
                            $gambar = $gambar_path;
                        }
                    }
                    
                    $status_text = ($admin['status'] == 'aktif') ? "Aktif" : "Tidak Aktif";
                    $status_class = ($admin['status'] == 'aktif') ? "text-green-500" : "text-red-500";
                ?>
                    <div class="bg-white p-5 rounded-lg shadow-md">
                        <img src="<?= htmlspecialchars($gambar); ?>" alt="Admin" class="w-full h-32 object-cover rounded">
                        <p class="mt-3"><strong>Id:</strong> <?= htmlspecialchars($admin['id']); ?></p>
                        <p><strong>Email:</strong> <?= htmlspecialchars($admin['email']); ?></p>
                        <p><strong>Username:</strong> <?= htmlspecialchars($admin['username']); ?></p>
                        <p><strong>Status:</strong> <span class="<?= $status_class; ?>"> <?= $status_text; ?></span></p>
                        <div class="flex justify-between mt-3">
                            <a href="edit admin.php?id=<?= htmlspecialchars($admin['id']); ?>" class="text-blue-500">✏ Edit</a>
                            <?php if ($admin['status'] == 'tidak aktif') : ?>
                                <a href="admin.php?delete=<?= htmlspecialchars($admin['id']); ?>" class="text-red-500" onclick="return confirm('Yakin ingin menghapus?');">🗑 Hapus</a>
                            <?php else : ?>
                                <span class="text-gray-400">❌ Tidak bisa hapus</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
</body>
</html>