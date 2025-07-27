<?php

include '../../../app/load.php';

// Initalize Slim
$app = new \Slim\Slim();

set_time_limit(0);

$json = file_get_contents('php://input');

$obj = json_decode($json);
$nuevo_pedido = new \NuevosPedidos\NuevosPedidos();

if( !isset( $_SESSION['id_user'] ) AND !$_SESSION['id_user'] ) {
    exit();
}

if (isset($_POST) && !empty($_POST)) {

    session_start();

    switch ($_POST['action']) {
        case "nOrden":
            $sql = "Select `fct_consecutivo_documentos`('t_ordenprod', 6);";
            $rs = mysqli_query(\db2(), $sql);
            $row = mysqli_fetch_array($rs);

            $newFolio = "OT".$row[0];

            $_SESSION["NroOrdenProduccion"] = $newFolio;
/*
            $sql1 = "Call `SPAD_AddUpdateOrdenProd`(
                        '$newFolio',
                        '',
                        '0',
                        '0',
                        '".$_SESSION['id_user']."',
                        now(),
                        'I');";

            $rs = mysqli_query(\db2(), $sql1);
*/
            $arr = array(
                "success" => true,
                "NroParte" => $NroParteDesc,
                "NroOrdenProduccion" => $newFolio,
                "SQL" =>$sql
            );
            //echo var_dump($arr);
            //die();
        
            echo json_encode($arr);
            exit();
            break;
        case "buscarPzas":
            $NroParte = $_POST["NroParte"];
            $sql = "SELECT * FROM c_articulo WHERE c_articulo.cve_articulo='".$NroParte."'";
            $rs = mysqli_query(\db2(), $sql);

            $sql = "SELECT a.cve_articulo, a.unidadMedida, a.num_multiplo, u.cve_umed AS clave_um, u.des_umed AS desc_um, u.mav_cveunimed
                    FROM c_articulo a
                    LEFT JOIN c_unimed u ON u.id_umed = IFNULL(a.unidadMedida, '')
                    WHERE a.cve_articulo = '".$NroParte."' 

                    UNION

                    SELECT a.cve_articulo, a.empq_cveumed AS unidadMedida,a.num_multiplo, u.cve_umed AS clave_um, u.des_umed AS desc_um, u.mav_cveunimed
                    FROM c_articulo a
                    LEFT JOIN c_unimed u ON u.id_umed = IFNULL(a.empq_cveumed, '')
                    WHERE a.cve_articulo = '".$NroParte."'

                    UNION

                    SELECT a.cve_articulo, a.empq_cveumed AS unidadMedida,a.num_multiplo, u.cve_umed AS clave_um, u.des_umed AS desc_um, u.mav_cveunimed
                    FROM c_articulo a
                    LEFT JOIN c_unimed u ON u.mav_cveunimed = 'XBX'
                    WHERE a.cve_articulo = '".$NroParte."' AND a.num_multiplo > 1";
            $rs_combo = mysqli_query(\db2(), $sql);

            $combo = "<option>Seleccione UM</option>";
            while($res_um = mysqli_fetch_assoc($rs_combo))
            {
                extract($res_um);
                if($mav_cveunimed == 'XBX' && $num_multiplo == 1) continue;
                if($clave_um)
                $combo .= "<option data-um='".$mav_cveunimed."' data-nmultiplo='".$num_multiplo."' value='".$unidadMedida."'>"."( ".$clave_um." ) ".$desc_um."</option>";
            }

            $pza = 0;
            $cja = 0;
            $pallet = 0;

            if (mysqli_num_rows($rs)>0) {
                //while ($row = mysqli_fetch_array($rs)) {
                    $row = mysqli_fetch_array($rs);
                    $num_multiplo= $row["num_multiplo"];
                    $cajas_palet = $row["cajas_palet"];
                    $unidadMedida = $row["unidadMedida"];
                    $control_peso = $row["control_peso"];
                //}
            }

            $arr = array(
                "success" => true,
                "num_multiplo" => $num_multiplo,
                "unidadMedida" => $unidadMedida,
                "control_peso" => $control_peso,
                "cajas_palet" => $cajas_palet,
                "comboUM"     => $combo
            );

            echo json_encode($arr);
            exit();
            break;
        case "buscarComponentes":
            $NroParte = $_POST["NroParte"];
            $ZonaAlmacen = $_POST["ZonaAlmacen"];
            $ZonaAlmacenDesc = $_POST["ZonaAlmacenDesc"];
            $Total = $_POST["Total"];
            $folio_rel = $_POST["folio_rel"];
            $unidadMedida = $_POST["unidadMedida"];
            $almacen = $_POST["almacen"];
            $idy_ubica = $_POST["idy_ubica"];

            $arrArts = array();

            $sql = "SELECT * FROM c_lotes where cve_articulo='".$NroParte."'";

            $rs = mysqli_query(\db2(), $sql);

            $lote = "";

            if (mysqli_num_rows($rs)>0) {
                while ($row = mysqli_fetch_array($rs)) {
                    $lote = $row["cve_articulo"];
                }
            }

            $sql = "SELECT
                    t_artcompuesto.Cve_Articulo,
                    t_artcompuesto.Cve_ArtComponente,
                    TRUNCATE(t_artcompuesto.Cantidad, 5) as Cantidad,
                    t_artcompuesto.Status,
                    t_artcompuesto.Activo,
                    t_artcompuesto.cve_umed,
                    CONVERT(CAST(comp.des_articulo as BINARY) USING utf8) as des_articulo,

                    IFNULL(TRUNCATE((SELECT SUM(Existencia) AS suma 
                    FROM V_ExistenciaGral, c_ubicacion  
                    WHERE V_ExistenciaGral.cve_articulo = t_artcompuesto.Cve_Articulo
                    AND V_ExistenciaGral.cve_almac = '{$almacen}' AND V_ExistenciaGral.tipo = 'ubicacion' 
                    AND c_ubicacion.idy_ubica = V_ExistenciaGral.cve_ubicacion
                    AND (IFNULL(V_ExistenciaGral.cve_lote, '') IN (
                    SELECT IFNULL(c_lotes.Lote, '') 
                    FROM c_lotes WHERE c_lotes.cve_articulo = t_artcompuesto.Cve_Articulo 
                    AND c_lotes.Caducidad > CURDATE() 
                    AND V_ExistenciaGral.cve_lote = c_lotes.Lote) OR IFNULL(V_ExistenciaGral.cve_lote, '') = '')),5), 0) AS suma,

                    IFNULL(TRUNCATE((SELECT SUM(Existencia) AS suma 
                    FROM V_ExistenciaProduccion, c_ubicacion  
                    WHERE V_ExistenciaProduccion.cve_articulo = t_artcompuesto.Cve_Articulo
                    AND V_ExistenciaProduccion.cve_almac = '{$almacen}'
                    AND c_ubicacion.idy_ubica = V_ExistenciaProduccion.cve_ubicacion
                    AND V_ExistenciaProduccion.cve_ubicacion = '{$idy_ubica}'
                    AND (IFNULL(V_ExistenciaProduccion.cve_lote, '') IN (
                    SELECT IFNULL(c_lotes.Lote, '') 
                    FROM c_lotes WHERE c_lotes.cve_articulo = t_artcompuesto.Cve_Articulo 
                    AND c_lotes.Caducidad > CURDATE() 
                    AND V_ExistenciaProduccion.cve_lote = c_lotes.Lote) OR IFNULL(V_ExistenciaProduccion.cve_lote, '') = '')),5), 0) AS suma_prod,

                    IF((c_articulo.peso = 0 OR IFNULL(c_articulo.peso, '') = ''), 0,c_articulo.peso) AS peso,
                    IF((comp.peso = 0 OR IFNULL(comp.peso, '') = ''), 0,comp.peso) AS peso_comp, 
                    IFNULL(comp.control_peso, 'N') AS control_peso_comp,
                    IFNULL(c_articulo.control_peso, 'N') AS control_peso,
                    IF(IFNULL(um.mav_cveunimed, 'H87') = 'H87', 1, 0) AS es_pieza,
                    um.des_umed AS UMCat,
                    um.des_umed
                    FROM
                    t_artcompuesto
                    #INNER JOIN c_articulo ON t_artcompuesto.Cve_ArtComponente = c_articulo.cve_articulo
                    #INNER JOIN c_articulo comp ON t_artcompuesto.Cve_Articulo = comp.cve_articulo
                    #INNER JOIN c_unimed ON t_artcompuesto.cve_umed = c_unimed.cve_umed
                    LEFT JOIN c_articulo ON t_artcompuesto.Cve_ArtComponente = c_articulo.cve_articulo
                    LEFT JOIN c_articulo comp ON t_artcompuesto.Cve_Articulo = comp.cve_articulo
                    LEFT JOIN c_unimed ON t_artcompuesto.cve_umed = c_unimed.cve_umed
                    LEFT JOIN c_unimed um ON um.id_umed = comp.unidadMedida
                    WHERE t_artcompuesto.Cve_ArtComponente='".$NroParte."' AND IFNULL(comp.tipo_producto, '') != 'ProductoNoSurtible' ";
                    // - (SELECT SUM(CantidadRecibida - CantidadUbicada) FROM td_entalmacen WHERE cve_articulo = t_artcompuesto.Cve_Articulo)
            
            if(!$rs = mysqli_query(\db2(), $sql))
                echo "NO".$sql;


            $existsComp = false;

            $uid = sha1(mt_rand(1, 90000) . 'SALT');

            if (@mysqli_num_rows($rs)>0) {
                $existsComp = true;

                if($folio_rel) $Total = 1;
                while ($row = mysqli_fetch_array($rs)) {

                    $code = $row["Cve_Articulo"];
                    $desc = $row["des_articulo"];
                    $Cantidad = $row["Cantidad"];
                    $valcbo = $row["cve_umed"];
                    $descbo = $row["des_umed"];
                    $suma = $row["suma"];
                    $suma_prod = $row["suma_prod"];
                    $peso = $row["peso"];
                    $peso_comp = $row["peso_comp"];
                    $_Total = $Total * $peso_comp;
                    //if($row['es_pieza'] == 1) {$_Total = ceil($_Total);}
                    $Total_Pz = 0; $suma_Pz = 0; $suma_prod_pz = 0;
                    if($row["es_pieza"] == 1 && $row["control_peso_comp"] == 'S') 
                    {

                        //$_Total = $Total * $Cantidad;
                        //$_Total = $Total * $peso_comp;
                        //$Total_Pz = ceil($Total*$peso); 
                        if($peso_comp == 0) $peso_comp = 1;
                        if($peso == 0) $peso = 1;
                        //$Total_Pz = ceil($Total/$peso); 
                        //$Total_Pz = ceil($_Total/$peso_comp); 
                        $Total_Pz = ceil($Total/$peso_comp); 
                        $_Total = $Total_Pz * $peso_comp;
                        $suma_Pz = round($suma*$peso_comp, 2);
                        $suma_prod_pz = round($suma_prod*$peso_comp, 2);
                    }
                    else
                        $_Total = $Total * $Cantidad;


                    $htmlZonaAlmacen = $ZonaAlmacenDesc ."<input name=\"hiddenzonaalmacen_{$code}_".$uid."\" id=\"hiddenzonaalmacen_{$code}_".$uid."\" type=\"hidden\" value='".$ZonaAlmacen."'/>";

                    $htmlproducto = $code." - ".$desc ."<input name=\"hiddencode_".$uid."\" id=\"hiddencode_".$uid."\" type=\"hidden\" value='".$code."'/>";
                    $htmlproducto .= "<input name=\"hiddendesc_{$code}_".$uid."\" id=\"hiddendesc_{$code}_".$uid."\" type=\"hidden\" value='".$desc."'/>";

                    $htmlUM = $descbo ."<input name=\"hiddenUnidadMedida_{$code}_".$uid."\" id=\"hiddenUnidadMedida_{$code}_".$uid."\" type=\"hidden\" value='".$valcbo."'/>";
                    $htmlUM .= "<input name=\"hiddenUnidadMedidaDesc_{$code}_".$uid."\" id=\"hiddenUnidadMedidaDesc_{$code}_".$uid."\" type=\"hidden\" value='".$descbo."'/>";

                    $htmlCantidad = $Cantidad ."<input name=\"hiddenCantidad_{$code}_".$uid."\" id=\"hiddenCantidad_{$code}_".$uid."\" type=\"hidden\" value='".$Cantidad."'/>";

                    $htmlCantidadDisponible = $suma."<input name=\"hiddenCantidadDisponible_{$code}_".$uid."\" id=\"hiddenCantidadDisponible_{$code}_".$uid."\" type=\"hidden\" value='$suma'/>";

                    $htmlCantidadUsadaPz = $Total_Pz ."<input type=\"hidden\" id=\"CantidadUsada_{$code}_".$uid."\" value=\"".$Total_Pz."\">";
                    $htmlCantidadUsadaKg = $_Total ."<input type=\"hidden\" id=\"CantidadUsadaKg_{$code}_".$uid."\" value=\"".$_Total."\">";
                    $htmlCantidadDisponible = $suma."<input name=\"hiddenCantidadDisponible_{$code}_".$uid."\" id=\"hiddenCantidadDisponible_{$code}_".$uid."\" type=\"hidden\" value='$suma'/>";
                    $htmlCantidadDisponiblePz = $suma_Pz."<input name=\"hiddenCantidadDisponiblePz_{$code}_".$uid."\" id=\"hiddenCantidadDisponiblePz_{$code}_".$uid."\" type=\"hidden\" value='$suma_Pz'/>";

                    #Area de Producción | Producto | Unidad/Kg por Producto | UM | Cant Requerida Kg | Cant Requerida Pza | Cantidad Disponible Kgs | Cantidad Disponible Pzas

                    $arrArts[] = array(
                        "uid" => $uid,
                        "col1" => "<div id='div_zona_almacen_$uid'>".$htmlZonaAlmacen."</div>",
                        "col2" => "<div id='div_producto_$uid'>".$htmlproducto."</div>",
                        "col3" => "<div id='div_cantidad_requerida_$uid'>".$htmlCantidad."</div>",
                        "col4" => "<div id='div_unidad_medida_$uid'>".$htmlUM."</div>",
                        "col04" => "<div id='div_peso_comp_$uid'>".$peso_comp."</div>",
                        "col5" => "<div id='div_cantidad_disponible_$uid'>".$htmlCantidadUsadaKg."</div>",
                        "col6" => "<div id='div_cantidad_disponiblePz_$uid'>".$htmlCantidadUsadaPz."</div>",
                        "col7" => "<div id='div_cantidad_usada_$uid'>".$htmlCantidadDisponible."</div>",
                        "col8" => "<div id='div_cantidad_usadaPz_$uid'>".$htmlCantidadDisponiblePz."</div>",
                        "col9" => "<div id='div_peso_comp_$uid'>".$suma_prod."</div>",
                        "col10" => "<div id='div_peso_comp_$uid'>".$suma_prod_pz."</div>",
                        "lote" => $lote
                    );
                }
            }

            $arr = array(
                "success" => true,
                "arrArts" => $arrArts,
                "sql" => $sql,
                "existsComp" => $existsComp
            );

            echo json_encode($arr);
            exit();
            break;
        case "addRowComponentes":
            if (!empty($_SESSION['arrComponentes'])) {
                foreach ($_SESSION['arrComponentes'] as $lp) {
                    if ($lp["codigo"]==$_POST["codigo"]) {
                        $arr = array(
                            "success" => false,
                            "err" => "El Código de Producto ya se Ha Introducido"
                        );
                        echo json_encode($arr);
                        exit();
                    }
                }
            }

            $UID = $_POST["UID"];

            $sql = "Select cve_articulo, CONVERT(CAST(c_articulo.des_articulo as BINARY) USING utf8) as des_articulo from c_articulo where cve_articulo='".$_POST[NroParte]."'";

            $rs = mysqli_query(\db2(), $sql);

            if (mysqli_num_rows($rs)>0) {
                $row = mysqli_fetch_array($rs);
                $NroParteDesc = $row["cve_articulo"]." - ".$row["des_articulo"];
            }

            $botonesAcciones = "
            <button id =\"aceptarComp_$UID\" class=\"btn btn-primary\" type=\"button\" onclick=\"aceptarRow('$UID')\"><i class=\"fa fa-check\"></i>&nbsp;&nbsp;Aceptar</button>
            <button id =\"cancelarComp_$UID\" class=\"btn btn-primary\" type=\"button\" onclick=\"cancelarRow('$UID')\"><i class=\"fa fa-check\"></i>&nbsp;&nbsp;Cancelar</button>";

            $arr = array(
                "success" => true,
                "col1" => $botonesAcciones,
                "col2" => "<div id='div_cod_$UID'><a href='#' onclick=\"buscarComponente('$UID')\">Buscar &nbsp;<i class=\"fa fa-search\" alt=\"Buscar\"></i></a>",
                "col3" => "<div id='div_desc_$UID'></div>",
                "col4" => "<div id='div_costo_$UID'></div>", //<input type=\"text\" style=\"width: 100px\" class=\"form-control\" id=\"costo_".$_POST["NroParte"]."\" value=\"0\" onchange=\"onChange(this,'','','')\" onKeyPress=\"return(currencyFormat(this,'.',',',event,'','',''))\">",
                "col5" => "<div id='div_unidad_medida_$UID'></div>"// $comboUnidadesMedida
            );
            echo json_encode($arr);
            exit();
            break;
        case "checkComponentes":
            if (empty($_SESSION['arrComponentes'])) {
                $arr = array(
                    "success" => false,
                    "err" => "Por Favor Introduzca un Producto..."
                );
                echo json_encode($arr);
                exit();
            }

            $arr = array(
                "success" => true
            );
            echo json_encode($arr);
            exit();
            break;
        case "gerAllLP":

            break;
        case "delRowComponentes":
            if (!empty($_SESSION['arrComponentes'])) {

                $i = 0;
                $i = count($_SESSION['arrComponentes']) - 1;
                while ($i > -1) {
                    if($_SESSION['arrComponentes'][$i]["codigo"]==$_POST["codigo"]) {
                        unset($_SESSION['arrComponentes'][$i]);
                    }
                    $i--;
                }

                $_SESSION['arrComponentes'] = array_values($_SESSION['arrComponentes']);
            }
            exit();
            break;
        case "cve_articulo":
            include_once("../admin/class/admin.inc.php");

            $tsCodigoArticulo = $_POST["cve_articulo"];
            $oPanel = new Panel();

            $row = $oPanel->fPreciosArticulos($tsCodigoArticulo);

            //print_r($row);

            $arr = array(
                "success" => true,
                "value1" => number_format($row[0]['precio1'], 2, '.', ''),
                "value2" => number_format($row[0]['precio2'], 2, '.', '')
            );
            echo json_encode($arr);
            exit();
            break;
        case "saveComponentes":
            $NroParte = $_POST["NroParte"];
            $NroParteDesc = $_POST["NroParteDesc"];
            $ZonaAlmacen = $_POST["ZonaAlmacen"];
            $idy_ubica = $_POST["idy_ubica"];
            $idy_ubica_dest = $_POST["idy_ubica_dest"];
            $ArticuloParte = $_POST["ArticuloParte"];
            $fecha_ot = $_POST["fecha_ot"];
            $UnidadesMedida = $_POST["UnidadesMedida"];
            $folio_rel = $_POST["folio_rel"];
            $almacen = $_POST["almacen"];
            $Proveedor = $_POST["Proveedor"];
            $NroLPTarima = $_POST["NroLPTarima"];
            $NroOrdenProduccion = $_POST["NroOrdenProduccion"];
            $num_OT = $_POST["num_OT"];
            $FechaCaducidad = $_POST["FechaCaduca"];
            $newFolio = $_SESSION["NroOrdenProduccion"];
            $status = 'P';

            $FechaCaducidad = date("Y-m-d H:i:s", strtotime($FechaCaducidad));
            $fecha_ot = date("Y-m-d", strtotime($fecha_ot));
            if(!empty($articulosC = $_POST['arrComp'])){
                $disponibles = [];
                foreach($articulosC as $articulo){
                      extract($articulo);
                      //$CantidadUsada = floatval($CantidadUsada);//intval($CantidadUsada);
                      //$CantidadDisponible = floatval($CantidadDisponible);//intval($CantidadDisponible);
                      $disponible[$code] = $CantidadDisponible > $CantidadUsada ? 1 : 0;
                }
                if(array_sum($disponible) > 0){
                    $status = 'P';
                }
            }

            //$sql = "Call `SPAD_AddUpdateOrdenProd`(
	        //        '$newFolio',
	        //        '$ArticuloParte',
	        //        '$NroLPTarima',
	        //        '$_Cant_Prod',
	        //        '".$_SESSION['id_user']."',
	        //        now(),
	        //        '$status');";

            if($folio_rel)
            {
/*
                $sql = "INSERT INTO th_subpedido (fol_folio, cve_almac, Sufijo, Fec_Entrada, status) VALUES ('$folio_rel', '$almacen', '1', NOW(), 'S')";
                $rs = mysqli_query(\db2(), $sql);

                $sql = "INSERT INTO td_subpedido (fol_folio, cve_almac, Sufijo, Cve_articulo, Num_cantidad, Status, Cve_Lote) (SELECT Fol_folio, '$almacen', '1', Cve_articulo, Num_cantidad, 'A', cve_lote FROM td_pedido WHERE Fol_folio = '$folio_rel')";
                $rs = mysqli_query(\db2(), $sql);

                $sql = "
                INSERT INTO t_recorrido_surtido (idy_ubica, cve_almac, cve_pasillo, cve_rack, Seccion, cve_nivel, Ubicacion, orden_secuencia, fol_folio, Sufijo, Cve_articulo, cve_usuario, picking, claverp, ClaveEtiqueta, cve_lote, Cantidad)
                (SELECT c_ubicacion.idy_ubica, 
                        c_ubicacion.cve_almac, 
                        c_ubicacion.cve_pasillo, 
                        c_ubicacion.cve_rack, 
                        c_ubicacion.Seccion, 
                        c_ubicacion.cve_nivel, 
                        c_ubicacion.Ubicacion, 
                        '1', 
                        '$folio_rel', 
                        '1', 
                        td_pedido.Cve_articulo, 
                        '', 
                        'S', 
                        c_ubicacion.CodigoCSD, 
                        '1', 
                        td_pedido.cve_lote, 
                        td_pedido.Num_cantidad 
                    FROM c_ubicacion 
                    RIGHT JOIN td_pedido ON Fol_folio = '$folio_rel'
                    WHERE AreaProduccion = 'S' 
                    LIMIT 1)";
                $rs = mysqli_query(\db2(), $sql);
*/
                $sql = "UPDATE th_pedido SET status = 'A', BanEmpaque = '1', Ship_Num = '$newFolio' WHERE Fol_folio = '$folio_rel'";
                $rs = mysqli_query(\db2(), $sql);


            }

            $sql = "INSERT INTO t_ordenprod( Folio_Pro, cve_almac, ID_Proveedor, Cve_Articulo, Cantidad, Cant_Prod, Cve_Usuario, Fecha, FechaReg, id_umed, Status, id_zona_almac, Referencia, idy_ubica, idy_ubica_dest)
                    VALUES ('".$newFolio."', '".$almacen."', '".$Proveedor."', '".$ArticuloParte."','".$NroLPTarima."', '".$_Cant_Prod."', '".$_SESSION['id_user']."', '".$fecha_ot."', NOW(), '".$UnidadesMedida."', '".$status."', '".$ZonaAlmacen."', '".$num_OT."', '".$idy_ubica."', '".$idy_ubica_dest."');";
            $rs = mysqli_query(\db2(), $sql);

            //$queryZona = mysqli_query(\db2(), "UPDATE t_ordenprod SET cve_almac = '$ZonaAlmacen' WHERE Folio_Pro = '$newFolio'");
            $articulos = array();

            if (!empty($_POST['arrComp'])) {
                foreach ($_POST['arrComp'] as $comp) {

                    $cve_art = $comp["code"];
                    $CantidadUsada = $comp["CantidadUsada"];
                    $sql_art = "SELECT a.control_peso, a.peso, u.mav_cveunimed, a.unidadMedida FROM c_articulo a LEFT JOIN c_unimed u ON u.id_umed = a.unidadMedida WHERE a.cve_articulo = '$cve_art'";
                    $rs_art = mysqli_query(\db2(), $sql_art);
                    $row_art = mysqli_fetch_array($rs_art);
                    $band_granel = $row_art["control_peso"];
                    $peso = $row_art["peso"];
                    $mav_cveunimed = $row_art["mav_cveunimed"];
                    $unidadMedida = $row_art["unidadMedida"];
/*
                    if($band_granel == 'S')
                    {
                        if($peso == 0)
                            $CantidadUsada = 0;
                        else 
                            $CantidadUsada = ceil($CantidadUsada/$peso);
                    }
*/
                    //$sql = "Call SPAD_AddUpdateTDOrdenProd (";
                    //$sql .= "'".$newFolio."','".$comp["code"]."',NOW(),".$CantidadUsada.",'".$_SESSION['id_user']."','1');";

                    if(is_float($CantidadUsada) && $mav_cveunimed == 'H87')
                    {
                        $CantidadUsada = ceil($CantidadUsada);
                    }


                    $sql = "INSERT INTO td_ordenprod(Folio_Pro,Cve_Articulo,Fecha_Prod,Cantidad,Usr_Armo,Activo) 
                            VALUES ('".$newFolio."','".$comp["code"]."',NOW(),".$CantidadUsada.",'".$_SESSION['id_user']."','1')";

                    $rs = mysqli_query(\db2(), $sql);

                    array_push($articulos,array(
                        "Cve_articulo" => $comp["code"],
                        "Num_cantidad" => $CantidadUsada,
                        "id_unimed" => $unidadMedida,
                        "Num_Meses" => ""
                    ));
                }
            }
        /*
            $sql = "select clave from c_almacenp where id = (SELECT cve_almacenp FROM `c_almacen` where cve_almac = ".$ZonaAlmacen.") ;";
            $query = mysqli_query(\db2(), $sql);
            if($query->num_rows > 0){
                $id_almacen = mysqli_fetch_all($query, MYSQLI_ASSOC)[0]["clave"];
            }
        */
            //Crear pedido para manufactura
            
            $data = array(
                'Fol_folio' => $newFolio,
                'Fec_Pedido' => date('Y-m-d H:m:s'),
                'Cve_clte' => '',
                'status' => 'A',
                'TipoPedido' => 'T',
                'Fec_Entrega' => date('Y-m-d H:m:s'),
                'cve_Vendedor' => "",
                'Fec_Entrada' => date('Y-m-d H:m:s'),
                'Pick_Num' => $num_OT,
                'destinatario' => 0,
                'Cve_Usuario' => $_SESSION['id_user'],
                'Observaciones' => "",
                'ID_Tipoprioridad' => 0,
                'cve_almac' => $almacen,
                'arrDetalle' => $articulos
            );
            $nuevo_pedido->save($data);

            $arr = array(
                "success" => true,
                "almace" => $almacen,
                "NroParte" => $NroParteDesc,
                "NroOrdenProduccion" => $newFolio,
                "NumOrdenTrabajo" => $num_OT
            );

            //echo var_dump($arr);
            //die();
        
            echo json_encode($arr);
            exit();
            break;

        case 'loadPopup':
            $sql = "SELECT cve_articulo, CONVERT(CAST(des_articulo as BINARY) USING utf8) as des_articulo FROM c_articulo WHERE (c_articulo.cve_articulo like '%".$_POST["search"]["value"]."%' OR c_articulo.des_articulo like '%".$_POST["search"]["value"]."%') AND c_articulo.Compuesto = 'N' ";

            //echo $sql;

            if ( isset($_POST['start']) && $_POST['length'] != -1 ) {
                $sql .= "LIMIT ".intval($_POST['start']).", ".intval($_POST['length']);
            }

            $rs = mysqli_query(\db2(), $sql) or die("Error description: " . mysqli_error(\db2()));

            $_arr = array();

            while ($row = mysqli_fetch_array($rs)) {
                $_arr[] = $row;
            }

            $arr = array(
                "data" => $_arr
            );

            //print_r($arr);

            echo json_encode($arr);
            exit();
            break;
        case 'pedidos_listos_por_asignar':
            $almacen = $_POST['almacen'];
            $sql = "SELECT 
                        t.Fol_folio, p.Cve_articulo, p.Num_cantidad
                    FROM th_pedido t 
                    LEFT JOIN td_pedido p ON p.Fol_folio = t.Fol_folio
                    WHERE t.status = 'A' AND 
                          t.Activo = 1 AND 
                          IFNULL(t.Ship_Num, '') = '' AND 
                          t.cve_almac = '$almacen' AND 
                          t.Fol_folio NOT IN (SELECT Folio_Pro FROM t_ordenprod) AND 
                          t.Fol_folio NOT IN (SELECT fol_folio FROM th_subpedido) AND 
                          (
                            SELECT COUNT(*) 
                            FROM td_pedido 
                            WHERE Fol_folio = t.Fol_folio AND 
                                  Cve_articulo IN (SELECT cve_articulo FROM c_articulo WHERE Compuesto = 'S')
                          ) = 1";

            $rs = mysqli_query(\db2(), $sql) or die("Error description: " . mysqli_error(\db2()));

            $options = "<option value=''>Seleccione Pedido</option>";
            while ($row = mysqli_fetch_array($rs)) {
                $options .= "<option value='".$row['Fol_folio']."::::".$row['Cve_articulo']."::::".$row['Num_cantidad']."'>".$row['Fol_folio']." -> ".$row['Cve_articulo']."</option>";
            }

            $arr = array(
                "data" => $options
            );

            //print_r($arr);

            echo json_encode($arr);
            exit();
            break;
    }
}
