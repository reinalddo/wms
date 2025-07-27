<?php

namespace QaAuditoria;

class QaAuditoria {

    const TABLE = 'th_incidencia';
    var $identifier;

    public function __construct( $ID_Incidencia = false, $key = false ) {

        if( $ID_Incidencia ) {
            $this->ID_Incidencia = (int) $ID_Incidencia;
        }

        if($key) {

            $sql = sprintf('
          SELECT
            ID_Incidencia
          FROM
            %s
          WHERE
            ID_Incidencia = ?
        ',
                self::TABLE
            );

            $sth = \db()->prepare($sql);
            $sth->setFetchMode(\PDO::FETCH_CLASS, '\QaAuditoria\QaAuditoria');
            $sth->execute(array($key));

            $ID_Incidencia = $sth->fetch();

            $this->ID_Incidencia = $ID_Incidencia->ID_Incidencia;

        }

    }

    private function load() {

        $sql = sprintf('
        SELECT
          *
        FROM
          %s
        WHERE
          ID_Incidencia = ?
      ',
            self::TABLE
        );

        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\QaAuditoria\QaAuditoria' );
        $sth->execute( array( $this->ID_Incidencia ) );

        $this->data = $sth->fetch();

    }

    function __get( $key ) {

        switch($key) {
            case 'ID_Incidencia':
                $this->load();
                return @$this->data->$key;
            default:
                return $this->key;
        }

    }

    function save( $data ) {

			$sql = sprintf('
			INSERT INTO
			  ' . self::TABLE . '
			SET
				Fol_folio = :Fol_folio,
				ReportadoCas = :ReportadoCas,
				Descripcion = :Descripcion,
				Respuesta = :Respuesta,
				status = :status,
				Fecha = :Fecha
		  ');

			$this->save = \db()->prepare($sql);

			$this->save->bindValue( ':Fol_folio', $data['Fol_folio'], \PDO::PARAM_STR );
			$this->save->bindValue( ':ReportadoCas', $data['ReportadoCas'], \PDO::PARAM_STR );
			$this->save->bindValue( ':Descripcion', $data['Descripcion'], \PDO::PARAM_STR );
			$this->save->bindValue( ':Respuesta', $data['Respuesta'], \PDO::PARAM_STR );
			$this->save->bindValue( ':status', $data['status'], \PDO::PARAM_STR );
			$this->save->bindValue( ':Fecha', $data['Fecha'], \PDO::PARAM_STR );
			
			   $this->save->execute();

    
	}
	
    function getAll() {

        $sql = '
        SELECT
          *
        FROM
          ' . self::TABLE . '
      ';

        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\QaAuditoria\QaAuditoria' );
        $sth->execute( array( ID_Incidencia ) );

        return $sth->fetchAll();

    }	

    function borrar( $data ) {
        $sql = '
        UPDATE
          ' . self::TABLE . '
        SET
          Activo = 0
        WHERE
          ID_Incidencia = ?
      ';
        $this->save = \db()->prepare($sql);
        $this->save->execute( array(
            $data['ID_Incidencia']
        ) );
    }

    function actualizarIncidencia( $data ) {
		$sql = "UPDATE " . self::TABLE . " 
		SET
			Fol_folio = '".$data['Fol_folio']."', 
			ReportadoCas = '".$data['ReportadoCas']."',
			Descripcion = '".$data['Descripcion']."',
			Respuesta = '".$data['Respuesta']."',
			status = '".$data['status']."',
			Fecha = '".$data['Fecha']."'				
		WHERE ID_Incidencia = '".$data['ID_Incidencia']."'";
        $rs = mysqli_query(\db2(), $sql) or die(mysqli_error(\db2()));
    }
	
	function loadClave() {

      $sql = sprintf('
        SELECT
          *
        FROM
          %s
        WHERE
          clave = ?
      ',
        self::TABLE
      );

      $sth = \db()->prepare( $sql );
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\QaAuditoria\QaAuditoria' );
      $sth->execute( array( $this->clave ) );

      $this->data = $sth->fetch();

    }
	
	function validaClave( $key ) {
      switch($key) {
        case 'ID_Incidencia':
        case 'clave':
          $this->loadClave();
          return @$this->data->$key;
        default:
          return $this->key;
      }

    }
	
	function traerProxId() {

		$sql = "show table status where name = 'th_incidencia';";

        $sth = \db()->prepare( $sql );
        $sth->execute( array( Auto_increment ) );
		$this->data = $sth->fetch();
		//var_dump($this->data); exit;
		return @$this->data;

    }
	
	function loadMesas($clave) {

       $sql = '
		SELECT 
			p.nombre as almacen, 
			u.descripcion as descripcion, 
			u.cve_ubicacion as clave
		FROM c_almacenp p, t_ubicaciones_revision u 
		where u.cve_almac = p.clave
		and p.clave = "'.$clave.'" 
		and p.activo = 1 and u.activo =1;
		';

        $sth = \db()->prepare( $sql );
		$sth->execute();
		return $sth->fetchAll();

    }
	
	function loadAuditoria($folio,$pedido) {
        $f="";
        if($pedido!=''){
            $f.=" and p.Fol_folio='".$pedido."'";
        }
        
        if($folio!=''){
            $f.=" and p.id_pedido='".$folio."'";
        }
        
       $sql = '
			SELECT p.Fol_folio, c.RazonSocial, u.descripcion, (SELECT 			
					SUM(td_pedido.Num_cantidad) AS pedidas
					FROM td_pedido, th_pedido, c_articulo, c_lotes
					WHERE td_pedido.Fol_folio=th_pedido.Fol_folio AND
					c_articulo.cve_articulo=td_pedido.Cve_articulo AND c_lotes.cve_articulo=c_articulo.cve_articulo			
					AND th_pedido.Fol_folio = p.Fol_folio) AS totalFact
			FROM th_pedido p
			LEFT JOIN t_ubicaciones_revision u ON p.cve_ubicacion = u.cve_ubicacion
			LEFT JOIN c_cliente c ON p.Cve_clte = c.Cve_Clte 
			WHERE 1 '.$f;

        $sth = \db()->prepare( $sql );
		$sth->execute();
		return $sth->fetchAll();
    }	

	
	
}

