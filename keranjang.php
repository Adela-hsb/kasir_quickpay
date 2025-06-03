<?php
session_start();
include 'connection.php';

// Hapus otomatis item dari keranjang jika waktu lebih dari 1 jam
foreach ($_SESSION['keranjang'] ?? [] as $id_produk => $item) {
    if (time() - $item['waktu'] > 3600) {
        unset($_SESSION['keranjang'][$id_produk]);
    }
}

// CEK STOK SAAT INISIALISASI
foreach ($_SESSION['keranjang'] ?? [] as $id_produk => $item) {
    $cek_stok = mysqli_query($conn, "SELECT stok FROM produk WHERE id_produk = '$id_produk' LIMIT 1");
    if ($cek_stok && $row = mysqli_fetch_assoc($cek_stok)) {
        if ($row['stok'] <= 0) {
            unset($_SESSION['keranjang'][$id_produk]);
        }
    } else {
        // Jika produk tidak ditemukan di database, hapus juga
        unset($_SESSION['keranjang'][$id_produk]);
    }
}

$max_jenis_produk = 5;
$total_jenis_produk = isset($_SESSION['keranjang']) ? count($_SESSION['keranjang']) : 0;

// Tambah produk ke keranjang via barcode
if (isset($_GET['barcode'])) {
    if ($total_jenis_produk >= $max_jenis_produk) {
        // Maksimal produk sudah tercapai
        header("Location: keranjang.php?error=Jumlah produk maksimal di keranjang adalah $max_jenis_produk");
        exit;
    }

    $barcode = mysqli_real_escape_string($conn, $_GET['barcode']);
    
    $query = "SELECT p.id_produk, p.nama_produk, k.kategori AS kategori, p.harga_jual, p.gambar 
              FROM produk p 
              LEFT JOIN kategori k ON p.fid_kategori = k.id 
              WHERE p.barcode = '$barcode' AND p.stok > 0 LIMIT 1";
    
    $result = mysqli_query($conn, $query);

    if ($produk = mysqli_fetch_assoc($result)) {
        $id_produk = $produk['id_produk'];
        if (!isset($_SESSION['keranjang'][$id_produk]) && $total_jenis_produk >= $max_jenis_produk) {
            header("Location: keranjang.php?error=Jumlah produk maksimal di keranjang adalah $max_jenis_produk");
            exit;
        }
        if (!isset($_SESSION['keranjang'][$id_produk])) {
            $_SESSION['keranjang'][$id_produk] = [
                'nama_produk' => $produk['nama_produk'],
                'kategori' => $produk['kategori'],
                'harga' => $produk['harga_jual'],
                'gambar' => $produk['gambar'],
                'jumlah' => 1,
                'waktu' => time()
            ];
        } else {
            $_SESSION['keranjang'][$id_produk]['jumlah']++;
        }
        header("Location: keranjang.php?success=1");
        exit;
    } else {
        header("Location: transaksi.php?error=Produk tidak ditemukan atau stok habis");
        exit;
    }
}

// Tambah produk via form POST
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_produk'])) {
    if ($total_jenis_produk >= $max_jenis_produk && !isset($_SESSION['keranjang'][$_POST['id_produk']])) {
        header("Location: keranjang.php?error=Jumlah produk maksimal di keranjang adalah $max_jenis_produk");
        exit;
    }

    $id_produk = $_POST['id_produk'];
    $nama_produk = $_POST['nama_produk'] ?? 'Produk tidak ditemukan';
    $kategori = $_POST['kategori'] ?? 'Kategori tidak tersedia';
    $harga = $_POST['harga'] ?? 0;
    $gambar = $_POST['gambar'] ?? 'default.jpg';

    if (!isset($_SESSION['keranjang'][$id_produk])) {
        $_SESSION['keranjang'][$id_produk] = [
            'nama_produk' => $nama_produk,
            'kategori' => $kategori,
            'harga' => $harga,
            'gambar' => $gambar,
            'jumlah' => 1,
            'waktu' => time()
        ];
    } else {
        $_SESSION['keranjang'][$id_produk]['jumlah']++;
    }
    header("Location: keranjang.php");
    exit;
}

// Tambah jumlah produk
if (isset($_GET['tambah'])) {
    $id_produk = $_GET['tambah'];
    if (isset($_SESSION['keranjang'][$id_produk])) {
        $_SESSION['keranjang'][$id_produk]['jumlah']++;
    }
    header("Location: keranjang.php");
    exit;
}

// Kurangi jumlah produk
if (isset($_GET['kurang'])) {
    $id_produk = $_GET['kurang'];
    if (isset($_SESSION['keranjang'][$id_produk])) {
        $_SESSION['keranjang'][$id_produk]['jumlah']--;
        if ($_SESSION['keranjang'][$id_produk]['jumlah'] <= 0) {
            unset($_SESSION['keranjang'][$id_produk]);
        }
    }
    header("Location: keranjang.php");
    exit;
}

// Hapus produk dari keranjang
if (isset($_GET['hapus'])) {
    $id_produk = $_GET['hapus'];
    unset($_SESSION['keranjang'][$id_produk]);
    header("Location: keranjang.php");
    exit;
}

// Proses checkout produk yang dicentang
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pilih'])) {
    $fid_admin = 1;
    $fid_member = $_SESSION['id_member'] ?? null;
    $tanggal_pembelian = date("Y-m-d");

    foreach ($_POST['pilih'] as $id_produk) {
        // Pastikan produk masih ada di keranjang
        if (!isset($_SESSION['keranjang'][$id_produk])) continue;

        $item = $_SESSION['keranjang'][$id_produk];
        $harga = $item['harga'];
        $jumlah = $item['jumlah'];
        $total_harga = $harga * $jumlah;
        $keuntungan = $total_harga * 0.2;
        $detail = "Dibeli melalui keranjang";

        $stmt = $conn->prepare("INSERT INTO transaksi (tanggal_pembelian, total_harga, id_admin, id_produk, jumlah, total_keuntungan, detail, id_member) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("siiiidsi", $tanggal_pembelian, $total_harga, $fid_admin, $id_produk, $jumlah, $keuntungan, $detail, $fid_member);

        if ($stmt->execute()) {
            // Hapus dari keranjang hanya jika transaksi berhasil
            unset($_SESSION['keranjang'][$id_produk]);
        }
    }

    header("Location: laporan.php?checkout=success");
    exit;
}
?>




<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Keranjang Belanja</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
<div class="flex">
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
        <li><a href="produk.php" class="flex items-center p-2 hover:bg-[#A0C878] text-black hover:text-black rounded"><span>📦</span><span class="ml-2">Produk</span></a></li>
        <li><a href="transaksi.php" class="flex items-center p-2 hover:bg-[#A0C878] text-black hover:text-black rounded"><span>💳</span><span class="ml-2">Transaksi</span></a></li>
        <li><a href="keranjang.php" class="flex items-center p-2 bg-[#A0C878] text-black hover:text-black rounded"><span>🛒</span><span class="ml-2">Keranjang</span></a></li>
        <li><a href="laporan.php" class="flex items-center p-2 hover:bg-[#A0C878] text-black hover:text-black rounded"><span>📊</span><span class="ml-2">Laporan</span></a></li>
    </ul>
    <a href="logout.php" class="block bg-[#88AA5B] text-white text-center p-2 mt-6 rounded hover:bg-[#7B964F]"><span>📴</span> Logout</a>

</div>

    <!-- Konten Keranjang -->
    <div class="container mx-auto px-4 py-8 w-3/4">
        <h2 class="text-2xl font-bold mb-6">Keranjang Belanja</h2>

        <?php if (isset($_GET['error'])): ?>
    <div class="bg-red-200 text-red-800 p-3 rounded mb-4">
        <?= htmlspecialchars($_GET['error']) ?>
    </div>
<?php endif; ?>

<?php if (isset($_GET['success'])): ?>
    <div class="bg-green-200 text-green-800 p-3 rounded mb-4">
        Produk berhasil ditambahkan ke keranjang.
    </div>
<?php endif; ?>


        <?php if (empty($_SESSION['keranjang'])): ?>
            <p class="text-center text-gray-500">Keranjang kosong.</p>
        <?php else: ?>

            <?php
$total_jenis_produk = isset($_SESSION['keranjang']) ? count($_SESSION['keranjang']) : 0;
?>
<div class="text-lg font-semibold mb-4">
    🛒 : <?= $total_jenis_produk ?>
</div>


            
            <form action="konfirmasi_pembayaran.php" method="post">
                <table class="w-full bg-white shadow-md rounded-md">
                    <thead>
                        <tr class="bg-[#A0C878]">
                            <th class="p-2">✔</th>
                            <th class="p-2">Gambar</th>
                            <th class="p-2">Nama Produk</th>
                            <th class="p-2">Kategori</th>
                            <th class="p-2">Harga</th>
                            <th class="p-2">Jumlah</th>
                            <th class="p-2">Waktu Sisa</th>
                            <th class="p-2">Total</th>
                            <th class="p-2">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $totalHarga = 0;
                        foreach ($_SESSION['keranjang'] as $id_produk => $item):
                            $subtotal = isset($item['harga']) && isset($item['jumlah']) ? $item['harga'] * $item['jumlah'] : 0;
                            $totalHarga += $subtotal;
                            $waktu_sisa = max(0, 3600 - (time() - $item['waktu']));
                            $menit = floor($waktu_sisa / 60);
                            $detik = $waktu_sisa % 60;
                        ?>
                        <tr class="border-b text-center">
                            <td class="p-2">
                            <input type="checkbox" name="pilih[]" value="<?php echo $id_produk; ?>" class="w-5 h-5 checkbox-produk" data-harga="<?php echo $subtotal; ?>">
                            </td>
                            <td class="p-2"><img src="uploads/<?php echo htmlspecialchars($item['gambar']); ?>" width="50"></td>
                            <td class="p-2"><?php echo htmlspecialchars($item['nama_produk']); ?></td>
                            <td class="p-2"><?= htmlspecialchars($item['kategori'] ?? '') ?></td>
                            <td class="p-2">Rp. <?php echo number_format($item['harga'], 0, ',', '.'); ?></td>
                            <td class="p-2 flex justify-center items-center">
                                <a href="keranjang.php?kurang=<?php echo $id_produk; ?>" class="bg-red-500 text-white px-2 py-1 rounded">-</a>
                                <span class="px-3"><?php echo $item['jumlah']; ?></span>
                                <a href="keranjang.php?tambah=<?php echo $id_produk; ?>" class="bg-green-500 text-white px-2 py-1 rounded">+</a>
                            </td>
                            <td class="p-2 text-red-600 font-bold" id="waktu-<?php echo $id_produk; ?>" data-waktu="<?php echo $waktu_sisa; ?>">
                                <?php echo sprintf("%02d:%02d", $menit, $detik); ?>
                            </td>
                            <td class="p-2">Rp. <?php echo number_format($subtotal, 0, ',', '.'); ?></td>
                            <td class="p-2">
                                <a href="keranjang.php?hapus=<?php echo $id_produk; ?>" class="text-red-600">Hapus</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="mt-6 flex justify-between">
                <div class="font-bold text-xl">Total: <span id="total-harga">Rp. 0</span></div>
                    <button type="submit" class="bg-[#88AA5B] text-white px-6 py-2 rounded-md">Checkout</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<!-- Script update waktu dan autofokus -->
<script src="html5-qrcode.min.js"></script>
<script>
function onScanSuccess(decodedText) {
    
}




setInterval(function () {
    document.querySelectorAll('[id^="waktu-"]').forEach(function (element) {
        let waktu = parseInt(element.getAttribute('data-waktu'));
        if (waktu > 0) {
            waktu--;
            element.setAttribute('data-waktu', waktu);
            let menit = Math.floor(waktu / 60);
            let detik = waktu % 60;
            element.innerText = `${String(menit).padStart(2, '0')}:${String(detik).padStart(2, '0')}`;
        }
    });
}, 1000);

// Fokus otomatis ke input barcode
window.onload = () => {
    const input = document.getElementById('barcode-input');
    input.focus();

    input.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            document.getElementById('scan-form').submit();
        }
    });

    document.addEventListener('click', () => {
        input.focus();
    });
};
</script>
<script>
document.querySelectorAll('.checkbox-produk').forEach(cb => {
    cb.addEventListener('change', updateTotal);
});

function updateTotal() {
    let total = 0;
    document.querySelectorAll('.checkbox-produk:checked').forEach(cb => {
        let harga = parseInt(cb.getAttribute('data-harga')) || 0;
        total += harga;
    });

    document.getElementById('total-harga').innerText = 'Rp. ' + total.toLocaleString('id-ID');
}
</script>
</body>
</html>
