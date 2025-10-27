<?php
require_once __DIR__ . '/../services/Auth.php';
require_once __DIR__ . '/../services/Database.php';
require_once __DIR__ . '/../services/Alert.php';

$auth = new Auth();
$auth->checkLogin();

$db = (new Database())->connect();
$pageTitle = "Edit Data Dokter";

$id = $_GET['id'] ?? null;

if (!$id) {
  echo "<script>
    Swal.fire({icon:'error', title:'Oops!', text:'ID dokter tidak ditemukan!'})
    .then(()=>window.location.href='data_dokter.php');
  </script>";
  exit;
}

$result = mysqli_query($db, "SELECT * FROM dokter WHERE inc='$id'");
$dokter = mysqli_fetch_assoc($result);

if (!$dokter) {
  echo "<script>
    Swal.fire({icon:'error', title:'Data tidak ditemukan', text:'Dokter tidak ditemukan!'})
    .then(()=>window.location.href='data_dokter.php');
  </script>";
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $did = trim($_POST['dokter_id']);
  $nama = trim($_POST['dokter_nama']);
  $spesialis = trim($_POST['spesialis']);
  $tgl = $_POST['tanggal_lahir'];
  $alamat = trim($_POST['alamat']);
  $jk = $_POST['jenis_kelamin'];
  $umur = intval($_POST['umur']);
  $telp = trim($_POST['no_telp']);

  if (!$did || !$nama || !$spesialis || !$tgl || !$alamat || !$jk || !$umur || !$telp) {
    Alert::toast('warning', 'Semua field wajib diisi.', 'edit_dokter.php?id=' . $id);
    exit;
  }

  $query = "UPDATE dokter SET 
    dokter_id='$did', dokter_nama='$nama', spesialis='$spesialis', tanggal_lahir='$tgl', 
    alamat='$alamat', jenis_kelamin='$jk', umur='$umur', no_telp='$telp'
    WHERE inc='$id'";

  if (mysqli_query($db, $query)) {
    Alert::toast('success', 'Data dokter berhasil diperbarui.', 'data_dokter.php');
  } else {
    Alert::toast('error', 'Terjadi kesalahan saat memperbarui data.', 'data_dokter.php');
  }
}

ob_start();
?>

<h1 class="text-2xl font-bold mb-6 text-gray-800 flex items-center gap-2">
  <i class="fa-solid fa-user-pen text-blue-600"></i> Edit Dokter
</h1>

<form method="POST" class="bg-white p-6 rounded-xl shadow-md grid grid-cols-1 md:grid-cols-2 gap-4">
  <div>
    <label class="block mb-1 text-gray-700">ID Dokter</label>
    <input type="text" name="dokter_id" value="<?= htmlspecialchars($dokter['dokter_id']); ?>" required class="w-full border rounded-lg p-2">
  </div>

  <div>
    <label class="block mb-1 text-gray-700">Nama Dokter</label>
    <input type="text" name="dokter_nama" value="<?= htmlspecialchars($dokter['dokter_nama']); ?>" required class="w-full border rounded-lg p-2">
  </div>

  <div>
    <label class="block mb-1 text-gray-700">Spesialis</label>
    <input type="text" name="spesialis" value="<?= htmlspecialchars($dokter['spesialis']); ?>" required class="w-full border rounded-lg p-2">
  </div>

  <div>
    <label class="block mb-1 text-gray-700">Tanggal Lahir</label>
    <input type="date" name="tanggal_lahir" value="<?= htmlspecialchars($dokter['tanggal_lahir']); ?>" required class="w-full border rounded-lg p-2">
  </div>

  <div>
    <label class="block mb-1 text-gray-700">Jenis Kelamin</label>
    <select name="jenis_kelamin" required class="w-full border rounded-lg p-2">
      <option value="Laki-laki" <?= $dokter['jenis_kelamin'] == 'Laki-laki' ? 'selected' : ''; ?>>Laki-laki</option>
      <option value="Perempuan" <?= $dokter['jenis_kelamin'] == 'Perempuan' ? 'selected' : ''; ?>>Perempuan</option>
    </select>
  </div>

  <div>
    <label class="block mb-1 text-gray-700">Umur</label>
    <input type="number" name="umur" value="<?= htmlspecialchars($dokter['umur']); ?>" min="20" required class="w-full border rounded-lg p-2">
  </div>

  <div class="md:col-span-2">
    <label class="block mb-1 text-gray-700">Alamat</label>
    <textarea name="alamat" required class="w-full border rounded-lg p-2"><?= htmlspecialchars($dokter['alamat']); ?></textarea>
  </div>

  <div class="md:col-span-2">
    <label class="block mb-1 text-gray-700">No. Telepon</label>
    <input type="text" name="no_telp" value="<?= htmlspecialchars($dokter['no_telp']); ?>" required class="w-full border rounded-lg p-2">
  </div>

  <div class="md:col-span-2 flex justify-end gap-3 pt-4">
    <a href="data_dokter.php" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg">Batal</a>
    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Simpan Perubahan</button>
  </div>
</form>

<?php
$contentFile = tempnam(sys_get_temp_dir(), 'content');
file_put_contents($contentFile, ob_get_clean());
include_once __DIR__ . '/../components/layout.php';
