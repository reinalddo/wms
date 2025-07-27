<?php

  namespace Transactions;

  class Transactions {

    const TABLE = 'transactions';

    // Construct existing product
    public function __construct( $transaction_id = false ) {

      if( $transaction_id ) {
        $this->transaction_id = $transaction_id;
      }

    }

    // Load product details
    private function load() {

      $sql = sprintf('
        SELECT
          *
        FROM
          %s
        WHERE
          transaction_id = ?
      ',
        self::TABLE
      );

      $sth = \db()->prepare( $sql );
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\Transaction\Transaction' );
      $ex = $sth->execute( array( $this->transaction_id ) );
      $user = $sth->fetch();

      $this->data = $user;

    }

    // Get key
    function __get($key) {

      switch($key) {
        case 'id_transaction':
        case 'id_user':
        case 'name':
        case 'email':
        case 'transaction_id':
        case 'transaction_amount':
        case 'transaction_fee':
        case 'timestamp':
          $this->load();
          return @$this->data->$key;
        default:
          return $this->key;
      }

    }

    function getList( $id_user ) {

      $sql = '
        SELECT
          *
        FROM
          ' . self::TABLE . '
        WHERE
          id_user = ?
      ';

      $sth = \db()->prepare( $sql );
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\Transactions\Transactions' );
      $sth->execute( array( $id_user ) );

      return $sth->fetchAll();

    }

    function save($data) {

      $sql = sprintf('
        INSERT INTO
          %s
        SET
          id_user = :id_user
        , name = :name
        , email = :email
        , transaction_id = :transaction_id
        , transaction_amount = :transaction_amount
        , transaction_fee = :transaction_fee
        , timestamp = :timestamp
      ',
        self::TABLE
      );

      $this->save = \db()->prepare($sql);

      $this->save->bindValue(':id_user', $data['id_user'], \PDO::PARAM_INT);
      $this->save->bindValue(':name', $data['name'], \PDO::PARAM_STR);
      $this->save->bindValue(':email', $data['email'], \PDO::PARAM_STR);
      $this->save->bindValue(':transaction_id', $data['transaction_id'], \PDO::PARAM_STR);
      $this->save->bindValue(':transaction_amount', $data['transaction_amount'], \PDO::PARAM_STR);
      $this->save->bindValue(':transaction_fee', $data['transaction_fee'], \PDO::PARAM_STR);
      $this->save->bindValue(':timestamp', time(), \PDO::PARAM_STR);

      $this->save->execute();

    }

  }
