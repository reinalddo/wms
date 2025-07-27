<?php

namespace Clientes;

class Clientes {

    const TABLE = 'c_cliente';
    var $identifier;

    public function __construct( $Cve_Clte = false, $key = false ) {

        if( $Cve_Clte ) {
            $this->Cve_Clte = (int) $Cve_Clte;
        }

        if($key) {

            $sql = sprintf('
          SELECT
            Cve_Clte
          FROM
            %s
          WHERE
            Cve_Clte = ?
        ',
                self::TABLE
            );

            $sth = \db()->prepare($sql);
            $sth->setFetchMode(\PDO::FETCH_CLASS, '\Articulos\Articulos');
            $sth->execute(array($key));

            $Cve_Clte = $sth->fetch();

            $this->Cve_Clte = $Cve_Clte->Cve_Clte;

        }

    }

    private function load() {

        $sql = sprintf('
        SELECT
          *
        FROM
          %s
        WHERE
          Cve_Clte = ?
      ',
            self::TABLE
        );

        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\Articulos\Articulos' );
        $sth->execute( array( $this->Cve_Clte ) );

        $this->data = $sth->fetch();

    }

    function __get( $key ) {

        switch($key) {
            case 'Cve_Clte':
                $this->load();
                return @$this->data->$key;
            default:
                return $this->key;
        }

    }

    function save( $data ) {
		try {
			$sql = sprintf('
			INSERT INTO
			  ' . self::TABLE . '
			SET
				Cve_Clte = :Cve_Clte,
				RazonSocial = :RazonSocial,
				RazonComercial = :RazonComercial,
				CalleNumero = :CalleNumero,
				Colonia = :Colonia,
				Ciudad = :Ciudad,
				Estado = :Estado,
				Pais = :Pais,
				CodigoPostal = :CodigoPostal,
				RFC = :RFC,
				Telefono1 = :Telefono1,
				Telefono2 = :Telefono2,
				Telefono3 = :Telefono3,
				ClienteTipo = :ClienteTipo,
				ClienteGrupo = :ClienteGrupo,
				ClienteFamilia = :ClienteFamilia,
				CondicionPago = :CondicionPago,
				MedioEmbarque = :MedioEmbarque,
				ViaEmbarque = :ViaEmbarque,
				CondicionEmbarque = :CondicionEmbarque,
				ZonaVenta = :ZonaVenta,
				cve_ruta = :cve_ruta,
				ID_Proveedor = :ID_Proveedor,
				Cve_CteProv = :Cve_CteProv
		  ');

			$this->save = \db()->prepare($sql);

			$this->save->bindValue( ':Cve_Clte', $data['ClaveCliente'], \PDO::PARAM_STR );
			$this->save->bindValue( ':RazonSocial', $data['RazonSocial'], \PDO::PARAM_STR );
			$this->save->bindValue( ':RazonComercial', $data['RazonComercial'], \PDO::PARAM_STR );
			$this->save->bindValue( ':CalleNumero', $data['CalleNumero'], \PDO::PARAM_STR );
			$this->save->bindValue( ':Colonia', $data['Colonia'], \PDO::PARAM_STR );
			$this->save->bindValue( ':Ciudad', $data['Ciudad'], \PDO::PARAM_STR );
			$this->save->bindValue( ':Estado', $data['Estado'], \PDO::PARAM_STR );
			$this->save->bindValue( ':Pais', $data['Pais'], \PDO::PARAM_STR );
			$this->save->bindValue( ':CodigoPostal', $data['CodigoPostal'], \PDO::PARAM_STR );
			$this->save->bindValue( ':RFC', $data['RFC'], \PDO::PARAM_STR );
			$this->save->bindValue( ':Telefono1', $data['Telefono1'], \PDO::PARAM_STR );
			$this->save->bindValue( ':Telefono2', $data['Telefono2'], \PDO::PARAM_STR );
			$this->save->bindValue( ':Telefono3', $data['Telefono3'], \PDO::PARAM_STR );
			$this->save->bindValue( ':ClienteTipo', $data['ClienteTipo'], \PDO::PARAM_STR );
			$this->save->bindValue( ':ClienteGrupo', $data['ClienteGrupo'], \PDO::PARAM_STR );
			$this->save->bindValue( ':ClienteFamilia', $data['ClienteFamilia'], \PDO::PARAM_STR );
			$this->save->bindValue( ':CondicionPago', $data['CondicionPago'], \PDO::PARAM_STR );
			$this->save->bindValue( ':MedioEmbarque', $data['MedioEmbarque'], \PDO::PARAM_STR );
			$this->save->bindValue( ':ViaEmbarque', $data['ViaEmbarque'], \PDO::PARAM_STR );
			$this->save->bindValue( ':CondicionEmbarque', $data['CondicionEmbarque'], \PDO::PARAM_STR );
			$this->save->bindValue( ':ZonaVenta', $data['ZonaVenta'], \PDO::PARAM_STR );
			$this->save->bindValue( ':cve_ruta', $data['cve_ruta'], \PDO::PARAM_STR );
			$this->save->bindValue( ':ID_Proveedor', $data['ID_Proveedor'], \PDO::PARAM_STR );
			$this->save->bindValue( ':Cve_CteProv', $data['ClaveClienteProv'], \PDO::PARAM_STR );

			$this->save->execute();
                        return "Guardado";
		} catch(PDOException $e) {
            return 'ERROR: ' . $e->getMessage();
        }

    }
	
    function getAll() {

        $sql = '
        SELECT
          *
        FROM
          ' . self::TABLE . '
      ';

        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\Clientes\Clientes' );
        $sth->execute( array( Cve_Clte ) );

        return $sth->fetchAll();

    }	

    function borrarCliente( $data ) {
        $sql = '
        UPDATE
          ' . self::TABLE . '
        SET
          Activo = 0
        WHERE
          Cve_Clte = ?
      ';
        $this->save = \db()->prepare($sql);
        $this->save->execute( array(
            $data['Cve_Clte']
        ) );
    }

    function actualizarClientes( $data ) {
        $sql = '
        UPDATE
          ' . self::TABLE . '
        SET
            RazonSocial = ?,
            RazonComercial = ?,
            CalleNumero = ?,
            Colonia = ?,
            Ciudad = ?,
            Estado = ?,
            Pais = ?,
            CodigoPostal = ?,
            RFC = ?,
            Telefono1 = ?,
            Telefono2 = ?,
            Telefono3 = ?,
            ClienteTipo = ?,
            ClienteGrupo = ?,
            ClienteFamilia = ?,
            CondicionPago = ?,
            MedioEmbarque = ?,
            ViaEmbarque = ?,
            CondicionEmbarque = ?,
            ZonaVenta = ?,
            cve_ruta = ?,
            ID_Proveedor = ?,
            Cve_CteProv = ?
        WHERE
          Cve_Clte = ?
      ';
        $this->save = \db()->prepare($sql);
        $this->save->execute( array(
            $data['RazonSocial'],
            $data['RazonComercial'],
            $data['CalleNumero'],
            $data['Colonia'],
            $data['Ciudad'],
            $data['Estado'],
            $data['Pais'],
            $data['CodigoPostal'],
            $data['RFC'],
            $data['Telefono1'],
            $data['Telefono2'],
            $data['Telefono3'],
            $data['ClienteTipo'],
            $data['ClienteGrupo'],
            $data['ClienteFamilia'],
            $data['CondicionPago'],
            $data['MedioEmbarque'],
            $data['ViaEmbarque'],
            $data['CondicionEmbarque'],
            $data['ZonaVenta'],
            $data['cve_ruta'],
            $data['Proveedor'],
            $data['ClaveClienteProv'],
            $data['ClaveCliente']
        ) );
    }
}
