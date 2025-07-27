<?php 
    $listaAP = new \AlmacenP\AlmacenP();
$id_almacen = $_SESSION['id_almacen'];
$confSql = \db()->prepare("SELECT DISTINCT * FROM (SELECT IF(ID_Permiso = 2 AND Id_Tipo = 2, 1, IF(ID_Permiso = 2 AND Id_Tipo = 3, 0, -1)) AS con_recorrido FROM Rel_ModuloTipo WHERE Cve_Almac = '$id_almacen') AS cr WHERE cr.con_recorrido != -1");
$confSql->execute();
$con_recorrido = $confSql->fetch()['con_recorrido'];

?>
<link href="/css/plugins/sweetalert/sweetalert.css" rel="stylesheet">

<div class="wrapper wrapper-content" style="padding-bottom: 10px">
    <?php if(isset($message) && !empty($message)): ?>
        <div class="alert alert-success">
            <?php echo $message ?>
        </div>
    <?php endif; ?>
    <div class="row">
        <div class="col-lg-6 col-lg-offset-3">
            <div class="ibox">
                <div class="ibox-title" style="display: none;">
                    <h5>Usa Recorrido de Surtido 
                        <input type="checkbox" name="check_rs" id="check_rs" style="margin-left: 20px;top: 15px;position: absolute;cursor: pointer;" <?php if ($con_recorrido == 1) echo "checked"; ?>>
                    </h5>
                </div>
                <div class="ibox-title">
                    <h5>Importar Rutas de Surtido</h5>
                </div>
                <div class="ibox-content">
                    <form id="form-import" action="/importar/rutasSurtido" method="post"  enctype="multipart/form-data">

                        <div class="form-group">
                            <label>Almacén*</label>
                            <select class="form-control" name="almacen" id="almacen">
                                <?php /* ?><option value="">Seleccione</option><?php */ ?>
                                <?php foreach( $listaAP->getAll() AS $a ): ?>
                                    <option value="<?php echo $a->id; ?>"><?php echo "(".$a->clave.") - ".$a->nombre; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php 
                        /*
                        ?>
                        <div class="form-group">
                            <label>Zona de Almacenaje*</label>
                            <select class="form-control" name="zonaalmacenajei" id="zonaalmacenajei">
                                <option value="">Seleccione</option>
                            </select>
                        </div>
                        <?php 
                        */
                        ?>
                        <div class="form-group">
                            <label>Agregar Ubicaciones a la Siguiente Ruta de Surtido </label>
                            <select class="form-control" name="agregarubicaciones" id="agregarubicaciones">
                            <option value="">Importar Nueva Ruta de Surtido</option>
                            <?php 
                                $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
                                $sql = "SELECT * FROM th_ruta_surtido WHERE cve_almac = $id_almacen AND Activo = 1";
                                if (!($res = mysqli_query($conn, $sql)))echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
                                while($row = mysqli_fetch_array($res))
                                {
                                    extract($row);
                                //id, Cve_TipoImp, Des_TipoImp, Activo
                            ?>
                            <option value="<?php echo $idr; ?>"><?php echo "( ".$idr." ) - ".$nombre; ?></option>
                            <?php 
                                }
                            ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Descripción</label>
                            <input type="text" name="nombre_ruta" class="form-control" id="nombre_ruta" placeholder="Descripción">
                        </div>

                        <input type="hidden" name="usuarios" id="usuarios" value="">

                    <div class="row">
                        <div class="form-group">
                            <div class="col-sm-12" style="margin-bottom: 30px">
                                <input type="checkbox" id="selectAll" onclick="selectAllCheckbox('selectAll','fromU')">
                                <label for="selectAll">Seleccionar Todo</label>
                            </div>
                        </div>
                        <div class="form-group" id="dragUbicaciones">
                            <div class="col-md-6 relative">
                                <label for="email">Surtidores Disponibles</label>
                                <ol data-draggable="target" id="fromU" class="wi" style="width: 90% !important;"></ol>
                                <div style="position: absolute;right: 0;top: 35%; display: inline-grid;">
                                    <button type="button" class="btn btn-primary floating-button" onclick="add('#fromU', '#toU')" title="Agregar">>></button>
                                    <button type="button" class="btn btn-primary floating-button" onclick="remove('#toU', '#fromU')" title="Quitar" style="margin-top: 40px"><<</button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="email">Surtidores a asignar</label>
                                <ol data-draggable="target" id="toU" class="wi"></ol>
                            </div>
                        </div>
                    </div>



                        <div class="form-group">
                            <label>Seleccionar archivo para importar</label>
                            <input type="file" class="form-control" id="layout" name="file" required>
                        </div>
                        <input type="hidden" name="utileria" value="true">
                        <div class="row">
                      <div class="col-md-6" style="text-align: left">
                        <button id="btn-layout" type="button" class="btn btn-primary">Descargar Layout</button>


                        <div class="row" id="importando_rutas_surtido" style="text-align: right;padding: 15px; display: none;font-size: 16px;top: 15px;position: relative;">
                            <div style="width: 50px;height: 50px;background-image: url(https://dev.assistpro-adl.com/img/load.gif);background-size: 100%;background-position: center;background-repeat: no-repeat;display: inline-flex;"></div>
                        </div>

                      </div>
                        <div class="col-md-6" style="text-align: right;">
                            <button type="button" id="btn-import" class="btn btn-primary">Importar</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                        </div>
                        </div>
                    </form>
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

<!-- Flot -->
<script src="/js/plugins/flot/jquery.flot.js"></script>
<script src="/js/plugins/flot/jquery.flot.tooltip.min.js"></script>
<script src="/js/plugins/flot/jquery.flot.spline.js"></script>
<script src="/js/plugins/flot/jquery.flot.resize.js"></script>
<script src="/js/plugins/flot/jquery.flot.pie.js"></script>

<!-- Peity -->
<script src="/js/plugins/peity/jquery.peity.min.js"></script>
<script src="/js/demo/peity-demo.js"></script>

<!-- Custom and plugin javascript -->
<script src="/js/inspinia.js"></script>
<script src="/js/plugins/pace/pace.min.js"></script>

<!-- jQuery UI -->
<script src="/js/plugins/jquery-ui/jquery-ui.min.js"></script>

<!-- GITTER -->
<script src="/js/plugins/gritter/jquery.gritter.min.js"></script>

<!-- Sparkline -->
<script src="/js/plugins/sparkline/jquery.sparkline.min.js"></script>

<!-- Sparkline demo data  -->
<script src="/js/demo/sparkline-demo.js"></script>

<!-- ChartJS-->
<script src="/js/plugins/chartJs/Chart.min.js"></script>

<!-- Toastr -->
<script src="/js/plugins/toastr/toastr.min.js"></script>

<!-- Morris -->
<script src="/js/plugins/morris/raphael-2.1.0.min.js"></script>
<script src="/js/plugins/morris/morris.js"></script>

<!-- d3 and c3 charts -->
<script src="/js/plugins/d3/d3.min.js"></script>
<script src="/js/plugins/c3/c3.min.js"></script>

<!-- jqGrid -->
<script src="/js/plugins/jqGrid/i18n/grid.locale-es.js"></script>
<script src="/js/plugins/jqGrid/jquery.jqGrid.min.js"></script>

<script src="/js/plugins/sweetalert/sweetalert.min.js"></script>

<script>


    function almacenPrede()
    { 
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                idUser: '<?php echo $_SESSION["id_user"]?>',
                action: 'search_almacen_pre'
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/almacenPredeterminado/index.php',
            success: function(data) {
                if (data.success == true) 
                {
                    document.getElementById('almacen').value = data.codigo.id;
                    //fillSelectZona();
                }
            },
            error: function(res){
                window.console.log(res);
            }
        });
    }
    almacenPrede();

    $('#almacen').change(function(e){
        //fillSelectZona();
    });

    $('#agregarubicaciones').change(function(e){
        if($(this).val()!='')
        {
            $("#nombre_ruta").val("");
            $("#nombre_ruta").prop("disabled", true);
        }
        else 
        {
            $("#nombre_ruta").val("");
            $("#nombre_ruta").prop("disabled", false);
        }

    });

    function CambiarTipoRecorridoSurtido(valor)
    {
        var msj = "No está";
        if(valor) msj = "está";

        console.log("msj = ", msj);
        console.log("valor = ", valor);
        console.log("id_almacen", $("#almacen").val());

        $.ajax({
          url: '/api/rutassurtido/update/index.php',
          dataType: 'json',
          data: {
            action: 'CambiarTipoRecorridoSurtido',
            valor: valor,
            id_almacen: $("#almacen").val()
          },
          type: 'POST'
        }).done(function(data) 
        {
            swal({
                    title: "Cambio Realizado",
                    text: "Este Almacén "+msj+" usando Ruta de Surtido",
                    type: "success",

                    showCancelButton: false,
                    cancelButtonText: "No",
                    cancelButtonColor: "#14960a",

                    confirmButtonColor: "#55b9dd",
                    confirmButtonText: "OK",
                    closeOnConfirm: true
                },
                function(e) {

                    if (e == true) {
                        //Reabasto
                        window.location.reload();
                    }

                });
        }
        ).fail(function(data){console.log("ERROR RUTA SURTIDO", data);});

    }

    $('#check_rs').click(function()
    {
        var valor = 0;
        if($(this).is(':checked'))
            valor = 1;

        CambiarTipoRecorridoSurtido(valor);

    });


    function surtidores()
    {
        console.log("almacen surtidores = ", '<?php echo $_SESSION['cve_almacen']; ?>');
        $.ajax({
            type: "POST",
            dataType: "json",
            url: '/api/rutassurtido/lista/index.php',
            data: {
                action : "traerSurtidores",
                almacen : '<?php echo $_SESSION['cve_almacen']; ?>'
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }},
            success: function(data) 
            {
                if (data["success"] == true) 
                {
                    var options = $("#surtidor_ruta");
                    options.empty();
                    options.append(new Option("Seleccionar", "0"));
                    for (var i=0; i<data["user"].length; i++)
                    {
                        options.append(new Option(data["user"][i].nombre, data["user"][i].id));
                    }
                    $('.chosen-select').trigger("chosen:updated");

                    for (var i=0; i<data["user"].length; i++)
                    {
                        var ul = document.getElementById("fromU");
                        var li = document.createElement("li");
                        var checkbox = document.createElement("input");
                        checkbox.style.marginRight = "10px";
                        checkbox.setAttribute("type", "checkbox");
                        checkbox.setAttribute("value", data["user"][i].id);
                        checkbox.setAttribute("class", "drag");
                        checkbox.setAttribute("onclick", "selectParent(this)");
                        li.appendChild(checkbox);
                        li.appendChild(document.createTextNode(data["user"][i].nombre));
                        li.setAttribute("dayta-draggable", "item");
                        li.setAttribute("draggable", "false");
                        li.setAttribute("aria-draggable", "false");
                        li.setAttribute("aria-grabbed","false");
                        li.setAttribute("tabindex","0");
                        li.setAttribute("class","itemlist");
                        li.setAttribute("onclick","selectChild(this)");
                        li.setAttribute("value",data["user"][i].id);
                        ul.appendChild(li);
                    }
                }
            }
        });
    }
surtidores();


    function selectAllCheckbox(check,checkGroup)
    {
        $('#'+checkGroup+' li input[type="checkbox"].drag').prop('checked', $('#'+check).is(":checked"));
    }

    function add(from, to)
    {
        var elements = document.querySelectorAll(`${from} input.drag:checked`), li, newli;
        var i = 0;
        for(e of elements)
        {
            e.checked = false;
            li = e.parentElement;
            console.log(li.value);
            newli = li.cloneNode(true);
            newli.setAttribute("aria-grabbed", "false");
            document.querySelector(`${from}`).removeChild(li);
            document.querySelector(`${to}`).appendChild(newli);
            i++;
        }
    }
  
    function remove(to, from)
    {
        var elements = document.querySelectorAll(`${to} input.drag:checked`), li, newli;
        for(e of elements)
        {
            e.checked = false;
            li = e.parentElement;
            newli = li.cloneNode(true);
            newli.setAttribute("aria-grabbed", "false");
            document.querySelector(`${to}`).removeChild(li);
            document.querySelector(`${from}`).appendChild(newli);
        }
    }
  
    function selectParent(e)
    {
        if(e.checked)
        {
            e.parentNode.setAttribute("aria-grabbed", "true");
        }
        else
        {
            e.parentNode.setAttribute("aria-grabbed", "false");
        }
    }
  
    function selectChild(e)
    {
        if(e.getAttribute("aria-grabbed") == "true"){
            e.firstChild.checked = true;
        }else{
            e.firstChild.checked = false;
        }
    }

    function fillSelectZona()
    {
        var almacen= $('#almacen').val();
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                cve_almacenp : almacen,
                action : "traerZonaporAlmacen"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }},
            url: '/api/almacen/update/index.php',
            success: function(data) 
            {
                var options = $("#zonaalmacenajei");
                options.empty();
                options.append(new Option("Seleccione", ""));
                for (var i=0; i<data.zona_almacen.length; i++)
                {
                    options.append(new Option(data.zona_almacen[i].descripcion_almacen, data.zona_almacen[i].clave_almacen));
                }
            }
        });
    }   

$('#btn-layout').on('click', function(e) {
  //e.preventDefault();  //stop the browser from following
  //window.location.href = 'http://www.tinegocios.com/proyectos/wms/Layout_Pedidos.xlsx';
  window.location.href = '/Layout/Layout_RutasSurtido.xlsx';

}); 

    $('#btn-import').on('click', function() {

        var usuarios = [];

        $("#toU").each(function() {
            var localRels = [];
            $(this).find('li').each(function(){
                localRels.push( $(this).attr('value'));
            });
            if(localRels.length > 0 )
            {
                usuarios = localRels;
            }
        });

        $("#usuarios").val(usuarios);
        console.log("usuarios = ",$("#usuarios").val());

        if(usuarios.length == 0 && $("#agregarubicaciones").val() == "")
        {
            swal("Error", "No se ha asignado ningún usuario", "error");
            return;
        }
        if($("#zonaalmacenajei").val() == "")
        {
            swal("Error", "Debe seleccionar una zona del alamacenaje", "error");
            return;
        }
        if($("#nombre_ruta").val() == "" && $("#agregarubicaciones").val() == "")
        {
            swal("Error", "Debe registrar una descripción", "error");
            return;
        }
        if($("#layout").val() == "" || $("#layout").val() == null)
        {
            swal("Error", "Debe subir un archivo para importar", "error");
            return;
        }



        $("#importando_rutas_surtido").show();
        $('#btn-import').hide();
        //$('#btn-import').prop('disable', true);
        var bar = $('.progress-bar');
        var percent = $('.percent');
        //var status = $('#status');

        var formData = new FormData();
        formData.append("clave", "valor");

        $.ajax({
            // Your server script to process the upload
            url: '/rutassurtido/importar',
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
                console.log(data);
                setTimeout(
                    function(){if (data.status == 200) {
                        swal("Éxito", data.statusText, "success");
                        window.location.reload();
                       // $('#importar').modal('hide');
                    }
                    else {
                        swal("Error", data.statusText, "error");
                    }
                },1000)
            }
        });
    });
</script>
