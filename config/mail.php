<?php
return [
  // --- Pilih salah satu provider dan isi nilainya ---
  'driver'      => 'smtp',            // 'smtp'
  'host'        => 'smtp.gmail.com',  // contoh: Gmail SMTP
  'port'        => 587,               // 587 (TLS) atau 465 (SSL)
  'encryption'  => 'tls',             // 'tls' atau 'ssl'
  'username'    => 'evanjamaq123@gmail.com',
  'password'    => 'paot hjfv kdbm piub',  // App Password (bukan password biasa)
  'from_email'  => 'noreply@emrest.local',
  'from_name'   => 'RS Emrest',

  // opsional
  'reply_to'    => 'cs@emrest.local',
  'reply_name'  => 'Customer Service',
  'smtp_debug'  => 0, // 0=off, 2=verbos, 3/4=lebih detail
];
