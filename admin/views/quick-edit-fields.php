<?php
/**
 * Quick edit fields for page group assignment
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<fieldset class="inline-edit-col-right">
    <div class="inline-edit-col">
        <label>
            <span class="title"><?php _e('Page Group', 'page-organizer'); ?></span>
            <select name="page_organizer_group" class="page-organizer-group-select">
                <option value="0"><?php _e('Ungrouped', 'page-organizer'); ?></option>
                <?php foreach ($groups as $group): ?>
                    <option value="<?php echo esc_attr($group->id); ?>">
                        <?php echo esc_html($group->name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
    </div>
</fieldset>

