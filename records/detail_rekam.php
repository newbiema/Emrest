<?php
require_once __DIR__ . '/../services/Database.php';
require_once __DIR__ . '/../services/Auth.php';
require_once __DIR__ . '/../services/Helper.php';

$auth = new Auth();
$auth->checkLogin();

$db = (new Database())->connect();
$id = $_GET['id'];

$sql = "SELECT rm.*, 
        p.pasien_nama, p.alamat AS alamat_pasien, p.jenis_kelamin AS jk_pasien,
        d.dokter_nama, d.spesialis,
        o.nama_obat, o.kategori, o.dosis
        FROM rekam_medis rm
        JOIN pasien p ON rm.pasien_id = p.inc
        JOIN dokter d ON rm.dokter_id = d.id
        JOIN obat o ON rm.obat_id = o.id
        WHERE rm.id = $id";

$data = mysqli_fetch_assoc(mysqli_query($db, $sql));
$pageTitle = "Detail Rekam Medis";
ob_start();
?>

<div class="bg-white p-6 rounded-xl shadow-md">
  <h1 class="text-2xl font-bold mb-6 text-gray-800 flex items-center gap-2">
    <i class="fa-solid fa-file-medical text-blue-600"></i> Detail Rekam Medis
  </h1>

  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
      <h2 class="font-semibold text-gray-700 mb-1">Kode RM:</h2>
      <p><?= htmlspecialchars($data['kode_rm']); ?></p>
      <h2 class="font-semibold text-gray-700 mt-3 mb-1">Tanggal:</h2>
      <p><?= htmlspecialchars($data['tanggal']); ?></p>
      <h2 class="font-semibold text-gray-700 mt-3 mb-1">Pasien:</h2>
      <p><?= htmlspecialchars($data['pasien_nama']); ?> (<?= htmlspecialchars($data['jk_pasien']); ?>)</p>
      <p><?= htmlspecialchars($data['alamat_pasien']); ?></p>
    </div>

    <div>
      <h2 class="font-semibold text-gray-700 mb-1">Dokter:</h2>
      <p><?= htmlspecialchars($data['dokter_nama']); ?> (<?= htmlspecialchars($data['spesialis']); ?>)</p>
      <h2 class="font-semibold text-gray-700 mt-3 mb-1">Obat:</h2>
      <p><?= htmlspecialchars($data['nama_obat']); ?> (<?= htmlspecialchars($data['kategori']); ?>)</p>
      <p><?= htmlspecialchars($data['dosis']); ?></p>
    </div>
  </div>

  <div class="mt-6">
    <h2 class="font-semibold text-gray-700 mb-1">Diagnosa:</h2>
    <p><?= nl2br(htmlspecialchars($data['diagnosa'])); ?></p>
    <h2 class="font-semibold text-gray-700 mt-3 mb-1">Tindakan:</h2>
    <p><?= nl2br(htmlspecialchars($data['tindakan'])); ?></p>
    <h2 class="font-semibold text-gray-700 mt-3 mb-1">Keterangan:</h2>
    <p><?= nl2br(htmlspecialchars($data['keterangan'])); ?></p>
  </div>

  <div class="mt-6 flex justify-end gap-3">
    <a href="export_detail_pdf.php?id=<?= $id ?>" target="_blank" 
       class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
      <i class="fa-solid fa-file-pdf"></i> Export PDF
    </a>
    <a href="data_rekam.php" 
       class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg">Kembali</a>
  </div>
</div>

<?php
$contentFile = tempnam(sys_get_temp_dir(), 'content');
file_put_contents($contentFile, ob_get_clean());
include_once __DIR__ . '/../components/layout.php';
?>
