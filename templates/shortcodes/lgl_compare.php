<?php
/**
 * Template: Compare Vehicles Shortcode
 * Renders the structural interface, type-selector dropdown, and inline AJAX search for the comparison table.
 * Manages localized state natively via LocalStorage to bypass CDN/Page Cache interference.
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="lgl-compare-container">
    
    <div class="lgl-compare-controls" style="margin-bottom: 20px; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 15px; background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0;">
        <div style="display: flex; align-items: center; gap: 10px;">
            <label for="lgl-compare-type-selector" style="font-weight: 600; color: var(--lgl-color-secondary, #333);">Category:</label>
            <select id="lgl-compare-type-selector" style="padding: 10px 15px; border-radius: 6px; border: 1px solid #ccd0d4; font-family: var(--lgl-font-primary, sans-serif); min-width: 180px;">
                <option value="caravan">Caravans</option>
                <option value="motorhome">Motorhomes</option>
                <option value="campervan">Campervans</option>
            </select>
        </div>
        
        <div style="display: flex; align-items: center; flex-grow: 1; max-width: 400px;">
            <select id="lgl-compare-search-input" style="width: 100%;">
                <option value=""><?php esc_html_e('Search & Add Vehicle...', 'lgl-shortcodes'); ?></option>
            </select>
        </div>
    </div>

    <div id="lgl-compare-render-target">
        <p class="lgl-compare-empty"><?php esc_html_e('No vehicles currently staged for comparison in this category.', 'lgl-shortcodes'); ?></p>
    </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    const STATE_KEY_DATA = 'lgl_compare_data';
    const targetNode = document.getElementById('lgl-compare-render-target');
    const typeSelector = document.getElementById('lgl-compare-type-selector');
    const searchSelector = $('#lgl-compare-search-input');

    /**
     * Extracts multidimensional array of Post IDs from local storage based on the 
     * active dropdown selection and executes the AJAX fetch request.
     */
    function renderCompareTable() {
        if (!targetNode || !typeSelector) return;

        const activeType = typeSelector.value;
        const allData = JSON.parse(localStorage.getItem(STATE_KEY_DATA)) || {};
        const postIds = allData[activeType] || [];
        
        if (postIds.length === 0) {
            targetNode.innerHTML = '<p class="lgl-compare-empty">No vehicles currently staged for comparison in this category.</p>';
            return;
        }

        const formData = new FormData();
        formData.append('action', 'lgl_get_compare_table');
        formData.append('nonce', lgl_ajax_obj.nonce);
        formData.append('post_type', activeType);
        formData.append('post_ids', JSON.stringify(postIds));
        formData.append('_cb', new Date().getTime()); // Cache buster

        targetNode.style.opacity = '0.5';

        fetch(lgl_ajax_obj.ajax_url, {
            method: 'POST',
            body: formData,
            cache: 'no-store'
        })
        .then(response => response.json())
        .then(payload => {
            targetNode.style.opacity = '1';
            if (payload.success) {
                targetNode.innerHTML = payload.data.html;
            } else {
                targetNode.innerHTML = '<p class="lgl-compare-error">' + payload.data + '</p>';
            }
        })
        .catch(err => {
            targetNode.style.opacity = '1';
            console.error('LGL Compare Network Error:', err);
        });
    }

    // Initialize Select2 AJAX bindings for inline additions
    if (searchSelector.length) {
        searchSelector.select2({
            placeholder: 'Search & Add Vehicle...',
            allowClear: true,
            ajax: {
                url: lgl_ajax_obj.ajax_url,
                type: 'POST',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        action: 'lgl_search_vehicles_for_compare',
                        nonce: lgl_ajax_obj.nonce,
                        q: params.term,
                        post_type: typeSelector.value // Dynamically binds to the active category
                    };
                },
                processResults: function (data) {
                    return {
                        results: data.success ? data.data : []
                    };
                },
                cache: true
            }
        });

        // Event interceptor when a vehicle is selected from the dropdown
        searchSelector.on('select2:select', function (e) {
            const selectedData = e.params.data;
            const activeType = typeSelector.value;
            const targetId = selectedData.id.toString();

            let allData = JSON.parse(localStorage.getItem(STATE_KEY_DATA)) || {};
            if (!allData[activeType]) allData[activeType] = [];

            if (!allData[activeType].includes(targetId)) {
                if (allData[activeType].length >= 4) {
                    alert('Comparison capacity reached for this category. Remove a vehicle first.');
                } else {
                    allData[activeType].push(targetId);
                    localStorage.setItem(STATE_KEY_DATA, JSON.stringify(allData));
                    
                    renderCompareTable();
                    // Dispatch global event to sync `.lgl-compare-btn` buttons if grid exists on the same page
                    document.dispatchEvent(new Event('lgl_compare_updated'));
                }
            } else {
                alert('Vehicle is already active in the comparison list.');
            }

            // Flush the input field to allow subsequent searches
            searchSelector.val(null).trigger('change');
        });
    }

    // Bind category change listener to flush UI states and re-render
    if (typeSelector) {
        typeSelector.addEventListener('change', function() {
            if (searchSelector.length) {
                searchSelector.val(null).trigger('change');
            }
            renderCompareTable();
        });
    }

    /**
     * Handle item deletion explicitly from within the rendered comparison table.
     */
    if (targetNode) {
        targetNode.addEventListener('click', function(e) {
            const removeBtn = e.target.closest('.lgl-compare-remove-btn');
            if (!removeBtn) return;

            const idToRemove = removeBtn.getAttribute('data-post-id');
            const typeToRemove = removeBtn.getAttribute('data-post-type');
            
            let allData = JSON.parse(localStorage.getItem(STATE_KEY_DATA)) || {};
            
            if (allData[typeToRemove]) {
                allData[typeToRemove] = allData[typeToRemove].filter(id => id !== idToRemove);
                localStorage.setItem(STATE_KEY_DATA, JSON.stringify(allData));
            }
            
            renderCompareTable();
            document.dispatchEvent(new Event('lgl_compare_updated'));
        });

        // Bootstrap initial payload request
        renderCompareTable();
    }
});
</script>