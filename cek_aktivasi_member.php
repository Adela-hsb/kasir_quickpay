<?php
include 'connection.php';

$now = time();
$limit = 5 * 60; // 5 menit dalam detik

$query = "SELECT id_member, last_activity FROM member WHERE status = 'Aktif'";
$result = mysqli_query($conn, $query);

while ($row = mysqli_fetch_assoc($result)) {
    $lastActivity = strtotime($row['last_activity']);
    if (($now - $lastActivity) > $limit) {
        $id = $row['id_member'];
        $update = "UPDATE member SET status = 'Tidak Aktif' WHERE id_member = '$id'";
        mysqli_query($conn, $update);
    }
}
