<?php
require_once __DIR__.'/../config/Database.php';

class CostCenterModel {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }

    // Obtener datos de tb_cc
    public function getCcData() {
        try {
            $query = "SELECT codigo, nombre, nombre_corto, activo FROM tb_cc";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error al obtener tb_cc: ' . $e->getMessage());
            return [];
        }
    }

    // Obtener datos de tb_scc con el nombre del cc
    public function getSccData() {
        try {
            $query = "SELECT s.codigo, s.nombre, s.nombre_corto, s.activo, c.nombre AS cc_nombre
                      FROM tb_scc s
                      JOIN tb_cc c ON s.cc_codigo = c.codigo";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error al obtener tb_scc: ' . $e->getMessage());
            return [];
        }
    }

    // Obtener datos de tb_sscc con el nombre del scc
    public function getSsccData() {
        try {
            $query = "SELECT ss.codigo, ss.nombre, ss.nombre_corto, ss.activo, sc.nombre AS scc_nombre
                      FROM tb_sscc ss
                      JOIN tb_scc sc ON ss.scc_codigo = sc.codigo";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error al obtener tb_sscc: ' . $e->getMessage());
            return [];
        }
    }

    // Obtener lista de cc para dropdown en formulario de scc
    public function getCcList() {
        try {
            $query = "SELECT codigo, nombre FROM tb_cc WHERE activo = 1 ORDER BY nombre";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error al obtener lista de tb_cc: ' . $e->getMessage());
            return [];
        }
    }

    // Obtener lista de scc para dropdown en formulario de sscc
    public function getSccList() {
        try {
            $query = "SELECT codigo, nombre FROM tb_scc WHERE activo = 1 ORDER BY nombre";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error al obtener lista de tb_scc: ' . $e->getMessage());
            return [];
        }
    }

    // Verificar si un código ya existe en tb_cc
    public function checkCcCode($codigo) {
        try {
            $query = "SELECT COUNT(*) FROM tb_cc WHERE codigo = :codigo";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':codigo' => $codigo]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log('Error al verificar código en tb_cc: ' . $e->getMessage());
            return false;
        }
    }
    
    // Verificar si un código ya existe en tb_scc
    public function checkSccCode($codigo) {
        try {
            $query = "SELECT COUNT(*) FROM tb_scc WHERE codigo = :codigo";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':codigo' => $codigo]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log('Error al verificar código en tb_scc: ' . $e->getMessage());
            return false;
        }
    }

    // Verificar si un código ya existe en tb_sscc
    public function checkSsccCode($codigo) {
        try {
            $query = "SELECT COUNT(*) FROM tb_sscc WHERE codigo = :codigo";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':codigo' => $codigo]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log('Error al verificar código en tb_sscc: ' . $e->getMessage());
            return false;
        }
    }

    // Agregar nuevo cc
    public function addCc($codigo, $nombre, $nombre_corto, $activo) {
        try {
            $query = "INSERT INTO tb_cc (codigo, nombre, nombre_corto, activo) VALUES (:codigo, :nombre, :nombre_corto, :activo)";
            $stmt = $this->db->prepare($query);
            return $stmt->execute([
                ':codigo' => strtoupper($codigo),
                ':nombre' => strtoupper($nombre),
                ':nombre_corto' => strtoupper($nombre_corto),
                ':activo' => $activo
            ]);
        } catch (PDOException $e) {
            error_log('Error al agregar tb_cc: ' . $e->getMessage());
            return false;
        }
    }

    // Agregar nuevo scc
    public function addScc($codigo, $nombre, $nombre_corto, $activo, $cc_codigo) {
        try {
            $query = "INSERT INTO tb_scc (codigo, nombre, nombre_corto, activo, cc_codigo) VALUES (:codigo, :nombre, :nombre_corto, :activo, :cc_codigo)";
            $stmt = $this->db->prepare($query);
            return $stmt->execute([
                ':codigo' => strtoupper($codigo),
                ':nombre' => strtoupper($nombre),
                ':nombre_corto' => strtoupper($nombre_corto),
                ':activo' => $activo,
                ':cc_codigo' => $cc_codigo
            ]);
        } catch (PDOException $e) {
            error_log('Error al agregar tb_scc: ' . $e->getMessage());
            return false;
        }
    }

    // Agregar nuevo sscc
    public function addSscc($codigo, $nombre, $nombre_corto, $activo, $scc_codigo) {
        try {
            $query = "INSERT INTO tb_sscc (codigo, nombre, nombre_corto, activo, scc_codigo) VALUES (:codigo, :nombre, :nombre_corto, :activo, :scc_codigo)";
            $stmt = $this->db->prepare($query);
            return $stmt->execute([
                ':codigo' => strtoupper($codigo),
                ':nombre' => strtoupper($nombre),
                ':nombre_corto' => strtoupper($nombre_corto),
                ':activo' => $activo,
                ':scc_codigo' => $scc_codigo
            ]);
        } catch (PDOException $e) {
            error_log('Error al agregar tb_sscc: ' . $e->getMessage());
            return false;
        }
    }

    // Editar un CC
    public function updateCc($codigo, $nombre, $activo) {
        try {
            $query = "UPDATE tb_cc SET nombre = :nombre, activo = :activo WHERE codigo = :codigo";
            $stmt = $this->db->prepare($query);
            return $stmt->execute([
                ':nombre' => strtoupper($nombre),
                ':activo' => strtoupper($activo),
                ':codigo' => $codigo
            ]);
        } catch (PDOException $e) {
            error_log('Error al actualizar tb_cc: ' . $e->getMessage());
            return false;
        }
    }

    // Editar un SCC
    public function updateScc($codigo, $nombre, $activo, $cc_codigo) {
        try {
            $query = "UPDATE tb_scc SET nombre = :nombre, activo = :activo, cc_codigo = :cc_codigo WHERE codigo = :codigo";
            $stmt = $this->db->prepare($query);
            return $stmt->execute([
                ':nombre' => strtoupper($nombre),
                ':activo' => strtoupper($activo),
                ':cc_codigo' => $cc_codigo,
                ':codigo' => $codigo
            ]);
        } catch (PDOException $e) {
            error_log('Error al actualizar tb_scc: ' . $e->getMessage());
            return false;
        }
    }

    // Editar un SSCC
    public function updateSscc($codigo, $nombre, $activo, $scc_codigo) {
        try {
            $query = "UPDATE tb_sscc SET nombre = :nombre, activo = :activo, scc_codigo = :scc_codigo WHERE codigo = :codigo";
            $stmt = $this->db->prepare($query);
            return $stmt->execute([
                ':nombre' => strtoupper($nombre),
                ':activo' => strtoupper($activo),
                ':scc_codigo' => $scc_codigo,
                ':codigo' => $codigo
            ]);
        } catch (PDOException $e) {
            error_log('Error al actualizar tb_sscc: ' . $e->getMessage());
            return false;
        }
    }

    // método requerido para cargar el valor actual en el formulario de edición de un scc
    public function getSccByCodigo($codigo) {
        try {
            $query = "SELECT codigo, nombre, activo, cc_codigo FROM tb_scc WHERE codigo = :codigo";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':codigo' => $codigo]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error al obtener tb_scc por código: ' . $e->getMessage());
            return false;
        }
    }

    // método requerido para cargar el valor actual en el formulario de edición de un sscc
    public function getSsccByCodigo($codigo) {
        try {
            $query = "SELECT codigo, nombre, activo, scc_codigo FROM tb_sscc WHERE codigo = :codigo";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':codigo' => $codigo]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error al obtener tb_sscc por código: ' . $e->getMessage());
            return false;
        }
    }
}