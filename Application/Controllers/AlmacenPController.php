<?php namespace Application\Controllers;

use Application\Models\AlmacenP;
use Framework\Http\Controller;

include 'Framework/Http/Controller.php';
include 'Application/Models/AlmacenP.php';
class AlmacenPController extends Controller
{

  /**
   * Undocumented function
   */
  public function __construct()
  {
    parent::__construct();
    $this->model = new AlmacenP();
  }


  /**
   * Muestra una vista con el listado de todos los registros
   *
   * @return void
   */
  public function index()
  {

  }


  /**
   * Muestra una vista con un registro especifico 
   * determinado por le parametro $id
   * @param int|string $id
   * @return void
   */
  public function show( $id )
  {

  }

  
  /**
   * Devuelve un un registro especifico 
   * determinado por le parametro $id
   * @param int|string $id
   * @return void
   */
  public function view( $id )
  {

  }


  /**
   * Devuelve todos los registro de la tabla
   *
   * @return void
   */
  public function all()
  {
    $rows = $this->model->all();
    $this->response(200, $rows);
  }


  /**
   * Muestra una vista para ingresar un nuevo registro
   *
   * @return void
   */
  public function create()
  {
    
  }


  /**
   * Inserta un nuevo registro en la base de datos
   *
   * @return void
   */
  public function store()
  {
    
  }


  /**
   * Edita un registro de la base de tabla
   *
   * @param int|string $id
   * @return void
   */
  public function edit( $id )
  {
    
  }


  /**
   * Actualiza un registro de la base de tabla
   *
   * @param int|string $id
   * @return void
   */
  public function update( $id )
  {
    
  }


  /**
   * Elimina un registro de la tabla
   *
   * @param int|string $id
   * @return void
   */
  public function destroy( $id )
  {
    
  }

    
    
  function activos()
  {
    $rows = $this->model->where(['activo'=>'1'])->get();
    return $rows;
  }

    /**
     * Undocumented function
     *
     * @param [type] $clave
     * @return void
     */
    public function exist($clave)
    {
        $sql = "SELECT id FROM ".self::TABLE." WHERE clave = ?";
        $sth = \db()->prepare( $sql );        
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\App\Controllers\AlmacenP' );
        $sth->execute( array( $clave ) );
        $this->data = $sth->fetch();
        
        if(!$this->data)
            return false; 
        else 
            return true;
    }
	
}
