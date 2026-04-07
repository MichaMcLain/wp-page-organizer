# CHANGELOG — WP Page Organizer

---

## [v1.2] — 2026-04-06
Bug fix
Fixed critical quick edit bug where opening Quick Edit for any reason would strip the page group assignment on save. Added "No Change" default option to quick edit dropdown. Updated JS selectors for correct group display on open.

## [v1.1.1] — 2026-04-05
Bug fix (in progress)
Added 5 console.log debug statements to setupQuickEdit in admin.js to diagnose Quick Edit group display bug. Awaiting team console output to identify root cause.

## [v1.1.0] — 2026-03-01
Refactor
Consolidated plugin from 12 files to 4. Fixed Quick Edit sticky bug using data-group-id approach. Removed dead code class files (class-database.php, class-groups.php, class-admin.php, page-groups.php).

## [v1.0.0] — 2026-01-01
New feature
Initial release. Page groups CRUD, colored badges, filter by group, Quick Edit, Bulk Edit, JSON export/import, statistics panel, help modal, database auto-upgrade system.
