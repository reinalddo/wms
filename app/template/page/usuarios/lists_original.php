<?php
$listaNCompa = new \Companias\Companias();
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
<link href="/css/plugins/ladda/select2.css" rel="stylesheet"/>
<link href="/css/plugins/sweetalert/bootstrap-imageupload.min.css" rel="stylesheet">
<!-- Mainly scripts -->

<div class="modal inmodal" id="myModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content animated bounceInRight">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <i class="fa fa-laptop modal-icon"></i>
                <h4 class="modal-title" id="modaltitle">Agregar Usuario</h4>
            </div>
            <div class="modal-body">
                <label>Clave</label>
                <input id="clave_user_edit" type="text" placeholder="Clave" class="form-control" value=""><label id="CodeMessage" style="color:red;"></label><br>
                <label>Nombre Completo</label>
                <input id="nombrec_user_edit" type="text" placeholder="Nombre Completo" class="form-control" value=""><br>
                <label>Email</label>
                <input id="email_user_edit" type="text" placeholder="Email" class="form-control" value=""><label id="emailMessage" style="color:red;"></label><br>
                <label>Nombre Usuario</label>
                <input id="nombre_user_edit" type="text" placeholder="Nombre Usuario" class="form-control" value=""><br>
                <label>Contraseña</label>
                <input id="pass_user_edit" type="password" placeholder="Contraseña" class="form-control" value=""><br>
                <label>Confirmar Contraseña</label>
                <input id="cpass_user_edit" type="password" placeholder="Confirmar Contraseña" class="form-control" value=""><label id="passMessage" style="color:red;"></label><br>
                <div class="form-group">
                    <label>Empresa</label>
                    <select name="country" id="compania_edit" style="width:100%;">
                        <option value="">Nombre de la Compañia</option>
                        <?php foreach( $listaNCompa->getComp() AS $p ): ?>
                            <option value="<?php echo $p->cve_cia; ?>"><?php echo $p->des_cia; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <label>Perfil</label>
                <input id="perfil_user_edit" type="text" placeholder="Perfil" class="form-control" value=""><br>
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
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-white" data-dismiss="modal" id="btnCancel">Cerrar</button>
                <button type="button" class="btn btn-primary ladda-button" data-style="contract" id="btnSave">Guardar</button>
            </div>
        </div>
    </div>
</div>

<div class="wrapper wrapper-content  animated fadeInRight">

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
                                        <button type="submit" class="btn btn-sm btn-primary">
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
                    <p>Verificar que no hayan campos vacÃ­os</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
</div>

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
<!-- Select -->
<script src="/js/select2.js"></script>
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
            height: 250,
            postData: {
                criterio: $("#txtCriterio").val()
            },
            mtype: 'POST',
            colNames:['ID','Perfil','Nombre','Clave de Empresa',"Acciones"],
            colModel:[
                //{name:'id',index:'id', width:60, sorttype:"int", editable: false},
                {name:'id_user',index:'id_user',width:10, editable:false, sortable:false},
                {name:'cve_usuario',index:'cve_usuario',width:100, editable:false, sortable:false},
                {name:'des_usuario',index:'des_usuario',width:150, editable:false, sortable:false},
                {name:'cve_cia',index:'cve_cia',width:45, editable:false, sortable:false},
                {name:'myac',index:'', width:80, fixed:true, sortable:false, resize:false, formatter:imageFormat},
            ],
            rowNum:30,
            rowList:[10,20,30],
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
            //html += '<a href="#" onclick="ver(\''+serie+'\')"><i class="fa fa-eye" alt="Ver"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            html += '<a href="#" onclick="editar(\''+id_usuario+'\')"><i class="fa fa-edit" alt="Editar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            html += '<a href="#" onclick="borrar(\''+id_usuario+'\')"><i class="fa fa-eraser" alt="Borrar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';

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
                action : "delete"
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

    function editar(id_usuario) {
        $('#hiddenIDUsuario').val(id_usuario);
        $("#modaltitle").html('<h4 class="modal-title">Editar Usuario '+id_usuario+'</h4>');
        $("#upload").show();
        $("#emailMessage").html("");
        $("#CodeMessage").html("");
        $("#passMessage").html("");
        $modal0 = $("#myModal");
        $modal0.modal('show');
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                id_user : id_usuario,
                action : "load"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/usuarios/update/index.php',
            success: function(data) {
                if (data.success == true) {
                    //$('#codigo').prop('disabled', true);
                    $('#clave_user_edit').val(data.cve_usuario);
                    $('#nombre_user_edit').val(data.des_usuario);
                    $('#perfil_user_edit').val(data.perfil);
                    $('#nombrec_user_edit').val(data.nombrec);
                    $('#email_user_edit').val(data.email);
                    $("#image").attr("src","/img/imageperfil/"+data.image_url);
                    $("#image").attr("ima",data.image_url);
                    $("#compania_edit").select2("val", data.cve_cia);
                    $('#pass_user_edit').val(data.pwd_usuario);
                    $modal0 = $("#myModal");
                    $modal0.modal('show');
                    $("#hiddenAction").val("edit");
                }
            }
        });
    }

    function agregar() {
        $("#modaltitle").html('<h4 class="modal-title">Agregar Usuario</h4>');
        $modal0 = $("#myModal");
        $modal0.modal('show');
        $("#upload").hide();
        $("#emailMessage").html("");
        $("#CodeMessage").html("");
        $("#passMessage").html("");

        l.ladda('stop');
        //$('#codigo').prop('disabled', false);
        $("#hiddenAction").val("add");
        $("#btnCancel").show();
        $("#Nestado").select2("val", "");
        $("#NombrePoblacion").val("");
    }

    var l = $( '.ladda-button' ).ladda();
    l.click(function() {

        $("#btnCancel").hide();

        l.ladda( 'start' );

        if ($('#image_user_edit').val()) {
            var path = $('#image_user_edit').val();
            var filename = path.replace(/^.*\\/, "");
            uploadFile();
        } else if ($('#image').attr('ima') != "" && !$('#image_user_edit').val()) {
            filename = $("#image").attr("ima");
        } else {
            filename = "noimage.jpg"
        }
        $.post('/api/usuarios/update/index.php',
            {
                cve_usuario: $("#clave_user_edit").val(),
                des_usuario: $("#nombre_user_edit").val(),
                cve_cia: $("#compania_edit").val(),
                pwd_usuario: $("#pass_user_edit").val(),
                Clave: $('#hiddenIDUsuario').val(),
                nombrec: $("#nombrec_user_edit").val(),
                email: $("#email_user_edit").val(),
                perfil: $('#perfil_user_edit').val(),
                action: $("#hiddenAction").val(),
                imagen: filename
            },
            function (response) {
                console.log(response);
            }, "json")
            .always(function () {
                $("#compania_edit").select2("val", "");
                $("#clave_user_edit").val("");
                $("#pass_user_edit").val("");
                $("#nombre_user_edit").val("");
                $('#perfil_user_edit').val("");
                $('#nombrec_user_edit').val("");
                $('#email_user_edit').val("");
                var $imageupload = $('.imageupload');
                $imageupload.imageupload({
                    maxFileSizeKb: 512,
                    maxWidth: 150,
                    maxHeight: 150,
                });
                l.ladda('stop');
                $("#btnCancel").show();
                $modal0.modal('hide');
                ReloadGrid();
            });


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
        $("#compania_edit").select2({

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
</script>


<!-- Segundas Grid --->
<script type="text/javascript">

    //////////////////////////////////////////////////////////Aqui se contruye el Grid//////////////////////////////////////////////////////////
    $(function($) {
        var grid_selector = "#grid-table2";
        var pager_selector = "#grid-pager2";

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
                {name:'id_user',index:'id_user',width:10, editable:false, sortable:false},
                {name:'cve_usuario',index:'cve_usuario',width:40, editable:false, sortable:false},
                {name:'des_usuario',index:'des_usuario',width:40, editable:false, sortable:false},
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


    $("#cpass_user_edit").keyup(function(e) {

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
</script>