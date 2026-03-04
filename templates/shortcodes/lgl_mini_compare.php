<?php
if (!defined('ABSPATH')) {
    exit;
}
$options = get_option('lgl_settings', array());
$page_id = isset($options['vehicle_comparison_page_id']) ? intval($options['vehicle_comparison_page_id']) : 0;
$link_url = ($page_id > 0) ? get_permalink($page_id) : '#';

?>
<div class="lgl-mini-wishlist-wrapper">
    <a href="<?php echo esc_url($link_url); ?>" class="lgl-mini-wishlist-link" style="text-decoration: none; color: inherit;">
        <div class="lgl-mini-wishlist-toggle " role="button" tabindex="0">
            <svg width="25" height="25" viewBox="0 0 25 25" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                <path d="M15.75 10.9375L17.3125 12.5L22.6875 7.10938L17.1875 1.5625L15.625 3.125L18.4375 5.9375H3.125V8.125H18.4688L15.75 10.9375ZM9.15625 14.0625L7.59375 12.5L2.21875 17.9688L7.67187 23.4375L9.23437 21.875L6.40625 19.0625H21.875V16.875H6.40625L9.15625 14.0625Z"></path>
            </svg>
        </div>
    </a>
</div>
<?php
