<?php
/**
 * Template: Compare Vehicles Shortcode
 * Renders the structural interface for the vehicle comparison table and manages localized 
 * state manipulation natively via LocalStorage to bypass CDN/Page Cache interference.
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="lgl-compare-container">
    <div id="lgl-compare-render-target">
        <p class="lgl-compare-empty"><?php esc_html_e('No vehicles currently staged for comparison. Assign vehicles directly from the grid view.', 'lgl-shortcodes'); ?></p>
    </div>
</div>

<script type="text/javascript">
document.addEventListener('DOMContentLoaded', function() {
    const STATE_KEY_IDS = 'lgl_compare_post_ids';
    const STATE_KEY_TYPE = 'lgl_compare_post_type';
    const targetNode = document.getElementById('lgl-compare-render-target');

    /**
     * Extracts array of Post IDs from local storage and executes the AJAX 
     * fetch request to construct the server-side generated HTML table.
     */
    function renderCompareTable() {
        let postIds = JSON.parse(localStorage.getItem(STATE_KEY_IDS)) || [];
        
        if (postIds.length === 0) {
            if(targetNode) targetNode.innerHTML = '<p class="lgl-compare-empty">No vehicles currently staged for comparison.</p>';
            localStorage.removeItem(STATE_KEY_TYPE); // Clean orphaned type states
            return;
        }

        const formData = new FormData();
        formData.append('action', 'lgl_get_compare_table');
        formData.append('nonce', lgl_ajax_obj.nonce);
        formData.append('post_ids', JSON.stringify(postIds));

        fetch(lgl_ajax_obj.ajax_url, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(payload => {
            if (payload.success && targetNode) {
                targetNode.innerHTML = payload.data.html;
            } else if (!payload.success) {
                console.warn('LGL Compare Warning:', payload.data);
                if(targetNode) targetNode.innerHTML = '<p class="lgl-compare-error">' + payload.data + '</p>';
            }
        })
        .catch(err => console.error('LGL Compare Network Error:', err));
    }

    /**
     * Intercept clicks globally on `.lgl-compare-btn` elements.
     * Evaluates current active post type, enforces parity, limits max count, and caches selection.
     */
    document.body.addEventListener('click', function(e) {
        const btn = e.target.closest('.lgl-compare-btn');
        if (!btn) return;
        
        e.preventDefault();
        
        const targetId = btn.getAttribute('data-post-id');
        const targetType = btn.getAttribute('data-post-type');
        
        if (!targetId || !targetType) return;

        let activeType = localStorage.getItem(STATE_KEY_TYPE);
        let activeIds = JSON.parse(localStorage.getItem(STATE_KEY_IDS)) || [];

        // Validate Post Type Parity (Client Side Gate)
        if (activeType && activeType !== targetType && activeIds.length > 0) {
            alert('Comparison conflict: You are already comparing a different vehicle classification. Clear your list to start a new comparison block.');
            return;
        }

        // Lock type state on first entry
        localStorage.setItem(STATE_KEY_TYPE, targetType);

        if (!activeIds.includes(targetId)) {
            // Cap at 4 items to prevent table bleed on smaller viewports
            if (activeIds.length >= 4) {
                alert('Comparison capacity reached. Please remove an existing vehicle to add a new one.');
                return;
            }
            activeIds.push(targetId);
            localStorage.setItem(STATE_KEY_IDS, JSON.stringify(activeIds));
            
            // Visual feedback - swap button text/state if desired
            btn.textContent = 'Added to Compare';
            btn.classList.add('is-active');
            
        } else {
            alert('This vehicle is already staged in your comparison list.');
        }

        // Force a re-render if the user is clicking while physically on the comparison page
        if (targetNode) {
            renderCompareTable();
        }
    });

    /**
     * Handle item deletion explicitly from within the rendered comparison table.
     */
    if (targetNode) {
        targetNode.addEventListener('click', function(e) {
            const removeBtn = e.target.closest('.lgl-compare-remove-btn');
            if (!removeBtn) return;

            const idToRemove = removeBtn.getAttribute('data-post-id');
            let activeIds = JSON.parse(localStorage.getItem(STATE_KEY_IDS)) || [];
            
            activeIds = activeIds.filter(id => id !== idToRemove);
            localStorage.setItem(STATE_KEY_IDS, JSON.stringify(activeIds));
            
            renderCompareTable();
        });

        // Trigger payload request on initial Document Ready
        renderCompareTable();
    }
});
</script>