<?php

include_once(QLWAPP_PLUGIN_DIR . 'includes/models/QLWAPP_Model.php');

class QLWAPP_Button extends QLWAPP_Model
{

    protected $table = 'button';

    function get_args()
    {

        $args = array(
            'layout' => 'button',
            'box' => 'yes',
            'position' => 'bottom-right',
            'text' => esc_html__('How can I help you?', 'wp-whatsapp-chat'),
            'message' => sprintf(esc_html__('Hello! I\'m testing the %s plugin %s', 'wp-whatsapp-chat'), QLWAPP_PLUGIN_NAME, QLWAPP_LANDING_URL),
            'icon' => 'qlwapp-whatsapp-icon',
            'phone' => '12057948080',
            'developer' => 'no',
            'rounded' => 'yes',
            'timefrom' => '00:00',
            'timeto' => '00:00',
            'timedays' => [],
            'timezone' => qlwapp_get_current_timezone(),
            'timeout' => 'readonly'
        );
        return $args;
    }

    function sanitize($settings)
    {

        if (isset($settings['layout'])) {
            $settings['layout'] = sanitize_html_class($settings['layout']);
        }
        if (isset($settings['position'])) {
            $settings['position'] = sanitize_html_class($settings['position']);
        }
        if (isset($settings['text'])) {
            $settings['text'] = sanitize_text_field($settings['text']);
        }
        if (isset($settings['message'])) {
            $settings['message'] = sanitize_text_field($settings['message']);
        }
        //      if (isset($settings['contactstimeout'])) {
        //        $settings['box']['contactstimeout'] = sanitize_text_field($settings['box']['contactstimeout']);
        //      }
        if (isset($settings['icon'])) {
            $settings['icon'] = sanitize_html_class($settings['icon']);
        }
        if (isset($settings['phone'])) {
            $settings['phone'] = qlwapp_format_phone($settings['phone']);
        }

        return $settings;
    }

    function save($button_data = NULL)
    {
        return parent::save_data($this->table, $this->sanitize($button_data));
    }
}
