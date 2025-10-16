<div class="custom-menu-item <?php if ($this_menu->parent_id > 0) {
                                    echo 'child';
                                } ?>">
    <div class="view">
        <?php
        if ($this_menu->dashicon != "") {
            echo '<div class="icon">
                    <div class="dashicons ' . esc_attr($this_menu->dashicon) . '"></div>
                </div>';
        }
        if ($this_menu->svg != "") {
            echo '<div class="icon">' . wp_kses_post($this_menu->svg) . '</div>';
        }
        ?>
        <div class="name">
            <h2><?php echo esc_attr($this_menu->name); ?></h2>
        </div>
        <div class="actions">
            <button class="button-secondary custom-menu-item--edit">Edit</button>
        </div>
    </div>
    <div class="edit custom-menu-form">
        <p class="required-fields" style="display:none;">Name and Link are required fields</p>

        <div class="input-wrap">
            <label for="">Item Name</label>
            <input type="text" class="not-empty custom-menu-field" name="name" value="<?php echo esc_attr($this_menu->name); ?>" id="">
        </div>

        <div class="input-wrap">
            <label for="">Dashicon Name</label>
            <div class="icon-picker-toggle">
                <input type="text" class="custom-menu-field" name="dashicon" id="" value="<?php echo esc_attr($this_menu->dashicon); ?>">
                <div class="icon-preview-trigger dashicons dashicons-menu"></div>
                <?php include "iconPicker.php"; ?>
            </div>
        </div>

        <div class="input-wrap">
            <label for="">Or Paste Custom SVG Code</label>
            <input type="text" class="custom-menu-field" name="svg" value="<?php echo wp_kses_post($this_menu->svg); ?>" id="">
        </div>

        <div class="input-wrap">
            <label for="">Link</label>
            <input type="text" class="not-empty custom-menu-field" name="link" value="<?php echo esc_attr($this_menu->link); ?>" id="">
        </div>

        <div class="dp__switch">
            <label>
                <input type="checkbox" name="is_target_blank" class="custom-menu-field" <?php if ($this_menu->is_target_blank == "1") {
                                                                                            echo 'checked';
                                                                                        } ?> value="<?php echo esc_attr($this_menu->is_target_blank); ?>">
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
            <input type="number" class="custom-menu-field" name="priority" min="0" value="<?php echo esc_attr($this_menu->priority); ?>" id="">
        </div>

        <div class="input-wrap">
            <label for="">Item Parent</label>
            <select class="custom-menu-field" name="parent_id">
                <?php
                echo $this->get_custom_menu_parents(esc_attr($this_menu->id), esc_attr($this_menu->parent_id));
                ?>
            </select>
        </div>
        
        <div class="input-wrap">
            <label for="">Capability</label>
            <select name="capability" class="custom-menu-field">
                <?php
                echo $this->get_capabilities_html(esc_attr($this_menu->capability), $all_capabilities);
                ?>
            </select>
        </div>

        <div class="dp__switch">
            <label>
                <input type="checkbox" name="is_me_only" class="custom-menu-field" <?php if ($this_menu->is_me_only == "1") {
                                                                                        echo 'checked';
                                                                                    } ?> value="<?php echo esc_attr($this_menu->is_me_only); ?>">
                <div class="rwmb-switch-status">
                    <span class="rwmb-switch-slider"></span>
                    <span class="rwmb-switch-on">On</span>
                    <span class="rwmb-switch-off"></span>
                </div>
                <div class="role-name">Available for <?php echo $current_user->user_login; ?> only</div>
            </label>
        </div>

        <div class="actions">
            <button class="button-secondary custom-menu-item--delete" data-id="<?php echo esc_attr($this_menu->id); ?>">Delete</button>
            <button class="button-secondary custom-menu-item--cancel" data-id="<?php echo esc_attr($this_menu->id); ?>">Cancel</button>
            <button data-type="update" class="button-secondary custom-menu-item--save" data-id="<?php echo esc_attr($this_menu->id); ?>">Save</button>
        </div>
    </div>
</div>