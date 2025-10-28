<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../services/Database.php';
require_once __DIR__ . '/../services/Auth.php';
require_once __DIR__ . '/../services/Helper.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$auth = new Auth();
$auth->checkLogin();
$auth->authorize(['admin','perawat','loket','dokter']);

$db = (new Database())->connect();
$result = mysqli_query($db, "SELECT * FROM pasien ORDER BY pasien_nama ASC");

// Konfigurasi Dompdf
$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);

// Buat HTML untuk PDF
ob_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Laporan Data Pasien</title>
  <style>
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #333; }
    h2 { text-align: center; margin-bottom: 20px; color: #2563eb; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th, td { border: 1px solid #555; padding: 6px; text-align: left; }
    th { background: #2563eb; color: white; }
    tr:nth-child(even) { background: #f2f2f2; }
    .header { display: flex; align-items: center; justify-content: center; margin-bottom: 20px; }
    .header img { height: 60px; margin-right: 15px; }
    .footer { text-align: right; margin-top: 30px; font-size: 10px; color: #777; }
  </style>
</head>
<body>
  <div class="header">
    <img src="<?= Helper::baseUrl('assets/img/logo.png') ?>" alt="Logo Rumah Sakit">
    <div>
      <h3>RS Emrest</h3>
      <p>Jl. Sehat No.1, Malang</p>
    </div>
  </div>

  <h2>Laporan Data Pasien</h2>

  <table>
    <thead>
      <tr>
        <th>No</th>
        <th>ID Pasien</th>
        <th>Nama</th>
        <th>Tgl Lahir</th>
        <th>JK</th>
        <th>Umur</th>
        <th>Alamat</th>
        <th>Nama KK</th>
      </tr>
    </thead>
    <tbody>
      <?php $no=1; while($row=mysqli_fetch_assoc($result)) { ?>
        <tr>
          <td><?= $no++ ?></td>
          <td><?= htmlspecialchars($row['pasien_id']); ?></td>
          <td><?= htmlspecialchars($row['pasien_nama']); ?></td>
          <td><?= htmlspecialchars($row['tanggal_lahir']); ?></td>
          <td><?= htmlspecialchars($row['jenis_kelamin']); ?></td>
          <td><?= htmlspecialchars($row['umur']); ?></td>
          <td><?= htmlspecialchars($row['alamat']); ?></td>
          <td><?= htmlspecialchars($row['nama_kk']); ?></td>
        </tr>
      <?php } ?>
    </tbody>
  </table>

  <div class="footer">
    Dicetak pada: <?= date('d M Y H:i') ?><br>
    Oleh: <?= $_SESSION['nama'] ?>
  </div>
</body>
</html>
<?php
$html = ob_get_clean();

// Load HTML ke Dompdf
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();

// Tampilkan ke browser tanpa download otomatis
$dompdf->stream('Laporan_Pasien.pdf', ['Attachment' => false]);
exit;
