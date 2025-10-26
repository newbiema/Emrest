<?php
require_once __DIR__ . '/../services/Auth.php';
require_once __DIR__ . '/../services/Database.php';
require_once __DIR__ . '/../services/Alert.php';


$auth = new Auth();
$auth->checkLogin();
$db = (new Database())->connect();
$pageTitle = "Tambah Pasien";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = $_POST['pasien_id'];
  $nama = $_POST['pasien_nama'];
  $tanggal = $_POST['tanggal_lahir'];
  $alamat = $_POST['alamat'];
  $jk = $_POST['jenis_kelamin'];
  $umur = $_POST['umur'];
  $kk = $_POST['nama_kk'];
  $berat = $_POST['berat'];
  $tinggi = $_POST['tinggi'];
  $tgl_daftar = $_POST['tanggal_daftar'];
  $telp = $_POST['telp'];

  $query = "INSERT INTO pasien (pasien_id, pasien_nama, tanggal_lahir, alamat, jenis_kelamin, umur, nama_kk, berat, tinggi, tanggal_daftar, telp)
            VALUES ('$id','$nama','$tanggal','$alamat','$jk','$umur','$kk','$berat','$tinggi','$tgl_daftar','$telp')";
    if (mysqli_query($db, $query)) {
    Alert::toast('success', 'Data pasien berhasil ditambahkan.', 'data_pasien.php');
    } else {
    Alert::toast('error', 'Terjadi kesalahan saat menambah data.', 'data_pasien.php');
    }


}

ob_start();
?>
<h1 class="text-2xl font-bold mb-6 text-gray-800"><i class="fa-solid fa-user-plus text-blue-600 mr-2"></i>Tambah Pasien</h1>

<form method="POST" class="bg-white p-6 rounded-xl shadow-md grid grid-cols-1 md:grid-cols-2 gap-4">
  <div>
    <label class="block mb-1 text-gray-700">ID Pasien</label>
    <input type="text" name="pasien_id" required class="w-full border rounded-lg p-2">
  </div>
  <div>
    <label class="block mb-1 text-gray-700">Nama Pasien</label>
    <input type="text" name="pasien_nama" required class="w-full border rounded-lg p-2">
  </div>
  <div>
    <label class="block mb-1 text-gray-700">Tanggal Lahir</label>
    <input type="date" name="tanggal_lahir" required class="w-full border rounded-lg p-2">
  </div>
  <div>
    <label class="block mb-1 text-gray-700">Alamat</label>
    <textarea name="alamat" required class="w-full border rounded-lg p-2"></textarea>
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
    <input type="number" name="umur" min="0" required class="w-full border rounded-lg p-2">
  </div>
  <div>
    <label class="block mb-1 text-gray-700">Nama KK</label>
    <input type="text" name="nama_kk" required class="w-full border rounded-lg p-2">
  </div>
  <div>
    <label class="block mb-1 text-gray-700">Berat Badan (kg)</label>
    <input type="number" name="berat" required class="w-full border rounded-lg p-2">
  </div>
  <div>
    <label class="block mb-1 text-gray-700">Tinggi Badan (cm)</label>
    <input type="number" name="tinggi" required class="w-full border rounded-lg p-2">
  </div>
  <div>
    <label class="block mb-1 text-gray-700">Tanggal Daftar</label>
    <input type="date" name="tanggal_daftar" required class="w-full border rounded-lg p-2">
  </div>
  <div>
    <label class="block mb-1 text-gray-700">No. Telepon</label>
    <input type="text" name="telp" required class="w-full border rounded-lg p-2">
  </div>

  <div class="md:col-span-2 flex justify-end gap-3 pt-4">
    <a href="data_pasien.php" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg">Batal</a>
    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Simpan</button>
  </div>
</form>
<?php
$contentFile = tempnam(sys_get_temp_dir(), 'content');
file_put_contents($contentFile, ob_get_clean());
include_once __DIR__ . '/../components/layout.php';
