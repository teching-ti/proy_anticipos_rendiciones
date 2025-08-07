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
            $query = "SELECT id, nombre, activo FROM tb_cargos_tarifario where activo = 1 order by id";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error al obtener los cargos del tarifario: ' . $e->getMessage());
            return [];
        }
    }

    // muestra todos los cargos, incluso los que no son válidos
    public function getAllCargosTarifario(){
        try {
            $query = "SELECT id, nombre, activo FROM tb_cargos_tarifario order by id";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error al obtener los cargos del tarifario: ' . $e->getMessage());
            return [];
        }
    }
    
    public function getCategoriasTarifario() {
        try {
            $query = "SELECT id, nombre FROM tb_categorias_tarifario WHERE activo = 1 ORDER BY id";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error al obtener las categorías del tarifario: ' . $e->getMessage());
            return [];
        }
    }

    public function getTarifario() {
        try {
            $query = "SELECT t.id, ct.nombre AS cargo, cat.nombre AS categoria, t.monto 
                      FROM tb_tarifario t
                      JOIN tb_cargos_tarifario ct ON t.cargo_id = ct.id
                      JOIN tb_categorias_tarifario cat ON t.concepto_id = cat.id
                      WHERE ct.activo = 1 AND cat.activo = 1
                      ORDER BY t.id";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error al obtener el tarifario: ' . $e->getMessage());
            return [];
        }
    }

    public function insertCargo($nombre){
        try{
            $query = "INSERT INTO tb_cargos_tarifario (nombre, activo) VALUES (:nombre, 1)";
            $stmt = $this->db->prepare($query);
            $stmt->execute([":nombre" => $nombre]);
            return $this->db->lastInsertId();
        }catch(PDOException $e){
            error_log("Error al insertar el cargo: ".$e->getMessage());
            return false;
        }
    }

    public function insertTarifario($cargoId, $conceptoId, $monto) {
        try {
            $query = "INSERT INTO tb_tarifario (cargo_id, concepto_id, monto) VALUES (:cargo_id, :concepto_id, :monto)";
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':cargo_id' => $cargoId,
                ':concepto_id' => $conceptoId,
                ':monto' => $monto
            ]);
            return true;
        } catch (PDOException $e) {
            error_log('Error al insertar en tb_tarifario: ' . $e->getMessage());
            return false;
        }
    }

    // Funcionalidades para editar elementos del tarifario
    public function getMontosPorCargo($cargoId) {
        try {
            $query = "SELECT concepto_id, monto 
                      FROM tb_tarifario 
                      WHERE cargo_id = :cargo_id";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':cargo_id' => $cargoId]);
            $montos = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $montos[$row['concepto_id']] = $row['monto'];
            }
            return $montos;
        } catch (PDOException $e) {
            error_log('Error al obtener montos por cargo: ' . $e->getMessage());
            return [];
        }
    }

    public function updateTarifario($cargoId, $montos) {
        try {
            $this->db->beginTransaction();
            $query = "UPDATE tb_tarifario SET monto = :monto WHERE cargo_id = :cargo_id AND concepto_id = :concepto_id";
            $stmt = $this->db->prepare($query);

            if (!is_array($montos)) {
                error_log('Montos no es un array: ' . print_r($montos, true));
                $this->db->rollBack();
                return false;
            }

            foreach ($montos as $conceptoId => $monto) {
                $stmt->execute([
                    ':cargo_id' => $cargoId,
                    ':concepto_id' => $conceptoId,
                    ':monto' => floatval($monto)
                ]);
            }

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log('Error al actualizar tb_tarifario: ' . $e->getMessage());
            return false;
        }
    }
    
    public function getCargoEstado($cargoId) {
        try {
            $query = "SELECT activo FROM tb_cargos_tarifario WHERE id = :cargo_id";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':cargo_id' => $cargoId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['activo'] : 0;
        } catch (PDOException $e) {
            error_log('Error al obtener el estado del cargo: ' . $e->getMessage());
            return 0;
        }
    }

    public function updateCargoEstado($cargoId, $activo) {
        try {
            $query = "UPDATE tb_cargos_tarifario SET activo = :activo WHERE id = :cargo_id";
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':cargo_id' => $cargoId,
                ':activo' => $activo ? 1 : 0
            ]);
            return true;
        } catch (PDOException $e) {
            error_log('Error al actualizar el estado del cargo: ' . $e->getMessage());
            return false;
        }
    }

    public function getDetallesTarifario($tarifarioId) {
        try {
            $query = "SELECT t.id, ct.nombre AS cargo, cat.nombre AS categoria, t.monto 
                      FROM tb_tarifario t
                      JOIN tb_cargos_tarifario ct ON t.cargo_id = ct.id
                      JOIN tb_categorias_tarifario cat ON t.concepto_id = cat.id
                      WHERE t.id = :tarifario_id";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':tarifario_id' => $tarifarioId]);
            $detalle = $stmt->fetch(PDO::FETCH_ASSOC);

            return $detalle ?: null;
        } catch (PDOException $e) {
            error_log('Error al obtener detalles del tarifario: ' . $e->getMessage());
            return null;
        }
    }

    public function updateMontoTarifario($tarifarioId, $monto) {
        try {
            $query = "UPDATE tb_tarifario SET monto = :monto WHERE id = :tarifario_id";
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':tarifario_id' => $tarifarioId,
                ':monto' => floatval($monto)
            ]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log('Error al actualizar monto del tarifario: ' . $e->getMessage());
            return false;
        }
    }
}