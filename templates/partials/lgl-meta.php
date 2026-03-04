<?php
if (class_exists('LGL_Import_Post_Types')) {

    $listing_fields = LGL_Import_Post_Types::get_listing_detail_fields();
    $options        = get_option('lgl_settings', array());
    $saved_order    = isset($options['field_order']) && is_array($options['field_order']) ? $options['field_order'] : array();

    // 1. Determine valid meta fields and taxonomies strictly based on the current post type
    $valid_meta_fields = isset($listing_fields['common']) ? $listing_fields['common'] : array();
    $valid_taxonomies  = array();

    if ($post_type !== 'caravan') {
        if (isset($listing_fields['motorhome_campervan'])) {
            $valid_meta_fields = array_merge($valid_meta_fields, $listing_fields['motorhome_campervan']);
        }
        $valid_taxonomies[] = 'listing-fuel-type';
        $valid_taxonomies[] = 'listing-gearbox';

        if ($post_type === 'motorhome') {
            $valid_taxonomies[] = 'listing-chassis';
        }
    } else {
        if (isset($listing_fields['caravan'])) {
            $valid_meta_fields = array_merge($valid_meta_fields, $listing_fields['caravan']);
        }
    }

    // 2. Build a complete array of all keys valid for this exact post
    $all_valid_keys = array_merge(array_keys($valid_meta_fields), $valid_taxonomies);

    // 3. Establish the final ordered list: Prioritize the saved DB array, then append new/unsaved keys
    $final_order = array();
    foreach ($saved_order as $key) {
        if (in_array($key, $all_valid_keys)) {
            $final_order[] = $key;
        }
    }
    foreach ($all_valid_keys as $key) {
        if (!in_array($key, $final_order)) {
            $final_order[] = $key;
        }
    }

    /**
     * Reusable closure to execute the DOM rendering logic for both meta and taxonomies.
     * Prevents code duplication while maintaining strict CSS classing logic.
     * * @param string $key   The taxonomy or meta key.
     * @param string $label The frontend display label.
     * @param string $value The extracted value.
     */
    $render_item = function ($key, $label, $value) {
        echo "<div class='lgl-meta-item lgl-{$key}'>";
        echo "<span class='lgl-meta-icon-label'>";

        echo LGL_Shortcodes::render_inline_svg(sanitize_file_name($key) );

        echo "<span class='lgl-label'>" . esc_html($label) . "</span>";
        echo "</span>";
        echo "<span class='lgl-value'>" . esc_html($value) . "</span>";
        echo "</div>";
    };

    echo "<div class='lgl-meta-list'>";

    // 4. Iterate through the natively ordered list and execute routing
    foreach ($final_order as $key) {

        // Intercept and skip if explicitly hidden in the LGL settings panel
        if (!empty($options['hide_field_' . $key])) {
            continue;
        }

        // Route execution based on field type: Taxonomy
        if (in_array($key, $valid_taxonomies)) {
            $terms = get_the_terms($post_id, $key);

            if ($terms && !is_wp_error($terms)) {
                $tax_obj        = get_taxonomy($key);
                $taxonomy_label = $tax_obj ? $tax_obj->labels->singular_name : $key;
                $term_names     = wp_list_pluck($terms, 'name');
                $value          = join(', ', $term_names);

                $render_item($key, $taxonomy_label, $value);
            }
        }

        // Route execution based on field type: Meta Field
        elseif (array_key_exists($key, $valid_meta_fields)) {
            $value = get_post_meta($post_id, $key, true);

            if (!empty($value)) {
                $render_item($key, $valid_meta_fields[$key], $value);
            }
        }
    }

    echo "</div>";
}
