# Page Organizer WordPress Plugin v0.17

A WordPress plugin that helps organize pages by custom definable groups without modifying the actual pages. Provides an admin interface for group management, page assignment via quick/bulk edit, and filtering capabilities.

## Features

- **Custom Page Groups**: Create groups with names like "Core", "Services", "Areas", "Ads"
- **Quick & Bulk Edit**: Assign pages to groups using WordPress's built-in Quick Edit and Bulk Edit features
- **Advanced Filtering**: Filter pages by groups in the admin area, including an "Ungrouped" option
- **Clean Admin Interface**: Dedicated admin page for managing groups with statistics
- **Non-Intrusive**: No modifications to your actual page content or structure
- **WordPress Standards**: Follows WordPress coding standards and best practices

## Installation

1. Download the plugin files
2. Upload to `/wp-content/plugins/page-organizer/` directory
3. Activate the plugin through the WordPress admin
4. Go to **Pages > Page Groups** to start creating groups

## Usage

### Creating Groups

1. Navigate to **Pages > Page Groups** in your WordPress admin
2. Enter a group name (e.g., "Core Pages", "Service Pages")
3. Optionally add a description
4. Click "Add Group"

### Assigning Pages to Groups

**Quick Edit Method:**
1. Go to **Pages** in your WordPress admin
2. Hover over a page and click "Quick Edit"
3. Select a group from the "Page Group" dropdown
4. Click "Update"

**Bulk Edit Method:**
1. Go to **Pages** in your WordPress admin
2. Select multiple pages using checkboxes
3. Choose "Edit" from the Bulk Actions dropdown and click "Apply"
4. Select a group from the "Page Group" dropdown
5. Click "Update"

### Filtering Pages

1. Go to **Pages** in your WordPress admin
2. Use the "Page Group" filter dropdown above the pages list
3. Select a group or "Ungrouped" to filter pages
4. The list will automatically update to show only pages in the selected group

## Screenshots

### Group Management Interface
The main admin page where you can create, edit, and delete groups, plus view statistics.

### Pages List with Groups
The enhanced pages list showing group assignments and filter options.

### Quick Edit Integration
Group assignment integrated into WordPress's Quick Edit functionality.

## Technical Details

### Database Tables

The plugin creates two custom database tables:

- `wp_page_organizer_groups`: Stores group information (name, description, timestamps)
- `wp_page_organizer_page_groups`: Stores page-group relationships

### File Structure

```
page-organizer-plugin/
├── page-organizer.php          # Main plugin file
├── readme.txt                  # WordPress plugin readme
├── README.md                   # This file
├── admin/
│   ├── class-admin.php         # Admin functionality
│   ├── page-groups.php         # Admin page template
│   └── views/
│       ├── groups-page.php     # Group management interface
│       ├── quick-edit-fields.php
│       └── bulk-edit-fields.php
├── includes/
│   ├── class-database.php      # Database management
│   └── class-groups.php        # Group operations
└── assets/
    ├── css/
    │   └── admin.css           # Admin styles
    └── js/
        └── admin.js            # Admin JavaScript
```

### WordPress Hooks

The plugin integrates with WordPress using these hooks:

- `admin_menu` - Adds the admin menu
- `quick_edit_custom_box` - Adds group field to Quick Edit
- `bulk_edit_custom_box` - Adds group field to Bulk Edit
- `save_post` - Handles group assignment saves
- `manage_pages_columns` - Adds group column to pages list
- `restrict_manage_posts` - Adds filter dropdown
- `parse_query` - Handles page filtering

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- MySQL 5.6 or higher

## Development

### Plugin Information

- **Version**: 1.0
- **Author**: Search Click Grow
- **Website**: https://searchclickgrow.com
- **License**: GPL v2 or later

### Security Features

- Nonce verification for all form submissions
- Capability checks for user permissions
- SQL injection prevention using prepared statements
- XSS prevention using WordPress sanitization functions

### Performance Considerations

- Efficient database queries with proper indexing
- Minimal impact on page load times
- Only loads admin assets on relevant admin pages
- Optimized JavaScript for smooth user experience

## Support

For support, feature requests, or bug reports, please visit: https://searchclickgrow.com

## License

This plugin is licensed under the GPL v2 or later.

```
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
```

