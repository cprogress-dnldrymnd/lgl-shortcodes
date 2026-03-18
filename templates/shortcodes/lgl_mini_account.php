<?php

/**
 * Template: Mini Account Bar Shortcode
 * Logged in  → "Hi, [Name]  |  Logout"
 * Logged out → "My Account" link → my_account_page_id setting
 *
 * Shortcode: [lgl_mini_account]
 */

if (! defined('ABSPATH')) {
    exit;
}

$options         = get_option('lgl_settings', array());
$account_page_id = isset($options['my_account_page_id']) ? intval($options['my_account_page_id']) : 0;
$account_url     = $account_page_id > 0 ? get_permalink($account_page_id) : wp_login_url(get_permalink());

if (is_user_logged_in()) :

    $user       = wp_get_current_user();
    $first_name = ! empty($user->first_name) ? $user->first_name : $user->display_name;
    $logout_url = wp_logout_url($account_url);

?>
    <div class="lgl-mini-account lgl-mini-account--logged-in">
        <a class="lgl-mini-account__name" href="<?php echo esc_url($account_url); ?>">
            <?php
            printf(
                /* translators: %s: user first name */
                esc_html__('Hi, %s', 'lgl-shortcodes'),
                esc_html($first_name)
            );
            ?>
        </a>
        <span class="lgl-mini-account__divider" aria-hidden="true">|</span>
        <a class="lgl-mini-account__logout" href="<?php echo esc_url($logout_url); ?>">
            <?php esc_html_e('Logout', 'lgl-shortcodes'); ?>
        </a>
    </div>

<?php else : ?>

    <div class="lgl-mini-account lgl-mini-account--logged-out">
        <a class="lgl-mini-account__login" href="<?php echo esc_url($account_url); ?>">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                <circle cx="12" cy="7" r="4" />
            </svg>
            <?php esc_html_e('My Account', 'lgl-shortcodes'); ?>
        </a>
    </div>

<?php endif; ?>