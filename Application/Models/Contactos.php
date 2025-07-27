<?php 

namespace Application\Models;

use \Illuminate\Database\Eloquent\Model;

class Contactos extends Model
{
    protected $table = 'c_contactos';
    public $timestamps = false;
    protected $primaryKey = 'id';
}