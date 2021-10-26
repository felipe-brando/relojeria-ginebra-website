<?php
if (!class_exists('QLWAPP_Frontend')) {


    class QLWAPP_Frontend
    {

        protected static $instance;

        function add_js()
        {

            $frontend = include_once(QLWAPP_PLUGIN_DIR . 'assets/frontend/js/frontend.asset.php');


            wp_enqueue_style(QLWAPP_DOMAIN, plugins_url('/assets/frontend/css/frontend.css', QLWAPP_PLUGIN_FILE), null, QLWAPP_PLUGIN_VERSION, 'all');
            wp_enqueue_script(QLWAPP_DOMAIN, plugins_url('/assets/frontend/js/frontend.js',  QLWAPP_PLUGIN_FILE), $frontend['dependencies'], $frontend['version'], true);
        }

        function add_box()
        {

            global $qlwapp;

            if (is_file($file = apply_filters('qlwapp_box_template', QLWAPP_PLUGIN_DIR . 'template/box.php'))) {
                include_once(QLWAPP_PLUGIN_DIR . 'includes/models/Box.php');
                include_once(QLWAPP_PLUGIN_DIR . 'includes/models/Contact.php');
                include_once(QLWAPP_PLUGIN_DIR . 'includes/models/Display.php');
                include_once(QLWAPP_PLUGIN_DIR . 'includes/models/Button.php');

                $box_model = new QLWAPP_Box();
                $contact_model = new QLWAPP_Contact();
                $button_model = new QLWAPP_Button();
                $display_model = new QLWAPP_Display();
                $display_service = new QLWAPP_DisplayServices_Controller();

                $contacts = $contact_model->get_contacts_reorder();
                $display = $display_model->get();
                $button = $button_model->get();
                $box = $box_model->get();



                include_once $file;
            }
        }

        function add_frontend_css()
        {

            $scheme_model = new QLWAPP_Scheme();
            $scheme = $scheme_model->get();
?>
            <style>
                :root {
                    <?php

                    unset($scheme['_wp_http_referer']);
                    unset($scheme['_scheme_form_nonce']);
                    foreach ($scheme as $key => $value) {
                        if ($value != '') {
                            printf('--%s-scheme-%s:%s;', QLWAPP_DOMAIN, $key, $value);
                        }
                    }
                    ?>
                }

                <?php if ($scheme['font-family']) : ?>#qlwapp {
                    font-family: var(--qlwapp-scheme-font-family);
                }

                <?php endif; ?><?php if ($scheme['font-size']) : ?>#qlwapp {
                    font-size: calc(var(--qlwapp-scheme-font-size)*1px);
                }

                <?php endif; ?><?php if ($scheme['brand']) : ?>#qlwapp .qlwapp-toggle,
                #qlwapp .qlwapp-box .qlwapp-header,
                #qlwapp .qlwapp-box .qlwapp-user,
                #qlwapp .qlwapp-box .qlwapp-user:before {
                    background-color: var(--qlwapp-scheme-brand);
                }

                <?php endif; ?><?php if ($scheme['text']) : ?>#qlwapp .qlwapp-toggle,
                #qlwapp .qlwapp-toggle .qlwapp-icon,
                #qlwapp .qlwapp-toggle .qlwapp-text,
                #qlwapp .qlwapp-box .qlwapp-header,
                #qlwapp .qlwapp-box .qlwapp-user {
                    color: var(--qlwapp-scheme-text);
                }

                <?php endif; ?>
            </style>
        <?php
        }

        function box_display1($show)
        {
            global $wp_query;
            $display_model = new QLWAPP_Display();
            $display = $display_model->get();
            if (is_customize_preview()) {
                return true;
            }
            $display_service = new QLWAPP_DisplayServices_Controller();
            return $display_service->is_show_view($display);
        }

        function do_shortcode($atts, $content = null)
        {

            $button_model = new QLWAPP_Button();
            $button = $button_model->get();

            $atts = wp_parse_args($atts, $button);

            ob_start();
        ?>
            <div style="width: auto;" id="qlwapp" class="qlwapp-js-ready <?php printf("qlwapp-%s qlwapp-%s", esc_attr($atts['layout']), esc_attr($atts['rounded'] === 'yes' ? 'rounded' : 'square')); ?>">
                <a class="qlwapp-toggle" data-action="open" data-phone="<?php echo esc_attr($atts['phone']); ?>" data-message="<?php echo esc_html($atts['message']); ?>" href="#" target="_blank">
                    <?php if ($atts['icon']) : ?>
                        <i class="qlwapp-icon <?php echo esc_attr($atts['icon']); ?>"></i>
                    <?php endif; ?>
                    <i class="qlwapp-close" data-action="close">&times;</i>
                    <?php if ($atts['text']) : ?>
                        <span class="qlwapp-text"><?php echo esc_html($content); ?></span>
                    <?php endif; ?>
                </a>
            </div>
<?php
            return ob_get_clean();
        }

        function display()
        {
            include_once(QLWAPP_PLUGIN_DIR . 'includes/models/QLWAPP_Model.php');
            include_once(QLWAPP_PLUGIN_DIR . 'includes/models/Display.php');
            include_once(QLWAPP_PLUGIN_DIR . 'includes/controllers/Display_services.php');
            $display_model = new QLWAPP_Display();
            $display = $display_model->get();
            $display_service = new QLWAPP_DisplayServices_Controller();

            if ($display_service->is_show_view($display)) {

                do_action('qlwapp_load');
            }
        }

        function load()
        {
            add_action('wp_enqueue_scripts', array($this, 'add_js'));
            add_action('wp_head', array($this, 'add_frontend_css'), 200);
            add_action('wp_footer', array($this, 'add_box'));
        }

        function init()
        {
            add_action('template_redirect', array($this, 'display'));
            add_shortcode('whatsapp', array($this, 'do_shortcode'));
            add_action('qlwapp_load', array($this, 'load'));
        }

        public static function instance()
        {
            if (!isset(self::$instance)) {
                self::$instance = new self();
                self::$instance->init();
            }
            return self::$instance;
        }
    }

    QLWAPP_Frontend::instance();
}
