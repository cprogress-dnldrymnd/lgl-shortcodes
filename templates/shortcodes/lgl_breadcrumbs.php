<?php
/**
 * Breadcrumbs and Back to Results Template
 * Shortcode: [lgl_breadcrumbs]
 */

if (!defined('ABSPATH')) {
    exit;
}

$post_type = get_post_type();
$post_type_obj = get_post_type_object($post_type);
$home_url = home_url();

$options = get_option('lgl_settings', array());
$archive_url = '';

// Resolve the proper archive base URL from settings
if ($post_type === 'caravan' && !empty($options['caravan_page'])) {
    $archive_url = get_permalink($options['caravan_page']);
} elseif ($post_type === 'motorhome' && !empty($options['motorhome_page'])) {
    $archive_url = get_permalink($options['motorhome_page']);
} elseif ($post_type === 'campervan' && !empty($options['campervan_page'])) {
    $archive_url = get_permalink($options['campervan_page']);
} else {
    // Fallback to native post type archive
    $archive_url = get_post_type_archive_link($post_type);
}

$post_type_label = $post_type_obj ? $post_type_obj->labels->name : 'Vehicles';

// The JS file will automatically replace the href of .lgl-br-archive and .lgl-back-to-results
// with the fully filtered URL stored in sessionStorage.
// Determine the CSS class based on the shortcode attribute
$style_class = (isset($style) && $style === 'light') ? 'lgl-breadcrumbs-light' : 'lgl-breadcrumbs-dark';

echo '<div class="lgl-breadcrumbs-wrapper ' . esc_attr($style_class) . '" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px;">';

    echo '<div class="lgl-breadcrumbs">';
        echo '<a href="' . esc_url($home_url) . '">Home</a> <span class="lgl-separator">|</span> ';

        if (is_singular() && in_array($post_type, array('caravan', 'motorhome', 'campervan'))) {
            // Single Vehicle Page
            echo '<a href="' . esc_url($archive_url) . '" class="lgl-br-archive">' . esc_html($post_type_label) . '</a> <span class="lgl-separator">&raquo;</span> ';
            echo '<span class="lgl-current-page">' . esc_html(get_the_title()) . '</span>';
            echo '</div>'; // End breadcrumbs left side
            
            // Back to Results Button (Right side)
            echo '<div class="lgl-br-back">';
            echo '<a href="' . esc_url($archive_url) . '" class="lgl-back-to-results lgl-btn lgl-btn-secondary" style="text-decoration: none;">&laquo; Back to Results</a>';
            echo '</div>';
        } else {
            // General Archive Page
            echo '<span class="lgl-current-page">' . esc_html($post_type_label) . '</span>';
            echo '</div>'; // End breadcrumbs left side
        }

echo '</div>';