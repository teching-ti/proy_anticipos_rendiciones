<?php
require_once 'src/config/Database.php';

class RendicionesModel {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }
}