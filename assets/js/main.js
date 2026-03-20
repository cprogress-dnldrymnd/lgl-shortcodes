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
        initBreadcrumbs();
    });

    /**
      * Retrieves the last search URL from session storage and updates 
      * the "Back to Results" and Breadcrumb archive links.
      * Strictly verifies the user's origin path to prevent stale session injection.
      * Reveals the Back button ONLY if the origin matches the archive page (and is not the homepage).
      */
    function initBreadcrumbs() {
        const lastUrl = sessionStorage.getItem('lgl_last_search_url');
        const $backWrapper = $('.lgl-back-to-results-wrapper');
        const $backBtn = $('.lgl-back-to-results');

        if (lastUrl && $backBtn.length) {
            if (document.referrer) {
                try {
                    const refObj = new URL(document.referrer);
                    const lastObj = new URL(lastUrl);

                    // Check if paths match AND ensure the path is not the root homepage
                    if (refObj.pathname === lastObj.pathname && refObj.pathname !== '/' && refObj.pathname !== '') {
                        $backBtn.attr('href', lastUrl);
                        $('.lgl-br-archive').attr('href', lastUrl);

                        // Origin validated: Reveal the Back to Results button
                        $backWrapper.show();
                        return; // Stop execution here
                    }
                } catch (e) {
                    // Fail silently on invalid URLs
                }
            }

            // If the script reaches here, the user came from the Homepage, an email, or direct link.
            // The wrapper remains hidden (display: none), and we purge the stale memory.
            sessionStorage.removeItem('lgl_last_search_url');
        }
    }

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
        let activeSearchXhr = null;

        // Initialize Select2 on target classes
        $('.lgl-select2').select2({
            width: '100%'
        });

        $('#lgl-search-form.lgl-filter-form-no-ajax').on('submit', function (e) {
            e.preventDefault();

            // The option VALUE is the full archive page URL set in LGL Settings → LGL Pages
            // ... inside the global search submit handler ...
            const destUrl = $('#lgl_post_type').val(); // This is the base URL

            if (!destUrl) {
                // No vehicle type chosen — shake the dropdown
                $('#lgl_post_type').closest('.lgl-filter-group').addClass('lgl-field-error');
                setTimeout(function () {
                    $('#lgl_post_type').closest('.lgl-filter-group').removeClass('lgl-field-error');
                }, 1200);
                return;
            }

            const makeVal = $('#lgl_make').val() || '';
            const modelVal = $('#lgl_model').val() || '';

            let redirectUrl = destUrl;

            // Ensure trailing slash on base URL
            if (!redirectUrl.endsWith('/')) redirectUrl += '/';

            // Append Make and Model as path segments
            if (makeVal) {
                redirectUrl += encodeURIComponent(makeVal) + '/';
                if (modelVal) {
                    redirectUrl += encodeURIComponent(modelVal) + '/';
                }
            }

            window.location.href = redirectUrl;
        });

        // Dependent Dropdown Logic (Make -> Model) for global search or initial load
        $('#lgl_make').on('change', function () {
            if (isUpdatingFilters) return; // Prevent conflicts with update_filter_options

            let make_id = $(this).val();
            let $model_select = $('#lgl_model');
            let postType = $('#lgl_target_post_type').val() || $('#lgl_post_type').find('option:selected').data('post-type') || '';

            // Soft reset model dropdown
            $model_select.empty().append('<option value="">Select Model</option>').prop('disabled', true).trigger('change.select2');

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
                            $model_select.prop('disabled', false).trigger('change.select2');
                        }
                    }
                });
            }
        });

        // Vehicle Type change → load Makes for the selected type (global search form only)
        $('#lgl_post_type').on('change', function () {
            const $selected = $(this).find('option:selected');
            const postTypeSlug = $selected.data('post-type');
            const $makeSelect = $('#lgl_make');
            const $modelSelect = $('#lgl_model');

            $makeSelect.empty().append('<option value="">Select Vehicle Type First</option>').prop('disabled', true).trigger('change.select2');
            $modelSelect.empty().append('<option value="">Select Make First</option>').prop('disabled', true).trigger('change.select2');

            if (!postTypeSlug) return;

            $makeSelect.empty().append('<option value="">Loading makes…</option>').trigger('change.select2');

            $.ajax({
                url: lgl_ajax_obj.ajax_url,
                type: 'POST',
                data: {
                    action: 'lgl_get_makes',
                    nonce: lgl_ajax_obj.nonce,
                    post_type: postTypeSlug,
                },
                success: function (response) {
                    $makeSelect.empty().append('<option value="">All Makes</option>');
                    if (response.success && response.data.length > 0) {
                        $.each(response.data, function (i, item) {
                            $makeSelect.append(new Option(item.text, item.id, false, false));
                        });
                        $makeSelect.prop('disabled', false).trigger('change.select2');
                    } else {
                        $makeSelect.empty().append('<option value="">No makes available</option>').trigger('change.select2');
                    }
                },
                error: function () {
                    $makeSelect.empty().append('<option value="">Error loading makes</option>').trigger('change.select2');
                },
            });
        });

        // Single handler — captures form data BEFORE execute_search disables the inputs!
        $('#lgl-search-form.lgl-filter-form-ajax, #lgl-sort-order').on('submit change', function (e) {
            if (e.type === 'submit') e.preventDefault();
            if (isUpdatingFilters) return;

            currentPage = 1;

            // Capture the serialized data immediately before the fields are disabled
            const currentFormData = $('#lgl-search-form').serialize();

            execute_search(currentFormData, false);
            update_filter_options(currentFormData);

            // Inject visibility evaluation here
            evaluateResetButtonVisibility();
        });
        // Intercept standard WordPress pagination clicks for AJAX handling
        $(document).on('click', '.lgl-pagination-wrap a.page-numbers', function (e) {
            e.preventDefault();

            let href = $(this).attr('href');
            let match = href.match(/paged=(\d+)/);

            if (match) {
                currentPage = parseInt(match[1], 10);
            } else {
                currentPage = 1;
            }

            execute_search(); // Falls back to standard serialization

            $('html, body').animate({
                scrollTop: $('.lgl-results-wrapper').offset().top - 40
            }, 400);
        });

        // Intercept Breadcrumb Clicks on Archive Pages for Seamless AJAX Routing
        $(document).on('click', '.lgl-ajax-breadcrumb', function (e) {
            // Abort and allow standard navigation if the search form isn't present (e.g., Single Pages)
            if ($('#lgl-search-form.lgl-filter-form-ajax').length === 0) return;

            e.preventDefault();

            let $clicked = $(this);
            let action = $clicked.data('action');
            let text = $clicked.text();

            // 1. Manipulate the Select2 nodes based on the breadcrumb depth
            if (action === 'clear-all') {
                $('#lgl_make').val('').trigger('change.select2');
                $('#lgl_model').val('').trigger('change.select2');
            } else if (action === 'clear-model') {
                $('#lgl_model').val('').trigger('change.select2');
            }

            // 2. Trigger the AJAX execution
            $('#lgl-search-form').trigger('submit');

            // 3. Mutate the DOM to reflect the new state instantly
            $clicked.nextAll().remove(); // Strip deeper levels and separators
            $clicked.replaceWith('<span class="lgl-current-page">' + text + '</span>'); // Convert link to active plain text node
        });

        /**
         * Intercepts the Reset Filters button click.
         * Forcibly nullifies all Select2 instances within the target form to clear the UI,
         * then delegates the actual reset operation to the primary AJAX submission pipeline.
         */
        $(document).on('click', '.lgl-reset-filters-btn', function (e) {
            e.preventDefault();

            const $form = $(this).closest('form');

            // 1. Flush the visual and internal state of all Select2 nodes
            $form.find('select.lgl-select2').val('').trigger('change.select2');

            // 2. Dispatch the submit event to trigger grid refresh and URL cleanup
            if ($form.hasClass('lgl-filter-form-ajax')) {
                $form.trigger('submit');
            }
        });


        /**
         * Evaluates all core Select2 filter fields to determine if any active filters exist.
         * Toggles the visibility of the Reset Filters button accordingly.
         * Excludes the post_type selector as it dictates the archive context, not the filter state.
         *
         * @return void
         */
        function evaluateResetButtonVisibility() {
            let hasActiveFilters = false;

            // Iterate over all active filter fields
            $('#lgl_make, #lgl_model, #lgl_condition, #lgl_berth, #lgl_price_min, #lgl_price_max').each(function () {
                if ($(this).val()) {
                    hasActiveFilters = true;
                    return false; // Break the $.each loop early for performance
                }
            });

            if (hasActiveFilters) {
                $('.lgl-reset-filters-btn').show();
            } else {
                $('.lgl-reset-filters-btn').hide();
            }
        }


        /**
         * Fetches valid filter options for the current filter state and repopulates
         * the dropdowns so impossible combinations are completely hidden.
         * * @param {string} providedFormData Pre-captured string to bypass disabled state limits
         */
        function update_filter_options(providedFormData) {
            const postType = $('#lgl_target_post_type').val();
            if (!postType) return; // Global search form bypass

            const formData = (typeof providedFormData === 'string') ? providedFormData : $('#lgl-search-form').serialize();

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

                    // Standard text value dropdowns
                    _repopulate_select('#lgl_condition', d.conditions, 'Any Condition');
                    _repopulate_select('#lgl_berth', d.berths, 'Any Berth');

                    // Complex object dropdowns
                    _repopulate_price_select('#lgl_price_min', d.prices, 'Min Price');
                    _repopulate_price_select('#lgl_price_max', d.prices, 'Max Price');

                    // Add Makes and Models to dynamic repopulation
                    _repopulate_object_select('#lgl_make', d.makes, 'All Makes');

                    if ($('#lgl_make').val()) {
                        _repopulate_object_select('#lgl_model', d.models, 'All Models');
                        $('#lgl_model').prop('disabled', false).trigger('change.select2');
                    } else {
                        $('#lgl_model').empty().append(new Option('Select Make First', '')).prop('disabled', true).trigger('change.select2');
                    }

                    isUpdatingFilters = false;
                }
            });
        }

        /**
         * Rebuilds a plain-value select (condition, berth) with only the provided values.
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

            if (current && !stillValid) $el.val('');
            $el.trigger('change.select2');
        }

        /**
         * Rebuilds a price select with {value, label} objects.
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

            if (current && !stillValid) $el.val('');
            $el.trigger('change.select2');
        }

        /**
         * Rebuilds a select with {id, text} object arrays (specifically Make & Model taxonomies).
         */
        function _repopulate_object_select(selector, items, placeholder) {
            const $el = $(selector);
            const current = String($el.val() || '');

            $el.empty().append(new Option(placeholder, ''));

            let stillValid = false;
            if (items && items.length > 0) {
                $.each(items, function (i, item) {
                    if (String(item.id) === current) stillValid = true;
                    $el.append(new Option(item.text, item.id, false, String(item.id) === current));
                });
            }

            if (current && !stillValid) $el.val('');
            $el.trigger('change.select2');
        }

        /**
         * Enables or disables all filter selects in the search form during a loading state.
         */
        function _set_filters_disabled(disabled) {
            const $selects = $('#lgl-search-form select.lgl-select2');

            if (disabled) {
                $selects.each(function () {
                    if ($(this).prop('disabled')) {
                        $(this).data('lgl-was-disabled', true);
                    }
                    $(this).prop('disabled', true).trigger('change.select2');
                });
            } else {
                $selects.each(function () {
                    if (!$(this).data('lgl-was-disabled')) {
                        $(this).prop('disabled', false).trigger('change.select2');
                    }
                    $(this).removeData('lgl-was-disabled');
                });
            }
        }

        /**
         * Compiles form parameters and dispatches the AJAX search payload.
         * * @param {string} providedFormData Pre-captured string to bypass disabled state limits
         */
        function execute_search(providedFormData, isInitialLoad = false) {
            if (activeSearchXhr) {
                activeSearchXhr.abort();
                activeSearchXhr = null;
            }

            let formDataStr = (typeof providedFormData === 'string') ? providedFormData : $('#lgl-search-form').serialize();
            let formData = formDataStr + '&sort_order=' + ($('#lgl-sort-order').val() || '');
            let postType = $('#lgl_target_post_type').val();
            let limit = parseInt($('#lgl-results-grid').data('limit'), 10) || 9;

            // Disable all filter fields while loading
            _set_filters_disabled(true);

            $('#lgl-loader').show();
            $('#lgl-results-grid').css('opacity', '0.5');
            $('.lgl-pagination-wrap').css('opacity', '0.5');

            // --- ADD THIS BLOCK: Live URL Update & State Persistence ---
            if (window.history.replaceState && !isInitialLoad) {
                const urlParams = new URLSearchParams(formDataStr);

                // 1. Extract make and model for the path
                const makeSlug = urlParams.get('listing_make');
                const modelSlug = urlParams.get('listing_model');

                // 2. Clean up query parameters (remove make/model as they are now in the path)
                const keysForDel = [];
                urlParams.forEach((value, key) => {
                    if (!value || key === 'post_type' || key === 'action' || key === 'nonce' || key === 'listing_make' || key === 'listing_model') {
                        keysForDel.push(key);
                    }
                });
                keysForDel.forEach(key => urlParams.delete(key));

                // 3. Add pagination and sorting
                const sortVal = $('#lgl-sort-order').val();
                if (sortVal) urlParams.set('sort_order', sortVal);
                if (currentPage > 1) urlParams.set('paged', currentPage);

                // 4. Construct the new path
                let baseUrl = $('#lgl_base_archive_url').val();

                if (baseUrl) {
                    if (!baseUrl.endsWith('/')) baseUrl += '/';

                    let newPath = baseUrl;
                    if (makeSlug) {
                        newPath += encodeURIComponent(makeSlug) + '/';
                        if (modelSlug) {
                            newPath += encodeURIComponent(modelSlug) + '/';
                        }
                    }

                    const newQueryString = urlParams.toString();
                    const newUrl = newPath + (newQueryString ? '?' + newQueryString : '');

                    window.history.replaceState(null, '', newUrl);

                    // Persist the exact filtered URL for the single page "Back" button
                    sessionStorage.setItem('lgl_last_search_url', window.location.href);

                    /**
                     * Dynamic Breadcrumb Sync
                     * Reconstructs the breadcrumb DOM to reflect the newly pushed URL state.
                     * Extracts active taxonomy labels directly from the Select2 option text.
                     */
                    if ($('.lgl-breadcrumbs').length > 0) {
                        const homeUrl = $('.lgl-breadcrumbs a').first().attr('href') || '/';

                        // Extract base archive label from existing DOM to preserve taxonomy context (e.g., 'Motorhomes')
                        const archiveLabel = $('.lgl-br-archive').text() || $('.lgl-current-page').first().text();

                        if (archiveLabel) {
                            let breadcrumbHtml = '<a href="' + homeUrl + '">Home</a> <span class="lgl-separator">|</span> ';

                            if (makeSlug) {
                                // Extract the human-readable text from the dropdown
                                const makeText = $('#lgl_make option[value="' + makeSlug + '"]').text().trim() || makeSlug;

                                // Archive node becomes a clickable reset link
                                breadcrumbHtml += '<a href="' + baseUrl + '" class="lgl-br-archive lgl-ajax-breadcrumb" data-action="clear-all">' + archiveLabel + '</a> <span class="lgl-separator">|</span> ';

                                if (modelSlug) {
                                    const modelText = $('#lgl_model option[value="' + modelSlug + '"]').text().trim() || modelSlug;

                                    // Make node becomes a clickable reset link for the model layer
                                    breadcrumbHtml += '<a href="' + baseUrl + makeSlug + '/" class="lgl-ajax-breadcrumb" data-action="clear-model">' + makeText + '</a> <span class="lgl-separator">|</span> ';

                                    // Model node becomes the terminal active span
                                    breadcrumbHtml += '<span class="lgl-current-page">' + modelText + '</span>';
                                } else {
                                    // Make node becomes the terminal active span
                                    breadcrumbHtml += '<span class="lgl-current-page">' + makeText + '</span>';
                                }
                            } else {
                                // Archive node becomes the terminal active span
                                breadcrumbHtml += '<span class="lgl-current-page lgl-br-archive">' + archiveLabel + '</span>';
                            }

                            // Inject the newly calculated trail into the DOM
                            $('.lgl-breadcrumbs').html(breadcrumbHtml);
                        }
                    }
                }
            }

            activeSearchXhr = $.ajax({
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

                        let visibleCount = $('#lgl-results-grid .lgl-post').length;
                        $('#lgl-results-count').html('Showing ' + visibleCount + ' of ' + response.data.count + ' results');
                    } else {
                        alert('Error fetching results.');
                    }
                },
                error: function (xhr) {
                    if (xhr.statusText !== 'abort') {
                        alert('A server error occurred. Please try again.');
                    }
                },
                complete: function () {
                    activeSearchXhr = null;
                    $('#lgl-loader').hide();
                    $('#lgl-results-grid').css('opacity', '1');
                    $('.lgl-pagination-wrap').css('opacity', '1');
                    _set_filters_disabled(false);
                }
            });
        }

        // Trigger initial search to populate grid on load
        if ($('#lgl-search-form').length) {
            const initialData = $('#lgl-search-form').serialize();
            execute_search(initialData, true); // Pass true here
            update_filter_options(initialData);
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