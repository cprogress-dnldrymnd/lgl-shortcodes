<!-- templates/admin/lgl-documentation.php -->
<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!current_user_can('manage_options')) {
    return;
}
?>
<div class="wrap lgl-docs">

    <h1 class="lgl-docs__heading">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" style="vertical-align:middle;margin-right:8px;">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <polyline points="14 2 14 8 20 8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <line x1="16" y1="13" x2="8" y2="13" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            <line x1="16" y1="17" x2="8" y2="17" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            <polyline points="10 9 9 9 8 9" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        </svg>
        <?php esc_html_e('LGL Shortcodes — Documentation', 'lgl-shortcodes'); ?>
    </h1>
    <p class="lgl-docs__intro"><?php esc_html_e('Reference guide for all available shortcodes. Copy any shortcode example and paste it into a page or post.', 'lgl-shortcodes'); ?></p>

    <?php

    // -------------------------------------------------------------------------
    // Shortcode definitions
    // -------------------------------------------------------------------------
    $shortcodes = array(

        array(
            'tag'         => 'lgl_listing',
            'label'       => 'Vehicle Listing Grid',
            'description' => 'Displays a grid or carousel of vehicles for a specified post type. Use this on dedicated listing pages (Caravans, Motorhomes, Campervans).',
            'examples'    => array(
                '[lgl_listing post_type="caravan"]',
                '[lgl_listing post_type="motorhome" limit="6"]',
                '[lgl_listing post_type="campervan" is_carousel="true"]',
                '[lgl_listing post_type="caravan" is_featured="true" style="style-1"]',
            ),
            'attributes'  => array(
                array('name' => 'post_type',   'default' => '—',       'description' => 'Required. The vehicle type to display. Accepts: <code>caravan</code>, <code>motorhome</code>, <code>campervan</code>. Supports comma-separated values e.g. <code>"caravan,motorhome"</code>.'),
                array('name' => 'limit',       'default' => '9',       'description' => 'Number of vehicles to display per page.'),
                array('name' => 'is_carousel', 'default' => 'false',   'description' => 'Set to <code>true</code> to render vehicles in a horizontal slider instead of a grid.'),
                array('name' => 'style',       'default' => 'style-1', 'description' => 'Card style variant. Currently supports <code>style-1</code>.'),
                array('name' => 'is_featured', 'default' => 'false',   'description' => 'Set to <code>true</code> to display only vehicles marked as featured in LGL Settings.'),
            ),
        ),

        array(
            'tag'         => 'lgl_search',
            'label'       => 'Vehicle Search Filter Form',
            'description' => 'Renders the vehicle search and filter form. When <code>post_type</code> is omitted the form displays a vehicle type selector and redirects to the relevant listing page on submit. When a <code>post_type</code> is set the form filters results inline via AJAX on the same page.',
            'examples'    => array(
                '[lgl_search]',
                '[lgl_search post_type="caravan"]',
                '[lgl_search post_type="motorhome"]',
            ),
            'attributes'  => array(
                array('name' => 'post_type', 'default' => '(none)', 'description' => 'Optional. Locks the form to a specific vehicle type: <code>caravan</code>, <code>motorhome</code>, or <code>campervan</code>. When omitted a vehicle-type dropdown is shown and the form redirects to the appropriate listing page.'),
            ),
        ),

        array(
            'tag'         => 'lgl_search_results',
            'label'       => 'Vehicle Search Results',
            'description' => 'Outputs the paginated results grid. Designed to be used alongside <code>[lgl_search post_type="..."]</code> on the same page — the search form updates these results via AJAX.',
            'examples'    => array(
                '[lgl_search_results]',
            ),
            'attributes'  => array(
                array('name' => '—', 'default' => '—', 'description' => 'No attributes. This shortcode reads filter state from the AJAX payload sent by <code>[lgl_search]</code>.'),
            ),
        ),

        array(
            'tag'         => 'lgl_compare',
            'label'       => 'Vehicle Comparison Table',
            'description' => 'Displays the full side-by-side vehicle comparison interface. Vehicles are managed via the in-page Select2 search or carried over from the <code>?compare=</code> URL parameter. Only vehicles of the same type can be compared. Up to 4 vehicles per category are supported.',
            'examples'    => array(
                '[lgl_compare]',
            ),
            'url_params'  => array(
                array('param' => '?compare=ID1,ID2', 'description' => 'Pre-loads specific vehicles into the comparison table. Both IDs must belong to the same post type. Any previously staged vehicles for that type are replaced. Example: <code>?compare=6917,6908</code>'),
            ),
            'attributes'  => array(
                array('name' => '—', 'default' => '—', 'description' => 'No shortcode attributes. Configure the Comparison Page under <strong>LGL Settings → LGL Pages</strong>.'),
            ),
        ),

        array(
            'tag'         => 'lgl_compare_duo',
            'label'       => 'Compare Duo Card',
            'description' => 'Renders a compact two-vehicle side-by-side preview card with a VS badge and a "Compare Now" button that links to the full comparison page. Ideal for use in landing pages, blog posts, or promotional content.',
            'examples'    => array(
                '[lgl_compare_duo post_id_1="123" post_id_2="456"]',
            ),
            'attributes'  => array(
                array('name' => 'post_id_1', 'default' => '—', 'description' => 'Required. The post ID of the first vehicle to display.'),
                array('name' => 'post_id_2', 'default' => '—', 'description' => 'Required. The post ID of the second vehicle to display.'),
            ),
            'notes'       => 'Both vehicles must be published. The "Compare Now" button links to the page set under <strong>LGL Settings → LGL Pages → Vehicle Comparison Page</strong> with both IDs passed as <code>?compare=ID1,ID2</code>.',
        ),

        array(
            'tag'         => 'lgl_mini_compare',
            'label'       => 'Mini Compare Button',
            'description' => 'Renders a small icon button that links directly to the Vehicle Comparison page. Intended for use in navigation bars or headers.',
            'examples'    => array(
                '[lgl_mini_compare]',
            ),
            'attributes'  => array(
                array('name' => '—', 'default' => '—', 'description' => 'No attributes. The destination URL is read from <strong>LGL Settings → LGL Pages → Vehicle Comparison Page</strong>.'),
            ),
        ),

        array(
            'tag'         => 'lgl_wishlist',
            'label'       => 'Wishlist Page',
            'description' => 'Displays the full saved wishlist for the currently logged-in user. Shows a vehicle card grid with image, title, price, berth, year, and a remove button. Guests see a login prompt. Configure the Wishlist Page URL under LGL Settings.',
            'examples'    => array(
                '[lgl_wishlist]',
            ),
            'attributes'  => array(
                array('name' => '—', 'default' => '—', 'description' => 'No attributes. Wishlist data is stored in user meta (<code>lgl_wishlists</code>). Set the page under <strong>LGL Settings → LGL Pages → Wishlist Page</strong>.'),
            ),
        ),

        array(
            'tag'         => 'lgl_mini_wishlist',
            'label'       => 'Mini Wishlist Dropdown',
            'description' => 'Renders a heart-icon toggle button with a live item count badge. Clicking it opens a dropdown showing saved vehicles with thumbnails, titles, prices, and remove buttons. Includes a link to the full Wishlist page. Requires the user to be logged in to show items.',
            'examples'    => array(
                '[lgl_mini_wishlist]',
            ),
            'attributes'  => array(
                array('name' => '—', 'default' => '—', 'description' => 'No attributes. The "View Your Wishlist" footer link points to the page set under <strong>LGL Settings → LGL Pages → Wishlist Page</strong>.'),
            ),
        ),

        array(
            'tag'         => 'lgl_related_vehicles',
            'label'       => 'Related Vehicles',
            'description' => 'Displays a grid of randomly selected vehicles related to the current single listing page. Matches by the <code>listing-make-model</code> taxonomy. Automatically excludes the current post. Intended for use inside single vehicle templates.',
            'examples'    => array(
                '[lgl_related_vehicles]',
                '[lgl_related_vehicles count="4"]',
                '[lgl_related_vehicles post_type="motorhome" count="3"]',
            ),
            'attributes'  => array(
                array('name' => 'post_type', 'default' => '(current post type)', 'description' => 'The vehicle type to query. Defaults to the post type of the current page.'),
                array('name' => 'count',     'default' => '3',                   'description' => 'Number of related vehicles to display.'),
            ),
        ),

    );

    foreach ($shortcodes as $sc) : ?>

        <div class="lgl-docs__card">

            <div class="lgl-docs__card-header">
                <div class="lgl-docs__card-tag">
                    <code class="lgl-docs__tag-code">[<?php echo esc_html($sc['tag']); ?>]</code>
                </div>
                <h2 class="lgl-docs__card-title"><?php echo esc_html($sc['label']); ?></h2>
            </div>

            <div class="lgl-docs__card-body">

                <p class="lgl-docs__description"><?php echo wp_kses($sc['description'], array('code' => array(), 'strong' => array())); ?></p>

                <?php if (!empty($sc['notes'])) : ?>
                    <div class="lgl-docs__note">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <?php echo wp_kses($sc['notes'], array('code' => array(), 'strong' => array())); ?>
                    </div>
                <?php endif; ?>

                <!-- Attributes Table -->
                <h3 class="lgl-docs__section-title"><?php esc_html_e('Attributes', 'lgl-shortcodes'); ?></h3>
                <table class="lgl-docs__table widefat">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Attribute', 'lgl-shortcodes'); ?></th>
                            <th><?php esc_html_e('Default', 'lgl-shortcodes'); ?></th>
                            <th><?php esc_html_e('Description', 'lgl-shortcodes'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sc['attributes'] as $attr) : ?>
                            <tr>
                                <td><code><?php echo esc_html($attr['name']); ?></code></td>
                                <td><code><?php echo esc_html($attr['default']); ?></code></td>
                                <td><?php echo wp_kses($attr['description'], array('code' => array(), 'strong' => array())); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- URL Parameters (optional) -->
                <?php if (!empty($sc['url_params'])) : ?>
                    <h3 class="lgl-docs__section-title"><?php esc_html_e('URL Parameters', 'lgl-shortcodes'); ?></h3>
                    <table class="lgl-docs__table widefat">
                        <thead>
                            <tr>
                                <th><?php esc_html_e('Parameter', 'lgl-shortcodes'); ?></th>
                                <th><?php esc_html_e('Description', 'lgl-shortcodes'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sc['url_params'] as $param) : ?>
                                <tr>
                                    <td><code><?php echo esc_html($param['param']); ?></code></td>
                                    <td><?php echo wp_kses($param['description'], array('code' => array(), 'strong' => array())); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>

                <!-- Examples -->
                <h3 class="lgl-docs__section-title"><?php esc_html_e('Usage Examples', 'lgl-shortcodes'); ?></h3>
                <div class="lgl-docs__examples">
                    <?php foreach ($sc['examples'] as $example) : ?>
                        <div class="lgl-docs__example">
                            <code class="lgl-docs__example-code"><?php echo esc_html($example); ?></code>
                            <button
                                class="lgl-docs__copy-btn button button-small"
                                data-clipboard="<?php echo esc_attr($example); ?>"
                                title="<?php esc_attr_e('Copy to clipboard', 'lgl-shortcodes'); ?>"
                            >
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                                <?php esc_html_e('Copy', 'lgl-shortcodes'); ?>
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>

            </div><!-- /.lgl-docs__card-body -->

        </div><!-- /.lgl-docs__card -->

    <?php endforeach; ?>

</div><!-- /.wrap.lgl-docs -->

<style>
.lgl-docs { max-width: 960px; }
.lgl-docs__heading { display: flex; align-items: center; font-size: 1.6rem; margin-bottom: 6px; color: #1e2a3b; }
.lgl-docs__intro { color: #666; margin-bottom: 30px; font-size: 14px; }

.lgl-docs__card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    margin-bottom: 28px;
    overflow: hidden;
    box-shadow: 0 1px 4px rgba(0,0,0,.05);
}

.lgl-docs__card-header {
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    padding: 16px 24px;
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
}

.lgl-docs__tag-code {
    background: #1e2a3b;
    color: #f6d100;
    padding: 4px 12px;
    border-radius: 5px;
    font-size: 13px;
    font-family: monospace;
    white-space: nowrap;
}

.lgl-docs__card-title {
    margin: 0;
    font-size: 1rem;
    font-weight: 700;
    color: #1e2a3b;
}

.lgl-docs__card-body { padding: 22px 24px 26px; }
.lgl-docs__description { margin-top: 0; color: #444; line-height: 1.65; }

.lgl-docs__note {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    background: #fffbeb;
    border: 1px solid #fde68a;
    border-radius: 6px;
    padding: 10px 14px;
    font-size: 13px;
    color: #92400e;
    margin-bottom: 18px;
}
.lgl-docs__note svg { flex-shrink: 0; margin-top: 1px; }

.lgl-docs__section-title {
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: #94a3b8;
    margin: 22px 0 8px;
}

.lgl-docs__table { border-collapse: collapse; width: 100%; font-size: 13px; margin-bottom: 4px; }
.lgl-docs__table th {
    background: #f1f5f9;
    color: #475569;
    font-weight: 600;
    text-align: left;
    padding: 8px 12px;
    border: 1px solid #e2e8f0;
}
.lgl-docs__table td {
    padding: 8px 12px;
    border: 1px solid #e2e8f0;
    vertical-align: top;
    color: #334155;
    line-height: 1.6;
}
.lgl-docs__table code {
    background: #f1f5f9;
    padding: 1px 6px;
    border-radius: 3px;
    font-size: 12px;
    color: #0f172a;
}

.lgl-docs__examples { display: flex; flex-direction: column; gap: 8px; }
.lgl-docs__example {
    display: flex;
    align-items: center;
    gap: 10px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 8px 12px;
}
.lgl-docs__example-code {
    flex: 1;
    font-size: 13px;
    font-family: monospace;
    color: #0f172a;
    background: none;
    white-space: nowrap;
    overflow: auto;
}
.lgl-docs__copy-btn {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    flex-shrink: 0;
    cursor: pointer;
}
.lgl-docs__copy-btn.is-copied {
    color: #16a34a;
    border-color: #16a34a;
}
</style>

<script type="text/javascript">
document.querySelectorAll('.lgl-docs__copy-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
        const text = btn.getAttribute('data-clipboard');
        if (!text) return;

        navigator.clipboard.writeText(text).then(function () {
            btn.classList.add('is-copied');
            btn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> Copied!';
            setTimeout(function () {
                btn.classList.remove('is-copied');
                btn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg> Copy';
            }, 2000);
        });
    });
});
</script>