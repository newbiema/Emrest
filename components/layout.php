<?php
require_once __DIR__ . '/../services/Helper.php';
require_once __DIR__ . '/../services/Auth.php';

$auth = new Auth();
$auth->checkLogin();

$user = $_SESSION['nama'] ?? 'User';
$role = $_SESSION['level'] ?? 'guest';

// ambil judul halaman
$pageTitle = $pageTitle ?? 'Rekam Medis';
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle) ?> | RS Emrest</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    body { font-family: 'Poppins', sans-serif; background-color: #f9fafb; }
    .sidebar { background: linear-gradient(to bottom, #2563eb, #1e3a8a); }
  </style>
</head>

<body class="flex h-screen overflow-hidden">

  <!-- Sidebar -->
  <aside class="sidebar w-64 text-white flex flex-col">
    <div class="p-6 border-b border-blue-500 text-center">
      <h2 class="text-2xl font-bold mb-1 flex justify-center items-center gap-2">
        <i class="fa-solid fa-hospital"></i> RS Emrest
      </h2>
      <p class="text-xs text-blue-100"><?= htmlspecialchars($user) ?> (<?= htmlspecialchars($role) ?>)</p>
    </div>

    <!-- Menu Utama -->
    <nav class="flex-1 p-4 space-y-1 text-sm">

      <!-- Dashboard -->
      <a href="<?= Helper::baseUrl('index.php') ?>"
         class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-blue-700 <?= basename($_SERVER['PHP_SELF'])=='index.php'?'bg-blue-700':'' ?>">
        <i class="fa-solid fa-gauge-high w-5"></i> Dashboard
      </a>

      <!-- Data Pasien (admin, perawat, loket, dokter, apotek) -->
      <?php if (in_array($role, ['admin','perawat','loket','dokter','apotek'])): ?>
      <a href="<?= Helper::baseUrl('patients/data_pasien.php') ?>"
         class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-blue-700">
        <i class="fa-solid fa-users w-5"></i> Data Pasien
      </a>
      <?php endif; ?>

      <!-- Data Dokter (admin, dokter) -->
      <?php if (in_array($role, ['admin'])): ?>
      <a href="<?= Helper::baseUrl('doctors/data_dokter.php') ?>"
         class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-blue-700">
        <i class="fa-solid fa-user-doctor w-5"></i> Data Dokter
      </a>
      <?php endif; ?>

      <!-- Data Obat (admin, apotek) -->
      <?php if (in_array($role, ['admin','apotek'])): ?>
      <a href="<?= Helper::baseUrl('medicines/data_obat.php') ?>"
         class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-blue-700">
        <i class="fa-solid fa-capsules w-5"></i> Data Obat
      </a>
      <?php endif; ?>

      <!-- Rekam Medis (admin, dokter, perawat) -->
      <?php if (in_array($role, ['admin','dokter','perawat'])): ?>
      <a href="<?= Helper::baseUrl('records/data_rekam.php') ?>"
         class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-blue-700">
        <i class="fa-solid fa-file-medical w-5"></i> Rekam Medis
      </a>
      <?php endif; ?>

      <!-- Manajemen User (admin only) -->
      <?php if ($role === 'admin'): ?>
      <a href="<?= Helper::baseUrl('accounts/data_user.php') ?>"
         class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-blue-700">
        <i class="fa-solid fa-users-gear w-5"></i> Manajemen User
      </a>
      <?php endif; ?>
    </nav>

    <!-- Tombol Logout -->
    <div class="p-4 border-t border-blue-700">
      <a href="<?= Helper::baseUrl('logout.php') ?>"
         class="block text-center py-2 bg-blue-800 hover:bg-blue-900 rounded-lg font-semibold text-white transition">
        <i class="fa-solid fa-right-from-bracket mr-2"></i> Logout
      </a>
    </div>
  </aside>

  <!-- Konten Utama -->
  <main class="flex-1 overflow-y-auto p-8">
    <?php include $contentFile; ?>
  </main>

</body>
</html>

<script>
  // Role user aktif dari session
  const USER_ROLE = "<?= $_SESSION['level'] ?? 'guest' ?>";

  // ==== DAFTAR PERMISSION YANG KITA PAKAI ====
  // patients:   patient.view, patient.add, patient.edit, patient.delete, patient.export
  // doctors:    doctor.view, doctor.add, doctor.edit, doctor.delete, doctor.export
  // medicines:  medicine.view, medicine.add, medicine.edit, medicine.delete, medicine.export
  // records:    record.view, record.add, record.edit, record.delete, record.export
  // accounts:   user.manage (list/add/edit/delete user)

  // ==== MATRIX ROLE -> PERMISSION ====
  const ROLE_ACCESS = {
    admin: [
      '*', // semua boleh
    ],
    dokter: [
      'patient.view',
      'doctor.view',
      'medicine.view',
      'record.view', 'record.add', 'record.edit',
      'patient.export', 'doctor.export', 'record.export',
    ],
    perawat: [
      'patient.view','patient.add','patient.edit',
      'medicine.view',
      'record.view','record.add',
      'patient.export',
    ],
    apotek: [
      'patient.view',
      'medicine.view','medicine.add','medicine.edit',
      'medicine.export',
    ],
    loket: [
      'patient.view','patient.add','patient.edit',
      'patient.export',
    ],
    guest: [] // default: tidak punya izin
  };

  // ==== UTIL: cek izin ====
  function can(perm) {
    const allow = ROLE_ACCESS[USER_ROLE] || [];
    if (allow.includes('*')) return true;
    return allow.includes(perm);
  }

  // ==== Auto-hide elemen berdasarkan atribut data ====
  document.addEventListener('DOMContentLoaded', () => {
    // 1 permission persis: data-role="perm"
    document.querySelectorAll('[data-role]').forEach(el => {
      const perm = (el.getAttribute('data-role') || '').trim();
      if (!perm) return;
      if (!can(perm)) el.style.display = 'none';
    });

    // salah satu permission: data-any="perm1,perm2"
    document.querySelectorAll('[data-any]').forEach(el => {
      const any = (el.getAttribute('data-any') || '').split(',').map(s => s.trim()).filter(Boolean);
      if (any.length === 0) return;
      const ok = any.some(p => can(p));
      if (!ok) el.style.display = 'none';
    });

    // blacklist: paksa sembunyi untuk permission tertentu (kondisi khusus)
    document.querySelectorAll('[data-not]').forEach(el => {
      const notPerm = (el.getAttribute('data-not') || '').trim();
      if (!notPerm) return;
      if (can(notPerm)) el.style.display = 'none';
    });
  });
</script>
