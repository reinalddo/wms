<?php

  namespace AreaEmbarque;

  class AreaEmbarque {

    const TABLE = 't_ubicacionembarque';
    var $identifier;

    public function __construct( $cve_ubicacion = false, $key = false ) 
    {
        if( $cve_ubicacion ) 
        {
            $this->cve_ubicacion = (int) $cve_ubicacion;
        }

        if($key) 
        {
            $sql = sprintf(' SELECT cve_ubicacion FROM %s WHERE cve_ubicacion = ? ', self::TABLE );
            $sth = \db()->prepare($sql);
            $sth->setFetchMode(\PDO::FETCH_CLASS, '\AreaEmbarque\AreaEmbarque');
            $sth->execute(array($key));
            $areaembarque = $sth->fetch();
            $this->cve_ubicacion = $cve_ubicacion->cve_ubicacion;
        }
    }

    private function load() 
    {

        $sql = sprintf(' 
            SELECT u.*, a.id 
            from  t_ubicacionembarque u, c_almacenp a 
            WHERE u.cve_almac= a.id and u.cve_ubicacion = ?', self::TABLE );

        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\AreaEmbarque\AreaEmbarque' );
        $sth->execute( array( $this->cve_ubicacion ) );
        $this->data = $sth->fetch();
    }
	
	  function exist($cve_ubicacion) 
    {
        $sql = sprintf(' SELECT * FROM ' . self::TABLE . ' WHERE cve_ubicacion = ? ', self::TABLE );
        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\AreaEmbarque\AreaEmbarque' );
        $sth->execute( array( $cve_ubicacion ) );
        $this->data = $sth->fetch();
	  
        if(!$this->data)
            return false; 
        else 
            return true;
    }

    function getAll() 
    {
        $sql = ' SELECT * FROM ' . self::TABLE . ' where Activo = "1" ';
        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\AreaEmbarque\AreaEmbarque' );
        @$sth->execute( array( ID_Embarque ) );
        return $sth->fetchAll();
    }
	
    function __get( $key ) 
    {
        switch($key) 
        {
            case 'cve_ubicacion':
            case 'cve_almac':
            case 'Activo':
            case 'ID_Embarque':
            case 'AreaStagging':
                $this->load();
                return @$this->data->$key;
            default:
                return $this->key;
        }
    }

    function save( $_post ) 
    {
        if( !$_post['cve_ubicacion'] ) { throw new \ErrorException( 'cve_ubicacion is required.' ); }
    
        $sql = sprintf('
            INSERT INTO
            ' . self::TABLE . '
            SET
            cve_ubicacion = :cve_ubicacion, 
            cve_almac = :cve_almac,
            descripcion = :descripcion,
            status = 1,
            AreaStagging = :stagging		
        ');

        $this->save = \db()->prepare($sql);
        $this->save->bindValue( ':cve_ubicacion', $_post['cve_ubicacion'], \PDO::PARAM_STR );
        $this->save->bindValue( ':cve_almac', $_post['cve_almac'], \PDO::PARAM_STR );
    		$this->save->bindValue( ':descripcion', $_post['descripcion'], \PDO::PARAM_STR );
        $this->save->bindValue( ':stagging', $_post['stagging'], \PDO::PARAM_STR );
        $this->save->execute();
    }

    /*function password( $data ) 
    {
      if( !$data['password'] ) { throw new \ErrorException( 'Unfortuantly you wont get far without a password.' ); }
      $sql = sprintf(' UPDATE ' . self::TABLE . ' SET password = :password WHERE id_user = ' . $this->id_user . ' ');
      $this->save = \db()->prepare($sql);
      $password = password_hash( $data['password'], PASSWORD_BCRYPT, array( 'cost' => 11 ) );
      $this->save->bindValue( ':password', $password, \PDO::PARAM_STR );
      $this->save->execute();
    }*/

  	function editar( $data ) 
    {
        $sql = "UPDATE " . self::TABLE . " 
        SET
          cve_almac = '".$data['cve_almac']."', 
          descripcion = '".$data['descripcion']."',
          AreaStagging = '".$data['stagging']."'
        WHERE cve_ubicacion = '".$data['cve_ubicacion']."'";
        $rs = mysqli_query(\db2(), $sql) or die(mysqli_error(\db2()));
    }

    function borrar( $data ) 
    {
        $sql = ' UPDATE ' . self::TABLE . ' SET Activo = 0 WHERE cve_ubicacion = ? ';
        $this->save = \db()->prepare($sql);
        $this->save->execute( array($data['cve_ubicacion']));
    }
	  
    function recovery( $data ) 
    {
        $sql = "UPDATE " . self::TABLE . " SET Activo = 1 WHERE  ID_Embarque='".$data['ID_Embarque']."';";
        $this->delete = \db()->prepare($sql);
        $this->delete->execute( array($data['ID_Embarque']) );
    }

    /*function settings_design( $data ) 
    { 
        $sql = ' UPDATE ' . self::TABLE . ' SET Empresa = ? , VendId = ? , ID_Externo = ? WHERE ID_Proveedor = ? ';

        $this->save = \db()->prepare($sql);
        $this->save->execute( array(
          $data['Empresa'],
          $data['VendId'],
          $data['ID_Externo']
        ) );
    }*/

    public function inUse( $data ) 
    {
      $sql = "SELECT cve_ubicacion FROM V_ExistenciaGral WHERE cve_ubicacion = '".$data['cve_ubicacion']."'";
      $sth = \db()->prepare($sql);
      $sth->execute();
      $data = $sth->fetch();
      if ($data['cve_ubicacion']) 
          return true;
      else
          return false;
    }

    public function pedidos_ws( $data ) 
    {
      $sql = "SELECT r.id, r.fol_folio, r.Sufijo, 
        IF((SELECT GROUP_CONCAT(DISTINCT STATUS SEPARATOR '') FROM th_subpedido WHERE fol_folio = r.fol_folio) IN ('C', 'B'), 1, 0) AS mostrar_boton, t.Cve_CteCon, t.Nom_CteCon
FROM rel_uembarquepedido r
LEFT JOIN th_consolidado t ON t.Fol_PedidoCon = r.fol_folio
WHERE r.cve_ubicacion = '".$data['cve_ubicacion']."' 
AND r.fol_folio IN (SELECT Fol_PedidoCon FROM td_consolidado WHERE STATUS = 'A' UNION SELECT fol_folio FROM th_pedido WHERE TipoPedido = 'W2' AND STATUS = 'C' AND Cve_Almac = r.cve_almac)";
      $sth = \db()->prepare($sql);
      $sth->execute();
      $data = $sth->fetchAll();

      $html = "";

      foreach($data as $folio_ws)
      {
            extract($folio_ws);
          $html .= '<div class="panel-heading">
            <h4 class="panel-title">'.
              '<a data-toggle="collapse" href="#collapse'.$id.'">'.$fol_folio.'-'.$Sufijo.'| ('.$Cve_CteCon.') |'.$Nom_CteCon.'</a>
            </h4>
          </div>
          <div id="collapse'.$id.'" class="panel-collapse collapse">
            <ul class="list-group">';

          $sql_pedidos = "SELECT GROUP_CONCAT(tc.id SEPARATOR '') AS id_embarque_pedido, '' AS folio_w2, 
                                 tc.Fol_Folio AS fol_pedidos, c.Cve_CteProv, c.RazonSocial
                            FROM td_consolidado tc
                            LEFT JOIN c_cliente c ON c.Cve_CteProv = tc.Cve_CteProv
                            WHERE tc.Fol_PedidoCon = '{$fol_folio}' AND tc.Status = 'A' 
                            GROUP BY tc.Fol_Folio

                          UNION 

                            SELECT GROUP_CONCAT(DISTINCT tc.id SEPARATOR '') AS id_embarque_pedido, 
                            t.Fol_PedidoCon AS folio_w2, tc.Fol_Folio AS fol_pedidos, 
                            c.Cve_CteProv, c.RazonSocial
                            FROM t_consolidado t
                            LEFT JOIN td_consolidado tc ON tc.Fol_PedidoCon = t.Fol_PedidoCon 
                            LEFT JOIN c_cliente c ON c.Cve_CteProv = tc.Cve_CteProv
                            WHERE t.Fol_Consolidado = '{$fol_folio}' AND tc.Status = 'A'
                            GROUP BY tc.Fol_Folio
                            ORDER BY folio_w2, fol_pedidos";
          $sth_pedidos = \db()->prepare($sql_pedidos);
          $sth_pedidos->execute();
          $data_pedidos = $sth_pedidos->fetchAll();

          foreach($data_pedidos as $folios_pedidos)
          {
            extract($folios_pedidos);

                $folio_w2_print = '';
                if($folio_w2 != '')
                    $folio_w2_print = $folio_w2.' - ';
            $html .= '
                  <li class="list-group-item">

                <div class="panel-heading">
                        <h4 class="panel-title">
                          <a data-toggle="collapse" href="#collapse'.$id_embarque_pedido.'">'.$folio_w2_print.$fol_pedidos.'| ('.$Cve_CteProv.') |'.$RazonSocial.'</a>';
                    if($mostrar_boton)
                        $html.= '<button class="btn btn-primary" type="button" onclick="enviarAEbarque_o_Qa('."'".$fol_pedidos."'".')" style="float: right;margin-top: -12px;">Enviar a Embarques</button>';
                    $html .= '</h4>
                      </div>
                      <div id="collapse'.$id_embarque_pedido.'" class="panel-collapse collapse">';

                      $sql_articulos = "SELECT c.Cve_Articulo, a.des_articulo, c.Cve_Lote, 
                                               IF(a.control_lotes = 'S' AND a.Caduca = 'S' AND DATE_FORMAT(l.Caducidad, '%d-%m-%Y') != DATE_FORMAT('00-00-0000', '%d-%m-%Y'), DATE_FORMAT(l.Caducidad, '%d-%m-%Y'), '') AS Caducidad, c.Cant_Pedida, um.cve_umed as unidad_medida
                                        FROM td_consolidado c 
                                        LEFT JOIN c_articulo a ON a.cve_articulo = c.Cve_Articulo
                                        LEFT JOIN c_unimed um ON um.id_umed = a.unidadMedida
                                        LEFT JOIN c_lotes l ON l.cve_articulo = c.Cve_Articulo AND l.Lote = c.Cve_Lote
                                        WHERE Fol_PedidoCon = IF('{$folio_w2}' != '', '{$folio_w2}', '{$fol_folio}') AND Fol_Folio = '".$fol_pedidos."';";
                      $sth_articulos = \db()->prepare($sql_articulos);
                      $sth_articulos->execute();
                      $data_articulos = $sth_articulos->fetchAll();

                      $html .= '<table class="table table-bordered">
                                <thead>
                                    <td>Clave</td> <td>Descripción</td> <td>Lote|Serie</td> <td>Caducidad</td> <td>Cantidad</td><td>Unidad Medida</td>
                                </thead>';
                      foreach($data_articulos as $articulos_pedido)
                      {
                            extract($articulos_pedido);
                            $html .= '<tr>';
                            $html .= '<td>'.$Cve_Articulo.'</td>';
                            $html .= '<td>'.$des_articulo.'</td>';
                            $html .= '<td>'.$Cve_Lote.'</td>';
                            $html .= '<td>'.$Caducidad.'</td>';
                            $html .= '<td align="right">'.$Cant_Pedida.'</td>';
                            $html .= '<td>'.$unidad_medida.'</td>';
                            $html .= '</tr>';
                      }

                $html .= '</table>
                        </div>
                  </li>';
          }
/*
                        <ul class="list-group">
                          <li class="list-group-item">Articulo0001</li>
                          <li class="list-group-item">Articulo0002</li>
                        </ul>
*/

        $html .= '</ul>
          </div>
        ';
    }

      return $html;
    }

}