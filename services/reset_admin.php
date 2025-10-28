<?php
require_once __DIR__ . '/Database.php';
$db = (new Database())->connect();

$newPass = 'admin'; // password baru
$newHash = password_hash($newPass, PASSWORD_DEFAULT);

$db->query("UPDATE account SET password='$newHash' WHERE username='admin'");
echo "Password admin direset ke '$newPass'";
