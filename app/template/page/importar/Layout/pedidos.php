<?php 
include_once  $_SERVER['DOCUMENT_ROOT']."/app/host.php";
$almacenes = new \AlmacenP\AlmacenP();
$listaPriori = new \TipoPrioridad\TipoPrioridad();
$listaAP = new \AlmacenP\AlmacenP();

$id_almacen = $_SESSION['id_almacen'];

$rutasvpSql = \db()->prepare("SELECT *  FROM t_ruta WHERE venta_preventa = 1 AND Activo = 1 AND cve_almacenp = {$id_almacen}");
$rutasvpSql->execute();
$rutasvp = $rutasvpSql->fetchAll(PDO::FETCH_ASSOC);

$confSql = \db()->prepare("SELECT IFNULL(Valor, '') AS instancia FROM t_configuraciongeneral WHERE cve_conf = 'instancia' LIMIT 1");
$confSql->execute();
$instancia = $confSql->fetch()['instancia'];

?>


<input type="hidden" id="instancia" value="<?php echo $instancia; ?>">

<div class="wrapper wrapper-content" style="padding-bottom: 10px">
  <?php if(isset($message) && !empty($message)): ?>
    <div class="alert alert-success">
        <?php echo $message ?>
    </div>
  <?php endif; ?>
  <?php if(isset($error) && !empty($error)): ?>
    <div class="alert alert-danger">
        <?php echo $error ?>
    </div>
  <?php endif; ?>
  <div class="row">
    <div class="col-lg-6 col-lg-offset-3">
      <div class="ibox">
        <div class="ibox-title">
            <h5>Importar Pedidos</h5>
        </div>
        <div class="ibox-content">
          <form id="form-import" action="/importar/pedidos" method="post"  enctype="multipart/form-data">
            <div class="form-group">
                <label>Seleccione tipo de pedido</label>
                <select class="form-control" name="tipo" id="tipo" required>
                  <option value="th" selected>Pedidos</option>
                  <?php 
                  //if((strpos($_SERVER['HTTP_HOST'], 'dev')) || (strpos($_SERVER['HTTP_HOST'], 'grupoasl')))
                  //{
                  ?>
                      <option value="vp">Pedidos Voice Picking</option>
                      <option value="ph">Pedidos PH</option>
                      <option value="lv">Pedidos Liverpool</option>
                      <option value="hs">Pedidos Home Store</option>
                  <?php 
                  //}
                  ?>
                </select>
             </div>

            <div class="form-group">
                <label>Almacén</label>
                <select class="form-control" id="almacenes" name="almacenes">
                    <option value="">Seleccione un Almacen</option>
                    <?php foreach( $almacenes->getAll() AS $a ): ?>
                        <option value="<?php echo $a->id; ?>" <?php if($a->id==$_GET["almacen"]) echo "selected";?>><?php echo "($a->clave) $a->nombre"; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>


                            <div class="row">
                                <div class="col-md-12">
                            <div class="form-group" style="text-align: center;">
                                <div class="checkbox">
                                    <label for="tipo_traslado" style="font-weight: 600;font-size: 14px; -moz-user-select: none; -webkit-user-select: none;-ms-user-select: none; user-select: none;">
                                    <input type="checkbox" name="tipo_traslado" id="tipo_traslado" value="0">Pedido Tipo Traslado</label>
                                    <input type="hidden" name="tipo_traslado_input" id="tipo_traslado_input" value="0">
                                </div>
                            </div>
                                </div>

                                <div class="col-md-12">

                                <div class="form-group col-md-12 tipo_traslado_int_ext" style="display: none;">
                                    <div class="row">
                                        <div class="col-md-4">
                                    <label>Tipo de Traslado:</label> 
                                        </div>
                                    
                                        <div class="col-md-4">
                                    <label>
                                    <input type="radio" name="traslado_interno_externo" checked id="tipo_interno" value="RI"> Interno</label>
                                        </div>

                                        <div class="col-md-4">
                                    <label>
                                    <input type="radio" name="traslado_interno_externo" id="tipo_externo" value="R"> Externo</label>
                                        </div>

                                        <input type="hidden" name="traslado_interno_externo_input" id="traslado_interno_externo_input" value="RI">

                                    </div>
                                </div>

                                </div>

                                <div class="form-group" style="display:none;margin: 15px;" id="almacen_destino">
                                    <label>Solicitar Productos al Almacén: </label>
                                    <select class="form-control" name="almacen_dest" id="almacen_dest">
                                        <option value="">Seleccione</option>
                                        <?php foreach( $listaAP->getAll('traslado') AS $a ): ?>
                                            <option value="<?php echo $a->id; ?>"><?php echo "(".$a->clave.") - ".$a->nombre; ?></option>
                                        <?php endforeach; ?>
                                    </select>

                                    <hr>

                                </div>
                            </div>




            <div class="form-group">
                <label>Fecha Pedido*</label>
                <div class="input-group date" id="data_6">
                    <input id="fecha_pedido" name="fecha_pedido" type="text" class="form-control" value="" required>
                    <span class="input-group-addon">
                    <span class="glyphicon glyphicon-calendar"></span>
                </div>
            </div>

            <div class="form-group">
                <label>Fecha Entrega*</label>
                <div class="input-group date" id="data_7">
                    <input id="fecha_entrega" name="fecha_entrega" type="text" class="form-control" value="" required>
                    <span class="input-group-addon">
                    <span class="glyphicon glyphicon-calendar"></span>
                </div>
            </div>

            <div class="form-group" style="display: none;">
                <label>Folio del Pedido*</label>
                <input id="folio_pedido" name="folio_pedido" type="text" class="form-control" value="" maxlength="20" required placeholder="Folio Pedido">
            </div>

            <div class="form-group" style="padding-right: 0;">
                <label>Prioridad</label>
                <select class="form-control" name="prioridad" id="prioridad">
                      <?php foreach( $listaPriori->getAll() AS $a ): ?>
                        <option value="<?php echo $a->ID_Tipoprioridad; ?>" <?php if($a->ID_Tipoprioridad == 2) echo "selected"; ?>><?php echo $a->ID_Tipoprioridad."-".$a->Descripcion; ?></option>
                    <?php endforeach; ?>

                </select>
            </div>

            <div class="form-group">
                <label>Número Orden del Cliente*</label>
                <input id="num_orden" name="num_orden" type="text" class="form-control" value="" maxlength="20" placeholder="Orden del Cliente">
            </div>


            <div class="form-group col-md-6" style="padding-left: 0;">
                <label>Rutas Venta | Preventa</label>
                    <select id="rutas_ventas" name="rutas_ventas" class="form-control">
                        <option value="">Ruta</option>
                        <?php foreach( $rutasvp AS $p ): ?>
                            <option value="<?php echo $p["cve_ruta"];?>"><?php echo $p["cve_ruta"]." ".$p["descripcion"]; ?></option>
                        <?php endforeach;  ?>
                    </select>
            </div>

            <div class="form-group col-md-6" style="padding-right: 0;">
                <label>Vendedor</label>
                <?php /* ?><input id="vendedor" name="vendedor" type="text" class="form-control"><?php   chosen-select */ ?>
                <select class="form-control" name="vendedor" id="vendedor">
                    <option value="">Seleccione</option>
                    <?php /*foreach( $listaUser->getAll() AS $a ): ?>
                      <?php 
                      if($a->es_cliente == 3)
                      {
                      ?>
                        <option value="<?php echo $a->cve_usuario; ?>"><?php echo "( ".$a->cve_usuario." ) - ".$a->nombre_completo; ?></option>
                      <?php 
                      }
                      ?>
                    <?php endforeach;*/ ?>
                </select>

            </div>


<?php 
//*********************************************************************************************************************************
?>
            <div class="form-group col-md-6" style="padding-left: 0;">
              <label>Cliente*</label>
              <input class="form-control" name="cliente_buscar" id="cliente_buscar" placeholder="Código del Cliente">
              <input class="form-control" name="cliente" id="cliente" placeholder="Código del Cliente" style="display: none;">
            </div>

            <div class="row">
                <div class="form-group col-md-6">
                <label>Clave y Nombre Cliente</label>
                     <?php /* ?><input id="desc_cliente" name="desc_cliente" type="text" class="form-control" value="" disabled><?php */ ?>
                     <select id="desc_cliente" name="desc_cliente" class="form-control">
                     </select>
                </div>
            </div>

            <div class="form-group">
                <div class="row">
                    <div class="col-md-12">
                        <label>Dirección de Envío *</label> 
                        <div class="input-group date">                                                
                            <select class="form-control chosen-select" name="destinatario" id="destinatario" disabled></select>
                        </div>
                    </div>

                </div>                              
            </div>
             <div class="form-group">
                <div class="row">
                    <div class="col-md-12">
                        <textarea id="txt-direc" name="txt-direc" class="form-control" disabled></textarea>
                    </div>
        
                </div>                              
            </div>

              <div class="col-md-12">
                  <div class="form-group">
                      <label>Observacion</label>
                      <textarea id="observacion" name="observacion" class="form-control" style="resize: none"></textarea>
                  </div>
              </div>

<?php 
//*********************************************************************************************************************************
?>
            <div class="checkbox">
                <label for="pedidoLP" style="font-weight: 600;font-size: 14px; -moz-user-select: none; -webkit-user-select: none;-ms-user-select: none; user-select: none;">
                <input type="checkbox" name="pedidoLP" id="pedidoLP" value="0">Pedido Tipo LP</label>
            </div>


             <label>Seleccionar archivo Excel para importar</label>
             <div class="form-group">
                <input type="file" name="file" id="file" class="form-control" accept=".xlsx" required>
             </div>
           </form>
           <div class="col-md-12">
            <div class="progress" style="display:none">
              <div class="progress-bar progress-bar-info progress-bar-striped" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:0%">
                <div class="percent">0%</div >
              </div>
            </div>
          </div>
          <div class="row">
          <div class="col-md-4" style="text-align: left">
            <button id="btn-layout" type="button" class="btn btn-primary">Descargar Layout</button>
          </div>
          <div class="col-md-4" style="text-align: left">
            <button id="btn-layoutLP" type="button" class="btn btn-primary">Descargar Layout LP</button>
          </div>
          <div class="col-md-4" style="text-align: right">
            <button onclick="prueba_importacion()" id="btn-import" type="submit" class="btn btn-primary">Importar</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
          </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<div>
</div>

<!-- Axios -->
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.16.2/axios.min.js"></script>

<!-- Mainly scripts -->
<script src="/js/jquery-2.1.1.js"></script>
<script src="/js/bootstrap.min.js"></script>
<script src="/js/plugins/metisMenu/jquery.metisMenu.js"></script>
<script src="/js/plugins/slimscroll/jquery.slimscroll.min.js"></script>
<script src="/js/moment.js"></script>
<script src="/js/moment-with-locales.js"></script>

<link href="/css/plugins/sweetalert/sweetalert.css" rel="stylesheet">

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
<script src="/js/plugins/sweetalert/sweetalert.min.js"></script>

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


<script src="/js/plugins/datapicker/bootstrap-datepicker.js"></script>
<script src="/js/plugins/clockpicker/clockpicker.js"></script>

<!--comienza la prueba de importacion EDG-->

<script>
$('#btn-layout').on('click', function(e) {
  //e.preventDefault();  //stop the browser from following
  //window.location.href = 'http://www.tinegocios.com/proyectos/wms/Layout_Pedidos.xlsx';
  window.location.href = '/Layout/Layout_Pedidos.xlsx';

}); 

$('#btn-layoutLP').on('click', function(e) {
  //e.preventDefault();  //stop the browser from following
  //window.location.href = 'http://www.tinegocios.com/proyectos/wms/Layout_Pedidos.xlsx';
  window.location.href = '/Layout/Layout_PedidosLP.xlsx';

}); 

    function almacenPrede(){ 
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
                if (data.success == true) {
                    document.getElementById('almacenes').value = data.codigo.id;
                    setTimeout(function() {
                        $('#almacenes').trigger('change');
                    }, 1000);
                }
            },
            error: function(res){
                window.console.log(res);
            }
        });
    }
    almacenPrede();

//************************************************************************************************
//************************************************************************************************
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
                            BuscarDestinatario(data.firsTValue);
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

        //$("#cliente").on('change', function(e)
        $("#cliente_buscar").on('keyup', function(e)
        {
            var cliente = e.target.value;
            console.log("cliente.length = ", cliente.length);
            if(cliente.length > 2)
                BuscarCliente(cliente);
            else
                BuscarCliente("Borrar_La_Lista_de_Clientes");
        });

        function BuscarDestinatario(cliente)
        {
            //document.getElementById("agregar_destinatario").removeAttribute("disabled");
            $.ajax({
                url: '/api/nuevospedidos/lista/index.php',
                data: {
                    action: 'getDestinatario',
                    cliente: cliente
                },
                dataType: 'json',
                method: 'GET',
                beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                },
                success: function(data) 
                {
                    console.log("SUCCESS: ", data);
                    var destinatario = document.getElementById("destinatario");
                    if(data.combo !== '' && data.find == true){
                        destinatario.innerHTML = data.combo;
                        var text = destinatario.options[destinatario.selectedIndex].text;
                        document.getElementById("txt-direc").value = text;
                        destinatario.removeAttribute('disabled');
                    }
                    else
                    {
                        //$("#destinatario, #agregar_destinatario").prop("disabled", true);
                    }

                    $(".chosen-select").trigger("chosen:updated");
                }, error: function(data){
                    console.log("ERROR: ", data);
                }

            });
        }


    $("#tipo_traslado").click(function()
    {
        if($("#tipo_traslado").is(':checked'))
        {
            $("#tipo_traslado_input").val("1");
            //$(".tipo_traslado_int_ext").show();
            console.log("folio_tr = ",$("#folio_tr").val());
            $("#folio").val($("#folio_tr").val());
            $("#almacen_destino").show();
            //fillSelectArti("S");
            //fillSelectLP();
            //$("#Articul span").show();
            //BuscarCliente("");
            //BuscarCliente("Borrar_La_Lista_de_Clientes");

            $("#cliente_buscar, #cliente, #desc_cliente, #destinatario, #txt-direc").val("");
            $("#cliente_buscar, #cliente, #desc_cliente, #destinatario, #txt-direc").prop("disabled", true);
            $("#almacen_dest").val("");
            $("#almacen_dest option").show();
            $("#almacen_dest option[value=" + $(this).val() + "]").hide();
            //$("#almacen_dest").val($("#almacen_dest option:first").val());
            //$("#almacen_dest").val($("#almacen_dest option:last").val());
            //$("#almacen_dest").val("");
            $("#almacen_dest").trigger("change");
            $("#almacen_dest").trigger("chosen:updated");

        }
        else
        {
            $("#tipo_traslado_input").val("0");
            $(".tipo_traslado_int_ext").hide();
            $("#folio").val($("#folio_fol").val());
            $("#almacen_destino").hide();
            $("#tipo_externo").prop("checked", true);
            $("#cliente_buscar, #cliente, #desc_cliente, #destinatario, #txt-direc").prop("disabled", false);
            //fillSelectArti("N");
            //fillSelectLP();
            //$("#Articul span").hide();
        }
            Activar_BloquearSelects("activar");
            $("#cliente_buscar").val("");
            $("#desc_cliente").empty();
    });

        $("#desc_cliente").on("change", function(e){
            var cliente = $(this).val();
            BuscarDestinatario(cliente);
            //console.log("Cliente = ", cliente);
        });

        $("#destinatario").on("change", function(e){
            var destinatario = document.getElementById("destinatario");
            var text = destinatario.options[destinatario.selectedIndex].text;
            document.getElementById("txt-direc").value = text;
        });
//************************************************************************************************
//************************************************************************************************

$('#btn-import_prueba').on('click', function (){
    prueba_importacion();
});
  
      $('#data_6').datetimepicker({
        locale: 'es',
        format: 'DD-MM-YYYY',
        useCurrent: true
    });

    $('#data_6').data("DateTimePicker").minDate(new Date());

      $('#data_7').datetimepicker({
        locale: 'es',
        format: 'DD-MM-YYYY',
        useCurrent: true
    });

    $('#data_7').data("DateTimePicker").minDate(new Date());

    var permitidos = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M','N','O','P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '/'];
    $("#folio_pedido").keyup(function(e){
        console.log("KEY = ", e.key.toUpperCase());

            //console.log("NO");
            var folio_pedido = $(this).val();
            var arr_tecla = folio_pedido.split("");

            for(var i = 0; i < arr_tecla.length; i++)
            {
                if($.inArray(arr_tecla[i].toUpperCase(), permitidos) == -1)
                   folio_pedido = folio_pedido.replace(arr_tecla[i].toUpperCase(), '');
            }

            $(this).val(folio_pedido);

            //console.log("SI");

    });
  //


        $("#rutas_ventas").change(function(){

            console.log("clave_ruta:", $(this).val());
            if(!$(this).val()) 
            {
                //$("#folio_pedido").prop("disabled", false);
                $("#folio_pedido").removeAttr("readonly");
                $("#folio_pedido").val("");
                return;
            }
            else
            {
                //$("#folio_pedido").prop("disabled", true);
                $("#folio_pedido").attr("readonly", "readonly");
                folio_consecutivo();
            }

              $.ajax({
                url: "/api/nuevospedidos/update/index.php",
                type: "POST",
                data: {
                  action: 'getAgentesRutas',
                  clave_ruta: $(this).val()
                },
                datatype: 'json',
                beforeSend: function(x){if(x && x.overrideMimeType) x.overrideMimeType("application/json;charset=UTF-8");
                },
                success: function(res){
                  console.log(res);
                  $("#vendedor").empty();
                  $("#vendedor").append(res.options);
                  $(".chosen-select").trigger("chosen:updated");

                },
                error : function(res){
                  window.console.log(res);
                },
                cache: false
              });


        });


  function prueba_importacion() {
    console.log("Funcion ok");
    

    if($("#almacenes").val() == "")
    {
      swal("Error", "Debe Seleccionar un Almacen", "error");
        return false;
    }

    if($("#tipo_traslado").is(':checked') && $("#almacen_dest").val() == '')
    {
        swal("Error", "Debe Seleccionar un Almacén Destino para el traslado", "error");
        $("#tipo_traslado_input").val("0");
        return;
    }
    else
    {
        $("#traslado_interno_externo_input").val("1");
        $("#traslado_interno_externo_input").val($("input[name=traslado_interno_externo]:checked").val());
    }


    if($("#fecha_pedido").val() == "")
    {
        swal("Error", "Debe Seleccionar una Fecha del Pedido", "error");
        return false;
    }

    if($("#fecha_entrega").val() == "")
    {
        swal("Error", "Debe Seleccionar una Fecha de Entrega", "error");
        return false;
    }
/*
    if($("#folio_pedido").val() == "")
    {
        swal("Error", "Debe Asignar un Folio al Pedido", "error");
        return false;
    }
*/
    if(($("#desc_cliente").val() == "" || $("#desc_cliente").val() == null)  && $("#rutas_ventas").val() == '' && !$("#tipo_traslado").is(':checked'))
    {
        swal("Error", "Debe Seleccionar un Cliente", "error");
        return false;
    }

    if((($("#destinatario").val() == "" || $("#destinatario").val() == null)  && $("#rutas_ventas").val() == '' )  && !$("#tipo_traslado").is(':checked'))
    {
        swal("Error", "Debe Seleccionar un Destinatario", "error");
        return false;
    }

    if($("#file").val() == "" || $("#file").val() == null)
    {
        swal("Error", "No ha seleccionado un archivo de importación", "error");
        return false;
    }


    if( $("#vendedor").val() == '' && $("#rutas_ventas").val() != '' )
    {
        swal("Error", "Por favor seleccione un Vendedor", "error");
        return;
    }


    console.log("fecha_pedido = ", $("#fecha_pedido").val(), "fecha_entrega = ", $("#fecha_entrega").val(), "folio_pedido = ", $("#folio_pedido").val(), "desc_cliente = ", $("#desc_cliente").val(), "destinatario = ", $("#destinatario").val());
    //return;

    $('#btn-import').prop('disable', true);
    var bar = $('.progress-bar');
    var percent = $('.percent');
    var formData = new FormData();
    formData.append("clave", "valor");

    var tipo = $("#tipo").val();
    var url_type = "/pedidos/importar";

    if(tipo == "th")
      url_type = "/pedidos/importarth";

    if($("#pedidoLP").is(":checked"))
        url_type = "/pedidos/importarLP";


    if($("#instancia").val() == 'welldex')
    {
        if(tipo == "th")
          url_type = "/pedidos/importarw";

        if($("#pedidoLP").is(":checked"))
            url_type = "/pedidos/importarLPw";
    }

      $.ajax({
        url: "/api/administradorpedidos/lista/index.php",
        type: "POST",
        data: {
          folio: $("#folio_pedido").val(),
          action: 'verificar_folio'
        },
        beforeSend: function(x){if(x && x.overrideMimeType) x.overrideMimeType("application/json;charset=UTF-8");
        },
        success: function(res){
          console.log("Existe = ", res.existe);

          if(res.existe == 0)
          {
                $.ajax({
                    // Your server script to process the upload
                    url: url_type,
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
                      console.log("success = ", data);
                      console.log("Si se conecta");
                        setTimeout(
                            function(){if (data.status == 200) {
                              //console.log("data.pedidos = ", data.pedidos);
                              if(data.pedidos != 0)
                                  swal("Exito", data.statusText, "success");
                              else
                                  swal("Error", data.statusText, "error");
                              $('#importar').modal('hide');
                              //ReloadGrid();
                            }
                            else {
                              swal("Error", data.statusText, "error");
                            }
                        },1000)
                      }, error: function(data)
                      {
                            console.log("error = ", data);
                      }
                  });
          }
          else
            swal("Error", "El Folio Asignado ya Existe", "error");
        },
        error : function(res){
          window.console.log(res);
        },
        cache: false
      });

      /*
    $.ajax({
        // Your server script to process the upload
        url: url_type,
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
          console.log("success = ", data);
          console.log("Si se conecta");
            setTimeout(
                function(){if (data.status == 200) {
                  //console.log("data.pedidos = ", data.pedidos);
                  if(data.pedidos != 0)
                      swal("Exito", data.statusText, "success");
                  else
                      swal("Error", data.statusText, "error");
                  $('#importar').modal('hide');
                  //ReloadGrid();
                }
                else {
                  swal("Error", data.statusText, "error");
                }
            },1000)
          }, error: function(data)
          {
                console.log("error = ", data);
          }
      });
    */
    }


    function folio_consecutivo() {
      $.ajax({
        type: "POST",
        dataType: "json",
        data: {
          action : "consecutivo_folio"
        },
        beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }},
        url: '/api/nuevospedidos/update/index.php',
        success: function(data) 
        {
          if (data.success == true) 
          {
/*
            var dt = new Date();
            var year = dt.getUTCFullYear(); 
            var month = ("0" + (dt.getMonth() + 1)).slice(-2);
*/
            
            //$("#folio").val("S" + year + month + data.Consecutivo);
            $("#folio_pedido").val(data.Consecutivo);
            //$("#folio_fol").val(data.Consecutivo);
            
          }
        }, error: function(data)
        {
          console.log("ERROR data folio_consecutivo = ",data);
        }
      });
    }

</script>