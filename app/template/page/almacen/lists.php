<?php
include_once  $_SERVER['DOCUMENT_ROOT']."/app/host.php";
$listaProvee = new \Proveedores\Proveedores();
$ga = new \Almacen\Almacen();
$listaAlmacen = new \AlmacenP\AlmacenP();
$almacenes = new \AlmacenP\AlmacenP();
$listaTAlmacen = new \TipoAlmacen\TipoAlmacen();


$mod=33;
$var1=85;
$var2=86;
$var3=87;
$var4=88;

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
    <link href="/css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">
    <link href="/css/plugins/ladda/select2.css" rel="stylesheet" />

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
    <!-- Select -->
    <script src="/js/select2.js"></script>

    <!-- Drag & Drop Panel -->
    <script src="/js/dragdrop.js"></script>


    <!-- Mainly scripts -->

    <style type="text/css">

    </style>

    <div class="modal inmodal" id="myModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content animated bounceInRight">
                <div class="modal-header" id="modaltitle">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                    <i class="fa fa-laptop modal-icon"></i>
                    <h4 class="modal-title">Agregar Zona de Almacenaje</h4>
                </div>
                <form id="myform">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Almacén *</label>
                            <select name="country" id="almacenP" style="width:100%;" class="form-control" required="true">
                            <option value="">Nombre del Almacén</option>
                            <?php foreach( $listaAlmacen->getAll("traslado") AS $p ): ?>
                            <option value="<?php echo $p->id; ?>"><?php echo $p->nombre; ?></option>
                            <?php endforeach; ?>
                        </select>
                        </div>

                        <div class="form-group" style="display: none;">
                            <label>Tipo Almacén *</label>
                            <select name="tipoAlmacen" id="tipoAlmacen" style="width:100%;" class="form-control" required="true">
                            <option value="">Seleccione Tipo de Almacén</option>
                            <?php foreach( $listaTAlmacen->getAll() AS $k ): ?>
                            <option value="<?php echo $k["id"]; ?>"><?php echo "[".$k["clave_talmacen"]."] - ".$k["desc_tipo_almacen"]; ?></option>
                            <?php endforeach; ?>
                        </select>
                        </div>

                        <label>Clave *</label>
                        <div class="form-group"><input id="CAlmacen" type="text" maxlength="20" placeholder="Clave de la Zona de Almacenaje" class="form-control" required="true">
                            <!--<label id="CodeMessage" style="color:red;"></label>--></div>
                        <label>Descripción *</label>
                        <input id="NAlmacen" type="text" placeholder="Descripción de la Zona de Almacenaje" class="form-control" required="true"><br>


                        <input type="hidden" id="hiddenAction">
                        <input type="hidden" id="hiddenIDAlmacen">

                    <div class="form-group">
                        <label for="control_abc">
                        <input type="checkbox" name="control_abc" id="control_abc" value="1"> Control ABC</label>
                    </div>
                    <div class="form-group tipo_ABC" style="display: none;margin-left: 20px;margin-bottom: 0;">
                        <label for="control_tipo_A">
                        <input type="checkbox" name="control_tipo_A" id="control_tipo_A" value="1"> A</label>
                    </div>
                    <div class="form-group tipo_ABC" style="display: none;margin-left: 20px;margin-bottom: 0;">
                        <label for="control_tipo_B">
                        <input type="checkbox" name="control_tipo_B" id="control_tipo_B" value="1"> B</label>
                    </div>
                    <div class="form-group tipo_ABC" style="display: none;margin-left: 20px;margin-bottom: 0;">
                        <label for="control_tipo_C">
                        <input type="checkbox" name="control_tipo_C" id="control_tipo_C" value="1"> C</label>
                    </div>


                                <div class="form-group">
                                    <label>Empresa | Proveedor</label>
                                    <select class="form-control" id="empresa_proveedor">
                                        <option value="">Seleccione</option>
                                        <?php foreach( $listaProvee->getAll(" AND es_cliente = 1 ") AS $a ): ?>
                                        <option value="<?php echo $a->ID_Proveedor; ?>">[<?php echo $a->ID_Proveedor; ?>] - <?php echo $a->Nombre; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-white" data-dismiss="modal" id="btnCancel">Cerrar</button>
                        <button type="submit" class="btn btn-primary ladda-button" data-style="contract" id="btnSave">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <div class="wrapper wrapper-content  animated fadeInRight">

        <h3>Zona de Almacenaje</h3>

        <div class="row">
            <div class="col-md-12">
                <div class="ibox ">
                    <div class="ibox-title">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="almacenes">Almacen:</label>
                                    <select class="form-control" id="almacenes" name="almacenes">
                                    <option value=" ">Seleccione el Almacen</option>
                                    <?php foreach( $almacenes->getAll() AS $a ): ?>
                                    <option value="<?php echo $a->id; ?>"><?php echo "($a->clave) $a->nombre"; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                </div>
                                <div class="input-group">
                                    <input type="text" class="form-control" name="txtCriterio" id="txtCriterio" placeholder="Buscar...">
                                    <div class="input-group-btn">
                                        
                                        <button onclick="ReloadGrid()" id="buscarA" type="submit" class="btn btn-primary">
                                            <span clas="fa fa-search"></span> Buscar
                                        </button>
                                        
                                    </div>
                                </div>

                            </div>
                            <div class="col-md-8">
                                <br><br><br><br>


                                <a href="#" class="permiso_registrar" onclick="agregar()"><button class="btn btn-primary" type="button"><i class="fa fa-plus"></i>&nbsp;&nbsp;Nuevo</button></a>


                                <a href="/api/v2/zona-de-almacenaje/exportar" target="_blank" class="btn btn-primary pull-right" style="margin-left:15px;"><span class="fa fa-upload"></span> Exportar</a>
                                <button class="btn btn-primary pull-right permiso_registrar" style="margin-left:15px;" data-toggle="modal" data-target="#importar"><span class="fa fa-download"></span> Importar</button>


                                <button class="btn btn-primary pull-right" type="button" id="inactivos"><i class="fa fa-search"></i>&nbsp;&nbsp;Zonas de almacenajes inactivas</button>
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
                        <h4 class="modal-title">Recuperar Zona de Almacenaje</h4>
                    </div>
                    <div class="modal-body">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="almacenes">Almacen:</label>
                                <select class="form-control" id="almacenes1" name="almacenes">
                                <option value=" ">Seleccione el Almacen</option>
                                <?php foreach( $almacenes->getAll() AS $a ): ?>
                                <option value="<?php echo $a->id; ?>"><?php echo $a->nombre; ?></option>
                                <?php endforeach; ?>
                            </select>
                            </div>
                            <div class="input-group">
                                <input type="text" class="form-control" name="txtCriterio" id="txtCriterio1" placeholder="Buscar...">
                                <div class="input-group-btn">
                                    <a href="#" onclick="ReloadGrid1()">
                                        <button type="submit" id="buscarA" class="btn btn-primary">
                                        <span class="fa fa-search"></span> Buscar
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



    <div class="modal fade" id="importar" role="dialog">
        <div class="vertical-alignment-helper">
            <div class="modal-dialog vertical-align-center">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Importar Zona de almacenaje</h4>
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
            url: '/zona-de-almacenaje/importar',
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
        /**
         * @author Ricardo Delgado.
         * Busca y selecciona el almacen predeterminado para el usuario.
         */
        var control_abc = document.getElementById('control_abc'),
        control_tipo_A = document.getElementById('control_tipo_A'),
        control_tipo_B = document.getElementById('control_tipo_B'),
        control_tipo_C = document.getElementById('control_tipo_C');

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
                        document.getElementById('almacenes').value = data.codigo.id;
                        setTimeout(function() {
                            ReloadGrid();
                        }, 1000);
                    }
                },
                error: function(res) {
                    window.console.log(res);
                }
            });
        }
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
                url: '/api/almacen/lista/index.php',
                datatype: "json",
                shrinkToFit: false,
                height: 'auto',
                postData: {
                    criterio: $("#txtCriterio").val()
                },
                mtype: 'POST',
                colNames: ["Acciones", 'ID', 'Clave', 'Descripción', 'Almacén', 'Tipo Almacén', 'Clasif. ABC', 'Empresa'],
                colModel: [
                    //{name:'id',index:'id', width:60, sorttype:"int", editable: false},
                    {
                        name: 'myac',
                        index: '',
                        width: 100,
                        fixed: true,
                        sortable: false,
                        resize: false,
                        formatter: imageFormat
                    }, {
                        name: 'cve_almac',
                        index: 'cve_almac',
                        width: 110,
                        editable: false,
                        hidden: true,
                        sortable: false
                    }, {
                        name: 'clave_almacen',
                        index: 'clave_almacen',
                        width: 100,
                        editable: false,
                        sortable: false
                    },
                    {
                        name: 'des_almac',
                        index: 'des_almac',
                        width: 200,
                        editable: false,
                        sortable: false
                    },
                    {
                        name: 'nombre',
                        index: 'nombre',
                        width: 200,
                        editable: false,
                        sortable: false
                    }, 
                    {
                        name: 'tipo_almacen',
                        index: 'tipo_almacen',
                        width: 200,
                        editable: false,
                        sortable: false
                    }, 
                    {
                        name: 'clasif_abc',
                        index: 'clasif_abc',
                        width: 80,
                        editable: false,
                        sortable: false
                    }, 
                    {
                        name: 'empresa_proveedor',
                        index: 'empresa_proveedor',
                        width: 200,
                        editable: false,
                        sortable: false
                    }
                ],
                rowNum: 30,
                rowList: [30, 40, 50],
                pager: pager_selector,
                sortname: 'cve_almac',
                viewrecords: true,
                sortorder: "desc",
                loadComplete: almacenPrede()
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
                var serie = rowObject[2];
                //var correl = rowObject[4];
                //var url = "x/?serie="+serie+"&correl="+correl;
                //var url2 = "v/?serie="+serie+"&correl="+correl;
                // la funcion downloadxml ejecuta el index.php que esta en la carpeta x pasandole la serie y el correlativo via get
                // la funcion viewPdf ejecuta el index.php que esta en la carpeta v pasandole la serie y el correlativo via get

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
                        almacen: $("#almacenes").val(),
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
                        almacen: $("#almacenes1").val(),
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
                    clave_almacen: _codigo,
                    action: "inUse"
                },
                beforeSend: function(x) {
                    if (x && x.overrideMimeType) {
                        x.overrideMimeType("application/json;charset=UTF-8");
                    }
                },
                url: '/api/almacen/update/index.php',
                success: function(data) {
                    if (data.success == true) {
                        swal(
                            '¡Alerta!',
                            'La zona de almacenaje esta siendo usada en este momento',
                            'warning'
                        );
                    } else {
                        swal({
                                title: "¿Está seguro que desea borrar la zona de almacenaje?",
                                text: "Está a punto de borrar una zona de almacenaje  y esta acción no se puede deshacer",
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
                                        clave_almacen: _codigo,
                                        action: "delete"
                                    },
                                    beforeSend: function(x) {
                                        if (x && x.overrideMimeType) {
                                            x.overrideMimeType("application/json;charset=UTF-8");
                                        }
                                    },
                                    url: '/api/almacen/update/index.php',
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
            console.log("editar() = ", _codigo);
            $("#hiddenIDAlmacen").val(_codigo);
            $("#modaltitle").html('<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button><i class="fa fa-laptop modal-icon"></i><h4 class="modal-title">Editar Zona de Almacenaje</h4>');
            $(".itemlist").remove();
            $("#CAlmacen, #almacenP").prop('disabled', true);
            $("#CodeMessage").html("");
            $("#almacenP").val("");
            $("#tipoAlmacen").val("");
            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    cve_almac: _codigo,
                    action: "load"
                },
                beforeSend: function(x) {
                    if (x && x.overrideMimeType) {
                        x.overrideMimeType("application/json;charset=UTF-8");
                    }
                },
                url: '/api/almacen/update/index.php',
                success: function(data) {
                    console.log("EDIT = ", data);
                    if (data.success == true) {
                        $("#tipoAlmacen").val(data.Cve_TipoZona);
                        $("#NAlmacen").val(data.NAlmacen);
                        $("#txtNomCompa").select2("val", data.txtNomCompa);
                        $("#CAlmacen").val(data.CAlmacen);
                        $("#almacenP").val(data.almacenP);
                        l.ladda('stop');
                        $("#btnCancel").show();
                        $modal0 = $("#myModal");
                        $modal0.modal('show');
                        $("#hiddenAction").val("edit");
                        $("#hiddenIDAlmacen").val(data.CAlmacen);

                        control_abc.checked = false;
                        control_tipo_A.checked = false;
                        control_tipo_B.checked = false;
                        control_tipo_C.checked = false;
                          if(data.clasif_abc != '')
                          {
                              control_abc.checked = true;
                              if(data.clasif_abc == 'A') control_tipo_A.checked = true;
                              else if(data.clasif_abc == 'B') control_tipo_B.checked = true;
                              else if(data.clasif_abc == 'C') control_tipo_C.checked = true;
                              $('.tipo_ABC').show();
                          }
                        $("#empresa_proveedor").val(data.ID_Proveedor);

                    }
                }
            });
        }

        function agregar() {
          control_abc.checked = false;
          control_tipo_A.checked = false;
          control_tipo_B.checked = false;
          control_tipo_C.checked = false;

            $("#modaltitle").html('<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button><i class="fa fa-laptop modal-icon"></i><h4 class="modal-title">Agregar Zona de Almacenaje</h4>');
            $modal0 = $("#myModal");
            $modal0.modal('show');
            $(".itemlist").remove();
            l.ladda('stop');
            //$('#codigo').prop('disabled', false);
            $("#hiddenAction").val("add");
            $("#btnCancel").show();
            $("#NAlmacen").val("");
            $("#CAlmacen").val("");
            $("#almacenP").val("");
            $("#tipoAlmacen").val("");
            $("#CAlmacen, #almacenP").prop('disabled', false);

            $("#CodeMessage").html("");
            // $("#Direccion").val("");


        }

    $('#control_abc').change(function() 
    {
        if($("#control_abc").is(':checked'))
            $('.tipo_ABC').show();
        else
            $('.tipo_ABC').hide();

        $('#control_tipo_A').prop('checked', false);
        $('#control_tipo_B').prop('checked', false);
        $('#control_tipo_C').prop('checked', false);
    });

    $('#control_tipo_A').change(function() 
    {
        $('#control_tipo_B').prop('checked', false);
        $('#control_tipo_C').prop('checked', false);
    });

    $('#control_tipo_B').change(function() 
    {
        $('#control_tipo_A').prop('checked', false);
        $('#control_tipo_C').prop('checked', false);
    });

    $('#control_tipo_C').change(function() 
    {
        $('#control_tipo_A').prop('checked', false);
        $('#control_tipo_B').prop('checked', false);
    });

        var l = $('.ladda-button').ladda();
        l.click(function() {

            if ($('#almacenP').val() == "") {
                return
            }
            if ($('#CAlmacen').val() == "") {
                return
            }
            if ($('#NAlmacen').val() == "") {
                return
            }

            $("#btnCancel").hide();

            l.ladda('start');

            var rels = [], controlABC = '';

            $("#to").each(function() {
                var localRels = [];

                $(this).find('li').each(function() {
                    localRels.push($(this).attr('value'));
                });

                rels.push(localRels);
            });

      if(control_abc.checked)
      {
          if(control_tipo_A.checked) controlABC = 'A';
          else if(control_tipo_B.checked) controlABC = 'B';
          else if(control_tipo_C.checked) controlABC = 'C';
      }


            $.post('/api/almacen/update/index.php', {
                        cve_almac: $("#hiddenIDAlmacen").val(),
                        clave_almacen: $("#CAlmacen").val(),
                        des_almac: $("#NAlmacen").val(),
                        cve_almacenp: $("#almacenP").val(),
                        cve_talmacen: $("#tipoAlmacen").val(),
                        empresa_proveedor: $("#empresa_proveedor").val(),
                        controlABC: controlABC,
                        //   des_direcc : $("#Direccion").val(),
                        action: $("#hiddenAction").val(),
                        usuarios: rels
                    },
                    function(response) {
                        console.log(response);
                    }, "json")
                .always(function() {
                    $("#hiddenIDAlmacen").val("");
                    $("#NAlmacen").val("");
                    $("#txtNomCompa").val("");
                    // $("#Direccion").val("");
                    $("#CAlmacen").val("");
                    l.ladda('stop');
                    $("#btnCancel").show();
                    $modal0.modal('hide');
                    ReloadGrid();
                    ReloadGrid1();
                });

            /*$.post( "/api/almacen/update/index.php",
             {
                 cve_almac : $("#hiddenIDAlmacen").val(),
                 des_almac : $("#NAlmacen").val(),
                 cve_cia : $("#txtNomCompa").val(),
                 des_direcc : $("#Direccion").val(),
                 action : $("#hiddenAction").val(),
                 usuarios: rels

             } ,function( data ) {
             alert(data);
             });*/
        });

        $("#CAlmacen").keyup(function(e) {

            var clave_almacen = $(this).val();

            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    clave_almacen: clave_almacen,
                    action: "exists"
                },
                beforeSend: function(x) {
                    if (x && x.overrideMimeType) {
                        x.overrideMimeType("application/json;charset=UTF-8");
                    }
                },
                url: '/api/almacen/update/index.php',
                success: function(data) {
                    if (data.success == false) {
                        $("#CodeMessage").html("");
                        $("#btnSave").prop('disabled', false);
                    } else {
                        $("#CodeMessage").html(" Clave de zona de almacenaje ya existe");
                        $("#btnSave").prop('disabled', true);
                    }
                }

            });

        });

    $("#CAlmacen").keydown(function(e)
    {
        //var permitidos = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '_'], codigo = '';

        //var nopermitidos = [' ','-','/','\\','Ñ',',',';','.',':','{','}','Ç','¨','´','Á','É','Í','Ó','Ú','À','È','Ì','Ò','Ù','+','[',']','*','^','`','¡','¿',"'",'"','?','=','(',')','&','%','$','·','#','@','|','!','º','ª'];

        var nopermitidos = [' ','/','\\','Ñ',',',';',':','{','}','Ç','¨','´', 'Á','É','Í','Ó','Ú','À','È','Ì','Ò','Ù','+','[',']','*','^','`','¡','¿',"'",'"','?','=','(',')','&','%','$','·','#','@','|','!','º','ª'];
        if($.inArray(e.key.toUpperCase(), nopermitidos) != -1)
        {
            console.log("NO = ", e.key);
            //codigo = $(this).val();
            //console.log("CVE = ", codigo.length);
            //codigo[codigo.length] = '';
            //$(this).val(codigo);
            return false;
        }

        console.log("KEY = ", e.key);
        if(e.key == 'Dead') {var dead = $(this).val(); $(this).val(dead.replace('Dead', ""));}

    });

    $("#CAlmacen").blur(function(e)
    {

        var dead = $(this).val(); 
        $(this).val(dead.replace("`", ""));
        $(this).val(dead.replace("´", ""));

    });

    </script>
    <script>
        $(document).ready(function() {
            $("#txtNomCompa").select2();

            $("#minimum").select2({
                minimumInputLength: 2
            });

            $("#minimum2").select2({
                minimumInputLength: 2
            });

            $("div.ui-jqgrid-bdiv").css("max-height", $("#list").height() - 80);

        });


        $("#txtCriterio").keyup(function(event) {
            if (event.keyCode == 13) {
                $("#buscarA").click();
            }
        });


        $("#inactivos").on("click", function() {
            $modal0 = $("#coModal");
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
                url: '/api/almacen/lista/index_i.php',
                datatype: "json",
                shrinkToFit: false,
                height: 'auto',
                postData: {
                    criterio: $("#txtCriterio").val()
                },
                mtype: 'POST',
                colNames: ['ID', 'Clave', 'Almacén', 'Descripción', "Acciones"],
                colModel: [
                    //{name:'id',index:'id', width:60, sorttype:"int", editable: false},
                    {
                        name: 'cve_almac',
                        index: 'cve_almac',
                        width: 110,
                        editable: false,
                        hidden: true,
                        sortable: false
                    }, {
                        name: 'clave_almacen',
                        index: 'clave_almacen',
                        width: 150,
                        editable: false,
                        sortable: false
                    }, {
                        name: 'nombre',
                        index: 'nombre',
                        width: 300,
                        editable: false,
                        sortable: false
                    },

                    {
                        name: 'des_almac',
                        index: 'des_almac',
                        width: 400,
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
                    },
                ],
                rowNum: 10,
                rowList: [30, 40, 50],
                pager: pager_selector,
                sortname: 'cve_almac',
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

                // la funcion downloadxml ejecuta el index.php que esta en la carpeta x pasandole la serie y el correlativo via get
                // la funcion viewPdf ejecuta el index.php que esta en la carpeta v pasandole la serie y el correlativo via get

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

        function recovery(_codigo) {
            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    cve_almac: _codigo,
                    action: "recovery"
                },
                beforeSend: function(x) {
                    if (x && x.overrideMimeType) {
                        x.overrideMimeType("application/json;charset=UTF-8");
                    }
                },
                url: '/api/almacen/update/index.php',
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