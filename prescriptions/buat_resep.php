<?php
require_once __DIR__.'/../services/Auth.php';
require_once __DIR__.'/../services/Database.php';
require_once __DIR__.'/../services/Helper.php';
require_once __DIR__.'/../services/Alert.php';

$auth = new Auth(); $auth->checkLogin();
$auth->authorize(['admin','dokter']);

$db = (new Database())->connect();
$pageTitle = "Resep";

$kunjungan_id = (int)($_GET['kunjungan_id'] ?? 0);
$k = mysqli_fetch_assoc(mysqli_query($db,"SELECT * FROM kunjungan WHERE id=$kunjungan_id"));
if(!$k){ Alert::toast('error','Kunjungan tidak ditemukan.', Helper::baseUrl('visits/data_kunjungan.php')); exit; }

// pastikan ada header resep (draft) 1 per visit
$r = mysqli_query($db,"SELECT * FROM resep WHERE kunjungan_id=$kunjungan_id ORDER BY id DESC LIMIT 1");
$resep = $r ? mysqli_fetch_assoc($r) : null;
if(!$resep){
  // ambil dokter terakhir dari pemeriksaan kunjungan ini
  $d = mysqli_fetch_assoc(mysqli_query($db,"SELECT dokter_id FROM pemeriksaan WHERE kunjungan_id=$kunjungan_id ORDER BY id DESC LIMIT 1"));
  $dokter_id = $d ? (int)$d['dokter_id'] : 0;
  mysqli_query($db,"INSERT INTO resep (kunjungan_id, dokter_id, status) VALUES ($kunjungan_id,$dokter_id,'draft')");
  $rid = mysqli_insert_id($db);
  $resep = mysqli_fetch_assoc(mysqli_query($db,"SELECT * FROM resep WHERE id=$rid"));
}

// tambah item
if (isset($_POST['add_item'])) {
  $obat_id = (int)($_POST['obat_id'] ?? 0);
  $qty     = (int)($_POST['qty'] ?? 1);
  $aturan  = trim($_POST['aturan_pakai'] ?? '');
  if ($obat_id && $qty>0) {
    $stmt = $db->prepare("INSERT INTO resep_item (resep_id, obat_id, qty, aturan_pakai) VALUES (?,?,?,?)");
    $stmt->bind_param('iiis', $resep['id'], $obat_id, $qty, $aturan);
    $stmt->execute();
    Alert::toast('success','Item ditambahkan.','buat_resep.php?kunjungan_id='.$kunjungan_id); exit;
  } else {
    Alert::toast('warning','Pilih obat & qty.','buat_resep.php?kunjungan_id='.$kunjungan_id); exit;
  }
}

// finalize → kirim ke apotek
if (isset($_POST['kirim_apotek'])) {
  mysqli_query($db,"UPDATE resep SET status='siap' WHERE id=".$resep['id']);
  mysqli_query($db,"UPDATE kunjungan SET status='obat_siap' WHERE id=$kunjungan_id");
  Alert::toast('success','Resep dikirim ke Apotek.', Helper::baseUrl('visits/detail.php?id='.$kunjungan_id));
  exit;
}

$obatQ = mysqli_query($db,"SELECT id, nama_obat, stok, COALESCE(harga, harga_jual, 0) AS harga FROM obat ORDER BY nama_obat ASC");
$items = mysqli_query($db,"SELECT ri.*, o.nama_obat FROM resep_item ri JOIN obat o ON o.id=ri.obat_id WHERE ri.resep_id=".$resep['id']." ORDER BY ri.id DESC");

ob_start();
?>
<h1 class="text-2xl font-bold mb-6 text-gray-800 flex items-center gap-2">
  <i class="fa-solid fa-capsules text-blue-600"></i> Resep
</h1>

<div class="bg-white rounded-xl shadow p-6 space-y-4">
  <form method="POST" class="grid md:grid-cols-4 gap-4">
    <div class="md:col-span-2">
      <label class="block mb-1">Obat</label>
      <select name="obat_id" class="border rounded p-2 w-full">
        <option value="">— Pilih Obat —</option>
        <?php while($o=mysqli_fetch_assoc($obatQ)){ ?>
          <option value="<?= (int)$o['id'] ?>"><?= htmlspecialchars($o['nama_obat']).' — Stok:'.$o['stok'].' — Rp'.number_format($o['harga']) ?></option>
        <?php } ?>
      </select>
    </div>
    <div>
      <label class="block mb-1">Qty</label>
      <input type="number" name="qty" value="1" min="1" class="border rounded p-2 w-full">
    </div>
    <div>
      <label class="block mb-1">Aturan Pakai</label>
      <input type="text" name="aturan_pakai" class="border rounded p-2 w-full" placeholder="3x1 sesudah makan">
    </div>
    <div class="md:col-span-4 flex justify-end gap-2">
      <button name="add_item" class="bg-blue-600 text-white px-4 py-2 rounded">Tambah Item</button>
    </div>
  </form>

  <div class="border-t pt-4">
    <h3 class="font-semibold mb-2">Item Resep</h3>
    <div class="overflow-x-auto">
      <table class="min-w-full table-auto">
        <thead class="bg-gray-100">
          <tr><th class="px-3 py-2 text-left">Obat</th><th class="px-3 py-2">Qty</th><th class="px-3 py-2 text-left">Aturan</th></tr>
        </thead>
        <tbody>
          <?php while($it=mysqli_fetch_assoc($items)){ ?>
            <tr class="border-b">
              <td class="px-3 py-2"><?= htmlspecialchars($it['nama_obat']) ?></td>
              <td class="px-3 py-2 text-center"><?= (int)$it['qty'] ?></td>
              <td class="px-3 py-2"><?= htmlspecialchars($it['aturan_pakai']) ?></td>
            </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
  </div>

  <form method="POST" class="flex justify-end">
    <button name="kirim_apotek" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded">Kirim ke Apotek</button>
  </form>
</div>

<?php
$contentFile = tempnam(sys_get_temp_dir(),'content'); file_put_contents($contentFile, ob_get_clean());
include_once __DIR__.'/../components/layout.php';
