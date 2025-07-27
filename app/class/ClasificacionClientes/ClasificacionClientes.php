<?php

  namespace ClasificacionClientes;

  class ClasificacionClientes {

    const TABLE = 'c_tipocliente';
    const TABLE2 = 'c_tipocliente2';
    var $identifier;

    public function __construct( $Cve_TipoCte = false, $key = false ) {
/*
      if( $Cve_TipoCte ) {
        $this->Cve_TipoCte = (int) $Cve_TipoCte;
      }
*/
      if($key) {

        $sql = sprintf('
          SELECT
            Cve_TipoCte
          FROM
            %s
          WHERE
            Cve_TipoCte = ?
        ',
          self::TABLE
        );

        $sth = \db()->prepare($sql);
        $sth->setFetchMode(\PDO::FETCH_CLASS, '\ClasificacionClientes\ClasificacionClientes');
        $sth->execute(array($key));

        $subgrupoarticulo = $sth->fetch();

        $this->Cve_TipoCte = $subgrupoarticulo->Cve_TipoCte;

      }

    }

    private function load() {

      $sql = sprintf('
        SELECT
		        * 
            FROM
            %s 
        WHERE
          Cve_TipoCte = ?
      ',
        self::TABLE
      );

      $sth = \db()->prepare( $sql );
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\ClasificacionClientes\ClasificacionClientes' );
      $sth->execute( array( $this->Cve_TipoCte ) );

      $this->data = $sth->fetch();

    }

    private function load2() {

      $sql = sprintf('
        SELECT
            c_tipocliente2.*,
            (SELECT cve_grupo FROM c_gpoclientes WHERE id = (SELECT id_grupo FROM c_tipocliente WHERE id = c_tipocliente2.id_tipocliente)) AS cve_grupo
        FROM
            %s 
        WHERE
          Cve_TipoCte = ?
      ',
        self::TABLE2
      );

      $sth = \db()->prepare( $sql );
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\ClasificacionClientes\ClasificacionClientes' );
      $sth->execute( array( $this->Cve_TipoCte ) );

      $this->data = $sth->fetch();

    }

    function actualizarInputSubgrupoA( $data ) {
        $val = $data["get_option"];
        $sql = sprintf('
        SELECT
          *
        FROM
          %s
        WHERE
          cve_gpoart = ?
      ',
              self::TABLE
          );

        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\ClasificacionClientes\ClasificacionClientes' );
        $sth->execute( array( $val ) );

        return $sth->fetchAll();

    }

    function __get( $key ) {

      switch($key) {
        case 'Cve_TipoCte':
        case 'Des_TipoCte':
          $this->load();
          return @$this->data->$key;
        default:
          return $this->key;
      }

    }
	
      function __get2( $key ) {

      switch($key) {
        case 'Cve_TipoCte':
        case 'Des_TipoCte':
          $this->load2();
          return @$this->data->$key;
        default:
          return $this->key;
      }

    }

    function getAll() {

        $sql = '
        SELECT
          *
        FROM
          ' . self::TABLE . '
		  where Activo=1
      ';

        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\ClasificacionClientes\ClasificacionClientes' );
        $sth->execute( array( $Cve_TipoCte ) );

        return $sth->fetchAll();

    }	

    function getAll2() {

        $sql = '
        SELECT
          *
        FROM
          ' . self::TABLE2 . '
      where Activo=1
      ';

        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\ClasificacionClientes\ClasificacionClientes' );
        $sth->execute( array( $Cve_TipoCte ) );

        return $sth->fetchAll();

    } 

    function save( $_post ) {

      $sql = sprintf('
        INSERT INTO
          ' . self::TABLE . '
        SET
          id_grupo    = :grupo_cliente
        , Cve_TipoCte = :Cve_TipoCte
        , Des_TipoCte = :Des_TipoCte
		
      ');

      $this->save = \db()->prepare($sql);

      //$this->save->bindValue( ':ID_Proveedor', $this->ID_Proveedor, \PDO::PARAM_STR );
      $this->save->bindValue( ':grupo_cliente', $_post['grupo_cliente'], \PDO::PARAM_STR );
      $this->save->bindValue( ':Cve_TipoCte', $_post['Cve_TipoCte'], \PDO::PARAM_STR );
      $this->save->bindValue( ':Des_TipoCte', $_post['Des_TipoCte'], \PDO::PARAM_STR );
      $this->save->execute();

    }

    function save2( $_post ) {

      $sql = sprintf('
        INSERT INTO
          ' . self::TABLE2 . '
        SET
          id_tipocliente = :tipo_cliente
        , Cve_TipoCte    = :Cve_TipoCte
        , Des_TipoCte    = :Des_TipoCte
    
      ');

      $this->save = \db()->prepare($sql);

      //$this->save->bindValue( ':ID_Proveedor', $this->ID_Proveedor, \PDO::PARAM_STR );
      $this->save->bindValue( ':tipo_cliente', $_post['tipo_cliente'], \PDO::PARAM_STR );
      $this->save->bindValue( ':Cve_TipoCte', $_post['Cve_TipoCte'], \PDO::PARAM_STR );
      $this->save->bindValue( ':Des_TipoCte', $_post['Des_TipoCte'], \PDO::PARAM_STR );
      $this->save->execute();

    }

	function actualizarClasificacionClientes( $data ) {
      $sql = '
        UPDATE
          ' . self::TABLE . '
        SET
          id_grupo    = ?
        , Cve_TipoCte = ?
        , Des_TipoCte = ?
        WHERE
          Cve_TipoCte = ?
      ';
      $this->save = \db()->prepare($sql);
      $this->save->execute( array(
        $data['grupo_cliente']
      , $data['Cve_TipoCte']
      , $data['Des_TipoCte']
      , $data['Cve_TipoCte']
      ) );
    }

  function actualizarClasificacionClientes2( $data ) {
      $sql = '
        UPDATE
          ' . self::TABLE2 . '
        SET
          id_tipocliente = ?
        , Cve_TipoCte = ?
        , Des_TipoCte = ?
        WHERE
          Cve_TipoCte = ?
      ';
      $this->save = \db()->prepare($sql);
      $this->save->execute( array(
        $data['tipo_cliente']
      , $data['Cve_TipoCte']
      , $data['Des_TipoCte']
      , $data['Cve_TipoCte']
      ) );
    }

      function borrarSubgrupoArt( $data ) {
          $sql = '
        UPDATE
          ' . self::TABLE . '
        SET
          Activo = 0
        WHERE
          Cve_TipoCte = ?
      ';
          $this->save = \db()->prepare($sql);
          $this->save->execute( array(
              $data['Cve_TipoCte']
          ) );
      }

      function borrarSubgrupoArt2( $data ) {
          $sql = '
        UPDATE
          ' . self::TABLE2 . '
        SET
          Activo = 0
        WHERE
          Cve_TipoCte = ?
      ';
          $this->save = \db()->prepare($sql);
          $this->save->execute( array(
              $data['Cve_TipoCte']
          ) );
      }

	   function exist($clave) {

      $sql = sprintf('
        SELECT
          *
        FROM
			 ' . self::TABLE . '
        WHERE
          Cve_TipoCte = ?
      ',
        self::TABLE
      );

      $sth = \db()->prepare( $sql );

      $sth->execute( array( $clave ) );

      $this->data = $sth->fetch();
	  
	  if(!$this->data)
		  return false; 
				else 
					return true;
    }
	
		function recovery( $data ) {

          $sql = "UPDATE " . self::TABLE . "
		  SET Activo = 1 WHERE  id='".$data['id']."';";
          $this->delete = \db()->prepare($sql);
          $this->delete->execute( array(
              $data['id']
          ) );
    }
	
  public function inUse( $data ) {

      $sql = "SELECT ClienteTipo FROM c_cliente WHERE ClienteTipo = '".$data['Cve_TipoCte']."'";
      $sth = \db()->prepare($sql);
      $sth->execute();
      $data = $sth->fetch();
    
    if ($data['ClienteTipo']) 
        return true;
    else
        return false;
  }  
	
  public function inUse2( $data ) {

      $sql = "SELECT ClienteTipo2 FROM c_cliente WHERE ClienteTipo2 = '".$data['Cve_TipoCte']."'";
      $sth = \db()->prepare($sql);
      $sth->execute();
      $data = $sth->fetch();
    
    if ($data['ClienteTipo2']) 
        return true;
    else
        return false;
  }  

  }
