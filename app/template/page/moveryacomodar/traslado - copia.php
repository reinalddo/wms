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

<link href="/css/plugins/chosen/chosen.css" rel="stylesheet">
<link href="/css/plugins/footable/footable.core.css" rel="stylesheet">
<link href="/css/plugins/iCheck/custom.css" rel="stylesheet">

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
<!--<script src="/js/select2.js"></script>-->
<script src="/js/plugins/staps/jquery.steps.min.js"></script>
<script src="/js/plugins/chosen/chosen.jquery.js"></script>
<script src="/js/plugins/iCheck/icheck.min.js"></script>



<!-- Data picker -->
<script src="/js/plugins/sweetalert/sweetalert.min.js"></script>

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
                            <h3>Traslado</h3>
                        </div>
                    </div>
                </div>
                <form id="myform">
                    <div class="ibox-content">
                        <div class="row">
                            <label class="check_traslado_acomodo">Tipo </label>
                            <div class="col-lg-12 text-center">
                                <div class="form-group check_traslado_acomodo">
                                    <label>Acomodo</label>
                                    <input id="checkAcomodo" type="checkbox" class="js-switch" checked="checked" title="Modo automatico"/>
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

                                <?php 
                                /*
                                ?>
                                <div class="col-md-6">
                                    <div class="form-group" id="zonaalmacenajeif">
                                        <label>Zona de Almacenaje*</label>
                                        <select class="form-control" id="zonaalmacenajei">
                                            <option value="">Seleccione</option>
                                        </select>
                                    </div>
                                </div>
                                <?php 
                                */
                                ?>
                                <div class="col-md-6">
                                    <div class="form-group" id="ubicacionif">
                                        <label>Ubicación*</label>
                                        <select class="form-control chosen-select" id="ubicacioni">
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
                            </div>


                            <div class="col-lg-12 b-b"><br>

                            <div class="form-group" style="text-align: center;">
                                <div class="checkbox">
                                    <label for="traslado_almacen" style="font-weight: 600;font-size: 14px; -moz-user-select: none; -webkit-user-select: none;-ms-user-select: none; user-select: none;">
                                    <input type="checkbox" name="traslado_almacen" id="traslado_almacen" value="0">Traslado entre Almacenes</label>
                                </div>
                            </div>

                                <label></label>
                                <div class="ibox-content">
                                    <div class="jqGrid_wrapper">
                                        <table id="grid-tabla"></table>
                                        <div id="grid-page"></div>

                                        <table id="grid-tabla3"></table>
                                        <div id="grid-page3"></div>

                                        <input type="hidden" id="ocupadoi">
                                        <input type="hidden" id="pesoi">
                                        <input type="hidden" id="capacidadi">
                                        <input type="hidden" id="pesomaxi">
                                        <input type="hidden" id="amixi">
                                        <input type="hidden" id="cantidad_max" value="1">
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

<!-- ******************************************************************************************* -->
                                <div class="col-md-6 traslado_almacen" style="display: none;">
                                    <div class="form-group">
                                        <label>Almacén*</label>
                                        <select class="form-control chosen-select" id="almacen_alm">
                                            <option value="">Seleccione</option>
                                            <?php foreach( $listaAP->getAll() AS $a ): ?>
                                                <option value="<?php echo $a->id; ?>"><?php echo $a->nombre; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div> 
                                </div>

                                <div class="col-md-6 traslado_almacen" style="display: none;">
                                    <div class="form-group" id="zonarecepcionif_alm">
                                        <label>Zona de Recepción</label>
                                        <select class="form-control" id="zonarecepcioni_alm">
                                            <option value="">Seleccione</option>
                                        </select>
                                    </div>
                                </div>
<!-- ******************************************************************************************* -->
                                <div class="col-md-6">
                                    <?php 
                                    /*
                                    ?>
                                    <div class="form-group">
                                        <label id="ubicacion_label">Ubicación*</label>
                                        <select class="form-control chosen-select" id="ubicacionf">
                                            <option value="0">Seleccione</option>
                                        </select>
                                    </div>
                                    <?php 
                                    */
                                    ?>
<?php 

?>
                <div class="row">
                  <div class="form-group col-md-12">
                    <label>Código BL*</label>
                    <input class="form-control" name="cliente_buscar" id="cliente_buscar" placeholder="Código BL">
                    <input class="form-control" name="cliente" id="cliente" placeholder="Código BL" style="display: none;">
                  </div>
                </div>

                <div class="row">
                  <div class="form-group col-md-12">
                  <label>Ubicación*</label>
                       <select id="ubicacionf" name="ubicacionf" class="form-control">
                       </select>
                  </div>
                </div>
<?php 
/*
?>
                <div class="row" style="display: none;">
                <div class="form-group col-md-12">
                    <label>Destinatario *</label> 
                        <select class="form-control chosen-select" name="destinatario" id="destinatario" disabled></select>
                        <br><br>
                            <button type="button" disabled id="agregar_destinatario" class="btn btn-success" data-toggle="modal" data-target="#modal_destinatario">
                                Asignar Destinatario
                            </button>
                    <br><br>
                    <textarea id="txt-direc" name="observacion" class="form-control" disabled></textarea>
                </div>
              </div>
<?php 
*/
?>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group" id="zonarecepcionff">
                                        <label>Zona de Recepción</label>
                                        <select class="form-control" id="zonarecepcionf">
                                            <option value="">Seleccione</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6" style="display:none;">
                                    <div class="form-group" id="zonaalmacenajeff">
                                        <label>Zona de Almacenaje*</label>
                                        <select class="form-control" id="zonaalmacenajef">
                                            <option value="0">Seleccione</option>
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
                                        <!--<option value="2">Por Caja</option>-->
                                        <option value="3">Por Pallet/Contenedor</option>
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
                    <label ><span class="fa fa-barcode"></span> Artículo | Pallet | Contenedor</label>
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


<script type="text/javascript">

    var lastEmptyItem, lastRowTable;
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
                    //fillSelectZona();
                    CargarUbicacionesBL();
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

            $('.chosen-select').chosen();

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
            colNames:['Seleccionar','idy_ubica','Folio Entrada','Tipo','Pallet|Contenedor', 'License Plate (LP)','Clave', 'Descripcion','Lote','Caducidad','Serie','Peso','Volumen Ocupado','Existencia','Proveedor','id_proveedor','id_articulo','Almacen','Zona de Almacenaje'],
            colModel:[
                {name:'myac',index:'', width:80, fixed:true, sortable:false, resize:false, formatter:imageFormat, frozen : true},
                {name:'idy_ubica',index:'idy_ubica',editable:false,width:150, sortable:false, hidden: true},
                {name:'folio',index:'folio',editable:false,width:100, sortable:false, hidden: false, align: 'right'},
                {name:'tipoa',index:'tipo',width:100,editable:false, sortable:false, hidden: false},
                {name:'pallet_contenedor',index:'pallet_contenedor',hidden:false, width:150,editable:false, sortable:false, align: 'right'},
                {name:'LP',index:'LP',hidden:false, width:150,editable:false, sortable:false, align: 'right'},
                {name:'clave',index:'clave',hidden:false, width:100,editable:false, sortable:false, align: 'right'},
                {name:'descripcion',index:'descripcion', editable:false,width:180, sortable:false},
                {name:'lote',index:'lote', editable:false,width:80, sortable:false, align: 'right'},
                {name:'caducidad',index:'caducidad', editable:false,width:100, sortable:false},
                {name:'serie',index:'serie', editable:false,width:80, sortable:false, align: 'right'},
                {name:'peso_total',index:'peso_total', editable:false, width:100, sortable:false, align: 'right'},
                {name:'volumen_ocupado',index:'volumen_ocupado',width:100, editable:false, sortable:false, align: 'right'},
                {name:'num_existencia',index:'num_existencia',width:100, editable:false, sortable:false, align: 'right'},
                {name:'proveedor',index:'proveedor',editable:false,width:150,sortable:false, hidden: false},
                {name:'id_proveedor',index:'id_proveedor',editable:false,width:150,sortable:false, hidden: true, align: 'right'},
                {name:'id_articulo',index:'id_articulo',editable:false,width:150, sortable:false, hidden: true, align: 'right'},
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
            colNames:[/*'Seleccionar',*/'idy_ubica','Almacen',' Zona de Almacenaje','Ubicacion', 'Pallet|Contenedor', 'License Plate (LP)','Clave Articulo','id_articulo','Articulo','Lote','Caducidad','Serie','Volumen Ocupado','Existencia','Peso','Proveedor','id_proveedor'],/*'Calle y Numero','Colonia','Ciudad','Estado','Pais','Codigo Postal','RFC','Telefono1','Telefono2'*/
            colModel:[
                //{name:'myac',index:'', width:80, fixed:true, sortable:false, resize:false, formatter:imageFormat, frozen : true},
                {name:'idy_ubica',index:'idy_ubica',editable:false,width:150, sortable:false, hidden: true},
                {name:'almacen',index:'almacen',editable:false,width:150,sortable:false, hidden: false},
                {name:'zona',index:'zona',editable:false,width:150, sortable:false, hidden: false},
                {name:'ubicacion',index:'ubicacion',width:250,editable:false, sortable:false, hidden: false},
                {name:'pallet',index:'pallet',width:250,editable:false, sortable:false, hidden: false},
                {name:'LP',index:'LP',width:250,editable:false, sortable:false, hidden: false},
                {name:'cve_articulo',index:'cve_articulo',hidden:false, width:150,editable:false, sortable:false, align: 'right'},
                {name:'id_articulo',index:'id_articulo',hidden:true, width:150,editable:false, sortable:false, align: 'right'},
                {name:'articulo',index:'articulo', editable:false,width:250, sortable:false},
                {name:'lote',index:'lote', editable:false,width:150, sortable:false, align: 'right'},
                {name:'caducidad',index:'caducidad', editable:false,width:150, sortable:false},
                {name:'serie',index:'serie', editable:false,width:150, sortable:false, align: 'right'},
                {name:'volumen_ocupado',index:'volumen_ocupado',width:150, editable:false, sortable:false, align: 'right'},
                {name:'num_existencia',index:'num_existencia',width:150, editable:false, sortable:false, align: 'right'},
                {name:'peso_total',index:'peso_total', editable:false, sortable:false, align: 'right'},
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
            html += '<a href="javascript:void(0)" onclick="getDataZI(\''+id+'\')"><i class="fa fa-check-circle" alt="Editar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
        //html += '<a href="javascript:void(0)" onclick="getDataZF(\''+id+'\')"><i class="fa fa-check-circle" alt="Editar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            return html;
        }
    });


var tipo_ubicaciones_action_send = "", descripcion_pca_send = "";

    $(function($) {
        var grid_selector = "#grid-tabla3";
        var pager_selector = "#grid-page3";

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
            colNames:['Seleccionar','idy_ubica','Ubicacion', 'Tipo', 'Pallet|Contenedor', 'License Plate (LP)','Clave Articulo','id_articulo','Articulo','Lote|Serie','Caducidad','Serie','Volumen Ocupado','Existencia','Peso','Proveedor','id_proveedor','Almacen',' Zona de Almacenaje'],/*'Calle y Numero','Colonia','Ciudad','Estado','Pais','Codigo Postal','RFC','Telefono1','Telefono2'*/
            colModel:[
                {name:'myac',index:'', width:80, fixed:true, sortable:false, resize:false, formatter:imageFormat, frozen : true},
                {name:'idy_ubica',index:'idy_ubica',editable:false,width:150, sortable:false, hidden: true},
                {name:'ubicacion',index:'ubicacion',width:100,editable:false, sortable:false, hidden: false},
                {name:'tipo',index:'tipo',width:80,editable:false, sortable:false, hidden: false},
                {name:'pallet',index:'pallet',width:150,editable:false, sortable:false, hidden: false},
                {name:'LP',index:'LP',width:150,editable:false, sortable:false, hidden: false},
                {name:'cve_articulo',index:'cve_articulo',hidden:false, width:150,editable:false, sortable:false},
                {name:'id_articulo',index:'id_articulo',hidden:true, width:150,editable:false, sortable:false, align: 'right'},
                {name:'articulo',index:'articulo', editable:false,width:200, sortable:false},
                {name:'lote',index:'lote', editable:false,width:150, sortable:false},
                {name:'caducidad',index:'caducidad', editable:false,width:100, sortable:false},
                {name:'serie',index:'serie', editable:false,width:150, sortable:false, align: 'right', hidden: true},
                {name:'volumen_ocupado',index:'volumen_ocupado',width:150, editable:false, sortable:false, align: 'right'},
                {name:'num_existencia',index:'num_existencia',width:150, editable:false, sortable:false, align: 'right'},
                {name:'peso_total',index:'peso_total', editable:false, sortable:false, align: 'right'},
                {name:'proveedor',index:'proveedor',editable:false,width:150,sortable:false, hidden: false},
                {name:'id_proveedor',index:'id_proveedor',editable:false,width:150,sortable:false, hidden: true, align: 'right'},
                {name:'almacen',index:'almacen',editable:false,width:150,sortable:false, hidden: false},
                {name:'zona',index:'zona',editable:false,width:150, sortable:false, hidden: false},
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
            html += '<a href="javascript:void(0)" onclick="getDataZI(\''+id+'\')"><i class="fa fa-check-circle" alt="Editar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
        //html += '<a href="javascript:void(0)" onclick="getDataZF(\''+id+'\')"><i class="fa fa-check-circle" alt="Editar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            return html;
        }

    });

    function CargarUbicacionesBL()
    {
        var almacen= $("#almacen").val();
        //$("#zonaalmacenajef").prop('disabled',true);
        $("#ubicacionf").prop('disabled',true);
        console.log("CargarUbicacionesBL() = ", almacen);
        $.ajax({
            type: "POST",
            dataType: "json",
            url: '/api/ubicaciones/update/index.php',
            data: {
                action : "ubicacionNoVacias",
                almacen : almacen,
                excludeInventario: 0
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }},
            success: function(data) {
                var options = $("#ubicacioni");
                options.empty();
                options.append(new Option("Seleccione Ubicación ("+data.ubicaciones.length+")", ""));
                for (var i=0; i<data.ubicaciones.length; i++)
                {
                    options.append(new Option(data.ubicaciones[i].ubicacion, data.ubicaciones[i].idy_ubica));
                }

                //options.addClass("chosen-select");
                $("#ubicacioni").trigger("chosen:updated");
            }
        });
    }

    $("#checkAcomodo").change(function() {
        if(!this.checked) 
        {
            $("#zonaalmacenajeif").hide();
            $("#zonarecepcionif").show();
            $("#valor_total_pendiente_pa").show();
            //$("#zonaalmacenajeff").show();
            $("#zonarecepcionff").hide();
            $("#ubicacionif").hide();
            $("#articuloi").val("");
            $("#articulof").val("");
            //$("#zonaalmacenajef").val("");
            $("#zonaalmacenajei").val("");
            $("#zonarecepcioni").val("");
            $("#ubicacionif").val("");
            $("#ubicacioni").val("");
            $("#addrow2").hide();
            $("#grid-tabla3").jqGrid("clearGridData");
            $("#grid-tabla2").jqGrid("clearGridData");
            $("#grid-tabla").jqGrid("clearGridData");
            $("#gbox_grid-tabla").show();
            $("#gbox_grid-tabla3").hide();
        }
        else
        {
            //$("#zonaalmacenajeif").show();
            $("#zonaalmacenajeif").hide();
            $("#zonarecepcionif").hide();
            //$("#zonaalmacenajeff").show();

            //$("#zonaalmacenajef").prop('disabled',true);
            $("#ubicacionf").prop('disabled',true);
            $("#valor_total_pendiente_pa").hide();
            $("#zonarecepcionff").hide();
            $("#ubicacionif").show();
            $("#grid-tabla3").jqGrid("clearGridData");
            $("#grid-tabla2").jqGrid("clearGridData");
            $("#grid-tabla").jqGrid("clearGridData");
            $("#articuloi").val("");
            //$("#zonaalmacenajef").val("");
            $("#zonaalmacenajei").val("");
            $("#zonarecepcioni").val("");
            $("#ubicacionif").val("");
            $("#ubicacioni").val("");
            $("#gbox_grid-tabla").hide();
            $("#gbox_grid-tabla3").show();
            //CargarUbicacionesBL();
        }

        tipo_ubicaciones_action_send = "";
        descripcion_pca_send = "";
    });

    $('#zonaalmacenajei').change(function(e) {
        var almacen= $(this).val();
        //$("#zonaalmacenajef").prop('disabled',true);
        $("#ubicacionf").prop('disabled',true);
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

                $("#ubicacionf").trigger("chosen:updated");
            }
        });
    });

    function UbicacionesAcomodoTraslado(almacen, action_val, tipo_ubicaciones_action_send)
    {
        //var almacen= $(this).val();
        calculatePesoUbi();
        console.log("Almacen =", almacen);
        console.log("action =", action_val);

        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                almacen : almacen,
                action : action_val,
                ubicacion_origen: $("#ubicacioni").val(),
                tipo_pca: tipo_ubicaciones_action_send,
                descripcion_pca: descripcion_pca_send,
                excludeInventario: 0
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }},
            url: '/api/ubicaciones/update/index.php',
            success: function(data) {
                //console.log(data);
                var select = document.getElementById('ubicacionf'),
                    node = data.ubicaciones,
                    options = "<option value ='0'>Seleccione Ubicacion ("+node.length+")</option>";

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

                $("#ubicacionf").trigger("chosen:updated");
            }
        });
    }

/*
    $('#zonaalmacenajef').change(function(e) {

        var action_val = "ubicacionNoLlenas";

        //if(tipo_ubicaciones_action_send)
            //action_val = tipo_ubicaciones_action_send;

        $("#ubicacionf").prop('disabled',false);
        $("#ubicacionf").trigger("chosen:updated");
//        if(!document.getElementById("checkAcomodo").checked)
//            action_val = "ubicacionesVacias";

        console.log(action_val);

        //UbicacionesAcomodoTraslado($(this).val(), action_val, tipo_ubicaciones_action_send);
    });
    */
    //EDG //PR java tabla acomodo //12
    $('#zonarecepcioni').change(function(e) {
        $("#grid-tabla").jqGrid('clearGridData');
        var almacen= $(this).val();
        console.log("cve_ubicacion = ", almacen);
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                tipo: document.getElementById("checkAcomodo").checked ? 'ubicacion' : 'area',
                cve_ubicacion : almacen,
                action : "getArticulosPendientesAcomodo"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }},
            url: '/api/ubicaciones/update/index.php',
            success: function(data) 
            {
                console.log("data = ", data);
                for (var i=0; i<data.articulos.length; i++)
                {
                  /*if(data.articulos[i].lote == ""){data.articulos[i].lote = "";}
                  if(data.articulos[i].lote == ""){data.articulos[i].caducidad = "";}*/
                  emptyItem=[{
                      idy_ubica:            data.articulos[i].folio,
                      pallet_contenedor:    data.articulos[i].pallet_contenedor,
                      LP:                   data.articulos[i].LP,
                      clave:                data.articulos[i].clave,
                      tipoa:                data.articulos[i].tipo,
                      folio:                data.articulos[i].folio,
                      descripcion:          data.articulos[i].descripcion,
                      num_existencia:       data.articulos[i].num_existencia,
                      peso_total:           data.articulos[i].peso_total,
                      id_articulo:          data.articulos[i].id_articulo,
                      lote:                 data.articulos[i].lote,
                      caducidad:            data.articulos[i].caducidad,
                      serie:                data.articulos[i].numero_serie,
                      volumen_ocupado:      data.articulos[i].volumen_ocupado,
                      proveedor:            data.articulos[i].proveedor,
                      id_proveedor:         data.articulos[i].id_proveedor,
                      almacen:              $("#almacen option:selected").text(),
                      zona:                 $("#zonarecepcioni option:selected").text()
                   }];
                   //console.log("tipo for["+i+"] = ", data.articulos[i].tipo)

                  $("#grid-tabla").jqGrid('addRowData',0,emptyItem);
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
/*
        $("#grid-tabla3").jqGrid('clearGridData');

        calculatePesoUbi();
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
                $("#grid-tabla3").jqGrid("clearGridData");
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
                    $("#grid-tabla3").jqGrid('addRowData',0,emptyItem);
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
*/
        $("#grid-tabla3").jqGrid('clearGridData');
        calculatePesoUbi();
        var almacen= $(this).val();
        //$("#zonaalmacenajef").prop('disabled',true);
        //$("#ubicacionf").prop('disabled',true);
        $("#ubicacionf").empty();
        $("#ubicacionf").trigger("chosen:updated");

        $("#idy_ubicai").val(almacen);
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
                var total_pendiente_acomodo = 0;

                $("#grid-tabla3").jqGrid("clearGridData");
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
                        tipo:data.articulos[i].tipo,
                        pallet:data.articulos[i].pallet,
                        LP:data.articulos[i].LP,
                        articulo:data.articulos[i].articulo,
                        volumen_ocupado:data.articulos[i].volumen_ocupado,
                        num_existencia:data.articulos[i].num_existencia,
                        peso_total:data.articulos[i].peso_total,
                        id_articulo:data.articulos[i].id_articulo,
                        lote:data.articulos[i].lote,
                        caducidad:data.articulos[i].caducidad,
                        //serie:data.articulos[i].serie,
                        almacen:$("#almacen option:selected").text(),
                        ubicacion:$("#ubicacioni option:selected").text(),
                        //zona:$("#zonaalmacenajei option:selected").text(),
                        zona: data.articulos[i].Zona_Almacenaje,
                        proveedor:data.articulos[i].proveedor,
                        id_proveedor:data.articulos[i].ID_Proveedor
                    }];

                    total_pendiente_acomodo += parseFloat(data.articulos[i].num_existencia);
                    $("#grid-tabla3").jqGrid('addRowData',0,emptyItem);

                    peso  = peso  + parseFloat(data.articulos[i].volumen_ocupado);
                    peso2 = peso2 + parseFloat(data.articulos[i].peso_total);
                }
                
                var input_pesoU = document.getElementById('input-volumenU');
                input_pesoU.value = (data.capacidad[0].capacidad_volumetrica + " / " + peso.toFixed(6));
                $("#porcentaje_v_ocupado").val(parseInt((peso*100)/data.capacidad[0].capacidad_volumetrica)+" %");
              
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
                var ocupado=(parseFloat(peso)/parseFloat(data.capacidad[0].capacidad_volumetrica)*100);
                $("#zixocupado").empty();
                $("#zixocupado").html("&nbsp;% de Ocupación "+ocupado.toFixed(2)+" ");
                $("#amixi").val(data.capacidad[0].amix);
                calculatePesoUbi(data.capacidad[0].peso, peso2);
                $("#valor_total_pendiente").val(total_pendiente_acomodo);
            }
        });

    });


    function change_ubicacionf()
    {
        $("#grid-tabla2").jqGrid('clearGridData');
        console.log("ubicacioni = ", $('#ubicacioni').val());
        calculatePesoUbi();
        var almacen= $('#ubicacionf').val();
        $("#idy_ubicaf").val(almacen);
        $.ajax({
            type: "POST",
            dataType: "json",
            url: '/api/ubicaciones/update/index.php',
            data: {
                action : "articulosenUbicacion",
                idy_ubica : almacen
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }},
            success: function(data) 
            {
                //console.log("data.articulos[0].Clave_Zona = ", data.articulos[0].Clave_Zona);
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
                        pallet:data.articulos[i].pallet,
                        LP:data.articulos[i].LP,
                        id_articulo:data.articulos[i].id_articulo,
                        caducidad:data.articulos[i].caducidad,
                        serie:data.articulos[i].serie,
                        lote:data.articulos[i].lote,
                        almacen:$("#almacen option:selected").text(),
                        ubicacion:$("#ubicacionf option:selected").text(),
                        //zona:$("#zonaalmacenajef option:selected").text(),
                        zona: data.articulos[i].Zona_Almacenaje,
                        proveedor:data.articulos[i].proveedor,
                        id_proveedor:data.articulos[i].ID_Proveedor
                    }];
                    console.log("data.articulos[i].Clave_Zona = ", data.articulos[i].Clave_Zona);
                    $("#grid-tabla2").jqGrid('addRowData',0,emptyItem);
                    peso  = peso  + parseFloat(data.articulos[i].volumen_ocupado);
                    peso2 = peso2 + parseFloat(data.articulos[i].peso_total);

                }
                    if(!$("#traslado_almacen").is(':checked'))
                    {
                        //$("#zonaalmacenajef").val(data.zona[0].cve_almac);
                    
                //$("#grid-tabla2").jqGrid('addRowData',0,lastEmptyItem);
                
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

                //lastRowTable["ubicacion"] = $("#ubicacionf option:selected").text();
                //$("#grid-tabla2").jqGrid('addRowData',0,lastRowTable);
            }
        });
    }

    $('#ubicacionf').change(function(e)
    {
        change_ubicacionf();
    });

    $('#almacen').change(function(e){
        $("#grid-tabla").jqGrid('clearGridData');
        $("#ubicacionf").empty();
        $("#ubicacionf").append(new Option("Seleccione", ""));
        CargarUbicacionesBL();
        //fillSelectZona();
        $("#almacen_alm").val("");
        $("#almacen_alm option").show();
        $("#almacen_alm option[value=" + $(this).val() + "]").hide();
        $("#almacen_alm").val("");
        $("#almacen_alm").trigger("change");
        $("#almacen_alm").trigger("chosen:updated");
    });

    $('#traslado_almacen').change(function() 
    {
        if($("#traslado_almacen").is(':checked'))
        {
            //console.log("traslado CHECK ON");
            $("#ubicacionf_chosen, #ubicacion_label").hide();//, #zonaalmacenajeff
            $(".traslado_almacen").show();
            //fillSelectZona();
            $("#almacen_alm option[value=" + $('#almacen').val() + "]").hide();
            $("#ubicacionf, #almacen_alm").val("");
            $("#ubicacionf, #almacen_alm").trigger("change");
            $("#ubicacionf, #almacen_alm").trigger("chosen:updated");
            $("#grid-tabla2").jqGrid('clearGridData');
            $("#grid-tabla2").hide();
        }
        else
        {
            //console.log("traslado CHECK OFF");
            $("#ubicacionf_chosen, #ubicacion_label").show();//, #zonaalmacenajeff
            $(".traslado_almacen").hide();
            $("#grid-tabla2").show();
            $("#grid-tabla2").jqGrid('clearGridData');
        }
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
                //input_pesoA.value = "";
                //input_volumenA.value = "";
            }
        }
        else
        {
            //input_pesoA.value = "";
            //input_volumenA.value = "";
            input_pesoA.style.borderColor = "";
            input_volumenA.style.borderColor = "";
        }
    }

    $('#almacen_alm').change(function(e){
        fillSelectZona();
    });

    function fillSelectZona()
    {

        var almacen= $('#almacen').val();

        //if(!$("#traslado_almacen").is(':checked'))
        //{
            /*
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
                    /*
                    var options = $("#zonarecepcioni_alm");
                    options.empty();
                    options.append(new Option("Seleccione", ""));
                    for (var i=0; i<data.zona_almacen.length; i++)
                    {
                        options.append(new Option(data.zona_almacen[i].descripcion_almacen, data.zona_almacen[i].clave_almacen));
                    }
                    */
                    /*
                    var options = $("#zonaalmacenajef");
                    options.empty();
                    options.append(new Option("Seleccione", "0"));
                    for (var i=0; i<data.zona_almacen.length; i++)
                    {
                        options.append(new Option(data.zona_almacen[i].descripcion_almacen, data.zona_almacen[i].clave_almacen));
                    }
                }
            });
            */
        //}
        //else 
        //{
            almacen= $('#almacen_alm').val();
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
                    var options = $("#zonarecepcioni_alm");
                    options.empty();
                    options.append(new Option("Seleccione", ""));
                    for (var i=0; i<data.zonas.length; i++)
                    {
                        options.append(new Option(data.zonas[i].cve_ubicacion+" - "+data.zonas[i].desc_ubicacion, data.zonas[i].cve_ubicacion));
                    }
                }
            });
        //}

/*
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
                var options = $("#zonarecepcionif_alm");
                options.empty();
                options.append(new Option("Seleccione", ""));
                for (var i=0; i<data.zonas.length; i++)
                {
                    options.append(new Option(data.zonas[i].cve_ubicacion+" - "+data.zonas[i].desc_ubicacion, data.zonas[i].cve_ubicacion));
                }
            }
        });
*/

    }   


function Calcular_Num_Piezas()
{
        var cantidad =0;
        $("#cantrecibi").prop('disabled',false);
        var rowData = $("#grid-tabla").jqGrid("getRowData", $("#rowi").val());

        if ($("#umedidai").val()=="1" || $("#umedidai").val()=="3")
        {
            cantidad = $("#cantrecibi").val();
            console.log(cantidad);
            if ($("#umedidai").val()=="3")
                $("#cantrecibi").prop('disabled',true);
        }
/*
        if ($("#umedidai").val()=="2")
        {
            cantidad = $("#cantrecibi").val()*$("#xcajai").val();
            if(rowData["num_existencia"] < cantidad)
            {
                swal('Error','No hay cajas completas para mover','error');
                cantidad = 0;
            }
            console.log(cantidad);
        }
        if ($("#umedidai").val()=="3")
        {
             $("#cantrecibi").prop('disabled',true);
             $("#cantrecibi").val(1);

            cantidad = $("#cantrecibi").val()*$("#xpalleti").val();
            if(rowData["num_existencia"] < cantidad)
            {
                swal('Error','No hay Pallets completos para mover','error');
                cantidad = 0;
            }
            console.log(cantidad);
        }
*/
        $("#ctotali").val(cantidad);
        calculatePesoMax(true);
}

    $("#cantrecibi").keyup(function(e){
        Calcular_Num_Piezas();
    });
  
    $("#umedidai").change(function(e){
        Calcular_Num_Piezas();
    });
  
    function comparar(_codigo)
    {
       var rowData;

       if(document.getElementById("checkAcomodo").checked)
          rowData = $("#grid-tabla3").jqGrid("getRowData", _codigo);
       else
          rowData = $("#grid-tabla").jqGrid("getRowData", _codigo);

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

    function PesoYVolumenRowsGridTable(clave)
    {
        var num_rows = $("#grid-tabla").jqGrid('getGridParam', 'reccount');
        var rowData, peso_clave = 0, volumen_clave = 0;

        for(var i = 1; i<=num_rows; i++)
        {
            rowData = $("#grid-tabla").jqGrid("getRowData", 'jqg'+i);
            if(rowData['pallet_contenedor'] == clave)
            {
                peso_clave += parseFloat(rowData['peso_total']);
                volumen_clave += parseFloat(rowData['volumen_ocupado']);
            }
        }

        console.log("Peso Clave = ", peso_clave);
        console.log("Volumen Clave = ", volumen_clave);

        $("#input-pesoA").val(peso_clave);
        $("#input-volumenA").val(volumen_clave);

    }
    
    function getDataCON(_codigo)
    {
       var rowData = $("#grid-tabla").jqGrid("getRowData", _codigo);
       console.log ("data",rowData);
       console.log ("codigo",_codigo);
       $("#articuloi").val(rowData["pallet_contenedor"]);
       $("#cve_articuloi").val(rowData["clave"]);
       $("#ID_Proveedor").val(rowData["id_proveedor"]);
       $("#idy_ubicai").val(rowData["idy_ubica"]);
       $("#rowi").val(_codigo);
       document.getElementById('label-cantT').innerHTML = "Cantidad en Piezas | Máx (1)";
       $("#cantidad_max").val(1);
      
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
            
            console.log("tipoCON", rowData["tipoa"]);

            if(!document.getElementById("checkAcomodo").checked)
                UbicacionesAcomodoTraslado($("#almacen").val(), "ubicacionesVacias", tipo_ubicaciones_action_send);

             $("#cantrecibi, #ctotali").prop('disabled',true);
             $("#cantrecibi, #ctotali").val(1);
             $("#apesoi").val(rowData["peso_total"]);

            PesoYVolumenRowsGridTable(rowData["pallet_contenedor"]);
             //$("#input-pesoA").val(rowData["peso_total"]);
             //$("#input-volumenA").val(rowData["volumen_ocupado"]);

             $("#acvi").val(rowData["volumen_ocupado"]);
             $("#umedidai").val(3);
             //$("#ctotali").prop('disabled',true);
             //$("#ctotali").val(1);
             $("#lotei").val(rowData["lote"]);
           
          }
       });
       Calcular_Num_Piezas();
     }

    function getDataZI(_codigo)//12
    { //PR3
        console.log("numRowsTable = ", $("#grid-tabla").jqGrid('getGridParam', 'reccount'));
        console.log ("codigo",_codigo);
        var rowData, rowData2;
        if(document.getElementById("checkAcomodo").checked)
        {
            rowData = $("#grid-tabla3").jqGrid("getRowData", _codigo);

            //$("#zonaalmacenajef option[value='0']").attr("selected",true);
            //fillSelectZona();
            //$("#zonaalmacenajef").prop('disabled',true);

            console.log("Tipo = ", rowData['tipo']);

            //$("#ubicacionf option[value='0']").attr("selected",true);
            //var action_val = "ubicacionNoLlenas";
            var tipo_ubicacion = "ubicacionNoLlenas";
            if(rowData['tipo'] == "Pallet")
            {
                tipo_ubicaciones_action_send = 'p';
                tipo_ubicacion = "ubicacionesVacias";
            }
            else if(rowData['tipo'] == "Contenedor")
                tipo_ubicaciones_action_send = 'c';
            else
                tipo_ubicaciones_action_send = 'a';

            descripcion_pca_send = rowData['pallet'];
            //var action_val = tipo_ubicaciones_action_send;
            //UbicacionesAcomodoTraslado($("#almacen").val(), action_val, tipo_ubicaciones_action_send);
            $("#ubicacionf").prop('disabled',false);

            //if(!$("#traslado_almacen").is(':checked'))
            //    UbicacionesAcomodoTraslado($("#almacen").val(), tipo_ubicacion, tipo_ubicaciones_action_send);
        }
        else
            rowData  = $("#grid-tabla").jqGrid("getRowData", _codigo);

        console.log ("data",rowData);
        if(document.getElementById("checkAcomodo").checked)
        {
            if(rowData["pallet"])
            {
                $("#articuloi").val(rowData["pallet"]);
                $("#cve_articuloi").val(rowData["pallet"]);
                console.log("pallet_contenedor = ", $("#pallet").val());
            }
            else
            {
                $("#articuloi").val(rowData["cve_articulo"]+" - "+rowData["articulo"]);
                $("#cve_articuloi").val(rowData["cve_articulo"]);
                console.log("cve_articuloi = ", $("#cve_articuloi").val());
            }

        }
        else
        {
            if(rowData['tipo'] == 'Artículo')
                $("#articuloi").val("["+rowData["clave"]+"] - "+rowData["descripcion"]);
            else
            {
                $("#articuloi").val(rowData["pallet_contenedor"]);
                PesoYVolumenRowsGridTable(rowData["pallet_contenedor"]);
            }
            $("#cve_articuloi").val(rowData["clave"]);
        }

        $("#ID_Proveedor").val(rowData["id_proveedor"]);
        $("#idy_ubicai").val(rowData["idy_ubica"]);
        $("#rowi").val(_codigo);
        document.getElementById('label-cantT').innerHTML = "Cantidad en Piezas | Máx ( "+rowData["num_existencia"]+" )";
        $("#cantidad_max").val(rowData["num_existencia"]);
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
              console.log("entro data", data);
                $("#cantrecibi").prop('disabled',false);
                $("#cantrecibi").val(0);
                $("#cantrecibi").val(0);
                $("#ctotali").val(0);
                calculatePesoMax(true);
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
                console.log("idy_ubica = ver", rowData["idy_ubica"]);

                console.log("tipoZI", rowData["tipoa"]);
                console.log("tipoZI", rowData["cve_articulo"]);

                if(rowData["tipo"] != "Artículo")
                {
                     //$("#cantrecibi, #ctotali").prop('disabled',true);
                     $("#ctotali").prop('disabled',true);
                     //$("#cantrecibi, #ctotali").val(1);
                     $("#cantrecibi, #ctotali").val(rowData["num_existencia"]);

                     document.getElementById('label-cantT').innerHTML = "Cantidad en Piezas | Máx ("+rowData["num_existencia"]+")";
                     $("#cantidad_max").val(rowData["num_existencia"]);

                    //if(!document.getElementById("checkAcomodo").checked)
                    //    UbicacionesAcomodoTraslado($("#almacen").val(), "ubicacionesVacias", tipo_ubicaciones_action_send);
                }
                else
                {
                     $("#cantrecibi").prop('disabled',false);
                     $("#cantrecibi, #ctotali").val(0);
                    //if(!document.getElementById("checkAcomodo").checked)
                        //UbicacionesAcomodoTraslado($("#almacen").val(), "ubicacionNoLlenas", tipo_ubicaciones_action_send);
                }

//                if(document.getElementById("checkAcomodo").checked)
//                {
                    if(rowData['tipo'] == 'Artículo')
                    {
                        $("#input-pesoA").val(rowData["peso_total"]);
                        $("#input-volumenA").val(rowData["volumen_ocupado"]);
                    }
//                }

                    if(document.getElementById("checkAcomodo").checked)
                    {
                        //console.log("lastEmptyItem1", lastEmptyItem);
                        //$("#grid-tabla2").jqGrid('delRowData', lastEmptyItem);
                        //$("#grid-tabla2").jqGrid("clearGridData");


                        //$("#grid-tabla2").delRowData(lastEmptyItem);
                        //*************************************************************************************
                        //*************************************************************************************
                        //*************************************************************************************
                            $("#grid-tabla2").jqGrid('clearGridData');
                            calculatePesoUbi();
                            var almacen= $("#ubicacioni").val();
                            $("#idy_ubicaf").val(almacen);
                            console.log("almacen = ", almacen);
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
                                            zona:0, //$("#zonaalmacenajef option:selected").text(),
                                            proveedor:data.articulos[i].proveedor,
                                            id_proveedor:data.articulos[i].ID_Proveedor
                                        }];
                                        $("#grid-tabla2").jqGrid('addRowData',0,emptyItem);
                                        peso  = peso  + parseFloat(data.articulos[i].volumen_ocupado);
                                        peso2 = peso2 + parseFloat(data.articulos[i].peso_total);

                                    }
                                    //$("#grid-tabla2").jqGrid('addRowData',0,lastEmptyItem);
                                    
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

                                    //rowData["ubicacion"] = $("#ubicacionf option:selected").text();
                                    //$("#grid-tabla2").jqGrid('addRowData',0,rowData);
                                }
                            });
                        //*************************************************************************************
                        //*************************************************************************************
                        //*************************************************************************************

                        //$("#grid-tabla2").trigger('reloadGrid');
                        lastEmptyItem = _codigo;
                        lastRowTable = rowData;
                        //console.log("lastEmptyItem2", lastEmptyItem);
                    }
             }
        });
        //Calcular_Num_Piezas();
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
        var rowData = "", rowData2 = "";

        if (!$("#checkAcomodo").is(":checked"))
            rowData = $("#grid-tabla").jqGrid("getRowData", $("#rowi").val());
        else 
            rowData2 = $("#grid-tabla3").jqGrid("getRowData", $("#rowi").val());

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
        //if($("#traslado_almacen").is(':checked'))
        /*
        if ($("#zonaalmacenajei").val()=="" && $("#checkAcomodo").is(":checked")==true)
        {
            swal('Error','Selecciona Zona de Almacenaje inicial','error');
            return;
        }
        */
        //if ($("#zonaalmacenajef").val()=="0" && !$("#traslado_almacen").is(':checked'))
        //{
        //    swal('Error','Selecciona Zona de Almacenaje final','error');
        //    return;
        //}

        if ($("#zonarecepcioni_alm").val()=="" && $("#traslado_almacen").is(':checked'))
        {
            swal('Error','Selecciona Zona de Recepción Destino','error');
            return;
        }

        if ($("#ubicacioni").val()=="" && $("#checkAcomodo").is(":checked")==true)
        {
            swal('Error','Selecciona ubicación inicial','error');
            return;
        }
        if ($("#ubicacionf").val()=="0" && !$("#traslado_almacen").is(':checked'))
        {
            swal('Error','Selecciona ubicación final','error');
            return;
        }

        if ($("#almacen_alm").val()=="" && $("#traslado_almacen").is(':checked'))
        {
            swal('Error','Selecciona el almacén destino','error');
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

        if($("#cantrecibi").val()=="" || $("#cantrecibi").val()=="0")
        {
            swal('Error','Seleccione cantidad','error');
            return;
        }

        //**************************************************************************************
        //      AQUI HAY QUE AGREGAR UN CAMPO EN LA TABLA C_PROVEEDORES, SERIA UNA BANDERA 
        //      QUE ME CLASIFIQUE QUE EL PROVEEDOR ES LA MISMA EMPRESA, CASO QUE SE DA CUANDO
        //      SE REALIZA UNA MANUFACTURA DE UN PRODUCTO Y EL PROVEEDOR DEBE SER EL MISMO 
        //      DUEÑO DEL SISTEMA O LA EMPRESA QUE REALIZÓ LA MANUFACTURA
        //**************************************************************************************
        if(rowData2['proveedor'] == '')
        {
            swal('Error','Este Artículo no posee un proveedor registrado para poder completar el traslado','error');
            return;
        }
        //**************************************************************************************

        var pesoAMover =parseFloat($("#apesoi").val())*parseFloat($("#cantrecibi").val());
        var cantidadVAMover =parseFloat($("#acvi").val())*parseFloat($("#cantrecibi").val())
        var volumenActual= parseFloat($("#ocupadof").val());
        var pesoActual=parseFloat($("#pesof").val());
        var pesoMaximo= parseFloat($("#pesomaxf").val());
        var volumenMaximo= parseFloat($("#capacidadf").val());
        var //select_almacena_a = document.getElementById('zonaalmacenajei'),
            //select_ubica_a = document.getElementById('ubicacioni'),
            //select_almacena_f = document.getElementById('zonaalmacenajef'),
            //select_ubica_f = document.getElementById('ubicacionf'),
            select_area = document.getElementById('zonarecepcioni'),
            //text_almacena_a = select_almacena_f.options.item(select_almacena_f.selectedIndex).text,
            //text_ubica_a = select_ubica_a.options.item(select_ubica_a.selectedIndex).text,
            //text_almacena_f = select_almacena_f.options.item(select_almacena_f.selectedIndex).text,
            text_ubica_f = '', 
            text_area = select_area.options.item(select_area.selectedIndex).text,
            text_origen;

            //if(!$("#traslado_almacen").is(':checked'))
            //    text_ubica_f = select_ubica_f.options.item(select_ubica_f.selectedIndex).text;

        if ((pesoActual+pesoAMover)>pesoMaximo && !$("#traslado_almacen").is(':checked'))
        {
            swal('Error','El peso de los articulos a mover es mayor a la capacidad disponible de la ubicación','error');
            return;
        }
/*
        if(($("#amixf").val()=="N" && ((volumenActual+cantidadVAMover)>volumenMaximo)) || ($("#amixf").val()=="N" && parseFloat($("#cantrecibi").val()) > 1))
        {
            swal('Error','Ubicación no disponible para acomodo mixto','error');
            return;
        }
*/
        if ((volumenActual+cantidadVAMover)>volumenMaximo && !$("#traslado_almacen").is(':checked'))
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
            //cve_almacf=$("#zonaalmacenajef").val();
            idyorigen=$("#idy_ubicai").val();
            text_origen = text_area;
            input_movi.value = "ACOMODO";

            if(rowData['tipoa'] == 'Artículo')
                input_arti.value = "("+$("#cve_articuloi").val()+") "+document.getElementById('articuloi').value;
            else
            {
                input_arti.value = rowData["pallet_contenedor"];
                //PesoYVolumenRowsGridTable(rowData["pallet_contenedor"]);
            }
            $("#cve_articuloi").val(rowData["clave"]);
        }
        else 
        {
            accion="traslado";
            //cve_almaci=$("#zonaalmacenajei").val();
            //cve_almacf=$("#zonaalmacenajef").val();
            idyorigen=$("#ubicacioni").val();
            //text_origen = text_almacena_a+" ("+text_ubica_a+")";
            //text_origen = "("+text_ubica_a+")";
            input_movi.value = "TRASLADO";
            
            $("#cve_articuloi").val(rowData2["cve_articulo"]);
            if(rowData2['tipo'] == 'Artículo')
            {
                input_arti.value = "("+$("#cve_articuloi").val()+") "+document.getElementById('articuloi').value;

            }
            else
            {
                input_arti.value = rowData2["articulo"] + " | " + rowData2["pallet"] +" | "+rowData2["LP"];
                //PesoYVolumenRowsGridTable(rowData["pallet_contenedor"]);
            }

        }


        input_origen.value = text_origen;
        //input_desti.value = text_almacena_f+" ("+text_ubica_f+")";

        if($("#traslado_almacen").is(':checked'))
        {
            var almacen_origen = document.getElementById('almacen'),
                almacen_destino =  document.getElementById('almacen_alm'), 
                zona_destino =  document.getElementById('zonarecepcioni_alm');
            input_origen.value = almacen_origen.options.item(almacen_origen.selectedIndex).text;// + "("+almacen_origen.value+")";
            input_desti.value  = almacen_destino.options.item(almacen_destino.selectedIndex).text + " > " +  zona_destino.options.item(zona_destino.selectedIndex).text;// + "("+almacen_destino.value+")";
        }

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
        var rowData = $("#grid-tabla").jqGrid("getRowData", $("#rowi").val());
        var rowData2 = $("#grid-tabla3").jqGrid("getRowData", $("#rowi").val());

        var pallet_contenedor_send = 'No-Hay-Pallet';
        var tipo_pallet_contenedor_articulo_send = 'No-Hay-Tipo';
        var valor_lp = "", valor_pallet = "";

        if(!document.getElementById("checkAcomodo").checked)
        {
            pallet_contenedor_send = rowData['pallet_contenedor'];
            tipo_pallet_contenedor_articulo_send = rowData['tipoa'];
        }
        else
        {
            valor_lp     = rowData2['LP'];
            valor_pallet = rowData2['pallet'];
            pallet_contenedor_send = rowData2['pallet'];
            tipo_pallet_contenedor_articulo_send = rowData2['tipo'];
        }

        var traslado_almacen = 0;
        if($("#traslado_almacen").is(':checked'))
            traslado_almacen = 1;

        //return;
        console.log("*********************************************************");
        console.log("Accion:"+" "+DATA.accion);

        console.log("traslado_almacen:", traslado_almacen);
        console.log("almacen_origen:", $("#almacen").val());
        console.log("almacen_destino:", $("#almacen_alm").val());
        console.log("zonarecepcioni_alm:", $("#zonarecepcioni_alm").val());

        console.log("tipo:", $("#umedidai").val());
        console.log("cantidad:", $("#cantrecibi").val());
        console.log("cantidad MAX:", $("#cantidad_max").val());
        console.log("idiorigen:", DATA.idyorigen);
        console.log("ididestino:", $("#ubicacionf").val());
        console.log("pallet_contenedor:", pallet_contenedor_send);
        console.log("tipo_pallet_contenedor_articulo:", tipo_pallet_contenedor_articulo_send);
        console.log("lp_val:", valor_lp);
        console.log("pallet_val:", valor_pallet);
        console.log("cve_articulo:", $("#cve_articuloi").val());
        console.log("ID_Proveedor:", $("#ID_Proveedor").val());

        //console.log("cve_almaci:", DATA.cve_almaci);
        console.log("cve_almacf:", DATA.cve_almacf);
        console.log("cve_lote:", $("#lotei").val());
        console.log("piezaxcaja:", $("#xcajai").val());
        console.log("piezaxpallet:", $("#xpalleti").val());
        console.log("cantidadTotal:", $("#ctotali").val());
        console.log("cve_usuario:", $("#usuario").val());
        console.log("*********************************************************");

        $("#modal-info").modal("hide");
        //return;
        $.ajax({
            dataType: "json",
            type: "POST",
            url: '/api/ubicaciones/update/index.php',
            data: {
                tipo: $("#umedidai").val(),//$("#umedidai option:selected").text(),
                traslado_almacen: traslado_almacen,
                almacen_origen: $("#almacen").val(),
                almacen_destino: $("#almacen_alm").val(),
                zonarecepcioni_alm: $("#zonarecepcioni_alm").val(),
                cantidad: $("#cantrecibi").val(),
                cantidad_max: $("#cantidad_max").val(),
                idiorigen: DATA.idyorigen,
                ididestino: $("#ubicacionf").val(),
                pallet_contenedor: pallet_contenedor_send,
                tipo_pallet_contenedor_articulo: tipo_pallet_contenedor_articulo_send,
                lp_val: valor_lp,
                pallet_val: valor_pallet,
                cve_articulo: $("#cve_articuloi").val(),
                ID_Proveedor: $("#ID_Proveedor").val(),
                //cve_almaci: DATA.cve_almaci,
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
                console.log("Entró Success");
                console.log(data);
                if(!data.success)
                {
                    swal('Error!','Estás intentando mover una cantidad mayor a la disponible','error');
                    return false;
                }
                swal('Exito!','Movimiento Guardado','success');
                //console.log(data);
                window.location.reload();
            },
            error: function(res)
            {
                console.log("Entró Error");
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


//******************************************************************************************************
//******************************************************************************************************
        $("#cliente_buscar").on('keyup', function(e)
        {
            var cliente = e.target.value;
            //console.log("cliente.length = ", cliente.length);
            if(cliente.length >= 2)
                BuscarCliente(cliente);
            else
                BuscarCliente("Borrar_La_Lista_de_Clientes");
            //$('#ubicacionf').trigger('change');
        });

        function BuscarCliente(cliente)
        {
            console.log("Cliente = ", cliente);
            //document.getElementById("agregar_destinatario").removeAttribute("disabled");
            $.ajax({
                url: '/api/ubicaciones/update/index.php',
                data: {
                    action: 'getBLSelect',
                    listaD: 1,
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
                        var desc_cliente = document.getElementById("ubicacionf");
                        if(data.combo !== '' && data.find == true){
                            desc_cliente.innerHTML = data.combo;
                            var text = desc_cliente.options[desc_cliente.selectedIndex].text;
                            //document.getElementById("txt-direc").value = text;
                            desc_cliente.removeAttribute('disabled');
                            //$("#cliente").val(data.clave_cliente);
                            //$("#desc_cliente").val("["+data.clave_cliente+"] - "+data.nombre_cliente);
                            $("#cliente").val(data.firsTValue);
                            //BuscarDestinatario(data.firsTValue);
                            console.log("#cliente = ", $("#cliente").val());
                        }
                        else
                        {
                            //$("#destinatario, #agregar_destinatario").prop("disabled", true);
                            $("#cliente").val("");
                            $("#ubicacionf").val("");
                            console.log("#cliente = ", $("#cliente").val());
                        }

                        //$('#ubicacionf').trigger('change');
                        //change_ubicacionf();
                        $(".chosen-select").trigger("chosen:updated");
                }, error: function(data){
                    console.log("ERROR: ", data);
                }

            });
        }

        function BuscarDestinatario(cliente)
        {
            document.getElementById("agregar_destinatario").removeAttribute("disabled");
            $.ajax({
                url: '/api/nuevospedidos/lista/index.php',
                data: {
                    action: 'getDestinatario',
                    descuento: 1,
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
                        //destinatario.removeAttribute('disabled');
                    }
                    //else
                    //{
                    //    $("#destinatario, #agregar_destinatario").prop("disabled", true);
                    //}

                    $(".chosen-select").trigger("chosen:updated");
                }, error: function(data){
                    console.log("ERROR: ", data);
                }

            });
        }

        $("#ubicacionf").on("change", function(e){
            var cliente = $(this).val();
            //BuscarDestinatario(cliente);
            //console.log("Cliente = ", cliente);
        });

        $("#destinatario").on("change", function(e){
            var destinatario = document.getElementById("destinatario");
            var text = destinatario.options[destinatario.selectedIndex].text;
            document.getElementById("txt-direc").value = text;
        });
//******************************************************************************************************
//******************************************************************************************************


    $("#myform a").click(function(event){
        event.preventDefault();
    });

    $(document).ready(function(){
        $("#checkAcomodo").prop('checked',true).change();
        //document.getElementById("checkAcomodo").checked = true;
        $(window).triggerHandler('resize.jqGrid');
        $("#gbox_grid-tabla3").show();
        $(".check_traslado_acomodo").hide();
    });

</script>
