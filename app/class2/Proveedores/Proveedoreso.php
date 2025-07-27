<?php

namespace Proveedores;

class Proveedores {

    const TABLE = 'c_proveedores';
    var $identifier;

    public function __construct( $id_proveedor = false, $key = false ) {

        if( $id_proveedor ) {
            $this->id_proveedor = (int) $id_proveedor;
        }

        if($key) {

            $sql = sprintf('
          SELECT
            ID_Proveedor
          FROM
            %s
          WHERE
            ID_Proveedor = ?
        ',
                self::TABLE
            );

            $sth = \db()->prepare($sql);
            $sth->setFetchMode(\PDO::FETCH_CLASS, '\Proveedores\Proveedores');
            $sth->execute(array($key));

            $proveedor = $sth->fetch();

            $this->ID_Proveedor = $proveedor->ID_Proveedor;

        }

    }

    function getAll() {

        $sql = '
        SELECT
          *
        FROM
          ' . self::TABLE . '
      ';

        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\Proveedores\Proveedores' );
        $sth->execute( array( $id_user ) );

        return $sth->fetchAll();

    }

    private function load() {

        $sql = sprintf('
        SELECT
          *
        FROM
          %s
        WHERE
          ID_Proveedor = ?
      ',
            self::TABLE
        );

        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\Proveedores\Proveedores' );
        $sth->execute( array( $this->ID_Proveedor ) );

        $this->data = $sth->fetch();

    }

    function __get( $key ) {

        switch($key) {
            case 'ID_Proveedor':
            case 'Empresa':
            case 'VendId':
                $this->load();
                return @$this->data->$key;
            default:
                return $this->key;
        }

    }

    function save( $_post ) {

        if( !$_post['nombre_proveedor'] ) { throw new \ErrorException( 'Full name is required.' ); }
        if( !$_post['contacto'] ) { throw new \ErrorException( 'Cant really register without ID.' ); }

        $sql = sprintf('
        INSERT INTO
          ' . self::TABLE . '
        SET
          Empresa = :Empresa
        , VendId = :VendId
      ');

        $this->save = \db()->prepare($sql);

        /*$password = password_hash( $data['password'], PASSWORD_BCRYPT, array( 'cost' => 11 ) );*/
        //$identifier = bin2hex(openssl_random_pseudo_bytes(10));

        $this->identifier = $identifier;

        //$this->save->bindValue( ':ID_Proveedor', $this->ID_Proveedor, \PDO::PARAM_STR );
        $this->save->bindValue( ':Empresa', $_post['nombre_proveedor'], \PDO::PARAM_STR );
        $this->save->bindValue( ':VendId', $_post['contacto'], \PDO::PARAM_STR );

        $this->save->execute();

    }

    function actualizarProveedor( $data ) {
        $sql = '
        UPDATE
          ' . self::TABLE . '
        SET
          Empresa = ?
        , VendId = ?
        WHERE
          ID_Proveedor = ?
      ';
        $this->save = \db()->prepare($sql);
        $this->save->execute( array(
          $data['nombre_proveedor']
        , $data['contacto']
        , $data['ID_Proveedor']
        ) );
    }
	
    function borrarProveedor( $data ) {
        $sql = '
        UPDATE
          ' . self::TABLE . '
        SET
          Activo = 0
        WHERE
          ID_Proveedor = ?
      ';
        $this->save = \db()->prepare($sql);
        $this->save->execute( array(
          $data['ID_Proveedor']
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
