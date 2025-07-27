<?php
  session_start();
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

  </head>
  <body class="page-header-fixed page-sidebar-closed-hide-logo page-sidebar-closed-hide-logo">

    <nav class="navbar-default navbar-static-side" role="navigation">
            <div class="sidebar-collapse">
                <ul class="nav metismenu" id="side-menu">
                    <li class="nav-header">
                        <div class="dropdown profile-element"> <span>
						<?php if(empty(!$user->image_url)):?>
                            <img src="<?php echo "/img/imageperfil/".$user->image_url; ?>" ima=<?php echo $user->image_url; ?> alt="Image preview" class="img-circle" id="current" style="max-width: 52px; max-height: 52px">
                        <?php else:?>
                            <img src="/img/profile_small.jpg" alt="Image preview" class="img-circle" id="current" style="max-width: 52px; max-height: 52px">
                        <?php endif;?>
                             </span>
                            <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                            <span class="clear"> <span class="block m-t-xs"> <strong class="font-bold"><?php echo $_SESSION['name']; ?></strong><b class="caret"></b>
                            <!-- </span> <span class="text-muted text-xs block"><?php echo $_SESSION["name"] ?><b class="caret"></b></span> </span> </a> -->
                            <ul class="dropdown-menu animated fadeInRight m-t-xs">
                                <li><a href="/perfil/lists">Perfil</a></li>
                                <li class="divider"></li>
                                <li><a href="/account/out">Cerrar Sesi&oacuten</a></li>
                            </ul>
                        </div>
                        <div class="logo-element">
                            AW
                        </div>
                    </li>
                    <li <?php if ($_GET["m"]== "dh" | $_GET["m"]== "dr" | $_GET["m"]== "di") { echo "class='active'"; }?>>
                        <a href="#"><i class="fa fa-user"></i> <span class="nav-label">Dashboard</span><span class="fa arrow"></span></a>
                        <ul class="nav nav-second-level collapse">
                            <li <?php if ($_GET["m"]== "dh") { echo "class='active'"; }?>><a href="/?m=dh">Home</a>
                            </li>
                            <li <?php if ($_GET["m"]== "dr") { echo "class='active'"; }?>><a href="/dashboard/resumen?m=dr">Resumen Ejecutivo</a>
                            </li>
                            <li <?php if ($_GET["m"]== "di") { echo "class='active'"; }?>><a href="/dashboard/inventario?m=di">Inventario</a>
                            </li>
                        </ul>
                    </li>

                    <li <?php if ($_GET["m"] >= 1 && $_GET["m"] <= 6): echo "class='active'"; endif; ?> >
                        <a href="#"><i class="fa fa-bar-chart-o"></i> <span class="nav-label">Administraci&oacute;n</span><span class="fa arrow"></span></a>
                        <ul class="nav nav-second-level collapse">
                            <li <?php if ($_GET["m"]== "1" || $_GET["m"]== "2") { echo "class='active'"; }?>>
                                <a href="#">
                                    Empresas
                                    <span class="fa arrow"></span>
                                </a>
                                <ul class="nav nav-third-level collapse">
                                    <li <?php if ($_GET["m"]== "1") { echo "class='active'"; }?>>
                                        <a href="/companias/lists?m=1">Empresas</a>
                                    </li>
                                    <li <?php if ($_GET["m"]== "2") { echo "class='active'"; }?>>
                                        <a href="/tipocompania/lists?m=2">Tipos de Empresas</a>
                                    </li>
                                </ul>
                            </li>
                            <li <?php if ($_GET["m"]== "3" || $_GET["m"]== "4") { echo "class='active'"; }?>>
                                <a href="#">
                                    Almacenes
                                    <span class="fa arrow"></span>
                                </a>
                                <ul class="nav nav-third-level collapse">
                                    <li <?php if ($_GET["m"]== "3") { echo "class='active'"; }?>>
                                        <a href="/almacenp/lists?m=3">Almacenes</a>
                                    </li>
									<li <?php if ($_GET["m"]== "48") { echo "class='active'"; }?>>
                                        <a href="/tipoalmacen/lists?m=48">Tipo de AlmacÃ©n</a>
                                    </li>
                                    <li <?php if ($_GET["m"]== "4") { echo "class='active'"; }?>>
                                        <a href="/almacen/lists?m=4">Zonas de Almacenajes</a>
                                    </li>
									 
                                </ul>
                            </li>
                            <li <?php if ($_GET["m"]== "5" || $_GET["m"]== "6") { echo "class='active'"; }?>>
                                <a href="#">
                                    Usuarios
                                    <span class="fa arrow"></span>
                                </a>
                                <ul class="nav nav-third-level collapse">
                                    <li <?php if ($_GET["m"]== "5") { echo "class='active'"; }?>>
                                        <a href="/usuarios/lists?m=5">Usuarios</a>
                                    </li>
                                    <li <?php if ($_GET["m"]== "6") { echo "class='active'"; }?>>
                                        <a href="/roles/lists?m=6">Roles</a>
                                    </li>
                                    <li <?php if ($_GET["m"]== "50") { echo "class='active'"; }?>>
                                        <a href="/perfil/lists?m=50">Perfiles</a>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </li>

                    <li <?php if ($_GET["m"] >= 7 && $_GET["m"] <= 26): echo "class='active'"; endif; ?>>
                        <a href="#"><i class="fa fa-book"></i> <span class="nav-label">CatÃ¡logos</span><span class="fa arrow"></span></a>
                        <ul class="nav nav-second-level collapse">
                            <li <?php if ($_GET["m"]== "7") { echo "class='active'"; }?>><a href="/clientes/lists?m=7"><i class=" entypo-doc-text"></i>Clientes</a></li>
                            <li <?php if ($_GET["m"]== "8") { echo "class='active'"; }?>><a href="/proveedores/lists?m=8"><i class=" entypo-doc-text"></i>Proveedores</a></li>
                            <li <?php if ($_GET["m"]== "9") { echo "class='active'"; }?>><a href="/ruta/lists?m=9"><i class=" entypo-doc-text"></i>Rutas</a></li>
                            
							  <li <?php if ($_GET["m"] >= 80 && $_GET["m"] <= 81): echo "class='active'"; endif; ?>><a href="#">Transportes <span class="fa arrow"></span></a>
							    <ul class="nav nav-third-level">
								<li <?php if ($_GET["m"]== "81") { echo "class='active'"; }?>><a href="/tipotransporte/lists?m=81"><i class=" entypo-doc-text"></i>Tipo de Transportes</a></li>
								<li <?php if ($_GET["m"]== "80") { echo "class='active'"; }?>><a href="/transporte/lists?m=80"><i class=" entypo-doc-text"></i>Transportes</a></li>
								
                                </ul>
                            </li>
                            <li <?php if ($_GET["m"] >= 11 && $_GET["m"] <= 17): echo "class='active'"; endif; ?>><a href="#">Productos <span class="fa arrow"></span></a>
                                <ul class="nav nav-third-level">
                                    <li <?php if ($_GET["m"]== "11") { echo "class='active'"; }?>><a href="/articulos/lists?m=11"><i class=" entypo-doc-text"></i>ArtÃ­culos</a></li>
                                    <li <?php if ($_GET["m"]== "12") { echo "class='active'"; }?>><a href="/grupoarticulos/list?m=12"><i class=" entypo-doc-text"></i>Grupos</a></li>
		                            <li <?php if ($_GET["m"]== "13") { echo "class='active'"; }?>><a href="/subgrupodearticulos/list?m=13"><i class=" entypo-doc-text"></i>ClasificaciÃ³n</a></li>
		                            <li <?php if ($_GET["m"]== "14") { echo "class='active'"; }?>><a href="/ssubgrupodearticulos/list?m=14"><i class=" entypo-doc-text"></i>Tipos de ArtÃ­culos</a></li>
		                            <li <?php if ($_GET["m"]== "15") { echo "class='active'"; }?>><a href="/lotes/lists?m=15"><i class=" entypo-doc-text"></i>Lotes</a></li>
                                    <li <?php if ($_GET["m"]== "16") { echo "class='active'"; }?>><a href="/unidadesmedida/lists?m=16"><i class=" entypo-doc-text"></i>Unidades de Medida</a></li>
                                    <li <?php if ($_GET["m"]== "17") { echo "class='active'"; }?>><a href="/tipocaja/list?m=17"><i class=" entypo-doc-text"></i>Tipos de Cajas</a></li>

                                </ul>
                            </li>
                            <li <?php if ($_GET["m"] >= 18 && $_GET["m"] <= 21): echo "class='active'"; endif; ?>><a href="#">ConfiguraciÃ³n AlmacÃ©n <span class="fa arrow"></span></a>
                                <ul class="nav nav-third-level">

                                    <li <?php if ($_GET["m"]== "18") { echo "class='active'"; }?>><a href="/ubicacionalmacenaje/lists?m=18"><i class=" entypo-doc-text"></i>Ubicaciones de Almacenaje</a></li>

                                    <li <?php if ($_GET["m"]== "19") { echo "class='active'"; }?>><a href="/arearevision/lists?m=19"><i class=" entypo-doc-text"></i>Areas de RevisiÃ³n</a></li>
                                    <li <?php if ($_GET["m"]== "20") { echo "class='active'"; }?>><a href="/cortinaentrada/lists?m=20"><i class=" entypo-doc-text"></i>Areas de RecepciÃ³n</a></li>
                                    <li <?php if ($_GET["m"]== "21") { echo "class='active'"; }?>><a href="/areaembarque/lists?m=21"><i class=" entypo-doc-text"></i>Areas de embarque</a></li>
                                </ul>
                            </li>
                            <li <?php if ($_GET["m"]== "22") { echo "class='active'"; }?>><a href="/contenedores/lists?m=22"><i class=" entypo-doc-text"></i>Pallet y Contenedores</a></li>
                            <li <?php if ($_GET["m"]== "23") { echo "class='active'"; }?>><a href="/protocolos/lists?m=23"><i class=" entypo-doc-text"></i>Protocolos de entrada</a></li>
                            <li <?php if ($_GET["m"]== "24") { echo "class='active'"; }?>><a href="/tipodeprioridad/lists?m=24"><i class=" entypo-doc-text"></i>Tipo de Prioridad</a></li>
                            
                            <li <?php if ($_GET["m"]== "26") { echo "class='active'"; }?>><a href="/motivodevolucion/list?m=26"><i class=" entypo-doc-text"></i>Motivo de DevoluciÃ³n</a></li>

                        </ul>
                    </li>
                    <li <?php if ($_GET["m"] >= 27 && $_GET["m"] <= 43): echo "class='active'"; endif; ?>>
                        <a href="#"><i class="fa fa-cubes"></i> <span class="nav-label">Procesos</span><span class="fa arrow"></span></a>

                        <ul class="nav nav-second-level collapse">
                            <li <?php if ($_GET["m"] >= 27 && $_GET["m"] <= 28): echo "class='active'"; endif; ?>><a href="#">AsignaciÃ³n Usuario AlmacÃ©n <span class="fa arrow"></span></a>
                                <ul class="nav nav-third-level">
                                    <li <?php if ($_GET["m"]== "27") { echo "class='active'"; }?>><a href="/asignacionusuariosalmacen/lists?m=27"><i class=" entypo-doc-text"></i>Por AlmacÃ©n </a></li>
                                    <li <?php if ($_GET["m"]== "28") { echo "class='active'"; }?>><a href="/asignacionalmacenesusuarios/lists?m=28"><i class=" entypo-doc-text"></i>Por Usuario</a></li>
                                </ul>
                            </li>

                            <li <?php if ($_GET["m"] >= 29 && $_GET["m"] <= 31): echo "class='active'"; endif; ?>><a href="#">Entradas <span class="fa arrow"></span></a>
                                <ul class="nav nav-third-level">
								<li <?php if ($_GET["m"]== "31") { echo "class='active'"; }?>><a href="/ordendecompra/lists?m=31"><i class=" entypo-doc-text"></i>Ordenes de Compra</a></li>
								<li <?php if ($_GET["m"]== "29") { echo "class='active'"; }?>><a href="/adminentrada/lists?m=29"><i class=" entypo-doc-text"></i>AdministraciÃ³n</a></li>
								
						    	<li <?php if ($_GET["m"]== "49") { echo "class='active'"; }?>><a href="/recepcionoc/lists?m=49"><i class=" entypo-doc-text"></i>RecepciÃ³n de Materiales</a></li>
                                <li <?php if ($_GET["m"]== "30") { echo "class='active'"; }?>><a href="#"><i class=" entypo-doc-text"></i>Ajustes</a></li>
                                    
                                </ul>
                            </li>
                            <li>
                                <a href="/acomodo/lists">Put Away</a>
                            </li>
                            <li <?php if ($_GET["m"] >= 32 && $_GET["m"] <= 37): echo "class='active'"; endif; ?>><a href="#">Picking <span class="fa arrow"></span></a>
                                <ul class="nav nav-third-level">
                                    <li <?php if ($_GET["m"]== "32") { echo "class='active'"; }?>><a href="/administradorpedidos/lists"><i class=" entypo-doc-text"></i>AdmnistraciÃ³n de Pedidos</a></li>
                                    <li <?php if ($_GET["m"]== "33") { echo "class='active'"; }?>><a href="#"><i class=" entypo-doc-text"></i>Registro</a></li>
                                    <li <?php if ($_GET["m"]== "34") { echo "class='active'"; }?>><a href="#"><i class=" entypo-doc-text"></i>EdiciÃ³n</a></li>
                                    <li <?php if ($_GET["m"]== "36") { echo "class='active'"; }?>><a href="#"><i class=" entypo-doc-text"></i>Acuse</a></li>
                                    <li <?php if ($_GET["m"]== "37") { echo "class='active'"; }?>><a href="/rutasdesurtido/lists?m=37"><i class=" entypo-doc-text"></i>Rutas de Surtido</a></li>
                                </ul>
                            </li>
    
							<li <?php if ($_GET["m"]== "35") { echo "class='active'"; }?>><a href="/qaauditoria/lists?m=35">QA AuditorÃ­a</a></li>

                            <li <?php if ($_GET["m"] >= 38 && $_GET["m"] <= 41): echo "class='active'"; endif; ?>><a href="#">Inventarios <span class="fa arrow"></span></a>
                                <ul class="nav nav-third-level">

                                    <li <?php if ($_GET["m"]== "38") { echo "class='active'"; }?>><a href="/inventariosfisicos/lists?m=38"><i class=" entypo-doc-text"></i>FÃ­sico</a></li>
                                    <li <?php if ($_GET["m"]== "39") { echo "class='active'"; }?>><a href="/inventariosciclicos/lists?m=39"><i class=" entypo-doc-text"></i>CÃ­clico</a></li>
                                    <li <?php if ($_GET["m"]== "40") { echo "class='active'"; }?>><a href="#"><i class=" entypo-doc-text"></i>Ajustes de Existencia</a></li>
                                    <li <?php if ($_GET["m"]== "41") { echo "class='active'"; }?>><a href="/maximosyminimos/lists?m=41"><i class=" entypo-doc-text"></i>Maximos y Minimos</a></li>

                                </ul>
                            </li>

                            <li <?php if ($_GET["m"] >= 42 && $_GET["m"] <= 43): echo "class='active'"; endif; ?>><a href="#">Embarques <span class="fa arrow"></span></a>
                                <ul class="nav nav-third-level">
                                    <li <?php if ($_GET["m"]== "42") { echo "class='active'"; }?>><a href="/embarques/lists?m=42"><i class=" entypo-doc-text"></i>Administrar Embarques</a></li>
                                    <li <?php if ($_GET["m"]== "43") { echo "class='active'"; }?>><a href="#"><i class=" entypo-doc-text"></i>Delivery</a></li>
                                </ul>
                            </li>
                            <li <?php if ($_GET["m"]== "44") { echo "class='active'"; }?>><a href="#">Kitting</a>
							<li <?php if ($_GET["m"]== "25") { echo "class='active'"; }?>><a href="/incidencias/lists?m=25">Incidencias</a></li>

                        </ul>
                    </li>
                    <li <?php if ($_GET["m"] == "45"): echo "class='active'"; endif; ?>><a href="#"><i class="fa fa-files-o"></i> <span class="nav-label">Reportes</span>  <span class="fa arrow"></span></a>
					<ul class="nav nav-second-level collapse">
						<li <?php if ($_GET["m"]== "46") { echo "class='active'"; }?>><a href="#"><i class=" entypo-doc-text"></i>Entradas <span class="fa arrow"></span></a>
						<ul class="nav nav-third-level collapse">
						<li <?php if ($_GET["m"]== "46") { echo "class='active'"; }?>><a href="/reportes/entrada"><i class=" entypo-doc-text"></i>Entradas</a></li>
						<li <?php if ($_GET["m"]== "46") { echo "class='active'"; }?>><a href="/reportes/comprobantei"><i class=" entypo-doc-text"></i>Comprobante de ingreso</a></li>
						</ul>
						</li>
						<li <?php if ($_GET["m"]== "46") { echo "class='active'"; }?>><a href="#"><i class=" entypo-doc-text"></i>Inventarios <span class="fa arrow"></span></a>
						<ul class="nav nav-third-level collapse">
						
						<li <?php if ($_GET["m"]== "46") { echo "class='active'"; }?>><a href="/reportes/invfidetconc"><i class=" entypo-doc-text"></i>Inventario Fisico Concentrado</a></li>
						<li <?php if ($_GET["m"]== "46") { echo "class='active'"; }?>><a href="/reportes/invfidet"><i class=" entypo-doc-text"></i>Inventario Fisico Detalle</a>
						</li>
						
						<li <?php if ($_GET["m"]== "46") { echo "class='active'"; }?>><a href="/reportes/difconteos"><i class=" entypo-doc-text"></i>Diferencia entre conteos</a></li>
						<li <?php if ($_GET["m"]== "46") { echo "class='active'"; }?>><a href="/reportes/invcicl"><i class=" entypo-doc-text"></i>Inventario Ciclico Concentrado</a></li>
						<li <?php if ($_GET["m"]== "46") { echo "class='active'"; }?>><a href="/reportes/invcicldet"><i class=" entypo-doc-text"></i>Inventario Ciclico Detalle</a></li>
						</ul>
						</li>
						<li <?php if ($_GET["m"]== "46") { echo "class='active'"; }?>><a href="#"><i class=" entypo-doc-text"></i>Existencias <span class="fa arrow"></span></a>
						<ul class="nav nav-third-level collapse">
						<li <?php if ($_GET["m"]== "46") { echo "class='active'"; }?>><a href="#"><i class=" entypo-doc-text"></i>Existencia por Ubicacion</a></li>
						<li <?php if ($_GET["m"]== "46") { echo "class='active'"; }?>><a href="#"><i class=" entypo-doc-text"></i>Concentrado de Existencias</a></li>
						<li <?php if ($_GET["m"]== "46") { echo "class='active'"; }?>><a href="#"><i class=" entypo-doc-text"></i>Pendientes de Acomodo</a></li>
						<li <?php if ($_GET["m"]== "46") { echo "class='active'"; }?>><a href="#"><i class=" entypo-doc-text"></i>Faltantes de Producto</a></li>
						<li <?php if ($_GET["m"]== "46") { echo "class='active'"; }?>><a href="#"><i class=" entypo-doc-text"></i>Articulos en mas de una ubicacion</a></li>
						<li <?php if ($_GET["m"]== "46") { echo "class='active'"; }?>><a href="#"><i class=" entypo-doc-text"></i>Ocupacion de Ubicaciones</a></li>
						<li <?php if ($_GET["m"]== "46") { echo "class='active'"; }?>><a href="#"><i class=" entypo-doc-text"></i>Articulos de Lento Movimiento</a></li>
						</ul>
						</li>
						
						<li <?php if ($_GET["m"]== "46") { echo "class='active'"; }?>><a href="#"><i class=" entypo-doc-text"></i>Movimientos del AlmacÃ©n <span class="fa arrow"></span></a>
						<ul class="nav nav-third-level collapse">
						<li <?php if ($_GET["m"]== "46") { echo "class='active'"; }?>><a href="#"><i class=" entypo-doc-text"></i>Movimientos de Entrada-Salida</a></li>
						<li <?php if ($_GET["m"]== "46") { echo "class='active'"; }?>><a href="#"><i class=" entypo-doc-text"></i>Movimientos de AlmacÃ©n</a></li>
						<li <?php if ($_GET["m"]== "46") { echo "class='active'"; }?>><a href="#"><i class=" entypo-doc-text"></i>Translado entre ubicaciones</a></li>
						<li <?php if ($_GET["m"]== "46") { echo "class='active'"; }?>><a href="#"><i class=" entypo-doc-text"></i>Kardex</a></li>
						<li <?php if ($_GET["m"]== "46") { echo "class='active'"; }?>><a href="#"><i class=" entypo-doc-text"></i>BitÃ¡cora</a></li>
						<li <?php if ($_GET["m"]== "46") { echo "class='active'"; }?>><a href="#"><i class=" entypo-doc-text"></i>Reabastecimiento por Pedido</a></li>
						<li <?php if ($_GET["m"]== "46") { echo "class='active'"; }?>><a href="#"><i class=" entypo-doc-text"></i>Reabastecimiento por Producto</a></li>
						</ul>
						</li>
						
						<li <?php if ($_GET["m"]== "46") { echo "class='active'"; }?>><a href="#"><i class=" entypo-doc-text"></i>Salidas<span class="fa arrow"></span></a>
						<ul class="nav nav-third-level collapse">>
						<li <?php if ($_GET["m"]== "46") { echo "class='active'"; }?>><a href="#"><i class=" entypo-doc-text"></i>Orden de Surtido</a></li>
						<li <?php if ($_GET["m"]== "46") { echo "class='active'"; }?>><a href="#"><i class=" entypo-doc-text"></i>Pedidos Servidos</a></li>
						<li <?php if ($_GET["m"]== "46") { echo "class='active'"; }?>><a href="#"><i class=" entypo-doc-text"></i>Shipper</a></li>
						<li <?php if ($_GET["m"]== "46") { echo "class='active'"; }?>><a href="#"><i class=" entypo-doc-text"></i>Urgencias</a></li>
						<li <?php if ($_GET["m"]== "46") { echo "class='active'"; }?>><a href="#"><i class=" entypo-doc-text"></i>Incidencias </a></li>
						<li <?php if ($_GET["m"]== "46") { echo "class='active'"; }?>><a href="#"><i class=" entypo-doc-text"></i>Status de Ordenes</a></li>
						<li <?php if ($_GET["m"]== "46") { echo "class='active'"; }?>><a href="#"><i class=" entypo-doc-text"></i>Reportes de Rutas</a></li>
						<li <?php if ($_GET["m"]== "46") { echo "class='active'"; }?>><a href="#"><i class=" entypo-doc-text"></i>Reportes de Back Orders</a></li>
						<li <?php if ($_GET["m"]== "46") { echo "class='active'"; }?>><a href="#"><i class=" entypo-doc-text"></i>Back Orders liberados</a></li>
						</ul>
						</li>
						
					</ul>
                    </li>
                    <li>
                        <a href="#"><i class="fa fa-cogs"></i> <span class="nav-label">UtilerÃ­a</span><span class="fa arrow"></span></a>
                        <ul class="nav nav-second-level">
                            <li><a href="#"><i class=" entypo-doc-text"></i>Importar OC</a></li>
                            <li><a href="#"><i class=" entypo-doc-text"></i>Importar Pedidos</a></li>
                            <li><a href="#"><i class=" entypo-doc-text"></i>Importar Olas</a></li>
                        </ul>
                    </li>
                    <li>
                        <a href="#"><i class="fa fa-print"></i> <span class="nav-label">ImpresiÃ³n</span><span class="fa arrow"></span></a>
                        <ul class="nav nav-second-level">
                            <li><a href="/ubicaciones/lists?m=46"><i class=" entypo-doc-text"></i>Ubicaciones</a></li>
                            <li><a href="#"><i class=" entypo-doc-text"></i>GuÃ­as de Embarque</a></li>
                            <li><a href="#"><i class=" entypo-doc-text"></i>Productos</a></li>
                        </ul>
                    </li>
                    <li>
                        <a href="#"><i class="fa fa-key"></i> <span class="nav-label">Licencias</span><span class="fa arrow"></span></a>
                        <ul class="nav nav-second-level">
                            <li><a href="#"><i class=" entypo-doc-text"></i>Dispositivos</a></li>
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
                        <i class="fa fa-sign-out"></i> Cerrar Sesi&oacuten
                    </a>
                </li>
            </ul>
        </nav>
        </div>

      <div class="page-content-wrapper">
        <div class="page-content">
