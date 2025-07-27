<?php
    $almacenes = new \AlmacenP\AlmacenP();
    $model_almacen = $almacenes->getAll();
?>

<link href="/css/plugins/dataTables/jquery.dataTables.min.css" rel="stylesheet"/>
<link href="/css/plugins/dataTables/buttons.dataTables.min.css" rel="stylesheet"/>
<link href="/css/plugins/chosen/chosen.css" rel="stylesheet">
<script src="/js/jquery-2.1.1.js"></script>
<script src="/js/moment.js"></script>
<script src="/js/moment-with-locales.js"></script>
<script src="/js/bootstrap.min.js"></script>
<script src="/js/plugins/metisMenu/jquery.metisMenu.js"></script>
<script src="/js/plugins/slimscroll/jquery.slimscroll.min.js"></script>
<script src="/js/plugins/sweetalert/sweetalert.min.js"></script>
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
<script src="/js/plugins/datapicker/bootstrap-datepicker.js"></script>
<style>
    .bt{margin-right: 10px;}
    .btn-blue{
        background-color: blue !important;
        border-color: blue !important;
        color: white !important;
    }
</style>

<?php 
    $cliente_almacen_style = ""; $cve_proveedor = "";
    if($_SESSION['es_cliente'] == 2) 
    {
        $cliente_almacen_style = "style='display: none;'";
        $cve_proveedor = $_SESSION['cve_proveedor'];
    }
?>
    <input type="hidden" id="cve_proveedor" value="<?php echo $cve_proveedor; ?>">

<div class="wrapper wrapper-content  animated " id="list">
    <h3>Existencia Ubicación*</h3>
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-title">
                    <div class="row">
                        <div class="col-lg-3" <?php echo $cliente_almacen_style; ?>>
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
                                  <label for="contenedor">Pallet|Contenedor</label>
                                  <select name="contenedor" id="contenedor" class="chosen-select form-control">
                                      <option value="">Seleccione contendor</option>
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
                        <div class="col-lg-2">
                            <label for="email">&#160;&#160;</label>
                            <div class="form-group">
                                <button id="search" name="singlebutton" class="btn btn-primary">Buscar</button>
                            </div>
                        </div>
                        <div class="col-lg-2">
                            <label for="email">&#160;&#160;</label>
                            <div class="form-group">
                                <button id="boton_pdf" name="singlebutton" class="btn btn-primary">PDF</button>
                            </div>
                        </div>
                        <div class="col-lg-2">
                            <label for="email">&#160;&#160;</label>
                            <div class="form-group">
                                <a href="#" id="boton_excel" class="btn btn-primary">Excel</a>
                            </div>
                        </div>
                    </div>
                    <div class="row" style="margin-top:15px">
                        <div class="col-md-6">
                            <label>Total de Productos</label>
                            <input id="totalproductos" type="text" value ="" class="form-control" style="text-align: center" disabled><br>
                        </div>
                        <div class="col-md-6">
                            <label>Total de Unidades</label>
                            <input id="totalunidades" type="text" value ="" class="form-control" style="text-align: center" disabled><br>
                        </div>
                    </div>
                </div>
            </div> 
            <div class="col-lg-2">
                <div class="checkbox" id="chb_asignar">
                    <label for="btn-asignarTodo">
                      <input type="checkbox" name="asignarTodo" id="btn-asignarTodo">Seleccionar Todo
                    </label>
                </div>
            </div>
            <div class="ibox-content">
                <div class="table-responsive">
                    <table id="example"  class="table table-hover table-striped no-margin">
                        <thead>
                            <div><label id="codigo_BL">Codigo BL:</label></div>
                            <tr>
                                <th scope="col" style="width: 80px !important; min-width: 80px !important;">Acciones</th>
                                <th>Código BL*</th>
                                <th>Picking</th>
                                <th>QA</th>
                                <th>Pallet|Contenedor</th>
                                <th>License Plate (LP)</th>
                                <th>Clave</th>
                                <th>Descripción</th>
                                <th>Lote</th>
                                <th>Caducidad</th>
                                <th>N. Serie</th>

                                <th>Total</th>
                                <th>RP</th>
                                <th>Cant QA</th>
                                <th>Obsoletos</th>

                                <th>Pallet</th>
                                <th>Caja</th>
                                <th>Piezas | Kgs</th>
                                <th>Disponible</th>
                                <th>Almacén</th>
                                <th>Zona de Almacenaje</th>
                                <th>Proveedor</th>
                                <th>Fecha de Ingreso</th>
                                <th>Folio OC</th>
                                <th>Costo Promedio</th>
                                <th>Subtotal</th>
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
                                <button id="btn-asignar" onclick="asignar()" type="button" type="button" class="btn btn-m btn-primary" style="padding-right: 20px;">Enviar a QA | Cuarentena</button><br><br><br>
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
<!--Modal Enviar a QA | Cuarentena-->
<div class="modal fade" id="modal-asignar-motivo" role="dialog">
    <div class="vertical-alignment-helper">
        <div class="modal-dialog vertical-align-center modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4>Enviar QA | Cuarentena</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <label for="motivo" class="text-center">Motivos</label>
                            <select id="motivo_selector" class="form-control">
                                <option value="">Seleccione motivo</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cerrar</button>
                    <button onclick="asignarmotivo()" id="asignarmotivo" class="btn btn-primary pull-right" type="button">
                        <i class="fa fa-plus"></i>&nbsp;&nbsp;Guardar
                    </button>
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

<script>
    $(document).ready(function()
    {
        $(function() 
        {
            $('.chosen-select').chosen();
            $('.chosen-select-deselect').chosen({ allow_single_deselect: true });
        });
        /**Busca y selecciona el almacen predeterminado para el usuario. */ 
        function almacenPrede()
        {
            $.ajax({
                type: "POST",
                dataType: "json",
                url: '/api/almacenPredeterminado/index.php',
                data: {
                    idUser: '<?php echo $_SESSION["id_user"]?>',
                    action: 'search_almacen_pre'
                },
                beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                },
                success: function(data) {
                    if (data.success == true) 
                    {
                      var alma = document.getElementById('almacen');
                      setTimeout(function() {
                          alma.value = data.codigo.id;
                          $(alma).trigger("chosen:updated");
                          $('#almacen').trigger('change');
                          $('#search').trigger('click');
                      }, 1000);
                    }
                },
                error: function(res){
                    window.console.log(res);
                }
            });
        }
        almacenPrede();

//table();
/*
        $('#example').DataTable( {
            "processing": true,
            dom: 'Bfrtip',
            buttons:
            [
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
            "language": 
            {
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
*/

        $(".dt-button.buttons-excel").attr('class','btn btn-primary btn-sm bt');
        $(".dt-button.buttons-pdf").attr('class', 'btn btn-danger btn-sm bt');
        $(".dt-buttons a.btn.btn-primary.btn-sm.bt").unbind();
        $('.dt-buttons a.btn.btn-danger.btn-sm.bt').unbind();
    });
  
    function totales()
    {
        console.log("cve_proveedor = ", $("#cve_proveedor").val());
        $.ajax({
            url:"/api/reportes/lista/existenciaubica.php",
            type: "POST",
            data: {
                /*
                almacen: $("#almacen").val(),
                bl: $("#selectBL").val(),
                articulo: $("#articulo").val(),
                zona: $("#zona").val(),
                contenedor: $("#contenedor").val(),
                action:"totales"
                */
                almacen: $("#almacen").val(),
                proveedor: $("#proveedor").val(),
                cve_proveedor: $("#cve_proveedor").val(),
                bl: $("#selectBL").val(),
                articulo: $("#articulo").val(),
                zona: $("#zona").val(),
                search: true,
                contenedor: $("#contenedor").val(),
                action: "totales"
            },
            success: function(data){
                //console.log("SUCCESS: ", data.data[0]);
                console.log("productos = ", data.productos);
                console.log("unidades = ", data.unidades);
                //$("#totalproductos").val(data.data[0].productos);
                //$("#totalunidades").val(data.data[0].unidades);
                $("#totalproductos").val(data.productos);
                $("#totalunidades").val(data.unidades);
            }, error: function(data){
                console.log("ERROR: ", data);
            }
        })
    }

    function table(search = false)
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
                { "data": "acciones", "formatter": "imageFormat"},
                { "data": "codigo" },
                { "data": "tipo_ubicacion" },
                { "data": "QA" },
                { "data": "contenedor" },
                { "data": "LP" },
                { "data": "clave" },
                { "data": "descripcion" },
                { "data": "lote" },
                { "data": "caducidad" },
                { "data": "nserie" },
                { "data": "cantidad" , "className": "dt-right"},
                { "data": "RP" , "className": "dt-right"},
                { "data": "Prod_QA" , "className": "dt-right"},
                { "data": "Obsoletos" , "className": "dt-right"},
                { "data": "Pallet", "className": "dt-right" },
                { "data": "Caja", "className": "dt-right" },
                { "data": "Piezas", "className": "dt-right" },
                { "data": "Libre", "className": "dt-right" },
                { "data": "almacen" },
                { "data": "zona" },
                { "data": "proveedor" },
                { "data": "fecha_ingreso" },
                { "data": "folio_oc" },
                { "data": "costoPromedio", "className": "dt-right" },
                { "data": "subtotalPromedio", "className": "dt-right" }
            ],
            "ajax": {
                "url": "/api/reportes/lista/existenciaubica.php",
                "type": "GET",
                "data": {
                    "almacen" : $("#almacen").val(),
                    "proveedor": $("#proveedor").val(),
                    "cve_proveedor": $("#cve_proveedor").val(),
                    "bl" : $("#selectBL").val(),
                    "articulo" : $("#articulo").val(),
                    "zona" : $("#zona").val(),
                    "search": search,
                    "contenedor" : $("#contenedor").val(),
                    "action":"existenciaUbicacion",
                }/*, success: function(data)
                {
                    console.log("Datos = ", data);
                }*/
            }
        });
        totales();
    //}?
    }
    
        $(".dt-button.buttons-excel").attr('class','btn btn-primary btn-sm bt');
        $(".dt-button.buttons-pdf").attr('class', 'btn btn-danger btn-sm bt');
    
        $("#btn-asignarTodo").change(function(e) {
            var val = $(e.currentTarget).prop('checked');                
            $.each( $('.column-asignar'), function (index, item) {
                $(item).first().prop('checked', val) 
            });               
            $("#btn-asignar").show();
        });

        function traer_BL()
        {
          $.ajax({
                url: "/api/reportes/lista/existenciaubica.php",
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
        $("#boton_excel").click(function(e)
        {
            $(this).attr("href", "/existenciaubicacion/exportar_excel?almacen="+$("#almacen").val()+"&proveedor="+$("#proveedor").val()+"&zona="+$("#zona").val()+"&articulo="+$("#articulo").val()+"&bl="+$("#selectBL").val()+"&contenedor="+$("#contenedor").val()+"&cve_proveedor="+$("#cve_proveedor").val());

  /*
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
          form.setAttribute('action', '/api/reportes/lista/existenciaubica.php');
          form.setAttribute('method', 'post');
          form.setAttribute('target', '_blank');
          form.appendChild(input1);
          form.appendChild(input2);
          form.appendChild(input3);
          form.appendChild(input4);
          form.appendChild(input5);
          document.body.appendChild(form);
          form.submit();
*/
        });

        //PDF
        //$('.dt-buttons a.btn.btn-danger.btn-sm.bt').unbind().bind('click', function(e)
        $('#boton_pdf').click(function(e)
        {
            var title = 'Reporte de Existencia por Ubicación';
            var cia = <?php echo $_SESSION['cve_cia'] ?>;
            var content = '';
            var nombre_BL = '';
/*
            $.ajax({
                url: "/api/reportes/lista/existenciaubica.php",
                type: "POST",
                data: {
                    almacen : $("#almacen").val(),
                    action:"traer_BL"
                },
                success: function (data) {
                    nombre_BL = data;
                }
            });
*/
            //url: "/api/reportes/lista/existenciaubica.php",
            console.log("#almacen = ", $("#almacen").val());
            console.log("#proveedor = ", $("#proveedor").val());
            console.log("#zona = ", $("#zona").val());
            console.log("#articulo = ", $("#articulo").val());
            console.log("#selectBL = ", $("#selectBL").val());
            console.log("#contenedor = ", $("#contenedor").val());

            $.ajax({
                type: "POST",
                url: "/api/reportes/update/index.php",
                data: {
                    "action": "existenciaUbicacion",
                    "almacen" : $("#almacen").val(),
                    "proveedor": $("#proveedor").val(),
                    "cve_proveedor": $("#cve_proveedor").val(),
                    "zona" : $("#zona").val(),
                    "articulo" : $("#articulo").val(),
                    "bl": $("#selectBL").val(),
                    "contenedor": $("#contenedor").val()

                },
                success: function(data, textStatus, xhr)
                {
                    var data = JSON.parse(data).data;
                    console.log("Reporte Existencia = ", data);
                    if(data.length == 0)
                    {
                        swal("Error", "No hay datos Disponibles para estos filtros", "error");
                        return;
                    }
                    var content_wrapper = document.createElement('div');
                    var table = document.createElement('table');
                    table.style.width = "100%";
                    table.style.borderSpacing = "0";
                    table.style.borderCollapse = "collapse";
                    var thead = document.createElement('thead');
                    var tbody = document.createElement('tbody');
                    var head_content = '';
                    head_content += `
                        <tr><th colspan="9">${data[0][0]}</th></tr>
                        <tr><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th></tr>
                    `;

                    head_content += `
                        <tr>
                            <th style="border: 1px solid #ccc; width: 70px; font-size: 11px;">Codigo BL</th>
                            <th style="border: 1px solid #ccc; font-size: 11px;">Pallet|Cont</th>
                            <th style="border: 1px solid #ccc; font-size: 11px;">License Plate (LP)</th>
                            <th style="border: 1px solid #ccc; font-size: 11px;">Clave</th>
                            <th style="border: 1px solid #ccc; width: 300px; font-size: 11px;">Descripción</th>
                            <th style="border: 1px solid #ccc; font-size: 11px;">Lote</th>
                            <th style="border: 1px solid #ccc; font-size: 11px;">Caducidad</th>
                            <th style="border: 1px solid #ccc; font-size: 11px;">N. Serie</th>
                            <th style="border: 1px solid #ccc; font-size: 11px;">Disponible</th>
                            <th style="border: 1px solid #ccc; font-size: 11px;">RP</th>
                            <th style="border: 1px solid #ccc; font-size: 11px;">Prod_QA</th>
                            <th style="border: 1px solid #ccc; font-size: 11px;">Obsoletos</th>
                            <th style="border: 1px solid #ccc; width: 50px; font-size: 11px;">Pallet</th>
                            <th style="border: 1px solid #ccc; width: 50px; font-size: 11px;">Caja</th>
                            <th style="border: 1px solid #ccc; width: 50px; font-size: 11px;">Piezas</th>
                            <th style="border: 1px solid #ccc; text-align: center;width: 80px; font-size: 11px;">Stock Pzas</th>
                            <th style="border: 1px solid #ccc; font-size: 11px;width: 80px;">Folio OC</th>
                        </tr>
                    `;
                    var body_content = '';
                    var total = 0;
                    data.forEach(function(item, index)
                    {
                        var subtotal = item.costoPromedio*item.cantidad;
                        total = total + subtotal;

                            //**************************************************
                            //FUNCION PARA LAS COLUMNAS PALLET | CAJAS | PIEZAS
                            //**************************************************
                            //Archivos donde se encuentra esta función:
                            //api\embarques\lista\index.php
                            //api\reportes\lista\existenciaubica.php
                            //api\reportes\lista\concentradoexistencia.php
                            //app\template\page\reportes\existenciaubica.php
                            //\Application\Controllers\EmbarquesController.php
                            //\Application\Controllers\InventarioController.php
                            //**************************************************
                            //clave busqueda: COLPCP
                            //**************************************************
                            var Pallet = 0, Caja = 0, Piezas = 0, cantidad_restante = 0;
                            var valor1 = 0, valor2 = 0;

                            if(item.piezasxcajas > 0)
                               valor1 = item.cantidad/item.piezasxcajas;

                            if(item.cajasxpallets > 0)
                               valor1 = valor1/item.cajasxpallets;
                           else
                               valor1 = 0;

                            Pallet = parseInt(valor1);

                            valor2 = 0;
                            cantidad_restante = item.cantidad - (Pallet*item.piezasxcajas*item.cajasxpallets);
                            if(!Number.isInteger(valor1) || valor1 == 0)
                            {
                                if(item.piezasxcajas > 0)
                                   valor2 = (cantidad_restante/item.piezasxcajas);
                            }
                            Caja = parseInt(valor2);

                            Piezas = 0;
                            if(item.piezasxcajas == 1) 
                            {
                                valor2 = 0; 
                                Caja = cantidad_restante;
                                Piezas = 0;
                            }
                            else if(item.piezasxcajas == 0 || item.piezasxcajas == "")
                            {
                                if(item.piezasxcajas == "") item.piezasxcajas = 0;
                                valor2 = 0; 
                                Caja = 0;
                                Piezas = cantidad_restante;
                            }
                            cantidad_restante = cantidad_restante - (Caja*item.piezasxcajas);

                            if(!Number.isInteger(valor2))
                            {
                                if(item.granel == 'S')
                                    Piezas = cantidad_restante.toFixed(2);
                                else 
                                    Piezas = cantidad_restante;
                            }

                        //*****************************************************************************************
                        body_content += `
                            <tr>
                                <td style="border: 1px solid #ccc; width: 70px; font-size: 10px;">${item.codigo}</td>
                                <td style="border: 1px solid #ccc; font-size: 10px;">${item.contenedor}</td>
                                <td style="border: 1px solid #ccc; font-size: 10px;">${item.LP}</td>
                                <td style="border: 1px solid #ccc; font-size: 10px;">${item.clave}</td>
                                <td style="border: 1px solid #ccc; width: 300px; font-size: 10px;">${item.descripcion}</td>
                                <td style="border: 1px solid #ccc; font-size: 10px;">${item.lote}</td>
                                <td style="border: 1px solid #ccc; text-align: center; font-size: 10px;">${item.caducidad}</td>
                                <td style="border: 1px solid #ccc; font-size: 10px;">${item.nserie}</td>
                                <td style="border: 1px solid #ccc; font-size: 10px;">${item.Libre}</td>
                                <td style="border: 1px solid #ccc; font-size: 10px;">${item.RP}</td>
                                <td style="border: 1px solid #ccc; font-size: 10px;">${item.Prod_QA}</td>
                                <td style="border: 1px solid #ccc; font-size: 10px;">${item.Obsoletos}</td>
                                <td style="border: 1px solid #ccc; text-align: right; width: 50px; font-size: 10px;">${Pallet}</td>
                                <td style="border: 1px solid #ccc; text-align: right; width: 50px; font-size: 10px;">${Caja}</td>
                                <td style="border: 1px solid #ccc; text-align: right; width: 50px; font-size: 10px;">${Piezas}</td>
                                <td style="border: 1px solid #ccc; text-align: right; width: 80px; font-size: 10px;">${item.cantidad}</td>
                                <td style="border: 1px solid #ccc; text-align: center; font-size: 10px;width: 80px;">${item.folio_oc}</td>
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

                    var url = '<?php echo $_SERVER['HTTP_HOST']; ?>';
                    console.log("URL = ", url);
                    if(url.includes('avavex') && $("#proveedor").val() != "")
                    {
                        console.log("ESTOY EN AVAVEX");
                        title = $("#proveedor option:selected" ).text();
                        console.log("CIA = ", cia);
                    }

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
    //}


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

                var options_contenedores = $("#contenedor");
                options_contenedores.empty();
                options_contenedores.append(new Option("Seleccione Pallet|Contenedor", ""));

                var options_zonas = $("#zona");
                options_zonas.empty();
                options_zonas.append(new Option("Seleccione Zona", ""));

                var options_proveedores = $("#proveedor");
                options_proveedores.empty();

                if($("#cve_proveedor").val() == '')
                    options_proveedores.append(new Option("Seleccione Proveedor", ""));

                for (var i=0; i<data.articulos.length; i++)
                {
                    if(data.articulos[i].id_articulo && data.articulos[i].articulo)
                      options_articulos.append(new Option(data.articulos[i].id_articulo +" "+data.articulos[i].articulo, data.articulos[i].id_articulo));
                }
              
                    
                for (var i=0; i<data.contenedores.length; i++)
                {
                    if(data.contenedores[i].Cve_Contenedor)
                      options_contenedores.append(new Option(data.contenedores[i].Cve_Contenedor));
                }

                for (var i=0; i<data.zonas.length; i++)
                {
                  if(data.zonas[i].clave && data.zonas[i].descripcion)
                    options_zonas.append(new Option(data.zonas[i].clave +" "+data.zonas[i].descripcion, data.zonas[i].clave));
                }
                options_zonas.append(new Option("RTS", "RTS"));
                options_zonas.append(new Option("RTM", "RTM"));

                for (var i=0; i<data.proveedores.length; i++)
                {
                  if(data.proveedores[i].proveedor)
                  {
                    proveedor = data.proveedores[i].proveedor.split("-");
                    //data.proveedores[i].proveedor
                    if($("#cve_proveedor").val() != '' && proveedor[0] == $("#cve_proveedor").val())
                    {
                        options_proveedores.append(new Option(proveedor[1], proveedor[0]));
                        break;
                    }
                    else if($("#cve_proveedor").val() == '')
                        options_proveedores.append(new Option(proveedor[1], proveedor[0]));
                  }

                }                
                //$("#proveedor").val($("#cve_proveedor").val());


                $("#articulo").trigger("chosen:updated");
                $("#contenedor").trigger("chosen:updated");
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
            url: "/api/reportes/lista/existenciaubica.php",
            type: "GET",
            data: {
                almacen : $("#almacen").val(),
                articulo : $("#articulo").val(),
                contenedor : $("#contenedor").val(),
                zona: $("#zona").val(),
                action:"existenciaUbicacion"
            },
            success: function(data) {}
        });
    }

    function asignar()
    {
        var folios = [];
        var asignados = [];

        $.each( $('#example>tbody>tr'), function (index, item)
        {
            var bl = $(item).children().eq(1)[0].textContent;
            if($(item).children().eq(0).children().prop("checked"))
            {
                asignados.push([bl]);
            } 
        });
        console.log("Seleccionados", asignados);
        if(asignados.length <= 0)
        {
            swal("Error", "Seleccione al menos un articulo", "error");
            return;
        }
        
        $('#motivo_selector').empty();
        $modal0 = $("#modal-asignar-motivo");
        $modal0.modal('show');
        $("#motivo_selector").append(new Option("Seleccione Motivo", ""));
        $.ajax({
            url: "/api/reportes/lista/existenciaubica.php",
            type: "POST",
            dataType: "json",
            data: {
                action:"traermotivos"
            },
            success: function(data) 
            {
                console.log("DA", data);
                var j = 0;
                $.each(data.sql, function(key, value) {
                    $("#motivo_selector").append(new Option(""+value.descri+"",value.id));
                    j++;
                });
            }
        });
    }
    
    function asignarmotivo()
    { 
        var asignados = [];
        $('#modal-asignar-usuario').modal("hide");
        $.each( $('#example>tbody>tr'), function (index, item) 
        {
            var bl = $(item).children().eq(1)[0].textContent;
            var contenedor = $(item).children().eq(4)[0].textContent;
            var cve_articulo = $(item).children().eq(6)[0].textContent;
            var lote = $(item).children().eq(8)[0].textContent;
            if(lote=="--"){lote="";}
            var serie = $(item).children().eq(10)[0].textContent;
            var cantidad = $(item).children().eq(18)[0].textContent;
            var cant_cuarentena = $(item).children().eq(11)[0].textContent;
            if($(item).children().eq(0).children().prop("checked"))
            {
                asignados.push([bl,contenedor,cve_articulo,lote,serie,cantidad, cant_cuarentena]);
            } 
        });
        if ($("#motivo_selector").val() == "") 
        {
            swal("Error", "Seleccione un motivo", "error");
            return;
        }
        console.log("motivos: ", $('#motivo_selector').val(), "registros:", asignados, "almacen: ", $("#almacen").val(), "fecha: ", moment().format("YYYYMM"));
        $.ajax({
            url: "/api/reportes/lista/existenciaubica.php",
            type: "POST",
            dataType: "json",
            data: 
            { 
                action:"savemotivo",
                motivos: $('#motivo_selector').val(),
                registros: asignados,
                almacen : $("#almacen").val(),
                fecha: moment().format("YYYYMM"),
                id_usuario: '<?php echo $_SESSION["id_user"]?>',
                action:"savemotivo"
            },
            success: function(data) 
            {
                console.log("data.success = ", data.success, "data.sql = ", data.sql, "data.sql2 = ", data.sql2, "data.procesos = ", data.procesos);
               if (data.success == true) 
               {
                  swal("Exito", "Se movio correctamente!", "success");
                  $("#modal-asignar-motivo").modal('hide');
                  table();
               }
               else
               {
                  swal("Error", "Algo salio mal!!!", "error");
                  $("#modal-asignar-motivo").modal('hide');
               }
            }

        });
    }

    $( "#search" ).click(function() {
        console.log('A1');
        if ($("#almacen").val()==""){
            return;
        }
        table(true);
        calcularImporteExistencias();
        traer_BL();
    });

    $(document).ready(function(){
        setTimeout(function(){
        calcularImporteExistencias();
        },2000);

        //console.log("cve_proveedor = ",$("#cve_proveedor").val());
        //if($("#cve_proveedor").val() != '')
        //{
        //    $("#proveedor").val($("#cve_proveedor").val());
        //    $("#proveedor").trigger("chosen:updated");
        //    console.log("proveedor = ",$("#proveedor").val());
        //    //$('#search').trigger('click');
        //}

    });
</script>