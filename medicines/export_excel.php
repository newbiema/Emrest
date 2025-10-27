<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../services/Database.php';
require_once __DIR__ . '/../services/Auth.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$auth = new Auth();
$auth->checkLogin();
$db = (new Database())->connect();
$result = mysqli_query($db, "SELECT * FROM obat ORDER BY nama_obat ASC");

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Data Obat');

$sheet->fromArray(['No', 'Kode Obat', 'Nama Obat', 'Kategori', 'Stok', 'Harga', 'Keterangan'], NULL, 'A1');
$rowNum = 2; $no = 1;

while ($row = mysqli_fetch_assoc($result)) {
  $sheet->fromArray([$no++, $row['kode_obat'], $row['nama_obat'], $row['kategori'], $row['stok'], $row['harga'], $row['keterangan']], NULL, "A$rowNum");
  $rowNum++;
}

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="Data_Obat.xlsx"');
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
