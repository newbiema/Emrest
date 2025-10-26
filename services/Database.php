<?php
class Database {
    private $host = "localhost";
    private $user = "root";
    private $pass = "";
    private $db   = "rekammedis_db";
    private $conn;

    public function connect() {
        if ($this->conn == null) {
            $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->db);

            if ($this->conn->connect_error) {
                die("Database connection failed: " . $this->conn->connect_error);
            }
        }
        return $this->conn;
    }
}
?>
