<?php
/**
 * Template: Compare Vehicles Shortcode
 * Renders the structural interface and type-selector dropdown for the vehicle comparison table.
 * Manages localized state natively via LocalStorage to bypass CDN/Page Cache interference.
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="lgl-compare-container">
    
    <div class="lgl-compare-controls" style="margin-bottom: 20px; display: flex; justify-content: flex-end; align-items: center; gap: 15px;">
        <label for="lgl-compare-type-selector" style="font-weight: 600; color: var(--lgl-color-secondary, #333);">Select Category:</label>
        <select id="lgl-compare-type-selector" style="padding: 10px 15px; border-radius: 6px; border: 1px solid #ccd0d4; font-family: var(--lgl-font-primary, sans-serif); min-width: 200px;">
            <option value="caravan">Caravans</option>
            <option value="motorhome">Motorhomes</option>
            <option value="campervan">Campervans</option>
        </select>
    </div>

    <div id="lgl-compare-render-target">
        <p class="lgl-compare-empty"><?php esc_html_e('No vehicles currently staged for comparison in this category.', 'lgl-shortcodes'); ?></p>
    </div>
</div>

<script type="text/javascript">
document.addEventListener('DOMContentLoaded', function() {
    const STATE_KEY_DATA = 'lgl_compare_data';
    const targetNode = document.getElementById('lgl-compare-render-target');
    const typeSelector = document.getElementById('lgl-compare-type-selector');

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

        // Visual loading state
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

    // Bind change listener to the dropdown
    if (typeSelector) {
        typeSelector.addEventListener('change', renderCompareTable);
    }

    /**
     * Handle item deletion explicitly from within the rendered comparison table.
     * Extracts the specific vehicle type assigned to the remove button.
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
            
            // Re-render and trigger global DOM event so grids update in real-time
            renderCompareTable();
            document.dispatchEvent(new Event('lgl_compare_updated'));
        });

        // Trigger payload request on initial Document Ready
        renderCompareTable();
    }
});
</script>