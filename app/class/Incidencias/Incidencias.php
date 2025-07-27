<?php

namespace Incidencias;

class Incidencias {

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
            $sth->setFetchMode(\PDO::FETCH_CLASS, '\Incidencias\Incidencias');
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
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\Incidencias\Incidencias' );
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

    function save($data) 
    {
      extract($data);
      $fecha_recibo = date('Y-m-d', strtotime($fecha_recibo));
      $fecha_accion = date('Y-m-d', strtotime($fecha_accion));
      
      if(!$cliente) $cliente = $desc_cliente;

			$sql = "
        			INSERT INTO
        				th_incidencia
        			SET
        				Fol_folio = '$Fol_folio',
        				cliente = '$cliente',
        				centro_distribucion = '$centro_distribucion',
        				tipo_reporte = '$tipo_reporte',
        				reportador = '$reportador',
        				cargo_reportador = '$cargo_reportador',
        				Fecha = '$fecha_recibo',
        				Descripcion = '$descripcion',
        				responsable_recibo = '$responsable_recibo',
        				responsable_caso = '$responsable_caso',
        				plan_accion = '$plan_accion',
        				responsable_plan = '$responsable_plan',
        				Fecha_accion = '$fecha_accion',
        				responsable_verificacion = '$responsable_verificacion',
                id_motivo_registro = '$mot_apertura',
                desc_motivo_registro = '$desc_apertura',
                id_motivo_cierre = '$mot_cierre',
                desc_motivo_cierre = '$desc_cierre',
                status = '$status'
		  ";
      //imagen = '$foto'
			$this->save = \db()->prepare($sql);
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
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\Incidencias\Incidencias' );
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
      extract($data);
      $fecha_accion = date('Y-m-d', strtotime($fecha_accion));
      $sql_cliente = "cliente = '$cliente',";
      if(!$cliente) {$cliente = $desc_cliente; $sql_cliente = "";}

  		$sql = "UPDATE th_incidencia
              SET
                Fol_folio = '$Fol_folio',
                {$sql_cliente}
                centro_distribucion = '$centro_distribucion',
                tipo_reporte = '$tipo_reporte',
                reportador = '$reportador',
                cargo_reportador = '$cargo_reportador',
                Descripcion = '$descripcion',
                responsable_recibo = '$responsable_recibo',
                responsable_caso = '$responsable_caso',
                plan_accion = '$plan_accion',
                responsable_plan = '$responsable_plan',
                Fecha_accion = '$fecha_accion',
                responsable_verificacion = '$responsable_verificacion',
                id_motivo_registro = '$mot_apertura',
                desc_motivo_registro = '$desc_apertura',
                id_motivo_cierre = '$mot_cierre',
                desc_motivo_cierre = '$desc_cierre',
                status = '$status'
          		WHERE ID_Incidencia = '$ID_Incidencia'";
      $this->save = \db()->prepare($sql);
			$this->save->execute();
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
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\Incidencias\Incidencias' );
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
}
