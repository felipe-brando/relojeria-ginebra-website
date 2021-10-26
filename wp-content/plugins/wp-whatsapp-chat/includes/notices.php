<?php

class QLWAPP_Notices {

  protected static $instance;

  public static function instance() {
    if (is_null(self::$instance)) {
      self::$instance = new self();
      self::$instance->init();
    }
    return self::$instance;
  }

  function init() {
    add_filter('plugin_action_links_' . plugin_basename(QLWAPP_PLUGIN_FILE), array($this, 'add_action_links'));
    add_action('admin_notices', array($this, 'add_notices'));
    add_action('wp_ajax_qlwapp_dismiss_notice', array($this, 'ajax_dismiss_notice'));
  }

  function ajax_dismiss_notice() {

    if (check_admin_referer('qlwapp_dismiss_notice', 'nonce') && isset($_REQUEST['notice_id'])) {

      $notice_id = sanitize_key($_REQUEST['notice_id']);

      update_user_meta(get_current_user_id(), $notice_id, true);

      wp_send_json($notice_id);
    }

    wp_die();
  }

  function add_notices() {

    if (!get_transient('qlwapp-first-rating') && !get_user_meta(get_current_user_id(), 'qlwapp-user-rating', true)) {
      ?>
      <div id="qlwapp-admin-rating" class="qlwapp-notice notice is-dismissible" data-notice_id="qlwapp-user-rating">
        <div class="notice-container" style="padding-top: 10px; padding-bottom: 10px; display: flex; justify-content: left; align-items: center;">
          <div class="notice-image">
            <img style="border-radius:50%;max-width: 90px;" src="<?php echo plugins_url('/assets/backend/img/logo.jpg', QLWAPP_PLUGIN_FILE); ?>" alt="<?php echo esc_html(QLWAPP_PLUGIN_NAME); ?>>">
          </div>
          <div class="notice-content" style="margin-left: 15px;">
            <p>
              <?php printf(esc_html__('Hello! Thank you for choosing the %s plugin!', 'wp-whatsapp-chat'), QLWAPP_PLUGIN_NAME); ?>
              <br/>
              <?php esc_html_e('Could you please give it a 5-star rating on WordPress? We know its a big favor, but we\'ve worked very much and very hard to release this great product. Your feedback will boost our motivation and help us promote and continue to improve this product.', 'wp-whatsapp-chat'); ?>
            </p>
            <a href="<?php echo esc_url(QLWAPP_REVIEW_URL); ?>" class="button-primary" target="_blank">
              <?php esc_html_e('Yes, of course!', 'wp-whatsapp-chat'); ?>
            </a>
            <a href="<?php echo esc_url(QLWAPP_SUPPORT_URL); ?>" class="button-secondary" target="_blank">
              <?php esc_html_e('Report a bug', 'wp-whatsapp-chat'); ?>
            </a>
          </div>				
        </div>
      </div>
      <script>
        (function ($) {
          $('.qlwapp-notice').on('click', '.notice-dismiss', function (e) {
            e.preventDefault();
            var notice_id = $(e.delegateTarget).data('notice_id');
            $.ajax({
              type: 'POST',
              url: ajaxurl,
              data: {
                notice_id: notice_id,
                action: 'qlwapp_dismiss_notice',
                nonce: '<?php echo wp_create_nonce('qlwapp_dismiss_notice'); ?>'
              },
              success: function (response) {
                console.log(response);
              },
            });
          });
        })(jQuery);
      </script>
      <?php
    }
  }

  public function add_action_links($links) {

    $links[] = '<a target="_blank" href="' . QLWAPP_PURCHASE_URL . '">' . esc_html__('Premium', 'wp-whatsapp-chat') . '</a>';
    $links[] = '<a href="' . admin_url('admin.php?page=' . sanitize_title(QLWAPP_PREFIX)) . '">' . esc_html__('Settings', 'wp-whatsapp-chat') . '</a>';

    return $links;
  }

}

QLWAPP_Notices::instance();
