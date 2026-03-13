<?php
/**
 * Groups management class for Page Organizer Plugin
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class PageOrganizerGroups {
    
    /**
     * Get all groups
     */
    public static function get_all() {
        global $wpdb;
        $groups_table = PageOrganizerDatabase::get_groups_table();
        
        return $wpdb->get_results("SELECT * FROM {$groups_table} ORDER BY name ASC");
    }
    
    /**
     * Get group by ID
     */
    public static function get_by_id($id) {
        global $wpdb;
        $groups_table = PageOrganizerDatabase::get_groups_table();
        
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$groups_table} WHERE id = %d", $id));
    }
    
    /**
     * Get group by name
     */
    public static function get_by_name($name) {
        global $wpdb;
        $groups_table = PageOrganizerDatabase::get_groups_table();
        
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$groups_table} WHERE name = %s", $name));
    }
    
    /**
     * Create new group
     */
    public static function create($name, $description = '') {
        global $wpdb;
        $groups_table = PageOrganizerDatabase::get_groups_table();
        
        // Check if group with same name exists
        if (self::get_by_name($name)) {
            return new WP_Error('group_exists', __('A group with this name already exists.', 'page-organizer'));
        }
        
        $result = $wpdb->insert(
            $groups_table,
            array(
                'name' => sanitize_text_field($name),
                'description' => sanitize_textarea_field($description)
            ),
            array('%s', '%s')
        );
        
        if ($result === false) {
            return new WP_Error('db_error', __('Failed to create group.', 'page-organizer'));
        }
        
        return $wpdb->insert_id;
    }
    
    /**
     * Update group
     */
    public static function update($id, $name, $description = '') {
        global $wpdb;
        $groups_table = PageOrganizerDatabase::get_groups_table();
        
        // Check if group exists
        if (!self::get_by_id($id)) {
            return new WP_Error('group_not_found', __('Group not found.', 'page-organizer'));
        }
        
        // Check if another group with same name exists
        $existing = self::get_by_name($name);
        if ($existing && $existing->id != $id) {
            return new WP_Error('group_exists', __('A group with this name already exists.', 'page-organizer'));
        }
        
        $result = $wpdb->update(
            $groups_table,
            array(
                'name' => sanitize_text_field($name),
                'description' => sanitize_textarea_field($description)
            ),
            array('id' => $id),
            array('%s', '%s'),
            array('%d')
        );
        
        if ($result === false) {
            return new WP_Error('db_error', __('Failed to update group.', 'page-organizer'));
        }
        
        return true;
    }
    
    /**
     * Delete group
     */
    public static function delete($id) {
        global $wpdb;
        $groups_table = PageOrganizerDatabase::get_groups_table();
        $page_groups_table = PageOrganizerDatabase::get_page_groups_table();
        
        // Check if group exists
        if (!self::get_by_id($id)) {
            return new WP_Error('group_not_found', __('Group not found.', 'page-organizer'));
        }
        
        // Delete all page-group relationships first
        $wpdb->delete($page_groups_table, array('group_id' => $id), array('%d'));
        
        // Delete the group
        $result = $wpdb->delete($groups_table, array('id' => $id), array('%d'));
        
        if ($result === false) {
            return new WP_Error('db_error', __('Failed to delete group.', 'page-organizer'));
        }
        
        return true;
    }
    
    /**
     * Get groups for a specific page
     */
    public static function get_page_groups($page_id) {
        global $wpdb;
        $groups_table = PageOrganizerDatabase::get_groups_table();
        $page_groups_table = PageOrganizerDatabase::get_page_groups_table();
        
        return $wpdb->get_results($wpdb->prepare("
            SELECT g.* 
            FROM {$groups_table} g 
            INNER JOIN {$page_groups_table} pg ON g.id = pg.group_id 
            WHERE pg.page_id = %d 
            ORDER BY g.name ASC
        ", $page_id));
    }
    
    /**
     * Get pages in a specific group
     */
    public static function get_group_pages($group_id) {
        global $wpdb;
        $page_groups_table = PageOrganizerDatabase::get_page_groups_table();
        
        return $wpdb->get_col($wpdb->prepare("
            SELECT page_id 
            FROM {$page_groups_table} 
            WHERE group_id = %d
        ", $group_id));
    }
    
    /**
     * Get ungrouped pages
     */
    public static function get_ungrouped_pages() {
        global $wpdb;
        $page_groups_table = PageOrganizerDatabase::get_page_groups_table();
        
        return $wpdb->get_col("
            SELECT p.ID 
            FROM {$wpdb->posts} p 
            LEFT JOIN {$page_groups_table} pg ON p.ID = pg.page_id 
            WHERE p.post_type = 'page' 
            AND p.post_status = 'publish'
            AND pg.page_id IS NULL
        ");
    }
    
    /**
     * Assign page to group
     */
    public static function assign_page_to_group($page_id, $group_id) {
        global $wpdb;
        $page_groups_table = PageOrganizerDatabase::get_page_groups_table();
        
        // Validate page exists and is a page
        $page = get_post($page_id);
        if (!$page || $page->post_type !== 'page') {
            return new WP_Error('invalid_page', __('Invalid page ID.', 'page-organizer'));
        }
        
        // If group_id is 0, remove all group assignments
        if ($group_id == 0) {
            $wpdb->delete($page_groups_table, array('page_id' => $page_id), array('%d'));
            return true;
        }
        
        // Validate group exists
        if (!self::get_by_id($group_id)) {
            return new WP_Error('invalid_group', __('Invalid group ID.', 'page-organizer'));
        }
        
        // Remove existing assignments for this page
        $wpdb->delete($page_groups_table, array('page_id' => $page_id), array('%d'));
        
        // Add new assignment
        $result = $wpdb->insert(
            $page_groups_table,
            array(
                'page_id' => $page_id,
                'group_id' => $group_id
            ),
            array('%d', '%d')
        );
        
        if ($result === false) {
            return new WP_Error('db_error', __('Failed to assign page to group.', 'page-organizer'));
        }
        
        return true;
    }
    
    /**
     * Remove page from group
     */
    public static function remove_page_from_group($page_id, $group_id = null) {
        global $wpdb;
        $page_groups_table = PageOrganizerDatabase::get_page_groups_table();
        
        if ($group_id) {
            // Remove from specific group
            $result = $wpdb->delete(
                $page_groups_table,
                array('page_id' => $page_id, 'group_id' => $group_id),
                array('%d', '%d')
            );
        } else {
            // Remove from all groups
            $result = $wpdb->delete(
                $page_groups_table,
                array('page_id' => $page_id),
                array('%d')
            );
        }
        
        return $result !== false;
    }
    
    /**
     * Get group statistics
     */
    public static function get_group_stats($group_id) {
        global $wpdb;
        $page_groups_table = PageOrganizerDatabase::get_page_groups_table();
        
        $count = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) 
            FROM {$page_groups_table} pg
            INNER JOIN {$wpdb->posts} p ON pg.page_id = p.ID
            WHERE pg.group_id = %d 
            AND p.post_status = 'publish'
        ", $group_id));
        
        return array(
            'page_count' => intval($count)
        );
    }
    
    /**
     * Get all group statistics
     */
    public static function get_all_group_stats() {
        global $wpdb;
        $groups_table = PageOrganizerDatabase::get_groups_table();
        $page_groups_table = PageOrganizerDatabase::get_page_groups_table();
        
        $stats = $wpdb->get_results("
            SELECT g.id, g.name, COUNT(pg.page_id) as page_count
            FROM {$groups_table} g
            LEFT JOIN {$page_groups_table} pg ON g.id = pg.group_id
            LEFT JOIN {$wpdb->posts} p ON pg.page_id = p.ID AND p.post_status = 'publish'
            GROUP BY g.id, g.name
            ORDER BY g.name ASC
        ");
        
        // Add ungrouped count
        $ungrouped_count = count(self::get_ungrouped_pages());
        
        return array(
            'groups' => $stats,
            'ungrouped_count' => $ungrouped_count
        );
    }
}

