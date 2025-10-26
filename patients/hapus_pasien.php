<?php
require_once __DIR__ . '/../services/Auth.php';
require_once __DIR__ . '/../services/Database.php';
require_once __DIR__ . '/../services/Alert.php';


$auth = new Auth();
$auth->checkLogin();

$db = (new Database())->connect();

$id = $_GET['id'] ?? null;

if (!$id) {
  echo "<script>
    Swal.fire({
      icon:'error',
      title:'Gagal!',
      text:'ID pasien tidak ditemukan.',
    }).then(()=>window.location.href='data_pasien.php');
  </script>";
  exit;
}

$query = "DELETE FROM pasien WHERE inc='$id'";

if (mysqli_query($db, $query)) {
  Alert::toast('success', 'Data pasien berhasil dihapus.', 'data_pasien.php');
} else {
  Alert::toast('error', 'Terjadi kesalahan saat menghapus data.', 'data_pasien.php');
}

