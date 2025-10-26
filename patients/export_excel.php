<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../services/Database.php';
require_once __DIR__ . '/../services/Auth.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

$auth = new Auth();
$auth->checkLogin();

$db = (new Database())->connect();

// Ambil data pasien
$result = mysqli_query($db, "SELECT * FROM pasien ORDER BY pasien_nama ASC");

// Buat objek spreadsheet baru
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Data Pasien');

// Judul header
$sheet->mergeCells('A1:H1');
$sheet->setCellValue('A1', 'LAPORAN DATA PASIEN');
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Header tabel
$headers = ['No', 'ID Pasien', 'Nama Pasien', 'Tanggal Lahir', 'JK', 'Umur', 'Alamat', 'Nama KK'];
$col = 'A';
foreach ($headers as $header) {
  $sheet->setCellValue($col . '3', $header);
  $col++;
}

// Style header
$sheet->getStyle('A3:H3')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
$sheet->getStyle('A3:H3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('2563EB');
$sheet->getStyle('A3:H3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Isi data
$rowNum = 4;
$no = 1;
while ($row = mysqli_fetch_assoc($result)) {
  $sheet->setCellValue('A' . $rowNum, $no++);
  $sheet->setCellValue('B' . $rowNum, $row['pasien_id']);
  $sheet->setCellValue('C' . $rowNum, $row['pasien_nama']);
  $sheet->setCellValue('D' . $rowNum, $row['tanggal_lahir']);
  $sheet->setCellValue('E' . $rowNum, $row['jenis_kelamin']);
  $sheet->setCellValue('F' . $rowNum, $row['umur']);
  $sheet->setCellValue('G' . $rowNum, $row['alamat']);
  $sheet->setCellValue('H' . $rowNum, $row['nama_kk']);
  $rowNum++;
}

// Auto width kolom
foreach (range('A', 'H') as $col) {
  $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Nama file
$filename = 'Data_Pasien_' . date('Ymd_His') . '.xlsx';

// Output ke browser
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"$filename\"");
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
