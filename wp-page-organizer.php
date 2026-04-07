<?php
/**
 * Plugin Name: WP Page Organizer
 * Plugin URI: https://searchclickgrow.com
 * Description: Organize pages by custom definable groups without modifying the actual pages. Provides admin interface for group management and page filtering.
 * Version: 1.2
 * Author: Search Click Grow
 * Author URI: https://searchclickgrow.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: page-organizer
 * Domain Path: /languages
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('PAGE_ORGANIZER_VERSION', '1.2');
define('PAGE_ORGANIZER_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('PAGE_ORGANIZER_PLUGIN_URL', plugin_dir_url(__FILE__));
define('PAGE_ORGANIZER_PLUGIN_FILE', __FILE__);

/**
 * Main Page Organizer Plugin Class
 */
class PageOrganizerPlugin {
    
    /**
     * Single instance of the plugin
     */
    private static $instance = null;
    
    /**
     * Get single instance of the plugin
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->init_hooks();
    }
    
    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        // Activation and deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // Initialize plugin
        add_action('init', array($this, 'init'));
        
        // Admin hooks
        if (is_admin()) {
            add_action('admin_menu', array($this, 'add_admin_menu'));
            add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
            add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'add_plugin_action_links'));
            add_action('quick_edit_custom_box', array($this, 'add_quick_edit_fields'), 10, 2);
            add_action('bulk_edit_custom_box', array($this, 'add_bulk_edit_fields'), 10, 2);
        // Hook into save_post for regular saves and quick edit
        add_action('save_post', array($this, 'save_page_group'));
            add_action('manage_pages_custom_column', array($this, 'display_page_group_column'), 10, 2);
            add_filter('manage_pages_columns', array($this, 'add_page_group_column'));
            add_action('restrict_manage_posts', array($this, 'add_group_filter_dropdown'));
            add_filter('parse_query', array($this, 'filter_pages_by_group'));
        }
        
        // Removed AJAX hooks to prevent interference with bulk edit
        // All functionality now uses regular WordPress form submission
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        // Load text domain for translations
        load_plugin_textdomain('page-organizer', false, dirname(plugin_basename(__FILE__)) . '/languages');
        
        // Check for database upgrades
        $this->maybe_upgrade_database();
        
        // Include required files
        $this->include_files();
    }
    
    /**
     * Include required files
     */
    private function include_files() {
        // All code consolidated into main plugin file as of v1.1
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Create database tables
        $this->create_database_tables();
        
        // Set default options
        add_option('page_organizer_version', PAGE_ORGANIZER_VERSION);
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Check and upgrade database if needed
     */
    public function maybe_upgrade_database() {
        $current_version = get_option('page_organizer_version', '0.0');
        
        if (version_compare($current_version, PAGE_ORGANIZER_VERSION, '<')) {
            $this->create_database_tables();
            
            // Add color column if it doesn't exist (for 0.1 to 0.4+ upgrade)
            global $wpdb;
            $groups_table = $wpdb->prefix . 'page_organizer_groups';
            
            // Check if color column exists
            $column_exists = $wpdb->get_results("SHOW COLUMNS FROM {$groups_table} LIKE 'color'");
            
            if (empty($column_exists)) {
                // Add color column with default value
                $wpdb->query("ALTER TABLE {$groups_table} ADD COLUMN color varchar(7) DEFAULT '#0073aa' AFTER description");
            }
            
            update_option('page_organizer_version', PAGE_ORGANIZER_VERSION);
        }
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Create database tables
     */
    private function create_database_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Groups table
        $groups_table = $wpdb->prefix . 'page_organizer_groups';
        $groups_sql = "CREATE TABLE $groups_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            description text,
            color varchar(7) DEFAULT '#0073aa',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY name (name)
        ) $charset_collate;";
        
        // Page groups relationship table
        $page_groups_table = $wpdb->prefix . 'page_organizer_page_groups';
        $page_groups_sql = "CREATE TABLE $page_groups_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            page_id bigint(20) NOT NULL,
            group_id mediumint(9) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY page_group (page_id, group_id),
            KEY page_id (page_id),
            KEY group_id (group_id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($groups_sql);
        dbDelta($page_groups_sql);
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_submenu_page(
            'edit.php?post_type=page',
            __('Page Groups', 'page-organizer'),
            __('Page Groups', 'page-organizer'),
            'manage_options',
            'page-organizer-groups',
            array($this, 'admin_page_groups')
        );
    }
    
    /**
     * Add settings link to plugin actions
     */
    public function add_plugin_action_links($links) {
        $settings_link = '<a href="' . admin_url('edit.php?post_type=page&page=page-organizer-groups') . '">' . __('Settings', 'page-organizer') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }
    
    /**
     * Admin page for managing groups
     */
    public function admin_page_groups() {
        // Check if database tables exist
        global $wpdb;
        $groups_table = $wpdb->prefix . 'page_organizer_groups';
        
        if ($wpdb->get_var("SHOW TABLES LIKE '{$groups_table}'") != $groups_table) {
            // Tables don't exist, create them
            $this->create_database_tables();
            
            // If still don't exist, show error
            if ($wpdb->get_var("SHOW TABLES LIKE '{$groups_table}'") != $groups_table) {
                echo '<div class="wrap"><h1>Page Groups</h1>';
                echo '<div class="notice notice-error"><p>Database tables could not be created. Please check your database permissions.</p></div>';
                echo '</div>';
                return;
            }
        }
        
        $groups = $this->get_all_groups();
        $stats = $this->get_all_group_stats();
        
        // Handle form submissions
        if (isset($_POST['action'])) {
            $this->handle_group_form_submission();
            // Refresh data after form submission
            $groups = $this->get_all_groups();
            $stats = $this->get_all_group_stats();
        }
        
        include PAGE_ORGANIZER_PLUGIN_DIR . 'admin/groups-page.php';
    }
    
    /**
     * Handle group form submissions
     */
    private function handle_group_form_submission() {
        if (!wp_verify_nonce($_POST['_wpnonce'], 'page_organizer_groups')) {
            wp_die(__('Security check failed.', 'page-organizer'));
        }
        
        $action = sanitize_text_field($_POST['action']);
        
        switch ($action) {
            case 'create_group':
                $this->handle_create_group();
                break;
            case 'update_group':
                $this->handle_update_group();
                break;
            case 'delete_group':
                $this->handle_delete_group();
                break;
            case 'update_settings':
                $this->handle_update_settings();
                break;
            case 'export_groups':
                $this->handle_export_groups();
                break;
            case 'import_groups':
                $this->handle_import_groups();
                break;
        }
    }
    
    /**
     * Handle create group
     */
    private function handle_create_group() {
        $name = sanitize_text_field($_POST['group_name']);
        $description = sanitize_textarea_field($_POST['group_description']);
        $color = sanitize_text_field($_POST['group_color']);
        
        // Validate color format
        if (!preg_match('/^#[a-fA-F0-9]{6}$/', $color)) {
            $color = '#0073aa'; // Default color
        }
        
        if (empty($name)) {
            add_settings_error('page_organizer', 'empty_name', __('Group name is required.', 'page-organizer'));
            return;
        }
        
        global $wpdb;
        $groups_table = $wpdb->prefix . 'page_organizer_groups';
        
        // Check if group with same name exists
        $existing = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$groups_table} WHERE name = %s", $name));
        if ($existing) {
            add_settings_error('page_organizer', 'group_exists', __('A group with this name already exists.', 'page-organizer'));
            return;
        }
        
        $result = $wpdb->insert($groups_table, array(
            'name' => $name,
            'description' => $description,
            'color' => $color
        ));
        
        if ($result === false) {
            add_settings_error('page_organizer', 'create_error', __('Failed to create group.', 'page-organizer'));
        } else {
            add_settings_error('page_organizer', 'group_created', __('Group created successfully.', 'page-organizer'), 'updated');
        }
    }
    
    /**
     * Handle update group
     */
    private function handle_update_group() {
        $id = intval($_POST['group_id']);
        $name = sanitize_text_field($_POST['group_name']);
        $description = sanitize_textarea_field($_POST['group_description']);
        $color = sanitize_text_field($_POST['group_color']);
        
        // Validate color format
        if (!preg_match('/^#[a-fA-F0-9]{6}$/', $color)) {
            $color = '#0073aa'; // Default color
        }
        
        if (empty($name)) {
            add_settings_error('page_organizer', 'empty_name', __('Group name is required.', 'page-organizer'));
            return;
        }
        
        global $wpdb;
        $groups_table = $wpdb->prefix . 'page_organizer_groups';
        
        // Check if another group with same name exists
        $existing = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$groups_table} WHERE name = %s AND id != %d", $name, $id));
        if ($existing) {
            add_settings_error('page_organizer', 'group_exists', __('A group with this name already exists.', 'page-organizer'));
            return;
        }
        
        $result = $wpdb->update($groups_table, array(
            'name' => $name,
            'description' => $description,
            'color' => $color
        ), array('id' => $id));
        
        if ($result === false) {
            add_settings_error('page_organizer', 'update_error', __('Failed to update group.', 'page-organizer'));
        } else {
            add_settings_error('page_organizer', 'group_updated', __('Group updated successfully.', 'page-organizer'), 'updated');
        }
    }
    
    /**
     * Handle export groups
     */
    private function handle_export_groups() {
        // Clear any output buffers to prevent HTML contamination
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        $groups = $this->get_all_groups();
        
        $export_data = array(
            'version' => PAGE_ORGANIZER_VERSION,
            'export_date' => current_time('Y-m-d H:i:s'),
            'site_url' => get_site_url(),
            'groups' => array()
        );
        
        foreach ($groups as $group) {
            $export_data['groups'][] = array(
                'name' => $group->name,
                'description' => $group->description,
                'color' => isset($group->color) ? $group->color : '#0073aa'
            );
        }
        
        $json_output = json_encode($export_data, JSON_PRETTY_PRINT);
        $filename = 'page-organizer-groups-' . date('Y-m-d-H-i-s') . '.json';
        
        // Set headers for file download
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($json_output));
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
        
        // Output the JSON and exit immediately
        echo $json_output;
        wp_die(); // Use wp_die() instead of exit for WordPress compatibility
    }
    
    /**
     * Handle import groups
     */
    private function handle_import_groups() {
        if (!isset($_FILES['import_file']) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
            add_settings_error('page_organizer', 'import_error', __('Please select a valid JSON file.', 'page-organizer'));
            return;
        }
        
        $file_content = file_get_contents($_FILES['import_file']['tmp_name']);
        $import_data = json_decode($file_content, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            add_settings_error('page_organizer', 'import_error', __('Invalid JSON file format.', 'page-organizer'));
            return;
        }
        
        if (!isset($import_data['groups']) || !is_array($import_data['groups'])) {
            add_settings_error('page_organizer', 'import_error', __('Invalid Page Organizer export file.', 'page-organizer'));
            return;
        }
        
        global $wpdb;
        $groups_table = $wpdb->prefix . 'page_organizer_groups';
        $imported_count = 0;
        $updated_count = 0;
        
        foreach ($import_data['groups'] as $group_data) {
            if (!isset($group_data['name']) || empty($group_data['name'])) {
                continue;
            }
            
            $name = sanitize_text_field($group_data['name']);
            $description = isset($group_data['description']) ? sanitize_textarea_field($group_data['description']) : '';
            $color = isset($group_data['color']) ? sanitize_text_field($group_data['color']) : '#0073aa';
            
            // Validate color format
            if (!preg_match('/^#[a-fA-F0-9]{6}$/', $color)) {
                $color = '#0073aa';
            }
            
            // Check if group exists
            $existing = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$groups_table} WHERE name = %s", $name));
            
            if ($existing) {
                // Update existing group
                $wpdb->update($groups_table, array(
                    'description' => $description,
                    'color' => $color
                ), array('id' => $existing->id));
                $updated_count++;
            } else {
                // Create new group
                $wpdb->insert($groups_table, array(
                    'name' => $name,
                    'description' => $description,
                    'color' => $color
                ));
                $imported_count++;
            }
        }
        
        $message = sprintf(
            __('Import completed: %d new groups created, %d existing groups updated.', 'page-organizer'),
            $imported_count,
            $updated_count
        );
        
        add_settings_error('page_organizer', 'import_success', $message, 'updated');
    }
    
    /**
     * Handle update settings
     */
    private function handle_update_settings() {
        if (!wp_verify_nonce($_POST['_wpnonce'], 'page_organizer_settings')) {
            wp_die(__('Security check failed.', 'page-organizer'));
        }
        
        $keep_data = isset($_POST['keep_data_on_uninstall']) ? true : false;
        update_option('page_organizer_keep_data_on_uninstall', $keep_data);
        
        add_settings_error('page_organizer', 'settings_updated', __('Settings saved successfully.', 'page-organizer'), 'updated');
    }
    
    /**
     * Handle delete group
     */
    private function handle_delete_group() {
        $id = intval($_POST['group_id']);
        
        global $wpdb;
        $groups_table = $wpdb->prefix . 'page_organizer_groups';
        $page_groups_table = $wpdb->prefix . 'page_organizer_page_groups';
        
        // Delete group relationships first
        $wpdb->delete($page_groups_table, array('group_id' => $id));
        
        // Delete group
        $result = $wpdb->delete($groups_table, array('id' => $id));
        
        if ($result === false) {
            add_settings_error('page_organizer', 'delete_error', __('Failed to delete group.', 'page-organizer'));
        } else {
            add_settings_error('page_organizer', 'group_deleted', __('Group deleted successfully.', 'page-organizer'), 'updated');
        }
    }
    
    /**
     * Get edit group
     */
    private function get_edit_group() {
        if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
            return $this->get_group_by_id(intval($_GET['edit']));
        }
        return null;
    }
    
    /**
     * Get all group statistics
     */
    private function get_all_group_stats() {
        global $wpdb;
        $groups_table = $wpdb->prefix . 'page_organizer_groups';
        $page_groups_table = $wpdb->prefix . 'page_organizer_page_groups';
        
        // Check if tables exist
        if ($wpdb->get_var("SHOW TABLES LIKE '{$groups_table}'") != $groups_table) {
            return array(
                'groups' => array(),
                'ungrouped_count' => 0
            );
        }
        
        $stats = $wpdb->get_results("
            SELECT g.id, g.name, COUNT(pg.page_id) as page_count
            FROM {$groups_table} g
            LEFT JOIN {$page_groups_table} pg ON g.id = pg.group_id
            LEFT JOIN {$wpdb->posts} p ON pg.page_id = p.ID AND p.post_status = 'publish'
            GROUP BY g.id, g.name
            ORDER BY g.name ASC
        ");
        
        if ($stats === false) {
            $stats = array();
        }
        
        // Add ungrouped count
        $ungrouped_count = count($this->get_ungrouped_pages());
        
        return array(
            'groups' => $stats,
            'ungrouped_count' => $ungrouped_count
        );
    }
    
    /**
     * Get ungrouped pages
     */
    private function get_ungrouped_pages() {
        global $wpdb;
        $page_groups_table = $wpdb->prefix . 'page_organizer_page_groups';
        
        // Check if table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '{$page_groups_table}'") != $page_groups_table) {
            return array();
        }
        
        $result = $wpdb->get_col("
            SELECT p.ID 
            FROM {$wpdb->posts} p 
            LEFT JOIN {$page_groups_table} pg ON p.ID = pg.page_id 
            WHERE p.post_type = 'page' 
            AND p.post_status = 'publish'
            AND pg.page_id IS NULL
        ");
        
        return $result ? $result : array();
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'page-organizer') !== false || $hook === 'edit.php') {
            wp_enqueue_script(
                'page-organizer-admin',
                PAGE_ORGANIZER_PLUGIN_URL . 'assets/admin.js',
                array('jquery'),
                PAGE_ORGANIZER_VERSION,
                true
            );
            
            wp_enqueue_style(
                'page-organizer-admin',
                PAGE_ORGANIZER_PLUGIN_URL . 'assets/admin.css',
                array(),
                PAGE_ORGANIZER_VERSION
            );
            
            wp_localize_script('page-organizer-admin', 'pageOrganizerAjax', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('page_organizer_nonce'),
                'strings' => array(
                    'confirm_delete' => __('Are you sure you want to delete this group?', 'page-organizer'),
                    'error' => __('An error occurred. Please try again.', 'page-organizer'),
                )
            ));
        }
    }
    
    /**
     * Add page group column to pages list
     */
    public function add_page_group_column($columns) {
        $columns['page_group'] = __('Page Group', 'page-organizer');
        return $columns;
    }
    
    /**
     * Display page group in column
     */
    public function display_page_group_column($column, $post_id) {
        if ($column === 'page_group') {
            $groups = $this->get_page_groups($post_id);
            if (!empty($groups)) {
                echo '<div class="multiple-groups">';
                foreach ($groups as $group) {
                    $color = isset($group->color) ? $group->color : '#0073aa';
                    echo '<span class="page-group-badge" data-group-id="' . esc_attr($group->id) . '" style="background-color: ' . esc_attr($color) . ';">' . esc_html($group->name) . '</span>';
                }
                echo '</div>';
            } else {
                echo '<span class="page-group-badge ungrouped">' . __('Ungrouped', 'page-organizer') . '</span>';
            }
        }
    }
    
    /**
     * Add quick edit fields
     */
    public function add_quick_edit_fields($column_name, $post_type) {
        if ($column_name === 'page_group' && $post_type === 'page') {
            $groups = $this->get_all_groups();
            ?>
            <fieldset class="inline-edit-col-right">
                <div class="inline-edit-col">
                    <label>
                        <span class="title"><?php _e('Page Group', 'page-organizer'); ?></span>
                        <select name="page_organizer_group" class="page-organizer-group-select">
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
            <?php
        }
    }
    
    /**
     * Add bulk edit fields
     */
    public function add_bulk_edit_fields($column_name, $post_type) {
        if ($column_name === 'page_group' && $post_type === 'page') {
            $groups = $this->get_all_groups();
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
            <?php
        }
    }
    
    /**
     * Save page group assignment
     */
    public function save_page_group($post_id) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        if (!current_user_can('edit_page', $post_id)) {
            return;
        }
        
        if (get_post_type($post_id) !== 'page') {
            return;
        }
        
        // Handle quick edit (single page)
        if (isset($_POST['page_organizer_group'])) {
            $group_id = intval($_POST['page_organizer_group']);
            if ($group_id !== -1) {
                $this->assign_page_to_group($post_id, $group_id);
            }
        }
        
        // Handle bulk edit (multiple pages)
        if (isset($_REQUEST['page_organizer_group_bulk'])) {
            $group_id = intval($_REQUEST['page_organizer_group_bulk']);
            // Only process if not "No Change" (-1)
            if ($group_id !== -1) {
                $this->assign_page_to_group($post_id, $group_id);
            }
        }
    }
    
    /**
     * Add group filter dropdown
     */
    public function add_group_filter_dropdown() {
        global $typenow;
        
        if ($typenow === 'page') {
            $groups = $this->get_all_groups();
            $selected_group = isset($_GET['page_group_filter']) ? $_GET['page_group_filter'] : '';
            
            echo '<select name="page_group_filter" onchange="this.form.submit();">';
            echo '<option value="">' . __('All Groups', 'page-organizer') . '</option>';
            echo '<option value="ungrouped"' . selected($selected_group, 'ungrouped', false) . '>' . __('Ungrouped', 'page-organizer') . '</option>';
            
            foreach ($groups as $group) {
                echo '<option value="' . esc_attr($group->id) . '"' . selected($selected_group, $group->id, false) . '>' . esc_html($group->name) . '</option>';
            }
            
            echo '</select>';
            
            // Add clear filter button if filtering is active
            if (!empty($selected_group)) {
                $clear_url = remove_query_arg('page_group_filter');
                echo '<a href="' . esc_url($clear_url) . '" class="button page-organizer-clear-filter" style="margin-left: 5px;">' . __('Clear Filter', 'page-organizer') . '</a>';
                
                // Show filter indicator
                if ($selected_group === 'ungrouped') {
                    echo '<span class="page-group-filter-active">' . __('Ungrouped', 'page-organizer') . '</span>';
                } else {
                    $group = $this->get_group_by_id($selected_group);
                    if ($group) {
                        echo '<span class="page-group-filter-active">' . esc_html($group->name) . '</span>';
                    }
                }
            }
        }
    }
    
    /**
     * Filter pages by group
     */
    public function filter_pages_by_group($query) {
        global $pagenow, $typenow;
        
        if ($pagenow === 'edit.php' && $typenow === 'page' && isset($_GET['page_group_filter']) && $_GET['page_group_filter'] !== '') {
            $group_filter = $_GET['page_group_filter'];
            
            if ($group_filter === 'ungrouped') {
                // Show only ungrouped pages
                global $wpdb;
                $page_groups_table = $wpdb->prefix . 'page_organizer_page_groups';
                
                $ungrouped_pages = $wpdb->get_col("
                    SELECT p.ID 
                    FROM {$wpdb->posts} p 
                    LEFT JOIN {$page_groups_table} pg ON p.ID = pg.page_id 
                    WHERE p.post_type = 'page' 
                    AND pg.page_id IS NULL
                ");
                
                if (!empty($ungrouped_pages)) {
                    $query->set('post__in', $ungrouped_pages);
                } else {
                    $query->set('post__in', array(0)); // No results
                }
            } else {
                // Show pages in specific group
                global $wpdb;
                $page_groups_table = $wpdb->prefix . 'page_organizer_page_groups';
                
                $grouped_pages = $wpdb->get_col($wpdb->prepare("
                    SELECT page_id 
                    FROM {$page_groups_table} 
                    WHERE group_id = %d
                ", $group_filter));
                
                if (!empty($grouped_pages)) {
                    $query->set('post__in', $grouped_pages);
                } else {
                    $query->set('post__in', array(0)); // No results
                }
            }
        }
    }
    
    /**
     * Get group by ID
     */
    public function get_group_by_id($id) {
        global $wpdb;
        $groups_table = $wpdb->prefix . 'page_organizer_groups';
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$groups_table} WHERE id = %d", $id));
    }
    
    /**
     * Get all groups
     */
    public function get_all_groups() {
        global $wpdb;
        $groups_table = $wpdb->prefix . 'page_organizer_groups';
        
        // Check if table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '{$groups_table}'") != $groups_table) {
            return array();
        }
        
        $result = $wpdb->get_results("SELECT * FROM {$groups_table} ORDER BY name ASC");
        return $result ? $result : array();
    }
    
    /**
     * Get page groups
     */
    public function get_page_groups($page_id) {
        global $wpdb;
        $groups_table = $wpdb->prefix . 'page_organizer_groups';
        $page_groups_table = $wpdb->prefix . 'page_organizer_page_groups';
        
        return $wpdb->get_results($wpdb->prepare("
            SELECT g.* 
            FROM {$groups_table} g 
            INNER JOIN {$page_groups_table} pg ON g.id = pg.group_id 
            WHERE pg.page_id = %d 
            ORDER BY g.name ASC
        ", $page_id));
    }
    
    /**
     * Assign page to group
     */
    public function assign_page_to_group($page_id, $group_id) {
        global $wpdb;
        $page_groups_table = $wpdb->prefix . 'page_organizer_page_groups';
        
        // Remove existing assignments
        $wpdb->delete($page_groups_table, array('page_id' => $page_id));
        
        // Add new assignment if group_id is not 0
        if ($group_id > 0) {
            $wpdb->insert($page_groups_table, array(
                'page_id' => $page_id,
                'group_id' => $group_id
            ));
        }
    }
    
}

// Initialize the plugin
PageOrganizerPlugin::get_instance();

// Uninstall hook
register_uninstall_hook(__FILE__, 'page_organizer_uninstall');

/**
 * Plugin uninstall function
 */
function page_organizer_uninstall() {
    // Check if user wants to keep data (default is to keep data)
    $keep_data = get_option('page_organizer_keep_data_on_uninstall', true);
    
    if (!$keep_data) {
        global $wpdb;
        
        // Drop custom tables only if user chose to delete data
        $groups_table = $wpdb->prefix . 'page_organizer_groups';
        $page_groups_table = $wpdb->prefix . 'page_organizer_page_groups';
        
        $wpdb->query("DROP TABLE IF EXISTS {$page_groups_table}");
        $wpdb->query("DROP TABLE IF EXISTS {$groups_table}");
        
        // Delete the keep data option since we're deleting everything
        delete_option('page_organizer_keep_data_on_uninstall');
    }
    
    // Always delete plugin version option
    delete_option('page_organizer_version');
}

