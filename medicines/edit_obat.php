<?php
require_once __DIR__ . '/../services/Auth.php';
require_once __DIR__ . '/../services/Database.php';
require_once __DIR__ . '/../services/Alert.php';

$auth = new Auth();
$auth->checkLogin();
$db = (new Database())->connect();
$pageTitle = "Edit Obat";

$id = $_GET['id'] ?? null;
if (!$id) {
  Alert::toast('error', 'ID obat tidak ditemukan.', 'data_obat.php');
  exit;
}

$result = mysqli_query($db, "SELECT * FROM obat WHERE id='$id'");
$obat = mysqli_fetch_assoc($result);
if (!$obat) {
  Alert::toast('error', 'Data obat tidak ditemukan.', 'data_obat.php');
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $kode = trim($_POST['kode_obat']);
  $nama = trim($_POST['nama_obat']);
  $kategori = trim($_POST['kategori']);
  $stok = intval($_POST['stok']);
  $harga = floatval($_POST['harga']);
  $keterangan = trim($_POST['keterangan']);

  $query = "UPDATE obat SET 
    kode_obat='$kode', nama_obat='$nama', kategori='$kategori',
    stok='$stok', harga='$harga', keterangan='$keterangan' 
    WHERE id='$id'";
  if (mysqli_query($db, $query)) {
    Alert::toast('success', 'Data obat berhasil diperbarui.', 'data_obat.php');
  } else {
    Alert::toast('error', 'Gagal memperbarui data obat.', 'data_obat.php');
  }
}

ob_start();
?>
<h1 class="text-2xl font-bold mb-6 text-gray-800 flex items-center gap-2">
  <i class="fa-solid fa-pen-to-square text-blue-600"></i> Edit Obat
</h1>

<form method="POST" class="bg-white p-6 rounded-xl shadow-md grid grid-cols-1 md:grid-cols-2 gap-4">
  <div>
    <label class="block mb-1 text-gray-700">Kode Obat</label>
    <input type="text" name="kode_obat" value="<?= htmlspecialchars($obat['kode_obat']); ?>" required class="w-full border rounded-lg p-2">
  </div>
  <div>
    <label class="block mb-1 text-gray-700">Nama Obat</label>
    <input type="text" name="nama_obat" value="<?= htmlspecialchars($obat['nama_obat']); ?>" required class="w-full border rounded-lg p-2">
  </div>
  <div>
    <label class="block mb-1 text-gray-700">Kategori</label>
    <input type="text" name="kategori" value="<?= htmlspecialchars($obat['kategori']); ?>" required class="w-full border rounded-lg p-2">
  </div>
  <div>
    <label class="block mb-1 text-gray-700">Stok</label>
    <input type="number" name="stok" value="<?= htmlspecialchars($obat['stok']); ?>" required class="w-full border rounded-lg p-2">
  </div>
  <div>
    <label class="block mb-1 text-gray-700">Harga (Rp)</label>
    <input type="number" name="harga" value="<?= htmlspecialchars($obat['harga']); ?>" required class="w-full border rounded-lg p-2">
  </div>
  <div class="md:col-span-2">
    <label class="block mb-1 text-gray-700">Keterangan</label>
    <textarea name="keterangan" class="w-full border rounded-lg p-2" rows="2"><?= htmlspecialchars($obat['keterangan']); ?></textarea>
  </div>
  <div class="md:col-span-2 flex justify-end gap-3 pt-4">
    <a href="data_obat.php" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg">Batal</a>
    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Simpan</button>
  </div>
</form>
<?php
$contentFile = tempnam(sys_get_temp_dir(), 'content');
file_put_contents($contentFile, ob_get_clean());
include_once __DIR__ . '/../components/layout.php';
