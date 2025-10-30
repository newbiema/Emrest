<?php
require_once __DIR__ . '/../services/Auth.php';
require_once __DIR__ . '/../services/Database.php';
require_once __DIR__ . '/../services/Alert.php';
require_once __DIR__ . '/../services/Helper.php';

$auth = new Auth();
$auth->checkLogin();
$auth->authorize(['admin','perawat','loket']);

// Matikan throw exception dari MySQLi agar tidak fatal error putih
mysqli_report(MYSQLI_REPORT_OFF);

$db = (new Database())->connect();
$pageTitle = "Tambah Pasien";

// ========= HANDLE POST SUBMIT =========
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Ambil input & sanitasi
  $id         = trim($_POST['pasien_id'] ?? '');
  $nama       = trim($_POST['pasien_nama'] ?? '');
  $tanggal    = $_POST['tanggal_lahir'] ?? '';
  $alamat     = trim($_POST['alamat'] ?? '');
  $jk         = $_POST['jenis_kelamin'] ?? '';
  $umur       = isset($_POST['umur']) ? (int)$_POST['umur'] : null;
  $kk         = trim($_POST['nama_kk'] ?? '');
  $berat      = isset($_POST['berat']) ? (float)$_POST['berat'] : null;
  $tinggi     = isset($_POST['tinggi']) ? (float)$_POST['tinggi'] : null;
  $tgl_daftar = $_POST['tanggal_daftar'] ?? '';
  $telp       = trim($_POST['telp'] ?? '');

  // Validasi backend (fallback jika JS dimatikan)
  $required = [
    'pasien_id' => $id, 'pasien_nama' => $nama, 'tanggal_lahir' => $tanggal, 'alamat' => $alamat,
    'jenis_kelamin' => $jk, 'umur' => $umur, 'nama_kk' => $kk, 'berat' => $berat,
    'tinggi' => $tinggi, 'tanggal_daftar' => $tgl_daftar, 'telp' => $telp
  ];
  foreach ($required as $field => $value) {
    if ($value === '' || $value === null) {
      Alert::toast('warning', 'Semua field wajib diisi.', Helper::baseUrl('patients/tambah_pasien.php?focus='.$field));
      exit;
    }
  }

  // Validasi telp
  if (!preg_match('/^[0-9+\-\s]{8,15}$/', $telp)) {
    Alert::toast('warning', 'Nomor telepon tidak valid.', Helper::baseUrl('patients/tambah_pasien.php?focus=telp'));
    exit;
  }

  // Insert dengan prepared statement
  $stmt = $db->prepare("INSERT INTO pasien 
    (pasien_id, pasien_nama, tanggal_lahir, alamat, jenis_kelamin, umur, nama_kk, berat, tinggi, tanggal_daftar, telp)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
  // s s s s s i s d d s s  -> "sssssisddss"
  $stmt->bind_param("sssssisddss", $id, $nama, $tanggal, $alamat, $jk, $umur, $kk, $berat, $tinggi, $tgl_daftar, $telp);

  if ($stmt->execute()) {
    $stmt->close();
    Alert::toast('success', 'Data pasien berhasil ditambahkan.', Helper::baseUrl('patients/data_pasien.php'));
    exit;
  } else {
    // Tangani duplikat & error lain dengan toast → tanpa error putih
    if ($stmt->errno == 1062) {
      // key unik bentrok (pasien_id)
      $stmt->close();
      Alert::toast(
        'warning',
        "ID Pasien <b>".htmlspecialchars($id)."</b> sudah terdaftar. Silakan gunakan ID lain.",
        Helper::baseUrl('patients/tambah_pasien.php?focus=pasien_id')
      );
      exit;
    } else {
      // error selain duplikat
      $err = $stmt->error;
      $stmt->close();
      // (opsional) jangan tampilkan detail $err ke user produksi; cukup pesan umum:
      Alert::toast(
        'error',
        'Terjadi kesalahan saat menyimpan data. Periksa kembali input Anda.',
        Helper::baseUrl('patients/tambah_pasien.php')
      );
      exit;
    }
  }
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
    <input type="number" name="berat" id="berat" min="1" max="300" step="0.1"
           class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-blue-500" required>
  </div>

  <div>
    <label class="block mb-1 text-gray-700">Tinggi Badan (cm) *</label>
    <input type="number" name="tinggi" id="tinggi" min="10" max="250" step="0.1"
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
// Validasi ringan di sisi klien
function validateForm() {
  const fields = ['pasien_id','pasien_nama','tanggal_lahir','alamat','jenis_kelamin','umur','nama_kk','berat','tinggi','tanggal_daftar','telp'];
  for (const id of fields) {
    const el = document.getElementById(id);
    if (!el || !el.value.trim()) {
      Swal.fire('Error!', 'Semua field wajib diisi!', 'error');
      el?.focus();
      return false;
    }
  }
  const telp = document.getElementById('telp').value.trim();
  if (!/^[0-9+\-\s]{8,15}$/.test(telp)) {
    Swal.fire('Peringatan!', 'Format nomor telepon tidak valid!', 'warning');
    document.getElementById('telp').focus();
    return false;
  }
  return true;
}

// Auto-focus ke field jika dikirim via query ?focus=
document.addEventListener('DOMContentLoaded', () => {
  const params = new URLSearchParams(window.location.search);
  const f = params.get('focus');
  if (f) {
    const el = document.getElementById(f);
    if (el) el.focus();
  }
});
</script>

<?php
// Render ke layout
$contentFile = tempnam(sys_get_temp_dir(), 'content');
file_put_contents($contentFile, ob_get_clean());
include_once __DIR__ . '/../components/layout.php';
