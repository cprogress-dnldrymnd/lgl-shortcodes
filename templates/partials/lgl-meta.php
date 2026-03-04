<?php
$listing_fields = LGL_Import_Post_Types::get_listing_detail_fields();

// Define an array of meta keys you want to exclude from the frontend display.
$exclude_keys = array('internal_stock_number', 'rrp', 'feature', 'warranty', 'sub_title', 'price');

if (!empty($listing_fields)) {
    // Access the specific field groupings
    $common_fields = $listing_fields['common'];
    $motorhome_campervan_fields = $listing_fields['motorhome_campervan'];
    $taxonomies = [];
    if ($post_type != 'campervan') {
        $common_fields = array_merge($common_fields, $motorhome_campervan_fields);
    } else {
        $taxonomies[] = 'listing-fuel-type';
        if ($post_type == 'motorhome') {
            $taxonomies[] = 'listing-chassis';
        }
        $taxonomies[] = 'listing-gearbox';
    }

    echo "<div class='lgl-meta-list'>";

    // Example iteration over common fields
    foreach ($common_fields as $meta_key => $label) {

        // Intercept and skip the current iteration if the meta key exists in the exclusion array.
        if (in_array($meta_key, $exclude_keys, true)) {
            continue;
        }

        $meta_value = get_post_meta($post_id, $meta_key, true);

        if (!empty($meta_value)) {
            echo "<div class='lgl-meta-item lgl-{$meta_key}'>";
            echo "<span class='lgl-meta-icon-label'>";

            /**
             * Construct the absolute path to the SVG file based on the current meta key.
             * Utilizes the plugin's root path constant.
             */
            $svg_file_path = LGL_SHORTCODES_PATH . 'assets/svg/' . $meta_key . '.svg';

            // Ensure the file exists on the server before attempting to read it
            if (file_exists($svg_file_path)) {
                // Output the raw SVG markup inline directly into the DOM
                echo file_get_contents($svg_file_path);
            }

            echo "<span class='lgl-label'>";
            echo esc_html($label);
            echo "</span>";
            echo "</span>";

            echo "<span class='lgl-value'>";
            echo esc_html($meta_value);
            echo "</span>";

            echo "</div>";
        }
    }

    /**
     * Iterate over the defined taxonomies array and retrieve associated terms.
     * Appends each taxonomy as a meta item matching the established DOM structure.
     */
    if (!empty($taxonomies)) {
        foreach ($taxonomies as $taxonomy_slug) {
            // Retrieve all terms assigned to the current post for this specific taxonomy.
            $terms = get_the_terms($post_id, $taxonomy_slug);

            // Proceed only if terms exist and no WP_Error was returned.
            if ($terms && !is_wp_error($terms)) {
                // Fetch the taxonomy object to dynamically retrieve its registered singular label.
                $tax_obj = get_taxonomy($taxonomy_slug);
                $taxonomy_label = $tax_obj ? $tax_obj->labels->singular_name : $taxonomy_slug;

                // Efficiently extract term names and join them into a comma-separated string for multi-select taxonomies.
                $term_names = wp_list_pluck($terms, 'name');
                $taxonomy_value = join(', ', $term_names);

                echo "<div class='lgl-meta-item lgl-{$taxonomy_slug}'>";
                echo "<span class='lgl-meta-icon-label'>";

                /**
                 * Construct the absolute path to the SVG file based on the taxonomy slug.
                 * Utilizes the plugin's root path constant.
                 */
                $svg_file_path = LGL_SHORTCODES_PATH . 'assets/svg/' . $taxonomy_slug . '.svg';

                // Ensure the file exists on the server before attempting to read it.
                if (file_exists($svg_file_path)) {
                    // Output the raw SVG markup inline directly into the DOM.
                    echo file_get_contents($svg_file_path);
                }

                echo "<span class='lgl-label'>";
                echo esc_html($taxonomy_label);
                echo "</span>";
                echo "</span>";

                echo "<span class='lgl-value'>";
                echo esc_html($taxonomy_value);
                echo "</span>";

                echo "</div>";
            }
        }
    }

    echo "</div>";
}
