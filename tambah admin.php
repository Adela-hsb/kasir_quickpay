<?php
session_start();
include 'connection.php';

$foto_tampil = "uploads/default.jpg"; // Gambar default jika tidak diupload

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Cek apakah email sudah terdaftar
    $cek_email = mysqli_query($conn, "SELECT * FROM admin WHERE email='$email'");
    if (mysqli_num_rows($cek_email) > 0) {
        $_SESSION['notif'] = "Email sudah digunakan!";
        header("Location: admin.php");
        exit();
    }

    if (!is_dir('uploads')) {
        mkdir('uploads', 0777, true);
    }

    // Upload Foto
    if (!empty($_FILES['gambar']['name'])) {
        $foto_name = time() . '_' . basename($_FILES['gambar']['name']);
        $foto_tmp = $_FILES['gambar']['tmp_name'];
        $foto_path = "uploads/" . $foto_name;

        if (move_uploaded_file($foto_tmp, $foto_path)) {
            $foto_tampil = $foto_name;
        }
    }

    $query = "INSERT INTO admin (username, email, password, gambar) 
              VALUES ('$username', '$email', '$password', '$foto_tampil')";

    if (mysqli_query($conn, $query)) {
        $_SESSION['notif'] = "Admin berhasil ditambahkan!";
        header("Location: admin.php");
        exit();
    } else {
        $_SESSION['notif'] = "Terjadi kesalahan: " . mysqli_error($conn);
        header("Location: admin.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Tambah Admin</title>
</head>
<body class="flex items-center justify-center min-h-screen bg-[#A0C878]">
    <div class="bg-white p-8 rounded-lg shadow-md w-[600px]">
        <div class="text-center mb-6">
            <h1 class="text-green-500 text-2xl font-bold">QuickPay</h1>
            <p class="text-sm text-gray-600">Tambah Akun Admin</p>
        </div>

        <?php if (isset($_SESSION['notif'])): ?>
            <p class="text-center text-red-600 mb-4"><?php echo htmlspecialchars($_SESSION['notif']); unset($_SESSION['notif']); ?></p>
        <?php endif; ?>

        <form action="" method="POST" enctype="multipart/form-data">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <img id="preview" src="<?php echo $foto_tampil; ?>" alt="Admin" class="w-full rounded-lg shadow-md">
                    <input type="file" name="gambar" class="mt-2 w-full" accept="image/*" onchange="previewImage(event)">
                </div>
                <div>
                    <label class="block text-gray-700">Email</label>
                    <input type="email" name="email" class="w-full p-2 mb-2 bg-[#CBE0A5] rounded-md" required>

                    <label class="block text-gray-700">Username</label>
                    <input type="text" name="username" class="w-full p-2 mb-2 bg-[#CBE0A5] rounded-md" required>

                    <label class="block text-gray-700">Password</label>
                    <input type="password" name="password" class="w-full p-2 mb-2 bg-[#CBE0A5] rounded-md" required>
                </div>
            </div>
            <div class="flex justify-between mt-6">
                <a href="admin.php" class="bg-gray-300 text-gray-700 px-6 py-2 rounded-md hover:bg-gray-400">
                    Kembali
                </a>
                <button type="submit" class="bg-[#88AA5B] text-white px-6 py-2 rounded-md shadow-md hover:bg-[#7B964F]">
                    Confirm
                </button>
            </div>
        </form>
    </div>

    <script>
        function previewImage(event) {
            const reader = new FileReader();
            reader.onload = function() {
                document.getElementById('preview').src = reader.result;
            }
            reader.readAsDataURL(event.target.files[0]);
        }
    </script>
</body>
</html>
