<?php
$vere = \db()->prepare("select * from t_profiles as a where id_menu=22 and id_submenu=49 and id_role='".$_SESSION["perfil_usuario"]."'");
$vere->execute();
$ver = $vere->fetchAll(PDO::FETCH_ASSOC);


$agre = \db()->prepare("select * from t_profiles as a where id_menu=22 and id_submenu=50 and id_role='".$_SESSION["perfil_usuario"]."'");
$agre->execute();
$ag = $agre->fetchAll(PDO::FETCH_ASSOC);


$edita = \db()->prepare("select * from t_profiles as a where id_menu=22 and id_submenu=51 and id_role='".$_SESSION["perfil_usuario"]."'");
$edita->execute();
$edit = $edita->fetchAll(PDO::FETCH_ASSOC);


$borra = \db()->prepare("select * from t_profiles as a where id_menu=22 and id_submenu=52 and id_role='".$_SESSION["perfil_usuario"]."'");
$borra->execute();
$borrar = $borra->fetchAll(PDO::FETCH_ASSOC);

?>
    <link href="/css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">
    <link href="/css/plugins/iCheck/custom.css" rel="stylesheet">
    <link href="/css/plugins/ladda/select2.css" rel="stylesheet" />
    <link href="/css/plugins/awesome-bootstrap-checkbox/awesome-bootstrap-checkbox.css" rel="stylesheet">
    <link href="/css/bootstrap-imageupload.min.css" rel="stylesheet">


    <!-- Input Mask-->


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
    <script src="/js/plugins/sweetalert/sweetalert.min.js"></script>
    <script src="/js/plugins/ladda/spin.min.js"></script>
    <script src="/js/plugins/ladda/ladda.min.js"></script>
    <script src="/js/plugins/ladda/ladda.jquery.min.js"></script>
    <!-- iCheck -->
    <script src="/js/plugins/iCheck/icheck.min.js"></script>
    <!-- TouchSpin -->
    <script src="/js/plugins/touchspin/jquery.bootstrap-touchspin.min.js"></script>
    <!-- Select -->
    <script src="/js/plugins/jasny/jasny-bootstrap.min.js"></script>
    <script src="/js/select2.js"></script>
    <script src="/js/bootstrap-imageupload.js"></script>

    <style>
        #FORM {
            width: 100%;
            /*height: 100%;*/
            position: absolute;
            left: 0px;
            z-index: 999;
        }
    </style>

    <style type="text/css">

    </style>

    <div class="wrapper wrapper-content  animated fadeInRight" id="list">

        <h3>Tipo de Transportes</h3>


        <div class="row">
            <div class="col-md-12">
                <div class="ibox ">
                    <div class="ibox-title">

                        <div class="row">
                            <div class="col-md-4">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="txtCriterio" id="txtCriterio" placeholder="Buscar...">
                                    <div class="input-group-btn">
                                        <a href="#" onclick="ReloadGrid()">
                                            <button type="submit" id="buscarA" class="btn btn-primary">
                                        <span class="fa fa-search"></span>  Buscar
                                        </button>
                                        </a>
                                    </div>
                                </div>

                            </div>
                            <div class="col-md-8">


                                <a href="#" class="permiso_registrar" onclick="agregar()"><button class="btn btn-primary" type="button"><i class="fa fa-plus"></i>&nbsp;&nbsp;Nuevo</button></a>

                                <a href="/api/v2/tipos-de-transporte/exportar" target="_blank" class="btn btn-primary pull-right" style="margin-left:15px"><span class="fa fa-upload"></span> Exportar</a>
                                <button class="btn btn-primary pull-right permiso_registrar" style="margin-left:15px" data-toggle="modal" data-target="#importar"><span class="fa fa-download"></span> Importar</button>


                                <button class="btn btn-primary pull-right" type="button" id="inactivos"><i class="fa fa-search"></i>&nbsp;&nbsp;Tipo de Transporte inactivos</button>

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


 
    <!-- Modal Recuperar -->
    <div class="modal fade" id="coModal" role="dialog">
        <div class="vertical-alignment-helper">
            <div class="modal-dialog vertical-align-center modal-lg" style="width: 1200px!important;">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Recuperar Tipo de Transporte</h4>
                    </div>
                    <div class="modal-body">
                        <div class="col-md-4">
                            <div class="input-group">
                                <input type="text" class="form-control input-sm" name="txtCriterio" id="txtCriterio1" placeholder="Buscar...">
                                <div class="input-group-btn">
                                    <a href="#" onclick="ReloadGrid1()">
                                        <button type="submit" id="buscarA" class="btn btn-sm btn-primary">
                                    <span class="fa fa-search"></span>  Buscar
                                    </button>
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
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-title">
                        <div class="row">
                            <div class="col-lg-4" id="_title">
                                <h3>Agregar Tipo de Transporte</h3>
                            </div>
                        </div>
                    </div>
                    <form id="myform">
                        <input type="hidden" id="hiddenAction" name="hiddenAction">
                        <div class="ibox-content">
                            <div class="row">

                                <div class="col-lg-6 b-r">

                                    <div class="form-group">
                                        <label>Clave de Tipo de Transporte *</label>
                                        <input id="clave_ttransporte" type="text" placeholder="Clave de Transporte" class="form-control" required="true">
                                        <!--<label id="CodeMessage" style="color:red;">-->
                                    </div>
                                    <div class="form-group">
                                        <label>Descripción *</label>
                                        <input id="desc_ttransporte" type="text" placeholder="Nombre de Transporte" class="form-control" required="true">
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4 form-group">
                                            <label>Alto (mm) *</label>
                                            <input id="alto" type="text" oninput="this.value = this.value.replace(/[^0-9]/g, ''); this.value = this.value.replace(/(\..*)\./g, '$1');" placeholder="Alto" class="form-control" required="true">
                                        </div>
                                        <div class="col-md-4 form-group">
                                            <label>Ancho (mm) *</label>
                                            <input id="ancho" type="text" oninput="this.value = this.value.replace(/[^0-9]/g, ''); this.value = this.value.replace(/(\..*)\./g, '$1');" placeholder="Ancho" class="form-control" required="true">
                                        </div>
                                        <div class="col-md-4 form-group">
                                            <label>Fondo (mm) *</label>
                                            <input id="fondo" type="text" oninput="this.value = this.value.replace(/[^0-9]/g, ''); this.value = this.value.replace(/(\..*)\./g, '$1');" placeholder="Fondo" class="form-control" required="true">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>Capacidad Maxima (kg) *</label>
                                        <input id="capacidad_carga" type="text" oninput="this.value = this.value.replace(/[^0-9]/g, ''); this.value = this.value.replace(/(\..*)\./g, '$1');" placeholder="Capacidad" class="form-control" required="true">
                                    </div>

                                    <div class="form-group">
                                        <label>Capacidad volumetrica (m3) *</label>
                                        <input id="capacidad_volumetrica" disabled type="text" oninput="this.value = this.value.replace(/[^0-9]/g, ''); this.value = this.value.replace(/(\..*)\./g, '$1');" placeholder="Valor Calculado Automaticamente" class="form-control" required="true">
                                    </div>

                                </div>
                                <div class="col-md-6">



                                    <div class="form-group">
                                        <div id="upload">
                                            <label>Imagen Actual</label>
                                            <img src="" alt="Image preview" ima="" class="thumbnail" id="image">
                                        </div>

                                        <div class="imageupload panel panel-default" id="upload">
                                            <div class="panel-heading clearfix">
                                                <h3 class="panel-title pull-left">Subir Imagen</h3>
                                            </div>
                                            <div class="file-tab panel-body">
                                                <label class="btn btn-primary btn-file fileContainer ">        <!-- The file is stored here. -->
                                                <b>Examinar</b>
                                                <input id="imagen" type="file" name="image-file">

                                            </label>

                                                <button type="button" class="btn btn-default">Remover</button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="pull-right"><br>
                                        <a href="#" onclick="cancelar()"><button type="button" class="btn btn-white" id="btnCancel">Cerrar</button></a>&nbsp;&nbsp;
                                        <button type="submit" class="btn btn-primary" id="btnSave">Guardar</button>
                                    </div>

                                    <input type="hidden" id="hiddenTransporte">
                                </div>
                            </div>

                        </div>
                    </form>
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
                        <h4 class="modal-title">Importar Tipos de Transporte</h4>
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
                            //console.log("almacen Init = ", $("#almacen").val());
                            ReloadGrid();
                            //filtralo();
                        }, 1000);

                    }
                },
                error: function(res) {
                    window.console.log(res);
                }
            });
        }
        almacenPrede();

    $('#btn-import').on('click', function() {

        $('#btn-import').prop('disable', true);
        var bar = $('.progress-bar');
        var percent = $('.percent');
        //var status = $('#status');

        var formData = new FormData();
        formData.append("clave", "valor");

        $.ajax({
            // Your server script to process the upload
            url: '/tipos-de-transporte/importar',
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
        var $imageupload = $('.imageupload');
        $imageupload.imageupload({
            maxFileSizeKb: 512,
            maxWidth: 150,
            maxHeight: 150,
        });

        $('#imageupload-disable').on('click', function() {
            $imageupload.imageupload('disable');
            $(this).blur();
        })

        $('#imageupload-enable').on('click', function() {
            $imageupload.imageupload('enable');
            $(this).blur();
        })

        $('#imageupload-reset').on('click', function() {
            $imageupload.imageupload('reset');
            $(this).blur();
        });


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

        function uploadFile() {
            var input = document.getElementById("imagen");
            file = input.files[0];

            if (file != undefined) {
                formData = new FormData();
                if (!!file.type.match(/image.*/)) {
                    formData.append("image", file);
                    $.ajax({
                        url: "/app/template/page/tipotransporte/upload.php",
                        type: "POST",
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(data) {
                            //alert(data);
                        },
                        error: function(xhr, ajaxOptions, thrownError) {
                            //console.log(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                            alert(thrownError);
                        }
                    });
                } else {
                    alert('Not a valid image!');
                }
            } else {
                alert('Input something!');
            }
        }

        //////////////////////////////////////////////////////////Aqui se contruye el Grid//////////////////////////////////////////////////////////
        $(function($) {
            var grid_selector = "#grid-table";
            var pager_selector = "#grid-pager";

            //console.log("almacen Init = ", $("#almacen").val());

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
                url: '/api/tipotransporte/lista/index.php',
                datatype: "json",
                shrinkToFit: false,
                height: 'auto',
                autowidth: true,
                postData: {
                    criterio: $("#txtCriterio").val()
                },
                mtype: 'POST',
                colNames: ['ID', 'Clave', 'Descripción', 'Alto (mm)', 'Ancho (mm)', 'Fondo (mm)', 'Capacidad en Kgs', 'Capacidad Volumetrica', 'Almacén', "Acciones"],
                /*'Calle y Numero','Colonia','Ciudad','Estado','Pais','Codigo Postal','RFC','Telefono1','Telefono2'*/
                colModel: [
                    //{name:'id',index:'id', width:60, sorttype:"int", editable: false},
                    {
                        name: 'id',
                        index: 'id',
                        width: 40,
                        editable: false,
                        sortable: false,
                        hidden: true
                    }, {
                        name: 'clave_ttransporte',
                        index: 'clave_ttransporte',
                        width: 200,
                        editable: false,
                        sortable: false
                    }, {
                        name: 'desc_ttransporte',
                        index: 'desc_ttransporte',
                        width: 250,
                        editable: false,
                        sortable: false,
                        hidden: false
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
                        name: 'fondo',
                        index: 'fondo',
                        width: 100,
                        editable: false,
                        sortable: false
                    }, {
                        name: 'capacidad_carga',
                        index: 'capacidad_carga',
                        width: 150,
                        editable: false,
                        sortable: false
                    }, {
                        name: 'capacidad_volumetrica',
                        index: 'capacidad_volumetrica',
                        width: 150,
                        editable: false,
                        sortable: false
                    },{
                        name: 'almacen',
                        index: 'almacen',
                        width: 200,
                        editable: false,
                        hidden: true,
                        sortable: false
                    }, {
                        name: 'myac',
                        index: '',
                        width: 100,
                        fixed: true,
                        sortable: false,
                        resize: false,
                        formatter: imageFormat
                    },
                ],
                rowNum: 30,
                rowList: [30, 40, 50],
                pager: pager_selector,
                sortname: 'id',
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
                //return '<img src="'+cellvalue+'" />&nbsp;&nbsp;&nbsp;&nbsp;<img src="'+cellvalue+'" />';
                var serie = rowObject[1];
                var correl = rowObject[4];
                var url = "x/?serie=" + serie + "&correl=" + correl;
                var url2 = "v/?serie=" + serie + "&correl=" + correl;
                $("#hiddenTransporte").val(serie);

                // la funcion downloadxml ejecuta el index.php que esta en la carpeta x pasandole la serie y el correlativo via get
                // la funcion viewPdf ejecuta el index.php que esta en la carpeta v pasandole la serie y el correlativo via get

                var html = '';
                //html += '<a href="#" onclick="ver(\''+serie+'\')"><i class="fa fa-eye" alt="Ver"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
                if($("#permiso_editar").val() == 1)
                {
                html += '<a href="#" onclick="editar(\'' + serie + '\')"><i class="fa fa-edit" alt="Editar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
                }
                if($("#permiso_eliminar").val() == 1)
                {
                html += '<a href="#" onclick="borrar(\'' + serie + '\')"><i class="fa fa-eraser" alt="Borrar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
                }

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
            //console.log("almacen Init = ", $("#almacen").val());
            $('#grid-table').jqGrid('clearGridData')
                .jqGrid('setGridParam', {
                    postData: {
                        criterio: $("#txtCriterio").val()
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
                        criterio: $("#txtCriterio1").val(),
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
                    clave_ttransporte: _codigo,
                    action: "inUse"
                },
                beforeSend: function(x) {
                    if (x && x.overrideMimeType) {
                        x.overrideMimeType("application/json;charset=UTF-8");
                    }
                },
                url: '/api/tipotransporte/update/index.php',
                success: function(data) {
                    if (data.success == true) {
                        swal(
                            '¡Alerta!',
                            'El tipo de transporte esta siendo usado en este momento',
                            'warning'
                        );
                        //$('#codigo').prop('disabled', true);
                    } else {

                        swal({
                                title: "¿Está seguro que desea borrar el tipo de transporte?",
                                text: "Está a punto de borrar un tipo de transporte y esta acción no se puede deshacer",
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
                                        clave_ttransporte: _codigo,
                                        action: "delete"
                                    },
                                    beforeSend: function(x) {
                                        if (x && x.overrideMimeType) {
                                            x.overrideMimeType("application/json;charset=UTF-8");
                                        }
                                    },
                                    url: '/api/tipotransporte/update/index.php',
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
            $('.imageupload').imageupload('reset');
            $("#CodeMessage").val("");
            $("#upload").show();
            $("#hiddenTransporte").val(_codigo);
            $("#clave_ttransporte").prop("disabled", true);
            $("#_title").html('<h3>Editar Tipo Transporte</h3>');
            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    clave_ttransporte: _codigo,
                    action: "load"
                },
                beforeSend: function(x) {
                    if (x && x.overrideMimeType) {
                        x.overrideMimeType("application/json;charset=UTF-8");
                    }
                },
                url: '/api/tipotransporte/update/index.php',
                success: function(data) {
                    if (data.success == true) {
                        $("#hiddenTransporte").val(data.clave_ttransporte);
                        $("#clave_ttransporte").val(data.clave_ttransporte);
                        $("#alto").val(data.alto);
                        $("#ancho").val(data.ancho);
                        $("#desc_ttransporte").val(data.desc_ttransporte);
                        $("#capacidad_carga").val(data.capacidad_carga);
                        $("#fondo").val(data.fondo);
                        $("#capacidad_volumetrica").val(data.capacidad_volumetrica);

                        $("#image").prop("src", "../img/foto_tipo_transporte/" + data.imagen);
                        $("#image").prop("name", data.imagen);
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
            $('#list').removeAttr('class').attr('class', '');
            $('#list').addClass('animated');
            $('#list').addClass("fadeInRight");
            $('#list').addClass("wrapper");
            $('#list').addClass("wrapper-content");
        }

        function agregar() {


            $('.imageupload').imageupload('reset');
            $("#upload").hide();

            $("#_title").html('<h3>Agregar Tipo de Transporte</h3>');
            $(':input', '#myform')
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
            $("#hiddenTransporte").val("0");

            $("#CodeMessage").val("");
            $("#clave_ttransporte").prop("disabled", false);
            $("#hiddenTransporte").val("");
            $("#clave_ttransporte").val("");
            $("#alto").val("");
            $("#ancho").val("");
            $("#desc_ttransporte").val("");
            $("#capacidad_carga").val("");
            $("#fondo").val("");
            $("#imagen").val("");
            $("#image").prop("src", "");
            $("#hiddenAction").val("add");
            $("#hiddenTransporte").val("0");
            $('#txtFechaAlta').attr("disabled", true);
        }

        var l = $('#myform').ladda();
        l.submit(function() {

            if ($('#imagen').val()) {
                var path = $('#imagen').val();
                var filename = path.replace(/^.*\\/, "");
                uploadFile();
            } else if ($('#image').attr('name') != undefined && !$('#imagen').val()) {
                var path = $('#image').attr("name");
                var filename = path.replace(/^.*\\/, "");
                console.log(filename);

            } else {
                filename = "noimage.jpg"
            }

            $("#btnCancel").hide();

            l.ladda('start');

            $.post('/api/tipotransporte/update/index.php', {
                        clave_ttransporte: $("#clave_ttransporte").val(),
                        alto: $("#alto").val(),
                        fondo: $("#fondo").val(),
                        ancho: $("#ancho").val(),
                        capacidad_carga: $("#capacidad_carga").val(),
                        desc_ttransporte: $("#desc_ttransporte").val(),
                        imagen: filename,
                        action: $("#hiddenAction").val()
                    },
                    function(response) {
                        console.log(response);
                    }, "json")
                .always(function() {
                    l.ladda('stop');
                    $("#btnCancel").show();
                    cancelar()
                    ReloadGrid();
                    ReloadGrid1();
                });
        });
    </script>

    <script>
        $(document).ready(function() {
            $("#compania_edit").select2({

            });

            $("#inactivos").on("click", function() {
                $modal0 = $("#coModal");
                $modal0.modal('show');
                ReloadGrid1();
            });
            $("div.ui-jqgrid-bdiv").css("max-height", $("#list").height() - 80);
        });
    </script>

    <script>
        $(document).ready(function() {
            $("#txtNomCompa").select2();

            $("#minimum2").select2({
                minimumInputLength: 2
            });

        });

        $("#alto").keyup(capacidadVolumetrica);
        $("#fondo").keyup(capacidadVolumetrica);
        $("#ancho").keyup(capacidadVolumetrica);

        function capacidadVolumetrica() {

            var alto = parseFloat($("#alto").val() || 0);
            var ancho = parseFloat($("#ancho").val() || 0);
            var profundo = parseFloat($("#fondo").val() || 0);

            var volumen = parseFloat((alto / 1000) * (ancho / 1000) * (profundo / 1000));
            volumen = volumen.toFixed(4);

            $("#capacidad_volumetrica").val(volumen);
        }

        $("#clave_ttransporte").keyup(function(e) {

            var claveCode = $(this).val();
            var claveCodeRegexp = new RegExp("^[a-zA-Z0-9]{1,20}$");

            if (claveCodeRegexp.test(claveCode)) {
                $("#CodeMessage").html("");
                $("#btnSave").prop('disabled', false);

                var clave_ttransporte = $(this).val();

                $.ajax({
                    type: "POST",
                    dataType: "json",
                    data: {
                        clave_ttransporte: clave_ttransporte,
                        action: "exists"
                    },
                    beforeSend: function(x) {
                        if (x && x.overrideMimeType) {
                            x.overrideMimeType("application/json;charset=UTF-8");
                        }
                    },
                    url: '/api/tipotransporte/update/index.php',
                    success: function(data) {
                        if (data.success == false) {
                            $("#CodeMessage").html("");
                            $("#btnSave").prop('disabled', false);
                        } else {
                            $("#CodeMessage").html(" Clave de Tipo de Transporte ya existe");
                            $("#btnSave").prop('disabled', true);
                        }
                    }

                });
            } else {
                $("#CodeMessage").html("Por favor, ingresar una Clave de Tipo de Transporte válida");
                $("#btnSave").prop('disabled', true);
            }

        });
        $("#txtCriterio").keyup(function(event) {
            if (event.keyCode == 13) {
                $("#buscarA").click();
            }
        });
    </script>

    <script>
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
                url: '/api/tipotransporte/lista/index_i.php',
                datatype: "json",
                height: 250,
                postData: {
                    criterio: $("#txtCriterio1").val()
                },
                mtype: 'POST',
                colNames: ['id', 'Clave', 'Descripción', 'Alto (mm)', 'Ancho (mm)', 'Fondo (mm)', 'Cap. Kgs', 'Cap. Vol', "Recuperar"],
                colModel: [{
                    name: 'id',
                    index: 'id',
                    width: 30,
                    editable: false,
                    sortable: false,
                    hidden: true
                }, {
                    name: 'clave_ttransporte',
                    index: 'clave_ttransporte',
                    width: 130,
                    editable: false,
                    sortable: false
                }, {
                    name: 'desc_ttransporte',
                    index: 'desc_ttransporte',
                    width: 500,
                    editable: false,
                    sortable: false
                }, {
                    name: 'alto',
                    index: 'alto',
                    width: 80,
                    editable: false,
                    sortable: false
                }, {
                    name: 'ancho',
                    index: 'ancho',
                    width: 80,
                    editable: false,
                    sortable: false
                }, {
                    name: 'fondo',
                    index: 'fondo',
                    width: 80,
                    editable: false,
                    sortable: false
                }, {
                    name: 'capacidad_carga',
                    index: 'capacidad_carga',
                    width: 80,
                    editable: false,
                    sortable: false
                }, {
                    name: 'capacidad_volumetrica',
                    index: 'capacidad_volumetrica',
                    width: 80,
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
                rowNum: 10,
                rowList: [10, 20, 30],
                pager: pager_selector,
                sortname: 'clave_ttransporte',
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

                var id = rowObject[0];

                var html = '';
                //html += '<a href="#" onclick="ver(\''+serie+'\')"><i class="fa fa-eye" alt="Ver"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
                html += '<a href="#" onclick="recovery(\'' + id + '\')"><i class="fa fa-check" alt="Borrar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';

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
    </script>

    <script>
        function recovery(_codigo) {
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
                url: '/api/tipotransporte/update/index.php',
                success: function(data) {
                    if (data.success == true) {
                        //$('#codigo').prop('disabled', true);
                        ReloadGrid();
                        ReloadGrid1();
                    }
                }
            });
        }
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