<?php
require_once __DIR__ . '/../services/Database.php';
require_once __DIR__ . '/../services/Auth.php';

$auth = new Auth();
$auth->checkLogin();

$db = (new Database())->connect();
$result = mysqli_query($db, "SELECT * FROM dokter ORDER BY dokter_nama ASC");

// Header agar browser langsung download file Excel
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Laporan_Data_Dokter_" . date('Ymd_His') . ".xls");
header("Pragma: no-cache");
header("Expires: 0");
?>

<table border="1" cellspacing="0" cellpadding="5" style="border-collapse: collapse; width: 100%;">
  <thead style="background-color: #2563eb; color: white;">
    <tr>
      <th>No</th>
      <th>ID Dokter</th>
      <th>Nama</th>
      <th>Spesialis</th>
      <th>Jenis Kelamin</th>
      <th>Tanggal Lahir</th>
      <th>Umur</th>
      <th>No. Telepon</th>
      <th>Alamat</th>
    </tr>
  </thead>
  <tbody>
    <?php $no = 1; while ($row = mysqli_fetch_assoc($result)) { ?>
      <tr>
        <td><?= $no++ ?></td>
        <td><?= htmlspecialchars($row['dokter_id']); ?></td>
        <td><?= htmlspecialchars($row['dokter_nama']); ?></td>
        <td><?= htmlspecialchars($row['spesialis']); ?></td>
        <td><?= htmlspecialchars($row['jenis_kelamin']); ?></td>
        <td><?= htmlspecialchars($row['tanggal_lahir']); ?></td>
        <td><?= htmlspecialchars($row['umur']); ?></td>
        <td><?= htmlspecialchars($row['no_telp']); ?></td>
        <td><?= htmlspecialchars($row['alamat']); ?></td>
      </tr>
    <?php } ?>
  </tbody>
</table>

<br>
<p style="text-align: right; font-size: 12px; color: #555;">
  Dicetak pada: <?= date('d M Y H:i'); ?><br>
  Oleh: <?= $_SESSION['nama']; ?>
</p>
