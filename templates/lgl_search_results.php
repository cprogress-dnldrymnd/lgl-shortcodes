<?php
/**
 * Template for rendering the search results grid wrapper.
 * The loop content relies on AJAX injection mapping to partials/result-item.php.
 */
?>
<div class="bt-elwg-cars-grid--default lgl-results-wrapper">
    <div class="bt-cars-grid-list-template">
        <div class="bt-filter-scroll-pos"></div>
        <div class="bt-car-topbar">
            <div class="bt-car-col-left">
                <div class="bt-car-results-block" id="lgl-results-count">
                    Awaiting Search...
                </div>
            </div>

            <div class="bt-car-col-right">
                <form class="bt-car-filter-form-sortview" id="lgl-sort-form" action="" method="get">
                    <input type="hidden" name="orderby" value="date">
                    <input type="hidden" name="order" value="desc">
                    <div class="bt-car-sort-block">
                        <span class="bt-sort-title">Sort by:</span>
                        <div class="bt-sort-field">
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
        
        <div class="bt-filter-results">
            <span class="bt-loading-wave" id="lgl-loader" style="display: none;">Loading...</span>
            <div class="bt-car-grid-layout bt-cols--3 bt-layout-default" id="lgl-results-grid" data-limit="9">
                </div>
            <div class="bt-car-pagination-wrap"></div>
        </div>
    </div>
</div>