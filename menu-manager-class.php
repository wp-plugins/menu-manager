<?php
/**
 * This file is part of Menu Manager. Please see the menu-manager.php file for copyright
 *
 * @author Sulaeman
 * @version 1.0.1
 * @package menu-manager
 */

class Menu_Manager
{
	/**
     * Called automatically to register hooks and
     * add the actions and filters.
     *
     * @static
     * @access public
     */
	function bootstrap()
	{
		// Add the actions and filters
		add_action('admin_menu', array(MM_PLUGIN_NAME, 'admin_menu'));
		add_action('template_redirect', array(MM_PLUGIN_NAME, 'get_head_tags'));
	}
	
	function admin_menu()
	{
		// Add the options page
		add_options_page(MM_DISPLAY_NAME, MM_DISPLAY_NAME, 6, __FILE__, array(MM_PLUGIN_NAME, 'get_options_menu'));
	}
	
	function get_options_menu()
	{
		global $wpdb;
		
		if (isset($_POST['mm_save_options_page']) && $_POST['mm_save_options_page'] != '')
		{
			$selected_menu = '';
			if (is_array($_POST['page_main_menu']))
			{
				foreach($_POST['page_main_menu'] as $mn)
				{
					Menu_Manager::set_menu(0, 0, '', $mn, '', 'page', 0, TRUE);
					$selected_menu .= '"' . $mn . '",';
				}
				$selected_menu = substr($selected_menu, 0, (strlen($selected_menu) - 1));
			}
			Menu_Manager::delete_menus('', 'page', $selected_menu);
			
			$message = 'Menu Manager settings saved.';
		}
		
		if (isset($_POST['mm_save_options_category']) && $_POST['mm_save_options_category'] != '')
		{
			$selected_menu = '';
			if (is_array($_POST['cat_main_menu']))
			{
				foreach($_POST['cat_main_menu'] as $mn)
				{
					Menu_Manager::set_menu(0, 0, '', $mn, '', 'category', 0, TRUE);
					$selected_menu .= '"' . $mn . '",';
				}
				$selected_menu = substr($selected_menu, 0, (strlen($selected_menu) - 1));
			}
			Menu_Manager::delete_menus('', 'category', $selected_menu);
		}
		
		if (isset($_POST['mm_save_options_custom']) && $_POST['mm_save_options_custom'] != '')
		{
			if (is_array($_POST['cust_main_menu']['unid']))
			{
				$i_menu = 0;
				foreach($_POST['cust_main_menu']['unid'] as $mn)
				{
					if ($_POST['cust_main_menu']['name'][$i_menu] != '')
					{
						if ($mn == '')
						{
							$mn = 0;
						}
						
						if ($_POST['cust_main_menu']['delete'][$i_menu] == '1')
						{
							Menu_Manager::delete_menus($mn);
						}
						else
						{
							Menu_Manager::set_menu($mn, $_POST['cust_main_menu']['parent'][$i_menu], $_POST['cust_main_menu']['name'][$i_menu], $_POST['cust_main_menu']['url'][$i_menu], $_POST['cust_main_menu']['class'][$i_menu], 'custom', $i_menu);
						}
					}
					
					++$i_menu;
				}
			}
		}
		
		if (isset($_POST['mm_structure_save_options']) && $_POST['mm_structure_save_options'] != '')
		{
			if (is_array($_POST['main_menu']))
			{
				$i_menu = 0;
				foreach($_POST['main_menu']['unid'] as $mn)
				{
					$parent = ($_POST['main_menu']['parent'][$i_menu] != '') ? $_POST['main_menu']['parent'][$i_menu] : 0;
					$order = ($_POST['main_menu']['sort'][$i_menu] != '') ? $_POST['main_menu']['sort'][$i_menu] : 0;
					Menu_Manager::set_menu($mn, $parent, '', '', '', '', $order);
					++$i_menu;
				}
			}
			
			$message = 'Menu Manager Structure settings saved.';
		}
		
		$pages = get_pages();

		$post_categories = get_categories( array(
			'type' => 'post',
			'child_of' => 0,
			'hide_empty' => false
		) );

		$selected_pages = array();
		$selected_pages_full = array();
		$_pages = Menu_Manager::get_menus( '', 'page' );
		if ($_pages)
		{
			foreach($_pages as $pg)
			{
				$selected_pages[] = $pg->menu_value;
				$selected_pages_full[$pg->menu_value] = array(
					'unid' => $pg->menu_id,
					'value' => $pg->menu_value
				);
			}
		}

		$selected_categories = array();
		$selected_categories_full = array();
		$categories = Menu_Manager::get_menus( '', 'category' );
		if ($categories)
		{
			foreach($categories as $category)
			{
				$selected_categories[] = $category->menu_value;
				$selected_categories_full[$category->menu_value] = array(
					'unid' => $category->menu_id,
					'value' => $category->menu_value
				);
			}
		}

		$selected_custom_menu = array();
		$menus = Menu_Manager::get_menus( '', 'custom' );
		if ($menus)
		{
			foreach($menus as $mn)
			{
				$selected_custom_menu[] = array(
					'unid' => $mn->menu_id,
					'name' => $mn->menu_name,
					'url' => $mn->menu_value,
					'class' => $mn->menu_class
				);
			}
		}
		
		$menu_structure = array();
		Menu_Manager::get_menu_structure($menu_structure);
		$menu_structure = Menu_Manager::get_menu_broken_structure($menu_structure);
		
		// Start the cache
        ob_start();
		
		// Get the markup and display
        require(MM_DIR . '/display/mm-main.php');
        $options_form = ob_get_contents();
        ob_end_clean();
        echo $options_form;
	}
	
	function get_menu_display()
	{
		$menu_structure = array();
		Menu_Manager::get_menu_structure($menu_structure, 0);
		$menu_structure = Menu_Manager::get_menu_broken_structure($menu_structure);
		
		Menu_Manager::create_menu_item($menu_structure);
	}
	
	function has_submenu($menu_structure, $parent = 0){
		if (is_array($menu_structure) && isset($menu_structure[$parent]) && count($menu_structure[$parent]) > 0)
		{
			return TRUE;
		}
		
		return FALSE;
	}

	function create_menu_item($menu_structure, $parent = 0){
		$n_menu = count($menu_structure[$parent]);
		if (is_array($menu_structure) && isset($menu_structure[$parent]) && $n_menu > 0)
		{
			foreach($menu_structure[$parent] as $menu)
			{
				if ($menu->menu_type == 'page')
				{
					$p = get_page( $menu->menu_value );
					$unid = $menu->menu_value;
					$menu->menu_value = get_permalink( $menu->menu_value );
					$menu->menu_name = $p->post_title;
				}
				elseif ($menu->menu_type == 'category')
				{
					$category = get_category( $menu->menu_value );
					$unid = $menu->menu_value;
					$menu->menu_value = get_option('siteurl') . '/' . $category->taxonomy . '/' . $category->slug;
					$menu->menu_name = $category->cat_name;
				}
				?>
				<li class="page_item">	
				<a href="<?php echo $menu->menu_value; ?>" class="<?php echo $menu->menu_class; ?>">
				<?php echo $menu->menu_name; ?>
				</a>
				<?php if (Menu_Manager::has_submenu($menu_structure, $menu->menu_id)) : ?>
				<ul class="mm_sub_nav">
				<?php echo Menu_Manager::create_menu_item($menu_structure, $menu->menu_id); ?>
				</ul>
				<?php endif; ?>
				</li><?php
			}
		}
	}
	
	function set_menu($unid, $parent = 0, $name = '', $value = '', $class = '', $type = '', $order = 0, $check_value = FALSE)
	{
		global $wpdb;
		
		$exist = TRUE;
		
		$unid = (intval($unid) > 0) ? $unid : 0;
		
		if ($unid == 0)
		{
			$exist = FALSE;
			
			if ($check_value)
			{
				$sql = 'SELECT menu_id, menu_parent, menu_order FROM ' . MM_TABLE_NAME . ' ';
				$sql .= 'WHERE ';
				$sql .= 'menu_value = "' . $value . '" AND menu_type = "' . $type . '" LIMIT 1';
				$current_menu = $wpdb->get_row($sql, ARRAY_A);
				if (is_array($current_menu) && count($current_menu) > 0)
				{
					$unid = $current_menu['menu_id'];
					$parent = $current_menu['menu_parent'];
					$order = $current_menu['menu_order'];
					$exist = TRUE;
				}
			}
			
			if (!$exist)
			{
				$sql = 'INSERT INTO ' . MM_TABLE_NAME . ' ';
				$sql .= '(menu_parent, menu_name, menu_value, menu_type, menu_order)';
				$sql .= 'VALUES ';
				
				$sql .= '(' . $parent . ', "' . mysql_real_escape_string($name) . '", "' . mysql_real_escape_string($value) . '", "' . $type . '", ' . $order . ')';
			}
		}
		
		if ($exist)
		{
			$sql = 'UPDATE ' . MM_TABLE_NAME . ' ';
			$sql .= 'SET ';
			
			if (is_numeric($parent) || is_int($parent))
			{
				$sql .= 'menu_parent = ' . $parent . ', ';
			}
			
			if ($name != '')
			{
				$sql .= 'menu_name = "' . mysql_real_escape_string($name) . '", ';
			}
			
			if ($value != '')
			{
				$sql .= 'menu_value = "' . mysql_real_escape_string($value) . '", ';
			}
			
			if ($class != '')
			{
				$sql .= 'menu_class = "' . mysql_real_escape_string($class) . '", ';
			}
			
			if ($type != '')
			{
				$sql .= 'menu_type = "' . $type . '", ';
			}
			
			$sql .= 'menu_order = ' . $order . ' ';
			
			$sql .= 'WHERE ';
			$sql .= 'menu_id = ' . $unid;
		}
		
		$wpdb->query($sql);
	}

	function delete_menus($unid, $type = '', $selected_menu = '')
	{
		global $wpdb;
		
		$unid = (intval($unid) > 0) ? $unid : 0;
		
		$sql = 'DELETE FROM ' . MM_TABLE_NAME . ' ';
		$sql .= 'WHERE ';
		
		if ($unid != '')
		{
			$sql .= 'menu_id = ' . $unid;
		}
		
		if ($type != '')
		{
			$sql .= 'menu_type = "' . $type . '"';
		}
		
		if ($selected_menu != '')
		{
			$sql .= ' AND menu_value NOT IN (' . $selected_menu . ')';
		}
		
		$wpdb->query($sql);
	}

	function get_menus($unid = '', $type = '', $sort = FALSE)
	{
		global $wpdb;
		
		$sql = 'SELECT * FROM ' . MM_TABLE_NAME . ' ';
		
		if ($unid != '' || $type != '')
		{
			$sql .= 'WHERE ';
		}
		
		if ($unid != '')
		{
			$sql .= 'menu_id = ' . $unid . ' ';
		}
		
		if ($type != '')
		{
			$sql .= 'menu_type = "' . $type . '" ';
		}
		
		if ($sort)
		{
			$sql .= 'ORDER BY menu_order';
		}
		
		$menus = $wpdb->get_results($sql);
		if ($menus)
		{
			if ($unid != '')
			{
				return $menus[0];
			}
			else
			{
				return $menus;
			}
		}
		
		return FALSE;
	}
	
	function get_menu_structure(&$menu_structure, $parent = 0)
	{
		global $wpdb;
		
		$sql = 'SELECT * FROM ' . MM_TABLE_NAME . ' ';
		$sql .= 'WHERE ';
		$sql .= 'menu_parent = ' . $parent . ' ORDER BY menu_order';
		
		$menus = $wpdb->get_results($sql);
		if ($menus)
		{
			if (count($menus) > 0)
			{
				foreach($menus as $menu)
				{
					$menu_structure[$parent][] = $menu;
					
					Menu_Manager::get_menu_structure($menu_structure, $menu->menu_id);
				}
			}
		}
	}
	
	function get_menu_broken_structure($menu_structure)
	{
		global $wpdb;
		
		$temp_menu_structure = $menu_structure;
		sort($menu_structure);
		$search_menu_not_list = '';
		
		$search_menu = array();
		if (is_array($temp_menu_structure) && count($temp_menu_structure))
		{
			foreach($menu_structure as $menus)
			{
				foreach($menus as $menu)
				{
					$search_menu[] = $menu->menu_id;
				}
			}
			
			$search_menu_not_list = implode(',', $search_menu);
		}
		
		if ($search_menu_not_list != '')
		{
			$sql = 'SELECT * FROM ' . MM_TABLE_NAME . ' ';
			$sql .= 'WHERE ';
			$sql .= 'menu_id NOT IN (' . $search_menu_not_list . ') ORDER BY menu_order';
			
			$menus = $wpdb->get_results($sql);
			if ($menus)
			{
				if (count($menus) > 0)
				{
					foreach($menus as $menu)
					{
						$temp_menu_structure[0][] = $menu;
					}
				}
			}
		}
		
		return $temp_menu_structure;
	}
	
	/**
     * Gets the Menu Manager stylesheet, javascript. Loads it only on pages where needed, and
     *
     * @static
     * @access public
     */
    function get_head_tags()
	{
		wp_enqueue_style('superfish_css', MM_DISPLAY_URL . '/styles/superfish.css', false, MM_VERSION);
		if (file_exists(TEMPLATEPATH . '/styles/menu-manager.css'))
		{
			wp_enqueue_style('menu-manager_css', get_bloginfo('template_directory') . '/styles/menu-manager.css', false, MM_VERSION);
		}
		else
		{
			wp_enqueue_style('menu-manager_css', MM_DISPLAY_URL . '/styles/menu-manager.css', false, MM_VERSION);
		}
		wp_enqueue_script('jquery');
		wp_enqueue_script('superfish_js', MM_DISPLAY_URL . '/js/superfish.js', false, MM_VERSION);
		wp_enqueue_script('menu-manager_js', MM_DISPLAY_URL . '/js/menu-manager.js', false, MM_VERSION);
    }
	
	/**
     * Validates url addresses. Borrowed from
     * http://www.phpcentral.com/208-url-validation-php.html
     *
     * @static
     * @access public
     * @returns int|boolean 0 if not a valid email, 1 if it is valid, false on error
     */
	function check_url($url)
	{
		return preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url);
	}  
	
	function update_data()
	{
		global $wpdb;
		
		$feeds = $wpdb->get_results('SELECT meta_value FROM ' . $wpdb->postmeta . ' WHERE meta_key LIKE "%mm_%"', ARRAY_A);
		if (is_array($feeds) && count($feeds))
		{
			foreach($feeds as $feed)
			{
				add_post_meta( 0, 'mm', unserialize($feed['meta_value']) );
			}
			
			$wpdb->query('DELETE FROM ' . $wpdb->postmeta . ' WHERE meta_key LIKE "%mm_%"');
		}
	}
	
	function install()
	{
		global $wpdb;
		
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		
		if ( $wpdb->get_var('SHOW TABLES LIKE "' . MM_TABLE_NAME . '"') != MM_TABLE_NAME )
		{
			$sql = "CREATE TABLE IF NOT EXISTS `". MM_TABLE_NAME . "` (";
			$sql .= "`menu_id` bigint(20) unsigned NOT NULL auto_increment,";
			$sql .= "`menu_parent` bigint(20) NOT NULL default '0',";
			$sql .= "`menu_name` varchar(100) default NULL,";
			$sql .= "`menu_value` longtext NOT NULL,";
			$sql .= "`menu_class` varchar(100) default NULL,";
			$sql .= "`menu_type` enum('custom','category','page') NOT NULL,";
			$sql .= "`menu_order` int(2) NOT NULL default '0',";
			$sql .= "PRIMARY KEY  (`menu_id`,`menu_parent`)";
			$sql .= ") ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";
			
			dbDelta($sql);
		}
	}
	
	function uninstall()
	{
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		
		$sql = "DROP TABLE `". MM_TABLE_NAME . "`;";
		
		dbDelta($sql);
	}

    /**
     * array_walk callback method for trim()
     *
     * @static
     * @access private
     * @param string $string (required): the string to update
     * @param mixed $key (ignored): the array key of the string (not needed but passed automatically by array_walk)
     */
    function _trim(&$string, $key) {
        $string = trim($string);
    }
}

?>
