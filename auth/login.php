<?php
session_start();
include 'connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    // Gunakan prepared statement untuk keamanan
    $stmt = $conn->prepare("SELECT id, email, password FROM admin WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Pastikan password di database dalam format hash sebelum verifikasi
        if (!empty($row['password']) && password_verify($password, $row['password'])) {
            // Regenerasi session ID untuk keamanan
            session_regenerate_id(true);

            $_SESSION['admin_id'] = $row['id'];
            $_SESSION['admin_email'] = $row['email'];

            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Email tidak ditemukan!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Form</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex items-center justify-center min-h-screen bg-gray-100">
    <div class="w-full max-w-sm bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-2xl font-semibold text-center mb-6">Login</h2>
        
        <?php if (isset($error)) : ?>
            <p class="text-red-500 text-sm text-center mb-4"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        
        <form method="POST">
            <div class="mb-4">
                <label class="block text-gray-700">Email</label>
                <div class="flex items-center border rounded-md px-3 py-2 bg-gray-100">
                    <span class="text-gray-500">✉️</span>
                    <input type="email" name="email" placeholder="Enter Email" class="ml-2 w-full bg-transparent outline-none" required>
                </div>
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700">Password</label>
                <div class="flex items-center border rounded-md px-3 py-2 bg-gray-100">
                    <span class="text-gray-500">🔒</span>
                    <input type="password" name="password" placeholder="Enter Password" class="ml-2 w-full bg-transparent outline-none" required>
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
