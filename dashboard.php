<?php
session_start();
include 'connection.php';

// Ambil data statistik utama
$kategori = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM kategori"))['total'] ?? 0;
$total_produk_terjual = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(jumlah) as total FROM transaksi"))['total'] ?? 0;
$total_transaksi = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM transaksi"))['total'] ?? 0;

$pendapatan_harian = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total_harga) as total FROM transaksi WHERE DATE(tanggal_pembelian) = CURDATE()"))['total'] ?? 0;
$pendapatan_mingguan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total_harga) as total FROM transaksi WHERE WEEK(tanggal_pembelian) = WEEK(CURDATE()) AND YEAR(tanggal_pembelian) = YEAR(CURDATE())"))['total'] ?? 0;
$pendapatan_bulanan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total_harga) as total FROM transaksi WHERE MONTH(tanggal_pembelian) = MONTH(CURDATE()) AND YEAR(tanggal_pembelian) = YEAR(CURDATE())"))['total'] ?? 0;
$pendapatan_tahunan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total_harga) as total FROM transaksi WHERE YEAR(tanggal_pembelian) = YEAR(CURDATE())"))['total'] ?? 0;

// Data penjualan 6 bulan terakhir
$penjualan_per_bulan = [];
$bulan_labels = [];
for ($i = 5; $i >= 0; $i--) {
    $bulan = date('M', strtotime("-$i month"));
    $bulan_labels[] = $bulan;
    $tahun_bulan = date('Y-m', strtotime("-$i month"));
    $query = mysqli_query($conn, "SELECT SUM(total_harga) as total FROM transaksi WHERE DATE_FORMAT(tanggal_pembelian, '%Y-%m') = '$tahun_bulan'");
    $hasil = mysqli_fetch_assoc($query);
    $penjualan_per_bulan[] = $hasil['total'] ?? 0;
}

// Produk terlaris (Top 5)
$produk_labels = [];
$produk_terlaris = [];
$query_produk = mysqli_query($conn, "
    SELECT produk.nama_produk, SUM(transaksi.jumlah) as total_terjual 
    FROM transaksi 
    JOIN produk ON transaksi.id_produk = produk.id_produk 
    GROUP BY transaksi.id_produk 
    ORDER BY total_terjual DESC 
    LIMIT 5
");
while ($row = mysqli_fetch_assoc($query_produk)) {
    $produk_labels[] = $row['nama_produk'];
    $produk_terlaris[] = $row['total_terjual'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: 'Ubuntu', sans-serif;
        }
    </style>
    <title>Dashboard Kasir</title>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">

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
            <li><a href="dashboard.php" class="flex items-center p-2 bg-[#A0C878] text-black rounded"><span>🏠</span><span class="ml-2">Beranda</span></a></li>
            <li><a href="admin.php" class="flex items-center p-2 hover:bg-[#A0C878] rounded"><span>🧑‍💼</span><span class="ml-2">Admin</span></a></li>
            <li><a href="member.php" class="flex items-center p-2 hover:bg-[#A0C878] rounded"><span>👥</span><span class="ml-2">Member</span></a></li>
            <li><a href="kategori.php" class="flex items-center p-2 hover:bg-[#A0C878] rounded"><span>📂</span><span class="ml-2">Kategori</span></a></li>
            <li><a href="produk.php" class="flex items-center p-2 hover:bg-[#A0C878] rounded"><span>📦</span><span class="ml-2">Produk</span></a></li>
            <li><a href="transaksi.php" class="flex items-center p-2 hover:bg-[#A0C878] rounded"><span>💳</span><span class="ml-2">Transaksi</span></a></li>
            <li><a href="keranjang.php" class="flex items-center p-2 hover:bg-[#A0C878] rounded"><span>🛒</span><span class="ml-2">Keranjang</span></a></li>
            <li><a href="laporan.php" class="flex items-center p-2 hover:bg-[#A0C878] rounded"><span>📊</span><span class="ml-2">Laporan</span></a></li>
        </ul>

        <a href="logout.php" class="block bg-[#88AA5B] text-white text-center p-2 mt-6 rounded hover:bg-[#7B964F]"><span>📴</span> Logout</a>
    </div>

    <!-- Main Content -->
    <div class="flex-1 p-6 overflow-y-auto">
        <div class="bg-[#A0C878] p-4 flex justify-between items-center rounded">
            <h2 class="text-lg font-semibold">Dashboard</h2>
            <div class="flex items-center">
                <span class="mr-2">👤</span> <span><?php echo $_SESSION['username'] ?? 'Guest'; ?></span>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-4 gap-4 mt-5">
            <div class="bg-[#DDEB9D] p-4 text-center rounded shadow-md">Kategori <br> <span class="text-xl font-bold"><?= $kategori; ?></span></div>
            <div class="bg-[#DDEB9D] p-4 text-center rounded shadow-md">Produk Terjual <br> <span class="text-xl font-bold"><?= $total_produk_terjual; ?></span></div>
            <div class="bg-[#DDEB9D] p-4 text-center rounded shadow-md">Total Transaksi <br> <span class="text-xl font-bold"><?= $total_transaksi; ?></span></div>
            <div class="bg-[#A0C878] p-4 text-center rounded shadow-md">Pendapatan Harian <br> Rp. <?= number_format($pendapatan_harian, 0, ',', '.'); ?></div>
            <div class="bg-[#A0C878] p-4 text-center rounded shadow-md">Pendapatan Mingguan <br> Rp. <?= number_format($pendapatan_mingguan, 0, ',', '.'); ?></div>
            <div class="bg-[#A0C878] p-4 text-center rounded shadow-md">Pendapatan Bulanan <br> Rp. <?= number_format($pendapatan_bulanan, 0, ',', '.'); ?></div>
            <div class="bg-[#A0C878] p-4 text-center rounded shadow-md">Pendapatan Tahunan <br> Rp. <?= number_format($pendapatan_tahunan, 0, ',', '.'); ?></div>
        </div>

        <!-- Charts -->
        <div class="grid grid-cols-2 gap-4 mt-6">
            <div class="bg-white p-4 shadow-md rounded">
                <h3 class="text-center mb-2 font-semibold">Penjualan 6 Bulan Terakhir</h3>
                <canvas id="salesChart"></canvas>
            </div>
            <div class="bg-white p-4 shadow-md rounded">
                <h3 class="text-center mb-2 font-semibold">Top 5 Produk Terlaris</h3>
                <canvas id="pieChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
    const ctx = document.getElementById('salesChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode($bulan_labels); ?>,
            datasets: [{
                label: 'Penjualan (Rp)',
                data: <?= json_encode($penjualan_per_bulan); ?>,
                borderColor: 'blue',
                backgroundColor: 'rgba(0, 0, 255, 0.1)',
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: { display: true, text: 'Total Penjualan per Bulan' },
                legend: { display: false }
            },
            scales: { y: { beginAtZero: true } }
        }
    });

    const ctx2 = document.getElementById('pieChart').getContext('2d');
    new Chart(ctx2, {
        type: 'pie',
        data: {
            labels: <?= json_encode($produk_labels); ?>,
            datasets: [{
                data: <?= json_encode($produk_terlaris); ?>,
                backgroundColor: ['green', 'blue', 'yellow', 'purple', 'orange']
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: { display: true, text: 'Produk Terlaris' },
                legend: { position: 'bottom' }
            }
        }
    });
</script>
</body>
</html>
