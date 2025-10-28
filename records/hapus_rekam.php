<?php
require_once __DIR__ . '/../services/Auth.php';
require_once __DIR__ . '/../services/Database.php';
require_once __DIR__ . '/../services/Helper.php';
require_once __DIR__ . '/../services/Alert.php';

$auth = new Auth();
$auth->checkLogin();
$auth->authorize(['admin']);

$db = (new Database())->connect();

$id = $_GET['id'] ?? null;

if (!$id) {
  Alert::toast('warning', 'ID rekam medis tidak ditemukan.', 'data_rekam.php');
  exit;
}

// Cek apakah data ada
$check = mysqli_query($db, "SELECT * FROM rekam_medis WHERE id='$id'");
if (mysqli_num_rows($check) == 0) {
  Alert::toast('error', 'Data tidak ditemukan.', 'data_rekam.php');
  exit;
}

// Hapus data
$query = "DELETE FROM rekam_medis WHERE id='$id'";
if (mysqli_query($db, $query)) {
  Alert::toast('success', 'Rekam medis berhasil dihapus.', 'data_rekam.php');
} else {
  Alert::toast('error', 'Gagal menghapus rekam medis.', 'data_rekam.php');
}
?>
