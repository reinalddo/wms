<?php
$contenedorAlmacen = new \AlmacenP\AlmacenP();
$almacenes = $contenedorAlmacen->getAll();
?>
<link href="/css/plugins/datapicker/datepicker3.css" rel="stylesheet">
<link href="/css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">
<link href="/css/plugins/ladda/select2.css" rel="stylesheet"/>
<link href="/css/plugins/chosen/chosen.css" rel="stylesheet">
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
<input type="hidden" id="almacenid" value="">
<div class="wrapper wrapper-content  animated " id="list">
 <h3>Reporte de Existencia Concentrado</h3>
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-title">
                    <div class="row">
                        <div class="col-lg-3">
    <?php
    $cliente_almacen_style = ""; $cve_proveedor = "";
    if($_SESSION['es_cliente'] == 2) 
    {
        $cliente_almacen_style = "style='display: none;'";
        $cve_proveedor = $_SESSION['cve_proveedor'];
    }

    if(isset($_SESSION['id_proveedor']))
    {
        $cve_proveedor = $_SESSION['id_proveedor'];
    }

    ?>

    <input type="hidden" id="cve_proveedor" value="<?php echo $cve_proveedor; ?>">
                            <div class="form-group" <?php echo $cliente_almacen_style; ?>>
                                <label>Almacén</label>
                                <select class="form-control chosen-select" name="almacen" id="almacen" >
                                    <option value="">Seleccione</option>
                                    <?php if(isset($almacenes) && !empty($almacenes)): ?>
                                        <?php foreach($almacenes as $almacen): ?>
                                            <option value="<?php echo $almacen->clave ?>" data-id="<?php echo $almacen->id ?>"><?php echo "(".$almacen->clave.") - ".$almacen->nombre ?></option>
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
                        </div>

                        <div class="col-lg-3">
                            <div class="form-group articulos1">
                                <label for="articulo">Articulos</label>
                                <select name="articulo" id="articulo" class="chosen-select form-control">
                                    <option value="">Seleccione Articulo</option>
                                </select>
                            </div>
                            <div class="form-group articulos2" style="display: none;">
                                <label for="articulo2">Articulos</label>
                                <select name="articulo2" id="articulo2" class="chosen-select form-control">
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
                                <label for="grupo">Grupo</label>
                                <select name="grupo" id="grupo" class="chosen-select form-control">
                                    <option value="">Seleccione Grupo</option>
                                </select>
                            </div>
                        </div>

                    </div>
                    <div class="row">
                        <div class="col-lg-3">
                            <div class="form-group">
                                <label>Existencias</label>
                                <select class="form-control chosen-select" name="filtro_concentrado_select" id="filtro_concentrado_select" >
                                    <option value="">Todas</option>
                                    <option value="WHERE concentrado.existencia > 0" selected>Con Existencias</option>
                                    <option value="WHERE concentrado.existencia = 0">Sin Existencias</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-3" style="margin-top: 24px;">
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
                            <div class="input-group-btn">
                                <button type="button" class="btn btn-sm btn-primary" onclick="table();">
                                    Buscar
                                </button>
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
                    <br>
                            <div class="form-group permiso_registrar" style="text-align: center;">
                                <div class="checkbox">
                                    <label for="check_almacen" style="font-weight: 600;font-size: 14px; -moz-user-select: none; -webkit-user-select: none;-ms-user-select: none; user-select: none;">
                                    <input type="checkbox" name="check_almacen" id="check_almacen" value="0">No Filtrar por Almacén</label>
                                </div>
                            </div>
                    <br>
                </div>
                </div>
                <?php 
                /*
                ?>
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
                <?php 
                */
                ?>

                        <div class="ibox-content">
                            <div class="jqGrid_wrapper">
                                <table id="grid-table"></table>
                                <div id="grid-pager"></div>
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
<script src="/js/plugins/chosen/chosen.jquery.js"></script>
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
      //  var data = {
      //              almacen: $("#almacen").val(),
      //              cve_proveedor : $("#cve_proveedor").val(),
      //              filtro_concentrado : $("#filtro_concentrado_select").val()
      //          };
      //$("#grid-table").jqGrid('clearGridData')
      //.jqGrid('setGridParam', {postData: data, datatype: 'json', page : 1})
      //.trigger('reloadGrid',[{current:true}]);

        table();
        console.log("buscar2");
        //totales();
    }

    $("#almacen, #filtro_concentrado_select").change(function()
    {
        if(firstTimeAlmacenPrede || (firstTimeAlmacenPrede && $("#filtro_concentrado_select").val()) )
            buscar();
        else
            firstTimeAlmacenPrede = true;
    });
  
      function setGridWidth(grid_selector){
        
        $(window).on('resize.jqGrid', function () {
            $(grid_selector).jqGrid( 'setGridWidth', $(".page-content").width() - 60 );
        });

        //resize on sidebar collapse/expand
        var parent_column = $(grid_selector).closest('[class*="col-"]');
        $(document).on('settings.ace.jqGrid' , function(ev, event_name, collapsed) {
            if( event_name === 'sidebar_collapsed' || event_name === 'main_container_fixed' ) {
                //setTimeout is for webkit only to give time for DOM changes and then redraw!!!
                setTimeout(function() {
                    $(grid_selector).jqGrid( 'setGridWidth', parent_column.width() );
                }, 0);
            }
        });
    }

    function totales()
    {
        console.log("almacen totales = ", $("#almacen").val());
        console.log("cve_proveedor totales = ", $("#cve_proveedor").val());


        var check_almacen = 0;
        if($("#check_almacen").is(':checked'))
            check_almacen = 1;

        $.ajax({
            url:"/api/reportes/lista/concentradoexistencia.php",
            type: "GET",
            data: {
                almacen: $("#almacen").val(),
                cve_proveedor : $("#cve_proveedor").val(),
                filtro_concentrado : $("#filtro_concentrado_select").val(),
                check_almacen: check_almacen
                //action:"totales"
            },
            success: function(data){
                console.log("TOTALES", data);
                $("#totalproductos").val(data.productos);
                $("#totalunidades").val(data.unidades);

                //if(!firstTime)
                //{
                    totalproductos_inicial = data.productos;
                    totalunidades_inicial = data.unidades;
                    //console.log("totalproductos_inicial = "+totalproductos_inicial);
                    //console.log("totalunidades_inicial = "+totalunidades_inicial);
                //}
            }
        });

    }
  
    function table()
    {
/*
        console.log("almacen = ", $("#almacen").val());
        console.log("cve_proveedor = ", $("#cve_proveedor").val());
        $('#myTable').DataTable().destroy();
        $('#concentrado').DataTable({
            "processing": true,
            //"dom": 'Bfrtip',
            //buttons: [
                //{extend: 'csvHtml5',title: 'Entradas'},
                //{extend: 'excelHtml5',title: 'Entradas'},
    			//{extend: 'pdfHtml5',title: 'Concentrado de Existencias'}
			 //],
             
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
*/

//**************************************************************************************************
        var grid_selector = "#grid-table";
        var pager_selector = "#grid-pager";

       setGridWidth(grid_selector);
       console.log("almacen = ", $("#almacen").val(), ", articulo = ", $("#articulo").val(), ", proveedor = ", $("#proveedor").val(), ", grupo = ", $("#grupo").val(), ", cve_proveedor = ", $("#cve_proveedor").val());

        var check_almacen = 0;
        if($("#check_almacen").is(':checked'))
            check_almacen = 1;

        $(grid_selector).jqGrid({
            
            url:'/api/reportes/lista/concentradoexistencia.php',
            datatype: "json",
            shrinkToFit: false,
            height:'auto',
            cache: false,
            postData: {
                    almacen : $("#almacen").val(),
                    cve_proveedor : $("#cve_proveedor").val(),
                    filtro_concentrado : $("#filtro_concentrado_select").val(),
                    proveedor: $("#proveedor").val(),
                    grupo: $("#grupo").val(),
                    check_almacen: check_almacen,
                    articulo : $("#articulo").val(),
                    articulo2 : $("#articulo2").val()
            },

            mtype: 'GET', //lilo
            colNames:['Clave','Grupo','Descripción', 'Existencia', 'Pallet','Caja','Piezas | Kgs','Prod OC','Prod RTM','Reserva Picking','Prod en QA','Obsoletos','RTS','Prod | Kitting'/*,'Proveedor'*/, 'Almacén'],
            colModel:[
                {name:'articulo',index:'articulo', width:150, editable:false, sortable:false},
                {name:'grupo',index:'grupo', width:150, fixed:true, sortable:false, resize:false},
                {name:'nombre',index:'nombre', width:300, fixed:true, sortable:false, resize:false},
                {name:'existencia',index:'existencia',width:100, editable:false, align:"right", sorttype: "text"},
                {name:'Pallet',index:'Pallet',width:100,editable:false, align:"right", hidden: true},
                {name:'Caja',index:'Caja',width:100,editable:false, align:"right", hidden: true},
                {name:'Piezas',index:'Piezas',width:100,editable:false, align:"right", hidden: true},
                {name:'Prod_OC',index:'Prod_OC', width:100, editable:false, sorttype: "text", align:"right", hidden: true},
                {name:'Prod_RTM',index:'Prod_RTM', width:100, editable:false, align:"right", hidden: true},
                {name:'Res_Pick',index:'Res_Pick', width:100, editable:false, align:"right", sorttype: "int", hidden: true},
                {name:'Prod_QA',index:'Prod_QA', width:100, editable:false, align:"right", sorttype: "int", hidden: true},
                {name:'Obsoletos',index:'Obsoletos', width:100, editable:false, sortable:false, align:"right", hidden: true},
                {name:'RTS',index:'RTS', width:100, editable:false, sortable:false, align:"right", hidden: true},
                {name:'Prod_kit',index:'Prod_kit', width:100, editable:false, sortable:false, align:"right", hidden: true},
                //{name:'proveedor',index:'proveedor', width:200, editable:false, sortable:false},
                {name:'Nombre_Almacen',index:'Nombre_Almacen', width:200, editable:false, sortable:false}
            ],
            loadonce: false,
            rowNum:30,
            rowList:[30,40,50],
            pager: "#grid-pager",
            //sortname: 'existencia',
            //sortorder: "desc",
            viewrecords: true,
            gridComplete: function(data){
                //$("#grid-table").setGridParam({datatype: 'local'});
                //console.log(data);
            },
            loadComplete:function(data){
              //var datos = data.rows;
              //for(var i=0; i<datos.length; i++)
              //{
              //  console.log("DEmo",datos[i].cell[1]);
              //  var id_ubicacion = datos[i].cell[1];
              //  traer_totales(id_ubicacion);
              //}
              console.log("*********************");
              console.log(data);

                //console.log("productos = ", data.productos);
                //console.log("unidades = ", data.unidades);
                //$("#totalproductos").val(data.data[0].productos);
                //$("#totalunidades").val(data.data[0].unidades);
                $("#totalproductos").val(data.productos);
                $("#totalunidades").val(data.unidades);
                $("#almacen, #articulo, #articulo2, #proveedor, #grupo, #filtro_concentrado_select").chosen();

              //$("#num_ubicaciones").text(data.records);
             // $("#codigo_BL_name").html("BL: "+data.bl);
            }, loadError: function(data)
            {
                console.log("ERROR = ", data);
            }
        });

        // Setup buttons
        $("#grid-table").jqGrid('navGrid', '#grid-pager',
                                {edit: false, add: false, del: false, search: false,reloadGridOptions: { fromServer: true }},
                                {height: 200, reloadAfterSubmit: true}
                               );

        $(window).triggerHandler('resize.jqGrid');//trigger window resize to make the grid get the correct size
//**************************************************************************************************


        //totales();

    } 
  
    $(document).ready(function(){


       function almacenPrede()
       { 
            console.log("idUser:", <?php echo $_SESSION["id_user"]; ?>);
            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    idUser: '<?php echo $_SESSION["id_user"]; ?>',
                    action: 'search_almacen_pre'
                },
                beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }},
                url: '/api/almacenPredeterminado/index.php',
                success: function(data) {
                    if (data.success == true) {
                        console.log("ALMACEN = ", data);
                        document.getElementById('almacen').value = data.codigo.clave;

                        var almacen_val = data.codigo.clave;
                        $("#almacenid").val(data.codigo.id);
                        
                        //$('#almacen').trigger('change');
                        generar_selects();
                        console.log("almacen_val = ", almacen_val);
                        table();
                        $('#almacen').trigger("chosen:updated");
                        $("#articulo, #articulo2").trigger("chosen:updated");
                        $("#proveedor").trigger("chosen:updated");
                        $("#grupo").trigger("chosen:updated");

                        //setTimeout(function() {
                        //   //buscar();
                        //   //table();
                        //}, 1000);
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

        var check_almacen = 0;
        if($("#check_almacen").is(':checked'))
            check_almacen = 1;

        $(this).attr("href", "/concentrado/exportar_excel?almacen="+$("#almacen").val()+"&cve_proveedor="+$("#cve_proveedor").val()+"&filtro_concentrado="+$("#filtro_concentrado_select").val()+"&proveedor="+$("#proveedor").val()+"&grupo="+$("#grupo").val()+"&articulo="+$("#articulo").val()+"&articulo2="+$("#articulo2").val()+"&check_almacen="+check_almacen);
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

        var check_almacen = 0;
        if($("#check_almacen").is(':checked'))
            check_almacen = 1;

                $.ajax({
                    url: "/api/reportes/lista/concentradoexistencia.php",
                    type: "GET",
                    data: {
                        almacen : $("#almacen").val(),
                        cve_proveedor : $("#cve_proveedor").val(),
                        filtro_concentrado : $("#filtro_concentrado_select").val(),
                        proveedor: $("#proveedor").val(),
                        grupo: $("#grupo").val(),
                        articulo : $("#articulo").val(),
                        articulo2 : $("#articulo2").val(),
                        check_almacen: check_almacen,
                        boton_pdf: 1
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
                        //<th style="border: 1px solid #ccc;">Proveedor</th>'+
                        var head_content = '<tr><th style="border: 1px solid #ccc;">Clave</th>'+
                                               '<th style="border: 1px solid #ccc;">Descripción</th>    '+
                                               '<th style="border: 1px solid #ccc;">Pallet</th>'+
                                               '<th style="border: 1px solid #ccc;">Caja</th>'+
                                               '<th style="border: 1px solid #ccc;">Piezas | Kgs</th>'+
                                               '<th style="border: 1px solid #ccc;">Existencia</th>'+
                                            '</tr>';

                        if(check_almacen == 1)
                            head_content = '<tr><th style="border: 1px solid #ccc;">Clave</th>'+
                                               '<th style="border: 1px solid #ccc;">Descripción</th>    '+
                                               '<th style="border: 1px solid #ccc;">Pallet</th>'+
                                               '<th style="border: 1px solid #ccc;">Caja</th>'+
                                               '<th style="border: 1px solid #ccc;">Piezas | Kgs</th>'+
                                               '<th style="border: 1px solid #ccc;">Existencia</th>'+
                                               '<th style="border: 1px solid #ccc;">Almacén</th>'+
                                            '</tr>';
                        var body_content = '';
                        //var data = JSON.parse(data);
                        var total_cant = 0;
                        //console.log("Data PDF = ", data, "Data length = ", data.data.length);
                        for(var i = 0; i < data.data.length; i++)
                        {
                            //'<td style="border: 1px solid #ccc;">'+data.data[i].proveedor+'</td> '+
                            if(check_almacen == 0)
                            body_content += '<tr>'+
                                        '<td style="border: 1px solid #ccc;">'+data.data[i].cve_articulo+'</td>'+
                                        '<td style="border: 1px solid #ccc;">'+data.data[i].articulo+'</td>'+
                                        '<td style="border: 1px solid #ccc;text-align: right;">'+data.data[i].Pallet+'</td>'+
                                        '<td style="border: 1px solid #ccc;text-align: right;">'+data.data[i].Caja+'</td>'+
                                        '<td style="border: 1px solid #ccc;text-align: right;">'+data.data[i].Piezas+'</td>'+
                                        '<td style="border: 1px solid #ccc;text-align: right;">'+parseFloat(data.data[i].existencia).toFixed(2)+'</td>'+
                                    '</tr>  ';    
                            else                                                                                         
                            body_content += '<tr>'+
                                        '<td style="border: 1px solid #ccc;">'+data.data[i].cve_articulo+'</td>'+
                                        '<td style="border: 1px solid #ccc;">'+data.data[i].articulo+'</td>'+
                                        '<td style="border: 1px solid #ccc;text-align: right;">'+data.data[i].Pallet+'</td>'+
                                        '<td style="border: 1px solid #ccc;text-align: right;">'+data.data[i].Caja+'</td>'+
                                        '<td style="border: 1px solid #ccc;text-align: right;">'+data.data[i].Piezas+'</td>'+
                                        '<td style="border: 1px solid #ccc;text-align: right;">'+parseFloat(data.data[i].existencia).toFixed(2)+'</td>'+
                                        '<td style="border: 1px solid #ccc;text-align: right;">'+data.data[i].Nombre_Almacen+'</td>'+
                                    '</tr>  ';                                                         

                                    total_cant += parseFloat(data.data[i].existencia);
                        }

                        var foot_content = '<tr><td colspan="6" style="text-align: right; font-weight: bold;">TOTAL</td><td style="text-align: right;">'+parseFloat(total_cant).toFixed(2)+'</td></tr>';

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


    //$('#almacen').change(function(e) 
    function generar_selects()
    {
        var almacen= $("#almacenid").val();

        var check_almacen = 0;
        if($("#check_almacen").is(':checked'))
            check_almacen = 1;


        console.log("cve_almac :",  almacen);
        console.log("clave_almacen: ", $("#almacen").val());
        console.log("check_almacen: ", check_almacen);

        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                cve_almac : almacen,
                clave_almacen: $("#almacen").val(),
                check_almacen: check_almacen,
                action : "getArticulosYZonasAlmacenConSinExistencia"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/ubicaciones/update/index.php',
            success: function(data) {

                console.log(data);
                var options_articulos = $("#articulo");
                options_articulos.empty();
                options_articulos.append(new Option("Seleccione Artículo", ""));

                var options_articulos2 = $("#articulo2");
                options_articulos2.empty();
                options_articulos2.append(new Option("Seleccione Artículo", ""));

                var options_proveedores = $("#proveedor");
                options_proveedores.empty();

                var options_grupo = $("#grupo");
                options_grupo.empty();
                options_grupo.append(new Option("Seleccione Grupo", ""));

                if($("#cve_proveedor").val() == '')
                    options_proveedores.append(new Option("Seleccione Proveedor", ""));

                for (var i=0; i<data.articulos.length; i++)
                {
                    if(data.articulos[i].cve_articulo && data.articulos[i].articulo)
                      options_articulos.append(new Option(" ( "+data.articulos[i].cve_articulo +" ) "+data.articulos[i].articulo, data.articulos[i].cve_articulo));
                }
              
                for (var i=0; i<data.articulos2.length; i++)
                {
                    if(data.articulos2[i].cve_articulo && data.articulos2[i].articulo)
                      options_articulos2.append(new Option(" ( "+data.articulos2[i].cve_articulo +" ) "+data.articulos2[i].articulo, data.articulos2[i].cve_articulo));
                }

                for (var i=0; i<data.grupos.length; i++)
                {
                    if(data.grupos[i].id)
                      options_grupo.append(new Option(data.grupos[i].cve_grupo +" "+data.grupos[i].des_grupo, data.grupos[i].id));
                }

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
                    else if($("#cve_proveedor").val() == '' && proveedor[1])
                        options_proveedores.append(new Option(proveedor[1], proveedor[0]));
                  }

                }                
                //$("#proveedor").val($("#cve_proveedor").val());


                $("#articulo, #articulo2").trigger("chosen:updated");
                $("#proveedor").trigger("chosen:updated");
                $("#grupo").trigger("chosen:updated");
            }, error: function(data){
                console.log("ERROR SELECT = ", data);
            }
        });
    }
    //);


      //  var data = {
      //          almacen: $("#almacen").val(),
      //          cve_proveedor : $("#cve_proveedor").val(),
      //          filtro_concentrado : $("#filtro_concentrado_select").val()
      //          };

      //$("#grid-table").jqGrid('clearGridData')
      //.jqGrid('setGridParam', {postData: data, datatype: 'json', page : 1})
      //.trigger('reloadGrid',[{current:true}]);

        //table();

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
    $("#almacen, #articulo, #articulo2, #proveedor, #grupo, #filtro_concentrado_select, #check_almacen").change(function(){

        var check_almacen = 0;
        if($("#check_almacen").is(':checked'))
            check_almacen = 1;

        var data = {
                    almacen : $("#almacen").val(),
                    cve_proveedor : $("#cve_proveedor").val(),
                    filtro_concentrado : $("#filtro_concentrado_select").val(),
                    proveedor: $("#proveedor").val(),
                    grupo: $("#grupo").val(),
                    check_almacen: check_almacen,
                    articulo : $("#articulo").val(),
                    articulo2 : $("#articulo2").val()
                };

       console.log("almacen = ", $("#almacen").val(), ", articulo = ", $("#articulo").val(), ", proveedor = ", $("#proveedor").val(), ", grupo = ", $("#grupo").val());


      $("#grid-table").jqGrid('clearGridData')
      .jqGrid('setGridParam', {postData: data, datatype: 'json', page : 1})
      .trigger('reloadGrid',[{current:true}]);

        //generar_selects();
        //table();

    });

    $("#check_almacen").click(function(){
        //setTimeout(function(){
            //generar_selects();
            //$('#almacen').trigger("chosen:updated");
            //$("#articulo").trigger("chosen:updated");
            //$("#proveedor").trigger("chosen:updated");
            //$("#grupo").trigger("chosen:updated");

    //}, 1000);
        
            if($(this).is(":checked"))
            {
                $(".articulos1").hide();
                $(".articulos2").show();
            }
            else
            {
                $(".articulos1").show();
                $(".articulos2").hide();
            }


    });

    $.fn.dataTable.ext.errMode = 'throw';
    $("#articulo, #articulo2").trigger("chosen:updated");
    $("#proveedor").trigger("chosen:updated");
    $("#grupo").trigger("chosen:updated");
    });

</script>