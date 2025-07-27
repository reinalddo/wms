<?php
include '../../../app/load.php';

// Initalize Slim
$app = new \Slim\Slim();

if( !isset( $_SESSION['id_user'] ) AND !$_SESSION['id_user'] ) {
    exit();
}

$ga = new \Destinatarios\Destinatarios();

if( $_POST['action'] == 'add' ) 
{
  $ga->save($_POST);
  $arr = array(
    "success"=>true
  );
  echo json_encode($arr);
} 

if( $_POST['action'] == 'edit' ) 
{
  $ga->actualizarDestinatarios($_POST);
  $arr = array(
    "success"=>true
  );
  echo json_encode($arr);
}

if( $_POST['action'] == 'exists' ) {
	$Cve_Dest=$ga->exist($_POST["Cve_Dest"]);
	
    if($Cve_Dest==true)
        $success = true;
    else 
		$success= false;

    $arr = array(
		"success"=>$success
	);

    echo json_encode($arr);
}

if( $_POST['action'] == 'CambiarSecuenciaDestinatario' ) 
{
    $result = $ga->CambiarSecuenciaDestinatario($_POST);
    $arr = array(
        "success" => $result
    );
    echo json_encode($arr);
}

if( $_POST['action'] == 'delete' ) 
{
    $result = $ga->borrarDestinatario($_POST);
    $arr = array(
        "success" => $result
    );
    echo json_encode($arr);
}


if( $_POST['action'] == 'load' ) 
{
  $ga->id = $_POST["codigo"];
  $ga->__get("id");
  $arr = array(
    "success"       => true,
    "id"            => $ga->data->id_destinatario,
    "RazonSocial"   => $ga->data->razonsocial,
    "Direccion"     => $ga->data->direccion,
    "Colonia"       => $ga->data->colonia,
    "CodigoPostal"  => $ga->data->postal,	
    "Ciudad"        => $ga->data->ciudad,
    "Estado"        => $ga->data->estado,
    "Telefono"      => $ga->data->telefono,
    "Contacto"      => $ga->data->contacto,
    "Email"         => $ga->data->email_destinatario,
    "Latitud"       => $ga->data->latitud,
    "Longitud"      => $ga->data->longitud,
    "Cve_Clte"      => $ga->data->Cve_Clte,
    "ClaveDeDestinatario" => $ga->data->clave_destinatario, 
    "dir_principal" => $ga->data->dir_principal
  );

  echo json_encode($arr);
} 
if( $_POST['action'] == 'loadDestinatarios' ) {   
    $clientes = $ga->getAll();
    $arr = array(
        "success" => true
    );
    $associativeArray = array();
    foreach ($clientes as $Destinatario)
    {     
		$store_data[] = array(
			'id' => $Destinatario->Cve_Dest,
			'razon_social' => $Destinatario->RazonSocial,
      
		); 
    }   

    $arr = array_merge($arr,$store_data);

    echo json_encode($arr);

}
if($_POST['action'] == 'loadClientsRuta' ){
    /*$model_ruta = new \Ruta\Ruta();
    $model_ruta->ID_Ruta = $_POST["ID_Ruta"];
    $model_ruta->__get("ID_Ruta"); 


     $arr = array(
        "success" => true,
        "ID" => $model_ruta->data->cve_ruta  
    );

    echo json_encode($arr);*/

    $clientes = $ga->getAll();
    $arr = array(
        "success" => true
    );
    $associativeArray = array();
    foreach ($clientes as $Destinatario)
    {
            $store_data[] = array(
                'id' => $Destinatario->Cve_Dest,
                'razon_social' => $Destinatario->RazonSocial,
                'cve_ruta' => $Destinatario -> cve_ruta
            ); 
        
    }   

    $arr = array_merge($arr,$store_data);

    echo json_encode($arr); 

}

if($_POST['action'] == 'getDane'){


    $codDaneSql = \db()->prepare("SELECT departamento,des_municipio  FROM c_dane WHERE cod_municipio='".$_POST["codigo"]."'");
    $codDaneSql->execute();
    $codDane = $codDaneSql->fetchAll(PDO::FETCH_ASSOC);
    //$departamento = $codDaneSql->fetch()['departamento'];
    //$municipio = $codDaneSql->fetch()['des_municipio'];

    $arr = array(
        "success" => true,
        "departamento" =>$codDane[0]["departamento"],
        "municipio" =>$codDane[0]["des_municipio"]
    );

    echo json_encode($arr);

}

if($_POST['action'] == 'getDaneText')
{

    $codDaneSql = \db()->prepare("SELECT IFNULL(cod_municipio, 0) cod_municipio, IFNULL(des_municipio, 0) des_municipio, IFNULL(departamento, 0) departamento, COUNT(*) resultado  FROM c_dane WHERE cod_municipio='".$_POST["codigo"]."'");
    $codDaneSql->execute();
    $codDane = $codDaneSql->fetchAll(PDO::FETCH_ASSOC);
    //$departamento = $codDaneSql->fetch()['departamento'];
    //$municipio = $codDaneSql->fetch()['des_municipio'];

    $arr = array(
        "success" => true,
        "cod_municipio" =>$codDane[0]["cod_municipio"],
        "departamento" =>$codDane[0]["departamento"],
        "des_municipio" =>$codDane[0]["des_municipio"],
        "resultado" =>$codDane[0]["resultado"]
    );

    echo json_encode($arr);

}

if( $_POST['action'] == 'asignarRutaACliente' ) 
{
  $data = $ga->asignarRutaACliente($_POST);
	$arr = array(
    "success"=>true,
    "data"=>$data
	);
  echo json_encode($arr);
} 


if( $_POST['action'] == 'recovery' ) {
    $ga->recoveryDestinatario($_POST);
    $ga->id_cliente = $_POST["id_cliente"];
    $ga->__get("id_cliente");
    $arr = array(
        "success" => true,
    );

    echo json_encode($arr);

}

if( $_POST['action'] == 'traer_agentes_ruta' ) 
{
    $ruta = $_POST['ruta'];
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql = "SELECT DISTINCT tv.cve_usuario as cve_Vendedor, tv.nombre_completo as Nombre
            FROM c_usuario tv
            LEFT JOIN Rel_Ruta_Agentes ra ON ra.cve_vendedor = tv.id_user
            WHERE ra.cve_ruta = (SELECT ID_Ruta FROM t_ruta WHERE cve_ruta = '{$ruta}' LIMIT 1)";

    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    //$select = "<option value=''>Seleccione Agente | Operador</option>";
    $select = "";
    while ($row = mysqli_fetch_array($res)) {
        $row=array_map('utf8_encode',$row);
        $select .= "<option value='".$row['cve_Vendedor']."'>"."( ".$row['cve_Vendedor']." ) - ".$row['Nombre']."</option>";
    }

    echo json_encode($select);

}

if( $_POST['action'] == 'traer_rutas_agente' ) 
{
    $agente = $_POST['agente'];
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql = "SELECT DISTINCT r.cve_ruta, r.descripcion
            FROM c_usuario tv
            INNER JOIN Rel_Ruta_Agentes ra ON ra.cve_vendedor = tv.id_user
            INNER JOIN t_ruta r ON r.ID_Ruta = ra.cve_ruta 
            WHERE tv.cve_usuario = '$agente'";

    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    //$select = "<option value=''>Seleccione Agente | Operador</option>";
    $select = "";
    while ($row = mysqli_fetch_array($res)) {
        $row=array_map('utf8_encode',$row);
        $select .= "<option value='".$row['cve_ruta']."'>"."( ".$row['cve_ruta']." ) - ".$row['descripcion']."</option>";
    }

    echo json_encode($select);

}


if (isset($_POST) && !empty($_POST) && $_POST['action'] === 'get_gps') 
{
    $almacen      = $_POST['almacen'];
    $rutas        = $_POST['rutas'];
    $agentes      = $_POST['agentes'];
    $dias         = $_POST['dias'];
    $codigoPostal = $_POST['codigo'];
    $txtCriterio  = $_POST['txtCriterio'];

    if(!$sidx) $sidx =1;

    $and_agente = ""; 
    $and_vendedor = "";
    $and_vendedor_relday = "";

    $sqlBusqueda = "";
    if(!empty($txtCriterio))
        $sqlBusqueda = " AND (c.Ciudad LIKE '%$txtCriterio%' OR c.CalleNumero LIKE '%$txtCriterio%' OR 
        c.CodigoPostal LIKE '%$txtCriterio%' OR c.Colonia LIKE '%$txtCriterio%' OR c.Contacto LIKE '%$txtCriterio%' 
        OR c.Cve_Clte LIKE '%$txtCriterio%' OR t_ruta.cve_ruta LIKE '%$txtCriterio%' OR c.id_cliente LIKE '%$txtCriterio%' OR 
        d.ciudad LIKE '%$txtCriterio%' OR d.clave_destinatario LIKE '%$txtCriterio%' OR d.colonia LIKE '%$txtCriterio%' OR 
        d.contacto LIKE '%$txtCriterio%' OR d.id_destinatario LIKE '%$txtCriterio%' OR cp.cod_municipio LIKE '%$txtCriterio%' 
        OR cp.departamento LIKE '%$txtCriterio%' OR cp.des_municipio LIKE '%$txtCriterio%')";

    $sqlCP = "";
    if(!empty($codigoPostal))
        $sqlCP = " AND cp.cod_municipio = '$codigoPostal' ";

    if (!empty($agentes)) {
        $and_agente = " AND d.id_destinatario IN (SELECT Id_destinatario FROM RelDayCli WHERE Cve_Vendedor = '{$agentes}') ";
        $and_vendedor = "AND ra.cve_vendedor = (SELECT id_user FROM c_usuario WHERE cve_usuario = '{$agentes}')";
        $and_vendedor_relday = " AND RelDayCli.Cve_Vendedor = (SELECT id_user FROM c_usuario WHERE cve_usuario = '{$agentes}')";
    }

        $and_dias = "";
        $comparar_dias = ""; $contador_dias ="";
        if($dias != "''")
        {
            $and_dias = " AND RelDayCli.Cve_Cliente = d.Cve_Clte AND d.id_destinatario = RelDayCli.Id_Destinatario";
            $order_by = "CASE WHEN Secuencia = '' THEN 200000 END ASC, Secuencia*1 ASC"; //Secuencia*1 permite pasar a entero y que se organice por entero cómo números y no como varchar
              if($dias == "IFNULL(RelDayCli.Lu, 20000)") 
                {
                    $comparar_dias = " AND IFNULL(RelDayCli.Lu, 20000) = RelDayCli.Lu"; 
                    $contador_dias = "AND IFNULL(RelDayCli.Lu, 20000) != 20000";
                }
              if($dias == "IFNULL(RelDayCli.Ma, 20000)") 
                {
                    $comparar_dias = " AND IFNULL(RelDayCli.Ma, 20000) = RelDayCli.Ma"; 
                    $contador_dias = "AND IFNULL(RelDayCli.Ma, 20000) != 20000";
                }
              if($dias == "IFNULL(RelDayCli.Mi, 20000)") 
                {
                    $comparar_dias = " AND IFNULL(RelDayCli.Mi, 20000) = RelDayCli.Mi"; 
                    $contador_dias = "AND IFNULL(RelDayCli.Mi, 20000) != 20000";
                }
              if($dias == "IFNULL(RelDayCli.Ju, 20000)") 
                {
                    $comparar_dias = " AND IFNULL(RelDayCli.Ju, 20000) = RelDayCli.Ju"; 
                    $contador_dias = "AND IFNULL(RelDayCli.Ju, 20000) != 20000";
                }
              if($dias == "IFNULL(RelDayCli.Vi, 20000)") 
                {
                    $comparar_dias = " AND IFNULL(RelDayCli.Vi, 20000) = RelDayCli.Vi"; 
                    $contador_dias = "AND IFNULL(RelDayCli.Vi, 20000) != 20000";
                }
              if($dias == "IFNULL(RelDayCli.Sa, 20000)") 
                {
                    $comparar_dias = " AND IFNULL(RelDayCli.Sa, 20000) = RelDayCli.Sa"; 
                    $contador_dias = "AND IFNULL(RelDayCli.Sa, 20000) != 20000";
                }
              if($dias == "IFNULL(RelDayCli.Do, 20000)") 
                {
                    $comparar_dias = " AND IFNULL(RelDayCli.Do, 20000) = RelDayCli.Do"; 
                    $contador_dias = "AND IFNULL(RelDayCli.Do, 20000) != 20000";
                }

        }

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    mysqli_set_charset($conn, 'utf8');

    $sql = "
            SELECT * FROM (
            SELECT DISTINCT
                    IFNULL(d.id_destinatario, '--') AS id_destinatario,
                    $dias AS Secuencia,
                    IFNULL(c.RazonSocial, '--') AS razonsocial, 
                    IF(d.dir_principal = 1, IFNULL(c.latitud, '--'), IFNULL(d.latitud, '--')) AS latitud,
                    IF(d.dir_principal = 1, IFNULL(c.longitud, '--'), IFNULL(d.longitud, '--')) AS longitud,
                    IFNULL(c_alm.latitud, '--') AS lat_alm,
                    IFNULL(c_alm.longitud, '--') AS lon_alm,
                    IFNULL(c.credito, 0) AS credito,
                    IFNULL(c.saldo_actual, 0.00) AS saldo_deudor,
                    IFNULL(tc.Des_TipoCte, '') AS clasificacion,
                    IFNULL(c_alm.nombre, '--') AS sucursal
            FROM c_destinatarios d
                LEFT JOIN c_cliente c ON d.Cve_Clte = c.Cve_Clte
                LEFT JOIN c_tipocliente tc on tc.id = c.ClienteTipo
                LEFT JOIN t_clientexruta ON t_clientexruta.clave_cliente = d.id_destinatario
                LEFT JOIN t_ruta ON t_ruta.ID_Ruta = t_clientexruta.clave_ruta
                LEFT JOIN RelDayCli ON t_ruta.ID_Ruta = RelDayCli.Cve_Ruta 
                LEFT JOIN Rel_Ruta_Agentes ra ON ra.cve_ruta = t_ruta.ID_Ruta {$and_vendedor} 
                LEFT JOIN c_usuario u ON u.id_user = ra.cve_vendedor
                LEFT JOIN c_dane cp ON cp.cod_municipio = d.postal
                LEFT JOIN c_almacenp c_alm ON c_alm.id = c.Cve_Almacenp
            WHERE d.Activo = '1' AND c.Cve_Almacenp = '$almacen' AND c.Cve_Clte = d.Cve_Clte AND t_ruta.cve_ruta = '$rutas' 
            {$sqlBusqueda} {$sqlCP} {$and_dias} {$and_vendedor_relday} {$comparar_dias}
            GROUP BY clave_cliente
            ) AS points WHERE points.latitud != '--' AND points.longitud != '--' AND points.latitud != '' AND points.longitud != '' AND points.latitud != '0' AND points.longitud != '0'";

    if (!($res = mysqli_query($conn, $sql))) 
    {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }
    mysqli_close($conn);

    $i = 0;

    while ($row = mysqli_fetch_array($res)) 
    {
        extract($row);

        $responce->rows[$i]=array($id_destinatario, $razonsocial, $latitud, $longitud, $lat_alm, $lon_alm, $sucursal, $Secuencia, $credito, $saldo_deudor, $clasificacion);
        $i++;
    }

    echo json_encode($responce);
}

if( $_POST['action'] == 'filtrar_rutas' ) 
{
    $almacen = $_POST['almacen'];
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql = "SELECT DISTINCT cve_ruta, descripcion FROM t_ruta WHERE cve_almacenp = '{$almacen}' AND Activo = 1";

    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $select = "<option value=''>Seleccione</option>";
    while ($row = mysqli_fetch_array($res)) {
        $row=array_map('utf8_encode',$row);
        $select .= "<option value='".$row['cve_ruta']."'>"."( ".$row['cve_ruta']." ) - ".$row['descripcion']."</option>";
    }

    echo json_encode($select);

}

if( $_POST['action'] == 'ReiniciarSecuencia' ) 
{
    $almacen = $_POST['almacen'];
    $rutas   = $_POST['rutas'];
    $agentes = $_POST['agentes'];
    $dias    = $_POST['dias'];

        $comparar_dias = ""; $contador_dias ="";
        if($dias != "''")
        {
            $and_dias = " AND RelDayCli.Cve_Cliente = d.Cve_Clte AND d.id_destinatario = RelDayCli.Id_Destinatario";
            $order_by = "CASE WHEN Secuencia = '' THEN 200000 END ASC, Secuencia*1 ASC"; //Secuencia*1 permite pasar a entero y que se organice por entero cómo números y no como varchar
              if($dias == "IFNULL(RelDayCli.Lu, 20000)") 
                {
                    $dias = "Lu";
                }
              if($dias == "IFNULL(RelDayCli.Ma, 20000)") 
                {
                    $dias = "Ma";
                }
              if($dias == "IFNULL(RelDayCli.Mi, 20000)") 
                {
                    $dias = "Mi";
                }
              if($dias == "IFNULL(RelDayCli.Ju, 20000)") 
                {
                    $dias = "Ju";
                }
              if($dias == "IFNULL(RelDayCli.Vi, 20000)") 
                {
                    $dias = "Vi";
                }
              if($dias == "IFNULL(RelDayCli.Sa, 20000)") 
                {
                    $dias = "Sa";
                }
              if($dias == "IFNULL(RelDayCli.Do, 20000)") 
                {
                    $dias = "Do";
                }
        }

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql = "UPDATE RelDayCli SET $dias = NULL WHERE Cve_Ruta = (SELECT ID_Ruta FROM t_ruta WHERE cve_ruta = '{$rutas}') AND Cve_Vendedor = (SELECT id_user FROM c_usuario WHERE cve_usuario = '{$agentes}')";

    $res = mysqli_query($conn, $sql);

    echo json_encode(true);

}

if (isset($_POST) && !empty($_POST) && $_POST['action'] === 'get_gps_bitacora') 
{
    $almacen      = $_POST['almacen'];
    $rutas        = $_POST['rutas'];

    if(!$sidx) $sidx =1;

    $and_agente = ""; 
    $and_vendedor = "";
    $and_vendedor_relday = "";

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    mysqli_set_charset($conn, 'utf8');

    $sql = "
            SELECT * FROM (
            SELECT DISTINCT
                    IFNULL(bt.Codigo, '--') AS id_destinatario,
                    IFNULL(d.razonsocial, '--') AS razonsocial, 
                    bt.latitude AS latitud,
                    bt.longitude AS longitud,
                    IFNULL(c_alm.latitud, '--') AS lat_alm,
                    IFNULL(c_alm.longitud, '--') AS lon_alm,
                    IFNULL(c_alm.nombre, '--') AS sucursal,
                    bt.DiaO,
                    bt.RutaId
            FROM BitacoraTiempos bt
                INNER JOIN c_destinatarios d ON d.id_destinatario = bt.Codigo
                INNER JOIN t_ruta ON t_ruta.ID_Ruta = bt.RutaId
                INNER JOIN c_almacenp c_alm ON c_alm.clave = bt.IdEmpresa
            WHERE c_alm.id = '$almacen' AND t_ruta.cve_ruta = '$rutas' AND bt.Visita = 1
            ORDER BY DiaO DESC
            LIMIT 1
            ) AS points WHERE points.latitud != '--' AND points.longitud != '--' AND points.latitud != '' AND points.longitud != '' AND points.latitud != '0' AND points.longitud != '0'";

    if (!($res = mysqli_query($conn, $sql))) 
    {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }
    mysqli_close($conn);

    $i = 0;

    while ($row = mysqli_fetch_array($res)) 
    {
        extract($row);

        $responce->rows[$i]=array($id_destinatario, $razonsocial, $latitud, $longitud, $lat_alm, $lon_alm, $sucursal);
        $i++;
    }

    echo json_encode($responce);
}

if (isset($_POST) && !empty($_POST) && $_POST['action'] === 'get_visitas_gps_bitacora') 
{
    $almacen      = $_POST['almacen'];
    $rutas        = $_POST['rutas'];
    $diao         = $_POST['diao'];

    if(!$sidx) $sidx =1;

    $and_agente = ""; 
    $and_vendedor = "";
    $and_vendedor_relday = "";

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    mysqli_set_charset($conn, 'utf8');

    $sql = "
            SELECT * FROM (
            SELECT DISTINCT
                    bt.Id,
                    IFNULL(bt.Codigo, '--') AS id_destinatario,
                    IFNULL(d.razonsocial, '--') AS razonsocial, 
                    bt.Descripcion,
                    bt.latitude AS latitud,
                    bt.longitude AS longitud,
                    IFNULL(c_alm.latitud, '--') AS lat_alm,
                    IFNULL(c_alm.longitud, '--') AS lon_alm,
                    IFNULL(c_alm.nombre, '--') AS sucursal,
                    bt.DiaO,
                    bt.RutaId
            FROM BitacoraTiempos bt
                LEFT JOIN c_destinatarios d ON d.id_destinatario = bt.Codigo
                INNER JOIN t_ruta ON t_ruta.ID_Ruta = bt.RutaId
                INNER JOIN c_almacenp c_alm ON c_alm.clave = bt.IdEmpresa
            WHERE c_alm.id = '$almacen' AND t_ruta.cve_ruta = '$rutas' AND bt.DiaO = '$diao'
            ORDER BY Id
            ) AS points WHERE points.latitud != '--' AND points.longitud != '--' AND points.latitud != '' AND points.longitud != '' AND points.latitud != '0' AND points.longitud != '0'";

    if (!($res = mysqli_query($conn, $sql))) 
    {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }
    mysqli_close($conn);

    $i = 0;

    while ($row = mysqli_fetch_array($res)) 
    {
        extract($row);

        $responce->rows[$i]=array($id_destinatario, $razonsocial, $latitud, $longitud, $lat_alm, $lon_alm, $sucursal, $Descripcion);
        $i++;
    }

    echo json_encode($responce);
}
