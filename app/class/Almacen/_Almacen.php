<?php

namespace Almacen;

class Almacen {

  const TABLE = 'c_almacen';
  var $identifier;

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
      $sth->setFetchMode(\PDO::FETCH_CLASS, '\Almacen\Almacen');
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
          %s
        WHERE
          cve_almac = ?
      ',
                   self::TABLE
                  );

    $sth = \db()->prepare( $sql );
    $sth->setFetchMode( \PDO::FETCH_CLASS, '\Almacen\Almacen' );
    $sth->execute( array( $this->cve_almac ) );

    $this->data = $sth->fetch();

  }

  function traerAlmacen($clave_almacen) {

    $sql = sprintf('
        SELECT
          *
        FROM
          %s
        WHERE
          clave_almacen = "'.$clave_almacen.'"
      ',
                   self::TABLE
                  );

    $sth = \db()->prepare( $sql );
    $sth->setFetchMode( \PDO::FETCH_CLASS, '\Almacen\Almacen' );
    $sth->execute( );
    $this->data = $sth->fetch();

  }

  function exist($clave_almacen) {

    $sql = sprintf('
        SELECT
          *
        FROM
          %s
        WHERE
          clave_almacen = ?
      ',
                   self::TABLE
                  );

    $sth = \db()->prepare( $sql );

    $sth->setFetchMode( \PDO::FETCH_CLASS, '\Almacen\Almacen' );
    $sth->execute( array( $clave_almacen ) );

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
    $sth->setFetchMode( \PDO::FETCH_CLASS, '\Almacen\Almacen' );
    $sth->execute( array( cve_almac ) );

    return $sth->fetchAll();

  }

  function __get( $key ) {

    switch($key) {
      case 'cve_almac':
      case 'cve_cia':
      case 'clave_almacen':
      case 'des_almac':
      case 'des_direcc':								
      case 'cve_almacenp':
        $this->load();
        return @$this->data->$key;
      default:
        return $this->key;
    }

  }

  function save( $_post ) {

    if( !$_post['clave_almacen'] ) { throw new \ErrorException( 'clave_almacen is required.' ); }
    if( !$_post['des_almac'] ) { throw new \ErrorException( 'des_almac is required.' ); }

    $sql = sprintf('
        INSERT INTO
          ' . self::TABLE . '
        SET
		des_almac = :des_almac
		,clave_almacen=:clave_almacen
		,cve_almacenp=:cve_almacenp
		');

    $this->save = \db()->prepare($sql);

    //$this->save->bindValue( ':ID_Proveedor', $this->ID_Proveedor, \PDO::PARAM_STR );

    $this->save->bindValue( ':des_almac', $_post['des_almac'], \PDO::PARAM_STR );
    $this->save->bindValue( ':clave_almacen', $_post['clave_almacen'], \PDO::PARAM_STR );
    $this->save->bindValue( ':cve_almacenp', $_post['cve_almacenp'], \PDO::PARAM_STR );

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

  function actualizarAlmacen( $data ) {
    $sql = '
        UPDATE
          ' . self::TABLE . '
        SET
          cve_almacenp = ?
        , des_almac = ?


        WHERE
          clave_almacen = ?
      ';
    $this->save = \db()->prepare($sql);
    $this->save->execute( array($data['cve_almacenp'], $data['des_almac'], $data['cve_almac']));
  }

  function borrarAlmacen( $data ) {
    $sql = '
        UPDATE
          ' . self::TABLE . '
        SET
          Activo = 0
        WHERE
          clave_almacen = ?
      ';
    $this->save = \db()->prepare($sql);
    $this->save->execute( array(
      $data['clave_almacen']
    ) );
  }

  function recovery( $data ) {
    $sql = '
        UPDATE
          ' . self::TABLE . '
        SET
          Activo = 1
        WHERE
          cve_almac = ?
      ';
    $this->save = \db()->prepare($sql);
    $this->save->execute( array(
      $data['cve_almac']
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

  function getLastInsertId(){
    $sql = '
        SELECT MAX(cve_almac) as ID_Almacen        
        FROM
          ' . self::TABLE . '
      ';

    $sth = \db()->prepare( $sql );
    $sth->setFetchMode( \PDO::FETCH_CLASS, '\Almacen\Almacen');
    $sth->execute(array( ID_Ruta ) );
    return $sth->fetch();
  }

  function saveUserAl( $usuario, $ID_Almacen ) 
  {
    try {
      $sql = mysqli_query(\db2(), "CALL SPAD_AgregaRelUsA ('".$usuario."', '".$ID_Almacen."'   );") or die(mysqli_error(\db2()));
    } 
    catch(PDOException $e) 
    {
      return 'ERROR: ' . $e->getMessage();
    }
  }

  function loadUserAlmacen($almacen) 
  {

    $sql = '
   SELECT 
          c.id_user as id_usuario,
		  c.cve_usuario as clave_usuario, 
		  c.nombre_completo as nombre_usuario
        FROM 
          trel_us_alm t 
        INNER JOIN 
          c_usuario c ON c.cve_usuario = t.cve_usuario 
        WHERE 
          t.cve_almac = "'.$almacen.'"
        AND 
          t.Activo = 1 
		  and c.Activo =1
      ';

    $sth = \db()->prepare( $sql );
    $sth->setFetchMode( \PDO::FETCH_CLASS, '\Almacen\Almacen' );
    $sth->execute( array( $user ) );

    return $sth->fetchAll();

  }


  function loadAlmacenUser($user) 
  {

    $sql = "SELECT 
              a.clave as clave_almacen, 
              a.nombre as descripcion_almacen
            FROM 
              trel_us_alm t 
            INNER JOIN 
              c_almacenp a ON a.clave = t.cve_almac 
            WHERE 
              t.Activo = 1 and t.cve_usuario = '{$user}' and a.Activo = 1";
    // echo $sql; exit;

    $sth = \db()->prepare( $sql );
    $sth->setFetchMode( \PDO::FETCH_CLASS, '\Almacen\Almacen' );
    $sth->execute();

    return $sth->fetchAll();

  }


  function loadUsers($cve_almac) {

    $sql = '
		SELECT 
			c.id_user as id_usuario, 
			c.cve_usuario as clave_usuario, 
			c.nombre_completo as nombre_usuario
		FROM 
			c_usuario c
		WHERE c.Activo =1 
		and c.id_user not in (SELECT c.id_user
		FROM c_usuario c, trel_us_alm t 
		where c.cve_usuario = t.cve_usuario 
		and t.cve_almac = "'.$cve_almac.'" 
		AND t.Activo = 1 and c.Activo =1);

    ';

    $sth = \db()->prepare( $sql );
    //$sth->setFetchMode( \PDO::FETCH_CLASS, '\Ruta\Ruta' );
    $sth->execute();

    return $sth->fetchAll();

  }



  function loadUbicacionesDeZonas($zonas,$rack, $area, $almacen, $vacias, $producto) 
  {
    if($producto != '') 
    {
      $producto_clave = explode("-",$producto);
      $cve_producto = str_replace(" ","",(str_replace(")","",(str_replace("(","",$producto_clave[0]))) ));
      
      $p_vacia.=" and e.cve_articulo = '".$cve_producto."' ";
    }
    $sqlVacias = ""/*"and u.idy_ubica ".($vacias?"NOT":"")." IN (
                            SELECT cve_ubicacion FROM V_ExistenciaGralProduccion e
                            WHERE  e.tipo = 'ubicacion'
                            {$p_vacia}
                            )
                            "*/;
    $sqlAreasVacias ="AND cve_ubicacion ".($vacias?"NOT":"")." IN (
                      SELECT cve_ubicacion FROM V_ExistenciaGralProduccion e WHERE  e.tipo = 'area'
                      {$p_vacia})";
    $ubicaciones = [];
    $areas = [];
    if($rack != "")
    {
      $split.=" and u.cve_rack=$rack";
    }
    
    if($almacen != "")
    {
      $split.=" and p.id=$almacen";
    }
    
    if($zonas != "")
    {
      $split.=" and a.cve_almac=$zonas";
    }
      
    
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $query = mysqli_query($conn, "SELECT codigo FROM t_codigocsd;");
    if($query->num_rows > 0)
    {
      $codigo = mysqli_fetch_row($query)[0];
    }
    if(!empty($codigo))
    {
      $data = explode('-', $codigo);
      $sqlPre = "CONCAT(";
      $totalData = sizeof($data) - 1;
      for($i = 0; $i <= $totalData; $i++)
      {
        if($data[$i]=='cve_rack')
        {
          $g="'rack-'";
        }
        if($data[$i]=='cve_nivel')
        {
          $g="'secci&oacute;n-'";
        }
        if($data[$i]=='Ubicacion')
        {
          $g="'pasillo-'";
        }
        $sqlPre .= "u.{$data[$i]}";
        //$sqlPre .=$g.",";
        $sqlPre .= ($i < $totalData) ? ", '-', ": ")";
      }
    }
    
    $sql1 = "SELECT 
              u.CodigoCSD AS ubicacion,
              u.cve_pasillo as pasillo,
              u.cve_rack as rack, 
              u.Seccion as seccion, 
              u.cve_nivel as nivel, 
              u.Ubicacion as ubic, 
              u.cve_almac as zona_almacen, 
              u.idy_ubica as id_ubicacion
              FROM   c_ubicacion u
              LEFT JOIN c_almacen a on u.cve_almac = a.cve_almac
              LEFT JOIN c_almacenp p on a.cve_almacenp = p.id
              where 1
              and u.activo = 1 
              and a.activo =1 
              and u.idy_ubica not in (select t_invpiezas.idy_ubica from t_invpiezas where activo = 1 )
              $split {$sqlVacias} ;";
    $sth = \db()->prepare( $sql1 );
    $sth->execute();
    $ubicaciones = $sth->fetchAll();
    //echo var_dump($sql1);
    //die();
    $areas_true = 0;
    
    if(!empty($area) && $area === 'true')
    {
      $sql ="SELECT cve_ubicacion AS id_ubicacion, desc_ubicacion AS ubicacion, 'true' AS area  FROM tubicacionesretencion WHERE Activo = 1 AND cve_almacp = {$almacen} {$sqlAreasVacias}";
      $sth = \db()->prepare( $sql );
      $sth->execute();
      $areas = $sth->fetchAll();
      //echo var_dump($sql);
      //die();
      $areas_true = 1;
    }
    if($areas_true ==1)
    {
      return array_merge($areas);
    }
    else
    {
      return array_merge($ubicaciones);
    }
  }

  function loadRackDeZonas($zonas, $vacias) 
  {
    $sqlVacias="";
    if($vacias == true)
    {
      $sqlVacias.= "and u.cve_almac ".(($vacias)?'NOT':'')." IN (SELECT cve_ubicacion FROM V_ExistenciaGralProduccion WHERE  tipo = 'ubicacion')";
    }
    $zonas_limbo ="";
    if($zonas != "")
    {
      $zonas_limbo.= "and a.cve_almac = '{$zonas}'";
    }
    

    $sql = "
      SELECT DISTINCT u.cve_rack as rack 
      FROM `c_ubicacion` u
      inner join c_almacen a on a.cve_almac = u.cve_almac
      {$zonas_limbo}
      and u.activo = 1 and a.activo =1 {$sqlVacias}
      ORDER BY rack asc
      ";

    //echo var_dump($sql);
    //die();
    $sth = \db()->prepare( $sql );
    $sth->execute();
    
    $responce[0] = $sth->fetchAll();
    return $responce;

  }


  function loadAlmacenes($usuario) 
  {
    $sql = 
      '
        SELECT
          a.clave as clave_almacen,
          a.nombre as descripcion_almacen
        FROM c_almacenp a
        WHERE a.Activo =1
        AND a.clave not in (SELECT a.clave
                            FROM c_almacenp a, trel_us_alm t 
                            WHERE a.clave = t.cve_almac 
                            AND t.cve_usuario = "'.$usuario.'"
                            AND t.Activo = 1 and a.Activo =1);
    ';
    
    $sth = \db()->prepare( $sql );
    //$sth->setFetchMode( \PDO::FETCH_CLASS, '\Ruta\Ruta' );
    $sth->execute();
    return $sth->fetchAll();
  }

  function traerZonaporAlmacen($cve_almacenp) 
  {
    $sql = 
      '
        SELECT 			
          a.cve_almac as clave_almacen, 
          a.clave_almacen AS clave2,
          a.des_almac as descripcion_almacen
        FROM 
          c_almacen a
        WHERE a.Activo =1 and a.cve_almacenp="'.$cve_almacenp.'" ORDER BY clave2 ASC
      ';
    //echo var_dump($sql);
    //die();
    $sth = \db()->prepare( $sql );
    //$sth->setFetchMode( \PDO::FETCH_CLASS, '\Ruta\Ruta' );
    $sth->execute();
    return $sth->fetchAll();
  }

  function traerArticulosDeAlmacen($cve_almacenp) {

    $sql = '
                SELECT
                a.*
                FROM
                c_articulo a, c_almacenp c
				where a.Activo=1 and a.cve_almac = c.id
				and c.id="'.$cve_almacenp.'"';

    $sth = \db()->prepare( $sql );            
    $sth->execute();

    return $sth->fetchAll();     

  }

  function traerArticulosDeAlmacenExist($cve_almacenp) {

    $sql = 'SELECT a.cve_almac, a.cve_ubicacion, a.cve_articulo, art.des_articulo, a.cve_lote, SUM(a.Existencia) as Suma FROM V_ExistenciaGral a, c_articulo art where a.cve_articulo = a.cve_articulo and a.cve_articulo=art.cve_articulo and a.cve_almac="'.$cve_almacenp.'" and a.cve_articulo NOT IN (SELECT art.cve_articulo from cab_planifica_inventario ca, det_planifica_inventario p, c_articulo art where ca.cve_articulo = a.cve_articulo AND p.cve_articulo = art.cve_articulo AND p.cve_articulo = a.cve_articulo)and a.cve_articulo NOT IN (SELECT art.cve_articulo from cab_planifica_inventario ca, det_planifica_inventario p, c_articulo art where ca.cve_articulo = a.cve_articulo AND p.cve_articulo = art.cve_articulo AND p.cve_articulo = a.cve_articulo) GROUP BY a.cve_articulo';

    $sth = \db()->prepare( $sql );            
    $sth->execute();

    return $sth->fetchAll();     

  }

  function buscarArticulos($cve_almacenp, $parameter) {

    $sql = 'SELECT a.cve_almac, a.cve_ubicacion, a.cve_articulo, art.des_articulo, a.cve_lote, SUM(a.Existencia) as Suma FROM v_existenciagral a, c_articulo art where a.cve_articulo = a.cve_articulo and a.cve_articulo=art.cve_articulo and a.cve_almac="'.$cve_almacenp.'" and (art.des_articulo like "%'.$parameter.'%" or art.cve_articulo like "%'.$parameter.'%") and a.cve_articulo NOT IN (SELECT art.cve_articulo from cab_planifica_inventario ca, det_planifica_inventario p, c_articulo art where ca.cve_articulo = a.cve_articulo AND p.cve_articulo = art.cve_articulo AND p.cve_articulo = a.cve_articulo) GROUP BY a.cve_articulo';

    $sth = \db()->prepare( $sql );            
    $sth->execute();

    return $sth->fetchAll();     

  }


  function borrarUsuarioAlmacen($cve_almacen) {
    $sql = '
        DELETE
        FROM
          trel_us_alm       
        WHERE
          cve_almac = "'.$cve_almacen.'";';
    $this->save = \db()->prepare($sql);
    $this->save->execute(array($cve_almacen));
  }

  function borrarAlmacenUsuario($cve_usuario) {
    $sql = '
        DELETE
        FROM
          trel_us_alm       
        WHERE
          cve_usuario = "'.$cve_usuario.'";';
    $this->save = \db()->prepare($sql);
    $this->save->execute(array($cve_almacen));
  }

  public function inUse( $data ) {

    $sql = "SELECT cve_articulo FROM V_ExistenciaGral WHERE cve_ubicacion IN (SELECT idy_ubica FROM c_ubicacion WHERE cve_almac = (SELECT cve_almac FROM c_almacen WHERE clave_almacen = '".$data['clave_almacen']."'))";
    $sth = \db()->prepare($sql);
    $sth->execute();
    $data = $sth->fetch();

    if ($data['cve_articulo']) 
      return true;
    else
      return false;
  }  

}

