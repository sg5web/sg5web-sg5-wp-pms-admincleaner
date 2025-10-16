<?php
global $current_user, $wpdb;
$all_capabilities = $this->get_all_capabilities();
?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Selecting the elements
        var registerButton = document.querySelector('.register-menu.button-primary');
        var cancelButton = document.querySelector('.register-new-custom-menu-item .custom-menu-item--cancel');
        var registerForm = document.querySelector('.register-new-custom-menu-item');

        // Event listener for the register button
        registerButton.addEventListener('click', function() {
            registerButton.style.display = 'none'; // Hide the register button
            registerForm.style.display = 'grid'; // Show the register form as a grid
        });

        // Event listener for the cancel button
        cancelButton.addEventListener('click', function() {
            registerForm.style.display = 'none'; // Hide the register form
            registerButton.style.display = 'inline-block'; // Show the register button as inline-block
        });
    });
</script>


<script>
document.addEventListener('DOMContentLoaded', function() {
    // Clicking outside any icon picker should close all icon pickers
    document.addEventListener('click', function(event) {
        if (!event.target.closest('.icon-picker-toggle')) {
            document.querySelectorAll('.menuIconlist').forEach(list => {
                list.style.display = 'none';
            });
        }
    });
});
</script>



<div id="custom_menus" class="tabcontent" <?php echo $custom_menus_style; ?>>
    <div class="DP_AC_admin_card">
        <div class="DP_AC_admin_header">
            <h2>Custom Menus Settings</h2>
            <p class="no-space">Create quick shortcuts and customize your and the user's experience by adding custom menus.</p>
            <a href="#" style="margin-top: 1rem;" class="register-menu button-primary">Register menu item</a>

            <div class="register-new-custom-menu-item custom-menu-form" style="display: none;">
                <p class="required-fields" style="display:none;">Name and Link are required fields</p>
                <div class="input-wrap">
                    <label for="">Item Name</label>
                    <input type="text" class="not-empty custom-menu-field" name="name" id="">
                </div>

                <div class="input-wrap icons">
                    <label for="">Dashicon Name</label>
                    <div class="icon-picker-toggle">
                        <input type="text" class="custom-menu-field" name="dashicon" id="">
                        <div class="icon-preview-trigger dashicons dashicons-menu"></div>
                        <?php include "iconPicker.php"; ?>
                    </div>
                </div>

                <div class="input-wrap icons">
                    <label for="">Or Paste Custom SVG Code</label>
                    <input type="text" class="custom-menu-field" name="svg" id="">
                </div>

                <div class="input-wrap">
                    <label for="">Link</label>
                    <input type="text" class="not-empty custom-menu-field" name="link" id="">
                </div>

                <div class="dp__switch">
                    <label>
                        <input type="checkbox" name="is_target_blank" class="custom-menu-field" value="0">
                        <div class="rwmb-switch-status">
                            <span class="rwmb-switch-slider"></span>
                            <span class="rwmb-switch-on">On</span>
                            <span class="rwmb-switch-off"></span>
                        </div>
                        <div class="role-name">Open in new tab</div>
                    </label>
                </div>

                <div class="input-wrap">
                    <div class="label-with-link">
                        <label for="">Menu priority</label>
                        <a target="_blank" href="https://developer.wordpress.org/reference/functions/add_menu_page/#menu-structure">View menu priority list ↗</a>
                    </div>
                    <input type="number" class="custom-menu-field" name="priority" min="0" value="0" id="">
                </div>

                <div class="input-wrap">
                    <label for="">Item Parent</label>
                    <select class="custom-menu-field" name="parent_id">
                        <?php
                        echo $this->get_custom_menu_parents();
                        ?>
                    </select>
                </div>

                <div class="input-wrap">
                    <label for="">Capability</label>
                    <select name="capability" class="custom-menu-field">
                        <?php
                        echo $this->get_capabilities_html('read', $all_capabilities);
                        ?>
                    </select>
                </div>

                <div class="dp__switch">
                    <label>
                        <input type="checkbox" name="is_me_only" class="custom-menu-field" value="0">
                        <div class="rwmb-switch-status">
                            <span class="rwmb-switch-slider"></span>
                            <span class="rwmb-switch-on">On</span>
                            <span class="rwmb-switch-off"></span>
                        </div>
                        <div class="role-name">Available for <?php echo $current_user->user_login; ?> only</div>
                    </label>
                </div>

                <div class="actions">
                    <button class="button-secondary custom-menu-item--cancel">Cancel</button>
                    <button data-type="register" class="button-secondary custom-menu-item--save">Save</button>
                </div>
            </div>
        </div>


        <div class="saved-custom-menus" id="">
            <?php echo $this->get_custom_menus_html($this->get_custom_menus()); ?>
        </div>
    </div>
</div>