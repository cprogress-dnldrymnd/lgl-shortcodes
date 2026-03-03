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
?>

<pre>
    <?php var_dump(get_post_meta(get_the_ID())); ?>
</pre>

<main id="lgl-primary" class="lgl-site-main">
    <div class="lgl-holder">
        <?php
        $interior_features = get_field('car_interior_features');
        $exterior_features = get_field('car_exterior_features'); // <-- make sure your ACF field name matches this
        $floor_plan        = get_field('car_floor_plan');
        $warranty          = get_field('car_warranty');
        $berth_terms = get_the_terms(get_the_ID(), 'car_berth');
        $berth_value = (!empty($berth_terms) && !is_wp_error($berth_terms)) ? $berth_terms[0]->name : '';
        $mileage = get_field('car_mileage');
        $year = get_field('car_year');
        $price = get_field('car_price');
        $location = get_field('car_location');
        $map_link = get_field('car_map_link');
        $map_iframe = get_field('car_map_iframe');
        $features = get_field('car_features');
        $dealer = get_field('car_dealer');
        $body_term = get_the_terms(get_the_ID(), 'car_body');
        $body = !empty($body_term) ? array_pop($body_term) : '';
        $condition_term = get_the_terms(get_the_ID(), 'car_condition');
        $condition = !empty($condition_term) ? array_pop($condition_term) : '';
        $make_term = get_the_terms(get_the_ID(), 'car_make');
        $make = !empty($make_term) ? array_pop($make_term) : '';
        $model_term = get_the_terms(get_the_ID(), 'car_model');
        $model = !empty($model_term) ? array_pop($model_term) : '';
        $fuel_type_term = get_the_terms(get_the_ID(), 'car_fuel_type');
        $fuel_type = !empty($fuel_type_term) ? array_pop($fuel_type_term) : '';
        $transmission_term = get_the_terms(get_the_ID(), 'car_transmission');
        $transmission = !empty($transmission_term) ? array_pop($transmission_term) : '';
        $door_term = get_the_terms(get_the_ID(), 'car_door');
        $door = !empty($door_term) ? array_pop($door_term) : '';
        $engine_term = get_the_terms(get_the_ID(), 'car_engine');
        $engine = !empty($engine_term) ? array_pop($engine_term) : '';
        $cylinder_term = get_the_terms(get_the_ID(), 'car_cylinder');
        $cylinder = !empty($cylinder_term) ? array_pop($cylinder_term) : '';
        $color_term = get_the_terms(get_the_ID(), 'car_color');
        $color_arr = array();
        if (!empty($color_term)) {
            foreach ($color_term as $color) {
                $color_arr[] = $color->name;
            }
        }



        $gallery = get_post_meta(get_the_ID(), '_listing_gallery_ids', true);


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
                    <?php
                    // --- Finance (simple example) ---
                    $price_num = !empty($price) ? (float) $price : 0;

                    // You can tweak these defaults or pull them from ACF Options later
                    $finance_rate_annual = 0.099; // 9.9% APR
                    $finance_years = 5;           // 5 years
                    $finance_down = 0;            // down payment

                    $loan = max(0, $price_num - $finance_down);
                    $months = max(1, (int) $finance_years * 12);
                    $r = $finance_rate_annual / 12;

                    $monthly = 0;
                    if ($loan > 0) {
                        // amortization formula
                        $monthly = ($r == 0) ? ($loan / $months) : ($loan * $r) / (1 - pow(1 + $r, -$months));
                    }
                    ?>

                    <div class="lgl-sidebar-wrap">
                        <div class="lgl-sidebar-block lgl-sale-block">

                            <div class="lgl-sale-card">

                                <div class="lgl-sale-top">
                                    <?php the_terms(get_the_ID(), 'car_condition', '<div class="lgl-sale-body">', ', ', '</div>'); ?>

                                    <div class="lgl-sale-icon-btn">
                                        <a class="lgl-icon-btn lgl-car-share-btn" href="#">
                                            <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                                <path fill-rule="evenodd" clip-rule="evenodd" d="M16.3739 0.666304C15.4022 -0.608175 13.3404 0.0678549 13.3404 1.66094V3.2606C10.1969 3.22339 8.04656 4.30394 6.61418 5.81295C5.08297 7.42608 4.45317 9.44283 4.18925 10.8218C4.0573 11.5112 4.50954 12.0115 4.98483 12.1842C5.43888 12.3492 6.04559 12.2786 6.45048 11.8157C7.59965 10.5022 9.83227 8.669 13.3404 8.78867V10.8391C13.3404 12.4322 15.4022 13.1082 16.3739 11.8337L19.4938 7.74195C20.1678 6.85783 20.1678 5.64217 19.4938 4.75805L16.3739 0.666304ZM6.23436 9.67458C7.73869 8.36175 9.8981 7.12973 13.3404 7.12973H14.1833C14.6487 7.12973 15.0259 7.50086 15.0259 7.95864L15.0258 10.8391L18.1455 6.74732C18.3703 6.45261 18.3703 6.04739 18.1455 5.75268L15.0258 1.66094V4.08948C15.0258 4.54725 14.6485 4.91834 14.1831 4.91834H13.3404C9.54877 4.91834 7.84598 6.94428 7.84598 6.94428C7.06205 7.77015 6.55755 8.75167 6.23436 9.67458Z" />
                                                <path d="M5.83398 0.836594H3.33398C1.95328 0.836594 0.833984 1.95588 0.833984 3.33659V16.6699C0.833984 18.0507 1.95328 19.1699 3.33398 19.1699H16.6673C18.0481 19.1699 19.1673 18.0507 19.1673 16.6699V14.1699C19.1673 13.7097 18.7942 13.3366 18.334 13.3366C17.8737 13.3366 17.5006 13.7097 17.5006 14.1699V16.6699C17.5006 17.1302 17.1276 17.5033 16.6673 17.5033H3.33398C2.87375 17.5033 2.50065 17.1302 2.50065 16.6699V3.33659C2.50065 2.87635 2.87375 2.50326 3.33398 2.50326H5.83398C6.29422 2.50326 6.66732 2.13016 6.66732 1.66993C6.66732 1.20969 6.29422 0.836594 5.83398 0.836594Z" />
                                            </svg>
                                        </a>


                                        <a class="lgl-icon-btn lgl-car-wishlist-btn" href="#" data-id="<?php echo get_the_ID(); ?>">
                                            <svg width="25" height="25" viewBox="0 0 25 25" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                                <path fill-rule="evenodd" clip-rule="evenodd" d="M14.9916 18.8679C14.3066 19.4224 13.4266 19.7282 12.5129 19.7282C11.6004 19.7282 10.7179 19.4236 10.0054 18.8512C5.51289 15.2466 2.65289 13.3342 2.50414 9.41186C2.34789 5.26103 7.12289 3.74375 10.0504 7.15438C10.6429 7.84341 11.5329 8.2385 12.4929 8.2385C13.4616 8.2385 14.3579 7.83864 14.9516 7.14128C17.8154 3.78539 22.7179 5.21462 22.4941 9.53324C22.2941 13.3747 19.3241 15.3585 14.9916 18.8679ZM12.9841 5.72634C12.8616 5.87033 12.6766 5.94292 12.4929 5.94292C12.3129 5.94292 12.1341 5.87271 12.0141 5.73348C7.58539 0.574693 -0.234601 3.14396 0.0053982 9.49159C0.196648 14.5433 3.95664 17.0471 8.38414 20.5994C9.56788 21.549 11.0404 22.0238 12.5129 22.0238C13.9891 22.0238 15.4641 21.5466 16.6454 20.5898C21.0241 17.0424 24.7366 14.5552 24.9904 9.64154C25.3279 3.1523 17.4016 0.546134 12.9841 5.72634Z" />
                                            </svg>
                                        </a>


                                        <a class="lgl-icon-btn lgl-car-compare-btn" href="#" data-id="<?php echo get_the_ID(); ?>">
                                            <svg width="25" height="25" viewBox="0 0 25 25" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M15.75 10.9375L17.3125 12.5L22.6875 7.10938L17.1875 1.5625L15.625 3.125L18.4375 5.9375H3.125V8.125H18.4688L15.75 10.9375ZM9.15625 14.0625L7.59375 12.5L2.21875 17.9688L7.67187 23.4375L9.23437 21.875L6.40625 19.0625H21.875V16.875H6.40625L9.15625 14.0625Z" />
                                            </svg>
                                        </a>

                                    </div>
                                </div>

                                <div class="lgl-sale-price">
                                    <?php
                                    if (!empty($price)) {
                                        echo '$' . number_format($price, 0);
                                    } else {
                                        echo '<span class="lgl-call-price">' . esc_html__('Call for price', 'autoart') . '</span>';
                                    }
                                    ?>
                                </div>

                                <?php if (!empty($price) && !empty($monthly) && $monthly > 0) { ?>
                                    <div class="lgl-sale-finance">
                                        <div class="lgl-finance-label"><?php echo esc_html__('FINANCE FROM', 'autoart'); ?></div>
                                    </div>
                                <?php } ?>
                            </div><!-- /.lgl-sale-card -->

                            <?php
                            // reuse these values (already in your file)
                            ?>

                            <div class="lgl-sale-meta-bottom">
                                <div class="lgl-post--meta">
                                    <div class="lgl-post--meta-row">

                                        <div class="lgl-post--meta-col">
                                            <div class="lgl-post--meta-item lgl-post--mileage">

                                                <svg xmlns="http://www.w3.org/2000/svg" width="17" height="15.692" viewBox="0 0 17 15.692">
                                                    <g id="Icon_ionic-ios-calendar" data-name="Icon ionic-ios-calendar" transform="translate(0 0)">
                                                        <path id="Path_119" data-name="Path 119" d="M18.74,6.75H17.106v.981a.328.328,0,0,1-.327.327h-.654a.328.328,0,0,1-.327-.327V6.75H7.952v.981a.328.328,0,0,1-.327.327H6.971a.328.328,0,0,1-.327-.327V6.75H5.01A1.639,1.639,0,0,0,3.375,8.385V19.5A1.639,1.639,0,0,0,5.01,21.135H18.74A1.639,1.639,0,0,0,20.375,19.5V8.385A1.639,1.639,0,0,0,18.74,6.75Zm.327,12.26a.82.82,0,0,1-.817.817H5.5a.82.82,0,0,1-.817-.817V11.654a.328.328,0,0,1,.327-.327H18.74a.328.328,0,0,1,.327.327Z" transform="translate(-3.375 -5.442)" fill="#00235d" />
                                                        <path id="Path_120" data-name="Path 120" d="M10.308,4.827A.328.328,0,0,0,9.981,4.5H9.327A.328.328,0,0,0,9,4.827v.981h1.308Z" transform="translate(-5.731 -4.5)" fill="#00235d" />
                                                        <path id="Path_121" data-name="Path 121" d="M26.058,4.827a.328.328,0,0,0-.327-.327h-.654a.328.328,0,0,0-.327.327v.981h1.308Z" transform="translate(-12.327 -4.5)" fill="#00235d" />
                                                    </g>
                                                </svg>

                                                <span class="lgl-label"><?php echo esc_html__('Year', 'autoart'); ?></span>
                                                <span class="lgl-value"><?php echo !empty($year) ? esc_html($year) : esc_html__('N/A', 'autoart'); ?></span>
                                            </div>
                                        </div>

                                        <div class="lgl-post--meta-col">
                                            <div class="lgl-post--meta-item lgl-post--fuel-type">

                                                <svg xmlns="http://www.w3.org/2000/svg" width="14.871" height="16.995" viewBox="0 0 14.871 16.995">
                                                    <path id="Icon_fa-regular-user" data-name="Icon fa-regular-user" d="M10.091,4.249A2.656,2.656,0,1,0,7.436,6.9,2.656,2.656,0,0,0,10.091,4.249Zm-6.9,0A4.249,4.249,0,1,1,7.436,8.5,4.249,4.249,0,0,1,3.187,4.249ZM1.636,15.4h11.6a4.327,4.327,0,0,0-4.282-3.718H5.919A4.327,4.327,0,0,0,1.636,15.4ZM0,16.01a5.917,5.917,0,0,1,5.919-5.919H8.952a5.917,5.917,0,0,1,5.919,5.919.986.986,0,0,1-.986.986H.986A.986.986,0,0,1,0,16.01Z" fill="#00235d" />
                                                </svg>

                                                <span class="lgl-label"><?php echo esc_html__('Berth', 'autoart'); ?></span>
                                                <span class="lgl-value">
                                                    <?php
                                                    if (!empty($berth_terms) && !is_wp_error($berth_terms)) {
                                                        $t = reset($berth_terms);
                                                        echo esc_html($t->name);
                                                    } else {
                                                        echo esc_html__('N/A', 'autoart');
                                                    }
                                                    ?>
                                                </span>
                                            </div>
                                        </div>

                                        <div class="lgl-post--meta-col">
                                            <div class="lgl-post--meta-item lgl-post--transmission">

                                                <svg xmlns="http://www.w3.org/2000/svg" width="17.368" height="13.646" viewBox="0 0 17.368 13.646">
                                                    <g id="noun-mileage-4955087" transform="translate(-2 -5)">
                                                        <g id="Group_1562" data-name="Group 1562" transform="translate(2 5)">
                                                            <path id="Path_1150" data-name="Path 1150" d="M99.266,110.3h1.215a.62.62,0,1,0,0-1.241H99.266A7.409,7.409,0,0,1,101,104.879l.858.858a.62.62,0,0,0,.877-.877l-.858-.858a7.411,7.411,0,0,1,4.185-1.736v1.215a.62.62,0,1,0,1.241,0v-1.215A7.409,7.409,0,0,1,111.489,104l-.858.858a.62.62,0,0,0,.877.877l.858-.858a7.411,7.411,0,0,1,1.736,4.185h-1.215a.62.62,0,1,0,0,1.241H114.1a7.392,7.392,0,0,1-1.146,3.387.62.62,0,1,0,1.045.67,8.684,8.684,0,1,0-14.634,0,.62.62,0,1,0,1.045-.67A7.392,7.392,0,0,1,99.266,110.3Z" transform="translate(-98 -101)" fill="#00235d" fill-rule="evenodd" />
                                                            <path id="Path_1151" data-name="Path 1151" d="M111.2,114.753a1.863,1.863,0,1,0,1.074.621l1.988-3.443a.62.62,0,1,0-1.074-.62Zm-.34,1.21a.62.62,0,1,1-.62.62A.621.621,0,0,1,110.861,115.962Z" transform="translate(-102.177 -104.797)" fill="#00235d" fill-rule="evenodd" />
                                                        </g>
                                                    </g>
                                                </svg>

                                                <span class="lgl-label"><?php echo esc_html__('Mileage', 'autoart'); ?></span>
                                                <span class="lgl-value">
                                                    <?php echo !empty($mileage) ? esc_html(number_format((float)$mileage, 0)) . esc_html__(' km', 'autoart') : esc_html__('N/A', 'autoart'); ?>
                                                </span>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                                <div class="lgl-post--readmore-sidebar">
                                    <a href="#">
                                        <?php echo esc_html__('Part-exchange available', 'autoart'); ?>
                                    </a>
                                </div>
                            </div>

                            <!-- dealer block stays after -->
                            <?php
                            if (!empty($dealer)) {
                                $d_title = get_the_title($dealer);
                                $d_link = get_the_permalink($dealer);
                                $d_avatar = get_field('avatar', $dealer);
                                $d_location = get_field('location', $dealer);
                                $d_phone = get_field('phone', $dealer);
                                $d_message_link = get_field('message_link', $dealer);
                                $d_whatsapp_link = get_field('whatsapp_link', $dealer);
                            ?>
                                <!-- your dealer HTML -->
                            <?php } ?>

                            <!-- ✅ Buttons OUTSIDE the card BUT STILL INSIDE lgl-sale-block -->
                            <div class="lgl-sale-actions-outside">
                                <a class="lgl-btn-calculator" href="#lgl-tab-overview">
                                    <?php echo esc_html__('FINANCE CALCULATOR', 'autoart'); ?>
                                </a>

                                <a class="lgl-btn-primary" href="#lgl-tab-overview">
                                    <?php echo esc_html__('ENQUIRE NOW', 'autoart'); ?>
                                </a>

                                <a class="lgl-btn-outline" href="#lgl-tab-overview">
                                    <?php echo esc_html__('RESERVE NOW', 'autoart'); ?>
                                </a>
                            </div>

                            <?php
                            // --- Contact info (edit these values) ---
                            $phone_display = '01978 810091';
                            $phone_link    = preg_replace('/\D+/', '', $phone_display); // 01978810091

                            $whatsapp_display = '01978 810091';
                            $whatsapp_link    = preg_replace('/\D+/', '', $whatsapp_display); // same as phone

                            $email = 'sales@clwydcaravans.com';
                            $location_text = 'Location';
                            $location_url  = '#'; // put your Google Maps link here
                            ?>

                            <div class="lgl-contact-info">
                                <h4 class="lgl-contact-title">Contact Information</h4>

                                <ul class="lgl-contact-list">
                                    <li class="lgl-contact-item">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="17.532" height="17.532" viewBox="0 0 17.532 17.532">
                                            <path id="download_1_" data-name="download (1)" d="M5.153.747A1.246,1.246,0,0,0,3.672.022L.922.772A1.254,1.254,0,0,0,0,1.978a14,14,0,0,0,14,14,1.254,1.254,0,0,0,1.206-.922l.75-2.75a1.246,1.246,0,0,0-.725-1.481l-3-1.25a1.246,1.246,0,0,0-1.447.362L9.521,11.478A10.561,10.561,0,0,1,4.5,6.456L6.04,5.2A1.247,1.247,0,0,0,6.4,3.75l-1.25-3Z" transform="translate(0.75 0.805)" fill="#001537" stroke="#f7faff" stroke-width="1.5" />
                                        </svg>
                                        </span>
                                        <a href="tel:<?php echo esc_attr($phone_link); ?>">
                                            <?php echo esc_html($phone_display); ?>
                                        </a>
                                    </li>

                                    <li class="lgl-contact-item">
                                        <span class="lgl-contact-icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16.209" viewBox="0 0 16 16.209">
                                                <path id="Path_172" data-name="Path 172" d="M19.646,30.06a8,8,0,1,0-3.061-2.986L15.567,31.26Zm.425-10.748a.911.911,0,0,1,.62-.239h.244a.765.765,0,0,1,.719.5l.5,1.373a.4.4,0,0,1-.062.382l-.394.492a.688.688,0,0,0-.107.684,5.517,5.517,0,0,0,2.639,2.417.7.7,0,0,0,.8-.128l.436-.436a.4.4,0,0,1,.4-.1l1.322.422a.767.767,0,0,1,.534.73v.336a.928.928,0,0,1-.272.656c-1.287,1.271-3.423.339-4.807-.51A8.039,8.039,0,0,1,20.13,23.5c-1.565-2.362-.614-3.686-.06-4.192Z" transform="translate(-15.5 -15.05)" fill="#25d366" />
                                            </svg>
                                        </span>
                                        <a target="_blank" rel="nofollow noopener"
                                            href="https://wa.me/<?php echo esc_attr($whatsapp_link); ?>">
                                            <?php echo esc_html($whatsapp_display); ?>
                                        </a>
                                    </li>

                                    <li class="lgl-contact-item">
                                        <span class="lgl-contact-icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16.337" height="16.337" viewBox="0 0 16.337 16.337">
                                                <path id="download" d="M8.15,1.538a.032.032,0,0,1,.038,0L14.7,6.19a.256.256,0,0,1,.108.207v.434L9.3,11.35a1.785,1.785,0,0,1-2.269,0l-5.5-4.518V6.4A.247.247,0,0,1,1.64,6.19ZM1.532,8.813l4.531,3.721a3.319,3.319,0,0,0,4.212,0l4.531-3.721V14.55a.256.256,0,0,1-.255.255H1.787a.256.256,0,0,1-.255-.255ZM8.169,0a1.573,1.573,0,0,0-.909.29L.75,4.943A1.782,1.782,0,0,0,0,6.4V14.55a1.788,1.788,0,0,0,1.787,1.787H14.55a1.788,1.788,0,0,0,1.787-1.787V6.4a1.787,1.787,0,0,0-.747-1.455L9.078.29A1.573,1.573,0,0,0,8.169,0Z" fill="#001537" />
                                            </svg>
                                        </span>
                                        <a href="mailto:<?php echo antispambot(esc_attr($email)); ?>">
                                            <?php echo esc_html(antispambot($email)); ?>
                                        </a>
                                    </li>

                                    <li class="lgl-contact-item">
                                        <span class="lgl-contact-icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="15.903" height="19.379" viewBox="0 0 15.903 19.379">
                                                <path id="Path_1154" data-name="Path 1154" d="M12.952,3A6.952,6.952,0,0,0,6,9.952a5.6,5.6,0,0,0,1.3,3.91l5.648,6.517L18.6,13.862a5.6,5.6,0,0,0,1.3-3.91A6.952,6.952,0,0,0,12.952,3Z" transform="translate(-5 -2)" fill="none" stroke="#000" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
                                            </svg>
                                        </span>
                                        <a href="<?php echo esc_url($location_url); ?>">
                                            <?php echo esc_html($location_text); ?>
                                        </a>
                                    </li>
                                </ul>
                            </div>

                            <?php
                            $safety_tips = get_field('car_safety_tips', 'options');
                            if (!empty($safety_tips) && (!empty($safety_tips['heading']) || !empty($safety_tips['content']) || !empty($safety_tips['link']))) {
                            ?>
                                <!-- safety tips HTML here -->
                            <?php } ?>

                        </div><!-- /.lgl-sidebar-block.lgl-sale-block -->
                    </div><!-- /.lgl-sidebar-wrap -->
                </div><!-- /.lgl-post--sidebar -->
            </div>
            <div class="lgl-post--tabs lgl-tabs lgl-tabs-js">
                <div class="lgl-tabs--tbnav">
                    <a href="#bt_panel_overview" class="lgl-nav-item lgl-is-active">
                        <span><?php echo esc_html__('Key Information', 'autoart'); ?></span>
                    </a>
                    <?php if (!empty(get_the_content())) { ?>
                        <a href="#bt_panel_desc" class="lgl-nav-item">
                            <span><?php echo esc_html__('Description', 'autoart'); ?></span>
                        </a>
                    <?php } ?>
                    <?php if (!empty($interior_features)) { ?>
                        <a href="#bt_panel_interior" class="lgl-nav-item">
                            <span><?php echo esc_html__('Interior features', 'autoart'); ?></span>
                        </a>
                    <?php } ?>
                    <?php if (!empty($exterior_features)) { ?>
                        <a href="#bt_panel_exterior" class="lgl-nav-item">
                            <span><?php echo esc_html__('Exterior features', 'autoart'); ?></span>
                        </a>
                    <?php } ?>
                    <?php if (!empty($floor_plan)) { ?>
                        <a href="#bt_panel_floorplan" class="lgl-nav-item">
                            <span><?php echo esc_html__('Floor plan', 'autoart'); ?></span>
                        </a>
                    <?php } ?>
                    <?php if (!empty($warranty)) { ?>
                        <a href="#bt_panel_warranty" class="lgl-nav-item">
                            <span><?php echo esc_html__('Warranty', 'autoart'); ?></span>
                        </a>
                    <?php } ?>
                </div>
                <div class="lgl-tabs--tbpanel">
                    <div id="bt_panel_overview" class="lgl-panel-item lgl-panel-overview lgl-is-active">
                        <div class="lgl-panel-item--inner">
                            <h3 class="lgl-title-ss">
                                <?php echo '<span>' . esc_html__('Key Information', 'autoart') . '</span>'; ?>
                            </h3>
                            <div class="lgl-content-ss lgl-meta-list">
                                <div class="lgl-meta-item lgl-body">
                                    <svg width="26" height="26" viewBox="0 0 26 26" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M3.25 8.66667L6.19869 11.1239C6.39338 11.2862 6.63879 11.375 6.89222 11.375H19.1077C19.3612 11.375 19.6066 11.2862 19.8013 11.1239L22.75 8.66667M7.04167 15.1667H7.0525M18.9583 15.1667H18.9692M8.8407 4.875H17.1594C17.9369 4.875 18.6548 5.29162 19.0405 5.9667L22.1791 11.4592C22.5532 12.1139 22.75 12.8549 22.75 13.6092V20.0417C22.75 20.64 22.265 21.125 21.6667 21.125H20.5833C19.985 21.125 19.5 20.64 19.5 20.0417V18.9583H6.5V20.0417C6.5 20.64 6.01497 21.125 5.41667 21.125H4.33333C3.73503 21.125 3.25 20.64 3.25 20.0417V13.6092C3.25 12.8549 3.4468 12.1139 3.82095 11.4592L6.95951 5.9667C7.34526 5.29162 8.06317 4.875 8.8407 4.875ZM7.58333 15.1667C7.58333 15.4658 7.34082 15.7083 7.04167 15.7083C6.74252 15.7083 6.5 15.4658 6.5 15.1667C6.5 14.8676 6.74252 14.625 7.04167 14.625C7.34082 14.625 7.58333 14.8676 7.58333 15.1667ZM19.5 15.1667C19.5 15.4658 19.2574 15.7083 18.9583 15.7083C18.6592 15.7083 18.4167 15.4658 18.4167 15.1667C18.4167 14.8676 18.6592 14.625 18.9583 14.625C19.2574 14.625 19.5 14.8676 19.5 15.1667Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>

                                    <?php
                                    echo '<span class="lgl-label">' . esc_html__('Body:', 'autoart') . '</span>';

                                    if (!empty($body)) {
                                        echo '<span class="lgl-value">' . $body->name . '</span>';
                                    } else {
                                        echo '<span class="lgl-value">' . esc_html__('N/A', 'autoart') . '</span>';
                                    }
                                    ?>
                                </div>
                                <div class="lgl-meta-item lgl-condition">
                                    <svg width="26" height="26" viewBox="0 0 26 26" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M8.66602 18.4166H17.3327M8.66602 18.4166C8.66602 19.6133 7.69597 20.5833 6.49935 20.5833C5.30273 20.5833 4.33268 19.6133 4.33268 18.4166M8.66602 18.4166C8.66602 17.22 7.69597 16.25 6.49935 16.25C5.30273 16.25 4.33268 17.22 4.33268 18.4166M17.3327 18.4166C17.3327 19.6133 18.3027 20.5833 19.4993 20.5833C20.696 20.5833 21.666 19.6133 21.666 18.4166M17.3327 18.4166C17.3327 17.22 18.3027 16.25 19.4993 16.25C20.696 16.25 21.666 17.22 21.666 18.4166M4.33268 18.4166H3.89935C3.29263 18.4166 2.98926 18.4166 2.75753 18.2985C2.55369 18.1947 2.38796 18.029 2.28409 17.8251C2.16602 17.5934 2.16602 17.2901 2.16602 16.6833V15.3833C2.16602 14.1699 2.16602 13.5631 2.40217 13.0996C2.60989 12.692 2.94135 12.3605 3.34904 12.1528C3.81251 11.9166 4.41924 11.9166 5.63268 11.9166H18.6327C19.4378 11.9166 19.8404 11.9166 20.1772 11.9699C22.0314 12.2636 23.4857 13.7179 23.7794 15.5721C23.8327 15.9089 23.8327 16.3115 23.8327 17.1166C23.8327 17.3179 23.8327 17.4186 23.8194 17.5027C23.7459 17.9663 23.3823 18.3298 22.9188 18.4033C22.8346 18.4166 22.734 18.4166 22.5327 18.4166H21.666M10.8327 5.41663V11.9166M4.33268 11.9166L4.69183 9.76175C4.94911 8.21805 5.07776 7.4462 5.46292 6.86699C5.80245 6.35642 6.27949 5.9523 6.83893 5.70134C7.47359 5.41663 8.25608 5.41663 9.82108 5.41663H13.4664C14.4838 5.41663 14.9926 5.41663 15.4544 5.55686C15.8632 5.68102 16.2435 5.88456 16.5736 6.15585C16.9465 6.46229 17.2287 6.88559 17.7931 7.73219L20.5827 11.9166" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>

                                    <?php
                                    echo '<span class="lgl-label">' . esc_html__('Condition:', 'autoart') . '</span>';

                                    if (!empty($condition)) {
                                        echo '<span class="lgl-value">' . $condition->name . '</span>';
                                    } else {
                                        echo '<span class="lgl-value">' . esc_html__('N/A', 'autoart') . '</span>';
                                    }
                                    ?>
                                </div>
                                <div class="lgl-meta-item lgl-mileage">
                                    <svg width="26" height="26" viewBox="0 0 26 26" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M12.9998 3.80554C5.83162 3.80554 0 9.63751 0 16.8057C0 17.8033 0.11375 18.798 0.338559 19.7626C0.364406 19.8733 0.394062 19.976 0.427172 20.071C0.818898 21.3239 1.99875 22.1945 3.31485 22.1945H22.681C23.905 22.1945 25.0092 21.4437 25.474 20.3319C25.5302 20.2212 25.577 20.0984 25.6128 19.9614C25.6265 19.9093 25.6478 19.8223 25.6797 19.6807C25.8919 18.7424 26 17.7744 26 16.8056C26 9.63752 20.168 3.80554 12.9998 3.80554ZM24.1598 19.3381C24.1077 19.569 24.091 19.6245 24.0872 19.6245L24.0906 19.5777C23.9088 20.2049 23.3341 20.6366 22.681 20.6366H3.31485C2.66327 20.6366 2.09005 20.2072 1.90673 19.5819L1.9174 19.6538C1.91511 19.6538 1.90064 19.6017 1.85575 19.4092C1.661 18.5728 1.55792 17.7014 1.55792 16.8057C1.55792 10.4861 6.68058 5.36346 12.9998 5.36346C19.319 5.36346 24.442 10.4861 24.442 16.8057C24.4421 17.676 24.3443 18.5234 24.1598 19.3381Z" fill="currentColor" />
                                        <path d="M13.5872 15.0926C13.4027 15.0294 13.2053 14.9933 12.9995 14.9933C11.9988 14.9933 11.1875 15.8046 11.1875 16.8057C11.1875 17.8064 11.9988 18.6176 12.9995 18.6176C13.9154 18.6176 14.6708 17.9376 14.7925 17.0552C14.8039 16.973 14.8115 16.8905 14.8115 16.8057C14.8115 16.0107 14.2992 15.3367 13.5872 15.0926Z" fill="currentColor" />
                                        <path d="M20.2971 9.89159C20.1378 9.71934 19.8498 9.71548 19.6239 9.88245L13.3477 14.5037C13.4846 14.5247 13.6208 14.557 13.7546 14.603C14.5655 14.8815 15.1463 15.5771 15.292 16.3998L20.2579 10.5644C20.4409 10.3499 20.4576 10.062 20.2971 9.89159Z" fill="currentColor" />
                                        <path d="M17.287 9.28835C17.5685 9.45156 17.9282 9.35452 18.091 9.07309C18.2539 8.7912 18.1572 8.43142 17.8758 8.26826C17.5943 8.10626 17.2337 8.20285 17.071 8.48392C16.9089 8.76611 17.0047 9.12631 17.287 9.28835Z" fill="currentColor" />
                                        <path d="M13.1649 8.18456C13.4905 8.18456 13.7537 7.9202 13.7537 7.59576C13.7537 7.2702 13.4905 7.00659 13.1649 7.00659C12.8401 7.00659 12.5762 7.2702 12.5762 7.59576C12.5762 7.9202 12.8401 8.18456 13.1649 8.18456Z" fill="currentColor" />
                                        <path d="M3.86804 16.2957C3.52648 16.2957 3.24805 16.5733 3.24805 16.9156C3.24805 17.258 3.52643 17.5349 3.86804 17.5349C4.21035 17.5349 4.48726 17.258 4.48726 16.9156C4.48726 16.5733 4.21035 16.2957 3.86804 16.2957Z" fill="currentColor" />
                                        <path d="M5.81114 11.5005C5.52971 11.3377 5.16911 11.4351 5.00631 11.7166C4.84432 11.998 4.9409 12.3578 5.22157 12.521C5.50381 12.6838 5.86436 12.5868 6.02716 12.3053C6.18921 12.0238 6.09262 11.6634 5.81114 11.5005Z" fill="currentColor" />
                                        <path d="M8.4547 8.26829C8.17245 8.43145 8.07663 8.79123 8.23943 9.07312C8.40148 9.35455 8.76202 9.45159 9.0435 9.28838C9.32498 9.12639 9.42157 8.76539 9.25953 8.48396C9.09677 8.20283 8.73618 8.10625 8.4547 8.26829Z" fill="currentColor" />
                                        <path d="M20.517 11.5005C20.2356 11.6634 20.139 12.0239 20.3018 12.3054C20.4646 12.5876 20.8244 12.6839 21.1066 12.521C21.388 12.3579 21.4847 11.9981 21.3218 11.7166C21.1598 11.4351 20.7992 11.3377 20.517 11.5005Z" fill="currentColor" />
                                        <path d="M21.995 17.0149C22.3206 17.0149 22.5838 16.7516 22.5838 16.4265C22.5838 16.1009 22.3206 15.8369 21.995 15.8369C21.6695 15.8369 21.4062 16.1009 21.4062 16.4265C21.4062 16.7516 21.6695 17.0149 21.995 17.0149Z" fill="currentColor" />
                                    </svg>

                                    <?php
                                    echo '<span class="lgl-label">' . esc_html__('Mileage', 'autoart') . '</span>';

                                    if (!empty($mileage)) {
                                        echo '<span class="lgl-value">' . number_format($mileage, 0) . esc_html__(' km', 'autoart') . '</span>';
                                    } else {
                                        echo '<span class="lgl-value">' . esc_html__('N/A', 'autoart') . '</span>';
                                    }
                                    ?>
                                </div>
                                <div class="lgl-meta-item lgl-engine">
                                    <svg width="26" height="26" viewBox="0 0 26 26" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M14.5305 24.5555H4.26046C3.98853 24.5642 3.71762 24.5182 3.46385 24.4201C3.21007 24.322 2.97861 24.1739 2.78322 23.9846C2.58783 23.7952 2.4325 23.5686 2.32647 23.318C2.22044 23.0674 2.16588 22.7981 2.16602 22.5261V3.50995C2.18671 2.95563 2.42646 2.43216 2.83266 2.0544C3.23885 1.67663 3.77832 1.47544 4.33268 1.49495H14.6027C14.8686 1.48899 15.1329 1.5371 15.3797 1.63634C15.6265 1.73559 15.8505 1.88392 16.0382 2.07233C16.226 2.26074 16.3735 2.48531 16.4719 2.73243C16.5702 2.97955 16.6174 3.24406 16.6105 3.50995V22.5261C16.6096 22.7966 16.5548 23.0642 16.4493 23.3134C16.3438 23.5625 16.1898 23.7881 15.9961 23.977C15.8025 24.166 15.5731 24.3144 15.3215 24.4138C15.0698 24.5131 14.8009 24.5613 14.5305 24.5555ZM4.26046 2.88884C4.17675 2.88595 4.0933 2.89982 4.01502 2.92965C3.93675 2.95947 3.86523 3.00465 3.80467 3.06252C3.74411 3.12039 3.69573 3.18978 3.66238 3.26662C3.62902 3.34346 3.61137 3.42619 3.61046 3.50995V22.5261C3.61046 22.6927 3.67666 22.8525 3.79449 22.9704C3.91233 23.0882 4.07215 23.1544 4.23879 23.1544H14.5305C14.7033 23.1567 14.8706 23.093 14.9981 22.9762C15.1256 22.8595 15.2037 22.6985 15.2166 22.5261V3.50995C15.202 3.33884 15.1231 3.17962 14.9958 3.06436C14.8684 2.94911 14.7022 2.88639 14.5305 2.88884H4.26046Z" fill="currentColor" />
                                        <path d="M21.327 24.5555C20.9932 24.5584 20.6622 24.4951 20.353 24.3694C20.0438 24.2436 19.7626 24.0579 19.5256 23.8229C19.2886 23.5879 19.1004 23.3082 18.972 23.0001C18.8437 22.692 18.7776 22.3615 18.7776 22.0278V16.6111C18.7776 16.228 18.6254 15.8606 18.3545 15.5897C18.0836 15.3188 17.7162 15.1667 17.3331 15.1667H16.3003C16.1088 15.1667 15.9251 15.0906 15.7897 14.9551C15.6542 14.8197 15.5781 14.636 15.5781 14.4444C15.5781 14.2529 15.6542 14.0692 15.7897 13.9337C15.9251 13.7983 16.1088 13.7222 16.3003 13.7222H17.3331C18.0993 13.7222 18.8341 14.0266 19.3759 14.5683C19.9176 15.1101 20.222 15.8449 20.222 16.6111V22.0278C20.222 22.3151 20.3362 22.5906 20.5393 22.7938C20.7425 22.997 21.018 23.1111 21.3053 23.1111C21.5927 23.1111 21.8682 22.997 22.0714 22.7938C22.2745 22.5906 22.3887 22.3151 22.3887 22.0278V12.4944L20.1281 7.43888C19.9931 7.15715 19.781 6.91944 19.5164 6.75328C19.2518 6.58712 18.9456 6.4993 18.6331 6.49999H18.1059C17.9144 6.49999 17.7307 6.4239 17.5952 6.28845C17.4598 6.15301 17.3837 5.96931 17.3837 5.77776C17.3837 5.58622 17.4598 5.40252 17.5952 5.26708C17.7307 5.13163 17.9144 5.05554 18.1059 5.05554H18.6331C19.2336 5.05523 19.8214 5.22904 20.3251 5.5559C20.8289 5.88277 21.2271 6.34868 21.4715 6.89721L23.7898 12.0683C23.8322 12.1613 23.8543 12.2622 23.8548 12.3644V22.0278C23.8548 22.6982 23.5885 23.3411 23.1144 23.8152C22.6404 24.2892 21.9974 24.5555 21.327 24.5555Z" fill="currentColor" />
                                        <path d="M12.9991 6.49999H5.77691C5.58536 6.49999 5.40166 6.4239 5.26622 6.28845C5.13078 6.15301 5.05469 5.96931 5.05469 5.77776C5.05469 5.58622 5.13078 5.40252 5.26622 5.26708C5.40166 5.13163 5.58536 5.05554 5.77691 5.05554H12.9991C13.1907 5.05554 13.3744 5.13163 13.5098 5.26708C13.6453 5.40252 13.7214 5.58622 13.7214 5.77776C13.7214 5.96931 13.6453 6.15301 13.5098 6.28845C13.3744 6.4239 13.1907 6.49999 12.9991 6.49999Z" fill="currentColor" />
                                        <path d="M12.9991 9.3889H5.77691C5.58536 9.3889 5.40166 9.31281 5.26622 9.17737C5.13078 9.04193 5.05469 8.85823 5.05469 8.66668C5.05469 8.47513 5.13078 8.29144 5.26622 8.15599C5.40166 8.02055 5.58536 7.94446 5.77691 7.94446H12.9991C13.1907 7.94446 13.3744 8.02055 13.5098 8.15599C13.6453 8.29144 13.7214 8.47513 13.7214 8.66668C13.7214 8.85823 13.6453 9.04193 13.5098 9.17737C13.3744 9.31281 13.1907 9.3889 12.9991 9.3889Z" fill="currentColor" />
                                        <path d="M18.0562 8.72443C17.8647 8.72443 17.681 8.64834 17.5455 8.5129C17.4101 8.37746 17.334 8.19376 17.334 8.00221V3.66888C17.334 3.47733 17.4101 3.29363 17.5455 3.15819C17.681 3.02275 17.8647 2.94666 18.0562 2.94666C18.2478 2.94666 18.4315 3.02275 18.5669 3.15819C18.7023 3.29363 18.7784 3.47733 18.7784 3.66888V8.00221C18.7784 8.19376 18.7023 8.37746 18.5669 8.5129C18.4315 8.64834 18.2478 8.72443 18.0562 8.72443Z" fill="currentColor" />
                                    </svg>

                                    <?php
                                    echo '<span class="lgl-label">' . esc_html__('Engine Size:', 'autoart') . '</span>';

                                    if (!empty($engine)) {
                                        echo '<span class="lgl-value">' . $engine->name . '</span>';
                                    } else {
                                        echo '<span class="lgl-value">' . esc_html__('N/A', 'autoart') . '</span>';
                                    }
                                    ?>
                                </div>
                                <div class="lgl-meta-item lgl-fuel-type">
                                    <svg width="26" height="26" viewBox="0 0 26 26" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M14.5305 24.5555H4.26046C3.98853 24.5642 3.71762 24.5182 3.46385 24.4201C3.21007 24.322 2.97861 24.1739 2.78322 23.9846C2.58783 23.7952 2.4325 23.5686 2.32647 23.318C2.22044 23.0674 2.16588 22.7981 2.16602 22.5261V3.50995C2.18671 2.95563 2.42646 2.43216 2.83266 2.0544C3.23885 1.67663 3.77832 1.47544 4.33268 1.49495H14.6027C14.8686 1.48899 15.1329 1.5371 15.3797 1.63634C15.6265 1.73559 15.8505 1.88392 16.0382 2.07233C16.226 2.26074 16.3735 2.48531 16.4719 2.73243C16.5702 2.97955 16.6174 3.24406 16.6105 3.50995V22.5261C16.6096 22.7966 16.5548 23.0642 16.4493 23.3134C16.3438 23.5625 16.1898 23.7881 15.9961 23.977C15.8025 24.166 15.5731 24.3144 15.3215 24.4138C15.0698 24.5131 14.8009 24.5613 14.5305 24.5555ZM4.26046 2.88884C4.17675 2.88595 4.0933 2.89982 4.01502 2.92965C3.93675 2.95947 3.86523 3.00465 3.80467 3.06252C3.74411 3.12039 3.69573 3.18978 3.66238 3.26662C3.62902 3.34346 3.61137 3.42619 3.61046 3.50995V22.5261C3.61046 22.6927 3.67666 22.8525 3.79449 22.9704C3.91233 23.0882 4.07215 23.1544 4.23879 23.1544H14.5305C14.7033 23.1567 14.8706 23.093 14.9981 22.9762C15.1256 22.8595 15.2037 22.6985 15.2166 22.5261V3.50995C15.202 3.33884 15.1231 3.17962 14.9958 3.06436C14.8684 2.94911 14.7022 2.88639 14.5305 2.88884H4.26046Z" fill="currentColor" />
                                        <path d="M21.327 24.5555C20.9932 24.5584 20.6622 24.4951 20.353 24.3694C20.0438 24.2436 19.7626 24.0579 19.5256 23.8229C19.2886 23.5879 19.1004 23.3082 18.972 23.0001C18.8437 22.692 18.7776 22.3615 18.7776 22.0278V16.6111C18.7776 16.228 18.6254 15.8606 18.3545 15.5897C18.0836 15.3188 17.7162 15.1667 17.3331 15.1667H16.3003C16.1088 15.1667 15.9251 15.0906 15.7897 14.9551C15.6542 14.8197 15.5781 14.636 15.5781 14.4444C15.5781 14.2529 15.6542 14.0692 15.7897 13.9337C15.9251 13.7983 16.1088 13.7222 16.3003 13.7222H17.3331C18.0993 13.7222 18.8341 14.0266 19.3759 14.5683C19.9176 15.1101 20.222 15.8449 20.222 16.6111V22.0278C20.222 22.3151 20.3362 22.5906 20.5393 22.7938C20.7425 22.997 21.018 23.1111 21.3053 23.1111C21.5927 23.1111 21.8682 22.997 22.0714 22.7938C22.2745 22.5906 22.3887 22.3151 22.3887 22.0278V12.4944L20.1281 7.43888C19.9931 7.15715 19.781 6.91944 19.5164 6.75328C19.2518 6.58712 18.9456 6.4993 18.6331 6.49999H18.1059C17.9144 6.49999 17.7307 6.4239 17.5952 6.28845C17.4598 6.15301 17.3837 5.96931 17.3837 5.77776C17.3837 5.58622 17.4598 5.40252 17.5952 5.26708C17.7307 5.13163 17.9144 5.05554 18.1059 5.05554H18.6331C19.2336 5.05523 19.8214 5.22904 20.3251 5.5559C20.8289 5.88277 21.2271 6.34868 21.4715 6.89721L23.7898 12.0683C23.8322 12.1613 23.8543 12.2622 23.8548 12.3644V22.0278C23.8548 22.6982 23.5885 23.3411 23.1144 23.8152C22.6404 24.2892 21.9974 24.5555 21.327 24.5555Z" fill="currentColor" />
                                        <path d="M12.9991 6.49999H5.77691C5.58536 6.49999 5.40166 6.4239 5.26622 6.28845C5.13078 6.15301 5.05469 5.96931 5.05469 5.77776C5.05469 5.58622 5.13078 5.40252 5.26622 5.26708C5.40166 5.13163 5.58536 5.05554 5.77691 5.05554H12.9991C13.1907 5.05554 13.3744 5.13163 13.5098 5.26708C13.6453 5.40252 13.7214 5.58622 13.7214 5.77776C13.7214 5.96931 13.6453 6.15301 13.5098 6.28845C13.3744 6.4239 13.1907 6.49999 12.9991 6.49999Z" fill="currentColor" />
                                        <path d="M12.9991 9.3889H5.77691C5.58536 9.3889 5.40166 9.31281 5.26622 9.17737C5.13078 9.04193 5.05469 8.85823 5.05469 8.66668C5.05469 8.47513 5.13078 8.29144 5.26622 8.15599C5.40166 8.02055 5.58536 7.94446 5.77691 7.94446H12.9991C13.1907 7.94446 13.3744 8.02055 13.5098 8.15599C13.6453 8.29144 13.7214 8.47513 13.7214 8.66668C13.7214 8.85823 13.6453 9.04193 13.5098 9.17737C13.3744 9.31281 13.1907 9.3889 12.9991 9.3889Z" fill="currentColor" />
                                        <path d="M18.0562 8.72443C17.8647 8.72443 17.681 8.64834 17.5455 8.5129C17.4101 8.37746 17.334 8.19376 17.334 8.00221V3.66888C17.334 3.47733 17.4101 3.29363 17.5455 3.15819C17.681 3.02275 17.8647 2.94666 18.0562 2.94666C18.2478 2.94666 18.4315 3.02275 18.5669 3.15819C18.7023 3.29363 18.7784 3.47733 18.7784 3.66888V8.00221C18.7784 8.19376 18.7023 8.37746 18.5669 8.5129C18.4315 8.64834 18.2478 8.72443 18.0562 8.72443Z" fill="currentColor" />
                                    </svg>

                                    <?php
                                    echo '<span class="lgl-label">' . esc_html__('Fuel Type:', 'autoart') . '</span>';

                                    if (!empty($fuel_type)) {
                                        echo '<span class="lgl-value">' . $fuel_type->name . '</span>';
                                    } else {
                                        echo '<span class="lgl-value">' . esc_html__('N/A', 'autoart') . '</span>';
                                    }
                                    ?>
                                </div>
                                <div class="lgl-meta-item lgl-door">
                                    <svg width="26" height="26" viewBox="0 0 26 26" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M8.66602 14.0834H10.8327" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        <path d="M21.6673 9.75001L17.659 3.73751C17.5598 3.58696 17.4247 3.46352 17.2658 3.3784C17.1069 3.29327 16.9292 3.24914 16.749 3.25001H5.41732C5.13 3.25001 4.85445 3.36415 4.65129 3.56731C4.44812 3.77048 4.33398 4.04603 4.33398 4.33335V21.6667C4.33398 21.954 4.44812 22.2295 4.65129 22.4327C4.85445 22.6359 5.13 22.75 5.41732 22.75H14.084C14.3713 22.75 14.6469 22.6359 14.85 22.4327C15.0532 22.2295 15.1673 21.954 15.1673 21.6667V20.5833C15.1724 19.0226 15.7389 17.5158 16.7632 16.3383C17.7876 15.1607 19.2015 14.3911 20.7465 14.17C21.0048 14.1308 21.2403 13.9996 21.4095 13.8005C21.5787 13.6015 21.6703 13.3479 21.6673 13.0867V9.75001ZM21.6673 9.75001H4.33398" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>

                                    <?php
                                    echo '<span class="lgl-label">' . esc_html__('Door:', 'autoart') . '</span>';

                                    if (!empty($door)) {
                                        echo '<span class="lgl-value">' . $door->name . '</span>';
                                    } else {
                                        echo '<span class="lgl-value">' . esc_html__('N/A', 'autoart') . '</span>';
                                    }
                                    ?>
                                </div>
                                <div class="lgl-meta-item lgl-year">
                                    <svg width="26" height="26" viewBox="0 0 26 26" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M22.454 10.6364V7.09092C22.454 5.78552 21.3958 4.72728 20.0904 4.72728H5.90856C4.60316 4.72728 3.54492 5.78552 3.54492 7.09092V10.6364M22.454 10.6364V21.2727C22.454 22.5782 21.3958 23.6364 20.0904 23.6364H5.90856C4.60316 23.6364 3.54492 22.5782 3.54492 21.2727V10.6364M22.454 10.6364H3.54492M8.27219 2.36365V7.09092M17.7267 2.36365V7.09092" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                                        <path d="M8.86275 13H6.49911C6.17276 13 5.9082 13.2646 5.9082 13.5909V15.9545C5.9082 16.2809 6.17276 16.5455 6.49911 16.5455H8.86275C9.1891 16.5455 9.45366 16.2809 9.45366 15.9545V13.5909C9.45366 13.2646 9.1891 13 8.86275 13Z" fill="currentColor" />
                                        <path d="M14.5761 13H11.4246C10.9895 13 10.6367 13.2646 10.6367 13.5909V15.9545C10.6367 16.2809 10.9895 16.5455 11.4246 16.5455H14.5761C15.0112 16.5455 15.364 16.2809 15.364 15.9545V13.5909C15.364 13.2646 15.0112 13 14.5761 13Z" fill="currentColor" />
                                        <path d="M19.4995 13H17.1358C16.8095 13 16.5449 13.2646 16.5449 13.5909V15.9545C16.5449 16.2809 16.8095 16.5455 17.1358 16.5455H19.4995C19.8258 16.5455 20.0904 16.2809 20.0904 15.9545V13.5909C20.0904 13.2646 19.8258 13 19.4995 13Z" fill="currentColor" />
                                    </svg>

                                    <?php
                                    echo '<span class="lgl-label">' . esc_html__('Year:', 'autoart') . '</span>';

                                    if (!empty($year)) {
                                        echo '<span class="lgl-value">' . $year . '</span>';
                                    } else {
                                        echo '<span class="lgl-value">' . esc_html__('N/A', 'autoart') . '</span>';
                                    }
                                    ?>
                                </div>
                                <div class="lgl-meta-item lgl-cylinder">
                                    <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" width="512" height="512" x="0" y="0" viewBox="0 0 512 512" fill="currentColor">
                                        <path d="M425.621 38.187C414.763 1.216 272.789 0 256 0S97.237 1.216 86.379 38.187c-.64 1.387-1.045 2.859-1.045 4.48v426.667c0 1.621.405 3.093 1.045 4.48C97.237 510.784 239.211 512 256 512s158.763-1.216 169.621-38.187c.64-1.387 1.045-2.859 1.045-4.48V42.667c.001-1.622-.405-3.094-1.045-4.48zM256 21.333c87.723 0 137.685 13.248 148.075 21.333C393.685 50.752 343.723 64 256 64S118.315 50.752 107.925 42.667c10.39-8.086 60.352-21.334 148.075-21.334zm149.333 446.656c-6.101 7.851-56.448 22.677-149.333 22.677-93.995 0-144.619-15.211-149.333-21.333V65.429C149.312 84.544 242.603 85.333 256 85.333s106.688-.789 149.333-19.904v402.56z"></path>
                                    </svg>

                                    <?php
                                    echo '<span class="lgl-label">' . esc_html__('Cylinder:', 'autoart') . '</span>';

                                    if (!empty($cylinder)) {
                                        echo '<span class="lgl-value">' . $cylinder->name . '</span>';
                                    } else {
                                        echo '<span class="lgl-value">' . esc_html__('N/A', 'autoart') . '</span>';
                                    }
                                    ?>
                                </div>
                                <div class="lgl-meta-item lgl-transmission">
                                    <svg width="26" height="26" viewBox="0 0 26 26" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M6.49935 4.33329C6.49935 5.52991 5.5293 6.49996 4.33268 6.49996C3.13606 6.49996 2.16602 5.52991 2.16602 4.33329C2.16602 3.13668 3.13606 2.16663 4.33268 2.16663C5.5293 2.16663 6.49935 3.13668 6.49935 4.33329Z" stroke="currentColor" stroke-width="2" />
                                        <path d="M6.49935 21.6667C6.49935 22.8633 5.5293 23.8333 4.33268 23.8333C3.13606 23.8333 2.16602 22.8633 2.16602 21.6667C2.16602 20.47 3.13606 19.5 4.33268 19.5C5.5293 19.5 6.49935 20.47 6.49935 21.6667Z" stroke="currentColor" stroke-width="2" />
                                        <path d="M15.1673 21.6667C15.1673 22.8633 14.1973 23.8333 13.0007 23.8333C11.804 23.8333 10.834 22.8633 10.834 21.6667C10.834 20.47 11.804 19.5 13.0007 19.5C14.1973 19.5 15.1673 20.47 15.1673 21.6667Z" stroke="currentColor" stroke-width="2" />
                                        <path d="M15.1673 4.33329C15.1673 5.52991 14.1973 6.49996 13.0007 6.49996C11.804 6.49996 10.834 5.52991 10.834 4.33329C10.834 3.13668 11.804 2.16663 13.0007 2.16663C14.1973 2.16663 15.1673 3.13668 15.1673 4.33329Z" stroke="currentColor" stroke-width="2" />
                                        <path d="M23.8333 4.33329C23.8333 5.52991 22.8633 6.49996 21.6667 6.49996C20.47 6.49996 19.5 5.52991 19.5 4.33329C19.5 3.13668 20.47 2.16663 21.6667 2.16663C22.8633 2.16663 23.8333 3.13668 23.8333 4.33329Z" stroke="currentColor" stroke-width="2" />
                                        <path d="M13 6.5V14.0833M13 19.5V17.3333" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                                        <path d="M4.33398 19.5V11.9167M4.33398 6.5V8.66667" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                                        <path d="M21.6673 6.5V8.66667C21.6673 10.7094 21.6673 11.7308 21.0327 12.3654C20.3981 13 19.3767 13 17.334 13H10.834M4.33398 13H6.50065" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                                        <path d="M19.5 16.25V15.4375C19.0513 15.4375 18.6875 15.8013 18.6875 16.25H19.5ZM18.6875 23.8333C18.6875 24.282 19.0513 24.6458 19.5 24.6458C19.9487 24.6458 20.3125 24.282 20.3125 23.8333H18.6875ZM23.1404 24.2576C23.3746 24.6403 23.8749 24.7606 24.2576 24.5262C24.6403 24.292 24.7606 23.7917 24.5262 23.4091L23.1404 24.2576ZM19.5 17.0625H21.9762V15.4375H19.5V17.0625ZM20.3125 20.0417V16.25H18.6875V20.0417H20.3125ZM23.0208 18.1458C23.0208 18.76 22.5375 19.2292 21.9762 19.2292V20.8542C23.4663 20.8542 24.6458 19.6258 24.6458 18.1458H23.0208ZM21.9762 17.0625C22.5375 17.0625 23.0208 17.5317 23.0208 18.1458H24.6458C24.6458 16.6659 23.4663 15.4375 21.9762 15.4375V17.0625ZM21.9762 19.2292H21.5119V20.8542H21.9762V19.2292ZM21.5119 19.2292H19.5V20.8542H21.5119V19.2292ZM20.819 20.4659L23.1404 24.2576L24.5262 23.4091L22.2049 19.6174L20.819 20.4659ZM18.6875 20.0417V23.8333H20.3125V20.0417H18.6875Z" fill="currentColor" />
                                    </svg>

                                    <?php
                                    echo '<span class="lgl-label">' . esc_html__('Transmission:', 'autoart') . '</span>';

                                    if (!empty($transmission)) {
                                        echo '<span class="lgl-value">' . $transmission->name . '</span>';
                                    } else {
                                        echo '<span class="lgl-value">' . esc_html__('N/A', 'autoart') . '</span>';
                                    }
                                    ?>
                                </div>
                                <div class="lgl-meta-item lgl-color">
                                    <svg width="26" height="26" viewBox="0 0 26 26" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M13 0.8125C5.8175 0.8125 0 6.2725 0 13C0 19.7275 5.8175 25.1875 13 25.1875C20.6213 25.1875 14.69 20.5887 17.5338 17.7937C18.7687 16.575 20.1825 16.38 21.4987 16.38C22.1 16.38 22.685 16.4287 23.2213 16.4287C24.83 16.4287 26.0163 16.055 26.0163 13.0163C26 6.2725 20.1825 0.8125 13 0.8125ZM23.8062 14.3325C23.725 14.3487 23.5462 14.365 23.205 14.365C22.9775 14.365 22.7337 14.365 22.4737 14.3488C22.165 14.3488 21.84 14.3325 21.4825 14.3325C20.0363 14.3325 17.9237 14.5437 16.0875 16.3313C14.1862 18.2162 14.625 20.5563 14.885 21.97C14.9338 22.2625 15.015 22.685 15.0312 22.945C14.7712 23.0262 14.1862 23.1562 12.9837 23.1562C6.93875 23.1562 2.015 18.6063 2.015 13C2.015 7.39375 6.955 2.84375 13 2.84375C19.045 2.84375 23.9688 7.39375 23.9688 13C23.9688 13.8125 23.8713 14.2025 23.8062 14.3325Z" fill="currentColor" />
                                        <path d="M9.5868 15.3888C7.91305 15.3888 6.5643 16.6887 6.5643 18.2975C6.5643 19.9062 7.9293 21.2062 9.5868 21.2062C11.2443 21.2062 12.6093 19.9062 12.6093 18.2975C12.6093 16.6887 11.2443 15.3888 9.5868 15.3888ZM9.5868 19.2075C9.01805 19.2075 8.5468 18.8012 8.5468 18.2975C8.5468 17.7937 9.01805 17.3875 9.5868 17.3875C10.1555 17.3875 10.6268 17.7937 10.6268 18.2975C10.6268 18.8012 10.1555 19.2075 9.5868 19.2075ZM9.2618 11.7487C9.2618 10.14 7.8968 8.84 6.2393 8.84C4.5818 8.84 3.2168 10.14 3.2168 11.7487C3.2168 13.3575 4.5818 14.6575 6.2393 14.6575C7.8968 14.6575 9.2618 13.3575 9.2618 11.7487ZM6.2393 12.6587C5.67055 12.6587 5.1993 12.2525 5.1993 11.7487C5.1993 11.245 5.67055 10.8387 6.2393 10.8387C6.80805 10.8387 7.2793 11.245 7.2793 11.7487C7.2793 12.2525 6.80805 12.6587 6.2393 12.6587ZM11.9755 4.0625C10.3018 4.0625 8.95305 5.3625 8.95305 6.97125C8.95305 8.58 10.318 9.88 11.9755 9.88C13.633 9.88 14.998 8.58 14.998 6.97125C14.998 5.3625 13.633 4.0625 11.9755 4.0625ZM11.9755 7.88125C11.4068 7.88125 10.9355 7.475 10.9355 6.97125C10.9355 6.4675 11.4068 6.06125 11.9755 6.06125C12.5443 6.06125 13.0155 6.4675 13.0155 6.97125C13.0155 7.475 12.5443 7.88125 11.9755 7.88125ZM15.9893 10.01C15.9893 11.6187 17.3543 12.9187 19.0118 12.9187C20.6693 12.9187 22.0343 11.6187 22.0343 10.01C22.0343 8.40125 20.6693 7.10125 19.0118 7.10125C17.3543 7.10125 15.9893 8.40125 15.9893 10.01ZM20.0518 10.01C20.0518 10.5137 19.5805 10.92 19.0118 10.92C18.443 10.92 17.9718 10.5137 17.9718 10.01C17.9718 9.50625 18.443 9.1 19.0118 9.1C19.5805 9.1 20.0518 9.50625 20.0518 10.01Z" fill="currentColor" />
                                    </svg>

                                    <?php
                                    echo '<span class="lgl-label">' . esc_html__('Color:', 'autoart') . '</span>';

                                    if (!empty($color_arr)) {
                                        echo '<span class="lgl-value">' . implode(', ', $color_arr) . '</span>';
                                    } else {
                                        echo '<span class="lgl-value">' . esc_html__('N/A', 'autoart') . '</span>';
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php if (!empty(get_the_content())) { ?>
                        <div id="bt_panel_desc" class="lgl-panel-item lgl-panel-desc">
                            <div class="lgl-panel-item--inner">
                                <h3 class="lgl-title-ss">
                                    <?php echo '<span>' . esc_html__('Description', 'autoart') . '</span>'; ?>
                                </h3>
                                <div class="lgl-content-ss">
                                    <?php the_content(); ?>
                                </div>
                            </div>
                        </div>
                    <?php } ?>

                    <?php if (!empty($interior_features)) { ?>
                        <div id="bt_panel_interior" class="lgl-panel-item lgl-panel-interior">
                            <div class="lgl-panel-item--inner">
                                <h3 class="lgl-title-ss">
                                    <span><?php echo esc_html__('Interior features', 'autoart'); ?></span>
                                </h3>
                                <div class="lgl-content-ss">
                                    <?php echo wp_kses_post($interior_features); ?>
                                </div>
                            </div>
                        </div>
                    <?php } ?>

                    <?php if (!empty($exterior_features)) { ?>
                        <div id="bt_panel_exterior" class="lgl-panel-item lgl-panel-exterior">
                            <div class="lgl-panel-item--inner">
                                <h3 class="lgl-title-ss">
                                    <span><?php echo esc_html__('Exterior features', 'autoart'); ?></span>
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
                                    <span><?php echo esc_html__('Floor plan', 'autoart'); ?></span>
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
                                    <span><?php echo esc_html__('Warranty', 'autoart'); ?></span>
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
    </div>
</main>

<?php
// Load standard WordPress footer
get_footer();
