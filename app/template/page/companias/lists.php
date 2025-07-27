<?php
include_once  $_SERVER['DOCUMENT_ROOT']."/app/host.php";

use \Companias\Companias as Compania;
$compania = new Compania();
$poblacion = $compania->getPoblacion();
$tiposCompania =  $compania->getCompania();
$listaTC = new \TipoCliente\TipoCliente();
$listaZona = new \Zona\Zona();

if(!isNikken()){
    $codDaneSql = \db()->prepare("SELECT *  FROM c_dane ");
    $codDaneSql->execute();
    $codDane = $codDaneSql->fetchAll(PDO::FETCH_ASSOC);
}

$vere = \db()->prepare("select * from t_profiles as a where id_menu=7 and id_submenu=5 and id_role='".$_SESSION["perfil_usuario"]."'");
$vere->execute();
$ver = $vere->fetchAll(PDO::FETCH_ASSOC);


$agre = \db()->prepare("select * from t_profiles as a where id_menu=7 and id_submenu=6 and id_role='".$_SESSION["perfil_usuario"]."'");
$agre->execute();
$ag = $agre->fetchAll(PDO::FETCH_ASSOC);


$edita = \db()->prepare("select * from t_profiles as a where id_menu=7 and id_submenu=7 and id_role='".$_SESSION["perfil_usuario"]."'");
$edita->execute();
$edit = $edita->fetchAll(PDO::FETCH_ASSOC);


$borra = \db()->prepare("select * from t_profiles as a where id_menu=7 and id_submenu=8 and id_role='".$_SESSION["perfil_usuario"]."'");
$borra->execute();
$borrar = $borra->fetchAll(PDO::FETCH_ASSOC);



// MOD 7

// VER 5
// AGREGAR 6
// EDITAR 7
// BORRAR 8

?>
    <!DOCTYPE html>
    <link href="/css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">

    <link href="/css/plugins/chosen/chosen.css" rel="stylesheet">
    <link href="/css/bootstrap-imageupload.min.css" rel="stylesheet">
    <link href="/css/plugins/sweetalert/sweetalert.css" rel="stylesheet">

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



<script>

var tipoEmpresas = [];
<?php foreach( $tiposCompania as $p ){ ?>
    tipoEmpresas.push({value:'<?php echo $p->cve_tipcia; ?>', text:'<?php echo $p->des_tipcia; ?>'})
<?php } ?>

</script>


        <!-- Mainly scripts -->
        <script src="/js/jquery-2.1.1.js"></script>
        <script src="/js/bootstrap.min.js"></script>
        <script src="/js/plugins/metisMenu/jquery.metisMenu.js"></script>
        <script src="/js/plugins/slimscroll/jquery.slimscroll.min.js"></script>
        <script src="/js/plugins/sweetalert/sweetalert.min.js"></script>

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
        <!-- Select -->
        <script src="/js/plugins/chosen/chosen.jquery.js"></script>
        <script src="/js/bootstrap-imageupload.js"></script>

    <div class="wrapper wrapper-content  animated" id="list">

        <h3>Empresas</h3>

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
                                            <button type="submit" class="btn btn-primary" id="buscarC">
                                        <span class="fa fa-search"></span> Buscar
                                        </button>
                                        </a>
                                    </div>
                                </div>

                            </div>

                            <div class="col-md-8">
                                <a href="#" onclick="agregar()"><button class="btn btn-primary permiso_registrar" type="button"><i class="fa fa-plus"></i>&nbsp;&nbsp;Nuevo</button></a>

                                <button class="btn btn-primary pull-right" type="button" id="inactivos"><i class="fa fa-search"></i>&nbsp;&nbsp;Empresas inactivas</button>
                            </div>
                        </div>
                    </div>
                    <div class="ibox-content permiso_consultar">

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
            <div class="col-md-12">
                <div class="ibox ">
                    <div class="ibox-title">
                        <div class="row">
                            <div class="col-md-4" id="_title">
                                <h3>Agregar Empresa</h3>
                            </div>
                        </div>
                    </div>
                    <form id="myform" method="post">
                        <input type="hidden" id="hiddenAction" name="hiddenAction">
                        <div class="ibox-content">
                            <div class="row">

                                <div class="col-md-6 b-r">
                                    <div class="form-group"><label>Clave de la Empresa *</label> <input id="txtCveCompa" type="text" placeholder="Clave de la Empresa" maxlength="20" class="form-control" required="true"><label id="CodeMessage" style="color:red;"></label></div>
                                    <div class="form-group"><label>Nombre de la Empresa *</label> <input id="txtNomCompa" type="text" placeholder="Nombre de la Empresa" class="form-control" required="true"></div>
                                    <div class="form-group"><label>RUT | RFC *</label> <input id="txtRutCompa" type="text" placeholder="RFC" class="form-control" required="true"></div>
                                    <div class="form-group">
                                        <label>Tipo de Empresa *</label>


                                        <select id="TipoCompania" class="form-control" required="true">
                                        <option value="">Seleccione</option>
                                        <?php foreach( $tiposCompania as $p ): ?>
                                        <option value="<?php echo $p->cve_tipcia; ?>"><?php echo $p->des_tipcia; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    </div>
                                    <div class="form-group"><label>Distrito *</label> <input id="txtDistrito" type="text" placeholder="Distrito" class="form-control" required="true"></div>
                                    <div class="form-group"><label>Código Dane / Código Postal *</label>

                                    </div>
                                    <div class="form-group">
                                        <?php if(isset($codDane) && !empty($codDane)): ?>
                                        <select id="txtCod" class="form-control" required="true">
                                                <option value="">Código</option>
                                                <?php foreach( $codDane AS $p ): ?>
                                                    <option value="<?php echo $p["cod_municipio"];?>"><?php echo $p["cod_municipio"]." ".$p["des_municipio"]; ?></option>
                                                <?php endforeach; ?>                                    
                                            </select>
                                        <?php else: ?>
                                        <input type="text" name="txtCod" id="txtCod" class="form-control">
                                        <?php endif; ?>
                                    </div>
                                    <div class="form-group"><label>Departamento/Estado *</label> <input disabled id="txtDepart" type="text" placeholder="Departamento" class="form-control" required="true"></div>
                                </div>


                                <div class="col-md-6">


                                    <div class="form-group"><label>Municipio/Ciudad *</label> <input disabled id="txtMunicipio" type="text" placeholder="Municipio" class="form-control" required="true"></div>
                                    <div class="form-group"><label>Dirección *</label> <input id="txtDirecc" type="text" placeholder="Dirección" class="form-control" required="true"></div>
                                    <div class="form-group"><label>Teléfono *</label> <input id="txtTelef" type="text" maxlength="14" oninput="this.value = this.value.replace(/[^0-9]/g, '');" placeholder="Teléfono" class="form-control" required="true"></div>
                                    <div class="form-group"><label>Contacto *</label> <input id="txtContac" type="text" placeholder="Contacto" class="form-control" required="true"></div>
                                    <div class="form-group"><label>Correo Electrónico *</label> <input id="txtCorreo" type="email" placeholder="Correo Electrónico" class="form-control" required="true">
                                        <!--<label id="emailMessage" style="color:red;">--></label>
                                    </div>
                                    <div class="form-group"><label>Comentarios</label> <input id="txtComent" type="text" placeholder="Comentarios" class="form-control"></div>

                                    <div class="form-group" style="text-align: center;">
                                        <div class="checkbox">
                                            <label for="es_3PL" style="font-weight: 600;font-size: 14px; -moz-user-select: none; -webkit-user-select: none;-ms-user-select: none; user-select: none;">
                                            <input type="checkbox" name="es_3PL" id="es_3PL" value="0">Es 3PL</label>
                                        </div>
                                    </div>

                                    <?php /* ?>
                                    <div class="form-group">
                                        <label for="transportista" style="font-weight: 600;font-size: 14px; -moz-user-select: none; -webkit-user-select: none;-ms-user-select: none; user-select: none;margin-left: 30px;margin-top: 30px;cursor: pointer;">
                                        <input type="checkbox" name="transportista" id="transportista" value="0">  Es Transportadora</label>
                                    </div>
                                    <?php */ ?>

                                    <div class="form-group">
                                        <div id="upload">
                                            <label>Imagen Actual</label>
                                            <img src="" alt="Image preview" ima="" class="thumbnail" id="image" style="max-width: 100%; width: 100%">
                                        </div>

                                        <div class="imageupload panel panel-default" id="upload">
                                            <div class="panel-heading clearfix">
                                                <h3 class="panel-title">Subir Imagen</h3>
                                            </div>
                                            <div class="file-tab panel-body">
                                                <div class="row" style="margin-bottom: 30px;">
                                                    <div class="col-md-4 text-center">
                                                        <div class="label label-default">
                                                            Tamaño Máximo: 512kb
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4 text-center">
                                                        <div class="label label-default">
                                                            Alto Máximo: 150px
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4 text-center">
                                                        <div class="label label-default">
                                                            Ancho Máximo: 150px
                                                        </div>
                                                    </div>
                                                </div>
                                                <label class="btn btn-primary btn-file fileContainer ">        <!-- The file is stored here. -->
                                            <b>Examinar</b>
                                            <input id="imagen" type="file" name="image-file">

                                        </label>

                                                <button type="button" class="btn btn-default">Remover</button>
                                            </div>
                                        </div>
                                    </div>

                                    <input type="hidden" id="hiddenIDCompania">

                                    <div class="pull-right">
                                        <a href="#" onclick="cancelar()"><button type="button" class="btn btn-white" id="btnCancel">Cerrar</button></a>&nbsp;&nbsp;
                                        <button type="submit" class="btn btn-primary" id="btnSave">Guardar</button>
                                    </div>

                                </div>
                            </div>

                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="coModall" role="dialog">
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
                        <h4 class="modal-title">Recuperar Empresa</h4>
                    </div>
                    <div class="modal-body">
                        <div class="col-md-4">
                            <div class="input-group">
                                <input type="text" class="form-control input-sm" name="txtCriterio" id="txtCriterio1" placeholder="Buscar por Empresa...">
                                <div class="input-group-btn">
                                    <a href="#" onclick="ReloadGrid1()">
                                        <button type="submit" class="btn btn-sm btn-primary">
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


    <div class="modal inmodal" id="modal-ver-registro" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content animated bounceInRight">
                <div class="modal-header" id="modaltitle">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                    <i class="fa fa-laptop modal-icon"></i>
                    <h4 class="modal-title">Detalles de Empresa</h4>
                </div>
                <form id="myform">
                    <div class="modal-body">

                        <div class="row">

                            <div class="col-md-6 b-r">
                                <div class="form-group">
                                    <label>Clave de la Empresa</label>
                                    <input id="txtCveCompa-readonly" type="text" class="form-control" readonly>
                                </div>
                                <div class="form-group">
                                    <label>Nombre de la Empresa</label>
                                    <input id="txtNomCompa-readonly" type="text" class="form-control" readonly>
                                </div>
                                <div class="form-group">
                                    <label>RUT | RFC</label>
                                    <input id="txtRutCompa-readonly" type="text" class="form-control" readonly>
                                </div>
                                <div class="form-group">
                                    <label>Tipo de Empresa </label>
                                    <input id="TipoCompania-readonly" type="text" class="form-control" readonly>
                                </div>
                                <div class="form-group">
                                    <label>Distrito</label>
                                    <input id="txtDistrito-readonly" type="text" class="form-control" readonly>
                                </div>
                                <div class="form-group">
                                    <label>Código Dane / Código Postal</label>
                                    <input id="txtCod-readonly" type="text" class="form-control" readonly>
                                </div>
                                <div class="form-group">
                                    <label>Departamento/Estado</label>
                                    <input id="txtDepart-readonly" type="text" class="form-control" readonly>
                                </div>

                            </div>

                            <div class="col-md-6">

                                <div class="form-group">
                                    <label>Municipio/Ciudad</label>
                                    <input disabled id="txtMunicipio-readonly" type="text" class="form-control" readonly>
                                </div>
                                <div class="form-group">
                                    <label>Dirección</label>
                                    <input id="txtDirecc-readonly" type="text" class="form-control" readonly>
                                </div>
                                <div class="form-group">
                                    <label>Teléfono</label>
                                    <input id="txtTelef-readonly" type="text" class="form-control" readonly>
                                </div>
                                <div class="form-group">
                                    <label>Contacto</label>
                                    <input id="txtContac-readonly" type="text" class="form-control" readonly>
                                </div>
                                <div class="form-group">
                                    <label>Correo Electrónico</label>
                                    <input id="txtCorreo-readonly" type="email" class="form-control" readonly>
                                </div>
                                <div class="form-group">
                                    <label>Comentarios</label>
                                    <input id="txtComent-readonly" type="text" class="form-control" readonly>
                                </div>
                                
                                <div class="form-group">
                                    <div id="upload">
                                        <label>Imagen Actual</label>
                                        <img src="" class="thumbnail" id="image-readonly" style="max-width: 100%; width: 100%">
                                    </div>
                                </div>

                                <input id="id-readonly" type="hidden"/>

                            </div>

                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-white" data-dismiss="modal" id="btnCancel">Cerrar</button>
                            <?php 
                            if($permiso_editar == 1)
                            {
                            ?>
                            <button type="button" onclick="editar( $('#id-readonly').val() ); $('#modal-ver-registro').modal('hide')" class="btn btn-primary ladda-button" id="btnSave">Editar</button>
                            <?php 
                            }
                            ?>
                        </div>
                    </div>
                </form>
                </div>
            </div>
        </div>




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



            function uploadFile() {
                var input = document.getElementById("imagen");
                file = input.files[0];

                if (file != undefined) {
                    formData = new FormData();
                    if (!!file.type.match(/image.*/)) {
                        formData.append("image", file);
                        $.ajax({
                            url: "/app/template/page/companias/upload.php",
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
                    //alert('Input something!');
                }
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
                    url: '/api/compania/lista/index.php',
                    datatype: "json",
                    contentType: "application/json",
                    shrinkToFit: false,
                    height: 'auto',
                    postData: {
                        criterio: $("#txtCriterio").val()
                    },
                    mtype: 'POST',
                    colNames: ["Acciones","ID", 'Clave de Empresa', 'Nombre', "Tipo"/*,"Es Transportadora"*/, "Dirección", "Responsable", "Teléfono", "Email", "Es 3PL"],
                    /*'Calle y Numero','Colonia','Ciudad','Estado','Pais','Codigo Postal','RFC','Telefono1','Telefono2'*/
                    colModel: [
                        {name: 'acciones',index: 'acciones',width: 80,fixed: true,sortable: false,resize: false,formatter: imageFormat},
                        {name: 'cve_cia',index: 'cve_cia',width: 40,editable: false,hidden: true,sortable: false},
                        {name: 'clave_empresa',index: 'clave_empresa',width: 140,editable: false,sortable: false},
                        {name: 'des_cia',index: 'des_cia',width: 310,editable: false,sortable: false},
                        {name: 'des_tipcia',index: 'des_tipcia',width: 180,editable: false,sortable: false},
                        //{name: 'es_transportadora',index:'es_transportadora',width:120, editable:false, sortable:false},
                        {name: 'des_direcc',index: 'des_direcc',width: 280,editable: false,sortable: false,resizable: false},
                        {name: 'des_contacto',index: 'des_contacto',editable: false,sortable: false,resizable: false},
                        {name: 'des_telf',index: 'des_telef',editable: false,sortable: false,resizable: false},
                        {name: 'des_email',index: 'des_email',width: 200,editable: false,sortable: false,resizable: false},
                        {name: 'es3pl',index: 'es3pl',width: 80,editable: false,sortable: false,resizable: false},
                    ],
                    rowNum: 30,
                    rowList: [30, 40, 50],
                    pager: pager_selector,
                    sortname: 'Cve_Clte',
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
                    var serie = rowObject[1];
                    var correl = rowObject[5];
                    var url = "x/?serie=" + serie + "&correl=" + correl;
                    var url2 = "v/?serie=" + serie + "&correl=" + correl;
                    $("#hiddencve_tipcia").val(serie);
                    // la funcion downloadxml ejecuta el index.php que esta en la carpeta x pasandole la serie y el correlativo via get
                    // la funcion viewPdf ejecuta el index.php que esta en la carpeta v pasandole la serie y el correlativo via get

                    var html = '';
                    html += '<a href="#" onclick="ver(\'' + serie + '\')"><i class="fa fa-search" title="Ver"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
                    if($("#permiso_editar").val() == 1)
                    {
                    html += '<a href="#" onclick="editar(\'' + serie + '\')"><i class="fa fa-edit" title="Editar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
                    }
                    if($("#permiso_eliminar").val() == 1)
                    {
                    html += '<a href="#" onclick="borrar(\'' + serie + '\')"><i class="fa fa-eraser" title="Borrar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
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

            function downloadxml(url) {
                var win = window.open(url, '_blank');
                win.focus();
            }

            function viewPdf(url) {
                var win = window.open(url, '_blank');
                win.focus();
            }

            $modal0 = null;

          
             /**************************************** FIN ****************************************/
            function borrar(_codigo) {
                console.log(_codigo);
                $.ajax({
                    url: '/api/compania/update/index.php',
                    type: "POST",
                    dataType: "json",
                    data: {
                        cve_cia: _codigo,
                        action: "tieneAlmacen"
                    },
                    beforeSend: function(x) {
                        if (x && x.overrideMimeType) {
                            x.overrideMimeType("application/json;charset=UTF-8");
                        }
                    },
                    success: function(data) {
                        if (data.success == true) {
                            swal({
                                title: "¡Alerta!",
                                text: "La empresa esta siendo usada en este momento",
                                type: "warning",
                                showCancelButton: false,
                            });
                        } else {
                            swal({
                                    title: "¿Está seguro que desea borrar la empresa?",
                                    text: "Está a punto de borrar una empresa y esta acción no se puede deshacer",
                                    type: "warning",
                                    showCancelButton: true,
                                    confirmButtonColor: "#DD6B55",
                                    confirmButtonText: "Borrar",
                                    cancelButtonText: "Cancelar",
                                    closeOnConfirm: false
                                },
                                function() {
                                    var borro = localStorage.getItem("borro") | 0;
                                    localStorage.setItem('borro', borro + 1);
                                    var articulo = $(`#${_codigo} td[aria-describedby='grid-tabla_id']`).text();
                                    $.ajax({
                                        url: '/api/compania/update/index.php',
                                        type: "POST",
                                        dataType: "json",
                                        data: {
                                            cve_cia: _codigo,
                                            action: "delete"
                                        },
                                        beforeSend: function(x) {
                                            if (x && x.overrideMimeType) {
                                                x.overrideMimeType("application/json;charset=UTF-8");
                                            }
                                        },
                                    }).done(function(data) {
                                        if (data.success) {
                                            ReloadGrid();
                                            swal("Borrado", "La empresa ha sido borrada exitosamente", "success");
                                        } else {
                                            swal("Error", "Ocurrió un error al eliminar la empresa", "error");
                                        }
                                    });
                                });
                            //    auto = setInterval(cargarTodoAuto, 7000); 
                        }
                    }
                });
            }



            function editar(_codigo) {
              console.log("x");
                $("#upload").show();
                //$('.imageupload').imageupload('reset');
                $("#hiddenIDCompania").val(_codigo);
                $("#_title").html('<h3>Editar Empresa</h3>');
                $("#emailMessage").html("");
                $("#CodeMessage").html("");
                $("#txtCveCompa").prop('disabled', true);
                $.ajax({
                  
                    type: "POST",
                    dataType: "json",
                    data: {
                        codigo: _codigo,
                        action: "load"
                    },
                    beforeSend: function(x) {
                        if (x && x.overrideMimeType) {
                            x.overrideMimeType("application/json;charset=UTF-8");
                        }
                    },
                    url: '/api/compania/update/index.php',
                    success: function(data) {
                        console.log(data);
                        if (data.success == true) {
                            $("#TipoCompania").val(data.cve_tipcia);
                            $("#txtCveCompa").val(data.clave_empresa);
                            $("#txtNomCompa").val(data.des_cia);
                            $("#txtRutCompa").val(data.des_rfc);
                            $("#txtDistrito").val(data.distrito);
                            $("#txtDirecc").val(data.des_direcc);
                            $("#txtCod").val(data.des_cp);
                            $("#txtDepart").val(data.departamento);
                            $("#txtMunicipio").val(data.municipio);
                            $("#txtTelef").val(data.des_telef);
                            $("#txtContac").val(data.des_contacto);
                            $("#txtCorreo").val(data.des_email);
                            $("#txtComent").val(data.des_observ);
                            $("#hiddenIDCompania").val(data.cve_cia);
                            $("#image").prop("src", data.imagen);
                            $("#image").prop("name", data.imagen);
/*
                            if(data.es_transportista == 1)
                            {
                                $("#transportista").prop("checked", true);
                            }
*/
                            $('#list').removeAttr('class').attr('class', '');
                            $('#list').addClass('animated');
                            $('#list').addClass("fadeOutRight");
                            $('#list').hide();

                            $('#FORM').show();
                            $('#FORM').removeAttr('class').attr('class', '');
                            $("#btnSave").removeAttr('disabled');
                            $('#FORM').addClass('animated');
                            $('#FORM').addClass("fadeInRight");

                            $("#hiddenAction").val("edit");
                        }
                    }
                });
              
            }


            function ver(_codigo) {
              
                $.ajax({
                    type: "POST",
                    dataType: "json",
                    data: {
                        codigo: _codigo,
                        action: "load"
                    },
                    beforeSend: function(x) {
                        if (x && x.overrideMimeType) {
                            x.overrideMimeType("application/json;charset=UTF-8");
                        }
                    },
                    url: '/api/compania/update/index.php',
                    success: function(data) {
                        console.log(data);
                        if (data.success == true) {
                            $("#TipoCompania-readonly").val(getTipoCopaniaById(data.cve_tipcia));
                            $("#txtCveCompa-readonly").val(data.clave_empresa);
                            $("#txtNomCompa-readonly").val(data.des_cia);
                            $("#txtRutCompa-readonly").val(data.des_rfc);
                            $("#txtDistrito-readonly").val(data.distrito);
                            $("#txtDirecc-readonly").val(data.des_direcc);
                            $("#txtCod-readonly").val(data.des_cp);
                            $("#txtDepart-readonly").val(data.departamento);
                            $("#txtMunicipio-readonly").val(data.municipio);
                            $("#txtTelef-readonly").val(data.des_telef);
                            $("#txtContac-readonly").val(data.des_contacto);
                            $("#txtCorreo-readonly").val(data.des_email);
                            $("#txtComent-readonly").val(data.des_observ);
                            $("#image-readonly").prop("src", data.imagen);
                            $("#image-readonly").prop("name", data.imagen);

                            $('#id-readonly').val(data.cve_cia) 

                            $('#modal-ver-registro').modal('show')

                        }
                    }
                });
            }

            function getTipoCopaniaById(id){
               var  val = ''
                $.each( tipoEmpresas, function(key, value){
                    if( value.value == id ) {
                        val = value.text
                        return true
                    }
                })
                return val;
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
                $("#imagen").val("");
                $("#upload").hide();
                $("#image").prop("src", "");
                $("#_title").html('<h3>' + ' Empresas</h3>');
                $('#txtClaveCliente').prop('disabled', false);
                $("#txtCveCompa").prop('disabled', false);
                $("#txtDepart").val("");
                $("#txtMunicipio").val("");
                $("#txtCod").val("");
                $("#emailMessage").html("");
                $("#txtCveCompa").html("");
                $("#CodeMessage").html("");
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
            }

            var l = $('#myform');
            l.submit(function() {

                $("#btnCancel").hide();

                //l.ladda('start');
                if ($('#imagen').val() !== '') {
                    var path = $('#imagen').val();
                    var filename = path.replace(/^.*\\/, "");
                    uploadFile();
                } else if ($('#image').attr('src') != "" && !$('#imagen').val()) {
                    var path = $('#image').attr("src");
                    var filename = path.replace(/^.*\\/, "");
                    uploadFile();
                } else {
                    filename = "noimage.jpg"
                }



                if ($("#txtCveCompa").val() !== '' &&
                    $("#txtDistrito").val() !== '' &&
                    $("#TipoCompania").val() !== '' &&
                    $("#txtNomCompa").val() !== '' &&
                    $("#txtRutCompa").val() !== '' &&
                    $("#txtDirecc").val() !== '' &&
                    //$("#txtCod").val() !== '' && 
                    $("#txtTelef").val() !== '' &&
                    $("#txtContac").val() !== '' &&
                    $("#txtCorreo").val() !== '') {
/*
                    var transportista = 0;
                    if($("#transportista").is(':checked'))
                        transportista = 1;
*/
                    var es_3PL = 0;
                    if($("#es_3PL").is(':checked'))
                        es_3PL = 1;

                    if ($("#hiddenAction").val() == "add") {
                        $.post('/api/compania/update/index.php', {
                            cve_empresa: $("#txtCveCompa").val(),
                            distrito: $("#txtDistrito").val(),
                            cve_tipcia: $("#TipoCompania").val(),
                            des_cia: $("#txtNomCompa").val(),
                            des_rfc: $("#txtRutCompa").val(),
                            des_direcc: $("#txtDirecc").val(),
                            des_cp: $("#txtCod").val(),
                            des_telef: $("#txtTelef").val(),
                            //transportista: transportista,
                            des_contacto: $("#txtContac").val(),
                            des_email: $("#txtCorreo").val(),
                            des_observ: $("#txtComent").val(),
                            txtDepart: $("#txtDepart").val(),
                            txtMunicipio: $("#txtMunicipio").val(),
                            es_3PL: es_3PL,
                            imagen: 'img/compania/'+filename,
                            action: "add"
                        },
                        function(response) {
                            console.log(response);
                        }, "json")
                    .always(function() {
                        //l.ladda('stop');
                        //window.location.reload();
                        $("#btnCancel").show();
                        cancelar()
                        ReloadGrid();
                    });
                } else {
                        $.post('/api/compania/update/index.php', {
                                    cve_cia: $("#hiddenIDCompania").val(),
                                    cve_empresa: $("#txtCveCompa").val(),
                                    distrito: $("#txtDistrito").val(),
                                    cve_tipcia: $("#TipoCompania").val(),
                                    des_cia: $("#txtNomCompa").val(),
                                    des_rfc: $("#txtRutCompa").val(),
                                    //transportista: transportista,
                                    des_direcc: $("#txtDirecc").val(),
                                    des_cp: $("#txtCod").val(),
                                    des_telef: $("#txtTelef").val(),
                                    des_contacto: $("#txtContac").val(),
                                    des_email: $("#txtCorreo").val(),
                                    des_observ: $("#txtComent").val(),
                                    es_3PL: es_3PL,
                                    imagen: 'img/compania/'+filename,
                                    action: "edit"
                                },
                                function(response) {
                                    console.log(response);
                                }, "json")
                            .always(function() {
                                $(':input', '#myform')
                                    .removeAttr('checked')
                                    .removeAttr('selected')
                                    .not(':button, :submit, :reset, :hidden, :radio, :checkbox')
                                    .val('');
                                //l.ladda('stop');
                                $("#btnCancel").show();
                                cancelar()
                                ReloadGrid();
                            });
                    }
                } else {
                    $("#coModall").modal();
                    setTimeout(function() {
                        $("#coModall").modal("hide");
                        //l.ladda('stop');
                    }, 3000);
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
            /*$("#txtCorreo").keyup(function(e) {

                    var zipCode = $(this).val();
                    var regex = /^(([^<>()\[\]\.,;:\s@\"]+(\.[^<>()\[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/;
                    var zipCodeRegexp = new RegExp(regex);

                    if (zipCodeRegexp.test(zipCode)) {
                        $("#emailMessage").html("");
                        $("#btnSave").prop('disabled', false);			
                    }else{
                        $("#emailMessage").html("Por favor, ingresar un Correo Electrónico válido");
                        $("#btnSave").prop('disabled', true);
                    }
                });*/

            $("#txtCveCompa").keyup(function(e) {

                var claveCode = $(this).val();
                var claveCodeRegexp = new RegExp("^[a-zA-Z0-9]{1,20}$");

                if (claveCodeRegexp.test(claveCode)) {
                    $("#CodeMessage").html("");
                    $("#btnSave").prop('disabled', false);

                    var clave_empresa = $(this).val();

                    $.ajax({
                        type: "POST",
                        dataType: "json",
                        data: {
                            clave_empresa: clave_empresa,
                            action: "exists"
                        },
                        beforeSend: function(x) {
                            if (x && x.overrideMimeType) {
                                x.overrideMimeType("application/json;charset=UTF-8");
                            }
                        },
                        url: '/api/compania/update/index.php',
                        success: function(data) {
                            if (data.success == false) {
                                $("#CodeMessage").html("");
                                $("#btnSave").prop('disabled', false);
                            } else {
                                $("#CodeMessage").html(" Clave de empresa ya existe");
                                $("#btnSave").prop('disabled', true);
                            }
                        }

                    });

                } else {
                    $("#CodeMessage").html("Por favor, ingresar una Clave de Empresa válida");
                    $("#btnSave").prop('disabled', true);
                }
            });

            $("#txtCveCompa").keyup(function(e) {

                var claveCode = $(this).val();
                var claveCodeRegexp = new RegExp("^[a-zA-Z0-9]{1,20}$");

                if (claveCodeRegexp.test(claveCode)) {
                    $("#CodeMessage").html("");
                    $("#btnSave").prop('disabled', false);

                } else {
                    $("#CodeMessage").html("Por favor, ingresar una Clave de Empresa válida");
                    $("#btnSave").prop('disabled', true);
                }
            });

            $("#txtCriterio").keyup(function(event) {
                if (event.keyCode == 13) {
                    $("#buscarC").click();
                }
            });
        </script>

        <script type="text/javascript">
            //<!-- Segundas Grid -->
            //////////////////////////////////////////////////////////Aqui se contruye el Grid//////////////////////////////////////////////////////////
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
                    url: '/api/compania/lista/index_i.php',
                    datatype: "json",
                    height: 250,
                    postData: {
                        criterio: $("#txtCriterio1").val()
                    },
                    mtype: 'POST',
                    colNames: ["ID", 'Clave de Empresa', 'Nombre', "Dirección", "Responsable", "Recuperar"],
                    /*'Calle y Numero','Colonia','Ciudad','Estado','Pais','Codigo Postal','RFC','Telefono1','Telefono2'*/
                    colModel: [
                        //{name:'id',index:'id', width:60, sorttype:"int", editable: false},
                        {name: 'cve_cia',index: 'cve_cia',width: 50,editable: false,hidden: true,sortable: false},
                        {name: 'clave_empresa',index: 'clave_empresa',width: 200,editable: false,sortable: false},
                        {name: 'des_cia',index: 'des_cia',editable: false,sortable: false},
                        {name: 'des_direcc',index: 'des_direcc',editable: false,sortable: false,resizable: false},
                        {name: 'des_contacto',index: 'des_contacto',editable: false,sortable: false,resizable: false},
                        {name: 'myac',index: '',width: 80,fixed: true,sortable: false,resize: false,formatter: imageFormat},
                      
                    ],
                    rowNum: 10,
                    rowList: [10, 20, 30],
                    pager: pager_selector,
                    sortname: 'Cve_Clte',
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
                    var id_empresa = rowObject[0];
                    // var estado = rowObject[1];
                    //var correl = rowObject[4];
                    //var url = "x/?serie="+poblacion+"&correl="+correl;
                    //var url2 = "v/?serie="+poblacion+"&correl="+correl;
                    $("#hiddenIDCompania").val(id_empresa);
                    //$("#hiddenIDEstado").val(estado);
                    // la funcion downloadxml ejecuta el index.php que esta en la carpeta x pasandole la serie y el correlativo via get
                    // la funcion viewPdf ejecuta el index.php que esta en la carpeta v pasandole la serie y el correlativo via get

                    var html = '';
                    //html += '<a href="#" onclick="ver(\''+serie+'\')"><i class="fa fa-eye" alt="Ver"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
                    /* html += '<a href="#" onclick="editar(\''+id_empresa+'\')"><i class="fa fa-edit" alt="Editar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';*/
                    html += '<a href="#" onclick="recovery(\'' + id_empresa + '\')"><i class="fa fa-check" alt="Borrar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';

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

            function recovery(_codigo) {
                $.ajax({
                    type: "POST",
                    dataType: "json",
                    data: {
                        cve_cia: _codigo,
                        action: "recovery"
                    },
                    beforeSend: function(x) {
                        if (x && x.overrideMimeType) {
                            x.overrideMimeType("application/json;charset=UTF-8");
                        }
                    },
                    url: '/api/compania/update/index.php',
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
