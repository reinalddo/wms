<?php 

namespace Application\Models;

use \Illuminate\Database\Eloquent\Model;

class AreaDeEmbarque extends Model
{
    protected $table = 't_ubicacionembarque';
    public $timestamps = false;
    protected $primaryKey = 'ID_Embarque';
}