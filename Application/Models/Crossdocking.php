<?php 

namespace Application\Models;

use \Illuminate\Database\Eloquent\Model;

class Crossdocking extends Model
{
    protected $primaryKey = 'id_consolidado';
    protected $table = 'th_consolidado';
    public $timestamps = false;
}