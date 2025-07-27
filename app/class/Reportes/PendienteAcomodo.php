<?php
namespace Reportes;

class PendienteAcomodo{
    public function obtenerTodos(){
        $sql = "
            SELECT  
                eh.Fol_Folio AS folio,
                eh.fol_oep AS orden,
                a.nombre AS almacen,
                u.des_almac AS area,
                e.cve_articulo AS clave_producto,
                p.des_articulo AS des_producto,
                l.LOTE AS lote,
                l.CADUCIDAD AS caducidad,
                e.numero_serie AS serie,
                pa.Cantidad AS cantidad,
                CONCAT(eh.Fec_Entrada, ' ',eh.HoraInicio) AS hora_inicio
            FROM `td_entalmacen` e 
            LEFT JOIN `th_entalmacen` eh ON eh.Fol_Folio = e.fol_folio
            LEFT JOIN c_almacenp a ON eh.Cve_Almac = a.clave
            LEFT JOIN c_almacen u ON eh.cve_ubicacion = u.clave_almacen
            LEFT JOIN c_articulo p ON e.cve_articulo = p.cve_articulo
            LEFT JOIN c_lotes l ON l.cve_articulo = e.cve_articulo AND l.LOTE = e.cve_lote
            LEFT JOIN t_pendienteacomodo pa ON pa.cve_articulo = e.cve_articulo AND pa.cve_lote = e.cve_lote
            WHERE eh.status = 'T'
        ";
        $sth = \db()->prepare($sql);
        $sth->execute();
        $pendiente = $sth->fetchAll();

        ob_start(); ?>
        <table style="width: 100%">
            <thead>
                <tr>
                    <th>FOLIO</th>
                    <th>ORDEN DE COMPRA</th>
                    <th>ALMACÉN</th>
                    <th>ÁREA DE RECEPCIÓN</th>
                    <th>CLAVE PRODUCTO</th>
                    <th>DESCRIPCIÓN</th>
                    <th>LOTE</th>
                    <th>CADUCIDAD</th>
                    <th>SERIE</th>
                    <th>CANTIDAD</th>
                    <th>HORA INGRESO</th>
                </tr>
            </thead>        
            <tbody>
        <?php
        $tabla = ob_get_clean();

         foreach($pendiente as $acomodo){
             ob_start();
             ?>
                <tr>
                    <td><?php echo $acomodo['folio']?></td>
                    <td><?php echo $acomodo['orden']?></td>
                    <td><?php echo $acomodo['almacen']?></td>
                    <td><?php echo $acomodo['area']?></td>
                    <td><?php echo $acomodo['clave_producto']?></td>
                    <td><?php echo $acomodo['des_producto']?></td>
                    <td><?php echo $acomodo['lote']?></td>
                    <td><?php echo $acomodo['caducidad']?></td>
                    <td><?php echo $acomodo['serie']?></td>
                    <td><?php echo $acomodo['cantidad']?></td>
                    <td><?php echo $acomodo['hora_inicio']?></td>
                </tr>
             <?php 

             $tabla .= ob_get_clean();
        } 

        $tabla .= '</tbody></table>';

        return $tabla;
    }
}

?>