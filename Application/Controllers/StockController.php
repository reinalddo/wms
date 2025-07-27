<?php

namespace Application\Controllers;

use Framework\View\View;
use Framework\Helpers\Utils;
use Framework\Http\Response;
use Application\Models\Lotes;
use Application\Models\Series;
use Framework\Http\Controller;
use jakeroid\tools\SimpleXLSX;
use Application\Models\AlmacenP;
use Application\Models\Usuarios;
use Application\Models\Articulos;
use Application\Models\Ubicaciones;
use Application\Models\ArticulosGrupos;
use Application\Models\ExistenciaPiezas;
use Application\Models\ExistenciaTarima;
use Application\Models\Pallets;
use Application\Models\Proveedores;
use Illuminate\Database\Capsule\Manager as Capsule;


/**
 * @SWG\Info(title="My First API", version="0.1")
 */

/**
 * @SWG\Get(
 *     path="/api/resource.json",
 *     @SWG\Response(response="200", description="An example resource")
 * )
 */

/**
 * @version 1.0.0
 * @category Stock
 * @author Brayan Rincon <brayan262@gemail.com>
 */
class StockController extends Controller
{
    const ALMACEN   = 0;
    const BL        = 1;
    const CLAVE_ART = 2;
    const LOTE      = 3;
    const CADUCIDAD = 4;
    const CANTIDAD  = 5;
    const PROVEEDOR = 6;
    const CODIGO_LP = 7;
    const QA        = 8;
    const MOTIVO_QA = 9;

private $camposRequeridos = [
        self::CLAVE_ART => 'Clave', 
        //self::ALMACEN => 'Almacén',
        //self::CANTIDAD => 'Cantidad',
        //self::CADUCIDAD => 'Caducidad',
        //self::LOTE => 'Lote',
        //self::SERIE => 'Serie',
    ];

    public function isDate($value) 
    {
        if (!$value) {
            return 0;
        }

        try {
            new \DateTime($value);
            return 1;
        } catch (\Exception $e) {
            return 0;
        }
    }

    public function importar()
    {
      $errores = [];
      $dir_cache = PATH_APP . 'Cache/';
      $file = $dir_cache . basename($_FILES['file']['name']);

      if (! move_uploaded_file($_FILES['file']['tmp_name'], $file)) 
      {
        $this->response(400, ['statusText' =>  "Error al recibir el fichero, verifique que se tenga permisos para escribir en Cache",]);
      }

      if ( $_FILES['file']['type'] != 'application/vnd.ms-excel' AND
      $_FILES['file']['type'] != 'application/msexcel' AND
      $_FILES['file']['type'] != 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' AND
      $_FILES['file']['type'] != 'application/xls' )
      {
        @unlink($file);
        $this->response(400, ['statusText' =>  "Error en el formato del fichero",]);
      }

      $xlsx = new SimpleXLSX( $file );

      $linea = 1;
      $correctos = 0; $lotes_no_validos = 0;
      $articulos_no_existentes = "";
      foreach ($xlsx->rows() as $row)
      {
        if($linea == 1) 
        {
          $linea++;continue;
        }
        $row[self::CLAVE_ART] = str_replace(" ", "", $row[self::CLAVE_ART]);
        $data_articulo = Articulos::where('cve_articulo',$row[self::CLAVE_ART])->get(['control_lotes', 'control_numero_series', 'Caduca'])->first();
        if( $data_articulo == NULL )
        {
          $articulos_no_existentes .= $row[self::CLAVE_ART]." , ";
          $linea++;continue;
        }
        // Verificar existencia del Lote
        if( $data_articulo->control_lotes == 'S' && $row[self::LOTE])
        {
          $data_lote = Lotes::where('Lote', $row[self::LOTE])
          ->where('cve_articulo', $row[self::CLAVE_ART])
          ->get(['Lote'])
          ->first();
          if( $data_lote == NULL)
          {
            $model = new Lotes(); 

              $excel_date = $row[self::CADUCIDAD];
              $unix_date = ($excel_date - 25569) * 86400;
              $excel_date = 25569 + ($unix_date / 86400);
              $unix_date = ($excel_date - 25569) * 86400;
              $lote_fecha = gmdate("Y-m-d", $unix_date);

//              if(!$this->isDate($lote_fecha))
//              {
                  $model->LOTE            = $this->pSQL($row[self::LOTE]);
                  $model->cve_articulo    = $this->pSQL($row[self::CLAVE_ART]);

                  if($data_articulo->Caduca == 'S' && $row[self::CADUCIDAD])
                  {
                    $excel_date = $row[self::CADUCIDAD];
                    $unix_date = ($excel_date - 25569) * 86400;
                    $excel_date = 25569 + ($unix_date / 86400);
                    $unix_date = ($excel_date - 25569) * 86400;
                    $caducidad = gmdate("Y-m-d", $unix_date);

                    $model->CADUCIDAD       = $caducidad;
                  }
                  $model->Activo          = 1;
                  $model->save();
//              }
//              else 
//                $lotes_no_validos++;
          }
        }
        // Verificar existencia en la SERIE
        if( $data_articulo->control_numero_series == 'S' )
        {
          $data_serie = Series::where('cve_articulo', $row[self::CLAVE_ART])
          ->get(['cve_articulo'])
          ->first();

          if( $data_serie == NULL)
          {
            $model = new Series(); 
            $model->cve_articulo  = $this->pSQL($row[self::CLAVE_ART]);
            $model->numero_serie    = $this->pSQL($row[self::LOTE]);
            $model->fecha_ingreso   = $this->pSQL(date('Y-m-d'));
            $model->save();
          }
        }

        if($data_articulo->Caduca != 'S' && $row[self::CADUCIDAD])
        {
              $excel_date = $row[self::CADUCIDAD];
              $unix_date = ($excel_date - 25569) * 86400;
              $excel_date = 25569 + ($unix_date / 86400);
              $unix_date = ($excel_date - 25569) * 86400;
              $fecha = gmdate("Y-m-d", $unix_date);
              $articulo = $this->pSQL($row[self::CLAVE_ART]);
              $lote = $this->pSQL($row[self::LOTE]);
            $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
            $sql = "UPDATE td_entalmacen SET fecha_fin='$fecha' WHERE cve_articulo = '$articulo' AND cve_lote = '$lote'";
            $rs = mysqli_query($conn, $sql);
        }

        // Verificar existencia previa
        $data_ubicacion = Ubicaciones::where('CodigoCSD', $row[self::BL])->get(['idy_ubica'])->first();
        $ubicacion = $data_ubicacion->idy_ubica;

        if( $ubicacion == NULL )
        {
          $linea++;continue;
        }
        // Obtener el ID del almacén
        $data_almacenp = AlmacenP::where('clave',$row[self::ALMACEN])->get(['id'])->first();
        $almacen = $data_almacenp->id;
        
        if($almacen == NULL)
        {
          $linea++;continue;
        }
        $articulo = $this->pSQL($row[self::CLAVE_ART]);
        $lote = ($this->pSQL($row[self::LOTE]) == null)?'':$this->pSQL($row[self::LOTE]);
        $cantidad = $this->pSQL($row[self::CANTIDAD]);
        $QA = $this->pSQL($row[self::QA]);
        $id_motivo = $this->pSQL($row[self::MOTIVO_QA]);
        
        //$proveedor = $this->pSQL($row[self::PROVEEDOR]);
        $id_proveedor = Proveedores::where('cve_proveedor',$row[self::PROVEEDOR])->get(['ID_Proveedor'])->first();
        $proveedor = $id_proveedor->ID_Proveedor;

        $codigo_lp = $this->pSQL($row[self::CODIGO_LP]);

        $result = "";
        if($codigo_lp != "")
        {
            $data_codigo_lp = Pallets::where('CveLP',$row[self::CODIGO_LP])->get(['IDContenedor'])->first();

            if($data_codigo_lp->IDContenedor)
            {
              //  $element = ExistenciaTarima::where('cve_articulo', $articulo)
              //  ->where('cve_almac', $almacen)
              //  ->where('idy_ubica', $ubicacion)
              //  ->where('lote', $lote)
               // ->first();
               // if($element != NULL)
               // {
                //  $model = $element; 
               // }
               // else 
                //{
                  $model = new ExistenciaTarima(); 
                //}

                $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

                $sql = "SELECT COUNT(*) num FROM ts_existenciatarima WHERE idy_ubica = '$ubicacion' AND cve_almac = '$almacen' AND cve_articulo = '$articulo' AND lote = '$lote' AND ID_Proveedor = $proveedor";
                $rs = mysqli_query($conn, $sql);
                $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                $existe = $resul['num'];

                if($existe)
                {
                    $sql = "DELETE FROM ts_existenciatarima WHERE idy_ubica = '$ubicacion' AND cve_almac = '$almacen' AND cve_articulo = '$articulo' AND lote = '$lote' AND ID_Proveedor = $proveedor";
                    $rs = mysqli_query($conn, $sql);
                }

                if($QA == 1)
                {
                    $sql = "SELECT MAX(Id) as ID FROM t_movcuarentena";
                    $rs = mysqli_query($conn, $sql);
                    $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                    $id_qa = $resul['ID']+1;

                    $folio_qa = "QA".$id_qa;
                    $id_user = $_SESSION['id_user'];
                    $sql = "INSERT INTO t_movcuarentena(Fol_Folio, Idy_Ubica, IdContenedor, Cve_Articulo, Cve_Lote, Cantidad, PzsXCaja, Fec_Ingreso, Id_MotivoIng, Tipo_Cat_Ing, Usuario_Ing) VALUES('{$folio_qa}', {$ubicacion}, 0, '{$articulo}', '{$lote}', {$cantidad}, 0, NOW(), {$id_motivo}, 'Q', {$id_user})";
                    $rs = mysqli_query($conn, $sql);

                }

                $model->cve_articulo    = $articulo;
                $model->cve_almac       = $almacen;
                $model->idy_ubica       = $ubicacion;
                $model->lote            = $lote;
                $model->existencia      = $cantidad;
                $model->ID_Proveedor    = $proveedor;
                $model->ntarima         = $data_codigo_lp->IDContenedor;
                $model->capcidad        = 1;
                $model->Cuarentena      = $QA;
                $result = $model->save();

                $bl = $row[self::BL];
                $cve_usuario = $_SESSION['cve_usuario'];
                $sql = "INSERT INTO t_cardex(cve_articulo, cve_lote, fecha, origen, destino, cantidad, Id_TipoMovimiento, cve_usuario, Cve_Almac, Fec_Ingreso) VALUES ('{$articulo}', '{$lote}', NOW(), 'Inventario Inicial', '{$ubicacion}', {$cantidad}, 1, '{$cve_usuario}', {$almacen}, CURDATE())";
                $rs = mysqli_query($conn, $sql);

            }
            //if($result) $correctos++;
        }
        else
        {
          //$element = ExistenciaPiezas::where('cve_articulo', $articulo)
          //->where('cve_almac', $almacen)
          //->where('idy_ubica', $ubicacion)
          //->where('cve_lote', $lote)
          //->first();
          //if($element != NULL)
          //{
            //$model = $element; 
          //}
          //else 
          //{
            $model = new ExistenciaPiezas(); 
          //}
  
          $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

          $sql = "SELECT COUNT(*) num FROM ts_existenciapiezas WHERE idy_ubica = '$ubicacion' AND cve_almac = '$almacen' AND cve_articulo = '$articulo' AND cve_lote = '$lote' AND ID_Proveedor = $proveedor";
          $rs = mysqli_query($conn, $sql);
          $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
          $existe = $resul['num'];

          if($existe)
          {
              $sql = "DELETE FROM ts_existenciapiezas WHERE idy_ubica = '$ubicacion' AND cve_almac = '$almacen' AND cve_articulo = '$articulo' AND cve_lote = '$lote' AND ID_Proveedor = $proveedor";
              $rs = mysqli_query($conn, $sql);
              $correctos--;
          }

          if($QA == 1)
          {
              $sql = "SELECT MAX(Id) as ID FROM t_movcuarentena";
              $rs = mysqli_query($conn, $sql);
              $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
              $id_qa = $resul['ID']+1;

              $folio_qa = "QA".$id_qa;
              $id_user = $_SESSION['id_user'];
              $sql = "INSERT INTO t_movcuarentena(Fol_Folio, Idy_Ubica, IdContenedor, Cve_Articulo, Cve_Lote, Cantidad, PzsXCaja, Fec_Ingreso, Id_MotivoIng, Tipo_Cat_Ing, Usuario_Ing) VALUES('{$folio_qa}', {$ubicacion}, 0, '{$articulo}', '{$lote}', {$cantidad}, 0, NOW(), {$id_motivo}, 'Q', {$id_user})";
              $rs = mysqli_query($conn, $sql);

          }


          $model->cve_articulo    = $articulo;
          $model->cve_almac       = $almacen;
          $model->idy_ubica       = $ubicacion;
          $model->cve_lote        = $lote;
          $model->Existencia      = $cantidad;
          $model->ID_Proveedor    = $proveedor;
          $model->Cuarentena      = $QA;
          $result = $model->save();

          $bl = $row[self::BL];
          $cve_usuario = $_SESSION['cve_usuario'];
          $sql = "INSERT INTO t_cardex(cve_articulo, cve_lote, fecha, origen, destino, cantidad, Id_TipoMovimiento, cve_usuario, Cve_Almac, Fec_Ingreso) VALUES ('{$articulo}', '{$lote}', NOW(), 'Inventario Inicial', '{$ubicacion}', {$cantidad}, 1, '{$cve_usuario}', {$almacen}, CURDATE())";
          $rs = mysqli_query($conn, $sql);

        }

        if($result) $correctos++;
        $linea++;
      }
      //$no_validos_msj = "\nHay {$lotes_no_validos} lotes en el archivo que no son válidos para registrar";
      //if($lotes_no_validos)
      @unlink($file);
      $msj_no_existentes = "";

      if($articulos_no_existentes)
        $msj_no_existentes = " - Los Siguientes artículos no existen en el sistema: ".$articulos_no_existentes;

      $this->response(200, [
        'errores' => $errores,
        'statusText' =>  "Stock importado con éxito. Total de Artículos afectados: \"{$correctos}\"", //.$msj_no_existentes
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


/*
    public function importar()
    {
      $errores = [];
      $dir_cache = PATH_APP . 'Cache/';
      $file = $dir_cache . basename($_FILES['file']['name']);

      if (! move_uploaded_file($_FILES['file']['tmp_name'], $file)) 
      {
        $this->response(400, ['statusText' =>  "Error al recibir el fichero, verifique que se tenga permisos para escribir en Cache",]);
      }

      if ( $_FILES['file']['type'] != 'application/vnd.ms-excel' AND
      $_FILES['file']['type'] != 'application/msexcel' AND
      $_FILES['file']['type'] != 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' AND
      $_FILES['file']['type'] != 'application/xls' )
      {
        @unlink($file);
        $this->response(400, ['statusText' =>  "Error en el formato del fichero",]);
      }
      // Obtener estructura del Código BL
      $sql = "SELECT codigo FROM t_codigocsd LIMIT 1"; //cve_rack-cve_nivel-Ubicacion
      $codigoBL = Capsule::select(Capsule::raw($sql));
      // pasillo - rack - nivel - seccion - ubicacion
      $formatoCodigoBL = strtolower($codigoBL[0]->codigo);
      $formatoCodigoBL = explode('-', str_ireplace('cve_', '', $formatoCodigoBL));

      $xlsx = new SimpleXLSX( $file );
      $linea = 1;

      foreach ($xlsx->rows() as $row)
      {
        if($linea == 1) 
        {
          $linea++;
          continue;
        }
        $eval = $this->validarRequeridosImportar($row);
        if( $eval === TRUE )
        {
          // Obtener el ID del Almacén
          $data_articulo = Articulos::where('cve_articulo',$row[self::CLAVE_ART])->get(['cve_articulo'])->first();
          if( $data_articulo == NULL )
          {
            $errores[$linea][] = [
              'column' => 'Artículo',
              'text' => 'El artículo ingresado no existe en el catálogo',
              'value' => $row[self::CLAVE_ART],
              'row' => $linea
            ];
            //$this->response(400, [
            //'statusText' =>  "La El campo Articulo de la fila {$linea} no existe. Corrija esto para continuar con la importación.",
            //]);
          }
          // Obtener el ID del Almacén
          $data_almacenp = AlmacenP::where('clave','=',$row[self::ALMACEN])->get(['id'])->first();
          if( $data_almacenp == NULL )
          {
            $errores[$linea][] = [
              'column' => 'Almacén',
              'text' => 'El almacén ingresado no existe',
              'value' => $row[self::ALMACEN],
              'row' => $linea
            ];
            //$this->response(400, [
            //'statusText' =>  "La El campo Almacén de la fila {$linea} no existe. Corrija esto para continuar con la importación.",
            //]);
          }
          // Analizar estructura de la ubicación en base al Código BL
          //$formatoCodigoBL_test = explode('-', $row[self::BL]);
          //if( count($formatoCodigoBL_test) != count($formatoCodigoBL) ){
          //$this->response(400, [
          //'statusText' =>  "El formato del Código BL ingresado en la fila {$linea} es incorrecto. Corrija esto para continuar con la //importación.",//
          //]);
          //}
          // Verificar que exista una ubicación con el Código Bl suministrado
          $data_ubicacion = Ubicaciones::where('CodigoCSD', $row[self::BL])->get(['idy_ubica'])->first();
          if( $data_ubicacion == NULL )
          {
            $errores[$linea][] = [
              'column' => 'Ubicación',
              'text' => 'El Ubicación (Código BL) ingresado no existe',
              'value' => $row[self::BL],
              'row' => $linea
            ];
            //$this->response(400, [
            //'statusText' =>  "La El campo Ubicación de la fila {$linea} no existe. Corrija esto para continuar con la importación.",
            //]);
          }
        }
        else 
        {
          $errores[$linea][] = [
            'column' => 'Artículo',
            'text' => 'La columna de Articulo está vacía',
            'value' => $row[self::CLAVE_ART],
            'row' => $linea
          ];
          //$this->response(400, [
          //'statusText' =>  "La Fila Nro. {$linea} de la columna \"{$eval}\" está vacía. Corrija esto para continuar con la importación.",
          //]);
        } 
        $linea++;
      }
      $linea = 1;
      $correctos = 0;
      foreach ($xlsx->rows() as $row)
      {
        if($linea == 1) 
        {
          $linea++;continue;
        }
        $data_articulo = Articulos::where('cve_articulo',$row[self::CLAVE_ART])->get(['control_lotes', 'control_numero_series'])->first();
        if( $data_articulo == NULL )
        {
          $linea++;continue;
        }
        // Verificar existencia del Lote
        if( $data_articulo->control_lotes == 'S' )
        {
          $data_lote = Lotes::where('LOTE', $row[self::LOTE])
          ->where('cve_articulo', $row[self::CLAVE_ART])
          ->get(['LOTE'])
          ->first();
          if( $data_lote == NULL)
          {
            $model = new Lotes(); 
            $model->LOTE            = $this->pSQL($row[self::LOTE]);
            $model->cve_articulo    = $this->pSQL($row[self::CLAVE_ART]);
            $model->CADUCIDAD       = $this->pSQL($row[self::CADUCIDAD]);
            $model->Activo          = 1;
            $model->save();
          }
        }

        // Verificar existencia en la SERIE
        if( $data_articulo->control_numero_series == 'S' )
        {
          $data_serie = Series::where('clave_articulo', $row[self::CLAVE_ART])
          ->get(['clave_articulo'])
          ->first();

          if( $data_serie == NULL)
          {
            $model = new Series(); 
            $model->clave_articulo  = $this->pSQL($row[self::CLAVE_ART]);
            $model->numero_serie    = $this->pSQL($row[self::SERIE]);
            $model->fecha_ingreso   = $this->pSQL(date('Y-m-d'));
            $model->save();
          }
        }
        // Verificar existencia previa
        $data_ubicacion = Ubicaciones::where('CodigoCSD', $row[self::BL])->get(['idy_ubica'])->first();
        $ubicacion = $data_ubicacion->idy_ubica;

        if( $ubicacion == NULL )
        {
          $linea++;continue;
        }
        // Obtener el ID del almacén
        $data_almacenp = AlmacenP::where('clave',$row[self::ALMACEN])->get(['id'])->first();
        $almacen = $data_almacenp->id;
        
        if($almacen == NULL)
        {
          $linea++;continue;
        }
        $articulo = $this->pSQL($row[self::CLAVE_ART]);
        $lote = ($this->pSQL($row[self::LOTE]) == null)?'':$this->pSQL($row[self::LOTE]);
        $cantidad = $this->pSQL($row[self::CANTIDAD]);

        $element = ExistenciaPiezas::where('cve_articulo', $articulo)
        ->where('cve_almac', $almacen)
        ->where('idy_ubica', $ubicacion)
        ->where('cve_lote', $lote)
        ->first();
        if($element != NULL)
        {
          $model = $element; 
        }
        else 
        {
          $model = new ExistenciaPiezas(); 
        }
        $model->cve_articulo    = $articulo;
        $model->cve_almac       = $almacen;
        $model->idy_ubica       = $ubicacion;
        $model->cve_lote        = $lote;
        $model->Existencia      = $cantidad;
        $result = $model->save();

        if($result) $correctos++;
        $linea++;
      }
      @unlink($file);
      $this->response(200, [
        'errores' => $errores,
        'statusText' =>  "Stock importado con éxito. Total de Artículos afectados: \"{$correctos}\"",
      ]);
    }
*/
}