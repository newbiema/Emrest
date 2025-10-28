<?php
require_once __DIR__.'/../services/Auth.php';
require_once __DIR__.'/../services/Database.php';
require_once __DIR__.'/../services/Helper.php';
require_once __DIR__.'/../services/Alert.php';

$auth = new Auth(); $auth->checkLogin();
$auth->authorize(['admin','apotek']);

$db = (new Database())->connect();
$pageTitle = "Proses Resep (Apotek)";

$kunjungan_id = (int)($_GET['kunjungan_id'] ?? 0);
$r = mysqli_query($db,"SELECT * FROM resep WHERE kunjungan_id=$kunjungan_id ORDER BY id DESC LIMIT 1");
$resep = $r ? mysqli_fetch_assoc($r) : null;
if(!$resep){ Alert::toast('error','Resep tidak ditemukan.', Helper::baseUrl('visits/detail.php?id='.$kunjungan_id)); exit; }

$items = mysqli_query($db,"SELECT ri.*, o.nama_obat, o.stok FROM resep_item ri JOIN obat o ON o.id=ri.obat_id WHERE ri.resep_id=".$resep['id']);

if (isset($_POST['serahkan'])) {
  // validasi stok >= qty
  $ok = true;
  $items2 = mysqli_query($db,"SELECT ri.*, o.stok, ri.obat_id FROM resep_item ri JOIN obat o ON o.id=ri.obat_id WHERE ri.resep_id=".$resep['id']);
  while($it=mysqli_fetch_assoc($items2)){
    if ((int)$it['stok'] < (int)$it['qty']) { $ok=false; break; }
  }
  if (!$ok) { Alert::toast('warning','Stok obat tidak mencukupi.','proses_apotek.php?kunjungan_id='.$kunjungan_id); exit; }

  // kurangi stok & catat mutasi
  mysqli_begin_transaction($db);
  try {
    $items3 = mysqli_query($db,"SELECT ri.*, ri.obat_id FROM resep_item ri WHERE ri.resep_id=".$resep['id']);
    while($it=mysqli_fetch_assoc($items3)){
      mysqli_query($db,"UPDATE obat SET stok=stok-".(int)$it['qty']." WHERE id=".(int)$it['obat_id']);
      $ref = 'VIS#'.$kunjungan_id;
      $mut = $db->prepare("INSERT INTO obat_mutasi(obat_id, tipe, qty, ref, keterangan) VALUES (?,'out',?,?,'Resep pasien')");
      $mut->bind_param('iis', $it['obat_id'], $it['qty'], $ref);
      $mut->execute();
    }
    mysqli_query($db, "UPDATE resep SET status='diserahkan' WHERE id=".$resep['id']);
    mysqli_query($db, "UPDATE kunjungan SET status='obat_ambil' WHERE id=$kunjungan_id");
    mysqli_commit($db);
    Alert::toast('success','Obat diserahkan. Lanjut ke pembayaran.', Helper::baseUrl('visits/detail.php?id='.$kunjungan_id));
  } catch (\Throwable $e) {
    mysqli_rollback($db);
    Alert::toast('error','Gagal memproses apotek.','proses_apotek.php?kunjungan_id='.$kunjungan_id);
  }
  exit;
}

ob_start();
?>
<h1 class="text-2xl font-bold mb-6 text-gray-800 flex items-center gap-2">
  <i class="fa-solid fa-pills text-blue-600"></i> Proses Resep
</h1>

<div class="bg-white rounded-xl shadow p-6 space-y-4">
  <div class="overflow-x-auto">
    <table class="min-w-full table-auto">
      <thead class="bg-gray-100">
        <tr><th class="px-3 py-2 text-left">Obat</th><th class="px-3 py-2">Stok</th><th class="px-3 py-2">Qty</th></tr>
      </thead>
      <tbody>
        <?php while($it=mysqli_fetch_assoc($items)) { ?>
          <tr class="border-b">
            <td class="px-3 py-2"><?= htmlspecialchars($it['nama_obat']) ?></td>
            <td class="px-3 py-2 text-center"><?= (int)$it['stok'] ?></td>
            <td class="px-3 py-2 text-center"><?= (int)$it['qty'] ?></td>
          </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>

  <form method="POST" class="flex justify-end">
    <button name="serahkan" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded">Serahkan Obat</button>
  </form>
</div>

<?php
$contentFile = tempnam(sys_get_temp_dir(),'content'); file_put_contents($contentFile, ob_get_clean());
include_once __DIR__.'/../components/layout.php';
