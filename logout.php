<?php
session_start();
session_destroy(); // Hapus semua sesi
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Logout</title>
    <meta http-equiv="refresh" content="3;url=index.html"> <!-- Redirect dalam 3 detik -->
</head>
<body class="flex items-center justify-center min-h-screen bg-gray-200">
    <div class="bg-white p-6 rounded-lg shadow-md text-center">
        <h2 class="text-2xl font-semibold text-red-600">Anda telah logout</h2>
        <p class="mt-2 text-gray-700">Terima kasih, sampai jumpa lagi!</p>
        <p class="mt-2 text-gray-500">Anda akan diarahkan ke halaman login...</p>
        <a href="index.html" class="mt-4 inline-block bg-blue-500 text-white px-4 py-2 rounded-md shadow-md hover:bg-blue-600">Klik di sini jika tidak otomatis</a>
    </div>
</body>
</html>
