<?php

namespace Application\Controllers;

use Framework\View\View;
use Framework\Helpers\Utils;
use Framework\Http\Response;
use Framework\Http\Controller;
use jakeroid\tools\SimpleXLSX;
use Application\Models\Usuarios;
use Application\Models\Protocolos;
use Illuminate\Database\Capsule\Manager as Capsule;


/**
 * @version 1.0.0
 * @category Protocolos
 * @author Brayan Rincon <brayan262@gemail.com>
 */
class ProtocolosController extends Controller
{
    const ID_PROTOCOLO = 0;
    const DESCRIPCION = 1;
    const FOLIO = 2;
    const ACTIVO = 3;

    private $camposRequeridos = [
        self::ID_PROTOCOLO => 'ID Protocolo',
        self::DESCRIPCION => 'Descripción',
        self::FOLIO => 'Almacén', 
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
            $clave = $this->pSQL($row[self::ID_PROTOCOLO]);
            $element = Protocolos::where('ID_Protocolo', $clave)->first();

            if($element != NULL){
                $model = $element; 
            }
            else {
                $model = new Protocolos(); 
            }
            
            $model->ID_Protocolo        = $clave;
            $model->descripcion         = $this->pSQL($row[self::DESCRIPCION]);
            $model->FOLIO               = $this->pSQL($row[self::FOLIO]);
            $model->Activo              = $this->pSQL($row[self::ACTIVO]);
            $model->save();
            $linea++;
        }
        
        @unlink($file);

        $this->response(200, [
            'statusText' =>  "Protocolos importados con exito. Total de Protocolos: \"{$linea}\"",
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
            'clave',
            'descripcion',
            'folio',
            'activo',
        ];

        $data_clientes = Protocolos::get();

        $filename =  "protocolos_".date('Ymd') . ".xls";

        ob_clean();
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        
        foreach($columnas as $column) {            
            echo $column . "\t" ;            
        }
        print("\r\n");

        foreach($data_clientes as $row)
        {            
            echo $this->clear_column($row->ID_Protocolo) . "\t";
            echo $this->clear_column($row->descripcion) . "\t";
            echo $this->clear_column($row->FOLIO) . "\t";
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
