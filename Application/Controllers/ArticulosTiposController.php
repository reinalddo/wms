<?php

namespace Application\Controllers;

use Framework\View\View;
use Framework\Helpers\Utils;
use Framework\Http\Response;
use Framework\Http\Controller;
use jakeroid\tools\SimpleXLSX;
use Application\Models\ArticulosTipos;
use Illuminate\Database\Capsule\Manager as Capsule;


/**
 * @version 1.0.0
 * @category Usuarios
 * @author Brayan Rincon <brayan262@gemail.com>
 */
class ArticulosTiposController extends Controller
{
    const CLAVE = 0;
    const DESCRIPCION = 1;
    //const GRUPO = 2;

    private $camposRequeridos = [
        self::CLAVE => 'Clave', 
        self::DESCRIPCION => 'Descripción',
        //self::GRUPO => 'Grupo',
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

        $id_almacen = $_POST['id_almacen'];

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
        $registros = 0;
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
            $element = ArticulosTipos::where('cve_ssgpoart', '=', $clave)->first();

            if($element != NULL){
                $model = $element; 
            }
            else {
                $model = new ArticulosTipos(); 
            }
            
            $model->cve_ssgpoart    = $clave;
            $model->des_ssgpoart    = $this->pSQL($row[self::DESCRIPCION]);
            $model->id_almacen      = $id_almacen;
            //$model->cve_sgpoart    = $this->pSQL($row[self::GRUPO]);
            $model->save();
            $registros++;
            $linea++;
        }
        
        @unlink($file);

        $this->response(200, [
            'statusText' =>  "Tipo de artículos importados con exito. Total: \"{$linea}\"",
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
            'cve_ssgpoart',
            'cve_sgpoart',
            'des_ssgpoart',
            'Opcinal',
            'activo',
        ];

        $data_rutas = ArticulosTipos::get();
        $filename = "tipos-de-articulos_".date('Ymd') . ".xls";
        ob_clean();
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        
        foreach($columnas as $column) {            
            echo $column . "\t" ;            
        }
        print("\r\n");

        foreach($data_rutas as $row)
        {            
            echo $this->clear_column($row->cve_ssgpoart) . "\t";
            echo $this->clear_column($row->cve_sgpoart) . "\t";
            echo $this->clear_column($row->des_ssgpoart) . "\t";
            echo $this->clear_column($row->Opcinal) . "\t";
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
