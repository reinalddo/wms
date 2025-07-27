<?php 

namespace Application\Models;

use \Illuminate\Database\Eloquent\Model;

class VisitasClientes extends Model
{
    protected $primaryKey = 'Id';
    protected $table = 'RelDayCli';
    public $timestamps = false;
}