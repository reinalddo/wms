<?php

namespace Application\Controllers;

use Framework\View\View;
use Framework\Helpers\Utils;
use Framework\Http\Response;
use Application\Models\Lotes;
use Framework\Http\Controller;
use jakeroid\tools\SimpleXLSX;
use Application\Models\Usuarios;
use Application\Models\TipoDeCaja;
use Illuminate\Database\Capsule\Manager as Capsule;


/**
 * @version 1.0.0
 * @category Usuarios
 * @author Brayan Rincon <brayan262@gemail.com>
 */
class TipoDeCajasController extends Controller
{
    const CLAVE = 0;
    const DESCRIPCION = 1;
    const PESO = 2;
    const ALTO = 3;
    const ANCHO = 4;
    const LARGO = 5;
    const PACKING = 6;

    private $camposRequeridos = [
        self::CLAVE => 'Clave', 
        self::DESCRIPCION => 'Descripción', 
        self::PESO => 'Peso',
        self::ALTO => 'Alto',
        self::ANCHO => 'Ancho',
        self::LARGO => 'Largo',
        self::PACKING => 'Packing',
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
            $element = TipoDeCaja::where('clave', $clave)->first();

            if($element != NULL){
                $model = $element; 
            }
            else {
                $model = new TipoDeCaja(); 
            }
            
            $packing = $this->pSQL($row[self::PACKING]);
            //$packing = $packing == '1' ? $packing = 'S' : $packing = 'N';

            $model->clave       = $clave;
            $model->descripcion = $this->pSQL($row[self::DESCRIPCION]);
            $model->peso        = $this->pSQL($row[self::PESO]);
            $model->largo       = $this->pSQL($row[self::LARGO]);
            $model->alto        = $this->pSQL($row[self::ALTO]);
            $model->ancho       = $this->pSQL($row[self::ANCHO]);
            $model->Activo      = 1;
            $model->Packing     = $packing  ;

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
            'descripcion',
            'peso',
            'largo',
            'alto',
            'ancho',
            'Packing',
            'Activo',
        ];

        $data_rutas = TipoDeCaja::get();
        $filename = "tipos-de-cajas-".date('Ymd') . ".xls";
        ob_clean();
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        
        foreach($columnas as $column) {            
            echo $column . "\t" ;            
        }
        print("\r\n");

        foreach($data_rutas as $row)
        {            
            echo $this->clear_column($row->clave) . "\t";
            echo $this->clear_column($row->descripcion) . "\t";
            echo $this->clear_column($row->peso) . "\t";
            echo $this->clear_column($row->largo) . "\t";
            echo $this->clear_column($row->alto) . "\t";
            echo $this->clear_column($row->ancho) . "\t";
            echo $this->clear_column($row->Packing) . "\t";
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
