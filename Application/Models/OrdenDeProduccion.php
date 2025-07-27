<?php 

namespace Application\Models;

use \Illuminate\Database\Eloquent\Model;

class OrdenDeProduccion extends Model
{
    protected $table = 't_ordenprod';
    public $timestamps = false;
    protected $primaryKey = 'Folio_Pro';
}