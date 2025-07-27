<?php
$listaAP = new \AlmacenP\AlmacenP();
//$model_almacen = $almacenes->getAll();
$R = new \Ruta\Ruta();
$rutas = $R->getAll();
$U = new \Usuarios\Usuarios();
$usuarios = $U->getAll();
$ciudadSql = \db()->prepare("SELECT DISTINCT(Ciudad) as ciudad  FROM th_dest_pedido ");
$ciudadSql->execute();
$ciudades = $ciudadSql->fetchAll(PDO::FETCH_ASSOC);
$AreaEmbarq = new \AreaEmbarque\AreaEmbarque();

if(!isNikken()){
    $codDaneSql = \db()->prepare("SELECT *  FROM c_dane ");
    $codDaneSql->execute();
    $codDane = $codDaneSql->fetchAll(PDO::FETCH_ASSOC);
}
?>

<style>
    .table{
        margin-top: 2px;
        border: 1px solid #dddddd;
    }

    .table > thead > tr > th, 
    .table > tbody > tr > th,
    .table > tfoot > tr > th, 
    .table > thead > tr > td, 
    .table > tbody > tr > td, 
    .table > tfoot > tr > td {
        border: 1px solid #dddddd;
        line-height: 1.42857;
        padding: 4px 8px;
        vertical-align: top;
    }
    .table > thead > tr {
        background-color: #f7f7f7;
    }
    .table > thead > tr > th {
        font-size: 12px;
    }
    .table > tfoot > tr {
        background-color: #f7f7f7;
    }
    .table > tfoot > tr > th {
        font-weight: 400;
        font-size: 11px;
        font-family: Verdana,Arial,sans-serif;
    }
    .table > tfoot .sep{
        width: 1px;
        height: 15px;
        background-color: #dddddd;
        display: inline-block;
        margin: 0px 5px;
        position: relative;
        top:3px
    }
    .table .page{
        width: 50px;
        height: 20px;
        border: 1px solid #dddddd;
    }
    .table a {
        color: #000;
    }
  .fotos {
    width: 200px;    
  }
  
</style>

<link href="/css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">
<link href="/css/bootstrap.min.css" rel="stylesheet">
<link href="/font-awesome/css/font-awesome.css" rel="stylesheet">
<link href="/css/plugins/sweetalert/sweetalert.css" rel="stylesheet">
<link href="/css/plugins/datapicker/datepicker3.css" rel="stylesheet">
<!-- FooTable -->
<link href="/css/plugins/footable/footable.core.css" rel="stylesheet">
<link href="/css/plugins/chosen/chosen.css" rel="stylesheet">
<link href="/css/animate.css" rel="stylesheet">
<link href="/css/style.css" rel="stylesheet">
<link href="/css/plugins/footable/footable.core.css" rel="stylesheet">

<!-- Mainly scripts -->
<script src="/js/jquery-2.1.1.js"></script>
<script src="/js/moment.js"></script>
<script src="/js/moment-with-locales.js"></script>
<script src="/js/bootstrap.min.js"></script>
<script src="/js/plugins/metisMenu/jquery.metisMenu.js"></script>
<script src="/js/plugins/slimscroll/jquery.slimscroll.min.js"></script>

<!-- jqGrid -->
<script src="/js/plugins/jqGrid/i18n/grid.locale-es.js"></script>
<script src="/js/plugins/jqGrid/jquery.jqGrid.js"></script>

<!-- ClientesxRuta -->
<script src="/js/plugins/footable/footable.all.min.js"></script>

<!-- Peity -->
<script src="/js/plugins/peity/jquery.peity.min.js"></script>

<!-- Custom and plugin javascript -->
<script src="/js/inspinia.js"></script>
<script src="/js/plugins/pace/pace.min.js"></script>
<script src="/js/plugins/jquery-ui/jquery-ui.min.js"></script>
<script src="/js/plugins/ladda/spin.min.js"></script>
<script src="/js/plugins/ladda/ladda.min.js"></script>
<script src="/js/plugins/ladda/ladda.jquery.min.js"></script>
<script src="/js/plugins/chosen/chosen.jquery.js"></script>
<script src="/js/plugins/footable/footable.all.min.js"></script>
<script src="/js/dragdrop.js"></script>
<!-- Drag & Drop Panel -->
<!-- Sweet alert -->
<script src="/js/plugins/sweetalert/sweetalert.min.js"></script>
<script src="/js/plugins/datapicker/bootstrap-datepicker.js"></script>

<style type="text/css">
  .class_pager
  {
    width: 100% !important;
  }
    .ui-jqgrid,
    .ui-jqgrid-view,
    .ui-jqgrid-hdiv,
    .ui-jqgrid-bdiv,
    .ui-jqgrid,
    .ui-jqgrid-htable,
    #grid-table,
    #grid-table-surtido,
    #grid-table2,
    #grid-table-surtido1,
    #grid-consolidados,
    #grid-surtidos,
    #grid-areas-embarque,
    #grid-pager,
    #grid-pager-surtido,
    #grid-pager2,
    #grid-pager-surtido1,
    #grid-pager3,
    #grid-pager4,
    #gridpager-areas-embarque {
        max-width: 100%;
    }
</style>

<!-- Administrador de pedidos -->
<div class="wrapper wrapper-content  animated fadeInRight" id="list">
    <div id="alerta"></div>
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-title">
                    <div class="row">
                        <div class="col-lg-4" id="_title">
                            <h3>Cross Dock</h3>
                        </div>
                    </div>
                </div>
                <form id="myform">
                    <input type="hidden" id="hiddenAction" name="hiddenAction">
                    <input type="hidden" id="hiddenID_Pedidos">
                    <input type="hidden" id="hiddenPedidos">
                    <div class="ibox-content">
                        <div class="row" style="margin-top:15px">
                            <div class="col-md-3">
                                <label>Almacen</label>
                                <select class="chosen-select form-control" id="almacen">
                                    <option value="">Seleccione un almacen</option>
                                    <?php foreach( $listaAP->getAll() AS $a ): ?>
                                        <option value="<?php echo $a->id; ?>"><?php echo $a->nombre; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="email">Status de Orden</label>
                                <select name="status" id="status" class="chosen-select form-control">
                                    <option value="">Seleccione un Status</option>
                                    <option value="I">Editando</option>
                                    <option value="A" selected>Listo por asignar</option>
                                    <option value="S">Surtiendo</option>
                                    <option value="L">Pendiente de auditar</option>
                                    <option value="R">Auditando</option>
                                    <option value="P">Pendiente de empaque</option>
                                    <option value="M">Empacando</option>
                                    <option value="C">Pendiente de embarque</option>
                                    <option value="E">Embarcando</option>
                                    <option value="T">Enviado</option>
                                    <option value="F">Entregado</option>
                                    <option value="K">Cancelado</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Fecha Inicio</label>
                                    <div class="input-group date" id="data_1">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input id="fechai" type="text" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Fecha Fin</label>
                                    <div class="input-group date" id="data_2">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input id="fechaf" type="text" class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row" style="margin-top:0px">
                            <div class="col-md-3">
                                <label for="email">En</label>
                                <select name="filtro" id="filtro" class="chosen-select form-control">
                                    <option value="">Seleccione un filtro</option>
                                    <option value="o.Fol_folio">Número de Orden</option>
                                    <option value="o.Pick_Num">Número OC Cliente</option>
                                    <option value="p.Descripcion">Prioridad</option>
                                    <option value="c.RazonSocial">Cliente</option>
                                    <option value="u.nombre_completo">Usuario</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>Buscar por</label>
                                <input id="criteriob" type="text" placeholder="" value="" class="form-control">
                            </div>
                            <div class="col-md-3"><label>   </label></div>
                            <div class="col-md-3"><label>   </label>
                                <div class="input-group-btn">
                                    <button  onclick="filtralo()" type="button" class="btn btn-m btn-primary btn-block">
                                        <i class="fa fa-search"></i> Buscar
                                    </button>
                                </div>
                            </div>
                        </div>                      
                    </div>
                </div>
                <div class="row" style="margin-top:15px">
                    <div class="col-md-4">
                        <label>Total de Pedidos</label>
                        <input id="totalpedidos" type="text" value="0" class="form-control" style="text-align: center" disabled><br>
                    </div>
                    <div class="col-md-4">
                        <label>Peso Total</label>
                        <input id="totalpeso" type="text" value="0" class="form-control" style="text-align: center" disabled><br>
                    </div>
                    <div class="col-md-4">
                        <label>Volumen Total</label>
                        <input id="volumentotal" type="text" value="0" class="form-control" style="text-align: center" disabled><br>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <div class="ibox-content">
        <div class="row">
            <div class="col-lg-2">
                <div class="checkbox" id="chb_asignar">
                    <label for="btn-asignarTodo">
                      <input type="checkbox" name="asignarTodo" id="btn-asignarTodo">Asignar Todo
                    </label>
                </div>
            </div>
            <div class="col-lg-2">
                <div class="checkbox"id="chb_planificar">
                    <label for="btn-planificarTodo">
                        <input type="checkbox" name="planificarTodo" id="btn-planificarTodo">Planificar Todo
                    </label>
                </div>
            </div>
            <div class="col-lg-8">
                <!--a href="/api/v2/pedidos/exportar-cabecera" target="_blank" class="btn btn-primary pull-right" style="margin-left:10px; ; margin-top: 10px"><span class="fa fa-upload"></span> Exportar</a>-->
                <button id="exportalo" class="btn btn-primary pull-right" >
                    <span class="fa fa-upload"></span>Exportar
                </button>
            </div>
        </div>
        <div class="jqGrid_wrapper">
            <div class="table-responsive">
                <table id="dt-detalles" class="table" style="table-layout: auto;width: 100%;"> <!--lilo-->
                    <thead>
                        <tr>
                            <th scope="col" style="width: 80px !important; min-width: 80px !important;">Acciones</th>
                            <th id="col-asignar" scope="col" style="width: 70px !important; min-width: 70px !important;">Asignar</th>
                            <th id="col-planificar" scope="col" style="width: 100px !important; min-width: 100px !important;">Planificar Ola</th>
                            <th scope="col" style="width: 150px !important; min-width: 150px !important;">Nro. Orden</th>
                            <th scope="col" style="width: 150px !important; min-width: 150px !important;">No. OC Cliente</th>
                            <th scope="col" style="width: 150px !important; min-width: 150px !important;">Fecha Registro</th>
                            <th scope="col" style="width: 150px !important; min-width: 150px !important;">Prioridad</th>
                            <th scope="col" style="width: 150px !important; min-width: 150px !important;">Status</th>
                            <th scope="col" style="width: 250px !important; min-width: 250px !important;">Cliente</th>
                            <th scope="col" style="width: 400px !important; min-width: 400px !important;">Dirección</th>
                            <th scope="col" style="width: 100px !important; min-width: 100px !important;">Código Dane</th>
                            <th scope="col" style="width: 150px !important; min-width: 150px !important;">Ciudad</th>
                            <th scope="col" style="width: 150px !important; min-width: 150px !important;">Estado</th>
                            <th scope="col" style="width: 100px !important; min-width: 100px !important;">Cantidad</th>
                            <th scope="col" style="width: 100px !important; min-width: 100px !important;">Volumen</th>
                            <th scope="col" style="width: 100px !important; min-width: 100px !important;">Peso</th>
                            <th scope="col" style="width: 100px !important; min-width: 100px !important;">Inicio</th>
                            <th scope="col" style="width: 100px !important; min-width: 100px !important;">Fin</th>
                            <th scope="col" style="width: 100px !important; min-width: 100px !important;">Tiempo de Surtido</th>
                            <th scope="col" style="width: 100px !important; min-width: 100px !important;">Surtidor</th>
                            <th scope="col" style="width: 100px !important; min-width: 100px !important;">% Surtido</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-info"></tbody>
                    <tfoot>
                        <tr>
                            <th colspan="22">
                                Página <input class="page" type="text" value="1" style="text-align:center"/> de <span class="total_pages">0</span>
                                <div class="sep"></div>
                                <select class="count">
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="70">70</option>
                                    <option value="100">100</option>
                                </select>
                                <div class="sep"></div>
                                Mostrando <span class="from">0</span> - <span class="to">0</span> de <span class="total">0</span>
                            </th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    <div class="row">
        <div id="tab-2" class="tab-pane">
            <div class="panel-body">
                <div class="ibox-content"></br></br>
                    <div class="form-group">
                        <div class="input-group-btn">
                            <button id="btn-asignar" onclick="asignar()" type="button" class="btn btn-m btn-primary" style="display:none; padding-right: 20px;">Asignar</button>
                            <button id="btn-planificar" type="button" class="btn btn-m btn-primary" onclick="planificar()" style="padding-right: 20px;">Planificar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
  </div>
 </div>
</div>


    <div class="modal fade" id="modal-importar" role="dialog">
        <div class="vertical-alignment-helper">
            <div class="modal-dialog vertical-align-center modal-md">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4>Importar pedidos</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">                                          
                            <form id="form-import" action="import" method="post" enctype="multipart/form-data">
                                <div class="form-group">
                                    <label>Seleccionar la cabecera para importar</label>
                                    <input type="file" name="cabecera" id="cabecera" class="form-control"  required>
                                </div>
                                <div class="form-group">
                                    <label>Seleccionar los detalles para importar</label>
                                    <input type="file" name="detalles" id="detalles" class="form-control"  required>
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
                    </div>
                    <div class="modal-footer">
                        <div style="text-align: right">
                            <button id="btn-import" type="button" class="btn btn-primary">Importar</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <!-- Administrador de ola -->
    <div class="wrapper wrapper-content  animated fadeInRight" id="planificarOla">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-title">
                        <div class="row">
                            <div class="col-lg-4" id="_title">
                                <h3>Planificacion de Ola</h3>
                            </div>
                        </div>
                    </div>
                    <form id="myform">
                        <input type="hidden" id="hiddenAction" name="hiddenAction">
                        <input type="hidden" id="hiddenID_Pedidos">
                        <input type="hidden" id="hiddenPedidos">
                        <div class="ibox-content">
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label>Status de Orden</label>
                                    <input id="p_status" type="text" placeholder="" value="Pendiente de empaque" class="form-control" disabled><br>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Numero de Ola</label>
                                    <input id="txt-nro-ola" type="text" placeholder="" value="" class="form-control" disabled><br>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label>Fecha de Entrega</label>
                                    <div class="input-group date" id="data_3" disabled>
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input id="txt-ola-fechaentrega" type="text" class="form-control" disabled>
                                    </div>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Peso total de la Ola</label>
                                    <input id="txt-peso-ola" type="text" value="0" class="form-control" disabled><br>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label>Volumen Total</label>
                                    <input id="txt-volumen-ola" type="text" value="0" class="form-control" disabled>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Total Numero de Ordenes</label>
                                    <input id="txt-total-ordenes-ola" type="text" value="0" class="form-control" disabled>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group  col-md-12 text-center">
                                    <div class="input-group-btn">
                                        <button type="button" id="btn-cancelar-consolidado-ola" onclick="cancelar()" style="margin-right: 20px;" class="btn  btn-m btn-default">Cancelar</button>
                                        <button type="button" id="btn-crear-consolidado-ola" onclick="crearConsolidadoDeOla()" style="margin-right: 20px;" class="btn btn-m btn-primary" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'></i> Procesando...">Crear Ola</button>
                                        <button type="button" id="btn-borrar-consolidado-ola" onclick="borrarConsolidadoDeOla()" style="margin-right: 20px;display:none" class="btn  btn-m btn-primary">Borrar Ola</button>
                                        <button type="button" id="btn-asignar-consolidado-ola" style="display:none" class="btn  btn-m btn-primary" data-toggle="modal" data-target="#modal-asignar-usuario-ola">Asignar</button>
                                    </div>
                                </div>
                            </div>
                            <div id="div-grid-consolidados" class="row" style="display:none">
                                <div class="jqGrid_wrapper col-md-12">
                                    <div class="row" style="margin-bottom: 10px;">
                                        <div class="col-lg-6">
                                            <h4>Consolidado</h4>
                                        </div>
                                        <div class="col-lg-6" style="text-align: right">
                                            <button type="button" class="btn btn-sm btn-primary" onclick="consolidadoExcel()">Excel</button>
                                            <button type="button" class="btn btn-sm btn-danger" onclick="consolidadoPDF()">PDF</button>
                                        </div>
                                    </div>
                                    <table id="grid-consolidados"></table>
                                    <div id="grid-pager3"></div>
                                </div>
                            </div>
                            <div id="div-grid-surtidos" class="row" style="display:none">
                                <div class="jqGrid_wrapper col-md-12"><br/><br/>
                                    <div class="row" style="margin-bottom: 10px;">
                                        <div class="col-lg-6">
                                            <h4>Orden de Surtido</h4>
                                        </div>
                                        <div class="col-lg-6" style="text-align: right">
                                            <button type="button" class="btn btn-sm btn-primary" onclick="surtidoExcel()">Excel</button>
                                            <button type="button" class="btn btn-sm btn-danger" onclick="surtidoPDF()">PDF</button>
                                        </div>
                                    </div>
                                    <table id="grid-surtidos"></table>
                                    <div id="grid-pager4"></div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="row">
                </div>
            </div>
        </div>
    </div>

    <div class="modal inmodal" id="modalItems" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" style="width: 90%">
            <div class="modal-content animated bounceInRight">
                <div class="modal-content" style="width: 100%;">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4>Surtiendo el folio <span id="folio_surtido"></span></h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="jqGrid_wrapper">
                                    <table id="grid-table-surtido"></table>
                                    <div id="grid-pager-surtido"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer text-center">
                        <button id="btnGuardarSurtidoPorUbicacion" type="button" class="btn btn-primary pull-right" onclick="finalizar_surtido()"><span class="fa fa-sign-out"></span> Finalizar</button>
                        <button type="button" class="btn btn-danger pull-left" data-dismiss="modal"><span class="fa fa-ban"></span > Cancelar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="modal_zonasembarque" role="dialog">
        <div class="vertical-alignment-helper">
            <div class="modal-dialog vertical-align-center modal-sm">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4>Zonas de Embarque</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <label for="zonaembarque">Zonas Disponibles</label>
                                <select id="zonaembarque" class="chosen-select form-control">
                                <option value="">Seleccione la zona</option>
                            </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cerrar</button>
                        <a href="#" onclick="asignarZonaEmbarque()"><button id ="asignarpedido" class="btn btn-primary pull-right" type="button">
                        <i class="fa fa-plus"></i>&nbsp;&nbsp;Asignar zona</button>
                    </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="modal fade" id="modal_ubicacion_manufactura" role="dialog">
        <div class="vertical-alignment-helper">
            <div class="modal-dialog vertical-align-center modal-sm">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4>Mover a manufactura</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <label for="ubicacionManufactura">Ubicaciones de manufactura</label>
                                <select id="ubicacionManufactura" class="chosen-select form-control">
                                <option value="">Seleccione la zona</option>
                            </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cerrar</button>
                        <a href="#" onclick="asignarUbicacionManufactura()"><button id ="asignarpedido" class="btn btn-primary pull-right" type="button">
                            <i class="fa fa-plus"></i>&nbsp;&nbsp;Asignar zona</button>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Asiganr Usuario -->
    <div class="modal fade" id="modal-asignar-usuario" role="dialog">
        <div class="vertical-alignment-helper">
            <div class="modal-dialog vertical-align-center modal-sm">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4>Asignar a Usuarios</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <h3 class="text-center">Pedidos seleccionados</h3>
                                <h3 id="txt-cantidad-pedidos" class="text-center">0</h3><br/>
                            </div>
                            <div class="col-md-12">
                                <label for="usuario" class="text-center">Usuarios Disponibles</label>
                                <select id="usuario_selector" class="form-control">
                                    <option value="">Seleccione usuario</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cerrar</button>
                        <button onclick="asignarPedido()" id="asignarpedido" class="btn btn-primary pull-right" type="button">
                            <i class="fa fa-plus"></i>&nbsp;&nbsp;Asignar Usuario
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

  <div class="wrapper wrapper-content  animated fadeInRight" id="asignacionPedidos">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-title">
                        <div class="row">
                            <div class="col-lg-4" id="_title">
                                <h3>Asignacion de pedidos</h3>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-body">
                  <div class="row">
                      Asignando pedidos: <span id="avance"></span> de <span id="total"></span>
                  </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Asiganr Usuario a Ola -->
    <div class="modal fade" id="modal-asignar-usuario-ola" role="dialog">
        <div class="vertical-alignment-helper">
            <div class="modal-dialog vertical-align-center modal-md">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4>Asignar a Usuarios</h4>
                    </div>
                        <div class="row">
                    <div class="modal-body">

                            <div class="col-md-12">
                                <h3 class="text-center">Pedidos seleccionados</h3>
                                <h3 id="txt-cantidad-pedidos-ola" class="text-center">0</h3><br/>
                            </div>

                            <div class="col-md-12">
                                <label for="usuario-ola" class="text-center">Usuarios Disponibles</label>
                                <select id="usuario-ola" class="chosen-select form-control">
                                <option value="">Seleccione usuario</option>
                                <?php foreach( $usuarios as $key => $value ): ?>
                                    <option value="<?php echo $value->cve_usuario; ?>"><?php echo $value->nombre_completo; ?></option>
                                <?php endforeach; ?>
                            </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cerrar</button>
                        <button onclick="planificarConsolidadoOla()" id="asignarpedido" class="btn btn-primary pull-right" type="button">
                            <i class="fa fa-plus"></i>&nbsp;&nbsp;Asignar Usuario
                        </button>
                        <button id="asignarAE" class="btn btn-primary pull-right" onclick="asiganarAreaEmbarqueOla()" type="button">
                            <i class="fa fa-plus"></i>&nbsp;&nbsp;Asignar Area de embarque
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="modal-status" role="dialog">
        <div class="vertical-alignment-helper">
            <div class="modal-dialog vertical-align-center modal-sm">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4>Cambiar Status de Pedido</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Nº de Orden</label>
                            <input type="text" name="txt-status-actual-folio" id="txt-status-actual-folio" class="form-control" readonly>
                        </div>
                        <div class="form-group">
                            <label>Status de la Orden</label>
                            <select class="form-control chosen-select" name="select-nuevo-status-folio" id="select-nuevo-status-folio">
                            <option value="">Seleccione un Status</option>
                            <option value="I">Editando</option>
                            <option value="A">Listo por asignar</option>
                            <option value="S">Surtiendo</option>
                            <option value="L">Pendiente de auditar</option>
                            <option value="R">Auditando</option>
                            <option value="P">Pendiente de empaque</option>
                            <option value="M">Empacando</option>
                            <option value="C">Pendiente de embarque</option>
                            <option value="E">Embarcando</option>
                            <option value="T">Enviado</option>
                            <option value="F">Entregado</option>
                            <option value="K">Cancelado</option>
                        </select>
                        </div>
                        <div id="modal-status-motivo" class="form-group">
                            <label>Describa el mótivo</label>
                            <textarea id="modal-status-motivo-txt" class="form-control" rows="10"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                        <button type="button" class="btn btn-primary" onclick="asignarStatus()">Guardar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Modal Área de Embarque -->
    <div class="modal fade" id="modal-area-embarque" role="dialog">
        <div class="vertical-alignment-helper">
            <div class="modal-dialog vertical-align-center modal-lg">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Asignación de Área de embarque</h4>
                    </div>
                    <div class="modal-body" style="padding: 30px !important;">
                        <div class="row">
                            <div class="jqGrid_wrapper">
                                <table id="grid-areas-embarque"></table>
                                <div id="gridpager-areas-embarque"></div>
                            </div>
                        </div>
                        <div id="areaemb" style="display: none; margin-top:15px">
                            <label for="areaembarque">Área de embarque</label>
                            <div class="input-group">
                                <select name="areaembarque" id="areaembarque" class="chosen-select form-control">
                                    <option value="">Seleccione una opcion</option>
                                    <?php foreach( $AreaEmbarq->getAll() AS $p ): ?>
                                        <option value="<?php echo $p->cve_ubicacion; ?>"><?php echo $p->descripcion; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="input-group-btn">
                                    <button type="submit" onclick="btnasiganarAreaEmbarqueOla()" class="btn btn-primary" id="btnAsignar"><i class="fa fa-plus"></i> Asignar </button>
                                </div>
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

    

    <!-- Modal Usuario Wave -->
    <div class="modal fade" id="waveModal" role="dialog">
        <div class="vertical-alignment-helper">
            <div class="modal-dialog vertical-align-center modal-lg" style="width: 95% !important;">

                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4>Asignar a Usuarios</h4>
                    </div>
                    <div class="modal-body">

                        <div class="dragdrop">
                            <div class="form-group" id="clientesEditar">
                                <div class="col-md-6">
                                    <label for="email">Usuarios Disponibles</label>
                                    <ol data-draggable="target" id="fromw" class="wi">
                                    </ol>
                                </div>
                                <div class="col-md-6">
                                    <label for="email">Usuarios para Asignar</label>
                                    <ol data-draggable="target" id="tow" class="wi">
                                    </ol>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <a href="#" onclick="planificarConsolidadoOla()"><button id ="guardar" class="btn btn-primary pull-right" type="button"><i class="fa fa-plus"></i>&nbsp;&nbsp;Asignar Usuario</button></a>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-8" style="float: none;margin: 0 auto;">
                                    <span><b>Nota: </b>Seleccionar un item de los <b>"Disponibles"</b> y arrastralo a los <b>"Asignados"</b></span>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <button id="asignarAE" class="btn btn-primary pull-right" onclick="asiganarAreaEmbarqueOla()" type="button">
                                    <i class="fa fa-plus"></i>&nbsp;&nbsp;Asignar Area de embarque
                                </button>
                                </div>
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

    <!-- Modal articulo -->
    <div class="modal inmodal" id="coModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" style="width: 90%">
            <div class="modal-content animated bounceInRight">
                <!-- Modal content-->
                <div class="modal-content" style="width: 100%;">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                      <h4>Detalle del pedido <span id="folio_detalle"></span></h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                        <div class="col-md-12">
                            <div class="input-group" style="margin: 7px;">
                                <button id="btnRealizarSurtido" class="btn btn-primary pull-right" onclick="prepararSurtido()" style="margin-right: 5px;"><i class="fa fa-cubes"></i>Realizar surtido</button>
                                <button class="btn btn-primary pull-right" onclick="imprimirRemision()" style="margin-right: 5px;"><i class="fa fa-print"></i>Imprimir Remisión</button>
                                <button class="btn btn-primary pull-right" onclick="imprimirGuiasEmbarque()" style="margin-right: 5px;"><i class="fa fa-print"></i>Imprimir Guías de Embarque</button>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Pedidos</th>
                                            <th>Disponible</th>
                                            <th>BackOrder</th>
                                            <th>Volumen</th>
                                            <th>Peso</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td id="total_productos_detalle" align="center">0</td>
                                            <td id="total_disponibles_detalle" align="center">0</td>
                                            <td id="backorder" align="center">0</td>
                                            <td id="total_volumen_detalle" align="right">0</td>
                                            <td id="total_peso_detalle" align="right">0</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Tipo de Caja</th>
                                            <th>Peso de la Caja</th>
                                            <th>Dimensiones</th>
                                            <th>Volumen</th>
                                            <th>Cajas Requeridas</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td id="tipo_caja_detalle">0</td>
                                            <td id="peso_caja_detalle" align="right">0</td>
                                            <td id="dimensiones_caja_detalle" align="right">0</td>
                                            <td id="volumen_caja_detalle" align="right">0</td>
                                            <td id="cantidad_caja_detalle" align="center">0</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                          <input type="hidden" id="fol_cambio" name="fol_cambio" value="">
                          <button class="btn btn-primary pull-right" onclick="editar_cajas()" style="margin-right: 5px;" id="editBox"><i class="fa fa-save"></i>Guardar</button>
                          
                        </div>
                    </div>
                    
                    <label id="titulo1" style="display: none;">Pendiente de Surtir</label>
                    <div class="row" id="tabla_surtiendo" style="display: none;">
                        <div class="col-md-12">
                            <div class="jqGrid_wrapper">
                                <table id="grid-table2"></table>
                                <div id="grid-pager2"></div>
                            </div>
                        </div>
                    </div>
                     <br id="br_titulo" style="display: none;">
                    <label id="titulo2" style="display: none;">Surtidos</label>
                    <div class="row" id="tabla_surtido1" style="display: none;">
                        <div class="col-md-12">
                            <div class="jqGrid_wrapper">
                                <table id="grid-table-surtido1"></table>
                                <div id="grid-pager-surtido1"></div>
                            </div>
                        </div>
                    </div>
                       
                    <label id="titulo3" style="display: none;">Pedido</label>
                    <div class="row" id="tabla_listo_por_asignar" style="display: none;">
                        <div class="col-md-12">
                            <div class="jqGrid_wrapper">
                                <table id="grid-table-listo-por-asignar"></table>
                                <div class="class_pager" id="grid-pager-listo-por-asignar" ></div>
                            </div>
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

    <!-- Modal articulo -->
    <div class="modal fade" id="modal_surtido" role="dialog">
        <div class="vertical-alignment-helper">
            <div class="modal-dialog vertical-align-center modal-lg" style="width: 95% !important;">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4>Surtir Artículos</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="jqGrid_wrapper">
                                    <table id="grid-table6"></table>
                                    <div id="grid-pager6"></div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" id="volverSurtido">Cancelar</button>
                        <button type="button" class="btn btn-primary" id="guardarSurtido">Guardar surtido</button>
                    </div>
                </div>
            </div>
        </div>
    </div>


<div class="modal fade" id="modal_fotos" role="dialog">
  <div class="vertical-alignment-helper">
    <div class="modal-dialog vertical-align-center modal-lg" style="width: 95% !important;">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 align="center">Foto de Empaque</h4>
              <div class="modal-body">
                <div class="row">
                  <div class="col-md-12">
                    <!--contenido-->
                    <div class="row" align="center">
                      <img id="foto1" src="" alt="" class="fotos" width="33%">
                    </div>                                                                   
                   </div>
                </div>
              </div>
        </div>
        <!-- Modal Contenido de Fotos Embarque-->
        <div class="modal-header align-center"> 
          <h4 align="center">Fotos  de Embarque</h4>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-4">
              <div class="row col-md-12" align="center">
                <img id="foto2" src="" alt="" class="fotos" width="100%">
              </div>
            </div>
            <div class="col-md-4">
              <div class="row col-md-12" align="center">
                <img id="foto3" src="" alt="" class="fotos" width="100%">
              </div>
            </div>
            <div class="col-md-4">
              <div class="row col-md-12" align="center">
                <img id="foto4" src="" alt="" class="fotos" width="100%">
              </div>
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





<div class="modal inmodal" id="modal-destinatario" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content animated bounceInRight">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <i class="fa fa-laptop modal-icon"></i>
        <h4 class="modal-title" id="modaltitle">Destinatario</h4>
      </div>
      <div class="modal-body">
        <div class="row">
              <div class="col-md-12">
                  <div class="form-group">
                      <label>Razón Social</label>
                      <input type="text" class="form-control" id="destinatario_razonsocial">
                  </div>
              </div>
              <div class="col-md-12">
                  <div class="form-group">
                      <label>Dirección</label>
                      <input type="text" class="form-control" id="destinatario_direccion">
                  </div>
              </div>
              <div class="col-md-12">
                  <div class="form-group">
                      <label>Colonia</label>
                      <input type="text" class="form-control" id="destinatario_colonia">
                  </div>
              </div>

              <div class="col-md-4">                    
                  <div class="form-group">
                      <label>Código Dane / Código Postal</label>
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
              </div>

              <div class="col-md-4">
                  <div class="form-group">
                      <label>Municipio / Ciudad</label>
                      <input type="text" class="form-control" id="destinatario_ciudad" <?php if(isset($codDane) && !empty($codDane)): ?>readonly<?php endif; ?> >
                  </div>
              </div>

              <div class="col-md-4">
                  <div class="form-group">
                      <label>Departamento / Estado</label>
                      <input type="text" class="form-control" id="destinatario_estado" <?php if(isset($codDane) && !empty($codDane)): ?>readonly<?php endif; ?> >
                  </div>
              </div>

              <div class="col-md-6">
                  <div class="form-group">
                      <label>Contacto</label>
                      <input type="text" class="form-control" id="destinatario_contacto">
                  </div>
              </div>
              <div class="col-md-6">
                  <div class="form-group">
                      <label>Teléfono</label>
                      <input type="text" class="form-control" id="destinatario_telefono">
                  </div>
              </div>

          </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-white" data-dismiss="modal" id="btnCancel">Cerrar</button>
        <button type="submit" class="btn btn-primary ladda-button" onclick="guardarDestinatario()">Guardar</button>
      </div>
    </div>
  </div>
</div>


<form target="_blank" class="hidden" action="/administradorpedidos/consolidado/pdf" method="post" id="pdfConsolidado">
    <input type="hidden" name="folio" value="0">
    <input type="hidden" name="nofooternoheader" value="true">
</form>
<form target="_blank" class="hidden" action="/administradorpedidos/consolidado/excel" method="post" id="excelConsolidado">
    <input type="hidden" name="folio" value="0">
    <input type="hidden" name="nofooternoheader" value="true">
</form>
<form target="_blank" class="hidden" action="/administradorpedidos/surtido/pdf" method="post" id="pdfSurtido">
    <input type="hidden" name="folio" value="0">
    <input type="hidden" name="nofooternoheader" value="true">
</form>
<form target="_blank" class="hidden" action="/administradorpedidos/surtido/excel" method="post" id="excelSurtido">
    <input type="hidden" name="folio" value="0">
    <input type="hidden" name="nofooternoheader" value="true">
</form>


<div id="inner_download"></div>
</div>




<script>
  
    $('#btn-import').on('click', function() {

        $('#btn-import').prop('disable', true);
        var bar = $('.progress-bar');
        var percent = $('.percent');
        //var status = $('#status');

        var formData = new FormData();
        formData.append("clave", "valor");

        $.ajax({
            // Your server script to process the upload
            url: '/pedidos/importar',
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
                            percent.html(percentComplete + '%');
                            if (percentComplete === 100) {
                                setTimeout(function() {
                                    $('.progress').hide();
                                }, 2000);
                            }
                        }
                    }, false);
                }
                return myXhr;
            },
            success: function(data) {
                setTimeout(
                    function() {
                        if (data.status == 200) {
                            swal("Exito", data.statusText, "success");
                            $('#modal-importar').modal('hide');
                            ReloadGrid();
                        } else {
                            swal("Error", data.statusText, "error");
                        }
                    }, 1000)
            },
        });
    });
</script>



    <script>

        function imageFormat(id, surtido) {
            var html = '';
            html += '<a href="#" onclick="articulos(\'' + id + '\')"><i class="fa fa-search" title="Ver Detalles"></i></a>&nbsp;';
            html += '<a href="#" onclick="cambiarStatus(\'' + id + '\', \'' + surtido + '\')"><i class="fa fa-check" title="Cambiar Status"></i></a>&nbsp;';
            html += '<a href="#" onclick="destinatario(\'' + id + '\')"><i class="fa fa-truck" title="Ver Destinatarios"></i></a>&nbsp;';
            html += '<a href="#" onclick="embarqueFoto(\'' + id + '\')"><i class="fa fa-camera" title="Ver fotos de embarque"></i></a>';
            return html;
        }

        //////////////////////////////////////////////////////////hace un reload del grid llamando al php en la carpera "c" para hacer la consulta//////////////////////////////////////////////////////////
        function ReloadGrid() {
          
            $.ajax({
                type: "GET",
                dataType: "JSON",
                url: '/api/v2/crossdock',
                data: {
                    search: $("#criteriob").val(),
                    fecha_inicio: $("#fechai").val(),
                    fecha_fin: $("#fechaf").val(),
                    status: $("#status").val(),
                    almacen: $("#almacen").val(),
                    page : $('#dt-detalles .page').val(),
                    rows : $('#dt-detalles .count').val(),
                },
                success: function(data) { //lilo
                    if (data.status == 200) {
                        $('#dt-detalles .total_pages').text(data.total_pages);
                        $('#dt-detalles .page').text(data.page);
                        $('#dt-detalles .from').text(data.from);
                        $('#dt-detalles .to').text(data.to);
                        $('#dt-detalles .total').text(data.total);

                        var row = '', i = 0; //lilo
                        $.each(data.data, function(index, item){
                            i++;
                            row += '<tr>'+
                                        '<td align="center">'+imageFormat(item.orden, item.surtido)+'</td>'+
                                        //'<td align="center"><input type="checkbox" /></td>'+
                                        '<td align="center" class="column-asignar" data-id="'+item.orden+'"><input type="checkbox" data-id="'+item.orden+'"/></td>'+
                                        '<td align="center" class="column-planificar" data-id="'+item.orden+'"><input type="checkbox" data-id="'+item.orden+'"/></td>'+
                                        
                                        '<td>'+item.orden+'</td>'+
                                        '<td>'+item.orden_cliente+'</td>'+
                                        '<td align="center">'+item.fecha_pedido+'</td>'+
                                        '<td>'+item.prioridad+'</td>'+
                                        '<td>'+item.status+'</td>'+
                                        '<td>'+item.cliente+'</td>'+
                                        '<td>'+item.direccion+'</td>'+                                            
                                        '<td align="center">'+item.dane+'</td>'+
                                        '<td>'+item.ciudad+'</td>'+
                                        '<td>'+item.estado+'</td>'+
                                        '<td align="right">'+item.cantidad+'</td>'+
                                        '<td align="right">'+item.volumen+'</td>'+
                                        '<td align="right">'+item.peso+'</td>'+
                                        '<td align="center">'+item.fecha_ini+'</td>'+
                                        '<td align="center">'+item.fecha_fi+'</td>'+
                                        '<td align="center">'+item.TiempoSurtido+'</td>'+
                                        '<td>'+item.asignado+'</td>'+
                                        '<td align="center">'+item.surtido+'</td>'+
                                    '</tr>';                            
                        });
                        $('#dt-detalles tbody').html(row);
                        
                        if ($("#status").val() == 'A') {
                            $('#btn-asignar').show();
                            $('#planificalo').show();
                            $('#planificaloTodo').show();
                            $('#planificaloTodo').show();
                            $('#btn-planificar').show();
                            $("#col-asignar, #col-planificar").show();
                            $(".column-asignar, .column-planificar").show();
                            $("#chb_asignar").show();
                            $("#chb_planificar").show();
                        }
                        else {
                            $('#btn-asignar').hide();
                            $('#planificalo').hide();
                            $('#planificaloTodo').hide();
                            $('#planificaloTodo').hide();
                            $('#btn-planificar').hide();
                            $("#col-asignar, #col-planificar").hide();
                            $(".column-asignar, .column-planificar").hide();
                            $("#chb_asignar").hide();
                            $("#chb_planificar").hide();
                        }
                        
                    } else {
                        swal({
                            title: "Error",
                            text: "No se pudo planificar el inventario",
                            type: "error"
                        });
                    }
                }
            });                    
        }

        

        
        function asyncSqrt(callback) {
            setTimeout(function() {
                callback();
            }, 0 | Math.random() * 100);
        }

        asyncSqrt(function() {
            ReloadGrid()
        });

        var itemsArticulosPedidos = [];
        var itemActual = -1;
        var folio = '';


        $("#destinatario_dane").change(function() {
            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    codigo : $("#destinatario_dane").val(),
                    action : "getDane"
                },
                beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
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
        
        var destinatario_folio = '';
        function destinatario( folio ){
            destinatario_folio = folio;
            $.ajax({
                url: '/api/crossdock/lista/index.php',
                dataType: 'json',
                data: {
                    action: 'destinatarioDelPedido',
                    folio: folio
                },
                type: 'POST'
            }).done(function(data) {

                $("#destinatario_razonsocial").val(data.data.razonsocial);
                $("#destinatario_direccion").val(data.data.direccion);
                $("#destinatario_colonia").val(data.data.colonia);
                $("#destinatario_dane").val(data.data.postal);
                $("#destinatario_ciudad").val(data.data.ciudad);
                $("#destinatario_contacto").val(data.data.contacto);
                $("#destinatario_telefono").val(data.data.telefono);
                $("#destinatario_ciudad").val(data.data.ciudad);
                $("#destinatario_estado").val(data.data.departamento);

                $('#modal-destinatario').modal('show');

            });
        }


        function guardarDestinatario(){
            $.ajax({
                url: '/api/crossdock/update/index.php',
                dataType: 'json',
                data: {
                    action: 'guardarDestinatario',
                    folio: destinatario_folio,
                    razonsocial : $("#destinatario_razonsocial").val(),
                    direccion : $("#destinatario_direccion").val(),
                    colonia : $("#destinatario_colonia").val(),
                    postal : $("#destinatario_dane").val(),
                    ciudad : $("#destinatario_ciudad").val(),
                    contacto : $("#destinatario_contacto").val(),
                    telefono : $("#destinatario_telefono").val(),
                    estado : $("#destinatario_estado").val(),
                },
                type: 'POST'
            }).done(function(data) {

                swal("Éxito", "Datos del destinatario actualizado con éxito", "success");
                $('#modal-destinatario').modal('hide');

            });
        }

        function prepararSurtido() {
            siguienteItem();
            
        }

        $('#modalItems').on('hidden.bs.modal', function(e) {
            itemActual = -1;
            articulos($("#fol_cambio").val());
        });
        //EDG117
        function siguienteItem() {
            $("#grid-table-surtido").jqGrid("clearGridData");
            if(window.detalle_pedido["articulos"].length > 0){
              $("#folio_surtido").html(window.detalle_pedido["articulos"][0].folio);
              window.detalle_pedido["articulos"].forEach(function (item,index){
                console.log("item detalle",item);
                  obj = {
                      clave: item["clave"],
                      articulo: item["articulo"],
                      bl: item["ubicacion"],
                      ruta: item["ruta"],
                      surtidor: item["surtidor"],
                      existencia: item["existencia"],
                      ya_surtido: '<span id="surtido-item-' + item.ubicacion + '">'+item["surtidas"]+'</span>',
                      solicitado: item["pedidas"],
                      surtido: '<input id="surtir-item-' + item.clave + '" value="'+item.existencia+'" type="number" style="width:70px"/>',
                      acciones: '<button  onclick="guardarSurtidoPorUbicacion('+index+')" type="button" class="btn btn-m btn-primary btn-block" id ="btn_surtir_'+item["clave"]+'" style="width:70px; display:'+((item["surtidas"]>0)?'none':'block')+'" >Surtir</button>'
                  };
                  emptyItem = [obj];
                  $("#grid-table-surtido").jqGrid('addRowData', 0, emptyItem);
              });
            }
            $('#modalItems').modal('show');
            $('#coModal').modal('hide');
        }
      

        function guardarSurtidoPorUbicacion(item) 
        {
         /* var myData = $("#grid-table-surtido").jqGrid('getRowData');
         
          var camposurtido = (myData[0].ya_surtido);
          var allRowsOnCurrentPage = $('#grid-table-surtido').jqGrid('getDataIDs');
          console.log(allRowsOnCurrentPage);
          return;
          var rowData = $('#my-jqgrid-table').jqGrid('getRowData', rowId);
          rowData.Currency = '12321';
          $('#my-jqgrid-table').jqGrid('setRowData', rowId, rowData);
          return;*/
          var articulo = window.detalle_pedido["articulos"][item];
          var ultimo = (item == (window.detalle_pedido["articulos"].length-1))?true:false;
          $("#btn_surtir_"+articulo.clave).hide();
          var surtir = 0;
          var existencia = 0;
          var surtir_pedido = true;

          surtir = parseInt($("#surtir-item-"+articulo.clave).val());
          existencia = parseInt(articulo.existencia);
          if(surtir <= existencia)
          {
            articulo["surtidas"] = surtir;
            window.detalle_pedido["articulos"][item]["surtidas"] = surtir;
          }
          else
          {
            alert("No se puede colocar un valor mayor a lo solicitado: clave del producto: "+ articulo.clave);
            surtir_pedido = false;
          }
          if(surtir > articulo["surtidas"])
          {
            alert("No se puede colocar un valor mayor a lo solicitado: clave del producto: "+ articulo.clave);
            surtir_pedido = false;
          }
          if(surtir_pedido)
          {
            $.ajax({
              url: '/api/crossdock/update/index.php',
              dataType: 'json',
              data: {
                almacen: $('#almacen').val(),
                action: 'guardarSurtidoPorUbicacion',
                items: articulo,
                folio: $("#fol_cambio").val(),
                ultimo:ultimo
              },
              type: 'POST'
            }).done(function(data) {
              console.log("Surtiendo pedido",data);
              $("#surtido-item-"+articulo.ubicacion).html(surtir);
              $("#surtir-item-"+articulo.clave).val(articulo.pedidas-surtir);
            }
            );
          }
          if(ultimo)
          {
            //colocar la funcion que llama el boton de finalizar 
            finalizar_surtido();
          }
        }
              
        function finalizar_surtido()
        {
            window.porcentajeSurtido = 0;
            var total = 0;
            var p_surtido = 0;
            
            if(!folio.includes("OT"))
            {
             $.ajax({
                 url: '/api/crossdock/update/index.php',
                 data: {
                    action: 'traerporcentaje',
                    folio: $("#fol_cambio").val()
                  },
                  type: 'POST',
                  dataType: 'json'
                  })
                  .done(function(data) {
                  console.log("porcentaje surtido",data.resp["surtido"]);
                  window.porcentajeSurtido = parseInt(data.resp["surtido"]);
                  enviar_finalizar_pedido();
             });               
            }
            else
            {
                console.log($("#fol_cambio").val());
                obtenerUbicacionManufactura();
            }
        }
      
        function enviar_finalizar_pedido()
        {
             if (window.porcentajeSurtido >= 99) {
               $.ajax({
                 url: '/api/crossdock/update/index.php',
                 data: {
                    almacen:$('#almacen').val(),
                    folio:  $("#fol_cambio").val(),
                    action: 'horafin_surtir'
                  },
                  type: 'POST',
                  dataType: 'json'
                  })
                  .done(function(data) {
                    console.log("horafin",data);
                    console.log("enviar a qa");
                    enviarAEbarque_o_Qa();
                  });
                 
                } else {
                    $('#modalItems').modal('hide');
                    swal({
                        title: "Surtido completado",
                        text: "Pedido Incompleto , ¿Desea cerrar el pedido?",
                        type: "success",

                        showCancelButton: true,
                        cancelButtonText: "Si",
                        cancelButtonColor: "#14960a",

                        confirmButtonColor: "#55b9dd",
                        confirmButtonText: "No",
                        closeOnConfirm: true
                    },
                    function(e) {
                        if (e == true) 
                        {
                            //No
                            swal("Éxito", "Surtido realizado con artículos pendientes por surtir por falta de existencia", "warning");
                        } else {
                            //Si
                            $.ajax({
                              url: '/api/crossdock/update/index.php',
                              data: {
                                  almacen:$('#almacen').val(),
                                  folio:  $("#fol_cambio").val(),
                                  sufijo: window.detalle_pedido["articulos"][0]["sufijo"],
                                  status: status,
                                  action: 'cerrarPedido'
                                  
                              },
                              type: 'POST',
                              dataType: 'json'
                            })
                            .done(function(data) {
                                if (data.success) {
                                    filtralo();
                                    $('#modalItems').modal('hide');
                                    swal("Éxito", "Status de la orden cambiado exitosamente", "success");
                                } else {
                                    swal("Error", "Ocurrió un error al cambiar el status de la orden", "success");
                                }
                            });
                        }
                    });
                }
        }
        
        function enviarAEbarque_o_Qa() {
            swal({
                    title: "Surtido completado",
                    text: "¿A donde desea enviar el pedido?",
                    type: "success",

                    showCancelButton: true,
                    cancelButtonText: "Envar a QA (Auditar)",
                    cancelButtonColor: "#14960a",

                    confirmButtonColor: "#55b9dd",
                    confirmButtonText: "Enviar a embarque",
                    closeOnConfirm: true
                },
                function(e) {
                    if (e == true) {
                        //Embarque
                        obtenerZonasDisponibles();
                    } else {
                        //QA
                        status = 'L';
                        $.ajax({
                                url: '/api/crossdock/update/index.php',
                                data: {
                                    folio: folio,
                                    status: status,
                                    action: 'cambiarStatus'
                                },
                                type: 'POST',
                                dataType: 'json'
                            })
                            .done(function(data) {
                                if (data.success) {
                                    filtralo();
                                    $('#modalItems').modal('hide');
                                    swal("Éxito", "Status de la orden cambiado exitosamente", "success");

                                } else {
                                    swal("Error", "Ocurrió un error al cambiar el status de la orden", "success");
                                }
                            });
                    }
                });
        }

        function obtenerZonasDisponibles() {
            $.ajax({
                    url: '/api/crossdock/lista/index.php',
                    data: {
                        almacen: $('#almacen').val(),
                        action: 'obtenerZonasEmbarque'
                    },
                    type: 'POST',
                    dataType: 'json'
                })
                .done(function(data) {
                    if (data.success) {

                        options = '';
                        $.each(data.data, function(key, value) {
                            options += '<option value="' + value.id + '">' + value.nombre + '</option>';
                        });

                        $('#zonaembarque').html(options);
                        $('#zonaembarque').trigger("chosen:updated");
                        $('#modal_zonasembarque').modal('show');
                    } else {

                    }
                });
        }


        function asignarZonaEmbarque() {
            $.ajax({
                    url: '/api/crossdock/update/index.php',
                    data: {
                        folio: $("#fol_cambio").val(),
                        almacen: $('#almacen').val(),
                        status: 'C',
                        zonaembarque: $('#zonaembarque').val(),
                        action: 'asignarZonaEmbarque'
                    },
                    type: 'POST',
                    dataType: 'json'
                })
                .done(function(data) {
                    if (data.success) {
                        $('#modal_zonasembarque').modal('hide');
                        $('#modalItems').modal('hide');
                        swal("Éxito", "Status de la orden cambiado exitosamente", "success");

                        filtralo();
                    } else {
                        swal("Error", "Ocurrió un error al cambiar el status de la orden", "success");
                    }
                });
        }
      
        
        function obtenerUbicacionManufactura() {
            $.ajax({
                url: '/api/crossdock/lista/index.php',
                data: {
                    almacen: $('#almacen').val(),
                    action: 'obtenerUbicacionManufactura'
                },
                type: 'POST',
                dataType: 'json'
            })
            .done(function(data) {
                if (data.success) {

                    options = '';
                    $.each(data.data, function(key, value) {
                        options += '<option value="' + value.id + '">' + value.nombre + '</option>';
                    });

                    $('#ubicacionManufactura').html(options);
                    $('#ubicacionManufactura').trigger("chosen:updated");
                    $('#modal_ubicacion_manufactura').modal('show');
                } else {

                }
            });
        }
      
        function asignarUbicacionManufactura() 
        {
            $.ajax({
                url: '/api/crossdock/update/index.php',
                data: {
                    ubicacion:$('#ubicacionManufactura').val(),
                    almacen:$("#almacen").val(),
                    folio:  folio,
                    action: 'existenciasManufactura'
                },
                type: 'POST',
                dataType: 'json'
            })
            .done(function(data) {
                if (data.success) {
                    filtralo();
                    $('#modalItems').modal('hide');
                    $('#modal_ubicacion_manufactura').modal('hide');
                    swal("Éxito", "Se han movido las existencias a manufactura", "success");
                } else {
                    swal("Error", "Ocurrió un error al mover las existencias a manufactura", "success");
                }
            });
        }
    </script>


    <!-- Grid de inventario -->
    <script type="text/javascript">
        function isParamsEmpty() {
            if ($("#criteriob").val() == '' && $("#fechai").val() == '' && $("#fechaf").val() == '' && $("#status").val() == '' && $("#filtro").val() == '') {
                return true;
            }
            return false;
        }


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
                        setTimeout(function() {
                            $('#almacen').val(data.codigo.id).trigger('chosen:updated');
                            filtralo();
                        }, 1000);

                    }
                },
                error: function(res) {
                    window.console.log(res);
                }
            });
        }
        almacenPrede()

        //////////////////////////////////////Aqui se contruye el Grid de inventario//////////////////////////////////////////////

  function alertarefresh()
  {
    $(function($)
    {
      var html="x";
      $.ajax({
        url: '/api/correcciondir/lista/index.php',
        dataType: 'json',
        type: 'POST'
      }).done(function(data){
        console.log(data["rows"].length);
        if(data["rows"].length >= 1)
          html = "<div style='margin-bottom:0;' class='alert alert-warning' role='alert'>Hay "+data["rows"].length+" pedidos sin Guias generadas <a class='btn btn-primary btn-sm' href='http://wms.nikken.assistpro-adl.com/correcciondir/lists'>Ver</a></div>";
        else
          html="";
        $("#alerta").html(html);
      });
    });
  }
  
  alertarefresh();
  var i_alerta = setInterval(alertarefresh,20000);

      
      
        $('#dt-detalles .page').keypress(function(e) {
            if(e.which == 13) {
                filtralo()
            }
        });

        $('#dt-detalles .count').change(function() {       
            filtralo()        
        });
      ///////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $('#exportalo').click(function(){
          console.log("exportar");
          fnExcelReport();
          
          
        });
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        
        function fecha()
        {
            var today = new Date();
            var dd = today.getDate();
            var mm = today.getMonth()+1; //January is 0!
            var yyyy = today.getFullYear();
            if(dd<10) { dd = '0'+dd } 
            if(mm<10) { mm = '0'+mm } 
            return dd + '-' + mm + '-' + yyyy;
        }
      
        "use strict";jQuery.base64=(function($){var _PADCHAR="=",_ALPHA="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/",_VERSION="1.0";function _getbyte64(s,i){var idx=_ALPHA.indexOf(s.charAt(i));if(idx===-1){throw"Cannot decode base64"}return idx}function _decode(s){var pads=0,i,b10,imax=s.length,x=[];s=String(s);if(imax===0){return s}if(imax%4!==0){throw"Cannot decode base64"}if(s.charAt(imax-1)===_PADCHAR){pads=1;if(s.charAt(imax-2)===_PADCHAR){pads=2}imax-=4}for(i=0;i<imax;i+=4){b10=(_getbyte64(s,i)<<18)|(_getbyte64(s,i+1)<<12)|(_getbyte64(s,i+2)<<6)|_getbyte64(s,i+3);x.push(String.fromCharCode(b10>>16,(b10>>8)&255,b10&255))}switch(pads){case 1:b10=(_getbyte64(s,i)<<18)|(_getbyte64(s,i+1)<<12)|(_getbyte64(s,i+2)<<6);x.push(String.fromCharCode(b10>>16,(b10>>8)&255));break;case 2:b10=(_getbyte64(s,i)<<18)|(_getbyte64(s,i+1)<<12);x.push(String.fromCharCode(b10>>16));break}return x.join("")}function _getbyte(s,i){var x=s.charCodeAt(i);if(x>255){throw"INVALID_CHARACTER_ERR: DOM Exception 5"}return x}function _encode(s){if(arguments.length!==1){throw"SyntaxError: exactly one argument required"}s=String(s);var i,b10,x=[],imax=s.length-s.length%3;if(s.length===0){return s}for(i=0;i<imax;i+=3){b10=(_getbyte(s,i)<<16)|(_getbyte(s,i+1)<<8)|_getbyte(s,i+2);x.push(_ALPHA.charAt(b10>>18));x.push(_ALPHA.charAt((b10>>12)&63));x.push(_ALPHA.charAt((b10>>6)&63));x.push(_ALPHA.charAt(b10&63))}switch(s.length-imax){case 1:b10=_getbyte(s,i)<<16;x.push(_ALPHA.charAt(b10>>18)+_ALPHA.charAt((b10>>12)&63)+_PADCHAR+_PADCHAR);break;case 2:b10=(_getbyte(s,i)<<16)|(_getbyte(s,i+1)<<8);x.push(_ALPHA.charAt(b10>>18)+_ALPHA.charAt((b10>>12)&63)+_ALPHA.charAt((b10>>6)&63)+_PADCHAR);break}return x.join("")}return{decode:_decode,encode:_encode,VERSION:_VERSION}}(jQuery));
      
        function fnExcelReport()
          {
              var tab_text="<table id='download_table' border='2px'><tr bgcolor='#87AFC6'>";
              var textRange; var j=0;
              tab = document.getElementById('dt-detalles'); // id of table

              for(j = 0 ; j < tab.rows.length-1 ; j++) 
              {     
                  tab_text=tab_text+tab.rows[j].innerHTML+"</tr>";
              }

              tab_text=tab_text+"</table>";
              tab_text= tab_text.replace(/<A[^>]*>|<\/A>/g, "");//remove if u want links in your table
              tab_text= tab_text.replace(/<img[^>]*>/gi,""); // remove if u want images in your table
              tab_text= tab_text.replace(/<input[^>]*>|<\/input>/gi, ""); // reomves input params
            
              $("#inner_download").html(tab_text);
            
              $("#download_table tr").each(function() {
                  console.log($(this).find("td:eq(0),th:eq(0)").remove());
                  console.log($(this).find("td:eq(0),th:eq(0)").remove());
                  console.log($(this).find("td:eq(0),th:eq(0)").remove());
                  console.log($(this).find("td:eq(-1),th:eq(-1)").remove());
              });
            
              tab_text = $("#inner_download").html();
            
              //tab_text = decodeURIComponent(escape(tab_text));
            
              $("#inner_download").html('');
              
              var filename = "pedidos_"+fecha()+ ".xls";
              
            
              //tableToExcel(tab_text,'Rastreo',filename);
            
              uri = 'data:application/vnd.ms-excel;base64,' + $.base64.encode(tab_text);
              console.log(uri);
              var link = document.createElement("a");    
              link.href = uri;
              link.style = "visibility:hidden";
              link.download = filename;
              document.body.appendChild(link);
              link.click();
              document.body.removeChild(link);
              
              //return (sa);
          }
      
          /*function tableToExcel(html, name, filename)
          { 
              let uri = 'data:application/vnd.ms-excel;base64,', 
              template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><title></title><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>{worksheet}</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--><meta http-equiv="content-type" content="text/plain; charset=UTF-8"/></head><body>{table}</body></html>', 
              base64 = function(s) { return window.btoa(decodeURIComponent(encodeURIComponent(s))) },   format = function(s, c) { return s.replace(/{(\w+)}/g, function(m, p) { return c[p]; })} 
              //if (!table.nodeType) table = document.getElementById(table) 
              //var ctx = {worksheet: name || 'Worksheet', table: table.innerHTML} 
              var ctx = {worksheet: name || 'Worksheet', table: html} 
              var link = document.createElement('a'); 
              link.download = filename; 
              link.href = uri + $.base64.encode(format(template, ctx)); 
              link.click();
              document.body.removeChild(link);
          }*/

      
      
      function filtralo() {

            if ($("#almacen").val() === '') return false;

            if ($("#status").val() == 'A') {
                $('#btn-asignar').show();
                $('#planificalo').show();
                $('#planificaloTodo').show();
                $('#planificaloTodo').show();
                $('#btn-planificar').show();
                $("#col-asignar, #col-planificar").show();
                $(".column-asignar, .column-planificar").show();
                $("#chb_asignar").show();
                $("#chb_planificar").show();
            }
            else {
                $('#btn-asignar').hide();
                $('#planificalo').hide();
                $('#planificaloTodo').hide();
                $('#planificaloTodo').hide();
                $('#btn-planificar').hide();
                $("#col-asignar, #col-planificar").hide();
                $(".column-asignar, .column-planificar").hide();
                $("#chb_asignar").hide();
                $("#chb_planificar").hide();
            }

            $.ajax({
                url: '/api/v2/crossdock',
                type: 'GET',
                data: {
                    search: $("#criteriob").val(),
                    criterio: $("#criteriob").val(),
                    fecha_inicio: $("#fechai").val(),
                    fecha_fin: $("#fechaf").val(),
                    status: $("#status").val(),
                    almacen: $("#almacen").val(),
                    page: $('#dt-detalles .page').val(),
                    rows: $('#dt-detalles .count').val(),
                    filtro: $("#filtro").val(),
                    facturaInicio: $("#factura_inicio").val(),
                    facturaFin: $("#factura_final").val()
                },
                datatype: 'json'
            }).done(function(data) {
                if (data.length < 2) return;   //lilo

                if (data.status == 200) { 
                        $('#dt-detalles .total_pages').text(data.total_pages);
                        $('#dt-detalles .page').text(data.page);
                        $('#dt-detalles .from').text(data.from);
                        $('#dt-detalles .to').text(data.to);
                        $('#dt-detalles .total').text(data.total);

                        var row = '', i = 0;
                        $.each(data.data, function(index, item){
                            i++;
                            row += '<tr>'+
                                        '<td align="center">'+imageFormat(item.orden,item.surtido)+'</td>'+
                                        //'<td align="center"><input type="checkbox" /></td>'+
                                        '<td align="center" class="column-asignar" data-id="'+item.orden+'"><input type="checkbox" data-id="'+item.orden+'"/></td>'+
                                        '<td align="center" class="column-planificar" data-id="'+item.orden+'"><input type="checkbox" data-id="'+item.orden+'"/></td>'+
                                        
                                        '<td>'+item.orden+'--</td>'+
                                        '<td>'+item.orden_cliente+'</td>'+
                                        '<td align="center">'+item.fecha_pedido+'</td>'+
                                        '<td>'+item.prioridad+'</td>'+
                                        '<td>'+item.status+'</td>'+
                                        '<td>'+item.cliente+'</td>'+
                                        '<td>'+item.direccion+'</td>'+                                            
                                        '<td align="center">'+item.dane+'</td>'+
                                        '<td>'+item.ciudad+'</td>'+
                                        '<td>'+item.estado+'</td>'+
                                        '<td align="right">'+item.cantidad+'</td>'+
                                        '<td align="right">'+item.volumen+'</td>'+
                                        '<td align="right">'+item.peso+'</td>'+
                                        '<td align="center">'+item.fecha_ini+'</td>'+
                                        '<td align="center">'+item.fecha_fi+'</td>'+
                                        '<td align="center">'+item.TiempoSurtido+'</td>'+
                                        '<td>'+item.asignado+'</td>'+
                                        '<td align="center">'+item.surtido+'</td>'+
                                    '</tr>';                            
                        });
                        $('#dt-detalles tbody').html(row);
                        
                        if ($("#status").val() == 'A') {
                            $('#btn-asignar').show();
                            $('#planificalo').show();
                            $('#planificaloTodo').show();
                            $('#planificaloTodo').show();
                            $('#btn-planificar').show();
                            $("#col-asignar, #col-planificar").show();
                            $(".column-asignar, .column-planificar").show();
                        }
                        else {
                            $('#btn-asignar').hide();
                            $('#planificalo').hide();
                            $('#planificaloTodo').hide();
                            $('#planificaloTodo').hide();
                            $('#btn-planificar').hide();
                            $("#col-asignar, #col-planificar").hide();
                            $(".column-asignar, .column-planificar").hide();
                        }
                        
                    } else {
                        swal({
                            title: "Error",
                            text: "No se pudo planificar el inventario",
                            type: "error"
                        });
                    }
                
                $("#totalpeso").val(data.pesototal);
                $("#totalpedidos").val(data.totalpedidos);
                $("#volumentotal").val(data.volumentotal);

                if ($("#status").val() == 'S') {
                    $("#grid-table").showCol("surtido");
                    $("#grid-table").showCol("fecha_surtido");
                    $("#grid-table").showCol("asignado");
                } else {
                    $("#grid-table").hideCol("surtido");
                    $("#grid-table").hideCol("fecha_surtido");
                    $("#grid-table").hideCol("asignado");
                }

            });
        }
      
        /*function filtralo(){return 0}*/




        function ReloadGrid1() {
            $('#grid-table2').jqGrid('clearGridData')
                .jqGrid('setGridParam', {
                    postData: {
                        //           criterio: $("#txtCriterio1").val(),
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


        /**
         */
        function viewPdf(url) {
            var win = window.open(url, '_blank');
            win.focus();
        }


        /**
         * Crea un consolidado con lo items seleccionados del Grid Principal
         * @author Brayan Rincon <brayan262@gmail.com>
         * @return void
         */
        function crearConsolidadoDeOla(folios) 
        {
          var folios = [];
          
          $.each( $('.column-planificar'), function (index, item) {
            var i = $(item).children().first();
            if ( i.prop('checked') == true ) {
                folios.push(i.data('id'));
            }
          });

          $('#btn-crear-consolidado-ola').prop('disabled', true);
          $('#btn-cancelar-consolidado-ola').prop('disabled', true);
          var dfecha = $('#txt-ola-fechaentrega').val().split("-");
          var fecha = dfecha[2]+"-"+dfecha[1]+"-"+dfecha[0];

          $.ajax({
            url: '/api/v2/pedidos/crearConsolidadoDeOla',
            data: {
                folios: folios,
                fecha_entrega: fecha,
                action: 'crearConsolidadoDeOla'
            },
            type: 'POST',
            datatype: 'json'
          })
          .done(function(data) {
            $('#btn-crear-consolidado-ola').prop('disabled', false);
            $('#btn-cancelar-consolidado-ola').prop('disabled', false);

            $('#div-grid-consolidados').show();
            $('#div-grid-surtidos').show();

            $('#btn-cancelar-consolidado-ola').hide();
            $('#btn-crear-consolidado-ola').hide();
            $('#btn-borrar-consolidado-ola').show();
            $('#btn-asignar-consolidado-ola').show();
            $('#txt-ola-fechaentrega').prop('disabled', true);

            $('#txt-nro-ola').val(data.data.nro_ola);
            $("#txt-total-ordenes-ola").val(folios.length);
            $("#txt-peso-ola").val(data.data.peso);
            $("#txt-volumen-ola").val(data.data.volumen);

            cargarConsolidado(folios);
            cargarSurtido(folios);
          });
        }


        /**
         * Borra un consolidado de Ola
         * @author Brayan Rincon <brayan262@gmail.com>
         * @return void
         */
        function borrarConsolidadoDeOla() {

            swal({
                title: "Borrar Consolidado de Ola",
                text: "¿Desea borrar la operación de Ola?, esta operación no se puede reversar",
                type: "info",

                cancelButtonText: "No",
                cancelButtonColor: "#14960a",
                showCancelButton: true,

                confirmButtonColor: "#55b9dd",
                confirmButtonText: "Si",
                closeOnConfirm: true
            }, function() {

                $('#btn-borrar-consolidado-ola').prop('disabled', true);
                $('#btn-asignar-consolidado-ola').prop('disabled', true);

                $.ajax({
                        url: '/api/v2/consolidadosOla',
                        data: {
                            consololidado: $('#txt-nro-ola').val()
                        },
                        type: 'DELETE',
                        datatype: 'json'
                    })
                    .done(function(data) {
                        $('#btn-borrar-consolidado-ola').prop('disabled', false);
                        $('#btn-asignar-consolidado-ola').prop('disabled', false)
                        cancelarCallback();
                        ReloadGrid();
                    });
            });
        }


        /**
         * 
         */
        function cargarConsolidado(folio) {
            $('#grid-consolidados').jqGrid('clearGridData')
                .jqGrid('setGridParam', {
                    postData: {
                        folio: folio,
                        action: 'cargarConsolidado'
                    },
                    datatype: 'json',
                    page: 1
                })
                .trigger('reloadGrid', [{
                    current: true
                }]);
        }


        /**
         * 
         */
        function cargarSurtido(folio) {
            $('#grid-surtidos').jqGrid('clearGridData')
                .jqGrid('setGridParam', {
                    postData: {
                        folio: folio,
                        action: 'cargarSurtido'
                    },
                    datatype: 'json',
                    page: 1
                })
                .trigger('reloadGrid', [{
                    current: true
                }]);
        }

        $modal0 = null;
    </script>

    <script type="text/javascript">
        function cancelar() {

            swal({
                title: "Cancelar operación",
                text: "¿Desea cancelar la operación de crear Ola?",
                type: "info",

                cancelButtonText: "No",
                cancelButtonColor: "#14960a",
                showCancelButton: true,

                confirmButtonColor: "#55b9dd",
                confirmButtonText: "Si",
                closeOnConfirm: true
            }, function() {
                cancelarCallback();
            });
        }

        function cancelarCallback() {
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
            $('#planificarOla').hide();
            $('#list').removeAttr('class').attr('class', '');
            $('#list').addClass('animated');
            $('#list').addClass("fadeInRight");
            $('#list').addClass("wrapper");
            $('#list').addClass("wrapper-content");

            $('#btn-crear-consolidado-ola').show();
            $('#btn-borrar-consolidado-ola').hide();
            $('#btn-asignar-consolidado-ola').hide();
            $('#btn-cancelar-consolidado-ola').show();

            $('#txt-ola-fechaentrega').prop('disabled', false)

            $('#div-grid-consolidados').hide();
            $('#div-grid-surtidos').hide();
        }


        /**
         * Asignar un Usuario a un consolidado de Ola
         * @author Brayan Rincon <brayan262@gmail.com>
         * @return void
         */
        function asignarUsuariosOla() {
            var rows = $("#grid-consolidados").jqGrid('getRowData');
            var bandera = false;

            for (var i = 0; i < rows.length; i++) {
                var row = rows[i];
                if (row['seleccion'] == "Yes") {
                    bandera = true;
                }
            }

            if (bandera == true) {

                $(".itemlist").remove();

            } else {
                swal({
                    title: "¡Advertencia!",
                    text: "Debe seleccionar al menos un artículo de la lista de consolidado",
                    type: "warning"
                });
            }
        }


        /**
         */
        function asignar()
        {
            var folios = [];
            var asignados = [];
            var almacen = $("#almacen").val();
          
            $.each( $('.column-asignar'), function (index, item) 
            {
                var i = $(item).children().first();
                if (i.prop('checked') == true) 
                {
                    asignados.push(i.data('id'));
                }
            });
            $.each( $('.column-planificar'), function (index, item) 
            {
                var i = $(item).children().first();
                if (i.prop('checked') == true) 
                {
                    folios.push(i.data('id'));
                }
            });
          
            if(almacen == "")
            {
                swal({title: "Error",text: "Seleccione el almacén",type: "error"});
                return;
            }
          
            $('#txt-cantidad-pedidos').text(asignados.length);
          
            claves = "";
            for (var i = 0; i < asignados.length; i++)
            {
                $.ajax({
                    type: "POST",
                    dataType: "json",
                    url: '/api/crossdock/update/index.php',
                    data: {
                        action: "loadArticulos",
                        id_pedido: asignados[i],
                        almacen:almacen,
                        status: 'A'
                    },
                    beforeSend: function(x) {if (x && x.overrideMimeType) {x.overrideMimeType("application/json;charset=UTF-8");}},
                    success: function(data) 
                    {
                        if (data.success == true) 
                        {
                            $.ajax({
                                type: "POST",
                                dataType: "json",
                                url: '/api/crossdock/update/index.php',
                                data: {
                                    action: "traerUsuarios",
                                    acticulos: data.articulos
                                },
                                beforeSend: function(x) {if (x && x.overrideMimeType) {x.overrideMimeType("application/json;charset=UTF-8");}},
                                success: function(data) 
                                {
                                    if (data.success == true) 
                                    {
                                        console.log(data);
                                        $('#usuario_selector').empty();
                                        $modal0 = $("#modal-asignar-usuario");
                                        $modal0.modal('show');
                                        $("#usuario_selector").append(new Option("Seleccione un Usuario", ""));
                                        var j = 0;
                                        $.each(data.usuarios.clave, function(key, value) {
                                            $("#usuario_selector").append(new Option("("+data.usuarios.clave[j]+") - "+data.usuarios.nombre[j], data.usuarios.clave[j]));
                                            j++;
                                        });
                                    }
                                }
                            });
                        }
                    }
                });
            }
        }


        /**
         * Undocumented function
         *
         * @return void
         */
        function planificarConsolidadoOla() {

            var rels = [];
            var localRels = [];

            if ($("#usuario-ola").val() == "") {

            }

            var date = new Date();
            var dia = date.getDate();
            var mes = date.getMonth();
            var anio = date.getFullYear();
            var hora = date.getHours();
            var min = date.getMinutes();
            var seg = date.getSeconds();
            if (dia < 10) {
                dia = "0" + dia;
            }
            if (mes < 10) {
                mes = "0" + mes;
            }
            var fechaHoy = anio + "-" + mes + "-" + dia + " " + hora + ":" + min + ":" + seg;

            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    numero_ola: $('#txt-nro-ola').val(),
                    fec_asignacion: $('#p_fechaAsignacion').val(),
                    fec_entrega: $('#txt-ola-fechaentrega').val(),
                    id_ruta: $('#ruta').val(),
                    pedidos: $('#hiddenPedidos').val(),
                    ids: $('#hiddenID_Pedidos').val(),
                    fecha: fechaHoy,
                    usuarios: rels,
                    action: "planificarOla"
                },
                beforeSend: function(x) {
                    if (x && x.overrideMimeType) {
                        x.overrideMimeType("application/json;charset=UTF-8");
                    }
                },
                url: '/api/crossdock/update/index.php',
                success: function(data) {
                    if (data.success == true) {
                        var rows = jQuery("#grid-consolidados").jqGrid('getRowData');
                        for (var i = 0; i < rows.length; i++) {
                            var row = rows[i];
                            if (row['seleccion'] == "Yes") {
                                $.ajax({
                                    type: "POST",
                                    dataType: "json",
                                    data: {
                                        pedidos: $('#hiddenPedidos').val(),
                                        ids: $('#hiddenID_Pedidos').val(),
                                        articulo: row['clave'],
                                        pedidas: row['pedidas'],
                                        surtidas: row['surtidas'],
                                        action: "planificarSubPedido"
                                    },
                                    beforeSend: function(x) {
                                        if (x && x.overrideMimeType) {
                                            x.overrideMimeType("application/json;charset=UTF-8");
                                        }
                                    },
                                    url: '/api/crossdock/update/index.php',
                                    success: function(data) {
                                        if (data.success == true) {
                                            console.log("Agrego");
                                        }
                                    }
                                });
                            }

                        }
                        swal({
                            title: "Ola " + $('#txt-nro-ola').val() + " Planificada",
                            text: "Los pedidos han sido asignados a la misma ola",
                            type: "success"
                        });
                        ReloadGrid();
                        $("#grid-table").jqGrid("clearGridData");
                        //	$('#waveModal').modal().hide();
                        $(".close").click();
                        cancelar();
                    }
                }
            });


        }


        /**
         * Undocumented function
         *
         * @return void
         */
        function planificar() {
          var grid = $('#dt-detalles'),
              folios = [],
              pedidosId = [],
              rowId = grid.jqGrid("getDataIDs"),
              rowCount = rowId.length;

          $.each( $('.column-planificar'), function (index, item) {
                var i = $(item).children().first();
                if ( i.prop('checked') == true ) {
                    folios.push(i.data('id'));
                }
            });
            console.log(folios);
            console.log(folios.length);
          if(folios.length > 1){
            $('#list').hide();
            $('#hiddenPedidos').val(folios);
            $('#hiddenID_Pedidos').val(pedidosId);
            $('#planificarOla').removeAttr('class').attr('class', '');
            $('#planificarOla').addClass('animated');
            $('#planificarOla').addClass("fadeInRight");
            $('#planificarOla').addClass("wrapper");
            $('#planificarOla').addClass("wrapper-content");
            $('#planificarOla').show();

            $.ajax({
              url: '/api/crossdock/lista/index.php',
              data: {
                folios: folios,
                action: 'obtenerPesoVolumen'
              },
              type: 'POST',
              datatype: 'json'
            })
            .done(function(data) {
              var data = JSON.parse(data);
              console.log(data);
              $("#txt-total-ordenes-ola").val(folios.length);
              $("#txt-peso-ola").val(data.pesototal);
              $("#txt-volumen-ola").val(data.volumentotal);
              $("#txt-ola-fechaentrega").val(data.fecha_entrega);
              $("#txt-nro-ola").val(data.folio);
            });

            $("#pdfConsolidado input[name='folio'], #excelConsolidado input[name='folio'], #pdfSurtido input[name='folio'], #excelSurtido input[name='folio']").each(function(i, v) {
                v.value = folios
                console.log('Valores del formulario', v.value);
            });
          }
          else{
             swal({
                  title: "Error",
                  text: "Debe seleccionar al menos dos folios",
                  type: "error"
              });
              return false;
          }
        }

        /**
         *
         */
        function asiganarAreaEmbarqueOla() {

            $("#areaemb")[0].style.display = 'none';
            $("#waveModal").modal('hide');
            $("#modal-area-embarque").modal('show');
            $('#grid-areas-embarque').jqGrid('clearGridData');

            var rows = $("#grid-table").jqGrid('getRowData');

            for (var i = 0; i < rows.length; i++) {
                var row = rows[i];
                if (row['planear'] == "Yes") {
                    $("#grid-areas-embarque").jqGrid('addRowData', i, row);
                }
            }
        }


        function asignarPedido()
        {
          var asignados = [];
          $('#modal-asignar-usuario').modal("hide");
          $.each( $('.column-asignar'), function (index, item) {
            var i = $(item).children().first();
            if ( i.prop('checked') == true ) 
            {
              asignados.push(i.data('id'));
            }
          });
          if(asignados.length == 0)
          {
            swal({title: "Folios",text: "Por favor seleccione uno o mas pedidos",type: "error"});
            return;
          }
          if ($('#usuario').val() == "") 
          {
            swal({title: "Solo puede asignar un usuario",text: "Por favor seleccione un unico usuario para asignar el pedido",type: "error"});
            return;
          }
          if ($("#usuario_selector").val() == "") 
          {
            swal({title: "Seleccione un usuario",text: "Por favor seleccione un unico usuario para asignar el pedido",type: "error"});
            return;
          }
          console.log("folios",asignados);
          var avance = 0;
          var total = asignados.length;
          var sinExistencia = "";
          console.log(total);
          /*
          $('#list').hide();
          $('#asignacionPedidos').removeAttr('class').attr('class', '');
          $('#asignacionPedidos').addClass('animated');
          $('#asignacionPedidos').addClass("fadeInRight");
          $('#asignacionPedidos').addClass("wrapper");
          $('#asignacionPedidos').addClass("wrapper-content");
          $("#asignacionPedidos").show();
          */
          $("#avance").html(avance);
          $("#total").html(total);

          asignados.forEach(function(value, key) {
            console.log(value);
            $.ajax({
              type: "POST",
              dataType: "json",
              async: false,
              data: {
                usuarios: $('#usuario_selector').val(),
                pedidos: value,
                almacen: $("#almacen").val(),
                action: "asignar",
                hora_inicio: moment().format("YYYY-MM-DD HH:mm:ss"),
                fecha: moment().format("YYYY-MM-DD")
              },
              beforeSend: function(x) {
                if (x && x.overrideMimeType) {
                  x.overrideMimeType("application/json;charset=UTF-8");
                }
              },
              url: '/api/crossdock/update/index.php',
              success: function(data) {
                if (data.success == true) {
                  if(data.sin_existencia == "")
                  {
                    avance +=1;
                    if(avance == 1)
                    {
                      swal({
                        title: "Asignando...",
                        text: "Se estan asignado los pedidos...",
                        type: "warning"
                      });
                      //alert("Asignando...");  //edg
                    }
                    if(avance == total)
                    {
                      swal({
                        title: "Asignados",
                        text: "Asignados "+avance+" de "+total,
                        type: "success"
                      });
                      //alert("Asignados "+avance+" de "+total);  
                    }
                  }
                  else
                  {
                    sinExistencia+=data.sin_existencia;
                  }
                }
              }
            });
          });


          if(avance == total)
          {
          $("#asignacionPedidos").hide();
          $('#list').show();
          ReloadGrid();
          $(".close").click();

          if(sinExistencia != "")
          {
          swal({
          title: "Error",
          text: "Los siguientes folios no cuentan con existencia: \n "+sinExistencia,
          type: "warning"
          });
          }
          else
          {

          }
          }
        }

        function editar_cajas()
        {
          var folio = $("#fol_cambio").val();
          var numero = $("#cajasCantidad").val();
          $.ajax({
                url: '/api/crossdock/update/index.php',
                data: {
                    action: 'changeNumberBox',
                    folio: folio,
                    numero: numero
                },
                dataType: 'json',
                method: 'POST'
            }).done(function(data) {
                if (data.success) {
                    console.log('success');
                    $("#coModal").modal('hide');
                  swal({
                            title: "Éxito",
                            text: "Se ha actualizado el número de cajas correctamente",
                            type: "success"
                        }, function(confirm) {
                        });
                } else {
                    //alert("Error");
                    console.log('error');
                    $("#coModal").modal('hide');
                  swal({
                            title: "Error",
                            text: data.error,
                            type: "error"
                        }, function(confirm) {
                        });
                }
            });
        }        
      
        function articulos(_codigo) 
        {
          //EDG117
          console.log("articulos",_codigo)
          if(_codigo == ""){_codigo = folio;}
          $("#folio_detalle").html(_codigo);
          $('#btnRealizarSurtido').hide();
          
          /* Verifica Si el pedio esta Surtiendose*/
          var pedido_status_surtiendo = false;
          window.estado_del_pedido = "";
          $.ajax({
            type: "POST",
            dataType: "json",
            url: '/api/crossdock/update/index.php',
            data: {
              action: 'verificarSiElPedidoEstaSurtiendose',
              folio: _codigo
            },
            beforeSend: function(x) {if (x && x.overrideMimeType) {x.overrideMimeType("application/json;charset=UTF-8");}},
            success: function(data) 
            {
              console.log(data.status);
              window.estado_del_pedido = data.status;
              console.log("HERE",window.estado_del_pedido);
              if (data.status == 'A') 
              {
                $('#btnRealizarSurtido').hide();
              } 
              if(data.status == 'S' || data.status == 'C' || data.status == 'E' || data.status == 'F' || data.status == 'I' || data.status == 'K' || data.status == 'L'|| data.status == 'M'|| data.status == 'P'|| data.status == 'R'|| data.status == 'T') 
              {
                $('#btnRealizarSurtido').show();
                pedido_status_surtiendo = true;
              }
              articulos_detalles();
            }
          });
          
          /* Trae los detalle de la caja */
          $.ajax({
            url: '/api/crossdock/update/index.php',
            data: {
              action: 'loadCajaDetalle',
              folio: _codigo
            },
            dataType: 'json',
            method: 'POST'
          }).done(function(data) 
          {
            var detalles_productos = data.detalles_productos,
            detalles_cajas = data.detalles_cajas;
            if (data.success) 
            {
              var peso = parseFloat(detalles_productos.peso) + parseFloat(detalles_cajas.peso);
              $("#total_volumen_detalle").html(detalles_productos.volumen + " m&sup3;");
              $("#total_peso_detalle").html(peso + " Kg");
              $("#tipo_caja_detalle").html(detalles_cajas.descripcion);
              $("#dimensiones_caja_detalle").html(detalles_cajas.dimensiones);
              $("#peso_caja_detalle").html(detalles_cajas.peso+ " Kg");
              $("#volumen_caja_detalle").html(detalles_cajas.volumen + " m&sup3;");
              $("#cantidad_caja_detalle").html('<input type="text" id="cajasCantidad" name="fname" value="'+detalles_cajas.cantidad+'" size="4">');
              $("#fol_cambio").val(data.info.data.folio);
            } 
            else
            {
              swal("Error", data.error, "error");
              $("#total_volumen_detalle").html(detalles_productos.volumen + " m&sup3;");
              $("#total_peso_detalle").html(detalles_productos.peso + " Kg");
              $("#tipo_caja_detalle").html('0');
              $("#dimensiones_caja_detalle").html('0');
              $("#peso_caja_detalle").html('0');
              $("#volumen_caja_detalle").html('0');
              $("#cantidad_caja_detalle").html('<input type="text" id="cajasCantidad" name="fname" value="0" size="4">');
            }
          });
          
          /* Cargar Datos del Pedido en Cabecera */
          $.ajax({
            type: "POST",
            dataType: "json",
            url: '/api/crossdock/update/index.php',
            data: {
              action: "detallesPedidoCabecera",
              id_pedido: _codigo,
              almacen:$("#almacen").val()
            },
            beforeSend: function(x) {if (x && x.overrideMimeType) {x.overrideMimeType("application/json;charset=UTF-8");}},
            success: function(data) 
            {
              
              if(data.success == true)
              {
                var productos_existentes = 0;
                var backorder = 0;
                if(parseInt(data.articulos_existentes) > parseInt(data.articulos_pedidos))
                {
                  console.log("cabecera_mayor",data);
                  productos_existentes = data.articulos_pedidos;
                }
                else
                {
                  console.log("cabecera_menor",data);
                  productos_existentes = data.articulos_existentes
                  backorder = data.articulos_pedidos - productos_existentes;
                }
                
                $("#total_productos_detalle").html(data.articulos_pedidos);
                $("#total_disponibles_detalle").html(productos_existentes);
                $("#backorder").html(backorder);
              }
            }
          });
          
          function articulos_detalles()
          {
            /* Carga los Detalles de articulos */
            console.log("HERE2",window.estado_del_pedido);
            $.ajax({
              type: "POST",
              dataType: "json",
              url: '/api/crossdock/update/index.php',
              data: {
                action: "loadArticulos",
                id_pedido: _codigo,
                almacen:$("#almacen").val(),
                status: window.estado_del_pedido,
              },
              beforeSend: function(x) {if (x && x.overrideMimeType) {x.overrideMimeType("application/json;charset=UTF-8");}},
              success: function(data) 
              {
                if (data.success == true) 
                {
                  if(pedido_status_surtiendo == false)
                  {
                    $("#titulo3").show();
                    $("#tabla_listo_por_asignar").show();

                    $("#titulo1").hide();
                    $("#tabla_surtiendo").hide();
                    $("#br_titulo").hide();

                    $("#titulo2").hide();
                    $("#tabla_surtido1").hide();

                    window.detalle_pedido = data;
                    $("#grid-table-listo-por-asignar").jqGrid("clearGridData");
                    var folio = _codigo;
                    var back_order = 0;
                    var back_order_total = 0;
                    var disponible_total_header = 0 ;
                    var pedido_total = 0;
                    var disponible_total = 0;
                    for(var i = 0; i < data.articulos.length; i++) 
                    {
                      if(data.articulos[i].Existencia_Total==null){data.articulos[i].Existencia_Total=0;}
                      if(parseInt(data.articulos[i].Existencia_Total) > parseInt(data.articulos[i].Pedido_Total) || parseInt(data.articulos[i].Existencia_Total) == parseInt(data.articulos[i].Pedido_Total))
                      {
                        data.articulos[i].Existencia_Total = data.articulos[i].Pedido_Total;
                      }
                      var back_order_asignar_articulo = (data.articulos[i].Pedido_Total-data.articulos[i].Existencia_Total);
                      var disponible_total_articulo = data.articulos[i].Existencia_Total;
                      var cantidad_pedida_articulo = data.articulos[i].Pedido_Total;

                      obj = {
                        folio: data.articulos[i].folio,
                        cliente: data.articulos[i].cliente,
                        clave: data.articulos[i].clave,
                        articulo: data.articulos[i].articulo,
                        volumen: data.articulos[i].volumen,
                        peso: data.articulos[i].peso,
                        cantidad_pedida: data.articulos[i].Pedido_Total,
                        existencia_total: data.articulos[i].Existencia_Total,
                        back_order_asignar: (data.articulos[i].Pedido_Total-data.articulos[i].Existencia_Total),
                      };
                      emptyItem = [obj];

                      $("#grid-table-listo-por-asignar").jqGrid('addRowData', 0, emptyItem);
                    }
                    $modal0 = $("#coModal");
                    $('#coModal').hide();
                    $modal0.modal('show');
                  }

                  if(pedido_status_surtiendo == true)
                  {
                    $("#titulo1").show();
                    $("#tabla_surtiendo").show();
                    $("#br_titulo").show();

                    $("#titulo2").show();
                    $("#tabla_surtido1").show();

                    $("#titulo3").hide();
                    $("#tabla_listo_por_asignar").hide();

                    window.detalle_pedido = data;

                    $("#grid-table2").jqGrid("clearGridData");
                    $("#grid-table-surtido1").jqGrid("clearGridData");

                    itemsArticulosPedidos = [];
                    folio = _codigo;
                    var back_order = 0;
                    var j = 0;
                    console.log("here3",data);
                    if(data.articulos.length > 0)
                    {
                      var surtidor_actual = data.articulos[0].surtidor;
                    
                    
                      for (var i = 0; i < data.articulos.length; i++) 
                      {
    //                     console.log(data.articulos);
    //                     return;
                        j++;
                        if(surtidor_actual == data.articulos[i].surtidor)
                        {
                          var ordenDeSecuencia_contador = j;
                        }
                        else
                        {
                          j = 1;
                          var ordenDeSecuencia_contador = j;
                          surtidor_actual = data.articulos[i].surtidor;
                        }
                        if(data.articulos[i].existencia==null){data.articulos[i].existencia=0;}
                        if(parseInt(data.articulos[i].existencia) > parseInt(data.articulos[i].pedidas) || parseInt(data.articulos[i].existencia) == parseInt(data.articulos[i].pedidas))
                        {
                          data.articulos[i].existencia = data.articulos[i].pedidas;
                        }

                        var back_order_surtir_articulo = (data.articulos[i].pedidas - data.articulos[i].existencia);
                        var disponible_total_surtir = data.articulos[i].existencia;
                        var cantidad_pedida_surtir = data.articulos[i].pedidas;

                        obj = {
                          folio: data.articulos[i].folio,
                          cliente: data.articulos[i].cliente,
                          clave: data.articulos[i].clave,
                          articulo: data.articulos[i].articulo,
                          volumen: data.articulos[i].volumen,
                          peso: data.articulos[i].peso,
                          solicitadas: data.articulos[i].pedidas,
                          surtidas: data.articulos[i].surtidas,
                          existencia: data.articulos[i].existencia,
                          back_order: (data.articulos[i].pedidas - data.articulos[i].existencia),
                          ubicacion: data.articulos[i].ubicacion,
                          ruta: data.articulos[i].ruta,
                          secuencia:data.articulos[i].secuencia,
                          ordenDeSecuencia: ordenDeSecuencia_contador,
                          surtidor: data.articulos[i].surtidor,
                        };
                        emptyItem = [obj];

                        $("#grid-table2").jqGrid('addRowData', 0, emptyItem);
                      }
                  }
                    
                  if(data.articulos_surtidos.length > 0)
                  {
                    for (var i = 0; i < data.articulos_surtidos.length; i++) 
                    {
                        if(data.articulos_surtidos[i].existencia==null){data.articulos_surtidos[i].existencia=0;}
                        if(parseInt(data.articulos_surtidos[i].existencia) > parseInt(data.articulos_surtidos[i].pedidas) || parseInt(data.articulos_surtidos[i].existencia) == parseInt(data.articulos_surtidos[i].pedidas))
                        {
                          data.articulos_surtidos[i].existencia = data.articulos_surtidos[i].pedidas;
                        }

                        tabla2 = {
                          folio: data.articulos_surtidos[i].folio,
                          cliente: data.articulos_surtidos[i].cliente,
                          clave: data.articulos_surtidos[i].clave,
                          articulo: data.articulos_surtidos[i].articulo,
                          volumen: data.articulos_surtidos[i].volumen,
                          peso: data.articulos_surtidos[i].peso,
                          solicitadas: data.articulos_surtidos[i].pedidas,
                          surtidas: data.articulos_surtidos[i].surtidas,
                          existencia: data.articulos_surtidos[i].existencia,
                          back_order: (data.articulos_surtidos[i].pedidas - data.articulos_surtidos[i].existencia),
                          ordenDeSecuencia: ordenDeSecuencia_contador

                        };
                        empty_tabla2 = [tabla2];
                        $("#grid-table-surtido1").jqGrid('addRowData', 0, empty_tabla2);
                      }
                    }
                    $modal0 = $("#coModal");
                    $('#coModal').hide();
                    $modal0.modal('show');
                  }
                }
              }
            });
          }
        }
      
//////////////////////FOTOS////////////////////////
        function embarqueFoto(_codigo)
        {
          $("#foto1,#foto2,#foto3,#foto4").attr("src","");
          $(".idPedido").val(_codigo);
          console.log("Fotos ajax");
          $.ajax({
            type: "POST",
            dataType: "json",
            data: {
              id_pedido: _codigo,
              action: "loadFotos"
            },
            url: '/api/crossdock/update/index.php',
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

      
        function traeModal() {

            $('#myModalClientes').show();

        }

        function minimizar() {

            $('#myModalClientes').hide();

        }
    </script>

    <!-- grid de articulos -->
    <script type="text/javascript">
/////////////////////////////////////Aqui se contruye el Grid de articulos/////////////////////////////////////////
        $(function($) {
            var grid_selector = "#grid-table2";
            var pager_selector = "#grid-pager2";

            //resize to fit page size
            $(window).on('resize.jqGrid', function() {
                $("#grid-table2").jqGrid('setGridWidth', $("#coModal").width() - 50);
            })
                //resize on sidebar collapse/expand
            var parent_column = $(grid_selector).closest('[class*="col-"]');
            $(document).on('settings.ace.jqGrid', function(ev, event_name, collapsed) {
                if (event_name === 'sidebar_collapsed' || event_name === 'main_container_fixed') {
                    //setTimeout is for webkit only to give time for DOM changes and then redraw!!!
                    setTimeout(function() {
                        $(grid_selector).jqGrid('setGridWidth', $(window).width() - 150);
                    }, 0);
                }
            })

            $(grid_selector).jqGrid({
                //url:'/api/inventariosfisicos/lista/index_detalle.php',
                datatype: "json",
                shrinkToFit: false,
                height: 'auto',
                mtype: 'POST',
                colNames: ['Folio', 'Cliente', 'Clave', 'Articulo', 'BL', 'Ruta','Secuencia', 'Orden de Surtido', 'Surtidor', 'Lote', 'Caducidad', 'Serie', 'Solicitadas', 'Disponible', 'Back Order','Surtidas', 'Volumen (m3)', 'Peso'],
                colModel: [
                    {name: 'folio',index: 'folio',width: 80,editable: false,sortable: false},
                    {name: 'cliente',index: 'cliente',width: 350,editable: false,sortable: false},
                    {name: 'clave',index: 'clave',width: 80,editable: false,sortable: false},
                    {name: 'articulo',index: 'articulo',width: 350,editable: false,sortable: false},
                    {name: 'ubicacion',index: 'ubicacion',width: 80,editable: false,sortable: false,align: 'center'},
                    {name: 'ruta',index: 'ruta',width: 80,editable: false,sortable: false,align: 'center'},
                    {name: 'secuencia',index: 'secuencia',width: 80,editable: false,sortable: false,align: 'center'},
                    {name: 'ordenDeSecuencia',index: 'ordenDeSecuencia',width: 120,editable: false,sortable: false,align: 'center'},
                    {name: 'surtidor',index: 'surtidor',width: 120,editable: false,sortable: false,align: 'center'},
                    {name: 'Lote',index: 'Lote',width: 80,editable: false,sortable: false,align: 'center'},
                    {name: 'Caducidad',index: 'Caducidad',width: 80,editable: false,sortable: false,align: 'center'},
                    {name: 'Serie',index: 'Serie',width: 80,editable: false,sortable: false,align: 'center'},
                    {name: 'solicitadas',index: 'solicitadas',width: 100,editable: false,sortable: false,align: 'right'},
                    {name: 'existencia',index: 'existencia',width: 80,editable: false,sortable: false,align: 'right'},
                    {name: 'back_order',index: 'back_order',width: 80,editable: false,sortable: false,align: 'right'},  
                    {name: 'surtidas',index: 'surtidas',width: 100,editable: false,sortable: false,align: 'right'},
                    {name: 'volumen',index: 'volumen',width: 100,editable: false,sortable: false,align: 'right'},
                    {name: 'peso',index: 'peso',width: 100,editable: false,sortable: false,align: 'right'},
                ],
                rowNum: 10,
                rowList: [10, 20, 30],
                pager: pager_selector,
                sortname: 'clave',
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
            function actionFormat(cellvalue, options, rowObject) {
                var serie = rowObject.folio + ' | ' + rowObject.clave,
                    html = '';
                html += '<a href="#" onclick="surtir(\'' + serie + '\')"><i class="fa fa-check" alt="Detalle"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
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
                            format: 'dd-mm-yyyy',
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
      
        $(function($) {
            var grid_selector = "#grid-table-surtido1";
            var pager_selector = "#grid-pager-surtido1";

            //resize to fit page size
            $(window).on('resize.jqGrid', function() {
                $("#grid-table-surtido1").jqGrid('setGridWidth', $("#coModal").width() - 50);
            })
                //resize on sidebar collapse/expand
            var parent_column = $(grid_selector).closest('[class*="col-"]');
            $(document).on('settings.ace.jqGrid', function(ev, event_name, collapsed) {
                if (event_name === 'sidebar_collapsed' || event_name === 'main_container_fixed') {
                    //setTimeout is for webkit only to give time for DOM changes and then redraw!!!
                    setTimeout(function() {
                        $(grid_selector).jqGrid('setGridWidth', $(window).width() - 150);
                    }, 0);
                }
            })

            $(grid_selector).jqGrid({
                //url:'/api/inventariosfisicos/lista/index_detalle.php',
                datatype: "json",
                shrinkToFit: false,
                height: 'auto',
                mtype: 'POST',
                colNames: ['Folio', 'Cliente', 'Clave', 'Articulo', 'Orden de Surtido', 'Solicitadas', 'Disponible', 'Back Order','Surtidas', 'Volumen (m3)', 'Peso'],
                colModel: [
                    {name: 'folio',index: 'folio',width: 80,editable: false,sortable: false},
                    {name: 'cliente',index: 'cliente',width: 350,editable: false,sortable: false},
                    {name: 'clave',index: 'clave',width: 80,editable: false,sortable: false},
                    {name: 'articulo',index: 'articulo',width: 350,editable: false,sortable: false},
                    {name: 'ordenDeSecuencia',index: 'ordenDeSecuencia',width: 120,editable: false,sortable: false,align: 'center'},
                    {name: 'solicitadas',index: 'solicitadas',width: 100,editable: false,sortable: false,align: 'right'},
                    {name: 'existencia',index: 'existencia',width: 80,editable: false,sortable: false,align: 'right'},
                    {name: 'back_order',index: 'back_order',width: 80,editable: false,sortable: false,align: 'right'},  
                    {name: 'surtidas',index: 'surtidas',width: 100,editable: false,sortable: false,align: 'right'},
                    {name: 'volumen',index: 'volumen',width: 100,editable: false,sortable: false,align: 'right'},
                    {name: 'peso',index: 'peso',width: 100,editable: false,sortable: false,align: 'right'},
                ],
                rowNum: 10,
                rowList: [10, 20, 30],
                pager: pager_selector,
                sortname: 'clave',
                viewrecords: true,
                sortorder: "desc"
            });

            // Setup buttons
            $("#grid-table-surtido1").jqGrid('navGrid', '#grid-pager-surtido1', {
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
            function actionFormat(cellvalue, options, rowObject) {
                var serie = rowObject.folio + ' | ' + rowObject.clave,
                    html = '';
                html += '<a href="#" onclick="surtir(\'' + serie + '\')"><i class="fa fa-check" alt="Detalle"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
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
                            format: 'dd-mm-yyyy',
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

        
/////////////////////////////////////Aqui se contruye el Grid de articulos/////////////////////////////////////////
        $(function($) {
            var grid_selector = "#grid-table-listo-por-asignar";
            var pager_selector = "#grid-pager-listo-por-asignar";

            //resize to fit page size
            $(window).on('resize.jqGrid', function() {
                $("#grid-table-listo-por-asignar").jqGrid('setGridWidth', $("#coModal").width() - 50);
            })
                //resize on sidebar collapse/expand
            var parent_column = $(grid_selector).closest('[class*="col-"]');
            $(document).on('settings.ace.jqGrid', function(ev, event_name, collapsed) {
                if (event_name === 'sidebar_collapsed' || event_name === 'main_container_fixed') {
                    //setTimeout is for webkit only to give time for DOM changes and then redraw!!!
                    setTimeout(function() {
                        $(grid_selector).jqGrid('setGridWidth', $(window).width() - 150);
                    }, 0);
                }
            })

            $(grid_selector).jqGrid({
                //url:'/api/inventariosfisicos/lista/index_detalle.php',
                datatype: "json",
                shrinkToFit: false,
                height: 'auto',
                mtype: 'POST',
                colNames: ['Folio', 'Cliente', 'Clave', 'Articulo', 'Cantidad Pedida', 'Disponible','BackOrder', 'Volumen (m3)', 'Peso'],
                colModel: [{
                        name: 'folio',
                        index: 'folio',
                        width: 80,
                        editable: false,
                        sortable: false
                    }, {
                        name: 'cliente',
                        index: 'cliente',
                        width: 350,
                        editable: false,
                        sortable: false
                    }, {
                        name: 'clave',
                        index: 'clave',
                        width: 80,
                        editable: false,
                        sortable: false
                    }, {
                        name: 'articulo',
                        index: 'articulo',
                        width: 350,
                        editable: false,
                        sortable: false
                    },{
                        name: 'cantidad_pedida',
                        index: 'cantidad_pedida',
                        width: 130,
                        editable: false,
                        sortable: false,
                        align: 'center'
                    },{
                        name: 'existencia_total',
                        index: 'existencia_total',
                        width: 130,
                        editable: false,
                        sortable: false,
                        align: 'center'
                    },{
                        name: 'back_order_asignar',
                        index: 'back_order_asignar',
                        width: 130,
                        editable: false,
                        sortable: false,
                        align: 'center'
                    },{
                        name: 'volumen',
                        index: 'volumen',
                        width: 100,
                        editable: false,
                        sortable: false,
                        align: 'right'
                    }, {
                        name: 'peso',
                        index: 'peso',
                        width: 100,
                        editable: false,
                        sortable: false,
                        align: 'right'
                    },

                    //{name:'myac',index:'', width:80, fixed:true, sortable:false, resize:false, formatter:actionFormat}
                ],
                rowNum: 10,
                rowList: [10, 20, 30],
                pager: pager_selector,
                sortname: 'clave',
                viewrecords: true,
                sortorder: "desc"
            });

            // Setup buttons
            $("#grid-table-listo-por-asignar").jqGrid('navGrid', '#grid-pager-listo-por-asignar', {
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
            function actionFormat(cellvalue, options, rowObject) {
                var serie = rowObject.folio + ' | ' + rowObject.clave,
                    html = '';
                html += '<a href="#" onclick="surtir(\'' + serie + '\')"><i class="fa fa-check" alt="Detalle"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
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
                            format: 'dd-mm-yyyy',
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
      
      
        function downloadxml(url) {
            var win = window.open(url, '_blank');
            win.focus();
        }

        function viewPdf(url) {
            var win = window.open(url, '_blank');
            win.focus();
        }

        $modal0 = null;
    </script>


    <!-- grid de articulos 2-->
    <script type="text/javascript">
        /////////////////////////////////////Aqui se contruye el Grid de articulos/////////////////////////////////////////
        $(function($) {
            var grid_selector = "#grid-consolidados";
            var pager_selector = "#grid-pager3";

            //resize to fit page size
            $(window).on('resize.jqGrid', function() {
                    $(grid_selector).jqGrid('setGridWidth', $(window).width() - 150);
                })
                //resize on sidebar collapse/expand
            var parent_column = $(grid_selector).closest('[class*="col-"]');
            $(document).on('settings.ace.jqGrid', function(ev, event_name, collapsed) {
                if (event_name === 'sidebar_collapsed' || event_name === 'main_container_fixed') {
                    //setTimeout is for webkit only to give time for DOM changes and then redraw!!!
                    setTimeout(function() {
                        $(grid_selector).jqGrid('setGridWidth', $(window).width() - 150);
                    }, 0);
                }
            })

            $(grid_selector).jqGrid({
                url: '/api/crossdock/update/index.php',
                datatype: "local",
                shrinkToFit: false,
                height: 'auto',
                mtype: 'POST',
                colNames: ['Clave', 'Articulo', 'Pedidas', 'Surtidas', 'Selección'],
                colModel: [{
                    name: 'clave',
                    index: 'clave',
                    width: 150,
                    editable: false,
                    sortable: false
                }, {
                    name: 'articulo',
                    index: 'articulo',
                    width: 250,
                    editable: false,
                    sortable: false
                }, {
                    name: 'pedidas',
                    index: 'pedidas',
                    width: 100,
                    editable: false,
                    sortable: false
                }, {
                    name: 'surtidas',
                    index: 'surtidas',
                    width: 100,
                    editable: false,
                    sortable: false
                }, {
                    name: 'seleccion',
                    width: 80,
                    align: 'center',
                    formatter: "checkbox",
                    formatoptions: {
                        disabled: false
                    },
                    edittype: 'checkbox',
                    editoptions: {
                        value: "Yes:No",
                        defaultValue: "No"
                    },
                    stype: "select",
                    searchoptions: {
                        sopt: ["eq", "ne"],
                        value: ":Any;true:Yes;false:No"
                    }
                }],
                beforeSelectRow: function(rowid, e) {
                    var $self = $(this),
                        $td = $(e.target).closest("tr.jqgrow>td"),
                        iCol = $td.length > 0 ? $td[0].cellIndex : -1,
                        cmName = iCol >= 0 ? $self.jqGrid("getGridParam", "colModel")[iCol].name : "",
                        localData = $self.jqGrid("getLocalRow", rowid);
                    if (cmName === "seleccion") {
                        localData.seleccion = $(e.target).is(":checked");
                    }

                    return true;
                },
                rowNum: 10,
                rowList: [10, 20, 30],
                pager: pager_selector,
                sortname: 'clave',
                viewrecords: true,
                sortorder: "desc"
            });

            // Setup buttons
            $("#grid-consolidados").jqGrid('navGrid', '#grid-pager3', {
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

            $(document).one('ajaxloadstart.page', function(e) {
                $(grid_selector).jqGrid('GridUnload');
                $('.ui-jqdialog').remove();
            });
        });

        /////////////////////////////////////Aqui se contruye el Grid de articulos/////////////////////////////////////////
        $(function($) {
            var grid_selector = "#grid-surtidos";
            var pager_selector = "#grid-pager4";

            //resize to fit page size
            $(window).on('resize.jqGrid', function() {
                    $(grid_selector).jqGrid('setGridWidth', $(window).width() - 150);
                })
                //resize on sidebar collapse/expand
            var parent_column = $(grid_selector).closest('[class*="col-"]');
            $(document).on('settings.ace.jqGrid', function(ev, event_name, collapsed) {
                if (event_name === 'sidebar_collapsed' || event_name === 'main_container_fixed') {
                    //setTimeout is for webkit only to give time for DOM changes and then redraw!!!
                    setTimeout(function() {
                        $(grid_selector).jqGrid('setGridWidth', $(window).width() - 150);
                    }, 0);
                }
            })

            $(grid_selector).jqGrid({
                url: '/api/crossdock/update/index.php',
                datatype: "local",
                shrinkToFit: false,
                height: 'auto',
                mtype: 'POST',
                colNames: ['Familia', 'Clave', 'Artículo', 'Lote', 'Ubicación', 'Existencias', 'Pedidas', 'Cajas'],
                colModel: [
                  {name: 'familia',index: 'familia',width: 150,editable: false,sortable: false}, 
                  {name: 'clave',index: 'clave',width: 150,editable: false,sortable: false}, 
                  {name: 'articulo',index: 'articulo',width: 250,editable: false,sortable: false}, 
                  {name: 'lote',index: 'lote',width: 100,editable: false,sortable: false}, 
                  {name: 'ubicacion',index: 'ubicacion',width: 100,editable: false,sortable: false}, 
                  {name: 'existencias',index: 'existencias',width: 100,editable: false,sortable: false}, 
                  {name: 'pedidas',index: 'pedidas',width: 100,editable: false,sortable: false}, 
                  {name: 'cajas',index: 'cajas',width: 100,editable: false,sortable: false}
                ],
                rowNum: 10,
                rowList: [10, 20, 30],
                pager: pager_selector,
                sortname: 'clave',
                viewrecords: true,
                sortorder: "desc"
            });

            // Setup buttons
            $("#grid-surtidos").jqGrid('navGrid', '#grid-pager3', {
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

            $(document).one('ajaxloadstart.page', function(e) {
                $(grid_selector).jqGrid('GridUnload');
                $('.ui-jqdialog').remove();
            });
        });
      
      
        $(function($) {
            var grid_selector = "#grid-table-surtido";
            var pager_selector = "#grid-pager-surtido";

            //resize to fit page size
            $(window).on('resize.jqGrid', function() {
                $("#grid-table-surtido").jqGrid('setGridWidth', $('#modalItems').width() - 50);
            })
            //resize on sidebar collapse/expand
            var parent_column = $(grid_selector).closest('[class*="col-"]');
            $(document).on('settings.ace.jqGrid', function(ev, event_name, collapsed) {
                if (event_name === 'sidebar_collapsed' || event_name === 'main_container_fixed') {
                    //setTimeout is for webkit only to give time for DOM changes and then redraw!!!
                    setTimeout(function() {
                        $(grid_selector).jqGrid('setGridWidth',  $(window).width() - 150);
                    }, 0);
                }
            })

            $(grid_selector).jqGrid({
                url: '/api/crossdock/update/index.php',
                datatype: "local",
                shrinkToFit: false,
                height: 'auto',
                mtype: 'POST',
                colNames: ['Clave', 'Artículo', 'BL', 'Ruta', 'Surtidor', 'Disponible', 'Solicitado','Surtido','Surtir','Acciones'],
                colModel: [
                  {name: 'clave',index: 'clave',width: 100,editable: false,sortable: false}, 
                  {name: 'articulo',index: 'articulo',width: 300,editable: false,sortable: false}, 
                  {name: 'bl',index: 'bl',width: 80,editable: false,sortable: false}, 
                  {name: 'ruta',index: 'ruta',width: 100,editable: false,sortable: false}, 
                  {name: 'surtidor',index: 'surtidor',width: 100,editable: false,sortable: false}, 
                  {name: 'existencia',index: 'existencia',width: 100,editable: false,sortable: false}, 
                  {name: 'solicitado',index: 'solicitado',width: 100,editable: false,sortable: false}, 
                {name: 'ya_surtido',index: 'ya_surtido',width: 100,editable: false,sortable: false}, 
                  {name: 'surtido',index: 'surtido',width: 100,editable: false,sortable: false},
                  {name: 'acciones',index: 'acciones',width: 100,editable: false,sortable: false}
                ],
                rowNum: 10,
                rowList: [10, 20, 30],
                pager: pager_selector,
                sortname: 'clave',
                viewrecords: true,
                sortorder: "desc"
            });

            // Setup buttons
            $("#grid-table-surtido").jqGrid('navGrid', '#grid-pager-surtido', {
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

            $(document).one('ajaxloadstart.page', function(e) {
                $(grid_selector).jqGrid('GridUnload');
                $('.ui-jqdialog').remove();
            });
        });

        //////////////////////////////////////////////////////////hace un reload del grid llamando al php en la carpera "c" para hacer la consulta//////////////////////////////////////////////////////////

        function downloadxml(url) {
            var win = window.open(url, '_blank');
            win.focus();
        }

        function viewPdf(url) {
            var win = window.open(url, '_blank');
            win.focus();
        }

        $modal0 = null;
    </script>



    <script>
        $(document).ready(function() {
            //TODO Quitar comentario
            $('td[aria-describedby="grid-table_planear"] input[type="checkbox"]').change(function() {
                //	console.log($('td[aria-describedby="grid-table_planear"] input[type="checkbox"]:checked').length);
                if ($('td[aria-describedby="grid-table_planear"] input[type="checkbox"]:checked').length > 0)
                    $("#planificalo").show();
                else
                    $("#planificalo").hide();
            });

            $('td[aria-describedby="grid-table_asignar"] input[type="checkbox"]').change(function() {
                console.log($('td[aria-describedby="grid-table_asignar"] input[type="checkbox"]:checked').length);
                if ($('td[aria-describedby="grid-table_asignar"] input[type="checkbox"]:checked').length > 0)
                    $("#btn-asignar").show();
                else
                    $("#btn-asignar").hide();
            });




            $('#planificarOla').hide();
            $('#asignacionPedidos').hide();
            $(function() {
                $('.chosen-select').chosen();
                $('.chosen-select-deselect').chosen({
                    allow_single_deselect: true
                });
            });
            $("div.ui-jqgrid-bdiv").css("max-height", $("#list").height() - 80);


            $("#btn-asignarTodo").change(function(e) {
                var val = $(e.currentTarget).prop('checked');                
                $.each( $('.column-asignar'), function (index, item) {
                    $(item).children().first().prop('checked', val) 
                });               
                $("#btn-asignar").show();
            });


            $("#btn-planificarTodo").change(function(e) {
                var val = $(e.currentTarget).prop('checked');                
                $.each( $('.column-planificar'), function (index, item) {
                    $(item).children().first().prop('checked', val) 
                });               
                $("#btn-planificar").show();
            });
        });
    </script>



    <script>
        //enable datepicker
        function pickDate(cellvalue, options, cell) {
            setTimeout(function() {
                $(cell).find('input[type=text]')
                    .datepicker({
                        format: 'dd-mm-yyyy',
                        autoclose: true
                    });
            }, 0);
        }

        function pickDate(cellvalue, options, cell) {
            setTimeout(function() {
                $(cell).find('input[type=text]')
                    .datepicker({
                        format: 'dd-mm-yyyy',
                        autoclose: true
                    });
            }, 0);
        }
    </script>

    <script>
        $("#exportExcel").on("click", function() {
            if ($("#almacen").val() === '') return false;
            var options = {
                criterio: $("#criteriob").val(),
                fechaInicio: $("#fechai").val(),
                fechaFin: $("#fechaf").val(),
                status: $("#status").val(),
                almacen: $("#almacen").val(),
                filtro: $("#filtro").val(),
                facturaInicio: $("#factura_inicio").val(),
                facturaFin: $("#factura_final").val()
            };
            var form = document.createElement("form"),
                input1 = document.createElement("input"),
                input2 = document.createElement("input"),
                input3 = document.createElement("input");
            input4 = document.createElement("input");
            input5 = document.createElement("input");
            input6 = document.createElement("input");
            input7 = document.createElement("input");
            input8 = document.createElement("input");
            input9 = document.createElement("input");
            input10 = document.createElement("input");
            input1.setAttribute('name', 'nofooternoheader');
            input1.setAttribute('value', 'true');
            input2.setAttribute('name', 'action');
            input2.setAttribute('value', 'getDataExcel');
            input3.setAttribute('name', 'criterio');
            input3.setAttribute('value', options.criterio);
            input4.setAttribute('name', 'fechaInicio');
            input4.setAttribute('value', options.fechaInicio);
            input5.setAttribute('name', 'fechaFin');
            input5.setAttribute('value', options.fechaFin);
            input6.setAttribute('name', 'status');
            input6.setAttribute('value', options.status);
            input7.setAttribute('name', 'almacen');
            input7.setAttribute('value', options.almacen);
            input8.setAttribute('name', 'filtro');
            input8.setAttribute('value', options.filtro);
            input9.setAttribute('name', 'facturaInicio');
            input9.setAttribute('value', options.facturaInicio);
            input10.setAttribute('name', 'facturaFin');
            input10.setAttribute('value', options.facturaFin);
            form.setAttribute('action', '/api/crossdock/update/index.php');
            form.setAttribute('method', 'post');
            form.setAttribute('target', '_blank');
            form.appendChild(input1);
            form.appendChild(input2);
            form.appendChild(input3);
            form.appendChild(input4);
            form.appendChild(input5);
            form.appendChild(input6);
            form.appendChild(input7);
            form.appendChild(input8);
            form.appendChild(input9);
            form.appendChild(input10);
            document.body.appendChild(form);
            form.submit()
        })
        $("#exportPDF").on("click", function() {
            if ($("#almacen").val() === '') return false;
            var options = {
                criterio: $("#criteriob").val(),
                fechaInicio: $("#fechai").val(),
                fechaFin: $("#fechaf").val(),
                status: $("#status").val(),
                almacen: $("#almacen").val(),
                filtro: $("#filtro").val(),
                facturaInicio: $("#factura_inicio").val(),
                facturaFin: $("#factura_final").val(),
                action: 'getDataPDF'
            };
            var title = "Reporte de Pedidos";
            var cia = <?php echo $_SESSION['cve_cia'] ?>;
            var content = '';

            $.ajax({
                url: "/api/crossdock/lista/index.php",
                type: "POST",
                data: options,
                success: function(data, textStatus, xhr) {
                    var data = JSON.parse(data);
                    var content_wrapper = document.createElement('div');
                    /*Detalle*/
                    var table = document.createElement('table');
                    table.style.width = "100%";
                    table.style.borderSpacing = "0";
                    table.style.borderCollapse = "collapse";
                    var thead = document.createElement('thead');
                    var tbody = document.createElement('tbody');
                    var head_content = '<tr>' +
                        '<th style="border: 1px solid #ccc">No. Orde</th>' +
                        '<th style="border: 1px solid #ccc">No. OC Cliente</th>' +
                        '<th style="border: 1px solid #ccc">Prioridad</th>' +
                        '<th style="border: 1px solid #ccc">Status</th>' +
                        '<th style="border: 1px solid #ccc">Cliente</th>' +
                        '<th style="border: 1px solid #ccc">Dirección</th>' +
                        '<th style="border: 1px solid #ccc">Código Dane</th>' +
                        '<th style="border: 1px solid #ccc">Ciudad</th>' +
                        '<th style="border: 1px solid #ccc">Estado</th>' +
                        '<th style="border: 1px solid #ccc">Cantidad</th>' +
                        '<th style="border: 1px solid #ccc">Volumen</th>' +
                        '<th style="border: 1px solid #ccc">Peso</th>' +
                        '<th style="border: 1px solid #ccc">Fecha Registro</th>' +
                        '<th style="border: 1px solid #ccc">Fecha Entrega</th>' +
                        '<th style="border: 1px solid #ccc">Usuario Activo</th>' +
                        '<th style="border: 1px solid #ccc">% Surtido</th>' +
                        '</tr>';
                    var body_content = '';

                    data.header.forEach(function(item, index) {
                        body_content += '<tr>' +
                            '<td style="border: 1px solid #ccc; white-space:nowrap;">' + item.orden + '</td> ' +
                            '<td style="border: 1px solid #ccc; white-space:nowrap;">' + item.orden_cliente + '</td> ' +
                            '<td style="border: 1px solid #ccc; white-space:nowrap;">' + item.prioridad + '</td> ' +
                            '<td style="border: 1px solid #ccc; white-space:nowrap;">' + item.status + '</td> ' +
                            '<td style="border: 1px solid #ccc; white-space:nowrap;">' + item.cliente + '</td> ' +
                            '<td style="border: 1px solid #ccc; white-space:nowrap;">' + item.direccion + '</td> ' +
                            '<td style="border: 1px solid #ccc; white-space:nowrap;">' + item.dane + '</td> ' +
                            '<td style="border: 1px solid #ccc; white-space:nowrap;">' + item.ciudad + '</td> ' +
                            '<td style="border: 1px solid #ccc; white-space:nowrap;">' + item.estado + '</td> ' +
                            '<td style="border: 1px solid #ccc; white-space:nowrap;">' + item.cantidad + '</td> ' +
                            '<td style="border: 1px solid #ccc; white-space:nowrap;">' + item.volumen + '</td> ' +
                            '<td style="border: 1px solid #ccc; white-space:nowrap;">' + item.peso + '</td> ' +
                            '<td style="border: 1px solid #ccc; white-space:nowrap;">' + item.fecha_pedido + '</td> ' +
                            '<td style="border: 1px solid #ccc; white-space:nowrap;">' + item.fecha_entrega + '</td> ' +
                            '<td style="border: 1px solid #ccc; white-space:nowrap;">' + item.usuario + '</td> ' +
                            '<td style="border: 1px solid #ccc; white-space:nowrap;">' + item.surtido + '</td> ' +
                            '</tr>  ';

                    });

                    tbody.innerHTML = body_content;
                    thead.innerHTML = head_content;
                    table.appendChild(thead);
                    table.appendChild(tbody);

                    content_wrapper.appendChild(table);

                    content = content_wrapper.innerHTML;

                    /*Creando formulario para ser enviado*/

                    var form = document.createElement("form");
                    form.setAttribute("method", "post");
                    form.setAttribute("action", "/api/reportes/generar/pdf.php");
                    form.setAttribute("target", "_blank");

                    var input_content = document.createElement('input');
                    var input_title = document.createElement('input');
                    var input_cia = document.createElement('input');
                    input_content.setAttribute('type', 'hidden');
                    input_title.setAttribute('type', 'hidden');
                    input_cia.setAttribute('type', 'hidden');
                    input_content.setAttribute('name', 'content');
                    input_title.setAttribute('name', 'title');
                    input_cia.setAttribute('name', 'cia');
                    input_content.setAttribute('value', content);
                    input_title.setAttribute('value', title);
                    input_cia.setAttribute('value', cia);

                    form.appendChild(input_content);
                    form.appendChild(input_title);
                    form.appendChild(input_cia);

                    document.body.appendChild(form);
                    form.submit();
                }
            });
        })



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

        function consolidadoExcel() {
            var form = document.getElementById('excelConsolidado');
            form.submit();
        }

        function surtidoExcel() {
            var form = document.getElementById('excelSurtido');
            form.submit();
        }
    </script>
    <script type="text/javascript">
        <!-- GRID ASIGNAR AREA EMBARQUE -->
        //////////////////////////////////////////////////////////Aqui se contruye el Grid//////////////////////////////////////////////////////////
        $(function($) {
            var grid_selector = "#grid-areas-embarque";
            var pager_selector = "#gridpager-areas-embarque";

            //resize to fit page size
            $(window).on('resize.jqGrid', function() {
                    $(grid_selector).jqGrid('setGridWidth', $(window).width() - 150);
                })
                //resize on sidebar collapse/expand
            var parent_column = $(grid_selector).closest('[class*="col-"]');
            $(document).on('settings.ace.jqGrid', function(ev, event_name, collapsed) {
                if (event_name === 'sidebar_collapsed' || event_name === 'main_container_fixed') {
                    //setTimeout is for webkit only to give time for DOM changes and then redraw!!!
                    setTimeout(function() {
                        $(grid_selector).jqGrid('setGridWidth', $(window).width() - 150);
                    }, 0);
                }
            })

            $(grid_selector).jqGrid({
                url: '/api/crossdock/lista/index.php',
                datatype: isParamsEmpty() ? "local" : "json",
                shrinkToFit: false,
                height: 'auto',
                postData: {
                    criterio: $("#criteriob").val(),
                    fechaInicio: $("#fechai").val(),
                    fechaFin: $("#fechaf").val(),
                    status: $("#status").val(),
                    filtro: $("#filtro").val()
                },
                mtype: 'POST',
                colNames: ['Fotos','ID','No. Orden','No. OC Cliente','Status','Cliente','Ciudad','Estado','Fecha Registro','Fecha Entrega', '', ''
                ],
                colModel: [
                  {name: 'fotos',width: 100,index: 'fotos',editable: false,sortable: false},
                  {name: 'id_pedido',index: 'id_pedido',editable: false,sortable: false,hidden: true,key: true}, 
                  {name: 'orden',width: 100,index: 'orden',editable: false,sortable: false}, 
                  {name: 'orden_cliente',width: 100,index: 'orden_cliente',editable: false,sortable: false}, 
                  {name: 'status',index: 'status',editable: false,sortable: false,width: 150}, 
                  {name: 'cliente',index: 'cliente',editable: false,sortable: false,width: 220}, 
                  {name: 'ciudad',index: 'ciudad',editable: false,sortable: false,width: 200}, 
                  {name: 'estado',index: 'estado',editable: false,sortable: false,width: 200}, 
                  {name: 'fecha_pedido',width: 100,index: 'fecha_pedido',editable: false,sortable: false,width: 150}, 
                  {name: 'fecha_entrega',width: 100,index: 'fecha_entrega',editable: false,sortable: false,width: 150}, 
                  {name: 'asigando',width: 100,index: 'asigando',editable: false,sortable: false}, 
                  {name: 'myac',index: '',width: 90,fixed: true,sortable: false,resize: false,formatter: imageFormat,align: "center"  }, 
                ],
                rowNum: 10,
                rowList: [10, 20, 30],
                pager: pager_selector,
                sortname: 'ID_Pedido',
                viewrecords: true,
                sortorder: "desc",
                autowidth: true
            });

            // Setup buttons
            $("#grid-areas-embarque").jqGrid('navGrid', '#gridpager-areas-embarque', {
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
                var folio = rowObject.orden;
                var html = '';
                html += '<a href="#" onclick="mostrarAreaEmbarque(\'' + folio + '\')"><i class="fa fa-check" alt="Borrar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
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

        //////////////////////////////////////////////////////////hace un reload del grid llamando al php en la carpera "c" para hacer la consulta//////////////////////////////////////////////////////////
        function ReloadGrid5() {
            var rows = jQuery("#grid-table").jqGrid('getRowData');
            $('#grid-areas-embarque').jqGrid('clearGridData');
            for (var i = 0; i < rows.length; i++) {
                var row = rows[i];
                if (row['planear'] == "Yes") {
                    $("#grid-areas-embarque").jqGrid('addRowData', i, row);
                }
            }
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
        window.folio;

        function btnasiganarAreaEmbarqueOla() {
            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    cve_ubicacion: $("#areaembarque")[0].value,
                    Fol_folio: window.folio,
                    action: "guardarAreaEmbarque"
                },
                beforeSend: function(x) {
                    if (x && x.overrideMimeType) {
                        x.overrideMimeType("application/json;charset=UTF-8");
                    }
                },
                url: '/api/crossdock/update/index.php',
                success: function(data) {
                    if (data.success == true) {
                        $("#grid-areas-embarque").jqGrid('delRowData', window.cont);
                        //    $('#grid-areas-embarque').jqGrid('clearGridData');
                        $("#areaemb")[0].style.display = 'none';
                        //    $("#areaembarque")[0].val("");
                        document.ready = document.getElementById("areaembarque").value = "";

                    }
                }
            });
        }

        function mostrarAreaEmbarque(_folio) {
            $("#areaemb")[0].style.display = 'block';
            window.folio = _folio;
            var rows = jQuery("#grid-areas-embarque").jqGrid('getRowData');
            for (var i = 0; i < rows.length; i++) {
                var row = rows[i];
                if (row['orden'] == _folio) {
                    window.cont = i;
                }
            }

        }

        $("#volverSurtido").on("click", function(e) {
            $("#modal_surtido").modal('hide');
            $("#coModal").modal('show');
        });

        var itemSurtido = 0;

        function surtir(_folio, _articulo) {
            item = itemsArticulosPedidos[itemSurtido];

            item = item.folio + ' | ' + item.clave

            $("#coModal").modal('hide');
            $("#modal_surtido").modal('show');
            ReloadSurtido(item, _articulo)
        }

        $(function($) {
            var grid_selector = "#grid-table6";
            var pager_selector = "#grid-pager6";

            //resize to fit page size
            $(window).on('resize.jqGrid', function() {
                    $(grid_selector).jqGrid('setGridWidth', $(window).width() - 150);
                })
                //resize on sidebar collapse/expand
            var parent_column = $(grid_selector).closest('[class*="col-"]');
            $(document).on('settings.ace.jqGrid', function(ev, event_name, collapsed) {
                if (event_name === 'sidebar_collapsed' || event_name === 'main_container_fixed') {
                    //setTimeout is for webkit only to give time for DOM changes and then redraw!!!
                    setTimeout(function() {
                        $(grid_selector).jqGrid('setGridWidth', $(window).width() - 150);
                    }, 0);
                }
            })

            $(grid_selector).jqGrid({
                url: '',
                shrinkToFit: false,
                dataType: 'json',
                height: 'auto',
                mtype: 'POST',
                colNames: ['Folio', 'Clave', 'Artículo', 'Lote', 'Caducidad', 'Fecha de Ingreso', 'Ubicación', 'Existencia', 'Pedidas', 'Surtidas'],
                colModel: [
                  {name: 'folio',width: 130,index: 'folio',editable: false,sortable: false,hidden: true}, 
                  {name: 'clave',width: 130,index: 'clave',editable: false,sortable: false}, 
                  {name: 'articulo',width: 130,index: 'articulo',editable: false,sortable: false}, 
                  {name: 'lote',width: 130,index: 'lote',editable: false,sortable: false}, 
                  {name: 'caducidad',width: 130,index: 'caducidad',editable: false,sortable: false}, 
                  {name: 'fecha',width: 130,index: 'fecha',editable: false,sortable: false}, 
                  {name: 'ubicacion',width: 130,index: 'ubicacion',editable: false,sortable: false}, 
                  {name: 'existencia',width: 130,index: 'existencia',editable: false,sortable: false}, 
                  {name: 'pedidas',width: 130,index: 'pedidas',editable: false,sortable: false}, 
                  {name: 'surtidas',width: 130,index: 'surtidas',editrules: {integer: true},editable: true,edittype: 'text',sortable: false  }, 
                ],
                rowNum: 30,
                rowList: [30, 40, 50],
                pager: pager_selector,
                sortname: 'clave',
                viewrecords: true,
                sortorder: "desc",
                autowidth: true,
                editurl: 'clientArray',
                onSelectRow: editRow,
            });
            var lastSelection;

            function editRow(id) {
                if (id && id !== lastSelection) {
                    var grid = $(grid_selector);
                    grid.jqGrid('restoreRow', lastSelection);
                    grid.jqGrid('editRow', id, {
                        keys: true
                    });
                    lastSelection = id;
                }
            }

            // Setup buttons
            $(grid_selector).jqGrid('navGrid', '#grid-pager', {
                edit: false,
                add: false,
                del: false,
                search: false
            }, {
                height: 200,
                reloadAfterSubmit: true
            });


            $(window).triggerHandler('resize.jqGrid');

            $(document).one('ajaxloadstart.page', function(e) {
                $(grid_selector).jqGrid('GridUnload');
                $('.ui-jqdialog').remove();
            });
        });

        function ReloadSurtido(_folio, _articulo) {
            $('#grid-table6').jqGrid('clearGridData')
                .jqGrid('setGridParam', {
                    url: '/api/crossdock/lista/index.php',
                    postData: {
                        folio: _folio,
                        articulo: _articulo,
                        action: 'getArticuloSurtido'
                    },
                    datatype: 'json',
                    page: 1
                })
                .trigger('reloadGrid', [{
                    current: true
                }]);
        }

        document.getElementById("guardarSurtido").addEventListener('click', function(e) {
            var data = $('#grid-table6').jqGrid('getRowData'),
                pedidas = parseInt(data[0].pedidas),
                existencia = parseInt(data[0].existencia),
                surtidas = 0;

            for (var i = 0; i < data.length; i++) {
                var current = data[i];
                surtidas += parseInt(current.surtidas);
            }
            if (surtidas === 0) {
                swal("Error", "Debes indiciar la cantidad de piezas surtidas", "error");
            } else if (surtidas > pedidas) {
                swal("Error", "La cantidad de piezas a surtir es mayor a solicitadas", "error");
            } else if (surtidas > existencia) {
                swal("Error", "La cantidad de piezas a surtir es mayor a la existencia", "error");
            } else {
                $.ajax({
                    url: '/api/crossdock/update/index.php',
                    dataType: 'json',
                    data: {
                        action: 'guardarsurtido',
                        folio: data[0].folio,
                        cve_articulo: data[0].clave,
                        lote: data[0].lote,
                        caducidad: data[0].caducidad,
                        pedidas: pedidas,
                        surtidas: surtidas,
                        ubicacion: data[0].ubicacion
                    },
                    type: 'POST'
                }).done(function(data) {
                    if (data.success) {
                        swal({
                            title: "Éxito",
                            text: "El surtido se guardó correctamente",
                            type: "success"
                        }, function(confirm) {
                            $("#modal_surtido").modal('hide');
                            $("#coModal").modal('show');
                        });
                    } else {
                        swal("Error", "Ocurrió un error al guardar el surtido", "error");
                    }
                });
            }
        })
    </script>
    <script type="text/javascript">
    var EstatusActual = null;

    /**Levanta el Modal */
    function cambiarStatus(_folio, surtido) {

        folio = _folio;
        if ($('#status').val() == 'S' && surtido >= 99) {
            enviarAEbarque_o_Qa();
            return true;
        }

        $.ajax({
            url: '/api/crossdock/lista/index.php',
            method: 'GET',
            dataType: 'json',
            data: {
                action: 'obtenerStatus',
                folio: _folio
            }
        }).done(function(data) {

            if (data.status) {
                EstatusActual = data.status;
                $("#select-nuevo-status-folio").val(data.status);
                $("#select-nuevo-status-folio").trigger('chosen:updated');
            }
            $("#modal-status-motivo-txt").val('');
            $("#modal-status-motivo").hide();
            $("#modal-status").modal('show');
            $("#txt-status-actual-folio").val(_folio);

        });
    }

    // Al cambia el estatus del Select
    $("#select-nuevo-status-folio").change(function() {
      if ($(this).val() == 'K') {
          $("#modal-status-motivo").show();
      } else {
          $("#modal-status-motivo").hide();
      }

      if (EstatusActual == 'K' && $("#select-nuevo-status-folio").val() != 'K') {
          $("#modal-status-motivo").show();
      }
    });


    function asignarStatus() 
    {
        /*if( EstatusActual == 'S' ){
            enviarAEbarque_o_Qa();
            return true;
        }*/

        var folio = $("#txt-status-actual-folio").val(),
            nuevo_status = $("#select-nuevo-status-folio").val(),
            motivo = $("#modal-status-motivo-txt").val();
        /*
        if (nuevo_status == 'K' && motivo == '') {
            swal("Cancelación de Pedido", "Debe ingresar el mótivo por el cual se está cancelando el pedido", "warning");
            return false;
        }
        */
        if (EstatusActual == 'K' && $("#select-nuevo-status-folio").val() != 'K' && motivo == '') {
            swal("Cancelación de Pedido", "Debe ingresar el mótivo por el cual se está reversando el cancelado del pedido", "warning");
            return false;
        }

        $.ajax({
            url: '/api/crossdock/update/index.php',
            data: {
                folio: folio,
                status: nuevo_status,
                almacen: $('#almacen').val(),
                action: 'cambiarStatus',
                motivo: motivo
            },
            type: 'POST',
            dataType: 'json'
        }).done(function(data) {
            if (data.success) {

                filtralo();
                $("#modal-status").modal('hide');
                swal("Éxito", "Status de la orden cambiado exitosamente", "success");

            } else {
                swal("Error", data.text, "error");
            }
        });
    }


    function imprimirRemision() 
    {
      var folio = $("#fol_cambio").val(),
          form = document.createElement('form'),
          input_nofooter = document.createElement('input'),
          input_folio = document.createElement('input');

      form.setAttribute('method', 'post');
      form.setAttribute('action', '/reportes/pdf/remision');
      form.setAttribute('target', '_blank');

      input_nofooter.setAttribute('name', 'nofooternoheader');
      input_nofooter.setAttribute('value', '1');

      input_folio.setAttribute('name', 'folio');
      input_folio.setAttribute('value', folio);

      form.appendChild(input_nofooter);
      form.appendChild(input_folio);

      document.getElementsByTagName('body')[0].appendChild(form);
      form.submit();
    }
      
    </script>


