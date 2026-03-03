/**
 * Frontend execution logic for LGL Shortcodes.
 * Handles Select2 initialization and AJAX operations.
 */
jQuery(document).ready(function ($) {
    search_form();
    add_to_wishlist();
});

function search_form() {
    // Initialize Select2 on target classes
    jQuery('.lgl-select2').select2({
        width: '100%'
    });

    // Dependent Dropdown Logic (Make -> Model)
    jQuery('#lgl_make').on('change', function () {
        let make_id = jQuery(this).val();
        let $model_select = jQuery('#lgl_model');

        // Reset model dropdown
        $model_select.empty().append('<option value="">Select Model</option>').prop('disabled', true);

        if (make_id) {
            jQuery.ajax({
                url: lgl_ajax_obj.ajax_url,
                type: 'POST',
                data: {
                    action: 'lgl_get_models',
                    nonce: lgl_ajax_obj.nonce,
                    make_id: make_id
                },
                success: function (response) {
                    if (response.success && response.data.length > 0) {
                        jQuery.each(response.data, function (index, item) {
                            $model_select.append(new Option(item.text, item.id, false, false));
                        });
                        $model_select.prop('disabled', false).trigger('change');
                    }
                }
            });
        }
    });

    // Handle Search Execution
    jQuery('#lgl-search-form, #lgl-sort-order').on('submit change', function (e) {
        if (e.type === 'submit') e.preventDefault();

        // Serialize primary form and combine with sorting value
        let formData = jQuery('#lgl-search-form').serialize() + '&sort_order=' + jQuery('#lgl-sort-order').val();
        let postType = jQuery('#lgl_target_post_type').val();

        // UI State management
        jQuery('#lgl-loader').show();
        jQuery('#lgl-results-grid').css('opacity', '0.5');

        jQuery.ajax({
            url: lgl_ajax_obj.ajax_url,
            type: 'POST',
            data: {
                action: 'lgl_fetch_results',
                nonce: lgl_ajax_obj.nonce,
                post_type: postType,
                form_data: formData
            },
            success: function (response) {
                if (response.success) {
                    jQuery('#lgl-results-grid').html(response.data.html);
                    jQuery('#lgl-results-count').html('Showing ' + response.data.count + ' results');
                } else {
                    alert('Error fetching results.');
                }
            },
            error: function () {
                alert('A server error occurred. Please try again.');
            },
            complete: function () {
                jQuery('#lgl-loader').hide();
                jQuery('#lgl-results-grid').css('opacity', '1');
            }
        });
    });

    // Trigger initial search to populate grid on load
    if (jQuery('#lgl-search-form').length) {
        jQuery('#lgl-search-form').trigger('submit');
    }

}

function add_to_wishlist() {
    // Add Notification Container to Body
    jQuery('body').append('<div id="lgl-notification-container"></div>');

    /**
     * Display a toast notification.
     * @param {string} message - The message to display.
     * @param {string} type - 'success' or 'error' for styling.
     */
    function showNotification(message, type = 'success') {
        const $container = jQuery('#lgl-notification-container');
        const $notification = jQuery('<div class="lgl-toast lgl-toast-' + type + '">' + message + '</div>');

        $container.append($notification);

        // Trigger reflow for transition
        $notification[0].offsetHeight;

        // Show
        $notification.addClass('show');

        // Remove after 3 seconds
        setTimeout(function () {
            $notification.removeClass('show');
            setTimeout(function () {
                $notification.remove();
            }, 300); // Matches CSS transition duration
        }, 3000);
    }

    // Handle Wishlist Click
    jQuery(document).on('click', '.lgl-wishlist-btn', function (e) {
        e.preventDefault();

        let $btn = jQuery(this);
        let postId = $btn.data('id');
        // Retrieve title from the data attribute as requested
        let postTitle = $btn.data('title');

        if ($btn.hasClass('processing')) return;

        $btn.addClass('processing');

        $.ajax({
            url: lgl_ajax_obj.ajax_url,
            type: 'POST',
            data: {
                action: 'lgl_add_to_wishlist',
                nonce: lgl_ajax_obj.nonce,
                post_id: postId
            },
            success: function (response) {
                if (response.success) {
                    if (response.data.status === 'added') {
                        $btn.addClass('added');
                        showNotification(postTitle + ' added to wishlist!');
                    } else if (response.data.status === 'removed') {
                        $btn.removeClass('added');
                        showNotification(postTitle + ' removed from wishlist.', 'error'); // Using error type for styling removal
                    }
                } else {
                    showNotification('Error: ' + (response.data || 'Unknown error.'), 'error');
                }
            },
            error: function () {
                showNotification('A server error occurred.', 'error');
            },
            complete: function () {
                $btn.removeClass('processing');
            }
        });
    });
}