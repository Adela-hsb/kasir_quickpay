<?php
include 'connection.php';

$no_telp = isset($_GET['no_telp']) ? sanitize($_GET['no_telp']) : '';

$response = ['status' => 'non_member'];

if ($no_telp) {
    $query = "SELECT point FROM member WHERE no_telp = '$no_telp' AND status = 'aktif'";
    $result = mysqli_query($conn, $query);

    if ($row = mysqli_fetch_assoc($result)) {
        $poin = $row['point'];
        $diskon = 0;
        if ($poin >= 20) $diskon = 20;
        elseif ($poin >= 10) $diskon = 10;
        elseif ($poin >= 5) $diskon = 5;

        session_start();
        $total = 0;
        foreach ($_SESSION['keranjang'] as $item) {
            $total += $item['harga'] * $item['jumlah'];
        }

        $potongan = ($diskon / 100) * $total;
        $total_setelah = $total - $potongan;

        $response = [
            'status' => 'member',
            'poin' => $poin,
            'diskon' => $diskon,
            'potongan' => round($potongan),
            'total_setelah_diskon' => round($total_setelah)
        ];
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?>
