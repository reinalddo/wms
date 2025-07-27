<?php

$listaAlm = new \Almacen\Almacen();
$listaGrup = new \GrupoArticulos\GrupoArticulos();
$listaMed = new \UnidadesMedida\UnidadesMedida();
$listaProv = new \Proveedores\Proveedores();
$listaTipcaja = new \TipoCaja\TipoCaja();
$listaSubgp = new \SubGrupoArticulos\SubGrupoArticulos();
$listaSubsub = new \SSubGrupoArticulos\SSubGrupoArticulos();
?>
<link href="/css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">

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

    <h3>Art&iacute;culos</h3>

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
                                        <i class="fa fa-search"></i>  Buscar
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
                            <h3>Agregar Artículo</h3>
                        </div>
                    </div>
                </div>
                <div class="ibox-content">
                    <form id="myform">
                        <div class="row">

                            <div class="col-lg-6 b-r">

                                    <div class="form-group"><label>C&oacute;digo del Artículo</label> <input id="codigo" name="codigo" type="text" placeholder="Código Artículo" class="form-control" maxlength="8" onkeyup="if (/\D/g.test(this.value)) this.value = this.value.replace(/\D/g,'')"></div>
                                    <div class="form-group"><label>Descripción de Artículo</label> <input id="des_articulo" name="des_articulo" type="text" placeholder="Descripción de Artículo" class="form-control"></div>
                                    <div class="form-group"><label>Observaciones</label> <input id="des_observ" name="des_observ" type="text" placeholder="Observaciones" class="form-control"></div>
                                    <div class="form-group">
                                        <label>Almacén</label>
                                        <select class="form-control" id="cve_almac" name="cve_almac">
                                            <?php foreach( $listaAlm->getAll() AS $p ): ?>
                                            <option value="<?php echo $p->cve_almac; ?>"><?php echo $p->des_almac; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="form-group"><label>Código de Barra</label> <input id="barras1" name="barras1" type="text" placeholder="Código de Barra" class="form-control"></div>
                                    <div class="form-group"><label>Código de Barra 2</label> <input id="barras2" name="barras2" type="text" placeholder="Código de Barra 2" class="form-control"></div>
                                    <div class="form-group"><label>Peso Unitario</label> <input id="peso" name="peso" type="text" placeholder="Peso Unitario" class="form-control"></div>
                                    <div class="form-group"><label>Unidades X Caja</label> <input id="num_multiplo" name="num_multiplo" type="text" placeholder="Unidades X Caja" class="form-control"></div>

                                    <div class="pull-left">
                                        <div class="form-group">
                                                <div style="margin-left: -100px;"><input type="checkbox" class="form-control"id="Caduca" name="Caduca" ></div>
                                                <div style="float: left;margin-top: -25px;/* width: 340px; */margin-left: 50px;"><label>Maneja Lotes</label></div>
                                        </div>
                                    </div>
                            </div>

                            <div class="col-lg-6">

                                <div class="form-group"><label>Cantidad en Tarima</label> <input id="num_multiploch" name="num_multiploch" type="text" placeholder="Cantidad en Tarima" class="form-control"></div>
                                <div class="form-group"><label>Máximo de Cajas a Subir en PTL</label> <input id="MaxCajas" name="MaxCajas" type="text" placeholder="Máximo de Cajas a Subir en PTL" class="form-control"></div>
                                <div class="form-group">
                                    <label>Descripción de Grupo</label>
                                    <select class="form-control" id="descrpGrup" name="descrpGrup" onchange="fetch_select(this.value);">
                                        <option>Seleccione</option>
                                        <?php foreach( $listaGrup->getAll() AS $p ): ?>
                                        <option value="<?php echo $p->cve_gpoart; ?>"><?php echo $p->des_gpoart; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Descripción de Subgrupo</label>
                                    <select class="form-control" id="descrpSubGrup" name="descrpSubGrup" onchange="fetch_subsub(this.value);">
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Descripción de Sub Sub Grupo</label>
                                    <select class="form-control" id="cve_ssgpo" name="cve_ssgpo">
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Descripción de Tipo de Caja</label>
                                    <select class="form-control" id="cve_tipcaja" name="cve_tipcaja">
                                        <?php foreach( $listaTipcaja->getAll() AS $p ): ?>
                                        <option value="<?php echo $p->cve_tipcaja; ?>"><?php echo $p->des_tipcaja; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Descripción de Proveedor</label>
                                    <select class="form-control" id="ID_Proveedor" name="ID_Proveedor">
                                        <?php foreach( $listaProv->getAll() AS $p ): ?>
                                        <option value="<?php echo $p->ID_Proveedor; ?>"><?php echo $p->Empresa; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Descripción de Unidad de Medida</label>
                                    <select class="form-control" id="cve_umed" name="cve_umed">
                                        <?php foreach( $listaMed->getAll() AS $p ): ?>
                                        <option value="<?php echo $p->cve_umed; ?>"><?php echo $p->des_umed; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                    <div class="pull-right">
                                        <a href="#" onclick="cancelar()"><button type="button" class="btn btn-white" id="btnCancel">Cerrar</button></a>&nbsp;&nbsp;
                                        <button type="button" class="btn btn-primary ladda-button" data-style="contract" id="btnSave">Guardar</button>
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

<script type="text/javascript">

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
            url:'/api/articulos/lista/index.php',
            datatype: "json",
            height: 250,
            postData: {
                criterio: $("#txtCriterio").val()
            },
            mtype: 'POST',
            colNames:['Clave de Artículo','Descripción de Artículo',""],
            colModel:[
                //{name:'id',index:'id', width:60, sorttype:"int", editable: false},

                {name:'cve_articulo',index:'cve_articulo',width:120, editable:false, sortable:false, resizable: false},
                {name:'des_articulo',index:'des_articulo',width:180, editable:false, sortable:false, resizable: false},
                {name:'myac',index:'', width:80, fixed:true, sortable:false, resize:false, formatter:imageFormat, frozen : true},
            ],
            rowNum:30,
            rowList:[10,20,30],
            pager: pager_selector,
            sortname: 'cve_gpoart',
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
                cve_articulo : _codigo,
                action : "delete"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/articulos/update/index.php',
            success: function(data) {
                if (data.success == true) {
                    //$('#codigo').prop('disabled', true);
                    ReloadGrid();
                }
            }
        });
    }

    /////// SELECT COMBO SUBGRUPO ///////
    function fetch_select(val) {
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                get_option : val,
                action : "inputSubSelect"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/subgrupodearticulos/update/index.php',
            success: function(data) {
                if (data.success == true) {
                    document.getElementById("descrpSubGrup").innerHTML=data.response;
                }
            }
        });
    }

    /////// SELECT COMBO SUBSUBGRUPO ///////
    function fetch_subsub(val) {
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                get_option : val,
                action : "inputSubSubSelect"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/ssubgrupodearticulos/update/index.php',
            success: function(data) {
                if (data.success == true) {
                    document.getElementById("cve_ssgpo").innerHTML=data.response;
                }
            }
        });
    }

    function editar(_codigo) {
        $("#_title").html('<h3>Editar Artículo</h3>');
        $("#hiddenID").val(_codigo);
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                cve_articulo : _codigo,
                action : "load"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/articulos/update/index.php',
            success: function(data) {
                if (data.success == true) {
                    $('#codigo').prop('disabled', true);

                    $.each( data, function( key, value ) {
                        $('#'+key).val(value);
                    });

                    $("#codigo").val(_codigo);

                    $("#descrpGrup").val(data.cve_gpoart);
                    $("#descrpSubGrup").val(data.cve_sgpoart);
                    $("#cve_ssgpo").val(data.cve_ssgpo);
                    $("#MaxCajas").val(data.max_cajas);
                    $("#ID_Proveedor").val(data.id_proveedor);

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
        $("#_title").html('<h3>Agregar Artículo</h3>');
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
        $('#codigo').prop('disabled', false);
    }

    var l = $( '.ladda-button' ).ladda();
    l.click(function() {

        if ($("#codigo").val()=="") {
            return;
        }

        if ($("#descripcion").val()=="") {
            return;
        }

        $("#btnCancel").hide();

        l.ladda( 'start' );

        if ($("#hiddenAction").val()=="add") {
            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    cve_articulo : $("#codigo").val(),
                    action : "exists"
                },
                beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                },
                url: '/api/articulos/update/index.php',
                success: function(data) {
                    if (data.success == false) {
                        $.ajax({
                            type: "POST",
                            dataType: "json",
                            data: {
                                des_articulo: $("#des_articulo").val(),
                                cve_umed: $("#cve_umed").val(),
                                cve_ssgpo: $("#cve_ssgpo").val(),
                                num_multiplo: $("#num_multiplo").val(),
                                des_observ: $("#des_observ").val(),
                                cve_tipcaja: $("#cve_tipcaja").val(),
                                ID_Proveedor: $("#ID_Proveedor").val(),
                                peso: $("#peso").val(),
                                num_multiploch: $("#num_multiploch").val(),
                                cve_almac: $("#cve_almac").val(),
                                barras1: $("#barras1").val(),
                                Caduca: $("#Caduca").val(),
                                Compuesto: $("#Compuesto").val(),
                                MaxCajas: $("#MaxCajas").val(),
                                barras2: $("#barras2").val(),
                                codigo: $("#codigo").val(),
                                action: "add"
                            },
                            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                            },
                            url: '/api/articulos/update/index.php',
                            success: function(data) {
                                if (data.success == true) {
                                    cancelar();
                                    ReloadGrid();
                                    l.ladda('stop');
                                    $("#btnCancel").show();
                                } else {
                                    alert(data.err);
                                    l.ladda('stop');
                                    $("#btnCancel").show();
                                }
                            }
                        });
                    }
                }
            });
        } else {
            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    des_articulo: $("#des_articulo").val(),
                    cve_umed: $("#cve_umed").val(),
                    cve_ssgpo: $("#cve_ssgpo").val(),
                    num_multiplo: $("#num_multiplo").val(),
                    des_observ: $("#des_observ").val(),
                    cve_tipcaja: $("#cve_tipcaja").val(),
                    ID_Proveedor: $("#ID_Proveedor").val(),
                    peso: $("#peso").val(),
                    num_multiploch: $("#num_multiploch").val(),
                    cve_almac: $("#cve_almac").val(),
                    barras1: $("#barras1").val(),
                    Caduca: $("#Caduca").val(),
                    Compuesto: $("#Compuesto").val(),
                    MaxCajas: $("#MaxCajas").val(),
                    barras2: $("#barras2").val(),
                    codigo: $("#codigo").val(),
                    action: "edit"
                },
                beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                },
                url: '/api/articulos/update/index.php',
                success: function(data) {
                    if (data.success == true) {
                        cancelar();
                        ReloadGrid();
                        l.ladda('stop');
                        $("#btnCancel").show();
                    } else {
                        alert(data.err);
                        l.ladda('stop');
                        $("#btnCancel").show();
                    }
                }
            });
        }
    });

    $("#codigo").keydown(function(event) {
        // Allow only backspace and delete
        if ( event.keyCode == 46 || event.keyCode == 8 ) {
            // let it happen, don't do anything
        }
        else {
            // Ensure that it is a number and stop the keypress
            if (event.keyCode < 48 || event.keyCode > 57 ) {
                event.preventDefault();
            }
        }
    });
</script>