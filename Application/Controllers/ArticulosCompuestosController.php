<?php

namespace Application\Controllers;

use Framework\View\View;
use Framework\Helpers\Utils;
use Framework\Http\Response;
use Framework\Http\Controller;
use jakeroid\tools\SimpleXLSX;
use Application\Models\Usuarios;
use Application\Models\ArticulosCompuestos;
use Illuminate\Database\Capsule\Manager as Capsule;


/**
 * @version 1.0.0
 * @category Ubicaciones
 * @author Brayan Rincon <brayan262@gemail.com>
 */
class ArticulosCompuestosController extends Controller
{
    const CVE_ART_COMP = 0;
    const CVE_ART_CAT  = 1;
    const CANT_REQ     = 2;
    const ETAPA        = 3;
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
        $articulo_compuesto_array = array();
        $linea = 1; $registros = 0; $art_repetidos = 0; $art_no_surtible = 0;
        foreach ($xlsx->rows() as $row)
        {
            if($linea == 1) {
                $linea++;continue;
            }
            //$clave = $this->pSQL($row[self::CODIGO_BL]);
            //$element = Ubicaciones::where('CodigoCSD', '=', $clave)->first();
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
                $model = new ArticulosCompuestos(); 
            }

            //$model = new Ubicaciones(); 
            //$id_zona = ZonasDeAlmacenaje::where('clave_almacen', "'".$row[self::ZONA]."'")->get(['cve_almac'])->first();
            $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
            $cve_art_comp = $row[self::CVE_ART_COMP];
            $sql = "SELECT cve_articulo FROM c_articulo WHERE cve_articulo = '{$cve_art_comp}' AND compuesto = 'S'";
            $rs = mysqli_query($conn, $sql);
            $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
            $cve_articulo = $resul['cve_articulo'];

            $cve_art_cat = $row[self::CVE_ART_CAT];

            $sql_track = $sql."\n\n";

            if($cve_articulo)
            {
                if(!in_array($cve_articulo, $articulo_compuesto_array))
                {
                    $sql = "DELETE FROM t_artcompuesto WHERE Cve_ArtComponente = '{$cve_articulo}'";
                    $sql_track .= $sql."\n\n";
                    $rs = mysqli_query($conn, $sql);
                }

                //$sql = "SELECT cve_articulo FROM c_articulo WHERE cve_articulo = '{$cve_art_cat}' AND compuesto = 'N'";
                $sql = "SELECT COUNT(*) as existe FROM t_artcompuesto WHERE Cve_Articulo = '{$cve_art_cat}' AND Cve_ArtComponente = '{$cve_articulo}'";
                $sql_track .= $sql."\n\n";
                $rs = mysqli_query($conn, $sql);
                $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                $cve_articulo_existe = $resul['existe'];
/*
                $articulo_cat = $row[self::CVE_ART_CAT];
                $sql = "SELECT COUNT(*) as es_no_surtible FROM c_articulo WHERE cve_articulo = '{$cve_art_cat}' AND tipo_producto = 'ProductoNoSurtible'";
                $sql_track .= $sql."\n\n";
                $rs = mysqli_query($conn, $sql);
                $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                $es_no_surtible = $resul['es_no_surtible'];
*/
                if(!$cve_articulo_existe/* && $es_no_surtible == 0*/)
                {//AND c.compuesto = 'N' 
                //**ESTA RESTRICCION DE QUE LOS COMPONENTES SEAN Compuesto = 'N' ME DIJO (ALEJANDRO) QUE LA QUITE EN UNA SESION DE TEAMS DEL 19-01-2024
                    $sql = "SELECT um.cve_umed FROM c_articulo c LEFT JOIN c_unimed um ON um.id_umed = c.unidadMedida WHERE c.cve_articulo = '{$cve_art_cat}'";
                    $sql_track .= $sql."\n\n";
                    $rs = mysqli_query($conn, $sql);
                    $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                    $cve_umed = $resul['cve_umed'];

                    $model->Cve_Articulo      = $this->pSQL($row[self::CVE_ART_CAT]);
                    $model->Cve_ArtComponente = $this->pSQL($row[self::CVE_ART_COMP]);
                    $model->Cantidad          = $this->pSQL($row[self::CANT_REQ]);
                    $model->Etapa          = $this->pSQL($row[self::ETAPA]);
                    $model->cve_umed          = $cve_umed.'';
                    $sql_track .= $this->pSQL($row[self::CVE_ART_CAT])." | ".$this->pSQL($row[self::CVE_ART_COMP])." | ".$this->pSQL($row[self::CANT_REQ])." | ".$cve_umed.''."\n\n";
                    $model->save();
                    array_push($articulo_compuesto_array, $cve_articulo);
                    $registros++;
                }
                else 
                {
                    //if($es_no_surtible > 0)
                    //    $art_no_surtible++;
                    //else
                        $art_repetidos++;
                }
            }
            $linea++;
        }


        @unlink($file);
        $mensaje_repetidos = "";$mensaje_no_surtibles = "";
        if($art_repetidos > 0)
            $mensaje_repetidos = "\n\n\nNo se cargaron {$art_repetidos} artículos por estar repetidos";
        //if($art_no_surtible > 0)
        //    $mensaje_no_surtibles = "\n\n\nNo se cargaron {$art_no_surtible} artículos declarados como NO SURTIBLES";

        $this->response(200, [
            'statusText' =>  "Artículos importados con exito. Total de Artículos: \"{$registros}\"".$mensaje_repetidos.$mensaje_no_surtibles,
            'sql_track' => $sql_track
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
            'manufactura'//
        ];

        $data_model = Ubicaciones::get();
        $filename = "ubicaciones-libres_".date('Ymd') . ".xls";

        ob_clean();
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        
        foreach($columnas as $column) {            
            echo $column . "\t" ;            
        }
        print("\r\n");

        foreach($data_model as $row)
        {
            $element = ZonasDeAlmacenaje::where('cve_almac', '=',$row->cve_almac)->first();
            //echo var_dump($element);
            $tipo = $row->Tipo;
            if($tipo == 'L') $libre = '1'; else $libre = '0';
            if($tipo == 'R') $reserva = '1'; else $reserva = '0';
            if($tipo == 'Q') $cuarentena = '1'; else $cuarentena = '0';
            if($row->Tipo == 'PTL') $ptl = '1'; else $ptl = '0';

            if($row->picking == 'S') $picking = '1'; else $picking = '0';
            if($row->acomodo== 'S') $acomodo = '1'; else $acomodo = '0';
            if($row->cve_nivel >=1) $ubicacion = 'N'; else $ubicacion = 'S';

            echo $this->clear_column($row->cve_almac) . "\t";
            echo $this->clear_column($element->des_almac) . "\t";
            echo $this->clear_column($row->cve_pasillo) . "\t";
            echo $this->clear_column($row->cve_rack) . "\t";
            echo $this->clear_column($row->cve_nivel) . "\t";
            echo $this->clear_column($row->Ubicacion) . "\t";
            echo $this->clear_column($row->Seccion) . "\t";
            echo $this->clear_column($row->CodigoCSD) . "\t";
            echo $this->clear_column($row->num_largo) . "\t";
            echo $this->clear_column($row->num_ancho) . "\t";
            echo $this->clear_column($row->num_alto) . "\t";
            echo $this->clear_column($row->PesoMaximo) . "\t";
            echo $this->clear_column($ubicacion) . "\t";            
            echo $this->clear_column($libre) . "\t";
            echo $this->clear_column($reserva) . "\t";
            echo $this->clear_column($cuarentena) . "\t";
            echo $this->clear_column($picking) . "\t";
            echo $this->clear_column($ptl) . "\t";
            echo $this->clear_column($acomodo) . "\t";
            echo $this->clear_column($row->AreaProduccion) . "\t";
            //echo $this->clear_column($row->AcomodoMixto) . "\t";
            echo  "\r\n";
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
