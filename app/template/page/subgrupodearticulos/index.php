<?php
$ga = new \GrupoArticulos\GrupoArticulos();
?>
<div class="main-content">
  <div class="main-content-inner">


    <div class="page-content">
      <div class="page-header">
        <h1>
          Comprobantes Recibidos
          <small>
            <i class="ace-icon fa fa-angle-double-right"></i>
            <!--Dynamic tables and grids using jqGrid plugin-->
          </small>
        </h1>
      </div><!-- /.page-header -->

      <div class="row">

        <div class="widget-box" id="widget-box-1">
          <div class="widget-header">
            <h5 class="widget-title"><i class="ace-icon fa fa-search"></i>Filtros de B&uacute;squeda</h5>

            <div class="widget-toolbar">
              <a href="#" data-action="collapse">
                <i class="ace-icon fa fa-chevron-up"></i>
              </a>

            </div>
          </div>

          <div class="widget-body">
            <div class="widget-main">
              <div class="row" style="padding: 5px">
                <div class="col-sm-10">
                                            <span class="input-icon input-icon-right">
										        <input type="text" placeholder="Raz&oacute;n Social Emisor" id="txtEmisor" />
                                            </span>
                  <span class="input-icon input-icon-right">
										        <select class="form-control" id="cboEstado">
                                                    <option value="">Ninguno</option>
                                                    <option value="0">Estado Procesamiento</option>
                                                    <option value="1">Consultado</option>
                                                    <option value="2">Publicado al Receptor</option>
                                                </select>
                                            </span>
                  <span class="input-icon input-icon-right">
										        <select class="form-control" id="cboEstado">
                                                    <option value="">Ninguno</option>
                                                    <option value="0">Sunap</option>
                                                    <option value="1">Aceptado</option>
                                                    <option value="2">Aceptado con Observaciones</option>
                                                </select>
                                            </span>
                </div>

              </div>
              <div class="row" style="padding: 5px">
                <div class="col-sm-10">
                                            <span class="input-icon input-icon-right">
										        <input type="text" placeholder="Raz&oacute;n Social Receptor" id="txtReceptor" />
                                            </span>
                  <span class="input-icon input-icon-right">
										        <input type="text" placeholder="Nro Serie" id="txtSerie"/>
                                            </span>
                  <span class="input-icon input-icon-right">
										        <input class="form-control date-picker" data-date-format="dd-mm-yyyy" type="text" id="id-date-picker-1"/>
                                            </span>
                </div>
              </div>
              <div class="row" style="padding: 5px">
                <div class="col-sm-10">
                                            <span class="input-icon input-icon-right">
										        <select class="form-control" id="cboTipoDocumento">
                                                    <option value="">Ninguno</option>
                                                    <option value="0">Tipo Comprobante</option>
                                                    <option value="1">Boleta</option>
                                                    <option value="2">Factura</option>
                                                    <option value="3">Nota Crédito</option>
                                                    <option value="4">Nota Débito</option>
                                                </select>
                                            </span>
                  <span class="input-icon input-icon-right">
										        <input type="text" placeholder="Nro Correlativo" id="txtCorrelativo" />
                                            </span>
                  <span class="input-icon input-icon-right">
										        <input class="form-control date-picker" data-date-format="dd-mm-yyyy" type="text" id="id-date-picker-2"/>
                                            </span>
                </div>
              </div>
              <div class="row" style="padding: 5px">
                <div class="col-sm-10">
                                            <span class="input-icon input-icon-right">
										        <button type="button" class="btn btn-purple btn-sm" id="btnSearch" onclick="ReloadGrid();"><span class="ace-icon fa fa-search icon-on-right bigger-110"></span>&nbsp;Search</button>
                                            </span>
                  <span class="input-icon input-icon-right">
										        <button type="button" class="btn btn-purple btn-sm" id="btnClean" onclick="clearFields();"><span class="ace-icon fa fa-search icon-on-right bigger-110"></span>&nbsp;Limpiar</button>
                                            </span>
                  <span class="input-icon input-icon-right">
										        <button type="button" class="btn btn-purple btn-sm" onclick="ReloadGrid();" id="btnReload"><span class="ace-icon fa fa-refresh bigger-110"></span>&nbsp;Actualizar</button>
                                            </span>
                </div>
              </div>
            </div>
          </div>
        </div>


      </div>
      <br>
      <div class="row">
        <div class="col-xs-12">

          <table id="grid-table"></table>

          <div id="grid-pager"></div>

          <script type="text/javascript">
            var $path_base = ".";//in Ace demo this will be used for editurl parameter
          </script>

          <!-- PAGE CONTENT ENDS -->
        </div><!-- /.col -->
      </div>


      <!-- /.row -->
    </div><!-- /.page-content -->
  </div>
</div><!-- /.main-content -->

<script type="text/javascript">
  function clearFields() {
    $("#id-date-picker-1").val("");
    $("#id-date-picker-2").val("");
    $("#txtEmisor").val("");
    $("#txtReceptor").val("");
    $("#cboTipoDocumento").val("");
    $("#cboEstado").val("");
    $("#txtSerie").val("");
    $("#txtCorrelativo").val("");
  }

  //////////////////////////////////////////////////////////Aqui se contruye el Grid//////////////////////////////////////////////////////////
  jQuery(function($) {
    var grid_selector = "#grid-table";
    var pager_selector = "#grid-pager";

    //resize to fit page size
    $(window).on('resize.jqGrid', function () {
      $(grid_selector).jqGrid( 'setGridWidth', $(".page-content").width() );
    })
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

    jQuery(grid_selector).jqGrid({
      url:'c/index.php',
      datatype: "json",
      height: 250,
      postData: {
        _FecIni: $("#id-date-picker-1").val(),
        _FecFin: $("#id-date-picker-2").val(),
        _Emisor: $("#txtEmisor").val(),
        _Receptor: $("#txtReceptor").val(),
        _TipComprobante: $("#cboTipoDocumento").val(),
        _Sunat: $("#cboEstado").val(),
        _Serie: $("#txtSerie").val(),
        _DocNro: $("#txtCorrelativo").val(),
        _IdEmp: "<?php $idEmp; ?>",
      },
      mtype: 'POST',
      colNames:['Emisor','Receptor','Tipo','Serie','Correlativo','Valor','Fecha de Emisión',"Estado",""],
      colModel:[
        //{name:'id',index:'id', width:60, sorttype:"int", editable: false},
        {name:'emisor',index:'emisor',width:150, editable:false, sortable:false},
        {name:'receptor',index:'receptor', width:150,editable: false, sortable:false},
        {name:'tipo',index:'tipo', width:70, editable: false, sortable:false},
        {name:'serie',index:'serie', width:90, editable: false, sortable:false},
        {name:'correlativo',index:'correlativo', width:70, sortable:false,editable: false},
        {name:'valor',index:'valor', width:70, sortable:false,editable:false},
        {name:'fechaEmision',index:'fechaEmision', width:70, sortable:false},
        {name:'estado',index:'estado', width:70, sortable:false,editable: false},
        {name:'myac',index:'', width:80, fixed:true, sortable:false, resize:false, formatter:imageFormat},
      ],
      rowNum:10,
      rowList:[10,20,30],
      pager: pager_selector,
      sortname: 'IdFacEle',
      viewrecords: true,
      sortorder: "desc",
      loadComplete : function() {
        var table = this;
        setTimeout(function(){
          styleCheckbox(table);

          updateActionIcons(table);
          updatePagerIcons(table);
          enableTooltips(table);
        }, 0);
      },
      gridComplete: function(){
        /*var ids = jQuery(grid_selector).jqGrid('getDataIDs');
         for(var i=0;i<ids.length;i++){
         var cl = ids[i];
         be = "<input style='height:22px;width:40px;' type='button' value='Edit' onclick=\"jQuery(grid_selector).jqGrid('editRow','"+cl+"');\"  />";
         se = "<input style='height:22px;width:40px;' type='button' value='Save' onclick=\"jQuery(grid_selector).jqGrid('saveRow','"+cl+"');\"  />";
         ce = "<input style='height:22px;width:50px;' type='button' value='Cancel' onclick=\"jQuery(grid_selector).jqGrid('restoreRow','"+cl+"');\" />";
         jQuery(grid_selector).jqGrid('setRowData',ids[i],{act:be+se+ce});
         }*/
      },
    });

    $(window).triggerHandler('resize.jqGrid');//trigger window resize to make the grid get the correct size
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    //////////////////////////////////////////////////////////aqui se construye la funcion de cada item del grid//////////////////////////////////////////////////////////
    function imageFormat( cellvalue, options, rowObject ){
      //return '<img src="'+cellvalue+'" />&nbsp;&nbsp;&nbsp;&nbsp;<img src="'+cellvalue+'" />';
      var serie = rowObject[3];
      var correl = rowObject[4];
      var url = "x/?serie="+serie+"&correl="+correl;
      var url2 = "v/?serie="+serie+"&correl="+correl;

      // la funcion downloadxml ejecuta el index.php que esta en la carpeta x pasandole la serie y el correlativo via get
      // la funcion viewPdf ejecuta el index.php que esta en la carpeta v pasandole la serie y el correlativo via get
      return '<a href="#" onclick="downloadxml(\''+url+'\')"><i class="ace-icon fa fa-download" alt="Download"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="#" onclick="viewPdf(\''+url2+'\')"><i class="ace-icon fa fa-file-pdf-o" alt="Ver PDF"></i></a>';
    }

    function aceSwitch( cellvalue, options, cell ) {
      setTimeout(function(){
        $(cell) .find('input[type=checkbox]')
            .addClass('ace ace-switch ace-switch-5')
            .after('<span class="lbl"></span>');
      }, 0);
    }
    //enable datepicker
    function pickDate( cellvalue, options, cell ) {
      setTimeout(function(){
        $(cell) .find('input[type=text]')
            .datepicker({format:'yyyy-mm-dd' , autoclose:true});
      }, 0);
    }

    function beforeDeleteCallback(e) {
      var form = $(e[0]);
      if(form.data('styled')) return false;

      form.closest('.ui-jqdialog').find('.ui-jqdialog-titlebar').wrapInner('<div class="widget-header" />')
      style_delete_form(form);

      form.data('styled', true);
    }

    function reloadPage() {
      var grid = $(grid_selector);
      $.ajax({
        url: "index.php",
        dataType: "json",
        success: function(data){
          grid.trigger("reloadGrid",[{current:true}]);
        },
        error: function(){}
      });
    }

    function beforeEditCallback(e) {
      var form = $(e[0]);
      form.closest('.ui-jqdialog').find('.ui-jqdialog-titlebar').wrapInner('<div class="widget-header" />')
      style_edit_form(form);
    }

    //it causes some flicker when reloading or navigating grid
    //it may be possible to have some custom formatter to do this as the grid is being created to prevent this
    //or go back to default browser checkbox styles for the grid
    function styleCheckbox(table) {

    }

    //unlike navButtons icons, action icons in rows seem to be hard-coded
    //you can change them like this in here if you want
    function updateActionIcons(table) {

      var replacement =
      {
        'ui-ace-icon fa fa-pencil' : 'ace-icon fa fa-pencil blue',
        'ui-ace-icon fa fa-trash-o' : 'ace-icon fa fa-trash-o red',
        'ui-icon-disk' : 'ace-icon fa fa-check green',
        'ui-icon-cancel' : 'ace-icon fa fa-times red'
      };
      $(table).find('.ui-pg-div span.ui-icon').each(function(){
        var icon = $(this);
        var $class = $.trim(icon.attr('class').replace('ui-icon', ''));
        if($class in replacement) icon.attr('class', 'ui-icon '+replacement[$class]);
      })

    }

    //replace icons with FontAwesome icons like above
    function updatePagerIcons(table) {
      var replacement =
      {
        'ui-icon-seek-first' : 'ace-icon fa fa-angle-double-left bigger-140',
        'ui-icon-seek-prev' : 'ace-icon fa fa-angle-left bigger-140',
        'ui-icon-seek-next' : 'ace-icon fa fa-angle-right bigger-140',
        'ui-icon-seek-end' : 'ace-icon fa fa-angle-double-right bigger-140'
      };
      $('.ui-pg-table:not(.navtable) > tbody > tr > .ui-pg-button > .ui-icon').each(function(){
        var icon = $(this);
        var $class = $.trim(icon.attr('class').replace('ui-icon', ''));

        if($class in replacement) icon.attr('class', 'ui-icon '+replacement[$class]);
      })
    }

    function enableTooltips(table) {
      $('.navtable .ui-pg-button').tooltip({container:'body'});
      $(table).find('.ui-pg-div').tooltip({container:'body'});
    }

    //var selr = jQuery(grid_selector).jqGrid('getGridParam','selrow');

    $(document).one('ajaxloadstart.page', function(e) {
      $(grid_selector).jqGrid('GridUnload');
      $('.ui-jqdialog').remove();
    });
  });

  //////////////////////////////////////////////////////////hace un reload del grid llamando al php en la carpera "c" para hacer la consulta//////////////////////////////////////////////////////////
  function ReloadGrid() {
    jQuery('#grid-table').jqGrid('clearGridData')
        .jqGrid('setGridParam', {postData: {
          _FecIni: $("#id-date-picker-1").val(),
          _FecFin: $("#id-date-picker-2").val(),
          _Emisor: $("#txtEmisor").val(),
          _Receptor: $("#txtReceptor").val(),
          _TipComprobante: $("#cboTipoDocumento").val(),
          _Sunat: $("#cboEstado").val(),
          _Serie: $("#txtSerie").val(),
          _DocNro: $("#txtCorrelativo").val(),
          _IdEmp: "<?php $idEmp; ?>",
        }, datatype: 'json', page : 1})
        .trigger('reloadGrid',[{current:true}]);
  }

  function downloadxml( url ) {
    var win = window.open(url, '_blank');
    win.focus();
  }

  function viewPdf( url ) {
    var win = window.open(url, '_blank');
    win.focus();
  }

  $('.date-picker').datepicker({
    autoclose: true,
    todayHighlight: true
  })
  //show datepicker when clicking on the icon
      .next().on(ace.click_event, function(){
    $(this).prev().focus();
  });

  //or change it into a date range picker
  $('.input-daterange').datepicker({autoclose:true});
</script>