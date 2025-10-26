<?php
require_once __DIR__ . '/../services/Auth.php';
require_once __DIR__ . '/../services/Database.php';

$auth = new Auth();
$auth->checkLogin();

$db = (new Database())->connect();
$pageTitle = "Edit Data Pasien";

$id = $_GET['id'] ?? null;

if (!$id) {
  echo "<script>
    Swal.fire({icon:'error',title:'Oops!',text:'ID pasien tidak ditemukan!'})
    .then(()=>window.location.href='data_pasien.php');
  </script>";
  exit;
}

$result = mysqli_query($db, "SELECT * FROM pasien WHERE inc='$id'");
$pasien = mysqli_fetch_assoc($result);

if (!$pasien) {
  echo "<script>
    Swal.fire({icon:'error',title:'Data tidak ditemukan',text:'Pasien tidak ditemukan!'})
    .then(()=>window.location.href='data_pasien.php');
  </script>";
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $pid = $_POST['pasien_id'];
  $nama = $_POST['pasien_nama'];
  $tgl = $_POST['tanggal_lahir'];
  $alamat = $_POST['alamat'];
  $jk = $_POST['jenis_kelamin'];
  $umur = $_POST['umur'];
  $kk = $_POST['nama_kk'];
  $berat = $_POST['berat'];
  $tinggi = $_POST['tinggi'];
  $tgl_daftar = $_POST['tanggal_daftar'];
  $telp = $_POST['telp'];

  $query = "UPDATE pasien SET 
    pasien_id='$pid', pasien_nama='$nama', tanggal_lahir='$tgl', alamat='$alamat', 
    jenis_kelamin='$jk', umur='$umur', nama_kk='$kk', berat='$berat', tinggi='$tinggi', 
    tanggal_daftar='$tgl_daftar', telp='$telp' WHERE inc='$id'";

  if (mysqli_query($db, $query)) {
    echo "<script>
      Swal.fire({
        icon:'success',
        title:'Berhasil!',
        text:'Data pasien berhasil diperbarui.',
        confirmButtonColor:'#2563eb'
      }).then(()=>window.location.href='data_pasien.php');
    </script>";
    exit;
  }
}

ob_start();
?>

<h1 class="text-2xl font-bold mb-6 text-gray-800">
  <i class="fa-solid fa-user-pen text-blue-600 mr-2"></i>Edit Pasien
</h1>

<form method="POST" class="bg-white p-6 rounded-xl shadow-md grid grid-cols-1 md:grid-cols-2 gap-4">
  <div>
    <label class="block mb-1 text-gray-700">ID Pasien</label>
    <input type="text" name="pasien_id" value="<?= htmlspecialchars($pasien['pasien_id']); ?>" required class="w-full border rounded-lg p-2">
  </div>
  <div>
    <label class="block mb-1 text-gray-700">Nama Pasien</label>
    <input type="text" name="pasien_nama" value="<?= htmlspecialchars($pasien['pasien_nama']); ?>" required class="w-full border rounded-lg p-2">
  </div>
  <div>
    <label class="block mb-1 text-gray-700">Tanggal Lahir</label>
    <input type="date" name="tanggal_lahir" value="<?= htmlspecialchars($pasien['tanggal_lahir']); ?>" required class="w-full border rounded-lg p-2">
  </div>
  <div>
    <label class="block mb-1 text-gray-700">Alamat</label>
    <textarea name="alamat" required class="w-full border rounded-lg p-2"><?= htmlspecialchars($pasien['alamat']); ?></textarea>
  </div>
  <div>
    <label class="block mb-1 text-gray-700">Jenis Kelamin</label>
    <select name="jenis_kelamin" required class="w-full border rounded-lg p-2">
      <option value="Laki-laki" <?= $pasien['jenis_kelamin'] == 'Laki-laki' ? 'selected' : ''; ?>>Laki-laki</option>
      <option value="Perempuan" <?= $pasien['jenis_kelamin'] == 'Perempuan' ? 'selected' : ''; ?>>Perempuan</option>
    </select>
  </div>
  <div>
    <label class="block mb-1 text-gray-700">Umur</label>
    <input type="number" name="umur" value="<?= htmlspecialchars($pasien['umur']); ?>" required class="w-full border rounded-lg p-2">
  </div>
  <div>
    <label class="block mb-1 text-gray-700">Nama KK</label>
    <input type="text" name="nama_kk" value="<?= htmlspecialchars($pasien['nama_kk']); ?>" required class="w-full border rounded-lg p-2">
  </div>
  <div>
    <label class="block mb-1 text-gray-700">Berat Badan (kg)</label>
    <input type="number" name="berat" value="<?= htmlspecialchars($pasien['berat']); ?>" required class="w-full border rounded-lg p-2">
  </div>
  <div>
    <label class="block mb-1 text-gray-700">Tinggi Badan (cm)</label>
    <input type="number" name="tinggi" value="<?= htmlspecialchars($pasien['tinggi']); ?>" required class="w-full border rounded-lg p-2">
  </div>
  <div>
    <label class="block mb-1 text-gray-700">Tanggal Daftar</label>
    <input type="date" name="tanggal_daftar" value="<?= htmlspecialchars($pasien['tanggal_daftar']); ?>" required class="w-full border rounded-lg p-2">
  </div>
  <div>
    <label class="block mb-1 text-gray-700">No. Telepon</label>
    <input type="text" name="telp" value="<?= htmlspecialchars($pasien['telp']); ?>" required class="w-full border rounded-lg p-2">
  </div>

  <div class="md:col-span-2 flex justify-end gap-3 pt-4">
    <a href="data_pasien.php" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg">Batal</a>
    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Simpan Perubahan</button>
  </div>
</form>

<?php
$contentFile = tempnam(sys_get_temp_dir(), 'content');
file_put_contents($contentFile, ob_get_clean());
include_once __DIR__ . '/../components/layout.php';
