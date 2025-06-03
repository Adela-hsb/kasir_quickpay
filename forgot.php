<?php
session_start();
include 'connection.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);

    // Cek email di database
    $stmt = $conn->prepare("SELECT email FROM admin WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $token = bin2hex(random_bytes(32));
        $expiry = date("Y-m-d H:i:s", strtotime("+1 hour"));

        // Simpan token dan waktu kadaluarsa
        $stmt = $conn->prepare("UPDATE admin SET reset_token = ?, reset_token_expiry = ? WHERE email = ?");
        $stmt->bind_param("sss", $token, $expiry, $email);
        $stmt->execute();

        $reset_link = "http" . (isset($_SERVER['HTTPS']) ? "s" : "") . "://" . $_SERVER['HTTP_HOST'] . "/usk_kasir/reset_password.php?token=" . urlencode($token);

        // Kirim email dengan PHPMailer
        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'ndariadela12@gmail.com';
            $mail->Password = 'mzoj tgtn belf zuun';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('ndariadela12@gmail.com', 'Admin');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'Reset Password';
            $mail->Body = "Klik link berikut untuk mereset password Anda:<br><a href='{$reset_link}'>{$reset_link}</a>";

            $mail->send();
            $_SESSION['success'] = "Link reset password telah dikirim ke email Anda.";
        } catch (Exception $e) {
            $_SESSION['error'] = "Gagal mengirim email. Silakan coba lagi.";
        }
    } else {
        $_SESSION['error'] = "Email tidak ditemukan!";
    }
    header("Location: forgot.php");
    exit();
}
?>

<!-- HTML -->
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Lupa Password</title>
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

        <h2 class="text-2xl font-bold mb-4 text-center">Lupa Password</h2>
        <?php if (isset($_SESSION['success'])): ?>
            <p class="text-green-600 mb-4 text-center"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></p>
        <?php elseif (isset($_SESSION['error'])): ?>
            <p class="text-red-600 mb-4 text-center"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></p>
        <?php endif; ?>
        <form method="POST" class="space-y-4">
            <input type="email" name="email" placeholder="Masukkan email Anda" required class="w-full p-2 border rounded">
            <button type="submit" class="w-full bg-green-400 text-white p-2 rounded hover:bg-green-500">Kirim Link</button>
        </form>
    </div>
</body>
</html>
