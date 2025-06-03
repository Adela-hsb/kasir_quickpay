<?php
include 'connection.php';

// Ambil data transaksi (produk yang bisa dipesan)
$query_transaksi = "SELECT p.id_produk, p.nama_produk, k.kategori AS kategori, p.harga_jual, p.gambar 
                    FROM produk p 
                    LEFT JOIN kategori k ON p.fid_kategori = k.id 
                    WHERE p.stok > 0";
$result_transaksi = mysqli_query($conn, $query_transaksi);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Transaksi</title>
</head>
<body class="bg-gray-100">
    <div class="flex">
        <!-- Sidebar -->
        <div class="w-1/5 bg-[#DDEB9D] p-5">
            <div class="flex items-center space-x-3 mb-6">
                <svg class="w-9 h-9 text-green-700" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="50" cy="50" r="40" stroke="currentColor" stroke-width="10" fill="none"/>
                    <path d="M65 65 L85 85" stroke="currentColor" stroke-width="10" stroke-linecap="round"/>
                    <path d="M40 50 h20 a10 10 0 0 0 0 -20 h-20 a10 10 0 0 0 0 20 z" fill="currentColor"/>
                </svg>
                <h1 class="text-green-700 text-2xl font-bold">QuickPay</h1>
            </div>

            <ul class="space-y-2">
                <li><a href="dashboard.php" class="flex items-center p-2 hover:bg-[#A0C878] text-black rounded"><span>🏠</span><span class="ml-2">Beranda</span></a></li>
                <li><a href="admin.php" class="flex items-center p-2 hover:bg-[#A0C878] text-black rounded"><span>🧑‍💼</span><span class="ml-2">Admin</span></a></li>
                <li><a href="member.php" class="flex items-center p-2 hover:bg-[#A0C878] text-black rounded"><span>👥</span><span class="ml-2">Member</span></a></li>
                <li><a href="kategori.php" class="flex items-center p-2 hover:bg-[#A0C878] text-black rounded"><span>📂</span><span class="ml-2">Kategori</span></a></li>
                <li><a href="produk.php" class="flex items-center p-2 hover:bg-[#A0C878] text-black rounded"><span>📦</span><span class="ml-2">Produk</span></a></li>
                <li><a href="transaksi.php" class="flex items-center p-2 bg-[#A0C878] text-black rounded"><span>💳</span><span class="ml-2">Transaksi</span></a></li>
                <li><a href="keranjang.php" class="flex items-center p-2 hover:bg-[#A0C878] text-black rounded"><span>🛒</span><span class="ml-2">Keranjang</span></a></li>
                <li><a href="laporan.php" class="flex items-center p-2 hover:bg-[#A0C878] text-black rounded"><span>📊</span><span class="ml-2">Laporan</span></a></li>
            </ul>
            <a href="logout.php" class="block bg-[#88AA5B] text-white text-center p-2 mt-6 rounded hover:bg-[#7B964F]"><span>📴</span> Logout</a>
        </div>

        <!-- Konten Utama -->
        <div class="w-4/5 p-6">
            <div class="bg-[#DDEB9D] p-4 flex justify-between items-center">
                <h2 class="text-lg font-bold">Transaksi</h2>
            </div>

            <!-- Input Barcode untuk scanner alat biasa -->
            <div class="mb-4 mt-6 max-w-sm">
                <form action="keranjang.php" method="get" id="barcodeForm" autocomplete="off">
                    <label for="barcodeInput" class="block mb-1 font-semibold">Scan atau Masukkan Kode Barcode:</label>
                    <input 
                        type="text" 
                        id="barcodeInput" 
                        name="barcode" 
                        class="border p-2 rounded w-full" 
                        autofocus 
                        autocomplete="off"
                        placeholder="Scan barcode di sini..." />
                    <button type="submit" class="mt-2 bg-green-600 text-white px-4 py-1 rounded-md">🔍 Cari Produk</button>
                </form>
            </div>

            <!-- Daftar Produk -->
            <div class="grid grid-cols-3 gap-4">
                <?php while ($row = mysqli_fetch_assoc($result_transaksi)) : ?>
                    <div class="bg-yellow-50 p-4 shadow-md rounded-md">
                        <img src="uploads/<?php echo htmlspecialchars($row['gambar']); ?>" alt="<?php echo htmlspecialchars($row['nama_produk']); ?>" class="w-full h-40 object-cover rounded-md" />
                        <p class="mt-2 text-sm">Nama Produk: <?php echo htmlspecialchars($row['nama_produk']); ?></p>
                        <p class="text-sm">Kategori: <?php echo !empty($row['kategori']) ? htmlspecialchars($row['kategori']) : 'Tidak Ada'; ?></p>
                        <p class="text-sm font-bold">Harga: Rp. <?php echo number_format($row['harga_jual'], 0, ',', '.'); ?></p>

                        <form action="keranjang.php" method="post">
                            <input type="hidden" name="id_produk" value="<?php echo $row['id_produk']; ?>" />
                            <input type="hidden" name="nama_produk" value="<?php echo htmlspecialchars($row['nama_produk']); ?>" />
                            <input type="hidden" name="kategori" value="<?php echo htmlspecialchars($row['kategori']); ?>" />
                            <input type="hidden" name="harga" value="<?php echo $row['harga_jual']; ?>" />
                            <input type="hidden" name="gambar" value="<?php echo htmlspecialchars($row['gambar']); ?>" />
                            <button type="submit" class="mt-3 bg-[#88AA5B] text-white w-full py-1 rounded-md">
                                🛒 Pesan
                            </button>
                        </form>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>

    <script>
        // Fokus input barcode otomatis saat halaman selesai dimuat
        window.onload = function() {
            document.getElementById('barcodeInput').focus();
        };

        // Submit otomatis ketika Enter ditekan di input barcode
        const barcodeInput = document.getElementById('barcodeInput');
        barcodeInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('barcodeForm').submit();
            }
        });

        // Auto-submit otomatis 0.5 detik setelah input barcode terisi (untuk scanner tanpa Enter)
        let timeout = null;
        barcodeInput.addEventListener('input', function() {
            clearTimeout(timeout);
            if (barcodeInput.value.trim() !== '') {
                timeout = setTimeout(() => {
                    document.getElementById('barcodeForm').submit();
                }, 500); // 500 ms setelah berhenti input
            }
        });
    </script>

    <?php mysqli_close($conn); ?>
</body>
</html>
