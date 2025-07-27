<?php

namespace Destinatarios;

class Destinatarios {

    const TABLE = 'c_destinatarios';

    var $identifier;

    public function __construct( $Cve_Dest = false, $key = false ) {

        if( $Cve_Dest ) {
            $this->Cve_Dest = (int) $Cve_Dest;
        }

        if($key) {

            $sql = sprintf('
          SELECT
            id_destinatario
          FROM
            %s
          WHERE
            id_destinatario = ?
        ',
                self::TABLE
            );

            $sth = \db()->prepare($sql);
            $sth->setFetchMode(\PDO::FETCH_CLASS, '\Destinatarios\Destinatarios');
            $sth->execute(array($key));

            $Cve_Dest = $sth->fetch();

            $this->id_destinatario = $Cve_Dest->id_destinatario;

        }

    }

    private function load() 
    {
      $sql = sprintf('SELECT * FROM c_destinatarios WHERE id_destinatario = ? ');
      $sth = \db()->prepare($sql);
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\Destinatarios\Destinatarios' );
      $sth->execute( array( $this->id ) );

      $this->data = $sth->fetch();
    }

    function __get( $key ) 
    {
      switch($key) {
        case 'id':
          $this->load();
        return @$this->data->$key;
        default:
          return $this->key;
      }
    }

    function save( $data ) 
    {
      try {
            $sql = sprintf('
              INSERT INTO
              ' . self::TABLE . '
              SET
              razonsocial = :RazonSocial,
              direccion = :Direccion,
              colonia = :Colonia,
              postal = :CodigoPostal,
              ciudad = :Ciudad,
              estado = :Estado,
              telefono = :Telefono,
              contacto = :Contacto,
              email_destinatario = :Email,
              latitud = :Latitud,
              longitud = :Longitud,
              Cve_Clte = :Cve_Clte,
              clave_destinatario = :ClaveDeDestinatario
            ');

            $this->save = \db()->prepare($sql);
            $this->save->bindValue( ':RazonSocial', $data['RazonSocial'], \PDO::PARAM_STR );
            $this->save->bindValue( ':Direccion', $data['Direccion'], \PDO::PARAM_STR );
            $this->save->bindValue( ':Colonia', $data['Colonia'], \PDO::PARAM_STR );
            $this->save->bindValue( ':CodigoPostal', $data['CodigoPostal'], \PDO::PARAM_STR );
            $this->save->bindValue( ':Ciudad', $data['Ciudad'], \PDO::PARAM_STR );
            $this->save->bindValue( ':Estado', $data['Estado'], \PDO::PARAM_STR );
            $this->save->bindValue( ':Telefono', $data['Telefono'], \PDO::PARAM_STR );
            $this->save->bindValue( ':Contacto', $data['Contacto'], \PDO::PARAM_STR );

            $this->save->bindValue( ':Email', $data['Email'], \PDO::PARAM_STR );
            $this->save->bindValue( ':Latitud', $data['Latitud'], \PDO::PARAM_STR );
            $this->save->bindValue( ':Longitud', $data['Longitud'], \PDO::PARAM_STR );

            $this->save->bindValue( ':Cve_Clte', $data['Cve_Clte'], \PDO::PARAM_STR );
            $this->save->bindValue( ':ClaveDeDestinatario', $data['ClaveDeDestinatario'], \PDO::PARAM_STR );
            $this->save->execute();

        $sql3 = "INSERT IGNORE INTO c_dane
                  SET
              cod_municipio = '{$data['CodigoPostal']}'
            , departamento = '{$data['Ciudad']}'
            , des_municipio = '{$data['Estado']}'";

        $rs = mysqli_query(\db2(), $sql3) or die(mysqli_error(\db2()));


      } 
      catch(PDOException $e) 
      {
        return 'ERROR: ' . $e->getMessage();
      }
    }
	

    function save_destinatario_cliente( $data ) 
    {
      try {
        $sql = sprintf('
          INSERT IGNORE INTO
          ' . self::TABLE . '
          SET
          razonsocial = :RazonSocial,
          direccion = :Direccion,
          colonia = :Colonia,
          postal = :CodigoPostal,
          ciudad = :Ciudad,
          estado = :Estado,
          telefono = :Telefono,
          contacto = :Contacto,
          email_destinatario = :email_cliente,
          latitud = :txtLatitud,
          longitud = :txtLongitud,
          Cve_Clte = :Cve_Clte,
          dir_principal = :dir_principal
        ');
        //clave_destinatario = :ClaveDeDestinatario

        $this->save = \db()->prepare($sql);
        $this->save->bindValue( ':RazonSocial', $data['RazonSocial'], \PDO::PARAM_STR );
        $this->save->bindValue( ':Direccion', $data['CalleNumero'], \PDO::PARAM_STR );
        $this->save->bindValue( ':Colonia', $data['Colonia'], \PDO::PARAM_STR );
        $this->save->bindValue( ':CodigoPostal', $data['CodigoPostal'], \PDO::PARAM_STR );
        $this->save->bindValue( ':Ciudad', $data['Ciudad'], \PDO::PARAM_STR );
        $this->save->bindValue( ':Estado', $data['Estado'], \PDO::PARAM_STR );
        $this->save->bindValue( ':Telefono', $data['Telefono1'], \PDO::PARAM_STR );
        $this->save->bindValue( ':Contacto', $data['Contacto'], \PDO::PARAM_STR );

        $this->save->bindValue( ':email_cliente', $data['email_cliente'], \PDO::PARAM_STR );
        $this->save->bindValue( ':txtLatitud', $data['txtLatitud'], \PDO::PARAM_STR );
        $this->save->bindValue( ':txtLongitud', $data['txtLongitud'], \PDO::PARAM_STR );

        $this->save->bindValue( ':Cve_Clte', $data['Cve_Clte'], \PDO::PARAM_STR );
        $this->save->bindValue( ':dir_principal', $data['usar_direccion'], \PDO::PARAM_STR );
        //$this->save->bindValue( ':ClaveDeDestinatario', $data['ClaveDeDestinatario'], \PDO::PARAM_STR );

        $this->save->execute();

      } 
      catch(PDOException $e) 
      {
        return 'ERROR: ' . $e->getMessage();
      }
    }

    function ConsultarDireccionPrincipal($data) 
    {
        $Cve_Clte = $data["Cve_Clte"];
        $sql = "
          SELECT COUNT(*) tiene_principal FROM c_destinatarios WHERE Cve_Clte = '{$Cve_Clte}' AND dir_principal = 1;
        ";
        $sth = \db()->prepare( $sql );
        $sth->execute();
        $row = $sth->fetch();
        return $row['tiene_principal'];
    } 

    function BorrarDireccionPrincipal($data) 
    {
        $Cve_Clte = $data["Cve_Clte"];
        $sql = "
          DELETE FROM c_destinatarios WHERE Cve_Clte = '{$Cve_Clte}' AND dir_principal = 1;
        ";
        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\Destinatarios\Destinatarios' );
        $sth->execute();
    } 

    function getAll() {
        $sql = '
        SELECT
          *
        FROM
          ' . self::TABLE . '
		  WHERE Activo = 1
      ';

        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\Destinatarios\Destinatarios' );
        $sth->execute( array( Cve_Dest ) );

        return $sth->fetchAll();

    }	
	
	function traerRutas($id_cliente) {
        $sql = '
        SELECT
          r.*,
					c_dane.des_municipio
        FROM
          t_clientexruta cr, t_ruta r, c_destinatarios, c_dane
		WHERE cr.clave_cliente = "'.$id_cliente.'"
		and cr.clave_ruta = r.ID_Ruta 
		and cr.clave_cliente= c_destinatarios.id_cliente
		and c_dane.cod_municipio=c_destinatarios.CodigoPostal
		and r.activo= 1;
      ';

        $sth = \db()->prepare( $sql );
        
        $sth->execute();

        return $sth->fetchAll();

    }	
  
    function asignarRutaACliente($data) 
    {
//       echo var_dump($data);
//       die();
      $destinatarios = $data["destinatarios"];
      $ruta = $data["ruta"];

      $rutas     = $data["rutas"];
      $agentes   = $data["agentes"];
      $dias      = $data["dias"];
      $sec_dest  = $data["sec_dest"];
      $sec_mayor = $data["sec_mayor"];
      $almacen   = $data["almacen"];

      foreach($destinatarios as $id_destinatario)
      {

        $sql = "SELECT COUNT(*) existe FROM t_clientexruta 
                WHERE clave_ruta = (SELECT ID_Ruta FROM t_ruta WHERE cve_ruta = '{$ruta}') AND 
                clave_cliente = '{$id_destinatario}'
        ";
        $sth = \db()->prepare( $sql );
        $sth->execute();
        $row = $sth->fetch();
        if($row['existe'] == 0)
        {
            $sql = "
              INSERT IGNORE INTO `t_clientexruta`
                (`clave_cliente`, `clave_ruta`)
              VALUES (
                ('{$id_destinatario}'),
                (SELECT ID_Ruta FROM t_ruta WHERE cve_ruta = '{$ruta}')
              )
            ";
            $sth = \db()->prepare( $sql );
            $sth->setFetchMode( \PDO::FETCH_CLASS, '\Destinatarios\Destinatarios' );
            $sth->execute();
        }

        $sql = "SELECT COUNT(*) existe FROM RelClirutas 
                WHERE IdRuta = (SELECT ID_Ruta FROM t_ruta WHERE cve_ruta = '{$ruta}') AND 
                IdCliente = '{$id_destinatario}' AND 
                IdEmpresa = '{$almacen}'
        ";
        $sql_relclirutas = $sql;
        $sth = \db()->prepare( $sql );
        $sth->execute();
        $row = $sth->fetch();
        if($row['existe'] == 0)
        {
            $sql = "
              INSERT IGNORE INTO `RelClirutas`
                (IdCliente, IdRuta, IdEmpresa, Fecha)
              VALUES (
                ('{$id_destinatario}'),
                (SELECT ID_Ruta FROM t_ruta WHERE cve_ruta = '{$ruta}'), 
                '{$almacen}',
                CURDATE()
              )
            ";
            $sth = \db()->prepare( $sql );
            $sth->setFetchMode( \PDO::FETCH_CLASS, '\Destinatarios\Destinatarios' );
            $sth->execute();
        }
//**************************************************************************************************
//**************************************************************************************************
            if($dias)
            {
                  $sqlDias = "";
                  $dias2 = "";
                  if($dias != "''")
                  {
                    if($dias == "IFNULL(RelDayCli.Lu, 20000)") $dias2 = "Lu";
                    if($dias == "IFNULL(RelDayCli.Ma, 20000)") $dias2 = "Ma";
                    if($dias == "IFNULL(RelDayCli.Mi, 20000)") $dias2 = "Mi";
                    if($dias == "IFNULL(RelDayCli.Ju, 20000)") $dias2 = "Ju";
                    if($dias == "IFNULL(RelDayCli.Vi, 20000)") $dias2 = "Vi";
                    if($dias == "IFNULL(RelDayCli.Sa, 20000)") $dias2 = "Sa";
                    if($dias == "IFNULL(RelDayCli.Do, 20000)") $dias2 = "Do";

                    $sqlDias = " AND $dias2 IS NOT NULL ";
                  }




                  $sql = "SELECT COUNT(*) existe 
                          FROM RelDayCli 
                          WHERE Cve_Ruta = (SELECT ID_Ruta FROM t_ruta WHERE cve_ruta = '{$ruta}') AND 
                                Id_Destinatario = '{$id_destinatario}' AND Cve_Almac = '{$almacen}' {$sqlDias}";
                  $sth = \db()->prepare( $sql );
                  $sth->execute();
                  $row = $sth->fetch();
                  if($row['existe'] == 1)
                  {
                      $sql = "
                        UPDATE RelDayCli SET $dias2 = NULL WHERE Cve_Ruta = (SELECT ID_Ruta FROM t_ruta WHERE cve_ruta = '{$ruta}') AND 
                                Id_Destinatario = '{$id_destinatario}' AND Cve_Almac = '{$almacen}'
                      ";
                      $sth = \db()->prepare( $sql );
                      $sth->setFetchMode( \PDO::FETCH_CLASS, '\Destinatarios\Destinatarios' );
                      $sth->execute();
                  }
            }
//**************************************************************************************************
//**************************************************************************************************
      }

      if($rutas && $agentes && $dias && $sec_dest[0])
      {

          if($dias == "IFNULL(RelDayCli.Lu, 20000)") $dias = "Lu";
          if($dias == "IFNULL(RelDayCli.Ma, 20000)") $dias = "Ma";
          if($dias == "IFNULL(RelDayCli.Mi, 20000)") $dias = "Mi";
          if($dias == "IFNULL(RelDayCli.Ju, 20000)") $dias = "Ju";
          if($dias == "IFNULL(RelDayCli.Vi, 20000)") $dias = "Vi";
          if($dias == "IFNULL(RelDayCli.Sa, 20000)") $dias = "Sa";
          if($dias == "IFNULL(RelDayCli.Do, 20000)") $dias = "Do";

          if($dias != 'Lu' && $dias != 'Ma' && $dias != 'Mi' && $dias != 'Ju' && $dias != 'Vi' && $dias != 'Sa' && $dias != 'Do')
            $dias = "";
          $sec = $sec_mayor;
          foreach($sec_dest as $destinatario)
          {
            $sec++;

              $sql = "SELECT COUNT(*) existe 
                      FROM RelDayCli 
                      WHERE Cve_Ruta = (SELECT ID_Ruta FROM t_ruta WHERE cve_ruta = '{$rutas}') AND 
                            Id_Destinatario = '{$destinatario}' AND Cve_Almac = '{$almacen}'";
              $sth = \db()->prepare( $sql );
              $sth->execute();
              $row = $sth->fetch();

              if($row['existe'] == 0)
              {

                  $sql = "
                    INSERT INTO `RelDayCli`
                      (Cve_Ruta, Cve_Cliente, Id_Destinatario, Cve_Vendedor, Cve_Almac)
                    VALUES 
                      ((SELECT ID_Ruta FROM t_ruta WHERE cve_ruta = '{$rutas}'), (SELECT Cve_Clte FROM c_destinatarios WHERE id_destinatario = '{$destinatario}'), '{$destinatario}', 
                      (SELECT Id_Vendedor FROM t_vendedores WHERE Cve_Vendedor = '{$agentes}')
                      , '{$almacen}')
                  ";

                  if($dias != "")
                  $sql = "
                    INSERT INTO `RelDayCli`
                      (Cve_Ruta, Cve_Cliente, Id_Destinatario, Cve_Vendedor, $dias, Cve_Almac)
                    VALUES 
                      ((SELECT ID_Ruta FROM t_ruta WHERE cve_ruta = '{$rutas}'), (SELECT Cve_Clte FROM c_destinatarios WHERE id_destinatario = '{$destinatario}'), '{$destinatario}', 
                      (SELECT Id_Vendedor FROM t_vendedores WHERE Cve_Vendedor = '{$agentes}')
                      , {$sec}, '{$almacen}')
                  ";

                  $sth = \db()->prepare( $sql );
                  $sth->setFetchMode( \PDO::FETCH_CLASS, '\Destinatarios\Destinatarios' );
                  $sth->execute();
              }
              else //if($dias != "")
              {
                      $sql = "
                        UPDATE RelDayCli SET $dias = {$sec} WHERE Cve_Ruta = (SELECT ID_Ruta FROM t_ruta WHERE cve_ruta = '{$rutas}') AND 
                                Id_Destinatario = '{$destinatario}' AND Cve_Almac = '{$almacen}'
                      ";
                      $sth = \db()->prepare( $sql );
                      $sth->setFetchMode( \PDO::FETCH_CLASS, '\Destinatarios\Destinatarios' );
                      $sth->execute();
              }
          }
      }
      return $sql_relclirutas;
    }	

	function traerDestinatariosDeRuta ($id_ruta){
		
		      $sql = '
        SELECT
          c.*
        FROM
          t_clientexruta cr, c_destinatarios c
		  WHERE cr.clave_ruta = '.$id_ruta.'
		and cr.clave_cliente = c.id_cliente 
		and c.activo= 1;
      ';

        $sth = \db()->prepare( $sql );
        
        $sth->execute();

        return $sth->fetchAll();
	
	
	}
	
	
    function borrarDestinatario( $data ) {

        $id_destinatario = $data['destinatario'];
        $cve_ruta = $data['rutas'];
        $dias = $data['dias'];

        if(!$dias)
        {
            $sql = "DELETE FROM RelClirutas WHERE IdCliente = '{$id_destinatario}' AND IdRuta = (SELECT ID_Ruta FROM t_ruta WHERE cve_ruta = '{$cve_ruta}')";
            $sth = \db()->prepare($sql);
            $sth->execute();

            $sql = "DELETE FROM t_clientexruta WHERE clave_cliente = '{$id_destinatario}' AND clave_ruta = (SELECT ID_Ruta FROM t_ruta WHERE cve_ruta = '{$cve_ruta}')";
            $sth = \db()->prepare($sql);
            $sth->execute();

            $sql = "DELETE FROM RelDayCli WHERE Id_Destinatario = '{$id_destinatario}' AND Cve_Ruta = (SELECT ID_Ruta FROM t_ruta WHERE cve_ruta = '{$cve_ruta}')";
            $sth = \db()->prepare($sql);
            $sth->execute();
        }
        else
        {
            $dias2 = "";
            $secuencia = $data['secuencia'];
            if($dias == "IFNULL(RelDayCli.Lu, 20000)") $dias2 = "Lu";
            if($dias == "IFNULL(RelDayCli.Ma, 20000)") $dias2 = "Ma";
            if($dias == "IFNULL(RelDayCli.Mi, 20000)") $dias2 = "Mi";
            if($dias == "IFNULL(RelDayCli.Ju, 20000)") $dias2 = "Ju";
            if($dias == "IFNULL(RelDayCli.Vi, 20000)") $dias2 = "Vi";
            if($dias == "IFNULL(RelDayCli.Sa, 20000)") $dias2 = "Sa";
            if($dias == "IFNULL(RelDayCli.Do, 20000)") $dias2 = "Do";

            $sql = "UPDATE RelDayCli SET $dias2 = NULL WHERE Id_Destinatario = '{$id_destinatario}' AND Cve_Ruta = (SELECT ID_Ruta FROM t_ruta WHERE cve_ruta = '{$cve_ruta}')";
            $sth = \db()->prepare($sql);
            $sth->execute();

            $sql = "UPDATE RelDayCli SET $dias2 = ($dias2-1) WHERE Cve_Ruta = (SELECT ID_Ruta FROM t_ruta WHERE cve_ruta = '{$cve_ruta}') AND $dias2 > $secuencia";
            $sth = \db()->prepare($sql);
            $sth->execute();

        }

        $sql = '
        UPDATE c_destinatarios
        SET Activo = "1"
        WHERE
          id_destinatario = ?
      ';
        $this->save = \db()->prepare($sql);

        return $this->save->execute( array($data['destinatario']) );

    }


    function CambiarSecuenciaDestinatario( $data ) 
    {
        $cve_ruta            = $data['rutas'];
        $secuencia_actual    = $data['secuencia_actual'];
        $secuencia_a_cambiar = $data['secuencia_a_cambiar'];
        $dias                = $data['dias'];
        $id_destinatario     = $data['id_destinatario'];
        $fix                 = $data['fix'];

        $dias2 = "";

        if($dias == "IFNULL(RelDayCli.Lu, 20000)") $dias2 = "Lu";
        if($dias == "IFNULL(RelDayCli.Ma, 20000)") $dias2 = "Ma";
        if($dias == "IFNULL(RelDayCli.Mi, 20000)") $dias2 = "Mi";
        if($dias == "IFNULL(RelDayCli.Ju, 20000)") $dias2 = "Ju";
        if($dias == "IFNULL(RelDayCli.Vi, 20000)") $dias2 = "Vi";
        if($dias == "IFNULL(RelDayCli.Sa, 20000)") $dias2 = "Sa";
        if($dias == "IFNULL(RelDayCli.Do, 20000)") $dias2 = "Do";

        $sql = "";
        if($fix == 1)
        {
            //$sql = "SET SQL_SAFE_UPDATES = 0;";
            //$sth = \db()->prepare($sql);
            //$sth->execute();
            $sql = "SET @i := $secuencia_actual;";
            $sth = \db()->prepare($sql);
            $sth->execute();

            $sql = "UPDATE RelDayCli SET $dias2 = (@i:=@i+1) WHERE Cve_Ruta = (SELECT ID_Ruta FROM t_ruta WHERE cve_ruta = '{$cve_ruta}') AND $dias2 > $secuencia_actual AND $dias2 IS NOT NULL ORDER BY $dias2";
            $sth = \db()->prepare($sql);
            $sth->execute();
        }
        else
        {
          if($secuencia_actual < $secuencia_a_cambiar)
             $sql = "UPDATE RelDayCli SET $dias2 = ($dias2-1) WHERE Cve_Ruta = (SELECT ID_Ruta FROM t_ruta WHERE cve_ruta = '{$cve_ruta}') AND $dias2 > $secuencia_actual AND $dias2 <= $secuencia_a_cambiar AND $dias2 IS NOT NULL";

          if($secuencia_actual > $secuencia_a_cambiar)
             $sql = "UPDATE RelDayCli SET $dias2 = ($dias2+1) WHERE Cve_Ruta = (SELECT ID_Ruta FROM t_ruta WHERE cve_ruta = '{$cve_ruta}') AND $dias2 < $secuencia_actual AND $dias2 >= $secuencia_a_cambiar AND $dias2 IS NOT NULL";

          $sth = \db()->prepare($sql);
          $sth->execute();

          $sql = "UPDATE RelDayCli SET $dias2 = $secuencia_a_cambiar WHERE Id_Destinatario = '{$id_destinatario}' AND Cve_Ruta = (SELECT ID_Ruta FROM t_ruta WHERE cve_ruta = '{$cve_ruta}')";
          $sth = \db()->prepare($sql);
          $sth->execute();
        }

        $sql = '
        UPDATE c_destinatarios
        SET Activo = "1"
        WHERE
          id_destinatario = ?
      ';
        $this->save = \db()->prepare($sql);

        return $this->save->execute( array($data['destinatario']) );

    }


    function actualizarDestinatarios( $data ) 
    {
      try{
        $sql = '
          UPDATE
          ' . self::TABLE . '
          SET
          razonsocial = :RazonSocial,
          direccion = :Direccion,
          colonia = :Colonia,
          postal = :CodigoPostal,
          ciudad = :Ciudad,
          estado = :Estado,
          telefono = :Telefono,
          contacto = :Contacto,
          email_destinatario = :Email,
          latitud = :Latitud,
          longitud = :Longitud,
          Cve_Clte = :Cve_Clte,
          clave_destinatario = :ClaveDeDestinatario
          WHERE
          id_destinatario = :id;
        ';

        $this->save = \db()->prepare($sql);
        $this->save->bindValue( ':id', $data['id'], \PDO::PARAM_STR );
        $this->save->bindValue( ':RazonSocial', $data['RazonSocial'], \PDO::PARAM_STR );
        $this->save->bindValue( ':Direccion', $data['Direccion'], \PDO::PARAM_STR );
        $this->save->bindValue( ':Colonia', $data['Colonia'], \PDO::PARAM_STR );
        $this->save->bindValue( ':CodigoPostal', $data['CodigoPostal'], \PDO::PARAM_STR );
        $this->save->bindValue( ':Ciudad', $data['Ciudad'], \PDO::PARAM_STR );
        $this->save->bindValue( ':Estado', $data['Estado'], \PDO::PARAM_STR );
        $this->save->bindValue( ':Telefono', $data['Telefono'], \PDO::PARAM_STR );
        $this->save->bindValue( ':Contacto', $data['Contacto'], \PDO::PARAM_STR );

        $this->save->bindValue( ':Email', $data['Email'], \PDO::PARAM_STR );
        $this->save->bindValue( ':Latitud', $data['Latitud'], \PDO::PARAM_STR );
        $this->save->bindValue( ':Longitud', $data['Longitud'], \PDO::PARAM_STR );

        $this->save->bindValue( ':Cve_Clte', $data['Cve_Clte'], \PDO::PARAM_STR );
        $this->save->bindValue( ':ClaveDeDestinatario', $data['ClaveDeDestinatario'], \PDO::PARAM_STR );
        $this->save->execute();

        if($data["dir_principal"] == '1')
        {
            $sql = '
              UPDATE c_cliente
              SET
              latitud = :Latitud,
              longitud = :Longitud
              WHERE
              Cve_Clte = :Cve_Clte
            ';

            $this->save = \db()->prepare($sql);
            $this->save->bindValue( ':Latitud', $data['Latitud'], \PDO::PARAM_STR );
            $this->save->bindValue( ':Longitud', $data['Longitud'], \PDO::PARAM_STR );
            $this->save->bindValue( ':Cve_Clte', $data['Cve_Clte'], \PDO::PARAM_STR );
            $this->save->execute();
        }

      }
      catch(PDOException $e) 
      {
        return 'ERROR: ' . $e->getMessage();
      }
    }


    function actualizarDestinatarioPrincipal( $data ) 
    {
      try{
        $sql = '
          UPDATE
          ' . self::TABLE . '
          SET
          razonsocial = :RazonSocial,
          direccion = :CalleNumero,
          colonia = :Colonia,
          postal = :CodigoPostal,
          ciudad = :Ciudad,
          estado = :Estado,
          telefono = :Telefono1,
          contacto = :Contacto,
          email_destinatario = :email_destinatario,
          latitud = :txtLatitud,
          longitud = :txtLongitud
          WHERE
          Cve_Clte = :Cve_Clte 
          AND 
          dir_principal = 1
        ';

        $this->save = \db()->prepare($sql);
        $this->save->bindValue( ':RazonSocial', $data['RazonSocial'], \PDO::PARAM_STR );
        $this->save->bindValue( ':CalleNumero', $data['CalleNumero'], \PDO::PARAM_STR );
        $this->save->bindValue( ':Colonia', $data['Colonia'], \PDO::PARAM_STR );
        $this->save->bindValue( ':CodigoPostal', $data['CodigoPostal'], \PDO::PARAM_STR );
        $this->save->bindValue( ':Ciudad', $data['Ciudad'], \PDO::PARAM_STR );
        $this->save->bindValue( ':Estado', $data['Estado'], \PDO::PARAM_STR );
        $this->save->bindValue( ':Telefono1', $data['Telefono1'], \PDO::PARAM_STR );
        $this->save->bindValue( ':Contacto', $data['Contacto'], \PDO::PARAM_STR );

        $this->save->bindValue( ':email_destinatario', $data['email_destinatario'], \PDO::PARAM_STR );
        $this->save->bindValue( ':txtLatitud', $data['txtLatitud'], \PDO::PARAM_STR );
        $this->save->bindValue( ':txtLongitud', $data['txtLongitud'], \PDO::PARAM_STR );

        $this->save->bindValue( ':Cve_Clte', $data['Cve_Clte'], \PDO::PARAM_STR );
        $this->save->execute();

        return $sql;
      }
      catch(PDOException $e) 
      {
        return 'ERROR: ' . $e->getMessage();
      }
    }

    function asignarRutaDestinatario( $cliente, $idRuta ) {

        $sql = '
        UPDATE
          ' . self::TABLE . '
        SET            
            cve_ruta = ?
        WHERE
          Cve_Dest = ?
      ';
        $this->save = \db()->prepare($sql);
        $this->save->execute( array(
            $idRuta,
            $cliente
        ) );



    }

     function loadDestinatarioRuta($ID_Ruta) {

        $sql = '
        SELECT
          c.*
        FROM
          c_destinatarios c, t_clientexruta cr      
        WHERE
          cr.clave_ruta = ? and c.id_cliente = cr.clave_cliente          
      ';

        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\Destinatarios\Destinatarios' );
        $sth->execute( array( id_cliente ) );

        return $sth->fetchAll();

    }
	
	function recoveryDestinatario( $data ) {

          $sql = "UPDATE c_destinatarios SET Activo = 1 WHERE  id_cliente='".$data['id_cliente']."';";
          $this->delete = \db()->prepare($sql);
          $this->delete->execute( array(
              $data['id_cliente']
          ) );
    }
	
	function exist($Cve_Dest) {
      $sql = sprintf('
        SELECT
          *
        FROM
          c_destinatarios
        WHERE
          Cve_Dest = ?
      ',
        self::TABLE
      );

      $sth = \db()->prepare( $sql );
	  
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\Destinatarios\Destinatarios' );
      $sth->execute( array( $Cve_Dest ) );

      $this->data = $sth->fetch();
	  
	  if(!$this->data)
		  return false; 
				else 
					return true;
    }

	function getDestinatario() {

          $sql = '
        SELECT
          *
        FROM
          c_destinatarios
      ';

          $sth = \db()->prepare( $sql );
          $sth->setFetchMode( \PDO::FETCH_CLASS, '\Destinatarios\Destinatarios' );
          $sth->execute( array( id_cliente ) );

          return $sth->fetchAll();

      }
	  
	function getDestinatario2(){
		$sql= 'Select c.*, d.departamento, d.des_municipio, a.nombre as almacenp from c_destinatarios c  left join c_almacenp a on a.id=c.Cve_Almacenp
		left join c_dane d on  d.cod_municipio=c.CodigoPostal where c.Activo=1';
		
		  $sth = \db()->prepare( $sql );
          $sth->setFetchMode( \PDO::FETCH_CLASS, '\Destinatarios\Destinatarios' );
          $sth->execute( array( id_cliente ) );

          return $sth->fetchAll();
		
	}
	  
	  
	}
