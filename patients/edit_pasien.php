<?php
require_once __DIR__ . '/../services/Auth.php';
require_once __DIR__ . '/../services/Database.php';
require_once __DIR__ . '/../services/Alert.php';
require_once __DIR__ . '/../services/Helper.php';

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

// Ambil data pasien
$stmt = $db->prepare("SELECT * FROM pasien WHERE inc = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$pasien = $result->fetch_assoc();
$stmt->close();

if (!$pasien) {
  echo "<script>
    Swal.fire({icon:'error',title:'Data tidak ditemukan',text:'Pasien tidak ditemukan!'})
    .then(()=>window.location.href='data_pasien.php');
  </script>";
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $pid = trim($_POST['pasien_id']);
  $nama = trim($_POST['pasien_nama']);
  $tgl = $_POST['tanggal_lahir'];
  $alamat = trim($_POST['alamat']);
  $jk = $_POST['jenis_kelamin'];
  $umur = intval($_POST['umur']);
  $kk = trim($_POST['nama_kk']);
  $berat = floatval($_POST['berat']);
  $tinggi = floatval($_POST['tinggi']);
  $tgl_daftar = $_POST['tanggal_daftar'];
  $telp = trim($_POST['telp']);

  // Validasi backend
  if (!$pid || !$nama || !$tgl || !$alamat || !$jk || !$umur || !$kk || !$berat || !$tinggi || !$tgl_daftar || !$telp) {
    Alert::toast('error', 'Semua field wajib diisi.', Helper::baseUrl('patients/edit_pasien.php?id=' . $id));
    exit;
  }

  if (!preg_match('/^[0-9+\-\s]{8,15}$/', $telp)) {
    Alert::toast('warning', 'Nomor telepon tidak valid.', Helper::baseUrl('patients/edit_pasien.php?id=' . $id));
    exit;
  }

  // Update data (prepared statement)
  $stmt = $db->prepare("UPDATE pasien SET 
    pasien_id=?, pasien_nama=?, tanggal_lahir=?, alamat=?, jenis_kelamin=?, umur=?, 
    nama_kk=?, berat=?, tinggi=?, tanggal_daftar=?, telp=? WHERE inc=?");

  $stmt->bind_param("sssssisddssi", $pid, $nama, $tgl, $alamat, $jk, $umur, $kk, $berat, $tinggi, $tgl_daftar, $telp, $id);

  if ($stmt->execute()) {
    Alert::toast('success', 'Data pasien berhasil diperbarui.', Helper::baseUrl('patients/data_pasien.php'));
  } else {
    Alert::toast('error', 'Terjadi kesalahan saat memperbarui data.', Helper::baseUrl('patients/data_pasien.php'));
  }
  $stmt->close();
}
?>

<?php ob_start(); ?>
<h1 class="text-2xl font-bold mb-6 text-gray-800">
  <i class="fa-solid fa-user-pen text-blue-600 mr-2"></i>Edit Pasien
</h1>

<form method="POST" onsubmit="return validateForm()" 
      class="bg-white p-6 rounded-xl shadow-md grid grid-cols-1 md:grid-cols-2 gap-4">

  <div>
    <label class="block mb-1 text-gray-700">ID Pasien *</label>
    <input type="text" name="pasien_id" id="pasien_id" value="<?= htmlspecialchars($pasien['pasien_id']); ?>" 
           required class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-blue-500">
  </div>

  <div>
    <label class="block mb-1 text-gray-700">Nama Pasien *</label>
    <input type="text" name="pasien_nama" id="pasien_nama" value="<?= htmlspecialchars($pasien['pasien_nama']); ?>" 
           required class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-blue-500">
  </div>

  <div>
    <label class="block mb-1 text-gray-700">Tanggal Lahir *</label>
    <input type="date" name="tanggal_lahir" id="tanggal_lahir" value="<?= htmlspecialchars($pasien['tanggal_lahir']); ?>" 
           required class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-blue-500">
  </div>

  <div>
    <label class="block mb-1 text-gray-700">Alamat *</label>
    <textarea name="alamat" id="alamat" 
              class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-blue-500" required><?= htmlspecialchars($pasien['alamat']); ?></textarea>
  </div>

  <div>
    <label class="block mb-1 text-gray-700">Jenis Kelamin *</label>
    <select name="jenis_kelamin" id="jenis_kelamin" 
            class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-blue-500" required>
      <option value="Laki-laki" <?= $pasien['jenis_kelamin'] == 'Laki-laki' ? 'selected' : ''; ?>>Laki-laki</option>
      <option value="Perempuan" <?= $pasien['jenis_kelamin'] == 'Perempuan' ? 'selected' : ''; ?>>Perempuan</option>
    </select>
  </div>

  <div>
    <label class="block mb-1 text-gray-700">Umur *</label>
    <input type="number" name="umur" id="umur" min="0" max="120" value="<?= htmlspecialchars($pasien['umur']); ?>" 
           required class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-blue-500">
  </div>

  <div>
    <label class="block mb-1 text-gray-700">Nama KK *</label>
    <input type="text" name="nama_kk" id="nama_kk" value="<?= htmlspecialchars($pasien['nama_kk']); ?>" 
           required class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-blue-500">
  </div>

  <div>
    <label class="block mb-1 text-gray-700">Berat Badan (kg) *</label>
    <input type="number" name="berat" id="berat" min="1" max="300" value="<?= htmlspecialchars($pasien['berat']); ?>" 
           required class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-blue-500">
  </div>

  <div>
    <label class="block mb-1 text-gray-700">Tinggi Badan (cm) *</label>
    <input type="number" name="tinggi" id="tinggi" min="10" max="250" value="<?= htmlspecialchars($pasien['tinggi']); ?>" 
           required class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-blue-500">
  </div>

  <div>
    <label class="block mb-1 text-gray-700">Tanggal Daftar *</label>
    <input type="date" name="tanggal_daftar" id="tanggal_daftar" value="<?= htmlspecialchars($pasien['tanggal_daftar']); ?>" 
           required class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-blue-500">
  </div>

  <div>
    <label class="block mb-1 text-gray-700">No. Telepon *</label>
    <input type="text" name="telp" id="telp" value="<?= htmlspecialchars($pasien['telp']); ?>" 
           required class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-blue-500">
  </div>

  <div class="md:col-span-2 flex justify-end gap-3 pt-4">
    <a href="<?= Helper::baseUrl('patients/data_pasien.php') ?>" 
       class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg">Batal</a>
    <button type="submit" 
            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Simpan Perubahan</button>
  </div>
</form>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function validateForm() {
  const fields = ['pasien_id','pasien_nama','tanggal_lahir','alamat','jenis_kelamin','umur','nama_kk','berat','tinggi','tanggal_daftar','telp'];
  for (const id of fields) {
    const el = document.getElementById(id);
    if (!el.value.trim()) {
      Swal.fire('Error!', 'Semua field wajib diisi!', 'error');
      el.focus();
      return false;
    }
  }
  const telp = document.getElementById('telp').value;
  if (!/^[0-9+\-\s]{8,15}$/.test(telp)) {
    Swal.fire('Peringatan!', 'Format nomor telepon tidak valid!', 'warning');
    return false;
  }
  return true;
}
</script>

<?php
$contentFile = tempnam(sys_get_temp_dir(), 'content');
file_put_contents($contentFile, ob_get_clean());
include_once __DIR__ . '/../components/layout.php';
