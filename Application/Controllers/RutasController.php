<?php

namespace Application\Controllers;

use Framework\View\View;
use Framework\Helpers\Utils;
use Framework\Http\Response;
use Framework\Http\Controller;
use jakeroid\tools\SimpleXLSX;
use Application\Models\Rutas;
use Illuminate\Database\Capsule\Manager as Capsule;


/**
 * @version 1.0.0
 * @category Usuarios
 * @author Brayan Rincon <brayan262@gemail.com>
 */
class RutasController extends Controller
{
    const CLAVE = 0;
    const DESCRIPCION = 1;
    //const STATUS = 2;
    const CLAVE_ALMACEN = 2;
    //const ACTIVO = 3;
  

    private $camposRequeridos = [
        self::CLAVE => 'Clave', 
        self::DESCRIPCION => 'Descripcion', 
        self::CLAVE_ALMACEN => 'Clave almacén',
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

        if (! move_uploaded_file($_FILES['file']['tmp_name'], $file)) {
            $this->response(400, [
                'statusText' =>  "Error al recibir el fichero, verifique que se tenga permisos para escribir en Cache",
            ]);
        }


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

        $xlsx = new SimpleXLSX( $file );
        $linea = 1;
        
        foreach ($xlsx->rows() as $row)
        {
            if($linea == 1) {
                $linea++;continue;
            }

            $eval = $this->validarRequeridosImportar($row);
            if( $eval !== TRUE ){
                $this->response(400, [
                    'statusText' =>  "La columna \"{$eval}\" de la Fila Nro. {$linea} está vacía. Corrija esto para continuar con la importación.",
                ]);
            }
            $linea++;
        }

        $linea = 1;
        foreach ($xlsx->rows() as $row)
        {
            if($linea == 1) {
                $linea++;continue;
            }
            $clave = $this->pSQL($row[self::CLAVE]);
            $element = Rutas::where('cve_ruta', '=', $clave)->first();

            if($element != NULL){
                $model = $element; 
            }
            else {
                $model = new Rutas(); 
            }
            
            $model->cve_ruta        = $clave;
            $model->descripcion     = $this->pSQL($row[self::DESCRIPCION]);
            $model->status          = 'A';//$this->pSQL($row[self::STATUS]);
            $model->cve_almacenp    = $this->pSQL($row[self::CLAVE_ALMACEN]);
            $model->Activo          = 1;//$this->pSQL($row[self::ACTIVO]);
            $model->save();
            $linea++;
        }
        
        @unlink($file);

        $this->response(200, [
            'statusText' =>  "Rutas importados con exito. Total de Rutas: \"{$linea}\"",
        ]);

    }

    public function validarRequeridosImportar($row)
    {
        foreach ($this->camposRequeridos as $key => $campo){
            if( empty($row[$key]) ){
                return $campo;
            }
        }
        return true;
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public function exportar()
    {
        $columnas = [
            'clave_ruta',
            'descripcion',
            'estatus',
            'clave_almacen',
            'activo',
        ];

        $data_rutas = Rutas::get([
            'cve_ruta',
            'descripcion',
            'status',
            'cve_almacenp',
            'Activo',
        ]);

        $filename = "rutas_".date('Ymd') . ".xls";

        ob_clean();
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        
        foreach($columnas as $column) {            
            echo $column . "\t" ;            
        }
        print("\r\n");

        foreach($data_rutas as $row)
        {            
            echo $this->clear_column($row->cve_ruta) . "\t";
            echo $this->clear_column($row->descripcion) . "\t";
            echo $this->clear_column($row->status) . "\t";
            echo $this->clear_column($row->cve_almacenp) . "\t";
            echo $this->clear_column($row->Activo) . "\t";
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
