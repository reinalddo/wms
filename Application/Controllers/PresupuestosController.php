<?php

namespace Application\Controllers;

use Framework\View\View;
use Framework\Helpers\Utils;
use Framework\Http\Response;
use Framework\Http\Controller;
use jakeroid\tools\SimpleXLSX;
use Application\Models\Presupuestos;
use Illuminate\Database\Capsule\Manager as Capsule;


/**
 * @version 1.0.0
 * @category Usuarios
 * @author Brayan Rincon <brayan262@gemail.com>
 */
class PresupuestosController extends Controller
{
    const NOMBRE          = 0;
    const ANIO            = 1;
    const CLAVE           = 2;
    const CONCEPTO        = 3;
    const MONTO           = 4;

    private $camposRequeridos = [
        self::NOMBRE => 'Nombre', 
        self::ANIO => 'Anio', 
        self::CLAVE => 'Clave',
        self::CONCEPTO => 'Concepto',
        self::MONTO => 'Monto',
    ];
    
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

        if (! move_uploaded_file($_FILES['file']['tmp_name'], $file)) 
        {
            $this->response(400, [
                'statusText' =>  "Error al recibir el fichero. Verifique que se tenga permisos para escribir en Cache",
            ]);
        }

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

        $linea = 1;
        foreach ($xlsx->rows() as $row)
        {
            if($row[self::CLAVE]!="")
            {
                if($linea == 1) 
                {
                    $linea++;continue;
                }
                $clave = $this->pSQL($row[self::CLAVE]);
                $element = Presupuestos::where('claveDePartida', '=', $clave)->first();

                if($element != NULL)
                {
                    $model = $element; 
                }
                else 
                {
                    $model = new Presupuestos(); 
                }
                $model->nombreDePresupuesto     = $this->pSQL($row[self::NOMBRE]);
                $model->anoDePresupuesto        = $this->pSQL($row[self::ANIO]);
                $model->claveDePartida          = $clave;
                $model->conceptoDePartida       = $this->pSQL($row[self::CONCEPTO]);
                $model->monto                   = $this->pSQL($row[self::MONTO]);
                $model->save();
            }
            $linea++;
        }
        @unlink($file);
        $this->response(200, [
            'debug' => $debug,
            'ext' => $extendido,
            'statusText' =>  "Artículos importados con exito. Total de artículos: \"{$linea}\"",
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
            'Nombre',
            'Anio',
            'Clave',
            'Concepto',
            'Monto',
        ];

        $data_rutas = Presupuestos::get();
        $filename = "presupuestos_".date('Ymd') . ".xls";
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
            echo $this->clear_column(utf8_decode($row->nombreDePresupuesto)) . "\t";
            echo $this->clear_column($row->anoDePresupuesto) . "\t";
            echo $this->clear_column($row->claveDePartida) . "\t";
            echo $this->clear_column(utf8_decode($row->conceptoDePartida)) . "\t";
            echo $this->clear_column($row->monto) . "\t";
            echo  "\r\n";
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
