<?php

/**
 * Template Name: LGL Custom Single Template
 * Template Post Type: caravan, post, page
 * Description: A customized single post view controlled strictly by the LGL Shortcodes plugin.
 * Author: Digitally Disruptive - Donald Raymundo
 */

if (! defined('ABSPATH')) {
    exit; // Prevent direct access to the file.
}

// Load standard WordPress header
get_header();

$post_id = get_the_ID();
$post_type = get_post_type();
// Retrieve global LGL settings payload
$lgl_options = get_option('lgl_settings', array());
// Feature Toggles
$disable_wishlist = !empty($lgl_options['disable_wishlist']);
$disable_compare  = !empty($lgl_options['disable_compare']);
// Button URLs (fallback to hash links if empty)
$url_finance = !empty($lgl_options['url_finance_calc']) ? esc_url($lgl_options['url_finance_calc']) : '#lgl-tab-overview';
$url_enquire = !empty($lgl_options['url_enquire_now']) ? esc_url($lgl_options['url_enquire_now']) : '#lgl-tab-overview';
$url_reserve = !empty($lgl_options['url_reserve_now']) ? esc_url($lgl_options['url_reserve_now']) : '#lgl-tab-overview';

// Contact Payload (With graceful fallbacks to prevent empty UI)
$contact_phone    = !empty($lgl_options['contact_phone']) ? sanitize_text_field($lgl_options['contact_phone']) : '01978 810091';
$phone_link       = preg_replace('/\D+/', '', $contact_phone);

$contact_whatsapp = !empty($lgl_options['contact_whatsapp']) ? sanitize_text_field($lgl_options['contact_whatsapp']) : '01978 810091';
$whatsapp_link    = preg_replace('/\D+/', '', $contact_whatsapp);

$contact_email    = !empty($lgl_options['contact_email']) ? sanitize_email($lgl_options['contact_email']) : 'sales@clwydcaravans.com';

$contact_address  = !empty($lgl_options['contact_address']) ? sanitize_textarea_field($lgl_options['contact_address']) : 'Clwyd Caravans';
$location_url     = 'https://www.google.com/maps/search/?api=1&query=' . urlencode($contact_address);
?>

<pre style="display: none">
    <?php var_dump(get_post_meta($post_id)); ?>
</pre>

<main id="lgl-primary" class="lgl-site-main single-lgl">
    <div class="lgl-holder">
        <?php

        $gallery = LGL_Shortcodes::convertStringToIntArray(get_post_meta($post_id, '_listing_gallery_ids', true));
        $price = get_post_meta($post_id, 'price', true);
        $berth = get_post_meta($post_id, 'berth', true);
        $mileage = get_post_meta($post_id, 'mileage', true);
        $axles = get_post_meta($post_id, 'axles', true);
        $year = get_post_meta($post_id, 'year', true);
        $condition = get_post_meta($post_id, 'condition', true);
        $feature = get_post_meta($post_id, 'feature', true);
        $sub_title = get_post_meta($post_id, 'sub_title', true);

        ?>
        <article <?php post_class('lgl-post'); ?>>
            <div class="lgl-post--wrap">
                <div class="lgl-post--main">
                    <?php if (!empty($gallery)) { ?>
                        <div class="lgl-post--gallery js-gallery-slider">
                            <div class="lgl-gallery-slider lgl-slider-for js-gallery-slider-for">
                                <?php if (has_post_thumbnail()) { ?>
                                    <div class="lgl-slider-item-wrap">
                                        <a href="<?php echo get_the_post_thumbnail_url()  ?>" class="lgl-slider-item elementor-clickable" data-elementor-lightbox-slideshow="lgl-gallery-car">
                                            <div class="lgl-cover-image">
                                                <?php the_post_thumbnail('full'); ?>
                                            </div>
                                        </a>
                                    </div>
                                <?php } ?>

                                <?php foreach ($gallery as $key => $item) { ?>
                                    <div class="lgl-slider-item-wrap">
                                        <a href="<?php echo esc_url(wp_get_attachment_image_url($item, 'full', false))  ?>" class="lgl-slider-item elementor-clickable" data-elementor-lightbox-slideshow="lgl-gallery-car">
                                            <div class="lgl-cover-image">
                                                <?php echo '<img src="' . esc_url(wp_get_attachment_image_url($item, 'full', false)) . '" alt="' . esc_html(get_the_title($item)) . '" />'; ?>
                                            </div>
                                        </a>
                                    </div>
                                <?php } ?>
                            </div>

                            <div class="lgl-gallery-slider lgl-slider-nav js-gallery-slider-nav">
                                <?php if (has_post_thumbnail()) { ?>
                                    <div class="lgl-slider-item-wrap">
                                        <div class="lgl-slider-item">
                                            <div class="lgl-cover-image">
                                                <?php the_post_thumbnail('full'); ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>

                                <?php foreach ($gallery as $key => $item) { ?>
                                    <div class="lgl-slider-item-wrap">
                                        <div class="lgl-slider-item">
                                            <div class="lgl-cover-image">
                                                <?php echo '<img src="' . esc_url(wp_get_attachment_image_url($item, 'medium', false)) . '" alt="' . esc_html(get_the_title($item)) . '" />'; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    <?php } else { ?>
                        <div class="lgl-post--thumbnail">
                            <div class="lgl-cover-image">
                                <?php
                                if (has_post_thumbnail()) {
                                    the_post_thumbnail('full');
                                }
                                ?>
                            </div>
                        </div>
                    <?php } ?>
                    <?php
                    $title = get_the_title();
                    echo '<h3>' . esc_html($title) . '</h3>';
                    ?>
                </div>

                <div class="lgl-post--sidebar">
                    <div class="lgl-sidebar-wrap">
                        <div class="lgl-sidebar-block lgl-sale-block">
                            <div class="lgl-sale-card">
                                <div class="lgl-sale-top">
                                    <div class="lgl-condition-tag">
                                        <?php echo esc_html($condition); ?>
                                    </div>
                                    <div class="lgl-sale-icon-btn">
                                        <a class="lgl-icon-btn lgl-vehicle-share-btn" href="#" data-url="<?php echo esc_url(get_permalink()); ?>" data-title="<?php echo esc_attr(get_the_title()); ?>">
                                            <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                                <path fill-rule="evenodd" clip-rule="evenodd" d="M16.3739 0.666304C15.4022 -0.608175 13.3404 0.0678549 13.3404 1.66094V3.2606C10.1969 3.22339 8.04656 4.30394 6.61418 5.81295C5.08297 7.42608 4.45317 9.44283 4.18925 10.8218C4.0573 11.5112 4.50954 12.0115 4.98483 12.1842C5.43888 12.3492 6.04559 12.2786 6.45048 11.8157C7.59965 10.5022 9.83227 8.669 13.3404 8.78867V10.8391C13.3404 12.4322 15.4022 13.1082 16.3739 11.8337L19.4938 7.74195C20.1678 6.85783 20.1678 5.64217 19.4938 4.75805L16.3739 0.666304ZM6.23436 9.67458C7.73869 8.36175 9.8981 7.12973 13.3404 7.12973H14.1833C14.6487 7.12973 15.0259 7.50086 15.0259 7.95864L15.0258 10.8391L18.1455 6.74732C18.3703 6.45261 18.3703 6.04739 18.1455 5.75268L15.0258 1.66094V4.08948C15.0258 4.54725 14.6485 4.91834 14.1831 4.91834H13.3404C9.54877 4.91834 7.84598 6.94428 7.84598 6.94428C7.06205 7.77015 6.55755 8.75167 6.23436 9.67458Z" />
                                                <path d="M5.83398 0.836594H3.33398C1.95328 0.836594 0.833984 1.95588 0.833984 3.33659V16.6699C0.833984 18.0507 1.95328 19.1699 3.33398 19.1699H16.6673C18.0481 19.1699 19.1673 18.0507 19.1673 16.6699V14.1699C19.1673 13.7097 18.7942 13.3366 18.334 13.3366C17.8737 13.3366 17.5006 13.7097 17.5006 14.1699V16.6699C17.5006 17.1302 17.1276 17.5033 16.6673 17.5033H3.33398C2.87375 17.5033 2.50065 17.1302 2.50065 16.6699V3.33659C2.50065 2.87635 2.87375 2.50326 3.33398 2.50326H5.83398C6.29422 2.50326 6.66732 2.13016 6.66732 1.66993C6.66732 1.20969 6.29422 0.836594 5.83398 0.836594Z" />
                                            </svg>
                                        </a>

                                        <?php
                                        if (!$disable_wishlist) {
                                            include LGL_SHORTCODES_PATH . 'templates/partials/lgl-button-wishlist.php';
                                        }
                                        ?>

                                        <?php if (!$disable_compare) { ?>
                                            <a class="lgl-icon-btn lgl-vehicle-compare-btn" href="#" data-id="<?php echo esc_attr($post_id); ?>">
                                                <svg width="25" height="25" viewBox="0 0 25 25" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M15.75 10.9375L17.3125 12.5L22.6875 7.10938L17.1875 1.5625L15.625 3.125L18.4375 5.9375H3.125V8.125H18.4688L15.75 10.9375ZM9.15625 14.0625L7.59375 12.5L2.21875 17.9688L7.67187 23.4375L9.23437 21.875L6.40625 19.0625H21.875V16.875H6.40625L9.15625 14.0625Z" />
                                                </svg>
                                            </a>
                                        <?php } ?>
                                    </div>
                                </div>

                                <div class="lgl-sale-price">
                                    <?php
                                    if (!empty($price)) {
                                        echo '$' . number_format($price, 0);
                                    } else {
                                        echo '<span class="lgl-call-price">' . esc_html__('Call for price', 'lgl') . '</span>';
                                    }
                                    ?>
                                </div>

                                <?php if (!empty($sub_title)) { ?>
                                    <div class="lgl-sale-finance">
                                        <div class="lgl-finance-label"><?php echo esc_html__($sub_title, 'lgl'); ?></div>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="lgl-sale-meta-bottom">
                                <?php
                                include LGL_SHORTCODES_PATH . 'templates/partials/lgl-meta-short.php';
                                ?>
                                <div class="lgl-post--part-exchange">
                                    <a href="#">
                                        <?php echo esc_html__('Part-exchange available', 'lgl'); ?>
                                    </a>
                                </div>
                            </div>


                            <div class="lgl-btn-group">
                                <a class="lgl-btn lgl-btn-secondary" href="<?php echo $url_finance; ?>">
                                    <?php echo esc_html__('FINANCE CALCULATOR', 'lgl'); ?>
                                </a>

                                <a class="lgl-btn lgl-btn-accent" href="<?php echo $url_enquire; ?>">
                                    <?php echo esc_html__('ENQUIRE NOW', 'lgl'); ?>
                                </a>

                                <a class="lgl-btn lgl-btn-outline" href="<?php echo $url_reserve; ?>">
                                    <?php echo esc_html__('RESERVE NOW', 'lgl'); ?>
                                </a>
                            </div>

                            <div class="lgl-contact-info">
                                <h4 class="lgl-contact-title">Contact Information</h4>
                                <ul class="lgl-contact-list">

                                    <?php if ($contact_phone) { ?>
                                        <li class="lgl-contact-item">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="17.532" height="17.532" viewBox="0 0 17.532 17.532">
                                                <path id="download_1_" data-name="download (1)" d="M5.153.747A1.246,1.246,0,0,0,3.672.022L.922.772A1.254,1.254,0,0,0,0,1.978a14,14,0,0,0,14,14,1.254,1.254,0,0,0,1.206-.922l.75-2.75a1.246,1.246,0,0,0-.725-1.481l-3-1.25a1.246,1.246,0,0,0-1.447.362L9.521,11.478A10.561,10.561,0,0,1,4.5,6.456L6.04,5.2A1.247,1.247,0,0,0,6.4,3.75l-1.25-3Z" transform="translate(0.75 0.805)" fill="#001537" stroke="#f7faff" stroke-width="1.5" />
                                            </svg>
                                            <a href="tel:<?php echo esc_attr($phone_link); ?>">
                                                <?php echo esc_html($contact_phone); ?>
                                            </a>
                                        </li>
                                    <?php } ?>

                                    <?php if ($contact_whatsapp) { ?>
                                        <li class="lgl-contact-item">
                                            <span class="lgl-contact-icon">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16.209" viewBox="0 0 16 16.209">
                                                    <path id="Path_172" data-name="Path 172" d="M19.646,30.06a8,8,0,1,0-3.061-2.986L15.567,31.26Zm.425-10.748a.911.911,0,0,1,.62-.239h.244a.765.765,0,0,1,.719.5l.5,1.373a.4.4,0,0,1-.062.382l-.394.492a.688.688,0,0,0-.107.684,5.517,5.517,0,0,0,2.639,2.417.7.7,0,0,0,.8-.128l.436-.436a.4.4,0,0,1,.4-.1l1.322.422a.767.767,0,0,1,.534.73v.336a.928.928,0,0,1-.272.656c-1.287,1.271-3.423.339-4.807-.51A8.039,8.039,0,0,1,20.13,23.5c-1.565-2.362-.614-3.686-.06-4.192Z" transform="translate(-15.5 -15.05)" fill="#25d366" />
                                                </svg>
                                            </span>
                                            <a target="_blank" rel="nofollow noopener" href="https://wa.me/<?php echo esc_attr($whatsapp_link); ?>">
                                                <?php echo esc_html($contact_whatsapp); ?>
                                            </a>
                                        </li>
                                    <?php } ?>

                                    <?php if ($contact_email) { ?>
                                        <li class="lgl-contact-item">
                                            <span class="lgl-contact-icon">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16.337" height="16.337" viewBox="0 0 16.337 16.337">
                                                    <path id="download" d="M8.15,1.538a.032.032,0,0,1,.038,0L14.7,6.19a.256.256,0,0,1,.108.207v.434L9.3,11.35a1.785,1.785,0,0,1-2.269,0l-5.5-4.518V6.4A.247.247,0,0,1,1.64,6.19ZM1.532,8.813l4.531,3.721a3.319,3.319,0,0,0,4.212,0l4.531-3.721V14.55a.256.256,0,0,1-.255.255H1.787a.256.256,0,0,1-.255-.255ZM8.169,0a1.573,1.573,0,0,0-.909.29L.75,4.943A1.782,1.782,0,0,0,0,6.4V14.55a1.788,1.788,0,0,0,1.787,1.787H14.55a1.788,1.788,0,0,0,1.787-1.787V6.4a1.787,1.787,0,0,0-.747-1.455L9.078.29A1.573,1.573,0,0,0,8.169,0Z" fill="#001537" />
                                                </svg>
                                            </span>
                                            <a href="mailto:<?php echo antispambot(esc_attr($contact_email)); ?>">
                                                <?php echo esc_html(antispambot($contact_email)); ?>
                                            </a>
                                        </li>
                                    <?php } ?>

                                    <?php if ($contact_address) { ?>
                                        <li class="lgl-contact-item">
                                            <span class="lgl-contact-icon">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="15.903" height="19.379" viewBox="0 0 15.903 19.379">
                                                    <path id="Path_1154" data-name="Path 1154" d="M12.952,3A6.952,6.952,0,0,0,6,9.952a5.6,5.6,0,0,0,1.3,3.91l5.648,6.517L18.6,13.862a5.6,5.6,0,0,0,1.3-3.91A6.952,6.952,0,0,0,12.952,3Z" transform="translate(-5 -2)" fill="none" stroke="#000" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
                                                </svg>
                                            </span>
                                            <a target="_blank" rel="noopener noreferrer" href="<?php echo esc_url($location_url); ?>">
                                                <?php echo nl2br(esc_html($contact_address)); ?>
                                            </a>
                                        </li>
                                    <?php } ?>

                                </ul>
                            </div>
                        </div>
                    </div>
                </div><!-- /.lgl-post--sidebar -->
            </div>
            <div class="lgl-post--tabs lgl-tabs lgl-tabs-js">
                <div class="lgl-tabs--tbnav">
                    <a href="#bt_panel_overview" class="lgl-nav-item lgl-is-active">
                        <span><?php echo esc_html__('Key Information', 'lgl'); ?></span>
                    </a>
                    <?php if (!empty(get_the_content())) { ?>
                        <a href="#bt_panel_desc" class="lgl-nav-item">
                            <span><?php echo esc_html__('Description', 'lgl'); ?></span>
                        </a>
                    <?php } ?>
                    <?php if (!empty($feature)) { ?>
                        <a href="#bt_panel_interior" class="lgl-nav-item">
                            <span><?php echo esc_html__('Features', 'lgl'); ?></span>
                        </a>
                    <?php } ?>

                    <?php if (!empty($floor_plan)) { ?>
                        <a href="#bt_panel_floorplan" class="lgl-nav-item">
                            <span><?php echo esc_html__('Floor plan', 'lgl'); ?></span>
                        </a>
                    <?php } ?>
                    <?php if (!empty($warranty)) { ?>
                        <a href="#bt_panel_warranty" class="lgl-nav-item">
                            <span><?php echo esc_html__('Warranty', 'lgl'); ?></span>
                        </a>
                    <?php } ?>
                </div>
                <div class="lgl-tabs--tbpanel">
                    <div id="bt_panel_overview" class="lgl-panel-item lgl-panel-overview lgl-is-active">
                        <div class="lgl-panel-item--inner">
                            <h3 class="lgl-title-ss">
                                <?php echo '<span>' . esc_html__('Key Information', 'lgl') . '</span>'; ?>
                            </h3>
                            <?php
                            include LGL_SHORTCODES_PATH . 'templates/partials/lgl-meta.php';
                            ?>
                        </div>
                    </div>
                    <?php if (!empty(get_the_content())) { ?>
                        <div id="bt_panel_desc" class="lgl-panel-item lgl-panel-desc">
                            <div class="lgl-panel-item--inner">
                                <h3 class="lgl-title-ss">
                                    <?php echo '<span>' . esc_html__('Description', 'lgl') . '</span>'; ?>
                                </h3>
                                <div class="lgl-content-ss">
                                    <?php the_content(); ?>
                                </div>
                            </div>
                        </div>
                    <?php } ?>

                    <?php if (!empty($feature)) { ?>
                        <div id="bt_panel_interior" class="lgl-panel-item lgl-panel-interior">
                            <div class="lgl-panel-item--inner">
                                <h3 class="lgl-title-ss">
                                    <span><?php echo esc_html__('Features', 'lgl'); ?></span>
                                </h3>
                                <div class="lgl-content-ss">
                                    <?php echo wp_kses_post($feature); ?>
                                </div>
                            </div>
                        </div>
                    <?php } ?>

                    <?php if (!empty($exterior_features)) { ?>
                        <div id="bt_panel_exterior" class="lgl-panel-item lgl-panel-exterior">
                            <div class="lgl-panel-item--inner">
                                <h3 class="lgl-title-ss">
                                    <span><?php echo esc_html__('Exterior features', 'lgl'); ?></span>
                                </h3>
                                <div class="lgl-content-ss">
                                    <?php echo wp_kses_post($exterior_features); ?>
                                </div>
                            </div>
                        </div>
                    <?php } ?>

                    <?php if (!empty($floor_plan)) { ?>
                        <div id="bt_panel_floorplan" class="lgl-panel-item lgl-panel-floorplan">
                            <div class="lgl-panel-item--inner">
                                <h3 class="lgl-title-ss">
                                    <span><?php echo esc_html__('Floor plan', 'lgl'); ?></span>
                                </h3>
                                <div class="lgl-content-ss">
                                    <?php echo wp_kses_post($floor_plan); ?>
                                </div>
                            </div>
                        </div>
                    <?php } ?>

                    <?php if (!empty($warranty)) { ?>
                        <div id="bt_panel_warranty" class="lgl-panel-item lgl-panel-warranty">
                            <div class="lgl-panel-item--inner">
                                <h3 class="lgl-title-ss">
                                    <span><?php echo esc_html__('Warranty', 'lgl'); ?></span>
                                </h3>
                                <div class="lgl-content-ss">
                                    <?php echo wp_kses_post($warranty); ?>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </article>
        <?= do_shortcode('[lgl_related_vehicles post_type="' . $post_type . '"]') ?>

    </div>
    <div class="lgl-additional-content">
        <?php
        $options = get_option('lgl_settings', array());
        if (isset($options['single_vehicle_content']) && !empty($options['single_vehicle_content'])) {
            echo do_shortcode($options['single_vehicle_content']);
        }
        ?>
    </div>
</main>

<?php
// Load standard WordPress footer
get_footer();
