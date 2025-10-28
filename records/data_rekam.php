<?php
require_once __DIR__ . '/../services/Auth.php';
require_once __DIR__ . '/../services/Database.php';
require_once __DIR__ . '/../services/Helper.php';

$auth = new Auth();
$auth->checkLogin();
$auth->authorize(['admin','dokter','perawat']);

$db = (new Database())->connect();
$pageTitle = "Data Rekam Medis";

// --- Search aman (escape) ---
$keyword = isset($_GET['search']) ? trim($_GET['search']) : '';
if ($keyword !== '') {
  $kw  = mysqli_real_escape_string($db, $keyword);
  $where = "WHERE rm.kode_rm LIKE '%$kw%' 
            OR p.pasien_nama LIKE '%$kw%'
            OR d.dokter_nama LIKE '%$kw%'
            OR rm.diagnosa LIKE '%$kw%'";
} else {
  $where = "";
}

// Ambil data join 4 tabel
$sql = "SELECT rm.id, rm.kode_rm, rm.tanggal, 
               p.pasien_nama, d.dokter_nama, o.nama_obat, 
               rm.diagnosa, rm.tindakan
        FROM rekam_medis rm
        JOIN pasien p ON rm.pasien_id = p.inc       -- inc = PK pasien
        JOIN dokter d ON rm.dokter_id = d.id        -- id = PK dokter
        JOIN obat   o ON rm.obat_id   = o.id        -- id = PK obat
        $where
        ORDER BY rm.id DESC";

$result = mysqli_query($db, $sql);

ob_start();
?>

<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-6">
  <div>
    <h1 class="text-3xl font-bold text-gray-800 flex items-center gap-2">
      <i class="fa-solid fa-notes-medical text-blue-600"></i> Data Rekam Medis
    </h1>
    <p class="text-sm text-gray-500 mt-1">Riwayat pemeriksaan pasien</p>
  </div>

  <div class="flex flex-wrap items-center gap-2">
    <!-- tambah: admin/dokter/perawat -->
    <a href="<?= Helper::baseUrl('records/tambah_rekam.php') ?>" 
       data-role="record.add"
       class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
      <i class="fa-solid fa-plus"></i> Tambah 
    </a>
    <!-- export: admin & dokter -->
    <a href="export_pdf.php" target="_blank"
       data-role="record.export"
       class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
       <i class="fa-solid fa-file-pdf"></i> Export PDF
    </a>
    <a href="export_excel.php"
       data-role="record.export"
       class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
        <i class="fa-solid fa-file-excel"></i> Export Excel
    </a>
  </div>
</div>

<!-- 🔍 Search -->
<form method="GET" class="mb-6 flex flex-wrap items-center gap-2">
  <div class="relative">
    <i class="fa-solid fa-search absolute left-3 top-3 text-gray-400"></i>
    <input type="text" name="search" value="<?= htmlspecialchars($keyword) ?>"
      placeholder="Cari (Kode RM, Pasien, Dokter, Diagnosa)..."
      class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-400 focus:outline-none w-80 md:w-96">
  </div>
  <button type="submit"
          class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
    <i class="fa-solid fa-magnifying-glass"></i> Cari
  </button>
  <?php if ($keyword): ?>
    <a href="<?= Helper::baseUrl('records/data_rekam.php') ?>"
       class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg flex items-center gap-2">
      <i class="fa-solid fa-rotate-left"></i> Reset
    </a>
  <?php endif; ?>
</form>

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
      <?php else: while ($row = mysqli_fetch_assoc($result)) { ?>
        <tr class="border-b hover:bg-gray-50 transition">
          <td class="px-4 py-3"><?= htmlspecialchars($row['kode_rm']); ?></td>
          <td class="px-4 py-3"><?= htmlspecialchars($row['tanggal']); ?></td>
          <td class="px-4 py-3"><?= htmlspecialchars($row['pasien_nama']); ?></td>
          <td class="px-4 py-3"><?= htmlspecialchars($row['dokter_nama']); ?></td>
          <td class="px-4 py-3"><?= htmlspecialchars($row['nama_obat']); ?></td>
          <td class="px-4 py-3"><?= htmlspecialchars($row['diagnosa']); ?></td>
          <td class="px-4 py-3"><?= htmlspecialchars($row['tindakan']); ?></td>
          <td class="px-4 py-3 text-center space-x-3">
            <!-- detail selalu boleh (karena sudah authorize di atas) -->
            <a href="<?= Helper::baseUrl('records/detail_rekam.php?id=' . $row['id']) ?>" 
               class="text-green-600 hover:text-green-800" title="Detail">
              <i class="fa-solid fa-eye"></i>
            </a>
            <!-- edit: admin & dokter -->
            <a href="<?= Helper::baseUrl('records/edit_rekam.php?id=' . $row['id']) ?>" 
               data-role="record.edit"
               class="text-blue-600 hover:text-blue-800" title="Edit">
              <i class="fa-solid fa-pen-to-square"></i>
            </a>
            <!-- delete: admin only -->
            <button type="button"
                    data-role="record.delete"
                    onclick="hapusRekam(<?= (int)$row['id']; ?>)" 
                    class="text-red-600 hover:text-red-800" title="Hapus">
              <i class="fa-solid fa-trash"></i>
            </button>
          </td>
        </tr>
      <?php } endif; ?>
    </tbody>
  </table>
</div>

<!-- SweetAlert Delete -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function hapusRekam(id) {
  Swal.fire({
    title: 'Yakin ingin menghapus?',
    text: 'Data rekam medis akan dihapus permanen!',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#2563eb',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Ya, hapus!',
    cancelButtonText: 'Batal'
  }).then((result) => {
    if (result.isConfirmed) {
      window.location.href = '<?= Helper::baseUrl("records/hapus_rekam.php?id=") ?>' + id;
    }
  });
}
</script>

<?php
$contentFile = tempnam(sys_get_temp_dir(), 'content');
file_put_contents($contentFile, ob_get_clean());
include_once __DIR__ . '/../components/layout.php';
