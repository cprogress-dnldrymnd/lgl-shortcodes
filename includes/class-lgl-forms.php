<?php

/**
 * Plugin Name: LGL Form Builder
 * Author: Digitally Disruptive - Donald Raymundo
 * Author URI: https://digitallydisruptive.co.uk/
 * Description: Integrated Form Builder for Finance Calculator, Enquiry, and Reserve forms.
 */

if (! defined('ABSPATH')) exit;

class LGL_Forms
{

    /**
     * Boot the form builder and register system hooks.
     */
    public function __construct()
    {
        // ── CPTs for submissions ──────────────────────────────────────
        add_action('init', [$this, 'register_post_types']);

        // ── Admin: extend LGL Settings tabs ──────────────────────────
        // Hook Builder at priority 10 (top) and Submissions at priority 99 (bottom)
        add_action('admin_menu',            [$this, 'add_builder_submenu'], 10);
        add_action('admin_menu',            [$this, 'add_submissions_submenu'], 99);
        
        add_action('admin_enqueue_scripts', [$this, 'admin_assets']);
        add_action('admin_post_lgl_save_finance_form',  [$this, 'save_finance']);
        add_action('admin_post_lgl_save_enquiry_form',  [$this, 'save_enquiry']);
        add_action('admin_post_lgl_save_reserve_form',  [$this, 'save_reserve']);

        // ── Per-product meta box ─────────────────────────────────────
        add_action('add_meta_boxes', [$this, 'add_product_meta_box']);
        add_action('save_post',      [$this, 'save_product_meta'], 10, 2);

        // ── Submission list table columns ────────────────────────────
        add_filter('manage_lgl_enquiry_sub_posts_columns',       [$this, 'enquiry_columns']);
        add_action('manage_lgl_enquiry_sub_posts_custom_column', [$this, 'enquiry_col_data'], 10, 2);
        add_filter('manage_lgl_reserve_sub_posts_columns',       [$this, 'reserve_columns']);
        add_action('manage_lgl_reserve_sub_posts_custom_column', [$this, 'reserve_col_data'], 10, 2);

        // ── Submission detail meta boxes ─────────────────────────────
        add_action('add_meta_boxes', [$this, 'add_submission_meta_boxes']);

        // ── AJAX — frontend form submissions ─────────────────────────
        add_action('wp_ajax_lgl_submit_enquiry',         [$this, 'ajax_submit_enquiry']);
        add_action('wp_ajax_nopriv_lgl_submit_enquiry',  [$this, 'ajax_submit_enquiry']);
        add_action('wp_ajax_lgl_submit_reserve',         [$this, 'ajax_submit_reserve']);
        add_action('wp_ajax_nopriv_lgl_submit_reserve',  [$this, 'ajax_submit_reserve']);

        // ── Frontend: inject modals into footer ──────────────────────
        add_action('wp_footer', [$this, 'render_modals']);

        // ── Localize extra data for frontend JS ──────────────────────
        add_action('wp_enqueue_scripts', [$this, 'localize_forms_data'], 20);
    }

    /* ═══════════════════════════════════════════════════════════════
       POST TYPES
    ═══════════════════════════════════════════════════════════════ */

    /**
     * Registers the Custom Post Types for storing form submissions.
     */
    public function register_post_types()
    {

        register_post_type('lgl_enquiry_sub', [
            'label'           => __('Enquiry Submissions', 'lgl-shortcodes'),
            'labels'          => [
                'name'          => __('Enquiry Submissions', 'lgl-shortcodes'),
                'singular_name' => __('Enquiry Submission', 'lgl-shortcodes'),
                'all_items'     => __('All Enquiries', 'lgl-shortcodes'),
            ],
            'public'          => false,
            'show_ui'         => true,
            'show_in_menu'    => false,
            'capability_type' => 'post',
            'capabilities'    => ['create_posts' => false],
            'map_meta_cap'    => true,
            'supports'        => ['title'],
            'has_archive'     => false,
            'rewrite'         => false,
        ]);

        register_post_type('lgl_reserve_sub', [
            'label'           => __('Reserve Submissions', 'lgl-shortcodes'),
            'labels'          => [
                'name'          => __('Reserve Submissions', 'lgl-shortcodes'),
                'singular_name' => __('Reserve Submission', 'lgl-shortcodes'),
                'all_items'     => __('All Reservations', 'lgl-shortcodes'),
            ],
            'public'          => false,
            'show_ui'         => true,
            'show_in_menu'    => false,
            'capability_type' => 'post',
            'capabilities'    => ['create_posts' => false],
            'map_meta_cap'    => true,
            'supports'        => ['title'],
            'has_archive'     => false,
            'rewrite'         => false,
        ]);
    }

    /* ═══════════════════════════════════════════════════════════════
       ADMIN MENUS
    ═══════════════════════════════════════════════════════════════ */

    /**
     * Registers the master Form Builder submenu page.
     */
    public function add_builder_submenu()
    {
        add_submenu_page(
            'lgl-settings', 
            __('Form Builder', 'lgl-shortcodes'),  
            __('Form Builder', 'lgl-shortcodes'),   
            'manage_options', 
            'lgl-form-builder',  
            [$this, 'render_master_form_builder_page']
        );
    }

    /**
     * Registers the Submission CPTs. Fired at priority 99 to sit at the bottom.
     */
    public function add_submissions_submenu()
    {
        add_submenu_page('lgl-settings', __('Enquiry Submissions', 'lgl-shortcodes'), __('Enquiry Submissions', 'lgl-shortcodes'), 'manage_options', 'edit.php?post_type=lgl_enquiry_sub', null);
        add_submenu_page('lgl-settings', __('Reserve Submissions', 'lgl-shortcodes'), __('Reserve Submissions', 'lgl-shortcodes'), 'manage_options', 'edit.php?post_type=lgl_reserve_sub',  null);
    }

    /* ═══════════════════════════════════════════════════════════════
       ADMIN ASSETS
    ═══════════════════════════════════════════════════════════════ */

    /**
     * Loads CSS and JS only on the Form Builder and Submission screens.
     */
    public function admin_assets($hook)
    {
        $is_builder = ($hook === 'lgl-settings_page_lgl-form-builder');
        $is_submission_page = in_array(get_current_screen()->post_type ?? '', ['lgl_enquiry_sub', 'lgl_reserve_sub'], true);

        if (! $is_builder && ! $is_submission_page) return;

        wp_enqueue_script('jquery-ui-sortable');
        wp_add_inline_style('wp-admin', $this->admin_css());
        wp_add_inline_script('jquery', $this->admin_js());
    }

    /**
     * Outputs the admin CSS styling.
     */
    private function admin_css()
    {
        return '
        .lgl-form-builder-wrap .lgl-fbl-layout{display:grid;grid-template-columns:1fr 280px;gap:20px;margin-top:16px}
        .lgl-fbl-section{background:#fff;border:1px solid #c3c4c7;border-radius:4px;padding:18px 20px;margin-bottom:14px}
        .lgl-fbl-section h3{margin:0 0 14px;font-size:14px;border-bottom:1px solid #f0f0f1;padding-bottom:10px}
        .lgl-fields-list{border:1px solid #ddd;border-radius:3px;margin-bottom:10px;min-height:40px}
        .lgl-field-row{display:flex;align-items:stretch;border-bottom:1px solid #eee;background:#fff;transition:box-shadow 0.2s}
        .lgl-field-row:last-child{border-bottom:none}
        .lgl-field-row.ui-sortable-helper{box-shadow:0 4px 12px rgba(0,0,0,.15);background:#f0f6fc}
        .lgl-field-handle{width:34px;flex-shrink:0;display:flex;align-items:center;justify-content:center;cursor:grab;color:#aaa;border-right:1px solid #eee;background:#f9f9f9}
        .lgl-field-handle:active{cursor:grabbing}
        .lgl-field-body{flex:1;padding:10px 12px}
        .lgl-field-top{display:flex;flex-wrap:wrap;gap:8px;align-items:flex-end}
        .lgl-field-bottom{display:flex;gap:8px;margin-top:8px;flex-wrap:wrap}
        .lgl-fg{display:flex;flex-direction:column}
        .lgl-fg label{font-size:11px;color:#646970;margin-bottom:2px;font-weight:700;text-transform:uppercase;letter-spacing:.4px}
        .lgl-fg input,.lgl-fg select{min-width:110px}
        .lgl-fg--wide{flex:1;min-width:180px}
        .lgl-fg--wide input{width:100%}
        .lgl-fg--opts{flex:1;min-width:160px}
        .lgl-fg--opts textarea{width:100%;resize:vertical}
        .lgl-fg--cb{justify-content:flex-end;padding-bottom:2px}
        .lgl-fg--cb label{text-transform:none;font-size:13px;font-weight:400;letter-spacing:0;flex-direction:row;align-items:center;gap:4px}
        .lgl-field-actions{margin-left:auto;display:flex;align-items:flex-end;gap:4px;}
        .lgl-rm-field{color:#d63638!important;}
        .lgl-rm-field:hover{background:#fceaeb;}
        .lgl-clone-field{color:#2271b1!important;}
        .lgl-clone-field:hover{background:#f0f6fc;}
        .lgl-toggle-field{color:#50575e!important;}
        .lgl-toggle-field:hover{background:#f0f0f1;}
        .lgl-add-field .dashicons{vertical-align:middle;margin-top:-2px}
        .lgl-fcount{font-size:12px;color:#646970;font-weight:400;margin-left:8px;background:#f0f0f1;padding:2px 8px;border-radius:10px}
        .lgl-help-list{margin:0;padding-left:16px}
        .lgl-help-list li{font-size:13px;margin-bottom:5px;color:#3c434a}
        .lgl-shortcode-tip{background:#f6f7f7;border:1px solid #e0e0e0;border-radius:3px;padding:10px;margin-top:8px;font-size:12px}
        .lgl-shortcode-tip code{display:inline-block;background:#fff;border:1px solid #e0e0e0;padding:2px 5px;border-radius:2px;margin:1px 0}
        .lgl-sub-badge{display:inline-block;padding:2px 8px;border-radius:10px;font-size:11px;font-weight:700;text-transform:uppercase}
        .lgl-sub-pending{background:#fcf0b2;color:#674c03}
        .lgl-sub-confirmed{background:#b8e6bf;color:#1a4d25}
        .lgl-sub-cancelled{background:#fceaeb;color:#8a1f1f}
        .lgl-sub-auto{background:#d2e9f7;color:#0a4b78}
        .lgl-sub-detail-table th{width:160px;font-weight:600}
        @media(max-width:960px){.lgl-form-builder-wrap .lgl-fbl-layout{grid-template-columns:1fr}}
        ';
    }

    /**
     * Outputs the admin JavaScript with expanded repeater functionality.
     */
    private function admin_js()
    {
        return '
        (function($){
            var fc = 0;
            $(function(){
                fc = $("#lgl-fields-list .lgl-field-row").length;
                initSort();
                
                // Auto ID from label
                $(document).on("input",".lgl-label-inp",function(){
                    var $r=$(this).closest(".lgl-field-row");
                    var $id=$r.find(".lgl-id-inp");
                    if($id.data("manual"))return;
                    $id.val($(this).val().toLowerCase().replace(/[^a-z0-9\s_-]/g,"").replace(/[\s-]+/g,"_").replace(/^_+|_+$/g,""));
                });
                
                $(document).on("keyup",".lgl-id-inp",function(){$(this).data("manual",true)});
                
                // Show/hide options for select type
                $(document).on("change",".lgl-type-sel",function(){
                    var $r=$(this).closest(".lgl-field-row");
                    $(this).val()==="select"?$r.find(".lgl-fg--opts").show():$r.find(".lgl-fg--opts").hide();
                });
                
                // Add field
                $(document).on("click","#lgl-add-field-btn",function(){
                    var tmpl=$("#lgl-field-tpl").html().replace(/__I__/g,fc);
                    var $r=$(tmpl);
                    $("#lgl-fields-list").append($r);
                    fc++;
                    updateCount();
                    $r.find(".lgl-label-inp").focus();
                });

                // Duplicate field
                $(document).on("click",".lgl-clone-field",function(){
                    var $row = $(this).closest(".lgl-field-row");
                    var $clone = $row.clone();
                    $row.after($clone);
                    reindex();
                    updateCount();
                });

                // Toggle/Collapse field
                $(document).on("click",".lgl-toggle-field",function(){
                    var $row = $(this).closest(".lgl-field-row");
                    $row.find(".lgl-field-bottom").slideToggle(200);
                    $(this).find(".dashicons").toggleClass("dashicons-arrow-up-alt2 dashicons-arrow-down-alt2");
                });
                
                // Remove field
                $(document).on("click",".lgl-rm-field",function(){
                    if(!confirm("Remove this field?"))return;
                    $(this).closest(".lgl-field-row").fadeOut(200,function(){$(this).remove();reindex();updateCount()});
                });
                
                // Reindex on save
                $("#lgl-fb-form").on("submit",function(){reindex()});
            });
            
            function initSort(){
                $("#lgl-fields-list").sortable({handle:".lgl-field-handle",items:"> .lgl-field-row",axis:"y",update:function(){reindex()}});
            }
            
            function reindex(){
                $("#lgl-fields-list .lgl-field-row").each(function(i){
                    $(this).attr("data-i",i);
                    $(this).find("[name]").each(function(){
                        $(this).attr("name",$(this).attr("name").replace(/fields\[\d+\]/,"fields["+i+"]"));
                    });
                });
                fc=$("#lgl-fields-list .lgl-field-row").length;
            }
            
            function updateCount(){
                var n=$("#lgl-fields-list .lgl-field-row").length;
                $(".lgl-fcount").text(n+(n===1?" field":" fields"));
            }
        })(jQuery);
        ';
    }

    /* ═══════════════════════════════════════════════════════════════
       ADMIN PAGES & ROUTING
    ═══════════════════════════════════════════════════════════════ */

    /**
     * Master render controller for the Form Builder Tabs.
     */
    public function render_master_form_builder_page()
    {
        if (! current_user_can('manage_options')) return;

        $active_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'finance';

        echo '<div class="wrap">';
        echo '<h1>' . __('Form Builder', 'lgl-shortcodes') . '</h1>';

        if (isset($_GET['saved'])) {
            echo '<div class="notice notice-success is-dismissible"><p>' . __('Settings saved.', 'lgl-shortcodes') . '</p></div>';
        }

        // Native WordPress Nav Tabs
        echo '<h2 class="nav-tab-wrapper">';
        $tabs = [
            'finance' => __('Finance Calculator', 'lgl-shortcodes'),
            'enquiry' => __('Enquiry Form', 'lgl-shortcodes'),
            'reserve' => __('Reserve Form', 'lgl-shortcodes'),
        ];

        foreach ($tabs as $key => $name) {
            $active = ($active_tab === $key) ? 'nav-tab-active' : '';
            $url = admin_url('admin.php?page=lgl-form-builder&tab=' . $key);
            echo '<a href="' . esc_url($url) . '" class="nav-tab ' . esc_attr($active) . '">' . esc_html($name) . '</a>';
        }
        echo '</h2>';

        // Route to the corresponding UI rendering logic
        if ($active_tab === 'finance') {
            $this->page_finance();
        } elseif ($active_tab === 'enquiry') {
            $this->page_enquiry();
        } elseif ($active_tab === 'reserve') {
            $this->page_reserve();
        }

        echo '</div>';
    }

    /**
     * Renders the Finance Calculator Settings UI.
     */
    private function page_finance()
    {
        $s = get_option('lgl_finance_form', []);
?>
        <div class="lgl-form-builder-wrap">
            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                <?php wp_nonce_field('lgl_save_finance_form'); ?>
                <input type="hidden" name="action" value="lgl_save_finance_form">
                <div class="lgl-fbl-layout">
                    <div>
                        <div class="lgl-fbl-section">
                            <h3><?php _e('Popup Labels', 'lgl-shortcodes'); ?></h3>
                            <table class="form-table" style="margin:0">
                                <tr>
                                    <th><label for="fc_button_text"><?php _e('Button Text', 'lgl-shortcodes'); ?></label></th>
                                    <td><input type="text" id="fc_button_text" name="button_text" value="<?php echo esc_attr($s['button_text'] ?? 'Finance Calculator'); ?>" class="regular-text"></td>
                                </tr>
                                <tr>
                                    <th><label for="fc_title"><?php _e('Modal Title', 'lgl-shortcodes'); ?></label></th>
                                    <td><input type="text" id="fc_title" name="title" value="<?php echo esc_attr($s['title'] ?? 'Finance Calculator'); ?>" class="large-text"></td>
                                </tr>
                                <tr>
                                    <th><label for="fc_subtitle"><?php _e('Subtitle', 'lgl-shortcodes'); ?></label></th>
                                    <td><input type="text" id="fc_subtitle" name="subtitle" value="<?php echo esc_attr($s['subtitle'] ?? '10.90% available, calculate the cost of your caravan or motorhome'); ?>" class="large-text"></td>
                                </tr>
                            </table>
                        </div>
                        <div class="lgl-fbl-section">
                            <h3><?php _e('Financial Parameters', 'lgl-shortcodes'); ?></h3>
                            <table class="form-table" style="margin:0">
                                <tr>
                                    <th><label for="fc_apr"><?php _e('Representative APR (%)', 'lgl-shortcodes'); ?></label></th>
                                    <td><input type="number" id="fc_apr" name="apr" value="<?php echo esc_attr($s['apr'] ?? '10.90'); ?>" step="0.01" min="0" class="small-text"> %
                                        <p class="description"><?php _e('Used for monthly repayment calculations.', 'lgl-shortcodes'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th><label for="fc_fee"><?php _e('Option to Purchase Fee (£)', 'lgl-shortcodes'); ?></label></th>
                                    <td>£ <input type="number" id="fc_fee" name="purchase_fee" value="<?php echo esc_attr($s['purchase_fee'] ?? '10'); ?>" step="1" min="0" class="small-text">
                                        <p class="description"><?php _e('Added to the final payment (e.g. £10 Option To Purchase Fee).', 'lgl-shortcodes'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th><label for="fc_dur"><?php _e('Duration Options', 'lgl-shortcodes'); ?></label></th>
                                    <td><textarea id="fc_dur" name="durations" rows="6" class="large-text"><?php echo esc_textarea($s['durations'] ?? "1 Year\n2 Years\n3 Years\n4 Years\n5 Years"); ?></textarea>
                                        <p class="description"><?php _e('One option per line. e.g. "1 Year", "18 Months".', 'lgl-shortcodes'); ?></p>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="lgl-fbl-section">
                            <h3><?php _e('Disclaimer', 'lgl-shortcodes'); ?></h3>
                            <textarea name="disclaimer" rows="4" class="large-text widefat"><?php echo esc_textarea($s['disclaimer'] ?? 'Finance examples are for illustration purposes only. The figures shown are based on assumptions and may not reflect the exact terms you are offered. All finance is subject to status, affordability checks, credit approval and terms & conditions. *Final payment includes £10 Option To Purchase Fee.'); ?></textarea>
                        </div>
                    </div>
                    <div>
                        <div class="lgl-fbl-section">
                            <h3><?php _e('Cash Price Source', 'lgl-shortcodes'); ?></h3>
                            <p class="description"><?php _e('The calculator reads the cash price from the <strong>price</strong> post meta key (<code>price</code>) — the same key already used across the plugin.', 'lgl-shortcodes'); ?></p>
                            <p class="description"><?php _e('Set it per vehicle in the <strong>Vehicle Form Settings</strong> sidebar meta box on each product.', 'lgl-shortcodes'); ?></p>
                        </div>
                        <div class="lgl-fbl-section">
                            <h3><?php _e('Calculated Outputs', 'lgl-shortcodes'); ?></h3>
                            <p class="description"><?php _e('These fields are auto-calculated — not editable:', 'lgl-shortcodes'); ?></p>
                            <ul class="lgl-help-list">
                                <li>Cash Price</li>
                                <li>Deposit</li>
                                <li>Total Amount of Credit</li>
                                <li>Agreement Duration</li>
                                <li>Monthly Repayments of</li>
                                <li>Total Amount Repayable</li>
                                <li>Purchase Fee</li>
                                <li>Interest Rate (flat)</li>
                                <li>Representative APR</li>
                                <li>Monthly Payment*</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <?php submit_button(__('Save Finance Settings', 'lgl-shortcodes')); ?>
            </form>
        </div>
<?php
    }

    /**
     * Initializes the Enquiry Form state.
     */
    private function page_enquiry()
    {
        $s = get_option('lgl_enquiry_form', $this->default_enquiry());
        $this->render_form_builder_page('enquiry', $s);
    }

    /**
     * Initializes the Reserve Form state.
     */
    private function page_reserve()
    {
        $s = get_option('lgl_reserve_form', $this->default_reserve());
        $this->render_form_builder_page('reserve', $s);
    }

    /**
     * Core renderer mapping structure for standard field-based form builders.
     */
    private function render_form_builder_page($type, $s)
    {
        $action = "lgl_save_{$type}_form";
        $fields = $s['fields'] ?? [];
    ?>
        <div class="lgl-form-builder-wrap">
            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" id="lgl-fb-form">
                <?php wp_nonce_field("lgl_save_{$type}_form"); ?>
                <input type="hidden" name="action" value="<?php echo esc_attr($action); ?>">
                <div class="lgl-fbl-layout">
                    <div>
                        <div class="lgl-fbl-section">
                            <h3><?php _e('General Settings', 'lgl-shortcodes'); ?></h3>
                            <table class="form-table" style="margin:0">
                                <tr>
                                    <th><label><?php _e('Button Text', 'lgl-shortcodes'); ?></label></th>
                                    <td><input type="text" name="button_text" value="<?php echo esc_attr($s['button_text'] ?? ''); ?>" class="regular-text"></td>
                                </tr>
                                <?php if ($type === 'reserve') : ?>
                                    <tr>
                                        <th><label><?php _e('"Reserved" Button Text', 'lgl-shortcodes'); ?></label></th>
                                        <td><input type="text" name="reserved_button_text" value="<?php echo esc_attr($s['reserved_button_text'] ?? 'Reserved'); ?>" class="regular-text">
                                            <p class="description"><?php _e('Shown on the button after the vehicle is reserved.', 'lgl-shortcodes'); ?></p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                                <tr>
                                    <th><label><?php _e('Modal Title', 'lgl-shortcodes'); ?></label></th>
                                    <td><input type="text" name="title" value="<?php echo esc_attr($s['title'] ?? ''); ?>" class="large-text"></td>
                                </tr>
                                <tr>
                                    <th><label><?php _e('Submit Button Text', 'lgl-shortcodes'); ?></label></th>
                                    <td><input type="text" name="submit_text" value="<?php echo esc_attr($s['submit_text'] ?? ''); ?>" class="regular-text"></td>
                                </tr>
                                <tr>
                                    <th><label><?php _e('Success Message', 'lgl-shortcodes'); ?></label></th>
                                    <td><textarea name="success_message" rows="3" class="large-text"><?php echo esc_textarea($s['success_message'] ?? ''); ?></textarea></td>
                                </tr>
                                <?php if ($type === 'reserve') : ?>
                                    <tr>
                                        <th><label><?php _e('Default Reserve Mode', 'lgl-shortcodes'); ?></label></th>
                                        <td>
                                            <select name="default_reserve_mode">
                                                <option value="auto_reserve" <?php selected($s['default_reserve_mode'] ?? '', 'auto_reserve'); ?>><?php _e('⚡ Auto Reserve', 'lgl-shortcodes'); ?></option>
                                                <option value="form_only" <?php selected($s['default_reserve_mode'] ?? 'form_only', 'form_only'); ?>><?php _e('📋 Form Only (show reservation form)', 'lgl-shortcodes'); ?></option>
                                                <option value="no_reserve" <?php selected($s['default_reserve_mode'] ?? '', 'no_reserve'); ?>><?php _e('🚫 No Reserve Form (hide button)', 'lgl-shortcodes'); ?></option>
                                            </select>
                                            <p class="description"><?php _e('This is the global default. Override per-product via the <strong>Vehicle Form Settings</strong> sidebar meta box.', 'lgl-shortcodes'); ?></p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </table>
                        </div>

                        <div class="lgl-fbl-section">
                            <h3><?php _e('Form Fields', 'lgl-shortcodes'); ?> <span class="lgl-fcount"><?php printf(_n('%d field', '%d fields', count($fields), 'lgl-shortcodes'), count($fields)); ?></span></h3>
                            <div id="lgl-fields-list">
                                <?php foreach ($fields as $i => $f) : ?>
                                    <?php $this->render_field_row($i, $f); ?>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" id="lgl-add-field-btn" class="button button-secondary lgl-add-field">
                                <span class="dashicons dashicons-plus-alt2"></span> <?php _e('Add Field', 'lgl-shortcodes'); ?>
                            </button>
                        </div>
                    </div>

                    <div>
                        <div class="lgl-fbl-section">
                            <h3><?php _e('Field Types', 'lgl-shortcodes'); ?></h3>
                            <ul class="lgl-help-list">
                                <li><code>text</code> — Text input</li>
                                <li><code>email</code> — Email input</li>
                                <li><code>tel</code> — Phone number</li>
                                <li><code>number</code> — Numeric</li>
                                <li><code>date</code> — Date picker</li>
                                <li><code>time</code> — Time (HH:MM AM/PM)</li>
                                <li><code>textarea</code> — Multi-line text</li>
                                <li><code>select</code> — Dropdown (add options below)</li>
                            </ul>
                        </div>
                        <div class="lgl-fbl-section">
                            <h3><?php _e('Tips', 'lgl-shortcodes'); ?></h3>
                            <ul class="lgl-help-list">
                                <li><?php _e('Drag rows to reorder.', 'lgl-shortcodes'); ?></li>
                                <li><?php _e('Half = two columns side by side.', 'lgl-shortcodes'); ?></li>
                                <li><?php _e('Full = spans the whole modal.', 'lgl-shortcodes'); ?></li>
                            </ul>
                        </div>
                        <?php if ($type === 'reserve') : ?>
                            <div class="lgl-fbl-section" style="background:#fff8e1;border-color:#ffe082">
                                <h3><?php _e('Reserve Modes', 'lgl-shortcodes'); ?></h3>
                                <ul class="lgl-help-list">
                                    <li><strong>Auto Reserve</strong> — One click reserves immediately. No form. Button becomes "Reserved".</li>
                                    <li><strong>Form Only</strong> — Shows this form popup. Submission saved to database.</li>
                                    <li><strong>No Reserve Form</strong> — Button completely hidden.</li>
                                </ul>
                                <p style="font-size:12px;color:#666;margin-top:8px">Per-product override via the <strong>Vehicle Form Settings</strong> sidebar on each product.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php submit_button(__('Save Form', 'lgl-shortcodes')); ?>
            </form>
        </div>

        <script type="text/html" id="lgl-field-tpl">
            <?php $this->render_field_row('__I__', ['id' => '', 'label' => '', 'type' => 'text', 'required' => '', 'placeholder' => '', 'width' => 'half', 'options' => '']); ?>
        </script>
    <?php
    }

    /**
     * Renders a single field item row inside the builder repeater.
     */
    private function render_field_row($i, $f)
    {
        $types = ['text' => 'Text', 'email' => 'Email', 'tel' => 'Phone', 'number' => 'Number', 'date' => 'Date', 'time' => 'Time', 'textarea' => 'Textarea', 'select' => 'Select'];
        $cur   = $f['type'] ?? 'text';
        $opts_style = $cur === 'select' ? '' : 'display:none';
    ?>
        <div class="lgl-field-row" data-i="<?php echo esc_attr($i); ?>">
            <div class="lgl-field-handle" title="Drag to reorder"><span class="dashicons dashicons-menu"></span></div>
            <div class="lgl-field-body">
                <div class="lgl-field-top">
                    <div class="lgl-fg">
                        <label><?php _e('Label', 'lgl-shortcodes'); ?></label>
                        <input type="text" name="fields[<?php echo esc_attr($i); ?>][label]" value="<?php echo esc_attr($f['label'] ?? ''); ?>" placeholder="Field Label" class="lgl-label-inp" style="min-width:150px">
                    </div>
                    <div class="lgl-fg">
                        <label><?php _e('ID / Name', 'lgl-shortcodes'); ?></label>
                        <input type="text" name="fields[<?php echo esc_attr($i); ?>][id]" value="<?php echo esc_attr($f['id'] ?? ''); ?>" placeholder="auto" class="lgl-id-inp" style="min-width:120px;font-family:monospace;font-size:12px">
                    </div>
                    <div class="lgl-fg">
                        <label><?php _e('Type', 'lgl-shortcodes'); ?></label>
                        <select name="fields[<?php echo esc_attr($i); ?>][type]" class="lgl-type-sel">
                            <?php foreach ($types as $v => $l) : ?>
                                <option value="<?php echo esc_attr($v); ?>" <?php selected($cur, $v); ?>><?php echo esc_html($l); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="lgl-fg">
                        <label><?php _e('Width', 'lgl-shortcodes'); ?></label>
                        <select name="fields[<?php echo esc_attr($i); ?>][width]">
                            <option value="half" <?php selected($f['width'] ?? 'half', 'half'); ?>>Half</option>
                            <option value="full" <?php selected($f['width'] ?? '', 'full'); ?>>Full</option>
                        </select>
                    </div>
                    <div class="lgl-fg lgl-fg--cb">
                        <label><input type="checkbox" name="fields[<?php echo esc_attr($i); ?>][required]" value="1" <?php checked(! empty($f['required'])); ?>> <?php _e('Required', 'lgl-shortcodes'); ?></label>
                    </div>
                    <div class="lgl-field-actions">
                        <button type="button" class="lgl-toggle-field button-link button-small" title="Toggle"><span class="dashicons dashicons-arrow-up-alt2"></span></button>
                        <button type="button" class="lgl-clone-field button-link button-small" title="Duplicate"><span class="dashicons dashicons-admin-page"></span></button>
                        <button type="button" class="lgl-rm-field button-link button-small" title="Remove"><span class="dashicons dashicons-trash"></span></button>
                    </div>
                </div>
                <div class="lgl-field-bottom">
                    <div class="lgl-fg lgl-fg--wide">
                        <label><?php _e('Placeholder', 'lgl-shortcodes'); ?></label>
                        <input type="text" name="fields[<?php echo esc_attr($i); ?>][placeholder]" value="<?php echo esc_attr($f['placeholder'] ?? ''); ?>">
                    </div>
                    <div class="lgl-fg lgl-fg--opts" style="<?php echo $opts_style; ?>">
                        <label><?php _e('Options (one per line)', 'lgl-shortcodes'); ?></label>
                        <textarea name="fields[<?php echo esc_attr($i); ?>][options]" rows="3"><?php echo esc_textarea($f['options'] ?? ''); ?></textarea>
                    </div>
                </div>
            </div>
        </div>
    <?php
    }

    /* ═══════════════════════════════════════════════════════════════
       SAVE SETTINGS
    ═══════════════════════════════════════════════════════════════ */

    public function save_finance()
    {
        check_admin_referer('lgl_save_finance_form');
        if (! current_user_can('manage_options')) wp_die('Unauthorized');
        update_option('lgl_finance_form', [
            'button_text'  => sanitize_text_field($_POST['button_text']  ?? 'Finance Calculator'),
            'title'        => sanitize_text_field($_POST['title']        ?? ''),
            'subtitle'     => sanitize_text_field($_POST['subtitle']     ?? ''),
            'apr'          => sanitize_text_field($_POST['apr']          ?? '10.90'),
            'purchase_fee' => sanitize_text_field($_POST['purchase_fee'] ?? '10'),
            'durations'    => sanitize_textarea_field($_POST['durations']    ?? ''),
            'disclaimer'   => sanitize_textarea_field($_POST['disclaimer']   ?? ''),
        ]);
        
        wp_redirect(admin_url('admin.php?page=lgl-form-builder&tab=finance&saved=1'));
        exit;
    }

    public function save_enquiry()
    {
        check_admin_referer('lgl_save_enquiry_form');
        if (! current_user_can('manage_options')) wp_die('Unauthorized');
        update_option('lgl_enquiry_form', [
            'button_text'     => sanitize_text_field($_POST['button_text']  ?? 'Enquire Now'),
            'title'           => sanitize_text_field($_POST['title']        ?? ''),
            'submit_text'     => sanitize_text_field($_POST['submit_text']  ?? ''),
            'success_message' => sanitize_textarea_field($_POST['success_message'] ?? ''),
            'fields'          => $this->parse_fields(),
        ]);
        
        wp_redirect(admin_url('admin.php?page=lgl-form-builder&tab=enquiry&saved=1'));
        exit;
    }

    public function save_reserve()
    {
        check_admin_referer('lgl_save_reserve_form');
        if (! current_user_can('manage_options')) wp_die('Unauthorized');
        $allowed_modes = ['auto_reserve', 'form_only', 'no_reserve'];
        $mode = sanitize_text_field($_POST['default_reserve_mode'] ?? 'form_only');
        update_option('lgl_reserve_form', [
            'button_text'          => sanitize_text_field($_POST['button_text']          ?? 'Reserve Now'),
            'reserved_button_text' => sanitize_text_field($_POST['reserved_button_text'] ?? 'Reserved'),
            'title'                => sanitize_text_field($_POST['title']                ?? ''),
            'submit_text'          => sanitize_text_field($_POST['submit_text']          ?? ''),
            'success_message'      => sanitize_textarea_field($_POST['success_message']     ?? ''),
            'default_reserve_mode' => in_array($mode, $allowed_modes, true) ? $mode : 'form_only',
            'fields'               => $this->parse_fields(),
        ]);
        
        wp_redirect(admin_url('admin.php?page=lgl-form-builder&tab=reserve&saved=1'));
        exit;
    }

    private function parse_fields()
    {
        $out = [];
        $raw = $_POST['fields'] ?? [];
        if (! is_array($raw)) return $out;
        foreach ($raw as $f) {
            $label = sanitize_text_field($f['label'] ?? '');
            if (! $label) continue;
            $out[] = [
                'id'          => sanitize_key($f['id'] ?? sanitize_title($label)),
                'label'       => $label,
                'type'        => sanitize_text_field($f['type']        ?? 'text'),
                'required'    => ! empty($f['required']) ? '1' : '',
                'placeholder' => sanitize_text_field($f['placeholder'] ?? ''),
                'width'       => in_array($f['width'] ?? '', ['half', 'full']) ? $f['width'] : 'half',
                'options'     => sanitize_textarea_field($f['options'] ?? ''),
            ];
        }
        return $out;
    }

    /* ═══════════════════════════════════════════════════════════════
       PER-PRODUCT META BOX
    ═══════════════════════════════════════════════════════════════ */

    public function add_product_meta_box()
    {
        $types = ['caravan', 'motorhome', 'campervan'];
        foreach ($types as $t) {
            add_meta_box('lgl_vehicle_forms', __('Vehicle Form Settings', 'lgl-shortcodes'), [$this, 'render_product_meta_box'], $t, 'side', 'default');
        }
    }

    public function render_product_meta_box($post)
    {
        wp_nonce_field('lgl_vehicle_forms_meta', 'lgl_vf_nonce');
        $reserve_mode = get_post_meta($post->ID, '_lgl_reserve_mode', true);
        $is_reserved  = get_post_meta($post->ID, '_lgl_is_reserved',  true);
        $reserved_at  = get_post_meta($post->ID, '_lgl_reserved_at',  true);
        $rs = get_option('lgl_reserve_form', []);
        if (! $reserve_mode) $reserve_mode = $rs['default_reserve_mode'] ?? 'form_only';
    ?>
        <div style="margin-bottom:14px">
            <label style="display:block;font-weight:600;margin-bottom:5px"><?php _e('Reserve Mode', 'lgl-shortcodes'); ?></label>
            <select name="lgl_reserve_mode" style="width:100%" id="lgl-vf-mode">
                <option value="auto_reserve" <?php selected($reserve_mode, 'auto_reserve'); ?>><?php _e('⚡ Auto Reserve', 'lgl-shortcodes'); ?></option>
                <option value="form_only" <?php selected($reserve_mode, 'form_only'); ?>><?php _e('📋 Form Only', 'lgl-shortcodes'); ?></option>
                <option value="no_reserve" <?php selected($reserve_mode, 'no_reserve'); ?>><?php _e('🚫 No Reserve Form', 'lgl-shortcodes'); ?></option>
            </select>
            <div id="lgl-vf-desc" style="font-size:11px;color:#666;margin-top:5px;line-height:1.4;padding:6px;background:#f9f9f9;border-radius:3px;border:1px solid #e0e0e0">
            </div>
        </div>
        <?php if ($is_reserved) : ?>
            <div style="border:1px solid #d63638;padding:8px;border-radius:3px;margin-bottom:12px;background:#fceaeb">
                <span style="display:inline-block;background:#d63638;color:#fff;padding:2px 8px;border-radius:3px;font-size:11px;font-weight:700">RESERVED</span>
                <?php if ($reserved_at) : ?>
                    <p style="margin:4px 0 0;font-size:11px;color:#777"><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($reserved_at))); ?></p>
                <?php endif; ?>
                <p style="margin:6px 0 0"><label style="font-size:11px;font-weight:600"><input type="checkbox" name="lgl_clear_reservation" value="1"> <?php _e('Clear reservation', 'lgl-shortcodes'); ?></label></p>
            </div>
        <?php endif; ?>
        <script>
            (function($) {
                var descs = {
                    auto_reserve: "Opens the reservation form popup. Submission saved to database and disable the reserve popup.",
                    form_only: "Opens the reservation form popup. Submission saved to the database.",
                    no_reserve: "Reserve button is completely hidden on this product."
                };

                function upd() {
                    $('#lgl-vf-desc').text(descs[$('#lgl-vf-mode').val()] || '');
                }
                $('#lgl-vf-mode').on('change', upd);
                upd();
            })(jQuery);
        </script>
<?php
    }

    public function save_product_meta($post_id, $post)
    {
        if (! isset($_POST['lgl_vf_nonce'])) return;
        if (! wp_verify_nonce($_POST['lgl_vf_nonce'], 'lgl_vehicle_forms_meta')) return;
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (! current_user_can('edit_post', $post_id)) return;
        if (! in_array($post->post_type, ['caravan', 'motorhome', 'campervan'], true)) return;

        if (isset($_POST['lgl_reserve_mode'])) {
            $allowed = ['auto_reserve', 'form_only', 'no_reserve'];
            $mode    = sanitize_text_field($_POST['lgl_reserve_mode']);
            if (in_array($mode, $allowed, true)) {
                update_post_meta($post_id, '_lgl_reserve_mode', $mode);
            }
        }
        if (! empty($_POST['lgl_clear_reservation'])) {
            delete_post_meta($post_id, '_lgl_is_reserved');
            delete_post_meta($post_id, '_lgl_reserved_at');
        }
    }

    /* ═══════════════════════════════════════════════════════════════
       SUBMISSION LIST TABLE COLUMNS
    ═══════════════════════════════════════════════════════════════ */

    public function enquiry_columns($cols)
    {
        return ['cb' => $cols['cb'], 'title' => __('Submission', 'lgl-shortcodes'), 'lgl_product' => __('Product', 'lgl-shortcodes'), 'lgl_name' => __('Name', 'lgl-shortcodes'), 'lgl_email' => __('Email', 'lgl-shortcodes'), 'lgl_phone' => __('Phone', 'lgl-shortcodes'), 'date' => __('Date', 'lgl-shortcodes')];
    }

    public function enquiry_col_data($col, $post_id)
    {
        $data = get_post_meta($post_id, '_lgl_form_data', true) ?: [];
        $this->render_common_col($col, $post_id, $data);
    }

    public function reserve_columns($cols)
    {
        return ['cb' => $cols['cb'], 'title' => __('Submission', 'lgl-shortcodes'), 'lgl_product' => __('Product', 'lgl-shortcodes'), 'lgl_name' => __('Name', 'lgl-shortcodes'), 'lgl_email' => __('Email', 'lgl-shortcodes'), 'lgl_phone' => __('Phone', 'lgl-shortcodes'), 'lgl_status' => __('Status', 'lgl-shortcodes'), 'lgl_mode' => __('Mode', 'lgl-shortcodes'), 'date' => __('Date', 'lgl-shortcodes')];
    }

    public function reserve_col_data($col, $post_id)
    {
        $data = get_post_meta($post_id, '_lgl_form_data', true) ?: [];
        if ('lgl_status' === $col) {
            $s = get_post_meta($post_id, '_lgl_reserve_status', true) ?: 'pending';
            $badges = ['pending' => 'lgl-sub-pending', 'confirmed' => 'lgl-sub-confirmed', 'cancelled' => 'lgl-sub-cancelled', 'auto' => 'lgl-sub-auto'];
            $cls = $badges[$s] ?? 'lgl-sub-pending';
            echo '<span class="lgl-sub-badge ' . esc_attr($cls) . '">' . esc_html(ucfirst($s)) . '</span>';
            return;
        }
        if ('lgl_mode' === $col) {
            $m = get_post_meta($post_id, '_lgl_reserve_mode_sub', true);
            echo esc_html(ucwords(str_replace('_', ' ', $m)));
            return;
        }
        $this->render_common_col($col, $post_id, $data);
    }

    private function render_common_col($col, $post_id, $data)
    {
        if ('lgl_product' === $col) {
            $pid = get_post_meta($post_id, '_lgl_product_id', true);
            echo $pid ? '<a href="' . esc_url(get_edit_post_link($pid)) . '">' . esc_html(get_the_title($pid)) . '</a>' : '—';
        } elseif ('lgl_name' === $col) {
            echo esc_html(trim(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? ''))) ?: '—';
        } elseif ('lgl_email' === $col) {
            echo esc_html($data['email'] ?? '—');
        } elseif ('lgl_phone' === $col) {
            echo esc_html($data['phone'] ?? '—');
        }
    }

    /* ═══════════════════════════════════════════════════════════════
       SUBMISSION DETAIL META BOXES
    ═══════════════════════════════════════════════════════════════ */

    public function add_submission_meta_boxes()
    {
        add_meta_box('lgl_sub_detail', __('Submission Details', 'lgl-shortcodes'), [$this, 'render_sub_detail'], 'lgl_enquiry_sub', 'normal', 'high');
        add_meta_box('lgl_sub_detail', __('Reservation Details', 'lgl-shortcodes'), [$this, 'render_sub_detail'], 'lgl_reserve_sub', 'normal', 'high');
        add_meta_box('lgl_sub_status', __('Reservation Status', 'lgl-shortcodes'), [$this, 'render_reserve_status_box'], 'lgl_reserve_sub', 'side', 'high');
    }

    public function render_sub_detail($post)
    {
        $data       = get_post_meta($post->ID, '_lgl_form_data', true) ?: [];
        $product_id = get_post_meta($post->ID, '_lgl_product_id', true);
        $mode       = get_post_meta($post->ID, '_lgl_reserve_mode_sub', true);
        if ($mode) echo '<p><strong>' . __('Reserve Mode:', 'lgl-shortcodes') . '</strong> ' . esc_html(ucwords(str_replace('_', ' ', $mode))) . '</p>';
        if ($product_id) echo '<p><strong>' . __('Linked Product:', 'lgl-shortcodes') . '</strong> <a href="' . esc_url(get_edit_post_link($product_id)) . '">' . esc_html(get_the_title($product_id)) . '</a> &nbsp;<a href="' . esc_url(get_permalink($product_id)) . '" target="_blank">(view)</a></p>';
        if (! $data) {
            echo '<p><em>' . __('No data recorded.', 'lgl-shortcodes') . '</em></p>';
            return;
        }
        echo '<table class="widefat lgl-sub-detail-table"><tbody>';
        foreach ($data as $k => $v) {
            echo '<tr><th>' . esc_html(ucwords(str_replace('_', ' ', $k))) . '</th><td>' . esc_html($v) . '</td></tr>';
        }
        echo '</tbody></table>';
    }

    public function render_reserve_status_box($post)
    {
        $status = get_post_meta($post->ID, '_lgl_reserve_status', true) ?: 'pending';
        echo '<p><label><strong>' . __('Status', 'lgl-shortcodes') . '</strong></label><br>';
        echo '<select name="_lgl_reserve_status" style="width:100%;margin-top:4px">';
        foreach (['pending' => 'Pending', 'confirmed' => 'Confirmed', 'cancelled' => 'Cancelled', 'auto' => 'Auto Reserved'] as $v => $l) {
            echo '<option value="' . esc_attr($v) . '"' . selected($status, $v, false) . '>' . esc_html($l) . '</option>';
        }
        echo '</select></p>';
        add_action('save_post_lgl_reserve_sub', function ($pid) {
            if (isset($_POST['_lgl_reserve_status'])) update_post_meta($pid, '_lgl_reserve_status', sanitize_text_field($_POST['_lgl_reserve_status']));
        });
    }

    /* ═══════════════════════════════════════════════════════════════
       AJAX HANDLERS
    ═══════════════════════════════════════════════════════════════ */

    public function ajax_submit_enquiry()
    {
        if (! wp_verify_nonce($_POST['lgl_forms_nonce'] ?? '', 'lgl_forms_nonce')) {
            wp_send_json_error(['message' => __('Security check failed.', 'lgl-shortcodes')]);
        }
        $product_id = absint($_POST['product_id'] ?? 0);
        $s          = get_option('lgl_enquiry_form', $this->default_enquiry());
        $fields     = $s['fields'] ?? [];
        [$data, $errors] = $this->collect_and_validate($fields);
        if ($errors) wp_send_json_error(['message' => implode(' ', $errors)]);

        $name    = trim(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? ''));
        $post_id = wp_insert_post(['post_type' => 'lgl_enquiry_sub', 'post_title' => sprintf('Enquiry: %s — %s', $name ?: 'Unknown', current_time('Y-m-d H:i')), 'post_status' => 'publish']);
        if (is_wp_error($post_id)) wp_send_json_error(['message' => __('Could not save submission.', 'lgl-shortcodes')]);

        update_post_meta($post_id, '_lgl_form_data',   $data);
        update_post_meta($post_id, '_lgl_product_id',  $product_id);
        update_post_meta($post_id, '_lgl_submitted_at', current_time('mysql'));
        
        if (class_exists('LGL_Email_Builder')) {
            LGL_Email_Builder::send('enquiry', $data, $product_id);
        }

        wp_send_json_success(['message' => $s['success_message'] ?: __('Thank you for your enquiry. We will be in touch shortly.', 'lgl-shortcodes')]);
    }

    public function ajax_submit_reserve()
    {
        if (! wp_verify_nonce($_POST['lgl_forms_nonce'] ?? '', 'lgl_forms_nonce')) {
            wp_send_json_error(['message' => __('Security check failed.', 'lgl-shortcodes')]);
        }
        $product_id = absint($_POST['product_id'] ?? 0);
        if ($product_id && get_post_meta($product_id, '_lgl_is_reserved', true)) {
            wp_send_json_error(['message' => __('Sorry, this vehicle has already been reserved.', 'lgl-shortcodes')]);
        }
        $s      = get_option('lgl_reserve_form', $this->default_reserve());
        $fields = $s['fields'] ?? [];
        [$data, $errors] = $this->collect_and_validate($fields);
        if ($errors) wp_send_json_error(['message' => implode(' ', $errors)]);

        $name    = trim(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? ''));
        $post_id = wp_insert_post(['post_type' => 'lgl_reserve_sub', 'post_title' => sprintf('Reservation: %s — %s', $name ?: 'Unknown', current_time('Y-m-d H:i')), 'post_status' => 'publish']);
        if (is_wp_error($post_id)) wp_send_json_error(['message' => __('Could not save reservation.', 'lgl-shortcodes')]);

        $actual_mode = self::get_current_reserve_mode($product_id);

        update_post_meta($post_id, '_lgl_form_data',        $data);
        update_post_meta($post_id, '_lgl_product_id',       $product_id);
        update_post_meta($post_id, '_lgl_submitted_at',     current_time('mysql'));
        update_post_meta($post_id, '_lgl_reserve_mode_sub', $actual_mode);

        if ($actual_mode === 'auto_reserve') {
            update_post_meta($post_id, '_lgl_reserve_status', 'auto');
            if ($product_id) {
                update_post_meta($product_id, '_lgl_is_reserved', '1');
                update_post_meta($product_id, '_lgl_reserved_at', current_time('mysql'));
            }
        } else {
            update_post_meta($post_id, '_lgl_reserve_status', 'pending');
        }
        
        if (class_exists('LGL_Email_Builder')) {
            LGL_Email_Builder::send('reserve', $data, $product_id);
        }

        $rs = get_option('lgl_reserve_form', []);
        wp_send_json_success([
            'message'         => $s['success_message'] ?: __('Thank you. Your reservation has been received.', 'lgl-shortcodes'),
            'reserved_btn'    => $rs['reserved_button_text'] ?? __('Reserved', 'lgl-shortcodes'),
        ]);
    }

    private function collect_and_validate($fields)
    {
        $data   = [];
        $errors = [];
        foreach ($fields as $f) {
            $key = 'lgl_f_' . $f['id'];
            $val = $f['type'] === 'textarea'
                ? sanitize_textarea_field($_POST[$key] ?? '')
                : sanitize_text_field($_POST[$key] ?? '');
            if ($f['required'] && '' === $val) $errors[] = sprintf(__('%s is required.', 'lgl-shortcodes'), $f['label']);
            if ($f['type'] === 'email' && $val && ! is_email($val)) $errors[] = __('Please enter a valid email address.', 'lgl-shortcodes');
            $data[$f['id']] = $val;
        }
        return [$data, $errors];
    }

    /* ═══════════════════════════════════════════════════════════════
       FRONTEND — LOCALIZE DATA
    ═══════════════════════════════════════════════════════════════ */

    public function localize_forms_data()
    {
        if (! is_singular(['caravan', 'motorhome', 'campervan']) && ! is_singular()) return;

        $post_id      = get_the_ID();
        $fin          = get_option('lgl_finance_form', []);
        $rs           = get_option('lgl_reserve_form', $this->default_reserve());
        $reserve_mode = get_post_meta($post_id, '_lgl_reserve_mode', true);
        $is_reserved  = (bool) get_post_meta($post_id, '_lgl_is_reserved', true);
        $cash_price   = (float) get_post_meta($post_id, 'price', true);
        if (! $reserve_mode) $reserve_mode = $rs['default_reserve_mode'] ?? 'form_only';

        $dur_raw   = $fin['durations'] ?? "1 Year\n2 Years\n3 Years\n4 Years\n5 Years";
        $durations = array_values(array_filter(array_map('trim', explode("\n", $dur_raw))));

        wp_localize_script('lgl-forms-js', 'lglForms', [
            'ajaxUrl'         => admin_url('admin-ajax.php'),
            'nonce'           => wp_create_nonce('lgl_forms_nonce'),
            'productId'       => $post_id,
            'cashPrice'       => $cash_price,
            'reserveMode'     => $reserve_mode,
            'isReserved'      => $is_reserved,
            'reservedBtnText' => $rs['reserved_button_text'] ?? __('Reserved', 'lgl-shortcodes'),
            'reserveBtnText'  => $rs['button_text'] ?? __('Reserve Now', 'lgl-shortcodes'),
            'apr'             => (float) ($fin['apr'] ?? 10.90),
            'purchaseFee'     => (float) ($fin['purchase_fee'] ?? 10),
            'durations'       => $durations,
        ]);
    }

    /* ═══════════════════════════════════════════════════════════════
       FRONTEND — RENDER MODALS IN FOOTER
    ═══════════════════════════════════════════════════════════════ */

    public function render_modals()
    {
        if (! is_singular(['caravan', 'motorhome', 'campervan'])) return;
        include LGL_SHORTCODES_PATH . 'templates/partials/lgl-modals.php';
    }

    /* ═══════════════════════════════════════════════════════════════
       PUBLIC HELPERS (used by lgl-modals.php and single-lgl.php)
    ═══════════════════════════════════════════════════════════════ */

    public static function get_finance_settings()
    {
        return get_option('lgl_finance_form', []);
    }

    public static function get_enquiry_settings()
    {
        $instance = new self;
        return get_option('lgl_enquiry_form', $instance->default_enquiry());
    }

    public static function get_reserve_settings()
    {
        $instance = new self;
        return get_option('lgl_reserve_form', $instance->default_reserve());
    }

    public static function get_current_reserve_mode($post_id)
    {
        $mode = get_post_meta($post_id, '_lgl_reserve_mode', true);
        if (! $mode) {
            $rs   = get_option('lgl_reserve_form', []);
            $mode = $rs['default_reserve_mode'] ?? 'form_only';
        }
        return $mode;
    }

    public static function is_reserved($post_id)
    {
        return (bool) get_post_meta($post_id, '_lgl_is_reserved', true);
    }

    public static function render_form_field($field, $prefix = 'lgl_f_')
    {
        $id    = 'lglf-' . esc_attr($field['id']);
        $name  = $prefix . esc_attr($field['id']);
        $req   = ! empty($field['required']);
        $ph    = esc_attr($field['placeholder'] ?? '');
        $type  = esc_attr($field['type'] ?? 'text');
        $width = ($field['width'] ?? 'half') === 'full' ? 'lgl-form-col-full' : 'lgl-form-col-half';
        $ra    = $req ? ' required' : '';
        $rl    = $req ? ' <span class="lgl-form-req">(Required)</span>' : '';

        $out  = '<div class="lgl-form-field ' . $width . '">';
        $out .= '<label for="' . $id . '">' . esc_html($field['label']) . $rl . '</label>';

        if ('textarea' === $field['type']) {
            $out .= '<textarea id="' . $id . '" name="' . $name . '" placeholder="' . $ph . '"' . $ra . '></textarea>';
        } elseif ('select' === $field['type']) {
            $opts = array_filter(array_map('trim', explode("\n", $field['options'] ?? '')));
            $out .= '<select id="' . $id . '" name="' . $name . '"' . $ra . '>';
            $out .= '<option value="">— Select —</option>';
            foreach ($opts as $o) $out .= '<option value="' . esc_attr($o) . '">' . esc_html($o) . '</option>';
            $out .= '</select>';
        } elseif ('time' === $field['type']) {
            $out .= '<div class="lgl-time-picker">'
                . '<input type="number" name="' . $name . '_hh" placeholder="HH" min="1" max="12" class="lgl-time-hh"' . $ra . '>'
                . '<span class="lgl-time-sep">:</span>'
                . '<input type="number" name="' . $name . '_mm" placeholder="MM" min="0" max="59" class="lgl-time-mm">'
                . '<select name="' . $name . '_ampm" class="lgl-time-ampm"><option>AM</option><option>PM</option></select>'
                . '<input type="hidden" name="' . $name . '" class="lgl-time-val">'
                . '</div>';
        } else {
            $out .= '<input type="' . $type . '" id="' . $id . '" name="' . $name . '" placeholder="' . $ph . '"' . $ra . '>';
        }
        $out .= '</div>';
        return $out;
    }

    /* ═══════════════════════════════════════════════════════════════
       DEFAULTS
    ═══════════════════════════════════════════════════════════════ */

    private function default_enquiry()
    {
        return [
            'button_text'     => 'ENQUIRE NOW',
            'title'           => 'Make an Enquiry',
            'submit_text'     => 'SUBMIT ENQUIRY',
            'success_message' => 'Thank you for your enquiry. We will be in touch shortly.',
            'fields'          => [
                ['id' => 'first_name', 'label' => 'First Name', 'type' => 'text',     'required' => '1', 'placeholder' => 'Enter your first name', 'width' => 'half', 'options' => ''],
                ['id' => 'last_name',  'label' => 'Last Name',  'type' => 'text',     'required' => '1', 'placeholder' => 'Enter your last name',  'width' => 'half', 'options' => ''],
                ['id' => 'email',      'label' => 'Email',      'type' => 'email',    'required' => '1', 'placeholder' => 'Enter your email',      'width' => 'half', 'options' => ''],
                ['id' => 'phone',      'label' => 'Phone',      'type' => 'tel',      'required' => '1', 'placeholder' => 'Enter your number',      'width' => 'half', 'options' => ''],
                ['id' => 'enquiry',    'label' => 'Enquiry',    'type' => 'textarea', 'required' => '',  'placeholder' => '',                       'width' => 'full', 'options' => ''],
            ],
        ];
    }

    private function default_reserve()
    {
        return [
            'button_text'          => 'RESERVE NOW',
            'reserved_button_text' => 'Reserved',
            'title'                => 'Reserve this Leisure Vehicle for free',
            'submit_text'          => 'RESERVE YOUR LEISURE VEHICLE',
            'success_message'      => 'Almost done... we will reserve the Leisure Vehicle for 5 days; a member of our sales team will be in touch with you shortly to confirm your viewing.',
            'default_reserve_mode' => 'form_only',
            'fields'               => [
                ['id' => 'first_name', 'label' => 'First Name',            'type' => 'text',     'required' => '1', 'placeholder' => 'Enter your first name', 'width' => 'half', 'options' => ''],
                ['id' => 'last_name',  'label' => 'Last Name',             'type' => 'text',     'required' => '1', 'placeholder' => 'Enter your last name',  'width' => 'half', 'options' => ''],
                ['id' => 'email',      'label' => 'Email',                 'type' => 'email',    'required' => '1', 'placeholder' => 'Enter your email',      'width' => 'half', 'options' => ''],
                ['id' => 'phone',      'label' => 'Phone',                 'type' => 'tel',      'required' => '1', 'placeholder' => 'Enter your number',      'width' => 'half', 'options' => ''],
                ['id' => 'address_1',  'label' => 'Address 1',             'type' => 'text',     'required' => '1', 'placeholder' => 'Enter Address 1',       'width' => 'half', 'options' => ''],
                ['id' => 'address_2',  'label' => 'Address 2',             'type' => 'text',     'required' => '',  'placeholder' => 'Enter Address 2',       'width' => 'half', 'options' => ''],
                ['id' => 'city',       'label' => 'City',                  'type' => 'text',     'required' => '1', 'placeholder' => 'Enter City',            'width' => 'half', 'options' => ''],
                ['id' => 'postcode',   'label' => 'Postcode',              'type' => 'text',     'required' => '1', 'placeholder' => 'Enter Postcode',        'width' => 'half', 'options' => ''],
                ['id' => 'visit_date', 'label' => 'Date of Planned Visit', 'type' => 'date',     'required' => '1', 'placeholder' => '',                      'width' => 'half', 'options' => ''],
                ['id' => 'visit_time', 'label' => 'Time of Planned Visit', 'type' => 'time',     'required' => '1', 'placeholder' => '',                      'width' => 'half', 'options' => ''],
                ['id' => 'comments',   'label' => 'Additional Comments',   'type' => 'textarea', 'required' => '',  'placeholder' => '',                      'width' => 'full', 'options' => ''],
            ],
        ];
    }
}