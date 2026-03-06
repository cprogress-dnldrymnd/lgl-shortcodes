<?php

/**
 * Template: Wishlist Page Shortcode
 * Renders the full wishlist page for the currently logged-in user.
 * Wishlist data is stored in user meta key `lgl_wishlists` as an array of post IDs.
 *
 * Shortcode: [lgl_wishlist]
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!is_user_logged_in()) : ?>
    <div class="lgl-wishlist-page lgl-wishlist-page--guest">
        <p class="lgl-wishlist-page__empty">
            <?php esc_html_e('Please log in to view your saved wishlist.', 'lgl-shortcodes'); ?>
        </p>
    </div>
<?php return;
endif;

$user_id  = get_current_user_id();
$wishlist = get_user_meta($user_id, 'lgl_wishlists', true);
$wishlist = is_array($wishlist) ? array_filter(array_map('intval', $wishlist)) : array();
?>

<div class="lgl-wishlist-page" id="lgl-wishlist-page">

    <?php if (empty($wishlist)) : ?>

        <div class="lgl-wishlist-page__empty-state">
            <svg xmlns="http://www.w3.org/2000/svg" width="56" height="56" viewBox="0 0 25 25" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M14.9916 18.8679C14.3066 19.4224 13.4266 19.7282 12.5129 19.7282C11.6004 19.7282 10.7179 19.4236 10.0054 18.8512C5.51289 15.2466 2.65289 13.3342 2.50414 9.41186C2.34789 5.26103 7.12289 3.74375 10.0504 7.15438C10.6429 7.84341 11.5329 8.2385 12.4929 8.2385C13.4616 8.2385 14.3579 7.83864 14.9516 7.14128C17.8154 3.78539 22.7179 5.21462 22.4941 9.53324C22.2941 13.3747 19.3241 15.3585 14.9916 18.8679Z" />
            </svg>
            <p><?php esc_html_e('Your wishlist is currently empty.', 'lgl-shortcodes'); ?></p>
        </div>

    <?php else : ?>

        <div class="lgl-wishlist-page__grid" id="lgl-wishlist-page-grid">
            <?php foreach ($wishlist as $post_id) :
                $post = get_post($post_id);
                if (!$post || $post->post_status !== 'publish') continue;

                $link      = get_permalink($post_id);
                $title     = get_the_title($post_id);
                $condition = get_post_meta($post_id, 'condition', true);
                $price     = get_post_meta($post_id, 'price', true);
                $berth     = get_post_meta($post_id, 'berth', true);
                $year      = get_post_meta($post_id, 'year', true);
                $formatted_price = $price ? '$' . number_format((float) $price, 0) : '';
            ?>
                <article class="lgl-wishlist-page__item" data-post-id="<?php echo esc_attr($post_id); ?>">

                    <div class="lgl-wishlist-page__image">
                        <a href="<?php echo esc_url($link); ?>">
                            <div class="lgl-cover-image">
                                <?php if (has_post_thumbnail($post_id)) : ?>
                                    <?php echo get_the_post_thumbnail($post_id, 'large'); ?>
                                <?php else : ?>
                                    <div class="lgl-wishlist-page__no-image"></div>
                                <?php endif; ?>
                            </div>
                        </a>

                        <button
                            class="lgl-wishlist-page__remove"
                            data-id="<?php echo esc_attr($post_id); ?>"
                            aria-label="<?php esc_attr_e('Remove from wishlist', 'lgl-shortcodes'); ?>">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                        </button>
                    </div>

                    <div class="lgl-wishlist-page__body">
                        <?php if ($condition) : ?>
                            <span class="lgl-value"><?php echo esc_html($condition); ?></span>
                        <?php endif; ?>

                        <h3 class="lgl-wishlist-page__title">
                            <a href="<?php echo esc_url($link); ?>"><?php echo esc_html($title); ?></a>
                        </h3>

                        <?php if ($formatted_price) : ?>
                            <p class="lgl-wishlist-page__price"><?php echo esc_html($formatted_price); ?></p>
                        <?php endif; ?>

                        <div class="lgl-wishlist-page__meta">
                            <?php if ($berth) : ?>
                                <div class="lgl-wishlist-page__meta-item">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="12" cy="7" r="4"></circle>
                                    </svg>
                                    <span class="lgl-wishlist-page__meta-label"><?php esc_html_e('Berth', 'lgl-shortcodes'); ?></span>
                                    <span class="lgl-wishlist-page__meta-value"><?php echo esc_html($berth); ?></span>
                                </div>
                            <?php endif; ?>

                            <?php if ($year) : ?>
                                <div class="lgl-wishlist-page__meta-item">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                        <line x1="16" y1="2" x2="16" y2="6"></line>
                                        <line x1="8" y1="2" x2="8" y2="6"></line>
                                        <line x1="3" y1="10" x2="21" y2="10"></line>
                                    </svg>
                                    <span class="lgl-wishlist-page__meta-label"><?php esc_html_e('Year', 'lgl-shortcodes'); ?></span>
                                    <span class="lgl-wishlist-page__meta-value"><?php echo esc_html($year); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <a href="<?php echo esc_url($link); ?>" class="lgl-btn lgl-wishlist-page__view-btn">
                            <?php esc_html_e('View Details', 'lgl-shortcodes'); ?>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path d="M4 12H20M20 12L16 8M20 12L16 16" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </a>
                    </div>

                </article>
            <?php endforeach; ?>
        </div>

    <?php endif; ?>

</div>

<script type="text/javascript">
    jQuery(document).ready(function($) {
        /**
         * Handles remove-from-wishlist clicks on the full wishlist page.
         * Fires the existing lgl_add_to_wishlist AJAX action (which toggles),
         * then removes the card from the DOM and shows the empty state if none remain.
         */
        $('#lgl-wishlist-page').on('click', '.lgl-wishlist-page__remove', function() {
            const btn = $(this);
            const postId = btn.data('id');
            const card = btn.closest('.lgl-wishlist-page__item');

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
                        card.fadeOut(250, function() {
                            $(this).remove();

                            // Show empty state if no cards remain
                            if ($('#lgl-wishlist-page-grid .lgl-wishlist-page__item').length === 0) {
                                $('#lgl-wishlist-page-grid').replaceWith(
                                    '<div class="lgl-wishlist-page__empty-state">' +
                                    '<p><?php echo esc_js(__('Your wishlist is currently empty.', 'lgl-shortcodes')); ?></p>' +
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