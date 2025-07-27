<?php 
include '../../app/load.php';
//error_reporting(0);
//// Initalize Slim
$app = new \Slim\Slim();

use PHPMailer\PHPMailer\PHPMailer;
require '../../PHPMailer/PHPMailer.php';
require '../../PHPMailer/SMTP.php';
require '../../PHPMailer/Exception.php';

$ga = new \Reportes\Reportes();

$mail = new PHPMailer();

 $mail->IsSMTP(); // enable SMTP
 $mail->SMTPDebug = 0; // debugging: 1 = errors and messages, 2 = messages only
 $mail->SMTPAuth = true; // authentication enabled
 

 //$mail->SMTPSecure = 'ssl'; // secure transfer enabled REQUIRED for Gmail
 $mail->SMTPSecure = 'tls'; // secure transfer enabled REQUIRED for Gmail
 //$mail->Host = "assistpro-adl.com";
 $mail->Host = "mail.assistpro-adl.com";
 $mail->Port = 587; // or 465
 $mail->IsHTML(true);
 $mail->Username = "system@assistpro-adl.com";
 $mail->Password = "y&#ulsux%_BU";
 //$mail->SetFrom("AssistPro WMS <system@assistpro-adl.com>");
 $mail->From         = 'system@assistpro-adl.com';
 $mail->FromName     = 'AssistPro WMS';


if( $_POST['action'] == 'email_entrada' ) 
{
//**************************************************
//require '../vendor/autoload.php';
//Create a new PHPMailer instance
$enlace = $_SERVER['HTTP_HOST'].$_POST['enlace'];
$usuario = $_POST['usuario'];
$folio = $_POST['folio'];
$imagen = $_SERVER['HTTP_HOST']."/api/emails/pdf.png";
 $mail->Subject = "Reporte de Entrada (".$folio.")";
 $mail->Body = "<b>Reporte de Entradas</b><br><br>
 Clic para Descargar el archivo<br><br>
 <br><br>
  <a href='$enlace' target='_blank'><img src='$imagen' width='50' /><br><b>Reporte de Entradas</b></a>";

          $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
          $sql = "
               SELECT email, nombre_completo FROM c_usuario WHERE cve_usuario = '$usuario'
           ";
           if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
           $row = mysqli_fetch_array($res);
           $email           = $row['email'];
           $nombre_completo = $row['nombre_completo'];


 $mail->AddAddress($email, $nombre_completo);
 //$mail->AddAddress("reinaldojose@hotmail.com", 'Reinaldo Matheus');
 //$mail->AddAddress("aolivares@gmx.com", 'AssistPro ADVL');

 
//send the message, check for errors

if (!$mail->send()) {
    echo 'Mailer Error: ' . $mail->ErrorInfo;
} else {
    $mail->ClearAllRecipients();//Mientras se recorre la misma instancia de correo, al inicio de cada iteración se debe de declarar para indicar al gestor que se trata de otro email y así sucesivamente
    echo 'Message sent!';
}
//*****************************************/
}

if( $_POST['action'] == 'email_existencias' ) 
{
//**************************************************
//require '../vendor/autoload.php';
//Create a new PHPMailer instance
$enlace = $_SERVER['HTTP_HOST'].$_POST['enlace'];
$correos = $_POST['correos'];
$usuario = $_POST['usuario'];
$imagen = $_SERVER['HTTP_HOST']."/api/emails/excel.png";

//$enlace = str_replace('http://', 'https://', $enlace);
// <br><br>
//  <a href='$enlace'><img src='$imagen' width='50' /><br><b>Reporte de Existencias</b></a><br>


  $sql = $ga->existenciaubica($_POST["almacen"],$_POST["articulo"], $_POST["zona"], $_POST['bl'], $_POST['contenedor'], $_POST['cve_proveedor'], $_POST['proveedor'], $_POST['grupo'], $_POST['clasificacion'], $_POST['lp'], $_POST['art_obsoletos'], $_POST['mostrar_folios_excel_existencias'], $_POST['existencia_cajas'], $_POST['lote'], $_POST['factura_oc'], '', $_POST['proyecto_existencias'], 1);

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $query = mysqli_query($conn, $sql);
    //$datos = mysqli_fetch_array($query, MYSQLI_ASSOC);
    $rows = array();
    while($row = mysqli_fetch_array($query, MYSQLI_ASSOC))
    {
        $rows[] = $row;
        //$datos = $row;
    }

$objPHPExcel = new PHPExcel();
$sheet = $objPHPExcel->getActiveSheet(); 
//Agregamos un texto a la celda Cabecera
$sheet->setCellValue('A1', 'Codigo BL');$sheet->getStyle('A1')->getFont()->setName('Tahoma')->setBold(true)->setSize(10);$sheet->getStyle('A1')->getBorders()->applyFromArray(array('allBorders' => 'thin')); 
$sheet->setCellValue('B1', 'Pallet|Cont');$sheet->getStyle('B1')->getFont()->setName('Tahoma')->setBold(true)->setSize(10);$sheet->getStyle('B1')->getBorders()->applyFromArray(array('allBorders' => 'thin')); 
$sheet->setCellValue('C1', 'License Plate (LP)');$sheet->getStyle('C1')->getFont()->setName('Tahoma')->setBold(true)->setSize(10);$sheet->getStyle('C1')->getBorders()->applyFromArray(array('allBorders' => 'thin')); 
$sheet->setCellValue('D1', 'Clave');$sheet->getStyle('D1')->getFont()->setName('Tahoma')->setBold(true)->setSize(10);$sheet->getStyle('D1')->getBorders()->applyFromArray(array('allBorders' => 'thin')); 
$sheet->setCellValue('E1', 'Clave Alterna');$sheet->getStyle('E1')->getFont()->setName('Tahoma')->setBold(true)->setSize(10);$sheet->getStyle('E1')->getBorders()->applyFromArray(array('allBorders' => 'thin')); 
$sheet->setCellValue('F1', 'CB Pieza');$sheet->getStyle('F1')->getFont()->setName('Tahoma')->setBold(true)->setSize(10);$sheet->getStyle('F1')->getBorders()->applyFromArray(array('allBorders' => 'thin')); 
$sheet->setCellValue('G1', 'Clasificacion');$sheet->getStyle('G1')->getFont()->setName('Tahoma')->setBold(true)->setSize(10);$sheet->getStyle('G1')->getBorders()->applyFromArray(array('allBorders' => 'thin')); 
$sheet->setCellValue('H1', 'Descripcion');$sheet->getStyle('H1')->getFont()->setName('Tahoma')->setBold(true)->setSize(10);$sheet->getStyle('H1')->getBorders()->applyFromArray(array('allBorders' => 'thin')); 
$sheet->setCellValue('I1', 'Lote | Serie');$sheet->getStyle('I1')->getFont()->setName('Tahoma')->setBold(true)->setSize(10);$sheet->getStyle('I1')->getBorders()->applyFromArray(array('allBorders' => 'thin')); 
$sheet->setCellValue('J1', 'Caducidad');$sheet->getStyle('J1')->getFont()->setName('Tahoma')->setBold(true)->setSize(10);$sheet->getStyle('J1')->getBorders()->applyFromArray(array('allBorders' => 'thin')); 
$sheet->setCellValue('K1', 'Unidad Medida');$sheet->getStyle('K1')->getFont()->setName('Tahoma')->setBold(true)->setSize(10);$sheet->getStyle('K1')->getBorders()->applyFromArray(array('allBorders' => 'thin')); 
$sheet->setCellValue('L1', 'Total');$sheet->getStyle('L1')->getFont()->setName('Tahoma')->setBold(true)->setSize(10);$sheet->getStyle('L1')->getBorders()->applyFromArray(array('allBorders' => 'thin')); 
$sheet->setCellValue('M1', 'RP');$sheet->getStyle('M1')->getFont()->setName('Tahoma')->setBold(true)->setSize(10);$sheet->getStyle('M1')->getBorders()->applyFromArray(array('allBorders' => 'thin')); 
$sheet->setCellValue('N1', 'Prod QA');$sheet->getStyle('N1')->getFont()->setName('Tahoma')->setBold(true)->setSize(10);$sheet->getStyle('N1')->getBorders()->applyFromArray(array('allBorders' => 'thin')); 
$sheet->setCellValue('O1', 'Disponible');$sheet->getStyle('O1')->getFont()->setName('Tahoma')->setBold(true)->setSize(10);$sheet->getStyle('O1')->getBorders()->applyFromArray(array('allBorders' => 'thin')); 
$sheet->setCellValue('P1', 'Costo Unitario');$sheet->getStyle('P1')->getFont()->setName('Tahoma')->setBold(true)->setSize(10);$sheet->getStyle('P1')->getBorders()->applyFromArray(array('allBorders' => 'thin')); 
$sheet->setCellValue('Q1', 'Fecha Ingreso');$sheet->getStyle('Q1')->getFont()->setName('Tahoma')->setBold(true)->setSize(10);$sheet->getStyle('Q1')->getBorders()->applyFromArray(array('allBorders' => 'thin')); 
$sheet->setCellValue('R1', 'Proyecto');$sheet->getStyle('R1')->getFont()->setName('Tahoma')->setBold(true)->setSize(10);$sheet->getStyle('R1')->getBorders()->applyFromArray(array('allBorders' => 'thin')); 
$sheet->setCellValue('S1', 'Grupo');$sheet->getStyle('S1')->getFont()->setName('Tahoma')->setBold(true)->setSize(10);$sheet->getStyle('S1')->getBorders()->applyFromArray(array('allBorders' => 'thin')); 
$sheet->setCellValue('T1', 'Proveedor');$sheet->getStyle('T1')->getFont()->setName('Tahoma')->setBold(true)->setSize(10);$sheet->getStyle('T1')->getBorders()->applyFromArray(array('allBorders' => 'thin')); 

    $i = 2;
    foreach($rows as $row)
    {
        $qa_exc   = '';if($row['QA']=='No') $qa_exc = $row['cantidad']; else $qa_exc = '';
        $rp_exc   = '';if($row['RP']!=0) $rp_exc = $row['RP']; else $rp_exc = '';
        $qa2_exc  = '';if($row['QA']=='Si') $qa2_exc = $row['cantidad']; else $qa2_exc = '';
        $disp_exc = ($row['cantidad']-$row['RP']);


        $sheet->setCellValue('A'.$i, $row['codigo']);
        $sheet->setCellValue('B'.$i, $row['contenedor']);
        $sheet->setCellValue('C'.$i, $row['LP']);
        $sheet->setCellValue('D'.$i, $row['clave']);
        $sheet->setCellValue('E'.$i, $row['clave_alterna']);
        $sheet->setCellValue('F'.$i, $row['codigo_barras_pieza']);
        $sheet->setCellValue('G'.$i, $row['des_clasif']);
        $sheet->setCellValue('H'.$i, $row['descripcion']);
        $sheet->setCellValue('I'.$i, $row['lote']);
        $sheet->setCellValue('J'.$i, $row['caducidad']);
        $sheet->setCellValue('K'.$i, $row['um']);
        $sheet->setCellValue('L'.$i, $qa_exc);
        $sheet->setCellValue('M'.$i, $rp_exc);
        $sheet->setCellValue('N'.$i, $qa2_exc);
        $sheet->setCellValue('O'.$i, $disp_exc);
        $sheet->setCellValue('P'.$i, $row['costoUnitario']);
        $sheet->setCellValue('Q'.$i, $row['fecha_ingreso']);
        $sheet->setCellValue('R'.$i, $row['proyecto']);
        $sheet->setCellValue('S'.$i, $row['des_grupo']);
        $sheet->setCellValue('T'.$i, $row['proveedor']);

        $i++;

    }

//exportamos nuestro documento 
$writer = new PHPExcel_Writer_Excel2007($objPHPExcel); 
$writer->save('Reporte de Existencias.xlsx'); 


/*
$objPHPExcel = new PHPExcel();
$sheet = $objPHPExcel->getActiveSheet(); 
//Agregamos un texto a la celda A1 
$sheet->setCellValue('A1', 'Prueba');
//Damos formato o estilo a nuestra celda 
$sheet->getStyle('A1')->getFont()->setName('Tahoma')->setBold(true)->setSize(8); 
$sheet->getStyle('A1')->getBorders()->applyFromArray(array('allBorders' => 'thin')); 
$sheet->getStyle('A1')->getAlignment()->setVertical('center')->setHorizontal('center'); 
$sheet->setCellValue('B1', 'PHPExcel'); 
//usamos los mismos estilos de A1 
$sheet->getStyle('B1')->getFont()->setName('Tahoma')->setBold(true)->setSize(8); 
$sheet->getStyle('B1')->getBorders()->applyFromArray(array('allBorders' => 'thin')); 
$sheet->getStyle('B1')->getAlignment()->setVertical('center')->setHorizontal('center'); 
//exportamos nuestro documento 
$writer = new PHPExcel_Writer_Excel2007($objPHPExcel); 
$writer->save('prueba.xlsx'); 
*/
//////////////////////////////////////////////////////////////////
$archivo = 'Reporte de Existencias.xlsx';
$datos_binarios = file_get_contents($archivo);
$datos_base64 = base64_encode($datos_binarios);

//if(is_array($correos))
//{
//    $correos = implode(",", $correos);
//    $correos = "'".$correos."'";
//    $correos = str_replace(",", "','", $correos);
//}

if(!is_array($correos))
{
$correoData = [
    "correos" => [$correos],
    "asunto" => "Reporte de Existencias",
    "cuerpo" => "<b>Reporte de Existencias</b><br><br>
                    Clic para Descargar el archivo<br><br>", // puede ser texto plano o un html construido
    "tipo" => "html", // puede ser html o text
    "attachments" => [
        [
            "base64" => $datos_base64,
            "nombre" => "Reporte de Existencias.xlsx"
        ]
    ]
];

// Convertir array a JSON
$json_data = json_encode($correoData);
 
// Inicializar cURL
$ch = curl_init();
 
 $url = 'https://wswms.assistpro-adl.com/api/servicios-generales/envio-correos';
// Configurar opciones de cURL
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($json_data)
]);
}
else
{
    for($i = 0; $i < count($correos); $i++)
    {
        $correoData = [
            "correos" => [$correos[$i]],
            "asunto" => "Reporte de Existencias",
            "cuerpo" => "<b>Reporte de Existencias</b><br><br>
                            Clic para Descargar el archivo<br><br>", // puede ser texto plano o un html construido
            "tipo" => "html", // puede ser html o text
            "attachments" => [
                [
                    "base64" => $datos_base64,
                    "nombre" => "Reporte de Existencias.xlsx"
                ]
            ]
        ];

        // Convertir array a JSON
        $json_data = json_encode($correoData);
         
        // Inicializar cURL
        $ch = curl_init();
         
         $url = 'https://wswms.assistpro-adl.com/api/servicios-generales/envio-correos';
        // Configurar opciones de cURL
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($json_data)
        ]);
    }

}
// Ejecutar la solicitud
$response = curl_exec($ch);
 
// Verificar si hubo errores
if(curl_errno($ch)) {
    echo 'Error cURL: ' . curl_error($ch);
} else {
    // Mostrar la respuesta
    echo $response;
}
 
// Cerrar la sesión cURL
curl_close($ch);

/*
$destinatarios = $correoData["correos"];
$asunto = $correoData["asunto"];
$cuerpo = $correoData["cuerpo"];
$tipo = $correoData["tipo"];
$attachments = $correoData["attachments"];

if(is_array($correos))
{
    for($i = 0; $i < count($correos); $i++)
    {
        $mail->AddAddress($correos[$i], $usuario[$i]);
         
        if (!$mail->send()) {
            echo 'Mailer Error: ' . $mail->ErrorInfo;
        } else {
            $mail->ClearAllRecipients();//Mientras se recorre la misma instancia de correo, al inicio de cada iteración se debe de declarar para indicar al gestor que se trata de otro email y así sucesivamente
            echo 'Message sent!'.$correos[$i];
        }
    }
}
else 
{
    $mail->AddAddress($correos, $usuario);
    $mail->addAttachment('Reporte de Existencias.xlsx');
    if (!$mail->send()) {
        echo 'Mailer Error: ' . $mail->ErrorInfo;
    } else {
        $mail->ClearAllRecipients();//Mientras se recorre la misma instancia de correo, al inicio de cada iteración se debe de declarar para indicar al gestor que se trata de otro email y así sucesivamente
        echo 'Message sent!!!';
    }
}
*/
/*
     $mail->Subject = $asunto;
     $mail->Body = $cuerpo;

    $mail->isHTML(true);

    $mail->AddAddress($destinatarios, "");//$usuario
    $mail->addAttachment($archivo);
    //$mail->AddStringAttachment($attachments);
    //$attach_parameter = explode(",", $attachments);
    //$mail->AddStringAttachment($attachments, $attach_parameter[0], $attach_parameter[1], $attach_parameter[2]);
    if (!$mail->send()) {
        echo 'Mailer Error: ' . $mail->ErrorInfo;
    } else {
        $mail->ClearAllRecipients();//Mientras se recorre la misma instancia de correo, al inicio de cada iteración se debe de declarar para indicar al gestor que se trata de otro email y así sucesivamente
        echo 'Message sent!!!';
    }
*/


//*****************************************/
}

if( $_POST['action'] == 'get_correos_existencias') 
{
    $caso                 = $_POST['caso'];
    $cve_cia              = $_POST['cve_cia'];
    $proveedor            = $_POST['proveedor'];
    $proyecto_existencias = $_POST['proyecto_existencias'];

    //caso = 1 (Sin filtros): Envio a correo de la empresa firmada
    //caso = 2 (Empresa seleccionada): Envio a correo del cliente seleccionado
    //caso = 3 (Proyecto Seleccionado): Envio a correo de los clientes pertenecientes al proyecto
    //caso = 4: No hay correos disponibles para enviar
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    if($caso == 1)
    {
      $sql = "
           SELECT des_contacto AS contacto, des_email AS email FROM c_compania WHERE cve_cia = $cve_cia
       ";
       if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
       $row = mysqli_fetch_array($res);
       $contacto = $row['contacto'];
       $email    = $row['email'];

       if(filter_var($email, FILTER_VALIDATE_EMAIL))
       {
          $arr = array(
            "data" => $contacto.";;;;;".$email
          );
          echo json_encode($arr);
       }
       else 
       {
          $arr = array(
            "data" => "formato_email_incorrecto;;;;;".$email
          );
          echo json_encode($arr);
       }
    }
    else //if($caso == 2)
    {
      $sql = "
           SELECT RazonSocial AS contacto, REPLACE(email_cliente, ' ', '') AS email FROM c_cliente WHERE ID_Proveedor = $proveedor
       ";
       if($caso == 3)
          $sql = "
               SELECT DISTINCT RazonSocial AS contacto, REPLACE(email_cliente, ' ', '') AS email FROM c_cliente WHERE IFNULL(email_cliente, '') != '' AND ID_Proveedor IN (SELECT DISTINCT id_proveedor FROM t_trazabilidad_existencias WHERE proyecto = '$proyecto_existencias' AND idy_ubica IS NOT NULL)
           ";
       if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}

       $contacto_arr = array();
       $email_arr = array();

       while($row = mysqli_fetch_array($res))
       {
           if($row['email'])
           {
                $separar_correos = explode(";", $row['email']);
                $correo_sin_formato = "";
               if(count($separar_correos) > 0)
               {
                    for($i = 0; $i < count($separar_correos); $i++)
                    {
                       if(!filter_var($separar_correos[$i], FILTER_VALIDATE_EMAIL))
                       {
                            $correo_sin_formato = $separar_correos[$i];
                            break;
                       }
                    }
               }

               if((!filter_var($row['email'], FILTER_VALIDATE_EMAIL) && count($separar_correos) == 0) || $correo_sin_formato)
               {
                  $arr = array(
                    "data" => "formato_email_incorrecto;;;;;".$row['email'],
                    "contactos" => "",
                    "correos" => ""
                  );
                  if($correo_sin_formato)
                      $arr = array(
                        "data" => "formato_email_incorrecto;;;;;".$correo_sin_formato,
                        "contactos" => "",
                        "correos" => ""
                      );

                  echo json_encode($arr);
                  exit;
               }
               else 
               {
                   $separar_correos = explode(";", $row['email']);
                   if(count($separar_correos) == 0)
                   {
                       $contacto_arr[] = $row['contacto'];
                       $email_arr[]    = $row['email'];
                   }
                   else
                   {
                        for($i = 0; $i < count($separar_correos); $i++)
                        {
                           $contacto_arr[] = $row['contacto'];
                           $email_arr[]    = $separar_correos[$i];
                        }
                   }
               }
           }
       }

      $arr = array(
        "data" => "OK",
        "contactos" => $contacto_arr,
        "correos" => $email_arr
      );
       echo json_encode($arr);

    }


}


 ?>