<?php

error_reporting(0);
define( 'NO_LOGIN', true );
include 'app/load.php';

//$user = new \User\User();
//$auth = new \User\Auth();

if(isset($_POST['usuario_recuperacion']))
{
/*
    require("SendGrid/sendgrid-php.php");

    $email = new \SendGrid\Mail\Mail();
    $email->setFrom("admin@adventech-logistica.com", "AssitPro ADVL");
    $email->setSubject("Recuperación de Contraseña");
    //$email->addTo("reinalddo@gmail.com", "Reinaldo Matheus");
    $email->addTo("reinalddo@gmail.com", "Reinaldo Matheus");
    //$email->addContent("text/plain", "Mensaje en Texto Plano");
    //$email->addContent("text/html", "<strong>Mensaje Con HTML</strong>");

    $html_email = '<!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Correo AssitPro WMS</title>
    </head>
    <body>
    <strong>Recuperación de Contraseña</strong>
    <br><br>
    Puede Reiniciar Su contraseña en el siguiente enlace: 
    <br><br>
    </body>
    </html>';

    $email->addContent("text/html", $html_email);
    //$sendgrid = new \SendGrid(getenv('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'));
    $apiKey = ("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");
    $sendgrid = new \SendGrid($apiKey);

    try {
        $response = $sendgrid->send($email);
        //print $response->statusCode() . "\n";
        //print_r($response->headers());
        //print $response->body() . "\n";
    } catch (Exception $e) {
        echo 'Caught exception: '. $e->getMessage() ."\n";
    }
*/
    //ini_set( 'display_errors', 1 );

$para      = 'reinalddo@gmail.com';
$titulo    = 'Recuperación de Contraseña';
$mensaje   = '<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Correo AssitPro WMS</title>
    </head>
    <body>
    <strong>Recuperación de Contraseña</strong>
    <br><br>
    Puede Reiniciar Su contraseña en el siguiente enlace: 
    <br><br>
    </body>
    </html>';
$cabeceras  = 'MIME-Version: 1.0' . "\r\n";
$cabeceras .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

// Cabeceras adicionales
$cabeceras .= 'To: Reinaldo <reinalddo@gmail.com>' . "\r\n";
$cabeceras .= 'From: ADVL <reinalddo@gmail.com>' . "\r\n";
//$cabeceras .= 'Cc: ' . "\r\n";
//$cabeceras .= 'Bcc: ' . "\r\n";

if(mail($para, $titulo, $mensaje, $cabeceras))
    echo "<script>alert('Enviado');</script>";
else
    echo "<script>alert('NO Enviado');</script>";


}

if(isset($_POST['ingreso_sap']))
{

    $curl = curl_init();

    curl_setopt_array($curl, array(

  CURLOPT_URL => 'https://54.158.67.33:50000/b1s/v1/Login',

  CURLOPT_RETURNTRANSFER => true,

  CURLOPT_ENCODING => '',

  CURLOPT_MAXREDIRS => 10,

  CURLOPT_TIMEOUT => 0,

  CURLOPT_FOLLOWLOCATION => true,

  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,

  CURLOPT_CUSTOMREQUEST => 'POST',

  CURLOPT_POSTFIELDS =>'{
    "CompanyDB": "SANDBOX_MEXICO",
    "UserName": "WhsUser",
    "Password": "Welcome1"
    }',

  CURLOPT_HTTPHEADER => array(

    'Content-Type: text/plain',

    'Cookie: B1SESSION=e148fc02-6d94-11ec-8000-0a244a1700f3; ROUTEID=.node2'

  ),

  CURLOPT_SSL_VERIFYHOST => false,
  
  CURLOPT_SSL_VERIFYPEER => false,

));

$response = curl_exec($curl);

    echo var_dump($response);
 curl_close($curl);

/*
$data = json_encode(array(
  'CompanyDB'=> 'SANDBOX_MEXICO',
  'UserName'=> 'WhsUser',
  'Password'=> 'Welcome1'
));
*/
/*
$Id = '.node2';
$data = '{
  "CompanyDB"=> "SANDBOX_MEXICO",
  "UserName"=> "WhsUser",
  "Password"=> "Welcome1"
}';
$header =  array(
    'Content-Type: text/plain',
    'Cookie: B1SESSION=e148fc02-6d94-11ec-8000-0a244a1700f3; ROUTEID=.node2'
  );
$url = 'https://54.158.67.33:50000/b1s/v1/Login';
//$url = 'https://localhost:50000/b1s/v1/Login';

$ch = curl_init(); 
curl_setopt($ch, CURLOPT_URL,$url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
$output = curl_exec($ch);
curl_close($ch);
echo var_dump($output);
*/
/*
$Id = '.node2';
$url = 'https://54.158.67.33:50000/b1s/v1/Login';
  $getLogin = curl_init($url.$Id."?apikey=API_KEY&source=MY_SOURCE&format=json");
    curl_setopt($getLogin, CURLOPT_POST, 0);
    curl_setopt($getLogin, CURLOPT_HTTPGET, true);
    curl_setopt($getLogin, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($getLogin, CURLOPT_TIMEOUT, 5);
    curl_setopt($getLogin, CURLOPT_CONNECTTIMEOUT, 5);
    $result = curl_exec($getLogin);
    return var_dump($getLogin);
    curl_close($getLogin);
*/
/*
require_once 'HTTP/Request2.php';
$request = new HTTP_Request2();
$request->setUrl('https://54.158.67.33:50000/b1s/v1/Login');
$request->setMethod(HTTP_Request2::METHOD_POST);
$request->setConfig(array(
  'follow_redirects' => TRUE
));
$request->setHeader(array(
  'Content-Type' => 'text/plain',
  'Cookie' => 'B1SESSION=e148fc02-6d94-11ec-8000-0a244a1700f3; ROUTEID=.node2'
));
$request->setBody('{
\n"CompanyDB": "SANDBOX_MEXICO",
\n"UserName": "WhsUser",
\n"Password": "Welcome1"
\n}');
try {
  $response = $request->send();
  if ($response->getStatus() == 200) {
    echo $response->getBody();
  }
  else {
    echo 'Unexpected HTTP status: ' . $response->getStatus() . ' ' .
    $response->getReasonPhrase();
  }
}
catch(HTTP_Request2_Exception $e) {
  echo 'Error: ' . $e->getMessage();
}
*/
}

function getRealIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP']))
        return $_SERVER['HTTP_CLIENT_IP'];
       
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
   
    return $_SERVER['REMOTE_ADDR'];
}


$mostrar_select = false; $loguear = false;
if( $_POST['action'] == 'login' ) {

    try {
        $pdo = \db();
        $pdo->beginTransaction();
        $pdo = null;

        /*$result = $auth->authByEmail(array(
            'email' => $_POST['email']
        , 'password' => $_POST['password']
        ));*/
        $almacen_unico = "";$sin_almacen = false; $almacen_bitacora = "";
        $sql = "SELECT * FROM trel_us_alm Where cve_usuario=:usuario AND Activo = 1;";
        //$result = mysqli_query(\db2(), $sql);
        //$result = (\db2(), $sql);
        //$result = $conn->prepare($sql);
        $pdo = \db();
        $result = $pdo->prepare($sql);
        $result->execute(array('usuario' => $_POST['email']));
        $pdo = null;
        //$result->execute();
        //if(mysqli_num_rows($result) > 1) $mostrar_select = true;
        //else if(mysqli_num_rows($result) == 1){
        //echo $result->rowCount()."--";
            if($result->rowCount() > 1) $mostrar_select = true;
            else if($result->rowCount() == 1){
            $row = $result->fetch();
            $almacen_unico = $row["cve_almac"];
            $almacen_bitacora = $almacen_unico;
            //$mostrar_select = false;
            $mostrar_select = true;
        }else
            $sin_almacen = true;

        //$sql = "SELECT * FROM c_usuario Where cve_usuario='" . $_POST['email'] . "' And pwd_usuario='".$_POST['password']."';";
        $sql = "SELECT * FROM c_usuario Where cve_usuario=:usuario And pwd_usuario=:pwssd;";
        //$result = mysqli_query(\db2(), $sql);
        $pdo = \db();
        $result = $pdo->prepare($sql);
        $result->execute(array('usuario' => $_POST['email'], 'pwssd'=> $_POST['password']));
        $pdo = null;

        $_arr = array();


        if(isset($_POST['select_almacenes']))
        {
            if($_POST['select_almacenes'] != "")
            {
                $clave_almacen = $_POST['select_almacenes'];
                //echo $clave_almacen."--------";

                //$sql = "SELECT id_user from t_usu_alm_pre where id_user = (SELECT id_user FROM c_usuario WHERE cve_usuario = '" . $_POST['email'] . "' LIMIT 1)";
                $sql = "SELECT id_user from t_usu_alm_pre where id_user = (SELECT id_user FROM c_usuario WHERE cve_usuario = :usuario LIMIT 1)";
                $pdo = \db();
                $result2 = $pdo->prepare($sql);
                $result2->execute(array('usuario' => $_POST['email']));
                $pdo = null;
                //$result2 = mysqli_query(\db2(), $sql);
                if($result2->rowCount() == 0) 
                {
                    //$sql = "INSERT INTO t_usu_alm_pre(id_user, cve_almac) VALUES((SELECT id_user FROM c_usuario WHERE cve_usuario = '" . $_POST['email'] . "' LIMIT 1), '{$clave_almacen}')";
                    $sql = "INSERT INTO t_usu_alm_pre(id_user, cve_almac) VALUES((SELECT id_user FROM c_usuario WHERE cve_usuario = :usuario LIMIT 1), :clave_almacen)";
                }
                else 
                {
                    //$sql = "UPDATE t_usu_alm_pre SET cve_almac = '{$clave_almacen}' WHERE id_user = (SELECT id_user FROM c_usuario WHERE cve_usuario = '" . $_POST['email'] . "' LIMIT 1);";
                    //echo $sql;
                    $sql = "UPDATE t_usu_alm_pre SET cve_almac = :clave_almacen WHERE id_user = (SELECT id_user FROM c_usuario WHERE cve_usuario = :usuario LIMIT 1);";
                }
                //$result2 = mysqli_query(\db2(), $sql);
                $pdo = \db();
                $result2 = $pdo->prepare($sql);
                $result2->execute(array('clave_almacen' => $clave_almacen, 'usuario' => $_POST['email']));
                $pdo = null;
                $mostrar_select = false;
                $loguear = true;
                $almacen_bitacora = $clave_almacen;

                if(isset($_POST['select_proveedores']))
                {
                    $_SESSION['id_proveedor'] = $_POST['select_proveedores'];
                }

            }
        }
        else if($mostrar_select == false)
        {
            //$sql = "SELECT id_user from t_usu_alm_pre where id_user = (SELECT id_user FROM c_usuario WHERE cve_usuario = '" . $_POST['email'] . "' LIMIT 1)";
            $sql = "SELECT id_user from t_usu_alm_pre where id_user = (SELECT id_user FROM c_usuario WHERE cve_usuario = :usuario LIMIT 1)";
            $pdo = \db();
            $result2 = $pdo->prepare($sql);
            $result2->execute(array('usuario' => $_POST['email']));
            $pdo = null;
            //$result2 = mysqli_query(\db2(), $sql);
            //if(mysqli_num_rows($result2) == 0) 
            if($result2->rowCount() == 0) 
            {
                //$sql = "INSERT INTO t_usu_alm_pre(id_user, cve_almac) VALUES((SELECT id_user FROM c_usuario WHERE cve_usuario = '" . $_POST['email'] . "' LIMIT 1), '{$almacen_unico}')";
                $sql = "INSERT INTO t_usu_alm_pre(id_user, cve_almac) VALUES((SELECT id_user FROM c_usuario WHERE cve_usuario = :usuario LIMIT 1), :almacen_unico)";
            }
            else 
            {
                //$sql = "UPDATE t_usu_alm_pre SET cve_almac = '{$almacen_unico}' WHERE id_user = (SELECT id_user FROM c_usuario WHERE cve_usuario = '" . $_POST['email'] . "' LIMIT 1);";
                $sql = "UPDATE t_usu_alm_pre SET cve_almac = :almacen_unico WHERE id_user = (SELECT id_user FROM c_usuario WHERE cve_usuario = :usuario LIMIT 1);";
            }
            //$result2 = mysqli_query(\db2(), $sql);
            $pdo = \db();
            $result2 = $pdo->prepare($sql);
            $result2->execute(array('usuario' => $_POST['email'], 'almacen_unico' => $almacen_unico));
            $pdo = null;
            $mostrar_select = false;
            $loguear = true;
        }


        //if (mysqli_num_rows($result)>0) {
        if ($result->rowCount()>0) {
            //$row = mysqli_fetch_array($result);
            $row = $result->fetch();
            
            if($row["Activo"] == 0) $ff = 7;

            if( $_POST['password'] == $row["pwd_usuario"] && $row["Activo"] == 1) 
            {

                $sql_cvecia = "SELECT cve_cia FROM c_almacenp WHERE clave = :clave_almacen";
                $pdo = \db();
                $result_cvecia = $pdo->prepare($sql_cvecia);
                $result_cvecia->execute(array('clave_almacen' => $clave_almacen));
                $pdo = null;
                $cve_almacen_cvecia = $result_cvecia->fetch()["cve_cia"];

                session_start();
                $_SESSION['id_user'] = $row['id_user'];
                $_SESSION['cve_usuario'] = $row['cve_usuario'];
                $_SESSION['identifier'] = $row['identifier'];
                $_SESSION['subdomain'] = $row['subdomain'];
                //$_SESSION['cve_cia'] = $row['cve_cia'];//AQUI SE CAMBIO A QUE LA COMPAÑIA A ESTAR EN LA SESIÓN SEA LA DEL ALMACÉN Y NO LA DEL USUARIO
                $_SESSION['cve_cia'] = $cve_almacen_cvecia;
                $_SESSION['name'] = $row['des_usuario'];
                $_SESSION['perfil_usuario'] = $row['perfil'];
                $_SESSION['image_url_usuario'] = $row['image_url'];
                $_SESSION['es_cliente'] = $row['es_cliente'];
                $_SESSION['cve_cliente'] = $row['cve_cliente'];
                $_SESSION['cve_proveedor'] = $row['cve_proveedor'];

                //$sql = "SELECT COUNT(`id_usuario`) AS cuenta FROM `users_online` WHERE `last_updated` > DATE_SUB(NOW(), INTERVAL 10 MINUTE) AND id_usuario = ".$row['id_user'];
                $sql = "SELECT COUNT(`id_usuario`) AS cuenta FROM `users_online` WHERE `last_updated` > DATE_SUB(NOW(), INTERVAL 10 MINUTE) AND id_usuario = :id_user";
                //$result = mysqli_query(\db2(), $sql);
                //$row_cuenta = mysqli_fetch_array($result);
                $pdo = \db();
                $result = $pdo->prepare($sql);
                $result->execute(array('id_user' => $row['id_user']));
                $pdo = null;
                $row_cuenta = $result->fetch();

                $cuenta = $row_cuenta['cuenta'];


                //**************************************************************************************************
                //****************************** LIMITAR POR LICENCIAS COMPRADAS ***********************************
                //**************************************************************************************************
                $licencia = true;
                $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
                $sql = "SELECT COUNT(DISTINCT uo.id_usuario) AS activos 
                        FROM users_bitacora ub
                        LEFT JOIN c_usuario u ON u.cve_usuario = ub.cve_usuario 
                        LEFT JOIN users_online uo ON uo.id_usuario = u.id_user
                        WHERE IFNULL(ub.fecha_cierre, '') > DATE_SUB(NOW(), INTERVAL 10 MINUTE) AND 
                        uo.id_usuario != (SELECT id_user FROM c_usuario WHERE cve_usuario = 'wmsmaster')";

                $result3 = mysqli_query($conn, $sql);
                $row_activos = mysqli_fetch_array($result3);
                mysqli_close($conn);
                $activos = $row_activos['activos'];

                $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
                $sql = "SELECT IF(IFNULL(FROM_BASE64(L_Web), 0) = '' OR IFNULL(FROM_BASE64(L_Web), 0) = 0, 0, FROM_BASE64(L_Web)) AS L_Web, IFNULL(FROM_BASE64(L_Mobile), 0) AS L_Mobile FROM t_license";
                $result3 = mysqli_query($conn, $sql);
                $row_licencias = mysqli_fetch_array($result3);
                mysqli_close($conn);
                $L_Web = $row_licencias['L_Web'];
                $L_Mobile = $row_licencias['L_Mobile'];

                if(($activos+0) >= ($L_Web+0))// +0 convierte a entero
                   $licencia = false;
                //**************************************************************************************************
                //*************************** FIN LIMITAR POR LICENCIAS COMPRADAS **********************************
                //**************************************************************************************************


            if($licencia == true || $row['cve_usuario'] == 'wmsmaster')
            {
                //echo "CUENTA = ".$cuenta." es_cliente = ".$row['es_cliente']." mostrar_select = ".$mostrar_select;

                if($row['cve_usuario'] == 'wmsmaster') $cuenta = 0;
                if($cuenta > 0) $mostrar_select = false;
                if($cuenta == 0 && $loguear == true) $mostrar_select = false;

                if($row['es_cliente'] > 0)
                {
                    $cve_almacen = $_POST['select_almacenes'];//$row['cve_almacen'];
                    //$sql = "SELECT id_user from t_usu_alm_pre where id_user = (SELECT id_user FROM c_usuario WHERE cve_usuario = '" . $_POST['email'] . "' LIMIT 1)";
                    $sql = "SELECT id_user from t_usu_alm_pre where id_user = (SELECT id_user FROM c_usuario WHERE cve_usuario = :usuario LIMIT 1)";
                    //$result2 = mysqli_query(\db2(), $sql);
                    $pdo = \db();
                    $result2 = $pdo->prepare($sql);
                    $result2->execute(array('usuario' => $_POST['email']));
                    $pdo = null;

                    //if(mysqli_num_rows($result2) == 0) 
                    if($result2->rowCount() == 0) 
                    {
                        //$sql = "INSERT INTO t_usu_alm_pre(id_user, cve_almac) VALUES((SELECT id_user FROM c_usuario WHERE cve_usuario = '" . $_POST['email'] . "' LIMIT 1), '{$cve_almacen}')";
                        $sql = "INSERT INTO t_usu_alm_pre(id_user, cve_almac) VALUES((SELECT id_user FROM c_usuario WHERE cve_usuario = :usuario LIMIT 1), :cve_almacen)";
                    }
                    else 
                    {
                        //$sql = "UPDATE t_usu_alm_pre SET cve_almac = '{$cve_almacen}' WHERE id_user = (SELECT id_user FROM c_usuario WHERE cve_usuario = '" . $_POST['email'] . "' LIMIT 1);";
                        $sql = "UPDATE t_usu_alm_pre SET cve_almac = :cve_almacen WHERE id_user = (SELECT id_user FROM c_usuario WHERE cve_usuario = :usuario LIMIT 1);";
                        //echo $sql;
                    }
                    //$result2 = mysqli_query(\db2(), $sql);
                    $pdo = \db();
                    $result2 = $pdo->prepare($sql);
                    $result2->execute(array('usuario' => $_POST['email'], 'cve_almacen' => $cve_almacen));
                    $pdo = null;
                    $almacen_bitacora = $cve_almacen;
                }

                if($row['cve_usuario'] == 'wmsmaster')// && $sin_almacen == true
                {

                    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
                    $sql_wmsmaster = "SELECT IF(COUNT(a.id) = (SELECT COUNT(t.cve_usuario) FROM trel_us_alm t INNER JOIN c_almacenp a ON a.clave = t.cve_almac AND a.Activo = 1 WHERE t.cve_usuario = 'wmsmaster'), 0, 1) AS agregar FROM c_almacenp a WHERE a.Activo = 1";
                    $result_wmsmaster = mysqli_query($conn, $sql_wmsmaster);
                    $row_wmsmaster = mysqli_fetch_array($result_wmsmaster);
                    mysqli_close($conn);
                    $agregar = $row_wmsmaster['agregar'];

                    if($agregar)
                    {
                        $sql_wmsmaster = "INSERT INTO trel_us_alm(cve_usuario, cve_almac, fecha_asignacion, Activo) (SELECT 'wmsmaster', clave, NOW(), 1 FROM c_almacenp WHERE clave NOT IN (SELECT cve_almac FROM trel_us_alm WHERE cve_usuario = 'wmsmaster') AND Activo = 1)";
                        $pdo = \db();
                        $result_wmsmaster = $pdo->prepare($sql_wmsmaster);
                        $result_wmsmaster->execute();
                        $pdo = null;
                        $ff = 5;
                    }
                    //echo "OK".$row['es_cliente']."select".$mostrar_select."cuenta".$cuenta."Sin almacen".$sin_almacen;
                    //header('Location: /login');
                    //$sin_almacen = false;
                    //$mostrar_select = false;
                    //$cuenta = 0;
                    //echo "OK".$row['es_cliente']."select".$mostrar_select."cuenta".$cuenta."Sin almacen".$sin_almacen;

                }

                if($row['es_cliente'] == 0 && $mostrar_select == false && $cuenta == 0 && $sin_almacen == false && ($row['web_apk'] == 'AW' || $row['web_apk'] == 'W'))
                {
                    $cve_usuario = $row['cve_usuario'];
                    $_SESSION['cve_almacen'] = $almacen_bitacora;

                     //$sql_id_alm = "SELECT id FROM c_almacenp Where clave='" . $almacen_bitacora . "'";
                    $sql_id_alm = "SELECT id FROM c_almacenp Where clave=:almacen_bitacora";
                     //$result_id_alm = mysqli_query(\db2(), $sql_id_alm);
                     //$row_id_alm = mysqli_fetch_array($result_id_alm);
                    $pdo = \db();
                    $result_id_alm = $pdo->prepare($sql_id_alm);
                    $result_id_alm->execute(array('almacen_bitacora' => $almacen_bitacora));
                    $pdo = null;
                    $row_id_alm = $result_id_alm->fetch();
                     $_SESSION['id_almacen'] = $row_id_alm["id"];


                    if($cve_usuario != 'wmsmaster')
                    {
                        //if($cuenta == 0) //significa que si la cuenta > 0, está activa, es decir que no va a ingresar un nuevo registro
                        {                //de esta manera, actualizará la fecha de la última sesión
                            $IP = getRealIP();
                            //$sql2 = 'INSERT INTO users_bitacora (cve_usuario, fecha_inicio, fecha_cierre, IP_Address, cve_almacen) VALUES("'.$cve_usuario.'", NOW(), NOW(), "'.$IP.'", "'.$almacen_bitacora.'");';
                            $sql2 = 'INSERT INTO users_bitacora (cve_usuario, fecha_inicio, fecha_cierre, IP_Address, cve_almacen) VALUES(:cve_usuario, NOW(), NOW(), :IP, :almacen_bitacora);';
                            //$data2 = mysqli_query(\db2(), $sql2);
                            $pdo = \db();
                            $data2 = $pdo->prepare($sql2);
                            $data2->execute(array('cve_usuario'=>$cve_usuario, 'IP' => $IP,'almacen_bitacora' => $almacen_bitacora));
                            $pdo = null;
                        }
                    }
                    header('Location: /dashboard/resumen?m=dh');
                }
                else if($row['es_cliente'] == 1)
                {
                    $cve_usuario = $row['cve_usuario'];
                    $_SESSION['cve_almacen'] = $almacen_bitacora;

                     //$sql_id_alm = "SELECT id FROM c_almacenp Where clave='" . $almacen_bitacora . "'";
                     $sql_id_alm = "SELECT id FROM c_almacenp Where clave= :almacen_bitacora";
                     //$result_id_alm = mysqli_query(\db2(), $sql_id_alm);
                     //$row_id_alm = mysqli_fetch_array($result_id_alm);
                     $pdo = \db();
                     $result_id_alm = $pdo->prepare($sql_id_alm);
                     $result_id_alm->execute(array('almacen_bitacora' => $almacen_bitacora));
                     $pdo = null;
                     $row_id_alm = $result_id_alm->fetch();
                     $_SESSION['id_almacen'] = $row_id_alm["id"];

                    if($cve_usuario != 'wmsmaster')
                    {
                        $IP = getRealIP();
                        //$sql2 = 'INSERT INTO users_bitacora (cve_usuario, fecha_inicio, fecha_cierre, IP_Address, cve_almacen) VALUES("'.$cve_usuario.'", NOW(), NOW(), "'.$IP.'", "'.$almacen_bitacora.'");';
                        $sql2 = 'INSERT INTO users_bitacora (cve_usuario, fecha_inicio, fecha_cierre, IP_Address, cve_almacen) VALUES(:cve_usuario, NOW(), NOW(), :IP, :almacen_bitacora);';
                        //$data2 = mysqli_query(\db2(), $sql2);
                        $pdo = \db();
                        $data2 = $pdo->prepare($sql2);
                        $data2->execute(array('cve_usuario'=>$cve_usuario, 'IP' => $IP,'almacen_bitacora' => $almacen_bitacora));
                        $pdo = null;
                    }

                    header('Location: /dashboard/monitoreo');
                }
                else if($row['es_cliente'] == 2)
                {
                    $cve_usuario = $row['cve_usuario'];
                    $_SESSION['cve_almacen'] = $almacen_bitacora;

                     //$sql_id_alm = "SELECT id FROM c_almacenp Where clave='" . $almacen_bitacora . "'";
                     $sql_id_alm = "SELECT id FROM c_almacenp Where clave=:almacen_bitacora";
                     //$result_id_alm = mysqli_query(\db2(), $sql_id_alm);
                     //$row_id_alm = mysqli_fetch_array($result_id_alm);
                     $pdo = \db();
                     $result_id_alm = $pdo->prepare($sql_id_alm);
                     $result_id_alm->execute(array('almacen_bitacora' => $almacen_bitacora));
                     $pdo = null;
                     $row_id_alm = $result_id_alm->fetch();
                     $_SESSION['id_almacen'] = $row_id_alm["id"];

                    if($cve_usuario != 'wmsmaster')
                    {
                        $IP = getRealIP();
                        //$sql2 = 'INSERT INTO users_bitacora (cve_usuario, fecha_inicio, fecha_cierre, IP_Address, cve_almacen) VALUES("'.$cve_usuario.'", NOW(), NOW(), "'.$IP.'", "'.$almacen_bitacora.'");';
                        $sql2 = 'INSERT INTO users_bitacora (cve_usuario, fecha_inicio, fecha_cierre, IP_Address, cve_almacen) VALUES(:cve_usuario, NOW(), NOW(), :IP, :almacen_bitacora);';
                        //$data2 = mysqli_query(\db2(), $sql2);
                        $pdo = \db();
                        $data2 = $pdo->prepare($sql2);
                        $data2->execute(array('cve_usuario'=>$cve_usuario, 'IP' => $IP,'almacen_bitacora' => $almacen_bitacora));
                        $pdo = null;
                    }

                    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
                    $sql_prov = "SELECT DISTINCT IFNULL(Valor, '') as mostrar_solo_existencias_en_proveedores FROM t_configuraciongeneral WHERE cve_conf = 'mostrar_solo_existencias_en_proveedores'";
                    if (!($res_prov = mysqli_query($conn, $sql_prov)))echo "Falló la preparación Prov: (" . mysqli_error($conn) . ") ";
                    $mostrar_solo_existencias_en_proveedores = mysqli_fetch_array($res_prov)['mostrar_solo_existencias_en_proveedores'];
                    mysqli_close($conn);

                    if($mostrar_solo_existencias_en_proveedores == 1)
                    {
                        header('Location: /reportes/existenciaubica');
                    }
                    else
                    {
                        header('Location: /dashboard/inventario?m=di');
                    }
                }
                else if($mostrar_select == false && $sin_almacen == false && $ff != 5)
                {
                    $ff = 2;
                    if($row['web_apk'] == 'A')
                        $ff = 8;
                    if($row['web_apk'] == 'WS' || $row['cve_usuario'] == 'WSUser')
                        $ff = 9;
                }
                else if($sin_almacen == true && $ff != 5)
                {
                    $ff = 3;
                    if($row['web_apk'] == 'A')
                       $ff = 6;
                    else if($row['web_apk'] == 'WS' || $row['cve_usuario'] == 'WSUser')
                        $ff = 9;
                }
            }
            else if($ff != 5)
            {
                $ff = 4;//licencias ocupadas
            }

            } else {

                throw new \ErrorException( 'Incorrect password provided' );

            }
        } 
        else if($ff != 5){
            $ff=1;
        }

        \db()->commit();

    } catch( \ErrorException $e ) {

        $error_login = $e->getMessage();
        //\db()->rollBack();

    }

}

if( $_POST['action'] == 'register' ) {

    try {

        \db()->beginTransaction();

        $_POST['emails_allowed'] = 100;
        $result = $user->save($_POST);

        \db()->commit();

        /*require("xmlapi-php/xmlapi.php");

        $subdominio=strtolower($_POST['subdomain']);
        $domain='presshunters.com';
        $cpanelusr = 'presshunters';//nombre del usuario cPanel
        $cpanelpass = 'test2';//Contrase�a del usuario cPanel
        $xmlapi = new xmlapi('presshunters.com');//Instanciamos la clase xmlapi pasando como parametro 127.0.0.1
        $xmlapi->set_port( 2083 );//Puerto cPanel puede ser 2082 � 2083
        $xmlapi->password_auth($cpanelusr,$cpanelpass);//Autenticacion en cPanel
        $xmlapi->set_debug(1); //Salida de errores 1= verdadero
        $json=$xmlapi->set_output('json');//Convierte mensajes de la api en formato json

        $_result = $xmlapi->api1_query($cpanelusr, 'SubDomain', 'addsubdomain', array($subdominio,$domain,0,0, '/public_html/'.$subdominio));//Creamos el subdominio

        $array = json_decode($_result);//Convierte en un array los datos json enviados por la API
        $errors_api= $array->{'error'}; //Extrae el mensaje de Mensaje de error
        if ($errors_api==null)
        {
            if ($_result){
                $messages="El dominio <strong>$subdominio.$domain</strong> ha sido creado con �xito.";

            } else {
                $errors="No se pudo crear el subdominio.";
            }
        }
        else {
            $errors=$errors_api;
        }

        session_start();
        $_SESSION['id_user'] = $result['id_user'];

        $identifier = $user->identifier;

        //echo  $identifier;
        $fichero = 'http://presshunters.com/tpl.txt';
        $actual = file_get_contents($fichero);
        $actual = str_replace("##########################IDENTIFIER############################", $identifier, $actual);

        file_put_contents($subdominio.'/index.php', $actual);

        $fichero = 'http://presshunters.com/tpl2.txt';
        $actual = file_get_contents($fichero);
        $actual = str_replace("##########################IDENTIFIER############################", $identifier, $actual);
        file_put_contents($subdominio.'/single.php', $actual);

        $fichero = 'http://presshunters.com/tpl3.txt';
        $actual = file_get_contents($fichero);
        file_put_contents($subdominio.'/load.php', $actual);*/

        header('Location: /settings/payments');

        echo 'User successfully created.';

    } catch( \ErrorException $e ) {

        \db()->rollBack();
        echo $e->getMessage();

    }

}

?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
    <head>

        <meta charset="utf-8" />

        <title><?php echo SITE_TITLE; ?> | Login</title>

        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta content="width=device-width, initial-scale=1" name="viewport" />
        <meta content="" name="description" />
        <meta content="" name="author" />

        <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=all" rel="stylesheet" type="text/css"/>
        <link href="<?php echo ST; ?>global/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo ST; ?>global/plugins/simple-line-icons/simple-line-icons.min.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo ST; ?>global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo ST; ?>global/plugins/uniform/css/uniform.default.css" rel="stylesheet" type="text/css"/>

        <link href="<?php echo ST; ?>admin/pages/css/login2.css" rel="stylesheet" type="text/css"/>

        <link href="<?php echo ST; ?>global/css/components-rounded.css" id="style_components" rel="stylesheet" type="text/css"/>
        <link href="<?php echo ST; ?>global/css/plugins.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo ST; ?>admin/layout4/css/layout.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo ST; ?>admin/layout4/css/themes/default.css" rel="stylesheet" type="text/css" id="style_color"/>
        <link href="<?php echo ST; ?>admin/layout4/css/custom.css" rel="stylesheet" type="text/css"/>

        <link rel="shortcut icon" href="favicon.ico"/>

    </head>
    <body class="login">

        <div class="plataform">
            <a class="logo-img" href="/login"></a>
            <div class="textform">WMS</div>

            <div class="content">

                <?php echo @$error; ?>

                <form class="login-form" action="/login" method="post">

                    <input type="hidden" name="action" value="login" />

                    <div class="alert alert-danger display-hide">
                        <button class="close" data-close="alert"></button>
                        <span> Ingrese nombre de usuario y contraseña. </span>

                    </div>

                    <?php if($ff==1){?>
                    <div class="alert alert-danger">
                        <button class="close" data-close="alert"></button>
                        <span> Nombre de usuario o contraseña incorrecta. </span>
                    </div>
                    <?php 
                    $mostrar_select = false;
                    }
                    ?>
                    <?php if($ff==2){?>
                    <div class="alert alert-danger">
                        <button class="close" data-close="alert"></button>
                        <span> Este usuario ya está firmado. </span>
                    </div>
                    <?php 
                    $mostrar_select = false;
                    }
                    ?>
                    <?php if($ff==3){?>
                    <div class="alert alert-danger">
                        <button class="close" data-close="alert"></button>
                        <span> Este usuario No está asignado a ningún almacén. </span>
                    </div>
                    <?php 
                    $mostrar_select = false;
                    }
                    ?>

                    <?php if($ff==4){?>
                    <div class="alert alert-danger">
                        <button class="close" data-close="alert"></button>
                        <span> Todas las licencias están ocupadas </span>
                    </div>
                    <?php 
                    $mostrar_select = false;
                    }
                    ?>
                    <?php if($ff==5){?>
                    <div class="alert alert-danger">
                        <button class="close" data-close="alert"></button>
                        <span> Intente de Nuevo por favor </span>
                    </div>
                    <?php 
                    $mostrar_select = false;
                    }
                    ?>
                    <?php if($ff==6){?>
                    <div class="alert alert-danger">
                        <button class="close" data-close="alert"></button>
                        <span> Usuario Tipo Operativo </span>
                    </div>
                    <?php 
                    $mostrar_select = false;
                    }
                    ?>
                    <?php if($ff==7){?>
                    <div class="alert alert-danger">
                        <button class="close" data-close="alert"></button>
                        <span> Usuario Inactivo </span>
                    </div>
                    <?php 
                    $mostrar_select = false;
                    }
                    ?>
                    <?php if($ff==8){?>
                    <div class="alert alert-danger">
                        <button class="close" data-close="alert"></button>
                        <span> Este Usuario es Operativo </span>
                    </div>
                    <?php 
                    $mostrar_select = false;
                    }
                    ?>
                    <?php if($ff==9){?>
                    <div class="alert alert-danger">
                        <button class="close" data-close="alert"></button>
                        <span> Usuario solo válido para WS </span>
                    </div>
                    <?php 
                    $mostrar_select = false;
                    }
                    ?>

                    <div class="form-group">
                        <label class="control-label visible-ie8 visible-ie9">Email</label>
                        <input class="form-control form-control-solid placeholder-no-fix" type="text" autocomplete="off" placeholder="Usuario" name="email" id="email" <?php if($mostrar_select == true){echo "readonly";} ?> value="<?php if(isset($_POST['email'])){if($_POST['email']) echo $_POST['email'];} ?>" 
                         />
                    </div>

                    <div class="form-group">
                        <label class="control-label visible-ie8 visible-ie9">Password</label>
                        <input class="form-control form-control-solid placeholder-no-fix" type="password" autocomplete="off" placeholder="Password" <?php if($mostrar_select == true){echo "readonly";} ?> name="password" value="<?php if(isset($_POST['password'])){if($_POST['password']) echo $_POST['password'];} ?>" />
                    </div>

                    <div class="form-actions">
<?php 
                if($mostrar_select == true)
                {
                    //mysqli_set_charset(\db2(), 'uft8mb4');
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);
    mysqli_close($conn);

                    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
                    $sql = "SELECT a.clave, a.nombre, CONCAT(RPAD(CONCAT('(', a.clave, ')'), 10, ' '), ' - ',a.nombre) as n_list FROM trel_us_alm t 
                            LEFT JOIN c_almacenp a ON a.clave = t.cve_almac
                            WHERE t.cve_usuario='" . $_POST['email'] . "' AND t.Activo = 1 AND a.Activo = 1 ORDER BY a.nombre";
                    $result = mysqli_query($conn, $sql);
                    mysqli_close($conn);
?>
                    <select name="select_almacenes" class="form-control" style="text-align: justify;text-justify: inter-word;">
                        <?php 
                        $num_res = mysqli_num_rows($result);
                        if($num_res > 1 || $num_res == 0)
                        {
                        ?>
                        <option value="">Seleccione Almacén</option>
                        <?php 
                        }
                        while($row = mysqli_fetch_array($result))
                        {
                            //$nombre_almacen = (utf8_encode($row["nombre"]))?utf8_encode($row["nombre"]):utf8_decode($row["nombre"]);
                            $nombre_almacen = (utf8_encode($row["n_list"]))?utf8_encode($row["n_list"]):utf8_decode($row["n_list"]);
                        ?>
                            <option value="<?php echo $row['clave']; ?>"><?php 
                            echo $nombre_almacen;
                            //echo str_pad("(".$row["clave"].")", 10)." - ".$nombre_almacen; 
                        ?></option>
                        <?php 
                        }
                        ?>
                        
                    </select>
<br>
                    <select name="select_proveedores" class="form-control">
                        <?php 
                        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
                        $sql = "SELECT ID_Proveedor, cve_proveedor, Nombre FROM c_proveedores WHERE Activo = 1 AND es_cliente = 1 ORDER BY Nombre ASC";
                        $result = mysqli_query($conn, $sql);
                        $num_res = mysqli_num_rows($result);
                        mysqli_close($conn);
                        //if($num_res > 1 || $num_res == 0)
                        {
                        ?>
                        <option value="">Todas las Empresas Asociadas</option>
                        <?php 
                        }
                        while($row = mysqli_fetch_array($result))
                        {
                            $nombre_proveedor = (utf8_encode($row["Nombre"]))?utf8_encode($row["Nombre"]):utf8_decode($row["Nombre"]);
                        ?>
                            <option value="<?php echo $row['ID_Proveedor']; ?>"><?php echo $nombre_proveedor; ?></option>
                        <?php 
                        }
                        ?>
                    </select>
<?php
                }
?>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn red btn-block uppercase">INGRESAR</button>
                    </div>

                    <?php 
                    if($mostrar_select == true)
                    {
                    ?>
                    <div class="form-actions">
                        <button type="button" id="cambiar_usuario" class="btn blue btn-block uppercase">CAMBIAR USUARIO</button>
                    </div>
                    <?php 
                    }
                    ?>

                    <div class="form-actions">

                        <div class="pull-left">
                            <a href="javascript:;" id="forget-password" class="forget-password">¿Se le olvidó su contraseña?</a>
                        </div>

                        <!--
<div class="pull-right">
<a href="javascript:;" id="register-btn" class="forget-password">Create an account</a>
</div>
--->

                        <div class="clearfix"></div>

                    </div>

                </form>

                    <div class="form-actions" style="display: none;">
                        <form action="" method="post">
                            <input type="hidden" name="ingreso_sap" value="">
                        <input type="submit" class="btn red btn-block uppercase" value="INGRESAR SAP">
                        </form>
                    </div>


                <form class="forget-form" action="/login" method="post">

                    <p class="hint">Ingrese el usuario que necesite recuperar la contraseña, El correo de recuperación llegará al correo asignado al usuario</p>
                    <br />

                    <div class="form-group">
                        <input class="form-control placeholder-no-fix" type="text" autocomplete="off" placeholder="Usuario" name="usuario_recuperacion" id="usuario_recuperacion" />
                    </div>

                    <div class="form-actions">
                        <button type="button" id="back-btn" class="btn btn-default">Regresar</button>
                        <button type="submit" class="btn btn-primary uppercase pull-right">Enviar</button>
                    </div>

                </form>

                <form class="register-form" action="/login" method="post">
                    <input type="hidden" name="action" value="register" />
                    <p class="hint">Fill out the form to create a new account with <?php echo SITE_TITLE; ?></p>
                    <br />

                    <div class="form-group">
                        <label class="control-label visible-ie8 visible-ie9">Full Name</label>
                        <input class="form-control placeholder-no-fix" type="text" placeholder="Full Name" name="name" />
                    </div>

                    <div class="form-group">
                        <label class="control-label visible-ie8 visible-ie9">Email</label>
                        <input class="form-control placeholder-no-fix" type="text" placeholder="Email" name="email" />
                    </div>

                    <div class="form-group">
                        <label class="control-label visible-ie8 visible-ie9">Country</label>
                        <select name="country" class="form-control">
                            <option value="">Country</option>
                            <option value="AF">Afghanistan</option>
                            <option value="AL">Albania</option>
                            <option value="DZ">Algeria</option>
                            <option value="AS">American Samoa</option>
                            <option value="AD">Andorra</option>
                            <option value="AO">Angola</option>
                            <option value="AI">Anguilla</option>
                            <option value="AR">Argentina</option>
                            <option value="AM">Armenia</option>
                            <option value="AW">Aruba</option>
                            <option value="AU">Australia</option>
                            <option value="AT">Austria</option>
                            <option value="AZ">Azerbaijan</option>
                            <option value="BS">Bahamas</option>
                            <option value="BH">Bahrain</option>
                            <option value="BD">Bangladesh</option>
                            <option value="BB">Barbados</option>
                            <option value="BY">Belarus</option>
                            <option value="BE">Belgium</option>
                            <option value="BZ">Belize</option>
                            <option value="BJ">Benin</option>
                            <option value="BM">Bermuda</option>
                            <option value="BT">Bhutan</option>
                            <option value="BO">Bolivia</option>
                            <option value="BA">Bosnia and Herzegowina</option>
                            <option value="BW">Botswana</option>
                            <option value="BV">Bouvet Island</option>
                            <option value="BR">Brazil</option>
                            <option value="IO">British Indian Ocean Territory</option>
                            <option value="BN">Brunei Darussalam</option>
                            <option value="BG">Bulgaria</option>
                            <option value="BF">Burkina Faso</option>
                            <option value="BI">Burundi</option>
                            <option value="KH">Cambodia</option>
                            <option value="CM">Cameroon</option>
                            <option value="CA">Canada</option>
                            <option value="CV">Cape Verde</option>
                            <option value="KY">Cayman Islands</option>
                            <option value="CF">Central African Republic</option>
                            <option value="TD">Chad</option>
                            <option value="CL">Chile</option>
                            <option value="CN">China</option>
                            <option value="CX">Christmas Island</option>
                            <option value="CC">Cocos (Keeling) Islands</option>
                            <option value="CO">Colombia</option>
                            <option value="KM">Comoros</option>
                            <option value="CG">Congo</option>
                            <option value="CD">Congo, the Democratic Republic of the</option>
                            <option value="CK">Cook Islands</option>
                            <option value="CR">Costa Rica</option>
                            <option value="CI">Cote d'Ivoire</option>
                            <option value="HR">Croatia (Hrvatska)</option>
                            <option value="CU">Cuba</option>
                            <option value="CY">Cyprus</option>
                            <option value="CZ">Czech Republic</option>
                            <option value="DK">Denmark</option>
                            <option value="DJ">Djibouti</option>
                            <option value="DM">Dominica</option>
                            <option value="DO">Dominican Republic</option>
                            <option value="EC">Ecuador</option>
                            <option value="EG">Egypt</option>
                            <option value="SV">El Salvador</option>
                            <option value="GQ">Equatorial Guinea</option>
                            <option value="ER">Eritrea</option>
                            <option value="EE">Estonia</option>
                            <option value="ET">Ethiopia</option>
                            <option value="FK">Falkland Islands (Malvinas)</option>
                            <option value="FO">Faroe Islands</option>
                            <option value="FJ">Fiji</option>
                            <option value="FI">Finland</option>
                            <option value="FR">France</option>
                            <option value="GF">French Guiana</option>
                            <option value="PF">French Polynesia</option>
                            <option value="TF">French Southern Territories</option>
                            <option value="GA">Gabon</option>
                            <option value="GM">Gambia</option>
                            <option value="GE">Georgia</option>
                            <option value="DE">Germany</option>
                            <option value="GH">Ghana</option>
                            <option value="GI">Gibraltar</option>
                            <option value="GR">Greece</option>
                            <option value="GL">Greenland</option>
                            <option value="GD">Grenada</option>
                            <option value="GP">Guadeloupe</option>
                            <option value="GU">Guam</option>
                            <option value="GT">Guatemala</option>
                            <option value="GN">Guinea</option>
                            <option value="GW">Guinea-Bissau</option>
                            <option value="GY">Guyana</option>
                            <option value="HT">Haiti</option>
                            <option value="HM">Heard and Mc Donald Islands</option>
                            <option value="VA">Holy See (Vatican City State)</option>
                            <option value="HN">Honduras</option>
                            <option value="HK">Hong Kong</option>
                            <option value="HU">Hungary</option>
                            <option value="IS">Iceland</option>
                            <option value="IN">India</option>
                            <option value="ID">Indonesia</option>
                            <option value="IR">Iran (Islamic Republic of)</option>
                            <option value="IQ">Iraq</option>
                            <option value="IE">Ireland</option>
                            <option value="IL">Israel</option>
                            <option value="IT">Italy</option>
                            <option value="JM">Jamaica</option>
                            <option value="JP">Japan</option>
                            <option value="JO">Jordan</option>
                            <option value="KZ">Kazakhstan</option>
                            <option value="KE">Kenya</option>
                            <option value="KI">Kiribati</option>
                            <option value="KP">Korea, Democratic People's Republic of</option>
                            <option value="KR">Korea, Republic of</option>
                            <option value="KW">Kuwait</option>
                            <option value="KG">Kyrgyzstan</option>
                            <option value="LA">Lao People's Democratic Republic</option>
                            <option value="LV">Latvia</option>
                            <option value="LB">Lebanon</option>
                            <option value="LS">Lesotho</option>
                            <option value="LR">Liberia</option>
                            <option value="LY">Libyan Arab Jamahiriya</option>
                            <option value="LI">Liechtenstein</option>
                            <option value="LT">Lithuania</option>
                            <option value="LU">Luxembourg</option>
                            <option value="MO">Macau</option>
                            <option value="MK">Macedonia, The Former Yugoslav Republic of</option>
                            <option value="MG">Madagascar</option>
                            <option value="MW">Malawi</option>
                            <option value="MY">Malaysia</option>
                            <option value="MV">Maldives</option>
                            <option value="ML">Mali</option>
                            <option value="MT">Malta</option>
                            <option value="MH">Marshall Islands</option>
                            <option value="MQ">Martinique</option>
                            <option value="MR">Mauritania</option>
                            <option value="MU">Mauritius</option>
                            <option value="YT">Mayotte</option>
                            <option value="MX">Mexico</option>
                            <option value="FM">Micronesia, Federated States of</option>
                            <option value="MD">Moldova, Republic of</option>
                            <option value="MC">Monaco</option>
                            <option value="MN">Mongolia</option>
                            <option value="MS">Montserrat</option>
                            <option value="MA">Morocco</option>
                            <option value="MZ">Mozambique</option>
                            <option value="MM">Myanmar</option>
                            <option value="NA">Namibia</option>
                            <option value="NR">Nauru</option>
                            <option value="NP">Nepal</option>
                            <option value="NL">Netherlands</option>
                            <option value="AN">Netherlands Antilles</option>
                            <option value="NC">New Caledonia</option>
                            <option value="NZ">New Zealand</option>
                            <option value="NI">Nicaragua</option>
                            <option value="NE">Niger</option>
                            <option value="NG">Nigeria</option>
                            <option value="NU">Niue</option>
                            <option value="NF">Norfolk Island</option>
                            <option value="MP">Northern Mariana Islands</option>
                            <option value="NO">Norway</option>
                            <option value="OM">Oman</option>
                            <option value="PK">Pakistan</option>
                            <option value="PW">Palau</option>
                            <option value="PA">Panama</option>
                            <option value="PG">Papua New Guinea</option>
                            <option value="PY">Paraguay</option>
                            <option value="PE">Peru</option>
                            <option value="PH">Philippines</option>
                            <option value="PN">Pitcairn</option>
                            <option value="PL">Poland</option>
                            <option value="PT">Portugal</option>
                            <option value="PR">Puerto Rico</option>
                            <option value="QA">Qatar</option>
                            <option value="RE">Reunion</option>
                            <option value="RO">Romania</option>
                            <option value="RU">Russian Federation</option>
                            <option value="RW">Rwanda</option>
                            <option value="KN">Saint Kitts and Nevis</option>
                            <option value="LC">Saint LUCIA</option>
                            <option value="VC">Saint Vincent and the Grenadines</option>
                            <option value="WS">Samoa</option>
                            <option value="SM">San Marino</option>
                            <option value="ST">Sao Tome and Principe</option>
                            <option value="SA">Saudi Arabia</option>
                            <option value="SN">Senegal</option>
                            <option value="SC">Seychelles</option>
                            <option value="SL">Sierra Leone</option>
                            <option value="SG">Singapore</option>
                            <option value="SK">Slovakia (Slovak Republic)</option>
                            <option value="SI">Slovenia</option>
                            <option value="SB">Solomon Islands</option>
                            <option value="SO">Somalia</option>
                            <option value="ZA">South Africa</option>
                            <option value="GS">South Georgia and the South Sandwich Islands</option>
                            <option value="ES">Spain</option>
                            <option value="LK">Sri Lanka</option>
                            <option value="SH">St. Helena</option>
                            <option value="PM">St. Pierre and Miquelon</option>
                            <option value="SD">Sudan</option>
                            <option value="SR">Suriname</option>
                            <option value="SJ">Svalbard and Jan Mayen Islands</option>
                            <option value="SZ">Swaziland</option>
                            <option value="SE">Sweden</option>
                            <option value="CH">Switzerland</option>
                            <option value="SY">Syrian Arab Republic</option>
                            <option value="TW">Taiwan, Province of China</option>
                            <option value="TJ">Tajikistan</option>
                            <option value="TZ">Tanzania, United Republic of</option>
                            <option value="TH">Thailand</option>
                            <option value="TG">Togo</option>
                            <option value="TK">Tokelau</option>
                            <option value="TO">Tonga</option>
                            <option value="TT">Trinidad and Tobago</option>
                            <option value="TN">Tunisia</option>
                            <option value="TR">Turkey</option>
                            <option value="TM">Turkmenistan</option>
                            <option value="TC">Turks and Caicos Islands</option>
                            <option value="TV">Tuvalu</option>
                            <option value="UG">Uganda</option>
                            <option value="UA">Ukraine</option>
                            <option value="AE">United Arab Emirates</option>
                            <option value="GB">United Kingdom</option>
                            <option value="US">United States</option>
                            <option value="UM">United States Minor Outlying Islands</option>
                            <option value="UY">Uruguay</option>
                            <option value="UZ">Uzbekistan</option>
                            <option value="VU">Vanuatu</option>
                            <option value="VE">Venezuela</option>
                            <option value="VN">Viet Nam</option>
                            <option value="VG">Virgin Islands (British)</option>
                            <option value="VI">Virgin Islands (U.S.)</option>
                            <option value="WF">Wallis and Futuna Islands</option>
                            <option value="EH">Western Sahara</option>
                            <option value="YE">Yemen</option>
                            <option value="ZM">Zambia</option>
                            <option value="ZW">Zimbabwe</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="control-label visible-ie8 visible-ie9">Subdomain</label>
                        <input class="form-control placeholder-no-fix" type="text" placeholder="Subdomain" name="subdomain" />
                    </div>

                    <br />
                    <p class="hint">Choose your desired password</p>
                    <br />

                    <div class="form-group">
                        <label class="control-label visible-ie8 visible-ie9">Password</label>
                        <input class="form-control placeholder-no-fix" type="password" autocomplete="off" id="register_password" placeholder="Password" name="password" />
                    </div>

                    <div class="form-group">
                        <label class="control-label visible-ie8 visible-ie9">Re-type Your Password</label>
                        <input class="form-control placeholder-no-fix" type="password" autocomplete="off" placeholder="Re-type Your Password" name="rpassword" />
                    </div>

                    <div class="form-group margin-top-20 margin-bottom-20">
                        <div id="register_tnc_error"> </div>
                    </div>

                    <div class="form-actions">
                        <button type="button" id="register-back-btn" class="btn btn-default">Back</button>
                        <button type="submit" id="register-submit-btn" class="btn red uppercase pull-right">Submit</button>
                    </div>
                </form>

            </div>

        </div>

        <div class="copyright hide"> 2014 © Metronic. Admin Dashboard Template. </div>

        <!--[if lt IE 9]>
<script src="<?php echo ST; ?>global/plugins/respond.min.js"></script>
<script src="<?php echo ST; ?>global/plugins/excanvas.min.js"></script>
<![endif]-->

        <script src="<?php echo ST; ?>global/plugins/jquery.min.js" type="text/javascript"></script>
        <script src="<?php echo ST; ?>global/plugins/jquery-migrate.min.js" type="text/javascript"></script>
        <script src="<?php echo ST; ?>global/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
        <script src="<?php echo ST; ?>global/plugins/jquery.blockui.min.js" type="text/javascript"></script>
        <script src="<?php echo ST; ?>global/plugins/uniform/jquery.uniform.min.js" type="text/javascript"></script>
        <script src="<?php echo ST; ?>global/plugins/jquery.cokie.min.js" type="text/javascript"></script>

        <script src="<?php echo ST; ?>global/plugins/jquery-validation/js/jquery.validate.min.js" type="text/javascript"></script>

        <script src="<?php echo ST; ?>global/scripts/metronic.js" type="text/javascript"></script>
        <script src="<?php echo ST; ?>admin/layout4/scripts/layout.js" type="text/javascript"></script>
        <script src="<?php echo ST; ?>admin/layout4/scripts/demo.js" type="text/javascript"></script>
        <script src="<?php echo ST; ?>admin/pages/scripts/login.js" type="text/javascript"></script>

        <script>
            jQuery(document).ready(function($) {
                Metronic.init(); // init metronic core components
                Layout.init(); // init current layout
                Login.init();
                Demo.init();
                $("#email").focus();

                $("#cambiar_usuario").click(function()
                {
                    window.location.href = '/login';
                });
            });
        </script>

    </body>
</html>
