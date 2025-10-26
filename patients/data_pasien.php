<?php
require_once __DIR__ . '/../services/Auth.php';
require_once __DIR__ . '/../services/Database.php';
require_once __DIR__ . '/../services/Helper.php';

$auth = new Auth();
$auth->checkLogin();

$db = (new Database())->connect();

$keyword = isset($_GET['search']) ? trim($_GET['search']) : '';

if ($keyword) {
  $sql = "SELECT * FROM pasien 
          WHERE pasien_id LIKE '%$keyword%' 
             OR pasien_nama LIKE '%$keyword%' 
             OR nama_kk LIKE '%$keyword%'
          ORDER BY inc DESC";
} else {
  $sql = "SELECT * FROM pasien ORDER BY inc DESC";
}

$result = mysqli_query($db, $sql);
$pageTitle = "Data Pasien";

ob_start();
?>

<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-6">
  <div>
    <h1 class="text-3xl font-bold text-gray-800 flex items-center gap-2">
      <i class="fa-solid fa-users text-blue-600"></i> Data Pasien
    </h1>
    <p class="text-sm text-gray-500 mt-1">Kelola data pasien rumah sakit dengan mudah dan aman</p>
  </div>

  <div class="flex flex-wrap items-center gap-2">
    <a href="<?= Helper::baseUrl('patients/tambah_pasien.php') ?>"
      class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition">
      <i class="fa-solid fa-plus"></i>
      <span>Tambah</span>
    </a>

    <a href="export_pdf.php" target="_blank"
      class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition">
      <i class="fa-solid fa-file-pdf"></i>
      <span>PDF</span>
    </a>

    <a href="export_excel.php" target="_blank"
      class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition">
      <i class="fa-solid fa-file-excel"></i>
      <span>Excel</span>
    </a>
  </div>
</div>


<form method="GET" class="mb-6 flex flex-wrap items-center gap-2">
  <div class="relative">
    <i class="fa-solid fa-search absolute left-3 top-3 text-gray-400"></i>
    <input type="text" name="search" value="<?= htmlspecialchars($keyword) ?>"
      placeholder="Cari pasien (ID, Nama, atau KK)..."
      class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-400 focus:outline-none w-72 md:w-96">
  </div>

  <button type="submit"
    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition">
    <i class="fa-solid fa-magnifying-glass"></i>
    <span>Cari</span>
  </button>

  <?php if ($keyword): ?>
    <a href="<?= Helper::baseUrl('patients/data_pasien.php') ?>"
      class="bg-gray-200 text-gray-800 hover:bg-gray-300 px-4 py-2 rounded-lg flex items-center gap-2 transition">
      <i class="fa-solid fa-rotate-left"></i>
      <span>Reset</span>
    </a>
  <?php endif; ?>
</form>


<!-- Tabel Data -->
<div class="bg-white shadow-lg rounded-xl overflow-hidden">
  <table class="min-w-full table-auto">
    <thead class="bg-blue-600 text-white">
      <tr>
        <th class="px-4 py-3 text-left">ID</th>
        <th class="px-4 py-3 text-left">Nama</th>
        <th class="px-4 py-3 text-left">Tanggal Lahir</th>
        <th class="px-4 py-3 text-left">Alamat</th>
        <th class="px-4 py-3 text-left">JK</th>
        <th class="px-4 py-3 text-center">Umur</th>
        <th class="px-4 py-3 text-left">Nama KK</th>
        <th class="px-4 py-3 text-center">Aksi</th>
      </tr>
    </thead>
    <tbody class="text-gray-700">
      <?php if (mysqli_num_rows($result) === 0): ?>
        <tr>
          <td colspan="8" class="text-center py-6 text-gray-500">
            <i class="fa-solid fa-circle-exclamation text-yellow-500 mr-2"></i>
            Tidak ada data pasien ditemukan.
          </td>
        </tr>
      <?php else: ?>
        <?php while ($row = mysqli_fetch_assoc($result)) { ?>
          <tr class="border-b hover:bg-gray-50 transition">
            <td class="px-4 py-3"><?= htmlspecialchars($row['pasien_id']); ?></td>
            <td class="px-4 py-3 font-medium"><?= htmlspecialchars($row['pasien_nama']); ?></td>
            <td class="px-4 py-3"><?= htmlspecialchars($row['tanggal_lahir']); ?></td>
            <td class="px-4 py-3"><?= htmlspecialchars($row['alamat']); ?></td>
            <td class="px-4 py-3"><?= htmlspecialchars($row['jenis_kelamin']); ?></td>
            <td class="px-4 py-3 text-center"><?= htmlspecialchars($row['umur']); ?></td>
            <td class="px-4 py-3"><?= htmlspecialchars($row['nama_kk']); ?></td>
            <td class="px-4 py-3 text-center space-x-3">
              <a href="<?= Helper::baseUrl('patients/edit_pasien.php?id=' . $row['inc']) ?>" 
                 class="text-blue-600 hover:text-blue-800">
                <i class="fa-solid fa-pen-to-square"></i>
              </a>
              <button onclick="hapusPasien(<?= $row['inc']; ?>)" 
                      class="text-red-600 hover:text-red-800">
                <i class="fa-solid fa-trash"></i>
              </button>
            </td>
          </tr>
        <?php } ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- SweetAlert Delete -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function hapusPasien(id) {
  Swal.fire({
    title: 'Yakin ingin menghapus?',
    text: "Data pasien akan dihapus permanen!",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#2563eb',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Ya, hapus!'
  }).then((result) => {
    if (result.isConfirmed) {
      window.location.href = '<?= Helper::baseUrl("patients/hapus_pasien.php?id=") ?>' + id;
    }
  });
}
</script>

<?php
$contentFile = tempnam(sys_get_temp_dir(), 'content');
file_put_contents($contentFile, ob_get_clean());
include_once __DIR__ . '/../components/layout.php';
