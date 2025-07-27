<?php
$listaNCompa = new \Companias\Companias();
$listaRoles = new \Roles\Roles();

$vere = \db()->prepare("select * from t_profiles as a where id_menu=14 and id_submenu=25 and id_role='".$_SESSION["perfil_usuario"]."'");
$vere->execute();
$ver = $vere->fetchAll(PDO::FETCH_ASSOC);


$agre = \db()->prepare("select * from t_profiles as a where id_menu=14 and id_submenu=26 and id_role='".$_SESSION["perfil_usuario"]."'");
$agre->execute();
$ag = $agre->fetchAll(PDO::FETCH_ASSOC);


$edita = \db()->prepare("select * from t_profiles as a where id_menu=14 and id_submenu=27 and id_role='".$_SESSION["perfil_usuario"]."'");
$edita->execute();
$edit = $edita->fetchAll(PDO::FETCH_ASSOC);


$borra = \db()->prepare("select * from t_profiles as a where id_menu=14 and id_submenu=28 and id_role='".$_SESSION["perfil_usuario"]."'");
$borra->execute();
$borrar = $borra->fetchAll(PDO::FETCH_ASSOC);

$usuario_sesion = $_SESSION['cve_usuario'];
$almacenes_usuario = \db()->prepare("SELECT c.clave, c.nombre
FROM c_almacenp c 
LEFT JOIN trel_us_alm t ON t.cve_almac = c.clave 
LEFT JOIN c_usuario u ON u.cve_usuario = t.cve_usuario
WHERE u.cve_usuario = '{$usuario_sesion}'");
$almacenes_usuario->execute();
$almacenes_usuario = $almacenes_usuario->fetchAll(PDO::FETCH_ASSOC);


// MOD 14

// VER 25
// AGREGAR 26
// EDITAR 27
// BORRAR 28

?>
<style>
    select{width:290px;margin:0 0 50px 0;font-size: 11px;border:1px solid #ccc;padding:10px;border-radius:10px 0 0 10px;}
    .list{float:left;width:274px;text-align:center}
    /*input {margin:0px 1px 0 1px;border:1px solid #ccc;padding:10px;}*/
    .izq{border-radius:10px 0 0 10px;}
    .der{border-radius:0 10px 10px 0;}

    .fileContainer {
        overflow: hidden;
        position: relative;
    }

    .fileContainer [type=file] {
        cursor: inherit;
        display: block;
        font-size: 999px;
        filter: alpha(opacity=0);
        min-height: 100%;
        min-width: 100%;
        opacity: 0;
        position: absolute;
        right: 0;
        text-align: right;
        top: 0;
    }


</style>
<link href="/css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">
<link href="/css/plugins/chosen/chosen.css" rel="stylesheet">
<link href="/css/bootstrap-imageupload.min.css" rel="stylesheet">
<link href="/css/plugins/sweetalert/sweetalert.css" rel="stylesheet">
<!-- Mainly scripts -->


<div class="wrapper wrapper-content  animated fadeInRight" id="FORM" style="display: none">

    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-title">
                    <div class="row">
                        <div class="col-lg-4" id="_title">
                            <h3>Agregar Usuario</h3>
                        </div>
                    </div>
                </div>
                <form id="myform" method="post" name="myform">
                    <input type="hidden" id="hiddenAction" name="hiddenAction">
                    <div class="ibox-content">
                        <div class="row">
                            <div class="col-lg-6 b-r">

                                <input id="clave_user_edit" type="hidden">
                                <label>Nombre Usuario</label>
                                <input id="nombrec_user_edit" type="text" placeholder="Nombre de Usuario" class="form-control" value="" maxlength="20" required="true"><br>
                                <label>Correo</label>
                                <input id="email_user_edit" type="email" placeholder="Email" class="form-control" value="" required="true"><!--<label id="emailMessage" style="color:red;"></label>--><br>
                                <label>Nombre Completo</label>
                                <input id="nombre_user_edit" type="text" placeholder="Nombre Completo" class="form-control" value="" required="true"><br>
                                <label>Contraseña</label>
                                <input id="pass_user_edit" type="password" placeholder="Contraseña" class="form-control" value="" required="true"><br>
                                <label>Confirmar Contrraseña</label>
                                <input id="cpass_user_edit_1" type="password" placeholder="Confirmar Contraseña" class="form-control" value="" required="true">
                                <label id="passMessage" style="color:red;"></label><br>
                                <label>Descripción</label>
                                <input id="desc_usuario" type="text" placeholder="Descripción de Usuario" class="form-control" value="">
                                <div class="form-group">
                                    <label>Empresa</label>
                                    <select  id="compania_edit" class="chosen-select form-control" required="true">
                                        <option value="">Nombre de la Compañia</option>
                                        <?php foreach( $listaNCompa->getComp() AS $p ): ?>
                                        <option value="<?php echo $p->cve_cia; ?>"><?php echo $p->des_cia; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>


                            <div class="col-lg-6">

                                <label>Rol</label>
                                <select id="rol_usuario" class="chosen-select form-control" required="true">
                                    <option value="">Rol</option>
                                    <?php foreach( $listaRoles->getAll() AS $r ): ?>
                                    <option value="<?php echo $r->id_role; ?>"><?php echo $r->rol; ?></option>
                                    <?php endforeach; ?>
                                </select>

                                <br>
                                <br>
                                <div id="upload">
                                    <label>Imagen Actual</label>
                                    <img src=""  alt="Image preview" ima="" class="thumbnail" id="image" style="height: auto;
                                                                                                                width: auto;
                                                                                                                max-width: 170px;
                                                                                                                max-height: 170px;">
                                </div>

                                <div class="imageupload panel panel-default" id="upload">
                                    <div class="panel-heading clearfix">
                                        <h3 class="panel-title pull-left">Subir Imagen</h3>
                                    </div>
                                    <div class="file-tab panel-body">
                                        <label class="btn btn-primary btn-file fileContainer ">        <!-- The file is stored here. -->
                                            <b>Examinar</b>
                                            <input id="image_user_edit" type="file" name="image-file">

                                        </label>

                                        <button type="button" class="btn btn-default">Remover</button>
                                    </div>
                                </div>

                                <input type="hidden" id="hiddenAction">
                                <input type="hidden" id="hiddenIDUsuario">

                                <div class="pull-right">
                                    <style>

                                        <?php if($edit[0]['Activo']==0){?>

                                        .fa-edit{

                                            display: none;

                                        }

                                        <?php }?>


                                        <?php if($borrar[0]['Activo']==0){?>

                                        .fa-eraser{

                                            display: none;

                                        }

                                        <?php }?>

                                    </style>


                                    <?php if($ag[0]['Activo']==1){?>
                                    <a href="#" onclick="agregar()"><button class="btn btn-primary" type="button"><i class="fa fa-plus"></i>&nbsp;&nbsp;Nuevo</button></a>
                                    <?php }?>
                                    <button type="submit" class="btn btn-primary"  id="btnSave">Guardar</button>
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

    <h3>Usuarios</h3>

    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-title">
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="input-group">
                                <input type="text" class="form-control input-sm" name="txtCriterio" id="txtCriterio" placeholder="Buscar por Usuario...">
                                <div class="input-group-btn">
                                    <a href="#" onclick="ReloadGrid()">
                                        <button type="submit" class="btn btn-sm btn-primary" id="buscarU">
                                            Buscar
                                        </button>
                                    </a>
                                </div>
                            </div>

                        </div>
                        <div class="col-lg-8">
                            <a href="#" onclick="agregar()"><button class="btn btn-primary" type="button"><i class="fa fa-plus"></i>&nbsp;&nbsp;Nuevo</button></a>
                            <button class="btn btn-primary pull-right" type="button" id="inactivos"><i class="fa fa-search"></i>&nbsp;&nbsp;Usuarios inactivos</button>
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
                    <h4 class="modal-title">Recuperar Usuarios</h4>
                </div>
                <div class="modal-body">
                    <div class="col-lg-4">
                        <div class="input-group">
                            <input type="text" class="form-control input-sm" name="txtCriterio" id="txtCriterio1" placeholder="Buscar por Usuario...">
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

<!--<div class="modal fade" id="waModal" role="dialog">
<div class="vertical-alignment-helper">
<div class="modal-dialog vertical-align-center">
Modal content
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
</div>-->



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
<!-- Select -->
<script src="/js/plugins/chosen/chosen.jquery.js"></script>
<script src="/js/plugins/ladda/spin.min.js"></script>
<script src="/js/plugins/ladda/ladda.min.js"></script>
<script src="/js/plugins/ladda/ladda.jquery.min.js"></script>

<!-- File Upload -->
<script src="/js/bootstrap-imageupload.js"></script>

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
            url:'/api/usuarios/lista/index.php',
            datatype: "json",
            shrinkToFit: false,
            height:'auto',
            postData: {
                criterio: $("#txtCriterio").val()
            },
            mtype: 'POST',
            colNames:['ID','Nombre Usuario','Nombre Completo','Email', 'Empresa','Rol','Almacenes',"Acciones"],
            colModel:[
                //{name:'id',index:'id', width:60, sorttype:"int", editable: false},
                {name:'id_user',index:'id_user',width:20, editable:false, hidden:true,sortable:false},
                {name:'cve_usuario',index:'cve_usuario',width:140, editable:false, sortable:false},
                // {name:'des_usuario',index:'des_usuario',width:150, editable:false, sortable:false},
                {name:'nombre_completo',index:'nombre_completo',width:260, editable:false, sortable:false},
                {name:'email',index:'email',width:255, editable:false, sortable:false},
                {name:'cve_cia',index:'cve_cia',width:275, editable:false, sortable:false},
                {name:'perfil',index:'perfil',width:215, editable:false, sortable:false},
                {name:'almacenes',index:'almacenes',width:215, editable:false, sortable:false},
                {name:'myac',index:'', width:80, fixed:true, sortable:false, resize:false, formatter:imageFormat},
            ],
            rowNum:30,
            rowList:[30,40,50],
            pager: pager_selector,
            sortname: 'cve_usuario',
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
            var id_usuario = rowObject[0];
            // var estado = rowObject[1];
            //var correl = rowObject[4];
            //var url = "x/?serie="+poblacion+"&correl="+correl;
            //var url2 = "v/?serie="+poblacion+"&correl="+correl;
            $("#hiddenIDUsuario").val(id_usuario);
            //$("#hiddenIDEstado").val(estado);
            // la funcion downloadxml ejecuta el index.php que esta en la carpeta x pasandole la serie y el correlativo via get
            // la funcion viewPdf ejecuta el index.php que esta en la carpeta v pasandole la serie y el correlativo via get

            var html = '';

            if (id_usuario!="1") {
                //html += '<a href="#" onclick="ver(\''+serie+'\')"><i class="fa fa-eye" alt="Ver"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
                html += '<a href="#" onclick="editar(\'' + id_usuario + '\')"><i class="fa fa-edit" alt="Editar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
                html += '<a href="#" onclick="borrar(\'' + id_usuario + '\')"><i class="fa fa-eraser" alt="Borrar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
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
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                id_user : _codigo,
                action : "tieneAlmacen"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                                    },
            url: '/api/usuarios/update/index.php',
            success: function(data) {
                console.log(data);
                if (data.success == true) {
                    swal({
                        title: "¡Alerta!",
                        text: "El usuario esta siendo usado en este momento",
                        type: "warning",
                        showCancelButton: false,
                    });
                }
                else{
                    swal({
                        title: "¿Está seguro que desea borrar el usuario?",
                        text: "Está a punto de borrar un usuario y esta acción no se puede deshacer",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#DD6B55",
                        confirmButtonText: "Borrar",
                        cancelButtonText: "Cancelar",
                        closeOnConfirm: false
                    },
                         function(){
                        var borro = localStorage.getItem("borro") | 0;
                        localStorage.setItem('borro', borro + 1);
                        $.ajax({
                            url: '/api/usuarios/update/index.php',
                            type: "POST",
                            dataType: "json",
                            data: {
                                id_user : _codigo,
                                action : "delete"
                            },
                            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                                                    },
                            dataType: 'json'
                        }).done(function(data){
                            if(data.success){
                                ReloadGrid();
                                ReloadGrid1();
                                swal("Borrado", "El usuario ha sido borrado exitosamente", "success");
                            }else{
                                swal("Error", "Ocurrió un error al eliminar el usuario", "error");
                            }
                        });
                    });
                }
            }
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
        Control.val=Solo_Numerico(Control.val);
    }

    function editar(id_usuario) {
        $('#hiddenIDUsuario').val(id_usuario);

        $("#upload").show();
        //$("#clave_user_edit").prop('disabled', true);
        $("#emailMessage").html("");
        $("#CodeMessage").html("");
        $("#passMessage").html("");

        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
            url: '/api/usuarios/update/index.php',
                action : "load"
                id_user : id_usuario,
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                                    },
            success: function(data) {
                if (data.success == true) {
                    //$('#codigo').prop('disabled', true);
                    $('#clave_user_edit').val(data.id_user);
                    $('#nombre_user_edit').val(data.nombre_completo); //des_usuario
                    $('#rol_usuario').val(data.perfil);
                    $('#rol_usuario').trigger("chosen:updated");
                    $('#nombrec_user_edit').val(data.cve_usuario);
                    $('#nombrec_user_edit').prop('disabled', true);
                    $('#email_user_edit').val(data.email);
                    $('#desc_usuario').val(data.des_usuario);
                    $("#image").attr("src","/img/imageperfil/"+data.image_url);
                    $("#image").attr("ima",data.image_url);
                    $("#compania_edit").val(data.cve_cia);
                    $('#compania_edit').trigger("chosen:updated");
                    $('#pass_user_edit').val(data.pwd_usuario);
                    $('#hiddenIDUsuario').val(data.id_user);

                    $('#list').removeAttr('class').attr('class', '');
                    $('#list').addClass('animated');
                    $('#list').addClass("fadeOutRight");
                    $('#list').hide();

                    $('#FORM').show();
                    $('#FORM').removeAttr('class').attr('class', '');
                    $('#FORM').addClass('animated');
                    $('#FORM').addClass("fadeInRight");
                    $("#hiddenAction").val("edit");
                    $("#_title").html("<h3>Editar Usuario</h3>");
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
        $('#nombrec_user_edit').prop('disabled', false);
    }



    function agregar() {
        $('#list').removeAttr('class').attr('class', '');
        $('#list').addClass('animated');
        $('#list').addClass("fadeOutRight");
        $('#list').hide();

        $('#FORM').show();
        $('#FORM').removeAttr('class').attr('class', '');
        $('#FORM').addClass('animated');
        $('#FORM').addClass("fadeInRight");
        $("#hiddenAction").val("add");
        $("#upload").hide();
        $("#clave_user_edit").prop('disabled', false);
        $("#emailMessage").html("");
        $("#CodeMessage").html("");
        $("#passMessage").html("");
        $("#clave_user_edit").val("");
        $("#nombre_user_edit").val("");
        $("#nombrec_user_edit").val("");
        $("#pass_user_edit").val("");
        $("#desc_usuario").val("");
        $('#nombrec_user_edit').prop('disabled', false);

        //l.ladda('stop');
        //$('#codigo').prop('disabled', false);
        $("#hiddenAction").val("add");
        $("#btnCancel").show();
        $("#Nestado").val("");
        $("#NombrePoblacion").val("");
        $("#_title").html("<h3>Agregar Usuario</h3>");
    }

    var l = $( '#myform' );
    l.submit(function(e) {
        e.preventDefault();

        $("#btnCancel").hide();


        //l.ladda( 'start' );
        if($("#nombre_user_edit").val() && $("#compania_edit").val()
           && $("#pass_user_edit").val() && $("#nombrec_user_edit").val() && $("#email_user_edit").val()
           && $("#rol_usuario").val()) {
            if ($('#image_user_edit').val()) {
                var path = $('#image_user_edit').val();
                var filename = path.replace(/^.*\\/, "");
                uploadFile();
            } else if ($('#image').attr('ima') != "" && !$('#image_user_edit').val()) {
                filename = $("#image").attr("ima");
            } else {
                filename = "noimage.jpg"
            }

                console.log("cve_usuario = ", $("#nombrec_user_edit").val());
                console.log("des_usuario = ", $("#desc_usuario").val());
                console.log("nombre_completo = ", $("#nombre_user_edit").val());
                console.log("cve_cia = ", $("#compania_edit").val());
                console.log("pwd_usuario = ", $("#pass_user_edit").val());
                console.log("Clave = ", $('#hiddenIDUsuario').val());
                console.log("email = ", $("#email_user_edit").val());
                console.log("perfil = ", $('#rol_usuario').val());
                console.log("action = ", $("#hiddenAction").val());
            
            $.post('/api/usuarios/update/index.php',
                   {
                cve_usuario: $("#nombrec_user_edit").val(),//clave_user_edit
                des_usuario: $("#desc_usuario").val(), 
                nombre_completo: $("#nombre_user_edit").val(),
                cve_cia: $("#compania_edit").val(),
                pwd_usuario: $("#pass_user_edit").val(),
                Clave: $('#hiddenIDUsuario').val(),
                email: $("#email_user_edit").val(),
                perfil: $('#rol_usuario').val(),
                action: $("#hiddenAction").val(),
                imagen: filename
            },
                   function (response) {
                console.log(response);
            }, "json")
                .always(function () {
                $("#compania_edit").val("");
                $("#clave_user_edit").val("");
                $("#pass_user_edit").val("");
                $("#nombre_user_edit").val("");
                $('#rol_usuario').val("");
                $('#nombrec_user_edit').val("");
                $('#email_user_edit').val("");
                var $imageupload = $('.imageupload');
                $imageupload.imageupload({
                    maxFileSizeKb: 512,
                    maxWidth: 150,
                    maxHeight: 150,
                });
                //l.ladda('stop');
                $("#btnCancel").show();
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
                ReloadGrid();
            });
                
        }/*else{
            $("#waModal").modal();
            setTimeout(function () {
                $("#waModal").modal("hide");
                l.ladda('stop');
            }, 3000);
        }*/

        /*$.post( "/api/usuarios/update/index.php",
         {
         cve_usuario: $("#clave_user_edit").val(),
         des_usuario: $("#nombre_user_edit").val(),
         cve_cia: $("#compania_edit").val(),
         pwd_usuario: $("#pass_user_edit").val(),
         Clave: $('#hiddenIDUsuario').val(),
         action: $("#hiddenAction").val(),
         imagen: filename

         } ,function( data ) {
         alert(data);
         });*/
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
        });
    });
</script>

<script>
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
</script>


<script>
    function uploadFile(){
        var input = document.getElementById("image_user_edit");
        file = input.files[0];

        if(file != undefined){
            formData= new FormData();
            if(!!file.type.match(/image.*/)){
                formData.append("image", file);
                $.ajax({
                    url: "/app/template/page/perfil/upload.php",
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
                        //alert(thrownError);
                    }
                });
            }else{
                alert('Not a valid image!');
            }
        }else{
            alert('Input something!');
        }
    }
</script>


<!-- Segundas Grid -->
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
            url:'/api/usuarios/lista/index_i.php',
            datatype: "json",
            height: 250,
            postData: {
                criterio: $("#txtCriterio1").val()
            },
            mtype: 'POST',
            colNames:['ID','Perfil','Nombre',"Recuperar"],
            colModel:[
                //{name:'id',index:'id', width:60, sorttype:"int", editable: false},
                {name:'id_user',index:'id_user',width:200, editable:false, sortable:false},
                {name:'cve_usuario',index:'cve_usuario',width:200, editable:false, sortable:false},
                {name:'des_usuario',index:'des_usuario',width:600, editable:false, sortable:false},
                {name:'myac',index:'', width:80, fixed:true, sortable:false, resize:false, formatter:imageFormat},
            ],
            rowNum:10,
            rowList:[10,20,30],
            pager: pager_selector,
            sortname: 'cve_usuario',
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
            var id_usuario = rowObject[0];
            // var estado = rowObject[1];
            //var correl = rowObject[4];
            //var url = "x/?serie="+poblacion+"&correl="+correl;
            //var url2 = "v/?serie="+poblacion+"&correl="+correl;
            $("#hiddenIDUsuario").val(id_usuario);
            //$("#hiddenIDEstado").val(estado);
            // la funcion downloadxml ejecuta el index.php que esta en la carpeta x pasandole la serie y el correlativo via get
            // la funcion viewPdf ejecuta el index.php que esta en la carpeta v pasandole la serie y el correlativo via get

            var html = '';
            //html += '<a href="#" onclick="ver(\''+serie+'\')"><i class="fa fa-eye" alt="Ver"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            /* html += '<a href="#" onclick="editar(\''+id_usuario+'\')"><i class="fa fa-edit" alt="Editar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';*/
            html += '<a href="#" onclick="recovery(\''+id_usuario+'\')"><i class="fa fa-check" alt="Borrar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';

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
                id_user : _codigo,
                action : "recovery"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                                    },
            url: '/api/usuarios/update/index.php',
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

<script>
    $("#clave_user_edit").keyup(function(e) {

        var claveCode = $(this).val();
        var claveCodeRegexp = new RegExp("^[a-zA-Z0-9]{1,20}$");

        if (claveCodeRegexp.test(claveCode)) {
            $("#CodeMessage").html("");
            $("#btnSave").prop('disabled', false);

            var cve_usuario = $(this).val();

            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    cve_usuario : cve_usuario,
                    action : "exists"
                },
                beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                                        },
                url: '/api/usuarios/update/index.php',
                success: function(data) {
                    if (data.success == false) {
                        $("#CodeMessage").html("");
                        $("#btnSave").prop('disabled', false);
                    }else{
                        $("#CodeMessage").html(" Clave de usuario ya existe");
                        $("#btnSave").prop('disabled', true);
                    }
                }

            });

        }else{
            $("#CodeMessage").html("Por favor, ingresar una Clave de Usuario válida");
            $("#btnSave").prop('disabled', true);
        }

    });

    $("#email_user_edit").keyup(function(e) {

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


    $("#cpass_user_edit").keyup(function(e) { //lilo

        var contrasena = $("#pass_user_edit").val();
        var ccontrasena = $("#cpass_user_edit").val();

        if (contrasena == ccontrasena ) {
            $("#passMessage").html("");
            $("#btnSave").prop('disabled', false);

        }else{
            $("#passMessage").html("Las Contraseñan deben coincidir");
            $("#btnSave").prop('disabled', true);
        }
    });

    $("#txtCriterio").keyup(function(event){
        if(event.keyCode == 13){
            $("#buscarU").click();
        }
    });


</script>