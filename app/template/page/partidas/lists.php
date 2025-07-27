<?php
include_once  $_SERVER['DOCUMENT_ROOT']."/app/host.php";

$vere = \db()->prepare("SELECT * FROM t_profiles AS a WHERE id_menu=179 and id_submenu=687 and id_role='".$_SESSION["perfil_usuario"]."'");
$vere->execute();
$ver = $vere->fetchAll(PDO::FETCH_ASSOC);


$agre = \db()->prepare("SELECT * FROM t_profiles AS a WHERE id_menu=179 and id_submenu=688 and id_role='".$_SESSION["perfil_usuario"]."'");
$agre->execute();
$ag = $agre->fetchAll(PDO::FETCH_ASSOC);


$edita = \db()->prepare("SELECT * FROM t_profiles AS a WHERE id_menu=179 and id_submenu=689 and id_role='".$_SESSION["perfil_usuario"]."'");
$edita->execute();
$edit = $edita->fetchAll(PDO::FETCH_ASSOC);


$borra = \db()->prepare("SELECT * FROM t_profiles AS a WHERE id_menu=179 and id_submenu=689 and id_role='".$_SESSION["perfil_usuario"]."'");
$borra->execute();
$borrar = $borra->fetchAll(PDO::FETCH_ASSOC);

?>
<link href="/css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">
<link href="/css/plugins/blueimp/css/blueimp-gallery.min.css" rel="stylesheet">
<link href="/css/plugins/chosen/chosen.css" rel="stylesheet">
<link href="/css/plugins/imgloader/fileinput.min.css" rel="stylesheet">

<!-- Mainly scripts -->
<script src="/js/jquery-2.1.1.js"></script>
<script src="/js/bootstrap.min.js"></script>
<script src="/js/plugins/metisMenu/jquery.metisMenu.js"></script>
<script src="/js/plugins/slimscroll/jquery.slimscroll.min.js"></script>

<link href="/css/plugins/sweetalert/sweetalert.css" rel="stylesheet">

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
<script src="/js/plugins/sweetalert/sweetalert.min.js"></script>
<!-- Select -->
<script src="/js/plugins/chosen/chosen.jquery.js"></script>

<script src="/js/plugins/imgloader/fileinput.min.js"></script>
<script src="/js/plugins/imgloader/locales/es.js"></script>
<!-- Jquery Validate -->
<script src="/js/plugins/validate/jquery.validate.min.js"></script>

<script src="/js/plugins/blueimp/jquery.blueimp-gallery.min.js"></script>

<style type="text/css">
    .ui-jqgrid,
    .ui-jqgrid-view,
    .ui-jqgrid-hdiv,
    .ui-jqgrid-bdiv,
    .ui-jqgrid,
    .ui-jqgrid-htable,
    #grid-table,
    #grid-table2,
    #grid-table3,
    #grid-table4,
    #grid-pager,
    #grid-pager2,
    #grid-pager3,
    #grid-pager4 {
        width: 100% !important;
        max-width: 100% !important;
    }
   ul.dropdown-menu.dropdown-menu-right {
        position: absolute;
        left: auto;
        right: 0;
    }
</style>

<style>
    #list {
        width: 100%;
        height: 100%;
        position: relative;
        left: 0px;
        z-index: 999;
    }

    #FORM {
        width: 100%;
        /*height: 100%;*/
        position: absolute;
        left: 0px;
        z-index: 999;
    }

    .ui-jqgrid-bdiv {
        height: 100%;
        width: 100% !important;
        max-width: 100% !important;
    }
</style>

<script src="/js/tools.js"></script>

<div class="wrapper wrapper-content  animated fadeInRight" id="arti">
  <h3>Partidas Presupuestarias</h3>
  <div class="row">
    <div class="col-md-12">
      <div class="ibox ">
        <div class="ibox-title">
          <div class="row">
            <div class="col-md-6" >
              <div class="input-group">
                <label>&nbsp; </label>
                <input type="text" class="form-control" name="txtCriterio" id="txtCriterio" placeholder="Buscar...">
                <div class="input-group-btn">
                  <button onclick="ReloadGrid()" style="margin-top: 22px" type="submit" class="btn btn-primary" id="buscarA">
                    <span class="fa fa-search"></span>  Buscar
                  </button>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <button onclick="agregar()" class="btn btn-primary pull-right"  type="button" style="margin-top: 22px; margin-left:10px; ">
                <i class="fa fa-plus"></i> Nuevo
              </button>
            </div>
          </div>
        </div>
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

<div class="modal inmodal" id="editModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content animated bounceInRight">
      <div class="modal-header" id="modaltitle">
        <button type="button" class="close" data-dismiss="modal">
          <span aria-hidden="true">&times;</span><span class="sr-only">Close</span>
        </button>
        <i class="fa fa-laptop modal-icon"></i>
        <h4 class="modal-title">Partida Presupuestaria</h4>
      </div>
      <form id="edit" action="javascript:save();">
        <input name="id" id="id" type="hidden">
        <input name="action" type="hidden" value="save">
        <div class="modal-body">
          <label>Nombre de la Partida *</label>
          <input id="new_nombre_partida" name="nombre_partida" type="text" placeholder="Partida" class="form-control" maxlength="30" required="true">
          <label>Clave de Partida *</label>
          <input id="new_clave_partida" name="clave_partida" type="text" placeholder="Clave" class="form-control" maxlength="30" required="true"><label id="CodeMessage" style="color:red;"></label>
          <label for="">Presupuesto *</label>
          <select class="form-control _presupuestos" name="id_presupuesto" id="new_presupuesto" required="true">
            <option value="">Selecionar Presupuesto</option>
          </select>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-white" data-dismiss="modal" id="new_btnCancel">Cerrar</button>
          <button type="submit" class="btn btn-primary ladda-button" data-style="contract" id="btnSave">Guardar</button>
        </div>
      </form>
    </div>
  </div>
</div>


<script>
  
  // Establece funcionalidad para evitar el doble click al guardar
  $('.ladda-button').ladda();
  
  // URL base a donde se dirigirán las peticiones del api
  var url_api = base_url()+"/api/partidas/update/index.php";
  
  // Traer datos para editar una partida
  function getEdit(id)
  {
    data={id:id,action:'get',callback:'toEdit'};
    core_get(url_api,data);
  }
  
  // Colocar datos de la pertida en interfaz
  function toEdit(data)
  {
    $("#id").val(data.id);
    $('#new_nombre_partida').val(data.nombre_partida);
    $('#new_clave_partida').val(data.clave_partida);
    $('#new_presupuesto').val(data.id_presupuesto);
    $("#editModal").modal('show');
  }
  
  // Guardar partida
  function save()
  {
    data = $("#edit").gform();
    data.action = "save";
    data.callback = "saveSuccess";
    core_get(url_api,data);
    $("#btnSave").ladda('start');
  }
  
  // Función ejecutada luego de salvar la info
  function saveSuccess(data)
  {
    $("#editModal").modal('hide');
    $("#edit").reset();
    $("#id").val("");
    $("#btnSave").ladda('stop');
    ReloadGrid();
  }
  
  // Crear una nueva partida
  function agregar()
  {
    $("#edit").reset();
    $("#id").val("");
    $("#editModal").modal('show');
  }
  
  // Comprobar la eliminación de una partida
  function eliminar(to_delete_id)
  {
    window.to_delete_id = to_delete_id;
    var options = {
      title:"¿Está seguro que desea borrar esta partida?",
      text:"Está a punto de borrar una partida y esta acción no se puede deshacer",
      fn:doEliminar
    };
    swal_yn(options);
  }
  
  // Eliminar una partida
  function doEliminar()
  {
    data={id:to_delete_id,action:'delete',callback:'deleteEnd'};
    core_get(url_api,data);
  }
  
  // Función ejecutada luego de que se borra una partida
  function deleteEnd(data)
  {
    ReloadGrid();
    console.log(data);
  }
  
  // Solicita los presupuestos actuales
  function getPresupuestos()
  {
    data={action:'getPresupuestos',callback:'addPresupuestos'};
    core_get(url_api,data);
  }
  
  // coloca los presupuestos en la interfaz
  function addPresupuestos(data)
  {
    $.each(data,function(i,v){
      $("._presupuestos").append(new Option(v.nombreDePresupuesto,v.id));
    });
  }
  
  // Swal estandarizado
  function swal_yn(opt)
  {
    var opt_def = {
      title: "--",
      text: "",
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "#DD6B55",
      confirmButtonText: "Borrar",
      cancelButtonText: "Cancelar",
      closeOnConfirm: true
    };
    var opts = $.extend(opt_def, opt);
    swal(opts,opts.fn);
  }
  
  getPresupuestos();
  
  
</script>

<script type="text/javascript">
  
        //////////////////////////////////////////////////////////Aqui se contruye el Grid//////////////////////////////////////////////////////////
        $(function($) {
            var grid_selector = "#grid-table";
            var pager_selector = "#grid-pager";


            //resize to fit page size
            $(window).on('resize.jqGrid', function() {
                    $(grid_selector).jqGrid('setGridWidth', $(".page-content").width() - 60);
                })
                //resize on sidebar collapse/expand
            var parent_column = $(grid_selector).closest('[class*="col-"]');
            $(document).on('settings.ace.jqGrid', function(ev, event_name, collapsed) {
                if (event_name === 'sidebar_collapsed' || event_name === 'main_container_fixed') {
                    //setTimeout is for webkit only to give time for DOM changes and then redraw!!!
                    setTimeout(function() {
                        $(grid_selector).jqGrid('setGridWidth', parent_column.width());
                    }, 0);
                }
            })

            //$("#txtCriterio").val()
            $(grid_selector).jqGrid({
                url: '/api/partidas/lista/index.php',
                datatype: "json",
                shrinkToFit: true,
                height: 'auto',
                mtype: 'POST',
                postData: {
                    criterio: $("#txtCriterio").val()
                },
                colNames: ['ID',
                    'Clave',
                    'Partida',
                    'Presupuesto',
                    'Acciones'
                ],

                colModel: [
                    {
                        name: 'id',
                        index: 'id',
                        width: 100,
                        editable: false,
                        hidden: true,
                        sortable: false,
                        resizable: false
                    }, {
                        name: 'clave_partida',
                        index: 'clave_partida',
                        width: 100,
                        editable: false,
                        sortable: false,
                        resizable: false
                    }, {
                        name: 'nombre_partida',
                        index: 'nombre_partida',
                        width: 200,
                        editable: false,
                        sortable: false,
                        resizable: false,
                        align:'center'
                    }, {
                        name: 'presupuesto',
                        index: 'presupuesto',
                        width: 232,
                        editable: false,
                        sortable: false,
                        resizable: false,
                        align:'center'
                    }, {
                        name: 'myac',
                        index: '',
                        width: 80,
                        fixed: true,
                        sortable: false,
                        resize: false,
                        formatter: imageFormat,
                        frozen: true
                    }, ],
                    rowNum: 30,
                    rowList: [30, 40, 50],
                    pager: pager_selector,
                    sortname: 'id',
                    viewrecords: true,
                    sortorder: "desc",
            });
           
            // Setup buttons
            $("#grid-table").jqGrid('navGrid', '#grid-pager', {
                edit: false,
                add: false,
                del: false,
                search: false
            }, {
                height: 200,
                reloadAfterSubmit: true
            });

            $(window).triggerHandler('resize.jqGrid'); //trigger window resize to make the grid get the correct size
          
            
            //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

            //////////////////////////////////////////////////////////aqui se construye la funcion de cada item del grid//////////////////////////////////////////////////////////
            function imageFormat(cellvalue, options, rowObject) {
                var serie = rowObject[0];
                var correl = rowObject[2];
                var url = "x/?serie=" + serie + "&correl=" + correl;
                var url2 = "v/?serie=" + serie + "&correl=" + correl;
                var html = '';
                html += '<a href="#" onclick="getEdit(\'' + serie + '\')"><i class="fa fa-edit" alt="Editar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
                html += '<a href="#" onclick="eliminar(\'' + serie + '\')"><i class="fa fa-eraser" alt="Borrar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
                return html;
            }

            function aceSwitch(cellvalue, options, cell) {
                setTimeout(function() {
                    $(cell).find('input[type=checkbox]')
                        .addClass('ace ace-switch ace-switch-5')
                        .after('<span class="lbl"></span>');
                }, 0);
            }
            //enable datepicker
            function pickDate(cellvalue, options, cell) {
                setTimeout(function() {
                    $(cell).find('input[type=text]')
                        .datepicker({
                            format: 'yyyy-mm-dd',
                            autoclose: true
                        });
                }, 0);
            }

            function beforeDeleteCallback(e) {
                var form = $(e[0]);
                if (form.data('styled')) return false;

                form.closest('.ui-jqdialog').find('.ui-jqdialog-titlebar').wrapInner('<div class="widget-header" />')
                style_delete_form(form);

                form.data('styled', true);
            }

            function reloadPage() {
                var grid = $(grid_selector);
                $.ajax({
                    url: "index.php",
                    dataType: "json",
                    success: function(data) {
                        grid.trigger("reloadGrid", [{
                            current: true
                        }]);
                    },
                    error: function() {}
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
            function styleCheckbox(table) {}

            //unlike navButtons icons, action icons in rows seem to be hard-coded
            //you can change them like this in here if you want
            function updateActionIcons(table) {}

            //replace icons with FontAwesome icons like above

            function updatePagerIcons(table) {}

            function enableTooltips(table) {
                $('.navtable .ui-pg-button').tooltip({
                    container: 'body'
                });
                $(table).find('.ui-pg-div').tooltip({
                    container: 'body'
                });
            }

            //var selr = jQuery(grid_selector).jqGrid('getGridParam','selrow');

            $(document).one('ajaxloadstart.page', function(e) {
                $(grid_selector).jqGrid('GridUnload');
                $('.ui-jqdialog').remove();
            });
        });
        //////////////////////////////////////////////////////////Aqui termina del Grid/////////////////////////////////////////////////////////////
      
      
        //////////////////////////////////////////////////////////hace un reload del grid llamando al php en la carpera "c" para hacer la consulta//////////////////////////////////////////////////////////
        function ReloadGrid() {
            $('#grid-table').jqGrid('clearGridData')
                .jqGrid('setGridParam', {
                    postData: {
                        criterio: $("#txtCriterio").val(),
                        almacen: $("#almacenes").val(),
                    },
                    datatype: 'json',
                    page: 1
                })
                .trigger('reloadGrid', [{
                    current: true
                }]);
        }

        function downloadxml(url) {
            var win = window.open(url, '_blank');
            win.focus();
        }

        function viewPdf(url) {
            var win = window.open(url, '_blank');
            win.focus();
        }

        $modal0 = null;

     
        function ver(_codigo) {

            $("#upload").show();



            $("#hiddenID").val(_codigo);
            $("#CodeMessage").html("");
            $("#CodeMessage2").html("");
            $("#CodeMessage3").html("");
            $("#CodeMessage4").html("");
            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    cve_articulo: _codigo,
                    action: "load"
                },
                beforeSend: function(x) {
                    if (x && x.overrideMimeType) {
                        x.overrideMimeType("application/json;charset=UTF-8");
                    }
                },
                url: '/api/presupuestos/update/index.php',
                success: function(data) {
                    if (data.success == true) {

                        $.each(data, function(key, value) {
                            if (key != "imagen")
                                $('#' + key).val(value);
                        });
                        $(".modal-subtitle").text(data.des_articulo);
                        $('#codigo_ver').val(data.cve_articulo);
                        $('#cve_codprov_ver').val(data.cve_codprov);
                        $('#des_articulo_ver').val(data.des_articulo);
                        $('#peso_ver').val(data.peso);
                        $('#barras2_ver').val(data.barras2);
                        $('#num_multiplo_ver').val(data.num_multiplo);
                        $('#barras3_ver').val(data.barras3);
                        $('#cajas_palet_ver').val(data.cajas_palet);

                        $('#grupo_ver').val(data.grupo);
                        $('#clasificacion_ver').val(data.clasificacion);
                        $('#tipo_ver').val(data.tipo);


                        $("#hiddenID").val(_codigo);

                        //Construyo la parte de las imagenes
                        $('#upload2 div').remove();
                        $('#upload2 img').remove();
                        $('#upload2 a').remove();

                        for (var i = 0; i < data.fotos.length; i++) {


                            $('#upload2').append('<a href="../img/articulo/' + data.fotos[i].url + '" title="Image from Unsplash" data-gallery=""><img src="../img/articulo/' + data.fotos[i].url + '" width="100" height="100"></a>');

                        }
                        l.ladda('stop');
                        $("#btnCancel").show();

                        //$('#list').removeAttr('class').attr('class', '');
                        //$('#list').addClass('animated');
                        //$('#list').addClass("fadeOutRight");
                        //$('#arti').hide();


                        $("#btnCancel").show();
                        $modal0 = $("#modalVer");
                        $modal0.modal('show');
                    }
                }
            });
        }

        function cancelar() {
            console.log("Cancelar");
            $("#input-2").fileinput('reset');
            $('#upload div').remove();
            $('#upload img').remove();
            $('#upload a').remove();
            $(':input', '#myform')
                .removeAttr('checked')
                .removeAttr('selected')
                .not(':button, :submit, :reset, :hidden, :radio, :checkbox')
                .val('');
            $('#FORM').removeAttr('class').attr('class', '');
            $('#FORM').addClass('animated');
            $('#FORM').addClass("fadeOutRight");
            $('#FORM').hide();

            $('#arti').show();
            $('#list').removeAttr('class').attr('class', '');
            $('#list').addClass('animated');
            $('#list').addClass("fadeInRight");
            $('#list').addClass("wrapper");
            $('#list').addClass("wrapper-content");
        }
  
        $(document).ready(function() {
            $("div.ui-jqgrid-bdiv").css("max-height", $("#list").height() - 80);
            $(function() {
                $('.chosen-select').chosen();
                $('.chosen-select-deselect').chosen({
                    allow_single_deselect: true
                });
            });
        });
    </script>

<style>
  <?php if($edit[0]['Activo']==0)   { ?>.fa-edit   {display: none;}<?php } ?>
  <?php if($borrar[0]['Activo']==0) { ?>.fa-eraser {display: none;}<?php } ?>
</style>