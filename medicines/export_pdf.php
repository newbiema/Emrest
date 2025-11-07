<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../services/Database.php';
require_once __DIR__ . '/../services/Auth.php';
require_once __DIR__ . '/../services/Helper.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$auth = new Auth();
$auth->checkLogin();

$auth->authorize(['admin','farmasi']);
$db = (new Database())->connect();
$result = mysqli_query($db, "SELECT * FROM obat ORDER BY nama_obat ASC");

$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);

ob_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Laporan Data Obat</title>
  <style>
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #333; }
    h2 { text-align: center; margin-bottom: 20px; color: #2563eb; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th, td { border: 1px solid #555; padding: 6px; text-align: left; }
    th { background: #2563eb; color: white; }
    tr:nth-child(even) { background: #f2f2f2; }
  </style>
</head>
<body>
  <h2>Laporan Data Obat</h2>
  <table>
    <thead>
      <tr>
        <th>No</th>
        <th>Kode Obat</th>
        <th>Nama Obat</th>
        <th>Kategori</th>
        <th>Stok</th>
        <th>Harga</th>
        <th>Keterangan</th>
      </tr>
    </thead>
    <tbody>
      <?php $no=1; while($row=mysqli_fetch_assoc($result)) { ?>
        <tr>
          <td><?= $no++ ?></td>
          <td><?= htmlspecialchars($row['kode_obat']); ?></td>
          <td><?= htmlspecialchars($row['nama_obat']); ?></td>
          <td><?= htmlspecialchars($row['kategori']); ?></td>
          <td><?= htmlspecialchars($row['stok']); ?></td>
          <td>Rp <?= number_format($row['harga'], 0, ',', '.'); ?></td>
          <td><?= htmlspecialchars($row['keterangan']); ?></td>
        </tr>
      <?php } ?>
    </tbody>
  </table>
  <p style="margin-top:20px; text-align:right;">Dicetak pada: <?= date('d M Y H:i') ?><br>Oleh: <?= $_SESSION['nama'] ?></p>
</body>
</html>
<?php
$html = ob_get_clean();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
$dompdf->stream('Laporan_Obat.pdf', ['Attachment' => false]);
exit;
