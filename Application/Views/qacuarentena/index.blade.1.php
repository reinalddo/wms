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

<script src="/js/plugins/ladda/spin.min.js"></script>
<script src="/js/plugins/ladda/ladda.min.js"></script>
<script src="/js/plugins/ladda/ladda.jquery.min.js"></script>
<script src="/js/moment.js"></script>

<!-- Select -->
<script src="/js/select2.js"></script>

<!-- Data picker -->
<script src="/js/plugins/datapicker/bootstrap-datepicker.js"></script>
<script src="/js/plugins/dataTables/jquery.dataTables.min.js"></script>
<script src="/js/plugins/dataTables/dataTables.buttons.min.js"></script>
<script src="/js/plugins/dataTables/buttons.flash.min.js"></script>
<script src="/js/plugins/dataTables/jszip.min.js"></script>
<script src="/js/plugins/dataTables/pdfmake.min.js"></script>
<script src="/js/plugins/dataTables/vfs_fonts.js"></script>
<script src="/js/plugins/dataTables/buttons.html5.min.js"></script>
<script src="/js/plugins/dataTables/buttons.print.min.js"></script>

<script src="/js/plugins/chosen/chosen.jquery.js"></script>
<link rel="stylesheet" href="/css/plugins/chosen/chosen.css">

<div class="wrapper wrapper-content  animated " id="list">
    <h3>Existencia Ubicación</h3>
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-title">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="email">Almacén</label>
                                <select name="almacen" id="almacen" class="chosen-select  form-control">
                                    <option value="">Seleccione Almacén</option>
                                @foreach ($almacenes as $value)
                                    <option value="{{ $value->id }}">{{ $value->clave .' - '. $value->nombre }}</option>
                                @endforeach
                            </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="email">Zona de Almacenaje</label>
                                <select name="zona" id="zona" class="chosen-select form-control">
                                <option value="">Seleccione Zona</option>
                            </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="email">Articulo</label>
                                <select name="articulo" id="articulo" class="chosen-select form-control">
                            <option value="">Seleccione Articulo</option>
                            </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label for="email">&#160;&#160;</label>
                            <div class="form-group">
                                <button id="search" name="singlebutton" class="btn btn-primary">
                                    <span class="fa fa-search"></span> Buscar
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <div class="ibox-content">
                <div class="jqGrid_wrapper">
                    <table id="grid-main"></table>
                    <div id="grid-main-pager"></div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>


<script type="text/javascript">
    //////////////////////////////////////////////////////////Aqui se contruye el Grid//////////////////////////////////////////////////////////
    $(function($) {
        var grid_selector = "#grid-main";
        var pager_selector = "#grid-main-pager";

        //resize to fit page size
        $(window).on('resize.jqGrid', function() {
            $(grid_selector).jqGrid('setGridWidth', $("#list").width() - 60);
        })
            //resize on sidebar collapse/expand
        var parent_column = $(grid_selector).closest('[class*="col-"]');
        $(document).on('settings.ace.jqGrid', function(ev, event_name, collapsed) {
            if (event_name === 'sidebar_collapsed' || event_name === 'main_container_fixed') {
                setTimeout(function() {
                    $(grid_selector).jqGrid('setGridWidth', parent_column.width());
                }, 0);
            }
        })

        $(grid_selector).jqGrid({
            url: '/api/v1/qacuarentena/all',
            datatype: "json",
            shrinkToFit: false,
            height: 'auto',
            postData: {
                action: 'all',
                lote: $("#lote").val(),
                almacen: $("#almacen").val(),
                articulo: $("#producto").val(),
                zona: $("#zona").val(),
            },
            mtype: 'GET',
            colNames: [
                'Almacén', 
                'Zona de Almacenaje', 
                'Pasillo', 
                'Rack', 
                'Nivel', 
                'Sección', 
                'Ubicación', 
                "Clave",
                "Descripción",
                "Lote",
                "Caducidad",
                "N. Serie",
                "Cantidad",
                "Entrada",
                "Responsable",
                "Salida",
                "Responsable",
                ''
            ],
            colModel: [
                //{name:'id',index:'id', width:60, sorttype:"int", editable: false},
                { name: 'almacen', index: 'almacen',  width: 100, editable: false, hidden: false,  sortable: false },
                { name: 'zona', index: 'zona', width: 140, editable: false, sortable: false },
                { name: 'pasillo',  index: 'pasillo',  width: 100, editable: false, sortable: false }, 
                { name: 'rack',  index: 'rack',  width: 100,  editable: false,  sortable: false},
                { name: 'nivel', index: 'nivel',  width: 100,  editable: false, sortable: false }, 
                { name: 'seccion', index: 'seccion',  width: 200,  editable: false, sortable: false }, 
                { name: 'ubicacion', index: 'ubicacion',  width: 200, editable: false, sortable: false }, 
                { name: 'clave', index: 'clave', width: 100, editable: false, sortable: false }, 
                { name: 'descripcion',  index: 'descripcion',  width: 300,  editable: false, sortable: false }, 
                { name: 'lote', index: 'lote',  width: 215, editable: false,  sortable: false}, 
                { name: 'caducidad', index: 'caducidad', width: 100, editable: false, sortable: false }, 
                { name: 'nserie', index: 'nserie', width: 100, editable: false,  sortable: false }, 
                { name: 'cantidad', index: 'cantidad',  width: 100,  editable: false, sortable: false }, 
                { name: 'cuarentena_entrada', index: 'cuarentena_entrada',  width: 100, editable: false,  sortable: false }, 
                { name: 'cuarentena_usuario_entrada', index: 'cuarentena_usuario_entrada',  width: 100, editable: false, sortable: false},
                { name: 'cuarentena_salida', index: 'cuarentena_salida',  width: 215, editable: 100,  sortable: false }, 
                { name: 'cuarentena_usuario_saldia',  index: 'cuarentena_usuario_saldia',  width: 100,  editable: false,  sortable: false}, 
                {
                    name: 'myac',
                    index: '',
                    width: 80,
                    fixed: true,
                    sortable: false,
                    resize: false,
                    formatter: imageFormat
                },
            ],
            rowNum: 30,
            rowList: [30, 40, 50],
            pager: pager_selector,
            sortname: 'almacen',
            viewrecords: true,
            sortorder: "desc"
        });

        // Setup buttons
        $("#grid-main").jqGrid('navGrid', '#grid-main-pager', {
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
            var id_usuario = rowObject[0];
            $("#hiddenIDUsuario").val(id_usuario);
             var html = '';

            if (id_usuario != "1") {
                html += '<a href="#" onclick="editar(\'' + id_usuario + '\')"><i class="fa fa-edit" alt="Editar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
                html += '<a href="#" onclick="borrar(\'' + id_usuario + '\')"><i class="fa fa-eraser" alt="Borrar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            }
            return html;
        }

        
        function reloadPage() {
            var grid = $(grid_selector);
            $.ajax({
                url: '/qacuarentena/all',
                dataType: "json",
                success: function(data) {
                    grid.trigger("reloadGrid", [{
                        current: true
                    }]);
                },
                error: function() {}
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
        $('#grid-main').jqGrid('clearGridData')
            .jqGrid('setGridParam', {
                postData: {
                    lote: $("#lote").val(),
                    almacen: $("#almacen").val(),
                    articulo: $("#producto").val(),
                    zona: $("#zona").val(),
                },
                datatype: 'json',
                page: 1
            })
            .trigger('reloadGrid', [{
                current: true
            }]);
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

        $("#search").click(function() {
            if ($("#almacen").val() == ""){
                return;
            }  
            ReloadGrid();
        });

    });


    $('#almacen').change(function(e) {
        var almacen = $(this).val();
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                cve_almac: almacen,
                action: "getArticulosYZonasAlmacenConExistencia"
            },
            beforeSend: function(x) {
                if (x && x.overrideMimeType) {
                    x.overrideMimeType("application/json;charset=UTF-8");
                }
            },
            url: '/api/ubicaciones/update/index.php',
            success: function(data) {

                var options_articulos = $("#articulo");
                options_articulos.empty();
                options_articulos.append(new Option("Seleccione Artículo", ""));

                var options_zonas = $("#zona");
                options_zonas.empty();
                options_zonas.append(new Option("Seleccione Zona", ""));

                for (var i = 0; i < data.articulos.length; i++) {
                    options_articulos.append(new Option(data.articulos[i].id_articulo + " " + data.articulos[i].articulo, data.articulos[i].id_articulo));
                }

                for (var i = 0; i < data.zonas.length; i++) {
                    options_zonas.append(new Option(data.zonas[i].clave + " " + data.zonas[i].descripcion, data.zonas[i].clave));
                }
                $("#articulo").trigger("chosen:updated");
                $("#zona").trigger("chosen:updated");

            }

        });


    });


</script>