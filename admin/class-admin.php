<?php
/**
 * Admin class for Page Organizer Plugin
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class PageOrganizerAdmin {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_init', array($this, 'init'));
    }
    
    /**
     * Initialize admin functionality
     */
    public function init() {
        // Check if database needs upgrade
        PageOrganizerDatabase::maybe_upgrade();
    }
    
    /**
     * Render groups management page
     */
    public function render_groups_page() {
        $groups = PageOrganizerGroups::get_all();
        $stats = PageOrganizerGroups::get_all_group_stats();
        
        // Handle form submissions
        if (isset($_POST['action'])) {
            $this->handle_group_form_submission();
            // Refresh data after form submission
            $groups = PageOrganizerGroups::get_all();
            $stats = PageOrganizerGroups::get_all_group_stats();
        }
        
        include PAGE_ORGANIZER_PLUGIN_DIR . 'admin/views/groups-page.php';
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
        }
    }
    
    /**
     * Handle create group
     */
    private function handle_create_group() {
        $name = sanitize_text_field($_POST['group_name']);
        $description = sanitize_textarea_field($_POST['group_description']);
        
        if (empty($name)) {
            add_settings_error('page_organizer', 'empty_name', __('Group name is required.', 'page-organizer'));
            return;
        }
        
        $result = PageOrganizerGroups::create($name, $description);
        
        if (is_wp_error($result)) {
            add_settings_error('page_organizer', 'create_error', $result->get_error_message());
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
        
        if (empty($name)) {
            add_settings_error('page_organizer', 'empty_name', __('Group name is required.', 'page-organizer'));
            return;
        }
        
        $result = PageOrganizerGroups::update($id, $name, $description);
        
        if (is_wp_error($result)) {
            add_settings_error('page_organizer', 'update_error', $result->get_error_message());
        } else {
            add_settings_error('page_organizer', 'group_updated', __('Group updated successfully.', 'page-organizer'), 'updated');
        }
    }
    
    /**
     * Handle delete group
     */
    private function handle_delete_group() {
        $id = intval($_POST['group_id']);
        
        $result = PageOrganizerGroups::delete($id);
        
        if (is_wp_error($result)) {
            add_settings_error('page_organizer', 'delete_error', $result->get_error_message());
        } else {
            add_settings_error('page_organizer', 'group_deleted', __('Group deleted successfully.', 'page-organizer'), 'updated');
        }
    }
    
    /**
     * Get group for editing
     */
    public function get_edit_group() {
        if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
            return PageOrganizerGroups::get_by_id(intval($_GET['edit']));
        }
        return null;
    }
    
    /**
     * Render quick edit fields
     */
    public function render_quick_edit_fields($groups) {
        include PAGE_ORGANIZER_PLUGIN_DIR . 'admin/views/quick-edit-fields.php';
    }
    
    /**
     * Render bulk edit fields
     */
    public function render_bulk_edit_fields($groups) {
        include PAGE_ORGANIZER_PLUGIN_DIR . 'admin/views/bulk-edit-fields.php';
    }
}

