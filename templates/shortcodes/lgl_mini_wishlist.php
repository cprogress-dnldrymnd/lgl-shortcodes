<?php
if (!defined('ABSPATH')) {
    exit;
}
$count = 0;
if (is_user_logged_in()) {
    $wishlist = get_user_meta(get_current_user_id(), 'lgl_wishlists', true);
    $count = is_array($wishlist) ? count($wishlist) : 0;
}

ob_start();
?>
<div class="lgl-mini-wishlist-wrapper">
    <div class="lgl-mini-wishlist-toggle lgl-mini-wishlist-toggle-trigger" role="button" tabindex="0">
        <svg width="25" height="25" viewBox="0 0 25 25" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd" clip-rule="evenodd" d="M14.9916 18.8679C14.3066 19.4224 13.4266 19.7282 12.5129 19.7282C11.6004 19.7282 10.7179 19.4236 10.0054 18.8512C5.51289 15.2466 2.65289 13.3342 2.50414 9.41186C2.34789 5.26103 7.12289 3.74375 10.0504 7.15438C10.6429 7.84341 11.5329 8.2385 12.4929 8.2385C13.4616 8.2385 14.3579 7.83864 14.9516 7.14128C17.8154 3.78539 22.7179 5.21462 22.4941 9.53324C22.2941 13.3747 19.3241 15.3585 14.9916 18.8679ZM12.9841 5.72634C12.8616 5.87033 12.6766 5.94292 12.4929 5.94292C12.3129 5.94292 12.1341 5.87271 12.0141 5.73348C7.58539 0.574693 -0.234601 3.14396 0.0053982 9.49159C0.196648 14.5433 3.95664 17.0471 8.38414 20.5994C9.56788 21.549 11.0404 22.0238 12.5129 22.0238C13.9891 22.0238 15.4641 21.5466 16.6454 20.5898C21.0241 17.0424 24.7366 14.5552 24.9904 9.64154C25.3279 3.1523 17.4016 0.546134 12.9841 5.72634Z"></path>
        </svg>
        <span class="lgl-wishlist-count"><?php echo esc_html($count); ?></span>
    </div>
    <div class="lgl-mini-wishlist-dropdown">
        <div class="lgl-mini-wishlist-header">
            <h3>My Wishlist</h3>
            <button class="lgl-mini-close-wishlist lgl-mini-wishlist-toggle-trigger">
                X
            </button>
        </div>
        <div class="lgl-mini-wishlist-content">
            <?php echo $this->get_mini_wishlist_html(); ?>
        </div>
        <div class="lgl-mini-wishlist-footer">
            <a href="/wishlist" class="lgl-view-wishlist-link">View Your Wishlist</a>
        </div>
    </div>
</div>