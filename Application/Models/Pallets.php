<?php 

namespace Application\Models;

use \Illuminate\Database\Eloquent\Model;

class Pallets extends Model
{
    protected $primaryKey = 'IDContenedor';
    protected $table = 'c_charolas';
    public $timestamps = false;
}