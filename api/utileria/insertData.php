<?php
include '../../config.php';

if(isset($_POST['action']) && !empty($_POST['action'])){ 

    switch ($_POST['action']) {
        case 'enter-view':

                $sql = 'SELECT id, clave, nombre FROM c_almacenp';
                $almacens = getArraySQL($sql);

                $array = [
                    "almacens"=>$almacens
                ];

                echo json_encode($array);
            break;
        case 'insert-product':

                $sql1 = "SELECT cve_articulo FROM c_articulo WHERE cve_articulo = '".$_POST['cve']."'";

                $validate = getArraySQL($sql1);
                $msj = 'success';

                if(!empty($validate) && is_array($validate)){
                    $msj = 'error';
                }
                else{
                    $sql = "INSERT INTO c_articulo (cve_articulo, des_articulo, ID_Proveedor, peso, cve_codprov, Caduca, Compuesto, Activo, cajas_palet, control_lotes, tipo_caja, alto, fondo, ancho, costo, grupo, cve_almac, barras2, num_multiplo) VALUES ('".strip_tags($_POST['cve'])."','".strip_tags($_POST['descrip'])."','".strip_tags($_POST['almacen'])."','".strip_tags($_POST['peso'])."','".strip_tags($_POST['codiBarra'])."','".strip_tags($_POST['caduca'])."','".strip_tags($_POST['compuesto'])."','".strip_tags($_POST['activo'])."','".strip_tags($_POST['cajas_palet'])."','".strip_tags($_POST['lotes'])."','".strip_tags($_POST['tipCja'])."','".strip_tags($_POST['alto'])."','".strip_tags($_POST['fondo'])."','".strip_tags($_POST['ancho'])."','".strip_tags($_POST['costo'])."', '".strip_tags($_POST['grupo'])."', '2', '".strip_tags($_POST['codigo_caja'])."', '".strip_tags($_POST['uni_caja'])."')";
                    executeSQL($sql);
                }
                        
                $array = [
                    "sql"=>$sql,
                    "msj"=>$msj
                ];

                echo json_encode($array);
            break;
        case 'insert-caja':

                $sql1 = "SELECT clave FROM c_tipocaja WHERE clave = '".$_POST['cve']."'";

                $validate = getArraySQL($sql1);
                $msj = 'success';

                if(!empty($validate) && is_array($validate)){
                    $msj = 'error';
                }
                else{
                    $sql = "INSERT INTO c_tipocaja (clave, descripcion, largo, alto, ancho, Packing, Activo) VALUES ('".strip_tags($_POST['cve'])."','".strip_tags($_POST['descrip'])."','".strip_tags($_POST['fondo'])."','".strip_tags($_POST['alto'])."','".strip_tags($_POST['ancho'])."','N','1')";
                    executeSQL($sql);
                }
                        
                $array = [
                    "sql"=>$sql,
                    "msj"=>$msj
                ];

                echo json_encode($array);
            break;
        case 'insert-data':

                $_lote = true;
                $_ubi = true;

                if(isset($_POST['svLo'])){

                    $sql = "SELECT LOTE, cve_articulo FROM c_lotes WHERE cve_articulo = '".$_POST['cve_arti']."' and LOTE = '".$_POST['lote']."'";
                    $validate = getArraySQL($sql);

                    if(!empty($validate) && is_array($validate)){
                        $_lote = false;
                    }
                    else{

                        $sql = "INSERT INTO c_lotes (LOTE, cve_articulo, CADUCIDAD, Activo) VALUES ('".strip_tags($_POST['lote'])."','".strip_tags($_POST['cve_arti'])."','".strip_tags($_POST['caducidad'])."','1')";
                        executeSQL($sql);
                    }
                }

                $sql = "SELECT idy_ubica FROM c_ubicacion WHERE CodigoCSD = '".$_POST['ubi']."'";

                $res = getArraySQL($sql);

                if(!empty($res) && is_array($res)){
                    
                    $sql = "INSERT INTO ts_existenciapiezas (cve_almac, idy_ubica, cve_articulo, cve_lote, Existencia) VALUES ('".strip_tags($_POST['almacen'])."','".$res[0]["idy_ubica"]."','".strip_tags($_POST['cve_arti'])."','".strip_tags($_POST['lote'])."','".strip_tags($_POST['exist'])."')";
                    executeSQL($sql);
                }
                else{
                    $_ubi = false;
                }
                        
                $array = [
                    "lote"=>$_lote,
                    "ubi"=>$_ubi,
                    "sql"=>$sql
                ];

                echo json_encode($array);
            break;
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

function executeSQL($sql){

    $conexion = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    mysqli_set_charset($conexion, "utf8");

    $result = mysqli_query($conexion, $sql);

    if($result) {
        $res = "success";
    }
    else{
        $res = "Error: " . $sql . "<br>" . mysqli_error($conexion);
    }

    $array = ["res" => $res];

    return $array;

    disconnectDB($conexion);
}