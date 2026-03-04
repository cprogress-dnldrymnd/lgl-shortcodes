/**
 * Frontend execution logic for LGL Shortcodes.
 * Handles Select2 initialization and AJAX operations.
 */
(function ($) {
    'use strict';

    $(document).ready(function () {
        search_form();
        add_to_wishlist();
        gallery_slider();
        tabs();
        initLGLMiniWishlist();
        sharevehicle();
    });


    function tabs() {
        if ($('.lgl-tabs-js').length > 0) {
            $('.lgl-tabs-js .lgl-nav-item').on('click', function (e) {
                e.preventDefault();
                $(this).addClass('lgl-is-active').siblings().removeClass('lgl-is-active');
                $($.attr(this, 'href')).addClass('lgl-is-active').siblings().removeClass('lgl-is-active');
            });
        }
    }
    /**
         * Initializes the search form features.
         * Binds Select2, handles dependent make/model dropdowns, and manages the AJAX submission and pagination UI.
         *
         * @return void
         */
    function search_form() {
        let currentPage = 1;

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

        // Handle Search Execution (Reset to Page 1 on strict filter changes)
        $('#lgl-search-form, #lgl-sort-order').on('submit change', function (e) {
            if (e.type === 'submit') e.preventDefault();
            currentPage = 1;
            execute_search();
        });

        // Intercept standard WordPress pagination clicks for AJAX handling
        $(document).on('click', '.lgl-pagination-wrap a.page-numbers', function (e) {
            e.preventDefault();

            let href = $(this).attr('href');

            // Extract the page number using regex
            let match = href.match(/paged=(\d+)/);

            // If match is found, parse it. If null (WordPress stripped it for Page 1), default to 1.
            if (match) {
                currentPage = parseInt(match[1], 10);
            } else {
                currentPage = 1;
            }

            // Execute the AJAX fetch with the updated page state
            execute_search();

            // UX: Scroll back to top of results when paginating
            $('html, body').animate({
                scrollTop: $('.lgl-results-wrapper').offset().top - 40
            }, 400);
        });

        /**
         * Compiles form parameters and dispatches the AJAX search payload.
         *
         * @return void
         */
        function execute_search() {
            // Serialize primary form and combine with sorting value
            let formData = $('#lgl-search-form').serialize() + '&sort_order=' + $('#lgl-sort-order').val();
            let postType = $('#lgl_target_post_type').val();

            // UI State management
            $('#lgl-loader').show();
            $('#lgl-results-grid').css('opacity', '0.5');
            $('.lgl-pagination-wrap').css('opacity', '0.5');

            $.ajax({
                url: lgl_ajax_obj.ajax_url,
                type: 'POST',
                data: {
                    action: 'lgl_fetch_results',
                    nonce: lgl_ajax_obj.nonce,
                    post_type: postType,
                    form_data: formData,
                    paged: currentPage
                },
                success: function (response) {
                    if (response.success) {
                        $('#lgl-results-grid').html(response.data.html);
                        $('.lgl-pagination-wrap').html(response.data.pagination);

                        // Update UI string dynamically
                        let visibleCount = $('#lgl-results-grid .lgl-post').length;
                        $('#lgl-results-count').html('Showing ' + visibleCount + ' of ' + response.data.count + ' results');
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
                    $('.lgl-pagination-wrap').css('opacity', '1');
                }
            });
        }

        // Trigger initial search to populate grid on load
        if ($('#lgl-search-form').length) {
            execute_search();
        }
    }

    /**
     * Initializes the mini wishlist UI components and binds necessary event listeners.
     * * @return {void}
     */
    function initLGLMiniWishlist() {
        const $wrapper = jQuery('.lgl-mini-wishlist-wrapper');
        const $toggle = jQuery('.lgl-mini-wishlist-toggle-trigger');
        const $dropdown = jQuery('.lgl-mini-wishlist-dropdown');
        const $content = jQuery('.lgl-mini-wishlist-content');
        const $badge = jQuery('.lgl-wishlist-count');

        if (!$wrapper.length) return;

        /**
         * Toggles the active state of the mini wishlist dropdown.
         */
        $toggle.on('click', function (e) {
            e.preventDefault();
            $dropdown.toggleClass('is-active');
        });

        /**
         * Closes the dropdown when a click event occurs outside of the wrapper component.
         */
        jQuery(document).on('click', function (e) {
            if (!$wrapper.is(e.target) && $wrapper.has(e.target).length === 0) {
                $dropdown.removeClass('is-active');
            }
        });

        /**
         * Executes the AJAX request to remove a vehicle from the wishlist 
         * and delegates DOM updates on success.
         */
        $content.on('click', '.lgl-remove-btn', function (e) {
            e.preventDefault();
            const $btn = jQuery(this);
            const postId = $btn.data('id');

            // Apply visual degradation to indicate processing
            $btn.css('opacity', '0.5').css('pointer-events', 'none');

            jQuery.ajax({
                url: lgl_ajax_obj.ajax_url,
                type: 'POST',
                data: {
                    action: 'lgl_add_to_wishlist',
                    post_id: postId,
                    nonce: lgl_ajax_obj.nonce
                },
                success: function (response) {
                    if (response.success) {
                        $badge.text(response.data.count);
                        refreshMiniWishlistHtml();
                    } else {
                        $btn.css('opacity', '1').css('pointer-events', 'auto');
                    }
                },
                error: function () {
                    $btn.css('opacity', '1').css('pointer-events', 'auto');
                }
            });
        });

        /**
         * Triggers a subsequent AJAX call to refresh the HTML payload of the dropdown 
         * list to maintain synchronization with the backend state.
         * * @return {void}
         */
        function refreshMiniWishlistHtml() {
            jQuery.ajax({
                url: lgl_ajax_obj.ajax_url,
                type: 'POST',
                data: {
                    action: 'lgl_refresh_mini_wishlist',
                    nonce: lgl_ajax_obj.nonce
                },
                success: function (response) {
                    if (response.success) {
                        $content.html(response.data.html);
                    }
                }
            });
        }
    }

    function sharevehicle() {
        $('.lgl-vehicle-share-btn').on('click', function (e) {
            e.preventDefault();

            const $btn = $(this);
            const urlToCopy = $btn.data('url');
            const vehicleTitle = $btn.data('title') || 'Vehicle';
            const successMessage = `Link for ${vehicleTitle} copied to clipboard!`;

            if (!urlToCopy) {
                console.error('Share Button Error: Missing data-url attribute.');
                return;
            }

            /**
             * Executes the clipboard copy operation using the most appropriate API available.
             * * @param {string} text - The string (URL) to be copied to the clipboard.
             * @param {string} successMsg - The notification message to display upon a successful copy.
             */
            const executeCopy = (text, successMsg) => {
                // Modern Async Clipboard API (Requires secure context / HTTPS)
                if (navigator.clipboard && window.isSecureContext) {
                    navigator.clipboard.writeText(text)
                        .then(() => {
                            showNotification(successMsg, 'success');
                        })
                        .catch((err) => {
                            console.error('Clipboard API Write Error: ', err);
                            showNotification('Failed to copy link. Please try again.', 'error');
                        });
                } else {
                    // Fallback implementation for older browsers or local/non-secure environments
                    const $tempInput = $('<input>');
                    $('body').append($tempInput);
                    $tempInput.val(text).select();

                    try {
                        const successful = document.execCommand('copy');
                        if (successful) {
                            showNotification(successMsg, 'success');
                        } else {
                            throw new Error('execCommand returned false');
                        }
                    } catch (err) {
                        console.error('Fallback Clipboard Copy Error: ', err);
                        showNotification('Failed to copy link. Please try again.', 'error');
                    } finally {
                        // Always clean up the temporary DOM element
                        $tempInput.remove();
                    }
                }
            };

            // Trigger the copy logic
            executeCopy(urlToCopy, successMessage);
        });
    }

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

    /**
         * Binds click events for the wishlist functionality.
         * Handles AJAX requests to add or remove items from the user's wishlist and triggers toast notifications.
         * * @return {void}
         */
    function add_to_wishlist() {
        // Add Notification Container to Body
        $('body').append('<div id="lgl-notification-container"></div>');

        // Handle Wishlist Click
        $(document).on('click', '.lgl-wishlist-btn', function (e) {
            e.preventDefault();

            let $btn = $(this);
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

    function gallery_slider() {
        /* Gallery Slider */
        if ($('.js-gallery-slider').length > 0) {
            $('.js-gallery-slider-for').slick({
                slidesToShow: 1,
                slidesToScroll: 1,
                fade: true,
                arrows: false,
                asNavFor: '.js-gallery-slider-nav',
                prevArrow: '<button type=\"button\" class=\"slick-prev\">Prev</button>',
                nextArrow: '<button type=\"button\" class=\"slick-next\">Next</button>'
            });
            $('.js-gallery-slider-nav').slick({
                slidesToShow: 4,
                slidesToScroll: 1,
                arrows: false,
                focusOnSelect: true,
                asNavFor: '.js-gallery-slider-for',
                responsive: [
                    {
                        breakpoint: 600,
                        settings: {
                            slidesToShow: 3
                        }
                    }
                ]
            });
        }
    }
})(jQuery);


/**
 * LGL Compare Grid Handler
 * Manages the global state of vehicle comparison buttons via a multidimensional JSON 
 * payload, allowing concurrent staging of Caravans, Motorhomes, and Campervans.
 */
(function ($) {
    'use strict';

    // Master key for multidimensional comparison storage
    const STATE_KEY_DATA = 'lgl_compare_data';

    /**
     * Retrieves the master comparison object, guaranteeing base structural integrity.
     * * @return {Object} The parsed storage object.
     */
    function getCompareData() {
        let data = localStorage.getItem(STATE_KEY_DATA);
        return data ? JSON.parse(data) : { caravan: [], motorhome: [], campervan: [] };
    }

    /**
     * Scans the DOM for all compare buttons and syncs their visual state 
     * based on the current active lists in the JSON payload.
     * * @return {void}
     */
    function syncCompareButtonStates() {
        let data = getCompareData();

        $('.lgl-compare-btn').each(function () {
            const btn = $(this);
            const postId = btn.attr('data-post-id');
            const postType = btn.attr('data-post-type');
            const postTitle = btn.attr('data-title') || 'Vehicle';

            // Verify if this specific ID exists within its designated vehicle type array
            if (data[postType] && data[postType].includes(postId)) {
                btn.addClass('is-active');
                btn.find('.lgl-compare-text').text('Added to Compare');
                showNotification(postTitle + ' added to compare list!');
            } else {
                btn.removeClass('is-active');
                btn.find('.lgl-compare-text').text('Compare');
                showNotification(postTitle + ' removed from compare list!');
            }
        });
    }

    /**
     * Intercepts clicks on compare buttons globally using event delegation.
     * Organizes payloads into respective type buckets.
     * * @param {Event} e The jQuery click event object.
     * @return {void}
     */
    $(document).on('click', '.lgl-compare-btn', function (e) {
        e.preventDefault();

        const btn = $(this);
        const targetId = btn.attr('data-post-id');
        const targetType = btn.attr('data-post-type');

        if (!targetId || !targetType) return;

        let data = getCompareData();

        // Ensure array exists for type fallback
        if (!data[targetType]) {
            data[targetType] = [];
        }

        if (!data[targetType].includes(targetId)) {
            // Enforce capacity limits per category
            if (data[targetType].length >= 4) {
                alert('Comparison capacity reached for ' + targetType + 's. Please remove an existing vehicle to add a new one.');
                return;
            }
            data[targetType].push(targetId);
        } else {
            // Toggle removal
            data[targetType] = data[targetType].filter(id => id !== targetId);
        }

        // Commit structure back to stringified storage
        localStorage.setItem(STATE_KEY_DATA, JSON.stringify(data));

        // Immediately sync the UI to reflect the new state
        syncCompareButtonStates();
    });

    // Run synchronization on initial page load
    $(document).ready(function () {
        syncCompareButtonStates();
    });

    // Re-sync buttons when the grid is filtered/paginated
    $(document).ajaxComplete(function (event, xhr, settings) {
        if (settings.data && settings.data.indexOf('action=lgl_fetch_results') !== -1) {
            syncCompareButtonStates();
        }
    });

    // Listen for custom event fired from the lgl-compare.php template when an item is removed via the table
    document.addEventListener('lgl_compare_updated', function () {
        syncCompareButtonStates();
    });

})(jQuery);