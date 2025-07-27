<?php
include_once $_SERVER['DOCUMENT_ROOT']."/app/host.php";
$listaProv = new \Proveedores\Proveedores();
$listaTC = new \TipoCliente\TipoCliente();
$listaZona = new \Zona\Zona();
$listaCliente = new \Clientes\Clientes();
$ClasificacionCliente = new \ClasificacionClientes\ClasificacionClientes();
$GrupoCliente = new \GrupoClientes\GrupoClientes();
$listaAlmacen = new \AlmacenP\AlmacenP();
$almacenes = new \AlmacenP\AlmacenP();
$rutas = new \Ruta\Ruta();
if(!isNikken()){
    $codDaneSql = \db()->prepare("SELECT *  FROM c_dane ");
    $codDaneSql->execute();
    $codDane = $codDaneSql->fetchAll(PDO::FETCH_ASSOC);
}

$vere = \db()->prepare("SELECT * from t_profiles as a where id_menu=18 and id_submenu=37 and id_role='".$_SESSION["perfil_usuario"]."'");
$vere->execute();
$ver = $vere->fetchAll(PDO::FETCH_ASSOC);


$agre = \db()->prepare("SELECT * from t_profiles as a where id_menu=18 and id_submenu=38 and id_role='".$_SESSION["perfil_usuario"]."'");
$agre->execute();
$ag = $agre->fetchAll(PDO::FETCH_ASSOC);


$edita = \db()->prepare("SELECT * from t_profiles as a where id_menu=18 and id_submenu=39 and id_role='".$_SESSION["perfil_usuario"]."'");
$edita->execute();
$edit = $edita->fetchAll(PDO::FETCH_ASSOC);


$borra = \db()->prepare("SELECT * from t_profiles as a where id_menu=18 and id_submenu=40 and id_role='".$_SESSION["perfil_usuario"]."'");
$borra->execute();
$borrar = $borra->fetchAll(PDO::FETCH_ASSOC);


// MOD 18

// VER 37
// AGREGAR 38
// EDITAR 39
// BORRAR 40
$cve_proveedor = "";
if(isset($_SESSION['id_proveedor']))
{
    $cve_proveedor = $_SESSION['id_proveedor'];
}

$directoryURI = $_SERVER['REQUEST_URI'];

//echo $directoryURI;
//echo "<br>";
//echo $_SESSION["perfil_usuario"];
?>

<input type="hidden" id="cve_proveedor" value="<?php echo $cve_proveedor; ?>">

    <!-- Mainly scripts -->
    <script src="/js/jquery-2.1.1.js"></script>
    <script src="/js/bootstrap.min.js"></script>

    <script src="/js/plugins/metisMenu/jquery.metisMenu.js"></script>
    <script src="/js/plugins/slimscroll/jquery.slimscroll.min.js"></script>
    <link href="/css/plugins/sweetalert/sweetalert.css" rel="stylesheet">
    <script src="/js/plugins/footable/footable.all.min.js"></script>
    <!-- Peity -->
    <script src="/js/plugins/peity/jquery.peity.min.js"></script>

    <!-- jqGrid -->
    <script src="/js/plugins/jqGrid/i18n/grid.locale-es.js"></script>
    <script src="/js/plugins/jqGrid/jquery.jqGrid.min.js"></script>

    <!-- Custom and plugin javascript -->
    <script src="/js/inspinia.js"></script>
    <script src="/js/plugins/pace/pace.min.js"></script>
    <script src="/js/plugins/validate/jquery.validate.min.js"></script>
    <script src="/js/plugins/jquery-ui/jquery-ui.min.js"></script>
    <script src="/js/plugins/sweetalert/sweetalert.min.js"></script>
    <script src="/js/plugins/ladda/spin.min.js"></script>
    <script src="/js/plugins/ladda/ladda.min.js"></script>
    <script src="/js/plugins/ladda/ladda.jquery.min.js"></script>

    <!-- Select -->

    <script src="/js/plugins/staps/jquery.steps.min.js"></script>
    <script src="/js/plugins/chosen/chosen.jquery.js"></script>
    <script src="/js/plugins/iCheck/icheck.min.js"></script>



    <link href="/css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">
    <link href="/css/plugins/chosen/chosen.css" rel="stylesheet">
    <link href="/css/plugins/footable/footable.core.css" rel="stylesheet">
    <link href="/css/plugins/iCheck/custom.css" rel="stylesheet">


    <style type="text/css">
        .ui-jqgrid,
        .ui-jqgrid-view,
        .ui-jqgrid-hdiv,
        .ui-jqgrid-bdiv,
        .ui-jqgrid,
        .ui-jqgrid-htable,
        #grid-table,
        #grid-table2,
        #grid-table3,
        #grid-table4,
        #grid-pager,
        #grid-pager2,
        #grid-pager3,
        #grid-pager4 {
            width: 100% !important;
            max-width: 100% !important;
        }
        
        ul.dropdown-menu.dropdown-menu-right {
            position: absolute;
            left: auto;
            right: 0;
        }
    </style>

    <?php 
    /*
    if((strpos($_SERVER['HTTP_HOST'], 'sctp') === false))
    {
    ?>
    <input type="hidden" id="instanciaSCTP" value="0">
    <?php 
    }
    else
    {
    ?>
    <input type="hidden" id="instanciaSCTP" value="1">
    <?php 
    }
    */
    ?>
    <div class="wrapper wrapper-content  animated " id="list">

        <h3>Clientes*</h3>

        <div class="row">
            <div class="col-lg-12">
                <div class="ibox">

                    <div class="ibox-title">
                        <div class="row">
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <label for="almacenes">Almacen:</label>
                                    <select class="form-control" id="almacenes" name="almacenes">
                                        <option value=" ">Seleccione el Almacen</option>
                                        <?php foreach( $almacenes->getAll() AS $a ): ?>
                                        <option value="<?php echo $a->id; ?>"><?php echo "(".$a->clave.") - ".$a->nombre; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="col-sm-3">
                                    <div class="form-group">
                                        <label>Empresa | Proveedor </label>
                                        <select class="form-control" id="cboProveedor_busq">
                                        <?php if($cve_proveedor == ""){ ?>
                                        <option value="">Seleccione</option>
                                        <?php } ?>
                                        <?php 
                                            foreach( $listaProv->getAll(" AND es_cliente = 1 ") AS $p ): 
                                                if($p->ID_Proveedor == $cve_proveedor && $cve_proveedor != '')
                                                {
                                        ?>
                                        <option value="<?php echo $p->ID_Proveedor; ?>"><?php echo $p->Nombre; ?></option>
                                        <?php 
                                                }
                                                else if($cve_proveedor == "")
                                                {
                                        ?>
                                        <option value="<?php echo $p->ID_Proveedor; ?>"><?php echo $p->Nombre; ?></option>
                                        <?php 
                                                }
                                        ?>
                                        <?php endforeach; ?>
                                    </select>
                                    </div>
                            </div>

                            <div class="col-sm-3">
                                <div class="form-group">
                                    <label for="almacenes">Código Postal:</label>
                                    <?php if(isset($codDane) && !empty($codDane)): ?>
                                    <select id="cp_search" name="cp_search" class="form-control chosen">
                                        <option value="">Código</option>
                                        <?php foreach( $codDane AS $p ): ?>
                                            <option value="<?php echo $p["cod_municipio"];?>"><?php echo $p["cod_municipio"]." ".$p["des_municipio"]; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php else: ?>
                                    <input type="text" name="cp_search" id="cp_search" class="form-control">
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="col-sm-3">
                                <div class="form-group">
                                    <label for="ruta_clientes">Ruta:</label>
                                    <?php /*if(isset($codDane) && !empty($codDane)): ?>
                                    <select id="cp_search" name="cp_search" class="form-control chosen">
                                        <option value="">Código</option>
                                        <?php foreach( $codDane AS $p ): ?>
                                            <option value="<?php echo $p["cod_municipio"];?>"><?php echo $p["cod_municipio"]." ".$p["des_municipio"]; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php else:*/ ?>
                                    <input type="text" name="ruta_clientes" id="ruta_clientes" class="form-control">
                                    <?php //endif; ?>
                                </div>
                            </div>

                        </div>
                        <div class="row">
                            <div class="col-lg-4">
                                <div id="busqueda">
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="txtCriterio" id="txtCriterio" placeholder="Buscar...">
                                        <div class="input-group-btn">
                                            
                                            <button onclick="filtrar()" type="submit" id="buscarA" class="btn btn-primary">
                                                <span class="fa fa-search"></span> Buscar
                                            </button>

                                        </div>
                                    </div>
                                </div>

                            </div>


                            <div class="col-lg-8 permiso_registrar">
                                <?php if($ag[0]['Activo']==1){?>
                                    <a href="#" onclick="agregar()"><button class="btn btn-primary" type="button"><i class="fa fa-plus"></i>&nbsp;&nbsp;Nuevo</button></a>
                                <?php }?>
                                
                                <a href="/api/v2/clientes/exportar?almacen=<?php echo $_SESSION['cve_almacen']; ?>" target="_blank" class="btn btn-primary pull-right" style="margin-left:15px"><span class="fa fa-upload"></span> Exportar</a>
                                <button class="btn btn-primary pull-right permiso_registrar" style="margin-left:15px" data-toggle="modal" data-target="#importar"><span class="fa fa-download"></span> Importar</button>
                                <button class="btn btn-primary pull-right permiso_consultar" style="margin-left:15px" type="button" id="inactivos"><i class="fa fa-search"></i>&nbsp;&nbsp;Clientes inactivos</button>
                                
                                <?php /* if(isSCTP()): ?>
                                    <button class="btn btn-primary pull-right" onclick="obtenerClientesSCTP()" style="margin-right: 20px">Obtener Clientes de Rutas SCTP</button>
                                <?php endif; ?>
                                <?php if(isLaCentral()): ?>
                                    <button class="btn btn-primary pull-right" onclick="obtenerClientesLaCentral()" style="margin-right: 20px">Obtener Clientes de Rutas de La Central</button>
                                <?php endif; */ ?>
                            </div>
                        </div>

                        <div class="row">
                            <hr>
                            <div class="col-md-4">
                                <label>Total Clientes: </label> <span id="num_clientes"></span>
                            </div>
                            <div class="col-md-4">
                                <label>Total Clientes Crédito: </label> <span id="num_clientes_credito"></span>
                            </div>
                            <div class="col-md-4">
                                <label>Total Clientes Contado: </label> <span id="num_clientes_contado"></span>
                            </div>
                        </div>

                    </div>
                    <div class="ibox-content">
                        <div class="tabbable" id="tabs-131708">
                        <!--
                            <ul class="nav nav-tabs">
                                <li class="active">
                                    <a href="#panel-928563" id="simple" data-toggle="tab">Vista Simple</a>
                                </li>

                                <li>
                                    <a href="#panel-594076" id="avanzada" data-toggle="tab">Vista Avanzada</a>
                                </li>
                            </ul>
                        -->
                             <div class="row">
                              <div class="col-lg-2">
                                <div class="checkbox" id="chb_asignar">
                                  <label for="btn-asignarTodo">
                                    <input type="checkbox" name="asignarTodo" id="btn-asignarTodo">Seleccionar Todo
                                  </label>
                                </div>
                              </div>
                            </div> 
                            <div class="tab-content">
                                <div class="tab-pane active permiso_consultar" id="panel-928563">
                                    <div class="jqGrid_wrapper">
                                        <table id="grid-table"></table>
                                        <div id="grid-pager"></div>
                                    </div>
                                </div>
                              
                                 <div class="ibox-content"></br></br>
                                  <div class="form-group">
                                    <div class="input-group-btn">
                                      <button id="btn-asignar" onclick="asignar()" type="button" class="btn btn-m btn-primary permiso_registrar">Asignar</button>

                                      <input style="margin-left: 30px;" type="button" id="imprimir_lp" class="btn btn-primary permiso_consultar" value="Imprimir Etiquetas Clientes Seleccionados">

                                    </div>
                                  </div>
                                </div> 
                                <?php 
                                /*
                                ?>
                                <div class="tab-pane" id="panel-594076">
                                    <table class="footable table table-stripped toggle-arrow-tiny" data-paging="true" data-filtering="true" data-sorting="true" data-expand-first="true">
                                        <thead>
                                            <tr>
                                                <th>Clave</th>
                                                <th>Almacen</th>
                                                <th>Razon Social</th>
                                                <th>Dirección</th>
                                                <th>Código</th>
                                                <th>Departamento/Estado</th>
                                                <th>Municipio/Ciudad</th>

                                                <th data-breakpoints="all">Rutas</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach( $listaCliente->getCliente2() AS $p ): ?>
                                            <tr>
                                                <td>
                                                    <?php echo $p->Cve_Clte; ?>
                                                </td>
                                                <td>
                                                    <?php echo $p->almacenp; ?>
                                                </td>
                                                <td>
                                                    <?php echo $p->RazonSocial; ?>
                                                </td>
                                                <td>
                                                    <?php echo $p->CalleNumero; ?>
                                                </td>
                                                <td>
                                                    <?php echo $p->CodigoPostal; ?>
                                                </td>
                                                <td>
                                                    <?php echo $p->Estado; ?>
                                                </td>
                                                <td>
                                                    <?php echo $p->Ciudad; ?>
                                                </td>

                                                <td>
                                                    <?php foreach( $listaCliente->traerRutas($p->id_cliente) AS $r ): ?>
                                                    <?php echo $r['descripcion'].", "; ?>
                                                    <?php endforeach; ?>

                                                </td>
                                                <td><a href="#" onclick="editar('<?php echo $p->Cve_Clte; ?>')"><i class="fa fa-edit" alt="Editar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;
                                                    <a href="#" onclick="borrar('<?php echo $p->Cve_Clte; ?>')"><i class="fa fa-eraser" alt="Borrar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="5">
                                                    <ul class="pagination pull-right"></ul>
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>

                                </div>
                                <?php 
                                */
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <div class="wrapper wrapper-content  animated fadeInRight" id="FORM" style="display: none">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-title">
                        <div class="row">
                            <div class="col-lg-4" id="_title">
                                <h3>Agregar Cliente</h3>
                            </div>
                        </div>
                    </div>
                    <form id="myform">
                        <input type="hidden" id="hiddenAction" name="hiddenAction">
                        <input type="hidden" id="hiddenIDCliente">
                        <div class="ibox-content">
                            <div class="row">
                                <div class="col-lg-12 b-r">
                                    <div class="row">
                                    <div class="col-lg-6 b-r form-group ">
                                      <label>Almacen *</label>
                                      <select class="form-control" id="almacenp" required="true">
                                        <option value="">Almacen</option>
                                        <?php foreach( $listaAlmacen->getAll() AS $p ): ?>
                                        <option value="<?php echo $p->id; ?>"><?php echo $p->nombre; ?></option>
                                        <?php endforeach; ?>
                                      </select>
                                      <br>
                                    <label>Clave Interna</label> 
                                    <input id="consecutivo" type="text"  class="form-control" maxlength="20" required="true" disabled>
                                    <br>
                                    <div class="form-group">
                                    <label>Clave ERP *</label> 
                                    <input id="txtClaveCliente" type="text" placeholder="Clave del Cliente" class="form-control" maxlength="20" >
                                    <label id="CodeMessage" style="color:red;"></label></div>
                                    <div class="form-group">
                                    <label>Clave WMS </label> 
                                    <input id="txtClaveClienteProv" type="text" placeholder="Clave WMS" class="form-control">
                                    </div>
                                    <div class="form-group"><label>Nombre Comercial *</label> <input id="txtNombreCorto" type="text" placeholder="Nombre Comercial" style="text-transform:uppercase;" class="form-control" required="true"></div>
                                    <div class="form-group"><label>Razón Social *</label> <input id="txtRazonSocial" type="text" placeholder="Razón Social" style="text-transform:uppercase;" class="form-control" required="true"></div>
                                    <div class="form-group"><label>Responsable</label> <input id="txtEncargado" type="text" placeholder="Encargado" style="text-transform:uppercase;" class="form-control" required="true"></div>
                                    <div class="form-group"><label>Referencia</label> <input id="txtReferencia" type="text" placeholder="Referencia" class="form-control" required="true"></div>
                                    <div class="form-group"><label>Dirección *</label> <input id="txtCalleNumero" type="text" placeholder="Calle y Numero" style="text-transform:uppercase;" class="form-control" required="true"></div>
                                    <div class="form-group"><label>Colonia </label> <input id="txtColonia" type="text" placeholder="Colonia" style="text-transform:uppercase;" class="form-control"></div>
                                    <div class="form-group">
                                        <label>CP | CD *</label>
                                        <?php if(isset($codDane) && !empty($codDane)): ?>
                                        <select id="txtCod" name="txtCod" class="form-control chosen" style="width:100%">
                                            <option value="">Código</option>
                                            <?php foreach( $codDane AS $p ): ?>
                                                <option value="<?php echo $p["cod_municipio"];?>"><?php echo $p["cod_municipio"]." ".$p["des_municipio"]; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <?php else: ?>
                                        <input type="text" name="txtCod" id="txtCod" class="form-control">
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="col-lg-6">

                                    <div class="checkbox" id="credito_cliente">
                                        <label style="padding: 0;">
                                        <input type="checkbox" name="credito_on_off" id="credito_on_off" class="icheck">
                                        <b>Crédito</b>
                                        </label>
                                    </div><br>

                                    <div class="form-group">
                                    <label>Límite Crédito</label> 
                                    <input id="limite_credito" type="number" placeholder="Límite Crédito" class="form-control">
                                    </div>

                                    <div class="form-group">
                                    <label>Días Crédito</label> 
                                    <input id="dias_credito" type="number" placeholder="Días Crédito" class="form-control">
                                    </div>

                                    <div class="form-group">
                                    <label>Crédito Actual</label> 
                                    <input id="credito_actual" type="text" placeholder="Crédito Actual" class="form-control" readonly>
                                    </div>

                                    <div class="form-group">
                                    <label>Saldo Inicial</label> 
                                    <input id="saldo_inicial" type="number" placeholder="Saldo Inicial" class="form-control">
                                    </div>

                                    <div class="form-group">
                                    <label>Saldo Actual</label> 
                                    <input id="saldo_actual" type="text" placeholder="Saldo Actual" class="form-control" readonly>
                                    </div>

                                    <br>
                                    <div class="form-group">
                                      <label>Grupo</label>
                                      <select class="form-control" id="grupocliente" required="true">
                                        <option value="">Seleccione Grupo</option>
                                        <?php foreach( $GrupoCliente->getAll() AS $p ): ?>
                                        <option value="<?php echo $p->cve_grupo; ?>"><?php echo "( ".$p->cve_grupo." ) - ".$p->des_grupo; ?></option>
                                        <?php endforeach; ?>
                                      </select>
                                    </div>

                                    <br>
                                    <div class="form-group">
                                      <label>Clasificación</label>
                                      <select class="form-control" id="tipocliente" required="true">
                                        <?php /*
                                        <option value="">Seleccione Clasificación</option>
                                         foreach( $ClasificacionCliente->getAll() AS $p ): ?>
                                        <option value="<?php echo $p->Cve_TipoCte; ?>"><?php echo "( ".$p->Cve_TipoCte." ) - ".$p->Des_TipoCte; ?></option>
                                        <?php endforeach; */ ?>
                                      </select>
                                    </div>
                                    <br>
                                    <div class="form-group">
                                      <label>Clasificación 2</label>
                                      <select class="form-control" id="tipocliente2" required="true">
                                        <?php /*
                                        <option value="">Seleccione Clasificación</option>
                                        foreach( $ClasificacionCliente->getAll2() AS $p ): ?>
                                        <option value="<?php echo $p->Cve_TipoCte; ?>"><?php echo "( ".$p->Cve_TipoCte." ) - ".$p->Des_TipoCte; ?></option>
                                        <?php endforeach; */ ?>
                                      </select>
                                    </div>

                                    <br>
                                    <div class="checkbox" id="gps_cliente" style="display: inline-block;">
                                        <label style="padding: 0;">
                                        <input type="checkbox" name="validar_gps" id="validar_gps" class="icheck">
                                        <b>Validar GPS</b>
                                        </label>
                                    </div>
                                    <div class="checkbox" id="cliente_gen" style="display: inline-block;">
                                        <label style="padding-left: 100px;">
                                        <input type="checkbox" name="cliente_general" id="cliente_general" class="icheck">
                                        <b>Cliente Genérico</b>
                                        </label>
                                    </div>
<?php 
/*
?>
                                    <div class="checkbox" id="tipo_traslado">
                                        <label style="padding: 0;">
                                        <input type="checkbox" name="cliente_tipo_traslado" id="cliente_tipo_traslado" class="icheck">
                                        <b>Cliente Tipo Traslado</b>
                                        </label>
                                    </div>
<?php 
*/
?>
                                    <hr>

                                </div>

                                </div>

                                <div class="row">
                                <div class="col-lg-6 b-r">
                                    <hr>
                                    <div class="form-group"><label>Alcaldía | Municipio</label> <input id="txtMunicipio" type="text" placeholder="Municipio" class="form-control"></div>
                                    <div class="form-group"><label>Ciudad | Departamento</label> <input id="txtDepart" type="text" placeholder="Departamento" class="form-control"></div>
                                    <div class="form-group"><label>País *</label> <input id="txtPais" type="text" placeholder="País" class="form-control" required="true"></div>
                                    <div class="form-group"><label>RFC/RUT</label> <input id="txtRFC" type="text" placeholder="RFC/RUT" maxlength="15" oninput="this.value = this.value.toUpperCase();" class="form-control"></div>
                                    <div class="form-group"><label>Contacto </label> <input id="txtContacto" type="text" placeholder="Contacto" class="form-control"></div>
                                    <div class="form-group"><label>Teléfono 1 </label> <input id="txtTelefono1" maxlength="14" oninput="this.value = this.value.replace(/[^0-9]/g, '');" type="text" maxlength="15" placeholder="Teléfono 1" class="form-control"></div>
                                    <div class="form-group"><label>Teléfono 2 </label> <input id="txtTelefono2" maxlength="14" oninput="this.value = this.value.replace(/[^0-9]/g, '');" type="text" maxlength="15" placeholder="Teléfono 2" class="form-control"></div>
                                </div>
                                <div class="col-lg-6">
                                    <hr>
                                    <div class="form-group">
                                        <label>Proveedor </label>
                                        <select class="form-control" id="cboProveedor">
                                        <option value="">Seleccione</option>
                                        <?php foreach( $listaProv->getAll(" AND es_cliente = 1 ") AS $p ): ?>
                                        <option value="<?php echo $p->ID_Proveedor; ?>"><?php echo $p->Nombre; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    </div>
                                        <div class="form-group"><label>Email</label> <input id="email_cliente" type="text" placeholder="Email del cliente" class="form-control"></div>
                                        <div class="form-group"><label>Latitud </label> <input id="txtLatitud" type="number" placeholder="Latitud" class="form-control"></div>
                                        <div class="form-group"><label>Longitud </label> <input id="txtLongitud" type="number" placeholder="Longitud" class="form-control"></div>
                                    <?php 
                                    /*
                                    ?>
                                        <div class="form-group"><label>Condicion de Pago </label> <input id="txtCondicionPago" type="text" placeholder="Condicion de Pago" class="form-control"></div>
                                        <div class="form-group">
                                        <label>Tipo Cliente </label>
                                        <select class="form-control" id="cboTipoCliente">
                                        <option value="">Seleccione</option>
                                        <?php foreach( $listaTC->getAll() AS $t ): ?>
                                        <option value="<?php echo $t->Cve_TipoCte; ?>"><?php echo $t->Des_TipoCte; ?></option>
                                        <?php endforeach; ?>
                                        </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Zona </label>
                                            <select class="form-control" id="cboZona">
                                            <option value="">Seleccione</option>
                                            <?php foreach( $listaZona->getAll() AS $p ): ?>
                                            <option value="<?php echo $p->Cve_ZonaCte; ?>"><?php echo $p->Des_ZonaCte; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        </div>
                                    <?php 
                                    */
                                    ?>
                                    <div class="form-group">
                                        <label>Dirección de Envío (Destinatario)</label>
                                        <select class="form-control chosen-select" name="direccion_envio" id="direccion_envio">
                                        <option value="">Seleccione</option>
                                        </select>
                                        <select name="direccion_envio_band" id="direccion_envio_band" style="display: none;">
                                            <option value=""></option>
                                        </select>
                                        <div style="text-align: right;">
                                            <br>
                                            <?php 
                                            /*
                                            ?>
                                            <button type="button" id="agregar_destinatario" class="btn btn-sm btn-success" data-toggle="modal" data-target="#modal_destinatario">Agregar Destinatario</button>
                                            <?php 
                                            */
                                            ?>
                                        </div>
                                    </div>
                                    <div class="checkbox" id="direccion_principal">
                                        <label>
                                        <input type="checkbox" name="usar_direccion" id="usar_direccion" class="icheck">
                                        Usar dirección principal
                                    </label>
                                    </div>
                                    <div class="form-group" style="text-align: right;">
                                        <br>
                                        <a href="#" onclick="cancelar()"><button type="button" class="btn btn-white" id="btnCancel">Cerrar</button></a>&nbsp;&nbsp;
                                        <button type="button" class="btn btn-primary ladda-button" data-style="contract" id="btnSave">Guardar</button>
                                    </div>
                                </div>
                                </div>
                            </div>

                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal_destinatario" role="dialog">
        <div class="vertical-alignment-helper">
            <div class="modal-dialog vertical-align-center">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Agregar Destinatario</h4>
                    </div>
                    <div class="modal-body">

                        <div class="col-md-6 b-r" style="z-index: 100;">

                        <div class="form-group">
                            <label>Consecutivo</label>
                            <input type="text" id="consecutivo_destinatario" class="form-control" readonly>
                        </div>
                        <div class="form-group">
                            <label>Razón Social</label>
                            <input type="text" style="text-transform:uppercase;" class="form-control" id="destinatario_razonsocial">
                        </div>
                        <div class="form-group">
                            <label>Dirección</label>
                            <input type="text" style="text-transform:uppercase;" class="form-control" id="destinatario_direccion">
                        </div>
                        <div class="form-group">
                            <label>Colonia</label>
                            <input type="text" style="text-transform:uppercase;" class="form-control" id="destinatario_colonia">
                        </div>
                        <div class="form-group">
                            <label>CP | CD</label>
                            <?php if(isset($codDane) && !empty($codDane)): ?>
                            <select id="destinatario_dane" class="form-control" required="true">
                                <option value="">Código</option>
                                <?php foreach( $codDane AS $p ): ?>
                                    <option value="<?php echo $p["cod_municipio"];?>"><?php echo $p["cod_municipio"]." ".$p["des_municipio"]; ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php else: ?>
                            <input type="text" name="destinatario_dane" id="destinatario_dane" class="form-control" required="true">
                            <?php endif; ?>
                        </div>
                        <div class="form-group">
                            <label>Alcaldía | Municipio</label>
                            <input type="text" class="form-control" id="destinatario_ciudad" readonly>
                        </div>
                        <div class="form-group">
                            <label>Ciudad | Departamento</label>
                            <input type="text" class="form-control" id="destinatario_estado" readonly>
                        </div>

                        </div>

                        <div class="col-md-6" style="z-index: 100;">

                        <div class="form-group">
                            <label>Contacto</label>
                            <input type="text" class="form-control" id="destinatario_contacto">
                        </div>
                        <div class="form-group">
                            <label>Teléfono</label>
                            <input type="text" class="form-control" id="destinatario_telefono">
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="text" class="form-control" id="txtEmailDest">
                        </div>
                        <div class="form-group">
                            <label>Latitud</label>
                            <input type="number" class="form-control" id="txtLatitudDest">
                        </div>
                        <div class="form-group">
                            <label>Longitud</label>
                            <input type="number" class="form-control" id="txtLongitudDest">
                        </div>

                        </div>

                        <div style="text-align: right;position: relative;top: 110px;width: 100%; z-index: 0;" class="row">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                            <button type="submit" class="btn btn-primary" id="guardar_destinatario">Guardar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="waModal" role="dialog">
        <div class="vertical-alignment-helper">
            <div class="modal-dialog vertical-align-center">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Advertencia</h4>
                    </div>
                    <div class="modal-body">
                        <p>Verificar que no hayan campos vacíos</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

<!-- 
    <div class="modal fade" id="modal_asignacion_ruta" role="dialog">
        <div class="vertical-alignment-helper">
            <div class="modal-dialog vertical-align-center modal-lg">
                
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Asignar Ruta</h4>
                    </div>
                    <div class="modal-body">
                      <div class="col-md-6">
                        <div class="form-group">

                            <label>Asignar Ruta</label>
                            <select class="form-control" id="ruta_asignada">
                              <option value="">Seleccione</option>
                              <?php foreach( $rutas->getAll() AS $p ): ?>
                              <option value="<?php echo $p->cve_ruta; ?>"><?php echo "(".$p->cve_ruta.") - ".$p->descripcion; ?></option>
                              <?php endforeach; ?>
                            </select>
                        </div>
                      </div>
                        <div class="ibox-content">
                          <table class="table table-bordered" id="tabla_usuarios_select">
                            <thead>
                              <tr>
                                <th>Clave de Cliente</th>
                                <th>Razon Social</th>
                                <th>Acciones</th>
                              </tr>
                            </thead>
                            <tbody>
                            </tbody>
                          </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                        <button type="button" class="btn btn-m btn-primary" id="guardar_ruta">Guardar Ruta</button>
                    </div>
                </div>
            </div>
        </div>
    </div> -->


    <div class="modal fade" id="asignar_lista_modal" role="dialog">
        <div class="vertical-alignment-helper">
            <div class="modal-dialog vertical-align-center">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Asignar a Lista</h4>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="array_destinatarios" value="">
                        <div class="row">
                            <div id="lista_precios">
                                <label>Lista de Precios</label>
                                <br>
                                <label style="padding: 0;">
                                <input type="checkbox" name="modificar_listap" id="modificar_listap">
                                <b>Modificar</b>
                                </label>
                                <select id="lista_precios_select" class="form-control">
                                    <option value="">Seleccione la Lista de Precios</option>
                                </select>

                            </div>
                        </div>
<br>
                        <div class="row">
                            <div id="lista_descuentos">
                                <label>Lista de Descuentos</label>
                                <br>
                                <label style="padding: 0;">
                                <input type="checkbox" name="modificar_listad" id="modificar_listad">
                                <b>Modificar</b>
                                </label>
                                <select id="lista_descuentos_select" class="form-control">
                                    <option value="">Seleccione la Lista de Descuentos</option>
                                </select>

                            </div>
                        </div>
<br>
                        <div class="row">
                            <div id="lista_promociones">
                                <label>Grupo de Promociones</label>
                                <br>
                                <label style="padding: 0;">
                                <input type="checkbox" name="modificar_listagp" id="modificar_listagp">
                                <b>Modificar</b>
                                </label>
                                <select id="lista_promociones_select" class="form-control">
                                    <option value="">Seleccione la Lista de Promociones</option>
                                </select>
                            </div>

                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                        <button type="button" id="asignarLista" class="btn btn-primary">Asignar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="coModal" role="dialog">
        <div class="vertical-alignment-helper">
            <div class="modal-dialog vertical-align-center modal-lg">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Recuperar Cliente</h4>
                    </div>
                    <div class="modal-body">
                        <div class="col-lg-4">
                            <div class="input-group">
                                <input type="text" class="form-control input-sm" name="txtCriterio" id="txtCriterio1" placeholder="Buscar por Clientes...">
                                <div class="input-group-btn">
                                    <a href="#" onclick="ReloadGrid1()">
                                        <button type="submit" class="btn btn-sm btn-primary">
                                        Buscar
                                    </button>
                                    </a>
                                </div>
                            </div>

                        </div>
                        <div class="ibox-content">
                            <div class="jqGrid_wrapper">
                                <table id="grid-table2"></table>
                                <div id="grid-pager2"></div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if(isSCTP()): ?>
    <div class="modal fade" id="modal_sctp" role="dialog">
        <div class="vertical-alignment-helper">
            <div class="modal-dialog vertical-align-center modal-sm">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Obteniendo Clientes de SCTP</h4>
                    </div>
                    <div class="modal-body">
                        <div class="text-center">
                            <i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i>
                            <div class="success">
                                <h3>
                                    <i class="fa fa-check" style="color: #1ab394"></i> ¡Clientes cargados exitosamente!
                                </h3>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" id="button_modal_sctp" disabled="disabled" data-dismiss="modal">Aceptar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <?php if(isLaCentral()): ?>
    <div class="modal fade" id="modal_lacentral" role="dialog">
        <div class="vertical-alignment-helper">
            <div class="modal-dialog vertical-align-center modal-sm">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Obteniendo Clientes de La Central</h4>
                    </div>
                    <div class="modal-body">
                        <div class="text-center">
                            <i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i>
                            <div class="success">
                                <h3>
                                    <i class="fa fa-check" style="color: #1ab394"></i> ¡Clientes cargados exitosamente!
                                </h3>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" id="button_modal_lacentral" disabled="disabled" data-dismiss="modal">Aceptar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>



    <div class="modal fade" id="importar" role="dialog">
        <div class="vertical-alignment-helper">
            <div class="modal-dialog vertical-align-center">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Importar Clientes</h4>
                    </div>
                    <div class="modal-body">
                        <form id="form-import" action="import" method="post" enctype="multipart/form-data">
                            <div class="form-group">
                                <label>Seleccionar archivo excel para importar</label>
                                <input type="file" name="file" id="file" class="form-control"  required>
                            </div>
                        </form>
                        <div class="col-md-12">
                            <div class="progress" style="display:none">
                                <div class="progress-bar progress-bar-info progress-bar-striped" role="progressbar"
                                aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:0%">
                                    <div class="percent">0%</div >
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                      <div style="display: inline-block;float: left;">
                        <button id="btn-layout" type="button" class="btn btn-primary">Descargar Layout</button>
                      </div>
                        <div style="display: inline-block;float: right;">
                            <button id="btn-import" type="button" class="btn btn-primary">Importar</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

<script>

$('#btn-layout').on('click', function(e) {
  //e.preventDefault();  //stop the browser from following
  //window.location.href = 'http://www.tinegocios.com/proyectos/wms/Layout_Pedidos.xlsx';
  window.location.href = '/Layout/Layout_Clientes.xlsx';

}); 

    $('#btn-import').on('click', function() {

        $('#btn-import').prop('disable', true);
        var bar = $('.progress-bar');
        var percent = $('.percent');
        //var status = $('#status');

        var formData = new FormData();
        formData.append("clave", "valor");

        $.ajax({
            // Your server script to process the upload
            url: '/api/v2/clientes/importar',
            type: 'POST',

            // Form data
            data: new FormData($('#form-import')[0]),

            // Tell jQuery not to process data or worry about content-type
            // You *must* include these options!
            cache: false,
            contentType: false,
            processData: false,
            beforeSend: function() {
                $('.progress').show();
                var percentVal = '0%';
                bar.width(percentVal);
                percent.html(percentVal);
            },
            // Custom XMLHttpRequest
            xhr: function() {
                var myXhr = $.ajaxSettings.xhr();
                if (myXhr.upload) {
                    // For handling the progress of the upload
                    myXhr.upload.addEventListener('progress', function(e) {
                        if (e.lengthComputable) {
                            var percentComplete = e.loaded / e.total;
                            percentComplete = parseInt(percentComplete * 100);
                            bar.css("width", percentComplete + "%");
                            percent.html(percentComplete+'%');
                            if (percentComplete === 100) {
                                setTimeout(function(){$('.progress').hide();}, 2000);
                            }
                        }
                    } , false);
                }
                return myXhr;
            },
            success: function(data) {
                console.log(data);
                setTimeout(
                    function(){if (data.status == 200) {
                        swal("Exito", data.statusText, "success");
                        $('#importar').modal('hide');
                        ReloadGrid();
                    }
                    else {
                        swal("Error", data.statusText, "error");
                    }
                },1000)
            }, error: function(data){
                console.log("ERROR", data);
            }
        });
    });
</script>


<script type="text/javascript">

    function exportar(){
        $.ajax(
            {
                type: "POST",
                dataType: "json",
                url: '/api/v2/clientes/exportar',
                done: function (data) {
                    var a = document.createElement('a');
                    var url = window.URL.createObjectURL(data);
                    a.href = url;
                    a.download = 'myfile.pdf';
                    a.click();
                    window.URL.revokeObjectURL(url);
                }
            }
        );
    }

</script>

    <script type="text/javascript">
        /**
         * @author Ricardo Delgado.
         * Busca y selecciona el almacen predeterminado para el usuario.
         */
        function almacenPrede() {
            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    idUser: '<?php echo $_SESSION["id_user"]?>',
                    action: 'search_almacen_pre'
                },
                beforeSend: function(x) {
                    if (x && x.overrideMimeType) {
                        x.overrideMimeType("application/json;charset=UTF-8");
                    }
                },
                url: '/api/almacenPredeterminado/index.php',
                success: function(data) {
                    if (data.success == true) {
                        document.getElementById('almacenes').value = data.codigo.id;

                        //if($("#instanciaSCTP").val() == '0')
                        //{
                            setTimeout(function() {
                                ReloadGrid();
                            }, 1000);
                        //}

                        //$("#buscarA").click();
                    }
                },
                error: function(res) {
                    window.console.log(res);
                }
            });
        }
        almacenPrede();

        $('#avanzada').on('click', function() {
            $("#busqueda")[0].style.display = 'none';
        });
        $('#simple').on('click', function() {
            $("#busqueda")[0].style.display = 'block';
        });
        //////////////////////////////////////////////////////////Aqui se contruye el Grid//////////////////////////////////////////////////////////
        $(function($) {
            var grid_selector = "#grid-table";
            var pager_selector = "#grid-pager";

            //resize to fit page size
            $(window).on('resize.jqGrid', function() {
                    $(grid_selector).jqGrid('setGridWidth', $(".page-content").width() - 60);
                })
                //resize on sidebar collapse/expand
            var parent_column = $(grid_selector).closest('[class*="col-"]');
            $(document).on('settings.ace.jqGrid', function(ev, event_name, collapsed) {
                if (event_name === 'sidebar_collapsed' || event_name === 'main_container_fixed') {
                    //setTimeout is for webkit only to give time for DOM changes and then redraw!!!
                    setTimeout(function() {
                        $(grid_selector).jqGrid('setGridWidth', parent_column.width());
                    }, 0);
                }
            })

            $(grid_selector).jqGrid({
                url: '/api/clientes/lista/index.php',
                datatype: "json",
                shrinkToFit: false,
                height: 'auto',
                postData: {
                    criterio: $("#txtCriterio").val(),
                    cve_proveedor: $("#cve_proveedor").val(),
                    ruta_clientes: $("#ruta_clientes").val()
                },
                mtype: 'POST',
                colNames: ["Acciones",'Asignar','Ruta', 'Clave Interna', 'Clave ERP', 'Clave WMS', 'Nombre Comercial', 'Razón Social', 'Dirección', 'CP | CD', 'Colonia', 'Alcaldía | Municipio', 'Ciudad | Departamento', 'Grupo', 'Clasificación', 'Clasificación 2', 'RFC', 'Crédito', 'Saldo', 'Lista de Precios', 'Lista de Descuento', 'Grupo Promoción', 'Destinatario Principal', 'Latitud', 'Longitud', 'Almacen', 'id_listaprecios', 'id_listadescuentos', 'id_listapromo'],
                /*'Calle y Numero','Colonia','Ciudad','Estado','Pais','Codigo Postal','RFC','Telefono1','Telefono2'*/
                colModel: [{
                    name: 'myac',
                    index: '',
                    width: 100,
                    fixed: true,
                    sortable: false,
                    resize: false,
                    formatter: imageFormat
                },{
                    name: 'asignar',
                    index: '1',
                    width: 80,
                    fixed: true,
                    sortable: false,
                    resize: false,
                    align:'center',
                    formatter: imageFormat2
                },
                {
                    name: 'ruta',
                    index: 'ruta',
                    width: 80,
                    editable: false,
                    sortable: false
                },
                {
                    name: 'id_cliente',
                    index: 'id_cliente',
                    width: 80,
                    editable: false,
                    sortable: false,
                    align:'right',
                    hidden: false
                }, {
                    name: 'Cve_Clte',
                    index: 'Cve_Clte',
                    width: 80,
                    editable: false,
                    sortable: false
                }, {
                    name: 'Cve_CteProv',
                    index: 'Cve_CteProv',
                    width: 80,
                    editable: false,
                    sortable: false
                }, {
                    name: 'RazonComercial',
                    index: 'RazonComercial',
                    width: 150,
                    editable: false,
                    sortable: false
                }, {
                    name: 'RazonSocial',
                    index: 'RazonSocial',
                    width: 200,
                    editable: false,
                    sortable: false
                }, {
                    name: 'CalleNumero',
                    index: 'CalleNumero',
                    width: 200,
                    editable: false,
                    sortable: false
                }, {
                    name: 'CodigoPostal',
                    index: 'CodigoPostal',
                    width: 80,
                    editable: false,
                    sortable: false
                }, {
                    name: 'Colonia',
                    index: 'Colonia',
                    width: 150,
                    editable: false,
                    sortable: false
                }, {
                    name: 'departamento',
                    index: 'departamento',
                    width: 150,
                    editable: false,
                    sortable: false
                }, {
                    name: 'des_municipio',
                    index: 'des_municipio',
                    width: 150,
                    editable: false,
                    sortable: false
                }, {
                    name: 'grupo',
                    index: 'grupo',
                    width: 150,
                    editable: false,
                    sortable: false
                }, {
                    name: 'clasificacion',
                    index: 'clasificacion',
                    width: 150,
                    editable: false,
                    sortable: false
                }, {
                    name: 'clasificacion2',
                    index: 'clasificacion2',
                    width: 150,
                    editable: false,
                    sortable: false
                }, {
                    name: 'rfc',
                    index: 'rfc',
                    width: 150,
                    editable: false,
                    sortable: false
                }, {
                    name: 'credito',
                    index: 'credito',
                    width: 150,
                    editable: false,
                    align:'right',
                    sortable: false
                }, {
                    name: 'saldo',
                    index: 'saldo',
                    width: 150,
                    editable: false,
                    align:'right',
                    sortable: false
                }, {
                    name: 'lista_precios',
                    index: 'lista_precios',
                    width: 150,
                    editable: false,
                    sortable: false
                }, {
                    name: 'lista_descuento',
                    index: 'lista_descuento',
                    width: 150,
                    editable: false,
                    sortable: false
                }, {
                    name: 'lista_promocion',
                    index: 'lista_promocion',
                    width: 150,
                    editable: false,
                    sortable: false
                }, {
                    name: 'destinatario_principal',
                    index: 'destinatario_principal',
                    width: 150,
                    editable: false,
                    sortable: false,
                    align:'right',
                    hidden: false
                }, {
                    name: 'latitud',
                    index: 'latitud',
                    width: 100,
                    editable: false,
                    sortable: false,
                    align:'right',
                    hidden: false
                }, {
                    name: 'longitud',
                    index: 'longitud',
                    width: 100,
                    editable: false,
                    sortable: false,
                    align:'right',
                    hidden: false
                }, {
                    name: 'almacenp',
                    index: 'almacenp',
                    width: 160,
                    editable: false,
                    sortable: false,
                    hidden: false
                }, {name: 'id_listaprecios', index: 'id_listaprecios', width: 160, editable: false, sortable: false, hidden: true }
                , {name: 'id_listadescuentos', index: 'id_listadescuentos', width: 160, editable: false, sortable: false, hidden: true }
                , {name: 'id_listapromo', index: 'id_listapromo', width: 160, editable: false, sortable: false, hidden: true }
                 ],
                rowNum: 30,
                rowList: [30, 40, 50],
                pager: pager_selector,
                sortname: 'id_cliente',
                viewrecords: true,
                sortorder: "desc",
                loadComplete: function(data){
                    console.log("SUCESS", data);
                    $("#num_clientes").text(data.num_clientes);
                    $("#num_clientes_credito").text(data.credito);
                    $("#num_clientes_contado").text(data.contado);
                }, loadError: function(data){
                    console.log("ERROR", data);
                }//,almacenPrede()
            });

            // Setup buttons
            $("#grid-table").jqGrid('navGrid', '#grid-pager', {
                edit: false,
                add: false,
                del: false,
                search: false
            }, {
                height: 200,
                reloadAfterSubmit: true
            });


            $(window).triggerHandler('resize.jqGrid'); //trigger window resize to make the grid get the correct size

            //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

            //////////////////////////////////////////////////////////aqui se construye la funcion de cada item del grid//////////////////////////////////////////////////////////
            function imageFormat(cellvalue, options, rowObject) {
                //return '<img src="'+cellvalue+'" />&nbsp;&nbsp;&nbsp;&nbsp;<img src="'+cellvalue+'" />';
                var serie = rowObject[4];
                var correl = rowObject[8];
                var url = "x/?serie=" + serie + "&correl=" + correl;
                var url2 = "v/?serie=" + serie + "&correl=" + correl;

                // la funcion downloadxml ejecuta el index.php que esta en la carpeta x pasandole la serie y el correlativo via get
                // la funcion viewPdf ejecuta el index.php que esta en la carpeta v pasandole la serie y el correlativo via get

                var html = '';
                //html += '<a href="#" onclick="ver(\''+serie+'\')"><i class="fa fa-eye" alt="Ver"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';

        if($("#permiso_consultar").val() == 1)
                html += '&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" class="check_lp permiso_consultar" title="Imprimir Etiqueta Cliente" value="'+serie+'">';
        if($("#permiso_editar").val() == 1)
                html += '&nbsp;&nbsp;&nbsp;&nbsp;<a href="#" onclick="editar(\'' + serie + '\')"><i class="fa fa-edit permiso_editar" alt="Editar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
        if($("#permiso_eliminar").val() == 1)
                html += '<a href="#" onclick="borrar(\'' + serie + '\')"><i class="fa fa-eraser permiso_eliminar" alt="Borrar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';

                //return '<a href="#" onclick="downloadxml(\''+url+'\')"><i class="ace-icon fa fa-download" alt="Download"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="#" onclick="viewPdf(\''+url2+'\')"><i class="ace-icon fa fa-file-pdf-o" alt="Ver PDF"></i></a>';
                return html;
            }

            function imageFormat2(cellvalue, options, rowObject) 
            {
                //return '<img src="'+cellvalue+'" />&nbsp;&nbsp;&nbsp;&nbsp;<img src="'+cellvalue+'" />';
                var serie = rowObject[4];
                var client = rowObject[8];
                var ruta = rowObject[5];
                var dir_principal = rowObject[22];
                var id_listaprecios = rowObject[26];
                var id_listadescuentos = rowObject[27];
                var id_listapromo = rowObject[28];

                //var html = '';
                //if(ruta == "--")
                //{

                  var html = '';

            if($("#permiso_registrar").val() == 1)
                  html = '<input type="checkbox" aling="center" class="checkbox-asignator permiso_registrar" id="'+serie+'" value="'+dir_principal+'" data-id_listaprecios="'+id_listaprecios+'" data-id_listadescuentos="'+id_listadescuentos+'" data-id_listapromo="'+id_listapromo+'" />';

                  if(!dir_principal) html = "";
                //}
                //else
                //{
                 // var html = "";
                //}
                return html;//EDG
            }
          
            function aceSwitch(cellvalue, options, cell) {
                setTimeout(function() {
                    $(cell).find('input[type=checkbox]')
                        .addClass('ace ace-switch ace-switch-5')
                        .after('<span class="lbl"></span>');
                }, 0);
            }
            //enable datepicker
            function pickDate(cellvalue, options, cell) {
                setTimeout(function() {
                    $(cell).find('input[type=text]')
                        .datepicker({
                            format: 'yyyy-mm-dd',
                            autoclose: true
                        });
                }, 0);
            }

            function beforeDeleteCallback(e) {
                var form = $(e[0]);
                if (form.data('styled')) return false;

                form.closest('.ui-jqdialog').find('.ui-jqdialog-titlebar').wrapInner('<div class="widget-header" />')
                style_delete_form(form);

                form.data('styled', true);
            }

            function reloadPage() {
                var grid = $(grid_selector);
                $.ajax({
                    url: "index.php",
                    dataType: "json",
                    success: function(data) {
                        grid.trigger("reloadGrid", [{
                            current: true
                        }]);
                    },
                    error: function() {}
                });
            }

            function beforeEditCallback(e) {
                var form = $(e[0]);
                form.closest('.ui-jqdialog').find('.ui-jqdialog-titlebar').wrapInner('<div class="widget-header" />')
                style_edit_form(form);
            }

            //it causes some flicker when reloading or navigating grid
            //it may be possible to have some custom formatter to do this as the grid is being created to prevent this
            //or go back to default browser checkbox styles for the grid
            function styleCheckbox(table) {}

            //unlike navButtons icons, action icons in rows seem to be hard-coded
            //you can change them like this in here if you want
            function updateActionIcons(table) {}

            //replace icons with FontAwesome icons like above

            function updatePagerIcons(table) {}

            function enableTooltips(table) {
                $('.navtable .ui-pg-button').tooltip({
                    container: 'body'
                });
                $(table).find('.ui-pg-div').tooltip({
                    container: 'body'
                });
            }

            //var selr = jQuery(grid_selector).jqGrid('getGridParam','selrow');

            $(document).one('ajaxloadstart.page', function(e) {
                $(grid_selector).jqGrid('GridUnload');
                $('.ui-jqdialog').remove();
            });
        });

        //////////////////////////////////////////////////////////hace un reload del grid llamando al php en la carpera "c" para hacer la consulta//////////////////////////////////////////////////////////
        function ReloadGrid() {
        console.log("criterio:", $("#txtCriterio").val());
        console.log("id_proveedor:", $("#cboProveedor_busq").val());
        console.log("ruta_clientes:", $("#ruta_clientes").val());
        console.log("cve_proveedor:", $("#cve_proveedor").val());
        console.log("almacen:", $("#almacenes").val());
        console.log("codigo:", $("#cp_search").val());

            $('#grid-table').jqGrid('clearGridData')
                .jqGrid('setGridParam', {
                    postData: {
                        criterio: $("#txtCriterio").val(),
                        id_proveedor: $("#cboProveedor_busq").val(),
                        ruta_clientes: $("#ruta_clientes").val(),
                        cve_proveedor: $("#cve_proveedor").val(),
                        almacen: $("#almacenes").val(),
                        codigo: $("#cp_search").val(),
                    },
                    datatype: 'json',
                    page: 1
                })
                .trigger('reloadGrid', [{
                    current: true
                }]);
            listas_selects();
        }

        function ReloadGrid1() {
            $('#grid-table2').jqGrid('clearGridData')
                .jqGrid('setGridParam', {
                    postData: {
                        criterio: $("#txtCriterio1").val(),
                    },
                    datatype: 'json',
                    page: 1
                })
                .trigger('reloadGrid', [{
                    current: true
                }]);
        }

        function downloadxml(url) {
            var win = window.open(url, '_blank');
            win.focus();
        }

        function viewPdf(url) {
            var win = window.open(url, '_blank');
            win.focus();
        }

        $modal0 = null;

        function borrar(_codigo) {
            swal({
                    title: "¿Está seguro que desea borrar el cliente?",
                    text: "Está a punto de borrar un cliente y esta acción no se puede deshacer",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    confirmButtonText: "Borrar",
                    cancelButtonText: "Cancelar",
                    closeOnConfirm: true
                },
                function() {
                    $.ajax({
                        type: "POST",
                        dataType: "json",
                        data: {
                            Cve_Clte: _codigo,
                            action: "delete"
                        },
                        beforeSend: function(x) {
                            if (x && x.overrideMimeType) {
                                x.overrideMimeType("application/json;charset=UTF-8");
                            }
                        },
                        url: '/api/clientes/update/index.php',
                        success: function(data) {
                            if (data.success == true) {
                                //$('#codigo').prop('disabled', true);
                                ReloadGrid();
                                ReloadGrid1();
                            }
                        }
                    });
                });


        }

        $("#cboProveedor_busq").change(function(){

            ReloadGrid();

        });

        function editar(_codigo) {
            $("#_title").html('<h3>Editar Cliente</h3>');

            console.log("_codigo: ", _codigo);

            $.ajax(
              {
                type: "POST",
                dataType: "json",
                url: '/api/clientes/update/index.php',
                data: {
                    codigo: _codigo,
                    action: "load"
                },
                beforeSend: function(x) {if (x && x.overrideMimeType) {x.overrideMimeType("application/json;charset=UTF-8"); }
                },
                success: function(data) {
                    console.log("data.destinatarios: ", data.destinatarios);
                    console.log("data: ", data);
                    if (data.success == true) {
                        var destinatarios = data.destinatarios,
                            selector = document.getElementById("direccion_envio");
                        $('#txtClaveCliente').prop('disabled', true);
                        $("#consecutivo").val(data.id_cliente);
                        $("#txtClaveCliente").val(data.Cve_Clte);
                        $("#txtClaveClienteProv").val(data.Cve_CteProv);
                        $("#txtRazonSocial").val(data.RazonSocial);
                        $("#txtNombreCorto").val(data.RazonComercial);
                        $("#txtEncargado").val(data.Encargado);
                        $("#txtReferencia").val(data.Referencia);
                        $("#txtCalleNumero").val(data.CalleNumero);
                        $("#txtColonia").val(data.Colonia);
                        $("#txtCod").val(data.CodigoPostal);
                        //$("txtCod").change();
                        $("#txtDepart").val(data.departamento);
                        $("#txtMunicipio").val(data.des_municipio);
                        $("#txtPais").val(data.Pais);
                        $("#txtRFC").val(data.RFC);
                        $("#txtTelefono1").val(data.Telefono1);
                        $("#txtTelefono2").val(data.Telefono2);
                        $("#cboProveedor").val(data.ID_Proveedor);
                        $("#email_cliente").val(data.email_cliente);
                        $("#txtLatitud").val(data.latitud);
                        $("#txtLongitud").val(data.longitud);

                        $("#limite_credito").val(data.limite_credito);
                        $("#dias_credito").val(data.dias_credito);
                        $("#credito_actual").val(data.credito_actual);
                        $("#saldo_inicial").val(data.saldo_inicial);
                        $("#saldo_actual").val(data.saldo_actual);

                        $("#almacenp").val(data.almacenp);
                        $("#txtContacto").val(data.contacto);
                        $("#btnCancel").show();

                        if (parseInt(data.credito) === 1) {
                            $("#credito_on_off").iCheck('check');
                        }

                        if (parseInt(data.validar_gps) === 1) {
                            $("#validar_gps").iCheck('check');
                        }

                        if (parseInt(data.cliente_general) === 1) {
                            $("#cliente_general").iCheck('check');
                        }

                        $("#grupocliente").val(data.ClienteGrupo);
                        $("#grupocliente").trigger('change');
                        var timer = null;
                        timer = setTimeout(function(){
                            $("#tipocliente").val(data.ClienteTipo);
                            clearTimeout(timer); //cancel the previous timer.
                            $("#tipocliente").trigger('change');
                            timer = setTimeout(function(){
                                $("#tipocliente2").val(data.ClienteTipo2);
                                clearTimeout(timer); //cancel the previous timer.
                                timer = null;
                            },1000);
                        },1000);

/*
                        $("#txtCondicionPago").val(data.CondicionPago);
                        $("#cboTipoCliente").val(data.ClienteTipo);
                        $("#cboZona").val(data.ZonaVenta);
*/
                        destinatarios.forEach(function(el, i) {
                            var option = document.createElement('option');
                            option.innerHTML = el.texto;
                            if (el.id_destinatario === data.id_destinatario) {
                                option.setAttribute('selected', 'selected');
                                option.value = el.value + '|1';
                            } else {
                                option.value = el.value + '|0';
                            }
                            selector.append(option);
                            //console.log("**********************************");
                            //console.log("data.dir_principal. ", data.dir_principal);
                            //console.log("el.dir_principal. ", el.dir_principal);
                            if (parseInt(el.dir_principal) === 1) {
                                $("#usar_direccion").iCheck('check');
                                //option.setAttribute('selected', 'selected');
                                //option.value = el.value + '|1';
                                //$("#usar_direccion").prop("disabled", true);
                                $("#direccion_principal").hide();
                                //console.log("data.dir_principal. ", data.dir_principal);
                            }


                        });
/*
                        console.log(data);
                        if (parseInt(data.dir_principal) === 1) {
                            $("#usar_direccion").iCheck('check');
                            console.log("data.direccion. ", data.direccion);
                        }
*/
                        $('#list').removeAttr('class').attr('class', '');
                        $('#list').addClass('animated');
                        $('#list').addClass("fadeOutRight");
                        $('#list').hide();

                        $('#FORM').show();
                        $('#FORM').removeAttr('class').attr('class', '');
                        $('#FORM').addClass('animated');
                        $('#FORM').addClass("fadeInRight");

                        $(".chosen-select").trigger("chosen:updated");

                        $("#hiddenAction").val("edit");
                    }
                }
            });
        }

        function cancelar() {
            $(':input', '#myform')
                .removeAttr('checked')
                .removeAttr('selected')
                .not(':button, :submit, :reset, :hidden, :radio, :checkbox')
                .val('');
            $('#FORM').removeAttr('class').attr('class', '');
            $('#FORM').addClass('animated');
            $('#FORM').addClass("fadeOutRight");
            $('#FORM').hide();

            $('#list').show();
            $('#list').removeAttr('class').attr('class', '');
            $('#list').addClass('animated');
            $('#list').addClass("fadeInRight");
            $('#list').addClass("wrapper");
            $('#list').addClass("wrapper-content");
            $("#direccion_principal").show();
            $("#usar_direccion").iCheck('uncheck');
            $("#credito_on_off").iCheck('uncheck');
            $("#validar_gps").iCheck('uncheck');
            $("#cliente_general").iCheck('uncheck');
            $("#direccion_envio option").remove();
        }

        function agregar() //lilo
        {
          $("#_title").html('<h3>Agregar Cliente*</h3>');
          $('#txtClaveCliente').prop('disabled', false);

          $("#txtDepart").val("");
          $("#txtMunicipio").val("");

          $(':input', '#myform')
              .removeAttr('checked')
              .removeAttr('selected')
              .not(':button, :submit, :reset, :hidden, :radio, :checkbox')
              .val('');

          $('#list').removeAttr('class').attr('class', '');
          $('#list').addClass('animated');
          $('#list').addClass("fadeOutRight");
          $('#list').hide();
          $('txtCod').val("");
          $("#direccion_envio").empty();
          $(".chosen-select").trigger("chosen:updated");

          $('#FORM').show();
          $('#FORM').removeAttr('class').attr('class', '');
          $('#FORM').addClass('animated');
          $('#FORM').addClass("fadeInRight");
          $("#hiddenAction").val("add");
          $("#usar_direccion").iCheck('check');

          $.ajax({
            type: "POST",
            dataType: "json",
            cache: false,
            url: '/api/clientes/update/index.php',
            data: {
              action: "getConsecutivo"
            },
            beforeSend: function(x) {if (x && x.overrideMimeType) {x.overrideMimeType("application/json;charset=UTF-8");}
            },
            success: function(data) {
                //console.log(data);
              $("#consecutivo").val(data);
            }
          });
        }




        var l = $('.ladda-button');
        l.click(function() 
        {
          var destinatarios = [], usar_direccion  = $("#usar_direccion").iCheck('update')[0].checked ? 1 : 0,
                                  credito_on_off  = $("#credito_on_off").iCheck('update')[0].checked ? 1 : 0,
                                  validar_gps     = $("#validar_gps").iCheck('update')[0].checked ? 1 : 0,
                                  cliente_general = $("#cliente_general").iCheck('update')[0].checked ? 1 : 0;
                                  //cliente_tipo_traslado = $("#cliente_tipo_traslado").iCheck('update')[0].checked ? 1 : 0

          document.querySelectorAll('#direccion_envio_band option').forEach(function(el, i) 
          {
              if (el.value.length < 1) 
              {
                return;
              }
              destinatarios.push(el.value);
          });

          
          if ($('#txtRazonSocial').val().trim() === '') 
          {
            swal("Error", "Ingresa la razón social", "error");
          } 
          if ($('#txtNombreCorto').val().trim() === '') 
          {
            swal("Error", "Ingresa un nombre comercial", "error");
          } 
          else if ($("#txtCalleNumero").val() == "") 
          {
            swal("Error", "Ingresa la dirección", "error");
          }
          /*
          else if ($("#txtCod").val()=="") 
          {
            swal("Error", "Ingresa el código postal", "error");
          }	
          */
          else if ($("#txtPais").val() == "") 
          {
            swal("Error", "Ingresa el país", "error");
          } 
          else if ($("#almacenp").val() == "")  
          {
            swal("Error", "Selecciona el almacén", "error");
          } 
          //else if ($("#cboProveedor").val() == "")  
          //{
          //  swal("Error", "Seleccione un Proveedor", "error");
          //} 
          else if ($("#txtClaveCliente").val() == "")  
          {
            swal("Error", "Debe Ingresar una Clave para el cliente", "error");
          } 
          else 
          {
            $("#btnCancel").hide();
            if ($("#hiddenAction").val() == "add") 
            {
              $.ajax({
                type: "POST",
                dataType: "json",
                url: '/api/clientes/update/index.php',
                data: {
                  action: "exists",
                  codigo: $("#txtClaveCliente").val(),
                },
                beforeSend: function(x) {if (x && x.overrideMimeType) {x.overrideMimeType("application/json;charset=UTF-8");}
                },
                success: function(data) {
                  if (!data.success) 
                  {
                    /*
                        CondicionPago: $("#txtCondicionPago").val(),
                        ZonaVenta: $("#cboZona").val(),
                        ClienteTipo: $("#cboTipoCliente").val(),
                    */
                    console.log("Creando Cliente");
                    //return;
                    $.ajax({
                      type: "POST",
                      dataType: "json",
                      url: '/api/clientes/update/index.php',
                      data: {

                        action: "add",
                        Cve_Clte: $("#txtClaveCliente").val(),
                        Cve_CteProv: $("#txtClaveClienteProv").val(),
                        RazonSocial: $("#txtRazonSocial").val(),
                        NombreCorto: $("#txtNombreCorto").val(),
                        Encargado: $("#txtEncargado").val(),
                        Referencia: $("#txtReferencia").val(),
                        CalleNumero: $("#txtCalleNumero").val(),
                        Colonia: $("#txtColonia").val(),
                        CodigoPostal: $("#txtCod").val(),
                        Ciudad: $("#txtDepart").val(),
                        Estado: $("#txtMunicipio").val(),
                        Pais: $("#txtPais").val(),
                        RFC: $("#txtRFC").val(),
                        Telefono1: $("#txtTelefono1").val(),
                        Telefono2: $("#txtTelefono2").val(),
                        email_cliente: $("#email_cliente").val(),
                        txtLatitud: $("#txtLatitud").val(),
                        txtLongitud: $("#txtLongitud").val(),
                        email_destinatario: $("#txtEmailDest").val(),
                        txtLatitudDest: $("#txtLatitudDest").val(),
                        txtLongitudDest: $("#txtLongitudDest").val(),
                        ID_Proveedor: $("#cboProveedor").val(),
                        almacenp: $("#almacenp").val(),
                        Contacto: $("#txtContacto").val(),
                        destinatarios: destinatarios,
                        usar_direccion: usar_direccion,
                        credito: credito_on_off,
                        limite_credito: $("#limite_credito").val(),
                        dias_credito: $("#dias_credito").val(),
                        credito_actual: $("#credito_actual").val(),
                        saldo_inicial: $("#saldo_inicial").val(),
                        saldo_actual: $("#saldo_actual").val(),
                        grupocliente: $("#grupocliente").val(),
                        tipocliente: $("#tipocliente").val(),
                        tipocliente2: $("#tipocliente2").val(),
                        //cliente_tipo_traslado: cliente_tipo_traslado,
                        cliente_general: cliente_general,
                        validar_gps: validar_gps
                      },
                      beforeSend: function(x) {if (x && x.overrideMimeType) {x.overrideMimeType("application/json;charset=UTF-8");}
                      },
                      success: function(data) {
                        //console.log(data);
                        //console.log("SUCCESS 2: ", data);
                        if (data.success) 
                        {
                          ReloadGrid();
                          swal("Exito", "Datos Guardados con exito.", "success");
                          location.reload();
                        } 
                        else 
                        {
                          swal("Error", "Ocurrión un error al guardar el cliente", "error");
                          
                        }
                        $("#btnCancel").show();
                      }
                    });
                  }
                  else
                  {
                          swal("Error", "El cliente "+$("#txtClaveCliente").val()+" ya existe", "error");
                  }
                }
              });
            } 
            else 
            {
/*
                  CondicionPago: $("#txtCondicionPago").val(),
                  ClienteTipo: $("#cboTipoCliente").val(),
                  ZonaVenta: $("#cboZona").val(),
*/
            console.log("USAR DIRECCION = ", usar_direccion);
            console.log("Cve_Clte = ", $("#txtClaveCliente").val());
            console.log("txtLatitud:", $("#txtLatitud").val());
            console.log("txtLongitud:", $("#txtLongitud").val());
              $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                  action: "edit",
                  Cve_Clte: $("#txtClaveCliente").val(),
                  Cve_CteProv: $("#txtClaveClienteProv").val(),
                  RazonSocial: $("#txtRazonSocial").val(),
                  NombreCorto: $("#txtNombreCorto").val(),
                  Encargado: $("#txtEncargado").val(),
                  Referencia: $("#txtReferencia").val(),
                  CalleNumero: $("#txtCalleNumero").val(),
                  Colonia: $("#txtColonia").val(),
                  CodigoPostal: $("#txtCod").val(),
                  Ciudad: $("#txtDepart").val(),
                  Estado: $("#txtMunicipio").val(),
                  Pais: $("#txtPais").val(),
                  RFC: $("#txtRFC").val(),
                  Telefono1: $("#txtTelefono1").val(),
                  Telefono2: $("#txtTelefono2").val(),
                  email_cliente: $("#email_cliente").val(),
                  txtLatitud: $("#txtLatitud").val(),
                  txtLongitud: $("#txtLongitud").val(),
                  email_destinatario: $("#txtEmailDest").val(),
                  txtLatitudDest: $("#txtLatitudDest").val(),
                  txtLongitudDest: $("#txtLongitudDest").val(),
                  ID_Proveedor: $("#cboProveedor").val(),
                  almacenp: $("#almacenp").val(),
                  Contacto: $("#txtContacto").val(),
                  direccion_envio: $("#direccion_envio").val(),
                  destinatarios: destinatarios,
                  usar_direccion: usar_direccion,
                  credito: credito_on_off,
                  limite_credito: $("#limite_credito").val(),
                  dias_credito: $("#dias_credito").val(),
                  credito_actual: $("#credito_actual").val(),
                  saldo_inicial: $("#saldo_inicial").val(),
                  saldo_actual: $("#saldo_actual").val(),
                  grupocliente: $("#grupocliente").val(),
                  tipocliente: $("#tipocliente").val(),
                  tipocliente2: $("#tipocliente2").val(),
                  //cliente_tipo_traslado: cliente_tipo_traslado,
                  cliente_general: cliente_general,
                  validar_gps: validar_gps
                },
                beforeSend: function(x) {if (x && x.overrideMimeType) {x.overrideMimeType("application/json;charset=UTF-8");}
                },
                url: '/api/clientes/update/index.php',
                success: function(data) {
                console.log("SUCCESS 3: ", data);
                  if (data.success) 
                  {

                    cancelar();
                    ReloadGrid();
                    swal("Exito", "Datos Guardados con exito.", "success");
                  } 
                  else 
                  {
                    swal("Error", "Ocurrió un error al editar el cliente", "error");
                  }
                  $("#btnCancel").show();

                }, error: function(data) {
                    console.log("ERROR: ", data);
                }
              });
            }
          }
        });
    </script>
    
    <script>

        $("#grupocliente").change(function() {
            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    grupocliente: $("#grupocliente").val(),
                    action: "getClasificacion1"
                },
                beforeSend: function(x) {
                    if (x && x.overrideMimeType) {
                        x.overrideMimeType("application/json;charset=UTF-8");
                    }
                },
                url: "/api/clientes/update/index.php",
                success: function(data) {
                    if (data.success == true) {
                        $("#tipocliente").empty();
                        $("#tipocliente2").empty();
                        $("#tipocliente").append(data.lista_options);
                    }
                }
            });
        });

        $("#tipocliente").change(function() {
            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    tipocliente: $("#tipocliente").val(),
                    action: "getClasificacion2"
                },
                beforeSend: function(x) {
                    if (x && x.overrideMimeType) {
                        x.overrideMimeType("application/json;charset=UTF-8");
                    }
                },
                url: "/api/clientes/update/index.php",
                success: function(data) {
                    if (data.success == true) {
                        $("#tipocliente2").empty();
                        $("#tipocliente2").append(data.lista_options);
                    }
                }
            });
        });

        $("#txtCod").change(function() {
            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    codigo: $("#txtCod").val(),
                    action: "getDane"
                },
                beforeSend: function(x) {
                    if (x && x.overrideMimeType) {
                        x.overrideMimeType("application/json;charset=UTF-8");
                    }
                },
                url: "/api/clientes/update/index.php",
                success: function(data) {
                            console.log("SUCCESS", data);
                            if (data.success == true) {
                                $("#txtDepart").prop("disabled", true);
                                $("#txtMunicipio").prop("disabled", true);
                                $("#txtDepart").val(data.departamento);
                                $("#txtMunicipio").val(data.municipio);
                            }
                            else
                            {
                                $("#txtDepart").prop("disabled", false);
                                $("#txtMunicipio").prop("disabled", false);
                                $("#txtDepart").val("");
                                $("#txtMunicipio").val("");
                            }
                }
            });
        });
    </script>

    <script>
        $(function($) {
            var grid_selector = "#grid-table2";
            var pager_selector = "#grid-pager2";

            //resize to fit page size
            $(window).on('resize.jqGrid', function() {
                    $(grid_selector).jqGrid('setGridWidth', $(".page-content").width() - 60);
                })
                //resize on sidebar collapse/expand
            var parent_column = $(grid_selector).closest('[class*="col-"]');
            $(document).on('settings.ace.jqGrid', function(ev, event_name, collapsed) {
                if (event_name === 'sidebar_collapsed' || event_name === 'main_container_fixed') {
                    //setTimeout is for webkit only to give time for DOM changes and then redraw!!!
                    setTimeout(function() {
                        $(grid_selector).jqGrid('setGridWidth', parent_column.width());
                    }, 0);
                }
            })

            $(grid_selector).jqGrid({
                url: '/api/clientes/lista/index_i.php',
                datatype: "json",
                shrinkToFit: false,
                height: 'auto',
                postData: {
                    criterio: $("#txtCriterio1").val()
                },
                mtype: 'POST',
                colNames: ['ID', 'Clave de Cliente', 'Almacen', 'Razón Social', "Recuperar"],
                /*'Calle y Numero','Colonia','Ciudad','Estado','Pais','Codigo Postal','RFC','Telefono1','Telefono2'*/
                colModel: [
                    //{name:'id',index:'id', width:60, sorttype:"int", editable: false},
                    {
                        name: 'id_cliente',
                        index: 'id_cliente',
                        width: 110,
                        editable: false,
                        sortable: false,
                        hidden: true
                    },

                    {
                        name: 'Cve_Clte',
                        index: 'Cve_Clte',
                        width: 150,
                        editable: false,
                        sortable: false
                    }, {
                        name: 'almacenp',
                        index: 'almacenp',
                        width: 40,
                        editable: false,
                        sortable: false,
                        hidden: false
                    }, {
                        name: 'RazonSocial',
                        index: 'RazonSocial',
                        width: 150,
                        editable: false,
                        sortable: false
                    }, {
                        name: 'myac',
                        index: '',
                        width: 80,
                        fixed: true,
                        sortable: false,
                        resize: false,
                        formatter: imageFormat
                    },
                ],
                rowNum: 10,
                rowList: [10, 20, 30],
                pager: pager_selector,
                sortname: 'id_cliente',
                viewrecords: true,
                sortorder: "desc"
            });

            // Setup buttons
            $("#grid-table2").jqGrid('navGrid', '#grid-pager2', {
                edit: false,
                add: false,
                del: false,
                search: false
            }, {
                height: 200,
                reloadAfterSubmit: true
            });


            $(window).triggerHandler('resize.jqGrid'); //trigger window resize to make the grid get the correct size
            //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

            //////////////////////////////////////////////////////////aqui se construye la funcion de cada item del grid//////////////////////////////////////////////////////////
            function imageFormat(cellvalue, options, rowObject) {
                //return '<img src="'+cellvalue+'" />&nbsp;&nbsp;&nbsp;&nbsp;<img src="'+cellvalue+'" />';
                var serie = rowObject[0];
                var correl = rowObject[4];
                var url = "x/?serie=" + serie + "&correl=" + correl;
                var url2 = "v/?serie=" + serie + "&correl=" + correl;

                var id_cliente = rowObject[0];

                $("#hiddenIDCliente").val(id_cliente);
                // la funcion downloadxml ejecuta el index.php que esta en la carpeta x pasandole la serie y el correlativo via get
                // la funcion viewPdf ejecuta el index.php que esta en la carpeta v pasandole la serie y el correlativo via get

                var html = '';
                //html += '<a href="#" onclick="ver(\''+serie+'\')"><i class="fa fa-eye" alt="Ver"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
                html += '<a href="#" onclick="recovery(\'' + id_cliente + '\')"><i class="fa fa-check" alt="Borrar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';

                //return '<a href="#" onclick="downloadxml(\''+url+'\')"><i class="ace-icon fa fa-download" alt="Download"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="#" onclick="viewPdf(\''+url2+'\')"><i class="ace-icon fa fa-file-pdf-o" alt="Ver PDF"></i></a>';
                return html;
            }

            function aceSwitch(cellvalue, options, cell) {
                setTimeout(function() {
                    $(cell).find('input[type=checkbox]')
                        .addClass('ace ace-switch ace-switch-5')
                        .after('<span class="lbl"></span>');
                }, 0);
            }
            //enable datepicker
            function pickDate(cellvalue, options, cell) {
                setTimeout(function() {
                    $(cell).find('input[type=text]')
                        .datepicker({
                            format: 'yyyy-mm-dd',
                            autoclose: true
                        });
                }, 0);
            }

            function beforeDeleteCallback(e) {
                var form = $(e[0]);
                if (form.data('styled')) return false;

                form.closest('.ui-jqdialog').find('.ui-jqdialog-titlebar').wrapInner('<div class="widget-header" />')
                style_delete_form(form);

                form.data('styled', true);
            }



            function reloadPage() {
                var grid = $(grid_selector);
                $.ajax({
                    url: "index.php",
                    dataType: "json",
                    success: function(data) {
                        grid.trigger("reloadGrid", [{
                            current: true
                        }]);
                    },
                    error: function() {}
                });
            }

            function beforeEditCallback(e) {
                var form = $(e[0]);
                form.closest('.ui-jqdialog').find('.ui-jqdialog-titlebar').wrapInner('<div class="widget-header" />')
                style_edit_form(form);
            }

            //it causes some flicker when reloading or navigating grid
            //it may be possible to have some custom formatter to do this as the grid is being created to prevent this
            //or go back to default browser checkbox styles for the grid
            function styleCheckbox(table) {}

            //unlike navButtons icons, action icons in rows seem to be hard-coded
            //you can change them like this in here if you want
            function updateActionIcons(table) {}

            //replace icons with FontAwesome icons like above

            function updatePagerIcons(table) {}

            function enableTooltips(table) {
                $('.navtable .ui-pg-button').tooltip({
                    container: 'body'
                });
                $(table).find('.ui-pg-div').tooltip({
                    container: 'body'
                });
            }

            //var selr = jQuery(grid_selector).jqGrid('getGridParam','selrow');

            $(document).one('ajaxloadstart.page', function(e) {
                $(grid_selector).jqGrid('GridUnload');
                $('.ui-jqdialog').remove();
            });
        });
    </script>

    <script>
        function recovery(_codigo) {
            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    id_cliente: _codigo,
                    action: "recovery"
                },
                beforeSend: function(x) {
                    if (x && x.overrideMimeType) {
                        x.overrideMimeType("application/json;charset=UTF-8");
                    }
                },
                url: '/api/clientes/update/index.php',
                success: function(data) {
                    if (data.success == true) {
                        //$('#codigo').prop('disabled', true);
                        ReloadGrid();
                        ReloadGrid1();
                    }
                }
            });
            /*$.post( "/api/usuarios/update/index.php",
             {
             id_user : _codigo,
             action : "delete"

             } ,function( data ) {
             alert(data);
             });*/
        }
    </script>

    <script>
        $("#txtClaveCliente").keyup(function(e) 
        {
          var claveCode = $(this).val();
          var claveCodeRegexp = new RegExp("^[a-zA-Z0-9]{1,20}$");
          if($(this).val() != "")
          {
            if (claveCodeRegexp.test(claveCode)) 
            {
              $("#CodeMessage").html("");
              $("#btnSave").prop('disabled', false);
              var Cve_Clte = $(this).val();
              $.ajax({
                type: "POST",
                dataType: "json",
                url: '/api/clientes/update/index.php',
                data: {
                  action: "exists",
                  id_almacen: <?php echo $_SESSION['id_almacen']; ?>,
                  Cve_Clte: Cve_Clte,
                },
                beforeSend: function(x) {if (x && x.overrideMimeType) {x.overrideMimeType("application/json;charset=UTF-8");}
                },
                success: function(data) {
                  if (data.success == false) 
                  {
                    $("#CodeMessage").html("");
                    $("#btnSave").prop('disabled', false);
                  }
                  else 
                  {
                    console.log("success_otro_almacen = ",data.success_otro_almacen);
                    if(data.success_otro_almacen == false)
                        $("#CodeMessage").html("La clave del cliente ya existe en este almacén");
                    else
                    {
                        $("#CodeMessage").html("La clave del cliente ya existe en Otro almacén, ¿Desea Copiarlo a este almacén? <input type='button' id='si_copiar' class='btn btn-primary' value='Si' > <input type='button' id='no_copiar' class='btn btn-danger' value='No' >");

                            $("#si_copiar").click(function(){
                                console.log("copiar cliente");
                              $.ajax({
                                type: "POST",
                                dataType: "json",
                                url: '/api/clientes/update/index.php',
                                data: {
                                  action : "CopiarClienteA_Almacen",
                                  cve_cliente : $("#txtClaveCliente").val(),
                                  id_almacen: <?php echo $_SESSION['id_almacen']; ?>
                                },
                                beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }},
                                success: function(data) {
                                  if (data.success == true) 
                                  {
                                    swal("Exito","Se ha copiado El cliente "+$("#txtClaveCliente").val(), "success");
                                    window.location.reload();
                                  }
                                }
                              });

                            });

                            $("#no_copiar").click(function(){
                                $("#txtClaveCliente").val("");
                                $("#CodeMessage").html("");
                            });
                    }
                    $("#btnSave").prop('disabled', true);
                  }
                }
              });
            } 
            else
            {
              $("#CodeMessage").html("Por favor, ingresar una clave de cliente válida");
              $("#btnSave").prop('disabled', true);
            }
          }
          else
          {
            $("#CodeMessage").html("");
            $("#btnSave").prop('disabled', false);
          }
        });
      
        
        $("#btn-asignarTodo").on("click", function(){
          $("input[type=checkbox].checkbox-asignator").each(function(i, e){
            if($("#btn-asignarTodo").prop("checked") == false)
            {
              $("input[type=checkbox].checkbox-asignator").prop("checked", true);
              //
              if($("input[type=checkbox].checkbox-asignator").prop("checked") == true){$("input[type=checkbox].checkbox-asignator").prop("checked", false);}
              else{$("input[type=checkbox].checkbox-asignator").prop("checked", true);}
            }
            else
            {
              $("input[type=checkbox].checkbox-asignator").prop("checked", false);
              //
              if($("input[type=checkbox].checkbox-asignator").prop("checked") == true){$("input[type=checkbox].checkbox-asignator").prop("checked", false);}
              else{$("input[type=checkbox].checkbox-asignator").prop("checked", true);}
            }
          });
        });
      
        $("#guardar_ruta").on("click", function(){
            var ruta = $("#ruta_asignada").val();
            var arr_clientes = [];
            $("#tabla_usuarios_select>tbody>tr").each(function(i, e){
              arr_clientes.push($(this).attr('id'));
            });
            if(arr_clientes.length == 0 || ruta == "")
            {
              swal("Error","Seleccione una Ruta y Clientes para su asignacion", "error");
            }
            else
            {
              $.ajax({
                type: "POST",
                dataType: "json",
                url: '/api/clientes/update/index.php',
                data: {
                  action : "asignarRutaACliente",
                  clientes : arr_clientes,
                  ruta : ruta
                },
                beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }},
                success: function(data) {
                  if (data.success == true) 
                  {
                    console.log("LOL2");
                    swal("Exito","Ruta Asignada a los Usuarios", "success");
                    $("#modal_asignacion_ruta").modal('hide');
                    ReloadGrid();
                  }
                }
              });
            }
        });
      
        function borrar_cliente_asignado(id_tr)
        {
          $("#tabla_usuarios_select>tbody>tr[id='"+id_tr+"']").remove();
          $("input[type=checkbox].checkbox-asignator[id='"+id_tr+"']").prop("checked", false);
        }
      

        function listas_selects()
        {
            //$("#lista_precios_select").val();
            //$("#lista_descuentos_select").val();
            //$("#lista_promociones_select").val();

            $.ajax({
                url: '/api/clientes/update/index.php',
                dataType: 'json',
                //cache: false,
                data: 
                {
                    action: 'listas_selects',
                    almacen: $("#almacenes").val(),
                    type: 'GET'
                },
                 }).done(function(data) 
                 {
                    console.log("SUCCESS listas_selects: ", data);
                    $("#lista_precios_select").empty();
                    $("#lista_precios_select").append(data.lista_precios);

                    $("#lista_descuentos_select").empty();
                    $("#lista_descuentos_select").append(data.lista_descuentos);

                    $("#lista_promociones_select").empty();
                    $("#lista_promociones_select").append(data.lista_promociones);
                 }).fail(function(data) 
                 {
                    console.log("FAIL listas_selects: ", data);
                    //swal("Éxito", "Lista Asignada Correctamente", "success");
                 });

        }

        function asignar()
        {
          //$("#tabla_usuarios_select>tbody").empty();
          console.log("asignar()");
          var folios = [];
          var asignados = [];//EDG
          
          var arr = [];
          var arr2 = [];
          var arr3 = [];
          var arr4 = [];
          var arr5 = [];
          $("input[type=checkbox].checkbox-asignator").each(function(i, e){
            if($(this).prop("checked") == true)
            {
              arr.push($(this).attr('id'));
              arr2.push($(this).attr('value'));
              arr3.push($(this).data('id_listaprecios'));
              arr4.push($(this).data('id_listadescuentos'));
              arr5.push($(this).data('id_listapromo'));

              console.log("asignar() ->", $(this).attr('id'));
              console.log("asignar() ->", $(this).attr('value'));
              console.log("***********************************");
            }
          });

          console.log("Destinatarios a asignar = ", arr2);

          if (arr2.length === 0)
          {
             swal("Error", "No ha seleccionado ningún cliente", "error");
             return;
          }

          $("#lista_precios_select").val("");
          $("#lista_descuentos_select").val("");
          $("#lista_promociones_select").val("");
          if (arr3.length === 1)
          {
            $("#lista_precios_select").val(arr3[0]);
          }

          if (arr4.length === 1)
          {
            $("#lista_descuentos_select").val(arr4[0]);
          }

          if (arr5.length === 1)
          {
            $("#lista_promociones_select").val(arr5[0]);
          }

          $("#array_destinatarios").val(arr2);
          
          $("#asignar_lista_modal").modal("show");

          /*
          for(var j = 0; j < arr.length; j++)
          {
            //arr.toString();
            if(arr != "")
            {
              $modal0 = $("#modal_asignacion_ruta");
              $modal0.modal('show');
              
              $("#tabla_usuarios_select").find('tbody').append(
                $('<tr id="'+arr[j]+'">'+
                    '<td>'+arr[j]+'</td>'+
                    '<td>'+arr2[j]+'</td>'+
                    '<td> <a href="#" onclick="borrar_cliente_asignado(\'' + arr[j] + '\')"><i class="fa fa-eraser" alt="Borrar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp </td>'+
                  '</tr>')
              );
            }
          }
          */
        }

        $("#asignarLista").click(function(){

            if($("#lista_precios_select").val()=="" && $("#lista_descuentos_select").val()=="" && $("#lista_promociones_select").val()=="")
            {
                swal("Error", "Debe Seleccionar una Lista", "warning");
                return;
            }

            var asignar_todos = 0;
            if($("#btn-asignarTodo").prop("checked") == true)
                asignar_todos = 1;

            console.log("Array Destinatarios a asignar = ", $("#array_destinatarios").val());
            console.log("lista_precios_select = ", $("#lista_precios_select").val());
            console.log("lista_descuentos_select = ", $("#lista_descuentos_select").val());
            console.log("lista_promociones_select = ", $("#lista_promociones_select").val());
            console.log("asignar_todos = ", asignar_todos);
            console.log("modificar_listap = ", $("#modificar_listap").is(":checked"));
            console.log("modificar_listad = ", $("#modificar_listad").is(":checked"));
            console.log("modificar_listagp = ", $("#modificar_listagp").is(":checked"));

            $.ajax({
                url: '/api/clientes/update/index.php',
                dataType: 'json',
                //cache: false,
                data: 
                {
                    action: 'asignarLista',
                    asignar_todos: asignar_todos,
                    criterio: $("#txtCriterio").val(),
                    id_proveedor: $("#cboProveedor_busq").val(),
                    ruta_clientes: $("#ruta_clientes").val(),
                    almacen: $("#almacenes").val(),
                    codigo: $("#cp_search").val(),
                    destinatarios: $("#array_destinatarios").val(),
                    lista_precios_select: $("#lista_precios_select").val(),
                    lista_descuentos_select: $("#lista_descuentos_select").val(),
                    lista_promociones_select: $("#lista_promociones_select").val(),
                    modificar_listap: ($("#modificar_listap").is(":checked"))?1:0,
                    modificar_listad: ($("#modificar_listad").is(":checked"))?1:0,
                    modificar_listagp: ($("#modificar_listagp").is(":checked"))?1:0,
                    type: 'GET'
                },
                 }).done(function(data) 
                 {
                    console.log("SUCESS asignarLista: ", data);
                    swal("Éxito", "Lista Asignada Correctamente", "success");
                    $("#asignar_lista_modal").modal("hide");
                    ReloadGrid();
                    $("#btn-asignarTodo, #modificar_listap, #modificar_listad, #modificar_listagp").prop("checked", false);

                 }).fail(function(data) 
                 {
                    console.log("FAIL asignarLista: ", data);
                    //swal("Éxito", "Lista Asignada Correctamente", "success");
                 });

        });

        function filtrar() 
        {
          ReloadGrid();
          var //filtering = FooTable.get('.footable').use(FooTable.Filtering), // get the filtering component for the table
              filter = $("#almacenes option:selected").text(); // get the value to filter by
            /*              
          if(filter == 'Seleccione el Almacen') 
          { // if the value is "none" remove the filter
            filtering.removeFilter('Almacen');
          }
          else
          { // otherwise add/update the filter.
            filtering.addFilter('Almacen', filter);
          }
          if($("#txtCriterio").val() == "") 
          {
            filtering.removeFilter('Clave');
            filtering.removeFilter('Razon Social');
          }
          else 
          {
            filtering.addFilter('Clave', $("#txtCriterio").val());
            filtering.addFilter('Razon Social', $("#txtCriterio").val());
          }
          if($("#ciudades").val() == "") 
          {
            filtering.removeFilter('Ciudad');
          }
          else
          {
            filtering.addFilter('Ciudad', $("#ciudades").val());
          }
          filtering.filter();
          */
        }


        $('.footable').footable({


            breakpoints: {
                mamaBear: 1200,
                babyBear: 600
            }



        });

        $("#txtCriterio").keyup(function(event) {
            //if (event.keyCode == 13) {
            var sizeBusq = $(this).val();
            if (sizeBusq.length == 4) {
                $("#buscarA").click();
            }
        });

        $("#ruta_clientes").keyup(function(event) {
            if (event.keyCode == 13) {
                $("#buscarA").click();
            }
        });

        $(document).ready(function() {
            localStorage.setItem("consecutivo_destinatario", 0);

            $("#direccion_envio").on('change', function(e) {

                $("#direccion_envio option").each(function(i, e) {
                    var value = e.value;
                    value = value.split("|");
                    value[8] = "0";
                    value = value.join("|");
                    $(`#direccion_envio option[value='${e.value}']`).attr('value', value);
                });

                var value = e.target.value;
                value = value.split("|");
                value[8] = "1";
                value = value.join("|");
                $(`#direccion_envio option[value='${e.target.value}']`).attr('value', value);
            });


            function isEmail(email) {
              var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
              return regex.test(email);
            }
            var consecutivo_dest = 0;

            $("#guardar_destinatario").on('click', function() {
                //$("#consecutivo_destinatario").val(parseInt($("#consecutivo_destinatario").val()) + parseInt(consecutivo_dest));
                var razon = $("#destinatario_razonsocial").val(),
                    direccion = $("#destinatario_direccion").val(),
                    colonia = $("#destinatario_colonia").val(),
                    postal = $("#destinatario_dane").val(),
                    ciudad = $("#destinatario_ciudad").val(),
                    estado = $("#destinatario_estado").val(),
                    contacto = $("#destinatario_contacto").val(),
                    telefono = $("#destinatario_telefono").val(),
                    emailDest = $("#txtEmailDest").val(),
                    latitudDest = $("#txtLatitudDest").val(),
                    longitudDest = $("#txtLongitudDest").val(),
                    value = `${razon}|${direccion}|${colonia}|${postal}|${ciudad}|${estado}|${contacto}|${telefono}|0|${emailDest}|${latitudDest}|${longitudDest}`,
                    texto = $("#consecutivo_destinatario").val()+` - ${direccion}`,
                    select_direccion = document.getElementById("direccion_envio"),
                    option_direccion = document.createElement('option'),
                    select_direccion_band = document.getElementById("direccion_envio_band"),
                    option_direccion_band = document.createElement('option');

                consecutivo_dest++;
                $("#consecutivo_destinatario").val(parseInt($("#consecutivo_destinatario").val()) + parseInt(consecutivo_dest));
                if((!isEmail($("#txtEmailDest").val()) && $("#txtEmailDest").val()) || (!isEmail($("#email_cliente").val()) && $("#email_cliente").val())){
                    swal('Error', 'Por favor escriba el email correctamente', 'error');
                }else if (razon === '') {
                    swal("Error", "Ingrese Razón social del destinatario", "error");
                } else if (direccion === '') {
                    swal("Error", "Ingrese Dirección del destinatario", "error");
                } else if (colonia === '') {
                    swal("Error", "Ingrese Colonia del destinatario", "error");
                } else if (postal === '') {
                    swal("Error", "Ingrese Código Postal del destinatario", "error");
                } else if (contacto === '') {
                    swal("Error", "Ingrese Contacto del destinatario", "error");
                } else if (telefono === '') {
                    swal("Error", "Ingrese Teléfono social del destinatario", "error");
                } else {
                    option_direccion.value = value;
                    option_direccion.innerHTML = texto;
                    select_direccion.append(option_direccion);
                    select_direccion.value = '';

                    option_direccion_band.value = value;
                    option_direccion_band.innerHTML = texto;
                    select_direccion_band.append(option_direccion_band);
                    select_direccion_band.value = '';
                    $(".chosen-select").trigger('chosen:updated');
                    $("#modal_destinatario").modal('hide');
                }
            });

            $("#agregar_destinatario").on("click", function() {
                $.ajax({
                    url: '/api/clientes/lista/index.php',
                    dataType: 'json',
                    cache: false,
                    data: 
                    {
                        action: 'obtenerClaveDestinatario',
                        type: 'GET'
                    },
                     }).done(function(data) {
                   if (data.clave) {
              $("#consecutivo_destinatario").val(parseInt(data.clave) + parseInt(consecutivo_dest));
            } });
                  
                  /*
                    if (data) {
                        var consecutivo_destinatario = parseInt(localStorage.getItem("consecutivo_destinatarioo")),
                            clave = parseInt(data.clave);
                        if (consecutivo_destinatario === 0) {
                            $("consecutivo_destinatario").val(clave);
                        } else {
                            $("consecutivo_destinatario").val(consecutivo_destinatario + clave);
                        }
                        localStorage.setItem("consecutivo_destinatario", (consecutivo_destinatario + 1));
                    }*/
               
                $("#destinatario_razonsocial").val('');
                $("#destinatario_direccion").val('');
                $("#destinatario_dane").val('');
                $("#destinatario_colonia").val('');
                $("#destinatario_ciudad").val('');
                $("#destinatario_estado").val('');
                $("#destinatario_contacto").val('');
                $("#destinatario_telefono").val('');
                $(".chosen-select").trigger('chosen:updated');
            });

        $("#imprimir_lp").click(function(){
            var lps = [], k = 0;
            $(".check_lp").each(function(i,j){
                if($(this).is(":checked"))
                {
                    console.log("i = ", i, " value = ", j.value);
                    lps[k] = j.value;
                    k++;
                }
            });
                console.log("clientes etiquetas = ", lps);

                //Imprimir_LPs(lps);
            window.open("/api/koolreport/export/reportes/etiquetas/clientes?cve_clientes="+lps+"&compania=<?php echo $_SESSION['cve_cia']; ?>");
        });

            $("#destinatario_dane").change(function() {
                $.ajax({
                    type: "POST",
                    dataType: "json",
                    data: {
                        codigo: $("#destinatario_dane").val(),
                        action: "getDane"
                    },
                    beforeSend: function(x) {
                        if (x && x.overrideMimeType) {
                            x.overrideMimeType("application/json;charset=UTF-8");
                        }
                    },
                    url: "/api/clientes/update/index.php",
                    success: function(data) {
                        if (data.success == true) {
                            $("#destinatario_ciudad").val(data.departamento);
                            $("#destinatario_estado").val(data.municipio);
                        }
                    }
                });
            });

            $('.icheck').iCheck({
                checkboxClass: 'icheckbox_square-green',
                radioClass: 'iradio_square-green'
            });

            $("div.ui-jqgrid-bdiv").css("max-height", $(".page-content").height());

            $(function() {
                $('.chosen-select').chosen();
                $('.chosen-select-deselect').chosen({
                    allow_single_deselect: true
                });
            });


            $("#inactivos").on("click", function() {
                $modal0 = $("#coModal");
                $modal0.modal('show');
            });
            //$("#txtCod").select2();
            $("#txtDepart").prop("disabled", true);
            $("#txtMunicipio").prop("disabled", true);


        });
    </script>

    <script type="text/javascript">
        <?php if(isSCTP()): ?>

        function obtenerClientesSCTP() {
            $("#modal_sctp .fa-spinner").show();
            $("#modal_sctp .success").hide();
            $("#modal_sctp #button_modal_sctp").attr('disabled', 'disabled');
            $("#modal_sctp").modal('show');
            $.ajax({
                url: '/api/synchronize/sctp.php',
                method: 'POST',
                dataType: 'json',
                data: {
                    action: 'clientesSCTP'
                }
            }).done(function(data) {
                if (data.success) {
                    ReloadGrid();
                    $("#modal_sctp .fa-spinner").hide();
                    $("#modal_sctp .success").show();
                    $("#modal_sctp #button_modal_sctp").removeAttr('disabled');

                } else {
                    $("#modal_sctp").modal('hide');
                    swal("Error", data.error, "error");
                }
            });
        }
        <?php endif; ?>
        <?php if(isLaCentral()): ?>

        function obtenerClientesLaCentral() {
            $("#modal_lacentral .fa-spinner").show();
            $("#modal_lacentral .success").hide();
            $("#modal_lacentral #button_modal_lacentral").attr('disabled', 'disabled');
            $("#modal_lacentral").modal('show');
            $.ajax({
                url: '/api/synchronize/lacentral.php',
                method: 'POST',
                dataType: 'json',
                data: {
                    action: 'clientesLaCentral'
                }
            }).done(function(data) {
                if (data.success) {
                    ReloadGrid();
                    $("#modal_lacentral .fa-spinner").hide();
                    $("#modal_lacentral .success").show();
                    $("#modal_lacentral #button_modal_lacentral").removeAttr('disabled');

                } else {
                    $("#modal_lacentral").modal('hide');
                    swal("Error", data.error, "error");
                }
            });
        }
        <?php endif; ?>
    </script>
    <style>
        <?php if($edit[0]['Activo']==0) {
            ?>.fa-edit {
                display: none;
            }
            <?php
        }
        
        ?><?php if($borrar[0]['Activo']==0) {
            ?>.fa-eraser {
                display: none;
            }
            <?php
        }
        
        ?>
    </style>