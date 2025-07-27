<?php
include '../../../config.php';

error_reporting(0);

if (isset($_POST) && !empty($_POST) && $_POST['action'] === 'loadGrid') {
    $page = $_POST['page']; // get the requested page
    $limit = $_POST['rows']; // get how many rows we want to have into the grid
    $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
    $sord = $_POST['sord']; // get the direction
    $almacen = $_POST['id_almacen'];
    $cve_articulo = $_POST['cve_articulo'];
    $status = $_POST['status'];
    $buscar_bl =  $_POST['buscar_bl'];
    $buscar_fol =  $_POST['buscar_fol'];
    $_page = 0;
    $split = "";
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
  
    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))
        echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

    if(isset($status) && $status != "")
    {
        $split .= " AND IFNULL(t_movcuarentena.Id_MotivoLib,0) ".(($status == "Abierto")?"=":">")." 0 ";
    }
  
    if(isset($buscar_bl) && $buscar_bl != "")
    {
        $split .= " AND c_ubicacion.codigoCSD like '%".$buscar_bl."%' ";  
    }
  
    if(isset($buscar_fol) && $buscar_fol != "")
    {
        $split .=" AND t_movcuarentena.Fol_Folio like '%".$buscar_fol."%' ";
    }
  
    if(isset($cve_articulo) && $cve_articulo != "")
    {
        $split .=" AND t_movcuarentena.Cve_Articulo like '%".$cve_articulo."%' ";
    }
  
    if(isset($almacen) && $almacen != "")
    {
        $split .=" AND c_almacenp.id = '".$almacen."' ";
    }
  
    if(!$sidx) $sidx =1;

    $sql = "
        SELECT
            count(*) as cuenta
        FROM 	t_movcuarentena
            INNER JOIN c_articulo on c_articulo.cve_articulo = t_movcuarentena.Cve_Articulo
            LEFT JOIN c_ubicacion on c_ubicacion.idy_ubica = t_movcuarentena.Idy_Ubica
            LEFT JOIN c_charolas on c_charolas.IDContenedor = t_movcuarentena.IdContenedor
            LEFT JOIN c_motivo me on me.id = t_movcuarentena.Id_MotivoIng and me.Tipo_Cat = 'Q'
            LEFT JOIN c_motivo ms on ms.id = t_movcuarentena.Id_MotivoLib and ms.Tipo_Cat = 'S'
            LEFT JOIN c_almacen on c_almacen.cve_almac = c_ubicacion.cve_almac
            LEFT JOIN c_almacenp on c_almacenp.id = c_almacen.cve_almacenp
        WHERE 1
            ".$split."
        LIMIT {$_page},{$limit};
    ";
    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
    }
    $row = mysqli_fetch_array($res);
    $count = $row['cuenta'];
  	$_page = 0;
	  if (intval($page)>0) $_page = ($page-1)*$limit;

    $sql = "
        SELECT
            t_movcuarentena.Fol_Folio AS folio,
            t_movcuarentena.Cve_Articulo AS clave,
            c_articulo.des_articulo AS descripcion,
            c_ubicacion.CodigoCSD AS bl,
            c_charolas.clave_contenedor AS tipo,
            IF(IFNULL(t_movcuarentena.Id_MotivoLib,0) > 0 ,'Cerrado','Abierto') AS estatus,
            date_format(t_movcuarentena.Fec_Ingreso,'%d-%m-%Y %k:%i:%s') AS fechaQA,
            t_movcuarentena.Fec_Ingreso AS fechaOrden,
            me.Des_Motivo AS motivo,
            date_format(t_movcuarentena.Fec_Libera,'%d-%m-%Y %k:%i:%s') AS fecha,
            SEC_TO_TIME(TIMESTAMPDIFF(SECOND,t_movcuarentena.Fec_Ingreso,IFNULL(t_movcuarentena.Fec_Libera,now()))) AS tiempo,
            ms.Des_Motivo AS motivo2,
            (SELECT nombre_completo FROM c_usuario WHERE t_movcuarentena.Usuario_Ing IN (cve_usuario, id_user)) AS usuario_ing,
            (SELECT nombre_completo FROM c_usuario WHERE t_movcuarentena.Usuario_Lib IN (cve_usuario, id_user)) AS usuario_lib,
            c_almacenp.nombre AS almacen,
            c_almacen.des_almac AS almacenaje
        FROM 	t_movcuarentena
            INNER JOIN c_articulo on c_articulo.cve_articulo = t_movcuarentena.Cve_Articulo
            LEFT JOIN c_ubicacion on c_ubicacion.idy_ubica = t_movcuarentena.Idy_Ubica
            LEFT JOIN c_charolas on c_charolas.IDContenedor = t_movcuarentena.IdContenedor
            LEFT JOIN c_motivo me on me.id = t_movcuarentena.Id_MotivoIng and me.Tipo_Cat = 'Q'
            LEFT JOIN c_motivo ms on ms.id = t_movcuarentena.Id_MotivoLib and ms.Tipo_Cat = 'S'
            LEFT JOIN c_almacen on c_almacen.cve_almac = c_ubicacion.cve_almac
            LEFT JOIN c_almacenp on c_almacenp.id = c_almacen.cve_almacenp
        WHERE 1
            ".$split."
        ORDER BY fechaOrden DESC
        LIMIT {$_page},{$limit};
    ";
    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
    }

    if( $count >0 ) {
        $total_pages = ceil($count/$limit);
    } else {
        $total_pages = 0;
    } if ($page > $total_pages)
        $page=$total_pages;

    $responce->page = $page;
    $responce->total = $total_pages;
    $responce->records = $count;
    $responce->query = $sql;

    $arr = array();
    $i = 0;
    while ($row = mysqli_fetch_array($res)) {
        $row=array_map('utf8_encode', $row);
        $row["dim"] = number_format($row['num_alto'], 2, '.', '').'  X  '.number_format($row['num_ancho'], 2, '.', '').'  X  '.number_format($row['num_largo'], 2, '.', '');
        $arr[] = $row;
        extract($row);
        $arreglo_tiempo = explode(":",$tiempo);
        if($arreglo_tiempo[0] > 24){
            $dia = intval($arreglo_tiempo[0] / 24);
            $horas = $arreglo_tiempo[0] % 24;
        }
        else{
            $dia = "";
            $horas = $arreglo_tiempo[0];
        }

        $tiempo = $dia." ".$horas.":".$arreglo_tiempo[1].":".$arreglo_tiempo[2]; 

        $responce->rows[$i]['id']=$row['idy_ubica'];
        $responce->rows[$i]['cell']=array("",$folio,$clave, $descripcion,$bl,$tipo,$estatus,$fechaQA,$motivo, $usuario_ing,$fecha,$tiempo,$motivo2,$usuario_lib,$almacen,$almacenaje);
        $i++;
    }
    echo json_encode($responce);
}

if(isset($_POST) && !empty($_POST['action']) && $_POST['action'] === 'loadStatistics'){
    $almacen = $_POST['almacen'];
    $almacenaje = $_POST['almacenaje'];
    $total_ubicaciones = 0;
    $ocupadas = 0;
    $porcentaje_ocupadas = 0;

    if(empty($almacenaje) && !empty($almacen)){
        $sql = "SELECT idy_ubica AS id FROM c_ubicacion WHERE cve_almac IN (SELECT cve_almac FROM c_almacen WHERE cve_almacenp = '$almacen');";
    }else{
        $sql = "SELECT idy_ubica AS id FROM c_ubicacion WHERE cve_almac = '$almacenaje'";
    }

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $query = mysqli_query($conn,$sql);
    if($query->num_rows > 0){
        $all_ids = mysqli_fetch_all($query, MYSQLI_ASSOC);
        $total_ubicaciones = intval(count($all_ids));
        $ids = '';
        foreach($all_ids as $key => $value){
            extract($value);
            $ids .= "{$id}";
            if($key !== ($total_ubicaciones - 1) ){
                $ids .= ',';
            }
        }
        $sqlOcupadas = "SELECT COUNT(DISTINCT cve_ubicacion) AS ocupadas FROM V_ExistenciaGral WHERE cve_ubicacion IN ($ids)";
        $query = mysqli_query($conn, $sqlOcupadas);
        if($query->num_rows > 0){
            $ocupadas = intval(mysqli_fetch_row($query)[0]);
        }
    }
    if($total_ubicaciones > 0){
        $porcentaje_ocupadas = ($ocupadas * 100) / $total_ubicaciones;
    }
    $vacias = $total_ubicaciones - $ocupadas;
    echo json_encode(array(
        'total'                 => $total_ubicaciones,
        'porcentajeocupadas'    => number_format($porcentaje_ocupadas, 2, ',', '.'),
        'vacias'                => $vacias
    ));
}

if (isset($_POST) && !empty($_POST) && $_POST['action'] === 'traer_articulos'){
    $almacen = $_POST['almacen'];
    
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    
    $sql="
        SELECT 
            c_articulo.cve_articulo,
            des_articulo
        FROM c_articulo
        inner join t_movcuarentena on t_movcuarentena.Cve_Articulo = c_articulo.cve_articulo
        group by c_articulo.cve_articulo
    ";
    
    if (!($res = mysqli_query($conn, $sql))){echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
    
    $articulos = array();
    foreach( mysqli_fetch_all($res) as $key=> $value)
    {
      $articulos[$key] = array_map("utf8_encode", $value);
    }
    
    $responce->success = true;
    $responce->articulos = $articulos;
    echo json_encode($responce);
}
