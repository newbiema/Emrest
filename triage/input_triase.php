<?php
require_once __DIR__.'/../services/Auth.php';
require_once __DIR__.'/../services/Database.php';
require_once __DIR__.'/../services/Helper.php';
require_once __DIR__.'/../services/Alert.php';

$auth = new Auth(); $auth->checkLogin();
$auth->authorize(['admin','perawat']);

$db = (new Database())->connect();
$pageTitle = "Input Triase";

$kunjungan_id = (int)($_GET['kunjungan_id'] ?? 0);
$k = mysqli_fetch_assoc(mysqli_query($db,"SELECT * FROM kunjungan WHERE id=$kunjungan_id"));

if (!$k) { Alert::toast('error','Kunjungan tidak ditemukan.', Helper::baseUrl('visits/data_kunjungan.php')); exit; }

if ($_SERVER['REQUEST_METHOD']==='POST') {
  $suhu = $_POST['suhu']!=='' ? (float)$_POST['suhu'] : null;
  $tinggi = $_POST['tinggi']!=='' ? (int)$_POST['tinggi'] : null;
  $berat = $_POST['berat']!=='' ? (float)$_POST['berat'] : null;
  $nadi = $_POST['nadi']!=='' ? (int)$_POST['nadi'] : null;
  $rr = $_POST['rr']!=='' ? (int)$_POST['rr'] : null;
  $td = trim($_POST['tekanan_darah'] ?? '');
  $catatan = trim($_POST['catatan'] ?? '');
  $perawat = $_SESSION['username'];

  $stmt = $db->prepare("INSERT INTO triase (kunjungan_id, perawat_user, suhu, tinggi, berat, nadi, rr, tekanan_darah, catatan) 
                        VALUES (?,?,?,?,?,?,?,?,?)");
  $stmt->bind_param('isssiiiss', $kunjungan_id, $perawat, $suhu, $tinggi, $berat, $nadi, $rr, $td, $catatan);

  if ($stmt->execute()) {
    mysqli_query($db, "UPDATE kunjungan SET status='triase' WHERE id=$kunjungan_id");
    Alert::toast('success','Triase tersimpan.', Helper::baseUrl('visits/detail.php?id='.$kunjungan_id));
  } else {
    Alert::toast('error','Gagal menyimpan triase.','input_triase.php?kunjungan_id='.$kunjungan_id);
  }
  exit;
}

ob_start();
?>
<h1 class="text-2xl font-bold mb-6 text-gray-800 flex items-center gap-2">
  <i class="fa-solid fa-notes-medical text-blue-600"></i> Triase
</h1>

<form method="POST" class="bg-white p-6 rounded-xl shadow grid grid-cols-1 md:grid-cols-3 gap-4">
  <div><label class="block mb-1">Suhu (°C)</label><input type="number" step="0.1" name="suhu" class="border rounded p-2 w-full"></div>
  <div><label class="block mb-1">Tinggi (cm)</label><input type="number" name="tinggi" class="border rounded p-2 w-full"></div>
  <div><label class="block mb-1">Berat (kg)</label><input type="number" step="0.1" name="berat" class="border rounded p-2 w-full"></div>
  <div><label class="block mb-1">Nadi (bpm)</label><input type="number" name="nadi" class="border rounded p-2 w-full"></div>
  <div><label class="block mb-1">RR</label><input type="number" name="rr" class="border rounded p-2 w-full"></div>
  <div><label class="block mb-1">Tekanan Darah</label><input type="text" name="tekanan_darah" placeholder="120/80" class="border rounded p-2 w-full"></div>
  <div class="md:col-span-3">
    <label class="block mb-1">Catatan</label>
    <textarea name="catatan" class="border rounded p-2 w-full" rows="3"></textarea>
  </div>
  <div class="md:col-span-3 flex justify-end gap-2">
    <a href="<?= Helper::baseUrl('visits/detail.php?id='.$kunjungan_id) ?>" class="bg-gray-300 px-4 py-2 rounded">Batal</a>
    <button class="bg-blue-600 text-white px-4 py-2 rounded">Simpan</button>
  </div>
</form>
<?php
$contentFile = tempnam(sys_get_temp_dir(),'content'); file_put_contents($contentFile, ob_get_clean());
include_once __DIR__.'/../components/layout.php';
