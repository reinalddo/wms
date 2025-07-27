<?php

namespace Application\Controllers;

use Framework\View\View;
use Framework\Helpers\Utils;
use Framework\Http\Response;
use Framework\Http\Controller;
use jakeroid\tools\SimpleXLSX;
use Application\Models\TipoDeTransporte;
use Illuminate\Database\Capsule\Manager as Capsule;


/**
 * @version 1.0.0
 * @category Tipo de transportes
 * @author Brayan Rincon <brayan262@gemail.com>
 */
class TipoDeTransporteController extends Controller
{
    const CLAVE = 0;
    const DESCRIPCION = 1;    
    const ALTO = 2;
    const ANCHO = 3;
    const FONDO = 4;
    const PESO = 5;

    private $camposRequeridos = [
        self::CLAVE => 'Clave', 
        self::DESCRIPCION => 'Descripción',         
        self::ALTO => 'Alto',
        self::ANCHO => 'Ancho',
        self::FONDO => 'Fondo',
        self::PESO => 'Capacidad',
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
            $element = TipoDeTransporte::where('clave_ttransporte', $clave)->first();

            if($element != NULL){
                $model = $element; 
            }
            else {
                $model = new TipoDeTransporte(); 
            }
            
            $model->clave_ttransporte       = $clave;
            $model->alto            = $this->pSQL($row[self::ALTO]);
            $model->fondo           = $this->pSQL($row[self::FONDO]);
            $model->ancho           = $this->pSQL($row[self::ANCHO]);
            $model->capacidad_carga = $this->pSQL($row[self::PESO]);
            $model->desc_ttransporte = $this->pSQL($row[self::DESCRIPCION]);
            $model->Activo          = 1;

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
            'alto',
            'fondo',
            'ancho',
            'capacidad_carga',
            'activo'
            
        ];

        $data_rutas = TipoDeTransporte::get([
            'clave_ttransporte',
            'desc_ttransporte',
            'alto',
            'fondo',
            'ancho',
            'capacidad_carga',
            'Activo'
        ]);

        $filename = "tipo-de-transporte_".date('Ymd') . ".xls";

        ob_clean();
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        
        foreach($columnas as $column) {            
            echo $column . "\t" ;            
        }
        print("\r\n");

        foreach($data_rutas as $row)
        {            
            echo $this->clear_column($row->clave_ttransporte) . "\t";
            echo $this->clear_column($row->alto) . "\t";
            echo $this->clear_column($row->fondo) . "\t";
            echo $this->clear_column($row->ancho) . "\t";
            echo $this->clear_column($row->capacidad_carga) . "\t";
            echo $this->clear_column($row->desc_ttransporte) . "\t";
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
