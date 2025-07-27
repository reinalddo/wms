<?php 

namespace Application\Models;

use \Illuminate\Database\Eloquent\Model;

class CodigoDane extends Model
{
    protected $primaryKey = 'id_dane';
    protected $table = 'c_dane';
    public $timestamps = false;
}