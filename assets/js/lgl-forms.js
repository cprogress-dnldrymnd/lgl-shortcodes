/* ==========================================================================
   LGL Forms — Modal Management, Finance Calculator & Form Submissions
   This block is appended inside the existing (function($){ ... })(jQuery)
   wrapper in main.js — or loaded as a separate inline addition.
   ========================================================================== */

(function ($) {
    'use strict';

    /* ── Guard: only run when lglForms data is present ── */
    if (typeof lglForms === 'undefined') return;

    var F = lglForms; // shorthand

    /* ────────────────────────────────────────────────────────────
       INIT
    ──────────────────────────────────────────────────────────── */
    $(function () {
        initModalTriggers();
        initModalClose();
        initFinanceCalculator();
        initEnquiryForm();
        initReserveForm();
        initTimePickers();
        applyInitialReserveState();
    });

    /* ────────────────────────────────────────────────────────────
       MODAL OPEN / CLOSE
    ──────────────────────────────────────────────────────────── */

    function initModalTriggers() {
        $(document).on('click', '[data-lgl-modal]', function (e) {
            e.preventDefault();
            var modal = $(this).data('lgl-modal');
            openModal('lgl-modal-' + modal);
        });
    }

    function initModalClose() {
        $(document).on('click', '.lgl-modal-close-btn', closeAllModals);
        $(document).on('click', '#lgl-modal-overlay', closeAllModals);
        $(document).on('keydown', function (e) {
            if (e.key === 'Escape') closeAllModals();
        });
    }

    function openModal(id) {
        closeAllModals();
        var $m = $('#' + id);
        if (!$m.length) return;
        $('#lgl-modal-overlay').addClass('lgl-overlay-active');
        $m.addClass('lgl-modal-active');
        
        // Auto-run calculator on open if native mode is active
        if (id === 'lgl-modal-finance' && F.financeMode !== 'off' && F.financeMode !== 'custom') {
            calcFinance();
        }

        setTimeout(function () {
            $m.find('input:visible:first, select:visible:first').trigger('focus');
        }, 120);
    }

    function closeAllModals() {
        $('.lgl-modal.lgl-modal-active').removeClass('lgl-modal-active');
        $('#lgl-modal-overlay').removeClass('lgl-overlay-active');
        $('#lgl-auto-reserve-confirm').hide();
    }

    /* ────────────────────────────────────────────────────────────
       FINANCE CALCULATOR
    ──────────────────────────────────────────────────────────── */

    function initFinanceCalculator() {
        // Run explicitly on button click (fallback)
        $(document).on('click', '#lgl-fc-calc-btn', calcFinance);
        
        // Auto-recalculate on input changes as per spec
        $(document).on('input change', '#lgl-fc-deposit, #lgl-fc-duration', calcFinance);
        
        // Fallback for enter key
        $(document).on('keypress', '#lgl-modal-finance input, #lgl-modal-finance select', function (e) {
            if (e.which === 13) {
                e.preventDefault();
                calcFinance();
            }
        });
    }

    function calcFinance() {
        $('.lgl-fc-calc-error').remove();

        var cashPrice = parseFloat(F.cashPrice) || 0;
        var deposit = parseFloat($('#lgl-fc-deposit').val()) || 0;
        var termMonths = parseInt($('#lgl-fc-duration').val()) || parseInt(F.defaultTerm) || 60;
        
        var minDeposit = parseFloat(F.minDeposit) || 0;
        var aprRate = parseFloat(F.aprRate) || 10.90;
        var purchaseFee = parseFloat(F.purchaseFee) || 0;
        var adminFee = parseFloat(F.adminFee) || 0;
        var calcType = F.calculationType || 'apr'; // 'apr' or 'flat'

        // Validation constraints
        if (cashPrice <= 0) {
            showCalcErr('Cash price is not set for this vehicle.');
            return;
        }
        if (deposit < minDeposit) {
            showCalcErr('Minimum deposit required is ' + fmt(minDeposit) + '.');
            return;
        }
        if (deposit >= cashPrice) {
            showCalcErr('Deposit cannot be equal to or greater than the cash price.');
            return;
        }
        if (termMonths <= 0) {
            showCalcErr('Please select a valid duration.');
            return;
        }

        /* ── Core Values ── */
        var amountFinanced = cashPrice - deposit;
        var monthlyPayment = 0;
        var totalInterest = 0;
        var interestRateDisplay = '';

        /* ── Mathematical Routing ── */
        if (calcType === 'flat') {
            // 3.3 Flat Rate Calculation
            var years = termMonths / 12;
            totalInterest = amountFinanced * (aprRate / 100) * years;
            monthlyPayment = (amountFinanced + totalInterest) / termMonths;
            interestRateDisplay = aprRate.toFixed(2) + '% p.a. (Flat)';
        } else {
            // 3.2 APR Calculation (Primary)
            var monthlyRate = (aprRate / 100) / 12;
            monthlyPayment = (amountFinanced * monthlyRate) / (1 - Math.pow(1 + monthlyRate, -termMonths));
            interestRateDisplay = aprRate.toFixed(2) + '% p.a. (Fixed)';
        }

        /* ── Totals ── */
        var totalCredit = amountFinanced;
        var totalRepayable = (monthlyPayment * termMonths) + purchaseFee + adminFee;

        // Dom Output Routing
        $('#lgl-fc-cash-price').text(fmt(cashPrice));
        $('#lgl-fc-deposit-out').text(fmt(deposit));
        $('#lgl-fc-credit').text(fmt(totalCredit));
        $('#lgl-fc-dur-out').text(termMonths + ' months');
        $('#lgl-fc-monthly').text(fmt(monthlyPayment));
        $('#lgl-fc-total').text(fmt(totalRepayable));
        
        // Output compound fee string if admin fee is present, otherwise just purchase fee
        var feeString = fmt(purchaseFee);
        if (adminFee > 0) feeString += ' (+ ' + fmt(adminFee) + ' Admin)';
        $('#lgl-fc-fee').text(feeString);
        
        $('#lgl-fc-rate').text(interestRateDisplay);
        $('#lgl-fc-apr').text(F.representativeApr); // Standardized string output
        $('#lgl-fc-payment').text(fmt(monthlyPayment));
    }

    function fmt(n) {
        return '£' + n.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }

    function showCalcErr(msg) {
        // Output zero defaults visually when there is an error to prevent hanging math visuals
        $('.lgl-fc-out-val').not('#lgl-fc-cash-price').text('—'); 
        $('#lgl-fc-calc-btn').before('<div class="lgl-fc-calc-error" style="color:#d63638;font-size:13px;margin-bottom:12px;">' + msg + '</div>');
    }

    /* ────────────────────────────────────────────────────────────
       ENQUIRY FORM
    ──────────────────────────────────────────────────────────── */

    function initEnquiryForm() {
        $(document).on('submit', '#lgl-enquiry-form', function (e) {
            e.preventDefault();
            var $f = $(this);
            if (!validateForm($f)) return;
            submitForm($f, 'enquiry');
        });
    }

    /* ────────────────────────────────────────────────────────────
       RESERVE FORM
    ──────────────────────────────────────────────────────────── */

    function initReserveForm() {
        $(document).on('submit', '#lgl-reserve-form', function (e) {
            e.preventDefault();
            var $f = $(this);
            if (!validateForm($f)) return;
            submitForm($f, 'reserve');
        });
    }

    /* ────────────────────────────────────────────────────────────
       APPLY INITIAL RESERVE STATE (already reserved on page load)
    ──────────────────────────────────────────────────────────── */

    function applyInitialReserveState() {
        if (!F.isReserved) return;
        $('.lgl-reserve-btn')
            .text(F.reservedBtnText)
            .addClass('lgl-btn-reserved')
            .prop('disabled', true)
            .removeAttr('data-lgl-modal')
            .removeAttr('data-lgl-action');
    }

    /* ────────────────────────────────────────────────────────────
       SHARED FORM SUBMISSION
    ──────────────────────────────────────────────────────────── */

    function submitForm($form, type) {
        var $btn = $form.find('.lgl-form-submit-btn');
        var $txt = $btn.find('.lgl-submit-txt');
        var $spin = $btn.find('.lgl-submit-spin');

        $btn.prop('disabled', true);
        $txt.hide();
        $spin.show();
        $form.find('.lgl-form-msg').hide().removeClass('lgl-msg-error lgl-msg-success');

        var data = $form.serialize();
        data += '&lgl_forms_nonce=' + encodeURIComponent(F.nonce);

        $.ajax({
            url: F.ajaxUrl,
            method: 'POST',
            data: data,
            success: function (res) {
                $btn.prop('disabled', false);
                $txt.show();
                $spin.hide();

                if (res.success) {
                    showFormMsg($form, res.data.message, 'success');
                    $form.find('input:not([type=hidden]), select, textarea').val('');

                    if (type === 'reserve' && res.data.reserved_btn) {
                        $('.lgl-reserve-btn')
                            .text(res.data.reserved_btn)
                            .addClass('lgl-btn-reserved')
                            .prop('disabled', true)
                            .removeAttr('data-lgl-modal');
                    }

                    /* Auto-close after a short delay */
                    setTimeout(closeAllModals, 3200);
                } else {
                    showFormMsg($form, res.data.message || 'Something went wrong. Please try again.', 'error');
                }
            },
            error: function () {
                $btn.prop('disabled', false);
                $txt.show();
                $spin.hide();
                showFormMsg($form, 'Network error. Please check your connection.', 'error');
            }
        });
    }

    /* ────────────────────────────────────────────────────────────
       FORM VALIDATION
    ──────────────────────────────────────────────────────────── */

    function validateForm($form) {
        var ok = true;
        $form.find('[required]').each(function () {
            $(this).removeClass('lgl-input-err');
            if ($(this).val().trim() === '') {
                $(this).addClass('lgl-input-err');
                ok = false;
            }
        });
        $form.find('input[type=email]').each(function () {
            if ($(this).val() && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test($(this).val())) {
                $(this).addClass('lgl-input-err');
                ok = false;
            }
        });
        if (!ok) {
            showFormMsg($form, 'Please fill in all required fields correctly.', 'error');
            $form.find('.lgl-input-err:first').trigger('focus');
        }
        return ok;
    }

    function showFormMsg($form, msg, type) {
        var $m = $form.find('.lgl-form-msg');
        $m.text(msg)
            .removeClass('lgl-msg-error lgl-msg-success')
            .addClass('lgl-msg-' + type)
            .show();
        $m[0].scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    /* ────────────────────────────────────────────────────────────
       TIME PICKERS — sync HH + MM + AM/PM into hidden field
    ──────────────────────────────────────────────────────────── */

    function initTimePickers() {
        $(document).on('input change', '.lgl-time-hh, .lgl-time-mm, .lgl-time-ampm', function () {
            var $p = $(this).closest('.lgl-time-picker');
            var hh = $p.find('.lgl-time-hh').val() || '';
            var mm = $p.find('.lgl-time-mm').val() || '';
            var ampm = $p.find('.lgl-time-ampm').val() || 'AM';
            if (hh && mm !== '') {
                $p.find('.lgl-time-val').val(hh + ':' + String(mm).padStart(2, '0') + ' ' + ampm);
            }
        });
    }

    /* ────────────────────────────────────────────────────────────
       TOAST NOTIFICATION (reuses existing lgl notification system)
    ──────────────────────────────────────────────────────────── */

    function lglToast(message, type) {
        /* Reuse the existing window.showNotification if available */
        if (typeof window.showNotification === 'function') {
            window.showNotification(message, type === 'success' ? 'success' : 'error');
            return;
        }
        /* Fallback */
        var $t = $('<div class="lgl-toast lgl-toast-' + type + ' show">' + message + '</div>');
        $('#lgl-notification-container').append($t);
        setTimeout(function () { $t.removeClass('show'); setTimeout(function () { $t.remove(); }, 300); }, 3000);
    }

})(jQuery);