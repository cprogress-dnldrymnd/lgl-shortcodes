<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<?php if (!$disable_compare) { ?>
    <a class="lgl-icon-btn lgl-compare-btn" href="#"
        data-post-id="<?php echo esc_attr($post_id); ?>"
        data-post-type="<?php echo esc_attr($post_type); ?>" data-title="<?php echo get_the_title(esc_attr($post_id)); ?>"
        aria-label="<?php esc_attr_e('Add vehicle to comparison list', 'lgl-shortcodes'); ?>">
        <svg width="25" height="25" viewBox="0 0 25 25" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
            <path d="M15.75 10.9375L17.3125 12.5L22.6875 7.10938L17.1875 1.5625L15.625 3.125L18.4375 5.9375H3.125V8.125H18.4688L15.75 10.9375ZM9.15625 14.0625L7.59375 12.5L2.21875 17.9688L7.67187 23.4375L9.23437 21.875L6.40625 19.0625H21.875V16.875H6.40625L9.15625 14.0625Z"></path>
        </svg>
    </a>

<?php } ?>