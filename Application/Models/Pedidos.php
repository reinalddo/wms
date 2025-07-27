<?php 

namespace Application\Models;

use \Illuminate\Database\Eloquent\Model;

class Pedidos extends Model
{
    protected $primaryKey = 'id_pedido';
    protected $table = 'th_pedido';
    public $timestamps = false;
}