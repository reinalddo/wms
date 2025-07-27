<?php 

namespace Application\Models;

use \Illuminate\Database\Eloquent\Model;

class Prioridades extends Model
{
    protected $primaryKey = 'ID_Tipoprioridad';
    protected $table = 't_tiposprioridad';
    public $timestamps = false;
}