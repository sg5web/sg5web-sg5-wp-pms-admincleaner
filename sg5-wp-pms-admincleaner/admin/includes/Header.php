<?php global $current_user; ?>
<header id="wp-admin-header">
    <h1>WP Admin Cleaner</h1>
    <!-- Tab links -->
    <h2 class="tab nav-tab-wrapper">
        <?php 
            if( $status === false && $status != 'valid' ){
                $license_active_tab = "nav-tab-active";
                $license_style = "style='display:block;'";
                $settings_active_tab = "";
                $settings_style = "style='display:none;'";
                $adminLogin_active_tab = "";
                $custom_dashboard_active_tab = "";
                $redirection_active_tab = "";
                $adminLogin_style = "style='display:none;'";
                $custom_dashboard_style = "style='display:none;'";
                $redirection_style = "style='display:none;'";
                $admin_plugins_style = "style='display:none;'";
                $rb_settings_style = "style='display:none;'";
                $mopr_settings_style = "style='display:none;'";
                $custom_menus_style = "style='display:none;'";
                $activate_license_message = '<h2 class="warning-licence-not-active">Activate licence to see the features.</h2>';
                $adminLogin_message = '<h2 class="warning-licence-not-active">Activate licence to see the features.</h2>';
                $custom_dashboard_message = '<h2 class="warning-licence-not-active">Activate licence to see the features.</h2>';
                $redirection_message = '<h2 class="warning-licence-not-active">Activate licence to see the features.</h2>';
                $user_roles_message = '<h2 class="warning-licence-not-active">Activate licence to see the features.</h2>';
                $rb_settings_message = '<h2 class="warning-licence-not-active">Activate licence to see the features.</h2>';
                $mopr_settings_message = '<h2 class="warning-licence-not-active">Activate licence to see the features.</h2>';
                $custom_menus_message = '<h2 class="warning-licence-not-active">Activate licence to see the features.</h2>';
                $admin_plugins_message = '<h2 class="warning-licence-not-active">Activate licence to see the features.</h2>';
                $theme_message = '<h2 class="warning-licence-not-active">Activate licence to see the features.</h2>';
                $reorder_message = '<h2 class="warning-licence-not-active">Activate licence to see the features.</h2>';
            } else {
                $license_active_tab = "";
                $license_style = "style='display:none;'";
                $rb_settings_style = "style='display:none;'";
                $mopr_settings_style = "style='display:none;'";
                $custom_menus_style = "style='display:none;'";
                $adminLogin_style = "style='display:none;'";
                $redirection_style = "style='display:none;'";
                $settings_active_tab = "nav-tab-active";
                $adminLogin_active_tab = "nav-tab-active";
                $admin_plugins_active_tab = "nav-tab-active";
                $settings_style = "style='display:block;'";
                $admin_plugins_style = "style='display:block;'";
                $activate_license_message = $this->admin_menus_options();
                $rb_settings_message = $this->rb_settings_menus_options();
                $mopr_settings_message = $this->mopr_settings_reorder_menus();
                $custom_menus_message = '';
                $admin_plugins_message = $this->admin_plugins();
                $user_roles_message = $this->user_roles_option();
                $theme_message = $this->theme_option();
                $reorder_message = $this->reorder_menus();
                $adminLogin_message = $this->adminLogin_form();
                $redirection_message = $this->redirection_form();
                $custom_dashboard_message = $this->custom_dashboard_form();
            }
        ?>
        <!-- <button class="tablinks nav-tab <?php echo $settings_active_tab; ?>" onclick="open_DP_AC_Tab(event, 'admin_settings')" data-id="admin_settings">Hide for "" only</button> -->
        <?php 
            $current_user = wp_get_current_user();
        ?>
        <button class="tablinks nav-tab <?php echo $settings_active_tab; ?>" onclick="open_DP_AC_Tab(event, 'admin_settings')" data-id="admin_settings">
            Hide for <b><?php echo $current_user->exists() ? esc_html($current_user->display_name) : '' ?></b> only
        </button>
        <?php if(in_array('administrator', $current_user->roles)){ ?>
            <button class="tablinks nav-tab" onclick="open_DP_AC_Tab(event, 'rb_settings')" data-id="rb_settings">Hide per Role</button>
        <?php } ?>
        <?php if($this->DP_AC_check_user_role_is_allowed()){ ?>
            <button class="tablinks nav-tab" onclick="open_DP_AC_Tab(event, 'reorder')" data-id="reorder">Menu Order <b><?php echo $current_user->exists() ? esc_html($current_user->display_name) : '' ?></b> only</button>
        <?php } ?>
        <?php if(in_array('administrator', $current_user->roles) && $current_user->ID == $this->DP_AC_super_admin){ ?>
            <button class="tablinks nav-tab" onclick="open_DP_AC_Tab(event, 'mopr_settings')" data-id="mopr_settings">Menu Order per Role</button>
        <?php } ?>
        <?php if(in_array('administrator', $current_user->roles) && $current_user->ID == $this->DP_AC_super_admin){ ?>
            <button class="tablinks nav-tab" onclick="open_DP_AC_Tab(event, 'admin_plugins')" data-id="admin_plugins">Hide Plugins</button>
            <button class="tablinks nav-tab" onclick="open_DP_AC_Tab(event, 'adminLogin')" data-id="adminLogin">WP Admin login page</button>
            <button class="tablinks nav-tab" onclick="open_DP_AC_Tab(event, 'redirection')" data-id="redirection">User Redirection after login</button>
            <button class="tablinks nav-tab" onclick="open_DP_AC_Tab(event, 'custom_dashboard')" data-id="custom_dashboard">Custom Dashboard</button>
        <?php } ?>
        <?php if($this->DP_AC_check_user_role_is_allowed()){ ?>
            <button class="tablinks nav-tab" onclick="open_DP_AC_Tab(event, 'theme')" data-id="theme">Dark Themes</button>
        <?php } ?>
        <?php if(in_array('administrator', $current_user->roles) && $current_user->ID == $this->DP_AC_super_admin){ ?>
            <button class="tablinks nav-tab" onclick="open_DP_AC_Tab(event, 'user_roles')" data-id="user_roles">Roles Settings</button>
        <?php } ?>
        <?php if(in_array('administrator', $current_user->roles) && $current_user->ID == $this->DP_AC_super_admin){ ?>
            <button class="tablinks nav-tab <?php echo $license_active_tab; ?>" onclick="open_DP_AC_Tab(event, 'license')" data-id="license">Plugin Settings & License</button>
        <?php } ?>
        <?php if(in_array('administrator', $current_user->roles)){ ?>
            <button class="tablinks nav-tab" onclick="open_DP_AC_Tab(event, 'custom_menus')" data-id="custom_menus">Custom Menus</button>
        <?php } ?>
    </h2>
</header>