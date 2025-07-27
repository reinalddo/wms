<?php 

namespace Application\Models;

use \Illuminate\Database\Eloquent\Model;

class Destinatarios extends Model
{
    protected $primaryKey = 'id_destinatario';
    protected $table = 'c_destinatarios';
    public $timestamps = false;
}