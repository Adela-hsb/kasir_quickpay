<?php
include 'connection.php';

// Kalau ada request hapus
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $query = "DELETE FROM kategori WHERE id = $id";

    if (mysqli_query($conn, $query)) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => mysqli_error($conn)]);
    }
    exit; // Penting: Biar PHP berhenti di sini kalau POST
}

// Kalau bukan POST (GET biasa), tampilkan halaman
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manajemen Kategori</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<table border="1" cellpadding="10" cellspacing="0" width="100%">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nama Kategori</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $result = mysqli_query($conn, "SELECT * FROM kategori ORDER BY id DESC");
        while ($row = mysqli_fetch_assoc($result)) {
        ?>
            <tr id="row-<?php echo $row['id']; ?>">
                <td><?php echo $row['id']; ?></td>
                <td><?php echo htmlspecialchars($row['nama_kategori']); ?></td>
                <td>
                    <button onclick="hapusKategori(<?php echo $row['id']; ?>)" style="color:red;">🗑 Hapus</button>
                </td>
            </tr>
        <?php } ?>
    </tbody>
</table>

<script>
function hapusKategori(id) {
    Swal.fire({
        title: 'Yakin mau hapus kategori ini?',
        text: "Data yang dihapus tidak bisa dikembalikan!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, hapus!'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('', { // Kirim ke file ini sendiri
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'id=' + id
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === "success") {
                    Swal.fire('Terhapus!', 'Kategori berhasil dihapus.', 'success');
                    const row = document.getElementById('row-' + id);
                    row.style.transition = "opacity 0.5s";
                    row.style.opacity = 0;
                    setTimeout(() => row.remove(), 500);
                } else {
                    Swal.fire('Gagal!', 'Gagal menghapus: ' + data.message, 'error');
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
