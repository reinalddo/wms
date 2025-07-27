<?php
include_once  $_SERVER['DOCUMENT_ROOT']."/app/host.php";
$listaAlm = new \AlmacenP\AlmacenP();
$listaGrup = new \GrupoArticulos\GrupoArticulos();
$listaMed = new \UnidadesMedida\UnidadesMedida();
$listaProv = new \Proveedores\Proveedores();
$listaTipcaja = new \TipoCaja\TipoCaja();
$listaSubgp = new \SubGrupoArticulos\SubGrupoArticulos();
$listaSubsub = new \SSubGrupoArticulos\SSubGrupoArticulos();
$almacenes = new \AlmacenP\AlmacenP();


$vere = \db()->prepare("SELECT * FROM t_profiles AS a WHERE id_menu=179 and id_submenu=687 and id_role='".$_SESSION["perfil_usuario"]."'");
$vere->execute();
$ver = $vere->fetchAll(PDO::FETCH_ASSOC);


$agre = \db()->prepare("SELECT * FROM t_profiles AS a WHERE id_menu=179 and id_submenu=688 and id_role='".$_SESSION["perfil_usuario"]."'");
$agre->execute();
$ag = $agre->fetchAll(PDO::FETCH_ASSOC);


$edita = \db()->prepare("SELECT * FROM t_profiles AS a WHERE id_menu=179 and id_submenu=689 and id_role='".$_SESSION["perfil_usuario"]."'");
$edita->execute();
$edit = $edita->fetchAll(PDO::FETCH_ASSOC);


$borra = \db()->prepare("SELECT * FROM t_profiles AS a WHERE id_menu=179 and id_submenu=689 and id_role='".$_SESSION["perfil_usuario"]."'");
$borra->execute();
$borrar = $borra->fetchAll(PDO::FETCH_ASSOC);

?>
    <link href="/css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">
    <link href="/css/plugins/blueimp/css/blueimp-gallery.min.css" rel="stylesheet">
    <link href="/css/plugins/chosen/chosen.css" rel="stylesheet">
    <link href="/css/plugins/imgloader/fileinput.min.css" rel="stylesheet">


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

    <script src="/js/plugins/ladda/spin.min.js"></script>
    <script src="/js/plugins/ladda/ladda.min.js"></script>
    <script src="/js/plugins/ladda/ladda.jquery.min.js"></script>
    <script src="/js/plugins/sweetalert/sweetalert.min.js"></script>
    <!-- Select -->
    <script src="/js/plugins/chosen/chosen.jquery.js"></script>

    <script src="/js/plugins/imgloader/fileinput.min.js"></script>
    <script src="/js/plugins/imgloader/locales/es.js"></script>
    <!-- Jquery Validate -->
    <script src="/js/plugins/validate/jquery.validate.min.js"></script>

    <script src="/js/plugins/blueimp/jquery.blueimp-gallery.min.js"></script>

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
       ul.dropdown-menu.dropdown-menu-right {
            position: absolute;
            left: auto;
            right: 0;
        }
    </style>

    <style>
        #list {
            width: 100%;
            height: 100%;
            position: relative;
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
        
        .ui-jqgrid-bdiv {
            height: 100%;
            width: 100% !important;
            max-width: 100% !important;
        }
    </style>



<div class="wrapper wrapper-content  animated fadeInRight" id="arti">
    <h3>Presupuestos</h3>
    <div class="row">
        <div class="col-md-12">
            <div class="ibox ">
                <div class="ibox-title">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Almacén</label>
                                <select class="form-control" id="almacenes" name="almacenes">
                                    <option value="">Seleccione un Almacen</option>
                                    <?php foreach( $almacenes->getAll() AS $a ): ?>
                                    <option value="<?php echo $a->id; ?>" <?php if($a->clave==$_GET["almacen"]) echo "selected";?>>
                                    <?php echo "($a->clave) $a->nombre"; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3" >
                            <div class="input-group">
                                <label>&nbsp; </label>
                                <input type="text" class="form-control" name="txtCriterio" id="txtCriterio" placeholder="Buscar...">
                                <div class="input-group-btn">
                                    <button onclick="ReloadGrid()" style="margin-top: 22px" type="submit" class="btn btn-primary" id="buscarA">
                                        <span class="fa fa-search"></span>  Buscar
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <a href="/api/v2/presupuestos/exportar" target="_blank" class="btn btn-primary pull-right" style="margin-left:15px; ; margin-top: 22px">
                            <span class="fa fa-upload"></span> Exportar</a>
                            <button class="btn btn-primary pull-right" style="margin-left:15px; ; margin-top: 22px" data-toggle="modal" data-target="#importar">
                            <span class="fa fa-download"></span> Importar</button>
                            <?php if($ag[0]['Activo']==1){?>
                            <button onclick="agregar()" class="btn btn-primary pull-right"  type="button" style="margin-top: 22px; margin-left:10px; ">
                                <i class="fa fa-plus"></i> Nuevo
                            </button>
                            <?php }?>
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

<!-- Modal Importar-->
<div class="modal fade" id="importar" role="dialog">
    <div class="vertical-alignment-helper">
        <div class="modal-dialog vertical-align-center">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Importar Artículos</h4>
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
                    <div class="col-md-6" style="text-align: right">
                        <button id="btn-import" type="button" class="btn btn-primary">Importar</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ToDo: cambiar este Modal detalle por detalle de presupuesto-->
<!-- Modal Detalle Articulo-->
<div class="modal inmodal" id="modalVer" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content animated bounceInRight">
            <div class="modal-header" id="modaltitle2">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <i class="fa fa-laptop modal-icon"></i>
                <h4 class="modal-title">Detalle del Artículo</h4>
                <h3 class="modal-subtitle"></h3>
            </div>
            <div class="col-md-6 b-r">
                </br>
                <label>Clave Interna (Pza)</label> <input id="codigo_ver" name="codigo_ver" type="text" placeholder="" class="form-control" disabled>
                <label>Código de Barras (Pza)</label> <input id="cve_codprov_ver" name="cve_codprov_ver" type="text" placeholder="" class="form-control" disabled>
                <label>Descripción</label> <input id="des_articulo_ver" name="des_articulo_ver" type="text" placeholder="" class="form-control" disabled>
                <label>Peso Unitario (Kg)</label> <input id="peso_ver" name="peso_ver" type="text" placeholder="" class="form-control" disabled>
                <label>Código de Barras (Caja)</label> <input id="barras2_ver" name="barras2_ver" type="text" placeholder="" class="form-control" disabled>
                <label>Unidades por Caja</label> <input oninput="this.value = this.value.replace(/[^0-9]/g, '');" id="num_multiplo_ver" name="num_multiplo_ver" type="text" placeholder="" class="form-control" disabled>
            </div>
            <div class="col-md-6">
                </br>
                <label>Código de Barras (Pallet)</label> <input id="barras3_ver" name="barras3_ver" type="text" placeholder="" class="form-control" disabled>
                <label>Cajas por Pallet</label> <input id="cajas_palet_ver" name="cajas_palet_ver" type="text" placeholder="" class="form-control" oninput="this.value = this.value.replace(/[^0-9]/g, '');" disabled>
                <label>Grupo</label> <input id="grupo_ver" name="grupo_ver" type="text" placeholder="" class="form-control" disabled>
                <label>Clasificación</label> <input id="clasificacion_ver" name="clasificacion_ver" type="text" placeholder="" class="form-control" disabled>
                <label>Tipo</label> <input id="tipo_ver" name="tipo_ver" type="text" placeholder="" class="form-control" disabled>
            </div>
            <div class="col-md-12">
                <div class="lightBoxGallery">
                    <h4>Imagenes Actuales</h4>
                    <div id="upload2">
                    </div>
                    <div id="blueimp-gallery" class="blueimp-gallery">
                        <div class="slides"></div>
                        <h3 class="title"></h3>
                        <a class="prev">‹</a>
                        <a class="next">›</a>
                        <a class="close">×</a>
                        <a class="play-pause"></a>
                        <ol class="indicator"></ol>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <button type="button" class="btn btn-white" data-dismiss="modal" id="btnCancel">Cerrar</button>
            </div>
            <div class="modal-footer">
            </div>
        </div>
    </div>
</div>


<!-- Wrapper de añadir presupuesto-->
<div class="wrapper wrapper-content  animated fadeInRight" id="FORM" style="display: none">
    <div class="row">
        <div class="col-md-12">
            <div class="ibox ">
                <div class="ibox-title">
                    <div class="row">
                        <div class="col-md-4" id="_title">
                            <h3>Presupuesto</h3>
                        </div>
                    </div>
                </div>
                <div class="ibox-content">
                    <form id="myform" role="form">
                        <div class="row">
                            <div class="col-md-6 b-r">
                                <div class="form-group"><label>Nombre del Presupuesto *</label> <input id="nombre_presupuesto" name="nombre_presupuesto" type="text" placeholder="Presupuesto" class="form-control" maxlength="30" required="true"></div>
                                <div class="form-group"><label>Año del Presupuesto *</label> <input id="ano_presupuesto" name="ano_presupuesto" type="text" placeholder="Año" class="form-control" maxlength="30" required="true"><label id="CodeMessage" style="color:red;"></label></div>
                                <div class="form-group"><label>Clave de Partida *</label> <input id="cve_partida" name="cve_partida" type="text" placeholder="Partida" class="form-control" maxlength="30" required="true"><label id="CodeMessage2" style="color:red;"></label></div>
                            </div>
                            <div class="col-md-6 b-r">
                                <div class="form-group"><label>Concepto de Partida *</label> <input id="concepto_partida" name="concepto_partida" type="text" placeholder="Concepto" class="form-control" maxlength="30" required="true"><label id="CodeMessage3" style="color:red;"></label></div>
                                <div class="form-group"><label>Monto *</label> <input id="monto_presupuesto" name="monto_presupuesto" type="text" placeholder="Monto" class="form-control" maxlength="30" required="true"><label id="CodeMessage4" style="color:red;"></div>
                                <div class="pull-right" style="clear: both;">
                                    <a href="#" onclick="cancelar()"><button type="button" class="btn btn-white" id="btnCancel">Cerrar</button></a>&nbsp;&nbsp;
                                    <button type="submit" class="btn btn-primary ladda-button" data-style="contract" id="btnSave">Guardar</button>
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
      
<div class="modal inmodal" id="modalEditarPresupuesto" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content animated bounceInRight">
            <div class="modal-header" id="modaltitle">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <i class="fa fa-laptop modal-icon"></i>
                <h4 class="modal-title">Editar Presupuesto</h4>
            </div>
            <form id="myform">
            <div class="modal-body">
                <label>Nombre del Presupuesto *</label> <input id="new_nombre_presupuesto" name="new_nombre_presupuesto" type="text" placeholder="Presupuesto" class="form-control" maxlength="30" required="true">
                <label>Año del Presupuesto *</label> <input id="new_ano_presupuesto" name="new_ano_presupuesto" type="text" placeholder="Año" class="form-control" maxlength="30" required="true"><label id="CodeMessage" style="color:red;"></label>
                <label>Clave de Partida *</label> <input id="new_cve_partida" name="new_cve_partida" type="text" placeholder="Partida" class="form-control" maxlength="30" required="true"><label id="CodeMessage2" style="color:red;"></label>
                <label>Concepto de Partida *</label> <input id="new_concepto_partida" name="new_concepto_partida" type="text" placeholder="Concepto" class="form-control" maxlength="30" required="true"><label id="CodeMessage3" style="color:red;"></label>
                <label>Monto *</label> <input id="new_monto_presupuesto" name="new_monto_presupuesto" type="text" placeholder="Monto" class="form-control" maxlength="30" required="true"><label id="CodeMessage4" style="color:red;"></label>
                <input name="new_hiddenAction" id="new_hiddenAction" type="hidden">
                <input name="new_hiddenID" id="new_hiddenID" type="hidden">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-white" data-dismiss="modal" id="new_btnCancel">Cerrar</button>
                <button type="submit" class="btn btn-primary ladda-button-2" data-style="contract" id="btnSave">Guardar</button>
            </div>
            </form>
        </div>
    </div>
</div>


<script>
$('#btn-layout').on('click', function(e) {
  e.preventDefault();  //stop the browser from following
  window.location.href = 'http://www.tinegocios.com/proyectos/wms/Layout_Presupuestos.xlsx';
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
        url: '/presupuestos/importar',
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
        // initialize with defaults
        $("#input-2").fileinput({
            language: 'es',
            maxFileCount: 5,
            allowedFileExtensions: ["jpg", "png"]
        });

        /**
         * @author Ricardo Delgado.
         * Busca y selecciona el almacen predeterminado para el usuario.
         */
        /*function almacenPrede() {
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
        }*/

        // with plugin options
        $("#input-2").fileinput({
            'showUpload': false,
            'previewFileType': 'any'
        });


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

            //$("#txtCriterio").val()
            $(grid_selector).jqGrid({
                url: '/api/presupuestos/lista/index.php',
                datatype: "json",
                shrinkToFit: true,
                height: 'auto',
                mtype: 'POST',
                postData: {
                    criterio: $("#txtCriterio").val()
                },
                colNames: ['ID',
                    'Nombre del Presupuesto',
                    'Año del Presupuesto',
                    'Partida',
                    'Concepto',
                    'Monto',
                    'Acciones'
                ],

                colModel: [
                    {
                        name: 'id',
                        index: 'id',
                        width: 100,
                        editable: false,
                        hidden: true,
                        sortable: false,
                        resizable: false
                    }, {
                        name: 'nombreDePresupuesto',
                        index: 'nombreDePresupuesto',
                        width: 260,
                        editable: false,
                        sortable: false,
                        resizable: false
                    }, {
                        name: 'anoDePresupuesto',
                        index: 'anoDePresupuesto',
                        width: 150,
                        editable: false,
                        sortable: false,
                        resizable: false,
                        align:'center'
                    }, {
                        name: 'claveDePartida',
                        index: 'claveDePartida',
                        width: 130,
                        editable: false,
                        sortable: false,
                        resizable: false,
                        align:'center'
                    }, {
                        name: 'conceptoDePartida',
                        index: 'conceptoDePartida',
                        width: 270,
                        editable: false,
                        sortable: false,
                        resizable: false,
                        align:'center'
                    }, {
                        name: 'monto',
                        index: 'monto',
                        width: 150,
                        editable: false,
                        sortable: false,
                        resizable: false,
                        align:'center'
                    },{
                        name: 'myac',
                        index: '',
                        width: 80,
                        fixed: true,
                        sortable: false,
                        resize: false,
                        formatter: imageFormat,
                        frozen: true
                    }, ],
                    rowNum: 30,
                    rowList: [30, 40, 50],
                    pager: pager_selector,
                    sortname: 'id',
                    viewrecords: true,
                    sortorder: "desc",
                    //loadComplete: almacenPrede()
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
                var correl = rowObject[4];
                var url = "x/?serie=" + serie + "&correl=" + correl;
                var url2 = "v/?serie=" + serie + "&correl=" + correl;

                // la funcion downloadxml ejecuta el index.php que esta en la carpeta x pasandole la serie y el correlativo via get
                // la funcion viewPdf ejecuta el index.php que esta en la carpeta v pasandole la serie y el correlativo via get

                var html = '';
                //html += '<a href="#" onclick="ver(\''+serie+'\')"><i class="fa fa-eye" alt="Ver"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
                html += '<a href="#" onclick="editar(\'' + serie + '\')"><i class="fa fa-edit" alt="Editar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
                html += '<a href="#" onclick="borrar(\'' + serie + '\')"><i class="fa fa-eraser" alt="Borrar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
                html += '<a href="#" onclick="ver(\'' + serie + '\')"><i class="fa fa-search" alt="Ver"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';

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
        //////////////////////////////////////////////////////////Aqui termina del Grid/////////////////////////////////////////////////////////////
      
      
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
                    id: _codigo,
                    action: "existeEnUbicacion"
                },
                beforeSend: function(x) {
                    if (x && x.overrideMimeType) {
                        x.overrideMimeType("application/json;charset=UTF-8");
                    }
                },
                url: '/api/presupuestos/update/index.php',
                success: function(data)
                {
                    if (data.success == true)
                    {
                        swal({
                            title: "¿Está seguro que desea borrar este presupuesto?",
                            text: "Está a punto de borrar un presupuesto y esta acción no se puede deshacer",
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
                                    id: _codigo,
                                    action: "delete"
                                },
                                beforeSend: function(x) {
                                    if (x && x.overrideMimeType) {
                                        x.overrideMimeType("application/json;charset=UTF-8");
                                    }
                                },
                                url: '/api/presupuestos/update/index.php',
                                success: function(data)
                                {
                                    ReloadGrid();
                                }
                            });
                        });
                    }
                }
            });
        }
/*
        /////// SELECT COMBO SUBGRUPO ///////
        function fetch_select(val) {
            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    get_option: val,
                    action: "inputSubSelect"
                },
                beforeSend: function(x) {
                    if (x && x.overrideMimeType) {
                        x.overrideMimeType("application/json;charset=UTF-8");
                    }
                },
                url: '/api/subgrupodearticulos/update/index.php',
                success: function(data) {
                    if (data.success == true) {
                        document.getElementById("descrpSubGrup").innerHTML = data.response;
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
                    get_option: val,
                    action: "inputSubSubSelect"
                },
                beforeSend: function(x) {
                    if (x && x.overrideMimeType) {
                        x.overrideMimeType("application/json;charset=UTF-8");
                    }
                },
                url: '/api/ssubgrupodearticulos/update/index.php',
                success: function(data) {
                    if (data.success == true) {
                        document.getElementById("cve_ssgpo").innerHTML = data.response;
                    }
                }
            });
        }
*/
        function editar(_codigo) {
            $("#modaltitle").html('<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button><i class="fa fa-laptop modal-icon"></i><h4 class="modal-title">Editar Presupuesto</h4>');
            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    id : _codigo,
                    action : "load"
                },
                beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                },
                url: '/api/presupuestos/update/index.php',
                success: function(data) {
                    if (data.success == true) {
                        //console.log("ok");
                        $("#new_hiddenID").val(data.id);
                        $('#new_nombre_presupuesto').val(data.nombreDePresupuesto);
                        $("#new_ano_presupuesto").val(data.anoDePresupuesto);
                        $("#new_cve_partida").val(data.claveDePartida);
                        $("#new_concepto_partida").val(data.conceptoDePartida);
                        $("#new_monto_presupuesto").val(data.monto);
                      
                        l.ladda('stop');
                        $("#btnCancel").show();
                        $("#modalEditarPresupuesto").modal('show');
                        $("#hiddenAction").val("edit");
                        
                    }
                }
            });
        }
  
        
        function ver(_codigo) {

            $("#upload").show();



            $("#hiddenID").val(_codigo);
            $("#CodeMessage").html("");
            $("#CodeMessage2").html("");
            $("#CodeMessage3").html("");
            $("#CodeMessage4").html("");
            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    cve_articulo: _codigo,
                    action: "load"
                },
                beforeSend: function(x) {
                    if (x && x.overrideMimeType) {
                        x.overrideMimeType("application/json;charset=UTF-8");
                    }
                },
                url: '/api/presupuestos/update/index.php',
                success: function(data) {
                    if (data.success == true) {

                        $.each(data, function(key, value) {
                            if (key != "imagen")
                                $('#' + key).val(value);
                        });
                        $(".modal-subtitle").text(data.des_articulo);
                        $('#codigo_ver').val(data.cve_articulo);
                        $('#cve_codprov_ver').val(data.cve_codprov);
                        $('#des_articulo_ver').val(data.des_articulo);
                        $('#peso_ver').val(data.peso);
                        $('#barras2_ver').val(data.barras2);
                        $('#num_multiplo_ver').val(data.num_multiplo);
                        $('#barras3_ver').val(data.barras3);
                        $('#cajas_palet_ver').val(data.cajas_palet);

                        $('#grupo_ver').val(data.grupo);
                        $('#clasificacion_ver').val(data.clasificacion);
                        $('#tipo_ver').val(data.tipo);


                        $("#hiddenID").val(_codigo);

                        //Construyo la parte de las imagenes
                        $('#upload2 div').remove();
                        $('#upload2 img').remove();
                        $('#upload2 a').remove();

                        for (var i = 0; i < data.fotos.length; i++) {


                            $('#upload2').append('<a href="../img/articulo/' + data.fotos[i].url + '" title="Image from Unsplash" data-gallery=""><img src="../img/articulo/' + data.fotos[i].url + '" width="100" height="100"></a>');

                        }
                        l.ladda('stop');
                        $("#btnCancel").show();

                        //$('#list').removeAttr('class').attr('class', '');
                        //$('#list').addClass('animated');
                        //$('#list').addClass("fadeOutRight");
                        //$('#arti').hide();


                        $("#btnCancel").show();
                        $modal0 = $("#modalVer");
                        $modal0.modal('show');
                    }
                }
            });
        }

        function cancelar() {
            console.log("Cancelar");
            $("#input-2").fileinput('reset');
            $('#upload div').remove();
            $('#upload img').remove();
            $('#upload a').remove();
            $(':input', '#myform')
                .removeAttr('checked')
                .removeAttr('selected')
                .not(':button, :submit, :reset, :hidden, :radio, :checkbox')
                .val('');
            $('#FORM').removeAttr('class').attr('class', '');
            $('#FORM').addClass('animated');
            $('#FORM').addClass("fadeOutRight");
            $('#FORM').hide();

            $('#arti').show();
            $('#list').removeAttr('class').attr('class', '');
            $('#list').addClass('animated');
            $('#list').addClass("fadeInRight");
            $('#list').addClass("wrapper");
            $('#list').addClass("wrapper-content");
        }

        function agregar() {
            console.log("Agregar");
            $("#input-2").fileinput('reset');
            $('#upload div').remove();
            $('#upload img').remove();
            $('#upload a').remove();
            //$('#nombre_presupuesto').val("");
            //$('#tipo_caja').val("");
            $('#grupo').val("");
            $('#clasificacion').val("");
            $('#tipo').val("");
            $("#imagen").val("");
            $("#upload").hide();
            $("#image").prop("src", "");
            //$("#_title").html('<h3>Agregar Presupuesto</h3>');
            $(':input', '#myform')
                .removeAttr('checked')
                //.removeAttr('selected')
                .not(':button, :submit, :reset, :hidden, :radio, :checkbox')
                .val('');
            $("#CodeMessage").html("");
            $("#CodeMessage2").html("");
            $("#CodeMessage3").html("");
            $("#CodeMessage4").html("");
            $('#list').removeAttr('class').attr('class', '');
            $('#list').addClass('animated');
            $('#list').addClass("fadeOutRight");
            $('#arti').hide();
            $('#FORM').show();
            $('#FORM').removeAttr('class').attr('class', '');
            $('#FORM').addClass('animated');
            $('#FORM').addClass("fadeInRight");
            $("#hiddenAction").val("add");
            $('#codigo').prop('disabled', false);
            $('#barras2').prop('disabled', false);
            $('#barras3').prop('disabled', false);
            $('#cve_codprov').prop('disabled', false);
        }

        var l = $('.ladda-button').ladda();
        l.click(function() {
            $("#btnCancel").hide();
            l.ladda('start');
            if ($("#hiddenAction").val() == "add") {
                $.ajax({
                    type: "POST",
                    dataType: "json",
                    data: {
                        nombre_presupuesto: $("#nombre_presupuesto").val(),
                        ano_presupuesto: $("#ano_presupuesto").val(),
                        cve_partida: $("#cve_partida").val(),
                        concepto_partida: $("#concepto_partida").val(),
                        monto_presupuesto: $("#monto_presupuesto").val(),
                        action: "add"
                    },
                    beforeSend: function(x) {
                        if (x && x.overrideMimeType) {
                            x.overrideMimeType("application/json;charset=UTF-8");
                        }
                    },
                    url: '/api/presupuestos/update/index.php',
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
  
        
        var l = $('.ladda-button-2').ladda();
        l.click(function() {
            $("#new_btnCancel").hide();
            l.ladda('start');
            if ($("#hiddenAction").val() == "edit") {
                $.ajax({
                    type: "POST",
                    dataType: "json",
                    data: {
                        id: $("#new_hiddenID").val(),
                        nombre_presupuesto: $("#new_nombre_presupuesto").val(),
                        ano_presupuesto: $("#new_ano_presupuesto").val(),
                        cve_partida: $("#new_cve_partida").val(),
                        concepto_partida: $("#new_concepto_partida").val(),
                        monto_presupuesto: $("#new_monto_presupuesto").val(),
                        action: "edit"
                    },
                    beforeSend: function(x) {
                        if (x && x.overrideMimeType) {
                            x.overrideMimeType("application/json;charset=UTF-8");
                        }
                    },
                    url: '/api/presupuestos/update/index.php',
                    success: function(data) {
                        if (data.success == true) {
                            $("#modalEditarPresupuesto").modal('hide');
                            cancelar();
                            ReloadGrid();
                            l.ladda('stop');
                            $("#new_btnCancel").show();
                        } else {
                            alert(data.err);
                            l.ladda('stop');
                            $("#new_btnCancel").show();
                        }
                    }
                });
              
            }
        });
  
  
    </script>

<script>
        $("#codigo").keyup(function(e) {

            var claveCode = $(this).val();
            var claveCodeRegexp = new RegExp("^[a-zA-Z0-9]{1,30}$");

            if (claveCodeRegexp.test(claveCode) || claveCode == "") {
                $("#CodeMessage").html("");
                $("#btnSave").prop('disabled', false);

                var cve_articulo = $(this).val();
                $.ajax({
                    type: "POST",
                    dataType: "json",
                    data: {

                        barras3: cve_articulo,
                        barras2: cve_articulo,
                        cve_articulo: cve_articulo,
                        cve_codprov: cve_articulo,
                        action: "exists"
                    },
                    beforeSend: function(x) {
                        if (x && x.overrideMimeType) {
                            x.overrideMimeType("application/json;charset=UTF-8");
                        }
                    },
                    url: '/api/presupuestos/update/index.php',
                    success: function(data) {
                        if (data.success == false) {
                            $("#CodeMessage").html("");
                            $("#btnSave").prop('disabled', false);
                        } else {
                            $("#CodeMessage").html(" Clave de articulo ya existe");
                            $("#btnSave").prop('disabled', true);
                        }
                    }

                });

            } else {
                $("#CodeMessage").html("Por favor, ingresar una Clave de Articulo válida");
                $("#btnSave").prop('disabled', true);
            }
        });

        $("#cve_codprov").keyup(function(e) {

            var claveCode = $(this).val();
            var claveCodeRegexp = new RegExp("^[a-zA-Z0-9]{1,30}$");

            if (claveCodeRegexp.test(claveCode) || claveCode == "") {
                $("#CodeMessage2").html("");
                $("#btnSave").prop('disabled', false);

                var cve_codprov = $(this).val();

                $.ajax({
                    type: "POST",
                    dataType: "json",
                    data: {
                        barras3: cve_codprov,
                        barras2: cve_codprov,
                        cve_articulo: cve_codprov,
                        cve_codprov: cve_codprov,
                        action: "exists"
                    },
                    beforeSend: function(x) {
                        if (x && x.overrideMimeType) {
                            x.overrideMimeType("application/json;charset=UTF-8");
                        }
                    },
                    url: '/api/presupuestos/update/index.php',
                    success: function(data) {
                        if (data.success == false) {
                            $("#CodeMessage2").html("");
                            $("#btnSave").prop('disabled', false);
                        } else {
                            $("#CodeMessage2").html(" Código de Barras ya existe");
                            $("#btnSave").prop('disabled', true);
                        }
                    }

                });

            } else {
                $("#CodeMessage2").html("Por favor, ingresar un Código de Barras válida");
                $("#btnSave").prop('disabled', true);
            }
        });


        $("#barras2").keyup(function(e) {

            var claveCode = $(this).val();
            var claveCodeRegexp = new RegExp("^[a-zA-Z0-9]{1,30}$");

            if (claveCodeRegexp.test(claveCode) || claveCode == "") {
                $("#CodeMessage3").html("");
                $("#btnSave").prop('disabled', false);

                var barras2 = $(this).val();

                $.ajax({
                    type: "POST",
                    dataType: "json",
                    data: {
                        barras3: barras2,
                        barras2: barras2,
                        cve_articulo: barras2,
                        cve_codprov: barras2,
                        action: "exists"
                    },
                    beforeSend: function(x) {
                        if (x && x.overrideMimeType) {
                            x.overrideMimeType("application/json;charset=UTF-8");
                        }
                    },
                    url: '/api/presupuestos/update/index.php',
                    success: function(data) {
                        if (data.success == false) {
                            $("#CodeMessage3").html("");
                            $("#btnSave").prop('disabled', false);
                        } else {
                            $("#CodeMessage3").html(" Código de Barras ya existe");
                            $("#btnSave").prop('disabled', true);
                        }
                    }

                });

            } else {
                $("#CodeMessage3").html("Por favor, ingresar un Código de Barras válida");
                $("#btnSave").prop('disabled', true);
            }
        });

        $("#barras3").keyup(function(e) {

            var claveCode = $(this).val();
            var claveCodeRegexp = new RegExp("^[a-zA-Z0-9]{1,30}$");

            if (claveCodeRegexp.test(claveCode) || claveCode == "") {
                $("#CodeMessage4").html("");
                $("#btnSave").prop('disabled', false);

                var barras3 = $(this).val();

                $.ajax({
                    type: "POST",
                    dataType: "json",
                    data: {
                        barras3: barras3,
                        barras2: barras3,
                        cve_articulo: barras3,
                        cve_codprov: barras3,

                        action: "exists"
                    },
                    beforeSend: function(x) {
                        if (x && x.overrideMimeType) {
                            x.overrideMimeType("application/json;charset=UTF-8");
                        }
                    },
                    url: '/api/presupuestos/update/index.php',
                    success: function(data) {
                        if (data.success == false) {
                            $("#CodeMessage4").html("");
                            $("#btnSave").prop('disabled', false);
                        } else {
                            $("#CodeMessage4").html(" Código de Barras ya existe");
                            $("#btnSave").prop('disabled', true);
                        }
                    }

                });

            } else {
                $("#CodeMessage4").html("Por favor, ingresar un Código de Barras válida");
                $("#btnSave").prop('disabled', true);
            }
        });


        $("#peso").keydown(function(e) {
            // Allow: backspace, delete, tab, escape, enter and .
            if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
                // Allow: Ctrl/cmd+A
                (e.keyCode == 65 && (e.ctrlKey === true || e.metaKey === true)) ||
                // Allow: Ctrl/cmd+C
                (e.keyCode == 67 && (e.ctrlKey === true || e.metaKey === true)) ||
                // Allow: Ctrl/cmd+X
                (e.keyCode == 88 && (e.ctrlKey === true || e.metaKey === true)) ||
                // Allow: home, end, left, right
                (e.keyCode >= 35 && e.keyCode <= 39)) {
                // let it happen, don't do anything
                return;
            }

            if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                e.preventDefault();
            }
        });


        var checkEqual = function(current) {
            var elements = [
                document.getElementById('codigo'),
                document.getElementById('cve_codprov'),
                document.getElementById('barras2'),
                document.getElementById('barras3')
            ];

            var i = 0;
            var error = document.createElement('span');
            error.style.color = 'red';
            error.style.fontWeight = 'bold';
            error.textContent = 'El código está repetido';
            var spanSelector = '#' + current.id + ' + span';

            for (i; i < elements.length; i++) {
                if (current.value == elements[i].value && current.id != elements[i].id && current.value != '') {
                    if (!document.querySelector(spanSelector)) {
                        current.parentElement.appendChild(error);
                    }
                    current.focus();
                    elements[i].style.border = "1px solid red";
                    break;
                } else {
                    elements[i].style.border = "1px solid #e5e6e7";
                    if (document.querySelector(spanSelector)) {
                        console.log('existe');
                        current.parentNode.removeChild(document.querySelector(spanSelector));
                    }
                }
            }
        }

        $("#txtCriterio").keyup(function(event) {
            if (event.keyCode == 13) {
                $("#buscarA").click();
            }
        });


      
        $("#inactivos").on("click", function() {
            $modal0 = $("#coModal");
            $modal0.modal('show');
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
                url: '/api/presupuestos/update/index.php',
                success: function(data) {
                    if (data.success == true) {
                        //$('#codigo').prop('disabled', true);
                        ReloadGrid();
                        ReloadGrid1();
                    }
                }
            });
        }



        $(document).ready(function() {
            $("div.ui-jqgrid-bdiv").css("max-height", $("#list").height() - 80);
            $(function() {
                $('.chosen-select').chosen();
                $('.chosen-select-deselect').chosen({
                    allow_single_deselect: true
                });
            });
        });
    </script>
    <script type="text/javascript">
        <?php if(isSCTP()): ?>

        <?php endif; ?>
        <?php if(isLaCentral()): ?>

        function obtenerProductosLaCentral() {
            $("#modal_lacentral .fa-spinner").show();
            $("#modal_lacentral .success").hide();
            $("#modal_lacentral #button_modal_lacentral").attr('disabled', 'disabled');
            $("#modal_lacentral").modal('show');
            $.ajax({
                url: '/api/synchronize/lacentral.php',
                method: 'POST',
                dataType: 'json',
                data: {
                    action: 'productosLaCentral'
                }
            }).done(function(data) {
                if (data.success) {
                    ReloadGrid();
                    $("#modal_lacentral .fa-spinner").hide();
                    $("#modal_lacentral .success").show();
                    $("#modal_lacentral #button_modal_lacentral").removeAttr('disabled');

                } else {
                    $("#modal_lacentral").modal('hide');
                    swal("Error", data.error, "error");
                }
            });
        }
        <?php endif; ?>
    </script>

    <style>
        <?php if($edit[0]['Activo']==0) {
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
        
        ?>
    </style>