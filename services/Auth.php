<?php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Helper.php';
require_once __DIR__ . '/Alert.php';

class Auth {
    private mysqli $db;
    private string $loginPage;
    private int $idleTtl = 60 * 60 * 2; // 2 jam idle timeout (opsional)

    public function __construct() {
        $this->startSession();
        $this->db = (new Database())->connect();
        $this->loginPage = Helper::baseUrl('login.php');
    }

    private function startSession(): void {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    }

    /** Normalisasi username (disarankan lowercase supaya konsisten) */
    private function normalizeUsername(string $u): string {
        return trim(mb_strtolower($u));
    }

    /**
     * Login:
     * - dukung password bcrypt (kolom `password`)
     * - fallback akun lama MD5 (panjang 32) + auto-upgrade ke bcrypt
     * - set session termasuk mapping pasien via pasien.account_id
     */
    public function login(string $username, string $password): bool {
        $username = $this->normalizeUsername($username);

        // NOTE: kolom pasien_id TIDAK ADA lagi di tabel account
        $stmt = $this->db->prepare("SELECT id, username, password, nama, level, verified FROM account WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $res = $stmt->get_result();
        if (!$res || $res->num_rows === 0) return false;

        $user = $res->fetch_assoc();
        $hash = $user['password'];
        $ok   = false;

        // 1) bcrypt modern
        if (strlen($hash) >= 50 && password_get_info($hash)['algo'] !== 0) {
            $ok = password_verify($password, $hash);
            // rehash bila perlu (cost berubah)
            if ($ok && password_needs_rehash($hash, PASSWORD_BCRYPT)) {
                $new = password_hash($password, PASSWORD_BCRYPT);
                $up  = $this->db->prepare("UPDATE account SET password=? WHERE id=?");
                $up->bind_param("si", $new, $user['id']);
                $up->execute();
                $up->close();
            }
        }
        // 2) fallback MD5 (legacy)
        elseif (strlen($hash) === 32) {
            if (hash_equals($hash, md5($password))) {
                $ok = true;
                // auto-upgrade ke bcrypt
                $new = password_hash($password, PASSWORD_BCRYPT);
                $up  = $this->db->prepare("UPDATE account SET password=? WHERE id=?");
                $up->bind_param("si", $new, $user['id']);
                $up->execute();
                $up->close();
            }
        }

        if (!$ok) return false;

        // (opsional) wajib verifikasi untuk user portal
        if ($user['level'] === 'user' && (int)($user['verified'] ?? 0) !== 1) {
            Alert::toast('warning', 'Akun belum diverifikasi. Cek email kamu ya.', Helper::baseUrl('login.php'));
            return false;
        }

        // sukses → set session dasar
        session_regenerate_id(true);
        $_SESSION['logged_in']     = true;
        $_SESSION['uid']           = (int)$user['id'];          // alias lama
        $_SESSION['account_id']    = (int)$user['id'];          // alias jelas
        $_SESSION['username']      = $user['username'];         // terserah mau lowercase/apa adanya
        $_SESSION['nama']          = $user['nama'];
        $_SESSION['level']         = $user['level'];            // admin|dokter|perawat|apotek|loket|user
        $_SESSION['verified']      = (int)($user['verified'] ?? 1);
        $_SESSION['login_time']    = time();
        $_SESSION['last_activity'] = time();
        $_SESSION['csrf']          = bin2hex(random_bytes(16));

        // mapping pasien: cari pasien via pasien.account_id
        // simpan ke session agar portal gampang akses
        $_SESSION['patient_inc']   = null;
        $_SESSION['patient_code']  = null; // pasien.pasien_id

        $accId = (int)$user['id'];
        $ps = $this->db->prepare("SELECT inc, pasien_id FROM pasien WHERE account_id = ? LIMIT 1");
        $ps->bind_param("i", $accId);
        $ps->execute();
        $rps = $ps->get_result();
        if ($rps && $rps->num_rows > 0) {
            $p = $rps->fetch_assoc();
            $_SESSION['patient_inc']  = (int)$p['inc'];
            $_SESSION['patient_code'] = $p['pasien_id'];
        }
        $ps->close();

        return true;
    }

    /** Cek login + idle timeout sederhana */
    public function checkLogin(): void {
        if (empty($_SESSION['logged_in'])) {
            header('Location: ' . $this->loginPage);
            exit;
        }
        // idle timeout (opsional)
        if (!empty($this->idleTtl) && isset($_SESSION['last_activity'])) {
            if (time() - (int)$_SESSION['last_activity'] > $this->idleTtl) {
                $this->logout(true);
            } else {
                $_SESSION['last_activity'] = time();
            }
        }
    }

    /** Cek apakah user memiliki salah satu role */
    public function hasRole($roles): bool {
        $role = $_SESSION['level'] ?? '';
        if (is_string($roles)) $roles = [$roles];
        return in_array($role, $roles, true);
    }

    /**
     * Guard halaman:
     * - Pastikan login
     * - Pastikan role termasuk yang diizinkan
     */
    public function authorize($roles, string $redirect = 'index.php'): void {
        $this->checkLogin();
        if (!$this->hasRole($roles)) {
            Alert::toast('warning', 'Akses ditolak.', Helper::baseUrl($redirect));
            exit;
        }
    }

    /**
     * Untuk portal pasien: wajib ada profil pasien yang tertaut.
     * Cek session 'patient_inc' (bisa juga fallback query bila kamu mau).
     */
    public function requireLinkedPatient(string $redirectIfMissing = 'portal/profile.php'): void {
        $this->checkLogin();
        if (($this->user()['level'] ?? '') === 'user') {
            if (empty($_SESSION['patient_inc'])) {
                header('Location: ' . Helper::baseUrl($redirectIfMissing));
                exit;
            }
        }
    }

    /** Data user singkat */
    public function user(): array {
        return [
            'id'           => $_SESSION['uid'] ?? null,
            'account_id'   => $_SESSION['account_id'] ?? null,
            'username'     => $_SESSION['username'] ?? null,
            'nama'         => $_SESSION['nama'] ?? null,
            'level'        => $_SESSION['level'] ?? null,
            'verified'     => $_SESSION['verified'] ?? null,
            'patient_inc'  => $_SESSION['patient_inc'] ?? null,
            'patient_code' => $_SESSION['patient_code'] ?? null,
        ];
    }

    /** Redirect pasca login sesuai role */
    public function redirectAfterLogin(): void {
        $role = $_SESSION['level'] ?? 'admin';
        switch ($role) {
            case 'dokter':
                $target = 'records/data_rekam.php';
                break;
            case 'perawat':
                $target = 'patients/data_pasien.php'; // bisa diarahkan ke triase/antrian jika ada
                break;
            case 'apotek':
                $target = 'medicines/data_obat.php';
                break;
            case 'loket':
                $target = 'patients/data_pasien.php'; // bisa diarahkan ke pembayaran jika ada
                break;
            case 'user':
                $target = 'portal/index.php';
                break;
            default:
                $target = 'index.php'; // admin
        }
        header('Location: ' . Helper::baseUrl($target));
        exit;
    }

    /**
     * Logout
     * @param bool $expired jika true, tampilkan pesan sesi berakhir (opsional)
     */
    public function logout(bool $expired = false): void {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();

        if ($expired) {
            header('Location: ' . $this->loginPage . '?expired=1');
        } else {
            header('Location: ' . $this->loginPage);
        }
        exit;
    }

    /* ===== Helper untuk UI (sembunyikan tombol sesuai role) ===== */

    /** true kalau user saat ini punya salah satu role */
    public static function can($roles): bool {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        $role = $_SESSION['level'] ?? '';
        if (is_string($roles)) $roles = [$roles];
        return in_array($role, $roles, true);
    }

    /** Buat class hidden/disabled cepat dari role */
    public static function hideIfNo($roles, string $classWhenHidden = 'hidden'): string {
        return self::can($roles) ? '' : $classWhenHidden;
    }
}
