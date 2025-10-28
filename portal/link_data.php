<?php
require_once __DIR__.'/../services/Auth.php';
require_once __DIR__.'/../services/Database.php';
require_once __DIR__.'/../services/Helper.php';
require_once __DIR__.'/../services/Alert.php';

$auth = new Auth(); $auth->checkLogin(); $auth->authorize(['user']);
$db = (new Database())->connect();
$pageTitle = "Tautkan Akun";

if ($_SERVER['REQUEST_METHOD']==='POST') {
  $no_rm = trim($_POST['pasien_id'] ?? '');
  $tgl   = trim($_POST['tanggal_lahir'] ?? '');
  if ($no_rm==='' || $tgl==='') {
    Alert::toast('warning','No RM dan Tanggal Lahir wajib.','link_data.php'); exit;
  }
  $no_rm_esc = mysqli_real_escape_string($db,$no_rm);
  $tgl_esc   = mysqli_real_escape_string($db,$tgl);
  $q = mysqli_query($db,"SELECT inc FROM pasien WHERE pasien_id='$no_rm_esc' AND tanggal_lahir='$tgl_esc' LIMIT 1");
  if ($q && $p = mysqli_fetch_assoc($q)) {
    $pid = (int)$p['inc'];
    $u = $_SESSION['username'];
    mysqli_query($db,"UPDATE account SET pasien_id=$pid WHERE username='".mysqli_real_escape_string($db,$u)."'");
    Alert::toast('success','Berhasil ditautkan.', Helper::baseUrl('portal/index.php'));
    exit;
  } else {
    Alert::toast('error','Data tidak cocok.','link_data.php'); exit;
  }
}

ob_start(); ?>
<h1 class="text-2xl font-bold text-gray-800 mb-4">Tautkan Akun ke Data Pasien</h1>
<p class="text-gray-600 mb-4">Masukkan No RM dan Tanggal Lahir yang terdaftar di rumah sakit.</p>
<form method="POST" class="bg-white p-6 rounded-xl shadow max-w-md">
  <div class="mb-4">
    <label class="block mb-1">No RM (ID Pasien)</label>
    <input name="pasien_id" class="border rounded p-2 w-full" required>
  </div>
  <div class="mb-4">
    <label class="block mb-1">Tanggal Lahir</label>
    <input type="date" name="tanggal_lahir" class="border rounded p-2 w-full" required>
  </div>
  <button class="bg-blue-600 text-white px-4 py-2 rounded">Tautkan</button>
</form>
<?php
$contentFile = tempnam(sys_get_temp_dir(),'content'); file_put_contents($contentFile, ob_get_clean());
include __DIR__.'/../components/layout_user.php';
