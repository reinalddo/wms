<?php 

namespace Application\Models;

use \Illuminate\Database\Eloquent\Model;

class RutasSurtido extends Model
{
    protected $table = 'th_ruta_surtido';
    protected $primaryKey = 'idr';
    public $timestamps = false;
}