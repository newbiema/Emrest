<?php
require_once __DIR__.'/../services/Auth.php';
require_once __DIR__.'/../services/Database.php';
require_once __DIR__.'/../services/Helper.php';

$auth = new Auth(); $auth->checkLogin();
$auth->authorize(['admin','loket','perawat','dokter','apotek']);

$db = (new Database())->connect();
$pageTitle = "Detail Kunjungan";

$id = (int)($_GET['id'] ?? 0);
$sql = "SELECT k.*, p.pasien_nama, p.pasien_id
        FROM kunjungan k JOIN pasien p ON p.inc=k.pasien_id WHERE k.id=$id";
$r = mysqli_query($db, $sql);
$k = $r ? mysqli_fetch_assoc($r) : null;

ob_start();
if(!$k){ echo '<div class="p-6 bg-white rounded-xl shadow">Data tidak ditemukan.</div>'; }
else { ?>
<div class="bg-white rounded-xl shadow p-6 space-y-4">
  <div class="flex items-center justify-between">
    <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
      <i class="fa-solid fa-file-medical text-blue-600"></i> Detail Kunjungan
    </h1>
    <div><?= Helper::statusBadge($k['status']) ?></div>
  </div>

  <div class="grid md:grid-cols-2 gap-4">
    <div><div class="text-sm text-gray-500">Kode</div><div class="font-semibold"><?= htmlspecialchars($k['kode_kunjungan']) ?></div></div>
    <div><div class="text-sm text-gray-500">Tanggal</div><div class="font-semibold"><?= htmlspecialchars(date('d M Y H:i', strtotime($k['created_at']))) ?></div></div>
    <div><div class="text-sm text-gray-500">Pasien</div><div class="font-semibold"><?= htmlspecialchars($k['pasien_nama']).' — '.htmlspecialchars($k['pasien_id']) ?></div></div>
    <div><div class="text-sm text-gray-500">Poli / No Antrian</div><div class="font-semibold"><?= htmlspecialchars($k['poli']) ?> / <?= (int)$k['no_antrian'] ?></div></div>
  </div>

  <?php if (!empty($k['keluhan_awal'])): ?>
    <div>
      <div class="text-sm text-gray-500">Keluhan Awal</div>
      <div class="font-semibold"><?= nl2br(htmlspecialchars($k['keluhan_awal'])) ?></div>
    </div>
  <?php endif; ?>

  <div class="flex flex-wrap gap-2 border-t pt-4">
    <?php if (in_array($k['status'], ['checkin']) && in_array($_SESSION['level'] ?? '', ['admin','perawat'])): ?>
      <a href="<?= Helper::baseUrl('triage/input_triase.php?kunjungan_id='.$k['id']) ?>" 
         class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded" data-role="triage.add">
        <i class="fa-solid fa-notes-medical mr-1"></i> Input Triase
      </a>
    <?php endif; ?>

    <?php if (in_array($k['status'], ['triase']) && in_array($_SESSION['level'] ?? '', ['admin','dokter'])): ?>
      <a href="<?= Helper::baseUrl('exams/pemeriksaan_dokter.php?kunjungan_id='.$k['id']) ?>" 
         class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded" data-role="exam.add">
        <i class="fa-solid fa-user-doctor mr-1"></i> Pemeriksaan Dokter
      </a>
    <?php endif; ?>

    <?php if (in_array($k['status'], ['dokter_done']) && in_array($_SESSION['level'] ?? '', ['admin','apotek','dokter'])): ?>
      <a href="<?= Helper::baseUrl('prescriptions/buat_resep.php?kunjungan_id='.$k['id']) ?>" 
         class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded" data-role="rx.write">
        <i class="fa-solid fa-capsules mr-1"></i> Resep
      </a>
    <?php endif; ?>

    <?php if (in_array($k['status'], ['obat_siap']) && in_array($_SESSION['level'] ?? '', ['admin','apotek'])): ?>
      <a href="<?= Helper::baseUrl('prescriptions/proses_apotek.php?kunjungan_id='.$k['id']) ?>" 
         class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded" data-role="rx.dispense">
        <i class="fa-solid fa-pills mr-1"></i> Serahkan Obat
      </a>
    <?php endif; ?>

    <?php if (in_array($k['status'], ['obat_ambil']) && in_array($_SESSION['level'] ?? '', ['admin','loket'])): ?>
      <a href="<?= Helper::baseUrl('payments/tagihan.php?kunjungan_id='.$k['id']) ?>" 
         class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded" data-role="pay.collect">
        <i class="fa-solid fa-money-bill mr-1"></i> Pembayaran
      </a>
    <?php endif; ?>
  </div>
</div>
<?php }
$contentFile = tempnam(sys_get_temp_dir(),'content'); file_put_contents($contentFile, ob_get_clean());
include_once __DIR__.'/../components/layout.php';
