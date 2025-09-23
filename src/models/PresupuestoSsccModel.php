<?php
require_once 'src/config/Database.php';

date_default_timezone_set('America/Lima');

class PresupuestoSsccModel {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }

    // Obtener todos los Centros de Costo
    public function getAllCC() {
        try{
            $query = "SELECT codigo, nombre FROM tb_cc WHERE activo = 1";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
        
    }
    
    // Obtener SCCs por CC
    public function getSCCsByCC($cc_id) {
        try{
            $query = "SELECT codigo, nombre FROM tb_scc WHERE cc_codigo = :cc_codigo AND activo = 1";
            $stmt = $this->db->prepare($query);
            $stmt->execute(['cc_codigo' => $cc_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error: " . $e->getMessage());
            return [];
        }
    }

    // Obtener SSCCs disponibles (sin presupuesto asignado)
    public function getAvailableSSCCsBySCC($scc_id) {
        try{
            $query = "SELECT s.codigo, s.nombre
                          FROM tb_sscc s 
                          LEFT JOIN tb_presupuestos_sscc p ON s.codigo = p.codigo_sscc 
                          WHERE s.scc_codigo = :scc_codigo AND p.id IS NULL AND s.activo = 1";
            $stmt = $this->db->prepare($query);
            $stmt->execute(['scc_codigo' => $scc_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error: " . $e->getMessage());
            return [];
        }

    }

    // Insertar nuevo presupuesto en tb_presupuestos_sscc
    public function addPresupuesto($codigo_sscc, $saldo_inicial, $saldo_final, $saldo_disponible, $ultima_actualizacion, $activo) {
        $fecha = date('Y-m-d H:i:s');
        try {
            $query = "INSERT INTO tb_presupuestos_sscc (codigo_sscc, saldo_inicial, saldo_final, saldo_disponible, ultima_actualizacion, activo) 
                      VALUES (:codigo_sscc, :saldo_inicial, :saldo_final, :saldo_disponible, :fecha, :activo)";
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':codigo_sscc' => $codigo_sscc,
                ':saldo_inicial' => $saldo_inicial,
                ':saldo_final' => $saldo_final,
                ':saldo_disponible' => $saldo_disponible,
                ':fecha' => $fecha,
                ':activo' => $activo
            ]);
            return true;
        } catch (PDOException $e) {
            error_log("Error al insertar presupuesto: " . $e->getMessage());
            return false;
        }
    }

    // Obtener el último ID insertado
    public function getLastInsertId() {
        return $this->db->lastInsertId();
    }

    // Obtener todos los presupuestos registrados.
    public function getAllPresupuestos(){
        try{
            //$query = "SELECT p.id, p.codigo_sscc, s.nombre, p.saldo_inicial, p.saldo_final, p.saldo_disponible, p.activo FROM tb_presupuestos_sscc p LEFT JOIN tb_sscc s ON p.codigo_sscc= s.codigo";
            $query = "SELECT p.id, p.codigo_sscc, s.nombre, p.saldo_inicial, p.saldo_final, p.saldo_disponible, p.activo, COALESCE(( SELECT SUM(monto) FROM tb_movimientos_presupuesto mp WHERE mp.id_presupuesto = p.id AND mp.tipo_movimiento = 2 ), 0.00) AS saldo_abonado FROM tb_presupuestos_sscc p LEFT JOIN tb_sscc s ON p.codigo_sscc = s.codigo";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e){
            error_log("Error al insertar movimiento: " . $e->getMessage());
            return [];
        }
    }

    // Insertar
    public function addMovimientoPresupuesto($id_presupuesto, $tipo_movimiento, $monto, $id_usuario, $fecha, $comentario) {
        try {
            $query = "INSERT INTO tb_movimientos_presupuesto (id_presupuesto, tipo_movimiento, monto, id_usuario, fecha, comentario) 
                      VALUES (:id_presupuesto, :tipo_movimiento, :monto, :id_usuario, :fecha, :comentario)";
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':id_presupuesto' => $id_presupuesto,
                ':tipo_movimiento' => $tipo_movimiento,
                ':monto' => $monto,
                ':id_usuario' => $id_usuario,
                ':fecha' => $fecha,
                ':comentario' => $comentario
            ]);
            return true;
        } catch (PDOException $e) {
            error_log("Error al insertar movimiento: " . $e->getMessage());
            return false;
        }
    }

    public function addFunds($presupuestoId, $montoAbono, $id_usuario) {
        try {
            $this->db->beginTransaction();

            // Obtener el saldo disponible y final actual con bloqueo explícito
            $query = "SELECT saldo_disponible, saldo_final FROM tb_presupuestos_sscc WHERE id = :id FOR UPDATE";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':id' => $presupuestoId]);
            $presupuesto = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$presupuesto) {
                throw new Exception('Presupuesto no encontrado');
            }

            $saldo_disponible = $presupuesto['saldo_disponible'] + $montoAbono;
            $saldo_final = $presupuesto['saldo_final'] + $montoAbono;

            // Validar que los saldos no sean negativos (si aplica)
            if ($saldo_disponible < 0 || $saldo_final < 0) {
                throw new Exception('El monto a añadir no puede resultar en saldos negativos.');
            }

            $fechaAct = date('Y-m-d H:i:s');

            // Actualizar el presupuesto
            $query = "UPDATE tb_presupuestos_sscc 
                      SET saldo_disponible = :saldo_disponible, saldo_final = :saldo_final, ultima_actualizacion = :ultima_actualizacion
                      WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':saldo_disponible' => $saldo_disponible,
                ':saldo_final' => $saldo_final,
                ':ultima_actualizacion' => $fechaAct,
                ':id' => $presupuestoId
            ]);

            // Registrar movimiento
            $fecha = date('Y-m-d H:i:s');
            $tipo_movimiento = 3; // Abono
            $comentario = 'abono';

            if ($this->addMovimientoPresupuesto($presupuestoId, $tipo_movimiento, $montoAbono, $id_usuario, $fecha, $comentario)) {
                $this->db->commit();
                return true;
            } else {
                throw new Exception('Error al registrar el movimiento del presupuesto.');
            }
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error en addFunds: " . $e->getMessage());
            return false;
        }
    }

}
?>