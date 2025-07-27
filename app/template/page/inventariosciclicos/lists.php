<?php
$almacenes = new \AlmacenP\AlmacenP();
$model_almacen = $almacenes->getAll();

$listaProto = new \Protocolos\Protocolos();
$listaProvee = new \Proveedores\Proveedores();
$listaAlma = new \Almacen\Almacen();
$listaArtic = new \Articulos\Articulos();


$mod=60;
$var1=183;
$var2=184;
$var3=185;
$var4=186;

$vere = \db()->prepare("select * from t_profiles as a where id_menu='".$mod."' and id_submenu='".$var1."' and id_role='".$_SESSION["perfil_usuario"]."'");
$vere->execute();
$ver = $vere->fetchAll(PDO::FETCH_ASSOC);


$agre = \db()->prepare("select * from t_profiles as a where id_menu='".$mod."' and id_submenu='".$var2."' and id_role='".$_SESSION["perfil_usuario"]."'");
$agre->execute();
$ag = $agre->fetchAll(PDO::FETCH_ASSOC);


$edita = \db()->prepare("select * from t_profiles as a where id_menu='".$mod."' and id_submenu='".$var3."' and id_role='".$_SESSION["perfil_usuario"]."'");
$edita->execute();
$edit = $edita->fetchAll(PDO::FETCH_ASSOC);


$borra = \db()->prepare("select * from t_profiles as a where id_menu='".$mod."' and id_submenu='".$var4."' and id_role='".$_SESSION["perfil_usuario"]."'");
$borra->execute();
$borrar = $borra->fetchAll(PDO::FETCH_ASSOC);


?>
<style>
@media screen and (max-width: 768px)
{
/*
    .modal-dialog {
        width: 400px;
        margin: 30px auto;
    }
*/
    .art-content {
        /* min-width: 880px; */
        max-width: 880px;
        width: 100%;
        overflow-x: scroll;
    }


    #gbox_grid-listado-articulos
    {
        width: 400px !important;
        overflow-x: scroll;
    }
}
</style>

    <link href="/css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">
    <link href="/css/plugins/ladda/select2.css" rel="stylesheet" />
    <link href="/css/plugins/sweetalert/sweetalert.css" rel="stylesheet">
    <link href="/css/plugins/datapicker/datepicker3.css" rel="stylesheet">
    <link href="/css/plugins/awesome-bootstrap-checkbox/awesome-bootstrap-checkbox.css" rel="stylesheet">


    <!-- Mainly scripts -->
    <!--<script src="/js/dropdownLists.js"></script>-->
    <script src="/js/jquery-2.1.1.js"></script>
    <script src="/js/bootstrap.min.js"></script>
    <script src="/js/plugins/metisMenu/jquery.metisMenu.js"></script>
    <script src="/js/plugins/slimscroll/jquery.slimscroll.min.js"></script>
    <script src="/js/moment.js"></script>
    <script src="/js/moment-with-locales.js"></script>


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


    <script src="/js/plugins/chosen/chosen.jquery.js"></script>

    <!-- Select -->
    <script src="/js/select2.js"></script>
    <!-- Data picker -->
    <script src="/js/plugins/datapicker/bootstrap-datepicker.js"></script>
    <script src="/js/plugins/clockpicker/clockpicker.js"></script>
    <!-- iCheck -->
    <script src="/js/plugins/iCheck/icheck.min.js"></script>
    <script src="/js/plugins/sweetalert/sweetalert.min.js"></script>


    <div class="wrapper wrapper-content  animated  hidden" id="list">

        <h3>Inventarios Ciclicos</h3>

        <div class="row">
            <div class="col-md-12">
                <div class="ibox ">
                    <div class="ibox-title">
                        <div class="row">

                            <div class="col-md-4">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="txtCriterio" id="txtCriterio" placeholder="Buscar...">
                                    <div class="input-group-btn">
                                        <a href="#" onclick="ReloadGrid2()">
                                            <button type="submit" class="btn btn-primary" id="buscarA">
                                            <i class="fa fa-search"></i> Buscar
                                        </button>
                                        </a>
                                    </div>
                                </div>
                                <!--   <input class="form-control" type="text" name="daterange" value="<?php echo date("d/m/Y"); ?> - <?php echo date("d/m/Y"); ?>" />-->
                            </div>

                            <div class="col-md-8">
                                <div class="pull-right">
                                    <?php if($ag[0]['Activo']==1){?>
                                    <a href="#" onclick="agregar()"><button class="btn btn-primary" type="button"><i class="fa fa-plus"></i>&nbsp;&nbsp;Nuevo</button></a>
                                    <?php }?>

                                    <a href="#" onclick="Reporte()"><button class="btn btn-primary" type="button"><i class="fa fa-plus"></i>&nbsp;&nbsp;Reporte</button></a>
                                    <a href="#" onclick="ReporteConcentrado()"><button class="btn btn-primary" type="button"><i class="fa fa-plus"></i>&nbsp;&nbsp;Reporte Concentrado</button></a>
                                </div>
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

    <div class="modal fade" id="coModal" role="dialog">
        <div class="vertical-alignment-helper">
            <div class="modal-dialog vertical-align-center modal-lg" style="width: 1200px!important;">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4>Administración de Inventario Ciclico</h4>
                    </div>
                    <div class="modal-body">
                        <div class="ibox-content">
                            <div class="jqGrid_wrapper" id="detalle_wrapper">
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

    <div class="modal fade" id="asignarUsuario" role="dialog">
        <div class="vertical-alignment-helper">
            <div class="modal-dialog vertical-align-center modal-sm">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4>Asignar Usuario a Inventario</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Folio Nº <span id="folionumero">0</span></label>
                        </div>
                        <div class="form-group">
                            <label>Usuarios Disponibles: </label>
                            <select id="usuario" class="form-control chosen-select">
                        </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                        <button type="button" id="guardarUsuario" class="btn btn-primary">Guardar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="modal-articulos" role="dialog">
        <div class="vertical-alignment-helper">
            <div class="modal-dialog vertical-align-center modal-md">
                <!-- Modal content-->
                <div class="modal-content art-content" style="width:80%">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4>Productos en el Sistema</h4>
                    </div>
                    <div class="modal-body">
                        <div class="ibox" style="margin:0">
                            <div class="row">
                                <div class="col-md-12">
                                    <label for="email">&nbsp;</label>
                                    <div class="input-group" style="display: block !important;">
                                        <input type="text" class="form-control" id="txt-buscar-producto" placeholder="Ingrese la clave o descripción del producto a buscar...">
                                        <div class="input-group-btn">
                                            <button onclick="ReloadGridArticulos()" type="submit" class="btn btn-primary" id="buscarA">
                                            <i class="fa fa-search"></i>Buscar
                                        </button>
                                        </div>
                                    </div>
                                    <?php 
                                    /*
                                    ?>
                                    <!--   <input class="form-control" type="text" name="daterange" value="<?php echo date("d/m/Y"); ?> - <?php echo date("d/m/Y"); ?>" />-->
                                    <?php 
                                    */
                                    ?>
                                </div>
                            </div>


                            <div class="ibox-content" style="padding-bottom: 0px; width:100%;">
                                <div class="jqGrid_wrapper">
                                    <table id="grid-listado-articulos"></table>
                                    <div id="grid-pagera"></div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                        <button type="button" class="btn btn-primary hide" onclick="agregarArti()">Seleccionar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="wrapper wrapper-content  animated fadeInRight" id="DetalleInventario">
        <h3 class="modal-title">Programación de Inventario Cíclico</h3>
        <div class="row">
            <br>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="email">Almacén*</label>
                    <select name="almacen" id="almacen" onchange="almac()" class="chosen-select form-control">
                    <option value="">Seleccione Almacén</option>
                    <?php foreach( $model_almacen AS $almacen ): ?>
                    <?php if($almacen->Activo == 1):?>
                    <option value="<?php echo $almacen->clave ; ?>" alma="<?=$almacen->clave?>"><?php echo"($almacen->clave)". $almacen->nombre; ?></option>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </select>
                </div>
            </div>
            <div class="col-md-3" style="display:none;">
                <div class="form-group">
                    <label for="email">Zona de Almacenaje</label>
                    <select name="zona" id="zona" class="chosen-select form-control">

                </select>
                </div>
            </div>

            <div class="col-md-3" style="display:none;">
                <div class="form-group">
                    <label for="email">Rack</label>
                    <select name="rack" id="rack" class="chosen-select form-control">
                </select>
                </div>
            </div>

            <div class="col-md-3" style="display:none;">
                <div class="form-group">
                    <label for="email">Ubicaciones</label>
                    <select name="ubicaciones" id="ubicaciones" class="chosen-select form-control">
                </select>
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group">
                    <label for="email"></label>
                    <button type="submit" class="btn btn-primary" onclick="almacen()" id="alma" style="margin-top: 25px;">
                <i class="fa fa-search"></i> Articulos
                </button>
                </div>
            </div>


            <div class="tab">
                <div class="ibox-content dragdrop">
                    <div class="form-group">
                        <div class="col-md-12">
                            <h4>Productos en el inventario*</h4>

                                <label for="articulos_produccion" style="font-weight: 600;font-size: 14px; -moz-user-select: none; -webkit-user-select: none;-ms-user-select: none; user-select: none;">
                                <input type="checkbox" name="articulos_produccion" id="articulos_produccion" value="0"> Incluir Artículos en Producción</label>


                                <label for="articulos_cuarentena" style="margin:0 20px;font-weight: 600;font-size: 14px; -moz-user-select: none; -webkit-user-select: none;-ms-user-select: none; user-select: none;">
                                <input type="checkbox" name="articulos_cuarentena" id="articulos_cuarentena" value="0"> Incluir Artículos en Cuarentena</label>


                                <label for="articulos_obsoletos" style="font-weight: 600;font-size: 14px; -moz-user-select: none; -webkit-user-select: none;-ms-user-select: none; user-select: none;">
                                <input type="checkbox" name="articulos_obsoletos" id="articulos_obsoletos" value="0"> Incluir Artículos Obsoletos</label>



                            <div class="jqGrid_wrapper">
                                <table id="grid-general"></table>
                                <div id="grid-pag-general"></div>
                            </div>
                        </div>

                    </div>
                </div>


            </div>
        </div>
        <br>
        <div class="center" style="display:none;">
            <label>Planeación de fechas del inventario</label>
        </div>

        <form id="myform" style="display:none;">

            <div class="col-md-12">

                <div class="tabs-container">

                    <div class="tabs-left">
                        <ul class="nav nav-tabs">
                            <li class="active"><a data-toggle="tab" href="#tab-1" aria-expanded="true">Fecha Planeada</a></li>
                            <li class="" style="display:none;"><a data-toggle="tab" href="#tab-2" aria-expanded="false">Diario</a></li>
                            <li class="" style="display:none;"><a data-toggle="tab" href="#tab-3" aria-expanded="false">Semanal</a></li>
                            <li class="" style="display:none;"><a data-toggle="tab" href="#tab-4" aria-expanded="false">Mensual</a></li>
                        </ul>
                        <div class="tab-content ">
                            <div id="tab-1" class="tab-pane active">
                                <div class="panel-body">

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Fecha</label>
                                            <div class="input-group date" id="data_1">
                                                <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input id="FechaUnSoloDia" name="FechaUnSoloDia" type="text" class="form-control" value="<?php echo date(" d/m/Y "); ?>">
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <div id="tab-2" class="tab-pane">
                                <div class="panel-body">

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Fecha de Inicio</label>
                                            <div class="input-group date" id="data_2">
                                                <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input id="FechaIniDiario" name="FechaIniDiario" type="text" class="form-control" value="<?php echo date(" d/m/Y "); ?>">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Fecha de Fin</label>
                                            <div class="input-group date" id="data_3">
                                                <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input id="FechaFinDiario" name="FechaFinDiario" type="text" class="form-control" value="<?php echo date(" d/m/Y "); ?>">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-sm-12">
                                        <div class="col-sm-2">
                                            <div class="radio">
                                                <input type="radio" id="RBDias" name="RBDias" value="S" checked="checked" onclick="radioB('D')">
                                                <label for="RBDias">
                                                Cada
                                            </label>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <div class="col-sm-3">
                                                <input id="TNDia" name="TNDia" style="margin-left: -15px" type="text" class="form-control" data-mask="$ 999,999,999.99" placeholder="">
                                            </div>
                                            <label style="margin-top: 8px; margin-left: -15px" class="col-sm-1 control-label">Días</label>
                                        </div>
                                    </div>

                                    <div class="col-sm-12">
                                        <div class="col-sm-12">
                                            <div class="radio">
                                                <input type="radio" name="RBLaborales" id="RBLaborales" value="S" onclick="radioB('S')">
                                                <label for="RBLaborales">
                                                Todos los dias Laborales
                                            </label>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <div id="tab-3" class="tab-pane">
                                <div class="panel-body">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Fecha de Inicio</label>
                                            <div class="input-group date" id="data_4">
                                                <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input id="FechaIniSemanal" name="FechaIniSemanal" type="text" class="form-control" value="<?php echo date(" d/m/Y "); ?>">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Fecha de Fin</label>
                                            <div class="input-group date" id="data_5">
                                                <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input id="FechaFinSemanal" name="FechaFinSemanal" type="text" class="form-control" value="<?php echo date(" d/m/Y "); ?>">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-sm-12">
                                        <div class="col-sm-3">
                                            Repetir cada
                                        </div>

                                        <div class="form-group">
                                            <div class="col-sm-3">
                                                <input id="TIntervalo" name="TIntervalo" style="margin-left: -15px" type="text" class="form-control" data-mask="$ 999,999,999.99" placeholder="">
                                            </div>
                                            <label style="margin-top: 8px; margin-left: -15px" class="col-sm-6 control-label">Semanas el:</label>
                                        </div>
                                    </div>

                                    <div class="col-sm-12">
                                        <div class="col-sm-2">
                                            <div class="checkbox">
                                                <input id="CHLunes" name="CHLunes" type="checkbox">
                                                <label for="CHLunes">
                                                Lunes
                                            </label>
                                            </div>
                                            <div class="checkbox">
                                                <input id="CHViernes" name="CHViernes" type="checkbox">
                                                <label for="CHViernes">
                                                Viernes
                                            </label>
                                            </div>
                                        </div>
                                        <div class="col-sm-2">
                                            <div class="checkbox">
                                                <input id="CHMartes" name="CHMartes" type="checkbox">
                                                <label for="CHMartes">
                                                Martes
                                            </label>
                                            </div>
                                            <div class="checkbox">
                                                <input id="CHSabado" name="CHSabado" type="checkbox">
                                                <label for="CHSabado">
                                                Sabado
                                            </label>
                                            </div>
                                        </div>
                                        <div class="col-sm-2">
                                            <div class="checkbox">
                                                <input id="CHMiercoles" name="CHMiercoles" type="checkbox">
                                                <label for="CHMiercoles">
                                                Miercoles
                                            </label>
                                            </div>
                                            <div class="checkbox">
                                                <input id="CHDomingo" name="CHDomingo" type="checkbox">
                                                <label for="CHDomingo">
                                                Domingo
                                            </label>
                                            </div>
                                        </div>
                                        <div class="col-sm-2">
                                            <div class="checkbox">
                                                <input id="CHJueves" name="CHJueves" type="checkbox">
                                                <label for="CHJueves">
                                                Jueves
                                            </label>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <div id="tab-4" class="tab-pane">
                                <div class="panel-body">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Fecha de Inicio</label>
                                            <div class="input-group date" id="data_6">
                                                <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input id="FechaIniMensual" name="FechaIniMensual" type="text" class="form-control" value="<?php echo date(" d/m/Y "); ?>">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Fecha de Fin</label>
                                            <div class="input-group date" id="data_7">
                                                <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input id="FechaFinMensual" name="FechaFinMensual" type="text" class="form-control" value="<?php echo date(" d/m/Y "); ?>">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-sm-12">
                                        <div class="col-sm-2">
                                            <div class="radio">
                                                <input type="radio" name="RBFecha" id="RBFecha" value="S" onclick="radioB('DD')">
                                                <label for="radioMensual">
                                                El día
                                            </label>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <div class="col-sm-3">
                                                <input id="TNDiaMensual" style="margin-left: -15px" type="text" class="form-control" placeholder="">
                                            </div>
                                            <div class="col-sm-2">
                                                <label style="margin-top: 13px; margin-left: -35px;">De cada</label>
                                            </div>
                                            <div class="col-sm-3">
                                                <input id="TNMesMensual" style="margin-left: -65px;" type="text" class="form-control" placeholder="">
                                            </div>
                                            <div class="col-sm-2">
                                                <label style="margin-top: 13px; margin-left: -85px;">Mes</label>
                                            </div>
                                        </div>

                                    </div>

                                    <div class="col-sm-12">
                                        <div class="col-sm-1">
                                            <div class="radio">
                                                <input type="radio" name="radioMensual" id="radioElArtMensual" value="S" onclick="radioB('MM')">
                                                <label for="radioMensual">
                                                El
                                            </label>
                                            </div>
                                        </div>
                                        <div class="col-sm-3">
                                            <div class="form-group">
                                                <select class="form-control" name="ComboEscalar" id="ComboEscalar" style="width:100%;">
                                                <option value="1">Primer</option>
                                                <option value="2">Segundo</option>
                                                <option value="3">Tercer</option>
                                                <option value="4">Cuarto</option>
                                                <option value="5">Ultimo</option>
                                            </select>
                                            </div>
                                        </div>
                                        <div class="col-sm-3">
                                            <div class="form-group">
                                                <select class="form-control" name="ComboDia" id="ComboDia" style="width:100%;">
                                                <option value="1">Lunes</option>
                                                <option value="2">Martes</option>
                                                <option value="3">Miércoles</option>
                                                <option value="4">Jueves</option>
                                                <option value="5">Viernes</option>
                                                <option value="6">Sábado</option>
                                                <option value="7">Domingo</option>
                                                <option value="8">Día</option>
                                                <option value="9">Dia de la semana</option>
                                                <option value="10">Día del fin de semana</option>
                                            </select>
                                            </div>
                                        </div>
                                        <div class="col-sm-2">
                                            <label for="radio1" style="margin-top: 13px;">De cada</label>
                                        </div>
                                        <div class="col-sm-2">
                                            <input id="TMes2" style="margin-left: -15px" type="text" class="form-control" data-mask="$ 999,999,999.99" placeholder="">
                                        </div>
                                        <div class="col-sm-1">
                                            <label for="radio1" style="margin-top: 13px; margin-left: -35px;">Meses</label>
                                        </div>
                                    </div>

                                </div>

                            </div>
                        </div>

                    </div>

                </div>
            </div>

        </form>

        <input type="hidden" id="hiddenAction">
        <input type="hidden" id="hiddenRuta">

        <input type="hidden" id="_fecha" value="<?php echo date(" Y-m-d "); ?>">
        <input type="hidden" id="_fechaFin" value="<?php echo date(" Y-m-d "); ?>">

        <div class="col-md-12 animated fadeInRight text-right">
            <br>
            <!--<button type="button" class="btn btn-white" id="btnCancel" onclick="cancelar()">Cerrar</button>-->
            <button type="button" class="btn btn-primary ladda-button permiso_registrar" data-style="contract" id="btnSave">Planificar</button>
        </div>

    </div>

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
                        document.getElementById('almacen').value = data.codigo.clave;
                        setTimeout(function() {
                            almac();
                        }, 1000);
                    }
                },
                error: function(res) {
                    window.console.log(res);
                }
            });
        }

        function almac() {
            $('#zona')
                .find('option')
                .remove()
                .end();
            var clave = $('#almacen').val()

            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    clave: clave,
                    action: "traerZonasDeAlmacenP"
                },
                beforeSend: function(x) {
                    if (x && x.overrideMimeType) {
                        x.overrideMimeType("application/json;charset=UTF-8");
                    }
                },
                url: '/api/almacenp/update/index.php',
                success: function(data) {
                    if (data.success == true) {
                        var options = $("#zona");
                        options.empty();
                        options.append(new Option("Seleccione", ""));
                        for (var i = 0; i < data.zonas.length; i++) {
                            options.append(new Option(data.zonas[i].nombre_zona, data.zonas[i].clave_zona));
                        }
                        $('.chosen-select').trigger("chosen:updated");
                    }
                }
            });
        }

        function seleccionarArticulo(cve_articulo, des_articulo, existencia, cve_ubicacion){
         
            var l = $("#grid-general").length;
            //Le asigno el id porque la línea anterior no me lo trae

            item = [{
                cve_articulo: cve_articulo,
                des_articulo: des_articulo,
                suma: existencia,
                cve_ubicacion: cve_ubicacion,
                acciones: '<a href="#" onclick="borr(' + l + ')" class="borr"><i class="fa fa-eraser" alt="Borrar"></i></a>'
            }];
            $("#grid-general").jqGrid('clearGridData');
            //Inserto en el grid destino
            $("#grid-general").jqGrid('addRowData', l, item);
            $("#modal-articulos").modal('hide');

            $(window).triggerHandler('resize.jqGrid');
    
        }



        $(function($) {
            

            $("#grid-listado-articulos").jqGrid('setGridParam', {
                datatype: 'json'
            }).trigger('reloadGrid');

            var grid_selector = "#grid-listado-articulos";
            var pager_selector = "#grid-pagera";

            //resize to fit page size

    

            $(grid_selector).jqGrid({
                url: '/api/articulos/lista/lista.php',
                datatype: "json",
                postData: {
                    almacen: $("#almacen").val(),
                    producto: $("#txt-buscar-producto").val(),
                    action: "buscarArticulosEnAlmacen"
                },
                shrinkToFit: false,
                height: 300,
                mtype: 'POST',
                colNames: ['Acciones', 'Clave', 'Descripción', 'Existencia', '% Stock', 'Ubicación' ],
                colModel: [{
                    name: 'acciones',
                    index: 'acciones',
                    width: 70,
                    editable: false,                  
                    sortable: false,
                    resizable: false,
                    formatter:imageFormat,
                    align:"center"
                }, {
                    name: 'cve_articulo',
                    index: 'cve_articulo',
                    width: 120,
                    editable: false,
                    sortable: false,                    
                    resizable: false
                }, {
                    name: 'des_articulo',
                    index: 'des_articulo',
                    width: 300,
                    editable: false,
                    sortable: false,
                    resizable: false
                }, {
                    name: 'existencia',
                    index: 'existencia',
                    width: 120,
                    editable: false,
                    sortable: false,
                    align : 'right',
                    resizable: false
                }, {
                    name: 'abc',
                    index: 'abc',
                    width: 160,
                    align : 'right',
                    editable: false,
                    sortable: false,
                    resizable: false
                }, {
                    name: 'cve_ubicacion',
                    index: 'cve_ubicacion',
                    width: 180,
                    editable: false,
                    hidden:true,                   
                    sortable: false,
                    resizable: false
                }],
                loadComplete: function(data){
                console.log("data = ", data);
                },
                loadError: function(data){
                console.log("data ERROR = ", data);
                },                rowNum: 30,
                rowList: [30, 40, 50],
                pager: pager_selector,
                viewrecords: true,
                //multiselect: true
            });

            $(window).triggerHandler('resize.jqGrid');

            function imageFormat( cellvalue, options, rowObject ){
                var articulo = "'"+rowObject[1]+"',"+   // cve_articulo
                                "'"+rowObject[2]+"',"+   // des_articulo
                                "'"+rowObject[3]+"',"+   // existencia
                                "'"+rowObject[5]+"'"  // cve_ubicacion
                                ;
   
                return '<a href="#" onclick="seleccionarArticulo('+articulo+')"><i class="fa fa-check" title="Ver Detalles"></i></a>';
            }


        });

        $("#modal-articulos").on('shown.bs.modal', function (e) {
            
                $("#grid-listado-articulos").jqGrid('setGridWidth', $("#modal-articulos .modal-body").width() - 60);
            
        });

        //////////////////////////////////////////////////////////Aqui se contruye el Grid//////////////////////////////////////////////////////////
        $(function($) {

            var pager_general = "#grid-pag-general";




            $('#grid-general').jqGrid({
                url: '/api/almacen/update/index.php',
                datatype: "json",
                postData: {
                    almacen: $("#almacen").val()
                },
                shrinkToFit: false,
                height: 400,
                mtype: 'POST',
                colNames: ['Clave', 'Descripción', 'Stock Físico', 'Ubicación', 'Acciones'],
                colModel: [{
                    name: 'cve_articulo',
                    index: 'cve_articulo',
                    align: 'left',
                    width: 100,
                    editable: false,
                    sortable: false,
                    resize: true
                }, {
                    name: 'des_articulo',
                    index: 'des_articulo',
                    align: 'left',
                    width: 350,
                    editable: false,
                    sortable: false,
                    resize: true
                }, {
                    name: 'suma',
                    index: 'Suma',
                    width: 150,
                    editable: true,
                    edittype: 'text',
                    align: 'right',
                    sortable: false,
                    resize: true
                }, {
                    name: 'cve_ubicacion',
                    index: 'cve_ubicacion',
                    width: 150,
                    hidden: true,
                    editable: false,
                    sortable: false,
                    resize: true
                }, {
                    name: 'acciones',
                    index: '',
                    width: 100,
                    editable: false,
                    fixed: true,
                    sortable: false,
                    align: 'center',
                    resize: true
                }],
                loadComplete: almacenPrede(),
                rowNum: 30,
                rowList: [30, 40, 50],
                pager: pager_general,
                viewrecords: true,
                subGrid: true,
                subGridRowExpanded: function(subgrid_id, row_id) {
                    // we pass two parameters
                    // subgrid_id is a id of the div tag created whitin a table data
                    // the id of this elemenet is a combination of the "sg_" + id of the row
                    // the row_id is the id of the row
                    // If we wan to pass additinal parameters to the url we can use
                    // a method getRowData(row_id) - which returns associative array in type name-value
                    // here we can easy construct the flowing
                    var articulos_produccion = 0;
                    if($("#articulos_produccion").is(':checked'))
                        articulos_produccion = 1;

                    var articulos_cuarentena = 0;
                    if($("#articulos_cuarentena").is(':checked'))
                        articulos_cuarentena = 1;

                    var articulos_obsoletos = 0;
                    if($("#articulos_obsoletos").is(':checked'))
                        articulos_obsoletos = 1;

                    var subgrid_table_id, pager_id;
                    subgrid_table_id = subgrid_id + "_t";
                    pager_id = "p_" + subgrid_table_id;

                    var articulo = $('#' + row_id + ' > td:nth-child(2)').text();
                    var almacen = $('#almacen').val();
                    $("#" + subgrid_id).html("<table id='" + subgrid_table_id + "' class='scroll'></table><div id='" + pager_id + "' class='scroll'></div>");

                    console.log("subgrid_table_id = ", subgrid_table_id, "articulo = ", articulo, "almacen = ", almacen, "articulos_produccion = ", articulos_produccion, "articulos_cuarentena = ", articulos_cuarentena, "articulos_obsoletos = ", articulos_obsoletos);
                    $("#" + subgrid_table_id).jqGrid({
                        url: "/api/v2/inventario/existencias-por-ubicaciones",
                        postData: {
                            articulo : articulo,
                            articulos_produccion: articulos_produccion,
                            articulos_cuarentena: articulos_cuarentena,
                            articulos_obsoletos: articulos_obsoletos,
                            almacen : almacen
                        },
                        datatype: "json",

                        colNames: ['id', 'Ubicación', 'cve_articulo', 'Lote|Serie', 'Caducidad|F. Entrada', 'Cantidad'],
                        colModel: [{
                            name: "cve_ubicacion",
                            index: "cve_ubicacion",                            
                            hidden: true,
                            sortable: false,
                        }, {
                            name: "ubicacion",
                            index: "ubicacion",
                            align : 'center',
                            width: 150,
                            sortable: false,
                        }, {
                            name: "cve_articulo",
                            index: "cve_articulo",
                            width: 200,
                            hidden: true,
                            sortable: false,
                        }, {
                            name: "lote",
                            index: "lote",
                            width: 200,
                            sortable: false,
                        }, {
                            name: "caducidad",
                            index: "caducidad",
                            width: 200,
                            align : 'center',
                            sortable: false,
                        }, {
                            name: "cantidad",
                            index: "cantidad",
                            width: 142,
                            align : 'right',
                            sortable: false
                        }],
                        rowNum: 20,
                        pager: pager_id,
                        sortname: 'num',
                        sortorder: "asc",
                        height: '100%',
                        loadComplete: function(data) {
                            console.log(data);
                            $('.cbox').trigger('click');
                        }
                    });
                    jQuery("#" + subgrid_table_id).jqGrid('navGrid', "#" + pager_id, {
                        edit: false,
                        add: false,
                        del: false
                    });
                    $('.cbox').trigger('click');
                },
                subGridRowColapsed: function(subgrid_id, row_id) {
                    // this function is called before removing the data
                    //var subgrid_table_id;
                    //subgrid_table_id = subgrid_id+"_t";
                    //jQuery("#"+subgrid_table_id).remove();
                }
            });
            $('.cbox').trigger('click');


            $(window).on('resize.jqGrid', function () {
                $("#grid-general").jqGrid( 'setGridWidth', $("#DetalleInventario").width() - 50)
            })


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
                url: '/api/inventariosciclicos/lista/index.php',
                datatype: "json",
                shrinkToFit: false,
                height: 400,
                postData: {
                    criterio: $("#txtCriterio").val(),
                    _fecha: $("#_fecha").val(),
                    _fechaFin: $("#_fechaFin").val()
                },
                mtype: 'POST',
                colNames: ['Consecutivo', 'Almacen', 'Usuario', 'Fecha Inicio', 'Fecha Fin', 'Diferencia', 'Status', "Detalle"],
                colModel: [
                    //{name:'id',index:'id', width:60, sorttype:"int", editable: false},

                    {
                        name: 'ORDEN',
                        index: 'ORDEN',
                        width: 120,
                        editable: false,
                        sortable: false,
                        resizable: false
                    }, {
                        name: 'nombre',
                        index: 'nombre',
                        width: 150,
                        editable: false,
                        sortable: false,
                        resizable: false
                    }, {
                        name: 'usuario',
                        index: 'usuario',
                        width: 150,
                        editable: false,
                        sortable: false,
                        resizable: false
                    }, {
                        name: 'fechaIni',
                        index: 'fechaIni',
                        width: 135,
                        editable: false,
                        sortable: false,
                        resizable: false
                    }, {
                        name: 'fechaFin',
                        index: 'fechaFin',
                        width: 135,
                        editable: false,
                        sortable: false,
                        resizable: false
                    }, {
                        name: 'diferencia',
                        index: 'diferencia',
                        width: 150,
                        editable: false,
                        sortable: false,
                        resizable: false
                    }, {
                        name: 'ESTADO',
                        index: 'ESTADO',
                        width: 120,
                        editable: false,
                        sortable: false,
                        resizable: false
                    }, {
                        name: 'myac',
                        index: '',
                        width: 80,
                        fixed: true,
                        sortable: false,
                        resize: false,
                        formatter: imageFormat,
                        frozen: true
                    },
                ],
                rowNum: 30,
                rowList: [30, 40, 50],
                pager: pager_selector,
                sortname: 'ORDEN',
                viewrecords: true,
                sortorder: "desc"
            });

            // Setup buttons
            $("#grid-table").jqGrid('navGrid', '#grid-pager', {
                edit: false,
                add: false,
                del: false,
                search: false
            }, {
                height: 300,
                reloadAfterSubmit: true
            });

            $(window).triggerHandler('resize.jqGrid'); //trigger window resize to make the grid get the correct size
            //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

            //////////////////////////////////////////////////////////aqui se construye la funcion de cada item del grid//////////////////////////////////////////////////////////


            function imageFormat(cellvalue, options, rowObject) {
                var serie = rowObject[0];
                var html = '';
                if (rowObject[2].length > 2) {
                    html += '<a href="#" onclick="detallec(\'' + serie + '\')"  title="Ver Detalle"><i class="fa fa-search"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
                }

                if (rowObject[6] === "Abierto") {
                    html += '<a href="#" onclick="asignarUsuario(\'' + serie + '\')"  title="Asignar Usuario y Contar"><i class="fa fa-check"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';

                }
                html += `<a href="#" onclick="generarPDF('` + serie + `')" title="Imprimir Reporte"><i class="fa fa-print"></i></a>`;
                return html;

            }


            function aceSwitch(cellvalue, options, cell) {
                setTimeout(function() {
                    $(cell).find('input[type=checkbox]')
                        .addClass('ace ace-switch ace-switch-5')
                        .after('<span class="lbl"></span>');
                }, 0);
            }
            $('#data_1').datetimepicker({
                locale: 'es',
                format: 'DD-MM-YYYY',   
                useCurrent: false
            });

            $('#data_2').datetimepicker({
                locale: 'es',
                format: 'DD-MM-YYYY',
                useCurrent: false
            });

            $('#data_3').datetimepicker({
                locale: 'es',
                format: 'DD-MM-YYYY',
                useCurrent: false
            });
            $('#data_4').datetimepicker({
                locale: 'es',
                format: 'DD-MM-YYYY',
                useCurrent: false
            });
            $('#data_5').datetimepicker({
                locale: 'es',
                format: 'DD-MM-YYYY',
                useCurrent: false
            });
            $('#data_6').datetimepicker({
                locale: 'es',
                format: 'DD-MM-YYYY',
                useCurrent: false
            });
            $('#data_7').datetimepicker({
                locale: 'es',
                format: 'DD-MM-YYYY',
                useCurrent: false
            });

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


        $("#articulos_produccion, #articulos_cuarentena, #articulos_obsoletos").click(function(){
/*
            if($("#articulos_produccion").is(':checked'))
                $("#grid-general").jqGrid('clearGridData');

            if($("#articulos_cuarentena").is(':checked'))
                $("#grid-general").jqGrid('clearGridData');

            if($("#articulos_obsoletos").is(':checked'))
*/
                $("#grid-general").jqGrid('clearGridData');

        });

        $('#zona').on('change', function() {
            if ($(this).val() != "")
                $("#cargarUbicaciones").prop('disabled', false);
            else
                $("#cargarUbicaciones").prop('disabled', true);
            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    zona: $('#zona').val(),
                    action: "traerRackDeZonas",
                    conProducto: true
                },
                beforeSend: function(x) {
                    if (x && x.overrideMimeType) {
                        x.overrideMimeType("application/json;charset=UTF-8");
                    }
                },
                url: '/api/almacen/update/index.php',
                success: function(data) {
                    if (data.success == true) {
                        var options = $("#rack");
                        options.empty();
                        options.append(new Option("Seleccione", ""));
                        for (var i = 0; i < data.racks.length; i++) {
                            options.append(new Option(data.racks[i].rack, data.racks[i].rack));
                        }
                        $('.chosen-select').trigger("chosen:updated");
                    }
                }
            });
        });



        $('#rack').on('change', function() {
            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    zona: $("#zona").val(),
                    rack: $("#rack").val(),
                    almacen: $("#almacen").val().split("|")[1],
                    conProducto: true,
                    action: "traerUbicacionesDeZonas"
                },
                beforeSend: function(x) {
                    if (x && x.overrideMimeType) {
                        x.overrideMimeType("application/json;charset=UTF-8");
                    }
                },
                url: '/api/almacen/update/index.php',
                success: function(data) {

                    if (data.success == true) {


                        var arr = $.map(data.ubicaciones, function(el) {
                            return el;
                        })
                        arr.pop();


                        var options = $("#ubicaciones");
                        options.empty();
                        options.append(new Option("Seleccione", ""));
                        for (var i = 0; i < data.ubicaciones.length; i++) {
                            options.append(new Option(data.ubicaciones[i].ubicacion, data.ubicaciones[i].id_ubicacion));
                        }
                        $('.chosen-select').trigger("chosen:updated");



                    }
                }
            })
        });



        var MODE = "Un Solo Día";

        function add(from, to) {
            var elements = document.querySelectorAll(`${from} input.drag:checked`),
                li, newli;
            for (e of elements) {
                e.checked = false;
                li = e.parentElement;
                newli = li.cloneNode(true);
                newli.setAttribute("aria-grabbed", "false");
                document.querySelector(`${from}`).removeChild(li);
                document.querySelector(`${to}`).appendChild(newli);
            }
        }

        function remove(to, from) {
            var elements = document.querySelectorAll(`${to} input.drag:checked`),
                li, newli;
            for (e of elements) {
                e.checked = false;
                li = e.parentElement;
                newli = li.cloneNode(true);
                newli.setAttribute("aria-grabbed", "false");
                document.querySelector(`${to}`).removeChild(li);
                document.querySelector(`${from}`).insertBefore(newli, document.querySelector(`${from}`).firstChild);
            }
        }
        $(document).ready(function() {
            $('a[data-toggle="tab"]').bind('click', function(e) {
                MODE = $(this).html();
            });

            $("div.ui-jqgrid-bdiv").css("max-height", $("#list").height() - 20);


        });

        //////////////////////////////////////////////////////////hace un reload del grid llamando al php en la carpera "c" para hacer la consulta//////////////////////////////////////////////////////////
        function ReloadGrid() {
            $('#grid-table').jqGrid('clearGridData')
                .jqGrid('setGridParam', {
                    postData: {
                        criterio: $("#txtCriterio").val(),
                        _fecha: $("#_fecha").val(),
                        _fechaFin: $("#_fechaFin").val()
                    },
                    datatype: 'json',
                    page: 1
                })
                .trigger('reloadGrid', [{
                    current: true
                }]);
        }

        function ReloadGridArticulos() {
            console.log("OK ReloadGridArticulos");
            $('#grid-listado-articulos').jqGrid('clearGridData')
                .jqGrid('setGridParam', {
                    postData: {
                        almacen: $("#almacen").val(),
                        producto: $("#txt-buscar-producto").val(),
                        action: "buscarArticulosEnAlmacen"
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

        function radioB(quienFue) {
            if (quienFue == "S") {
                $("#RBDias")[0].checked = false;
                $("#RBLaborales")[0].checked = true;
            }
            if (quienFue == "D") {
                $("#RBDias")[0].checked = true;
                $("#RBLaborales")[0].checked = false;
            }
            if (quienFue == "DD") {
                $("#radioElArtMensual")[0].checked = false;
                $("#RBFecha")[0].checked = true;
            }
            if (quienFue == "MM") {
                $("#radioElArtMensual")[0].checked = true;
                $("#RBFecha")[0].checked = false;
            }
        }

        $modal0 = null;

        function cancelar() {
            $("#grid-general").jqGrid('clearGridData');
            /*$("#fromU .itemlist").remove();
            $("#toU .itemlist").remove();
            $(':input', '#myform')
                .removeAttr('checked')
                .removeAttr('selected')
                .not(':button, :submit, :reset, :hidden, :radio, :checkbox')
                .val('');
            $('#DetalleInventario').removeAttr('class').attr('class', '');
            $('#DetalleInventario').addClass('animated');
            $('#DetalleInventario').addClass("fadeOutRight");
            $('#DetalleInventario').hide();
            $('#almacen').val("");
            $('#txtCriterio2').val("");
            //$('#FechaUnSoloDia').val(new Date());



            $('#list').show();
            $('#list').removeAttr('class').attr('class', '');
            $('#list').addClass('animated');
            $('#list').addClass("fadeInRight");
            $('#list').addClass("wrapper");
            $('#list').addClass("wrapper-content");
            $('a[href="#tab-1"]').tab('show');*/
        }

        function agregar() {
            /*$modal0 = $("#myModal");
            $modal0.modal('show');*/
            l.ladda('stop');
            //$('#codigo').prop('disabled', false);
            $("#hiddenAction").val("add");
            $("#btnCancel").show();
            $("#ClavRuta").val("");
            $("#DescripRuta").val("");
            $("#StatusRuta").val("");
            $("#txtNomCompa").val("");
            $("#hiddenRuta").val("0");

            $('#list').removeAttr('class').attr('class', '');
            $('#list').addClass('animated');
            $('#list').addClass("fadeOutRight");
            $('#list').hide();

            $('#DetalleInventario').show();
            $('#DetalleInventario').removeAttr('class').attr('class', '');
            $('#DetalleInventario').addClass('animated');
            $('#DetalleInventario').addClass("fadeInRight");

            MODE = "Un Solo Día";
        }

        function buildRequestStringData(form) {
            var select = form.find('select'),
                input = form.find('input'),
                requestString = '{';
            for (var i = 0; i < select.length; i++) {
                requestString += '"' + $(select[i]).attr('name') + '": "' + $(select[i]).val() + '",';
            }
            if (select.length > 0) {
                requestString = requestString.substring(0, requestString.length - 1);
            }
            for (var i = 0; i < input.length; i++) {
                if ($(input[i]).attr('type') !== 'checkbox') {
                    requestString += '"' + $(input[i]).attr('name') + '":"' + $(input[i]).val() + '",';
                } else {
                    if ($(input[i]).attr('checked')) {
                        requestString += '"' + $(input[i]).attr('name') + '":"' + $(input[i]).val() + '",';
                    }
                }
            }
            if (input.length > 0) {
                requestString = requestString.substring(0, requestString.length - 1);
            }
            requestString += '}';
            return requestString;
        }

        var l = $('.ladda-button').ladda();
        l.click(function() {

            $.ajax({
                type: "POST",
                dataType: "json",
                url: '/api/inventariosfisicos/update/index.php',
                data:
                    {
                    action : "VerificarInventariosAbiertos"
                },
                beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }},
                success: function(data) 
                {
/*
                    if(data.abiertos > 0)
                    {
                      swal({
                                title: 'No puede Planificar Inventario',
                                text: 'Tiene Inventarios Pendientes por Cerrar en Administración',
                                type: 'error'
                            });
                        return;
                    }
*/
                    //else 
                    //{
                        //**********************************************************************************************
                        /************************ VALIDAR INPUTS DEL FORM ****************************/
            var articulos = [];
            var ubicaciones = [];
            var ggg = 0;
            var ss = 0;
            var art = '';
            var opc = 0;

            $('#grid-general').each(function() {

                $(this).find('tr').each(function(i) {
                    ss++;
                    if (ss > 1) {
                        $(this).find('td').each(function(i) {
                            // code to be execute 
                         
                            $(this).children('div').children('div').children('.ui-jqgrid-view').children('.ui-jqgrid-bdiv').children('div').children('table').children('tbody').children('tr.jqgrow').each(function(i) {
                                opc = 0;
                                ggg = 0;
                                $(this).find('td').each(function(i) {
                                    ggg++;
                                    if (ggg == 1) {
                                        if ($(this).children('input').prop('checked')) {
                                            opc = 1;
                                        }
                                    }
                                    //if (opc == 1) {
                                        if (ggg == 2) {
                                            ubicaciones.push($(this).text());
                                        }
                                        /*
                                        con esto inserta los valores vacíos en articulosAInventariar[]
                                        if (ggg == 4) {
                                            articulos.push($(this).text());
                                        }
                                        */
                                    //}
                                });

                            });
                        });
                    }
                });

            });

     
            $('[aria-describedby="grid-general_cve_articulo"]').each(function(index, item) {
                articulos.push($(item).text());
            });


            // console.log(articulos);
            var _exists = false;
            if ($("#almacen").val() == "") {
                swal(
                    'Error',
                    'Por favor seleccione un almacen',
                    'error'
                );
                return;
            }

            if (articulos.length == 0) {
                swal(
                    'Error',
                    'Por favor seleccione un producto al menos',
                    'error'
                );
                return;
            }

            var articulosAInventariar = [];

            switch (MODE) {
                case "Un Solo Día":
                    for (i = 0; i < articulos.length; i++) {
                        var _articulos = articulos[i];
                        var _ubicaciones = ubicaciones[i];

                        articulosAInventariar.push({
                            id_plan: '0',
                            fecha: $("#FechaUnSoloDia").val(),
                            articulo: _articulos,
                            MODE: MODE,
                            ubicacion: []
                        });
                    }
                    break;
                case "Diario":
                    var opt;
                    if ($("#RBLaborales").is(":checked")) {
                        opt = "S";
                    }
                    if ($("#RBDias").is(":checked")) {
                        opt = "S";
                    }
                    for (i = 0; i < articulos.length; i++) {
                        var _articulos = articulos[i];
                        var _ubicaciones = ubicaciones[i];
                        articulosAInventariar.push({
                            id_plan: '0',
                            FechaIni: $("#FechaIniDiario").val(),
                            FechaFin: $("#FechaFinDiario").val(),
                            articulo: _articulos,
                            laborales: opt,
                            NDias: $("#TNDia").val(),
                            MODE: MODE,
                            ubicacion: []
                        });
                    }
                    break;
                case "Semanal":
                    for (i = 0; i < articulos.length; i++) {
                        var _articulos = articulos[i];
                        var _ubicaciones = ubicaciones[i];
                        articulosAInventariar.push({
                            id_plan: '0',
                            fechaInicial: $("#FechaIniSemanal").val(),
                            fechaFinal: $("#FechaFinSemanal").val(),
                            articulo: _articulos,
                            intervalo: $("#TIntervalo").val(),
                            lunes: $("#CHLunes").is(":checked"),
                            martes: $("#CHMartes").is(":checked"),
                            miercoles: $("#CHMiercoles").is(":checked"),
                            jueves: $("#CHJueves").is(":checked"),
                            viernes: $("#CHViernes").is(":checked"),
                            sabado: $("#CHSabado").is(":checked"),
                            domingo: $("#CHDomingo").is(":checked"),
                            MODE: MODE,
                            ubicacion: []
                        });
                    }
                    break;
                case "Mensual":
                    if ($("#RBFecha").is(":checked")) {
                        _inter = $("#TNMesMensual").val();
                        _escalar = 0;
                        _dia = $("#TNDiaMensual").val();
                    } else {
                        _inter = $("#TMes2").val();
                        _escalar = $("#ComboEscalar").val();
                        _dia = $("#ComboDia").val();
                    }
                    for (i = 0; i < articulos.length; i++) {
                        var _articulos = articulos[i];
                        var _ubicaciones = ubicaciones[i];
                        articulosAInventariar.push({
                            id_plan: '0',
                            fechaInicial: $("#FechaIniMensual").val(),
                            fechaFinal: $("#FechaFinMensual").val(),
                            articulo: _articulos,
                            intervalo: _inter,
                            escalar: _escalar,
                            dia: _dia,
                            MODE: MODE,
                            ubicacion: []
                        });
                    }
                    break;
            }

            /************************ FIN VALIDAR INPUTS DEL FORM ************************/

            var alma = $("#almacen option:selected").attr('alma');

            console.log("articulosAInventariar = ", articulosAInventariar);
            console.log("MODE = ", MODE);
            console.log("almacen = ", alma);


            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    action: "existe",
                    articulos: articulosAInventariar
                },
                beforeSend: function(x) {
                    if (x && x.overrideMimeType) {
                        x.overrideMimeType("application/json;charset=UTF-8");
                    }
                },
                url: '/api/inventariosciclicos/update/index.php',
                success: function(data) {
                    console.log(data);
                    var articulos_produccion = 0;
                    if($("#articulos_produccion").is(':checked'))
                        articulos_produccion = 1;

                    var articulos_cuarentena = 0;
                    if($("#articulos_cuarentena").is(':checked'))
                        articulos_cuarentena = 1;

                    var articulos_obsoletos = 0;
                    if($("#articulos_obsoletos").is(':checked'))
                        articulos_obsoletos = 1;

                    if (data.success == false) {
                        console.log("Entro por false");
                        $("#btnCancel").hide();
                        //l.ladda( 'start' );
                        $.ajax({
                            type: "POST",
                            dataType: "json",
                            data: {
                                action: "add",
                                articulos_produccion: articulos_produccion,
                                articulos_cuarentena: articulos_cuarentena,
                                articulos_obsoletos: articulos_obsoletos,
                                almacen: alma,
                                MODE: MODE,
                                arrInv: articulosAInventariar
                            },
                            beforeSend: function(x) {
                                if (x && x.overrideMimeType) {
                                    x.overrideMimeType("application/json;charset=UTF-8");
                                }
                            },
                            url: '/api/inventariosciclicos/update/index.php',
                            success: function(data) {
                                if (data.success == true) {
                                    //console.log("Inv Creado",data);
                                    //l.ladda('stop');
                                    //$("#btnCancel").show();
                                    
                                    setTimeout(function(){
                                        location.reload(true);
                                        
                                    },2000);
                                    cancelar();
                                    
                                        swal(
                                            'Completado',
                                            'Inventario cíclico planificado con éxito',
                                            'success'
                                        );
                                } else {
                                    //alert(data.err);
                                    //l.ladda('stop');
                                    $("#btnCancel").show();
                                }
                            }, error: function(data) {
                                console.log("ERROR:", data);
                            }
                        });
                    }
                    if (data.success == true) {
                        console.log("Entro por true");
                        swal(
                            'Error',
                            'Hay productos que pertenecen a conteo activo de un inventario',
                            'error'
                        );
                    }
                },
                error: function(data)
                {
                    console.log("ERROR EXISTE", data);
                }
            });
//**********************************************************************************************
                    //}
                },
                error: function(data)
                {
                    console.log("ERROR", data);
                }
            });

        });




        function buscarEnLista() {


            $("#fromU .itemlist").remove();
            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    almacen: $("#almacen").val(),
                    parameter: $("#txtCriterio2").val(),
                    action: "buscarArticulos"
                },
                beforeSend: function(x) {
                    if (x && x.overrideMimeType) {
                        x.overrideMimeType("application/json;charset=UTF-8");
                    }
                },
                url: '/api/almacen/update/index.php',
                success: function(data) {

                    if (data.success == true) {


                        var arr = $.map(data.articulos, function(el) {
                            return el;
                        })
                        arr.pop();
                        for (var i = 0; i < data.articulos.length; i++) {
                            var ul = document.getElementById("fromU");
                            var li = document.createElement("li");
                            var checkbox = document.createElement("input");
                            checkbox.style.marginRight = "10px";
                            checkbox.setAttribute("type", "checkbox");
                            checkbox.setAttribute("value", data.articulos[i].cve_articulo);
                            checkbox.setAttribute("class", "drag");
                            checkbox.setAttribute("onclick", "selectParent(this)");
                            li.appendChild(checkbox);
                            li.appendChild(document.createTextNode(data.articulos[i].cve_articulo + " - " + data.articulos[i].des_articulo + " (" + data.articulos[i].Suma + " piezas disp.)"));
                            li.setAttribute("dayta-draggable", "item");
                            li.setAttribute("draggable", "false");
                            li.setAttribute("aria-draggable", "false");
                            li.setAttribute("aria-grabbed", "false");
                            li.setAttribute("tabindex", "0");
                            li.setAttribute("class", "itemlist");
                            li.setAttribute("onclick", "selectChild(this)");
                            li.setAttribute("value", data.articulos[i].cve_articulo);
                            //  li.setAttribute("area", data.articulos[i].area === "true")
                            ul.appendChild(li);
                        }
                    }
                }
            })
        }

        function almacen2() {
            $('#grid-listado-articulos').jqGrid('clearGridData')
                .jqGrid('setGridParam', {
                    postData: {
                        almacen: $("#almacenes").val(),
                        producto: $("#producto").val(),
                        action: "traerArticulosDeAlmacenExist2"
                    },
                    datatype: 'json',
                    page: 1
                })
                .trigger('reloadGrid', [{
                    current: true
                }]);

        }

        $(document)



        function almacen() {
            ReloadGridArticulos();
            $("#modal-articulos").modal('show');
        }




        function agregarArti() {

            var ids = $("#grid-listado-articulos").jqGrid('getGridParam', 'selarrrow');



            //Si hay filas seleccionadas entro, sino no hace nada
            var gg = 0;
            if (ids != null) {
                var i = ids.length - 1;
                //Recorro el array de ids para copiar cada fila al otro grid
                for (i; i >= 0; i--) {

                    gg = 0;
                    //Nombre de la tabla origen
                    var $grid = jQuery("#grid-listado-articulos");
                    //Obtengo un objeto con los datos de una fila, esta línea
                    //no trae el id de la fila
                    var data = $grid.jqGrid('getRowData', ids[i]);

                    var da = JSON.stringify(data);
                    var dat = JSON.stringify(data);
                    var res = dat.split(":");
                    var arti = res[1].split('"');
                    var des = res[2].split('"');
                    var sum = res[3].split('"');
                    var sum = res[3].split('"');
                    var ubi = res[4].split('"');

                    $('#grid-general').each(function() {
                        $(this).find('tr').each(function(i) {
                            $(this).find('td').each(function(i) {
                                // code to be execute 
                                if ($(this).text() == arti[1]) {
                                    gg++;
                                }
                            });
                        });
                    });


                    if (gg < 1) {
                        g = $("#grid-general");
                        //Le asigno el id porque la línea anterior no me lo trae
                        data.item_id = ids[i];

                        emptyItem = [{
                            cve_articulo: arti[1],
                            des_articulo: des[1],
                            suma: sum[1],
                            cve_ubicacion: ubi[1],
                            acciones: '<a href="#" onclick="borr(' + g.length + ')" class="borr"><i class="fa fa-eraser" alt="Borrar"></i></a>'
                        }];
                        //Inserto en el grid destino
                        $("#grid-general").jqGrid('addRowData', g.length, emptyItem);
                        //Quito la fila seleccionada del primer grid (origen)
                        $grid.jqGrid('delRowData', ids[i]);
                    } else {
                        swal(
                            '¡Alerta!',
                            'El articulo que intenta agregar esta siendo usado en este momento',
                            'warning'
                        );
                    }
                }
            }

            $("#modal-articulos").modal('hide');

            $(window).triggerHandler('resize.jqGrid');

        };


        $(".borr").click(function() {
            $(this).parent('td').parent('tr').remove();
        });

        function borr(valor) {
            //alert(valor);
            $('#jqg' + valor).remove();
        };



        function selectParent(e) {
            if (e.checked) {
                e.parentNode.setAttribute("aria-grabbed", "true");
            } else {
                e.parentNode.setAttribute("aria-grabbed", "false");
            }
        }

        function selectChild(e) {
            if (e.getAttribute("aria-grabbed") == "true") {
                e.firstChild.checked = true;
            } else {
                e.firstChild.checked = false;
            }
        }

        $("#txtCriterio").keyup(function(event) {
            if (event.keyCode == 13) {
                $("#buscarA").click();
            }
        });

        $("#txtCriterio2").keyup(function(event) {
            if (event.keyCode == 13) {
                $("#buscar2").click();
            }
        });
    </script>
    <script type="text/javascript">
        function detalle(_codigo) {
            loadArticleDetails(_codigo);
            $modal0 = $("#coModal");
            $modal0.modal('show');
        }

        function detallec(_codigo) {
            loadArticleDetailsc(_codigo);
            $modal0 = $("#coModal");
            $modal0.modal('show');
        }


        function asignarUsuario(_codigo) {
            $.ajax({
                url: '/api/inventariosciclicos/lista/index.php',
                data: {
                    action: 'getPendingCount',
                    id: _codigo
                },
                dataType: 'json',
                method: 'GET'
            }).done(function(data) {
                if (data.total > 0) {
                    swal({
                        title: "Advertencia",
                        text: "Quedan artículos pendientes por contar. ¿Desea contar los artículos pendientes?",
                        type: "warning",
                        showCancelButton: true,
                        cancelButtonText: "No",
                        showConfirmButton: true,
                        confirmButtonText: "Sí",
                        allowOutsideClick: false
                    }, function(confirm) {
                        if (confirm) {
                            detalle(_codigo);
                        } else {
                            loadModalUser(_codigo);
                        }
                    });
                } else {
                    loadModalUser(_codigo);
                }
            });
        }

        function loadModalUser(_codigo) {
            $.ajax({
                url: '/api/inventariosciclicos/lista/index.php',
                data: {
                    action: 'getAvailableUsers',
                    id: _codigo
                },
                dataType: 'json',
                method: 'GET'
            }).done(function(data) {
                var usuarios = data.usuarios;
                var usuario = document.getElementById('usuario');
                var select = document.createElement('option');
                usuario.innerHTML = null;
                select.text = 'Seleccione Usuario';
                select.value = '';
                usuario.add(select)

                for (var i = 0; i < usuarios.length; i++) {
                    var options = document.createElement('option');
                    options.value = usuarios[i].cve_usuario;
                    options.text = usuarios[i].nombre_completo;
                    usuario.add(options);
                }

                $("#usuario").trigger("chosen:updated");
                $("#folionumero").html(_codigo);
                $("#asignarUsuario").modal('show');
            });
        }

        $("#guardarUsuario").on("click", function(e) {
            $.ajax({
                url: '/api/inventariosciclicos/update/index.php',
                data: {
                    action: 'guardarUsuario',
                    id: $("#folionumero").html(),
                    usuario: $("#usuario").val()
                },
                dataType: 'json',
                method: 'POST'
            }).done(function(data) {
                $("#asignarUsuario").modal('hide');
                if (data.success) {
                    swal("Exito", "Usuario asignado correctamente", "success");
                    swal({
                        title: "Exito",
                        text: "Usuario asignado correctamente",
                        type: "success",
                        showCancelButton: false,
                        allowOutsideClick: false
                    }, function(confirm) {
                        detalle($("#folionumero").html());
                    });

                } else {
                    swal("Error", "El usuario que intenta asignar ya fue asignado durante los conteos", "error");
                }
            }).always(function() {
                $('#grid-table').jqGrid('clearGridData')
                    .jqGrid('setGridParam', {
                        postData: {
                            criterio: ''
                        },
                        datatype: 'json',
                        page: 1
                    })
                    .trigger('reloadGrid', [{
                        current: true
                    }]);
            })
        });


        var loadArticleDetails;
        (function() {
            loadArticleDetails = function(codigo) {
                $.jgrid.gridUnload("#grid-table2");
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
                    url: '/api/inventariosciclicos/update/index.php',
                    datatype: "json",
                    postData: {
                        ID_PLAN: codigo,
                        action: "loadDetalle"
                    },
                    shrinkToFit: false,
                    height: 'auto',
                    mtype: 'POST',
                    colNames: ['ID', 'Clave', 'Descripción', 'Zona de almacenaje', 'Ubicación', 'Serie', 'Lote', 'Caducidad', 'Stock Teórico', 'Stock Físico', 'Diferencia', 'Datetime Inicio', 'Datetime Final', 'Conteo', 'Usuario'],
                    colModel: [{
                        name: 'ID_PLAN',
                        index: 'ID_PLAN',
                        width: 60,
                        sorttype: "int",
                        editable: false,
                        hidden: true
                    }, {
                        name: 'clave',
                        index: 'clave',
                        width: 120,
                        editable: false,
                        sortable: false
                    }, {
                        name: 'descripcion',
                        index: 'descripcion',
                        width: 200,
                        editable: false,
                        sortable: false
                    }, {
                        name: 'zona',
                        index: 'zona',
                        width: 200,
                        editable: false,
                        sortable: false
                    }, {
                        name: 'ubicacion',
                        index: 'ubicacion',
                        width: 200,
                        editable: false,
                        sortable: false
                    }, {
                        name: 'serie',
                        index: 'serie',
                        width: 150,
                        editable: false,
                        sortable: false
                    }, {
                        name: 'lote',
                        index: 'lote',
                        width: 150,
                        editable: false,
                        sortable: false
                    }, {
                        name: 'caducidad',
                        index: 'caducidad',
                        width: 150,
                        editable: false,
                        sortable: false
                    }, {
                        name: 'stockTeorico',
                        index: 'stockTeorico',
                        width: 150,
                        editable: false,
                        sortable: false
                    }, {
                        name: 'stockFisico',
                        index: 'stockFisico',
                        width: 150,
                        editrules: {
                            integer: true
                        },
                        editable: true,
                        edittype: 'text',
                        sortable: false
                    }, {
                        name: 'diferencia',
                        index: 'diferencia',
                        width: 150,
                        editable: false,
                        sortable: false
                    }, {
                        name: 'inicio',
                        index: 'inicio',
                        width: 150,
                        editable: false,
                        sortable: false
                    }, {
                        name: 'fin',
                        index: 'fin',
                        width: 150,
                        editable: false,
                        sortable: false
                    }, {
                        name: 'conteo',
                        index: 'conteo',
                        width: 100,
                        editable: false,
                        sortable: false
                    }, {
                        name: 'usuario',
                        index: 'usuario',
                        width: 100,
                        editable: false,
                        sortable: false
                    }],
                    rowNum: 30,
                    rowList: [30, 40, 50],
                    pager: pager_selector,
                    viewrecords: true,
                    editurl: '/api/inventariosciclicos/update/index.php',
                    onSelectRow: function(id) {
                        var ID_PLAN = id.split('|')[3];
                        console.log(id);
                        jQuery('#grid-table2').jqGrid(
                            'editRow',
                            id, {
                                keys: true,
                                oneditfunc: function() {
                                    console.error("editando");
                                },
                                successfunc: function() {
                                    $('#grid-table2').jqGrid('clearGridData')
                                        .jqGrid('setGridParam', {
                                            postData: {
                                                action: 'loadDetalle',
                                                ID_PLAN: ID_PLAN
                                            },
                                            datatype: 'json',
                                            page: 1
                                        })
                                        .trigger('reloadGrid', [{
                                            current: true
                                        }]);
                                    $('#grid-table').jqGrid('clearGridData')
                                        .jqGrid('setGridParam', {
                                            postData: {
                                                criterio: ''
                                            },
                                            datatype: 'json',
                                            page: 1
                                        })
                                        .trigger('reloadGrid', [{
                                            current: true
                                        }]);
                                }
                            }
                        );
                    },
                });

                // Setup buttons
                $("#grid-table2").jqGrid('navGrid', '#grid-pager2', {
                    edit: false,
                    add: false,
                    del: false,
                    search: false
                }, {
                    height: 300,
                    reloadAfterSubmit: true
                });


                $(window).triggerHandler('resize.jqGrid');
            }

            loadArticleDetailsc = function(codigo) {
                $.jgrid.gridUnload("#grid-table2");
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
                    url: '/api/inventariosciclicos/update/index.php',
                    datatype: "json",
                    postData: {
                        ID_PLAN: codigo,
                        action: "loadDetalle"
                    },
                    shrinkToFit: false,
                    height: 'auto',
                    mtype: 'POST',
                    colNames: ['ID', 'Clave', 'Descripción', 'Zona de almacenaje', 'Ubicación', 'Serie', 'Lote', 'Caducidad', 'Stock Teórico', 'Stock Físico', 'Diferencia', 'Datetime Inicio', 'Datetime Final', 'Conteo', 'Usuario'],
                    colModel: [{
                        name: 'ID_PLAN',
                        index: 'ID_PLAN',
                        width: 60,
                        sorttype: "int",
                        editable: false,
                        hidden: true
                    }, {
                        name: 'clave',
                        index: 'clave',
                        width: 120,
                        editable: false,
                        sortable: false
                    }, {
                        name: 'descripcion',
                        index: 'descripcion',
                        width: 200,
                        editable: false,
                        sortable: false
                    }, {
                        name: 'zona',
                        index: 'zona',
                        width: 200,
                        editable: false,
                        sortable: false
                    }, {
                        name: 'ubicacion',
                        index: 'ubicacion',
                        width: 200,
                        editable: false,
                        sortable: false
                    }, {
                        name: 'serie',
                        index: 'serie',
                        width: 150,
                        editable: false,
                        sortable: false
                    }, {
                        name: 'lote',
                        index: 'lote',
                        width: 150,
                        editable: false,
                        sortable: false
                    }, {
                        name: 'caducidad',
                        index: 'caducidad',
                        width: 150,
                        editable: false,
                        sortable: false
                    }, {
                        name: 'stockTeorico',
                        index: 'stockTeorico',
                        width: 150,
                        editable: false,
                        sortable: false
                    }, {
                        name: 'stockFisico',
                        index: 'stockFisico',
                        width: 150,
                        editrules: {
                            integer: true
                        },
                        editable: true,
                        edittype: 'text',
                        sortable: false
                    }, {
                        name: 'diferencia',
                        index: 'diferencia',
                        width: 150,
                        editable: false,
                        sortable: false
                    }, {
                        name: 'inicio',
                        index: 'inicio',
                        width: 150,
                        editable: false,
                        sortable: false
                    }, {
                        name: 'fin',
                        index: 'fin',
                        width: 150,
                        editable: false,
                        sortable: false
                    }, {
                        name: 'conteo',
                        index: 'conteo',
                        width: 100,
                        editable: false,
                        sortable: false
                    }, {
                        name: 'usuario',
                        index: 'usuario',
                        width: 100,
                        editable: false,
                        sortable: false
                    }],
                    rowNum: 30,
                    rowList: [30, 40, 50],
                    pager: pager_selector,
                    viewrecords: true,
                });

                // Setup buttons
                $("#grid-table2").jqGrid('navGrid', '#grid-pager2', {
                    edit: false,
                    add: false,
                    del: false,
                    search: false
                }, {
                    height: 300,
                    reloadAfterSubmit: true
                });


                $(window).triggerHandler('resize.jqGrid');
            }

        })();
    </script>

    <script type="text/javascript">
        function generarPDF(consecutivo) {
            var form = document.createElement('form'),
                nobody = document.createElement('input'),
                folio = document.createElement('input'),
                action = document.createElement('input');

            form.setAttribute('target', '_blank');
            form.setAttribute('method', 'post');
            form.setAttribute('action', '/api/inventariosciclicos/update/index.php');
            nobody.setAttribute('type', 'hidden');
            nobody.setAttribute('name', 'nofooternoheader');
            nobody.setAttribute('value', 'true');
            folio.setAttribute('type', 'hidden');
            folio.setAttribute('name', 'id');
            folio.setAttribute('value', consecutivo);
            action.setAttribute('type', 'hidden');
            action.setAttribute('name', 'action');
            action.setAttribute('value', 'printReport');
            form.appendChild(nobody);
            form.appendChild(folio);
            form.appendChild(action);
            document.getElementsByTagName('body')[0].appendChild(form);
            form.submit();
        }

        $('#modal-articulos').on('shown.bs.modal', function (e) {
            $("#grid-listado-articulos").jqGrid('setGridWidth', $('#modal-articulos .modal-body').width()-100);
        })


    </script>


    <style type="text/css">
      
        .ui-jqgrid tr.jqgrow td {
            padding: 4px; 
        }

        .tab .ui-jqgrid .ui-jqgrid-btable {
            table-layout: inherit;
            margin: 0;
            outline-style: none;
            width: auto !important;
        }
        
        .tab .ui-state-default ui-jqgrid-hdiv {
           
        }
        
        .tab .ui-jqgrid-view {
           
        }
        
        .tab .ui-state-default ui-jqgrid-hdiv {
           
        }
        
        .tab .ui-jqgrid .ui-jqgrid-bdiv {
           
            height: 200px !important;
            height: 10px;
        }
        
        #detalle_wrapper .ui-jqgrid-hdiv.ui-state-default.ui-corner-top,
        #detalle_wrapper #gview_grid-table2,
        #detalle_wrapper .ui-jqgrid.ui-widget.ui-widget-content.ui-corner-all,
        #detalle_wrapper .ui-jqgrid-pager.ui-state-default.ui-corner-bottom,
        #detalle_wrapper #grid-table2,
        #detalle_wrapper .ui-jqgrid-bdiv {
       
            
        }
        
        .tab .ui-state-default,
        .ui-widget-content,
        .ui-widget-header .ui-state-default {
          
        }
        
        .tab .ui-widget-content {
           
        }
        
        #FORM {
            width: 100%;
            /*height: 100%;*/
            position: absolute;
            left: 0px;
            z-index: 999;
        }
        
        .inmodal .modal-body {
            height: 510px;
        }
        
        .ibox-content .tab {
            clear: both;
            height: 30px;
        }
        
        .ibox-content .tab input {
            clear: both;
            margin-left: -20px;
        }
        /* Sortable items */
        
        .sortable-list {
            background-color: #F5F5F5;
            list-style: none;
            margin: 0;
            /*min-height: 30px; */
            padding: 1px;
            overflow: auto;
            border: 1px solid #ccc;
            -webkit-border-radius: 5px;
            -moz-border-radius: 5px;
            border-radius: 5px;
            min-height: 200px;
            height: 200px;
        }
        
        .sortable-list2 {
            background-color: #F5F5F5;
            list-style: none;
            margin: 0;
            min-height: 30px;
            padding: 1px;
            height: 140px;
            overflow: auto;
        }
        
        .sortable-item {
            background-color: #FFF;
            border: 0px solid #000;
            cursor: pointer;
            display: block;
            font-weight: bold;
            margin-bottom: 1px;
            padding: 5px 0;
        }
        
        .sortable-item-over {
            background-color: #b2e1ff;
            border: 0px solid #000;
            cursor: pointer;
            display: block;
            font-weight: bold;
            margin-bottom: 1px;
            padding: 5px 0;
        }
        
        .activeItem {
            background-color: #7fceff;
            cursor: move;
            display: block;
            font-weight: bold;
            margin-bottom: 1px;
            padding: 5px 0;
        }
        
        .art-content {
            min-width: 880px;
        }
        
        .art-wrapper {
            width: 100% !important;
        }
   
    </style>


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