<?php
require_once 'src/config/Database.php';

class TarifarioModel {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }

    public function getCargosTarifario(){
        try {
            $query = "SELECT id, nombre FROM tb_cargos_tarifario order by id";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error al obtener los cargos del tarifario: ' . $e->getMessage());
            return [];
        }
        
    }
}