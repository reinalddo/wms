<?php
/*td_pedido Fol_folio
t_ubicacionembarque ID_Embarque
th_ordenembarque ID_OEmbarque
td_ordenembarque ID_OEmbarque
th_pedido Fol_folio*/
namespace Embarques;

class Embarques {

  const TABLE = 't_ubicacionembarque'; //Obtener ID de Embarque
  const TABLE_PEDIDO = 'th_pedido'; // actualizar status a T  Fol_folio
  const TABLE_EMBARQUE = 'th_ordenembarque';
  const TABLE_EMBARQUE_FOLIOS = 'td_ordenembarque';
  var $identifier;

  public function __construct( $ID_Embarque = false, $key = false ) {

    if( $ID_Embarque ) {
      $this->ID_Embarque = (int) $ID_Embarque;
    }

    if($key) {

      $sql = sprintf('
          SELECT
            ID_Embarque
          FROM
            %s
          WHERE
            ID_Embarque = ?
        ',
                     self::TABLE
                    );

      $sth = \db()->prepare($sql);
      $sth->setFetchMode(\PDO::FETCH_CLASS, '\Embarques\Embarques');
      $sth->execute(array($key));

      $embarque = $sth->fetch();

      $this->ID_Embarque = $embarque->ID_Embarque;

    }

  }

  private function load() {

    $sql = sprintf('
        SELECT
          *
        FROM
          %s
        WHERE
          ID_Embarque = ?
      ',
                   self::TABLE
                  );

    $sth = \db()->prepare( $sql );
    $sth->setFetchMode( \PDO::FETCH_CLASS, '\Embarques\Embarques' );
    $sth->execute( array( $this->ID_Embarque ) );

    $this->data = $sth->fetch();

  }

  function getAll() {

    $sql = '
        SELECT
          *
        FROM
          ' . self::TABLE . '
      ';

    $sth = \db()->prepare( $sql );
    $sth->setFetchMode( \PDO::FETCH_CLASS, '\Embarques\Embarques' );
    $sth->execute( array( ID_Embarque ) );

    return $sth->fetchAll();

  }

  function __get( $key ) {

    switch($key) {
      case 'ID_Embarque':
      case 'cve_ubicacion':
      case 'cve_almac':
      case 'status':
        $this->load();
        return @$this->data->$key;
      default:
        return $this->key;
    }

  }

  function save( $_post ) {
    extract($_post);

    $sql = sprintf('
        INSERT INTO
          ' . self::TABLE_EMBARQUE . '
        SET
          ID_Transporte = :ID_Transporte
        , cve_usuario = :cve_usuario
        , fecha = NOW()
        , destino = :destino
        , comentarios = :comentarios
        , Num_Guia = :Num_Guia
        , Tipo_Entrega = :Tipo_Entrega
      ');

    $this->save = \db()->prepare($sql);

    $this->save->bindValue( ':ID_Transporte', $transporte, \PDO::PARAM_STR );
    $this->save->bindValue( ':cve_usuario', $chofer, \PDO::PARAM_STR );
    $this->save->bindValue( ':destino', $destino, \PDO::PARAM_STR );
    $this->save->bindValue( ':comentarios', $comentarios, \PDO::PARAM_STR );
    $this->save->bindValue( ':Num_Guia', $guia, \PDO::PARAM_STR );
    $this->save->bindValue( ':Tipo_Entrega', $tipo_entrega, \PDO::PARAM_STR );
    $this->save->execute();

    $ID_Embarque = \db()->lastInsertId();

    $sql = "INSERT INTO ".self::TABLE_EMBARQUE_FOLIOS."
          VALUES
        ";
    $i = 1;
    $total_folios = count($folios);

    foreach ($folios as $folio){
      $sql .= "({$ID_Embarque}, '{$folio}', NULL)";
      if($i < $total_folios){
        $sql .= ",";
      }
      $i ++;
    }
    $query = mysqli_query(\db2(), $sql);

    if($query){
      return true;
    }

  }

  /*function password( $data ) {

      if( !$data['password'] ) { throw new \ErrorException( 'Unfortuantly you wont get far without a password.' ); }

      $sql = sprintf('
        UPDATE
          ' . self::TABLE . '
        SET
          password = :password
        WHERE
          id_user = ' . $this->id_user . '
      ');

      $this->save = \db()->prepare($sql);

      $password = password_hash( $data['password'], PASSWORD_BCRYPT, array( 'cost' => 11 ) );

      $this->save->bindValue( ':password', $password, \PDO::PARAM_STR );
      $this->save->execute();

    }*/

  function actualizarEmbarque( $data ) {
    extract($data);

    $sql = sprintf('
      UPDATE
        ' . self::TABLE_EMBARQUE . '
      SET
        ID_Transporte = :ID_Transporte
      , cve_usuario = :cve_usuario
      , fecha = NOW()
      , destino = :destino
      , comentarios = :comentarios
      , Num_Guia = :Num_Guia
      , Tipo_Entrega = :Tipo_Entrega

      WHERE
      ID_OEmbarque = :ID_OEmbarque
    ');

    $this->save = \db()->prepare($sql);

    $this->save->bindValue( ':ID_Transporte', $transporte, \PDO::PARAM_STR );
    $this->save->bindValue( ':cve_usuario', $chofer, \PDO::PARAM_STR );
    $this->save->bindValue( ':destino', $destino, \PDO::PARAM_STR );
    $this->save->bindValue( ':comentarios', $comentarios, \PDO::PARAM_STR );
    $this->save->bindValue( ':Num_Guia', $guia, \PDO::PARAM_STR );
    $this->save->bindValue( ':Tipo_Entrega', $tipo_entrega, \PDO::PARAM_STR );
    $this->save->bindValue( ':ID_OEmbarque', $id, \PDO::PARAM_STR );
    $this->save->execute();

    $ID_Embarque = $id;

    $sql = "DELETE FROM ".self::TABLE_EMBARQUE_FOLIOS." WHERE ID_OEmbarque = {$id}";

    $query = mysqli_query(\db2(), $sql);

    if($query){
      $total_folios = count($folios);
      if($total_folios > 0){
        $sql = "INSERT INTO ".self::TABLE_EMBARQUE_FOLIOS."
            VALUES
          ";
        $i = 1;
        foreach ($folios as $folio){
          $sql .= "({$ID_Embarque}, '{$folio}', NULL)";
          if($i < $total_folios){
            $sql .= ",";
          }
          $i ++;
        }
        $query = mysqli_query(\db2(), $sql);
        return $query;
      }else{
        return $query;
      }
    }
    return true;
  }

  function borrarEmbarque( $data ) {
    $sql = '
        UPDATE
          ' . self::TABLE_EMBARQUE . '
        SET
          Activo = 0
        WHERE
          ID_OEmbarque = ?
      ';
    $this->save = \db()->prepare($sql);
    $this->save->execute( array(
      $data['ID_Embarque']
    ) );
  }

  /*function settings_design( $data ) {

      $sql = '
        UPDATE
          ' . self::TABLE . '
        SET
          Empresa = ?
        , VendId = ?
        , ID_Externo = ?
        WHERE
          ID_Proveedor = ?
      ';

      $this->save = \db()->prepare($sql);
      $this->save->execute( array(
        $data['Empresa']
      , $data['VendId']
      , $data['ID_Externo']
      ) );

    }*/


  function informe($factura){
    $sql='SELECT
            *
        FROM
            V_RepCDPuntoV
        where FacturaMadre="'.$factura.'"';

    $sth = \db()->prepare( $sql );

    $sth->execute();
    return $sth->fetchAll();


  }


  public function inUse( $data ) {

    $sql= "SELECT ID_OEmbarque from `td_ordenembarque` where ID_OEmbarque = '".$data['ID_Embarque']."'";
    $sth = \db()->prepare($sql);
    $sth->execute();
    $data = $sth->fetch();

    if ($data['ID_OEmbarque']) 
      return true;
    else
      return false;
  }

}
