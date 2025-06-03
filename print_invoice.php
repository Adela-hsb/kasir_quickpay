<?php
session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        function printInvoice() {
            var printContents = document.getElementById('invoice-print').innerHTML;
            var originalContents = document.body.innerHTML;
            document.body.innerHTML = printContents;
            window.print();
            document.body.innerHTML = originalContents;
        }
    </script>
</head>
<body class="bg-gray-100 p-6">
    <div class="max-w-3xl mx-auto bg-white p-6 shadow-md rounded-md">
        <h2 class="text-2xl font-bold text-center mb-4">Kasir Digital</h2>
        <div id="invoice-print">
            <p class="text-center">Jl. Raya Cikarang - Cianjur, Bekasi, Jawa Barat 17530</p>
            <p class="text-center">No. Telp: 6281311813177</p>
            <hr class="my-2">
            <p class="text-center">Kamis, <?php echo date('d M Y H:i:s'); ?> WIB</p>
            <table class="w-full border-collapse border border-gray-300 mt-2">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="border border-gray-300 p-2">Qty</th>
                        <th class="border border-gray-300 p-2">Item</th>
                        <th class="border border-gray-300 p-2">Price</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $total = 0;
                    foreach ($_SESSION['keranjang'] as $item) {
                        $subtotal = $item['harga'] * $item['jumlah'];
                        $total += $subtotal;
                        echo "<tr class='text-center'>
                                <td class='border border-gray-300 p-2'>{$item['jumlah']}</td>
                                <td class='border border-gray-300 p-2'>{$item['nama_produk']}</td>
                                <td class='border border-gray-300 p-2'>Rp. " . number_format($item['harga'], 0, ',', '.') . "</td>
                              </tr>";
                    }
                    ?>
                </tbody>
            </table>
            <p class="text-right font-bold mt-4">Total: Rp. <?php echo number_format($total, 0, ',', '.'); ?></p>
        </div>
        <button onclick="printInvoice()" class="mt-4 w-full bg-blue-500 text-white py-2 rounded">Print</button>
    </div>
</body>
</html>
