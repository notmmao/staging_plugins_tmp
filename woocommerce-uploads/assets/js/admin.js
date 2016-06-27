/*
 * @author WPFortune
 */

jQuery(document).ready(function($) {

    /*
     * Navigation tabs
     */

     // On page load
     if (window.location.hash != '') {

        var current_tab_id = window.location.hash.replace('#', '');
        switch_tabs(current_tab_id);
        set_form_action(current_tab_id);

     }

     // On click

     var form_action;

     $('#wpf-umf-settings-tabs a').click(function() {

          var tab_id = $(this).data('id');

          switch_tabs(tab_id);

          // Add hashtag to form action
          set_form_action($(this).attr('href').replace('#', ''));

     });

     function set_form_action(tab_id) {

          // Add hashtag to form action
          var form_element = $('form#wpf-umf-main-form');

          if (form_action == undefined) {
                form_action = form_element.attr('action');
          }

          form_element.attr('action', form_action+'#'+tab_id);
     }

     function switch_tabs(tab_id) {

        $('#wpf-umf-settings-tabs .nav-tab').removeClass('nav-tab-active');
        $('a#wpf-umf-settings-tab-'+tab_id).addClass('nav-tab-active');

        $('.wpf-umf-settings-container').hide();

        $('#wpf-umf-settings-container-'+tab_id).show();

     }

    /*
     * Thumbnail quality slider
     */
    var quality_input_el = $('input#wpf_umf_thumbnail_wp_quality');
    var quality_slider_el = $('#wpf_umf_thumbnail_quality_slider');

    quality_slider_el.slider({
          range: "min",
          value: 1,
          step: 2,
          min: 0,
          max: 100,
          slide: function(event, ui) {
                quality_input_el.val(ui.value);
          }
    });

    // Default set slider to corresponding input value
    quality_slider_el.slider("value", parseInt(quality_input_el.val()));

    /*
     * Show / hide boxes depending on selected input values
     */

     $('input[name="wpf_umf_uploader"]').change(function() {

        var box = $('#wpf-umf-uploader-box');

        ($(this).val() == 'ajax') ? box.slideDown() : box.slideUp();

     });

     $('input[name="wpf_umf_thumbnail_enable"]').change(function() {

        var box = $('#wpf-umf-thumbnail-box');

        ($(this).is(':checked')) ? box.slideDown() : box.slideUp();

     });

     $('input[name="wpf_umf_message_enable"]').change(function() {

        var box = $('#wpf-umf-custom-messages-box');

        ($(this).is(':checked')) ? box.slideDown() : box.slideUp();

     });

     $('input[name="wpf_umf_notifications_enable"]').change(function() {

        var box = $('#wpf-umf-notifications-box');

        ($(this).is(':checked')) ? box.slideDown() : box.slideUp();

     });

     /* Order page, uploaded files */

     $('#wpf_umf_uploaded_file_submit').click(function(e) {

        e.preventDefault();

        var formdata = $('#wpf-umf-order-uploads').find('input, select').serialize();

        var approve_action = $('#wpf-umf-order-uploads select[name="wpf_umf_uploaded_file_approve"]').val();

        formdata = formdata + '&action=wpf_umf_uploads_approve';

        $.post(wpf_umf_admin.ajaxurl, formdata, function(data) {

            if (data.success == 1) {
                $('#wpf-umf-order-uploads input[name^="wpf_umf_uploaded_file"]:checked').each(function() {

                    if (approve_action == 'accept') {
                      var newclass = 'approved';
                    } else {
                      var newclass = 'declined';
                    }
                    $(this).attr('checked', false);
                    $(this).parents('.wpf-umf-ou-uploaded-file').attr('class', 'wpf-umf-ou-uploaded-file '+newclass);
                });
            } else {
                alert('An error occured saving the data, please refresh this page and check if changes took place.');
            }

        }, 'json');

     });

     /* Order page, send email */

     $('#wpf_umf_uploads_email_submit').click(function(e) {

        e.preventDefault();

        var formdata = $('#wpf-umf-order-uploads-email').find('textarea, select, input').serialize();

        formdata = formdata + '&action=wpf_umf_order_uploads_email';

        $.post(wpf_umf_admin.ajaxurl, formdata, function(data) {

            if(data.success == 1) {
                $('#wpf-umf-order-uploads-email-error').fadeOut('fast');
                $('#wpf-umf-order-uploads-email-success').html(wpf_umf_admin.approve_email_success).fadeIn('fast');
                $('textarea#wpf_umf_order_uploads_email').val('');
            } else {

                $('#wpf-umf-order-uploads-email-success').fadeOut('fast');
                $('#wpf-umf-order-uploads-email-error').html(data.error).fadeIn('fast');

            }

        }, 'json');


     });

     /* Current products confirmation */

     $('a#wpf-umf-enable-current-products').click(function() {

        return confirm(wpf_umf_admin.enable_current_products_confirm);

     });

     /* Check all uploads for approval */

     $('input.wpf-umf-ou-upload-check-all').click(function(){

        if ($(this).is(':checked')) {
            $('input[name^="wpf_umf_uploaded_file"]').prop('checked', true);
        } else {
            $('input[name^="wpf_umf_uploaded_file"]').prop('checked', false);
        }

     });

});