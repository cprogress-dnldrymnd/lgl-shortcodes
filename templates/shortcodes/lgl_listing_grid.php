<?php
if ($post_type) {
    echo $post_type;
    echo LGL_Shortcodes::get_search_results_data(
        post_type: $post_type,
        paged: 1,
        posts_per_page: 6
    );
}
