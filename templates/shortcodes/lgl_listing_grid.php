<?php
if ($post_type) {
    $lgl = new LGL_Shortcodes(); // or use your existing instance

    $results = $lgl->get_search_results_data(
        post_type: 'caravan',
        form_data: array('sort_order' => 'price_low'),
        paged: 1,
        posts_per_page: 6
    );

    echo '<div class="lgl-grid-layout lgl-cols--3 lgl-layout-default" id="lgl-results-grid">';
    echo $results['html'];
    echo '</div>';
}
