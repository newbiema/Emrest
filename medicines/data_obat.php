<?php
require_once __DIR__ . '/../services/Auth.php';
require_once __DIR__ . '/../services/Database.php';
require_once __DIR__ . '/../services/Helper.php';

$auth = new Auth();
$auth->checkLogin();
$auth->authorize(['admin','apotek','dokter','perawat']);
$db = (new Database())->connect();
$pageTitle = "Data Obat";

$keyword = isset($_GET['search']) ? trim($_GET['search']) : '';

if ($keyword) {
  $sql = "SELECT * FROM obat 
          WHERE nama_obat LIKE '%$keyword%' 
             OR kategori LIKE '%$keyword%'
             OR kode_obat LIKE '%$keyword%'
          ORDER BY id DESC";
} else {
  $sql = "SELECT * FROM obat ORDER BY id DESC";
}
$result = mysqli_query($db, $sql);

ob_start();
?>
<div class="flex justify-between items-center mb-6">
  <h1 class="text-3xl font-bold text-gray-800">
    <i class="fa-solid fa-pills text-blue-600 mr-2"></i>Data Obat
  </h1>
  <div class="flex gap-3">
    <a href="<?= Helper::baseUrl('medicines/tambah_obat.php') ?>" 
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

<form method="GET" class="mb-5 flex gap-2">
  <input type="text" name="search" value="<?= htmlspecialchars($keyword) ?>" 
         placeholder="Cari obat (nama, kategori, kode)..."
         class="w-80 border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-400 focus:outline-none">
  <button type="submit" 
          class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
    <i class="fa-solid fa-search"></i> Cari
  </button>
  <?php if ($keyword): ?>
    <a href="<?= Helper::baseUrl('medicines/data_obat.php') ?>" 
       class="bg-gray-200 text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-300 transition">
      <i class="fa-solid fa-rotate-left"></i> Reset
    </a>
  <?php endif; ?>
</form>

<div class="bg-white shadow-lg rounded-xl overflow-hidden">
  <table class="min-w-full table-auto">
    <thead class="bg-blue-600 text-white">
      <tr>
        <th class="px-4 py-3 text-left">Kode</th>
        <th class="px-4 py-3 text-left">Nama Obat</th>
        <th class="px-4 py-3 text-left">Kategori</th>
        <th class="px-4 py-3 text-left">Stok</th>
        <th class="px-4 py-3 text-left">Harga</th>
        <th class="px-4 py-3 text-center">Aksi</th>
      </tr>
    </thead>
    <tbody class="text-gray-700">
      <?php if (mysqli_num_rows($result) === 0): ?>
        <tr>
          <td colspan="6" class="text-center py-6 text-gray-500">Tidak ada data obat.</td>
        </tr>
      <?php else: while ($row = mysqli_fetch_assoc($result)): ?>
        <tr class="border-b hover:bg-gray-50 transition">
          <td class="px-4 py-3"><?= htmlspecialchars($row['kode_obat']); ?></td>
          <td class="px-4 py-3"><?= htmlspecialchars($row['nama_obat']); ?></td>
          <td class="px-4 py-3"><?= htmlspecialchars($row['kategori']); ?></td>
          <td class="px-4 py-3"><?= htmlspecialchars($row['stok']); ?></td>
          <td class="px-4 py-3">Rp <?= number_format($row['harga'], 0, ',', '.'); ?></td>
          <td class="px-4 py-3 text-center space-x-3">
            <a href="<?= Helper::baseUrl('medicines/edit_obat.php?id=' . $row['id']) ?>" 
               class="text-blue-600 hover:text-blue-800">
              <i class="fa-solid fa-pen-to-square"></i>
            </a>
            <button onclick="hapusObat(<?= $row['id']; ?>)" 
                    class="text-red-600 hover:text-red-800">
              <i class="fa-solid fa-trash"></i>
            </button>
          </td>
        </tr>
      <?php endwhile; endif; ?>
    </tbody>
  </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function hapusObat(id) {
  Swal.fire({
    title: 'Yakin ingin menghapus?',
    text: "Data obat akan dihapus permanen!",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#2563eb',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Ya, hapus!'
  }).then((result) => {
    if (result.isConfirmed) {
      window.location.href = 'hapus_obat.php?id=' + id;
    }
  });
}
</script>
<?php
$contentFile = tempnam(sys_get_temp_dir(), 'content');
file_put_contents($contentFile, ob_get_clean());
include_once __DIR__ . '/../components/layout.php';
