<?php

namespace Application\Controllers;

use Framework\View\View;
use Framework\Helpers\Utils;
use Framework\Http\Response;
use Framework\Http\Controller;
use jakeroid\tools\SimpleXLSX;
use Application\Models\Usuarios;
use Application\Models\AreaDeEmbarque;
use Illuminate\Database\Capsule\Manager as Capsule;


/**
 * @version 1.0.0
 * @category Areas de Embarque
 * @author Brayan Rincon <brayan262@gemail.com>
 */
class AreaDeEmbarqueController extends Controller
{
    const UBICACION = 0;
    const ALMACEN = 1;
    const DESCRIPCION = 2;
    const STATUS = 3;
    const STAGGING = 4;
    const LARGO = 5;
    const ANCHO = 6;
    const ALTO = 7;
    const ACTIVO =8;

    private $camposRequeridos = [
        self::UBICACION => 'Ubicación', 
        self::ALMACEN => 'Almacén', 
        self::DESCRIPCION => 'Descripción',
        self::STATUS => 'Estatus',
        self::STAGGING => 'Stagging',
        self::LARGO => 'Largo',
        self::ANCHO => 'Ancho',
        self::ALTO => 'Alto',
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
            $element = AreaDeEmbarque::where('cve_almac', $almacen)->where('cve_ubicacion', $ubicacion)->first();

            if($element != NULL){
                $model = $element; 
            }
            else {
                $model = new AreaDeEmbarque(); 
            }

            $model->cve_ubicacion   = $ubicacion;
            $model->cve_almac       = $almacen;
            $model->status          = $this->pSQL($row[self::STATUS]);
            $model->Activo          = $this->pSQL($row[self::ACTIVO]);
            $model->DESCRIPCION     = $this->pSQL($row[self::DESCRIPCION]);
            $model->AreaStagging    = $this->pSQL($row[self::STAGGING]);
            $model->largo           = $this->pSQL($row[self::LARGO]);
            $model->ancho           = $this->pSQL($row[self::ANCHO]);
            $model->alto            = $this->pSQL($row[self::ALTO]);
            $model->save();
            $linea++;
        }
        
        @unlink($file);

        $this->response(200, [
            'statusText' =>  "AreaDeEmbarque importados con exito. Total de AreaDeEmbarque: \"{$linea}\"",
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
            'status',
            'AreaStagging',
            'largo',
            'ancho',
            'alto',
            'Activo',
        ];

        $data_clientes = AreaDeEmbarque::get();

        $filename = "areas-de-embarque_".date('Ymd') . ".xls";

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
            echo $this->clear_column($row->cve_almac) . "\t";
            echo $this->clear_column($row->descripcion) . "\t";
            echo $this->clear_column($row->status) . "\t";
            echo $this->clear_column($row->AreaStagging) . "\t";
            echo $this->clear_column($row->largo) . "\t";
            echo $this->clear_column($row->ancho) . "\t";
            echo $this->clear_column($row->alto) . "\t";
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
