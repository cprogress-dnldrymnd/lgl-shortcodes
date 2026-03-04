<?php
if (!defined('ABSPATH')) {
    exit;
}
global $post;

// Inherit context from the current global post; fallback to 'caravan' if outside the loop
$current_post_type = (is_a($post, 'WP_Post')) ? $post->post_type : 'caravan';
$current_post_id   = (is_a($post, 'WP_Post')) ? $post->ID : 0;

// Standardize parameters, permitting overrides via shortcode attributes
$attributes = shortcode_atts(array(
    'post_type' => $current_post_type,
    'count'     => 3
), $atts, 'lgl_related_vehicles');

// Compile targeted WP_Query arguments
$args = array(
    'post_type'      => sanitize_text_field($attributes['post_type']),
    'post_status'    => 'publish',
    'posts_per_page' => intval($attributes['count']),
    'orderby'        => 'rand',           // Trigger MySQL RAND() for randomized output
    'post__not_in'   => array($current_post_id), // Prevent querying the currently active listing
);

// Retrieve 'listing-make-model' term IDs for the current post context
if ( $current_post_id ) {
    $term_ids = wp_get_post_terms( $current_post_id, 'listing-make-model', array( 'fields' => 'ids' ) );
    
    // Inject tax_query parameter if the current post possesses valid taxonomy terms
    if ( ! is_wp_error( $term_ids ) && ! empty( $term_ids ) ) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'listing-make-model',
                'field'    => 'term_id',
                'terms'    => $term_ids,
            ),
        );
    }
}

$query = new WP_Query($args);

ob_start();

if ($query->have_posts()) {
    // Implementing a structural wrapper for front-end DOM manipulation (flexbox/CSS grids)
    echo '<div class="lgl-related-posts">';
    echo '<h2 class="lgl-related-heading">Related Vehicles</h2>';
    echo '<div class="lgl-grid-layout lgl-cols--3">';
    while ($query->have_posts()) {
        $query->the_post();
        // Invoke existing isolated template logic to adhere to the DRY principle
        include LGL_SHORTCODES_PATH . 'templates/partials/lgl-grid.php';
    }
    echo '</div>';
    echo '</div>';
} else {
    echo '<div class="lgl-no-results">No related vehicles available at this time.</div>';
}

// Restore global post data object
wp_reset_postdata();