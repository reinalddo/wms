<?php namespace application\models;

use Framework\Database\Model;
include_once $_SERVER['DOCUMENT_ROOT']. '/Framework/Database/Model.php';
class InvPiezas extends Model
{
    protected $table = 't_invpiezas';
}