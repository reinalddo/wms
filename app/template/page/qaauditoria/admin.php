<?php
$almacenes = new \AlmacenP\AlmacenP();
$model_almacen = $almacenes->getAll();
?>
<link href="/css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">
<link href="/css/plugins/ladda/select2.css" rel="stylesheet"/>
<link href="/css/plugins/sweetalert/sweetalert.css" rel="stylesheet">
<link href="/css/plugins/chosen/chosen.css" rel="stylesheet">
<link href="/css/plugins/dataTables/dataTables1.min.css" rel="stylesheet"/>




<style>
    #inicio {
        width: 100%;
        height: 100%;
        position: absolute;
        left: 0px;
        z-index: 999;
    }

    #modulo {
        width: 100%;
        /*height: 100%;*/
        position: absolute;
        left: 0px;
        z-index: 999;
    }
    #auditoriayempaque{
        width: 100%;
        padding-left: 15px;
        padding-right: 15px;
    }
</style>

<!-- Inicio -->
<div class="wrapper wrapper-content  animated" id="inicio">
    <h3>QA Auditoría</h3>

  <div class="row">
    <div class="col-md-12">
      <div class="ibox ">
          <div class="ibox-title">
              <div class="row">
                  <div class="col-md-3">
                      <div class="form-group">
                          <label>Status de Orden</label>
                          <select id="select-status" class="chosen-select form-control">
                              <option value="">Seleccione un Status</option>
                              <option value="R">Auditando</option>
                              <option value="L">Pendiente de auditar</option>
                              <!--<option value="C">Pendiente de Embarque</option>-->
                          </select>
                      </div>
                  </div>
                  <div class="col-md-8">
                      <div class="input-group" style="margin-top: 23px;">
                          <input type="text" class="form-control" name="txtCriterio" id="txtCriterio" placeholder="Ingrese el # de Pedido o Folio a buscar...">
                          <div class="input-group-btn">
                              <button onclick="ReloadGrid()" type="submit" class="btn btn-primary" id="buscarA">
                              <i class="fa fa-search"></i> Buscar
                              </button>                                
                          </div>
                        
                        
                        
                       &nbsp;&nbsp;&nbsp;
                        <div  class="input-group-btn">
                          <button onclick="areas_dispo()" type="submit" class="btn btn-primary" id="btn-areas-dispo">
                                  <i class="fa fa-search"></i> Areas de Revision Disponibles
                            <!--EDG !!! !!! !!! !!! falta definir la funcion areas_dispo() -->
                          </button>   
                         
                        </div>
                   </div>
                  </div>
              </div>
           
          </div>
          <div class="ibox-content">
                  <div class="text-right">
                    <label class="text-right"><input type="checkbox" id="btn-asignar-todo" /> Asignar todo</label>
                  </div>
                    <table id="grid-table"></table>
                    <table id="table-info"  class="table table-hover table-striped no-margin display nowrap" style="width:100%">
                  <tbody id = "tbody">
                </tbody>
                     <div class="text-right">
                      <br>
                      <button type="button" class="btn btn-primary permiso_editar" id="btn-cambiar-status">
                        <span class="fa "></span> Cambiar a Pendiente por Auditar
                      </button>
                      <button type="button" class="btn btn-primary permiso_editar" id="btn-cambiar-auditado">
                        <span class="fa "></span> Cambiar a Auditado
                      </button>
                     </div>
              </table>
      </div>
    </div>
  </div>
</div>

<!--modal de de mesas disponibles-->
<div class="modal fade" id="mesas" role="dialog">
    <div class="vertical-alignment-helper">
        <div class="modal-dialog vertical-align-center modal-lg" style="width: 400px!important;">
            <!-- Contenido -->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="mesas">&times;</button>
                    <h4>Areas de Revision Disponibles</h4>
                 </div>
                          <div class="modal-body">
                              <div id="disponibles_ahora">
                                
                              </div>
                          </div>
                     
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
</div>
  
  
  
  
<!--modal de de ver detalle lupa-->
<div class="modal fade" id="detalleModal" role="dialog">
    <div class="vertical-alignment-helper">
        <div class="modal-dialog vertical-align-center modal-lg" style="width: 1200px!important;">
            <!-- Contenido -->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4>Detalle de Pedido <span id="n_inventario"></span></h4>
                        </div>
                          <div class="modal-body">
                              <div class="jqGrid_wrapper" style="overflow-x: hidden;">
                                  <table id="grid-table2"></table>
                                  <div id="grid-pager2"></div>
                              </div>
                          </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
  </div>
  
  


<!-- Mainly scripts -->
<!--<script src="/js/dropdownLists.js"></script>-->
<script src="/js/jquery-2.1.1.js"></script>
<script src="/js/bootstrap.min.js"></script>
<script src="/js/plugins/metisMenu/jquery.metisMenu.js"></script>
<script src="/js/plugins/slimscroll/jquery.slimscroll.min.js"></script>


<!-- Peity -->
<script src="/js/plugins/peity/jquery.peity.min.js"></script>

<!-- jqGrid -->
<script src="/js/plugins/jqGrid/i18n/grid.locale-es.js"></script>
<script src="/js/plugins/jqGrid/jquery.jqGrid.js"></script>


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
<script src="/js/plugins/sweetalert/sweetalert.min.js"></script>
<script src="/js/plugins/dataTables/dataTables.min.js"></script>
<script src="/js/plugins/dataTables/dataTables_bootstrap.min.js"></script>
<script src="/js/utils.js"></script>

<script type="text/javascript">
  var select_status = document.getElementById("select-status");
  
  $('.chosen-select').chosen();

  $(function($) {
    var grid_selector = "#grid-table";
    var pager_selector = "#grid-pager";
    
    //resize to fit page size
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
    })
        
    $(grid_selector).jqGrid({
        url:'/api/qaauditoria/index.php',
        datatype: 'local',
        shrinkToFit: false,
        height:'auto',
        mtype: 'POST',
        colNames:['Accion','No.pedido','# Folio','Cliente', '# Articulos', 'Area de Revision', 'Status','Cambiar Status'], 
        colModel:[
            //comenar aqio
            {name:'Accion',index:'Accion', align:'center', width:80, fixed:true, sortable:false, resize:false, formatter: getActions},
            {name:'No_pedido',index:'No_pedido', editable:false, sortable:false,width:90},
            {name:'Fol_Folio',index:'Fol_Folio', editable:false, sortable:false,width:90},
            {name:'Cliente',index:'Cliente', editable:false, sortable:false,width:280},
            {name:'# Articulos',index:'# Articulos', editable:false, sortable:false,width:90},
            {name:'Area de Revision',index:'Area de Revision', editable:false, sortable:false,width:110},
            {name:'Status',index:'Status', editable:false, sortable:false,width:190},
            
            {name: 'Cambiar_Status',index: 'Cambiar_Status',width: 109.5,fixed: true,sortable: false,resize: false,align: "center",
              formatter: "checkbox", formatoptions: {disabled: false},
              edittype: "checkbox", editoptions: {value: "Yes:No", defaultValue: "Yes"},
              stype: "select", 
              searchoptions: {sopt: ["eq", "ne"], value: ":Any;true:Yes;false:No"}
            }
        ],
        rowNum:30,
        rowList:[30,40,50],
        pager: pager_selector,
        sortname: 'folio',
        viewrecords: true,
        sortorder: "desc",
        loadonce: true,
        //loadComplete: almacenPrede()
    });
      
    $(grid_selector).jqGrid('navGrid', '#grid-pager',
      {edit: false, add: false, del: false, search: false},
      {height: 200, reloadAfterSubmit: true}
     );
      
    function agregarcheck(cell, options, row){
          var id = row[2];
          var html =`<input type="checkbox" id="casilla_check'.id.'" name="my_check">`;
          return html;
    }
     //  <a href="#" onclick="printPDF(${id})" title="Imprimir PDF"><i class="fa fa-print"></i>PDF</a>
        //    <a href="#" onclick="printExcel(${id})" title="Imprimir Excel"><i class="fa fa-print"></i>Excel</a>`;
       
    function getActions(cell, options, row){
        var id = row[2];
        var html = '<a href="#" onclick="verDetalle(\'' + id + '\')" title="Ver Detalle"><i class="fa fa-search"></i></a>';
           
        return html;
    }
      
      

    $(window).triggerHandler('resize.jqGrid');

    $(document).one('ajaxloadstart.page', function(e) {
        $.jgrid.gridUnload(pedidos_grid_selector);
        $.jgrid.gridUnload(grid_selector);
        $('.ui-jqdialog').remove();
    });
//*****************************************************************************************************************************
//*****************************************************************************************************************************
    var grid_selector = "#grid-table2";
    var pager_selector = "#grid-pager2";
    
    //resize to fit page size
/*
    $(window).on('resize.jqGrid', function () {
        $(grid_selector).jqGrid( 'setGridWidth', $(".page-content").width() - 60 );
    });
*/
    //resize on sidebar collapse/expand
    /*
    var parent_column = $(grid_selector).closest('[class*="col-"]');
    $(document).on('settings.ace.jqGrid' , function(ev, event_name, collapsed) {
        if( event_name === 'sidebar_collapsed' || event_name === 'main_container_fixed' ) {
            //setTimeout is for webkit only to give time for DOM changes and then redraw!!!
            setTimeout(function() {
                $(grid_selector).jqGrid( 'setGridWidth', parent_column.width() );
            }, 0);
        }
    })
        */
    $(grid_selector).jqGrid({
        url:'/api/qaauditoria/index.php',
        datatype: 'local',
        shrinkToFit: false,
        height:'auto',
        mtype: 'POST',
        colNames:['Clave','Descripción','Lote|Serie', 'Caducidad', 'Surtidas', 'Pendiente de Auditar','Revisadas'], 
        colModel:[
            //comenar aqio
            {name:'cve_articulo',index:'cve_articulo', editable:false, sortable:false,width:150},
            {name:'descripcion',index:'descripcion', editable:false, sortable:false,width:200},
            {name:'lote',index:'lote', editable:false, sortable:false,width:150},
            {name:'caducidad',index:'caducidad', editable:false, sortable:false,width:120},
            {name:'surtidas',index:'surtidas', editable:false, sortable:false,width:110},
            {name:'p_auditar',index:'p_auditar', editable:false, sortable:false,width:110},
            {name:'revisadas',index:'revisadas', editable:false, sortable:false,width:110}
        ],
        rowNum:30,
        rowList:[30,40,50],
        pager: pager_selector,
        sortname: 'folio',
        viewrecords: true,
        sortorder: "desc",
        loadonce: true,
        //loadComplete: almacenPrede()
    });
      
    $(grid_selector).jqGrid('navGrid', '#grid-pager2',
      {edit: false, add: false, del: false, search: false},
      {height: 200, reloadAfterSubmit: true, width: 1200}
     );
      
    function agregarcheck(cell, options, row){
          var id = row[2];
          var html =`<input type="checkbox" id="casilla_check'.id.'" name="my_check">`;
          return html;
    }
     //  <a href="#" onclick="printPDF(${id})" title="Imprimir PDF"><i class="fa fa-print"></i>PDF</a>
        //    <a href="#" onclick="printExcel(${id})" title="Imprimir Excel"><i class="fa fa-print"></i>Excel</a>`;
       
    function getActions(cell, options, row){
        var id = row[2];
        var html = '<a href="#" onclick="verDetalle(\'' + id + '\')" title="Ver Detalle"><i class="fa fa-search"></i></a>';
           
        return html;
    }
      
      

    $(window).triggerHandler('resize.jqGrid');

    $(document).one('ajaxloadstart.page', function(e) {
        $.jgrid.gridUnload(pedidos_grid_selector);
        $.jgrid.gridUnload(grid_selector);
        $('.ui-jqdialog').remove();
    });


  });

  function ReloadGrid() {
      var status = $("#select-status").val();
      $('#grid-table').jqGrid('clearGridData')
          .jqGrid('setGridParam', {postData: {
              "action" : "pedidos_qa",
              "status" :  status
          }, datatype: 'json', page : 1,url:"/api/qaauditoria/index.php"})
          .trigger('reloadGrid',[{current:true}]);
  }
  
  $( document ).ready(function() {
    console.log("Cargar");
    ReloadGrid();
  });
  
  
  
  
  //funcion de cambiar el status del pedido pendiente por auditar o auditando 
  function change_status(folios) {
      
          $.ajax({
            url: "/api/qaauditoria/index.php",
            type: "POST",
            data: {
                "action" : "cambio_status",
                "folios" : folios,
                "status" : "L"
            },
            beforeSend: function(x){
                if(x && x.overrideMimeType) x.overrideMimeType("application/json;charset=UTF-8");
            },
            success: function(res){
                //console.log(res);
            },
            error : function(res){
                window.console.log(res);
            }
        });
    //console.log("Ok primer paso");
    $( document ).ready(function() {
   // location.reload();
      ReloadGrid();
      //ReloadGrid();
  });
  }
  
    function ReloadDetalle(id) {
        $('#grid-table2').jqGrid('clearGridData')
        .jqGrid('setGridParam', {postData: {
            action: 'detalle_qa',
            folio: id
        }, datatype: 'json', page : 1,url:"/api/qaauditoria/index.php"})
        .trigger('reloadGrid',[{current:true}]);
    }

        //no definido
    function verDetalle(id){
      
        console.log("verDetalle = ", id);
        $("#n_inventario").text(id);
        $("#detalleModal").modal('show');
        ReloadDetalle(id);
    }

    
 //change_auditado
   function change_auditado(folios) {
      //var $auditando_ahora = $("td[aria-describedby='grid-table_Status'] input[type='Text' value='AUDITANDO']"); 
      //console.log($auditando_ahora);
     
     //var $checkboxes = $('td[aria-describedby="grid-table_Cambiar_Status"] input[type="checkbox"]');
    //console.log($checkboxes);
          $.ajax({
            url: "/api/qaauditoria/index.php",
            type: "POST",
            data: {
                "action" : "cambio_auditado",
                "folios" : folios,
                "status" : "P"
            },
            beforeSend: function(x){
                if(x && x.overrideMimeType) x.overrideMimeType("application/json;charset=UTF-8");
            },
            success: function(res){
                //console.log(res);
            },
            error : function(res){
                window.console.log(res);
            }
        });
    //console.log("Ok primer paso");
    $( document ).ready(function() {
   // location.reload();
      ReloadGrid();
  });
  };

  //Areas disponibles para revision y auditoria
  function areas_dispo(){
    console.log("hola estoy buscando");
      $.ajax({
            url: "/api/qaauditoria/index.php",
            type: "POST",
            data: {
                "action" : "mesas_disponibles",
              },
              beforeSend: function(x){
                  if(x && x.overrideMimeType) x.overrideMimeType("application/json;charset=UTF-8");
              },
              success: function(res){
                var texto = "";
                var res = res;
              for(var i=0; i<res.length; ++i){
                texto+=res[i]+"<br>";
              }

                $("#mesas").modal('show');
                $("#disponibles_ahora").html(texto);
                console.log(res);
              },
              error : function(res){
                window.console.log(res);
            }
        });
    }

    function init(){
      var status = $("#select-status").val();
      if (status != ""){
        $.ajax({
            url: "/api/qaauditoria/index.php",
            type: "POST",
            data: {
                "action" : "pedidos_qa",
                "status" :  status
            },
            beforeSend: function(x){
                if(x && x.overrideMimeType) x.overrideMimeType("application/json;charset=UTF-8");
            },
            success: function(res){
                console.log(res);
            },
            error : function(res){
                window.console.log(res);
            }
        });
       }
      else
        {
          console.log("selecionar status");
        }
     }
  
  
  //funcion asignar todo
   $("#btn-asignar-todo").on('click', function(e){
    console.log("asignar");
            var $checkboxes = $('td[aria-describedby="grid-table_Cambiar_Status"] input[type="checkbox"]');
    console.log($checkboxes);
            if(e.target.checked){
                if($checkboxes.length > 0){
                    $checkboxes.each(function(i,v){
                        v.checked = true;
                    });
                }
                } else {
                    if($checkboxes.length > 0){
                        $checkboxes.each(function(i,v){
                            v.checked = false;
                    });
                }
            }
        });
  
  
  //funcion cambiar status a pendiente por auditar
  $("#btn-cambiar-status").on('click', function(){
    console.log("cambiar status");

    var myGrid = $('#grid-table'), i, rowData, folios = [],
        rowIds = myGrid.jqGrid("getDataIDs"),
        n = rowIds.length;

    for (i = 0; i < n; i++) {
        rowData = myGrid.jqGrid("getRowData", rowIds[i]);
        if (rowData.Cambiar_Status=='Yes') {
            folios.push(rowData.Fol_Folio);
        }
    }
    console.log(folios);
    change_status(folios);

     });

  
   //funcion cambiar a auditado
   $("#btn-cambiar-auditado").on('click', function(){
    console.log("cambiar status a ya auditado");

    var myGrid = $('#grid-table'), i, rowData, folios = [],
        rowIds = myGrid.jqGrid("getDataIDs"),
        n = rowIds.length;

    for (i = 0; i < n; i++) {
        rowData = myGrid.jqGrid("getRowData", rowIds[i]);
        console.log(rowData);
        if (rowData.Cambiar_Status=='Yes') {
          if(rowData.Status == 'AUDITANDO')
          {
            swal("Error", "Por favor seleccione un pedido que no se este auditando", "error");
            //alert("Hey ! no puedes hacer eso amigo ! No se puede pasar a auditado un pedido que se está auditando. Debes pasarlo a pendiente por auditar primero...");
            return;
          }
            folios.push(rowData.Fol_Folio);
        }
    }
    console.log(folios);
    change_auditado(folios);

     });
  
  
   //funcion mostrar mesas disponibles
   $("btn-areas-dispo").on('click', function(){
    console.log("buscar mesas disponibles");
    //areas_dispo();

     });
  
  
   
</script>

