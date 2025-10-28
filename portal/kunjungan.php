<?php
require_once __DIR__ . '/../services/Auth.php';
require_once __DIR__ . '/../services/Database.php';
require_once __DIR__ . '/../services/Helper.php';

$auth = new Auth();
$auth->checkLogin();
$auth->authorize(['user']);

$db = (new Database())->connect();
$pageTitle = "Kunjungan Saya";

$username = $_SESSION['username'];
$acc = mysqli_fetch_assoc(mysqli_query($db, "SELECT pasien_id FROM account WHERE username='$username'"));
if (empty($acc['pasien_id'])) {
  header('Location: ' . Helper::baseUrl('portal/link_data.php'));
  exit;
}
$pasien_id = $acc['pasien_id'];

// ambil semua kunjungan pasien
$kunj = mysqli_query($db, "SELECT * FROM kunjungan WHERE pasien_id=$pasien_id ORDER BY created_at DESC");

ob_start();
?>
<h1 class="text-2xl font-bold text-gray-800 mb-4 flex items-center gap-2">
  <i class="fa-solid fa-list-ol text-blue-600"></i> Kunjungan Saya
</h1>

<?php if (mysqli_num_rows($kunj) === 0): ?>
  <div class="bg-white rounded-xl shadow-md p-6 text-center text-gray-500">
    <i class="fa-solid fa-circle-info text-blue-500 text-3xl mb-2"></i>
    <p>Belum ada kunjungan tercatat.</p>
    <a href="daftar.php" class="mt-3 inline-block bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
      <i class="fa-solid fa-notes-medical mr-1"></i> Daftar Sekarang
    </a>
  </div>
<?php else: ?>
  <div class="space-y-4">
    <?php while ($k = mysqli_fetch_assoc($kunj)) { ?>
      <div class="bg-white shadow-md rounded-xl p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
          <div class="font-semibold text-gray-800"><?= htmlspecialchars($k['kode_kunjungan']); ?></div>
          <div class="text-sm text-gray-500"><?= htmlspecialchars($k['poli']); ?> — <?= date('d M Y H:i', strtotime($k['created_at'])); ?></div>
          <div class="text-sm mt-1">Keluhan: <span class="text-gray-700"><?= htmlspecialchars($k['keluhan_awal'] ?: '-'); ?></span></div>
        </div>
        <div class="flex items-center justify-between sm:justify-end gap-3">
          <span><?= Helper::statusBadge($k['status']); ?></span>
          <a href="?detail=<?= $k['id'] ?>" class="text-blue-600 hover:text-blue-800 text-sm flex items-center gap-1">
            <i class="fa-solid fa-eye"></i> Detail
          </a>
        </div>
      </div>
    <?php } ?>
  </div>
<?php endif; ?>

<?php
// detail kunjungan (via ?detail=id)
if (isset($_GET['detail'])) {
  $id = (int)$_GET['detail'];
  $detail = mysqli_fetch_assoc(mysqli_query($db, "SELECT * FROM kunjungan WHERE id=$id AND pasien_id=$pasien_id"));
  if ($detail) {
?>
  <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-40">
    <div class="bg-white rounded-xl shadow-lg max-w-md w-full mx-3 p-6 relative">
      <button onclick="window.location='kunjungan.php'" class="absolute top-3 right-3 text-gray-500 hover:text-red-600">
        <i class="fa-solid fa-xmark text-lg"></i>
      </button>
      <h2 class="text-xl font-bold text-gray-800 mb-4">Detail Kunjungan</h2>
      <p><strong>Kode:</strong> <?= htmlspecialchars($detail['kode_kunjungan']); ?></p>
      <p><strong>Poli:</strong> <?= htmlspecialchars($detail['poli']); ?></p>
      <p><strong>No Antrian:</strong> <?= htmlspecialchars($detail['no_antrian']); ?></p>
      <p><strong>Tanggal:</strong> <?= htmlspecialchars(date('d M Y H:i', strtotime($detail['created_at']))); ?></p>
      <p><strong>Keluhan:</strong> <?= htmlspecialchars($detail['keluhan_awal'] ?: '-'); ?></p>
      <p><strong>Status:</strong> <?= Helper::statusBadge($detail['status']); ?></p>

      <div class="mt-4 flex justify-end">
        <button onclick="window.location='kunjungan.php'" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
          Tutup
        </button>
      </div>
    </div>
  </div>
<?php
  }
}

$contentFile = tempnam(sys_get_temp_dir(), 'content');
file_put_contents($contentFile, ob_get_clean());
include __DIR__ . '/../components/layout_user.php';
