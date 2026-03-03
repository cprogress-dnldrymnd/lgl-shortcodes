<?php
/**
 * Template for rendering the search results grid wrapper.
 * The loop content relies on AJAX injection mapping to partials/result-item.php.
 */
?>
<div class="lgl-elwg-cars-grid--default lgl-results-wrapper lgl-holder">
    <div class="lgls-grid-list-template">
        <div class="lgl-filter-scroll-pos"></div>
        <div class="lgl-topbar">
            <div class="lgl-col-left">
                <div class="lgl-results-block" id="lgl-results-count">
                    Awaiting Search...
                </div>
            </div>

            <div class="lgl-col-right">
                <form class="lgl-filter-form-sortview" id="lgl-sort-form" action="" method="get">
                    <input type="hidden" name="orderby" value="date">
                    <input type="hidden" name="order" value="desc">
                    <div class="lgl-sort-block">
                        <span class="lgl-sort-title">Sort by:</span>
                        <div class="lgl-sort-field">
                            <select name="sort_order" id="lgl-sort-order" class="lgl-select2" style="width: 220px;">
                                <option value="date_high" selected="selected">Date: newest first</option>
                                <option value="date_low">Date: oldest first</option>
                                <option value="mileage_high">Mileage: highest first</option>
                                <option value="mileage_low">Mileage: lowest first</option>
                                <option value="price_high">Price: highest first</option>
                                <option value="price_low">Price: lower first</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="lgl-filter-results">
            <span class="lgl-loading-wave" id="lgl-loader" style="display: none;">Loading...</span>
            <div class="lgl-grid-layout lgl-cols--3 lgl-layout-default" id="lgl-results-grid" data-limit="9">
                </div>
            <div class="lgl-pagination-wrap"></div>
        </div>
    </div>
</div>