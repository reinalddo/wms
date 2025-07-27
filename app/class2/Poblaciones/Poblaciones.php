<?php

  namespace Poblaciones;

  class Poblaciones {

    const TABLE = 'c_poblacion';
    const TABLE_ESTADOS = 'c_estado';
    var $identifier;

    public function __construct( $cve_pobla = false, $key = false ) {

      if( $cve_pobla ) {
        $this->cve_pobla = (int) $cve_pobla;
      }

      if($key) {

        $sql = sprintf('
          SELECT
            cve_pobla
          FROM
            %s
          WHERE
            cve_pobla = ?
        ',
          self::TABLE
        );

        $sth = \db()->prepare($sql);
        $sth->setFetchMode(\PDO::FETCH_CLASS, '\Poblaciones\Poblaciones');
        $sth->execute(array($key));

        $poblaciones = $sth->fetch();

        $this->cve_pobla = $poblaciones->cve_pobla;

      }

    }

    private function load() {

      $sql = sprintf('
        SELECT
          *
        FROM
          %s
        WHERE
          cve_pobla = ?
      ',
        self::TABLE
      );

      $sth = \db()->prepare( $sql );
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\Poblaciones\Poblaciones' );
      $sth->execute( array( $this->cve_pobla ) );

      $this->data = $sth->fetch();

    }

    function __get( $key ) {

      switch($key) {
        case 'cve_pobla':
        case 'cve_estado':
        case 'des_pobla':
          $this->load();
          return @$this->data->$key;
        default:
          return $this->key;
      }

    }

      function save( $_post ) {
          try {
              $sql = "INSERT INTO " . self::TABLE . " (cve_estado, des_pobla, Activo)";
              $sql .= "Values (";
              $sql .= "'".$_post['cve_estado']."',";
              $sql .= "'".$_post['des_pobla']."', '1');";

              $rs = mysqli_query(\db2(), $sql) or die("Error description: " . mysqli_error(\db2()));

          } catch(Exception $e) {
              return 'ERROR: ' . $e->getMessage();
          }
      }

    function getAll() {

          $sql = 'SELECT * FROM  ' . self::TABLE_ESTADOS . ' ';

          $sth = \db()->prepare( $sql );
          $sth->setFetchMode( \PDO::FETCH_CLASS, '\Poblaciones\Poblaciones' );
          $sth->execute( array( cve_pobla ) );

          return $sth->fetchAll();

    }

      function actualizarPoblaciones( $_post ) {
          try {
              $sql = "UPDATE " . self::TABLE . " SET cve_estado='".$_post['cve_estado']."', des_pobla='".$_post['des_pobla']."' WHERE cve_pobla='".$_post['cve_pobla']."';";

              $rs = mysqli_query(\db2(), $sql) or die("Error description: " . mysqli_error(\db2()));
          } catch(Exception $e) {
              return 'ERROR: ' . $e->getMessage();
          }
      }

      function borrarPoblaciones( $data ) {
          $sql = '
        UPDATE
          ' . self::TABLE . '
        SET
          Activo = 0
        WHERE
          cve_pobla = ?
      ';
          $this->save = \db()->prepare($sql);
          $this->save->execute( array(
              $data['cve_pobla']
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
