<?php

$r = new \Roles\Roles();
$roles = $r->getAll();


$vere = \db()->prepare("select * from t_profiles as a where id_menu=16 and id_submenu=33 and id_role='".$_SESSION["perfil_usuario"]."'");
$vere->execute();
$ver = $vere->fetchAll(PDO::FETCH_ASSOC);


$agre = \db()->prepare("select * from t_profiles as a where id_menu=16 and id_submenu=34 and id_role='".$_SESSION["perfil_usuario"]."'");
$agre->execute();
$ag = $agre->fetchAll(PDO::FETCH_ASSOC);


$edita = \db()->prepare("select * from t_profiles as a where id_menu=16 and id_submenu=35 and id_role='".$_SESSION["perfil_usuario"]."'");
$edita->execute();
$edit = $edita->fetchAll(PDO::FETCH_ASSOC);


$borra = \db()->prepare("select * from t_profiles as a where id_menu=16 and id_submenu=36 and id_role='".$_SESSION["perfil_usuario"]."'");
$borra->execute();
$borrar = $borra->fetchAll(PDO::FETCH_ASSOC);



// MOD 116
// VER 33
// AGREGAR 34
// EDITAR 35
// BORRAR 36

?>

<style type="text/css">

.group input[type="checkbox"] {
    display: none;
}

.group input[type="checkbox"] + .bt-group > label span {
    width: 20px;
}

.group input[type="checkbox"] + .bt-group > label span:first-child {
    display: none;
}
.group input[type="checkbox"] + .bt-group > label span:last-child {
    display: inline-block;   
}

.group input[type="checkbox"]:checked + .bt-group > label span:first-child {
    display: inline-block;
}
.group input[type="checkbox"]:checked + .bt-group > label span:last-child {
    display: none;   
}

.plomo input[type="checkbox"] {
    display: none;
}

.plomo input[type="checkbox"] + label {
    background-color : #8c8c8c;
    color: #FFFFFF;
     border-color: white;
}

.plomo input[type="checkbox"]:checked + label {
    background-color : #1ab394;
    color: #FFFFFF;
     border-color: white;
}

</style>
<link href="/css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">
<link href="/css/plugins/ladda/select2.css" rel="stylesheet"/>
<!-- Sweet Alert -->
<link href="/css/plugins/sweetalert/sweetalert.css" rel="stylesheet">
<link href="/css/plugins/iCheck/custom.css" rel="stylesheet">
<link href="/css/animate.css" rel="stylesheet">
<link href="/css/style.css" rel="stylesheet">
<link href="/css/switchery.min.css" rel="stylesheet">

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

<div class="wrapper wrapper-content  animated fadeInRight">
    <h3>Permisos</h3>
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox-title col-md-3">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title">Roles</h3>
                    </div>
                    <div class="box-body">
                        <div class="list-group" id="div-list-group">
                    
                        </div>
                    </div>
                </div>
            </div>

            <div class="ibox-title col-md-9">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title">Módulos</h3>
                        <div class="[ form-group group ]">
                            <input type="checkbox" name="fancy-checkbox-primary" id="checkbox-select-all" autocomplete="off" disabled />
                            <div class="[ btn-group bt-group ]">
                                <label for="checkbox-select-all" class="[ btn btn-primary ]">
                                    <span class="[ glyphicon glyphicon-ok ]"></span>
                                    <span> </span>
                                </label>
                                <label for="checkbox-select-all" class="[ btn btn-default active ]">
                                    Seleccionar Todo
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="box-body">
                        <div class="panel-group" id="accordion">
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
<script src="/js/switchery.min.js"></script>
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
<!-- Sweet alert -->
<script src="/js/plugins/sweetalert/sweetalert.min.js"></script>
<!-- Select -->
<script src="/js/select2.js"></script>
<!-- File Upload -->

<script src="/js/plugins/iCheck/icheck.min.js"></script>

<script type="text/javascript">

    var div_list_group = document.getElementById('div-list-group'),
        div_items_movil = document.getElementById('div-items-movil'),
        div_panel_acordion = document.getElementById('accordion'),
        ID_PERFIL = 0,
        ITEMS = []; 

    init();

    document.getElementById('checkbox-select-all').onchange = function(){selectItemsAll();};

    function init(){

        $.ajax({
            url: "/api/roles/index.php",
            type: "POST",
            data: {
                "action" : "enter-view"
            },
            beforeSend: function(x){
                if(x && x.overrideMimeType) x.overrideMimeType("application/json;charset=UTF-8");
            },
            success: function(res){
                window.console.log(res.roles);
                fillItemsRoles(res.roles);
            },
            error : function(res){
                window.console.log(res);
            }
        });
    }

    function selectItemsAll(){

        var statu = false,
            check = document.getElementById('checkbox-select-all');

        if(check.checked)
            statu = true;

        var arrayMovil = ITEMS.movil,
            arrayOtro = ITEMS.otros;

        for(var i = 0; i < arrayMovil.length; i++){

            var element = document.getElementById(arrayMovil[i].element);

            if(element){
                element.checked = statu;
            }

        }

        for(var l = 0; l < arrayOtro.length; l++){

            var element = document.getElementById(arrayOtro[l].element);

            if(element){
                element.checked = statu;
            }
        }
    }

    function createSwitchery(){

        if(typeof Switchery !== 'undefined'){
            var elems = document.querySelectorAll('.js-switch');

            for (var i = 0; i < elems.length; i++) {
              var switchery = new Switchery(elems[i]);
            }
        }
    }

    function fillItemsRoles(items){

        var options = "";

        for(var i = 0; i < items.length; i++){

            options += '<a href="#" id="roles-'+items[i].id_role+'" class="list-group-item" onclick="changeRol(\''+items[i].id_role+'\')"> '+items[i].rol+'</a>';
        }

        div_list_group.innerHTML = options;
    }

    function changeRol(role){

        document.getElementById('checkbox-select-all').disabled = false;

        $("#headModal").html('<h2>Cargando...</h2>');
        $modal0 = $("#pleaseWaitDialog");
        $modal0.modal('show');

        $(".list-group-item").removeClass("active");
        $("#roles-"+role).addClass("active");

        div_panel_acordion.innerHTML = "";

        ID_PERFIL = role;

        $.ajax({
            url: "/api/roles/index.php",
            type: "POST",
            data: {
                "action" : "search-role",
                "role": role
            },
            beforeSend: function(x){
                if(x && x.overrideMimeType) x.overrideMimeType("application/json;charset=UTF-8");
            },
            success: function(res){
                ITEMS = {movil:[],otros:[]};
                fillItemsPermiMovil(res.permiso_movil);
                fillItemsPermiOtro(res.permiso_otro);
            },
            error : function(res){
                window.console.log(res);
            }
        });
    }

    function fillItemsPermiOtro(NODE){

        var actual = NODE.shift();

        if(actual){

            $.ajax({
                url: "/api/roles/index.php",
                type: "POST",
                data: {
                    "action" : "search-menu",
                    "role" : ID_PERFIL,
                    "id-padre": actual.id_menu,
                    "modulo" : actual.modulo
                },
                beforeSend: function(x){
                    if(x && x.overrideMimeType) x.overrideMimeType("application/json;charset=UTF-8");
                },
                success: function(res){
                    fillMenus(actual.id_menu, actual.modulo, res.estru);
                    fillItemsPermiOtro(NODE);
                    fillItems(res.items);
                },
                error : function(res){
                    window.console.log(res);
                }
            });
        }
        else{
            $modal0.modal('hide');
            //createSwitchery();
            addButtonSave();
        }
    }

    function fillItems(array){

        if(array.length > 0){
            for(var i = 0; i < array.length; i++){
                ITEMS.otros.push(array[i]);
            }
        }
    }

    function fillMenus(id, modulo, estru){

        var base = '<div class="panel panel-default">'+
                        '<div class="panel-heading">'+
                            '<h4 class="panel-title">'+
                                '<a data-toggle="collapse" data-parent="#accordion" href="#panel-accordion-'+id+'">'+modulo+'</a>'+
                            '</h4>'+
                        '</div>'+
                        '<div id="panel-accordion-'+id+'" class="panel-collapse collapse">'+
                            '<div id="panel-items-'+id+'" class="panel-body">'+
                                '<ul class="list-group">'+
                                    estru+
                                '</ul>'+
                            '</div>'+
                        '</div>'+
                    '</div>';

        div_panel_acordion.innerHTML += base;
    }

    function addButtonSave(){

        var base = '<div class="panel panel-default">'+
                        '<div class="panel-heading">'+
                            '<div style="left: 44%; position: relative;">'+
                                '<button onclick=save(); type="button" class="btn btn-primary">Guardar</button>'+
                            '</div>'+
                            '<div style="text-align: left; position: relative;">'+
                                '<button onclick=Reiniciar(); type="button" class="btn btn-warning">Reiniciar Perfil</button>'+
                            '</div>'+

                        '</div>'+
                    '</div>';

        div_panel_acordion.innerHTML += base;
    }


    function Reiniciar()
    {
        console.log("Reiniciar Perfil = ", ID_PERFIL);
        swal({
            title: "Reiniciar Perfil",
            text: "¿Está Seguro de Proceder a Reiniciar El perfil? esto borrará toda la configuración del menú del Perfil Actual",
            type: "info",

            cancelButtonText: "No",
            cancelButtonColor: "#14960a",
            showCancelButton: true,

            confirmButtonColor: "#55b9dd",
            confirmButtonText: "Si",
            closeOnConfirm: true
        }, function() {


        $.ajax({
            url: "/api/roles/index.php",
            type: "POST",
            data: {
                "action" : "reiniciar-perfil",
                "role" : ID_PERFIL
            },
            beforeSend: function(x){
                if(x && x.overrideMimeType) x.overrideMimeType("application/json;charset=UTF-8");
            },
            success: function(res){
                window.console.log(res);
                swal("Éxito", "Perfil Reiniciado con Éxito", "success");
                window.location.reload();
            },
            error : function(res){
                window.console.log(res);
            }
        });


        });
    }

    function save(){

        var array_movil = ITEMS.movil.slice(),
            array_otros = ITEMS.otros.slice();


        console.log("array_movil = ", array_movil);
        console.log("array_otros = ", array_otros);
        //return;

        $("#headModal").html('<h2>Guardando Perfil...</h2>');
        $modal0 = $("#pleaseWaitDialog");
        $modal0.modal('show');

        saveMovil(array_movil);

        function saveMovil(array){

            var actual = array.shift();


            if(actual){

                var element = document.getElementById(actual.element),
                    state = 0;

                //if($("#"+element.checked).prop("checked"))
                if(element.checked)
                    state = 1;
                console.log("++++++++++++++ saveMovil() +++++++++++++++");
                console.log("actual = ", actual);
                console.log("element.checked = ", element.checked);
                console.log("ID_PERFIL = ", ID_PERFIL);
                console.log("actual.id = ", actual.id);
                
                $.ajax({
                    url: "/api/roles/index.php",
                    type: "POST",
                    data: {
                        "action" : "save-movil",
                        "usuario_log" : "<?php echo $_SESSION['cve_usuario']; ?>",
                        "role" : ID_PERFIL,
                        "id": actual.id,
                        "state": state
                    },
                    beforeSend: function(x){
                        if(x && x.overrideMimeType) x.overrideMimeType("application/json;charset=UTF-8");
                    },
                    success: function(res){
                        window.console.log(res);
                        saveMovil(array);
                    },
                    error : function(res){
                        window.console.log(res);
                        saveMovil(array);
                    }
                });
            }
            else{
                console.log("++++++++++++++ saveOtros() [ELSE] +++++++++++++++");
                saveOtros(array_otros);
            }
        }

        function saveOtros(array){

            var actual = array.shift();

            if(actual){

                var elems = actual.element.split("-"),
                    element = document.getElementById(actual.element),
                    state = 0,
                    CASE = 4,
                    change = 0;

                if(element){ 

                    //if($("#"+element.checked).prop("checked"))
                    if(element.checked)
                        state = 1;

                    if(elems[1] === "ver"){
                        CASE = 1;
                    }
                    else if(elems[1] === "agregar"){
                        CASE = 2;
                    }
                    else if(elems[1] === "editar"){
                        CASE = 3;
                    }

                    if(actual.state !== "")
                        change = 1;

                    console.log("++++++++++++++ saveOtros() +++++++++++++++");
                    console.log("actual = ", actual);
                    console.log("element.checked = ", element.checked);
                    console.log("elems = ", elems);
                    console.log("change = ", change);
                    console.log("state = ", state);
                    
                    if(change !== state){ 
                        $.ajax({
                            url: "/api/roles/index.php",
                            type: "POST",
                            data: {
                                "action" : "save-otros",
                                "usuario_log" : "<?php echo $_SESSION['cve_usuario']; ?>",
                                "role" : ID_PERFIL,
                                "id": elems[2],
                                "state": state,
                                "case": CASE
                            },
                            beforeSend: function(x){
                                if(x && x.overrideMimeType) x.overrideMimeType("application/json;charset=UTF-8");
                            },
                            success: function(res){
                                console.log("SUCCESS: ", res);
                                saveOtros(array);
                            },
                            error : function(res){
                                console.log("ERROR: ", res);
                                saveOtros(array);
                            }
                        });
                    }
                    else{
                        saveOtros(array);
                    }
                    
                }
                else{
                    saveOtros(array);
                }
            }
            else{
                $modal0.modal('hide');
            }
        }
    }

    function fillItemsPermiMovil(node){

        var options = '<ul class="list-group">';

        var base = '<div class="panel panel-default">'+
                        '<div class="panel-heading">'+
                            '<h4 class="panel-title">'+
                                '<a data-toggle="collapse" data-parent="#accordion" href="#panel-accordion-movil">Definición de Perfiles WEB | Móvil</a>'+
                            '</h4>'+
                        '</div>'+
                        '<div id="panel-accordion-movil" class="panel-collapse collapse">'+
                            '<div id="div-items-movil" class="panel-body">'+
                            '</div>'+
                        '</div>'+
                    '</div>';

        div_panel_acordion.innerHTML += base;

        if(node.length){

            for(var i = 0; i < node.length; i++){

                var check = "";

                if(node[i]["state"] === "1")
                    check = "checked";

                options += '<li class="list-group-item d-flex justify-content-between align-items-center" >'+
                                '<h5 style="float: none; ">'+node[i]["name"]+'</h5>'+
                                '<div class="form-group group" style="margin-bottom: 0;">'+
                                    '<input type="checkbox" name="fancy-checkbox-primary" id="checkbox-movil-'+node[i]["id"]+'" '+check+'/>'+
                                    '<div class="btn-group bt-group">'+
                                        '<label for="checkbox-movil-'+node[i]["id"]+'" class="btn btn-default active ">HABILITAR'+
                                        '</label>'+
                                        '<label for="checkbox-movil-'+node[i]["id"]+'" class="btn btn-primary">'+
                                            '<span class="glyphicon glyphicon-ok "></span>'+
                                            '<span> </span>'+
                                        '</label>'+
                                    '</div>'+
                                '</div>'+
                            '</li>';



                ITEMS.movil.push({element : 'checkbox-movil-'+node[i]["id"], id : node[i]["id"]});
            }
        }

        options += '<ul class="list-group">';

        document.getElementById('div-items-movil').innerHTML = options;

        //createSwitchery();
    }

    function getProfiles(rol) {
        $("#hidden_rol").val(rol);
        $(".list-group-item").removeClass("active");
        $("#item_rol_"+rol).addClass("active");
        l.ladda( 'stop' );

        $("#headModal").html('<h2>Cargando...</h2>');
        $modal0 = $("#pleaseWaitDialog");
        $modal0.modal('show');

        $.ajax({
            type: "POST",
            data: {
                rol : rol
            },
            url: '/api/perfiles/lista/index.php',
            success: function(data) {
                $('#tabla_perfiles').html(data);
                $('#btnSave').show();
                $('#selectedalldiv').show();
                $('input[type="checkbox"]').iCheck({
                    checkboxClass: 'icheckbox_square-green',
                    radioClass: 'iradio_square-green'
                });
                $('#select-all').on('ifChecked', function(){
                    $('input[type="checkbox"]').each(function() {
                        if (this.id!="select-all") $(this).iCheck('check');
                    });
                });

                $('#select-all').on('ifUnchecked', function() {
                    $('input[type="checkbox"]').each(function() {
                        if (this.id!="select-all") $(this).iCheck('uncheck');
                    });
                });

                $('#select-all').iCheck('uncheck');

                $modal0.modal('hide');
            }
        });
    }


    ///************************ CREAR PROFILE *************************/
/*
    l.click(function() {

        l.ladda( 'start' );

        $("#headModal").html('<h2>Guardando Perfil...</h2>');
        $modal0 = $("#pleaseWaitDialog");
        $modal0.modal('show');

        var formData = new FormData(document.getElementsByName('formProfiles')[0]);// yourForm: form selector
        formData.append("action", "add");
        formData.append("rol", $("#hidden_rol").val());
        $.ajax({
            type: "POST",
            url: "/api/perfiles/update/index.php",// where you wanna post
            data: formData,
            processData: false,
            contentType: false,
            error: function(jqXHR, textStatus, errorMessage) {
                console.log(errorMessage); // Optional
            },
            success: function(data) {
                console.log(data)
                l.ladda( 'stop' );
                $modal0.modal('hide');
                var body = $("html, body");
                body.stop().animate({scrollTop:0}, 500, 'swing', function() {});
            }
        });
    });*/
</script>
