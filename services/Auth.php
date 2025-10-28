<?php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Helper.php';
require_once __DIR__ . '/Alert.php';

class Auth {
    private $db;
    private string $loginPage;

    public function __construct() {
        $this->startSession();
        $this->db = (new Database())->connect();
        $this->loginPage = Helper::baseUrl('login.php');
    }

    private function startSession(): void {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    }

    /** Login: gunakan password_verify */
    public function login(string $username, string $password): bool {
        $stmt = $this->db->prepare("SELECT * FROM account WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res && $res->num_rows > 0) {
            $user = $res->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['logged_in'] = true;
                $_SESSION['username']  = $user['username'];
                $_SESSION['nama']      = $user['nama'];
                $_SESSION['level']     = $user['level'];
                $_SESSION['login_time']= time();
                $_SESSION['csrf']      = bin2hex(random_bytes(16));
                return true;
            }
        }
        return false;
    }

    public function checkLogin(): void {
        if (empty($_SESSION['logged_in'])) {
            header('Location: ' . $this->loginPage);
            exit;
        }
    }

    public function hasRole($roles): bool {
        $role = $_SESSION['level'] ?? '';
        if (is_string($roles)) $roles = [$roles];
        return in_array($role, $roles, true);
    }

    public function authorize($roles, string $redirect = 'index.php'): void {
        $this->checkLogin();
        if (!$this->hasRole($roles)) {
            Alert::toast('warning', 'Akses ditolak.', Helper::baseUrl($redirect));
            exit;
        }
    }

    public function user(): array {
        return [
            'username' => $_SESSION['username'] ?? null,
            'nama'     => $_SESSION['nama'] ?? null,
            'level'    => $_SESSION['level'] ?? null,
        ];
    }

    public function redirectAfterLogin(): void {
        $role = $_SESSION['level'] ?? 'admin';
        switch ($role) {
            case 'dokter':  $target = 'records/data_rekam.php'; break;
            case 'perawat': $target = 'patients/data_pasien.php'; break;
            case 'apotek':  $target = 'medicines/data_obat.php'; break;
            case 'loket':   $target = 'patients/data_pasien.php'; break;
            case 'user' :   $target = 'portal/index.php'; break;
            default:        $target = 'index.php';
        }
        header('Location: ' . Helper::baseUrl($target));
        exit;
    }

    public function logout(): void {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
        header('Location: ' . $this->loginPage);
        exit;
    }
}
