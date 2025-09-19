<?php

require_once 'src/libs/PhpOffice/PhpWord/Autoloader.php';
\PhpOffice\PhpWord\Autoloader::register();
use PhpOffice\PhpWord\TemplateProcessor;

class DocumentService { 
    public function generarDesdePlantilla($datos) {
        $template = new TemplateProcessor('src/templates/Anexo 1.docx');

        // Llenar dinámicamente todos los valores
        foreach ($datos as $clave => $valor) {
        $template->setValue($clave, $valor); } $nombreDescarga = 'Doc. Autorizacion_Anticipo_' . $datos['id_anticipo'] . '.docx';

        // Guardar temporal
        $tempFile = sys_get_temp_dir() . '/' . $nombreDescarga; $template->saveAs($tempFile);

        // Descargar
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=\"$nombreDescarga\"");
        header("Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document");
        readfile($tempFile); unlink($tempFile);
        exit;
    }

// require_once 'src/libs/PhpOffice/PhpWord/Autoloader.php';
// \PhpOffice\PhpWord\Autoloader::register();

// require_once 'src/libs/dompdf/autoload.inc.php';
// use PhpOffice\PhpWord\TemplateProcessor;
// use PhpOffice\PhpWord\IOFactory;
// use Dompdf\Dompdf;

// class DocumentService {
//     public function generarDesdePlantilla($datos) {
//         $template = new TemplateProcessor('src/templates/Anexo 1.docx');

//         // Llenar dinámicamente los valores
//         foreach ($datos as $clave => $valor) {
//             $template->setValue($clave, $valor);
//         }

//         // Guardar como Word temporal
//         $tempDocx = sys_get_temp_dir() . '/temp_' . uniqid() . '.docx';
//         $template->saveAs($tempDocx);

//         // Cargar el DOCX en PhpWord
//         $phpWord = IOFactory::load($tempDocx);

//         // Definir PDF temporal
//         $tempPdf = sys_get_temp_dir() . '/temp_' . uniqid() . '.pdf';

//         // Guardar como PDF usando DomPDF
//         \PhpOffice\PhpWord\Settings::setPdfRendererPath('src/libs/dompdf');
//         \PhpOffice\PhpWord\Settings::setPdfRendererName('DomPDF');
//         $pdfWriter = IOFactory::createWriter($phpWord, 'PDF');
//         $pdfWriter->save($tempPdf);

//         // Descargar el PDF
//         $nombreDescarga = 'Autorizacion_' . $datos['id_anticipo'] . '.pdf';
//         header("Content-Description: File Transfer");
//         header("Content-Disposition: attachment; filename=\"$nombreDescarga\"");
//         header("Content-Type: application/pdf");
//         readfile($tempPdf);

//         // Eliminar archivos temporales
//         unlink($tempDocx);
//         unlink($tempPdf);
//         exit;
//     }
}