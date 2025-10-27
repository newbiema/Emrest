<?php
require_once __DIR__ . '/../services/Auth.php';
require_once __DIR__ . '/../services/Database.php';
require_once __DIR__ . '/../services/Alert.php';

$auth = new Auth();
$auth->checkLogin();

$db = (new Database())->connect();
$pageTitle = "Tambah Dokter";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = trim($_POST['dokter_id']);
  $nama = trim($_POST['dokter_nama']);
  $spesialis = trim($_POST['spesialis']);
  $tanggal = $_POST['tanggal_lahir'];
  $alamat = trim($_POST['alamat']);
  $jk = $_POST['jenis_kelamin'];
  $umur = intval($_POST['umur']);
  $telp = trim($_POST['no_telp']);
  $tanggal_daftar = $_POST['tanggal_daftar'];




  // Validasi sederhana
  if (!$id || !$nama || !$spesialis || !$tanggal || !$alamat || !$jk || !$umur || !$telp) {
    Alert::toast('warning', 'Semua field wajib diisi.', 'tambah_dokter.php');
    exit;
  }

  // Insert data
    $query = "INSERT INTO dokter (dokter_id, dokter_nama, spesialis, tanggal_lahir, alamat, jenis_kelamin, umur, no_telp, tanggal_daftar)
            VALUES ('$id', '$nama', '$spesialis', '$tanggal', '$alamat', '$jk', '$umur', '$telp', '$tanggal_daftar')";

  if (mysqli_query($db, $query)) {
    Alert::toast('success', 'Data dokter berhasil ditambahkan.', 'data_dokter.php');
  } else {
    Alert::toast('error', 'Terjadi kesalahan saat menambah data dokter.', 'data_dokter.php');
  }
}

ob_start();
?>

<h1 class="text-2xl font-bold mb-6 text-gray-800 flex items-center gap-2">
  <i class="fa-solid fa-user-doctor text-blue-600"></i> Tambah Dokter
</h1>

<form method="POST" class="bg-white p-6 rounded-xl shadow-md grid grid-cols-1 md:grid-cols-2 gap-4">
  <div>
    <label class="block mb-1 text-gray-700">ID Dokter</label>
    <input type="text" name="dokter_id" required class="w-full border rounded-lg p-2">
  </div>

  <div>
    <label class="block mb-1 text-gray-700">Nama Dokter</label>
    <input type="text" name="dokter_nama" required class="w-full border rounded-lg p-2">
  </div>

  <div>
    <label class="block mb-1 text-gray-700">Spesialis</label>
    <input type="text" name="spesialis" required class="w-full border rounded-lg p-2" placeholder="Contoh: Anak, Umum, Gigi">
  </div>

  <div>
    <label class="block mb-1 text-gray-700">Tanggal Lahir</label>
    <input type="date" name="tanggal_lahir" required class="w-full border rounded-lg p-2">
  </div>

  <div>
    <label class="block mb-1 text-gray-700">Jenis Kelamin</label>
    <select name="jenis_kelamin" required class="w-full border rounded-lg p-2">
      <option value="">-- Pilih --</option>
      <option value="Laki-laki">Laki-laki</option>
      <option value="Perempuan">Perempuan</option>
    </select>
  </div>

  <div>
    <label class="block mb-1 text-gray-700">Umur</label>
    <input type="number" name="umur" min="20" required class="w-full border rounded-lg p-2">
  </div>

  <div class="md:col-span-2">
    <label class="block mb-1 text-gray-700">Alamat</label>
    <textarea name="alamat" required class="w-full border rounded-lg p-2" rows="2"></textarea>
  </div>

  <div class="md:col-span-2">
    <label class="block mb-1 text-gray-700">No. Telepon</label>
    <input type="text" name="no_telp" required class="w-full border rounded-lg p-2" placeholder="Contoh: 08123456789">
  </div>

  <div class="md:col-span-2">
    <label class="block mb-1 text-gray-700">Tanggal Daftar</label>
    <input type="date" name="tanggal_daftar" required class="w-full border rounded-lg p-2" value="<?= date('Y-m-d') ?>">
  </div>


  <div class="md:col-span-2 flex justify-end gap-3 pt-4">
    <a href="data_dokter.php" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg">
      Batal
    </a>
    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
      Simpan
    </button>
  </div>
</form>

<?php
$contentFile = tempnam(sys_get_temp_dir(), 'content');
file_put_contents($contentFile, ob_get_clean());
include_once __DIR__ . '/../components/layout.php';
