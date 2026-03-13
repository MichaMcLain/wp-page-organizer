/**
 * Admin JavaScript for Page Organizer Plugin
 */

jQuery(document).ready(function($) {
    
    // AJAX functionality for group management
    var PageOrganizerAdmin = {
        
        init: function() {
            // Quick edit functionality
            this.setupQuickEdit();
            
            // Bulk edit functionality
            this.setupBulkEdit();
            
            // Color picker functionality
            this.setupColorPicker();
            
            // AJAX form submissions
            this.setupAjaxForms();
        },
        
        setupQuickEdit: function() {
            $(document).on('click', 'a.editinline', function() {
                var $row = $(this).closest('tr');
                var $badge = $row.find('.column-page_group .page-group-badge:not(.ungrouped)');
                var groupId = $badge.length ? $badge.first().data('group-id') : '0';
                setTimeout(function() {
                    $('.page-organizer-group-select').val(groupId || '0');
                }, 200);
            });
        },
        
        setupBulkEdit: function() {
            // Bulk edit works through regular form submission, no AJAX needed
            // The save_page_group method handles both quick edit and bulk edit
            console.log('Bulk edit setup: Using regular form submission like Quick Edit');
        },
        
        setupColorPicker: function() {
            // Handle preset color selection
            $(document).on('click', '.color-option', function() {
                var color = $(this).data('color');
                
                // Update selection
                $('.color-option').removeClass('selected');
                $(this).addClass('selected');
                
                // Update inputs
                $('#group_color').val(color);
                $('#custom_color').val(color);
            });
            
            // Handle custom color picker
            $(document).on('change', '#custom_color', function() {
                var color = $(this).val();
                
                // Clear preset selection
                $('.color-option').removeClass('selected');
                
                // Update text input
                $('#group_color').val(color);
            });
            
            // Handle manual hex input
            $(document).on('input', '#group_color', function() {
                var color = $(this).val();
                
                // Validate hex format
                if (/^#[a-fA-F0-9]{6}$/.test(color)) {
                    // Update color picker
                    $('#custom_color').val(color);
                    
                    // Check if it matches a preset
                    var $matchingPreset = $('.color-option[data-color="' + color + '"]');
                    $('.color-option').removeClass('selected');
                    if ($matchingPreset.length) {
                        $matchingPreset.addClass('selected');
                    }
                }
            });
        },
        
        setupAjaxForms: function() {
            // Removed AJAX form handling to prevent interference with bulk edit
            // All forms now use regular WordPress form submission
        },
        

        
        // Utility functions
        showNotice: function(message, type) {
            type = type || 'success';
            var $notice = $('<div class="notice notice-' + type + ' is-dismissible page-organizer-notice"><p>' + message + '</p></div>');
            $('.wrap h1').after($notice);
            
            // Auto-dismiss after 5 seconds
            setTimeout(function() {
                $notice.fadeOut();
            }, 5000);
        },
        
        confirmDelete: function(message) {
            return confirm(message || pageOrganizerAjax.strings.confirm_delete);
        }
    };
    
    // Initialize admin functionality
    PageOrganizerAdmin.init();
    
    // Make PageOrganizerAdmin globally available
    window.PageOrganizerAdmin = PageOrganizerAdmin;
    
    // Handle delete confirmations
    $(document).on('submit', 'form[onsubmit*="confirm"]', function(e) {
        var confirmMessage = $(this).attr('onsubmit').match(/confirm\('([^']+)'\)/);
        if (confirmMessage && confirmMessage[1]) {
            if (!confirm(confirmMessage[1])) {
                e.preventDefault();
                return false;
            }
        }
    });
    
    // Enhanced filter functionality
    $(document).on('change', 'select[name="page_group_filter"]', function() {
        $(this).closest('form').submit();
    });
    
    // Keyboard shortcuts
    $(document).on('keydown', function(e) {
        // Ctrl/Cmd + G to focus on group filter
        if ((e.ctrlKey || e.metaKey) && e.key === 'g') {
            e.preventDefault();
            $('select[name="page_group_filter"]').focus();
        }
        
        // Escape key to close help modal
        if (e.key === 'Escape') {
            $('#page-organizer-help-modal').hide();
        }
    });
    
    // Help modal functionality
    $(document).on('click', '.page-organizer-help-btn', function() {
        $('#page-organizer-help-modal').show();
    });
    
    $(document).on('click', '.page-organizer-help-close', function() {
        $('#page-organizer-help-modal').hide();
    });
    
    $(document).on('click', '.page-organizer-help-modal', function(e) {
        if (e.target === this) {
            $(this).hide();
        }
    });
    
    // Tooltips
    $(document).on('mouseenter', '.page-organizer-tooltip', function() {
        var $this = $(this);
        var tooltip = $this.attr('data-tooltip');
        if (tooltip) {
            $this.attr('title', tooltip);
        }
    });
    
    // Auto-save draft functionality for group forms (future enhancement)
    var autoSaveTimer;
    $(document).on('input', '#group_name, #group_description', function() {
        clearTimeout(autoSaveTimer);
        autoSaveTimer = setTimeout(function() {
            // Auto-save logic could be implemented here
        }, 2000);
    });
    
    // Form validation
    $(document).on('submit', 'form[action=""]', function(e) {
        var $nameField = $(this).find('#group_name');
        if ($nameField.length && !$nameField.val().trim()) {
            e.preventDefault();
            $nameField.focus();
            PageOrganizerAdmin.showNotice('Group name is required.', 'error');
            return false;
        }
    });
    
    // Character counter for description field
    $(document).on('input', '#group_description', function() {
        var $this = $(this);
        var maxLength = 500; // Reasonable limit
        var currentLength = $this.val().length;
        var $counter = $this.siblings('.char-counter');
        
        if (!$counter.length) {
            $counter = $('<div class="char-counter" style="font-size: 12px; color: #666; margin-top: 5px;"></div>');
            $this.after($counter);
        }
        
        $counter.text(currentLength + ' characters');
        
        if (currentLength > maxLength) {
            $counter.css('color', '#d63638');
        } else {
            $counter.css('color', '#666');
        }
    });
});

// Global utility functions
window.pageOrganizerUtils = {
    
    // Format group name for display
    formatGroupName: function(name) {
        return name.charAt(0).toUpperCase() + name.slice(1).toLowerCase();
    },
    
    // Validate group name
    validateGroupName: function(name) {
        return name && name.trim().length > 0 && name.trim().length <= 255;
    },
    
    // Get current page post type
    getCurrentPostType: function() {
        var urlParams = new URLSearchParams(window.location.search);
        return urlParams.get('post_type') || 'post';
    },
    
    // Check if current page is pages list
    isPagesList: function() {
        return this.getCurrentPostType() === 'page' && window.location.pathname.includes('edit.php');
    }
};

