<?php 

namespace Application\Models;

use \Illuminate\Database\Eloquent\Model;

class UnidadesMedida extends Model
{
    protected $table = 'c_unimed';
    public $timestamps = false;
    protected $primaryKey = 'id_umed';
}