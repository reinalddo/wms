<?php

  namespace AlmacenP;

  class AlmacenP {

    const TABLE = 'c_almacenp';
    var $identifier;

    public function __construct( $id = false, $key = false ) {

      if( $id ) {
        $this->id = (int) $id;
      }

      if($key) {

        $sql = sprintf('
          SELECT
            id
          FROM
            %s
          WHERE
            id = ?
        ',
          self::TABLE
        );

        $sth = \db()->prepare($sql);
        $sth->setFetchMode(\PDO::FETCH_CLASS, '\Almacen\Almacen');
        $sth->execute(array($key));

        $almacenp = $sth->fetch();

        $this->id = $almacenp->id;

      }

    }

    private function load() {

      $sql = sprintf('
        SELECT
          *
        FROM
          %s
        WHERE
          id = ?
      ',
        self::TABLE
      );

      $sth = \db()->prepare( $sql );
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\Almacen\Almacen' );
      $sth->execute( array( $this->id ) );

      $this->data = $sth->fetch();

    }
	
	function exist($clave) {

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
	  
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\AlmacenP\AlmacenP' );
      $sth->execute( array( $clave ) );

      $this->data = $sth->fetch();
	  
	  if(!$this->data)
		  return false; 
				else 
					return true;
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
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\AlmacenP\AlmacenP' );
        $sth->execute( array( cve_almac ) );

        return $sth->fetchAll();

    }
	
    function __get( $key ) {

      switch($key) {
case 'id':
case 'clave':
case 'nombre':
case 'rut':
case 'codigopostal':
case 'distrito':
case 'telefono':
case 'contacto':
case 'correo_electronico':
case 'comentarios':
case 'cve_talmacen':
case 'BL':

		
          $this->load();
          return @$this->data->$key;
        default:
          return $this->key;
      }

    }

    function save( $_post ) 
    {
      if( !$_post['clave'] ) { throw new \ErrorException( 'clave is required.' ); }
	  
	  
      $sql = sprintf('
        INSERT INTO
          ' . self::TABLE . '
        SET
       clave= :clave
      ,nombre= :nombre
      ,rut= :rut 
      ,codigopostal= :codigopostal 
      ,distrito= :distrito 
      ,direccion= :direccion 
      ,telefono= :telefono 
      ,contacto= :contacto 
      ,correo = :correo 
      ,comentarios= :comentarios 
      ,cve_talmacen= :cve_talmacen 
      ,cve_cia= :cve_cia
      ,BL = :code
      ,BL_Pasillo = :pasillo
      ,BL_Rack = :rack
      ,BL_Nivel = :nivel
      ,BL_Seccion =:seccion
      ,BL_Posicion = :posicion

      ');

        $this->save = \db()->prepare($sql);

        //$this->save->bindValue( ':ID_Proveedor', $this->ID_Proveedor, \PDO::PARAM_STR );
        $this->save->bindValue( ':clave', $_post['clave'], \PDO::PARAM_STR );
        $this->save->bindValue( ':rut', $_post['rut'], \PDO::PARAM_STR );
        $this->save->bindValue( ':nombre', $_post['nombre'], \PDO::PARAM_STR );
        $this->save->bindValue( ':codigopostal', $_post['codigopostal'], \PDO::PARAM_STR );
        $this->save->bindValue( ':distrito', $_post['distrito'], \PDO::PARAM_STR );
        $this->save->bindValue( ':telefono', $_post['telefono'], \PDO::PARAM_STR );
        $this->save->bindValue( ':contacto', $_post['contacto'], \PDO::PARAM_STR );
        $this->save->bindValue( ':correo', $_post['correo'], \PDO::PARAM_STR );
        $this->save->bindValue( ':comentarios', $_post['comentarios'], \PDO::PARAM_STR );
        $this->save->bindValue( ':direccion', $_post['direccion'], \PDO::PARAM_STR );
        $this->save->bindValue( ':cve_talmacen', $_post['cve_talmacen'], \PDO::PARAM_STR );
        $this->save->bindValue( ':cve_cia', $_post['cve_cia'], \PDO::PARAM_STR );
      
        $this->save->bindValue( ':code', $_post['code'], \PDO::PARAM_STR );
        $this->save->bindValue( ':pasillo', $_post['pasillo'], \PDO::PARAM_STR );
        $this->save->bindValue( ':rack', $_post['rack'], \PDO::PARAM_STR );
        $this->save->bindValue( ':nivel', $_post['nivel'], \PDO::PARAM_STR );
        $this->save->bindValue( ':seccion', $_post['seccion'], \PDO::PARAM_STR );
        $this->save->bindValue( ':posicion', $_post['posicion'], \PDO::PARAM_STR );
   

        $this->save->execute();
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

	function actualizarAlmacenP( $_post ) {
    
        //echo var_dump($_post);
        //die();

        if( !$_post['clave'] ) { throw new \ErrorException( 'clave is required.' ); }
	  
	  
        $sql = sprintf('
            UPDATE 
              ' . self::TABLE . '
            SET
            clave= :clave
            ,nombre= :nombre
            ,rut= :rut 
            ,codigopostal= :codigopostal 
            ,distrito= :distrito 
            ,telefono= :telefono 
            ,contacto= :contacto 
            ,correo = :correo 
            ,comentarios= :comentarios 
            ,cve_talmacen= :cve_talmacen 
            ,direccion= :direccion 
            ,cve_cia= :cve_cia 
            ,BL = :code
            ,BL_Pasillo = :pasillo
            ,BL_Rack = :rack
            ,BL_Nivel = :nivel
            ,BL_Seccion =:seccion
            ,BL_Posicion = :posicion
        
    		where id= :id

          ');

        $this->save = \db()->prepare($sql);

        //$this->save->bindValue( ':ID_Proveedor', $this->ID_Proveedor, \PDO::PARAM_STR );
        $this->save->bindValue( ':clave', $_post['clave'], \PDO::PARAM_STR );
        $this->save->bindValue( ':nombre', $_post['nombre'], \PDO::PARAM_STR );
        $this->save->bindValue( ':rut', $_post['rut'], \PDO::PARAM_STR );
        $this->save->bindValue( ':codigopostal', $_post['codigopostal'], \PDO::PARAM_STR );
        $this->save->bindValue( ':distrito', $_post['distrito'], \PDO::PARAM_STR );
        $this->save->bindValue( ':telefono', $_post['telefono'], \PDO::PARAM_STR );
        $this->save->bindValue( ':contacto', $_post['contacto'], \PDO::PARAM_STR );
        $this->save->bindValue( ':correo', $_post['correo'], \PDO::PARAM_STR );
        $this->save->bindValue( ':comentarios', $_post['comentarios'], \PDO::PARAM_STR );
        $this->save->bindValue( ':direccion', $_post['direccion'], \PDO::PARAM_STR );
        $this->save->bindValue( ':id', $_post['id'], \PDO::PARAM_STR );
        $this->save->bindValue( ':cve_talmacen', $_post['cve_talmacen'], \PDO::PARAM_STR );
        $this->save->bindValue( ':cve_cia', $_post['cve_cia'], \PDO::PARAM_STR );
    
        $this->save->bindValue( ':code', $_post['code'], \PDO::PARAM_STR );
        $this->save->bindValue( ':pasillo', $_post['pasillo'], \PDO::PARAM_STR );
        $this->save->bindValue( ':rack', $_post['rack'], \PDO::PARAM_STR );
        $this->save->bindValue( ':nivel', $_post['nivel'], \PDO::PARAM_STR );
        $this->save->bindValue( ':seccion', $_post['seccion'], \PDO::PARAM_STR );
        $this->save->bindValue( ':posicion', $_post['posicion'], \PDO::PARAM_STR );

        $this->save->execute();
    }

    function borrarAlmacenP( $data ) {
          $sql = '
        UPDATE
          ' . self::TABLE . '
        SET
          Activo = 0
        WHERE
          id = ?
      ';
          $this->save = \db()->prepare($sql);
          $this->save->execute( array(
              $data['id']
          ) );
    }
	
	function loadZonas($clave) {

     $sql = '
       SELECT 
        p.nombre as almacen, 
        a.des_almac as nombre_zona, 
            a.cve_almac as clave_zona,
          a.clave_almacen as clave_zona2
        FROM c_almacenp p, c_almacen a 
        where a.cve_almacenp = p.id 
        and p.clave = "'.$clave.'" 
        and p.activo = 1 and a.activo =1 order by clave_zona asc;
    ';

    $sth = \db()->prepare( $sql );
    $sth->execute();
    return $sth->fetchAll();
  }
  
  function loadProveedores($clave_almacen){
    
      $sql ='
          SELECT 
                  c_proveedores.Nombre,
                  ts_existenciapiezas.ID_Proveedor
          FROM ts_existenciapiezas
          inner join c_proveedores on c_proveedores.ID_Proveedor = ts_existenciapiezas.ID_Proveedor
          where cve_almac = (select id from c_almacenp where clave = "'.$clave_almacen.'")
          group by ts_existenciapiezas.ID_Proveedor
          order by c_proveedores.Nombre
                ';
      $sth = \db()->prepare( $sql );
      $sth->execute();
      return $sth->fetchAll();
  }

    function tieneEmpresa() {
        $sql = sprintf('
            SELECT
              cr.*
            FROM
              c_almacenp cr, c_compania cc
            WHERE
              cc.cve_cia = cr.cve_cia
              and cr.Activo = 1 and cc.Activo = 1
              and cr.id = ?
            ',
            self::TABLE
        );

        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\AlmacenP\AlmacenP' );
        $sth->execute( array( $this->id ) );

        $this->data = $sth->fetch();

    }
    /**
     * @author Ricardo Delgado.
     * Actualiza el almacen predeterminado para el usuario.
     * @param {object}.
     */ 
    function actualizarAlmaPre($data){
        $sql = 'DELETE FROM t_usu_alm_pre WHERE id_user =:id';
        $delete = \db()->prepare($sql);
        $delete->bindValue( ':id', $data['idUser'], \PDO::PARAM_STR );
        $delete->execute();

        $sql = 'INSERT INTO t_usu_alm_pre SET id_user= :id, cve_almac= :alma';
        $insert = \db()->prepare($sql);
        $insert->bindValue( ':id', $data['idUser'], \PDO::PARAM_STR );
        $insert->bindValue( ':alma', $data['alma'], \PDO::PARAM_STR );
        $insert->execute();
    }

    /**
     * @author Ricardo Delgado.
     * Obtiene el almacen predeterminado del usuario.
     * @param {object} id del usuario.
     * @returns {json}.
     */ 
    function getAlmaPre($data){
        $sql = 'SELECT 
                  alm.id, alm.clave
                FROM t_usu_alm_pre alm_pre, c_almacenp alm 
                WHERE 
                  alm_pre.cve_almac = alm.clave and id_user = :id';
        $search = \db()->prepare($sql);
        $search->bindValue( ':id', $data['idUser'], \PDO::PARAM_STR );
        $search->execute();
        return $search->fetchAll();
    }

    function getUser($data)
    {
      $sql = 'SELECT id_user, cve_usuario, nombre_completo
              FROM c_usuario
              WHERE id_user = :id';
      $search = \db()->prepare($sql);
      $search->bindValue( ':id', $data['idUser'], \PDO::PARAM_STR );
      $search->execute();
      return $search->fetchAll();
    }


    function isInUse(){
      /* Condiciones a comprobar
      * No tener Entradas Pendientes th_aduana -> (Cve_Almac [clave], status = C|I [Pendiente, Recibiendo])
      * Surtidos Pendientes (Descartado por Efrain)
      * Embarques Pendientes -> Pedidos del embarque -> th_pedido (cve_almac [id], status = C)
      * Inventarios Pendientes th_inventario -> (cve_almacen [clave], status = A)
      * Sin productos en sus ubicaciones   V_ExistenciaGral -> cve_almac [clave], c_ubicacion -> cve_almac [clave zona almacenaje]
      * Sin productos en zona de almacenaje _ExistenciaGral -> cve_almac [clave], c_ubicacion -> cve_almac [clave zona almacenaje]
      * Sin productos en zona de recepcion td_entalmacen -> cve_ubicacion [ubicacion de recepcion, -> tubicacionesretencion -> cve_ubicacion, c_almacenp[id]]
      * Sin productos en zona de embarque th_entalmacen -> Cve_Almac[clave], STATUS -> C
      * c_articulo -> cve_almac [id]
      */
      $sql = "SELECT
                    (SELECT COUNT(ID_Aduana) FROM th_aduana WHERE Cve_Almac = a.clave AND (status = 'I' || status = 'C')) AS entradas,
                    (SELECT COUNT(Fol_folio) FROM th_pedido WHERE cve_almac = a.id AND status = 'C') AS embarques,
                    (SELECT COUNT(ID_Inventario) FROM th_inventario WHERE cve_almacen = a.clave AND status = 'A') AS inventarios,
                    (SELECT COUNT(DISTINCT cve_articulo) FROM V_ExistenciaGral WHERE cve_almac = a.id OR cve_ubicacion IN (SELECT idy_ubica FROM c_ubicacion WHERE cve_almac IN (SELECT clave_almacen FROM c_almacen WHERE cve_almacenp = a.id))) AS ubicaciones_zonas,
                    (SELECT COUNT(DISTINCT fol_folio) FROM td_entalmacen WHERE cve_ubicacion IN (SELECT cve_ubicacion FROM tubicacionesretencion WHERE cve_almacp = a.id AND Activo = 1)) AS recepcion,
                    (SELECT COUNT(Fol_Folio) FROM th_entalmacen WHERE Cve_Almac = a.clave AND STATUS = 'C') AS embarque
              FROM c_almacenp a
              WHERE id = ?";
      $result = \db()->prepare($sql);
      $result->execute(array($this->id));
      $data = $result->fetch(\PDO::FETCH_ASSOC);
      $total = 0;
      foreach($data as $exists){
        $total += intval($exists);
      }
      return $total > 0;
    }

    function recovery( $data ) {

        $sql = "UPDATE c_almacenp SET Activo = 1 WHERE  id='".$data['id']."';";
        $this->delete = \db()->prepare($sql);
        $this->delete->execute( array(
          $data['id']
        ) );
    }


  }
