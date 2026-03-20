<?php
/**
 * Breadcrumbs and Back to Results Template
 * Shortcode: [lgl_breadcrumbs]
 */

if (!defined('ABSPATH')) {
    exit;
}

$post_type = get_post_type();
$home_url = home_url();
$options = get_option('lgl_settings', array());
$current_id = get_queried_object_id();

// Determine the CSS class based on the shortcode attribute. Defaults to 'dark'.
$style_class = (isset($style) && $style === 'light') ? 'lgl-breadcrumbs-light' : 'lgl-breadcrumbs-dark';

echo '<div class="lgl-breadcrumbs-wrapper ' . esc_attr($style_class) . '">';

    echo '<div class="lgl-breadcrumbs">';
    echo '<a href="' . esc_url($home_url) . '">Home</a> <span class="lgl-separator">|</span> ';

    // 1. Single Vehicle Page View
    if (is_singular(array('caravan', 'motorhome', 'campervan'))) {
        $archive_url = '';
        $archive_label = '';
        
        // Resolve the proper archive base URL and Label from settings
        if ($post_type === 'caravan') {
            $archive_url = !empty($options['caravan_page']) ? get_permalink($options['caravan_page']) : get_post_type_archive_link('caravan');
            $archive_label = 'Caravans';
        } elseif ($post_type === 'motorhome') {
            $archive_url = !empty($options['motorhome_page']) ? get_permalink($options['motorhome_page']) : get_post_type_archive_link('motorhome');
            $archive_label = 'Motorhomes';
        } elseif ($post_type === 'campervan') {
            $archive_url = !empty($options['campervan_page']) ? get_permalink($options['campervan_page']) : get_post_type_archive_link('campervan');
            $archive_label = 'Campervans';
        }

        // Output middle breadcrumb (Archive Link)
        echo '<a href="' . esc_url($archive_url) . '" class="lgl-br-archive">' . esc_html($archive_label) . '</a> <span class="lgl-separator">|</span> ';
        // Output current vehicle title
        echo '<span class="lgl-current-page">' . esc_html(get_the_title()) . '</span>';
        echo '</div>'; // End breadcrumbs left side
        
        // Output Back to Results Button (Right side)
        // The href here will be dynamically overwritten by our JS to include previous filter parameters
        echo '<div class="lgl-br-back">';
        echo '<a href="' . esc_url($archive_url) . '" class="lgl-back-to-results">&laquo; Back to Results</a>';
        echo '</div>';
    } 
    // 2. Custom Archive Pages or Standard Pages View
    else {
        // Fetch the actual title of the current page (e.g., "Motorhomes") instead of the post type label
        $page_title = get_the_title($current_id);
        
        echo '<span class="lgl-current-page">' . esc_html($page_title) . '</span>';
        echo '</div>'; // End breadcrumbs left side
    }

echo '</div>';