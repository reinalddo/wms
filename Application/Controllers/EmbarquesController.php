<?php

namespace Application\Controllers;

use Framework\View\View;
use Framework\Helpers\Utils;
use Framework\Http\Response;
use Framework\Http\Controller;
use jakeroid\tools\SimpleXLSX;
use Illuminate\Database\Capsule\Manager as Capsule;


/**
 * @version 1.0.0
 * @category Usuarios
 * @author Brayan Rincon <brayan262@gemail.com>
 */
class EmbarquesController extends Controller
{
    /**
     * Renderiza la vista general
     *
     * @return void
     */
  
    public function exportar()
    {

//***************************************************************************************************
    $id = $_GET['id'];
    $folios = $_GET['folios'];
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

/*
    $sql="
        SELECT 
            caja.fol_folio,
            t.descripcion,
            caja.Guia, 
            (select RazonSocial from c_cliente inner join th_pedido on th_pedido.Cve_clte = c_cliente.Cve_Clte where th_pedido.Fol_folio = caja.fol_folio) as cliente,
            (select Cve_Clte from th_pedido where th_pedido.Fol_folio = caja.fol_folio) as cve_cliente,
            d.id_destinatario,
            d.razonsocial,
            item.Cve_articulo as clave_art,
            IFNULL(ar.control_lotes, 'N') as control_lote,
            IFNULL(ar.control_numero_series, 'N') as control_serie,
            ar.cajas_palet as cajasxpallets,
            ar.num_multiplo as piezasxcajas, 
            l.LOTE,
            l.Caducidad,
            IFNULL((SELECT des_articulo FROM c_articulo WHERE cve_articulo = item.Cve_articulo), '') as descripcion_producto,
            s.Cantidad as cantidad,
            TRUNCATE(IFNULL(((ar.alto/1000) * (ar.ancho/1000) * (ar.fondo/1000)), '0') * s.Cantidad,4) AS volumen_total,
            TRUNCATE(IFNULL((SELECT peso FROM c_articulo WHERE cve_articulo = item.Cve_articulo), '0') * s.Cantidad,4) AS peso_total

        FROM th_cajamixta caja
            LEFT JOIN c_tipocaja t on t.id_tipocaja = caja.cve_tipocaja 
            LEFT JOIN td_cajamixta item ON item.Cve_CajaMix IN (SELECT Cve_CajaMix FROM th_cajamixta WHERE fol_folio IN ('{$folios}')  AND NCaja = caja.NCaja)
            LEFT JOIN c_articulo ar ON ar.cve_articulo = item.Cve_articulo
            LEFT JOIN c_lotes l ON l.cve_articulo = ar.cve_articulo
            LEFT JOIN c_destinatarios d ON d.id_destinatario = (SELECT destinatario FROM th_pedido WHERE th_pedido.Fol_folio = caja.fol_folio)
            LEFT JOIN td_surtidopiezas s ON s.fol_folio IN ('{$folios}') AND s.Cve_articulo = item.Cve_articulo
        WHERE caja.fol_folio IN (SELECT Fol_folio FROM td_ordenembarque WHERE ID_OEmbarque = {$id}) AND caja.Activo = 1
    ";
*/
/*
        

*/
    $sql="
SELECT DISTINCT
            caja.fol_folio,
            '' AS NCaja,
            t.clave,
            t.descripcion,
            '' AS ntarima,
            '' AS Guia,
             TRUNCATE(
                (CASE 
                    WHEN caja.cve_tipocaja = 1 THEN
                    (
                        SELECT
                            IFNULL(ROUND(SUM(td_cajamixta.Cantidad * ((a.alto/1000) * (a.ancho/1000) * (a.fondo/1000))),3), 0) AS volumentotal
                        FROM td_cajamixta
                            LEFT JOIN c_articulo a ON a.cve_articulo = td_cajamixta.Cve_articulo
                        WHERE td_cajamixta.Cve_CajaMix = caja.Cve_CajaMix
                    )
                    ELSE
                    (
                        SELECT ROUND((largo/1000)*(alto/1000)*(ancho/1000),3) FROM c_tipocaja WHERE id_tipocaja = caja.cve_tipocaja
                    ) 
                END),4) AS volumen,
            (SELECT
                 IFNULL(ROUND(SUM(td_cajamixta.Cantidad * a.peso),3), 0) AS volumentotal
             FROM td_cajamixta
                 LEFT JOIN c_articulo a ON a.cve_articulo = td_cajamixta.Cve_articulo
             WHERE td_cajamixta.Cve_CajaMix = caja.Cve_CajaMix) AS Peso,
            (SELECT RazonSocial FROM c_cliente INNER JOIN th_pedido ON th_pedido.Cve_clte = c_cliente.Cve_Clte WHERE th_pedido.Fol_folio = caja.fol_folio) AS cliente,
            (SELECT Cve_Clte FROM th_pedido WHERE th_pedido.Fol_folio = caja.fol_folio) AS cve_cliente,

            d.id_destinatario,
            d.razonsocial,
            item.Cve_articulo AS clave_art,
            IFNULL(ar.control_lotes, 'N') AS control_lote,
            IFNULL(ar.control_numero_series, 'N') AS control_serie,
            IFNULL(ar.cajas_palet, 0) AS cajasxpallets,
            ar.num_multiplo AS piezasxcajas, 
            l.LOTE,
            l.Caducidad,
            IFNULL((SELECT des_articulo FROM c_articulo WHERE cve_articulo = item.Cve_articulo), '') AS descripcion_producto,
            s.Cantidad AS cantidad,
            TRUNCATE(IFNULL(((ar.alto/1000) * (ar.ancho/1000) * (ar.fondo/1000)), '0') * s.Cantidad,4) AS volumen_total,
            TRUNCATE(IFNULL((SELECT peso FROM c_articulo WHERE cve_articulo = item.Cve_articulo), '0') * s.Cantidad,4) AS peso_total

        FROM th_cajamixta caja
            LEFT JOIN c_tipocaja t ON t.id_tipocaja = caja.cve_tipocaja 
            LEFT JOIN td_cajamixta item ON item.Cve_CajaMix IN (SELECT Cve_CajaMix FROM th_cajamixta WHERE fol_folio IN ('{$folios}')  AND NCaja = caja.NCaja) AND caja.Cve_CajaMix = item.Cve_CajaMix
            LEFT JOIN c_articulo ar ON ar.cve_articulo = item.Cve_articulo 
            LEFT JOIN c_lotes l ON l.cve_articulo = ar.cve_articulo AND l.LOTE = item.Cve_Lote AND l.cve_articulo = ar.cve_articulo
            LEFT JOIN c_destinatarios d ON d.id_destinatario=(SELECT destinatario FROM th_pedido WHERE th_pedido.Fol_folio = caja.fol_folio)
            LEFT JOIN td_surtidopiezas s ON s.fol_folio IN ('{$folios}') AND s.Cve_articulo = item.Cve_articulo
        WHERE caja.fol_folio IN (SELECT Fol_folio FROM td_ordenembarque WHERE ID_OEmbarque = {$id}) AND caja.Activo = 1

UNION 

SELECT DISTINCT
            tt.Fol_Folio AS fol_folio,
            '' AS NCaja,
            ch.CveLP AS clave,
            ch.CveLP AS descripcion,
            tt.ntarima AS ntarima,
            '' AS Guia,
             TRUNCATE(IFNULL(ROUND(SUM(tt.cantidad * ((ar.alto/1000) * (ar.ancho/1000) * (ar.fondo/1000))),3), 0) ,4) AS volumen,
            (IFNULL(ROUND(SUM(tt.cantidad * ar.peso),3), 0)) AS Peso,
            (SELECT RazonSocial FROM c_cliente INNER JOIN th_pedido ON th_pedido.Cve_clte = c_cliente.Cve_Clte WHERE th_pedido.Fol_folio = tt.Fol_Folio) AS cliente,
            (SELECT Cve_Clte FROM th_pedido WHERE th_pedido.Fol_folio = tt.Fol_Folio) AS cve_cliente,

            IF(p.cve_ubicacion = '', d.id_destinatario, p.cve_ubicacion) AS id_destinatario,
            IF(p.cve_ubicacion = '', d.razonsocial, (SELECT descripcion FROM t_ruta WHERE cve_ruta = p.cve_ubicacion)) AS razonsocial,
            tt.cve_articulo AS clave_art,
            IFNULL(ar.control_lotes, 'N') AS control_lote,
            IFNULL(ar.control_numero_series, 'N') AS control_serie,
            IFNULL(ar.cajas_palet, 0) AS cajasxpallets,
            ar.num_multiplo AS piezasxcajas, 
            l.LOTE,
            l.Caducidad,
            IFNULL((SELECT des_articulo FROM c_articulo WHERE cve_articulo = tt.cve_articulo), '') AS descripcion_producto,
            tt.cantidad AS cantidad,
            TRUNCATE(IFNULL(((ar.alto/1000) * (ar.ancho/1000) * (ar.fondo/1000)), '0') * s.Cantidad,4) AS volumen_total,
            TRUNCATE(IFNULL((SELECT peso FROM c_articulo WHERE cve_articulo = tt.cve_articulo), '0') * s.Cantidad,4) AS peso_total

        FROM t_tarima tt
            LEFT JOIN c_charolas ch ON tt.ntarima = ch.IDContenedor
            LEFT JOIN th_pedido p ON p.Fol_folio = tt.Fol_Folio
            LEFT JOIN c_articulo ar ON ar.cve_articulo = tt.cve_articulo 
            LEFT JOIN c_lotes l ON l.cve_articulo = ar.cve_articulo AND l.LOTE = tt.lote AND l.cve_articulo = ar.cve_articulo
            LEFT JOIN c_destinatarios d ON d.id_destinatario=(SELECT destinatario FROM th_pedido WHERE th_pedido.Fol_folio = tt.Fol_Folio)
            LEFT JOIN td_surtidopiezas s ON s.fol_folio IN ('{$folios}') AND s.Cve_articulo = tt.cve_articulo
        WHERE tt.Fol_Folio IN (SELECT Fol_folio FROM td_ordenembarque WHERE ID_OEmbarque = {$id}) AND tt.Ban_Embarcado = 'S'

    GROUP BY ntarima";

    $res = "";
    if (!($res = mysqli_query($conn, $sql))) 
    {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

//***************************************************************************************************

        $columnas = [
            'Pedido',
            'Cliente',
            'Nombre',
            'Clave Destinatario',
            'Destinatario',
            'Clave',
            'Descripción',
            'Cant. Enviada Pzas',
            'Lote',
            'Caducidad',
            'Serie',
            'Pallet',
            'Caja',
            'Piezas',
            'Volumen (m3)',
            'Peso (Kg)',
            'Caja Empaque',
            'Guia Embarque'
        ];

        $filename = "Embarque #{$id}" . ".xls";
        ob_clean();

        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");

        foreach($columnas as $column) 
        {            
            echo $column . "\t" ;            
        }
        print("\r\n");


        $sql_guias = "SELECT Guia as GuiaEmb FROM th_cajamixta WHERE fol_folio IN ('{$folios}') ORDER BY Cve_CajaMix ASC";
        $res_guias = mysqli_query($conn, $sql_guias);
        while($row = mysqli_fetch_object($res))
        {
            $Nserie = "";
            $NLote = "";

        //**************************************************
        //FUNCION PARA LAS COLUMNAS PALLET | CAJAS | PIEZAS
        //**************************************************
        //Archivos donde se encuentra esta función:
        //api\embarques\lista\index.php
        //api\reportes\lista\existenciaubica.php
        //api\reportes\lista\concentradoexistencia.php
        //app\template\page\reportes\existenciaubica.php
        //\Application\Controllers\EmbarquesController.php
        //\Application\Controllers\InventarioController.php
        //**************************************************
        //clave busqueda: COLPCP
        //**************************************************
        $row_guias = mysqli_fetch_array($res_guias);
        extract($row_guias);
        $Guia = $GuiaEmb;

            $valor1 = 0;
            if($row->piezasxcajas > 0)
               $valor1 = $row->cantidad/$row->piezasxcajas;

            if($row->cajasxpallets > 0)
               $valor1 = $valor1/$row->cajasxpallets;
           else
               $valor1 = 0;

            $Pallet = intval($valor1);

            $valor2 = 0;
            $cantidad_restante = $row->cantidad - ($Pallet*$row->piezasxcajas*$row->cajasxpallets);
            if(!is_int($valor1) || $valor1 == 0)
            {
                if($row->piezasxcajas > 0)
                   $valor2 = ($cantidad_restante/$row->piezasxcajas);// - ($Pallet*$cantidad);
            }
            $Cajas = intval($valor2);

            $Piezas = 0;
            if($row->piezasxcajas == 1) 
            {
                $valor2 = 0; 
                $Caja = $cantidad_restante;
                $Piezas = 0;
            }
            else if($piezasxcajas == 0 || $piezasxcajas == "")
            {
                if($piezasxcajas == "") $piezasxcajas = 0;
                $valor2 = 0; 
                $Caja = 0;
                $Piezas = $cantidad_restante;
            }

            $cantidad_restante = $cantidad_restante - ($Cajas*$row->piezasxcajas);

            if(!is_int($valor2))
            {
               //$Piezas = ($Cajas*$cantidad_restante) - $piezasxcajas;
                $Piezas = $cantidad_restante;
            }
        //**************************************************

            if($row->control_serie == "S") $Nserie = $row->LOTE;
            else if($row->control_lote == "S") $NLote = $row->LOTE;

            echo $this->clear_column($row->fol_folio) . "\t";
            echo $this->clear_column($row->cve_cliente) . "\t";
            echo $this->clear_column($row->cliente) . "\t";
            echo $this->clear_column($row->id_destinatario) . "\t";
            echo $this->clear_column($row->razonsocial) . "\t";
            echo $this->clear_column($row->clave_art) . "\t";
            echo $this->clear_column($row->descripcion_producto) . "\t";
            echo $this->clear_column($row->cantidad) . "\t";
            echo $this->clear_column($NLote) . "\t";
            echo $this->clear_column($row->Caducidad) . "\t";
            echo $this->clear_column($Nserie) . "\t";
            echo $this->clear_column($Pallet) . "\t";
            echo $this->clear_column($Cajas) . "\t";
            echo $this->clear_column($Piezas) . "\t";
            echo $this->clear_column($row->volumen_total) . "\t";
            echo $this->clear_column($row->peso_total) . "\t";
            echo $this->clear_column($row->descripcion) . "\t";
            echo $this->clear_column($Guia) . "\t";
            echo  "\r\n";
        }
    mysqli_close($conn);
/*
        $this->response(200, [
            'id' =>  $id,
            'folios' => $folios,
            'Embarque' => 'Embarque OK'
        ]);
*/
        exit;
    }

    private function clear_column(&$str)
    {
        $str = preg_replace("/\t/", "\\t", $str);
        $str = preg_replace("/\r?\n/", "\\n", $str);
        if(strstr($str, '"')) $str = '"' . str_replace('"', '""', $str) . '"';
        return $str;
    }

    public function importarFotosTH()
    {
        $statusText = "Foto Importada con Éxito";
        $statusType = 1;
        $id_embarque_folio = $_POST['folio_foto_th'];
        $ruta_path = 'data/uploads/fotos_folio/Embarque/';
        $descripcion_foto = $_POST['descripcion_foto'];
        $type = $_FILES['image_file_th']['type'];

        $nombre = $_FILES['image_file_th']['name'];
        $nombre_archivo = $nombre;
        $extension = "";

        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

        //$sql_id_pedido = "SELECT id_pedido FROM th_pedido WHERE fol_folio = '$id_embarque_folio'";
        //$res_pedido = mysqli_query($conn, $sql_id_pedido);
        //$embarque_pedido_id = mysqli_fetch_array($res_pedido)["id_pedido"];
/*
        $sql_limite = "SELECT COUNT(*) as cantidad FROM th_embarque_fotos WHERE folio_pedido = '$id_embarque_folio'";
        $res_conteo = mysqli_query($conn, $sql_limite);
        $cantidad = mysqli_fetch_array($res_conteo)["cantidad"];

        if($cantidad >= 20)
        {
            $this->response(200, [
                'statusType' =>  "error",
                'statusText' =>  "Límite de 20 Documentos alcanzado"
            ]);
            return;
        }
*/

        for($i = strlen($nombre_archivo)-1; $i > 0; $i--)
        {
          $extension .= $nombre_archivo[$i];
          if($nombre_archivo[$i] == ".")
          {
            break;
          }
        }
        $extension = strrev($extension);
        $_FILES['image_file_th']['name'] = "1"."-".$id_embarque_folio.$extension;

        $ruta = $ruta_path.$_FILES['image_file_th']['name'];

        while(file_exists($ruta))
        {
            $nombre = $_FILES['image_file_th']['name'];
            $arr = explode("-", $nombre);
            $num = (int)$arr[0];
            $num++;
            $_FILES['image_file_th']['name'] = str_replace(($num-1)."-", $num."-", $nombre);
            $ruta = $ruta_path.$_FILES['image_file_th']['name'];
        }

        $tmp_file = $_FILES['image_file_th']['tmp_name'];
        $foto = file_get_contents($tmp_file);

        if(move_uploaded_file($tmp_file, $ruta))
        {
            $sql = "INSERT INTO th_embarque_fotos(folio_pedido, ruta, descripcion, type, foto) VALUES (?, ?, ?, ?, ?)";
            $sth = \db()->prepare($sql);
            $sth->bindParam(1, $id_embarque_folio);
            $sth->bindParam(2, $ruta);
            $sth->bindParam(3, $descripcion_foto);
            $sth->bindParam(4, $type);
            $sth->bindParam(5, $foto);
        }
        //$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

          if (!$sth->execute())
          {
              $statusText = "Falló la preparación: (" . mysqli_error($conn) . ")"; 
              $statusType = 0;
          }

        $this->response(200, [
            'statusType' =>  $statusType,
            'statusText' =>  $statusText
        ]);
    }


    public function DocumentosEmbarque()
    {
        $statusText = "Documento Importado con Éxito";
        $statusType = 1;
        $folio = $_POST['folio_documento'];
        $ruta_path = 'data/uploads/documentos_articulos/';
        $type = $_FILES['image_file_th']['type'];

        $nombre = $_FILES['image_file_th']['name'];
        $nombre_archivo = $nombre;
        $extension = "";

        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

        //$sql_id_pedido = "SELECT id_pedido FROM th_pedido WHERE fol_folio = '$id_embarque_folio'";
        //$res_pedido = mysqli_query($conn, $sql_id_pedido);
        //$embarque_pedido_id = mysqli_fetch_array($res_pedido)["id_pedido"];
/*
        $crear_tabla = "CREATE TABLE IF NOT EXISTS c_articulo_documento (
                          id INT NOT NULL AUTO_INCREMENT,
                          cve_articulo VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL,
                          ruta VARCHAR(200) COLLATE utf8mb4_spanish_ci NOT NULL,
                          descripcion VARCHAR(300) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
                          documento BLOB NOT NULL,
                          TYPE VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL,
                          PRIMARY KEY (`id`)
                        ) ENGINE=INNODB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;";
        $res_tabla = mysqli_query($conn, $crear_tabla);
*/
/*
        $sql_limite = "SELECT COUNT(*) as cantidad FROM c_embarque_documentos WHERE folio = '$folio'";
        $res_conteo = mysqli_query($conn, $sql_limite);
        $cantidad = mysqli_fetch_array($res_conteo)["cantidad"];

        if($cantidad >= 20)
        {
            $this->response(200, [
                'statusType' =>  "error",
                'statusText' =>  "Límite de 20 Documentos alcanzado"
            ]);
            return;
        }
*/
        for($i = strlen($nombre_archivo)-1; $i > 0; $i--)
        {
          $extension .= $nombre_archivo[$i];
          if($nombre_archivo[$i] == ".")
          {
            break;
          }
        }
        $extension = strrev($extension);
        $_FILES['image_file_th']['name'] = "1"."-".$folio.$extension;

        $ruta = $ruta_path.$_FILES['image_file_th']['name'];

        while(file_exists($ruta))
        {
            $nombre = $_FILES['image_file_th']['name'];
            $arr = explode("-", $nombre);
            $num = (int)$arr[0];
            $num++;
            $_FILES['image_file_th']['name'] = str_replace(($num-1)."-", $num."-", $nombre);
            $ruta = $ruta_path.$_FILES['image_file_th']['name'];
            $descripcion = $_FILES['image_file_th']['name'];
        }

        $tmp_file = $_FILES['image_file_th']['tmp_name'];
        $documento = file_get_contents($tmp_file);

        if(move_uploaded_file($tmp_file, $ruta))
        {
            $sql = "INSERT INTO c_embarque_documentos(folio, ruta, descripcion, documento, type) VALUES (?, ?, ?, ?, ?)";
            $sth = \db()->prepare($sql);
            $sth->bindParam(1, $folio);
            $sth->bindParam(2, $ruta);
            $sth->bindParam(3, $nombre_archivo);
            $sth->bindParam(4, $documento);
            $sth->bindParam(5, $type);
        }
        //$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

          if (!$sth->execute())
          {
              $statusText = "Falló la preparación: (" . mysqli_error($conn) . ")"; 
              $statusType = 0;
          }

        $this->response(200, [
            'statusType' =>  $statusType,
            'statusText' =>  $statusText
        ]);
    }


}
