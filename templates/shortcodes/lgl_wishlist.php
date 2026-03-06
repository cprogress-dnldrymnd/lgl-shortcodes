<?php

/**
 * Template: Wishlist Page Shortcode
 * Renders the full wishlist page for the currently logged-in user.
 * Reuses the existing mini-wishlist CSS classes so no additional styles are required.
 *
 * Shortcode: [lgl_wishlist]
 */

if (!defined('ABSPATH')) {
    exit;
}
$options = get_option('lgl_settings', array());
if (!empty($options['disable_wishlist'])) {
    return;
}

if (!is_user_logged_in()) : ?>
    <div class="lgl-mini-wishlist-content">
        <div class="lgl-wishlist-empty">
            <?php esc_html_e('Please log in to view your saved wishlist.', 'lgl-shortcodes'); ?>
        </div>
    </div>
<?php return;
endif;

$user_id  = get_current_user_id();
$wishlist = get_user_meta($user_id, 'lgl_wishlists', true);
$wishlist = is_array($wishlist) ? array_values(array_filter(array_map('intval', $wishlist))) : array();
?>

<div class="lgl-wishlist-page" id="lgl-wishlist-page">

    <?php if (empty($wishlist)) : ?>

        <div class="lgl-mini-wishlist-content">
            <div class="lgl-wishlist-empty">
                <?php esc_html_e('Your wishlist is currently empty.', 'lgl-shortcodes'); ?>
            </div>
        </div>

    <?php else : ?>

        <div class="lgl-mini-wishlist-content lgl-wishlist-page__list" id="lgl-wishlist-page-list">
            <ul class="lgl-mini-wishlist-items">
                <?php foreach ($wishlist as $post_id) :
                    $post = get_post($post_id);
                    if (!$post || $post->post_status !== 'publish') continue;

                    $link            = get_permalink($post_id);
                    $title           = get_the_title($post_id);
                    $price           = get_post_meta($post_id, 'price', true);
                    $formatted_price = $price ? '$' . number_format((float) $price, 0) : '';
                ?>
                    <li class="lgl-wishlist-item" data-post-id="<?php echo esc_attr($post_id); ?>">

                        <div class="lgl-wishlist-thumb">
                            <a href="<?php echo esc_url($link); ?>" tabindex="-1" aria-hidden="true">
                                <?php if (has_post_thumbnail($post_id)) : ?>
                                    <?php echo get_the_post_thumbnail($post_id, 'thumbnail'); ?>
                                <?php endif; ?>
                            </a>
                        </div>

                        <div class="lgl-wishlist-info">
                            <h4 class="lgl-wishlist-title">
                                <a href="<?php echo esc_url($link); ?>"><?php echo esc_html($title); ?></a>
                            </h4>
                            <?php if ($formatted_price) : ?>
                                <span class="lgl-wishlist-price"><?php echo esc_html($formatted_price); ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="lgl-wishlist-remove">
                            <button
                                class="lgl-remove-btn"
                                data-id="<?php echo esc_attr($post_id); ?>"
                                aria-label="<?php esc_attr_e('Remove from wishlist', 'lgl-shortcodes'); ?>">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#999" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2M10 11v6M14 11v6" />
                                </svg>
                            </button>
                        </div>

                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

    <?php endif; ?>

</div>

<script type="text/javascript">
    jQuery(document).ready(function($) {
        /**
         * Handles remove-from-wishlist clicks.
         * Reuses the same lgl_add_to_wishlist AJAX toggle action as the mini wishlist.
         * Fades out the row and shows the empty state if none remain.
         */
        $('#lgl-wishlist-page').on('click', '.lgl-remove-btn', function() {
            const btn = $(this);
            const postId = btn.data('id');
            const row = btn.closest('.lgl-wishlist-item');

            btn.prop('disabled', true);

            $.ajax({
                url: lgl_ajax_obj.ajax_url,
                type: 'POST',
                data: {
                    action: 'lgl_add_to_wishlist',
                    nonce: lgl_ajax_obj.nonce,
                    post_id: postId
                },
                success: function(response) {
                    if (response.success) {
                        row.fadeOut(250, function() {
                            $(this).remove();

                            // Replace the list with the empty state message if nothing remains
                            if ($('#lgl-wishlist-page-list .lgl-wishlist-item').length === 0) {
                                $('#lgl-wishlist-page-list').replaceWith(
                                    '<div class="lgl-mini-wishlist-content">' +
                                    '<div class="lgl-wishlist-empty">' +
                                    '<?php echo esc_js(__('Your wishlist is currently empty.', 'lgl-shortcodes')); ?>' +
                                    '</div>' +
                                    '</div>'
                                );
                            }
                        });

                        // Sync the mini-wishlist count badge if present on the same page
                        if (response.data && typeof response.data.count !== 'undefined') {
                            $('.lgl-wishlist-count').text(response.data.count);
                        }

                        // Trigger global refresh for the mini-wishlist dropdown
                        document.dispatchEvent(new Event('lgl_wishlist_updated'));
                    }
                },
                error: function() {
                    btn.prop('disabled', false);
                }
            });
        });
    });
</script>