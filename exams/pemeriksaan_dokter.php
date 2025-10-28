<?php
require_once __DIR__.'/../services/Auth.php';
require_once __DIR__.'/../services/Database.php';
require_once __DIR__.'/../services/Helper.php';
require_once __DIR__.'/../services/Alert.php';

$auth = new Auth(); $auth->checkLogin();
$auth->authorize(['admin','dokter']);

$db = (new Database())->connect();
$pageTitle = "Pemeriksaan Dokter";

$kunjungan_id = (int)($_GET['kunjungan_id'] ?? 0);
$k = mysqli_fetch_assoc(mysqli_query($db,"SELECT * FROM kunjungan WHERE id=$kunjungan_id"));
if(!$k){ Alert::toast('error','Kunjungan tidak ditemukan.', Helper::baseUrl('visits/data_kunjungan.php')); exit; }

if ($_SERVER['REQUEST_METHOD']==='POST') {
  $dokter_id = (int)($_POST['dokter_id'] ?? 0);
  $anam = trim($_POST['anamnesis'] ?? '');
  $diag = trim($_POST['diagnosis'] ?? '');
  $tind = trim($_POST['tindakan'] ?? '');
  $cat  = trim($_POST['catatan'] ?? '');

  if (!$dokter_id || $diag==='') {
    Alert::toast('warning','Dokter & diagnosis wajib diisi.','pemeriksaan_dokter.php?kunjungan_id='.$kunjungan_id); exit;
  }

  $stmt = $db->prepare("INSERT INTO pemeriksaan (kunjungan_id,dokter_id,anamnesis,diagnosis,tindakan,catatan) VALUES (?,?,?,?,?,?)");
  $stmt->bind_param('iissss', $kunjungan_id, $dokter_id, $anam, $diag, $tind, $cat);
  if ($stmt->execute()) {
    mysqli_query($db, "UPDATE kunjungan SET status='dokter_done' WHERE id=$kunjungan_id");
    Alert::toast('success','Pemeriksaan tersimpan.', Helper::baseUrl('visits/detail.php?id='.$kunjungan_id));
  } else {
    Alert::toast('error','Gagal menyimpan.','pemeriksaan_dokter.php?kunjungan_id='.$kunjungan_id);
  }
  exit;
}

$dokterQ = mysqli_query($db, "SELECT id, dokter_nama, spesialis FROM dokter ORDER BY dokter_nama ASC");
ob_start();
?>
<h1 class="text-2xl font-bold mb-6 text-gray-800 flex items-center gap-2">
  <i class="fa-solid fa-user-doctor text-blue-600"></i> Pemeriksaan Dokter
</h1>

<form method="POST" class="bg-white p-6 rounded-xl shadow grid grid-cols-1 md:grid-cols-2 gap-4">
  <div>
    <label class="block mb-1">Dokter</label>
    <select name="dokter_id" class="border rounded p-2 w-full" required>
      <option value="">— Pilih Dokter —</option>
      <?php while($d=mysqli_fetch_assoc($dokterQ)){ ?>
        <option value="<?= (int)$d['id'] ?>"><?= htmlspecialchars($d['dokter_nama']).' ('.$d['spesialis'].')' ?></option>
      <?php } ?>
    </select>
  </div>
  <div></div>

  <div class="md:col-span-2">
    <label class="block mb-1">Anamnesis</label>
    <textarea name="anamnesis" class="border rounded p-2 w-full" rows="3"></textarea>
  </div>

  <div class="md:col-span-2">
    <label class="block mb-1">Diagnosis *</label>
    <textarea name="diagnosis" class="border rounded p-2 w-full" rows="2" required></textarea>
  </div>

  <div class="md:col-span-2">
    <label class="block mb-1">Tindakan</label>
    <textarea name="tindakan" class="border rounded p-2 w-full" rows="2"></textarea>
  </div>

  <div class="md:col-span-2">
    <label class="block mb-1">Catatan</label>
    <textarea name="catatan" class="border rounded p-2 w-full" rows="2"></textarea>
  </div>

  <div class="md:col-span-2 flex justify-end gap-2">
    <a href="<?= Helper::baseUrl('visits/detail.php?id='.$kunjungan_id) ?>" class="bg-gray-300 px-4 py-2 rounded">Batal</a>
    <button class="bg-blue-600 text-white px-4 py-2 rounded">Simpan</button>
  </div>
</form>
<?php
$contentFile = tempnam(sys_get_temp_dir(),'content'); file_put_contents($contentFile, ob_get_clean());
include_once __DIR__.'/../components/layout.php';
