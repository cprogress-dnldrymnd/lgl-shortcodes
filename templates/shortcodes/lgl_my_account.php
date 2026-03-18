<?php

/**
 * Template: My Account Shortcode
 * Renders a full account dashboard with tabs: Dashboard, Wishlist, Account Details, Logout.
 * Displays a login/register form when the user is not authenticated.
 *
 * Shortcode: [lgl_my_account]
 */

if (! defined('ABSPATH')) {
    exit;
}

// ─── Active tab resolution ────────────────────────────────────────────────────
$active_tab = isset($_GET['lgl_account_tab']) ? sanitize_key($_GET['lgl_account_tab']) : 'dashboard';
$allowed_tabs = array('dashboard', 'wishlist', 'account-details');
if (! in_array($active_tab, $allowed_tabs, true)) {
    $active_tab = 'dashboard';
}

// ─── Guest: Login / Register ──────────────────────────────────────────────────
if (! is_user_logged_in()) :
    $login_error    = '';
    $register_error = '';
    $register_success = '';

    // Handle Login Submission
    if (isset($_POST['lgl_login_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['lgl_login_nonce'])), 'lgl_login_action')) {
        $credentials = array(
            'user_login'    => sanitize_text_field(wp_unslash($_POST['lgl_username'] ?? '')),
            'user_password' => wp_unslash($_POST['lgl_password'] ?? ''),
            'remember'      => ! empty($_POST['lgl_remember']),
        );
        $user = wp_signon($credentials, is_ssl());
        if (is_wp_error($user)) {
            $login_error = $user->get_error_message();
        } else {
            wp_safe_redirect(get_permalink());
            exit;
        }
    }

    // Handle Register Submission
    if (isset($_POST['lgl_register_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['lgl_register_nonce'])), 'lgl_register_action')) {
        $reg_first = sanitize_text_field(wp_unslash($_POST['lgl_reg_first'] ?? ''));
        $reg_last  = sanitize_text_field(wp_unslash($_POST['lgl_reg_last'] ?? ''));
        $reg_email = sanitize_email(wp_unslash($_POST['lgl_reg_email'] ?? ''));
        $reg_pass  = wp_unslash($_POST['lgl_reg_password'] ?? '');
        $reg_pass2 = wp_unslash($_POST['lgl_reg_password2'] ?? '');

        if (empty($reg_first) || empty($reg_last) || empty($reg_email) || empty($reg_pass)) {
            $register_error = __('Please fill in all fields.', 'lgl-shortcodes');
        } elseif ($reg_pass !== $reg_pass2) {
            $register_error = __('Passwords do not match.', 'lgl-shortcodes');
        } elseif (! is_email($reg_email)) {
            $register_error = __('Please enter a valid email address.', 'lgl-shortcodes');
        } elseif (get_user_by('email', $reg_email)) {
            $register_error = __('An account with this email address already exists.', 'lgl-shortcodes');
        } else {
            $reg_username = sanitize_user(strtolower($reg_first . '.' . $reg_last), true);
            $reg_username = ! username_exists($reg_username) ? $reg_username : $reg_email;

            $new_user_id = wp_create_user($reg_username, $reg_pass, $reg_email);

            if (is_wp_error($new_user_id)) {
                $register_error = $new_user_id->get_error_message();
            } else {
                wp_update_user(array(
                    'ID'         => $new_user_id,
                    'first_name' => $reg_first,
                    'last_name'  => $reg_last,
                ));

                // Auto-login after registration
                $credentials = array(
                    'user_login'    => $reg_username,
                    'user_password' => $reg_pass,
                    'remember'      => true,
                );
                $user = wp_signon($credentials, is_ssl());
                if (! is_wp_error($user)) {
                    wp_safe_redirect(get_permalink());
                    exit;
                }
                $register_success = __('Account created successfully! You can now log in.', 'lgl-shortcodes');
            }
        }
    }

    // Default sub-tab (login vs register)
    $auth_tab = isset($_GET['lgl_auth']) ? sanitize_key($_GET['lgl_auth']) : 'login';
    if (! empty($register_error)) {
        $auth_tab = 'register';
    }
?>

    <div class="lgl-account-wrap lgl-account-guest">

        <div class="lgl-auth-tabs lgl-tabs-js" role="tablist">
            <a href="#lgl-auth-login"
                class="lgl-auth-tab lgl-nav-item <?php echo $auth_tab === 'login' ? 'lgl-is-active' : ''; ?>"
                role="tab"
                aria-selected="<?php echo $auth_tab === 'login' ? 'true' : 'false'; ?>">
                <?php esc_html_e('Login', 'lgl-shortcodes'); ?>
            </a>
            <a href="#lgl-auth-register"
                class="lgl-auth-tab lgl-nav-item <?php echo $auth_tab === 'register' ? 'lgl-is-active' : ''; ?>"
                role="tab"
                aria-selected="<?php echo $auth_tab === 'register' ? 'true' : 'false'; ?>">
                <?php esc_html_e('Register', 'lgl-shortcodes'); ?>
            </a>
        </div>

        <!-- LOGIN PANEL -->
        <div id="lgl-auth-login"
            class="lgl-auth-panel lgl-tab-panel <?php echo $auth_tab === 'login' ? 'lgl-is-active' : ''; ?>"
            role="tabpanel">

            <?php if ($login_error) : ?>
                <div class="lgl-account-notice lgl-account-notice--error">
                    <?php echo wp_kses_post($login_error); ?>
                </div>
            <?php endif; ?>

            <form class="lgl-auth-form" method="post" action="">
                <?php wp_nonce_field('lgl_login_action', 'lgl_login_nonce'); ?>

                <div class="lgl-form-group">
                    <label for="lgl-login-username"><?php esc_html_e('Username or Email', 'lgl-shortcodes'); ?></label>
                    <input type="text"
                        id="lgl-login-username"
                        name="lgl_username"
                        value="<?php echo isset($_POST['lgl_username']) ? esc_attr(sanitize_text_field(wp_unslash($_POST['lgl_username']))) : ''; ?>"
                        autocomplete="username"
                        required />
                </div>

                <div class="lgl-form-group">
                    <label for="lgl-login-password"><?php esc_html_e('Password', 'lgl-shortcodes'); ?></label>
                    <input type="password"
                        id="lgl-login-password"
                        name="lgl_password"
                        autocomplete="current-password"
                        required />
                </div>

                <div class="lgl-form-group lgl-form-row">
                    <label class="lgl-checkbox-label">
                        <input type="checkbox" name="lgl_remember" value="1" />
                        <?php esc_html_e('Remember me', 'lgl-shortcodes'); ?>
                    </label>
                    <a class="lgl-forgot-link" href="<?php echo esc_url(wp_lostpassword_url()); ?>">
                        <?php esc_html_e('Forgot password?', 'lgl-shortcodes'); ?>
                    </a>
                </div>

                <button type="submit" class="lgl-btn lgl-btn--primary">
                    <?php esc_html_e('Log In', 'lgl-shortcodes'); ?>
                </button>
            </form>
        </div><!-- /login panel -->

        <!-- REGISTER PANEL -->
        <div id="lgl-auth-register"
            class="lgl-auth-panel lgl-tab-panel <?php echo $auth_tab === 'register' ? 'lgl-is-active' : ''; ?>"
            role="tabpanel">

            <?php if ($register_error) : ?>
                <div class="lgl-account-notice lgl-account-notice--error">
                    <?php echo wp_kses_post($register_error); ?>
                </div>
            <?php endif; ?>

            <?php if ($register_success) : ?>
                <div class="lgl-account-notice lgl-account-notice--success">
                    <?php echo wp_kses_post($register_success); ?>
                </div>
            <?php endif; ?>

            <?php if (get_option('users_can_register')) : ?>

                <form class="lgl-auth-form" method="post" action="">
                    <?php wp_nonce_field('lgl_register_action', 'lgl_register_nonce'); ?>

                    <div class="lgl-form-row-split">
                        <div class="lgl-form-group">
                            <label for="lgl-reg-first"><?php esc_html_e('First Name', 'lgl-shortcodes'); ?></label>
                            <input type="text"
                                id="lgl-reg-first"
                                name="lgl_reg_first"
                                value="<?php echo isset($_POST['lgl_reg_first']) ? esc_attr(sanitize_text_field(wp_unslash($_POST['lgl_reg_first']))) : ''; ?>"
                                required />
                        </div>
                        <div class="lgl-form-group">
                            <label for="lgl-reg-last"><?php esc_html_e('Last Name', 'lgl-shortcodes'); ?></label>
                            <input type="text"
                                id="lgl-reg-last"
                                name="lgl_reg_last"
                                value="<?php echo isset($_POST['lgl_reg_last']) ? esc_attr(sanitize_text_field(wp_unslash($_POST['lgl_reg_last']))) : ''; ?>"
                                required />
                        </div>
                    </div>

                    <div class="lgl-form-group">
                        <label for="lgl-reg-email"><?php esc_html_e('Email Address', 'lgl-shortcodes'); ?></label>
                        <input type="email"
                            id="lgl-reg-email"
                            name="lgl_reg_email"
                            value="<?php echo isset($_POST['lgl_reg_email']) ? esc_attr(sanitize_email(wp_unslash($_POST['lgl_reg_email']))) : ''; ?>"
                            autocomplete="email"
                            required />
                    </div>

                    <div class="lgl-form-row-split">
                        <div class="lgl-form-group">
                            <label for="lgl-reg-password"><?php esc_html_e('Password', 'lgl-shortcodes'); ?></label>
                            <input type="password"
                                id="lgl-reg-password"
                                name="lgl_reg_password"
                                autocomplete="new-password"
                                required />
                        </div>
                        <div class="lgl-form-group">
                            <label for="lgl-reg-password2"><?php esc_html_e('Confirm Password', 'lgl-shortcodes'); ?></label>
                            <input type="password"
                                id="lgl-reg-password2"
                                name="lgl_reg_password2"
                                autocomplete="new-password"
                                required />
                        </div>
                    </div>

                    <button type="submit" class="lgl-btn lgl-btn--primary">
                        <?php esc_html_e('Create Account', 'lgl-shortcodes'); ?>
                    </button>
                </form>

            <?php else : ?>
                <p class="lgl-account-notice"><?php esc_html_e('User registration is currently disabled.', 'lgl-shortcodes'); ?></p>
            <?php endif; ?>

        </div><!-- /register panel -->

    </div><!-- /lgl-account-guest -->

<?php
    return; // Stop here for guests
endif;

// ─── Authenticated user ───────────────────────────────────────────────────────
$current_user = wp_get_current_user();
$logout_url   = wp_logout_url(home_url('/'));
$current_url  = get_permalink();
$options      = get_option('lgl_settings', array());

// Account update feedback (from AJAX, passed via query string)
$update_msg  = '';
$update_type = '';
if (isset($_GET['lgl_updated'])) {
    if ($_GET['lgl_updated'] === '1') {
        $update_msg  = __('Your account details have been updated successfully.', 'lgl-shortcodes');
        $update_type = 'success';
    } elseif ($_GET['lgl_updated'] === 'error') {
        $update_msg  = __('There was a problem updating your account. Please try again.', 'lgl-shortcodes');
        $update_type = 'error';
    }
}
?>

<div class="lgl-account-wrap lgl-account-member">

    <!-- SIDEBAR NAV -->
    <nav class="lgl-account-nav" aria-label="<?php esc_attr_e('Account navigation', 'lgl-shortcodes'); ?>">

        <?php
        $nav_items = array(
            'dashboard'      => array(
                'label' => __('Dashboard', 'lgl-shortcodes'),
                'icon'  => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>',
            ),
            'wishlist'       => array(
                'label' => __('Wishlist', 'lgl-shortcodes'),
                'icon'  => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>',
            ),
            'account-details' => array(
                'label' => __('Account Details', 'lgl-shortcodes'),
                'icon'  => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
            ),
        );

        foreach ($nav_items as $tab_key => $item) {
            $is_active = ($active_tab === $tab_key);
            $tab_url   = add_query_arg('lgl_account_tab', $tab_key, $current_url);
        ?>
            <a href="<?php echo esc_url($tab_url); ?>"
                class="lgl-account-nav__item <?php echo $is_active ? 'lgl-account-nav__item--active' : ''; ?>"
                aria-current="<?php echo $is_active ? 'page' : 'false'; ?>">
                <?php echo $item['icon']; // Already escaped SVG markup 
                ?>
                <span><?php echo esc_html($item['label']); ?></span>
            </a>
        <?php
        }
        ?>

        <a href="<?php echo esc_url($logout_url); ?>"
            class="lgl-account-nav__item lgl-account-nav__item--logout">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                <polyline points="16 17 21 12 16 7" />
                <line x1="21" y1="12" x2="9" y2="12" />
            </svg>
            <span><?php esc_html_e('Log Out', 'lgl-shortcodes'); ?></span>
        </a>

    </nav><!-- /lgl-account-nav -->

    <!-- MAIN CONTENT AREA -->
    <div class="lgl-account-content">

        <?php if ($update_msg && $active_tab === 'account-details') : ?>
            <div class="lgl-account-notice lgl-account-notice--<?php echo esc_attr($update_type); ?>">
                <?php echo esc_html($update_msg); ?>
            </div>
        <?php endif; ?>

        <!-- ─── DASHBOARD TAB ─────────────────────────────────────── -->
        <?php if ($active_tab === 'dashboard') : ?>
            <div class="lgl-account-panel" id="lgl-tab-dashboard">
                <h2 class="lgl-account-panel__title">
                    <?php
                    printf(
                        /* translators: %s: display name */
                        esc_html__('Hello, %s', 'lgl-shortcodes'),
                        esc_html($current_user->display_name)
                    );
                    ?>
                </h2>
                <p class="lgl-account-panel__intro">
                    <?php esc_html_e('From your account dashboard you can view your saved wishlist, manage your account details, and update your password.', 'lgl-shortcodes'); ?>
                </p>

                <div class="lgl-dashboard-cards">

                    <a href="<?php echo esc_url(add_query_arg('lgl_account_tab', 'wishlist', $current_url)); ?>" class="lgl-dashboard-card">
                        <div class="lgl-dashboard-card__icon">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
                            </svg>
                        </div>
                        <div class="lgl-dashboard-card__body">
                            <h3><?php esc_html_e('Wishlist', 'lgl-shortcodes'); ?></h3>
                            <p>
                                <?php
                                $wishlist_count = count((array) get_user_meta($current_user->ID, 'lgl_wishlists', true));
                                printf(
                                    /* translators: %d: count */
                                    esc_html(_n('%d saved vehicle', '%d saved vehicles', $wishlist_count, 'lgl-shortcodes')),
                                    (int) $wishlist_count
                                );
                                ?>
                            </p>
                        </div>
                    </a>

                    <a href="<?php echo esc_url(add_query_arg('lgl_account_tab', 'account-details', $current_url)); ?>" class="lgl-dashboard-card">
                        <div class="lgl-dashboard-card__icon">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                                <circle cx="12" cy="7" r="4" />
                            </svg>
                        </div>
                        <div class="lgl-dashboard-card__body">
                            <h3><?php esc_html_e('Account Details', 'lgl-shortcodes'); ?></h3>
                            <p><?php echo esc_html($current_user->user_email); ?></p>
                        </div>
                    </a>

                </div>
            </div>

            <!-- ─── WISHLIST TAB ──────────────────────────────────────── -->
        <?php elseif ($active_tab === 'wishlist') : ?>
            <div class="lgl-account-panel" id="lgl-tab-wishlist">
                <h2 class="lgl-account-panel__title"><?php esc_html_e('My Wishlist', 'lgl-shortcodes'); ?></h2>
                <?php echo do_shortcode('[lgl_wishlist]'); ?>
            </div>

            <!-- ─── ACCOUNT DETAILS TAB ──────────────────────────────── -->
        <?php elseif ($active_tab === 'account-details') : ?>
            <div class="lgl-account-panel" id="lgl-tab-account-details">
                <h2 class="lgl-account-panel__title"><?php esc_html_e('Account Details', 'lgl-shortcodes'); ?></h2>

                <form class="lgl-account-form" id="lgl-account-details-form" method="post" action="">
                    <?php wp_nonce_field('lgl_update_account_action', 'lgl_update_account_nonce'); ?>
                    <input type="hidden" name="action" value="lgl_update_account" />

                    <fieldset class="lgl-fieldset">
                        <legend class="lgl-fieldset__legend"><?php esc_html_e('Personal Information', 'lgl-shortcodes'); ?></legend>

                        <div class="lgl-form-row-split">
                            <div class="lgl-form-group">
                                <label for="lgl-account-first"><?php esc_html_e('First Name', 'lgl-shortcodes'); ?></label>
                                <input type="text"
                                    id="lgl-account-first"
                                    name="lgl_first_name"
                                    value="<?php echo esc_attr($current_user->first_name); ?>"
                                    required />
                            </div>
                            <div class="lgl-form-group">
                                <label for="lgl-account-last"><?php esc_html_e('Last Name', 'lgl-shortcodes'); ?></label>
                                <input type="text"
                                    id="lgl-account-last"
                                    name="lgl_last_name"
                                    value="<?php echo esc_attr($current_user->last_name); ?>"
                                    required />
                            </div>
                        </div>

                        <div class="lgl-form-group">
                            <label for="lgl-account-email"><?php esc_html_e('Email Address', 'lgl-shortcodes'); ?></label>
                            <input type="email"
                                id="lgl-account-email"
                                name="lgl_email"
                                value="<?php echo esc_attr($current_user->user_email); ?>"
                                autocomplete="email"
                                required />
                        </div>
                    </fieldset>

                    <fieldset class="lgl-fieldset">
                        <legend class="lgl-fieldset__legend">
                            <?php esc_html_e('Change Password', 'lgl-shortcodes'); ?>
                            <span class="lgl-fieldset__legend-hint"><?php esc_html_e('(leave blank to keep current password)', 'lgl-shortcodes'); ?></span>
                        </legend>

                        <div class="lgl-form-group">
                            <label for="lgl-account-current-pass"><?php esc_html_e('Current Password', 'lgl-shortcodes'); ?></label>
                            <input type="password"
                                id="lgl-account-current-pass"
                                name="lgl_current_password"
                                autocomplete="current-password" />
                        </div>

                        <div class="lgl-form-row-split">
                            <div class="lgl-form-group">
                                <label for="lgl-account-new-pass"><?php esc_html_e('New Password', 'lgl-shortcodes'); ?></label>
                                <input type="password"
                                    id="lgl-account-new-pass"
                                    name="lgl_new_password"
                                    autocomplete="new-password" />
                            </div>
                            <div class="lgl-form-group">
                                <label for="lgl-account-new-pass2"><?php esc_html_e('Confirm New Password', 'lgl-shortcodes'); ?></label>
                                <input type="password"
                                    id="lgl-account-new-pass2"
                                    name="lgl_new_password2"
                                    autocomplete="new-password" />
                            </div>
                        </div>
                    </fieldset>

                    <div class="lgl-form-actions">
                        <button type="submit" class="lgl-btn lgl-btn--primary" id="lgl-account-save-btn">
                            <?php esc_html_e('Save Changes', 'lgl-shortcodes'); ?>
                        </button>
                        <span class="lgl-account-saving" id="lgl-account-saving" style="display:none;">
                            <?php esc_html_e('Saving…', 'lgl-shortcodes'); ?>
                        </span>
                    </div>

                    <div id="lgl-account-form-msg" class="lgl-account-notice" style="display:none;" role="alert" aria-live="polite"></div>

                </form>
            </div>

        <?php endif; ?>

    </div><!-- /lgl-account-content -->

</div><!-- /lgl-account-member -->

<script type="text/javascript">
    jQuery(document).ready(function($) {

        // ── Auth tab switching (guest view) ──────────────────────────────────────
        $('.lgl-auth-tabs .lgl-nav-item').on('click', function(e) {
            e.preventDefault();
            var target = $(this).attr('href');
            $('.lgl-auth-tabs .lgl-nav-item').removeClass('lgl-is-active');
            $('.lgl-auth-panel').removeClass('lgl-is-active');
            $(this).addClass('lgl-is-active');
            $(target).addClass('lgl-is-active');
        });

        // ── AJAX Account Details form ────────────────────────────────────────────
        $('#lgl-account-details-form').on('submit', function(e) {
            e.preventDefault();

            var $form = $(this);
            var $btn = $('#lgl-account-save-btn');
            var $saving = $('#lgl-account-saving');
            var $msg = $('#lgl-account-form-msg');

            $btn.prop('disabled', true).hide();
            $saving.show();
            $msg.hide().removeClass('lgl-account-notice--success lgl-account-notice--error');

            $.ajax({
                url: lgl_ajax_obj.ajax_url,
                type: 'POST',
                data: {
                    action: 'lgl_update_account',
                    nonce: lgl_ajax_obj.nonce,
                    first_name: $form.find('[name="lgl_first_name"]').val(),
                    last_name: $form.find('[name="lgl_last_name"]').val(),
                    email: $form.find('[name="lgl_email"]').val(),
                    current_password: $form.find('[name="lgl_current_password"]').val(),
                    new_password: $form.find('[name="lgl_new_password"]').val(),
                    new_password2: $form.find('[name="lgl_new_password2"]').val(),
                },
                success: function(response) {
                    if (response.success) {
                        $msg.addClass('lgl-account-notice--success').text(response.data.message).show();
                        // Clear password fields on success
                        $form.find('[name="lgl_current_password"], [name="lgl_new_password"], [name="lgl_new_password2"]').val('');
                    } else {
                        $msg.addClass('lgl-account-notice--error').text(response.data.message).show();
                    }
                },
                error: function() {
                    $msg.addClass('lgl-account-notice--error')
                        .text('<?php echo esc_js(__('A server error occurred. Please try again.', 'lgl-shortcodes')); ?>')
                        .show();
                },
                complete: function() {
                    $btn.prop('disabled', false).show();
                    $saving.hide();
                }
            });
        });

    });
</script>