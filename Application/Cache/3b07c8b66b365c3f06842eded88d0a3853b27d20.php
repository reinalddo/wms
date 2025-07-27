
    <link rel="stylesheet" href="/css/modulos/usuarios.css">
    <link rel="stylesheet" href="/css/plugins/chosen/chosen.css">
    <link rel="stylesheet" href="/css/bootstrap-imageupload.min.css">
    <link rel="stylesheet" href="/css/plugins/sweetalert/sweetalert.css">
    <!-- Mainly scripts -->

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


    <div class="wrapper wrapper-content animated fadeInRight" id="list">
        <h3>Usuarios</h3>
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-title">
                        <div class="row">

                            <div class="col-md-12 col-lg-12">   
                                <div class="input-group">
                                    <div class="input-group-btn"> 
                                        <button onclick="agregar()" class="btn btn-primary" type="button"><i class="fa fa-plus"></i> Nuevo</button>
                                    </div>
                                    <input type="text" class="form-control" name="txtCriterio" id="txtCriterio" placeholder="Buscar por Usuario...">
                                    <div class="input-group-btn">                                  
                                        <button onclick="ReloadGrid()" type="submit" class="btn btn-primary" id="buscarU">
                                            <span class="fa fa-search"></span> Buscar
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12 col-lg-12" style="margin-top:5px">
                                <button class="btn btn-primary pull-center" type="button" id="inactivos"><i class="fa fa-search"></i> Usuarios inactivos</button>
                            </div>

                        </div>
                    </div>
                    
                    <div class="ibox-content">
                        <div class="jqGrid_wrapper">
                            <table id="gridtable-usuarios"></table>
                            <div id="grid-pager"></div>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>

    
    <div class="wrapper wrapper-content animated fadeInRight" id="FORM" style="display: none">
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
                    <form id="myform">
                        <input type="hidden" id="hiddenAction" name="hiddenAction">
                        <div class="ibox-content">
                            
                            <div class="row" style="margin-bottom: 15px;">                                
                                <div class="col-md-12">
                                    <div class="pull-right">                                            
                                        <button onclick="cancelar()" class="btn btn-white" type="button"><i class="fa fa-ban"></i> Cancelar</button>
                                     <?php if($allowAdd->Activo==1): ?>
                                        <button type="submit" class="btn btn-primary" id="btnSave"><i class="fa fa-save"></i> Guardar</button>
                                    <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="row">

                                <div class="col-lg-6 b-r">
                                    <div class="row">

                                        <input id="clave_user_edit" type="hidden">

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Nombre Usuario</label>
                                                <input id="nombrec_user_edit" type="text" placeholder="Nombre de Usuario" class="form-control" value="" maxlength="20" required="true">
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Nombre Completo</label>
                                                <input id="nombre_user_edit" type="text" placeholder="Nombre Completo" class="form-control" value="" required="true">
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Correo</label>
                                                <input id="email_user_edit" type="email" placeholder="Email" class="form-control" value="" required="true">
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Descripción</label>
                                                <input id="desc_usuario" type="text" placeholder="Descripción de Usuario" class="form-control" value "">
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Contraseña</label>
                                                <input id="pass_user_edit" type="password" placeholder="Contraseña" class="form-control" value="" required="true">
                                            </div> 
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Confirmar Contraseña</label>
                                                <input id="cpass_user_edit" type="password" placeholder="Confirmar Contraseña" class="form-control" value="" required="true">
                                                <label id="passMessage" style="color:red;"></label>
                                            </div>
                                        </div>                                        

                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>Empresa</label>
                                                <select id="compania_edit" class="chosen-select form-control" required="true">
                                                    <option value="">Nombre de la Compañia</option>
                                                    <?php $__currentLoopData = $empresas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <option value="<?php echo e($value->cve_cia); ?>"><?php echo e($value->des_cia); ?></option>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>;
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="row">

                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>Perfil de usuario</label>
                                                <select id="rol_usuario" class="chosen-select form-control" required="true">
                                                    <option value="">Perfil</option>
                                                    <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <option value="<?php echo e($value->id_role); ?>"><?php echo e($value->rol); ?></option>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div id="upload">
                                                <label>Imagen Actual</label>
                                                <img src="" alt="Image preview" ima="" class="thumbnail" id="image" style="height: auto;  width: auto;  max-width: 170px;  max-height: 170px;">
                                            </div>
                                        </div>

                                        <div class="col-md-12">
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
                                        </div>

                                        <input type="hidden" id="hiddenAction">
                                        <input type="hidden" id="hiddenIDUsuario">
                         

                                    </div>
                                </div>

                            </div>
                            <div class="row">                                
                                <div class="col-lg-12">
                                    <div class="pull-right">                                            
                                        <button onclick="cancelar()" class="btn btn-white" type="button"><i class="fa fa-ban"></i> Cancelar</button>
                                        <?php if($allowAdd->Activo==1): ?>
                                        <button type="submit" class="btn btn-primary" id="btnSave"><i class="fa fa-save"></i> Guardar</button>
                                        <?php endif; ?>
                                    </div>
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
            <div class="modal-dialog vertical-align-center modal-lg">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Recuperar Usuarios</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">                            
                            <div class="input-group">
                                <input type="text" class="form-control" name="txtCriterio" id="txtCriterio1" placeholder="Buscar por Usuario...">
                                <div class="input-group-btn">                                   
                                    <button onclick="reloadGridUsuariosInactivos()" type="submit" class="btn btn-primary">
                                        <span class="fa fa-search"></span> Buscar
                                    </button>                                    
                                </div>
                            </div>
                        </div>

                        <div class="row" style="margin-top:15px">
                            <div class="jqGrid_wrapper">
                                <table id="gridtable-usuarios-inactivos"></table>
                                <div id="grid-pager2"></div>
                            </div>
                        </div>
                        
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal"><span class="fa fa-ban"></span> Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>


 

    <script type="text/javascript">
        //////////////////////////////////////////////////////////Aqui se contruye el Grid//////////////////////////////////////////////////////////
        $(function($) {
            var grid_selector = "#gridtable-usuarios";
            var pager_selector = "#grid-pager";

            //resize to fit page size
            $(window).on('resize.jqGrid', function() {
                $(grid_selector).jqGrid('setGridWidth', $("#list").width() - 60);
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
                url: '/api/usuarios/lista/index.php',
                datatype: "json",
                shrinkToFit: false,
                height: 'auto',
                postData: {
                    criterio: $("#txtCriterio").val()
                },
                mtype: 'POST',
                colNames: ["Acciones", 'ID', 'Nombre Usuario', 'Nombre Completo', 'Email', 'Empresa', 'Rol', 'Almacenes'],
                colModel: [
                    //{name:'id',index:'id', width:60, sorttype:"int", editable: false},
                     {
                        name: 'myac',
                        index: '',
                        width: 80,
                        fixed: true,
                        sortable: false,
                        resize: false,
                        formatter: imageFormat
                    }, {
                        name: 'id_user',
                        index: 'id_user',
                        width: 20,
                        editable: false,
                        hidden: true,
                        sortable: false
                    }, {
                        name: 'cve_usuario',
                        index: 'cve_usuario',
                        width: 140,
                        editable: false,
                        sortable: false
                    },
                    // {name:'des_usuario',index:'des_usuario',width:150, editable:false, sortable:false},
                    {
                        name: 'nombre_completo',
                        index: 'nombre_completo',
                        width: 260,
                        editable: false,
                        sortable: false
                    }, {
                        name: 'email',
                        index: 'email',
                        width: 255,
                        editable: false,
                        sortable: false
                    }, {
                        name: 'cve_cia',
                        index: 'cve_cia',
                        width: 275,
                        editable: false,
                        sortable: false
                    }, {
                        name: 'perfil',
                        index: 'perfil',
                        width: 215,
                        editable: false,
                        sortable: false
                    }, {
                        name: 'almacenes',
                        index: 'almacenes',
                        width: 215,
                        editable: false,
                        sortable: false
                    },
                ],
                rowNum: 30,
                rowList: [30, 40, 50],
                pager: pager_selector,
                sortname: 'cve_usuario',
                viewrecords: true,
                sortorder: "desc"
            });

            // Setup buttons
            $("#gridtable-usuarios").jqGrid('navGrid', '#grid-pager', {
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
            /**
             * Crea los botones de acción
             **/
            function imageFormat(cellvalue, options, rowObject) {
                var id_usuario = rowObject[1];
                $("#hiddenIDUsuario").val(id_usuario);
                 var html = '';

                if (id_usuario != "1") {
                    html += '<a href="#" onclick="editar(\'' + id_usuario + '\')"><i class="fa fa-edit" alt="Editar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
                    html += '<a href="#" onclick="borrar(\'' + id_usuario + '\')"><i class="fa fa-eraser" alt="Borrar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
                }
                return html;
            }


            /*function aceSwitch(cellvalue, options, cell) {
                setTimeout(function() {
                    $(cell).find('input[type=checkbox]')
                        .addClass('ace ace-switch ace-switch-5')
                        .after('<span class="lbl"></span>');
                }, 0);
            }*/


            //enable datepicker
            /*function pickDate(cellvalue, options, cell) {
                setTimeout(function() {
                    $(cell).find('input[type=text]')
                        .datepicker({
                            format: 'yyyy-mm-dd',
                            autoclose: true
                        });
                }, 0);
            }*/


            /*function beforeDeleteCallback(e) {
                var form = $(e[0]);
                if (form.data('styled')) return false;

                form.closest('.ui-jqdialog').find('.ui-jqdialog-titlebar').wrapInner('<div class="widget-header" />')
                style_delete_form(form);

                form.data('styled', true);
            }*/

            
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

            /**
             * Causa cierto parpadeo al volver a cargar o al navegar por la cuadrícula
             * puede ser posible tener algún formateador personalizado para hacer esto a 
             * medida que se crea la grilla para evitar esto o volver a los estilos 
             * predeterminados de casilla de verificación del navegador para la grilla
             */
            function styleCheckbox(table) {}


            /**
             * A diferencia de los iconos de navButtons, los íconos de acción en las 
             * filas parecen estar codificados puedes cambiarlos aquí si quieres
             */ 
            function updateActionIcons(table) {}

            /** Replace icons with FontAwesome icons like above */
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
            $('#gridtable-usuarios').jqGrid('clearGridData')
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
                    id_user: _codigo,
                    action: "tieneAlmacen"
                },
                beforeSend: function(x) {
                    if (x && x.overrideMimeType) {
                        x.overrideMimeType("application/json;charset=UTF-8");
                    }
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
                    } else {
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
                            function() {
                                var borro = localStorage.getItem("borro") | 0;
                                localStorage.setItem('borro', borro + 1);
                                $.ajax({
                                    url: '/api/usuarios/update/index.php',
                                    type: "POST",
                                    dataType: "json",
                                    data: {
                                        id_user: _codigo,
                                        action: "delete"
                                    },
                                    beforeSend: function(x) {
                                        if (x && x.overrideMimeType) {
                                            x.overrideMimeType("application/json;charset=UTF-8");
                                        }
                                    },
                                    dataType: 'json'
                                }).done(function(data) {
                                    if (data.success) {
                                        ReloadGrid();
                                        reloadGridUsuariosInactivos();
                                        swal("Borrado", "El usuario ha sido borrado exitosamente", "success");
                                    } else {
                                        swal("Error", "Ocurrió un error al eliminar el usuario", "error");
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
            Control.val = Solo_Numerico(Control.val);
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
                    id_user: id_usuario,
                    action: "load"
                },
                beforeSend: function(x) {
                    if (x && x.overrideMimeType) {
                        x.overrideMimeType("application/json;charset=UTF-8");
                    }
                },
                url: '/api/usuarios/update/index.php',
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
                        $("#image").attr("src", "/img/imageperfil/" + data.image_url);
                        $("#image").attr("ima", data.image_url);
                        $("#compania_edit").val(data.cve_cia);
                        $('#compania_edit').trigger("chosen:updated");
                        $('#pass_user_edit').val(data.pwd_usuario);
                        $('#cpass_user_edit').val(data.pwd_usuario);
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

        var l = $('#myform');
        l.submit(function(e) {

            $("#btnCancel").hide();


            //l.ladda( 'start' );
            if ($("#nombre_user_edit").val() && $("#compania_edit").val() &&
                $("#pass_user_edit").val() && $("#nombrec_user_edit").val() && $("#email_user_edit").val() &&
                $("#rol_usuario").val()) {
                if ($('#image_user_edit').val()) {
                    var path = $('#image_user_edit').val();
                    var filename = path.replace(/^.*\\/, "");
                    uploadFile();
                } else if ($('#image').attr('ima') != "" && !$('#image_user_edit').val()) {
                    filename = $("#image").attr("ima");
                } else {
                    filename = "noimage.jpg"
                }
                $.post('/api/usuarios/update/index.php', {
                            cve_usuario: $("#nombrec_user_edit").val(), //clave_user_edit
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
                        function(response) {
                            console.log(response);
                        }, "json")
                    .always(function() {
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
            }
            /*else{
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
        function uploadFile() {
            var input = document.getElementById("image_user_edit");
            file = input.files[0];

            if (file != undefined) {
                formData = new FormData();
                if (!!file.type.match(/image.*/)) {
                    formData.append("image", file);
                    $.ajax({
                        url: "/app/template/page/perfil/upload.php",
                        type: "POST",
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(data) {
                            //alert(data);
                        },
                        error: function(xhr, ajaxOptions, thrownError) {
                            //console.log(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                            //alert(thrownError);
                        }
                    });
                } else {
                    alert('Not a valid image!');
                }
            } else {
                alert('Input something!');
            }
        }
    </script>


    <!-- Segundas Grid -->
    <script type="text/javascript">
        //////////////////////////////////////////////////////////Aqui se contruye el Grid//////////////////////////////////////////////////////////
        $(function($) {
            var grid_selector = "#gridtable-usuarios-inactivos";
            var pager_selector = "#grid-pager2";

            //resize to fit page size
         

            $(window).on('resize.jqGrid', function() {
                $("#gridtable-usuarios-inactivos").jqGrid('setGridWidth', $("#coModal .modal-content").width() - 35);
            })

            $(window).triggerHandler('resize.jqGrid');

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
                url: '/api/usuarios/lista/index_i.php',
                datatype: "json",
                shrinkToFit: false,
                height: 250,
                postData: {
                    criterio: $("#txtCriterio1").val()
                },
                mtype: 'POST',
                colNames: ['ID', 'Perfil', 'Nombre', "Recuperar"],
                colModel: [
                    //{name:'id',index:'id', width:60, sorttype:"int", editable: false},
                    {
                        name: 'id_user',
                        index: 'id_user',
                        width: 200,
                        editable: false,
                        sortable: false
                    }, {
                        name: 'cve_usuario',
                        index: 'cve_usuario',
                        width: 200,
                        editable: false,
                        sortable: false
                    }, {
                        name: 'des_usuario',
                        index: 'des_usuario',
                        width: 600,
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
                sortname: 'cve_usuario',
                viewrecords: true,
                sortorder: "desc"
            });

            // Setup buttons
            $("#gridtable-usuarios-inactivos").jqGrid('navGrid', '#grid-pager2', {
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
                var id_usuario = rowObject[0];
                $("#hiddenIDUsuario").val(id_usuario);
       
                var html = '';
                html += '<a href="#" onclick="recovery(\'' + id_usuario + '\')"><i class="fa fa-check" alt="Borrar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
           
                return html;
            }

            /*function aceSwitch(cellvalue, options, cell) {
                setTimeout(function() {
                    $(cell).find('input[type=checkbox]')
                        .addClass('ace ace-switch ace-switch-5')
                        .after('<span class="lbl"></span>');
                }, 0);
            }*/
            //enable datepicker
            /*function pickDate(cellvalue, options, cell) {
                setTimeout(function() {
                    $(cell).find('input[type=text]')
                        .datepicker({
                            format: 'yyyy-mm-dd',
                            autoclose: true
                        });
                }, 0);
            }*/

            /*function beforeDeleteCallback(e) {
                var form = $(e[0]);
                if (form.data('styled')) return false;

                form.closest('.ui-jqdialog').find('.ui-jqdialog-titlebar').wrapInner('<div class="widget-header" />')
                style_delete_form(form);

                form.data('styled', true);
            }*/

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
        function reloadGridUsuariosInactivos() {
            $('#gridtable-usuarios-inactivos').jqGrid('clearGridData')
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
                    id_user: _codigo,
                    action: "recovery"
                },
                beforeSend: function(x) {
                    if (x && x.overrideMimeType) {
                        x.overrideMimeType("application/json;charset=UTF-8");
                    }
                },
                url: '/api/usuarios/update/index.php',
                success: function(data) {
                    if (data.success == true) {
                        //$('#codigo').prop('disabled', true);
                        ReloadGrid();
                        reloadGridUsuariosInactivos();
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
                        cve_usuario: cve_usuario,
                        action: "exists"
                    },
                    beforeSend: function(x) {
                        if (x && x.overrideMimeType) {
                            x.overrideMimeType("application/json;charset=UTF-8");
                        }
                    },
                    url: '/api/usuarios/update/index.php',
                    success: function(data) {
                        if (data.success == false) {
                            $("#CodeMessage").html("");
                            $("#btnSave").prop('disabled', false);
                        } else {
                            $("#CodeMessage").html(" Clave de usuario ya existe");
                            $("#btnSave").prop('disabled', true);
                        }
                    }

                });

            } else {
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

            } else {
                $("#emailMessage").html("Por favor, ingresar un Correo Electrónico válido");
                $("#btnSave").prop('disabled', true);
            }
        });


        $("#cpass_user_edit").keyup(function(e) {

            var contrasena = $("#pass_user_edit").val();
            var ccontrasena = $("#cpass_user_edit").val();

            if (contrasena == ccontrasena) {
                $("#passMessage").html("");
                $("#btnSave").prop('disabled', false);

            } else {
                $("#passMessage").html("Las Contraseñan deben coincidir");
                $("#btnSave").prop('disabled', true);
            }
        });

        $("#txtCriterio").keyup(function(event) {
            if (event.keyCode == 13) {
                $("#buscarU").click();
            }
        });
    </script>