 <?php
    include '../../../config.php';
    error_reporting(0);
    header('Access-Control-Allow-Credentials: false');  
    header("Access-Control-Allow-Methods: POST, GET");
    header("Content-Type: text/html; charset=UTF-8; application/json"); 

    $ands ="";
    $ARRAY;

    $_articulo = getValue('articulo');
    $_lote = getValue('lote');
    $_tipo = getValue('tipo');
    $_almacen = getValue('almacen');

    if(!empty($_almacen)) $ands .= "and a.cve_almac='".$_almacen."' ";
    if(!empty($_articulo)) $ands .= "and a.cve_articulo = '".$_articulo."' ";
    if(!empty($_lote)) $ands .= "and a.cve_lote = '".$_lote."' ";
    if(!empty($_tipo)) $ands .= "and a.tipo = '".$_tipo."' ";

    $sql = "SELECT a.cve_almac, (b.nombre)almacen_nombre, a.cve_articulo, c.des_articulo, a.cve_ubicacion, (d.desc_ubicacion)ubi_nombre, a.cve_lote, a.tipo, a.Existencia FROM V_ExistenciaGral a, c_almacenp b, c_articulo c, tubicacionesretencion d WHERE a.cve_almac = b.id and a.cve_articulo = c.cve_articulo  and a.cve_ubicacion = d.cve_ubicacion ".$ands;

    $AREA = getArraySQL($sql);

    $sql = "SELECT a.cve_almac, (b.nombre)almacen_nombre, a.cve_articulo, c.des_articulo, a.cve_ubicacion, (e.des_almac)ubi_nombre, a.cve_lote, a.tipo, a.Existencia FROM V_ExistenciaGral a, c_almacenp b, c_articulo c, c_ubicacion d, c_almacen e WHERE a.cve_almac = b.id and a.cve_articulo = c.cve_articulo and a.cve_ubicacion = d.idy_ubica and d.cve_almac = e.cve_almac ".$ands;

    $UBICACION = getArraySQL($sql);

    $ARRAY= array_merge($AREA, $UBICACION);

    echo json_encode($ARRAY);
           
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
