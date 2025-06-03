<?php
include 'connection.php';

$filter = $_GET['filter'] ?? 'harian';
$tanggal = $_GET['tanggal'] ?? date('Y-m-d');

switch ($filter) {
  case 'harian':
    $group = "DATE(tanggal_pembelian)";
    $where = "DATE(tanggal_pembelian) = '$tanggal'";
    break;
  case 'mingguan':
    $group = "DATE(tanggal_pembelian)";
    $where = "YEARWEEK(tanggal_pembelian, 1) = YEARWEEK('$tanggal', 1)";
    break;
  case 'bulanan':
    $group = "DATE(tanggal_pembelian)";
    $where = "YEAR(tanggal_pembelian) = YEAR('$tanggal') AND MONTH(tanggal_pembelian) = MONTH('$tanggal')";
    break;
  case 'tahunan':
    $group = "DATE(tanggal_pembelian)";
    $where = "YEAR(tanggal_pembelian) = YEAR('$tanggal')";
    break;
  default:
    $group = "DATE(tanggal_pembelian)";
    $where = "DATE(tanggal_pembelian) = '$tanggal'";
}

$query = "
  SELECT t.id_transaksi, DATE(t.tanggal_pembelian) AS tanggal, p.nama_produk AS produk,
         t.jumlah, t.total_harga AS total, 
         (t.jumlah * p.modal) AS modal,
         (t.total_harga - (t.jumlah * p.modal)) AS keuntungan
  FROM transaksi t
  JOIN produk p ON t.id_produk = p.id_produk
  WHERE $where
  ORDER BY t.tanggal_pembelian DESC
";

$result = mysqli_query($conn, $query);

$data = [];
$total_modal = 0;
$total_penjualan = 0;
$total_keuntungan = 0;

while ($row = mysqli_fetch_assoc($result)) {
  $data[] = $row;
  $total_modal += $row['modal'];
  $total_penjualan += $row['total'];
  $total_keuntungan += $row['keuntungan'];
}

$grafik = [];
$grafik_query = "
  SELECT DATE(t.tanggal_pembelian) AS label,
         SUM(t.total_harga - (t.jumlah * p.modal)) AS keuntungan
  FROM transaksi t
  JOIN produk p ON t.id_produk = p.id_produk
  WHERE $where
  GROUP BY $group
  ORDER BY label ASC
";

$grafik_result = mysqli_query($conn, $grafik_query);

while ($g = mysqli_fetch_assoc($grafik_result)) {
  $grafik[] = $g;
}

echo json_encode([
  "data" => $data,
  "total_modal" => $total_modal,
  "total_penjualan" => $total_penjualan,
  "total_keuntungan" => $total_keuntungan,
  "grafik" => $grafik
]);
?>
