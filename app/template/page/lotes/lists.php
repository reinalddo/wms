<?php
$listaArtic = new \Articulos\Articulos();
$almacenes = new \AlmacenP\AlmacenP();

$mod=29;
$var1=73;
$var2=74;
$var3=75;
$var4=76;

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
    <link href="/css/plugins/chosen/chosen.css" rel="stylesheet">
    <link href="/css/plugins/datapicker/datepicker3.css" rel="stylesheet">


    <!-- Mainly scripts -->
    <script src="/js/jquery-2.1.1.js"></script>
    <script src="/js/bootstrap.min.js"></script>
    <script src="/js/plugins/metisMenu/jquery.metisMenu.js"></script>
    <script src="/js/plugins/slimscroll/jquery.slimscroll.min.js"></script>
    <link href="/css/plugins/sweetalert/sweetalert.css" rel="stylesheet">
    <script src="/js/moment.js"></script>
    <script src="/js/moment-with-locales.js"></script>
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
    <script src="/js/plugins/chosen/chosen.jquery.js"></script>
    <!-- Data picker -->
    <script src="/js/plugins/datapicker/bootstrap-datepicker.js"></script>

    <!-- Mainly scripts -->


    <div class="modal inmodal" id="myModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content animated bounceInRight">
                <div class="modal-header" id="modaltitle">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                    <i class="fa fa-laptop modal-icon"></i>
                    <h4 class="modal-title">Agregar Lote</h4>
                </div>
                <form id="myform">
                    <div class="modal-body">
                        <div class="form-group" id="Articul">
                            <label>Clave *</label>
                            <input id="cve_lote_articulo" type="text" placeholder="Clave de Lote" class="form-control" required="true"><label id="CodeMessage" style="color:red;"></label>
                            <br>
                            <label>Artículos  *</label>
                            <select id="cve_articulo" class="chosen-select form-control" required="true">
                        <option value="">Seleccione Artículo</option>
                        <?php foreach( $listaArtic->getConLotes() AS $a ): ?>
                            <option value="<?php echo $a->cve_articulo; ?>"><?php echo "( ".$a->cve_articulo." ) ".$a->des_articulo; ?></option>
                        <?php endforeach; ?>
                    </select>
                        </div>
                        <div class="form-group" id="fechaDeCaducidad_div">
                            <label>Fecha de Caducidad  *</label>
                            <div class="input-group date" id="data_1">
                                <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input id="fechacaducidad" type="text" class="form-control" value="" required="true"><label id="msg_caducidad" style="color:red;"></label>
                            </div>
                        </div>
                        <input type="hidden" id="hiddenAction">
                        <input type="hidden" id="hiddenid_lote">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-white" data-dismiss="modal" id="btnCancel">Cerrar</button>
                        <button type="button" class="btn btn-primary ladda-button" id="btnSave">Guardar</button>
                    </div>
                </form>
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
                        <h4 class="modal-title">Recuperar Lote</h4>
                    </div>
                    <div class="modal-body">
                        <div class="col-md-4">
                            <div class="input-group">
                                <input type="text" class="form-control input-sm" name="txtCriterio" id="txtCriterio1" placeholder="Buscar por Proveedor...">
                                <div class="input-group-btn">
                                    <a href="#" onclick="ReloadGrid1()">
                                        <button type="submit" id="buscarR" class="btn btn-sm btn-primary">
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

    <div class="wrapper wrapper-content  animated fadeInRight">
      <h3>Lotes</h3>
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
                            <div class="form-group col-md-4">
                                <label>Caducidad Desde</label>
                                <div class="input-group date" id="data_2">
                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input id="fechai" type="text" class="form-control">
                                </div>
                            </div>


                            <div class="form-group col-md-4">
                                <label>Caducidad Hasta</label>
                                <div class="input-group date" id="data_3">
                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input id="fechaf" type="text" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="row">

                            <div class="col-md-4">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="txtCriterio" id="txtCriterio" placeholder="Buscar...">
                                    <div class="input-group-btn">
                                    
                                        <button onclick="ReloadGrid()" type="submit" class="btn btn-primary" id="buscarL">
                                            <span class="fa fa-search"></span> Buscar
                                        </button>
                                        
                                    </div>
                                </div>

                            </div>
                            <div class="col-md-8" style="margin-top:10px">
                                
                                <a href="/api/v2/lotes/exportar" target="_blank" class="btn btn-primary pull-right" style="margin-left:15px"><span class="fa fa-upload"></span> Exportar</a>
                                <button class="btn btn-primary pull-right permiso_registrar" style="margin-left:15px" data-toggle="modal" data-target="#importar"><span class="fa fa-download"></span> Importar</button>


                                <button class="btn btn-primary pull-right" type="button" style="margin-left:10px;" id="inactivos"><i class="fa fa-search"></i> Lotes inactivos</button>
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


    <div class="modal fade" id="importar" role="dialog">
        <div class="vertical-alignment-helper">
            <div class="modal-dialog vertical-align-center">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Importar Lotes</h4>
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
                        <button id="btn-layout" type="button" class="btn btn-primary">Descargar Layout</button>
                      </div>
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
$('#btn-layout').on('click', function(e) {
    //e.preventDefault();  //stop the browser from following
    //window.location.href = 'http://www.tinegocios.com/proyectos/wms/Layout_Articulos_Importador.xlsx';
    window.location.href = '/Layout/Layout_Lotes.xlsx';
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
            url: '/lotes/importar',
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
                console.log("DATA = ", data);
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

function exportar(){
    $.ajax(
        {
            type: "POST",
            dataType: "json",
            url: '/api/v2/lotes/exportar',
            done: function (data) {
                var a = document.createElement('a');
                var url = window.URL.createObjectURL(data);
                a.href = url;
                a.download = 'myfile.pdf';
                a.click();
                window.URL.revokeObjectURL(url);
            }
        }
    );
}

</script>




    <script type="text/javascript">
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

        /**
         * @author Ricardo Delgado.
         * Busca y selecciona el almacen predeterminado para el usuario.
         */
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
                        }, 1500);
                    }
                },
                error: function(res) {
                    window.console.log(res);
                }
            });
        }
        almacenPrede();

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
                url: '/api/lotes/lista/index.php',
                datatype: "json",
                shrinkToFit: false,
                height: 'auto',
                postData: {
                    criterio: $("#txtCriterio").val()
                },
                mtype: 'POST',
                colNames: ['id', "Acciones", 'Clave de Artículo', 'Descripción', 'Lote', 'Caducidad'],
                colModel: [{
                    name: 'id',
                    index: 'id',
                    width: 60,
                    sorttype: "int",
                    editable: false,
                    hidden: true
                }, {
                    name: 'myac',
                    index: '',
                    width: 100,
                    fixed: true,
                    sortable: false,
                    resize: false,
                    formatter: imageFormat
                }, {
                    name: 'cve_articulo',
                    index: 'LOTE',
                    width: 150,
                    editable: false,
                    sortable: false
                }, {
                    name: 'des_articulo',
                    index: 'des_articulo',
                    width: 400,
                    editable: false,
                    sortable: false
                }, {
                    name: 'LOTE',
                    index: 'LOTE',
                    width: 150,
                    editable: false,
                    sortable: false
                }, {
                    name: 'CADUCIDAD',
                    index: 'CADUCIDAD',
                    width: 150,
                    editable: false,
                    sortable: false
                }, ],
                rowNum: 30,
                rowList: [30, 40, 50],
                pager: pager_selector,
                sortname: 'cve_articulo',
                viewrecords: true,
                sortorder: "desc",
                loadComplete: function(data){console.log("SUCCESS: ", data);}
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
                var serie = rowObject[0];
                var correl = rowObject[2];
                var aplica_caducidad = rowObject[4];
                var url = "x/?serie=" + serie + "&correl=" + correl;
                var url2 = "v/?serie=" + serie + "&correl=" + correl;
                $("#hiddenid_lote").val(serie);
                // la funcion downloadxml ejecuta el index.php que esta en la carpeta x pasandole la serie y el correlativo via get
                // la funcion viewPdf ejecuta el index.php que esta en la carpeta v pasandole la serie y el correlativo via get

                var html = '';
                //html += '<a href="#" onclick="ver(\''+serie+'\')"><i class="fa fa-eye" alt="Ver"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
                if($("#permiso_editar").val() == 1)
                html += '<a href="#" onclick="editar(\'' + serie + '\', \'' + correl + '\', \'' + aplica_caducidad + '\')"><i class="fa fa-edit" alt="Editar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
                if($("#permiso_eliminar").val() == 1)
                html += '<a href="#" onclick="borrar(\'' + serie + '\', \'' + correl + '\')"><i class="fa fa-eraser" alt="Borrar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';

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
                        fechaInicio: $("#fechai").val(),
                        fechaFin: $("#fechaf").val(),
                        almacen: $("#almacenes").val()
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

        function borrar(_codigo, _correl) {
            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    cve_articulo: _codigo,
                    action: "inUse"
                },
                beforeSend: function(x) {
                    if (x && x.overrideMimeType) {
                        x.overrideMimeType("application/json;charset=UTF-8");
                    }
                },
                url: '/api/lotes/update/index.php',
                success: function(data) {
                    if (data.success == true) {
                        swal(
                            '¡Alerta!',
                            'El lote de articulos esta siendo usado en este momento',
                            'warning'
                        );
                    } else {
                        swal({
                                title: "¿Está seguro que desea borrar el lote de articulos?",
                                text: "Está a punto de borrar un lote de articulos y esta acción no se puede deshacer",
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
                                        LOTE: _codigo,
                                        cve_articulo: _correl,
                                        action: "delete"
                                    },
                                    beforeSend: function(x) {
                                        if (x && x.overrideMimeType) {
                                            x.overrideMimeType("application/json;charset=UTF-8");
                                        }
                                    },
                                    url: '/api/lotes/update/index.php',
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

        function editar(_codigo, _correl, aplica_caducidad) 
        {
          $("#fechaDeCaducidad_div").show();
          if(aplica_caducidad == 'No Aplica')
          {
            $("#fechacaducidad").prop('required',false);
            $("#fechacaducidad").val("No Aplica");
            $("#fechaDeCaducidad_div").hide();
          }
          $("#hiddenid_lote").val(_codigo);
          $("#modaltitle").html('<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button><i class="fa fa-laptop modal-icon"></i><h4 class="modal-title">Editar Lote</h4>');
          $.ajax({
            type: "POST",
            dataType: "json",
            data: {
              id_lote: _codigo,
              cve_articulo: _correl,
              action: "load"
            },
            beforeSend: function(x) {if (x && x.overrideMimeType) {x.overrideMimeType("application/json;charset=UTF-8");}},
            url: '/api/lotes/update/index.php',
            success: function(data) {
              if (data.success == true) {
                $("#cve_articulo").prop("disabled", true);
                $("#cve_lote_articulo").prop("disabled", true);
                $("#cve_articulo").val(data.cve_articulo);
                $("#cve_articulo").trigger("chosen:updated");
                $("#cve_lote_articulo").val(data.LOTE);
                $("#fechacaducidad").val(data.CADUCIDAD);
                l.ladda('stop');
                $("#btnCancel").show();
                $modal0 = $("#myModal");
                $modal0.modal('show');
                $("#hiddenAction").val("edit");
                
              }
            }
          });
        }
      
        $("#cve_articulo").change(function() {
          var cve_art = $("#cve_articulo").val();
          //alert("Se buscara si es con o sin caducidad");
          $.ajax({
            type: "POST",
            dataType: "json",
            url: '/api/lotes/update/index.php',
            data: {
              action: "maneja_caducidad",
              cve_articulo: cve_art
            },
            beforeSend: function(x) {if (x && x.overrideMimeType) {x.overrideMimeType("application/json;charset=UTF-8");}},
            success: function(data) {
              if (data.success == true) 
              {
                if(data.respuesta == 'N')
                {
                  $("#fechacaducidad").prop('required',false);
                  $("#fechacaducidad").val("");
                  $("#fechaDeCaducidad_div").hide();
                }
                else
                {
                  $("#fechacaducidad").prop('required',true);
                  $("#fechaDeCaducidad_div").show();
                  $("#fechacaducidad").val("");
                }
              }
            }
          });
        });

        function agregar() 
        {
          $("#modaltitle").html('<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button><i class="fa fa-laptop modal-icon"></i><h4 class="modal-title">Agregar Lote</h4>');
          $modal0 = $("#myModal");
          $modal0.modal('show');
          l.ladda('stop');
          //$('#codigo').prop('disabled', false);
          $("#hiddenAction").val("add");
          $("#btnCancel").show();
          $("#cve_lote_articulo").prop("disabled", false);
          $("#cve_articulo").prop("disabled", false);

          $("#cve_articulo").val("");
          $("#cve_lote_articulo").val("");
          $("#CADUCIDAD").val("");
        }

        var l = $('.ladda-button').ladda();
        l.click(function() 
        {
          if ($("#cve_lote_articulo").val() == "") 
          {
            return;
          }
          if ($("#cve_articulo").val() == "") 
          {
            return;
          }
          if($("#fechaDeCaducidad_div").is(":visible") == true)
          {
            if($("#fechacaducidad").val() == "")
            {
              return;
            }
          }

          $("#btnCancel").hide();

          l.ladda('start');

          console.log("OK SAVE");

            console.log("id_lote:", $("#hiddenid_lote").val());
            console.log("nuevo_lote:", $("#cve_lote_articulo").val());
            console.log("cve_articulo:", $("#cve_articulo").val());
            console.log("CADUCIDAD:", $("#fechacaducidad").val());
            console.log("action:", $("#hiddenAction").val());

          $.post('/api/lotes/update/index.php', 
          {
            id_lote: $("#hiddenid_lote").val(),
            nuevo_lote: $("#cve_lote_articulo").val(),
            cve_articulo: $("#cve_articulo").val(),
            CADUCIDAD: $("#fechacaducidad").val(),
            action: $("#hiddenAction").val()
          },
          function(response) {
            console.log(response);
          }, "json")
          .always(function() {
            $("#des_tipcia").val("");
            l.ladda('stop');
            $("#btnCancel").show();
            $modal0.modal('hide');
            ReloadGrid();
          });
        });

        $('#data_1').datetimepicker({
            locale: 'es',
            format: 'DD-MM-YYYY',
            useCurrent: false
        });
        $('#data_1 .input-group.date').datepicker({
            todayBtn: "linked",
            keyboardNavigation: false,
            forceParse: false,
            calendarWeeks: true,
            autoclose: true,
            format: 'dd-mm-yyyy',
            'default': 'now',
        });


        $('#data_2').datetimepicker({
            locale: 'es',
            format: 'DD-MM-YYYY',
            useCurrent: false
        });
        $('#data_2 .input-group.date').datepicker({
            todayBtn: "linked",
            keyboardNavigation: false,
            forceParse: false,
            calendarWeeks: true,
            autoclose: true,
            format: 'dd-mm-yyyy',
            'default': 'now',
        });

        $('#data_3').datetimepicker({
            locale: 'es',
            format: 'DD-MM-YYYY',
            useCurrent: false
        });
        $('#data_3 .input-group.date').datepicker({
            todayBtn: "linked",
            keyboardNavigation: false,
            forceParse: false,
            calendarWeeks: true,
            autoclose: true,
            format: 'dd-mm-yyyy',
            'default': 'now',
        });




        //enable datepicker
        function pickDate(cellvalue, options, cell) {
            setTimeout(function() {
                $(cell).find('input[type=text]')
                    .datepicker({
                        format: 'dd-mm-yyyy',
                        autoclose: true
                    });
            }, 0);
        }
    </script>

    <script>
        $("#cve_lote_articulo").keyup(function(e) {

            var claveCode = $(this).val();
            var claveCodeRegexp = new RegExp("^[a-zA-Z0-9-]{1,30}$");

            if (claveCodeRegexp.test(claveCode)) {
                $("#CodeMessage").html("");
                $("#btnSave").prop('disabled', false);

                var cve_lote_articulo = $(this).val();

                $.ajax({
                    type: "POST",
                    dataType: "json",
                    data: {
                        LOTE: cve_lote_articulo,
                        action: "exists"
                    },
                    beforeSend: function(x) {
                        if (x && x.overrideMimeType) {
                            x.overrideMimeType("application/json;charset=UTF-8");
                        }
                    },
                    url: '/api/lotes/update/index.php',
                    success: function(data) {
                        if (data.success == false) {
                            $("#CodeMessage").html("");
                            $("#btnSave").prop('disabled', false);
                        } else {
                            $("#CodeMessage").html("Clave de lote ya existe");
                            $("#btnSave").prop('disabled', true);
                        }
                    }

                });

            } else {
                $("#CodeMessage").html("Por favor, ingresar una Clave de lote válida");
                $("#btnSave").prop('disabled', true);
            }
        });

        $("#cve_lote_articulo").keyup(function(e) {

            var claveCode = $(this).val();
            var claveCodeRegexp = new RegExp("^[a-zA-Z0-9-]{1,30}$");

            if (claveCodeRegexp.test(claveCode)) {
                $("#CodeMessage").html("");
                $("#btnSave").prop('disabled', false);

            } else {
                $("#CodeMessage").html("Por favor, ingresar una Clave de lote válida");
                $("#btnSave").prop('disabled', true);
            }
        });

        $("#txtCriterio").keyup(function(event) {
            if (event.keyCode == 13) {
                $("#buscarL").click();
            }
        });

        $("#txtCriterio1").keyup(function(event) {
            if (event.keyCode == 13) {
                $("#buscarR").click();
            }
        });
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
                ReloadGrid1();
            });
            $("div.ui-jqgrid-bdiv").css("max-height", $("#list").height() - 80);
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
                url: '/api/lotes/lista/index_i.php',
                datatype: "json",
                height: 250,
                postData: {
                    criterio: $("#txtCriterio").val()
                },
                mtype: 'POST',
                colNames: ['id', 'Clave de Lote', 'Clave de Artículo', 'Artículo', 'Caducidad', "Recuperar"],
                colModel: [{
                    name: 'id',
                    index: 'id',
                    width: 60,
                    sorttype: "int",
                    editable: false,
                    hidden: true
                }, {
                    name: 'LOTE',
                    index: 'LOTE',
                    width: 150,
                    editable: false,
                    sortable: false
                }, {
                    name: 'cve_articulo',
                    index: 'LOTE',
                    width: 150,
                    editable: false,
                    sortable: false
                }, {
                    name: 'des_articulo',
                    index: 'des_articulo',
                    width: 400,
                    editable: false,
                    sortable: false
                }, {
                    name: 'CADUCIDAD',
                    index: 'CADUCIDAD',
                    width: 150,
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
                sortname: 'cve_articulo',
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
                var id = rowObject[2];
                var lote = rowObject[1];

                // la funcion downloadxml ejecuta el index.php que esta en la carpeta x pasandole la serie y el correlativo via get
                // la funcion viewPdf ejecuta el index.php que esta en la carpeta v pasandole la serie y el correlativo via get

                var html = '';
                //html += '<a href="#" onclick="ver(\''+serie+'\')"><i class="fa fa-eye" alt="Ver"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
                html += '<a href="#" onclick="recovery(\'' + id + '\', \'' + lote + '\')"><i class="fa fa-check" alt="Recuperar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';

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
        function recovery(_codigo, _correl) {
            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    cve_articulo: _codigo,
                    LOTE: _correl,
                    action: "recovery"
                },
                beforeSend: function(x) {
                    if (x && x.overrideMimeType) {
                        x.overrideMimeType("application/json;charset=UTF-8");
                    }
                },
                url: '/api/lotes/update/index.php',
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