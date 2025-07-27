<?php

namespace Zona;

class Zona {

    const TABLE = 'c_zonacliente';
    var $identifier;

    public function __construct( $Cve_ZonaCte = false, $key = false ) {

        if( $Cve_ZonaCte ) {
            $this->Cve_ZonaCte = (int) $Cve_ZonaCte;
        }

        if($key) {

          $sql = sprintf('
          SELECT
            Cve_ZonaCte
          FROM
            %s
          WHERE
            Cve_ZonaCte = ?
        ',
                self::TABLE
            );

            $sth = \db()->prepare($sql);
            $sth->setFetchMode(\PDO::FETCH_CLASS, '\Zona\Zona');
            $sth->execute();

            $Zona = $sth->fetch();

            $this->Cve_ZonaCte = $Zona->Cve_ZonaCte;

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
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\Zona\Zona' );
        $sth->execute();

        return $sth->fetchAll();

    }
}
