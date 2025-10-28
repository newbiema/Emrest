<?php
require_once __DIR__ . '/../services/Auth.php';
require_once __DIR__ . '/../services/Database.php';
require_once __DIR__ . '/../services/Alert.php';
require_once __DIR__ . '/../services/Helper.php';

$auth = new Auth();
$auth->checkLogin();
$auth->authorize(['admin']);

$db = (new Database())->connect();

$username = $_GET['username'] ?? '';
if ($username === '') {
  Alert::toast('warning', 'Username tidak valid.', Helper::baseUrl('accounts/data_user.php'));
  exit;
}

// cegah hapus diri sendiri
if (isset($_SESSION['username']) && $_SESSION['username'] === $username) {
  Alert::toast('warning', 'Tidak bisa menghapus akun yang sedang digunakan.', Helper::baseUrl('accounts/data_user.php'));
  exit;
}

$stmt = $db->prepare("DELETE FROM account WHERE username = ?");
$stmt->bind_param('s', $username);

if ($stmt->execute()) {
  Alert::toast('success', 'User berhasil dihapus.', Helper::baseUrl('accounts/data_user.php'));
} else {
  Alert::toast('error', 'Gagal menghapus user.', Helper::baseUrl('accounts/data_user.php'));
}
exit;
