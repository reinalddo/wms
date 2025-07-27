<?php

namespace Application\Controllers;

use Framework\View\View;
use Framework\Helpers\Utils;
use Framework\Http\Response;
use Framework\Http\Controller;
use jakeroid\tools\SimpleXLSX;
use Application\Models\Usuarios;
use Application\Models\Crossdocking;
use Application\Models\CrossdockingDetalles;
use Illuminate\Database\Capsule\Manager as Capsule;


/**
 * @version 1.0.0
 * @category Crossdocking
 * @author Brayan Rincon <brayan262@gemail.com>
 */
class CrossdockingController extends Controller
{
    const FOL_PEDIDOCON = 0;
    const NO_ORDCOMP = 1;
    const CODB_PROV = 2;
    const NIT_PROV = 3;
    const NOM_PROV = 4;
    const CVE_CTECON = 5;
    const CODB_CTECON = 6;
    const NOM_CTECON = 7;
    const DIR_CTECON = 8;
    const CD_CTECON = 9;
    const NIT_CTECON = 10;
    const COD_CTECON = 11;
    const CODB_CTEENV = 12;
    const NOM_CTEENV = 13;
    const DIR_CTEENV = 14;
    const CD_CTEENV = 15;
    const TEL_CTEENV = 16;
    const FEC_ENTREGA = 17;
    const TOT_CAJAS = 17;
    const TOT_PZS = 19;
    const PLACA_TRANS = 20;
    const SELLOS = 21;
    const STATUS =22;
    const ACTIVO = 23;


    const FOL_PEDIDOCON_DETALLE = 0;
    const NO_ORDCOMP_DETALLE = 1;
    const FEC_ORDCOM_DETALLE = 2;
    const CVE_ARTICULO_DETALLE = 3;
    const CANT_PEDIDA_DETALLE = 4;
    const UNID_EMPAQUE_DETALLE =5;
    const TOT_CAJAS_DETALLE = 6;
    const STATUS_DETALLE = 7;
    const FACT_MADRE_DETALLE = 8;
    const CVE_CLTE_DETALLE = 9;
    const CVE_CTEPROV_DETALLE = 10;
    const FOL_FOLIO_DETALLE = 11;
    const CODB_CTE_DETALLE = 12;
    const COD_PV_DETALLE = 13;

    private $camposRequeridos = [
        self::FOL_PEDIDOCON => 'Folio', 
        self::NO_ORDCOMP => 'Nro. Orden de Compra',
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
        $file_cabecera = $dir_cache . basename($_FILES['cabecera']['name']);
        $file_detalle = $dir_cache . basename($_FILES['detalle']['name']);

        if (! move_uploaded_file($_FILES['cabecera']['tmp_name'], $file_cabecera)) {
            $this->response(400, [
                'statusText' =>  "Error al recibir el fichero, verifique que se tenga permisos para escribir en Cache",
            ]);
        }

        if (! move_uploaded_file($_FILES['detalle']['tmp_name'], $file_detalle)) {
            $this->response(400, [
                'statusText' =>  "Error al recibir el fichero, verifique que se tenga permisos para escribir en Cache",
            ]);
        }


        if ( $_FILES['cabecera']['type'] != 'application/vnd.ms-excel' AND
                $_FILES['cabecera']['type'] != 'application/msexcel' AND
                $_FILES['cabecera']['type'] != 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' AND
                $_FILES['cabecera']['type'] != 'application/xls' )
            {
            @unlink($file_cabecera);
            $this->response(400, [
                'statusText' =>  "Error en el formato del fichero",
            ]);
        }

        $xlsx = new SimpleXLSX( $file_cabecera );
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

        ########################################## Cabecera ##############################################
        
        $linea = 1;
        foreach ($xlsx->rows() as $row)
        {
            if($linea == 1) {
                $linea++;continue;
            }
            $Fol_PedidoCon = $this->pSQL($row[self::FOL_PEDIDOCON]);
            $No_OrdComp = $this->pSQL($row[self::NO_ORDCOMP]);

            $element = Crossdocking::where('Fol_PedidoCon', $Fol_PedidoCon)->where('No_OrdComp', $No_OrdComp)->first();

            if($element != NULL){
                $model = $element; 
            }
            else {
                $model = new Crossdocking(); 
            }


            $model->Fol_PedidoCon    = $this->pSQL($row[self::FOL_PEDIDOCON]);
            $model->No_OrdComp       = $this->pSQL($row[self::NO_ORDCOMP]);
            $model->CodB_Prov        = $this->pSQL($row[self::CODB_PROV]);
            $model->NIT_Prov         = $this->pSQL($row[self::NIT_PROV]);
            $model->Nom_Prov         = $this->pSQL($row[self::NOM_PROV]);
            $model->Cve_CteCon       = $this->pSQL($row[self::CVE_CTECON]);
            $model->CodB_CteCon      = $this->pSQL($row[self::CODB_CTECON]);
            $model->Nom_CteCon       = $this->pSQL($row[self::NOM_CTECON]);
            $model->Dir_CteCon       = $this->pSQL($row[self::DIR_CTECON]);
            $model->Cd_CteCon        = $this->pSQL($row[self::CD_CTECON]);
            $model->NIT_CteCon       = $this->pSQL($row[self::NIT_CTECON]);
            $model->Cod_CteCon       = $this->pSQL($row[self::COD_CTECON]);
            $model->CodB_CteEnv      = $this->pSQL($row[self::CODB_CTEENV]);
            $model->Nom_CteEnv       = $this->pSQL($row[self::NOM_CTEENV]);
            $model->Dir_CteEnv       = $this->pSQL($row[self::DIR_CTEENV]);
            $model->Cd_CteEnv        = $this->pSQL($row[self::CD_CTEENV]);
            $model->Tel_CteEnv       = $this->pSQL($row[self::TEL_CTEENV]);
            $model->Fec_Entrega      = $this->pSQL($row[self::FEC_ENTREGA]);
            $model->Tot_Cajas        = $this->pSQL($row[self::TOT_CAJAS]);
            $model->Tot_Pzs          = $this->pSQL($row[self::TOT_PZS]);
            $model->Placa_Trans      = $this->pSQL($row[self::PLACA_TRANS]);
            $model->Sellos           = $this->pSQL($row[self::SELLOS]);
            $model->Status           = $this->pSQL($row[self::STATUS]);
            $model->Activo           = $this->pSQL($row[self::ACTIVO]);
            $model->save();
            $linea++;
        }



        ############################################## Detalle ##########################################
        $xlsx = new SimpleXLSX( $file_detalle );
        $linea = 1;
        foreach ($xlsx->rows() as $row)
        {
            if($linea == 1) {
                $linea++;continue;
            }

            $Fol_PedidoCon = $this->pSQL($row[self::FOL_PEDIDOCON_DETALLE]);
            $No_OrdComp = $this->pSQL($row[self::NO_ORDCOMP_DETALLE]);

            $element = CrossdockingDetalles::where('Fol_PedidoCon', $Fol_PedidoCon)->where('No_OrdComp', $No_OrdComp)->first();

            if($element != NULL){
                $model = $element; 
            }
            else {
                $model = new CrossdockingDetalles(); 
            }
            
            $model->Fol_PedidoCon   = $this->pSQL($row[self::FOL_PEDIDOCON_DETALLE]);
            $model->No_OrdComp      = $this->pSQL($row[self::NO_ORDCOMP_DETALLE]);
            $model->Fec_OrdCom      = $this->pSQL($row[self::FEC_ORDCOM_DETALLE]);
            $model->Cve_Articulo    = $this->pSQL($row[self::CVE_ARTICULO_DETALLE]);
            $model->CANT_PEDIDA     = $this->pSQL($row[self::CANT_PEDIDA_DETALLE]);
            $model->Unid_Empaque    = $this->pSQL($row[self::UNID_EMPAQUE_DETALLE]);
            $model->Tot_Cajas       = $this->pSQL($row[self::TOT_CAJAS_DETALLE]);
            $model->Status          = $this->pSQL($row[self::STATUS_DETALLE]);
            $model->Fact_Madre      = $this->pSQL($row[self::FACT_MADRE_DETALLE]);
            $model->Cve_Clte        = $this->pSQL($row[self::CVE_CLTE_DETALLE]);
            $model->Cve_CteProv     = $this->pSQL($row[self::CVE_CTEPROV_DETALLE]);
            $model->Fol_Folio       = $this->pSQL($row[self::FOL_FOLIO_DETALLE]);
            $model->CodB_Cte        = $this->pSQL($row[self::CODB_CTE_DETALLE]);
            $model->Cod_PV          = $this->pSQL($row[self::COD_PV_DETALLE]);
            $model->save();
            $linea++;
        }

        $linea--;
        $this->response(200, [
            'statusText' =>  "Crossdocking importados con exito. Total de Crossdocking: \"{$linea}\"",
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
    public function exportarCabecera()
    {
        $columnas = [
            'fol_pedidocon',
            'no_ordcomp',
            'codb_prov',
            'nit_prov',
            'nom_prov',
            'cve_ctecon',
            'codb_ctecon',
            'nom_ctecon',
            'dir_ctecon',
            'cd_ctecon',
            'nit_ctecon',
            'cod_ctecon',
            'codb_cteenv',
            'nom_cteenv',
            'dir_cteenv',
            'cd_cteenv',
            'tel_cteenv',
            'fec_entrega',
            'tot_cajas',
            'tot_pzs',
            'placa_trans',
            'sellos',
            'status',
            'activo',
        ];

        $data_clientes = Crossdocking::get();

        $filename = "destinatarios_".date('Ymd') . ".xls";

        ob_clean();
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        
        foreach($columnas as $column) {            
            echo $column . "\t" ;            
        }
        print("\r\n");

        foreach($data_clientes as $row)
        {            
            echo $this->clear_column($row->CodB_Prov) . "\t";
            echo $this->clear_column($row->NIT_Prov) . "\t";
            echo $this->clear_column($row->Nom_Prov) . "\t";
            echo $this->clear_column($row->Cve_CteCon) . "\t";
            echo $this->clear_column($row->CodB_CteCon) . "\t";
            echo $this->clear_column($row->Nom_CteCon) . "\t";
            echo $this->clear_column($row->Dir_CteCon) . "\t";
            echo $this->clear_column($row->Cd_CteCon) . "\t";
            echo $this->clear_column($row->NIT_CteCon) . "\t";
            echo $this->clear_column($row->CodB_CteEnv) . "\t";
            echo $this->clear_column($row->Nom_CteEnv) . "\t";
            echo $this->clear_column($row->Dir_CteEnv) . "\t";
            echo $this->clear_column($row->Cd_CteEnv) . "\t";
            echo $this->clear_column($row->Tel_CteEnv) . "\t";
            echo $this->clear_column($row->Fec_Entrega) . "\t";
            echo $this->clear_column($row->Tot_Cajas) . "\t";
            echo $this->clear_column($row->Tot_Pzs) . "\t";
            echo $this->clear_column($row->Placa_Trans) . "\t";
            echo  "\r\n";
        }

        exit;
        
    }


        /**
     * Undocumented function
     *
     * @return void
     */
    public function exportarDetalles($folio = '')
    {
        $columnas = [
            'Fol_PedidoCon',
            'No_OrdComp',
            'Fec_OrdCom',
            'Cve_Articulo',
            'Cant_Pedida',
            'Unid_Empaque',
            'Tot_Cajas',
            'Status',
            'Fact_Madre',
            'Cve_Clte',
            'Cve_CteProv',
            'Fol_Folio',
            'CodB_Cte',
            'Cod_PV',
        ];

        if( $folio == '' ) {
            $data_model = CrossdockingDetalles::get();
        } else {
            $data_model = CrossdockingDetalles::where('Fol_Folio', $folio)->get();
        }

        $filename = "crossdocking-detalles_".date('Ymd') . ".xls";

        ob_clean();
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        
        foreach($columnas as $column) {            
            echo $column . "\t" ;            
        }
        print("\r\n");

        foreach($data_model as $row)
        {            
            echo $this->clear_column($row->Fol_PedidoCon) . "\t";
            echo $this->clear_column($row->No_OrdComp) . "\t";
            echo $this->clear_column($row->Fec_OrdCom) . "\t";
            echo $this->clear_column($row->Cve_Articulo) . "\t";
            echo $this->clear_column($row->Cant_Pedida) . "\t";
            echo $this->clear_column($row->Unid_Empaque) . "\t";
            echo $this->clear_column($row->Tot_Cajas) . "\t";
            echo $this->clear_column($row->Status) . "\t";
            echo $this->clear_column($row->Fact_Madre) . "\t";
            echo $this->clear_column($row->Cve_Clte) . "\t";
            echo $this->clear_column($row->Nom_CteEnv) . "\t";
            echo $this->clear_column($row->Cve_CteProv) . "\t";
            echo $this->clear_column($row->Fol_Folio) . "\t";
            echo $this->clear_column($row->CodB_Cte) . "\t";
            echo $this->clear_column($row->Cod_PV) . "\t";
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
