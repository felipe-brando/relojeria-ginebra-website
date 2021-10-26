<?php

include_once(QLWAPP_PLUGIN_DIR . 'includes/models/QLWAPP_Model.php');

class QLWAPP_Box extends QLWAPP_Model
{

  protected $table = 'box';

  function get_args()
  {
    $args = array(
      'enable' => 'yes',
      'auto_open' => 'no',
      'auto_delay_open' => 1000,
      'header' => '<p><span style="font-size: 12px;line-height: 34px;vertical-align: bottom;letter-spacing: -0.2px">Powered by</span> <a href="' . QLWAPP_LANDING_URL . '" target="_blank" rel="noopener" style="font-size: 24px;line-height: 34px;font-family: Calibri;font-weight: bold;text-decoration: none;color: white">WhatsApp Chat</a></p>',
      'footer' => '<p style="text-align: start;">WhatsApp Chat is free, download and try it now <a target="_blank" href="' . QLWAPP_LANDING_URL . '">here!</a></p>',
      'response' => esc_html__('Write a response', 'wp-whatsapp-chat')
      //              ,'contactstimeout' => 'no' 
    );
    return $args;
  }

  function sanitize($settings)
  {

    if (isset($settings['header'])) {
      $settings['header'] = wp_kses_post($settings['header']);
    }
    if (isset($settings['auto_open'])) {
      $settings['auto_open'] = wp_kses_post($settings['auto_open']);
    }
    if (isset($settings['auto_delay_open'])) {
      $settings['auto_delay_open'] = wp_kses_post($settings['auto_delay_open']);
    }
    if (isset($settings['footer'])) {
      $settings['footer'] = wp_kses_post($settings['footer']);
    }
    return $settings;
  }

  function save($box_data = NULL)
  {
    return parent::save_data($this->table, $this->sanitize($box_data));
  }
}
