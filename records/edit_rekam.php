<?php
require_once __DIR__ . '/../services/Auth.php';
require_once __DIR__ . '/../services/Database.php';
require_once __DIR__ . '/../services/Alert.php';

$auth = new Auth();
$auth->checkLogin();

$db = (new Database())->connect();
$pageTitle = "Edit Rekam Medis";

// Ambil ID dari URL
$id = $_GET['id'] ?? null;
if (!$id) {
  Alert::toast('warning', 'Data tidak ditemukan.', 'data_rekam.php');
  exit;
}

// Ambil data rekam medis
$result = mysqli_query($db, "SELECT * FROM rekam_medis WHERE id = '$id'");
$rekam = mysqli_fetch_assoc($result);

if (!$rekam) {
  Alert::toast('error', 'Rekam medis tidak ditemukan.', 'data_rekam.php');
  exit;
}

// Ambil data pasien, dokter, obat untuk dropdown
$pasien = mysqli_query($db, "SELECT inc, pasien_nama FROM pasien ORDER BY pasien_nama ASC");
$dokter = mysqli_query($db, "SELECT id, dokter_nama FROM dokter ORDER BY dokter_nama ASC");
$obat = mysqli_query($db, "SELECT id, nama_obat FROM obat ORDER BY nama_obat ASC");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $kode_rm = trim($_POST['kode_rm']);
  $tanggal = $_POST['tanggal'];
  $pasien_id = $_POST['pasien_id'];
  $dokter_id = $_POST['dokter_id'];
  $obat_id = $_POST['obat_id'];
  $diagnosa = trim($_POST['diagnosa']);
  $tindakan = trim($_POST['tindakan']);
  $keterangan = trim($_POST['keterangan']);

  if (!$kode_rm || !$tanggal || !$pasien_id || !$dokter_id || !$obat_id || !$diagnosa) {
    Alert::toast('warning', 'Semua field wajib diisi.', "edit_rekam.php?id=$id");
    exit;
  }

  $query = "UPDATE rekam_medis SET 
    kode_rm = '$kode_rm',
    tanggal = '$tanggal',
    pasien_id = '$pasien_id',
    dokter_id = '$dokter_id',
    obat_id = '$obat_id',
    diagnosa = '$diagnosa',
    tindakan = '$tindakan',
    keterangan = '$keterangan'
    WHERE id = '$id'";

  if (mysqli_query($db, $query)) {
    Alert::toast('success', 'Data rekam medis berhasil diperbarui.', 'data_rekam.php');
  } else {
    Alert::toast('error', 'Terjadi kesalahan saat memperbarui data.', 'data_rekam.php');
  }
}

ob_start();
?>

<h1 class="text-2xl font-bold mb-6 text-gray-800 flex items-center gap-2">
  <i class="fa-solid fa-file-pen text-blue-600"></i> Edit Rekam Medis
</h1>

<form method="POST" class="bg-white p-6 rounded-xl shadow-md grid grid-cols-1 md:grid-cols-2 gap-4">
  <div>
    <label class="block mb-1 text-gray-700">Kode Rekam Medis</label>
    <input type="text" name="kode_rm" value="<?= htmlspecialchars($rekam['kode_rm']); ?>" required class="w-full border rounded-lg p-2">
  </div>

  <div>
    <label class="block mb-1 text-gray-700">Tanggal</label>
    <input type="date" name="tanggal" value="<?= htmlspecialchars($rekam['tanggal']); ?>" required class="w-full border rounded-lg p-2">
  </div>

  <div>
    <label class="block mb-1 text-gray-700">Pasien</label>
    <select name="pasien_id" required class="w-full border rounded-lg p-2">
      <option value="">-- Pilih Pasien --</option>
      <?php while ($p = mysqli_fetch_assoc($pasien)) { ?>
        <option value="<?= $p['inc']; ?>" <?= $rekam['pasien_id'] == $p['inc'] ? 'selected' : ''; ?>>
          <?= htmlspecialchars($p['pasien_nama']); ?>
        </option>
      <?php } ?>
    </select>
  </div>

  <div>
    <label class="block mb-1 text-gray-700">Dokter</label>
    <select name="dokter_id" required class="w-full border rounded-lg p-2">
      <option value="">-- Pilih Dokter --</option>
      <?php while ($d = mysqli_fetch_assoc($dokter)) { ?>
        <option value="<?= $d['id']; ?>" <?= $rekam['dokter_id'] == $d['id'] ? 'selected' : ''; ?>>
          <?= htmlspecialchars($d['dokter_nama']); ?>
        </option>
      <?php } ?>
    </select>
  </div>

  <div>
    <label class="block mb-1 text-gray-700">Obat</label>
    <select name="obat_id" required class="w-full border rounded-lg p-2">
      <option value="">-- Pilih Obat --</option>
      <?php while ($o = mysqli_fetch_assoc($obat)) { ?>
        <option value="<?= $o['id']; ?>" <?= $rekam['obat_id'] == $o['id'] ? 'selected' : ''; ?>>
          <?= htmlspecialchars($o['nama_obat']); ?>
        </option>
      <?php } ?>
    </select>
  </div>

  <div class="md:col-span-2">
    <label class="block mb-1 text-gray-700">Diagnosa</label>
    <textarea name="diagnosa" required class="w-full border rounded-lg p-2" rows="2"><?= htmlspecialchars($rekam['diagnosa']); ?></textarea>
  </div>

  <div class="md:col-span-2">
    <label class="block mb-1 text-gray-700">Tindakan</label>
    <textarea name="tindakan" class="w-full border rounded-lg p-2" rows="2"><?= htmlspecialchars($rekam['tindakan']); ?></textarea>
  </div>

  <div class="md:col-span-2">
    <label class="block mb-1 text-gray-700">Keterangan</label>
    <textarea name="keterangan" class="w-full border rounded-lg p-2" rows="2"><?= htmlspecialchars($rekam['keterangan']); ?></textarea>
  </div>

  <div class="md:col-span-2 flex justify-end gap-3 pt-4">
    <a href="data_rekam.php" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg">
      Batal
    </a>
    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
      Simpan Perubahan
    </button>
  </div>
</form>

<?php
$contentFile = tempnam(sys_get_temp_dir(), 'content');
file_put_contents($contentFile, ob_get_clean());
include_once __DIR__ . '/../components/layout.php';
?>
