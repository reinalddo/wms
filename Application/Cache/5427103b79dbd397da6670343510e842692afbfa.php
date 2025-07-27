<?php
include $_SERVER['DOCUMENT_ROOT']."/app/host.php";


if(!isNikken()){
    $codDaneSql = \db()->prepare("SELECT *  FROM c_dane ");
    $codDaneSql->execute();
    $codDane = $codDaneSql->fetchAll(PDO::FETCH_ASSOC);
}
?>
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
    </style>


    <link href="/css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">
    <link href="/css/plugins/chosen/chosen.css" rel="stylesheet">
    <link href="/css/plugins/sweetalert/sweetalert.css" rel="stylesheet">


    <!-- Mainly scripts -->
    <script src="/js/jquery-2.1.1.js"></script>
    <script src="/js/bootstrap.min.js"></script>
    <script src="/js/plugins/metisMenu/jquery.metisMenu.js"></script>
    <script src="/js/plugins/slimscroll/jquery.slimscroll.min.js"></script>

    <!-- Peity -->
    <script src="/js/plugins/peity/jquery.peity.min.js"></script>

    <!-- Custom and plugin javascript -->
    <script src="/js/inspinia.js"></script>
    <script src="/js/plugins/pace/pace.min.js"></script>

    <script src="/js/plugins/jquery-ui/jquery-ui.min.js"></script>

    <script src="/js/plugins/ladda/spin.min.js"></script>
    <script src="/js/plugins/ladda/ladda.min.js"></script>
    <script src="/js/plugins/ladda/ladda.jquery.min.js"></script>
    <script src="/js/plugins/sweetalert/sweetalert.min.js"></script>
    <script src="/js/plugins/chosen/chosen.jquery.js"></script>


    
<style>

    .table{
        margin-top: 2px;
        border: 1px solid #dddddd;
    }

    .table > thead > tr > th, 
    .table > tbody > tr > th,
    .table > tfoot > tr > th, 
    .table > thead > tr > td, 
    .table > tbody > tr > td, 
    .table > tfoot > tr > td {
        border: 1px solid #dddddd;
        line-height: 1.42857;
        padding: 4px 8px;
        vertical-align: top;
    }
    .table > thead > tr {
        background-color: #f7f7f7;
    }
    .table > thead > tr > th {
        font-size: 12px;
    }
    .table > tfoot > tr {
        background-color: #f7f7f7;
    }
    .table > tfoot > tr > th {
        font-weight: 400;
        font-size: 11px;
        font-family: Verdana,Arial,sans-serif;
    }
    .table > tfoot .sep{
        width: 1px;
        height: 15px;
        background-color: #dddddd;
        display: inline-block;
        margin: 0px 5px;
        position: relative;
        top:3px
    }
    .table .page{
        width: 50px;
        height: 20px;
        border: 1px solid #dddddd;
    }
    .table a {
        color: #000;
    }

</style>



    <!-- Jquery Validate -->
    <script src="/js/plugins/validate/jquery.validate.min.js"></script>

    <!-- Mainly scripts -->

    <div class="wrapper wrapper-content  animated fadeInRight" id="FORM" style="display: none">

        <div class="row">
            <div class="col-md-12">
                <div class="ibox ">
                    <div class="ibox-title">
                        <div class="row">
                            <div class="col-md-4" id="_title">
                                <h3>Agregar Destinatario</h3>
                            </div>
                        </div>
                    </div>
                    <form id="myform">
                        <input type="hidden" id="hiddenAction" name="hiddenAction">
                        <div class="ibox-content">
                            <div class="row">

                                <div class="col-md-6 b-r">
                                    <div class="form-group"><label>Consecutivo *</label> <input id="txtClaveDest" type="text" placeholder="Consecutivo" class="form-control" maxlength="20" readonly></div>
                                    <div class="form-group">
                                        <label>Cliente *</label>
                                        <select class="form-control chosen-select" id="cboCliente" required="true">
                                        <option value="">Seleccione un Cliente</option>
                                        <?php $__currentLoopData = $clientes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($value->Cve_Clte); ?>"><?php echo e($value->RazonSocial); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                    </div>
                                    <div class="form-group"><label>Razón Social *</label> <input id="txtRazonSocial" type="text" placeholder="Razón Social" class="form-control" required="true"></div>
                                    <div class="form-group"><label>Dirección *</label> <input id="txtCalleNumero" type="text" placeholder="Calle y Numero" class="form-control" required="true"></div>
                                    <div class="form-group"><label>Colonia </label> <input id="txtColonia" type="text" placeholder="Colonia" class="form-control"></div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group"><label>Código Dane / Código postal *</label> 
                                        <?php if(isset($codigos_dane) && !empty($codigos_dane)): ?>
                                            <select id="txtCod" class="form-control" required="true" style="width: 100%">
                                                <option value="">Seleccione Código</option>
                                            </select>
                                        <?php else: ?>
                                            <input type="text" name="txtCod" id="txtCod" class="form-control" required="true" style="width: 100%">
                                        <?php endif; ?>
                                    </div>
                                    <div class="form-group"><label>Municipio/Ciudad</label> <input id="txtDepart" type="text" placeholder="Departamento" class="form-control"></div>
                                    <div class="form-group"><label>Departamento/Estado</label> <input id="txtMunicipio" type="text" placeholder="Municipio" class="form-control"></div>
                                    <div class="form-group"><label>Teléfono</label> <input id="txtTelefono" maxlength="14" oninput="this.value = this.value.replace(/[^0-9]/g, '');" type="text" maxlength="15" placeholder="Teléfono" class="form-control"></div>
                                    <div class="form-group"><label>Contacto</label> <input id="txtContacto" type="text" maxlength="15" placeholder="Contacto" class="form-control"></div>

                                    <div class="pull-right">
                                        <a href="#" onclick="cancelar()"><button type="button" class="btn btn-white" id="btnCancel">Cerrar</button></a>&nbsp;&nbsp;
                                        <button type="submit" class="btn btn-primary" id="btnSave">Guardar</button>
                                    </div>

                                </div>
                            </div>

                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="wrapper wrapper-content  animated fadeInRight" id="list">

        <h3>Destinatarios</h3>

        <div class="row">
            <div class="col-md-12">
                <div class="ibox ">
                    <div class="ibox-title">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="almacenes">Almacen:</label>
                                    <select class="form-control" id="almacenes" name="almacenes">
                                    <option value="">Seleccione el Almacen</option>
                                    <?php $__currentLoopData = $almacenes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($value->id); ?>"><?php echo e($value->clave." - ".$value->nombre); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="almacenes">Código Postal:</label> 
                                    <?php if(isset($codigos_dane) && !empty($codigos_dane)): ?>
                                        <select id="codigo" class="form-control" required="true" style="width: 100%">
                                            <option value="">Seleccione Código</option>
                                        </select>
                                    <?php else: ?>
                                        <input type="text" name="codigo" id="codigo" class="form-control" required="true" style="width: 100%">
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="txtCriterio" id="txtCriterio" placeholder="Buscar...">
                                    <div class="input-group-btn">

                                        <button onclick="ReloadGrid()" type="submit" class="btn btn-primary" id="buscarP">
                                        <span class="fa fa-search"></span> Buscar
                                    </button>

                                    </div>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <a href="#" onclick="agregar()"><button class="btn btn-primary" type="button"><i class="fa fa-plus"></i>&nbsp;&nbsp;Nuevo</button></a>

                                <a href="/api/v2/clientes/destinatarios/exportar" target="_blank" class="btn btn-primary pull-right" style="margin-left:15px;"><span class="fa fa-upload"></span> Exportar</a>
                                <button class="btn btn-primary pull-right" style="margin-left:15px;" data-toggle="modal" data-target="#importar"><span class="fa fa-download"></span> Importar</button>

                            </div>
                        </div>
                    </div>
                    <div class="ibox-content">

                        <div class="table-responsive">
                            <table id="dt-detalles" class="table" style="table-layout: auto;width: 100%;">
                                <thead>
                                    <tr>
                                        <th scope="col" style="width: 270px !important; min-width: 270px !important;">Cliente</th>
                                        <th scope="col" style="width: 180px !important; min-width: 180px !important;">Destinatario</th>
                                        <th scope="col" style="width: 270px !important; min-width: 270px !important;">Dirección</th>
                                        <th scope="col" style="width: 250px !important; min-width: 250px !important;">Colonia</th>
                                        <th scope="col" style="width: 100px !important; min-width: 100px !important;">Código Postal</th>
                                        <th scope="col" style="width: 200px !important; min-width: 200px !important;">Ciudad</th>
                                        <th scope="col" style="width: 200px !important; min-width: 200px !important;">Estado</th>
                                        <th scope="col" style="width: 200px !important; min-width: 200px !important;">Contacto</th>
                                        <th scope="col" style="width: 100px !important; min-width: 100px !important;">Teléfono</th>
                                        <th scope="col" style="width: 80px !important; min-width: 80px !important;">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-info"></tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="22">
                                            Página 
                                            <input class="page" type="text" value="1" style="text-align:center"/> 
                                            de <span class="total_pages">0</span>
                                            <div class="sep"></div>
                                            <select class="count">
                                                <option value="10">10</option>
                                                <option value="20">30</option>
                                                <option value="30">30</option>
                                            </select>
                                            <div class="sep"></div>
                                            Mostrando <span class="from">0</span> - <span class="to">0</span> de <span class="total">0</span>
                                        </th>
                                    </tr>
                                </tfoot>
                            </table>
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
                        <h4 class="modal-title">Importar destinatarios</h4>
                    </div>
                    <div class="modal-body">
                        <form id="form-import" action="import" method="post" enctype="multipart/form-data">
                            <div class="form-group">
                                <label>Seleccionar archivo excel para importar</label>
                                <input type="file" name="file" id="file" class="form-control" required>
                            </div>
                        </form>
                        <div class="col-md-12">
                            <div class="progress" style="display:none">
                                <div class="progress-bar progress-bar-info progress-bar-striped" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:0%">
                                    <div class="percent">0%</div>
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
                url: '/clientes/destinatarios/importar',
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
                                percent.html(percentComplete + '%');
                                if (percentComplete === 100) {
                                    setTimeout(function() {
                                        $('.progress').hide();
                                    }, 2000);
                                }
                            }
                        }, false);
                    }
                    return myXhr;
                },
                success: function(data) {
                    setTimeout(
                        function() {
                            if (data.status == 200) {
                                swal("Exito", data.statusText, "success");
                                $('#importar').modal('hide');
                                ReloadGrid();
                            } else {
                                swal("Error", data.statusText, "error");
                            }
                        }, 1000)
                },
            });
        });
    </script>



    <?php if(isset($codigos_dane) && !empty($codigos_dane)): ?>
        <script>
            var postales = [];
        <?php $__currentLoopData = $codigos_dane; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            postales.push({
                '<?php echo e($value["cod_municipio"]); ?>' : '<?php echo e($value["des_municipio"]); ?>'
            });
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        function asyncSqrt(callback) {
            setTimeout(function() {
                callback();
            }, 0 | Math.random() * 100);
        }

        asyncSqrt(function() {
            $.each( postales, function(index, item){
                $('#txtCod').append('<option value="'+Object.keys(item)+'">'+index+' - '+item[Object.keys(item)]+'</option>')
                $('#codigo').append('<option value="'+Object.keys(item)+'">'+index+' - '+item[Object.keys(item)]+'</option>')
            });
        });
        </script>
    <?php endif; ?>


    <script type="text/javascript">
        /**
         * @author  Ricardo Delgado.
         * Busca y selecciona el almacen predeterminado para el usuario.
         */
        function almacenPrede() {
            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    idUser: '<?php echo $_SESSION["id_user"]?>',
                    action: 'search_almacen_pre'
                },
                beforeSend: function(x) {
                    if (x && x.overrideMimeType) {
                        x.overrideMimeType("application/json;charset=UTF-8");
                    }
                },
                url: '/api/almacenPredeterminado/index.php',
                success: function(data) {
                    if (data.success == true) {
                        document.getElementById('almacenes').value = data.codigo.id;
                        ReloadGrid();
                    }
                },
                error: function(res) {
                    window.console.log(res);
                }
            });
        }
    </script>


    <script type="text/javascript">

    
        //////////////////////////////////////////////////////////hace un reload del grid llamando al php en la carpera "c" para hacer la consulta//////////////////////////////////////////////////////////
        function ReloadGrid() {
            if ($("#almacenes").val() !== '') {
                
                $.ajax({
                    type: "GET",
                    dataType: "JSON",
                    url: '/api/v2/destinatarios',
                    data: {
                        criterio: $("#txtCriterio").val(),
                        almacen: $("#almacenes").val(),
                        codigo: $("#codigo").val(),
                    },
                    success: function(data) {
                        if (data.status == 200) {
                            $('#dt-detalles .total_pages').text(data.total_pages);
                            $('#dt-detalles .page').text(data.page);
                            $('#dt-detalles .from').text(data.from);
                            $('#dt-detalles .to').text(data.to);
                            $('#dt-detalles .total').text(data.total);

                            var row = '', i = 0;
                            $.each(data.data, function(index, item){
                                i++;
                                row += '<tr>'+
                                            '<td>'+item.cliente+'</td>'+
                                            '<td>'+item.destinatario+'</td>'+
                                            '<td>'+item.direccion+'</td>'+
                                            '<td>'+item.colonia+'</td>'+
                                            '<td align="center">'+item.postal+'</td>'+
                                            '<td>'+item.ciudad+'</td>'+                                            
                                            '<td>'+item.estado+'</td>'+
                                            '<td>'+item.contacto+'</td>'+
                                            '<td>'+item.telefono+'</td>'+
                                            '<td align="center">'+imageFormat(item.id)+'</td>'+
                                        '</tr>';                            
                            });
                            $('#dt-detalles tbody').html(row);
                            
                        } else {
                            swal({
                                title: "Error",
                                text: "No se pudo planificar el inventario",
                                type: "error"
                            });
                        }
                    }
                });
            

            } else {
                swal("Error", "Debe seleccionar al menos el almacén", "error");
            }
        }


        function imageFormat(id) {

            var html = '';
            html += '<a href="#" onclick="editar(\'' + id + '\')"><i class="fa fa-edit" alt="Editar"></i></a>&nbsp;';
            html += '<a href="#" onclick="borrar(\'' + id + '\')"><i class="fa fa-eraser" alt="Borrar"></i></a>';
            return html;
        }

        function borrar(_codigo) {
            swal({
                    title: "¿Está seguro que desea borrar el destinatario?",
                    text: "Está a punto de borrar un destinatario y esta acción no se puede deshacer",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    confirmButtonText: "Borrar",
                    cancelButtonText: "Cancelar",
                    closeOnConfirm: true
                },
                function() {
                    $.ajax({
                        type: "POST",
                        dataType: "json",
                        data: {
                            destinatario: _codigo,
                            action: "delete"
                        },
                        beforeSend: function(x) {
                            if (x && x.overrideMimeType) {
                                x.overrideMimeType("application/json;charset=UTF-8");
                            }
                        },
                        url: '/api/destinatarios/update/index.php',
                        success: function(data) {
                            if (data.success == true) {
                                ReloadGrid();
                            }
                        }
                    });
                });


        }

        function editar(_codigo) {
            $("#_title").html('<h3>Editar Destinatario</h3>');
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
                url: '/api/destinatarios/update/index.php',
                success: function(data) {
                    if (data.success == true) {
                        $('#txtClaveDest').prop('disabled', true);
                        $("#txtClaveDest").val(data.id);
                        $("#txtRazonSocial").val(data.RazonSocial);
                        $("#txtCalleNumero").val(data.Direccion);
                        $("#txtColonia").val(data.Colonia);
                        $("#txtCod").val(data.CodigoPostal);
                        $("#txtCod").change();
                        $("#txtTelefono").val(data.Telefono);
                        $("#txtContacto").val(data.Contacto);
                        $("#cboCliente").val(data.Cve_Clte);
                        $(".chosen-select").trigger("chosen:updated");
                        $("#btnCancel").show();

                        $('#list').removeAttr('class').attr('class', '');
                        $('#list').addClass('animated');
                        $('#list').addClass("fadeOutRight");
                        $('#list').hide();

                        $('#FORM').show();
                        $('#FORM').removeAttr('class').attr('class', '');
                        $('#FORM').addClass('animated');
                        $('#FORM').addClass("fadeInRight");

                        $("#hiddenAction").val("edit");
                    }
                }
            });
        }

        function cancelar() {
            $(':input', '#myform')
                .removeAttr('checked')
                .removeAttr('selected')
                .not(':button, :submit, :reset, :hidden, :radio, :checkbox')
                .val('');
            $('#FORM').removeAttr('class').attr('class', '');
            $('#FORM').addClass('animated');
            $('#FORM').addClass("fadeOutRight");
            $('#FORM').hide();

            $('#list').removeAttr('class').attr('class', '');
            $('#list').addClass('animated');
            $('#list').addClass("fadeInRight");
            $('#list').show();

        }

        function agregar() {
            $("#_title").html('<h3>Agregar Destinatario</h3>');

            $.ajax({
                url: '/api/clientes/lista/index.php',
                dataType: 'json',
                data: {
                    action: 'obtenerClaveDestinatario'
                },
                type: 'GET'
            }).done(function(data) {
                if (data.clave) {
                    $("#txtClaveDest").val(data.clave);
                }
            });

            $("#txtDepart").val("");
            $("#txtMunicipio").val("");

            $(':input', '#myform')
                .removeAttr('checked')
                .removeAttr('selected')
                .not(':button, :submit, :reset, :hidden, :radio, :checkbox')
                .val('');

            $('#list').hide();
            $('#FORM').show();
            $('#FORM').removeAttr('class').attr('class', '');
            $('#FORM').addClass('animated');
            $('#FORM').addClass("fadeInRight");
            $("#hiddenAction").val("add");

        }

        $("#btnSave").on("click", function(e) {
            e.preventDefault();
            var action = $("#hiddenAction").val();
            $("#btnCancel").hide();

            if ($("#cboCliente").val() == "") {
                swal('Error', 'Por favor seleccione un cliente', 'error');
            } else if ($("#txtRazonSocial").val() == "") {
                swal('Error', 'Por favor indique una razon social', 'error');
            } else if ($("#txtCalleNumero").val() == "") {
                swal('Error', 'Por favor indique una direccion', 'error');
            } else if ($("#txtCod").val() == "") {
                swal('Error', 'Por favor seleccione un codigo postal', 'error');
            } else {
                if (action === "add") {
                    $.ajax({
                        type: "POST",
                        dataType: "json",
                        data: {
                            RazonSocial: $("#txtRazonSocial").val(),
                            Direccion: $("#txtCalleNumero").val(),
                            Colonia: $("#txtColonia").val(),
                            CodigoPostal: $("#txtCod").val(),
                            Ciudad: $("#txtDepart").val(),
                            Estado: $("#txtMunicipio").val(),
                            Telefono: $("#txtTelefono").val(),
                            Contacto: $("#txtContacto").val(),
                            Cve_Clte: $("#cboCliente").val(),
                            action: action
                        },
                        beforeSend: function(x) {
                            if (x && x.overrideMimeType) {
                                x.overrideMimeType("application/json;charset=UTF-8");
                            }
                        },
                        url: '/api/destinatarios/update/index.php',
                        success: function(data) {
                            if (data.success) {
                                cancelar();
                                ReloadGrid();
                            } else {
                                $("#btnCancel").show();
                            }
                        }
                    });
                } else if (action === 'edit') {
                    $.ajax({
                        type: "POST",
                        dataType: "json",
                        data: {
                            id: $("#txtClaveDest").val(),
                            RazonSocial: $("#txtRazonSocial").val(),
                            Direccion: $("#txtCalleNumero").val(),
                            Colonia: $("#txtColonia").val(),
                            CodigoPostal: $("#txtCod").val(),
                            Ciudad: $("#txtDepart").val(),
                            Estado: $("#txtMunicipio").val(),
                            Telefono: $("#txtTelefono").val(),
                            Contacto: $("#txtContacto").val(),
                            Cve_Clte: $("#cboCliente").val(),
                            action: action
                        },
                        beforeSend: function(x) {
                            if (x && x.overrideMimeType) {
                                x.overrideMimeType("application/json;charset=UTF-8");
                            }
                        },
                        url: '/api/destinatarios/update/index.php',
                        success: function(data) {
                            if (data.success) {
                                ReloadGrid();
                                cancelar();
                            } else {
                                $("#btnCancel").show();
                            }
                        }
                    });
                }
            }

        });
    </script>
    <script>
        $(document).ready(function() {
            $('.chosen-select').chosen();
            $('.chosen-select-deselect').chosen({
                allow_single_deselect: true
            });
            $("#txtDepart").prop("disabled", true);
            $("#txtMunicipio").prop("disabled", true);

            $("#txtCod").change(function() {
                $.ajax({
                    type: "POST",
                    dataType: "json",
                    data: {
                        codigo: $("#txtCod").val(),
                        action: "getDane"
                    },
                    beforeSend: function(x) {
                        if (x && x.overrideMimeType) {
                            x.overrideMimeType("application/json;charset=UTF-8");
                        }
                    },
                    url: "/api/destinatarios/update/index.php",
                    success: function(data) {
                        if (data.success == true) {
                            $("#txtDepart").val(data.departamento);
                            $("#txtMunicipio").val(data.municipio);
                        }
                    }
                });
            });

        });
    </script>
    <script type="text/javascript">
        $("#txtCriterio").keyup(function(event) {
            if (event.keyCode == 13) {
                $("#buscarP").click();
            }
        });

            $('#dt-detalles .page').keypress(function(e) {
        if(e.which == 13) {
            filtrar()
        }
    });

    $('#dt-detalles .count').change(function() {       
        filtrar()        
    });
    
    </script>

