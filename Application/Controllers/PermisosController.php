<?php

namespace Application\Controllers;

use Framework\View\View;
use Framework\Helpers\Utils;
use Framework\Http\Response;
use Application\Models\Roles;
use Framework\Http\Controller;
use jakeroid\tools\SimpleXLSX;
use Application\Models\Usuarios;
use Illuminate\Database\Capsule\Manager as Capsule;


/**
 * @SWG\Info(title="My First API", version="0.1")
 */

/**
 * @SWG\Get(
 *     path="/api/resource.json",
 *     @SWG\Response(response="200", description="An example resource")
 * )
 */

/**
 * @version 1.0.0
 * @category Inventario
 * @author Brayan Rincon <brayan262@gemail.com>
 */
class PermisosController extends Controller
{

    public function index()
    {
        $roles = Roles::where('activo', 1)->get();
        return new View('permisos.index', compact(['roles']) );
    }


    public function find( $perfil )
    {
        $sql = "SELECT * FROM t_menu WHERE orden = '0'";
        $rows_menu = Capsule::select(Capsule::raw($sql));


        $sql = "SELECT * FROM s_permisos_modulo WHERE ID_PERMISO IN (1,2,3,4,9,51,52,53,54,57,58,60,61,62,63,68,121)";
        $rows_permisos_modulos = Capsule::select(Capsule::raw($sql));

        foreach($rows_permisos_modulos as $value){
            $sql = "SELECT * FROM t_permisos_perfil WHERE ID_PERFIL = '{$perfil}' AND ID_PERMISO = '{$value->ID_PERMISO}'";
            $rows_permisos_perfil = Capsule::select(Capsule::raw($sql));
        }

        $data = [
            'menu' => $rows_menu,
            'generales' => [],            
        ];

        $this->response(200, ['data' => $data] );
    }
}