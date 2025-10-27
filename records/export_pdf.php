<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../services/Auth.php';
require_once __DIR__ . '/../services/Database.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$auth = new Auth();
$auth->checkLogin();

$db = (new Database())->connect();

// Query sama seperti di data_rekam.php
$sql = "SELECT rm.id, rm.kode_rm, rm.tanggal, 
               p.pasien_nama, d.dokter_nama, o.nama_obat, 
               rm.diagnosa, rm.tindakan
        FROM rekam_medis rm
        JOIN pasien p ON rm.pasien_id = p.inc
        JOIN dokter d ON rm.dokter_id = d.id
        JOIN obat o ON rm.obat_id = o.id
        ORDER BY rm.id DESC";
$result = mysqli_query($db, $sql);

$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('isHtml5ParserEnabled', true);
$dompdf = new Dompdf($options);

ob_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Laporan Data Rekam Medis</title>
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color:#333; }
    h2 { text-align:center; color:#2563eb; margin:0 0 12px; }
    .sub { text-align:center; font-size:11px; color:#666; margin-bottom:8px; }
    table { width:100%; border-collapse: collapse; }
    th, td { border:1px solid #555; padding:6px; vertical-align: top; }
    th { background:#2563eb; color:#fff; }
    tr:nth-child(even){ background:#f7f9fc; }
    .footer { text-align:right; margin-top:10px; font-size:10px; color:#777; }
  </style>
</head>
<body>
  <h2>Laporan Data Rekam Medis</h2>
  <div class="sub">RS Emrest • Dicetak: <?= date('d M Y H:i') ?> • Oleh: <?= htmlspecialchars($_SESSION['nama'] ?? '-') ?></div>

  <table>
    <thead>
      <tr>
        <th style="width:35px;">No</th>
        <th style="width:90px;">Kode RM</th>
        <th style="width:80px;">Tanggal</th>
        <th style="width:140px;">Pasien</th>
        <th style="width:140px;">Dokter</th>
        <th style="width:130px;">Obat</th>
        <th>Diagnosa</th>
        <th style="width:150px;">Tindakan</th>
      </tr>
    </thead>
    <tbody>
      <?php $no=1; if (mysqli_num_rows($result) > 0): ?>
        <?php while($row = mysqli_fetch_assoc($result)) { ?>
          <tr>
            <td><?= $no++ ?></td>
            <td><?= htmlspecialchars($row['kode_rm'] ?? '-') ?></td>
            <td><?= htmlspecialchars($row['tanggal'] ?? '-') ?></td>
            <td><?= htmlspecialchars($row['pasien_nama'] ?? '-') ?></td>
            <td><?= htmlspecialchars($row['dokter_nama'] ?? '-') ?></td>
            <td><?= htmlspecialchars($row['nama_obat'] ?? '-') ?></td>
            <td><?= nl2br(htmlspecialchars($row['diagnosa'] ?? '-')) ?></td>
            <td><?= nl2br(htmlspecialchars($row['tindakan'] ?? '-')) ?></td>
          </tr>
        <?php } ?>
      <?php else: ?>
        <tr><td colspan="8" style="text-align:center;">Belum ada data.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>

  <div class="footer">
    Sistem Rekam Medis RS Emrest
  </div>
</body>
</html>
<?php
$html = ob_get_clean();

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
$dompdf->stream('Laporan_Rekam_Medis.pdf', ['Attachment' => false]);
exit;
