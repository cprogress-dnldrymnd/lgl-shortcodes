<?php
$listing_fields = LGL_Shortcodes::get_external_listing_fields();

// Define an array of meta keys you want to exclude from the frontend display.
$exclude_keys = array( 'internal_stock_number', 'rrp' ); 

if (!empty($listing_fields)) {
    // Access the specific field groupings
    $common_fields = $listing_fields['common'];
    $motorhome_campervan_fields = $listing_fields['motorhome_campervan'];
    
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
    echo "</div>";
}
?>