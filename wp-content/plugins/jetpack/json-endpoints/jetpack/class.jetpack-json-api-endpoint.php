<?php

include JETPACK__PLUGIN_DIR . '/modules/module-info.php';

/**
 * Base class for Jetpack Endpoints, has the validate_call helper function.
 */
abstract class Jetpack_JSON_API_Endpoint extends WPCOM_JSON_API_Endpoint {

	protected $needed_capabilities;
	protected $expected_actions = array();
	protected $action;


	public function callback( $path = '', $blog_id = 0, $object = null ) {
		if ( is_wp_error( $error = $this->validate_call( $blog_id, $this->needed_capabilities ) ) ) {
			return $error;
		}

		if ( is_wp_error( $error = $this->validate_input( $object ) ) ) {
			return $error;
		}

		if ( ! empty( $this->action ) ) {
			if( is_wp_error( $error = call_user_func( array( $this, $this->action ) ) ) ) {
				return $error;
			}
		}

		return $this->result();
	}

	abstract protected function result();

	protected function validate_input( $object ) {
		$args = $this->input();

		if( isset( $args['action'] ) && $args['action'] == 'update' ) {
			$this->action = 'update';
		}

		if ( preg_match( "/\/update\/?$/", $this->path ) ) {
			$this->action = 'update';

		} elseif( preg_match( "/\/install\/?$/", $this->path ) ) {
			$this->action = 'install';

		} elseif( ! empty( $args['action'] ) ) {
			if( ! in_array( $args['action'], $this->expected_actions ) ) {
				return new WP_Error( 'invalid_action', __( 'You must specify a valid action', 'jetpack' ) );
			}
			$this->action =  $args['action'];
		}
		return true;
	}

	/**
	 * Switches to the blog and checks current user capabilities.
	 * @return bool|WP_Error a WP_Error object or true if things are good.
	 */
	protected function validate_call( $_blog_id, $capability, $check_validation = true ) {
		$blog_id = $this->api->switch_to_blog_and_validate_user( $this->api->get_blog_id( $_blog_id ) );
		if ( is_wp_error( $blog_id ) ) {
			return $blog_id;
		}

		if ( is_wp_error( $error = $this->check_capability( $capability ) ) ) {
			return $error;
		}

		if (
			$check_validation &&
			'GET' !== $this->method &&
			/**
			 * Filter to disallow JSON API requests to the site.
			 * Setting to false disallows you to manage your site remotely from WordPress.com
			 * and disallows plugin auto-updates.
			 *
			 * @since 7.3.0
			 *
			 * @param bool $check_validation Whether to allow API requests to manage the site
			 */
			! apply_filters( 'jetpack_json_manage_api_enabled', $check_validation )
		) {
			return new WP_Error( 'unauthorized_full_access', __( 'Full management mode is off for this site.', 'jetpack' ), 403 );
		}

		return true;
	}

	/**
	 * @param $capability
	 *
	 * @return bool|WP_Error
	 */
	protected function check_capability( $capability ) {
		// If this endpoint accepts site based authentication, skip capabilities check.
		if ( $this->accepts_site_based_authentication() ) {
			return true;
		}
		if ( is_array( $capability ) ) {
			// the idea is that the we can pass in an array of capabilitie that the user needs to have before we allowing them to do something
			$capabilities = ( isset( $capability['capabilities'] ) ? $capability['capabilities'] : $capability );

			// We can pass in the number of conditions we must pass by default it is all.
			$must_pass = ( isset( $capability['must_pass'] ) && is_int( $capability['must_pass'] ) ? $capability['must_pass'] : count( $capabilities ) );

			$failed = array(); // store the failed capabilities
			$passed = 0; //

			foreach ( $capabilities as $cap ) {
				if ( current_user_can( $cap ) ) {
					$passed ++;
				} else {
					$failed[] = $cap;
				}
			}
			// Check if all conditions have passed.
			if ( $passed < $must_pass ) {
				return new WP_Error(
					'unauthorized',
					/* translators: %s: comma-separated list of capabilities */
					sprintf( __( 'This user is not authorized to %s on this blog.', 'jetpack' ), implode( ', ', $failed ) ),
					403
				);
			}
		} else {
			if ( !current_user_can( $capability ) ) {
				return new WP_Error( 'unauthorized', sprintf( __( 'This user is not authorized to %s on this blog.', 'jetpack' ), $capability ), 403 );
			}
		}

		return true;
	}

}
