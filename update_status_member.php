<?php
include 'connection.php';

if (isset($_POST['id_member']) && isset($_POST['status'])) {
    $id = $_POST['id_member'];
    $status = $_POST['status'];

    $updateQuery = "UPDATE member SET status='$status'";

    if ($status === 'Aktif') {
        $now = date('Y-m-d H:i:s');
        $updateQuery .= ", last_activity='$now'";
    }

    $updateQuery .= " WHERE id_member='$id'";

    if (mysqli_query($conn, $updateQuery)) {
        echo "success";
    } else {
        echo "error";
    }
}
?>
