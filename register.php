<?php
include 'connection.php'; // Menghubungkan ke database

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    // Cek apakah password dan konfirmasi password sama
    if ($password !== $confirmPassword) {
        $error = "Password dan konfirmasi password tidak cocok!";
    } else {
        // Hash password sebelum disimpan
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Simpan ke database
        $query = "INSERT INTO admin (email, username, password, gambar) VALUES ('$email', '$username', '$hashedPassword', 'default.png')";
        if ($conn->query($query) === TRUE) {
            // Redirect ke login setelah registrasi berhasil
            header("Location: login.php");
            exit();
        } else {
            $error = "Gagal mendaftar: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Sign Up</title>
</head>
<body class="flex items-center justify-center min-h-screen bg-gray-100">
    <div class="bg-white p-8 rounded-lg shadow-md w-96">
        <h2 class="text-2xl font-bold text-center mb-6">Sign Up</h2>

        <?php if (!empty($error)) : ?>
            <p class="text-red-500 text-center"><?= $error; ?></p>
        <?php endif; ?>

        <form action="register.php" method="POST">
            <div class="mb-4">
                <label class="block font-medium mb-1">Username</label>
                <div class="flex items-center bg-gray-100 p-2 rounded-md">
                    <span class="text-gray-500 px-2">👤</span>
                    <input type="text" name="username" required placeholder="Enter Username" class="w-full bg-transparent focus:outline-none" />
                </div>
            </div>
            <div class="mb-4">
                <label class="block font-medium mb-1">Email</label>
                <div class="flex items-center bg-gray-100 p-2 rounded-md">
                    <span class="text-gray-500 px-2">✉️</span>
                    <input type="email" name="email" required placeholder="Enter Email" class="w-full bg-transparent focus:outline-none" />
                </div>
            </div>
            <div class="mb-4">
                <label class="block font-medium mb-1">Password</label>
                <div class="flex items-center bg-gray-100 p-2 rounded-md">
                    <span class="text-gray-500 px-2">🔒</span>
                    <input type="password" name="password" required placeholder="Enter Password" class="w-full bg-transparent focus:outline-none" />
                </div>
            </div>
            <div class="mb-4">
                <label class="block font-medium mb-1">Confirm Password</label>
                <div class="flex items-center bg-gray-100 p-2 rounded-md">
                    <span class="text-gray-500 px-2">🔒</span>
                    <input type="password" name="confirm_password" required placeholder="Enter Password" class="w-full bg-transparent focus:outline-none" />
                </div>
            </div>
            <button type="submit" class="w-full bg-green-400 text-white py-2 rounded-md mt-4">Sign Up</button>
            <div class="text-center mt-4 text-sm">
                Already have an account? <a href="login.php" class="text-green-600 font-semibold">Login Here!</a>
            </div>
        </form>
    </div>
</body>
</html>
