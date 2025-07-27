<?php

namespace TipoCompania;

class TipoCompania {

    const TABLE = 'c_tipocia';
    var $identifier;

    public function __construct( $cve_tipcia = false, $key = false ) {

        if( $cve_tipcia ) {
            $this->cve_tipcia = (int) $cve_tipcia;
        }

        if($key) {

            $sql = sprintf('
          SELECT
            cve_tipcia
          FROM
            %s
          WHERE
            cve_tipcia = ?
        ',
                self::TABLE
            );

            $sth = \db()->prepare($sql);
            $sth->setFetchMode(\PDO::FETCH_CLASS, '\TipoCompania\TipoCompania');
            $sth->execute(array($key));

            $TipoCompania = $sth->fetch();

            $this->cve_tipcia = $TipoCompania->cve_tipcia;

        }

    }

    private function load() {

        $sql = sprintf('
        SELECT
          *
        FROM
          %s
        WHERE
          cve_tipcia = ?
      ',
            self::TABLE
        );

        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\TipoCompania\TipoCompania' );
        $sth->execute( array( $this->cve_tipcia ) );

        $this->data = $sth->fetch();

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
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\TipoCompania\TipoCompania' );
        $sth->execute( array( $cve_tipcia ) );

        return $sth->fetchAll();

    }

    function __get( $key ) {

        switch($key) {
            case 'cve_tipcia':
            case 'des_tipcia':
                $this->load();
                return @$this->data->$key;
            default:
                return $this->key;
        }

    }

    function save( $_post ) {

        if( !$_post['des_tipcia'] ) { throw new \ErrorException( 'des_tipcia is required.' ); }

        $sql = sprintf('
        INSERT INTO
          ' . self::TABLE . '
        SET       
          des_tipcia = :des_tipcia
      ,clave_tcia= :clave_tcia
      ,es_transportista= :es_transportista
      ');

        $this->save = \db()->prepare($sql);

        $this->save->bindValue( ':des_tipcia', $_post['des_tipcia'], \PDO::PARAM_STR );
    $this->save->bindValue( ':clave_tcia', $_post['clave_tcia'], \PDO::PARAM_STR );
    $this->save->bindValue( ':es_transportista', $_post['transportista'], \PDO::PARAM_STR );

        $this->save->execute();

    }

    function tieneEmpresa() {
      $sql = sprintf('
        SELECT
          cr.*
        FROM
          c_tipocia r, c_compania cr
        WHERE
          cr.cve_tipcia = r.cve_tipcia 
          and cr.Activo = 1
          and r.cve_tipcia = ?
      ',
        self::TABLE
      );

      $sth = \db()->prepare( $sql );
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\TipoCompania\TipoCompania' );
      $sth->execute( array( $this->cve_tipcia ) );

      $this->data = $sth->fetch();

    }

    function borrarTipoCompania( $data ) {
        $sql = '
        UPDATE
          ' . self::TABLE . '
        SET
          Activo = 0
        WHERE
          cve_tipcia = ?
      ';
        $this->save = \db()->prepare($sql);
        $this->save->execute( array(
            $data['cve_tipcia']
        ) );
    }

    function actualizarTipoCompania( $data ) {
        $sql = '
        UPDATE
          ' . self::TABLE . '
        SET
          des_tipcia = ?
          ,es_transportista = ?
        WHERE
          clave_tcia = ?
      ';
        $this->save = \db()->prepare($sql);
        $this->save->execute( array(
            $data['des_tipcia']
            ,$data['transportista']
		, $data['clave_tcia']		
        ) );
    }
	
	 function exist($clave_tcia) {

      $sql = sprintf('
        SELECT
          *
        FROM
          c_tipocia
		  WHERE
          clave_tcia = ?
      ',
        self::TABLE
      );

      $sth = \db()->prepare( $sql );
	  
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\TipoCompania\TipoCompania' );
      $sth->execute( array( $clave_tcia ) );

      $this->data = $sth->fetch();
	  
	  if(!$this->data)
		  return false; 
				else 
					return true;
    }

       function recovery( $data ) {

          $sql = "UPDATE c_tipocia SET Activo = 1 WHERE  cve_tipcia='".$data['cve_tipcia']."';";
          $this->delete = \db()->prepare($sql);
          $this->delete->execute( array(
              $data['cve_tipcia']
          ) );
      }
}
