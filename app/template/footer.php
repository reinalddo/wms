


        </div>
      </div>
    </div>
    <!-- Mainly scripts -->
    <script src="/js/plugins/metisMenu/jquery.metisMenu.js"></script>
    <!--<script src="/js/plugins/jeditable/jquery.jeditable.js"></script>-->
    <script src='/js/jquery.marquee.min_2.js'></script>


    <?php 
    $val_sesion = 0;
    if(isset($_SESSION['cve_usuario']))
    {
        $val_sesion = 1;
    }
    ?>
    <input type="hidden" id="val_sesion" value="<?php echo $val_sesion; ?>">

    <script>
        $(document).ready(function(){
                 //   console.log("enlace = ", '<?php echo $_SERVER['HTTP_HOST']; ?>');
            console.log("permiso_consultar = ", $("#permiso_consultar").val());
            console.log("permiso_registrar = ", $("#permiso_registrar").val());
            console.log("permiso_editar = ", $("#permiso_editar").val());
            console.log("permiso_eliminar = ", $("#permiso_eliminar").val());




          $('.marquee').marquee({
            //speed in milliseconds of the marquee
            duration: 9000,
            //gap in pixels between the tickers
            gap: 20,
            //time in milliseconds before the marquee will start animating
            delayBeforeStart: 10,
            //'left' or 'right'
            direction: 'left',
            //true or false - should the marquee be duplicated to show an effect of continues flow
            duplicated: false
          });

          //****************************************************************************************************
          //*************************** CONTROL DE TIEMPO SE SESIÓN ********************************************
          /****************************************************************************************************
          var minutos_sesion = 30;
          var segundos_sesion = minutos_sesion*60;
          var timer = null, cierre_update = null, tiempo_sesion = segundos_sesion*1000;//10min

          $( "body" ).mousemove(function( event ) 
          {
              //console.log("clear");
              clearTimeout(timer); //cancel the previous timer.
              timer = null;

              timer = setTimeout('location.replace("/account/out")',tiempo_sesion);
          });

          $( "body" ).keyup(function( event ) 
          {
              //console.log("clear");
              clearTimeout(timer); //cancel the previous timer.
              timer = null;

              timer = setTimeout('location.replace("/account/out")',tiempo_sesion);
          });


          $("body").bind("touchstart",function(e)
          {
              //console.log("clear");
              clearTimeout(timer); //cancel the previous timer.
              timer = null;

              timer = setTimeout('location.replace("/account/out")',tiempo_sesion);
          });


          timer = setTimeout('location.replace("/account/out")',tiempo_sesion);

          cierre_update = setInterval (function(){
              $.ajax({
                url:'/api/usuarios/update/index.php',
                data: {
                  action: 'fecha_cierre_sesion'
                },
                datatype: 'json',
                method: 'POST'
              }).done(function(data)
              {
                    var sesion = "<?php echo $_SESSION['id_user']; ?>";
                    console.log("ID_USER SESION = ", sesion);
                    console.log("FECHA CIERRE SESION ACTUALIZADA");
              });
          },tiempo_sesion/2);
          //****************************************************************************************************/
          //*************************** FIN CONTROL DE TIEMPO SE SESIÓN ****************************************/
          //****************************************************************************************************/

        });
            if($("#permiso_consultar").val() == 1) $(".permiso_consultar").show(); else $(".permiso_consultar").hide();
            if($("#permiso_registrar").val() == 1) $(".permiso_registrar").show(); else $(".permiso_registrar").hide();
            if($("#permiso_editar").val() == 1)    $(".permiso_editar").show();    else $(".permiso_editar").hide();
            if($("#permiso_eliminar").val() == 1)  $(".permiso_eliminar").show();  else $(".permiso_eliminar").hide();

    </script>

<?php 
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
mysqli_set_charset($conn, 'latin1');
$sql = "SELECT * FROM t_mensaje WHERE clave = 'FPAG' AND activo = 1";
$rs = mysqli_query($conn, $sql);
$row = mysqli_fetch_array($rs);
$titulo_fuera_pago = $row['descripcion'];
$mensaje_fuera_pago = $row['mensaje'];
if(mysqli_num_rows($rs) > 0)
{
?>

<div class="theme-config" id="cogs">
    <div class="theme-config-box show">
        <div class="spin-icon">
            <i class="fa fa-cogs fa-spin"></i>
        </div>
        <div class="skin-settings">
            <div class="title">
            <small style="text-transform: none;font-weight: 400">
                .
            </small></div>
            <div class="setings-item">
            </div>                  
        </div>
    </div>
</div>

  <div id='toast'>

  <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.0.1/css/toastr.css" rel="stylesheet"/>
  <script src="/js/plugins/toastr/toastr.min.js"></script>
  <script>
    //$(document).ready(function() {
    $(function() {

        toastr.success('<?php echo $titulo_fuera_pago; ?>', '<?php echo utf8_encode($mensaje_fuera_pago); ?>', {
            timeOut: 5000,
            extendedTimeout: 5000,
            closeButton: true,
            progressBar: true,
            showDuration: 1000,
            hideDuration: 400,
            color: '#F00',
            preventDuplicates: true
        });
        
                setTimeout("$('#cogs').fadeOut()", 5000);

    });

  </script>
</div>

<style>
.toast-success {
  background-color: #bd362f;
}
</style>
<?php 
}
@mysqli_close($conn);
?>

  </body>
</html>
