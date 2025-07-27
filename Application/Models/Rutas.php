<?php 

namespace Application\Models;

use \Illuminate\Database\Eloquent\Model;

class Rutas extends Model
{
    protected $table = 't_ruta';
    protected $primaryKey = 'ID_Ruta';
    public $timestamps = false;
}