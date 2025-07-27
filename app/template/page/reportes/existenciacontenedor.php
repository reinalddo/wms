<?php
    $almacenes = new \AlmacenP\AlmacenP();
    $model_almacen = $almacenes->getAll();
?>

<link href="/css/plugins/dataTables/jquery.dataTables.min.css" rel="stylesheet"/>
<link href="/css/plugins/dataTables/buttons.dataTables.min.css" rel="stylesheet"/>
<link href="/css/plugins/chosen/chosen.css" rel="stylesheet">

<style>
.bt{
    margin-right: 10px;
}
.btn-blue{
    background-color: blue !important;
    border-color: blue !important;
    color: white !important;
}
</style>

<div class="wrapper wrapper-content  animated " id="list">
    <h3>Existencia por ubicacion de contenedores*</h3>
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-title">
                    <div class="row">
                        <div class="col-lg-3">
                            <div class="form-group">
                                <label for="email">Almacén</label>
                                <select name="almacen" id="almacen" class="chosen-select form-control">
                                    <option value="">Seleccione Almacén</option>
                                    <?php foreach( $model_almacen AS $almacen ): ?>
                                        <?php if($almacen->Activo == 1):?>
                                            <option value="<?php echo $almacen->id; ?>"><?php echo"($almacen->clave)". $almacen->nombre; ?></option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-group">
                                <label for="zona">Zona de Almacenaje</label>
                                <select name="zona" id="zona" class="chosen-select form-control">
                                    <option value="">Seleccione Zona</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-group">
                                <label for="articulo">Articulo</label>
                                <select name="articulo" id="articulo" class="chosen-select form-control">
                                    <option value="">Seleccione Articulo</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-group">
                                <label for="proveedor">Proveedor</label>
                                <select name="proveedor" id="proveedor" class="chosen-select form-control">
                                    <option value="">Seleccione Proveedor</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-group">
                                <label for="BL">BL</label>
                                <input name="BL" id="selectBL" class="form-control" placeholder="BL"></input>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <label for="email">&#160;&#160;</label>
                            <div class="form-group">
                                <button id="search" name="singlebutton" class="btn btn-primary">Buscar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
                <div class="ibox-content">
                  <div class="table-responsive">
                    <table id="example"  class="table table-hover table-striped no-margin">
                      <thead>
                        <div>
                          <label id="codigo_BL">Codigo BL:</label>
                        </div>
                        <tr>
                          <th>Almacén</th>
                          <th>Zona de Almacenaje</th>
                          <th>Código BL*</th>
                          <th>Clave</th>
                          <th>Descripción</th>
                          <th>Cantidad</th>
                          <th>Fecha de Ingreso</th>
                          <th>Tipo de Ubicación</th>
                        </tr>
                      </thead>
                    </table>
                  </div>
                </div>
              
                <div class="inbox">
                  <div class="ibox-title">
                    <div class="row">
                      <div class="col-md-3">
                        <div class="form-group">
                          <label>Importe Total</label> 
                            <input id="totalPromedio" name="totalPromedio" type="text" class="form-control" readonly>
                        </div>
                      </div>
                    </div>
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

<!--script src="/IsisWMS-War/assets/global/scripts/datatable.js" type="text/javascript"></script>
<script src="/IsisWMS-War/assets/global/plugins/datatables/datatables.min.js" type="text/javascript"></script>
<script src="/IsisWMS-War/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js"    type="text/javascript"></script-->
<script>
  
   
  
    $(document).ready(function(){
      
      

      $(function() {
        $('.chosen-select').chosen();
        $('.chosen-select-deselect').chosen({ allow_single_deselect: true });
      });
      /**
         * @author Ricardo Delgado.
         * Busca y selecciona el almacen predeterminado para el usuario.
         */ 
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
                      var alma = document.getElementById('almacen');
                      setTimeout(function() {
                        alma.value = data.codigo.id;
                        $(alma).trigger("chosen:updated");
                        $('#almacen').trigger('change');
                        $('#search').trigger('click');
                      }, 1000);
                        /*document.getElementById('almacen').value = data.codigo.id;
                        $('#almacen').trigger('change');
                        $('#search').trigger('click');*/
                        //search();
                    }
                },
                error: function(res){
                    window.console.log(res);
                }
            });
        }

        almacenPrede();
      
      
      

        $('#example').DataTable( {
          "processing": true,
          dom: 'Bfrtip',
          buttons: [
            {
                extend: 'excelHtml5',
                title: 'Existencias en ubicación'
            },
             {
                extend: 'pdfHtml5',
                title: 'Existencias en ubicación'
            }
          ],
        "bFilter": false,
         responsive: true,
            "language": {
                  "lengthMenu": "Mostrando _MENU_ registros",
                  "zeroRecords": "Sin Registros - :(!!",
                  "info": "Pagina _PAGE_ de _PAGES_",
                  "infoEmpty": "No Existen Registros",
                  "infoFiltered": "(Filtrando de un total de _MAX_ registros)",
                   "sSearch": "Filtrar:",
                   "sProcessing":       "Cargando...",

                  "oPaginate": {
                      "sNext": "Sig",
                      "sPrevious": "Ant",
                      "sLast": "Ultimo",
                      "sFirst":     "Primero",
                  }
              },
          

      });

      $(".dt-button.buttons-excel").attr('class','btn btn-primary btn-sm bt');
      $(".dt-button.buttons-pdf").attr('class', 'btn btn-danger btn-sm bt');
      $(".dt-buttons a.btn.btn-primary.btn-sm.bt").unbind();
      $('.dt-buttons a.btn.btn-danger.btn-sm.bt').unbind();
      
    });


    function table()
    {
      $('#example').DataTable( {
        "processing": true,
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'excelHtml5',
                title: 'Existencia en ubicación'
            },
            {
                extend: 'pdfHtml5',
                  title: 'Existencia en ubicación'
            }
        ],
        responsive: true,
            "language": {
                  "lengthMenu": "Mostrando _MENU_ registros",
                  "zeroRecords": "Sin Registros - :(!!",
                  "info": "Pagina _PAGE_ de _PAGES_",
                  "infoEmpty": "No Existen Registros",
                  "infoFiltered": "(Filtrando de un total de _MAX_ registros)",
                   "sSearch": "Filtrar:",
                   "sProcessing":       "Cargando...",
                  "oPaginate": {
                      "sNext": "Sig",
                      "sPrevious": "Ant",
                      "sLast": "Ultimo",
                      "sFirst":     "Primero",
                  }
              },
        "processing": true,
        "bDestroy": true,
        "serverSide": true,
        "bFilter": false,
        "columns": [
            { "data": "almacen" },
            { "data": "zona" },
            { "data": "codigo" },
            { "data": "clave" },
            { "data": "descripcion" },
            { "data": "cantidad", "className": "dt-right" },
            { "data": "fecha_ingreso" },
            { "data": "tipo_ubicacion" },
        ],
        

        "ajax": {
            
            "url": "/api/reportes/lista/existenciacontenedor.php",
             "type": "GET",
                "data": {
                  "almacen" : $("#almacen").val(),
                  "articulo" : $("#articulo").val(),
                  "zona": $("#zona").val(),
                  "proveedor": $("#proveedor").val(),
                  "bl": $("#selectBL").val(),
                  "action":"existenciaUbicacion"
                }
        }
        
        });
    
    
    $(".dt-button.buttons-excel").attr('class','btn btn-primary btn-sm bt');
    $(".dt-button.buttons-pdf").attr('class', 'btn btn-danger btn-sm bt');
      
    function traer_BL()
    {
      $.ajax({
            url: "/api/reportes/lista/existenciacontenedor.php",
             type: "POST",
                data: {
                  almacen : $("#almacen").val(),
                  action:"traer_BL"
                },
                success: function (data) {
                  
                  $("#codigo_BL").text("Codigo BL:"+" "+data);
                }
        });
    }
    traer_BL();
      
    //Excel
    
    $(".dt-buttons a.btn.btn-primary.btn-sm.bt").unbind().bind('click', function(e){
      var form = document.createElement("form"),
          input1 = document.createElement("input"),
          input2 = document.createElement("input"),
          input3 = document.createElement("input"),
          input4 = document.createElement("input"),
          input5 = document.createElement("input");
      input1.setAttribute('name', 'nofooternoheader');
      input1.setAttribute('value', 'true');
      input2.setAttribute('name', 'almacen');
      input2.setAttribute('value', document.getElementById("almacen").value);
      input3.setAttribute('name', 'zona');
      input3.setAttribute('value', document.getElementById("zona").value);
      input4.setAttribute('name', 'articulo');
      input4.setAttribute('value', document.getElementById("articulo").value);
      input5.setAttribute('name', 'action');
      input5.setAttribute('value', 'exportExcelExistenciaUbica');
      form.setAttribute('action', '/api/reportes/lista/existenciacontenedor.php');
      form.setAttribute('method', 'post');
      form.setAttribute('target', '_blank');
      form.appendChild(input1);
      form.appendChild(input2);
      form.appendChild(input3);
      form.appendChild(input4);
      form.appendChild(input5);
      document.body.appendChild(form);
      form.submit();
    });

    //PDF
    $('.dt-buttons a.btn.btn-danger.btn-sm.bt').unbind().bind('click', function(e)
    {
      var title = 'Reporte de Existencia por Ubicación';
      var cia = <?php echo $_SESSION['cve_cia'] ?>;
      var content = '';
      var nombre_BL = '';

      $.ajax({
        url: "/api/reportes/lista/existenciacontenedor.php",
        type: "POST",
        data: {
          almacen : $("#almacen").val(),
          action:"traer_BL"
        },
        success: function (data) {
          nombre_BL = data;
        }
      });

      $.ajax({
        type: "POST",
        url: "/api/reportes/update/index.php",
        data: {
            "action": "existenciaUbicacion",
            "almacen" : $("#almacen").val(),
            "zona" : $("#zona").val(),
            "articulo" : $("#articulo").val(),
        },
        success: function(data, textStatus, xhr)
        {
//           console.log("Data",data);
//           return;
          var data = JSON.parse(data).data;
          var content_wrapper = document.createElement('div');
          var table = document.createElement('table');
          table.style.width = "100%";
          table.style.borderSpacing = "0";
          table.style.borderCollapse = "collapse";
          var thead = document.createElement('thead');
          var tbody = document.createElement('tbody');
          var head_content = '';
          head_content += `<tr>
                                <th colspan="9">${data[0][0]}</th>
                            </tr>
                            <tr>
                                <th > </th>
                                <th > </th>
                                <th > </th>
                                <th > </th>
                            </tr>`;
          head_content += `<tr>
                                <th style="border: 1px solid #ccc">Codigo BL</th>
                                <th style="border: 1px solid #ccc">Clave</th>
                                <th style="border: 1px solid #ccc">Descripción</th>
                                <th style="border: 1px solid #ccc; text-align: center;">Cantidad</th>
                              </tr>`;
          var body_content = '';
          var total = 0;
          data.forEach(function(item, index)
          {
            var subtotal = item.costoPromedio*item.cantidad;
            total = total + subtotal;
            body_content += `
            <tr>
              <td style="border: 1px solid #ccc">${item.codigo}</td>
              <td style="border: 1px solid #ccc">${item.clave}</td>
              <td style="border: 1px solid #ccc">${item.descripcion}</td>
              <td style="border: 1px solid #ccc; text-align: right;">${item.cantidad}</td>
            </tr>
            `;
          });
          body_content += `
            <br>
            <tr>
              <td ></td>
              <td ></td>
              <td ></td>
              <td ></td>
              <td ></td>
              <td ></td>
              <td ></td>
              <td style="border: 1px solid #ccc"><b>TOTAL</b></td>
              <td style="border: 1px solid #ccc">$ ${total}</td>
            </tr>
          `;

          tbody.innerHTML = body_content;
          thead.innerHTML = head_content;
          table.appendChild(thead);
          table.appendChild(tbody);
          content_wrapper.appendChild(table);
          content = content_wrapper.innerHTML;

          /*Creando formulario para ser enviado*/

          var form = document.createElement("form");
          form.setAttribute("method", "post");
          form.setAttribute("action", "/api/reportes/generar/pdf.php");
          form.setAttribute("target", "_blank");

          var input_content = document.createElement('input');
          var input_title = document.createElement('input');
          var input_cia = document.createElement('input');
          input_content.setAttribute('type', 'hidden');
          input_title.setAttribute('type', 'hidden');
          input_cia.setAttribute('type', 'hidden');
          input_content.setAttribute('name', 'content');
          input_title.setAttribute('name', 'title');
          input_cia.setAttribute('name', 'cia');
          input_content.setAttribute('value', content);
          input_title.setAttribute('value', title);
          input_cia.setAttribute('value', cia);

          form.appendChild(input_content);
          form.appendChild(input_title);
          form.appendChild(input_cia);

          document.body.appendChild(form);
          form.submit();
        }
      });
    });
}


      $('#almacen').change(function(e) {
        var almacen= $(this).val();
            $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                cve_almac : almacen,
                action : "getArticulosYZonasAlmacenConExistencia"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/ubicaciones/update/index.php',
            success: function(data) {

                    var options_articulos = $("#articulo");
                    options_articulos.empty();
                    options_articulos.append(new Option("Seleccione Artículo", ""));

                    var options_zonas = $("#zona");
                    options_zonas.empty();
                    options_zonas.append(new Option("Seleccione Zona", ""));
              
                    var options_proveedor = $("#proveedor");
                    options_proveedor.empty();
                    options_proveedor.append(new Option("Seleccione Proveedor", ""));

                    for (var i=0; i<data.articulos.length; i++)
                    {
                        if(data.articulos[i].id_articulo && data.articulos[i].articulo)
                          options_articulos.append(new Option(data.articulos[i].id_articulo +" "+data.articulos[i].articulo, data.articulos[i].id_articulo));
                    }

                    for (var i=0; i<data.zonas.length; i++)
                    {
                      if(data.zonas[i].clave && data.zonas[i].descripcion)
                        options_zonas.append(new Option(data.zonas[i].clave +" "+data.zonas[i].descripcion, data.zonas[i].clave));
                    }
              
                    for (var i=0; i<data.proveedores.length; i++)
                    {
                      if(data.proveedores[i].proveedor)
                      {
                        proveedor = data.proveedores[i].proveedor.split("-");
                        options_proveedor.append(new Option(data.proveedores[i].proveedor, proveedor[1]));
                      }
                        
                    }
                    $("#articulo").trigger("chosen:updated");
                    $("#zona").trigger("chosen:updated");
                    $("#proveedor").trigger("chosen:updated");

                }

        });
        
        


        });

      function calcularImporteExistencias()
      {
          $.ajax({
              type: "POST",
              dataType: "json",
              data: 
              {
                  almacen : $("#almacen").val(),
                  articulo : $("#articulo").val(),
                  zona: $("#zona").val(),
                  proveedor: $("#proveedor").val(),
                  bl: $("#selectBL").val(),
                  action : "calcularImporte"
              },
              beforeSend: function(x)
              { 
                  if(x && x.overrideMimeType) 
                  { 
                    x.overrideMimeType("application/json;charset=UTF-8"); 
                  }
              },
              url: '/api/ubicaciones/update/index.php',
              success: function(data) 
              {

                  if (data.success == true)
                  {
                    if (data.importe == null)
                    {
                      $("#totalPromedio").val("$"+" "+("0.00")); 
                    }
                    if (data.importe != null)
                    {
                      $("#totalPromedio").val("$"+" "+(data.importe)); 
                    }
                    
                  }
                  else
                  {
                    console.log("No Calculo el importe");
                  }
              }
          });
      }
  
      function traer_BL()
      {
       $.ajax({
            url: "/api/reportes/lista/existenciacontenedor.php",
            type: "GET",
            data: {
              almacen : $("#almacen").val(),
              articulo : $("#articulo").val(),
              zona: $("#zona").val(),
              action:"existenciaUbicacion"
            },
            success: function(data) {
          
            }
        });
               
              
      }
  
  $( "#search" ).click(function() {
    console.log('A1');
    if ($("#almacen").val()=="")
      return;
    table();
    calcularImporteExistencias();
    traer_BL();
  });
  
  
  $(document).ready(function(){
      setTimeout(function(){

      
       
      calcularImporteExistencias();

      },2000);
  });

</script>
