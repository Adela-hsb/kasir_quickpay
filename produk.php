<?php
session_start();
include 'connection.php';

// // Cek transaksi yang selesai dan update stok produk
// $query_transaksi = "SELECT id_produk, SUM(jumlah) AS total_terjual 
//                     FROM transaksi 
//                     WHERE tanggal_pembelian IS NOT NULL 
//                     GROUP BY id_produk";

// $result_transaksi = mysqli_query($conn, $query_transaksi);

// if (!$result_transaksi) {
//     die("Query transaksi error: " . mysqli_error($conn)); // Menampilkan pesan error jika query gagal
// }

// while ($row = mysqli_fetch_assoc($result_transaksi)) {
//     $id_produk = $row['id_produk'];
//     $total_terjual = $row['total_terjual'];

//     // Update stok produk
//     $query_update_stok = "UPDATE produk SET stok = stok - ? WHERE id_produk = ?";
//     $stmt_update_stok = mysqli_prepare($conn, $query_update_stok);
//     mysqli_stmt_bind_param($stmt_update_stok, "ii", $total_terjual, $id_produk);

//     if (!mysqli_stmt_execute($stmt_update_stok)) {
//         echo "<script>alert('Gagal mengupdate stok produk: " . mysqli_error($conn) . "');</script>";
//     }

//     mysqli_stmt_close($stmt_update_stok);
// }

// Ambil data produk dari database dengan join ke tabel kategori
$query = "SELECT p.id_produk, p.nama_produk, k.kategori AS kategori, p.harga_jual, p.gambar, p.stok 
          FROM produk p 
          LEFT JOIN kategori k ON p.fid_kategori = k.id";

$result = mysqli_query($conn, $query);

if (!$result) {
    die("Query error: " . mysqli_error($conn));
}
?>



<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produk</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="flex min-h-screen">
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
        <li><a href="admin.php" class="flex items-center p-2 hover:bg-[#A0C878] text-black hover:text-black rounded"><span>🧑‍💼</span><span class="ml-2">Admin</span></a></li>
        <li><a href="member.php" class="flex items-center p-2 hover:bg-[#A0C878] text-black hover:text-black rounded"><span>👥</span><span class="ml-2">Member</span></a></li>
        <li><a href="kategori.php" class="flex items-center p-2 hover:bg-[#A0C878] text-black hover:text-black rounded"><span>📂</span><span class="ml-2">Kategori</span></a></li>
        <li><a href="produk.php" class="flex items-center p-2 bg-[#A0C878] text-black hover:text-black rounded"><span>📦</span><span class="ml-2">Produk</span></a></li>
        <li><a href="transaksi.php" class="flex items-center p-2 hover:bg-[#A0C878] text-black hover:text-black rounded"><span>💳</span><span class="ml-2">Transaksi</span></a></li>
        <li><a href="keranjang.php" class="flex items-center p-2 hover:bg-[#A0C878] text-black hover:text-black rounded"><span>🛒</span><span class="ml-2">Keranjang</span></a></li>
        <li><a href="laporan.php" class="flex items-center p-2 hover:bg-[#A0C878] text-black hover:text-black rounded"><span>📊</span><span class="ml-2">Laporan</span></a></li>
    </ul>
    <a href="logout.php" class="block bg-[#88AA5B] text-white text-center p-2 mt-6 rounded hover:bg-[#7B964F]"><span>📴</span> Logout</a>

</div>

        <!-- Konten utama -->
        <main class="flex-1 p-6">
            <h2 class="text-3xl font-semibold mb-5 text-center">Produk</h2>
            <div class="flex justify-between mb-4">
                <button onclick="window.location.href='tambah_produk.php';" class="bg-[#A0C878] text-black px-4 py-2 rounded hover:bg-[#DDEB9D]]">
                    Tambah Produk ➕
                </button>
            </div>

            <?php if (mysqli_num_rows($result) > 0) : ?>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                    <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                        <div class="bg-[#DDEB9D] p-4 rounded shadow-md">
                            <img src="uploads/<?php echo htmlspecialchars($row['gambar']); ?>" 
                                 alt="<?php echo htmlspecialchars($row['nama_produk']); ?>" 
                                 class="w-full h-48 object-cover rounded">
                            <h3 class="text-lg font-semibold mt-2"><?php echo htmlspecialchars($row['nama_produk']); ?></h3>
                            <p class="text-sm text-gray-600">Kategori: <?php echo !empty($row['kategori']) ? htmlspecialchars($row['kategori']) : 'Tidak Ada'; ?></p>
                            <p class="text-sm text-gray-800">Harga: Rp. <?php echo number_format($row['harga_jual'], 0, ',', '.') . ",-"; ?></p>
                            <p class="text-sm text-red-600 font-bold">Stok: <?php echo $row['stok']; ?></p>
                            <div class="mt-3 flex justify-between items-center">
                                <a href="detail_produk.php?id=<?php echo $row['id_produk']; ?>" class="bg-[#A0C878] text-black  px-3 py-1 rounded text-sm">Detail</a>
                                <div>
    <a href="edit_produk.php?id=<?php echo $row['id_produk']; ?>" class="text-blue-500">✏</a>
    <?php if ($row['stok'] == 0) : ?>
        <!-- Icon sampah merah, bisa dihapus -->
        <a href="produk.php?delete=<?php echo $row['id_produk']; ?>" 
           class="text-red-500 ml-2 hover:text-red-700" 
           onclick="return confirm('Yakin ingin menghapus?');" 
           title="Hapus produk">
           🗑
        </a>
    <?php else : ?>
        <!-- Icon sampah abu-abu, disabled -->
        <span class="text-gray-400 ml-2 cursor-not-allowed" title="Tidak bisa dihapus karena stok masih ada">🗑</span>
    <?php endif; ?>
</div>

                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else : ?>
                <p class="text-center text-gray-600">Tidak ada produk tersedia.</p>
            <?php endif; ?>
        </main>
    </div>

<?php
// Hapus produk jika ada request delete dan stoknya 0
if (isset($_GET['delete'])) {
    $id_hapus = mysqli_real_escape_string($conn, $_GET['delete']);

    // Cek stok dan gambar
    $stmt_check = mysqli_prepare($conn, "SELECT stok, gambar FROM produk WHERE id_produk = ?");
    mysqli_stmt_bind_param($stmt_check, "i", $id_hapus);
    mysqli_stmt_execute($stmt_check);
    $result_check = mysqli_stmt_get_result($stmt_check);
    $data = mysqli_fetch_assoc($result_check);
    mysqli_stmt_close($stmt_check);

    if (!$data) {
        echo "<script>alert('Produk tidak ditemukan.'); window.location='produk.php';</script>";
    } elseif ($data['stok'] > 0) {
        echo "<script>alert('Tidak bisa menghapus, stok masih ada!'); window.location='produk.php';</script>";
    } else {
        // Hapus gambar dari server
        $gambar_path = "uploads/" . $data['gambar'];
        if (!empty($data['gambar']) && file_exists($gambar_path)) {
            unlink($gambar_path);
        }

        // Hapus produk dari database
        $stmt = mysqli_prepare($conn, "DELETE FROM produk WHERE id_produk = ?");
        mysqli_stmt_bind_param($stmt, "i", $id_hapus);
        
        if (mysqli_stmt_execute($stmt)) {
            echo "<script>alert('Produk berhasil dihapus!'); window.location='produk.php';</script>";
        } else {
            echo "<script>alert('Gagal menghapus produk: " . mysqli_error($conn) . "');</script>";
        }

        mysqli_stmt_close($stmt);
    }
}
?>
</body>
</html>
