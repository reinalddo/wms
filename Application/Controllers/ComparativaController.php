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
class ComparativaController extends Controller
{
    const CLAVE_ALMACEN   = 0;
    const CLAVE_PROVEEDOR = 1;
    const CLAVE_ARTICULO  = 2;
    const CLAVE_LOTE      = 3;
    const CADUCIDAD       = 4;
    const CANTIDAD        = 5;

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
    public function index()
    {
       
    }

public function clave_permitida($clave)
{
    $permitidos = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '_', ',', '-', '.', '*', ' ');

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

    public function importar()
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
        $linea = 1; $importados = 0; $no_permitidos = 0; $claves_no_permitidas = '';

        $sql = "DELETE FROM t_match";
        $rs = mysqli_query($conn, $sql);

        foreach ($xlsx->rows() as $row)
        {
            if($row[self::CLAVE_ARTICULO]!="")
            {
                if($linea == 1)
                {
                    $linea++;continue;
                }

                //if(!$this->clave_permitida($row[self::CLAVE_ARTICULO]))
                //{
                //    $no_permitidos++;
                //    $claves_no_permitidas .= $row[self::CLAVE_ARTICULO]."\n";
                //    $linea++;continue;
                //}

                $clave_articulo = $this->pSQL($row[self::CLAVE_ARTICULO]);
                $sql = "SELECT COUNT(*) as articulo FROM c_articulo WHERE cve_articulo = '$clave_articulo'";
                $rs = mysqli_query($conn, $sql);
                $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                $articulo_existe = $resul['articulo'];

                $clave_almacen = $this->pSQL($row[self::CLAVE_ALMACEN]);
                //$sql = "SELECT id FROM c_almacenp WHERE clave = '$clave_almacen'";
                //$rs = mysqli_query($conn, $sql);
                //$resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                //$almacen = $resul['id'];

                $clave_proveedor = $this->pSQL($row[self::CLAVE_PROVEEDOR]);

                $clave_lote      = $this->pSQL($row[self::CLAVE_LOTE]);
                $clave_caducidad = $this->pSQL($row[self::CADUCIDAD]);
                $cantidad  = $this->pSQL($row[self::CANTIDAD]);
                //$sql = "SELECT COUNT(*) as proveedores FROM c_proveedores WHERE cve_proveedor = '$clave_proveedor'";
                //$rs = mysqli_query($conn, $sql);
                //$resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                //$proveedor_existe = $resul['proveedores'];
/*
                if(!$proveedor_existe) $clave_proveedor ="";
                else
                {
                    $sql = "SELECT ID_Proveedor FROM c_proveedores WHERE cve_proveedor = '$clave_proveedor'";
                    $rs = mysqli_query($conn, $sql);
                    $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                    $clave_proveedor = $resul['ID_Proveedor'];
                }
*/
                if($articulo_existe)
                {
                    $sql = "INSERT INTO t_match(Cve_Almac, Cve_Articulo, Cve_Lote, Caducidad, Num_Cantidad, Cve_Proveedor) 
                            VALUES ('$clave_almacen', '$clave_articulo', '$clave_lote', '$clave_caducidad', $cantidad, '$clave_proveedor')";
                    $rs = mysqli_query($conn, $sql);
                    $importados++;
                }

            }
            $linea++;
        }
        @unlink($file);
        $mensaje_no_permitidos = "";
        if($no_permitidos) $mensaje_no_permitidos = "\n\nHay {$no_permitidos} claves de artículos con caracteres no permitidos que no se cargaron en el sistema, La clave debe contener solo los caracteres A-Z, a-z, 0-9, _ , sin espacios\n".$claves_no_permitidas;
        $this->response(200, [
            'debug' => $debug,
            'ext' => $extendido,
            'statusText' =>  "Artículos importados con exito. Total de artículos: \"{$importados}\"".$mensaje_no_permitidos,
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
        $columnas = [
            'clave',
            'descripcion',
            'almacen',
            'proveedor',
            'barras2',
            'alto',
            'fondo',
            'ancho',
            'clasificacion',
            'costo',
            'grupo',
            'control_lotes',
            'control_numero_series',
            'control_peso',
            'control_volumen',
            'req_refrigeracion',
            'mat_peligroso',
            'Compuesto',
            'tipo_caja',
        ];

        $data_rutas = Articulos::get();
        $filename = "articulos_".date('Ymd') . ".xls";
        ob_clean();
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        
        foreach($columnas as $column) 
        {            
            echo $column . "\t" ;            
        }
        print("\r\n");

        foreach($data_rutas as $row)
        {        
            if($this->clear_column($row->Activo) == 1)
            {
                echo $this->clear_column($row->cve_articulo) . "\t";
                echo $this->clear_column($row->des_articulo) . "\t";
                echo $this->clear_column($row->cve_almac) . "\t";
                echo $this->clear_column($row->cve_codprov) . "\t";
                echo $this->clear_column($row->barras2) . "\t";
                echo $this->clear_column($row->alto) . "\t";
                echo $this->clear_column($row->fondo) . "\t";
                echo $this->clear_column($row->ancho) . "\t";
                echo $this->clear_column($row->clasificacion) . "\t";
                echo $this->clear_column($row->costo) . "\t";
                echo $this->clear_column($row->grupo) . "\t";
                echo $this->clear_column($row->control_lotes) . "\t";
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
}
