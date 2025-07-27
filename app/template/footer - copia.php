        </div>
      </div>
    </div>

    <div class="page-footer">
      <div class="page-footer-inner">
        2015 &copy; <?php echo SITE_TITLE; ?>
      </div>
      <div class="scroll-to-top">
        <i class="icon-arrow-up"></i>
      </div>
    </div>

    <!--[if lt IE 9]>
    <script src="<?php echo ST; ?>global/plugins/respond.min.js"></script>
    <script src="<?php echo ST; ?>global/plugins/excanvas.min.js"></script>
    <![endif]-->

    <script src="<?php echo ST; ?>global/plugins/jquery.min.js" type="text/javascript"></script>
    <script src="<?php echo ST; ?>global/plugins/jquery-migrate.min.js" type="text/javascript"></script>

    <script src="<?php echo ST; ?>global/plugins/jquery-ui/jquery-ui.min.js" type="text/javascript"></script>
    <script src="<?php echo ST; ?>global/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
    <script src="<?php echo ST; ?>global/plugins/bootstrap-hover-dropdown/bootstrap-hover-dropdown.min.js" type="text/javascript"></script>
    <script src="<?php echo ST; ?>global/plugins/jquery-slimscroll/jquery.slimscroll.min.js" type="text/javascript"></script>
    <script src="<?php echo ST; ?>global/plugins/jquery.blockui.min.js" type="text/javascript"></script>
    <script src="<?php echo ST; ?>global/plugins/jquery.cokie.min.js" type="text/javascript"></script>
    <script src="<?php echo ST; ?>global/plugins/uniform/jquery.uniform.min.js" type="text/javascript"></script>
    <script src="<?php echo ST; ?>global/plugins/bootstrap-switch/js/bootstrap-switch.min.js" type="text/javascript"></script>

    <script src="<?php echo ST; ?>global/plugins/jqvmap/jqvmap/jquery.vmap.js" type="text/javascript"></script>
    <script src="<?php echo ST; ?>global/plugins/jqvmap/jqvmap/maps/jquery.vmap.russia.js" type="text/javascript"></script>
    <script src="<?php echo ST; ?>global/plugins/jqvmap/jqvmap/maps/jquery.vmap.world.js" type="text/javascript"></script>
    <script src="<?php echo ST; ?>global/plugins/jqvmap/jqvmap/maps/jquery.vmap.europe.js" type="text/javascript"></script>
    <script src="<?php echo ST; ?>global/plugins/jqvmap/jqvmap/maps/jquery.vmap.germany.js" type="text/javascript"></script>
    <script src="<?php echo ST; ?>global/plugins/jqvmap/jqvmap/maps/jquery.vmap.usa.js" type="text/javascript"></script>
    <script src="<?php echo ST; ?>global/plugins/jqvmap/jqvmap/data/jquery.vmap.sampledata.js" type="text/javascript"></script>
    <script src="<?php echo ST; ?>global/plugins/bootstrap-summernote/summernote.min.js" type="text/javascript"></script>

    <script src="<?php echo ST; ?>global/plugins/morris/morris.min.js" type="text/javascript"></script>
    <script src="<?php echo ST; ?>global/plugins/morris/raphael-min.js" type="text/javascript"></script>
    <script src="<?php echo ST; ?>global/plugins/jquery.sparkline.min.js" type="text/javascript"></script>

    <script src="<?php echo ST; ?>global/scripts/metronic.js" type="text/javascript"></script>
    <script src="<?php echo ST; ?>admin/layout4/scripts/layout.js" type="text/javascript"></script>
    <script src="<?php echo ST; ?>admin/layout4/scripts/demo.js" type="text/javascript"></script>
    <script src="<?php echo ST; ?>admin/pages/scripts/index3.js" type="text/javascript"></script>
    <script src="<?php echo ST; ?>admin/pages/scripts/tasks.js" type="text/javascript"></script>
    <script src="<?php echo ST; ?>bootstrap-colorpicker/dist/js/bootstrap-colorpicker.min.js" type="text/javascript"></script>

    <script>
    jQuery(document).ready(function() {
       Metronic.init(); // init metronic core componets
       Layout.init(); // init layout

       $('#summernote_1').summernote({height: 300});

       var postForm = function() {
         var content = $('textarea[name="content"]').html($('#summernote_1').code());
       }

       $(function(){
          $('.colorpicker').colorpicker();
      });



      /**
      *** Preview images ( File upload )
      **/

      $( 'body' ).on( 'click', '.property-preview-photos-upload-go', function() {

        $( '.property-preview-photos-upload' ).last().trigger( 'click' );

        return false;

      } );

      $( 'body' ).on( 'change', '.property-preview-photos-upload', function() {

        for( var i = 0; i < this.files.length; ++i ) {

          var name = this.files[i]['name'];
          var reader = new FileReader();
          var featured = ( !$( 'input[name="featured"]' ).val() ) ? 'featured' : '';

          reader.onload = function( e ) {
            var count = $( '.property-preview-photos-upload' ).last().attr( 'data-number' );

            if( !$( 'input[name="featured"]' ).val() ) {
              $( 'input[name="featured"]' ).val( name );
            }

            $( '.property-preview-photos-upload' ).last().hide();
            $( '.uploaded_preview' ).prepend( '<p>' + name + '</p>' );
            $( '.uploaded_files' ).append( '<input type="file" name="photos[]" class="btn btn-grey property-preview-photos-upload" data-number="' + ++count + '" style="display: none;" />' );
          }

          reader.readAsDataURL( this.files[i] );

        }

      } );

    });
    </script>

  </body>
</html>
