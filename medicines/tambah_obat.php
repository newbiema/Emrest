<?php
require_once __DIR__ . '/../services/Auth.php';
require_once __DIR__ . '/../services/Database.php';
require_once __DIR__ . '/../services/Alert.php';

$auth = new Auth();
$auth->checkLogin();
$db = (new Database())->connect();
$pageTitle = "Tambah Obat";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $kode = trim($_POST['kode_obat']);
  $nama = trim($_POST['nama_obat']);
  $kategori = trim($_POST['kategori']);
  $stok = intval($_POST['stok']);
  $harga = floatval($_POST['harga']);
  $keterangan = trim($_POST['keterangan']);

  if (!$kode || !$nama || !$kategori || $stok <= 0 || $harga <= 0) {
    Alert::toast('warning', 'Semua field wajib diisi dengan benar.', 'tambah_obat.php');
    exit;
  }

  $query = "INSERT INTO obat (kode_obat, nama_obat, kategori, stok, harga, keterangan)
            VALUES ('$kode', '$nama', '$kategori', '$stok', '$harga', '$keterangan')";
  if (mysqli_query($db, $query)) {
    Alert::toast('success', 'Data obat berhasil ditambahkan.', 'data_obat.php');
  } else {
    Alert::toast('error', 'Terjadi kesalahan saat menambah data obat.', 'data_obat.php');
  }
}

ob_start();
?>
<h1 class="text-2xl font-bold mb-6 text-gray-800 flex items-center gap-2">
  <i class="fa-solid fa-pills text-blue-600"></i> Tambah Obat
</h1>

<form method="POST" class="bg-white p-6 rounded-xl shadow-md grid grid-cols-1 md:grid-cols-2 gap-4">
  <div>
    <label class="block mb-1 text-gray-700">Kode Obat</label>
    <input type="text" name="kode_obat" required class="w-full border rounded-lg p-2">
  </div>
  <div>
    <label class="block mb-1 text-gray-700">Nama Obat</label>
    <input type="text" name="nama_obat" required class="w-full border rounded-lg p-2">
  </div>
  <div>
    <label class="block mb-1 text-gray-700">Kategori</label>
    <input type="text" name="kategori" required class="w-full border rounded-lg p-2" placeholder="Contoh: Antibiotik">
  </div>
  <div>
    <label class="block mb-1 text-gray-700">Stok</label>
    <input type="number" name="stok" min="1" required class="w-full border rounded-lg p-2">
  </div>
  <div>
    <label class="block mb-1 text-gray-700">Harga (Rp)</label>
    <input type="number" name="harga" min="100" required class="w-full border rounded-lg p-2">
  </div>
  <div class="md:col-span-2">
    <label class="block mb-1 text-gray-700">Keterangan</label>
    <textarea name="keterangan" class="w-full border rounded-lg p-2" rows="2"></textarea>
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
