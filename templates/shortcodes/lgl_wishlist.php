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
    echo 'Wishlist is disabled';
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
            <?php echo $this->get_mini_wishlist_html(); ?>
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