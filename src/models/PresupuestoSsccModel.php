<?php
require_once 'src/config/Database.php';

class PresupuestoSsccModel {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }

    // Verificar si el código SSCC existe
    private function ssccExists($codigo_sscc) {
        try {
            $query = "SELECT COUNT(*) FROM tb_sscc WHERE codigo = :codigo_sscc AND activo = 1";
            $stmt = $this->db->prepare($query);
            $stmt->execute(['codigo_sscc' => $codigo_sscc]);
            $count = $stmt->fetchColumn();
            error_log('Verificando codigo_sscc ' . $codigo_sscc . ': ' . ($count > 0 ? 'Existe' : 'No existe'));
            return $count > 0;
        } catch (PDOException $e) {
            error_log('Error al verificar codigo_sscc: ' . $e->getMessage());
            return false;
        }
    }

    // Agregando un nuevo presupuesto
    public function addPresupuesto($codigo_sscc, $saldo_inicial, $saldo_final, $saldo_disponible, $ultima_actualizacion, $activo = 1) {
        error_log('Entrando a addPresupuesto con datos: ' . json_encode(func_get_args()));
        try {
            $this->db->beginTransaction();

            // Validar datos
            if (empty($codigo_sscc) || !is_numeric($saldo_inicial) || !is_numeric($saldo_final) || 
                !is_numeric($saldo_disponible) || empty($ultima_actualizacion)) {
                error_log('Datos incompletos o inválidos en addPresupuesto');
                $this->db->rollBack();
                return false;
            }

            // Validar formato de fecha
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $ultima_actualizacion) || !strtotime($ultima_actualizacion)) {
                error_log('Formato de fecha inválido: ' . $ultima_actualizacion);
                $this->db->rollBack();
                return false;
            }

            // Verificar que codigo_sscc exista
            if (!$this->ssccExists($codigo_sscc)) {
                error_log('Código SSCC no válido: ' . $codigo_sscc);
                $this->db->rollBack();
                return false;
            }

            // Insertar presupuesto
            $query = "INSERT INTO tb_presupuestos_sscc (codigo_sscc, saldo_inicial, saldo_final, saldo_disponible, ultima_actualizacion, activo)
                      VALUES (:codigo_sscc, :saldo_inicial, :saldo_final, :saldo_disponible, :ultima_actualizacion, :activo)";
            $stmt = $this->db->prepare($query);
            $params = [
                'codigo_sscc' => $codigo_sscc,
                'saldo_inicial' => $saldo_inicial,
                'saldo_final' => $saldo_final,
                'saldo_disponible' => $saldo_disponible,
                'ultima_actualizacion' => $ultima_actualizacion,
                'activo' => $activo
            ];
            error_log('Ejecutando INSERT tb_presupuestos_sscc con params: ' . json_encode($params));
            $stmt->execute($params);

            $this->db->commit();
            error_log('Presupuesto registrado exitosamente');
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log('Error al agregar presupuesto: ' . $e->getMessage());
            return false;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log('Error general en addPresupuesto: ' . $e->getMessage());
            return false;
        }
    }
}
?>