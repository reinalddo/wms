<?php 

namespace Application\Models;

use \Illuminate\Database\Eloquent\Model;

class TipoDeCaja extends Model
{
    protected $table = 'c_tipocaja';
    public $timestamps = false;
    protected $primaryKey = 'id_tipocaja';
}