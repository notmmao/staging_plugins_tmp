jQuery(document).ready(function($){

    $('.wpf-umf-single-upload-field').each(function(){

        /* Params */
        var container           = $(this).attr('id');
        var browse_button       = $(this).find('.wpf-umf-browse-button').attr('id');
        var upload_button       = $(this).find('.wpf-umf-upload-button');
        var file_list           = $(this).find('.wpf-umf-file-list');
        var error_el            = $(this).find('.wpf-umf-error-el');
        var max_uploads         = parseInt($(this).data('maxuploads'));
        var max_file_size       = parseInt($(this).data('maxfilesize'));
        var order_id            = $('#wpf-umf-upload-boxes input[name="wpf_umf_order_id"]').val();
        var product_id          = $(this).data('productid');
        var product_item_number = $(this).data('itemnumber');
        var upload_type         = $(this).data('uploadtype');
        var allowed             = $(this).data('allowed');
        var upload_mode         = $(this).data('uploadmode');
        var file_number         = calculate_file_number(max_uploads, container);
        var mime_types;

        if (allowed != undefined) {

            //mime_types = [{ extensions : allowed }];

        }

        if(wpf_umf_uploader.dropzone == 1) {
      		var dropzone = $('#'+container).find('.wpf-umf-dropzone').attr('id');
      	} else {
      		var dropzone = false;
      	}

        /* Uploader begin */
        var uploader = new plupload.Uploader({
            runtimes : 'html5,flash,silverlight,html4',
            browse_button : browse_button, // you can pass in id...
            container: container,
            drop_element: dropzone,
            url : wpf_umf_uploader.ajaxurl,
            chunk_size : wpf_umf_uploader.max_chunk_size+'mb',
            filters : {
                max_file_size : max_file_size+'mb',
                mime_types: mime_types
            },
            multipart_params: {
                action: 'wpf_umf_ajax_upload',
                order_id: order_id,
                product_id: product_id,
                product_item_number: product_item_number,
                upload_type: upload_type,
                file_number: file_number,
                upload_mode: upload_mode,
                _wpf_umf_nonce: wpf_umf_uploader.nonce,
                wpf_umf_sid: wpf_umf_uploader.sid,
            },
            flash_swf_url : wpf_umf_uploader.flash_swf_url,
            silverlight_xap_url : wpf_umf_uploader.silverlight_xap_url,
            init: {
                PostInit: function() {

                    $('#wpf-umf-browser-check').hide();

                    upload_button.click(function(e) {
                        e.preventDefault();
                        uploader.start();
                    });


                },

                FilesAdded: function(up, files) {

                    error_el.fadeOut('fast');

                    file_number = calculate_file_number(max_uploads, container);
                    uploader.settings.multipart_params.file_number = file_number;

                    if (up.files.length > max_uploads) {

                        plupload.each(files, function(file) {
                            uploader.removeFile(file);
                        });

                        error_el.html(wpf_umf_uploader.max_amount_uploads_reached).fadeIn('fast');

                    } else {

                        plupload.each(files, function(file) {
                            file_list.append('<div id="' + file.id + '"><span class="wpf-umf-upload-file-name">' + file.name + ' (' + plupload.formatSize(file.size) + ')</span> <span class="wpf-umf-upload-percent">0%</span> <div class="clear"></div> <div class="wpf-umf-upload-bar"><div class="wpf-umf-upload-bar-progress"></div></div></div>');
                        });

                        if (wpf_umf_uploader.autostart == 1) {
                            up.start();
                        }

                    }
                },
                BeforeUpload: function(up, file) {
                    uploader.settings.multipart_params.file_name = file.name;
                },
                FileUploaded: function(up, file, response) {

                    json_response = jQuery.parseJSON(response.response);

                    if (json_response.OK == 1) {

                        $('div#'+file.id).find('.wpf-umf-upload-bar-progress').addClass('upload-success');
                        $('#'+container).find('.wpf-umf-uploaded-files-container').append(json_response.html);
                        file_number = calculate_file_number(max_uploads, container);
                        uploader.settings.multipart_params.file_number = file_number;

                    } else {
                        $('div#'+file.id).find('.wpf-umf-upload-bar-progress').addClass('upload-error');
                        error_el.html(json_response.info).fadeIn('fast');
                        setTimeout(function(){ up.removeFile(file); }, 50);

                    }

                },

                UploadProgress: function(up, file) {
                     $('div#'+file.id).find('.wpf-umf-upload-bar .wpf-umf-upload-bar-progress').css('width', file.percent+'%');
                     $('div#'+file.id).find('.wpf-umf-upload-percent').html(file.percent+'%');
                },
                UploadComplete: function (up, files) {

                    up.splice();

                },
                Error: function(up, err) {
                    error_el.html('\nError #' + err.code + ': ' + err.message).fadeIn('fast');
                }
            }
        });

        uploader.init();

    });

    /* Calculate file number */

    function calculate_file_number(max_uploads, container) {

        var file_number = max_uploads + 1; // backup

        for (i = 1; i <= max_uploads; i++) {

            check = $('#'+container).find('.wpf-umf-single-uploaded-file[data-filenumber="'+i+'"]').length;

            if (check == 0) {
              file_number = i;
              break;
            }

        }

        return file_number;

    }


});