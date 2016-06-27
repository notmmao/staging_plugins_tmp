/* global wp, woocommerce_admin_meta_boxes_variations, woocommerce_admin, woocommerce_admin_meta_boxes, accounting */
jQuery( function ( $ ) {

    var $prices = ajax_object.prices;

    var set_price_events = '';
    var adjust_price_events = '';

    $.each($prices, function (p) {
        set_price_events = set_price_events + 'variable' + p + ' ';
        adjust_price_events = adjust_price_events + 'variable' + p + '_increase '  + 'variable' + p + '_decrease ';
    });

    $( document.body ).on( adjust_price_events, function(event) {

        var action = event.type;
        var field = action.slice(0, -9);

        var value = window.prompt(woocommerce_admin_meta_boxes_variations.i18n_enter_a_value_fixed_or_percent).toString();

        if ( value != null ) {
            $(':input[name^="' + field + '"]').each(function () {
                var current_value = accounting.unformat($(this).val(), woocommerce_admin.mon_decimal_point),
                    new_value,
                    mod_value;

                if (value.indexOf('%') >= 0) {
                    mod_value = ( current_value / 100 ) * accounting.unformat(value.replace(/\%/, ''), woocommerce_admin.mon_decimal_point);
                } else {
                    mod_value = accounting.unformat(value, woocommerce_admin.mon_decimal_point);
                }

                if (action.indexOf('increase') !== -1) {
                    new_value = current_value + mod_value;
                } else {
                    new_value = current_value - mod_value;
                }

                $(this).val(accounting.formatNumber(
                    new_value,
                    woocommerce_admin_meta_boxes.currency_format_num_decimals,
                    woocommerce_admin_meta_boxes.currency_format_thousand_sep,
                    woocommerce_admin_meta_boxes.currency_format_decimal_sep
                ));

            });
            $(':input[name^="' + field + '"]').promise().done(function(){
                $( this ).trigger('change');
            });

        }

    } );

    $( document.body ).on( set_price_events, function(event) {

        var field = event.type;

        var value = window.prompt( woocommerce_admin_meta_boxes_variations.i18n_enter_a_value );

        if ( value != null ) {
            $(':input[name^="'+field+'"]').val(value).change();
        }

    } );



});
