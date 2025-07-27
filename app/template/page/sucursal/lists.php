<?php
include $_SERVER['DOCUMENT_ROOT']."/app/host.php";
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


$vere = \db()->prepare("select * from t_profiles as a where id_menu=129 and id_submenu=13 and id_role='".$_SESSION["perfil_usuario"]."'");
$vere->execute();
$ver = $vere->fetchAll(PDO::FETCH_ASSOC);


$agre = \db()->prepare("select * from t_profiles as a where id_menu=129 and id_submenu=14 and id_role='".$_SESSION["perfil_usuario"]."'");
$agre->execute();
$ag = $agre->fetchAll(PDO::FETCH_ASSOC);


$edita = \db()->prepare("select * from t_profiles as a where id_menu=129 and id_submenu=15 and id_role='".$_SESSION["perfil_usuario"]."'");
$edita->execute();
$edit = $edita->fetchAll(PDO::FETCH_ASSOC);


$borra = \db()->prepare("select * from t_profiles as a where id_menu=129 and id_submenu=16 and id_role='".$_SESSION["perfil_usuario"]."'");
$borra->execute();
$borrar = $borra->fetchAll(PDO::FETCH_ASSOC);



// MOD 8

// VER 9
// AGREGAR 10
// EDITAR 11
// BORRAR 12


?>
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
    #listt {
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

<div class="wrapper wrapper-content  animated" id="list">

    <h3>Sucursal</h3>

    <div class="row">
        <div class="col-md-12">
            <div class="ibox ">
                <div class="ibox-title">
                    <div class="row">    
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Empresa</label>
                                <select id="cve_ciag" class="form-control">
                                    <option value="">Seleccione</option>
                                    <?php foreach( $compania->getAll() AS $p ): ?>
                                    <option value="<?php echo $p["cve_cia"];?>"><?php echo $p["des_cia"]; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="input-group">
                                <input type="text" class="form-control" name="txtCriterio" id="txtCriterio" placeholder="Buscar...">
                                <div class="input-group-btn">
                                    <a href="#" onclick="ReloadGrid()">
                                        <button type="submit" class="btn btn btn-primary" id="buscarC">
                                            <span class="fa fa-search"></span> Buscar
                                        </button>
                                    </a>
                                </div>
                            </div>

                        </div>
                        <div class="col-md-8">


                            <a href="#" onclick="agregar()"><button class="btn btn-primary permiso_registrar" type="button"><i class="fa fa-plus"></i>&nbsp;&nbsp;Nuevo</button></a>
                            <button class="btn btn-primary pull-right" type="button" id="inactivos"><i class="fa fa-search"></i>&nbsp;&nbsp;Inactivas</button>
                            <?php if(isSCTP()): ?>
                                <button class="btn btn-primary pull-right" onclick="obtenerSucursalesSCTP()" style="margin-right: 20px">Obtener Sucursales de Rutas SCTP</button>
                            <?php endif; ?>
                            <?php if(isLaCentral()): ?>
                                <button class="btn btn-primary pull-right" onclick="obtenerSucursalesLaCentral()" style="margin-right: 20px">Obtener Sucursales de Rutas de La Central</button>
                            <?php endif; ?>
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
        <div class="col-md-12">
            <div class="ibox ">
                <div class="ibox-title">
                    <div class="row">
                        <div class="col-md-4" id="_title">
                            <h3>Agregar Sucursal</h3>
                        </div>
                    </div>
                </div>
                <form id="myform">
                    <input type="hidden" id="hiddenAction" name="hiddenAction">
                    <div class="ibox-content">
                        <div class="row">

                            <div class="col-md-6 b-r">
                                <div class="form-group">
                                    <label>Empresa</label>
                                    <select id="cve_cia" class="form-control">
                                        <option value="">Seleccione</option>
                                        <?php foreach( $compania->getAll() AS $p ): ?>
                                        <option value="<?php echo $p["cve_cia"];?>"><?php echo $p["des_cia"]; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group"><label>Clave de la Sucursal</label> <input id="txtCveCompa" type="text" placeholder="Clave de la Sucursal" maxlength="20" class="form-control"><label id="CodeMessage" style="color:red;"></label></div>
                                <div class="form-group"><label>Nombre de la Sucursal</label> <input id="txtNomCompa" type="text" placeholder="Nombre de la Sucursal" class="form-control"></div>
                                <div class="form-group"><label>RUT</label> <input id="txtRutCompa" type="text" placeholder="RUT" class="form-control"></div>

                                <div class="form-group"><label>Distrito</label> <input id="txtDistrito" type="text" placeholder="Distrito" class="form-control"></div>
                                <div class="form-group">
                                    <label>Código Dane / Código Postal</label>
                                    <?php if(isset($codDane) && !empty($codDane)): ?>
                                        <select id="txtCod" class="chosen-select form-control">
                                            <option value="">Código</option>
                                            <?php foreach( $codDane AS $p ): ?>
                                                <option value="<?php echo $p["cod_municipio"];?>"><?php echo $p["cod_municipio"]." ".$p["des_municipio"]; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    <?php else: ?>
                                        <input type="text" name="txtCod" id="txtCod" class="form-control">
                                    <?php endif; ?> 
                                </div>
                                <div class="form-group"><label>Departamento/Estado</label> <input disabled id="txtDepart" type="text" placeholder="Departamento" class="form-control"></div>
                            </div>


                            <div class="col-md-6">


                                <div class="form-group"><label>Municipio/Ciudad</label> <input disabled id="txtMunicipio" type="text" placeholder="Municipio" class="form-control"></div>
                                <div class="form-group"><label>Dirección</label> <input id="txtDirecc" type="text" placeholder="Dirección" class="form-control"></div>
                                <div class="form-group"><label>Teléfono</label> <input id="txtTelef" type="text" maxlength="14" oninput="this.value = this.value.replace(/[^0-9]/g, '');" placeholder="Teléfono" class="form-control"></div>
                                <div class="form-group"><label>Contácto</label> <input id="txtContac" type="text" placeholder="Contácto" class="form-control"></div>
                                <div class="form-group"><label>Correo Electrónico</label> <input id="txtCorreo" type="text" placeholder="Correo Electrónico" pattern="[a-zA-Z]{3,}@[a-zA-Z]{3,}[.]{1}[a-zA-Z]{2,}[.]{1}[a-zA-Z]{2,}[.]{1}[a-zA-Z]{2,}" class="form-control"><label id="emailMessage" style="color:red;"></label></div>
                                <div class="form-group"><label>Comentarios</label> <input id="txtComent" type="text" placeholder="Comentarios" class="form-control"></div>
                                <div class="form-group">
                                    <div id="upload">
                                        <label>Imagen Actual</label>
                                        <img src=""  alt="Image preview" ima="" class="thumbnail" id="image" style="max-width: 100%; width: 100%">
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

                                <input type="hidden" id="hiddenIDCompania">

                                <div class="pull-right">
                                    <a href="#" onclick="cancelar()"><button type="button" class="btn btn-white" id="btnCancel">Cerrar</button></a>&nbsp;&nbsp;
                                    <button type="button" class="btn btn-primary ladda-button" data-style="contract" id="btnSave">Guardar</button>
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
                    <h4 class="modal-title">Recuperar Sucursal</h4>
                </div>
                <div class="modal-body">
                    <div class="col-md-4">
                        <div class="input-group">
                            <input type="text" class="form-control input-sm" name="txtCriterio" id="txtCriterio1" placeholder="Buscar por Sucursal...">
                            <div class="input-group-btn">
                                <a href="#" onclick="ReloadGrid1()">
                                    <button type="submit" class="btn btn-sm btn-primary">
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
<?php if(isSCTP()): ?>
<div class="modal fade" id="modal_sctp" role="dialog">
    <div class="vertical-alignment-helper">
        <div class="modal-dialog vertical-align-center modal-sm">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Obteniendo Sucursales de SCTP</h4>
                </div>
                <div class="modal-body">
                    <div class="text-center">
                        <i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i>
                        <div class="success">
                            <h3>
                                <i class="fa fa-check" style="color: #1ab394"></i>
                                ¡Sucursales cargadas exitosamente!
                            </h3>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="button_modal_sctp" disabled="disabled" data-dismiss="modal">Aceptar</button>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
<?php if(isLaCentral()): ?>
<div class="modal fade" id="modal_lacentral" role="dialog">
    <div class="vertical-alignment-helper">
        <div class="modal-dialog vertical-align-center modal-sm">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Obteniendo Sucursales de La Central</h4>
                </div>
                <div class="modal-body">
                    <div class="text-center">
                        <i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i>
                        <div class="success">
                            <h3>
                                <i class="fa fa-check" style="color: #1ab394"></i>
                                ¡Sucursales cargadas exitosamente!
                            </h3>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="button_modal_lacentral" disabled="disabled" data-dismiss="modal">Aceptar</button>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

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



    function uploadFile(){
        var input = document.getElementById("imagen");
        file = input.files[0];

        if(file != undefined){
            formData= new FormData();
            if(!!file.type.match(/image.*/)){
                formData.append("image", file);
                $.ajax({
                    url: "/app/template/page/sucursal/upload.php",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(data){
                        //alert(data);
                    }
                    ,
                    error: function(xhr, ajaxOptions, thrownError) {
                        //console.log(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                        alert(thrownError);
                    }
                });
            }else{
                alert('Not a valid image!');
            }
        }else{
            alert('Input something!');
        }
    }


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
            url:'/api/sucursal/lista/index.php',
            datatype: "json",
            contentType: "application/json",
            shrinkToFit: false,
            height:'auto',
            postData: {
                criterio: $("#txtCriterio").val()
            },
            mtype: 'POST',
            colNames:["ID",'Empresa','Clave de Sucursal','Nombre',"Dirección", "Responsable", "Teléfono", "Email", "Acciones"],/*'Calle y Numero','Colonia','Ciudad','Estado','Pais','Codigo Postal','RFC','Telefono1','Telefono2'*/
            colModel:[
                {name:'id',index:'id', width:60, sorttype:"int", editable: false,hidden:true},
                {name:'empresa',index:'empresa', width: 140, editable:false, sortable:false},
                {name:'clave_sucursal',index:'clave_sucursal', width: 140, editable:false, sortable:false},
                {name:'des_cia',index:'des_cia', width: 310, editable:false, sortable:false},

                {name:'des_direcc',index:'des_direcc',  width: 280, editable:false, sortable:false, resizable: false},
                {name:'des_contacto',index:'des_contacto', editable:false, sortable:false, resizable: false},
                {name:'des_telf',index:'des_telef', editable:false, sortable:false, resizable: false},
                {name:'des_email',index:'des_email',  width: 220, editable:false, sortable:false, resizable: false},
                {name:'myac',index:'', width:80, fixed:true, sortable:false, resize:false, formatter:imageFormat},
            ],
            rowNum:30,
            rowList:[30,40,50],
            pager: pager_selector,
            sortname: 'Cve_Clte',
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

            var serie = rowObject[0];
            var correl = rowObject[4];
            var url = "x/?serie="+serie+"&correl="+correl;
            var url2 = "v/?serie="+serie+"&correl="+correl;
            $("#hiddencve_tipcia").val(serie);
            // la funcion downloadxml ejecuta el index.php que esta en la carpeta x pasandole la serie y el correlativo via get
            // la funcion viewPdf ejecuta el index.php que esta en la carpeta v pasandole la serie y el correlativo via get

            var html = '';
            //html += '<a href="#" onclick="ver(\''+serie+'\')"><i class="fa fa-eye" alt="Ver"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            if($("#permisos_editar").val() == 1)
            {
            html += '<a href="#" onclick="editar(\''+serie+'\')"><i class="fa fa-edit" alt="Editar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            }
            if($("#permisos_eliminar").val() == 1)
            {
            html += '<a href="#" onclick="borrar(\''+serie+'\')"><i class="fa fa-eraser" alt="Borrar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            }

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
                empresa :$("#cve_ciag").val(),
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

    /******************************** LLAMAA LOS DATOS - TIPO DE COMPAÑIA ****************************/

     /**************************************** FIN ****************************************/
    function borrar(_codigo) {

        swal({
            title: "¿Está seguro que desea borrar la este registro?",
            text: "Está a punto de borrar una sucrusal y esta acción no se puede deshacer",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Borrar",
            cancelButtonText: "Cancelar",
            closeOnConfirm: true
        },
        function(){
            $.ajax({
                url: '/api/sucursal/update/index.php',
                type: "POST",
                dataType: "json",
                data: {
                    id : _codigo,
                    action : "delete"
                },
                beforeSend: function(x) {
                    if(x && x.overrideMimeType) {
                        x.overrideMimeType("application/json;charset=UTF-8");
                    }
                },
            }).done(function(data){
                if(data.success){
                    ReloadGrid();
                    swal("Borrado", "La sucursal ha sido borrada exitosamente", "success");
                }else{
                    swal("Error", "Ocurrió un error al eliminar la sucursal", "error");
                }
            });
        });


    }



    function editar(_codigo) {
        $("#upload").show();
        $('.imageupload').imageupload('reset');
        $("#hiddenIDCompania").val(_codigo);
        $("#_title").html('<h3>Editar Sucursal</h3>');
        $("#emailMessage").html("");
        $("#CodeMessage").html("");
        $("#txtCveCompa").prop('disabled', true);
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                codigo : _codigo,
                action : "load"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                                    },
            url: '/api/sucursal/update/index.php',
            success: function(data) {
                console.log(data);
                if (data.success == true) {
                    $("#cve_cia").val(data.cve_cia);
                    $("#txtCveCompa").val(data.clave_sucursal);
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
                    $("#hiddenIDCompania").val(data.id);
                    $("#image").prop("src",data.imagen);
                    $("#image").prop("name",data.imagen);

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
        
        $('.imageupload').imageupload('reset');
        $("#imagen").val("");
        $("#upload").hide();
        $("#image").prop("src","");
        $("#_title").html('<h3>' +' Nueva Sucursal</h3>');
        $('#txtClaveCliente').prop('disabled', false);
        $("#txtCveCompa").prop('disabled', false);
        $("#txtDepart").val("");
        $("#txtMunicipio").val("");
        $("#txtCod").val("");
        $("#emailMessage").html("");
        $("#txtCveCompa").html("");
        $("#CodeMessage").html("");
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
    }

    var l = $( '.ladda-button' ).ladda();
    l.click(function() {

        $("#btnCancel").hide();

        l.ladda('start');
        if ($('#imagen').val()) {
            var path = $('#imagen').val();
            var filename = path.replace(/^.*\\/, "");
            uploadFile();
        } else if ($('#image').attr('src') != "" && !$('#imagen').val()) {
            var path = $('#image').attr("src");
            var filename = path.replace(/^.*\\/, "");			
        } else {
            filename = "noimage.jpg"
        }

        if ($("#txtCveCompa").val() && $("#txtDistrito").val() && $("#cve_cia").val() && $("#txtNomCompa").val()
            && $("#txtRutCompa").val() && $("#txtDirecc").val() && $("#txtTelef").val()

            && $("#txtContac").val() && $("#txtCorreo").val() && $("#txtComent").val() ) {
            if ($("#hiddenAction").val() == "add") {
                $.ajax({
                    type: "POST",
                    dataType: "json",
                    data: {
                        codigo: $("#txtClaveCliente").val(),
                        action: "exists"
                    },
                    beforeSend: function (x) {
                        if (x && x.overrideMimeType) {
                            x.overrideMimeType("application/json;charset=UTF-8");
                        }
                    },
                    url: '/api/sucursal/update/index.php',
                    success: function (data) {
                        if (data.success == false) {
                            $.post('/api/sucursal/update/index.php',
                                   {
                                cve_cia: $("#cve_cia").val(),
                                clave_sucursal: $("#txtCveCompa").val(),
                                distrito: $("#txtDistrito").val(),
                                des_cia: $("#txtNomCompa").val(),
                                des_rfc: $("#txtRutCompa").val(),
                                des_direcc: $("#txtDirecc").val(),
                                des_cp: $("#txtCod").val(),
                                des_telef: $("#txtTelef").val(),
                                des_contacto: $("#txtContac").val(),
                                des_email: $("#txtCorreo").val(),
                                des_observ: $("#txtComent").val(),
                                imagen: filename,
                                action: "add"
                            },
                                   function (response) {
                                console.log(response);
                            }, "json")
                                .always(function () {
                                l.ladda('stop');
                                $("#btnCancel").show();
                                cancelar()
                                ReloadGrid();
                            });
                        } else {
                            alert("Código ya Existe...");
                            $("#btnCancel").show();
                            l.ladda('stop');
                        }
                    }
                });
            } else {
                $.post('/api/sucursal/update/index.php',
                       {
                    id: $("#hiddenIDCompania").val(),
                    cve_cia: $("#cve_cia").val(),
                    clave_sucursal: $("#txtCveCompa").val(),
                    distrito: $("#txtDistrito").val(),
                    cve_tipcia: $("#TipoCompania").val(),
                    des_cia: $("#txtNomCompa").val(),
                    des_rfc: $("#txtRutCompa").val(),
                    des_direcc: $("#txtDirecc").val(),
                    des_cp: $("#txtCod").val(),
                    des_telef: $("#txtTelef").val(),
                    des_contacto: $("#txtContac").val(),
                    des_email: $("#txtCorreo").val(),
                    des_observ: $("#txtComent").val(),
                    imagen: filename,
                    action: "edit"
                },
                       function (response) {
                    console.log(response);
                }, "json")
                    .always(function () {
                    $(':input', '#myform')
                        .removeAttr('checked')
                        .removeAttr('selected')
                        .not(':button, :submit, :reset, :hidden, :radio, :checkbox')
                        .val('');
                    l.ladda('stop');
                    $("#btnCancel").show();
                    cancelar()
                    ReloadGrid();
                });
            }
        }else
        {
            $("#coModall").modal();
            setTimeout(function () {
                $("#coModall").modal("hide");
                l.ladda('stop');
            }, 3000);
        }
    });

</script>
<script>
    $(document).ready(function(){

        $(function() {
            $('.chosen-select').chosen();
            $('.chosen-select-deselect').chosen({ allow_single_deselect: true });
        });

        $("#inactivos").on("click", function(){
            $modal0 = $("#coModal");
            $modal0.modal('show');
            ReloadGrid1();
        });

        $( "#txtCod" ).change(function() {
            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    codigo : $("#txtCod").val(),
                    action : "getDane"
                },
                beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                                        },
                url: "/api/clientes/update/index.php",
                success: function(data) {
                    if (data.success == true) {
                        $("#txtDepart").val(data.departamento);
                        $("#txtMunicipio").val(data.municipio);
                    }
                }
            });
        });

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
        }else{
            $("#emailMessage").html("Por favor, ingresar un Correo Electrónico válido");
            $("#btnSave").prop('disabled', true);
        }
    });

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
                    sucursal : clave_empresa,
                    action : "exists"
                },
                beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                                        },
                url: '/api/sucursal/update/index.php',
                success: function(data) {
                    if (data.success == false) {
                        $("#CodeMessage").html("");
                        $("#btnSave").prop('disabled', false);
                    }else{
                        $("#CodeMessage").html(" Clave de sucursal ya existe");
                        $("#btnSave").prop('disabled', true);
                    }
                }

            });

        }else{
            $("#CodeMessage").html("Por favor, ingresar una Clave de Sucursal válida");
            $("#btnSave").prop('disabled', true);
        }
    });

    $("#txtCveCompa").keyup(function(e) {

        var claveCode = $(this).val();
        var claveCodeRegexp = new RegExp("^[a-zA-Z0-9]{1,20}$");

        if (claveCodeRegexp.test(claveCode)) {
            $("#CodeMessage").html("");
            $("#btnSave").prop('disabled', false);

        }else{
            $("#CodeMessage").html("Por favor, ingresar una Clave de Sucursal válida");
            $("#btnSave").prop('disabled', true);
        }
    });

    $("#txtCriterio").keyup(function(event){
        if(event.keyCode == 13){
            $("#buscarC").click();
        }
    });

</script>

<script type="text/javascript">

    //////////////////////////////////////////////////////////Aqui se contruye el Grid//////////////////////////////////////////////////////////
    $(function($) {
        var grid_selector = "#grid-table2";
        var pager_selector = "#grid-pager2";

        //resize to fit page size
        $(window).on("resize", function () {
            var $grid = $("#grid-table2"),
                newWidth = $grid.closest(".ui-jqgrid").parent().width();
            $grid.jqGrid("setGridWidth", newWidth, true);
        });
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
            url:'/api/sucursal/lista/index_i.php',
            datatype: "json",
            height: 250,
            shrinkToFit: true,
            postData: {
                criterio: $("#txtCriterio1").val()
            },
            mtype: 'POST',
            colNames:["ID",'Empresa','Clave de Sucursal','Nombre', "Dirección", "Responsable", "Teléfono", "Email", "Acciones"],/*'Calle y Numero','Colonia','Ciudad','Estado','Pais','Codigo Postal','RFC','Telefono1','Telefono2'*/
            colModel:[
                {name:'id',index:'id', width:60, sorttype:"int", editable: false,hidden:true},
                {name:'empresa',index:'empresa', width: 100, editable:false, sortable:false},
                {name:'clave_sucursal',index:'clave_sucursal', width: 130, editable:false, sortable:false},
                {name:'des_cia',index:'des_cia', width: 150, editable:false, sortable:false},

                {name:'des_direcc',index:'des_direcc',  width: 270, editable:false, sortable:false, resizable: false},
                {name:'des_contacto',index:'des_contacto', width: 130,editable:false, sortable:false, resizable: false},
                {name:'des_telf',index:'des_telef', width: 130, editable:false, sortable:false, resizable: false},
                {name:'des_email',index:'des_email',  width: 150, editable:false, sortable:false, resizable: false},
                {name:'myac',index:'', width:70, fixed:true, sortable:false, resize:false, formatter:imageFormat},

            ],
            rowNum:30,
            rowList:[30,40,50],
            pager: pager_selector,
            sortname: 'Cve_Clte',
            viewrecords: true,
            sortorder: "desc"
        });

        // Setup buttons
        $("#grid-table2").jqGrid('navGrid', '#grid-pager2',
                                 {edit: false, add: false, del: false, search: false},
                                 {height: 200, reloadAfterSubmit: true}
                                );


        $(window).triggerHandler('resize.jqGrid');//trigger window resize to make the grid get the correct size
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        //////////////////////////////////////////////////////////aqui se construye la funcion de cada item del grid//////////////////////////////////////////////////////////
        function imageFormat( cellvalue, options, rowObject ){
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
            html += '<a href="#" onclick="recovery(\''+id_empresa+'\')"><i class="fa fa-check" alt="Borrar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';

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
    function ReloadGrid1() {
        $('#grid-table2').jqGrid('clearGridData')
            .jqGrid('setGridParam', {postData: {
                criterio: $("#txtCriterio1").val(),
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

    function recovery(_codigo) {
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                id : _codigo,
                action : "recovery"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                                    },
            url: '/api/sucursal/update/index.php',
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

    <?php if(isSCTP()): ?>
        function obtenerSucursalesSCTP(){
            $("#modal_sctp .fa-spinner").show();
            $("#modal_sctp .success").hide();
            $("#modal_sctp #button_modal_sctp").attr('disabled', 'disabled');
            $("#modal_sctp").modal('show');
            $.ajax({
                url: '/api/synchronize/sctp.php',
                method: 'POST',
                dataType: 'json',
                data: {
                    action: 'sucursalesSCTP'
                }
            }).done(function(data){
                if(data.success){
                    ReloadGrid();
                    $("#modal_sctp .fa-spinner").hide();
                    $("#modal_sctp .success").show();
                    $("#modal_sctp #button_modal_sctp").removeAttr('disabled');

                }else{
                    $("#modal_sctp").modal('hide');
                    swal("Error", data.error, "error");
                }
            });
        }
    <?php endif; ?>

    <?php if(isLaCentral()): ?>
        function obtenerSucursalesLaCentral(){
            $("#modal_lacentral .fa-spinner").show();
            $("#modal_lacentral .success").hide();
            $("#modal_lacentral #button_modal_lacentral").attr('disabled', 'disabled');
            $("#modal_lacentral").modal('show');
            $.ajax({
                url: '/api/synchronize/lacentral.php',
                method: 'POST',
                dataType: 'json',
                data: {
                    action: 'sucursalesLaCentral'
                }
            }).done(function(data){
                if(data.success){
                    ReloadGrid();
                    $("#modal_lacentral .fa-spinner").hide();
                    $("#modal_lacentral .success").show();
                    $("#modal_lacentral #button_modal_lacentral").removeAttr('disabled');

                }else{
                    $("#modal_lacentral").modal('hide');
                    swal("Error", data.error, "error");
                }
            });
        }
    <?php endif; ?>

</script>


<style>

<?php if($edit[0]['Activo']==0){ ?>
    .fa-edit{
        display: none;
    }
<?php } ?>
<?php if($borrar[0]['Activo']==0){ ?>
    .fa-eraser{
        display: none;
    }
<?php } ?>

</style>