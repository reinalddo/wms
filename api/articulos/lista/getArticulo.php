 <?php
    include '../../../config.php';
    
    error_reporting(0);
    header('Access-Control-Allow-Credentials: false');  
    header("Access-Control-Allow-Methods: POST, GET");
    header("Content-Type: text/html; charset=UTF-8; application/json"); 

    $ands ="";
    $_criterio = getValue('filtro');
    $_grupo = getValue('grupo');
    $_clasificacion = getValue('clasificacion');
    $_tipo = getValue('tipo');
    $_compuesto = getValue('compuesto');
    $_almacen = getValue('almacen');

    if(!empty($_almacen)) $ands .= "and a.cve_almac='".$_almacen."' ";
    if(!empty($_grupo)) $ands .= "and c_gpoarticulo.cve_gpoart = '".$_grupo."' ";
    if(!empty($_clasificacion)) $ands .= "and c_sgpoarticulo.cve_sgpoart = '".$_clasificacion."' ";
    if(!empty($_tipo)) $ands .= "and c_ssgpoarticulo.cve_ssgpoart = '".$_tipo."' ";
    if(!empty($_compuesto)) $ands .= "and a.Compuesto='".$_compuesto."' ";
    if(!empty($_criterio)){
        $ands.=" and CONCAT_WS(' ', a.cve_articulo, a.des_articulo, a.cve_codprov) like '%".$_criterio."%' ";
    }

    $sql = "Select a.id, a.cve_articulo, a.des_articulo, a.cve_almac, a.cve_umed, a.cve_ssgpo, a.fec_altaart, a.num_multiplo, a.des_observ, a.PrecioVenta, a.cve_tipcaja, a.cve_codprov, a.ID_Proveedor, a.peso, a.num_multiploch, a.barras2, a.barras3, a.Caduca, a.Max_Cajas, a.Activo, cajas_palet, a.control_lotes, a.control_numero_series, a.control_peso, a.control_volumen, a.req_refrigeracion, a.mat_peligroso, a.grupo, a.clasificacion, a.tipo, a.tipo_caja, a.alto, a.fondo, a.ancho, a.costo,
        ts_ubicxart.CapacidadMinima as stock_minimo,
        c_almacenp.nombre as almacen,
        ts_ubicxart.CapacidadMaxima as stock_maximo,
        c_gpoarticulo.des_gpoart as grupoa,
        c_sgpoarticulo.des_sgpoart as clasificaciona,
        c_ssgpoarticulo.des_ssgpoart as tipoa,
        a.Compuesto as compuesto
        from c_articulo a
        left join ts_ubicxart
        on a.cve_articulo = ts_ubicxart.cve_articulo
        left join c_gpoarticulo
        on a.grupo = c_gpoarticulo.cve_gpoart
        left join c_sgpoarticulo
        on a.clasificacion = c_sgpoarticulo.cve_sgpoart
        left join c_ssgpoarticulo
        on a.tipo = c_ssgpoarticulo.cve_ssgpoart
        left join c_almacenp
        on a.cve_almac = c_almacenp.id
        Where a.Activo = '1' ".$ands;

    $res = getArraySQL($sql);

    echo json_encode($res);
           
function getValue($name){
    $data = json_decode(file_get_contents('php://input'), true);

    if(isset($data[$name])){
        return $data[$name];
    }
    else{
        if(isset($_GET[$name]))
            return $_GET[$name];
    }
}

function getArraySQL($sql){

    $conexion = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    mysqli_set_charset($conexion, "utf8");

    if(!$result = mysqli_query($conexion, $sql)) 
        echo "Falló la preparación: (" . mysqli_error($conexion) . ") ";;

    $rawdata = array();

    $i = 0;

    while($row = mysqli_fetch_assoc($result))
    {
        $rawdata[$i] = $row;
        $i++;
    }

    mysqli_close($conexion);

    return $rawdata;
}
