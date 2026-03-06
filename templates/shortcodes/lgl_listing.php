<?php
if ($post_type) {
    $results = LGL_Shortcodes::get_search_results_data(
        post_type: explode(',', $post_type),
        paged: 1,
        posts_per_page: $limit,
        is_carousel: $is_carousel,
        style: $style,
        is_featured: $is_featured
    );
    if ($is_carousel) {
        echo '<div class="vehicle-slider-holder">';
        echo '<div class="vehicle-slider-jr">';
    } else {
        echo '<div class="lgl-grid-layout lgl-cols--3 lgl-layout-default ">';
    }
    echo $results['html'];
    if ($is_carousel) {
        echo '</div>';
    }
    echo '</div>';
}
