<div id="wpf-umf-upload-for-product">

    <div class="wpf-umf-ufp-image"><?php echo $product->get_image(); ?></div>

    <div class="wpf-umf-ufp-info">

        <h3><?php echo $product->get_title(); ?></h3>

        <?php if (!empty($variation)): ?>
            <p><?php echo html_entity_decode($variation); ?></p>
        <?php endif; ?>

        <p><?php _e('Items in cart:', 'woocommerce-uploads-before'); ?> <?php echo $cart['quantity']; ?></p>

    </div>

    <div class="clear"></div>

</div>