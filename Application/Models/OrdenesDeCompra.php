<?php 

namespace Application\Models;

use \Illuminate\Database\Eloquent\Model;

class OrdenesDeCompra extends Model
{
    protected $primaryKey = 'num_pedimento';
    protected $table = 'th_aduana';
    public $timestamps = false;
}