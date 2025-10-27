<?php
require_once __DIR__ . '/../services/Database.php';
require_once __DIR__ . '/../services/Helper.php';
require_once __DIR__ . '/../services/Alert.php';

$db = (new Database())->connect();
$id = $_GET['id'] ?? null;

if ($id) {
  $query = "DELETE FROM dokter WHERE inc='$id'";
  if (mysqli_query($db, $query)) {
    Alert::toast('success', 'Data dokter berhasil dihapus.', 'data_dokter.php');
  } else {
    Alert::toast('error', 'Gagal menghapus data dokter.', 'data_dokter.php');
  }
} else {
  Alert::toast('warning', 'ID dokter tidak ditemukan.', 'data_dokter.php');
}
