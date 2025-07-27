<?php

namespace TipoCliente;

class TipoCliente {

    const TABLE = 'c_tipocliente';
    var $identifier;

    public function __construct( $tipocliente = false, $key = false ) {

        if( $tipocliente ) {
            $this->tipocliente = (int) $tipocliente;
        }

        if($key) {

            $sql = sprintf('
          SELECT
            Cve_TipoCte
          FROM
            %s
          WHERE
            Cve_TipoCte = ?
        ',
                self::TABLE
            );

            $sth = \db()->prepare($sql);
            $sth->setFetchMode(\PDO::FETCH_CLASS, '\TipoCliente\TipoCliente');
            $sth->execute();

            $Cve_TipoCte = $sth->fetch();

            $this->tipocliente = $Cve_TipoCte->tipocliente;

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
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\TipoCliente\TipoCliente' );
        $sth->execute();

        return $sth->fetchAll();

    }
}
