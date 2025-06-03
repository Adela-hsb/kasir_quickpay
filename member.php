<?php
session_start();
include 'connection.php';

// Hapus jika ada permintaan
if (isset($_POST['delete_id'])) {
    $id = $_POST['delete_id'];
    $deleteQuery = "DELETE FROM member WHERE id_member='$id'";
    if (mysqli_query($conn, $deleteQuery)) {
        echo "success";
    } else {
        echo "error";
    }
    exit;
}

// Update otomatis status jadi Tidak Aktif jika tidak ada transaksi dalam 1 menit
$now = date('Y-m-d H:i:s');
$inactiveQuery = "UPDATE member 
                  SET status = 'Tidak Aktif' 
                  WHERE last_activity IS NOT NULL 
                    AND TIMESTAMPDIFF(SECOND, last_activity, '$now') > 60
                    AND status = 'Aktif'";
mysqli_query($conn, $inactiveQuery);

// Ambil data member
$result = mysqli_query($conn, "SELECT * FROM member");
if (!$result) {
    die("Query error: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <title>Data Member</title>
</head>
<body class="bg-gray-100">
    <div class="flex min-h-screen">
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
                <li><a href="member.php" class="flex items-center p-2 bg-[#A0C878] text-black rounded"><span>👥</span><span class="ml-2">Member</span></a></li>
                <li><a href="kategori.php" class="flex items-center p-2 hover:bg-[#A0C878] text-black rounded"><span>📂</span><span class="ml-2">Kategori</span></a></li>
                <li><a href="produk.php" class="flex items-center p-2 hover:bg-[#A0C878] text-black rounded"><span>📦</span><span class="ml-2">Produk</span></a></li>
                <li><a href="transaksi.php" class="flex items-center p-2 hover:bg-[#A0C878] text-black rounded"><span>💳</span><span class="ml-2">Transaksi</span></a></li>
                <li><a href="keranjang.php" class="flex items-center p-2 hover:bg-[#A0C878] text-black rounded"><span>🛒</span><span class="ml-2">Keranjang</span></a></li>
                <li><a href="laporan.php" class="flex items-center p-2 hover:bg-[#A0C878] text-black rounded"><span>📊</span><span class="ml-2">Laporan</span></a></li>
            </ul>
            <a href="logout.php" class="block bg-[#88AA5B] text-white text-center p-2 mt-6 rounded hover:bg-[#7B964F]"><span>📴</span> Logout</a>
        </div>

        <!-- Konten Utama -->
        <main class="flex-1 p-6">
            <div class="bg-white p-4 rounded shadow-md">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-2xl font-semibold">Data Member</h2>
                    <button onclick="window.location.href='tambah_member.php';" class="bg-[#DDEB9D] text-black px-4 py-2 rounded hover:bg-[#A0C878]">
                        Tambah Member ➕
                    </button>
                </div>

                <!-- Tabel -->
                <table class="w-full border-collapse border border-green-500">
                    <thead>
                        <tr class="bg-[#A0C878] text-left">
                            <th class="border p-2">Id</th>
                            <th class="border p-2">Nama</th>
                            <th class="border p-2">No Telp</th>
                            <th class="border p-2">Point</th>
                            <th class="border p-2">Status</th>
                            <th class="border p-2">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-yellow-100">
                        <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                            <tr id="row_<?php echo $row['id_member']; ?>">
                                <td class="border p-2"><?php echo $row['id_member']; ?></td>
                                <td class="border p-2"><?php echo $row['nama_member']; ?></td>
                                <td class="border p-2"><?php echo $row['no_telp']; ?></td>
                                <td class="border p-2"><?php echo $row['point']; ?></td>
                                <td>
    <?php if ($row['status'] === 'aktif'): ?>
        <span class="px-2 py-1 bg-green-200 text-green-800 rounded-full text-sm font-medium">Aktif</span>
    <?php else: ?>
        <span class="px-2 py-1 bg-red-200 text-red-800 rounded-full text-sm font-medium">Tidak Aktif</span>
    <?php endif; ?>
</td>

                                <td class="border p-2">
                                    <a href="edit_member.php?id=<?php echo $row['id_member']; ?>" class="text-blue-500">✏️ Edit</a>
                                    <button class="delete-btn text-red-500 ml-3" data-id="<?php echo $row['id_member']; ?>">🗑 Hapus</button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script>
        $(document).ready(function () {
            // Hapus member
            $(".delete-btn").click(function () {
                var memberId = $(this).data("id");
                if (confirm("Yakin ingin menghapus member ini?")) {
                    $.ajax({
                        type: "POST",
                        url: "member.php",
                        data: { delete_id: memberId },
                        success: function (response) {
                            if (response === "success") {
                                $("#row_" + memberId).fadeOut();
                            } else {
                                alert("Gagal menghapus data.");
                            }
                        }
                    });
                }
            });

            // Ubah status member
            $(".status-btn").click(function () {
                var button = $(this);
                var memberId = button.data("id");
                var currentStatus = button.data("status");
                var newStatus = currentStatus === "Aktif" ? "Tidak Aktif" : "Aktif";

                $.ajax({
                    type: "POST",
                    url: "update_status_member.php",
                    data: { id_member: memberId, status: newStatus },
                    success: function (response) {
                        if (response === "success") {
                            var newClass = newStatus === "Aktif" ? "bg-green-500" : "bg-red-500";
                            button.text(newStatus).removeClass("bg-green-500 bg-red-500").addClass(newClass).data("status", newStatus);
                        } else {
                            alert("Gagal mengubah status.");
                        }
                    }
                });
            });
        });
    </script>
</body>
</html>
