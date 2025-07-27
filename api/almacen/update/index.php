<?php
include '../../../app/load.php';

// Initalize Slim
$app = new \Slim\Slim();

if( !isset( $_SESSION['id_user'] ) AND !$_SESSION['id_user'] ) {
    exit();
}

$ga = new \Almacen\Almacen();

if( $_POST['action'] == 'add' ) {
    $ga->save($_POST);
    /*$idAlmacen  = $ga->getLastInsertId()->ID_Almacen;

    if(!empty($_POST['usuarios']))
    {
        for($i=0;$i<count($_POST['usuarios'][0]);$i++)
        {                       
            $ga->saveUserAl($_POST['usuarios'][0][$i],$idAlmacen);
        }
    }*/

}

if( $_POST['action'] == 'edit' ) {
    $ga->actualizarAlmacen($_POST);
    /*$userAlmacen = $ga->loadUserAlmacen($_POST["cve_almac"]);

    // Arreglo de usuarios actuales en el almacen
    $usuario_data = array();
    foreach($userAlmacen as $user)
    {
        $usuario_data[]  = $user->cve_usuario;
    }

    $success = true;
    // Si el arreglo usuarios a agregar está vacío inactiva todos los usuarios
    if(!empty($_POST["usuarios"]))
    {
        $diferencia = array_diff($_POST["usuarios"][0],$usuario_data);

        $array_dif = array();
        foreach($diferencia as $diff)
        {
            $array_dif[] = $diff;
        }

        foreach($array_dif as $usuarioAlmacen)
        {
            $ga->saveUserAl($usuarioAlmacen,$_POST["cve_almac"]);
        }

        $diferencia_inv = array_diff($usuario_data,$_POST["usuarios"][0]);

        $array_difinv = array();
        foreach($diferencia_inv as $diffinv)
        {
            $array_difinv[] = $diffinv;
        }

        foreach($array_difinv as $arraydiffinv)
        {
            $ga->borrarUsuarioAlmacen($_POST["cve_almac"],$arraydiffinv);
        }

    }
    else
    {
        foreach($usuario_data as $user)
        {
            $ga->borrarUsuarioAlmacen($_POST["cve_almac"],$user);
        }
    }*/

    $arr = array(
        "success" => $success,
        "err" => $resp
    );

    echo json_encode($arr);
}

if( $_POST['action'] == 'exists' ) {

    $clave=$ga->exist($_POST["clave_almacen"]);



    if($clave==true)
        $success = true;
    else 
        $success= false;

    $arr = array(
        "success"=>$success
    );

    echo json_encode($arr);

}

if( $_POST['action'] == 'delete' ) {
    $ga->borrarAlmacen($_POST);
    $ga->cve_almac = $_POST["clave_almacen"];
    $ga->__get("clave_almacen");
    $arr = array(
        "success" => true,
        //"nombre_proveedor" => $ga->data->Empresa,
        //"contacto" => $ga->data->VendId
    );

    echo json_encode($arr);

}

if( $_POST['action'] == 'recovery' ) {
    $ga->recovery($_POST);
    $arr = array(
        "success" => true,
        //"nombre_proveedor" => $ga->data->Empresa,
        //"contacto" => $ga->data->VendId
    );

    echo json_encode($arr);

}

if( $_POST['action'] == 'load' ) {
    $ga->traerAlmacen($_POST["cve_almac"]);



    $arr = array(
        "success" => true,
        "Cve_TipoZona" =>$ga->data->Cve_TipoZona,
        "NAlmacen" => $ga->data->des_almac,
        "CAlmacen" => $ga->data->clave_almacen,
        "clasif_abc" => $ga->data->clasif_abc,
        "ID_Proveedor" => $ga->data->ID_Proveedor,
        "almacenP" => $ga->data->cve_almacenp
    );



    echo json_encode($arr);

}

if( $_POST['action'] == 'guardarUsuario' ) 
{	
  $ga->borrarUsuarioAlmacen($_POST["cve_almac"]);
  $usuarios = $_POST["usuarios"][0];
  if(!empty($_POST["usuarios"]))
  {
    foreach($usuarios as $usuarioAlmacen)
    {
      $ga->saveUserAl($usuarioAlmacen,$_POST["cve_almac"]);
    }
  }
  $arr = array(
    "success" => $success,
    "err" => $resp
  );
  echo json_encode($arr);
}

if( $_POST['action'] == 'loadAlmacenes' ) {
    $almacenUser = $ga->loadAlmacenUser($_POST["cve_usuario"]);


    $current_almacen = array(
        "Current" =>array()
    );

    foreach ($almacenUser as $currentAlmacen)
    {
        $current_almacen['Current'][] = array (
            'id' => $currentAlmacen->cve_almac,
            'desc' =>$currentAlmacen->des_almac,
        );
    }

    $almacen_data = array();
    foreach($almacenUser as $almacen)
    {
        $almacen_data[]  = $almacen->cve_almac;
    }


    $model_almacen = new \Almacen\Almacen();
    $almacenes = $model_almacen->getAll();

    $store_data = array(
        "Almacenes" =>array()
    );

    foreach ($almacenes as $almacen)
    {
        if(!in_array($almacen->cve_almac,$almacen_data))
        {
            $store_data['Almacenes'][] = array (
                'id' => $almacen->cve_almac,
                'desc' =>$almacen->des_almac
            );
        }

    }

    $finalArray = array_merge($store_data,$current_almacen);

    echo json_encode($finalArray);

}

if( $_POST['action'] == 'guardarAlmacen' ) {

    $ga->borrarAlmacenUsuario($_POST["cve_usuario"]);

    $almacenes = $_POST["almacenes"][0];

    if(!empty($_POST["almacenes"]))
    {
        foreach($almacenes as $almacenUsuario)
        {
            $ga->saveUserAl($_POST["cve_usuario"],$almacenUsuario);
        }
    }

    $arr = array(
        "success" => true,
        "err" => $resp
    );

    echo json_encode($arr);
}

if( $_POST['action'] == 'traerUsuariosDeAlmacen' ) {	
    $userAlmacen = $ga->loadUserAlmacen($_POST["cve_almac"]);
    $users = $ga->loadUsers($_POST["cve_almac"]);

    $arr = array(
        "success" => true,        
        "usuariosAlmacen" => $userAlmacen,
        "todosUsuarios" => $users		
    );


    $arr = array_merge($arr);

    echo json_encode($arr);
}

if( $_POST['action'] == 'traerAlmacenesDeUsuario' ) {	
    $almacenUsuario = $ga->loadAlmacenUser($_POST["cve_usuario"]);
    $almacenes = $ga->loadAlmacenes($_POST["cve_usuario"]);

    $arr = array(
        "success" => true,        
        "almacenesUsuario" => $almacenUsuario,
        "todosAlmacenes" => $almacenes		
    );


    //$arr = array_merge($arr);

    echo json_encode($arr);
}

if( $_POST['action'] == 'traerUbicacionesDeZonas' ) 
{	
  //echo var_dump($_POST["rack"]);
  //die();
  $vacias = isset($_POST['conProducto']) ? false : true;
  $ubicaciones = $ga->loadUbicacionesDeZonas($_POST["zona"],$_POST["rack"],$_POST["area"], $_POST['almacen'], $vacias, $_POST['producto'], $_POST["pasillo"], $_POST["rackk"], $_POST["nivel"], $_POST["seccion"], $_POST["ubicacion"]);
  $arr = array(
    "success" => true,        
    "ubicaciones" => $ubicaciones,
  );
  $arr = array_merge($arr);
  echo json_encode($arr);
}

if( $_POST['action'] == 'traerUbicacionesDeZonas2' ) {	
    $ubicaciones = $ga->loadUbicacionesDeZonas($_POST["zona"],$_POST["rack"]);

    $arr = array(         
        "data" => $ubicaciones
    );


    $arr = array_merge($arr);

    echo json_encode($arr);
}

if( $_POST['action'] == 'traerRackDeZonas' ) {	
    $vacias = isset($_POST['conProducto']) ? false : true;
    
    $rack = $ga->loadRackDeZonas($_POST["zona"], $vacias, $_POST["pasillo"], $_POST["rackk"], $_POST["nivel"], $_POST["seccion"], $_POST["ubicacion"], $_POST["id_almacen"]);

    $arr = array(
        "success" => true,        
        "racks" => $rack[0],  
        "racks1" => $rack[1],
    );


    $arr = array_merge($arr);

    echo json_encode($arr);
}

if( $_POST['action'] == 'traerZonaporAlmacen' ) 
{	
  $ubicaciones = $ga->traerZonaporAlmacen($_POST["cve_almacenp"]);
  $arr = array(
    "success" => true,        
    "zona_almacen" => $ubicaciones
  );
  $arr = array_merge($arr);
  echo json_encode($arr);
}

if( $_POST['action'] == 'traerArticulosDeAlmacen' ) {	
    $articulos = $ga->traerArticulosDeAlmacen($_POST["almacen"]);

    $arr = array(
        "success" => true,        
        "articulos" => $articulos
    );


    $arr = array_merge($arr);

    echo json_encode($arr);
}
if( $_POST['action'] == 'traerArticulosDeAlmacenExist' ) {   
    $articulos = $ga->traerArticulosDeAlmacenExist($_POST["almacen"]);

    $arr = array(
        "success" => true,        
        "articulos" => $articulos
    );


    $arr = array_merge($arr);

    echo json_encode($arr);
}


if( $_POST['action'] == 'traerArticulosDeAlmacenExist2' ){

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $_page = 0;

    $ff=explode('|',$_POST["almacen"]);

   
    if (intval($page)>0) $_page = ($page-1)*$limit;

    $almacen = $ff[0];
    $where = "a.clave LIKE '%{$almacen}%' OR a.nombre LIKE '%{$almacen}%'";

    $sql = "SELECT 
                e.id
                , e.NConteo conteo
                , e.idy_ubica ubicacion_id
                , e.cve_articulo AS clave_producto
                , e.Cantidad AS existencia
                , e.cve_lote AS lote
                , p.des_articulo AS nombre_producto
                , a.id almacen_id  
                , a.clave AS clave_almacen
                , a.nombre AS nombre_almacen	
                , z.cve_almac AS zona_id
                , z.des_almac AS nombre_zona
                , 1 as tipo
            FROM t_invpiezas e
                JOIN c_articulo p ON e.cve_articulo = p.cve_articulo
                JOIN c_almacen z ON z.cve_almac = (SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = e.idy_ubica LIMIT 1)
            JOIN c_almacenp a ON z.cve_almacenp = a.id   
            WHERE ({$where}) AND e.cuarentena = 0
                AND e.Cantidad > 0 AND e.NConteo = (
                    SELECT 
                        MAX(e2.NConteo) conteo FROM t_invpiezas e2 
                    WHERE  e2.cve_articulo = e.cve_articulo AND e2.idy_ubica = e.idy_ubica AND e2.cve_lote = e.cve_lote AND e2.ID_Inventario = e.ID_Inventario
                )

            UNION ALL 

            SELECT 	 
                e.id_invcajas id
                , e.NConteo conteo
                , e.idy_ubica ubicacion_id
                , e.cve_articulo AS clave_producto
                , e.Cantidad AS existencia
                , e.cve_lote AS lote
                , p.des_articulo AS nombre_producto
                , a.id almacen_id  
                , a.clave AS clave_almacen
                , a.nombre AS nombre_almacen	
                , z.cve_almac AS zona_id
                , z.des_almac AS nombre_zona
                , 2 as tipo
            FROM t_invcajas e
                JOIN c_articulo p ON e.cve_articulo = p.cve_articulo
                JOIN c_almacen z ON z.cve_almac = (SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = e.idy_ubica LIMIT 1)
            JOIN c_almacenp a ON z.cve_almacenp = a.id   
            WHERE ({$where}) AND e.cuarentena = 0
                AND e.Cantidad > 0 AND e.NConteo = (
                    SELECT 
                        MAX(e2.NConteo) conteo FROM t_invpiezas e2 
                    WHERE  e2.cve_articulo = e.cve_articulo AND e2.idy_ubica = e.idy_ubica AND e2.cve_lote = e.cve_lote AND e2.ID_Inventario = e.ID_Inventario
                )            
            ORDER BY nombre_producto ASC";


    // hace una llamada previa al procedimiento almacenado Lis_Facturas
    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    if( $count >0 ) {
        $total_pages = ceil($count/$limit);
        //$total_pages = ceil($count/1);
    } else {
        $total_pages = 0;
    } if ($page > $total_pages)
        $page=$total_pages;

    $arr = array();
    $i = 0;
    $gg=1;
    while ($row = mysqli_fetch_array($res)) {
        //$row=array_map('utf8_encode',$row);
        $arr[] = $row;
        $responce->rows[$i]['id']=$gg;
        $responce->rows[$i]['cell']=array(
            utf8_encode($row['clave_producto']), 
            utf8_encode($row['nombre_producto']),
            utf8_encode($row['existencia']), 
            2,
            1
        );
        //$responce->rows[$i]['cell']=array($row['cve_almac'],$row['cve_ubicacion'], utf8_encode($row['cve_articulo']), utf8_encode($row['des_articulo']), utf8_encode($row['cve_lote']), utf8_encode($row['Suma']));
        $i++;
        $gg++;
    }
    echo json_encode($responce);

}


/**
 * Trae los articulos del almacen y filtra por articulo y descripción
 */
if( $_POST['action'] == 'buscarArticulosEnAlmacen' ){

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $_page = 0;

    $almacen = explode('|',$_POST["almacen"])[0];
    $producto = $_POST["producto"];
   
    if (intval($page)>0) $_page = ($page-1)*$limit;

    $where = "a.clave = '{$almacen}' ";

    if( ! empty($producto) ){
        $where .= " AND p.cve_articulo LIKE '%{$producto}%' OR p.des_articulo LIKE '%{$producto}%' ";
    }

    $sql = "SELECT 
                e.id
                , e.NConteo conteo
                , e.idy_ubica ubicacion_id
                , e.cve_articulo AS clave_producto
                , e.Cantidad AS existencia
                , e.cve_lote AS lote
                , p.des_articulo AS nombre_producto
                , a.id almacen_id  
                , a.clave AS clave_almacen
                , a.nombre AS nombre_almacen	
                , z.cve_almac AS zona_id
                , z.des_almac AS nombre_zona
                , 1 as tipo
            FROM t_invpiezas e
                JOIN c_articulo p ON e.cve_articulo = p.cve_articulo
                JOIN c_almacen z ON z.cve_almac = (SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = e.idy_ubica LIMIT 1)
            JOIN c_almacenp a ON z.cve_almacenp = a.id   
            WHERE ({$where}) AND e.cuarentena = 0
                AND e.Cantidad > 0 AND e.NConteo = (
                    SELECT 
                        MAX(e2.NConteo) conteo FROM t_invpiezas e2 
                    WHERE  e2.cve_articulo = e.cve_articulo AND e2.idy_ubica = e.idy_ubica AND e2.cve_lote = e.cve_lote AND e2.ID_Inventario = e.ID_Inventario
                )

            UNION ALL 

            SELECT 	 
                e.id_invcajas id
                , e.NConteo conteo
                , e.idy_ubica ubicacion_id
                , e.cve_articulo AS clave_producto
                , e.Cantidad AS existencia
                , e.cve_lote AS lote
                , p.des_articulo AS nombre_producto
                , a.id almacen_id  
                , a.clave AS clave_almacen
                , a.nombre AS nombre_almacen	
                , z.cve_almac AS zona_id
                , z.des_almac AS nombre_zona
                , 2 as tipo
            FROM t_invcajas e
                JOIN c_articulo p ON e.cve_articulo = p.cve_articulo
                JOIN c_almacen z ON z.cve_almac = (SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = e.idy_ubica LIMIT 1)
            JOIN c_almacenp a ON z.cve_almacenp = a.id   
            WHERE ({$where}) AND e.cuarentena = 0
                AND e.Cantidad > 0 AND e.NConteo = (
                    SELECT 
                        MAX(e2.NConteo) conteo FROM t_invpiezas e2 
                    WHERE  e2.cve_articulo = e.cve_articulo AND e2.idy_ubica = e.idy_ubica AND e2.cve_lote = e.cve_lote AND e2.ID_Inventario = e.ID_Inventario
                )            
            ORDER BY nombre_producto ASC";


    // hace una llamada previa al procedimiento almacenado Lis_Facturas
    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    if( $count >0 ) {
        $total_pages = ceil($count/$limit);
        //$total_pages = ceil($count/1);
    } else {
        $total_pages = 0;
    } if ($page > $total_pages)
        $page=$total_pages;

    $i = 0;
    $gg=1;
    
    while ($row = mysqli_fetch_array($res)) {
        //$row=array_map('utf8_encode',$row);
        $arr[] = $row;
        $responce->rows[$i]['id']=$gg;
        $responce->rows[$i]['cell']=array(
            utf8_encode($row['clave_producto']), 
            utf8_encode($row['nombre_producto']),
            utf8_encode($row['existencia']), 
            2,
            1
        );
        //$responce->rows[$i]['cell']=array($row['cve_almac'],$row['cve_ubicacion'], utf8_encode($row['cve_articulo']), utf8_encode($row['des_articulo']), utf8_encode($row['cve_lote']), utf8_encode($row['Suma']));
        $i++;
        $gg++;
    }
    echo json_encode($responce);
    exit;

}




if( $_GET['action'] == 'traerArticulosDeAlmacenExist22' ){

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $_page = 0;

    $almacen = $_GET["almacen"];
    $articulo = $_GET['articulo'];

    if (intval($page)>0) $_page = ($page-1)*$limit;

    $sql = "SELECT 
                a.cve_almac,
                art.control_numero_series AS serie, 
                a.cve_ubicacion,
                a.cve_articulo,
                a.cve_articulo AS cve_articulos, 
                art.des_articulo,
                a.cve_lote,
                SUM(a.Existencia) AS suma, 
                (CASE 
                    WHEN a.tipo = 'area' AND a.cve_ubicacion IS NOT NULL THEN 
                        (SELECT 
                                desc_ubicacion AS ubicacion 
                        FROM tubicacionesretencion WHERE cve_ubicacion = a.cve_ubicacion) 
                    WHEN a.tipo = 'ubicacion' AND a.cve_ubicacion IS NOT NULL THEN 
                        (SELECT 
                                u.CodigoCSD AS ubicacion  
                        FROM c_ubicacion u WHERE u.idy_ubica = a.cve_ubicacion)
                    ELSE '--'
                END) AS ubicacion 
            FROM V_ExistenciaGralProduccion a, c_articulo art 
            WHERE 
                a.cve_articulo = a.cve_articulo AND 
                a.cve_articulo = art.cve_articulo AND 
                a.cve_articulo = '{$articulo}' AND
                a.cve_almac = '{$almacen}' AND 
                a.cve_articulo NOT IN (
                    SELECT 
                            art.cve_articulo 
                    FROM cab_planifica_inventario ca, det_planifica_inventario p,c_articulo art 
                    WHERE 
                        ca.cve_articulo = a.cve_articulo AND 
                        p.cve_articulo = art.cve_articulo AND 
                        p.cve_articulo = a.cve_articulo
                    ) AND 
                a.cve_articulo NOT IN (
                    SELECT 
                            art.cve_articulo 
                    FROM cab_planifica_inventario ca,det_planifica_inventario p,c_articulo art 
                    WHERE 
                        ca.cve_articulo = a.cve_articulo AND 
                        p.cve_articulo = art.cve_articulo AND 
                        p.cve_articulo = a.cve_articulo
                    ) AND 
                a.tipo = 'ubicacion' 
            GROUP BY a.cve_articulo, a.cve_ubicacion  
            ORDER BY suma DESC";
    // hace una llamada previa al procedimiento almacenado Lis_Facturas
    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    if( $count >0 ) {
        $total_pages = ceil($count/$limit);
        //$total_pages = ceil($count/1);
    } else {
        $total_pages = 0;
    } if ($page > $total_pages)
        $page=$total_pages;

    $arr = array();
    $i = 0;
    $gg=1;
    while ($row = mysqli_fetch_array($res)) {
        //$row=array_map('utf8_encode',$row);
        $arr[] = $row;
        $responce->rows[$i]['id']=$gg;
        $responce->rows[$i]['cell']=array(utf8_encode($row['cve_ubicacion']), $row['ubicacion'],$row['cve_articulo'], utf8_encode($row['cve_lote']), utf8_encode($row['serie']), utf8_encode($row['Suma']));
        //$responce->rows[$i]['cell']=array($row['cve_almac'],$row['cve_ubicacion'], utf8_encode($row['cve_articulo']), utf8_encode($row['des_articulo']), utf8_encode($row['cve_lote']), utf8_encode($row['Suma']));
        $i++;
        $gg++;
    }
    echo json_encode($responce);

}


if( $_POST['action'] == 'buscarArticulos' ) {   
    $articulos = $ga->buscarArticulos($_POST["almacen"], $_POST["parameter"]);

    $arr = array(
        "success" => true,        
        "articulos" => $articulos
    );


    $arr = array_merge($arr);

    echo json_encode($arr);
}

if( $_POST['action'] == 'inUse' ) {
    $use = $ga->inUse($_POST);
    $arr = array(
        "success" => $use,
    );

    echo json_encode($arr);

}
