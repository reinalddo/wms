<?php 

namespace Application\Models;

use \Illuminate\Database\Eloquent\Model;

class MotivosDeDevolucion extends Model
{
    protected $primaryKey = 'MOT_ID';
    protected $table = 'motivos_devolucion';
    public $timestamps = false;
}