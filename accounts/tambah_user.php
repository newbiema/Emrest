<?php
require_once __DIR__ . '/../services/Auth.php';
require_once __DIR__ . '/../services/Database.php';
require_once __DIR__ . '/../services/Alert.php';
require_once __DIR__ . '/../services/Helper.php';

$auth = new Auth();
$auth->checkLogin();
$auth->authorize(['admin']);

$db = (new Database())->connect();
$pageTitle = "Tambah User";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username'] ?? '');
  $nama     = trim($_POST['nama'] ?? '');
  $level    = trim($_POST['level'] ?? '');
  $password = $_POST['password'] ?? '';
  $confirm  = $_POST['confirm'] ?? '';

  // Validasi sederhana
  if ($username === '' || $nama === '' || $level === '' || $password === '') {
    Alert::toast('warning', 'Semua field wajib diisi!', 'tambah_user.php');
    exit;
  }
  if ($password !== $confirm) {
    Alert::toast('warning', 'Konfirmasi password tidak cocok.', 'tambah_user.php');
    exit;
  }

  // cek duplikasi username
  $stmt = $db->prepare("SELECT username FROM account WHERE username = ?");
  $stmt->bind_param('s', $username);
  $stmt->execute();
  $stmt->store_result();
  if ($stmt->num_rows > 0) {
    Alert::toast('warning', 'Username sudah digunakan.', 'tambah_user.php');
    exit;
  }
  $stmt->close();

  // insert
  $hash = password_hash($password, PASSWORD_DEFAULT);
  $stmt = $db->prepare("INSERT INTO account (username, password, nama, level) VALUES (?,?,?,?)");
  $stmt->bind_param('ssss', $username, $hash, $nama, $level);
  if ($stmt->execute()) {
    Alert::toast('success', 'User berhasil ditambahkan.', Helper::baseUrl('accounts/data_user.php'));
  } else {
    Alert::toast('error', 'Gagal menambahkan user.', 'tambah_user.php');
  }
  exit;
}

ob_start();
?>
<h1 class="text-2xl font-bold mb-6 text-gray-800 flex items-center gap-2">
  <i class="fa-solid fa-user-plus text-blue-600"></i> Tambah User
</h1>

<form method="POST" class="bg-white p-6 rounded-xl shadow-md grid grid-cols-1 md:grid-cols-2 gap-4">
  <div>
    <label class="block mb-1 text-gray-700">Username</label>
    <input type="text" name="username" required class="w-full border rounded-lg p-2">
  </div>

  <div>
    <label class="block mb-1 text-gray-700">Nama</label>
    <input type="text" name="nama" required class="w-full border rounded-lg p-2">
  </div>

  <div>
    <label class="block mb-1 text-gray-700">Level</label>
    <select name="level" required class="w-full border rounded-lg p-2">
      <option value="">-- Pilih --</option>
      <option value="admin">Admin</option>
      <option value="dokter">Dokter</option>
      <option value="perawat">Perawat</option>
      <option value="apotek">Apotek</option>
      <option value="loket">Loket</option>
      <option value="user">User</option>

    </select>
  </div>

  <div>
    <label class="block mb-1 text-gray-700">Password</label>
    <input type="password" name="password" required class="w-full border rounded-lg p-2">
  </div>

  <div>
    <label class="block mb-1 text-gray-700">Konfirmasi Password</label>
    <input type="password" name="confirm" required class="w-full border rounded-lg p-2">
  </div>

  <div class="md:col-span-2 flex justify-end gap-3 pt-2">
    <a href="<?= Helper::baseUrl('accounts/data_user.php') ?>" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg">Batal</a>
    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Simpan</button>
  </div>
</form>
<?php
$contentFile = tempnam(sys_get_temp_dir(), 'content'); file_put_contents($contentFile, ob_get_clean());
include_once __DIR__ . '/../components/layout.php';
