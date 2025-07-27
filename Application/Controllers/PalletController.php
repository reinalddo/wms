<?php

namespace Application\Controllers;

use Framework\View\View;
use Framework\Helpers\Utils;
use Framework\Http\Response;
use Framework\Http\Controller;
use jakeroid\tools\SimpleXLSX;
use Application\Models\Usuarios;
use Application\Models\Pallets;
use Illuminate\Database\Capsule\Manager as Capsule;


/**
 * @version 1.0.0
 * @category Pallet y Contenedores
 * @author Brayan Rincon <brayan262@gemail.com>
 */
class PalletController extends Controller
{
    const CLAVE = 0;
    const ALMACEN = 1;
    const DESCRIPCION = 2;
    const CHAROLA = 3;
    const PEDIDO = 4;
    const SUFIJO = 5;
    const TIPO = 6;
    const ALTO = 7;
    const ANCHO = 8;
    const FONDO = 9;
    const PESO = 10;
    const PESOMAX = 11;
    const CAPAVOL = 12;
    const ACTIVO = 13;

    private $camposRequeridos = [
        self::CLAVE => 'Clave', 
        self::ALMACEN => 'Almacén', 
        self::DESCRIPCION => 'Descripción',
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
            $element = Pallets::where('clave_contenedor', $clave)->first();

            if($element != NULL){
                $model = $element; 
            }
            else {
                $model = new Pallets(); 
            }
            
            $model->clave_contenedor    = $clave;
            $model->cve_almac           = $this->pSQL($row[self::ALMACEN]);
            $model->charola             = $this->pSQL($row[self::CHAROLA]);
            $model->Pedido              = $this->pSQL($row[self::PEDIDO]);
            $model->sufijo              = $this->pSQL($row[self::SUFIJO]);
            $model->tipo                = $this->pSQL($row[self::TIPO]);
            $model->Activo              = $this->pSQL($row[self::ACTIVO]);
            $model->alto                = $this->pSQL($row[self::ALTO]);
            $model->ancho               = $this->pSQL($row[self::ANCHO]);
            $model->fondo               = $this->pSQL($row[self::FONDO]);
            $model->peso                = $this->pSQL($row[self::PESO]);
            $model->pesomax             = $this->pSQL($row[self::PESOMAX]);
            $model->capavol             = $this->pSQL($row[self::CAPAVOL]);
            $model->descripcion         = $this->pSQL($row[self::DESCRIPCION]);
            $model->save();
            $linea++;
        }
        
        @unlink($file);

        $this->response(200, [
            'statusText' =>  "Pallets importados con exito. Total de Pallets: \"{$linea}\"",
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

        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

        $utf8Sql = "SET NAMES 'utf8mb4';";
        $res_charset = mysqli_query($conn, $utf8Sql);


    //////////////////////////////////////////////se recibe los parametros POST del grid////////////////////////////////////////////////
    $_criterio   = $_GET['criterio'];
    $almacen     = $_GET['almacen'];
    $tipo_pallet = $_GET['vacio']; //Generico / No Generico
    $tipo        = $_GET['tipo']; //Pallet/Contenedor/Caja
    $status      = $_GET['status'];
    $activo      = $_GET['activo'];
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    $pallet_generico = "";
    $pallet_generico_count = "";
    if($tipo_pallet != 2) //todos
    {
        $pallet_generico = " AND c_charolas.TipoGen = $tipo_pallet ";
        $pallet_generico_count = " AND c.TipoGen = $tipo_pallet ";
    }

        if(intval($page)>0) $_page = ($page-1)*$limit;
        $condicion= '';
    if($_criterio != ''){
    $condicion =" AND (c_charolas.clave_contenedor LIKE '%".$_criterio."%' OR c_charolas.descripcion LIKE '%".$_criterio."%' OR c_charolas.CveLP LIKE '%".$_criterio."%') ";}
    
    $sqlStatus = ""; $sql_activo = "";


    $act = 0;
    if($activo == '3'/* || $activo == '4'*/)
    {
        $act = 1;
    }

    if($activo)
    $sql_activo = " AND c_charolas.Activo = '$act' ";

    if($status != "2")
    {
        if($status == '0') //ocupado
        {
            $sqlStatus = " AND IFNULL(c_charolas.CveLP, '') IN (SELECT Cve_Contenedor FROM V_ExistenciaGralProduccion WHERE Cve_Contenedor != '' AND Existencia > 0) AND IFNULL(tde.ClaveEtiqueta, '') != '' ";
            //$sqlStatus = " AND c_charolas.CveLP != c_charolas.clave_contenedor ";
        }
        else //disponible
        {
            //$act = 1;
            $sqlStatus = " AND IFNULL(c_charolas.CveLP, '') NOT IN (SELECT Cve_Contenedor FROM V_ExistenciaGralProduccion WHERE Cve_Contenedor != '' AND Existencia > 0) AND IFNULL(tde.ClaveEtiqueta, '') = '' ";
            //$sqlStatus = " AND c_charolas.CveLP = c_charolas.clave_contenedor ";

            /*
            if($status == '3' || $status == '4')
            {
                $act = 0;
                if($status == '3')
                    $act = 1;
            }
            $sql_activo = " AND c_charolas.Activo = '$act' ";
            */
        }
    }

    $pallet_generico = "";
    if($tipo_pallet != 2) //todos
    {
        $pallet_generico = " AND c_charolas.TipoGen = $tipo_pallet ";
    }

    $sql_tipo = "";
    if($tipo != 2) //todos
    {
        $sql_tipo = " AND c_charolas.tipo = '$tipo' ";
    }

    $sql = "
                SELECT
                        c_charolas.IDContenedor,
                        #IF((c_charolas.clave_contenedor = V_EntradasContenedores.Clave_Contenedor AND c_charolas.Permanente = 1) OR
                        #   (c_charolas.clave_contenedor = tde.ClaveEtiqueta) OR (c_charolas.CveLP = tde.ClaveEtiqueta)
                        #, 'Ocupado','Libre') AS statu,
                        IF(IFNULL(c_charolas.clave_contenedor, '') NOT IN (SELECT IFNULL(Cve_Contenedor, '') FROM V_ExistenciaGralProduccion WHERE Cve_Contenedor != '')  AND IFNULL(tde.ClaveEtiqueta, '') = '', 'Disponible', 'Ocupado') AS statu,
                        #IF(c_charolas.CveLP = c_charolas.clave_contenedor, 'Disponible', 'Ocupado') AS statu,
                        #IF(IFNULL(tde.Ubicada, 'N') = 'N', 'RTM', u.CodigoCSD) AS BL,
                        IFNULL(IF(IFNULL(tde.Ubicada, 'N') = 'N' AND IFNULL(c_charolas.CveLP, ''), 'RTM', u.CodigoCSD), '') AS BL,
                        c_charolas.descripcion,
                        c_charolas.peso,
                        c_charolas.pesomax,
                        c_charolas.capavol,
                        c_charolas.alto,
                        c_charolas.ancho,
                        c_charolas.fondo,
                        c_charolas.tipo,
                        c_charolas.clave_contenedor,
                        c_charolas.CveLP,
                        IF(c_charolas.Activo = 1, 'ON', 'OFF') as Activo,
                        c_almacenp.nombre as des_almac,
                        c_charolas.cve_almac,
                        IFNULL(c_charolas.TipoGen, 0) AS TipoGenVal
                FROM c_charolas
                        LEFT JOIN c_almacenp ON (c_almacenp.clave = c_charolas.cve_almac OR c_almacenp.id = c_charolas.cve_almac)
                        LEFT JOIN V_ExistenciaGralProduccion ep ON ep.Cve_Contenedor = c_charolas.clave_contenedor AND ep.tipo = 'ubicacion'
                        LEFT JOIN c_ubicacion u ON u.idy_ubica = ep.cve_ubicacion
                        #LEFT JOIN V_EntradasContenedores ON V_EntradasContenedores.Clave_Contenedor = c_charolas.clave_contenedor
                        LEFT JOIN td_entalmacenxtarima tde ON tde.ClaveEtiqueta = c_charolas.clave_contenedor #OR tde.ClaveEtiqueta = c_charolas.CveLP
                WHERE #(c_almacenp.clave = c_charolas.cve_almac OR c_almacenp.id = c_charolas.cve_almac )
                    c_almacenp.id = c_charolas.cve_almac
                    {$sqlStatus}
                    #AND (c_almacenp.id = '$almacen' OR c_almacenp.clave = (SELECT clave FROM c_almacenp WHERE id = '$almacen'))
                    {$sql_activo}
                    AND (c_almacenp.id = '$almacen')
                    #AND c_charolas.clave_contenedor = IFNULL(c_charolas.CveLP, '')
                    ".$condicion.$pallet_generico.$sql_tipo."
                GROUP BY c_charolas.IDContenedor
                ORDER BY c_charolas.IDContenedor DESC
                ";


    if (!($res = mysqli_query($conn, $sql)))
        {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }


        $columnas = [
            'Pallet|Contenedor',
            'License Plate',
            'BL',
            'Descripcion',
            'Status',
            'Tipo',
            'Almacen',
            'Activo'
        ];

        //$data_clientes = Pallets::get();

        $filename =  "Catalogo Pallets".date('Y-m-d') . ".xls";

        ob_clean();
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        
        foreach($columnas as $column) {            
            echo $column . "\t" ;            
        }
        print("\r\n");

        //foreach($data_clientes as $row)
        while($row = mysqli_fetch_object($res))
        {            
            echo $this->clear_column($row->clave_contenedor) . "\t";
            echo $this->clear_column($row->CveLP) . "\t";
            echo $this->clear_column($row->BL) . "\t";
            echo $this->clear_column($row->descripcion) . "\t";
            echo $this->clear_column($row->statu) . "\t";
            echo $this->clear_column($row->tipo) . "\t";
            echo $this->clear_column($row->des_almac) . "\t";
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
