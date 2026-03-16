/**
 * Frontend execution logic for LGL Shortcodes.
 * Handles Select2 initialization and AJAX operations.
 */
(function ($) {
    'use strict';

    $(document).ready(function () {
        search_form();
        add_to_wishlist();
        vehicle_slider();
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
        let isUpdatingFilters = false;

        // Initialize Select2 on target classes
        $('.lgl-select2').select2({
            width: '100%'
        });

        // Dependent Dropdown Logic (Make -> Model)
        $('#lgl_make').on('change', function () {
            let make_id = $(this).val();
            let $model_select = $('#lgl_model');

            // Resolve current post_type: prefer the hidden input (type-specific search form),
            // fall back to the vehicle type select (global search form).
            let postType = $('#lgl_target_post_type').val() || '';
            if (!postType) {
                const selectedText = $('#lgl_post_type').find('option:selected').text().trim().toLowerCase();
                postType = selectedText.replace(/s$/, '');
            }

            // Reset model dropdown
            $model_select.empty()
                .append('<option value="">Select Model</option>')
                .prop('disabled', true)
                .trigger('change');

            if (make_id) {
                $.ajax({
                    url: lgl_ajax_obj.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'lgl_get_models',
                        nonce: lgl_ajax_obj.nonce,
                        make_id: make_id,
                        post_type: postType
                    },
                    success: function (response) {
                        if (response.success && response.data.length > 0) {
                            $model_select.empty().append('<option value="">All Models</option>');
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
        $('#lgl-search-form.lgl-filter-form-ajax, #lgl-sort-order').on('submit change', function (e) {
            if (e.type === 'submit') e.preventDefault();
            currentPage = 1;
            execute_search();
        });


        //search redirect + dynamic make loading
        $('#lgl-search-form.lgl-filter-form-ajax, #lgl-sort-order').on('submit change', function (e) {
            if (e.type === 'submit') e.preventDefault();
            if (isUpdatingFilters) return; // Ignore events fired by our own repopulation
            currentPage = 1;
            execute_search();
            update_filter_options();
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
         * Fetches valid filter options for the current filter state and repopulates
         * condition, berth, and price dropdowns so impossible combinations are impossible.
         */
        function update_filter_options() {
            const postType = $('#lgl_target_post_type').val();
            if (!postType) return; // Global search form — no post_type locked in yet

            const formData = $('#lgl-search-form').serialize();

            $.ajax({
                url: lgl_ajax_obj.ajax_url,
                type: 'POST',
                data: {
                    action: 'lgl_get_filter_options',
                    nonce: lgl_ajax_obj.nonce,
                    post_type: postType,
                    form_data: formData,
                },
                success: function (response) {
                    if (!response.success) return;
                    const d = response.data;

                    isUpdatingFilters = true;

                    _repopulate_select('#lgl_condition', d.conditions, 'Any Condition');
                    _repopulate_select('#lgl_berth', d.berths, 'Any Berth');
                    _repopulate_price_select('#lgl_price_min', d.prices, 'Min Price');
                    _repopulate_price_select('#lgl_price_max', d.prices, 'Max Price');

                    isUpdatingFilters = false;
                }
            });
        }

        /**
         * Rebuilds a plain-value select (condition, berth) with only the provided values.
         * Preserves the current selection if it is still valid; resets to "" otherwise.
         */
        function _repopulate_select(selector, values, placeholder) {
            const $el = $(selector);
            const current = $el.val();

            $el.empty().append(new Option(placeholder, ''));

            let stillValid = false;
            $.each(values, function (i, val) {
                if (String(val) === String(current)) stillValid = true;
                $el.append(new Option(val, val, false, String(val) === String(current)));
            });

            if (current && !stillValid) {
                $el.val('');
            }

            $el.trigger('change'); // Refresh Select2 display
        }

        /**
         * Rebuilds a price select with {value, label} objects.
         * Preserves the current selection if it is still in the new price list.
         */
        function _repopulate_price_select(selector, prices, placeholder) {
            const $el = $(selector);
            const current = parseFloat($el.val()) || 0;

            $el.empty().append(new Option(placeholder, ''));

            let stillValid = false;
            $.each(prices, function (i, item) {
                if (item.value === current) stillValid = true;
                $el.append(new Option(item.label, item.value, false, item.value === current));
            });

            if (current && !stillValid) {
                $el.val('');
            }

            $el.trigger('change'); // Refresh Select2 display
        }

        /**
         * Compiles form parameters and dispatches the AJAX search payload.
         *
         * @return void
         */
        function execute_search() {
            // Serialize primary form and combine with sorting value
            let formData = $('#lgl-search-form').serialize() + '&sort_order=' + $('#lgl-sort-order').val();
            let postType = $('#lgl_target_post_type').val();
            let limit = parseInt($('#lgl-results-grid').data('limit'), 10) || 9;

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
                    paged: currentPage,
                    limit: limit,
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
            update_filter_options();
        }
    }



    /**
     * Initializes the mini wishlist UI components and binds necessary event listeners.
     * @return {void}
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
         * @return {void}
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
             * @param {string} text - The string (URL) to be copied to the clipboard.
             * @param {string} successMsg - The notification message to display upon a successful copy.
             */
            const executeCopy = (text, successMsg) => {
                // Modern Async Clipboard API (Requires secure context / HTTPS)
                if (navigator.clipboard && window.isSecureContext) {
                    navigator.clipboard.writeText(text)
                        .then(() => {
                            window.showNotification(successMsg, 'success');
                        })
                        .catch((err) => {
                            console.error('Clipboard API Write Error: ', err);
                            window.showNotification('Failed to copy link. Please try again.', 'error');
                        });
                } else {
                    // Fallback implementation for older browsers or local/non-secure environments
                    const $tempInput = $('<input>');
                    $('body').append($tempInput);
                    $tempInput.val(text).select();

                    try {
                        const successful = document.execCommand('copy');
                        if (successful) {
                            window.showNotification(successMsg, 'success');
                        } else {
                            throw new Error('execCommand returned false');
                        }
                    } catch (err) {
                        console.error('Fallback Clipboard Copy Error: ', err);
                        window.showNotification('Failed to copy link. Please try again.', 'error');
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
     * Display a toast notification globally.
     * Attached to the window object to prevent ReferenceErrors across different IIFE scopes.
     * @param {string} message - The message to display.
     * @param {string} type - 'success' or 'error' for styling.
     * @return {void}
     */
    window.showNotification = function (message, type = 'success') {
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
    };

    /**
     * Binds click events for the wishlist functionality.
     * Handles AJAX requests to add or remove items from the user's wishlist and triggers toast notifications.
     * @return {void}
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
                            window.showNotification(postTitle + ' added to wishlist!');
                        } else if (response.data.status === 'removed') {
                            $btn.removeClass('added');
                            window.showNotification(postTitle + ' removed from wishlist.', 'error'); // Using error type for styling removal
                        }
                    } else {
                        window.showNotification('Error: ' + (response.data || 'Unknown error.'), 'error');
                    }
                },
                error: function () {
                    window.showNotification('A server error occurred.', 'error');
                },
                complete: function () {
                    $btn.removeClass('processing');
                }
            });
        });
    }

    function vehicle_slider() {
        //vehicle slider
        $('.vehicle-slider-js').slick({
            mobileFirst: true, // Reverses default max-width breakpoint calculation to min-width
            slidesToShow: 1,
            slidesToScroll: 1,
            fade: false, // Disabled to allow standard inline block layout for multiple slides
            arrows: true,
            responsive: [
                {
                    // Triggers at min-width: 768px
                    breakpoint: 767,
                    settings: {
                        slidesToShow: 2,
                        slidesToScroll: 2,
                    }
                },
                {
                    // Triggers at min-width: 1300px
                    breakpoint: 1300,
                    settings: {
                        slidesToShow: 3,
                        slidesToScroll: 3,
                    }
                }
            ],
            prevArrow: '<button type="button" class="slick-prev"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left" viewBox="0 0 16 16"> <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8"/> </svg></button>',
            nextArrow: '<button type="button" class="slick-next"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-right" viewBox="0 0 16 16"> <path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8"/> </svg></button>'
        });
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
     * @return {Object} The parsed storage object.
     */
    function getCompareData() {
        let data = localStorage.getItem(STATE_KEY_DATA);
        return data ? JSON.parse(data) : { caravan: [], motorhome: [], campervan: [] };
    }

    /**
     * Scans the DOM for all compare buttons and syncs their visual state 
     * based on the current active lists in the JSON payload.
     * @return {void}
     */
    function syncCompareButtonStates() {
        let data = getCompareData();

        $('.lgl-compare-btn').each(function () {
            const btn = $(this);
            const postId = btn.attr('data-post-id');
            const postType = btn.attr('data-post-type');

            // Verify if this specific ID exists within its designated vehicle type array
            if (data[postType] && data[postType].includes(postId)) {
                btn.addClass('is-active');
                btn.find('.lgl-compare-text').text('Added to Compare');
            } else {
                btn.removeClass('is-active');
                btn.find('.lgl-compare-text').text('Compare');
            }
        });
    }

    /**
     * Intercepts clicks on compare buttons globally using event delegation.
     * Organizes payloads into respective type buckets.
     * @param {Event} e The jQuery click event object.
     * @return {void}
     */
    $(document).on('click', '.lgl-compare-btn', function (e) {
        e.preventDefault();

        const btn = $(this);
        const targetId = btn.attr('data-post-id');
        const targetType = btn.attr('data-post-type');
        const postTitle = btn.attr('data-title') || 'Vehicle';

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
            window.showNotification(postTitle + ' added to compare list!', 'success');
        } else {
            // Toggle removal
            data[targetType] = data[targetType].filter(id => id !== targetId);
            window.showNotification(postTitle + ' removed from compare list!', 'error');
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

/**
 * Initializes the Finance Specialist range slider module.
 * Operates in index-mode: maps the linear slider integer to a discrete array of real database prices.
 * * @return {void}
 */
const initFinanceSlider = () => {
    const slider = document.querySelector('.lgl-budget-slider');
    const output = document.querySelector('.lgl-slider-output');
    const actionButtons = document.querySelectorAll('.lgl-btn-finance');

    if (!slider || !output) return;

    // Parse the injected array of exact database prices
    let priceData = [];
    try {
        priceData = JSON.parse(slider.getAttribute('data-prices') || '[]');
    } catch (e) {
        console.error('LGL Finance Slider: Failed to parse price data.', e);
        return;
    }

    if (priceData.length === 0) return;

    /**
     * Formats a raw numeric value into a localized string with comma separators.
     */
    const formatCurrency = (value) => {
        return new Intl.NumberFormat('en-GB').format(value);
    };

    /**
     * Calculates the active percentage of the track for dynamic linear-gradient painting.
     */
    const updateSliderFill = (sliderElement) => {
        const min = parseInt(sliderElement.min, 10);
        const max = parseInt(sliderElement.max, 10);
        const val = parseInt(sliderElement.value, 10);

        const percentage = ((val - min) / (max - min)) * 100;
        sliderElement.style.setProperty('--lgl-slider-fill', `${percentage}%`);
    };

    /**
     * Appends or updates the 'price_max' query parameter on all target action buttons.
     */
    const updateActionLinks = (actualPrice) => {
        if (!actionButtons.length) return;

        actionButtons.forEach(button => {
            try {
                // Ignore hash links if a page isn't set in backend
                if (button.getAttribute('href') === '#') return;

                const url = new URL(button.href);
                url.searchParams.set('price_max', actualPrice);
                button.href = url.toString();
            } catch (error) {
                console.error('LGL Finance Slider: Invalid URL encountered.', error);
            }
        });
    };

    /**
     * Event handler for the slider 'input' event.
     */
    const handleSliderInput = (e) => {
        // e.target.value is the array index, not the actual price
        const index = parseInt(e.target.value, 10);
        const actualPrice = priceData[index];

        output.textContent = formatCurrency(actualPrice);
        updateSliderFill(e.target);
        updateActionLinks(actualPrice);
    };

    // Attach listener
    slider.addEventListener('input', handleSliderInput);

    // Bootstrap initial DOM state
    const initialIndex = parseInt(slider.value, 10);
    const initialPrice = priceData[initialIndex];

    output.textContent = formatCurrency(initialPrice);
    updateSliderFill(slider);
    updateActionLinks(initialPrice);
};

// Defer execution
document.addEventListener('DOMContentLoaded', initFinanceSlider);