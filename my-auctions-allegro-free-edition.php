<?php
/*
 * Plugin Name: My auctions allegro
 * Plugin URI: https://wordpress.org/plugins/my-auctions-allegro-free-edition
 * Version: 2.6.14
 * Description: Plug-in display auctions from popular polish auction website called allegro.pl, also from 1.7 version you can import basic information from auctions to WooCommerce
 * Author: Grojan Team (rebranding to SkyCoder)
 * Author URI: https://www.grojanteam.pl
 * Text Domain: my-auctions-allegro-free-edition
 * Domain Path: /lang/
 * Requires PHP: 7.2
 * WC Requires at least: 5.0.0
 * WC Tested up to: 5.5.1
 */
defined('ABSPATH') or die();

define ( 'GJMAA_PATH', plugin_dir_path ( __FILE__ ) );
define ( 'GJMAA_PATH_CODE', plugin_dir_path ( __FILE__ ) .'/src/' );
define ( 'GJMAA_URL', plugin_dir_url ( __FILE__ ) );
define ( 'GJMAA_TEXT_DOMAIN', 'my-auctions-allegro-free-edition');

require_once(GJMAA_PATH . 'core/functions.php');

register_activation_hook ( __FILE__, ['GJMAA','install'] );

add_action('init',array("GJMAA",'initPlugin'));
add_action('widgets_init',array('GJMAA','initWidgets'));
add_action('admin_notices', array('GJMAA','checkForConnections'));
