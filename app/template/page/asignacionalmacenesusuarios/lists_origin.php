<?php
$model_usuarios = new \Usuarios\Usuarios();
$usuarios = $model_usuarios->getAll();

?>

<link href="/css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">
<link href="/css/plugins/ladda/select2.css" rel="stylesheet"/>

<!-- Mainly scripts -->
<!-- Barra de nuevo y busqueda -->
<div class="wrapper wrapper-content  animated fadeInRight">

    <h3>Asignación Almacén a Usuario</h3>

    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-title">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="email">Usuarios</label>
                                <select name="country" id="txtUsuario" style="width:100%;">
                                    <!--<option value="">Usuario</option>-->
                                    <?php foreach( $usuarios AS $usuario ): ?>
                                        <?php if($usuario->Activo == 1):?>
                                        <option value="<?php echo $usuario->cve_usuario; ?>"><?php echo $usuario->cve_usuario; ?></option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4" style="margin-top: 5px;">
                            </br>
                            <button id ="editar" class="btn btn-primary" type="button"><i class="fa fa-search"></i>&nbsp;&nbsp;Buscar</button>
                        </div>
                    </div>
                </div>
                <div class="ibox-content dragdrop">
                    <div class="col-md-6">
                        <label for="email">Almacenes Disponibles:</label>
                        <div class="form-group" id="clientesEditar">
                            <ol data-draggable="target" id="from" class="wi">
                            </ol>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group" id="clientesEditar1">
                            <label for="email">Almacenes Asignados:</label>
                            <ol data-draggable="target" id="to" class="wi">
                            </ol>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <button id ="guardar" class="btn btn-primary pull-right" type="button"><i class="fa fa-plus"></i>&nbsp;&nbsp;Guardar</button>
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
<script src="/js/select2.js"></script>

<!-- Drag & Drop Panel -->
<script src="/js/dragdrop.js"></script>
<script type="text/javascript">
    $(document).on("click","#editar", function(){
        $(".itemlist").remove();
        $("#guardar").prop( "disabled", false );
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                cve_usuario: $('#txtUsuario').val(),
                action : "loadAlmacenes"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/almacen/update/index.php',
            success: function(data) {
                var arr = $.map(data.Almacenes, function(el) { return el; });
                var arr_current = $.map(data.Current, function(el) { return el; });


                    for (var i=0; i<arr.length; i++)
                    {
                        var ul = document.getElementById("from");
                        var li = document.createElement("li");
                        li.appendChild(document.createTextNode(arr[i].desc));
                        li.setAttribute("data-draggable", "item");
                        li.setAttribute("draggable", "true");
                        li.setAttribute("aria-grabbed","false");
                        li.setAttribute("tabindex","0");
                        li.setAttribute("class","itemlist");
                        li.setAttribute("value",arr[i].id);
                        ul.appendChild(li);
                    }

                    for (var j=0; j<arr_current.length; j++)
                    {
                        var ul = document.getElementById("to");
                        var li = document.createElement("li");
                        li.appendChild(document.createTextNode(arr_current[j].desc));
                        li.setAttribute("data-draggable", "item");
                        li.setAttribute("draggable", "true");
                        li.setAttribute("aria-grabbed","false");
                        li.setAttribute("tabindex","0");
                        li.setAttribute("class","itemlist");
                        li.setAttribute("value",arr_current[j].id);
                        ul.appendChild(li);
                    }




            }
        });

    });

    $(document).on("click","#guardar", function(){

        var rels = [];

        $("#to").each(function() {
            var localRels = [];

            $(this).find('li').each(function(){
                localRels.push( $(this).attr('value') );
            });

            rels.push(localRels);
        });

        $.post('/api/almacen/update/index.php',
            {
                cve_usuario:$('#txtUsuario').val(),
                action : 'guardarAlmacen',
                almacenes: rels
            },
            function(response){
                console.log(response);
            }, "json")
            .always(function() {
                //$("#conModal").modal().delay(1000).fadeOut();
                $("#conModal").modal();
                setTimeout(function(){
                    $("#conModal").modal("hide");
                }, 3000);
            });

        /*$.post( "/api/almacen/update/index.php",
         {
             cve_usuario:$('#txtUsuario').val(),
             action : 'guardarAlmacen',
             almacenes: rels

         } ,function( data ) {
         });*/
    });

</script>

<script>
    $(document).ready(function(){
        $("#txtUsuario").select2();
        $("#guardar").prop( "disabled", true );
        $("#myElem").hide();
    });
</script>





