jQuery(document).ready(function($) {

    $('#wpf-umf-upload-boxes').sortable({

        axis: 'y',
        update: function (event, ui) {

            reset_boxes();

        }
    });

    function reset_boxes()
    {

        $('#wpf-umf-upload-container .wpf-umf-upload-box').each(function(index) {

            var new_id = index + 1;

            $(this).attr('data-id', new_id).attr('id', 'wpf-umf-upload-box-'+new_id);

            // Add new names / ids to fields

            $(this).find('input, textarea, select').each(function( index ) {

                // Name
                var input_name = String($(this).attr('name'));
                $(this).attr('name', input_name.replace(/\[(\d*)\]/g, '['+new_id+']'));

                // Id
                var input_id = String($(this).attr('id'));
                $(this).attr('id', input_id.replace(/wpf_umf_upload_(\d*)/g, 'wpf_umf_upload_'+new_id));

            });

            $(this).find('label').each(function( index ) {

                var label_for = String($(this).attr('for'));
                $(this).attr('for', label_for.replace(/wpf_umf_upload_(\d*)/g, 'wpf_umf_upload_'+new_id));

            });

        });

    }


    $('a#wpf-umf-upload-add-set').click(function(e) {

        e.preventDefault();

        var old_upload_box  =   $('#wpf-umf-upload-container .wpf-umf-upload-box').last();
        var new_upload_box  =   old_upload_box.clone();

        new_upload_box.appendTo('#wpf-umf-upload-boxes');
        reset_boxes();

    });

    $('#wpf-umf-upload-boxes').on('click', '.wpf-umf-upload-box-advanced', function(e) {

        e.preventDefault();

        var clicked_element = $(this);
        var toggle_element = $(this).parent('.wpf-umf-upload-box').find('.wpf-umf-upload-box-collapse');

        toggle_element.slideToggle(function(){

            if (toggle_element.is(':visible')) {
              clicked_element.html('<span class="dashicons dashicons-arrow-up"></span>' + uploadset_message.less_info);
            } else {
              clicked_element.html('<span class="dashicons dashicons-arrow-down"></span> '  + uploadset_message.more_info);
            }

        });

    });

    $('#wpf-umf-upload-boxes').on('click', '.wpf-umf-upload-box-delete', function(e) {

        e.preventDefault();

        var elements_count = $('#wpf-umf-upload-boxes .wpf-umf-upload-box').length;
        var clicked_element = $(this);

        if (elements_count == 1) {

            alert(uploadset_message.cant_delete_upload_box);

        } else {

            $(this).parent('.wpf-umf-upload-box').remove();
            reset_boxes();

        }

    });


    $('input[name$="[variation_show]"]').change(function(){

        var tid = $(this).parents('.wpf-umf-upload-box').data('id');

        if ($(this).is(':checked')) {
            $('select#wpf_umf_upload_'+tid+'_variation_show option').removeAttr('selected');
        }

    });

    $('select[name$="[variation_show][]"]').change(function(){

        var tid = $(this).parents('.wpf-umf-upload-box').data('id');
        var all_radio = $('input#wpf_umf_upload_'+tid+'_variation_show_0');

        if ($(this).val() == null) {
            all_radio.attr('checked','checked');
        } else {
            all_radio.removeAttr('checked');
        }

    });


});