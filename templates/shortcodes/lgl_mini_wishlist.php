<?php
$count = 0;
if (is_user_logged_in()) {
    $wishlist = get_user_meta(get_current_user_id(), 'lgl_wishlists', true);
    $count = is_array($wishlist) ? count($wishlist) : 0;
}

ob_start();
?>
<div class="lgl-mini-wishlist-wrapper">
    <div class="lgl-mini-wishlist-toggle" role="button" tabindex="0">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z" />
        </svg>
        <span class="lgl-wishlist-count"><?php echo esc_html($count); ?></span>
    </div>
    <div class="lgl-mini-wishlist-dropdown">
        <h3 class="lgl-mini-wishlist-header">My Wishlist</h3>
        <div class="lgl-mini-wishlist-content">
            <?php echo $this->get_mini_wishlist_html(); ?>
        </div>
        <div class="lgl-mini-wishlist-footer">
            <a href="/wishlist" class="lgl-view-wishlist-link">View Your Wishlist</a>
        </div>
    </div>
</div>