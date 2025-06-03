<?php
session_start();
include 'connection.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ID member tidak ditemukan.");
}

$id = mysqli_real_escape_string($conn, $_GET['id']);

$query = "SELECT * FROM member WHERE id_member = '$id'";
$result = mysqli_query($conn, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    die("Data member tidak ditemukan.");
}

$member = mysqli_fetch_assoc($result);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_member = mysqli_real_escape_string($conn, $_POST['id_member']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $no_telp = mysqli_real_escape_string($conn, $_POST['no_telp']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    $checkQuery = "SELECT * FROM member WHERE (nama_member = '$nama' OR no_telp = '$no_telp') AND id_member != '$id_member'";
    $checkResult = mysqli_query($conn, $checkQuery);

    if (mysqli_num_rows($checkResult) > 0) {
        echo "<script>alert('Nama atau nomor telepon sudah digunakan oleh member lain.');</script>";
    } else {
        $updateQuery = "UPDATE member SET nama_member='$nama', no_telp='$no_telp', status='$status'";

        if ($status === 'Aktif') {
            $now = date('Y-m-d H:i:s');
            $updateQuery .= ", last_activity='$now'";
        }

        $updateQuery .= " WHERE id_member='$id_member'";

        if (mysqli_query($conn, $updateQuery)) {
            echo "<script>alert('Data berhasil diperbarui!'); window.location.href='member.php';</script>";
            exit();
        } else {
            echo "<script>alert('Gagal memperbarui data!');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Edit Member</title>
</head>
<body class="flex items-center justify-center min-h-screen bg-[#A0C878]">
    <div class="bg-white p-8 rounded-lg shadow-md w-[600px]">
        <div class="text-center mb-6">
            <h1 class="text-green-500 text-2xl font-bold">QuickPay</h1>
            <p class="text-sm text-gray-600">Edit Data Member</p>
        </div>

        <form method="POST">
            <input type="hidden" name="id_member" value="<?php echo $member['id_member']; ?>">

            <div class="mb-4">
                <label class="block text-gray-700 mb-1">ID Member</label>
                <input type="text" value="<?php echo $member['id_member']; ?>" class="w-full p-2 bg-gray-200 rounded-md" readonly>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 mb-1">Nama Member</label>
                <input type="text" name="nama" value="<?php echo $member['nama_member']; ?>" class="w-full p-2 bg-[#CBE0A5] rounded-md" required>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 mb-1">No Telp</label>
                <input type="text" name="no_telp" value="<?php echo $member['no_telp']; ?>" class="w-full p-2 bg-[#CBE0A5] rounded-md" required>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full p-2 bg-[#CBE0A5] rounded-md">
                    <option value="Aktif" <?php echo ($member['status'] == 'Aktif') ? 'selected' : ''; ?>>Aktif</option>
                    <option value="Tidak Aktif" <?php echo ($member['status'] == 'Tidak Aktif') ? 'selected' : ''; ?>>Tidak Aktif</option>
                </select>
            </div>

            <div class="flex justify-between mt-6">
                <a href="member.php" class="bg-gray-300 text-gray-700 px-6 py-2 rounded-md hover:bg-gray-400">
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
