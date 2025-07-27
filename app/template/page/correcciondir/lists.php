<?php
include_once $_SERVER['DOCUMENT_ROOT']."/app/host.php";
$listaProv = new \Proveedores\Proveedores();
$listaTC = new \TipoCliente\TipoCliente();
$listaZona = new \Zona\Zona();
$listaCliente = new \Clientes\Clientes();
$listaAlmacen = new \AlmacenP\AlmacenP();
$almacenes = new \AlmacenP\AlmacenP();
if(!isNikken()){
    $codDaneSql = \db()->prepare("SELECT *  FROM c_dane ");
    $codDaneSql->execute();
    $codDane = $codDaneSql->fetchAll(PDO::FETCH_ASSOC);
}

$vere = \db()->prepare("select * from t_profiles as a where id_menu=18 and id_submenu=37 and id_role='".$_SESSION["perfil_usuario"]."'");
$vere->execute();
$ver = $vere->fetchAll(PDO::FETCH_ASSOC);


$agre = \db()->prepare("select * from t_profiles as a where id_menu=18 and id_submenu=38 and id_role='".$_SESSION["perfil_usuario"]."'");
$agre->execute();
$ag = $agre->fetchAll(PDO::FETCH_ASSOC);


$edita = \db()->prepare("select * from t_profiles as a where id_menu=18 and id_submenu=39 and id_role='".$_SESSION["perfil_usuario"]."'");
$edita->execute();
$edit = $edita->fetchAll(PDO::FETCH_ASSOC);


$borra = \db()->prepare("select * from t_profiles as a where id_menu=18 and id_submenu=40 and id_role='".$_SESSION["perfil_usuario"]."'");
$borra->execute();
$borrar = $borra->fetchAll(PDO::FETCH_ASSOC);



// MOD 18

// VER 37
// AGREGAR 38
// EDITAR 39
// BORRAR 40

?>

    <!-- Mainly scripts -->
    <script src="/js/jquery-2.1.1.js"></script>
    <script src="/js/bootstrap.min.js"></script>

    <script src="/js/plugins/metisMenu/jquery.metisMenu.js"></script>
    <script src="/js/plugins/slimscroll/jquery.slimscroll.min.js"></script>
    <link href="/css/plugins/sweetalert/sweetalert.css" rel="stylesheet">
    <script src="/js/plugins/footable/footable.all.min.js"></script>
    <!-- Peity -->
    <script src="/js/plugins/peity/jquery.peity.min.js"></script>

    <!-- jqGrid -->
    <script src="/js/plugins/jqGrid/i18n/grid.locale-es.js"></script>
    <script src="/js/plugins/jqGrid/jquery.jqGrid.min.js"></script>

    <!-- Custom and plugin javascript -->
    <script src="/js/inspinia.js"></script>
    <script src="/js/plugins/pace/pace.min.js"></script>
    <script src="/js/plugins/validate/jquery.validate.min.js"></script>
    <script src="/js/plugins/jquery-ui/jquery-ui.min.js"></script>
    <script src="/js/plugins/sweetalert/sweetalert.min.js"></script>
    <script src="/js/plugins/ladda/spin.min.js"></script>
    <script src="/js/plugins/ladda/ladda.min.js"></script>
    <script src="/js/plugins/ladda/ladda.jquery.min.js"></script>

    <!-- Select -->

    <script src="/js/plugins/staps/jquery.steps.min.js"></script>
    <script src="/js/plugins/chosen/chosen.jquery.js"></script>
    <script src="/js/plugins/iCheck/icheck.min.js"></script>



    <link href="/css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">
    <link href="/css/plugins/chosen/chosen.css" rel="stylesheet">
    <link href="/css/plugins/footable/footable.core.css" rel="stylesheet">
    <link href="/css/plugins/iCheck/custom.css" rel="stylesheet">


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

    <div class="wrapper wrapper-content  animated " id="list">

        <h3>Corrección de datos de envío</h3>

        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                     
                    <div class="ibox-content">
                        <div class="tabbable" id="tabs-131708">
                            <ul class="nav nav-tabs">
                                <li class="active">
                                    <a href="#panel-928563" id="simple" data-toggle="tab">Vista Simple</a>
                                </li>
                            </ul>
                            <div class="tab-content">
                                <div class="tab-pane active" id="panel-928563">
                                    <div class="jqGrid_wrapper">
                                        <table id="grid-table"></table>
                                        <div id="grid-pager"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="importar" role="dialog">
        <div class="vertical-alignment-helper">
            <div class="modal-dialog vertical-align-center">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Importar Clientes</h4>
                    </div>
                    <div class="modal-body">
                        <form id="form-import" action="import" method="post" enctype="multipart/form-data">
                            <div class="form-group">
                                <label>Seleccionar archivo excel para importar</label>
                                <input type="file" name="file" id="file" class="form-control"  required>
                            </div>
                        </form>
                        <div class="col-md-12">
                            <div class="progress" style="display:none">
                                <div class="progress-bar progress-bar-info progress-bar-striped" role="progressbar"
                                aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:0%">
                                    <div class="percent">0%</div >
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div style="text-align: right">
                            <button id="btn-import" type="button" class="btn btn-primary">Importar</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

<div class="modal inmodal" id="modal-destinatario" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content animated bounceInRight">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                    <i class="fa fa-laptop modal-icon"></i>
                    <h4 class="modal-title" id="modaltitle">Destinatario</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Razón Social</label>
                                <input type="text" class="form-control" id="destinatario_razonsocial">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Dirección</label>
                                <input type="text" class="form-control" id="destinatario_direccion">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Colonia</label>
                                <input type="text" class="form-control" id="destinatario_colonia">
                            </div>
                        </div>

                        <div class="col-md-4">                    
                            <div class="form-group">
                                <label>Código Dane / Código Postal</label>
                                <?php if(isset($codDane) && !empty($codDane)): ?>
                                    <select id="destinatario_dane" class="form-control" required="true">
                                        <option value="">Código</option>
                                        <?php foreach( $codDane AS $p ): ?>
                                            <option value="<?php echo $p["cod_municipio"];?>"><?php echo $p["cod_municipio"]." ".$p["des_municipio"]; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php else: ?>
                                    <input type="text" name="destinatario_dane" id="destinatario_dane" class="form-control" required="true">
                                <?php endif; ?> 
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Municipio / Ciudad</label>
                                <input type="text" class="form-control" id="destinatario_ciudad" <?php if(isset($codDane) && !empty($codDane)): ?>readonly<?php endif; ?> >
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Departamento / Estado</label>
                                <input type="text" class="form-control" id="destinatario_estado" <?php if(isset($codDane) && !empty($codDane)): ?>readonly<?php endif; ?> >
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Contacto</label>
                                <input type="text" class="form-control" id="destinatario_contacto">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Teléfono</label>
                                <input type="text" class="form-control" id="destinatario_telefono">
                            </div>
                        </div>

                    </div>
                </div>
            
                <div class="modal-footer">
                    <button type="button" class="btn btn-white" data-dismiss="modal" id="btnCancel">Cerrar</button>
                    <button type="submit" class="btn btn-primary ladda-button" onclick="guardarDestinatario()">Guardar</button>
                </div>
            </div>
        </div>
    </div>

<script>

    $('#btn-import').on('click', function() {

        $('#btn-import').prop('disable', true);
        var bar = $('.progress-bar');
        var percent = $('.percent');
        //var status = $('#status');

        var formData = new FormData();
        formData.append("clave", "valor");

        $.ajax({
            // Your server script to process the upload
            url: '/api/v2/clientes/importar',
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
                setTimeout(
                    function(){if (data.status == 200) {
                        swal("Exito", data.statusText, "success");
                        $('#importar').modal('hide');
                        ReloadGrid();
                    }
                    else {
                        swal("Error", data.statusText, "error");
                    }
                },1000)
            },
        });
    });
</script>


<script type="text/javascript">

    function exportar(){
        $.ajax(
            {
                type: "POST",
                dataType: "json",
                url: '/api/v2/clientes/exportar',
                done: function (data) {
                    var a = document.createElement('a');
                    var url = window.URL.createObjectURL(data);
                    a.href = url;
                    a.download = 'myfile.pdf';
                    a.click();
                    window.URL.revokeObjectURL(url);
                }
            }
        );
    }

</script>

    <script type="text/javascript">
        /**
         * @author Ricardo Delgado.
         * Busca y selecciona el almacen predeterminado para el usuario.
         */
              
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
          
            console.log("cargando tabla");
          
            $(grid_selector).jqGrid({
                url: '/api/correcciondir/lista/index.php',
                datatype: "json",
                shrinkToFit: false,
                height: 'auto',
                postData: {
                    criterio: $("#txtCriterio").val()
                },
                mtype: 'POST',
                colNames: ['Factura/Entrega', 'Fecha', 'Fecha Entrega', 'Num Bultos', 'Cliente','Acciones'],
                colModel: [{name: 'Factura/Entrega',index: 'Factura/Entrega', width: 100, editable: false,  sortable: false}, 
                           {name: 'Fecha',          index: 'Fecha',           width: 110, editable: false,  sortable: false}, 
                           {name: 'Fecha Entrega',  index: 'Fecha Entrega',   width: 160, editable: false,  sortable: false,  hidden: false    }, 
                           {name: 'Num Bultos',     index: 'Num Bultos',      width: 300, editable: false,  sortable: false}, 
                           {name: 'Cliente',        index: 'Cliente',         width: 350, editable: false,  sortable: false}, 
                           {name: 'myac',           index: '',                width: 80,  fixed: true,      sortable: false,  
                            resize: false,          formatter: imageFormat},],
                            rowNum: 30,rowList: [30, 40, 50],pager: pager_selector,sortname: 'fol_folio',viewrecords: true,sortorder: "desc"}
            );

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
                var correl = rowObject[4];
                var url = "x/?serie=" + serie + "&correl=" + correl;
                var url2 = "v/?serie=" + serie + "&correl=" + correl;

                // la funcion downloadxml ejecuta el index.php que esta en la carpeta x pasandole la serie y el correlativo via get
                // la funcion viewPdf ejecuta el index.php que esta en la carpeta v pasandole la serie y el correlativo via get

                var html = '';
                //html += '<a href="#" onclick="editar(\'' + serie + '\')"><i class="fa fa-edit" alt="Editar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
                //html += '<a href="#" onclick="borrar(\'' + serie + '\')"><i class="fa fa-eraser" alt="Borrar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
                html += '<a href="#" onclick="destinatario(\'' + serie + '\')"><i class="fa fa-truck" alt="Ver destinatario"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
                return html;
            }

            $(document).one('ajaxloadstart.page', function(e) {
                $(grid_selector).jqGrid('GridUnload');
                $('.ui-jqdialog').remove();
            });
        });

        //////////////////////////////////////////////////////////hace un reload del grid llamando al php en la carpera "c" para hacer la consulta//////////////////////////////////////////////////////////
        function ReloadGrid() {
            $('#grid-table').jqGrid('clearGridData')
                .jqGrid('setGridParam', {
                    postData: {
                        criterio: $("#txtCriterio").val(),
                        almacen: $("#almacenes").val(),
                        codigo: $("#cp_search").val(),
                    },
                    datatype: 'json',
                    page: 1
                })
                .trigger('reloadGrid', [{
                    current: true
                }]);
        }

        function ReloadGrid1() {
            $('#grid-table2').jqGrid('clearGridData')
                .jqGrid('setGridParam', {
                    postData: {
                        criterio: $("#txtCriterio1").val(),
                    },
                    datatype: 'json',
                    page: 1
                })
                .trigger('reloadGrid', [{
                    current: true
                }]);
        }

        function editar(_codigo) {
            $("#_title").html('<h3>Editar Cliente</h3>');
            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    codigo: _codigo,
                    action: "load"
                },
                beforeSend: function(x) {
                    if (x && x.overrideMimeType) {
                        x.overrideMimeType("application/json;charset=UTF-8");
                    }
                },
                url: '/api/clientes/update/index.php',
                success: function(data) {
                    if (data.success == true) {
                        var destinatarios = data.destinatarios,
                            selector = document.getElementById("direccion_envio");
                        $('#txtClaveCliente').prop('disabled', true);
                        $("#txtClaveCliente").val(data.Cve_Clte);
                        $("#txtClaveClienteProv").val(data.Cve_CteProv);
                        $("#txtRazonSocial").val(data.RazonSocial);
                        $("#txtCalleNumero").val(data.CalleNumero);
                        $("#txtColonia").val(data.Colonia);
                        $("#txtCod").val(data.CodigoPostal);
                        $("txtCod").change();
                        $("#txtDepart").val(data.departamento);
                        $("#txtMunicipio").val(data.des_municipio);
                        $("#txtPais").val(data.Pais);
                        $("#txtRFC").val(data.RFC);
                        $("#txtTelefono1").val(data.Telefono1);
                        $("#txtTelefono2").val(data.Telefono2);
                        $("#txtCondicionPago").val(data.CondicionPago);
                        $("#cboProveedor").val(data.ID_Proveedor);
                        $("#cboTipoCliente").val(data.ClienteTipo);
                        $("#cboZona").val(data.ZonaVenta);
                        $("#almacenp").val(data.almacenp);
                        $("#txtContacto").val(data.contacto);
                        $("#btnCancel").show();

                        destinatarios.forEach(function(el, i) {
                            var option = document.createElement('option');
                            option.innerHTML = el.texto;
                            if (el.id_destinatario === data.id_destinatario) {
                                option.setAttribute('selected', 'selected');
                                option.value = el.value + '|1';
                            } else {
                                option.value = el.value + '|0';
                            }
                            selector.append(option);
                        });
                        console.log(data);
                        if (parseInt(data.id_destinatario) === 0) {
                            $("#usar_direccion").iCheck('check');
                        }
                        $('#list').removeAttr('class').attr('class', '');
                        $('#list').addClass('animated');
                        $('#list').addClass("fadeOutRight");
                        $('#list').hide();

                        $('#FORM').show();
                        $('#FORM').removeAttr('class').attr('class', '');
                        $('#FORM').addClass('animated');
                        $('#FORM').addClass("fadeInRight");

                        $(".chosen-select").trigger("chosen:updated");

                        $("#hiddenAction").val("edit");
                    }
                }
            });
        }
 </script>

    <script>
        function recovery(_codigo) {
            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    id_cliente: _codigo,
                    action: "recovery"
                },
                beforeSend: function(x) {
                    if (x && x.overrideMimeType) {
                        x.overrideMimeType("application/json;charset=UTF-8");
                    }
                },
                url: '/api/clientes/update/index.php',
                success: function(data) {
                    if (data.success == true) {
                        ReloadGrid();
                        ReloadGrid1();
                    }
                }
            });
        }
        var destinatario_folio = '';
        function destinatario( folio ){
            destinatario_folio = folio;
            $.ajax({
                url: '/api/administradorpedidos/lista/index.php',
                dataType: 'json',
                data: {
                    action: 'destinatarioDelPedido',
                    folio: folio
                },
                type: 'POST'
            }).done(function(data) {

                $("#destinatario_razonsocial").val(data.data.razonsocial);
                $("#destinatario_direccion").val(data.data.direccion);
                $("#destinatario_colonia").val(data.data.colonia);
                $("#destinatario_dane").val(data.data.postal);
                $("#destinatario_ciudad").val(data.data.ciudad);
                $("#destinatario_contacto").val(data.data.contacto);
                $("#destinatario_telefono").val(data.data.telefono);
                $("#destinatario_ciudad").val(data.data.ciudad);
                $("#destinatario_estado").val(data.data.estado);
                console.log("data.data.estado = ", data.data.estado);

              $('#modal-destinatario').modal('show');
              //guardarDestinatario();

            });
        }

      function guardarDestinatario(){
            $.ajax({
                url: '/api/administradorpedidos/update/index.php',
                dataType: 'json',
                data: {
                    action: 'guardarDestinatario',
                    correccion: '1',
                    folio: destinatario_folio,
                    razonsocial : $("#destinatario_razonsocial").val(),
                    direccion : $("#destinatario_direccion").val(),
                    colonia : $("#destinatario_colonia").val(),
                    postal : $("#destinatario_dane").val(),
                    ciudad : $("#destinatario_ciudad").val(),
                    contacto : $("#destinatario_contacto").val(),
                    telefono : $("#destinatario_telefono").val(),
                    estado : $("#destinatario_estado").val(),
                },
                type: 'POST'
            }).done(function(data) {

                swal("Éxito", "Datos del destinatario actualizado con éxito", "success");
                $('#modal-destinatario').modal('hide');

            });
        }
    </script>

    <script>
        $(document).ready(function() {
            localStorage.setItem("consecutivo", 0);

            $("#direccion_envio").on('change', function(e) {

                $("#direccion_envio option").each(function(i, e) {
                    var value = e.value;
                    value = value.split("|");
                    value[8] = "0";
                    value = value.join("|");
                    $(`#direccion_envio option[value='${e.value}']`).attr('value', value);
                });

                var value = e.target.value;
                value = value.split("|");
                value[8] = "1";
                value = value.join("|");
                $(`#direccion_envio option[value='${e.target.value}']`).attr('value', value);
            });

          $("div.ui-jqgrid-bdiv").css("max-height", $(".page-content").height());

            $(function() {
                $('.chosen-select').chosen();
                $('.chosen-select-deselect').chosen({
                    allow_single_deselect: true
                });
            });
        });
    </script>
   
    <style>
        <?php if($edit[0]['Activo']==0) {
            ?>.fa-edit {
                display: none;
            }
            <?php
        }
        
        ?><?php if($borrar[0]['Activo']==0) {
            ?>.fa-eraser {
                display: none;
            }
            <?php
        }
        
        ?>
    </style>