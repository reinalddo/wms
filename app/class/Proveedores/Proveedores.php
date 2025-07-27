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

    function getAllProvRTM($id_almacen) 
    {
        $sql = "SELECT * FROM c_proveedores WHERE ID_Proveedor IN (SELECT ID_Proveedor FROM V_ExistenciaGral WHERE tipo = 'area' AND cve_almac = $id_almacen)";

        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\Proveedores\Proveedores' );
        $sth->execute( array( $id_user ) );

        return $sth->fetchAll();
    }

    function getTransportadoras() 
    {
        $sql = "SELECT * FROM c_proveedores WHERE es_transportista = 1";

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
        //if( !$_post['nombre_proveedor'] ) { throw new \ErrorException( 'Full name is required.' ); }
        $sql = "
            INSERT INTO ". self::TABLE . "
            SET
                cve_proveedor = '".$_post['cve_proveedor']."', 
                Nombre = '".utf8_decode($_post['nombre_proveedor'])."', 
                direccion = '".utf8_decode($_post['direccion'])."', 
                colonia = '".utf8_decode($_post['colonia'])."', 
                cve_dane = '".utf8_decode($_post['cve_dane'])."', 
                ciudad = '".utf8_decode($_post['ciudad'])."', 
                estado = '".utf8_decode($_post['estado'])."', 
                pais = '".utf8_decode($_post['pais'])."'  , 
                RUT = '".utf8_decode($_post['RUT'])."', 
                telefono1 = '".$_post['telefono1']."', 
                telefono2 = '".$_post['telefono2']."',
                es_cliente = '".$_post['cliente_proveedor']."',
                es_transportista = '".$_post['transportista']."',
                Empresa = '".$_SESSION["cve_cia"]."'
        ";
        $rs = mysqli_query(\db2(), $sql) or die(mysqli_error(\db2()));

        $sql3 = "INSERT IGNORE INTO c_dane
                  SET
              cod_municipio = '{$_post['cve_dane']}'
            , departamento = '{$_post['ciudad']}'
            , des_municipio = '{$_post['estado']}'";

        $rs = mysqli_query(\db2(), $sql3) or die(mysqli_error(\db2()));

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
                telefono2 = '".$data['telefono2']."',
                es_transportista = '".$data['transportista']."',
                es_cliente = ".$data['cliente_proveedor']."
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
