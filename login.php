<?php
require_once __DIR__ . '/services/Auth.php';

$auth = new Auth();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if ($auth->login($username, $password)) {
        header("Location: index.php");
        exit;
    } else {
        header("Location: form_login.php?error=true");
        exit;
    }

} else {
    header("Location: form_login.php");
    exit;
}
?>
