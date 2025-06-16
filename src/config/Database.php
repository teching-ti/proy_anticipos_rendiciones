<?php
class Database {
    private $host = 'localhost';
    private $db_name = 'db_ant_rend';
    private $username = 'root';
    private $password = '';
    private $conn;

    public function connect() {
        $this->conn = null;

        try {
            //PDO
            $this->conn = new PDO("mysql:host={$this->host};dbname={$this->db_name}", $this->username, $this->password);

            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        } catch (PDOException $e) {
            /*
                - Este mensaje de error deberá ser cambiado por uno general en fase de deploy
            */
            echo 'Error: ' . $e->getMessage();
        }
        return $this->conn;
    }
}