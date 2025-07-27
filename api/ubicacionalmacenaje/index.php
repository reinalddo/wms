<?php
include '../../config.php';

function QuitarAcentosYCaracteresEspeciales($texto)
{
    $texto = str_replace("´", "", $texto);$texto = str_replace("`", "", $texto);

    $texto = str_replace("á", "a", $texto);$texto = str_replace("é", "e", $texto);
    $texto = str_replace("í", "i", $texto);$texto = str_replace("ó", "o", $texto);
    $texto = str_replace("ú", "u", $texto);

    $texto = str_replace("à", "a", $texto);$texto = str_replace("è", "e", $texto);
    $texto = str_replace("ì", "i", $texto);$texto = str_replace("ò", "o", $texto);
    $texto = str_replace("ù", "u", $texto);

    $texto = str_replace("Á", "A", $texto);$texto = str_replace("É", "E", $texto);
    $texto = str_replace("Í", "I", $texto);$texto = str_replace("Ó", "O", $texto);
    $texto = str_replace("Ú", "U", $texto);

    $texto = str_replace("À", "A", $texto);$texto = str_replace("È", "E", $texto);
    $texto = str_replace("Ì", "I", $texto);$texto = str_replace("Ò", "O", $texto);
    $texto = str_replace("Ù", "U", $texto);

    $texto = preg_replace('([^A-Za-z0-9])', '', $texto);

    return $texto;
}


if(isset($_POST['action']) && !empty($_POST['action'])){ 

    switch ($_POST['action']) 
    {
        case 'enter-view':
                $id_user = $_POST['id_user'];
                $sql = 'SELECT c_almacenp.id, c_almacenp.clave, c_almacenp.nombre FROM c_almacenp LEFT JOIN t_usu_alm_pre ON c_almacenp.clave = t_usu_alm_pre.cve_almac WHERE c_almacenp.Activo = 1 AND t_usu_alm_pre.id_user = '.$id_user.'';
                $almacens = getArraySQL($sql);

                $array = [
                    "almacens"=>$almacens
                ];

                echo json_encode($array);
            break;
        case 'enter-codiBL':
                $cia = $_POST['cia'];
                $id_almacen = $_POST['id_almacen'];
                //$sql = 'SELECT id, cve_cia, codigo FROM t_codigocsd';
                //$sql = "SELECT codigo FROM t_codigocsd WHERE cve_cia = {$cia};";
                $sql = "SELECT codigo FROM t_codigocsd WHERE cve_almac = {$id_almacen};";
                $codigo_BL = getArraySQL($sql);

                $array = [
                    "codigoBL"=>$codigo_BL
                ];

                echo json_encode($array);
            break;
        case 'search-alma':

                $sql = 'SELECT *,a.cve_almac, a.des_almac FROM c_almacen a, c_almacenp b WHERE a.Activo = 1 and a.cve_almacenp = b.id and b.clave = "'.$_POST['id'].'"';
                $zona = getArraySQL($sql);
                $bl = "";
                if(count($zona)>0)
                {
                  $bl = $zona[0]["BL"];
                }

                $array = [
                    "zona"=>$zona,
                    'sql' =>  $sql
                ];
                //echo var_dump($array);
                //die();
                echo json_encode($array);
            break;
        case 'actualizarBL':

                $sql = 'SELECT * FROM c_almacenp WHERE clave = "'.$_POST['id'].'"';
                $result = getArraySQL($sql);
                
              
                //echo var_dump($result);
                //die();
                echo json_encode($result);
            break;
        case 'traer_BL':

                $sqlZona = "";
                if($_POST['zona'])
                {
                    $zona = $_POST['zona'];
                    $sqlZona = " c_ubicacion.cve_almac = '{$zona}' AND ";
                }
                $almacen = $_POST['almacen'];
                $sql = "
                    SELECT * FROM c_ubicacion 
                        inner join c_almacen on c_almacen.cve_almac = c_ubicacion.cve_almac
                        inner join c_almacenp on c_almacenp.id = c_almacen.cve_almacenp
                    WHERE {$sqlZona} c_almacenp.clave = '{$almacen}' and c_ubicacion.Activo = 1";
                $result = getArraySQL($sql);
                
              
                //echo var_dump($result);
                //die();
                echo json_encode($result);
            break;
        case 'traer_racks':

                $sql = '
                    SELECT DISTINCT cve_rack FROM c_ubicacion 
                        inner join c_almacen on c_almacen.cve_almac = c_ubicacion.cve_almac
                        inner join c_almacenp on c_almacenp.id = c_almacen.cve_almacenp
                    WHERE c_almacenp.clave = "'.$_POST['almacen'].'"
                    and c_ubicacion.Activo = "1"
                    ORDER BY CAST(cve_rack AS DECIMAL)
                ';
                $result = getArraySQL($sql);
                
              
                //echo var_dump($result);
                //die();
                echo json_encode($result);
            break;
        case 'search-table-info':

                $sql_almacen = "";
                $sql_zone = "";
                $sql_tipo = "";

                if(isset($_POST['alma'])){
                    $sql_almacen = " and b.cve_almacenp = '".$_POST['alma']."'";
                }

                if(isset($_POST['zone'])){
                    $sql_zone = " and b.cve_almac = '".$_POST['zone']."'";
                }

                if(isset($_POST['tipo'])){
                    $sql_tipo = " and a.Tipo = '".$_POST['tipo']."'";
                }

                $sql = 'SELECT a.idy_ubica, a.cve_almac, a.cve_pasillo, a.cve_rack, a.cve_nivel, a.num_ancho, a.num_largo, a.num_alto, a.Status, a.picking, a.Seccion, a.Ubicacion, a.orden_secuencia, a.PesoMaximo, a.PesoOcupado, a.claverp, a.CodigoCSD, a.TECNOLOGIA, a.Maneja_Cajas, a.Maneja_Piezas, a.Reabasto, a.Activo, a.Tipo, a.AcomodoMixto, a.AreaProduccion, a.Ptl, a.Maximo, a.Minimo, (b.des_almac)zona, (b.cve_almac)almacen, (b.cve_almacenp)alma FROM c_ubicacion a, c_almacen b WHERE a.cve_almac = b.cve_almac and a.Activo = 1'.$sql_almacen.$sql_zone.$sql_tipo;
                $table = getArraySQL($sql);

                $array = [
                    "table"=>$table,
                    "sql"=>$sql
                ];

                echo json_encode($array);
            break;
        case 'search-table-recu':

                $sql_almacen = "";

                if(isset($_POST['alma'])){
                    $sql_almacen = " and b.cve_almacenp = '".$_POST['alma']."'";
                }


                $sql = 'SELECT a.idy_ubica, a.cve_almac, a.cve_pasillo, a.cve_rack, a.cve_nivel, a.num_ancho, a.num_largo, a.num_alto, a.Status, a.picking, a.Seccion, a.Ubicacion, a.orden_secuencia, a.PesoMaximo, a.PesoOcupado, a.claverp, a.CodigoCSD, a.TECNOLOGIA, a.Maneja_Cajas, a.Maneja_Piezas, a.Reabasto, a.Activo, a.Tipo, a.AcomodoMixto, a.AreaProduccion, a.Ptl, a.Maximo, a.Minimo, (b.des_almac)zona, (b.cve_almac)almacen, (b.cve_almacenp)alma FROM c_ubicacion a, c_almacen b WHERE a.cve_almac = b.cve_almac and a.Activo = 0'.$sql_almacen;
                $table = getArraySQL($sql);

                $array = [
                    "table"=>$table,
                    "sql"=>$sql
                ];

                echo json_encode($array);
            break;
        case 'search-table-zona':

                $sql_almacen = "";

                if(isset($_POST['id'])){
                    $sql_almacen = '"'.$_POST['id'].'"';

                    $sql = 'SELECT (a.cve_articulo)clave, a.cve_lote, a.Existencia, (b.des_articulo)arti, (c.CodigoCSD)ubica FROM V_ExistenciaGralProduccion a, c_articulo b ,c_ubicacion c WHERE a.cve_ubicacion = c.idy_ubica and a.cve_articulo = b.cve_articulo and c.idy_ubica = '.$sql_almacen;
                    $table = getArraySQL($sql);

                    $array = [
                        "table"=>$table,
                        "sql"=>$sql
                    ];

                    echo json_encode($array);
                }
            break;
        case 'save':
            $msj = "success";
            $clave_c_almacen = $_POST['zone'];
            $query = "SELECT c_almacenp.BL FROM `c_almacen`LEFT JOIN c_almacenp ON c_almacenp.id = c_almacen.cve_almacenp WHERE cve_almac = '".$clave_c_almacen."';";

            $result =  getArraySQL($query);
            $codigoBL = $result[0]["BL"];

            $div_BL = explode("-", $codigoBL);
            $div_code = explode("-", $_POST['code']);

            $arr_BL=array();
            $g = 0;
            foreach($div_BL as $item_BL)
            {
              array_push($arr_BL, array($item_BL => $div_code[$g]));
              $g++;
            }

            //@$niveles_totales = count($_POST["niveles"]);
            //@$secciones_totales_porNivel = count($_POST["seccion"]);
            //@$ubicaciones_totales_porSeccion = count($_POST["ubSeccion"]);

            $usa_niveles = $_POST["niveles"] >= 1;
            $usa_secciones = $_POST["seccion"] >= 1;
            $usa_ubicaciones = $_POST["ubSeccion"] >= 1;

            $usa_nivel_inicial = $_POST["nivel_inicial"] >= 1;
            $usa_seccion_inicial = $_POST["seccion_inicial"]  >= 1;

            $nivel_actual = 0;
            $nivel_limite = $_POST["niveles"];
            $seccion_actual = 0;
            $seccion_limite = $_POST["seccion"];
            $ubicacion_actual = 0;
            $ubicacion_limite = ($_POST["ubSeccion"] == "")?1:$_POST["ubSeccion"];
            $orden_save = $_POST["orden_save"];
            $orden_save_arr = explode("-", $orden_save);

            if($usa_niveles)
            {
              $nivel_actual = 1;
              if($usa_nivel_inicial)
              {
                $nivel_actual = $_POST["nivel_inicial"];
              }
            }

            if($usa_secciones)
            {
              $seccion_actual = 1;
              if($usa_seccion_inicial)
              {
                $seccion_actual = $_POST["seccion_inicial"];
              }
            }

            $sql = "";

            $pre_sql = "INSERT IGNORE INTO c_ubicacion (cve_almac, cve_pasillo, cve_rack, cve_nivel, num_ancho, num_largo, num_alto, picking, Seccion, Ubicacion, PesoMaximo, CodigoCSD, TECNOLOGIA, Tipo, AcomodoMixto, AreaProduccion, AreaStagging, Status, Reabasto, Ptl, Activo, Maximo, Minimo) VALUES 
                        ('".strip_tags($_POST['zone'])."',
                        '".strip_tags(QuitarAcentosYCaracteresEspeciales($_POST['pasillo']))."',
                        '".strip_tags(QuitarAcentosYCaracteresEspeciales($_POST['rack']))."',
                        '_NIVEL_',
                        '".strip_tags($_POST['ancho'])."',
                        '".strip_tags($_POST['fondo'])."',
                        '".strip_tags($_POST['alto'])."',
                        '".strip_tags($_POST['pick'])."',
                        '_SECCION_',
                        '_UBICACION_',
                        '".strip_tags($_POST['pesoM'])."',
                        '_CODIGO_',
                        '".strip_tags($_POST['tecno'])."',
                        '".strip_tags($_POST['tipo'])."',
                        '".strip_tags($_POST['acoMixt'])."',
                        '".strip_tags($_POST['arePro'])."',
                        '".strip_tags($_POST['areStag'])."',
                        '".strip_tags($_POST['Traslado'])."',
                        '".strip_tags($_POST['c_reabasto'])."',
                        '".strip_tags($_POST['ptl'])."','1','0','0'); ";

            $arr_csd_exist = array();
            for($c_n = $nivel_actual; $c_n <= $nivel_limite; $c_n++)
            {
              for($c_s = $seccion_actual; $c_s <= $seccion_limite; $c_s++)
              {
                for($c_u = 1; $c_u <= $ubicacion_limite; $c_u++)
                {
                  $codigo = "";

                  for($i_save = 0; $i_save < count($orden_save_arr); $i_save++)
                  {
                    //Pasillo-Rack-Nivel-Sección-Posición
                        if($orden_save_arr[$i_save] == 'Pasillo')
                        {
                            if($_POST['pasillo'] != ""){$codigo .= $_POST['pasillo']."-";}
                        }
                        else if($orden_save_arr[$i_save] == 'Rack')
                        {
                            if($_POST['rack'] != ""){$codigo .= $_POST['rack']."-";}
                        }
                        else if($orden_save_arr[$i_save] == 'Nivel')
                        {
                            if($c_n != 0){$codigo .= $c_n."-";}
                        }
                        else if($orden_save_arr[$i_save] == 'Sección')
                        {
                            if($c_s != 0){$codigo .= $c_s."-";}
                        }
                        else if($orden_save_arr[$i_save] == 'Posición')
                        {
                            if($c_u != 0){$codigo .= $c_u."-";}
                        }
                  }
                      $codigo[strlen($codigo)-1] = ' ';
                      //$codigo[strlen($codigo)] = '';
                      $codigo = trim($codigo);
                      //$codigo = str_replace(' ', '', $codigo);

/*
                  if($_POST['pasillo'] != ""){$codigo .= $_POST['pasillo']."-";}
                  if($_POST['rack'] != ""){$codigo .= $_POST['rack']."-";}
                  if($c_n != 0){$codigo .= $c_n."-";}
                  if($c_s != 0){$codigo .= $c_s."-";}
                  if($c_u != 0){$codigo .= $c_u;}
*/               
                  $sql_validate = "SELECT CodigoCSD, cve_almac FROM c_ubicacion WHERE CodigoCSD = '".$codigo."' AND cve_almac = '".$_POST['zone']."'";
                  //and cve_almac = '".$_POST['zone']."'
                  $validate = getArraySQL($sql_validate);
                  
                  if(!empty($validate) && is_array($validate)) 
                  {
                    array_push($arr_csd_exist, $validate);
                    $msj = "advertencia";
                  }
                  else
                  {
                    $this_sql = $pre_sql;
                    $this_sql = str_replace("_NIVEL_", $c_n,$this_sql);
                    $this_sql = str_replace("_SECCION_", $c_s,$this_sql);
                    $this_sql = str_replace("_UBICACION_", $c_u,$this_sql);
                    $this_sql = str_replace("_CODIGO_", $codigo,$this_sql);
                    $sql .= $this_sql;
                  }
                }
              }
            }
            executeSQL($sql);
        
            $array = ["codigos_existentes"=>$arr_csd_exist, "sql"=>$sql, "msj"=>$msj];
        
            echo json_encode($array);
          break;
        case 'edit':
            if($_POST["ptl"]=="S"){$ptl="PTL";}else{$ptl="";}


            $sql = "UPDATE c_ubicacion SET  cve_almac = '".strip_tags($_POST['zone'])."', num_ancho = '".strip_tags($_POST['ancho'])."', num_largo = '".strip_tags($_POST['fondo'])."', num_alto = '".strip_tags($_POST['alto'])."', PesoMaximo = '".strip_tags($_POST['pesoM'])."', Tipo = '".strip_tags($_POST['tipo'])."', picking = '".strip_tags($_POST['pick'])."', AcomodoMixto = '".strip_tags($_POST['acoMixt'])."', AreaProduccion = '".strip_tags($_POST['arePro'])."', AreaStagging = '".strip_tags($_POST['areStag'])."', Status = '".strip_tags($_POST['Traslado'])."', Ptl = '".$_POST['ptl']."', Reabasto = '".$_POST['c_reabasto']."', TECNOLOGIA = '".$ptl."' WHERE idy_ubica = '".$_POST['id']."'";
            //echo $sql;
            executeSQL($sql);
            echo json_encode(["msj" => "success"]);
            break;
        case 'remove':

                $sql = "UPDATE c_ubicacion SET Activo = '0' WHERE idy_ubica = '".$_POST['id']."'";
                executeSQL($sql);
                $sql = "UPDATE td_ruta_surtido SET Activo = '0' WHERE idy_ubica = '".$_POST['id']."'";
                executeSQL($sql);

                echo json_encode(["msj" =>  "success"]);
            break;
        case 'recuperar':

                $sql = "UPDATE c_ubicacion SET Activo = '1' WHERE idy_ubica = '".$_POST['id']."'";
                executeSQL($sql);

                $sql = "UPDATE td_ruta_surtido SET Activo = '1' WHERE idy_ubica = '".$_POST['id']."'";
                executeSQL($sql);

                echo json_encode(["msj" =>  "success"]);
            break;
        case 'search-ubica':

                $sql = "SELECT a.idy_ubica, a.cve_almac, a.cve_pasillo, a.cve_rack, a.cve_nivel, a.num_ancho, a.num_largo, a.num_alto, IFNULL(a.Status, '') as Status, a.picking, a.Seccion, a.Ubicacion, a.orden_secuencia, a.PesoMaximo, a.PesoOcupado, a.claverp, a.CodigoCSD, a.TECNOLOGIA, a.Maneja_Cajas, a.Maneja_Piezas, a.Reabasto, a.Activo, a.Tipo, a.AcomodoMixto, a.AreaProduccion, IFNULL(a.AreaStagging, 'N') AreaStagging, a.Ptl, a.Maximo, a.Minimo, (b.des_almac)zona, (b.cve_almac)almacen, (c.clave)alma FROM c_ubicacion a, c_almacen b, c_almacenp c WHERE a.cve_almac = b.cve_almac and b.cve_almacenp = c.id and a.idy_ubica = '".$_POST['idy_ubica']."'";
                $res = getArraySQL($sql);

                $array = [
                    "res"=>$res,
                    "sql"=>$sql
                ];

                echo json_encode($array);
            break;
        case 'traerBL':

                $sql = "SELECT * FROM `c_almacenp` WHERE clave = '".$_POST['clave']."'";
                $res = getArraySQL($sql);

                $array = [
                    "res"=>$res,
                    "sql"=>$sql
                ];

                echo json_encode($array);
            break;
        case 'search-all-ubica':

                $sql = "SELECT a.idy_ubica, a.cve_pasillo, a.cve_rack, a.cve_nivel, a.Seccion, a.Ubicacion FROM c_ubicacion a";
                $res = getArraySQL($sql);

                $array = [
                    "res"=>$res,
                    "sql"=>$sql
                ];

                echo json_encode($array);
            break;
        case 'change-all-ubica':

                $sql = "UPDATE c_ubicacion SET CodigoCSD = '".$_POST['code']."' WHERE idy_ubica = '".$_POST['id']."'";
                executeSQL($sql);

                echo json_encode(["msj" =>  "success"]);
            break;
        case 'get-all-compo':

                $sql = "SELECT a.cve_rack FROM c_ubicacion a WHERE a.cve_almac = '".$_POST['zona']."' GROUP BY a.cve_rack ORDER BY a.cve_rack ASC";
                $rack = getArraySQL($sql);

                $sql = "SELECT a.cve_nivel FROM c_ubicacion a WHERE a.cve_almac = '".$_POST['zona']."' GROUP BY a.cve_nivel ORDER BY a.cve_nivel ASC";
                $nivel = getArraySQL($sql);

                $sql = "SELECT a.Seccion FROM c_ubicacion a WHERE a.cve_almac = '".$_POST['zona']."' GROUP BY a.Seccion ORDER BY a.Seccion ASC";
                $seccion = getArraySQL($sql);

                $sql = "SELECT a.Ubicacion FROM c_ubicacion a WHERE a.cve_almac = '".$_POST['zona']."' GROUP BY a.Ubicacion ORDER BY a.Ubicacion ASC";
                $posi = getArraySQL($sql);

                $array = [
                    "rack"=>$rack,
                    "nivel"=>$nivel,
                    "seccion"=>$seccion,
                    "posi"=>$posi
                ];
                echo json_encode($array);
            break;
        case 'show-table-zona':

                $sql_rack = "";
                $sql_nivel = "";
                $sql_seccion = "";
                $sql_posi = "";

                if(isset($_POST['rack'])){
                    $sql_rack = " and a.cve_rack = '".$_POST['rack']."'";
                }
                if(isset($_POST['nivel'])){
                    $sql_nivel = " and a.cve_nivel = '".$_POST['nivel']."'";
                }
                if(isset($_POST['seccion'])){
                    $sql_seccion = " and a.Seccion = '".$_POST['seccion']."'";
                }
                if(isset($_POST['posi'])){
                    $sql_posi = " and a.Ubicacion = '".$_POST['posi']."'";
                }

                $sql = "SELECT codigo FROM t_codigocsd WHERE cve_cia = '".$_POST['cia']."'; ";
                $bl = getArraySQL($sql);

                $sql = "SELECT a.idy_ubica, a.CodigoCSD, a.cve_pasillo, a.cve_rack, a.cve_nivel, a.Seccion, a.Ubicacion  FROM c_ubicacion a WHERE a.cve_almac = '".$_POST['zona']."'".$sql_rack.$sql_nivel.$sql_seccion.$sql_posi;
                $res = getArraySQL($sql);

                $array = [
                    "res"=>$res,
                    "bl"=>$bl
                ];

                echo json_encode($array);
            break;
    }   

}

function getArraySQL($sql)
{
  $conexion = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
  mysqli_set_charset($conexion, "utf8");

  if(!$result = mysqli_query($conexion, $sql)) 
  {
    echo "Falló la preparación: (" . mysqli_error($conexion) . ") ".$sql;
  }

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

    $result = mysqli_multi_query($conexion, $sql);

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