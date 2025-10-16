<div id="rb_settings" class="tabcontent" style="display: none;">
    <div class="DP_AC_admin_card">
        <div class="DP_AC_admin_header">
            <h2>Hide menu items per user role</h2>
            <p class="no-space">This will hide menu items per user role.</p>
        </div>
        <?php
            $super_admin = get_user_by('ID', $this->DP_AC_super_admin);
        ?>
        <?php if( $status !== false && $status == 'valid' ){ ?>
        <div class="DP_AC_admin_header">
            <h2>Select role:</h2>
            <select class="select-option" id="rb_settings_role">
            <option value="">---Select role---</option>
            <?php
            global $wp_roles;
            $all_roles = $wp_roles->roles;
    
            $html = '';
            foreach($all_roles as $role_slug => $role){
                if($role_slug == "administrator" && $current_user->ID != $this->DP_AC_super_admin){ continue; }
                $html .= '<option value="'.$role_slug.'">'.$role['name'].'</option>';
            }
            echo $html;
            ?>
            </select>
            <?php if($current_user->ID == $this->DP_AC_super_admin){ ?>
                <p><b>Administrator option</b> will apply to administrators that aren't super admins (<?php echo $super_admin->data->user_login; ?>). </p>
            <?php } ?>
        </div>
        <?php } ?>
        <div class="DP_AC_admin_body" id="per-role-body">
            <div class="panel" id="panel-rb_settings-tools">
                <div class="panel-body clearfix">
                    <?php echo $rb_settings_message; ?>
                </div>
            </div>
        </div>
    </div>
</div>