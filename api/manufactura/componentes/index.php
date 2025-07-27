<?php

include '../../../app/load.php';

// Initalize Slim
$app = new \Slim\Slim();

set_time_limit(0);

$json = file_get_contents('php://input');

$obj = json_decode($json);

if( !isset( $_SESSION['id_user'] ) AND !$_SESSION['id_user'] ) {
    exit();
}

if (isset($_POST) && !empty($_POST)) {

    session_start();

    switch ($_POST['action']) {
        case "buscarComponentes":
            $NroParte = $_POST["NroParte"];

            $arrArts = array();

            $sql = "SELECT
                    t_artcompuesto.Cve_Articulo,
                    t_artcompuesto.Cve_ArtComponente,
                    t_artcompuesto.Cantidad,
                    t_artcompuesto.`Status`,
                    t_artcompuesto.Activo,
                    c_unimed.cve_umed,
                    CONVERT(c_articulo.des_articulo USING utf8) as des_articulo,
                    c_articulo.control_peso,
                    c_unimed.des_umed
                    FROM
                    t_artcompuesto
                    INNER JOIN c_articulo ON t_artcompuesto.Cve_Articulo = c_articulo.cve_articulo
                    LEFT JOIN c_unimed ON c_unimed.id_umed = c_articulo.UnidadMedida
                    where t_artcompuesto.Cve_ArtComponente='".$NroParte."'";

            $rs = mysqli_query(\db2(), $sql);

            $existsComp = false;

            //$uid = sha1(mt_rand(1, 90000) . 'SALT');

            if (mysqli_num_rows($rs)>0) {
                $existsComp = true;

                $uid = 0;
                while ($row = mysqli_fetch_array($rs)) {
                    $uid++;
                    $code = $row["Cve_Articulo"];
                    $desc = $row["des_articulo"];
                    $costo = $row["Cantidad"];
                    $valcbo = $row["cve_umed"];
                    $descbo = $row["des_umed"];
                    $granel = $row["control_peso"];
                    $htmlUM = $valcbo . " <input name=\"hiddenUnidadMedida_".$uid."\" id=\"hiddenUnidadMedida_".$uid."\" type=\"hidden\" value='".$valcbo."'/>";
                    $htmlUM .= "<input name=\"hiddenUnidadMedidaDesc_".$uid."\" id=\"hiddenUnidadMedidaDesc_".$uid."\" type=\"hidden\" value='".$descbo."'/>";

                    $div_cod = $code."<input name=\"hiddencode_".$uid."\" id=\"hiddencode_".$uid."\" type=\"hidden\" value='".$code."'/>";
                    $div_desc = $desc."<input name=\"hiddendesc_".$uid."\" id=\"hiddendesc_".$uid."\" type=\"hidden\" value='".$desc."'/>";
                    $div_costo = $costo."<input name=\"hiddencosto_".$uid."\" id=\"hiddencosto_".$uid."\" type=\"hidden\" value='".$costo."'/>";

                    $html = '<a href="#" class="editando" id="editando_'.$uid.'" onclick="editarRow(\''.$uid.'\')"><i class="fa fa-edit" alt="Editar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
                    $html .= '<a href="#" class="editando" id="editando_'.$uid.'" onclick="borrarRow(\''.$uid.'\')"><i class="fa fa-eraser" alt="Borrar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';

                    $arrArts[] = array(
                        "uid" => $uid,
                        "col1" => $html,
                        "col2" => "<div id='div_cod_$uid'>".$div_cod."</div>",
                        "col3" => "<div id='div_desc_$uid'>".$div_desc."</div>",
                        "col4" => "<div id='div_costo_$uid'>".$div_costo."</div>",
                        "col5" => "<div id='div_unidad_medida_$uid'>".$htmlUM."</div>",
                        "col6" => "<div id='div_granel_$uid'>".$granel."</div>"
                    );
                }
            }

            $arr = array(
                "success" => true,
                "arrArts" => $arrArts,
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

            $sql = "Select cve_articulo, CONVERT(CAST(c_articulo.des_articulo as BINARY) USING utf8) as des_articulo from c_articulo where cve_articulo='".$_POST["NroParte"]."'";

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

            $sql = "Delete FROM t_artcompuesto WHERE Cve_ArtComponente='".$NroParte."'";

            $rs = mysqli_query(\db2(), $sql);

            if (!empty($_POST['arrComp'])) {
                foreach ($_POST['arrComp'] as $comp) {
                    $sql = "insert into t_artcompuesto (Cve_Articulo, Cve_ArtComponente, Cantidad, Activo, cve_umed) Values (";
                    $sql .= "'".$comp["code"]."','".$NroParte."','".$comp["cantidad"]."','1','".$comp["UM"]."');";
                    $rs = mysqli_query(\db2(), $sql);
                }
            }

            $arr = array(
                "success" => true,
                "NroParte" => $NroParte
            );

            echo json_encode($arr);
            exit();
            break;

        case 'loadPopup':
            $value = $_POST["search"]["value"];
            $id_almacen = $_POST['id_almacen'];
            $sql = "SELECT 
                        c_articulo.cve_articulo, 
                        CONVERT(CAST(c_articulo.des_articulo as BINARY) USING utf8) as des_articulo,
                        IFNULL(c_articulo.control_peso, 'N') as control_peso
                    FROM c_articulo 
                    INNER JOIN Rel_Articulo_Almacen ra ON c_articulo.cve_articulo = ra.Cve_Articulo AND ra.Cve_Almac = {$id_almacen}
                    WHERE 
                        (c_articulo.cve_articulo LIKE '%{$value}%' OR 
                        c_articulo.des_articulo LIKE '%{$value}%')  ";
                        #AND (Compuesto = 'N' OR Compuesto IS NULL)

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
    }
}