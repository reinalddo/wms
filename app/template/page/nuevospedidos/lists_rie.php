<?php
include $_SERVER['DOCUMENT_ROOT']."/app/host.php";
$listaZR = new \CortinaEntrada\CortinaEntrada();
$listaProvee = new \Proveedores\Proveedores();
$listaAlma = new \Almacen\Almacen();
$listaArtic = new \Articulos\Articulos();
$listaUser = new \Usuarios\Usuarios();
$listaPriori = new \TipoPrioridad\TipoPrioridad();
$listaOC = new \OrdenCompra\OrdenCompra();
$listaAP = new \AlmacenP\AlmacenP();
$listaProto= new \Protocolos\Protocolos();
$clientes  = new \Clientes\Clientes();
$clientes = $clientes->getAll();
$usuario = $_SESSION['id_user'];
if(!isNikken()){
    $codDaneSql = \db()->prepare("SELECT *  FROM c_dane ");
    $codDaneSql->execute();
    $codDane = $codDaneSql->fetchAll(PDO::FETCH_ASSOC);
}

?>
<link href="/css/plugins/datapicker/datepicker3.css" rel="stylesheet">
<link href="/css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">
<link href="/css/plugins/ladda/select2.css" rel="stylesheet"/>
<link href="/css/plugins/clockpicker/clockpicker.css" rel="stylesheet">
 <link href="/css/plugins/chosen/chosen.css" rel="stylesheet">
<link href="/css/plugins/sweetalert/sweetalert.css" rel="stylesheet">
<link href="/css/plugins/iCheck/custom.css" rel="stylesheet">

<div class="wrapper wrapper-content  animated" id="FORM" >
    <div class="row">
        <div class="col-md-12">
            <div class="ibox">
                <div class="ibox-title">
                    <div class="row">
                        <div class="col-lg-4" id="_title">
                            <h3>Registro de Pedidos</h3>
                        </div>
                    </div>
                </div>
                <form id="myform">
                    <div class="ibox-content">
                        <div class="row">
                            <div class="col-md-6 b-r b-b">
								<div class="form-group">
                                    <label>Folio*</label> 
                                    <input id="folio" name="folio" type="text" class="form-control" onBlur="validarFolio()" disabled>
                                </div>
								<div class="form-group">
                                    <label>Almacén*</label>
                                    <select class="form-control" name="almacen" id="almacen">
                                        <option value="">Seleccione</option>
                                        <?php foreach( $listaAP->getAll() AS $a ): ?>
                                            <option value="<?php echo $a->clave; ?>"><?php echo $a->nombre; ?></option>
                                        <?php endforeach; ?>
                                    </select>

                                </div>

                                <div class="form-group col-md-6" style="padding-left: 0;">
                                  <label>Cliente*</label>
                                  <input class="form-control" name="cliente_buscar" id="cliente_buscar" placeholder="Código del Cliente">
                                  <input class="form-control" name="cliente" id="cliente" placeholder="Código del Cliente" style="display: none;">
                                  <?php 
                                  /*
                                  ?>
                                  <select class="form-control chosen-select" name="cliente" id="cliente">
                                    <option value="">Seleccione</option>
                                    <?php */ /* if(!empty($clientes)): ?>
                                      <?php foreach($clientes as $cliente): ?>
                                        <option value="<?php echo $cliente->Cve_Clte ?>"><?php echo $cliente->Cve_Clte."-".$cliente->RazonSocial?></option>
                                      <?php endforeach; ?>
                                    <?php endif; */ /* ?>
                                  </select>
                                  <?php 
                                  */
                                  ?>
                                </div>

                                <div class="form-group col-md-6" style="padding-right: 0;">
                                    <label>Prioridad</label>
                                    <select class="form-control" name="prioridad" id="prioridad">
                                        <option value="0">Seleccione</option>
                                          <?php foreach( $listaPriori->getAll() AS $a ): ?>
                                            <option value="<?php echo $a->ID_Tipoprioridad; ?>"><?php echo $a->ID_Tipoprioridad."-".$a->Descripcion; ?></option>
                                        <?php endforeach; ?>

                                    </select>
                                </div>

                                <div class="row">
                                    <div class="form-group col-md-6">
                                    <label>Clave y Nombre Cliente</label>
                                         <?php /* ?><input id="desc_cliente" name="desc_cliente" type="text" class="form-control" value="" disabled><?php */ ?>
                                         <select id="desc_cliente" name="desc_cliente" class="form-control">
                                         </select>
                                    </div>

                                    <div class="form-group col-md-6">
                                    <label>Numero OC Cliente</label>
                                         <input id="nroOC" name="nroOC" type="text" class="form-control" value="">
                                    </div>
                                </div>
								<div class="form-group">
                                    <label>Vendedor</label>
                                    <input id="vendedor" name="vendedor" type="text" class="form-control">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group" id="data_1">
                                    <label>Fecha de entrega solicitada*</label>
                                    <div class="input-group date">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                        <input id="fechaSolicitud" name="fechaSolicitud" type="text" class="form-control">
                                    </div>
                                </div>


                                <div class="form-group">
                                    <label>Horario Planeado</label>
                                    <div class="input-group date" style="display: block;">

                                        <div style="display: inline-block;">
                                        <label><b>Desde</b></label>
                                        <br>
                                        <input id="hora_desde" name="hora_desde" type="time" class="form-control" 
                                        style="
                                            display: inline-block;
                                            margin-right: 20px;
                                            width: 200px;">
                                        </div>

                                        <div style="display: inline-block;">
                                        <label><b>Hasta</b></label>
                                        <br>
                                        <input id="hora_hasta" name="hora_hasta" type="time" class="form-control" style="
                                            display: inline-block;
                                            margin-right: 20px;
                                            width: 200px;"></div>
                                        </div>
                                        <br>
                                    </div>

                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <label>Dirección de Envío *</label> 
                                            <div class="input-group date">                                                
                                                <select class="form-control chosen-select" name="destinatario" id="destinatario" disabled></select>
                                                <span class="input-group-addon" style="padding:0px !important; ">                                                
                                                    <button type="button" disabled id="agregar_destinatario" class="btn btn-success" data-toggle="modal" data-target="#modal_destinatario">
                                                        Agregar Destinatario
                                                    </button>
                                                </span>
                                            </div>
                                        </div>

                                    </div>                              
                                </div>
                                 <div class="form-group">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <textarea id="txt-direc" name="observacion" class="form-control" disabled></textarea>
                                        </div>
                            
                                    </div>                              
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Observacion</label>
                                    <textarea id="observacion" name="observacion" class="form-control" style="resize: none"></textarea>
                                </div>
                            </div>
                        </div>
                        <?php 
                        //*********************************************************************************************
                        //*********************************************************************************************
                        //*********************************************************************************************
                        ?>
                        <div class="row">  
                            <div class="ibox-title">
                                <div class="row">
                                    <div class="col-md-4" id="_title">
                                        <h3>Detalles del Pedido</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 b-t">
                                <br>
                                <div class="form-group">
                                    <label>Artículo*</label>
                                    <select name="articulo" id="articulo" class="chosen-select form-control">
                                        <option value="">Seleccione Artículo</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4 b-t">
                                <br>
                                <div class="form-group">
                                    <label>Cantidad de Piezas</label> 
                                    <input id="cantPzas" name="cantPzas" type="number" oninput="parseFloat($(this).val()).toFixed(2)"  type="number" step="0.01" class="form-control">
                                </div>                                
                            </div>
                            <div class="col-md-4 b-t">
                                <br>
                                <div class="form-group">
                                    <label>Caducidad minima (meses)</label> 
                                    <input id="caducidadMin" name="caducidadMin" type="number" class="form-control">
                                </div>
                            </div>
                        </div>
                        <?php 
                        //*********************************************************************************************
                        //*********************************************************************************************
                        //*********************************************************************************************
                        /*
                        ?>
                            <div class="row" style="display: none;">
                              <div class="col-lg-12" style="pading: 30px; marging: 30px;">
                                <div class="form-group col-lg-12" style="pading: 30px; marging: 30px;  border: 1px solid; border-color: #dedede; border-radius: 15px"><!--#1ab394-->
                                  <div class="form-group col-md-3" id="Articul" >
                                      <label>Artículo*</label>
                                                <!--country      basic-->
                                      <select name="articulo" id="articulo" class="chosen-select form-control" readonly>
                                          <option value="">Seleccione Artículo</option>
                                      </select>
                                      <input id="ancho" type="hidden" class="form-control">
                                      <input id="alto" type="hidden" class="form-control">
                                      <input id="fondo" type="hidden" class="form-control">
                                  </div>
                                  <div class="form-group col-md-3">
                                      <label>Cantidad de Piezas</label> 
                                      <input type="hidden" id="modo_peso" value="1">
                                      <input id="CantPiezas" type="text" placeholder="Cantidad de Piezas" class="form-control" maxlength="8">
                                      <input id="CantPiezasPeso" type="text" placeholder="Cantidad de Piezas Kg" class="form-control" maxlength="8" style="display: none;">
                                  </div>
                                  <div class="form-group col-md-3">
                                        <label>Caducidad minima (meses)</label> 
                                        <input id="caducidadMin" name="caducidadMin" type="number" class="form-control">
                                  </div>
                                  <div class="form-group col-md-3">
                                      <label>Peso (Kgs)</label> 
                                      <input type="hidden" id="pesohidden" value="0"/>
                                      <input id="peso" disabled type="text" placeholder="Peso" class="form-control">
                                  </div>
                                  <input id="status" type="hidden" placeholder="Cantidad de Piezas" class="form-control">
                              
                                  <div class="col-lg-12">
                                    <div class="form-group col-md-4">
                                        <label>Precio Unitario</label> 
                                        <input type="hidden" id="PUhidden" value="0"/>
                                        <input id="precioUnitario" type="text" placeholder="$ 00.00 " class="form-control">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Importe Total (Articulo)</label> 
                                        <input type="hidden" id="importeHidden" value="0"/>
                                        <input id="importeTotal" disabled type="text" placeholder="$ 00.00 " class="form-control">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Importe Total de la Orden de Compra</label> 
                                        <input id="importeOrden" disabled type="text" placeholder="$ 00.00 " class="form-control">
                                    </div>
                                  </div>
                                </div>
                              </div>
                            </div>
                          <?php 
                          */
                          ?>
                        <div class="row">
                            <div class="col-md-12 text-center">
                                <button type="button" class="btn btn-primary" id="btnAgregar">Agregar</button>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <br>
                                <div class="ibox-content">
                                    <div style="width: 100%; text-align: center; margin-bottom: 20px;">
                                        <button type="button" id="exportExcel" class="btn btn-primary">
                                            <i class="fa fa-file-excel-o"></i>
                                            Excel
                                        </button>
                                        <button type="button" id="exportPDF" class="btn btn-danger">
                                            <i class="fa fa-file-pdf-o"></i>
                                            PDF
                                        </button>
                                    </div>
                                    </div>
                                    <div class="jqGrid_wrapper">
                                        <table id="grid-table"></table>
                                        <div id="grid-pager"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label> Total Articulos</label> 
                                <input name="totalArt" id="totalArt" disabled type="number" class="form-control">
                            </div>
                            <div class="form-group col-md-6">
                                <label> Total Piezas</label> 
                                <input name="totalPiez" id="totalPiez" disabled type="number" class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="pull-right">
                                    <button type="button" class="btn btn-primary" id="btnRegistrar">Guardar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="articulos" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body" style="text-align: center;">
                <h3 class="modal-title">Cargando artículos del almacén</h3>
                <i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i>
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
                        <input type="text" id="consecutivo" class="form-control" disabled value="0">
                    </div>
                    <div class="form-group">
                        <label>Cliente</label>
                        <input type="text" id="agregar_cliente" disabled class="form-control">
                        <input type="hidden" id="hidden_agregar_cliente">
                    </div>
                    <div class="form-group">
                        <label>Nombre / Razón Social</label>
                        <input type="text" class="form-control" style="text-transform:uppercase;" id="destinatario_razonsocial">
                    </div>
                    <div class="form-group">
                        <label>Dirección</label>
                        <input type="text" class="form-control" style="text-transform:uppercase;" id="destinatario_direccion">
                    </div>
                    <div class="form-group">
                        <label>Colonia</label>
                        <input type="text" class="form-control" style="text-transform:uppercase;" id="destinatario_colonia">
                    </div>
                    <div class="form-group">
                        <label>CP | CD</label>
                        <?php if(isset($codDane) && !empty($codDane)): ?>
                            <select id="destinatario_dane" class="form-control chosen-select">
                                <option value="">Código</option>
                                <?php foreach( $codDane AS $p ): ?>
                                    <option value="<?php echo $p["cod_municipio"];?>"><?php echo $p["cod_municipio"]." ".$p["des_municipio"]; ?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php else: ?>
                            <input type="text" name="destinatario_dane" id="destinatario_dane" class="form-control">
                        <?php endif; ?> 
                    </div>
                    <div class="form-group">
                        <label>Alcaldía | Municipio</label>
                        <input type="text" class="form-control" id="destinatario_estado" readonly>
                    </div>
                    <div class="form-group">
                        <label>Ciudad | Departamento</label>
                        <input type="text" class="form-control" id="destinatario_ciudad" readonly>
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
                        <input type="email" class="form-control" id="txtEmailDest">
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







<div class="modal fade" id="modal-perfil-usuario" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="vertical-alignment-helper">
        <div class="modal-dialog vertical-align-center  modal-sm">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Validar perfil de usuario</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Contraseña</label>
                        <input type="password" id="password" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">                    
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="verificaCredenciales()">Verificar</button>
                </div>
            </div>
        </div>
    </div>
</div>


<script src="/js/pdfmake.min.js"></script>
<script src="/js/vfs_fonts.js"></script>
<script src="/js/jszip.min.js"></script>
<!-- Mainly scripts -->
<script src="/js/jquery-2.1.4.min.js"></script>
<script src="/js/bootstrap.min.js"></script>
<script src="/js/plugins/metisMenu/jquery.metisMenu.js"></script>
<script src="/js/plugins/slimscroll/jquery.slimscroll.min.js"></script>
<script src="/js/moment.js"></script>
<script src="/js/moment-with-locales.js"></script>

<!-- Peity -->
<script src="/js/plugins/peity/jquery.peity.min.js"></script>

<!-- jqGrid -->
<script src="/js/plugins/jqGrid/i18n/grid.locale-es.js"></script>
<script src="/js/plugins/jqGrid/jquery.jqGrid.js"></script>

<!-- Select -->
<script src="/js/plugins/chosen/chosen.jquery.js"></script>

<!-- Custom and plugin javascript -->
<script src="/js/inspinia.js"></script>
<script src="/js/plugins/pace/pace.min.js"></script>

<script src="/js/plugins/jquery-ui/jquery-ui.min.js"></script>

<script src="/js/plugins/ladda/spin.min.js"></script>
<script src="/js/plugins/ladda/ladda.min.js"></script>
<script src="/js/plugins/ladda/ladda.jquery.min.js"></script>

<!-- Select -->
<script src="/js/select2.js"></script>

<!-- Data picker -->
<script src="/js/plugins/datapicker/bootstrap-datepicker.js"></script>
<script src="/js/plugins/clockpicker/clockpicker.js"></script>
<script src="/js/plugins/sweetalert/sweetalert.min.js"></script>
<script src="/js/plugins/iCheck/icheck.min.js"></script>
<script src="/js/utils.js"></script>

<script type="text/javascript">

    var exportDataGrid = new ExportDataGrid();

    /**
     * @author Ricardo Delgado.
     * Busca y selecciona el almacen predeterminado para el usuario.
     */ 

    function CargarClientes(almacen_clientes){

        console.log("val_almacen = ", almacen_clientes);

        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                action: 'getClientes',
                almacen: almacen_clientes
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/nuevospedidos/lista/index.php',
            success: function(data) {
                //console.log("SUCCESS: ", data);
                var combo = data.combo;
                var cliente = document.getElementById("cliente");
                if(data.combo !== ''){
                    cliente.innerHTML = data.combo;
                }
                $(".chosen-select").trigger("chosen:updated");
            },
            error: function(res){
                console.log("ERROR: ", res);
            }
        });

    }

    function almacenPrede(){ 
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                idUser: '<?php echo $_SESSION["id_user"]?>',
                action: 'search_almacen_pre'
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/almacenPredeterminado/index.php',
            success: function(data) {
                if (data.success == true) {
                    document.getElementById('almacen').value = data.codigo.clave;
                    setTimeout(function() {
                        $('#almacen').trigger('change');
                    }, 1000);
                    var almacen_clientes = $("#almacen").val();
                    //CargarClientes(almacen_clientes);
                }
            },
            error: function(res){
                window.console.log(res);
            }
        });
    }
    almacenPrede();

    $("#almacen").change(function(){
        //CargarClientes($(this).val());
    });
    //////////////////////////////////////////////////////////Aqui se contruye el Grid//////////////////////////////////////////////////////////

    $(function($) {
        var grid_selector = "#grid-table";
        var pager_selector = "#grid-pager";

        //resize to fit page size
        $(window).on('resize.jqGrid', function () {
            $(grid_selector).jqGrid( 'setGridWidth', $(".page-content").width() - 60 );
        })
        //resize on sidebar collapse/expand
        var parent_column = $(grid_selector).closest('[class*="col-"]');
        $(document).on('settings.ace.jqGrid' , function(ev, event_name, collapsed) {
            if( event_name === 'sidebar_collapsed' || event_name === 'main_container_fixed' ) {
                //setTimeout is for webkit only to give time for DOM changes and then redraw!!!
                setTimeout(function() {
                    $(grid_selector).jqGrid( 'setGridWidth', parent_column.width() );
                }, 0);
            }
        });

        $(grid_selector).jqGrid({
            datatype: "local",
            height: 'auto',
            width: 'auto',
			shrinkToFit: false,
            mtype: 'POST',
            colNames:[
                'Clave Cliente',
                'Clave Prioridad',
                'Clave', 
                'Articulo', 
                'Cantidad de Piezas', 
                'Caducidad Minima (meses)', 
                'Fecha Registro',
                'Cliente',
                'Destinatario',
                'Fecha Compromiso Entrega',
                'Prioridad',
                'Acciones'
            ],
            colModel:[
                {name:'c_cliente',index:'c_cliente', hidden: true},
                {name:'c_prioridad',index:'c_prioridad', hidden: true},
                {name:'clave',index:'clave',width:190, editable:false, align: 'center',sortable:false},
                {name:'articulo',index:'articulo',width:450, align: 'center', editable:false, sortable:false},
				{name:'cantPiezas',index:'cantPiezas',width:135, align: 'center', editable:false, sortable:false},
                {name:'caducidad',index:'caducidad',width:175, align: 'center', editable:false, sortable:false},
                {name:'fecha_registro',index:'fecha_registro',width:175, align: 'center', editable:false, sortable:false},
                {name:'cliente',index:'cliente',width:175, align: 'center', editable:false, sortable:false},
                {name:'destinatario',index:'destinatario',width:175, align: 'center', editable:false, sortable:false},
                {name:'fecha_compromiso',index:'fecha_compromiso',width:175, align: 'center', editable:false, sortable:false},
				{name:'prioridad',index:'prioridad',width:175, align: 'center', editable:false, sortable:false},
                {name:'myac',index:'', width:80, fixed:true, align: 'center', sortable:false, resize:false, formatter:imageFormat2}
            ],
            rowNum:30,
            rowList:[30,40,50],
            pager: pager_selector,
            sortname: 'clave',
            pgbuttons: false,
            viewrecords: true,
            userDataOnFooter: true,
            footerrow: true,
            loadonce: true,
            sortorder: "desc",
            gridComplete: function(){
            }
        });

        $(window).triggerHandler('resize.jqGrid');
        function imageFormat2( cellvalue, options, rowObject ){
            var correl = options.rowId;
            var html = '<a href="#" onclick="borrarAdd(\''+correl+'\')"><i class="fa fa-eraser" alt="Borrar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            return html;
        }
        
    });


    function borrarAdd(_codigo) {
        swal({
            title: "¿Está seguro que desea borrar el artículo?",
            text: "Está a punto de borrar un artículo recibido y esta acción no se puede deshacer",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Borrar",
            cancelButtonText: "Cancelar",
            closeOnConfirm: false
            },
            function(){
                $("#grid-table").jqGrid('delRowData', _codigo);
                swal("Borrado", "El articulo ha sido borrado exitosamente", "success");
                   
        });
    }


    /**
     * Undocumented function
     *
     * @return void
     */
    function registrarPedido(){
   
        var arrDetalle = [];

        var ids = $("#grid-table").jqGrid('getDataIDs');

        for (var i = 0; i < ids.length; i++)
        {
            var rowId = ids[i];
            var rowData = $('#grid-table').jqGrid ('getRowData', rowId);

            arrDetalle.push({
                Cve_articulo: rowData.clave,
                Num_cantidad: rowData.cantPiezas,
                Num_Meses: rowData.caducidad
            });
        }

        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                Fol_folio: $("#folio").val(),
                Fec_Pedido: moment().format("YYYY-MM-DD HH:mm:ss"),
                Fec_Entrada: moment().format("YYYY-MM-DD HH:mm:ss"),
                Fec_Entrega: moment($("#fechaSolicitud").val(), 'DD-MM-YYYY').format('YYYY-MM-DD HH:mm:ss'),
                Cve_clte: $("#cliente").val(),
                status: "A",
                cve_Vendedor: $("#vendedor").val(),
                Observaciones: $("#observacion").val(),
                ID_Tipoprioridad: $("#prioridad").val(),
                cve_almac: $("#almacen").val(),
                destinatario: $("#destinatario").val(),
                Pick_Num: $("#nroOC").val(),
                hora_desde: $("#hora_desde").val(),
                hora_hasta: $("#hora_hasta").val(),
                arrDetalle: arrDetalle,
                Cve_Usuario: <?php echo $usuario; ?>,
                action : "add"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
             url: '/api/nuevospedidos/update/index.php',
            success: function(data) {
                if (data.success == true) {
                    swal(
                      '¡Exito!',
                      'El pedido ha sido registrado correctamente',
                      'success'
                    );
                    resetForm();
                } else {
                    swal("Error", "Ocurrio un error al guardar el pedido", "error");
                }
            }, error: function(data) {
                console.log("error pedido = ", data);
            }
        });
    }


    /**
     * Undocumented function
     *
     * @return void
     */
    function validarUsuarioAdminstrador()
    {
      var cliente=$('#cliente').val();
      var destinatario= $('#txt-direc').val();
      var folio=$('#folio').val();
      if(folio == ''){
        console.log("Sin folio");
        swal("Error", "Coloque un folio", "error"); 
      }else if(cliente == 'Seleccione' || destinatario == 'Seleccione' || destinatario == ''){
        console.log("Sin destinatario");
        swal("Error", "Coloque un destinatario", "error"); 
      }else{
         //$('#modal-perfil-usuario').modal('show');
          console.log("_cliente = ", cliente);
          console.log("_destinatario = ", destinatario);
          console.log("_folio = ", folio);
         registrarPedido();
      }
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    function verificaCredenciales(){
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                password: $("#password").val(),
                action : 'validarCredenciales'
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/nuevospedidos/update/index.php',
            success: function(data) {
                if (data.status == 200) {
                    $('#modal-perfil-usuario').modal('hide');
                    registrarPedido();
                } else {
                    swal("Error", "Ocurrio un error al validar las credenciales de administrador", "error");
                }
            }
        });
    }



    function validarFolio(){
        var folio = $("#folio").val();
        
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                Fol_folio : folio,
                action : "exists"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/nuevospedidos/update/index.php',
            success: function(data) {
                if (data.success) {
                    swal("Error", "El folio que intentas registrar ya existe", "error");
                    $("#btnRegistrar").prop('disabled', true);
                }else{
                    $("#btnRegistrar").prop('disabled', false);
                }
            }
            
        });
    }

    $('#data_1 .input-group.date').datetimepicker({
       locale: 'es',
       format: 'DD-MM-YYYY',
       useCurrent: false
    });
    
</script>

<script>
    $(document).ready(function(){
        localStorage.setItem("consecutivo", 0);
        $('.icheck').iCheck({
            checkboxClass: 'icheckbox_square-green',
            radioClass: 'iradio_square-green'
        });
        $('.chosen-select').chosen();
        $('.chosen-select-deselect').chosen({ allow_single_deselect: true });

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

            function isEmail(email) {
              var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
              return regex.test(email);
            }

        $("#guardar_destinatario").on("click", function(){
            var cliente = $("#hidden_agregar_cliente").val(),
                razon = $("#destinatario_razonsocial").val(),
                direccion = $("#destinatario_direccion").val(),            
                colonia = $("#destinatario_colonia").val(),
                postal = $("#destinatario_dane").val(),            
                ciudad = $("#destinatario_ciudad").val(),            
                estado = $("#destinatario_estado").val(),            
                contacto = $("#destinatario_contacto").val(),            
                telefono = $("#destinatario_telefono").val();

            if(!isEmail($("#txtEmailDest").val()) && $("#txtEmailDest").val()){
                    swal('Error', 'Por favor escriba el email correctamente', 'error');
            }else if(razon === ''){
                swal("Error", "Ingrese Razón Social del destinatario", "error");
            }else if(direccion === ''){
                swal("Error", "Ingrese Dirección del destinatario", "error");
            }else if(postal === ''){
                swal("Error", "Ingrese Código Postal del destinatario", "error");
            }else if(contacto === ''){
                swal("Error", "Ingrese Contaccto del destinatario", "error");
            }else if(telefono === ''){
                swal("Error", "Ingrese Teléfono del destinatario", "error");
            }else{
                $.ajax({
                    url: '/api/nuevospedidos/update/index.php',
                    dataType: 'json',
                    method: 'POST',
                    data: {
                        action: 'agregarDestinatario',
                        cliente: cliente,
                        razon: razon,
                        direccion: direccion,
                        colonia: colonia,
                        postal: postal, 
                        ciudad: ciudad,
                        estado: estado,
                        contacto: contacto,
                        email_destinatario: $("#txtEmailDest").val(),
                        txtLatitudDest: $("#txtLatitudDest").val(),
                        txtLongitudDest: $("#txtLongitudDest").val(),
                        telefono: telefono
                    }
                }).done(function(data){
                    if(data.success){
                        swal("Éxito", "Dirección añadida exitosamente", "success");
                        $("#modal_destinatario").modal('hide')
                        //$("#cliente").trigger("change");
                    }else{
                        swal("Error", "Ocurrió un error al guardar la dirección intenta de nuevo", "error");
                    }
                });
            }
        });

        $("#agregar_destinatario").on("click", function(){
            $.ajax({
                url: '/api/clientes/lista/index.php',
                data: {
                    action: 'obtenerClaveDestinatario',
                },
                dataType: 'json',
                type: 'GET'
            }).done(function(data){
                $("#consecutivo").val(data.clave);
                var cliente_clave = $("#cliente").val(),
                    cliente = $("#cliente").val(); //$(`#cliente option[value='${cliente_clave}']`).html();
                $("#destinatario_razonsocial").val('');
                $("#destinatario_direccion").val('');
                $("#destinatario_colonia").val('');
                $("#destinatario_ciudad").val('');
                $("#destinatario_estado").val('');
                $("#destinatario_telefono").val('');
                $("#destinatario_dane").val('');
                $(".chosen-select").trigger('chosen:updated');
                $("#agregar_cliente").val(cliente);
                $("#hidden_agregar_cliente").val(cliente_clave);
            });
        });

        //$("#cliente").on('change', function(e)
        $("#cliente_buscar").on('keyup', function(e)
        {
            var cliente = e.target.value;
            console.log("Cliente = ", cliente);
            document.getElementById("agregar_destinatario").removeAttribute("disabled");
            $.ajax({
                url: '/api/nuevospedidos/lista/index.php',
                data: {
                    action: 'getClientesSelect',
                    cliente: cliente
                },
                dataType: 'json',
                method: 'GET',
                beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                },
                success: function(data) 
                {
                    console.log("SUCCESS: ", data);
                    //var combo = data.combo;
                    //if(combo.length > 36 || data.direccion.length > 2){
                        var desc_cliente = document.getElementById("desc_cliente");
                        if(data.combo !== '' && data.find == true){
                            desc_cliente.innerHTML = data.combo;
                            var text = desc_cliente.options[desc_cliente.selectedIndex].text;
                            //document.getElementById("txt-direc").value = text;
                            desc_cliente.removeAttribute('disabled');
                            //$("#cliente").val(data.clave_cliente);
                            //$("#desc_cliente").val("["+data.clave_cliente+"] - "+data.nombre_cliente);
                            $("#cliente").val(data.firsTValue);
                            BuscarDestinatario(data.firsTValue);
                            console.log("#cliente = ", $("#cliente").val());
                        }
                        else
                        {
                            $("#destinatario, #agregar_destinatario").prop("disabled", true);
                            $("#cliente").val("");
                            $("#desc_cliente").val("");
                            console.log("#cliente = ", $("#cliente").val());
                        }

                        $(".chosen-select").trigger("chosen:updated");
/*
                        var destinatario = document.getElementById("destinatario");
                        if(data.combo !== '' && data.find == true){
                            destinatario.innerHTML = data.combo;
                            var text = destinatario.options[destinatario.selectedIndex].text;
                            document.getElementById("txt-direc").value = text;
                            destinatario.removeAttribute('disabled');
                            $("#cliente").val(data.clave_cliente);
                            $("#desc_cliente").val("["+data.clave_cliente+"] - "+data.nombre_cliente);
                            console.log("#cliente = ", $("#cliente").val());
                        }
                        else
                        {
                            $("#destinatario, #agregar_destinatario").prop("disabled", true);
                            $("#cliente").val("");
                            $("#desc_cliente").val("");
                            console.log("#cliente = ", $("#cliente").val());
                        }

                        $(".chosen-select").trigger("chosen:updated");
*/
                    //}
                    /*else{
                        swal(
                            {
                                title: "Advertencia",
                                text: "El cliente seleccionado no posee una dirección de envío principal. Debe crear una.",
                                type: "warning",
                                showCancelButton: false,
                                allowEscapeKey: false
                            }, function(confirm){
                                var cliente_clave = $("#cliente").val(),
                                    cliente = $(`#cliente option[value='${cliente_clave}']`).html();
                                $("#agregar_cliente").val(cliente);
                                $("#hidden_agregar_cliente").val(cliente_clave);
                                $("#modal_destinatario").modal('show')
                            }
                        );
                    }*/
                }, error: function(data){
                    console.log("ERROR: ", data);
                }

            });/*.done(function(data){
                console.log("SUCCESS: ", data);
                var combo = data.combo;
                if(combo.length > 36 || data.direccion.length > 2){
                    var destinatario = document.getElementById("destinatario");
                    if(data.combo !== ''){
                        destinatario.innerHTML = data.combo;
                        var text = destinatario.options[destinatario.selectedIndex].text;
                        document.getElementById("txt-direc").value = text;
                        destinatario.removeAttribute('disabled');
                        
                    }
                    $(".chosen-select").trigger("chosen:updated");
                }else{
                    swal(
                        {
                            title: "Advertencia",
                            text: "El cliente seleccionado no posee una dirección de envío principal. Debe crear una.",
                            type: "warning",
                            showCancelButton: false,
                            allowEscapeKey: false
                        }, function(confirm){
                            var cliente_clave = $("#cliente").val(),
                                cliente = $(`#cliente option[value='${cliente_clave}']`).html();
                            $("#agregar_cliente").val(cliente);
                            $("#hidden_agregar_cliente").val(cliente_clave);
                            $("#modal_destinatario").modal('show')
                        }
                    );
                }
            }).error(function(data)
            {
                console.log("ERROR: ", data);
            });
            */
        });

        function BuscarDestinatario(cliente)
        {
            document.getElementById("agregar_destinatario").removeAttribute("disabled");
            $.ajax({
                url: '/api/nuevospedidos/lista/index.php',
                data: {
                    action: 'getDestinatario',
                    cliente: cliente
                },
                dataType: 'json',
                method: 'GET',
                beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                },
                success: function(data) 
                {
                    console.log("SUCCESS: ", data);
                    var destinatario = document.getElementById("destinatario");
                    if(data.combo !== '' && data.find == true){
                        destinatario.innerHTML = data.combo;
                        var text = destinatario.options[destinatario.selectedIndex].text;
                        document.getElementById("txt-direc").value = text;
                        destinatario.removeAttribute('disabled');
                    }
                    else
                    {
                        $("#destinatario, #agregar_destinatario").prop("disabled", true);
                    }

                    $(".chosen-select").trigger("chosen:updated");
                }, error: function(data){
                    console.log("ERROR: ", data);
                }

            });
        }

        $("#desc_cliente").on("change", function(e){
            var cliente = $(this).val();
            BuscarDestinatario(cliente);
            //console.log("Cliente = ", cliente);
        });

        $("#destinatario").on("change", function(e){
            var destinatario = document.getElementById("destinatario");
            var text = destinatario.options[destinatario.selectedIndex].text;
            document.getElementById("txt-direc").value = text;
        });

        $("#almacen").on("change", function(e){
            $("#articulos").modal('toggle')
            var articulos = document.getElementById('articulo');
            articulos.innerHTML = '';
            var seleccione = document.createElement('option');
            seleccione.value = '';
            seleccione.innerHTML = 'Seleccione Artículo';
            articulos.appendChild(seleccione);
            $.ajax({
                url: '/api/articulos/lista/index.php',
                method: 'GET',
                data: {
                    almacen: e.target.value
                },
                datatype: 'json'
            })
            .done(function(data){
                data = JSON.parse(data);
                if(data.length > 0){
                    for(var i = 0; i < data.length; i++){
                        var articulo = document.createElement('option');
                        articulo.value = data[i].cve_articulo;
                        articulo.innerHTML = `${data[i].cve_articulo} - ` + `${data[i].des_articulo}`;
                        articulos.appendChild(articulo);
                    }
                }
            })
            .always(function(){
                $(".chosen-select").trigger("chosen:updated");
                $("#articulos").modal('toggle')
            });
        });

        $("#btnRegistrar").on('click', function(e){
            validarUsuarioAdminstrador();
        });

        $("#btnAgregar").on('click', function(e){
            var clave = $("#articulo").val(),
                articulo = $.trim($(`#articulo option[value='${clave}']`).html().split(' - ')[1]),
                piezas = $("#cantPzas").val(),
                caducidad = $("#caducidadMin").val(),
                fecha = moment().format('DD-MM-YYYY HH:mm:ss'),
                cliente_clave = $("#cliente").val(),
                almacen = $("#almacen").val(),
                folio = $("#folio").val(),
                cliente = $("#cliente").val(), //$(`#cliente option[value='${cliente_clave}']`).html(),
                destinatario = $("#destinatario").val(),
                fecha_compromiso = $("#fechaSolicitud").val(),
                prioridad_clave = $("#prioridad").val(),
                prioridad = $(`#prioridad option[value='${prioridad_clave}']`).html(),
                existe = $('#grid-table').jqGrid ('getRowData', clave);
            if(!$.isEmptyObject(existe)){
                swal("Error", "El artículo que intentas añadir ya existe", "error");
            }
            else if(clave === ''){
                swal("Error", "Seleccione artículo", "error");
            }
            else if(almacen === ''){
                swal("Error", "Seleccione almacen", "error");
            }
            else if(folio === ''){
                swal("Error", "Ingrese folio", "error");
            }
            else if(piezas === ''){
                swal("Error", "Ingrese cantidad de piezas", "error");   
            }
            else if(cliente_clave === ''){
                swal("Error", "Seleccione cliente", "error");   
            }
            else if(destinatario === ''){
                swal("Error", "Ingrese destinatario", "error");   
            }
            else if(fecha_compromiso === ''){
                swal("Error", "Seleccione fecha de compromiso", "error");   
            }
            else if(prioridad_clave === ''){
                swal("Error", "Seleccione prioridad", "error");   
            }
            else{
                var grid = $("#grid-table");
                grid.jqGrid('addRowData', 
                    clave, 
                    {
                        c_cliente: cliente_clave,
                        c_prioridad: prioridad_clave,
                        clave: clave,
                        articulo: articulo,
                        cantPiezas: piezas,
                        caducidad: caducidad,
                        fecha_registro: fecha,
                        cliente: cliente,
                        destinatario: destinatario,
                        fecha_compromiso: fecha_compromiso,
                        prioridad: prioridad
                    }, 
                    'last');
                $("#totalArt").val(grid.getGridParam("reccount"));
                $("#totalPiez").val(getTotalPiezas);
                $("#articulo").val('');
                $("#caducidadMin").val('');
                $("#cantPzas").val('');
                $('.chosen-select').trigger('chosen:updated');
            }
        })
    });

    function getTotalPiezas(){
        var data = $('#grid-table').jqGrid('getGridParam','data');
        var total = 0;
        for(var i = 0; i < data.length; i++){
            total += parseFloat(data[i].cantPiezas);
        }
        return total;
    }
    function resetForm(){
        document.getElementById('myform').reset();
        $("#grid-table").jqGrid("clearGridData");
        $('.chosen-select').trigger('chosen:updated');
    }

    function utf8_decode (strData) { 

      var tmpArr = []
      var i = 0
      var c1 = 0
      var seqlen = 0

      strData += ''

      while (i < strData.length) {
        c1 = strData.charCodeAt(i) & 0xFF
        seqlen = 0

        if (c1 <= 0xBF) {
          c1 = (c1 & 0x7F)
          seqlen = 1
        } else if (c1 <= 0xDF) {
          c1 = (c1 & 0x1F)
          seqlen = 2
        } else if (c1 <= 0xEF) {
          c1 = (c1 & 0x0F)
          seqlen = 3
        } else {
          c1 = (c1 & 0x07)
          seqlen = 4
        }

        for (var ai = 1; ai < seqlen; ++ai) {
          c1 = ((c1 << 0x06) | (strData.charCodeAt(ai + i) & 0x3F))
        }

        if (seqlen === 4) {
          c1 -= 0x10000
          tmpArr.push(String.fromCharCode(0xD800 | ((c1 >> 10) & 0x3FF)))
          tmpArr.push(String.fromCharCode(0xDC00 | (c1 & 0x3FF)))
        } else {
          tmpArr.push(String.fromCharCode(c1))
        }

        i += seqlen
      }

      return tmpArr.join('')
    }

    function folio_consecutivo() {
      $.ajax({
        type: "POST",
        dataType: "json",
        data: {
          action : "consecutivo_folio"
        },
        beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }},
        url: '/api/nuevospedidos/update/index.php',
        success: function(data) 
        {
          if (data.success == true) 
          {
/*
            var dt = new Date();
            var year = dt.getUTCFullYear(); 
            var month = ("0" + (dt.getMonth() + 1)).slice(-2);
*/
            
            //$("#folio").val("S" + year + month + data.Consecutivo);
            $("#folio").val(data.Consecutivo);
            
          }
        }, error: function(data)
        {
          console.log("ERROR data folio_consecutivo = ",data);
        }
      });
    }
    folio_consecutivo();


    $("#exportExcel").on("click", function(){
        window.exportDataGrid.exportExcel("grid-table","Lista_Articulos.xls");
    });
    $("#exportPDF").on("click", function(){
        window.exportDataGrid.exportPDF("grid-table","Lista_Articulos.pdf");
    });

</script>
