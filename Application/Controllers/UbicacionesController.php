<?php

namespace Application\Controllers;

use Framework\View\View;
use Framework\Helpers\Utils;
use Framework\Http\Response;
use Framework\Http\Controller;
use jakeroid\tools\SimpleXLSX;
use Application\Models\Usuarios;
use Application\Models\Ubicaciones;
use Application\Models\ZonasDeAlmacenaje;
use Illuminate\Database\Capsule\Manager as Capsule;


/**
 * @version 1.0.0
 * @category Ubicaciones
 * @author Brayan Rincon <brayan262@gemail.com>
 */
class UbicacionesController extends Controller
{
    const CVE_ALMACEN     = 0;
    const CVE_ZONA        = 1;
    const ZONA_ALMACENAJE = 2;
    const PASILLO         = 3;
    const RACK            = 4;
    const NIVEL           = 5;
    const SECCION         = 6;
    const POSICION        = 7;
    const CODIGO_BL       = 8;
    const ALTO            = 9;
    const ANCHO           = 10;
    const FONDO           = 11;
    const PESO_MAX        = 12;
    const TIPO            = 13;
    const PICKING         = 14;
    const PTL             = 15;
    const ACOMODO_MIXTO   = 16;
    const AREA_PRODUCCION = 17;
    const TRASLADO        = 18;
    const EMBARQUE        = 19;
    const SALIDA          = 20;

/*
    const ALMACEN        = 0;
    const ZONA           = 1;
    const PASILLO        = 2;
    const RACK           = 3;
    const NIVEL          = 4;
    const POSICION       = 5;
    const SECCION        = 6;
    const CODIGO_BL      = 7;
    const ALTO           = 8;
    const ANCHO          = 9;
    const FONDO          = 10;
    const PESO_MAX       = 11;    
    const UBICACION_RACK = 12;
    const UBICACION_PISO = 13;
    const TIPO           = 14;
    const PICKING        = 15;
    const PTL            = 16;
    const ACOMODO_MIXTO  = 17;
    const MANUFACTURA    = 18;
*/
/*
    private $camposRequeridos = [
        self::ALMACEN => 'Almacén', 
        self::ZONA => 'Zona', 
        self::PASILLO => 'Pasillo',
    ];
*/
    /**
     * Renderiza la vista general
     *
     * @return void
     */
    public function index()
    {
       
    }

    public function importar()
    {
        $modificar_importacion = $_POST['modificar_importacion'];

        $dir_cache = PATH_APP . 'Cache/';
        $file = $dir_cache . basename($_FILES['file']['name']);

        if (! move_uploaded_file($_FILES['file']['tmp_name'], $file)) {
            $this->response(400, [
                'statusText' =>  "Error al recibir el fichero, verifique que se tenga permisos para escribir en Cache",
            ]);
        }

/*
        if ( $_FILES['file']['type'] != 'application/vnd.ms-excel' AND
                $_FILES['file']['type'] != 'application/msexcel' AND
                $_FILES['file']['type'] != 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' AND
                $_FILES['file']['type'] != 'application/xls' )
            {
            @unlink($file);
            $this->response(400, [
                'statusText' =>  "Error en el formato del fichero",
            ]);
        }
*/
        $xlsx = new SimpleXLSX( $file );
/*
        $linea = 1;
        foreach ($xlsx->rows() as $row)
        {
            if($linea == 1) {
                $linea++;continue;
            }

            $eval = $this->validarRequeridosImportar($row);
            if( $eval === TRUE ){
            }

            else {
                $this->response(400, [
                    'statusText' =>  "La columna \"{$eval}\" de la Fila Nro. {$linea} está vacía. Corrija esto para continuar con la importación.",
                ]);
            }

            $linea++;
        }
*/
        $linea = 1; $registros = 0; $csdrepetidos = 0;
        foreach ($xlsx->rows() as $row)
        {
            if($linea == 1 || trim($this->pSQL($row[self::CODIGO_BL])) == '') {
                $linea++;continue;
            }
            $clave = trim($this->pSQL($row[self::CODIGO_BL]));
            $codigobl = $clave;

            $posbl = strpos($codigobl, 'Ñ');
            if($posbl >= 0 && $posbl != '')
            {
                $codigobl = str_replace('Ñ', 'NN', $codigobl);
            }
            $element = Ubicaciones::where('CodigoCSD', '=', $clave)->first();
/*
            $tipo = '';
            if($row[self::LIBRE] == 'S') $tipo = 'L';
            if($row[self::CUARENTENA] == 'S') $tipo = 'Q';
            if($row[self::RESERVADA] == 'S') $tipo = 'R';
*/
            if($element != NULL){
                $model = $element; 
            }
            else {
                $model = new Ubicaciones(); 
            }

            //$model = new Ubicaciones(); 
            //$id_zona = ZonasDeAlmacenaje::where('clave_almacen', "'".$row[self::ZONA]."'")->get(['cve_almac'])->first();
            $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
            $cve_zona = trim($row[self::CVE_ZONA]);
            $sql = "SELECT cve_almac as id_zona FROM c_almacen WHERE clave_almacen = '$cve_zona'";
            $rs = mysqli_query($conn, $sql);
            $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
            $id_zona = $resul['id_zona'];
            if(mysqli_num_rows($rs) == 0)
                $id_zona = 0;

            $CodigoCSD = $codigobl;//$row[self::CODIGO_BL];
            $sql = "SELECT COUNT(*) as codigocsd FROM c_ubicacion WHERE CodigoCSD = '$CodigoCSD' AND cve_almac = '$id_zona'";
            $rs = mysqli_query($conn, $sql);
            $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
            $codigocsd = $resul['codigocsd'];

            $id_almacen = 0;
            if(!$codigocsd && $modificar_importacion == 0)
            {
            if(!$id_zona)
            {
                $cve_almacen = trim($this->pSQL($row[self::CVE_ALMACEN]));
                $sql = "SELECT id FROM c_almacenp WHERE clave = '{$cve_almacen}'";
                $rs = mysqli_query($conn, $sql);
                $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                $id_almacen = $resul['id'];

                $model_zona = new ZonasDeAlmacenaje();
                $model_zona->clave_almacen = trim($this->pSQL($row[self::CVE_ZONA]));
                $model_zona->cve_almacenp  = $id_almacen;
                $model_zona->des_almac     = trim($this->pSQL($row[self::ZONA_ALMACENAJE]));
                $model_zona->save();

                $cve_zona = trim($row[self::CVE_ZONA]);
                $sql = "SELECT cve_almac FROM c_almacen WHERE clave_almacen = '{$cve_zona}'";
                $rs = mysqli_query($conn, $sql);
                $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                $id_zona = $resul['cve_almac'];
            }

            if($id_zona)
            {
                $model->cve_almac       = $id_zona;//$this->pSQL($row[self::ZONA]);
/*
CVE_ALMACEN                  CVE_ZONA           ZONA_ALMACENAJE         PASILLO         RACK           NIVEL        SECCION
POSICION                     CODIGO_BL          ALTO                    ANCHO           FONDO          PESO_MAX     TIPO
PICKING                      PTL                ACOMODO_MIXTO           AREA_PRODUCCION     TRASLADO
*/

                //$model->Ubicacion       = $this->pSQL($row[self::ZONA]);
                //$model->idy_ubica       = 22;
                if(!$codigobl) {
                    $linea++;continue;
                }

                $status = 'N';
                if(trim($this->pSQL($row[self::TRASLADO])) == 'S') $status = 'T';
                else if(trim($this->pSQL($row[self::EMBARQUE])) == 'S') $status = 'E';
                else if(trim($this->pSQL($row[self::SALIDA])) == 'S') $status = 'S';

                $model->cve_pasillo     = trim($this->pSQL($row[self::PASILLO]));
                $model->cve_rack        = trim($this->pSQL($row[self::RACK]));
                $model->cve_nivel       = trim($this->pSQL($row[self::NIVEL]));
                $model->Ubicacion       = trim($this->pSQL($row[self::POSICION]));
                $model->Seccion         = trim($this->pSQL($row[self::SECCION]));
                $model->CodigoCSD       = $codigobl;//$this->pSQL($row[self::CODIGO_BL]);
                $model->num_alto        = trim($this->pSQL($row[self::ALTO]));
                $model->num_ancho       = trim($this->pSQL($row[self::ANCHO]));
                $model->num_largo       = trim($this->pSQL($row[self::FONDO]));
                $model->PesoMaximo      = trim($this->pSQL($row[self::PESO_MAX]));
                $model->tipo            = trim($this->pSQL($row[self::TIPO]));
                $model->picking         = trim($this->pSQL($row[self::PICKING]));
                $model->Ptl             = trim($this->pSQL($row[self::PTL]));
                $model->AcomodoMixto    = trim($this->pSQL($row[self::ACOMODO_MIXTO]));
                $model->AreaProduccion  = trim($this->pSQL($row[self::AREA_PRODUCCION]));
                $model->Status          = $status;
                $model->orden_secuencia = "";
                $model->Reabasto        = "";
                //$model->PesoMaximo       = $this->pSQL($row[self::PESO_MAX]);
                //$model->PesoMaximo       = $this->pSQL($row[self::PESO_MAX]);

                $model->save();
            }
            }
            else if($codigocsd && $modificar_importacion == 1)
            {
                $cve_zona = trim($row[self::CVE_ZONA]);
                $sql = "SELECT cve_almac FROM c_almacen WHERE clave_almacen = '{$cve_zona}'";
                $rs = mysqli_query($conn, $sql);
                $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                $id_zona = $resul['cve_almac'];

                if($cve_zona && $codigobl)
                {
                    $sql = "UPDATE c_ubicacion SET cve_almac = '{$id_zona}' WHERE CodigoCSD = '{$codigobl}'";
                    $rs = mysqli_query($conn, $sql);
                }
                $cve_pasillo     = trim($this->pSQL($row[self::PASILLO]));
                if($cve_pasillo && $codigobl)
                {
                    $sql = "UPDATE c_ubicacion SET cve_pasillo = '{$cve_pasillo}' WHERE CodigoCSD = '{$codigobl}' AND cve_almac = '$id_zona'";
                    $rs = mysqli_query($conn, $sql);
                }

                $cve_rack        = trim($this->pSQL($row[self::RACK]));
                if($cve_rack && $codigobl)
                {
                    $sql = "UPDATE c_ubicacion SET cve_rack = '{$cve_rack}' WHERE CodigoCSD = '{$codigobl}' AND cve_almac = '$id_zona'";
                    $rs = mysqli_query($conn, $sql);
                }

                $cve_nivel       = trim($this->pSQL($row[self::NIVEL]));
                if($cve_nivel && $codigobl)
                {
                    $sql = "UPDATE c_ubicacion SET cve_nivel = '{$cve_nivel}' WHERE CodigoCSD = '{$codigobl}' AND cve_almac = '$id_zona'";
                    $rs = mysqli_query($conn, $sql);
                }

                $Ubicacion       = trim($this->pSQL($row[self::POSICION]));
                if($Ubicacion && $codigobl)
                {
                    $sql = "UPDATE c_ubicacion SET Ubicacion = '{$Ubicacion}' WHERE CodigoCSD = '{$codigobl}' AND cve_almac = '$id_zona'";
                    $rs = mysqli_query($conn, $sql);
                }

                $Seccion         = trim($this->pSQL($row[self::SECCION]));
                if($Seccion && $codigobl)
                {
                    $sql = "UPDATE c_ubicacion SET Seccion = '{$Seccion}' WHERE CodigoCSD = '{$codigobl}' AND cve_almac = '$id_zona'";
                    $rs = mysqli_query($conn, $sql);
                }

                $alto = trim($row[self::ALTO]);
                if($alto && $codigobl)
                {
                    $sql = "UPDATE c_ubicacion SET num_alto = '{$alto}' WHERE CodigoCSD = '{$codigobl}' AND cve_almac = '$id_zona'";
                    $rs = mysqli_query($conn, $sql);
                }

                $ancho = trim($row[self::ANCHO]);
                if($ancho && $codigobl)
                {
                    $sql = "UPDATE c_ubicacion SET num_ancho = '{$ancho}' WHERE CodigoCSD = '{$codigobl}' AND cve_almac = '$id_zona'";
                    $rs = mysqli_query($conn, $sql);
                }

                $fondo = trim($row[self::FONDO]);
                if($fondo && $codigobl)
                {
                    $sql = "UPDATE c_ubicacion SET num_largo = '{$fondo}' WHERE CodigoCSD = '{$codigobl}' AND cve_almac = '$id_zona'";
                    $rs = mysqli_query($conn, $sql);
                }

                $PesoMaximo = trim($row[self::PESO_MAX]);
                if($PesoMaximo && $codigobl)
                {
                    $sql = "UPDATE c_ubicacion SET PesoMaximo = '{$PesoMaximo}' WHERE CodigoCSD = '{$codigobl}' AND cve_almac = '$id_zona'";
                    $rs = mysqli_query($conn, $sql);
                }

                $tipo = $row[self::TIPO];
                if($tipo && $codigobl)
                {
                    $sql = "UPDATE c_ubicacion SET tipo = '{$tipo}' WHERE CodigoCSD = '{$codigobl}' AND cve_almac = '$id_zona'";
                    $rs = mysqli_query($conn, $sql);
                }

                $picking = trim($row[self::PICKING]);
                if($picking && $codigobl)
                {
                    $sql = "UPDATE c_ubicacion SET picking = '{$picking}' WHERE CodigoCSD = '{$codigobl}' AND cve_almac = '$id_zona'";
                    $rs = mysqli_query($conn, $sql);
                }

                $Ptl = trim($row[self::PTL]);
                if($Ptl && $codigobl)
                {
                    $sql = "UPDATE c_ubicacion SET Ptl = '{$Ptl}' WHERE CodigoCSD = '{$codigobl}' AND cve_almac = '$id_zona'";
                    $rs = mysqli_query($conn, $sql);
                }

                $AcomodoMixto = trim($row[self::ACOMODO_MIXTO]);
                if($AcomodoMixto && $codigobl)
                {
                    $sql = "UPDATE c_ubicacion SET AcomodoMixto = '{$AcomodoMixto}' WHERE CodigoCSD = '{$codigobl}' AND cve_almac = '$id_zona'";
                    $rs = mysqli_query($conn, $sql);
                }

                $AreaProduccion = trim($row[self::AREA_PRODUCCION]);
                if($AreaProduccion && $codigobl)
                {
                    $sql = "UPDATE c_ubicacion SET AreaProduccion = '{$AreaProduccion}' WHERE CodigoCSD = '{$codigobl}' AND cve_almac = '$id_zona'";
                    $rs = mysqli_query($conn, $sql);
                }
                $traslado = trim($row[self::TRASLADO]);
                if($traslado && $codigobl)
                {
                    if($traslado == 'S') $traslado = 'T';
                    $sql = "UPDATE c_ubicacion SET Status = '{$traslado}' WHERE CodigoCSD = '{$codigobl}' AND cve_almac = '$id_zona'";
                    $rs = mysqli_query($conn, $sql);
                }
                $embarque = trim($row[self::EMBARQUE]);
                if($embarque && $codigobl)
                {
                    if($embarque == 'S') $embarque = 'E';
                    $sql = "UPDATE c_ubicacion SET Status = '{$embarque}' WHERE CodigoCSD = '{$codigobl}' AND cve_almac = '$id_zona'";
                    $rs = mysqli_query($conn, $sql);
                }
                $salida = trim($row[self::SALIDA]);
                if($salida && $codigobl)
                {
                    if($salida == 'S') $salida = 'S';
                    $sql = "UPDATE c_ubicacion SET Status = '{$salida}' WHERE CodigoCSD = '{$codigobl}' AND cve_almac = '$id_zona'";
                    $rs = mysqli_query($conn, $sql);
                }

            }
            else 
                $csdrepetidos++;

            $linea++;
            $registros++;
        }

        @unlink($file);

        $repetidos = "";
        //if($csdrepetidos > 0)
            //$repetidos = "\n\nHay {$csdrepetidos} Codigos BL Repetidos";

        $this->response(200, [
            'statusText' =>  "Ubicaciones importados con exito. Total de Ubicaciones: \"{$registros}\" {$repetidos}",
        ]);

    }
/*
    public function validarRequeridosImportar($row)
    {
        foreach ($this->camposRequeridos as $key => $campo){
            if( empty($row[$key]) ){
                return $campo;
            }
        }
        return true;
    }
*/
    /**
     * Undocumented function
     *
     * @return void
     */
    public function exportar()
    {
        $columnas = [
            'almacen',
            'zona',
            'pasillo',
            'rack',
            'nivel',
            'posicion',
            'seccion',
            'codigo_bl',
            'alto',
            'ancho',
            'fondo',
            'peso_max',
            'ubicacion_rack',//
            'ubicacion_piso',//
            'libre',
            'reservada',
            'cuarentena',
            'picking',
            'ptl',
            'acomodo_mixto',
            'manufactura',
            'Status',
            'Ruta de Surtido',
            'Secuencia'//
        ];

        //$almacen = $_GET['almacen'];

        $almacen            = $_GET['almacen'];
        $zona               = $_GET['zona'];
        $codigoBL           = $_GET['codigoBL'];
        $rack               = $_GET['rack'];
        $busqueda           = $_GET['busqueda'];
        $tipo_ubicacion     = $_GET['tipo_ubicacion'];
        $con_sin_existencia = $_GET['con_sin_existencia'];
        $instancia          = $_GET['instancia'];


/*
        $orden_bl = " ORDER BY ISNULL(sec_surtido), sec_surtido ASC ";
        if(isset($_POST['orden_bl']))
        {
            if($_POST['orden_bl'] == "1")
               $orden_bl = " ORDER BY t.CodigoCSD ASC ";
        }
*/
        //$data_model = Ubicaciones::get();

        $sqlZona = "";
        if($zona != '')
            $sqlZona = " AND u.cve_almac = '{$zona}' ";

        $sqlRack = "";
        if($rack != '')
            $sqlRack = " AND u.cve_rack = '{$rack}' ";

        $sqlBL = "";
        if($codigoBL != '')
            $sqlBL = " AND u.CodigoCSD = '{$codigoBL}' ";

        $sqlBusq = "";
        if($busqueda != '')
            $sqlBusq = " AND (u.CodigoCSD LIKE '%{$busqueda}%' OR a.desc_almac LIKE '%{$busqueda}%' OR u.cve_rack LIKE '%{$busqueda}%') ";

        $sqlTipo = "";
        if($tipo_ubicacion != '')
            $sqlTipo = " AND u.Tipo = '{$tipo_ubicacion}' ";

        $SqlExistencia = "";
        if($con_sin_existencia != "0")
        {
          if($con_sin_existencia == "1") //Con Existencia
          {
             $SqlExistencia = " AND ((u.idy_ubica IN (SELECT idy_ubica FROM ts_existenciapiezas WHERE cve_almac = '$almacen'))
                        OR  (u.idy_ubica IN (SELECT idy_ubica FROM ts_existenciatarima WHERE cve_almac = '$almacen'))
                        OR  (u.idy_ubica IN (SELECT idy_ubica FROM ts_existenciacajas WHERE Cve_Almac = '$almacen'))) 
                      ";
          }
          else //Sin Existencia
          {
             $SqlExistencia = " AND (u.idy_ubica NOT IN (SELECT idy_ubica FROM ts_existenciapiezas WHERE cve_almac = '$almacen' ))
                        AND (u.idy_ubica NOT IN (SELECT idy_ubica FROM ts_existenciatarima WHERE cve_almac = '$almacen' ))
                        AND (u.idy_ubica NOT IN (SELECT idy_ubica FROM ts_existenciacajas WHERE Cve_Almac = '$almacen' )) ";
          }

        }


        $sql = "SELECT a.clave_almacen, a.des_almac, u.*,
                    IFNULL(thrs.nombre, '') AS ruta_surtido,
                    IFNULL(rs.orden_secuencia, '') AS sec_surtido
                FROM c_ubicacion u 
                LEFT JOIN c_almacen a ON a.cve_almac = u.cve_almac
                LEFT JOIN td_ruta_surtido rs ON rs.idy_ubica = u.idy_ubica AND rs.Activo = 1
                LEFT JOIN th_ruta_surtido thrs ON thrs.idr = rs.idr AND thrs.Activo = 1
                WHERE a.cve_almacenp = $almacen {$sqlZona} {$sqlRack} {$sqlBL} {$sqlBusq} {$sqlTipo} {$SqlExistencia}";

        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

        if (!($res = mysqli_query($conn, $sql))) {
            echo "9Falló la preparación: (" . mysqli_error($conn) . ") " . $sql;
        }

        $filename = "ubicaciones-libres_".date('Ymd') . ".xls";

        ob_clean();
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        
        foreach($columnas as $column) {            
            echo $column . "\t" ;            
        }
        print("\r\n");

        //foreach($data_model as $row)
        while($row = mysqli_fetch_object($res))
        {
            //$element = ZonasDeAlmacenaje::where('cve_almac', '=',$row->cve_almac)->first();
            //echo var_dump($element);
            $tipo = $row->Tipo;
            $traslado = $row->Status;
            $piso = 'N';
            if($tipo == 'L') $libre = '1'; else $libre = '0';
            if($tipo == 'R') $reserva = '1'; else $reserva = '0';
            if($tipo == 'Q') $cuarentena = '1'; else $cuarentena = '0';
            if($row->Tipo == 'PTL') $ptl = '1'; else $ptl = '0';

            if($traslado == 'T') $traslado = 'S';

            if($row->picking == 'S') $picking = '1'; else $picking = '0';
            if($row->acomodo== 'S') $acomodo = '1'; else $acomodo = '0';
            if($row->cve_nivel >=1) $ubicacion = 'N'; else {$ubicacion = 'S'; $piso = 'S';}

            if($row->Activo == 1)
            {
                if($instancia == 'repremundo' || $instancia == 'dev')
                    $codigocsd = $row->CodigoCSD;
                else
                    $codigocsd = str_replace("-", "_", $row->CodigoCSD);
            echo $this->clear_column($row->clave_almacen) . "\t";
            echo $this->clear_column($row->des_almac) . "\t";
            echo $this->clear_column($row->cve_pasillo) . "\t";
            echo $this->clear_column($row->cve_rack) . "\t";
            echo $this->clear_column($row->cve_nivel) . "\t";
            echo $this->clear_column($row->Ubicacion) . "\t";
            echo $this->clear_column($row->Seccion) . "\t";
            echo $this->clear_column($codigocsd) . "\t";
            echo $this->clear_column($row->num_largo) . "\t";
            echo $this->clear_column($row->num_ancho) . "\t";
            echo $this->clear_column($row->num_alto) . "\t";
            echo $this->clear_column($row->PesoMaximo) . "\t";
            echo $this->clear_column($ubicacion) . "\t";            
            echo $this->clear_column($piso) . "\t";            
            echo $this->clear_column($libre) . "\t";
            echo $this->clear_column($reserva) . "\t";
            echo $this->clear_column($cuarentena) . "\t";
            echo $this->clear_column($picking) . "\t";
            echo $this->clear_column($ptl) . "\t";
            echo $this->clear_column($acomodo) . "\t";
            echo ($this->clear_column($row->AreaProduccion)=='')?('N'):($this->clear_column($row->AreaProduccion)) . "\t";
            echo $this->clear_column($traslado) . "\t";
            echo $this->clear_column($row->ruta_surtido) . "\t";
            echo $this->clear_column($row->sec_surtido) . "\t";


            //echo $this->clear_column($row->AcomodoMixto) . "\t";
            echo  "\r\n";
            }
        }
        exit;
        
    }

    /**
     * Undocumented function
     *
     * @param [type] $str
     * @return void
     */
    private function clear_column(&$str)
    {
        $str = preg_replace("/\t/", "\\t", $str);
        $str = preg_replace("/\r?\n/", "\\n", $str);
        if(strstr($str, '"')) $str = '"' . str_replace('"', '""', $str) . '"';
        return $str;
    }


}
