<div id="user_roles" class="tabcontent" style="display: none;">
    <div class="DP_AC_admin_card">

        <?php if( $status !== false && $status == 'valid' ){ ?>
            <?php if($this->DP_AC_super_admin != '' && $current_user->ID == $this->DP_AC_super_admin){ ?>
            <div class="DP_AC_admin_header">
                <h2>Manage Admins</h2>
            </div>
            <div class="DP_AC_admin_body">
                <select class="select-option" id="DP_AC_super_admin">
                    <?php
                    $DP_AC_super_admin = get_option('DP_AC_super_admin');
                    $admin_users  = get_users(array('role' => 'administrator'));
                    $html = '<option value="" '.(($this->DP_AC_super_admin == "") ? 'selected' : '' ).'>--- Assign super admin ---</option>';
                    foreach($admin_users as $admin_user){
                        $html .= '<option value="'.$admin_user->ID.'" '.(($DP_AC_super_admin == $admin_user->ID) ? 'selected' : '' ).'>'.$admin_user->user_login.'</option>';
                    }
                    echo $html;
                    ?>
                </select>
                <p><b>Note:</b> There can be only one Super admin. Not selected admins will have limited options.</p>
            </div>
            <?php } ?>

            <div class="DP_AC_admin_body">
                <div class="panel" id="panel-user_roles-tools">
                    <div class="panel-body clearfix">
                    </div>
                    <div class="panel-body clearfix">
                        <?php
                            $DP_AC_disable_wpac = get_option('DP_AC_disable_wpac');
                            echo '<div class="dp__switch">    
                                <label>
                                    <input type="checkbox" id="DP_AC_disable_wpac" '.((!empty($DP_AC_disable_wpac) && $DP_AC_disable_wpac == "yes") ? 'checked' : '').' />
                                    <div class="rwmb-switch-status">
                                        <span class="rwmb-switch-slider"></span>
                                        <span class="rwmb-switch-on">On</span>
                                        <span class="rwmb-switch-off"></span>
                                    </div>
                                    <div class="role-name">Disable WP Admin Cleaner for other admins</div>
                                </label>
                            </div>';
                        ?>
                    </div>
                </div>
            </div>
        <?php } ?>
        
        <div class="DP_AC_admin_header">
            <h2>Make it available for other than admins</h2>
            <p class="no-space">This will hide menu items per current user. If you need to hide it per user roles <button class="inline-bth" onclick="open_DP_AC_Tab(event, 'rb_settings')" data-id="rb_settings">click here</button>.</p>
        </div>
            
        <div class="DP_AC_admin_body">
            <div class="panel" id="panel-user_roles-tools">
                <div class="panel-body clearfix">
                    <?php echo $user_roles_message; ?>
                </div>
            </div>
        </div>
    </div>
</div>