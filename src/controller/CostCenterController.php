<?php
require_once 'src/config/Database.php';
require_once 'src/models/CostCenterModel.php';

class CostCenterController {
    private $db;
    private $costCenterModel;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
        $this->costCenterModel = new CostCenterModel();
    }

    public function index() {
        if (!isset($_SESSION['dni'])) {
            header('Location: /proy_anticipos_rendiciones/iniciar_sesion');
            exit;
        }
        
        // Obtener datos para las tablas
        $cc_data = $this->costCenterModel->getCcData();
        $scc_data = $this->costCenterModel->getSccData();
        $sscc_data = $this->costCenterModel->getSsccData();

        // Obtener listas para los dropdowns
        $cc_list = $this->costCenterModel->getCcList();
        $scc_list = $this->costCenterModel->getSccList();

        require_once 'src/views/cost_center.php';
    }

    // función para crear un centro de costo
    public function add_cc() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $codigo = trim($_POST['codigo'] ?? '');
            $nombre = trim($_POST['nombre'] ?? '');
            $nombre_corto = trim($_POST['nombre_corto'] ?? '');
            $activo = isset($_POST['activo']) ? 1 : 0;
            // expresión regular que se utilizará para validar campos del formulario
            $val_cod = '/^[a-zA-Z0-9_-]{4,}$/';
            $val_nom = '/^[a-zA-Z0-9., _-]{4,}$/';

            if (empty($codigo) || empty($nombre) || empty($nombre_corto)) {
                // Se valida que no ingresen campos vacíos
                $_SESSION['error'] = 'Todos los campos son obligatorios.';
            } elseif ($this->costCenterModel->checkCcCode($codigo)) {
                // Se utiliza la función de revisar el código para que no existan dos cc con el mismo código
                $_SESSION['error'] = 'No pueden haber dos CC con el mismo código.';
            } elseif (!preg_match($val_cod, $codigo) || !preg_match($val_nom, $nombre) || !preg_match($val_cod, $nombre_corto)){
                // Se utiliza la expresión regular anteriormente creada para revisar que los datos ingresados no tengan caracteres especiales y tengan 4 o más caracteres
                $_SESSION['error'] = 'Revise que los datos ingresados no posean símbolos y cuenten con 4 caracteres como mínimo.';
            } else {
                if ($this->costCenterModel->addCc($codigo, $nombre, $nombre_corto, $activo)) {
                    $_SESSION['success'] = 'Centro de costo agregado correctamente.';
                } else {
                    $_SESSION['error'] = 'Error al agregar el centro de costo.';
                }
            }
            header('Location: /proy_anticipos_rendiciones/centro_costos');
            exit;
        }
    }

    // función para crear un sub centro de costo
    public function add_scc() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $codigo = trim($_POST['codigo'] ?? '');
            $nombre = trim($_POST['nombre'] ?? '');
            $nombre_corto = trim($_POST['nombre_corto'] ?? '');
            $activo = isset($_POST['activo']) ? 1 : 0;
            $cc_codigo = $_POST['cc_codigo'] ?? '';

            // expresión regular que se utilizará para validar campos del formulario
            $val_cod = '/^[a-zA-Z0-9_-]{4,}$/';
            $val_nom = '/^[a-zA-Z0-9., _-]{4,}$/';

            if (empty($codigo) || empty($nombre) || empty($nombre_corto) || empty($cc_codigo)) {
                // Se valida que no ingresen campos vacíos
                $_SESSION['error'] = 'Todos los campos son obligatorios.';
            } elseif ($this->costCenterModel->checkSccCode($codigo)) {
                // Se utiliza la función de revisar el código para que no existan dos cc con el mismo código
                $_SESSION['error'] = 'No pueden existir dos SCC con el mismo código.';
            } elseif (!preg_match($val_cod, $codigo) || !preg_match($val_nom, $nombre) || !preg_match($val_cod, $nombre_corto)){
                // Se utiliza la expresión regular anteriormente creada para revisar que los datos ingresados no tengan caracteres especiales y tengan 4 o más caracteres
                $_SESSION['error'] = 'Revise que los datos ingresados no posean símbolos y cuenten con 4 caracteres como mínimo.';
            } else {
                if ($this->costCenterModel->addScc($codigo, $nombre, $nombre_corto, $activo, $cc_codigo)) {
                    $_SESSION['success'] = 'Subcentro de costo agregado correctamente.';
                } else {
                    $_SESSION['error'] = 'Error al agregar el subcentro de costo.';
                }
            }
            header('Location: /proy_anticipos_rendiciones/centro_costos');
            exit;
        }
    }

    // función para crear un sub sub-centro de costo
    public function add_sscc() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $codigo = trim($_POST['codigo'] ?? '');
            $nombre = trim($_POST['nombre'] ?? '');
            $nombre_corto = trim($_POST['nombre_corto'] ?? '');
            $activo = isset($_POST['activo']) ? 1 : 0;
            $scc_codigo = $_POST['scc_codigo'] ?? '';

            // expresión regular que se utilizará para validar campos del formulario
            $val_cod = '/^[a-zA-Z0-9_-]{4,}$/';
            $val_nom = '/^[a-zA-Z0-9., _-]{4,}$/';

            if (empty($codigo) || empty($nombre) || empty($nombre_corto) || empty($scc_codigo)) {
                // Se valida que no ingresen campos vacíos
                $_SESSION['error'] = 'Todos los campos son obligatorios.';
            } elseif ($this->costCenterModel->checkSsccCode($codigo)) {
                // Se utiliza la función de revisar el código para que no existan dos cc con el mismo código
                $_SESSION['error'] = 'No pueden existir dos SSCC con el mismo código.';
            } elseif (!preg_match($val_cod, $codigo) || !preg_match($val_nom, $nombre) || !preg_match($val_cod, $nombre_corto)){
                // Se utiliza la expresión regular anteriormente creada para revisar que los datos ingresados no tengan caracteres especiales y tengan 4 o más caracteres
                $_SESSION['error'] = 'Revise que los datos ingresados no posean símbolos y cuenten con 4 caracteres como mínimo.';
            } else {
                if ($this->costCenterModel->addSscc($codigo, $nombre, $nombre_corto, $activo, $scc_codigo)) {
                    $_SESSION['success'] = 'Sub-subcentro de costo agregado correctamente.';
                } else {
                    $_SESSION['error'] = 'Error al agregar el sub-subcentro de costo.';
                }
            }
            header('Location: /proy_anticipos_rendiciones/centro_costos');
            exit;
        }
    }

    // función para editar un cc
    public function edit_cc() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $codigo = trim($_POST['codigo'] ?? '');
            $nombre = trim($_POST['nombre'] ?? '');
            $activo = isset($_POST['activo']) ? 1 : 0;

            // expresión regular que se utilizará para validar campos del formulario
            $val_texto = '/^[a-zA-Z0-9., _-]{4,}$/';

            if (empty($nombre)) {
                $_SESSION['error'] = 'El nombre es obligatorio.';
            } elseif (!preg_match($val_texto, $nombre)){
                // Se utiliza la expresión regular anteriormente creada para revisar que los datos ingresados no tengan caracteres especiales y tengan 4 o más caracteres
                $_SESSION['error'] = 'Revise que los datos ingresados no posean símbolos y cuenten con 4 caracteres como mínimo.';
            } else {
                if ($this->costCenterModel->updateCc($codigo, $nombre, $activo)) {
                    $_SESSION['success'] = 'Centro de costo actualizado correctamente.';
                } else {
                    $_SESSION['error'] = 'Error al actualizar el centro de costo.';
                }
            }
            header('Location: /proy_anticipos_rendiciones/centro_costos');
            exit;
        }
    }

    // función para editar un scc
    public function edit_scc() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $codigo = trim($_POST['codigo'] ?? '');
            $nombre = trim($_POST['nombre'] ?? '');
            $activo = isset($_POST['activo']) ? 1 : 0;
            $cc_codigo = $_POST['cc_codigo'] ?? '';

            // expresión regular que se utilizará para validar campos del formulario
            $val_texto = '/^[a-zA-Z0-9., _-]{4,}$/';

            if (empty($nombre)) {
                $_SESSION['error'] = 'El nombre es obligatorio.';
            } elseif (!preg_match($val_texto, $nombre)){
                // Se utiliza la expresión regular anteriormente creada para revisar que los datos ingresados no tengan caracteres especiales y tengan 4 o más caracteres
                $_SESSION['error'] = 'Revise que los datos ingresados no posean símbolos y cuenten con 4 caracteres como mínimo.';
            } else {
                if ($this->costCenterModel->updateScc($codigo, $nombre, $activo, $cc_codigo)) {
                    $_SESSION['success'] = 'Subcentro de costo actualizado correctamente.';
                } else {
                    $_SESSION['error'] = 'Error al actualizar el subcentro de costo.';
                }
            }
            header('Location: /proy_anticipos_rendiciones/centro_costos');
            exit;
        }
    }

    // función para editar un sscc
    public function edit_sscc() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $codigo = trim($_POST['codigo'] ?? '');
            $nombre = trim($_POST['nombre'] ?? '');
            $activo = isset($_POST['activo']) ? 1 : 0;
            $scc_codigo = $_POST['scc_codigo'] ?? '';

            // expresión regular que se utilizará para validar campos del formulario
            $val_texto = '/^[a-zA-Z0-9., _-]{4,}$/';

            if (empty($nombre) || empty($scc_codigo)) {
                $_SESSION['error'] = 'El nombre y el subcentro de costo son obligatorios.';
            } elseif (!preg_match($val_texto, $nombre)){
                // Se utiliza la expresión regular anteriormente creada para revisar que los datos ingresados no tengan caracteres especiales y tengan 4 o más caracteres
                $_SESSION['error'] = 'Revise que los datos ingresados no posean símbolos y cuenten con 4 caracteres como mínimo.';
            } else {
                if ($this->costCenterModel->updateSscc($codigo, $nombre, $activo, $scc_codigo)) {
                    $_SESSION['success'] = 'Sub-subcentro de costo actualizado correctamente.';
                } else {
                    $_SESSION['error'] = 'Error al actualizar el sub-subcentro de costo.';
                }
            }
            header('Location: /proy_anticipos_rendiciones/centro_costos');
            exit;
        }
    }
}