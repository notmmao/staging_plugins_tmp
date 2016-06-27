/**
 * Created by Stephen on 5/2/2015.
 */
jQuery( function ( $ ) {

    function update_customer_data() {

        var user_id = $('#customer_user').val();

        var data = {
            'action': 'wcurp_set_customer_data',
            'user_id': user_id,
            'security': urp_ajax_object.security
        };

        $.post(ajaxurl, data, function(response) {
            $('#base_price').html(response.base_price);
            $('#multiplier').html(response.multiplier);
            $('#tax_exempt').html(response.tax_exempt);
            $('#disable_shipping').html(response.disable_shipping);
            $('#is_wholesale').html(response.is_wholesale);
        });
    }

    $('#customer_user').change(function(){
        update_customer_data();
    });

});