<?php
include '../../../config.php';

if(isset($_POST['action']) && !empty($_POST['action'])){ 

    switch ($_POST['action']) {
        case 'enter-view':

                $sql = 'SELECT cve_usuario FROM c_usuario WHERE Activo = 1';
                $res = getArraySQL($sql);

                $array = [
                    "users"=>$res
                ];

                echo json_encode($array);
            break;
        case 'getListTable':

                $sql_user = "";
                $sql_feIn = "";
                $sql_feEn = "";

                if(isset($_POST['user'])){
                    $sql_user = " and a.cve_usuario = '".$_POST['user']."'";
                }

                if(isset($_POST['feIn'])){
                    $sql_feIn = " and a.Fecha >= '".$_POST['feIn']."'";
                }

                if(isset($_POST['feEn'])){
                    $sql_feEn = " and a.Fecha <= '".$_POST['feEn']."'";
                }

                $sql = 'SELECT a.cve_usuario, a.MODULO, a.mensage, a.Referencia, a.Fecha FROM t_bitacora a WHERE a.Activo = 1 '.$sql_user.$sql_feIn.$sql_feEn;

                $table = getArraySQL($sql);

                $array = [
                    "table"=>$table,
                    "sql"=>$sql
                ];

                echo json_encode($array);
            break;
        case 'getListProductos':

                $sql_feIn = "";
                $sql_feEn = "";

                if(isset($_POST['feIn'])){
                    $sql_feIn = " and b.fecha >= '".$_POST['feIn']."'";
                }

                if(isset($_POST['feEn'])){
                    $sql_feEn = " and b.fecha <= '".$_POST['feEn']."'";
                }

                $array = [];

                $sql = "SELECT a.cve_articulo, a.des_articulo FROM c_articulo a, t_cardex b, c_proveedores c, td_entalmacen d WHERE a.cve_articulo = b.cve_articulo and b.origen = c.ID_Proveedor and b.destino = d.fol_folio and a.activo = 1 and b.id_TipoMovimiento = 1 and b.cve_almac = '".$_POST['idAl']."' ".$sql_feIn.$sql_feEn." GROUP by a.cve_articulo";
                $entrada = getArraySQL($sql);

                $sql = "SELECT a.cve_articulo, a.des_articulo FROM c_articulo a, t_cardex b, td_entalmacen c, c_ubicacion d WHERE a.cve_articulo = b.cve_articulo and b.origen = c.fol_folio and b.destino = d.idy_ubica and a.activo = 1 and b.id_TipoMovimiento = 2 and  b.cve_almac = '".$_POST['idAl']."' ".$sql_feIn.$sql_feEn." GROUP by a.cve_articulo";
                $acomodo = getArraySQL($sql);

                $sql = "SELECT a.cve_articulo, a.des_articulo FROM c_articulo a, t_cardex b, c_ubicacion c WHERE a.cve_articulo = b.cve_articulo and b.origen = c.idy_ubica and b.destino = c.idy_ubica and a.activo = 1 and b.id_TipoMovimiento = 20 and b.cve_almac = '".$_POST['idAl']."' ".$sql_feIn.$sql_feEn." GROUP by a.cve_articulo";
                $traslado = getArraySQL($sql);

                $sql = "SELECT a.cve_articulo, a.des_articulo FROM c_articulo a, t_cardex b, c_ubicacion c WHERE a.cve_articulo = b.cve_articulo and b.origen = c.idy_ubica and a.activo = 1 and b.id_TipoMovimiento = 5 and b.cve_almac = '".$_POST['idAl']."' ".$sql_feIn.$sql_feEn." GROUP by a.cve_articulo";
                $entradaR = getArraySQL($sql);


                $sql = "SELECT a.cve_articulo, a.des_articulo FROM c_articulo a, t_cardex b, c_ubicacion c WHERE a.cve_articulo = b.cve_articulo and b.origen = c.idy_ubica and a.activo = 1 and b.id_TipoMovimiento = 4 and b.cve_almac = '".$_POST['idAl']."' ".$sql_feIn.$sql_feEn." GROUP by a.cve_articulo";
                $salidaR = getArraySQL($sql);

                $sql = "SELECT a.cve_articulo, a.des_articulo FROM c_articulo a, t_cardex b, c_ubicacion c WHERE a.cve_articulo = b.cve_articulo and b.origen = c.idy_ubica and a.activo = 1 and b.id_TipoMovimiento = 8 and b.cve_almac = '".$_POST['idAl']."' ".$sql_feIn.$sql_feEn." GROUP by a.cve_articulo";
                $salida = getArraySQL($sql);

                $sql = "SELECT a.cve_articulo, a.des_articulo FROM c_articulo a, t_cardex b, c_ubicacion c WHERE a.cve_articulo = b.cve_articulo and b.destino = c.idy_ubica and a.activo = 1 and b.id_TipoMovimiento = 9 and b.cve_almac = '".$_POST['idAl']."' ".$sql_feIn.$sql_feEn." GROUP by a.cve_articulo";
                $entradaA = getArraySQL($sql);

                $sql = "SELECT a.cve_articulo, a.des_articulo FROM c_articulo a, t_cardex b, c_ubicacion c WHERE a.cve_articulo = b.cve_articulo and b.origen = c.idy_ubica and a.activo = 1 and b.id_TipoMovimiento = 10 and b.cve_almac = '".$_POST['idAl']."' ".$sql_feIn.$sql_feEn." GROUP by a.cve_articulo";
                $salidaA = getArraySQL($sql);

                array_push($array, $entrada);
                array_push($array, $acomodo);
                array_push($array, $traslado);
                array_push($array, $entradaR);
                array_push($array, $salidaR);
                array_push($array, $salida);
                array_push($array, $entradaA);
                array_push($array, $salidaA);

                $array = [
                    "articulos"=>$array
                ];

                echo json_encode($array);
            break;
        case 'getListLote':

                $sql_feIn = "";
                $sql_feEn = "";

                if(isset($_POST['feIn'])){
                    $sql_feIn = " and b.fecha >= '".$_POST['feIn']."'";
                }

                if(isset($_POST['feEn'])){
                    $sql_feEn = " and b.fecha <= '".$_POST['feEn']."'";
                }

                $array = [];

                $sql = "SELECT a.lote FROM c_lotes a, t_cardex b, c_proveedores c, td_entalmacen d WHERE a.lote = b.cve_lote and b.origen = c.ID_Proveedor and b.destino = d.fol_folio and a.activo = 1 and b.id_TipoMovimiento = 1 and b.cve_articulo = '".$_POST['articulo']."' ".$sql_feIn.$sql_feEn." GROUP by a.lote";
                $entrada = getArraySQL($sql);

                $sql = "SELECT a.lote FROM c_lotes a, t_cardex b, td_entalmacen c, c_ubicacion d WHERE a.lote = b.cve_lote and b.origen = c.fol_folio and b.destino = d.idy_ubica and a.activo = 1 and b.id_TipoMovimiento = 2 and  b.cve_articulo = '".$_POST['articulo']."' ".$sql_feIn.$sql_feEn." GROUP by a.lote";
                $acomodo = getArraySQL($sql);

                $sql = "SELECT a.lote FROM c_lotes a, t_cardex b, c_ubicacion c WHERE a.lote = b.cve_lote and b.origen = c.idy_ubica and a.activo = 1 and b.id_TipoMovimiento = 20 and b.cve_articulo = '".$_POST['articulo']."' ".$sql_feIn.$sql_feEn." GROUP by a.lote";
                $traslado = getArraySQL($sql);

                $sql = "SELECT a.lote FROM c_lotes a, t_cardex b, c_ubicacion c WHERE a.lote = b.cve_lote and b.origen = c.idy_ubica and a.activo = 1 and b.id_TipoMovimiento = 5 and b.cve_articulo = '".$_POST['articulo']."' ".$sql_feIn.$sql_feEn." GROUP by a.lote";
                $entradaR = getArraySQL($sql);

                $sql = "SELECT a.lote FROM c_lotes a, t_cardex b, c_ubicacion c WHERE a.lote = b.cve_lote and b.origen = c.idy_ubica and a.activo = 1 and b.id_TipoMovimiento = 4 and b.cve_articulo = '".$_POST['articulo']."' ".$sql_feIn.$sql_feEn." GROUP by a.lote";
                $salidaR = getArraySQL($sql);

                $sql = "SELECT a.lote FROM c_lotes a, t_cardex b, c_ubicacion c WHERE a.lote = b.cve_lote and b.origen = c.idy_ubica and a.activo = 1 and b.id_TipoMovimiento = 8 and b.cve_articulo = '".$_POST['articulo']."' ".$sql_feIn.$sql_feEn." GROUP by a.lote";
                $salida = getArraySQL($sql);

                $sql = "SELECT a.lote FROM c_lotes a, t_cardex b, c_ubicacion c WHERE a.lote = b.cve_lote and b.destino = c.idy_ubica and a.activo = 1 and b.id_TipoMovimiento = 9 and b.cve_articulo = '".$_POST['articulo']."' ".$sql_feIn.$sql_feEn." GROUP by a.lote";
                $entradaA = getArraySQL($sql);

                $sql = "SELECT a.lote FROM c_lotes a, t_cardex b, c_ubicacion c WHERE a.lote = b.cve_lote and b.origen = c.idy_ubica and a.activo = 1 and b.id_TipoMovimiento = 10 and b.cve_articulo = '".$_POST['articulo']."' ".$sql_feIn.$sql_feEn." GROUP by a.lote";
                $salidaA = getArraySQL($sql);

                array_push($array, $entrada);
                array_push($array, $acomodo);
                array_push($array, $traslado);
                array_push($array, $entradaR);
                array_push($array, $salidaR);
                array_push($array, $salida);
                array_push($array, $entradaA);
                array_push($array, $salidaA);

                $array = [
                    "lote"=>$array
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