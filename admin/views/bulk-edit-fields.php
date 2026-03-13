<?php
/**
 * Bulk edit fields for page group assignment
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<fieldset class="inline-edit-col-right">
    <div class="inline-edit-col">
        <?php wp_nonce_field('page_organizer_bulk_edit', 'page_organizer_bulk_edit_nonce'); ?>
        <label>
            <span class="title"><?php _e('Page Group', 'page-organizer'); ?></span>
            <select name="page_organizer_group_bulk" class="page-organizer-group-bulk-select">
                <option value="-1"><?php _e('— No Change —', 'page-organizer'); ?></option>
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

