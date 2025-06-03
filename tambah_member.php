<?php
session_start();
include 'connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = $_POST['nama'];
    $no_telp = $_POST['no_telp'];
    $status = $_POST['status'];

    $cek_query = "SELECT * FROM member WHERE nama_member = '$nama' OR no_telp = '$no_telp'";
    $cek_result = mysqli_query($conn, $cek_query);

    if (mysqli_num_rows($cek_result) > 0) {
        echo "<script>alert('Gagal! Nama atau No Telp sudah terdaftar.'); window.location='member.php';</script>";
    } else {
        $id_query = "SELECT MAX(CAST(id_member AS UNSIGNED)) AS last_id FROM member";
        $id_result = mysqli_query($conn, $id_query);
        $row = mysqli_fetch_assoc($id_result);
        $last_id = $row['last_id'];
        $new_id = $last_id ? $last_id + 1 : 1;

        $query = "INSERT INTO member (id_member, nama_member, no_telp, point, status) 
                  VALUES ('$new_id', '$nama', '$no_telp', 0, '$status')";
        
        if (mysqli_query($conn, $query)) {
            echo "<script>alert('Member berhasil ditambahkan!'); window.location='member.php';</script>";
        } else {
            echo "<script>alert('Gagal menambahkan member!'); window.location='member.php';</script>";
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
    <title>Tambah Member</title>
</head>
<body class="flex items-center justify-center min-h-screen bg-[#A0C878]">
    <div class="bg-white p-8 rounded-lg shadow-md w-[600px]">
        <div class="text-center mb-6">
            <h1 class="text-green-500 text-2xl font-bold">QuickPay</h1>
            <p class="text-sm text-gray-600">Tambah Member Baru</p>
        </div>

        <form method="POST" action="tambah_member.php">
            <div class="mb-4">
                <label class="block text-gray-700 mb-1">Nama Member</label>
                <input type="text" name="nama" class="w-full p-2 bg-[#CBE0A5] rounded-md" required>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 mb-1">No Telp</label>
                <input type="text" name="no_telp" class="w-full p-2 bg-[#CBE0A5] rounded-md" required>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full p-2 bg-[#CBE0A5] rounded-md">
                    <option value="Aktif">Aktif</option>
                    <option value="Tidak Aktif">Tidak Aktif</option>
                </select>
            </div>

            <div class="text-center">
                <button type="submit" class="bg-[#88AA5B] text-white px-6 py-2 rounded-md shadow-md hover:bg-[#7B964F]">
                    Confirm
                </button>
                <a href="member.php" class="bg-gray-300 text-gray-800 px-6 py-2 rounded-md shadow-md hover:bg-gray-400">Kembali</a>
            </div>
        </form>
    </div>
</body>
</html>
