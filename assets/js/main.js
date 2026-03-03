/**
 * Frontend execution logic for LGL Shortcodes.
 * Handles Select2 initialization and AJAX operations.
 */
jQuery(document).ready(function($) {
    
    // Initialize Select2 on target classes
    $('.lgl-select2').select2({
        width: '100%'
    });

    // Dependent Dropdown Logic (Make -> Model)
    $('#lgl_make').on('change', function() {
        let make_id = $(this).val();
        let $model_select = $('#lgl_model');

        // Reset model dropdown
        $model_select.empty().append('<option value="">Select Model</option>').prop('disabled', true);

        if(make_id) {
            $.ajax({
                url: lgl_ajax_obj.ajax_url,
                type: 'POST',
                data: {
                    action: 'lgl_get_models',
                    nonce: lgl_ajax_obj.nonce,
                    make_id: make_id
                },
                success: function(response) {
                    if(response.success && response.data.length > 0) {
                        $.each(response.data, function(index, item) {
                            $model_select.append(new Option(item.text, item.id, false, false));
                        });
                        $model_select.prop('disabled', false).trigger('change');
                    }
                }
            });
        }
    });

    // Handle Search Execution
    $('#lgl-search-form, #lgl-sort-order').on('submit change', function(e) {
        if(e.type === 'submit') e.preventDefault();

        // Serialize primary form and combine with sorting value
        let formData = $('#lgl-search-form').serialize() + '&sort_order=' + $('#lgl-sort-order').val();
        let postType = $('#lgl_target_post_type').val();

        // UI State management
        $('#lgl-loader').show();
        $('#lgl-results-grid').css('opacity', '0.5');

        $.ajax({
            url: lgl_ajax_obj.ajax_url,
            type: 'POST',
            data: {
                action: 'lgl_fetch_results',
                nonce: lgl_ajax_obj.nonce,
                post_type: postType,
                form_data: formData
            },
            success: function(response) {
                if(response.success) {
                    $('#lgl-results-grid').html(response.data.html);
                    $('#lgl-results-count').html('Showing ' + response.data.count + ' results');
                } else {
                    alert('Error fetching results.');
                }
            },
            error: function() {
                alert('A server error occurred. Please try again.');
            },
            complete: function() {
                $('#lgl-loader').hide();
                $('#lgl-results-grid').css('opacity', '1');
            }
        });
    });

    // Trigger initial search to populate grid on load
    if ($('#lgl-search-form').length) {
        $('#lgl-search-form').trigger('submit');
    }
});