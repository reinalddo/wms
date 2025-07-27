<?php
$almacenes = new \AlmacenP\AlmacenP();
$model_almacen = $almacenes->getAll();
?>
<link href="/css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">
<link href="/css/plugins/ladda/select2.css" rel="stylesheet"/>
<link href="/css/plugins/dataTables/dataTables1.min.css" rel="stylesheet"/>
<link href="/css/plugins/sweetalert/sweetalert.css" rel="stylesheet">
<link href="/css/plugins/datapicker/datepicker3.css" rel="stylesheet">
<link href="/css/plugins/chosen/chosen.css" rel="stylesheet">
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

<div class="modal" id="modal-previa" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Ubicación</h4>
            </div>
            <div class="modal-body" style="height: 150px;">
                <div class="col-lg-4">
                   <label>Código BL: </label><span id="codigo_bl"></span><br>
                   <!--<label>Rack: </label><span id="rack"></span><br>-->
                   <label>Sección: </label><span id="seccion"></span><br>
                   <label>Nivel: </label><span id="nivel"></span><br>
                   <label>Posición: </label><span id="posicion"></span><br>
                  <span id="id" hidden></span><br>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
                <?php 
                /*
                ?>
                <button type="button" class="btn btn-primary" onClick="pdf_ubicaciones()" style="margin-right: 5px;"><i class="fa fa-print"></i>PDF</button>
                <?php 
                */
                ?>
                <a href="#" onclick="imprimir_etiqueta_ubicaciones()" id="imprimir_etiqueta_ubicaciones" class="btn btn-primary"><i class="fa fa-file-pdf-o"></i> PDF</a>

            </div>
        </div>
    </div>
</div>

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

<div class="modal" id="rangeDialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" id="headModalRango">
                <h2>Imprimir Rango</h2>
            </div>
            <div class="modal-body">
                <div class="col-lg-4">
                    <label>Desde</label>
                    <input id="desde" type="text" maxlength="4" placeholder="Desde" class="form-control" name="desde" value="1">
                    <input id="hiddenid" type="hidden" name="hiddenid">
                </div>
                <div class="col-lg-4">
                    <label>Hasta</label>
                    <input id="hasta" type="text" maxlength="4" placeholder="Hasta" class="form-control" name="hasta">
                </div>
                <br>
                <br>
                <br>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-white" data-dismiss="modal" id="btnCancel">Cerrar</button>
                <button type="button" class="btn btn-primary ladda-button" data-style="contract" id="btnPrintRange">Imprimir</button>
            </div>
        </div>
    </div>
</div>

<div class="wrapper wrapper-content  animated " id="list">
    <h3>Ubicaciones</h3>
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-title">
				            <div class="row">
                        <div class="col-lg-6">
                            <div class="form-group">
                              <label >Almacén</label>
                              <select id="select-almacen" class="chosen-select form-control"></select>
                            </div>
                        </div>	
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label >Zona de Almacenaje</label>
                                <select id="select-zona" class="chosen-select form-control"></select>
                            </div>		
                        </div>    										
                        <div class="col-md-3">
                            <div class="form-group">
                                <label >Rack</label>
                                <select id="select-rack" class="chosen-select form-control"></select>		
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label >Nivel</label>
                                <select id="select-nivel" class="chosen-select form-control"></select>       
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label >Seccion</label>
                                <select id="select-seccion" class="chosen-select form-control"></select>       
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label >Posicion</label>
                                <select id="select-posicion" class="chosen-select form-control"></select>       
                            </div>
                        </div>
                        <div class="col-md-12" style="display: none;">
                            <label>&nbsp;&nbsp;&nbsp;&nbsp;</label>
                            <button type="button" class="btn btn-success pull-right" onClick="ImprimirRango()"><i class="fa fa-print"> Imprimir Todos</i></button>						
                        </div>
					          </div>
                </div>
                <div class="ibox-content">
                    <div class="row">
                        <div class="col-lg-12">

                            <div class="table-responsive">
                                <table id="table-info"  class="table table-hover table-striped no-margin display nowrap" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>Codigo BL</th>
                                            <th>Rack</th>
                                            <th>Sección</th>
                                            <th>Nivel</th>
                                            <th>Posicion</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id = "tbody">
                                    </tbody>
                                </table>
                            </div>
                            <a href="#" name="btnImprimir" style="display:none; float:right;" onclick="GenerarReporte()" id="btnImprimir" class="btn btn-primary"><i class="fa fa-file-pdf-o"></i> Generar Ubicaciones Seleccionadas</a>
                        </div>
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
<script src="/js/plugins/sweetalert/sweetalert.min.js"></script>
<!-- Select -->
<script src="/js/select2.js"></script>
<!-- Data picker -->
<script src="/js/moment.js"></script>
<script src="/js/plugins/dataTables/dataTables.min.js"></script>
<script src="/js/plugins/dataTables/dataTables_bootstrap.min.js"></script>
<script src="/js/plugins/datapicker/bootstrap-datepicker.js"></script>
<script src="/js/plugins/chosen/chosen.jquery.js"></script>
<script src="/js/utils.js"></script>   
<script>

    var select_almacen = document.getElementById('select-almacen'),
        select_zona = document.getElementById('select-zona'),
        select_rack = document.getElementById('select-rack'),
        select_nivel = document.getElementById('select-nivel'),
        select_seccion = document.getElementById('select-seccion'),
        select_posicion = document.getElementById('select-posicion'),
        tableDataInfo = new TableDataRest(),
        DATA_R = [];


    $('.chosen-select').chosen();

    select_almacen.onchange = function(){changeAlmacen();};
    select_zona.onchange = function(){changeZona();$("#btnImprimir").show();};
    select_rack.onchange = function(){searchTable();$("#btnImprimir").show();};
    select_nivel.onchange = function(){searchTable();$("#btnImprimir").show();};
    select_seccion.onchange = function(){searchTable();$("#btnImprimir").show();};
    select_posicion.onchange = function(){searchTable();$("#btnImprimir").show();};

    init();

    function init(){
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                id_user: <?php echo $_SESSION['id_user']; ?>,
                action: 'enter-view'
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/ubicacionalmacenaje/index.php',
            success: function(data) {
                fillsAlmacens(data.almacens);
                almacenPrede();
            },
            error:function(res){
                window.console.log(res);
            }

        });
    }

    function fillsAlmacens(node){

        var options = "";

        if(node){

            for(var i = 0; i < node.length; i++){
                options += "<option value = "+node[i].clave+">"+htmlEntities(node[i].clave)+" - "+htmlEntities(node[i].nombre)+"</option>";
            }
        }
        select_almacen.innerHTML += options;
        $(select_almacen).trigger("chosen:updated");
    }
    /**
     * @author Ricardo Delgado.
     * Busca y selecciona el almacen predeterminado para el usuario.
     */ 
    function almacenPrede(){ 
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                idUser: '<?php echo $_SESSION["id_user"]?>',
                action: 'search_almacen_pre'
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/almacenPredeterminado/index.php',
            success: function(data) {
                if (data.success == true) {
                    select_almacen.value = data.codigo.clave;
                    $(select_almacen).trigger("chosen:updated");
                    changeAlmacen();
                }
            },
            error: function(res){
                window.console.log(res);
            }
        });
    }

    function changeAlmacen(){
        
        var value = select_almacen.value;

        if(value){

            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    action: 'search-alma',
                    'id': value
                },
                beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                },
                url: '/api/ubicacionalmacenaje/index.php',
                success: function(data) {
                    fillZona(data.zona);
                    searchTable();
                },
                error:function(res){
                    window.console.log(res);
                }

            });
        }
        else{
            fillZona();
            searchTable();
        }

        function fillZona(node){

            var options = "<option value = ''>Seleccione una zona de almacenaje (0)</option>";

            if(node){

                options = "<option value = ''>Seleccione una zona de almacenaje ("+node.length+")</option>";
                
                for(var i = 0; i < node.length; i++){
                    options += "<option value = "+node[i].cve_almac+">"+htmlEntities(node[i].des_almac)+"</option>";
                }
            }

            select_zona.innerHTML = options;
            $(select_zona).trigger("chosen:updated");
        }
    }

    function changeZona(){

        var value = select_zona.value;
        console.log("Zona value = ", value);
        if(value){

            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    action: 'get-all-compo',
                    'zona': value
                },
                beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                },
                url: '/api/ubicacionalmacenaje/index.php',
                success: function(data) {
                    fillRack(data.rack);
                    fillNivel(data.nivel);
                    fillSeccion(data.seccion);
                    fillPosi(data.posi);
                    searchTable();
                },
                error:function(res){
                    window.console.log(res);
                }

            });
        }
        else{
            fillRack();
            fillNivel();
            fillSeccion();
            fillPosi();
            searchTable();
        }

        function fillRack(node){
            console.log("fillRack = ", node);
            var options = "<option value = ''>Seleccione un Rack (0)</option>";

            if(node){

                options = "<option value = ''>Seleccione un Rack ("+node.length+")</option>";
                
                for(var i = 0; i < node.length; i++){
                    options += "<option value = "+node[i].cve_rack+">"+node[i].cve_rack+"</option>";
                }
            }

            select_rack.innerHTML = options;
            $(select_rack).trigger("chosen:updated");
        }

        function fillNivel(node){

            var options = "<option value = ''>Seleccione un Nivel (0)</option>";

            if(node){

                options = "<option value = ''>Seleccione un Nivel ("+node.length+")</option>";
                
                for(var i = 0; i < node.length; i++){
                    options += "<option value = "+node[i].cve_nivel+">"+node[i].cve_nivel+"</option>";
                }
            }

            select_nivel.innerHTML = options;
            $(select_nivel).trigger("chosen:updated");
        }

        function fillSeccion(node){

            var options = "<option value = ''>Seleccione un Seccion (0)</option>";

            if(node){

                options = "<option value = ''>Seleccione un Seccion ("+node.length+")</option>";
                
                for(var i = 0; i < node.length; i++){
                    options += "<option value = "+node[i].Seccion+">"+node[i].Seccion+"</option>";
                }
            }

            select_seccion.innerHTML = options;
            $(select_seccion).trigger("chosen:updated");
        }

        function fillPosi(node){

            var options = "<option value = ''>Seleccione un Posicion (0)</option>";

            if(node){

                options = "<option value = ''>Seleccione un Posicion ("+node.length+")</option>";
                
                for(var i = 0; i < node.length; i++){
                    options += "<option value = "+node[i].Ubicacion+">"+node[i].Ubicacion+"</option>";
                }
            }

            select_posicion.innerHTML = options;
            $(select_posicion).trigger("chosen:updated");
        }
    }

    function searchTable(){

        var value = select_zona.value;

        if(value){

            var data = { action : "show-table-zona", cia: <?php echo $_SESSION['cve_cia'] ?> };

            if(select_zona.value !== ''){
                data.zona = select_zona.value;
            }

            if(select_rack.value !== ''){
                data.rack = select_rack.value;
            }

            if(select_nivel.value !== ''){
                data.nivel = select_nivel.value;
            }

            if(select_seccion.value !== ''){
                data.seccion = select_seccion.value;
            }

            if(select_posicion.value !== ''){
                data.posi = select_posicion.value;
            }

            $.ajax({
                type: "POST",
                dataType: "json",
                data: data,
                beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                },
                url: '/api/ubicacionalmacenaje/index.php',
                success: function(data) {
                    console.log("data = ",data);
                    fillTableInfo(data.res, data.bl);
                },
                error:function(res){
                    window.console.log(res);
                }

            });
        }
        else{
            fillTableInfo(0);
        }
    }


    function GenerarReporte()
    {
        var rep = [], k = 0;
        $(".check_imprimir").each(function(i,j){
            if($(this).is(":checked"))
            {
                console.log("i = ", i, " value = ", j.value);
                rep.push({
                            idy_ubica: $(this).data("idyubica"),
                            bl: $(this).data("bl")
                         });
                k++;
            }
        });

        console.log("reporte = ", rep);

        if(rep.length == 0)
        {
            //swal("No hay ubicaciones Seleccionadas", "Debe Seleccionar una o más ubicaciones para generar el reporte", "warning");
            //return;
            imprimir_filtros();
        }
        else
        {
            $("#btnImprimir").attr("target","_blank");
            $("#btnImprimir").attr("href", "/api/koolreport/export/reportes/etiquetas/ubicaciones?ubicacion="+JSON.stringify(rep));
            setTimeout(function(){$("#btnImprimir").attr("target","");}, 2000);
        }
    }

    function fillTableInfo(node, codeBL){

        tableDataInfo.destroy();

        var data = [],
            arrayCode;

        DATA_R = node;
        var arrayCode = [];
        if(codeBL){
          if(codeBL.length > 0)
            arrayCode = codeBL[0].codigo.split("-");
        }
         console.log("fillTableInfo node.length = ", node.length);

        for(var i = 0; i < node.length ; i++){

            var ubicacion = node[i].CodigoCSD;
            var idy_ubica = node[i].idy_ubica;

            //if(arrayCode.length > 0){
            //    ubicacion = fillCodeBL(node[i], arrayCode);
            //}

            data.push([
                ubicacion,
                node[i].cve_rack,
                node[i].Seccion,
                node[i].cve_nivel,
                node[i].Ubicacion,
                '&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" class="check_imprimir permiso_consultar" title="Imprimir Ubicación" data-idyubica = "'+idy_ubica+'" data-bl = "'+ubicacion+'" />']);
            //'<a href="#" class="pointer-events: none;cursor: default;">'+'<i id="button-table-'+i+'" class="glyphicon glyphicon-print btnInfo" ></i></a>'+
        }

        tableDataInfo.init("table-info",true, true, data);
        $(".btnInfo").click(function(){
            console.log("vista_previa btnInfo");
            var array = this.id.split("-"),
                id = parseInt(array[2]);
                console.log("vista_previa btnInfo = ", id);
            vista_previa(id);
           //printEtiqueta(DATA_R[id].idy_ubica);             
        });
      
      

        function vista_previa(id){
          console.log("vista_previa");
          $('#modal-previa').modal('show');
          $("#codigo_bl").html(DATA_R[id].CodigoCSD);//cve_rack+"-"+DATA_R[id].cve_nivel+"-"+DATA_R[id].Ubicacion
          $("#select-rack").html(DATA_R[id].cve_rack);
          $("#seccion").html(DATA_R[id].Seccion);
          $("#nivel").html(DATA_R[id].cve_nivel);
          $("#posicion").html(DATA_R[id].Ubicacion);
          $("#id").val(id);
          //printEtiqueta(DATA_R[id].idy_ubica);
          console.log(DATA_R[id]);
        };
      
        function fillCodeBL(data, array){

            var value = "";

            for(var l = 0; l < array.length; l++){

                if(array[l] === "cve_rack")
                    value += data.cve_rack;
                else if(array[l] === "cve_nivel")
                    value += data.cve_nivel;
                else if(array[l] === "Seccion")
                    value += data.Seccion;
                else if(array[l] === "Ubicacion")
                    value += data.Ubicacion;
                else if(array[l] === "cve_pasillo")
                    value += data.cve_pasillo;

                if(l !== (array.length - 1))
                    value += "-";
            }

            return value;
        }
    }
  
    function pdf_ubicaciones(){
        console.log("pdf_ubicaciones()");
        var cia ="";
        var form = document.createElement("form");
        var id = $("#id").val();
        console.log(DATA_R);
        
        form.setAttribute("method", "post");
        form.setAttribute("action", "/api/reportes/generar/pdf.php");
        form.setAttribute("target", "_blank");
      
        var input_content = document.createElement('input');
        var input_title = document.createElement('input');
        var input_cia = document.createElement('input');
      
        var content ='<div class="col-lg-4"><label>Código BL: </label><span id="codigo_bl">'+DATA_R[id].CodigoCSD+'</span><br><br><label>Rack: </label><span id="rack">'+DATA_R[id].cve_rack+'</span><br><br><label>Sección: </label><span id="seccion">'+DATA_R[id].cve_nivel+'</span><br><br><label>Nivel: </label><span id="nivel">'+DATA_R[id].Seccion+'</span><br><br><label>Posición: </label><span id="posicion">'+DATA_R[id].Ubicacion+'</span><br><br></div>';

        input_content.setAttribute('type', 'hidden');
        input_content.setAttribute('value', content);
        input_content.setAttribute('name', 'content');
        input_title.setAttribute('type', 'hidden');
        input_title.setAttribute('value', 'Ubicacion');
        input_title.setAttribute('name', 'title');
        input_cia.setAttribute('type', 'hidden');
        input_cia.setAttribute('value', cia);
        input_cia.setAttribute('name', 'cia');

        form.appendChild(input_content);
        form.appendChild(input_title);
        form.appendChild(input_cia);

        document.body.appendChild(form);
        form.submit(); 
      }

/*
        function GenerarReporte()
        {
            var criterio = $("#txtCriterio").val();
            var OCBusq = $("#OCBusq").val();
            var cve_articulo = $("#articulos_list").val();
            var lote = $("#lotes_list").val();
            var fechaI = $("#fechaI").val();
            var fechaF = $("#fechaF").val();
            var cve_proveedor = $("#cve_proveedor").val();
            var almacen = $("#almacenes").val();
            var movimiento = $("#movimiento").val();

            if(cve_articulo == '' && lote == '' && fechaI == '' && fechaF == '' && criterio == '' && OCBusq == '')
            {
                swal("Filtros Vacíos", "Debe Seleccionar algún filtro para generar el Reporte", "error");
                return;
            }
           var cve_cia = <?php echo $_SESSION['cve_cia']; ?>;

            //$("#reporte_kardex").attr("href", "/api/koolreport/excel/kardex/export.php?almacen="+almacen+"&criterio="+criterio+"&cve_articulo="+cve_articulo+"&lote="+lote+"&fechaI="+fechaI+"&fechaF="+fechaF);
            $("#reporte_kardex").attr("target","_blank");
            $("#reporte_kardex").attr("href", "/api/koolreport/export/reportes/kardex/reporte-kardex?almacen="+almacen+"&criterio="+criterio+"&cve_articulo="+cve_articulo+"&lote="+lote+"&fechaI="+fechaI+"&fechaF="+fechaF+"&cve_cia="+cve_cia+"&cve_proveedor="+cve_proveedor+"&OCBusq="+OCBusq);

            setTimeout(function(){$("#reporte_kardex").attr("target","");}, 2000);

        }


*/
    function imprimir_filtros()
    {
        console.log("zona = ", $("#select-zona").val());
        console.log("rack = ", $("#select-rack").val());
        console.log("nivel = ", $("#select-nivel").val());
        console.log("seccion = ", $("#select-seccion").val());
        console.log("posicion = ", $("#select-posicion").val());

        var zona      = $("#select-zona").val();
        var rack      = $("#select-rack").val();
        var nivel     = $("#select-nivel").val();
        var seccion   = $("#select-seccion").val();
        var posicion  = $("#select-posicion").val();

        $("#btnImprimir").attr("target","_blank");
        $("#btnImprimir").attr("href", "/api/koolreport/export/reportes/etiquetas/ubicaciones?zona="+zona+"&rack="+rack+"&nivel="+nivel+"&seccion="+seccion+"&posicion="+posicion);

        setTimeout(function(){$("#btnImprimir").attr("target","");}, 2000);
  }

    function imprimir_etiqueta_ubicaciones()
    {
        var id = $("#id").val();

        var BL = DATA_R[id].CodigoCSD;

        $("#imprimir_etiqueta_ubicaciones").attr("target","_blank");
        $("#imprimir_etiqueta_ubicaciones").attr("href", "/api/koolreport/export/reportes/etiquetas/ubicaciones?BL="+BL);

        setTimeout(function(){$("#imprimir_etiqueta_ubicaciones").attr("target","");}, 2000);
  }

    function printEtiqueta(id){

        var title = '';
        var content = '';

        console.log("id_ubicacion = ", id);

        $.ajax({
            url: "/api/reportes/update/index.php",
            type: "POST",
            data: {
                "action":"ubicacioneshowreport",
                "id_ubicacion" : id
            },
            success: function(data, textStatus, xhr){
                var a = document.createElement('a');
                a.href='/api/reportes/generar/etiqueta_ubi.php';
                a.target = '_blank';
                document.body.appendChild(a);
                a.click();
            }
        });

    }

    var $modal0 = null;
    var totalEtiq = 0;

    function ImprimirRango(){

                console.log("zona = ", $("#select-zona").val());
                console.log("rack = ", $("#select-rack").val());
                console.log("nivel = ", $("#select-nivel").val());
                console.log("seccion = ", $("#select-seccion").val());
                console.log("posicion = ", $("#select-posicion").val());
                //return;

         $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                zona: $("#select-zona").val(),
                rack: $("#select-rack").val(),
                nivel: $("#select-nivel").val(),
                seccion: $("#select-seccion").val(),
                posicion: $("#select-posicion").val(),
				action:"setRangoUbicacionesShowReport"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }},
            url: '/api/reportes/update/index.php',
            success: function(data, textStatus, xhr){
                var a = document.createElement('a');
                a.href='/api/reportes/generar/etiquetaRango_ubi.php';
                a.target = '_blank';
                document.body.appendChild(a);
                a.click();
            }
        });
    }

    $('#desde').keypress(function(e) {
        var a = [];
        var k = e.which;

        for (i = 48; i < 58; i++)
            a.push(i);

        if (!(a.indexOf(k)>=0))
            e.preventDefault();
    });

    $('#hasta').keypress(function(e) {
        var a = [];
        var k = e.which;

        for (i = 48; i < 58; i++)
            a.push(i);

        if (!(a.indexOf(k)>=0))
            e.preventDefault();
    });

    $('#btnPrintRange').on('click', function() {

        if ($("#desde").val()=="") {
            return;
        }

        var d = parseInt($("#desde").val());

        if (d<1) {
            return;
        }

        if ($("#hasta").val()=="") {
            return;
        }

        var h = parseInt($("#hasta").val());

        if (d>h) {
            return;
        }

        if (h>totalEtiq) {
            return;
        }

        $modal0.modal('hide');

        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                desde : $("#desde").val(),
                hasta : $("#hasta").val(),
                FolPedidoCon : $("#hiddenid").val(),
                FacturaMadre : $("#hiddenFacturaMadre").val(),
                action : "printRangeGuiasEmbarqueShowReport"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/reportes/update/index.php',
            success: function(data) {
                if (data.success == true) {
                    var a = document.createElement('a');
                    a.href='/api/reportes/generar/etiquetaRango_ubi.php';
                    a.target = '_blank';
                    document.body.appendChild(a);
                    a.click();
                    $modal0.modal('hide');
                } else {
                    alert(data.err);
                }
            }
        });
    });
	
		
	function almacen(){
		$('#select-zona')
			.find('option')
			.remove()
			.end()
		;
		
        $(".itemlist").remove();

        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                clave : $('#almacen').val(),
                action : "traerZonasDeAlmacenP"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/almacenp/update/index.php',
            success: function(data) {
				
                 if (data.success == true) {
			        var options = $("#select-zona");
					options.empty();
					 options.append(new Option("Seleccione", ""));
					for (var i=0; i<data.zonas.length; i++)
                    {
						options.append(new Option(data.zonas[i].nombre_zona, data.zonas[i].clave_zona));
					}
					 $('.chosen-select').trigger("chosen:updated");	
                }
            }
        });
		}
		/*
		$('#select-zona').on('change', function() {

			if ($(this).val()!="") 
				$("#cargarUbicaciones").prop('disabled',false);
			else
				$("#cargarUbicaciones").prop('disabled',true);
				
			$.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    zona : $('#select-zona').val(),
                    action : "traerRackDeZonas"
                },
                beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                },
                url: '/api/almacen/update/index.php',
                success: function(data) {
    				
                     if (data.success == true) {
    					
    					
    					
    					        var options = $("#select-rack");
    							options.empty();
    							options.append(new Option("Seleccione", ""));
    					for (var i=0; i<data.racks.length; i++)
                        {
    						options.append(new Option(data.racks[i].rack, data.racks[i].rack));
    					}
    					 $('.chosen-select').trigger("chosen:updated");
    					
    					
                    }
                },
                error: function(res){
                    console.log(res);
                }
            });			
        });
*/
</script>