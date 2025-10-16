<div id="license" class="tabcontent" <?php echo $license_style; ?>>
    <div class="panel" id="panel-license-tools">
        <div class="panel-body clearfix">
            <div id="License" class="of-tabs">
                <?php if(in_array('administrator', $current_user->roles)){ ?>
                <form method="post" action="options.php">

                    <?php settings_fields('DP_AC_license'); ?>

                    
                    <div id="licence-form" class="DP_AC_admin_card">
                    
                        <div class="DP_AC_admin_header">
                            <h3>Welcome to WP Admin Cleaner</h3>
                            <h4>To get you started, please <b>activate your license first</b></h4>			
                        </div><!-- End of DP_AC_admin_header -->
                        <div class="DP_AC_admin_body">
                            <label class="description" for="DP_AC_license_key"><?php _e('Enter your license key'); ?></label>
                            <input id="DP_AC_license_key" name="DP_AC_license_key" type="password" class="regular-text" value="<?php esc_attr_e( $license ); ?>" />	

                            <?php if( $status !== false && $status == 'valid' ) { ?>
                                <div class="licence-status">
                                    Status: <span class="licence-status-active"><?php _e('active'); ?></span>
                                </div>

                                <?php wp_nonce_field( 'DP_AC_nonce', 'DP_AC_nonce' ); ?>
                            
                                <input type="submit" class="button-secondary " class="DP_AC_license_deactivate" name="DP_AC_license_deactivate" value="<?php _e('Deactivate License'); ?>"/>
                            <?php } else { ?>
                                <?php wp_nonce_field( 'DP_AC_nonce', 'DP_AC_nonce' ); ?>
                                <?php submit_button( 'Activate License', 'primary', 'DP_AC_license_activate', true, array() ); ?>
                                <?php } ?>	
                        </div><!-- End of DP_AC_admin_body -->

                    </div><!-- End of DP_AC_admin_card -->
                </form>	
                <?php } ?>

                <div class="DP_AC_admin_card">
                    <div class="DP_AC_admin_header">
                            <h3>Plugin Settings</h3>
                    </div>
                    <div class="DP_AC_admin_body">
                        <?php
                            $DP_AC_remove_data = get_option('DP_AC_remove_data');
                            echo '<div class="dp__switch">    
                                <label>
                                    <input type="checkbox" id="DP_AC_remove_data" '.((!empty($DP_AC_remove_data) && $DP_AC_remove_data == "yes") ? 'checked' : '').' />
                                    <div class="rwmb-switch-status">
                                        <span class="rwmb-switch-slider"></span>
                                        <span class="rwmb-switch-on">On</span>
                                        <span class="rwmb-switch-off"></span>
                                    </div>
                                    <div class="role-name">Remove all data from database on plugin uninstall</div>
                                </label>
                            </div>';
                        ?>
                    </div>
                </div>	

                <div class="DP_AC_admin_card">
                    
                        <div class="DP_AC_admin_header">
                            <h3>Recomended links</h3>
                        </div><!-- End of DP_AC_admin_header -->
                        <div class="DP_AC_admin_body">
                            <h4>Admin area</h4>
                            <a target="_blank" href="https://dplugins.com/login/" class="DP_AC-links">https://dplugins.com/login/</a>
                            <p>Access your account, download files, licenses or generate the invoice</p> 
                            <br>
                            <br>
                            
                            <h4>Documentation</h4> 
                            <a target="_blank" href="https://dplugins.com/support/" class="DP_AC-links">https://dplugins.com/support/</a>						
                            <br>
                            <br>

                            <h4>Support</h4> 
                            <a target="_blank" href="https://docs.dplugins.com/wp-admin-cleaner/wp-admin-cleaner/" class="DP_AC-links">https://docs.dplugins.com/</a>						
                            <br>
                            <br>
                            <br>		

                            <h4>Facebook group</h4> 
                            <a target="_blank" href="https://www.facebook.com/groups/dplugins" class="DP_AC-links">https://www.facebook.com/groups/dplugins</a>
                            <br>
                            <br>

                            <h4>Youtube</h4> 
                            <a target="_blank" href="https://www.youtube.com/c/dPlugins" class="DP_AC-links">https://www.youtube.com/c/dPlugins</a>
                            <br>
                            <br>				
                        </div><!-- End of DP_AC_admin_body -->

                </div><!-- End of DP_AC_admin_card -->

            </div> <!-- End of Licence -->
        </div>
    </div>
</div>