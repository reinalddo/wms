<?php

namespace Application\Controllers;

use Framework\View\View;
use Framework\Helpers\Utils;
use Framework\Http\Response;
use Framework\Http\Controller;
use jakeroid\tools\SimpleXLSX;
use Application\Models\Usuarios;
use Application\Models\Lotes;
use Illuminate\Database\Capsule\Manager as Capsule;


/**
 * @version 1.0.0
 * @category Usuarios
 * @author Brayan Rincon <brayan262@gemail.com>
 */
class LotesController extends Controller
{
    const ARTICULO = 0;
    const LOTE = 1;
    const CADUCIDAD = 2;
/*
    private $camposRequeridos = [
        self::CLAVE => 'Clave', 
        self::ARTICULO => 'Artículo', 
        self::CADUCIDAD => 'Caducidad',
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
/*
        foreach ($xlsx->rows() as $row)
        {
            if($linea == 1) {
                $linea++;continue;
            }

            $eval = $this->validarRequeridosImportar($row);
            if( $eval === TRUE ){
                $row[self::CLAVE];
                $row[self::ARTICULO];
                $row[self::CADUCIDAD];
            }
            else {
                $this->response(400, [
                    'statusText' =>  "La columna \"{$eval}\" de la Fila Nro. {$linea} está vacía. Corrija esto para continuar con la importación.",
                ]);
            }
            $linea++;
        }
*/
        $linea = 1;$registros = 0;
        foreach ($xlsx->rows() as $row)
        {
            if($linea == 1) {
                $linea++;continue;
            }
            $lote = $this->pSQL($row[self::LOTE]);
            $articulo = $this->pSQL($row[self::ARTICULO]);
            $element = Lotes::where('LOTE', '=', $lote)->where('cve_articulo','=',$articulo)->first();

            if($element != NULL){
                $model = $element; 
            }
            else {
                $model = new Lotes(); 
            }
            
            $model->LOTE            = $lote;
            $model->cve_articulo    = $articulo;
            if($this->pSQL($row[self::CADUCIDAD]))
            $model->CADUCIDAD       = $this->pSQL($row[self::CADUCIDAD]);
            $model->save();
            $linea++;$registros++;
        }

        @unlink($file);

        $this->response(200, [
            'statusText' =>  "Lotes importados con exito. Total de Lotes: \"{$registros}\"",
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
            'clave_articulo',
            'lote',
            'caducidad',
            'activo',
        ];

        $data_clientes = Lotes::get([
            'cve_articulo',
            'LOTE',
            'CADUCIDAD',
            'Activo',
        ]);

        $filename = "lotes_".date('Ymd') . ".xls";

        ob_clean();
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        
        foreach($columnas as $column) {            
            echo $column . "\t" ;            
        }
        print("\r\n");

        foreach($data_clientes as $row)
        {            
            echo $this->clear_column($row->cve_articulo) . "\t";
            echo $this->clear_column($row->LOTE) . "\t";
            echo $this->clear_column($row->CADUCIDAD) . "\t";
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
