<?php
/**
 * Template Name: Finance Specialist Shortcode
 * Component: [lgl_finance_specialist]
 * Extracts dynamically populated LGL Settings and handles URL routing to designated inventory pages.
 */

if (!defined('ABSPATH')) {
    exit;
}

// Fetch Global Settings Payload
$options = get_option('lgl_settings', array());

// Data extraction with safe fallbacks
$heading     = !empty($options['finance_heading']) ? $options['finance_heading'] : 'Finance Specialist';
$description = !empty($options['finance_description']) ? $options['finance_description'] : 'We can assist you in finding the most suitable vehicle based on your monthly budget. Use our slider below to adjust how much you would like to spend each month.';
$footer_text = !empty($options['finance_footer_text']) ? $options['finance_footer_text'] : 'Finance provided by Close Brothers Motor Finance';

// Routing Extraction
$url_motorhome = !empty($options['motorhome_page']) ? get_permalink($options['motorhome_page']) : '#';
$url_caravan   = !empty($options['caravan_page']) ? get_permalink($options['caravan_page']) : '#';
$url_campervan = !empty($options['campervan_page']) ? get_permalink($options['campervan_page']) : '#';

// 1. Fetch unique prices across all relevant post types
$caravan_prices   = LGL_Shortcodes::get_unique_meta_values('caravan', 'price');
$motorhome_prices = LGL_Shortcodes::get_unique_meta_values('motorhome', 'price');
$campervan_prices = LGL_Shortcodes::get_unique_meta_values('campervan', 'price');

// 2. Merge, sanitize, and deduplicate
$raw_prices = array_merge($caravan_prices, $motorhome_prices, $campervan_prices);
$clean_prices = array();

foreach ($raw_prices as $price) {
    // Strip everything except numbers and decimals to ensure strict float conversion
    $numeric_val = floatval(preg_replace('/[^0-9.]/', '', $price));
    if ($numeric_val > 0) {
        $clean_prices[] = $numeric_val;
    }
}

$clean_prices = array_unique($clean_prices);
sort($clean_prices, SORT_NUMERIC);

// 3. Fallback to prevent slider breakage if database is completely empty
if (empty($clean_prices)) {
    $clean_prices = array(10000, 20000, 30000, 40000, 50000);
}

// 4. Encode for DOM injection
$prices_json = wp_json_encode(array_values($clean_prices));
$max_index   = count($clean_prices) - 1;
// Start the slider roughly in the middle
$start_index = floor($max_index / 2);
?>

<div class="lgl-finance-specialist-wrapper">
    <div class="lgl-finance-content">
        <h2 class="lgl-finance-heading"><?php echo esc_html($heading); ?></h2>
        <p class="lgl-finance-description"><?php echo esc_html($description); ?></p>
        
        <div class="lgl-finance-slider-container">
            <input type="range" 
                   class="lgl-budget-slider" 
                   min="0" 
                   max="<?php echo esc_attr($max_index); ?>" 
                   step="1" 
                   value="<?php echo esc_attr($start_index); ?>" 
                   data-prices="<?php echo esc_attr($prices_json); ?>">
            <p class="lgl-finance-max-price">Max vehicle price <strong><?php echo isset($options['currency_symbol']) ? esc_html($options['currency_symbol']) : '£'; ?><span class="lgl-slider-output">...</span></strong></p>
        </div>

        <div class="lgl-finance-actions">
            <a href="<?php echo esc_url($url_motorhome); ?>" class="lgl-btn lgl-btn-primary lgl-btn-finance">SEARCH MOTORHOMES</a>
            <a href="<?php echo esc_url($url_caravan); ?>" class="lgl-btn lgl-btn-secondary lgl-btn-finance">SEARCH CARAVANS</a>
            
            <?php if (!empty($options['campervan_page'])): ?>
                <a href="<?php echo esc_url($url_campervan); ?>" class="lgl-btn lgl-btn-secondary lgl-btn-finance">SEARCH CAMPERVANS</a>
            <?php endif; ?>
        </div>
        
        <p class="lgl-finance-footer-note"><?php echo esc_html($footer_text); ?></p>
    </div>
</div>