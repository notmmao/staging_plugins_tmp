<?php
/**
 * Post Types Admin
 *
 * @author   Actuality Extensions
 * @category Admin
 * @package  WC_CRM_Admin/Admin
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WC_CRM_Admin_Post_Types' ) ) :

/**
 * WC_CRM_Admin_Post_Types Class
 *
 * Handles the edit posts views and some functionality on the edit post screen for WC post types.
 */
class WC_CRM_Admin_Post_Types {

	/**
     * Hook into ajax events
     */
    public function __construct() {

      add_action( 'admin_init', array( $this, 'register_post_types') );
      add_action( 'add_meta_boxes', array( $this, 'remove_meta_boxes' ), 10 );
      add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 30 );
      add_action( 'save_post', 'WC_Crm_Accounts::save_meta_boxes', 1, 2 );
      add_action( 'wc_crm_process_accounts_meta', 'WC_Crm_Accounts::save', 10, 2 );
      add_filter( 'post_row_actions','WC_Crm_Accounts::remove_quick_edit',10,1);

      add_filter( 'post_updated_messages', array( $this, 'post_updated_messages' ), 10, 1 );

      add_filter( 'comments_clauses', array( $this, 'exclude_comments' ), 10, 1 );
      add_action( 'comment_feed_join', array( $this, 'exclude_comments_from_feed_join' ) );
      add_action( 'comment_feed_where', array( $this, 'exclude_comments_from_feed_where' ) );

      add_action( 'manage_wc_crm_accounts_posts_columns' , array($this, 'add_wc_crm_accounts_column') );
      add_action( 'manage_wc_crm_accounts_posts_custom_column' , array($this, 'wc_crm_accounts_custom_columns'), 10, 2 );
    }

    /**
   * Register core post types.
   */
  public static function register_post_types() {
    if ( post_type_exists('wc_crm_accounts') ) {
      return;
    }

    $args = array(
          'labels'              => array(
              'name'               => __( 'Accounts', 'wc_crm' ),
              'singular_name'      => __( 'Account', 'wc_crm' ),
              'add_new'            => __( 'Add Account', 'wc_crm' ),
              'add_new_item'       => __( 'Add New Account', 'wc_crm' ),
              'edit'               => __( 'Edit', 'wc_crm' ),
              'edit_item'          => __( 'Edit Account', 'wc_crm' ),
              'new_item'           => __( 'New Account', 'wc_crm' ),
              'view'               => __( 'View Account', 'wc_crm' ),
              'view_item'          => __( 'View Account', 'wc_crm' ),
              'search_items'       => __( 'Search Accounts', 'wc_crm' ),
              'not_found'          => __( 'No Accounts found', 'wc_crm' ),
              'not_found_in_trash' => __( 'No Accounts found in trash', 'wc_crm' ),
              'parent'             => __( 'Parent Account', 'wc_crm' ),
              'menu_name'          => _x( 'Accounts', 'Admin menu name', 'wc_crm' )
            ),
          'description'         => __( 'This is where Accounts are stored.', 'wc_crm' ),
          'public'              => false,
          'show_ui'             => true,
          'capability_type'     => 'post',
          'map_meta_cap'        => true,
          'publicly_queryable'  => false,
          'exclude_from_search' => true,
          'show_in_menu'        => false,
          'hierarchical'        => false,
          'show_in_nav_menus'   => false,
          'rewrite'             => false,
          'query_var'           => false,
          'supports'            => array( 'custom-fields'),
          'has_archive'         => false,
        );
    register_post_type( 'wc_crm_accounts', $args);
  }

  function post_updated_messages($messages)
  {
    global $post_type;
    switch ($post_type) {
      case 'wc_crm_accounts':
        $messages['wc_crm_accounts'] = array(
           0 => '', // Unused. Messages start at index 1.
           1 => __('Account updated.', 'wc_crm'),
           2 => __('Custom field updated.', 'wc_crm'),
           3 => __('Custom field deleted.', 'wc_crm'),
           4 => __('Account updated.', 'wc_crm'),
          /* translators: %s: date and time of the revision */
           5 => isset($_GET['revision']) ? sprintf( __('Account restored to revision from %s', 'wc_crm'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
           6 => __('Account published.', 'wc_crm'),
           7 => __('Account saved.', 'wc_crm'),
           8 => __('Account submitted.', 'wc_crm'),
        );
        break;
    }
    return $messages;
  }

  /**
   * Remove bloat
   */
  public function remove_meta_boxes() {
      remove_meta_box( 'commentsdiv', 'wc_crm_accounts', 'normal' );
      remove_meta_box( 'woothemes-settings', 'wc_crm_accounts', 'normal' );
      remove_meta_box( 'commentstatusdiv', 'wc_crm_accounts', 'normal' );
      remove_meta_box( 'slugdiv', 'wc_crm_accounts', 'normal' );
  }

  /**
   * Add WC_CRM Accounts Meta boxes
   */
  public function add_meta_boxes() {
  add_meta_box( 'wc_crm_account_data', __( 'Account Data', 'wc_crm' ), 'WC_Crm_Accounts::output', 'wc_crm_accounts', 'normal', 'high' );
  add_meta_box( 'wc_crm_account_customers', __( 'Customers', 'wc_crm' ), 'WC_Crm_Accounts::output_customers', 'wc_crm_accounts', 'normal', 'high');
  add_meta_box( 'wc_crm-account-actions', __( 'Account Actions', 'wc_crm' ), 'WC_Crm_Accounts::output_actions', 'wc_crm_accounts', 'side', 'high' );
  add_meta_box( 'woocommerce-order-notes', __( 'Account Notes', 'wc_crm' ), 'WC_Crm_Accounts::output_notes', 'wc_crm_accounts', 'side', 'default' );
  }

  public static function exclude_comments( $clauses ) {
    global $wpdb, $typenow;

    if ( is_admin() && in_array( $typenow, wc_get_order_types() ) && current_user_can( 'manage_woocommerce' ) ) {
      return $clauses; // Don't hide when viewing orders in admin
    }

    if ( ! $clauses['join'] ) {
      $clauses['join'] = '';
    }

    if ( ! strstr( $clauses['join'], "JOIN $wpdb->posts" ) ) {
      $clauses['join'] .= " LEFT JOIN $wpdb->posts ON comment_post_ID = $wpdb->posts.ID ";
    }

    if ( $clauses['where'] ) {
      $clauses['where'] .= ' AND ';
    }

    $clauses['where'] .= " $wpdb->posts.post_type NOT IN ('" . implode( "','", wc_crm_get_exclude_comments_post_types() ) . "') ";

    return $clauses;
  }

  /**
   * Exclude order comments from queries and RSS
   * @param  string $join
   * @return string
   */
  public static function exclude_account_from_feed_join( $join ) {
    global $wpdb;

    if ( ! strstr( $join, $wpdb->posts ) ) {
      $join = " LEFT JOIN $wpdb->posts ON $wpdb->comments.comment_post_ID = $wpdb->posts.ID ";
    }

    return $join;
  }

  /**
   * Exclude order comments from queries and RSS
   * @param  string $where
   * @return string
   */
  public static function exclude_account_from_feed_where( $where ) {
    global $wpdb;

    if ( $where ) {
      $where .= ' AND ';
    }

    $where .= " $wpdb->posts.post_type NOT IN ('" . implode( "','", wc_crm_get_exclude_comments_post_types() ) . "') ";

    return $where;
  }

  public function add_wc_crm_accounts_column( $columns ) {
    unset($columns['date']);
    $columns['title']     = __('Account Name', 'wc_crm');
    $columns['owner']     = __('Account Owner', 'wc_crm');
    $columns['type']      = __('Account Type', 'wc_crm');
    $columns['ownership'] = __('Ownership', 'wc_crm');
    $columns['industry']  = __('Industry', 'wc_crm');
    
      return $columns;

  }

  public function wc_crm_accounts_custom_columns( $column, $post_id  )
  {
    switch ( $column ) {
    case 'owner' :
        $account_owner = get_post_meta($post_id,'_account_owner', true); ;
        $user_meta     = get_user_meta( $account_owner );
        if($user_meta){
          $user_meta = (object)$user_meta;
          ?>
          <a href="<?php echo admin_url('profile.php'); ?>" target="_blank">
            <?php echo $user_meta->first_name[0]; ?> <?php echo $user_meta->last_name[0]; ?>
          </a>
          <?php
        }else{
          echo '-';
        }
      break;

    case 'type' :
      $options = wc_crm_get_account_types();
      $type = get_post_meta($post_id,'_account_type', true);
      echo isset($options[$type]) ? $options[$type] : '';
      break;

    case 'ownership' :
      $options   = wc_crm_get_account_ownerships();
      $ownership = get_post_meta($post_id,'_ownership', true);
      echo isset($options[$ownership]) ? $options[$ownership] : '';
      break;

    case 'industry' :
        $all_i = wc_crm_get_industries();
        $ind   = get_post_meta($post_id,'_industry', true);
        echo isset($all_i[$ind]) ? $all_i[$ind] : '';
        break;
      }
  }

}

new WC_CRM_Admin_Post_Types();

endif;