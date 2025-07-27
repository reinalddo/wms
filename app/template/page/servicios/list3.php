<?php 

$mod=30;
$var1=77;
$var2=78;
$var3=79;
$var4=80;

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

$ac_unidades_medida = \db()->prepare("INSERT INTO c_unimed (cve_umed, des_umed, mav_cveunimed, Activo) (SELECT um.cve_umed, um.des_umed, um.mav_cveunimed, um.Activo FROM (
SELECT 'Pz' AS cve_umed, 'Pieza' AS des_umed, 'H87' AS mav_cveunimed, 1 AS Activo FROM DUAL
UNION
SELECT 'Caja' AS cve_umed, 'Caja' AS des_umed, 'XBX' AS mav_cveunimed, 1 AS Activo FROM DUAL
UNION
SELECT 'KGM' AS cve_umed, 'Kilos' AS des_umed, 'KGM' AS mav_cveunimed, 1 AS Activo FROM DUAL
UNION
SELECT 'KT' AS cve_umed, 'KIT' AS des_umed, 'KT' AS mav_cveunimed, 1 AS Activo FROM DUAL
UNION
SELECT 'LTR' AS cve_umed, 'Litros' AS des_umed, 'LTR' AS mav_cveunimed, 1 AS Activo FROM DUAL
UNION
SELECT 'BULTO' AS cve_umed, 'Bulto' AS des_umed, 'BULTO' AS mav_cveunimed, 1 AS Activo FROM DUAL
UNION
SELECT 'MTR' AS cve_umed, 'Metros' AS des_umed, 'MTR' AS mav_cveunimed, 1 AS Activo FROM DUAL
UNION
SELECT 'PR' AS cve_umed, 'Par' AS des_umed, 'PR' AS mav_cveunimed, 1 AS Activo FROM DUAL
) AS um WHERE IFNULL(um.mav_cveunimed, '') NOT IN (SELECT IFNULL(mav_cveunimed, '') FROM c_unimed)) ON DUPLICATE KEY UPDATE mav_cveunimed = um.mav_cveunimed
");
$ac_unidades_medida->execute();


$unimed = \db()->prepare("SELECT * FROM c_unimed");
$unimed->execute();
$listUnimed = $unimed->fetchAll(PDO::FETCH_ASSOC);

$GrupoServicio = \db()->prepare("SELECT * FROM c_gposervicios WHERE Cve_GpoServicio = 'G03'");
$GrupoServicio->execute();
$gs = $GrupoServicio->fetch();
$grupo = "";
$grupo_id = "";
if($gs['Cve_GpoServicio'] != '')
{
$grupo = "(".$gs['Cve_GpoServicio'].") - ".$gs['Des_GpoServicio'];
$grupo_id = $gs['Id_GpoServicio'];
}

?>
<link href="/css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">

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

<input type="hidden" name="id_servicio" id="id_servicio" value="">
<input type="hidden" id="grupo_serv_id" value="<?php echo $grupo_id; ?>">

<div class="modal inmodal" id="myModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content animated bounceInRight">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <i class="fa fa-laptop modal-icon"></i>
                <h4 class="modal-title" id="modaltitle">Agregar Servicio</h4>
            </div>
            <form id="myform">
                <div class="modal-body">

                    <label>Clave</label><input id="Clave" type="text" maxlength="20" placeholder="Clave" class="form-control" required="true"><br>
                    <label>Descripción</label><input id="descripcion" type="text" placeholder="Descripción" class="form-control" required="true"><br>
                    <label>Unidad de Medida</label>
                    <select class="chosen-select form-control" id="unimedida_list" required="true">
                        <option value="">Seleccione una Unidad de Medida</option>
                        <?php foreach( $listUnimed AS $unimed ): ?>
                            <option value="<?php echo $unimed['id_umed']; ?>"><?php echo "(".$unimed['cve_umed'].") ".$unimed['des_umed']; ?></option>
                        <?php endforeach; ?>
                    </select><br>
                    <label>Grupo de Servicio</label><input id="gruposerv_text" type="text" class="form-control" value="<?php echo $grupo; ?>" readonly="readonly"><br>
                    <input type="hidden" id="hiddenAction">
                    <input type="hidden" id="hiddenCveumed">
                </div>
                <div class="modal-footer">
                    <span id="CodeMessage"></span>
                    <button type="button" class="btn btn-white" data-dismiss="modal" id="btnCancel">Cerrar</button>
                    <button type="submit" class="btn btn-primary ladda-button" id="btnSave">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style type="text/css">

</style>

<div class="wrapper wrapper-content  animated fadeInRight">

    <h3>Servicios</h3>

    <div class="row">
        <div class="col-md-12">
            <div class="ibox ">
                <div class="ibox-title">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="input-group">
                                <input type="text" class="form-control" name="txtCriterio" id="txtCriterio" placeholder="Buscar...">
                                <div class="input-group-btn">
                                    
                                    <button onclick="ReloadGrid()" type="submit" id="buscarA" class="btn btn-primary">
                                        Buscar
                                    </button>
                                    
                                </div>
                            </div>

                        </div>
                        <div class="col-md-8">
                            <style>

                               

                            </style>

                            <a href="#" class="" onclick="agregar()"><button class="btn btn-primary" type="button"><i class="fa fa-plus"></i>&nbsp;&nbsp;Nuevo</button></a>


                            <!--<a href="/api/v2/unidades-medida/exportar" target="_blank" class="btn btn-primary pull-right" style="margin-left:15px;"><span class="fa fa-upload"></span> Exportar</a>-->
                            <!--<button class="btn btn-primary pull-right" style="margin-left:15px;" data-toggle="modal" data-target="#importar"><span class="fa fa-download"></span> Importar</button>-->

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
                    <h4 class="modal-title">Recuperar servicio</h4>
                </div>
                <div class="modal-body">
                    <div class="col-md-4">
                        <div class="input-group">
                            <input type="text" class="form-control input-sm" name="txtCriterio" id="txtCriterio1" placeholder="Buscar...">
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



    <div class="modal fade" id="importar" role="dialog">
        <div class="vertical-alignment-helper">
            <div class="modal-dialog vertical-align-center">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Importar Servicio</h4>
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
            url: '/servicios/importar',
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
            url:'/api/servicios/lista/index.php',
            datatype: "json",
            shrinkToFit: false,
            height:'auto',
            postData: {
                id_grupo: $("#grupo_serv_id").val(),
                criterio: $("#txtCriterio").val()
            },
            mtype: 'POST',
            colNames:["Acciones", 'ID', 'Clave','Descripción', 'Unidad Medida', 'Grupo'],
            colModel:[
                //{name:'id',index:'id', width:60, sorttype:"int", editable: false},
                {name:'myac',index:'', width:100, fixed:true, sortable:false, resize:false, formatter:imageFormat},
                {name:'id',index:'id',width:100, editable:false, sortable:false, hidden: true},
                {name:'clave',index:'clave',width:150, editable:false, sortable:false},
                {name:'descripcion',index:'descripcion',width:350, editable:false, sortable:false},
                {name:'unimed',index:'unimed',width:150, editable:false, sortable:false},
                {name:'grupo',index:'grupo',width:150, editable:false, sortable:false},
            ],
            loadComplete: function(data){console.log("SUCCESS", data);},
            loadError: function(data){console.log("ERROR", data);},
            rowNum:30,
            rowList:[30,40,50],
            pager: pager_selector,
            sortname: 'clave',
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
            var serie = rowObject[1];
            var correl = rowObject[4];
            var url = "x/?serie="+serie+"&correl="+correl;
            var url2 = "v/?serie="+serie+"&correl="+correl;
            $("#hiddenCveumed").val(serie);
            // la funcion downloadxml ejecuta el index.php que esta en la carpeta x pasandole la serie y el correlativo via get
            // la funcion viewPdf ejecuta el index.php que esta en la carpeta v pasandole la serie y el correlativo via get

            var html = '';
            //html += '<a href="#" onclick="ver(\''+serie+'\')"><i class="fa fa-eye" alt="Ver"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            //if($("#permiso_editar").val() == 1)
            html += '<a href="#" onclick="editar(\''+serie+'\')"><i class="fa fa-edit" alt="Editar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            //if($("#permiso_eliminar").val() == 1)
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
            url:'/api/servicios/lista/index_i.php',
            datatype: "json",
            height: 250,
            postData: {
                criterio: $("#txtCriterio").val()
            },
            mtype: 'POST',
            colNames:['Clave','Descripción',"Acciones"],
            colModel:[
                //{name:'id',index:'id', width:60, sorttype:"int", editable: false},
                {name:'cve_umed',index:'cve_umed',width:310, editable:false, sortable:false},
                {name:'des_umed',index:'des_umed',width:600, editable:false, sortable:false},
                {name:'myac',index:'', width:80, fixed:true, sortable:false, resize:false, formatter:imageFormat},
            ],
            rowNum:10,
            rowList:[10,20,30],
            pager: pager_selector,
            sortname: 'cve_umed',
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
            $("#hiddenCveumed").val(serie);
            // la funcion downloadxml ejecuta el index.php que esta en la carpeta x pasandole la serie y el correlativo via get
            // la funcion viewPdf ejecuta el index.php que esta en la carpeta v pasandole la serie y el correlativo via get

            var html = '';
            //html += '<a href="#" onclick="ver(\''+serie+'\')"><i class="fa fa-eye" alt="Ver"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            html += '<a href="#" onclick="recovery(\''+serie+'\')"><i class="fa fa-check" alt="Recuperar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            //html += '<a href="#" onclick="borrar(\''+serie+'\')"><i class="fa fa-eraser" alt="Borrar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';

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

    function borrar(_codigo) {

        swal({
            title: "¿Está seguro que desea borrar este servicio?",
            text: "",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Borrar",
            cancelButtonText: "Cancelar",
            closeOnConfirm: true
        },

             function(){
            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    clave : _codigo,
                    action : "delete"
                },
                beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                                        },
                url: '/api/servicios/update/index.php',
                success: function(data) {
                    if (data.success == true) {
                        //$('#codigo').prop('disabled', true);
                        ReloadGrid();
                    }
                }
            });

        });
    }
    function Solo_Numerico(variable){
        Numer=parseInt(variable);
        if (isNaN(Numer)){
            return "";
        }
        return Numer;
    }
    function ValNumero(Control){
        Control.value=Solo_Numerico(Control.value);
    }

    function editar(_codigo) {
        $("#id_servicio").val(_codigo);
        $("#modaltitle").text("Editar Servicio");
        $("#CodeMessage").html("");
        $("#Clave").prop("disabled",true);

        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                clave : _codigo,
                action : "load"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                                    },
            url: '/api/servicios/update/index.php',
            success: function(data) {
                if (data.success == true) {
                    //$('#codigo').prop('disabled', true);
                    $("#Clave").val(data.clave);
                    $("#descripcion").val(data.descripcion);
                    $("#unimedida_list").val(data.unimed);
                    l.ladda('stop');
                    $("#btnCancel").show();
                    $modal0 = $("#myModal");
                    $modal0.modal('show');
                    $("#hiddenAction").val("edit");
                }
            },
            error: function(data){console.log("ERROR", data);}
        });
    }

    $("#Clave").keyup(function(e){

    var valor = $(this).val().trim();
    
    $(this).val(valor);

    });

    $("#Clave").bind({
        paste : function(){
            console.log("paste");
    var valor = $(this).val().trim();
    
    $(this).val(valor);
        }
    });
    function agregar() {
        $("#CodeMessage").html("");
        $("#modaltitle").text("Agregar Servicio");
        $("#Clave").prop("disabled",false);
        $modal0 = $("#myModal");
        $modal0.modal('show');
        l.ladda('stop');
        //$('#codigo').prop('disabled', false);
        $("#hiddenAction").val("add");
        $("#btnCancel").show();
        $("#Clave").val("");
        $("#descripcion").val("");
    }

    var l = $( '.ladda-button' ).ladda();
    l.click(function() {

        if ($("#Clave").val()=="" || $("#descripcion").val()=="" || $("#unimedida_list").val()=="") {
            swal("Aún Hay Campos Vacíos", "Por favor llene todos los datos", "error");
            return;
        }

        $("#btnCancel").hide();

        l.ladda( 'start' );

        $.post('/api/servicios/update/index.php',
               {
            id: $("#id_servicio").val(),
            //id_almacen: '<?php echo $_SESSION['id_almacen']; ?>',
            Clave : $("#Clave").val(),
            descripcion : $("#descripcion").val(),
            unimedida: $("#unimedida_list").val(),
            gpo_id: $("#grupo_serv_id").val(),
            action : $("#hiddenAction").val()
        },
               function(response){
            console.log(response);
        }, "json")
            .always(function() {
            //$("#Des_umed").val("");
            l.ladda('stop');
            $("#btnCancel").show();
            $modal0.modal('hide');
            ReloadGrid();
        });
    });

    $("#Clave").keyup(function(e) {

        var claveCode = $(this).val();
        var claveCodeRegexp = new RegExp("^[a-zA-Z0-9]{1,20}$");

        //console.log("KEY = ", e.key);
        //if (claveCodeRegexp.test(claveCode) || claveCode.indexOf('-') > -1 || claveCode.indexOf('_') > -1) {
            $("#CodeMessage").html("");
            $("#btnSave").prop('disabled', false);

            var cve_umed = $(this).val();

            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    cve_umed : cve_umed,
                    action : "exists"
                },
                beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                                        },
                url: '/api/servicios/update/index.php',
                success: function(data) {
                    if (data.success == false) {
                        $("#CodeMessage").html("");
                        $("#btnSave").prop('disabled', false);
                    }else{
                        $("#CodeMessage").html(" Clave de Servicio ya existe");
                        $("#btnSave").prop('disabled', true);
                    }
                }

            });

        //}else{
        //    $("#CodeMessage").html("Por favor, ingresar una Clave de Servicio válida");
        //    $("#btnSave").prop('disabled', true);
        //}


    });

    $("#inactivos").on("click", function(){
        $modal0 = $("#coModal");
        $modal0.modal('show');
    });


    function recovery(_codigo) {
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                cve_umed : _codigo,
                action : "recovery"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                                    },
            url: '/api/unidadesmedida/update/index.php',
            success: function(data) {
                if (data.success == true) {
                    //$('#codigo').prop('disabled', true);
                    ReloadGrid();
                    ReloadGrid1();
                }
            }
        });
    }

    $('#btn-layout').on('click', function(e) {
        //console.log("Layout_servicios", window.location.href, '/Layout/Layout_servicios.xlsx');
        window.location.href = '/Layout/Layout_Contactos.xlsx';
    }); 

    $("#txtCriterio").keyup(function(event){
        if(event.keyCode == 13){
            $("#buscarA").click();
        }
    });

    $(document).ready(function(){
        $("div.ui-jqgrid-bdiv").css("max-height",$("#list").height()-80);
    });
</script>

<?php /* if($edit[0]['Activo']==0){?>

.fa-edit{

    display: none;

}

<?php }?>


<?php if($borrar[0]['Activo']==0){?>

.fa-eraser{

    display: none;

}

<?php } */?>