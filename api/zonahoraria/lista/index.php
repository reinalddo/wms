    <?php
include '../../../app/load.php';

// Initalize Slim
$app = new \Slim\Slim();

if( !isset( $_SESSION['id_user'] ) AND !$_SESSION['id_user'] ) {
    exit();
}

$ga = new \ZonaHoraria\ZonaHoraria();


if( $_POST['action'] == 'add' ) {
    $agg=$ga->save($_POST);
    if($agg==true)
        $success = true;
    else 
		$success= false;

    $arr = array(
		"success"=>$success
 	);

    echo json_encode($arr);
} 

if( $_POST['action'] == 'update' ) {
    $agg=$ga->update($_POST);
    if($agg==true)
        $success = true;
    else 
        $success= false;

    $arr = array(
        "success"=>$success
    );

    echo json_encode($arr);
} 

if( $_POST['action'] == 'getZonaHoraria' ) {
    if($_POST['tipo'] == "buscar"){
        $ga->getZonaHoraria();
        $arr = [];
        $arr["id_user"] = $ga->data->id_user;
        $arr["descripcion"] = $ga->data->descripcion;
        $arr["success"] = true;
        echo json_encode($arr);
    }
    else{
        $ga->existe($_SESSION['id_user']);
          if($ga->data->id_user == $_SESSION['id_user']){
               $arr["success"] = false;
            }
          else 
               $arr["success"]= true;
        echo json_encode($arr);
    }
} 


