jQuery(document).ready(function($) {

     $('#wpf-umf-upload-boxes').on('click', 'a.wpf-umf-suf-delete-button', function(e) {

        e.preventDefault();

        if (confirm(wpf_umf_main.delete_confirm)) {

            var tel = $(this);

            $.post(wpf_umf_main.ajaxurl, {
              'action': 'wpf_umf_ajax_delete_upload',
              '_wpf_umf_nonce': wpf_umf_main.nonce,
              'order_id': $('input[name="wpf_umf_order_id"]').val(),
              'product_id': tel.data('productid'),
              'item_number': tel.data('itemnumber'),
              'uploader_type': tel.data('uploadertype'),
              'file_number': tel.data('filenumber'),
              'mode': tel.data('mode'),
              'uploadmode': tel.data('uploadmode'),
              'wpf_umf_sid': wpf_umf_main.sid
            }, function(data) {

                if(data.success == 1) {

                    tel.parents('.wpf-umf-single-uploaded-file').fadeOut('fast', function(){   

                        if (tel.data('mode') == 'html') {
                            $(this).replaceWith('<div class="wpf-umf-single-upload-field"><input type="file" name="wpf_upload['+tel.data('productid')+']['+tel.data('itemnumber')+']['+tel.data('uploadertype')+']['+tel.data('filenumber')+']" /></div>');
                        } else {
                            $(this).remove();
                        }
                    });

                } else {

                    alert(data.error);

                }

            }, 'json');

        }

     });

     $('#wpf-umf-upload-boxes form').submit(function(){
        $('#wpf-umf-uploading').fadeIn();
     });

});