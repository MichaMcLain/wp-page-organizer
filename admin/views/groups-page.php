<?php
/**
 * Groups management page view
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$edit_group = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    global $wpdb;
    $groups_table = $wpdb->prefix . 'page_organizer_groups';
    
    // Check if table exists before querying
    if ($wpdb->get_var("SHOW TABLES LIKE '{$groups_table}'") == $groups_table) {
        $edit_group = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$groups_table} WHERE id = %d", intval($_GET['edit'])));
    }
}
?>

<div class="wrap">
    <h1>
        <?php _e('Page Groups', 'page-organizer'); ?>
        <button type="button" class="button button-secondary page-organizer-help-btn" style="margin-left: 10px;">
            <?php _e('Help & Instructions', 'page-organizer'); ?>
        </button>
    </h1>
    
    <?php settings_errors('page_organizer'); ?>
    
    <div class="page-organizer-admin">
        <div class="page-organizer-content">
            
            <!-- Add/Edit Group Form -->
            <div class="page-organizer-form-section">
                <h2><?php echo $edit_group ? __('Edit Group', 'page-organizer') : __('Add New Group', 'page-organizer'); ?></h2>
                
                <form method="post" action="">
                    <?php wp_nonce_field('page_organizer_groups'); ?>
                    
                    <?php if ($edit_group): ?>
                        <input type="hidden" name="action" value="update_group">
                        <input type="hidden" name="group_id" value="<?php echo esc_attr($edit_group->id); ?>">
                    <?php else: ?>
                        <input type="hidden" name="action" value="create_group">
                    <?php endif; ?>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="group_name"><?php _e('Group Name', 'page-organizer'); ?> <span class="required">*</span></label>
                            </th>
                            <td>
                                <input type="text" 
                                       id="group_name" 
                                       name="group_name" 
                                       value="<?php echo $edit_group ? esc_attr($edit_group->name) : ''; ?>" 
                                       class="regular-text" 
                                       required>
                                <p class="description"><?php _e('Enter a unique name for this group (e.g., "Core", "Services", "Areas").', 'page-organizer'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="group_description"><?php _e('Description', 'page-organizer'); ?></label>
                            </th>
                            <td>
                                <textarea id="group_description" 
                                          name="group_description" 
                                          rows="3" 
                                          class="large-text"><?php echo $edit_group ? esc_textarea($edit_group->description) : ''; ?></textarea>
                                <p class="description"><?php _e('Optional description for this group.', 'page-organizer'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="group_color"><?php _e('Color', 'page-organizer'); ?></label>
                            </th>
                            <td>
                                <div class="page-organizer-color-picker">
                                    <div class="preset-colors">
                                        <?php 
                                        $preset_colors = array(
                                            '#0073aa', // Blue (WordPress default)
                                            '#d63638', // Red
                                            '#00a32a', // Green
                                            '#ff6900', // Orange
                                            '#8b5cf6', // Purple
                                            '#f59e0b'  // Yellow/Amber
                                        );
                                        $current_color = $edit_group && isset($edit_group->color) ? $edit_group->color : '#0073aa';
                                        
                                        foreach ($preset_colors as $color): ?>
                                            <div class="color-option <?php echo $current_color === $color ? 'selected' : ''; ?>" 
                                                 data-color="<?php echo esc_attr($color); ?>" 
                                                 style="background-color: <?php echo esc_attr($color); ?>;"
                                                 title="<?php echo esc_attr($color); ?>"></div>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="custom-color-input">
                                        <label for="custom_color"><?php _e('Custom:', 'page-organizer'); ?></label>
                                        <input type="color" 
                                               id="custom_color" 
                                               value="<?php echo esc_attr($current_color); ?>">
                                        <input type="text" 
                                               id="group_color" 
                                               name="group_color" 
                                               value="<?php echo esc_attr($current_color); ?>" 
                                               pattern="^#[a-fA-F0-9]{6}$" 
                                               maxlength="7" 
                                               class="small-text">
                                    </div>
                                </div>
                                <p class="description"><?php _e('Choose a color for this group. The color will be used for group labels in the pages list.', 'page-organizer'); ?></p>
                            </td>
                        </tr>
                    </table>
                    
                    <p class="submit">
                        <input type="submit" 
                               class="button-primary" 
                               value="<?php echo $edit_group ? __('Update Group', 'page-organizer') : __('Add Group', 'page-organizer'); ?>">
                        
                        <?php if ($edit_group): ?>
                            <a href="<?php echo admin_url('edit.php?post_type=page&page=page-organizer-groups'); ?>" 
                               class="button"><?php _e('Cancel', 'page-organizer'); ?></a>
                        <?php endif; ?>
                    </p>
                </form>
            </div>
            
            <!-- Groups List -->
            <div class="page-organizer-groups-list">
                <h2><?php _e('Existing Groups', 'page-organizer'); ?></h2>
                
                <?php if (empty($groups)): ?>
                    <p><?php _e('No groups created yet. Create your first group above.', 'page-organizer'); ?></p>
                <?php else: ?>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th scope="col" class="manage-column column-name"><?php _e('Name', 'page-organizer'); ?></th>
                                <th scope="col" class="manage-column column-description"><?php _e('Description', 'page-organizer'); ?></th>
                                <th scope="col" class="manage-column column-pages"><?php _e('Pages', 'page-organizer'); ?></th>
                                <th scope="col" class="manage-column column-actions"><?php _e('Actions', 'page-organizer'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($groups as $group): ?>
                                <?php 
                                // Get page count for this group
                                global $wpdb;
                                $page_groups_table = $wpdb->prefix . 'page_organizer_page_groups';
                                $page_count = $wpdb->get_var($wpdb->prepare("
                                    SELECT COUNT(*) 
                                    FROM {$page_groups_table} pg
                                    INNER JOIN {$wpdb->posts} p ON pg.page_id = p.ID
                                    WHERE pg.group_id = %d 
                                    AND p.post_status = 'publish'
                                ", $group->id));
                                ?>
                                <tr>
                                    <td class="column-name">
                                        <strong><?php echo esc_html($group->name); ?></strong>
                                    </td>
                                    <td class="column-description">
                                        <?php echo esc_html($group->description); ?>
                                    </td>
                                    <td class="column-pages">
                                        <span class="page-count"><?php echo $page_count; ?></span>
                                        <?php if ($page_count > 0): ?>
                                            <a href="<?php echo admin_url('edit.php?post_type=page&page_group_filter=' . $group->id); ?>" 
                                               class="view-pages-link"><?php _e('View Pages', 'page-organizer'); ?></a>
                                        <?php endif; ?>
                                    </td>
                                    <td class="column-actions">
                                        <a href="<?php echo admin_url('edit.php?post_type=page&page=page-organizer-groups&edit=' . $group->id); ?>" 
                                           class="button button-small"><?php _e('Edit', 'page-organizer'); ?></a>
                                        
                                        <form method="post" style="display: inline-block;" 
                                              onsubmit="return confirm('<?php _e('Are you sure you want to delete this group? This will remove all page assignments to this group.', 'page-organizer'); ?>');">
                                            <?php wp_nonce_field('page_organizer_groups'); ?>
                                            <input type="hidden" name="action" value="delete_group">
                                            <input type="hidden" name="group_id" value="<?php echo esc_attr($group->id); ?>">
                                            <input type="submit" 
                                                   class="button button-small button-link-delete" 
                                                   value="<?php _e('Delete', 'page-organizer'); ?>">
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
            
            <!-- Statistics -->
            <div class="page-organizer-stats">
                <h2><?php _e('Statistics', 'page-organizer'); ?></h2>
                
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-number"><?php echo count($groups); ?></div>
                        <div class="stat-label"><?php _e('Total Groups', 'page-organizer'); ?></div>
                    </div>
                    
                    <div class="stat-item">
                        <div class="stat-number"><?php echo $stats['ungrouped_count']; ?></div>
                        <div class="stat-label">
                            <a href="<?php echo admin_url('edit.php?post_type=page&page_group_filter=ungrouped'); ?>">
                                <?php _e('Ungrouped Pages', 'page-organizer'); ?>
                            </a>
                        </div>
                    </div>
                    
                    <?php 
                    $total_grouped = 0;
                    foreach ($stats['groups'] as $group_stat) {
                        $total_grouped += $group_stat->page_count;
                    }
                    ?>
                    <div class="stat-item">
                        <div class="stat-number"><?php echo $total_grouped; ?></div>
                        <div class="stat-label"><?php _e('Grouped Pages', 'page-organizer'); ?></div>
                    </div>
                    </div>
                </div>
            
            <!-- Settings Section -->
            <div class="postbox">
                <div class="postbox-header">
                    <h2 class="hndle"><?php _e('Plugin Settings', 'page-organizer'); ?></h2>
                </div>
                <div class="inside">
                    <form method="post" action="">
                        <?php wp_nonce_field('page_organizer_settings', '_wpnonce'); ?>
                        <input type="hidden" name="action" value="update_settings">
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="keep_data_on_uninstall"><?php _e('Data Retention', 'page-organizer'); ?></label>
                                </th>
                                <td>
                                    <label>
                                        <input type="checkbox" 
                                               id="keep_data_on_uninstall" 
                                               name="keep_data_on_uninstall" 
                                               value="1" 
                                               <?php checked(get_option('page_organizer_keep_data_on_uninstall', true)); ?>>
                                        <?php _e('Keep groups and data when plugin is deleted', 'page-organizer'); ?>
                                    </label>
                                    <p class="description">
                                        <?php _e('When enabled, your groups and page assignments will be preserved even if you delete the plugin. Useful for plugin updates or temporary removal.', 'page-organizer'); ?>
                                    </p>
                                </td>
                            </tr>
                        </table>
                        
                        <p class="submit">
                            <input type="submit" 
                                   name="submit" 
                                   class="button-primary" 
                                   value="<?php _e('Save Settings', 'page-organizer'); ?>">
                        </p>
                    </form>
                </div>
            </div>
            
            <!-- Export/Import Section -->
            <div class="postbox">
                <div class="postbox-header">
                    <h2 class="hndle"><?php _e('Export/Import Groups', 'page-organizer'); ?></h2>
                </div>
                <div class="inside">
                    <div class="export-import-section">
                        <!-- Export -->
                        <div class="export-section">
                            <h3><?php _e('Export Groups', 'page-organizer'); ?></h3>
                            <p class="description">
                                <?php _e('Export your group names and colors to a JSON file. Page assignments are not included - only group definitions for reuse across sites.', 'page-organizer'); ?>
                            </p>
                            <form method="post" action="">
                                <?php wp_nonce_field('page_organizer_groups'); ?>
                                <input type="hidden" name="action" value="export_groups">
                                <p class="submit">
                                    <input type="submit" 
                                           name="submit" 
                                           class="button-secondary" 
                                           value="<?php _e('Export Groups', 'page-organizer'); ?>">
                                </p>
                            </form>
                        </div>
                        
                        <!-- Import -->
                        <div class="import-section">
                            <h3><?php _e('Import Groups', 'page-organizer'); ?></h3>
                            <p class="description">
                                <?php _e('Import group definitions from a JSON file. Existing groups with the same name will be updated with new colors.', 'page-organizer'); ?>
                            </p>
                            <form method="post" action="" enctype="multipart/form-data">
                                <?php wp_nonce_field('page_organizer_groups', '_wpnonce'); ?>
                                <input type="hidden" name="action" value="import_groups">
                                <table class="form-table">
                                    <tr>
                                        <th scope="row">
                                            <label for="import_file"><?php _e('JSON File', 'page-organizer'); ?></label>
                                        </th>
                                        <td>
                                            <input type="file" 
                                                   id="import_file" 
                                                   name="import_file" 
                                                   accept=".json" 
                                                   required>
                                            <p class="description">
                                                <?php _e('Select a JSON file exported from Page Organizer.', 'page-organizer'); ?>
                                            </p>
                                        </td>
                                    </tr>
                                </table>
                                <p class="submit">
                                    <input type="submit" 
                                           name="submit" 
                                           class="button-secondary" 
                                           value="<?php _e('Import Groups', 'page-organizer'); ?>">
                                </p>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.page-organizer-admin {
    max-width: 1200px;
}

.page-organizer-form-section {
    background: #fff;
    border: 1px solid #ccd0d4;
    padding: 20px;
    margin-bottom: 20px;
}

.page-organizer-groups-list {
    background: #fff;
    border: 1px solid #ccd0d4;
    padding: 20px;
    margin-bottom: 20px;
}

.page-organizer-stats {
    background: #fff;
    border: 1px solid #ccd0d4;
    padding: 20px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-top: 15px;
}

.stat-item {
    text-align: center;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 4px;
}

.stat-number {
    font-size: 2em;
    font-weight: bold;
    color: #0073aa;
    margin-bottom: 5px;
}

.stat-label {
    font-size: 14px;
    color: #666;
}

.stat-label a {
    color: #0073aa;
    text-decoration: none;
}

.stat-label a:hover {
    text-decoration: underline;
}

.page-count {
    font-weight: bold;
    margin-right: 10px;
}

.view-pages-link {
    font-size: 12px;
    text-decoration: none;
}

.required {
    color: #d63638;
}

.column-actions form {
    margin-left: 5px;
}

.button-link-delete {
    color: #d63638 !important;
    border-color: transparent !important;
    background: transparent !important;
    box-shadow: none !important;
    text-decoration: underline;
}

.button-link-delete:hover {
    color: #d63638 !important;
    background: #f6f7f7 !important;
}

/* Help Modal Styles */
.page-organizer-help-modal {
    display: none;
    position: fixed;
    z-index: 100000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}

.page-organizer-help-content {
    background-color: #fff;
    margin: 5% auto;
    padding: 20px;
    border: 1px solid #ccc;
    border-radius: 5px;
    width: 80%;
    max-width: 800px;
    max-height: 80vh;
    overflow-y: auto;
    position: relative;
}

.page-organizer-help-close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    position: absolute;
    right: 15px;
    top: 10px;
}

.page-organizer-help-close:hover {
    color: #000;
}
</style>

<!-- Help Modal -->
<div id="page-organizer-help-modal" class="page-organizer-help-modal">
    <div class="page-organizer-help-content">
        <span class="page-organizer-help-close">&times;</span>
        <h2><?php _e('Page Organizer - Help & Instructions', 'page-organizer'); ?></h2>
        
        <div class="help-section">
            <h3><?php _e('Getting Started', 'page-organizer'); ?></h3>
            <p><?php _e('Page Organizer helps you organize your pages into custom groups without modifying the actual pages. This is perfect for categorizing pages like "Core", "Services", "Areas", "Ads", etc.', 'page-organizer'); ?></p>
        </div>
        
        <div class="help-section">
            <h3><?php _e('Creating Groups', 'page-organizer'); ?></h3>
            <ol>
                <li><?php _e('Use the "Create New Group" form above', 'page-organizer'); ?></li>
                <li><?php _e('Enter a group name (e.g., "Core Pages", "Services")', 'page-organizer'); ?></li>
                <li><?php _e('Add an optional description', 'page-organizer'); ?></li>
                <li><?php _e('Choose a color from the presets or enter a custom hex color', 'page-organizer'); ?></li>
                <li><?php _e('Click "Create Group"', 'page-organizer'); ?></li>
            </ol>
        </div>
        
        <div class="help-section">
            <h3><?php _e('Assigning Pages to Groups', 'page-organizer'); ?></h3>
            <h4><?php _e('Quick Edit (Single Page):', 'page-organizer'); ?></h4>
            <ol>
                <li><?php _e('Go to Pages → All Pages', 'page-organizer'); ?></li>
                <li><?php _e('Hover over a page and click "Quick Edit"', 'page-organizer'); ?></li>
                <li><?php _e('Select a group from the "Page Group" dropdown', 'page-organizer'); ?></li>
                <li><?php _e('Click "Update"', 'page-organizer'); ?></li>
            </ol>
            
            <h4><?php _e('Bulk Edit (Multiple Pages):', 'page-organizer'); ?></h4>
            <ol>
                <li><?php _e('Go to Pages → All Pages', 'page-organizer'); ?></li>
                <li><?php _e('Select multiple pages using checkboxes', 'page-organizer'); ?></li>
                <li><?php _e('Choose "Edit" from the Bulk Actions dropdown', 'page-organizer'); ?></li>
                <li><?php _e('Select a group from the "Page Group" dropdown', 'page-organizer'); ?></li>
                <li><?php _e('Click "Update"', 'page-organizer'); ?></li>
            </ol>
        </div>
        
        <div class="help-section">
            <h3><?php _e('Filtering Pages', 'page-organizer'); ?></h3>
            <ol>
                <li><?php _e('Go to Pages → All Pages', 'page-organizer'); ?></li>
                <li><?php _e('Use the "Filter by Group" dropdown above the pages list', 'page-organizer'); ?></li>
                <li><?php _e('Select a group or "Ungrouped" to filter pages', 'page-organizer'); ?></li>
                <li><?php _e('Click "Clear Filter" to show all pages again', 'page-organizer'); ?></li>
            </ol>
        </div>
        
        <div class="help-section">
            <h3><?php _e('Export & Import Groups', 'page-organizer'); ?></h3>
            <p><?php _e('Perfect for deploying the same group structure across multiple websites:', 'page-organizer'); ?></p>
            <h4><?php _e('Export:', 'page-organizer'); ?></h4>
            <ol>
                <li><?php _e('Scroll to the "Export/Import Groups" section below', 'page-organizer'); ?></li>
                <li><?php _e('Click "Export Groups" to download a JSON file', 'page-organizer'); ?></li>
            </ol>
            
            <h4><?php _e('Import:', 'page-organizer'); ?></h4>
            <ol>
                <li><?php _e('Click "Choose File" and select your exported JSON file', 'page-organizer'); ?></li>
                <li><?php _e('Click "Import Groups" to create the groups on this site', 'page-organizer'); ?></li>
            </ol>
        </div>
        
        <div class="help-section">
            <h3><?php _e('Tips & Best Practices', 'page-organizer'); ?></h3>
            <ul>
                <li><?php _e('Use descriptive group names that make sense to your team', 'page-organizer'); ?></li>
                <li><?php _e('Choose distinct colors for easy visual identification', 'page-organizer'); ?></li>
                <li><?php _e('Use the export/import feature to maintain consistency across client sites', 'page-organizer'); ?></li>
                <li><?php _e('The "Ungrouped" filter helps you find pages that haven\'t been categorized yet', 'page-organizer'); ?></li>
                <li><?php _e('Page assignments are preserved when you update the plugin', 'page-organizer'); ?></li>
            </ul>
        </div>
        
        <div class="help-section">
            <h3><?php _e('Need More Help?', 'page-organizer'); ?></h3>
            <p><?php _e('Visit', 'page-organizer'); ?> <a href="https://searchclickgrow.com" target="_blank">Search Click Grow</a> <?php _e('for additional support and resources.', 'page-organizer'); ?></p>
        </div>
    </div>
</div>
</style>

