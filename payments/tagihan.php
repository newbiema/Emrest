<?php
require_once __DIR__.'/../services/Auth.php';
require_once __DIR__.'/../services/Database.php';
require_once __DIR__.'/../services/Helper.php';

$auth = new Auth(); $auth->checkLogin();
$auth->authorize(['admin','loket']);

$db = (new Database())->connect();
$pageTitle = "Tagihan";

$kunjungan_id = (int)($_GET['kunjungan_id'] ?? 0);
$k = mysqli_fetch_assoc(mysqli_query($db,"SELECT * FROM kunjungan WHERE id=$kunjungan_id"));
if(!$k){ die('Kunjungan tidak ditemukan'); }

$sql = "SELECT SUM(ri.qty * COALESCE(o.harga, o.harga_jual, 0)) AS subtotal_obat
        FROM resep r 
        JOIN resep_item ri ON ri.resep_id = r.id
        JOIN obat o ON o.id = ri.obat_id
        WHERE r.kunjungan_id=$kunjungan_id";
$ro = mysqli_fetch_assoc(mysqli_query($db,$sql));
$subtotal_obat = (float)($ro['subtotal_obat'] ?? 0);

// Tarif jasa sederhana (bisa dibuat tabel tarif_jasa per poli)
$tarif = ['Umum'=>50000,'Anak'=>60000,'Gigi'=>80000,'Kandungan'=>90000];
$subtotal_jasa = (float)($tarif[$k['poli']] ?? 50000);

$total = $subtotal_jasa + $subtotal_obat;

ob_start();
?>
<h1 class="text-2xl font-bold mb-6 text-gray-800 flex items-center gap-2">
  <i class="fa-solid fa-receipt text-blue-600"></i> Tagihan
</h1>

<div class="bg-white rounded-xl shadow p-6 space-y-2">
  <div class="flex justify-between"><span>Jasa Poli (<?= htmlspecialchars($k['poli']) ?>)</span><span>Rp <?= number_format($subtotal_jasa) ?></span></div>
  <div class="flex justify-between"><span>Obat</span><span>Rp <?= number_format($subtotal_obat) ?></span></div>
  <hr>
  <div class="flex justify-between font-semibold text-lg"><span>Total</span><span>Rp <?= number_format($total) ?></span></div>

  <form method="POST" action="<?= Helper::baseUrl('payments/bayar.php') ?>" class="mt-4 grid md:grid-cols-3 gap-3">
    <input type="hidden" name="kunjungan_id" value="<?= $kunjungan_id ?>">
    <input type="hidden" name="subtotal_jasa" value="<?= $subtotal_jasa ?>">
    <input type="hidden" name="subtotal_obat" value="<?= $subtotal_obat ?>">
    <input type="hidden" name="total" value="<?= $total ?>">
    <div>
      <label class="block mb-1">Metode</label>
      <select name="metode" class="border p-2 rounded w-full">
        <option value="tunai">Tunai</option>
        <option value="qris">QRIS</option>
        <option value="kartu">Kartu</option>
      </select>
    </div>
    <div class="md:col-span-2 flex items-end justify-end">
      <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">Bayar & Selesaikan</button>
    </div>
  </form>
</div>

<?php
$contentFile = tempnam(sys_get_temp_dir(),'content'); file_put_contents($contentFile, ob_get_clean());
include_once __DIR__.'/../components/layout.php';
