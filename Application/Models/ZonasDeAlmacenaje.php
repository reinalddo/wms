<?php 

namespace Application\Models;

use \Illuminate\Database\Eloquent\Model;

class ZonasDeAlmacenaje extends Model
{
    protected $table = 'c_almacen';
    public $timestamps = false;
    protected $primaryKey = 'cve_almac';
}