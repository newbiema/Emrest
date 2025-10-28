<?php
require_once __DIR__.'/../services/Auth.php';
require_once __DIR__.'/../services/Database.php';
require_once __DIR__.'/../services/Helper.php';

$auth = new Auth(); $auth->checkLogin();
$auth->authorize(['admin','loket','perawat','dokter','apotek']);

$db = (new Database())->connect();
$pageTitle = "Data Kunjungan";

$kw   = trim($_GET['search'] ?? '');
$stat = trim($_GET['status'] ?? '');
$poli = trim($_GET['poli'] ?? '');

$where = [];
if ($kw!=='')   $where[] = "(k.kode_kunjungan LIKE '%".mysqli_real_escape_string($db,$kw)."%' OR p.pasien_nama LIKE '%".mysqli_real_escape_string($db,$kw)."%')";
if ($stat!=='') $where[] = "k.status='".mysqli_real_escape_string($db,$stat)."'";
if ($poli!=='') $where[] = "k.poli='".mysqli_real_escape_string($db,$poli)."'";
$W = $where ? ('WHERE '.implode(' AND ', $where)) : '';

$sql = "SELECT k.*, p.pasien_nama, p.pasien_id
        FROM kunjungan k
        JOIN pasien p ON p.inc = k.pasien_id
        $W
        ORDER BY k.created_at DESC";
$rs = mysqli_query($db,$sql);

ob_start();
?>
<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-6">
  <div>
    <h1 class="text-3xl font-bold text-gray-800 flex items-center gap-2">
      <i class="fa-solid fa-list-ol text-blue-600"></i> Data Kunjungan
    </h1>
    <p class="text-sm text-gray-500">Antrian & status layanan</p>
  </div>
  <a href="<?= Helper::baseUrl('visits/daftar.php') ?>" data-role="visit.create"
     class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
    <i class="fa-solid fa-plus"></i> Kunjungan Baru
  </a>
</div>

<form class="mb-4 grid grid-cols-1 md:grid-cols-4 gap-2">
  <input type="text" name="search" value="<?= htmlspecialchars($kw) ?>" placeholder="Cari kode/nama pasien" class="border p-2 rounded">
  <select name="status" class="border p-2 rounded">
    <option value="">Semua Status</option>
    <?php foreach(['checkin','triase','dokter_done','obat_siap','obat_ambil','lunas','batal'] as $s): ?>
      <option value="<?= $s ?>" <?= $s===$stat?'selected':'' ?>><?= ucfirst(str_replace('_',' ',$s)) ?></option>
    <?php endforeach; ?>
  </select>
  <select name="poli" class="border p-2 rounded">
    <option value="">Semua Poli</option>
    <?php foreach(['Umum','Anak','Gigi','Kandungan'] as $pl): ?>
      <option <?= $poli===$pl?'selected':'' ?>><?= $pl ?></option>
    <?php endforeach; ?>
  </select>
  <button class="bg-blue-600 text-white rounded px-4">Filter</button>
</form>

<div class="bg-white rounded-xl shadow overflow-hidden">
  <table class="min-w-full table-auto">
    <thead class="bg-blue-600 text-white">
      <tr>
        <th class="px-4 py-3 text-left">Kode</th>
        <th class="px-4 py-3 text-left">Tanggal</th>
        <th class="px-4 py-3 text-left">Pasien</th>
        <th class="px-4 py-3 text-left">Poli</th>
        <th class="px-4 py-3 text-left">Antrian</th>
        <th class="px-4 py-3 text-left">Status</th>
        <th class="px-4 py-3 text-center">Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php if (mysqli_num_rows($rs)===0): ?>
        <tr><td colspan="7" class="text-center py-6 text-gray-500">Tidak ada data</td></tr>
      <?php else: while($r=mysqli_fetch_assoc($rs)) { ?>
        <tr class="border-b hover:bg-gray-50">
          <td class="px-4 py-3 font-semibold"><?= htmlspecialchars($r['kode_kunjungan']) ?></td>
          <td class="px-4 py-3"><?= htmlspecialchars(date('d M Y H:i', strtotime($r['created_at']))) ?></td>
          <td class="px-4 py-3"><?= htmlspecialchars($r['pasien_nama']).' — '.htmlspecialchars($r['pasien_id']) ?></td>
          <td class="px-4 py-3"><?= htmlspecialchars($r['poli']) ?></td>
          <td class="px-4 py-3"><?= (int)$r['no_antrian'] ?></td>
          <td class="px-4 py-3"><?= Helper::statusBadge($r['status']) ?></td>
          <td class="px-4 py-3 text-center">
            <a href="<?= Helper::baseUrl('visits/detail.php?id='.(int)$r['id']) ?>" class="text-blue-600 hover:text-blue-800">
              <i class="fa-solid fa-eye"></i>
            </a>
          </td>
        </tr>
      <?php } endif; ?>
    </tbody>
  </table>
</div>

<?php
$contentFile = tempnam(sys_get_temp_dir(),'content'); file_put_contents($contentFile, ob_get_clean());
include_once __DIR__.'/../components/layout.php';
