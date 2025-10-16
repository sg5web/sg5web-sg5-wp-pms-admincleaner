<div id="adminLogin" class="tabcontent" style="display: none;">
    <div class="DP_AC_admin_card">

        <?php if( $status !== false && $status == 'valid' ){ ?>
        <div class="DP_AC_admin_header">
            <h2>Login page styles:</h2>
        </div>
        <div class="DP_AC_admin_body">
            <div class="panel" id="panel-adminLogin-tools">
                <div class="panel-body clearfix">
                    <?php 
                        $DP_AC_logo = get_option('DP_AC_logo');
                        $DP_AC_form_bg = get_option('DP_AC_form_bg');
                        $DP_AC_logow = get_option('DP_AC_logow');
                        $DP_AC_logoh = get_option('DP_AC_logoh');
                        $DP_AC_logoURL = get_option('DP_AC_logoURL');
                        $DP_AC_bg_img = get_option('DP_AC_bg_img');
                        $DP_AC_bg = get_option('DP_AC_bg');
                        $DP_AC_bgo = get_option('DP_AC_bgo');
                        $DP_AC_color = get_option('DP_AC_color');
                        $DP_AC_btn_bg = get_option('DP_AC_btn_bg');
                        $DP_AC_btn_bgh = get_option('DP_AC_btn_bgh');
                        $DP_AC_btn_bd = get_option('DP_AC_btn_bd');
                        $DP_AC_btn_bdh = get_option('DP_AC_btn_bdh');
                        $DP_AC_btn_color = get_option('DP_AC_btn_color');
                        $DP_AC_btn_colorh = get_option('DP_AC_btn_colorh');
                        $DP_AC_layout = get_option('DP_AC_layout');
                        $DP_AC_login_css = get_option('DP_AC_login_css');
                        if(!empty($DP_AC_login_css)){
                            $DP_AC_login_css = base64_decode($DP_AC_login_css);
                        }
                    ?>
                    
                    
                    <h3>Layout options</h3>

                    <div class="DP-AC-fields custom-css">
                        <!-- <label class="description" for="DP_AC_layout">Layout Option</label> -->
                        <select class="regular-text adminlogin" id="DP_AC_layout" name="DP_AC_layout">
                            <?php
                                $layouts = array(
                                    '' => 'Choose layout',
                                    'wp-login--plain' => 'Plain',
                                    'wp-login--plain-dark' => 'Plain dark',
                                    'wp-login--halfscreen-image' => 'Half Screen Image',
                                    'wp-login--halfscreen-image-dark' => 'Half Screen Image Dark',
                                    'custom' => 'Write your own CSS',
                                );
                                foreach($layouts as $key => $val){
                                    echo '<option value="'.$key.'" '.((!empty($DP_AC_layout) && $DP_AC_layout == $key) ? 'selected' : '').'>'.$val.'</option>';
                                }
                            ?>
                        </select>
                        <div class="DP_AC-editor DP_AC_login_css" style="<?php if($DP_AC_layout != "custom"){ echo 'display:none'; } ?>">
                            <textarea id="DP_AC_login_css"><?php echo $DP_AC_login_css; ?></textarea>									
                        </div>
                        <div class="DP_AC-editor DP_AC_login_css" style="<?php if($DP_AC_layout != "custom"){ echo 'display:none'; } ?>">
                            <div class="css-files">
                                Themes CSS Files: 
                                <a target="_blank" href="https://gist.github.com/krstivoja/f749dc371e218a4a2d8f04bda6d913ea">Plain ↗</a> | 
                                <a target="_blank" href="https://gist.github.com/krstivoja/5b07a95196edc8c1bb23699a8a1104ee">Plain dark ↗</a> | 
                                <a target="_blank" href="https://gist.github.com/krstivoja/bb70a6c5e07e3ab52dba68292be0c7af">Half Screen Image ↗</a> | 
                                <a target="_blank" href="https://gist.github.com/krstivoja/456a7c25428f835e1bc88c5535eddef8">Half Screen Image Dark ↗</a>
                            </div>
                        </div>
                    </div>

                    <div class="option-space"></div>

                    <h3>Page</h3>

                    <div class="DP-AC-fields">
                        <input type="text" class="regular-text adminlogin color-picker" id="DP_AC_color" name="DP_AC_color" value="<?php echo esc_attr($DP_AC_color); ?>" data-alpha-enabled="true" data-default-color="#3c434a" />
                        <label class="description" for="DP_AC_color">Text Color</label>
                    </div>								
                    <div class="DP-AC-fields">
                        <input type="text" class="regular-text adminlogin color-picker" id="DP_AC_bg" name="DP_AC_bg" value="<?php echo esc_attr($DP_AC_bg); ?>" data-alpha-enabled="true" data-default-color="#f0f0f1" />
                        <label class="description" for="DP_AC_bg">Background Color</label>
                    </div>

                    <div class="DP-AC-fields">
                        <input type="text" class="regular-text adminlogin color-picker" id="DP_AC_form_bg" name="DP_AC_form_bg" value="<?php echo esc_attr($DP_AC_form_bg); ?>" data-alpha-enabled="true" data-default-color="#f0f0f1" />
                        <label class="description" for="DP_AC_form_bg">Form Background Color</label>
                    </div>

                    <div class="DP-AC-fields">
                        <input type="text" class="regular-text adminlogin color-picker" id="DP_AC_bgo" name="DP_AC_bgo" value="<?php echo esc_attr($DP_AC_bgo); ?>" data-alpha-enabled="true" data-default-color="#f0f0f1" />
                        <label class="description" for="DP_AC_bgo">Background Image Overlay</label>
                    </div>

                    <div class="option-space"></div>

                    <div class="file-upload">
                        <label>Upload file or paste CDN link</label>
                        <span>
                            <input type="text" id="DP_AC_bg_img" name="DP_AC_bg_img" class="script-file regular-text adminlogin" value="<?php echo esc_attr($DP_AC_bg_img); ?>">
                            <button class="script-file-upload action-svg-icon">
                                <svg width="24" height="24" >
                                    <use xlink:href="#upload-icon"></use>
                                </svg>
                                Background Image
                            </a>
                        <span>
                    </div>

                    <div class="option-space"></div>

                    <h3>Logo</h3>

                    <div class="file-upload">
                        <label>Upload file or paste CDN link</label>
                        <span>
                            <input type="text" id="DP_AC_logo" name="DP_AC_logo" class="script-file regular-text adminlogin" value="<?php echo esc_attr($DP_AC_logo); ?>">
                            <button class="script-file-upload action-svg-icon">
                                <svg width="24" height="24" >
                                    <use xlink:href="#upload-icon"></use>
                                </svg>
                                Logo
                            </a>
                        <span>
                    </div>
                    <div class="logo-w-h">
                        <div class="logo-width">
                            <label>Logo Width (px)</label>
                            <input type="number" min="1" class="regular-text adminlogin" id="DP_AC_logow" name="DP_AC_logow" value="<?php echo esc_attr($DP_AC_logow); ?>" />									
                        </div>
                        <div class="logo-width">
                            <label>Logo height (px)</label>
                            <input type="number" min="1" class="regular-text adminlogin" id="DP_AC_logoh" name="DP_AC_logoh" value="<?php echo esc_attr($DP_AC_logoh); ?>" />									
                        </div>
                    </div>

                    <div class="logo-url">
                        <label>Logo URL</label>
                        <input type="text" class="regular-text adminlogin" id="DP_AC_logoURL" name="DP_AC_logoURL" value="<?php echo esc_url($DP_AC_logoURL); ?>" />									
                    </div>

                    <div class="option-space"></div>

                    <h3>Button</h3>

                    <div class="DP-AC-fields">
                        <input type="text" class="regular-text adminlogin color-picker" id="DP_AC_btn_color" name="DP_AC_btn_color" value="<?php echo esc_attr($DP_AC_btn_color); ?>" data-alpha-enabled="true" data-default-color="#ffffff" />
                        <label class="description" for="DP_AC_btn_color"> Text Color</label>
                    </div>
                    <div class="DP-AC-fields">
                        <input type="text" class="regular-text adminlogin color-picker" id="DP_AC_btn_colorh" name="DP_AC_btn_colorh" value="<?php echo esc_attr($DP_AC_btn_colorh); ?>" data-alpha-enabled="true" data-default-color="#ffffff" />
                        <label class="description" for="DP_AC_btn_colorh"> Text Color - Hover</label>
                    </div>
                    <div class="DP-AC-fields">
                        <input type="text" class="regular-text adminlogin color-picker" id="DP_AC_btn_bg" name="DP_AC_btn_bg" value="<?php echo esc_attr($DP_AC_btn_bg); ?>" data-alpha-enabled="true" data-default-color="#2271b1" />
                        <label class="description" for="DP_AC_btn_bg"> Background Color</label>
                    </div>
                    <div class="DP-AC-fields">
                        <input type="text" class="regular-text adminlogin color-picker" id="DP_AC_btn_bgh" name="DP_AC_btn_bgh" value="<?php echo esc_attr($DP_AC_btn_bgh); ?>" data-alpha-enabled="true" data-default-color="#135e96" />
                        <label class="description" for="DP_AC_btn_bgh"> Background Color - Hover</label>
                    </div>
                    <div class="DP-AC-fields">
                        <input type="text" class="regular-text adminlogin color-picker" id="DP_AC_btn_bd" name="DP_AC_btn_bd" value="<?php echo esc_attr($DP_AC_btn_bd); ?>" data-alpha-enabled="true" data-default-color="#2271b1" />
                        <label class="description" for="DP_AC_btn_bd"> Border Color</label>
                    </div>
                    <div class="DP-AC-fields">
                        <input type="text" class="regular-text adminlogin color-picker" id="DP_AC_btn_bdh" name="DP_AC_btn_bdh" value="<?php echo esc_attr($DP_AC_btn_bdh); ?>" data-alpha-enabled="true" data-default-color="#135e96" />
                        <label class="description" for="DP_AC_btn_bdh"> Border Color - Hover</label>
                    </div>
                
                    <svg display="none">
                        <symbol width="24" height="24" viewBox="0 0 24 24" id="upload-icon" fill="currentColor">
                            <path d="M0 0h24v24H0z" fill="none"></path><path d="M5 4v2h14V4H5zm0 10h4v6h6v-6h4l-7-7-7 7z"></path>
                        </symbol>


                        <symbol width="24" height="24" viewBox="0 0 24 24" id="delete-icon" fill="currentColor">
                            <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"></path>
                        </symbol>

                        <symbol width="24" height="24" viewBox="0 0 24 24" id="copy-icon" fill="currentColor">
                            <path d="M0 0h24v24H0z" fill="none"></path>
                            <path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"></path>
                        </symbol>


                        <symbol width="24" height="24" viewBox="0 0 128 128" id="js-icon" fill="currentColor">
                            <path fill="#F0DB4F" d="M1.408 1.408h125.184v125.185h-125.184z"></path>
                            <path fill="#323330" d="M116.347 96.736c-.917-5.711-4.641-10.508-15.672-14.981-3.832-1.761-8.104-3.022-9.377-5.926-.452-1.69-.512-2.642-.226-3.665.821-3.32 4.784-4.355 7.925-3.403 2.023.678 3.938 2.237 5.093 4.724 5.402-3.498 5.391-3.475 9.163-5.879-1.381-2.141-2.118-3.129-3.022-4.045-3.249-3.629-7.676-5.498-14.756-5.355l-3.688.477c-3.534.893-6.902 2.748-8.877 5.235-5.926 6.724-4.236 18.492 2.975 23.335 7.104 5.332 17.54 6.545 18.873 11.531 1.297 6.104-4.486 8.08-10.234 7.378-4.236-.881-6.592-3.034-9.139-6.949-4.688 2.713-4.688 2.713-9.508 5.485 1.143 2.499 2.344 3.63 4.26 5.795 9.068 9.198 31.76 8.746 35.83-5.176.165-.478 1.261-3.666.38-8.581zm-46.885-37.793h-11.709l-.048 30.272c0 6.438.333 12.34-.714 14.149-1.713 3.558-6.152 3.117-8.175 2.427-2.059-1.012-3.106-2.451-4.319-4.485-.333-.584-.583-1.036-.667-1.071l-9.52 5.83c1.583 3.249 3.915 6.069 6.902 7.901 4.462 2.678 10.459 3.499 16.731 2.059 4.082-1.189 7.604-3.652 9.448-7.401 2.666-4.915 2.094-10.864 2.07-17.444.06-10.735.001-21.468.001-32.237z"></path>
                        </symbol>2
                    </svg>
                </div>
            </div>
        </div>
        <?php } ?>

        <div class="DP_AC_admin_header">
            <h2>Login page settings:</h2>
        </div>
        <div class="DP_AC_admin_header">
            <div class="panel" id="panel-adminLogin-tools">
                <div class="panel-body clearfix">
                    <?php if( $status !== false && $status == 'valid' ){ ?>
                    <?php 
                        $DP_AC_al_option = get_option('DP_AC_al_option');
                        $option_checked = ($DP_AC_al_option == "yes") ? 'checked' : '';
                    ?>
                    <div class="dp__switch">
                        <label>
                            <input name="DP_AC_al_option" type="checkbox" value="<?php echo esc_attr($DP_AC_al_option); ?>" <?php echo esc_attr($option_checked); ?> />
                            <div class="rwmb-switch-status">
                                <span class="rwmb-switch-slider"></span>
                                <span class="rwmb-switch-on">On</span>
                                <span class="rwmb-switch-off"></span>
                            </div>
                            <div class="role-name">Enable admin login settings</div>
                        </label>
                    </div>
                    
                    <div class="option-space"></div>
                    <?php } ?>

                    <?php echo $adminLogin_message; ?>
                </div>
            </div>
        </div>
        <div class="DP_AC_admin_body">
            <a href="#" class="of-save-adminlogin button-primary" style="margin-top: 15px;">Save Changes <span class="spinner"></span></a>
        </div>	

    </div>
</div>