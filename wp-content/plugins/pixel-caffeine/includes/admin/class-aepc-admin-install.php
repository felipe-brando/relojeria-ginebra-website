<?php
/**
 * Main class for the tasks to install the plugin
 *
 * @package Pixel Caffeine
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Main class for the tasks to install the plugin
 *
 * @class AEPC_Admin_Install
 */
class AEPC_Admin_Install {

	const AEPC_DB_VERSION = 202009141224;

	/**
	 * AEPC_Admin_Install Constructor.
	 *
	 * @return void
	 */
	public static function init() {
		if ( get_option( 'aepc_db_version' ) < self::AEPC_DB_VERSION ) {
			self::install();
			add_action( 'admin_init', array( __CLASS__, 'update' ) );

			// Save version on database.
			update_option( 'aepc_db_version', self::AEPC_DB_VERSION );
		}
	}

	/**
	 * Add the capability manage_ads for administrators
	 *
	 * @return void
	 */
	public static function add_role_capability() {
		$role = get_role( 'administrator' );
		if ( $role instanceof WP_Role ) {
			$role->add_cap( 'manage_ads' );
		}
	}

	/**
	 * Add the table for custom audiences
	 *
	 * @return void
	 */
	public static function install() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$sql = "
CREATE TABLE {$wpdb->prefix}aepc_custom_audiences (
  ID mediumint(9) NOT NULL AUTO_INCREMENT,
  name varchar(255) NOT NULL,
  description longtext NULL,
  date datetime NOT NULL default '0000-00-00 00:00:00',
  date_gmt datetime NOT NULL default '0000-00-00 00:00:00',
  modified_date datetime NOT NULL default '0000-00-00 00:00:00',
  modified_date_gmt datetime NOT NULL default '0000-00-00 00:00:00',
  retention tinyint(1) UNSIGNED DEFAULT 14 NOT NULL,
  rule longtext NOT NULL,
  fb_id varchar(15) NOT NULL DEFAULT 0,
  approximate_count bigint(20) NOT NULL DEFAULT 0,
  UNIQUE KEY ID (ID)
) $charset_collate;
CREATE TABLE {$wpdb->prefix}aepc_logs (
  ID mediumint(9) NOT NULL AUTO_INCREMENT,
  exception varchar(255) NOT NULL,
  message longtext NOT NULL,
  date datetime NOT NULL default '0000-00-00 00:00:00',
  context longtext NULL,
  UNIQUE KEY ID (ID)
) $charset_collate;
";
		dbDelta( $sql );

		// Add capability.
		self::add_role_capability();
	}

	/**
	 * The update task resolver
	 *
	 * @return void
	 */
	public static function update() {
		$db_version = get_option( 'aepc_db_version' );

		if ( empty( $db_version ) ) {
			return;
		}

		// Updates.
		switch ( true ) {
			case $db_version <= 201707141506:
				self::update_2_0();
				// no break.
			case $db_version <= 202009161224:
				self::update_2_1_4();
				// no break.
		}
	}

	/**
	 * UPDATES
	 */

	/**
	 * Run update from 2.0 and backwards
	 *
	 * @return void
	 */
	public static function update_2_0() {
		update_option( 'aepc_updated', '2.0' );
	}

	/**
	 * Run update from 2.1.4 and backwards
	 *
	 * @return void
	 */
	public static function update_2_1_4() {
		if ( AEPC_Admin::$api->is_logged_in() ) {
			add_option( 'aepc_show_warning_two_factory_mandatory', 1 );
		}
	}

}
