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

<div class="wrapper wrapper-content  animated " id="list">
    <h3>Existencia Ubicación</h3>
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
                                @foreach ($almacenes as $value)
                                    <option value="{{ $value->id }}">{{ $value->clave .' - '. $value->nombre }}</option>
                                @endforeach
                            </select>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-group">
                                <label for="email">Zona de Almacenaje</label>
                                <select name="zona" id="zona" class="chosen-select form-control">
                                <option value="">Seleccione Zona</option>
                            </select>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-group">
                                <label for="email">Articulo</label>
                                <select name="articulo" id="articulo" class="chosen-select form-control">
                            <option value="">Seleccione Articulo</option>
                            </select>
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
                    <table id="example" class="table table-hover table-striped no-margin">
                        <thead>
                            <tr>
                                <th>Almacén</th>
                                <th>Zona de Almacenaje</th>
                                <th>Pasillo</th>
                                <th>Rack</th>
                                <th>Nivel</th>
                                <th>Sección</th>
                                <th>Ubicación</th>
                                <th>Clave</th>
                                <th>Descripción</th>
                                <th>Lote</th>
                                <th>Caducidad</th>
                                <th>N. Serie</th>
                                <th>Cantidad</th>
                                <th>Entrada</th>
                                <th>Responsable</th>
                                <th>Salida</th>
                                <th>Responsable</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>Almacén</th>
                                <th>Zona de Almacenaje</th>
                                <th>Pasillo</th>
                                <th>Rack</th>
                                <th>Nivel</th>
                                <th>Sección</th>
                                <th>Ubicación</th>
                                <th>Clave</th>
                                <th>Descripción</th>
                                <th>Lote</th>
                                <th>Caducidad</th>
                                <th>N. Serie</th>
                                <th>Cantidad</th>
                                <th>Entrada</th>
                                <th>Responsable</th>
                                <th>Salida</th>
                                <th>Responsable</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<script>
    $(document).ready(function() {
        $(function() {
            $('.chosen-select').chosen();
            $('.chosen-select-deselect').chosen({
                allow_single_deselect: true
            });
        });

        $('#example').DataTable({
            "processing": true,
            dom: 'Bfrtip',
            buttons: [{
                extend: 'excelHtml5',
                title: 'Existencias en ubicación'
            }, {
                extend: 'pdfHtml5',
                title: 'Existencias en ubicación'
            }],
            "bFilter": false,
            responsive: true,
            "language": {
                "lengthMenu": "Mostrando _MENU_ registros",
                "zeroRecords": "Sin Registros - :(!!",
                "info": "Pagina _PAGE_ de _PAGES_",
                "infoEmpty": "No Existen Registros",
                "infoFiltered": "(Filtrando de un total de _MAX_ registros)",
                "sSearch": "Filtrar:",
                "sProcessing": "Cargando...",

                "oPaginate": {
                    "sNext": "Sig",
                    "sPrevious": "Ant",
                    "sLast": "Ultimo",
                    "sFirst": "Primero",
                }
            }
        });

        $(".dt-button.buttons-excel").attr('class', 'btn btn-primary btn-sm bt');
        $(".dt-button.buttons-pdf").attr('class', 'btn btn-danger btn-sm bt');
        $(".dt-buttons a.btn.btn-primary.btn-sm.bt").unbind();
        $('.dt-buttons a.btn.btn-danger.btn-sm.bt').unbind();
    });


    function table() {
        $('#example').DataTable({
            "processing": true,
            dom: 'Bfrtip',
            buttons: [{
                extend: 'excelHtml5',
                title: 'Existencia en ubicación'
            }, {
                extend: 'pdfHtml5',
                title: 'Existencia en ubicación'
            }],
            responsive: true,
            "language": {
                "lengthMenu": "Mostrando _MENU_ registros",
                "zeroRecords": "Sin Registros - :(!!",
                "info": "Pagina _PAGE_ de _PAGES_",
                "infoEmpty": "No Existen Registros",
                "infoFiltered": "(Filtrando de un total de _MAX_ registros)",
                "sSearch": "Filtrar:",
                "sProcessing": "Cargando...",
                "oPaginate": {
                    "sNext": "Sig",
                    "sPrevious": "Ant",
                    "sLast": "Ultimo",
                    "sFirst": "Primero",
                }
            },
            "processing": true,
            "bDestroy": true,
            "serverSide": true,
            "bFilter": false,
            "columns": [{
                "data": "almacen"
            }, {
                "data": "zona"
            }, {
                "data": "pasillo"
            }, {
                "data": "rack"
            }, {
                "data": "nivel"
            }, {
                "data": "seccion"
            }, {
                "data": "ubicacion"
            }, {
                "data": "clave"
            }, {
                "data": "descripcion"
            }, {
                "data": "lote"
            }, {
                "data": "caducidad"
            }, {
                "data": "nserie"
            }, {
                "data": "cantidad"
            }, {
                "data": "cuarentena_entrada"
            }, {
                "data": "cuarentena_usuario_entrada"
            }, {
                "data": "cuarentena_salida"
            }, {
                "data": "cuarentena_usuario_saldia"
            }],

            "ajax": {
                "url": "/api/qacuarentena/main.php",
                "type": "GET",
                "data": {
                    action: 'all',
                    lote: $("#lote").val(),
                    almacen: $("#almacen").val(),
                    articulo: $("#producto").val(),
                    zona: $("#zona").val(),
                }
            },

        });

        $(".dt-button.buttons-excel").attr('class', 'btn btn-primary btn-sm bt');
        $(".dt-button.buttons-pdf").attr('class', 'btn btn-danger btn-sm bt');
        //Excel

        $(".dt-buttons a.btn.btn-primary.btn-sm.bt").unbind().bind('click', function(e) {
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
        });

        //PDF
        $('.dt-buttons a.btn.btn-danger.btn-sm.bt').unbind().bind('click', function(e) {
            var title = 'Reporte de Existencia por Ubicación';
            var cia = <?php echo $_SESSION['cve_cia'] ?>;
            var content = '';

            $.ajax({
                url: "/api/reportes/update/index.php",
                type: "POST",
                data: {
                    "almacen": $("#almacen").val(),
                    "zona": $("#zona").val(),
                    "articulo": $("#articulo").val(),
                    "action": "existenciaUbicacion"

                },
                success: function(data, textStatus, xhr) {
                    var content_wrapper = document.createElement('div');
                    var table = document.createElement('table');
                    table.style.width = "100%";
                    table.style.borderSpacing = "0";
                    table.style.borderCollapse = "collapse";
                    var thead = document.createElement('thead');
                    var tbody = document.createElement('tbody');
                    var head_content = `<tr>
                                        <th style="border: 1px solid #ccc">Almacén</th>
                                        <th style="border: 1px solid #ccc">Zona de Almacenaje</th>
                                        <th style="border: 1px solid #ccc">Pasillo</th>
                                        <th style="border: 1px solid #ccc">Rack</th>
                                        <th style="border: 1px solid #ccc">Nivel</th>
                                        <th style="border: 1px solid #ccc">Sección</th>
                                        <th style="border: 1px solid #ccc">Ubicación</th>
                                        <th style="border: 1px solid #ccc">Clave</th>
                                        <th style="border: 1px solid #ccc">Descripción</th>
                                        <th style="border: 1px solid #ccc">Lote</th>
                                        <th style="border: 1px solid #ccc">Caducidad</th>
                                        <th style="border: 1px solid #ccc">N. Serie</th>
                                        <th style="border: 1px solid #ccc">Cantidad</th>
                                        <th style="border: 1px solid #ccc">Proveedor</th>
                                        <th style="border: 1px solid #ccc">Fecha de Ingreso</th>
                                        <th style="border: 1px solid #ccc">Tipo Ubicación</th>
                                    </tr>`;
                    var body_content = '';
                    var data = JSON.parse(data).data;

                    data.forEach(function(item, index) {
                        body_content += `
                        <tr>
                            <td style="border: 1px solid #ccc">${item.almacen}</td>
                            <td style="border: 1px solid #ccc">${item.zona}</td>
                            <td style="border: 1px solid #ccc">${item.pasillo}</td>
                            <td style="border: 1px solid #ccc">${item.rack}</td>
                            <td style="border: 1px solid #ccc">${item.nivel}</td>
                            <td style="border: 1px solid #ccc">${item.seccion}</td>
                            <td style="border: 1px solid #ccc">${item.ubicacion}</td>
                            <td style="border: 1px solid #ccc">${item.clave}</td>
                            <td style="border: 1px solid #ccc">${item.descripcion}</td>
                            <td style="border: 1px solid #ccc">${item.lote}</td>
                            <td style="border: 1px solid #ccc">${item.caducidad}</td>
                            <td style="border: 1px solid #ccc">${item.nserie}</td>
                            <td style="border: 1px solid #ccc">${item.cantidad}</td>
                            <td style="border: 1px solid #ccc">${item.proveedor}</td>
                            <td style="border: 1px solid #ccc">${item.fecha_ingreso}</td>
                            <td style="border: 1px solid #ccc">${item.tipo_ubicacion}</td>
                        </tr>
                    `;
                    });

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
        var almacen = $(this).val();
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                cve_almac: almacen,
                action: "getArticulosYZonasAlmacenConExistencia"
            },
            beforeSend: function(x) {
                if (x && x.overrideMimeType) {
                    x.overrideMimeType("application/json;charset=UTF-8");
                }
            },
            url: '/api/ubicaciones/update/index.php',
            success: function(data) {

                var options_articulos = $("#articulo");
                options_articulos.empty();
                options_articulos.append(new Option("Seleccione Artículo", ""));

                var options_zonas = $("#zona");
                options_zonas.empty();
                options_zonas.append(new Option("Seleccione Zona", ""));

                for (var i = 0; i < data.articulos.length; i++) {
                    options_articulos.append(new Option(data.articulos[i].id_articulo + " " + data.articulos[i].articulo, data.articulos[i].id_articulo));
                }

                for (var i = 0; i < data.zonas.length; i++) {
                    options_zonas.append(new Option(data.zonas[i].clave + " " + data.zonas[i].descripcion, data.zonas[i].clave));
                }
                $("#articulo").trigger("chosen:updated");
                $("#zona").trigger("chosen:updated");

            }

        });


    });

    $("#search").click(function() {
        if ($("#almacen").val() == "")
            return;
        table();
    });
</script>