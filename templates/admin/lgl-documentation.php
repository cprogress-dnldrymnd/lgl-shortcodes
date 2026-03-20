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
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            <polyline points="14 2 14 8 20 8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            <line x1="16" y1="13" x2="8" y2="13" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
            <line x1="16" y1="17" x2="8" y2="17" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
            <polyline points="10 9 9 9 8 9" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
        </svg>
        <?php esc_html_e('LGL Shortcodes — Documentation', 'lgl-shortcodes'); ?>
    </h1>

    <p class="lgl-docs__intro"><?php esc_html_e('Reference guide for all available shortcodes and LGL Settings tabs.', 'lgl-shortcodes'); ?></p>

    <nav class="lgl-docs__toc">
        <a href="#lgl-docs-shortcodes" class="lgl-docs__toc-link"><?php esc_html_e('Shortcodes', 'lgl-shortcodes'); ?></a>
        <a href="#lgl-docs-settings" class="lgl-docs__toc-link"><?php esc_html_e('Settings', 'lgl-shortcodes'); ?></a>
        <a href="#lgl-docs-form-builder" class="lgl-docs__toc-link"><?php esc_html_e('Form Builder', 'lgl-shortcodes'); ?></a>
        <a href="#lgl-docs-email-builder" class="lgl-docs__toc-link"><?php esc_html_e('Email Builder', 'lgl-shortcodes'); ?></a>
    </nav>

    <?php /* ================================================================
           SECTION 1 — SHORTCODES
           ================================================================ */ ?>

    <h2 class="lgl-docs__section-heading" id="lgl-docs-shortcodes">
        <?php esc_html_e('Shortcodes', 'lgl-shortcodes'); ?>
    </h2>

    <?php

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
                array('name' => 'post_type',   'default' => '—',       'description' => 'Required. Vehicle type to display. Accepts: <code>caravan</code>, <code>motorhome</code>, <code>campervan</code>. Supports comma-separated values e.g. <code>"caravan,motorhome"</code>.'),
                array('name' => 'limit',       'default' => '9',       'description' => 'Number of vehicles to display per page.'),
                array('name' => 'is_carousel', 'default' => 'false',   'description' => 'Set to <code>true</code> to render vehicles in a horizontal slider instead of a grid.'),
                array('name' => 'style',       'default' => 'style-1', 'description' => 'Card style variant. Currently supports <code>style-1</code> and <code>style-2</code>.'),
                array('name' => 'is_featured', 'default' => 'false',   'description' => 'Set to <code>true</code> to display only vehicles marked as featured in LGL Settings → Featured Vehicles.'),
            ),
        ),

        array(
            'tag'         => 'lgl_search',
            'label'       => 'Vehicle Search Filter Form',
            'description' => 'Renders the vehicle search and filter form. When <code>post_type</code> is omitted the form shows a vehicle-type selector and redirects on submit. When a <code>post_type</code> is set the form filters results inline via AJAX.',
            'examples'    => array(
                '[lgl_search]',
                '[lgl_search post_type="caravan"]',
                '[lgl_search post_type="motorhome"]',
            ),
            'attributes'  => array(
                array('name' => 'post_type', 'default' => '(none)', 'description' => 'Optional. Locks the form to a specific vehicle type: <code>caravan</code>, <code>motorhome</code>, or <code>campervan</code>. When omitted a vehicle-type dropdown is shown and the form redirects to the appropriate listing page set under <strong>LGL Pages</strong>.'),
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
                array('name' => '—', 'default' => '—', 'description' => 'No attributes. Reads filter state from the AJAX payload sent by <code>[lgl_search]</code>.'),
            ),
        ),

        array(
            'tag'         => 'lgl_breadcrumbs',
            'label'       => 'Breadcrumbs & Back to Results',
            'description' => 'Renders a contextual breadcrumb trail that adapts to the current page type. On archive/search pages it reflects the active Make and Model filters as clickable AJAX nodes, allowing the user to step back through the filter hierarchy without a full page reload. On single vehicle pages it displays the full trail and reveals a "Back to Results" link if the user arrived from a search page (tracked via sessionStorage).',
            'examples'    => array(
                '[lgl_breadcrumbs]',
                '[lgl_breadcrumbs style="light"]',
            ),
            'attributes'  => array(
                array('name' => 'style', 'default' => 'dark', 'description' => 'Text colour scheme. Accepts <code>dark</code> (dark text, for light backgrounds) or <code>light</code> (white text, for dark/hero backgrounds).'),
            ),
            'notes' => '<span>The "Back to Results" button is hidden by default and is revealed client-side only when the user navigated from a valid archive/search page. It will not appear on direct visits or from external referrers.</span>',
        ),

        array(
            'tag'         => 'lgl_compare',
            'label'       => 'Vehicle Comparison Table',
            'description' => 'Full side-by-side vehicle comparison interface. Vehicles are managed via the inline Select2 search or the <code>?compare=</code> URL parameter. Only vehicles of the same type can be compared. Up to 4 vehicles per category.',
            'examples'    => array(
                '[lgl_compare]',
            ),
            'url_params'  => array(
                array('param' => '?compare=ID1,ID2', 'description' => 'Pre-loads specific vehicles into the table. Both IDs must be the same post type. Any previously staged vehicles for that type are replaced. Example: <code>?compare=6917,6908</code>'),
            ),
            'attributes'  => array(
                array('name' => '—', 'default' => '—', 'description' => 'No shortcode attributes. Set the page under <strong>LGL Settings → LGL Pages → Vehicle Comparison Page</strong>.'),
            ),
        ),

        array(
            'tag'         => 'lgl_compare_duo',
            'label'       => 'Compare Duo Card',
            'description' => 'Compact two-vehicle side-by-side preview card with a VS badge and a "Compare Now" CTA linking to the full comparison page. Ideal for landing pages or blog content.',
            'examples'    => array(
                '[lgl_compare_duo post_id_1="123" post_id_2="456"]',
            ),
            'attributes'  => array(
                array('name' => 'post_id_1', 'default' => '—', 'description' => 'Required. Post ID of the first vehicle.'),
                array('name' => 'post_id_2', 'default' => '—', 'description' => 'Required. Post ID of the second vehicle.'),
            ),
            'notes' => '<span>Both vehicles must be published. The "Compare Now" button links to <strong>LGL Settings → LGL Pages → Vehicle Comparison Page</strong> with both IDs as <code>?compare=ID1,ID2</code>.</span>',
        ),

        array(
            'tag'         => 'lgl_mini_compare',
            'label'       => 'Mini Compare Button',
            'description' => 'Small icon button linking to the Vehicle Comparison page. Intended for navigation bars or headers.',
            'examples'    => array(
                '[lgl_mini_compare]',
            ),
            'attributes'  => array(
                array('name' => '—', 'default' => '—', 'description' => 'No attributes. Destination URL is read from <strong>LGL Settings → LGL Pages → Vehicle Comparison Page</strong>.'),
            ),
        ),

        array(
            'tag'         => 'lgl_wishlist',
            'label'       => 'Wishlist Page',
            'description' => 'Full saved-wishlist page for the currently logged-in user. Shows a responsive table of saved vehicles with thumbnail, title, price, condition, year, berth, and a remove button. Stale posts (deleted or unpublished) are automatically pruned on render. Guests see a login prompt.',
            'examples'    => array(
                '[lgl_wishlist]',
            ),
            'attributes'  => array(
                array('name' => '—', 'default' => '—', 'description' => 'No attributes. Wishlist is stored in user meta (<code>lgl_wishlists</code>). Set the page under <strong>LGL Settings → LGL Pages → Wishlist Page</strong>.'),
            ),
        ),

        array(
            'tag'         => 'lgl_mini_wishlist',
            'label'       => 'Mini Wishlist Dropdown',
            'description' => 'Heart-icon toggle with a live item count badge. Clicking opens a dropdown of saved vehicles with thumbnails, prices, meta tags, and remove buttons. Includes a link to the full Wishlist page. The dropdown content refreshes via AJAX after each remove action.',
            'examples'    => array(
                '[lgl_mini_wishlist]',
            ),
            'attributes'  => array(
                array('name' => '—', 'default' => '—', 'description' => 'No attributes. "View Your Wishlist" footer link points to <strong>LGL Settings → LGL Pages → Wishlist Page</strong>.'),
            ),
        ),

        array(
            'tag'         => 'lgl_my_account',
            'label'       => 'My Account Dashboard',
            'description' => 'Full account management interface. Guests see a tabbed Login / Register form. Logged-in users see a sidebar navigation with four sections: Dashboard (overview cards), Wishlist (embeds <code>[lgl_wishlist]</code>), Account Details (first name, last name, email, password change via AJAX), and a Logout link. Registration is gated by the WordPress "Anyone can register" setting.',
            'examples'    => array(
                '[lgl_my_account]',
            ),
            'attributes'  => array(
                array('name' => '—', 'default' => '—', 'description' => 'No attributes. Set the page under <strong>LGL Settings → LGL Pages → My Account Page</strong> so deep-links from the mini account bar work correctly.'),
            ),
            'notes' => '<span>The login and register forms support deep-linking via the <code>?lgl_auth=login</code> or <code>?lgl_auth=register</code> query parameters — used by the mini account bar to open the correct tab automatically.</span>',
        ),

        array(
            'tag'         => 'lgl_mini_account',
            'label'       => 'Mini Account Bar',
            'description' => 'Compact inline account status bar for use in navigation headers. Logged-in users see "Hi, [First Name] | Logout". Guests see "Sign In | Register" links that deep-link to the correct tab on the My Account page via the <code>?lgl_auth=</code> parameter.',
            'examples'    => array(
                '[lgl_mini_account]',
            ),
            'attributes'  => array(
                array('name' => '—', 'default' => '—', 'description' => 'No attributes. Destination URLs are built from <strong>LGL Settings → LGL Pages → My Account Page</strong>.'),
            ),
        ),

        array(
            'tag'         => 'lgl_related_vehicles',
            'label'       => 'Related Vehicles',
            'description' => 'Grid of randomly selected vehicles related to the current single listing by the <code>listing-make-model</code> taxonomy. Automatically excludes the current post. Intended for single vehicle templates.',
            'examples'    => array(
                '[lgl_related_vehicles]',
                '[lgl_related_vehicles count="4"]',
                '[lgl_related_vehicles post_type="motorhome" count="3"]',
            ),
            'attributes'  => array(
                array('name' => 'post_type', 'default' => '(current post type)', 'description' => 'Vehicle type to query. Defaults to the post type of the current page.'),
                array('name' => 'count',     'default' => '3',                   'description' => 'Number of related vehicles to display.'),
            ),
        ),

    );

    foreach ($shortcodes as $sc) : ?>

        <div class="lgl-docs__card">
            <div class="lgl-docs__card-header">
                <code class="lgl-docs__tag-code">[<?php echo esc_html($sc['tag']); ?>]</code>
                <h2 class="lgl-docs__card-title"><?php echo esc_html($sc['label']); ?></h2>
            </div>
            <div class="lgl-docs__card-body">

                <p class="lgl-docs__description"><?php echo wp_kses($sc['description'], array('code' => array(), 'strong' => array())); ?></p>

                <?php if (!empty($sc['notes'])) : ?>
                    <div class="lgl-docs__note">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="12" cy="12" r="10" />
                            <line x1="12" y1="8" x2="12" y2="12" />
                            <line x1="12" y1="16" x2="12.01" y2="16" />
                        </svg>
                        <?php echo wp_kses($sc['notes'], array('code' => array(), 'strong' => array(), 'span' => array())); ?>
                    </div>
                <?php endif; ?>

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

                <h3 class="lgl-docs__section-title"><?php esc_html_e('Usage Examples', 'lgl-shortcodes'); ?></h3>
                <div class="lgl-docs__examples">
                    <?php foreach ($sc['examples'] as $example) : ?>
                        <div class="lgl-docs__example">
                            <code class="lgl-docs__example-code"><?php echo esc_html($example); ?></code>
                            <button class="lgl-docs__copy-btn button button-small" data-clipboard="<?php echo esc_attr($example); ?>" title="<?php esc_attr_e('Copy to clipboard', 'lgl-shortcodes'); ?>">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <rect x="9" y="9" width="13" height="13" rx="2" ry="2" />
                                    <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1" />
                                </svg>
                                <?php esc_html_e('Copy', 'lgl-shortcodes'); ?>
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>

            </div>
        </div>

    <?php endforeach; ?>

    <?php /* ================================================================
           SECTION 2 — SETTINGS TABS
           ================================================================ */ ?>

    <h2 class="lgl-docs__section-heading" id="lgl-docs-settings">
        <?php esc_html_e('Settings', 'lgl-shortcodes'); ?>
    </h2>
    <p class="lgl-docs__settings-intro">
        <?php printf(
            wp_kses(__('All settings are saved under <strong>LGL Settings</strong> in the WordPress admin sidebar. Changes take effect immediately after clicking <strong>Save Changes</strong>.', 'lgl-shortcodes'), array('strong' => array()))
        ); ?>
    </p>

    <?php

    $settings_tabs = array(

        array(
            'slug'        => 'general',
            'label'       => 'General Settings',
            'description' => 'Top-level feature toggles and global formatting options that apply site-wide.',
            'fields'      => array(
                array('name' => 'Disable Wishlist',   'type' => 'Checkbox',    'default' => 'Off',    'description' => 'When checked, hides all wishlist buttons across the vehicle grid and single listing pages. The <code>[lgl_mini_wishlist]</code> shortcode output is also suppressed.'),
                array('name' => 'Disable Compare',    'type' => 'Checkbox',    'default' => 'Off',    'description' => 'When checked, hides all compare buttons across the vehicle grid and single listing pages.'),
                array('name' => 'Currency Symbol',    'type' => 'Text',        'default' => '$',      'description' => 'The currency symbol used when formatting prices across all plugin output. e.g. <code>£</code>, <code>€</code>, <code>$</code>.'),
                array('name' => 'Currency Position',  'type' => 'Select',      'default' => 'before', 'description' => 'Controls whether the symbol appears before or after the number. <code>before</code> outputs e.g. <code>£10,000</code>; <code>after</code> outputs <code>10,000£</code>.'),
            ),
        ),

        array(
            'slug'        => 'design',
            'label'       => 'Design Settings',
            'description' => 'Controls the CSS custom properties injected into <code>:root</code> on every front-end page. Changes here propagate automatically to all plugin components without requiring a CSS recompile.',
            'fields'      => array(
                array('name' => 'Primary Font',     'type' => 'Text',         'default' => '"DM Sans", sans-serif',  'description' => 'Sets <code>--lgl-font-primary</code>. Used for body text, labels, and most UI elements.'),
                array('name' => 'Secondary Font',   'type' => 'Text',         'default' => '"Poppins", sans-serif',  'description' => 'Sets <code>--lgl-font-secondary</code>. Used for headings and accent typography.'),
                array('name' => 'Accent Color',     'type' => 'Color Picker', 'default' => '#f6d100',                'description' => 'Sets <code>--lgl-color-accent</code>. Used for highlights, badges, and CTA accents.'),
                array('name' => 'Primary Color',    'type' => 'Color Picker', 'default' => '#003793',                'description' => 'Sets <code>--lgl-color-primary</code>. Used for the search form background and primary buttons.'),
                array('name' => 'Secondary Color',  'type' => 'Color Picker', 'default' => '#001537',                'description' => 'Sets <code>--lgl-color-secondary</code>. Used for headings, card titles, and dark UI surfaces.'),
                array('name' => 'Tertiary Color',   'type' => 'Color Picker', 'default' => '#00e6f6',                'description' => 'Sets <code>--lgl-color-tertiary</code>. Used for gradient accents and decorative elements.'),
                array('name' => 'Quaternary Color', 'type' => 'Color Picker', 'default' => '#007bff',                'description' => 'Sets <code>--lgl-color-quaternary</code>. Used for links, hover states, and interactive highlights.'),
            ),
        ),

        array(
            'slug'        => 'single-page',
            'label'       => 'Single Page',
            'description' => 'Configuration for the single vehicle detail page rendered by the plugin\'s custom template (<code>templates/single-lgl.php</code>). The Finance Calculator, Enquiry, and Reserve buttons are configured under <strong>LGL Settings → Form Builder</strong>, not here.',
            'fields'      => array(
                array('name' => 'Single Vehicle Additional Content', 'type' => 'Textarea', 'default' => '(empty)', 'description' => 'Free-form HTML or plain text appended to the bottom of each single vehicle page inside the <code>.lgl-additional-content</code> block. Accepts shortcodes.'),
            ),
        ),

        array(
            'slug'        => 'contact',
            'label'       => 'Contact Information',
            'description' => 'Dealer contact details displayed in the sidebar of every single vehicle page. These values are also available as merge tags in the Email Builder (e.g. <code>{{contact_phone}}</code>). Each field has a hardcoded fallback so the UI is never left blank.',
            'fields'      => array(
                array('name' => 'Phone Number',    'type' => 'Text',     'default' => '', 'description' => 'Displayed as a clickable <code>tel:</code> link. Non-numeric characters are automatically stripped when building the href.'),
                array('name' => 'WhatsApp Number', 'type' => 'Text',     'default' => '', 'description' => 'Used to build a <code>https://wa.me/</code> link. Non-numeric characters are automatically stripped.'),
                array('name' => 'Email Address',   'type' => 'Text',     'default' => '', 'description' => 'Displayed as a clickable <code>mailto:</code> link on the single vehicle page.'),
                array('name' => 'Address',         'type' => 'Textarea', 'default' => '', 'description' => 'Displayed in the contact sidebar. Also used to generate a Google Maps search link via the Maps API.'),
            ),
        ),

        array(
            'slug'        => 'visibility',
            'label'       => 'Field Visibility',
            'description' => 'Drag-and-drop interface to control which vehicle specification fields appear on the single listing page and in the comparison table, and in what order. Changes apply to all vehicle types globally.',
            'fields'      => array(
                array('name' => 'Drag handles',   'type' => 'Sortable list',       'default' => '(plugin default order)', 'description' => 'Drag rows to reorder fields. The saved order is applied to both the single vehicle spec list (<code>lgl-meta-list</code>) and the comparison table rows.'),
                array('name' => 'Hide checkbox',  'type' => 'Checkbox per field',  'default' => 'Off (all visible)',      'description' => 'Check the checkbox next to any field to hide it from the front end. Hidden fields move to the bottom of the list and cannot be sorted until unhidden.'),
            ),
            'notes' => 'Fields available for ordering include all common meta fields (price, berth, year, mileage, condition, etc.) plus type-specific fields for caravans and motorhomes/campervans, and taxonomy fields (Fuel Type, Chassis, Gearbox).',
        ),

        array(
            'slug'        => 'lgl-pages',
            'label'       => 'LGL Pages',
            'description' => 'Maps key plugin features to specific WordPress pages. The plugin reads these IDs to generate internal links and redirect targets across all shortcodes.',
            'fields'      => array(
                array('name' => 'Vehicle Comparison Page', 'type' => 'Page selector', 'default' => '(none)', 'description' => 'The page containing the <code>[lgl_compare]</code> shortcode. Used as the destination for all "Compare" links across the site, including the mini compare button and the compare duo card CTA.'),
                array('name' => 'Wishlist Page',           'type' => 'Page selector', 'default' => '(none)', 'description' => 'The page containing the <code>[lgl_wishlist]</code> shortcode. Used as the "View Your Wishlist" link in the mini wishlist dropdown footer.'),
                array('name' => 'My Account Page',         'type' => 'Page selector', 'default' => '(none)', 'description' => 'The page containing the <code>[lgl_my_account]</code> shortcode. Used as the destination for the mini account bar links and as the post-logout redirect target.'),
                array('name' => 'Caravan Page',            'type' => 'Page selector', 'default' => '(none)', 'description' => 'The main Caravan listing page. Used as the redirect target when the global search form is submitted with Caravan selected, and as the base URL for SEO-friendly Make/Model URL rewriting.'),
                array('name' => 'Motorhome Page',          'type' => 'Page selector', 'default' => '(none)', 'description' => 'The main Motorhome listing page. Used as the redirect target for the global search form when Motorhome is selected.'),
                array('name' => 'Campervan Page',          'type' => 'Page selector', 'default' => '(none)', 'description' => 'The main Campervan listing page. Used as the redirect target for the global search form when Campervan is selected.'),
            ),
            'notes' => '<span>After setting or changing archive page IDs, visit <strong>Settings → Permalinks</strong> and click Save Changes to flush the rewrite rules. This ensures Make/Model URL paths (e.g. <code>/caravans/bailey/unicorn/</code>) resolve correctly.</span>',
        ),

        array(
            'slug'        => 'featured',
            'label'       => 'Featured Vehicles',
            'description' => 'Selects which specific vehicles are shown when <code>[lgl_listing is_featured="true"]</code> is used. Featured status is also stored as post meta (<code>is_featured</code>) and displayed as a star icon in the WP admin list table.',
            'fields'      => array(
                array('name' => 'Featured Caravans',   'type' => 'Multi-select (searchable)', 'default' => '(none)', 'description' => 'Select one or more published Caravans to mark as featured. Only these vehicles will appear in <code>[lgl_listing post_type="caravan" is_featured="true"]</code>.'),
                array('name' => 'Featured Motorhomes', 'type' => 'Multi-select (searchable)', 'default' => '(none)', 'description' => 'Select one or more published Motorhomes to mark as featured.'),
                array('name' => 'Featured Campervans', 'type' => 'Multi-select (searchable)', 'default' => '(none)', 'description' => 'Select one or more published Campervans to mark as featured.'),
            ),
            'notes' => '<span>Saving this page automatically syncs the <code>is_featured</code> post meta on all affected vehicles. The star toggle in the WP admin post list and the per-post <strong>Featured Vehicle</strong> meta box also update this setting in real time.</span>',
        ),

    );

    foreach ($settings_tabs as $tab) : ?>

        <div class="lgl-docs__card lgl-docs__card--settings">
            <div class="lgl-docs__card-header lgl-docs__card-header--settings">
                <span class="lgl-docs__settings-badge"><?php esc_html_e('Settings', 'lgl-shortcodes'); ?></span>
                <h2 class="lgl-docs__card-title"><?php echo esc_html($tab['label']); ?></h2>
                <a href="<?php echo esc_url(admin_url('admin.php?page=lgl-settings&tab=' . $tab['slug'])); ?>" class="lgl-docs__settings-link button button-small" target="_self">
                    <?php esc_html_e('Open Tab →', 'lgl-shortcodes'); ?>
                </a>
            </div>
            <div class="lgl-docs__card-body">

                <p class="lgl-docs__description"><?php echo esc_html($tab['description']); ?></p>

                <?php if (!empty($tab['notes'])) : ?>
                    <div class="lgl-docs__note">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="12" cy="12" r="10" />
                            <line x1="12" y1="8" x2="12" y2="12" />
                            <line x1="12" y1="16" x2="12.01" y2="16" />
                        </svg>
                        <?php echo wp_kses($tab['notes'], array('code' => array(), 'strong' => array(), 'span' => array())); ?>
                    </div>
                <?php endif; ?>

                <h3 class="lgl-docs__section-title"><?php esc_html_e('Fields', 'lgl-shortcodes'); ?></h3>
                <table class="lgl-docs__table widefat">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Field', 'lgl-shortcodes'); ?></th>
                            <th><?php esc_html_e('Type', 'lgl-shortcodes'); ?></th>
                            <th><?php esc_html_e('Default', 'lgl-shortcodes'); ?></th>
                            <th><?php esc_html_e('Description', 'lgl-shortcodes'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tab['fields'] as $field) : ?>
                            <tr>
                                <td><strong><?php echo esc_html($field['name']); ?></strong></td>
                                <td><span class="lgl-docs__type-badge"><?php echo esc_html($field['type']); ?></span></td>
                                <td><code><?php echo esc_html($field['default']); ?></code></td>
                                <td><?php echo wp_kses($field['description'], array('code' => array(), 'strong' => array())); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

            </div>
        </div>

    <?php endforeach; ?>


    <?php /* ================================================================
           SECTION 3 — FORM BUILDER
           ================================================================ */ ?>

    <h2 class="lgl-docs__section-heading" id="lgl-docs-form-builder">
        <?php esc_html_e('Form Builder', 'lgl-shortcodes'); ?>
    </h2>
    <p class="lgl-docs__settings-intro">
        <?php esc_html_e('The Form Builder lives under LGL Settings → Form Builder and controls the Finance Calculator modal, the Enquiry form modal, and the Reserve form modal rendered on every single vehicle page. No shortcode is required — modals are injected automatically via the wp_footer hook on single vehicle pages.', 'lgl-shortcodes'); ?>
    </p>

    <?php

    $form_builder_tabs = array(

        array(
            'slug'        => 'finance',
            'label'       => 'Finance Calculator',
            'description' => 'Configures the Finance Calculator modal that appears when a visitor clicks the Finance Calculator button on a single vehicle page. Supports a native built-in calculator, a custom iframe/HTML embed from a third-party provider, or the button can be hidden entirely.',
            'fields'      => array(
                array('name' => 'Calculator Mode',    'type' => 'Select',   'default' => 'On (Native)',  'description' => '<code>On</code> — uses the built-in APR/flat-rate calculator. <code>Custom</code> — renders your own iframe or HTML code. <code>Off</code> — hides the Finance Calculator button completely.'),
                array('name' => 'Custom Calculator Code', 'type' => 'Textarea', 'default' => '(empty)', 'description' => 'Only shown when mode is set to <code>Custom</code>. Paste your finance provider\'s <code>&lt;iframe&gt;</code> or embed code here.'),
                array('name' => 'Button Text',        'type' => 'Text',     'default' => 'Finance Calculator', 'description' => 'Label on the button that opens the modal. Supports <code>{{make}}</code> and <code>{{model}}</code> merge tags.'),
                array('name' => 'Modal Title',        'type' => 'Text',     'default' => 'Finance Calculator', 'description' => 'Heading inside the modal. Supports <code>{{make}}</code> and <code>{{model}}</code> merge tags.'),
                array('name' => 'Subtitle',           'type' => 'Text',     'default' => '(example APR string)', 'description' => 'Optional subtitle displayed below the modal title.'),
                array('name' => 'Calculation Type',   'type' => 'Select',   'default' => 'APR',          'description' => '<code>APR</code> uses compound interest (standard). <code>Flat Rate</code> calculates simple interest on the original principal.'),
                array('name' => 'APR Rate (%)',        'type' => 'Number',   'default' => '10.90',        'description' => 'The interest rate used in calculations. For APR mode this is the annual percentage rate; for flat rate it is the annual flat rate.'),
                array('name' => 'Representative APR', 'type' => 'Text',     'default' => '10.9% APR Representative', 'description' => 'Display-only string shown in the output table. Does not affect calculations.'),
                array('name' => 'Finance Provider',   'type' => 'Text',     'default' => '(empty)',      'description' => 'Optional display label for the finance provider name.'),
                array('name' => 'Term Options',        'type' => 'Textarea', 'default' => '24, 36, 48, 60', 'description' => 'One term duration per line (in months). These populate the Duration dropdown in the modal.'),
                array('name' => 'Default Term',        'type' => 'Number',   'default' => '60',           'description' => 'The term (in months) pre-selected when the modal opens.'),
                array('name' => 'Default Deposit (£)', 'type' => 'Number',  'default' => '500',          'description' => 'The deposit amount pre-filled when the modal opens.'),
                array('name' => 'Minimum Deposit (£)', 'type' => 'Number',  'default' => '100',          'description' => 'Validation floor for the deposit input. An error is shown if the user enters less.'),
                array('name' => 'Purchase Fee (£)',    'type' => 'Number',   'default' => '10',           'description' => 'A fixed fee added to the total amount repayable (e.g. option-to-purchase fee).'),
                array('name' => 'Admin Fee (£)',        'type' => 'Number',   'default' => '0',            'description' => 'Optional admin fee also added to the total repayable.'),
                array('name' => 'Disclaimer Text',    'type' => 'Textarea', 'default' => '(compliance text)', 'description' => 'Legal disclaimer displayed below the output table. Must include statements such as "Finance subject to status" and "Illustration purposes only" for UK FCA compliance.'),
            ),
            'notes' => '<span>The cash price is read from the <code>price</code> post meta key on each vehicle — the same key used across all other plugin components. No separate price field is required here.</span>',
        ),

        array(
            'slug'        => 'enquiry',
            'label'       => 'Enquiry Form',
            'description' => 'Configures the Enquiry modal form. When submitted, a record is saved as a Custom Post Type under LGL Settings → Enquiry Submissions and a notification email is dispatched via the Email Builder settings.',
            'fields'      => array(
                array('name' => 'Button Text',     'type' => 'Text',     'default' => 'ENQUIRE NOW',   'description' => 'Label on the Enquire button on the single vehicle page. Supports <code>{{make}}</code> and <code>{{model}}</code> merge tags.'),
                array('name' => 'Modal Title',     'type' => 'Text',     'default' => 'Make an Enquiry', 'description' => 'Heading inside the modal. Supports merge tags.'),
                array('name' => 'Submit Button Text', 'type' => 'Text',  'default' => 'SUBMIT ENQUIRY', 'description' => 'Label on the form submit button.'),
                array('name' => 'Success Message', 'type' => 'Textarea', 'default' => '(thank you text)', 'description' => 'Message displayed inside the modal after a successful submission.'),
                array('name' => 'Form Fields',     'type' => 'Repeater', 'default' => 'First Name, Last Name, Email, Phone, Enquiry', 'description' => 'Drag-and-drop field builder. Add, remove, reorder, and configure each field. Supports types: text, email, tel, number, date, time, textarea, select, acceptance (checkbox). Each field can be set to half-width (two columns) or full-width.'),
            ),
        ),

        array(
            'slug'        => 'reserve',
            'label'       => 'Reserve Form',
            'description' => 'Configures the Reserve modal form and the vehicle reservation system. Supports three modes that can be set globally here and overridden per vehicle via the Vehicle Form Settings meta box on each post edit screen.',
            'fields'      => array(
                array('name' => 'Button Text',             'type' => 'Text',     'default' => 'RESERVE NOW',   'description' => 'Label on the Reserve button on the single vehicle page.'),
                array('name' => '"Reserved" Button Text', 'type' => 'Text',     'default' => 'Reserved',      'description' => 'Label shown on the button after the vehicle has been reserved. The button is disabled at this point.'),
                array('name' => 'Modal Title',             'type' => 'Text',     'default' => 'Reserve this Leisure Vehicle for free', 'description' => 'Heading inside the modal.'),
                array('name' => 'Submit Button Text',      'type' => 'Text',     'default' => 'RESERVE YOUR LEISURE VEHICLE', 'description' => 'Label on the form submit button.'),
                array('name' => 'Success Message',         'type' => 'Textarea', 'default' => '(confirmation text)', 'description' => 'Message displayed inside the modal after a successful submission.'),
                array('name' => 'Default Reserve Mode',    'type' => 'Select',   'default' => 'Form Only',     'description' => 'Global default behaviour. <code>Auto Reserve</code> — form submission immediately marks the vehicle as reserved and disables the button. <code>Form Only</code> — submission is saved as pending, no lockout. <code>No Reserve Form</code> — button is hidden entirely.'),
                array('name' => 'Form Fields',             'type' => 'Repeater', 'default' => 'Standard address + visit date/time fields', 'description' => 'Same field builder as the Enquiry form. Default fields include name, email, phone, address, planned visit date and time.'),
            ),
            'notes' => '<span>The reserve mode can be overridden per vehicle using the <strong>Vehicle Form Settings</strong> meta box on the post edit screen. Reserved vehicles display a "Reserved" tag over their thumbnail on both grid cards and the single page gallery.</span>',
        ),

    );

    foreach ($form_builder_tabs as $tab) : ?>

        <div class="lgl-docs__card lgl-docs__card--settings">
            <div class="lgl-docs__card-header lgl-docs__card-header--settings">
                <span class="lgl-docs__settings-badge" style="background:#6d3fc0;">Form Builder</span>
                <h2 class="lgl-docs__card-title"><?php echo esc_html($tab['label']); ?></h2>
                <a href="<?php echo esc_url(admin_url('admin.php?page=lgl-form-builder&tab=' . $tab['slug'])); ?>" class="lgl-docs__settings-link button button-small" target="_self">
                    <?php esc_html_e('Open Tab →', 'lgl-shortcodes'); ?>
                </a>
            </div>
            <div class="lgl-docs__card-body">

                <p class="lgl-docs__description"><?php echo esc_html($tab['description']); ?></p>

                <?php if (!empty($tab['notes'])) : ?>
                    <div class="lgl-docs__note">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="12" cy="12" r="10" />
                            <line x1="12" y1="8" x2="12" y2="12" />
                            <line x1="12" y1="16" x2="12.01" y2="16" />
                        </svg>
                        <?php echo wp_kses($tab['notes'], array('code' => array(), 'strong' => array(), 'span' => array())); ?>
                    </div>
                <?php endif; ?>

                <h3 class="lgl-docs__section-title"><?php esc_html_e('Fields', 'lgl-shortcodes'); ?></h3>
                <table class="lgl-docs__table widefat">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Field', 'lgl-shortcodes'); ?></th>
                            <th><?php esc_html_e('Type', 'lgl-shortcodes'); ?></th>
                            <th><?php esc_html_e('Default', 'lgl-shortcodes'); ?></th>
                            <th><?php esc_html_e('Description', 'lgl-shortcodes'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tab['fields'] as $field) : ?>
                            <tr>
                                <td><strong><?php echo esc_html(isset($field['name']) ? $field['name'] : array_keys($field)[0]); ?></strong></td>
                                <td><span class="lgl-docs__type-badge"><?php echo esc_html($field['type']); ?></span></td>
                                <td><code><?php echo esc_html($field['default']); ?></code></td>
                                <td><?php echo wp_kses($field['description'], array('code' => array(), 'strong' => array())); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

            </div>
        </div>

    <?php endforeach; ?>


    <?php /* ================================================================
           SECTION 4 — EMAIL BUILDER
           ================================================================ */ ?>

    <h2 class="lgl-docs__section-heading" id="lgl-docs-email-builder">
        <?php esc_html_e('Email Builder', 'lgl-shortcodes'); ?>
    </h2>
    <p class="lgl-docs__settings-intro">
        <?php esc_html_e('The Email Builder lives under LGL Settings → Email Builder and controls the HTML email templates sent when forms are submitted. A live 700px-wide preview renders in real time as you edit. Merge tags (e.g. {{first_name}}, {{product_title}}) are resolved to placeholder values in the preview and to real submission data on send.', 'lgl-shortcodes'); ?>
    </p>

    <?php

    $email_builder_tabs = array(

        array(
            'slug'        => 'global',
            'label'       => 'Global Template',
            'description' => 'Defines the shared header, footer, and colour palette that wraps every email sent by the plugin. Can optionally be applied to all outgoing WordPress emails site-wide (including Gravity Forms, WooCommerce, and core notifications).',
            'fields'      => array(
                array('name' => 'Apply to All Site Emails', 'type' => 'Checkbox',    'default' => 'Off',     'description' => 'When enabled, wraps every <code>wp_mail()</code> call — including third-party plugins — in this global template. Gravity Forms HTML shells are automatically stripped and replaced. WooCommerce emails with their own native templates are skipped to prevent double-wrapping.'),
                array('name' => 'From Name',               'type' => 'Text',         'default' => '(blogname)', 'description' => 'Overrides the sender display name on all outgoing WordPress emails. Leave blank to use the WordPress default.'),
                array('name' => 'From Email',              'type' => 'Email',        'default' => '(admin email)', 'description' => 'Overrides the sender email address. Use an address at your own domain (e.g. <code>noreply@yoursite.com</code>) for better deliverability.'),
                array('name' => 'Theme Colors',            'type' => 'Color Pickers', 'default' => 'Dark navy / white', 'description' => 'Six colour pickers: Outer Background, Inner Background, Text Color, Header Background, Header Text, and Link & Accent. All colours are injected as inline CSS into each sent email.'),
                array('name' => 'Global Header (HTML)',    'type' => 'Textarea',     'default' => '<div class="eb-header"><h1>{{site_name}}</h1></div>', 'description' => 'HTML rendered above the email body in every email. Supports merge tags <code>{{site_name}}</code>, <code>{{year}}</code>, and all contact information tags.'),
                array('name' => 'Global Footer (HTML)',    'type' => 'Textarea',     'default' => '(copyright notice)', 'description' => 'HTML rendered below the email body in every email. Same merge tag support as the header.'),
            ),
            'notes' => '<span>Developers can also call <code>LGL_Email_Builder::wrap_html( $subject, $body )</code> directly to wrap any arbitrary HTML in the global template from custom code.</span>',
        ),

        array(
            'slug'        => 'enquiry',
            'label'       => 'Enquiry Emails',
            'description' => 'Controls the notification email sent to the site admin (or a custom address) when an Enquiry form is submitted, and optionally an auto-reply sent back to the person who submitted.',
            'fields'      => array(
                array('name' => 'Recipients',           'type' => 'Radio',    'default' => 'Admin',  'description' => 'Three options: <code>Site admin email</code>, <code>Custom email address</code>, or <code>Both</code>. Selecting Custom or Both reveals a free-text email field.'),
                array('name' => 'From Name (override)', 'type' => 'Text',     'default' => '(global setting)', 'description' => 'Per-form sender name override. Falls back to the global Email Builder setting, then the WordPress default.'),
                array('name' => 'From Email (override)', 'type' => 'Email',   'default' => '(global setting)', 'description' => 'Per-form sender address override.'),
                array('name' => 'Subject',              'type' => 'Text',     'default' => 'New Enquiry: {{first_name}} {{last_name}} — {{product_title}}', 'description' => 'Email subject line. Supports all merge tags.'),
                array('name' => 'Email Body',           'type' => 'HTML Editor', 'default' => '(vehicle + contact table)', 'description' => 'Full HTML email body. A tag toolbar provides one-click insertion of all available merge tags grouped by category (Submitter, Vehicle, Site, Form Fields).'),
                array('name' => 'Send Test Email',      'type' => 'Button',   'default' => '—',      'description' => 'Sends a preview to a specified address with placeholder values substituted for all merge tags.'),
                array('name' => 'Auto-Reply Enabled',   'type' => 'Checkbox', 'default' => 'Off',    'description' => 'When enabled, a confirmation email is sent to the address provided in the form\'s <code>email</code> field.'),
                array('name' => 'Auto-Reply Subject',   'type' => 'Text',     'default' => 'Thank you for your enquiry, {{first_name}}', 'description' => 'Subject line for the auto-reply. Supports merge tags.'),
                array('name' => 'Auto-Reply Body',      'type' => 'HTML Editor', 'default' => '(confirmation template)', 'description' => 'HTML body for the auto-reply email.'),
            ),
        ),

        array(
            'slug'        => 'reserve',
            'label'       => 'Reserve Emails',
            'description' => 'Same structure as Enquiry Emails but for the Reserve form. Triggered when a vehicle is reserved (both Auto Reserve and Form Only modes send this email).',
            'fields'      => array(
                array('name' => 'Recipients',           'type' => 'Radio',    'default' => 'Admin',  'description' => 'Same recipient options as the Enquiry email: admin, custom, or both.'),
                array('name' => 'From Name (override)', 'type' => 'Text',     'default' => '(global setting)', 'description' => 'Per-form sender name override.'),
                array('name' => 'From Email (override)', 'type' => 'Email',   'default' => '(global setting)', 'description' => 'Per-form sender address override.'),
                array('name' => 'Subject',              'type' => 'Text',     'default' => 'New Reservation: {{first_name}} {{last_name}} — {{product_title}}', 'description' => 'Email subject line. Supports all merge tags.'),
                array('name' => 'Email Body',           'type' => 'HTML Editor', 'default' => '(vehicle + contact table)', 'description' => 'Full HTML email body with tag toolbar.'),
                array('name' => 'Send Test Email',      'type' => 'Button',   'default' => '—',      'description' => 'Sends a preview with placeholder values.'),
                array('name' => 'Auto-Reply Enabled',   'type' => 'Checkbox', 'default' => 'Off',    'description' => 'Sends a confirmation auto-reply to the submitter\'s email.'),
                array('name' => 'Auto-Reply Subject',   'type' => 'Text',     'default' => 'Your reservation request has been received, {{first_name}}', 'description' => 'Subject line for the auto-reply.'),
                array('name' => 'Auto-Reply Body',      'type' => 'HTML Editor', 'default' => '(reservation confirmation template)', 'description' => 'HTML body for the auto-reply.'),
            ),
            'notes' => '<span>All emails — admin notifications and auto-replies — are wrapped in the Global Template header, footer, and colours automatically. The full list of available merge tags is shown in the sidebar of each email editor tab.</span>',
        ),

    );

    foreach ($email_builder_tabs as $tab) : ?>

        <div class="lgl-docs__card lgl-docs__card--settings">
            <div class="lgl-docs__card-header lgl-docs__card-header--settings">
                <span class="lgl-docs__settings-badge" style="background:#0a6650;">Email Builder</span>
                <h2 class="lgl-docs__card-title"><?php echo esc_html($tab['label']); ?></h2>
                <a href="<?php echo esc_url(admin_url('admin.php?page=lgl-email-builder&tab=' . $tab['slug'])); ?>" class="lgl-docs__settings-link button button-small" target="_self">
                    <?php esc_html_e('Open Tab →', 'lgl-shortcodes'); ?>
                </a>
            </div>
            <div class="lgl-docs__card-body">

                <p class="lgl-docs__description"><?php echo esc_html($tab['description']); ?></p>

                <?php if (!empty($tab['notes'])) : ?>
                    <div class="lgl-docs__note">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="12" cy="12" r="10" />
                            <line x1="12" y1="8" x2="12" y2="12" />
                            <line x1="12" y1="16" x2="12.01" y2="16" />
                        </svg>
                        <?php echo wp_kses($tab['notes'], array('code' => array(), 'strong' => array(), 'span' => array())); ?>
                    </div>
                <?php endif; ?>

                <h3 class="lgl-docs__section-title"><?php esc_html_e('Fields', 'lgl-shortcodes'); ?></h3>
                <table class="lgl-docs__table widefat">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Field', 'lgl-shortcodes'); ?></th>
                            <th><?php esc_html_e('Type', 'lgl-shortcodes'); ?></th>
                            <th><?php esc_html_e('Default', 'lgl-shortcodes'); ?></th>
                            <th><?php esc_html_e('Description', 'lgl-shortcodes'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tab['fields'] as $field) : ?>
                            <tr>
                                <td><strong><?php echo esc_html(isset($field['name']) ? $field['name'] : '—'); ?></strong></td>
                                <td><span class="lgl-docs__type-badge"><?php echo esc_html($field['type']); ?></span></td>
                                <td><code><?php echo esc_html($field['default']); ?></code></td>
                                <td><?php echo wp_kses($field['description'], array('code' => array(), 'strong' => array())); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

            </div>
        </div>

    <?php endforeach; ?>

</div><!-- /.wrap.lgl-docs -->

<style>
    /* ---- Layout ---- */
    .lgl-docs {
        max-width: 960px;
    }

    .lgl-docs__heading {
        display: flex;
        align-items: center;
        font-size: 1.6rem;
        margin-bottom: 6px;
        color: #1e2a3b;
    }

    .lgl-docs__intro {
        color: #666;
        margin-bottom: 16px;
        font-size: 14px;
    }

    .lgl-docs__settings-intro {
        color: #555;
        margin-bottom: 24px;
        font-size: 13px;
    }

    /* ---- TOC ---- */
    .lgl-docs__toc {
        display: flex;
        gap: 8px;
        margin-bottom: 32px;
        flex-wrap: wrap;
    }

    .lgl-docs__toc-link {
        display: inline-block;
        padding: 6px 16px;
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
        color: #1e2a3b;
        text-decoration: none;
    }

    .lgl-docs__toc-link:hover {
        background: #1e2a3b;
        color: #f6d100;
        border-color: #1e2a3b;
    }

    /* ---- Section headings ---- */
    .lgl-docs__section-heading {
        font-size: 1.15rem;
        font-weight: 700;
        color: #1e2a3b;
        margin: 36px 0 16px;
        padding-bottom: 10px;
        border-bottom: 2px solid #e2e8f0;
    }

    /* ---- Cards ---- */
    .lgl-docs__card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        margin-bottom: 28px;
        overflow: hidden;
        box-shadow: 0 1px 4px rgba(0, 0, 0, .05);
    }

    .lgl-docs__card-header {
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        padding: 14px 24px;
        display: flex;
        align-items: center;
        gap: 14px;
        flex-wrap: wrap;
    }

    .lgl-docs__card-header--settings {
        background: #f0f4ff;
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

    .lgl-docs__settings-badge {
        background: #003793;
        color: #fff;
        padding: 3px 10px;
        border-radius: 5px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .05em;
        white-space: nowrap;
    }

    .lgl-docs__card-title {
        margin: 0;
        font-size: 1rem;
        font-weight: 700;
        color: #1e2a3b;
        flex: 1;
    }

    .lgl-docs__settings-link {
        margin-left: auto;
        white-space: nowrap;
    }

    /* ---- Body ---- */
    .lgl-docs__card-body {
        padding: 22px 24px 26px;
    }

    .lgl-docs__description {
        margin-top: 0;
        color: #444;
        line-height: 1.65;
    }

    /* ---- Note ---- */
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
        line-height: 1.5;
    }

    .lgl-docs__note svg {
        flex-shrink: 0;
        margin-top: 2px;
    }

    /* ---- Sub-headings inside cards ---- */
    .lgl-docs__section-title {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .07em;
        color: #94a3b8;
        margin: 22px 0 8px;
    }

    /* ---- Tables ---- */
    .lgl-docs__table {
        border-collapse: collapse;
        width: 100%;
        font-size: 13px;
        margin-bottom: 4px;
    }

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

    /* ---- Type badge ---- */
    .lgl-docs__type-badge {
        display: inline-block;
        background: #e0f2fe;
        color: #0369a1;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
        white-space: nowrap;
    }

    /* ---- Examples ---- */
    .lgl-docs__examples {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

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
    document.querySelectorAll('.lgl-docs__copy-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const text = btn.getAttribute('data-clipboard');
            if (!text) return;
            navigator.clipboard.writeText(text).then(function() {
                btn.classList.add('is-copied');
                btn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> Copied!';
                setTimeout(function() {
                    btn.classList.remove('is-copied');
                    btn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg> Copy';
                }, 2000);
            });
        });
    });
</script>