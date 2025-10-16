<div id="admin_plugins" class="tabcontent" style="display: none;">
    <div class="DP_AC_admin_card">
        <div class="DP_AC_admin_header">
            <h2>Hide plugins</h2>
            <p>Note: for all admins and non-admins except super admin <b>(<?php echo $super_admin->data->user_login; ?>)</b>.</p>
        </div>
        
        <div class="DP_AC_admin_body">
            <div class="panel" id="panel-admin_plugins-tools">
                <div class="panel-body clearfix">
                    <?php echo $admin_plugins_message; ?>
                </div>
            </div>
        </div>
    </div>
</div>