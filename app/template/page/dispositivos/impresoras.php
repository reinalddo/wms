<?php

$listaGrupoArt = new \GrupoArticulos\GrupoArticulos();

$vere = \db()->prepare("select * from t_profiles as a where id_menu=27 and id_submenu=65 and id_role='".$_SESSION["perfil_usuario"]."'");
$vere->execute();
$ver = $vere->fetchAll(PDO::FETCH_ASSOC);


$agre = \db()->prepare("select * from t_profiles as a where id_menu=27 and id_submenu=66 and id_role='".$_SESSION["perfil_usuario"]."'");
$agre->execute();
$ag = $agre->fetchAll(PDO::FETCH_ASSOC);


$edita = \db()->prepare("select * from t_profiles as a where id_menu=27 and id_submenu=67 and id_role='".$_SESSION["perfil_usuario"]."'");
$edita->execute();
$edit = $edita->fetchAll(PDO::FETCH_ASSOC);


$borra = \db()->prepare("select * from t_profiles as a where id_menu=27 and id_submenu=68 and id_role='".$_SESSION["perfil_usuario"]."'");
$borra->execute();
$borrar = $borra->fetchAll(PDO::FETCH_ASSOC);
/*
function getRealIP_IMP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP']))
        return $_SERVER['HTTP_CLIENT_IP'];
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    return $_SERVER['REMOTE_ADDR'];
}
*/
?>
<link href="/css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">
<link href="/css/plugins/chosen/chosen.css" rel="stylesheet">
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


<div class="wrapper wrapper-content  animated fadeInRight">

    <h3>Impresoras</h3>

    <div class="row">
        <div class="col-md-12">
            <div class="ibox ">
                <div class="ibox-title">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="input-group">
                                <input type="text" class="form-control" name="txtCriterio" id="txtCriterio" placeholder="Buscar...">
                                <div class="input-group-btn">
                                    
                                        <button  onclick="ReloadGrid()" type="submit" id="buscarA" class="btn btn-primary">
                                        <span class="fa fa-search"></span>  Buscar
                                        </button>
                                 
                                </div>
                            </div>

                        </div>
                        <div class="col-md-8">
  

                            <a href="#" class="permiso_registrar" onclick="agregar()"><button class="btn btn-primary" type="button"><i class="fa fa-plus"></i>&nbsp;&nbsp;Nuevo</button></a>


                            <button  class="btn btn-primary pull-right" type="button" id="inactivos"><i class="fa fa-search"></i>&nbsp;&nbsp;Impresoras inactivas</button>
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

<div class="modal inmodal" id="myModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content animated bounceInRight">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <i class="fa fa-print modal-icon"></i>
                <h4 class="modal-title" id="modaltitle">Agregar Impresora</h4>
            </div>
            <form id="myform">
                <div class="modal-body">
                    <label>Descripción *</label>
                    <input id="descripcion" type="text" placeholder="Descripción Impresora" class="form-control" required="true"><!--<label id="CodeMessage" style="color:red;"></label>--><br>

                    <div class="row">

                        <div class="col-md-4">
                        <label>Marca</label>
                        <input id="marca" type="text" placeholder="Marca" class="form-control">
                        </div>

                        <div class="col-md-4">
                        <label>Modelo</label>
                        <input id="modelo" type="text" placeholder="Modelo" class="form-control">
                        </div>

                        <div class="col-md-4">
                        <label>Tipo Impresora *</label>
                        <select class="form-control" id="tipo_impresora" name="tipo_impresora" required="true">
                            <option value="">Seleccione</option>
                            <?php 
                                $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

                                $sql = "SELECT * FROM s_tipoimpresoras WHERE Activo = 1";
                                if (!($res = mysqli_query($conn, $sql)))echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
                                while($row = mysqli_fetch_array($res))
                                {
                                    extract($row);
                                //id, Cve_TipoImp, Des_TipoImp, Activo
                            ?>
                            <option value="<?php echo $Cve_TipoImp; ?>"><?php echo "( ".$Cve_TipoImp." ) - ".$Des_TipoImp; ?></option>
                            <?php 
                                }
                            ?>
                        </select>
                        </div>

                    </div>
                    <br>
                    <label>Dirección (MAC / TCP - IP) *</label>
                    <input id="direccionip" type="text" placeholder="Dirección (MAC / TCP - IP)" class="form-control" required="true">
                    <br>
                    <div class="row">

                        <div class="col-md-4">
                        <label>Puerto</label>
                        <input name="puerto" id="puerto" type="text" placeholder="Marca" class="form-control">
                        </div>

                        <div class="col-md-4">
                        <label>Tiempo de espera (Seg)</label>
                        <input name="tiempo" id="tiempo" type="text" placeholder="Tiempo Espera" class="form-control">
                        </div>

                        <div class="col-md-4">
                        <label>Tipo Conexión *</label>
                        <select class="form-control" id="tipo_conexion" name="tipo_conexion" required="true">
                            <option value="">Seleccione</option>
                            ?>
                            <option value="TC">( TC ) - TCP-IP</option>
                            <option value="BT">( BT ) - Bluetooth</option>
                        </select>
                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-white" data-dismiss="modal" id="btnCancel">Cerrar</button>
                    <button type="submit" class="btn btn-primary ladda-button" id="btnSave">Guardar</button>
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
                    <h4 class="modal-title">Recuperar Clasificación</h4>
                </div>
                <div class="modal-body">
                    <div class="col-md-4">
                        <div class="input-group">
                            <input type="text" class="form-control input-sm" name="txtCriterio" id="txtCriterio1" placeholder="Buscar">
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
            url:'/api/dispositivos/lista/index.php',
            datatype: "json",
            shrinkToFit: false,
            height:'auto',
            postData: {
                almacen: <?php echo $_SESSION['id_almacen']; ?>,
                criterio: $("#txtCriterio").val()
            },
            mtype: 'POST',
            colNames:["Acciones", "Dirección (MAC/TCP-IP)","Descripción","Marca", "Modelo", "Tipo Impresora", "Tipo Conexión", "Puerto"],
            colModel:[
                //{name:'id',index:'id', width:60, sorttype:"int", editable: false},
                {name:'myac',index:'', width:100, sortable:false, resize:false, formatter:imageFormat},
                {name:'direccionip',index:'cve_sgpoart',width:180, editable:false, sortable:false},
                {name:'descripcion',index:'des_gpoart',width:220, editable:false, sortable:false},
                {name:'marca',index:'des_sgpoart',width:150, editable:false, sortable:false},
                {name:'modelo',index:'cve_sgpoart',width:150, editable:false, sortable:false},
                {name:'tipo_impresora',index:'cve_sgpoart',width:150, editable:false, sortable:false},
                {name:'tipo_conexion',index:'cve_sgpoart',width:150, editable:false, sortable:false},
                {name:'puerto',index:'cve_sgpoart',width:120, editable:false, sortable:false, align:'right'},

            ],
            rowNum:30,
            rowList:[30,40,50],
            pager: pager_selector,
            sortname: 'cve_sgpoart',
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
            var id = rowObject[4];
            var serie = rowObject[1];
            var correl = rowObject[4];
            var url = "x/?serie="+serie+"&correl="+correl;
            var url2 = "v/?serie="+serie+"&correl="+correl;

            // la funcion downloadxml ejecuta el index.php que esta en la carpeta x pasandole la serie y el correlativo via get
            // la funcion viewPdf ejecuta el index.php que esta en la carpeta v pasandole la serie y el correlativo via get

            $("#hiddenCodigo").val(serie);

            var html = '';
            //html += '<a href="#" onclick="ver(\''+serie+'\')"><i class="fa fa-eye" alt="Ver"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
/*
            if($("#permiso_editar").val() == 1)
            html += '<a href="#" onclick="editar(\''+id+'\')"><i class="fa fa-edit" alt="Editar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            if($("#permiso_eliminar").val() == 1)
            html += '<a href="#" onclick="borrar(\''+id+'\')"><i class="fa fa-eraser" alt="Borrar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
*/
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
                almacen: <?php echo $_SESSION['id_almacen']; ?>,
                criterio: $("#txtCriterio").val(),
            }, datatype: 'json', page : 1})
            .trigger('reloadGrid',[{current:true}]);
    }

    function ReloadGrid1() {
        $('#grid-table2').jqGrid('clearGridData')
            .jqGrid('setGridParam', {postData: {
                almacen: <?php echo $_SESSION['id_almacen']; ?>,
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

    $("#tipo_conexion").change(function(){

        if($(this).val() == 'TC') $("#puerto").val("9100");
        else if($(this).val() == 'BT') $("#puerto").val("0");
        else $("#puerto").val("");

    });

    $modal0 = null;

    function borrar(_codigo) {
        console.log("almacen: ", <?php echo $_SESSION['id_almacen']; ?>);
        console.log("cve_sgpoart :",  _codigo);

        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                almacen: <?php echo $_SESSION['id_almacen']; ?>,
                cve_sgpoart : _codigo,
                action : "inUse"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                                    },
            url: '/api/dispositivos/update/index.php',
            success: function(data) {
                if (data.success == true) {
                    swal(
                        '¡Alerta!',
                        'La Clasificación de articulos esta siendo usado en este momento',
                        'warning'
                    );
                    //$('#codigo').prop('disabled', true);
                    //ReloadGrid();
                }
                else {
                    swal({
                        title: "¿Está seguro que desea borrar la clasificación de articulos?",
                        text: "Está a punto de borrar una clasificación de articulos y esta acción no se puede deshacer",
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
                                almacen: <?php echo $_SESSION['id_almacen']; ?>,
                                cve_sgpoart : _codigo,
                                action : "delete"
                            },
                            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                                                    },
                            url: '/api/dispositivos/update/index.php',
                            success: function(data) {
                                if (data.success == true) {
                                    swal("Borrado", "La calificación del artículo ha sido borrada exitosamente", "success");
                                    ReloadGrid();
                                    ReloadGrid1();
                                }else{
                                    swal("Error", "Ocurrió un error al eliminar la calificación", "error");
                                }                

                            }
                        });

                    });
                }
            }
        });
    }

    function editar(_codigo) {
        $("#hiddenCodigo").val(_codigo);
        $("#codigo").prop('disabled',true);
        console.log("_codigo = ", _codigo);
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                almacen: <?php echo $_SESSION['id_almacen']; ?>,
                codigo : _codigo,
                action : "load"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                                    },
            url: '/api/dispositivos/update/index.php',
            success: function(data) {
                if (data.success == true) {
                    //$('#codigo').prop('disabled', true);
                    console.log("EDIT = ", data);
                    $("#codigo").val(data.cve_sgpoart);
                    $("#cve_gpoart").val(data.cve_gpoart);
                    $("#descripcion").val(data.descripcion);
                    l.ladda('stop');
                    $("#btnCancel").show();
                    $modal0 = $("#myModal");
                    $modal0.modal('show');
                    $("#hiddenAction").val("edit");
                }
            }
        });
    }


    function agregar() {
        $modal0 = $("#myModal");
        $modal0.modal('show');
        $("#codigo").prop('disabled',false);
        l.ladda('stop');
        //$('#codigo').prop('disabled', false);
        $("#hiddenAction").val("add");
        $("#btnCancel").show();
        //$("#grupoArt").val("");
        $("#descripcion").val("");
        $("#codigo").val("");
    }

    var l = $( '.ladda-button' ).ladda();
    l.click(function() {

        $("#btnCancel").hide();

        l.ladda( 'start' );

        console.log("almacen: ", <?php echo $_SESSION['id_almacen']; ?>);
        console.log("descripcion: ", $("#descripcion").val());
        console.log("marca: ", $("#marca").val());
        console.log("modelo: ", $("#modelo").val());
        console.log("tipo_impresora: ", $("#tipo_impresora").val());
        console.log("direccionip: ", $("#direccionip").val());
        console.log("puerto: ", $("#puerto").val());
        console.log("tiempo: ", $("#tiempo").val());
        console.log("tipo_conexion: ", $("#tipo_conexion").val());

/*
        console.log("cve_sgpoart :", $("#codigo").val());
        console.log("cve_gpoart :", $("#cve_gpoart").val());
        console.log("des_sgpoart :", $("#descripcion").val());
        console.log("action :", $("#hiddenAction").val());
*/

        $.post('/api/dispositivos/update/index.php',
               {
            almacen: <?php echo $_SESSION['id_almacen']; ?>,
            descripcion: $("#descripcion").val(),
            marca: $("#marca").val(),
            modelo: $("#modelo").val(),
            tipo_impresora: $("#tipo_impresora").val(),
            direccionip: $("#direccionip").val(),
            puerto: $("#puerto").val(),
            tiempo: $("#tiempo").val(),
            tipo_conexion: $("#tipo_conexion").val(),
            action : 'add'
        },
               function(response){
            console.log(response);
        }, "json")
            .always(function(data) {
                console.log("SUCCESS = ", data);
            if(data == 0)
                swal("Clave Ocupada", "Ya esta clave está en uso", "error");
            else
            {
            $("#grupoArt").val("");
            $("#descripcion").val("");
            l.ladda('stop');
            $("#btnCancel").show();
            $modal0.modal('hide');
            ReloadGrid();
            }
        });




    });

    $("#codigo").keyup(function(e) {

        var claveCode = $(this).val();
        var claveCodeRegexp = new RegExp("^[a-zA-Z0-9]{1,20}$");

        if (claveCodeRegexp.test(claveCode)) {
            $("#CodeMessage").html("");
            $("#btnSave").prop('disabled', false);

            var clave = $(this).val();

            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    clave : clave,
                    action : "exists"
                },
                beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                                        },
                url: '/api/dispositivos/update/index.php',
                success: function(data) {
                    if (data.success == false) {
                        $("#CodeMessage").html("");
                        $("#btnSave").prop('disabled', false);
                    }else{
                        $("#CodeMessage").html(" Clave de clasificacion de articulo ya existe");
                        $("#btnSave").prop('disabled', true);
                    }
                }

            });

        }else{
            $("#CodeMessage").html("Por favor, ingresar una Clave de clasificacion válida");
            $("#btnSave").prop('disabled', true);
        }
    });

    $("#txtCriterio").keyup(function(event){
        if(event.keyCode == 13){
            $("#buscarA").click();
        }
    });

    $("#inactivos").on("click", function(){
        $modal0 = $("#coModal");
        $modal0.modal('show');
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
            url:'/api/dispositivos/lista/index_i.php',
            datatype: "json",
            shrinkToFit: false,
            height:'auto',
            postData: {
                almacen: <?php echo $_SESSION['id_almacen']; ?>,
                criterio: $("#txtCriterio1").val()
            },
            mtype: 'POST',
            colNames:["id","Clave",'Grupo','Descripción',"Recuperar"],
            colModel:[
                {name:'id',index:'id', width:60, sorttype:"int", editable: false,hidden:true},
                {name:'cve_sgpoart',index:'cve_sgpoart',width:110, editable:false, sortable:false},
                {name:'des_gpoart',index:'des_gpoart',width:300, editable:false, sortable:false, hidden:true},
                {name:'des_sgpoart',index:'des_sgpoart',width:600, editable:false, sortable:false},
                {name:'myac',index:'', width:80, fixed:true, sortable:false, resize:false, formatter:imageFormat},
            ],
            rowNum:10,
            rowList:[10,20,30],
            pager: pager_selector,
            sortname: 'cve_sgpoart',
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
            var serie = rowObject[0];
            var correl = rowObject[4];
            var url = "x/?serie="+serie+"&correl="+correl;
            var url2 = "v/?serie="+serie+"&correl="+correl;

            var id = rowObject[0];

            // la funcion downloadxml ejecuta el index.php que esta en la carpeta x pasandole la serie y el correlativo via get
            // la funcion viewPdf ejecuta el index.php que esta en la carpeta v pasandole la serie y el correlativo via get

            var html = '';
            //html += '<a href="#" onclick="ver(\''+serie+'\')"><i class="fa fa-eye" alt="Ver"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            html += '<a href="#" onclick="recovery(\''+id+'\')"><i class="fa fa-check" alt="Borrar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';

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
                id : _codigo,
                action : "recovery"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                                    },
            url: '/api/dispositivos/update/index.php',
            success: function(data) {
                if (data.success == true) {
                    //$('#codigo').prop('disabled', true);
                    ReloadGrid();
                    ReloadGrid1();
                }
            }
        });
    }

    $(document).ready(function(){
        $("div.ui-jqgrid-bdiv").css("max-height",$("#list").height()-80);
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