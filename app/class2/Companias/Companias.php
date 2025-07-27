<?php

  namespace Companias;

  class Companias {

    const TABLE = 'c_almacen';
    const COMPANIA = 'c_compania';
    const TIPO = 'c_tipocia';

    var $identifier;
    var $clavecomp;

    public function __construct( $cve_almac = false, $key = false ) {

      if( $cve_almac ) {
        $this->cve_almac = (int) $cve_almac;
      }

      if($key) {

        $sql = sprintf('
          SELECT
            cve_almac
          FROM
            %s
          WHERE
            cve_almac = ?
        ',
          self::TABLE
        );

        $sth = \db()->prepare($sql);
        $sth->setFetchMode(\PDO::FETCH_CLASS, '\Companias\Companias');
        $sth->execute(array($key));

        $almacen = $sth->fetch();

        $this->cve_almac = $almacen->cve_almac;

      }

    }

    private function load() {

      $sql = sprintf('
        SELECT
          *
        FROM
          c_compania
        WHERE
          cve_cia = ?
      ',
        self::TABLE
      );

      $sth = \db()->prepare( $sql );
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\Companias\Companias' );
      $sth->execute( array( $this->cve_cia ) );

      $this->data = $sth->fetch();

    }
	
		   function exist($clave_empresa) {

      $sql = sprintf('
        SELECT
          *
        FROM
          c_compania
        WHERE
          clave_empresa = ?
      ',
        self::TABLE
      );

      $sth = \db()->prepare( $sql );
	  
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\Companias\Companias' );
      $sth->execute( array( $clave_empresa ) );

      $this->data = $sth->fetch();
	  
	  if(!$this->data)
		  return false; 
				else 
					return true;
    }
	

    function loadcomp() {

          $sql = sprintf('
        SELECT
          *
        FROM
          c_compania
        WHERE
          cve_cia = ?
      ',
              self::TABLE
          );

          $sth = \db()->prepare( $sql );
          $sth->setFetchMode( \PDO::FETCH_CLASS, '\Companias\Companias' );
          $sth->execute( array( $this->cve_cia ) );

          $this->data = $sth->fetch();

    }

      function getAll() {

        $sql = '
        SELECT
          *
        FROM
          c_compania
		  where Activo=1
      ';

        $sth = \db()->prepare( $sql );
      //  $sth->setFetchMode( \PDO::FETCH_CLASS, '\Companias\Companias' );
        $sth->execute();

        return $sth->fetchAll();

    }

      function getPoblacion() {

          $sql = '
        SELECT
          *
        FROM
          c_poblacion
      ';

          $sth = \db()->prepare( $sql );
          $sth->setFetchMode( \PDO::FETCH_CLASS, '\Companias\Companias' );
          @$sth->execute( array( cve_pobla ) );

          return $sth->fetchAll();

      }

      function borrarCompania( $data ) {
          $sql = '
        UPDATE
          ' . self::COMPANIA . '
        SET
          Activo = 0
        WHERE
          cve_cia = ?
      ';
          $this->save = \db()->prepare($sql);
          $this->save->execute( array(
              $data['cve_cia']
          ) );
      }

      function getCompania() {

          $sql = '
        SELECT
          *
        FROM
          c_tipocia
		  where Activo=1
      ';

          $sth = \db()->prepare( $sql );
          $sth->setFetchMode( \PDO::FETCH_CLASS, '\Companias\Companias' );
          @$sth->execute( array( cve_cia ) );

          return $sth->fetchAll();

      }

      function getComp() {

          $sql = '
        SELECT
          *
        FROM
          c_compania
		  where Activo=1
      ';

          $sth = \db()->prepare( $sql );
          $sth->setFetchMode( \PDO::FETCH_CLASS, '\Companias\Companias' );
          @$sth->execute( array( cve_cia ) );

          return $sth->fetchAll();

      }

      function getNCompania() {

          $sql = sprintf('
        SELECT
          *
        FROM
          c_compania
        WHERE
          cve_cia = ?
      ',
              self::COMPANIA
          );

          $sth = \db()->prepare( $sql );
          $sth->setFetchMode( \PDO::FETCH_CLASS, '\Companias\Companias' );
          $sth->execute( array( $this->clavecomp ) );

          $this->data = $sth->fetch();

      }
    public function getTipos(){
      $sql = 'SELECT cve_cia, des_tipcia FROM '.self::TIPO.' WHERE Activo = 1';
      $sth = \db()->prepare( $sql );
      $sth->execute();
      return $sth->fetchAll();
    }
	
    function __get( $key ) {

      switch($key) {
        case 'cve_cia':
          $this->load();
          return @$this->data->$key;
        default:
          return $this->key;
      }

    }

    function save( $data ) {
    
    $sql2 = "INSERT INTO c_compania
              SET
          clave_empresa = '{$data['cve_empresa']}'
        , distrito = '{$data['distrito']}'
        , cve_tipcia = '{$data['cve_tipcia']}'
        , des_cia = '{$data['des_cia']}'
        , des_rfc = '{$data['des_rfc']}'
        , des_direcc = '{$data['des_direcc']}'
        , des_cp = '{$data['des_cp']}'
        , des_telef = '{$data['des_telef']}'
        , des_contacto = '{$data['des_contacto']}'
        , des_email = '{$data['des_email']}'
        , des_observ = '{$data['des_observ']}'
		    , imagen = '".'/img/compania/'.$data['imagen']."';";
      
      
     /* $sql = sprintf('
        INSERT INTO
          ' . self::COMPANIA . '
        SET
          clave_empresa = :clave_empresa
        , distrito = :distrito
        , cve_tipcia = :tipocia
        , cve_cia = :cve_cia
        , des_cia = :des_cia
        , des_rfc = :des_rfc
        , des_direcc = :des_direcc
        , des_cp = :des_cp
        , des_telef = :des_telef										
        , des_contacto = :des_contacto
        , des_email = :des_email
        , des_observ = :des_observ    
		, imagen = :"/img/compania/'.$data["imagen"].'"  		
      ');*/
      
      $this->save = \db()->prepare($sql2);
      $this->save->execute();
/*
      //$this->save->bindValue( ':ID_Proveedor', $this->ID_Proveedor, \PDO::PARAM_STR );
      $this->save->bindValue( ':clave_empresa', $data['cve_empresa'], \PDO::PARAM_STR );
      $this->save->bindValue( ':distrito', $data['distrito'], \PDO::PARAM_STR );
      $this->save->bindValue( ':tipocia', $data['cve_tipcia'], \PDO::PARAM_STR );
      $this->save->bindValue( ':cve_cia', $data['cve_cia'], \PDO::PARAM_STR );
      $this->save->bindValue( ':des_cia', $data['des_cia'], \PDO::PARAM_STR );
      $this->save->bindValue( ':des_rfc', $data['des_rfc'], \PDO::PARAM_STR );
      $this->save->bindValue( ':des_direcc', $data['des_direcc'], \PDO::PARAM_STR );
      $this->save->bindValue( ':des_cp', $data['des_cp'], \PDO::PARAM_STR );
      $this->save->bindValue( ':des_telef', $data['des_telef'], \PDO::PARAM_STR );
      $this->save->bindValue( ':des_contacto', $data['des_contacto'], \PDO::PARAM_STR );
      $this->save->bindValue( ':des_email', $data['des_email'], \PDO::PARAM_STR );
      $this->save->bindValue( ':des_observ', $data['des_observ'], \PDO::PARAM_STR );
	    $this->save->bindValue( ':imagen', $data['imagen'], \PDO::PARAM_STR );
*/

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

	function actualizarCompa( $data ) {
		//var_dump($data); exit;
      $sql = '
        UPDATE
          ' . self::COMPANIA . '
        SET
          clave_empresa = ?
        , distrito = ?
        , cve_cia = ?
        , cve_tipcia = ?
        , des_cia = ?
        , des_rfc = ?
        , des_direcc = ?
        , des_cp = ?
        , des_telef = ?
        , des_contacto = ?
        , des_email = ?												
        , des_observ = ?  
		    , imagen = "'.$data["imagen"].'"  		
        WHERE
          cve_cia = ?
      ';
      $this->save = \db()->prepare($sql);
      $this->save->execute( array(
          $data['cve_empresa']
        , $data['distrito']
        , $data['cve_cia']
        , $data['cve_tipcia']
        , $data['des_cia']
        , $data['des_rfc']
        , $data['des_direcc']
        , $data['des_cp']
        , $data['des_telef']
        , $data['des_contacto']
        , $data['des_email']
        , $data['des_observ']
        , $data['cve_cia']
      ) );
    }
    function tieneAlmacen() {
      $sql = sprintf('
        SELECT
          r.*
        FROM
          c_compania cr, c_almacenp r
        WHERE
          cr.cve_cia = r.cve_cia 
          and cr.Activo = 1 and r.Activo = 1
          and cr.cve_cia = ?
      ',
        self::TABLE
      );

      $sth = \db()->prepare( $sql );
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\Companias\Companias' );
      $sth->execute( array( $this->cve_cia ) );

      $this->data = $sth->fetch();

    }


  function recovery( $data ) {

          $sql = "UPDATE c_compania SET Activo = 1 WHERE  cve_cia='".$data['cve_cia']."';";
          $this->delete = \db()->prepare($sql);
          $this->delete->execute( array(
              $data['cve_cia']
          ) );
  }
}

