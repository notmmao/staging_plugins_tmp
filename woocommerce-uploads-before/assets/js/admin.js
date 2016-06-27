/*
 * @author WPFortune
 */

jQuery(document).ready(function($) {

     $('input[name="wpf_umf_before_uploads_required"]').change(function() {

        var box = $('#wpf-umf-upload-before-box');

        ($(this).is(':checked')) ? box.slideDown() : box.slideUp();

     });


});