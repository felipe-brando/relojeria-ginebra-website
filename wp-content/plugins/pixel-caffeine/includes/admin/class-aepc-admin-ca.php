<?php
/**
 * CRUD class for custom audience record
 *
 * @package Pixel Caffeine
 */

use PixelCaffeine\Admin\Exception\FBAPIException;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * CRUD class for custom audience record
 *
 * @class AEPC_Admin_CA
 */
class AEPC_Admin_CA {

	/**
	 * The custom audience data
	 *
	 * @var array
	 */
	protected $data = array(
		'ID'                => 0,
		'fb_id'             => 0,
		'date'              => '',
		'date_gmt'          => '',
		'modified_date'     => '',
		'modified_date_gmt' => '',
		'name'              => '',
		'description'       => '',
		'prefill'           => true,
		'retention'         => 14,
		'rule'              => array(),
		'approximate_count' => -1,
	);

	/**
	 * Save here if some error occurred per each field
	 *
	 * @var array
	 */
	protected $errors = array();

	/**
	 * Flag indicates if CA exists or not
	 *
	 * @var bool
	 */
	protected $exists = false;

	/**
	 * Save the available translations to go speedy
	 *
	 * @var array
	 */
	private static $translations = array();

	/**
	 * AEPC_Admin_CA constructor.
	 *
	 * Initialize the instance in base of ID. If ID is zero, it preparse the instance for a new CA to save
	 *
	 * @param int $id The Custom audience ID if any.
	 */
	public function __construct( $id = 0 ) {
		if ( ! empty( $id ) ) {
			$this->populate( $id );
		}
	}

	/**
	 * Populate the data from DB
	 *
	 * @param int $id The Custom audience ID to populate.
	 *
	 * @return void
	 */
	public function populate( $id ) {
		if ( empty( $id ) ) {
			return;
		}

		$ca_object = $this->read( $id );

		if ( ! $ca_object ) {
			return;
		}

		$this->data   = $ca_object;
		$this->exists = true;
	}

	/**
	 * Populate the data from DB by getting the record by Facebook ID, instead of record ID
	 *
	 * @param int $fb_id The custom audience ID from Facebook to use for populating instance.
	 *
	 * @return void
	 */
	public function populate_by_fb_id( $fb_id ) {
		if ( empty( $fb_id ) ) {
			return;
		}

		global $wpdb;

		$ca = wp_cache_get( 'fb_id_' . $fb_id, 'aepc-audiences' );
		if ( false === $ca ) {
			$ca = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}aepc_custom_audiences WHERE fb_id = %d", $fb_id ), ARRAY_A );
			wp_cache_set( 'fb_id_' . $fb_id, $ca, 'aepc-audiences' );
		}

		if ( $ca ) {
			$this->data   = array_map( 'maybe_unserialize', $ca );
			$this->exists = true;
		}
	}

	/**
	 * Retrieve the record from DB
	 *
	 * @param int $id The Custom Audience ID to read.
	 *
	 * @return array|null
	 */
	public function read( $id ) {
		global $wpdb;

		$ca = wp_cache_get( 'id_' . $id, 'aepc-audiences' );
		if ( false === $ca ) {
			$ca = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}aepc_custom_audiences WHERE ID = %d", $id ), ARRAY_A );
			wp_cache_set( 'id_' . $id, $ca, 'aepc-audiences' );
		}
		return is_array( $ca ) ? array_map( 'maybe_unserialize', $ca ) : $ca;
	}

	/**
	 * Create the record in DB
	 *
	 * @param array $args The custom audience data to create.
	 *
	 * @return bool|int
	 * @throws FBAPIException When API fail.
	 * @throws Exception When the fields are not validated.
	 */
	public function create( $args = array() ) {
		$args = array_intersect_key( $args, $this->data );
		$data = wp_parse_args( $args, $this->data );

		if ( $this->exists() ) {
			return false;
		}

		// Fields validation.
		$data = $this->validate_fields( $data );

		// Save in Facebook Ad account.
		if ( ! AEPC_Admin::$api->is_debug() ) {
			$res = AEPC_Admin::$api->create_audience( $data );

			// Add Facebook ID in arguments.
			$data['fb_id'] = isset( $res->id ) ? $res->id : 0;
		}

		// Sanitize values for database.
		$this->data = $data;
		foreach ( $data as &$val ) {
			if ( is_array( $val ) ) {
				// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions
				$val = serialize( $val );
			}
		}

		// Save the values.
		global $wpdb;

		// Add datatime.
		$data['date']     = $data['modified_date'];
		$data['date_gmt'] = $data['modified_date_gmt'];

		$wpdb->insert( $wpdb->prefix . 'aepc_custom_audiences', $data );
		$this->set_id( $wpdb->insert_id );
		$this->exists = true;

		return $this->get_id();
	}

	/**
	 * Update a record in DB. Must be defined an ID, to select what record you have to edit
	 *
	 * @param array $args The custom audience parameters to update.
	 *
	 * @return bool
	 * @throws FBAPIException When API fail.
	 * @throws Exception When the fields are not validated.
	 */
	public function update( $args = array() ) {
		if ( ! $this->exists() ) {
			return false;
		}

		$original_values = $this->data;
		$args            = array_intersect_key( $args, $this->data );
		$to_update       = wp_parse_args( $args, $this->data );

		if ( empty( $this->data['ID'] ) ) {
			return false;
		}

		$ca_object = $this->read( $this->data['ID'] );

		if ( ! $ca_object ) {
			return false;
		}

		// Fields validation.
		$to_update  = $this->validate_fields( $to_update );
		$this->data = $to_update;

		// Save in Facebook Ad account.
		if ( ! AEPC_Admin::$api->is_debug() ) {
			AEPC_Admin::$api->update_audience( $this->get_facebook_id(), $to_update );
		}

		// Sanitize values for database.
		foreach ( $to_update as $key => &$val ) {
			if ( is_array( $val ) ) {
				// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions
				$val = serialize( $val );
			}

			if ( $val === $original_values[ $key ] ) {
				unset( $val );
			}
		}

		// Do not update if all values are unchanged.
		if ( empty( $to_update ) ) {
			return false;
		}

		// Save the values.
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->update( $wpdb->prefix . 'aepc_custom_audiences', $to_update, array( 'ID' => $this->get_id() ) );
		wp_cache_delete( 'id_' . $this->get_id(), 'aepc-audiences' );
		wp_cache_delete( 'fb_id_' . $this->get_facebook_id(), 'aepc-audiences' );
		return true;
	}

	/**
	 * Refresh the size of audience, getting it from Facebook API
	 *
	 * @return void
	 * @throws FBAPIException When audience fetching is failing.
	 */
	public function refresh_size() {
		$ca = AEPC_Admin::$api->get_audience( $this->get_facebook_id(), 'approximate_count' );
		$this->update(
			array(
				'approximate_count' => intval( $ca->approximate_count ),
			)
		);
	}

	/**
	 * Refresh the data audience, getting it from Facebook API
	 *
	 * @return void
	 * @throws FBAPIException When audience fetching is failing.
	 */
	public function refresh_facebook_data() {
		$ca = AEPC_Admin::$api->get_audience( $this->get_facebook_id() );
		$this->update(
			array(
				'name'              => $ca->name,
				'description'       => $ca->description,
				'approximate_count' => intval( $ca->approximate_count ),
			)
		);
	}

	/**
	 * Fields validation, useful for create and update method
	 *
	 * @param string|array|object $args The fields to validate.
	 *
	 * @return array
	 * @throws Exception Throws a general Exception when the validation of the fields fail.
	 */
	protected function validate_fields( $args = array() ) {
		$this->reset_errors();

		$args = wp_parse_args( $args, $this->data );

		if ( empty( $args['name'] ) ) {
			AEPC_Admin_Notices::add_notice( 'error', 'ca_name', __( 'The name for the cluster is required.', 'pixel-caffeine' ) );
		}

		if ( empty( $args['rule'] ) ) {
			AEPC_Admin_Notices::add_notice( 'error', 'ca_include_url', __( 'You have to define one of included or excluded URL.', 'pixel-caffeine' ) );
			AEPC_Admin_Notices::add_notice( 'error', 'ca_exclude_url', __( 'You have to define one of included or excluded URL.', 'pixel-caffeine' ) );
			AEPC_Admin_Notices::add_notice( 'error', 'ca_rule', __( 'A custom audience from a website must contain at least one audience rule.', 'pixel-caffeine' ) );
		}

		if ( empty( $args['retention'] ) ) {
			AEPC_Admin_Notices::add_notice( 'error', 'ca_retention', __( 'You have to define the number of days to keep the user in this cluster.', 'pixel-caffeine' ) );
		}

		$args['retention']         = intval( $args['retention'] );
		$args['modified_date']     = current_time( 'mysql', false );
		$args['modified_date_gmt'] = current_time( 'mysql', true );
		$args['approximate_count'] = intval( $args['approximate_count'] );

		if ( $args['retention'] < 1 || $args['retention'] > 180 ) {
			AEPC_Admin_Notices::add_notice( 'error', 'ca_retention', __( 'The retention value must be beetwen 1 and 180 days value.', 'pixel-caffeine' ) );
		}

		// Remove the prefill field, because it's useful only for facebook request and it's useless for future.
		unset( $args['prefill'] );

		// Throw exception if error.
		if ( $this->have_errors() ) {
			throw new Exception( __( '<strong>Cannot save custom audience</strong> Please, check fields errors below.', 'pixel-caffeine' ) );
		}

		return $args;
	}

	/**
	 * Update a record in DB. Must be defined an ID, to select what record you have to edit
	 *
	 * @return bool
	 * @throws FBAPIException When API fail.
	 */
	public function delete() {
		if ( ! $this->exists() ) {
			return false;
		}

		// Save in Facebook Ad account.
		if ( ! AEPC_Admin::$api->is_debug() ) {
			AEPC_Admin::$api->delete_audience( $this->get_facebook_id() );
		}

		// Save the values.
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->delete( $wpdb->prefix . 'aepc_custom_audiences', array( 'ID' => $this->get_id() ) );
		wp_cache_delete( 'id_' . $this->get_id(), 'aepc-audiences' );
		wp_cache_delete( 'fb_id_' . $this->get_facebook_id(), 'aepc-audiences' );
		$this->exists = false;
		return true;
	}

	/**
	 * Create an identical CA in a new record with a new ID
	 *
	 * @param string $name The name of the new custom audience to duplicate.
	 *
	 * @return AEPC_Admin_CA
	 * @throws FBAPIException When Api fail.
	 */
	public function duplicate( $name = null ) {
		$new = clone $this;

		$new->set_id( 0 );
		$new->exists = false;

		// Change name if defined.
		if ( ! is_null( $name ) ) {
			$new->set_name( $name );
		}

		$new->create();

		return $new;
	}

	/**
	 * Check if the CA exists
	 *
	 * @return bool
	 */
	public function exists() {
		return (bool) $this->exists;
	}

	/**
	 * Get the ID of record of this instance
	 *
	 * @return int
	 */
	public function get_id() {
		return absint( $this->data['ID'] );
	}

	/**
	 * Set the ID of record of this instance and also populate data by ID
	 *
	 * @param int $id Set the custom audience ID.
	 *
	 * @return void
	 */
	public function set_id( $id ) {
		$this->data['ID'] = $id;
		$this->populate( $id );
	}

	/**
	 * Get the Facebook ID for this CA
	 *
	 * @return string
	 */
	public function get_facebook_id() {
		return $this->data['fb_id'];
	}

	/**
	 * Set the Facebook ID for this CA
	 *
	 * @param string $fb_id Set the custom audience Facebook ID.
	 *
	 * @return string
	 */
	public function set_facebook_id( $fb_id ) {
		$this->data['fb_id'] = $fb_id;
		return $fb_id;
	}

	/**
	 * Get the date of record of this instance
	 *
	 * @param bool $gmt Set if you want the date as GMT.
	 *
	 * @return string
	 */
	public function get_date( $gmt = false ) {
		return $this->data[ 'date' . ( $gmt ? '_gmt' : '' ) ];
	}

	/**
	 * Set the date of record of this instance
	 *
	 * @param string $date Set the custom audience Facebook date.
	 *
	 * @return void
	 */
	public function set_date( $date ) {
		$this->data['date']     = $date;
		$this->data['date_gmt'] = gmdate( 'Y-m-d H:i:s', ( strtotime( $date ) - ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) ) );
	}

	/**
	 * Get the time for print
	 *
	 * @param string $what You can return a specific date with a specific format - 't_time' for only time - 'h_time' for human date.
	 *
	 * @return array<string, string>|string
	 */
	public function get_human_date( $what = '' ) {
		$t_time = (string) mysql2date( get_option( 'date_format' ) . ' - ' . get_option( 'time_format' ), $this->get_date(), true );
		$time   = (int) mysql2date( 'G', $this->get_date( true ) );

		$time_diff = time() - $time;

		if ( $time_diff < MINUTE_IN_SECONDS ) {
			$h_time = __( 'Now', 'pixel-caffeine' );
		} else {
			/* translators: es. "2 minutes ago", "2 hours ago" */
			$h_time = sprintf( __( '%s ago', 'pixel-caffeine' ), human_time_diff( $time ) );
		}

		if ( ! empty( $what ) && isset( ${$what} ) ) {
			return ${$what};
		}

		return array(
			't_time' => $t_time,
			'h_time' => $h_time,
		);
	}

	/**
	 * Print out the human date
	 *
	 * @param string $what You can return a specific date with a specific format - 't_time' for only time - 'h_time' for human date.
	 *
	 * @return void
	 */
	public function human_date( $what ) {
		$human_date = $this->get_human_date( $what );
		if ( is_string( $human_date ) ) {
			echo esc_html( $human_date );
		}
	}

	/**
	 * Get the modified_date of record of this instance
	 *
	 * @param bool $gmt Set if you want the date as GMT.
	 *
	 * @return string
	 */
	public function get_modified_date( $gmt = false ) {
		return $this->data[ 'modified_date' . ( $gmt ? '_gmt' : '' ) ];
	}

	/**
	 * Set the modified_date of record of this instance
	 *
	 * @param string $modified_date Set the custom audience modified date.
	 *
	 * @return void
	 */
	public function set_modified_date( $modified_date ) {
		$this->data['modified_date']     = $modified_date;
		$this->data['modified_date_gmt'] = gmdate( 'Y-m-d H:i:s', ( strtotime( $modified_date ) - ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) ) );
	}

	/**
	 * Get the name of record of this instance
	 *
	 * @return string
	 */
	public function get_name() {
		return $this->data['name'];
	}

	/**
	 * Set the name of record of this instance
	 *
	 * @param string $name Set the custom audience name.
	 *
	 * @return void
	 */
	public function set_name( $name ) {
		$this->data['name'] = $name;
	}

	/**
	 * Get the description of record of this instance
	 *
	 * @return string
	 */
	public function get_description() {
		return $this->data['description'];
	}

	/**
	 * Set the ID of record of this instance
	 *
	 * @param string $description Set the custom audience description.
	 *
	 * @return void
	 */
	public function set_description( $description ) {
		$this->data['description'] = $description;
	}

	/**
	 * Get if the custom audience must include website traffic recorded prior to the audience creation.
	 *
	 * @return bool
	 */
	public function get_prefill() {
		return (bool) $this->data['prefill'];
	}

	/**
	 * Set if the custom audience must include website traffic recorded prior to the audience creation.
	 *
	 * @param bool $prefill Set the custom audience prefill.
	 *
	 * @return void
	 */
	public function set_prefill( $prefill ) {
		$this->data['prefill'] = $prefill;
	}

	/**
	 * Get number of days to keep the user in this cluster. You can use any value between 1 and 180 days.
	 * Defaults to 14 days if not specified.
	 *
	 * @return int
	 */
	public function get_retention() {
		return intval( $this->data['retention'] );
	}

	/**
	 * Set number of days to keep the user in this cluster. You can use any value between 1 and 180 days.
	 *
	 * @param int $retention Set the custom audience retention.
	 *
	 * @return void
	 */
	public function set_retention( $retention ) {
		$this->data['retention'] = $retention;
	}

	/**
	 * Get Audience rules to be applied on the referrer URL.
	 *
	 * @param string $what You can have a specific rule, as "include_url" or "exclude_url".
	 *
	 * @return array|string
	 */
	public function get_rule( $what = '' ) {
		$rules = maybe_unserialize( $this->data['rule'] );

		if ( 'include_url' === $what ) {
			foreach ( $rules as $rule ) {
				if ( 'include' === $rule['main_condition'] && 'url' === $rule['event_type'] && isset( $rule['conditions'][0]['value'] ) ) {
					return $rule['conditions'][0]['value'];
				}
			}
		} elseif ( 'exclude_url' === $what ) {
			foreach ( $rules as $rule ) {
				if ( 'exclude' === $rule['main_condition'] && 'url' === $rule['event_type'] && isset( $rule['conditions'][0]['value'] ) ) {
					return ! empty( $rule['conditions'][0]['value'] ) ? $rule['conditions'][0]['value'] : '';
				}
			}
		} else {
			return $rules;
		}

		return array();
	}

	/**
	 * Get the condition used for the URL field
	 *
	 * @param string $what You can have a specific rule, as "include_url" or "exclude_url".
	 *
	 * @return array|string
	 */
	public function get_url_condition( $what = '' ) {
		$rules = maybe_unserialize( $this->data['rule'] );

		if ( 'include_url' === $what ) {
			foreach ( $rules as $rule ) {
				if ( 'include' === $rule['main_condition'] && 'url' === $rule['event_type'] && isset( $rule['conditions'][0]['operator'] ) ) {
					return $rule['conditions'][0]['operator'];
				}
			}
		} elseif ( 'exclude_url' === $what ) {
			foreach ( $rules as $rule ) {
				if ( 'exclude' === $rule['main_condition'] && 'url' === $rule['event_type'] && isset( $rule['conditions'][0]['operator'] ) ) {
					return ! empty( $rule['conditions'][0]['operator'] ) ? $rule['conditions'][0]['operator'] : '';
				}
			}
		} else {
			return $rules;
		}

		return array();
	}

	/**
	 * Get Audience rules to be applied on the referrer URL.
	 *
	 * @param array $rules Set the custom audience rules.
	 *
	 * @return void
	 */
	public function set_rule( array $rules ) {
		$this->data['rule'] = $rules;
	}

	/**
	 * Get the rule filters, excluding URL filter
	 *
	 * @param string $condition Could be 'include' or 'exclude' to check specifically for what rule we want to get.
	 *
	 * @return array
	 */
	public function get_filters( $condition = '' ) {
		$filters = (array) $this->get_rule();

		// Exclude URL from filters.
		foreach ( $filters as $k => $f ) {
			if (
				'url' === $f['event_type']
				|| ! empty( $condition ) && (
					'include' === $condition && 'exclude' === $f['main_condition']
					|| 'exclude' === $condition && 'include' === $f['main_condition']
				)
			) {
				unset( $filters[ $k ] );
			}
		}

		return $filters;
	}

	/**
	 * Check if there are some filters in CA
	 *
	 * @param string $condition Could be 'include' or 'exclude' to check specifically for what rule we want to check.
	 *
	 * @return bool
	 */
	public function has_filters( $condition = '' ) {
		$filters = $this->get_filters();

		// Check for specific condition.
		if ( ! empty( $condition ) ) {
			foreach ( $filters as $filter ) {
				if (
					'include' === $condition && 'include' === $filter['main_condition']
					|| 'exclude' === $condition && 'exclude' === $filter['main_condition']
				) {
					return true;
				}
			}
		} else {
			return ! empty( $filters );
		}

		return false;
	}

	/**
	 * Get the size of audience
	 *
	 * @return int
	 */
	public function get_size() {
		return intval( $this->data['approximate_count'] );
	}

	/**
	 * Translate the configuration array of a filter into a readable statement to print out on screen
	 *
	 * @param array  $rule The rule configuration.
	 * @param string $highlight_before Prefix of highlighting.
	 * @param string $highlight_after Suffix of highlighting.
	 *
	 * @return string
	 */
	public function get_human_filter( $rule, $highlight_before = '[', $highlight_after = ']' ) {

		// Standard statements for the filter rows, they may be changed in some condition.
		$translate_words = array(

			// Specific cases.
			'attributes'     => array(
				'login_status' => array(
					/* translators: 2: the value, in the custom audience rule, in a filter statement */
					'eq'  => __( 'is %2$s', 'pixel-caffeine' ),
					/* translators: 2: the value, in the custom audience rule, in a filter statement */
					'neq' => __( 'is not %2$s', 'pixel-caffeine' ),
				),
				'referrer'     => array(
					/* translators: 2: the value, in the custom audience rule, in a filter statement */
					'i_contains'     => __( 'come from %2$s', 'pixel-caffeine' ),
					/* translators: 2: the value, in the custom audience rule, in a filter statement */
					'i_not_contains' => __( 'don\'t come from %2$s', 'pixel-caffeine' ),
				),
				'device_type'  => array(
					/* translators: 2: the value, in the custom audience rule, in a filter statement */
					'i_contains'     => __( 'use %2$s', 'pixel-caffeine' ),
					/* translators: 2: the value, in the custom audience rule, in a filter statement */
					'i_not_contains' => __( 'don\'t use %2$s', 'pixel-caffeine' ),
					/* translators: 2: the value, in the custom audience rule, in a filter statement */
					'eq'             => __( 'use %2$s', 'pixel-caffeine' ),
					/* translators: 2: the value, in the custom audience rule, in a filter statement */
					'neq'            => __( 'don\'t use %2$s', 'pixel-caffeine' ),
				),
			),

			'blog'           => array(
				'categories'    => array(
					/* translators: 1: the taxonomy name, 2 the term of that taxonomy - in the custom audience rule, in the filter summary on frontend */
					'i_contains'     => __( 'read posts from %2$s %1$s', 'pixel-caffeine' ),
					/* translators: 1: the taxonomy name, 2 the term of that taxonomy - in the custom audience rule, in the filter summary on frontend */
					'i_not_contains' => __( 'don\'t read posts from %2$s %1$s', 'pixel-caffeine' ),
				),
				'tax_post_tag'  => array(
					/* translators: 1: the taxonomy name, 2 the term of that taxonomy - in the custom audience rule, in the filter summary on frontend */
					'i_contains'     => __( 'read posts from %2$s %1$s', 'pixel-caffeine' ),
					/* translators: 1: the taxonomy name, 2 the term of that taxonomy - in the custom audience rule, in the filter summary on frontend */
					'i_not_contains' => __( 'don\'t read posts from %2$s %1$s', 'pixel-caffeine' ),
				),
				'posts'         => array(
					/* translators: 1: the post type or blog, 2: should be "the post(s) <post title>" or "any post" if all - in the custom audience rule, in the filter summary on frontend */
					'i_contains'     => __( 'read %2$s from %1$s', 'pixel-caffeine' ),
					/* translators: 1: the post type or blog, 2: should be "the post(s) <post title>" or "any post" if all - in the custom audience rule, in the filter summary on frontend */
					'i_not_contains' => __( 'don\'t read %2$s from %1$s', 'pixel-caffeine' ),
				),
				'pages'         => array(
					/* translators: 1: is "page" or "pages", 2: is the page title - in the custom audience rule, in the filter summary on frontend */
					'i_contains'     => __( 'visit %2$s %1$s', 'pixel-caffeine' ),
					/* translators: 1: is "page" or "pages", 2: is the page title - in the custom audience rule, in the filter summary on frontend */
					'i_not_contains' => __( 'don\'t visit %2$s %1$s', 'pixel-caffeine' ),
				),
				'custom_fields' => array(
					/* translators: 1: the custom field key, 2: the value. Complete statement: "read a post contains [field_key] custom field with [value] and [value2] as value" */
					'i_contains'     => __( 'read a post contains %1$s %2$s', 'pixel-caffeine' ),
					/* translators: 1: the custom field key, 2: the value. Complete statement: "don\'t read a post contains [field_key] custom field with [value] and [value2] as value" */
					'i_not_contains' => __( 'don\'t read a post contains %1$s %2$s', 'pixel-caffeine' ),
				),
			),

			'ecommerce'      => array(

				'ViewContent'          => array(
					'generic'  => __( 'visit a product page', 'pixel-caffeine' ),
					'specific' => array(
						/* translators: %s: the product title - in the custom audience rule, in the filter summary on frontend */
						'singular' => __( 'visit %s product page', 'pixel-caffeine' ),
						/* translators: %s: the product titles - in the custom audience rule, in the filter summary on frontend */
						'plural'   => __( 'visit %s product pages', 'pixel-caffeine' ),
					),
				),

				'Search'               => array(
					'generic' => _x( 'search', 'it is followed by "something" or a specific string searched', 'pixel-caffeine' ),
				),

				'AddToCart'            => array(
					'generic'  => __( 'add to cart a product', 'pixel-caffeine' ),
					'specific' => array(
						/* translators: 2: the product title - in the custom audience rule, in the filter summary on frontend */
						'singular' => __( 'add to cart %2$s product', 'pixel-caffeine' ),
						/* translators: 2: the product titles - in the custom audience rule, in the filter summary on frontend */
						'plural'   => __( 'add to cart %2$s products', 'pixel-caffeine' ),
					),
				),

				'AddToWishlist'        => array(
					'generic'  => __( 'add to wishlist a product', 'pixel-caffeine' ),
					'specific' => array(
						/* translators: 2: the product title - in the custom audience rule, in the filter summary on frontend */
						'singular' => __( 'add to wishlist %2$s product', 'pixel-caffeine' ),
						/* translators: 2: are the product titles - in the custom audience rule, in the filter summary on frontend */
						'plural'   => __( 'add to wishlist %2$s products', 'pixel-caffeine' ),
					),
				),

				'InitiateCheckout'     => array(
					'generic'  => __( 'enter the checkout flow', 'pixel-caffeine' ),
					'specific' => array(
						/* translators: 2: the product title - in the custom audience rule, in the filter summary on frontend */
						'singular' => __( 'enter the checkout flow containing %2$s product', 'pixel-caffeine' ),
						/* translators: 2: are the product titles - in the custom audience rule, in the filter summary on frontend */
						'plural'   => __( 'enter the checkout flow containing %2$s products', 'pixel-caffeine' ),
					),
				),

				'AddPaymentInfo'       => array(
					'generic'  => __( 'add payment information in the checkout flow', 'pixel-caffeine' ),
					'specific' => array(
						/* translators: 2: the product title - in the custom audience rule, in the filter summary on frontend */
						'singular' => __( 'add payment information in the checkout flow containing %2$s product', 'pixel-caffeine' ),
						/* translators: 2: are the product titles - in the custom audience rule, in the filter summary on frontend */
						'plural'   => __( 'add payment information in the checkout flow containing %2$s products', 'pixel-caffeine' ),
					),
				),

				'Purchase'             => array(
					'generic'  => __( 'make a purchase', 'pixel-caffeine' ),
					'specific' => array(
						/* translators: 2: the product title - in the custom audience rule, in the filter summary on frontend */
						'singular' => __( 'purchase %2$s product', 'pixel-caffeine' ),
						/* translators: 2: are the product titles - in the custom audience rule, in the filter summary on frontend */
						'plural'   => __( 'purchase %2$s products', 'pixel-caffeine' ),
					),
				),

				'Lead'                 => array(
					'generic'  => __( 'sign up for something', 'pixel-caffeine' ),
					'specific' => array(
						/* translators: 2: the product title - in the custom audience rule, in the filter summary on frontend */
						'singular' => __( 'sign up for %2$s product', 'pixel-caffeine' ),
						/* translators: 2: are the product titles - in the custom audience rule, in the filter summary on frontend */
						'plural'   => __( 'sign up for %2$s products', 'pixel-caffeine' ),
					),
				),

				'CompleteRegistration' => array(
					'generic'  => __( 'complete registration for a service', 'pixel-caffeine' ),
					'specific' => array(
						/* translators: 2: the product title - in the custom audience rule, in the filter summary on frontend */
						'singular' => __( 'complete registration for %2$s product', 'pixel-caffeine' ),
						/* translators: 2: are the product titles - in the custom audience rule, in the filter summary on frontend */
						'plural'   => __( 'complete registration for %2$s products', 'pixel-caffeine' ),
					),
				),
			),

			// Standard statements.

			/* translators: 1: the parameter, 2: the value - in the custom audience rule, in the filter summary on frontend */
			'i_contains'     => __( '%1$s contains %2$s', 'pixel-caffeine' ),
			/* translators: 1: the parameter, 2: the value - in the custom audience rule, in the filter summary on frontend */
			'i_not_contains' => __( '%1$s not contains %2$s', 'pixel-caffeine' ),
			/* translators: 1: the parameter, 2: the value - in the custom audience rule, in the filter summary on frontend */
			'eq'             => __( 'have set %2$s as %1$s', 'pixel-caffeine' ),
			/* translators: 1: the parameter, 2: the value - in the custom audience rule, in the filter summary on frontend */
			'neq'            => __( 'have not set %2$s as %1$s', 'pixel-caffeine' ),
			/* translators: 1: the parameter, 2: the value - in the custom audience rule, in the filter summary on frontend */
			'gte'            => __( '%1$s greater than or equal to %2$s', 'pixel-caffeine' ),
			/* translators: 1: the parameter, 2: the value - in the custom audience rule, in the filter summary on frontend */
			'gt'             => __( '%1$s greater than %2$s', 'pixel-caffeine' ),
			/* translators: 1: the parameter, 2: the value - in the custom audience rule, in the filter summary on frontend */
			'lte'            => __( '%1$s lower than or equal to %2$s', 'pixel-caffeine' ),
			/* translators: 1: the parameter, 2: the value - in the custom audience rule, in the filter summary on frontend */
			'lt'             => __( '%1$s lower than %2$s', 'pixel-caffeine' ),
		);

		// Don't add any statement for URL filter.
		if ( 'url' === $rule['event_type'] && 'url' === $rule['event'] ) {
			return '';
		}

		$conditions   = array();
		$values_count = 0;
		$prepend      = '';

		// Force to add conditions key when it doesn't exist.
		if ( ! isset( $rule['conditions'] ) ) {
			$rule['conditions'] = array();
		}

		$event_statements = isset( $translate_words[ $rule['event_type'] ] ) && is_array( $translate_words[ $rule['event_type'] ] ) && isset( $translate_words[ $rule['event_type'] ][ $rule['event'] ] )
			? $translate_words[ $rule['event_type'] ][ $rule['event'] ]
			: false;

		foreach ( $rule['conditions'] as $k => $condition ) {
			$statement = '';
			$parameter = '';
			$value     = '';

			// Remove condition if it's not allowed empty key and empty value.
			if ( in_array( $rule['event_type'], array( 'attributes', 'blog' ), true ) && ( isset( $condition['key'] ) && empty( $condition['key'] ) || empty( $condition['value'] ) ) ) {
				unset( $rule['conditions'][ $k ] );
				continue;
			}

			// Define the value and parameters in specific cases.
			if ( ! empty( $condition['value'] ) ) {
				$condition['value'] = array_map( 'trim', explode( ',', $condition['value'] ) );

				// Save the count of values useful for parameter, to choose between singular and plural.
				$values_count = count( $condition['value'] );

				// Use this to set some text after and before the value, by replacing the value of variable with a localized string and %s for the value.
				$value_wrapper = '%s';

				// Sanitize all values.
				foreach ( $condition['value'] as &$v ) {

					if ( 'attributes' === $rule['event_type'] && 'language' === $rule['event'] ) {
						/**
						 * Get language english name.
						 */

						if ( 'en-US' === $v ) {
							$v = __( 'English (American)', 'pixel-caffeine' );
						} else {
							if ( empty( self::$translations ) ) {
								require_once ABSPATH . 'wp-admin/includes/translation-install.php';
								self::$translations = wp_get_available_translations();
							}

							foreach ( self::$translations as $translation ) {
								if ( str_replace( '_', '-', $translation['language'] ) === $v ) {
									$v = $translation['english_name'];
								}
							}
						}
					} elseif ( 'blog' === $rule['event_type'] && in_array( $rule['event'], array( 'categories', 'tax_post_tag' ), true ) ) {
						/**
						 * Get label of taxonomy.
						 */

						if ( '[[any]]' === $v ) {
							$v = _x( 'any', 'Sentence like: "read posts from any category"', 'pixel-caffeine' );
						} else {
							$term = get_term_by( 'slug', $v, str_replace( 'tax_', '', ( ! empty( $condition['key'] ) ? $condition['key'] : $rule['event'] ) ) );
							if ( $term instanceof WP_Term ) {
								$v = $term->name;
							}
						}

						// Set now parameter.
						if ( ! empty( $condition['key'] ) && 'tax_category' === $condition['key'] ) {
							$parameter = _n( 'category', 'categories', $values_count, 'pixel-caffeine' );
						} elseif ( 'tax_post_tag' === $rule['event'] && 'tax_post_tag' === $condition['key'] ) {
							$parameter = _n( 'tag', 'tags', $values_count, 'pixel-caffeine' );
						} elseif ( function_exists( 'WC' ) && 'tax_post_tag' === $rule['event'] && 'tax_product_tag' === $condition['key'] ) {
							$parameter = _n( 'product tag', 'product tags', $values_count, 'pixel-caffeine' );
						} else {
							if ( '[[any]]' === $v ) {
								$v = __( 'any term', 'pixel-caffeine' );
							}

							$taxonomy = get_taxonomy( str_replace( 'tax_', '', $condition['key'] ) );
							if ( $taxonomy ) {
								$label = $taxonomy->label;
							} else {
								$label = str_replace( 'tax_', '', $condition['key'] );
							}
							/* translators: it is part of the human statement for the custom audience filter. In this case %s is the taxonomy name */
							$parameter = sprintf( __( 'of %s custom taxonomy', 'pixel-caffeine' ), $highlight_before . $label . $highlight_after );
						}
					} elseif ( 'blog' === $rule['event_type'] && 'posts' === $rule['event'] ) {
						/**
						 * Get post title
						 */

						if ( '[[any]]' === $v ) {
							$v = __( 'any post', 'pixel-caffeine' );
						} else {
							/* translators: it is part of the human statement for the custom audience filter. In this case %s is count of values */
							$value_wrapper = _n( 'the post %s', 'the posts %s', $values_count, 'pixel-caffeine' );
							$post_title    = get_the_title( $v );
							if ( $post_title ) {
								$v = $post_title;
							}
						}

						// Set now parameter.
						if ( 'post' === $condition['key'] ) {
							$parameter = 'blog';
						} else {
							/* translators: The complete statement is "read the posts [Post Title 1] and [Post Title 2] from [Post Type Name] post type" */
							$key       = __( '%s post type', 'pixel-caffeine' );
							$post_type = get_post_type_object( $condition['key'] );
							if ( $post_type ) {
								/**
								 * Define it as stdClass instead of simple object
								 *
								 * @var stdClass $post_type_labels.
								 */
								$post_type_labels = get_post_type_labels( $post_type );
								$condition['key'] = $post_type_labels->singular_name;
							}
							$parameter = sprintf( $key, $highlight_before . ucfirst( $condition['key'] ) . $highlight_after );
						}
					} elseif ( 'blog' === $rule['event_type'] && 'pages' === $rule['event'] ) {
						/**
						 * Get page title
						 */

						if ( '[[any]]' === $v ) {
							$v = __( 'any', 'pixel-caffeine' );
						} elseif ( ! in_array( $v, array( 'home', 'blog' ), true ) ) {
							$v = get_the_title( $v );
						}

						// Set now parameter.
						$parameter = _n( 'page', 'pages', $values_count, 'pixel-caffeine' );
					} elseif ( 'blog' === $rule['event_type'] && 'custom_fields' === $rule['event'] ) {
						/**
						 * Exception for custom fields
						 */

						/* translators: it is part of the human statement for the custom audience filter. In this case %s is count of values */
						$value_wrapper = _n( 'with %s value', 'with %s values', $values_count, 'pixel-caffeine' );
						if ( '[[any]]' === $v ) {
							$v = __( 'any', 'pixel-caffeine' );
						}

						// Set now parameter.
						if ( '[[any]]' === $condition['key'] ) {
							$parameter = __( 'the custom fields defined on \'Track Custom Fields Based Events\' option on General Settings tab', 'pixel-caffeine' );
						}
					} elseif ( 'ecommerce' === $rule['event_type'] && 'Search' === $rule['event'] ) {
						/**
						 * Exception search event
						 */

						if ( '[[any]]' === $v ) {
							$v = __( 'something', 'pixel-caffeine' );
						}

						$statement = '%2$s';
					} elseif ( 'ecommerce' === $rule['event_type'] ) {
						/**
						 * Exception search event
						 */

						if ( '[[any]]' === $v ) {
							$v = __( 'any', 'pixel-caffeine' );
						}

						// Replace IDs with product title, if a store plugin installed.
						if ( 'content_ids' === $condition['key'] ) {
							foreach ( AEPC_Addons_Support::get_detected_addons() as $addon ) {
								if ( $addon->is_product_of_this_addon( intval( $v ) ) ) {
									$v = $addon->get_product_name( intval( $v ) );
								}
							}
						}
					}

					// Translate underscores into spaces.
					if ( empty( $condition['key'] ) || ! in_array( $condition['key'], array( 'content_type' ), true ) ) {
						$v = str_replace( '_', ' ', $v );
					}

					$v = ! empty( $v ) ? $highlight_before . $v . $highlight_after : '';
				}

				// Format array list.
				if ( 1 === $values_count ) {
					$value = $condition['value'][0];
				} else {
					$last_condition = array_pop( $condition['value'] );
					$value          = implode( ', ', $condition['value'] ) . ' ' . __( 'or', 'pixel-caffeine' ) . ' ' . $last_condition;
				}

				// Wrap the value list with some text defined in some cases.
				$value = sprintf( $value_wrapper, $value );

			}

			// Define the parameter, for cases not covered above.
			if ( empty( $parameter ) ) {
				if ( 'attributes' === $rule['event_type'] && 'language' === $rule['event'] ) {
					$parameter = __( 'browser language', 'pixel-caffeine' );

				} elseif ( 'blog' === $rule['event_type'] && 'custom_fields' === $rule['event'] && '[[any]]' !== $condition['key'] ) {
					/* translators: it is part of the human statement for the custom audience filter. In this case %s the custom field name */
					$parameter = sprintf( __( '%s custom field', 'pixel-caffeine' ), $highlight_before . $condition['key'] . $highlight_after );

				} elseif ( 'ecommerce' === $rule['event_type'] ) {
					/* translators: it is part of the human statement for the custom audience filter. In this case %s the parameter name */
					$parameter = sprintf( __( '%s parameter', 'pixel-caffeine' ), $highlight_before . $condition['key'] . $highlight_after );

				} elseif ( ! empty( $condition['key'] ) ) {
					$parameter = $condition['key'];
				}
			}

			// Set by default the statement to use for this row, it could be changed in some cases above.
			if ( empty( $statement ) ) {
				if ( is_array( $event_statements ) && isset( $event_statements[ $condition['operator'] ] ) && is_string( $event_statements[ $condition['operator'] ] ) ) {
					$statement = ' ' . $event_statements[ $condition['operator'] ];
				} elseif ( is_array( $event_statements ) && isset( $condition['key'] ) && 'content_ids' === $condition['key'] && isset( $event_statements['specific'] ) ) {
					$statement = $event_statements['specific'];

					if ( is_array( $statement ) ) {
						$statement = $statement[ $values_count <= 1 ? 'singular' : 'plural' ];
					}

					$statement = ' ' . $statement;
				} elseif ( is_string( $translate_words[ $condition['operator'] ] ) ) {
					$statement = ' ' . $translate_words[ $condition['operator'] ];
				}
			}

			if ( empty( $value ) ) {
				$value = __( 'nothing', 'pixel-caffeine' );
			}

			// Decide what statement use.
			if ( ! empty( $condition['key'] ) && 'content_ids' === $condition['key'] ) {
				$prepend = sprintf( trim( $statement ), $parameter, $value ) . ' ';
			} else {
				$conditions[] = sprintf( trim( $statement ), $parameter, $value );
			}
		}

		// Set some statement to prepend to above generated.
		if ( empty( $prepend ) && is_array( $event_statements ) && isset( $event_statements['generic'] ) && is_string( $event_statements['generic'] ) ) {
			$prepend = $event_statements['generic'] . ' ';
		} elseif ( 'events' === $rule['event_type'] ) {
			$prepend = sprintf( 'is tracked with the event [%s]', $rule['event'] ) . ' ';
		}

		// Add conditions if any.
		if ( ! empty( $prepend ) && ! empty( $conditions ) && ! in_array( $rule['event'], array( 'Search' ), true ) ) {
			$prepend .= __( 'with', 'pixel-caffeine' ) . ' ';
		}

		// Save final text.
		if ( empty( $conditions ) ) {
			$final = '';
		} elseif ( 1 === count( $conditions ) ) {
			$final = $conditions[0];
		} else {
			$last_condition = array_pop( $conditions );
			$final          = implode( ', ', $conditions ) . ' ' . __( 'and', 'pixel-caffeine' ) . ' ' . $last_condition;
		}

		// Save final statement.
		return trim( $prepend . $final );
	}

	/**
	 * Get a list of all rule formatted for human reading to print out on frontend
	 *
	 * @param string $highlight_before What put before the highlighted word.
	 * @param string $highlight_after What put after the highlighted word.
	 *
	 * @return array
	 */
	public function get_human_rule_list( $highlight_before = '[', $highlight_after = ']' ) {
		$filters = (array) $this->get_rule();

		// Change each condition into text readable.
		foreach ( $filters as $filter_id => &$rule ) {

			// Don't add any statement for URL filter.
			if ( 'url' === $rule['event_type'] && 'url' === $rule['event'] ) {
				unset( $filters[ $filter_id ] );
				continue;
			}

			// Save final statement.
			$rule = $this->get_human_filter( $rule, $highlight_before, $highlight_after );
		}

		return array_filter( $filters );
	}

	/**
	 * Set the size of audience
	 *
	 * @param int $size Set the custom audience size.
	 *
	 * @return void
	 */
	public function set_size( $size ) {
		$this->data['approximate_count'] = intval( $size );
	}

	/**
	 * Add new filter to rules already existing with AND condition
	 *
	 * @param array $rule The rule configuration to add into filters.
	 *
	 * @return void
	 */
	public function add_filter( array $rule ) {

		// Remove conditions with emptu value and key.
		foreach ( $rule['conditions'] as $k => $condition ) {
			if (
				isset( $condition['key'] ) && empty( $condition['key'] )
				|| ! isset( $condition['key'] ) && empty( $condition['value'] )
			) {
				unset( $rule['conditions'][ $k ] );
			}
		}

		$this->set_rule( array_merge( (array) $this->get_rule(), array( $rule ) ) );
	}

	/**
	 * Get error message if any
	 *
	 * @param string $field The field from where get the errors.
	 *
	 * @return array
	 */
	public function get_error( $field ) {
		return AEPC_Admin_Notices::get_notices( 'error', 'ca_' . $field );
	}

	/**
	 * Return if the CA have some errors
	 *
	 * @return bool
	 */
	public function have_errors() {
		return AEPC_Admin_Notices::has_notice( 'error' );
	}

	/**
	 * Get all error messages
	 *
	 * @return array
	 */
	public function get_errors() {
		return AEPC_Admin_Notices::get_notices( 'error' );
	}

	/**
	 * Delete an error for a field
	 *
	 * @param string $field The field from where remove the errors.
	 *
	 * @return void
	 */
	public function remove_error( $field ) {
		AEPC_Admin_Notices::remove_notices( 'error', 'ca_' . $field );

		if ( 'rule' === $field ) {
			AEPC_Admin_Notices::remove_notices( 'error', 'ca_include_url' );
			AEPC_Admin_Notices::remove_notices( 'error', 'ca_exclude_url' );
		}
	}

	/**
	 * Reset all errors
	 *
	 * @return void
	 */
	public function reset_errors() {
		AEPC_Admin_Notices::remove_notices( 'error' );
	}

}
