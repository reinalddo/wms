<?php 

namespace Application\Models;

use \Illuminate\Database\Eloquent\Model;

class Ubicaciones extends Model
{
    protected $table = 'c_ubicacion';
    public $timestamps = false;
    protected $primaryKey = 'idy_ubica';
}