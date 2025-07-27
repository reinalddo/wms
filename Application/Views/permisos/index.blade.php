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
<!-- Sweet alert -->
<script src="/js/plugins/sweetalert/sweetalert.min.js"></script>
<!-- Select -->
<script src="/js/select2.js"></script>
<!-- File Upload -->

<script src="/js/plugins/iCheck/icheck.min.js"></script>

<link href="/css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">
<link href="/css/plugins/ladda/select2.css" rel="stylesheet"/>
<!-- Sweet Alert -->
<link href="/css/plugins/iCheck/custom.css" rel="stylesheet">
<link href="/css/animate.css" rel="stylesheet">
<link href="/css/style.css" rel="stylesheet">



<div class="modal" id="modal-loading" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" id="modal-loading-text">
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

<div class="panel panel-default">
    <div class="panel-heading">
        <h4 class="panel-title">
            <a data-toggle="collapse" data-parent="#accordionThree" href="#collapse-1" aria-expanded="false" class="collapsed">
                Permisos Generales
            </a>
        </h4>
    </div>
    <div id="collapse-1" class="panel-collapse collapse" aria-expanded="false" style="height: 0px;">
        <div class="panel-body">
            <div class="row">                
                <div class="col-md-12">
                    <p></p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="wrapper wrapper-content  animated fadeInRight">
    <h3>Permisos</h3>

    <div class="row">
        <div class="col-md-12">
            <div class="ibox-title col-md-3">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title">Roles</h3>
                    </div>
                    <div class="box-body">
                        <div class="list-group">
                            @foreach ($roles as $value)
                                <a class="list-group-item" href="#" id="item-rol-{{ $value->id_role }}" onclick="obtenerPerfil('{{ $value->id_role }}');">{{ $value->rol }}</a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            <div class="ibox-title col-md-9">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title">Módulos</h3>
                    </div>
                    <div class="box-body">
                        <form method="post" id="formProfiles" name="formProfiles">
                            <div class="ibox float-e-margins" id="selectedalldiv" style="display: none">
                                <div class="row" style="background-color: #e7eaec; padding: 12px">
                                    <div class="col-md-12" style="padding-top: 5px; padding-right: 5px">
                                        <h5>Seleccionar Todo</h5>&nbsp;&nbsp;
                                        <input type="checkbox" id="select-all" name="select-all"  />
                                    </div>
                                </div>
                            </div>
                            <div class="table table-hover table-mail" id="tabla_perfiles">
                                <div class="row">Selecciona un rol para visualizar los módulos </div>
                            </div>
                            <div class="pull-right">
                                <input type="hidden" name="hidden_rol" id="hidden_rol">
                                <button id="btnSave" type="button" class="btn btn-info ladda-button" style="display: none; margin: 20px 0 0 auto; width: 100px">Guardar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<script>

    var collapse = `
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4 class="panel-title">
                <a data-toggle="collapse" data-parent="#accordionThree" href="#collapse-:n" aria-expanded="false" class="collapsed">
                    :titulo
                </a>
            </h4>
        </div>
        <div id="collapse-:n" class="panel-collapse collapse" aria-expanded="false" style="height: 0px;">
            <div class="panel-body">
                <div class="row">                
                    <div class="col-md-12">
                        :contenido
                    </div>
                </div>
            </div>
        </div>
    </div>`;

    function obtenerPerfil(rol) {
        $("#hidden_rol").val(rol);
        $(".list-group-item").removeClass("active");
        $("#item-rol-"+rol).addClass("active");
        

        $("#modal-loading-text").html('<h2>Cargando...</h2>');
        $("#modal-loading").modal('show');

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
                $("#modal-loading").modal('hide');
            }
        });
    }





    l.click(function() {

        l.ladda( 'start' );

        $("#modal-loading").html('<h2>Guardando Perfil...</h2>');
        $modal0 = $("#modal-loading");
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
    });
</script>