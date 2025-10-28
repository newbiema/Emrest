<?php
require_once __DIR__ . '/../services/Auth.php';
require_once __DIR__ . '/../services/Database.php';
require_once __DIR__ . '/../services/Helper.php';
require_once __DIR__ . '/../services/Alert.php';

$auth = new Auth();
$auth->checkLogin();
$auth->authorize(['user']);

$db = (new Database())->connect();
$pageTitle = "Daftar Periksa";

// Ambil pasien_id dari account login
$username = $_SESSION['username'];
$acc = mysqli_fetch_assoc(mysqli_query($db, "SELECT pasien_id FROM account WHERE username='$username'"));
if (empty($acc['pasien_id'])) {
  header('Location: ' . Helper::baseUrl('portal/link_data.php'));
  exit;
}
$pasien_id = $acc['pasien_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $poli = trim($_POST['poli'] ?? '');
  $keluhan = trim($_POST['keluhan_awal'] ?? '');

  if ($poli === '') {
    Alert::toast('warning', 'Pilih poli tujuan.', 'daftar.php');
    exit;
  }

  // Cek apakah pasien sudah daftar hari ini
  $cek = mysqli_query($db, "SELECT id FROM kunjungan 
    WHERE pasien_id=$pasien_id AND poli='$poli' AND DATE(created_at)=CURDATE() 
    AND status NOT IN ('lunas','batal')");

  if (mysqli_num_rows($cek) > 0) {
    Alert::toast('warning', 'Anda sudah daftar hari ini di poli tersebut.', 'daftar.php');
    exit;
  }

  // Tentukan nomor antrian
  $getAntri = mysqli_fetch_assoc(mysqli_query($db, "SELECT MAX(no_antrian) AS mx FROM kunjungan WHERE poli='$poli' AND DATE(created_at)=CURDATE()"));
  $no = ($getAntri['mx'] ?? 0) + 1;
  $kode = 'VIS-' . date('ymd') . '-' . strtoupper(substr(md5(uniqid()), 0, 5));

  mysqli_query($db, "INSERT INTO kunjungan (kode_kunjungan, pasien_id, poli, no_antrian, keluhan_awal, status) 
                     VALUES ('$kode', $pasien_id, '$poli', $no, '$keluhan', 'checkin')");

  Alert::toast('success', 'Pendaftaran berhasil. No antrian Anda: ' . $no, 'kunjungan.php');
  exit;
}

ob_start();
?>
<h1 class="text-2xl font-bold text-gray-800 mb-4 flex items-center gap-2">
  <i class="fa-solid fa-notes-medical text-blue-600"></i> Daftar Periksa
</h1>

<form method="POST" class="bg-white p-6 rounded-xl shadow-md max-w-lg mx-auto space-y-4">
  <div>
    <label class="block mb-1 text-gray-700 font-medium">Pilih Poli</label>
    <select name="poli" required class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-blue-400">
      <option value="">— Pilih Poli —</option>
      <option>Umum</option>
      <option>Anak</option>
      <option>Gigi</option>
      <option>Kandungan</option>
    </select>
  </div>

  <div>
    <label class="block mb-1 text-gray-700 font-medium">Keluhan Awal</label>
    <textarea name="keluhan_awal" rows="4" placeholder="Tuliskan keluhan Anda..." 
      class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-blue-400"></textarea>
  </div>

  <div class="flex justify-end gap-3 pt-2">
    <a href="<?= Helper::baseUrl('portal/index.php') ?>" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg">Batal</a>
    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium">
      <i class="fa-solid fa-paper-plane mr-1"></i> Kirim
    </button>
  </div>
</form>

<?php
$contentFile = tempnam(sys_get_temp_dir(), 'content');
file_put_contents($contentFile, ob_get_clean());
include __DIR__ . '/../components/layout_user.php';
