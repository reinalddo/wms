<?php
$contenedorAlmacen = new \AlmacenP\AlmacenP();
$almacenes = $contenedorAlmacen->getAll();
?>
<link href="/css/plugins/datapicker/datepicker3.css" rel="stylesheet">
<link href="/css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">
<link href="/css/plugins/ladda/select2.css" rel="stylesheet"/>
<link href="/css/plugins/dataTables/jquery.dataTables.min.css" rel="stylesheet"/>
<link href="/css/plugins/dataTables/buttons.dataTables.min.css" rel="stylesheet"/>
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
 <h3>Reporte de Existencia Concentrado</h3>
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-title">
                    <div class="row">
                        <div class="col-lg-4">
    <?php
    $cliente_almacen_style = ""; $cve_proveedor = "";
    if($_SESSION['es_cliente'] == 2) 
    {
        $cliente_almacen_style = "style='display: none;'";
        $cve_proveedor = $_SESSION['cve_proveedor'];
    }
    ?>

    <input type="hidden" id="cve_proveedor" value="<?php echo $cve_proveedor; ?>">
                            <div class="form-group" <?php echo $cliente_almacen_style; ?>>
                                <label>Almacén</label>
                                <select class="form-control chosen-select" name="almacen" id="almacen" >
                                    <option value="">Seleccione</option>
                                    <?php if(isset($almacenes) && !empty($almacenes)): ?>
                                        <?php foreach($almacenes as $almacen): ?>
                                            <option value="<?php echo $almacen->clave ?>" data-id="<?php echo $almacen->id ?>"><?php echo $almacen->nombre ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <!--
                            <div class="input-group-btn">
                                <a href="#" onclick="buscar()">
                                    <button type="submit" class="btn btn-sm btn-primary" id="buscarP">
                                        Buscar
                                    </button>
                                </a>
                            </div>
                        -->
                            <div class="input-group-btn">
                                <a href="#">
                                    <button type="button" class="btn btn-sm btn-primary" id="boton_pdf">
                                        PDF
                                    </button>
                                </a>
                            </div>
                            <div class="input-group-btn">
                                <a href="#" id="boton_excel">
                                    <button type="button" class="btn btn-sm btn-primary">
                                        Excel
                                    </button>
                                </a>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label>Existencias</label>
                                <select class="form-control chosen-select" name="filtro_concentrado_select" id="filtro_concentrado_select" >
                                    <option value="">Todas</option>
                                    <option value="WHERE concentrado.existencia > 0">Con Existencias</option>
                                    <option value="WHERE concentrado.existencia = 0">Sin Existencias</option>
                                </select>
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
                <div class="ibox-content">
                    <div class="table-responsive">
                        <table id="concentrado"  class="table table-hover table-striped no-margin">
                            <thead>
                                <tr>
                                    <th style="white-space: nowrap;">Almacén</th>
                                    <th style="white-space: nowrap;">Clave</th>
                                    <th style="white-space: nowrap;">Grupo</th>
                                    <th style="white-space: nowrap;">Descripción</th>
                                    <th style="white-space: nowrap;">Existencia</th>
                                    <th style="white-space: nowrap;">Pallet</th>
                                    <th style="white-space: nowrap;">Caja</th>
                                    <th style="white-space: nowrap;">Piezas | Kgs</th>
                                    <th style="white-space: nowrap;">Prod OC</th>
                                    <th style="white-space: nowrap;">Prod RTM</th>
                                    <th style="white-space: nowrap;">Reserva Picking</th>
                                    <th style="white-space: nowrap;">Prod en QA</th>
                                    <th style="white-space: nowrap;">Obsoletos</th>
                                    <th style="white-space: nowrap;">RTS</th>
                                    <th style="white-space: nowrap;">Prod | Kitting</th>
                                    <th style="white-space: nowrap;">Proveedor</th>
                                </tr>
                            </thead>
                            <tfoot>
                                <!--
                                    <tr style="display: none;">
                                        <th colspan="3" style="text-align:right">Total de Unidades:</th>
                                        <th id="resultado_total"></th>
                                    </tr>
                                -->
                            </tfoot>
                        </table>
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

<script>
    
    var totalproductos_inicial = 0;
    var totalunidades_inicial = 0;
    var firstTime = false;
    var firstTimeAlmacenPrede = false;
    var tabla_inicial = $("#concentrado");

    function buscar() 
    {
        console.log("buscar");
        table();
        console.log("buscar2");
        totales();
    }

    $("#almacen, #filtro_concentrado_select").change(function()
    {
        if(firstTimeAlmacenPrede || (firstTimeAlmacenPrede && $("#filtro_concentrado_select").val()) )
            buscar();
        else
            firstTimeAlmacenPrede = true;
    });
  
    function totales()
    {
        console.log("almacen totales = ", $("#almacen").val());
        console.log("cve_proveedor totales = ", $("#cve_proveedor").val());
        $.ajax({
            url:"/api/reportes/lista/concentradoexistencia.php",
            type: "GET",
            data: {
                almacen: $("#almacen").val(),
                cve_proveedor : $("#cve_proveedor").val(),
                filtro_concentrado : $("#filtro_concentrado_select").val()
                //action:"totales"
            },
            success: function(data){
                console.log("TOTALES", data);
                $("#totalproductos").val(data.productos);
                $("#totalunidades").val(data.unidades);

                //if(!firstTime)
                //{
                    totalproductos_inicial = data.data[0].productos;
                    totalunidades_inicial = data.data[0].unidades;
                    //console.log("totalproductos_inicial = "+totalproductos_inicial);
                    //console.log("totalunidades_inicial = "+totalunidades_inicial);
                //}
            }
        });

    }
  
    function table()
    {
        console.log("almacen = ", $("#almacen").val());
        console.log("cve_proveedor = ", $("#cve_proveedor").val());
        $('#myTable').DataTable().destroy();
        $('#concentrado').DataTable({
            "processing": true,
            //"dom": 'Bfrtip',
            /*
            buttons: [
                {extend: 'csvHtml5',title: 'Entradas'},
                {extend: 'excelHtml5',title: 'Entradas'},
    			{extend: 'pdfHtml5',title: 'Concentrado de Existencias'}
			 ],
             */
            "responsive": true, 
            "serverSide": true,
            "pagingType": "full_numbers",
		    "language": 
            {
                "lengthMenu": "Mostrando _MENU_ registros",
                "zeroRecords": "Sin Registros - :(!!",
                "info": "Pagina _PAGE_ de _PAGES_",
                "infoEmpty": "No Existen Registros",
                "infoFiltered": "(Filtrando de un total de _MAX_ registros)",
                "sSearch": "Filtrar:",
                //"sProcessing":   	"Cargando...",
                "sProcessing":      "",
                "oPaginate": {
                    "sNext": "Sig",
                    "sPrevious": "Ant",
                    "sLast": "Ultimo",
                    "sFirst":    	"Primero",
                }
            },
            "bDestroy": true,
            "columns": [
                { "data": "Nombre_Almacen"},
                { "data": "articulo" },
                { "data": "grupo" },
                { "data": "nombre" },
                { "data": "existencia", "className": "dt-right" },
                { "data": "Pallet", "className": "dt-right" },
                { "data": "Caja", "className": "dt-right" },
                { "data": "Piezas", "className": "dt-right" },
                { "data": "Prod_OC", "className": "dt-right" },
                { "data": "Prod_RTM", "className": "dt-right" },
                { "data": "Res_Pick", "className": "dt-right" },
                { "data": "Prod_QA", "className": "dt-right" },
                { "data": "Obsoletos", "className": "dt-right" },
                { "data": "RTS", "className": "dt-right" },
                { "data": "Prod_kit", "className": "dt-right" },
                { "data": "proveedor" }
            ],
            "columnDefs": [
                {"className": "dt-right", "targets": [3]}
            ],
            "ajax": {
                "url": "/api/reportes/lista/concentradoexistencia.php",
                "type": "GET",
                "dataType": "json",
                "data": {
                    "almacen" : $("#almacen").val(),
                    "cve_proveedor" : $("#cve_proveedor").val(),
                    "filtro_concentrado" : $("#filtro_concentrado_select").val()
                }
            },

            "footerCallback": function ( row, data, start, end, display ) {

            var api = this.api(), data;
 
            // Remove the formatting to get integer data for summation
            var intVal = function ( i ) {
                return typeof i === 'string' ?
                    i.replace(/[\$,]/g, '')*1 :
                    typeof i === 'number' ?
                        i : 0;
            };
 
            // Total over all pages
            var total = api
                .column( 4 )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );
 
            // Total over this page
            //var pageTotal = api
            //    .column( 3, { page: 'current'} )
            //    .data()
            //    .reduce( function (a, b) {
            //        return intVal(a) + intVal(b);
            //    }, 0 );
 
            // Update footer
                $( api.column( 4 ).footer() ).html(
                    //pageTotal.toFixed(2) +' ( '+ total.toFixed(2) +' total)'
                    total.toFixed(2)
                );

                var filteredData = $('#concentrado_filter input').val();
                if(totalproductos_inicial == $('#concentrado').DataTable().data().length || filteredData == "")
                {
                    console.log("MOD 1", totalproductos_inicial, totalunidades_inicial);
                    $("#totalproductos").val(totalproductos_inicial);
                    $("#totalunidades").val(totalunidades_inicial);
                }
                else
                {

                    if(!firstTime)
                    {
                        console.log("MOD 2", totalproductos_inicial, totalunidades_inicial);
                        $("#totalproductos").val(totalproductos_inicial);
                        $("#totalunidades").val(totalunidades_inicial);
                        firstTime = true;
                    }
                    else
                    {
                        console.log("MOD 3");
                        $("#totalproductos").val($('#concentrado').DataTable().data().length);
                        $("#totalunidades").val(total.toFixed(2));
                    }

                    //console.log("totalproductos_inicial2 = "+totalproductos_inicial);
                    //console.log("valor productos 2 = "+$('#concentrado').DataTable().data().length);

                    //console.log("totalunidades_inicial2 = "+totalunidades_inicial);
                    //console.log("valor unidades 2 = "+total.toFixed(2));

                }

            }
        });



        totales();

    }
  
    $(document).ready(function(){

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
                    if (data.success == true) {
                        document.getElementById('almacen').value = data.codigo.clave;
                        setTimeout(function() {
                            $('#almacen').trigger('change');
                            buscar();
                        }, 1000);
                    }
                },
                error: function(res){
                    window.console.log(res);
                }
            });
        }
        almacenPrede();
       
/*	
        $(".dt-button.buttons-csv").attr('class','btn btn-info btn-blue btn-sm bt');
        //$(".dt-button.buttons-excel").attr('class','btn btn-primary btn-sm bt');
        //$(".dt-button.buttons-pdf").attr('class', 'btn btn-danger btn-sm bt');
        $(".dt-button.buttons-excel").attr('class','btn btn-primary btn-sm bt');
        $(".dt-button.buttons-pdf").attr('class', 'btn btn-danger btn-sm bt');

        $(".dt-buttons a.btn.btn-primary.btn-sm.bt").unbind();
        $('.dt-buttons a.btn.btn-danger.btn-sm.bt').unbind();
*/
        $(".dt-button.buttons-excel").attr('class','btn btn-primary btn-sm bt');
        $(".dt-button.buttons-pdf").attr('class', 'btn btn-danger btn-sm bt');
        $(".dt-buttons a.btn.btn-primary.btn-sm.bt").unbind();
        $('.dt-buttons a.btn.btn-danger.btn-sm.bt').unbind();
        $('input[type=search]').attr('class', 'form-control input-sm');	   

        //excel

       $("#boton_excel").click(function(e){

        $(this).attr("href", "/concentrado/exportar_excel?almacen="+$("#almacen").val()+"&cve_proveedor="+$("#cve_proveedor").val()+"&filtro_concentrado="+$("#filtro_concentrado_select").val());
/*
          var form = document.createElement("form"),
              input1 = document.createElement("input"),
              input2 = document.createElement("input"),
              input5 = document.createElement("input");
          input1.setAttribute('name', 'nofooternoheader');
          input1.setAttribute('value', 'true');
          input2.setAttribute('name', 'almacen');
          input2.setAttribute('value', document.getElementById("almacen").value);
          input5.setAttribute('name', 'action');
          input5.setAttribute('value', 'concentrado_excel');
          form.setAttribute('action', '/api/reportes/lista/concentradoexistencia.php');
          form.setAttribute('method', 'post');
          form.setAttribute('target', '_blank');
          form.appendChild(input1);
          form.appendChild(input2);
          form.appendChild(input5);
          document.body.appendChild(form);
          form.submit();
*/
        });

        //PDF
        //$('.dt-buttons a.btn.btn-danger.btn-sm.bt').unbind().bind('click', function(e)
        $('#boton_pdf').click( function(e)
        {
                var title = $('#list h3:first-of-type').text();
                var cia = <?php echo $_SESSION['cve_cia'] ?>;
                var content = '';
                console.log("almacen boton_pdf= ", $("#almacen").val());
                console.log("cve_proveedor = ", $("#cve_proveedor").val());

                $.ajax({
                    url: "/api/reportes/lista/concentradoexistencia.php",
                    type: "GET",
                    data: {
                        almacen: $("#almacen").val(),
                        cve_proveedor : $("#cve_proveedor").val(),
                        filtro_concentrado : $("#filtro_concentrado_select").val()
                        //action:"concentrado_pdf"
                        
                    },
                    success: function(data, textStatus, xhr){
                        var content_wrapper = document.createElement('div');
                        var table = document.createElement('table');
                        table.style.width = "100%";
                        table.style.borderSpacing = "0";
                        table.style.borderCollapse = "collapse";
                        var thead = document.createElement('thead');
                        var tbody = document.createElement('tbody');
                        var tfoot = document.createElement('tfoot');
                        var head_content = '<tr><th style="border: 1px solid #ccc">Proveedor</th>'+
                                               '<th style="border: 1px solid #ccc">Clave</th>'+
                                               '<th style="border: 1px solid #ccc">Descripción</th>    '+
                                               '<th style="border: 1px solid #ccc">Pallet</th>'+
                                               '<th style="border: 1px solid #ccc">Caja</th>'+
                                               '<th style="border: 1px solid #ccc">Piezas | Kgs</th>'+
                                               '<th style="border: 1px solid #ccc">Existencia</th>'+
                                            '</tr>';
                        var body_content = '';
                        //var data = JSON.parse(data);
                        var total_cant = 0;
                        console.log("Data PDF = ", data);
                        for(var i = 0; i < data.data.length; i++)
                        {
                            body_content += '<tr>'+
                                        '<td style="border: 1px solid #ccc">'+data.data[i].proveedor+'</td> '+
                                        '<td style="border: 1px solid #ccc">'+data.data[i].articulo+'</td>'+
                                        '<td style="border: 1px solid #ccc">'+data.data[i].nombre+'</td>'+
                                        '<td style="border: 1px solid #ccc">'+data.data[i].Pallet+'</td>'+
                                        '<td style="border: 1px solid #ccc">'+data.data[i].Caja+'</td>'+
                                        '<td style="border: 1px solid #ccc">'+data.data[i].Piezas+'</td>'+
                                        '<td style="border: 1px solid #ccc">'+parseFloat(data.data[i].existencia).toFixed(2)+'</td>'+
                                    '</tr>  ';                                                         
                                    total_cant += parseFloat(data.data[i].existencia);
                        }

                        var foot_content = '<tr><td colspan="3" style="text-align: right; font-weight: bold;">TOTAL</td><td>'+parseFloat(total_cant).toFixed(2)+'</td></tr>';

                        tbody.innerHTML = body_content;
                        thead.innerHTML = head_content;
                        tfoot.innerHTML = foot_content;
                        table.appendChild(thead);
                        table.appendChild(tfoot);
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
        table();

    //*******************************************************************************************
    //Proceso para mantener los datos cuando se filtra
    //*******************************************************************************************

//    var total = 0;
//    var table = $('#concentrado').DataTable();

    //console.log($("#concentrado_wrapper #concentrado tbody tr .dt-right").text());

    //var table = $("#concentrado").DataTable().rows( { filter : 'applied'} ).nodes();
    //total = table.column( 3 ).data().sum();

//    console.log("Total = "+total);

    //console.log($("#concentrado_wrapper #concentrado_filter").find('input')[0].children[0].outerHTML);
//    console.log($("#concentrado_wrapper #concentrado_filter").hasClass("dataTables_filter"));

//    var elem = $("#concentrado_wrapper #concentrado_filter input[type=search]");

//    $("#concentrado_wrapper tbody > tr > td").each(function(){
//        console.log("OK");
//    });

    //*******************************************************************************************
    $.fn.dataTable.ext.errMode = 'throw';
    });

</script>