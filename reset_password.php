<?php
session_start();
include 'connection.php';

if (!isset($_GET['token']) || empty($_GET['token'])) {
    $_SESSION['error'] = "Token tidak valid.";
    header("Location: forgot.php");
    exit();
}

$token = $_GET['token'];
$stmt = $conn->prepare("SELECT email, reset_token_expiry FROM admin WHERE reset_token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $_SESSION['error'] = "Token tidak ditemukan atau sudah kadaluarsa.";
    header("Location: forgot.php");
    exit();
}

$data = $result->fetch_assoc();
$email = $data['email'];
$expiry = $data['reset_token_expiry'];

if (strtotime($expiry) < time()) {
    $_SESSION['error'] = "Token sudah kadaluarsa.";
    header("Location: forgot.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (strlen($new_password) < 6) {
        $_SESSION['error'] = "Password minimal 6 karakter.";
    } elseif ($new_password !== $confirm_password) {
        $_SESSION['error'] = "Konfirmasi password tidak cocok.";
    } else {
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE admin SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE email = ?");
        $stmt->bind_param("ss", $hashed, $email);
        $stmt->execute();

        $_SESSION['success'] = "Password berhasil diubah. Silakan login.";
        header("Location: login.php");
        exit();
    }
}
?>

<!-- HTML -->
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex justify-center items-center h-screen bg-gray-100">
    <div class="bg-white p-6 rounded shadow w-96">
        <!-- Logo QuickPay -->
        <div class="flex items-center space-x-3 mb-6 justify-center">
            <!-- Logo SVG QuickPay -->
            <svg class="w-9 h-9 text-green-700" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="50" cy="50" r="40" stroke="currentColor" stroke-width="10" fill="none"/>
                <path d="M65 65 L85 85" stroke="currentColor" stroke-width="10" stroke-linecap="round"/>
                <path d="M40 50 h20 a10 10 0 0 0 0 -20 h-20 a10 10 0 0 0 0 20 z" fill="currentColor"/>
            </svg>
            <h1 class="text-2xl font-bold text-green-700">QuickPay</h1>
        </div>

        <h2 class="text-2xl font-bold mb-4 text-center">Reset Password</h2>
        <?php if (isset($_SESSION['error'])): ?>
            <p class="text-red-600 mb-4 text-center"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></p>
        <?php endif; ?>
        <form method="POST" class="space-y-4">
            <input type="password" name="new_password" placeholder="Password baru" required class="w-full p-2 border rounded">
            <input type="password" name="confirm_password" placeholder="Konfirmasi password" required class="w-full p-2 border rounded">
            <button type="submit" class="w-full bg-green-400 text-white p-2 rounded hover:bg-green-500">Ubah Password</button>
        </form>
    </div>
</body>
</html>
