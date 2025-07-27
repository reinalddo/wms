<?php

namespace Application\Controllers;

use Framework\View\View;
use Framework\Helpers\Utils;
use Framework\Http\Response;
use Framework\Http\Controller;
use jakeroid\tools\SimpleXLSX;
use Application\Models\Usuarios;
use Application\Models\Articulos;
use Application\Models\ArticulosExtencion;
use Application\Models\ArticulosImportados;
use Illuminate\Database\Capsule\Manager as Capsule;


/**
 * @version 1.0.0
 * @category Usuarios
 * @author Brayan Rincon <brayan262@gemail.com>
 */
class ArticulosController extends Controller
{
/*
    const CLAVE               = 0;
    const DESCRIPCION         = 1;
    const UNIDAD_DE_MEDIDA    = 2;
    const TIPO                = 3;
    const PIEZAS_X_CAJA       = 4;
    const OBSERVACIONES       = 5;
    const ALMACEN             = 6;
    const COSTO               = 7;
    const TIPO_DE_CAJA        = 8;
    const CODIGO_BARRAS       = 9;
    const PROVEEDOR           = 10;
    const PESO                = 11;
    const CODIGO_BARRAS_CAJA  = 12;
    const KIT                 = 13;
    const MANEJA_LOTE         = 14;
    const MANEJA_SERIE        = 15;
    const ALTO                = 16;
    const LARGO               = 17;
    const ANCHO               = 18;
    const PRECIO_UNITARIO     = 19;
    const GRUPO               = 20;
*/

    const CLAVE_ART          = 0;
    const DESCRIPCION        = 1;
    const CLAVE_ALMACEN      = 2;
    const COD_BARRAS         = 3;
    const CVE_UNIDAD_MEDIDA  = 4;
    const OBSERVACIONES      = 5;
    const COSTO_UNITARIO     = 6;
    const PRECIO_UNITARIO    = 7;
    const IVA                = 8;
    const PESO               = 9;
    const ALTO               = 10;
    const LARGO              = 11;
    const ANCHO              = 12;
    const CVE_PROVEEDOR      = 13;
    const CANT_EQUIVALENTE   = 14;
    const CVE_UNI_MED_EQUIV  = 15;
    const CVE_CAJA_ORIGEN    = 16;
    const COD_BARRAS_CAJA    = 17;
    const CAJAS_POR_TARIMA   = 18;
    const COD_BARRAS_PALLET  = 19;
    const GRUPO              = 20;
    const CLASE              = 21;
    const TIPO               = 22;
    const BAND_KIT           = 23;
    const BAND_PESO          = 24;
    const BAND_SERIE         = 25;
    const BAND_LOTE          = 26;
    const BAND_CADUCIDAD     = 27;
    const BAND_REFRIGERACION = 28;
    const BAND_RIESGO        = 29;
    const BAND_ACTIVO_FIJO   = 30;
    const BAND_ENVASE        = 31;
    const BAND_USA_ENVASE    = 32;
    const STOCK_MAX          = 33;
    const STOCK_MIN          = 34;
    const CVE_ALT            = 35;

    /*
    private $camposRequeridos = [
        self::CLAVE => 'Clave', 
        self::DESCRIPCION => 'Descripcion', 
        self::ALMACEN => 'Almacen'
    ];
    */
    /**
     * Renderiza la vista general
     *
     * @return void
     */

    //**********************************************************
    //           IMPORTADOR MÁXIMOS Y MÍNIMOS
    //**********************************************************
    const CLAVE_ARTICULOMM = 0;
    const UBICACION        = 1;
    const MINIMO           = 2;
    const MAXIMO           = 3;
    const CAJA_PIEZA       = 4;
    //**********************************************************

    public function index()
    {
       
    }

public function clave_permitida($clave)
{
    $permitidos = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '_', ',', '-', '.', '*', '/');
//, ' '
    $ok = true;
    $clave .= '';
    for($i = 0; $i < strlen($clave); $i++)
    {
        //echo strtoupper($clave[$i]);
        if(!in_array(strtoupper($clave[$i]), $permitidos))
        {
            $ok = false;
            break;
        }
    }
    
    return $ok;
}



public function importarMyM()
{
        $dir_cache = PATH_APP . 'Cache/';
        $file = $dir_cache . basename($_FILES['file']['name']);

        if (! move_uploaded_file($_FILES['file']['tmp_name'], $file)) 
        {
            $this->response(400, [
                'statusText' =>  "Error al recibir el fichero. Verifique que se tenga permisos para escribir en Cache",
            ]);
        }
        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

        if ( $_FILES['file']['type'] != 'application/vnd.ms-excel' AND
                $_FILES['file']['type'] != 'application/msexcel' AND
                $_FILES['file']['type'] != 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' AND
                $_FILES['file']['type'] != 'application/xls' )
        {
            @unlink($file);
            $this->response(400, [
                'statusText' =>  "Error en elformato del fichero",
            ]);
        }

        $xlsx = new SimpleXLSX( $file );
        $linea = 1;
        $extendido = array();
        $debug = "";

        $linea = 1; $importados = 0; $no_permitidos = 0; $claves_no_permitidas = ''; $BL_no_permitidos = 0; $clavesBL_no_permitidas = ''; $track_banderas = "";
        foreach ($xlsx->rows() as $row)
        {
            if($linea == 1)
            {
                $linea++;continue;
            }

            $clave_articulo = $this->pSQL($row[self::CLAVE_ARTICULOMM]);
            $ubicacion_bl   = $this->pSQL($row[self::UBICACION]);
            $minimo         = $this->pSQL($row[self::MINIMO]);
            $maximo         = $this->pSQL($row[self::MAXIMO]);
            $caja_pieza     = $this->pSQL($row[self::CAJA_PIEZA]);

            $sql = "SELECT COUNT(*) as existe FROM c_articulo WHERE cve_articulo = '$clave_articulo'";
            $rs = mysqli_query($conn, $sql);
            $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
            $existe_articulo = $resul['existe'];

            $sql = "SELECT idy_ubica FROM c_ubicacion WHERE CodigoCSD = '$ubicacion_bl'";
            $rs = mysqli_query($conn, $sql);
            $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
            $idy_ubica = $resul['idy_ubica'];
            $existe_ubicacion = mysqli_num_rows($rs);

            if(!$existe_articulo) {$no_permitidos++; $claves_no_permitidas .= $clave_articulo." | ";}
            if(!$existe_ubicacion) {$BL_no_permitidos++; $clavesBL_no_permitidas .= $ubicacion_bl." | ";}
            if(!$existe_articulo || !$existe_ubicacion) continue;

            if(!$minimo) $minimo = 0;
            if(!$maximo) $maximo = 0;
            if(!$caja_pieza || ($caja_pieza != 'P' && $caja_pieza != 'C')) $caja_pieza = 'P';

            $sql = "INSERT INTO ts_ubicxart(cve_articulo, idy_ubica, CapacidadMinima, CapacidadMaxima, caja_pieza) VALUES ('{$clave_articulo}', '{$idy_ubica}', {$minimo}, {$maximo}, '{$caja_pieza}') ON DUPLICATE KEY UPDATE CapacidadMinima = {$minimo}, CapacidadMaxima = {$maximo}, caja_pieza = '{$caja_pieza}'";
            $rs = mysqli_query($conn, $sql);

            $linea++;
            $importados++;
        }


        @unlink($file);
        $mensaje_no_permitidos = "";

        if($no_permitidos) $mensaje_no_permitidos .= "\n\nHay {$no_permitidos} claves de artículos que no existen ";//.$claves_no_permitidas;

        if($BL_no_permitidos) $mensaje_no_permitidos .= "\n\nHay {$BL_no_permitidos} BL que no existen: ".$claves_no_permitidas;


        $this->response(200, [
            'debug' => $debug,
            'statusText' =>  "Máximos y Mínimos importados con éxito. Total registrados: \"{$importados}\" \n\n".$clavesBL_no_permitidas
            //'track_banderas' => $track_banderas
        ]);

}
    public function importar()
    {

        $modificar_importacion = $_POST['modificar_importacion'];
        $dir_cache = PATH_APP . 'Cache/';
        $file = $dir_cache . basename($_FILES['file']['name']);

        if (! move_uploaded_file($_FILES['file']['tmp_name'], $file)) 
        {
            $this->response(400, [
                'statusText' =>  "Error al recibir el fichero. Verifique que se tenga permisos para escribir en Cache",
            ]);
        }
        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

        if ( $_FILES['file']['type'] != 'application/vnd.ms-excel' AND
                $_FILES['file']['type'] != 'application/msexcel' AND
                $_FILES['file']['type'] != 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' AND
                $_FILES['file']['type'] != 'application/xls' )
        {
            @unlink($file);
            $this->response(400, [
                'statusText' =>  "Error en elformato del fichero",
            ]);
        }

        $xlsx = new SimpleXLSX( $file );
        $linea = 1;
        $extendido = array();
        $debug = "";
/*
        foreach ($xlsx->rows() as $row)
        {
            
            if($linea == 1) 
            {
                $linea++;
                if(count($row)>19)
                {
                    for($i = 20;$i < count($row);$i++)
                    {
                      $encabezado = explode(":",$row[$i])[1];
                      $extendido[$i] = $encabezado;
                    }
                }
                continue;
            }

            $eval = $this->validarRequeridosImportar($row);
            if( $eval !== TRUE )
            {
                $this->response(400, [
                    'statusText' =>  "La columna \"{$eval}\" de la Fila Nro. {$linea} está vacía. Corrija esto para continuar con la importación.",
                ]);
            }
            $linea++;
        }
*/
        /*
function clave_permitida($clave)
{
    $permitidos = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '_');

    $ok = true;
    for($i = 0; $i < strlen($clave); $i++)
    {
        echo strtoupper($clave[$i]);
        if(!in_array(strtoupper($clave[$i]), $permitidos))
        {
            $ok = false;
            break;
        }
    }
    
    return $ok;
}
        */
        $linea = 1; $importados = 0; $no_permitidos = 0; $claves_no_permitidas = ''; $track_banderas = "";
        foreach ($xlsx->rows() as $row)
        {
            if($row[self::CLAVE_ART]!="")// && $modificar_importacion == 0
            {
                if($linea == 1)
                {
                    $linea++;continue;
                }

                if(!$this->clave_permitida($row[self::CLAVE_ART]))
                {
                    $no_permitidos++;
                    $claves_no_permitidas .= $row[self::CLAVE_ART]."\n";
                    $linea++;continue;
                }
                $id_almacen = 0;
                $cod_barras        = $this->pSQL($row[self::COD_BARRAS]);
                $cod_barras_caja   = $this->pSQL($row[self::COD_BARRAS_CAJA]);
                $cod_barras_pallet = $this->pSQL($row[self::COD_BARRAS_PALLET]);

                if($cod_barras == '0' || $cod_barras_caja == '0' || $cod_barras_pallet == '0') continue;

                $sql="SELECT COUNT(*) AS existe 
                      FROM c_articulo 
                      WHERE (
                      (cve_codprov = '$cod_barras' AND IFNULL(cve_codprov, '') != '' ) || (barras2 = '$cod_barras' AND IFNULL(barras2, '') != '' ) || (barras3 = '$cod_barras' AND IFNULL(barras3, '') != '' ) ||
                      (cve_codprov = '$cod_barras_caja' AND IFNULL(cve_codprov, '') != '' ) || (barras2 = '$cod_barras_caja' AND IFNULL(barras2, '') != '' ) || (barras3 = '$cod_barras_caja' AND IFNULL(barras3, '') != '' ) ||
                      (cve_codprov = '$cod_barras_pallet' AND IFNULL(cve_codprov, '') != '' ) || (barras2 = '$cod_barras_pallet' AND IFNULL(barras2, '') != '' ) || (barras3 = '$cod_barras_pallet' AND IFNULL(barras3, '') != '' )
                      ) ";
                $rs = mysqli_query($conn, $sql);
                $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                $codigos_barra_existen = $resul['existe'];

                $articulo_existe = "";
                $articulo_existe_en_c_articulo = ""; //esto por si acaso está en c_articulo pero no en Rel_Articulo_Almacen
                $clave_articulo = $this->pSQL($row[self::CLAVE_ART]);
                if($modificar_importacion == 0)
                {
                    $clave_almacen = $this->pSQL($row[self::CLAVE_ALMACEN]);
                    $sql = "SELECT id FROM c_almacenp WHERE clave = '$clave_almacen'";
                    $rs = mysqli_query($conn, $sql);
                    $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                    $almacen = $resul['id'];
                    $id_almacen = $almacen;

                    //$sql = "SELECT COUNT(*) as articulo FROM c_articulo WHERE cve_articulo = '$clave_articulo'";
                    $sql = "SELECT COUNT(*) AS articulo FROM c_articulo a 
                            LEFT JOIN Rel_Articulo_Almacen r ON r.Cve_Articulo = a.cve_articulo 
                            WHERE a.cve_articulo = '$clave_articulo' AND r.Cve_Almac = '$almacen'";
                    $rs = mysqli_query($conn, $sql);
                    $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                    $articulo_existe = $resul['articulo'];

                    $sql = "SELECT COUNT(*) AS articulo FROM c_articulo WHERE cve_articulo = '$clave_articulo'";
                    $rs = mysqli_query($conn, $sql);
                    $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                    $articulo_existe_en_c_articulo = $resul['articulo'];
                }
                else
                {
                    $sql = "SELECT COUNT(*) AS articulo FROM c_articulo WHERE cve_articulo = '$clave_articulo'";
                    $rs = mysqli_query($conn, $sql);
                    $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                    $articulo_existe = $resul['articulo'];

                    $clave_almacen = $this->pSQL($row[self::CLAVE_ALMACEN]);
                    $sql = "SELECT id FROM c_almacenp WHERE clave = '$clave_almacen'";
                    $rs = mysqli_query($conn, $sql);
                    $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                    $id_almacen = $resul['id'];

                    $sql = "SELECT COUNT(*) AS articulo FROM c_articulo WHERE cve_articulo = '$clave_articulo'";
                    $rs = mysqli_query($conn, $sql);
                    $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                    $articulo_existe_en_c_articulo = $resul['articulo'];

                }

                if($articulo_existe)
                {
                    $sql = "SELECT COUNT(*) AS inactivo FROM c_articulo WHERE cve_articulo = '$clave_articulo' AND IFNULL(Activo, 0) = 0";
                    $rs = mysqli_query($conn, $sql);
                    $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                    $articulo_inactivo = $resul['inactivo'];

                    if($articulo_inactivo > 0)
                    {
                        $sql = "UPDATE c_articulo SET Activo = 1 WHERE cve_articulo = '$clave_articulo'";
                        $rs = mysqli_query($conn, $sql);
                    }
                }

                $clave_proveedor = $this->pSQL($row[self::CVE_PROVEEDOR]);
                $sql = "SELECT COUNT(*) as proveedores FROM c_proveedores WHERE cve_proveedor = '$clave_proveedor'";
                $rs = mysqli_query($conn, $sql);
                $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                $proveedor_existe = $resul['proveedores'];

                if(!$proveedor_existe) $clave_proveedor ="";
                else
                {
                    $sql = "SELECT ID_Proveedor FROM c_proveedores WHERE cve_proveedor = '$clave_proveedor'";
                    $rs = mysqli_query($conn, $sql);
                    $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                    $clave_proveedor = $resul['ID_Proveedor'];
                }

                $clave_tipocaja = $this->pSQL($row[self::CVE_CAJA_ORIGEN]);
                $sql = "SELECT id_tipocaja FROM c_tipocaja WHERE clave = '$clave_tipocaja'";
                $rs = mysqli_query($conn, $sql);
                $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                $tipo_caja = $resul['id_tipocaja'];

                $cve_umed = $this->pSQL($row[self::CVE_UNIDAD_MEDIDA]);
                $sql = "SELECT id_umed FROM c_unimed WHERE cve_umed = '$cve_umed'";
                $rs = mysqli_query($conn, $sql);
                $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                $unidadMedida = $resul['id_umed'];

                $cve_umed_empaque = $this->pSQL($row[self::CVE_UNI_MED_EQUIV]);
                $sql = "SELECT id_umed FROM c_unimed WHERE cve_umed = '$cve_umed_empaque'";
                $rs = mysqli_query($conn, $sql);
                $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                $unidadMedidaEmpaque = $resul['id_umed'];
//                $element = Articulos::where('cve_articulo', '=', $clave_articulo)->first();

//                if($element != NULL)
//                {
//                    $model = $element; 
//                }
//                else 
//                {
//                }

                $tipo_producto = $this->pSQL($row[self::BAND_ACTIVO_FIJO]);

                $grupo         = $this->pSQL($row[self::GRUPO]);
                $clasificacion = $this->pSQL($row[self::CLASE]);
                $tipo          = $this->pSQL($row[self::TIPO]);
                $stockmax      = $this->pSQL($row[self::STOCK_MAX]);
                $stockmin      = $this->pSQL($row[self::STOCK_MIN]);
                $cve_alt       = $this->pSQL($row[self::CVE_ALT]);

                if($stockmax == "") $stockmax = 0;
                if($stockmin == "") $stockmin = 0;

                if($this->pSQL($row[self::BAND_ACTIVO_FIJO]) == 'NS') $tipo_producto = 'ProductoNoSurtible';

                    //echo "articulo_existe = ".$articulo_existe."----";
                    if($articulo_existe == 0 && $articulo_existe_en_c_articulo > 0 && $modificar_importacion == 0)
                    {
                        $sql = "INSERT IGNORE INTO Rel_Articulo_Almacen(Cve_Articulo, Cve_Almac, Grupo_ID, Clasificacion_ID, Tipo_Art_ID, StockMax, StockMin) VALUES ('$clave_articulo', $almacen, (SELECT id FROM c_gpoarticulo WHERE cve_gpoart = '{$grupo}' AND id_almacen = '{$almacen}'), (SELECT id FROM c_sgpoarticulo WHERE cve_sgpoart = '{$clasificacion}' AND id_almacen = '{$almacen}'), (SELECT id FROM c_ssgpoarticulo WHERE cve_ssgpoart = '{$tipo}' AND id_almacen = '{$almacen}'), $stockmax, $stockmin)";
                        $rs = mysqli_query($conn, $sql);
                        $importados++;
                    }

                if((((!$articulo_existe && !$codigos_barra_existen) || ($modificar_importacion == 1)) && $unidadMedida != '') || ($modificar_importacion == 1))
                {
/*
                    if($proveedor_existe && $modificar_importacion == 0)
                    {
                        $sql = "INSERT IGNORE INTO rel_articulo_proveedor(Cve_Articulo, Id_Proveedor) VALUES ('$clave_articulo', $clave_proveedor)";
                        $rs = mysqli_query($conn, $sql);
                    }
*/

                    if($modificar_importacion == 0)
                    {
                        $sql = "INSERT IGNORE INTO Rel_Articulo_Almacen(Cve_Articulo, Cve_Almac, Grupo_ID, Clasificacion_ID, Tipo_Art_ID, StockMax, StockMin) VALUES ('$clave_articulo', $almacen, (SELECT id FROM c_gpoarticulo WHERE cve_gpoart = '{$grupo}' AND id_almacen = '{$almacen}'), (SELECT id FROM c_sgpoarticulo WHERE cve_sgpoart = '{$clasificacion}' AND id_almacen = '{$almacen}'), (SELECT id FROM c_ssgpoarticulo WHERE cve_ssgpoart = '{$tipo}' AND id_almacen = '{$almacen}'), $stockmax, $stockmin)";
                        $rs = mysqli_query($conn, $sql);
                    }

                    $num_multiplo           = $this->pSQL($row[self::CANT_EQUIVALENTE]);
                    if($num_multiplo == '' || $num_multiplo == 0)
                    {
                        $num_multiplo = 1;
                    }
                if($articulo_existe == 0 && $modificar_importacion == 0 && $articulo_existe_en_c_articulo == 0)
                {
                    $Compuesto              = $this->pSQL($row[self::BAND_KIT]);
                    //$track_banderas .= "linea = ".$linea." - ".$Compuesto.", \n";
                    //if($Compuesto == 0 || $Compuesto == ''){ $Compuesto = 'N'; } else { $Compuesto = 'S';}

                    $control_peso           = $this->pSQL($row[self::BAND_PESO]);
                    //if($control_peso == 0 || $control_peso == ''){ $control_peso = 'N'; } else { $control_peso = 'S';}

                    $control_lotes          = $this->pSQL($row[self::BAND_LOTE]);
                    //if($control_lotes == 0 || $control_lotes == ''){ $control_lotes = 'N'; } else { $control_lotes = 'S';}

                    $control_numero_series  = $this->pSQL($row[self::BAND_SERIE]);
                    //if($control_numero_series == 0 || $control_numero_series == ''){ $control_numero_series = 'N'; } else { $control_numero_series = 'S';}

                    $mat_peligroso          = $this->pSQL($row[self::BAND_RIESGO]);
                    //if($mat_peligroso == 0 || $mat_peligroso == ''){ $mat_peligroso = 'N'; } else { $mat_peligroso = 'S';}

                    $Caduca                 = $this->pSQL($row[self::BAND_CADUCIDAD]);
                    //if($Caduca == 0 || $Caduca == ''){ $Caduca = 'N'; } else { $Caduca = 'S';}

                    $req_refrigeracion      = $this->pSQL($row[self::BAND_REFRIGERACION]);
                    //if($req_refrigeracion == 0 || $req_refrigeracion == ''){ $req_refrigeracion = 'N'; } else { $req_refrigeracion = 'S';}

                    $Ban_Envase             = $this->pSQL($row[self::BAND_ENVASE]);
                    //if($Ban_Envase == 0 || $Ban_Envase == ''){ $Ban_Envase = 'N'; } else { $Ban_Envase = 'S';}

                    $Usa_Envase             = $this->pSQL($row[self::BAND_USA_ENVASE]);
                    //if($Usa_Envase == 0 || $Usa_Envase == ''){ $Usa_Envase = 'N'; } else { $Usa_Envase = 'S';}
                }    

                    if($articulo_existe == 0 && $modificar_importacion == 0 && $articulo_existe_en_c_articulo == 0)
                    {
                        $model = new Articulos(); 
                        $model->cve_articulo           = $clave_articulo;
                        $model->des_articulo           = $this->pSQL($row[self::DESCRIPCION]);
                        $model->unidadMedida           = $unidadMedida;
                        $model->tipo                   = $this->pSQL($row[self::TIPO]);
                        $model->num_multiplo           = $num_multiplo;
                        $model->empq_cveumed           = $unidadMedidaEmpaque;
                        $model->des_observ             = $this->pSQL($row[self::OBSERVACIONES]);
                        $model->cve_almac              = $almacen;
                        $model->PrecioVenta            = $this->pSQL($row[self::PRECIO_UNITARIO]);
                        $model->tipo_caja              = $tipo_caja;
                        $model->cve_codprov            = $this->pSQL($row[self::COD_BARRAS]);
                        $model->ID_Proveedor           = $clave_proveedor;
                        $model->peso                   = $this->pSQL($row[self::PESO]);
                        $model->barras2                = $this->pSQL($row[self::COD_BARRAS_CAJA]);
                        $model->barras3                = $this->pSQL($row[self::COD_BARRAS_PALLET]);
                        $model->Compuesto              = $Compuesto;
                        $model->control_peso           = $control_peso;
                        $model->control_lotes          = $control_lotes;
                        $model->control_numero_series  = $control_numero_series;
                        $model->alto                   = $this->pSQL($row[self::ALTO]);
                        $model->fondo                  = $this->pSQL($row[self::LARGO]);
                        $model->ancho                  = $this->pSQL($row[self::ANCHO]);
                        $model->costo                  = $this->pSQL($row[self::COSTO_UNITARIO]);
                        $model->grupo                  = $this->pSQL($row[self::GRUPO]);
                        $model->clasificacion          = $this->pSQL($row[self::CLASE]);
                        $model->mav_pctiva             = $this->pSQL($row[self::IVA]);
                        $model->cajas_palet            = $this->pSQL($row[self::CAJAS_POR_TARIMA]);
                        $model->mat_peligroso          = $mat_peligroso;
                        $model->Caduca                 = $Caduca;
                        $model->req_refrigeracion      = $req_refrigeracion;
                        $model->tipo_producto          = $tipo_producto;
                        $model->Ban_Envase             = $Ban_Envase;
                        $model->Usa_Envase             = $Usa_Envase;
                        $model->cve_alt                = $cve_alt;

                        $model->save();

                        if($proveedor_existe)
                        {
                            $sql = "INSERT IGNORE INTO rel_articulo_proveedor(Cve_Articulo, Id_Proveedor) VALUES ('$clave_articulo', $clave_proveedor)";
                            $rs = mysqli_query($conn, $sql);
                        }
                    }
                    else if($articulo_existe > 0 && $modificar_importacion == 1 && $articulo_existe_en_c_articulo > 0)
                    {
                        $des_articulo           = $this->pSQL($row[self::DESCRIPCION]);
                        $tipo                   = $this->pSQL($row[self::TIPO]);
                        $des_observ             = $this->pSQL($row[self::OBSERVACIONES]);
                        $PrecioVenta            = $this->pSQL($row[self::PRECIO_UNITARIO]);
                        $cve_codprov            = $this->pSQL($row[self::COD_BARRAS]);
                        $peso                   = $this->pSQL($row[self::PESO]);
                        $barras3                = $this->pSQL($row[self::COD_BARRAS_PALLET]);
                        $alto                   = $this->pSQL($row[self::ALTO]);
                        $fondo                  = $this->pSQL($row[self::LARGO]);
                        $ancho                  = $this->pSQL($row[self::ANCHO]);
                        $costo                  = $this->pSQL($row[self::COSTO_UNITARIO]);
                        $grupo                  = $this->pSQL($row[self::GRUPO]);
                        $clasificacion          = $this->pSQL($row[self::CLASE]);
                        $mav_pctiva             = $this->pSQL($row[self::IVA]);
                        $cajas_palet            = $this->pSQL($row[self::CAJAS_POR_TARIMA]);
                        $stockmax               = $this->pSQL($row[self::STOCK_MAX]);
                        $stockmin               = $this->pSQL($row[self::STOCK_MIN]);
                        $cve_alt                = $this->pSQL($row[self::CVE_ALT]);
                        $tipo_producto          = $this->pSQL($row[self::BAND_ACTIVO_FIJO]);

                        
                        $Compuesto           = $this->pSQL($row[self::BAND_KIT]);
                        if($Compuesto != '')
                        {
                            $sql = "UPDATE c_articulo SET Compuesto = '$Compuesto' WHERE cve_articulo = '$clave_articulo'";
                            $rs = mysqli_query($conn, $sql);
                        }
                        
                        $control_peso           = $this->pSQL($row[self::BAND_PESO]);
                        if($control_peso != '')
                        {
                            $sql = "UPDATE c_articulo SET control_peso = '$control_peso' WHERE cve_articulo = '$clave_articulo'";
                            $rs = mysqli_query($conn, $sql);
                        }
                        
                        $control_lotes           = $this->pSQL($row[self::BAND_LOTE]);
                        if($control_lotes != '')
                        {
                            $sql = "UPDATE c_articulo SET control_lotes = '$control_lotes' WHERE cve_articulo = '$clave_articulo'";
                            $rs = mysqli_query($conn, $sql);
                        }
                        
                        $control_numero_series           = $this->pSQL($row[self::BAND_SERIE]);
                        if($control_numero_series != '')
                        {
                            $sql = "UPDATE c_articulo SET control_numero_series = '$control_numero_series' WHERE cve_articulo = '$clave_articulo'";
                            $rs = mysqli_query($conn, $sql);
                        }
                        
                        $mat_peligroso           = $this->pSQL($row[self::BAND_RIESGO]);
                        if($mat_peligroso != '')
                        {
                            $sql = "UPDATE c_articulo SET mat_peligroso = '$mat_peligroso' WHERE cve_articulo = '$clave_articulo'";
                            $rs = mysqli_query($conn, $sql);
                        }
                        
                        $Caduca           = $this->pSQL($row[self::BAND_CADUCIDAD]);
                        if($Caduca != '')
                        {
                            $sql = "UPDATE c_articulo SET Caduca = '$Caduca' WHERE cve_articulo = '$clave_articulo'";
                            $rs = mysqli_query($conn, $sql);
                        }
                        
                        $req_refrigeracion           = $this->pSQL($row[self::BAND_REFRIGERACION]);
                        if($req_refrigeracion != '')
                        {
                            $sql = "UPDATE c_articulo SET req_refrigeracion = '$req_refrigeracion' WHERE cve_articulo = '$clave_articulo'";
                            $rs = mysqli_query($conn, $sql);
                        }
                        
                        $Ban_Envase           = $this->pSQL($row[self::BAND_ENVASE]);
                        if($Ban_Envase != '')
                        {
                            $sql = "UPDATE c_articulo SET Ban_Envase = '$Ban_Envase' WHERE cve_articulo = '$clave_articulo'";
                            $rs = mysqli_query($conn, $sql);
                        }
                        
                        $Usa_Envase           = $this->pSQL($row[self::BAND_USA_ENVASE]);
                        if($Usa_Envase != '')
                        {
                            $sql = "UPDATE c_articulo SET Usa_Envase = '$Usa_Envase' WHERE cve_articulo = '$clave_articulo'";
                            $rs = mysqli_query($conn, $sql);
                        }


                        if($des_articulo != '')
                        {
                            $des_articulo           = $this->pSQL($row[self::DESCRIPCION]);
                            $sql = "UPDATE c_articulo SET des_articulo = '$des_articulo' WHERE cve_articulo = '$clave_articulo'";
                            $rs = mysqli_query($conn, $sql);
                        }
                        if($unidadMedida != '')
                        {
                            $unidadMedida           = $unidadMedida;
                            $sql = "UPDATE c_articulo SET unidadMedida = '$unidadMedida' WHERE cve_articulo = '$clave_articulo'";
                            $rs = mysqli_query($conn, $sql);
                        }
                        if($tipo != '')
                        {
                            $tipo                   = $this->pSQL($row[self::TIPO]);
                            $sql = "UPDATE c_articulo SET tipo = '$tipo' WHERE cve_articulo = '$clave_articulo'";
                            $rs = mysqli_query($conn, $sql);
                            $sql = "UPDATE Rel_Articulo_Almacen SET Tipo_Art_ID = (SELECT id FROM c_ssgpoarticulo WHERE cve_ssgpoart = '$tipo' AND id_almacen = $id_almacen) WHERE cve_articulo = '$clave_articulo' AND Cve_Almac = $id_almacen";
                            $rs = mysqli_query($conn, $sql);
                        }
                        if($num_multiplo != '')
                        {
                            $num_multiplo           = $num_multiplo;
                            $sql = "UPDATE c_articulo SET num_multiplo = '$num_multiplo' WHERE cve_articulo = '$clave_articulo'";
                            $rs = mysqli_query($conn, $sql);
                        }
                        if($empq_cveumed != '')
                        {
                            $empq_cveumed           = $unidadMedidaEmpaque;
                            $sql = "UPDATE c_articulo SET empq_cveumed = '$empq_cveumed' WHERE cve_articulo = '$clave_articulo'";
                            $rs = mysqli_query($conn, $sql);
                        }
                        if($des_observ != '')
                        {
                            $des_observ             = $this->pSQL($row[self::OBSERVACIONES]);
                            $sql = "UPDATE c_articulo SET des_observ = '$des_observ' WHERE cve_articulo = '$clave_articulo'";
                            $rs = mysqli_query($conn, $sql);
                        }
                        if($cve_almac != '')
                        {
                            $cve_almac              = $almacen;
                            $sql = "UPDATE c_articulo SET cve_almac = '$cve_almac' WHERE cve_articulo = '$clave_articulo'";
                            $rs = mysqli_query($conn, $sql);
                        }
                        if($PrecioVenta != '')
                        {
                            $PrecioVenta            = $this->pSQL($row[self::PRECIO_UNITARIO]);
                            $sql = "UPDATE c_articulo SET PrecioVenta = '$PrecioVenta' WHERE cve_articulo = '$clave_articulo'";
                            $rs = mysqli_query($conn, $sql);
                        }
                        if($tipo_caja != '')
                        {
                            $tipo_caja              = $tipo_caja;
                            $sql = "UPDATE c_articulo SET tipo_caja = '$tipo_caja' WHERE cve_articulo = '$clave_articulo'";
                            $rs = mysqli_query($conn, $sql);
                        }
                        if($cve_codprov != '')
                        {
                            $cve_codprov            = $this->pSQL($row[self::COD_BARRAS]);
                            $sql = "UPDATE c_articulo SET cve_codprov = '$cve_codprov' WHERE cve_articulo = '$clave_articulo'";
                            $rs = mysqli_query($conn, $sql);
                        }
                        if($peso != '')
                        {
                            $peso                   = $this->pSQL($row[self::PESO]);
                            $sql = "UPDATE c_articulo SET peso = '$peso' WHERE cve_articulo = '$clave_articulo'";
                            $rs = mysqli_query($conn, $sql);
                        }
                        if($barras2 != '')
                        {
                            $barras2                = $this->pSQL($row[self::COD_BARRAS_CAJA]);
                            $sql = "UPDATE c_articulo SET barras2 = '$barras2' WHERE cve_articulo = '$clave_articulo'";
                            $rs = mysqli_query($conn, $sql);
                        }
                        if($barras3 != '')
                        {
                            $barras3                = $this->pSQL($row[self::COD_BARRAS_PALLET]);
                            $sql = "UPDATE c_articulo SET barras3 = '$barras3' WHERE cve_articulo = '$clave_articulo'";
                            $rs = mysqli_query($conn, $sql);
                        }
                        if($alto != '')
                        {
                            $alto                   = $this->pSQL($row[self::ALTO]);
                            $sql = "UPDATE c_articulo SET alto = '$alto' WHERE cve_articulo = '$clave_articulo'";
                            $rs = mysqli_query($conn, $sql);
                        }
                        if($fondo != '')
                        {
                            $fondo                  = $this->pSQL($row[self::LARGO]);
                            $sql = "UPDATE c_articulo SET fondo = '$fondo' WHERE cve_articulo = '$clave_articulo'";
                            $rs = mysqli_query($conn, $sql);
                        }
                        if($ancho != '')
                        {
                            $ancho                  = $this->pSQL($row[self::ANCHO]);
                            $sql = "UPDATE c_articulo SET ancho = '$ancho' WHERE cve_articulo = '$clave_articulo'";
                            $rs = mysqli_query($conn, $sql);
                        }
                        if($costo != '')
                        {
                            $costo                  = $this->pSQL($row[self::COSTO_UNITARIO]);
                            $sql = "UPDATE c_articulo SET costo = '$costo' WHERE cve_articulo = '$clave_articulo'";
                            $rs = mysqli_query($conn, $sql);
                        }
                        if($grupo != '')
                        {
                            $grupo                  = $this->pSQL($row[self::GRUPO]);
                            $sql = "UPDATE c_articulo SET grupo = '$grupo' WHERE cve_articulo = '$clave_articulo'";
                            $rs = mysqli_query($conn, $sql);
                            $sql = "UPDATE Rel_Articulo_Almacen SET Grupo_ID = (SELECT id FROM c_gpoarticulo WHERE cve_gpoart = '$grupo' AND id_almacen = $id_almacen) WHERE cve_articulo = '$clave_articulo' AND Cve_Almac = $id_almacen";
                            $rs = mysqli_query($conn, $sql);

                        }
                        if($clasificacion != '' || $this->pSQL($row[self::CLASE]) != '')
                        {
                            $clasificacion          = $this->pSQL($row[self::CLASE]);
                            $sql = "UPDATE c_articulo SET clasificacion = '$clasificacion' WHERE cve_articulo = '$clave_articulo'";
                            $rs = mysqli_query($conn, $sql);
                            $sql = "UPDATE Rel_Articulo_Almacen SET Clasificacion_ID = (SELECT id FROM c_sgpoarticulo WHERE cve_sgpoart = '$clasificacion' AND id_almacen = $id_almacen) WHERE cve_articulo = '$clave_articulo' AND Cve_Almac = $id_almacen";
                            $rs = mysqli_query($conn, $sql);
                        }
                        if($mav_pctiva != '')
                        {
                            $mav_pctiva             = $this->pSQL($row[self::IVA]);
                            $sql = "UPDATE c_articulo SET mav_pctiva = '$mav_pctiva' WHERE cve_articulo = '$clave_articulo'";
                            $rs = mysqli_query($conn, $sql);
                        }
                        if($cajas_palet != '')
                        {
                            $cajas_palet            = $this->pSQL($row[self::CAJAS_POR_TARIMA]);
                            $sql = "UPDATE c_articulo SET cajas_palet = '$cajas_palet' WHERE cve_articulo = '$clave_articulo'";
                            $rs = mysqli_query($conn, $sql);
                        }
                        if($tipo_producto != '')
                        {
                            $tipo_producto          = $this->pSQL($row[self::BAND_ACTIVO_FIJO]);
                            $sql = "UPDATE c_articulo SET tipo_producto = '$tipo_producto' WHERE cve_articulo = '$clave_articulo'";
                            $rs = mysqli_query($conn, $sql);
                        }
                        if($stockmax != '')
                        {
                            //$tipo_producto          = $this->pSQL($row[self::BAND_ACTIVO_FIJO]);
                            $sql = "UPDATE Rel_Articulo_Almacen SET StockMax = '$stockmax' WHERE Cve_Articulo = '$clave_articulo' AND Cve_Almac = '$id_almacen'";
                            $rs = mysqli_query($conn, $sql);
                        }
                        if($stockmin != '')
                        {
                            //$tipo_producto          = $this->pSQL($row[self::BAND_ACTIVO_FIJO]);
                            $sql = "UPDATE Rel_Articulo_Almacen SET StockMin = '$stockmin' WHERE Cve_Articulo = '$clave_articulo' AND Cve_Almac = '$id_almacen'";
                            $rs = mysqli_query($conn, $sql);
                        }

                        if($cve_alt != '')
                        {
                            $sql = "UPDATE c_articulo SET cve_alt = '$cve_alt' WHERE cve_articulo = '$clave_articulo'";
                            $rs = mysqli_query($conn, $sql);
                        }

                        if($clave_proveedor != '')
                        {
                            $sql = "UPDATE c_articulo SET ID_Proveedor = '$clave_proveedor' WHERE cve_articulo = '$clave_articulo'";
                            $rs = mysqli_query($conn, $sql);

                            $sql = "INSERT IGNORE INTO rel_articulo_proveedor(Cve_Articulo, Id_Proveedor) VALUES ('$clave_articulo', $clave_proveedor)";
                            $rs = mysqli_query($conn, $sql);

                        }

                    }
                    $importados++;
                }

                /*
                if(count($extendido) > 0)
                {
                    $debug = $id = Articulos::where('cve_articulo', '=', $clave)->first()["id"];
                    $element2 = ArticulosExtencion::where('id_articulo', '=', $id)->first();

                    if($element2 != NULL)
                    {
                        $model_ext = $element2; 
                    }
                    else 
                    {
                        $model_ext = new ArticulosExtencion(); 
                    }
                  
                    $model_ext->id_articulo = $id;
                    foreach($extendido as $key => $val)
                    {
                        $model_ext[$val] = $this->pSQL($row[$key]);
                    }
                    $model_ext->save();
                }
                */
            }
/*
            else if($row[self::CLAVE_ART]!="" && $modificar_importacion == 1)
            {

                    $row[self::DESCRIPCION];
                    $row[self::CLAVE_ALMACEN];
                    $row[self::COD_BARRAS];
                    $row[self::CVE_UNIDAD_MEDIDA];
                    $row[self::OBSERVACIONES];
                    $row[self::COSTO_UNITARIO];
                    $row[self::PRECIO_UNITARIO];
                    $row[self::IVA];
                    $row[self::PESO];
                    $row[self::ALTO];
                    $row[self::LARGO];
                    $row[self::ANCHO];
                    $row[self::CVE_PROVEEDOR];
                    $row[self::CANT_EQUIVALENTE];
                    $row[self::CVE_UNI_MED_EQUIV];
                    $row[self::CVE_CAJA_ORIGEN];
                    $row[self::COD_BARRAS_CAJA];
                    $row[self::CAJAS_POR_TARIMA];
                    $row[self::COD_BARRAS_PALLET];
                    $row[self::GRUPO];
                    $row[self::CLASE];
                    $row[self::TIPO];

            }
*/
            $linea++;
        }
        @unlink($file);
        $mensaje_no_permitidos = "";

        if($no_permitidos) $mensaje_no_permitidos = "\n\nHay {$no_permitidos} claves de artículos con caracteres no permitidos que no se cargaron en el sistema, La clave debe contener solo los caracteres A-Z, a-z, 0-9, _ , sin espacios\n";//.$claves_no_permitidas;
        if($codigos_barra_existen && $modificar_importacion == 0) $mensaje_no_permitidos .= "\n\n Hay Códigos de Barras Repetidos en el archivo o que ya existen en el sistema";
        $importados_msj = "importados"; if($modificar_importacion == 1) $importados_msj = "modificados";
        $this->response(200, [
            'debug' => $debug,
            'ext' => $extendido,
            'statusText' =>  "Artículos $importados_msj con exito. Total de artículos: \"{$importados}\"".$mensaje_no_permitidos,
            'track_banderas' => $track_banderas
        ]);
    }

    public function validarRequeridosImportar($row)
    {
        foreach ($this->camposRequeridos as $key => $campo){
            if( empty($row[$key]) )
            {
                return $campo;
            }
        }
        return true;
    }
  
    public function exportar()
    {

    $ands ="";

    $_criterio = $_GET['criterio'];
    if (!empty($_criterio)){
        $ands.=" AND ((a.cve_articulo LIKE '%{$_criterio}%' OR a.cve_alt LIKE '%{$_criterio}%' OR a.des_articulo LIKE '%{$_criterio}%') OR CONCAT_WS(' ', a.cve_articulo, a.des_articulo, a.cve_codprov) like '%{$_criterio}%')";
    }

    $_grupo = $_GET['grupo'];
    $_clasificacion = $_GET['clasificacion'];
    $_tipo = $_GET['tipo'];
    $compuesto = $_GET['compuesto'];
    $almacen = $_GET['almacen'];
    $instancia = $_GET['instancia'];
    $proveedor = $_GET['proveedor'];

    if(isset($_GET['id_proveedor']))
    {
        if($_GET['id_proveedor'] != "")
        {
            $proveedor = $_GET['id_proveedor'];
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

        $columnas = [
            'Clave',
            'Clave Alterna',
            'Descripcion',
            'Proveedor',
            'Codigo de Barras',
            'Unidad de Medida',
            'Alto',
            'Fondo',
            'Ancho',
            'Peso',
            'Pzas por Caja',
            'Clasificacion',
            'Costo',
            'Grupo',
            'Clasif. ABC',
            'Control_lotes',
            'Caduca',
            'Control_numero_series',
            'Control_peso',
            'Control_volumen',
            'Req_refrigeracion',
            'Mat_peligroso',
            'Compuesto',
            'Tipo_caja',
        ];

        $id_almacen = $_SESSION['id_almacen'];
        //$data_rutas = Articulos::get();
/*
        $sql = "SELECT a.* , p.Nombre AS proveedor
                FROM c_articulo a
                LEFT JOIN Rel_Articulo_Almacen r ON r.Cve_Articulo = a.cve_articulo
                LEFT JOIN rel_articulo_proveedor ap ON ap.Cve_Articulo = r.Cve_Articulo
                LEFT JOIN c_proveedores p ON p.ID_Proveedor = ap.Id_Proveedor
                WHERE r.Cve_Almac = $id_almacen";
*/
        $sql = "SELECT a.* , IFNULL(GROUP_CONCAT(DISTINCT c_proveedores.Nombre SEPARATOR ','), '') AS proveedor, c_unimed.cve_umed as UM
                FROM c_articulo a
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

        if($instancia == 'foam')
            $sql = "SELECT a.* , IFNULL(GROUP_CONCAT(DISTINCT c_proveedores.Nombre SEPARATOR ','), '') AS proveedor, c_unimed.cve_umed as UM
                    FROM c_articulo a
                    LEFT JOIN c_unimed ON a.unidadMedida = c_unimed.id_umed
                    LEFT JOIN rel_articulo_proveedor ON CONVERT(rel_articulo_proveedor.Cve_Articulo, CHAR) = CONVERT(a.cve_articulo, CHAR) 
                    LEFT JOIN c_proveedores ON c_proveedores.ID_Proveedor = rel_articulo_proveedor.ID_Proveedor 
                    LEFT JOIN c_articulo_imagen ai ON CONVERT(ai.cve_articulo, CHAR) = CONVERT(a.cve_articulo, CHAR)
                    LEFT JOIN c_articulo_documento ad ON CONVERT(ad.cve_articulo, CHAR) = CONVERT(a.cve_articulo, CHAR)
                    LEFT JOIN c_tipo_producto tp ON CONVERT(a.tipo_producto, CHAR) = CONVERT(tp.clave, CHAR)
                    WHERE (a.Activo = '1') 
                    GROUP BY a.cve_articulo
                    ORDER BY a.des_articulo";

        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

        if (!($res = mysqli_query($conn, $sql))) {
            echo "9Falló la preparación: (" . mysqli_error($conn) . ") " . $sql;
        }

        $filename = "articulos_".date('Ymd') . ".xls";
        ob_clean();
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");

        foreach($columnas as $column) 
        {            
            echo $column . "\t" ;            
        }
        print("\r\n");

        //foreach($data_rutas as $row)
        while($row = mysqli_fetch_object($res))
        {        
            if($this->clear_column($row->Activo) == 1)
            {
                echo $this->clear_column($row->cve_articulo) . "\t";
                echo $this->clear_column($row->cve_alt) . "\t";
                echo $this->clear_column(utf8_decode($row->des_articulo)) . "\t";
                echo $this->clear_column($row->proveedor) . "\t";
                echo $this->clear_column($row->barras2) . "\t";
                echo $this->clear_column($row->UM) . "\t";
                echo $this->clear_column($row->alto) . "\t";
                echo $this->clear_column($row->fondo) . "\t";
                echo $this->clear_column($row->ancho) . "\t";
                echo $this->clear_column($row->peso) . "\t";
                echo $this->clear_column($row->num_multiplo) . "\t";
                echo $this->clear_column($row->clasificacion) . "\t";
                echo $this->clear_column($row->costo) . "\t";
                echo $this->clear_column($row->grupo) . "\t";
                echo $this->clear_column($row->control_abc) . "\t";
                echo $this->clear_column($row->control_lotes) . "\t";
                echo $this->clear_column($row->Caduca) . "\t";
                echo $this->clear_column($row->control_numero_series) . "\t";
                echo $this->clear_column($row->control_peso) . "\t";
                echo $this->clear_column($row->control_volumen) . "\t";
                echo $this->clear_column($row->req_refrigeracion) . "\t";
                echo $this->clear_column($row->mat_peligroso) . "\t";
                echo $this->clear_column($row->Compuesto) . "\t";
                echo $this->clear_column($row->tipo_caja) . "\t";
                echo  "\r\n";
            }
        }
        exit;
        
    }

    private function clear_column(&$str)
    {
        $str = preg_replace("/\t/", "\\t", $str);
        $str = preg_replace("/\r?\n/", "\\n", $str);
        if(strstr($str, '"')) $str = '"' . str_replace('"', '""', $str) . '"';
        return $str;
    }


    public function DocumentosArticulo()
    {
        $statusText = "Documento Importado con Éxito";
        $statusType = 1;
        $cve_articulo = $_POST['cve_articulo_documento'];
        $ruta_path = 'data/uploads/documentos_articulos/';
        $type = $_FILES['image_file_th']['type'];

        $nombre = $_FILES['image_file_th']['name'];
        $nombre_archivo = $nombre;
        $extension = "";

        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

        //$sql_id_pedido = "SELECT id_pedido FROM th_pedido WHERE fol_folio = '$id_embarque_folio'";
        //$res_pedido = mysqli_query($conn, $sql_id_pedido);
        //$embarque_pedido_id = mysqli_fetch_array($res_pedido)["id_pedido"];
/*
        $crear_tabla = "CREATE TABLE IF NOT EXISTS c_articulo_documento (
                          id INT NOT NULL AUTO_INCREMENT,
                          cve_articulo VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL,
                          ruta VARCHAR(200) COLLATE utf8mb4_spanish_ci NOT NULL,
                          descripcion VARCHAR(300) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
                          documento BLOB NOT NULL,
                          TYPE VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL,
                          PRIMARY KEY (`id`)
                        ) ENGINE=INNODB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;";
        $res_tabla = mysqli_query($conn, $crear_tabla);
*/

        $sql_limite = "SELECT COUNT(*) as cantidad FROM c_articulo_documento WHERE cve_articulo = '$cve_articulo'";
        $res_conteo = mysqli_query($conn, $sql_limite);
        $cantidad = mysqli_fetch_array($res_conteo)["cantidad"];

        if($cantidad >= 5)
        {
            $this->response(200, [
                'statusType' =>  "error",
                'statusText' =>  "Límite de 5 Documentos alcanzado"
            ]);
            return;
        }

        for($i = strlen($nombre_archivo)-1; $i > 0; $i--)
        {
          $extension .= $nombre_archivo[$i];
          if($nombre_archivo[$i] == ".")
          {
            break;
          }
        }
        $extension = strrev($extension);
        $_FILES['image_file_th']['name'] = "1"."-".$cve_articulo.$extension;

        $ruta = $ruta_path.$_FILES['image_file_th']['name'];

        while(file_exists($ruta))
        {
            $nombre = $_FILES['image_file_th']['name'];
            $arr = explode("-", $nombre);
            $num = (int)$arr[0];
            $num++;
            $_FILES['image_file_th']['name'] = str_replace(($num-1)."-", $num."-", $nombre);
            $ruta = $ruta_path.$_FILES['image_file_th']['name'];
            $descripcion = $_FILES['image_file_th']['name'];
        }

        $tmp_file = $_FILES['image_file_th']['tmp_name'];
        $documento = file_get_contents($tmp_file);

        if(move_uploaded_file($tmp_file, $ruta))
        {
            $sql = "INSERT INTO c_articulo_documento(cve_articulo, ruta, descripcion, documento, type) VALUES (?, ?, ?, ?, ?)";
            $sth = \db()->prepare($sql);
            $sth->bindParam(1, $cve_articulo);
            $sth->bindParam(2, $ruta);
            $sth->bindParam(3, $nombre_archivo);
            $sth->bindParam(4, $documento);
            $sth->bindParam(5, $type);
        }
        //$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

          if (!$sth->execute())
          {
              $statusText = "Falló la preparación: (" . mysqli_error($conn) . ")"; 
              $statusType = 0;
          }

        $this->response(200, [
            'statusType' =>  $statusType,
            'statusText' =>  $statusText
        ]);
    }

}
