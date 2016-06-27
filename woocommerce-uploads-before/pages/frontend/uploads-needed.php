<?php if (is_array($uploads_needed)): ?>

    <input type="hidden" name="wpf_umf_uploads_needed" value="1" />

    <div id="wpf-umf-before-uploads-needed">

      <?php _e('The following products need uploads:', $this->plugin_id); ?>

      <ul>
      <?php  foreach ($uploads_needed AS $id => $product): ?>

          <li><?php echo $product['name']; ?></li>

      <?php endforeach; ?>
      </ul>

      <?php _e('Please upload your files before proceeding to checkout'); ?>

    </div>

<?php
endif;
?>