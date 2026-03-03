/**
 * Frontend execution logic for LGL Shortcodes.
 * Handles Select2 initialization and AJAX operations.
 */
(function($) {
    'use strict';

    $(document).ready(function() {
        search_form();
        add_to_wishlist();
    });

    function search_form() {
        // Initialize Select2 on target classes
        $('.lgl-select2').select2({
            width: '100%'
        });

        // Dependent Dropdown Logic (Make -> Model)
        $('#lgl_make').on('change', function () {
            let make_id = $(this).val();
            let $model_select = $('#lgl_model');

            // Reset model dropdown
            $model_select.empty().append('<option value="">Select Model</option>').prop('disabled', true);

            if (make_id) {
                $.ajax({
                    url: lgl_ajax_obj.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'lgl_get_models',
                        nonce: lgl_ajax_obj.nonce,
                        make_id: make_id
                    },
                    success: function (response) {
                        if (response.success && response.data.length > 0) {
                            $.each(response.data, function (index, item) {
                                $model_select.append(new Option(item.text, item.id, false, false));
                            });
                            $model_select.prop('disabled', false).trigger('change');
                        }
                    }
                });
            }
        });

        // Handle Search Execution
        $('#lgl-search-form, #lgl-sort-order').on('submit change', function (e) {
            if (e.type === 'submit') e.preventDefault();

            // Serialize primary form and combine with sorting value
            let formData = $('#lgl-search-form').serialize() + '&sort_order=' + $('#lgl-sort-order').val();
            let postType = $('#lgl_target_post_type').val();

            // UI State management
            $('#lgl-loader').show();
            $('#lgl-results-grid').css('opacity', '0.5');

            $.ajax({
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
                        $('#lgl-results-grid').html(response.data.html);
                        $('#lgl-results-count').html('Showing ' + response.data.count + ' results');
                    } else {
                        alert('Error fetching results.');
                    }
                },
                error: function () {
                    alert('A server error occurred. Please try again.');
                },
                complete: function () {
                    $('#lgl-loader').hide();
                    $('#lgl-results-grid').css('opacity', '1');
                }
            });
        });

        // Trigger initial search to populate grid on load
        if ($('#lgl-search-form').length) {
            $('#lgl-search-form').trigger('submit');
        }
    }

    function add_to_wishlist() {
        // Add Notification Container to Body
        $('body').append('<div id="lgl-notification-container"></div>');

        /**
         * Display a toast notification.
         * @param {string} message - The message to display.
         * @param {string} type - 'success' or 'error' for styling.
         */
        function showNotification(message, type = 'success') {
            const $container = $('#lgl-notification-container');
            const $notification = $('<div class="lgl-toast lgl-toast-' + type + '">' + message + '</div>');

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

        // Handle Wishlist Click (Delegated for dynamic AJAX elements)
        $(document).on('click', '.lgl-wishlist-btn', function (e) {
            e.preventDefault();

            let $btn = $(this);
            let postId = $btn.data('id');

            // Prevent multiple clicks
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
                        } else if (response.data.status === 'removed') {
                            $btn.removeClass('added');
                        }
                    } else {
                        alert('Error updating wishlist: ' + (response.data || 'Unknown error.'));
                    }
                },
                error: function () {
                    alert('A server error occurred. Please try again.');
                },
                complete: function () {
                    $btn.removeClass('processing');
                }
            });
        });
    }

})(jQuery);