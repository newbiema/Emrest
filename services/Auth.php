<?php
require_once __DIR__ . '/Database.php';

class Auth {
    private $db;

    public function __construct() {
        $this->db = (new Database())->connect();
        session_start();
    }

    public function login($username, $password) {
        $username = md5($username); // tetap md5 biar cocok sama DB kamu
        $password = md5($password);

        $stmt = $this->db->prepare("SELECT * FROM account WHERE username = ? AND password = ?");
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $data = $result->fetch_assoc();
            $_SESSION['username'] = $data['username'];
            $_SESSION['nama'] = $data['nama'];
            $_SESSION['level'] = $data['level'];
            return true;
        }
        return false;
    }

    public function checkLogin() {
        if (!isset($_SESSION['username'])) {
            header("Location: form_login.php");
            exit;
        }
    }

    public function logout() {
        session_destroy();
        header("Location: form_login.php");
        exit;
    }
}
?>
