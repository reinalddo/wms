<?php
include '../../config.php';

if(isset($_POST['action']) && !empty($_POST['action'])){ 

    switch ($_POST['action']) {
        case 'enter-view':

                $sql = 'SELECT id_role, rol FROM t_roles WHERE activo = 1';
                $roles = getArraySQL($sql);

                $array = [
                    "roles"=>$roles
                ];

                echo json_encode($array);
            break;
        case 'search-role':

                $id_role = $_POST['role'];

                $sql = "SELECT * FROM s_permisos_modulo WHERE ID_PERMISO in (1,2,3,4,9,51,52,53,54,57,58,60,61,62,63,68,121)";

                $permi = getArraySQL($sql);

                $PERMI_MOVIL = [];

                if(!empty($permi) && is_array($permi)){

                    for($i = 0; $i < count($permi); $i++){
                        $sqlRRR = "SELECT * FROM t_permisos_perfil WHERE ID_PERFIL = '".$id_role."' AND ID_PERMISO = '".$permi[$i]['ID_PERMISO']."' GROUP BY ID_PERMISO, ID_PERFIL";

                        $res = getArraySQL($sqlRRR);

                        $arrayPer = [
                            "name"=> $permi[$i]['DESCRIPCION'],
                            "state"=> $res[0]["Activo"],
                            "id"=>$permi[$i]['ID_PERMISO'],
                            "SQL"=>$sqlRRR
                        ];

                        array_push($PERMI_MOVIL, $arrayPer);
                    }
                }

                $sql = "SELECT * FROM t_menu WHERE orden = '0' AND es_cliente = 0 ORDER BY orden_screen ASC";
                $PERMI_OTRO = getArraySQL($sql);

                if(!empty($PERMI_OTRO) && is_array($PERMI_OTRO)){

                    for($i = 0; $i < count($PERMI_OTRO); $i++){

                        $sqlRRR = "SELECT a.id_menu, a.id_role, a.id_submenu, a.Activo, b.id_opciones FROM t_profiles a, t_submenu b WHERE a.id_role = '".$id_role."' and a.id_menu = '".$PERMI_OTRO[$i]['id_menu']."' and a.id_submenu = b.id_submenu";

                        $res = getArraySQL($sqlRRR);

                        $PERMI_OTRO[$i]["permisos"] = $res;
                    }
                }

                $array = [
                    "permiso_movil"=>$PERMI_MOVIL,
                    "permiso_otro"=>$PERMI_OTRO
                ];

                echo json_encode($array);
                
            break;
        case 'search-menu':

                $structure = '';
                $id_role = $_POST['role'];
                $ITEMS = [];

                $sql = "SELECT id_menu, modulo, id_menu_padre FROM t_menu WHERE id_menu_padre = '".$_POST['id-padre']."' AND orden != '0' AND es_cliente = 0";

                $MENU = getArraySQL($sql);

                if(!empty($MENU) && is_array($MENU)){

                    for($i = 0; $i < count($MENU); $i++){

                        $sqlRRR = "SELECT a.id_menu, a.id_role, a.id_submenu, a.Activo, b.id_opciones FROM t_profiles a, t_submenu b WHERE a.id_role = '".$id_role."' and a.id_menu = '".$MENU[$i]['id_menu']."' AND b.id_menu = '".$MENU[$i]['id_menu']."' and a.id_submenu = b.id_submenu AND a.id_menu IN (SELECT id_menu FROM t_menu WHERE es_cliente = 0) ORDER BY b.id_opciones ASC";

                        $res = getArraySQL($sqlRRR);

                        $structure .= '<li class="list-group-item d-flex justify-content-between align-items-center" style="background-color: #f5f5f5;" >'.
                                        '<h5 style="float: none;">'.$MENU[$i]['modulo'].'</h5>'.
                                        '<div class="plomo" style="position: absolute; right: 1%; top: 10%;">';
 

                        if(!empty($res) && is_array($res)){

                            for($l = 0; $l < count($res); $l++){
                                $check = '';

                                if($res[$l]['id_opciones'] == 1){

                                    if($res[$l]['Activo'] == 1)
                                        $check = 'checked';
                                    $structure .= '<input id="checkbox-ver-'.$MENU[$i]['id_menu'].'" type="checkbox" '.$check.'/>'.
                                        '<label for="checkbox-ver-'.$MENU[$i]['id_menu'].'" class="btn  bt-group ">VER</label>';
                                    array_push($ITEMS, ["element"=>"checkbox-ver-".$MENU[$i]['id_menu'], "state"=>$check]);
                                }
                                else if($res[$l]['id_opciones'] == 2){

                                    if($res[$l]['Activo'] == 1)
                                        $check = 'checked';
                                    $structure .= '<input id="checkbox-agregar-'.$MENU[$i]['id_menu'].'" type="checkbox" class="js-switch" '.$check.'/>'.
                                    '<label for="checkbox-agregar-'.$MENU[$i]['id_menu'].'" class="btn  bt-group ">AGREGAR</label>';
                                    array_push($ITEMS, ["element"=>"checkbox-agregar-".$MENU[$i]['id_menu'], "state"=>$check]);
                                }
                                else if($res[$l]['id_opciones'] == 3){

                                    if($res[$l]['Activo'] == 1)
                                        $check = 'checked';
                                    $structure .= '<input id="checkbox-editar-'.$MENU[$i]['id_menu'].'" type="checkbox" class="js-switch" '.$check.'/>'.
                                    '<label for="checkbox-editar-'.$MENU[$i]['id_menu'].'" class="btn  bt-group ">EDITAR</label>';
                                    array_push($ITEMS, ["element"=>"checkbox-editar-".$MENU[$i]['id_menu'], "state"=>$check]);
                                }
                                else{
                                    if($res[$l]['Activo'] == 1)
                                        $check = 'checked';
                                    $structure .= '<input id="checkbox-borrar-'.$MENU[$i]['id_menu'].'" type="checkbox" class="js-switch" '.$check.'/>'.
                                    '<label for="checkbox-borrar-'.$MENU[$i]['id_menu'].'" class="btn  bt-group ">BORRAR</label>';
                                    array_push($ITEMS, ["element"=>"checkbox-borrar-".$MENU[$i]['id_menu'], "state"=>$check]);
                                }
                            }
                            $structure .= '</div></li>';
                        }
                        else{

                            $check = '';

                            $structure .= '<input id="checkbox-ver-'.$MENU[$i]['id_menu'].'" type="checkbox" class="js-switch" '.$check.'/>'.
                                    '<label for="checkbox-ver-'.$MENU[$i]['id_menu'].'" class="btn  bt-group ">VER</label>';
                            $structure .= '<input id="checkbox-agregar-'.$MENU[$i]['id_menu'].'" type="checkbox" class="js-switch" '.$check.'/>'.
                                    '<label for="checkbox-agregar-'.$MENU[$i]['id_menu'].'" class="btn  bt-group ">AGREGAR</label>';
                            $structure .= '<input id="checkbox-editar-'.$MENU[$i]['id_menu'].'" type="checkbox" class="js-switch" '.$check.'/>'.
                                    '<label for="checkbox-editar-'.$MENU[$i]['id_menu'].'" class="btn  bt-group ">EDITAR</label>';
                            $structure .= '<input id="checkbox-borrar-'.$MENU[$i]['id_menu'].'" type="checkbox" class="js-switch" '.$check.'/>'.
                                    '<label for="checkbox-borrar-'.$MENU[$i]['id_menu'].'" class="btn  bt-group ">BORRAR</label>';
                            $structure .= '</div></li>';
                            array_push($ITEMS, ["element"=>"checkbox-ver-".$MENU[$i]['id_menu'], "state"=>$check]);
                            array_push($ITEMS, ["element"=>"checkbox-agregar-".$MENU[$i]['id_menu'], "state"=>$check]);
                            array_push($ITEMS, ["element"=>"checkbox-editar-".$MENU[$i]['id_menu'], "state"=>$check]);
                            array_push($ITEMS, ["element"=>"checkbox-borrar-".$MENU[$i]['id_menu'], "state"=>$check]);
                        }

                        //BUSCANDO HIJOS

                        $sqlC = "SELECT id_menu, modulo, id_menu_padre FROM t_menu WHERE id_menu_padre = '".$MENU[$i]['id_menu']."' AND orden != '0' AND es_cliente = 0";
                        $children = getArraySQL($sqlC);

                        $MENU[$i]['children'] = $children;

                        if(!empty($children) && is_array($children)){

                            for($t = 0; $t < count($children); $t++){

                                $sqlCCC = "SELECT a.id_menu, a.id_role, a.id_submenu, a.Activo, b.id_opciones FROM t_profiles a, t_submenu b WHERE a.id_role = '".$id_role."' and a.id_menu = '".$children[$t]['id_menu']."' and a.id_submenu = b.id_submenu ORDER BY b.id_opciones ASC";

                                $res_chil = getArraySQL($sqlCCC);

                                $structure .= '<li class="list-group-item d-flex justify-content-between align-items-center" >'.
                                                '<h5 style="float: none;"> * '.$children[$t]['modulo'].'</h5>'.
                                                '<div class="plomo" style="position: absolute; right: 1%; top: 10%;">';
                                if(!empty($res_chil) && is_array($res_chil)){

                                    for($q = 0; $q < count($res_chil); $q++){
                                        $check = '';

                                        if($res_chil[$q]['id_opciones'] == 1){

                                            if($res_chil[$q]['Activo'] == 1)
                                                $check = 'checked';
                                            $structure .= '<input id="checkbox-ver-'.$children[$t]['id_menu'].'" type="checkbox" class="js-switch" '.$check.'/>'.
                                            '<label for="checkbox-ver-'.$children[$t]['id_menu'].'" class="btn  bt-group ">VER</label>';
                                            array_push($ITEMS, ["element"=>"checkbox-ver-".$children[$t]['id_menu'], "state"=>$check]);
                                        }
                                        else if($res_chil[$q]['id_opciones'] == 2){

                                            if($res_chil[$q]['Activo'] == 1)
                                                $check = 'checked';
                                            $structure .= '<input id="checkbox-agregar-'.$children[$t]['id_menu'].'" type="checkbox" class="js-switch" '.$check.'/>'.
                                            '<label for="checkbox-agregar-'.$children[$t]['id_menu'].'" class="btn  bt-group ">AGREGAR</label>';
                                            array_push($ITEMS, ["element"=>"checkbox-agregar-".$children[$t]['id_menu'], "state"=>$check]);
                                        }
                                        else if($res_chil[$q]['id_opciones'] == 3){

                                            if($res_chil[$q]['Activo'] == 1)
                                                $check = 'checked';
                                            $structure .= '<input id="checkbox-editar-'.$children[$t]['id_menu'].'" type="checkbox" class="js-switch" '.$check.'/>'.
                                            '<label for="checkbox-editar-'.$children[$t]['id_menu'].'" class="btn  bt-group ">EDITAR</label>';
                                            array_push($ITEMS, ["element"=>"checkbox-editar-".$children[$t]['id_menu'], "state"=>$check]);
                                        }
                                        else{
                                            if($res_chil[$q]['Activo'] == 1)
                                                $check = 'checked';
                                            $structure .= '<input id="checkbox-borrar-'.$children[$t]['id_menu'].'" type="checkbox" class="js-switch" '.$check.'/>'.
                                            '<label for="checkbox-borrar-'.$children[$t]['id_menu'].'" class="btn  bt-group ">BORRAR</label>';
                                            array_push($ITEMS, ["element"=>"checkbox-borrar-".$children[$t]['id_menu'], "state"=>$check]);
                                        }
                                    }
                                    $structure .= '</div></li>';
                                }
                                else{

                                    $check = '';

                                    $structure .= '<input id="checkbox-ver-'.$children[$t]['id_menu'].'" type="checkbox" class="js-switch" '.$check.'/>'.
                                            '<label for="checkbox-ver-'.$children[$t]['id_menu'].'" class="btn  bt-group ">VER</label>';
                                    $structure .= '<input id="checkbox-agregar-'.$children[$t]['id_menu'].'" type="checkbox" class="js-switch" '.$check.'/>'.
                                            '<label for="checkbox-agregar-'.$children[$t]['id_menu'].'" class="btn  bt-group ">AGREGAR</label>';

                                    $structure .= '<input id="checkbox-editar-'.$children[$t]['id_menu'].'" type="checkbox" class="js-switch" '.$check.'/>'.
                                            '<label for="checkbox-editar-'.$children[$t]['id_menu'].'" class="btn  bt-group ">EDITAR</label>';
                                    $structure .= '<input id="checkbox-borrar-'.$children[$t]['id_menu'].'" type="checkbox" class="js-switch" '.$check.'/>'.
                                            '<label for="checkbox-borrar-'.$children[$t]['id_menu'].'" class="btn  bt-group ">BORRAR</label>';
                                    $structure .= '</div></li>';
                                    array_push($ITEMS, ["element"=>"checkbox-ver-".$children[$t]['id_menu'], "state"=>$check]);
                                    array_push($ITEMS, ["element"=>"checkbox-agregar-".$children[$t]['id_menu'], "state"=>$check]);
                                    array_push($ITEMS, ["element"=>"checkbox-editar-".$children[$t]['id_menu'], "state"=>$check]);
                                    array_push($ITEMS, ["element"=>"checkbox-borrar-".$children[$t]['id_menu'], "state"=>$check]);
                                }
                            }
                        }

                        $MENU[$i]['children'] = $children;
                    }
                }
                
                $array = [
                    "estru"=>$structure,
                    "menus"=>$MENU,
                    "items"=>$ITEMS
                ];

                echo json_encode($array);

            break;
        case 'save-movil':

                $id_role = $_POST['role'];
                $id_permi = $_POST['id'];
                $state = $_POST['state'];

                $sql = "SELECT * FROM t_permisos_perfil WHERE ID_PERFIL = '".$id_role."' AND ID_PERMISO = '".$id_permi."'";
                $validate = getArraySQL($sql);

                if(!empty($validate) && is_array($validate)){

                    $sql = "UPDATE t_permisos_perfil SET Activo = '".$state."' WHERE ID_PERFIL = '".$id_role."' AND ID_PERMISO = '".$id_permi."'";
                    executeSQL($sql);
                }
                else{

                    $sql = "INSERT INTO t_permisos_perfil (ID_PERFIL, ID_PERMISO, STATUS, Activo) VALUES ('".strip_tags($id_role)."','".strip_tags($id_permi)."','1','".strip_tags($state)."')";
                    executeSQL($sql);
                }
                      

                $modulo_log = "Administración->Usuarios->Permisos";
                $usuario_sesion = $_POST['usuario_log'];
                $operacion_log = "El usuario {$usuario_sesion} generó los permisos del Perfil";
                $observaciones_log = "";
                $sql_log = "INSERT INTO t_log_operaciones(modulo, usuario, fecha, operacion, dispositivo, observaciones) VALUES ('{$modulo_log}', '{$usuario_sesion}', NOW(), CONCAT('{$operacion_log} ', (SELECT rol FROM t_roles WHERE id_role = {$id_role})), 'web', '{$observaciones_log}')";
                executeSQL($sql_log);

                $array = [
                    "msj"=>$sql
                ];

                echo json_encode($array);
            break;
        case 'reiniciar-perfil':
            $id_role = $_POST['role'];

            $sql = "DELETE FROM t_profiles WHERE id_role = {$id_role}";
            executeSQL($sql);

            $array = [
                "msj"=>$sql
            ];

            echo json_encode($array);

            break;
        case 'save-otros':

                $id_role = $_POST['role'];
                $id_permi = $_POST['id'];
                $state = $_POST['state'];
                $case = $_POST['case'];

                $sql = "SELECT COUNT(*) as num_menu FROM t_profiles WHERE id_menu = '".$id_permi."' AND id_role = '".$id_role."'";
                $validate = getArraySQL($sql);

                if($validate[0]['num_menu'] < 4)
                {
                    $sql = "SELECT * FROM t_submenu a WHERE a.id_menu = '".$id_permi."' AND a.id_submenu NOT IN (SELECT DISTINCT b.id_submenu FROM t_profiles b WHERE b.id_menu = '".$id_permi."' AND id_role = '".$id_role."')";
                    $v_submenu = getArraySQL($sql);

                    foreach ($v_submenu as $row) 
                    {
                        $id_subMenu = $row['id_submenu'];
                        $sql = "INSERT INTO t_profiles (id_menu, id_submenu, id_role, Activo) VALUES ('".strip_tags($id_permi)."','".strip_tags($id_subMenu)."','".strip_tags($id_role)."','0')";
                        executeSQL($sql);


                    }

                }


                $sql = "SELECT * FROM t_submenu a WHERE a.id_menu = '".$id_permi."' and a.id_opciones = '".$case."'";

                $validate = getArraySQL($sql);

                if(!empty($validate) && is_array($validate)){
                    $id_subMenu = $validate[0]['id_submenu'];
                }
                else{

                    $sql = "INSERT INTO t_submenu (id_menu, id_opciones) VALUES ('".strip_tags($id_permi)."','".strip_tags($case)."')";
                    executeSQL($sql);
                }

                $sql = "SELECT * FROM t_submenu a WHERE a.id_menu = '".$id_permi."' and a.id_opciones = '".$case."'";

                $validate = getArraySQL($sql);

                if(!empty($validate) && is_array($validate)){

                    $id_subMenu = $validate[0]['id_submenu'];

                    $sql = "SELECT a.id_perfil, a.id_submenu, a.id_role, a.id_menu FROM t_profiles a WHERE a.id_menu = '".$id_permi."' and a.id_role = '".$id_role."' and a.id_submenu = '".$id_subMenu."'";

                    $validate = getArraySQL($sql);

                    if(!empty($validate) && is_array($validate)){

                         $sql = "UPDATE t_profiles SET Activo = '".$state."' WHERE id_perfil = '".$validate[0]['id_perfil']."'";
                        executeSQL($sql);
                    }
                    else{
                        $sql = "INSERT INTO t_profiles (id_menu, id_submenu, id_role, Activo) VALUES ('".strip_tags($id_permi)."','".strip_tags($id_subMenu)."','".strip_tags($id_role)."','".strip_tags($state)."')";
                        executeSQL($sql);
                    }
                }

                #$sql = "INSERT INTO t_profiles (id_menu, id_submenu, id_role, Activo) (SELECT DISTINCT t.id_menu_padre, sb.id_submenu, {$id_role}, 1 FROM t_menu t LEFT JOIN t_submenu sb ON sb.id_menu = t.id_menu WHERE t.id_menu = {$id_permi} AND t.orden > 0 AND CONCAT(t.id_menu_padre, sb.id_submenu) NOT IN (SELECT CONCAT(pr.id_menu, pr.id_submenu) FROM t_profiles pr WHERE pr.id_role = {$id_role}))";
                //$sql = "INSERT INTO t_profiles (id_menu, id_submenu, id_role, Activo) (SELECT DISTINCT t.id_menu_padre, sb.id_submenu, {$id_role}, 1 FROM t_menu t LEFT JOIN t_submenu sb ON sb.id_menu = t.id_menu WHERE t.id_menu = {$id_permi} AND t.orden > 0 AND CONCAT(t.id_menu_padre, sb.id_submenu, {$id_role}) NOT IN (SELECT CONCAT(k.id_menu_padre, sk.id_submenu, {$id_role}) FROM t_menu k LEFT JOIN t_submenu sk ON sk.id_menu = k.id_menu WHERE k.id_menu = {$id_permi} AND k.orden > 0))";
                $sql = "INSERT INTO t_profiles (id_menu, id_submenu, id_role, Activo) (SELECT DISTINCT t.id_menu_padre, sb.id_submenu, {$id_role}, 1 FROM t_menu t LEFT JOIN t_submenu sb ON sb.id_menu = t.id_menu WHERE t.id_menu = {$id_permi} AND t.orden > 0 AND CONCAT(t.id_menu_padre, sb.id_submenu) NOT IN (SELECT CONCAT(pr.id_menu, pr.id_submenu) FROM t_profiles pr WHERE pr.id_role = {$id_role}))";
                executeSQL($sql);

                $modulo_log = "Administración->Usuarios->Permisos";
                $usuario_sesion = $_POST['usuario_log'];
                $operacion_log = "El usuario {$usuario_sesion} generó los permisos del Perfil";
                $observaciones_log = "";
                $sql_log = "INSERT INTO t_log_operaciones(modulo, usuario, fecha, operacion, dispositivo, observaciones) VALUES ('{$modulo_log}', '{$usuario_sesion}', NOW(), CONCAT('{$operacion_log} ', (SELECT rol FROM t_roles WHERE id_role = {$id_role})), 'web', '{$observaciones_log}')";
                executeSQL($sql_log);

                $array = [
                    "msj"=>$sql
                ];

                echo json_encode($array);
            break;
    }   

}

function getArraySQL($sql){

    $conexion = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    mysqli_set_charset($conexion, "utf8");

    if(!$result = mysqli_query($conexion, $sql)) 
        echo "Falló la preparación: (" . mysqli_error($conexion) . ") ";;

    $rawdata = array();

    $i = 0;

    while($row = mysqli_fetch_assoc($result))
    {
        $rawdata[$i] = $row;
        $i++;
    }

    mysqli_close($conexion);

    return $rawdata;
}

function executeSQL($sql){

    $conexion = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    mysqli_set_charset($conexion, "utf8");

    $result = mysqli_query($conexion, $sql);

    if($result) {
        $res = "success";
    }
    else{
        $res = "Error: " . $sql . "<br>" . mysqli_error($conexion);
    }

    $array = ["res" => $res];

    return $array;

    disconnectDB($conexion);
}