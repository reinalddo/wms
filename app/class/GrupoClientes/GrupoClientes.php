<?php

namespace GrupoClientes;

class GrupoClientes {

    const TABLE = 'c_gpoclientes';
    var $identifier;
    var $cve_grupo;

    public function __construct( $cve_grupo = false, $key = false ) {

        if( $cve_grupo ) {
            $this->cve_grupo = (int) $cve_grupo;
        }

        if($key) {

            $sql = sprintf('
          SELECT
            cve_grupo
          FROM
            %s
          WHERE
            cve_grupo = ?
        ',
                self::TABLE
            );

            $sth = \db()->prepare($sql);
            $sth->setFetchMode(\PDO::FETCH_CLASS, '\GrupoClientes\GrupoClientes');
            $sth->execute(array($key));

            $grupoarticulos = $sth->fetch();

            $this->cve_grupo = $grupoarticulos->cve_grupo;

        }

    }

    public function load() {

        $sql = sprintf('
        SELECT
          *
        FROM
          %s
        WHERE
          cve_grupo = ?
      ',
            self::TABLE
        );

        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\GrupoClientes\GrupoClientes' );
        $sth->execute( array( $this->cve_grupo ) );

        $this->data = $sth->fetch();

    }

    function getAll() {

        $sql = '
        SELECT
          *
        FROM
          ' . self::TABLE . '
		  WHERE Activo=1
      ';

        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\GrupoClientes\GrupoClientes' );
        $sth->execute( array( $cve_grupo ) );

        return $sth->fetchAll();

    }

    function __get( $key ) {

        switch($key) {
            case 'cve_grupo':
            case 'des_grupo':
                $this->load();
                return @$this->data->$key;
            default:
                return $this->key;
        }

    }

    function save( $data ) {

        if( !$data['codigo'] ) { throw new \ErrorException( 'des_grupo is required.' ); }
        if( !$data['descripcion'] ) { throw new \ErrorException( 'des_grupo is required.' ); }
        //if( !$data['por_depcont'] ) { throw new \ErrorException( 'por_depcont is required.' ); }
        //if( !$data['por_depfical'] ) { throw new \ErrorException( 'por_depfical is required.' ); }

        $sql = sprintf('
        INSERT INTO
          ' . self::TABLE . '
        SET
          cve_grupo = :cve_grupo, 
          des_grupo = :des_grupo
        ');

        $this->save = \db()->prepare($sql);

        /*$password = password_hash( $data['password'], PASSWORD_BCRYPT, array( 'cost' => 11 ) );*/
        $identifier = bin2hex(openssl_random_pseudo_bytes(10));

        $this->identifier = $identifier;

        //$this->save->bindValue( ':ID_Proveedor', $this->ID_Proveedor, \PDO::PARAM_STR );
        $this->save->bindValue( ':cve_grupo', $data['codigo'], \PDO::PARAM_STR );
        $this->save->bindValue( ':des_grupo', $data['descripcion'], \PDO::PARAM_STR );
        //$this->save->bindValue( ':por_depcont', $data['por_depcont'], \PDO::PARAM_STR );
        //$this->save->bindValue( ':por_depfical', $data['por_depfical'], \PDO::PARAM_STR );

        $this->save->execute();

    }

    function actualizarGrupoClientes( $data ) {
        $sql = '
        UPDATE
          ' . self::TABLE . '
        SET
          des_grupo = ?
        WHERE
          cve_grupo = ?
      ';
        $this->save = \db()->prepare($sql);
        $this->save->execute( array(
            $data['descripcion']
            //, $data['por_depcont']
            //, $data['por_depfical']
        , $data['codigo']
        ) );
    }

    function borrarGrupoClientes( $data ) {
        $sql = '
        UPDATE
          ' . self::TABLE . '
        SET
          Activo = 0
        WHERE
          cve_grupo = ?
      ';
        $this->save = \db()->prepare($sql);
        $this->save->execute( array(
            $data['cve_grupo']
        ) );
    }

	function exist($clave) {

      $sql = sprintf('
        SELECT
          *
        FROM
			 ' . self::TABLE . '
        WHERE
          cve_grupo = ?
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

      $sql = "SELECT grupo FROM `c_articulo` WHERE grupo = '".$data['cve_grupo']."'";
      $sth = \db()->prepare($sql);
      $sth->execute();
      $data = $sth->fetch();
    
    if ($data['grupo']) 
        return true;
    else
        return false;
  }  
   
}
