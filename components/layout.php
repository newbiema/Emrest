<?php
require_once __DIR__ . '/../services/Helper.php';
$current = basename($_SERVER['SCRIPT_NAME']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Rekam Medis'; ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Poppins', sans-serif; background-color: #f9fafb; }
    .sidebar { background: linear-gradient(to bottom, #2563eb, #1e40af); }
    .sidebar a.active { background-color: #1e3a8a; color: #fff; }
  </style>
</head>

<body class="flex min-h-screen">
  <!-- Sidebar -->
  <aside class="sidebar w-64 text-white flex flex-col fixed h-full shadow-lg">
    <div class="p-6 text-center border-b border-blue-500">
      <h2 class="text-2xl font-bold"><i class="fa-solid fa-hospital-user mr-2"></i>Emrest</h2>
      <p class="text-sm text-blue-100 mt-1"><?= $_SESSION['nama'] ?? 'Guest'; ?></p>
      <p class="text-xs text-blue-200"><?= $_SESSION['level'] ?? ''; ?></p>
    </div>

    <nav class="flex-1 p-4 space-y-2">
      <a href="<?= Helper::baseUrl('index.php') ?>"
         class="flex items-center p-2 rounded-lg transition-all <?= $current === 'index.php' ? 'active' : 'hover:bg-blue-600' ?>">
        <i class="fa-solid fa-gauge mr-3"></i> Dashboard
      </a>

      <a href="<?= Helper::baseUrl('patients/data_pasien.php') ?>"
         class="flex items-center p-2 rounded-lg transition-all <?= $current === 'data_pasien.php' ? 'active' : 'hover:bg-blue-600' ?>">
        <i class="fa-solid fa-users mr-3"></i> Data Pasien
      </a>

      <a href="<?= Helper::baseUrl('doctors/data_dokter.php') ?>"
         class="flex items-center p-2 rounded-lg transition-all <?= $current === 'data_dokter.php' ? 'active' : 'hover:bg-blue-600' ?>">
        <i class="fa-solid fa-user-doctor mr-3"></i> Data Dokter
      </a>

      <a href="<?= Helper::baseUrl('medicines/data_obat.php') ?>"
         class="flex items-center p-2 rounded-lg transition-all <?= $current === 'data_obat.php' ? 'active' : 'hover:bg-blue-600' ?>">
        <i class="fa-solid fa-capsules mr-3"></i> Data Obat
      </a>

      <a href="<?= Helper::baseUrl('records/data_rekam.php') ?>"
         class="flex items-center p-2 rounded-lg transition-all <?= $current === 'data_rekam.php' ? 'active' : 'hover:bg-blue-600' ?>">
        <i class="fa-solid fa-file-medical mr-3"></i> Rekam Medis
      </a>
    </nav>

    <a href="<?= Helper::baseUrl('logout.php') ?>" 
       class="block mt-auto p-4 text-center bg-blue-800 hover:bg-blue-900 transition-all">
      <i class="fa-solid fa-right-from-bracket mr-2"></i> Logout
    </a>
  </aside>

  <!-- Main Content -->
  <main class="flex-1 ml-64 p-8 overflow-y-auto">
    <?php include $contentFile; ?>
  </main>
</body>
</html>
