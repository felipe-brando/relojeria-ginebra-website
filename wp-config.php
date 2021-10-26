<?php
/**
* The base configuration for WordPress
*
* The wp-config.php creation script uses this file during the
* installation. You don't have to use the web site, you can
* copy this file to "wp-config.php" and fill in the values.
*
* This file contains the following configurations:
*
* * MySQL settings
* * Secret keys
* * Database table prefix
* * ABSPATH
*
* @link https://wordpress.org/support/article/editing-wp-config-php/
*
* @package WordPress
*/
// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('WP_CACHE', true);
define( 'WPCACHEHOME', '/home/relojef/www/wp-content/plugins/wp-super-cache/' );
define( 'DB_NAME', "relojef129" );
/** MySQL database username */
define( 'DB_USER', "relojef129" );
/** MySQL database password */
define( 'DB_PASSWORD', "BidAche1986" );
/** MySQL hostname */
define( 'DB_HOST', "relojef129.mysql.db" );
/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );
/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );
/**#@+
* Authentication Unique Keys and Salts.
*
* Change these to different unique phrases!
* You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
* You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
*
* @since 2.6.0
*/
define( 'AUTH_KEY',         'K8eX=_IyGEXM38IG@Hl*gXesBW^x~e,KQb)=}4;&2+-Lx/b{( +5eW[9|vV,jA m' );
define( 'SECURE_AUTH_KEY',  'x[<WZ>4/y=K-kP<YF3`o@<YK />[^})x]]!hwS?1NWszh5sUlH1BK_dQbOFy1IY>' );
define( 'LOGGED_IN_KEY',    '72-%P{y=C;#.%H^E<e.&a.=Ee*89]J;o$YRS4@+!%9&e*r1X8[T%|`v3}K1,N=yC' );
define( 'NONCE_KEY',        'd!|Gi]^L1@>apFET&W=BXFjec4{Y{AD<#O@V.j$;HyOaTkJ(5+iQv$%RSS+FAg.z' );
define( 'AUTH_SALT',        'rOhW,h1j*(xJu*7rHwA6@KB(|i2|e 3+?iP#>do1mCj%L/1A;o)Wx7Zv=fu<cr#Z' );
define( 'SECURE_AUTH_SALT', ';/~U7S/:@TMsv|P;CU|<,UxC<6(aOnhOhp$heam0OlV6rI&R?i8@(*.CN!sat)jq' );
define( 'LOGGED_IN_SALT',   'c@inb)6E?VY^x~:iOA<<^cQ!46<o_`]K-[Zt_;Gjv`@1h|dtX~_*dn.Hii8M:$BO' );
define( 'NONCE_SALT',       'H~?C<V}89NRT{D+5cNiN`?XBdh6>7HZGU_Nv$nkgna33^cnf0vi0DPvA@X,2-ZFl' );
/**#@-*/
/**
* WordPress Database Table prefix.
*
* You can have multiple installations in one database if you give each
* a unique prefix. Only numbers, letters, and underscores please!
*/
$table_prefix = 'rjafbt86_';
/**
* For developers: WordPress debugging mode.
*
* Change this to true to enable the display of notices during development.
* It is strongly recommended that plugin and theme developers use WP_DEBUG
* in their development environments.
*
* For information on other constants that can be used for debugging,
* visit the documentation.
*
* @link https://wordpress.org/support/article/debugging-in-wordpress/
*/
define( 'WP_DEBUG', false );
/* That's all, stop editing! Happy publishing. */
/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
define( 'ABSPATH', __DIR__ . '/' );
}
/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
