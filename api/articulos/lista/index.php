<?php
include '../../../config.php';
//include ("barcode.php");

error_reporting(0);

if (isset($_POST) && !empty($_POST)) {
    $page = $_POST['page']; // get the requested page
    $limit = $_POST['rows']; // get how many rows we want to have into the grid
    $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
    $sord = $_POST['sord']; // get the direction

    //////////////////////////////////////////////se recibe los parametros POST del grid////////////////////////////////////////////////
    $ands ="";

    $_criterio = $_POST['criterio'];
    if (!empty($_criterio)){
        $ands.=" AND ((a.cve_articulo LIKE '%{$_criterio}%' OR a.cve_alt LIKE '%{$_criterio}%' OR a.des_articulo LIKE '%{$_criterio}%') OR CONCAT_WS(' ', a.cve_articulo, a.des_articulo, a.cve_codprov) like '%{$_criterio}%')";
    }

    $_grupo = $_POST['grupo'];
    $_clasificacion = $_POST['clasificacion'];
    $_tipo = $_POST['tipo'];
    $compuesto = $_POST['compuesto'];
    $almacen = $_POST['almacen'];
    $instancia = $_POST['instancia'];
    $proveedor = $_POST['proveedor'];

    if(isset($_POST['id_proveedor']))
    {
        if($_POST['id_proveedor'] != "")
        {
            $proveedor = $_POST['id_proveedor'];
        }
    }

    $sql_proveedor_0 = "";
    if($instancia != 'foam')
    {
    if (!empty($almacen)) {$ands .= "AND ra.Cve_Almac='{$almacen}' ";}
    //if (!empty($_grupo)) $ands .= "AND c_gpoarticulo.id = '{$_grupo}' ";
    //if (!empty($_grupo)) $ands .= "AND a.grupo = '{$_grupo}' ";
    if (!empty($_grupo)) $ands .= " AND a.grupo = c_gpoarticulo.cve_gpoart AND c_gpoarticulo.id = '{$_grupo}' ";
    //if (!empty($_clasificacion)) $ands .= "AND c_sgpoarticulo.cve_sgpoart = '{$_clasificacion}' ";
    if (!empty($_clasificacion)) $ands .= "AND a.clasificacion = '{$_clasificacion}' ";
    //if (!empty($_tipo)) $ands .= "AND c_ssgpoarticulo.cve_ssgpoart = '{$_tipo}' ";
    if (!empty($_tipo)) $ands .= "AND a.tipo = '{$_tipo}' ";
    }
    if (!empty($compuesto)) $ands .= "AND a.Compuesto='{$compuesto}' ";

    if (!empty($proveedor)) {$ands .= "AND (c_proveedores.ID_Proveedor ='{$proveedor}') ";}//OR IFNULL(a.ID_Proveedor, 0) = 0
    //else $sql_proveedor_0 = " OR (IFNULL(a.ID_Proveedor, 0) = 0 AND a.Activo = '1') ";

    //if (!empty($_criterio)) $ands = '('.$ands.')';
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    if (!empty($_FecIni)) $_FecIni = date("Y-m-d", strtotime($_FecIni));
    if (!empty($_FecFin)) $_FecFin = date("Y-m-d", strtotime($_FecFin));

    $start = $limit*$page - $limit; // do not put $limit*($page - 1)

    if(!$sidx) $sidx =1;

    // se conecta a la base de datos
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

/*
    // prepara la llamada al procedimiento almacenado Lis_Facturas
    $sqlCount = "SELECT count(a.cve_articulo) AS cuenta FROM c_articulo a
    LEFT JOIN ts_ubicxart ON a.cve_articulo = ts_ubicxart.cve_articulo
    LEFT JOIN c_gpoarticulo ON a.grupo = c_gpoarticulo.cve_gpoart
    LEFT JOIN c_sgpoarticulo ON a.clasificacion = c_sgpoarticulo.cve_sgpoart
    LEFT JOIN c_ssgpoarticulo ON a.tipo = c_ssgpoarticulo.cve_ssgpoart
    LEFT JOIN c_almacenp ON a.cve_almac = c_almacenp.id
    LEFT JOIN rel_articulo_proveedor ON rel_articulo_proveedor.Cve_Articulo = a.cve_articulo
    LEFT JOIN c_proveedores ON c_proveedores.ID_Proveedor = rel_articulo_proveedor.ID_Proveedor
    WHERE a.Activo = '1' ".$ands;
  
  //echo var_dump($sqlCount);

    if (!($res = mysqli_query($conn, $sqlCount))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $row = mysqli_fetch_array($res);
    $count = $row[0];
  */
    //echo var_dump($count);
    //die();

    $_page = 0;

    if (intval($page)>0) $_page = ($page-1)*$limit;

    $sql = "SELECT a.*,
        CONCAT_WS('x', a.alto, a.fondo, a.ancho) as 'dimension',
        round((a.alto/1000)*(a.ancho/1000)*(a.fondo/1000),6) as 'volumen',
        /*ts_ubicxart.CapacidadMinima as stock_minimo,*/
        c_almacenp.nombre as almacen,
        /*ts_ubicxart.CapacidadMaxima as stock_maximo,*/
        IFNULL(c_gpoarticulo.des_gpoart, '') AS grupoa,
        IFNULL(c_sgpoarticulo.cve_sgpoart, '') AS clasificaciona,
        IFNULL(c_ssgpoarticulo.des_ssgpoart, '') AS tipoa,
        a.Compuesto as compuesto,
        a.costo,
        #a.tipo_producto,
        tp.descripcion as tipo_producto,
        a.umas,
        ai.url as imagen,
        #IF(IFNULL(a.ID_Proveedor, 0) != 0, group_concat(c_proveedores.Nombre separator ','), '') AS Proveedor,
        #IF(IFNULL(a.ID_Proveedor, 0) = 0, '', IFNULL(GROUP_CONCAT(DISTINCT c_proveedores.Nombre SEPARATOR ', '), '')) AS Proveedor,
        IFNULL(GROUP_CONCAT(DISTINCT c_proveedores.Nombre SEPARATOR ', '), '') AS Proveedor,
        IF(IFNULL(ad.cve_articulo, '') = '', 'N', 'S') AS tiene_documentos,
        a.cve_alt as clave_alterna,
        c_unimed.des_umed
        FROM c_articulo a
            /*LEFT JOIN ts_ubicxart ON a.cve_articulo = ts_ubicxart.cve_articulo*/
            LEFT JOIN Rel_Articulo_Almacen ra ON CONVERT(ra.Cve_Articulo, CHAR) = CONVERT(a.cve_articulo, CHAR) AND ra.Cve_Almac='{$almacen}'
            LEFT JOIN c_gpoarticulo ON ra.Grupo_ID = c_gpoarticulo.id
            LEFT JOIN c_sgpoarticulo ON ra.Clasificacion_ID = c_sgpoarticulo.id
            LEFT JOIN c_ssgpoarticulo ON ra.Tipo_Art_ID = c_ssgpoarticulo.id
            LEFT JOIN c_unimed ON a.unidadMedida = c_unimed.id_umed
            LEFT JOIN rel_articulo_proveedor ON CONVERT(rel_articulo_proveedor.Cve_Articulo, CHAR) = CONVERT(a.cve_articulo, CHAR) 
            LEFT JOIN c_proveedores ON c_proveedores.ID_Proveedor = rel_articulo_proveedor.ID_Proveedor 
            LEFT JOIN c_almacenp ON ra.Cve_Almac = c_almacenp.id AND c_almacenp.id ='{$almacen}'
            LEFT JOIN c_articulo_imagen ai ON CONVERT(ai.cve_articulo, CHAR) = CONVERT(a.cve_articulo, CHAR)
            LEFT JOIN c_articulo_documento ad ON CONVERT(ad.cve_articulo, CHAR) = CONVERT(a.cve_articulo, CHAR)
            LEFT JOIN c_tipo_producto tp ON CONVERT(a.tipo_producto, CHAR) = CONVERT(tp.clave, CHAR)
        WHERE (a.Activo = '1' {$ands}) {$sql_proveedor_0}  #AND rel_articulo_proveedor.Id_Proveedor != 0 
        GROUP BY a.cve_articulo
        ORDER BY a.des_articulo
        ";

        if($instancia == "foam") //la tabla Rel_Articulo_Almacen de foam tiene mas de 220mil registros, por eso la elimino
            $sql = "SELECT a.*,
                CONCAT_WS('x', a.alto, a.fondo, a.ancho) as 'dimension',
                round((a.alto/1000)*(a.ancho/1000)*(a.fondo/1000),6) as 'volumen',
                /*ts_ubicxart.CapacidadMinima as stock_minimo,*/
                '' as almacen,
                /*ts_ubicxart.CapacidadMaxima as stock_maximo,*/
                '' AS grupoa,
                '' AS clasificaciona,
                '' AS tipoa,
                a.Compuesto as compuesto,
                a.costo,
                #a.tipo_producto,
                tp.descripcion as tipo_producto,
                a.umas,
                ai.url as imagen,
                #IF(IFNULL(a.ID_Proveedor, 0) != 0, group_concat(c_proveedores.Nombre separator ','), '') AS Proveedor,
                IFNULL(GROUP_CONCAT(DISTINCT c_proveedores.Nombre SEPARATOR ', '), '') AS Proveedor,
                IF(IFNULL(ad.cve_articulo, '') = '', 'N', 'S') AS tiene_documentos,
                a.cve_alt as clave_alterna,
                c_unimed.des_umed
                FROM c_articulo a
                    /*LEFT JOIN ts_ubicxart ON a.cve_articulo = ts_ubicxart.cve_articulo*/
                    LEFT JOIN c_unimed ON a.unidadMedida = c_unimed.id_umed
                    LEFT JOIN rel_articulo_proveedor ON CONVERT(rel_articulo_proveedor.Cve_Articulo, CHAR) = CONVERT(a.cve_articulo, CHAR) 
                    LEFT JOIN c_proveedores ON c_proveedores.ID_Proveedor = rel_articulo_proveedor.ID_Proveedor 
                    LEFT JOIN c_articulo_imagen ai ON CONVERT(ai.cve_articulo, CHAR) = CONVERT(a.cve_articulo, CHAR)
                    LEFT JOIN c_articulo_documento ad ON CONVERT(ad.cve_articulo, CHAR) = CONVERT(a.cve_articulo, CHAR)
                    LEFT JOIN c_tipo_producto tp ON CONVERT(a.tipo_producto, CHAR) = CONVERT(tp.clave, CHAR)
                WHERE (a.Activo = '1' {$ands}) {$sql_proveedor_0}  #AND rel_articulo_proveedor.Id_Proveedor != 0 
                GROUP BY a.cve_articulo
                ORDER BY a.des_articulo
                ";
//     echo var_dump($sql);
//     die();
    // hace una llamada previa al procedimiento
    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación 1: (" . mysqli_error($conn) . ") ".$sql;
    }
    $count = mysqli_num_rows($res);
    $lim = "";
    if($_POST['action'] != "traer_catalogo")
        $lim = " LIMIT {$start}, {$limit}";

    $sql .= $lim;
    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación 2: (" . mysqli_error($conn) . ") ".$sql;
    }

    if($_POST['action'] != "traer_catalogo")
    {
        if( $count >0 ) {
            $total_pages = ceil($count/$limit);
            //$total_pages = ceil($count/1);
        } else {
            $total_pages = 0;
        } if ($page > $total_pages)
            $page=$total_pages;

        $responce->page = $page;
        $responce->total = $total_pages;
        $responce->records = $count;
    }

    $arr = array();
    $i = 0;
    $responce->sql = $sql;

    while ($row = mysqli_fetch_array($res)) {
        //echo var_dump($row);
        $row=array_map('utf8_encode', $row);
        $arr[] = $row;
        $responce->rows[$i]['id']=$row['cve_tipcaja'];
        $responce->rows[$i]['cell']=array(
                                          "", //0
                                          $row["id"], //1
                                          $row["cve_articulo"], //2
                                          $row["cve_codprov"], //3
                                          $row["barras2"], //4
                                          $row["barras3"], //5
                                          $row["des_articulo"], //6
                                          $row["compuesto"], //7
                                          $row["Usa_Envase"], //8
                                          $row['dimension'], //9
                                          $row['volumen'], //10
                                          $row["peso"], //11
                                          $row["costo"], //12
                                          $row["num_multiplo"], //13
                                          $row["cajas_palet"], //14
                                          $row["grupoa"], //15
                                          $row["clasificaciona"], //16
                                          $row["tipoa"], //17
                                          $row["Proveedor"], //18
                                          $row["tipo_producto"], //19
                                          $row["umas"], //20
                                          $row["des_umed"], //21
                                          $row["imagen"], //22
                                          $row["almacen"], //23
                                          nl2br($row["des_detallada"]),  //24
                                          '<img alt="'.$row["cve_articulo"].'" src="/api/articulos/lista/barcode.php?codetype=Code128&size=20&text='.$row["cve_articulo"].'&print=true"/>', //25
                                          $row["tiene_documentos"], //26
                                          $row["clave_alterna"] //27
                                         );
        $i++;
    }
    
    echo json_encode($responce);exit;
}
else if(isset($_GET) && !empty($_GET)){
    $page = 1; // get the requested page
    $limit = 100; // get how many rows we want to have into the grid
    $sidx = $_GET['sidx']; // get index row - i.e. user click to sort
    $sord = "asc"; // get the direction
    $almacen = $_GET['almacen'];
    $search = $_GET['search'];
    $searchSQL = "";
    $SQL_SFA = "";
    if(!empty($search)){
        $searchSQL = " AND CONCAT_WS(' ', c_articulo.cve_articulo, c_articulo.des_articulo) like '%$search%' ";
    }

    $_grupo = "";
    $_clasificacion = "";

    $start = $limit*$page - $limit; // do not put $limit*($page - 1)

    if(!$sidx) $sidx =1;

    // se conecta a la base de datos
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

    // prepara la llamada al procedimiento almacenado Lis_Facturas

    $id_almacen = $almacen;
    if(isset($_GET['modulo']))
    {
        $sfa_activo = $_GET['sfa_activo'];
        if($sfa_activo == 1)
        {
            //$SQL_SFA = " AND c_articulo.cve_articulo IN (SELECT Cve_Articulo FROM detallelp WHERE Cve_Almac = '$almacen' AND PrecioMin > 0) ";
        }
        
        $sql_almacen = "SELECT clave FROM c_almacenp WHERE id = '$almacen'";

        if (!($res = mysqli_query($conn, $sql_almacen))) {
            echo "Falló la preparación 3: (" . mysqli_error($conn) . ") ".$sql;
        }
        $_grupo = $_GET['grupo'];
        $_clasificacion = $_GET['clasificacion'];

        if($_grupo)
           $searchSQL .= " AND c_articulo.grupo = '{$_grupo}' ";

        if($_clasificacion)
           $searchSQL .= " AND c_articulo.clasificacion = '{$_clasificacion}' ";

        $almacen = mysqli_fetch_array($res)['clave'];
    }

    $SQL_Surtibles = "";$SQL_ArticulosConExistencia = "";
    if(isset($_GET['productos_surtibles']))
    {
        if($_GET['productos_surtibles'] == 'N')
        {
            $SQL_Surtibles = " AND IFNULL(tipo_producto, '') != 'ProductoNoSurtible' ";
        }
        else
            $SQL_ArticulosConExistencia = " AND c_articulo.cve_articulo IN (SELECT DISTINCT cve_articulo FROM V_ExistenciaGral WHERE tipo = 'ubicacion' AND cve_almac = '$id_almacen') ";
    }

    $SQL_LP = "";
    if(isset($_GET['LP']))
    {
        if($_GET['LP'])
        {
            $cvelp = $_GET['LP'];
            $SQL_LP = " AND c_articulo.cve_articulo IN (SELECT cve_articulo FROM ts_existenciatarima WHERE ntarima IN (SELECT DISTINCT IDContenedor FROM c_charolas WHERE CveLP = '$cvelp' )) ";
        }
    }

    $SQL_PRY = "";
    if(isset($_GET['pry']))
    {
        if($_GET['pry'])
        {
            $pry = $_GET['pry'];
            $SQL_PRY = " AND c_articulo.cve_articulo IN (SELECT DISTINCT cve_articulo FROM td_entalmacen WHERE fol_folio IN (SELECT Fol_Folio FROM th_entalmacen WHERE Proyecto = '$pry')) ";
        }
    }

/*
    $sqlCount = "SELECT COUNT(*) AS total FROM c_articulo WHERE cve_almac IN (SELECT id FROM c_almacenp WHERE clave = '$almacen') {$SQL_Surtibles} {$searchSQL} {$SQL_ArticulosConExistencia} {$SQL_LP} {$SQL_SFA}";

    if (!($res = mysqli_query($conn, $sqlCount))) {
        echo "Falló la preparación 4: (" . mysqli_error($conn) . ") ".$sql;
    }

    $count = mysqli_fetch_array($res)['total'];
    */
    $_page = 0;

    if (intval($page)>0) $_page = ($page-1)*$limit;

    $sql = "SELECT c_articulo.id as id, c_articulo.cve_articulo as cve_articulo, c_articulo.des_articulo as des_articulo, CONCAT_WS('x', c_articulo.alto, c_articulo.fondo, c_articulo.ancho) AS volumen 
        FROM c_articulo 
        LEFT JOIN Rel_Articulo_Almacen ra ON ra.Cve_Articulo = c_articulo.cve_articulo
        WHERE c_articulo.Activo = 1 AND ra.Cve_Almac IN (SELECT id FROM c_almacenp WHERE clave = '$almacen') {$SQL_Surtibles} {$searchSQL} {$SQL_ArticulosConExistencia} {$SQL_LP} {$SQL_SFA} {$SQL_PRY};";
    // hace una llamada previa al procedimiento
  
   // echo var_dump($sql);
    //die();
    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación 5: (" . mysqli_error($conn) . ") ".$sql;
    }

    $count = mysqli_num_rows($res);

    if( $count >0 ) {
        $total_pages = ceil($count/$limit);
    } else {
        $total_pages = 0;
    } if ($page > $total_pages)
        $page=$total_pages;

    $responce->page = $page;
    $responce->total = $total_pages;
    $responce->records = $count;
    $responce->sql = $sql;
    $i = 0;
    $arr = array();

    while ($row = mysqli_fetch_array($res)) {
        $row=array_map('utf8_encode', $row);
        $arr[] = $row;
        $responce->rows[$i]['id']=$row['cve_articulo'];
        $responce->rows[$i]['cell']=array($row['cve_articulo'], $row['des_articulo'], $row['volumen'], $row['id']);
        $i++;
    }
    $responce->arr = $arr;
    echo json_encode($responce);


}
