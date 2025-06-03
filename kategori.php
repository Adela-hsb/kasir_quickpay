<?php
include 'connection.php';
header('Content-Type: text/html; charset=UTF-8');

// Proses hapus kategori via AJAX
if (isset($_GET['action']) && $_GET['action'] == 'delete') {
    header('Content-Type: application/json');

    $id_kategori = intval($_GET['id']);
    $query = "DELETE FROM kategori WHERE id = $id_kategori";

    if (mysqli_query($conn, $query)) {
        echo json_encode(["status" => "success"]);
    } else {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => mysqli_error($conn)]);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kategori Produk</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
        <li><a href="kategori.php" class="flex items-center p-2 bg-[#A0C878] text-black hover:text-black rounded"><span>📂</span><span class="ml-2">Kategori</span></a></li>
        <li><a href="produk.php" class="flex items-center p-2 hover:bg-[#A0C878] text-black hover:text-black rounded"><span>📦</span><span class="ml-2">Produk</span></a></li>
        <li><a href="transaksi.php" class="flex items-center p-2 hover:bg-[#A0C878] text-black hover:text-black rounded"><span>💳</span><span class="ml-2">Transaksi</span></a></li>
        <li><a href="keranjang.php" class="flex items-center p-2 hover:bg-[#A0C878] text-black hover:text-black rounded"><span>🛒</span><span class="ml-2">Keranjang</span></a></li>
        <li><a href="laporan.php" class="flex items-center p-2 hover:bg-[#A0C878] text-black hover:text-black rounded"><span>📊</span><span class="ml-2">Laporan</span></a></li>
    </ul>
    <a href="logout.php" class="block bg-[#88AA5B] text-white text-center p-2 mt-6 rounded hover:bg-[#7B964F]"><span>📴</span> Logout</a>

</div>

    <!-- Konten -->
    <div class="w-4/5 p-10">
        <h2 class="text-3xl font-bold mb-5 text-gray-700">Kategori Produk</h2>
        <a href="tambah_kategori.php" class="bg-[#DDEB9D] text-black px-5 py-2 rounded shadow-md hover:bg-[#A0C878] transition mb-5 inline-block">+ Tambah Kategori</a>

        <div class="mt-5 overflow-x-auto">
            <table class="w-full bg-white shadow-lg rounded-lg overflow-hidden">
                <thead>
                    <tr class="bg-gradient-to-r from-[#A0C878] to-[#A0C878] text-black">
                        <th class="p-4 text-left">ID Kategori</th>
                        <th class="p-4 text-left">Nama Kategori</th>
                        <th class="p-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $result = mysqli_query($conn, "SELECT * FROM kategori ORDER BY id DESC");
                    while ($row = mysqli_fetch_assoc($result)) { ?>
                        <tr class="border-t hover:bg-gray-100 transition" id="row-<?php echo $row['id']; ?>">
                            <td class="p-4"><?php echo $row['id']; ?></td>
                            <td class="p-4"><?php echo htmlspecialchars($row['kategori']); ?></td>
                            <td class="p-4 text-center">
                                <a href="edit_kategori.php?id=<?php echo $row['id']; ?>" class="text-blue-500 hover:text-blue-700">✏ Edit</a>
                                <button onclick="hapusKategori(<?php echo $row['id']; ?>)" class="text-red-500 hover:text-red-700 ml-3">🗑 Hapus</button>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function hapusKategori(id) {
    Swal.fire({
        title: 'Yakin mau hapus?',
        text: "Data yang dihapus tidak bisa dikembalikan!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('kategori.php?action=delete&id=' + id)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Server error');
                }
                return response.json();
            })
            .then(data => {
                if (data.status === "success") {
                    Swal.fire('Terhapus!', 'Kategori berhasil dihapus.', 'success');
                    const row = document.getElementById('row-' + id);
                    row.style.transition = 'opacity 0.5s';
                    row.style.opacity = 0;
                    setTimeout(() => row.remove(), 500);
                } else {
                    Swal.fire('Gagal!', 'Gagal menghapus kategori.', 'error');
                }
            })
            .catch(error => {
                Swal.fire('Error!', 'Terjadi kesalahan saat menghubungi server.', 'error');
                console.error('Fetch error:', error);
            });
        }
    });
}
</script>

</body>
</html>
