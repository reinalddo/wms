<?php
    $listaAlm = new \AlmacenP\AlmacenP();
    $almacenes = new \AlmacenP\AlmacenP();

    $mod=37;
    $var1=105;
    $var2=106;
    $var3=107;
    $var4=108;

    $vere = \db()->prepare("SELECT * from t_profiles as a where id_menu='".$mod."' and id_submenu='".$var1."' and id_role='".$_SESSION["perfil_usuario"]."'");
    $vere->execute();
    $ver = $vere->fetchAll(PDO::FETCH_ASSOC);

    $agre = \db()->prepare("SELECT * from t_profiles as a where id_menu='".$mod."' and id_submenu='".$var2."' and id_role='".$_SESSION["perfil_usuario"]."'");
    $agre->execute();
    $ag = $agre->fetchAll(PDO::FETCH_ASSOC);

    $edita = \db()->prepare("SELECT * from t_profiles as a where id_menu='".$mod."' and id_submenu='".$var3."' and id_role='".$_SESSION["perfil_usuario"]."'");
    $edita->execute();
    $edit = $edita->fetchAll(PDO::FETCH_ASSOC);

    $borra = \db()->prepare("SELECT * from t_profiles as a where id_menu='".$mod."' and id_submenu='".$var4."' and id_role='".$_SESSION["perfil_usuario"]."'");
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

<!-- Jquery Validate -->
<script src="/js/plugins/validate/jquery.validate.min.js"></script>

<div class="wrapper wrapper-content  animated fadeInRight" id="FORM" style="display: none">
    <div class="row">
        <div class="col-md-12">
            <div class="ibox ">
                <div class="ibox-title">
                    <div class="row">
                        <div class="col-md-4" id="_title">
                            <h3>Agregar Pallet y Contenedores</h3>
                        </div>
                    </div>
                </div>
                <form id="myform" role="form">
                    <input type="hidden" id="hiddenAction" name="hiddenAction">
                    <input type="hidden" id="hiddenIDProveedor">
                    <div class="ibox-content">
                        <div class="row">
                            <div class="col-md-6 b-r">
                                <div class="form-group">
                                    <label>Almacen *</label>
                                    <select class="form-control" id="Almacen" name="cve_almac" required="true">
                                        <option value="">Seleccione</option>
                                        <?php foreach( $listaAlm->getAll() AS $p ): ?>
                                            <option value="<?php echo $p->id; ?>"><?php echo $p->nombre; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <label>Tipo de Contenedor *</label>
                                <div class="form-group">
                                    <select class="form-control" id="TipoCont" name="tipo" required="true">
                                        <option value="">Seleccione</option>                        
                                        <option value="Pallet">Pallet</option>
                                        <option value="Contenedor">Contenedor</option>
                                    </select>
                                </div>
                                <label>Clave *</label>
                                <div class="form-group">
                                    <input id="ClavContenedor" type="text" maxlength="20" placeholder="Clave del Contenedor" class="form-control"  name="clave">
                                </div>
                                <label>Descripcion *</label>
                                <div class="form-group">
                                    <input id="descripcion" type="text" maxlength="80" placeholder="Descripción" class="form-control" required="true" name="descripcion">
                                </div>
                                <div class="col-md-12">
                                    <div class="col-md-4">
                                        <label>Alto (mm) *</label>
                                        <input id="altoCont" type="text" <?php /* ?>maxlength="6"<?php */ ?> oninput="this.value = this.value.replace(/[^0-9.]/g, ''); this.value = this.value.replace(/(\..*)\./g, '$1');" placeholder="Alto en mm" class="form-control" name="alto" required="true">
                                    </div>
                                    <div class="col-md-4">
                                        <label>Ancho (mm) *</label>
                                        <input id="anchoCont" type="text" <?php /* ?>maxlength="6"<?php */ ?> oninput="this.value = this.value.replace(/[^0-9.]/g, ''); this.value = this.value.replace(/(\..*)\./g, '$1');" placeholder="Ancho en mm" class="form-control" name="ancho" required="true">
                                    </div>
                                    <div class="col-md-4">
                                        <label>Fondo (mm) *</label>
                                        <input id="profCont" type="text" <?php /* ?>maxlength="6"<?php */ ?> oninput="this.value = this.value.replace(/[^0-9.]/g, ''); this.value = this.value.replace(/(\..*)\./g, '$1');" placeholder="Fondo en mm" class="form-control" name="fondo" required="true">
                                    </div>
                                </div>
                                </br>
                            </div>
                            <div class="col-md-6">
                                </br>
                                <label>Peso (kg) *</label>
                                <input id="pesoCont" type="number" step="0.01" <?php /* ?>maxlength="6"<?php */ ?> placeholder="Peso en kilogramos" class="form-control" name="peso" required="true"/>
                                </br>
                                <label>Capacidad máxima (kg) *</label>
                                <input id="pesoMax" type="number"  <?php /* ?>maxlength="6"<?php */ ?> oninput="this.value = this.value.replace(/[^0-9,]/g, ''); this.value = this.value.replace(/(\..*)\./g, '$1'); " placeholder="Capacidad Máxima en kilogramos" class="form-control" name="capmax" required="true">
                                </br>
                                <label>Capacidad volumétrica (m3) *</label>
                                <input id="capavol" type="number"  <?php /* ?>maxlength="6"<?php */ ?> oninput="parseFloat($(this).val()).toFixed(2)"  type="number" step="0.01" placeholder="Capacidad volumétrica en metros cúbicos" name="capvol" class="form-control" required="true">

                                </br>
                                <div class="form-group">
                                    <label for="TipoContGen">
                                    <input type="checkbox" id="TipoContGen" name="tipoGen">
                                    Pallet/Contenedor Genérico *
                                    </label>
                                </div>

                                <input type="hidden" id="hiddenAction">
                                <input type="hidden" id="hiddenContenedor">
                                </br></br>
                                <div class="pull-right">
                                    <a href="#" onclick="cancelar()"><button type="button" class="btn btn-white" id="btnCancel">Cerrar</button></a>&nbsp;&nbsp;
                                    <button class="btn btn-primary ladda-button" data-style="contract" type="submit" id="btnSave">Guardar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<div class="wrapper wrapper-content  animated fadeInRight" id="list">
    <h3>Pallets y Contenedores</h3>
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
                        </div>
                        <div class="col-md-4">
                                <label>Tipo de Pallet</label>
                                <select id="select-existencia" class="chosen-select form-control">
                                    <option value="2">Todos los Pallets</option>
                                    <option value="1">Genéricos</option>
                                    <option value="0">No Genéricos</option>
                                </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="input-group">
                                <input type="text" class="form-control " name="txtCriterio" id="txtCriterio" placeholder="Buscar...">
                                <div class="input-group-btn">
                                    <button onclick="ReloadGrid()" type="button" id="buscarA" class="btn   btn-primary"><i class="fa fa-search"></i> Buscar</button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <a href="#" class="permiso_registrar" onclick="agregar()"><button class="btn btn-primary" type="button"><i class="fa fa-plus"></i>&nbsp;&nbsp;Nuevo</button></a>
                            <a href="/api/v2/pallets/exportar" target="_blank" class="btn btn-primary pull-right" style="margin-left:15px;"><span class="fa fa-upload"></span> Exportar</a>
                            <button class="btn btn-primary pull-right permiso_registrar" style="margin-left:15px;" data-toggle="modal" data-target="#importar"><span class="fa fa-download"></span> Importar</button>
                            <button class="btn btn-primary pull-right" type="button" id="inactivos"><i class="fa fa-search"></i>&nbsp;&nbsp;Pallet y Contenedores inactivos</button>
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
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Recuperar Pallet y Contenedor</h4>
                </div>
                <div class="modal-body" style="overflow: scroll;">
                    <div class="col-md-4">
                        <div class="input-group">
                            <input type="text" class="form-control" name="txtCriterio" id="txtCriterio1" placeholder="Buscar...">
                            <div class="input-group-btn">
                                <a href="#" onclick="ReloadGrid1()"><button type="submit" id="buscarA" class="btn btn-primary"><span class="fa fa-search"></span> Buscar</button></a>
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
                <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button></div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="importar" role="dialog">
    <div class="vertical-alignment-helper">
        <div class="modal-dialog vertical-align-center">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Importar Pallets y Contenedores</h4>
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
                            <div class="progress-bar progress-bar-info progress-bar-striped" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:0%">
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
    $('#btn-import').on('click', function() 
    {
        $('#btn-import').prop('disable', true);
        var bar = $('.progress-bar');
        var percent = $('.percent');
        var formData = new FormData();
        formData.append("clave", "valor");

        $.ajax({
            // Your server script to process the upload
            url: '/pallets/importar',
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
                console.log("Reload = 8");
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
        function Solo_Numerico(variable) {
            Numer = parseInt(variable);
            if (isNaN(Numer)) {
                return "";
            }
            return Numer;
        }

        function ValNumero(Control) {
            Control.value = Solo_Numerico(Control.value);
        }
        /**
         * @author Ricardo Delgado.
         * Busca y selecciona el almacen predeterminado para el usuario.
         */
        function almacenPrede() {
            console.log("id_user = ", <?php echo $_SESSION["id_user"]?>);
            $.ajax({
                type: "POST",
                dataType: "json",
                async: true,
                cache: false,
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
                    console.log("Reload = 2 ->", data.codigo.id);
                    if (data.success == true) {
                        document.getElementById('almacenes').value = data.codigo.id;
                        setTimeout(function() {
                            ReloadGrid();
                            ReloadGrid1()
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
                url: '/api/contenedores/lista/index.php',
                datatype: "json",
                height: 550,
                shrinkToFit: false,
                //height: '550',
                postData: {
                    almacen: $("#almacenes").val(),
                    criterio: $("#txtCriterio").val(),
                    vacio: $("#select-existencia").val()
                },
                mtype: 'POST',
                colNames: ['ID', 'Acciones', 'Clave', 'Descripción', 'Status', 'Tipo', 'Alto', 'Ancho', 'Fondo', 'Peso', 'Cap. Kgs', 'Cap. Vol','Almacén', 'Genérico'],
                colModel: [
                    {name: 'IDContenedor',index: 'IDContenedor',width: 10,editable: false,hidden: true,align:'center',sortable: false},
					{name: 'myac',index: '',width: 80,fixed: true,sortable: false,resize: false,formatter: imageFormat},
					{name: 'clave_contenedor',index: 'clave_contenedor',width: 130,editable: false,sortable: false},
					{name: 'descripcion',index: 'descripcion',width: 150,editable: false,align:'left',sortable: false},
					{name: 'statu',index: 'statu',width: 100,editable: false,align:'center',sortable: false, hidden:true},
					{name: 'tipo',index: 'tipo',width: 100,editable: false,align:'center',sortable: false},
					{name: 'alto',index: 'alto',width: 100,editable: false,align:'right',sortable: false},
					{name: 'ancho',index: 'ancho',width: 100,editable: false,align:'right',sortable: false},
					{name: 'fondo',index: 'fondo',width: 100,editable: false,align:'right',sortable: false},
					{name: 'peso',index: 'peso',width: 100,editable: false,align:'right',sortable: false},
					{name: 'pesomax',index: 'pesomax',width: 100,editable: false,align:'right',sortable: false},
					{name: 'capavol',index: 'capavol',width: 100,editable: false,align:'right',sortable: false},
                    {name: 'des_almac',index: 'des_almac',width: 150,editable: false,align:'center',sortable: false},
                    {name: 'TipoGen',index: 'TipoGen',width: 150,editable: false,align:'center',sortable: false},
                ],
                rowNum: 30,
                rowList: [30, 40, 50],
                pager: pager_selector,
                sortname: 'IDContenedor',
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
                var almacen = rowObject[0];
                $("#hiddenContenedor").val(serie);
                //var correl = rowObject[4];
                //var url = "x/?serie="+serie+"&correl="+correl;
                //var url2 = "v/?serie="+serie+"&correl="+correl;
                // la funcion downloadxml ejecuta el index.php que esta en la carpeta x pasandole la serie y el correlativo via get
                // la funcion viewPdf ejecuta el index.php que esta en la carpeta v pasandole la serie y el correlativo via get

                var html = '';
                //html += '<a href="#" onclick="ver(\''+serie+'\')"><i class="fa fa-eye" alt="Ver"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
                if($("#permiso_editar").val() == 1)
                html += '<a href="#" onclick="editar(\'' + almacen + '\')" title="Editar"><i class="fa fa-edit" alt="Editar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
                if($("#permiso_eliminar").val() == 1)
                html += '<a href="#" onclick="borrar(\'' + serie + '\', \'' + almacen + '\')" title="Desactivar"><i class="fa fa-eraser" alt="Borrar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';

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
            console.log("almacen = ", $("#almacenes").val());
            $('#grid-table').jqGrid('clearGridData')
                .jqGrid('setGridParam', {
                    postData: {
                        criterio: $("#txtCriterio").val(),
                        almacen: $("#almacenes").val(),
                        vacio: $("#select-existencia").val()
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
                        almacen: $("#almacenes").val(),
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

        function borrar(_codigo, _almacen) {
            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    clave: _almacen,
                    action: "inUse"
                },
                beforeSend: function(x) {
                    if (x && x.overrideMimeType) {
                        x.overrideMimeType("application/json;charset=UTF-8");
                    }
                },
                url: '/api/contenedores/update/index.php',
                success: function(data) {
                    console.log("Reload = 3");
                    swal({
                            title: "¿Está seguro que desea Desactivar el pallet o contenedor?",
                            text: "Está a punto de Desactivar un pallet o contenedor, puede volverlo a activar luego",
                            type: "warning",
                            showCancelButton: true,
                            confirmButtonColor: "#DD6B55",
                            confirmButtonText: "Desactivar",
                            cancelButtonText: "Cancelar",
                            closeOnConfirm: true
                        },

                        function() {
                            $.ajax({
                                type: "POST",
                                dataType: "json",
                                data: {
                                    clave_contenedor: _codigo,
                                    action: "delete"
                                },
                                beforeSend: function(x) {
                                    if (x && x.overrideMimeType) {
                                        x.overrideMimeType("application/json;charset=UTF-8");
                                    }
                                },
                                url: '/api/contenedores/update/index.php',
                                success: function(data) {
                                    console.log("Reload = 4");
                                    if (data.success == true) {
                                        //$('#codigo').prop('disabled', true);
                                        ReloadGrid();
                                        ReloadGrid1();
                                    }
                                }
                            });

                        });
                    //}
                }
            });
        }

        function editar(_codigo) {
            console.log("_codigo Editar = ", _codigo);

            $("#hiddenContenedor").val(_codigo);
            $("#_title").html('<h3>Editar Pallets y Contenedores</h3>');
            $("#CodeMessage").html("");
            $("#ClavContenedor").prop('disabled', true);
            $('#TipoContGen').prop('disabled', true);

            $("#myform").validate({
                rules: {
                    cve_almac: {
                        required: true
                    },
                    tipo: {
                        required: true
                    },
                    clave: {
                        required: true,
                        maxlength: 20
                    },
                    descripcion: {
                        required: true,
                        maxlength: 50
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
                    fondo: {
                        required: true,
                        maxlength: 6,
                        number: true
                    },
                    capmax: {
                        required: true,
                        maxlength: 6,
                        number: true
                    },
                    capvol: {
                        required: false,
                        maxlength: 6,
                        number: true
                    }
                }
            });

            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    IDContenedor: _codigo,
                    action: "load"
                },
                beforeSend: function(x) {
                    if (x && x.overrideMimeType) {
                        x.overrideMimeType("application/json;charset=UTF-8");
                    }
                },
                url: '/api/contenedores/update/index.php',
                success: function(data) {
                    console.log("Edit = ", data);
                    console.log("Reload = 5");
                    if (data.success == true) {
                        //$('#codigo').prop('disabled', true);
                        $("#Almacen").val(data.cve_almac);
                        $("#ClavContenedor").val(data.clave_contenedor);
                        $("#descripcion").val(data.descripcion);
                        $("#TipoCont").val(data.tipo);

                        //console.log("data.TipoGenVal", data.TipoGenVal); 
                        //TipoGenVal == 0 --> Genérico
                        //TipoGenVal == 1 --> NO Genérico
                        console.log("data.TipoGenVal = ", data.TipoGenVal);
                        if(data.TipoGenVal == 0) $("#TipoContGen").prop("checked", false); 
                        else if(data.TipoGenVal == "" || data.TipoGenVal == 1) $("#TipoContGen").prop("checked", true);
                        //else if(data.tipoGenVal == "" || data.tipoGenVal == 1) $("#TipoContGen").attr("checked", "checked");

                        $("#anchoCont").val(data.ancho);
                        $("#profCont").val(data.fondo);
                        $("#pesoCont").val(data.peso);
                        $("#altoCont").val(data.alto);
                        $("#pesoMax").val(data.pesomax);
                        $("#capavol").val(data.capavol);
                        $("#hiddenContenedor").val(data.IDContenedor);

                        if ($("#TipoCont").val() == 'Contenedor')
                            $("#capavol").prop('disabled', true);
                        else
                            $("#capavol").prop('disabled', false);

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
            $("#_title").html('<h3>Agregar Pallet y Contenedores</h3>');

            $(':input', '#myform')
                .removeAttr('checked')
                .removeAttr('selected')
                .not(':button, :submit, :reset, :hidden, :radio, :checkbox')
                .val('');

            $('#list').removeAttr('class').attr('class', '');
            $('#list').addClass('animated');
            $('#list').addClass("fadeOutRight");
            $('#list').hide();
            //$('txtCod').select2("val","");

            $('#FORM').show();
            $('#FORM').removeAttr('class').attr('class', '');
            $('#FORM').addClass('animated');
            $('#FORM').addClass("fadeInRight");
            $("#hiddenAction").val("add");
            $('#TipoContGen').prop('disabled', false);

            l.ladda('stop');
            //$('#codigo').prop('disabled', false);
            $("#btnCancel").show();
            $("#CodeMessage").html("");
            $("#ClavContenedor").prop('disabled', false);

            $("#ClavContenedor").html("").val("");
            $("#anchoCont").html("").val("");
            $("#profCont").html("").val("");
            $("#pesoCont").html("").val("");
            $("#altoCont").html("").val("");
            $("#pesoMax").html("").val("");
            $("#capavol").html("").val("");
            $("#hiddenContenedor").val("0");
        }

        var l = $('.ladda-button').ladda();
        l.click(function() {

            /*$("#myform").validate({
                 rules: {
                     cve_almac: {
                         required: true
                     },
					 tipo: {
                         required: true
                     },
					 clave: {
                         required: true,
						 maxlength: 20
                     },
					 descripcion: {
						required: true,
						maxlength: 50
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
                     fondo: {
                         required: true,
                         maxlength: 6,
						 number: true 
                     },
					 capmax: {
                         required: true,
                         maxlength: 6,
						 number: true 
                     },
					 capvol: {
                         required: false,
                         maxlength: 6,
						 number: true 
                     }
                 }
             });*/


            if ($("#Almacen").val() == "") {
                return;
            }
            if ($("#ClavContenedor").val() == "") {
                return;
            }
            if ($("#descripcion").val() == "") {
                return;
            }
            if ($("#anchoCont").val() == "") {
                return;
            }
            if ($("#profCont").val() == "") {
                return;
            }
            if ($("#pesoCont").val() == "") {
                return;
            }
            if ($("#pesoMax").val() == "") {
                return;
            }
            if ($("#altoCont").val() == "") {
                return;
            }
            if ($("#TipoCont").val() == "") {
                return;
            }
            if ($("#capavol").val() == "") {
                return;
            }


            $("#btnCancel").hide();

            l.ladda('start');

            if ($("#hiddenAction").val() == "add") {

                var tipoGenVal = 0;
                if($("#TipoContGen").is(':checked')) tipoGenVal = 1;

                $.post('/api/contenedores/update/index.php', {
                            IDContenedor: $("#hiddenContenedor").val(),
                            cve_almac: $("#Almacen").val(),
                            clave_contenedor: $("#ClavContenedor").val(),
                            descripcion: $("#descripcion").val(),
                            ancho: $("#anchoCont").val(),
                            alto: $("#altoCont").val(),
                            fondo: $("#profCont").val(),
                            peso: $("#pesoCont").val(),
                            pesomax: $("#pesoMax").val(),
                            capavol: $("#capavol").val(),
                            tipo: $("#TipoCont").val(),
                            tipoGen: tipoGenVal,
                            action: $("#hiddenAction").val()
                        },
                        function(response) {
                            console.log("response pallet = ", response);
                        }, "json")
                    .always(function(response) {
                        console.log("always pallet = ", response);
                        cancelar();
                        $("#Almacen").val("");
                        $("#ClavContenedor").val("");
                        $("#TipoCont").val("");
                        l.ladda('stop');
                        $("#btnCancel").show();
                        ReloadGrid();
                    });
            } else {
                var tipoGenVal = 0;
                if($("#TipoContGen").is(':checked')) tipoGenVal = 1;

                $.post('/api/contenedores/update/index.php', {
                            IDContenedor: $("#hiddenContenedor").val(),
                            cve_almac: $("#Almacen").val(),
                            clave_contenedor: $("#ClavContenedor").val(),
                            descripcion: $("#descripcion").val(),
                            ancho: $("#anchoCont").val(),
                            alto: $("#altoCont").val(),
                            fondo: $("#profCont").val(),
                            peso: $("#pesoCont").val(),
                            pesomax: $("#pesoMax").val(),
                            capavol: $("#capavol").val(),
                            tipo: $("#TipoCont").val(),
                            tipoGen: tipoGenVal,
                            action: "edit"
                        },
                        function(response) {
                            console.log("response edit pallet = ", response);
                            window.location.reload();
                        }, "json")
                    .always(function(response) {
                        console.log("always edit pallet = ", response);
                        cancelar();
                        $("#Almacen").val("");
                        $("#ClavContenedor").val("");
                        $("#TipoCont").val("");
                        l.ladda('stop');
                        $("#btnCancel").show();
                        ReloadGrid();
                    });

            }
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

            $("#inactivos").on("click", function() {
                $modal0 = $("#coModal");
                $modal0.modal('show');
            });
            $("div.ui-jqgrid-bdiv").css("max-height", $("#list").height() - 80);
        });
    </script>

    <script>
        $("#txtCorreo").keyup(function(e) {

            var zipCode = $(this).val();
            var regex = /^(([^<>()\[\]\.,;:\s@\"]+(\.[^<>()\[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/;
            var zipCodeRegexp = new RegExp(regex);

            if (zipCodeRegexp.test(zipCode)) {
                $("#emailMessage").html("");
                $("#btnSave").prop('disabled', false);
            } else {
                $("#emailMessage").html("Por favor, ingresar un Correo Electrónico válido");
                $("#btnSave").prop('disabled', true);
            }
        });



        $("#ClavContenedor").keyup(function(e) {

            var claveCode = $(this).val();
            var claveCodeRegexp = new RegExp("^[a-zA-Z0-9]{1,20}$");

            if (claveCodeRegexp.test(claveCode)) {
                $("#CodeMessage").html("");
                $("#btnSave").prop('disabled', false);

                var clave_contenedor = $(this).val();

                $.ajax({
                    type: "POST",
                    dataType: "json",
                    data: {
                        clave_contenedor: clave_contenedor,
                        action: "exists"
                    },
                    beforeSend: function(x) {
                        if (x && x.overrideMimeType) {
                            x.overrideMimeType("application/json;charset=UTF-8");
                        }
                    },
                    url: '/api/contenedores/update/index.php',
                    success: function(data) {
                        console.log("Reload = 6");
                        if (data.success == false) {
                            $("#CodeMessage").html("");
                            $("#btnSave").prop('disabled', false);
                        } else {
                            $("#CodeMessage").html(" Clave de contenedor ya existe");
                            $("#btnSave").prop('disabled', true);
                        }
                    }

                });

            } else {
                $("#CodeMessage").html("Por favor, ingresar una Clave válida");
                $("#btnSave").prop('disabled', true);
            }
        });
    </script>

    <script>
        $('#TipoCont').change(function() {
            if ($("#TipoCont").val() == 'Contenedor') {
                $("#capavol").prop('disabled', true);
                $("#capavol").val(
                    ($("#anchoCont").val() / 1000) *
                    ($("#altoCont").val() / 1000) *
                    ($("#profCont").val() / 1000)
                );
            }

            if ($("#TipoCont").val() == 'Pallet') {
                $("#capavol").prop('disabled', false);
                $("#capavol").val("");
                $("#anchoCont").val("");
                $("#altoCont").val("");
                $("#profCont").val("");
            }
        });

        $("#anchoCont").keyup(function(e) {
            if ($("#TipoCont").val() == 'Contenedor') {
                $("#capavol").val(
                    ($("#anchoCont").val() / 1000) *
                    ($("#altoCont").val() / 1000) *
                    ($("#profCont").val() / 1000)
                );
            }
        });
        $("#altoCont").keyup(function(e) {
            if ($("#TipoCont").val() == 'Contenedor') {
                $("#capavol").val(
                    ($("#anchoCont").val() / 1000) *
                    ($("#altoCont").val() / 1000) *
                    ($("#profCont").val() / 1000)
                );
            }
        });
        $("#profCont").keyup(function(e) {
            if ($("#TipoCont").val() == 'Contenedor') {
                $("#capavol").val(
                    ($("#anchoCont").val() / 1000) *
                    ($("#altoCont").val() / 1000) *
                    ($("#profCont").val() / 1000)
                );
            }
        });

        $("#txtCriterio").keyup(function(event) {
            if (event.keyCode == 13) {
                $("#buscarA").click();
            }
        });

        $("#txtCriterio1").keyup(function(event) {
            if (event.keyCode == 13) {
                $("#buscarR").click();
            }
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

            console.log($("#txtCriterio1").val()); 
            $(grid_selector).jqGrid({
                url: '/api/contenedores/lista/index_i.php',
                datatype: "json",
                height: 250,
                postData: {
                    almacen: $("#almacenes").val(),
                    criterio: $("#txtCriterio1").val()
                },
                mtype: 'POST',
                colNames: ['ID', 'Clave', 'Descripción', 'Almacén', 'Tipo', 'Alto', 'Ancho', 'Fondo', 'Peso', 'Cap. Kgs', 'Cap. Vol', 'Recuperar'],
                colModel: [
                    //{name:'id',index:'id', width:60, sorttype:"int", editable: false},
                    {
                        name: 'IDContenedor',
                        index: 'IDContenedor',
                        width: 80,
                        editable: false,
                        hidden: true,
                        sortable: false
                    }, {
                        name: 'clave_contenedor',
                        index: 'clave_contenedor',
                        width: 80,
                        editable: false,
                        sortable: false
                    }, {
                        name: 'descripcion',
                        index: 'descripcion',
                        width: 80,
                        editable: false,
                        sortable: false
                    }, {
                        name: 'des_almac',
                        index: 'des_almac',
                        width: 60,
                        editable: false,
                        sortable: false
                    }, {
                        name: 'tipo',
                        index: 'tipo',
                        width: 40,
                        editable: false,
                        sortable: false
                    }, {
                        name: 'alto',
                        index: 'alto',
                        width: 25,
                        editable: false,
                        sortable: false
                    }, {
                        name: 'ancho',
                        index: 'ancho',
                        width: 25,
                        editable: false,
                        sortable: false
                    }, {
                        name: 'fondo',
                        index: 'fondo',
                        width: 25,
                        editable: false,
                        sortable: false
                    }, {
                        name: 'peso',
                        index: 'peso',
                        width: 25,
                        editable: false,
                        sortable: false
                    }, {
                        name: 'pesomax',
                        index: 'pesomax',
                        width: 25,
                        editable: false,
                        sortable: false
                    }, {
                        name: 'capavol',
                        index: 'capavol',
                        width: 25,
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
                rowList: [10, 20, 30],
                pager: pager_selector,
                sortname: 'IDContenedor',
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

                var IDContenedor = rowObject[0];

                $("#hiddenContenedor").val(IDContenedor);
                // la funcion downloadxml ejecuta el index.php que esta en la carpeta x pasandole la serie y el correlativo via get
                // la funcion viewPdf ejecuta el index.php que esta en la carpeta v pasandole la serie y el correlativo via get

                var html = '';
                //html += '<a href="#" onclick="ver(\''+serie+'\')"><i class="fa fa-eye" alt="Ver"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
                html += '<a href="#" onclick="recovery(\'' + IDContenedor + '\')"><i class="fa fa-check" alt="Borrar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';

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
                        console.log("Reload = 7");
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
                    IDContenedor: _codigo,
                    action: "recovery"
                },
                beforeSend: function(x) {
                    if (x && x.overrideMimeType) {
                        x.overrideMimeType("application/json;charset=UTF-8");
                    }
                },
                url: '/api/contenedores/update/index.php',
                success: function(data) {
                    console.log("Reload = 1");
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