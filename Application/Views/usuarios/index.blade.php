<?php 
include_once $_SERVER['DOCUMENT_ROOT']."/app/host.php";
$listaProv = new \Proveedores\Proveedores();
?>

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
                                        <button onclick="agregar()" class="btn btn-primary permiso_registrar" type="button"><i class="fa fa-plus"></i> Nuevo</button>
                                    </div>
                                    <input type="text" class="form-control" name="txtCriterio" id="txtCriterio" placeholder="Buscar por Usuario...">
                                    <div class="input-group-btn">                                  
                                        <button onclick="ReloadGrid()" type="submit" class="btn btn-primary" id="buscarU">
                                            <span class="fa fa-search"></span> Buscar
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-5 col-lg-5" style="margin-top:5px">
                                <button class="btn btn-primary pull-center" type="button" id="inactivos"><i class="fa fa-search"></i> Usuarios inactivos</button>
                            </div>

                            <div class="col-md-4 col-lg-4" style="margin-top:5px; margin-left: 15px;">
                                <a href="#" id="generarReporteUsuarios" class="btn btn-primary" style="margin: 10px;float: right;">
                                    <span class="fa fa-file-excel-o"></span> Generar Reporte de Usuarios
                                </a>
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
                            
                            <div class="row" style="margin-bottom: 15px; display: none;">                                
                                <div class="col-md-12">
                                    <div class="pull-right">                                            
                                        <button onclick="cancelar()" class="btn btn-white" type="button"><i class="fa fa-ban"></i> Cancelar</button>
                                     @if($allowAdd->Activo==1)
                                        <button type="submit" class="btn btn-primary" id="btnSave"><i class="fa fa-save"></i> Guardar</button>
                                    @endif
                                    </div>
                                </div>
                            </div>

                            <div class="row">

                                <div class="col-lg-6 b-r">
                                    <div class="row">

                                        <input id="clave_user_edit" type="hidden">

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Clave | Nombre Usuario</label>
                                                <input id="nombrec_user_edit" type="text" placeholder="Clave de Usuario" class="form-control" value="" maxlength="20" required="true">
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
                                                <input id="desc_usuario" type="text" placeholder="Descripción de Usuario" class="form-control" value = "">
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
                                                    @foreach( $empresas as $value )
                                                        <option value="{{ $value->cve_cia }}">{{ $value->des_cia }}</option>
                                                    @endforeach;
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
                                                    @foreach( $roles as $value )
                                                        <option value="{{ $value->id_role }}">{{ $value->rol }}</option>
                                                    @endforeach
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
<?php 

$usuario_sesion = $_SESSION['cve_usuario'];
$almacenes_usuario = \db()->prepare("SELECT c.clave, c.nombre FROM c_almacenp c LEFT JOIN trel_us_alm t ON t.cve_almac = c.clave LEFT JOIN c_usuario u ON u.cve_usuario = t.cve_usuario WHERE u.cve_usuario = '{$usuario_sesion}'");
$almacenes_usuario->execute();
$almacenes_usuario = $almacenes_usuario->fetchAll(PDO::FETCH_ASSOC);
 ?>
                                    <div style="display: inline-flex; white-space: nowrap;width: 150px;">
                                    <input type="radio" name="proveedor_cliente_usuario" id="usuario_sistema" class="form-control" value="0" style="display: inline-block; width: 20px;cursor: pointer;">
                                    <span style="display: inline-block;margin: 12px;font-weight: bold;">Usuario Sistema</span>
                                    </div>

                                    <div style="display: inline-flex; white-space: nowrap;width: 165px;">
                                    <input type="radio" name="proveedor_cliente_usuario" id="usuario_apk" class="form-control" value="A" style="display: inline-block; width: 20px;cursor: pointer;">
                                    <span style="display: inline-block;margin: 12px;font-weight: bold;">Usuario Operativo</span>
                                    </div>

                                    <div style="display: inline-flex; white-space: nowrap;width: 150px;">
                                    <input type="radio" name="proveedor_cliente_usuario" id="usuario_apk_web" class="form-control" value="AW" style="display: inline-block; width: 20px;cursor: pointer;">
                                    <span style="display: inline-block;margin: 12px;font-weight: bold;">Usuario Mixto</span>
                                    </div>

                                    <div style="display: inline-flex; white-space: nowrap;width: 210px;">
                                    <input type="radio" name="proveedor_cliente_usuario" id="usuario_vendedor" class="form-control" value="3" style="display: inline-block; width: 20px;cursor: pointer;">
                                    <span style="display: inline-block;margin: 12px;font-weight: bold;">Usuario Venta | Preventa</span>
                                    </div>

                                    <div style="display: inline-flex; white-space: nowrap;width: 150px;">
                                    <input type="radio" name="proveedor_cliente_usuario" id="usuario_cliente" class="form-control" value="1" style="display: inline-block; width: 20px;cursor: pointer;">
                                    <span style="display: inline-block;margin: 12px;font-weight: bold;">Usuario Cliente</span>
                                    </div>

                                    <div style="display: inline-flex; white-space: nowrap;width: 170px;">
                                    <input type="radio" name="proveedor_cliente_usuario" id="usuario_proveedor" class="form-control" value="2" style="display: inline-block; width: 20px;cursor: pointer;">
                                    <span style="display: inline-block;margin: 12px;font-weight: bold;">Usuario Proveedor</span>
                                    </div>

                                    <br>
                                    <div id="almacenes_usuario" style="display: none;">
                                    <label>Almacenes</label>

                                    <select id="almacen_asignado" class="chosen-select form-control">
                                        <option value="0">Asignar Almacén a Cliente</option>
                                        @foreach( $almacenes_usuario AS $r )
                                        <option value="<?php echo $r['clave']; ?>"><?php echo "( ".$r['clave']." ) - ".$r['nombre']; ?></option>
                                        @endforeach
                                    </select>

                                    <br><br>

                    <label>Cliente*</label>
                    <input class="form-control" name="cliente_buscar" id="cliente_buscar" placeholder="Código del Cliente">
                    <input class="form-control" name="cliente" id="cliente" placeholder="Código del Cliente" style="display: none;">
                    <br><br>
                  <label>Clave y Nombre Cliente</label>
                       <select id="desc_cliente" name="desc_cliente" class="form-control">
                       </select>


                                    </div>


                                    <br>
                                    <div id="proveedor_select" style="display: none;">
                                    <label>Proveedor</label>

                                    <select class="form-control" id="proveedor_usuario" style="margin: 0;">
                                        <option value="">Seleccione Proveedor</option>
                                        <?php foreach( $listaProv->getAll() AS $p ): ?>
                                        <option value="<?php echo $p->ID_Proveedor; ?>"><?php echo $p->Nombre; ?></option>
                                        <?php endforeach; ?>
                                    </select>

                                    <br><br>

                                    <label>Almacen Proveedor</label>

                                    <select id="almacen_asignado_proveedor" class="chosen-select form-control">
                                        <option value="0">Asignar Almacén a Proveedor</option>
                                        @foreach( $almacenes_usuario AS $r )
                                        <option value="<?php echo $r['clave']; ?>"><?php echo "( ".$r['clave']." ) - ".$r['nombre']; ?></option>
                                        @endforeach
                                    </select>

                                    <br><br>

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
                                        @if($allowAdd->Activo==1)
                                        <button type="submit" class="btn btn-primary btnSave" id="btnSave"><i class="fa fa-save"></i> Guardar</button>
                                        @endif
                                    </div>
                                </div>
                            </div>

                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <input type="hidden" name="usuario_sesion" id="usuario_sesion" value="<?php echo $usuario_sesion; ?>">

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

            $("#usuario_sistema").prop('checked',true);
            $("#usuario_sistema").val("0");

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
                    cve_almacen: <?php echo "'".$_SESSION['cve_almacen']."'"; ?>,
                    usuario_sesion: $("#usuario_sesion").val(),
                    criterio: $("#txtCriterio").val()
                },
                mtype: 'POST',
                colNames: ["Acciones", 'ID', 'Nombre Usuario', 'Nombre Completo', 'Email', 'Rol', 'Almacenes', 'Empresa'],
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
                    }, {
                        name: 'cve_cia',
                        index: 'cve_cia',
                        width: 275,
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
                    if($("#permiso_editar").val() == 1)
                    {
                    html += '<a href="#" onclick="editar(\'' + id_usuario + '\')"><i class="fa fa-edit" alt="Editar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
                    }
                    if($("#permiso_eliminar").val() == 1)
                    {
                    html += '<a href="#" onclick="borrar(\'' + id_usuario + '\')"><i class="fa fa-eraser" alt="Desactivar Usuario" title="Desactivar Usuario"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
                    }
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
                        cve_almacen: <?php echo "'".$_SESSION['cve_almacen']."'"; ?>,
                        usuario_sesion: $("#usuario_sesion").val(),
                        criterio: $("#txtCriterio").val(),
                    },
                    datatype: 'json',
                    page: 1
                })
                .trigger('reloadGrid', [{
                    current: true
                }]);
        }


        $("#generarReporteUsuarios").click(function(){

            var almacen = $("#almacen").val(),
            criterio = $("#criteriob").val(),
            tipopedido = $("#tipopedido").val(),
            ciudad_pedido_list = $("#ciudad_pedido_list").val(),
            ruta_pedido_list = $("#ruta_pedido_list").val(),
            fechaInicio = $("#fechai").val(),
            fechaFin = $("#fechaf").val();

            $(this).attr("href", "/api/koolreport/excel/reporte_usuarios/export.php");

        });

//******************************************************************************************************
//******************************************************************************************************
    $("#nombrec_user_edit").keyup(function(e) {
        var claveCode = $(this).val();
        var claveCodeRegexp = new RegExp("^[a-zA-Z0-9]{1,20}$");
        var disable = false;
        if (claveCodeRegexp.test(claveCode)) 
        {
            $("#CodeMessage").html("");
            $("#btnSave, .btnSave").prop('disabled', false);
            disable = true;
        }
        else
        {
            $("#CodeMessage").html("Caracter Inválido");
            $("#btnSave, .btnSave").prop('disabled', true);
        }

//************************************************************************************************************
//                                      VERIFICAR SI EXISTE CLAVE DE USUARIO
//************************************************************************************************************
        $.ajax({
            url: '/api/usuarios/update/index.php',
            data: {
                action: 'ExisteUsuario',
                usuario: $(this).val()
            },
            dataType: 'json',
            method: 'GET',
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            success: function(data) 
            {
                console.log("SUCCESS: ", data);

                if(data.success == 1)
                {
                    $("#btnSave, .btnSave").prop('disabled', true);
                }


            }, error: function(data){
                console.log("ERROR: ", data);
            }

        });
//************************************************************************************************************
    });


        $("#cliente_buscar").on('keyup', function(e)
        {
            var cliente = e.target.value;
            //console.log("cliente.length = ", cliente.length);
            if(cliente.length > 2)
                BuscarCliente(cliente);
            else
                BuscarCliente("Borrar_La_Lista_de_Clientes");
        });

        function BuscarCliente(cliente)
        {
            console.log("Cliente = ", cliente);
            //document.getElementById("agregar_destinatario").removeAttribute("disabled");
            $.ajax({
                url: '/api/nuevospedidos/lista/index.php',
                data: {
                    action: 'getClientesSelect',
                    id_almacen: <?php echo $_SESSION['id_almacen']; ?>,
                    cliente: cliente
                },
                dataType: 'json',
                method: 'GET',
                beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                },
                success: function(data) 
                {
                    console.log("SUCCESS: ", data);
                    //var combo = data.combo;
                    //if(combo.length > 36 || data.direccion.length > 2){
                        var desc_cliente = document.getElementById("desc_cliente");
                        if(data.combo !== '' && data.find == true){
                            desc_cliente.innerHTML = data.combo;
                            var text = desc_cliente.options[desc_cliente.selectedIndex].text;
                            //document.getElementById("txt-direc").value = text;
                            desc_cliente.removeAttribute('disabled');
                            //$("#cliente").val(data.clave_cliente);
                            //$("#desc_cliente").val("["+data.clave_cliente+"] - "+data.nombre_cliente);
                            $("#cliente").val(data.firsTValue);
                            //BuscarDestinatario(data.firsTValue);
                            console.log("#cliente = ", $("#cliente").val());
                        }
                        else
                        {
                            //$("#destinatario, #agregar_destinatario").prop("disabled", true);
                            $("#cliente").val("");
                            $("#desc_cliente").val("");
                            console.log("#cliente = ", $("#cliente").val());
                        }

                        $(".chosen-select").trigger("chosen:updated");
                }, error: function(data){
                    console.log("ERROR: ", data);
                }

            });
        }
//******************************************************************************************************
//******************************************************************************************************
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
                    /*
                    if (data.success == true) {
                        swal({
                            title: "¡Alerta!",
                            text: "El usuario esta siendo usado en este momento",
                            type: "warning",
                            showCancelButton: false,
                        });
                    } else {
                        */
                        swal({
                                title: "¿Está seguro que desea desactivar el usuario?",
                                text: "Al desactivar el usuario no podrá usarlo en diferentes movimientos o tareas, pero si podrá consultar sus movimientos ya hechos",
                                type: "warning",
                                showCancelButton: true,
                                confirmButtonColor: "#DD6B55",
                                confirmButtonText: "Desactivar",
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
                                        swal("Usuario Desactivado", "El usuario ha sido desactivado exitosamente", "success");
                                    } else {
                                        swal("Error", "Ocurrió un error al eliminar el usuario", "error");
                                    }
                                });
                            });
                    //}
                }
            });
        }

        $("#usuario_sistema, #usuario_cliente, #usuario_proveedor, #usuario_vendedor, #usuario_apk, #usuario_apk_web").change(function(){

            var tipo_usuario = $("input[name=proveedor_cliente_usuario]:checked").val();

            if(tipo_usuario == 0 || tipo_usuario == 3 || tipo_usuario == 'A' || tipo_usuario == 'AW')
            {
                //$(this).val("1");
                $("#almacenes_usuario").hide();
                $("#proveedor_select").hide();
            }
            else if(tipo_usuario == 1)
            {
                //$(this).val("1");
                $("#almacenes_usuario").show();
                $("#proveedor_select").hide();
            }
            else
            {
                //$(this).val("2");
                $("#almacenes_usuario").hide();
                $("#proveedor_select").show();
            }
        });

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
                        console.log("Edit ", data);
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

                        if(data.es_cliente == 0)
                        {
                            if(data.web_apk == 'A')
                            {
                                $("#usuario_apk").prop('checked',true);
                                $("#usuario_apk").val("A");
                            }
                            else if(data.web_apk == 'AW')
                            {
                                $("#usuario_apk_web").prop('checked',true);
                                $("#usuario_apk_web").val("AW");
                            }
                            else
                            {
                                $("#usuario_sistema").prop('checked',true);
                                $("#usuario_sistema").val("0");
                            }
                            $("#almacenes_usuario").hide();
                        }
                        if(data.es_cliente == 3)
                        {
                            $("#usuario_vendedor").prop('checked',true);
                            $("#usuario_vendedor").val("3");
                            $("#almacenes_usuario").hide();
                        }
                        else if(data.es_cliente == 1)
                        {
                            $("#usuario_cliente").prop('checked',true);
                            $("#almacenes_usuario").show();
                            $("#cliente_buscar").val(data.cve_cliente);
                            $("#desc_cliente").append("<option value='"+data.cve_cliente+"'>Cliente: "+data.cve_cliente+"</option>");
                            $("#desc_cliente").val(data.cve_cliente);
                            $("#desc_cliente").trigger("chosen:updated");
                            $("#almacen_asignado").val(data.cve_almacen);
                            $("#almacen_asignado").trigger("chosen:updated");
                        }
                        else if(data.es_cliente == 2)
                        {
                            $("#usuario_proveedor").prop('checked',true);
                            $("#proveedor_select").show();
                            $("#proveedor_usuario").val(data.cve_proveedor);
                            $("#almacen_asignado_proveedor").val(data.cve_almacen);

                            $("#proveedor_usuario").trigger("chosen:updated");
                            $("#almacen_asignado_proveedor").trigger("chosen:updated");
                        }

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
            e.preventDefault();

            if($('#rol_usuario').val() == "")
               return;

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

                console.log("cve_usuario = ", $("#nombrec_user_edit").val());
                console.log("des_usuario = ", $("#desc_usuario").val());
                console.log("nombre_completo = ", $("#nombre_user_edit").val());
                console.log("cve_cia = ", $("#compania_edit").val());
                console.log("pwd_usuario = ", $("#pass_user_edit").val());
                console.log("Clave = ", $('#hiddenIDUsuario').val());
                console.log("email = ", $("#email_user_edit").val());
                console.log("perfil = ", $('#rol_usuario').val());
                console.log("action = ", $("#hiddenAction").val());
                console.log("es_cliente = ", $("input[name=proveedor_cliente_usuario]:checked").val());
                console.log("almacen_asignado = ", $("#almacen_asignado").val());
                console.log("almacen_asignado_proveedor = ", $("#almacen_asignado_proveedor").val());
                console.log("proveedor_usuario = ", $("#proveedor_usuario").val());
                console.log("cliente_asignado = ", $("#desc_cliente").val());


                if($('#rol_usuario').val() == '')
                {
                    swal({
                        title: "¡Alerta!",
                        text: "Debe asignar un perfil",
                        type: "warning",
                        showCancelButton: false
                    });
                    return;
                }

                if($("input[name=proveedor_cliente_usuario]:checked").val() == 0)
                {
                    $('#almacen_asignado').val("");
                    $('#desc_cliente').val("");
                    $('#proveedor_usuario').val("");
                    $('#almacen_asignado_proveedor').val("");
                }
                else if($("input[name=proveedor_cliente_usuario]:checked").val() == 1)
                {
                    //$('#almacen_asignado').val("");
                    //$('#desc_cliente').val("");
                    $('#proveedor_usuario').val("");
                    $('#almacen_asignado_proveedor').val("");
                }
                else if($("input[name=proveedor_cliente_usuario]:checked").val() == 2)
                {
                    $('#almacen_asignado').val("");
                    $('#desc_cliente').val("");
                    //$('#proveedor_usuario').val("");
                    //$('#almacen_asignado_proveedor').val("");
                }

                if($("input[name=proveedor_cliente_usuario]:checked").val() == 1 && ($("#almacen_asignado").val() == "0" || $("#desc_cliente").val() == ""))
                {
                    swal({
                        title: "¡Alerta!",
                        text: "Debe asignar un almacén y un cliente al usuario tipo cliente",
                        type: "warning",
                        showCancelButton: false
                    });
                    return;
                }

                if($("input[name=proveedor_cliente_usuario]:checked").val() == 2 && $("#proveedor_usuario").val() == "")
                {
                    swal({
                        title: "¡Alerta!",
                        text: "Debe asignar un proveedor ",
                        type: "warning",
                        showCancelButton: false
                    });
                    return;
                }

                if($("input[name=proveedor_cliente_usuario]:checked").val() == 2 && $("#almacen_asignado_proveedor").val() == "0")
                {
                    swal({
                        title: "¡Alerta!",
                        text: "Debe asignar un almacén al proveedor ",
                        type: "warning",
                        showCancelButton: false
                    });
                    return;
                }

                var almacen_asig = "";
                if($("input[name=proveedor_cliente_usuario]:checked").val() == 1)
                    almacen_asig = $("#almacen_asignado").val();
                else if($("input[name=proveedor_cliente_usuario]:checked").val() == 2)
                    almacen_asig = $("#almacen_asignado_proveedor").val();


                //console.log("almacen_asignado = ", almacen_asig);
                //return;

                $.post('/api/usuarios/update/index.php', {
                            cve_usuario: $("#nombrec_user_edit").val(), //clave_user_edit
                            des_usuario: $("#desc_usuario").val(),
                            usuario_log: "<?php echo $_SESSION['cve_usuario']; ?>",
                            nombre_completo: $("#nombre_user_edit").val(),
                            cve_cia: $("#compania_edit").val(),
                            pwd_usuario: $("#pass_user_edit").val(),
                            Clave: $('#hiddenIDUsuario').val(),
                            email: $("#email_user_edit").val(),
                            perfil: $('#rol_usuario').val(),
                            action: $("#hiddenAction").val(),
                            es_cliente: $("input[name=proveedor_cliente_usuario]:checked").val(),
                            almacen_asignado: almacen_asig,
                            cliente_asignado: $("#desc_cliente").val(),
                            proveedor_asignado: $("#proveedor_usuario").val(),
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
                        
                        //window.location.reload();
                        //ReloadGrid();

                    }).fail(function(data){
                        console.log("ERROR = ",data);
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
                    cve_almacen: <?php echo "'".$_SESSION['cve_almacen']."'"; ?>,
                    usuario_sesion: $("#usuario_sesion").val(),
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
                        cve_almacen: <?php echo "'".$_SESSION['cve_almacen']."'"; ?>,
                        usuario_sesion: $("#usuario_sesion").val(),
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