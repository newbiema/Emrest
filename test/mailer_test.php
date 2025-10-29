<?php
require_once __DIR__ . '/../services/Mailer.php';

use Emrest\Mailer;

$mailer = new Mailer([
  'smtp_debug' => 2, // lihat log SMTP di output
]);

$ok = $mailer->sendVerification('email_tujuan@xevan19@gmail.com', 'Tester', bin2hex(random_bytes(8)));
echo $ok ? "SUKSES\n" : "GAGAL\n";
