<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="lgl-post--meta">
    <div class="lgl-post--meta-row">
        <div class="lgl-post--meta-col">
            <div class="lgl-post--meta-item lgl-post--berth">
                <?php
                echo LGL_Shortcodes::render_inline_svg('berth');
                ?>
                <span class="label-val">
                    <span class="lgl-label">Berth</span><span class="lgl-value"><?php echo esc_html($berth ? $berth : 'N/A'); ?></span>

                </span>
            </div>
        </div>
        <div class="lgl-post--meta-col">
            <div class="lgl-post--meta-item lgl-post--year">
                <?php
                echo LGL_Shortcodes::render_inline_svg('year');
                ?>
                <div class="label-val">
                    <span class="lgl-label">Year</span><span class="lgl-value"><?php echo esc_html($year ? $year : 'N/A'); ?></span>
                </div>
            </div>
        </div>
        <?php if ($post_type == 'caravan') { ?>
            <div class="lgl-post--meta-col">
                <div class="lgl-post--meta-item lgl-post--axles">
                    <?php
                    echo LGL_Shortcodes::render_inline_svg('axles');
                    ?>
                    <div class="label-val">
                        <span class="lgl-label">Axles</span><span class="lgl-value"><?php echo esc_html($axles ? $axles : 'N/A'); ?></span>
                    </div>
                </div>
            </div>
        <?php } else { ?>
            <div class="lgl-post--meta-col">
                <div class="lgl-post--meta-item lgl-post--mileage">
                    <?php
                    echo LGL_Shortcodes::render_inline_svg('mileage');
                    ?>
                    <div class="label-val">
                        <span class="lgl-label">Mileage</span><span class="lgl-value"><?php echo esc_html($mileage ? $mileage : 'N/A'); ?></span>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
</div>