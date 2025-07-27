<?php
$listaAP = new \AlmacenP\AlmacenP();

$listaNCompa = new \Companias\Companias();
$listaT = new \TipoTransporte\TipoTransporte();

$listaTransportadoras = new \Proveedores\Proveedores();

$vere = \db()->prepare("select * from t_profiles as a where id_menu=23 and id_submenu=53 and id_role='".$_SESSION["perfil_usuario"]."'");
$vere->execute();
$ver = $vere->fetchAll(PDO::FETCH_ASSOC);


$agre = \db()->prepare("select * from t_profiles as a where id_menu=23 and id_submenu=54 and id_role='".$_SESSION["perfil_usuario"]."'");
$agre->execute();
$ag = $agre->fetchAll(PDO::FETCH_ASSOC);


$edita = \db()->prepare("select * from t_profiles as a where id_menu=23 and id_submenu=55 and id_role='".$_SESSION["perfil_usuario"]."'");
$edita->execute();
$edit = $edita->fetchAll(PDO::FETCH_ASSOC);


$borra = \db()->prepare("select * from t_profiles as a where id_menu=23 and id_submenu=56 and id_role='".$_SESSION["perfil_usuario"]."'");
$borra->execute();
$borrar = $borra->fetchAll(PDO::FETCH_ASSOC);

?>
<link href="/css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">
<link href="/css/plugins/ladda/select2.css" rel="stylesheet"/>
<link href="/css/plugins/awesome-bootstrap-checkbox/awesome-bootstrap-checkbox.css" rel="stylesheet">
<link href="/css/plugins/iCheck/custom.css" rel="stylesheet">


<!-- Mainly scripts -->
<script src="/js/jquery-2.1.1.js"></script>
<script src="/js/bootstrap.min.js"></script>
<script src="/js/plugins/metisMenu/jquery.metisMenu.js"></script>
<script src="/js/plugins/slimscroll/jquery.slimscroll.min.js"></script>
<link href="/css/plugins/sweetalert/sweetalert.css" rel="stylesheet">

<!-- Input Mask--
<script src="/js/plugins/jasny/jasny-bootstrap.min.js"></script>-->
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

<!-- TouchSpin -->
<script src="/js/plugins/touchspin/jquery.bootstrap-touchspin.min.js"></script>
<!-- Select -->
<script src="/js/select2.js"></script>
<!-- iCheck -->
<script src="/js/plugins/iCheck/icheck.min.js"></script>

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

<style type="text/css">

</style>

<div class="wrapper wrapper-content  animated fadeInRight" id="list">

    <h3>Transportes</h3>

    <div class="row">
        <div class="col-md-12">
            <div class="ibox ">
                <div class="ibox-title">

                <div class="row" style="margin:15px 0">
                    <div class="col-md-3" style="padding-left: 0;">
                        <label>Almacen</label>
                        <select class="chosen-select form-control" id="almacen">
                            <option value="">Seleccione un almacen</option>
                            <?php foreach( $listaAP->getAll() AS $a ): ?>
                                <option value="<?php echo $a->id; ?>"><?php echo $a->nombre; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="input-group">
                                <input type="text" class="form-control" name="txtCriterio" id="txtCriterio" placeholder="Buscar...">
                                <div class="input-group-btn">
                                     
                                        <button onclick="ReloadGrid()" type="submit" id="buscarA" class="btn btn-primary">
                                            <span class="fa fa-search"></span> Buscar
                                        </button>
                                    
                                </div>
                            </div>

                        </div>
                        <div class="col-md-8">


                            <a href="#" class="permiso_registrar" onclick="agregar()"><button class="btn btn-primary" type="button"><i class="fa fa-plus"></i>&nbsp;&nbsp;Nuevo</button></a>
                            
                            
                            <a href="/api/v2/transporte/exportar" target="_blank" class="btn btn-primary pull-right" style="margin-left:15px"><span class="fa fa-upload"></span> Exportar</a>
                            <button class="btn btn-primary pull-right permiso_registrar" style="margin-left:15px" data-toggle="modal" data-target="#importar"><span class="fa fa-download"></span> Importar</button>

                            
							<button  class="btn btn-primary pull-right" type="button" id="inactivos"><i class="fa fa-search"></i>&nbsp;&nbsp;Transportes inactivos</button>
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
                            <h3>Agregar Transporte</h3>
                        </div>
                    </div>
                </div>
                <form id="myform">
                    <input type="hidden" id="hiddenAction" name="hiddenAction">
                    <div class="ibox-content">
                        <div class="row">

                            <div class="col-md-6 b-r">
                                
                                <div class="form-group">
                                    <label>Almacen</label>
                                    <select class="chosen-select form-control" id="almacen_reg" required>
                                        <option value="">Seleccione un almacen</option>
                                        <?php foreach( $listaAP->getAll() AS $a ): ?>
                                            <option value="<?php echo $a->id; ?>"><?php echo $a->nombre; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                
                                    <label>Clave de Transporte *</label>
                                    <input id="clave" type="text" placeholder="Clave de Transporte" class="form-control" required="true">
                                    <label id="CodeMessage" style="color:red;">
                                </div>

                                <div class="form-group">
                                
                                    <label>Descripción del Transporte *</label>
                                    <input id="descrip_transporte" type="text" placeholder="Descripción del Transporte" class="form-control" required="true">
                                    <!--<label id="CodeMessage" style="color:red;">-->
                                </div>

                                <div class="row">
                                <div class="col-xs-12">
								<div class="form-group">
                                    <label>Empresa *</label>
                                    <select name="country" id="cve_cia"  class="form-control" required="true">
									 <option value="">Seleccione Empresa</option>
                                        <?php foreach( $listaTransportadoras->getTransportadoras() AS $p ): ?>
                                            <option value="<?php echo $p->ID_Proveedor; ?>"><?php echo $p->Nombre; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                </div>

                                <div class="col-xs-6" style="display: none;">
                                <div class="form-group">

                                    <label for="transportista" style="font-weight: 600;font-size: 14px; -moz-user-select: none; -webkit-user-select: none;-ms-user-select: none; user-select: none;margin-left: 30px;margin-top: 30px;cursor: pointer;">
                                    <input type="checkbox" name="transportista" id="transportista" value="0">  Es Transportista</label>

                                </div>
                                </div>
                                </div>

								
								  <div class="form-group">
                                    <label>Tipo de Transporte *</label>
                                    <select name="country" id="tipo_transporte" class="form-control" required="true">
										 <option value="">Seleccione Tipo de Transporte</option>
                                        <?php foreach( $listaT->getAll() AS $p ): ?>
                                            <option value="<?php echo $p->clave_ttransporte; ?>"><?php echo $p->desc_ttransporte; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group">
									
									
									 <label>Placas *</label>
                                    <input id="Placas" type="text" placeholder="Placas" class="form-control" required="true">
									
                                </div>
                                <div class="form-group">
                                    <label>Número Económico *</label>
                                    <input id="Nombre" type="text" placeholder="Número Económico" class="form-control" required="true">
									<!--<label id="CodeMessage2" style="color:red;">-->
                                </div>
                              								

                                
                            </div>
                            <div class="col-md-6">                                
                                
								<div class="form-group">
								<label>Imagen tipo transporte</label>
                                   <img src="" class="img img-responsive" id="imagen" alt="tipo transporte"/>
                                </div>
                                <div class="col-md-4 form-group">
                                    <label>Alto (mm)</label>
                                    <input id="alto" type="text" placeholder="Alto" disabled class="form-control">
                                </div>
                                <div class="col-md-4 form-group">
                                    <label>Ancho (mm)</label>
                                    <input id="ancho" type="text" placeholder="Ancho" disabled class="form-control">
                                </div>
								
								  <div class="col-md-4 form-group">
                                    <label>Fondo (mm)</label>
                                    <input id="fondo" type="text" placeholder="Fondo" disabled class="form-control">
                                </div>
								
								<div class="form-group">
                                    <label>Capacidad Máxima (kg)</label>
                                    <input id="capacidad_carga" type="text" value="" placeholder="Capacidad de Carga (TN)" disabled class="form-control">
                                </div>
								
								<div class="form-group">
                                    <label>Capacidad Volumetrica (m3)</label>
                                    <input id="capacidad_volumetrica" type="text" disabled placeholder="Capacidad" class="form-control">
                                </div>


                                <div class="form-group col-md-4" style="padding-left: 0;">
                                        <label>Transporte Externo</label>
                                        <select class="form-control" name="transporte_externo" id="transporte_externo">
                                            <option value="0">No</option>
                                            <option value="1">Si</option>
                                        </select>
                                </div>

                                <div class="pull-right"><br>
                                    <a href="#" onclick="cancelar()"><button type="button" class="btn btn-white" id="btnCancel">Cerrar</button></a>&nbsp;&nbsp;
                                    <button type="submit" class="btn btn-primary ladda-button" id="btnSave">Guardar</button>
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

<div class="modal fade" id="coModal" role="dialog">
    <div class="vertical-alignment-helper">
        <div class="modal-dialog vertical-align-center modal-lg" style="width: 1200px!important;">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Recuperar Transporte</h4>
                </div>
                <div class="modal-body">
                    <div class="col-md-4">
                        <div class="input-group">
                            <input type="text" class="form-control input-sm" name="txtCriterio" id="txtCriterio1" placeholder="Buscar por Transporte...">
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
                        <h4 class="modal-title">Importar Transportes</h4>
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
                        $('#almacen_reg').val(data.codigo.id);
                        
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
            url: '/transporte/importar',
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
            url:'/api/transporte/lista/index.php',
            datatype: "json",
            shrinkToFit: false,
			height:'auto',
            postData: {
                almacen: $("#almacen").val(),
                criterio: $("#txtCriterio").val()
            },
            mtype: 'POST',
            colNames:['Acciones', 'Clave', 'Descripción','Placas','Núm. Eco','Transporte Externo', 'Tipo de Transporte','Capacidad Carga (Kgs)','Capacidad Volumetrica(m3)', 'Almacén', 'Empresa'],
            colModel:[
                //{name:'id',index:'id', width:60, sorttype:"int", editable: false},
                 
                {name:'myac',index:'', width:80, fixed:true, sortable:false, resize:false, formatter:imageFormat},
                {name:'ID_Transporte',index:'ID_Transporte',width:80, editable:false, sortable:false},
                {name:'Nombre',index:'Nombre',width:110, editable:false, sortable:false},
                {name:'Placas',index:'Placas',width:110, editable:false, sortable:false},
				{name:'num_ec',index:'num_ec',width:110, editable:false, sortable:false},
                {name:'externo',index:'externo',width:200, editable:false, sortable:false},
                {name:'desc_ttransporte',index:'desc_ttransporte',width:200, editable:false, sortable:false},
				{name:'capacidad_carga',index:'capacidad_carga',width:150, editable:false, sortable:false},
				{name:'capacidad_volumetrica',index:'capacidad_volumetrica',width:180, editable:false, sortable:false},
                {name:'almacen', index: 'almacen', width: 200, editable: false, sortable: false},
                {name:'des_cia',index:'des_cia',width:250, editable:false, sortable:false},
            
            ],
        
            rowNum:30,
            rowList:[30,40,50],
            loadComplete: function(data){console.log("data = ", data);},
            pager: pager_selector,
            sortname: 'ID_Transporte',
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
            var correl = rowObject[5];
            var url = "x/?serie="+serie+"&correl="+correl;
            var url2 = "v/?serie="+serie+"&correl="+correl;
            $("#hiddenTransporte").val(serie);

            // la funcion downloadxml ejecuta el index.php que esta en la carpeta x pasandole la serie y el correlativo via get
            // la funcion viewPdf ejecuta el index.php que esta en la carpeta v pasandole la serie y el correlativo via get

            var html = '';
            //html += '<a href="#" onclick="ver(\''+serie+'\')"><i class="fa fa-eye" alt="Ver"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            if($("#permiso_editar").val() == 1)
            {
            html += '<a href="#" onclick="editar(\''+serie+'\')"><i class="fa fa-edit" alt="Editar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            }
            if($("#permiso_eliminar").val() == 1)
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
                almacen: $("#almacen").val(),
                criterio: $("#txtCriterio").val()
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
            title: "¿Está seguro que desea borrar el transporte?",
            text: "Está a punto de borrar un transporte y esta acción no se puede deshacer",
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
                ID_Transporte : _codigo,
                action : "delete"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/transporte/update/index.php',
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

    function editar(_codigo) {
		$("#CodeMessage").html("");
		$("#CodeMessage2").html("");
        $("#hiddenTransporte").val(_codigo);
		$('#clave').prop("disabled",true);
        $("#_title").html('<h3>Editar Transporte</h3>');

        //var transportista = 0;
        //if($("#transportista").is(':checked'))
            //transportista = 1;

        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                ID_Transporte : _codigo,
                action : "load"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/transporte/update/index.php',
            success: function(data) {
                console.log("data edit = ", data);
                if (data.success == true) {
                   
                    $("#almacen_reg").val(data.almacen);
                    $("#clave").val(data.ID_Transporte);
                    $("#descrip_transporte").val(data.Nombre);
                    $("#Nombre").val(data.num_ec);
                    $("#Placas").val(data.Placas);
                    $("#cve_cia").val(data.cve_cia).change();
                    $("#tipo_transporte").val(data.tipo_transporte).change();
                    $("#transporte_externo").val(data.transporte_externo).change();

            /*
                    if(data.es_transportista == 1)
                    {
                        $("#transportista").prop("checked", true);
                    }
            */
                    l.ladda('stop');
                    $("#btnCancel").show();

    
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

    }

    function agregar() {
		$("#CodeMessage").html("");
		$("#CodeMessage2").html("");
		$('#clave').val("");
		$('#Nombre').val("");
		$('#Placas').val("");
		$('#cve_cia').val("");
		$('#imagen').prop("src","../img/foto_tipo_transporte/noimage.jpg");
		$('#tipo_transporte').val();
		$('#clave').prop("disabled",false);
        $("#_title").html('<h3>Agregar Transporte</h3>');
        $(':input','#myform')
            .removeAttr('checked')
            .removeAttr('selected')
            .not(':button, :submit, :reset, :hidden, :radio, :checkbox')
            .val('');

        $('#list').hide();

        $('#FORM').show();
        $('#FORM').removeAttr('class').attr('class', '');
        $('#FORM').addClass('animated');
        $('#FORM').addClass("fadeInRight");
        $("#hiddenAction").val("add");
        $("#hiddenTransporte").val("0");
        
    }

    var l = $( '.ladda-button' ).ladda();
    l.click(function() {
		  
		    if ($("#clave_ttransporte").val()=="") {return;}
			    if ($("#cve_cia").val()=="") {return;}
                    if ($("#descrip_transporte").val()=="") {return;}
                    if ($("#tipo_transporte").val()=="") {return;}
					    if ($("#Placas").val()=="") {return;}
                            if ($("#Nombre").val()=="") {return;}
                            if ($("#almacen_reg").val()=="") {return;}
                            if ($("#clave").val()=="") {return;}

        $("#btnCancel").hide();

        l.ladda( 'start' );

            console.log("ID_Transporte: ",  $("#clave").val());
            console.log("des_tr: ",  $("#descrip_transporte").val());
            console.log("num_ec: ",  $("#Nombre").val());
            console.log("Placas: ",  $("#Placas").val());
            console.log("cve_cia: ",  $("#cve_cia").val());
            console.log("tipo_transporte: ",  $("#tipo_transporte").val());
            console.log("almacen: ",  $("#almacen_reg").val());
            console.log("action:  ",  $("#hiddenAction").val());

            
        //var transportista = 0;
        //if($("#transportista").is(':checked'))
            //transportista = 1;


            $.post('/api/transporte/update/index.php',
                {
                    ID_Transporte: $("#clave").val(),
                    des_tr: $("#descrip_transporte").val(),
                    //transportista: transportista,
                    num_ec: $("#Nombre").val(),
                    Placas: $("#Placas").val(),
                    cve_cia: $("#cve_cia").val(),
                    tipo_transporte: $("#tipo_transporte").val(),
                    transporte_externo: $("#transporte_externo").val(),
                    almacen: $("#almacen_reg").val(),
                    action : $("#hiddenAction").val()
                },
                function(response){
                    console.log(response);
                }, "json")
                .always(function(data) {
                    console.log(data);
                    if(!data.countPlaca && !data.countClave)
                    {
                        l.ladda('stop');
                        $("#btnCancel").show();
                        cancelar()
                        ReloadGrid();
    					ReloadGrid1();
                    }
                    else
                    {
                        if(data.countPlaca)
                            swal("Error", "La Placa ya existe", "error");
                        if(data.countClave)
                            swal("Error", "La Clave del transporte ya existe", "error");
                    }
                });
    });

</script>

<script>
    $(document).ready(function(){
        $("#compania_edit").select2({

        });

        $("#inactivos").on("click", function(){
            $modal0 = $("#coModal");
            $modal0.modal('show');
        });
	//	$("div.ui-jqgrid-bdiv").css("max-height",$("#list").height()-80);
    });
</script>

<script>
    $(document).ready(function(){
        $("#txtNomCompa").select2();

        $("#minimum2").select2({
            minimumInputLength: 2
        });

    });
</script>

<script>
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
            url:'/api/transporte/lista/index_i.php',
            datatype: "json",
            shrinkToFit: false,
            height: 350,
            postData: {
                criterio: $("#txtCriterio1").val()
            },
			mtype: 'POST',
            colNames:['Clave','Empresa','Tipo de Transporte','Placas','Núm. Eco','Cap. Kgs','Cap. Vol(m3)','Recuperar'],
            colModel:[
                //{name:'id',index:'id', width:60, sorttype:"int", editable: false},

                 
                {name:'ID_Transporte',index:'ID_Transporte',width:100, editable:false, sortable:false},
				{name:'des_cia',index:'des_cia',width:250, editable:false, sortable:false},
                {name:'desc_ttransporte',index:'desc_ttransporte',width:150, editable:false, sortable:false},
                {name:'Placas',index:'Placas',width:100, editable:false, sortable:false},
				{name:'Nombre',index:'Nombre',width:100, editable:false, sortable:false},
				{name:'capacidad_carga',index:'capacidad_carga',width:100, editable:false, sortable:false},
				{name:'capacidad_volumetrica',index:'capacidad_volumetrica',width:140, editable:false, sortable:false},
                {name:'myac',index:'', width:100, fixed:true, sortable:false, resize:false, formatter:imageFormat},
            ],
            rowNum:10,
            rowList:[10,20,30],
            pager: pager_selector,
            sortname: 'ID_Transporte',
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
            var serie = rowObject[1];
            var correl = rowObject[5];
            var url = "x/?serie="+serie+"&correl="+correl;
            var url2 = "v/?serie="+serie+"&correl="+correl;

			var IDTransp = rowObject[2];
			$("#hiddenTransporte").val(IDTransp);

            
            var html = '';
            //html += '<a href="#" onclick="ver(\''+serie+'\')"><i class="fa fa-eye" alt="Ver"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            html += '<a href="#" onclick="recovery(\''+serie+'\')"><i class="fa fa-check" alt="Borrar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';

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
</script>

<script>
    function recovery(_codigo) {
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                ID_Transporte : _codigo,
                action : "recovery"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/transporte/update/index.php',
            success: function(data) {
                if (data.success == true) {
                    //$('#codigo').prop('disabled', true);
                    ReloadGrid();
					ReloadGrid1();
                }
            }
        });		
    }
	
	$('#tipo_transporte').change(function(e) {
		var clave_ttransporte= $(this).val();
			$.ajax({
			type: "POST",
			dataType: "json",
			data: {
                clave_ttransporte : clave_ttransporte,
				action : "load"
			},
			beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
			},
			url: '/api/tipotransporte/update/index.php',
			success: function(data) {
				$('#alto').val(data.alto);
				$('#ancho').val(data.ancho);
				$('#fondo').val(data.fondo);
				$('#capacidad_carga').val(data.capacidad_carga);
				$('#capacidad_volumetrica').val(data.capacidad_volumetrica);
				if (data.imagen!=null)
				$('#imagen').prop("src","../img/foto_tipo_transporte/"+data.imagen);
				else
				$('#imagen').prop("src","../img/foto_tipo_transporte/noimage.jpg");	
				}
			
		});
		
		
		
	});
	
	
	  $("#clave").keyup(function(e) {

        var claveCode = $(this).val();
        var claveCodeRegexp = new RegExp("^[a-zA-Z0-9\-]{1,20}$");

        if (claveCodeRegexp.test(claveCode)) {
            $("#CodeMessage").html("");
            $("#btnSave").prop('disabled', false);

			 var ID_Transporte = $(this).val();
        
		$.ajax({
			type: "POST",
			dataType: "json",
			data: {
                ID_Transporte : ID_Transporte,
                id_almacen: <?php echo $_SESSION['id_almacen']; ?>,
				action : "exists"
			},
		
			url: '/api/transporte/update/index.php',
			success: function(data) {
                console.log("Exist = ", data);
                    if (data.success == true) {
                        //$("#CodeMessage").html("Clave de Transporte ya existe");

                    console.log("success_otro_almacen = ",data.success_otro_almacen);
                    if(data.success_otro_almacen == false)
                        $("#CodeMessage").html("La clave del transporte ya existe en este almacén");
                    else
                    {
                        $("#CodeMessage").html("La clave del transporte ya existe en Otro almacén, ¿Desea Copiarlo a este almacén? <input type='button' id='si_copiar' class='btn btn-primary' value='Si' > <input type='button' id='no_copiar' class='btn btn-danger' value='No' >");

                            $("#si_copiar").click(function(){
                                console.log("copiar transporte");
                              $.ajax({
                                type: "POST",
                                dataType: "json",
                                url: '/api/transporte/update/index.php',
                                data: {
                                  action : "CopiarTransporteA_Almacen",
                                  ID_Transporte : ID_Transporte,
                                  id_almacen: <?php echo $_SESSION['id_almacen']; ?>
                                },
                                beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }},
                                success: function(data) {
                                  if (data.success == true) 
                                  {
                                    swal("Exito","Se ha copiado El Transporte "+ID_Transporte, "success");
                                    window.location.reload();
                                  }
                                }, error: function(data)
                                {
                                    console.log("ERROR Copiar = ", data);
                                }
                              });

                            });

                            $("#no_copiar").click(function(){
                                $("#clave").val("");
                                $("#CodeMessage").html("");
                            });
                    }

                        $("#btnSave").prop('disabled', true);
                     }else{
                           $("#CodeMessage").html("");
                           $("#btnSave").prop('disabled', false);
                    }
				}, error: function(data){
                    console.log("ERROR Exist = ", data);
                }
			
		});
			
        }else{
            $("#CodeMessage").html("Por favor, ingresar una Clave de Transporte válida");
            $("#btnSave").prop('disabled', true);
        }
    });
	
	$("#Nombre").keyup(function(e) {

        var claveCode = $(this).val();
        var claveCodeRegexp = new RegExp("^[a-zA-Z0-9]{1,20}$");


            $("#CodeMessage2").html("");
            $("#btnSave").prop('disabled', false);

			 var Nombre = $(this).val();
        
		$.ajax({
			type: "POST",
			dataType: "json",
			data: {
                Nombre : Nombre,
				action : "existsNombre"
			},
		
			url: '/api/transporte/update/index.php',
			success: function(data) {
                if (data.success == false) {
                   $("#CodeMessage").html("");
            $("#btnSave").prop('disabled', false);
             }else{
            $("#CodeMessage2").html("Número Económico ya existe");
            $("#btnSave").prop('disabled', true);
        }
				}
			
		});
			
       
    });
	
	$("#txtCriterio").keyup(function(event){
    if(event.keyCode == 13){
        $("#buscarA").click();
    }
});
</script>


                            <style>

<?php /* if($edit[0]['Activo']==0){?>

.fa-edit{

    display: none;

}

<?php }?>


<?php if($borrar[0]['Activo']==0){?>

.fa-eraser{

    display: none;

}

<?php } */ ?>

</style>