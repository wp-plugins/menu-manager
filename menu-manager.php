<?php
/*
Plugin Name: Menu Manager
Plugin URI: http://www.feelinc.me/plugin/menu-manager-wordpress-plugin
Description: Menu managers enables you to manage blog menu very easy. You can add Pages, Categories, and Custom Link into your blog menu.
Version: 1.0.4
Author: Sulaeman
Author URI: http://www.feelinc.me/
*/

global $wpdb;

define('MM_VERSION', '1.0.4');
define('MM_PLUGIN_NAME', 'Menu_Manager');
define('MM_FILE', basename(__FILE__));
define('MM_DIR', dirname(__FILE__));
define('MM_ADMIN_URL', $_SERVER['PHP_SELF'] . "?page=" . basename(MM_DIR) . '/' . MM_FILE);
define('MM_PATH', MM_DIR . '/' . MM_FILE);
define('MM_DISPLAY_NAME', 'Menu Manager');
define('MM_DISPLAY_URL', get_bloginfo('wpurl') . '/wp-content/plugins/' . basename(MM_DIR) . '/display');
define('MM_TEMPLATE', 'mm-display.php');
define('MM_TABLE_NAME', $wpdb->prefix . 'mm_menus');

require_once(ABSPATH . '/wp-includes/pluggable.php');

include_once('menu-manager-class.php');

// Add the installation and uninstallation hooks
register_activation_hook(__FILE__, array(MM_PLUGIN_NAME, 'install'));
register_deactivation_hook(__FILE__, array(MM_PLUGIN_NAME, 'uninstall'));

if (class_exists('Menu_Manager')) {
	Menu_Manager::bootstrap();
	
	function mm_menu()
	{
		Menu_Manager::get_menu_display();
	}
}

?>
