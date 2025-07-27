<?php 

namespace Application\Models;

use \Illuminate\Database\Eloquent\Model;

class Clientes extends Model
{
    protected $table = 'c_cliente';
    public $timestamps = false;
    protected $primaryKey = 'id_cliente';
}