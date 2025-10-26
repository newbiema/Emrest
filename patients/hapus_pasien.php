<?php
require_once __DIR__ . '/../services/Auth.php';
require_once __DIR__ . '/../services/Database.php';
require_once __DIR__ . '/../services/Alert.php';
require_once __DIR__ . '/../services/Helper.php'; 

$auth = new Auth();
$auth->checkLogin();

$db = (new Database())->connect();

// Pastikan ada parameter id
if (!isset($_GET['id'])) {
  Alert::toast('error', 'ID pasien tidak ditemukan.', Helper::baseUrl('patients/data_pasien.php'));
  exit;
}

$id = intval($_GET['id']); // amanin input biar gak bisa injeksi

// Jalankan query hapus
$query = "DELETE FROM pasien WHERE inc = '$id'";

if (mysqli_query($db, $query)) {
  Alert::toast('success', 'Data pasien berhasil dihapus.', Helper::baseUrl('patients/data_pasien.php'));
} else {
  Alert::toast('error', 'Terjadi kesalahan saat menghapus data.', Helper::baseUrl('patients/data_pasien.php'));
}
