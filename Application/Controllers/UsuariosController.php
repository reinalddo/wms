<?php

namespace Application\Controllers;

use Framework\View\View;
use Framework\Helpers\Utils;
use Framework\Http\Response;
use Framework\Http\Controller;
use Application\Models\Usuarios;
use Illuminate\Database\Capsule\Manager as Capsule;


/**
 * @version 1.0.0
 * @category Usuarios
 * @author Brayan Rincon <brayan262@gemail.com>
 */
class UsuariosController extends Controller
{
    /**
     * Renderiza la vista general
     *
     * @return void
     */
    public function index()
    {
        //Obtener todas las empresas
        $empresas = new \Companias\Companias();
        $empresas = $empresas->getComp();

        //Obtener todos los roles
        $roles = new \Roles\Roles();
        $roles = $roles->getAll();

        // MOD 14
        // VER 25
        // AGREGAR 26
        // EDITAR 27
        // BORRAR 28

        $allowView = Capsule::table('t_profiles')
                    ->where('id_menu', 14)
                    ->where('id_submenu', 25) 
                    ->where('id_role', $_SESSION["perfil_usuario"])
                    ->get()->first();


        $allowAdd = Capsule::table('t_profiles')
                    ->where('id_menu', 14)
                    ->where('id_submenu', 26) 
                    ->where('id_role', $_SESSION["perfil_usuario"])
                    ->get()->first();

        $allowEdit = Capsule::table('t_profiles')
                    ->where('id_menu', 14)
                    ->where('id_submenu', 27) 
                    ->where('id_role', $_SESSION["perfil_usuario"])
                    ->get()->first();

        $allowDelete = Capsule::table('t_profiles')
                    ->where('id_menu', 14)
                    ->where('id_submenu', 28) 
                    ->where('id_role', $_SESSION["perfil_usuario"])
                    ->get()->first();

        return new View('usuarios.index', compact([
            'empresas', 'roles',
            'allowView', 'allowAdd', 'allowEdit', 'allowDelete'
        ]) );
    }


    /**
     * Devuelve todos los registros
     *
     * @return void
     */
    public function all()
    {     
        $data = Capsule::table('c_usuario')->get();
        $this->response(200, ['data'=>$data]);
    }

    /**
     * Devuelve todos los registros
     *
     * @return void
     */
    public function paginate()
    {     
        $page   = $this->pSQL($_POST['page']); // get the requested page
        $limit  = $this->pSQL($_POST['rows']); // get how many rows we want to have into the grid
        $sidx   = $this->pSQL($_POST['sidx']); // get index row - i.e. user click to sort
        $sord   = $this->pSQL($_POST['sord']); // get the direction
        $skip   = 0;

        $criterio = $this->pSQL($_POST['criterio']);

        if(!empty($fecInicio)) {
            $fecInicio = date("Y-m-d", strtotime($fecInicio));
        }
        if(!empty($fechaFin)) {
            $fechaFin = date("Y-m-d", strtotime($fechaFin));
        }
            
        $data = Capsule::table('c_usuario')->where('Activo', 1);
        $count = $data->count();

        if(!empty($criterio)) {
            $data = $data->where('cve_usuario', 'LIKE', "%{$criterio}%")
                            ->where('nombre_completo', 'LIKE', "%{$criterio}%")
                            ->where('email', 'LIKE', "%{$criterio}%")
                            ->orderBy('des_usuario','ASC')
                            ->skip($skip)
                            ->take($limit);
        }

        $data = $data->get();
        $response = $this->prepareStructureResponseGrid($data, $page, $limit, $sidx, $sord);

        foreach ($data as $value) {
            $response['rows']['id'] = $value->id_user;
            $response['rows']['cell'][] = [
                $value->id_user,
                $value->cve_usuario,
                $value->des_usuario,
                utf8_encode($this->replaceStrings($value->cve_cia))
            ];
        }
        echo json_encode($response);


    }



    public function store()
    {
        
    }

    /**
     * Retorna los usuarios con perfil de Administrador
     *
     * @return void
     */
    public function usuariosAdministradores()
    {
      $data = Usuarios::where('perfil', 1)->where('activo', 1)->get();
      $this->response(200, [
          'data' => $data
      ]);
    }


    /**
     * Valida el usaurio y contraseña de un usuario
     *
     * @return void
     */
    public function validarCredencial()
    {
        $user = $this->getPost('user');
        $password = $this->getPost('password');

        $data = Usuarios::where('cve_usuario', $user)->where('pwd_usuario', $password)->count();
        $this->response(200, [
            'data' => $data > 0 ? TRUE : FALSE
        ]);
    }


    public function update( $id )
    {     
        
    }


    /**
     * Undocumented function
     *
     * @return void
     */
    public function destroy( $id )
    {     
        
    }
}
