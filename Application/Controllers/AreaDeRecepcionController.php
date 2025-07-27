<?php

namespace Application\Controllers;

use Framework\View\View;
use Framework\Helpers\Utils;
use Framework\Http\Response;
use Framework\Http\Controller;
use jakeroid\tools\SimpleXLSX;
use Application\Models\Usuarios;
use Application\Models\AreaDeRecepcion;
use Illuminate\Database\Capsule\Manager as Capsule;


/**
 * @version 1.0.0
 * @category Areas de Recepción
 * @author Brayan Rincon <brayan262@gemail.com>
 */
class AreaDeRecepcionController extends Controller
{
    const UBICACION = 0;
    const ALMACEN = 1;
    const DESCRIPCION = 2;
    const ACTIVO =3;


    private $camposRequeridos = [
        self::UBICACION => 'Ubicación', 
        self::DESCRIPCION => 'Descripción', 
        self::ALMACEN => 'Almacén',
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

            $almacen = $this->pSQL($row[self::ALMACEN]);
            $ubicacion = $this->pSQL($row[self::UBICACION]);
            $element = AreaDeRecepcion::where('cve_almacp', $almacen)->where('cve_ubicacion', $ubicacion)->first();

            if($element != NULL){
                $model = $element; 
            }
            else {
                $model = new AreaDeRecepcion(); 
            }

            $model->cve_ubicacion   = $ubicacion;
            $model->cve_almacp      = $almacen;
            $model->desc_ubicacion  = $this->pSQL($row[self::DESCRIPCION]);
            $model->Activo          = $this->pSQL($row[self::ACTIVO]);
            $model->save();
            $linea++;
        }
        
        @unlink($file);

        $this->response(200, [
            'statusText' =>  "AreaDeRecepcion importados con exito. Total de AreaDeRecepcion: \"{$linea}\"",
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
            'ubicacion',
            'almacen',
            'descripcion',
            'activo',
        ];

        $data_clientes = AreaDeRecepcion::get();

        $filename = "areas-de-revision_".date('Ymd') . ".xls";

        ob_clean();
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        
        foreach($columnas as $column) {            
            echo $column . "\t" ;            
        }
        print("\r\n");

        foreach($data_clientes as $row)
        {            
            echo $this->clear_column($row->cve_ubicacion) . "\t";
            echo $this->clear_column($row->cve_almacp) . "\t";
            echo $this->clear_column($row->desc_ubicacion) . "\t";
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
