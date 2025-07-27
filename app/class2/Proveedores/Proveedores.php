<?php

namespace Proveedores;

class Proveedores {

    const TABLE = 'c_proveedores';
    var $identifier;

    public function __construct( $cve_proveedor = false, $key = false ) 
    {
        if( $cve_proveedor ) 
        {
            $this->cve_proveedor = (int) $cve_proveedor;
        }

        if($key) 
        {
            $sql = sprintf('SELECT cve_proveedor FROM %s WHERE cve_proveedor = ? ', self::TABLE);

            $sth = \db()->prepare($sql);
            $sth->setFetchMode(\PDO::FETCH_CLASS, '\Proveedores\Proveedores');
            $sth->execute(array($key));

            $proveedor = $sth->fetch();
            $this->cve_proveedor = $proveedor->cve_proveedor;
        }
    }

    function getAll($filtro = "") 
    {
        $sql = ' SELECT * FROM ' . self::TABLE . ' where Activo=1 '.$filtro;

        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\Proveedores\Proveedores' );
        $sth->execute( array( $id_user ) );

        return $sth->fetchAll();
    }

    private function load() 
    {
        $sql = sprintf(' SELECT * FROM %s WHERE ID_Proveedor = ? ', self::TABLE);

        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\Proveedores\Proveedores' );
        $sth->execute( array( $this->ID_Proveedor ) );

        $this->data = $sth->fetch();
    }

    function __get( $key ) 
    {
        switch($key) 
        {
            case 'ID_Proveedor':
            case 'cve_proveedor':
            case 'Nombre':
            case 'direccion':
            case 'colonia':
            case 'cve_dane':
            case 'ciudad':
            case 'estado':
            case 'pais':
            case 'RUT':     
            case 'telefono1':
            case 'telefono2':
            case 'departamento':
            case 'des_municipio':     
                $this->load();
                return @$this->data->$key;
            default:
                return $this->key;
        }
    }

    function save( $_post ) 
    {
		try{
			if( !$_post['nombre_proveedor'] ) { return 'ERROR: El nombre es requerido.'; }
			if (!IsSet($_post["Activo"])) {
				$Activo = 1;
			} else {
				$Activo = $_post["Activo"];
			}
			$sql = "Call SPWS_AgregaProveedores ('" .utf8_decode($_post['nombre_proveedor'])."','" 
					.$_post['RFC']."','"
					.utf8_decode($_post['direccion'])."','"
					.$_post['CP']."','"
					.$_post['Cve_Proveedor']."','"
					.utf8_decode($_post['colonia'])."','"
					.utf8_decode($_post['ciudad'])."','"
					.utf8_decode($_post['estado'])."','"
					.utf8_decode($_post['pais'])."','"
					.$_post['telefono1']."','"
					.$_post['telefono2']."','"
					.$_post["longitud"]."','"
					.$_post["latitud"]."','"
					.$Activo."');";
			$rs = mysqli_query(\db2(), $sql) or die(mysqli_error(\db2()));
			return "Guardado";
		}
		catch(PDOException $e) 
		{
			return 'ERROR: ' . $e->getMessage();
		}
    }

    function actualizarProveedor( $data ) 
    {
        $sql = "
            UPDATE " . self::TABLE . " 
            SET
                Nombre = '".$data['nombre_proveedor']."', 
                direccion = '".$data['direccion']."',
                colonia = '".$data['colonia']."',
                cve_dane = '".$data['cve_dane']."',         
                ciudad = '".$data['ciudad']."',
                estado = '".$data['estado']."',
                pais = '".$data['pais']."',         
                RUT = '".$data['RUT']."',     
                telefono1 = '".$data['telefono1']."',
                telefono2 = '".$data['telefono2']."'       
            WHERE ID_Proveedor = '".$data['ID_Proveedor']."'
        ";
        $rs = mysqli_query(\db2(), $sql) or die(mysqli_error(\db2()));
    }
  
    function borrarProveedor( $data ) 
    {
        $sql = ' UPDATE ' . self::TABLE . ' SET Activo = 0 WHERE ID_Proveedor = ? ';
        $this->save = \db()->prepare($sql);
        $this->save->execute( array($data['ID_Proveedor']) );
    } 
  
    function recoveryProveedor( $data )
    {
        $sql = "UPDATE " . self::TABLE . "SET Activo = 1 WHERE  ID_Proveedor='".$data['ID_Proveedor']."';";
        $this->delete = \db()->prepare($sql);
        $this->delete->execute( array($data['ID_Proveedor']) );
    }

    function exist($cve_proveedor) 
    {
        $sql = sprintf(' SELECT * FROM ' . self::TABLE . ' WHERE cve_proveedor = ? ', self::TABLE );
        $sth = \db()->prepare( $sql );

        $sth->setFetchMode( \PDO::FETCH_CLASS, '\Proveedores\Proveedores' );
        $sth->execute( array( $cve_proveedor ) );

        $this->data = $sth->fetch();
    
        if(!$this->data)
            return false; 
        else 
            return true;
    }
  
    function tieneOrden() 
    {
        $sql = sprintf(' SELECT r.* FROM c_proveedores r, th_aduana cr WHERE cr.ID_Proveedor = r.ID_Proveedor  and r.ID_Proveedor = ? ', self::TABLE );

        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\Ruta\Ruta' );
        $sth->execute( array( $this->ID_Proveedor ) );
        $this->data = $sth->fetch();
    }
  
    public function inUse( $data ) 
    {
        $sql = "SELECT ID_Proveedor from th_aduana where ID_Proveedor='".$data['ID_Proveedor']."'";
        $sth = \db()->prepare($sql);
        $sth->execute();
        $data = $sth->fetch();

        if ($data['ID_Proveedor']) 
            return true;
        else
            return false;
  } 
}
