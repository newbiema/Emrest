<?php
require_once __DIR__ . '/../services/Auth.php';
require_once __DIR__ . '/../services/Database.php';
require_once __DIR__ . '/../services/Helper.php'; // penting!

$auth = new Auth();
$auth->checkLogin();

$db = (new Database())->connect();

$result = mysqli_query($db, "SELECT * FROM pasien ORDER BY inc DESC");
$pageTitle = "Data Pasien";

ob_start();
?>
<div class="flex justify-between items-center mb-6">
  <h1 class="text-3xl font-bold text-gray-800">
    <i class="fa-solid fa-users text-blue-600 mr-2"></i>Data Pasien
  </h1>
  <a href="<?= Helper::baseUrl('patients/tambah_pasien.php') ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
    <i class="fa-solid fa-plus"></i> Tambah Pasien
  </a>
</div>

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
          <a href="<?= Helper::baseUrl('patients/edit_pasien.php?id=' . $row['inc']) ?>" class="text-blue-600 hover:text-blue-800">
            <i class="fa-solid fa-pen-to-square"></i>
          </a>
          <button onclick="hapusPasien(<?= $row['inc']; ?>)" class="text-red-600 hover:text-red-800">
            <i class="fa-solid fa-trash"></i>
          </button>
        </td>
      </tr>
      <?php } ?>
    </tbody>
  </table>
</div>

<!-- SweetAlert untuk hapus -->
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
