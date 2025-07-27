<?php
$model_usuarios = new \Usuarios\Usuarios();
$usuarios = $model_usuarios->getAll(1);
?>

    <link href="/css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">
    <link href="/css/plugins/chosen/chosen.css" rel="stylesheet">
    <link href="/css/bootstrap.min.css" rel="stylesheet">
    <link href="/font-awesome/css/font-awesome.css" rel="stylesheet">
    <link href="/css/animate.css" rel="stylesheet">
    <link href="/css/style.css" rel="stylesheet">
    <link href="/css/plugins/clockpicker/clockpicker.css" rel="stylesheet">
    <link href="/css/plugins/iCheck/custom.css" rel="stylesheet">

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
    <!-- Select -->
    <script src="/js/plugins/chosen/chosen.jquery.js"></script>

    <!-- Drag & Drop Panel -->
    <script src="/js/dragdrop.js"></script>

    <!-- Data picker -->
    <script src="/js/plugins/datapicker/bootstrap-datepicker.js"></script>

    <script src="/js/plugins/iCheck/icheck.min.js"></script>

    <!-- Clock picker -->
    <script src="/js/plugins/clockpicker/clockpicker.js"></script>

    <!-- Sweet alert -->
    <script src="/js/plugins/sweetalert/sweetalert.min.js"></script>



    <style type="text/css">
        .input-group-addon {
            padding: 0px;
        }
        
        #clientesEditar li {
            cursor: pointer;
        }
        
        .wi {
            width: 90% !important;
        }
        
        .relative {
            position: relative;
        }
        
        .floating-button {
            position: absolute;
            right: 0;
            top: 40%;
            transform: translateY(-40%);
        }
        
        [aria-grabbed="true"] {
            background: #1ab394 !important;
            color: #fff !important;
        }
    </style>
    <!-- Mainly scripts -->
    <!-- Barra de nuevo y busqueda -->
    <div class="wrapper wrapper-content  animated fadeInRight">

        <h3>Asignación de Almacén a Usuario</h3>

        <div class="row">
            <div class="col-md-12">
                <div class="panel-body">
                    <div class="ibox ">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="email">Usuarios</label>
                                <div class="input-group">
                                    <select id="txtUsuario" class="chosen-select form-control">
                                    <!--<option value="">Usuario</option>-->
                                    <?php foreach( $usuarios AS $usuario ): ?>
                                        <?php if($usuario->Activo == 1):?>
                                        <option value="<?php echo $usuario->cve_usuario; ?>"><?php echo "($usuario->cve_usuario) ".$usuario->nombre_completo; ?></option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                                    <div class="input-group-addon">
                                        <button id="editar" class="btn btn-primary" type="button">
                                        <i class="fa fa-search"></i>&nbsp;&nbsp;Cargar Almacenes
                                    </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="ibox-content dragdrop">
                        <div class="form-group">
                            <div class="col-sm-12" style="margin-bottom: 30px">
                                <input type="checkbox" id="selectAll">
                                <label for="selectAll">Seleccionar Todo</label>
                            </div>
                        </div>
                        <label for="usuario">Almacen</label>
                        <div class="form-group" id="clientesEditar">
                            <div class="col-md-6" relative>
                                <label for="email">Almacenes Disponibles</label>
                                <ol data-draggable="target" id="from" class="wi">
                                </ol>
                                <button class="btn btn-primary floating-button" onclick="add('#from', '#to')">>></button>
                                <button class="btn btn-primary floating-button" onclick="remove('#to', '#from')" style="margin-top: 40px"><<</button>
                            </div>
                            <div class="col-md-6">
                                <label for="email">Almacenes Asignados</label>
                                <ol data-draggable="target" id="to" class="wi">
                                </ol>
                            </div>


                            <div class="col-md-12">
                                <button id="guardar" class="btn btn-primary pull-center permiso_registrar" type="button">
                                    <i class="fa fa-plus"></i> Planificar almacen
                                </button>
                            </div>


                        </div>


                    </div>


                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="conModal" role="dialog">
        <div class="vertical-alignment-helper">
            <div class="modal-dialog vertical-align-center">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Aviso de confirmación</h4>
                    </div>
                    <div class="modal-body">
                        <p>Los datos fueron guardados satisfactoriamente</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>


    <script type="text/javascript">
        $(document).on("click", "#editar", function() {
            $("#from .itemlist").remove();
            $("#to .itemlist").remove();
            if ($('#txtUsuario').val()) {
                $("#guardar").prop("disabled", false);
            }

            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    cve_usuario: $('#txtUsuario').val(),
                    action: "traerAlmacenesDeUsuario"
                },
                beforeSend: function(x) {
                    if (x && x.overrideMimeType) {
                        x.overrideMimeType("application/json;charset=UTF-8");
                    }
                },
                url: '/api/almacen/update/index.php',
                success: function(data) {

                    if (data.success == true) {

                        var arr = $.map(data.todosAlmacenes, function(el) {
                            return el;
                        })
                        arr.pop();
                        for (var i = 0; i < data.todosAlmacenes.length; i++) {

                            var ul = document.getElementById("from");
                            var li = document.createElement("li");
                            var checkbox = document.createElement("input");
                            checkbox.style.marginRight = "10px";
                            checkbox.setAttribute("type", "checkbox");
                            checkbox.setAttribute("class", "drag");
                            checkbox.setAttribute("onclick", "selectParent(this)");
                            li.appendChild(checkbox);
                            li.appendChild(document.createTextNode(data.todosAlmacenes[i].descripcion_almacen));
                            li.setAttribute("dayta-draggable", "item");
                            li.setAttribute("draggable", "false");
                            li.setAttribute("aria-draggable", "false");
                            li.setAttribute("aria-grabbed", "false");
                            li.setAttribute("tabindex", "0");
                            li.setAttribute("class", "itemlist");
                            li.setAttribute("onclick", "selectChild(this)");
                            li.setAttribute("value", data.todosAlmacenes[i].clave_almacen);
                            ul.appendChild(li);


                        }

                        var arr1 = $.map(data.almacenesUsuario, function(el) {
                            return el;
                        })
                        arr1.pop();
                        for (var i = 0; i < data.almacenesUsuario.length; i++) {

                            var ul = document.getElementById("to");
                            var li = document.createElement("li");
                            var checkbox = document.createElement("input");
                            checkbox.style.marginRight = "10px";
                            checkbox.setAttribute("type", "checkbox");
                            checkbox.setAttribute("class", "drag");
                            checkbox.setAttribute("onclick", "selectParent(this)");
                            li.appendChild(checkbox);
                            li.appendChild(document.createTextNode(data.almacenesUsuario[i].descripcion_almacen));
                            li.setAttribute("dayta-draggable", "item");
                            li.setAttribute("draggable", "false");
                            li.setAttribute("aria-draggable", "false");
                            li.setAttribute("aria-grabbed", "false");
                            li.setAttribute("tabindex", "0");
                            li.setAttribute("class", "itemlist");
                            li.setAttribute("onclick", "selectChild(this)");
                            li.setAttribute("value", data.almacenesUsuario[i].clave_almacen);
                            ul.appendChild(li);

                        }

                    }

                }
            });

        });

        $(document).on("click", "#guardar", function() {

            var rels = [];

            $("#to").each(function() {
                var localRels = [];

                $(this).find('li').each(function() {
                    localRels.push($(this).attr('value'));
                });

                rels.push(localRels);
            });

            console.log("almacen = ", rels);
            $.post('/api/almacen/update/index.php', {
                        cve_usuario: $('#txtUsuario').val(),
                        action: 'guardarAlmacen',
                        almacenes: rels
                    },
                    function(response) {
                        console.log(response);
                    }, "json")
                .always(function() {
                    //$("#conModal").modal().delay(1000).fadeOut();
                    $("#conModal").modal();
                    setTimeout(function() {
                        $("#conModal").modal("hide");
                    }, 3000);
                    $("#selectAll").iCheck('uncheck')
                    $("#from .itemlist").remove();
                    $("#to .itemlist").remove();
                    $('.chosen-select').trigger("chosen:updated");
                });
        });

        function add(from, to) {
            console.log("Entro");
            var elements = document.querySelectorAll(`${from} input.drag:checked`),
                li, newli;
            for (e of elements) {
                e.checked = false;
                li = e.parentElement;
                newli = li.cloneNode(true);
                newli.setAttribute("aria-grabbed", "false");
                document.querySelector(`${from}`).removeChild(li);
                document.querySelector(`${to}`).appendChild(newli);
            }
        }

        function remove(to, from) {
            var elements = document.querySelectorAll(`${to} input.drag:checked`),
                li, newli;
            for (e of elements) {
                e.checked = false;
                li = e.parentElement;
                newli = li.cloneNode(true);
                newli.setAttribute("aria-grabbed", "false");
                document.querySelector(`${to}`).removeChild(li);
                document.querySelector(`${from}`).insertBefore(newli, document.querySelector(`${from}`).firstChild);
            }
        }

        function selectParent(e) {
            if (e.checked) {
                e.parentNode.setAttribute("aria-grabbed", "true");
            } else {
                e.parentNode.setAttribute("aria-grabbed", "false");
            }
        }

        function selectChild(e) {
            if (e.getAttribute("aria-grabbed") == "true") {
                e.firstChild.checked = true;
            } else {
                e.firstChild.checked = false;
            }
        }
    </script>

    <script>
        $(document).ready(function() {
            $(function() {
                $('.chosen-select').chosen();
                $('.chosen-select-deselect').chosen({
                    allow_single_deselect: true
                });
            });
            $('#selectAll').iCheck({
                checkboxClass: 'icheckbox_square-green',
                radioClass: 'iradio_square-green'
            });
            $("body").on("ifToggled", function(e) {

                if (e.target.checked && e.target.id === 'selectAll') {
                    $('#from li input[type="checkbox"].drag').each(function(i, e) {
                        e.checked = true;
                        e.parentElement.setAttribute('aria-grabbed', true);
                    });
                } else {
                    $('#from li input[type="checkbox"].drag').each(function(i, e) {
                        e.checked = false;
                        e.parentElement.setAttribute('aria-grabbed', false);
                    });
                }
            });
            $("#guardar").prop("disabled", true);
            $("#myElem").hide();
        });
    </script>