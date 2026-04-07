# CLAUDE.md — WP Page Organizer
Last Updated: April 6, 2026

---

## App Identity
- Plugin name: WP Page Organizer
- Type: WordPress admin-only plugin (no hosting required)
- GitHub repo: github.com/MichaMcLain/wp-page-organizer
- Local path: ~/Desktop/App Projects/wp-page-organizer
- Current version: v1.2
- Status: Feature complete
- Slack channel: #agent-wporganizer

---

## Purpose
Helps WordPress admins organize large numbers of pages into custom-defined groups (Core, Services, Areas, Ads, etc.) without modifying actual pages. Groups are purely organizational overlays stored in separate DB tables. Built for SCG internal use and client sites managed through WP Engine.

---

## Tech Stack
- Language: PHP (WordPress standard)
- Admin UI: WordPress admin, jQuery, vanilla JS
- Styling: Custom admin.css
- Database: WordPress custom tables via dbDelta() and $wpdb
- Auth: WordPress native (manage_options capability, nonces)
- Hosting: None — plugin installed directly on WordPress sites
- Deployment: Manual — download zip from GitHub, install via wp-admin

---

## File Structure
```
wp-page-organizer/
├── wp-page-organizer.php     # Main plugin file — all PHP logic
├── admin/
│   └── groups-page.php       # Admin page view template
├── assets/
│   ├── admin.css
│   └── admin.js
└── README.md
```

All logic lives in the single PageOrganizerPlugin singleton class. No separate class files.

---

## Database
- wp_page_organizer_groups — group definitions (id, name, description, color, timestamps)
- wp_page_organizer_page_groups — junction table (page_id → group_id)
- One page can belong to only one group at a time
- Data preserved on deactivation, deleted on uninstall (opt-in checkbox)

---

## What Is Built and Working
- Create, edit, delete page groups with name, description, and color
- Color picker: 6 preset colors + custom hex
- Page Group column in wp-admin Pages list (colored badges)
- Filter pages by group (including Ungrouped filter)
- Quick Edit integration — assign group inline
- Bulk Edit integration — assign multiple pages at once
- Export groups to JSON
- Import groups from JSON
- Plugin settings: data retention toggle on uninstall
- Help and Instructions modal
- Statistics panel: total groups, ungrouped count, grouped count
- Database auto-upgrade on version change

---

## Bug History — Quick Edit Group Stripping (Fixed in v1.2)
Symptom: Opening Quick Edit on any page would silently strip the page group on save, even if no change was intended.

Root cause: Quick edit dropdown defaulted to "Ungrouped" (value 0), so any Update click would overwrite the group.

Fix (v1.2): Added "— No Change —" (value -1) as the default option. save_post now skips the assignment entirely when value is -1. Rewrote JS selectors so the dropdown correctly pre-selects the current group when Quick Edit opens. Removed all debug console.log statements.

---

## What Is Planned
- Nothing formally scoped — plugin is feature complete
- LocalWP test environment recommended for future iteration

---

## Deployment Process
1. Make changes locally
2. git add -A && git commit -m "vX.X.X - description" && git push origin main
3. Download zip from GitHub repo
4. In wp-admin → Plugins → Add New → Upload Plugin → install zip
5. Test on live or staging WordPress site

No Railway. No auto-deploy. Fully manual install.

---

## Key Conventions
- Nonce key for main forms: page_organizer_groups
- Bulk edit field name: page_organizer_group_bulk
- Quick edit field name: page_organizer_group
- Asset handles: page-organizer-admin (JS and CSS)
- JS global: pageOrganizerAjax (ajaxurl, nonce, strings)
- save_post hook handles both quick edit and bulk edit saves
