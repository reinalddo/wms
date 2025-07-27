<?php

//$listaArtic = new \Articulos\Articulos();
session_start();

$listaUnidadMedida = new \UnidadesMedida\UnidadesMedida();
$comboUnidadesMedida = '<select id="###########UID###########" class="chosen-select form-control"><option value="">Seleccione Unidad de Medida</option>';
foreach( $listaUnidadMedida->getAll() AS $a ):
    $comboUnidadesMedida .= '<option value="'.$a->cve_umed.'">'.$a->cve_umed." ".$a->des_umed.'</option>';
endforeach;
$comboUnidadesMedida .= '</select>';

$id_almacen = $_SESSION['id_almacen'];

$sql = "SELECT a.cve_articulo, a.des_articulo, COUNT(t.Cve_ArtComponente) AS componentes
        FROM t_artcompuesto t
        LEFT JOIN c_articulo a ON a.cve_articulo = t.Cve_ArtComponente
        LEFT JOIN Rel_Articulo_Almacen ra ON ra.Cve_Articulo = a.cve_articulo 
        WHERE a.Compuesto = 'S' AND ra.Cve_Almac = $id_almacen AND a.Activo = 1
        GROUP BY cve_articulo
        ORDER BY componentes DESC;";
$rs = mysqli_query(\db2(), $sql);

$comboArticuloParte = '<select id="txtArticuloParte" class="chosen-select form-control"><option value="">Seleccione Código del Producto</option>';
while( $a = mysqli_fetch_object($rs)) {
    $comboArticuloParte .= '<option value="' . $a->cve_articulo . '">' . $a->cve_articulo . " " . $a->des_articulo . ' ('.$a->componentes.') </option>';
}
$comboArticuloParte .= '</select>';

?>
<link href="/css/plugins/datapicker/datepicker3.css" rel="stylesheet">
<link href="/css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">
<link href="/css/plugins/chosen/chosen.css" rel="stylesheet">
<link href="/css/plugins/sweetalert/sweetalert.css" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="//cdn.datatables.net/plug-ins/9dcbecd42ad/integration/bootstrap/3/dataTables.bootstrap.css">
<link rel="stylesheet" type="text/css" href="//cdn.datatables.net/responsive/1.0.3/css/dataTables.responsive.css">
<!--<link href="/css/jquery.auto-complete.css" rel="stylesheet"/>-->

<style>
    #list {
        width: 100%;
        height: 100%;
        position: absolute;
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
</style>
<input type="hidden" name="id_almacen" id="id_almacen" value="<?php echo $id_almacen; ?>">
<div class="modal" id="pleaseWaitDialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" id="headModal">
                <h2>Cargando...</h2>
            </div>
            <div class="modal-body">
                <div class="progress progress-striped active">
                    <div class="progress-bar" style="width: 100%;"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal" id="pleaseWaitSaving" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" id="headModal">
                <h2>Guardando...</h2>
            </div>
            <div class="modal-body">
                <div class="progress progress-striped active">
                    <div class="progress-bar" style="width: 100%;"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="conModal" role="dialog">
    <div class="vertical-alignment-helper">
        <div class="modal-dialog vertical-align-center modal-lg" style="max-width: 90% !important;">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">B&uacute;squeda de Art&iacute;culos</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="input-group">
                            <input name="txtBusqueda" id="txtBusqueda" type="text" class="form-control input-sm"/>
                            <input name="hiddenuid" id="hiddenuid" type="hidden"/>
                            <div class="input-group-btn">
                                <button class="btn btn-sm btn-primary" type="button" onclick="doSearch()"><i class="fa fa-search"></i>&nbsp;&nbsp;Buscar</button>
                            </div>
                        </div>
                    </div>
                    <table style="width: 100%" class="table table-striped table-bordered table-hover" id="dataTableProductos">
                        <thead>
                        <tr>
                            <th>C&oacute;digo</th>
                            <th>Descripci&oacute;n</th>
                            <th style="display: none;">Granel</th>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="wrapper wrapper-content  animated fadeInRight" id="FORM">

    <div class="row">
        <div class="col-md-12">
            <div class="ibox ">
                <div class="ibox-title">
                    <div class="row">
                        <div class="col-md-4" id="_title">
                            <h4>Crear / Modificar Lista de Materiales (BOM)<br><br>Nuevos Productos / Ofertas (Kitting)</h4>
                        </div>
                        <div class="col-md-2" id="_title">
                            <button class="btn btn-primary permiso_registrar" data-toggle="modal" data-target="#importar"><span class="fa fa-download"></span> Importar</button>
                        </div>
                        <div class="col-md-2" id="_title">
                            <a href="#" class="btn btn-primary pull-left" id="exportar"><span class="fa fa-upload"></span> Exportar</a>
                        </div>
                        <div class="col-md-2" id="_title">
                            <?php /* ?><a href="#" class="btn btn-primary pull-left" id="exportarTodo"><span class="fa fa-upload"></span> Exportar Todo</a><?php */ ?>
                            <a href="/api/v2/BOM_Articulos/exportartodo" target="_blank" class="btn btn-primary pull-left"><span class="fa fa-upload"></span> Exportar Todo</a>

                        </div>
                    </div>
                </div>
                <form id="myform">
                    <div class="ibox-content">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>No. de Parte de Producto / Kit *</label>
                                    <!--input name="txtArticuloParte" id="txtArticuloParte" type="text" class="form-control" maxlength="20"/-->
                                    <?php echo $comboArticuloParte ?>
                                </div>
                            </div>
                            <div class="col-md-8" style="margin-top: 5px;">
                                </br>
                                <button id ="editar" class="btn btn-primary" type="button"><i class="fa fa-check"></i>&nbsp;&nbsp;Aceptar</button>
                            </div>
                        </div>

                        <br>

                        <div class="row" id="_rowPaso3" style="display: none; overflow-x: scroll;">
                            <div class="row">
                                <div class="col-md-4" id="_title">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<label>Agregar / Editar Componentes</label>
                                </div>
                            </div>

                            <div class="col-xs-12">
                                <br>
                                <table class="table table-striped table-bordered table-hover" id="editable" >
                                    <thead>
                                    <tr>
                                        <th id="_titleArt"></th>
                                        <th>Producto</th>
                                        <th>Descripci&oacute;n</th>
                                        <th>Cantidad Requerida*</th>
                                        <th>Unidad de Medida*</th>
                                        <th style="display: none;">Granel</th>
                                    </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>

                            <div class="col-md-12">
                                <div class="pull-right">
                                    <a href="#" onclick="cancelar()"><button type="button" class="btn btn-white" id="btnCancel">Cancelar</button></a>&nbsp;&nbsp;
                                    <button type="button" class="btn btn-primary permiso_registrar" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'></i> Guardando..." id="btnSave">Guardar</button>
                                </div>
                            </div>


                        </div>
                        <input type="hidden" id="hiddenAction">
                        <input type="hidden" id="hiddenID_Aduana">

                    </div>
                </form>
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
                        <h4 class="modal-title">Importar Productos Compuestos</h4>
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
                      <div class="col-md-6" style="text-align: left">
                        <button id="btn-layout" type="button" class="btn btn-primary">Descargar Layout</button>
                      </div>
                        <div class="col-md-6" style="text-align: right">
                            <button id="btn-import" type="button" class="btn btn-primary">Importar</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

</div>

<!-- Mainly scripts -->
<script src="/js/jquery-2.1.1.js"></script>
<script src="/js/moment.js"></script>
<script src="/js/moment-with-locales.js"></script>
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

<!-- Select -->
<script src="/js/plugins/chosen/chosen.jquery.js"></script>
<!-- Data picker -->
<script src="/js/plugins/datapicker/bootstrap-datepicker.js"></script>
<script src="/js/plugins/sweetalert/sweetalert.min.js"></script>

<script type="text/javascript" src="https://cdn.datatables.net/1.10.4/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/responsive/1.0.3/js/dataTables.responsive.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/plug-ins/9dcbecd42ad/integration/bootstrap/3/dataTables.bootstrap.js"></script>

<!--<script src="/js/jquery.auto-complete.js"></script>-->

<script type="text/javascript">
    function cancelar() {
        $('#editar').prop('disabled', false);
        $('#btnSave').prop('disabled', false);
        $('#_rowPaso3').hide();
        $('#editable tbody tr').remove();
        $("#txtArticuloParte").chosen().val("");
        $("#txtArticuloParte").chosen().trigger("chosen:updated");
        $("#txtArticuloParte").prop('disabled', false).trigger('chosen:updated');
        $("#_titleArt").html("");
    }

    var $modalWaitSaving = null;

    $('#btnSave').on('click', function() {
        var arrComp = [];

        $('[id^="hiddencode_"]').each(function() {
            var __id = $(this).attr("id");
            var _uid = str_replace_all(__id, "hiddencode_", "");
            var val = $(this).val();

            var desc = $('#hiddendesc_'+_uid).val();
            var cantidad = $('#hiddencosto_'+_uid).val();
            var UM = $('#hiddenUnidadMedida_'+_uid).val();
            var UMDesc = $('#hiddenUnidadMedidaDesc_'+_uid).val();


            arrComp.push({
                NroParte : $('#txtArticuloParte').val(),
                code : val,
                descripcion : val,
                cantidad : cantidad,
                UM : UM,
                UMDesc : UMDesc
            });
        });

        if (arrComp.length==0) {
            alert("Ingrese un al menos un Producto...");
            return;
        }

        $modalWaitSaving = $("#pleaseWaitSaving");
        $modalWaitSaving.modal('show');

        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                NroParte : $('#txtArticuloParte').val(),
                action : "saveComponentes",
                arrComp : arrComp
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); } },
            url: '/api/manufactura/componentes/index.php',
            success: function(data) {
                if (data.success == false) {
                    alert("Ocurrio un Error, Intente mas tarde...");
                    $modalWaitSaving.modal('hide');
                } else {
                    $modalWaitSaving.modal('hide');
                    alert("Producto Compuesto " + data.NroParte + " Guardado...");
                    cancelar();
                    $("#txtArticuloParte").chosen().val("");
                    $("#txtArticuloParte").chosen().trigger("chosen:updated");
                    $("#txtArticuloParte").prop('disabled', false).trigger('chosen:updated');
                }
            }
        });
    });

    function str_replace_all(string, str_find, str_replace){
        try{
            return string.replace( new RegExp(str_find, "gi"), str_replace ) ;
        } catch(ex){
            return string;
        }
    }
</script>

<script>
    $(document).ready(function(){
        $(function() {
            $('.chosen-select').chosen();
            $('.chosen-select-deselect').chosen({ allow_single_deselect: true });
        });

        /*$('#cboArticuloParte').chosen().change(function() {
            if($(this).text() != '') {
                $('#_rowPaso3').show();
            }
        });*/
        //$("#dataTables_empty").hide();
    });

    var $modalWait = null;

    $('#editar').click(function() {
        if($('#txtArticuloParte').val() != '') {
            $modalWait = $("#pleaseWaitDialog");
            $modalWait.modal('show');
            console.log("Buscar: ", $('#txtArticuloParte').val());
            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    action : "buscarComponentes",
                    NroParte : $('#txtArticuloParte').val()
                },
                beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); } },
                url: '/api/manufactura/componentes/index.php',
                success: function(data) {
                    console.log("SUCCESS", data);
                    if (data.success == false) {
                        alert(data.err);
                    }else{
                        if (data.existsComp==false) {
                            $("#_titleArt").html($('#txtArticuloParte').val());
                            $('#_rowPaso3').show();
                            $("#txtArticuloParte").prop('disabled', true).trigger('chosen:updated');
                        } else {
                            var found = false;
                            for (var i = 0; i < data.arrArts.length; i++) {
                                var uid = data.arrArts[i].uid;
                                var div1 = data.arrArts[i].col1;
                                var div2 = data.arrArts[i].col2;
                                var div3 = data.arrArts[i].col3;
                                var div4 = data.arrArts[i].col4;
                                var div5 = data.arrArts[i].col5;
                                var div6 = data.arrArts[i].col6;

                                var _html = "<tr class=\"gradeX\" id=\"tableprods_" + uid + "\">" +
                                    "<td id=\"col1_" + uid + "\">" + div1 +
                                    "</td>" +
                                    "<td id=\"col2_" + uid + "\">" + div2 +
                                    "</td>" +
                                    "<td id=\"col3_" + uid + "\">" + div3 +
                                    "</td>" +
                                    "<td id=\"col4_" + uid + "\">" + div4 +
                                    "</td>" +
                                    "<td id=\"col5_" + uid + "\">" + div5 +
                                    "</td>" +
                                    "<td style='display:none;' id=\"col6_" + uid + "\">" + div6 +
                                    "</td>" +
                                    "</tr>";
                                $('#editable > tbody:last-child').append(_html);
                                found = true;
                            }
                            if (found) {
                                $('#_rowPaso3').show();
                                $("#txtArticuloParte").prop('disabled', true).trigger('chosen:updated');
                                $("#_titleArt").html($('#txtArticuloParte').val());
                            }
                        }
                        $modalWait.modal('hide');
                        $('#editar').prop('disabled', true);
                        var uid = uniqueID();
                        var _html = "<tr class=\"gradeX\" id=\"tableprods_" + uid + "\">" +
                            "<td id=\"col1_" + uid + "\">" +
                            "<button class=\"btn btn-primary agregarComp permiso_registrar\" type=\"button\" onclick=\"agregarComponente('" + uid + "')\"><i class=\"fa fa-check\"></i>&nbsp;&nbsp;Agregar Componente</button>" +
                            "</td>" +
                            "<td id=\"col2_" + uid + "\">" +
                            "</td>" +
                            "<td id=\"col3_" + uid + "\">" +
                            "</td>" +
                            "<td id=\"col4_" + uid + "\">" +
                            "</td>" +
                            "<td id=\"col5_" + uid + "\">" +
                            "</td>" +
                            "</tr>";
                        $('#editable > tbody:last-child').append(_html);
                    }
                }, error: function(data){console.log("ERROR: ", data);}
            });
        }
    });

    function uniqueID(){
        function chr4(){
            return Math.random().toString(16).slice(-4);
        }
        return chr4() + chr4() +
            '-' + chr4() +
            '-' + chr4() +
            '-' + chr4() +
            '-' + chr4() + chr4() + chr4();
    }

    function aceptarRow(uid) {
        if (existsCode($("#hiddencode_"+uid).val(), "hiddencode_"+uid)) {
            alert("Ya Existe este Producto...");
            return;
        }

        var valcbo = $("#cboUnidadMedida_"+uid).chosen().val();
        var descbo = $("#cboUnidadMedida_"+uid+" option:selected").text();
        $("#cboUnidadMedida_"+uid).prop("disabled", true);

        //console.log("valcbo = ", valcbo, " - descbo = ", descbo);

        if ($("#hiddencode_"+uid).length==0) {
            return;
        }

        if ($("#hiddendesc_"+uid).length==0) {
            return;
        }

        if ($("#costo_"+uid).val()=="0" && valcbo != "Kilogramo") {
            return;
        }


        if (valcbo=="") {
            return;
        }

        var code = $("#hiddencode_"+uid).val();
        var desc = $("#hiddendesc_"+uid).val();
        var htmlUM = descbo+"<input name=\"hiddenUnidadMedida_"+uid+"\" id=\"hiddenUnidadMedida_"+uid+"\" type=\"hidden\" value='"+valcbo+"'/>"
        htmlUM += "<input name=\"hiddenUnidadMedidaDesc_"+uid+"\" id=\"hiddenUnidadMedidaDesc_"+uid+"\" type=\"hidden\" value='"+descbo+"'/>"

        $("#div_cod_"+uid).html(code+"<input name=\"hiddencode_"+uid+"\" id=\"hiddencode_"+uid+"\" type=\"hidden\" value='"+code+"'/>");
        $("#div_desc_"+uid).html(desc+"<input name=\"hiddendesc_"+uid+"\" id=\"hiddendesc_"+uid+"\" type=\"hidden\" value='"+desc+"'/>");
        $("#div_costo_"+uid).html($("#costo_"+uid).val()+"<input name=\"hiddencosto_"+uid+"\" id=\"hiddencosto_"+uid+"\" type=\"hidden\" value='"+$("#costo_"+uid).val()+"'/>");
        $("#div_unidad_medida_"+uid).html(htmlUM);

        var html = '';
        if($("#permiso_editar").val() == 1)
        html += '<a href="#" class="editando" id="editando_'+uid+'" onclick="editarRow(\''+uid+'\')"><i class="fa fa-edit" alt="Editar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
        if($("#permiso_eliminar").val() == 1)
        html += '<a href="#" class="editando" id="editando_'+uid+'" onclick="borrarRow(\''+uid+'\')"><i class="fa fa-eraser" alt="Borrar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';

        $('#col1_'+uid).html(html);
        var uid = uniqueID();
        var _html = "<tr class=\"gradeX\" id=\"tableprods_"+uid+"\">" +
            "<td id=\"col1_"+uid+"\">" +
            "<button class=\"btn btn-primary agregarComp permiso_registrar\" type=\"button\" onclick=\"agregarComponente('"+uid+"')\"><i class=\"fa fa-check\"></i>&nbsp;&nbsp;Agregar Componente</button>" +
            "</td>" +
            "<td id=\"col2_"+uid+"\">" +
            "</td>" +
            "<td id=\"col3_"+uid+"\">" +
            "</td>" +
            "<td id=\"col4_"+uid+"\">" +
            "</td>" +
            "<td id=\"col5_"+uid+"\">" +
            "</td>" +
            "</tr>";
        $('#_rowPaso3').show();
        $('#editable > tbody:last-child').append(_html);

        $('#btnSave').prop('disabled', false);

        $('[id^="editando_"]').each(function() {
            $(this).show();
            $(".agregarComp").show();
        });
    }

    function existsCode(code, _id) {
        var found = false;
        $('[id^="hiddencode_"]').each(function() {
            var __id = $(this).attr("id");
            var val = $(this).val();
            if (_id!=__id) {
                if (code==val) {
                    found = true;
                }
            }
        });
        return found;
    }

    $("#exportar").click(function(){

        var txtArticuloParte = $("#txtArticuloParte").val();

        console.log("txtArticuloParte="+txtArticuloParte);

        if(txtArticuloParte)
          $(this).attr("href", "/api/koolreport/excel/BOM_ArticulosOT/export.php?txtArticuloParte="+txtArticuloParte+"");
        else
            swal("Error", "Debe Seleccionar Un Artículo para generar el reporte", "error");

    });

    $("#exportarTodo").click(function(){

          $(this).attr("href", "/api/koolreport/excel/BOM_ArticulosOT/export.php");

    });

    function aceptarEditRow(uid) {
        if (existsCode($("#hiddencode_"+uid).val(), "hiddencode_"+uid)) {
            alert("Ya Existe este Producto...");
            return;
        }

        if ($("#hiddencode_"+uid).length==0) {
            return;
        }

        if ($("#hiddendesc_"+uid).length==0) {
            return;
        }

        if ($("#costo_"+uid).val()=="0") {
            return;
        }

        var valcbo = $("#cboUnidadMedida_"+uid).chosen().val();
        var descbo = $("#cboUnidadMedida_"+uid+" option:selected").text();
        $("#cboUnidadMedida_"+uid).prop("disabled", true);

        if (valcbo=="") {
            return;
        }

        var code = $("#hiddencode_"+uid).val();
        var desc = $("#hiddendesc_"+uid).val();
        var costo = $("#costo_"+uid).val();

        var htmlUM = descbo+"<input name=\"hiddenUnidadMedida_"+uid+"\" id=\"hiddenUnidadMedida_"+uid+"\" type=\"hidden\" value='"+valcbo+"'/>"
        htmlUM += "<input name=\"hiddenUnidadMedidaDesc_"+uid+"\" id=\"hiddenUnidadMedidaDesc_"+uid+"\" type=\"hidden\" value='"+descbo+"'/>"

        var costohtml = costo;
        costohtml += "<input name=\"hiddencosto_"+uid+"\" id=\"hiddencosto_"+uid+"\" type=\"hidden\" value='"+costo+"' />";

        $("#div_cod_"+uid).html(code+"<input name=\"hiddencode_"+uid+"\" id=\"hiddencode_"+uid+"\" type=\"hidden\" value='"+code+"'/>");
        $("#div_desc_"+uid).html(desc+"<input name=\"hiddendesc_"+uid+"\" id=\"hiddendesc_"+uid+"\" type=\"hidden\" value='"+desc+"'/>");
        $("#div_costo_"+uid).html(costohtml);
        $("#div_unidad_medida_"+uid).html(htmlUM);

        var html = '';
        if($("#permiso_editar").val() == 1)
        html += '<a href="#" class="editando" id="editando_'+uid+'" onclick="editarRow(\''+uid+'\')"><i class="fa fa-edit" alt="Editar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
        if($("#permiso_eliminar").val() == 1)
        html += '<a href="#" class="editando" id="editando_'+uid+'" onclick="borrarRow(\''+uid+'\')"><i class="fa fa-eraser" alt="Borrar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';

        $('#col1_'+uid).html(html);

        $('#btnSave').prop('disabled', false);

        $('[id^="editando_"]').each(function() {
            $(this).show();
            $(".agregarComp").show();
        });
    }

    function editarRow(uid) {
        var code = $("#hiddencode_"+uid).val();
        var desc = $("#hiddendesc_"+uid).val();
        var costo = $("#hiddencosto_"+uid).val();
        var valcbo = $("#hiddenUnidadMedida_"+uid).val();
        var descbo = $("#hiddenUnidadMedidaDesc_"+uid).val();

        console.log("UID edit = ", uid, " granel = ", $("#col6_"+uid+" div").text());

//******************
        var funcion_number = 'onkeypress=\"return isNumberKey(event)\"';
        var granel = $("#col6_"+uid+" div").text();

        if(granel == 'S')
            funcion_number = '';
//******************/
        var buttons = "<button id =\"aceptarComp_"+uid+"\" class=\"btn btn-primary\" type=\"button\" onclick=\"aceptarEditRow('"+uid+"')\"><i class=\"fa fa-check\"></i>&nbsp;&nbsp;Aceptar</button>&nbsp;&nbsp;";
        buttons += "<button id =\"cancelarComp_"+uid+"\" class=\"btn btn-primary\" type=\"button\" onclick=\"cancelarEditRow('"+uid+"')\"><i class=\"fa fa-check\"></i>&nbsp;&nbsp;Cancelar</button>";

        var htmlUM = getCboUnidadesMedida(uid) + "<input name=\"hiddenUnidadMedida_"+uid+"\" id=\"hiddenUnidadMedida_"+uid+"\" type=\"hidden\" value='"+valcbo+"'/>"
        htmlUM += "<input name=\"hiddenUnidadMedidaDesc_"+uid+"\" id=\"hiddenUnidadMedidaDesc_"+uid+"\" type=\"hidden\" value='"+descbo+"'/>";

        var costohtml = "<input type=\"text\" style=\"width: 100px\" class=\"form-control\" id=\"costo_"+uid+"\" value=\""+costo+"\" "+funcion_number+">";
        costohtml += "<input name=\"hiddencosto_"+uid+"\" id=\"hiddencosto_"+uid+"\" type=\"hidden\" value='"+costo+"' />";

        $('#col1_'+uid).html(buttons);
        $('#div_cod_'+uid).html(code+"&nbsp;<a href='#' onclick=\"buscarComponente('"+uid+"')\"><i class=\"fa fa-search\" alt=\"Buscar\"></i></a><input name=\"hiddencode_"+uid+"\" id=\"hiddencode_"+uid+"\" type=\"hidden\" value='"+code+"'/>");
        $('#div_desc_'+uid).html(desc+"<input name=\"hiddendesc_"+uid+"\" id=\"hiddendesc_"+uid+"\" type=\"hidden\" value='"+desc+"'/>");
        $('#div_costo_'+uid).html(costohtml);
        $('#div_unidad_medida_'+uid).html(htmlUM);
        $('.chosen-select').chosen();
        $('.chosen-select-deselect').chosen({ allow_single_deselect: true });
        $("#cboUnidadMedida_"+uid).chosen().val(valcbo);
        $("#cboUnidadMedida_"+uid).prop("disabled", true);
        $("#cboUnidadMedida_"+uid).chosen().trigger("chosen:updated");

        $(".editando").each(function() {
            if($(this).id != "editando_" + uid){
                $(this).hide();
            }
        });

        $(".agregarComp").hide();
        $('#btnSave').prop('disabled', true);
    }

    function borrarRow(uid) {
        $('#tableprods_'+uid).remove();
    }

    function cancelarEditRow(uid) {
        var code = $("#hiddencode_"+uid).val();
        var desc = $("#hiddendesc_"+uid).val();
        var costo = $("#hiddencosto_"+uid).val();
        var valcbo = $("#hiddenUnidadMedida_"+uid).val();
        var descbo = $("#hiddenUnidadMedidaDesc_"+uid).val();

        var htmlUM = descbo+"<input name=\"hiddenUnidadMedida_"+uid+"\" id=\"hiddenUnidadMedida_"+uid+"\" type=\"hidden\" value='"+valcbo+"'/>"
        htmlUM += "<input name=\"hiddenUnidadMedidaDesc_"+uid+"\" id=\"hiddenUnidadMedidaDesc_"+uid+"\" type=\"hidden\" value='"+descbo+"'/>"

        var costohtml = costo;
        costohtml += "<input name=\"hiddencosto_"+uid+"\" id=\"hiddencosto_"+uid+"\" type=\"hidden\" value='"+costo+"'/>";

        $("#div_cod_"+uid).html(code+"<input name=\"hiddencode_"+uid+"\" id=\"hiddencode_"+uid+"\" type=\"hidden\" value='"+code+"'/>");
        $("#div_desc_"+uid).html(desc+"<input name=\"hiddendesc_"+uid+"\" id=\"hiddendesc_"+uid+"\" type=\"hidden\" value='"+desc+"'/>");
        $("#div_costo_"+uid).html(costohtml);
        $("#div_unidad_medida_"+uid).html(htmlUM);

        var html = '';
        html += '<a href="#" class="editando" id="editando_'+uid+'" onclick="editarRow(\''+uid+'\')"><i class="fa fa-edit" alt="Editar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
        html += '<a href="#" class="editando" id="editando_'+uid+'" onclick="borrarRow(\''+uid+'\')"><i class="fa fa-eraser" alt="Borrar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';

        $('#col1_'+uid).html(html);

        $('#btnSave').prop('disabled', false);

        $('[id^="editando_"]').each(function() {
            $(this).show();
            $(".agregarComp").show();
        });
    }

    function cancelarRow(uid) {
        var _html = "" +
            "<td id=\"col1_"+uid+"\">" +
            "<button class=\"btn btn-primary agregarComp\" type=\"button\" onclick=\"agregarComponente('"+uid+"')\"><i class=\"fa fa-check\"></i>&nbsp;&nbsp;Agregar Componente</button>" +
            "</td>" +
            "<td id=\"col2_"+uid+"\">" +
            "</td>" +
            "<td id=\"col3_"+uid+"\">" +
            "</td>" +
            "<td id=\"col4_"+uid+"\">" +
            "</td>" +
            "<td id=\"col5_"+uid+"\">" +
            "</td>" +
            "";
        $('#tableprods_'+uid).html(_html);
        $('#btnSave').prop('disabled', false);

        $('[id^="editando_"]').each(function() {
            $(this).show();
        });
    }

    function agregarComponente(uid) {
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                action : "addRowComponentes",
                NroParte : $('#cboArticuloParte :selected').val(),
                UID : uid
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); } },
            url: '/api/manufactura/componentes/index.php',
            success: function(data) {
                //console.log("SUCCESS: ", data);
                if (data.success == false) {
                    alert(data.err);
                }else{
                    $('#_rowPaso3').show();
                    $('#col1_'+uid).html(data.col1);
                    $('#col2_'+uid).html(data.col2);
                    $('#col3_'+uid).html(data.col3);
                    $('#col4_'+uid).html(data.col4);
                    $('#col5_'+uid).html(data.col5);
                    $('.chosen-select').chosen();
                    $('.chosen-select-deselect').chosen({ allow_single_deselect: true });

                    $('[id^="editando_"]').each(function() {
                        $(this).hide();
                    });

                    $('#btnSave').prop('disabled', true);
                }
            }, error: function(data) {
                console.log("ERROR: ", data);
            }
        });
    }

    var oTable2 = $('#dataTableProductos').dataTable( {
        dom: 'tipr',
        responsive: true,
        "searching": true,
        "bProcessing": true,
        "serverSide": true,
        "pagingType": "full_numbers",
        "language": {
            "lengthMenu": "Mostrando _MENU_ registros",
            "zeroRecords": "",
            "info": "Pagina _PAGE_ de _PAGES_",
            "infoEmpty": "",
            "infoFiltered": "",
            "sSearch": "Filtrar:",
            "sProcessing":   	"Cargando...",

            "oPaginate": {
                "sNext": "Sig",
                "sPrevious": "Ant",
                "sLast": "Ultimo",
                "sFirst": "Primero",
            }
        },
        "ajax": {
            "url": "/api/manufactura/componentes/index.php",
            "type": "POST",
            "data": {
                "action" : "loadPopup",
                "criterio" : $("#txtBusqueda").val(),
                "id_almacen" : $("#id_almacen").val(),
                "action" : "loadPopup"
            },
            "columns": [
                { "data": "cve_articulo" },
                { "data": "des_articulo" },
                { "data": "control_peso"}
            ],
            "columnDefs": [
                {
                    "targets": [ 1 ],
                    "visible": false,
                    "searchable": false
                },
                {
                    "targets": [ 1 ],
                    "visible": false
                }
            ]
        },
        "createdRow": function ( row, data, index ) {
            var code = data[0],
                descripcion = data[1],
                granel = data[2];
            $('td', row).eq(1).html("<a href=\"#\" onclick=\"agregarProductoCompuesto('"+data[0]+"', '"+addslashes(data[0])+"', '"+granel+"')\">"+data[1]+"</a>");
            $('td', row).eq(1).html("<a href=\"#\" onclick=\"agregarProductoCompuesto('"+data[0]+"', '"+addslashes(data[1])+"', '"+granel+"')\">"+data[1]+"</a>");
            $('td', row).eq(2).hide();
            $(row).attr('id', 'tr_'+code);

        }

    } );


    function addslashes (str) {
        return str.replace(/&/g, "&amp;").replace(/>/g, "&gt;").replace(/</g, "&lt;").replace(/"/g, "&quot;")
    }

    function buscarComponente(uid) {
        $('#hiddenuid').val(uid)
        $modal2 = $('#conModal');
        $modal2.modal('show');
    }

    function doSearch() {
        oTable2.DataTable().search($("#txtBusqueda").val()).draw();
    }

    var table = $('#editable').dataTable({
        responsive: true,
        "autoWidth": false,
        "bInfo":false
        ,"bLengthChange":false
        ,"bFilter":false
        ,"bPaginate":false
        ,"bSort": false
        ,"aoColumns": [
            null,
            null,
            null,
            null,
            null
        ],
        "createdRow": function ( row, data, index ) {
            //$(row).attr('id', 'first_row');
        },
        "initComplete": function () {
            $('#editable tbody .dataTables_empty').remove();
        }
    });

    function agregarProductoCompuesto(code, desc, granel) {
        var uid = $('#hiddenuid').val();

        var htmlUM = getCboUnidadesMedida(uid) + "<input name=\"hiddenUnidadMedida_"+uid+"\" id=\"hiddenUnidadMedida_"+uid+"\" type=\"hidden\" value=''/>";
        htmlUM += "<input name=\"hiddenUnidadMedidaDesc_"+uid+"\" id=\"hiddenUnidadMedidaDesc_"+uid+"\" type=\"hidden\" value=''/>";

        console.log("granel = ", granel);

        var funcion_number = 'onkeypress=\"return isNumberKey(event)\"';
        if(granel == 'S')
            funcion_number = '';

        var costohtml = "<input type=\"number\" style=\"width: 100px\" class=\"form-control\" id=\"costo_"+uid+"\" value=\"0\" "+funcion_number+">";
        costohtml += "<input name=\"hiddencosto_"+uid+"\" id=\"hiddencosto_"+uid+"\" type=\"hidden\" value=''/>";

        $('#div_cod_'+uid).html(code+"&nbsp;<a href='#' onclick=\"buscarComponente('"+uid+"')\"><i class=\"fa fa-search\" alt=\"Buscar\"></i></a><input name=\"hiddencode_"+uid+"\" id=\"hiddencode_"+uid+"\" type=\"hidden\" value='"+code+"'/>");
        $('#div_desc_'+uid).html(desc+"<input name=\"hiddendesc_"+uid+"\" id=\"hiddendesc_"+uid+"\" type=\"hidden\" value='"+desc+"'/>");
        $('#div_costo_'+uid).html(costohtml);
        $('#div_unidad_medida_'+uid).html(htmlUM);
        $modal2.modal('hide');
        $('.chosen-select').chosen();
        $('.chosen-select-deselect').chosen({ allow_single_deselect: true });

        $('[id^="editando_"]').each(function() {
            $(this).hide();
        });

        $('#btnSave').prop('disabled', true);
    }

    function getCboUnidadesMedida(uid) {
        var cboUnidadesMedida = '<?php echo $comboUnidadesMedida ?>';
        cboUnidadesMedida = cboUnidadesMedida.replace('###########UID###########', 'cboUnidadMedida_'+uid);
        return cboUnidadesMedida;
    }

    function isNumberKey(event){

        var charCode = (event.which) ? event.which : event.keyCode
        if (charCode > 31 && (charCode < 48 || charCode > 57))
            return false;
        return true;
/*
            if (event.shiftKey) {
                event.preventDefault();
            }

            if (event.keyCode == 46 || event.keyCode == 8) {
            }
            else {
                if (event.keyCode < 95) {
                    if (event.keyCode < 48 || event.keyCode > 57) {
                        event.preventDefault();
                    }
                }
                else {
                    if (event.keyCode < 96 || event.keyCode > 105) {
                        event.preventDefault();
                    }
                }
            }
        return true;
        */
    }

$('#btn-layout').on('click', function(e) {
  //e.preventDefault();  //stop the browser from following
  //window.location.href = 'http://www.tinegocios.com/proyectos/wms/ubicacion.xlsx';
    window.location.href = '/Layout/Layout_Componentes_Productos_Compuestos.xlsx';
});
  
$('#btn-import').on('click', function() {

    $('#btn-import').prop('disable', true);
    var bar = $('.progress-bar');
    var percent = $('.percent');
    //var status = $('#status');

    var formData = new FormData();
    formData.append("clave", "valor");
   
    $.ajax({
        // Your server script to process the upload
        url: '/articulos-compuestos/importar',
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
            console.log("SUCCESS: ", data);
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
        }, error: function(data){
            console.log("ERROR: ", data);
        }
    });
});

</script>