<?php
session_start();
include 'connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Query untuk cek apakah email ada di database
    $query = "SELECT * FROM admin WHERE email = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);

        // Jika password di database menggunakan password_hash()
        if (password_verify($password, $user['password'])) {
            $_SESSION['username'] = $user['username'];
            $_SESSION['id'] = $user['id'];
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Email tidak ditemukan!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Login - QuickPay</title>
</head>
<body class="flex items-center justify-center min-h-screen bg-gray-100">

    <!-- Form Login -->
    <div class="w-full max-w-sm bg-white p-6 rounded-lg shadow-md">
        <!-- Navbar dalam form -->
        <div class="flex items-center space-x-3 mb-6">
            <!-- Logo SVG QuickPay -->
            <svg class="w-9 h-9 text-green-700" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="50" cy="50" r="40" stroke="currentColor" stroke-width="10" fill="none"/>
                <path d="M65 65 L85 85" stroke="currentColor" stroke-width="10" stroke-linecap="round"/>
                <path d="M40 50 h20 a10 10 0 0 0 0 -20 h-20 a10 10 0 0 0 0 20 z" fill="currentColor"/>
            </svg>
            <h1 class="text-2xl font-bold text-green-700">QuickPay</h1>
        </div>

        <h2 class="text-2xl font-semibold text-center mb-6">Login</h2>

        <?php if (isset($error)) { echo "<p class='text-red-500 text-center'>$error</p>"; } ?>

        <form method="POST">
            <div class="mb-4">
                <label class="block text-gray-700">Email</label>
                <div class="flex items-center border rounded-md px-3 py-2 bg-gray-100">
                    <span class="text-gray-500">✉️</span>
                    <input type="email" name="email" required class="ml-2 w-full bg-transparent outline-none">
                </div>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700">Password</label>
                <div class="flex items-center border rounded-md px-3 py-2 bg-gray-100">
                    <span class="text-gray-500">🔒</span>
                    <input type="password" name="password" required class="ml-2 w-full bg-transparent outline-none">
                </div>
            </div>
            <button type="submit" class="w-full bg-green-400 text-white py-2 rounded-md hover:bg-green-500">Sign in</button>
        </form>

        <div class="text-center mt-4">
            <a href="forgot.php" class="text-blue-500 text-sm">Forgot your password?</a>
        </div>
    </div>
</body>
</html>
