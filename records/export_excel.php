<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../services/Auth.php';
require_once __DIR__ . '/../services/Database.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

$auth = new Auth();
$auth->checkLogin();
$auth->authorize(['admin','dokter']);

$db = (new Database())->connect();

$sql = "SELECT rm.id, rm.kode_rm, rm.tanggal, 
               p.pasien_nama, d.dokter_nama, o.nama_obat, 
               rm.diagnosa, rm.tindakan
        FROM rekam_medis rm
        JOIN pasien p ON rm.pasien_id = p.inc
        JOIN dokter d ON rm.dokter_id = d.id
        JOIN obat o ON rm.obat_id = o.id
        ORDER BY rm.id DESC";
$result = mysqli_query($db, $sql);

$sheet = new Spreadsheet();
$sheet->getProperties()
  ->setCreator('RS Emrest')
  ->setTitle('Laporan Data Rekam Medis');

$ws = $sheet->getActiveSheet();
$ws->setTitle('Rekam Medis');

// Header judul
$ws->setCellValue('A1', 'Laporan Data Rekam Medis - RS Emrest');
$ws->mergeCells('A1:H1');
$ws->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$ws->getStyle('A1')->getFont()->setBold(true)->setSize(14);

// Header kolom
$headers = ['No', 'Kode RM', 'Tanggal', 'Pasien', 'Dokter', 'Obat', 'Diagnosa', 'Tindakan'];
$ws->fromArray($headers, null, 'A3');
$ws->getStyle('A3:H3')->getFont()->setBold(true);
$ws->getStyle('A3:H3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$ws->getStyle('A3:H3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE5EEF8');

// Isi data
$rowNum = 4; $no = 1;
if (mysqli_num_rows($result) > 0) {
  while ($r = mysqli_fetch_assoc($result)) {
    $ws->setCellValue("A{$rowNum}", $no++);
    $ws->setCellValue("B{$rowNum}", $r['kode_rm'] ?? '-');
    $ws->setCellValue("C{$rowNum}", $r['tanggal'] ?? '-');
    $ws->setCellValue("D{$rowNum}", $r['pasien_nama'] ?? '-');
    $ws->setCellValue("E{$rowNum}", $r['dokter_nama'] ?? '-');
    $ws->setCellValue("F{$rowNum}", $r['nama_obat'] ?? '-');
    $ws->setCellValue("G{$rowNum}", $r['diagnosa'] ?? '-');
    $ws->setCellValue("H{$rowNum}", $r['tindakan'] ?? '-');
    $rowNum++;
  }
}

// Border tabel
$lastRow = max($rowNum-1, 3);
$ws->getStyle("A3:H{$lastRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

// Wrap text kolom panjang & vertical align top
$ws->getStyle("G4:H{$lastRow}")->getAlignment()->setWrapText(true);
$ws->getStyle("A4:H{$lastRow}")->getAlignment()->setVertical(Alignment::VERTICAL_TOP);

// Auto width
foreach (range('A','H') as $col) {
  $ws->getColumnDimension($col)->setAutoSize(true);
}

// Freeze header
$ws->freezePane('A4');

// Output
$filename = 'Laporan_Rekam_Medis_' . date('Ymd_His') . '.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"{$filename}\"");
header('Cache-Control: max-age=0');

$writer = new Xlsx($sheet);
$writer->save('php://output');
exit;
