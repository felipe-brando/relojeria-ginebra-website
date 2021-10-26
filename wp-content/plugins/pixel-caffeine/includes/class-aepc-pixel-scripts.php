<?php
/**
 * Manager of the JS handlers for the Pixel events
 *
 * @package Pixel Caffeine
 */

use PixelCaffeine\FB\User_Data_Factory;
use PixelCaffeine\ServerSide\Pixel_Event;
use PixelCaffeine\ServerSide\Server_Side_Tracking;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Manager of the JS handlers for the Pixel events
 *
 * @class AEPC_Pixel_Scripts
 */
class AEPC_Pixel_Scripts {

	/**
	 * Hooks to include scripts.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'sync_events_status_in_addon' ) );

		add_action( 'template_redirect', array( __CLASS__, 'track_standard_events' ), 5 );
		add_action( 'template_redirect', array( __CLASS__, 'track_advanced_events' ), 5 );
		add_action( 'template_redirect', array( __CLASS__, 'track_custom_fields' ), 5 );
		add_action( 'template_redirect', array( __CLASS__, 'track_conversions_events' ), 5 );

		// The following are hook in wp_footer, to give ability to function helper to enqueue the code.
		add_action( 'wp_footer', array( __CLASS__, 'enqueue_scripts' ) );

		if ( 'head' === get_option( 'aepc_pixel_position', 'head' ) ) {
			add_action( 'wp_head', array( __CLASS__, 'pixel_init' ), 99 );
		} else {
			add_action( 'wp_footer', array( __CLASS__, 'pixel_init' ), 1 );
		}

		add_action( 'wp_footer', array( __CLASS__, 'add_pixel_init_noscript' ), 1 );

		/**
		 * AMP Integration
		 */
		add_action( 'amp_post_template_footer', array( __CLASS__, 'track_on_amp_pages' ) );

		// Init the Server Side tracking module (it will be init only if the option is enabled).
		Server_Side_Tracking::init();
	}

	/**
	 * Arguments to initialize the pixel
	 *
	 * @return array
	 */
	public static function pixel_init_arguments() {
		// All arguments to pass to js.
		$args = array(
			'pixel_id'               => PixelCaffeine()->get_pixel_id(),
			'user'                   => array(),
			'enable_advanced_events' => AEPC_Track::is_advanced_events_active() ? 'yes' : 'no',
			'fire_delay'             => intval( AEPC_Track::detect_delay_firing( 'PageView' ) ),
			'can_use_sku'            => AEPC_Track::can_use_sku() ? 'yes' : 'no',
		);

		// eCommerce parameters.
		if ( AEPC_Addons_Support::are_detected_addons() ) {
			$args = array_merge(
				$args,
				array(
					'enable_viewcontent'      => self::is_event_enabled( 'viewcontent' ) ? 'yes' : 'no',
					'enable_addtocart'        => self::is_event_enabled( 'addtocart' ) ? 'yes' : 'no',
					'enable_addtowishlist'    => self::is_event_enabled( 'addtowishlist' ) ? 'yes' : 'no',
					'enable_initiatecheckout' => self::is_event_enabled( 'initiatecheckout' ) ? 'yes' : 'no',
					'enable_addpaymentinfo'   => self::is_event_enabled( 'addpaymentinfo' ) ? 'yes' : 'no',
					'enable_purchase'         => self::is_event_enabled( 'purchase' ) ? 'yes' : 'no',
					'allowed_params'          => array(
						'AddToCart'     => AEPC_Track::get_allowed_standard_params( 'AddToCart' ),
						'AddToWishlist' => AEPC_Track::get_allowed_standard_params( 'AddToWishlist' ),
					),
				)
			);
		}

		// Logged in user information.
		if ( 'yes' === get_option( 'aepc_enable_advanced_matching', 'yes' ) && is_user_logged_in() ) {
			$args['user'] = User_Data_Factory::create_from_session()->normalize();
		}

		foreach ( (array) $args as $key => $value ) {
			if ( ! is_scalar( $value ) ) {
				continue;
			}

			$args[ $key ] = html_entity_decode( (string) $value, ENT_QUOTES, 'UTF-8' );
		}

		// Force user to be object in order to avoid the JS error "parameter 'user_data' with invalid value of '[]'".
		$args['user'] = (object) $args['user'];

		return $args;
	}

	/**
	 * Pass extra parameters to JS in order to add them in all pixels
	 *
	 * @return array
	 */
	public static function pixel_advanced_parameters() {
		$params = array();

		if ( 'yes' === get_option( 'aepc_enable_utm_tags', 'yes' ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification
			foreach ( $_GET as $key => $value ) {
				if ( strpos( $key, 'utm_' ) !== false ) {
					$params[ $key ] = urldecode( $value );
				}
			}
		}

		return apply_filters( 'aepc_pixel_extra_args', $params );
	}

	/**
	 * Get the attributes for the script tag
	 *
	 * @return string
	 */
	protected static function script_attributes() {
		// string $tag => string $value.
		$attributes = apply_filters( 'aepc_init_script_attributes', array() );

		return array_reduce(
			array_keys( $attributes ),
			function( $attrs, $attr ) use ( $attributes ) {
				return $attrs . ' ' . sprintf( '%s="%s"', $attr, esc_attr( $attributes[ $attr ] ) );
			},
			''
		);
	}

	/**
	 * Add the Facebook Pixel snippet
	 *
	 * @return void
	 */
	public static function pixel_init() {
		// Split in order to make fbq delayable through WP Rocket, if used.
		?>
		<!-- Facebook Pixel Code -->
		<script<?php echo wp_kses_post( self::script_attributes() ); ?>>
			var aepc_pixel = <?php echo wp_json_encode( self::pixel_init_arguments() ) ?: '{}'; ?>,
				aepc_pixel_args = <?php echo wp_json_encode( self::pixel_advanced_parameters() ) ?: '{}'; ?>,
				aepc_extend_args = function( args ) {
					if ( typeof args === 'undefined' ) {
						args = {};
					}

					for(var key in aepc_pixel_args)
						args[key] = aepc_pixel_args[key];

					return args;
				};

			// Extend args
			if ( 'yes' === aepc_pixel.enable_advanced_events ) {
				aepc_pixel_args.userAgent = navigator.userAgent;
				aepc_pixel_args.language = navigator.language;

				if ( document.referrer.indexOf( document.domain ) < 0 ) {
					aepc_pixel_args.referrer = document.referrer;
				}
			}

			<?php if ( PixelCaffeine()->is_debug_mode() ) : ?>
			var fbq_calls = [],
				fbq = function() {
					console.log( 'fbq: ', arguments[0], arguments[1], arguments[2] );
					fbq_calls.push( arguments );
				};
			<?php elseif ( AEPC_Track::can_init_pixel() ) : ?>
			!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
				n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
				n.push=n;n.loaded=!0;n.version='2.0';n.agent='dvpixelcaffeinewordpress';n.queue=[];t=b.createElement(e);t.async=!0;
				t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
				document,'script','https://connect.facebook.net/en_US/fbevents.js');
			<?php endif; ?>

			<?php if ( AEPC_Track::can_init_pixel() ) : ?>
			fbq('init', aepc_pixel.pixel_id, aepc_pixel.user);

				<?php /* Trigger the event when delay is passed and where there are all fbq calls that need to wait. */ ?>
			setTimeout( function() {
				fbq('track', "PageView", aepc_pixel_args);
			}, aepc_pixel.fire_delay * 1000 );
			<?php endif; ?>
		</script>
		<!-- End Facebook Pixel Code -->
		<?php
	}

	/**
	 * Add the noscript tag in the <body>
	 *
	 * @return void
	 */
	public static function add_pixel_init_noscript() {
		if ( ! PixelCaffeine()->is_debug_mode() && AEPC_Track::can_init_pixel() ) {
			?>
			<noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=<?php echo esc_attr( PixelCaffeine()->get_pixel_id() ); ?>&amp;ev=PageView&amp;noscript=1"/></noscript>
			<?php
		}
	}

	/**
	 * Include the necessary scripts file
	 *
	 * @return void
	 */
	public static function enqueue_scripts() {
		wp_register_script( 'aepc-pixel-events', PixelCaffeine()->build_url( 'frontend.js' ), array( 'jquery' ), PixelCaffeine()->version, true );
		wp_enqueue_script( 'aepc-pixel-events' );

		$standard_events = AEPC_Track::get_standard_events();
		$custom_events   = AEPC_Track::get_custom_events();

		foreach ( array( &$standard_events, &$custom_events ) as &$events ) {
			foreach ( $events as $event => &$pixels ) {
				$pixels = array_map(
					function( Pixel_Event $pixel ) {
						$pixel->set_as_tracked( Pixel_Event::TYPE_BROWSER );
						return $pixel->to_array();
					},
					$pixels
				);
			}
		}

		wp_localize_script(
			'aepc-pixel-events',
			'aepc_pixel_events',
			array_filter(
				array(
					'standard_events' => $standard_events,
					'custom_events'   => $custom_events,
					'css_events'      => self::track_css_events(),
					'link_clicks'     => self::track_link_click_events(),
					'js_events'       => self::track_js_event_events(),
					'ss_in_ajax'      => AEPC_Track::is_server_side_tracking_enabled() && Server_Side_Tracking::must_track_in_ajax(),
					'api'             => array(
						'root'  => esc_url_raw( rest_url() ),
						'nonce' => wp_create_nonce( 'wp_rest' ),
					),
				)
			)
		);
	}

	/**
	 * Check if the event is enabled by option
	 *
	 * @param string $event The event name to check.
	 * @return bool
	 */
	protected static function is_event_enabled( $event ) {
		$callback = 'AEPC_Track::is_' . strtolower( $event ) . '_active';
		return is_callable( $callback ) ? call_user_func( $callback ) : false;
	}

	/**
	 * Enable the vents in the add-on instance if the event is enabled in the settings
	 *
	 * @return void
	 */
	public static function sync_events_status_in_addon() {
		foreach ( AEPC_Addons_Support::get_detected_addons() as $addon ) {
			foreach ( $addon->get_event_supported() as $event_name ) {
				$addon->set_event_status( $event_name, self::is_event_enabled( $event_name ) );
			}
		}
	}

	/**
	 * Standard Events trackable at load page
	 *
	 * @return void
	 */
	public static function track_standard_events() {

		if ( self::is_event_enabled( 'Search' ) && is_search() ) {
			AEPC_Track::track( 'Search', array( 'search_string' => get_search_query( false ) ) );
		}

		// Fire the dynamic ads events caught by ecommerce addons supported.
		foreach ( AEPC_Addons_Support::get_detected_addons() as $addon ) {
			foreach ( $addon->get_event_supported() as $event_name ) {
				$addon->send_event( $event_name );
			}
		}
	}

	/**
	 * Track advanced events
	 *
	 * @return void
	 */
	public static function track_advanced_events() {

		// Exit if the general option is disabled.
		if ( ! AEPC_Track::is_advanced_events_active() ) {
			return;
		}

		// Start!
		$params     = array();
		$enable_tax = AEPC_Track::is_taxonomy_events_active();

		// Track login status.
		$params['login_status'] = is_user_logged_in() ? 'logged_in' : 'not_logged_in';

		// Set parameters for each page type.
		if ( is_singular() ) {
			$params['post_type']   = get_post_type();
			$params['object_id']   = get_the_ID();
			$params['object_type'] = is_page() ? 'page' : 'single';

			if ( $enable_tax ) {
				global $post;

				foreach ( get_the_taxonomies( $post ) as $taxonomy => $taxonomy_name ) {
					$terms = get_the_terms( $post, $taxonomy );
					if ( ! empty( $terms ) ) {
						$terms = wp_list_pluck( (array) $terms, 'name' );

						$params[ 'tax_' . $taxonomy ] = $terms;
					}
				}
			}
		} elseif ( is_author() ) {
			$params['object_id']   = get_the_author_meta( 'ID' );
			$params['object_type'] = 'author_archive';
		} elseif ( is_date() ) {
			$params['object_type'] = 'date_archive';
		} elseif ( is_category() || is_tag() || is_tax() ) {
			/**
			 * The queried object from WP request
			 *
			 * @var stdClass $qry
			 */
			$qry = get_queried_object();

			if ( $enable_tax ) {
				$params[ 'tax_' . $qry->taxonomy ] = $qry->name;
			}

			$params['object_id'] = $qry->term_id;

			if ( is_category() ) {
				$params['object_type'] = 'cat_archive';
			} elseif ( is_tag() ) {
				$params['object_type'] = 'tag_archive';
			} else {
				$params['object_type'] = 'tax_archive';
			}
		} elseif ( is_post_type_archive() ) {
			$params['post_type']   = get_post_type();
			$params['object_type'] = 'post' === get_post_type() ? 'blog' : 'cpt_archive';
		}

		// Set object_id and object_type for Home & Blog pages.
		if ( is_front_page() && is_home() ) {  // Home blog posts.
			$params['object_type'] = 'home';

		} elseif ( is_front_page() ) { // Static home page.
			$params['object_id']   = get_option( 'page_on_front' );
			$params['object_type'] = 'home';

		} elseif ( is_home() ) { // Static blog page.
			$params['object_id']   = get_option( 'page_for_posts' );
			$params['object_type'] = 'blog';
		}

		// Track events if any.
		if ( $params ) {
			AEPC_Track::track( 'AdvancedEvents', $params );
		}
	}

	/**
	 * Track custom fields
	 *
	 * @return void
	 */
	public static function track_custom_fields() {
		$params = array();

		if ( is_page() || is_single() ) {
			global $post;

			// Get custom fields choose by user.
			$fields_to_track = AEPC_Track::get_custom_fields_to_track();
			foreach ( $fields_to_track as $field ) {
				$meta = get_post_meta( $post->ID, $field, true );
				if ( $meta ) {
					$params[ $field ] = $meta;
				}
			}
		}

		if ( $params ) {
			AEPC_Track::track( 'CustomFields', $params );
		}
	}

	/**
	 * Track all conversion events defined by user, for all events contains a specific URL
	 *
	 * @return void
	 */
	public static function track_conversions_events() {
		$conversions = AEPC_Track::get_conversions_events();

		foreach ( $conversions as $track ) {
			if ( 'page_visit' !== $track['trigger'] ) {
				continue;
			}

			$current_rel_uri = trailingslashit( home_url( add_query_arg( null, null ) ) );

			if (
				'*' === $track['url']
				|| ( 'contains' === $track['url_condition'] && preg_match(
					sprintf( '/%s/', addcslashes( strtr( $track['url'], array( '*' => '[^/]+' ) ), '/' ) ),
					$current_rel_uri
				) )
				|| ( 'exact' === $track['url_condition'] && (
					( '/' === $track['url'][0] && home_url( $track['url'] ) === $current_rel_uri )
					|| $track['url'] === $current_rel_uri
				) )
			) {
				AEPC_Track::track(
					$track['event'],
					$track['params'],
					$track['custom_params'],
					User_Data_Factory::create_from_session(),
					isset( $track['delay'] ) && '' !== $track['delay'] ? $track['delay'] : false
				);
			}
		}
	}

	/**
	 * Get all conversion events defined by user, for all events handled by css selector
	 *
	 * @return array
	 */
	public static function track_css_events() {
		$conversions   = AEPC_Track::get_conversions_events();
		$css_selectors = array();

		foreach ( $conversions as $track ) {
			if ( 'css_selector' !== $track['trigger'] ) {
				continue;
			}

			if ( ! isset( $css_selectors[ $track['css'] ] ) ) {
				$css_selectors[ $track['css'] ] = array();
			}

			$css_selectors[ $track['css'] ][] = array(
				'trackType'   => AEPC_Track::get_track_type( $track['event'] ),
				'trackName'   => $track['event'],
				'trackParams' => AEPC_Track::sanitize_fields( array_merge( $track['params'], $track['custom_params'] ) ),
			);
		}

		return $css_selectors;
	}

	/**
	 * Get all conversion events defined by user, for all events handled by link click, containing an URL
	 *
	 * @return array
	 */
	public static function track_link_click_events() {
		$conversions = AEPC_Track::get_conversions_events();
		$links       = array();

		foreach ( $conversions as $track ) {
			if ( 'link_click' !== $track['trigger'] ) {
				continue;
			}

			if ( ! isset( $links[ $track['url'] ] ) ) {
				$links[ $track['url'] ] = array();
			}

			$links[ $track['url'] ][ $track['url_condition'] ][] = array(
				'trackType'   => AEPC_Track::get_track_type( $track['event'] ),
				'trackName'   => $track['event'],
				'trackParams' => AEPC_Track::sanitize_fields( array_merge( $track['params'], $track['custom_params'] ) ),
			);
		}

		return $links;
	}

	/**
	 * Get all conversion events defined by user, for all events handled by JS event
	 *
	 * @return array
	 */
	public static function track_js_event_events() {
		$conversions = AEPC_Track::get_conversions_events();
		$links       = array();

		foreach ( $conversions as $track ) {
			if ( 'js_event' !== $track['trigger'] ) {
				continue;
			}

			if ( ! isset( $links[ $track['js_event_element'] ] ) ) {
				$links[ $track['js_event_element'] ] = array();
			}

			if ( ! isset( $links[ $track['js_event_element'] ][ $track['js_event_name'] ] ) ) {
				$links[ $track['js_event_element'] ][ $track['js_event_name'] ] = array();
			}

			$links[ $track['js_event_element'] ][ $track['js_event_name'] ][] = array(
				'trackType'   => AEPC_Track::get_track_type( $track['event'] ),
				'trackName'   => $track['event'],
				'trackParams' => AEPC_Track::sanitize_fields( array_merge( $track['params'], $track['custom_params'] ) ),
			);
		}

		return $links;
	}

	/**
	 * Return a formatted list of categories as facebook expects
	 *
	 * @param int|int[] $object_id The ID(s) of the object(s) to retrieve.
	 * @param string    $tax The taxonomy name.
	 *
	 * @return string
	 */
	public static function content_category_list( $object_id, $tax = 'product_cat' ) {
		$terms = wp_get_object_terms( $object_id, $tax );

		if ( is_wp_error( $terms ) ) {
			return '';
		}

		$categories = wp_list_pluck( $terms, 'name' );
		return array_pop( $categories );
	}

	/**
	 * Return a formatted list of categories as facebook expects
	 *
	 * @param WP_Term $term The term object for which have the path if it's a child.
	 *
	 * @return string
	 */
	public static function content_category_path( $term ) {
		$path = $term->name;

		if ( $term->parent ) {
			$parent = get_term( $term->parent );
			if ( $parent instanceof WP_Term ) {
				$path = self::content_category_path( $parent ) . ' > ' . $path;
			}
		}

		return html_entity_decode( $path );
	}

	// INTEGRATIONS.

	/**
	 * Add Facebook Pixel tracking also on AMP pages, if there is the AMP plugin activated
	 *
	 * @return void
	 */
	public static function track_on_amp_pages() {

		// Track the pixel events.
		self::track_standard_events();
		self::track_advanced_events();
		self::track_custom_fields();
		self::track_conversions_events();

		?>
		<amp-pixel src="<?php echo esc_url( 'https://www.facebook.com/tr?id=' . esc_attr( PixelCaffeine()->get_pixel_id() ) . '&ev=PageView&noscript=1' ); ?>&ord=RANDOM"></amp-pixel>
		<?php

		foreach ( AEPC_Track::get_standard_events() as $track_name => $pixels ) {
			foreach ( $pixels as $pixel ) {
				?>
				<amp-pixel src="<?php echo esc_url( $pixel->get_track_url() ); ?>&ord=RANDOM"></amp-pixel>
				<?php
			}
		}

		foreach ( AEPC_Track::get_custom_events() as $track_name => $pixels ) {
			foreach ( $pixels as $pixel ) {
				?>
				<amp-pixel src="<?php echo esc_url( $pixel->get_track_url() ); ?>&ord=RANDOM"></amp-pixel>
				<?php
			}
		}
	}
}
