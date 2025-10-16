<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*====================================================
=            Create Options page and Menu            =
====================================================*/

class DP_AC_admin {
	public $this_menu;
	public $this_submenu;
	public $this_wp_admin_bar;
	public $DP_AC_super_admin;
	public function __construct(){
		add_filter( 'plugin_action_links_wp-admin-cleaner/wp-admin-cleaner.php', array($this, 'settings_link') );
		add_action( 'admin_init', array($this, 'register_table'), 1 );
		add_action( 'admin_init', array($this, 'add_capability'), 1 );
		add_action( 'admin_init', array($this, 'deactivate_DP_AC'), 100 );
		add_action( 'admin_bar_menu', array($this, 'admin_bar_action'), 100 );
		add_filter( 'menu_order', array($this, 'admin_menu_order'), 9999999999 );
		add_action( 'admin_menu', array($this, 'DP_AC_license_menu_page'), 1 );
		add_action( 'admin_menu', array($this, 'add_custom_menus'), 1 );
		add_action( 'admin_head', array($this, 'set_vars'), -1 );
		add_action( 'admin_head', array($this, 'admin_bar_logo'), 1 );
		add_action( 'wp_head', array($this, 'admin_bar_logo'), 1 );
		add_action( 'admin_head', array($this, 'disallow_admin_menus_submenus'), 1 );
		add_action( 'admin_head', array($this, 'remove_admin_menus_submenus'), 9999999999 );
		add_action( 'wp_before_admin_bar_render', array($this, 'remove_top_admin_bar'), 999 ); 
		add_action( 'admin_enqueue_scripts', array($this, 'enqueue_admin_script') );
		add_action( 'wp_ajax_save_admin_settings', array($this, 'save_admin_settings_func') );
		add_action( 'wp_ajax_DP_AC_save_roles', array($this, 'save_roles') );
		add_action( 'wp_ajax_DP_AC_save_custom_menus', array($this, 'save_custom_menus') );
		add_action( 'wp_ajax_DP_AC_save_custom_menus_delete', array($this, 'delete_custom_menus') );
		add_action( 'wp_ajax_DP_AC_save_DP_AC_disable_wpac', array($this, 'DP_AC_disable_wpac') );
		add_action( 'wp_ajax_DP_AC_save_DP_AC_reorder_force', array($this, 'DP_AC_reorder_force') );
		add_action( 'wp_ajax_DP_AC_save_DP_AC_remove_data', array($this, 'DP_AC_remove_data') );
		add_action( 'wp_ajax_DP_AC_save_DP_AC_super_admin', array($this, 'DP_AC_super_admin') );
		add_action( 'wp_ajax_DP_AC_save_themes', array($this, 'save_themes') );
		add_action( 'wp_ajax_DP_AC_save_adminLogin_options', array($this, 'save_adminLogin_options') );
		add_action( 'wp_ajax_DP_AC_save_redirection_options', array($this, 'save_redirection_options') );
		add_action( 'wp_ajax_DP_AC_save_cus_dashboard', array($this, 'save_cus_dashboard') );
		add_action( 'wp_ajax_DP_AC_loadRoleMenuSettings', array($this, 'loadRoleMenuSettings') );
		add_action( 'wp_ajax_DP_AC_loadRoleMenuOrder', array($this, 'loadRoleMenuOrder') );
		add_action( 'wp_ajax_DP_AC_save_reorder', array($this, 'save_reorder') );
		add_filter( 'custom_menu_order', function() { return true; } );
		add_filter( 'all_plugins', array($this, 'restrict_plugins'), 101, 1 );
		add_action( 'login_enqueue_scripts', array($this, 'this_login_scripts'), -1 );
		add_filter( 'login_redirect', array($this, 'login_redirect'), 101, 3 );
		if(!isset($_GET['bricks'])){
			add_filter( 'show_admin_bar', array($this, 'hide_admin_bar'), 101 );
		}
		add_filter( 'login_headerurl', array($this, 'login_headerURL'), 101 );

		// if DP_AC_cus_dashboard field has url then call hook to change dashboard page
		$DP_AC_cus_dashboard = get_option('DP_AC_cus_dashboard');
		if(!empty($DP_AC_cus_dashboard)){
			add_action( 'in_admin_header', array($this, 'custom_dashoard_page') );
			add_action( 'template_redirect', array($this, 'custom_dashoard_page_nonlogin_redirect') );
			add_action( 'wp_head', array($this, 'add_iframe_loaded_class') );
		}
    }

	public function register_table(){
		$DP_AC_table_created = get_option('DP_AC_tc');
		if($DP_AC_table_created != "yes"){
			global $wpdb;
			$charset_collate = $wpdb->get_charset_collate();
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

			$dp_ac_custom_menus = $wpdb->prefix . "dp_ac_custom_menus";
			$sql = "CREATE TABLE " . $dp_ac_custom_menus . " (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			name varchar(255) DEFAULT '' NOT NULL,
			dashicon varchar(255) DEFAULT '' NULL,
			svg TEXT DEFAULT '' NULL,
			link TEXT DEFAULT '' NULL,
			capability varchar(255) DEFAULT '' NOT NULL,
			priority bigint(20) DEFAULT '0' NULL,
			is_me_only bigint(20) DEFAULT '0' NULL,
			is_target_blank bigint(20) DEFAULT '0' NULL,
			user_id bigint(20) DEFAULT '0' NOT NULL,
			parent_id bigint(20) DEFAULT '0' NULL,
			PRIMARY KEY (id)
			) " . $charset_collate . ";";
			dbDelta($sql);
			update_option('DP_AC_tc', 'yes', 'no');
		}
	}

	public function get_custom_menu_parents($current_id = '', $selected_parent = ''){
		global $current_user, $wpdb;
		$selected = '';
		$parent_menus_html = '<option value="">--Select parent menu--</option>';
		$dp_ac_custom_menus = $wpdb->prefix . "dp_ac_custom_menus";
		$parent_menus = $wpdb->get_results("SELECT DISTINCT(id), name from ".$dp_ac_custom_menus." WHERE parent_id = '0' AND (is_me_only = '' OR user_id = '".$current_user->ID."')", OBJECT);
		if(!empty($parent_menus)){
			foreach($parent_menus as $parent_menu){
				if($current_id != "" && $current_id == $parent_menu->id) continue;
				if($parent_menu->id == $selected_parent){
					$selected = 'selected';
				} else {
					$selected = '';
				}
				$parent_menus_html .= '<option '.$selected.' value="'.$parent_menu->id.'">'.$parent_menu->name.'</option>';
			}
		}
		return $parent_menus_html;
	}

	public function get_all_capabilities() {
		global $wp_roles;
		if (!isset($wp_roles)) {
			$wp_roles = new WP_Roles();
		}

		$capabilities = [];

		foreach ($wp_roles->roles as $role) {
			foreach ($role['capabilities'] as $cap => $grant) {
				$capabilities[$cap] = $cap;
			}
		}

		return $capabilities;
	}

	public function get_custom_menus($parent_id = 0){
		global $current_user, $wpdb;
		$dp_ac_custom_menus = $wpdb->prefix . "dp_ac_custom_menus";
		$query = "SELECT * from ".$dp_ac_custom_menus." WHERE (is_me_only = '' OR user_id = '".$current_user->ID."') AND parent_id = '".$parent_id."'";
		$custom_menus = $wpdb->get_results($query, OBJECT);
		return $custom_menus;
	}

	public function create_slug($title) {
		// Convert the title to lowercase
		$slug = strtolower($title);
		// Replace non-letter or digits by hyphens
		$slug = preg_replace('~[^\pL\d]+~u', '-', $slug);
		// Remove unwanted characters
		$slug = preg_replace('~[^-\w]+~', '', $slug);
		// Trim hyphens from the beginning and end
		$slug = trim($slug, '-');
		// Remove duplicate hyphens
		$slug = preg_replace('~-+~', '-', $slug);
		// Return the slug
		return $slug;
	}

	public function add_parent_custom_menu($custom_menu, $dashicon = true){
		$slug = $this->create_slug($custom_menu->name);
		if($dashicon){
			add_menu_page(
				$custom_menu->name,         // Page title
				$custom_menu->name,         // Menu title
				$custom_menu->capability,        // Capability
				$slug, // Menu slug
				'',                      // Function to execute (empty in this case, as it's an external link)
				$custom_menu->dashicon  // Icon URL
			);
		} else {
			add_menu_page(
				$custom_menu->name,         // Page title
				$custom_menu->name,         // Menu title
				$custom_menu->capability,        // Capability
				$slug, // Menu slug
				'',                      // Function to execute (empty in this case, as it's an external link)
				'data:image/svg+xml;base64,' . base64_encode(wp_kses_post($custom_menu->svg)),  // SVG
				$custom_menu->priority // priority
			);
		}
		return $slug;
	}
	
	public function add_sub_custom_menu($custom_menu, $parent_slug){
		$slug = $this->create_slug($custom_menu->name);
		add_submenu_page(
			$parent_slug, // Parent slug
			$custom_menu->name,     // Page title
			$custom_menu->name,          // Menu title
			$custom_menu->capability,         // Capability
			$slug,    // Menu slug
			'' // Function to execute
		);
		return $slug;
	}

	public function add_custom_menus(){
		if(get_option('DP_AC_tc') == "yes"){
			$custom_menus = $this->get_custom_menus();
			if(!empty($custom_menus)){
				$links_data = array();
				$slug = '';
				$parent_slug = '';
				$i = 0;
				foreach($custom_menus as $custom_menu){
					if($custom_menu->dashicon != ""){
						$parent_slug = $this->add_parent_custom_menu($custom_menu, true);
					} else {
						$parent_slug = $this->add_parent_custom_menu($custom_menu, false);
					}
					$links_data[$i]['slug'] = $parent_slug;
					$links_data[$i]['link'] = $custom_menu->link;
					$links_data[$i]['target'] = ($custom_menu->is_target_blank == "1") ? "_blank" : '';
					$custom_menus_sub = $this->get_custom_menus($custom_menu->id);
					if(!empty($custom_menus_sub)){
						foreach($custom_menus_sub as $custom_menu_sub){
							$slug = $this->add_sub_custom_menu($custom_menu_sub, $parent_slug);
							$links_data[$i]['slug'] = $slug;
							$links_data[$i]['link'] = $custom_menu_sub->link;
							$links_data[$i]['target'] = ($custom_menu_sub->is_target_blank == "1") ? "_blank" : '';
						}
					}
				}
				// JavaScript for main menu redirection
				add_action('admin_footer', function() use ($links_data) {
					echo "<script type='text/javascript'>";
						foreach($links_data as $link){
							echo "document.querySelectorAll('a[href=\"{$link['slug']}\"]').forEach(function(element) {
								element.setAttribute('href', '{$link['link']}');
								element.setAttribute('target', '{$link['target']}'); // If you also want to open in a new tab
							});";
						}
					echo "</script>";
				}, 99999999);
			}
		}
	}

	public function get_custom_menus_html($custom_menus){
		global $current_user;
		$all_capabilities = $this->get_all_capabilities();
		$allData = '';
		if(!empty($custom_menus)){
			$this_menu = '';
			foreach($custom_menus as $custom_menu){
				$this_menu = $custom_menu;
				ob_start();
				include DP_AC_DIR . 'admin/includes/tabs/CustomMenuItem.php';
				$allData .= ob_get_clean();
				if($custom_menu->parent_id == "0"){
					$custom_sub_menus = $this->get_custom_menus($custom_menu->id);
					if(!empty($custom_sub_menus)){
						foreach($custom_sub_menus as $custom_sub_menu){
							$this_menu = $custom_sub_menu;
							ob_start();
							include DP_AC_DIR . 'admin/includes/tabs/CustomMenuItem.php';
							$allData .= ob_get_clean();
						}
					}
				}
			}
		}
		return $allData;
	}

	public function get_capabilities_html($selected_capability = '', $capabilities = array()){
		$capabilities_html = '';
		$selected = '';
		foreach($capabilities as $key => $val){
			if($selected_capability != ""){
				if($selected_capability == $key){
					$selected = 'selected';
				} else {
					$selected = '';
				}
			}
			$capabilities_html .= '<option value="'.$key.'" '.$selected.'>'.$val.'</option>';
		}
		return $capabilities_html;
	}

	public function check_if_user_has_wpac_activated(){
        global $current_user;
        $DP_AC_trigger_action = get_user_meta($current_user->ID, 'DP_AC_trigger_action', true);
        if($DP_AC_trigger_action == "no"){
            return true;
        } else {
            return false;
        }
    }

    public function check_if_admin_cleaner_turned_off_for_users(){
        global $current_user;
        if(in_array('administrator', $current_user->roles)){
            $DP_AC_disable_wpac = get_option('DP_AC_disable_wpac');
            if($DP_AC_disable_wpac == "yes"){
                if($current_user->ID != $this->DP_AC_super_admin){
                    return false;
                } else {
                    return true;
                }
            } else {
                return true;
            }
        } else {
            return true;
        }
    }

	public function set_vars(){
		global $menu, $submenu, $wp_admin_bar, $current_user;
		$this->this_menu = $menu;
		$this->this_submenu = $submenu;
		$this->this_wp_admin_bar = $wp_admin_bar;
	}

	public function admin_bar_logo(){
		$DP_AC_logo = get_option('DP_AC_logo');
		if(!empty($DP_AC_logo)){
			echo '<style type="text/css">
				#wpadminbar #wp-admin-bar-wp-logo > .ab-item .ab-icon:before {
					background-image: url('.esc_url($DP_AC_logo).') !important;
					background-position: 0 0;
					color:rgba(0, 0, 0, 0);
				}
				#wpadminbar #wp-admin-bar-wp-logo.hover > .ab-item .ab-icon {
					background-position: 0 0;
				}
			</style>';
		}
	}

	public function DP_AC_is_gutenberg_editor() {
		if( function_exists( 'is_gutenberg_page' ) && is_gutenberg_page() ) { 
			return true;
		}   
		
		$current_screen = get_current_screen();
		if ( method_exists( $current_screen, 'is_block_editor' ) && $current_screen->is_block_editor() ) {
			return true;
		}
		return false;
	}

	public function get_DP_AC_roles($check_for_allow_ac = false){
		global $current_user;
		$DP_AC_roles = get_option('DP_AC_roles');
		$DP_AC_roles = (empty($DP_AC_roles)) ? array() : $DP_AC_roles;

		if($check_for_allow_ac){
			global $wp_roles, $wpdb;
			$options_table = $wpdb->prefix . 'options';
			$all_roles = $wp_roles->roles;
			foreach($all_roles as $role_slug => $role){
				$option_id = $wpdb->get_var( "SELECT option_id FROM $options_table WHERE option_name LIKE '".$role_slug."_DP_AC%'" );
				if($option_id > 0){
					$DP_AC_roles[] = $role_slug;	
				}
			}
		}

		if(!in_array('administrator', $DP_AC_roles) && in_array('administrator', $current_user->roles)){
			$DP_AC_roles[] = 'administrator';
		}

		$DP_AC_roles = array_unique($DP_AC_roles);

		return $DP_AC_roles;
	}

	public function add_capability(){
		$DP_AC_roles = $this->get_DP_AC_roles();
		global $wp_roles;
		$all_roles = $wp_roles->roles;
		foreach($all_roles as $role_slug => $role){
			$role_object = get_role( $role_slug );
			if(in_array($role_slug, $DP_AC_roles)){
				$role_object->add_cap( 'DP_AC_cap' );
			} else {
				$role_object->remove_cap( 'DP_AC_cap' );
			}
		}
	}

	public function settings_link($links){
		// Build and escape the URL.
		global $current_user;
		$add_settings_link = false;
		if(in_array('administrator', $current_user->roles) && $current_user->ID != $this->DP_AC_super_admin){
			$DP_AC_disable_wpac = get_option('DP_AC_disable_wpac');
			$DP_AC_disable_wpac = (!empty($DP_AC_disable_wpac)) ? $DP_AC_disable_wpac : 'no';
			if($DP_AC_disable_wpac == "no"){
				$add_settings_link = true;
			}
		} else {
			$add_settings_link = true;
		}
		if($add_settings_link){
			$url = esc_url( add_query_arg(
				'page',
				'dplugins_admin_cleaner',
				get_admin_url() . 'tools.php'
			) );
			// Create the link.
			$settings_link = "<a href='$url'>" . __( 'Settings' ) . '</a>';
			// Adds the link to the end of the array.
			array_push(
				$links,
				$settings_link
			);
		}		
		return $links;
	}

	public function DP_AC_check_user_role_is_allowed(){
		global $current_user;
		$DP_AC_roles = $this->get_DP_AC_roles();
		$user_has_allowed_roles = array_intersect($current_user->roles, $DP_AC_roles);
		if(empty($user_has_allowed_roles)){
			return false;
		} else {
			return true;
		}
	}

    public function DP_AC_license_menu_page() {
		global $current_user;
		$this->DP_AC_super_admin = get_option('DP_AC_super_admin') ? get_option('DP_AC_super_admin') : $current_user->ID;
		$capability = 'DP_AC_cap';
		if(in_array('administrator', $current_user->roles)){
			$capability = 'manage_options';
		}
		if(in_array('administrator', $current_user->roles)){
			if($current_user->ID == $this->DP_AC_super_admin){
				add_submenu_page( 'tools.php', 'WP Admin Cleaner', 'WP Admin Cleaner', $capability, DPLUGINS_AC_ADMIN_SLUG, array($this, 'admin_page') );
			} else {
				$DP_AC_disable_wpac = get_option('DP_AC_disable_wpac');
				$DP_AC_disable_wpac = (!empty($DP_AC_disable_wpac)) ? $DP_AC_disable_wpac : 'no';
				if($DP_AC_disable_wpac == "no"){
					add_submenu_page( 'tools.php', 'WP Admin Cleaner', 'WP Admin Cleaner', $capability, DPLUGINS_AC_ADMIN_SLUG, array($this, 'admin_page') );
				}
			}
		} else {
			$user_has_allowed_roles = $this->DP_AC_check_user_role_is_allowed();
			// if user role is not allowed to use this
			if(empty($user_has_allowed_roles)){
				return;
			}

			add_submenu_page( 'tools.php', 'WP Admin Cleaner', 'WP Admin Cleaner', $capability, DPLUGINS_AC_ADMIN_SLUG, array($this, 'admin_page') );
		}
	}

	/*======================================================
	=            Plugin Admin Scripts and Pages            =
	======================================================*/

	public function enqueue_admin_script() {
		$screen = get_current_screen();
		//echo $screen->id; exit;
		$DP_AC_scripts = array(
			'tools_page_dplugins_admin_cleaner',
		);
		if(in_array($screen->id, $DP_AC_scripts)){
			wp_enqueue_media();
			wp_enqueue_style( 'wp-color-picker' );
			/* code mirror */			
			wp_enqueue_script('wp-theme-plugin-editor');
			wp_enqueue_style('wp-codemirror');
			/* code mirror */
			
			wp_enqueue_style( 'DP_AC_admin_css', plugin_dir_url( __FILE__ ) . 'css/admin.css', DP_AC_PLUGINVERSION);
			wp_enqueue_style( 'DP_AC_jquery_ui', plugin_dir_url( __FILE__ ) . 'css/jquery-ui.css', DP_AC_PLUGINVERSION);

			$DP_AC_editor_settings['codeEditor'] = wp_enqueue_code_editor(array('type' => 'text/css'));
			wp_enqueue_script( "DP_AC_jquery_ui", plugin_dir_url( __FILE__ ).'js/jquery-ui.min.js', array('jquery'), DP_AC_PLUGINVERSION );
			wp_enqueue_script( "DP_AC_color_picker_alpha", plugin_dir_url( __FILE__ ).'js/wp-color-picker-alpha.min.js', array('wp-color-picker'), DP_AC_PLUGINVERSION );
			wp_register_script( "DP_AC_ajax_scripts", plugin_dir_url( __FILE__ ).'js/admin-min.js', array('jquery', 'wp-color-picker'), DP_AC_PLUGINVERSION );
		   	wp_localize_script( 'DP_AC_ajax_scripts', 'DP_AC_ajax', array( 
				'ajaxurl' => admin_url( 'admin-ajax.php' ), 
				'siteurl' => site_url(), 
				'DP_AC_nonce' => wp_create_nonce('ajax-nonce'),
				'DP_AC_editor_settings' => $DP_AC_editor_settings,
			));
		   	wp_enqueue_script( 'DP_AC_ajax_scripts' );
		}

		/* load theme files for wp admin and gutenberg */
		global $current_user;
		$DP_AC_global_css = get_option('DP_AC_global_css');
		$DP_AC_inject_global = get_option('DP_AC_inject_global');
		$DP_AC_gutenberg 		= get_user_meta($current_user->ID, 'DP_AC_gutenberg', true);
		$DP_AC_gutenberg_Oxygen = get_user_meta($current_user->ID, 'DP_AC_gutenberg_Oxygen', true);
		$DP_AC_wp_admin 		= get_user_meta($current_user->ID, 'DP_AC_wp_admin', true);
		$DP_AC_inject_css 		= get_user_meta($current_user->ID, 'DP_AC_inject_css', true);
		
		if(strpos($screen->id, 'acf') === false && strpos($screen->id, 'scorg') === false){
			if($DP_AC_gutenberg == "yes"){
				if($this->DP_AC_is_gutenberg_editor()){
					wp_enqueue_style( 'DP_AC_gutenberg', plugin_dir_url( __FILE__ ) . 'css/wpcleaner-gutenberg.css');
				}
			}
			if($DP_AC_gutenberg_Oxygen == "yes"){
				if($this->DP_AC_is_gutenberg_editor()){
					wp_enqueue_style( 'DP_AC_gutenberg_Oxygen', plugin_dir_url( __FILE__ ) . 'css/wpcleaner-gutenberg-oxygen.css');
				}
			}
			if($DP_AC_wp_admin == "yes" && !$this->DP_AC_is_gutenberg_editor()){
				wp_enqueue_style( 'DP_AC_wp_admin', plugin_dir_url( __FILE__ ) . 'css/wpcleaner-admin.css');
			}
		}
		
		if($DP_AC_inject_css == "yes"){
			$upload_dir = wp_upload_dir();
			$filepath = $upload_dir['basedir'].'/wp-admin-cleaner'."/DP_AC-".$current_user->ID.".css";
			if(file_exists($filepath)){
				$file_url = $upload_dir['baseurl'].'/wp-admin-cleaner'.'/DP_AC-'.$current_user->ID.'.css';
				wp_enqueue_style('DP_AC-css', $file_url, array(), time());
			}
		}
		if($DP_AC_inject_global == "yes" && !empty($DP_AC_global_css)){
			$upload_dir = wp_upload_dir();
			$filepath = $upload_dir['basedir'].'/wp-admin-cleaner'."/DP_AC-admin-css.css";
			if(file_exists($filepath)){
				$file_url = $upload_dir['baseurl'].'/wp-admin-cleaner'.'/DP_AC-admin-css.css';
				wp_enqueue_style('DP_AC-admin-css', $file_url, array(), time());
			}
		}
	}

	/*====================================
	=            Licence Form            =
	====================================*/
	public function admin_page() {
		global $current_user;
		$license = get_option( 'DP_AC_license_key' );
		$status  = get_option( 'DP_AC_license_status' );
		//$status = "valid";
		$license_active_tab = "";
		$adminLogin_active_tab = "";
		$redirection_active_tab = "";
		$admin_plugins_active_tab = "";
		$rb_settings_active_tab = "";
		$mopr_settings_active_tab = "";
		$settings_active_tab = "";
		$license_style = "";
		$adminLogin_style = "";
		$redirection_style = "";
		$admin_plugins_style = "";
		$rb_settings_style = "";
		$mopr_settings_style = "";
		$settings_style = "";
		$admin_plugins_style = "";
		$activate_license_message = '';
		$adminLogin_message = '';
		$redirection_message = '';
		$rb_settings_message = '';
		$mopr_settings_message = '';
		$admin_plugins_message = '';
		$user_roles_message = '';
		$theme_message = '';
		$reorder_message = '';
		?>
		<div class="success-message notice notice-success inline" style="display:none;"><p>Options Saved!</p></div>
		<div class="error-message notice notice-error inline" style="display:none;"><p>There is some error!</p></div>
		<div class="wrap">
			<?php require DP_AC_DIR . 'admin/includes/Header.php'; ?>
			<?php require DP_AC_DIR . 'admin/includes/tabs/AdminSettings.php'; ?>
			<?php require DP_AC_DIR . 'admin/includes/tabs/UserRoles.php'; ?>
			<?php require DP_AC_DIR . 'admin/includes/tabs/HidePerUserRoles.php'; ?>
			<?php require DP_AC_DIR . 'admin/includes/tabs/ReOrderPerUserRoles.php'; ?>
			<?php require DP_AC_DIR . 'admin/includes/tabs/HidePlugins.php'; ?>
			<?php require DP_AC_DIR . 'admin/includes/tabs/AdminLogin.php'; ?>
			<?php require DP_AC_DIR . 'admin/includes/tabs/Redirection.php'; ?>
			<?php require DP_AC_DIR . 'admin/includes/tabs/CustomDashboard.php'; ?>
			<?php require DP_AC_DIR . 'admin/includes/tabs/Theme.php'; ?>
			<?php require DP_AC_DIR . 'admin/includes/tabs/ReOrder.php'; ?>
			<?php require DP_AC_DIR . 'admin/includes/tabs/License.php'; ?>
			<?php require DP_AC_DIR . 'admin/includes/tabs/CustomMenus.php'; ?>
		<?php
	}

	public function admin_menus_options(){
		global $current_user;
		$menu = $this->this_menu;
		$submenu = $this->this_submenu;
		$wp_admin_bar = $this->this_wp_admin_bar;
		$adminBar = $wp_admin_bar;
		$reflector = new ReflectionObject($adminBar);
		$nodes = $reflector->getProperty('nodes');
		$nodes->setAccessible(true);
		$bars = $nodes->getValue($adminBar);
		$html = '';
		$html .= '<div class="select-all">
			<label class="of-input">
				<input type="checkbox" class="of-checkboxes select-all"> Select/Unselect All
			</label>
		</div>';
		$is_super_admin = false;
		if(in_array('administrator', $current_user->roles) && $current_user->ID == $this->DP_AC_super_admin){
			$is_super_admin = true;
		}
		
		$DP_AC_t_to_hide = get_user_meta($current_user->ID, 'DP_AC_t_to_hide', true);
		$DP_AC_m_to_hide = get_user_meta($current_user->ID, 'DP_AC_m_to_hide', true);
		$DP_AC_sm_to_hide = get_user_meta($current_user->ID, 'DP_AC_sm_to_hide', true);
		$DP_AC_top_menus_to_hide = (!empty($DP_AC_t_to_hide)) ? unserialize($DP_AC_t_to_hide) : array();
		$DP_AC_menus_to_hide = (!empty($DP_AC_m_to_hide)) ? unserialize($DP_AC_m_to_hide) : array();
		$DP_AC_submenus_to_hide = (!empty($DP_AC_sm_to_hide)) ? unserialize($DP_AC_sm_to_hide) : array();

		$this_user_DP_AC_menus_to_hide = array();
		$this_user_DP_AC_submenus_to_hide = array();
		$this_user_DP_AC_topmenus_to_hide = array();
		if(!$is_super_admin){
			foreach($current_user->roles as $role){
				$this_user_DP_AC_t_to_hide = get_option($role.'_DP_AC_t_to_hide');
				$this_user_DP_AC_m_to_hide = get_option($role.'_DP_AC_m_to_hide');
				$this_user_DP_AC_sm_to_hide = get_option($role.'_DP_AC_sm_to_hide');
				$this_user_DP_AC_topmenus_to_hide = (!empty($this_user_DP_AC_t_to_hide)) ? unserialize($this_user_DP_AC_t_to_hide) : array();
				$this_user_DP_AC_menus_to_hide = (!empty($this_user_DP_AC_m_to_hide)) ? unserialize($this_user_DP_AC_m_to_hide) : array();
				$this_user_DP_AC_submenus_to_hide = (!empty($this_user_DP_AC_sm_to_hide)) ? unserialize($this_user_DP_AC_sm_to_hide) : array();
				break;
			}
		}

		$html .= '<div class="DP_AC_admin_header toggle-header">
			<h2>Left Bar</h2>
			<div class="toggle-trigger section opened"></div>
		</div>
		<div class="main-menus-box">';
		/* left menus */
		foreach($menu as $key => $main_menu){
			if(!empty($main_menu[0])){
				if(!empty($this_user_DP_AC_menus_to_hide) && in_array($main_menu[5], $this_user_DP_AC_menus_to_hide)) continue;
				$checked = (!empty($DP_AC_menus_to_hide) && in_array($main_menu[5], $DP_AC_menus_to_hide)) ? 'checked' : '';
				$html .= '<div class="main-menu ">
				<label class="of-input"><input '.$checked.' type="checkbox" class="of-checkboxes main-menus" name="main_menus" value="'.$main_menu[5].'"> '.$main_menu[0].'</label>
				';
				if(isset($submenu[$main_menu[2]])){
					$html .= '<div class="toggle-trigger"></div> <div class="sub-menu" style="display:none">';
					foreach($submenu[$main_menu[2]] as $child_key => $sub_menu){
						//echo "<pre>"; print_r($submenu); "</pre>";
						if(!empty($this_user_DP_AC_submenus_to_hide) && isset($this_user_DP_AC_submenus_to_hide[$main_menu[2]]) && in_array($child_key, $this_user_DP_AC_submenus_to_hide[$main_menu[2]])) continue;
						$checked = (!empty($DP_AC_submenus_to_hide) && isset($DP_AC_submenus_to_hide[$main_menu[2]]) && in_array($child_key, $DP_AC_submenus_to_hide[$main_menu[2]])) ? 'checked' : '';
						$html .= '<label class="of-input"><input '.$checked.' type="checkbox" class="of-checkboxes sub-menus" name="sub_menus" value="'.$child_key.'" data-parent="'.$main_menu[2].'"> '.$sub_menu[0].'</label>';
					}
					$html .= '</div>';
				}
				$html .= '</div>';
			}
		}

		$html .= '</div>
		<div class="DP_AC_admin_header toggle-header">
			<h2>Top Bar <span style="font-weight:300; color: #b7b7b7;">(Backend)</span></h2>
			<div class="toggle-trigger section"></div>
		</div>
		<div class="main-menus-box" style="display:none;">';

		/* top menus */
		foreach($bars as $bar){
			if($bar->id != "root"){
				continue;
			}
			if(!empty($bar->children)){
				foreach($bar->children as $sub_bar1){
					if(!empty($this_user_DP_AC_topmenus_to_hide) && in_array($sub_bar1->id, $this_user_DP_AC_topmenus_to_hide)) continue;
					$checked = (!empty($DP_AC_top_menus_to_hide) && in_array($sub_bar1->id, $DP_AC_top_menus_to_hide)) ? 'checked' : '';
					if($sub_bar1->id == "root-default"){
						$sub_bar1->title = "Root Default";
					}
					if($sub_bar1->id == "top-secondary"){
						$sub_bar1->title = "Top Secondary";
					}
					$html .= '<div class="main-menu">
					<label class="of-input"><input '.$checked.' type="checkbox" class="of-checkboxes main-menus top" name="top_menus" value="'.$sub_bar1->id.'"> '.((!empty($sub_bar1->title)) ? strip_tags($sub_bar1->title) : $sub_bar1->id).'</label>
					';
					if(!empty($sub_bar1->children)){
						$html .= '<div class="toggle-trigger"></div> <div class="sub-menu" style="display:none">';
						foreach($sub_bar1->children as $sub_bar2){
							if($sub_bar2->type == "item" && $sub_bar2->title != ""){
								if(!empty($this_user_DP_AC_topmenus_to_hide) && in_array($sub_bar2->id, $this_user_DP_AC_topmenus_to_hide)) continue;
								$checked = (!empty($DP_AC_top_menus_to_hide) && in_array($sub_bar2->id, $DP_AC_top_menus_to_hide)) ? 'checked' : '';
								$html .= '<label class="of-input"><input '.$checked.' type="checkbox" class="of-checkboxes sub-menus top" name="top_menus" value="'.$sub_bar2->id.'" data-parent="'.$sub_bar1->id.'"> '.((!empty($sub_bar2->title)) ? strip_tags($sub_bar2->title) : $sub_bar2->id).'</label>';	
							}
							if(!empty($sub_bar2->children)){
								foreach($sub_bar2->children as $sub_bar3){
									if($sub_bar3->title != ""){
										if(!empty($this_user_DP_AC_topmenus_to_hide) && in_array($sub_bar3->id, $this_user_DP_AC_topmenus_to_hide)) continue;
										$checked = (!empty($DP_AC_top_menus_to_hide) && in_array($sub_bar3->id, $DP_AC_top_menus_to_hide)) ? 'checked' : '';
										$html .= '<label class="of-input sub-sub-menu"><input '.$checked.' type="checkbox" class="of-checkboxes sub-menus top" name="top_menus" value="'.$sub_bar3->id.'" data-parent="'.$sub_bar1->id.'"> '.((!empty($sub_bar3->title)) ? strip_tags($sub_bar3->title) : $sub_bar3->id).'</label>';
									}
									if(!empty($sub_bar3->children)){
										foreach($sub_bar3->children as $sub_bar4){
											if($sub_bar4->type == "item" && $sub_bar4->title != "" && strpos($sub_bar4->id, 'scorg') === false && strpos($sub_bar4->id, 'scss') === false && strpos($sub_bar4->id, 'dplugin') === false){
												if(!empty($this_user_DP_AC_topmenus_to_hide) && in_array($sub_bar4->id, $this_user_DP_AC_topmenus_to_hide)) continue;
												$checked = (!empty($DP_AC_top_menus_to_hide) && in_array($sub_bar4->id, $DP_AC_top_menus_to_hide)) ? 'checked' : '';
												$html .= '<label class="of-input sub-sub-sub-menu"><input '.$checked.' type="checkbox" class="of-checkboxes sub-menus top" name="top_menus" value="'.$sub_bar4->id.'" data-parent="'.$sub_bar1->id.'"> '.((!empty($sub_bar4->title)) ? strip_tags($sub_bar4->title) : $sub_bar4->id).'</label>';
											}
										}
									}
								}
							}
						}
						$html .= '</div>';
					}
					$html .= '</div>';
				}
			}
		}
		$html .= '</div>';

		$DP_AC_tf_menus = get_option('DP_AC_tf_menus');
		$DP_AC_tf_menus = (!empty($DP_AC_tf_menus)) ? $DP_AC_tf_menus : array();
		$DP_AC_tf_to_hide = get_user_meta($current_user->ID, 'DP_AC_tf_to_hide', true);
		$DP_AC_top_menus_frontend_to_hide = (!empty($DP_AC_tf_to_hide)) ? unserialize($DP_AC_tf_to_hide) : array();
		$this_user_DP_AC_topmenus_frontend_to_hide = array();
		if(!$is_super_admin){
			foreach($current_user->roles as $role){
				$this_user_DP_AC_tf_to_hide = get_option($role.'_DP_AC_tf_to_hide');
				$this_user_DP_AC_topmenus_frontend_to_hide = (!empty($this_user_DP_AC_tf_to_hide)) ? unserialize($this_user_DP_AC_tf_to_hide) : array();
				break;
			}
		}

		$checked = "";
		$html .= '<div class="DP_AC_admin_header toggle-header tbf">			
			<div class="title-with-btn"> 
				<h2>Top Bar <span style="font-weight:300; color: #b7b7b7;">(Frontend)</span></h2>
				<a href="#" class="button-secondary refresh-topbar-frontend">Refresh</a>								
			</div>
			<div class="toggle-trigger section"></div>
			<iframe class="refresh-topbar" style="display:none;"></iframe>
		</div>
		<div class="main-menus-box" style="display:none;">';
		if(!empty($DP_AC_tf_menus)){
			foreach($DP_AC_tf_menus as $id => $title){
				if(!empty($this_user_DP_AC_topmenus_frontend_to_hide) && in_array($id, $this_user_DP_AC_topmenus_frontend_to_hide)) continue;
				$checked = (!empty($DP_AC_top_menus_frontend_to_hide) && in_array($id, $DP_AC_top_menus_frontend_to_hide)) ? 'checked' : '';
				$html .= '<div class="main-menu ">
					<label class="of-input">
						<input '.$checked.' type="checkbox" class="of-checkboxes main-menus topfrontend" name="top_menus_frontend" value="'.$id.'"> '.$title.'
					</label>
				</div>';
			}
		}
		$html .= '</div>';
		
		$html .= '<a href="#" id="save_admin_settings" data-tabid="admin_settings" class="of-save button-primary" style="margin-top: 15px;">Save Changes <span class="spinner"></span></a>';

		$html .= '<div class="notice notice-info is-dismissible inline" style="margin-top: 30px;"><p>All admin menu items are always visible on this page. To view changes, navigate away from this WP Admin Cleaner settings page.</p></div>';

		return $html;
	}

	public function admin_plugins(){
		$html = '';
		$html .= '<div class="select-all">
			<label class="of-input">
				<input type="checkbox" class="of-checkboxes select-all"> Select/Unselect All
			</label>
		</div>';
		$all_plugins = get_plugins();
		$DP_AC_restricted_plugins = get_option('DP_AC_rp');
		$DP_AC_restricted_plugins = (!empty($DP_AC_restricted_plugins)) ? unserialize($DP_AC_restricted_plugins) : array();
		if(empty($DP_AC_restricted_plugins)){
			$DP_AC_restricted_plugins = array();
		}
		$checked = '';
		foreach($all_plugins as $key => $plugin){
			$checked = (!empty($DP_AC_restricted_plugins) && in_array($key, $DP_AC_restricted_plugins)) ? 'checked' : '';
			$html .= '<div class="main-menu ">
				<label class="of-input"><input '.$checked.' type="checkbox" class="of-checkboxes main-menus" name="main_menus" value="'.$key.'"> '.$plugin['Name'].'</label>
				';
			$html .= '</div>';
		}
		$html .= '<a href="#" data-tabid="admin_plugins" class="of-save button-primary" style="margin-top: 15px;">Save Changes <span class="spinner"></span></a>';
		return $html;
	}

	public function user_roles_option(){
		global $wp_roles;
    	$all_roles = $wp_roles->roles;
		$DP_AC_roles = $this->get_DP_AC_roles();

		$html = '';
		foreach($all_roles as $role_slug => $role){
			if($role_slug == "administrator"){ continue; }
			$html .= '<div class="dp__switch">    
				<label>
					<input type="checkbox" value="'.$role_slug.'" '.((!empty($DP_AC_roles) && in_array($role_slug, $DP_AC_roles)) ? 'checked' : '').' />
					<div class="rwmb-switch-status">
						<span class="rwmb-switch-slider"></span>
						<span class="rwmb-switch-on">On</span>
						<span class="rwmb-switch-off"></span>
					</div>
					<div class="role-name">'.$role['name'].'</div>
				</label>
			</div>';
		}
		$html .= '<a href="#" class="of-save-roles button-primary" style="margin-top: 15px;">Save Changes <span class="spinner"></span></a>';

		return $html;
	}

	public function rb_settings_menus_options(){
		global $current_user;
		$menu = $this->this_menu;
		$submenu = $this->this_submenu;
		$wp_admin_bar = $this->this_wp_admin_bar;
		
		$adminBar = $wp_admin_bar;
		$reflector = new ReflectionObject($adminBar);
		$nodes = $reflector->getProperty('nodes');
		$nodes->setAccessible(true);
		$bars = $nodes->getValue($adminBar);
		$html = '';
		$html .= '<div id="rb_settings_options" style="display:none;"><div class="select-all">
			<label class="of-input">
				<input type="checkbox" class="of-checkboxes select-all"> Select/Unselect All
			</label>
		</div>';
		$is_super_admin = false;
		if(in_array('administrator', $current_user->roles) && $current_user->ID == $this->DP_AC_super_admin){
			$is_super_admin = true;
		}

		$DP_AC_t_to_hide = '';
		$DP_AC_m_to_hide = '';
		$DP_AC_sm_to_hide = '';
		$DP_AC_top_menus_to_hide = (!empty($DP_AC_t_to_hide)) ? unserialize($DP_AC_t_to_hide) : array();
		$DP_AC_menus_to_hide = (!empty($DP_AC_m_to_hide)) ? unserialize($DP_AC_m_to_hide) : array();
		$DP_AC_submenus_to_hide = (!empty($DP_AC_sm_to_hide)) ? unserialize($DP_AC_sm_to_hide) : array();

		$html .= '<div class="DP_AC_admin_header toggle-header">
			<h2>Left Bar</h2>
			<div class="toggle-trigger section opened"></div>
		</div>
		<div class="main-menus-box">';
		/* left menus */
		foreach($menu as $key => $main_menu){
			if(!empty($main_menu[0])){
				$checked = (!empty($DP_AC_menus_to_hide) && in_array($main_menu[5], $DP_AC_menus_to_hide)) ? 'checked' : '';
				$html .= '<div class="main-menu ">
				<label class="of-input"><input '.$checked.' type="checkbox" class="of-checkboxes main-menus" name="main_menus" value="'.$main_menu[5].'"> '.$main_menu[0].'</label>
				';
				if(isset($submenu[$main_menu[2]])){
					$html .= '<div class="toggle-trigger"></div> <div class="sub-menu" style="display:none">';
					foreach($submenu[$main_menu[2]] as $child_key => $sub_menu){
						//if($sub_menu[0] == "WP Admin Cleaner") continue;
						//echo "<pre>"; print_r($submenu); "</pre>";
						$checked = (!empty($DP_AC_submenus_to_hide) && isset($DP_AC_submenus_to_hide[$main_menu[2]]) && in_array($child_key, $DP_AC_submenus_to_hide[$main_menu[2]])) ? 'checked' : '';
						$html .= '<label class="of-input"><input '.$checked.' type="checkbox" class="of-checkboxes sub-menus" name="sub_menus" value="'.$child_key.'" data-parent="'.$main_menu[2].'"> '.$sub_menu[0].'</label>';
					}
					$html .= '</div>';
				}
				$html .= '</div>';
			}
		}
		$checked = "";

		$html .= '</div>
		<div class="DP_AC_admin_header toggle-header">
			<h2>Top Bar <span style="font-weight:300; color: #b7b7b7;">(Backend)</span></h2>
			<div class="toggle-trigger section"></div>
		</div>
		<div class="main-menus-box" style="display:none;">';
		/* top menus */
		foreach($bars as $bar){
			if($bar->id != "root"){
				continue;
			}
			if(!empty($bar->children)){
				foreach($bar->children as $sub_bar1){
					$checked = (!empty($DP_AC_top_menus_to_hide) && in_array($sub_bar1->id, $DP_AC_top_menus_to_hide)) ? 'checked' : '';
					if($sub_bar1->id == "root-default"){
						$sub_bar1->title = "Root Default";
					}
					if($sub_bar1->id == "top-secondary"){
						$sub_bar1->title = "Top Secondary";
					}
					$html .= '<div class="main-menu topmain">
					<label class="of-input"><input '.$checked.' type="checkbox" class="of-checkboxes main-menus top" name="top_menus" value="'.$sub_bar1->id.'"> '.((!empty($sub_bar1->title)) ? strip_tags($sub_bar1->title) : $sub_bar1->id).'</label>
					';
					if(!empty($sub_bar1->children)){
						$html .= '<div class="toggle-trigger"></div> <div class="sub-menu" style="display:none">';
						foreach($sub_bar1->children as $sub_bar2){
							if($sub_bar2->type == "item" && $sub_bar2->title != ""){
								$checked = (!empty($DP_AC_top_menus_to_hide) && in_array($sub_bar2->id, $DP_AC_top_menus_to_hide)) ? 'checked' : '';
								$html .= '<label class="of-input"><input '.$checked.' type="checkbox" class="of-checkboxes sub-menus top" name="top_menus" value="'.$sub_bar2->id.'" data-parent="'.$sub_bar1->id.'"> '.((!empty($sub_bar2->title)) ? strip_tags($sub_bar2->title) : $sub_bar2->id).'</label>';	
							}
							if(!empty($sub_bar2->children)){
								foreach($sub_bar2->children as $sub_bar3){
									if($sub_bar3->title != ""){
										$checked = (!empty($DP_AC_top_menus_to_hide) && in_array($sub_bar3->id, $DP_AC_top_menus_to_hide)) ? 'checked' : '';
										$html .= '<label class="of-input sub-sub-menu"><input '.$checked.' type="checkbox" class="of-checkboxes sub-menus top" name="top_menus" value="'.$sub_bar3->id.'" data-parent="'.$sub_bar1->id.'"> '.((!empty($sub_bar3->title)) ? strip_tags($sub_bar3->title) : $sub_bar3->id).'</label>';
									}
									if(!empty($sub_bar3->children)){
										foreach($sub_bar3->children as $sub_bar4){
											if($sub_bar4->type == "item" && $sub_bar4->title != "" && strpos($sub_bar4->id, 'scorg') === false && strpos($sub_bar4->id, 'scss') === false && strpos($sub_bar4->id, 'dplugin') === false){
												$checked = (!empty($DP_AC_top_menus_to_hide) && in_array($sub_bar4->id, $DP_AC_top_menus_to_hide)) ? 'checked' : '';
												$html .= '<label class="of-input sub-sub-menu sub-sub-sub-menu"><input '.$checked.' type="checkbox" class="of-checkboxes sub-menus top" name="top_menus" value="'.$sub_bar4->id.'" data-parent="'.$sub_bar1->id.'"> '.((!empty($sub_bar4->title)) ? strip_tags($sub_bar4->title) : $sub_bar4->id).'</label>';
											}
										}
									}
								}
							}
						}
						$html .= '</div>';
					}
					$html .= '</div>';
				}
			}
		}
		$html .= '</div>';

		$DP_AC_tf_menus = get_option('DP_AC_tf_menus');
		$DP_AC_tf_menus = (!empty($DP_AC_tf_menus)) ? $DP_AC_tf_menus : array();
		$DP_AC_tf_to_hide = '';
		$DP_AC_top_menus_frontend_to_hide = (!empty($DP_AC_tf_to_hide)) ? unserialize($DP_AC_tf_to_hide) : array();

		$checked = "";
		$html .= '<div class="DP_AC_admin_header toggle-header tbf">
			
			<div class="title-with-btn"> 
				<h2>Top Bar <span style="font-weight:300; color: #b7b7b7;">(Frontend)</span></h2>
				<a href="#" class="button-secondary refresh-topbar-frontend">Refresh</a>						
			</div>
			<div class="toggle-trigger section"></div>		
			<iframe class="refresh-topbar" style="display:none;"></iframe>
		</div>
		<div class="main-menus-box" style="display:none;">';
		if(!empty($DP_AC_tf_menus)){
			foreach($DP_AC_tf_menus as $id => $title){
				$checked = (!empty($DP_AC_top_menus_frontend_to_hide) && in_array($id, $DP_AC_top_menus_frontend_to_hide)) ? 'checked' : '';
				$html .= '<div class="main-menu topfrontend-main">
					<label class="of-input">
						<input '.$checked.' type="checkbox" class="of-checkboxes main-menus topfrontend" name="top_menus_frontend" value="'.$id.'"> '.$title.'
					</label>
				</div>';
			}
		}
		$html .= '</div>';
		
		$html .= '<a href="#" data-tabid="rb_settings" class="of-save button-primary" style="margin-top: 15px;">Save Changes <span class="spinner"></span></a>';
		$html .= '<div class="notice notice-info is-dismissible inline" style="margin-top: 30px;"><p>All admin menu items are always visible on this page. To view changes, navigate away from this WP Admin Cleaner settings page.</p></div></div>';
	
		return $html;
	}
	
	public function mopr_settings_reorder_menus(){
		$html = '';
		global $menu;
		$html .= '<div id="mopr_settings_options" style="display:none;"><ul class="reorder-admin-menus" id="reorder-admin-menus">';
		foreach($menu as $key => $main_menu){
			if(!empty($main_menu[0])){
				$html .= '<li><span class="move"></span>
					<div '.((isset($main_menu[6]) && strpos($main_menu[6], 'dashicons') !== false) ? 'class="menu-name dashicons-before '.$main_menu[6].'"' : 'class="menu-name wp-menu-image svg" style="background-image:url('.$main_menu[6].'); background-repeat:no-repeat;	"').'>'.$main_menu[0].'</div>
					<input type="hidden" name="WPAC_menu_order[]" value="'.$main_menu[2].'" />
				</li>';
			}
		}
		$html .= '</ul>';
		$html .= '<div class="reorder-buttons"><a href="#" data-tabid="mopr_settings" class="of-save-reorder button-primary" style="margin: 15px;">Save Changes <span class="spinner"></span></a>';
		$html .= '<a href="#" data-tabid="mopr_settings_reset" class="of-save-reorder button-primary" style="margin: 15px;">Reset Order <span class="spinner"></span></a></div></div>';

		return $html;
	}
	
	public function theme_option(){
		global $current_user;
		$themes = array(
			'DP_AC_wp_admin' => 'WP Admin',
			'DP_AC_gutenberg' => 'Gutenberg',
			'DP_AC_gutenberg_Oxygen' => 'Gutenberg optimized for Oxygen',
			'DP_AC_inject_css' => 'Inject Custom CSS'
		);
		if($current_user->ID == $this->DP_AC_super_admin){
			$themes['DP_AC_inject_global'] = 'Inject Global Custom CSS';
		}
		$html = '';
		foreach($themes as $option => $name){
			$option_value = get_user_meta($current_user->ID, $option, true);
			if($option == "DP_AC_inject_global"){
				$option_value = get_option('DP_AC_inject_global');
			}
			$html .= '<div class="dp__switch" '.(($option == "DP_AC_inject_global") ? 'style="margin-top: 20px;"' : '').'>    
				<label>
					<input name="'.$option.'" type="checkbox" value="'.$option.'" '.((!empty($option_value) &&  $option_value == "yes") ? 'checked' : '').' />
					<div class="rwmb-switch-status">
						<span class="rwmb-switch-slider"></span>
						<span class="rwmb-switch-on">On</span>
						<span class="rwmb-switch-off"></span>
					</div>
					<div class="role-name">'.$name.'</div>
				</label>
			</div>';
			if($option == "DP_AC_inject_css"){
				$html.= '<div class="DP_AC-editor DP_AC_custom_css" style="'.(($option_value != "yes") ? 'display:none;' : '').'">
					<textarea id="DP_AC_custom_css">' . base64_decode(get_user_meta($current_user->ID, 'DP_AC_custom_css', true)) . '</textarea>
				</div>';
			}
			if($option == "DP_AC_inject_global"){
				$html.= '<div class="DP_AC-editor DP_AC_global_css" style="'.(($option_value != "yes") ? 'display:none;' : '').'">
					<textarea id="DP_AC_global_css">' . base64_decode(get_option('DP_AC_global_css')) . '</textarea>
				</div>';
			}
		}
		$html .= '<a href="#" class="of-save-theme button-primary" style="margin-top: 15px;">Save Changes <span class="spinner"></span></a>';

		return $html;
	}

	public function reorder_menus(){
		$html = '';
		global $menu;
		$html .= '<ul class="reorder-admin-menus" id="reorder-admin-menus">';
		foreach($menu as $key => $main_menu){
			if(!empty($main_menu[0])){
				$html .= '<li><span class="move"></span>
					<div '.((isset($main_menu[6]) && strpos($main_menu[6], 'dashicons') !== false) ? 'class="menu-name dashicons-before '.$main_menu[6].'"' : 'class="menu-name wp-menu-image svg" style="background-image:url('.$main_menu[6].'); background-repeat:no-repeat;	"').'>'.$main_menu[0].'</div>
					<input type="hidden" name="WPAC_menu_order[]" value="'.$main_menu[2].'" />
				</li>';
			}
		}
		$html .= '</ul>';
		$html .= '<div class="reorder-buttons"><a href="#" data-tabid="reorder" class="of-save-reorder button-primary" style="margin: 15px;">Save Changes <span class="spinner"></span></a>';
		$html .= '<a href="#" data-tabid="reorder_reset" class="of-save-reorder button-primary" style="margin: 15px;">Reset Order <span class="spinner"></span></a></div>';
		//$html .= '<a href="#" data-tabid="reorder" class="of-save-reorder button-primary" style="margin: 15px;">Save Changes <span class="spinner"></span></a>';

		return $html;
	}
	
	public function adminLogin_form(){
		$DP_AC_login = !empty(get_option('DP_AC_login')) ? get_option('DP_AC_login') : 'login';
		$DP_AC_redirect = !empty(get_option('DP_AC_redirect')) ? get_option('DP_AC_redirect') : '404';
		$html = '<div class="DP-AC-fields URL-options">
			<label class="description" for="DP_AC_login">Login URL</label>
			<div class="url-input">
				<label class="start" for="DP_AC_login">'.site_url().'/</label><input type="text" class="regular-text adminlogin" id="DP_AC_login" name="DP_AC_login" value="'.esc_attr($DP_AC_login).'" />
				<span class="end">/</span>
			</div>
		</div>
		<div class="DP-AC-fields URL-options">
			<label class="description" for="DP_AC_redirect">Redirect /wp-admin/ URL</label>
			<div class="url-input">
				<label class="start" for="DP_AC_redirect">'.site_url().'/</label><input type="text" class="regular-text adminlogin" id="DP_AC_redirect" name="DP_AC_redirect" value="'.esc_attr($DP_AC_redirect).'" />
				<span class="end">/</span>
			</div>
		</div>';

		return $html;
	}
	
	public function redirection_form(){
		$DP_AC_redirection = get_option('DP_AC_redirection');
		$DP_AC_topbar = get_option('DP_AC_topbar');
		global $wp_roles;
		$all_roles = $wp_roles->roles;
		$html = '<table>';
		foreach($all_roles as $role_slug => $role){
			$html .= '<tr>
				<td><label class="description" for="'.$role_slug.'"> '.esc_attr($role['name']).'</label></td>
				<td><input type="text" class="regular-text redirection" name="'.$role_slug.'" value="'.((!empty($DP_AC_redirection) && isset($DP_AC_redirection[$role_slug])) ? $DP_AC_redirection[$role_slug] : '').'" /></td>
				<td>
				'.(($role_slug != "administrator") ? '<div class="dp__switch">
					<label>
						<input type="checkbox" class="DP_AC_topbar" '.((!empty($DP_AC_topbar) && isset($DP_AC_topbar[$role_slug]) && $DP_AC_topbar[$role_slug] == "yes") ? 'checked' : '').' />
						<div class="rwmb-switch-status">
							<span class="rwmb-switch-slider"></span>
							<span class="rwmb-switch-on">On</span>
							<span class="rwmb-switch-off"></span>
						</div>
						<div class="role-name">Hide Topbar <input type="hidden" class="DP_AC_topbar_input" name="'.$role_slug.'" value="'.((!empty($DP_AC_topbar) && isset($DP_AC_topbar[$role_slug])) ? $DP_AC_topbar[$role_slug] : 'no').'" /></div>
					</label>
				</div>' : '').'
				</td>
			</tr>';
		}
		$html .= '</table><div class="option-space"></div>
		<a href="#" class="of-save-redirection button-primary" style="margin-top: 15px;">Save Changes <span class="spinner"></span></a>';
		return $html;
	}
	
	public function custom_dashboard_form(){
		$DP_AC_cus_dashboard = get_option('DP_AC_cus_dashboard');
		if(!is_array($DP_AC_cus_dashboard)){
			$DP_AC_cus_dashboard = array();
		}
		global $wp_roles;
		$all_roles = $wp_roles->roles;
		$DP_AC_target_parent = get_option('DP_AC_target_parent');
		$html = '<div class="DP-AC-fields">
			<label class="description" for="DP_AC_cus_dashboard"> Custom Dashboard Page URL</label>
		</div>';
		$html .= '<table>';
		$role_slug = 'all';
		$html .= '<tr>
			<td><label class="description" for="'.$role_slug.'"> All</label></td>
			<td>
				<input type="text" class="regular-text cus-dashboard" name="'.$role_slug.'" value="'.((!empty($DP_AC_cus_dashboard) && isset($DP_AC_cus_dashboard[$role_slug])) ? $DP_AC_cus_dashboard[$role_slug] : '').'">
			</td>
		</tr>';
		foreach($all_roles as $role_slug => $role){
			$html .= '<tr>
				<td><label class="description" for="'.$role_slug.'"> '.esc_attr($role['name']).'</label></td>
				<td>
					<input type="text" class="regular-text cus-dashboard" name="'.$role_slug.'" value="'.((!empty($DP_AC_cus_dashboard) && isset($DP_AC_cus_dashboard[$role_slug])) ? $DP_AC_cus_dashboard[$role_slug] : '').'">
				</td>
			</tr>';
		}
		$html .= '</table>
		<div class="DP_AC_admin_body" style="padding-left: 0px; padding-right: 0px;">
			<div class="panel" id="panel-user_roles-tools">
				<div class="panel-body clearfix">
				</div>
				<div class="panel-body clearfix">
					<div class="dp__switch">    
						<label>
							<input type="checkbox" id="DP_AC_target_parent" '.((!empty($DP_AC_target_parent) && $DP_AC_target_parent == "yes") ? 'checked' : '').' />
							<div class="rwmb-switch-status">
								<span class="rwmb-switch-slider"></span>
								<span class="rwmb-switch-on">On</span>
								<span class="rwmb-switch-off"></span>
							</div>	
							<div class="role-name">Set taget="_parent" for embedded page <a href="https://docs.dplugins.com/wp-admin-cleaner/custom-wordpress-dashboard/#iframe-parent" target="_blank">view documentation</a></div>
						</label>
					</div>
				</div>
			</div>
		</div>
		<div class="option-space"></div>
		<a href="#" class="of-save-cus-dashboard button-primary" style="margin-top: 15px;">Save Changes <span class="spinner"></span></a>';
		return $html;
	}

	public function this_login_scripts(){
		$DP_AC_logo = get_option('DP_AC_logo');
		$DP_AC_logow = get_option('DP_AC_logow');
		$DP_AC_logoh = get_option('DP_AC_logoh');
		$DP_AC_bg_img = get_option('DP_AC_bg_img');
		$DP_AC_bgo = get_option('DP_AC_bgo'); // Add this line to retrieve the value of DP_AC_bgo
		$DP_AC_bg = get_option('DP_AC_bg');
		$DP_AC_form_bg = get_option('DP_AC_form_bg');
		$DP_AC_color = get_option('DP_AC_color');
		$DP_AC_btn_bg = get_option('DP_AC_btn_bg');
		$DP_AC_btn_bgh = get_option('DP_AC_btn_bgh');
		$DP_AC_btn_bd = get_option('DP_AC_btn_bd');
		$DP_AC_btn_bdh = get_option('DP_AC_btn_bdh');
		$DP_AC_btn_color = get_option('DP_AC_btn_color');
		$DP_AC_btn_colorh = get_option('DP_AC_btn_colorh');
		$DP_AC_layout = get_option('DP_AC_layout');
		if(!empty($DP_AC_layout)){
			if($DP_AC_layout == "custom"){
				$upload_dir = wp_upload_dir();
				$filepath = $upload_dir['basedir'].'/wp-admin-cleaner'.'/DP_AC_Login-custom.css';
				if(file_exists($filepath)){
					$file_url = $upload_dir['baseurl'].'/wp-admin-cleaner'.'/DP_AC_Login-custom.css';
					wp_enqueue_style('DP_AC_login-'.$DP_AC_layout, $file_url, DP_AC_PLUGINVERSION);
				}
			} else {
				wp_enqueue_style( 'DP_AC_login-'.$DP_AC_layout, plugin_dir_url( __FILE__ ) . 'css/'.$DP_AC_layout.'.css', DP_AC_PLUGINVERSION);
			}
		}
		echo '<style type="text/css">
			.login h1 a {
				'.((!empty($DP_AC_logo)) ? 'background-image: url('.esc_url($DP_AC_logo).') !important;' : '').'
				'.((!empty($DP_AC_logow)) ? 'background-size: '.esc_attr($DP_AC_logow).'px !important; width: '.esc_attr($DP_AC_logow).'px !important;' : '').'
				'.((!empty($DP_AC_logoh)) ? 'background-size: '.esc_attr($DP_AC_logoh).'px !important; height: '.esc_attr($DP_AC_logoh).'px !important;' : '').'
				background-size: contain !important;
			}
		</style>';
		
		if (!empty($DP_AC_bg_img)) {
			echo '<style type="text/css">
				#bg-image {
					background-image:url('.esc_url($DP_AC_bg_img).') !important;
					position: fixed;
					top: 0;
					left: 0;
					width: 100%;
					height: 100%;
					z-index: -1;
					background-size:cover !important;
					background-position:center center !important;
					'.((!empty($DP_AC_bgo)) ? 'box-shadow: inset 0 0 0 5000px '.$DP_AC_bgo.';' : '').'
					'.((!empty(get_available_languages())) ? 'grid-row: span 2;' : ''  ).'
				}
			</style>
			<div id="bg-image"></div>';
		}

		echo '<style type="text/css">
			body {
				'.(!empty($DP_AC_bg) ? 'background: '.esc_attr($DP_AC_bg).' !important;' : '').'
			}
			'.(!empty($DP_AC_color) ? 'body, .login #backtoblog a, .login label, .login #nav a {color: '.esc_attr($DP_AC_color).' !important; }' : '').'
			.wp-core-ui .button-primary {
				'.(!empty($DP_AC_btn_bg) ? 'background: '.esc_attr($DP_AC_btn_bg).' !important;' : '').'
				'.(!empty($DP_AC_btn_bd) ? 'border-color: '.esc_attr($DP_AC_btn_bd).' !important;' : '').'
				'.(!empty($DP_AC_btn_color) ? 'color: '.esc_attr($DP_AC_btn_color).' !important;' : '').'
			}
			#loginform,
			.login form,
			.message{
				'.(!empty($DP_AC_form_bg) ? 'background: '.esc_attr($DP_AC_form_bg).' !important;' : '').'
			}
			.login form{
				'.(!empty($DP_AC_form_bg) ? 'border-color: '.esc_attr($DP_AC_form_bg).' !important;' : '').'
			}
			.wp-core-ui .button-primary.focus, .wp-core-ui .button-primary.hover, .wp-core-ui .button-primary:focus, .wp-core-ui .button-primary:hover {
				'.(!empty($DP_AC_btn_bgh) ? 'background: '.esc_attr($DP_AC_btn_bgh).' !important;' : '').'
				'.(!empty($DP_AC_btn_bdh) ? 'border-color: '.esc_attr($DP_AC_btn_bdh).' !important;' : '').'
				'.(!empty($DP_AC_btn_colorh) ? 'color: '.esc_attr($DP_AC_btn_colorh).' !important;' : '').'
			}
			#language-switcher{
				background: transparent !important;
			}
			'.((in_array($DP_AC_layout, array('wp-login--halfscreen-image', 'wp-login--halfscreen-image-dard'))) ? 'body.login>*:not(#bg-image, div[id*="login"], .language-switcher, div[class*="login"]) {
				display: none !important;
			}' : '').'
		</style>';
    }

	public function save_admin_settings_func(){
		check_ajax_referer('ajax-nonce', 'verify_nonce');
		global $current_user;
		$top_menus = (isset($_POST['top_menus'])) ? $_POST['top_menus'] : array();
		$top_menus_frontend = (isset($_POST['top_menus_frontend'])) ? $_POST['top_menus_frontend'] : array();
		$menus = (isset($_POST['menus'])) ? $_POST['menus'] : array();
		$sub_menus = (isset($_POST['sub_menus'])) ? $_POST['sub_menus'] : array();
		$tabid = $_POST['tabid'];
		$role_slug = $_POST['role_slug'];

		$recreate_submenus = array();
		if(!empty($sub_menus)){
			foreach($sub_menus as $sub_menu){
				$recreate_submenus[$sub_menu['parent']][] = $sub_menu['current'];
			}
		}
		if($tabid == "admin_settings"){
			update_user_meta($current_user->ID, 'DP_AC_tf_to_hide', serialize($top_menus_frontend));
			update_user_meta($current_user->ID, 'DP_AC_t_to_hide', serialize($top_menus));
			update_user_meta($current_user->ID, 'DP_AC_m_to_hide', serialize($menus));
			update_user_meta($current_user->ID, 'DP_AC_sm_to_hide', serialize($recreate_submenus));
		} else if($tabid == "admin_plugins"){
			update_option('DP_AC_rp', serialize($menus), 'no');
		} else {
			update_option($role_slug.'_DP_AC_tf_to_hide', serialize($top_menus_frontend), 'no');
			update_option($role_slug.'_DP_AC_t_to_hide', serialize($top_menus), 'no');
			update_option($role_slug.'_DP_AC_m_to_hide', serialize($menus), 'no');
			update_option($role_slug.'_DP_AC_sm_to_hide', serialize($recreate_submenus), 'no');
		}

		echo 'success';
		
		wp_die();
	}
	
	public function save_roles(){
		check_ajax_referer('ajax-nonce', 'verify_nonce');
		$roles = (isset($_POST['roles'])) ? $_POST['roles'] : array();
		update_option('DP_AC_roles', $roles, 'no');

		echo 'success';		
		wp_die();
	}
	
	public function save_custom_menus(){
		check_ajax_referer('ajax-nonce', 'verify_nonce');
		$id = (isset($_POST['id'])) ? $_POST['id'] : '';
		$form_type = (isset($_POST['form_type'])) ? $_POST['form_type'] : '';
		$custom_menus = (isset($_POST['custom_menus'])) ? $_POST['custom_menus'] : array();
		global $wpdb, $current_user;
		$custom_menus['user_id'] = $current_user->ID;
		$dp_ac_custom_menus = $wpdb->prefix . "dp_ac_custom_menus";
		if($id != ""){
			$wpdb->update( $dp_ac_custom_menus, 
				$custom_menus,
				array( 'id' => $id )
			);
		} else {
			$wpdb->insert($dp_ac_custom_menus, $custom_menus);
		}

		echo json_encode(array(
			'menu_parents' => $this->get_custom_menu_parents(),
			'custom_menus' => $this->get_custom_menus_html($this->get_custom_menus())
		));
		wp_die();
	}
	
	public function delete_custom_menus(){
		check_ajax_referer('ajax-nonce', 'verify_nonce');
		$id = (isset($_POST['id'])) ? $_POST['id'] : '';
		global $wpdb;
		$dp_ac_custom_menus = $wpdb->prefix . "dp_ac_custom_menus";
		$wpdb->delete(
			$dp_ac_custom_menus,
			array(
				'id' => $id
			)
		);
		$wpdb->delete(
			$dp_ac_custom_menus,
			array(
				'parent_id' => $id
			)
		);
		echo json_encode(array(
			'custom_menus' => $this->get_custom_menus_html($this->get_custom_menus())
		));
		wp_die();
	}
	
	public function DP_AC_disable_wpac(){
		check_ajax_referer('ajax-nonce', 'verify_nonce');
		$DP_AC_disable_wpac = (isset($_POST['DP_AC_disable_wpac'])) ? $_POST['DP_AC_disable_wpac'] : array();
		update_option('DP_AC_disable_wpac', $DP_AC_disable_wpac, 'no');

		echo 'success';		
		wp_die();
	}
	
	public function DP_AC_reorder_force(){
		check_ajax_referer('ajax-nonce', 'verify_nonce');
		$DP_AC_reorder_force = (isset($_POST['DP_AC_reorder_force'])) ? $_POST['DP_AC_reorder_force'] : 'no';
		update_option('DP_AC_reorder_force', $DP_AC_reorder_force, 'no');

		echo 'success';		
		wp_die();
	}
	
	public function DP_AC_remove_data(){
		check_ajax_referer('ajax-nonce', 'verify_nonce');
		$DP_AC_remove_data = (isset($_POST['DP_AC_remove_data'])) ? $_POST['DP_AC_remove_data'] : 'no';
		update_option('DP_AC_remove_data', $DP_AC_remove_data, 'no');

		echo 'success';		
		wp_die();
	}
	
	public function DP_AC_super_admin(){
		check_ajax_referer('ajax-nonce', 'verify_nonce');
		$this->DP_AC_super_admin = (isset($_POST['DP_AC_super_admin'])) ? $_POST['DP_AC_super_admin'] : '';
		update_option('DP_AC_super_admin', $this->DP_AC_super_admin, 'no');

		echo 'success';		
		wp_die();
	}
	
	public function loadRoleMenuSettings(){
		check_ajax_referer('ajax-nonce', 'verify_nonce');
		$role_slug = $_POST['role_slug'];
		$DP_AC_tf_to_hide = get_option($role_slug.'_DP_AC_tf_to_hide');
		$DP_AC_t_to_hide = get_option($role_slug.'_DP_AC_t_to_hide');
		$DP_AC_m_to_hide = get_option($role_slug.'_DP_AC_m_to_hide');
		$DP_AC_sm_to_hide = get_option($role_slug.'_DP_AC_sm_to_hide');
		$DP_AC_top_menus_frontend_to_hide = (!empty($DP_AC_tf_to_hide)) ? unserialize($DP_AC_tf_to_hide) : array();
		$DP_AC_top_menus_to_hide = (!empty($DP_AC_t_to_hide)) ? unserialize($DP_AC_t_to_hide) : array();
		$DP_AC_menus_to_hide = (!empty($DP_AC_m_to_hide)) ? unserialize($DP_AC_m_to_hide) : array();
		$DP_AC_submenus_to_hide = (!empty($DP_AC_sm_to_hide)) ? unserialize($DP_AC_sm_to_hide) : array();
		echo json_encode(array(
			'top_bar' => $DP_AC_top_menus_to_hide,
			'top_bar_frontend' => $DP_AC_top_menus_frontend_to_hide,
			'menus' => $DP_AC_menus_to_hide,
			'submenus' => json_encode($DP_AC_submenus_to_hide)
		));

		wp_die();
	}
	
	public function loadRoleMenuOrder(){
		check_ajax_referer('ajax-nonce', 'verify_nonce');
		$role_slug = $_POST['role_slug'];
		$DP_AC_reorder = get_option($role_slug.'_DP_AC_reorder');
		if(!empty($DP_AC_reorder)){
			asort($DP_AC_reorder);
		}
		$DP_AC_reorder_order = (!empty($DP_AC_reorder)) ? $DP_AC_reorder : array();
		echo json_encode(array(
			'order' => $DP_AC_reorder_order,
		));

		wp_die();
	}
	
	public function save_themes(){
		check_ajax_referer('ajax-nonce', 'verify_nonce');
		global $current_user;
		$themes = (isset($_POST['themes'])) ? $_POST['themes'] : array();
		if(!empty($themes)){
			foreach($themes as $theme){
				if($theme['name'] == "DP_AC_custom_css"){
					$theme['value'] = base64_encode(json_decode(stripslashes($theme['value'])));
					$upload_dir = wp_upload_dir();
					$folder_path = $upload_dir['basedir'].'/wp-admin-cleaner';
					$filepath = $upload_dir['basedir'].'/wp-admin-cleaner'."/DP_AC-".$current_user->ID.".css";
					if(!empty($theme['value'])){
						if (!is_dir($folder_path)) {
							mkdir($folder_path);
						}
						$upload_css = file_put_contents($filepath, base64_decode($theme['value']));
					} else {
						if(file_exists($filepath)) unlink($filepath);
					}
				}
				if($theme['name'] == "DP_AC_global_css"){
					$theme['value'] = base64_encode(json_decode(stripslashes($theme['value'])));
					$upload_dir = wp_upload_dir();
					$folder_path = $upload_dir['basedir'].'/wp-admin-cleaner';
					$filepath = $upload_dir['basedir'].'/wp-admin-cleaner'."/DP_AC-admin-css.css";
					if(!empty($theme['value'])){
						if (!is_dir($folder_path)) {
							mkdir($folder_path);
						}
						$upload_css = file_put_contents($filepath, base64_decode($theme['value']));
					} else {
						if(file_exists($filepath)) unlink($filepath);
					}
				}
				if(in_array($theme['name'], array('DP_AC_inject_global', 'DP_AC_global_css'))){
					update_option($theme['name'], $theme['value'], 'no');
				} else {
					update_user_meta($current_user->ID, $theme['name'], $theme['value']);
				}
			}
		}

		echo 'success';		
		wp_die();
	}
	
	public function save_adminLogin_options(){
		check_ajax_referer('ajax-nonce', 'verify_nonce');
		global $current_user;
		$adminlogin_options = (isset($_POST['adminlogin_options'])) ? $_POST['adminlogin_options'] : array();
		if(!empty($adminlogin_options)){
			foreach($adminlogin_options as $option){
				if($option['key'] == "DP_AC_login_css"){
					$option['value'] = base64_encode(json_decode(stripslashes($option['value'])));
					$upload_dir = wp_upload_dir();
					$folder_path = $upload_dir['basedir'].'/wp-admin-cleaner';
					$filepath = $upload_dir['basedir'].'/wp-admin-cleaner'.'/DP_AC_Login-custom.css';
					if(!empty($option['value'])){
						if (!is_dir($folder_path)) {
							mkdir($folder_path);
						}
						$upload_css = file_put_contents($filepath, base64_decode($option['value']));
					} else {
						if(file_exists($filepath)) unlink($filepath);
					}
				}
				update_option($option['key'], $option['value'], 'no');
			}
		}

		echo 'success';		
		wp_die();
	}
	
	public function save_redirection_options(){
		check_ajax_referer('ajax-nonce', 'verify_nonce');
		global $current_user;
		$redirection_options = (isset($_POST['redirection_options'])) ? $_POST['redirection_options'] : array();
		$topbar_options = (isset($_POST['topbar_options'])) ? $_POST['topbar_options'] : array();
		update_option('DP_AC_redirection', $redirection_options, 'no');
		update_option('DP_AC_topbar', $topbar_options, 'no');

		echo 'success';		
		wp_die();
	}
	
	public function save_cus_dashboard(){
		check_ajax_referer('ajax-nonce', 'verify_nonce');
		$cus_dashboard_options = (isset($_POST['cus_dashboard_options'])) ? $_POST['cus_dashboard_options'] : array();
		update_option('DP_AC_cus_dashboard', $cus_dashboard_options, 'no');

		$DP_AC_target_parent = (isset($_POST['DP_AC_target_parent'])) ? $_POST['DP_AC_target_parent'] : array();
		update_option('DP_AC_target_parent', $DP_AC_target_parent, 'no');
		
		echo 'success';		
		wp_die();
	}
	
	public function save_reorder(){
		check_ajax_referer('ajax-nonce', 'verify_nonce');
		global $current_user;
		$reorder = (isset($_POST['reorder'])) ? $_POST['reorder'] : array();
		$tabid = $_POST['tabid'];
		$role_slug = $_POST['role_slug'];
		if($tabid == "mopr_settings_reset"){
			update_option($role_slug.'_DP_AC_reorder', array(), 'no');
		} else {
			if($tabid == "reorder_reset"){
				update_user_meta($current_user->ID, 'DP_AC_reorder', array());
			} else {
				if(!empty($reorder)){
					$reorder_array = array();
					$i = 0;
					foreach($reorder as $order){
						$reorder_array[$order] = $i;
						$i++;
					}
					if($tabid == "mopr_settings"){
						update_option($role_slug.'_DP_AC_reorder', $reorder_array, 'no');
					} else {
						update_user_meta($current_user->ID, 'DP_AC_reorder', $reorder_array);
					}
				}
			}	
		}

		echo 'success';		
		wp_die();
	}

	public function remove_admin_menus_submenus(){
		// if settings page of admin cleaner
		if(isset($_GET) && isset($_GET['page']) && $_GET['page'] == "dplugins_admin_cleaner"){
			return;
		}
		global $current_user;
		$DP_AC_roles = $this->get_DP_AC_roles(true);
		$user_has_allowed_roles = array_intersect($current_user->roles, $DP_AC_roles);
		// if user role is not allowed to use this
		if(empty($user_has_allowed_roles)){
			return;
		}

		// if deactivated
		if($this->check_if_admin_cleaner_turned_off_for_users()){
            if($this->check_if_user_has_wpac_activated()){
                return;
            }
        }
		global $menu, $submenu, $current_user;
		$DP_AC_menus_to_hide = array();
		$DP_AC_submenus_to_hide = array();
		if(in_array('administrator', $current_user->roles) && $current_user->ID == $this->DP_AC_super_admin){
			$DP_AC_m_to_hide = get_user_meta($current_user->ID, 'DP_AC_m_to_hide', true);
			$DP_AC_sm_to_hide = get_user_meta($current_user->ID, 'DP_AC_sm_to_hide', true);
			$DP_AC_menus_to_hide = (!empty($DP_AC_m_to_hide)) ? unserialize($DP_AC_m_to_hide) : array();
			$DP_AC_submenus_to_hide = (!empty($DP_AC_sm_to_hide)) ? unserialize($DP_AC_sm_to_hide) : array();
		} else {
			foreach($current_user->roles as $role){
				$DP_AC_m_to_hide = get_option($role.'_DP_AC_m_to_hide');
				$DP_AC_sm_to_hide = get_option($role.'_DP_AC_sm_to_hide');
				$DP_AC_menus_to_hide = (!empty($DP_AC_m_to_hide)) ? unserialize($DP_AC_m_to_hide) : array();
				$DP_AC_submenus_to_hide = (!empty($DP_AC_sm_to_hide)) ? unserialize($DP_AC_sm_to_hide) : array();

				$this_user_DP_AC_m_to_hide = get_user_meta($current_user->ID, 'DP_AC_m_to_hide', true);
				$this_user_DP_AC_sm_to_hide = get_user_meta($current_user->ID, 'DP_AC_sm_to_hide', true);
				$this_user_DP_AC_menus_to_hide = (!empty($this_user_DP_AC_m_to_hide)) ? unserialize($this_user_DP_AC_m_to_hide) : array();
				$this_user_DP_AC_submenus_to_hide = (!empty($this_user_DP_AC_sm_to_hide)) ? unserialize($this_user_DP_AC_sm_to_hide) : array();

				$DP_AC_menus_to_hide = array_merge($DP_AC_menus_to_hide, $this_user_DP_AC_menus_to_hide);
				$DP_AC_submenus_to_hide = array_merge($DP_AC_submenus_to_hide, $this_user_DP_AC_submenus_to_hide);
			}
		}
		//echo "<pre>"; print_r($DP_AC_menus_to_hide); "</pre>"; exit;
		if(!empty($DP_AC_menus_to_hide)){
			foreach($menu as $key => $main_menu){
				if(!empty($main_menu[0])){
					if(in_array($main_menu[5], $DP_AC_menus_to_hide)){
						if(isset($submenu[$main_menu[2]])){
							$sub_menus = $submenu[$main_menu[2]];
							foreach($sub_menus as $sub_menu){
								//remove_submenu_page( $main_menu[2], $sub_menu[2]);
								unset($submenu[$key][$sub_menu]);
							}
						}
						//echo $main_menu[2] . '<br/>';
						remove_menu_page( $main_menu[2] );
					}
				}
			}	
		}
		//exit;

		if(!empty($DP_AC_submenus_to_hide)){
			foreach($DP_AC_submenus_to_hide as $key => $sub_menus){
				foreach($sub_menus as $sub_menu){
					if(isset($submenu[$key])){
						unset($submenu[$key][$sub_menu]);
					}
				}
			}
		}
	}
	
	public function disallow_admin_menus_submenus(){
		// if settings page of admin cleaner
		if(isset($_GET) && isset($_GET['page']) && $_GET['page'] == "dplugins_admin_cleaner"){
			return;
		}
		global $current_user;
		$DP_AC_roles = $this->get_DP_AC_roles(true);
		$user_has_allowed_roles = array_intersect($current_user->roles, $DP_AC_roles);
		
		// if user role is not allowed to use this
		if(empty($user_has_allowed_roles)){
			return;
		}

		// if deactivated
		if($this->check_if_admin_cleaner_turned_off_for_users()){
            if($this->check_if_user_has_wpac_activated()){
                return;
            }
        }
		global $menu, $submenu, $current_user;
		$DP_AC_menus_to_hide = array();
		$DP_AC_submenus_to_hide = array();
		if(in_array('administrator', $current_user->roles) && $current_user->ID == $this->DP_AC_super_admin){
			$DP_AC_m_to_hide = get_user_meta($current_user->ID, 'DP_AC_m_to_hide', true);
			$DP_AC_sm_to_hide = get_user_meta($current_user->ID, 'DP_AC_sm_to_hide', true);
			$DP_AC_menus_to_hide = (!empty($DP_AC_m_to_hide)) ? unserialize($DP_AC_m_to_hide) : array();
			$DP_AC_submenus_to_hide = (!empty($DP_AC_sm_to_hide)) ? unserialize($DP_AC_sm_to_hide) : array();
		} else {
			foreach($current_user->roles as $role){
				$DP_AC_m_to_hide = get_option($role.'_DP_AC_m_to_hide');
				$DP_AC_sm_to_hide = get_option($role.'_DP_AC_sm_to_hide');
				$DP_AC_menus_to_hide = (!empty($DP_AC_m_to_hide)) ? unserialize($DP_AC_m_to_hide) : array();
				$DP_AC_submenus_to_hide = (!empty($DP_AC_sm_to_hide)) ? unserialize($DP_AC_sm_to_hide) : array();
				$this_user_DP_AC_m_to_hide = get_user_meta($current_user->ID, 'DP_AC_m_to_hide', true);
				$this_user_DP_AC_sm_to_hide = get_user_meta($current_user->ID, 'DP_AC_sm_to_hide', true);
				$this_user_DP_AC_menus_to_hide = (!empty($this_user_DP_AC_m_to_hide)) ? unserialize($this_user_DP_AC_m_to_hide) : array();
				$this_user_DP_AC_submenus_to_hide = (!empty($this_user_DP_AC_sm_to_hide)) ? unserialize($this_user_DP_AC_sm_to_hide) : array();

				$DP_AC_menus_to_hide = array_merge($DP_AC_menus_to_hide, $this_user_DP_AC_menus_to_hide);
				$DP_AC_submenus_to_hide = array_merge($DP_AC_submenus_to_hide, $this_user_DP_AC_submenus_to_hide);
			}
		}
		
		$disabled_message = '<div class="admin-alert" style="background: #fff;border: 1px solid #ccd0d4;color: #444;font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Oxygen-Sans, Ubuntu, Cantarell, \'Helvetica Neue\', sans-serif;margin: 2em auto;margin-top:0px;padding: 1em 2em;max-width: 700px;-webkit-box-shadow: 0 1px 1px rgba(0, 0, 0, .04);box-shadow: 0 1px 1px rgba(0, 0, 0, .04);">
			<div style="font-size: 14px;line-height: 1.5;margin: 25px 0 20px;">
				Sorry, you are not allowed to access this page.
			</div>
		</div>';
		$screen = get_current_screen();
		global $menu, $submenu, $submenu_file, $parent_file;
		$menu = $this->this_menu;
		$submenu = $this->this_submenu;
		$wp_admin_bar = $this->this_wp_admin_bar;
		$curr_url = explode("/", $_SERVER['REQUEST_URI']);
		$total_length = count($curr_url);
		if(!empty($DP_AC_menus_to_hide)){
			if(in_array($screen->id, $DP_AC_menus_to_hide)){
				echo $disabled_message; exit;
			}
			$current_page = $screen->id.'.php';
			foreach($menu as $key => $main_menu){
				if(!empty($main_menu[0])){
					if(in_array($main_menu[5], $DP_AC_menus_to_hide)){
						if(isset($submenu[$main_menu[2]])){
							$sub_menus = $submenu[$main_menu[2]];
							foreach($sub_menus as $sub_menu){
								$sub_item_string_length = strlen($sub_menu[2]);
								$sub_string_current_url = strrev(substr(strrev($curr_url[$total_length - 1]), 0, $sub_item_string_length));
								if($sub_menu[2] == $sub_string_current_url){
									if (!empty($parent_file)) {
										// Extract the parent menu slug
										$parent_menu_slug = str_replace('.php', '', $parent_file);
										if($parent_menu_slug == $main_menu[2]){
											echo $disabled_message; exit;
										}
									}
								}
								if(empty($submenu_file) && in_array($current_page, $sub_menu)){
									echo $disabled_message; exit;
								} else {
									if(!empty($submenu_file) && isset($submenu[$main_menu[2]][$sub_menu[2]])){
										if(in_array($submenu_file, $submenu[$main_menu[2]][$sub_menu[2]])){
											echo $disabled_message; exit;
										}
									}
								}
								if(empty($submenu_file) && in_array($current_page, $sub_menu)){
									echo $disabled_message; exit;
								} else {
									if(!empty($submenu_file) && in_array($submenu_file, $sub_menu)){
										echo $disabled_message; exit;
									}
								}
							}
						}
					}
					
					if(in_array('menu-comments', $DP_AC_menus_to_hide) && $current_page == "edit-comments.php"){
						echo $disabled_message; exit;
					}
				}
			}
		}
		
		if(!empty($DP_AC_submenus_to_hide)){
			$current_page = $screen->id.'.php';
			foreach($DP_AC_submenus_to_hide as $key => $sub_items){
				//echo $key . '<br/>';
				foreach($menu as $sub_key => $main_menu){
					if(in_array($key, $main_menu)){
						if(!empty($main_menu[0])){
							if(isset($submenu[$main_menu[2]])){
								foreach($submenu[$main_menu[2]] as $sub_submenu){
									foreach($sub_items as $sub_item){
										if(isset($submenu[$main_menu[2]][$sub_item][2])){
											$sub_item_string_length = strlen($submenu[$main_menu[2]][$sub_item][2]);
											$sub_string_current_url = strrev(substr(strrev($curr_url[$total_length - 1]), 0, $sub_item_string_length));
											//echo $submenu[$main_menu[2]][$sub_item][2] . ' - ' . $sub_string_current_url . '<br/>';
											if($submenu[$main_menu[2]][$sub_item][2] == $sub_string_current_url){
												echo $disabled_message; exit;
											}
											if ( isset( $_GET['page']) && ( $_GET['page'] == 'wc-settings' ) ) {
												echo $disabled_message; exit;
											}
											if(empty($submenu_file) && in_array($current_page, $submenu[$main_menu[2]][$sub_item])){
												echo $disabled_message; exit;
											} else {
												if(!empty($submenu_file) && isset($submenu[$main_menu[2]][$sub_item])){
													if(in_array($submenu_file, $submenu[$main_menu[2]][$sub_item])){
														echo $disabled_message; exit;
													}
												}
											}
										}
									}	
								}
							}
						}
					}
				}
			}
		}
	}

	public function deactivate_DP_AC(){
		if(isset($_GET['deactivate_DP_AC'])){
			$show_activate_deactivate = false;
			global $current_user;
			$DP_AC_roles = $this->get_DP_AC_roles();
			$user_has_allowed_roles = array_intersect($current_user->roles, $DP_AC_roles);
			if(!in_array('administrator', $current_user->roles)){
				if(!empty($user_has_allowed_roles)){
					$show_activate_deactivate = true;
				}
			} else {
				if($current_user->ID == $this->DP_AC_super_admin){
					$show_activate_deactivate = true;
				} else {
					$DP_AC_disable_wpac = get_option('DP_AC_disable_wpac');
					if($DP_AC_disable_wpac != "yes"){
						$show_activate_deactivate = true;
					}
				}
			}

			if($show_activate_deactivate){
				$action = (sanitize_text_field($_GET['deactivate_DP_AC']) == "yes") ? 'no' : 'yes';
				update_user_meta($current_user->ID, 'DP_AC_trigger_action', $action);
			}
		}
	}

	public function admin_bar_action($admin_bar){
		global $menu, $submenu, $wp_admin_bar;
		$show_activate_deactivate = false;
		global $current_user;
		$DP_AC_roles = $this->get_DP_AC_roles();
		$user_has_allowed_roles = array_intersect($current_user->roles, $DP_AC_roles);
		if(!in_array('administrator', $current_user->roles)){
			if(!empty($user_has_allowed_roles)){
				$show_activate_deactivate = true;
			}
		} else {
			if($current_user->ID == $this->DP_AC_super_admin){
				$show_activate_deactivate = true;
			} else {
				$DP_AC_disable_wpac = get_option('DP_AC_disable_wpac');
				if($DP_AC_disable_wpac != "yes"){
					$show_activate_deactivate = true;
				}
			}
		}

		if($show_activate_deactivate){
			$label = 'Deactivate Cleaner';
			$class = 'active';
			$url = site_url().'/wp-admin/tools.php?page='.DPLUGINS_AC_ADMIN_SLUG.'&deactivate_DP_AC=yes';
			$DP_AC_trigger_action = get_user_meta($current_user->ID, 'DP_AC_trigger_action', true);
			if($DP_AC_trigger_action == "no"){
				$label = 'Activate Cleaner';
				$class = 'inactive';
				$url = site_url().'/wp-admin/tools.php?page='.DPLUGINS_AC_ADMIN_SLUG.'&deactivate_DP_AC=no';
			}
		    $admin_bar->add_menu( array(
		    	'parent' => 'top-secondary',
		        'id'    => 'admin-cleaner-main',
		        'title' => $label,
		        'href'  => esc_url($url),
		        'meta'  => array(
		            'title' => $label,
		            'class' => 'admin-cleaner-main ' . $class,
		        ),
		    ));
		}
	}

	public function admin_menu_order( $menu_order ){
		global $current_user;
		if(in_array('administrator', $current_user->roles) && $current_user->ID == $this->DP_AC_super_admin){
			$DP_AC_reorder = get_user_meta($current_user->ID, 'DP_AC_reorder', true);
			if(!empty($DP_AC_reorder)){
				$menu_order = $this->reorder_menu($menu_order, $DP_AC_reorder);
			}
			return $menu_order;
		}
		if($this->DP_AC_check_user_role_is_allowed()){
			$DP_AC_reorder_force = get_option('DP_AC_reorder_force');
			$DP_AC_reorder = get_user_meta($current_user->ID, 'DP_AC_reorder', true);
			if(!empty($DP_AC_reorder)){
				$menu_order = $this->reorder_menu($menu_order, $DP_AC_reorder);
			}
			if($DP_AC_reorder_force == "yes"){
				$DP_AC_reorder = get_option($current_user->roles[0].'_DP_AC_reorder');
				if(!empty($DP_AC_reorder)){
					$menu_order = $this->reorder_menu($menu_order, $DP_AC_reorder);
				}
			}
		} else {
			$DP_AC_reorder = get_option($current_user->roles[0].'_DP_AC_reorder');
			if(!empty($DP_AC_reorder)){
				$menu_order = $this->reorder_menu($menu_order, $DP_AC_reorder);
			}
		}
		
		return $menu_order;
	}

	public function move_element(&$array, $a, $b) {
		$out = array_splice($array, $a, 1);
		array_splice($array, $b, 0, $out);
	}

	public function reorder_menu($menu_order, $DP_AC_reorder){
		$menu_order = array_unique($menu_order);
		
		// traverse through the new positions and move 
		// the items if found in the original menu_positions
		foreach( $DP_AC_reorder as $value => $new_index ) {
			if( $current_index = array_search( $value, $menu_order ) ) {
				$this->move_element($menu_order, $current_index, $new_index);
			}
		}

		return $menu_order;
	}

	public function containsHtml($string) {
		// Remove HTML tags from the string.
		$strippedString = strip_tags($string);

		// Compare the stripped string with the original string.
		// If they are different, HTML tags were present.
		return $strippedString !== $string;
	}

	public function remove_top_admin_bar(){
		global $wp_admin_bar, $current_user;
		$this->DP_AC_super_admin = get_option('DP_AC_super_admin') ? get_option('DP_AC_super_admin') : $current_user->ID;
		if(isset($_GET) && isset($_GET['page']) && $_GET['page'] == "dplugins_admin_cleaner"){
			return;
		}
		/* refresh top bar */
		if(isset($_GET['DP_AC_refresh']) && sanitize_text_field( $_GET['DP_AC_refresh'] ) == "yes"){
			$title = "";
			foreach ( $wp_admin_bar->get_nodes() as $node ) {
				$title = (isset($node->title) && !$this->containsHtml($node->title)) ? $node->title : $node->id;
				$DP_AC_tf_menus[$node->id] = $title;
			}
			update_option('DP_AC_tf_menus', $DP_AC_tf_menus, 'no');
		}
		/* refresh top bar */

		$DP_AC_roles = $this->get_DP_AC_roles(true);
		$user_has_allowed_roles = array_intersect($current_user->roles, $DP_AC_roles);
		// if user role is not allowed to use this
		if(empty($user_has_allowed_roles)){
			return;
		}

		// if deactivated
		if($this->check_if_admin_cleaner_turned_off_for_users()){
            if($this->check_if_user_has_wpac_activated()){
                return;
            }
        }

		$DP_AC_top_menus_to_hide = array();
		if(in_array('administrator', $current_user->roles) && $current_user->ID == $this->DP_AC_super_admin){
			$DP_AC_t_to_hide = get_user_meta($current_user->ID, 'DP_AC_t_to_hide', true);
			$DP_AC_top_menus_to_hide = (!empty($DP_AC_t_to_hide)) ? unserialize($DP_AC_t_to_hide) : array();

			$DP_AC_tf_to_hide = get_user_meta($current_user->ID, 'DP_AC_tf_to_hide', true);
			$DP_AC_tf_to_hide = (!empty($DP_AC_tf_to_hide)) ? unserialize($DP_AC_tf_to_hide) : array();

			$DP_AC_top_menus_to_hide = array_merge($DP_AC_top_menus_to_hide, $DP_AC_tf_to_hide);
		} else {
			foreach($current_user->roles as $role){
				$DP_AC_t_to_hide = get_option($role.'_DP_AC_t_to_hide');
				$DP_AC_top_menus_to_hide = (!empty($DP_AC_t_to_hide)) ? unserialize($DP_AC_t_to_hide) : array();

				$DP_AC_tf_to_hide = get_option($role.'_DP_AC_tf_to_hide');
				$DP_AC_tf_to_hide = (!empty($DP_AC_tf_to_hide)) ? unserialize($DP_AC_tf_to_hide) : array();

				$this_DP_AC_tf_to_hide = get_user_meta($current_user->ID, 'DP_AC_tf_to_hide', true);
				$this_DP_AC_tf_to_hide = (!empty($this_DP_AC_tf_to_hide)) ? unserialize($this_DP_AC_tf_to_hide) : array();

				$this_user_DP_AC_t_to_hide = get_user_meta($current_user->ID, 'DP_AC_t_to_hide', true);
				$this_user_DP_AC_top_menus_to_hide = (!empty($this_user_DP_AC_t_to_hide)) ? unserialize($this_user_DP_AC_t_to_hide) : array();
				
				$DP_AC_top_menus_to_hide = array_merge($DP_AC_top_menus_to_hide, $DP_AC_tf_to_hide, $this_DP_AC_tf_to_hide, $this_user_DP_AC_top_menus_to_hide);
			}
		}
		if(!empty($DP_AC_top_menus_to_hide)){
			foreach($DP_AC_top_menus_to_hide as $top_menu){
				$wp_admin_bar->remove_menu($top_menu);
			}
		}
	}

	public function restrict_plugins( $plugins ){
		global $current_user;
		if($current_user->ID == $this->DP_AC_super_admin) return $plugins;
		$DP_AC_restricted_plugins = get_option('DP_AC_rp');
		$DP_AC_restricted_plugins = (!empty($DP_AC_restricted_plugins)) ? unserialize($DP_AC_restricted_plugins) : array();
		if(empty($DP_AC_restricted_plugins)) return $plugins;
		
		$shouldHide = ! array_key_exists( 'show_all', $_GET );
		if ( $shouldHide ) {
			foreach ( $DP_AC_restricted_plugins as $restrictedPlugin ) {
				unset( $plugins[ $restrictedPlugin ] );
			}

		}

		return $plugins;
	}

	public function login_redirect( $redirect_to, $request, $user ){
		$DP_AC_redirection = get_option('DP_AC_redirection');
		if(!empty($DP_AC_redirection)){
			if(!empty($user->roles) && isset($user->roles)){
				foreach($user->roles as $role){
					if(isset($DP_AC_redirection[$role]) && !empty($DP_AC_redirection[$role])){
						return $DP_AC_redirection[$role];
					}
				}
			}
		}
		return $redirect_to;
	}

	public function get_current_page_url(){
		return (empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	}

	public function custom_dashoard_page(){
		global $pagenow, $current_user;
		if ($pagenow === 'index.php') {
			$DP_AC_cus_dashboard = get_option('DP_AC_cus_dashboard');
			$DP_AC_cus_dashboard_url = '';
			if(isset($DP_AC_cus_dashboard['all']) && !empty($DP_AC_cus_dashboard['all'])){
				$DP_AC_cus_dashboard_url = $DP_AC_cus_dashboard['all'];
			} else {
				if(isset($DP_AC_cus_dashboard[$current_user->roles[0]])){
					$DP_AC_cus_dashboard_url = $DP_AC_cus_dashboard[$current_user->roles[0]];
				}
			}
			if(!empty($DP_AC_cus_dashboard_url)){
				echo '<style>

				#wpbody, #wpfooter, .clear{
					display: none !important;
				}
				
				
				#DP-AC-custom-dashboard {
					--top: 46px;
					position: fixed;
					left: 0;
					top: var(--top);
					height: calc(100% - var(--top));
					width: 100%;
				}
				
				@media only screen and (min-width: 782px) {
					#DP-AC-custom-dashboard {
						--top: 32px;
						--left: 160px;
						left: var(--left);
						width: calc(100% - var(--left));
					}
				
					.auto-fold #DP-AC-custom-dashboard {
						--left: 36px;
					}
				
				}
				
				@media only screen and (min-width: 960px) {
				
					.auto-fold #DP-AC-custom-dashboard {
						--left: 160px;
					}
					
					.folded #DP-AC-custom-dashboard {
						--left: 36px;
					}
				
				}
				
				
				html:not(.wp-toolbar) #DP-AC-custom-dashboard {
					--top: 0px;
				}
				
			
				</style>';

				echo '<iframe id="DP-AC-custom-dashboard" src="'.esc_url($DP_AC_cus_dashboard_url).'"></iframe>';
				//echo '<embed id="DP-AC-custom-dashboard" src="'.esc_url($DP_AC_cus_dashboard_url).'"/>';
			}
		}
	}

	public function custom_dashoard_page_nonlogin_redirect(){
		$DP_AC_cus_dashboard = get_option('DP_AC_cus_dashboard');
		if(!is_array($DP_AC_cus_dashboard)){
			$DP_AC_cus_dashboard = array();
		}
		$all_url = '';
		if(isset($DP_AC_cus_dashboard['all']) && !empty($DP_AC_cus_dashboard['all'])){
			$all_url = $DP_AC_cus_dashboard['all'];
		}
		if(!is_user_logged_in()){
			if(!empty($DP_AC_cus_dashboard)){
				$current_url = $this->get_current_page_url();
				if(!empty($all_url)){
					if($current_url == $all_url){
						wp_redirect(site_url());
						exit;
					}
				} else {
					foreach($DP_AC_cus_dashboard as $role => $url){
						if($current_url == $url){
							wp_redirect(site_url());
							exit;
						}
					}
				}
			}
		} else {
			global $current_user;
			if(!empty($DP_AC_cus_dashboard) && $current_user->ID != $this->DP_AC_super_admin){
				$current_url = $this->get_current_page_url();
				if(empty($all_url)){
					$this_roles = ( array ) $current_user->roles;
					$check_role = current($this_roles);
					foreach($DP_AC_cus_dashboard as $inrole => $inurl){
						if ( $current_url === $inurl && $inrole !== $check_role && $DP_AC_cus_dashboard[$check_role] !== $inurl ){
							wp_redirect(site_url());
							exit;
						}
					}
				}
			}
		}
	}

	public function add_iframe_loaded_class(){
		$DP_AC_target_parent = get_option('DP_AC_target_parent');
		$html = (empty($DP_AC_target_parent) || $DP_AC_target_parent == "yes" ? "<base target='_parent'>" : "")."<style>
			html.loaded-in-iframe{
				margin-top: 0 !important;
			}
			html.loaded-in-iframe #wpadminbar {
				display: none!important;
			}
		</style>
		<script>
			function inIframe() {
				try {
					return window.self !== window.top;
				} catch (Exception) {
					return true;
				}
			}

			if (inIframe()) {
				document.getElementsByTagName('html')[0].classList.add('loaded-in-iframe');
			}
		</script>";
		$DP_AC_cus_dashboard = get_option('DP_AC_cus_dashboard');
		if(!is_array($DP_AC_cus_dashboard)){
			$DP_AC_cus_dashboard = array();
		}
		if(!empty($DP_AC_cus_dashboard)){
			$current_url = $this->get_current_page_url();
			if(isset($DP_AC_cus_dashboard['all']) && !empty($DP_AC_cus_dashboard['all'])){
				$all_url = $DP_AC_cus_dashboard['all'];
			}
			if(!empty($all_url)){
				if($current_url == $all_url){
					echo $html;
				}
			} else {
				foreach($DP_AC_cus_dashboard as $role => $url){
					if($current_url == $url){
						echo $html;
						break;
					}
				}
			}
		}
	}

	public function hide_admin_bar($default_state){
		$DP_AC_topbar = get_option('DP_AC_topbar');
		if(!empty($DP_AC_topbar)){
			$excluded_roles = array();
			foreach($DP_AC_topbar as $key => $val){
				if($val == "yes"){
					$excluded_roles[] = $key;
				}
			}
			if(!empty($excluded_roles)){
				return is_user_logged_in() && ! array_intersect( wp_get_current_user()->roles, $excluded_roles);
			} else {
				return $default_state;
			}
		}

		return $default_state;
	}

	public function login_headerURL($url){
		$DP_AC_logoURL = get_option('DP_AC_logoURL');
		if(!empty($DP_AC_logoURL)){
			return $DP_AC_logoURL;
		}
		return $url;
	}
}

new DP_AC_admin();
