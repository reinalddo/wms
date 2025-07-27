<?php 
include_once  $_SERVER['DOCUMENT_ROOT']."/app/host.php";
$listaProv = new \Proveedores\Proveedores();
$almacenes = new \AlmacenP\AlmacenP();
$listaProto = new \Protocolos\Protocolos();

$cve_proveedor = "";
if(isset($_SESSION['id_proveedor']))
{
    $cve_proveedor = $_SESSION['id_proveedor'];
}
?>
<input type="hidden" id="cve_proveedor" value="<?php echo $cve_proveedor; ?>">


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
            <h5>Importar OC | Entradas</h5>
        </div>
        <div class="ibox-content">
          <form id="form-import" action="/importar/pedidos" method="post"  enctype="multipart/form-data">

            <div class="form-group">
                <label>Almacén</label>
                <select class="form-control" id="almacenes" name="almacenes">
                    <option value="">Seleccione un Almacen</option>
                    <?php foreach( $almacenes->getAll(" AND c_almacenp.id = ".$_SESSION['id_almacen']) AS $a ): ?>
                        <option value="<?php echo $a->clave; ?>" selected><?php echo "($a->clave) $a->nombre"; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group"><label>Numero de Orden | Cross Docking (ERP)</label> 
                <input id="NumOrden" name="NumOrden" type="text" placeholder="Numero de Orden" class="form-control" maxlength="30">
            </div>

            <?php 
            /*
            ?>
            <div class="form-group">
                <label>Empresa | Proveedor*</label>
                <select class="form-control" id="empresa_proveedor">
                    <option value="">Seleccione</option>
                    <?php foreach( $listaProv->getAll(" AND es_cliente = 1 ") AS $a ): ?>
                    <option value="<?php echo $a->ID_Proveedor; ?>">[<?php echo $a->ID_Proveedor; ?>] - <?php echo $a->Nombre; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php 
            */
            ?>
            <div class="form-group">
                <label>Empresa</label> <!--lilo-->
                <select class="chosen-select form-control" id="empresa" name="empresa">
                    <option value="">Seleccione una Empresa</option>  
                    <?php foreach( $listaProv->getAll(" AND es_cliente = 1 ") AS $p ): ?>    
                        <option value="<?php echo $p->ID_Proveedor; ?>"><?php echo $p->Nombre; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Proveedor</label> <!--lilo-->
                <select class="chosen-select form-control" id="proveedor" name="proveedor">
                    <?php if($cve_proveedor == ""){ ?>
                    <option value="">Seleccione un Proveedor</option>  
                    <?php } foreach( $listaProv->getAll() AS $p ): ?>    
                        <?php if($cve_proveedor == ""){ ?>
                        <option value="<?php echo $p->ID_Proveedor; ?>"><?php echo $p->Nombre; ?></option>
                    <?php }
                    else if($cve_proveedor == $p->ID_Proveedor) {
                    ?>
                        <option value="<?php echo $p->ID_Proveedor; ?>"><?php echo $p->Nombre; ?></option>
                    <?php 
                    } 
                    ?>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Seleccione OC | Entradas</label>
                <select class="form-control" name="tipo" id="tipo" required>
                  <option value="oc" selected>OC | Entradas</option>
                </select>
            </div>
<?php 
              $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

              $id_almacen = $_SESSION['id_almacen'];
              $sql = "SELECT * FROM tubicacionesretencion WHERE cve_almacp = {$id_almacen} AND Activo = 1;";
              $rs = mysqli_query($conn, $sql);

?>

            <div class="form-group" id="zonarecepcionif">
                <label>Zona de Recepción</label>
                <select class="form-control" name="zonarecepcioni" id="zonarecepcioni">
                    <option value="">Seleccione</option>
                    <?php 
                    while($row = mysqli_fetch_array($rs, MYSQLI_ASSOC))
                    {
                        extract($row);
                    ?>
                        <option value="<?php echo $cve_ubicacion; ?>"><?php echo "(".$cve_ubicacion.") ".$desc_ubicacion; ?></option>
                    <?php 
                    }
                    ?>
                </select>
            </div>

<?php 

              $sql = "SELECT CURDATE() fecha_actual FROM DUAL";
              $rs = mysqli_query($conn, $sql);
              $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
              $fecha_actual = $resul['fecha_actual'];
?>
            <div class="form-group" style="display:none;">
                <label>Fecha Actual</label>
                <div class="input-group date" id="data_6">
                    <input id="fecha_oc" name="fecha_oc" type="text" class="form-control" value="<?php echo $fecha_actual; ?>" required>
                    <span class="input-group-addon">
                    <span class="glyphicon glyphicon-calendar"></span>
                </div>
            </div>

            <div class="form-group">
                <label>Fecha Entrega Estimada*</label>
                <div class="input-group date" id="data_6">
                    <input id="fechaestimada" name="fechaestimada" type="text" class="form-control" value="<?php echo $fecha_actual; ?>" required>
                    <span class="input-group-addon">
                    <span class="glyphicon glyphicon-calendar"></span>
                </div>
            </div>

            <div class="form-group">
                <label>Tipo de Orden de Compra (Protocolo)*</label>
                <select class="form-control" id="Protocol" name="Protocol">
                    <option value="">Seleccione</option>
                    <?php foreach( $listaProto->getAll() AS $a ): ?>
                    <option value="<?php echo $a->ID_Protocolo; ?>"><?php echo $a->descripcion; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group"><label>Consecutivo de Protocolo</label> 
                <input id="Consecut" name="Consecut" type="text" placeholder="Consecutivo de Protocolo"  class="form-control" readonly="readonly">
            </div>

            <div class="form-group"><label>Tipo de Cambio</label> 
                <input id="tipo_cambio" name="tipo_cambio" type="number" min="0"  class="form-control" value="1">
            </div>

             <label>Seleccionar archivo Excel para importar</label>
             <div class="form-group">
                <input type="file" name="file" id="file" class="form-control" accept=".xlsx" required>
             </div>

            <div class="checkbox">
                <label for="palletizar" style="font-weight: 600;font-size: 14px; -moz-user-select: none; -webkit-user-select: none;-ms-user-select: none; user-select: none;">
                <input type="checkbox" name="palletizar" id="palletizar" value="0">Palletizar</label>
                <input type="hidden" name="palletizar_oc" id="palletizar_oc" value="0">
            </div>

           </form>
           <div class="col-md-12">
            <div class="progress" style="display:none">
              <div class="progress-bar progress-bar-info progress-bar-striped" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:0%">
                <div class="percent">0%</div >
              </div>
            </div>
          </div>
          <div class="col-md-6" style="text-align: left">
            <button id="btn-layout" type="button" class="btn btn-primary">Descargar Layout</button>
          </div>
          <div style="text-align: right">
            <button onclick="prueba_importacion()" id="btn-import" type="submit" class="btn btn-primary">Importar</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
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

<script src="/js/plugins/chosen/chosen.jquery.js"></script>

<!-- d3 and c3 charts -->
<script src="/js/plugins/d3/d3.min.js"></script>
<script src="/js/plugins/c3/c3.min.js"></script>

<!-- jqGrid -->
<script src="/js/plugins/jqGrid/i18n/grid.locale-es.js"></script>
<script src="/js/plugins/jqGrid/jquery.jqGrid.min.js"></script>

<!-- Data picker -->
<script src="/js/plugins/datapicker/bootstrap-datepicker.js"></script>
<script src="/js/plugins/datapicker/bootstrap-datepicker.js"></script>
<script src="/js/plugins/sweetalert/sweetalert.min.js"></script>
<script src="/js/utils.js"></script> 

<!--comienza la prueba de importacion EDG-->

<script>
$('#btn-layout').on('click', function(e) {
  //e.preventDefault();  //stop the browser from following
  //window.location.href = 'http://www.tinegocios.com/proyectos/wms/Layout_Pedidos.xlsx';
  //window.location.href = '../../../../Layout/Layout_OC.xlsx';
  window.location.href = '/Layout/Layout__OC.xlsx';
}); 
  
$('#btn-import_prueba').on('click', function (){
    prueba_importacion();
});
    $('#data_6').datetimepicker({
        locale: 'es',
        format: 'DD-MM-YYYY',
        useCurrent: false
    });

    $('#data_6').data("DateTimePicker").date(new Date());

    $('#Protocol').change(function(e) {
        var ID_Protocolo= $(this).val();
        console.log("ID_Protocolo = ", ID_Protocolo);
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                ID_Protocolo : ID_Protocolo,
                action : "getConsecutivo"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                                    },
            url: '/api/ordendecompra/update/index.php',
            success: function(data) {
                console.log("SUCCESS = ", data);
                $('#Consecut').val(data.consecutivo);
            }
            , error: function(data)
            {
                console.log("ERROR = ", data);
            }

        });

    });

    $("#palletizar").click(function(){

        var palletizar = 0;
        if($("#palletizar").is(':checked'))
            palletizar = 1;

      $("#palletizar_oc").val(palletizar);

      console.log("palletizar_oc = ", $("#palletizar_oc").val());

    });

  //
  function prueba_importacion() {
    console.log("Funcion ok");
/*
    if($("#zonarecepcioni").val() == "")
    {
        swal("Error", "Selecciona una Zona de Recepción", "error");
        return;
    }
*/
    if($("#NumOrden").val() == "")
    {
        swal("Error", "Registra un Número de Orden | Cross Docking (ERP)", "error");
        return;
    }

    if($("#Protocol").val() == "" && $("#zonarecepcioni").val() != "")
    {
        swal("Error", "Debe Registrar un Protocolo", "error");
        return;
    }


    if($("#file").val() == "")
    {
        swal("Error", "Debe Subir un Archivo Excel XLSX", "error");
        return;
    }


    
    $('#btn-import').prop('disable', true);
    var bar = $('.progress-bar');
    var percent = $('.percent');
    var formData = new FormData();
    formData.append("clave", "valor");



    if($("#almacenes").val() && $("#tipo").val() && $("#proveedor").val() /*&& $("#Protocol").val()*/ && $("#file").val())
    {


      $.ajax({
          // Your server script to process the upload
          url: '/ordenescompra/importarocentradas',
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
                    console.log("data.pedidos = ", data.pedidos);
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
                  swal("Error", "El archivo posee Formatos de Fecha Incorrectos ", "error");
            }
        });
      }
      else 
      {
        swal("Error", "Debe seleccionar todos los campos", "error");
      }
    }
</script>