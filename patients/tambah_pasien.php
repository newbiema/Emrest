<?php
require_once __DIR__ . '/../services/Auth.php';
require_once __DIR__ . '/../services/Database.php';
require_once __DIR__ . '/../services/Alert.php';
require_once __DIR__ . '/../services/Helper.php';

$auth = new Auth();
$auth->checkLogin();
$db = (new Database())->connect();
$pageTitle = "Tambah Pasien";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Ambil input & sanitasi
  $id        = trim($_POST['pasien_id']);
  $nama      = trim($_POST['pasien_nama']);
  $tanggal   = $_POST['tanggal_lahir'];
  $alamat    = trim($_POST['alamat']);
  $jk        = $_POST['jenis_kelamin'];
  $umur      = intval($_POST['umur']);
  $kk        = trim($_POST['nama_kk']);
  $berat     = floatval($_POST['berat']);
  $tinggi    = floatval($_POST['tinggi']);
  $tgl_daftar= $_POST['tanggal_daftar'];
  $telp      = trim($_POST['telp']);

  // Validasi backend (fallback jika JS dimatikan)
  if (!$id || !$nama || !$tanggal || !$alamat || !$jk || !$umur || !$kk || !$berat || !$tinggi || !$tgl_daftar || !$telp) {
    Alert::toast('error', 'Semua field wajib diisi.', Helper::baseUrl('patients/tambah_pasien.php'));
    exit;
  }

  if (!preg_match('/^[0-9+\-\s]{8,15}$/', $telp)) {
    Alert::toast('warning', 'Nomor telepon tidak valid.', Helper::baseUrl('patients/tambah_pasien.php'));
    exit;
  }

  // Insert data (prepared statement agar aman)
  $stmt = $db->prepare("INSERT INTO pasien 
    (pasien_id, pasien_nama, tanggal_lahir, alamat, jenis_kelamin, umur, nama_kk, berat, tinggi, tanggal_daftar, telp)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
  $stmt->bind_param("sssssisddss", $id, $nama, $tanggal, $alamat, $jk, $umur, $kk, $berat, $tinggi, $tgl_daftar, $telp);

  if ($stmt->execute()) {
    Alert::toast('success', 'Data pasien berhasil ditambahkan.', Helper::baseUrl('patients/data_pasien.php'));
  } else {
    Alert::toast('error', 'Terjadi kesalahan saat menambah data.', Helper::baseUrl('patients/data_pasien.php'));
  }
  $stmt->close();
}
?>

<?php ob_start(); ?>
<h1 class="text-2xl font-bold mb-6 text-gray-800">
  <i class="fa-solid fa-user-plus text-blue-600 mr-2"></i>Tambah Pasien
</h1>

<form method="POST" onsubmit="return validateForm()" 
      class="bg-white p-6 rounded-xl shadow-md grid grid-cols-1 md:grid-cols-2 gap-4">

  <div>
    <label class="block mb-1 text-gray-700">ID Pasien *</label>
    <input type="text" name="pasien_id" id="pasien_id" 
           class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-blue-500" required>
  </div>

  <div>
    <label class="block mb-1 text-gray-700">Nama Pasien *</label>
    <input type="text" name="pasien_nama" id="pasien_nama" 
           class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-blue-500" required>
  </div>

  <div>
    <label class="block mb-1 text-gray-700">Tanggal Lahir *</label>
    <input type="date" name="tanggal_lahir" id="tanggal_lahir" 
           class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-blue-500" required>
  </div>

  <div>
    <label class="block mb-1 text-gray-700">Alamat *</label>
    <textarea name="alamat" id="alamat" 
              class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-blue-500" required></textarea>
  </div>

  <div>
    <label class="block mb-1 text-gray-700">Jenis Kelamin *</label>
    <select name="jenis_kelamin" id="jenis_kelamin" 
            class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-blue-500" required>
      <option value="">-- Pilih --</option>
      <option value="Laki-laki">Laki-laki</option>
      <option value="Perempuan">Perempuan</option>
    </select>
  </div>

  <div>
    <label class="block mb-1 text-gray-700">Umur *</label>
    <input type="number" name="umur" id="umur" min="0" max="120" 
           class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-blue-500" required>
  </div>

  <div>
    <label class="block mb-1 text-gray-700">Nama KK *</label>
    <input type="text" name="nama_kk" id="nama_kk" 
           class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-blue-500" required>
  </div>

  <div>
    <label class="block mb-1 text-gray-700">Berat Badan (kg) *</label>
    <input type="number" name="berat" id="berat" min="1" max="300"
           class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-blue-500" required>
  </div>

  <div>
    <label class="block mb-1 text-gray-700">Tinggi Badan (cm) *</label>
    <input type="number" name="tinggi" id="tinggi" min="10" max="250"
           class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-blue-500" required>
  </div>

  <div>
    <label class="block mb-1 text-gray-700">Tanggal Daftar *</label>
    <input type="date" name="tanggal_daftar" id="tanggal_daftar"
           class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-blue-500" required>
  </div>

  <div>
    <label class="block mb-1 text-gray-700">No. Telepon *</label>
    <input type="text" name="telp" id="telp"
           class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-blue-500" required>
  </div>

  <div class="md:col-span-2 flex justify-end gap-3 pt-4">
    <a href="<?= Helper::baseUrl('patients/data_pasien.php') ?>" 
       class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg">Batal</a>
    <button type="submit" 
            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Simpan</button>
  </div>
</form>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function validateForm() {
  const fields = ['pasien_id', 'pasien_nama', 'tanggal_lahir', 'alamat', 'jenis_kelamin', 'umur', 'nama_kk', 'berat', 'tinggi', 'tanggal_daftar', 'telp'];
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
