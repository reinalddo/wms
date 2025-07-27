<?php
include_once  $_SERVER['DOCUMENT_ROOT']."/app/host.php";
$listaAlm = new \AlmacenP\AlmacenP();
$listaGrup = new \GrupoArticulos\GrupoArticulos();
$listaMed = new \UnidadesMedida\UnidadesMedida();
$listaProv = new \Proveedores\Proveedores();
$listaTipcaja = new \TipoCaja\TipoCaja();
$listaSubgp = new \SubGrupoArticulos\SubGrupoArticulos();
$listaSubsub = new \SSubGrupoArticulos\SSubGrupoArticulos();
$almacenes = new \AlmacenP\AlmacenP();

$vere = \db()->prepare("SELECT * FROM t_profiles AS a WHERE id_menu=25 and id_submenu=57 and id_role='".$_SESSION["perfil_usuario"]."'");
$vere->execute();
$ver = $vere->fetchAll(PDO::FETCH_ASSOC);

$agre = \db()->prepare("SELECT * FROM t_profiles AS a WHERE id_menu=25 and id_submenu=58 and id_role='".$_SESSION["perfil_usuario"]."'");
$agre->execute();
$ag = $agre->fetchAll(PDO::FETCH_ASSOC);

$edita = \db()->prepare("SELECT * FROM t_profiles AS a WHERE id_menu=25 and id_submenu=59 and id_role='".$_SESSION["perfil_usuario"]."'");
$edita->execute();
$edit = $edita->fetchAll(PDO::FETCH_ASSOC);

$borra = \db()->prepare("SELECT * FROM t_profiles AS a WHERE id_menu=25 and id_submenu=60 and id_role='".$_SESSION["perfil_usuario"]."'");
$borra->execute();
$borrar = $borra->fetchAll(PDO::FETCH_ASSOC);

$tipo_caracteristicas = \db()->prepare("SELECT c.Id_Carac, c.Cve_Carac, t.TipoCar_Desc, c.Des_Carac
                                        FROM c_tipo_car t, c_caracteristicas c
                                        WHERE t.Id_Tipo_car = c.Id_Tipo_car AND c.Activo = 1");
$tipo_caracteristicas->execute();
$tipo_caracteristicas = $tipo_caracteristicas->fetchAll(PDO::FETCH_ASSOC);

$envases_datos = \db()->prepare("SELECT a.cve_articulo, a.des_articulo FROM c_articulo a LEFT JOIN Rel_Articulo_Almacen ra ON ra.Cve_Articulo = a.cve_articulo WHERE a.Ban_Envase = 'S' AND ra.Cve_Almac = '".$_SESSION["id_almacen"]."'");
$envases_datos->execute();
$res_envases_datos = $envases_datos->fetchAll(PDO::FETCH_ASSOC);

$num_almacenes = \db()->prepare("SELECT COUNT(*) as num_almacenes FROM c_almacenp WHERE Activo = 1");
$num_almacenes->execute();
$num_almacenes = $num_almacenes->fetch()['num_almacenes'];

$almacenes_replicar = \db()->prepare("SELECT * FROM c_almacenp WHERE id != '".$_SESSION["id_almacen"]."'");
$almacenes_replicar->execute();
$res_almacenes_replicar = $almacenes_replicar->fetchAll(PDO::FETCH_ASSOC);

$cve_proveedor = "";
if(isset($_SESSION['id_proveedor']))
{
    $cve_proveedor = $_SESSION['id_proveedor'];
}

?>
<input type="hidden" id="cve_proveedor" value="<?php echo $cve_proveedor; ?>">


    <link href="/css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">
    <link href="/css/plugins/blueimp/css/blueimp-gallery.min.css" rel="stylesheet">
    <link href="/css/plugins/chosen/chosen.css" rel="stylesheet">
    <link href="/css/plugins/imgloader/fileinput.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" />

    <!-- Mainly scripts -->
    <script src="/js/jquery-2.1.1.js"></script>
    <script src="/js/bootstrap.min.js"></script>
    <script src="/js/plugins/metisMenu/jquery.metisMenu.js"></script>
    <script src="/js/plugins/slimscroll/jquery.slimscroll.min.js"></script>

    <link href="/css/plugins/sweetalert/sweetalert.css" rel="stylesheet">

    <!-- Peity -->
    <script src="/js/plugins/peity/jquery.peity.min.js"></script>

    <!-- jqGrid -->
    <script src="/js/plugins/jqGrid/i18n/grid.locale-es.js"></script>
    <script src="/js/plugins/jqGrid/jquery.jqGrid.min.js"></script>

    <!-- Custom and plugin javascript -->
    <script src="/js/inspinia.js"></script>
    <script src="/js/plugins/pace/pace.min.js"></script>

    <script src="/js/plugins/jquery-ui/jquery-ui.min.js"></script>

    <script src="/js/plugins/ladda/spin.min.js"></script>
    <script src="/js/plugins/ladda/ladda.min.js"></script>
    <script src="/js/plugins/ladda/ladda.jquery.min.js"></script>
    <script src="/js/plugins/sweetalert/sweetalert.min.js"></script>
    <!-- Select -->
    <script src="/js/plugins/chosen/chosen.jquery.js"></script>

    <script src="/js/plugins/imgloader/fileinput.min.js"></script>
    <script src="/js/plugins/imgloader/locales/es.js"></script>
    <!-- Jquery Validate -->
    <script src="/js/plugins/validate/jquery.validate.min.js"></script>
    <script src="/js/plugins/blueimp/jquery.blueimp-gallery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>

    <style>
        #list {
            width: 100%;
            height: 100%;
            position: relative;
            left: 0px;
            z-index: 999;
        }
        
        #FORM {
            width: 100%;
            /*height: 100%;*/
            position: absolute;
            left: 0px;
            z-index: 999;
        }
        
        .ui-jqgrid-bdiv {
            height: 100%;
            width: 100% !important;
            max-width: 100% !important;
        }

        .select2 /*, .select2-container, .select2-container--default, .select2-container--below*/
        {
            width: 100% !important;
        }
    </style>

    <div class="wrapper wrapper-content  animated fadeInRight" id="arti">
        <h3>Artículos*1</h3>
        <div class="row">
            <div class="col-md-12">
                <div class="ibox ">
                    <div class="ibox-title">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Almacén</label>
                                    <select class="form-control" id="almacenes" name="almacenes">
                                        <!--<option value="">Seleccione un Almacen</option>-->
                                        <?php foreach( $almacenes->getAll() AS $a ): ?>
                                            <option value="<?php echo $a->id; ?>" <?php if($a->clave==$_GET["almacen"]) echo "selected";?>><?php echo "($a->clave) $a->nombre"; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Grupo de Artículo</label>
                                    <select class="chosen-select form-control" id="grupo_b" name="grupo_b">
                                        <option value="">Seleccione un grupo</option>
                                        <?php foreach( $listaGrup->getAll($_SESSION['id_almacen']) AS $p ): ?>
                                            <option value="<?php echo $p->id; ?>"><?php echo "( ".str_pad($p->cve_gpoart, 9, "_", STR_PAD_LEFT)." ) "." - ".$p->des_gpoart; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Clasificación de Artículo</label>
                                    <select class="chosen-select form-control" id="clasificacion_b" name="clasificacion_b">
                                        <option value="">Clasificación de Artículo</option>
                                        <?php foreach( $listaSubgp->getAll($_SESSION['id_almacen']) AS $p ): ?>
                                            <option value="<?php echo $p->cve_sgpoart; ?>"><?php echo "( ".str_pad($p->cve_sgpoart, 9, "_", STR_PAD_LEFT)." ) "." - ".$p->des_sgpoart; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Tipo de Artículo</label>
                                    <select class="chosen-select form-control" id="tipo_b" name="tipo_b">
                                        <option value="">Tipo de Artículo</option>
                                        <?php foreach( $listaSubsub->getAll($_SESSION['id_almacen']) AS $p ): ?>
                                            <option value="<?php echo $p->cve_ssgpoart; ?>"><?php echo "( ".str_pad($p->cve_ssgpoart, 9, "_", STR_PAD_LEFT)." ) "." - ".$p->des_ssgpoart; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                            </div>
                        </div>
                        <div class="row" class="col-md-12">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Artículo Compuesto</label>
                                    <select name="tipo_c" id="tipo_c" class="chosen-select form-control">
                                        <option value="">Seleccione una opcion</option>
                                        <option value="S">Si</option>
                                        <option value="N">No</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">  
                                <div class="form-group">
                                    <label>Proveedor</label> <!--lilo-->
                                    <select class="chosen-select form-control" id="proveedor" name="proveedor">
                                        <option value="">Seleccione un Proveedor</option>  
                                        <?php foreach( $listaProv->getAll() AS $p ): ?>    
                                            <?php if($cve_proveedor != "" && $cve_proveedor == $p->ID_Proveedor){ ?>
                                            <option value="<?php echo $p->ID_Proveedor; ?>"><?php echo "(".$p->clave_proveedor.") - ".$p->Nombre; ?></option>
                                            <?php } else if($cve_proveedor == ""){ ?>
                                            <option value="<?php echo $p->ID_Proveedor; ?>"><?php echo $p->Nombre; ?></option>
                                        <?php } ?>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="input-group">
                                    <label>&nbsp; </label>
                                    <input type="text" class="form-control" name="txtCriterio" id="txtCriterio" placeholder="Buscar...">
                                    <div class="input-group-btn">
                                        <button onclick="ReloadGrid()" style="margin-top: 22px" type="submit" class="btn btn-primary" id="buscarA">
                                            <span class="fa fa-search"></span>  Buscar
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <?php if($num_almacenes > 1){ ?>
                            <div class="col-md-3">
                                <button style="margin-top: 22px" type="button" class="btn btn-primary permiso_registrar" id="replicar-productos">
                                            <span class="fa fa-reply-all"></span>  Replicar Artículos en otro almacén
                                </button>
                            </div>
                            <?php } ?>
                            <div class="col-md-6">
                                <a href="/api/v2/articulos/exportar" target="_blank" class="btn btn-primary pull-right" style="margin-left:15px; margin-top: 22px;"><span class="fa fa-upload"></span> Exportar</a>
                                <button class="btn btn-primary pull-right permiso_registrar" style="margin-left:15px; ; margin-top: 22px" data-toggle="modal" data-target="#importar"><span class="fa fa-download"></span> Importar</button>
                                <button class="btn btn-primary pull-right" type="button" id="inactivos" style="margin-top: 22px;  margin-left:10px; "><i class="fa fa-search"></i> Artículos inactivos</button>
                                    <button onclick="agregar()" class="btn btn-primary pull-right permiso_registrar"  type="button" style="margin-top: 22px; margin-left:10px; ">
                                        <i class="fa fa-plus"></i> Nuevo
                                    </button>
                                <?php if(isSCTP()): ?>
                                    <div style="display: flex; flex-direction: row; justify-content: flex-end; width: 100%; margin-top: 30px">
                                        <button class="btn btn-primary" onclick="obtenerProductosSCTP()">Obtener Artículos de Rutas SCTP</button>
                                    </div>
                                <?php endif; ?>
                                <?php if(isLaCentral()): ?>
                                    <div style="display: flex; flex-direction: row; justify-content: flex-end; width: 100%; margin-top: 30px">
                                        <button class="btn btn-primary" onclick="obtenerProductosLaCentral()">Obtener Artículos de Rutas de La Central</button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="ibox-content">
                        <div class="jqGrid_wrapper">
                            <table id="grid-table"></table>
                            <div id="grid-pager"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="replicar-modal" role="dialog">
        <div class="vertical-alignment-helper">
            <div class="modal-dialog vertical-align-center">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Replicar <b>Todos</b> los Artículos en otro Almacén</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Almacén *</label>
                            <select class="form-control" id="almacen-replicar" name="almacen-replicar" required="true">
                                <option value="">Seleccione Almacén</option>
                                <?php
                                    foreach( $res_almacenes_replicar AS $p ):
                                ?>
                                    <option value="<?php echo $p['id']; ?>"><?php echo "( ".$p['clave']." ) ".$p['nombre']; ?></option>
                                <?php 
                                    endforeach; 
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">

                      <div class="col-md-6" style="text-align: right">
                          <button id="btn-replicar" type="button" class="btn btn-primary">Replicar</button>
                          <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                      </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="importar" role="dialog">
        <div class="vertical-alignment-helper">
            <div class="modal-dialog vertical-align-center">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Importar Artículos</h4>
                    </div>
                    <div class="modal-body">
                        <form id="form-import" action="import" method="post" enctype="multipart/form-data">
                            <div class="form-group">
                                <label>Seleccionar archivo excel para importar</label>
                                <input type="file" name="file" id="file" class="form-control"  required>
                                <div class="checkbox">
                                    <label for="check-importar">
                                    <input type="checkbox" name="check-importar" id="check-importar" value="1">Modificar</label>
                                </div>
                                <input type="hidden" name="modificar_importacion" id="modificar_importacion" value="0">
                            </div>
                        </form>
                        <div class="col-md-12">
                            <div class="progress" style="display:none">
                                <div class="progress-bar progress-bar-info progress-bar-striped" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:0%">
                                    <div class="percent">0%</div >
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                      <div class="col-md-6" style="text-align: left">
                        <button id="btn-layout" type="button" class="btn btn-primary">Descargar Layout</button>
                      </div>
                      <div class="col-md-6" style="text-align: right">
                          <button id="btn-import" type="button" class="btn btn-primary">Importar</button>
                          <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                      </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Detalle Articulo-->
    <div class="modal inmodal" id="modalVer" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content animated bounceInRight">
                <div class="modal-header" id="modaltitle">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                    <i class="fa fa-laptop modal-icon"></i>
                    <h4 class="modal-title">Detalle del Artículo</h4>
                    <h3 class="modal-subtitle"></h3>
                </div>
                <div class="col-md-6 b-r">
                    </br>
                    <label>Clave Interna (Pza)</label> <input id="codigo_ver" name="codigo_ver" type="text" placeholder="" class="form-control" disabled>
                    <label>Código de Barras (Pza)</label> <input id="cve_codprov_ver" name="cve_codprov_ver" type="text" placeholder="" class="form-control" disabled>
                    <label>Descripción</label> <input id="des_articulo_ver" name="des_articulo_ver" type="text" placeholder="" class="form-control" disabled>
                    <label>Peso Unitario (Kg)</label> <input id="peso_ver" name="peso_ver" type="text" placeholder="" class="form-control" disabled>
                    <label>Código de Barras (Caja)</label> <input id="barras2_ver" name="barras2_ver" type="text" placeholder="" class="form-control" disabled>
                    <label>Equivalencia ( Cajas | Kg | Lt )</label> <input oninput="this.value = this.value.replace(/[^0-9]/g, '');" id="num_multiplo_ver" name="num_multiplo_ver" type="text" placeholder="" class="form-control" disabled>
                </div>
                <div class="col-md-6">
                    </br>
                    <label>Código de Barras (Pallet)</label> <input id="barras3_ver" name="barras3_ver" type="text" placeholder="" class="form-control" disabled>
                    <label>Cajas por Pallet</label> <input id="cajas_palet_ver" name="cajas_palet_ver" type="text" placeholder="" class="form-control" oninput="this.value = this.value.replace(/[^0-9]/g, '');" disabled>
                    <label>Grupo</label> <input id="grupo_ver" name="grupo_ver" type="text" placeholder="" class="form-control" disabled>
                    <label>Clasificación</label> <input id="clasificacion_ver" name="clasificacion_ver" type="text" placeholder="" class="form-control" disabled>
                    <label>Tipo</label> <input id="tipo_ver" name="tipo_ver" type="text" placeholder="" class="form-control" disabled>
                </div>
                <div class="col-md-12">
                    <div class="lightBoxGallery">
                        <h4>Imagenes Actuales</h4>
                        <div id="upload2"></div>
                        <div id="blueimp-gallery" class="blueimp-gallery">
                            <div class="slides"></div>
                            <h3 class="title"></h3>
                            <a class="prev">‹</a>
                            <a class="next">›</a>
                            <a class="close">×</a>
                            <a class="play-pause"></a>
                            <ol class="indicator"></ol>
                        </div>
                    </div>
                </div>
                <div class="col-md-12">
                    <button type="button" class="btn btn-white" data-dismiss="modal" id="btnCancel">Cerrar</button>
                </div>
                <div class="modal-footer"></div>
            </div>
        </div>
    </div>

    <!-- Modal Recuperar Articulo -->
    <div class="modal fade" id="coModal" role="dialog">
        <div class="vertical-alignment-helper">
            <div class="modal-dialog vertical-align-center modal-lg" style="width: 1200px!important;">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Recuperar Artículo</h4>
                    </div>
                    <div class="modal-body">
                        <div class="col-md-4">
                            <div class="input-group">
                                <input type="text" class="form-control input-sm" name="txtCriterio" id="txtCriterio1" placeholder="Buscar...">
                                <div class="input-group-btn">
                                    <a href="#" onclick="ReloadGrid1()">
                                        <button type="submit" id="buscarR" class="btn btn-sm btn-primary">Buscar</button>
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

    <div class="wrapper wrapper-content  animated fadeInRight" id="FORM" style="display: none">
        <div class="row">
            <div class="col-md-12">
                <div class="ibox ">
                    <div class="ibox-title">
                        <div class="row">
                            <div class="col-md-4" id="_title">
                                <h3>Agregar Artículo</h3>
                            </div>
                        </div>
                    </div>
                    <div class="ibox-content">
                        <form id="myform" role="form">
                            <div class="row">
                                <div class="col-md-6 b-r">
                                    <div class="form-group">
                                        <label>Almacén *</label>
                                        <select class="form-control" id="cve_almac" name="cve_almac" required="true">
                                            <?php /* ?><option value="">Almacén</option><?php */ ?>
                                            <?php
                                                $x = 0;
                                                foreach( $listaAlm->getAll() AS $p ):
                                                //$x++;
                                            ?>                                      
                                                <option value="<?php echo $p->id; ?>" <?php //if($x == 1){echo 'selected';}?>><?php echo $p->nombre; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="form-group"><label>Clave Interna (Pza) *</label> <input id="codigo" name="codigo" type="text" placeholder="Código Artículo" class="form-control" maxlength="30" required="true"><label id="codigo_msg" style="color:red;"></label></div>
                                    <div class="form-group"><label>Código de Barras (Pza) </label> <input id="cve_codprov" name="cve_codprov" type="text" placeholder="Código de Barra" class="form-control"><label id="cve_codprov_msg" style="color:red;"></label></div>

                                    <div class="form-group"><label>Descripción Corta *</label> <input id="des_articulo" name="des_articulo" type="text" maxlength="30" placeholder="Nombre de Artículo" class="form-control" required="true"></div>

                                    <div class="form-group"><label>Descripción Detallada</label> <textarea id="des_detallada" name="des_detallada" placeholder="Descripción Detallada del Artículo" class="form-control" rows="8"></textarea></div>

                                    <div class="form-group">
                                        <label>Unidad de Medida Base</label>
                                        <select class="form-control" id="unidadMedida" name="unidadMedida">
                                            <!--<option value="">Seleccione unidad</option>-->
                                            <?php foreach( $listaMed->getAll() AS $p ): ?>
                                            <option value="<?php echo $p->id_umed; ?>"><?php echo $p->des_umed; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <div style="float: left; width: 100%; margin-bottom: 15px;">
                                        <label>Características </label>
                                            <select class="form-control" id="caracteristicas_art" name="caracteristicas_art[]" multiple size="10px">
                                                <?php foreach ($tipo_caracteristicas as $row): ?>
                                                <option value="<?php echo $row['Id_Carac']; ?>"><?php echo "[".$row['Cve_Carac']."] - (".$row['TipoCar_Desc'].") - ".$row['Des_Carac']; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group"><label>Peso Unitario (Kg) *</label> <input id="peso" name="peso" type="text" placeholder="Peso Unitario" class="form-control" required="true"></div>
                                    <div class="form-group"><label>Precio Unitario ($) *</label> <input id="costo" name="costo" type="text" placeholder="Precio Unitario" class="form-control" required="true"></div>
                                    <div class="col-md-6" style="padding-left: 0;">
                                    <div class="form-group"><label>IVA (%)</label> <input id="iva" name="iva" type="text" placeholder="IVA (%)" class="form-control"></div>
                                    </div>
                                    <div class="col-md-6" style="padding-right: 0;">
                                    <div class="form-group"><label>IEPS</label> <input id="IEPS" name="IEPS" type="text" placeholder="IEPS" class="form-control"></div>
                                    </div>

                                    <div>
                                    <div class="col-md-6" style="padding-left: 0;">
                                    <div class="form-group"><label>Código de Barras (Empaque)</label> <input id="barras2" name="barras2" type="text" placeholder="Código de Caja" class="form-control"><label id="barras2_msg" style="color:red;"></label></div>
                                    </div>


                                    <div class="col-md-6" style="padding-right: 0;">
                                        <div class="form-group">
                                            <label>Unidad de Medida (Empaque)</label>
                                            <select class="form-control" id="unidadMedida_empaque" name="unidadMedida_empaque">
                                                <option value="">Seleccione unidad</option>
                                                <?php foreach( $listaMed->getAll() AS $p ): ?>
                                                <option value="<?php echo $p->id_umed; ?>" <?php /*if($p->des_umed == 'Caja') echo "selected"; */ ?> ><?php echo $p->des_umed; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    </div>


                                    <div class="form-group">
                                        <div style="float: left; width: 100%;">
                                        <label>Equivalencia ( Cajas | Kg | Lt ) </label> <input oninput="this.value = this.value.replace(/[^0-9]/g, '');" id="num_multiplo" name="num_multiplo" type="text" placeholder="Unidades X Caja" class="form-control">
                                        </div>
                                    </div>
                                    <div class="form-group"><label>Código de Barras (Pallet)</label> <input id="barras3" name="barras3" type="text" placeholder="Código de Barras (Pallet)" class="form-control"><label id="barras3_msg" style="color:red;"></div>
                                    <div class="form-group"><label>Cajas por Pallet </label> <input id="cajas_palet" name="cajas_palet" type="text" placeholder="Cajas por Pallet" class="form-control" oninput="this.value = this.value.replace(/[^0-9]/g, '');"></div>
                                    <div class="form-group">
                                        <label>Dimensiones Pieza (mm)*</label>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label>Alto (mm)</label>
                                        <input id="alto" type="text" oninput="this.value = this.value.replace(/[^0-9]/g, ''); this.value = this.value.replace(/(\..*)\./g, '$1');" placeholder="Alto" class="form-control">
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label>Ancho (mm)</label>
                                        <input id="ancho" type="text" oninput="this.value = this.value.replace(/[^0-9]/g, ''); this.value = this.value.replace(/(\..*)\./g, '$1');" placeholder="Ancho" class="form-control">
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label>Fondo (mm)</label>
                                        <input id="fondo" type="text" oninput="this.value = this.value.replace(/[^0-9]/g, ''); this.value = this.value.replace(/(\..*)\./g, '$1');" placeholder="Fondo" class="form-control">
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label>Uso de Producto</label>
                                        <select class="form-control" id="tipo_producto" name="tipo_producto">
                                            <option value="">Seleccione Uso de Producto</option>
                                            <option value="Activo Fijo"> (AF) Activo Fijo</option>
                                            <option value="Producto de Consumo"> (PC) Producto de Consumo</option>
                                            <option value="Material de Empaque"> (ME) Material de Empaque</option>
                                            <option value="Materia Prima"> (MP) Materia Prima</option>
                                            <option value="Herramientas"> (H) Herramientas</option>
                                            <option value="ProductoNoSurtible"> (NS) Producto No Surtible</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label>UMAS</label>
                                        <input id="umas" name="umas" type="text" placeholder="UMAS" class="form-control">
                                    </div>
                                    <?php 
                                    /*
                                    ?>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Unidad de Medida</label>
                                            <select class="form-control" id="unidadMedida" name="unidadMedida">
                                                <option value="">Seleccione unidad</option>
                                                <?php foreach( $listaMed->getAll() AS $p ): ?>
                                                <option value="<?php echo $p->id_umed; ?>"><?php echo $p->des_umed; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <?php  
                                    */
                                    ?>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Caja Artículo</label>
                                    </div>

                                    <div class="col-md-8 form-group" style="z-index: 10;">
                                        <label>Descripción</label>
                                        <input id="descripcion_caja" <?php /* ?>disabled <?php */ ?> type="text" placeholder="Descripción Caja" class="form-control">
                                    </div>
                                    <div class="col-md-4 form-group" style="z-index: 10;">
                                        <label>Peso (Kg)</label>
                                        <input id="peso_caja" <?php /* ?>disabled <?php */ ?> type="text" placeholder="Peso Caja" class="form-control">
                                    </div>

                                    <div class="form-group">
                                        <input type="hidden" id="tipo_caja" value="">
                                        <label>Dimensiones Caja (mm)</label>
                                    </div>
                                    <?php 
                                    /*
                                    ?>
                                    <div class="form-group">
                                        <label>Tipo de Caja</label>
                                        <input type="hidden" id="cve_tipcaja" value="">
                                        <select class="form-control" id="tipo_caja" name="tipo_caja">
                                            <option value="">Tipo de Caja</option>
                                            <?php foreach( $listaTipcaja->getAll() AS $p ): ?>
                                                <option value="<?php echo $p->id_tipocaja; ?>"><?php echo $p->clave." ".$p->descripcion; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <?php 
                                    */
                                    ?>
                                    
                                    <div class="col-md-4 form-group" style="z-index: 10;">
                                        <label>Alto (mm)</label>
                                        <input id="altotc" <?php /* ?>disabled <?php */ ?> type="text" oninput="this.value = this.value.replace(/[^0-9]/g, ''); this.value = this.value.replace(/(\..*)\./g, '$1');" placeholder="Alto" class="form-control">
                                    </div>
                                    <div class="col-md-4 form-group" style="z-index: 10;">
                                        <label>Ancho (mm)</label>
                                        <input id="anchotc" <?php /* ?>disabled <?php */ ?> type="text" oninput="this.value = this.value.replace(/[^0-9]/g, ''); this.value = this.value.replace(/(\..*)\./g, '$1');" placeholder="Ancho" class="form-control">
                                    </div>
                                    <div class="col-md-4 form-group" style="z-index: 10;">
                                        <label>Fondo (mm)</label>
                                        <input id="fondotc" <?php /* ?>disabled <?php */ ?> type="text" oninput="this.value = this.value.replace(/[^0-9]/g, ''); this.value = this.value.replace(/(\..*)\./g, '$1');" placeholder="Fondo" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <div class="checkbox">
                                            <label for="checkboxes-0">
                                            <input type="checkbox" name="control_lotes" id="control_lotes" value="1">Control de Lotes</label>
                                        </div>
                                        <div class="checkbox" id="control_caducidad_div" style="display: none;">
                                            <label for="checkboxes-0">
                                            <input type="checkbox" name="control_caducidad" id="control_caducidad" value="1">Control de Caducidad</label>
                                        </div>
                                        <div class="checkbox">
                                            <label for="checkboxes-0">
                                                <input type="checkbox" name="control_numero_series" id="control_numero_series" value="1">Control  de Número de Series</label>
                                        </div>
                                        <div class="checkbox">
                                            <label for="checkboxes-0">
                                            <input type="checkbox" name="control_peso" id="control_peso" value="1">Control de Peso (Granel)</label>
                                        </div>
                                        <div class="checkbox">
                                            <label for="checkboxes-0">
                                            <input type="checkbox" name="control_volumen" id="control_volumen" value="1">Control de Volumen</label>
                                        </div>
                                        <div class="checkbox">
                                            <label for="checkboxes-0">
                                            <input type="checkbox" name="compuesto" id="compuesto" value="1">Articulo Compuesto</label>
                                        </div>

                                        <div class="checkbox">
                                            <label for="checkboxes-0">
                                            <input type="checkbox" name="envase" id="envase" value="1">Envase</label>
                                        </div>
                                        <div class="checkbox tipo_envase" style="display: none;margin-left: 20px;">
                                            <label for="checkboxes-0">
                                            <input type="checkbox" name="tipo_envase_plastico" id="tipo_envase_plastico" value="1">Plástico</label>
                                        </div>
                                        <div class="checkbox tipo_envase" style="display: none;margin-left: 20px;">
                                            <label for="checkboxes-0">
                                            <input type="checkbox" name="tipo_envase_cristal" id="tipo_envase_cristal" value="1">Cristal</label>
                                        </div>
                                        <div class="checkbox tipo_envase" style="display: none;margin-left: 20px;">
                                            <label for="checkboxes-0">
                                            <input type="checkbox" name="tipo_envase_garrafon" id="tipo_envase_garrafon" value="1">Garrafón</label>
                                        </div>

                                        <div class="checkbox">
                                            <label for="checkboxes-0">
                                            <input type="checkbox" name="usa_envase" id="usa_envase" value="1">Usa Envase</label>
                                        </div>

                                        <div class="form-group usa_envase_datos" style="border: 1px solid black;border-radius: 5px; padding: 10px; display: none;">
                                            <label>Envase </label>
                                                <select class="form-control" id="envase1" name="envase1">
                                                    <option value="">Seleccione</option>
                                                    <?php foreach ($res_envases_datos as $row): ?>
                                                    <option value="<?php echo $row['cve_articulo']; ?>"><?php echo "[".$row['cve_articulo']."] - (".$row['des_articulo'].") "; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <div class="row">
                                                    <div class="col-sm-6">
                                                        <label>Cantidad Base </label>
                                                        <input type="number" name="cantidad_base1" id="cantidad_base1" class="form-control">
                                                    </div>

                                                    <div class="col-sm-6">
                                                        <label>Cantidad Equivalente </label>
                                                        <input type="number" name="cantidad_equivalente1" id="cantidad_equivalente1" class="form-control">
                                                    </div>
                                                    <div style="text-align: center;">
                                                        <button type="button" id="agregar_envase2" class="btn btn-primary" style="margin: 15px 0;"><i class="fa fa-plus"></i>Agregar Envase 2</button></div>
                                                </div>
                                            <div class="datos_envase2" style="display: none; margin-top: 15px;">
                                            <label>Envase 2</label>
                                                <select class="form-control" id="envase2" name="envase2">
                                                    <option value="">Seleccione</option>
                                                    <?php foreach ($res_envases_datos as $row): ?>
                                                    <option value="<?php echo $row['cve_articulo']; ?>"><?php echo "[".$row['cve_articulo']."] - (".$row['des_articulo'].") "; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <div class="row">
                                                    <div class="col-sm-6">
                                                        <label>Cantidad Base </label>
                                                        <input type="number" name="cantidad_base2" id="cantidad_base2" class="form-control">
                                                    </div>

                                                    <div class="col-sm-6">
                                                        <label>Cantidad Equivalente </label>
                                                        <input type="number" name="cantidad_equivalente2" id="cantidad_equivalente2" class="form-control">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="checkbox">
                                            <label for="checkboxes-0">
                                            <input type="checkbox" name="control_abc" id="control_abc" value="1">Control ABC</label>
                                        </div>
                                        <div class="checkbox tipo_ABC" style="display: none;margin-left: 20px;">
                                            <label for="checkboxes-0">
                                            <input type="checkbox" name="control_tipo_A" id="control_tipo_A" value="1">A</label>
                                        </div>
                                        <div class="checkbox tipo_ABC" style="display: none;margin-left: 20px;">
                                            <label for="checkboxes-0">
                                            <input type="checkbox" name="control_tipo_B" id="control_tipo_B" value="1">B</label>
                                        </div>
                                        <div class="checkbox tipo_ABC" style="display: none;margin-left: 20px;">
                                            <label for="checkboxes-0">
                                            <input type="checkbox" name="control_tipo_C" id="control_tipo_C" value="1">C</label>
                                        </div>

                                    </div>
                                    <div class="form-group" style="clear: both;"><label>Stock Mínimo (Pzas)</label> <input id="stock_minimo" name="stock_minimo" type="text" placeholder="Stock Minimo" class="form-control" oninput="this.value = this.value.replace(/[^0-9]/g, '');"></div>
                                    <div class="form-group"><label>Stock Máximo (Pzas)</label> <input id="stock_maximo" name="stock_maximo" type="text" placeholder="Stock Maximo" class="form-control" oninput="this.value = this.value.replace(/[^0-9]/g, '');"></div>
                                    <div class="form-group">
                                        <div class="checkbox">
                                            <label for="checkboxes-0">
                                            <input type="checkbox" name="req_refrigeracion" id="req_refrigeracion" value="1">Requiere Refrigeracion</label>
                                        </div>
                                        <div class="checkbox">
                                            <label for="checkboxes-0">
                                            <input type="checkbox" name="mat_peligroso" id="mat_peligroso" value="1">Material Peligroso</label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>Grupo de Artículo </label>
                                        <select class="form-control" id="grupo" name="grupo">
                                            <option value="">Grupo de Artículo</option>
                                            <?php foreach( $listaGrup->getAll($_SESSION['id_almacen']) AS $p ): ?>
                                                <option value="<?php echo $p->cve_gpoart; ?>"><?php echo "( ".str_pad($p->cve_gpoart, 9, "_", STR_PAD_LEFT)." ) "." - ".$p->des_gpoart; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Clasificación de Artículo </label>
                                        <select class="form-control" id="clasificacion" name="clasificacion">
                                            <option value="">Clasificación de Artículo</option>
                                            <?php foreach( $listaSubgp->getAll($_SESSION['id_almacen']) AS $p ): ?>
                                                <option value="<?php echo $p->cve_sgpoart; ?>"><?php echo "( ".str_pad($p->cve_sgpoart, 9, "_", STR_PAD_LEFT)." ) "." - ".$p->des_sgpoart; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Tipo de Artículo </label>
                                        <select class="form-control" id="tipo" name="tipo">
                                            <option value="">Tipo de Artículo</option>
                                            <?php foreach( $listaSubsub->getAll($_SESSION['id_almacen']) AS $p ): ?>
                                                <option value="<?php echo $p->cve_ssgpoart; ?>"><?php echo "( ".str_pad($p->cve_ssgpoart, 9, "_", STR_PAD_LEFT)." ) "." - ".$p->des_ssgpoart; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                <?php 
                                if($cve_proveedor == "")
                                {
                                ?>
                                <div class="form-group">
                                    <label>Proveedor </label>
                                    <select class="form-control chosen-select" id="cboProveedor">
                                    <option value="">Seleccione</option>
                                    <?php foreach( $listaProv->getAll() AS $p ): ?>
                                    <option value="<?php echo $p->ID_Proveedor; ?>"><?php echo $p->Nombre; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                </div>
                                <?php 
                                }
                                ?>

                                    <div class="form-group">
                                        <div class="lightBoxGallery">
                                            <h4>Imagenes Actuales</h4>
                                            <div id="upload"></div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label">Seleccione Fotos</label>
                                        <input id="input-2" name="input2[]" type="file" class="file" multiple data-show-upload="false" data-show-caption="true">
                                        <label>Dimensiones: 250x250 px , Formato: Jpg, Tamaño: 512 Kb  </label>
                                         ------ AAA----
                                        <div id="elegir1" class="form_img">
                                            <input id="up_1" id="foto_file" type="file" class="file_img"/><br />
                                            <!--input type="submit" class="ui blue inline button" value="Subir archivo" /-->
                                        </div>
                                        <img class="fotos" src="" width="33%" id="foto1">
                                    </div>
                                    <div class="form-group">
                                        <div class="checkbox">
                                            <label for="replicar_articulos" style="font-size: 16px; font-weight: bold;">
                                            <input type="checkbox" name="replicar_articulos" id="replicar_articulos" value="1">Replicar Artículo en los demás almacenes</label>
                                        </div>
                                    </div>
                                    <div class="pull-right" style="clear: both;">
                                        <a href="#" onclick="cancelar()"><button type="button" class="btn btn-white" id="btnCancel">Cerrar</button></a>&nbsp;&nbsp;
                                        <button type="button" class="btn btn-primary ladda-button" data-style="slide-left" id="btnSave">Guardar</button>
                                    </div>
                                </div>
                            </div>
                            <input name="hiddenAction" id="hiddenAction" type="hidden">
                            <input name="hiddenID" id="hiddenID" type="hidden">
                        </form>
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
                            <h4 class="modal-title">Obteniendo Artículos de SCTP</h4>
                        </div>
                        <div class="modal-body">
                            <div class="text-center">
                                <i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i>
                                <div class="success">
                                    <h3>
                                        <i class="fa fa-check" style="color: #1ab394"></i> ¡Artículos cargados exitosamente!
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
                            <h4 class="modal-title">Obteniendo Artículos de La Central</h4>
                        </div>
                        <div class="modal-body">
                            <div class="text-center">
                                <i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i>
                                <div class="success">
                                    <h3>
                                        <i class="fa fa-check" style="color: #1ab394"></i> ¡Artículos cargados exitosamente!
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

<script>

$('#btn-layout').on('click', function(e) {
    //e.preventDefault();  //stop the browser from following
    //window.location.href = 'http://www.tinegocios.com/proyectos/wms/Layout_Articulos_Importador.xlsx';
    window.location.href = '/Layout/Layout_Articulos.xlsx';
}); 
  
$('#btn-import').on('click', function() 
{
    $("#modificar_importacion").val(0);
    if($("#check-importar").is(":checked"))
    {
        $("#modificar_importacion").val(1);
    }
        console.log("checked Modificar", $("#modificar_importacion").val());

    //return;

    $('#btn-import').prop('disable', true);
    var bar = $('.progress-bar');
    var percent = $('.percent');
    //var status = $('#status');
    var formData = new FormData();
    formData.append("clave", "valor");
    $.ajax({
        // Your server script to process the upload
        url: '/articulos/importar',
        type: 'POST',
        // Form data
        data: new FormData($('#form-import')[0]),
        // Tell jQuery not to process data or worry about content-type
        // You *must* include these options!
        cache: false,
        contentType: false,
        processData: false,
        beforeSend: function() 
        {
            $('.progress').show();
            var percentVal = '0%';
            bar.width(percentVal);
            percent.html(percentVal);
        },
        // Custom XMLHttpRequest
        xhr: function() 
        {
            var myXhr = $.ajaxSettings.xhr();
            if (myXhr.upload) 
            {
                // For handling the progress of the upload
                myXhr.upload.addEventListener('progress', function(e) 
                {
                    if (e.lengthComputable) 
                    {
                        var percentComplete = e.loaded / e.total;
                        percentComplete = parseInt(percentComplete * 100);
                        bar.css("width", percentComplete + "%");
                        percent.html(percentComplete+'%');
                        if (percentComplete === 100) 
                        {
                            setTimeout(function(){$('.progress').hide();}, 2000);
                        }
                    }
                }, false);
            }
            return myXhr;
        },
        success: function(data) 
        {
            console.log(data);
            setTimeout(function(){
                if (data.status == 200) 
                {
                    swal("Exito", data.statusText, "success");
                    $('#importar').modal('hide');
                    ReloadGrid();
                }
                else 
                {
                    swal("Error", data.statusText, "error");
                }
            },1000)
        }, error: function(data)
        {
            console.log(data);
        },
    });
});
</script>
<script type="text/javascript">
    // initialize with defaults
    $("#input-2").fileinput({
        language: 'es',
        maxFileCount: 5,
        allowedFileExtensions: ["jpg", "png"]
    });

    /**
     * @author Ricardo Delgado.
     * Busca y selecciona el almacen predeterminado para el usuario.
     */
    function almacenPrede() 
    {
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                idUser: '<?php echo $_SESSION["id_user"]?>',
                action: 'search_almacen_pre'
            },
            beforeSend: function(x) {
                if (x && x.overrideMimeType) 
                {
                    x.overrideMimeType("application/json;charset=UTF-8");
                }
            },
            url: '/api/almacenPredeterminado/index.php',
            success: function(data) {
                if (data.success == true) 
                {
                    document.getElementById('almacenes').value = data.codigo.id;
                    document.getElementById('cve_almac').value = data.codigo.id;
                    setTimeout(function() {
                        ReloadGrid();
                    }, 1500);
                }
            },
            error: function(res) {
                window.console.log(res);
            }
        });
    }

    // with plugin options
    $("#input-2").fileinput({
        'showUpload': false,
        'previewFileType': 'any'
    });

    function uploadFile() 
    {
        var input = document.getElementById("input-2");

        //console.log("UPLOAD = ", input.files.length);

        for (var i = 0; i < input.files.length; i++)  
        {
            var file = input.files[i];
            if (file != undefined) 
            {
                formData = new FormData();

                if (!!file.type.match(/image.*/)) 
                {
                    formData.append("image", file);
                    //formData.append("cve_articulo", $("#codigo").val());
                    //console.log("formData = ", formData);
                    $.ajax({
                        url: "/app/template/page/articulos/upload.php",
                        type: "POST",
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(data) {
                            //console.log("Upload = ", data);
                        },
                        error: function(xhr, ajaxOptions, thrownError) {
                            alert(thrownError);
                        }
                    });
                } 
                else 
                {
                    alert('Not a valid image!');
                }
            } 
            else 
            {
                alert('Input something!');
            }
        }
    }

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
            url: '/api/articulos/lista/index.php',
            datatype: "json",
            shrinkToFit: false,
            height: 'auto',
            postData: {
                criterio: "",
                id_proveedor: ($("#cve_proveedor").val())?($("#cve_proveedor").val()):($("#cboProveedor").val()),
                almacen: $("#almacenes").val(),
            },
            mtype: 'POST',
            colNames: ['Acciones','ID','Clave','CB Pieza','CB Caja','CB Pallet','Descripción','KIT', 'Usa Envase','Dimensiones (mm)','Volumen (m3)','Peso U (Kgs)','Costo','Piezas por Caja','Cajas por Pallet','Grupo','Clasificación (SAT)','Tipo', 'Empresa|Proveedor','Tipo de Producto','UMAS','Unida de Medida', 'Imagen','Almacen', 'Descripción Detallada', 'Barcode'],
            colModel: [
                {name: 'myac',index: '',width: 80,fixed: true,sortable: false,resize: false,formatter: imageFormat,frozen: true},
                {name: 'id',index: 'id',width: 100,editable: false,hidden: true,sortable: false,resizable: true},
                {name: 'cve_articulo',index: 'cve_articulo',width: 110,editable: false,sortable: false,resizable: true},
                {name: 'cve_codprov',index: 'cve_codprov',width: 130,editable: false,sortable: false,resizable: true},
                {name: 'barras2',index: 'barras2',width: 130,editable: false,sortable: false,resizable: true},
                {name: 'barras3',index: 'barras3',width: 130,editable: false,sortable: false,resizable: true},
                {name: 'des_articulo',index: 'des_articulo',width: 200,editable: false,sortable: false,resizable: true},
                {name: 'compuesto',index: 'compuesto',width: 70,editable: false,sortable: false,resizable: true,align:'center'},
                {name: 'envase',index: 'envase',width: 80,editable: false,sortable: false,resizable: true,align:'center'},
                {name: 'dimension',index: 'dimension',width: 150,editable: false,sortable: false,resizable: true,align:'right'},
                {name: 'volumen',index: 'volumen',width: 100,editable: false,sortable: false,resizable: true,align:'right'},
                {name: 'peso',index: 'peso',width: 130,editable: false,sortable: false,resizable: true,align:'right'},
                {name: 'costo',index: 'costo',width: 110,editable: false,sortable: false,resizable: true,align:'right'},
                {name: 'num_multiplo',index: 'num_multiplo',width: 130,editable: false,sortable: false,resizable: true},
                {name: 'cajas_palet',index: 'cajas_palet',width: 130,editable: false,sortable: false,resizable: true},
                {name: 'grupoa',index: 'grupoa',width: 150,editable: false,sortable: false,resizable: true},
                {name: 'clasificaciona',index: 'clasificaciona',width: 150,editable: false,sortable: false,resizable: true},
                {name: 'tipoa',index: 'tipoa',width: 180,editable: false,sortable: false,resizable: true},
                {name: 'empresa_proveedor',index: 'empresa_proveedor',width: 200,editable: false,sortable: false,resizable: true},
                {name: 'tipo_producto',index: 'tipo_producto',width: 150,editable: false,sortable: false,resizable: true},
                {name: 'umas',index: 'umas',width: 110,editable: false,sortable: false,resizable: true,align: "center"},
                {name: 'des_umed',index: 'des_umed',width: 110,editable: false,sortable: false,resizable: true,align: "center"},
                {name: 'imagen',index: 'imagen',width: 110,editable: false,sortable: false,resizable: true,align: "center", hidden: true},
                {name: 'almacen',index: 'almacen',width: 150,editable: false,sortable: false,resizable: true},
                {name: 'des_detallada',index: 'des_detallada',width: 110,editable: false,sortable: false,resizable: true, hidden: true},
                {name: 'barcode',index: 'barcode',width: 110,editable: false,sortable: false,resizable: true, hidden: true}
            ],
            rowNum: 30,
            rowList: [30, 40, 50],
            pager: pager_selector,
            sortname: 'id',
            viewrecords: true,
            sortorder: "desc",
            loadBeforeSend: function(data){
                console.log("loadBeforeSend = ", data);
            },
            loadComplete: almacenPrede(),//function(data){console.log("SUCCESS", data);},
            loadError: function(data){
                console.log("ERROR", data);
            }
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

        function imageFormat(cellvalue, options, rowObject) 
        {
            var serie = rowObject[1];
            var correl = rowObject[4];
            var url = "x/?serie=" + serie + "&correl=" + correl;
            var url2 = "v/?serie=" + serie + "&correl=" + correl;

            var html = '';
            if($("#permiso_editar").val() == 1)
            html += '<a href="#" onclick="editar(\'' + serie + '\')" title="Editar"><i class="fa fa-edit" ></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';

            if($("#permiso_eliminar").val() == 1)
            html += '<a href="#" onclick="borrar(\'' + serie + '\')" title="Borrar"><i class="fa fa-eraser" ></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';

            html += '<a href="#" onclick="ver(\'' + serie + '\')" title="Ver"><i class="fa fa-search" ></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            return html;
        }

        function aceSwitch(cellvalue, options, cell) 
        {
            setTimeout(function() {
                $(cell).find('input[type=checkbox]')
                    .addClass('ace ace-switch ace-switch-5')
                    .after('<span class="lbl"></span>');
            }, 0);
        }
        //enable datepicker
        function pickDate(cellvalue, options, cell) 
        {
            setTimeout(function() {
                $(cell).find('input[type=text]')
                    .datepicker({
                        format: 'yyyy-mm-dd',
                        autoclose: true
                    });
            }, 0);
        }

        function beforeDeleteCallback(e) 
        {
            var form = $(e[0]);
            if (form.data('styled')) return false;
            form.closest('.ui-jqdialog').find('.ui-jqdialog-titlebar').wrapInner('<div class="widget-header" />')
            style_delete_form(form);
            form.data('styled', true);
        }

        function reloadPage() 
        {
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

        function beforeEditCallback(e) 
        {
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

        function enableTooltips(table) 
        {
            $('.navtable .ui-pg-button').tooltip({
                container: 'body'
            });
            $(table).find('.ui-pg-div').tooltip({
                container: 'body'
            });
        }

        $(document).one('ajaxloadstart.page', function(e) {
            $(grid_selector).jqGrid('GridUnload');
            $('.ui-jqdialog').remove();
        });
    });

    //////////////////////////////////////////////////////////hace un reload del grid llamando al php en la carpera "c" para hacer la consulta//////////////////////////////////////////////////////////
    function ReloadGrid() 
    {
        $('#grid-table').jqGrid('clearGridData')
            .jqGrid('setGridParam', {
                postData: {
                    proveedor: $("#proveedor").val(),//lilo
                    criterio: $("#txtCriterio").val(),
                    grupo: $("#grupo_b").val(),
                    clasificacion: $("#clasificacion_b").val(),
                    tipo: $("#tipo_b").val(),
                    compuesto: $("#tipo_c").val(),
                    almacen: $("#almacenes").val(),
                },
                datatype: 'json',
                page: 1
            })
            .trigger('reloadGrid', [{
                current: true
            }]);

            $("#caracteristicas_art").select2();
    }

    function ReloadGrid1() 
    {
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

    function downloadxml(url) 
    {
        var win = window.open(url, '_blank');
        win.focus();
    }

    function viewPdf(url)
    {
        var win = window.open(url, '_blank');
        win.focus();
    }

    $modal0 = null;

    function borrar(_codigo) {
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                cve_articulo: _codigo,
                action: "existeEnUbicacion"
            },
            beforeSend: function(x) {
                if (x && x.overrideMimeType) 
                {
                    x.overrideMimeType("application/json;charset=UTF-8");
                }
            },
            url: '/api/articulos/update/index.php',
            success: function(data) {
                if (data.success == true) 
                {
                    swal(
                        '¡Alerta!',
                        'El artículo esta siendo usado en este momento',
                        'warning'
                    );
                    ReloadGrid();
                }
                else 
                {
                    swal({
                        title: "¿Está seguro que desea borrar el articulo?",
                        text: "Está a punto de borrar un articulo y esta acción no se puede deshacer",
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
                                cve_articulo: _codigo,
                                action: "delete"
                            },
                            beforeSend: function(x) {
                                if (x && x.overrideMimeType) 
                                {
                                    x.overrideMimeType("application/json;charset=UTF-8");
                                }
                            },
                            url: '/api/articulos/update/index.php',
                            success: function(data) {
                                if (data.success == true) 
                                {
                                    ReloadGrid();
                                    ReloadGrid1();
                                }
                            }
                        });
                    });
                }
            }
        });
    }

    /////// SELECT COMBO SUBGRUPO ///////
    function fetch_select(val) 
    {
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                get_option: val,
                action: "inputSubSelect"
            },
            beforeSend: function(x) {
                if (x && x.overrideMimeType) 
                {
                    x.overrideMimeType("application/json;charset=UTF-8");
                }
            },
            url: '/api/subgrupodearticulos/update/index.php',
            success: function(data) {
                if (data.success == true) 
                {
                    document.getElementById("descrpSubGrup").innerHTML = data.response;
                }
            }
        });
    }

    /////// SELECT COMBO SUBSUBGRUPO ///////
    function fetch_subsub(val) 
    {
      $.ajax({
        type: "POST",
        dataType: "json",
        data: {
            get_option: val,
            action: "inputSubSubSelect"
        },
        beforeSend: function(x) {
        if (x && x.overrideMimeType) {x.overrideMimeType("application/json;charset=UTF-8");}},
        url: '/api/ssubgrupodearticulos/update/index.php',
        success: function(data) {
          if (data.success == true) 
          {
            document.getElementById("cve_ssgpo").innerHTML = data.response;
          }
        }
      });
    }

    function editar(_codigo) 
    {
        $("#input-2").fileinput('reset');
        $("#upload").show();
        $("#arti").hide();
        $("#_title").html('<h3>Editar Artículo</h3>');
        $("#hiddenID").val(_codigo);
        $("#CodeMessage").html("");
        $("#CodeMessage2").html("");
        $("#CodeMessage3").html("");
        $("#CodeMessage4").html("");
        $("#cve_almac").val($("#almacenes").val());
        
        console.log("editar() = ",_codigo, "almacen = ", $("#almacenes").val());

        $.ajax({
            type: "POST",
            dataType: "json",
            url: '/api/articulos/update/index.php',
            data: 
            {
              action: "load",
              almacen: $("#almacenes").val(),
              cve_articulo: _codigo
            },
            beforeSend: function(x) {if (x && x.overrideMimeType) {x.overrideMimeType("application/json;charset=UTF-8");}},
            success: function(data) 
            {
                console.log("datos",data);
                if (data.success == true) 
                {
                    $("#control_caducidad_div").show();

                    <?php 
                    if(strtolower($_SESSION['cve_usuario']) != 'wmsmaster' && strtolower($_SESSION['cve_usuario']) != 'cgonzalez' && strtolower($_SESSION['cve_usuario']) != 'agonzalez' && strtolower($_SESSION['cve_usuario']) != 'jlopez' && strtolower($_SESSION['cve_usuario']) != 'vicrfsdelgado' && strtolower($_SESSION['cve_usuario']) != 'jgomez')
                    {
                    ?>
                    //************************************************
                    //          DESHABILITAR CHECKS AL EDITAR
                    //************************************************
                    $("#control_lotes").prop('disabled', true);
                    $("#control_caducidad").prop('disabled', true);
                    $("#control_numero_series").prop('disabled', true);
                    $("#control_peso").prop('disabled', true);
                    $("#control_volumen").prop('disabled', true);
                    $("#compuesto").prop('disabled', true);
                    $("#req_refrigeracion").prop('disabled', true);
                    $("#mat_peligroso").prop('disabled', true);
                    //$("#control_abc").prop('disabled', true);
                    //$("#control_tipo_A").prop('disabled', true);
                    //$("#control_tipo_B").prop('disabled', true);
                    //$("#control_tipo_C").prop('disabled', true);
                    $("#envase").prop('disabled', true);
                    $("#tipo_envase_plastico").prop('disabled', true);
                    $("#tipo_envase_cristal").prop('disabled', true);
                    $("#tipo_envase_garrafon").prop('disabled', true);
                    $("#usa_envase").prop('disabled', true);
                    //************************************************/
                    <?php 
                    }
                    ?>


                    $('#codigo').prop('disabled', true);
                    $('#barras2').prop('disabled', false);
                    $('#barras3').prop('disabled', false);
                    $('#cve_codprov').prop('disabled', false);
                    //$('#cve_almac').prop('disabled', true);
                    $.each(data, function(key, value) 
                    {
                        if (key != "imagen")
                        {
                           $('#' + key).val(value);
                        }
                    });
                    var iva = data.mav_pctiva;
                    $('#codigo').val(data.cve_articulo);
                    if(!isNaN(iva) && iva) $('#iva').val(parseFloat(iva).toFixed(2)); else $('#iva').val(0);
                    $('#iva').val(parseFloat(iva).toFixed(2));
                    $("#hiddenID").val(_codigo);

                    var IEPS = data.IEPS;
                    console.log("IEPS edit = ", IEPS);
                    if(!isNaN(IEPS) && IEPS) $('#IEPS').val(parseFloat(IEPS).toFixed(2)); else $('#IEPS').val(0);

                    //$("#tipo_caja").val(data.tipo_caja).change();
                    if (data.control_lotes == "1"){$("#control_lotes").prop("checked", "checked");}
                    if (data.Caduca == "1"){$("#control_caducidad").prop("checked", "checked");}
                    if (data.control_numero_series == "1"){$("#control_numero_series").prop("checked", "checked");}
                    if (data.control_peso == "1"){$("#control_peso").prop("checked", "checked");/*$("#peso").prop('disabled', true);*/}
                    if (data.control_volumen == "1"){$("#control_volumen").prop("checked", "checked");}
                    if (data.req_refrigeracion == "1"){$("#req_refrigeracion").prop("checked", "checked");}
                    if (data.compuesto == "1"){$("#compuesto").prop("checked", "checked");}
                    if (data.mat_peligroso == "on"){$("#mat_peligroso").prop("checked", "checked");}
                    if (data.ban_envase == "1")
                    {
                        $("#envase").prop("checked", "checked");
                        $(".tipo_envase").show();
                        if (data.Tipo_Envase == "P"){$("#tipo_envase_plastico").prop("checked", "checked");}
                        if (data.Tipo_Envase == "C"){$("#tipo_envase_cristal").prop("checked", "checked");}
                        if (data.Tipo_Envase == "G"){$("#tipo_envase_garrafon").prop("checked", "checked");}
                    }
                    if (data.control_abc != "N")
                    {
                        if (data.control_abc == "A")
                        {
                            $("#control_tipo_A").prop("checked", "checked");
                            $("#control_abc").prop("checked", "checked");
                            $(".tipo_ABC").show();
                        }
                        if (data.control_abc == "B")
                        {
                            $("#control_tipo_B").prop("checked", "checked");
                            $("#control_abc").prop("checked", "checked");
                            $(".tipo_ABC").show();
                        }
                        if (data.control_abc == "C")
                        {
                            $("#control_tipo_C").prop("checked", "checked");
                            $("#control_abc").prop("checked", "checked");
                            $(".tipo_ABC").show();
                        }
                    }
                    if (data.usa_envase == "1")
                    {
                        $("#usa_envase").prop("checked", "checked");
                        $(".usa_envase_datos, .datos_envase2").show();
                        $("#agregar_envase2").hide();
                        var envases_productos = data.envase_productos;
                        if(envases_productos != '')
                        {
                            var arr_env = envases_productos.split(";;;;;");
                            if(arr_env.length == 2)
                            {
                                $("#envase1").val(arr_env[0]);
                                $("#envase2").val(arr_env[1]);

                                var cantidades_bases = data.cantidad_base;
                                if(cantidades_bases != '')
                                {
                                    var arr_cantb = cantidades_bases.split(";;;;;");
                                    $("#cantidad_base1").val(arr_cantb[0]);
                                    $("#cantidad_base2").val(arr_cantb[1]);
                                }
                                
                                var cantidades_equiv = data.cantidad_equivalente;

                                if(cantidades_equiv != '')
                                {
                                    var arr_canteq = cantidades_equiv.split(";;;;;");
                                    $("#cantidad_equivalente1").val(arr_canteq[0]);
                                    $("#cantidad_equivalente2").val(arr_canteq[1]);
                                }
                            }
                            else
                            {
                                $("#envase1").val(data.envase_productos);
                                $("#cantidad_base1").val(data.cantidad_base);
                                $("#cantidad_equivalente1").val(data.cantidad_equivalente);
                            }
                        }
                    }
                    //Construyo la parte de las imagenes
                    $('#upload div').remove();
                    $('#upload img').remove();
                    $('#upload a').remove();
                    for (var i = 0; i < data.fotos.length; i++) 
                    {
                        $('#upload').append('<img src="../img/articulo/' + data.fotos[i].url + '" width="100" height="100">');
                    }
                    l.ladda('stop');
                    $("#btnCancel").show();
                    $('#list').removeAttr('class').attr('class', '');
                    $('#list').addClass('animated');
                    $('#list').addClass("fadeOutRight");
                    $('#arti').hide();
                    $('#FORM').show();
                    $('#FORM').removeAttr('class').attr('class', '');
                    $('#FORM').addClass('animated');
                    $('#FORM').addClass("fadeInRight");
                    $("#hiddenAction").val("edit");

                    $("#tipo_caja").val(data.id_tipocaja);
                    $("#descripcion_caja").val(data.des_caja);
                    $("#peso_caja").val(data.peso_caja);

                    $("#stock_minimo").val(data.stock_minimo);
                    $("#stock_maximo").val(data.stock_maximo);

                    var alto_c = parseFloat(data.alto_caja); if(isNaN(alto_c)) alto_c = 0;
                    $("#altotc").val(alto_c);
                    var ancho_c = parseFloat(data.ancho_caja); if(isNaN(ancho_c)) ancho_c = 0;
                    $("#anchotc").val(ancho_c);
                    var fondo_c = parseFloat(data.largo_caja); if(isNaN(fondo_c)) fondo_c = 0;
                    $("#fondotc").val(fondo_c);
                    //console.log("data.Id_Carac = ", data.Id_Carac);
                    var id_carac_val = data.Id_Carac;

                    var id_carac_vec = '';

                    if(id_carac_val)
                    {
                       id_carac_vec = id_carac_val.split(",");
                    }
                    $("#unidadMedida_empaque").val(data.empq_cveumed);
                    $("#caracteristicas_art").select2().val(id_carac_vec).trigger('change');
                    //['1', '2']
                    //$("#caracteristicas_art").select2().text(id_carac_vec).trigger('change');
                    //$("#caracteristicas_art").select2().attr("aria-selected", "true").trigger('change');
                    //$('#caracteristicas_art').select2('data', {id: 23, text: 'text1'});

                }
            }, error: function(data) 
            {
                console.log("ERROR datos = ",data);
            }

        });
    }

    function ver(_codigo) 
    {
        $("#upload").show();
        $("#hiddenID").val(_codigo);
        $("#CodeMessage").html("");
        $("#CodeMessage2").html("");
        $("#CodeMessage3").html("");
        $("#CodeMessage4").html("");
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                cve_articulo: _codigo,
                action: "load"
            },
            beforeSend: function(x) {
                if (x && x.overrideMimeType) {x.overrideMimeType("application/json;charset=UTF-8");}
            },
            url: '/api/articulos/update/index.php',
            success: function(data) {
                if (data.success == true) 
                {
                    $.each(data, function(key, value) {
                        if (key != "imagen")
                        {
                          $('#' + key).val(value);
                        }
                    });
                    $(".modal-subtitle").text(data.des_articulo);
                    $('#codigo_ver').val(data.cve_articulo);
                    $('#cve_codprov_ver').val(data.cve_codprov);
                    $('#des_articulo_ver').val(data.des_articulo);
                    $('#peso_ver').val(data.peso);
                    $('#barras2_ver').val(data.barras2);
                    $('#num_multiplo_ver').val(data.num_multiplo);
                    $('#barras3_ver').val(data.barras3);
                    $('#cajas_palet_ver').val(data.cajas_palet);
                    $('#grupo_ver').val(data.des_grupo);
                    $('#clasificacion_ver').val(data.des_clasificacion);
                    $('#tipo_ver').val(data.des_tipo);
                    $("#hiddenID").val(_codigo);
                    //Construyo la parte de las imagenes
                    $('#upload2 div').remove();
                    $('#upload2 img').remove();
                    $('#upload2 a').remove();
                    for (var i = 0; i < data.fotos.length; i++) 
                    {
                        $('#upload2').append('<a href="../img/articulo/' + data.fotos[i].url + '" title="Image from Unsplash" data-gallery=""><img src="../img/articulo/' + data.fotos[i].url + '" width="100" height="100"></a>');
                    }
                    l.ladda('stop');
                    $("#btnCancel").show();
                    $("#btnCancel").show();
                    $modal0 = $("#modalVer");
                    $modal0.modal('show');
                }
            }
        });
    }

    function cancelar() 
    {
        console.log("Cancelar");
        $("#input-2").fileinput('reset');
        $('#upload div').remove();
        $('#upload img').remove();
        $('#upload a').remove();
        $(':input', '#myform')
            .removeAttr('checked')
            .removeAttr('selected')
            .not(':button, :submit, :reset, :hidden, :radio, :checkbox')
            .val('');
        $('#FORM').removeAttr('class').attr('class', '');
        $('#FORM').addClass('animated');
        $('#FORM').addClass("fadeOutRight");
        $('#FORM').hide();
        $('#arti').show();
        $('#list').removeAttr('class').attr('class', '');
        $('#list').addClass('animated');
        $('#list').addClass("fadeInRight");
        $('#list').addClass("wrapper");
        $('#list').addClass("wrapper-content");
        //window.location.reload();
    }

    function agregar() 
    {
        console.log("Agregar");
        $("#input-2").fileinput('reset');
        $('#upload div').remove();
        $('#upload img').remove();
        $('#upload a').remove();
        //$('#cve_almac').val("");
        //$('#tipo_caja').val("");
        $('#grupo').val("");
        $('#clasificacion').val("");
        $('#tipo').val("");
        $("#imagen").val("");
        $("#upload").hide();
        $("#image").prop("src", "");
        $("#_title").html('<h3>Agregar Artículo</h3>');
        $(':input', '#myform')
            .removeAttr('checked')
            //.removeAttr('selected')
            .not(':button, :submit, :reset, :hidden, :radio, :checkbox')
            .val('');
        $("#CodeMessage").html("");
        $("#CodeMessage2").html("");
        $("#CodeMessage3").html("");
        $("#CodeMessage4").html("");
        $('#list').removeAttr('class').attr('class', '');
        $('#list').addClass('animated');
        $('#list').addClass("fadeOutRight");
        $('#arti').hide();
        $('#FORM').show();
        $('#FORM').removeAttr('class').attr('class', '');
        $('#FORM').addClass('animated');
        $('#FORM').addClass("fadeInRight");
        $("#hiddenAction").val("add");
        $('#codigo').prop('disabled', false);
        $('#barras2').prop('disabled', false);
        $('#barras3').prop('disabled', false);
        $('#cve_codprov').prop('disabled', false);

        //************************************************
        //          HABILITAR CHECKS AL EDITAR
        //************************************************
            $("#control_lotes").prop('disabled', false);
            $("#control_caducidad").prop('disabled', false);
            $("#control_numero_series").prop('disabled', false);
            $("#control_peso").prop('disabled', false);
            $("#control_volumen").prop('disabled', false);
            $("#compuesto").prop('disabled', false);
            $("#req_refrigeracion").prop('disabled', false);
            $("#mat_peligroso").prop('disabled', false);
            $("#envase").prop('disabled', false);
                    $("#tipo_envase_plastico").prop('disabled', false);
                    $("#tipo_envase_cristal").prop('disabled', false);
                    $("#tipo_envase_garrafon").prop('disabled', false);
            $("#usa_envase").prop('disabled', false);

            $("#control_abc").prop('disabled', false);
                    $("#control_tipo_A").prop('disabled', false);
                    $("#control_tipo_B").prop('disabled', false);
                    $("#control_tipo_C").prop('disabled', false);

            $("#peso").prop('disabled', false);
        //************************************************/

        $("#unidadMedida_empaque option").each(function(index){

            if($("#unidadMedida").val() == $(this).attr("value"))
               $(this).hide();
           else 
               $(this).show();

        });

    }
/*
    $("#control_peso").change(function() {
        if(this.checked) 
        {
            //console.log("ON");
            //$("#peso").prop('disabled', true);
            //$("#peso").val(1);
        }
        else
        {
            //console.log("OFF");
            //$("#peso").prop('disabled', false);
            //$("#peso").val(0);
        } 

    });
*/
    
    $("#unidadMedida").change(function(){

        var id_medida = $(this).val();

        $("#unidadMedida_empaque option").each(function(index)
        {
               $(this).remove();
        });

        $("#unidadMedida option").each(function(index)
        {
            if(id_medida != $(this).attr("value"))
            {
               $("#unidadMedida_empaque").append("<option value='"+$(this).attr("value")+"'>"+$(this).text()+"</option>")
            }
        });

    });
    //Mostrar o no Control de CADUCIDAD
/*
    $('#control_lotes').change(function() 
    {
        if($("#control_lotes").is(':checked'))
        {
            $("#control_caducidad_div").show();
        }
        else
        {
            $("#control_caducidad_div").hide();
        }
    });
*/
    $('#control_lotes').change(function() 
    {
        if($("#control_lotes").is(':checked'))
        {
            console.log("lotes checked");
            $("#control_caducidad_div").show();
            $('#control_numero_series').prop('checked', false);
        }
        else
            $('#control_caducidad').prop('checked', false);
    });

    $('#control_caducidad').change(function() 
    {
        if(!$("#control_lotes").is(':checked'))
        {
            $('#control_caducidad').prop('checked', false);
        }
    });

    $('#control_numero_series').change(function() 
    {
        if($("#control_numero_series").is(':checked'))
        {
            console.log("series checked");
            $("#control_caducidad_div").hide();
            $('#control_lotes').prop('checked', false);
            $('#control_caducidad').prop('checked', false);
        }
    });

    $('#envase').change(function() 
    {
            $('#usa_envase').prop('checked', false);

            if($("#envase").is(':checked'))
                $('.tipo_envase').show();
            else
                $('.tipo_envase').hide();

            $('#tipo_envase_plastico').prop('checked', false);
            $('#tipo_envase_cristal').prop('checked', false);
            $('#tipo_envase_garrafon').prop('checked', false);
    });

    $('#tipo_envase_plastico').change(function() 
    {
        $('#tipo_envase_cristal').prop('checked', false);
        $('#tipo_envase_garrafon').prop('checked', false);
    });

    $('#tipo_envase_cristal').change(function() 
    {
        $('#tipo_envase_plastico').prop('checked', false);
        $('#tipo_envase_garrafon').prop('checked', false);
    });

    $('#tipo_envase_garrafon').change(function() 
    {
        $('#tipo_envase_plastico').prop('checked', false);
        $('#tipo_envase_cristal').prop('checked', false);
    });


    $('#usa_envase').change(function() 
    {
            $('#envase').prop('checked', false);
            $('.tipo_envase').hide();
            $('#tipo_envase_plastico').prop('checked', false);
            $('#tipo_envase_cristal').prop('checked', false);
            $('#tipo_envase_garrafon').prop('checked', false);

            $(".usa_envase_datos").show(500);
            if(!$("#usa_envase").is(':checked'))
            {
                $(".usa_envase_datos, .datos_envase2").hide(500);
            }

    });

    $('#control_abc').change(function() 
    {
        if($("#control_abc").is(':checked'))
            $('.tipo_ABC').show();
        else
            $('.tipo_ABC').hide();

        $('#control_tipo_A').prop('checked', false);
        $('#control_tipo_B').prop('checked', false);
        $('#control_tipo_C').prop('checked', false);
    });

    $('#control_tipo_A').change(function() 
    {
        $('#control_tipo_B').prop('checked', false);
        $('#control_tipo_C').prop('checked', false);
    });

    $('#control_tipo_B').change(function() 
    {
        $('#control_tipo_A').prop('checked', false);
        $('#control_tipo_C').prop('checked', false);
    });

    $('#control_tipo_C').change(function() 
    {
        $('#control_tipo_A').prop('checked', false);
        $('#control_tipo_B').prop('checked', false);
    });

    $("#replicar-productos").click(function(){
        $("#replicar-modal").modal('show');
    });

    $("#btn-replicar").click(function(){

        if($("#almacen-replicar").val() == '')
        {
            swal("Error", "Debe Seleccionar un Almacén", "error");
            return;
        }else
        {
            swal({
                title: "Advertencia",
                text: "Esta acción replicará todos los artículos de este almacén al almacén seleccionado, los productos que ya existen en el almacén destino no se tomarán en cuenta. \n¿Desea Continuar?",
                type: "info",

                cancelButtonText: "No",
                cancelButtonColor: "#14960a",
                showCancelButton: true,

                confirmButtonColor: "#55b9dd",
                confirmButtonText: "Si",
                closeOnConfirm: true
            }, function() {
                console.log("Replicar");
                $("#replicar-modal").modal('hide');

                $.ajax({
                    type: "POST",
                    dataType: "json",
                    data:
                    {
                        id_almacen_origen: $("#cve_almac").val(),
                        id_almacen_replicar: $("#almacen-replicar").val(),                        
                        action: "ReplicarArticulos"
                    },
                    beforeSend: function(x) {if (x && x.overrideMimeType) {x.overrideMimeType("application/json;charset=UTF-8");}},
                    url: '/api/articulos/update/index.php',
                    success: function(data)
                    {
                        swal("Éxito", "Artículos Replicados con éxito", "success");
                    }
                });

            });
        }
    });




    var l = $('.ladda-button').ladda();
    l.click(function() 
    {
        //if($("#codigo").val() == ""){return;}
        if($("#num_multiplo").val() == "")
        {
            swal("Error", "Equivalencia (Cajas|Kg|Lt), Debe ser mayor a cero", "warning");
            return;
        }
        if($("#des_articulo").val() == "")
        {
            swal("Error", "Falta la descripción", "warning");
            return;
        }
        if ($("#codigo").val() == "") 
        {
            swal("Error", "Falta el código del producto", "warning");
            return;
        }
        if ($("#unidadMedida").val() == "") 
        {
            swal("Error", "Falta la unidad de medida", "warning");
            return;
        }
/*
        if ($("#cve_proveedor").val() == "" && $("#cboProveedor").val() == "") 
        {
            swal("Error", "Debe Seleccionar un proveedor", "error");
            return;
        }
*/
/*
        if ($("#unidadMedida_empaque").val() == "" && $("#barras2").val() != "") 
        {
            swal("Error", "Falta la unidad de medida de empaque", "warning");
            return;
        }
*/
        if ($("#descripcion").val() == "") 
        {
            swal("Error", "Falta la descripción", "warning");
            return;
        }

        if($("#envase").is(':checked') && !$("#tipo_envase_plastico").is(':checked') && !$("#tipo_envase_cristal").is(':checked') && !$("#tipo_envase_garrafon").is(':checked'))
        {
            swal("Error", "Debe Seleccionar un tipo de envase", "warning");
            return;
        }

        if($("#usa_envase").is(':checked') && $("#envase1").val() == $("#envase2").val())
        {
            swal("Error", "No Puede seleccionar el mismo envase", "error");
            return;
        }

        if($("#usa_envase").is(':checked') && ($("#envase1").val() == '' || $("#cantidad_base1").val() == '' || $("#cantidad_equivalente1").val() == ''))
        {
            swal("Error", "Debe Seleccionar un envase y colocar las cantidades", "error");
            return;
        }


        //if($("#usa_envase").is(':checked') && ($("#envase2").val() == '' || $("#cantidad_base2").val() == '' || $("#cantidad_equivalente2").val() == ''))
        //{
        //    swal("Error", "Debe Seleccionar un envase y colocar las cantidades", "error");
        //    return;
        //}



/*
        if($("#control_abc").is(':checked') && !$("#control_tipo_A").is(':checked') && !$("#control_tipo_B").is(':checked') && !$("#control_tipo_C").is(':checked'))
        {
            swal("Error", "Debe Seleccionar un control ABC", "warning");
            return;
        }
*/

        if($("#control_peso").is(':checked') && ($("#peso").val() == '0' || $("#peso").val() == ''))
        {
            swal("Error", "Los productos con bandera de control de peso deben tener peso registrado mayor a cero", "error");
            return;
        }

        $("#btnCancel").hide();
        l.ladda('start');
        var fotos = [];
        var filename = "noimage.jpg";
        var path = "";
        var input = $("#input-2");
        if (input[0].files.length == 0) 
        {
            fotos.push(filename);
        } 
        else 
        {
            for (var i = 0; i < input[0].files.length; i++) 
            {
                path = input[0].files[i].name;
                filename = path.replace(/^.*\\/, "");
                fotos.push(filename);
            }
            uploadFile();
        }

/*
        //ANTES DE BORRAR AVERIGUAR PORQUÉ ESTA INSTRUCCIÓN
        if($("#num_multiplo").val() > 0)
            $("#unidadMedida").val("2");
        else 
            $("#unidadMedida").val("");
*/
        if ($("#hiddenAction").val() == "add") 
        {

            $.ajax({
                type: "POST",
                dataType: "json",
                url: '/api/articulos/update/index.php',
                data: 
                {
                    action: "add",
                    cve_almac: $("#cve_almac").val(),
                    cve_articulo: $("#codigo").val(),
                    cve_codprov: $("#cve_codprov").val(),
                    des_articulo: $("#des_articulo").val(),
                    des_detallada: $("#des_detallada").val(),
                    peso: $("#peso").val(),
                    costo: $("#costo").val(),
                    iva: $("#iva").val(),
                    IEPS: $("#IEPS").val(),
                    barras2: $("#barras2").val(),
                    num_multiplo: $("#num_multiplo").val(),
                    barras3: $("#barras3").val(),
                    cajas_palet: $("#cajas_palet").val(),
                    control_lotes: $("#control_lotes").prop("checked"),
                    control_caducidad: $("#control_caducidad").prop("checked"),
                    control_numero_series: $("#control_numero_series").prop("checked"),
                    control_peso: $("#control_peso").prop("checked"),
                    envase: $("#envase").prop("checked"),
                    tipo_envase_plastico: $("#tipo_envase_plastico").prop("checked"),
                    tipo_envase_cristal: $("#tipo_envase_cristal").prop("checked"),
                    tipo_envase_garrafon: $("#tipo_envase_garrafon").prop("checked"),
                    usa_envase: $("#usa_envase").prop("checked"),
                    envase1: $("#envase1").val(),
                    envase2: $("#envase2").val(),
                    cantidad_base1: $("#cantidad_base1").val(),
                    cantidad_base2: $("#cantidad_base2").val(),
                    cantidad_equivalente1: $("#cantidad_equivalente1").val(),
                    cantidad_equivalente2: $("#cantidad_equivalente2").val(),
                    control_abc: $("#control_abc").prop("checked"),
                    control_tipo_A: $("#control_tipo_A").prop("checked"),
                    control_tipo_B: $("#control_tipo_B").prop("checked"),
                    control_tipo_C: $("#control_tipo_C").prop("checked"),
                    control_volumen: $("#control_volumen").prop("checked"),
                    descripcion_caja: $("#descripcion_caja").val(),
                    peso_caja: $("#peso_caja").val(),
                    altotc: $("#altotc").val(),
                    anchotc: $("#anchotc").val(),
                    fondotc: $("#fondotc").val(),
                    stock_minimo: $("#stock_minimo").val(),
                    stock_maximo: $("#stock_maximo").val(),
                    req_refrigeracion: $("#req_refrigeracion").prop("checked"),
                    compuesto: $("#compuesto").prop("checked"),
                    mat_peligroso: $("#mat_peligroso").prop("checked"),
                    replicar_articulos: $("#replicar_articulos").prop("checked"),
                    grupo: $("#grupo").val(),
                    tipo: $("#tipo").val(),
                    clasificacion: $("#clasificacion").val(),
                    cve_proveedor: ($("#cve_proveedor").val())?($("#cve_proveedor").val()):($("#cboProveedor").val()),
                    //tipo_caja: $("#tipo_caja").val(),
                    //cve_tipcaja: $("#cve_tipcaja").val(),
                    alto: $("#alto").val(),
                    fondo: $("#fondo").val(),
                    tipo_producto: $("#tipo_producto").val(),
                    umas: $("#umas").val(),
                    unidadMedida: $("#unidadMedida").val(),
                    unidadMedida_empaque: $("#unidadMedida_empaque").val(),
                    caracteristicas_art: $("#caracteristicas_art").val(),
                    ancho: $("#ancho").val(),
                    fotos: fotos
                },
                beforeSend: function(x) {if (x && x.overrideMimeType) {x.overrideMimeType("application/json;charset=UTF-8");}},
                success: function(data) 
                {
                    console.log("data save = ", data);
                    if (data.success == true) 
                    {
                        cancelar();
                        ReloadGrid();
                        l.ladda('stop');
                        $("#btnCancel").show();
                        window.location.reload();
                    } 
                    else
                    {
                        //alert(data.err);
                        swal("Error", "Los códigos de barra usados ya están ocupados", "error");
                        l.ladda('stop');
                        $("#btnCancel").show();
                    }
                },
                error: function(data) 
                {
                    console.log("ERROR data save = ", data);
                    alert(data.err);
                    l.ladda('stop');
                    $("#btnCancel").show();
                }
            });
        }
        else 
        {
            console.log("fotos = ", fotos);
            console.log("cantidad_equivalente1 = ", $("#cantidad_equivalente1").val());
            console.log("cantidad_equivalente2 = ", $("#cantidad_equivalente2").val());
            //return;
            $.ajax({
                type: "POST",
                dataType: "json",
                data: 
                {
                    id: $("#hiddenID").val(),
                    cve_almac: $("#cve_almac").val(),
                    cve_articulo: $("#codigo").val(),
                    cve_codprov: $("#cve_codprov").val(),
                    des_articulo: $("#des_articulo").val(),
                    des_detallada: $("#des_detallada").val(),
                    peso: $("#peso").val(),
                    costo: $("#costo").val(),
                    iva: $("#iva").val(),
                    IEPS: $("#IEPS").val(),
                    barras2: $("#barras2").val(),
                    num_multiplo: $("#num_multiplo").val(),
                    barras3: $("#barras3").val(),
                    cajas_palet: $("#cajas_palet").val(),
                    control_lotes: $("#control_lotes").prop("checked"),
                    control_caducidad: $("#control_caducidad").prop("checked"),
                    control_numero_series: $("#control_numero_series").prop("checked"),
                    control_peso: $("#control_peso").prop("checked"),
                    envase: $("#envase").prop("checked"),
                    tipo_envase_plastico: $("#tipo_envase_plastico").prop("checked"),
                    tipo_envase_cristal: $("#tipo_envase_cristal").prop("checked"),
                    tipo_envase_garrafon: $("#tipo_envase_garrafon").prop("checked"),
                    usa_envase: $("#usa_envase").prop("checked"),
                    envase1: $("#envase1").val(),
                    envase2: $("#envase2").val(),
                    cantidad_base1: $("#cantidad_base1").val(),
                    cantidad_base2: $("#cantidad_base2").val(),
                    cantidad_equivalente1: $("#cantidad_equivalente1").val(),
                    cantidad_equivalente2: $("#cantidad_equivalente2").val(),
                    control_abc: $("#control_abc").prop("checked"),
                    control_tipo_A: $("#control_tipo_A").prop("checked"),
                    control_tipo_B: $("#control_tipo_B").prop("checked"),
                    control_tipo_C: $("#control_tipo_C").prop("checked"),
                    control_volumen: $("#control_volumen").prop("checked"),
                    stock_minimo: $("#stock_minimo").val(),
                    stock_maximo: $("#stock_maximo").val(),
                    req_refrigeracion: $("#req_refrigeracion").prop("checked"),
                    compuesto: $("#compuesto").prop("checked"),
                    mat_peligroso: $("#mat_peligroso").prop("checked"),
                    replicar_articulos: $("#replicar_articulos").prop("checked"),
                    grupo: $("#grupo").val(),
                    tipo: $("#tipo").val(),
                    clasificacion: $("#clasificacion").val(),
                    cve_proveedor: ($("#cve_proveedor").val())?($("#cve_proveedor").val()):($("#cboProveedor").val()),
                    descripcion_caja: $("#descripcion_caja").val(),
                    peso_caja: $("#peso_caja").val(),
                    altotc: $("#altotc").val(),
                    anchotc: $("#anchotc").val(),
                    fondotc: $("#fondotc").val(),
                    tipo_caja: $("#tipo_caja").val(),
                    //cve_tipcaja: $("#cve_tipcaja").val(),
                    alto: $("#alto").val(),
                    fondo: $("#fondo").val(),
                    tipo_producto: $("#tipo_producto").val(),
                    umas: $("#umas").val(),
                    unidadMedida: $("#unidadMedida").val(),
                    unidadMedida_empaque: $("#unidadMedida_empaque").val(),
                    caracteristicas_art: $("#caracteristicas_art").val(),
                    ancho: $("#ancho").val(),
                    fotos: fotos,
                    action: "edit"
                },
                beforeSend: function(x) {if (x && x.overrideMimeType) {x.overrideMimeType("application/json;charset=UTF-8");}},
                url: '/api/articulos/update/index.php',
                success: function(data) 
                {
                    if (data.success == true) 
                    {
                        cancelar();
                        ReloadGrid();
                        l.ladda('stop');
                        $("#btnCancel").show();
                        window.location.reload();
                    } 
                    else 
                    {
                        if(data.err == 'error2')
                        {
                            swal("Error", "El código de barras usado ya existe en otro artículo", "error");
                        }
                        //alert(data.err);
                        l.ladda('stop');
                        $("#btnCancel").show();
                    }
                }, error: function(data) 
                   {
                        console.log("ERROR datos = ",data);
                   }
            });
        }
    });

/*
    $("#num_multiplo").keyup(function()
    {
        if($(this).val() > 0)
            $("#unidadMedida").val("2");
        else 
            $("#unidadMedida").val("");
    });
*/

/*
    $("#codigo").keydown(function(){

        $("#codigo_msg").text("OK");

    });
*/
    function revisar_codigos(texto,campo)
    {
        console.log(texto,campo);
        $("#btnSave").prop('disabled', false);
        var codigo_barras1, codigo_barras2, codigo_barras3;
        var claveCode = texto;

        //var claveCodeRegexp = new RegExp("^[a-zA-Z0-9]{1,30}$");
        //if (/*claveCodeRegexp.test(texto) ||*/ claveCode == "") 
        //{
            var sin_considencias = false;
            var clave_articulo = $("#codigo").val();
            var codigo_barras1 = $("#cve_codprov").val();
            var codigo_barras2 = $("#barras2").val();
            var codigo_barras3 = $("#barras3").val();
            var to_bdd = false;

            if(codigo_barras1 != "" || codigo_barras2 != "" || codigo_barras3 != "")
            {
                var msg = "El Código de Barras ya fue incluido en otro campo";
                var a = (codigo_barras1==codigo_barras2)&&(codigo_barras1!="");
                var b = (codigo_barras1==codigo_barras3)&&(codigo_barras1!="");
                var c = (codigo_barras2==codigo_barras3)&&(codigo_barras2!="");
                if(a||b||c)
                {
                    $("#"+campo+"_msg").html(msg);
                    $("#btnSave").prop('disabled', true);
                }
                else
                {
                    to_bdd = true;
                    $("#"+campo+"_msg").html("");
                    $("#btnSave").prop('disabled', false);
                }
            }
            else 
            {
                to_bdd = true;
                $("#"+campo+"_msg").html("");
            }
            if(to_bdd){revisar_codigos_bdd(texto,campo);}
        //}
        //else
        //{
        //  $("#"+campo+"_msg").html("Este codigo es invalido, debe tener numeros y/o letras y ser de 1 a 30 caracteres");
        //}

//        var nopermitidos = [' ','-','/','\\','Ñ',',',';','.',':','{','}','Ç','¨','´','Á','É','Í','Ó','Ú','À','È','Ì','Ò','Ù','+','[',']','*','^','`','¡','¿',"'",'"','?','=','(',')','&','%','$','·','#','@','|','!','º','ª'];
//
//        if($.inArray(e.key.toUpperCase(), nopermitidos) != -1)
//        {
//            console.log("NO = ", e.key);
//            $(this).val("");
//
//        }

    }
      
    function revisar_codigos_bdd(texto,campo)
    {
        console.log("revisar_codigos_bdd texto = ", texto, "revisar_codigos_bdd campo = ", campo);
        $.ajax({
            type: "POST",
            dataType: "json",
            data:
            {
                clave_producto: $('#codigo').val(),
                search: texto,
                id_almacen: <?php echo $_SESSION['id_almacen']; ?>,
                action: "exists"
            },
            beforeSend: function(x) {if (x && x.overrideMimeType) {x.overrideMimeType("application/json;charset=UTF-8");}},
            url: '/api/articulos/update/index.php',
            success: function(data)
            {
                console.log("revisar_codigos_bdd = ", data);
                if(data.success == false)
                {
                    $("#"+campo+"_msg").html("");
                    $("#btnSave").prop('disabled', false);
                }
                else
                {
                    console.log("success_mismo_almacen = ",data.success_mismo_almacen);
                    if(data.success_mismo_almacen == true)
                    {
                        $("#btnSave").prop('disabled', true);
                    }
                    else 
                        $("#btnSave").prop('disabled', false);

                    console.log("success_otro_almacen = ",data.success_otro_almacen);
                    if(data.success_otro_almacen == true && campo == 'codigo')
                    {
                        $("#btnSave").prop('disabled', true);
                        $("#"+campo+"_msg").html("Este código ya se encuentra en uso en otro almacén, ¿Desea Copiarlo a este almacén? <input type='button' id='si_copiar' class='btn btn-primary' value='Si' > <input type='button' id='no_copiar' class='btn btn-danger' value='No' >");

                            $("#si_copiar").click(function(){
                                console.log("copiar artículo");
                              $.ajax({
                                type: "POST",
                                dataType: "json",
                                url: '/api/articulos/update/index.php',
                                data: {
                                  action : "CopiarArticuloA_Almacen",
                                  cve_articulo : texto,
                                  id_almacen: <?php echo $_SESSION['id_almacen']; ?>
                                },
                                beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }},
                                success: function(data) {
                                  if (data.success == true) 
                                  {
                                    swal("Exito","Se ha copiado El artículo "+texto, "success");
                                    window.location.reload();
                                  }
                                }
                              });

                            });

                            $("#no_copiar").click(function(){
                                $("#codigo").val("");
                                $("#"+campo+"_msg").html("");
                            });

                    }
                    else if(campo != 'codigo')
                    {
                        //$("#btnSave").prop('disabled', false);
                        //$("#"+campo+"_msg").html("Este código ya se encuentra en uso en otro producto");
                    }
                    
                    
                }
            }
        });
    }
      
    $("#codigo,#cve_codprov,#barras2,#barras3").keyup(function(e)
    {
       //console.log("texto, campo = ", $(this).val(),$(this).attr("id")) 
/*
       if(e.key == ' ')
       {
        console.log(" ESPACIO ");
        return false;
        var cve_codigo = $("#codigo").val();
        cve_codigo.replace(" ", "");
        $("#codigo").val(cve_codigo);
       }
*/
      revisar_codigos($(this).val(),$(this).attr("id"));
    });
        
    $("#codigo,#cve_codprov,#barras2,#barras3").keyup(function(e)
    {
        //var permitidos = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '_'], codigo = '';

        //var nopermitidos = [' ','-','/','\\','Ñ',',',';','.',':','{','}','Ç','¨','´','Á','É','Í','Ó','Ú','À','È','Ì','Ò','Ù','+','[',']','*','^','`','¡','¿',"'",'"','?','=','(',')','&','%','$','·','#','@','|','!','º','ª'];

        var nopermitidos = [' ','/','\\','Ñ',',',';',':','{','}','Ç','¨','´', 'Á','É','Í','Ó','Ú','À','È','Ì','Ò','Ù','+','[',']','*','^','`','¡','¿',"'",'"','?','=','(',')','&','%','$','·','#','@','|','!','º','ª'];
        if($.inArray(e.key.toUpperCase(), nopermitidos) != -1)
        {
            console.log("NO = ", e.key);
            //codigo = $(this).val();
            //console.log("CVE = ", codigo.length);
            //codigo[codigo.length] = '';
            //$(this).val(codigo);
            return false;
        }
        //console.log("KEY = ", e.key);

      $("#codigo_msg,#cve_codprov_msg,#barras2_msg,#barras3_msg").html("");
    });
      
    function codigo_de_barras_funcion(codex)
    {
        console.log(codex);
        $("#btnSave").prop('disabled', false);
        var codigo_barras1, codigo_barras2, codigo_barras3;
        var claveCode = codex;
        var claveCodeRegexp = new RegExp("^[a-zA-Z0-9]{1,30}$");
        if (claveCodeRegexp.test(claveCode) || claveCode == "") 
        {
            var sin_considencias = false;
            var clave_articulo = $("#codigo").val();
            var codigo_barras1 = $("#cve_codprov").val();
            var codigo_barras2 = $("#barras2").val();
            var codigo_barras3 = $("#barras3").val();
            
            if(codigo_barras1 != "" || codigo_barras2 != "" || codigo_barras3 != "")
            {
                var msg = "El Código de Barras ya fue incluido en otro campo";

                var a = (codigo_barras1==codigo_barras2)&&(codigo_barras1!="");
                if(a){$('#CodeMessage2,#CodeMessage3').html(msg); $("#btnSave").prop('disabled', true);}

                var b = (codigo_barras1==codigo_barras3)&&(codigo_barras1!="");
                if(b){$('#CodeMessage2,#CodeMessage4').html(msg); $("#btnSave").prop('disabled', true);}

                var c = (codigo_barras2==codigo_barras3)&&(codigo_barras2!="");
                if(c){$('#CodeMessage3,#CodeMessage4').html(msg); $("#btnSave").prop('disabled', true);}
                return 0;
            }
            else
            {
               sin_considencias = true;
            }
            
            //Verificar que no se repita codigo de barras en otro producto
            if(sin_considencias)
            {
                console.log("Buscar en bdd");
                $('#CodeMessage2,#CodeMessage3,#CodeMessage4').html("");
                $.ajax({
                    type: "POST",
                    dataType: "json",
                    data:
                    {
                        search: codex,
                        action: "exists"
                    },
                    beforeSend: function(x) {if (x && x.overrideMimeType) {x.overrideMimeType("application/json;charset=UTF-8");}},
                    url: '/api/articulos/update/index.php',
                    success: function(data)
                    {
                        if (data.success == false) 
                        {
                            mensaje.html("");
                            $("#btnSave").prop('disabled', false);
                        }
                        else 
                        {
                            mensaje.html(" Código de Barras ya existe en otro articulo");
                            $("#btnSave").prop('disabled', true);
                        }
                    }
                });
            }
        }
        else 
        {
            mensaje.html("Por favor, ingresar un Código de Barras válida");
            $("#btnSave").prop('disabled', true);
        }
    }
/*
    $('#tipo_caja').change(function(e) {
        var id_tipocaja = $(this).val();
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                id_tipocaja: id_tipocaja,
                action: "load"
            },
            beforeSend: function(x) {
                if (x && x.overrideMimeType) {
                    x.overrideMimeType("application/json;charset=UTF-8");
                }
            },
            url: '/api/tipocaja/update/index.php',
            success: function(data) {
                $('#cve_tipcaja').val(data.clave);
                $('#altotc').val(data.alto);
                $('#anchotc').val(data.ancho);
                $('#fondotc').val(data.largo);
            }
        });
    });
*/
    $("#peso").keydown(function(e) {
        // Allow: backspace, delete, tab, escape, enter and .
        if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
            // Allow: Ctrl/cmd+A
            (e.keyCode == 65 && (e.ctrlKey === true || e.metaKey === true)) ||
            // Allow: Ctrl/cmd+C
            (e.keyCode == 67 && (e.ctrlKey === true || e.metaKey === true)) ||
            // Allow: Ctrl/cmd+X
            (e.keyCode == 88 && (e.ctrlKey === true || e.metaKey === true)) ||
            // Allow: home, end, left, right
            (e.keyCode >= 35 && e.keyCode <= 39)) {
            // let it happen, don't do anything
            return;
        }

        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault();
        }
    });


    var checkEqual = function(current) {
        var elements = [
            document.getElementById('codigo'),
            document.getElementById('cve_codprov'),
            document.getElementById('barras2'),
            document.getElementById('barras3')
        ];

        var i = 0;
        var error = document.createElement('span');
        error.style.color = 'red';
        error.style.fontWeight = 'bold';
        error.textContent = 'El código está repetido';
        var spanSelector = '#' + current.id + ' + span';

        for (i; i < elements.length; i++) 
        {
            if (current.value == elements[i].value && current.id != elements[i].id && current.value != '') {
                if (!document.querySelector(spanSelector)) {
                    current.parentElement.appendChild(error);
                }
                current.focus();
                elements[i].style.border = "1px solid red";
                break;
            } 
            else
            {
                elements[i].style.border = "1px solid #e5e6e7";
                if (document.querySelector(spanSelector)) 
                {
                    console.log('existe');
                    current.parentNode.removeChild(document.querySelector(spanSelector));
                }
            }
        }
    }

    $("#agregar_envase2").click(function(){
        $(".datos_envase2").show(500);
    });


    $("#txtCriterio").keyup(function(event) {
        if (event.keyCode == 13) {
            $("#buscarA").click();
        }
    });


    function grupo() {
        $('#clasificacion_b')
            .find('option')
            .remove()
            .end();
        $(".itemlist").remove();

        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                grupo: $('#grupo_b').val(),
                action: "traerClasificacionDeGrupo"
            },
            beforeSend: function(x) {
                if (x && x.overrideMimeType) 
                {
                    x.overrideMimeType("application/json;charset=UTF-8");
                }
            },
            url: '/api/articulos/update/index.php',
            success: function(data) {
                if (data.success == true) 
                {
                    var options = $("#clasificacion_b");
                }
            }
        });
    }

    function clasificacion() 
    {
        $('#tipo_b')
            .find('option')
            .remove()
            .end();
        $(".itemlist").remove();

        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                clasificacion: $('#clasificacion_b').val(),
                action: "traerTipoDeClasificacion"
            },
            beforeSend: function(x) {
                if (x && x.overrideMimeType) 
                {
                    x.overrideMimeType("application/json;charset=UTF-8");
                }
            },
            url: '/api/articulos/update/index.php',
            success: function(data) {
                if (data.success == true){}
            }
        });
    }

    $("#inactivos").on("click", function() {
        $modal0 = $("#coModal");
        $modal0.modal('show');
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
            if (event_name === 'sidebar_collapsed' || event_name === 'main_container_fixed') 
            {
                //setTimeout is for webkit only to give time for DOM changes and then redraw!!!
                setTimeout(function() {
                    $(grid_selector).jqGrid('setGridWidth', parent_column.width());
                }, 0);
            }
        })

        $(grid_selector).jqGrid({
            url: '/api/articulos/lista/index_i.php',
            datatype: "json",
            height: 250,
            shrinkToFit: false,
            height: 'auto',
            postData: {
                criterio: $("#txtCriterio1").val()
            },
            mtype: 'POST',
            colNames: ['ID','Clave','CD Pza','Descripcion','PU Pza','CB Caja','Pzas * Caja','CB Pallet','Cajas * Pallet','Grupo','Clasificación','Tipo','Recuperar'],
            colModel: [
                //{name:'id',index:'id', width:60, sorttype:"int", editable: false},
                {name: 'id',index: 'id',width: 30,editable: false,hidden: true,sortable: false,resizable: true}, 
                {name: 'cve_articulo',index: 'cve_articulo',width: 60,editable: false,sortable: false,resizable: true}, 
                {name: 'cve_codprov',index: 'cve_codprov',width: 100,editable: false,sortable: false,resizable: true}, 
                {name: 'des_articulo',index: 'des_articulo',width: 160,editable: false,sortable: false,resizable: true}, 
                {name: 'peso',index: 'peso',width: 65,editable: false,sortable: false,resizable: true}, 
                {name: 'barras2',index: 'barras2',width: 65,editable: false,sortable: false,resizable: true}, 
                {name: 'num_multiplo',index: 'num_multiplo',width: 75,editable: false,sortable: false,resizable: true}, 
                {name: 'barras3',index: 'barras3',width: 65,editable: false,sortable: false,resizable: true}, 
                {name: 'cajas_palet',index: 'cajas_palet',width: 95,editable: false,sortable: false,resizable: true}, 
                {name: 'grupoa',index: 'grupoa',width: 80,editable: false,sortable: false,resizable: true}, 
                {name: 'clasificaciona',index: 'clasificaciona',width: 120,editable: false,sortable: false,resizable: true}, 
                {name: 'tipoa',index: 'tipoa',width: 100,editable: false,sortable: false,resizable: true}, 
                {name: 'myac',index: '',width: 75,fixed: true,sortable: false,resize: false,formatter: imageFormat,frozen: true},
            ],
            rowNum: 10,
            rowList: [10, 20, 30],
            pager: pager_selector,
            sortname: 'id',
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

        function imageFormat(cellvalue, options, rowObject) 
        {
            var serie = rowObject[1];
            var correl = rowObject[4];
            var url = "x/?serie=" + serie + "&correl=" + correl;
            var url2 = "v/?serie=" + serie + "&correl=" + correl;
            var id = rowObject[0];
            var html = '';
            html += '<a href="#" onclick="recovery(\'' + id + '\')"><i class="fa fa-check" alt="Borrar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            return html;
        }

        function aceSwitch(cellvalue, options, cell) 
        {
            setTimeout(function() {
                $(cell).find('input[type=checkbox]')
                    .addClass('ace ace-switch ace-switch-5')
                    .after('<span class="lbl"></span>');
            }, 0);
        }
            //enable datepicker
        function pickDate(cellvalue, options, cell) 
        {
            setTimeout(function() {
                $(cell).find('input[type=text]')
                    .datepicker({
                        format: 'yyyy-mm-dd',
                        autoclose: true
                    });
            }, 0);
        }

        function beforeDeleteCallback(e) 
        {
            var form = $(e[0]);
            if (form.data('styled')) return false;
            form.closest('.ui-jqdialog').find('.ui-jqdialog-titlebar').wrapInner('<div class="widget-header" />')
            style_delete_form(form);
            form.data('styled', true);
        }

        function reloadPage() 
        {
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

        $(document).one('ajaxloadstart.page', function(e) {
            $(grid_selector).jqGrid('GridUnload');
            $('.ui-jqdialog').remove();
        });
    });
</script>
<script>
    function recovery(_codigo) 
    {
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                id: _codigo,
                action: "recovery"
            },
            beforeSend: function(x) {
                if (x && x.overrideMimeType) {
                    x.overrideMimeType("application/json;charset=UTF-8");
                }
            },
            url: '/api/articulos/update/index.php',
            success: function(data) {
                console.log("inactivos = ", data);
                //ReloadGrid1();
                if (data.success == true) {
                    //$('#codigo').prop('disabled', true);
                    ReloadGrid();
                    ReloadGrid1();
                }
            }, error: function(data) {
                console.log("ERROR inactivos = ", data);
            }
        });
    }

    $(document).ready(function() {
        $("div.ui-jqgrid-bdiv").css("max-height", $("#list").height() - 80);
        $(function() {
            $('.chosen-select').chosen();
            $('.chosen-select-deselect').chosen({
                allow_single_deselect: true
            });
        });
    });
</script>
<script type="text/javascript">
    <?php if(isSCTP()): ?>
    function obtenerProductosSCTP() 
    {
        $("#modal_sctp .fa-spinner").show();
        $("#modal_sctp .success").hide();
        $("#modal_sctp #button_modal_sctp").attr('disabled', 'disabled');
        $("#modal_sctp").modal('show');
        $.ajax({
            url: '/api/synchronize/sctp.php',
            method: 'POST',
            dataType: 'json',
            data: {
                action: 'productosSCTP'
            }
        }).done(function(data) {
            if (data.success) 
            {
                ReloadGrid();
                $("#modal_sctp .fa-spinner").hide();
                $("#modal_sctp .success").show();
                $("#modal_sctp #button_modal_sctp").removeAttr('disabled');
            } 
            else 
            {
                $("#modal_sctp").modal('hide');
                swal("Error", data.error, "error");
            }
        });
    }
    <?php endif; ?>
    <?php if(isLaCentral()): ?>

    function obtenerProductosLaCentral() 
    {
        $("#modal_lacentral .fa-spinner").show();
        $("#modal_lacentral .success").hide();
        $("#modal_lacentral #button_modal_lacentral").attr('disabled', 'disabled');
        $("#modal_lacentral").modal('show');
        $.ajax({
            url: '/api/synchronize/lacentral.php',
            method: 'POST',
            dataType: 'json',
            data: {
                action: 'productosLaCentral'
            }
        }).done(function(data) {
            if (data.success) 
            {
                ReloadGrid();
                $("#modal_lacentral .fa-spinner").hide();
                $("#modal_lacentral .success").show();
                $("#modal_lacentral #button_modal_lacentral").removeAttr('disabled');

            } 
            else 
            {
                $("#modal_lacentral").modal('hide');
                swal("Error", data.error, "error");
            }
        });
    }
    <?php endif; ?>
      
    $(document).ready(function(e){
        //file type validation
        $(".file_img").change(function() {
            var file = this.files[0];
            var imagefile = file.type;
            var match= ["image/jpeg","image/png","image/jpg"];
            if(!((imagefile==match[0]) || (imagefile==match[1]) || (imagefile==match[2])))
            {
                alert('Por favor seleccione una imagen del formato (JPEG/JPG/PNG).');
                $(this).val('');
                return false;
            }
            else
            {
                var formData = new FormData();
                formData.append('action', 'subirImagen');
                formData.append('file', $('.file_img')[0].files[0]);
                $.ajax({
                    type: 'POST',
                    url: '/api/administradorpedidos/lista/index.php',
                    data: new FormData(),
                    dataType: "json",
                    contentType: false,
                    cache: false,
                    processData:false,
                    success: function(msg){
                        console.log(msg,"#foto_to_up_"+ msg.numeroImagen);
                        $("#foto_to_up_"+ msg.numeroImagen).val(msg.nameFile);
                        $("#foto"+ msg.numeroImagen).attr("src","../to_img.php?img=embarques/"+msg.nameFile);
                    }
                });
            }
        });
    });

    function embarqueFoto(folio)
    {
        console.log(folio);
        $(".idPedido").val(folio);
        console.log("Fotos ajax");
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                id_pedido: folio,
                action: "loadFotos"
            },
            url: '/api/administradorpedidos/update/index.php',
        }).done(
        function(data)
        {
            console.log("mostrar Fotos");
            $modal00 = $("#modal_fotos");
            $modal00.modal('show');
            console.log(data);
            window.resurned_data = data;
            if(data.data[0].foto1!=""){$("#foto1").attr("src","../to_img.php?img=embarques/"+data.data[0].foto1);}
            if(data.data[0].foto2!=""){$("#foto2").attr("src","../to_img.php?img=embarques/"+data.data[0].foto2);}
            if(data.data[0].foto3!=""){$("#foto3").attr("src","../to_img.php?img=embarques/"+data.data[0].foto3);}
            if(data.data[0].foto4!=""){$("#foto4").attr("src","../to_img.php?img=embarques/"+data.data[0].foto4);}
        });
    }
</script>
<style>
    <?php /* if($edit[0]['Activo']==0) {?>
        .fa-edit {
            display: none;
        }
    <?php } ?>
    <?php if($borrar[0]['Activo']==0) { ?>
        .fa-eraser {
            display: none;
        }
    <?php } */ ?>
    </style>