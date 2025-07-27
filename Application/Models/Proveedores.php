<?php 

namespace Application\Models;

use \Illuminate\Database\Eloquent\Model;

class Proveedores extends Model
{
    protected $table = 'c_proveedores';
    protected $primaryKey = 'ID_Proveedor';
    public $timestamps = false;
}