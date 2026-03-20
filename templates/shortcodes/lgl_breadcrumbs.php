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

    // Helper function to strictly cast page IDs and ensure valid URLs
    $get_archive_url = function ($setting_key, $cpt_slug) use ($options, $home_url) {
        // Check if setting exists and is not empty
        if (!empty($options[$setting_key])) {
            $permalink = get_permalink((int)$options[$setting_key]);
            if ($permalink) {
                return $permalink;
            }
        }
        // Fallback to native post type archive if settings fail
        $link = get_post_type_archive_link($cpt_slug);
        return $link ? $link : rtrim($home_url, '/') . '/' . $cpt_slug . '/';
    };

    // Resolve the proper archive base URL and Label
    if ($post_type === 'caravan') {
        $archive_url = $get_archive_url('caravan_page', 'caravan');
        $archive_label = 'Caravans';
    } elseif ($post_type === 'motorhome') {
        $archive_url = $get_archive_url('motorhome_page', 'motorhome');
        $archive_label = 'Motorhomes';
    } elseif ($post_type === 'campervan') {
        $archive_url = $get_archive_url('campervan_page', 'campervan');
        $archive_label = 'Campervans';
    }

    // Output middle breadcrumb (Archive Link)
    echo '<a href="' . esc_url($archive_url) . '" class="lgl-br-archive">' . esc_html($archive_label) . '</a> <span class="lgl-separator">|</span> ';
    // Output current vehicle title
    echo '<span class="lgl-current-page">' . esc_html(get_the_title()) . '</span>';
    echo '</div>'; // End breadcrumbs left side

    // Output Back to Results Button (Right side)
    echo '<div class="lgl-br-back lgl-back-to-results-wrapper" style="display: none;">';
    echo '<a href="' . esc_url($archive_url) . '" class="lgl-back-to-results" style="text-decoration: none;">&laquo; Back to Results</a>';
    echo '</div>';
}
// 2. Custom Archive Pages or Standard Pages View
else {
    // Fetch the actual title of the current page
    $page_title = get_the_title($current_id);

    echo '<span class="lgl-current-page">' . esc_html($page_title) . '</span>';
    echo '</div>'; // End breadcrumbs left side
}

echo '</div>';
