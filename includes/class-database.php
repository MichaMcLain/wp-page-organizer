<?php
/**
 * Database management class for Page Organizer Plugin
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class PageOrganizerDatabase {
    
    /**
     * Get groups table name
     */
    public static function get_groups_table() {
        global $wpdb;
        return $wpdb->prefix . 'page_organizer_groups';
    }
    
    /**
     * Get page groups table name
     */
    public static function get_page_groups_table() {
        global $wpdb;
        return $wpdb->prefix . 'page_organizer_page_groups';
    }
    
    /**
     * Create database tables
     */
    public static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Groups table
        $groups_table = self::get_groups_table();
        $groups_sql = "CREATE TABLE $groups_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            description text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY name (name)
        ) $charset_collate;";
        
        // Page groups relationship table
        $page_groups_table = self::get_page_groups_table();
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
     * Drop database tables
     */
    public static function drop_tables() {
        global $wpdb;
        
        $groups_table = self::get_groups_table();
        $page_groups_table = self::get_page_groups_table();
        
        $wpdb->query("DROP TABLE IF EXISTS {$page_groups_table}");
        $wpdb->query("DROP TABLE IF EXISTS {$groups_table}");
    }
    
    /**
     * Check if tables exist
     */
    public static function tables_exist() {
        global $wpdb;
        
        $groups_table = self::get_groups_table();
        $page_groups_table = self::get_page_groups_table();
        
        $groups_exists = $wpdb->get_var("SHOW TABLES LIKE '{$groups_table}'") === $groups_table;
        $page_groups_exists = $wpdb->get_var("SHOW TABLES LIKE '{$page_groups_table}'") === $page_groups_table;
        
        return $groups_exists && $page_groups_exists;
    }
    
    /**
     * Get database version
     */
    public static function get_db_version() {
        return get_option('page_organizer_db_version', '0.0');
    }
    
    /**
     * Update database version
     */
    public static function update_db_version($version) {
        update_option('page_organizer_db_version', $version);
    }
    
    /**
     * Check if database needs upgrade
     */
    public static function needs_upgrade() {
        return version_compare(self::get_db_version(), PAGE_ORGANIZER_VERSION, '<');
    }
    
    /**
     * Upgrade database if needed
     */
    public static function maybe_upgrade() {
        if (self::needs_upgrade()) {
            self::create_tables();
            self::update_db_version(PAGE_ORGANIZER_VERSION);
        }
    }
}

