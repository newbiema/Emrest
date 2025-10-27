<?php
require_once __DIR__ . '/../services/Auth.php';
require_once __DIR__ . '/../services/Database.php';
require_once __DIR__ . '/../services/Helper.php';

$auth = new Auth();
$auth->checkLogin();

$db = (new Database())->connect();
$pageTitle = "Data Rekam Medis";

// Ambil data join dari 3 tabel
$sql = "SELECT rm.id, rm.kode_rm, rm.tanggal, 
               p.pasien_nama, d.dokter_nama, o.nama_obat, 
               rm.diagnosa, rm.tindakan
        FROM rekam_medis rm
        JOIN pasien p ON rm.pasien_id = p.inc
        JOIN dokter d ON rm.dokter_id = d.id
        JOIN obat o ON rm.obat_id = o.id
        ORDER BY rm.id DESC";

$result = mysqli_query($db, $sql);

ob_start();
?>

<div class="flex justify-between items-center mb-6">
  <h1 class="text-3xl font-bold text-gray-800 flex items-center gap-2">
    <i class="fa-solid fa-notes-medical text-blue-600"></i> Data Rekam Medis
  </h1>
  <div class="flex gap-3">
    <a href="<?= Helper::baseUrl('records/tambah_rekam.php') ?>" 
       class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
      <i class="fa-solid fa-plus"></i> Tambah 
    </a>
    <a href="export_pdf.php" target="_blank"
       class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
       <i class="fa-solid fa-file-pdf"></i> Export PDF
    </a>
    <a href="export_excel.php"
        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
        <i class="fa-solid fa-file-excel"></i> Export Excel
    </a>

  </div>
</div>

<div class="bg-white shadow-md rounded-xl overflow-hidden">
  <table class="min-w-full table-auto">
    <thead class="bg-blue-600 text-white">
      <tr>
        <th class="px-4 py-3 text-left">Kode RM</th>
        <th class="px-4 py-3 text-left">Tanggal</th>
        <th class="px-4 py-3 text-left">Pasien</th>
        <th class="px-4 py-3 text-left">Dokter</th>
        <th class="px-4 py-3 text-left">Obat</th>
        <th class="px-4 py-3 text-left">Diagnosa</th>
        <th class="px-4 py-3 text-left">Tindakan</th>
        <th class="px-4 py-3 text-center">Aksi</th>
      </tr>
    </thead>
    <tbody class="text-gray-700">
      <?php if (mysqli_num_rows($result) === 0): ?>
        <tr>
          <td colspan="8" class="text-center py-5 text-gray-500">Belum ada data rekam medis.</td>
        </tr>
      <?php else: ?>
        <?php while ($row = mysqli_fetch_assoc($result)) { ?>
          <tr class="border-b hover:bg-gray-50 transition">
            <td class="px-4 py-3"><?= htmlspecialchars($row['kode_rm']); ?></td>
            <td class="px-4 py-3"><?= htmlspecialchars($row['tanggal']); ?></td>
            <td class="px-4 py-3"><?= htmlspecialchars($row['pasien_nama']); ?></td>
            <td class="px-4 py-3"><?= htmlspecialchars($row['dokter_nama']); ?></td>
            <td class="px-4 py-3"><?= htmlspecialchars($row['nama_obat']); ?></td>
            <td class="px-4 py-3"><?= htmlspecialchars($row['diagnosa']); ?></td>
            <td class="px-4 py-3"><?= htmlspecialchars($row['tindakan']); ?></td>
            <td class="px-4 py-3 text-center space-x-3">
              <a href="<?= Helper::baseUrl('records/detail_rekam.php?id=' . $row['id']) ?>" 
                 class="text-green-600 hover:text-green-800">
                <i class="fa-solid fa-eye"></i>
              </a>
              <a href="<?= Helper::baseUrl('records/edit_rekam.php?id=' . $row['id']) ?>" 
                 class="text-blue-600 hover:text-blue-800">
                <i class="fa-solid fa-pen-to-square"></i>
              </a>
              <a href="<?= Helper::baseUrl('records/hapus_rekam.php?id=' . $row['id']) ?>" 
                 onclick="return confirm('Yakin hapus data ini?')" 
                 class="text-red-600 hover:text-red-800">
                <i class="fa-solid fa-trash"></i>
              </a>
            </td>
          </tr>
        <?php } ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php
$contentFile = tempnam(sys_get_temp_dir(), 'content');
file_put_contents($contentFile, ob_get_clean());
include_once __DIR__ . '/../components/layout.php';
?>
