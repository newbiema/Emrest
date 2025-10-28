<?php
require_once __DIR__ . '/../services/Auth.php';
require_once __DIR__ . '/../services/Database.php';
require_once __DIR__ . '/../services/Alert.php';

$auth = new Auth();
$auth->checkLogin();
$auth->authorize(['admin']);
$db = (new Database())->connect();

$id = $_GET['id'] ?? null;
if (!$id) {
  Alert::toast('error', 'ID obat tidak ditemukan.', 'data_obat.php');
  exit;
}

if (mysqli_query($db, "DELETE FROM obat WHERE id='$id'")) {
  Alert::toast('success', 'Data obat berhasil dihapus.', 'data_obat.php');
} else {
  Alert::toast('error', 'Terjadi kesalahan saat menghapus data.', 'data_obat.php');
}
