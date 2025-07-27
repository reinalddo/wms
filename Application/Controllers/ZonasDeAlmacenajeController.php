<?php

namespace Application\Controllers;

use Framework\View\View;
use Framework\Helpers\Utils;
use Framework\Http\Response;
use Application\Models\Lotes;
use Framework\Http\Controller;
use jakeroid\tools\SimpleXLSX;
use Application\Models\Usuarios;
use Application\Models\ZonasDeAlmacenaje;
use Illuminate\Database\Capsule\Manager as Capsule;


/**
 * @version 1.0.0
 * @category Zonas De Almacenaje
 * @author Brayan Rincon <brayan262@gemail.com>
 */
class ZonasDeAlmacenajeController extends Controller
{
    const CLAVE = 0;
    const ALMACEN = 1;
    const DESCRIPCION = 2;
    const DIRECCION = 3;
    const ACTIVO = 4;

    private $camposRequeridos = [
        self::CLAVE => 'Clave', 
        self::DESCRIPCION => 'Descripción', 
        self::ALMACEN => 'ID Almacén',
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
            if( $eval === TRUE ){
          
            }
            else {
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
            $element = ZonasDeAlmacenaje::where('clave_almacen', $clave)->first();

            if($element != NULL){
                $model = $element; 
            }
            else {
                $model = new ZonasDeAlmacenaje(); 
            }
            
            $model->clave_almacen   = $clave;
            $model->cve_almacenp    = $this->pSQL($row[self::ALMACEN]);
            $model->des_almac       = $this->pSQL($row[self::DESCRIPCION]);
            $model->des_direcc      = $this->pSQL($row[self::DIRECCION]);
            $model->Activo          = $this->pSQL($row[self::ACTIVO]);

            $model->save();
            $linea++;
        }
        
        @unlink($file);

        $this->response(200, [
            'statusText' =>  "Tipos de Caja importados con exito. Total de registros: \"{$linea}\"",
        ]);

    }

    public function validarRequeridosImportar($row)
    {
        foreach ($this->camposRequeridos as $key => $campo){
            if( $row[$key] == '' ){
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
            'clave',
            'almacen_id',
            'descripcion',
            'direccion',
            'Activo',
        ];

        $data_rutas = ZonasDeAlmacenaje::get();
        $filename = "zonas-de-almacenaje-".date('Ymd') . ".xls";
        ob_clean();
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        
        foreach($columnas as $column) {            
            echo $column . "\t" ;            
        }
        print("\r\n");

        foreach($data_rutas as $row)
        {            
            echo $this->clear_column($row->clave_almacen) . "\t";
            echo $this->clear_column($row->cve_almacenp) . "\t";
            echo $this->clear_column($row->des_almac) . "\t";
            echo $this->clear_column($row->des_direcc) . "\t";
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
