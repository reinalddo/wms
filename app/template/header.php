<?php
    //session_set_cookie_params(86400);
    session_start();
    //set_time_limit(0);
header('Content-type: text/html; charset=utf-8');
    $user = new \User\User( ID_USER );
    $directoryURI = $_SERVER['REQUEST_URI'];
    $path = parse_url($directoryURI, PHP_URL_PATH);
    $components = explode('/', $path);
    $first_part = $components[1];
    //$_SESSION['id_user'] = $user->id_user;
    //$_SESSION['name'] = $user->name;
    $model_usuario = new \Usuarios\Usuarios();
    $model_usuario->id_user = $_SESSION["id_user"];
    $model_usuario->__get("id_user");
    $user = $model_usuario->data;

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    mysqli_ping($conn);
    //mysqli_set_charset($conn, 'utf8mb3');

    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

    $sql = "SELECT * FROM c_compania WHERE cve_cia = '{$user->cve_cia}'";
    //echo $sql."<br>";
    $rs = mysqli_query($conn, $sql);
    $empresa = mysqli_fetch_array($rs);
    $img = explode("/",$empresa["imagen"]);

    $count = mysqli_num_rows($rs);

    //../to_img.php?img=embarques/"+data.data[0].foto1

    //$empresaImg = '../to_img.php?img=compania/'.$img[sizeof($img)-1];
    $empresaImg = $empresa["imagen"];

    $sqlSFA = "SELECT Valor FROM t_configuraciongeneral WHERE cve_conf = 'SFA'";
    if (!($res = mysqli_query($conn, $sqlSFA))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }
    $valor_sfa = "0";
    if(mysqli_num_rows($res) == 0)
        $valor_sfa = "0";
    else
    {
        $row = mysqli_fetch_array($res);
        $valor_sfa = $row['Valor'];
    }

//require("/class/SendGrid/sendgrid-php.php");
//$email = new \SendGrid\Mail\Mail();

?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="es" class="no-js">
<!--<![endif]-->
<head>

    <meta charset="UTF-8">

    <title><?php echo SITE_TITLE; ?></title>

    <?php /* ?><meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests"><?php */ ?>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1" name="viewport"/>
    <meta content="" name="description"/>
    <meta content="" name="author"/>

    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=all" rel="stylesheet" type="text/css"/>
    <!--link href="<?php echo ST; ?>global/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css"/-->
    <link href="<?php echo ST; ?>global/plugins/simple-line-icons/simple-line-icons.min.css" rel="stylesheet" type="text/css"/>

    <!--link href="<?php //echo ST; ?>admin/layout4/css/layout.css" rel="stylesheet" type="text/css"/>
    link href="<?php //echo ST; ?>admin/layout4/css/themes/light.css" rel="stylesheet" type="text/css" id="style_color"/ -->

    <!--JQUERY-->

    <link href="/css/bootstrap.min.css" rel="stylesheet">
    <link href="/font-awesome/css/font-awesome.css" rel="stylesheet">
    <link href="/css/animate.css" rel="stylesheet">
    <link href="/css/plugins/jQueryUI/jquery-ui-1.10.4.custom.min.css" rel="stylesheet">
    <link href="/css/plugins/jqGrid/ui.jqgrid.css" rel="stylesheet">
    <!--<meta http-equiv="Content-type" content="text/html; charset=utf-8" />-->

    <!-- datatables -->
    <link href="/css/plugins/dataTables/datatables.min.css" rel="stylesheet">

    <!-- fin datatables -->

    <link href="/css/style.css" rel="stylesheet">

    <link rel="shortcut icon" href="favicon.ico"/>

    <!--<script src="/dist/app.js"></script>-->
    <style type="text/css">
        @media (max-width: 767px){
            .modal .modal-dialog .modal-content .modal-footer{
                text-align: left !important;
            }
        }
        
        .theme-config{
            display: none; 
        }

        .marquee {
          width: 70%;
          overflow: hidden;
          display: block;
          margin-top: 20px;
          position: absolute;
          left: 45%;
          top: 25%;
          transform: translate(-50%, -50%);
        }
    </style>
    
    <script src="/js/session.js"></script>
    <script></script>

</head>

<?php 
if($_SESSION['es_cliente'] != 0 && (strrpos($directoryURI, '/dashboard/resumen') !== false || strrpos($directoryURI, '/dashboard/inventario') !== false || strrpos($directoryURI, '/adminentrada/lists') !== false || strrpos($directoryURI, '/dashboard/monitoreo') !== false || 
  strrpos($directoryURI, '/embarques/lists') !== false || strrpos($directoryURI, '/adminentrada/lists') !== false || 
  strrpos($directoryURI, '/reportes/concentradoexistencia') !== false || strrpos($directoryURI, '/dashboard/inventario') !== false || 
  strrpos($directoryURI, '/incidencias/lists') !== false || strrpos($directoryURI, '/account/out') !== false || 
  strrpos($directoryURI, '/acercade') !== false || strrpos($directoryURI, '/reportes/existenciaubica') !== false || strrpos($directoryURI, '/articulos/cdigital') !== false || strrpos($directoryURI, '/nuevospedidos/lists') !== false || strrpos($directoryURI, '/kardex') !== false))
{
    //OK
}
else if($_SESSION['es_cliente'] != 0)
{
  echo "<meta http-equiv='refresh' content='0;URL=/account/out'\">";
  echo "<script>alert('URL NO PERMITIDA EN CUENTA DE CLIENTES');</script>";
  //session_destroy();
//header("Refresh:0");
}
$id_role = $_SESSION["perfil_usuario"]; // get the requested page

//#style="background-color: rgb(0, 15, 159);"
?>
  
    <body class="page-header-fixed page-sidebar-closed-hide-logo page-sidebar-closed-hide-logo">
      <nav class="navbar-default navbar-static-side" role="navigation">
        <div class="sidebar-collapse">
          <ul class="nav metismenu" id="side-menu">
            <li class="nav-header">
              <div class="dropdown profile-element" style="text-align: center;background-color: #fff;">
                <span>
                  <?php if(empty(!$user->image_url)):?>
                  <img src="<?php echo $empresaImg; ?>" ima="<?php echo $empresaImg; ?>" alt="Image preview" class="img-circle" id="current" style="max-width: 120px; max-height: 100px;border-radius: 9%;">
                  <?php else:?>
                  <img src="/img/profile_small.jpg" alt="Image preview" class="img-circle" id="current" style="max-width: 120px; max-height: 100px;border-radius: 9%;">
                  <?php endif;?>
                </span>
                <a data-toggle="dropdown" class="dropdown-toggle" href="#" style="color: #000;">
                  <span class="clear">
                    <span class="block m-t-xs">
                      <strong class="font-bold"><?php echo $user->nombre_completo; ?></strong>
                      <b class="caret"></b>
                      <ul class="dropdown-menu animated fadeInRight m-t-xs">
                        <li><a href="/perfil/lists">Perfil</a></li>
                        <li class="divider"></li>
                        <li><a href="/account/out">Cerrar Sesi&oacuten</a></li>
                      </ul>
                    </span>
                  </span>
                </a>
              </div>
              <div class="logo-element">
                  AW
              </div>
            </li>
            <?php
            //include '../../config.php';
            
            if (!empty($directoryURI) && strpos($directoryURI, ".ico")===false)
            {
              //toda esta seccion se utiliza para saber que menu esta activo
              $_SESSION["arrActive"] = array();
              $_SESSION["path"] = $directoryURI;

              $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
              //mysqli_set_charset($conn, 'utf8');
    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

              if($_SESSION["es_cliente"] == 0)
                 $sql_cliente = "es_cliente = 0";
              else if($_SESSION["es_cliente"] == 1)
                 $sql_cliente = "es_cliente = 3 OR es_cliente = 1";
              else if($_SESSION["es_cliente"] == 2)
              {
                $sql_prov = "SELECT DISTINCT IFNULL(Valor, '') as mostrar_solo_existencias_en_proveedores FROM t_configuraciongeneral WHERE cve_conf = 'mostrar_solo_existencias_en_proveedores'";
                if (!($res_prov = mysqli_query($conn, $sql_prov)))echo "Falló la preparación Prov: (" . mysqli_error($conn) . ") ";
                $mostrar_solo_existencias_en_proveedores = mysqli_fetch_array($res_prov)['mostrar_solo_existencias_en_proveedores'];

                 $sql_cliente = "es_cliente > 1";

                 if($mostrar_solo_existencias_en_proveedores == 1)
                 {
                    $sql_cliente = " es_cliente = 2 AND id_menu IN (235, 236, 260) ";
                 }
              }

              $sql = "SELECT * FROM t_menu WHERE href = '".$_SESSION["path"]."' AND ({$sql_cliente}) ORDER BY orden_screen ASC;";
              //$sql = "SELECT * FROM t_menu WHERE ({$sql_cliente}) ORDER BY orden_screen ASC;";
              //echo $sql."<br>";
              $rs = mysqli_query($conn, $sql);
              while ($row = mysqli_fetch_array($rs))
              {
                $id_menu = $row["id_menu"];
                $_SESSION["arrActive"][] = $id_menu;
                $id_menu_padre = $row["id_menu_padre"];
                $_SESSION["arrActive"][] = $id_menu_padre;
                $orden = $row["orden"];
              }
              if ($orden=="2")
              {
                $sql = "Select * from t_menu where id_menu = '".$id_menu_padre."' AND ({$sql_cliente}) ORDER BY orden_screen ASC;";
                //echo $sql."<br>";
                $rs = mysqli_query($conn, $sql);
                while ($row = mysqli_fetch_array($rs))
                {
                  $id_menu_padre = $row["id_menu_padre"];
                  $_SESSION["arrActive"][] = $id_menu_padre;
                }
              }

            }

            $sql = "SELECT * from t_menu where orden = '0' AND ({$sql_cliente}) ORDER BY orden_screen ASC;";
            //echo $sql."<br>";
            $res = mysqli_query($conn, $sql);

            $arr = array();
            $i = 0;
            //echo "<li>".$id_role." -- ".$row['id_menu']."</li>";//////////////////
            while ($row = mysqli_fetch_array($res))
            {
              //$sqlR = "SELECT * FROM t_profiles WHERE id_role = '".$id_role."' AND id_menu = '".$row['id_menu']."';";
              $sqlR = "SELECT p.id_perfil, p.id_menu, p.id_submenu, p.id_role, IFNULL((SELECT DISTINCT Activo FROM t_profiles pr WHERE pr.id_menu = (SELECT DISTINCT id_menu_padre FROM t_menu WHERE id_menu = ".$row['id_menu'].") AND pr.id_submenu = (SELECT id_submenu FROM t_submenu WHERE id_menu = pr.id_menu AND id_opciones = 1) AND pr.id_role = '".$id_role."'), 1) AS Activo FROM t_profiles p WHERE p.id_role = '".$id_role."' AND p.id_menu = '".$row['id_menu']."';";
/*
              $sqlR = "SELECT DISTINCT p.id_perfil, p.id_menu, p.id_submenu, p.id_role, IFNULL(pr.Activo, 0) AS Activo 
                      FROM t_profiles p 
                      LEFT JOIN t_menu m ON m.id_menu = p.id_menu
                      LEFT JOIN t_submenu sm ON sm.id_menu = m.id_menu_padre AND sm.id_opciones = 1
                      LEFT JOIN t_profiles pr ON pr.id_menu = sm.id_menu AND pr.id_submenu = sm.id_submenu AND pr.id_role = p.id_role
                      WHERE p.id_role = '".$id_role."' AND p.id_menu = '".$row['id_menu']."';";
*/
              //echo $sqlR."<br>";
              $resR = mysqli_query($conn, $sqlR);
              $mainP = mysqli_fetch_object($resR);
              if ($mainP->Activo=="1")
              {
              ?>
                <li <?php if (in_array($row['id_menu'], $_SESSION["arrActive"])) echo "class='active'"; ?>>
                  <a href="<?php echo $row['href']; ?>">
                    <i class="<?php echo $row["icono"]; ?>"></i>
                    <span class="nav-label"><?php echo trim(utf8_encode($row["modulo"])); ?></span>
                    <span class="fa arrow"></span>
                  </a>
                  <ul class="nav nav-second-level collapse">
                  <?php
                    $sql_sfa = "";
                    if($valor_sfa == 0)
                      $sql_sfa = " AND modulo != 'SFA' ";

                    $sql = "SELECT * from t_menu where orden = '1' and id_menu_padre='".$row['id_menu']."' AND ({$sql_cliente}) {$sql_sfa} ORDER BY orden_screen ASC;";
                    //echo $sql."<br>";
                    $res1 = mysqli_query($conn, $sql);
                    $arr = array();
                    $i = 0;
                    while ($row1 = mysqli_fetch_array($res1))
                    {
                      //if($row1['modulo'] == 'SFA' && $row1['href'] == 'SFA')
                      $sqlR = "SELECT * FROM t_profiles WHERE id_role = '".$id_role."' AND id_menu = '".$row1['id_menu']."';";
                      //$sqlR = "SELECT id_perfil, id_menu, id_submenu, id_role, IFNULL((SELECT DISTINCT Activo FROM t_profiles pr WHERE pr.id_menu = (SELECT DISTINCT id_menu_padre FROM t_menu WHERE id_menu = ".$row1['id_menu'].") AND pr.id_submenu = (SELECT id_submenu FROM t_submenu WHERE id_menu = pr.id_menu AND id_opciones = 1) AND pr.id_role = '".$id_role."'), 1) AS Activo FROM t_profiles WHERE id_role = '".$id_role."' AND id_menu = '".$row1['id_menu']."';";

                      //echo $sqlR."<br>";
                      $resR = mysqli_query($conn, $sqlR);
                      $main = mysqli_fetch_object($resR);
                      if ($main->Activo=="1")
                      {
                      ?>
                        <li <?php if (in_array($row1[ 'id_menu'], $_SESSION[ "arrActive"])) echo "class='active'"; ?>>
                        <?php
                          $sql2 = "SELECT * from t_menu where orden = '2' and id_menu_padre='".$row1['id_menu']."' AND ({$sql_cliente}) order by orden_screen ASC;";
                          //echo $sql2."<br>";
                          $res2 = mysqli_query($conn, $sql2);
                          $found = false;
                          if (mysqli_num_rows($res2)>0)
                            $found = true;
                        ?>
                          <a href="<?php echo $row1['href']; ?>" <?php if(substr($row1['href'], 0, 4) == 'http'){?> target="_blank" <?php } ?>>
                            <?php echo trim(utf8_encode($row1["modulo"])); ?>
                            <?php if ($found) { ?>
                              <span class="fa arrow"></span>
                            <?php } ?>
                          </a>
                          <?php
                          if ($found) { ?>
                          <ul class="nav nav-third-level collapse">
                          <?php
                          }
                            while ($row2 = mysqli_fetch_array($res2)) 
                            {
                              $sqlR = "SELECT * FROM t_profiles WHERE id_role = '" . $id_role . "' AND id_menu = '" . $row2['id_menu'] . "';";
                              //echo $sqlR."<br>";
                              $resR = mysqli_query($conn, $sqlR);
                              $main = mysqli_fetch_object($resR);
                              if ($main->Activo=="1") 
                              {
                              ?>
                                <li <?php if (in_array($row2[ 'id_menu'], $_SESSION[ "arrActive"])) echo "class='active'"; ?>>
                                  <a href="<?php echo $row2['href']; ?>">
                                    <?php echo trim(utf8_encode($row2["modulo"])); ?>
                                  </a>
                                </li>
                              <?php
                              }
                            }
                              if ($found) 
                              {
                                ?>
                                  </ul>
                                <?php 
                              } 
                                ?>
                                  </li>
                                <?php
                      } 
                    }
                      ?>
                          </ul>
                        </li>
                      <?php
              }
            }
                    ?>
                  </ul>
              </div>
        </nav>
        <div id="page-wrapper" class="gray-bg dashbard-1">
            <div class="row border-bottom">
                <nav class="navbar navbar-static-top" role="navigation" style="margin-bottom: 0; position: relative;">
                  <div class="navbar-header">
                      <a class="navbar-minimalize minimalize-styl-2 btn btn-primary " href="#"><i class="fa fa-bars"></i> </a>
                  </div>
                  <div class="marquee">
                      <?php 
                  $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

                  $query = mysqli_query($conn, "SELECT GROUP_CONCAT(mensaje SEPARATOR ', ') AS mensaje FROM t_mensaje WHERE fecha_final >= NOW() AND activo = 1;");
                  $mensaje = mysqli_fetch_assoc($query)['mensaje'];
                  //echo $mensaje;
                      ?>
                  </div>
                  <ul class="nav navbar-top-links navbar-right">
                    <li>
                      <span class="m-r-sm text-muted welcome-message"></span>
                    </li>
                    <li>
                      <a href="/account/out">
                          <i class="fa fa-sign-out"></i> Cerrar Sesi&oacuten
                      </a>
                    </li>
                  </ul>
                </nav>
            </div>
            <div class="page-content-wrapper">
                <div class="page-content">


<?php 
    $sql_permisos = "
    SELECT  
      p.id_menu,
      GROUP_CONCAT(DISTINCT IF(s.id_opciones = 1, p.Activo, ''), IF(s.id_opciones = 2, p.Activo, ''), IF(s.id_opciones = 3, p.Activo, ''), IF(s.id_opciones = 4, p.Activo, '') ORDER BY s.id_opciones SEPARATOR '-' ) AS permisos
      FROM t_profiles p 
      LEFT JOIN t_submenu s ON s.id_submenu = p.id_submenu AND p.id_menu = p.id_menu
      LEFT JOIN t_menu m ON m.id_menu = p.id_menu AND m.es_cliente = 0
      WHERE p.id_role = {$id_role} AND m.href = '$directoryURI'
      GROUP BY id_menu
      ORDER BY s.id_opciones";
    if (!($res_permisos = mysqli_query($conn, $sql_permisos)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $permisos_crud = mysqli_fetch_array($res_permisos)['permisos'];
    $arr_permisos_crud = explode("-", $permisos_crud);

    $permiso_consultar = $arr_permisos_crud[0];
    $permiso_registrar = $arr_permisos_crud[1];
    $permiso_editar    = $arr_permisos_crud[2];
    $permiso_eliminar  = $arr_permisos_crud[3];
/*
    echo $directoryURI;
    echo "<br>";
    echo "permiso_consultar = ".$permiso_consultar;
    echo "<br>";
    echo "permiso_registrar = ".$permiso_registrar;
    echo "<br>";
    echo "permiso_editar = ".$permiso_editar;
    echo "<br>";
    echo "permiso_eliminar = ".$permiso_eliminar;
    echo "<br>";
*/
    $sql_decimales = "SELECT IF(COUNT(*) = 0, 3, Valor) AS Valor FROM t_configuraciongeneral WHERE cve_conf = 'decimales_cantidad'";
    if (!($res_decimales = mysqli_query($conn, $sql_decimales)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $decimales_cantidad = mysqli_fetch_array($res_decimales)['Valor'];

    $sql_decimales = "SELECT IF(COUNT(*) = 0, 3, Valor) AS Valor FROM t_configuraciongeneral WHERE cve_conf = 'decimales_costo'";
    if (!($res_decimales = mysqli_query($conn, $sql_decimales)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $decimales_costo = mysqli_fetch_array($res_decimales)['Valor'];

?>
<input type="hidden" id="permiso_consultar" name="permiso_consultar" value="<?php echo $permiso_consultar; ?>">
<input type="hidden" id="permiso_registrar" name="permiso_registrar" value="<?php echo $permiso_registrar; ?>">
<input type="hidden" id="permiso_editar" name="permiso_editar" value="<?php echo $permiso_editar; ?>">
<input type="hidden" id="permiso_eliminar" name="permiso_eliminar" value="<?php echo $permiso_eliminar; ?>">

<input type="hidden" id="decimales_cantidad" name="decimales_cantidad" value="<?php echo $decimales_cantidad; ?>">
<input type="hidden" id="decimales_costo" name="decimales_costo" value="<?php echo $decimales_costo; ?>">


