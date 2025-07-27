<?php 

namespace Application\Models;

use \Illuminate\Database\Eloquent\Model;

class ClientesRutas extends Model
{
    protected $table = 't_clientexruta';
    public $timestamps = false;
    protected $primaryKey = 'id_clientexruta';
}