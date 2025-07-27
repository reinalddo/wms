<?php
$listaAlm = new \Almacen\Almacen();
?>
<link href="/css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">
<link href="/css/plugins/iCheck/custom.css" rel="stylesheet">
<link href="/css/plugins/awesome-bootstrap-checkbox/awesome-bootstrap-checkbox.css" rel="stylesheet">
<link href="/css/plugins/summernote/summernote.css" rel="stylesheet">

<link rel="stylesheet" href="/css/plugins/acordion/reset.css"> <!-- CSS reset -->
<link rel="stylesheet" href="/css/plugins/acordion/style.css"> <!-- Resource style -->

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

    <h3>Ubicación de Almacenaje</h3>

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
                            <h3>Agregar Ubicación de Almacenaje</h3>
                        </div>
                    </div>
                </div>
                <form id="myform">
                    <input type="hidden" id="hiddenAction" name="hiddenAction">
                    <div class="ibox-content">
                        <div class="row">

                            <div class="col-lg-6 b-r">
                                <div class="form-group">
                                    <select class="form-control" id="Almacen" name="Almacen">
                                        <option value="">Seleccione el Almacén</option>
                                        <?php foreach( $listaAlm->getAll() AS $p ): ?>
                                            <option value="<?php echo $p->cve_almac; ?>"><?php echo $p->des_almac; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Rack</label>
                                    <input id="NRack" class="touchspin1" type="text" value="0" name="NRack">
                                </div>
                                <div class="form-group"><label>Secciones por Rack</label>
                                    <input id="NSec" class="touchspin1" type="text" value="0" name="NSec">
                                </div>
                                <div class="form-group"><label>Ubicaciones por Sección</label>
                                    <input id="UNiv" class="touchspin1" type="text" value="0" name="UNiv">
                                </div>
                                <div class="form-group"><label>Alto de Ubicación</label>
                                    <input id="AlUbi" class="touchspin1" type="text" value="0" name="demo1">
                                </div>
                                <div class="form-group"><label>Largo de Ubicación</label>
                                    <input id="LaUbi" class="touchspin1" type="text" value="0" name="demo1">
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group"><label>Pasillo</label> <input id="Pas" type="text" placeholder="Pasillo" class="form-control"></div>
                                <div class="form-group">
                                    <div class="col-sm-12">
                                        <div class="col-sm-4">
                                            <div class="radio">
                                                <input type="radio" name="radio1" id="radio1" value="1">
                                                <label for="radio1">
                                                    Niveles
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-sm-8">
                                            <input id="NNivel" class="touchspin1" type="text" value="0" name="NNivel">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-sm-12">
                                        <div class="col-sm-4">
                                            <div class="radio">
                                                <input type="radio" name="radio1" id="radio2" value="2">
                                                <label for="radio1">
                                                    Rango de Nivel
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-sm-12">
                                        <div class="col-md-1"><label style="margin-top:5px;">Del</label></div>
                                        <div class="col-md-5"><input id="Nini" class="touchspin1" type="text" value="0" name="demo1"></div>
                                        <div class="col-md-1"><label style="margin-top:5px;">Al</label></div>
                                        <div class="col-md-5"><input id="NFin" class="touchspin1" type="text" value="0" name="demo1"></div>
                                    </div>
                                </div>
                                <div class="form-group"><label style="margin-top:15px;">Ancho de Ubicación</label> <input id="AnUbi" type="text" placeholder="Ancho de Ubicación" class="form-control"></div>
                                <div class="form-group"><label>Peso Máximo</label> <input id="PMax" type="text" placeholder="Peso Máximo" class="form-control"></div>
                                <div class="form-group">
                                    <div class="col-sm-6">
                                        <div class="radio">
                                            <input type="radio" name="ubica_rack" id="radio1" value="1">
                                            <label for="radio1">
                                                Ubicación de Rack
                                            </label>
                                        </div>
                                        <div class="radio">
                                            <input type="radio" name="ubica_rack" id="radio2" value="2">
                                            <label for="radio1">
                                                Ubicación de Piso
                                            </label>
                                        </div>
                                    </div>

                                    <div class="col-sm-6">
                                        <div class="checkbox">
                                            <input type="checkbox" name="Pick" id="Pick" value="1">
                                            <label for="checkbox1">
                                                Picking
                                            </label>
                                        </div>
                                        <div class="checkbox">
                                            <input type="checkbox" name="ubicaptl" id="ChkPTL" value="1">
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

<div class="wrapper wrapper-content  animated fadeInRight" id="DetalleUbicacion" style="display: none">

    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-title">
                    <div class="row">
                        <div class="col-lg-6" id="_title2">
                            <h3>Editar Ubicación de Almacenaje</h3>
                        </div>
                    </div>
                </div>
                <form id="myform">
                    <input type="hidden" id="hiddenAction" name="hiddenAction">
                    <div class="ibox-content">
                        <div class="row">

                            <div class="col-lg-6 b-r">
                                <div class="form-group">
                                    <label>Almacén</label>
                                    <select class="form-control" id="Almacen2" name="Almacen2">
                                        <option value="">Seleccione el Almacén</option>
                                        <?php foreach( $listaAlm->getAll() AS $p ): ?>
                                            <option value="<?php echo $p->cve_almac; ?>"><?php echo $p->des_almac; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group"><label>Clave de Pasillo</label>
                                    <input id="Pas2" class="touchspin1" type="text" value="0" name="NSec2">
                                </div>
                                <div class="form-group">
                                    <label>Clave de Rack</label>
                                    <input id="NRack2" class="touchspin1" type="text" value="0" name="NRack2">
                                </div>
                                <div class="form-group"><label>Clave de Sección</label>
                                    <input id="NSec2" class="touchspin1" type="text" value="0" name="NSec2">
                                </div>
                                <div class="form-group"><label>Ubicación</label>
                                    <input id="AlUbica2" class="touchspin1" type="text" value="0" name="demo12">
                                </div>
                                <div class="form-group"><label>Largo de Ubicación</label>
                                    <input id="LaUbi2" class="touchspin1" type="text" value="0" name="demo12">
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group"><label>Alto de Ubicación</label>
                                    <input id="AlUbi2" class="touchspin1" type="text" value="0" name="demo12">
                                </div>
                                <div class="form-group"><label>Ancho de Ubicación</label>
                                    <input id="AnUbi2" class="touchspin1" type="text" value="0" name="demo12">
                                </div>
                                <div class="form-group"><label>Clave de Nivel</label>
                                    <input id="NNivel2" class="touchspin1" type="text" value="0" name="UNiv2">
                                </div>
                                <div class="form-group"><label>Peso Máximo</label> <input id="PMax2" type="text" placeholder="Peso Máximo" class="form-control"></div>
                                <div class="form-group">
                                    <div class="col-sm-6">
                                        <div class="radio">
                                            <input type="radio" name="ubica_rack2" id="radio12" value="1">
                                            <label for="radio1">
                                                Ubicación de Rack
                                            </label>
                                        </div>
                                        <div class="radio">
                                            <input type="radio" name="ubica_rack2" id="radio22" value="2">
                                            <label for="radio1">
                                                Ubicación de Piso
                                            </label>
                                        </div>
                                    </div>

                                    <div class="col-sm-6">
                                        <div class="checkbox">
                                            <input type="checkbox" name="Pick2" id="Pick2" value="1">
                                            <label for="checkbox1">
                                                Picking
                                            </label>
                                        </div>
                                        <div class="checkbox">
                                            <input type="checkbox" name="ubicaptl2" id="ChkPTL2" value="1">
                                            <label for="checkbox2">
                                                Ubicación de PTL
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="pull-right"><br>
                                    <a href="#" onclick="cancelar_two()"><button type="button" class="btn btn-white" id="btnCancel">Cerrar</button></a>&nbsp;&nbsp;
                                    <button type="button" class="btn btn-primary ladda-button2" data-style="contract" id="btnSave">Editar</button>
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

<div class="wrapper wrapper-content  animated fadeInRight" id="FORM2" style="display: none">
    <div class="wrapper wrapper-content">
        <div class="row">
            <div class="col-lg-4">
                <div class="ibox float-e-margins">
                    <div class="ibox-content mailbox-content">
                        <div class="file-manager">
                            <div class="widget style1 navy-bg">
                                <div class="row vertical-align">
                                    <div class="col-xs-3">
                                        <i class="fa fa-list fa-3x"></i>
                                    </div>
                                    <div class="col-xs-9 text-right">
                                        <h2 class="font-bold">PASILLO <span id="Pasillo"></span></h2>
                                    </div>
                                </div>
                            </div>
                            <div class="space-25"></div>

                            <ul class="cd-accordion-menu animated">
                                <li class="has-children">
                                    <input type="checkbox" name ="group-1" id="group-1" checked>
                                    <label for="group-1">Rack <span id="Numero_Rack"></span></label>

                                    <ul id="NivelesSecciones">
                                    </ul>
                                </li>

                            </ul> <!-- cd-accordion-menu -->

                            <div class="clearfix"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-8 animated fadeInRight">
                <div class="mail-box">
                    <div class="mail-body">
                        <div class="widget style1 navy-bg">
                            <div class="row vertical-align">
                                <div class="col-xs-3">
                                    <i class="fa fa-tasks fa-3x"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <h2 id="title"></h2>
                                </div>
                            </div>
                        </div>
                        <div ><hr>

                        <div class="col-lg-12" id="Niveles">
                        </div>
                        <div class="col-lg-12" id="Secciones">
                        </div>
                        <div class="col-lg-12" id="Ubicaciones">
                        </div>

                    </div>
                    <div class="clearfix"></div>

                </div>
            </div>
            <div class="col-lg-12 animated fadeInRight text-right">
                <a href="#" onclick="cancelar_tree()"><button type="button" class="btn btn-white" id="btnCancel">Cerrar</button></a>&nbsp;&nbsp;
            </div>
        </div>
    </div>
</div>

<!-- Input Mask-->
<script src="/js/plugins/jasny/jasny-bootstrap.min.js"></script>

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
<!-- TouchSpin -->
<script src="/js/plugins/touchspin/jquery.bootstrap-touchspin.min.js"></script>
<!-- Acordion -->
<script src="/js/plugins/acordion/tree.js"></script>

<script type="text/javascript">
    /****************************** ACORDION ******************************/
    var acc = document.getElementsByClassName("accordion");
    var i;

    for (i = 0; i < acc.length; i++) {
        acc[i].onclick = function() {
            this.classList.toggle("active");
            var panel = this.nextElementSibling;
            if (panel.style.maxHeight){
                panel.style.maxHeight = null;
            } else {
                panel.style.maxHeight = '100%';
            }
        }
    }
    /*********************************************************************/

    $(".touchspin1").TouchSpin({
        min: -1000000000,
        max: 1000000000,
        stepinterval: 50,
        maxboostedstep: 10000000,
        buttondown_class: 'btn btn-white',
        buttonup_class: 'btn btn-white'
    });

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
            url:'/api/ubicacionalmacenaje/lista/index.php',
            datatype: "json",
            height: 250,
            postData: {
                criterio: $("#txtCriterio").val()
            },
            mtype: 'POST',
            colNames:['', 'Clave','Descripción de Almacén','Ubicación',""],/*'Calle y Numero','Colonia','Ciudad','Estado','Pais','Codigo Postal','RFC','Telefono1','Telefono2'*/
            colModel:[
                //{name:'id',index:'id', width:60, sorttype:"int", editable: false},

                {name:'cve_almac',index:'cve_almac',width:180, editable:false, sortable:false, hidden:true},
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
            rowList:[10,20,30],
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
            var almacen = rowObject[1];
            var correl = rowObject[4];
            var url = "x/?serie="+serie+"&correl="+correl;
            var url2 = "v/?serie="+serie+"&correl="+correl;
            $("#hiddenUbicacion").val(serie);

            // la funcion downloadxml ejecuta el index.php que esta en la carpeta x pasandole la serie y el correlativo via get
            // la funcion viewPdf ejecuta el index.php que esta en la carpeta v pasandole la serie y el correlativo via get

            var html = '';
            //html += '<a href="#" onclick="ver(\''+serie+'\')"><i class="fa fa-eye" alt="Ver"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            html += '<a href="#" onclick="resultados(\''+serie+'\',\''+almacen+'\')"><i class="fa fa-edit" alt="Editar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            html += '<a href="#" onclick="borrar(\''+serie+'\',\''+almacen+'\')"><i class="fa fa-eraser" alt="Borrar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';

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

    function Secciones(_codigo,_nivel) {

        $("#title").html('<h2 class="font-bold">Nivel '+_nivel+'</h2>');

        $('#Ubicaciones').hide();

        $('#Niveles').removeAttr('class').attr('class', '');
        $('#Niveles').addClass('animated');
        $('#Niveles').addClass("fadeOutRight");
        $('#Niveles').hide();

        $('#Secciones').show();
        $('#Secciones').removeAttr('class').attr('class', '');
        $('#Secciones').addClass('animated');
        $('#Secciones').addClass("fadeInRight");
    }

    function Ubicaciones(_almacen,_nivel,_seccion) {

        $("#title").html('<h2 class="font-bold">Sección '+_seccion+'</h2>');

        $('#Secciones').removeAttr('class').attr('class', '');
        $('#Secciones').addClass('animated');
        $('#Secciones').addClass("fadeOutRight");
        $('#Secciones').hide();

        $('#Ubicaciones').show();
        $('#Ubicaciones').removeAttr('class').attr('class', '');
        $('#Ubicaciones').addClass('animated');
        $('#Ubicaciones').addClass("fadeInRight");

        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                cve_almac : _almacen,
                cve_nivel : _nivel,
                Seccion : _seccion,
                action : "ubicacion"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/ubicacionalmacenaje/update/index.php',
            success: function(data) {
                if (data.success == true) {
                    $('#Ubicaciones').hide();
                    $("#Ubicaciones").html(data.Ubicaciones);
                    $('#Ubicaciones').show();
                }
            }
        });
    }

    function borrarUbicacion(_almacen,_nivel,_seccion,_ubicacion) {
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                cve_almac : _almacen,
                cve_nivel : _nivel,
                Seccion : _seccion,
                Ubicacion : _ubicacion,
                action : "deleteUbica"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/ubicacionalmacenaje/update/index.php',
            success: function(data) {
                if (data.success == true) {
                    $('#Ubicaciones').removeAttr('class').attr('class', '');
                    $('#Ubicaciones').addClass('animated');
                    $('#Ubicaciones').addClass("fadeOutRight");
                    $('#Ubicaciones').hide();
                    $("#Ubicaciones").html(data.Ubicaciones);
                    $('#Ubicaciones').show();
                    $('#Ubicaciones').removeAttr('class').attr('class', '');
                    $('#Ubicaciones').addClass('animated');
                    $('#Ubicaciones').addClass("fadeInRight");
                }
            }
        });
    }

    function borrar(_almacen) {
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                cve_almac : _almacen,
                action : "delete"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/ubicacionalmacenaje/update/index.php',
            success: function(data) {
                if (data.success == true) {
                    ReloadGrid();
                }
            }
        });
    }

    function resultados(_codigo) {

        $("#title").html('<h2 class="font-bold">Niveles</h2>');

        $('#Secciones').hide();
        $('#Ubicaciones').hide();

        $('#list').removeAttr('class').attr('class', '');
        $('#list').addClass('animated');
        $('#list').addClass("fadeOutRight");
        $('#list').hide();

        $('#FORM2').show();
        $('#FORM2').removeAttr('class').attr('class', '');
        $('#FORM2').addClass('animated');
        $('#FORM2').addClass("fadeInRight");

        /****************** RACK / PASILLO *********************/
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                codigo : _codigo,
                action : "rack_pasillo"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/ubicacionalmacenaje/update/index.php',
            success: function(data) {
                if (data.success == true) {
                    $("#Pasillo").html(data.cve_pasillo);
                    $("#Numero_Rack").html(data.cve_rack);
                    $("#Niveles").html(data.Niveles);
                    $("#Secciones").html(data.Secciones);
                    $("#Ubicaciones").html(data.Ubicaciones);
                    $("#NivelesSecciones").html(data.NivelesSecciones);
                }
            }
        });
    }

    function DetalleUbicacion(_codigo,_ubicacion) {
        $("#_title2").html('<h3>Editar Ubicación de Almacenaje (Ubicación <span id="id_ubicacion"></span>)</h3>');
        $('#FORM2').removeAttr('class').attr('class', '');
        $('#FORM2').addClass('animated');
        $('#FORM2').addClass("fadeOutRight");
        $('#FORM2').hide();

        $('#DetalleUbicacion').show();
        $('#DetalleUbicacion').removeAttr('class').attr('class', '');
        $('#DetalleUbicacion').addClass('animated');
        $('#DetalleUbicacion').addClass("fadeInRight");

        /************** ENABLED ***************/
        $("#Pick2").click(function() {
            $("#ChkPTL2").iCheck('enable');
        });
        $('input[name^="radio1"]').change(function () {
            if (this.value == 1) {
                $('#txtNivel2').attr("disabled", false);
                $('#Nini2').attr("disabled", true);
                $('#NFin2').attr("disabled", true);
            } else if (this.value == 2) {
                $('#NNivel2').attr("disabled", true); //$("#NNivel").attr("disabled", false)
                $('#Nini2').attr("disabled", false);
                $('#NFin2').attr("disabled", false);
            }
        });

        $("#hiddenUbicacion").val(_codigo);
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                idy_ubica : _codigo,
                action : "cargar"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/ubicacionalmacenaje/update/index.php',
            success: function(data) {
                if (data.success == true) {
                    $("#id_ubicacion").html(data.idy_ubica);
                    $("#Almacen2").val(data.cve_almac);
                    $("#Pas2").val(data.cve_pasillo);
                    $("#NRack2").val(data.cve_rack);
                    $("#NSec2").val(data.Seccion);
                    $("#NNivel2").val(data.cve_nivel);
                    $("#AlUbica2").val(data.Ubicacion);
                    $("#LaUbi2").val(data.num_largo);
                    $("#AlUbi2").val(data.num_alto);
                    $("#AnUbi2").val(data.num_ancho);
                    $("#PMax2").val(data.PesoMaximo);
                    if (data.orden_secuencia == '1') {
                        $('#radio12').iCheck('check');
                        $('#radio22').iCheck('uncheck');
                    } else {
                        $('#radio22').iCheck('check');
                        $('#radio12').iCheck('uncheck');
                    }
                    if (data.orden_secuencia == '2') {
                        $('#radio12').iCheck('uncheck');
                        $('#radio22').iCheck('check');
                    } else {
                        $('#radio22').iCheck('uncheck');
                        $('#radio12').iCheck('check');
                    }
                    if (data.picking == 'S') {
                        $('#Pick2').iCheck('check');
                    } else {
                        $('#Pick2').iCheck('uncheck');
                    }
                    if (data.Reabasto == '1') {
                        $('#ChkPTL2').iCheck('check');
                    } else {
                        $('#ChkPTL2').iCheck('uncheck');
                    }
                    l.ladda('stop');
                    $("#btnCancel").show();

                    $('#FORM2').removeAttr('class').attr('class', '');
                    $('#FORM2').addClass('animated');
                    $('#FORM2').addClass("fadeOutRight");
                    $('#FORM2').hide();

                    $('#DetalleUbicacion').show();
                    $('#DetalleUbicacion').removeAttr('class').attr('class', '');
                    $('#DetalleUbicacion').addClass('animated');
                    $('#DetalleUbicacion').addClass("fadeInRight");

                    $("#hiddenAction").val("edit");
                }
            }
        });
    }

    function cancelar_tree() {
        $('#FORM2').removeAttr('class').attr('class', '');
        $('#FORM2').addClass('animated');
        $('#FORM2').addClass("fadeOutRight");
        $('#FORM2').hide();

        $('#list').show();
        $('#list').removeAttr('class').attr('class', '');
        $('#list').addClass('animated');
        $('#list').addClass("fadeInRight");
        $('#list').addClass("wrapper");
        $('#list').addClass("wrapper-content");
    }

    function cancelar_two() {
        $('#DetalleUbicacion').removeAttr('class').attr('class', '');
        $('#DetalleUbicacion').addClass('animated');
        $('#DetalleUbicacion').addClass("fadeOutRight");
        $('#DetalleUbicacion').hide();

        $('#FORM2').show();
        $('#FORM2').removeAttr('class').attr('class', '');
        $('#FORM2').addClass('animated');
        $('#FORM2').addClass("fadeInRight");
        $('#FORM2').addClass("wrapper");
        $('#FORM2').addClass("wrapper-content");
    }

    function cancelar() {

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

    function salir() {
        $(':input','#myform')
            .removeAttr('checked')
            .removeAttr('selected')
            .not(':button, :submit, :reset, :hidden, :radio, :checkbox')
            .val('');
        $('#FORM2').removeAttr('class').attr('class', '');
        $('#FORM2').addClass('animated');
        $('#FORM2').addClass("fadeOutRight");
        $('#FORM2').hide();

        $('#list').show();
        $('#list').removeAttr('class').attr('class', '');
        $('#list').addClass('animated');
        $('#list').addClass("fadeInRight");
        $('#list').addClass("wrapper");
        $('#list').addClass("wrapper-content");
    }

    function agregar() {
        $("#_title").html('<h3>Agregar Ubicación de Almacenaje</h3>');
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
        $('#txtNivel').attr("disabled", true);
        $('#Nini').attr("disabled", true);
        $('#NFin').attr("disabled", true);
        $('#ChkPTL').iCheck('disable');
        /************** ENABLED ***************/
        $("#Pick").click(function() {
            $("#ChkPTL").iCheck('enable');
        });
        $('input[name^="radio1"]').change(function () {
            if (this.value == 1) {
                $('#txtNivel').attr("disabled", false);
                $('#Nini').attr("disabled", true);
                $('#NFin').attr("disabled", true);
            } else if (this.value == 2) {
                $('#NNivel').attr("disabled", true); //$("#NNivel").attr("disabled", false)
                $('#Nini').attr("disabled", false);
                $('#NFin').attr("disabled", false);
            }
        });
    }

    function MyParse(h)
    {
        var back, error = 0;
        if (h == "" )
            return error;
        try
        {
            parseFloat(h);
        }
        catch (Exception)
        {
            return error;
        }
        var ar = h.split('.');
        if (ar.length < 2)
            return parseFloat(h);
        back = parseInt(ar[0]);
        while (ar[1].length < 4)
            ar[1] += "0";
        ar[1] = ar[1].substring(0, 4);
        back += parseFloat(ar[1]) / 10000;
        return back;
    }

    function padLeft(nr, n, str){
        return Array(n-String(nr).length+1).join(str||'0')+nr;
    }

    var l = $( '.ladda-button' ).ladda();
    l.click(function() {

        $("#btnCancel").hide();

        var rack, sec = 0, nivs = 0, nivi = 0, nivf = 0, ubis;
        if ($("#Almacen").val() == "")
        {
            alert("Informacion Incompleta, Selecciona el almacen al que perteneceran las ubicaciones a crear");
            return;
        }
        if ($("#txtClavePasillo").val() == "")
        {
            alert("Informacion Incompleta, Introduce el nombre del pasillo");
            return;
        }
        if ($("#NRack").val() == "")
        {
            alert("Informacion Incompleta, Selecciona el rack a crear");
            return;
        }
        else
            rack = $("#NRack").val();

        if ($("#NSec").val() == 0)
        {
            alert("Informacion Incompleta, El rack al menos debe tener una sección");
            return;
        }
        else
        {
            sec = $("#NSec").val();
        }
        if ($("#NNivel").attr("disabled", false))
        {
            if ($("#NNivel").val() == 0)
            {
                alert("Informacion Incompleta, El rack al menos debe tener un nivel");
                return;
            }
            else
            {
                nivs = $("#NNivel").val();
                nivi = 0;
                nivf = 0;
            }
        }
        else
        {
            if ($("#NIni").val() == 0)
            {
                alert("Informacion Incompleta, Se debe especificar al menos un nivel");
                return;
            }
            else
            {
                nivs = 0;
                nivi = $("#NIni").val();
                nivf = $("#NFin").val();
                if (nivi > nivf)
                {
                    alert("Informacion Incorrecta, El nivel de inicio es mayor que el nivel final");
                    nivi = 0;
                    nivf = 0;
                    $("#NIni").focus();
                    return;
                }
            }
        }
        if ($("#UNiv").val() == 0)
        {
            alert("Informacion Incompleta, Los niveles deben tener al menos una ubicacion");
            $("#UNiv").focus();
            return;
        }
        else
            ubis = $("#UNiv").val();

        var status = "1", orden = "1", cverp, picking;

        var alto, ancho, largo, poc, pmax, vdis = 0;

        cverp = "";

        alto = $("#AlUbi").val();

        //AlUbi.Text = DB.MyParse(AlUbi.Text).ToString();
        ancho = $("#AnUbi").val();
        //AnUbi.Text = DB.MyParse(AnUbi.Text).ToString();
        largo = $("#LaUbi").val();
        //LaUbi.Text = DB.MyParse(LaUbi.Text).ToString();
        if($("#Pick").prop("checked"))
            picking = "S";
        else
            picking = "N";
        poc = 0;
        pmax = $("#PMax").val();
        //PMax.Text = DB.MyParse(PMax.Text).ToString();
        var cont = 1;
        var niv_dif = 0, nini = 0, nfin = 0;
        if (nivs == 0)
        {
            niv_dif = (nivf - nivi) + 1;
            nini = nivi;
            nfin = nivf;
        }
        else
        {
            niv_dif = nivs;
            nini = 1;
            nfin = nivs;
        }

        arrDet = [];

        var total = sec * ubis * niv_dif;
        for (NIV = nini; NIV <= nfin; NIV++)
        {
            var ubi = 1;
            for (secc = 1; secc <= sec; secc++)
            {
                //PBar.Texto = "Creando Seccion " + secc.ToString();
                for (UBI = 1; UBI <= ubis; UBI++)
                {
                    var _t = "";

                    if ($("#ChkPTL").prop("checked"))
                        _t = "PTL";

                    var TipoUbicacion = ($("#RBRack").prop("checked")) ? "R" : "P";

                    arrDet.push({
                        cve_almac : $("#Almacen").val(),
                        cve_pasillo : $("#Pas").val(),
                        cve_rack : rack,
                        cve_nivel : NIV,
                        Ubicacion : ubi,
                        orden_secuencia : orden,
                        Status : status,
                        CodigoCSD : cverp,
                        num_alto : alto,
                        num_ancho : ancho,
                        num_largo : largo,
                        num_volumenDisp : vdis,
                        PesoMaximo : pmax,
                        PesoOcupado : poc,
                        picking : picking,
                        Seccion : padLeft(secc, 3),
                        TipoUbicacion : TipoUbicacion,
                        Tecnologia : _t
                    });
                    cont++;
                    ubi++;
                }
            }
        }

        l.ladda( 'start' );

            $.post('/api/ubicacionalmacenaje/update/index.php',
                {
                    action : "add",
                    arrDet : arrDet
                },
                function(response){
                    console.log(response);
                }, "json")
                .always(function() {
                    l.ladda('stop');
                    $('#FORM').removeAttr('class').attr('class', '');
                    $('#FORM').addClass('animated');
                    $('#FORM').addClass("fadeOutRight");
                    $('#FORM').hide();
                    ReloadGrid();
                    $('#list').show();
                    $('#list').removeAttr('class').attr('class', '');
                    $('#list').addClass('animated');
                    $('#list').addClass("fadeInRight");
                });
    });

    /**************************************** EDITAR *******************************************/

    var l = $( '.ladda-button2' ).ladda();
    l.click(function() {

        $("#btnCancel").hide();

        var rack, sec = 0, nivs = 0, nivi = 0, nivf = 0, ubis;
        if ($("#Almacen2").val() == "")
        {
            alert("Informacion Incompleta, Selecciona el almacen al que perteneceran las ubicaciones a crear");
            return;
        }
        if ($("#txtClavePasillo2").val() == "")
        {
            alert("Informacion Incompleta, Introduce el nombre del pasillo");
            return;
        }
        if ($("#NRack2").val() == "")
        {
            alert("Informacion Incompleta, Selecciona el rack a crear");
            return;
        }
        else
            rack = $("#NRack2").val();

        if ($("#NSec2").val() == 0)
        {
            alert("Informacion Incompleta, El rack al menos debe tener una sección");
            return;
        }
        else
        {
            sec = $("#NSec2").val();
        }
        if ($("#NNivel2").attr("disabled", false))
        {
            if ($("#NNivel2").val() == 0)
            {
                alert("Informacion Incompleta, El rack al menos debe tener un nivel");
                return;
            }
            else
            {
                nivs = $("#NNivel2").val();
                nivi = 0;
                nivf = 0;
            }
        }
        else
        {
            if ($("#NIni2").val() == 0)
            {
                alert("Informacion Incompleta, Se debe especificar al menos un nivel");
                return;
            }
            else
            {
                nivs = 0;
                nivi = $("#NIni2").val();
                nivf = $("#NFin2").val();
                if (nivi > nivf)
                {
                    alert("Informacion Incorrecta, El nivel de inicio es mayor que el nivel final");
                    nivi = 0;
                    nivf = 0;
                    $("#NIni2").focus();
                    return;
                }
            }
        }
        if ($("#UNiv2").val() == 0)
        {
            alert("Informacion Incompleta, Los niveles deben tener al menos una ubicacion");
            $("#UNiv2").focus();
            return;
        }
        else
            ubis = $("#UNiv2").val();

        var status = "1", orden = "1", cverp, picking;

        var alto, ancho, largo, poc, pmax, vdis = 0;

        cverp = "";

        alto = $("#AlUbi2").val();

        //AlUbi.Text = DB.MyParse(AlUbi.Text).ToString();
        ancho = $("#AnUbi2").val();
        //AnUbi.Text = DB.MyParse(AnUbi.Text).ToString();
        largo = $("#LaUbi2").val();
        //LaUbi.Text = DB.MyParse(LaUbi.Text).ToString();
        if($("#Pick2").prop("checked"))
            picking = "S";
        else
            picking = "N";
        poc = 0;
        pmax = $("#PMax2").val();
        //PMax.Text = DB.MyParse(PMax.Text).ToString();
        var cont = 1;
        var niv_dif = 0, nini = 0, nfin = 0;
        if (nivs == 0)
        {
            niv_dif = (nivf - nivi) + 1;
            nini = nivi;
            nfin = nivf;
        }
        else
        {
            niv_dif = nivs;
            nini = 1;
            nfin = nivs;
        }

        arrDet = [];

        var total = sec * ubis * niv_dif;
        for (NIV = nini; NIV <= nfin; NIV++)
        {
            var ubi = 1;
            for (secc = 1; secc <= sec; secc++)
            {
                //PBar.Texto = "Creando Seccion " + secc.ToString();
                for (UBI = 1; UBI <= ubis; UBI++)
                {
                    var _t = "";

                    if ($("#ChkPTL2").prop("checked"))
                        _t = "PTL";

                    var TipoUbicacion = ($("#RBRack2").prop("checked")) ? "R" : "P";

                    arrDet.push({
                        cve_almac : $("#Almacen2").val(),
                        cve_pasillo : $("#Pas2").val(),
                        cve_rack : rack,
                        cve_nivel : NIV,
                        Ubicacion : ubi,
                        orden_secuencia : orden,
                        Status : status,
                        CodigoCSD : cverp,
                        num_alto : alto,
                        num_ancho : ancho,
                        num_largo : largo,
                        num_volumenDisp : vdis,
                        PesoMaximo : pmax,
                        PesoOcupado : poc,
                        picking : picking,
                        Seccion : padLeft(secc, 3),
                        TipoUbicacion : TipoUbicacion,
                        Tecnologia : _t
                    });
                    cont++;
                    ubi++;
                }
            }
        }

        l.ladda( 'start' );

        $.post('/api/ubicacionalmacenaje/update/index.php',
            {
                action : "edit",
                arrDet : arrDet
            },
            function(response){
                console.log(response);
            }, "json")
            .always(function() {
                l.ladda('stop');
                $('#DetalleUbicacion').removeAttr('class').attr('class', '');
                $('#DetalleUbicacion').addClass('animated');
                $('#DetalleUbicacion').addClass("fadeOutRight");
                $('#DetalleUbicacion').hide();
                ReloadGrid();
                $('#FORM2').show();
                $('#FORM2').removeAttr('class').attr('class', '');
                $('#FORM2').addClass('animated');
                $('#FORM2').addClass("fadeInRight");
            });
    });


</script>