=== Page Organizer ===
Contributors: searchclickgrow
Tags: pages, organization, groups, admin, management
Requires at least: 5.0
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Organize pages by custom definable groups without modifying the actual pages. Provides admin interface for group management and page filtering.

== Description ==

Page Organizer is a WordPress plugin that helps you organize your pages by custom definable groups. This plugin does not make any changes to your actual pages, but rather provides an admin view that helps organize similar types of pages.

**Key Features:**

* Create custom page groups (e.g., "Core", "Services", "Areas", "Ads")
* Assign pages to groups using Quick Edit or Bulk Edit
* Filter pages by groups in the admin area
* Default "Ungrouped" status for unassigned pages
* Clean, intuitive admin interface
* No modifications to your actual page content

**Perfect for:**

* Website administrators who manage many pages
* Agencies organizing client websites
* Content managers who need better page organization
* Anyone who wants to categorize pages without using WordPress categories

**How it works:**

1. Create custom groups with names that make sense for your website
2. Use Quick Edit or Bulk Edit to assign pages to groups
3. Filter your pages list by group to quickly find what you need
4. View group statistics and manage assignments from the dedicated admin page

The plugin adds a "Page Groups" submenu under Pages in your WordPress admin, where you can create, edit, and delete groups. It also adds a "Page Group" column to your pages list and provides filtering options.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/page-organizer` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Go to Pages > Page Groups to start creating your groups
4. Use Quick Edit or Bulk Edit on your pages to assign them to groups
5. Use the filter dropdown on the Pages screen to filter by groups

== Frequently Asked Questions ==

= Does this plugin modify my pages? =

No, this plugin does not modify your actual pages in any way. It only creates organizational relationships that are stored separately in the database.

= Can I assign a page to multiple groups? =

Currently, each page can only be assigned to one group at a time. This keeps the organization simple and clear.

= What happens to my data if I deactivate the plugin? =

Your page assignments are stored in custom database tables. If you deactivate the plugin, the data remains. If you uninstall the plugin, all data is removed.

= Can I export my group assignments? =

Yes, you can export and import group definitions (name, description, color) as JSON from the Page Groups admin page.

== Screenshots ==

1. Page Groups management interface
2. Pages list with group column and filter
3. Quick Edit with group assignment
4. Bulk Edit with group assignment

== Changelog ==

= 1.2 =
* Fixed critical quick edit bug where opening Quick Edit for any reason would strip the page group assignment on save
* Added "No Change" default option to quick edit dropdown to prevent unintended group changes
* Updated JS selectors for correct group display when Quick Edit opens

= 1.1.1 =
* Added debug console.log statements to diagnose quick edit display issue (debug build only)

= 1.1 =
* Consolidated plugin from 12 files to 4 core files
* Removed dead code classes (class-database.php, class-groups.php, class-admin.php)
* Fixed asset paths and removed duplicate inline styles

= 1.0 =
* Initial release
* Create custom page groups with name, description, and color
* Assign pages to groups via Quick Edit and Bulk Edit
* Filter pages by groups in the admin area
* Colored badge display in pages list column
* JSON export and import for group definitions
* Statistics panel and help modal
* Database auto-upgrade system

== Upgrade Notice ==

= 1.2 =
Critical fix: Quick Edit would silently strip the page group assignment on save for any page. Upgrade immediately to prevent data loss. Includes a new "No Change" default option so Quick Edit never removes a group unintentionally.

= 1.1 =
Major refactor: Plugin consolidated from 12 files to 4 core files. No functional changes to group data or page assignments.

= 1.0 =
Initial release of WP Page Organizer.

== Developer Notes ==

This plugin is developed by Search Click Grow for better WordPress page management. The plugin follows WordPress coding standards and best practices.

**Database Tables:**
* `wp_page_organizer_groups` - Stores group information
* `wp_page_organizer_page_groups` - Stores page-group relationships

**Hooks and Filters:**
The plugin provides several hooks for developers who want to extend functionality:
* `page_organizer_before_group_save` - Action before saving a group
* `page_organizer_after_group_save` - Action after saving a group
* `page_organizer_group_deleted` - Action when a group is deleted

For support and feature requests, please visit: https://searchclickgrow.com

