<?php 
$listaAP = new \AlmacenP\AlmacenP();
$listaProvee = new \Proveedores\Proveedores();
?>
<link href="/css/plugins/dataTables/dataTables1.min.css" rel="stylesheet"/>
<link href="/css/plugins/dataTables/buttons.dataTables.min.css" rel="stylesheet"/>
<link href="/css/plugins/chosen/chosen.css" rel="stylesheet">
<link href="/css/plugins/sweetalert/sweetalert.css" rel="stylesheet">
<style>
.bt{
	margin-right: 10px;
}

.btn-blue{
	background-color: blue !important;
    border-color: blue !important;
	color: white !important;
}

.blink {
  
      animation-name: blink;
      animation-duration: 4s;
      animation-timing-function: linear;
      animation-iteration-count: infinite;

      -webkit-animation-name:blink;
      -webkit-animation-duration: 4s;
      -webkit-animation-timing-function: linear;
      -webkit-animation-iteration-count: infinite;
    }

.checks_borrar{
    cursor: pointer;
}

    @-moz-keyframes blink{  
      0% { opacity: 1.0; }
      50% { opacity: 0.0; }
      100% { opacity: 1.0; }
    }

    @-webkit-keyframes blink {  
      0% { opacity: 1.0; }
      50% { opacity: 0.0; }
       100% { opacity: 1.0; }
    }

    @keyframes blink {  
      0% { opacity: 1.0; }
       50% { opacity: 0.0; }
      100% { opacity: 1.0; }
    }

</style>


<div class="modal fade" id="modal_detalles" role="dialog">

        <div class="modal-dialog modal-lg" style="width:90%;">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4>Detalle de Entrada</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="table-responsive">
                            <table id="detalles"class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th>FOLIO ENTRADA</th>
                                        <th>LINEA</th>
                                        <th>CONTENEDOR/PALLET</th>
										<th>LP</th>
                                        <th>CJ</th>
                                        <th>CLAVE</th>
                                        <th>DESCRIPCIÓN</th>
                                        <th>LOTE</th>
                                        <th>CADUCIDAD</th>
                                        <th>SERIE</th>
                                        <!--<th>CANTIDAD PEDIDA</th>-->
                                        <th>CANTIDAD RECIBIDA</th>
                                        <!--<th>CANTIDAD FALTANTE</th>
                                        <th>DIFERENCIA</th>-->
                                        <th>CANTIDAD UBICADA</th>
                                        <th>CANTIDAD POR UBICAR</th>
                                        <th>FECHA RECEPCIÓN</th>                    
                                    </tr>
                                </thead>  
                                <tbody></tbody>              
                            </table>
                        </div>
                        </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" id="btn-eliminar" style="float:left;">Eliminar Registro(s)</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
</div>





<div class="wrapper wrapper-content  animated " id="list">
    <h3>Pendiente de acomodo*</h3>
    <div class="ibox">
        <div class="ibox-title">
            <div class="row">
                <div class="col-md-3">
                    <label>Almacen</label>
                    <div class="input-group">                            
                        <select class="form-control chosen-select" id="almacen">
                            <option value="">Seleccione un almacen</option>
                            <?php foreach( $listaAP->getAll() AS $a ): ?>
                                <option value="<?php echo $a->clave; ?>" <?php if($a->clave == $_SESSION['cve_almacen']) echo "selected"; ?>><?php echo "(".$a->clave .") ". $a->nombre; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div> 
                <div class="col-md-3">
                    <label>Proveedor</label>
                        <select class="form-control chosen-select" id="proveedor">
                            <option value="">Seleccione un Proveedor</option>
                            <?php foreach( $listaProvee->getAllProvRTM($_SESSION['id_almacen']) AS $a ): ?>
                                <option value="<?php echo $a->ID_Proveedor; ?>"><?php echo "( ".$a->cve_proveedor ." ) ". $a->Nombre; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                  <div class="col-md-3">
                    <br>
                          <div class="input-group-btn"> 
                                <input type="text" class="form-control" name="buscador" id="buscador" placeholder="Entrada|OC|Articulo|Lote|LP|Recepción|Proveedor">
                              <button id="button-buscar" class="btn btn-primary"> <span class="fa fa-search"></span> Buscar</button>
                          </div>
                      </div>
                  
								<div class="col-md-1"> 
										<label>TOTAL PENDIENTE</label>
										<input  class="form-control" id="valor_total_pendiente" placeholder="0.00" disabled style="text-align: center;"></input>
								</div>

                <div class="col-md-2">
                    <label for="email">&#160;&#160;</label>
                    <div class="form-group">
                        <button id="boton_pdf" name="singlebutton" class="btn btn-primary">PDF</button>
                    </div>
                </div>
              </div> 
            </div>
        </div>
        <div class="ibox-content">
            <div class="table-responsive">
                <table id="table-info"  class="table table-hover table-striped no-margin" style="width:100%">
                    <thead>
                        <tr>
                            <th>ACCIONES</th>
                            <th>FOLIO INGRESO</th>
                            <th>FOLIO OC</th>
                            <th>FOLIO ERP</th>
                            <th>TIPO RECEPCION</th>
                            <th>ALMACÉN</th>
                            <th>PROVEEDOR</th>
                            <th>FECHA COMPROMISO</th>
                            <th>FECHA RECEPCIÓN</th>
                            <th>FECHA CIERRE</th>
                            <th>TOTAL</th>
                            <th>PESO</th>
                           <!-- <th>ERP</th> -->
                            <th>RECEPCIÓN ENTRADA</th>
                            <th>USUARIO</th>
                        </tr>
                    </thead> 
                    <tbody id = "tbody">
                    </tbody>               
                </table>
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
<script src="/js/plugins/chosen/chosen.jquery.js"></script>

<!-- Select -->
<script src="/js/select2.js"></script>
<!-- Data picker -->
<script src="/js/plugins/dataTables/dataTables.min.js"></script>
<script src="/js/plugins/dataTables/dataTables_bootstrap.min.js"></script>
<script src="/js/plugins/dataTables/dataTables.buttons.min.js"></script>
<script src="/js/plugins/dataTables/buttons.flash.min.js"></script>
<script src="/js/plugins/dataTables/jszip.min.js"></script>
<script src="/js/plugins/dataTables/pdfmake.min.js"></script>
<script src="/js/plugins/dataTables/vfs_fonts.js"></script>
<script src="/js/plugins/dataTables/buttons.html5.min.js"></script>
<script src="/js/plugins/dataTables/buttons.print.min.js"></script> 
<script src="/js/plugins/sweetalert/sweetalert.min.js"></script>
<script src="/js/utils.js"></script> 
<script type="text/javascript">

    var almacen = document.getElementById('almacen'),
        proveedor = document.getElementById('proveedor'),
        button_buscar = document.getElementById('button-buscar'),
        TABLE = null, TABLE2 = null,
        tableData = new TableData(),
        buttons = [{
                      extend: 'excelHtml5',
                      title: 'Pendiente Acomodo',
                      customize: function() { swal("Descargando Excel", "Su descarga empezara en breve", "success");}
                    },
                    {
                      extend: 'pdfHtml5',
                      title: 'Pendiente Acomodo',
                      orientation: 'landscape',
                      download: 'open',
                      customize: function() { swal("Descargando PDF", "Su descarga empezara en breve", "success");},
                    }
                ];
    almacenPrede();

    button_buscar.onclick = function()
    {
      button_buscar.disabled = true;
      button_buscar.textContent = "Cargando...";
      $(button_buscar).addClass('blink');
      search();
    };

    /**
     * @author Ricardo Delgado.
     * Busca y selecciona el almacen predeterminado para el usuario.
     */

     $("#almacen").change(function(){
        search();
     });

    function almacenPrede()
    {
      $.ajax({
        type: "POST",
        dataType: "json",
        data: {
          idUser: '<?php echo $_SESSION["id_user"]?>',
          action: 'search_almacen_pre'
        },
        beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }},
        url: '/api/almacenPredeterminado/index.php',
        success: function(data) {
          if (data.success == true) 
          {
            almacen.value = data.codigo.clave;
            search();
          }
        },
        error: function(res){
          window.console.log(res);
        }
      });
    }

    function search()
    {
        console.log("action :", 'cabecera');
        console.log("almacen:", almacen.value);
        console.log("proveedor:", proveedor.value);
        console.log("criterio:", $("#buscador").val());

      var data = {action : 'cabecera', almacen: almacen.value, proveedor: proveedor.value, criterio: $("#buscador").val()};
      $.ajax({
        url: "/api/reportes/lista/pendienteacomodo.php",
        type: "POST",
        data: data,
        beforeSend: function(x){if(x && x.overrideMimeType) x.overrideMimeType("application/json;charset=UTF-8");},
        success: function(res)
        {
          console.log(res.data);
          console.log("hole");
          //return;
          fillTable(res.data);
          button_buscar.disabled = false;
          button_buscar.textContent = "Buscar";
          $(button_buscar).removeClass('blink');
        },
        error : function(res)
        {
          window.console.log(res);
          button_buscar.disabled = false;
          button_buscar.textContent = "Buscar";
          $(button_buscar).removeClass('blink');
        }
      });
    }
  
    function total_pendiete()
    {
      var table = $('#table-info').DataTable();
      var allData = table.rows().data();
      var total =0;
      console.log("esta es la tabla");
      console.log(allData);
      for(var i=0; i < allData.length; i++)
      {
        total += parseFloat(allData[i][8]);
      }
      $("#valor_total_pendiente").val(total);
    }

    function fillTable(node)
    {
      tableData.destroy();
      var body = document.getElementById('tbody');
      body.innerHTML = "";
      if(node)
      {
        body.innerHTML += data(node);
      }
      TABLE = tableData.init("table-info",buttons);
      $(".dt-button.buttons-excel").attr('class','btn btn-primary btn-sm bt');
      $(".dt-button.buttons-pdf").attr('class', 'btn btn-danger btn-sm bt');
      function data(node)
      {
        var _body = "";
        for(var i = 0; i < node.length ; i++)
        {
          _body += 
          '<tr>'+
          '<td><button class="btn" onclick="ver('+node[i].numero_oc+')"> <span class="fa fa-search"></span></button>&nbsp;&nbsp;<a href="/api/koolreport/excel/detalle_rtm/export.php?folio='+node[i].numero_oc+'" class="btn btn-primary" title="Descargar Detalle RTM en Excel"><i class="fa fa-file-excel-o"></i></a></td>'+
          '<td>'+htmlEntities(node[i].numero_oc)+'</td>'+

          '<td>'+htmlEntities(node[i].folio_oc)+'</td>'+
          '<td>'+htmlEntities(node[i].folio_erp)+'</td>'+

          '<td>'+htmlEntities(node[i].tipo)+'</td>'+
          '<td>'+htmlEntities(node[i].almacen)+'</td>'+
          '<td>'+htmlEntities(node[i].proveedor)+'</td>'+
          '<td>'+htmlEntities(node[i].fecha_entrega)+'</td>'+
          '<td>'+htmlEntities(node[i].fecha_recepcion)+'</td>'+
          '<td>'+htmlEntities(node[i].fecha_fin_recepcion)+'</td>'+
          '<td align="right">'+htmlEntities(node[i].total_pedido)+'</td>'+
          '<td align="right">'+parseFloat(htmlEntities(node[i].peso_estimado)).toFixed(2)+'</td>'+
          //'<td>'+htmlEntities(node[i].ERP)+'</td>'+
          '<td>'+htmlEntities(node[i].retecion)+'</td>'+
         '<td>'+htmlEntities(node[i].usuario_activo)+'</td>'+
          '</tr>';
        }
        return _body;
      }
      total_pendiete();
    }


    function ver(folio)
    {
      html = '';
      $.ajax({
        type: "POST",
        dataType: "json",
        url: "/api/reportes/lista/pendienteacomodo.php",
        data: {
          action: 'detalles',
          folio: folio,
        },
        beforeSend: function(x) {
          if(x && x.overrideMimeType) {x.overrideMimeType("application/json;charset=UTF-8");}
          $('#detalles > tbody').html('<tr><td colspan="8">Cargando datos...</td></tr>');
        },
        success: function(data) 
        {
          if (data.success == true) 
          {
            var color_fila = "", check_borrar = '';
            $.each(data.data, function(key, value){
            color_fila = "";
            var lote_val = (value.lote=='')?(value.numero_serie):(value.lote);

            check_borrar = "<label for='check_elm"+value.id+"'><input type='checkbox' class='checks_borrar' id='check_elm"+value.id+"' name='check_elm"+value.id+"' data-folio='"+value.folio_entrada+"' data-articulo='"+value.clave+"' data-lote='"+lote_val+"' data-contenedor='"+value.contenedor+"' data-cantidad='"+value.cantidad_recibida+"' data-id='"+value.id+"' />Eliminar Registro</label>";

            if(value.CantidadDisponible == 0 || (parseFloat(value.CantidadDisponible) > 0 && parseFloat(value.CantidadUbicada) >= parseFloat(value.cantidad_recibida)))
            {
                color_fila = 'style="background-color: #00ff00;"';
                check_borrar = '';
            }
            if(value.CantidadUbicada > 0)
                check_borrar = '';

            html += '<tr '+color_fila+'>'+
              '<td>'+check_borrar+'</td>'+
              '<td align="right">'+value.folio_entrada+'</td>'+
              '<td align="right">'+value.linea+'</td>'+
              '<td>'+value.contenedor+'</td>'+
              '<td>'+value.pallet+'</td>'+
              '<td>'+value.cj+'</td>'+
              '<td>'+value.clave+'</td>'+
              '<td>'+value.descripcion+'</td>'+
              '<td>'+value.lote+'</td>'+
              '<td align="center">'+value.caducidad+'</td>'+
              '<td>'+value.numero_serie+'</td>'+
              //'<td align="right">'+value.cantidad_pedida+'</td>'+
              '<td align="right">'+value.cantidad_recibida+'</td>'+
              //'<td align="right">'+value.cantidad_faltante+'</td>'+
              //'<td align="right">'+value.cantidad_danada+'</td>'+
              '<td align="right">'+value.CantidadUbicada+'</td>'+
              '<td align="right">'+value.CantidadDisponible+'</td>'+
              '<td align="center">'+value.fecha_recepcion+'</td>'
              '</tr>';
            });
            $('#detalles > tbody').html(html);
          }
        },
        error: function(res)
        {
          window.console.log(res);
        }
      });
      $('#modal_detalles').modal('show');   
    }


    function EliminarRegistros()
    {

        var borrar_id = [], borrar_articulo = [], borrar_lote = [], borrar_contenedor = [], borrar_folio = [], borrar_cantidad = [], k = 0;
        $(".checks_borrar").each(function(i,j){
            if($(this).is(":checked"))
            {
                console.log("i = ", i, " id = ", $(this).data("id"), " folio = ", $(this).data("folio"), " articulo = ", $(this).data("articulo"), " lote = ", $(this).data("lote"), " contenedor = ", $(this).data("contenedor"), " cantidad = ", $(this).data("cantidad"));
                borrar_id[k]         = $(this).data("id");
                borrar_folio[k]      = $(this).data("folio");
                borrar_articulo[k]   = $(this).data("articulo");
                borrar_lote[k]       = $(this).data("lote");
                borrar_contenedor[k] = $(this).data("contenedor");
                borrar_cantidad[k]   = $(this).data("cantidad");
                k++;
            }
        });
        console.log("borrar_id = ", borrar_id);
        console.log("borrar_folio = ", borrar_folio);
        console.log("borrar_articulo = ", borrar_articulo);
        console.log("borrar_lote = ", borrar_lote);
        console.log("borrar_contenedor = ", borrar_contenedor);
        console.log("borrar_cantidad = ", borrar_cantidad);
        //return;

        $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    borrar_id: borrar_id, 
                    borrar_folio: borrar_folio, 
                    borrar_articulo: borrar_articulo, 
                    borrar_lote: borrar_lote, 
                    borrar_contenedor: borrar_contenedor, 
                    borrar_cantidad: borrar_cantidad, 
                    action: "EliminarRegistrosEntradas"
                },
                beforeSend: function(x) {
                    if (x && x.overrideMimeType) {
                        x.overrideMimeType("application/json;charset=UTF-8");
                    }
                },
                url: '/api/reportes/update/index.php',
                success: function(data) {
                    swal("Éxito", "Operación Realizada con éxito", "success");
                    $('#modal_detalles').modal('hide');
                }
            });

    }

    $('#btn-eliminar').click(function(e)
    {
        swal({
                title: "¿Está seguro que desea borrar estos registros?",
                text: "Esta acción elimina los registros seleccionados de entrada y no podran recibirse en este folio, esta acción no se puede deshacer",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Borrar",
                cancelButtonText: "Cancelar",
                closeOnConfirm: true
            },
            function() {
                EliminarRegistros();
            });
    });

        $('#boton_pdf').click(function(e)
        {
            var title = 'Reporte Pendiente Acomodo';
            var cia = <?php echo $_SESSION['cve_cia'] ?>;
            var content = '';
            var nombre_BL = '';

            //url: "/api/reportes/lista/existenciaubica.php",
            console.log("#almacen = ", $("#almacen").val());

            $.ajax({
                type: "POST",
                url: "/api/reportes/update/index.php",
                data: {
                    "action": "pendienteAcomodo",
                    "almacen" : $("#almacen").val()
                },
                success: function(data, textStatus, xhr)
                {
                    var data = JSON.parse(data).data;
                    console.log("data = ", data);
                    var content_wrapper = document.createElement('div');
                    var table = document.createElement('table');
                    table.style.width = "100%";
                    table.style.borderSpacing = "0";
                    table.style.borderCollapse = "collapse";
                    var thead = document.createElement('thead');
                    var tbody = document.createElement('tbody');
                    var head_content = '';
                    /*head_content += `
                        <tr><th colspan="9">${data[0][0]}</th></tr>
                        <tr><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th></tr>
                    `;*/
                    head_content += `
                        <tr>
                            <th style="border: 1px solid #ccc; width: 70px; font-size: 11px;">Folio Ingreso</th>
                            <th style="border: 1px solid #ccc; width: 50px;font-size: 11px;">Folio OC</th>
                            <th style="border: 1px solid #ccc; width: 50px;font-size: 11px;">Folio ERP</th>
                            <th style="border: 1px solid #ccc; font-size: 11px;">Tipo Recepción</th>
                            <th style="border: 1px solid #ccc; width: 70px; font-size: 11px;">Almacén</th>
                            <th style="border: 1px solid #ccc; font-size: 11px; width: 100px;">Proveedor</th>
                            <th style="border: 1px solid #ccc; font-size: 11px;width: 100px;">Fecha Compromiso</th>
                            <th style="border: 1px solid #ccc; font-size: 11px;width: 100px;">Fecha Recepción</th>
                            <th style="border: 1px solid #ccc; width: 50px; font-size: 11px;width: 100px;">Fecha Cierre</th>
                            <th style="border: 1px solid #ccc; width: 50px; font-size: 11px;">Total</th>
                            <th style="border: 1px solid #ccc; width: 50px; font-size: 11px;">Peso</th>
                            <th style="border: 1px solid #ccc; text-align: center;width: 100px; font-size: 11px;">Recepción Entrada</th>
                            <th style="border: 1px solid #ccc; text-align: center;width: 100px; font-size: 11px;">Usuario</th>
                        </tr>
                    `;
                    var body_content = '';
                    var total = 0;
                    data.forEach(function(item, index)
                    {
                        var subtotal = item.costoPromedio*item.cantidad;
                        total = total + subtotal;

                        body_content += `
                            <tr>
                                <td style="border: 1px solid #ccc; text-align: center; width: 70px; font-size: 10px;">${item.numero_oc}</td>
                                <td style="border: 1px solid #ccc; width: 50px;font-size: 10px;">${item.folio_oc}</td>
                                <td style="border: 1px solid #ccc; width: 50px;font-size: 10px;">${item.folio_erp}</td>
                                <td style="border: 1px solid #ccc; text-align: center;font-size: 10px;">${item.tipo}</td>
                                <td style="border: 1px solid #ccc; width: 70px; font-size: 10px;">${item.almacen}</td>
                                <td style="border: 1px solid #ccc; font-size: 10px;width: 100px;">${item.proveedor}</td>
                                <td style="border: 1px solid #ccc; text-align: center; font-size: 10px;width: 100px;">${item.fecha_entrega}</td>
                                <td style="border: 1px solid #ccc; font-size: 10px;width: 100px;">${item.fecha_recepcion}</td>
                                <td style="border: 1px solid #ccc; text-align: right; width: 50px; font-size: 10px;width: 100px;">${item.fecha_fin_recepcion}</td>
                                <td style="border: 1px solid #ccc; text-align: right; width: 50px; font-size: 10px;">${item.total_pedido}</td>
                                <td style="border: 1px solid #ccc; text-align: right; width: 50px; font-size: 10px;">${item.peso_estimado}</td>
                                <td style="border: 1px solid #ccc; text-align: left; width: 100px; font-size: 10px;">${item.retencion}</td>
                                <td style="border: 1px solid #ccc; text-align: left; width: 100px; font-size: 10px;">${item.usuario_activo}</td>
                            </tr>
                        `;
                    });
                    body_content += `
                    `;

                    tbody.innerHTML = body_content;
                    thead.innerHTML = head_content;
                    table.appendChild(thead);
                    table.appendChild(tbody);
                    content_wrapper.appendChild(table);
                    content = content_wrapper.innerHTML;

                    //Creando formulario para ser enviado

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
                }, error: function(data)
                {
                    console.log("ERROR", data);
                }
            });
        });

/*
    $(document).ready(function(){
      $('#example').DataTable({
        "processing": true,
				dom: 'Bfrtip',
        buttons: [
        {
          extend: 'csvHtml5',
          title: 'Comprobante de Ingreso'
        },
        {
          extend: 'excelHtml5',
          title: 'Comprobante de Ingreso'
        },
        {
          extend: 'pdfHtml5',
          title: 'Comprobante de Ingreso'
        },
        ],
		responsive: true,
		language: {
                "lengthMenu": "Mostrando _MENU_ registros",
                "zeroRecords": "Sin Registros",
                "info": "Pagina _PAGE_ de _PAGES_",
                "infoEmpty": "No Existen Registros",
                "infoFiltered": "(Filtrando de un total de _MAX_ registros)",
                "sSearch": "",
                "searchPlaceholder": "Buscar",
                "sProcessing":      "Cargando...",
                "paginate": {
                    "first":      "Primero",
                    "last":       "Ultimo",
                    "next":       "Siguiente",
                    "previous":   "Anterior"
                }
        },
        "bLengthChange": false,
        retrieve: true,
        "serverSide": false,
        bDestroy: true,
        scrollX: true,
        "bFilter": true	
    });
	
	$(".dt-button.buttons-csv").attr('class','btn btn-info btn-blue btn-sm bt');
	$(".dt-button.buttons-excel").attr('class','btn btn-primary btn-sm bt');
	$(".dt-button.buttons-pdf").attr('class', 'btn btn-danger btn-sm bt');
	$('input[type=search]').attr('class', 'form-control input-sm');
	
	$('.dt-buttons a.btn.btn-danger.btn-sm.bt').unbind().bind('click', function(e){
        e.preventDefault();
        console.log('enviando form');
       window.location.href = "/reportes/pdf/pendienteacomodo?nofooternoheader=true&cia=<?php echo $_SESSION['cve_cia'] ?>"; 
    });

    });

     $(".dt-button.buttons-csv").attr('class','btn btn-info btn-blue btn-sm bt');
        $(".dt-button.buttons-excel").attr('class','btn btn-primary btn-sm bt');
        $(".dt-button.buttons-pdf").attr('class', 'btn btn-danger btn-sm bt');
	*/
	$('.chosen-select').chosen();
</script>