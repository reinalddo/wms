<?php

  namespace PedidosUrgentes;

  class PedidosUrgentes {

    const TABLE = 't_urgencias';
    var $identifier;

    public function __construct( $Clave = false, $key = false ) {

      if( $Clave ) {
        $this->Clave = (int) $Clave;
      }

      if($key) {

        $sql = sprintf('
          SELECT
            Clave
          FROM
            %s
          WHERE
            Clave = ?
        ',
          self::TABLE
        );

        $sth = \db()->prepare($sql);
        $sth->setFetchMode(\PDO::FETCH_CLASS, '\PedidosUrgentes\PedidosUrgentes');
        $sth->execute(array($key));

        $pedidosurgentes = $sth->fetch();

        $this->Clave = $pedidosurgentes->Clave;

      }

    }

    private function load() {

      $sql = sprintf('
        SELECT
          *
        FROM
          %s
        WHERE
          Clave = ?
      ',
        self::TABLE
      );

      $sth = \db()->prepare( $sql );
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\PedidosUrgentes\PedidosUrgentes' );
      $sth->execute( array( $this->Clave ) );

      $this->data = $sth->fetch();

    }

    function __get( $key ) {

      switch($key) {
        case 'Clave':
        case 'fol_folio':
        case 'descripcion':
        case 'Fecha':
          $this->load();
          return @$this->data->$key;
        default:
          return $this->key;
      }

    }

      function save( $data ) {
          try {
              $newDate = date("Y-m-d H:i:s", strtotime($data['Fecha']));
              $sql = mysqli_query(\db2(), "SELECT SPAD_AgregaUrgencia(
		      '".$data['fol_folio']."'
		    , '".$data['descripcion']."'
		    , '".$newDate."'
		    );") or die(mysqli_error(\db2()));
          } catch(PDOException $e) {
              return 'ERROR: ' . $e->getMessage();
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

      function actualizarPedidosUrgentes( $data ) {
          $sql = mysqli_query(\db2(), "SELECT SPAD_AgregaUrgencia(
		      '".$data['Clave']."'
		    , '".$data['fol_folio']."'
		    , '".$data['descripcion']."'
		    , '".$data['Fecha']."'	    
		    );") or die(mysqli_error(\db2()));
          $this->save = \db()->prepare($sql);
          $this->save->execute( array(
              $data['fol_folio'],
              $data['descripcion'],
              $data['Fecha']
          ) );
      }

      function borrarPedidosUrgentes( $data ) {
          $sql = '
        UPDATE
          ' . self::TABLE . '
        SET
          Activo = 0
        WHERE
          Clave = ?
      ';
          $this->save = \db()->prepare($sql);
          $this->save->execute( array(
              $data['Clave']
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

  }
