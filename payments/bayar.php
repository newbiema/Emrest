<?php
require_once __DIR__.'/../services/Auth.php';
require_once __DIR__.'/../services/Database.php';
require_once __DIR__.'/../services/Alert.php';
require_once __DIR__.'/../services/Helper.php';

$auth = new Auth(); $auth->checkLogin();
$auth->authorize(['admin','loket']);

$db = (new Database())->connect();

$kunjungan_id   = (int)($_POST['kunjungan_id'] ?? 0);
$subtotal_jasa  = (float)($_POST['subtotal_jasa'] ?? 0);
$subtotal_obat  = (float)($_POST['subtotal_obat'] ?? 0);
$total          = (float)($_POST['total'] ?? 0);
$metode         = $_POST['metode'] ?? 'tunai';
$kasir          = $_SESSION['username'] ?? '';

if (!$kunjungan_id || $total < 0) {
  Alert::toast('warning','Data tidak valid.', Helper::baseUrl('visits/data_kunjungan.php'));
  exit;
}

$stmt = $db->prepare("INSERT INTO pembayaran (kunjungan_id, subtotal_jasa, subtotal_obat, total, metode, status, dibayar_pada, kasir_user)
                      VALUES (?,?,?,?,?,'lunas', NOW(), ?)");
$stmt->bind_param('idddss', $kunjungan_id, $subtotal_jasa, $subtotal_obat, $total, $metode, $kasir);

if ($stmt->execute()) {
  mysqli_query($db, "UPDATE kunjungan SET status='lunas' WHERE id=$kunjungan_id");
  Alert::toast('success','Pembayaran lunas.','../visits/detail.php?id='.$kunjungan_id);
} else {
  Alert::toast('error','Gagal memproses pembayaran.','../payments/tagihan.php?kunjungan_id='.$kunjungan_id);
}
exit;
