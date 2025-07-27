<?php

namespace OrdenCompra;

$tools = new \Tools\Tools();

class OrdenCompra
{
  
  private $tools = null;

  const TABLE = 'th_aduana';
  const TABLE_D = 'td_aduana';
  
  public $ID_Aduana = false;
  
  var $identifier;

  public function __construct( $ID_Aduana = false, $key = false )
  {
    $this->tools = new \Tools\Tools();
    //TODO: Aclarar por que en la definición de la clase, se busca el ID de aduana dos veces, por ID_Aduana y por Key
    if($ID_Aduana)
    {
      $this->ID_Aduana = (int) $ID_Aduana;
    }
    if($key)
    {
      $query_th_aduana = $this->tools->dbQuery("SELECT ID_Aduana FROM ".self::TABLE." WHERE ID_Aduana = ".$key);
      $ID_Aduana = $query_th_aduana->fetch();
      $this->ID_Aduana = $ID_Aduana->ID_Aduana;
    }
  }

    private function load() {

        $sql = "
        SELECT
          *,
           DATE_FORMAT(fech_pedimento,'%d-%m-%Y %H:%i:%s'  ) as fech_pedimento,
           DATE_FORMAT(fech_llegPed,'%d-%m-%Y %H:%i:%s'  ) as fech_llegPed,
           DATE_FORMAT(fechaDeFallo,'%d-%m-%Y %H:%i:%s'  ) as fechaDeFallo,
           DATE_FORMAT(fechaSuficiencia,'%d-%m-%Y %H:%i:%s'  ) as fechaSuficiencia,
           DATE_FORMAT(fechaContrato,'%d-%m-%Y %H:%i:%s'  ) as fechaContrato,
           (SELECT (SUM(td_aduana.cantidad*td_aduana.costo)) FROM td_aduana WHERE num_pedimento = td_aduana.num_orden) as importe
        FROM
         ".self::TABLE."
        WHERE
          num_pedimento = ".$this->num_pedimento."
      ";



        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\OrdenCompra\OrdenCompra' );
        $sth->execute( array( $this->num_pedimento ) );

        $this->data = $sth->fetch();

    }

    private function loadAdd() {

        $sql = "
        SELECT
          *,
           DATE_FORMAT(fech_pedimento,'%d-%m-%Y %H:%i:%s'  ) as fech_pedimento,
           DATE_FORMAT(fech_llegPed,'%d-%m-%Y %H:%i:%s'  ) as fech_llegPed,
           DATE_FORMAT(fechaDeFallo,'%d-%m-%Y %H:%i:%s'  ) as fechaDeFallo,
           DATE_FORMAT(fechaSuficiencia,'%d-%m-%Y %H:%i:%s'  ) as fechaSuficiencia,
           DATE_FORMAT(fechaContrato,'%d-%m-%Y %H:%i:%s'  ) as fechaContrato,
           (SELECT (SUM(td_aduana.cantidad*td_aduana.costo)) FROM td_aduana WHERE num_pedimento = td_aduana.num_orden) as importe
        FROM
         ".self::TABLE."
        WHERE
          ID_Aduana = ".$this->ID_Aduana."
      ";



        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\OrdenCompra\OrdenCompra' );
        $sth->execute( array( $this->ID_Aduana ) );

        $this->data = $sth->fetch();

    }

	function load2($num_pedimento) {

        $sql = sprintf('SELECT
            *
            FROM
            %s
            WHERE
            num_pedimento = ?
        ',
            self::TABLE
        );

        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\OrdenCompra\OrdenCompra' );
        $sth->execute( array( $num_pedimento ) );

        $this->data = $sth->fetch();

    }

    private function loadDetalle() 
    {
      $sql = 
      "
      SELECT 
        c_articulo.des_articulo,
        td_aduana.ID_Aduana,
        td_aduana.cve_articulo,
        td_aduana.cantidad,
        td_aduana.cve_lote,
        td_aduana.caducidad,
        td_aduana.temperatura,
        td_aduana.num_orden,
        td_aduana.Ingresado,
        td_aduana.costo,
        c_articulo.id,
        c_articulo.peso,
        c_articulo.alto*c_articulo.fondo*c_articulo.ancho*td_aduana.cantidad/1000000000 as volumen,
        (td_aduana.costo*td_aduana.cantidad) as importeTotal,
        td_aduana.costo as precioUnitario,
        td_aduana.Activo 
      FROM td_aduana 
      INNER JOIN c_articulo ON c_articulo.cve_articulo = td_aduana.cve_articulo
      WHERE td_aduana.num_orden = '".$this->num_pedimento."'
      ";
      
      $arr = array();
      $rs = mysqli_query(\db2(), $sql) or die("Error description: " . mysqli_error(\db2()));
      while ($row = mysqli_fetch_array($rs)) 
      {
        $row=array_map('utf8_encode', $row);
        $arr[] = $row;
      }
      $this->dataDetalle = $arr;
    }

	private function loadSC() 
  {
    $folio = $this->num_pedimento;
    $sql = 
    "
    SELECT 
      c_articulo.des_articulo,
      td_aduana.ID_Aduana,
      td_aduana.cve_articulo,
      td_aduana.cantidad as cantidad,
      td_aduana.cve_lote,
      td_aduana.caducidad,
      td_aduana.temperatura,
      td_aduana.num_orden,
      td_aduana.Ingresado,
      c_articulo.id,
      c_articulo.peso,
      td_aduana.Activo 
    FROM td_aduana
      INNER JOIN c_articulo ON td_aduana.cve_articulo = c_articulo.cve_articulo
      LEFT JOIN td_entalmacen ON td_entalmacen.fol_folio=td_aduana.num_orden and td_entalmacen.cve_articulo=td_aduana.cve_articulo
    WHERE td_aduana.num_orden ='$folio'  
      AND (SELECT 
              COALESCE(SUM(CantidadRecibida),0) AS cantidad2 
            FROM td_entalmacen 
            WHERE fol_folio = '$folio' 
              AND cve_articulo = td_aduana.cve_articulo
          ) < cantidad
      GROUP by td_aduana.cve_articulo
    ";

    $arr = array();
    $rs = mysqli_query(\db2(), $sql) or die("Error description: " . mysqli_error(\db2()));
    while ($row = mysqli_fetch_array($rs)) 
    {
      $row=array_map('utf8_encode', $row);
      $arr[] = $row;
    }
    $this->dataDetalle2 = $arr;
  }

    function __get( $key ) {

        switch($key) {
            case 'ID_Aduana':
                $this->loadAdd();
                return @$this->data->$key;
				    case 'num_pedimento':
                $this->load();
                return @$this->data->$key;

            default:
                return $this->key;
        }

    }

    function __getDetalle( $key ) {

        switch($key) {
            case 'num_pedimento':
                $this->loadDetalle();
				        $this->loadSC();
                return @$this->dataDetalle->$key;
            default:
                return $this->key;
        }

    }

    function save($_post)
    {
      $fechaentrada = date('Y-m-d h:i:s', strtotime($_post['fechaentrada'])); 
      $fechaestimada = date('Y-m-d h:i:s', strtotime($_post['fechaestimada'])); //echo var_dump($fechaentrada, $fechaestimada); die();
      $fechaDeFallo =0;
      $fechaDeSuficiencia = null;
      $fechaDeContrato =0;
      if($_post['fechaDeFallo']!="")
      {
        $fechaDeFallo = date('Y-m-d h:i:s', strtotime($_post['fechaDeFallo']));
      }
      if($_post['fechaSuficiencia']!="")
      {
        $fechaDeSuficiencia = date('Y-m-d h:i:s', strtotime($_post['fechaSuficiencia']));
      }
      if($_post['fechaContrato']!="")
      {
        $fechaDeContrato = date('Y-m-d h:i:s', strtotime($_post['fechaContrato']));
      }
      $num_pedimento = $this->getMax()->id + 1;


    $sql = "DESC td_aduana";
    $rs_ad = mysqli_query(\db2(), $sql) or die("Error description: " . mysqli_error(\db2()));
    
    while($row_aduana = mysqli_fetch_array($rs_ad))
    {
        if($row_aduana['Field'] == 'Cve_Lote' && $row_aduana['Null'] == 'NO')
        {
            $td_aduana_alter = $this->tools->dbQuery("ALTER TABLE td_aduana CHANGE Cve_Lote Cve_Lote VARCHAR(50) NULL");
        }
    }

      try
      {

        $to_insert_th_aduana = array(
          "num_pedimento"       => $num_pedimento,
          "fech_pedimento"      => $fechaentrada, //Fecha de Creacion de la Orden
          "fech_llegPed"        => $fechaestimada,//Fecha estimada de llegada de la Orden
          "ID_Proveedor"        => $_post['ID_Proveedor'],
          "ID_Protocolo"        => $_post['ID_Protocolo'],
          "Consec_protocolo"    => $_post['Consec_protocolo'],
          "factura"             => strtoupper($_post['factura']),
          "cve_usuario"         => $_post['cve_usuario'],
          "Cve_Almac"           => $_post['Cve_Almac'],
          "Activo"              => 1,
          "status"              => "C",
          "recurso"             => $_post['recurso'],
          "procedimiento"       => $_post['procedimiento'],
          "areaSolicitante"     => $_post['areaSolicitante'],
          "numSuficiencia"      => $_post['numSuficiencia'],
          "fechaSuficiencia"    => $fechaDeSuficiencia,
          "montoSuficiencia"    => $_post['montoSuficiencia'],
          "numeroContrato"    => $_post['numeroContrato'],
          "fechaContrato"       => $fechaDeContrato,
          "dictamen"            => $_post['dictamen'],
          "presupuesto"         => $_post['presupuesto'],
          "condicionesDePago"   => $_post['condicionesDePago'],
          "lugarDeEntrega"      => $_post['lugarDeEntrega'],
          "fechaDeFallo"        => $fechaDeFallo,
          "plazoDeEntrega"      => $_post['plazoDeEntrega'],
          "Tipo_Cambio"         => $_post['tipo_cambio'],
          "Proyecto"            => $_post['numeroDeExpediente']
          
          //"importeOrden"          => $_post['importeOrden']
        );
            
        $insert_orden = $this->tools->dbInsert(self::TABLE,$to_insert_th_aduana);
        $orden_id = $this->tools->insertId;
        
        if (!empty($_post["arrDetalle"]))
        {
          $td_aduana_delete = $this->tools->dbQuery("Delete From td_aduana WHERE num_orden = '".$num_pedimento."'");
          foreach ($_post["arrDetalle"] as $item)
          {
            $to_insert = array(
              "cve_articulo"  => $item['codigo'],
              "cantidad"      => $item['CantPiezas'],
              "num_orden"     => $num_pedimento,
              "cve_lote"      => '',
              "Activo"        => 1,
              "costo"         => $item['precioNewOk'],
              "Factura"   => $item['Factura_Art']
            );
            $insert_articulo = $this->tools->dbInsert(self::TABLE_D,$to_insert);
            $rel_articulo_proveedor = $this->tools->dbQuery("SELECT * From rel_articulo_proveedor WHERE Cve_Articulo = '".$item['codigo']."' AND ID_Proveedor = '".$_post['ID_Proveedor']."'  ");
            if (@sizeof($rel_articulo_proveedor)>0)
            {
              $to_insert_proveedor = array(
              "Cve_Articulo"  => $item['codigo'],
              "ID_Proveedor"  => $_post['ID_Proveedor'],              
              );
              $insert_proveedor = $this->tools->dbInsert("rel_articulo_proveedor",$to_insert_proveedor);
            }

          }

            $sql_protocolo = $this->tools->dbQuery("UPDATE t_protocolo SET FOLIO = '".$_post['Consec_protocolo']."' WHERE ID_Protocolo = '".$_post['ID_Protocolo']."'"); 
        }
        
      }
      catch(Exception $e)
      {
        return 'ERROR: ' . $e->getMessage();
      }
    }

    function borrarCliente( $data ) {
        $id = $data['ID_Aduana'];
        $sql = "
        UPDATE
          th_aduana
        SET
          Activo = 0
        WHERE
          num_pedimento = $id
      ";
        $this->save = \db()->prepare($sql);
        $this->save->execute();
    }

    function actualizarOrden($_post)
    {
      $fechaentrada = date('Y-m-d h:i:s', strtotime($_post['fechaentrada']));
      $fechaestimada = date('Y-m-d h:i:s', strtotime($_post['fechaestimada']));
      $fechaDeFallo = date('Y-m-d h:i:s', strtotime($_post['fechaDeFallo']));
      $fechaDeSuficiencia = date('Y-m-d h:i:s', strtotime($_post['fechaSuficiencia']));
      $fechaDeContrato = date('Y-m-d h:i:s', strtotime($_post['fechaContrato']));
      try
      {

        $to_update_th_aduana = array(
          "fech_pedimento"      => $fechaentrada,
          "fech_llegPed"        => $fechaestimada,
          "ID_Proveedor"        => $_post['ID_Proveedor'],
          "ID_Protocolo"        => $_post['ID_Protocolo'],
          "Consec_protocolo"    => $_post['Consec_protocolo'],
          "factura"             => $_post['factura'],
          "cve_usuario"         => $_post['cve_usuario'],
          "Cve_Almac"           => $_post['Cve_Almac'],
          "Activo"              => 1,
          "recurso"             => $_post['recurso'],
          "procedimiento"       => $_post['procedimiento'],
          "areaSolicitante"     => $_post['areaSolicitante'],
          "numSuficiencia"      => $_post['numSuficiencia'],
          "fechaSuficiencia"    => $fechaDeSuficiencia,
          "montoSuficiencia"    => $_post['montoSuficiencia'],
          "numeroContrato"      => $_post['numeroContrato'],
          "fechaContrato"       => $fechaDeContrato,
          "dictamen"            => $_post['dictamen'],
          "presupuesto"         => $_post['presupuesto'],
          "condicionesDePago"   => $_post['condicionesDePago'],
          "lugarDeEntrega"      => $_post['lugarDeEntrega'],
          "fechaDeFallo"        => $fechaDeFallo,
          "plazoDeEntrega"      => $_post['plazoDeEntrega'],
          "Proyecto"            => $_post['numeroDeExpediente']
          
          //"importeAlmacenado"          => $_post['importeOrden']
          
        );

        $where_update = array(
          "num_pedimento"    => $_post['num_pedimento']
        );
            
        $insert_orden = $this->tools->dbUpdate(self::TABLE,$to_update_th_aduana,$where_update);

        if (!empty($_post["arrDetalle"]))
        {
          $td_aduana_delete = $this->tools->dbQuery("Delete From td_aduana WHERE num_orden = '".$_post['num_pedimento']."'");
          foreach ($_post["arrDetalle"] as $item)
          {
            $to_update = array(
              "cve_articulo"  => $item['codigo'],
              "cantidad"      => $item['CantPiezas'],
              "num_orden"     => $_post['num_pedimento'],
              "cve_lote"      => "",
              "Activo"        => 1,
              "costo"         => $item['precioNewOk']
            );
            $insert_articulo = $this->tools->dbInsert(self::TABLE_D,$to_update);
          }
        }
      } 
      
      catch(Exception $e) 
      {
        return 'ERROR: ' . $e->getMessage();
      }
    }

		function exist($num_pedimento) {
      $sql = sprintf('
        SELECT
          *
        FROM
            '.self::TABLE.'
        WHERE
          num_pedimento = ?
      ');

      $sth = \db()->prepare( $sql );

      $sth->setFetchMode( \PDO::FETCH_CLASS, '\Articulos\Articulos' );
      $sth->execute( array( $num_pedimento ) );

      $this->data = $sth->fetch();

	  if(!$this->data)
		  return false;
				else
					return true;
    }

    function modoEdicion($num_pedimento,$status,$id_user) {
        $sql = '
        Update
        '.self::TABLE.'
        set status="'.$status.'",
        cve_usuario="'.$id_user.'"
        WHERE
        num_pedimento = '.$num_pedimento.'
        ';
        // echo $sql; exit;

        $sth = \db()->prepare( $sql );

        //$sth->setFetchMode( \PDO::FETCH_CLASS, '\Articulos\Articulos' );
        $sth->execute( array( $num_pedimento ) );

        //  $this->data = $sth->fetch();
    }

		function consecutivo($ID_Protocolo) {
/*
      $sql = '
         Select if(max(Consec_protocolo) is null,1,max(Consec_protocolo)+1) as Consec_protocolo   from    '.self::TABLE.' where ID_Protocolo="'.$ID_Protocolo.'"
      ';
*/
      $sql  = "SELECT (FOLIO+1) as Consec_protocolo from t_protocolo where ID_Protocolo='".$ID_Protocolo."'";
	 // echo $sql; exit;

      $sth = \db()->prepare( $sql );

      //$sth->setFetchMode( \PDO::FETCH_CLASS, '\Articulos\Articulos' );
      $sth->execute( array( $num_pedimento ) );

		return $sth->fetch()["Consec_protocolo"];


    }

	    function getAll() {

        $sql = '
        SELECT
          *
        FROM
          ' . self::TABLE . '
      ';

        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\OrdenCompra\OrdenCompra' );
        $sth->execute( array( id_user ) );

        return $sth->fetchAll();

    }

		    function getAll2() {

        $sql = '
        SELECT
          *
        FROM
          ' . self::TABLE . '
		  where status!="A" or status!="T"
      ';

        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\OrdenCompra\OrdenCompra' );
        $sth->execute( array( id_user ) );

        return $sth->fetchAll();

    }

	function getAllProv($almacen=null) {
        if ($almacen) $split=" AND p.clave='$almacen'";
        $sql = "SELECT  a.num_pedimento,
                        a.ID_Aduana,
                        a.factura,
                        c_proveedores.Nombre
            	FROM th_aduana a
            	LEFT JOIN c_proveedores ON c_proveedores.ID_Proveedor = a.ID_Proveedor
            	LEFT JOIN c_almacenp p ON a.Cve_Almac=p.clave 
                WHERE (a.status = 'C' OR a.status = 'I' ){$split}
                ORDER BY a.num_pedimento DESC;";

        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\OrdenCompra\OrdenCompra' );
        $sth->execute( array( id_user ) );

        return $sth->fetchAll();

    }

	   function getMax() {

        $sql = '
                SELECT max(num_pedimento) AS id
                FROM th_aduana
                ORDER BY ID_Aduana DESC
                LIMIT 1
        ';

        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\OrdenCompra\OrdenCompra' );
        @$sth->execute( array( id_user ) );

        return $sth->fetch();

    }

     function getMax_RL() {

        $sql = '
                SELECT max(Fol_Folio) AS id
                FROM th_entalmacen
                ORDER BY Fol_Folio DESC
                LIMIT 1
        ';

        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\OrdenCompra\OrdenCompra' );
        @$sth->execute( array( id_user ) );

        return $sth->fetch();

    }

	function getArticulo($folio,$articulo){
		$sql='
      Select * from  ' . self::TABLE_D . ' 
      inner join c_articulo on c_articulo.cve_articulo = '.self::TABLE_D.'.cve_articulo
      where '.self::TABLE_D.'.cve_articulo ="'.$articulo.'" 
      and num_orden="'.$folio.'"
      ';
		$sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\OrdenCompra\OrdenCompra' );
        $sth->execute( array( id_user ) );

        return $sth->fetch();

	}
  
  function getArticuloLibre($articulo){
		$sql='Select * from c_articulo where cve_articulo="'.$articulo.'" ';
		$sth = \db()->prepare( $sql );
        //$sth->setFetchMode( \PDO::FETCH_CLASS, '\OrdenCompra\OrdenCompra' );
        $sth->execute();

        return $sth->fetch();

	}


	function getFecha($fecha){
		$sql='Select * from  ' . self::TABLE . ' where fech_llegPed="'.$fecha.'"';
		$sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\OrdenCompra\OrdenCompra' );
        $sth->execute( array( id_user ) );

        return $sth->fetchAll();

	}

	function getAlmacen($almacen){
		$sql='Select * from  ' . self::TABLE . ' a
		left join c_almacenp p on a.Cve_Almac=p.clave
		where p.clave='.$almacen.'';
		$sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\OrdenCompra\OrdenCompra' );
        $sth->execute( array( id_user ) );

        return $sth->fetchAll();

	}
  
    //EDG
    function calcularCostoPromedio($data)
    {
      extract($data);
    
      foreach($data["arrDetalle"] as $item)
      {
          //query para traer los datos
          $sql= "SELECT 
                V_ExistenciaGral.cve_almac, 
                c_articulo.cve_articulo, 
                SUM(Existencia) Existencia_Total, 
                c_articulo.costoPromedio
                FROM `V_ExistenciaGral`
                LEFT JOIN c_articulo ON V_ExistenciaGral.cve_articulo = c_articulo.cve_articulo
                WHERE V_ExistenciaGral.cve_articulo = '".$item["cve_articulo"]."'
                GROUP BY V_ExistenciaGral.cve_articulo";
          $sth = \db()->prepare($sql);
          $sth->execute();
          $cosulta = $sth->fetch();
        
          $Existencias_Piezas = $cosulta["Existencia_Total"];
          $cantidad_entrada =  $item["cantidad_por_recibir"];
          $Precio_Promedio = $cosulta["costoPromedio"];
          $costo_entrada = $item["costo"];

           //$costoPromedio = ($Existencias_Piezas + $cantidad_entrada / (($Existencias_Piezas * $Precio_Promedio)+($cantidad_entrada * $costo_entrada)) 

          //Simplex
          $cantidad_total = $Existencias_Piezas + $cantidad_entrada;
          $costo_almacen = $Existencias_Piezas * $Precio_Promedio;
          $costo_entrada = $cantidad_entrada * $costo_entrada;

          $costoPromedio = ($costo_almacen + $costo_entrada) / $cantidad_total;
        
        $sqlup ="UPDATE c_articulo SET costoPromedio = '".$costoPromedio."' WHERE cve_articulo = '".$item["cve_articulo"]."'";
        $sth = \db()->prepare($sqlup);
        $sth->execute();
        //echo var_dump($costoPromedio);
      }
      //echo var_dump($costoPromedio);
      //die();
      
    }

  function receiveOC($data)
  {
    // echo var_dump($data["arrDetalle"]);
    // die();
    /* Variables para th_entalmacen ya que hay variables generales y particulares por articulo */

/* Cve_Almac / oc / tipo / arrDetalle / Cve_Usuario / usuario / Cve_Proveedor / fechafin / STATUS / Fact_Prov / */

    $XD_correcto = true;
    $folio_xd = "";
    $dataArrDetalle = $data["arrDetalle"];
    $cve_proveedor_empresa = $data["empresa"];
    $folio_recepcion = $data["folio_recepcion"];
    $id_entrada = $data["folio_recepcion"];
    //$proveedorID = $data["proveedorID"];
    $conexion = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    if($data["tipo"] == 'CD')
    {
        $folio_xd = $data["folio_xd"];
        //$conexion = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

        $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
        if (!($res_charset = mysqli_query($conexion, $sql_charset)))
            echo "Falló la preparación Charset: (" . mysqli_error($conexion) . ") ";
        $charset = mysqli_fetch_array($res_charset)['charset'];
        mysqli_set_charset($conexion , $charset);

        $sql = "SELECT Cve_articulo, IFNULL(cve_lote, '') AS cve_lote, SUM(Num_cantidad) AS Cantidad FROM td_pedido WHERE Fol_folio = '{$folio_xd}' GROUP BY Cve_articulo";
        $rs = mysqli_query($conexion, $sql);
        while($row_xd = mysqli_fetch_array($rs, MYSQLI_ASSOC))
        {
          $cve_articulo_xd = $row_xd["Cve_articulo"];
          $cantidad_xd = $row_xd["Cantidad"];
          $cantidad_entrada_xd = 0;
          foreach ($dataArrDetalle as $item)
          {
              if($item['clave_articulo'] == $cve_articulo_xd)
              {
                  $cantidad_entrada_xd += $item['cantidad_por_recibir'];
              }
          }

          if($cantidad_entrada_xd < $cantidad_xd) {$XD_correcto = false; break;}
        }

        if($XD_correcto == false)
          return 'NOXD';
        //return 'SIXD';
    }


    //$id_entrada = "";
    //$data["Fol_Folio"] = $data["oc"];
    $zona_th = $data["arrDetalle"][0]["zona_recepcion"];
    $hora_recepcion_th = $data["arrDetalle"][0]["hora_de_recepcion"];
    
    /* Consulta de td_aduana para saber sus datos */
    $sql = "SELECT * FROM td_aduana WHERE num_orden = '".$data["oc"]."';";
    $datos_aduana = $this->tools->dbQuery($sql)->fetch();
    
    /* Consulta de th_aduana para saber sus datos */
    $sql = "SELECT * FROM th_aduana WHERE num_pedimento = '".$data["oc"]."';";
    $cabecera_aduana = $this->tools->dbQuery($sql)->fetch();
  

    $recepcion_por_cajas = 0;
    $sql = "SELECT Valor FROM t_configuraciongeneral WHERE cve_conf = 'recepcion_por_cajas'";
    if(!$res_cajas = mysqli_query($conexion, $sql)) echo "Falló la preparación: (".mysqli_error($conexion).")";
    if(mysqli_num_rows($res_cajas) > 0)
    {
      $row_cajas = mysqli_fetch_assoc($res_cajas);
      $recepcion_por_cajas = $row_cajas['Valor'];
    }


/*

    if($data["tipo"] == 'RL')
    {
        $sql = "SELECT IFNULL(MAX(num_pedimento), 0) AS Maximo FROM th_aduana";
        $rs = mysqli_query($conexion, $sql);
        $row_max = mysqli_fetch_array($rs, MYSQLI_ASSOC);
        $data["Fol_Folio"] = $row_max["Maximo"]+1;
    }

    $sql = "SELECT IF((SELECT COUNT(DISTINCT cve_articulo) FROM td_aduana WHERE num_orden = '".$data["oc"]."') = (SELECT COUNT(DISTINCT cve_articulo) FROM td_entalmacen WHERE num_orden = '".$data["oc"]."'), 'T', 'C') AS Status_OC FROM DUAL;";
    $oc_status = $this->tools->dbQuery($sql)->fetch();
    $status_oc = $oc_status["Status_OC"];

    $sql = "UPDATE th_aduana SET status = '$status_oc' WHERE num_pedimento = '".$data["oc"]."';";
    $status_aduana = $this->tools->dbQuery($sql)->fetch();
*/
    /* Consulta de th_entalmacen para saber si existe una entrada previa */
    $sql = "SELECT count(*) as x from th_entalmacen where id_ocompra = '".$data["oc"]."' AND Fol_Folio = '".$folio_recepcion."'";
    $existe_entrada = $this->tools->dbQuery($sql)->fetch();
    $existe_entrada = $existe_entrada["x"];

    $sql = "SELECT NOW() as Fecha_Actual FROM DUAL";
    $fecha_entrada = $this->tools->dbQuery($sql)->fetch();
    $hora_recepcion_th = $fecha_entrada["Fecha_Actual"];

    //$sql = "SELECT ID_Proveedor FROM c_proveedores WHERE cve_proveedor = '$cve_proveedor_empresa'";
    //$row_proveedor = $this->tools->dbQuery($sql)->fetch();
    //$ID_Proveedor_clave = $row_proveedor["ID_Proveedor"];


    /* Arreglo Campo / Valor para insertar en th_entalmacen */
    $id_compraOC = NULL;
  if($data["tipo"] == 'OC') $id_compraOC = $data["oc"];
    $data_entrada_header = array(
      "Fol_Folio"         => $data["folio_recepcion"],
      "Cve_Almac"         => $data["Cve_Almac"],
      "Fec_Entrada"       => $hora_recepcion_th,
      "Cve_Usuario"       => $data["Cve_Usuario"],
      "Cve_Proveedor"     => $data["Cve_Proveedor"],//($data["Cve_Proveedor"]!='')?($data["Cve_Proveedor"]):($proveedorID),
      "STATUS"            => "E",
      "Cve_Autorizado"    => $data["Cve_Autorizado"],
      "tipo"              => $data["tipo"],
      //"statusaurora"      => $daCve_Proveedorta["statusaurora"],
      "id_ocompra"        => '$id_compraOC',
      "placas"            => $data["placas"],
      //"entarimado"        => $data["entarimado"],
      "bufer"             => $data["bufer"],
      "HoraInicio"        => $hora_recepcion_th,
      "ID_Protocolo"      => $data["ID_Protocolo"],
      "Consec_protocolo"  => $data["Consec_protocolo"],
      "Proveedor"         => '$cve_proveedor_empresa',
      "cve_ubicacion"     => $zona_th,
      "HoraFin"           => $data["fechafin"],
      "Fact_Prov"         => $data["Fact_Prov"]
    );

    $data_oc = $data["oc"];
    //echo "tipo = ".$data["tipo"]." existe_entrada = ".$existe_entrada;
    if($existe_entrada == 0)
    {
      // No existe una entrada previa
      //echo "No existe la entrada anteriormente, se insertará";
      $data_Cve_Almac        = $data["Cve_Almac"];
      $data_Cve_Usuario      = $data["Cve_Usuario"];
      $data_Cve_Proveedor    = $data["Cve_Proveedor"];//($data["Cve_Proveedor"]!='')?($data["Cve_Proveedor"]):($proveedorID);
      $data_Cve_Autorizado   = $data["Cve_Autorizado"];
      $data_tipo             = $data["tipo"];
      //$data_statusaurora     = $daCve_Proveedorta["statusaurora"];
      $data_oc               = $data["oc"];
      $data_placas           = $data["placas"];
      //$data_entarimado       = $data["entarimado"];
      $data_bufer            = $data["bufer"];
      $data_ID_Protocolo     = $data["ID_Protocolo"];
      $data_Consec_protocolo = $data["Consec_protocolo"];
      $data_fechafin         = $data["fechafin"];
      $data_Fact_Prov        = $data["Fact_Prov"];
      $estatus               = "E";
      //$hora_recepcion_th;
      //$hora_recepcion_th;
      //$zona_th;

      $claveproyecto       = $data["claveproyecto"];

      //$sql = "INSERT INTO th_entalmacen(Cve_Almac, Fec_Entrada, Cve_Usuario, Cve_Proveedor, STATUS, Cve_Autorizado, tipo, statusaurora, id_ocompra, placas, entarimado, bufer, HoraInicio, ID_Protocolo, Consec_protocolo, cve_ubicacion, HoraFin, Fact_Prov) VALUES ('$data_Cve_Almac', '$hora_recepcion_th', '$data_Cve_Usuario','$data_Cve_Proveedor', 'E', '$data_Cve_Autorizado', '$data_tipo', '$data_statusaurora', '$data_oc', '$data_placas', '$data_entarimado', '$data_bufer', '$hora_recepcion_th', '$data_ID_Protocolo', '$data_Consec_protocolo', '$zona_th', '$data_fechafin', '$data_Fact_Prov')";
      $sql = "";
      if($data_tipo == 'RL')
      {
        //$sql = "SELECT IFNULL(MAX(num_pedimento), 0) AS Maximo FROM th_aduana";
        //$rs = mysqli_query($conexion, $sql);
        //$row_max = mysqli_fetch_array($rs, MYSQLI_ASSOC);
        //$data_oc = $row_max["Maximo"]+1;
        $data_oc = "(SELECT IFNULL(MAX(num_pedimento), '') + 1 FROM th_aduana)";
        $sql = "INSERT IGNORE INTO th_entalmacen(Fol_Folio, Cve_Almac, Fec_Entrada, Cve_Usuario, Cve_Proveedor, STATUS, Cve_Autorizado, tipo, placas, bufer, HoraInicio, ID_Protocolo, Consec_protocolo, cve_ubicacion, HoraFin, Fact_Prov, Proyecto, Proveedor) VALUES ($folio_recepcion, '$data_Cve_Almac', '$hora_recepcion_th', '$data_Cve_Usuario','$data_Cve_Proveedor', 'E', '$data_Cve_Autorizado', '$data_tipo', '$data_placas', '$data_bufer', '$hora_recepcion_th', '$data_ID_Protocolo', '$data_Consec_protocolo', '$zona_th', '$data_fechafin', '$data_Fact_Prov', '$claveproyecto', '$cve_proveedor_empresa')";
      }
      else
      {
      //$sql = "INSERT IGNORE INTO th_entalmacen(Cve_Almac, Fec_Entrada, Cve_Usuario, Cve_Proveedor, STATUS, Cve_Autorizado, tipo, id_ocompra, placas, entarimado, bufer, HoraInicio, ID_Protocolo, Consec_protocolo, cve_ubicacion, HoraFin, Fact_Prov) VALUES ('$data_Cve_Almac', '$hora_recepcion_th', '$data_Cve_Usuario','$data_Cve_Proveedor', 'E', '$data_Cve_Autorizado', '$data_tipo', '$data_oc', '$data_placas', '$data_entarimado', '$data_bufer', '$hora_recepcion_th', '$data_ID_Protocolo', '$data_Consec_protocolo', '$zona_th', '$data_fechafin', '$data_Fact_Prov')";
      $sql = "INSERT IGNORE INTO th_entalmacen(Fol_Folio, Cve_Almac, Fec_Entrada, Cve_Usuario, Cve_Proveedor, STATUS, Cve_Autorizado, tipo, id_ocompra, placas, bufer, HoraInicio, ID_Protocolo, Consec_protocolo, cve_ubicacion, HoraFin, Fact_Prov, Proyecto, Proveedor) VALUES ($folio_recepcion, '$data_Cve_Almac', '$hora_recepcion_th', '$data_Cve_Usuario','$data_Cve_Proveedor', 'E', '$data_Cve_Autorizado', '$data_tipo', '$id_compraOC', '$data_placas', '$data_bufer', '$hora_recepcion_th', '$data_ID_Protocolo', '$data_Consec_protocolo', '$zona_th', '$data_fechafin', '$data_Fact_Prov', '$claveproyecto', '$cve_proveedor_empresa')";
      }
      $this->tools->dbQuery($sql);


      $sql = "SELECT MAX(Fol_Folio) Fol_Folio FROM th_entalmacen";
      $folio_max = $this->tools->dbQuery($sql)->fetch();
      //$id_entrada = $folio_max["Fol_Folio"];
      //echo "*********".$id_entrada;

      //$this->tools->dbInsert("th_entalmacen",$data_entrada_header);
      //$id_entrada = $this->tools->insertId;
    }
    else
    {
      // Actualizar la entrada previa
      //echo "Ya existe la entrada anteriormente, se actualizará";

      //$this->tools->dbUpdate("th_entalmacen",$data_entrada_header,array("id_ocompra"=>'$id_compraOC'));//data["oc"]

      $sql = "SELECT Fol_Folio FROM th_entalmacen WHERE id_ocompra = ".$id_compraOC;//data["oc"]
      $folio_max = $this->tools->dbQuery($sql)->fetch();
      //$id_entrada = $folio_max["Fol_Folio"];
    }

    /* Consulta th_entalmacen para saber el folio de la entrada con relacion a la orden */

/*
    if($data["tipo"] != 'RL')
    {
      $sql = "Select Fol_Folio from th_entalmacen where id_ocompra = '".$data["oc"]."'";
      $id_entrada = $this->tools->dbQuery($sql)->fetch();
      $id_entrada = $id_entrada["Fol_Folio"];
    }
*/
//*************************************************************************************************
    if($data["tipo"] == 'CD' && $XD_correcto == true)
    {
        $sql = "UPDATE th_pedido SET Ship_Num = '{$id_entrada}' WHERE Fol_folio = '{$folio_xd}'";
        $rs = mysqli_query($conexion, $sql);
    }

//*************************************************************************************************

    $cantidad_por_recibir_OC = 0;
    $cantidad_disponible = 0;
    $primeravez = false;
    $cantidad_pedida = 0;

    $array_cantidad_por_recibir_OC = array();
    $array_cantidad_disponible = array();
    $array_cantidad_pedida = array();
    $array_cve_articulo = array();

    foreach ($data["arrDetalle"] as $item)
    {
      /* Consulta td_entalmacen para saber si existe ya un detalle de la entrada */
      $sql = "Select count(*) as x from td_entalmacen where Fol_Folio = '".$id_entrada."' and cve_articulo = '".$item['clave_articulo']."' and cve_lote = '".$item['lote']."'";
      $existe_detalle = $this->tools->dbQuery($sql)->fetch();
      $existe_detalle = $existe_detalle["x"];
      
      /* Consulta td_aduana para saber los datos solicitados de cada articulo de ese orden */
      $sql = "Select * from td_aduana where num_orden = '".$data["oc"]."' and cve_articulo = '".$item['clave_articulo']."' ORDER BY Item ASC";
      $datos_compra = $this->tools->dbQuery($sql)->fetch();
      if($item['lote'] == NULL){$item['lote'] = " ";}
      /* Arreglo Campo / Valor para insertar en td_entalmacen */
/*
      $to_insert = array(
        "fol_folio"          => $id_entrada,
        "cve_articulo"       => $item['clave_articulo'],
        "cve_lote"           => $item['lote'],//$datos_aduana['lote'],
        "CantidadPedida"     => $datos_compra['cantidad'],
        "CantidadRecibida"   => $item['cantidad_por_recibir'] - $cantidad_por_recibir_OC,
        "CantidadDisponible" => $item['cantidad_por_recibir'],
        "numero_serie"       => $data['serie'],
        "status"             => "E",
        "cve_usuario"        => $data['usuario'],
        "cve_ubicacion"      => $item["zona_recepcion"],//$data['zona'],
        "fecha_inicio"       => date('Y-m-d H:i:s', strtotime($item['hora_de_recepcion'])),//$data['fechainicio'],
        "fecha_fin"          => date('Y-m-d H:i:s', strtotime($data['fechafin'])),
        "tipo_entrada"       => 1,
        "costoUnitario"      => $item['costo'],
        "num_orden"          => $data["oc"]
      );
*/

//       echo var_dump($to_insert);
//       die();
//      if(!$primeravez)
//      { 
      $cve_articulo = $item['clave_articulo'];

      $conexion = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
      //$sql = "SELECT COUNT(*) serie FROM c_articulo WHERE cve_articulo = '$cve_articulo' AND control_numero_series = 'S'";
      //$sql = "SELECT (IF(control_lotes = 'S', 'lote', IF(control_numero_series = 'S', 'serie', ''))) AS bandera, IFNULL(num_multiplo, 1) as num_multiplo FROM c_articulo WHERE cve_articulo = '$cve_articulo'";
      $sql = "SELECT (IF(a.control_lotes = 'S', 'lote', IF(a.control_numero_series = 'S', 'serie', ''))) AS bandera, 
                    IFNULL(a.num_multiplo, 1) AS num_multiplo,  u.mav_cveunimed
              FROM c_articulo a
              LEFT JOIN c_unimed u ON u.id_umed = a.unidadMedida
              WHERE a.cve_articulo = '$cve_articulo'";
      $rs = mysqli_query($conexion, $sql);
      $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
      $lote_SN = $resul['bandera']; //0 = N, 1 = S
      $unidad_medida_articulo = $resul['mav_cveunimed'];
      $unidades_por_caja = $resul['num_multiplo'];
      $cambio_realizado = false;

          //$cantidad_pedida = $datos_compra['cantidad'] - $cantidad_por_recibir_OC; 
      if(!in_array($cve_articulo, $array_cve_articulo))
      {
//          if(($cantidad_por_recibir_OC == 0 && !$serie_N) || $serie_N)
//          {
              $cantidad_pedida = $datos_compra['cantidad'];
              //if($unidad_medida_articulo == 'XBX') {$cantidad_pedida = $cantidad_pedida*$unidades_por_caja; $item['cantidad_por_recibir'] = $item['cantidad_por_recibir']*$unidades_por_caja;}
//          }
//          else //if(!$serie_N)
//          {
//              $cantidad_pedida = $cantidad_disponible; 
//          }
/*
          else
          {
              $cantidad_pedida = $datos_compra['cantidad'] - $cantidad_por_recibir_OC; 
          }
*/

          if(!$cantidad_pedida) $cantidad_pedida = $item['cantidad_por_recibir'];
          $cantidad_disponible = $cantidad_pedida - $item['cantidad_por_recibir']; 
          //$cantidad_disponible = $datos_compra['cantidad'] - $item['cantidad_por_recibir']; 



          array_push($array_cve_articulo, $cve_articulo);
          //array_push($array_cantidad_por_recibir_OC, $cantidad_por_recibir_OC);
          array_push($array_cantidad_disponible, $cantidad_disponible);
          array_push($array_cantidad_pedida, $cantidad_pedida);
/*
          if($serie_N)
            $cantidad_por_recibir_OC++;
          else
            $cantidad_por_recibir_OC = $datos_compra['cantidad'] - $item['cantidad_por_recibir'];
*/
      }
      else
      {
          $pos = 0;
          for($i_pos = count($array_cve_articulo); $i_pos > 0; $i_pos--)
          {
              if($cve_articulo == $array_cve_articulo[$i_pos])
              {
                  $pos = $i_pos;
                  break;
              }
          }
          $cantidad_pedida = $array_cantidad_disponible[$pos];
          if(!$cantidad_pedida) $cantidad_pedida = $item['cantidad_por_recibir'];

          //if($unidad_medida_articulo == 'XBX') {$cantidad_pedida = $cantidad_pedida*$unidades_por_caja; 
            //$item['cantidad_por_recibir'] = $item['cantidad_por_recibir']*$unidades_por_caja;
          //}

          $cantidad_disponible = $cantidad_pedida - $item['cantidad_por_recibir'];

          array_push($array_cve_articulo, $cve_articulo);
          //array_push($array_cantidad_por_recibir_OC, $cantidad_por_recibir_OC);
          array_push($array_cantidad_disponible, $cantidad_disponible);
          array_push($array_cantidad_pedida, $cantidad_pedida);
      }
/*
      if($cantidad_por_recibir_OC == 0 && !$serie_N)
      {
          $cantidad_pedida = $datos_compra['cantidad'];
      }
      else if(!$serie_N)
      {
          $cantidad_pedida = $cantidad_disponible; 
      }
      else
      {
          $cantidad_pedida = $datos_compra['cantidad'] - $cantidad_por_recibir_OC; 
      }

      $cantidad_disponible = $datos_compra['cantidad'] - $item['cantidad_por_recibir']; 
*/
//          $primeravez = true; 
//      }
//      else 
//      { 
//          $cantidad_pedida =  $cantidad_disponible;
//          $cantidad_disponible = $cantidad_pedida - $item['cantidad_por_recibir'];
//      }

/*
      $sql = "INSERT INTO td_entalmacen(fol_folio, cve_articulo, cve_lote, CantidadPedida, CantidadRecibida, CantidadDisponible, numero_serie, status, cve_usuario, cve_ubicacion, fecha_inicio, fecha_fin, tipo_entrada, costoUnitario, num_orden) 
        VALUES (".$id_entrada.", '".$item['cve_articulo']."', '".$item['lote']."', ".$cantidad_pedida.", ".$item['cantidad_por_recibir'].", ".$cantidad_disponible.", '".$data['serie']."', 'E', '".$data['usuario']."', '".$item["zona_recepcion"]."', '".date('Y-m-d H:i:s', strtotime($item['hora_de_recepcion']))."', '".date('Y-m-d H:i:s', strtotime($data['fechafin']))."', 1, ".$item['costo'].", ".$data["oc"].")";
*/

      $lote = "";
      $serie = "";

      $lote = strtoupper($item['lote']);
      $serie = "";

      if($lote == ' ') $lote = "";
      //if($data["tipo"] != 'RL')
      //{
      if(!$item['costo']) $item['costo'] = 0;
      if($cantidad_pedida == "") $cantidad_pedida = $item['cantidad_por_recibir'];

      //if($unidad_medida_articulo == 'XBX') {$cantidad_pedida = $cantidad_pedida*$unidades_por_caja; 
        //$item['cantidad_por_recibir'] = $item['cantidad_por_recibir']*$unidades_por_caja;
      //}


      $sql = "SELECT COUNT(*) as existe FROM td_entalmacen WHERE cve_articulo='$cve_articulo' AND cve_lote='$lote' AND cve_ubicacion='$zona_th' AND fol_folio = $id_entrada";
      $res_existe_entalmacen = $this->tools->dbQuery($sql)->fetch();
      $existe_entalmacen = $res_existe_entalmacen["existe"];

      if($existe_entalmacen)
      {//CantidadDisponible +".$cantidad_disponible."
          $sql = "UPDATE td_entalmacen SET CantidadRecibida = CantidadRecibida + ".$item['cantidad_por_recibir'].",
                                           CantidadDisponible = CantidadDisponible+(CantidadPedida-CantidadRecibida)
          WHERE cve_articulo='$cve_articulo' AND cve_lote='$lote' AND cve_ubicacion='$zona_th' AND fol_folio = $id_entrada";
      }//".$cantidad_disponible."
      else 
        $sql = "INSERT IGNORE INTO td_entalmacen(fol_folio, cve_articulo, cve_lote, CantidadPedida, CantidadRecibida, CantidadDisponible, numero_serie, status, cve_usuario, cve_ubicacion, fecha_inicio, fecha_fin, tipo_entrada, costoUnitario, num_orden, num_pedimento, fecha_pedimento) 
        VALUES (".$id_entrada.", '".$cve_articulo."', '".$lote."', ".$cantidad_pedida.", ".$item['cantidad_por_recibir'].",".($cantidad_pedida-$item['cantidad_por_recibir'])." , '".$serie."', 'E', '".$data['usuario']."', '".$item["zona_recepcion"]."', '".date('Y-m-d H:i:s', strtotime($item['hora_de_recepcion']))."', '".date('Y-m-d H:i:s', strtotime($data['fechafin']))."', 1, ".$item['costo'].", '".$data_oc."', '".$item["num_pedimento"]."', '".$item["fecha_pedimento"]."')";


      //echo "xxxxxxxxx".$id_entrada;
      //echo "sssssssss".$cantidad_pedida;
      //echo $sql;
      $result = $this->tools->dbQuery($sql);

        //if(!$result) echo $sql;
      //}
        $sql = "INSERT IGNORE INTO td_entalmacen_enviaSAP (Fol_Folio, Cve_Articulo, Cve_lote, Cant_Rec, Item, Fec_Envio, Enviado) VALUES(".$id_entrada.", '".$cve_articulo."', '".$lote."', ".$item['cantidad_por_recibir'].", '".$datos_compra['Item']."', NOW(), 0);";
          //echo $sql;
        $result = $this->tools->dbQuery($sql);
        if(!$result) echo $sql;

      if($lote)
      {
          $caducidad = $item['caducidad'];
          if($caducidad == '')
              $caducidad = '0000-00-00';
          if($lote_SN == 'lote')
          {
              $sql = "INSERT IGNORE INTO c_lotes(cve_articulo, Lote, Caducidad) VALUES('{$cve_articulo}', '{$lote}', '{$caducidad}')";
              $result = $this->tools->dbQuery($sql);
          }

          if($lote_SN == 'serie')
          {
              $sql = "INSERT IGNORE INTO c_serie(cve_articulo, numero_serie, fecha_ingreso) VALUES('{$cve_articulo}', '{$lote}', '{$caducidad}')";
              $result = $this->tools->dbQuery($sql);
          }

      }

      $sql = "INSERT INTO t_cardex(cve_articulo, cve_lote, fecha, origen, destino, cantidad, id_TipoMovimiento, cve_usuario, Cve_Almac) VALUES('$cve_articulo', '$lote', NOW(), '$id_entrada', '".$item["zona_recepcion"]."', ".$item['cantidad_por_recibir'].", 1, '$data_Cve_Usuario', (SELECT id FROM c_almacenp WHERE clave = '$data_Cve_Almac'))";
      $this->tools->dbQuery($sql);

/*
      if($serie_N)
        $cantidad_por_recibir_OC++;
      else
        $cantidad_por_recibir_OC = $datos_compra['cantidad'] - $item['cantidad_por_recibir'];
*/      
      //$result = $this->tools->dbInsert("td_entalmacen",$to_insert);
      
      /*if($existe_detalle == 0)
      {
        
      }*/
      /*else
      {
        $result = $this->tools->dbUpdate("td_entalmacen",$to_insert,array("Fol_Folio" =>$id_entrada,"cve_articulo" =>$item['cve_articulo'], "cve_lote"=>$item['lote']));
      }*/


//********************************************************************
//          INSERTAR EN T_PENDIENTEACOMODO
//********************************************************************

     $lote_pendienteacomodo = $lote;
     if($lote_pendienteacomodo != NULL)
        $lote_pendienteacomodo = $lote;
      else
        $lote_pendienteacomodo = '';

      //$cve_articulo = $data["arrDetalle"][0]["cve_articulo"];
      $cantidad_por_recibir = $item['cantidad_por_recibir'];
      $Cve_Proveedor = $data["Cve_Proveedor"];//($data["Cve_Proveedor"]!='')?($data["Cve_Proveedor"]):($proveedorID);

      $to_insert = array(
        "cve_articulo"       => $cve_articulo,
        "cve_lote"           => $lote_pendienteacomodo,
        "Cantidad"           => $cantidad_por_recibir,
        "cve_ubicacion"      => $zona_th,
        "ID_Proveedor"       => $Cve_Proveedor

      );
//       echo var_dump($to_insert);
//       die();
  
      $sql = "SELECT id FROM t_pendienteacomodo WHERE cve_articulo='$cve_articulo' AND cve_lote='$lote_pendienteacomodo' AND cve_ubicacion='$zona_th' AND ID_Proveedor='$Cve_Proveedor'";
      //echo $sql;
      $id_entrada_pendienteacomodo = $this->tools->dbQuery($sql)->fetch();
      $id_entrada_pendienteacomodo = $id_entrada_pendienteacomodo["id"];

      if($id_entrada_pendienteacomodo)
      {
          $sql = "UPDATE t_pendienteacomodo SET Cantidad=Cantidad+$cantidad_por_recibir WHERE id=$id_entrada_pendienteacomodo AND cve_articulo='$cve_articulo' AND cve_lote='$lote_pendienteacomodo' AND cve_ubicacion='$zona_th' AND ID_Proveedor='$Cve_Proveedor'";
          $this->tools->dbQuery($sql);
      }
      else 
      {
          $sql = "INSERT INTO t_pendienteacomodo(cve_articulo, cve_lote, Cantidad, cve_ubicacion, ID_Proveedor) VALUES('$cve_articulo', '$lote_pendienteacomodo', $cantidad_por_recibir, '$zona_th', '$Cve_Proveedor')";
          $this->tools->dbQuery($sql);

      }
//********************************************************************

    }
    
    $anio = date("Y");
    foreach ($data["arrDetalle"] as $dato)
    {
        $sql = "SELECT * FROM c_articulo where cve_articulo = '".$dato["cve_articulo"]."'";
        $articulo = $this->tools->dbQuery($sql)->fetch();
        
        if($articulo["tipo_producto"] == "Activo Fijo")
        {
          for($i = 1; $i <= $dato["cantidad"]; $i++)
          {
            $sql = "SELECT count(*) as cont FROM t_activo_fijo ;";
            $cont = $this->tools->dbQuery($sql)->fetch();
            $cve_activo = "";
            if($cabecera_aduana["areaSolicitante"] != "")
            {
              $cve_activo = ($cont["cont"]+1).'-'.$cabecera_aduana["areaSolicitante"].'-'.$anio;
              $to_insert_serie = array(
                'clave_activo' => $cve_activo,
                'id_orden_compra' => $cabecera_aduana["ID_Aduana"],
                'id_articulo' => $articulo["id"],
                'id_pedido' => 0,
                'fecha_entrada' => date('Y-m-d')
              );
            }
            else{
              $to_insert_serie = array(
                'id_orden_compra' => $cabecera_aduana["ID_Aduana"],
                'id_articulo' => $articulo["id"],
                'id_pedido' => 0,
                'fecha_entrada' => date('Y-m-d')
              );
            }
            $this->tools->dbInsert("t_activo_fijo",$to_insert_serie);
          }
        }
    }
    //$to_insert = array("status" => "T");
    //$this->tools->dbUpdate("th_aduana",$to_insert,array("num_pedimento" =>$data["oc"]));
   //PROCESO CONTENEDORES
   ///////////////////
   //////insertar datos con contenedor en td_entalmacenxtarima y cajas///////7 
    $clave_contenedor_selected = "";$TipoGen = 1;
    foreach ($data["arrDetalle"] as $item)
    {
        if($item['multiplo'] >= 1)
        {
          if($item['contenedor'] != "")
          {
            $pallet = $item['contenedor'];
            $cve_lp_pallet = $item['lp_selected'];
            $sql = "SELECT *, (SELECT id FROM c_almacenp WHERE clave = '$data_Cve_Almac') as id_almacen FROM c_charolas WHERE clave_contenedor = '$pallet' AND tipo = 'Pallet'";

            if(!$res_pallet=mysqli_query($conexion, $sql)) echo "Falló la preparación: (".mysqli_error($conexion).")";
            $hay_pallets = mysqli_num_rows($res_pallet);
            $row_pallet = mysqli_fetch_assoc($res_pallet);

            $IDContenedor     = $row_pallet['IDContenedor'];
            $cve_almac        = $row_pallet['id_almacen'];//$row_pallet['cve_almac'];
            $clave_contenedor = $row_pallet['Clave_Contenedor'];
            $descripcion      = utf8_encode($row_pallet['descripcion']);
            $tipo             = $row_pallet['tipo'];
            $Activo           = $row_pallet['Activo'];
            $alto             = $row_pallet['alto'];
            $ancho            = $row_pallet['ancho'];
            $fondo            = $row_pallet['fondo'];
            $peso             = $row_pallet['peso'];
            $pesomax          = $row_pallet['pesomax'];
            $capavol          = $row_pallet['capavol'];
            $CveLP            = $cve_lp_pallet;//$row_pallet['CveLP'];
            $TipoGen          = $row_pallet['TipoGen'];

            if($hay_pallets == 0)
            {

              $sql = "SELECT *, (SELECT id FROM c_almacenp WHERE clave = '$data_Cve_Almac') as id_almacen FROM c_charolas WHERE TipoGen = 1 AND tipo = 'Pallet' ORDER BY IDContenedor ASC LIMIT 1";

                if(!$res_pallet=mysqli_query($conexion, $sql)) echo "Falló la preparación: (".mysqli_error($conexion).")";
                $row_pallet = mysqli_fetch_assoc($res_pallet);

                $cve_almac        = $row_pallet['id_almacen'];//$row_pallet['cve_almac'];
                $clave_contenedor = $pallet;//$row_pallet['clave_contenedor'];
                $descripcion      = utf8_encode($row_pallet['descripcion']);
                $tipo             = $row_pallet['tipo'];
                $Activo           = $row_pallet['Activo'];
                $alto             = $row_pallet['alto'];
                $ancho            = $row_pallet['ancho'];
                $fondo            = $row_pallet['fondo'];
                $peso             = $row_pallet['peso'];
                $pesomax          = $row_pallet['pesomax'];
                $capavol          = $row_pallet['capavol'];
                $CveLP            = $row_pallet['CveLP'];
                $TipoGen          = $row_pallet['TipoGen'];

            }
          }

            if($item['lp_selected'] == '')
            {
//******************************************************************************************
//******************************************************************************************
/*
                $sql ="SELECT AUTO_INCREMENT  AS id FROM information_schema.TABLES
                       WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'c_charolas'";

                if(!$res_id = mysqli_query($conexion, $sql)) echo "Falló la preparación: (".mysqli_error($conexion).")";
                $row_autoid = mysqli_fetch_assoc($res_id);
                $nextid = $row_autoid['id'];
*/
                  $sql = "SELECT (MAX(IFNULL(IDContenedor, 0))+1) as nextid FROM c_charolas";

                  if(!$res_id = mysqli_query($conexion, $sql)) echo "Falló la preparación: (".mysqli_error($conexion).")";
                  $row_autoid = mysqli_fetch_assoc($res_id);
                  $nextid = $row_autoid['nextid'];

                $label_lp = $item['lp_selected'];

                if($IDContenedor != '')
                  $label_lp = "LP".str_pad($nextid, 6, "0", STR_PAD_LEFT).$id_entrada;

                if($TipoGen == 1) 
                {
                   //$clave_contenedor = $label_lp;
                //else
                  //$clave_contenedor .= "-".$nextid;
                   //$TipoGen = 0;
                  $sqlGuardar = "INSERT IGNORE INTO c_charolas(cve_almac, clave_contenedor, descripcion, Permanente, tipo, Activo, alto, ancho, fondo, peso, pesomax, capavol, CveLP, TipoGen) VALUES('$cve_almac', CONCAT('$clave_contenedor','-', '$IDContenedor'), '$descripcion', 0, '$tipo', '$Activo', '$alto', '$ancho', '$fondo', '$peso', '$pesomax', '$capavol', '$label_lp', 0)";
                  if($hay_pallets == 0)
                    $sqlGuardar = "INSERT IGNORE INTO c_charolas(cve_almac, clave_contenedor, descripcion, Permanente, tipo, Activo, alto, ancho, fondo, peso, pesomax, capavol, CveLP, TipoGen) VALUES('$cve_almac', '$label_lp', '$descripcion', 0, '$tipo', '$Activo', '$alto', '$ancho', '$fondo', '$peso', '$pesomax', '$capavol', '$label_lp', 0)";


                  if(!$res_id = mysqli_query($conexion, $sqlGuardar)) echo "Falló la preparación: (".mysqli_error($conexion).")";
                }else
                {
                  $sqlGuardar = "UPDATE c_charolas SET CveLP = '$label_lp' WHERE IDContenedor = '$IDContenedor'";

                  if(!$res_id = mysqli_query($conexion, $sqlGuardar)) echo "Falló la preparación: (".mysqli_error($conexion).")";
                }
//******************************************************************************************
//******************************************************************************************
            }
            else
            {
                $label_lp = $item['lp_selected'];

                if($TipoGen == 1) 
                {
  /*
                  $sql ="SELECT AUTO_INCREMENT  AS id FROM information_schema.TABLES
                           WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'c_charolas'";

                  if(!$res_id = mysqli_query($conexion, $sql)) echo "Falló la preparación: (".mysqli_error($conexion).")";
                  $row_autoid = mysqli_fetch_assoc($res_id);
                  $nextid = $row_autoid['id'];
*/

                  $sql = "SELECT DISTINCT IFNULL(ClaveEtiqueta, '') as ClaveEtiqueta FROM td_entalmacenxtarima WHERE ClaveEtiqueta = (SELECT clave_contenedor FROM c_charolas WHERE CveLP = '$label_lp' AND tipo = 'Pallet') AND fol_folio = $id_entrada";

                  if(!$res_lp = mysqli_query($conexion, $sql)) echo "Falló la preparación: (".mysqli_error($conexion).")";
                  $row_lp = mysqli_fetch_assoc($res_lp);
                  $ClaveEtiqueta = $row_lp['ClaveEtiqueta'];

                  if($ClaveEtiqueta == '')
                  {
                      $sql = "SELECT (MAX(IFNULL(IDContenedor, 0))+1) as nextid FROM c_charolas";

                      if(!$res_id = mysqli_query($conexion, $sql)) echo "Falló la preparación: (".mysqli_error($conexion).")";
                      $row_autoid = mysqli_fetch_assoc($res_id);
                      $nextid = $row_autoid['nextid'];



                      $sqlGuardar = "INSERT IGNORE INTO c_charolas(cve_almac, clave_contenedor, descripcion, Permanente, tipo, Activo, alto, ancho, fondo, peso, pesomax, capavol, CveLP, TipoGen) VALUES('$cve_almac', CONCAT('$clave_contenedor','-', '$nextid'), '$descripcion', 0, '$tipo', '$Activo', '$alto', '$ancho', '$fondo', '$peso', '$pesomax', '$capavol', '$label_lp', 0)";
    /*
                      if($hay_pallets == 0)
                        $sqlGuardar = "INSERT IGNORE INTO c_charolas(cve_almac, clave_contenedor, descripcion, Permanente, tipo, Activo, alto, ancho, fondo, peso, pesomax, capavol, CveLP, TipoGen) VALUES('$cve_almac', '$label_lp', '$descripcion', 0, '$tipo', '$Activo', '$alto', '$ancho', '$fondo', '$peso', '$pesomax', '$capavol', '$label_lp', 0)";
    */
                      if(!$res_id = mysqli_query($conexion, $sqlGuardar)) echo "Falló la preparación: (".mysqli_error($conexion).")";

                      $item['contenedor'] = $clave_contenedor.'-'.$nextid;
                  }
                  else 
                      $item['contenedor'] = $ClaveEtiqueta;
                }
                else
                {
                      //$sqlGuardar = "DELETE FROM c_charolas WHERE CveLP = '$label_lp'";
                      //if(!$res_id = mysqli_query($conexion, $sqlGuardar)) echo "Falló la preparación: (".mysqli_error($conexion).")";

                      $sqlGuardar = "UPDATE c_charolas SET CveLP = '$label_lp' WHERE IDContenedor = '$IDContenedor'";
                      if(!$res_id = mysqli_query($conexion, $sqlGuardar)) echo "Falló la preparación: (".mysqli_error($conexion).")";
                }
            }

            if($item['lote'] == NULL){$item['lote'] = " ";}
            $can = $item['cantidad_por_recibir'] / $item['multiplo'];
            $can = round($can, 0, PHP_ROUND_HALF_DOWN);
          
            //if($can == 0)
            //{
              $can = $item['cantidad_por_recibir'] ;
            //}
              //$residuo = $item['cantidad_por_recibir'] % $item['multiplo'];

              if($item['contenedor'])
              {
               $insert_xtarima = array(
                 "fol_folio"      => $id_entrada,
                 "cve_articulo"   => $item['clave_articulo'],
                 "cve_lote"       => $item['lote'],
                 "ClaveEtiqueta"  => $item['contenedor'],
                 "Cantidad"       => $can,
                 "Ubicada"        => "N",
                 "Activo"         => 1,
                 "PzsXCaja"       => $item['multiplo']
                 );

               $this->tools->dbInsert("td_entalmacenxtarima",$insert_xtarima);
             }

              $label_caja = "";
              if($item['unidad_medida'] == 'XBX' && $item["caja_generica"] == 'N' && $recepcion_por_cajas)
              {
                  $clave_articulo = $item['clave_articulo'];
                  $lote = $item['lote'];
                  $lote = trim($lote);
                  $pzasxcaja = $item['multiplo'];
                  if($pzasxcaja == 0) $pzasxcaja = 1;
                  $n_cajas = $item['cantidad_por_recibir']/$pzasxcaja;
                  for($n = 0; $n < $n_cajas; $n++)
                  {
                  //$sql ="SELECT AUTO_INCREMENT  AS id FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'c_charolas'";
                  $sql = "SELECT (MAX(IFNULL(IDContenedor, 0))+1) as nextid FROM c_charolas";

                  if(!$res_id = mysqli_query($conexion, $sql)) echo "Falló la preparación: (".mysqli_error($conexion).")";
                  $row_autoid = mysqli_fetch_assoc($res_id);
                  $nextid = $row_autoid['nextid'];

                  $sql ="SELECT IFNULL(tipo_caja, '') as tipo_caja FROM c_articulo WHERE cve_articulo = '$clave_articulo'";

                  if(!$res_id = mysqli_query($conexion, $sql)) echo "Falló la preparación: (".mysqli_error($conexion).")";
                  $row_caja = mysqli_fetch_assoc($res_id);
                  $tipo_caja = $row_caja['tipo_caja'];

                  if($tipo_caja != "")
                      $sql ="SELECT * FROM c_tipocaja WHERE id_tipocaja = $tipo_caja";
                  else
                      $sql ="SELECT * FROM c_tipocaja WHERE clave = '1'"; //caja generica

                  if(!$res_id = mysqli_query($conexion, $sql)) echo "Falló la preparación: (".mysqli_error($conexion).")";
                  $row_caja = mysqli_fetch_assoc($res_id);

                  $clave_caja = $row_caja['clave'];
                  $descripcion = $row_caja['descripcion'];
                  $alto = $row_caja['alto'];
                  $ancho = $row_caja['ancho'];
                  $largo = $row_caja['largo'];
                  $peso = $row_caja['peso'];
                  if($alto == '') $alto = 0;if($ancho == '') $ancho = 0; if($largo == '') $largo = 0; if($peso == '') $peso = 0;
                  $volumen = $alto*$ancho*$largo;

                     $label_caja = "CJ".str_pad($nextid, 6, "0", STR_PAD_LEFT);
                  $cve_almacen = $data["Cve_Almac"];
                  $sqlGuardar = "INSERT IGNORE INTO c_charolas(cve_almac, clave_contenedor, descripcion, Permanente, tipo, Activo, alto, ancho, fondo, peso, pesomax, capavol, CveLP, TipoGen) VALUES((SELECT id FROM c_almacenp WHERE clave = '$cve_almacen'), CONCAT('$clave_caja','-', '$nextid'), '$descripcion', 0, 'Caja', 1, '$alto', '$ancho', '$largo', '$peso', 0, $volumen, '$label_caja', 0)";

                  if(!$res_id = mysqli_query($conexion, $sqlGuardar)) echo "Falló la preparación: (".mysqli_error($conexion).")";
//*************************************************************************************
                  $clave_caja = $clave_caja.'-'.$nextid;
                  $sql ="SELECT IDContenedor FROM c_charolas WHERE clave_contenedor = '$clave_caja' AND tipo = 'Caja'";
                  if(!$res_id = mysqli_query($conexion, $sql)) echo "Falló la preparación: (".mysqli_error($conexion).")";
                  $row_caja = mysqli_fetch_assoc($res_id);
                  $id_caja = $row_caja['IDContenedor'];
//*************************************************************************************

                  $claveEtiqueta = $item['contenedor'];
                  if($item['contenedor'])
                    $sql = "INSERT INTO td_entalmacencaja(Fol_Folio, Cve_Almac, Cve_Articulo, Cve_Lote, PzsXCaja, Id_Caja, ClaveEtiqueta) VALUES ('$id_entrada', '$cve_almacen', '$clave_articulo', '$lote', $pzasxcaja, $id_caja, '$claveEtiqueta')";
                  else
                    $sql = "INSERT INTO td_entalmacencaja(Fol_Folio, Cve_Almac, Cve_Articulo, Cve_Lote, PzsXCaja, Id_Caja) VALUES ('$id_entrada', '$cve_almacen', '$clave_articulo', '$lote', $pzasxcaja, $id_caja)";

                  if(!$res_id = mysqli_query($conexion, $sql)) echo "Falló la preparación: (".mysqli_error($conexion).")".$sql;
                  }
              }

              


               /*
          if($item['lp_selected'] == '')
          {
                   $a = 'LP';
              if(preg_match("/{$a}/", $item['pallet']))
              {
                 $sql = "UPDATE c_charolas
                 SET CveLP = '{$item['pallet']}'
                 WHERE IDContenedor = '{$item['id_con']}';
                 ";  
                 $sth = \db()->prepare( $sql );
                 $sth->execute();
              }
          }
          else
            
          if($item['lp_selected'] != '' && $TipoGen == 1)
          {
                 $pallet = $item['contenedor'];
                 $lp_selected = $item['lp_selected'];

                 $sql = "UPDATE c_charolas
                 SET clave_contenedor = CONCAT('{$pallet}', '-', IDContenedor)
                 WHERE CveLP = '{$lp_selected}';
                 ";  
                 $sth = \db()->prepare( $sql );
                 $sth->execute();
          }
          */
/*
          if($residuo > 0)
          {
               $insert_xtarimaa = array(
               "fol_folio"      => $id_entrada,
               "cve_articulo"   => $item['clave_articulo'],
               "cve_lote"       => $item['lote'],
               "ClaveEtiqueta"  => $item['contenedor'],
               "Cantidad"       => 1,
               "Ubicada"        => "N",
               "Activo"         => 1,
               "PzsXCaja"       => $residuo
               );

              // echo var_dump( $insert_xtarimaa);
              // die();
               $this->tools->dbInsert("td_entalmacenxtarima",$insert_xtarimaa);
          }
*/
        //insertar datos de contenedor en td_entalmacencajas
/*
        if($item['multiplo'] > 1)
        {
          $can = $item['cantidad_por_recibir'] / $item['multiplo'];
          $can = round($can, 0, PHP_ROUND_HALF_DOWN);
              if($can == 0)
              {
                  $can = $item['cantidad_por_recibir'];
              }
               $residuo = $item['cantidad_por_recibir'] % $item['multiplo'];

            $insert_xcaja = array(
                "Fol_Folio"      => $id_entrada,
                "Cve_Articulo"   => $item['clave_articulo'],
                "Cve_Lote"       => $item['lote'],
                "PiezasXCaja"    => $item['multiplo'],
                "NCajas"         => $can,
                "Ubicadas"        => 0
            );

           //echo var_dump( $insert_xcaja);
           //die();
           $this->tools->dbInsert("td_entalmacencajas",$insert_xcaja);
        }
      
          if($residuo > 0)
          {
             $insert_xcajaa = array(
           "Fol_Folio"      => $id_entrada,
           "Cve_Articulo"   => $item['clave_articulo'],
           "Cve_Lote"       => $item['lote'],
           "PiezasXCaja"    => $residuo,
           "NCajas"         => 1,
           "Ubicadas"        => 0
           );
          // echo var_dump( $insert_xcajaa);
          // die();

            $this->tools->dbInsert("td_entalmacencajas",$insert_xcajaa);

          }
*/
       }
     
   }
  
    
    //insertar si tiene contenedor pero no maneja cajas
      foreach ($data["arrDetalle"] as $item)
      {
       // echo var_dump($item);
       //         die();
        if($item['contenedor'] != "" && ($item['multiplo'] <= 1 || $item['multiplo'] == null))
        {
                $pallet = $item['contenedor'];

                $sql = "SELECT * FROM c_charolas WHERE clave_contenedor = '$pallet' AND tipo = 'Pallet'";

                if(!$res_pallet=mysqli_query($conexion, $sql)) echo "Falló la preparación: (".mysqli_error($conexion).")";
                $hay_pallets = mysqli_num_rows($res_pallet);
                $row_pallet = mysqli_fetch_assoc($res_pallet);

                $IDContenedor     = $row_pallet['IDContenedor'];
                $cve_almac        = $row_pallet['cve_almac'];
                $clave_contenedor = $row_pallet['Clave_Contenedor'];
                $descripcion      = utf8_encode($row_pallet['descripcion']);
                $tipo             = $row_pallet['tipo'];
                $Activo           = $row_pallet['Activo'];
                $alto             = $row_pallet['alto'];
                $ancho            = $row_pallet['ancho'];
                $fondo            = $row_pallet['fondo'];
                $peso             = $row_pallet['peso'];
                $pesomax          = $row_pallet['pesomax'];
                $capavol          = $row_pallet['capavol'];
                $CveLP            = $row_pallet['CveLP'];
                $TipoGen          = $row_pallet['TipoGen'];

            if($hay_pallets == 0)
            {

              $sql = "SELECT * FROM c_charolas WHERE TipoGen = 1 AND tipo = 'Pallet' ORDER BY IDContenedor ASC LIMIT 1";

                if(!$res_pallet=mysqli_query($conexion, $sql)) echo "Falló la preparación: (".mysqli_error($conexion).")";
                $row_pallet = mysqli_fetch_assoc($res_pallet);

                $cve_almac        = $row_pallet['cve_almac'];
                $clave_contenedor = $row_pallet['Clave_Contenedor'];
                $descripcion      = utf8_encode($row_pallet['descripcion']);
                $tipo             = $row_pallet['tipo'];
                $Activo           = $row_pallet['Activo'];
                $alto             = $row_pallet['alto'];
                $ancho            = $row_pallet['ancho'];
                $fondo            = $row_pallet['fondo'];
                $peso             = $row_pallet['peso'];
                $pesomax          = $row_pallet['pesomax'];
                $capavol          = $row_pallet['capavol'];
                $CveLP            = $row_pallet['CveLP'];
                $TipoGen          = $row_pallet['TipoGen'];

            }

                if($item['lp_selected'] == '')
                {
//******************************************************************************************
//******************************************************************************************
/*
                        $sql ="SELECT AUTO_INCREMENT  AS id FROM information_schema.TABLES
                               WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'c_charolas'";

                        if(!$res_id = mysqli_query($conexion, $sql)) echo "Falló la preparación: (".mysqli_error($conexion).")";
                        $row_autoid = mysqli_fetch_assoc($res_id);
                        $nextid = $row_autoid['id'];
*/
                  $sql = "SELECT (MAX(IFNULL(IDContenedor, 0))+1) as nextid FROM c_charolas";

                  if(!$res_id = mysqli_query($conexion, $sql)) echo "Falló la preparación: (".mysqli_error($conexion).")";
                  $row_autoid = mysqli_fetch_assoc($res_id);
                  $nextid = $row_autoid['nextid'];

                        $label_lp = "LP".str_pad($nextid, 6, "0", STR_PAD_LEFT).$id_entrada;

                        if($TipoGen == 1) 
                        {
                           //$clave_contenedor = $label_lp;
                        //else
                          //$clave_contenedor .= "-".$nextid;
                           //$TipoGen = 0;
                          if($IDContenedor != '')
                            $label_lp = "LP".str_pad($nextid, 6, "0", STR_PAD_LEFT).$id_entrada;

                          $sqlGuardar = "INSERT IGNORE INTO c_charolas(cve_almac, clave_contenedor, descripcion, Permanente, tipo, Activo, alto, ancho, fondo, peso, pesomax, capavol, CveLP, TipoGen) VALUES('$cve_almac', CONCAT('$clave_contenedor','-', '$IDContenedor'), '$descripcion', 0, '$tipo', '$Activo', '$alto', '$ancho', '$fondo', '$peso', '$pesomax', '$capavol', '$label_lp', 0)";

                          if($hay_pallets == 0)
                            $sqlGuardar = "INSERT IGNORE INTO c_charolas(cve_almac, clave_contenedor, descripcion, Permanente, tipo, Activo, alto, ancho, fondo, peso, pesomax, capavol, CveLP, TipoGen) VALUES('$cve_almac', '$label_lp', '$descripcion', 0, '$tipo', '$Activo', '$alto', '$ancho', '$fondo', '$peso', '$pesomax', '$capavol', '$label_lp', 0)";

                          $data["sql"] = $sqlGuardar;
                          if(!$res_id = mysqli_query($conexion, $sqlGuardar)) echo "Falló la preparación: (".mysqli_error($conexion).")";
                        }else
                        {
                          $sqlGuardar = "UPDATE c_charolas SET CveLP = '$label_lp' WHERE IDContenedor = '$IDContenedor'";

                          if(!$res_id = mysqli_query($conexion, $sqlGuardar)) echo "Falló la preparación: (".mysqli_error($conexion).")";
                        }
//******************************************************************************************
//******************************************************************************************
                }
                else
                {
                    //$clave_contenedor = $item['lp_selected'];
                    $label_lp = $item['lp_selected'];

                    if($TipoGen == 1) 
                    {
/*
                      $sql ="SELECT AUTO_INCREMENT  AS id FROM information_schema.TABLES
                             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'c_charolas'";

                      if(!$res_id = mysqli_query($conexion, $sql)) echo "Falló la preparación: (".mysqli_error($conexion).")";
                      $row_autoid = mysqli_fetch_assoc($res_id);
                      $nextid = $row_autoid['id'];
*/
                  $sql = "SELECT DISTINCT IFNULL(ClaveEtiqueta, '') as ClaveEtiqueta FROM td_entalmacenxtarima WHERE ClaveEtiqueta = (SELECT clave_contenedor FROM c_charolas WHERE CveLP = '$label_lp' AND tipo = 'Pallet') AND fol_folio = $id_entrada";

                  if(!$res_lp = mysqli_query($conexion, $sql)) echo "Falló la preparación: (".mysqli_error($conexion).")";
                  $row_lp = mysqli_fetch_assoc($res_lp);
                  $ClaveEtiqueta = $row_lp['ClaveEtiqueta'];

                  if($ClaveEtiqueta == '')
                  {
                    $sql = "SELECT (MAX(IFNULL(IDContenedor, 0))+1) as nextid FROM c_charolas";

                    if(!$res_id = mysqli_query($conexion, $sql)) echo "Falló la preparación: (".mysqli_error($conexion).")";
                    $row_autoid = mysqli_fetch_assoc($res_id);
                    $nextid = $row_autoid['nextid'];

                        $sqlGuardar = "INSERT IGNORE INTO c_charolas(cve_almac, clave_contenedor, descripcion, Permanente, tipo, Activo, alto, ancho, fondo, peso, pesomax, capavol, CveLP, TipoGen) VALUES('$cve_almac', CONCAT('$clave_contenedor','-', '$IDContenedor'), '$descripcion', 0, '$tipo', '$Activo', '$alto', '$ancho', '$fondo', '$peso', '$pesomax', '$capavol', '$label_lp', 0)";//ON DUPLICATE KEY UPDATE CveLP = '$label_lp'

  /*
                        if($hay_pallets == 0)
                          $sqlGuardar = "INSERT IGNORE INTO c_charolas(cve_almac, clave_contenedor, descripcion, Permanente, tipo, Activo, alto, ancho, fondo, peso, pesomax, capavol, CveLP, TipoGen) VALUES('$cve_almac', '$label_lp', '$descripcion', 0, '$tipo', '$Activo', '$alto', '$ancho', '$fondo', '$peso', '$pesomax', '$capavol', '$label_lp', 0)";
  */
                        if(!$res_id = mysqli_query($conexion, $sqlGuardar)) echo "Falló la preparación: (".mysqli_error($conexion).")";
                        $item['contenedor'] = $clave_contenedor.'-'.$nextid;
                    }
                    else
                        $item['contenedor'] = $ClaveEtiqueta;

                    }
                    else
                    {
                      //$sqlGuardar = "DELETE FROM c_charolas WHERE CveLP = '$label_lp'";
                      //if(!$res_id = mysqli_query($conexion, $sqlGuardar)) echo "Falló la preparación: (".mysqli_error($conexion).")";

                      $sqlGuardar = "UPDATE c_charolas SET CveLP = '$label_lp' WHERE IDContenedor = '$IDContenedor'";
                      if(!$res_id = mysqli_query($conexion, $sqlGuardar)) echo "Falló la preparación: (".mysqli_error($conexion).")";
                    }
                }

          if($item['lote'] == NULL){$item['lote'] = " ";}
          
            /*
             $insert_xtarimasin = array(
               "fol_folio"      => $id_entrada,
               "cve_articulo"   => $item['clave_articulo'],
               "cve_lote"       => $item['lote'],
               "ClaveEtiqueta"  => $item['contenedor'], //$label_lp, 
               "Cantidad"       => $item['cantidad_por_recibir'],
               "Ubicada"        => 'N',
               "Activo"         => 1,
               "PzsXCaja"       => 1
               );
             $this->tools->dbInsert("td_entalmacenxtarima",$insert_xtarimasin);
               */
                /*
               if($item['lp_selected'] == '')
               {
                   $a = 'LP';
                  if(preg_match("/{$a}/", $item['pallet']))
                  {
                   $sql = "UPDATE c_charolas
                   SET CveLP = '{$item['pallet']}'
                   WHERE IDContenedor = '{$item['id_con']}';
                   ";  
                    $sth = \db()->prepare($sql);
                    $sth->execute();
                  }
                }
                else
                  */
                /*
                if($item['lp_selected'] != '' && $TipoGen == 1)
                {
                       $pallet = $item['contenedor'];
                       $lp_selected = $item['lp_selected'];

                       $sql = "UPDATE c_charolas
                       SET clave_contenedor = CONCAT('{$pallet}', '-', IDContenedor)
                       WHERE CveLP = '{$lp_selected}';
                       ";  
                       $sth = \db()->prepare( $sql );
                       $sth->execute();
                }
                */
          }
     }

//********************************************************************
//          INSERTAR EN T_PENDIENTEACOMODO
/********************************************************************

     $lote_pendienteacomodo = $data["arrDetalle"][0]['lote'];
     if($lote_pendienteacomodo != NULL)
        $lote_pendienteacomodo = $data["arrDetalle"][0]['lote'];
      else
        $lote_pendienteacomodo = '';

      $cve_articulo = $data["arrDetalle"][0]["cve_articulo"];
      $cantidad_por_recibir = $data["arrDetalle"][0]['cantidad_por_recibir'];
      $Cve_Proveedor = $data['Cve_Proveedor'];

      $to_insert = array(
        "cve_articulo"       => $cve_articulo,
        "cve_lote"           => $lote_pendienteacomodo,
        "Cantidad"           => $cantidad_por_recibir,
        "cve_ubicacion"      => $zona_th,
        "ID_Proveedor"       => $Cve_Proveedor

      );
//       echo var_dump($to_insert);
//       die();
  
      $sql = "SELECT id FROM t_pendienteacomodo WHERE cve_articulo='$cve_articulo' AND cve_lote='$lote_pendienteacomodo' AND cve_ubicacion='$zona_th' AND ID_Proveedor=$Cve_Proveedor";
      $id_entrada = $this->tools->dbQuery($sql)->fetch();
      $id_entrada = $id_entrada["id"];

      if($id_entrada)
      {
          $sql = "UPDATE t_pendienteacomodo SET Cantidad=Cantidad+$cantidad_por_recibir WHERE id=$id_entrada AND cve_articulo='$cve_articulo' AND cve_lote='$lote_pendienteacomodo' AND cve_ubicacion='$zona_th' AND ID_Proveedor=$Cve_Proveedor";
          $this->tools->dbQuery($sql);
      }
      else 
      {
          $sql = "INSERT INTO t_pendienteacomodo(cve_articulo, cve_lote, Cantidad, cve_ubicacion, ID_Proveedor) VALUES('$cve_articulo', '$lote_pendienteacomodo', $cantidad_por_recibir, '$zona_th', $Cve_Proveedor)";
          $this->tools->dbQuery($sql);
      }
*/ //********************************************************************

    //$sql = "SELECT DISTINCT IF((SELECT COUNT(DISTINCT cve_articulo) FROM td_aduana WHERE num_orden = '".$data["oc"]."') = (SELECT COUNT(DISTINCT cve_articulo) FROM td_entalmacen WHERE num_orden = '".$data["oc"]."'), 'T', 'C') AS Status_OC FROM th_aduana c WHERE c.num_pedimento = '".$data["oc"]."';";

      //$sql = "SELECT IF(SUM(CantidadPedida) = SUM(IFNULL(CantidadUbicada, 0)+IFNULL(CantidadRecibida, 0)), 'T', 'I') as Status_OC FROM td_entalmacen WHERE fol_folio = '$id_entrada';";

    if($data["tipo"] == 'RL')
    {

        $sql = "SELECT ID_Proveedor FROM c_proveedores WHERE cve_proveedor = '$cve_proveedor_empresa'";
        $row_proveedor = $this->tools->dbQuery($sql)->fetch();
        $ID_clave_empresa = $row_proveedor["ID_Proveedor"];

        $sql = "SELECT cve_proveedor FROM c_proveedores WHERE ID_Proveedor = '$Cve_Proveedor'";
        $row_proveedor = $this->tools->dbQuery($sql)->fetch();
        $ID_Proveedor_clave = $row_proveedor["cve_proveedor"];

        //$sql = "INSERT INTO th_aduana(num_pedimento, fech_pedimento, Factura, fech_llegPed, STATUS, ID_Proveedor, ID_Protocolo, Consec_protocolo, cve_usuario, Cve_Almac, procedimiento) (SELECT id_ocompra, NOW(), Fol_OEP, NOW(), 'T', Cve_Proveedor, ID_Protocolo, Consec_protocolo, Cve_Usuario, Cve_Almac, (SELECT cve_proveedor FROM c_proveedores WHERE ID_Proveedor = th_entalmacen.Cve_Proveedor) AS procedimiento FROM th_entalmacen WHERE id_ocompra = {$data_oc})";
/*
      $sql = "INSERT INTO th_aduana(num_pedimento, fech_pedimento, Factura, fech_llegPed, STATUS, ID_Proveedor, ID_Protocolo, Consec_protocolo, cve_usuario, Cve_Almac, procedimiento) (SELECT id_ocompra, NOW(), Fol_OEP, NOW(), 'T', '$ID_clave_empresa', ID_Protocolo, Consec_protocolo, Cve_Usuario, Cve_Almac, '$ID_Proveedor_clave' FROM th_entalmacen WHERE id_ocompra = {$data_oc} ORDER BY Fec_Entrada DESC LIMIT 1)";
        //echo $sql;
        $rs = mysqli_query($conexion, $sql);

        $sql = "INSERT INTO td_aduana(cve_articulo, cantidad, cve_lote, num_orden) (SELECT cve_articulo, CantidadPedida, cve_lote, '{$data_oc}' FROM td_entalmacen WHERE num_orden = {$data_oc})";
        $rs = mysqli_query($conexion, $sql);

        $sql = "INSERT INTO td_aduanaxtarima(Num_Orden, Cve_Articulo, Cve_Lote, ClaveEtiqueta, Cantidad, Recibida) (SELECT '{$data_oc}', cve_articulo, cve_lote, ClaveEtiqueta, Cantidad, 'S' FROM td_entalmacenxtarima WHERE fol_folio = (SELECT Fol_Folio FROM th_entalmacen WHERE id_ocompra = {$data_oc}))";
        $rs = mysqli_query($conexion, $sql);
*/

    }



    if($data["tipo"] != 'RL')
    {

        $sql = "SELECT IF((SELECT SUM(cantidad) FROM td_aduana WHERE num_orden = '".$data["oc"]."') = SUM(IFNULL(CantidadRecibida, 0)), 'T', 'I') AS Status_OC FROM td_entalmacen WHERE fol_folio = '$id_entrada'";
      $oc_status = $this->tools->dbQuery($sql)->fetch();
      $status_oc = $oc_status["Status_OC"];

      $sql = "UPDATE th_aduana SET status = '$status_oc' WHERE num_pedimento = '".$data["oc"]."';";
      $status_aduana = $this->tools->dbQuery($sql);

      $sql = "UPDATE th_entalmacen SET status = '$status_oc' WHERE fol_folio = '$id_entrada';";
      $status_aduana = $this->tools->dbQuery($sql);
    }

      $sql = "DELETE FROM td_entalmacenxtarima WHERE cve_articulo NOT IN (SELECT cve_articulo FROM td_entalmacen WHERE fol_folio = '$id_entrada') AND fol_folio = '$id_entrada'"; //NO SÉ PORQUÉ ESTÁ AGREGANDO UN ARTÍCULO EXTRA ASÍ QUE SI NO EXISTE EN td_entalmacen LO ELIMINO
      $this->tools->dbQuery($sql);

      $sql = "INSERT INTO t_trazabilidad_existencias(cve_almac, cve_articulo, cve_lote,cantidad, ntarima, id_proveedor, folio_entrada, folio_oc, factura_ent, factura_oc, proyecto, id_tipo_movimiento) 
              (SELECT DISTINCT a.id AS cve_almac, IFNULL(t.cve_articulo, d.cve_articulo) AS cve_articulo, IFNULL(t.cve_lote, d.cve_lote) AS cve_lote, 
                              IFNULL(t.Cantidad, d.CantidadRecibida) AS cantidad, ch.IDContenedor AS ntarima, e.Cve_Proveedor AS id_proveedor, 
                              e.Fol_Folio AS folio_entrada, e.id_ocompra AS folio_oc, IFNULL(e.Fact_Prov, '') AS factura_ent, IFNULL(oc.Factura, '') AS factura_oc,
                              IFNULL(e.Proyecto, '') AS proyecto, 1 AS id_tipo_movimiento
              FROM th_entalmacen e
              LEFT JOIN c_almacenp a ON a.clave = e.Cve_Almac
              LEFT JOIN td_entalmacen d ON d.fol_folio = e.Fol_Folio
              LEFT JOIN td_entalmacenxtarima t ON t.fol_folio = e.Fol_Folio
              LEFT JOIN c_charolas ch ON ch.clave_contenedor = t.ClaveEtiqueta
              LEFT JOIN th_aduana oc ON oc.num_pedimento = IF(e.tipo != 'RL' AND e.id_ocompra IS NOT NULL, e.id_ocompra, NULL)
              WHERE e.Fol_Folio = '$id_entrada')";
      $this->tools->dbQuery($sql);

    return $data; 
  }//receiveOC($data)
  
  function VerificarFacturaEntrada_Repetida($factura)
  {
    $success = true;

    $sql = "SELECT COUNT(*) Factura FROM th_entalmacen WHERE Fact_Prov='$factura' AND Fact_Prov<>'';";
    $num_factura = $this->tools->dbQuery($sql)->fetch();
    $num_factura = $num_factura["Factura"];

    if($num_factura > 0) $success = false;

    return $success;
  }

  function VerificarFacturaOC_ERP_Repetido($factura)
  {
    $success = true;

    $sql = "SELECT COUNT(*) Factura FROM th_aduana WHERE factura='$factura' AND factura<>'';";
    $num_factura = $this->tools->dbQuery($sql)->fetch();
    $num_factura = $num_factura["Factura"];

    if($num_factura > 0) $success = false;

    return $success;
  }

    function guardarEntradaLibre($_post)
    {
      $tabla_th_entrada = 'th_entalmacen';
      $tabla_td_entrada = 'td_entalmacen';
      
      $sql = 'Select if(max(Fol_Folio) is null,1,max(Fol_Folio)+1) as Fol_Folio from th_entalmacen';
      $sth = \db()->prepare( $sql );
      $sth->execute();
  	  $folio_next = $sth->fetch();
      
      //echo var_dump();
      //die();
      
      try
      {
        $to_insert_th_entalmacen = array(
          "Fol_Folio"           => $folio_next["Fol_Folio"],
          "Cve_Almac"           => $_post['Cve_Almac'],
          "Fec_Entrada"         => $_post['Fec_Entrada'],
          "Cve_Usuario"         => $_post['Cve_Usuario'],
          "Cve_Proveedor"       => $_post['Cve_Proveedor'],
          "STATUS"              => "E",
          "tipo"                => $_post['tipo'],
          "HoraInicio"          => $_post['HoraInicio'],
          "ID_Protocolo"        => $_post['ID_Protocolo'],
          "Consec_protocolo"    => $_post['Consec_protocolo'],
          "cve_ubicacion"       => $_post['zona'],
          "HoraFin"             => $_post['HoraFin'],
          "Fact_Prov"           => $_post['Fact_Prov']
          
        );
            
        $insert_orden = $this->tools->dbInsert($tabla_th_entrada,$to_insert_th_entalmacen);
        $orden_id = $this->tools->insertId;
       
        
        if (!empty($_post["arrDetalle"]))
        {
          //$td_aduana_delete = $this->tools->dbQuery("Delete From td_aduana WHERE num_orden = '".$num_pedimento."'");
          foreach ($_post["arrDetalle"] as $item)
          {
            $to_insert = array(
              "fol_folio"          => $folio_next["Fol_Folio"],
              "cve_articulo"       => $item['clave_articulo'],
              "CantidadPedida"     => 0,
              "CantidadRecibida"   => $item['cantidad'],
              "CantidadDisponible" => $item['cantidad'],
              "numero_serie"       => $item['serie'],
              "status"             => "E",
              "cve_usuario"        => $item['usuario'],
              "cve_ubicacion"      => $_post['zona'],
              "fecha_inicio"       => $_post['fechainicio'],//NOW()
              "fecha_fin"          => $_post['fechafin'],//NOW()
              "tipo_entrada"       => 0,
              "costoUnitario"      => $item['costo']
            );
            $insert_articulo = $this->tools->dbInsert($tabla_td_entrada,$to_insert);
          }
        }
      }
      catch(Exception $e)
      {
        return 'ERROR: ' . $e->getMessage();
      }
    }
  
    function folioConsecutico() {
      $sql = 'Select if(max(Fol_Folio) is null,1,max(Fol_Folio)+1) as Fol_Folio from th_entalmacen';
	    // echo $sql; exit;

      $sth = \db()->prepare( $sql );

      //$sth->setFetchMode( \PDO::FETCH_CLASS, '\Articulos\Articulos' );
      $sth->execute();

  	  return $sth->fetch();


    }

  
    function getArraySQL($sql){

        $conexion = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

        mysqli_set_charset($conexion, "utf8");

        if(!$result = mysqli_query($conexion, $sql)) 
            echo "Falló la preparación: (" . mysqli_error($conexion) . ") ";;

        $rawdata = array();

        $i = 0;

        while($row = mysqli_fetch_assoc($result))
        {
            $rawdata[$i] = $row;
            $i++;
        }

        mysqli_close($conexion);

        return $rawdata;
    }

    function updateFiles() {

        $sql = 'SELECT num_pedimento, cve_usuario FROM '.self::TABLE.' WHERE status = "A"';

        $array = $this->getArraySQL($sql);

        for($i = 0; $i < count($array); $i++){

            $user = $array[$i]["cve_usuario"];
            $id = $array[$i]["num_pedimento"];
            $this->modoEdicion($id,'C',$user);
        }
    }
  
    function precio() {

        $sql = 'SELECT num_pedimento, cve_usuario FROM '.self::TABLE.' WHERE status = "A"';

        $array = $this->getArraySQL($sql);

        for($i = 0; $i < count($array); $i++){

            $user = $array[$i]["cve_usuario"];
            $id = $array[$i]["num_pedimento"];
            $this->modoEdicion($id,'C',$user);
        }
    }
  
    function partida_select($presupuesto) {
    $sql = 'SELECT * FROM `c_presupuestos` WHERE id="'.$presupuesto.'"';

    $sth = \db()->prepare($sql);
    $sth->execute();
    return $sth->fetch();
    }
  
  
    function presupuestoAsignado($presupuesto)
    {
    //extract($_post);
    $sql = 'SELECT 
            th_aduana.presupuesto, 
            c_presupuestos.monto 
            FROM `th_aduana` 
            LEFT JOIN c_presupuestos ON th_aduana.presupuesto = c_presupuestos.id
            WHERE th_aduana.presupuesto="'.$presupuesto.'"
            GROUP BY presupuesto';

    $sth = \db()->prepare($sql);
    $sth->execute();
    return $sth->fetchAll();
    }
    
    function importeTotalDeOrden($presupuesto)
    {
    //extract($_post);
      
    $sql = 'SELECT 
            presupuesto,
            SUM(td_aduana.costo*td_aduana.cantidad) AS importeTotalDePresupuesto 
            FROM `th_aduana`
            LEFT JOIN td_aduana on th_aduana.num_pedimento = td_aduana.num_orden
            WHERE presupuesto = "'.$presupuesto.'"   
            GROUP BY presupuesto';

    //echo var_dump($sql);
    //die();
      
    $sth = \db()->prepare($sql);
    $sth->execute();
    return $sth->fetchAll();
    }
  
    function getTotalPedido($_post){

			extract($_post);

			if ($fechainicio && $fechafin) $split=' and (th_aduana.fech_pedimento>="'.$fechainicio.'" and th_aduana.fech_pedimento<="'.$fechafin.'") ';

			if ($almacen!=""){
			$split=" and th_aduana.Cve_Almac='$almacen' ";
			}
      
      $pres="";
      
      if ($presupuesto!=""){
			$pres=" and th_aduana.presupuesto='$presupuesto' ";
			}

			    $sql = "select th_aduana.num_pedimento as numero_oc,
                  'OC' AS tipo,
                  th_aduana.fech_pedimento AS fecha_entrega,
                  sum(td_aduana.cantidad) AS total_pedido,
                  th_aduana.status as estado
                  from th_aduana
                  LEFT JOIN td_aduana ON td_aduana.num_orden = th_aduana.num_pedimento
                  where th_aduana.Activo='1'  $split $pres ";

				$sth = \db()->prepare( $sql );
				$sth->execute();
        //echo var_dump($sql);
        //die();
				return $sth->fetch();
        

	}
  
  function traer_almacenes($data) 
  {
      $id_user = $_SESSION['id_user'];

    //$sql = "SELECT * FROM c_almacenp";
    $sql = "SELECT * FROM c_almacenp
      LEFT JOIN t_usu_alm_pre ON c_almacenp.clave = t_usu_alm_pre.cve_almac 
      WHERE Activo=1 AND id_user = $id_user";
    $sth = \db()->prepare($sql);
    $sth->setFetchMode( \PDO::FETCH_CLASS, '\OrdenCompra\OrdenCompra' );
    $sth->execute();
    return $sth->fetchAll();
  }
  
   function traer_contenedores($data) 
  {
/*
    $sql = "SELECT * FROM c_charolas 
            WHERE (NOT EXISTS (SELECT NULL
            FROM td_entalmacenxtarima
            WHERE td_entalmacenxtarima.ClaveEtiqueta = c_charolas.clave_contenedor) OR Permanente = 0) AND clave_contenedor NOT LIKE 'LP0%'";
*/
/*
    $sql = "SELECT * FROM c_charolas 
            WHERE c_charolas.clave_contenedor NOT IN (SELECT Cve_Contenedor FROM V_ExistenciaGralProduccion) AND IFNULL(LEFT(c_charolas.CveLP, 3), 0) != 'LP0'";
*/
    $almacen_id = $data['almacen'];
    /*
    $sql = "
      SELECT DISTINCT
      #ch.*
      ch.IDContenedor, ch.cve_almac, ch.clave_contenedor, ch.descripcion, ch.Permanente, IFNULL(ch.Pedido, '') AS Pedido, IFNULL(ch.sufijo, '') AS sufijo, IF(UPPER(ch.tipo) = 'PALLET', 'PALLET', 'CONTE') AS tipo, ch.Activo, ch.alto, ch.ancho, ch.fondo, ch.peso, ch.pesomax, ch.capavol, IFNULL(ch.Costo, 0) AS Costo, IFNULL(ch.CveLP, '') AS CveLP, IFNULL(ch.TipoGen, 0) as TipoGen, 
      IF(ch.TipoGen = 1, 'GE', 'NG') as Generico
      FROM c_charolas ch
      LEFT JOIN c_almacenp ON c_almacenp.id = ch.cve_almac 
      WHERE 
      (
      (
        ch.Activo = 1 AND 
        ch.IDContenedor NOT IN (SELECT ntarima FROM t_tarima WHERE Abierta = 0) AND 
        ch.IDContenedor NOT IN (SELECT ntarima FROM ts_existenciatarima) AND 
        (ch.clave_contenedor NOT IN (SELECT ClaveEtiqueta FROM td_entalmacenxtarima) AND ch.CveLP NOT IN (SELECT ClaveEtiqueta FROM td_entalmacenxtarima))
      ) OR (ch.TipoGen = 1 AND ch.Activo = 1)
      ) 
      AND ch.clave_contenedor NOT LIKE 'LP%' AND ((IFNULL(ch.CveLP, '') = '' AND ch.TipoGen = 0) OR (ch.TipoGen = 1))
      AND c_almacenp.id = {$almacen_id}
      AND ch.clave_contenedor != IFNULL(ch.CveLP, '')
      GROUP BY ch.IDContenedor  
      ORDER BY ch.TipoGen DESC";
      */

      $sql = "
        SELECT DISTINCT
          #ch.*
          ch.IDContenedor, ch.cve_almac, ch.clave_contenedor, ch.descripcion, ch.Permanente, IFNULL(ch.Pedido, '') AS Pedido, IFNULL(ch.sufijo, '') AS sufijo, IF(UPPER(ch.tipo) = 'PALLET', 'PALLET', 'CONTE') AS tipo, ch.Activo, ch.alto, ch.ancho, ch.fondo, ch.peso, ch.pesomax, ch.capavol, IFNULL(ch.Costo, 0) AS Costo, IFNULL(ch.CveLP, '') AS CveLP, IFNULL(ch.TipoGen, 0) AS TipoGen, 
          IF(ch.TipoGen = 1, 'GE', 'NG') AS Generico
          FROM c_charolas ch
          LEFT JOIN c_almacenp ON c_almacenp.id = ch.cve_almac 
          WHERE 
          (
          (
            ch.Activo = 1 
            AND ch.IDContenedor NOT IN (SELECT ntarima FROM t_tarima WHERE Abierta = 0) 
            AND ch.IDContenedor NOT IN (SELECT ntarima FROM ts_existenciatarima) 
            AND (IFNULL(ch.clave_contenedor, '') NOT IN (SELECT ClaveEtiqueta FROM td_entalmacenxtarima) AND IFNULL(ch.CveLP, '') NOT IN (SELECT ClaveEtiqueta FROM td_entalmacenxtarima))
          ) AND ((ch.TipoGen = 1 AND ch.Activo = 1) OR (ch.TipoGen = 0 AND ch.Activo = 1)) #AND ch.clave_contenedor = IFNULL(ch.CveLP, '')
          ) 
          #/*AND ch.clave_contenedor NOT LIKE 'LP%' */AND ((IFNULL(ch.CveLP, '') = '' AND ch.TipoGen = 0) OR (ch.TipoGen = 1))
          AND c_almacenp.id = {$almacen_id}
          AND ch.tipo != 'Caja'
          #AND ch.clave_contenedor != IFNULL(ch.CveLP, '') and ch.TipoGen = 0
          GROUP BY ch.IDContenedor  
          ORDER BY ch.TipoGen DESC
          LIMIT 50 #Hay instancias que tienen mas de 50mil libres asi que lo limito a 300
        ";

    $sth = \db()->prepare($sql);
    $sth->setFetchMode( \PDO::FETCH_CLASS, '\OrdenCompra\OrdenCompra' );
    $sth->execute();
    return $sth->fetchAll();
  }
	
  function traer_lps($data) 
  {
    $almacen_id = $data['almacen'];
/*
    $sql = "
      SELECT DISTINCT
      ch.*
      FROM c_charolas ch
      LEFT JOIN c_almacenp ON c_almacenp.id = ch.cve_almac 
      WHERE 
      (
      (
        ch.Activo = 1 AND 
        ch.IDContenedor NOT IN (SELECT ntarima FROM t_tarima WHERE Abierta = 0) AND 
        ch.IDContenedor NOT IN (SELECT ntarima FROM ts_existenciatarima) AND 
        (ch.clave_contenedor NOT IN (SELECT ClaveEtiqueta FROM td_entalmacenxtarima) OR ch.CveLP NOT IN (SELECT ClaveEtiqueta FROM td_entalmacenxtarima))
      ) OR (ch.TipoGen = 1 AND ch.Activo = 1)
      ) 
      AND ch.clave_contenedor NOT LIKE 'LP%'
      AND c_almacenp.id = {$almacen_id}
      GROUP BY ch.IDContenedor  
      ORDER BY ch.TipoGen DESC";
*/

    $sql = "SELECT c_charolas.*
        FROM c_charolas
            LEFT JOIN c_almacenp ON (c_almacenp.clave = c_charolas.cve_almac OR c_almacenp.id = c_charolas.cve_almac)
            LEFT JOIN V_ExistenciaGralProduccion ep ON ep.Cve_Contenedor = c_charolas.clave_contenedor AND ep.tipo = 'ubicacion'
            LEFT JOIN c_ubicacion u ON u.idy_ubica = ep.cve_ubicacion
        WHERE 
          c_almacenp.id = c_charolas.cve_almac
          AND c_charolas.Activo = 1  AND c_charolas.tipo != 'Caja'
          AND (c_almacenp.id = '{$almacen_id}') AND IFNULL(ep.Cve_Contenedor, '') = ''
          AND c_charolas.clave_contenedor = IFNULL(c_charolas.CveLP, '')
          AND c_charolas.clave_contenedor NOT IN (SELECT Cve_Contenedor FROM V_ExistenciaGralProduccion WHERE Cve_Contenedor != '' AND Existencia > 0)
        GROUP BY c_charolas.IDContenedor
        LIMIT 50";

    $sth = \db()->prepare($sql);
    $sth->setFetchMode( \PDO::FETCH_CLASS, '\OrdenCompra\OrdenCompra' );
    $sth->execute();
    return $sth->fetchAll();
  }

	function traer_medidas($data) 
  {
    //$sql = "SELECT * FROM c_unimed";

    $cve_articulo = $data['cve_articulo'];
    
    $sql = "SELECT a.cve_articulo, a.unidadMedida, a.num_multiplo, u.cve_umed, u.des_umed, IFNULL(u.mav_cveunimed, u.cve_umed) as mav_cveunimed, u.id_umed, u.Activo
            FROM c_articulo a
            LEFT JOIN c_unimed u ON u.id_umed = IFNULL(a.unidadMedida, '')
            WHERE a.cve_articulo = '$cve_articulo' AND u.Activo = 1

            UNION

            SELECT a.cve_articulo, a.empq_cveumed AS unidadMedida,a.num_multiplo, u.cve_umed, u.des_umed, IFNULL(u.mav_cveunimed, u.cve_umed) as mav_cveunimed, u.id_umed, u.Activo
            FROM c_articulo a
            LEFT JOIN c_unimed u ON u.id_umed = IFNULL(a.empq_cveumed, '')
            WHERE a.cve_articulo = '$cve_articulo' AND u.Activo = 1

            UNION

            SELECT a.cve_articulo, a.empq_cveumed AS unidadMedida,a.num_multiplo, u.cve_umed, u.des_umed, IFNULL(u.mav_cveunimed, u.cve_umed) as mav_cveunimed, u.id_umed, u.Activo
            FROM c_articulo a
            LEFT JOIN c_unimed u ON u.mav_cveunimed = 'XBX'
            WHERE a.cve_articulo = '$cve_articulo' AND u.Activo = 1 AND a.num_multiplo > 1";

    $sth = \db()->prepare($sql);
    $sth->setFetchMode( \PDO::FETCH_CLASS, '\OrdenCompra\OrdenCompra' );
    $sth->execute();
    return $sth->fetchAll();
  }
  
   function traer_zonas($data) 
  {
    $sql = "SELECT
            tubicacionesretencion.*
			      ,c_almacenp.nombre
            ,c_almacenp.clave
            from 
            tubicacionesretencion,
            c_almacenp
            WHERE
            tubicacionesretencion.cve_almacp= c_almacenp.id 
            AND tubicacionesretencion.Activo = 1 
            and c_almacenp.clave='{$data["almacen"]}';";
    $sth = \db()->prepare($sql);
    //$sth->setFetchMode( \PDO::FETCH_CLASS, '\OrdenCompra\OrdenCompra' );
    $sth->execute();
    $res = $sth->fetchAll();
    return $res;
  }


  function traer_crossdocking($data) 
  {
    $oc = $data['oc'];
    $id_almacen = $data['almacen'];
    $sql = "SELECT th.* FROM th_pedido th WHERE th.status = 'A' AND th.Fol_folio LIKE 'XD%' AND IFNULL(th.Ship_Num, '') = '' AND cve_almac = $id_almacen AND (SELECT COUNT(*) FROM td_aduana WHERE num_orden = $oc) = (SELECT COUNT(*) FROM td_pedido WHERE Fol_folio = th.Fol_folio AND CONCAT(cve_articulo, IFNULL(cve_lote, '')) IN (SELECT CONCAT(cve_articulo, IFNULL(cve_lote, '')) FROM td_aduana WHERE num_orden = $oc))";
    $sth = \db()->prepare($sql);
    //$sth->setFetchMode( \PDO::FETCH_CLASS, '\OrdenCompra\OrdenCompra' );
    $sth->execute();
    $pedidoscross = $sth->fetchAll();

    $arr_pedidos = array();
    //$sql_track = "";
    foreach($pedidoscross as $pcross)
    {
        $folio = $pcross['Fol_folio'];
        $sql = "SELECT Cve_articulo, IFNULL(cve_lote, '') as cve_lote, Num_cantidad FROM td_pedido where Fol_folio = '$folio' AND CONCAT(Cve_articulo, IFNULL(cve_lote, '')) IN (SELECT CONCAT(cve_articulo, IFNULL(cve_lote, '')) FROM td_aduana WHERE num_orden = $oc)";
        $sth = \db()->prepare($sql);
        //$sth->setFetchMode( \PDO::FETCH_CLASS, '\OrdenCompra\OrdenCompra' );
        $sth->execute();
        $pcross_res = $sth->fetchAll();

        $ok = true;
        foreach($pcross_res as $row)
        {
            $cve_articulo_p = $row['Cve_articulo'];
            $cve_lote_p = $row['cve_lote'];
            $Num_cantidad = $row['Num_cantidad'];
            $sql_oc = "SELECT COUNT(*) as ok FROM td_aduana where num_orden = $oc AND cve_articulo = '$cve_articulo_p' AND cve_lote = '$cve_lote_p' AND cantidad >= $Num_cantidad";
            $sth_oc = \db()->prepare($sql_oc);
            //$sth_oc->setFetchMode( \PDO::FETCH_CLASS, '\OrdenCompra\OrdenCompra' );
            $sth_oc->execute();
            $pcross_res = $sth_oc->fetchAll();
            foreach($pcross_res as $pc)
              if($pc['ok'] == 0) $ok = false;
            //$sql_track .= $sql_oc;
        }

        if($ok == true) $arr_pedidos[] = "'".$folio."'";
    }
    //$i = 0;
    $pedidoscross = 0;
    if(count($arr_pedidos) > 0)
    {
      $pedidosXD = implode(",", $arr_pedidos);
      $sql = "SELECT th.* FROM th_pedido th WHERE th.status = 'A' AND th.Fol_folio LIKE 'XD%' AND IFNULL(th.Ship_Num, '') = '' AND cve_almac = $id_almacen AND th.Fol_folio IN ({$pedidosXD})";
      $sth = \db()->prepare($sql);
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\OrdenCompra\OrdenCompra' );
      $sth->execute();
      $pedidoscross = $sth->fetchAll();
    }

    return $pedidoscross;
    //return $arr_pedidos;
  }


  function traer_ordenes($data) 
  {
    $almacen = $data['almacen'];
    $sql = "SELECT DISTINCT th_aduana.*,
              pr.ID_Proveedor AS ID_Proveedor_entrada,
              IF(t_protocolo.descripcion LIKE '%Intern%', 1, 0) AS protocolo,
              c_proveedores.cve_proveedor AS cve_proveedor,
              c_proveedores.nombre AS nombre_proveedor, 
              aduana.cve_proveedor as cve_proveedor_procedimiento,
              IF(IFNULL(th_aduana.procedimiento, '') != '', aduana.nombre, '') AS nombre_proveedor_procedimiento
            FROM `th_aduana`  
            #LEFT JOIN c_proveedores ON c_proveedores.cve_proveedor = th_aduana.procedimiento #Empresa_Proveedor
            #LEFT JOIN c_proveedores aduana ON th_aduana.ID_Proveedor = aduana.ID_Proveedor #Proveedor
            LEFT JOIN c_proveedores pr ON pr.cve_proveedor = th_aduana.procedimiento AND th_aduana.procedimiento != ''
            LEFT JOIN c_proveedores ON c_proveedores.ID_Proveedor = th_aduana.ID_Proveedor
            LEFT JOIN c_proveedores aduana ON th_aduana.procedimiento = aduana.cve_proveedor 
            LEFT JOIN t_protocolo ON t_protocolo.ID_Protocolo = th_aduana.ID_Protocolo
            WHERE (th_aduana.status = 'C' OR th_aduana.status = 'I') AND th_aduana.Cve_Almac = '$almacen'
            ORDER BY th_aduana.num_pedimento DESC
            ";

    $sth = \db()->prepare($sql);
    $sth->setFetchMode( \PDO::FETCH_CLASS, '\OrdenCompra\OrdenCompra' );
    $sth->execute();
    $ordenes = $sth->fetchAll();
    $i = 0;

$confSql = \db()->prepare("SELECT IFNULL(Valor, '') AS Valor FROM t_configuraciongeneral WHERE cve_conf = 'instancia' LIMIT 1 ");
$confSql->execute();
$instancia = $confSql->fetch()['Valor'];

  if($instancia != 'dicoisa')/*ESTO MIENTRAS RESUELVO LO DE QUE AL SELECCIONAR UNA ORDEN QUE TRAIGA LUEGO LOS PRODUCTOS*/
    foreach($ordenes as $ord)
    {
      $id_td = $ord->num_pedimento;

      //$sql = "SELECT td_aduana.*, 
      //          IF(IFNULL(td_aduana.cve_lote, '') = '', td_aduana.cve_articulo, CONCAT(td_aduana.cve_articulo, ';;;;;', td_aduana.cantidad,';;;;;',IF(c_articulo.Caduca = 'S', CONCAT(td_aduana.cve_lote, ';;;;;', l.Caducidad), td_aduana.cve_lote))) as val_articulo,
              //  IFNULL(td_aduana.cve_lote, '') as val_lote,
              //  IF(IFNULL(td_aduana.cve_lote, '') = '', c_articulo.des_articulo, CONCAT(c_articulo.des_articulo,' | ',td_aduana.cve_lote)) as nombre_articulo_select,
              //  c_articulo.des_articulo AS nombre_articulo,
              //  c_articulo.tipo_producto
              //FROM `td_aduana`  
              //LEFT JOIN c_articulo ON c_articulo.cve_articulo = td_aduana.cve_articulo
              //LEFT JOIN c_lotes l ON l.Lote = td_aduana.cve_lote AND c_articulo.cve_articulo = l.cve_articulo
              //WHERE num_orden = '{$id_td}'
              //and c_articulo.cve_articulo not in (select cve_articulo from td_entalmacen where td_entalmacen.num_orden = td_aduana.num_orden)
              //";

              #AND IFNULL(cve_lote, '') = IFNULL(a.cve_lote, '')
      $sql = "SELECT DISTINCT a.cve_articulo, 
      IFNULL(axt.Cantidad,(a.cantidad-IFNULL((SELECT SUM(CantidadRecibida) FROM td_entalmacen WHERE num_orden = th.id_ocompra AND cve_articulo = a.cve_articulo ),0))) AS cantidad, 
      a.cve_lote, a.caducidad, a.temperatura, a.num_orden, a.costo, a.IVA, 
      IFNULL(IF(IFNULL(a.cve_lote, '') = '', IF(IFNULL(axt.ClaveEtiqueta, '') = '', a.cve_articulo, CONCAT(a.cve_articulo, ':::::', axt.ClaveEtiqueta)), CONCAT(a.cve_articulo, ';;;;;', (IFNULL(axt.Cantidad, a.cantidad)-IF(axt.Cantidad IS NULL, (SELECT SUM(CantidadRecibida) FROM td_entalmacen WHERE num_orden = '{$id_td}' AND cve_articulo = a.cve_articulo AND IFNULL(cve_lote, '') = IFNULL(a.cve_lote, '')), 0)),';;;;;',IF(c_articulo.Caduca = 'S', CONCAT(a.cve_lote, ';;;;;', l.Caducidad), a.cve_lote),  IF(IFNULL(axt.ClaveEtiqueta, '') = '', '', CONCAT(':::::', axt.ClaveEtiqueta)  ))), a.cve_articulo) AS val_articulo,
      IFNULL(a.cve_lote, '') AS val_lote,
                IFNULL(axt.ClaveEtiqueta, '') AS val_lp,
                IF(IFNULL(a.cve_lote, '') = '', IF(IFNULL(axt.ClaveEtiqueta, '') = '', c_articulo.des_articulo, CONCAT(c_articulo.des_articulo, ' | ', axt.ClaveEtiqueta)), CONCAT(c_articulo.des_articulo,' | ',a.cve_lote, IF(IFNULL(axt.ClaveEtiqueta, '') = '', '', CONCAT(' | ', axt.ClaveEtiqueta)))) AS nombre_articulo_select,
                um.mav_cveunimed as cve_unidad_medida,
                c_articulo.num_multiplo,
                c_articulo.des_articulo AS nombre_articulo,
                c_articulo.tipo_producto,
                IF((IFNULL(c.largo, 0)*IFNULL(c.alto, 0)*IFNULL(c.ancho, 0)   ) = 0, 'S', 'N') AS caja_generica  # *IFNULL(c.peso, 0)
              FROM td_aduana a
              LEFT JOIN th_entalmacen th ON th.id_ocompra = a.num_orden
              LEFT JOIN td_aduanaxtarima axt ON axt.Num_Orden = a.num_orden AND axt.Cve_Articulo = a.cve_articulo AND axt.Cve_Lote = a.cve_lote
              LEFT JOIN c_articulo ON c_articulo.cve_articulo = a.cve_articulo
              LEFT JOIN c_lotes l ON l.Lote = a.cve_lote AND c_articulo.cve_articulo = l.cve_articulo
              LEFT JOIN td_entalmacen e ON e.cve_articulo = a.cve_articulo AND th.id_ocompra = a.num_orden #AND IFNULL(e.cve_lote, '') = IFNULL(a.cve_lote, '') 
              LEFT JOIN c_unimed um ON um.id_umed = c_articulo.unidadMedida
              LEFT JOIN c_tipocaja c ON c.clave = CONCAT('CO', c_articulo.cve_articulo)
              WHERE a.num_orden = '{$id_td}'
              AND c_articulo.cve_articulo NOT IN (SELECT cve_articulo FROM td_entalmacen WHERE td_entalmacen.fol_folio = th.Fol_Folio AND e.CantidadPedida = e.CantidadRecibida)";

      $sth = \db()->prepare($sql);
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\OrdenCompra\OrdenCompra' );
      $sth->execute();
      $articulos = $sth->fetchAll();
      $ord->articulos = $articulos;
      $i++;
    }
    
    return $ordenes;
  }
  

  function traer_ordenesRealizadas($data) 
  {
    $id_prov = $data['id_prov'];
    $sql = "SELECT th_aduana.*,
              c_proveedores.Nombre AS nombre_proveedor
            FROM `th_aduana`  
            LEFT JOIN c_proveedores ON c_proveedores.ID_Proveedor = th_aduana.ID_Proveedor
            WHERE th_aduana.status = 'T' AND th_aduana.ID_Proveedor = $id_prov
            AND th_aduana.num_pedimento IN (SELECT DISTINCT th.num_pedimento
            FROM c_articulo a
            LEFT JOIN ts_existenciapiezas ts ON a.cve_articulo = ts.cve_articulo
            LEFT JOIN c_ubicacion cu ON cu.idy_ubica = ts.idy_ubica
            LEFT JOIN c_almacenp ca ON ca.id = ts.cve_almac
            LEFT JOIN td_aduana td ON td.cve_articulo = a.cve_articulo
            LEFT JOIN th_aduana th ON th.num_pedimento = td.num_orden
            INNER JOIN td_ajusteexist tex ON tex.cve_articulo = ts.cve_articulo
            WHERE th.ID_Proveedor = $id_prov)
            ORDER BY th_aduana.num_pedimento DESC";
    $sth = \db()->prepare($sql);
    $sth->setFetchMode( \PDO::FETCH_CLASS, '\OrdenCompra\OrdenCompra' );
    $sth->execute();
    $ordenes = $sth->fetchAll();
    /*
    $i = 0;
    foreach($ordenes as $ord)
    {
      $id_td = $ord->num_pedimento;
      $sql = "SELECT td_aduana.*, 
                c_articulo.des_articulo AS nombre_articulo,
                c_articulo.tipo_producto
              FROM `td_aduana`  
              LEFT JOIN c_articulo ON c_articulo.cve_articulo = td_aduana.cve_articulo
              WHERE num_orden = '{$id_td}'
              and c_articulo.cve_articulo not in (select cve_articulo from td_entalmacen where td_entalmacen.num_orden = td_aduana.num_orden)
              ";
      $sth = \db()->prepare($sql);
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\OrdenCompra\OrdenCompra' );
      $sth->execute();
      $articulos = $sth->fetchAll();
      $ord->articulos = $articulos;
      $i++;
    }
    */
    return $ordenes;
  }

  function traer_facturas($data) 
  {
    $num_orden = $data['num_orden'];
    $sql = "SELECT th_aduana.*, p.Nombre FROM th_aduana 
            LEFT JOIN c_proveedores p ON p.ID_Proveedor = th_aduana.ID_Proveedor
            WHERE STATUS = 'T' AND factura != '' AND num_pedimento = $num_orden";
    $sth = \db()->prepare($sql);
    $sth->setFetchMode( \PDO::FETCH_CLASS, '\OrdenCompra\OrdenCompra' );
    $sth->execute();
    return $sth->fetchAll();
  }

  function traer_proveedores($data) 
  {
    $almacen = $data['almacen'];
    $sql = "SELECT DISTINCT p.ID_Proveedor, p.Nombre FROM c_proveedores p
            #LEFT JOIN th_aduana th ON th.ID_Proveedor = p.ID_Proveedor
            LEFT JOIN c_almacenp c ON c.clave = '$almacen' #th.Cve_Almac
            WHERE c.clave = '$almacen' AND p.Activo = 1";
    $sth = \db()->prepare($sql);
    $sth->setFetchMode( \PDO::FETCH_CLASS, '\OrdenCompra\OrdenCompra' );
    $sth->execute();
    return $sth->fetchAll();
  }

  function traer_todos_los_articulos_proveedores($data) 
  {
    $id_prov  = $data['id_prov'];
    $id_orden = $data['id_orden'];

    $and_orden = "";

    if($id_orden) $and_orden = "AND td.num_orden = $id_orden";

    $sql = "SELECT DISTINCT a.*
            FROM c_articulo a
              LEFT JOIN ts_existenciapiezas ts ON a.cve_articulo = ts.cve_articulo
              LEFT JOIN c_ubicacion cu ON cu.idy_ubica = ts.idy_ubica
              LEFT JOIN c_almacenp ca ON ca.id = ts.cve_almac
              LEFT JOIN td_aduana td ON td.cve_articulo = a.cve_articulo
              LEFT JOIN th_aduana th ON th.num_pedimento = td.num_orden
              INNER JOIN td_ajusteexist tex ON tex.cve_articulo = ts.cve_articulo
            WHERE th.ID_Proveedor = $id_prov $and_orden AND a.Activo = 1";
    $sth = \db()->prepare($sql);
    $sth->setFetchMode( \PDO::FETCH_CLASS, '\OrdenCompra\OrdenCompra' );
    $sth->execute();
    return $sth->fetchAll();
  }

  function traer_todos_los_articulos($data) 
  {
    $articulo_search = ""; $sql_search = ""; $id_almacen = $data["almacen"];
    if(isset($data['articulo_search']))
    {
        $articulo_search = $data['articulo_search'];
        $sql_search = " AND (a.cve_articulo LIKE '%$articulo_search%' OR a.des_articulo LIKE '%$articulo_search%') ";
    }
    $sql = "SELECT a.*, u.mav_cveunimed as cve_unidad_medida, IF((IFNULL(c.largo, 0)*IFNULL(c.alto, 0)*IFNULL(c.ancho, 0)/* *IFNULL(c.peso, 0)*/) = 0, 'S', 'N') AS caja_generica FROM c_articulo a LEFT JOIN c_unimed u ON u.id_umed = a.unidadMedida LEFT JOIN c_tipocaja c ON c.clave = CONCAT('CO', a.cve_articulo) LEFT JOIN Rel_Articulo_Almacen ra ON ra.Cve_Articulo = a.cve_articulo WHERE a.Activo = 1 AND ra.Cve_Almac = {$id_almacen} {$sql_search}";
    $sth = \db()->prepare($sql);
    $sth->setFetchMode( \PDO::FETCH_CLASS, '\OrdenCompra\OrdenCompra' );
    $sth->execute();
    return $sth->fetchAll();
  }
  
  function articulos_con_lotes($data) 
  {
    $sql = "SELECT * FROM c_articulo WHERE control_lotes = 'S'";
    $sth = \db()->prepare($sql);
    $sth->setFetchMode( \PDO::FETCH_CLASS, '\OrdenCompra\OrdenCompra' );
    $sth->execute();
    return $sth->fetchAll();
  }
  
  function articulos_con_series($data) 
  {
    $sql = "SELECT * FROM c_articulo WHERE control_numero_series = 'S'";
    $sth = \db()->prepare($sql);
    $sth->setFetchMode( \PDO::FETCH_CLASS, '\OrdenCompra\OrdenCompra' );
    $sth->execute();
    return $sth->fetchAll();
  }
  
  function articulos_con_peso($data) 
  {
    $sql = "SELECT * FROM c_articulo WHERE control_peso = 'S'";
    $sth = \db()->prepare($sql);
    $sth->setFetchMode( \PDO::FETCH_CLASS, '\OrdenCompra\OrdenCompra' );
    $sth->execute();
    return $sth->fetchAll();
  }
    
  function traer_lotes($data) 
  {
    $sql = "SELECT * FROM c_lotes";
    $sth = \db()->prepare($sql);
    $sth->setFetchMode( \PDO::FETCH_CLASS, '\OrdenCompra\OrdenCompra' );
    $sth->execute();
    return $sth->fetchAll();
  }
  
  function fecha_actual()
  {
    $sql = "SELECT DATE_FORMAT(CURDATE(), '%d-%m-%Y') fecha_actual FROM DUAL";
    $sth = \db()->prepare($sql);
    $sth->execute();
    $fecha_actual = $sth->fetchAll() [0]["fecha_actual"]; 
    return $fecha_actual;
  }

  function hora_actual($data)
  {
    $sql = "SELECT date_format(CURRENT_TIMESTAMP, '%d-%m-%Y %H:%i:%s') hora_actual";
    $sth = \db()->prepare($sql);
    $sth->setFetchMode( \PDO::FETCH_CLASS, '\OrdenCompra\OrdenCompra' );
    $sth->execute();
    return $sth->fetchAll();
  }
  
  function traer_folio_R($data)
  {
    $sql = "SELECT IFNULL(MAX(Fol_Folio), 0)+1 AS consecutivo
            FROM th_entalmacen";
    $sth = \db()->prepare($sql);
    $sth->execute();
    $consecutivo = $sth->fetchAll() [0]["consecutivo"]; 
    return $consecutivo;
  }

  function traer_folio_Dev_Proveedores($data)
  {
    $sql = "SELECT CONCAT('DP', IFNULL(MAX(folio_dev), 0)+1) AS consecutivo
            FROM c_devproveedores";
    $sth = \db()->prepare($sql);
    $sth->execute();
    $consecutivo = $sth->fetchAll() [0]["consecutivo"]; 
    return $consecutivo;
  }

  function guardar_entrada($data) 
  {
    $sql = "SELECT date_format(CURRENT_TIMESTAMP, '%d-%m-%Y %H:%i:%s') hora_actual";
    $sth = \db()->prepare($sql);
    $sth->setFetchMode( \PDO::FETCH_CLASS, '\OrdenCompra\OrdenCompra' );
    $sth->execute();
    return $sth->fetchAll();
  }
  
  function activos_fijos($data)
  {
    foreach($data["activos"] as $da)
    {
      $sql ="insert into t_activo_fijo(clave_activo, id_articulo, id_orden_compra, id_pedido, id_serie, nombre_empleado, clave_empleado, rfc_empleado, fecha_entrada)
             values('".$da['cve_articulo']."','','".$da['ID_Aduana']."','','','','','',now());";
      $sth = \db()->prepare($sql);
      $sth->execute();
      return $sth->fetchAll();
    }
  }
  
}
