<?php

/**
 * Plugin Name: LGL Email Builder
 * Author: Digitally Disruptive - Donald Raymundo
 * Author URI: https://digitallydisruptive.co.uk/
 * Description: Visual email template editor with merge tag support and global theming.
 */

if (! defined('ABSPATH')) exit;

class LGL_Email_Builder
{

    /* ═══════════════════════════════════════════════════════════════
       BOOT
    ═══════════════════════════════════════════════════════════════ */

    /**
     * Initialize WordPress hooks for menu generation, assets, and form processing.
     * Priority set to 10 to render above Submissions.
     *
     * @return void
     */
    public function __construct()
    {
        add_action('admin_menu',            [$this, 'add_submenu_pages'], 10);
        add_action('admin_enqueue_scripts', [$this, 'admin_assets']);
        add_action('admin_post_lgl_save_enquiry_email', [$this, 'save_email_settings']);
        add_action('admin_post_lgl_save_reserve_email', [$this, 'save_email_settings']);
        add_action('admin_post_lgl_save_global_email',  [$this, 'save_global_email_settings']);

        // ── Global template: apply to ALL outgoing wp_mail calls when enabled ──
        add_filter('wp_mail', [$this, 'maybe_apply_global_template'], 10, 1);
    }

    /* ═══════════════════════════════════════════════════════════════
       wp_mail FILTER — Apply global template to all site emails
    ═══════════════════════════════════════════════════════════════ */

    /**
     * Intercepts all outgoing wp_mail() calls and wraps the body inside the global
     * LGL email template (header, footer, colours) when "Apply to all site emails"
     * is enabled in LGL → Email Builder → Global Template.
     *
     * The filter is deliberately conservative:
     *  - Skips emails that are already fully-formed HTML documents (<!DOCTYPE / <html).
     *  - Skips emails sent by LGL itself (they are already wrapped by send()).
     *  - Always sets Content-Type to text/html so clients render correctly.
     *
     * @param array $args  wp_mail argument array: to, subject, message, headers, attachments.
     * @return array       Potentially modified argument array.
     */
    public function maybe_apply_global_template(array $args): array
    {
        $global = self::get_global_email_settings();

        // Feature disabled — pass through untouched
        if (empty($global['apply_to_all_emails'])) {
            return $args;
        }

        $subject = $args['subject'] ?? '';
        $body    = $args['message'] ?? '';

        // Skip emails that are already a complete HTML document (LGL's own emails,
        // WooCommerce, etc. that wrap themselves).
        if (stripos($body, '<!DOCTYPE') !== false || stripos($body, '<html') !== false) {
            return $args;
        }

        // Wrap the body in the global template
        $args['message'] = self::wrap_html($subject, $body);

        // Ensure the email is sent as HTML
        $headers = $args['headers'] ?? [];
        if (is_string($headers)) {
            $headers = array_filter(array_map('trim', explode("\n", $headers)));
        }

        $has_content_type = false;
        foreach ((array) $headers as $header) {
            if (stripos($header, 'content-type') !== false) {
                $has_content_type = true;
                break;
            }
        }

        if (! $has_content_type) {
            $headers[] = 'Content-Type: text/html; charset=UTF-8';
        }

        $args['headers'] = $headers;

        return $args;
    }

    /* ═══════════════════════════════════════════════════════════════
       MENU ROUTING
    ═══════════════════════════════════════════════════════════════ */
    /**
     * Register the consolidated master submenu page for the Email Builder.
     *
     * @return void
     */
    public function add_submenu_pages()
    {
        add_submenu_page(
            'lgl-settings',
            __('Email Builder', 'lgl-shortcodes'),
            __('Email Builder', 'lgl-shortcodes'),
            'manage_options',
            'lgl-email-builder',
            [$this, 'render_master_email_builder_page']
        );
    }

    /* ═══════════════════════════════════════════════════════════════
       ASSETS
    ═══════════════════════════════════════════════════════════════ */

    /**
     * Enqueue inline CSS and JS strictly on the master email builder screen.
     *
     * @param string $hook The current admin page hook.
     * @return void
     */
    public function admin_assets($hook)
    {
        if ($hook !== 'lgl-settings_page_lgl-email-builder') return;

        wp_add_inline_style('wp-admin', $this->admin_css());

        // Build contact tag preview values from the single source of truth.
        // Uses saved lgl_settings values when available, falls back to the field label.
        $contact_placeholders = [];
        if (class_exists('LGL_Shortcodes')) {
            $lgl_options = get_option('lgl_settings', []);
            foreach (LGL_Shortcodes::get_contact_fields_definition() as $key => $field) {
                $contact_placeholders[$key] = ! empty($lgl_options[$key])
                    ? $lgl_options[$key]
                    : '[' . $field['label'] . ']';
            }
        }

        // Inject before the main script so resolveTags can reference it.
        wp_add_inline_script('jquery', 'var lglContactTagPlaceholders = ' . wp_json_encode($contact_placeholders) . ';');
        wp_add_inline_script('jquery', $this->admin_js());
    }

    /**
     * Define the inline CSS required for the builder layout and 700px preview column.
     *
     * @return string Minified CSS payload.
     */
    private function admin_css(): string
    {
        return '
        /* ── Master Layout ── */
        .lgl-eb-wrap { max-width: 1600px; margin-top: 20px; }
        .lgl-eb-master-layout {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 700px; 
            gap: 30px;
            margin-top: 20px;
            align-items: start;
        }

        /* ── Email Builder Layout ── */
        .lgl-eb-layout { display: grid; grid-template-columns: 1fr 300px; gap: 24px; }

        /* ── Section cards ── */
        .lgl-eb-section {
            background: #fff;
            border: 1px solid #c3c4c7;
            border-radius: 4px;
            padding: 20px 24px;
            margin-bottom: 20px;
        }
        .lgl-eb-section h3 {
            margin: 0 0 16px;
            font-size: 14px;
            font-weight: 700;
            color: #1d2327;
            border-bottom: 1px solid #f0f0f1;
            padding-bottom: 10px;
        }
        .lgl-eb-section h4 {
            font-size: 13px;
            font-weight: 600;
            color: #3c434a;
            margin: 16px 0 8px;
        }

        /* ── Form rows ── */
        .lgl-eb-row { margin-bottom: 14px; }
        .lgl-eb-row label {
            display: block;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .4px;
            color: #646970;
            margin-bottom: 5px;
        }
        .lgl-eb-row input[type="text"],
        .lgl-eb-row input[type="email"],
        .lgl-eb-row select {
            width: 100%;
            padding: 8px 10px;
            border: 1px solid #c3c4c7;
            border-radius: 3px;
            font-size: 13px;
        }

        /* ── Apply-to-all notice ── */
        .lgl-eb-apply-all-section {
            background: #f0f6fc;
            border: 1px solid #72aee6;
        }
        .lgl-eb-apply-all-section h3 { color: #0a4b78; border-bottom-color: #c2dcf3; }
        .lgl-eb-apply-all-row {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 4px 0;
        }
        .lgl-eb-apply-all-row input[type="checkbox"] { margin-top: 3px; width: 16px; height: 16px; }
        .lgl-eb-apply-all-row label {
            font-size: 13px;
            font-weight: 600;
            text-transform: none;
            letter-spacing: 0;
            color: #1d2327;
            cursor: pointer;
        }
        .lgl-eb-apply-all-row p {
            margin: 4px 0 0;
            font-size: 12px;
            color: #50575e;
            line-height: 1.5;
        }

        /* ── Color Pickers ── */
        .lgl-eb-color-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 16px;
        }
        .lgl-eb-color-row {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .lgl-eb-color-row input[type="color"] {
            width: 100%;
            height: 36px;
            padding: 0;
            border: 1px solid #c3c4c7;
            border-radius: 3px;
            cursor: pointer;
        }

        /* ── Tag toolbar ── */
        .lgl-eb-tag-toolbar {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            padding: 8px 10px;
            background: #f6f7f7;
            border: 1px solid #c3c4c7;
            border-bottom: none;
            border-radius: 3px 3px 0 0;
        }
        .lgl-eb-tag-toolbar .lgl-tag-group-label {
            width: 100%;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .4px;
            color: #8c8f94;
            margin-bottom: 2px;
        }
        .lgl-eb-insert-tag {
            font-size: 11px;
            padding: 3px 8px;
            background: #fff;
            border: 1px solid #c3c4c7;
            border-radius: 2px;
            cursor: pointer;
            color: #1d2327;
            font-family: monospace;
            line-height: 1.4;
            transition: background .15s, border-color .15s;
        }
        .lgl-eb-insert-tag:hover {
            background: #2271b1;
            color: #fff;
            border-color: #2271b1;
        }
        .lgl-eb-textarea {
            width: 100%;
            min-height: 380px;
            font-family: monospace;
            font-size: 13px;
            padding: 12px;
            border: 1px solid #c3c4c7;
            border-radius: 0 0 3px 3px;
            resize: vertical;
            box-sizing: border-box;
            line-height: 1.6;
        }
        .lgl-eb-textarea.lgl-eb-textarea-small { min-height: 150px; }
        .lgl-eb-subject-wrap { position: relative; }
        .lgl-eb-subject-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
            margin-bottom: 6px;
        }

        /* ── Sticky Preview Panel ── */
        .lgl-eb-preview-sticky {
            position: sticky;
            top: 40px;
        }
        .lgl-eb-preview-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }
        .lgl-eb-preview-header h3 {
            margin: 0;
            border: none;
            padding: 0;
        }
        #lgl-eb-preview-frame,
        #lgl-global-preview-frame {
            width: 100%;
            height: calc(100vh - 120px);
            min-height: 600px;
            border: 1px solid #c3c4c7;
            border-radius: 3px;
            background: #fff;
            display: block;
        }

        /* ── Merge tag reference ── */
        .lgl-eb-tag-ref { list-style: none; margin: 0; padding: 0; }
        .lgl-eb-tag-ref li {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            padding: 5px 0;
            border-bottom: 1px solid #f0f0f1;
            font-size: 12px;
        }
        .lgl-eb-tag-ref li:last-child { border-bottom: none; }
        .lgl-eb-tag-ref code {
            background: #f6f7f7;
            padding: 2px 5px;
            border: 1px solid #e0e0e0;
            border-radius: 2px;
            font-size: 11px;
            cursor: pointer;
            color: #2271b1;
        }
        .lgl-eb-tag-ref code:hover { background: #2271b1; color: #fff; border-color: #2271b1; }
        .lgl-eb-tag-ref .lgl-tag-desc { color: #8c8f94; font-size: 11px; }

        /* ── Toggle for auto-reply ── */
        .lgl-eb-toggle-row {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 0;
        }
        .lgl-eb-toggle-row input[type="checkbox"] { width: 16px; height: 16px; }
        .lgl-eb-collapsible { display: none; }
        .lgl-eb-collapsible.is-open { display: block; }

        /* ── Recipient type ── */
        .lgl-eb-recipient-opts { display: flex; flex-direction: column; gap: 8px; }
        .lgl-eb-recipient-opts label { font-weight: 400; text-transform: none; letter-spacing: 0; font-size: 13px; display: flex; align-items: center; gap: 7px; cursor: pointer; }
        .lgl-eb-recipient-opts input[type="radio"] { width: 14px; height: 14px; }
        #lgl-custom-email-row { display: none; padding-left: 22px; }
        #lgl-custom-email-row.is-visible { display: block; margin-top: 8px; }

        /* ── Tabs ── */
        .lgl-eb-tabs { display: flex; gap: 0; border-bottom: 2px solid #c3c4c7; margin-bottom: 20px; }
        .lgl-eb-tab {
            padding: 10px 20px;
            cursor: pointer;
            font-weight: 600;
            font-size: 13px;
            color: #50575e;
            border-bottom: 3px solid transparent;
            margin-bottom: -2px;
            user-select: none;
        }
        .lgl-eb-tab.active { color: #2271b1; border-bottom-color: #2271b1; }
        .lgl-eb-tab-content { display: none; }
        .lgl-eb-tab-content.active { display: block; }

        /* ── Send test button ── */
        .lgl-eb-test-row { display: flex; gap: 8px; align-items: center; margin-top: 12px; }
        .lgl-eb-test-row input { flex: 1; max-width: 280px; }
        .lgl-eb-test-msg { font-size: 12px; margin-left: 8px; }
        .lgl-eb-test-msg.success { color: #00a32a; }
        .lgl-eb-test-msg.error { color: #d63638; }

        @media (max-width: 1400px) { 
            .lgl-eb-master-layout { grid-template-columns: 1fr; }
            #lgl-eb-preview-frame { height: 600px; }
            .lgl-eb-preview-sticky { position: static; }
        }
        @media (max-width: 1024px) { .lgl-eb-layout { grid-template-columns: 1fr; } }
        ';
    }

    /**
     * Define inline JavaScript for frontend DOM interactions and live preview color routing.
     *
     * @return string JavaScript payload.
     */
    private function admin_js(): string
    {
        return '
    (function($){

        // ── Inner UI Tab switching ────────────────────────────────────
        $(document).on("click", ".lgl-eb-tab", function(){
            var idx = $(this).index();
            $(this).addClass("active").siblings().removeClass("active");
            var $panels = $(this).closest(".lgl-eb-builder-column").find(".lgl-eb-tab-panels");
            $panels.find(".lgl-eb-tab-content").removeClass("active").eq(idx).addClass("active");
            
            setTimeout(function(){ $("#lgl-eb-preview-btn").trigger("click"); }, 50);
        });

        // ── Track last focused editor ─────────────────────────────────
        var $lastFocus = null;
        $(document).on("focus", ".lgl-eb-textarea, .lgl-eb-subject-input", function(){
            $lastFocus = $(this);
        });

        // ── Insert merge tag ──────────────────────────────────────────
        $(document).on("click", ".lgl-eb-insert-tag, .lgl-eb-tag-ref code", function(e){
            e.preventDefault();
           if ($(this).hasClass("lgl-global-tag")) return;
    var tag = $(this).data("tag") || $(this).text().trim();
    if (!$lastFocus || !$lastFocus.length) {
        $lastFocus = $(".lgl-eb-tab-content.active .lgl-eb-textarea:first");
    }
            if (!$lastFocus.length) return;
            var el = $lastFocus[0];
            var start = el.selectionStart, end = el.selectionEnd;
            var val = el.value;
            el.value = val.substring(0, start) + tag + val.substring(end);
            el.selectionStart = el.selectionEnd = start + tag.length;
            el.focus();
            
            $("#lgl-eb-preview-btn").trigger("click");
        });

        // ── Auto-reply toggle ─────────────────────────────────────────
        $(document).on("change", "#lgl-eb-auto-reply-toggle", function(){
            $(this).is(":checked")
                ? $("#lgl-eb-autoreply-section").addClass("is-open")
                : $("#lgl-eb-autoreply-section").removeClass("is-open");
        });

        // ── Recipient type ────────────────────────────────────────────
        $(document).on("change", "[name=\'recipient_type\']", function(){
            var val = $(this).val();
            (val === "custom" || val === "both")
                ? $("#lgl-custom-email-row").addClass("is-visible")
                : $("#lgl-custom-email-row").removeClass("is-visible");
        });

        // ── Shared tag helpers ────────────────────────────────────────────

function getPlainTagValue(key, siteName, currentYear) {
    var map = {
        first_name:    "John",
        last_name:     "Doe",
        email:         "john@example.com",
        phone:         "07700 900000",
        product_title: "Bailey Autograph 75-4i",
        product_url:   "#",
        product_price: "£29,995",
        product_type:  "caravan",
        site_name:     siteName,
        site_url:      window.location.origin,
        admin_email:   "admin@example.com",
        date:          new Date().toLocaleDateString("en-GB"),
        time:          new Date().toLocaleTimeString("en-GB", {hour:"2-digit",minute:"2-digit"}),
        year:          String(currentYear)
    };
    
    if (map.hasOwnProperty(key)) return map[key];
    
    if (typeof lglContactTagPlaceholders !== "undefined" &&
        lglContactTagPlaceholders.hasOwnProperty(key)) {
        return lglContactTagPlaceholders[key];
    }
    
    return null;
}

function resolveAllTags(html, siteName, currentYear) {
    if (!html) return html || "";
    html = html.replace(/="([^"]*)"/g, function(match, val) {
        if (val.indexOf("{{") === -1) return match;
        return "=\"" + val.replace(/\{\{(\w+)\}\}/g, function(m, key) {
            var v = getPlainTagValue(key, siteName, currentYear);
            return v !== null ? v : m;
        }) + "\"";
    });
    
    html = html.replace(/=[\x27]([^\x27]*)[\x27]/g, function(match, val) {
        if (val.indexOf("{{") === -1) return match;
        return "=\x27" + val.replace(/\{\{(\w+)\}\}/g, function(m, key) {
            var v = getPlainTagValue(key, siteName, currentYear);
            return v !== null ? v : m;
        }) + "\x27";
    });
    
    html = html.replace(/\{\{(\w+)\}\}/g, function(m, key) {
        var v = getPlainTagValue(key, siteName, currentYear);
        return v !== null
            ? v
            : "<em style=\"color:#c00\">[" + key + "]</em>";
    });
    
    return html;
}

        // ── Live preview ──────────────────────────────────────────────
        function renderPreview() {
            var $activeEditor = $(".lgl-eb-tab-content.active .lgl-eb-textarea");
            if (!$activeEditor.length) return;
            
   var html = $activeEditor.val() || "";

            var currentYear = new Date().getFullYear();
            
            var siteName = $("#lgl-site-name").val() || "Website";
            var colorBg = $("#lgl-color-bg").val() || "#f5f5f5";
            var colorBodyBg = $("#lgl-color-body-bg").val() || "#ffffff";
            var colorText = $("#lgl-color-text").val() || "#1d2327";
            var colorHeaderBg = $("#lgl-color-header-bg").val() || "#001537";
            var colorHeaderText = $("#lgl-color-header-text").val() || "#ffffff";
            var colorLink = $("#lgl-color-link").val() || "#003793";

                var rawHeader = $("#lgl-global-header-template").val() || \'<div class="eb-header"><h1>{{site_name}}</h1></div>\';
            var rawFooter = $("#lgl-global-footer-template").val() || \'<div class="eb-footer">&copy; {{year}} {{site_name}}. This is an automated notification.</div>\';

        html       = resolveAllTags(html,      siteName, currentYear);
var headerHtml = resolveAllTags(rawHeader, siteName, currentYear);
var footerHtml = resolveAllTags(rawFooter, siteName, currentYear);


            var $frame = $("#lgl-eb-preview-frame");
            if (!$frame.length) return;

            var fullHtml = `<!DOCTYPE html>
            <html lang="en">
            <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Preview</title>
            <style>
              body { margin:0; padding:0; background:${colorBg}; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; color:${colorText}; }
              .eb-wrapper { max-width:640px; margin:30px auto; background:${colorBodyBg}; border-radius:6px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,.08); }
              .eb-header { background:${colorHeaderBg}; padding:24px 32px; }
              .eb-header h1, .eb-header h2, .eb-header h3 { margin:0; font-size:20px; color:${colorHeaderText}; }
              .eb-body { padding:32px; line-height:1.65; }
              .eb-body h2 { font-size:18px; color:${colorHeaderBg}; margin:0 0 16px; }
              .eb-body p { margin:0 0 14px; font-size:14px; }
              .eb-body a { color:${colorLink}; }
              .eb-button { display:inline-block; padding:12px 24px; background:${colorLink}; color:#fff; border-radius:5px; text-decoration:none; font-weight:600; }
              .eb-body ul { padding-left:20px; margin:0 0 14px; }
              .eb-body li { font-size:14px; margin-bottom:6px; }
              .eb-body table { width:100%; border-collapse:collapse; margin-bottom:16px; }
              .eb-body table td, .eb-body table th { padding:10px 12px; border:1px solid #e0e0e0; font-size:13px; text-align:left; }
              .eb-body table th { background:${colorBg}; font-weight:600; }
              .eb-footer { background:${colorBg}; padding:16px 32px; font-size:11px; color:#8c8f94; text-align:center; }
            </style>
            </head>
            <body>
              <div class="eb-wrapper">
                ${headerHtml}
                <div class="eb-body">${html}</div>
                ${footerHtml}
              </div>
            </body>
            </html>`;

            var doc = $frame[0].contentDocument || $frame[0].contentWindow.document;
            doc.open(); doc.write(fullHtml); doc.close();
        }

        $(document).on("click", "#lgl-eb-preview-btn", function(e){
            if(e) e.preventDefault();
            renderPreview();
        });

        var previewTimeout;
        $(document).on("input", ".lgl-eb-textarea", function(){
            clearTimeout(previewTimeout);
            previewTimeout = setTimeout(renderPreview, 400);
        });

        // ── Send test email ───────────────────────────────────────────
        $(document).on("click", "#lgl-eb-send-test", function(e){
            e.preventDefault();
            var email = $("#lgl-eb-test-email").val().trim();
            if (!email) { alert("Please enter a test email address."); return; }
            var $msg = $("#lgl-eb-test-msg");
            $msg.text("Sending...").removeClass("success error");
            $.ajax({
                url: ajaxurl, type: "POST",
                data: {
                    action: "lgl_send_test_email",
                    nonce: $("#lgl-eb-nonce-value").val(),
                    email: email,
                    subject: $("#lgl-eb-subject").val(),
                    body: $("#lgl-eb-body").val()
                },
                success: function(r){
                    if (r.success) {
                        $msg.text("Test email sent successfully!").addClass("success");
                    } else {
                        $msg.text("Failed: " + (r.data || "Unknown error")).addClass("error");
                    }
                },
                error: function(xhr) {
                    $msg.text("Request failed (" + xhr.status + "). Check server logs.").addClass("error");
                }
            });
        });

$(document).on("click", ".lgl-global-tag", function(e) {
    e.preventDefault();
    var targetId = $(this).data("target");
    var tag      = $(this).data("tag");
    if (!targetId) return;
    var el = document.getElementById(targetId);
    if (!el) return;
    var start = el.selectionStart, end = el.selectionEnd;
    el.value = el.value.substring(0, start) + tag + el.value.substring(end);
    el.selectionStart = el.selectionEnd = start + tag.length;
    el.focus();
    renderGlobalPreview();
});

$(document).on("click", ".lgl-eb-tag-ref code", function(e) {
    e.preventDefault();
    var tag = $(this).data("tag") || $(this).text().trim();
    if ($lastFocus && $lastFocus.hasClass("lgl-global-textarea")) {
        var el = $lastFocus[0];
        var start = el.selectionStart, end = el.selectionEnd;
        el.value = el.value.substring(0, start) + tag + el.value.substring(end);
        el.selectionStart = el.selectionEnd = start + tag.length;
        el.focus();
        renderGlobalPreview();
    }
});

function renderGlobalPreview() {
    var $frame = $("#lgl-global-preview-frame");
    if (!$frame.length) return;

    var siteName   = $("#lgl-g-site-name").val()       || get_option_blogname || "Website";
    var colorBg    = $("#lgl-gc-bg").val()             || "#f5f5f5";
    var colorBodBg = $("#lgl-gc-body-bg").val()        || "#ffffff";
    var colorText  = $("#lgl-gc-text").val()           || "#1d2327";
    var colorHBg   = $("#lgl-gc-header-bg").val()      || "#001537";
    var colorHTxt  = $("#lgl-gc-header-txt").val()     || "#ffffff";
    var colorLink  = $("#lgl-gc-link").val()           || "#003793";
    var currentYear = new Date().getFullYear();

    function resolveTags(html) {
      return resolveAllTags(html, siteName, currentYear);
    }

    var headerHtml = resolveTags($("#lgl-global-header-ta").val() || "");
    var footerHtml = resolveTags($("#lgl-global-footer-ta").val() || "");

    var sampleBody = "<h2>Sample Email Content</h2>"
        + "<p>This is how your email body will appear inside the global template. "
        + "Headers and footers defined here wrap every transactional email sent by the plugin.</p>"
        + "<p><a class=\"eb-button\" style=\"color: #fff\" href=\"#\">Example Button</a></p>"
        + "<table><tr><th>Field</th><th>Value</th></tr>"
        + "<tr><td>Vehicle</td><td>Bailey Autograph 75-4i</td></tr>"
        + "<tr><td>Price</td><td>£29,995</td></tr></table>";

    var fullHtml = "<!DOCTYPE html><html lang=\"en\"><head>"
        + "<meta charset=\"UTF-8\"><meta name=\"viewport\" content=\"width=device-width,initial-scale=1\">"
        + "<style>"
        + "body{margin:0;padding:0;background:" + colorBg + ";font-family:-apple-system,BlinkMacSystemFont,\"Segoe UI\",Roboto,sans-serif;color:" + colorText + "}"
        + ".eb-wrapper{max-width:640px;margin:30px auto;background:" + colorBodBg + ";border-radius:6px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.08)}"
        + ".eb-header{background:" + colorHBg + ";padding:24px 32px}"
        + ".eb-header h1,.eb-header h2,.eb-header h3{margin:0;font-size:20px;color:" + colorHTxt + "}"
        + ".eb-body{padding:32px;line-height:1.65}"
        + ".eb-body h2{font-size:18px;color:" + colorHBg + ";margin:0 0 16px}"
        + ".eb-body p{margin:0 0 14px;font-size:14px}"
        + ".eb-body a{color:" + colorLink + "}"
        + ".eb-button{display:inline-block;padding:12px 24px;background:" + colorLink + ";color:#fff;border-radius:5px;text-decoration:none;font-weight:600}"
        + ".eb-body table{width:100%;border-collapse:collapse;margin-bottom:16px}"
        + ".eb-body table td,.eb-body table th{padding:10px 12px;border:1px solid #e0e0e0;font-size:13px;text-align:left}"
        + ".eb-body table th{background:" + colorBg + ";font-weight:600}"
        + ".eb-footer{background:" + colorBg + ";padding:16px 32px;font-size:11px;color:#8c8f94;text-align:center}"
        + "</style></head><body>"
        + "<div class=\"eb-wrapper\">"
        + headerHtml
        + "<div class=\"eb-body\">" + sampleBody + "</div>"
        + footerHtml
        + "</div></body></html>";

    var doc = $frame[0].contentDocument || $frame[0].contentWindow.document;
    doc.open(); doc.write(fullHtml); doc.close();
}

var globalPreviewTimeout;
$(document).on("input", ".lgl-global-textarea", function() {
    clearTimeout(globalPreviewTimeout);
    globalPreviewTimeout = setTimeout(renderGlobalPreview, 300);
});

$(document).on("input change", ".lgl-global-color", function() {
    var idMap = {
        "lgl-gc-bg":         "lgl-g-color-bg",
        "lgl-gc-body-bg":    "lgl-g-color-body-bg",
        "lgl-gc-text":       "lgl-g-color-text",
        "lgl-gc-header-bg":  "lgl-g-color-header-bg",
        "lgl-gc-header-txt": "lgl-g-color-header-txt",
        "lgl-gc-link":       "lgl-g-color-link"
    };
    var mirrorId = idMap[$(this).attr("id")];
    if (mirrorId) $("#" + mirrorId).val($(this).val());
    clearTimeout(globalPreviewTimeout);
    globalPreviewTimeout = setTimeout(renderGlobalPreview, 100);
});

$(document).on("click", "#lgl-global-preview-btn", function(e) {
    e.preventDefault();
    renderGlobalPreview();
});

$(document).ready(function() {
    $("[name=\'recipient_type\']:checked").trigger("change");
    if ($("#lgl-eb-auto-reply-toggle").is(":checked")) {
        $("#lgl-eb-autoreply-section").addClass("is-open");
    }
    
    if (typeof renderPreview === "function") {
        renderPreview();
    }
    
    if ($("#lgl-global-preview-frame").length) {
        renderGlobalPreview();
    }
});

    })(jQuery);
    ';
    }

    /* ═══════════════════════════════════════════════════════════════
       PAGE RENDERERS & ROUTING
    ═══════════════════════════════════════════════════════════════ */

    /**
     * Master render controller for the Email Builder Tabs.
     *
     * @return void
     */
    public function render_master_email_builder_page()
    {
        if (! current_user_can('manage_options')) return;

        $active_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'global';

        echo '<div class="wrap">';
        echo '<h1>' . __('Email Builder', 'lgl-shortcodes') . '</h1>';

        if (isset($_GET['saved'])) {
            echo '<div class="notice notice-success is-dismissible"><p>' . __('Settings saved.', 'lgl-shortcodes') . '</p></div>';
        }

        echo '<h2 class="nav-tab-wrapper">';
        $tabs = [
            'global'  => __('Global Template', 'lgl-shortcodes'),
            'enquiry' => __('Enquiry Emails', 'lgl-shortcodes'),
            'reserve' => __('Reserve Emails', 'lgl-shortcodes'),
        ];

        foreach ($tabs as $key => $name) {
            $active = ($active_tab === $key) ? 'nav-tab-active' : '';
            $url = admin_url('admin.php?page=lgl-email-builder&tab=' . $key);
            echo '<a href="' . esc_url($url) . '" class="nav-tab ' . esc_attr($active) . '">' . esc_html($name) . '</a>';
        }
        echo '</h2>';

        if ($active_tab === 'global') {
            $this->render_global_email_page();
        } elseif ($active_tab === 'enquiry') {
            $this->render_enquiry_email_page();
        } elseif ($active_tab === 'reserve') {
            $this->render_reserve_email_page();
        }

        echo '</div>';
    }

    private function render_global_email_page()
    {
        $global_settings  = self::get_global_email_settings();
        $contact_defs     = self::get_contact_tag_definitions();

        $global_tags = ['{{site_name}}' => 'Website name', '{{year}}' => 'Current year'];
        foreach ($contact_defs as $key => $label) {
            $global_tags['{{' . $key . '}}'] = $label;
        }
?>
        <div class="lgl-eb-wrap">
            <div class="lgl-eb-master-layout">

                <div class="lgl-eb-builder-column">
                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" id="lgl-global-form">
                        <?php wp_nonce_field('lgl_save_global_email', 'lgl_eb_form_nonce'); ?>
                        <input type="hidden" name="action" value="lgl_save_global_email">

                        <!-- Hidden mirror inputs for live JS preview -->
                        <input type="hidden" id="lgl-g-site-name" value="<?php echo esc_attr(get_option('blogname')); ?>">
                        <input type="hidden" id="lgl-g-color-bg" value="<?php echo esc_attr($global_settings['color_bg']); ?>">
                        <input type="hidden" id="lgl-g-color-body-bg" value="<?php echo esc_attr($global_settings['color_body_bg']); ?>">
                        <input type="hidden" id="lgl-g-color-text" value="<?php echo esc_attr($global_settings['color_text']); ?>">
                        <input type="hidden" id="lgl-g-color-header-bg" value="<?php echo esc_attr($global_settings['color_header_bg']); ?>">
                        <input type="hidden" id="lgl-g-color-header-txt" value="<?php echo esc_attr($global_settings['color_header_text']); ?>">
                        <input type="hidden" id="lgl-g-color-link" value="<?php echo esc_attr($global_settings['color_link']); ?>">

                        <!-- ══════════════════════════════════════════════════════
                             NEW: Apply global template to ALL site emails
                        ══════════════════════════════════════════════════════ -->
                        <div class="lgl-eb-section lgl-eb-apply-all-section">
                            <h3>🌐 <?php _e('Apply to All Site Emails', 'lgl-shortcodes'); ?></h3>
                            <div class="lgl-eb-apply-all-row">
                                <input
                                    type="checkbox"
                                    id="lgl_apply_to_all_emails"
                                    name="apply_to_all_emails"
                                    value="1"
                                    <?php checked(! empty($global_settings['apply_to_all_emails'])); ?>>
                                <div>
                                    <label for="lgl_apply_to_all_emails">
                                        <?php _e('Wrap all outgoing WordPress emails in this global template', 'lgl-shortcodes'); ?>
                                    </label>
                                    <p>
                                        <?php _e(
                                            'When enabled, every email sent via <code>wp_mail()</code> — including WordPress core, WooCommerce, contact forms, and any other plugin — will be wrapped in the header, footer, and colour settings defined below. Emails that are already full HTML documents are skipped automatically.',
                                            'lgl-shortcodes'
                                        ); ?>
                                    </p>
                                    <p>
                                        <?php _e(
                                            'Developers can also call <code>LGL_Email_Builder::wrap_html( $subject, $body )</code> directly, or use the global helper <code>lgl_wrap_email_html( $subject, $body )</code>.',
                                            'lgl-shortcodes'
                                        ); ?>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Colors -->
                        <div class="lgl-eb-section">
                            <h3><?php _e('Theme Colors', 'lgl-shortcodes'); ?></h3>
                            <div class="lgl-eb-color-grid">
                                <div class="lgl-eb-color-row">
                                    <label><?php _e('Outer Background', 'lgl-shortcodes'); ?></label>
                                    <input type="color" name="color_bg" id="lgl-gc-bg" value="<?php echo esc_attr($global_settings['color_bg']); ?>" class="lgl-global-color">
                                </div>
                                <div class="lgl-eb-color-row">
                                    <label><?php _e('Inner Background', 'lgl-shortcodes'); ?></label>
                                    <input type="color" name="color_body_bg" id="lgl-gc-body-bg" value="<?php echo esc_attr($global_settings['color_body_bg']); ?>" class="lgl-global-color">
                                </div>
                                <div class="lgl-eb-color-row">
                                    <label><?php _e('Text Color', 'lgl-shortcodes'); ?></label>
                                    <input type="color" name="color_text" id="lgl-gc-text" value="<?php echo esc_attr($global_settings['color_text']); ?>" class="lgl-global-color">
                                </div>
                                <div class="lgl-eb-color-row">
                                    <label><?php _e('Header Background', 'lgl-shortcodes'); ?></label>
                                    <input type="color" name="color_header_bg" id="lgl-gc-header-bg" value="<?php echo esc_attr($global_settings['color_header_bg']); ?>" class="lgl-global-color">
                                </div>
                                <div class="lgl-eb-color-row">
                                    <label><?php _e('Header Text', 'lgl-shortcodes'); ?></label>
                                    <input type="color" name="color_header_text" id="lgl-gc-header-txt" value="<?php echo esc_attr($global_settings['color_header_text']); ?>" class="lgl-global-color">
                                </div>
                                <div class="lgl-eb-color-row">
                                    <label><?php _e('Link & Accent', 'lgl-shortcodes'); ?></label>
                                    <input type="color" name="color_link" id="lgl-gc-link" value="<?php echo esc_attr($global_settings['color_link']); ?>" class="lgl-global-color">
                                </div>
                            </div>
                        </div>

                        <!-- Global Header -->
                        <div class="lgl-eb-section">
                            <h3><?php _e('Global Header (HTML)', 'lgl-shortcodes'); ?></h3>
                            <div class="lgl-eb-tag-toolbar" data-for="lgl-global-header-ta">
                                <span class="lgl-tag-group-label"><?php _e('Available Tags', 'lgl-shortcodes'); ?></span>
                                <?php foreach ($global_tags as $tag => $desc) : ?>
                                    <button type="button" class="lgl-eb-insert-tag lgl-global-tag" data-tag="<?php echo esc_attr($tag); ?>" data-target="lgl-global-header-ta" title="<?php echo esc_attr($desc); ?>"><?php echo esc_html($tag); ?></button>
                                <?php endforeach; ?>
                            </div>
                            <textarea name="header" id="lgl-global-header-ta" class="lgl-eb-textarea lgl-eb-textarea-small lgl-global-textarea"><?php echo esc_textarea($global_settings['header']); ?></textarea>
                        </div>

                        <!-- Global Footer -->
                        <div class="lgl-eb-section">
                            <h3><?php _e('Global Footer (HTML)', 'lgl-shortcodes'); ?></h3>
                            <div class="lgl-eb-tag-toolbar" data-for="lgl-global-footer-ta">
                                <span class="lgl-tag-group-label"><?php _e('Available Tags', 'lgl-shortcodes'); ?></span>
                                <?php foreach ($global_tags as $tag => $desc) : ?>
                                    <button type="button" class="lgl-eb-insert-tag lgl-global-tag" data-tag="<?php echo esc_attr($tag); ?>" data-target="lgl-global-footer-ta" title="<?php echo esc_attr($desc); ?>"><?php echo esc_html($tag); ?></button>
                                <?php endforeach; ?>
                            </div>
                            <textarea name="footer" id="lgl-global-footer-ta" class="lgl-eb-textarea lgl-eb-textarea-small lgl-global-textarea"><?php echo esc_textarea($global_settings['footer']); ?></textarea>
                        </div>

                        <!-- Tag Reference -->
                        <div class="lgl-eb-section">
                            <h3><?php _e('Available Merge Tags', 'lgl-shortcodes'); ?></h3>
                            <ul class="lgl-eb-tag-ref">
                                <?php foreach ($global_tags as $tag => $desc) : ?>
                                    <li>
                                        <code data-tag="<?php echo esc_attr($tag); ?>"><?php echo esc_html($tag); ?></code>
                                        <span class="lgl-tag-desc"><?php echo esc_html($desc); ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>

                        <?php submit_button(__('Save Global Template', 'lgl-shortcodes')); ?>
                    </form>
                </div>

                <div class="lgl-eb-preview-column">
                    <div class="lgl-eb-section lgl-eb-preview-sticky">
                        <div class="lgl-eb-preview-header">
                            <h3><?php _e('Live Preview', 'lgl-shortcodes'); ?></h3>
                            <button type="button" id="lgl-global-preview-btn" class="button button-secondary button-small"><?php _e('⟳ Refresh', 'lgl-shortcodes'); ?></button>
                        </div>
                        <iframe id="lgl-global-preview-frame" title="Global Template Preview"></iframe>
                    </div>
                </div>

            </div>
        </div>
    <?php
    }

    private function render_enquiry_email_page()
    {
        $form_settings  = get_option('lgl_enquiry_form', []);
        $email_settings = get_option('lgl_enquiry_email', $this->default_email('enquiry'));
        $this->render_page('enquiry', $form_settings, $email_settings);
    }

    private function render_reserve_email_page()
    {
        $form_settings  = get_option('lgl_reserve_form', []);
        $email_settings = get_option('lgl_reserve_email', $this->default_email('reserve'));
        $this->render_page('reserve', $form_settings, $email_settings);
    }

    private function render_page(string $type, array $form_settings, array $email_settings)
    {
        $action          = "lgl_save_{$type}_email";
        $all_tags        = $this->get_merge_tags($form_settings['fields'] ?? []);
        $subject         = $email_settings['subject']          ?? '';
        $body            = $email_settings['body']             ?? '';
        $rec_type        = $email_settings['recipient_type']   ?? 'admin';
        $custom_email    = $email_settings['custom_email']     ?? '';
        $auto_reply      = ! empty($email_settings['auto_reply_enabled']);
        $ar_subject      = $email_settings['auto_reply_subject'] ?? '';
        $ar_body         = $email_settings['auto_reply_body']    ?? '';
        $global_settings = self::get_global_email_settings();
        $test_nonce      = wp_create_nonce('lgl_email_builder');
    ?>
        <div class="lgl-eb-wrap">
            <div class="lgl-eb-master-layout">

                <div class="lgl-eb-builder-column">
                    <div class="lgl-eb-tabs">
                        <div class="lgl-eb-tab active"><?php _e('📬 Admin Notification', 'lgl-shortcodes'); ?></div>
                        <div class="lgl-eb-tab"><?php _e('↩️ Auto-Reply to Submitter', 'lgl-shortcodes'); ?></div>
                    </div>

                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                        <?php wp_nonce_field("lgl_save_{$type}_email", 'lgl_eb_form_nonce'); ?>
                        <input type="hidden" name="action" value="<?php echo esc_attr($action); ?>">
                        <input type="hidden" name="form_type" value="<?php echo esc_attr($type); ?>">
                        <input type="hidden" id="lgl-eb-nonce-value" value="<?php echo esc_attr($test_nonce); ?>">

                        <input type="hidden" id="lgl-global-header-template" value="<?php echo esc_attr($global_settings['header']); ?>">
                        <input type="hidden" id="lgl-global-footer-template" value="<?php echo esc_attr($global_settings['footer']); ?>">
                        <input type="hidden" id="lgl-site-name" value="<?php echo esc_attr(get_option('blogname')); ?>">

                        <input type="hidden" id="lgl-color-bg" value="<?php echo esc_attr($global_settings['color_bg']); ?>">
                        <input type="hidden" id="lgl-color-body-bg" value="<?php echo esc_attr($global_settings['color_body_bg']); ?>">
                        <input type="hidden" id="lgl-color-text" value="<?php echo esc_attr($global_settings['color_text']); ?>">
                        <input type="hidden" id="lgl-color-header-bg" value="<?php echo esc_attr($global_settings['color_header_bg']); ?>">
                        <input type="hidden" id="lgl-color-header-text" value="<?php echo esc_attr($global_settings['color_header_text']); ?>">
                        <input type="hidden" id="lgl-color-link" value="<?php echo esc_attr($global_settings['color_link']); ?>">

                        <div class="lgl-eb-tab-panels">

                            <div class="lgl-eb-tab-content active">
                                <div class="lgl-eb-layout">
                                    <div class="lgl-eb-main">

                                        <div class="lgl-eb-section">
                                            <h3><?php _e('Recipients', 'lgl-shortcodes'); ?></h3>
                                            <div class="lgl-eb-recipient-opts">
                                                <label>
                                                    <input type="radio" name="recipient_type" value="admin" <?php checked($rec_type, 'admin'); ?>>
                                                    <?php _e('Site admin email', 'lgl-shortcodes'); ?>
                                                    <code style="font-size:11px;background:#f6f7f7;padding:1px 6px;border-radius:2px;border:1px solid #e0e0e0;"><?php echo esc_html(get_option('admin_email')); ?></code>
                                                </label>
                                                <label>
                                                    <input type="radio" name="recipient_type" value="custom" <?php checked($rec_type, 'custom'); ?>>
                                                    <?php _e('Custom email address', 'lgl-shortcodes'); ?>
                                                </label>
                                                <label>
                                                    <input type="radio" name="recipient_type" value="both" <?php checked($rec_type, 'both'); ?>>
                                                    <?php _e('Both (admin + custom)', 'lgl-shortcodes'); ?>
                                                </label>
                                            </div>
                                            <div id="lgl-custom-email-row" <?php echo in_array($rec_type, ['custom', 'both'], true) ? 'class="is-visible"' : ''; ?>>
                                                <div class="lgl-eb-row">
                                                    <label><?php _e('Custom email address', 'lgl-shortcodes'); ?></label>
                                                    <input type="email" name="custom_email" value="<?php echo esc_attr($custom_email); ?>" placeholder="sales@example.com">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="lgl-eb-section">
                                            <h3><?php _e('Subject Line', 'lgl-shortcodes'); ?></h3>
                                            <div class="lgl-eb-subject-tags">
                                                <?php foreach ($this->subject_tags($all_tags) as $tag => $label) : ?>
                                                    <button type="button" class="lgl-eb-insert-tag" data-tag="<?php echo esc_attr($tag); ?>" title="<?php echo esc_attr($label); ?>"><?php echo esc_html($tag); ?></button>
                                                <?php endforeach; ?>
                                            </div>
                                            <div class="lgl-eb-row">
                                                <input type="text" name="subject" id="lgl-eb-subject" class="lgl-eb-subject-input" value="<?php echo esc_attr($subject); ?>" placeholder="<?php echo esc_attr($type === 'enquiry' ? 'New Enquiry: {{first_name}} {{last_name}} — {{product_title}}' : 'New Reservation: {{first_name}} {{last_name}} — {{product_title}}'); ?>">
                                            </div>
                                        </div>

                                        <div class="lgl-eb-section">
                                            <h3><?php _e('Email Body', 'lgl-shortcodes'); ?> <span style="font-size:11px;font-weight:400;color:#8c8f94;">(HTML supported)</span></h3>
                                            <?php $this->render_tag_toolbar($all_tags, 'lgl-eb-body'); ?>
                                            <textarea name="body" id="lgl-eb-body" class="lgl-eb-textarea"><?php echo esc_textarea($body ?: $this->default_admin_body($type)); ?></textarea>
                                        </div>

                                        <div class="lgl-eb-section">
                                            <h3><?php _e('Send Test Email', 'lgl-shortcodes'); ?></h3>
                                            <p class="description"><?php _e('Sends a preview with placeholder values substituted for merge tags.', 'lgl-shortcodes'); ?></p>
                                            <div class="lgl-eb-test-row">
                                                <input type="email" id="lgl-eb-test-email" placeholder="your@email.com" value="<?php echo esc_attr(get_option('admin_email')); ?>">
                                                <button type="button" id="lgl-eb-send-test" class="button"><?php _e('Send Test', 'lgl-shortcodes'); ?></button>
                                                <span class="lgl-eb-test-msg" id="lgl-eb-test-msg"></span>
                                            </div>
                                        </div>

                                    </div>

                                    <div class="lgl-eb-sidebar">
                                        <?php $this->render_tag_reference($all_tags); ?>
                                    </div>
                                </div>
                            </div>

                            <div class="lgl-eb-tab-content">
                                <div class="lgl-eb-layout">
                                    <div class="lgl-eb-main">
                                        <div class="lgl-eb-section">
                                            <h3><?php _e('Auto-Reply Settings', 'lgl-shortcodes'); ?></h3>
                                            <div class="lgl-eb-toggle-row">
                                                <input type="checkbox" id="lgl-eb-auto-reply-toggle" name="auto_reply_enabled" value="1" <?php checked($auto_reply); ?>>
                                                <label for="lgl-eb-auto-reply-toggle" style="font-weight:600;font-size:13px;cursor:pointer;"><?php _e('Send an automatic reply to the person who submitted this form', 'lgl-shortcodes'); ?></label>
                                            </div>
                                            <p class="description"><?php _e('Requires the form to have an <code>email</code> field.', 'lgl-shortcodes'); ?></p>
                                        </div>

                                        <div id="lgl-eb-autoreply-section" class="lgl-eb-collapsible <?php echo $auto_reply ? 'is-open' : ''; ?>">
                                            <div class="lgl-eb-section">
                                                <h3><?php _e('Auto-Reply Subject', 'lgl-shortcodes'); ?></h3>
                                                <div class="lgl-eb-subject-tags">
                                                    <?php foreach ($this->subject_tags($all_tags) as $tag => $label) : ?>
                                                        <button type="button" class="lgl-eb-insert-tag" data-tag="<?php echo esc_attr($tag); ?>"><?php echo esc_html($tag); ?></button>
                                                    <?php endforeach; ?>
                                                </div>
                                                <div class="lgl-eb-row">
                                                    <input type="text" name="auto_reply_subject" class="lgl-eb-subject-input" value="<?php echo esc_attr($ar_subject); ?>" placeholder="<?php esc_attr_e('Thank you for your enquiry, {{first_name}}', 'lgl-shortcodes'); ?>">
                                                </div>
                                            </div>

                                            <div class="lgl-eb-section">
                                                <h3><?php _e('Auto-Reply Body', 'lgl-shortcodes'); ?></h3>
                                                <?php $this->render_tag_toolbar($all_tags, 'lgl-eb-ar-body'); ?>
                                                <textarea name="auto_reply_body" id="lgl-eb-ar-body" class="lgl-eb-textarea"><?php echo esc_textarea($ar_body ?: $this->default_autoreply_body($type)); ?></textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="lgl-eb-sidebar">
                                        <?php $this->render_tag_reference($all_tags); ?>
                                    </div>
                                </div>
                            </div>

                        </div><?php submit_button(__('Save Email Settings', 'lgl-shortcodes')); ?>
                    </form>
                </div>

                <div class="lgl-eb-preview-column">
                    <div class="lgl-eb-section lgl-eb-preview-sticky">
                        <div class="lgl-eb-preview-header">
                            <h3><?php _e('Live HTML Preview', 'lgl-shortcodes'); ?></h3>
                            <button type="button" id="lgl-eb-preview-btn" class="button button-secondary button-small"><?php _e('⟳ Refresh', 'lgl-shortcodes'); ?></button>
                        </div>
                        <iframe id="lgl-eb-preview-frame" title="Email Preview"></iframe>
                    </div>
                </div>

            </div>
        </div>
<?php
    }

    /* ═══════════════════════════════════════════════════════════════
       TAG TOOLBAR
    ═══════════════════════════════════════════════════════════════ */

    private function render_tag_toolbar(array $all_tags, string $textarea_id)
    {
        $groups = $this->grouped_tags($all_tags);
        echo '<div class="lgl-eb-tag-toolbar" data-for="' . esc_attr($textarea_id) . '">';
        foreach ($groups as $group_label => $tags) {
            echo '<span class="lgl-tag-group-label">' . esc_html($group_label) . '</span>';
            foreach ($tags as $tag => $label) {
                printf(
                    '<button type="button" class="lgl-eb-insert-tag" data-tag="%s" title="%s">%s</button>',
                    esc_attr($tag),
                    esc_attr($label),
                    esc_html($tag)
                );
            }
        }
        echo '</div>';
    }

    /* ═══════════════════════════════════════════════════════════════
       TAG REFERENCE SIDEBAR
    ═══════════════════════════════════════════════════════════════ */

    private function render_tag_reference(array $all_tags)
    {
        $groups = $this->grouped_tags($all_tags);
        echo '<div class="lgl-eb-section">';
        echo '<h3>' . __('Merge Tag Reference', 'lgl-shortcodes') . '</h3>';
        echo '<p class="description" style="margin-bottom:12px;font-size:11px;">' . __('Click a tag to insert it into the active editor.', 'lgl-shortcodes') . '</p>';

        foreach ($groups as $group_label => $tags) {
            echo '<h4 style="margin:14px 0 6px;font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:#8c8f94;">' . esc_html($group_label) . '</h4>';
            echo '<ul class="lgl-eb-tag-ref">';
            foreach ($tags as $tag => $label) {
                printf(
                    '<li><code data-tag="%s">%s</code><span class="lgl-tag-desc">%s</span></li>',
                    esc_attr($tag),
                    esc_html($tag),
                    esc_html($label)
                );
            }
            echo '</ul>';
        }
        echo '</div>';
    }

    /* ═══════════════════════════════════════════════════════════════
       SAVE
    ═══════════════════════════════════════════════════════════════ */

    public function save_email_settings()
    {
        $form_type = sanitize_key($_POST['form_type'] ?? '');
        check_admin_referer("lgl_save_{$form_type}_email", 'lgl_eb_form_nonce');
        if (! current_user_can('manage_options')) wp_die('Unauthorized');

        $allowed_recipients = ['admin', 'custom', 'both'];
        $rec_type = sanitize_text_field($_POST['recipient_type'] ?? 'admin');

        update_option("lgl_{$form_type}_email", [
            'subject'            => sanitize_text_field($_POST['subject']            ?? ''),
            'body'               => wp_kses_post($_POST['body']                      ?? ''),
            'recipient_type'     => in_array($rec_type, $allowed_recipients, true) ? $rec_type : 'admin',
            'custom_email'       => sanitize_email($_POST['custom_email']            ?? ''),
            'auto_reply_enabled' => ! empty($_POST['auto_reply_enabled']),
            'auto_reply_subject' => sanitize_text_field($_POST['auto_reply_subject'] ?? ''),
            'auto_reply_body'    => wp_kses_post($_POST['auto_reply_body']           ?? ''),
        ]);

        wp_redirect(admin_url("admin.php?page=lgl-email-builder&tab={$form_type}&saved=1"));
        exit;
    }

    /**
     * Saves global template settings including the new "apply to all emails" toggle.
     *
     * @return void
     */
    public function save_global_email_settings()
    {
        check_admin_referer("lgl_save_global_email", 'lgl_eb_form_nonce');
        if (! current_user_can('manage_options')) wp_die('Unauthorized');

        update_option('lgl_global_email_settings', [
            'header'              => wp_kses_post($_POST['header']       ?? ''),
            'footer'              => wp_kses_post($_POST['footer']       ?? ''),
            'color_bg'            => sanitize_hex_color($_POST['color_bg']          ?? '#f5f5f5'),
            'color_body_bg'       => sanitize_hex_color($_POST['color_body_bg']     ?? '#ffffff'),
            'color_text'          => sanitize_hex_color($_POST['color_text']        ?? '#1d2327'),
            'color_header_bg'     => sanitize_hex_color($_POST['color_header_bg']   ?? '#001537'),
            'color_header_text'   => sanitize_hex_color($_POST['color_header_text'] ?? '#ffffff'),
            'color_link'          => sanitize_hex_color($_POST['color_link']        ?? '#003793'),
            // NEW: persist the "apply to all" checkbox
            'apply_to_all_emails' => ! empty($_POST['apply_to_all_emails']),
        ]);

        wp_redirect(admin_url('admin.php?page=lgl-email-builder&tab=global&saved=1'));
        exit;
    }

    /* ═══════════════════════════════════════════════════════════════
       STATIC: PROCESS & SEND EMAILS
    ═══════════════════════════════════════════════════════════════ */

    public static function build_tag_values(array $form_data, int $product_id): array
    {
        $price = $product_id ? get_post_meta($product_id, 'price', true) : '';

        $lgl_options    = get_option('lgl_settings', []);
        $contact_values = [];
        foreach (self::get_contact_tag_definitions() as $key => $label) {
            $contact_values[$key] = $lgl_options[$key] ?? '';
        }

        return array_merge($form_data, $contact_values, [
            'product_title'   => $product_id ? html_entity_decode(get_the_title($product_id), ENT_QUOTES) : '',
            'product_url'     => $product_id ? get_permalink($product_id) : '',
            'product_price'   => $price ? LGL_Shortcodes::format_price($price) : '',
            'product_type'    => $product_id ? get_post_type($product_id) : '',
            'site_name'       => get_option('blogname'),
            'site_url'        => home_url(),
            'admin_email'     => get_option('admin_email'),
            'date'            => wp_date(get_option('date_format')),
            'time'            => wp_date(get_option('time_format')),
        ]);
    }

    public static function process_tags(string $template, array $values): string
    {
        foreach ($values as $key => $value) {
            if (is_array($value)) continue;
            $template = str_replace('{{' . $key . '}}', esc_html((string) $value), $template);
        }
        $template = preg_replace('/\{\{[^}]+\}\}/', '', $template);
        return $template;
    }

    public static function send(string $form_type, array $form_data, int $product_id): void
    {
        $email_cfg  = get_option("lgl_{$form_type}_email", []);
        $tag_values = self::build_tag_values($form_data, $product_id);

        $subject = self::process_tags($email_cfg['subject'] ?? '', $tag_values);
        $body    = self::process_tags($email_cfg['body']    ?? '', $tag_values);

        if (empty($subject)) {
            $name    = trim(($form_data['first_name'] ?? '') . ' ' . ($form_data['last_name'] ?? ''));
            $product = $tag_values['product_title'] ?: 'Unknown Product';
            $subject = $form_type === 'enquiry'
                ? "New Enquiry: {$name} — {$product}"
                : "New Reservation: {$name} — {$product}";
        }

        if (empty($body)) {
            $body = '<p><strong>Product:</strong> ' . esc_html($tag_values['product_title']) . '</p><ul>';
            foreach ($form_data as $k => $v) {
                $body .= '<li><strong>' . esc_html(ucwords(str_replace('_', ' ', $k))) . ':</strong> ' . esc_html($v) . '</li>';
            }
            $body .= '</ul>';
        }

        $recipients = self::resolve_recipients($email_cfg);
        $headers    = ['Content-Type: text/html; charset=UTF-8'];

        if (! empty($recipients)) {
            wp_mail($recipients, $subject, self::wrap_html($subject, $body), $headers);
        }

        if (! empty($email_cfg['auto_reply_enabled'])) {
            $submitter_email = $form_data['email'] ?? '';
            if (is_email($submitter_email)) {
                $ar_subject = self::process_tags($email_cfg['auto_reply_subject'] ?? '', $tag_values);
                $ar_body    = self::process_tags($email_cfg['auto_reply_body']    ?? '', $tag_values);

                if (empty($ar_subject)) {
                    $ar_subject = 'Thank you for your ' . $form_type . ', ' . ($form_data['first_name'] ?? '');
                }
                if (empty($ar_body)) {
                    $ar_body = '<p>Hi ' . esc_html($form_data['first_name'] ?? 'there') . ',</p><p>Thank you for getting in touch. We will be in contact with you shortly.</p>';
                }

                wp_mail($submitter_email, $ar_subject, self::wrap_html($ar_subject, $ar_body), $headers);
            }
        }
    }

    /* ═══════════════════════════════════════════════════════════════
       HELPERS
    ═══════════════════════════════════════════════════════════════ */

    private static function resolve_recipients(array $cfg): array
    {
        $admin  = get_option('admin_email');
        $custom = sanitize_email($cfg['custom_email'] ?? '');
        switch ($cfg['recipient_type'] ?? 'admin') {
            case 'custom':
                return $custom ? [$custom] : [$admin];
            case 'both':
                return array_filter([$admin, $custom]);
            default:
                return [$admin];
        }
    }

    /**
     * Retrieves the Global Template settings with required fallback defaults.
     * Public so theme/plugin developers can access the settings directly.
     *
     * @return array Keys: header, footer, color_*, apply_to_all_emails.
     */
    public static function get_global_email_settings(): array
    {
        $defaults = [
            'header'              => '<div class="eb-header"><h1>{{site_name}}</h1></div>',
            'footer'              => '<div class="eb-footer">&copy; {{year}} {{site_name}}. This is an automated notification.</div>',
            'color_bg'            => '#f5f5f5',
            'color_body_bg'       => '#ffffff',
            'color_text'          => '#1d2327',
            'color_header_bg'     => '#001537',
            'color_header_text'   => '#ffffff',
            'color_link'          => '#003793',
            'apply_to_all_emails' => false,
        ];
        return wp_parse_args(get_option('lgl_global_email_settings', []), $defaults);
    }

    /**
     * Wraps an email body inside the global template HTML shell.
     * Public static — callable from anywhere:
     *   LGL_Email_Builder::wrap_html( $subject, $body )
     *
     * @param string $subject Used as the <title> inside the DOCTYPE.
     * @param string $body    The inner HTML content (does NOT need to be a full document).
     * @return string         Complete HTML email document.
     */
    public static function wrap_html(string $subject, string $body): string
    {
        $site = esc_html(get_option('blogname'));
        $year = date('Y');

        if (stripos($body, '<html') !== false || stripos($body, '<!DOCTYPE') !== false) {
            return $body;
        }

        $global_settings = self::get_global_email_settings();

        $lgl_options = get_option('lgl_settings', []);

        $tag_search  = ['{{site_name}}', '{{year}}'];
        $tag_replace = [$site, $year];

        foreach (self::get_contact_tag_definitions() as $key => $label) {
            $tag_search[]  = '{{' . $key . '}}';
            $tag_replace[] = $lgl_options[$key] ?? '';
        }

        $header = str_replace($tag_search, $tag_replace, $global_settings['header']);
        $footer = str_replace($tag_search, $tag_replace, $global_settings['footer']);

        $bg         = esc_attr($global_settings['color_bg']);
        $body_bg    = esc_attr($global_settings['color_body_bg']);
        $text       = esc_attr($global_settings['color_text']);
        $header_bg  = esc_attr($global_settings['color_header_bg']);
        $header_txt = esc_attr($global_settings['color_header_text']);
        $link       = esc_attr($global_settings['color_link']);

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{$subject}</title>
<style>
  body { margin:0; padding:0; background:{$bg}; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; color:{$text}; }
  .eb-wrapper { max-width:640px; margin:30px auto; background:{$body_bg}; border-radius:6px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,.08); }
  .eb-header { background:{$header_bg}; padding:24px 32px; }
  .eb-header h1, .eb-header h2, .eb-header h3 { margin:0; font-size:20px; color:{$header_txt}; }
  .eb-body { padding:32px; line-height:1.65; }
  .eb-body h2 { font-size:18px; color:{$header_bg}; margin:0 0 16px; }
  .eb-body p { margin:0 0 14px; font-size:14px; }
  .eb-body a { color:{$link}; }
  .eb-button { display:inline-block; padding:12px 24px; background:{$link}; color:#fff; border-radius:5px; text-decoration:none; font-weight:600; }
  .eb-body ul { padding-left:20px; margin:0 0 14px; }
  .eb-body li { font-size:14px; margin-bottom:6px; }
  .eb-body table { width:100%; border-collapse:collapse; margin-bottom:16px; }
  .eb-body table td, .eb-body table th { padding:10px 12px; border:1px solid #e0e0e0; font-size:13px; text-align:left; }
  .eb-body table th { background:{$bg}; font-weight:600; }
  .eb-footer { background:{$bg}; padding:16px 32px; font-size:11px; color:#8c8f94; text-align:center; }
</style>
</head>
<body>
  <div class="eb-wrapper">
    {$header}
    <div class="eb-body">{$body}</div>
    {$footer}
  </div>
</body>
</html>
HTML;
    }

    private function get_merge_tags(array $form_fields): array
    {
        $tags = $this->system_tags();
        foreach ($form_fields as $field) {
            $id = $field['id'] ?? '';
            if (! $id || isset($tags['{{' . $id . '}}'])) continue;
            $tags['{{' . $id . '}}'] = $field['label'] ?? ucwords(str_replace('_', ' ', $id));
        }
        return $tags;
    }

    private function system_tags(): array
    {
        $tags = [
            '{{first_name}}'    => 'First name',
            '{{last_name}}'     => 'Last name',
            '{{email}}'         => 'Email address',
            '{{phone}}'         => 'Phone number',
            '{{product_title}}' => 'Vehicle name',
            '{{product_url}}'   => 'Vehicle page URL',
            '{{product_price}}' => 'Vehicle price',
            '{{product_type}}'  => 'Vehicle type',
            '{{site_name}}'     => 'Website name',
            '{{site_url}}'      => 'Website URL',
            '{{admin_email}}'   => 'Admin email address',
            '{{date}}'          => 'Submission date',
            '{{time}}'          => 'Submission time',
        ];

        foreach (self::get_contact_tag_definitions() as $key => $label) {
            $tags['{{' . $key . '}}'] = $label;
        }

        return $tags;
    }

    public static function get_contact_tag_definitions(): array
    {
        if (! class_exists('LGL_Shortcodes')) {
            return [];
        }

        $tags = [];
        foreach (LGL_Shortcodes::get_contact_fields_definition() as $key => $field) {
            $tags[$key] = $field['label'];
        }

        return $tags;
    }

    private function subject_tags(array $all_tags): array
    {
        $common = ['{{first_name}}', '{{last_name}}', '{{product_title}}', '{{product_price}}', '{{site_name}}', '{{date}}'];
        return array_filter($all_tags, fn($k) => in_array($k, $common, true), ARRAY_FILTER_USE_KEY);
    }

    private function grouped_tags(array $all_tags): array
    {
        $system_keys = array_keys($this->system_tags());
        $groups = ['Submitter' => [], 'Vehicle' => [], 'Site' => [], 'Form Fields' => []];

        foreach ($all_tags as $tag => $label) {
            if (in_array($tag, ['{{first_name}}', '{{last_name}}', '{{email}}', '{{phone}}'], true)) {
                $groups['Submitter'][$tag] = $label;
            } elseif (str_starts_with($tag, '{{product_')) {
                $groups['Vehicle'][$tag] = $label;
            } elseif (in_array($tag, array_merge(
                ['{{site_name}}', '{{site_url}}', '{{admin_email}}', '{{date}}', '{{time}}'],
                array_map(fn($k) => '{{' . $k . '}}', array_keys(self::get_contact_tag_definitions()))
            ), true)) {
                $groups['Site'][$tag] = $label;
            } elseif (! in_array($tag, $system_keys, true)) {
                $groups['Form Fields'][$tag] = $label;
            }
        }

        return array_filter($groups);
    }

    /* ═══════════════════════════════════════════════════════════════
       DEFAULT EMAIL TEMPLATES
    ═══════════════════════════════════════════════════════════════ */

    private function default_email(string $type): array
    {
        return [
            'subject'            => $type === 'enquiry'
                ? 'New Enquiry: {{first_name}} {{last_name}} — {{product_title}}'
                : 'New Reservation: {{first_name}} {{last_name}} — {{product_title}}',
            'body'               => $this->default_admin_body($type),
            'recipient_type'     => 'admin',
            'custom_email'       => '',
            'auto_reply_enabled' => false,
            'auto_reply_subject' => $type === 'enquiry'
                ? 'Thank you for your enquiry, {{first_name}}'
                : 'Your reservation request has been received, {{first_name}}',
            'auto_reply_body'    => $this->default_autoreply_body($type),
        ];
    }

    private function default_admin_body(string $type): string
    {
        $noun = $type === 'enquiry' ? 'Enquiry' : 'Reservation';
        return <<<HTML
<h2>New {$noun} Received</h2>
<p>A new {$noun} has been submitted on <a href="{{site_url}}">{{site_name}}</a>.</p>

<h3>Vehicle</h3>
<table>
  <tr><th>Vehicle</th><td><a href="{{product_url}}">{{product_title}}</a></td></tr>
  <tr><th>Price</th><td>{{product_price}}</td></tr>
</table>

<h3>Contact Details</h3>
<table>
  <tr><th>Name</th><td>{{first_name}} {{last_name}}</td></tr>
  <tr><th>Email</th><td><a href="mailto:{{email}}">{{email}}</a></td></tr>
  <tr><th>Phone</th><td>{{phone}}</td></tr>
</table>

<h3>Submission Info</h3>
<table>
  <tr><th>Date</th><td>{{date}} at {{time}}</td></tr>
</table>

<p><a class="eb-button" href="{{product_url}}">View Vehicle →</a></p>
HTML;
    }

    private function default_autoreply_body(string $type): string
    {
        $noun    = $type === 'enquiry' ? 'enquiry' : 'reservation request';
        $promise = $type === 'enquiry'
            ? 'A member of our team will be in touch with you shortly.'
            : 'We will hold the vehicle and contact you within 24 hours to confirm your visit.';

        return <<<HTML
<h2>Thank you, {{first_name}}!</h2>
<p>We have received your {$noun} for the <strong>{{product_title}}</strong>.</p>
<p>{$promise}</p>

<table>
  <tr><th>Vehicle</th><td><a href="{{product_url}}">{{product_title}}</a></td></tr>
  <tr><th>Price</th><td>{{product_price}}</td></tr>
  <tr><th>Name</th><td>{{first_name}} {{last_name}}</td></tr>
  <tr><th>Email</th><td>{{email}}</td></tr>
  <tr><th>Submitted</th><td>{{date}} at {{time}}</td></tr>
</table>

<p>Kind regards,<br><strong>{{site_name}}</strong></p>
HTML;
    }
}
