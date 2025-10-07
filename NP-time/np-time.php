<?php
/**
 * Plugin Name:       NP-Time
 * Plugin URI:        https://nopassdev.com/
 * Description:       弹窗预约、小费、多语言兼容、下单后自动为用户注册激活发送邮件。
 * Version:           2.0.0
 * Requires at least: 5.6
 * Requires PHP:      7.4
 * Author:            NopassDev
 * Author URI:        https://nopassdev.com/
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Define constants.
if ( ! defined( 'NP_TIME_VERSION' ) ) {
	define( 'NP_TIME_VERSION', '2.0.0' );
}
if ( ! defined( 'NP_TIME_FILE' ) ) {
	define( 'NP_TIME_FILE', __FILE__ );
}
if ( ! defined( 'NP_TIME_PATH' ) ) {
	define( 'NP_TIME_PATH', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'NP_TIME_URL' ) ) {
	define( 'NP_TIME_URL', plugin_dir_url( __FILE__ ) );
}

// Autoload minimal includes.
require_once NP_TIME_PATH . 'includes/class-np-time.php';

// Bootstrap.
function np_time_bootstrap() {
	return NP_Time_Plugin::instance();
}
add_action( 'plugins_loaded', 'np_time_bootstrap' );
