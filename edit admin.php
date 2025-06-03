<?php
session_start();
include 'connection.php';

if (empty($_SESSION['username'])) {
    session_regenerate_id(true);
    header("Location: admin.php");
    exit();
}

$admin_login = $_SESSION['id'];
$id_admin = $_GET['id'] ?? '';
$gambar_tampil = "uploads/default.jpg";
$email = "";
$username = "";
$gambar_lama = "";

if ($id_admin) {
    $id_admin = mysqli_real_escape_string($conn, $id_admin);
    $result = mysqli_query($conn, "SELECT * FROM admin WHERE id = '$id_admin'");
    if ($row = mysqli_fetch_assoc($result)) {
        $email = $row['email'];
        $username = $row['username'];
        $gambar_lama = $row['gambar'];
        if (!empty($row['gambar'])) {
            $gambar_tampil = "uploads/" . $row['gambar'];
        }
    }
}

$is_self_edit = ($admin_login == $id_admin);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = $is_self_edit ? mysqli_real_escape_string($conn, $_POST['email']) : $email;
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;

    $cek_duplikat = mysqli_query($conn, "SELECT * FROM admin WHERE (email = '$email' OR username = '$username') AND id != '$id_admin'");
    
    if (mysqli_num_rows($cek_duplikat) > 0) {
        echo "<script>alert('Email atau Username sudah digunakan oleh admin lain!'); window.location='admin.php';</script>";
        exit();
    }

    $gambar_name = $gambar_lama;
    if ($is_self_edit && !empty($_FILES['gambar']['name'])) {
        $gambar_name = time() . '_' . basename($_FILES['gambar']['name']);
        $gambar_tmp = $_FILES['gambar']['tmp_name'];
        $gambar_path = "uploads/" . $gambar_name;

        if (!empty($gambar_lama) && file_exists("uploads/" . $gambar_lama) && $gambar_lama != "default.jpg") {
            unlink("uploads/" . $gambar_lama);
        }

        if (move_uploaded_file($gambar_tmp, $gambar_path)) {
            $gambar_tampil = $gambar_path;
        }
    }

    $query = "UPDATE admin SET username = '$username'";
    if ($is_self_edit) {
        $query .= ", email = '$email'";
        if ($password) {
            $query .= ", password = '$password'";
        }
        $query .= ", gambar = '$gambar_name'";
    }
    $query .= " WHERE id = '$id_admin'";

    if (mysqli_query($conn, $query)) {
        $_SESSION['gambar'] = $gambar_name;
        header("Location: admin.php");
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Edit Admin</title>
</head>
<body class="flex items-center justify-center min-h-screen bg-[#A0C878]">
    <div class="bg-white p-8 rounded-lg shadow-md w-[700px]">
        <div class="text-center mb-6">
            <div class="flex justify-center items-center gap-2 mb-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 1.343-3 3v4a3 3 0 006 0v-4c0-1.657-1.343-3-3-3z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14" />
                </svg>
                <h1 class="text-2xl font-bold text-green-500">QuickPay</h1>
            </div>
            <p class="text-sm text-gray-600">Edit Akun Admin</p>
        </div>

        <form action="" method="POST" enctype="multipart/form-data">
            <div class="grid grid-cols-2 gap-4 items-center">
                <div class="text-center">
                    <img id="preview" src="<?php echo htmlspecialchars($gambar_tampil); ?>" alt="Admin" class="w-full rounded-lg shadow-md h-[200px] object-cover">
                    <input type="file" name="gambar" class="mt-2 w-full text-sm" accept="image/*" onchange="previewImage(event)" <?php echo !$is_self_edit ? 'disabled' : ''; ?>>
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold">Email</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" class="w-full p-2 mb-2 bg-[#CBE0A5] rounded-md" <?php echo !$is_self_edit ? 'readonly' : ''; ?>>

                    <label class="block text-gray-700 font-semibold">Username</label>
                    <input type="text" name="username" value="<?php echo htmlspecialchars($username); ?>" class="w-full p-2 mb-2 bg-[#CBE0A5] rounded-md" required>

                    <label class="block text-gray-700 font-semibold">Password (kosongkan jika tidak diubah)</label>
                    <input type="password" name="password" class="w-full p-2 mb-2 bg-[#CBE0A5] rounded-md" <?php echo !$is_self_edit ? 'readonly' : ''; ?>>
                </div>
            </div>
            <div class="text-center mt-4 flex justify-center gap-4">
                <button type="submit" class="bg-[#88AA5B] text-white px-6 py-2 rounded-md shadow-md hover:bg-[#7B964F]">Confirm</button>
                <a href="admin.php" class="bg-gray-300 text-gray-800 px-6 py-2 rounded-md shadow-md hover:bg-gray-400">Kembali</a>
            </div>
        </form>
    </div>

    <script>
        function previewImage(event) {
            const reader = new FileReader();
            reader.onload = function () {
                document.getElementById('preview').src = reader.result;
            }
            reader.readAsDataURL(event.target.files[0]);
        }
    </script>
</body>
</html>
