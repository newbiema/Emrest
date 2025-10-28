<?php
require_once __DIR__ . '/../services/Auth.php';
require_once __DIR__ . '/../services/Database.php';
require_once __DIR__ . '/../services/Helper.php';
require_once __DIR__ . '/../services/Alert.php';

$auth = new Auth();
$auth->checkLogin();
$auth->authorize(['admin','loket']);

$db = (new Database())->connect();
$pageTitle = "Daftar Kunjungan";

// pasien list (dropdown cepat)
$pasienQ = mysqli_query($db, "SELECT inc, pasien_id, pasien_nama FROM pasien ORDER BY pasien_nama ASC");

function nextAntrian(mysqli $db, string $poli): int {
  $poliEsc = mysqli_real_escape_string($db, $poli);
  $sql = "SELECT MAX(no_antrian) AS mx FROM kunjungan 
          WHERE poli='$poliEsc' AND DATE(created_at)=CURDATE()";
  $r = mysqli_query($db, $sql);
  $mx = ($r && $row = mysqli_fetch_assoc($r)) ? (int)$row['mx'] : 0;
  return $mx + 1;
}

if ($_SERVER['REQUEST_METHOD']==='POST') {
  $pasien_id = (int)($_POST['pasien_id'] ?? 0);
  $poli      = trim($_POST['poli'] ?? '');
  $keluhan   = trim($_POST['keluhan_awal'] ?? '');

  if (!$pasien_id || $poli==='') {
    Alert::toast('warning','Pasien & poli wajib diisi.','daftar.php'); exit;
  }
  $kode = 'VIS-'.date('ymd').'-'.strtoupper(substr(md5(uniqid('',true)),0,6));
  $antri = nextAntrian($db, $poli);

  $stmt = $db->prepare("INSERT INTO kunjungan (kode_kunjungan,pasien_id,poli,no_antrian,keluhan_awal,status) 
                        VALUES (?,?,?,?,?,'checkin')");
  $stmt->bind_param('siiss', $kode, $pasien_id, $poli, $antri, $keluhan);
  if ($stmt->execute()) {
    Alert::toast('success','Kunjungan dibuat.','data_kunjungan.php');
  } else {
    Alert::toast('error','Gagal membuat kunjungan.','daftar.php');
  }
  exit;
}

ob_start();
?>
<h1 class="text-2xl font-bold mb-6 text-gray-800 flex items-center gap-2">
  <i class="fa-solid fa-right-to-bracket text-blue-600"></i> Daftar Kunjungan
</h1>

<form method="POST" class="bg-white p-6 rounded-xl shadow-md grid grid-cols-1 md:grid-cols-2 gap-4">
  <div>
    <label class="block mb-1 text-gray-700">Pasien</label>
    <select name="pasien_id" required class="w-full border rounded-lg p-2">
      <option value="">— Pilih Pasien —</option>
      <?php while($p = mysqli_fetch_assoc($pasienQ)) { ?>
        <option value="<?= (int)$p['inc'] ?>">
          <?= htmlspecialchars($p['pasien_nama']).' — '.$p['pasien_id'] ?>
        </option>
      <?php } ?>
    </select>
  </div>

  <div>
    <label class="block mb-1 text-gray-700">Poli</label>
    <select name="poli" required class="w-full border rounded-lg p-2">
      <option value="">— Pilih Poli —</option>
      <option>Umum</option><option>Anak</option><option>Gigi</option><option>Kandungan</option>
    </select>
  </div>

  <div class="md:col-span-2">
    <label class="block mb-1 text-gray-700">Keluhan Awal (opsional)</label>
    <textarea name="keluhan_awal" class="w-full border rounded-lg p-2" rows="3"></textarea>
  </div>

  <div class="md:col-span-2 flex justify-end gap-3">
    <a href="<?= Helper::baseUrl('visits/data_kunjungan.php') ?>" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg">Batal</a>
    <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Buat Kunjungan</button>
  </div>
</form>
<?php
$contentFile = tempnam(sys_get_temp_dir(),'content'); file_put_contents($contentFile, ob_get_clean());
include_once __DIR__.'/../components/layout.php';
