<?php
require_once __DIR__ . '/../services/Auth.php';
require_once __DIR__ . '/../services/Database.php';
require_once __DIR__ . '/../services/Helper.php';

$auth = new Auth();
$auth->checkLogin();
$auth->authorize(['admin','dokter']); // list dokter boleh admin & dokter

$db = (new Database())->connect();

// Search (escape biar aman)
$keyword = isset($_GET['search']) ? trim($_GET['search']) : '';
if ($keyword !== '') {
  $kw  = mysqli_real_escape_string($db, $keyword);
  $sql = "SELECT * FROM dokter 
          WHERE dokter_id   LIKE '%$kw%' 
             OR dokter_nama LIKE '%$kw%' 
             OR spesialis   LIKE '%$kw%' 
          ORDER BY id DESC";
} else {
  $sql = "SELECT * FROM dokter ORDER BY id DESC";
}

$result    = mysqli_query($db, $sql);
$pageTitle = "Data Dokter";

ob_start();
?>

<div class="flex justify-between items-center mb-6">
  <h1 class="text-3xl font-bold text-gray-800">
    <i class="fa-solid fa-user-doctor text-blue-600 mr-2"></i>Data Dokter
  </h1>

  <div class="flex gap-2">
    <!-- tambah: admin only -->
    <a href="<?= Helper::baseUrl('doctors/tambah_dokter.php') ?>" 
       data-role="doctor.add"
       class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
      <i class="fa-solid fa-plus"></i> Tambah
    </a>

    <!-- export: admin & dokter -->
    <a href="export_pdf.php" target="_blank"
       data-role="doctor.export"
       class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
      <i class="fa-solid fa-file-pdf"></i> Export PDF
    </a>
    <a href="export_excel.php" target="_blank"
       data-role="doctor.export"
       class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
      <i class="fa-solid fa-file-excel"></i> Export Excel
    </a>
  </div>
</div>

<!-- 🔍 Search Bar -->
<form method="GET" class="mb-5 flex gap-2">
  <input type="text" name="search" value="<?= htmlspecialchars($keyword) ?>" 
         placeholder="Cari dokter (ID, Nama, Spesialis)..." 
         class="w-80 border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-400 focus:outline-none">
  <button type="submit" 
          class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
    <i class="fa-solid fa-search"></i> Cari
  </button>
  <?php if ($keyword): ?>
    <a href="<?= Helper::baseUrl('doctors/data_dokter.php') ?>" 
       class="bg-gray-200 text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-300 transition">
      <i class="fa-solid fa-rotate-left"></i> Reset
    </a>
  <?php endif; ?>
</form>

<!-- 🩺 Tabel Data Dokter -->
<div class="bg-white shadow-lg rounded-xl overflow-hidden">
  <table class="min-w-full table-auto">
    <thead class="bg-blue-600 text-white">
      <tr>
        <th class="px-4 py-3 text-left">ID</th>
        <th class="px-4 py-3 text-left">Nama</th>
        <th class="px-4 py-3 text-left">Spesialis</th>
        <th class="px-4 py-3 text-left">JK</th>
        <th class="px-4 py-3 text-left">Tanggal Lahir</th>
        <th class="px-4 py-3 text-left">Alamat</th>
        <th class="px-4 py-3 text-center">Umur</th>
        <th class="px-4 py-3 text-left">No. Telp</th>
        <th class="px-4 py-3 text-center">Aksi</th>
      </tr>
    </thead>
    <tbody class="text-gray-700">
      <?php if (mysqli_num_rows($result) === 0): ?>
        <tr>
          <td colspan="9" class="text-center py-6 text-gray-500">
            <i class="fa-solid fa-circle-exclamation text-yellow-500 mr-2"></i>
            Tidak ada data dokter ditemukan.
          </td>
        </tr>
      <?php else: while ($row = mysqli_fetch_assoc($result)) { ?>
        <tr class="border-b hover:bg-gray-50 transition">
          <td class="px-4 py-3"><?= htmlspecialchars($row['dokter_id']); ?></td>
          <td class="px-4 py-3 font-medium"><?= htmlspecialchars($row['dokter_nama']); ?></td>
          <td class="px-4 py-3"><?= htmlspecialchars($row['spesialis']); ?></td>
          <td class="px-4 py-3"><?= htmlspecialchars($row['jenis_kelamin']); ?></td>
          <td class="px-4 py-3"><?= htmlspecialchars($row['tanggal_lahir']); ?></td>
          <td class="px-4 py-3"><?= htmlspecialchars($row['alamat']); ?></td>
          <td class="px-4 py-3 text-center"><?= htmlspecialchars($row['umur']); ?></td>
          <td class="px-4 py-3"><?= htmlspecialchars($row['no_telp']); ?></td>
          <td class="px-4 py-3 text-center space-x-2">
            <!-- edit: admin only -->
            <a href="<?= Helper::baseUrl('doctors/edit_dokter.php?id=' . $row['id']) ?>" 
               data-role="doctor.edit"
               class="text-blue-600 hover:text-blue-800">
              <i class="fa-solid fa-pen-to-square"></i>
            </a>
            <!-- delete: admin only -->
            <button onclick="hapusDokter(<?= (int)$row['id']; ?>)" 
                    data-role="doctor.delete"
                    class="text-red-600 hover:text-red-800">
              <i class="fa-solid fa-trash"></i>
            </button>
          </td>
        </tr>
      <?php } endif; ?>
    </tbody>
  </table>
</div>

<!-- 🧾 SweetAlert Delete -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function hapusDokter(id) {
  Swal.fire({
    title: 'Yakin ingin menghapus?',
    text: "Data dokter akan dihapus permanen!",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#2563eb',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Ya, hapus!',
    cancelButtonText: 'Batal'
  }).then((result) => {
    if (result.isConfirmed) {
      window.location.href = '<?= Helper::baseUrl("doctors/hapus_dokter.php?id=") ?>' + id;
    }
  });
}
</script>

<?php
$contentFile = tempnam(sys_get_temp_dir(), 'content');
file_put_contents($contentFile, ob_get_clean());
include_once __DIR__ . '/../components/layout.php';
