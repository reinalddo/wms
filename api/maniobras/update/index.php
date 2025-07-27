<?php
include '../../../app/load.php';

// Initalize Slim
$app = new \Slim\Slim();

if( !isset( $_SESSION['id_user'] ) AND !$_SESSION['id_user'] ) {
    exit();
}

$ga = new \Maniobras\Maniobras();

if( $_POST['action'] == 'exists' ) {
    $folio = $_POST['Fol_folio'];
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $query = mysqli_query($conn, "SELECT Fol_folio FROM th_pedido WHERE  Fol_folio = '$folio'");
    
    $success = $query->num_rows > 0 ? true : false;

    $arr = array(
        "success" => $success
    );
    mysqli_close($conn);
    echo json_encode($arr);
} 

if( $_POST['action'] == 'add' ) {
    //echo var_dump($_POST);
    //die();
    $res = $ga->save($_POST);
    $success = true;
    $arr = array(
        "success" => $success,
        "folio" => $res
    );

    echo json_encode($arr);
    exit();
} 

if( $_POST['action'] == 'validarCredenciales' ) {
    $password = $_POST["password"];
    $id_user = $_SESSION['id_user'];

    $sql = "SELECT 
                * 
            FROM c_usuario 
            WHERE id_user = '{$id_user}' AND pwd_usuario = '{$password}' AND perfil = 1;";
        
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) > 0){
        $response = ['status' => 200];
    } else $response = ['status' => 404];

    echo json_encode($response);
    exit();
}

if($_POST['action'] === 'agregarDestinatario'){
    extract($_POST);
    $query2 = true;
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $sql = "INSERT INTO c_destinatarios (Cve_Clte, razonsocial, direccion, colonia, postal, ciudad, estado, contacto, telefono, email_destinatario, latitud, longitud) VALUES ('{$cliente}', '{$razon}', '{$direccion}', '{$colonia}', '{$postal}', '{$ciudad}', '{$estado}', '{$contacto}', '{$telefono}', '{$email_destinatario}', '{$txtLatitudDest}', '{$txtLongitudDest}');";
    $query = mysqli_query($conn, $sql);
    mysqli_close($conn);

    echo json_encode(array(
        "success"   => $query && $query2
    ));
}

if( $_POST['action'] == 'VerificarFolio' )
{
    extract($_POST);
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $sql = "SELECT * FROM th_pedservicios where Fol_folio = '{$Fol_folio}'";
    $query = mysqli_query($conn, $sql);
    $existe = mysqli_num_rows($query);
    mysqli_close($conn);

  $arr = array(
    "success" => true,
    "existe" => $existe
    //"Consecutivo" => $data["Consecutivo"]
  );
  echo json_encode($arr);
}

if( $_POST['action'] == 'getAgentesRutas' )
{
    extract($_POST);
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

      $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))
        echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

    $sql = "SELECT u.cve_usuario, u.nombre_completo 
            from c_usuario u
            inner join t_ruta r on r.cve_ruta = '{$clave_ruta}'
            inner join Rel_Ruta_Agentes a on u.id_user = a.cve_vendedor and u.es_cliente = 3 and r.ID_Ruta = a.cve_ruta";
    $query = mysqli_query($conn, $sql);

    $options = "<option value=''>Seleccione</option>";

    while($row = mysqli_fetch_assoc($query))
    {
        extract($row);
        $options .= "<option value='".$cve_usuario."'>(".$cve_usuario.") - ".utf8_encode($nombre_completo)."</option>";
    }

      $sql = "SELECT IFNULL(IFNULL(DiaO, 1), '') AS DiaO, Fecha, CURDATE() AS F_Actual FROM DiasO WHERE RutaId = (SELECT ID_Ruta FROM t_ruta WHERE cve_ruta = '{$clave_ruta}') AND Id = (SELECT MAX(Id) FROM DiasO WHERE RutaId = (SELECT ID_Ruta FROM t_ruta WHERE cve_ruta = '{$clave_ruta}'))";
      $sth = \db()->prepare( $sql );$sth->execute();$res = $sth->fetch();
      $DiaO = $res['DiaO'];
      $Fecha = $res['Fecha'];
      $F_Actual = $res['F_Actual'];


      if($Fecha."" != $F_Actual."" && $DiaO != '')
        $DiaO++;

      if($DiaO == '') $DiaO = 1;

    //echo $options;
    mysqli_close($conn);

  $arr = array(
    "success" => true,
    "udiao"   => $DiaO,
    "options" => $options
    //"Consecutivo" => $data["Consecutivo"]
  );
  echo json_encode($arr);
}

if( $_POST['action'] == 'consecutivo_folio' )
{
  $data = $ga->consecutivo_folio($_POST);
  $arr = array(
    "success" => true,
    "Consecutivo" => $data
    //"Consecutivo" => $data["Consecutivo"]
  );
  echo json_encode($arr);
}

if( $_POST['action'] == 'consecutivo_folio_traslado' )
{
  $data = $ga->consecutivo_folio_traslado($_POST);
  $arr = array(
    "success" => true,
    "Consecutivo" => $data
    //"Consecutivo" => $data["Consecutivo"]
  );
  echo json_encode($arr);
}