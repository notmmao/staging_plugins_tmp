<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="panel-wrap woocommerce activity-phone-call">
  <div id="log_data" class="panel">
	  <h2><?php _e('Activity #', 'wc_crm'); ?><?php echo $the_activity->ID; ?><?php _e(' details', 'wc_crm'); ?></h2>
	    <p class="email_date_time"><?php _e('Call placed on ', 'wc_crm'); ?><?php echo $date; ?> <?php _e('at', 'wc_crm'); ?> <?php echo $time; ?></p>
    <table class="view-activity">
        <tr>
          <th width="200"><strong><?php _e('Subject', 'wc_crm'); ?></strong></th>
          <td><?php echo stripslashes($the_activity->subject); ?></td>
        </tr>
        <tr>
          <th width="200"><strong><?php _e('Type', 'wc_crm'); ?></strong></th>
          <td><?php echo $the_activity->call_type; ?></td>
        </tr>
        <tr>
          <th width="200"><strong><?php _e('Purpose', 'wc_crm'); ?></strong></th>
          <td><?php echo $the_activity->call_purpose; ?></td>
        </tr>
        <tr>
          <th><strong><?php _e('Customer Name', 'wc_crm'); ?></strong></th>
          <td>
            <?php
            $recipients = $the_activity->get_recipients();
            if($recipients){
              $users = array();
              foreach ($recipients as $customer) {
                $name = trim($customer->first_name.' '.$customer->last_name);
                if( empty($name) ){
                  $name = $customer->email;
                }
                $users[] = '<a href="admin.php?page='.WC_CRM_TOKEN.'&c_id='.$customer->c_id.'">'.$name.'</a>';
              }
              echo implode(', ', $users);
            }
            ?>
           </td>
        </tr>
        <tr>
            <th width="100"><strong><?php _e('Related To', 'wc_crm'); ?></strong></th>
            <td>
              <?php echo ucwords($the_activity->related_to); ?>
              <?php $number_order_product = str_replace('#', '', $the_activity->number_order_product); ?>
              <a href="post.php?post=<?php echo $number_order_product; ?>&action=edit"> <?php echo $the_activity->number_order_product; ?></a>
            </td>
        </tr>
        <tr>
            <th width="100"><strong><?php _e('Call Duration', 'wc_crm'); ?></strong></th>
            <td><?php echo wc_crm_convertToHoursMins($the_activity->call_duration); ?></td>
        </tr>
        <tr>
        <th><strong><?php _e('Call Result', 'wc_crm'); ?></strong></th>
        <td><?php echo stripslashes($the_activity->message); ?></td>
      </tr>
    </table>

  </div>
</div>