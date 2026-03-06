<?php
if ($post_type) {
    $results = LGL_Shortcodes::get_search_results_data(
        post_type: $post_type,
        paged: 1,
        posts_per_page: 6
    );
    echo $results['post_type'];
    echo 'xx2';
    echo '<div class="lgl-grid-layout lgl-cols--3 lgl-layout-default" id="lgl-results-grid">';
    echo $results['html'];
    echo '</div>';
}
