<?php
require_once __DIR__ . '/../services/Auth.php';
require_once __DIR__ . '/../services/Database.php';
require_once __DIR__ . '/../services/Helper.php';

$auth = new Auth();
$auth->checkLogin();
$auth->authorize(['admin']); // hanya admin

$db = (new Database())->connect();
$pageTitle = "Manajemen User";

// Search aman
$keyword = isset($_GET['search']) ? trim($_GET['search']) : '';
if ($keyword !== '') {
  $kw  = mysqli_real_escape_string($db, $keyword);
  $sql = "SELECT username, nama, level FROM account
          WHERE username LIKE '%$kw%' OR nama LIKE '%$kw%' OR level LIKE '%$kw%'
          ORDER BY username ASC";
} else {
  $sql = "SELECT username, nama, level FROM account ORDER BY username ASC";
}
$result = mysqli_query($db, $sql);

ob_start();
?>

<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-6">
  <div>
    <h1 class="text-3xl font-bold text-gray-800 flex items-center gap-2">
      <i class="fa-solid fa-users-gear text-blue-600"></i> Manajemen User
    </h1>
    <p class="text-sm text-gray-500 mt-1">Tambah, ubah, dan hapus akun pengguna sistem</p>
  </div>

  <div class="flex flex-wrap items-center gap-2">
    <a href="<?= Helper::baseUrl('accounts/tambah_user.php') ?>"
       data-role="user.manage"
       class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
      <i class="fa-solid fa-user-plus"></i> Tambah User
    </a>
  </div>
</div>

<!-- Search -->
<form method="GET" class="mb-6 flex flex-wrap items-center gap-2">
  <div class="relative">
    <i class="fa-solid fa-search absolute left-3 top-3 text-gray-400"></i>
    <input type="text" name="search" value="<?= htmlspecialchars($keyword) ?>"
      placeholder="Cari (username, nama, level)"
      class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-400 focus:outline-none w-72 md:w-96">
  </div>
  <button type="submit"
          class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
    <i class="fa-solid fa-magnifying-glass"></i> Cari
  </button>
  <?php if ($keyword): ?>
    <a href="<?= Helper::baseUrl('accounts/data_user.php') ?>"
       class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg flex items-center gap-2">
      <i class="fa-solid fa-rotate-left"></i> Reset
    </a>
  <?php endif; ?>
</form>

<div class="bg-white shadow-lg rounded-xl overflow-hidden">
  <table class="min-w-full table-auto">
    <thead class="bg-blue-600 text-white">
      <tr>
        <th class="px-4 py-3 text-left">Username</th>
        <th class="px-4 py-3 text-left">Nama</th>
        <th class="px-4 py-3 text-left">Level</th>
        <th class="px-4 py-3 text-center">Aksi</th>
      </tr>
    </thead>
    <tbody class="text-gray-700">
      <?php if (mysqli_num_rows($result) === 0): ?>
        <tr>
          <td colspan="4" class="text-center py-6 text-gray-500">
            <i class="fa-solid fa-circle-exclamation text-yellow-500 mr-2"></i>
            Tidak ada user ditemukan.
          </td>
        </tr>
      <?php else: while ($row = mysqli_fetch_assoc($result)) { ?>
        <tr class="border-b hover:bg-gray-50 transition">
          <td class="px-4 py-3 font-medium"><?= htmlspecialchars($row['username']); ?></td>
          <td class="px-4 py-3"><?= htmlspecialchars($row['nama']); ?></td>
          <td class="px-4 py-3">
            <span class="inline-block px-2 py-1 rounded text-xs
              <?= $row['level']==='admin' ? 'bg-red-100 text-red-700' :
                  ($row['level']==='dokter' ? 'bg-green-100 text-green-700' :
                  ($row['level']==='perawat' ? 'bg-blue-100 text-blue-700' :
                  ($row['level']==='apotek' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-700'))) ?>">
              <?= htmlspecialchars(ucfirst($row['level'])); ?>
            </span>
          </td>
          <td class="px-4 py-3 text-center space-x-2">
            <a href="<?= Helper::baseUrl('accounts/edit_user.php?username=' . urlencode($row['username'])) ?>"
               data-role="user.manage"
               class="text-blue-600 hover:text-blue-800" title="Edit">
              <i class="fa-solid fa-pen-to-square"></i>
            </a>
            <?php if ($row['username'] !== ($_SESSION['username'] ?? '')): ?>
              <button type="button"
                      data-role="user.manage"
                      onclick="hapusUser('<?= htmlspecialchars($row['username']) ?>')"
                      class="text-red-600 hover:text-red-800" title="Hapus">
                <i class="fa-solid fa-trash"></i>
              </button>
            <?php else: ?>
              <span class="text-gray-400 text-sm italic">sedang login</span>
            <?php endif; ?>
          </td>
        </tr>
      <?php } endif; ?>
    </tbody>
  </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function hapusUser(username){
  Swal.fire({
    title: 'Hapus user?',
    text: 'Tindakan ini tidak bisa dibatalkan.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#2563eb',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Ya, hapus',
    cancelButtonText: 'Batal'
  }).then(res=>{
    if(res.isConfirmed){
      window.location.href = '<?= Helper::baseUrl("accounts/hapus_user.php?username=") ?>' + encodeURIComponent(username);
    }
  });
}
</script>

<?php
$contentFile = tempnam(sys_get_temp_dir(), 'content');
file_put_contents($contentFile, ob_get_clean());
include_once __DIR__ . '/../components/layout.php';
