<?php
include $_SERVER['DOCUMENT_ROOT']."/app/host.php";
$ga = new \Proveedores\Proveedores();
if(!isNikken()){
    $codDaneSql = \db()->prepare("SELECT *  FROM c_dane ");
    $codDaneSql->execute();
    $codDane = $codDaneSql->fetchAll(PDO::FETCH_ASSOC);
}
$vere = \db()->prepare("select * from t_profiles as a where id_menu=18 and id_submenu=37 and id_role='".$_SESSION["perfil_usuario"]."'");
$vere->execute();
$ver = $vere->fetchAll(PDO::FETCH_ASSOC);


$agre = \db()->prepare("select * from t_profiles as a where id_menu=18 and id_submenu=38 and id_role='".$_SESSION["perfil_usuario"]."'");
$agre->execute();
$ag = $agre->fetchAll(PDO::FETCH_ASSOC);


$edita = \db()->prepare("select * from t_profiles as a where id_menu=18 and id_submenu=39 and id_role='".$_SESSION["perfil_usuario"]."'");
$edita->execute();
$edit = $edita->fetchAll(PDO::FETCH_ASSOC);


$borra = \db()->prepare("select * from t_profiles as a where id_menu=18 and id_submenu=40 and id_role='".$_SESSION["perfil_usuario"]."'");
$borra->execute();
$borrar = $borra->fetchAll(PDO::FETCH_ASSOC);
?>
    <link href="/css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">
    <link href="/css/plugins/chosen/chosen.css" rel="stylesheet">
    <link href="/css/plugins/sweetalert/sweetalert.css" rel="stylesheet">


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
    <script src="/js/plugins/sweetalert/sweetalert.min.js"></script>
    <script src="/js/plugins/chosen/chosen.jquery.js"></script>

    <!-- Jquery Validate -->
    <script src="/js/plugins/validate/jquery.validate.min.js"></script>


    <style type="text/css">
        .ui-jqgrid,
        .ui-jqgrid-view,
        .ui-jqgrid-hdiv,
        .ui-jqgrid-bdiv,
        .ui-jqgrid,
        .ui-jqgrid-htable,
        #grid-table,
        #grid-table2,
        #grid-table3,
        #grid-table4,
        #grid-pager,
        #grid-pager2,
        #grid-pager3,
        #grid-pager4 {
            width: 100% !important;
            max-width: 100% !important;
        }
    </style>

    <!-- Mainly scripts -->

    <div class="wrapper wrapper-content  animated fadeInRight" id="FORM" style="display: none">

        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-title">
                        <div class="row">
                            <div class="col-lg-4" id="_title">
                                <h3>Agregar Proveedor</h3>
                            </div>
                        </div>
                    </div>
                    <form id="myform" role="form">
                        <input type="hidden" id="hiddenAction" name="hiddenAction">
                        <input type="hidden" id="hiddenIDProveedor">
                        <div class="ibox-content">
                            <div class="row">

                                <div class="col-lg-6 b-r">
                                    <div class="form-group"><label>Clave Proveedor</label> <input id="cve_proveedor" name="cve_proveedor" type="text" placeholder="Clave Proveedor" class="form-control" maxlength="20" >
                                        <!--<label id="CodeMessage" style="color:red;"></label>--></div>
                                    <div class="form-group"><label>Razon Social *</label> <input id="nombre_proveedor" type="text" placeholder="Nombre" class="form-control" name="nombre_proveedor"></div>
                                    <div class="form-group"><label>Direccion</label> <input id="Direccion" type="text" placeholder="Direccion" class="form-control" name="Direccion"></div>
                                    <div class="form-group"><label>Colonia</label> <input id="colonia" type="text" placeholder="Colonia" class="form-control"></div>
                                    <div class="form-group">
                                        <label>Código Dane / Código postal *</label>
                                        <?php if(isset($codDane) && !empty($codDane)): ?>
                                        <select id="txtCod" class="form-control" >
                                            <option value="">Código</option>
                                            <?php foreach( $codDane AS $p ): ?>
                                                <option value="<?php echo $p["cod_municipio"];?>"><?php echo $p["cod_municipio"]." ".$p["des_municipio"]; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <?php else: ?>
                                        <input type="text" name="txtCod" id="txtCod" class="form-control" >
                                        <?php endif; ?>
                                    </div>
                                    <div class="form-group"><label>Municipio/Ciudad</label> <input id="txtDepart" type="text" placeholder="Departamento" class="form-control"></div>
                                    <div class="form-group"><label>Departamento/Estado</label> <input id="txtMunicipio" type="text" placeholder="Municipio" class="form-control"></div>
                                </div>
                                <div class="col-lg-6">

                                    <div class="form-group"><label>País *</label> <input id="pais" type="text" placeholder="País" class="form-control" ></div>
                                    <div class="form-group"><label>RUT/RFC </label> <input id="RUT" type="text" placeholder="RUT" maxlength="15" oninput="this.value = this.value.toUpperCase();" class="form-control" ></div>
                                    <div class="form-group"><label>Telefono 1</label> <input id="telefono1" type="text" maxlength="14" oninput="this.value = this.value.replace(/[^0-9]/g, '');" placeholder="Telefono 1" class="form-control"></div>
                                    <div class="form-group"><label>Telefono 2</label> <input id="telefono2" type="text" maxlength="14" oninput="this.value = this.value.replace(/[^0-9]/g, '');" placeholder="Telefono 2" class="form-control"></div>

                            <div class="form-group">
                                <div class="checkbox">
                                    <label for="cliente_proveedor" style="font-weight: 600;font-size: 14px; -moz-user-select: none; -webkit-user-select: none;-ms-user-select: none; user-select: none;">
                                    <input type="checkbox" name="cliente_proveedor" id="cliente_proveedor" value="0">Empresa | Proveedor</label>
                                </div>
                            </div>
                                    <div class="form-group">
                                        <label for="transportista" style="font-weight: 600;font-size: 14px; -moz-user-select: none; -webkit-user-select: none;-ms-user-select: none; user-select: none;margin-left: 30px;margin-top: 30px;cursor: pointer;">
                                        <input type="checkbox" name="transportista" id="transportista" value="0">  Es Transportadora</label>
                                    </div>

                                    <input type="hidden" id="hiddenAction">
                                    <input type="hidden" id="hiddenIDProveedor">

                                    <div class="pull-right">
                                        <a href="#" onclick="cancelar()"><button type="button" class="btn btn-white" id="btnCancel">Cerrar</button></a>&nbsp;&nbsp;
                                        <button type="submit" class="btn btn-primary ladda-button" data-style="contract" id="btnSave">Guardar</button>
                                    </div>

                                </div>
                            </div>

                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="waModal" role="dialog">
        <div class="vertical-alignment-helper">
            <div class="modal-dialog vertical-align-center">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Advertencia</h4>
                    </div>
                    <div class="modal-body">
                        <p>Verificar que no hayan campos vacíos</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
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
                        <h4 class="modal-title">Recuperar Proveedor</h4>
                    </div>
                    <div class="modal-body">
                        <div class="col-lg-4">
                            <div class="input-group">
                                <input type="text" class="form-control input-sm" name="txtCriterio" id="txtCriterio1" placeholder="Buscar por Proveedor...">
                                <div class="input-group-btn">
                                    <a href="#" onclick="ReloadGrid1()">
                                        <button type="submit" id="buscarA" class="btn btn-sm btn-primary">
                                        Buscar
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


    <div class="wrapper wrapper-content  animated fadeInRight" id="prov">
        <h3>Proveedores</h3>
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-title">
                        <div class="row">
                            <div class="col-lg-4">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="txtCriterio" id="txtCriterio" placeholder="Buscar...">
                                    <div class="input-group-btn">
                                        
                                        <button onclick="ReloadGrid()" type="submit" class="btn btn-primary" id="buscarP">
                                            <span class="fa fa-search"></span> Buscar
                                        </button>
                                        
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-8">


                                <a href="#" class="permiso_registrar" onclick="agregar()"><button class="btn btn-primary" type="button"><i class="fa fa-plus"></i>&nbsp;&nbsp;Nuevo</button></a>


                                <a href="/api/v2/proveedores/exportar" target="_blank" class="btn btn-primary pull-right" style="margin-left:15px"><span class="fa fa-upload"></span> Exportar</a>
                                <button class="btn btn-primary pull-right permiso_registrar" style="margin-left:15px" data-toggle="modal" data-target="#importar"><span class="fa fa-download"></span> Importar</button>


                                <button class="btn btn-primary pull-right" type="button" id="inactivos"><i class="fa fa-search"></i>&nbsp;&nbsp;Proveedores inactivos</button>
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


    <div class="modal fade" id="importar" role="dialog">
        <div class="vertical-alignment-helper">
            <div class="modal-dialog vertical-align-center">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Importar Proveedores</h4>
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
                        <div class="col-md-6" style="text-align: left">
                            <button id="btn-layout" type="button" class="btn btn-primary">Descargar Layout</button><!--cambiar layout-->
                        </div>
                        <div class="col-md-6" style="text-align: right">
                            <button id="btn-import" type="button" class="btn btn-primary">Importar</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>


<script>

$('#btn-layout').on('click', function(e) {
  //e.preventDefault();  //stop the browser from following
  //window.location.href = 'http://www.tinegocios.com/proyectos/wms/Layout_Pedidos.xlsx';
  window.location.href = '/Layout/Layout_Proveedores.xlsx';

}); 

    $('#btn-import').on('click', function() {

        $('#btn-import').prop('disable', true);
        var bar = $('.progress-bar');
        var percent = $('.percent');
        //var status = $('#status');

        var formData = new FormData();
        formData.append("clave", "valor");

        $.ajax({
            // Your server script to process the upload
            url: '/proveedores/importar',
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
                url: '/api/proveedores/lista/index.php',
                datatype: "json",
                shrinkToFit: false,
                height: 'auto',
                postData: {
                    criterio: $("#txtCriterio").val()
                },
                mtype: 'POST',
                colNames: ["Acciones", 'ID', 'Clave', "Razon Social", "RUT", "Dirección", "Código Dane", "Departamento/Estado", "Municipio/Ciudad", "Es Transportadora"],
                colModel: [
                    //{name:'id',index:'id', width:60, sorttype:"int", editable: false},
                    {
                        name: 'myac',
                        index: '',
                        width: 80,
                        fixed: true,
                        sortable: false,
                        resize: false,
                        formatter: imageFormat
                    },
                    {
                        name: 'ID_Proveedor',
                        index: 'ID_Proveedor',
                        width: 40,
                        editable: false,
                        sortable: false,
                        hidden: true
                    }, {
                        name: 'cve_proveedor',
                        index: 'cve_proveedor',
                        width: 100,
                        editable: false,
                        sortable: false,
                        hidden: false
                    }, {
                        name: 'Nombre',
                        index: 'Nombre',
                        width: 200,
                        editable: false,
                        sortable: false
                    }, {
                        name: 'RUT',
                        index: 'RUT',
                        width: 100,
                        editable: false,
                        sortable: false,
                        hidden: true
                    }, {
                        name: 'direccion',
                        index: 'direccion',
                        width: 300,
                        editable: false,
                        sortable: false
                    }, {
                        name: 'cve_dane',
                        index: 'cve_dane',
                        width: 200,
                        editable: false,
                        sortable: false
                    }, {
                        name: 'departamento',
                        index: 'departamento',
                        width: 200,
                        editable: false,
                        sortable: false
                    }, {
                        name: 'des_municipio',
                        index: 'des_municipio',
                        width: 200,
                        editable: false,
                        sortable: false
                    },
                    {name: 'es_transportadora',index:'es_transportadora',width:120, editable:false, sortable:false}


                ],
                rowNum: 30,
                rowList: [30, 40, 50],
                pager: pager_selector,
                sortname: 'ID_Proveedor',
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
                $("#hiddenIDProveedor").val(serie);
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
                    ID_Proveedor: _codigo,
                    action: "tieneOrden"
                },
                beforeSend: function(x) {
                    if (x && x.overrideMimeType) {
                        x.overrideMimeType("application/json;charset=UTF-8");
                    }
                },
                url: '/api/proveedores/update/index.php',
                success: function(data) {
                    if (data.success == true) {
                        swal(
                            '¡Alerta!',
                            'El proveedor esta siendo usado en este momento',
                            'warning'
                        );
                    } else {
                        swal({
                                title: "¿Está seguro que desea borrar el proveedor?",
                                text: "Está a punto de borrar un proveedor y esta acción no se puede deshacer",
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
                                        ID_Proveedor: _codigo,
                                        action: "delete"
                                    },
                                    beforeSend: function(x) {
                                        if (x && x.overrideMimeType) {
                                            x.overrideMimeType("application/json;charset=UTF-8");
                                        }
                                    },
                                    url: '/api/proveedores/update/index.php',
                                    success: function(data) {
                                        if (data.success == true) {
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

            $("#hiddenIDProveedor").val(_codigo);
            $("#_title").html('<h3>Editar Proveedor</h3>');
            $("#emailMessage").html("");
            $("#CodeMessage").html("");
            $("#cve_proveedor").prop('disabled', true);

            $("#myform").validate({
                rules: {
                    cve_proveedor: {
                        required: true
                    },
                    nombre_proveedor: {
                        required: true
                    }
                }
            });

            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    ID_Proveedor: _codigo,
                    action: "load"
                },
                beforeSend: function(x) {
                    if (x && x.overrideMimeType) {
                        x.overrideMimeType("application/json;charset=UTF-8");
                    }
                },
                url: '/api/proveedores/update/index.php',
                success: function(data) {
                    console.log(data);
                    if (data.success == true) {
                        $("#hiddenIDProveedor").val(_codigo);
                        $("#cve_proveedor").val(data.cve_proveedor);
                        $("#nombre_proveedor").val(data.nombre_proveedor);
                        $("#Direccion").val(data.direccion);
                        $("#colonia").val(data.colonia);
                        $("#txtCod").val(data.cve_dane).change();
                        $("#txtDepart").val(data.ciudad);
                        $("#txtMunicipio").val(data.estado);
                        $("#pais").val(data.pais);
                        $("#RUT").val(data.RUT);
                        $("#telefono1").val(data.telefono1);
                        $("#telefono2").val(data.telefono2);
                        //l.ladda('stop');
                        $("#btnCancel").show();

                        if(data.es_transportista == 1)
                        {
                            $("#transportista").prop("checked", true);
                        }

                        if(data.cliente_proveedor == 1)
                          $("#cliente_proveedor").prop("checked", true);

                        $('#prov').hide();

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

            $('#prov').show();

            window.location.reload();
        }

        function agregar() {
            $("#_title").html('<h3>Agregar Proveedor</h3>');
            $("#cve_proveedor").prop('disabled', false);

            $("#txtDepart").val("");
            $("#txtMunicipio").val("");

            $(':input', '#myform')
                .removeAttr('checked')
                .removeAttr('selected')
                .not(':button, :submit, :reset, :hidden, :radio, :checkbox')
                .val('');

            $('#prov').hide();
            $('txtCod').val("");

            $('#FORM').show();
            $('#FORM').removeAttr('class').attr('class', '');
            $('#FORM').addClass('animated');
            $('#FORM').addClass("fadeInRight");
            $("#hiddenAction").val("add");

        }
        var buttonSave = document.getElementById('myform');
        buttonSave.addEventListener('submit', function(e) {
            e.preventDefault();
        });

        buttonSave.onsubmit = function() {

            /*$("#myform").validate({
                rules: {
                    cve_proveedor: {
                        required: true
                    },
                    nombre_proveedor: {
                        required: true
                    }
                }
            });*/

            /*if ($("#cve_proveedor").val() == "") {
                alert("Ingrese una clave de Proveedor");
                return;
            }*/
            if ($("#nombre_proveedor").val() == "") {
                alert("Ingrese un nombre de Proveedor");
                return;
            }
            /*
            if($("#txtCod").val() == "")
            {
              alert("Ingrese Codigo Postal");
              return;
            }
            */
            if($("#pais").val() == "")
            {
              alert("Ingrese Pais");
              return;
            }
/*
            if($("#RUT").val() == "")
            {
              alert("Ingrese RUT/RFC");
              return;
            }
*/          
            //if ($("#Direccion").val()=="") {return;}

            $("#btnCancel").hide();

            //l.ladda('start');
            var transportista = 0;
            if($("#transportista").is(':checked'))
                transportista = 1;

            if ($("#hiddenAction").val() == "add") 
            {
                console.log("proveedor add");
                $.ajax({
                    type: "POST",
                    dataType: "json",
                    data: {
                        codigo: $("#cve_proveedor").val(),
                        action: "exists"
                    },
                    beforeSend: function(x) {
                        if (x && x.overrideMimeType) {
                            x.overrideMimeType("application/json;charset=UTF-8");
                        }
                    },
                    url: '/api/proveedores/update/index.php',
                    success: function(data) {
                        console.log("SUCESS", data);
                        if (data.success == false) {
                            var cliente_proveedor = 0;
                            if($("#cliente_proveedor").is(':checked'))
                                cliente_proveedor = 1;

                            $.ajax({
                                type: "POST",
                                dataType: "json",
                                data: {
                                    //ID_Proveedor: $("#hiddenIDProveedor").val(),
                                    cve_proveedor: $("#cve_proveedor").val(),
                                    nombre_proveedor: $("#nombre_proveedor").val(),
                                    direccion: $("#Direccion").val(),
                                    colonia: $("#colonia").val(),
                                    cve_dane: $("#txtCod").val(),
                                    ciudad: $("#txtDepart").val(),
                                    estado: $("#txtMunicipio").val(),
                                    transportista: transportista,
                                    pais: $("#pais").val(),
                                    RUT: $("#RUT").val(),
                                    telefono1: $("#telefono1").val(),
                                    telefono2: $("#telefono2").val(),
                                    cliente_proveedor: cliente_proveedor,
                                    action: "add"
                                },
                                beforeSend: function(x) {
                                    if (x && x.overrideMimeType) {
                                        x.overrideMimeType("application/json;charset=UTF-8");
                                    }
                                },
                                url: '/api/proveedores/update/index.php',
                                success: function(data) {
                                    console.log("SUCESS-2: ", data);
                                    if (data.success == true) {
                                        cancelar();
                                        ReloadGrid();
                                        //l.ladda('stop');
                                        $("#btnCancel").show();
                                    } else {
                                        alert(data.err);
                                        //l.ladda('stop');
                                        $("#btnCancel").show();
                                    }
                                    console.log(data);
                                    window.location.reload();
                                }, error: function(data)
                                {
                                    console.log("ERROR-2: ", data);
                                }
                            });
                        }
                    },
                    error: function(res) {
                        window.console.log("ERROR: ", res);
                    }
                });
            } else {

                console.log("Editar Proveedor");

                var cliente_proveedor = 0;
                if($("#cliente_proveedor").is(':checked'))
                    cliente_proveedor = 1;

                console.log("Editar Proveedor", cliente_proveedor);

                $.ajax({
                    type: "POST",
                    dataType: "json",
                    data: {
                        ID_Proveedor: $("#hiddenIDProveedor").val(),
                        cve_proveedor: $("#cve_proveedor").val(),
                        nombre_proveedor: $("#nombre_proveedor").val(),
                        direccion: $("#Direccion").val(),
                        colonia: $("#colonia").val(),
                        cve_dane: $("#txtCod").val(),
                        ciudad: $("#txtDepart").val(),
                        estado: $("#txtMunicipio").val(),
                        transportista: transportista,
                        pais: $("#pais").val(),
                        RUT: $("#RUT").val(),
                        telefono1: $("#telefono1").val(),
                        telefono2: $("#telefono2").val(),
                        cliente_proveedor: cliente_proveedor,
                        action: "edit"
                    },
                    beforeSend: function(x) {
                        if (x && x.overrideMimeType) {
                            x.overrideMimeType("application/json;charset=UTF-8");
                        }
                    },
                    url: '/api/proveedores/update/index.php',
                    success: function(data) {
                        if (data.success == true) {
                            cancelar();
                            ReloadGrid();
                            //l.ladda('stop');
                            $("#btnCancel").show();
                        } else {
                            alert(data.err);
                            //l.ladda('stop');
                            $("#btnCancel").show();
                        }
                    }, error: function(data)
                    {
                        console.log("ERROR: ", data);
                    }
                });
            } // else
        };
    </script>

    <script>
        $(document).ready(function() {
            $(function() {
                $('.chosen-select').chosen();
                $('.chosen-select-deselect').chosen({
                    allow_single_deselect: true
                });
            });

            $("#inactivos").on("click", function() {
                $modal0 = $("#coModal");
                $modal0.modal('show');
            });
            //  $("div.ui-jqgrid-bdiv").css("max-height",$("#prov").height()-80);
        });
    </script>


    <script>
        $(document).ready(function() {
            $("#txtDepart").prop("disabled", true);
            $("#txtMunicipio").prop("disabled", true);

            $("#txtCod").change(function() {
                $.ajax({
                    type: "POST",
                    dataType: "json",
                    data: {
                        codigo: $("#txtCod").val(),
                        action: "getDane"
                    },
                    beforeSend: function(x) {
                        if (x && x.overrideMimeType) {
                            x.overrideMimeType("application/json;charset=UTF-8");
                        }
                    },
                    url: "/api/clientes/update/index.php",
                    success: function(data) {
                            console.log("SUCCESS", data);
                            if (data.success == true) {
                                $("#txtDepart").prop("disabled", true);
                                $("#txtMunicipio").prop("disabled", true);
                                $("#txtDepart").val(data.departamento);
                                $("#txtMunicipio").val(data.municipio);
                            }
                            else
                            {
                                $("#txtDepart").prop("disabled", false);
                                $("#txtMunicipio").prop("disabled", false);
                                $("#txtDepart").val("");
                                $("#txtMunicipio").val("");
                            }
                    }
                });
            });

        });
    </script>

    <script>
        $(function($) {
            var grid_selector = "#grid-table2";
            var pager_selector = "#grid-pager2";

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
                url: '/api/proveedores/lista/index_i.php',
                datatype: "json",
                shrinkToFit: false,
                height: 'auto',
                postData: {
                    criterio: $("#txtCriterio1").val()
                },
                mtype: 'POST',
                colNames: ['ID', 'Clave', "Razon Social", "RUT", "Dirección", "Código Dane", "Departamento/Estado", "Municipio/Cuidad", "Recuperar"],
                colModel: [
                    //{name:'id',index:'id', width:60, sorttype:"int", editable: false},

                    {
                        name: 'ID_Proveedor',
                        index: 'ID_Proveedor',
                        width: 40,
                        hidden: true,
                        editable: false,
                        sortable: false,
                        hidden: true
                    }, {
                        name: 'cve_proveedor',
                        index: 'cve_proveedor',
                        width: 110,
                        editable: false,
                        sortable: false,
                        hidden: false
                    }, {
                        name: 'Nombre',
                        index: 'Nombre',
                        width: 200,
                        editable: false,
                        sortable: false
                    }, {
                        name: 'RUT',
                        index: 'RUT',
                        width: 100,
                        editable: false,
                        sortable: false,
                        hidden: true
                    }, {
                        name: 'direccion',
                        index: 'direccion',
                        width: 200,
                        editable: false,
                        sortable: false
                    }, {
                        name: 'cve_dane',
                        index: 'cve_dane',
                        width: 200,
                        editable: false,
                        sortable: false
                    }, {
                        name: 'departamento',
                        index: 'departamento',
                        width: 200,
                        editable: false,
                        sortable: false
                    }, {
                        name: 'des_municipio',
                        index: 'des_municipio',
                        width: 200,
                        editable: false,
                        sortable: false
                    },


                    {
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
                rowList: [10, 20, 30],
                pager: pager_selector,
                sortname: 'ID_Proveedor',
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

                var id_proveedor = rowObject[0];

                $("#hiddenIDProveedor").val(id_proveedor);
                // la funcion downloadxml ejecuta el index.php que esta en la carpeta x pasandole la serie y el correlativo via get
                // la funcion viewPdf ejecuta el index.php que esta en la carpeta v pasandole la serie y el correlativo via get

                var html = '';
                //html += '<a href="#" onclick="ver(\''+serie+'\')"><i class="fa fa-eye" alt="Ver"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
                html += '<a href="#" onclick="recovery(\'' + id_proveedor + '\')"><i class="fa fa-check" alt="Borrar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';

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
                    ID_Proveedor: _codigo,
                    action: "recovery"
                },
                beforeSend: function(x) {
                    if (x && x.overrideMimeType) {
                        x.overrideMimeType("application/json;charset=UTF-8");
                    }
                },
                url: '/api/proveedores/update/index.php',
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

    <script>
        $("#txtCriterio").keyup(function(event) {
            if (event.keyCode == 13) {
                $("#buscarP").click();
            }
        });


        $("#cve_proveedor").keyup(function(e) {

            var claveCode = $(this).val();
            var claveCodeRegexp = new RegExp("^[a-zA-Z0-9_-]{1,20}$");

            if (claveCodeRegexp.test(claveCode)) {
                $("#CodeMessage").html("");
                $("#btnSave").prop('disabled', false);

                var cve_proveedor = $(this).val();

                $.ajax({
                    type: "POST",
                    dataType: "json",
                    data: {
                        cve_proveedor: cve_proveedor,
                        action: "exists"
                    },
                    beforeSend: function(x) {
                        if (x && x.overrideMimeType) {
                            x.overrideMimeType("application/json;charset=UTF-8");
                        }
                    },
                    url: '/api/proveedores/update/index.php',
                    success: function(data) {
                        if (data.success == false) {
                            $("#CodeMessage").html("");
                            $("#btnSave").prop('disabled', false);
                        } else {
                            $("#CodeMessage").html(" Clave de proveedor ya existe");
                            $("#btnSave").prop('disabled', true);
                        }
                    }

                });

            } else {
                $("#CodeMessage").html("Por favor, ingresar una Clave de Proveedor válida");
                $("#btnSave").prop('disabled', true);
            }
        });

        $("#cve_proveedor").keyup(function(e) {

            var claveCode = $(this).val();
            var claveCodeRegexp = new RegExp("^[a-zA-Z0-9_-]{1,20}$");

            if (claveCodeRegexp.test(claveCode)) {
                $("#CodeMessage").html("");
                $("#btnSave").prop('disabled', false);

            } else {
                $("#CodeMessage").html("Por favor, ingresar una Clave de Proveedor válida");
                $("#btnSave").prop('disabled', true);
            }
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