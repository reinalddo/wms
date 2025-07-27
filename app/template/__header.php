<?php
  session_start();
  $user = new \User\User( ID_USER );
  $directoryURI = $_SERVER['REQUEST_URI'];
  $path = parse_url($directoryURI, PHP_URL_PATH);
  $components = explode('/', $path);
  $first_part = $components[1];
  //$_SESSION['id_user'] = $user->id_user;
  //$_SESSION['name'] = $user->name;
?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en" class="no-js">
<!--<![endif]-->
  <head>

    <meta charset="utf-8"/>

    <title><?php echo SITE_TITLE; ?></title>

    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1" name="viewport"/>
    <meta content="" name="description"/>
    <meta content="" name="author"/>

    <link href="http://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=all" rel="stylesheet" type="text/css"/>
    <!--link href="<?php echo ST; ?>global/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css"/-->
    <link href="<?php echo ST; ?>global/plugins/simple-line-icons/simple-line-icons.min.css" rel="stylesheet" type="text/css"/>

    <!--link href="<?php echo ST; ?>admin/layout4/css/layout.css" rel="stylesheet" type="text/css"/>
    <!--link href="<?php echo ST; ?>admin/layout4/css/themes/light.css" rel="stylesheet" type="text/css" id="style_color"/-->

<!--JQUERY-->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
    <link href="/css/bootstrap.min.css" rel="stylesheet">
    <link href="/font-awesome/css/font-awesome.css" rel="stylesheet">
    <link href="/css/animate.css" rel="stylesheet">
    <link href="/css/plugins/jQueryUI/jquery-ui-1.10.4.custom.min.css" rel="stylesheet">
    <link href="/css/plugins/jqGrid/ui.jqgrid.css" rel="stylesheet">

    <!-- datatables -->
    <link href="/css/plugins/dataTables/datatables.min.css" rel="stylesheet">

    <!-- fin datatables -->

    <link href="/css/style.css" rel="stylesheet">

    <link rel="shortcut icon" href="favicon.ico"/>
    <script src="/js/session.js"></script>

  </head>
  <body class="page-header-fixed page-sidebar-closed-hide-logo page-sidebar-closed-hide-logo">

    <nav class="navbar-default navbar-static-side" role="navigation">
            <div class="sidebar-collapse">
                <ul class="nav metismenu" id="side-menu">
                    <li class="nav-header">
                        <div class="dropdown profile-element"> <span>
                            <img alt="image" class="img-circle" src="/img/profile_small.jpg" />
                             </span>
                            <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                            <span class="clear"> <span class="block m-t-xs"> <strong class="font-bold"><?php echo $_SESSION['name']; ?></strong><b class="caret"></b>
                            <!-- </span> <span class="text-muted text-xs block"><?php echo $_SESSION["name"] ?><b class="caret"></b></span> </span> </a> -->
                            <ul class="dropdown-menu animated fadeInRight m-t-xs">
                                <li><a href="/perfil/lists">Perfil</a></li>
                                <li class="divider"></li>
                                <li><a href="login.html">Log out</a></li>
                            </ul>
                        </div>
                        <div class="logo-element">
                            AW
                        </div>
                    </li>
                    <li <?php if (($_GET["m"]== "2")) { echo "class='active'"; }?>>
                        <a href="#"><i class="fa fa-user"></i> <span class="nav-label">Dashboard</span><span class="fa arrow"></span></a>
                        <ul class="nav nav-second-level collapse">
                            <li <?php if ($_GET["m"]== "a") { echo "class='active'"; }?>><a href="#">Resumen Ejectuvo <span class="fa arrow"></span></a>
                                <ul class="nav nav-third-level">
                                    <li class=""><a href="#"><i class=" entypo-doc-text"></i>Número de Almacenes</a></li>
                                    <li class=""><a href="#"><i class=" entypo-doc-text"></i>Número de Ubicaciones<span class="fa arrow"></span></a>
                                        <ul class="nav nav-for-level">
                                            <li class=""><a href="#"><i class=" entypo-doc-text"></i>Porcentaje de ocupación Vacías</a>
                                        </ul>
                                    </li>
                                    <li class=""><a href="#"><i class=" entypo-doc-text"></i>Número de Operadores en línea</a></li>
                                    <li class=""><a href="#"><i class=" entypo-doc-text"></i>Número de Ordenes de Entrada</a></li>
                                    <li class=""><a href="#"><i class=" entypo-doc-text"></i>Número de Pedidos</a></li>
                                    <li class=""><a href="#"><i class=" entypo-doc-text"></i>Número de embarques efectuados</a></li>
                                </ul>
                            </li>
                            <li <?php if ($_GET["m"]== "a") { echo "class='active'"; }?>><a href="#">Inventario<span class="fa arrow"></span></a>
                                <ul class="nav nav-third-level">
                                    <li class=""><a href="#"><i class=" entypo-doc-text"></i>Productos de mayor movimiento</a></li>
                                    <li class=""><a href="#"><i class=" entypo-doc-text"></i>Productos de lento movimiento</a></li>
                                    <li class=""><a href="#"><i class=" entypo-doc-text"></i>Lotes próximos a caducar</a></li>
                                </ul>
                            </li>

                            <li <?php if ($_GET["m"]== "2") { echo "class='active'"; }?>><a href="#">Visor de surtido</span></a></li>
                            <li <?php if ($_GET["m"]== "2") { echo "class='active'"; }?>><a href="#">Embarques</a></li>
                            <li <?php if ($_GET["m"]== "2") { echo "class='active'"; }?>><a href="#">Productividad</span></a></li>

                        </ul>
                    </li>

                    <li <?php if (($_GET["m"]== "3") or ($_GET["m"]== "4") or ($_GET["m"]== "5") or ($_GET["m"]== "6") or ($_GET["m"]== "7")) { echo "class='active'"; }?>>
                        <a href="#"><i class="fa fa-bar-chart-o"></i> <span class="nav-label">Administraci&oacute;n</span><span class="fa arrow"></span></a>
                        <ul class="nav nav-second-level collapse">
                            <li class="<?php echo $first_part=='roles' ? 'active' : ''; ?>"><a href="#empresas"><i class=" entypo-doc-text"></i>Empresas</a></li>
                            <li <?php if ($_GET["m"]== "3") { echo "class='active'"; }?>><a href="#">Almacenes <span class="fa arrow"></span></a>
                                <ul class="nav nav-third-level">
                            		<li class="<?php echo $first_part=='tipocompania' ? 'active' : ''; ?>"><a href="/tipocompania/lists?m=3"><i class=" entypo-doc-text"></i>Tipo de Compañías</a></li>
		                            <li class="<?php echo $first_part=='companias' ? 'active' : ''; ?>"><a href="/companias/lists?m=3"><i class=" entypo-doc-text"></i>Compañías</a></li>
                                    <li class="<?php $menu2 = true; echo $first_part=='almacen' ? 'active' : ''; ?>"><a href="/almacen/lists?m=3"><i class=" entypo-doc-text"></i>Almacenes</a></li>
                                </ul>
                            </li>
                            <li class="<?php echo $first_part=='roles' ? 'active' : ''; ?>"><a href="/roles/lists?m=4"><i class=" entypo-doc-text"></i>Perfiles</a></li>
                            <!-- <li class="<//?php echo $first_part=='perfiles' ? 'active' : ''; ?>"><a href="#"><i class=" entypo-doc-text"></i>Perfiles</a></li> -->
                            <li class="<?php echo $first_part=='usuarios' ? 'active' : ''; ?>"><a href="/usuarios/lists?m=4"><i class=" entypo-doc-text"></i>Usuarios</a></li>

                        </ul>
                    </li>

                    <li <?php if (($_GET["m"]== "8") or ($_GET["m"]== "9") or ($_GET["m"]== "10") or ($_GET["m"]== "11")) { echo "class='active'"; }?>>
                        <a href="#"><i class="fa fa-book"></i> <span class="nav-label">Catálogos</span><span class="fa arrow"></span></a>
                        <ul class="nav nav-second-level collapse">
                            <li class="<?php echo $first_part=='clientes' ? 'active' : ''; ?>"><a href="/clientes/lists?m=8"><i class=" entypo-doc-text"></i>Clientes</a></li>
                            <li class="<?php echo $first_part=='proveedores' ? 'active' : ''; ?>"><a href="/proveedores/lists?m=8"><i class=" entypo-doc-text"></i>Proveedores</a></li>
                            <li class="<?php echo $first_part=='poblaciones' ? 'active' : ''; ?>"><a href="/poblaciones/lists?m=8"><i class=" entypo-doc-text"></i>Poblaciones</a></li>
                            <li class="<?php echo $first_part=='ruta' ? 'active' : ''; ?>"><a href="/ruta/lists?m=8"><i class=" entypo-doc-text"></i>Rutas</a></li>
                            <li class="<?php echo $first_part=='transporte' ? 'active' : ''; ?>"><a href="/transporte/lists?m=8"><i class=" entypo-doc-text"></i>Transportes</a></li>

                            <li <?php if ($_GET["m"]== "10") { echo "class='active'"; }?>><a href="#">Productos <span class="fa arrow"></span></a>
                                <ul class="nav nav-third-level">
                                    <li class="<?php echo $first_part=='articulos' ? 'active' : ''; ?>"><a href="/articulos/lists?m=10"><i class=" entypo-doc-text"></i>Artículos</a></li>
                                    <li class="<?php echo $first_part=='grupoarticulos' ? 'active' : ''; ?>"><a href="/grupoarticulos/list?m=10"><i class=" entypo-doc-text"></i>Grupos de Articulos</a></li>
		                            <li class="<?php echo $first_part=='subgrupodearticulos' ? 'active' : ''; ?>"><a href="/subgrupodearticulos/list?m=10"><i class=" entypo-doc-text"></i>SubGrupo de Articulos</a></li>
		                            <li class="<?php echo $first_part=='ssubgrupodearticulos' ? 'active' : ''; ?>"><a href="/ssubgrupodearticulos/list?m=10"><i class=" entypo-doc-text"></i>SubsubGrupos de Artículos</a></li>
		                            <li class="<?php echo $first_part=='lotes' ? 'active' : ''; ?>"><a href="/lotes/lists?m=10"><i class=" entypo-doc-text"></i>Lotes</a></li>
                                    <li class="<?php echo $first_part=='unidadesmedida' ? 'active' : ''; ?>"><a href="/unidadesmedida/lists?m=10"><i class=" entypo-doc-text"></i>Unidades de Medida</a></li>
                                    <li class="<?php echo $first_part=='tipocaja' ? 'active' : ''; ?>"><a href="/tipocaja/list?m=10"><i class=" entypo-doc-text"></i>Tipos de Cajas</a></li>

                                </ul>
                            </li>
                            <li <?php if ($_GET["m"]== "11") { echo "class='active'"; }?>><a href="#">Configuración Almacén <span class="fa arrow"></span></a>
                                <ul class="nav nav-third-level">

                                    <li class="<?php echo $first_part=='ubicacionalmacenaje' ? 'active' : ''; ?>"><a href="/ubicacionalmacenaje/lists?m=3"><i class=" entypo-doc-text"></i>Ubicacion de Almacenaje</a></li>

                                    <li class="<?php echo $first_part=='arearevision' ? 'active' : ''; ?>"><a href="/arearevision/lists?m=11"><i class=" entypo-doc-text"></i>Areas de Revisión</a></li>
                                    <li><a href="#"><i class=" entypo-doc-text"></i>Cortinas</a></li>
                                    <li><a href="#"><i class=" entypo-doc-text"></i>Areas de embarque</a></li>
                                </ul>
                            </li>
                            <li class="<?php echo $first_part=='contenedores' ? 'active' : ''; ?>"><a href="/contenedores/lists?m=9"><i class=" entypo-doc-text"></i>Contenedores</a></li>
                            <li><a href="#"><i class=" entypo-doc-text"></i>Protocolos de entrada</a></li>
                            <li class="<?php echo $first_part=='tipodeprioridad' ? 'active' : ''; ?>"><a href="/tipodeprioridad/lists?m=9"><i class=" entypo-doc-text"></i>Tipo de Prioridad</a></li>
                            <li class="<?php echo $first_part=='incidencias' ? 'active' : ''; ?>"><a href="/incidencias/lists?m=9"><i class=" entypo-doc-text"></i>Incidencias</a></li>
                            <li class="<?php echo $first_part=='motivodevolucion' ? 'active' : ''; ?>"><a href="/motivodevolucion/list?m=9"><i class=" entypo-doc-text"></i>Motivo de Devolución</a></li>

                        </ul>
                    </li>
                    <li <?php if (($_GET["m"]== "13") or ($_GET["m"]== "14") or ($_GET["m"]== "15") or ($_GET["m"]== "16") or ($_GET["m"]== "17")) { echo "class='active'"; }?>>
                        <a href="#"><i class="fa fa-book"></i> <span class="nav-label">Procesos</span><span class="fa arrow"></span></a>

                        <ul class="nav nav-second-level collapse">
                            <li class="<?php echo $first_part=='clientes' ? 'active' : ''; ?>"><a href="#"><i class=" entypo-doc-text"></i>Asignación de usuarios a Almacenes</a></li>

                            <li <?php if ($_GET["m"]== "14") { echo "class='active'"; }?>><a href="#">Entradas <span class="fa arrow"></span></a>
                                <ul class="nav nav-third-level">
                                    <li><a href="#"><i class=" entypo-doc-text"></i>Administraci&oacute;n</a></li>
                                    <li><a href="#"><i class=" entypo-doc-text"></i>Ajustes</a></li>
                                    <li class="<?php echo $first_part=='ordendecompra' ? 'active' : ''; ?>"><a href="/ordendecompra/lists?m=14"><i class=" entypo-doc-text"></i>Ordenes de Compra</a></li>
                                </ul>
                            </li>
                            <li <?php if ($_GET["m"]== "15") { echo "class='active'"; }?>><a href="#">Picking <span class="fa arrow"></span></a>
                                <ul class="nav nav-third-level">
                                    <li><a href="#"><i class=" entypo-doc-text"></i>Admnistración</a></li>
                                    <li><a href="#"><i class=" entypo-doc-text"></i>Registro</a></li>
                                    <li><a href="#"><i class=" entypo-doc-text"></i>Edición</a></li>
                                    <li class="<?php echo $first_part=='tipodeprioridad' ? 'active' : ''; ?>"><a href="/tipodeprioridad/lists?m=15"><i class=" entypo-doc-text"></i>Prioridad</a></li>
                                    <li><a href="#"><i class=" entypo-doc-text"></i>Acuse</a></li>
                                    <li class="<?php echo $first_part=='rutasdesurtido' ? 'active' : ''; ?>"><a href="/rutasdesurtido/lists?m=15"><i class=" entypo-doc-text"></i>Rutas de Surtido</a></li>
                                </ul>
                            </li>

                            <li <?php if ($_GET["m"]== "16") { echo "class='active'"; }?>><a href="#">Inventarios <span class="fa arrow"></span></a>
                                <ul class="nav nav-third-level">

                                    <li><a href="#"><i class=" entypo-doc-text"></i>Físico</a></li>
                                    <li><a href="#"><i class=" entypo-doc-text"></i>Cíclico</a></li>
                                    <li><a href="#"><i class=" entypo-doc-text"></i>Ajustes de Existencia</a></li>
                                    <li class="<?php echo $first_part=='maximosyminimos' ? 'active' : ''; ?>"><a href="/maximosyminimos/lists?m=16"><i class=" entypo-doc-text"></i>Maximos y Minimos</a></li>

                                </ul>
                            </li>

                            <li <?php if ($_GET["m"]== "17") { echo "class='active'"; }?>><a href="#">Embarques <span class="fa arrow"></span></a>
                                <ul class="nav nav-third-level">
                                    <li class="<?php echo $first_part=='embarques' ? 'active' : ''; ?>"><a href="/embarques/lists?m=17"><i class=" entypo-doc-text"></i>Administrar Embarques</a></li>
                                    <li><a href="#"><i class=" entypo-doc-text"></i>Delivery</a></li>
                                </ul>
                            </li>

                            <li class="<?php echo $first_part=='motivodevolucion' ? 'active' : ''; ?>"><a href="#"><i class=" entypo-doc-text"></i>Kitting</a></li>

                        </ul>
                    </li>
                    <li <?php if (($_GET["m"]== "18") ) { echo "class='active'"; }?>>
                        <a href="#"><i class="fa fa-files-o"></i> <span class="nav-label">Reportes</span></a>

                    </li>
                    <li <?php if (($_GET["m"]== "19") ) { echo "class='active'"; }?>>
                        <a href="#"><i class="fa fa-files-o"></i> <span class="nav-label">Utileria</span><span class="fa arrow"></span></a>
                        <ul class="nav nav-third-level">
                            <li class="<?php echo $first_part=='ubicaciones' ? 'active' : ''; ?>"><a href="/ubicaciones/lists?m=19"><i class=" entypo-doc-text"></i>Impresión de Ubicaciones</a></li>
                            <li><a href="#"><i class=" entypo-doc-text"></i>Licencias</a></li>
                        </ul>

                    </li>
                </ul>
            </div>
        </nav>

		<div id="page-wrapper" class="gray-bg dashbard-1">
        <div class="row border-bottom">
        <nav class="navbar navbar-static-top" role="navigation" style="margin-bottom: 0">
        <div class="navbar-header">
            <a class="navbar-minimalize minimalize-styl-2 btn btn-primary " href="#"><i class="fa fa-bars"></i> </a>
        </div>
            <ul class="nav navbar-top-links navbar-right">
                <li>
                    <span class="m-r-sm text-muted welcome-message"></span>
                </li>

                <li>
                    <a href="/account/out">
                        <i class="fa fa-sign-out"></i> Log out
                    </a>
                </li>
            </ul>
        </nav>
        </div>

      <div class="page-content-wrapper">
        <div class="page-content">