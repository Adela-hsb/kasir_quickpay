<?php
session_start();
include 'connection.php';

// Pastikan user sudah login
if (!isset($_SESSION['username'])) {
    die("Error: User belum login.");
}

$username = mysqli_real_escape_string($conn, $_SESSION['username']);

// Ambil data admin
$queryAdmin = "SELECT username FROM admin WHERE username = '$username' LIMIT 1";
$resultAdmin = mysqli_query($conn, $queryAdmin);
$admin = "Tidak Diketahui";

if ($rowAdmin = mysqli_fetch_assoc($resultAdmin)) {
    $admin = $rowAdmin['username'];
}

// Pastikan ada data yang dikirim dari checkbox
if (!isset($_POST['pilih']) || count($_POST['pilih']) === 0) {
    die("Tidak ada produk yang dipilih untuk checkout.");
}

$totalHarga = 0;
$produkList = [];
$id_member = '';
$no_telp = '';
$diskon = 0;

// Ambil hanya produk yang dipilih
foreach ($_POST['pilih'] as $id_produk) {
    if (isset($_SESSION['keranjang'][$id_produk])) {
        $item = $_SESSION['keranjang'][$id_produk];
        $subtotal = $item['harga'] * $item['jumlah'];
        $totalHarga += $subtotal;
        $produkList[] = "{$item['nama_produk']} ({$item['jumlah']}x)";
    }
}

$produkStr = implode(", ", $produkList);

// Simpan detail produk dan harga ke session
$_SESSION['detail'] = $produkList;
$_SESSION['produk_harga'] = array_map(function($item) { return $item['harga']; }, $_SESSION['keranjang']);

if (isset($_POST['no_telp'])) {
    $no_telp = $_POST['no_telp'];

    $queryMember = "SELECT point, status FROM member WHERE no_telp = '$no_telp' LIMIT 1";
    $resultMember = mysqli_query($conn, $queryMember);
    if ($rowMember = mysqli_fetch_assoc($resultMember)) {
        $status_member = $rowMember['status'] == 'aktif' ? 'Member Aktif' : 'Member Tidak Aktif';
        $poin = $rowMember['point'];

        if ($status_member == 'Member Aktif') {
            $diskon = 10;
        }
    } else {
        $status_member = 'Bukan Member';
    }
} else {
    $status_member = 'Bukan Member';
}

$potongan = ($totalHarga * $diskon) / 100;
$totalSetelahDiskon = $totalHarga - $potongan;

if (!empty($no_telp) && $status_member == 'Member Aktif') {
    $updatePoinQuery = "UPDATE member SET point = point + 50 WHERE no_telp = '$no_telp'";
    if (mysqli_query($conn, $updatePoinQuery)) {
        $_SESSION['poin_diperbarui'] = true;
    } else {
        echo "<p class='text-red-500 mt-2'>Gagal memperbarui poin member.</p>";
    }
}

$_SESSION['diskon'] = $diskon;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Konfirmasi Pembayaran</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex justify-center items-center min-h-screen">

<div class="bg-white p-6 rounded-lg shadow-md w-full max-w-3xl">
    <h1 class="text-green-700 text-2xl font-bold mb-4 text-center">QuickPay</h1>
    <h2 class="text-2xl font-bold mb-4">Konfirmasi Pembayaran</h2>
    <table class="w-full">
        <tr><td class="py-2">Tanggal Pembelian</td><td class="py-2 text-gray-700"><?php echo date("Y-m-d"); ?></td></tr>
        <tr><td class="py-2">Produk</td><td class="py-2 text-gray-700"><?php echo htmlspecialchars($produkStr); ?></td></tr>
        <tr><td class="py-2">Total Harga</td><td class="py-2 text-gray-700">Rp<?php echo number_format($totalHarga, 0, ',', '.'); ?></td></tr>
        <tr><td class="py-2">Diskon</td><td class="py-2 text-gray-700"><?php echo $diskon; ?>% (-Rp<?php echo number_format($potongan, 0, ',', '.'); ?>)</td></tr>
        <tr><td class="py-2">Total Setelah Diskon</td><td class="py-2 text-gray-700">Rp<?php echo number_format($totalSetelahDiskon, 0, ',', '.'); ?></td></tr>
        <tr><td class="py-2">Admin</td><td class="py-2 text-gray-700"><?php echo htmlspecialchars($admin); ?></td></tr>
    </table>

    <form action="konfirmasi_pembayaran.php" method="POST" class="mt-4">
        <label class="block text-gray-600">Masukkan Nomor Telepon</label>
        <input type="text" name="no_telp" class="border p-2 rounded-md w-full mt-1" placeholder="Masukkan nomor telepon" required>
        <?php foreach ($_POST['pilih'] as $id_produk): ?>
            <input type="hidden" name="pilih[]" value="<?php echo htmlspecialchars($id_produk); ?>">
        <?php endforeach; ?>
        <button type="submit" class="bg-[#88AA5B] text-white px-4 py-2 mt-4 rounded-md w-full">Cek Member</button>
    </form>

    <?php if (!empty($no_telp)): ?>
        <div class="mt-4">
            <p class="text-lg font-bold">Status Member: <?php echo $status_member; ?></p>
            <?php if ($status_member == 'Member Aktif'): ?>
                <p class="text-green-500">Diskon: <?php echo $diskon; ?>%</p>
            <?php else: ?>
                <p class="text-red-500">Tidak ada diskon karena bukan member aktif.</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="mt-6">
        <div class="flex gap-4">
            <button onclick="showCash()" type="button" class="bg-gray-200 hover:bg-gray-300 px-4 py-2 rounded-md flex items-center gap-2">
                💵 Cash
            </button>
        </div>
    </div>

    <!-- CASH PAYMENT -->
<form action="proses_pembayaran.php" method="POST" class="mt-4 hidden" id="cash-section">
    <input type="hidden" name="tanggal" value="<?php echo date('Y-m-d'); ?>">
    <input type="hidden" name="produk" value="<?php echo htmlspecialchars($produkStr); ?>">
    <input type="hidden" name="total_harga" id="total-harga" value="<?php echo $totalSetelahDiskon; ?>">
    <input type="hidden" name="admin" value="<?php echo htmlspecialchars($admin); ?>">
    <input type="hidden" name="no_telp" value="<?php echo htmlspecialchars($no_telp); ?>">
    <input type="hidden" name="metode_pembayaran" value="cash">
    <?php foreach ($_POST['pilih'] as $id_produk): ?>
        <input type="hidden" name="produk_terpilih[]" value="<?php echo htmlspecialchars($id_produk); ?>">
    <?php endforeach; ?>
    
    <label class="block text-gray-600 mt-2">Masukkan Nominal Cash</label>
    <div class="flex items-center mt-1">
        <span class="bg-gray-200 px-3 py-2 rounded-l">Rp</span>
        <input type="number" name="nominal" id="cash-input" class="border p-2 rounded-r w-full" required>
    </div>

    <button type="submit" id="submit-button" class="bg-green-800 text-white px-4 py-2 mt-4 rounded-md w-full" disabled>
        Konfirmasi Pembayaran
    </button>
</form>



<div id="toast-poin" class="fixed top-4 left-1/2 transform -translate-x-1/2 bg-green-500 text-white px-4 py-2 rounded shadow-lg hidden z-50 transition duration-500 ease-in-out">
    ✅ Poin member berhasil diperbarui! 50 poin telah ditambahkan!
</div>

<script>
    function showCash() {
        document.getElementById('cash-section').classList.remove('hidden');
        document.getElementById('qris-section').classList.add('hidden');
    }
    function showQRIS() {
        document.getElementById('qris-section').classList.remove('hidden');
        document.getElementById('cash-section').classList.add('hidden');
    }
    <?php if (isset($_SESSION['poin_diperbarui'])): ?>
        const toast = document.getElementById('toast-poin');
        toast.classList.remove('hidden');
        setTimeout(() => {
            toast.classList.add('hidden');
        }, 3000);
        <?php unset($_SESSION['poin_diperbarui']); ?>
    <?php endif; ?>
</script>

<script>
    const cashInput = document.getElementById('cash-input');
    const submitButton = document.getElementById('submit-button');
    const totalHarga = parseFloat(document.getElementById('total-harga').value);

    cashInput.addEventListener('input', function () {
        const enteredCash = parseFloat(this.value);
        if (!isNaN(enteredCash) && enteredCash >= totalHarga) {
            submitButton.disabled = false;
            submitButton.classList.remove('opacity-50', 'cursor-not-allowed');
        } else {
            submitButton.disabled = true;
            submitButton.classList.add('opacity-50', 'cursor-not-allowed');
        }
    });
</script>

</body>
</html>
