<?php
require_once __DIR__ . '/../services/Auth.php';
require_once __DIR__ . '/../services/Database.php';
require_once __DIR__ . '/../services/Helper.php';

$auth = new Auth(); $auth->checkLogin(); $auth->authorize(['user']);
$db = (new Database())->connect();

$accUser = $_SESSION['username'];
// ambil pasien_id dari account
$acc = mysqli_fetch_assoc(mysqli_query($db,"SELECT pasien_id FROM account WHERE username='".mysqli_real_escape_string($db,$accUser)."'"));
if (empty($acc['pasien_id'])) {
  header('Location: '.Helper::baseUrl('portal/link_data.php')); exit;
}
$pasien_id = (int)$acc['pasien_id'];

$pageTitle = "Beranda";
$kunj = mysqli_query($db,"SELECT * FROM kunjungan WHERE pasien_id=$pasien_id AND status IN ('checkin','triase','dokter_done','obat_siap','obat_ambil') ORDER BY id DESC LIMIT 1");
$aktif = $kunj ? mysqli_fetch_assoc($kunj) : null;

ob_start(); ?>
<h1 class="text-2xl font-bold text-gray-800 mb-4">Halo, <?= htmlspecialchars($_SESSION['nama'] ?? 'Pasien') ?></h1>

<?php if ($aktif): ?>
  <div class="bg-white rounded-xl shadow p-5 mb-6">
    <div class="flex items-center justify-between">
      <div>
        <div class="text-sm text-gray-500">Kunjungan Aktif</div>
        <div class="text-lg font-semibold"><?= htmlspecialchars($aktif['kode_kunjungan']) ?> — Poli <?= htmlspecialchars($aktif['poli']) ?></div>
        <div class="text-sm mt-1">No Antrian: <span class="font-semibold"><?= (int)$aktif['no_antrian'] ?></span></div>
      </div>
      <div><?= Helper::statusBadge($aktif['status']) ?></div>
    </div>
    <div class="mt-4 flex gap-2">
      <a href="<?= Helper::baseUrl('portal/kunjungan.php?detail='.$aktif['id']) ?>" class="px-4 py-2 rounded bg-blue-600 text-white">Lihat Detail</a>
    </div>
  </div>
<?php endif; ?>

<!-- di dalam konten portal/index.php kamu -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
  <a href="<?= Helper::baseUrl('portal/daftar.php') ?>" class="bg-white rounded-xl p-4 sm:p-5 shadow hover:shadow-md transition">
    <i class="fa-solid fa-notes-medical text-blue-600 text-2xl"></i>
    <div class="mt-2 font-semibold">Daftar Periksa</div>
    <div class="text-sm text-gray-500">Buat kunjungan baru</div>
  </a>
  <a href="<?= Helper::baseUrl('portal/kunjungan.php') ?>" class="bg-white rounded-xl p-4 sm:p-5 shadow hover:shadow-md transition">
    <i class="fa-solid fa-list-ol text-blue-600 text-2xl"></i>
    <div class="mt-2 font-semibold">Kunjungan Saya</div>
    <div class="text-sm text-gray-500">Riwayat & status</div>
  </a>
  <a href="<?= Helper::baseUrl('portal/resep.php') ?>" class="bg-white rounded-xl p-4 sm:p-5 shadow hover:shadow-md transition">
    <i class="fa-solid fa-capsules text-blue-600 text-2xl"></i>
    <div class="mt-2 font-semibold">Resep</div>
    <div class="text-sm text-gray-500">Obat & aturan pakai</div>
  </a>
  <a href="<?= Helper::baseUrl('portal/tagihan.php') ?>" class="bg-white rounded-xl p-4 sm:p-5 shadow hover:shadow-md transition">
    <i class="fa-solid fa-receipt text-blue-600 text-2xl"></i>
    <div class="mt-2 font-semibold">Tagihan</div>
    <div class="text-sm text-gray-500">Status pembayaran</div>
  </a>
  <a href="<?= Helper::baseUrl('portal/profil.php') ?>" class="bg-white rounded-xl p-4 sm:p-5 shadow hover:shadow-md transition">
    <i class="fa-solid fa-user text-blue-600 text-2xl"></i>
    <div class="mt-2 font-semibold">Profil</div>
    <div class="text-sm text-gray-500">Data diri & kontak</div>
  </a>
</div>

<?php
$contentFile = tempnam(sys_get_temp_dir(),'content'); file_put_contents($contentFile, ob_get_clean());
include __DIR__.'/../components/layout_user.php';
