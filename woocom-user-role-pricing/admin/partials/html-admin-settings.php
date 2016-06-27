<?php
/**
 * Admin View: Settings
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>

<div class="wrap woocommerce">
	<form method="<?php echo esc_attr( apply_filters( 'woocom_urp_settings_form_method_tab_' . $current_tab, 'post' ) ); ?>" id="mainform" action="" enctype="multipart/form-data">
		<div class="icon32 icon32-woocommerce-settings" id="icon-woocommerce"><br /></div><h2 class="nav-tab-wrapper woo-nav-tab-wrapper">
			<?php
				foreach ( $tabs as $name => $label ) {
					echo '<a href="' . admin_url( 'admin.php?page=user_role_pricing&tab=' . $name ) . '" class="nav-tab ' . ( $current_tab == $name ? 'nav-tab-active' : '' ) . '">' . $label . '</a>';
				}

			?>

			</h2>

		<?php
			switch($current_tab) {
				case 'general':
					include('html-admin-settings-general.php');
					break;
				case 'role':
					include('html-admin-settings-role.php');
					break;
				case 'prices':
					include('html-admin-settings-prices.php');
					break;
				case 'license':
					include('html-admin-license-form.php');
					break;
			}

		?>

	</form>
	<p class="dashicons-before dashicons-media-text"><a href="http://stephensherrardplugins.com/docs/woocommerce-user-role-pricing/" target="_blank"><?php _e('Documentation', 'woocom-urp'); ?></a></p>
</div>
