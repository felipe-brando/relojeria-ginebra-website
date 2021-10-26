<?php

class QLWAPP {

  protected static $instance;

  function includes() {
    include_once(QLWAPP_PLUGIN_DIR . 'includes/notices.php');
    include_once(QLWAPP_PLUGIN_DIR . 'includes/settings.php');
    include_once(QLWAPP_PLUGIN_DIR . 'includes/frontend.php');
    include_once(QLWAPP_PLUGIN_DIR . 'includes/backend.php');
  }

  public static function is_min() {
    return;
    if (!defined('SCRIPT_DEBUG') || !SCRIPT_DEBUG) {
      return '.min';
    }
  }

  function add_premium_js() {
    if (!class_exists('QLWAPP_PRO')) {
      ?>
      <style>
        .qlwapp-premium-field {
          opacity: 0.5; 
          pointer-events: none;
        }
        .qlwapp-premium-field .description {
          display: block!important;
        }
      </style>
      <?php

    }
  }

  function init() {
    add_action('admin_footer', array($this, 'add_premium_js'));
    load_plugin_textdomain('wp-whatsapp-chat', false, dirname(plugin_basename(__FILE__)) . '/languages/');
    do_action('qlwapp_init');
  }

  public static function do_activation() {
    set_transient('qlwapp-first-rating', true, MONTH_IN_SECONDS);
  }

  public static function instance() {
    if (!isset(self::$instance)) {
      self::$instance = new self();
      self::$instance->includes();
      self::$instance->init();
    }
    return self::$instance;
  }

}

QLWAPP::instance();
