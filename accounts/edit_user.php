<?php
require_once __DIR__ . '/../services/Auth.php';
require_once __DIR__ . '/../services/Database.php';
require_once __DIR__ . '/../services/Alert.php';
require_once __DIR__ . '/../services/Helper.php';

$auth = new Auth();
$auth->checkLogin();
$auth->authorize(['admin']);

$db = (new Database())->connect();
$pageTitle = "Edit User";

$u = $_GET['username'] ?? '';
if ($u === '') {
  Alert::toast('warning', 'Username tidak ditemukan.', Helper::baseUrl('accounts/data_user.php'));
  exit;
}

// ambil data user
$stmt = $db->prepare("SELECT username, nama, level FROM account WHERE username = ?");
$stmt->bind_param('s', $u);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
  Alert::toast('error', 'User tidak ada.', Helper::baseUrl('accounts/data_user.php'));
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nama  = trim($_POST['nama'] ?? '');
  $level = trim($_POST['level'] ?? '');
  $pass  = $_POST['password'] ?? '';
  $conf  = $_POST['confirm'] ?? '';

  if ($nama === '' || $level === '') {
    Alert::toast('warning', 'Nama & level wajib diisi.', 'edit_user.php?username='.urlencode($u));
    exit;
  }

  if ($pass !== '') {
    if ($pass !== $conf) {
      Alert::toast('warning', 'Konfirmasi password tidak cocok.', 'edit_user.php?username='.urlencode($u));
      exit;
    }
    $hash = password_hash($pass, PASSWORD_DEFAULT);
    $stmt = $db->prepare("UPDATE account SET nama=?, level=?, password=? WHERE username=?");
    $stmt->bind_param('ssss', $nama, $level, $hash, $u);
  } else {
    $stmt = $db->prepare("UPDATE account SET nama=?, level=? WHERE username=?");
    $stmt->bind_param('sss', $nama, $level, $u);
  }

  if ($stmt->execute()) {
    Alert::toast('success', 'User berhasil diperbarui.', Helper::baseUrl('accounts/data_user.php'));
  } else {
    Alert::toast('error', 'Gagal memperbarui user.', 'edit_user.php?username='.urlencode($u));
  }
  exit;
}

ob_start();
?>
<h1 class="text-2xl font-bold mb-6 text-gray-800 flex items-center gap-2">
  <i class="fa-solid fa-user-pen text-blue-600"></i> Edit User
</h1>

<form method="POST" class="bg-white p-6 rounded-xl shadow-md grid grid-cols-1 md:grid-cols-2 gap-4">
  <div>
    <label class="block mb-1 text-gray-700">Username</label>
    <input type="text" value="<?= htmlspecialchars($user['username']) ?>" class="w-full border rounded-lg p-2 bg-gray-100" disabled>
  </div>

  <div>
    <label class="block mb-1 text-gray-700">Nama</label>
    <input type="text" name="nama" value="<?= htmlspecialchars($user['nama']) ?>" required class="w-full border rounded-lg p-2">
  </div>

  <div>
    <label class="block mb-1 text-gray-700">Level</label>
    <select name="level" required class="w-full border rounded-lg p-2">
      <?php
        $levels = ['admin','dokter','perawat','apotek','loket'];
        foreach ($levels as $lv) {
          $sel = $user['level']===$lv ? 'selected' : '';
          echo "<option value=\"$lv\" $sel>".ucfirst($lv)."</option>";
        }
      ?>
    </select>
  </div>

  <div class="md:col-span-2 border-t pt-4">
    <p class="text-sm text-gray-500 mb-2">Kosongkan password jika tidak ingin mengubah.</p>
    <div class="grid md:grid-cols-2 gap-4">
      <div>
        <label class="block mb-1 text-gray-700">Password Baru</label>
        <input type="password" name="password" class="w-full border rounded-lg p-2">
      </div>
      <div>
        <label class="block mb-1 text-gray-700">Konfirmasi Password</label>
        <input type="password" name="confirm" class="w-full border rounded-lg p-2">
      </div>
    </div>
  </div>

  <div class="md:col-span-2 flex justify-end gap-3 pt-2">
    <a href="<?= Helper::baseUrl('accounts/data_user.php') ?>" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg">Batal</a>
    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Simpan</button>
  </div>
</form>
<?php
$contentFile = tempnam(sys_get_temp_dir(), 'content'); file_put_contents($contentFile, ob_get_clean());
include_once __DIR__ . '/../components/layout.php';
