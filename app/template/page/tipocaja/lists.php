<?php 

$mod=31;
$var1=81;
$var2=82;
$var3=83;
$var4=84;

$vere = \db()->prepare("SELECT * FROM t_profiles AS a WHERE id_menu='".$mod."' and id_submenu='".$var1."' and id_role='".$_SESSION["perfil_usuario"]."'");
$vere->execute();
$ver = $vere->fetchAll(PDO::FETCH_ASSOC);


$agre = \db()->prepare("SELECT * FROM t_profiles AS a WHERE id_menu='".$mod."' and id_submenu='".$var2."' and id_role='".$_SESSION["perfil_usuario"]."'");
$agre->execute();
$ag = $agre->fetchAll(PDO::FETCH_ASSOC);


$edita = \db()->prepare("SELECT * FROM t_profiles AS a WHERE id_menu='".$mod."' and id_submenu='".$var3."' and id_role='".$_SESSION["perfil_usuario"]."'");
$edita->execute();
$edit = $edita->fetchAll(PDO::FETCH_ASSOC);


$borra = \db()->prepare("SELECT * FROM t_profiles AS a WHERE id_menu='".$mod."' and id_submenu='".$var4."' and id_role='".$_SESSION["perfil_usuario"]."'");
$borra->execute();
$borrar = $borra->fetchAll(PDO::FETCH_ASSOC);

?>
<link href="/css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">
<link href="/css/plugins/touchspin/jquery.bootstrap-touchspin.min.css" rel="stylesheet">
<link href="/css/plugins/iCheck/custom.css" rel="stylesheet">
<!-- Mainly scripts -->

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
<script src="/js/plugins/sweetalert/sweetalert.min.js"></script>
<script src="/js/plugins/jquery-ui/jquery-ui.min.js"></script>

<script src="/js/plugins/ladda/spin.min.js"></script>
<script src="/js/plugins/ladda/ladda.min.js"></script>
<script src="/js/plugins/ladda/ladda.jquery.min.js"></script>
<script src="/js/plugins/touchspin/jquery.bootstrap-touchspin.min.js"></script>
<script src="/js/plugins/validate/jquery.validate.min.js"></script>
<script src="/js/plugins/iCheck/icheck.min.js"></script>

<div class="modal inmodal" id="myModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content animated bounceInRight">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <i class="fa fa-laptop modal-icon"></i>
                <h4 class="modal-title" id="modaltitle">Agregar Tipo de Cajas</h4>
            </div>
            <div class="modal-body">
                <form id="myform" role="form">
                    <input type="hidden" id="hiddenAction" name="hiddenAction">
                    <input type="hidden" id="hiddenId_caja">
                    <div class="row">
                        <div class="col-md-12">
                            <label>Clave *</label>
                            <div class="form-group">
                                <input id="clave" type="text" maxlength="20" placeholder="Clave de Tipo de Caja" class="form-control" required="true" name="clave"><label id="CodeMessage" style="color:red;"></label>
                            </div>
                            <label>Descripcion *</label>
                            <div class="form-group"><input id="descripcion" type="text" placeholder="Descripción" class="form-control" required="true" name="descripcion"></div>
                                <div class="form-group">
                                    <label>
                                        <input type="checkbox" class="i-checks" id="caja_empaque">
                                        Caja de Empaque
                                    </label>
                                </div>
                            </div>
                        
                        </br>

                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-md-3">
                                    <label>Peso (kg) *</label>
                                    <input id="peso" type="text" maxlength="6" placeholder="Peso en Kg" class="form-control" name="peso" required="true">
                                </div>
                                <div class="col-md-3">
                                    <label>Alto (mm) *</label>
                                    <input id="alto" type="text" maxlength="6" placeholder="Alto en mm" class="form-control" name="alto" required="true">
                                </div>
                                <div class="col-md-3">
                                    <label>Ancho (mm) *</label>
                                    <input id="ancho" type="text" maxlength="6" placeholder="Ancho en mm" class="form-control" name="ancho" required="true">
                                </div>
                                <div class="col-md-3">
                                    <label>Fondo (mm) *</label>
                                    <input id="largo" type="text" maxlength="6" placeholder="Fondo en mm" class="form-control" name="fondo" required="true">
                                </div>
                            </div>
                        </div>
                    </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-white" data-dismiss="modal" id="btnCancel">Cerrar</button>
                <button type="submit" class="btn btn-primary ladda-button" id="btnSave">Guardar</button>
            </div>

            </form>
        </div>

    </div>
</div>
</div>

<div class="wrapper wrapper-content  animated fadeInRight">

    <h3>Tipo de Cajas</h3>

    <div class="row">
        <div class="col-md-12">
            <div class="ibox ">
                <div class="ibox-title">
                    <div class="row">
                        <div class="col-md-5">
                            <div class="input-group">
                                <input type="text" class="form-control" name="txtCriterio" id="txtCriterio" placeholder="Buscar...">
                                <div class="input-group-btn">

                                    <button onclick="ReloadGrid()" type="submit" id="buscarA" class="btn btn-primary">
                                        <span class="fa fa-search"></span> Buscar
                                    </button>

                                </div>
                            </div>

                        </div>
                        <div class="col-md-7">
                            <a href="/api/v2/tipos-de-cajas/exportar" target="_blank" class="btn btn-primary pull-right" style="margin-left:15px;"><span class="fa fa-upload"></span> Exportar</a>
                            <button class="btn btn-primary pull-right permiso_registrar" style="margin-left:15px;" data-toggle="modal" data-target="#importar"><span class="fa fa-download"></span> Importar</button>



                            <button class="btn btn-primary pull-right" type="button" style="margin-left:10px;" id="inactivos"><i class="fa fa-search"></i>&nbsp;&nbsp;Tipos de cajas inactivas</button>
                            <button onclick="agregar()" class="btn btn-primary pull-right permiso_registrar" style="margin-left:10px;" type="button">
                                <i class="fa fa-plus"></i> Nuevo
                            </button>
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

<div class="modal fade" id="modal-record" role="dialog">
    <div class="vertical-alignment-helper">
        <div class="modal-dialog vertical-align-center modal-lg" style="width: 1200px!important;">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Recuperar Tipo de Caja</h4>
                </div>
                <div class="modal-body">
                    <div class="col-md-4">
                        <div class="input-group">
                            <input type="text" class="form-control " name="txtCriterio" id="txtCriterio1" placeholder="Buscar ...">
                            <div class="input-group-btn">
                            
                                <button onclick="ReloadGrid1()" type="submit" class="btn btn-primary">
                                    <span class="fa fa-search"></span>Buscar
                                </button>
                               
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


    <div class="modal fade" id="importar" role="dialog">
        <div class="vertical-alignment-helper">
            <div class="modal-dialog vertical-align-center">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Importar Tipos de Caja</h4>
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
                        <div style="text-align: right">
                            <button id="btn-import" type="button" class="btn btn-primary">Importar</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
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
            url: '/tipos-de-cajas/importar',
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
            },
        });
    });
</script>




<script type="text/javascript">
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
            url: '/api/tipocaja/lista/index.php',
            datatype: "json",
            shrinkToFit: false,
            height: 'auto',
            postData: {
                criterio: $("#txtCriterio").val()
            },
            mtype: 'POST',
            colNames: ['ID', 'Clave', 'Descripción', "Peso (kg)", "Alto (mm)", "Ancho (mm)", "Fondo (mm)", "Empaque", "Acciones"],
            colModel: [{
                name: 'id_tipocaja',
                index: 'id_tipocaja',
                width: 60,
                sorttype: "int",
                editable: false,
                hidden: true
            }, {
                name: 'clave',
                index: 'clave',
                width: 100,
                editable: false,
                sortable: false
            }, {
                name: 'descripcion',
                index: 'descripcion',
                width: 300,
                editable: false,
                sortable: false
            }, {
                name: 'peso',
                index: 'peso',
                align:'right',
                width: 100,
                editable: false,
                sortable: false
            }, {
                name: 'alto',
                index: 'alto',
                align:'right',
                width: 100,
                editable: false,
                sortable: false
            }, {
                name: 'ancho',
                index: 'ancho',
                align:'right',
                width: 100,
                editable: false,
                sortable: false
            }, {
                name: 'largo',
                index: 'largo',
                align:'right',
                width: 100,
                editable: false,
                sortable: false
            }, {
                name: 'empaque',
                index: 'empaque',
                align:'center',
                width: 100,
                editable: false,
                sortable: false
            }, {
                name: 'myac',
                index: '',
                align:'center',
                width: 100,
                fixed: true,
                sortable: false,
                resize: false,
                formatter: imageFormat
            }, ],
            rowNum: 30,
            rowList: [30, 40, 50],
            pager: pager_selector,
            sortname: 'clave',
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
            height: 200,
            reloadAfterSubmit: true
        });


        $(window).triggerHandler('resize.jqGrid'); //trigger window resize to make the grid get the correct size
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        //////////////////////////////////////////////////////////aqui se construye la funcion de cada item del grid//////////////////////////////////////////////////////////
        function imageFormat(cellvalue, options, rowObject) {
            var serie = rowObject[0];
            var correl = rowObject[4];
            var url = "x/?serie=" + serie + "&correl=" + correl;
            var url2 = "v/?serie=" + serie + "&correl=" + correl;

            $("#hiddencvetipcaja").val(serie);

            var html = '';
            //html += '<a href="#" onclick="ver(\''+serie+'\')"><i class="fa fa-eye" alt="Ver"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            if($("#permiso_editar").val() == 1)
            html += '<a href="#" onclick="editar(\'' + serie + '\')"><i class="fa fa-edit" alt="Editar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            if($("#permiso_eliminar").val() == 1)
            html += '<a href="#" onclick="borrar(\'' + serie + '\')"><i class="fa fa-eraser" alt="Borrar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';

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

    //////////////////////////////////////////////////////////hace un reload del grid llamando al php en la carpera "c" para hacer la consulta//////////////////////////////////////////////////////////
    function ReloadGrid() {
        $('#grid-table').jqGrid('clearGridData')
            .jqGrid('setGridParam', {
                postData: {
                    criterio: $("#txtCriterio").val(),
                },
                datatype: 'json',
                page: 1
            })
            .trigger('reloadGrid', [{
                current: true
            }]);
    }

    function ReloadGrid1() {
        $('#grid-table2').jqGrid('clearGridData')
            .jqGrid('setGridParam', {
                postData: {
                    criterio: $("#txtCriterio").val(),
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
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                clave: _codigo,
                action: "inUse"
            },
            beforeSend: function(x) {
                if (x && x.overrideMimeType) {
                    x.overrideMimeType("application/json;charset=UTF-8");
                }
            },
            url: '/api/tipocaja/update/index.php',
            success: function(data) {
                if (data.success == true) {
                    swal(
                        '¡Alerta!',
                        'El tipo de caja esta siendo usado en este momento',
                        'warning'
                    );
                } else {
                    swal({
                            title: "¿Está seguro que desea borrar el tipo de caja?",
                            text: "Está a punto de borrar un tipo de caja y esta acción no se puede deshacer",
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
                                    id_tipocaja: _codigo,
                                    action: "delete"
                                },
                                beforeSend: function(x) {
                                    if (x && x.overrideMimeType) {
                                        x.overrideMimeType("application/json;charset=UTF-8");
                                    }
                                },
                                url: '/api/tipocaja/update/index.php',
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
            }
        });
    }

    function editar(_codigo) {

        $("#clave").prop('disabled', true);
        $("#hiddenAction").val("edit");
        $("#modaltitle").html('Editar Tipo de Cajas');
        $("#myform").validate({
            rules: {
                clave: {
                    required: true,
                    maxlength: 20
                },
                descripcion: {
                    required: true
                },
                peso: {
                    required: true,
                    maxlength: 6,
                    number: true
                },
                alto: {
                    required: true,
                    maxlength: 6,
                    number: true
                },
                ancho: {
                    required: true,
                    maxlength: 6,
                    number: true
                },
                largo: {
                    required: true,
                    maxlength: 6,
                    number: true
                }
            }
        });


        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                id_tipocaja: _codigo,
                action: "load"
            },
            beforeSend: function(x) {
                if (x && x.overrideMimeType) {
                    x.overrideMimeType("application/json;charset=UTF-8");
                }
            },
            url: '/api/tipocaja/update/index.php',
            success: function(data) {
                if (data.success == true) {
                    $("#clave").val(data.clave);
                    $("#descripcion").val(data.descripcion);
                    $("#largo").val(data.largo);
                    $("#alto").val(data.alto);
                    $("#ancho").val(data.ancho);
                    $("#peso").val(data.peso);
                    l.ladda('stop');
                    $("#btnCancel").show();
                    $modal0 = $("#myModal");
                    $modal0.modal('show');
                    $("#hiddenAction").val("edit");
                    $("#hiddenId_caja").val(data.id_tipocaja);
                    $("#caja_empaque").iCheck(data.Packing === 'S' ? 'check' : 'uncheck');
                }
            }, error: function(data) {
                console.log("ERROR ", data);
            }


        });
    }

    function agregar() {
        $("#hiddenAction").val("add");
        $modal0 = $("#myModal");
        $modal0.modal('show');
        l.ladda('stop');
        $("#hiddenAction").val("add");
        $("#btnCancel").show();
        $("#clave").val("");
        $("#descripcion").val("");
        $("#largo").val("");
        $("#alto").val("");
        $("#ancho").val("");
        $("#peso").val("");
        $("#clave").prop('disabled', false);
        $("#modaltitle").html('Agregar Tipo de Cajas');
        $("#caja_empaque").iCheck('uncheck');
    }

    var l = $('.ladda-button').ladda();
    l.click(function() {

  
        if ($("#clave").val() == "") {
            return;
        }
        if ($("#descripcion").val() == "") {
            return;
        }
        if ($("#largo").val() == "") {
            return;
        }
        if ($("#alto").val() == "") {
            return;
        }
        if ($("#peso").val() == "") {
            return;
        }
        if ($("#ancho").val() == "") {
            return;
        }

        var empaque = $("#caja_empaque").iCheck("update")[0].checked ? 'S' : 'N';

        $("#btnCancel").hide();

        l.ladda('start');

        $.post('/api/tipocaja/update/index.php', {
                    id_tipocaja: $("#hiddenId_caja").val(),
                    clave: $("#clave").val(),
                    descripcion: $("#descripcion").val(),
                    largo: $("#largo").val(),
                    alto: $("#alto").val(),
                    ancho: $("#ancho").val(),
                    peso: $("#peso").val(),
                    action: $("#hiddenAction").val(),
                    empaque: empaque
                },
                function(response) {
                    console.log(response);
                }, "json")
            .always(function() {
                $("#codigo").val("");
                $("#descripcion").val("");
                l.ladda('stop');
                $("#btnCancel").show();
                $modal0.modal('hide');
                ReloadGrid();
                ReloadGrid1();
            });
    });

    $(".touchspin").TouchSpin({
        min: 0,
        max: 100,
        step: 0.01,
        decimals: 2,
        boostat: 5,
        maxboostedstep: 10,
        //postfix: '%',
        buttondown_class: 'btn btn-white',
        buttonup_class: 'btn btn-white'
    });
</script>



<script>
    $("#clave").keyup(function(e) {

        var claveCode = $(this).val();
        var claveCodeRegexp = new RegExp("^[a-zA-Z0-9]{1,20}$");

        if (claveCodeRegexp.test(claveCode)) {
            $("#CodeMessage").html("");
            $("#btnSave").prop('disabled', false);

            var clave = $(this).val();

            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    clave: clave,
                    action: "exists"
                },
                beforeSend: function(x) {
                    if (x && x.overrideMimeType) {
                        x.overrideMimeType("application/json;charset=UTF-8");
                    }
                },
                url: '/api/tipocaja/update/index.php',
                success: function(data) {
                    if (data.success == false) {
                        $("#CodeMessage").html("");
                        $("#btnSave").prop('disabled', false);
                    } else {
                        $("#CodeMessage").html(" Clave de tipo de caja ya existe");
                        $("#btnSave").prop('disabled', true);
                    }
                }

            });

        } else {
            $("#CodeMessage").html("Por favor, ingresar una Clave válida");
            $("#btnSave").prop('disabled', true);
        }
    });

    $('#alto').keypress(function(e) {
        var a = [];
        var k = e.which;

        for (i = 48; i < 58; i++)
            a.push(i);

        if (!(a.indexOf(k) >= 0))
            e.preventDefault();
    });

    $('#ancho').keypress(function(e) {
        var a = [];
        var k = e.which;

        for (i = 48; i < 58; i++)
            a.push(i);

        if (!(a.indexOf(k) >= 0))
            e.preventDefault();
    });

    $('#largo').keypress(function(e) {
        var a = [];
        var k = e.which;

        for (i = 48; i < 58; i++)
            a.push(i);

        if (!(a.indexOf(k) >= 0))
            e.preventDefault();
    });

    $("#txtCriterio").keyup(function(event) {
        if (event.keyCode == 13) {
            $("#buscarA").click();
        }
    });


    $("#inactivos").on("click", function() {
        $modal0 = $("#modal-record");
        $modal0.modal('show');
    });


    $(function($) {
        var grid_selector = "#grid-table2";
        var pager_selector = "#grid-pager2";

        //resize to fit page size
        $(window).on("resize", function() {
            var $grid = $("#grid-table2"),
                newWidth = $grid.closest(".ui-jqgrid").parent().width();
            $grid.jqGrid("setGridWidth", newWidth, true);
        });
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
            url: '/api/tipocaja/lista/index_i.php',
            datatype: "json",
            height: 250,
            postData: {
                criterio: $("#txtCriterio").val()
            },
            mtype: 'POST',
            colNames: ['ID', 'Clave', 'Descripción', "Alto (mm)", "Ancho (mm)", "Fondo (mm)", "Acciones"],
            colModel: [{
                name: 'id_tipocaja',
                index: 'id_tipocaja',
                width: 60,
                sorttype: "int",
                editable: false,
                hidden: true
            }, {
                name: 'clave',
                index: 'clave',
                width: 150,
                editable: false,
                sortable: false
            }, {
                name: 'descripcion',
                index: 'descripcion',
                width: 550,
                editable: false,
                sortable: false
            }, {
                name: 'alto',
                index: 'alto',
                width: 100,
                editable: false,
                sortable: false
            }, {
                name: 'ancho',
                index: 'ancho',
                width: 100,
                editable: false,
                sortable: false
            }, {
                name: 'largo',
                index: 'largo',
                width: 100,
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
            }, ],
            rowNum: 30,
            rowList: [30, 40, 50],
            pager: pager_selector,
            sortname: 'cve_gpoart',
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



            // la funcion downloadxml ejecuta el index.php que esta en la carpeta x pasandole la serie y el correlativo via get
            // la funcion viewPdf ejecuta el index.php que esta en la carpeta v pasandole la serie y el correlativo via get

            var html = '';
            //html += '<a href="#" onclick="ver(\''+serie+'\')"><i class="fa fa-eye" alt="Ver"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            html += '<a href="#" onclick="recovery(\'' + serie + '\')"><i class="fa fa-check" alt="Borrar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';

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


    function recovery(_codigo) {
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                id_tipocaja: _codigo,
                action: "recovery"
            },
            beforeSend: function(x) {
                if (x && x.overrideMimeType) {
                    x.overrideMimeType("application/json;charset=UTF-8");
                }
            },
            url: '/api/tipocaja/update/index.php',
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

    $(document).ready(function() {
        $("div.ui-jqgrid-bdiv").css("max-height", $("#list").height() - 80);
        $('.i-checks').iCheck({
            checkboxClass: 'icheckbox_square-green',
            radioClass: 'iradio_square-green'
        });
    });
</script>


<style>
    <?php /* if($edit[0]['Activo']==0) {
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
    */
    ?>
</style>