<?php
/**
 * Admin page for managing page groups
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$admin = new PageOrganizerAdmin();
$admin->render_groups_page();
?>

