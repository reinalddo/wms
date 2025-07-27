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
      $sql = "SELECT id, fol_folio, Sufijo, IF((SELECT GROUP_CONCAT(DISTINCT STATUS SEPARATOR '') FROM th_subpedido WHERE fol_folio = rel_uembarquepedido.fol_folio) = 'B', 1, 0) AS mostrar_boton FROM rel_uembarquepedido WHERE cve_ubicacion = '".$data['cve_ubicacion']."' AND fol_folio IN (SELECT Fol_PedidoCon FROM td_consolidado WHERE STATUS = 'A')";
      $sth = \db()->prepare($sql);
      $sth->execute();
      $data = $sth->fetchAll();

      $html = "";

      foreach($data as $folio_ws)
      {
            extract($folio_ws);
          $html .= '<div class="panel-heading">
            <h4 class="panel-title">'.
              '<a data-toggle="collapse" href="#collapse'.$id.'">'.$fol_folio.'</a>
            </h4>
          </div>
          <div id="collapse'.$id.'" class="panel-collapse collapse">
            <ul class="list-group">';

          $sql_pedidos = "SELECT GROUP_CONCAT(id SEPARATOR '') as id_embarque_pedido, Fol_Folio as fol_pedidos FROM td_consolidado WHERE Fol_PedidoCon = '".$fol_folio."' AND Status = 'A' GROUP BY Fol_Folio";
          $sth_pedidos = \db()->prepare($sql_pedidos);
          $sth_pedidos->execute();
          $data_pedidos = $sth_pedidos->fetchAll();

          foreach($data_pedidos as $folios_pedidos)
          {
            extract($folios_pedidos);
            $html .= '
                  <li class="list-group-item">

                <div class="panel-heading">
                        <h4 class="panel-title">
                          <a data-toggle="collapse" href="#collapse'.$id_embarque_pedido.'">'.$fol_pedidos.'</a>';
                    if($mostrar_boton)
                        $html.= '<button class="btn btn-primary" type="button" onclick="enviarAEbarque_o_Qa('."'".$fol_pedidos."'".')" style="float: right;margin-top: -12px;">Enviar QA o Embarques</button>';
                    $html .= '</h4>
                      </div>
                      <div id="collapse'.$id_embarque_pedido.'" class="panel-collapse collapse">';

                      $sql_articulos = "SELECT c.Cve_Articulo, a.des_articulo, c.Cve_Lote, 
                                                DATE_FORMAT(l.Caducidad, '%d-%m-%Y') AS Caducidad, c.Cant_Pedida 
                                        FROM td_consolidado c 
                                        LEFT JOIN c_articulo a ON a.cve_articulo = c.Cve_Articulo
                                        LEFT JOIN c_lotes l ON l.cve_articulo = c.Cve_Articulo AND l.Lote = c.Cve_Lote
                                        WHERE Fol_PedidoCon = '".$fol_folio."' AND Fol_Folio = '".$fol_pedidos."';";
                      $sth_articulos = \db()->prepare($sql_articulos);
                      $sth_articulos->execute();
                      $data_articulos = $sth_articulos->fetchAll();

                      $html .= '<table class="table table-bordered">
                                <thead>
                                    <td>Clave</td> <td>Descripción</td> <td>Lote|Serie</td> <td>Caducidad</td> <td>Cantidad</td>
                                </thead>';
                      foreach($data_articulos as $articulos_pedido)
                      {
                            extract($articulos_pedido);
                            $html .= '<tr>';
                            $html .= '<td>'.$Cve_Articulo.'</td>';
                            $html .= '<td>'.$des_articulo.'</td>';
                            $html .= '<td>'.$Cve_Lote.'</td>';
                            $html .= '<td>'.$Caducidad.'</td>';
                            $html .= '<td>'.$Cant_Pedida.'</td>';
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