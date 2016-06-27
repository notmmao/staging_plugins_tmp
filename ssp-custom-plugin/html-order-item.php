<tr class="item <?php echo apply_filters( 'woocommerce_admin_html_order_item_class', ( ! empty( $class ) ? $class : '' ), $item ); ?>" data-order_item_id="<?php echo $item_id; ?>">
    <td class="check-column"><input type="checkbox" data-selected="0" class="item-selector belongs-to-order-<?php echo $post->ID?> belongs-to-item-<?php echo $item_id ?>" data-item="<?php echo $item_id; ?>" data-order="<?php echo ($post->ID + 100000); ?>"/></td>
    <td class="name">
        <?php echo ( $_product && $_product->get_sku() ) ? esc_html( $_product->get_sku() ) . ' &ndash; ' : ''; ?>

        <?php if ( $_product ) : ?>
            <a target="_blank" href="<?php echo esc_url( admin_url( 'post.php?post=' . absint( $_product->id ) . '&action=edit' ) ); ?>">
                <?php echo esc_html( $item['name'] ); ?>
            </a>
        <?php else : ?>
            <?php echo esc_html( $item['name'] ); ?>
        <?php endif; ?>
        <br>
        <span class="approval-link"><a href="<?php echo PROOF_DOMAIN_NAME; ?>/approval/<?php echo $item_id ?>" target="_blank">Approval page</a></span>&nbsp;&nbsp;&nbsp;<span class="approval-link artwork-issues-link"><a>Issues</a></span>
    </td>

    <?php do_action( 'woocommerce_admin_order_item_values', $_product, $item, absint( $item_id ) ); ?>

    <td class="quantity" width="1%">
        <div class="view">
            <?php
                echo ( isset( $item['qty'] ) ) ? esc_html( $item['qty'] ) : '';

                if ( $refunded_qty = $order->get_qty_refunded_for_item( $item_id ) ) {
                    echo '<small class="refunded">-' . $refunded_qty . '</small>';
                }
            ?>
        </div>
    </td>

    <td class="line_cost" width="1%">
        <div class="view">
            <?php
                if ( isset( $item['line_total'] ) ) {
                    if ( isset( $item['line_subtotal'] ) && $item['line_subtotal'] != $item['line_total'] ) {
                        echo '<del>' . wc_price( $item['line_subtotal'] ) . '</del> ';
                    }

                    echo wc_price( $item['line_total'] );
                }

                if ( $refunded = $order->get_total_refunded_for_item( $item_id ) ) {
                    echo '<small class="refunded">-' . wc_price( $refunded ) . '</small>';
                }
            ?>
        </div>
    
    </td>

    <td class="wc-order-edit-line-item">
    </td>
</tr>

<tr>
<td></td>
<td colspan="5">    
<div class="container-fluid">
<?php

if(!isset($filesGroupedByItem[$item_id])) {
    $filesGroupedByItem[$item_id][0] = ssp_empty_file($proofOrderId, $item_id, 'front');
    $filesGroupedByItem[$item_id][1] = ssp_empty_file($proofOrderId, $item_id, 'back');
}


foreach ($filesGroupedByItem[$item_id] as $proofFile) {
// BEGIN FILE OUTPUT /////////////////////////////////////////////////////////////
?>
    <div class="row">
        <div class="col-xs-6" style="padding-left: 0;">
            <small>
                <?php if(!empty($proofFile['name'])): ?>
                <a class="proof-file-name" href="<?php echo PROOF_DOMAIN_NAME; ?>/file/<?php echo $proofFile['id'] ?>"><?php echo $proofFile['name'] ?></a>
                &nbsp;<i class="proof-file-size"><?php echo ssp_format_file_size($proofFile['size']); ?></i>
                <?php else: ?>
                    <a class="proof-file-name">no file</a>
                    &nbsp;<i class="proof-file-size"></i>  
                <?php endif; ?>
            </small>
        </div>
        <div class="col-xs-1">
            <?php
            switch ($proofFile['status']) {
                case 'missing': {
                    echo "<span data-side='".$proofFile['side']."' data-item-id='" . $proofFile['item_id'] . "' class='label belongs-to-item-". $proofFile['item_id'] . " {$proofFile['side']} file-status missing belongs-to-order-" . $post->ID . "'></span>";
                } break;
                case 'approved': {
                    echo "<span data-side='".$proofFile['side']."' data-item-id='" . $proofFile['item_id'] . "' data-file-id='" . $proofFile['id'] . "' class='{$proofFile['side']} label belongs-to-item-". $proofFile['item_id'] . " file-status belongs-to-order-" . $post->ID . " label-success'>approved</span>";
                } break;
                case 'not approved': {
                    echo "<span data-side='".$proofFile['side']."' data-item-id='" . $proofFile['item_id'] . "' data-file-id='" . $proofFile['id'] . "' class='{$proofFile['side']} label belongs-to-item-". $proofFile['item_id'] . " file-status belongs-to-order-" . $post->ID . " label-danger'>not approved</span>";
                } break;
                case 'approval': {
                    echo "<span data-side='".$proofFile['side']."' data-item-id='" . $proofFile['item_id'] . "' data-file-id='" . $proofFile['id'] . "' class='{$proofFile['side']} label belongs-to-item-". $proofFile['item_id'] . " file-status belongs-to-order-" . $post->ID . " label-warning'>approval</span>";
                } break;
                case 'ready': {
                    echo "<span data-side='".$proofFile['side']."' data-item-id='" . $proofFile['item_id'] . "' data-file-id='" . $proofFile['id'] . "' class='{$proofFile['side']} label belongs-to-item-". $proofFile['item_id'] . " file-status belongs-to-order-" . $post->ID . " label-info'>ready</span>";
                } break;
                case 'missing': {
                    echo "<span data-side='".$proofFile['side']."' data-item-id='" . $proofFile['item_id'] . "' data-file-id='" . $proofFile['id'] . "' class='{$proofFile['side']} label belongs-to-item-". $proofFile['item_id'] . " file-status belongs-to-order-" . $post->ID . " label-default'>missing</span>";
                } break;
                case 'issues': {
                    echo "<span data-side='".$proofFile['side']."' data-item-id='" . $proofFile['item_id'] . "' data-file-id='" . $proofFile['id'] . "' class='{$proofFile['side']} label belongs-to-item-". $proofFile['item_id'] . " file-status belongs-to-order-" . $post->ID . " label-default'>issues</span>";
                } break;
                default: {
                    echo "<span data-side='".$proofFile['side']."' data-item-id='" . $proofFile['item_id'] . "' data-file-id='" . $proofFile['id'] . "' class='{$proofFile['side']} label belongs-to-item-". $proofFile['item_id'] . " file-status belongs-to-order-" . $post->ID . " label-default'>" . $proofFile['status'] . "</span>";
                } break;
            }
        ?>
        </div>
        <div class="col-xs-1 text-right">
            <button data-action="<?php echo PROOF_DOMAIN_NAME; ?>/api/reset-file-status/<?php echo $proofFile['id'] ?>" class="btn btn-redirect btn-link btn-small reset-button">reset</button><span class="allow-uploads loader-box loader-box-reset"></span>
        </div>
        <div class="col-xs-1 text-right">
            <div class="fileupload" style="padding: 0; margin: 0;" data-action="<?php echo PROOF_DOMAIN_NAME; ?>/upload/<?php echo ($post->ID + 100000) ?>/">
                <div class="pseudo-input MAX_FILE_SIZE" data-value="524288000" /></div>
                <span class="btn btn-link btn-small fileinput-button">
                    <span>upload...</span>
                    <input type="file" class="file">
                </span>
                <span class="upload-messages" style="font-size:0.8em; position: absolute; top: 4px;"></span>
                <i class="icon-minus-sign abort-icon" style="display:none; cursor: pointer;"></i>
                <div class="pseudo-input side" data-value="<?php echo $proofFile['side'] ?>" /></div>
                <div class="pseudo-input order" data-value="<?php echo ($post->ID + 100000) ?>"></div>
                <div class="pseudo-input item" data-value="<?php echo $proofFile['item_id'] ?>" /></div>
                <div class="pseudo-input admin" data-value="1" /></div>
            </div>
        </div>
        <div class="col-xs-1 text-right">
            <button data-action="<?php echo PROOF_DOMAIN_NAME; ?>/api/remove/<?php echo $proofFile['id'] ?>" type="submit" style="margin: 0px; font-size:1.3em;" class="btn-link btn-redirect file-remove btn-redirect"><strong>&times;</strong></button><span class="allow-uploads loader-box loader-box-reset"></span>
        </div>
    </div>
    <?php if ($proofFile['comment']): ?>
    <div class="row comment-row" >
        <div class="col-xs-12" style="padding:0;">
            <div class="comment-box"><?php echo $proofFile['comment'] ?></div>
        </div>  
    </div>
    <?php endif; ?>
<?php 
// END FILE OUTPUT ///////////////////////////////////////////////////////////////
} 
?>
</div>

<?php do_action( 'woocommerce_before_order_itemmeta', $item_id, $item, $_product ) ?>

<div class="view row">
    <div class="col-xs-6">
    <?php
        global $wpdb;

        if ( $metadata = $order->has_meta( $item_id ) ) {
            echo '<table cellspacing="0" class="display_meta">';
            foreach ( $metadata as $meta ) {

                // Skip hidden core fields
                if ( in_array( $meta['meta_key'], apply_filters( 'woocommerce_hidden_order_itemmeta', array(
                    '_qty',
                    '_tax_class',
                    '_product_id',
                    '_variation_id',
                    '_line_subtotal',
                    '_line_subtotal_tax',
                    '_line_total',
                    '_line_tax',
                ) ) ) ) {
                    continue;
                }

                // Skip serialised meta
                if ( is_serialized( $meta['meta_value'] ) ) {
                    continue;
                }

                // Get attribute data
                if ( taxonomy_exists( $meta['meta_key'] ) ) {
                    $term           = get_term_by( 'slug', $meta['meta_value'], $meta['meta_key'] );
                    $attribute_name = str_replace( 'pa_', '', wc_clean( $meta['meta_key'] ) );
                    $attribute      = $wpdb->get_var(
                        $wpdb->prepare( "
                                SELECT attribute_label
                                FROM {$wpdb->prefix}woocommerce_attribute_taxonomies
                                WHERE attribute_name = %s;
                            ",
                            $attribute_name
                        )
                    );

                    $meta['meta_key']   = ( ! is_wp_error( $attribute ) && $attribute ) ? $attribute : $attribute_name;
                    $meta['meta_value'] = ( isset( $term->name ) ) ? $term->name : $meta['meta_value'];
                }

                echo '<tr><th>' . wp_kses_post( urldecode( $meta['meta_key'] ) ) . ':</th><td>' . wp_kses_post( wpautop( urldecode( $meta['meta_value'] ) ) ) . '</td></tr>';
            }
            echo '</table>';
        }
    ?>
</div>
</div>

</td>
</tr>
