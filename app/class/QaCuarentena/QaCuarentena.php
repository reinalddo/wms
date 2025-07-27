<?php namespace QaCuarentena;

class QaCuarentena
{
    const TABLE = 'th_qacuarentena';
    var $identifier;

    public function __construct( )
    {
        
    }

    
    function index()
    {
        $sql = 'SELECT
                    *
                FROM '.self::TABLE;

        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\QaCuarentena\QaCuarentena' );
        $sth->execute();
        return $sth->fetchAll();
    }



    function saveItemsInCuarentena( $data )
    {
        $sql = '
            INSERT INTO '.self::TABLE.' (
                clave_almacen,
                almacen,
                clave_producto,
                producto,
                lote,
                fecha_ingreso,                
                id_user,
                estatus
            ) VALUES (
                :clave_almacen,
                :almacen,
                :clave_producto,
                :producto,                
                :lote,
                :fecha_ingreso,
                :id_user,
                1
            )
        ';


        foreach ($data as $key => $value) {
            $this->save = \db()->prepare($sql);
            $this->save->bindValue( ':clave_almacen', $value['clave_almacen'], \PDO::PARAM_STR );
            $this->save->bindValue( ':almacen', $value['almacen'], \PDO::PARAM_STR );
            $this->save->bindValue( ':clave_producto', $value['clave_producto'], \PDO::PARAM_STR );
            $this->save->bindValue( ':producto', $value['producto'], \PDO::PARAM_STR );
            $this->save->bindValue( ':lote', $value['lote'], \PDO::PARAM_STR );
            $this->save->bindValue( ':fecha_ingreso', $value['fecha'], \PDO::PARAM_STR );
            $this->save->bindValue( ':id_user', $_SESSION['id_user'] , \PDO::PARAM_INT );
            $this->save->execute();
        }

        return true;
    }



}