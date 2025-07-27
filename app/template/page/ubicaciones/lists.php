<?php

$listaAlm = new \Almacen\Almacen();

?>
<link href="/css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">
<link href="/css/plugins/iCheck/custom.css" rel="stylesheet">
<link href="/css/plugins/awesome-bootstrap-checkbox/awesome-bootstrap-checkbox.css" rel="stylesheet">

<style>
    #list {
        width: 100%;
        height: 100%;
        position: absolute;
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
</style>

<div class="wrapper wrapper-content  animated " id="list">

    <h3>Ubicaciones</h3>

    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-title">
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="input-group">
                                <input type="text" class="form-control input-sm" name="txtCriterio" id="txtCriterio" placeholder="Buscar...">
                                <div class="input-group-btn">
                                    <a href="#" onclick="ReloadGrid()">
                                        <button type="submit" class="btn btn-sm btn-primary">
                                            Buscar
                                        </button>
                                    </a>
                                </div>
                            </div>

                        </div>
                        <div class="col-lg-4">
                            <a href="#" onclick="agregar()"><button class="btn btn-primary" type="button"><i class="fa fa-plus"></i>&nbsp;&nbsp;Nuevo</button></a>
                            
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




<div class="wrapper wrapper-content  animated fadeInRight" id="FORM" style="display: none">

    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-title">
                    <div class="row">
                        <div class="col-lg-4" id="_title">
                            <h3>Agregar Ubicación</h3>
                        </div>
                    </div>
                </div>
                <form id="myform">
                    <input type="hidden" id="hiddenAction" name="hiddenAction">
                    <div class="ibox-content">
                        <div class="row">

                            <div class="col-lg-6 b-r">
                                <div class="form-group">
                                    <label>Zona de Almacenaje</label>
                                    <select class="form-control" id="Almacen" name="cve_almac">
                                        <option value="">Seleccione la Zona de Almacenaje</option>
                                        <?php foreach( $listaAlm->getAll() AS $p ): ?>
                                            <option value="<?php echo $p->cve_almac; ?>"><?php echo $p->des_almac; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group"><label>Clave de Pasillo</label> <input id="txtClavePasillo" type="text" placeholder="Clave de Pasillo" class="form-control"></div>
                                <div class="form-group"><label>Clave de Rack</label> <input id="txtClaveRack" type="text" placeholder="Clave de Rack" class="form-control"></div>
                                <div class="form-group"><label>Clave de Sección</label> <input id="txtClaveSeccion" type="text" placeholder="Clave de Sección" class="form-control"></div>
                                <div class="form-group"><label>Clave de Nivel</label> <input id="txtClaveNivel" type="text" placeholder="Clave de Nivel" class="form-control"></div>
                                <div class="form-group"><label>Largo de Ubicación</label> <input id="txtLargoUbicacion" type="text" placeholder="Largo de Ubicación" class="form-control"></div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group"><label>Alto de Ubicación</label> <input id="txtAltoUbicacion" type="text" placeholder="Alto de Ubicación" class="form-control"></div>
                                <div class="form-group"><label>Ancho de Ubicación</label> <input id="txtAnchoUbicacion" type="text" placeholder="Ancho de Ubicación" class="form-control"></div>
                                <div class="form-group"><label>Ubicación</label> <input id="txtUbicacion" type="text" placeholder="Ubicación" class="form-control"></div>
                                <div class="form-group"><label>Peso Máximo</label> <input id="txtPesoMax" type="text" placeholder="Peso Máximo" class="form-control"></div>
                                <div class="form-group">
                                    <div class="col-sm-6">
                                        <div class="radio">
                                            <input type="radio" name="radio1" id="radio1" value="1">
                                            <label for="radio1">
                                                Ubicación de Rack
                                            </label>
                                        </div>
                                        <div class="radio">
                                            <input type="radio" name="radio1" id="radio2" value="2">
                                            <label for="radio1">
                                                Ubicación de Piso
                                            </label>
                                        </div>
                                    </div>

                                    <div class="col-sm-6">
                                        <div class="checkbox">
                                            <input type="checkbox" name="checkbox1" id="checkbox1" value="1">
                                            <label for="checkbox1">
                                                Picking
                                            </label>
                                        </div>
                                        <div class="checkbox">
                                            <input type="checkbox" name="checkbox2" id="checkbox2" value="1">
                                            <label for="checkbox2">
                                                Ubicación de PTL
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="pull-right"><br>
                                    <a href="#" onclick="cancelar()"><button type="button" class="btn btn-white" id="btnCancel">Cerrar</button></a>&nbsp;&nbsp;
                                    <button type="button" class="btn btn-primary ladda-button" data-style="contract" id="btnSave">Guardar</button>
                                </div>
                                <input type="hidden" id="hiddenUbicacion">
                            </div>
                        </div>

                    </div>
                </form>
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
<!-- iCheck -->
<script src="/js/plugins/iCheck/icheck.min.js"></script>


<script type="text/javascript">

    $('.i-checks').iCheck({
        checkboxClass: 'icheckbox_square-green',
        radioClass: 'iradio_square-green'
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
        })

        $(grid_selector).jqGrid({
            url:'/api/ubicaciones/lista/index.php',
            datatype: "json",
            height: 250,
            postData: {
                criterio: $("#txtCriterio").val()
            },
            mtype: 'POST',
            colNames:['Clave','Zona de Almacenaje','Ubicación',""],/*'Calle y Numero','Colonia','Ciudad','Estado','Pais','Codigo Postal','RFC','Telefono1','Telefono2'*/
            colModel:[
                //{name:'id',index:'id', width:60, sorttype:"int", editable: false},

                {name:'idy_ubica',index:'idy_ubica',width:40, editable:false, sortable:false},
                {name:'des_almac',index:'des_almac',width:180, editable:false, sortable:false},
                {name:'Ubicacion',index:'Ubicacion',width:100, editable:false, sortable:false, resizable: false},
                /*{name:'Ciudad',index:'cve_gpoart',width:50, editable:false, sortable:false, resizable: false},
                 {name:'Estado',index:'cve_gpoart',width:50, editable:false, sortable:false, resizable: false},
                 {name:'Pais',index:'cve_gpoart',width:50, editable:false, sortable:false, resizable: false},
                 {name:'CodigoPostal',index:'cve_gpoart',width:50, editable:false, sortable:false, resizable: false},
                 {name:'RFC',index:'cve_gpoart',width:50, editable:false, sortable:false, resizable: false},
                 {name:'Telefono1',index:'cve_gpoart',width:50, editable:false, sortable:false, resizable: false},
                 {name:'Telefono2',index:'cve_gpoart',width:50, editable:false, sortable:false, resizable: false},*/
                {name:'myac',index:'', width:80, fixed:true, sortable:false, resize:false, formatter:imageFormat},
            ],
            rowNum:30,
            rowList:[30,40,50],
            pager: pager_selector,
            sortname: 'idy_ubica',
            viewrecords: true,
            sortorder: "desc"
        });

        // Setup buttons
        $("#grid-table").jqGrid('navGrid', '#grid-pager',
            {edit: false, add: false, del: false, search: false},
            {height: 200, reloadAfterSubmit: true}
        );


        $(window).triggerHandler('resize.jqGrid');//trigger window resize to make the grid get the correct size
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        //////////////////////////////////////////////////////////aqui se construye la funcion de cada item del grid//////////////////////////////////////////////////////////
        function imageFormat( cellvalue, options, rowObject ){
            //return '<img src="'+cellvalue+'" />&nbsp;&nbsp;&nbsp;&nbsp;<img src="'+cellvalue+'" />';
            var serie = rowObject[0];
            var correl = rowObject[4];
            var url = "x/?serie="+serie+"&correl="+correl;
            var url2 = "v/?serie="+serie+"&correl="+correl;
            $("#hiddenUbicacion").val(serie);

            // la funcion downloadxml ejecuta el index.php que esta en la carpeta x pasandole la serie y el correlativo via get
            // la funcion viewPdf ejecuta el index.php que esta en la carpeta v pasandole la serie y el correlativo via get

            var html = '';
            //html += '<a href="#" onclick="ver(\''+serie+'\')"><i class="fa fa-eye" alt="Ver"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            html += '<a href="#" onclick="editar(\''+serie+'\')"><i class="fa fa-edit" alt="Editar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            html += '<a href="#" onclick="borrar(\''+serie+'\')"><i class="fa fa-eraser" alt="Borrar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';

            //return '<a href="#" onclick="downloadxml(\''+url+'\')"><i class="ace-icon fa fa-download" alt="Download"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="#" onclick="viewPdf(\''+url2+'\')"><i class="ace-icon fa fa-file-pdf-o" alt="Ver PDF"></i></a>';
            return html;
        }

        function aceSwitch( cellvalue, options, cell ) {
            setTimeout(function(){
                $(cell) .find('input[type=checkbox]')
                    .addClass('ace ace-switch ace-switch-5')
                    .after('<span class="lbl"></span>');
            }, 0);
        }
        //enable datepicker
        function pickDate( cellvalue, options, cell ) {
            setTimeout(function(){
                $(cell) .find('input[type=text]')
                    .datepicker({format:'yyyy-mm-dd' , autoclose:true});
            }, 0);
        }

        function beforeDeleteCallback(e) {
            var form = $(e[0]);
            if(form.data('styled')) return false;

            form.closest('.ui-jqdialog').find('.ui-jqdialog-titlebar').wrapInner('<div class="widget-header" />')
            style_delete_form(form);

            form.data('styled', true);
        }

        function reloadPage() {
            var grid = $(grid_selector);
            $.ajax({
                url: "index.php",
                dataType: "json",
                success: function(data){
                    grid.trigger("reloadGrid",[{current:true}]);
                },
                error: function(){}
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
        function styleCheckbox(table) {
        }

        //unlike navButtons icons, action icons in rows seem to be hard-coded
        //you can change them like this in here if you want
        function updateActionIcons(table) {
        }

        //replace icons with FontAwesome icons like above

        function updatePagerIcons(table) {
        }

        function enableTooltips(table) {
            $('.navtable .ui-pg-button').tooltip({container:'body'});
            $(table).find('.ui-pg-div').tooltip({container:'body'});
        }

        //var selr = jQuery(grid_selector).jqGrid('getGridParam','selrow');

        $(document).one('ajaxloadstart.page', function(e) {
            $(grid_selector).jqGrid('GridUnload');
            $('.ui-jqdialog').remove();
        });
    });

    //////////////////////////////////////////////////////////hace un reload del grid llamando al php en la carpera "c" para hacer la consulta//////////////////////////////////////////////////////////
    function ReloadGrid() {
        $('#grid-table').jqGrid('clearGridData')
            .jqGrid('setGridParam', {postData: {
                criterio: $("#txtCriterio").val(),
            }, datatype: 'json', page : 1})
            .trigger('reloadGrid',[{current:true}]);
    }

    function downloadxml( url ) {
        var win = window.open(url, '_blank');
        win.focus();
    }

    function viewPdf( url ) {
        var win = window.open(url, '_blank');
        win.focus();
    }

    $modal0 = null;

    function borrar(_codigo) {
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                idy_ubica : _codigo,
                action : "delete"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/ubicaciones/update/index.php',
            success: function(data) {
                if (data.success == true) {
                    //$('#codigo').prop('disabled', true);
                    ReloadGrid();
                }
            }
        });
    }

    function editar(_codigo) {
        $("#hiddenUbicacion").val(_codigo);
        $("#_title").html('<h3>Editar Ubicación</h3>');
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                codigo : _codigo,
                action : "load"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/ubicaciones/update/index.php',
            success: function(data) {
                if (data.success == true) {
                    $("#hiddenUbicacion").val(data.idy_ubica);
                    $("#Almacen").val(data.cve_almac);
                    $("#txtClavePasillo").val(data.cve_pasillo);
                    $("#txtClaveRack").val(data.cve_rack);
                    $("#txtClaveSeccion").val(data.Seccion);
                    $("#txtClaveNivel").val(data.cve_nivel);
                    $("#txtLargoUbicacion").val(data.num_largo);
                    $("#txtAltoUbicacion").val(data.num_alto);
                    $("#txtAnchoUbicacion").val(data.num_ancho);
                    $("#txtUbicacion").val(data.Ubicacion);
                    $("#txtPesoMax").val(data.PesoMaximo);
                    if (data.orden_secuencia == '1') {
                        $('#radio1').iCheck('check');
                        $('#radio2').iCheck('uncheck');
                    } else {
                        $('#radio2').iCheck('check');
                        $('#radio1').iCheck('uncheck');
                    }
                    if (data.CodigoCSD == '1') {
                        $('#checkbox1').iCheck('check');
                    } else {
                        $('#checkbox1').iCheck('uncheck');
                    }
                    if (data.Reabasto == '1') {
                        $('#checkbox2').iCheck('check');
                    } else {
                        $('#checkbox2').iCheck('uncheck');
                    }
                    l.ladda('stop');
                    $("#btnCancel").show();

                    $('#list').removeAttr('class').attr('class', '');
                    $('#list').addClass('animated');
                    $('#list').addClass("fadeOutRight");
                    $('#list').hide();

                    $('#FORM').show();
                    $('#FORM').removeAttr('class').attr('class', '');
                    $('#FORM').addClass('animated');
                    $('#FORM').addClass("fadeInRight");

                    $("#hiddenAction").val("edit");
                }
            }
        });
    }

    function cancelar() {
        $(':input','#myform')
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
    }

    function agregar() {
        $("#_title").html('<h3>Agregar Ubicación</h3>');
        $(':input','#myform')
            .removeAttr('checked')
            .removeAttr('selected')
            .not(':button, :submit, :reset, :hidden, :radio, :checkbox')
            .val('');
        $('#list').removeAttr('class').attr('class', '');
        $('#list').addClass('animated');
        $('#list').addClass("fadeOutRight");
        $('#list').hide();

        $('#FORM').show();
        $('#FORM').removeAttr('class').attr('class', '');
        $('#FORM').addClass('animated');
        $('#FORM').addClass("fadeInRight");
        $("#hiddenAction").val("add");
        $("#hiddenUbicacion").val("0");
        $('#checkbox2').iCheck('disable');
        $("#checkbox1").click(function() {
            $("#checkbox2").iCheck('enable');
        });
    }

    var l = $( '.ladda-button' ).ladda();
    l.click(function() {

        $("#btnCancel").hide();

        l.ladda( 'start' );

            $.post('/api/ubicaciones/update/index.php',
                {
                    idy_ubica : $("#hiddenUbicacion").val(),
                    cve_almac: $("#Almacen").val(),
                    cve_pasillo: $("#txtClavePasillo").val(),
                    cve_rack: $("#txtClaveRack").val(),
                    Seccion: $("#txtClaveSeccion").val(),
                    cve_nivel: $("#txtClaveNivel").val(),
                    num_largo: $("#txtLargoUbicacion").val(),
                    num_alto: $("#txtAltoUbicacion").val(),
                    num_ancho: $("#txtAnchoUbicacion").val(),
                    Ubicacion: $("#txtUbicacion").val(),
                    PesoMaximo: $("#txtPesoMax").val(),
                    orden_secuencia: $('input[name=radio1]:checked').val(),
                    CodigoCSD: $('input[name=checkbox1]:checked').val(),
                    Reabasto: $('input[name=checkbox2]:checked').val(),
                    action : $("#hiddenAction").val()
                },
                function(response){
                    console.log(response);
                }, "json")
                .always(function() {
                    l.ladda('stop');
                    $("#btnCancel").show();
                    cancelar()
                    ReloadGrid();
                });
    });

</script>