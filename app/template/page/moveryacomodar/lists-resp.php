<?php
    $listaZR = new \CortinaEntrada\CortinaEntrada();
    $listaProvee = new \Proveedores\Proveedores();
    $listaAlma = new \Almacen\Almacen();
    $listaArtic = new \Articulos\Articulos();
    $listaUser = new \Usuarios\Usuarios();
    $listaOC = new \OrdenCompra\OrdenCompra();
    $listaAP = new \AlmacenP\AlmacenP();
    $listaProto= new \Protocolos\Protocolos();
    $listaUbic= new \Ubicaciones\Ubicaciones();
?>
<link href="/css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">
<link href="/css/plugins/ladda/select2.css" rel="stylesheet"/>
<link href="/css/plugins/sweetalert/sweetalert.css" rel="stylesheet">
<link href="/css/switchery.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.1.0/css/all.css" integrity="sha384-lKuwvrZot6UHsBSfcMvOkWwlCMgc0TaWr+30HWe3a4ltaBwTZhyTEggF5tJv8tbt" crossorigin="anonymous">

<div class="wrapper wrapper-content  animated fadeInRight" id="FORM" >
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-title">
                    <div class="row">
                        <div class="col-lg-4" id="_title">
                            <h3>Acomodo y traslado*</h3>
                        </div>
                    </div>
                </div>
                <form id="myform">
                    <div class="ibox-content">
                        <div class="row">
                            <label>Tipo </label>
                            <div class="col-lg-12 text-center">
                                <div class="form-group">
                                    <label>Acomodo</label>
                                    <input id="checkAcomodo" type="checkbox" class="js-switch" title="Modo automatico"/>
                                    <label>Traslado</label>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Almacén*</label>
                                        <select class="form-control" id="almacen">
                                            <option value="">Seleccione</option>
                                            <?php foreach( $listaAP->getAll() AS $a ): ?>
                                                <option value="<?php echo $a->id; ?>"><?php echo $a->nombre; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div> </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Usuario*</label>
                                        <select class="form-control" id="usuario" disabled>
                                            <?php foreach( $listaUser->getAll() AS $a ): ?>
                                                <option value="<?php echo $a->cve_usuario; ?>"><?php echo "(".$a->cve_usuario.") ".$a->nombre_completo; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div> 
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12 b-b">
                                <h4 class="text-center">Zona Origen</h4>
                                <div class="col-md-6">
                                    <div class="form-group" id="zonaalmacenajeif">
                                        <label>Zona de Almacenaje*</label>
                                        <select class="form-control" id="zonaalmacenajei">
                                            <option value="">Seleccione</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group" id="zonarecepcionif">
                                        <label>Zona de Recepción</label>
                                        <select class="form-control" id="zonarecepcioni">
                                            <option value="">Seleccione</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group" id="valor_total_pendiente_pa">
                                       <label>TOTAL PENDIENTE DE ACOMODO</label>
                                       <input  class="form-control" id="valor_total_pendiente" placeholder="0.00" disabled></input>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group" id="ubicacionif">
                                        <label>Ubicación*</label>
                                        <select class="form-control" id="ubicacioni">
                                            <option value="">Seleccione</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-12 b-b"><br>
                                <label></label>
                                <div class="ibox-content">
                                    <div class="jqGrid_wrapper">
                                        <table id="grid-tabla"></table>
                                        <div id="grid-page"></div>
                                        <input type="hidden" id="ocupadoi">
                                        <input type="hidden" id="pesoi">
                                        <input type="hidden" id="capacidadi">
                                        <input type="hidden" id="pesomaxi">
                                        <input type="hidden" id="amixi">
                                        <label class="pull-right" id="ziocupado">&nbsp;Ocupado m3: 0 </label>
                                        <label class="pull-right" id="zipeso">&nbsp;Peso m3: 0 </label>
                                        <label class="pull-right" id="zicapacidad">&nbsp;Capacidad m3: 0 </label>
                                        <label class="pull-right" id="zipesomax">&nbsp;Peso Máximo m3: 0 </label>
                                        <label class="pull-right" id="zixocupado">&nbsp;% Ocupado: 0 </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <h4 class="text-center">Zona Destino</h4>
                                <div class="col-md-6">
                                    <div class="form-group" id="zonaalmacenajeff">
                                        <label>Zona de Almacenaje*</label>
                                        <select class="form-control" id="zonaalmacenajef">
                                            <option value="">Seleccione</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group" id="zonarecepcionff">
                                        <label>Zona de Recepción</label>
                                        <select class="form-control" id="zonarecepcionf">
                                            <option value="">Seleccione</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Ubicación*</label>
                                        <select class="form-control" id="ubicacionf">
                                            <option value="">Seleccione</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-6 b-t">
                                <div class="form-group">
                                    <label>Articulo | Pallet | Contenedor</label>
                                    <input type="text" class="form-control"  disabled="" id="articuloi" value="">
                                    <input type="hidden" id="cve_articuloi"/>
                                    <input type="hidden" id="ID_Proveedor"/>
                                    <input type="hidden" id="apesoi"/>
                                    <input type="hidden" id="idy_ubicai"/>
                                    <input type="hidden" id="acvi"/>
                                    <input type="hidden" id="lotei"/>
                                    <input type="hidden" id="rowi"/>
                                </div>
                                <div class="form-group"><label>Cantidad a mover*</label> <input disabled id="cantrecibi" oninput="this.value = this.value.replace(/[^0-9]/g, '');" type="text" placeholder="Cantidad Recibida" class="form-control"></div>
                                <input id="xpiezai" type="hidden" placeholder="Cantidad Recibida" class="form-control">
                                <input id="xcajai" type="hidden" placeholder="Cantidad Recibida" class="form-control">
                                <input id="xpalleti" type="hidden" placeholder="Cantidad Recibida" class="form-control">
                                <div class="form-group"><label id="label-pesoA">Peso por unidad del articulo / Peso total del articulo</label><input id="input-pesoA" disabled type="text" class="form-control"></div>
                                <div class="form-group"><label id="label-volumenA">Volumen por unidad del articulo / Volumen total del articulo</label><input id="input-volumenA" disabled type="text" class="form-control"></div>
                            </div>
                            <div class="col-lg-6 b-t">
                                <div class="form-group">
                                    <label>Unidad de Medida</label>
                                    <select class="form-control" id="umedidai">
                                        <option value="1">Por Pieza</option>
                                        <option value="2">Por Caja</option>
                                        <option value="3">Por Pallet</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label id="label-cantT">Cantidad Total</label> 
                                    <input disabled id="ctotali" type="text" placeholder="Cantidad Recibida" class="form-control">
                                </div>
                                <div class="form-group row">
                                    <div class="col-lg-2">
                                        <label id="label-pesoU">Peso Ocupado</label>
                                        <input id="porcentaje_p_ocupado" disabled type="text" class="form-control">
                                    </div>
                                    <div class="col-lg-10">
                                        <label id="label-pesoU">Peso MAX de la ubicacion / Peso Ocupado de la Ubicacion</label>
                                        <input id="input-pesoU" disabled type="text" class="form-control">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-lg-2">
                                        <label id="label-pesoU">Volumen Ocupado</label>
                                        <input id="porcentaje_v_ocupado" disabled type="text" class="form-control">
                                    </div>
                                    <div class="col-lg-10">
                                        <label id="label-volimenU">Volumen MAX de la ubicacion / Volumen Ocupado de la Ubicacion</label>
                                        <input id="input-volumenU" disabled type="text" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <center><a href="#" id="addrow"><button type="button" class="btn btn-primary" id="btnCancel">Mover a Zona Destino </button></a>&nbsp;&nbsp;</center>
                            </div>
                            <div class="col-lg-12">
                                <label></label>
                                <div class="ibox-content">
                                    <div class="jqGrid_wrapper">
                                        <table id="grid-tabla2"></table>
                                        <div id="grid-page2"></div>
                                        <input type="hidden" id="ocupadof">
                                        <input type="hidden" id="pesof">
                                        <input type="hidden" id="capacidadf">
                                        <input type="hidden" id="pesomaxf">
                                        <input type="hidden" id="amixf">
                                        <label class="pull-right" id="zfocupado">&nbsp;Ocupado m3: 0 </label>
                                        <label class="pull-right" id="zfpeso">&nbsp;Peso m3: 0 </label>
                                        <label class="pull-right" id="zfcapacidad">&nbsp;Capacidad m3: 0 </label>
                                        <label class="pull-right" id="zfpesomax">&nbsp;Peso Máximo m3: 0 </label>
                                        <label class="pull-right" id="zfxocupado">&nbsp;% Ocupado: 0 </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" id="hiddenAction">
                    <input type="hidden" id="hiddenID_Aduana">
                    <input type="hidden" id="guardo">
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="mod" role="dialog"> 
    <div class="vertical-alignment-helper">
        <div class="modal-dialog vertical-align-center modal-lg" style="width: auto;">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4>QA</h4>
                </div>
                <div class="modal-body">
                    <div class="ibox-content">
                        <label class="checkbox-inline" for="checkqa">
                            <input type="hidden" id="pro" />
                            <input type="checkbox" name="checkqa" id="checkqa" value="1">
                            <label>El producto se encuentra en QA</label>
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" id="acep" class="btn btn-default">Si</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">No</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-info" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="padding:10px 50px;">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h2 >Resumen de Movimiento.</h2>
            </div>
            <div class="modal-body" style="padding:10px 50px;">
                <div class="form-group">
                    <label ><span class="fa fa-exchange-alt"></span> Tipo de Movimiento</label>
                    <input type="text" class="form-control" id="input-movi" disabled="true">
                </div>
                <div class="form-group">
                    <label ><span class="fa fa-barcode"></span> Articulo</label>
                    <input type="text" class="form-control" id="input-arti" disabled="true">
                </div>
                <div class="form-group">
                    <label ><span class="fa fa-box-open"></span> Origen</label>
                    <input type="text" class="form-control" id="input-origen" disabled="true">
                </div>
                <div class="form-group">
                    <label ><span class="fa fa-box"></span> Destino</label>
                    <input type="text" class="form-control" id="input-desti" disabled="true">
                </div>
                <div class="form-group">
                    <label ><span class="fas fa-elementor"></span> Cantidad</label>
                    <input type="text" class="form-control" id="input-cant" disabled="true">
                </div>
                <div class="form-group">
                    <label ><span class="fas fa-percent"></span> Peso / Volumen</label>
                    <input type="text" class="form-control" id="input-peso" disabled="true">
                </div>
                <div class="form-group">
                    <label ><span class="fa fa-user"></span> Usuario</label>
                    <input type="text" class="form-control" id="input-user" disabled="true">
                </div>
            </div>
            <div class="modal-footer">
                <button onclick="save()" class="btn btn-success btn-block"><span class="glyphicon glyphicon-transfer"></span><span> Mover</span></button>
                <button class="btn btn-danger btn-block" data-dismiss="modal"><span class="glyphicon glyphicon-remove"></span><span> Cancelar</span></button>
            </div>
        </div>
    </div>
</div>

<!-- Mainly scripts -->
<script src="/js/jquery-2.1.1.js"></script>
<script src="/js/bootstrap.min.js"></script>
<script src="/js/plugins/metisMenu/jquery.metisMenu.js"></script>
<script src="/js/plugins/slimscroll/jquery.slimscroll.min.js"></script>

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
<script src="/js/switchery.min.js"></script>

<!-- Select -->
<script src="/js/select2.js"></script>

<!-- Data picker -->
<script src="/js/plugins/sweetalert/sweetalert.min.js"></script>

<script type="text/javascript">

    var initSwich = '';
    if(typeof Switchery !== 'undefined')
        initSwich = new Switchery(document.querySelector('.js-switch'));

    var input_arti = document.getElementById('input-arti'),
        input_origen = document.getElementById('input-origen'),
        input_desti = document.getElementById('input-desti'),
        input_cant = document.getElementById('input-cant'),
        input_peso = document.getElementById('input-peso'),
        input_user = document.getElementById('input-user'),
        input_movi = document.getElementById('input-movi'),
        DATA = {idyorigen : "", cve_almacf : "", cve_almaci : "", accion : ""};

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
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/almacenPredeterminado/index.php',
            success: function(data) {
                if (data.success == true) 
                {
                    document.getElementById('almacen').value = data.codigo.id;
                    fillSelectZona();
                }
            },
            error: function(res){
                window.console.log(res);
            }
        });
    }
    selectUser();
    function selectUser()
    {
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                idUser: '<?php echo $_SESSION["id_user"]?>',
                action: 'search_usuario'
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
                    document.getElementById('usuario').value = data.data.cve_usuario;
                }
            },
            error: function(res) {
                window.console.log(res);
            }
        });
    }
    almacenPrede();
    $(function($) {
        var grid_selector = "#grid-tabla";
        var pager_selector = "#grid-page";

        //resize to fit page size
        $(window).on('resize.jqGrid', function () {
            $(grid_selector).jqGrid( 'setGridWidth', $(".ibox-content").width() - 60 );
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
        })

        $(grid_selector).jqGrid({ //12
            url:'#',
            datatype: "json",
            shrinkToFit: false,
            mtype: 'POST',
            colNames:['Seleccionar','idy_ubica','Folio Entrada','Tipo','Clave','Descripcion','Lote','Caducidad','Serie','Peso','Volumen Ocupado','Existencia','Proveedor','id_proveedor','id_articulo','Almacen','Zona de Almacenaje'],
            colModel:[
                {name:'myac',index:'', width:80, fixed:true, sortable:false, resize:false, formatter:imageFormat, frozen : true},
                {name:'idy_ubica',index:'idy_ubica',editable:false,width:150, sortable:false, hidden: true},
                {name:'folio',index:'folio',editable:false,width:100, sortable:false, hidden: false},
                {name:'tipoa',index:'tipo',width:100,editable:false, sortable:false, hidden: false},
                {name:'clave',index:'clave',hidden:false, width:100,editable:false, sortable:false},
                {name:'descripcion',index:'descripcion', editable:false,width:180, sortable:false},
                {name:'lote',index:'lote', editable:false,width:80, sortable:false},
                {name:'caducidad',index:'caducidad', editable:false,width:100, sortable:false},
                {name:'serie',index:'serie', editable:false,width:80, sortable:false},
                {name:'peso_total',index:'peso_total', editable:false, width:100, sortable:false},
                {name:'volumen_ocupado',index:'volumen_ocupado',width:100, editable:false, sortable:false},
                {name:'num_existencia',index:'num_existencia',width:100, editable:false, sortable:false},
                {name:'proveedor',index:'proveedor',editable:false,width:150,sortable:false, hidden: false},
                {name:'id_proveedor',index:'id_proveedor',editable:false,width:150,sortable:false, hidden: true},
                {name:'id_articulo',index:'id_articulo',editable:false,width:150, sortable:false, hidden: true},
                {name:'almacen',index:'almacen',editable:false,width:150,sortable:false, hidden: false},
                {name:'zona',index:'zona',editable:false,width:150, sortable:false, hidden: false},
            ],
            rowNum:10,
            rowList:[10,20,30],

            //pager: pager_selector,
            sortname: 'cve_gpoart',
            viewrecords: true,
            sortorder: "desc"
            //loadComplete : totalPendiente()
        });
      
        //PR boton traslado
        function imageFormat( cellvalue, options, rowObject ){
            var id = options.rowId;
            var html = '<a href="javascript:void(0)" onclick="comparar(\''+id+'\')"><i class="fa fa-check" alt="Editar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            return html;
        }
    });

    $(function($) {
        var grid_selector = "#grid-tabla2";
        var pager_selector = "#grid-page2";

        //resize to fit page size
        $(window).on('resize.jqGrid', function () {
            $(grid_selector).jqGrid( 'setGridWidth', $(".ibox-content").width() - 60 );
        })
        //resize on sidebar collapse/expand
        var parent_column = $(grid_selector).closest('[class*="col-"]');
        $(document).on('settings.ace.jqGrid' , function(ev, event_name, collapsed) {
            if( event_name === 'sidebar_collapsed' || event_name === 'main_container_fixed' ) {
                //setTimeout is for webkit only to give time for DOM changes and then redraw!!!
                setTimeout(function(){
                    $(grid_selector).jqGrid( 'setGridWidth', parent_column.width() );
                }, 0);
            }
        })

        $(grid_selector).jqGrid({
            url:'#',
            datatype: "json",
            shrinkToFit: false,
            mtype: 'POST',
            colNames:['Seleccionar','idy_ubica','Almacen',' Zona de Almacenaje','Ubicacion','Clave Articulo','id_articulo','Articulo','Lote','Caducidad','Serie','Volumen Ocupado','Existencia','Peso','Proveedor','id_proveedor'],/*'Calle y Numero','Colonia','Ciudad','Estado','Pais','Codigo Postal','RFC','Telefono1','Telefono2'*/
            colModel:[
                {name:'myac',index:'', width:80, fixed:true, sortable:false, resize:false, formatter:imageFormat, frozen : true},
                {name:'idy_ubica',index:'idy_ubica',editable:false,width:150, sortable:false, hidden: true},
                {name:'almacen',index:'almacen',editable:false,width:150,sortable:false, hidden: false},
                {name:'zona',index:'zona',editable:false,width:150, sortable:false, hidden: false},
                {name:'ubicacion',index:'ubicacion',width:250,editable:false, sortable:false, hidden: false},
                {name:'cve_articulo',index:'cve_articulo',hidden:false, width:150,editable:false, sortable:false},
                {name:'id_articulo',index:'id_articulo',hidden:true, width:150,editable:false, sortable:false},
                {name:'articulo',index:'articulo', editable:false,width:250, sortable:false},
                {name:'lote',index:'lote', editable:false,width:150, sortable:false},
                {name:'caducidad',index:'caducidad', editable:false,width:150, sortable:false},
                {name:'serie',index:'serie', editable:false,width:150, sortable:false},
                {name:'volumen_ocupado',index:'volumen_ocupado',width:150, editable:false, sortable:false},
                {name:'num_existencia',index:'num_existencia',width:150, editable:false, sortable:false},
                {name:'peso_total',index:'peso_total', editable:false, sortable:false},
                {name:'proveedor',index:'proveedor',editable:false,width:150,sortable:false, hidden: false},
                {name:'id_proveedor',index:'id_proveedor',editable:false,width:150,sortable:false, hidden: true},
            ],
            rowNum:10,
            rowList:[10,20,30],
            //pager: pager_selector,
            sortname: 'cve_gpoart',
            viewrecords: true,
            sortorder: "desc"
        });

        function imageFormat( cellvalue, options, rowObject )
        {
            var id = options.rowId;
            var html = '';
            html += '<a href="javascript:void(0)" onclick="getDataZF(\''+id+'\')"><i class="fa fa-check-circle" alt="Editar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            return html;
        }
    });
  
    $("#checkAcomodo").change(function() {
        if(!this.checked) 
        {
            $("#zonaalmacenajeif").hide();
            $("#zonarecepcionif").show();
            $("#zonaalmacenajeff").show();
            $("#zonarecepcionff").hide();
            $("#ubicacionif").hide();
            $("#articuloi").val("");
            $("#articulof").val("");
            $("#zonaalmacenajef").val("");
            $("#zonaalmacenajei").val("");
            $("#zonarecepcioni").val("");
            $("#ubicacionif").val("");
            $("#ubicacioni").val("");
            $("#addrow2").hide();
            $("#grid-tabla2").jqGrid("clearGridData");
            $("#grid-tabla").jqGrid("clearGridData");
        }
        else
        {
            $("#zonaalmacenajeif").show();
            $("#zonarecepcionif").hide();
            $("#zonaalmacenajeff").show();
            $("#zonarecepcionff").hide();
            $("#ubicacionif").show();
            $("#grid-tabla2").jqGrid("clearGridData");
            $("#grid-tabla").jqGrid("clearGridData");
            $("#articuloi").val("");
            $("#zonaalmacenajef").val("");
            $("#zonaalmacenajei").val("");
            $("#zonarecepcioni").val("");
            $("#ubicacionif").val("");
            $("#ubicacioni").val("");
        }
    });

    $('#zonaalmacenajei').change(function(e) {
        var almacen= $(this).val();
        $.ajax({
            type: "POST",
            dataType: "json",
            url: '/api/ubicaciones/update/index.php',
            data: {
                action : "ubicacionNoVacias",
                zona : almacen,
                excludeInventario: 0
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }},
            success: function(data) {
                var options = $("#ubicacioni");
                options.empty();
                options.append(new Option("Seleccione", ""));
                for (var i=0; i<data.ubicaciones.length; i++)
                {
                    options.append(new Option(data.ubicaciones[i].ubicacion, data.ubicaciones[i].idy_ubica));
                }
            }
        });
    });

    $('#zonaalmacenajef').change(function(e) {
        var almacen= $(this).val();
        calculatePesoUbi();
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                zona : almacen,
                action : "ubicacionNoLlenas",
                excludeInventario: 0
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }},
            url: '/api/ubicaciones/update/index.php',
            success: function(data) {
                //console.log(data);
                var select = document.getElementById('ubicacionf'),
                    node = data.ubicaciones,
                    options = "<option value =''>Seleccione Ubicacion ("+node.length+")</option>";

                for (var i=0; i < node.length; i++)
                {
                  //console.log("123",node);
                  //console.log("aco2",node[0][2]);
                  //console.log("aco3",node.AcomodoMixto);
                    
                  var state = "";
                    if(node.AcomodoMixto === "S")
                        state = " (MIXTO)";
                    options += "<option value = "+node[i].idy_ubica+">"+node[i].ubicacion+state+"</option>";
                }
                select.innerHTML = options;
            }
        });
    });
    //EDG //PR java tabla acomodo //12
    $('#zonarecepcioni').change(function(e) {
        $("#grid-tabla").jqGrid('clearGridData');
        var almacen= $(this).val();
        var tipo_env = document.getElementById("checkAcomodo").checked ? 'ubicacion' : 'area';

       // console.log("Almacen = "+almacen+" - Tipo = "+tipo_env);
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                tipo: tipo_env,
                cve_ubicacion : almacen,
                action : "getArticulosPendientesAcomodo"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }},
            url: '/api/ubicaciones/update/index.php',
            success: function(data) 
            {
                for (var i=0; i<data.articulos.length; i++)
                {
                  /*if(data.articulos[i].lote == ""){data.articulos[i].lote = "";}
                  if(data.articulos[i].lote == ""){data.articulos[i].caducidad = "";}*/
                  emptyItem=[{
                      idy_ubica:        data.articulos[i].folio,
                      clave:            data.articulos[i].clave,
                      tipoa:            data.articulos[i].tipo,
                      folio:            data.articulos[i].folio,
                      descripcion:      data.articulos[i].descripcion,
                      num_existencia:   data.articulos[i].num_existencia,
                      peso_total:       data.articulos[i].peso_total,
                      id_articulo:      data.articulos[i].id_articulo,
                      lote:             data.articulos[i].lote,
                      caducidad:        data.articulos[i].caducidad,
                      serie:            data.articulos[i].numero_serie,
                      volumen_ocupado:  data.articulos[i].volumen_ocupado,
                      proveedor:        data.articulos[i].proveedor,
                      id_proveedor:     data.articulos[i].id_proveedor,
                      almacen:          $("#almacen option:selected").text(),
                      zona:             $("#zonarecepcioni option:selected").text()
                   }];
                  $("#grid-tabla").jqGrid('addRowData',0,emptyItem);
                  if(i == data.articulos.length-1)
                  {
                    totalPendiente();
                  }
                }

                $("#ocupadoi").val(0);
                $("#pesoi").val(0);
                $("#capacidadi").val(0);
                $("#pesomaxi").val(0);
                $("#ziocupado").empty();
                $("#zipeso").empty();
                $("#zicapacidad").empty();
                $("#zipesomax").empty();
                $("#zixocupado").empty();
            }
        });
    });
    //PR java tabla traslado //12
    $('#ubicacioni').change(function(e){
        $("#grid-tabla").jqGrid('clearGridData');
        var almacen= $(this).val();
        $("#idy_ubicai").val(almacen);
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                idy_ubica : almacen,
                action : "articulosenUbicacion"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }},
            url: '/api/ubicaciones/update/index.php',
            success: function(data) 
            {
                $("#grid-tabla").jqGrid("clearGridData");
                var peso=0;
                var peso2=0;
                for (var i=0; i<data.articulos.length; i++)
                {
                    emptyItem=[{
                        idy_ubica:data.articulos[i].idy_ubica,
                        cve_articulo:data.articulos[i].cve_articulo,
                        articulo:data.articulos[i].articulo,
                        volumen_ocupado:data.articulos[i].volumen_ocupado,
                        num_existencia:data.articulos[i].num_existencia,
                        peso_total:data.articulos[i].peso_total,
                        id_articulo:data.articulos[i].id_articulo,
                        lote:data.articulos[i].lote,
                        almacen:$("#almacen option:selected").text(),
                        ubicacion:$("#ubicacioni option:selected").text(),
                        zona:$("#zonaalmacenajei option:selected").text(),
                        proveedor:data.articulos[i].proveedor,
                        id_proveedor:data.articulos[i].ID_Proveedor
                    }];
                    peso=peso+parseFloat(data.articulos[i].volumen_ocupado);
                    peso2=peso2+parseFloat(data.articulos[i].peso_total);
                    $("#grid-tabla").jqGrid('addRowData',0,emptyItem);
                }
                $("#ocupadoi").val(peso.toFixed(6));
                $("#pesoi").val(peso2.toFixed(2));
                $("#capacidadi").val(data.capacidad[0].capacidad_volumetrica);
                $("#pesomaxi").val(data.capacidad[0].peso);
                $("#ziocupado").empty();
                $("#ziocupado").html("&nbsp;Volumen Ocupado: "+peso.toFixed(6)+" ");
                $("#zipeso").empty();
                $("#zipeso").html("&nbsp;Peso Ocupado: "+peso2.toFixed(2)+" ");
                $("#zicapacidad").empty();
                $("#zicapacidad").html("&nbsp;Volumen Máximo: "+data.capacidad[0].capacidad_volumetrica+" ");
                $("#zipesomax").empty();
                $("#zipesomax").html("&nbsp;Peso Máximo: "+data.capacidad[0].peso+" ");
                $("#zfxiocupado").empty();
                var ocupado = (parseFloat(peso)/parseFloat(data.capacidad[0].capacidad_volumetrica)*100);
                $("#zixocupado").empty();
                $("#zixocupado").html("&nbsp;% de Ocupación "+ocupado.toFixed(2)+" ");
            }
        });
    });

    $('#ubicacionf').change(function(e){
        $("#grid-tabla2").jqGrid('clearGridData');
        calculatePesoUbi();
        var almacen= $(this).val();
        $("#idy_ubicaf").val(almacen);
        $.ajax({
            type: "POST",
            dataType: "json",
            url: '/api/ubicaciones/update/index.php',
            data: {
                action : "articulosenUbicacion",
                idy_ubica : almacen,
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }},
            success: function(data) 
            {
                $("#grid-tabla2").jqGrid("clearGridData");
                var peso  = 0;
                var peso2 = 0;
                for (var i=0; i<data.articulos.length; i++)
                {
                  if(data.articulos[i].volumen_ocupado == null){
                    data.articulos[i].volumen_ocupado = "0";
                  }
                  if(data.articulos[i].peso_total == null){
                    data.articulos[i].peso_total = "0";
                  }
                  //console.log(data.articulos[i].volumen_ocupado);
                    emptyItem=[{
                        idy_ubica:data.articulos[i].idy_ubica,
                        cve_articulo:data.articulos[i].cve_articulo,
                        articulo:data.articulos[i].articulo,
                        volumen_ocupado:data.articulos[i].volumen_ocupado,
                        num_existencia:data.articulos[i].num_existencia,
                        peso_total:data.articulos[i].peso_total,
                        id_articulo:data.articulos[i].id_articulo,
                        lote:data.articulos[i].lote,
                        almacen:$("#almacen option:selected").text(),
                        ubicacion:$("#ubicacionf option:selected").text(),
                        zona:$("#zonaalmacenajef option:selected").text(),
                        proveedor:data.articulos[i].proveedor,
                        id_proveedor:data.articulos[i].ID_Proveedor
                    }];
                    $("#grid-tabla2").jqGrid('addRowData',0,emptyItem);
                    peso  = peso  + parseFloat(data.articulos[i].volumen_ocupado);
                    peso2 = peso2 + parseFloat(data.articulos[i].peso_total);
                }
                
                var input_pesoU = document.getElementById('input-volumenU');
                input_pesoU.value = (data.capacidad[0].capacidad_volumetrica + " / " + peso.toFixed(6));
                $("#porcentaje_v_ocupado").val(parseInt((peso*100)/data.capacidad[0].capacidad_volumetrica)+" %");
              
                $("#ocupadof").val(peso.toFixed(6));
                $("#pesof").val(peso2.toFixed(2));
                $("#capacidadf").val(data.capacidad[0].capacidad_volumetrica);
                $("#pesomaxf").val(data.capacidad[0].peso);
                $("#zfocupado").empty();
                $("#zfocupado").html("&nbsp;Volumen Ocupado: "+peso.toFixed(6)+" ");
                $("#zfpeso").empty();
                $("#zfpeso").html("&nbsp;Peso Ocupado: "+peso2.toFixed(2)+" ");
                $("#zfcapacidad").empty();
                $("#zfcapacidad").html("&nbsp;Volumen Máximo: "+data.capacidad[0].capacidad_volumetrica+" ");
                $("#zfpesomax").empty();
                $("#zfpesomax").html("&nbsp;Peso Máximo: "+data.capacidad[0].peso+" ");
                var ocupado=(parseFloat(peso)/parseFloat(data.capacidad[0].capacidad_volumetrica)*100);
                $("#zfxocupado").empty();
                $("#zfxocupado").html("&nbsp;% de Ocupación "+ocupado.toFixed(2)+" ");
                $("#amixf").val(data.capacidad[0].amix);
                calculatePesoUbi(data.capacidad[0].peso, peso2);
            }
        });
    });

    $('#almacen').change(function(e){
        $("#grid-tabla").jqGrid('clearGridData');
        $("#ubicacionf").empty();
        $("#ubicacionf").append(new Option("Seleccione", ""));
        fillSelectZona();
    });

    function calculatePesoUbi(_MAX, _OCU)
    {
        var input_pesoU = document.getElementById('input-pesoU');
        if(_MAX)
        { 
            var MAX = parseFloat(_MAX).toFixed(2),
            OCU = parseFloat(_OCU).toFixed(2);
            input_pesoU.value = MAX+ " / "+OCU;
            calculatePesoMax(true);
            $("#porcentaje_p_ocupado").val(parseInt(((OCU * 100)/MAX),10)+"%");
        }
        else
        {
            input_pesoU.value = "";
        }
    }

    function calculatePesoMax(state)
    {
        var input_pesoA = document.getElementById('input-pesoA');
        var input_volumenA = document.getElementById('input-volumenA');
        if(state)
        { 
            var cantTotal = parseFloat(document.getElementById('ctotali').value),
            pesoUnidad = parseFloat(document.getElementById('apesoi').value),
            volumenUnidad = parseFloat(document.getElementById('acvi').value),
            cantReci = parseFloat(document.getElementById('cantrecibi').value),
            pesoActual = parseFloat(document.getElementById('pesof').value),
            volumenActual = parseFloat(document.getElementById('ocupadof').value),
            pesoMaximo = parseFloat(document.getElementById('pesomaxf').value),
            volumenMaximo = parseFloat(document.getElementById('capacidadf').value),
            pesoTotal = (pesoUnidad*cantReci) + pesoActual,
            volumenTotal = (volumenUnidad*cantReci) + volumenActual,
            select_ubica = document.getElementById('ubicacionf');
            if(cantTotal)
            {
                input_pesoA.value = pesoUnidad+ " / "+(pesoUnidad*cantReci);
                input_volumenA.value = volumenUnidad+ " / "+(volumenUnidad*cantReci)
              
                if(select_ubica.value)
                {
                    if(pesoTotal > pesoMaximo)
                    {
                        input_pesoA.style.borderColor = "red";
                    }
                    else
                    {
                        input_pesoA.style.borderColor = "green";
                    }
                  
                    if(volumenTotal > volumenMaximo)
                    {
                        input_volumenA.style.borderColor = "red";
                    }
                    else
                    {
                        input_volumenA.style.borderColor = "green";
                    }
                }
                else
                {
                    input_pesoA.style.borderColor = "";
                    input_volumenA.style.borderColor = "";
                }
            }
            else
            {
                input_pesoA.value = "";
                input_volumenA.value = "";
            }
        }
        else
        {
            input_pesoA.value = "";
            input_volumenA.value = "";
            input_pesoA.style.borderColor = "";
            input_volumenA.style.borderColor = "";
        }
    }

    function fillSelectZona()
    {
        var almacen= $('#almacen').val();
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                cve_almacenp : almacen,
                action : "traerZonaporAlmacen"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }},
            url: '/api/almacen/update/index.php',
            success: function(data) 
            {
                var options = $("#zonaalmacenajei");
                options.empty();
                options.append(new Option("Seleccione", ""));
                for (var i=0; i<data.zona_almacen.length; i++)
                {
                    options.append(new Option(data.zona_almacen[i].descripcion_almacen, data.zona_almacen[i].clave_almacen));
                }
                var options = $("#zonaalmacenajef");
                options.empty();
                options.append(new Option("Seleccione", ""));
                for (var i=0; i<data.zona_almacen.length; i++)
                {
                    options.append(new Option(data.zona_almacen[i].descripcion_almacen, data.zona_almacen[i].clave_almacen));
                }
            }
        });
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                id_almacen : almacen,
                action : "loadPorAlmacen2",
                excludeInventario: 0
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }},
            url: '/api/cortinaentrada/update/index.php',
            success: function(data) 
            {
                var options = $("#zonarecepcioni");
                options.empty();
                options.append(new Option("Seleccione", ""));
                for (var i=0; i<data.zonas.length; i++)
                {
                    options.append(new Option(data.zonas[i].desc_ubicacion, data.zonas[i].cve_ubicacion));
                }
            }
        });
    }   

    $("#cantrecibi").keyup(function(e){
        var cantidad =0;
        if ($("#umedidai").val()=="1")
        {
            cantidad = $(this).val();
            console.log(cantidad);
        }
        if ($("#umedidai").val()=="2")
        {
            cantidad = $(this).val()*$("#xcajai").val();
            console.log(cantidad);
        }
        if ($("#umedida").val()=="3")
        {
            cantidad = $(this).val()*$("#xpalleti").val();
            console.log(cantidad);
        }
        $("#ctotali").val(cantidad);
        calculatePesoMax(true);
    });
  
    function comparar(_codigo)
    {
       var rowData = $("#grid-tabla").jqGrid("getRowData", _codigo);
       if(rowData["tipoa"] != 'Contenedor')
       {
          console.log("2");
          getDataZI(_codigo);
       }
       else
       {
          console.log("3");
          getDataCON(_codigo);
       }
    }
    
    function getDataCON(_codigo)
    {
       var rowData = $("#grid-tabla").jqGrid("getRowData", _codigo);
       console.log ("data",rowData);
       $("#articuloi").val(rowData["clave"]);
       $("#cve_articuloi").val(rowData["clave"]);
       $("#ID_Proveedor").val(rowData["id_proveedor"]);
       $("#idy_ubicai").val(rowData["idy_ubica"]);
       $("#rowi").val(_codigo);
       document.getElementById('label-cantT').innerHTML = "Cantidad Total MAX(1)";
      
       $.ajax({
          dataType: "json",
          type: "POST",
          url: '/api/contenedores/update/index.php',
          data: {
              action : "loadcon",
              clave_contenedor : rowData["clave"],
          },
          beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }},
          success: function(data) 
          {
             console.log("idd", data.data.IDContenedor);
             if(rowData["clave"] != "")
                {
                   $("#cve_articuloi").val(data.data.IDContenedor);
                  console.log("123",$("#cve_articuloi").val(data.data.IDContenedor));
                }
            
             $("#cantrecibi").prop('disabled',true);
             $("#cantrecibi").val(1);
             $("#apesoi").val(rowData["peso_total"]);
             $("#input-pesoA").val(rowData["peso_total"]);
             $("#acvi").val(rowData["volumen_ocupado"]);
             $("#input-volumenA").val(rowData["volumen_ocupado"]);
             $("#umedidai").val(3);
             $("#ctotali").prop('disabled',true);
             $("#ctotali").val(1);
             $("#lotei").val(rowData["lote"]);
            
          }
       });
     }

    function getDataZI(_codigo)//12
    { //PR3
        var rowData = $("#grid-tabla").jqGrid("getRowData", _codigo);
        console.log ("data",rowData);
        $("#articuloi").val(rowData["descripcion"]);
        $("#cve_articuloi").val(rowData["clave"]);
        $("#ID_Proveedor").val(rowData["id_proveedor"]);
        $("#idy_ubicai").val(rowData["idy_ubica"]);
        $("#rowi").val(_codigo);
        document.getElementById('label-cantT').innerHTML = "Cantidad Total MAX( "+rowData["num_existencia"]+" )";
        $.ajax({
            dataType: "json",
            type: "POST",
            url: '/api/articulos/update/index.php',
            data: {
                action : "load",
                cve_articulo : rowData["id_articulo"],
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }},
            success: function(data) 
            {
              //EDG
              console.log("entro data");
                $("#cantrecibi").prop('disabled',false);
                $("#cantrecibi").val(0);
                $("#cantrecibi").val(0);
                $("#ctotali").val(0);
                calculatePesoMax();
                $("#xpiezai").val(1);
                $("#xcajai").val(data.num_multiplo);
                $("#xpalleti").val(data.num_multiplo*data.cajas_palet);
                $("#apesoi").val(data.peso);
                $("#acvi").val(data.ancho*data.alto*data.fondo/1000000000);
              
                if(rowData["lote"] != "")
                   {
                     console.log("entro lote");
                     $("#lotei").val(rowData["lote"]);
                   }
                else
                   {  
                     console.log("entro serie");
                     $("#lotei").val(rowData["serie"]);
                   }
             }
        });
    }

    $("#acep").click( function(){
        $.ajax({
            dataType: "json",
            type: "POST",
            data: {
                qa: $("#checkqa").val(),
                producto: $("#pro").val(),
                action : "qa"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }},
            url: '/api/ubicaciones/update/index.php',
            success: function(data) 
            {
                swal('Exito!','Asignado a QA','success');
            },
            error: function(res)
            {
                console.log(res);
            }
        });
    });
    //PR1
    $("#addrow").click( function() {
        var error="NINGUNO";
        var rowData = $("#grid-tabla").jqGrid("getRowData", $("#rowi").val());
        if (parseFloat(rowData["num_existencia"])<parseFloat($("#ctotali").val()))
        {
            swal('Error','Seleccionaste una cantidad mayor a su existencia','error');
            return;
        }
        if ($("#almacen").val()=="")
        {
            swal('Error','Selecciona un almacen','error');
            return;
        }
        if ($("#usuario").val()=="")
        {
            swal('Error','Selecciona un usuario','error');
            return;
        }
        if ($("#zonarecepcioni").val()=="" && $("#checkAcomodo").is(":checked")==false)
        {
            swal('Error','Selecciona Zona de recepcion inicial','error');
            return;
        }
        if ($("#zonaalmacenajei").val()=="" && $("#checkAcomodo").is(":checked")==true)
        {
            swal('Error','Selecciona Zona de Almacenaje inicial','error');
            return;
        }
        if ($("#zonaalmacenajef").val()=="")
        {
            swal('Error','Selecciona Zona de Almacenaje final','error');
            return;
        }
        if ($("#ubicacioni").val()=="" && $("#checkAcomodo").is(":checked")==true)
        {
            swal('Error','Selecciona ubicación inicial','error');
            return;
        }
        if ($("#ubicacionf").val()=="")
        {
            swal('Error','Selecciona ubicación final','error');
            return;
        }
        if($("#cve_articuloi").val()=="")
        {
            swal('Error','Seleccione un articulo','error');
            return;
        }
        if($("#cantrecibi").val()=="" || $("#cantrecibi").val()=="0")
        {
            swal('Error','Seleccione cantidad','error');
            return;
        }
        var pesoAMover =parseFloat($("#apesoi").val())*parseFloat($("#cantrecibi").val());
        var cantidadVAMover =parseFloat($("#acvi").val())*parseFloat($("#cantrecibi").val())
        var volumenActual= parseFloat($("#ocupadof").val());
        var pesoActual=parseFloat($("#pesof").val());
        var pesoMaximo= parseFloat($("#pesomaxf").val());
        var volumenMaximo= parseFloat($("#capacidadf").val());
        var select_almacena_a = document.getElementById('zonaalmacenajei'),
            select_ubica_a = document.getElementById('ubicacioni'),
            select_almacena_f = document.getElementById('zonaalmacenajef'),
            select_ubica_f = document.getElementById('ubicacionf'),
            select_area = document.getElementById('zonarecepcioni'),
            text_almacena_a = select_almacena_f.options.item(select_almacena_f.selectedIndex).text,
            text_ubica_a = select_ubica_a.options.item(select_ubica_a.selectedIndex).text,
            text_almacena_f = select_almacena_f.options.item(select_almacena_f.selectedIndex).text,
            text_ubica_f = select_ubica_f.options.item(select_ubica_f.selectedIndex).text,
            text_area = select_area.options.item(select_area.selectedIndex).text,
            text_origen;
        if ((pesoActual+pesoAMover)>pesoMaximo && $("#amixf").val()=="N")
        {
            swal('Error','El peso de los articulos a mover es mayor a la capacidad disponible de la ubicación','error');
            return;
        }
        if ((volumenActual+cantidadVAMover)>volumenMaximo)
        {
            swal('Error','El volumen de los articulos a mover es mayor a capacidad disponible de la ubicación','error');
            return;
        }
        var accion;
        var cve_almaci;
        var cve_almacf;
        var idyorigen;
        if (!$("#checkAcomodo").is(":checked"))
        {
            accion="acomodo";
            cve_almaci=$("#zonarecepcioni").val();
            cve_almacf=$("#zonaalmacenajef").val();
            idyorigen=$("#idy_ubicai").val();
            text_origen = text_area;
            input_movi.value = "ACOMODO";
        }
        else 
        {
            accion="traslado";
            cve_almaci=$("#zonaalmacenajei").val();
            cve_almacf=$("#zonaalmacenajef").val();
            idyorigen=$("#ubicacioni").val();
            text_origen = text_almacena_a+" ("+text_ubica_a+")";
            input_movi.value = "TRASLADO";
        }
        input_arti.value = "("+$("#cve_articuloi").val()+") "+document.getElementById('articuloi').value;
        input_origen.value = text_origen;
        input_desti.value = text_almacena_f+" ("+text_ubica_f+")";
        input_cant.value = document.getElementById('ctotali').value; 
        input_peso.value = pesoAMover+" / "+cantidadVAMover;
        input_user.value = $("#usuario").val();
        DATA.idyorigen = idyorigen;
        DATA.cve_almacf = cve_almacf;
        DATA.cve_almaci = cve_almaci;
        DATA.accion = accion;
        $("#modal-info").modal();
    });
  
    function save()//12
    { //PR2
        console.log("Accion:"+" "+DATA.accion);
        console.log("ID Origen:"+" "+DATA.idyorigen);
        //return;
        $("#modal-info").modal("hide");
        $.ajax({
            dataType: "json",
            type: "POST",
            url: '/api/ubicaciones/update/index.php',
            data: {
                tipo: $("#umedidai option:selected").text(),
                cantidad: $("#cantrecibi").val(),
                idiorigen: DATA.idyorigen,
                ididestino: $("#ubicacionf").val(),
                cve_articulo: $("#cve_articuloi").val(),
                ID_Proveedor: $("#ID_Proveedor").val(),
                cve_almaci: DATA.cve_almaci,
                cve_almacf: DATA.cve_almacf,
                cve_lote: $("#lotei").val(),
                piezaxcaja: $("#xcajai").val(),
                piezaxpallet: $("#xpalleti").val(),
                cantidadTotal: $("#ctotali").val(),
                cve_usuario: $("#usuario").val(),
                action : DATA.accion //acomodo
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }},
            success: function(data) 
            {
                if(!data.success)
                {
                    swal('Error!','Estás intentando mover una cantidad mayor a la disponible','error');
                    return false;
                }
                swal('Exito!','Movimiento Guardado','success');
                window.location.reload();
            },
            error: function(res)
            {
                console.log(res);
                swal('Error!','No se puede mover este articulo.','error');
                return false;
            }
        });
    }
    //EDG
    function totalPendiente()
    {
        var allData = $('#grid-tabla').jqGrid('getRowData');
        var total =0;
        console.log("esta es la tabla");
        console.log(allData);
        for(var i=0; i < allData.length; i++)
        {
            total += parseFloat(allData[i]["num_existencia"]);
        }
        console.log(total);
        $("#valor_total_pendiente").val(total);
    }

    $("#myform a").click(function(event){
        event.preventDefault();
    });

    $(document).ready(function(){
        $("#checkAcomodo").prop('checked',false).change();
        $(window).triggerHandler('resize.jqGrid');
    });

</script>
