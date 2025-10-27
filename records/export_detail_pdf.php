<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../services/Database.php';
require_once __DIR__ . '/../services/Auth.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$auth = new Auth();
$auth->checkLogin();

$db = (new Database())->connect();

$param = $_GET['id'] ?? '';
if ($param === '') {
  die('Parameter id tidak ditemukan.');
}

/* --- Tentukan cara cari pasien: inc (angka) atau pasien_id (string) --- */
if (ctype_digit($param)) {
  // contoh: ?id=2  -> pasien.inc = 2
  $pasienSql = "SELECT * FROM pasien WHERE inc=" . (int)$param . " LIMIT 1";
  $rekamWhere = "rm.pasien_id=" . (int)$param;
} else {
  // contoh: ?id=P001 -> pasien.pasien_id = 'P001'
  $safe = mysqli_real_escape_string($db, $param);
  $pasienSql = "SELECT * FROM pasien WHERE pasien_id='{$safe}' LIMIT 1";
  // cari inc dulu supaya konsisten di rekam_medis
  $getInc = mysqli_query($db, "SELECT inc FROM pasien WHERE pasien_id='{$safe}' LIMIT 1");
  $rowInc = mysqli_fetch_assoc($getInc);
  if (!$rowInc) die('Data pasien tidak ditemukan.');
  $rekamWhere = "rm.pasien_id=".(int)$rowInc['inc'];
}

$pasien = mysqli_fetch_assoc(mysqli_query($db, $pasienSql));
if (!$pasien) {
  die('Data pasien tidak ditemukan.');
}

/* --- Ambil riwayat rekam medis pasien (join dokter & obat dgn kolom terbaru) --- */
$records = mysqli_query($db, "
  SELECT rm.*, d.dokter_nama, d.spesialis, o.nama_obat, o.dosis
  FROM rekam_medis rm
  LEFT JOIN dokter d ON rm.dokter_id = d.id
  LEFT JOIN obat   o ON rm.obat_id   = o.id
  WHERE {$rekamWhere}
  ORDER BY rm.tanggal DESC
");

/* --- Dompdf setup --- */
$options = new Options();
$options->set('isRemoteEnabled', true);     // jika pakai logo dari URL
$options->set('isHtml5ParserEnabled', true);
$dompdf = new Dompdf($options);

/* --- HTML --- */
ob_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Laporan Rekam Medis - <?= htmlspecialchars($pasien['pasien_nama']) ?></title>
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color:#333; }
    h2 { text-align:center; color:#2563eb; margin:0 0 8px; }
    .head { text-align:center; margin-bottom:14px; }
    .sub { font-size:11px; color:#666; }
    table { width:100%; border-collapse: collapse; }
    th, td { border:1px solid #555; padding:6px; vertical-align: top; }
    th { background:#2563eb; color:#fff; }
    tr:nth-child(even){ background:#f7f9fc; }
    .section { margin: 12px 0 6px; font-weight: bold; color:#1f2937; }
    .meta td { border:none; padding:2px 0; }
    .footer { text-align:right; margin-top:18px; font-size:10px; color:#777; }
  </style>
</head>
<body>
  <div class="head">
    <h2>RS Emrest</h2>
    <div class="sub">Jl. Sehat No.1, Malang</div>
    <hr>
  </div>

  <div class="section">Data Pasien</div>
  <table class="meta">
    <tr><td><strong>ID Pasien</strong></td><td>: <?= htmlspecialchars($pasien['pasien_id']) ?></td></tr>
    <tr><td><strong>Nama</strong></td><td>: <?= htmlspecialchars($pasien['pasien_nama']) ?></td></tr>
    <tr><td><strong>Jenis Kelamin</strong></td><td>: <?= htmlspecialchars($pasien['jenis_kelamin']) ?></td></tr>
    <tr><td><strong>Umur</strong></td><td>: <?= htmlspecialchars($pasien['umur']) ?> tahun</td></tr>
    <tr><td><strong>Alamat</strong></td><td>: <?= htmlspecialchars($pasien['alamat']) ?></td></tr>
    <tr><td><strong>Telepon</strong></td><td>: <?= htmlspecialchars($pasien['telp'] ?? '-') ?></td></tr>
  </table>

  <div class="section">Riwayat Rekam Medis</div>
  <table>
    <thead>
      <tr>
        <th style="width:70px;">Tanggal</th>
        <th style="width:90px;">Kode RM</th>
        <th style="width:140px;">Dokter</th>
        <th style="width:110px;">Obat</th>
        <th>Diagnosa</th>
        <th style="width:140px;">Tindakan</th>
        <th style="width:120px;">Keterangan</th>
      </tr>
    </thead>
    <tbody>
      <?php if (mysqli_num_rows($records) === 0): ?>
        <tr><td colspan="7" style="text-align:center;">Belum ada rekam medis.</td></tr>
      <?php else: while($r = mysqli_fetch_assoc($records)) { ?>
        <tr>
          <td><?= htmlspecialchars($r['tanggal']) ?></td>
          <td><?= htmlspecialchars($r['kode_rm']) ?></td>
          <td><?= htmlspecialchars(($r['dokter_nama'] ?? '-') . ($r['spesialis'] ? " ({$r['spesialis']})" : '')) ?></td>
          <td><?= htmlspecialchars(($r['nama_obat'] ?? '-') . ($r['dosis'] ? " / {$r['dosis']}" : '')) ?></td>
          <td><?= nl2br(htmlspecialchars($r['diagnosa'])) ?></td>
          <td><?= nl2br(htmlspecialchars($r['tindakan'])) ?></td>
          <td><?= nl2br(htmlspecialchars($r['keterangan'])) ?></td>
        </tr>
      <?php } endif; ?>
    </tbody>
  </table>

  <div class="footer">
    Dicetak: <?= date('d M Y H:i') ?> • Oleh: <?= htmlspecialchars($_SESSION['nama'] ?? '-') ?>
  </div>
</body>
</html>
<?php
$html = ob_get_clean();

/* --- Render --- */
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
$filename = 'Rekam_Medis_' . preg_replace('/[^A-Za-z0-9_\-]/','_', $pasien['pasien_nama']) . '.pdf';
$dompdf->stream($filename, ['Attachment' => false]);
exit;
