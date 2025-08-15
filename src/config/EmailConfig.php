<?php
require_once 'src/libs/PHPMailer/PHPMailer.php';
require_once 'src/libs/PHPMailer/Exception.php';
require_once 'src/libs/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailConfig {
    private $mail;

    public function __construct() {
        $this->mail = new PHPMailer(true);
        $this->mail->isSMTP();
        $this->mail->Host = ''; // host según proveedor - editable
        $this->mail->SMTPAuth = true;
        $this->mail->Username = ''; // correo - editable
        $this->mail->Password = ''; // contraseña - editable
        $this->mail->SMTPSecure = 'ssl';
        $this->mail->Port = 465;
        $this->mail->setFrom('', 'SIAR - TECHING');
        $this->mail->CharSet = 'UTF-8';
        $this->mail->isHTML(true); // Habilitar HTML en el cuerpo
    }

    public function sendSiarNotification($to, $subject, $body, $cc = []) {
        try {
            $this->mail->addAddress($to);
            // Agregar aprobadores en copia
            foreach ($cc as $ccEmail) {
                if (filter_var($ccEmail, FILTER_VALIDATE_EMAIL)) {
                    $this->mail->addCC($ccEmail);
                } else {
                    error_log("Correo inválido en CC: $ccEmail");
                }
            }
            $this->mail->Subject = $subject;
            $this->mail->Body = $body;
            $this->mail->send();
            error_log("Correo enviado");
            return true;
        } catch (Exception $e) {
            error_log("Error al enviar correo: {$this->mail->ErrorInfo}");
            return false;
        } finally {
            $this->mail->clearAddresses();
            $this->mail->clearCCs(); // Limpiar CCs para evitar duplicado
        }
    }
}