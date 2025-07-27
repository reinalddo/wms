<?php 

namespace Application\Models;

use \Illuminate\Database\Eloquent\Model;

class AreaDeRevision extends Model
{
    protected $primaryKey = 'ID_URevision';
    protected $table = 't_ubicaciones_revision';
    public $timestamps = false;
}