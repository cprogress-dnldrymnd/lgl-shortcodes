<?php
/**
 * Template: Compare Vehicles Shortcode
 * Renders the structural interface, type-selector dropdown, and inline AJAX search for the comparison table.
 * Manages localized state natively via LocalStorage to bypass CDN/Page Cache interference.
 *
 * Supports ?compare=ID1,ID2 URL parameter to pre-load a specific pair of vehicles.
 * Auto-detects their post type and validates both belong to the same type before seeding.
 */

if (!defined('ABSPATH')) {
    exit;
}

$options = get_option('lgl_settings', array());
if (!empty($options['disable_compare'])) {
    return;
}

// ==========================================================================
// URL Parameter Pre-load: Resolve ?compare=ID1,ID2
// ==========================================================================

$preload         = null;   // Will hold { ids: [], type: '' } if valid
$preload_error   = null;   // Will hold a user-facing error string if invalid

$allowed_types = array('caravan', 'motorhome', 'campervan');

if (!empty($_GET['compare'])) {
    $raw_ids = sanitize_text_field(wp_unslash($_GET['compare']));

    // Parse comma-separated IDs and cast to integers, discarding any non-numeric entries
    $parsed_ids = array_values(
        array_filter(
            array_map('intval', explode(',', $raw_ids)),
            fn($id) => $id > 0
        )
    );

    if (count($parsed_ids) < 2) {
        $preload_error = __('At least two vehicle IDs are required for comparison.', 'lgl-shortcodes');
    } else {
        // Resolve the post type for each ID
        $resolved_types = array();

        foreach ($parsed_ids as $pid) {
            $post = get_post($pid);

            if (!$post || $post->post_status !== 'publish' || !in_array($post->post_type, $allowed_types, true)) {
                $preload_error = sprintf(
                    /* translators: %d: post ID */
                    __('Vehicle #%d could not be found or is not a supported vehicle type.', 'lgl-shortcodes'),
                    $pid
                );
                break;
            }

            $resolved_types[$pid] = $post->post_type;
        }

        if (!$preload_error) {
            $unique_types = array_unique(array_values($resolved_types));

            if (count($unique_types) > 1) {
                // Build human-readable type labels for the error message
                $type_labels = array(
                    'caravan'    => __('Caravan', 'lgl-shortcodes'),
                    'motorhome'  => __('Motorhome', 'lgl-shortcodes'),
                    'campervan'  => __('Campervan', 'lgl-shortcodes'),
                );

                $found_labels = array_map(
                    fn($t) => $type_labels[$t] ?? $t,
                    $unique_types
                );

                $preload_error = sprintf(
                    /* translators: %s: comma-separated list of vehicle types */
                    __('Vehicles must be the same type to compare. Found: %s.', 'lgl-shortcodes'),
                    implode(', ', $found_labels)
                );
            } else {
                // All IDs are valid and share the same post type — prepare the preload payload
                $preload = array(
                    'ids'  => $parsed_ids,
                    'type' => $unique_types[0],
                );
            }
        }
    }
}
?>

<div class="lgl-compare-container">

    <?php if ($preload_error): ?>
        <div class="lgl-notice lgl-notice--error" style="margin-bottom: 20px; padding: 12px 16px; background: #fef2f2; border: 1px solid var(--lgl-color-error, #dc3545); border-radius: 8px; color: var(--lgl-color-error, #dc3545); font-weight: 500;">
            <?php echo esc_html($preload_error); ?>
        </div>
    <?php endif; ?>

    <div class="lgl-compare-controls" style="margin-bottom: 20px; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 15px; background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0;">
        <div style="display: flex; align-items: center; gap: 10px;">
            <label for="lgl-compare-type-selector" style="font-weight: 600; color: var(--lgl-color-secondary, #333);"><?php esc_html_e('Category:', 'lgl-shortcodes'); ?></label>
            <select id="lgl-compare-type-selector" style="padding: 10px 15px; border-radius: 6px; border: 1px solid #ccd0d4; font-family: var(--lgl-font-primary, sans-serif); min-width: 180px;">
                <?php foreach ($allowed_types as $type):
                    $label_map = array(
                        'caravan'   => __('Caravans', 'lgl-shortcodes'),
                        'motorhome' => __('Motorhomes', 'lgl-shortcodes'),
                        'campervan' => __('Campervans', 'lgl-shortcodes'),
                    );
                    $selected = ($preload && $preload['type'] === $type) ? 'selected' : '';
                ?>
                    <option value="<?php echo esc_attr($type); ?>" <?php echo $selected; ?>>
                        <?php echo esc_html($label_map[$type]); ?>
                    </option>
                <?php endforeach; ?>
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
    const targetNode     = document.getElementById('lgl-compare-render-target');
    const typeSelector   = document.getElementById('lgl-compare-type-selector');
    const searchSelector = $('#lgl-compare-search-input');

    // -------------------------------------------------------------------------
    // URL Preload: seed localStorage from ?compare= if PHP resolved a valid pair
    // -------------------------------------------------------------------------
    <?php if ($preload): ?>
    (function () {
        const preload = <?php echo wp_json_encode($preload); ?>;

        let allData = JSON.parse(localStorage.getItem(STATE_KEY_DATA)) || {};

        // Replace the bucket for this post type with exactly the IDs from the URL param,
        // discarding any previously stored vehicles for this type.
        // Other post type buckets (caravan / motorhome / campervan) are left untouched.
        allData[preload.type] = preload.ids.map(id => id.toString());

        localStorage.setItem(STATE_KEY_DATA, JSON.stringify(allData));

        // Sync the dropdown to reflect the resolved post type
        if (typeSelector) {
            typeSelector.value = preload.type;
        }
    })();
    <?php endif; ?>

    // -------------------------------------------------------------------------
    // Core: fetch and render the comparison table via AJAX
    // -------------------------------------------------------------------------
    function renderCompareTable() {
        if (!targetNode || !typeSelector) return;

        const activeType = typeSelector.value;
        const allData    = JSON.parse(localStorage.getItem(STATE_KEY_DATA)) || {};
        const postIds    = allData[activeType] || [];

        if (postIds.length === 0) {
            targetNode.innerHTML = '<p class="lgl-compare-empty"><?php echo esc_js(__('No vehicles currently staged for comparison in this category.', 'lgl-shortcodes')); ?></p>';
            return;
        }

        const formData = new FormData();
        formData.append('action',    'lgl_get_compare_table');
        formData.append('nonce',     lgl_ajax_obj.nonce);
        formData.append('post_type', activeType);
        formData.append('post_ids',  JSON.stringify(postIds));
        formData.append('_cb',       Date.now()); // Cache buster

        targetNode.style.opacity = '0.5';

        fetch(lgl_ajax_obj.ajax_url, {
            method: 'POST',
            body:   formData,
            cache:  'no-store'
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

    // -------------------------------------------------------------------------
    // Select2: inline vehicle search & add
    // -------------------------------------------------------------------------
    if (searchSelector.length) {
        searchSelector.select2({
            placeholder: '<?php echo esc_js(__('Search & Add Vehicle...', 'lgl-shortcodes')); ?>',
            allowClear:  true,
            ajax: {
                url:      lgl_ajax_obj.ajax_url,
                type:     'POST',
                dataType: 'json',
                delay:    250,
                data: function (params) {
                    return {
                        action:    'lgl_search_vehicles_for_compare',
                        nonce:     lgl_ajax_obj.nonce,
                        q:         params.term,
                        post_type: typeSelector.value
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
            const activeType   = typeSelector.value;
            const targetId     = selectedData.id.toString();

            let allData = JSON.parse(localStorage.getItem(STATE_KEY_DATA)) || {};
            if (!allData[activeType]) allData[activeType] = [];

            if (!allData[activeType].includes(targetId)) {
                if (allData[activeType].length >= 4) {
                    alert('<?php echo esc_js(__('Comparison capacity reached for this category. Remove a vehicle first.', 'lgl-shortcodes')); ?>');
                } else {
                    allData[activeType].push(targetId);
                    localStorage.setItem(STATE_KEY_DATA, JSON.stringify(allData));
                    renderCompareTable();
                    document.dispatchEvent(new Event('lgl_compare_updated'));
                }
            } else {
                alert('<?php echo esc_js(__('Vehicle is already active in the comparison list.', 'lgl-shortcodes')); ?>');
            }

            // Flush the input field to allow subsequent searches
            searchSelector.val(null).trigger('change');
        });
    }

    // -------------------------------------------------------------------------
    // Category dropdown: re-render on change
    // -------------------------------------------------------------------------
    if (typeSelector) {
        typeSelector.addEventListener('change', function () {
            if (searchSelector.length) {
                searchSelector.val(null).trigger('change');
            }
            renderCompareTable();
        });
    }

    // -------------------------------------------------------------------------
    // Remove button: delegated click handler within the rendered table
    // -------------------------------------------------------------------------
    if (targetNode) {
        targetNode.addEventListener('click', function (e) {
            const removeBtn = e.target.closest('.lgl-compare-remove-btn');
            if (!removeBtn) return;

            const idToRemove   = removeBtn.getAttribute('data-post-id');
            const typeToRemove = removeBtn.getAttribute('data-post-type');

            let allData = JSON.parse(localStorage.getItem(STATE_KEY_DATA)) || {};

            if (allData[typeToRemove]) {
                allData[typeToRemove] = allData[typeToRemove].filter(id => id !== idToRemove);
                localStorage.setItem(STATE_KEY_DATA, JSON.stringify(allData));
            }

            renderCompareTable();
            document.dispatchEvent(new Event('lgl_compare_updated'));
        });

        // Bootstrap initial render
        renderCompareTable();
    }
});
</script>