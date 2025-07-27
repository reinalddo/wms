<?php

  namespace AdminOrdenTrabajo;

  class AdminOrdenTrabajo {

    const ORDENPROD = 't_ordenprod';
    const DETALLE = 'td_ordenpro';

    var $identifier;
    var $clavecomp;

    public function __construct( $Folio_Pro = false, $key = false ) {

      if( $Folio_Pro ) {
        $this->Folio_Pro = (int) $Folio_Pro;
      }

      if($key) {

        $sql = sprintf('
          SELECT
            Folio_Pro
          FROM
            %s
          WHERE
            Folio_Pro = ?
        ',
          self::ORDENPROD
        );

        $sth = \db()->prepare($sql);
        $sth->setFetchMode(\PDO::FETCH_CLASS, '\AdminOrdenTrabajo\AdminOrdenTrabajo');
        $sth->execute(array($key));

        $almacen = $sth->fetch();

        $this->Folio_Pro = $almacen->Folio_Pro;

      }

    }
	
		   function detalle($Folio_Pro) {

      $sql = sprintf('
        SELECT
          Cve_Articulo, (select des_articulo from c_articulo where c.Cve_Articulo = cve_articulo) as descripcion, Cve_Lote, Fecha_Prod, Cantidad
        FROM
          td_ordenpro
        WHERE
          Folio_Pro = ?
      ',
        self::DETALLE
      );

      $sth = \db()->prepare( $sql );
	  
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\AdminOrdenTrabajo\AdminOrdenTrabajo' );
      $sth->execute( array( $Folio_Pro ) );

      $this->data = $sth->fetch();
	  
	  if(!$this->data)
		  return false; 
				else 
					return true;
    }
	

   
}

