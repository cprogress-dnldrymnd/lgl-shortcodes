<?php
/**
 * LGL Modals Partial
 * Renders the Finance Calculator, Enquiry, and Reserve modals.
 * Included via LGL_Forms::render_modals() → wp_footer hook on single vehicle pages.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$fin       = LGL_Forms::get_finance_settings();
$enq       = LGL_Forms::get_enquiry_settings();
$rs        = LGL_Forms::get_reserve_settings();
$post_id   = get_the_ID();
$mode      = LGL_Forms::get_current_reserve_mode( $post_id );
$reserved  = LGL_Forms::is_reserved( $post_id );

// Build duration <option> list
$dur_raw   = $fin['durations'] ?? "1 Year\n2 Years\n3 Years\n4 Years\n5 Years";
$durations = array_values( array_filter( array_map( 'trim', explode( "\n", $dur_raw ) ) ) );
?>

<!-- ════════════════════════════════════════════════════════════
     OVERLAY
════════════════════════════════════════════════════════════ -->
<div class="lgl-modal-overlay" id="lgl-modal-overlay"></div>

<!-- ════════════════════════════════════════════════════════════
     FINANCE CALCULATOR MODAL
════════════════════════════════════════════════════════════ -->
<div class="lgl-modal" id="lgl-modal-finance" role="dialog" aria-modal="true" aria-labelledby="lgl-fc-title">
	<div class="lgl-modal-inner">
		<div class="lgl-modal-header">
			<div>
				<h2 id="lgl-fc-title"><?php echo esc_html( $fin['title'] ?? __( 'Finance Calculator', 'lgl-shortcodes' ) ); ?></h2>
				<?php if ( ! empty( $fin['subtitle'] ) ) : ?>
					<p class="lgl-modal-subtitle"><?php echo esc_html( $fin['subtitle'] ); ?></p>
				<?php endif; ?>
			</div>
			<button class="lgl-modal-close-btn" aria-label="<?php esc_attr_e( 'Close', 'lgl-shortcodes' ); ?>">&#x2715;</button>
		</div>
		<div class="lgl-modal-body">

			<!-- User inputs -->
			<div class="lgl-fc-inputs">
				<div class="lgl-fc-field">
					<label for="lgl-fc-deposit"><?php _e( 'Deposit', 'lgl-shortcodes' ); ?> <span class="lgl-form-req">(Required)</span></label>
					<div class="lgl-fc-input-wrap">
						<span class="lgl-fc-prefix">£</span>
						<input type="number" id="lgl-fc-deposit" placeholder="100" min="0" step="100" class="lgl-fc-input">
					</div>
				</div>
				<div class="lgl-fc-field">
					<label for="lgl-fc-duration"><?php _e( 'Duration', 'lgl-shortcodes' ); ?></label>
					<select id="lgl-fc-duration" class="lgl-fc-input">
						<?php foreach ( $durations as $d ) : ?>
							<option value="<?php echo esc_attr( $d ); ?>"><?php echo esc_html( $d ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>

			<button type="button" class="lgl-btn lgl-btn-primary lgl-fc-calc-btn" id="lgl-fc-calc-btn">
				<?php _e( 'CALCULATE', 'lgl-shortcodes' ); ?>
			</button>

			<!-- Calculated outputs -->
			<div class="lgl-fc-outputs" id="lgl-fc-outputs">
				<div class="lgl-fc-output-grid">
					<div class="lgl-fc-output-item"><span class="lgl-fc-out-label"><?php _e( 'Cash Price', 'lgl-shortcodes' ); ?></span><span class="lgl-fc-out-val" id="lgl-fc-cash-price">—</span></div>
					<div class="lgl-fc-output-item"><span class="lgl-fc-out-label"><?php _e( 'Deposit', 'lgl-shortcodes' ); ?></span><span class="lgl-fc-out-val" id="lgl-fc-deposit-out">—</span></div>
					<div class="lgl-fc-output-item"><span class="lgl-fc-out-label"><?php _e( 'Total Amount of Credit', 'lgl-shortcodes' ); ?></span><span class="lgl-fc-out-val" id="lgl-fc-credit">—</span></div>
					<div class="lgl-fc-output-item"><span class="lgl-fc-out-label"><?php _e( 'Agreement Duration', 'lgl-shortcodes' ); ?></span><span class="lgl-fc-out-val" id="lgl-fc-dur-out">—</span></div>
					<div class="lgl-fc-output-item"><span class="lgl-fc-out-label"><?php _e( 'Monthly Repayments of', 'lgl-shortcodes' ); ?></span><span class="lgl-fc-out-val" id="lgl-fc-monthly">—</span></div>
					<div class="lgl-fc-output-item"><span class="lgl-fc-out-label"><?php _e( 'Total Amount Repayable', 'lgl-shortcodes' ); ?></span><span class="lgl-fc-out-val" id="lgl-fc-total">—</span></div>
					<div class="lgl-fc-output-item"><span class="lgl-fc-out-label"><?php _e( 'Purchase Fee', 'lgl-shortcodes' ); ?></span><span class="lgl-fc-out-val" id="lgl-fc-fee">—</span></div>
					<div class="lgl-fc-output-item"><span class="lgl-fc-out-label"><?php _e( 'Interest Rate', 'lgl-shortcodes' ); ?></span><span class="lgl-fc-out-val" id="lgl-fc-rate">—</span></div>
					<div class="lgl-fc-output-item"><span class="lgl-fc-out-label"><?php _e( 'Representative APR', 'lgl-shortcodes' ); ?></span><span class="lgl-fc-out-val" id="lgl-fc-apr">—</span></div>
					<div class="lgl-fc-output-item lgl-fc-output-full"><span class="lgl-fc-out-label"><?php _e( 'Monthly Payment*', 'lgl-shortcodes' ); ?></span><span class="lgl-fc-out-val lgl-fc-out-big" id="lgl-fc-payment">—</span></div>
				</div>
			</div>

			<p class="lgl-fc-disclaimer"><?php echo esc_html( $fin['disclaimer'] ?? '' ); ?></p>
		</div>
	</div>
</div>

<!-- ════════════════════════════════════════════════════════════
     ENQUIRY MODAL
════════════════════════════════════════════════════════════ -->
<div class="lgl-modal" id="lgl-modal-enquiry" role="dialog" aria-modal="true" aria-labelledby="lgl-enq-title">
	<div class="lgl-modal-inner">
		<div class="lgl-modal-header">
			<h2 id="lgl-enq-title"><?php echo esc_html( $enq['title'] ?? __( 'Make an Enquiry', 'lgl-shortcodes' ) ); ?></h2>
			<button class="lgl-modal-close-btn" aria-label="<?php esc_attr_e( 'Close', 'lgl-shortcodes' ); ?>">&#x2715;</button>
		</div>
		<div class="lgl-modal-body">
			<form id="lgl-enquiry-form" class="lgl-modal-form" novalidate>
				<input type="hidden" name="action"          value="lgl_submit_enquiry">
				<input type="hidden" name="lgl_forms_nonce" value="<?php echo esc_attr( wp_create_nonce( 'lgl_forms_nonce' ) ); ?>">
				<input type="hidden" name="product_id"      value="<?php echo esc_attr( $post_id ); ?>">
				<div class="lgl-form-grid">
					<?php foreach ( ( $enq['fields'] ?? [] ) as $field ) : ?>
						<?php echo LGL_Forms::render_form_field( $field ); ?>
					<?php endforeach; ?>
				</div>
				<div class="lgl-form-msg" style="display:none"></div>
				<button type="submit" class="lgl-btn lgl-btn-accent lgl-form-submit-btn">
					<span class="lgl-submit-txt"><?php echo esc_html( $enq['submit_text'] ?? __( 'SUBMIT ENQUIRY', 'lgl-shortcodes' ) ); ?></span>
					<span class="lgl-submit-spin" style="display:none"></span>
				</button>
			</form>
		</div>
	</div>
</div>

<!-- ════════════════════════════════════════════════════════════
     RESERVE MODAL (only if form_only mode)
════════════════════════════════════════════════════════════ -->
<?php if ( $mode === 'form_only' ) : ?>
<div class="lgl-modal" id="lgl-modal-reserve" role="dialog" aria-modal="true" aria-labelledby="lgl-res-title">
	<div class="lgl-modal-inner">
		<div class="lgl-modal-header">
			<h2 id="lgl-res-title"><?php echo esc_html( $rs['title'] ?? __( 'Reserve this Leisure Vehicle for free', 'lgl-shortcodes' ) ); ?></h2>
			<button class="lgl-modal-close-btn" aria-label="<?php esc_attr_e( 'Close', 'lgl-shortcodes' ); ?>">&#x2715;</button>
		</div>
		<div class="lgl-modal-body">
			<form id="lgl-reserve-form" class="lgl-modal-form" novalidate>
				<input type="hidden" name="action"          value="lgl_submit_reserve">
				<input type="hidden" name="lgl_forms_nonce" value="<?php echo esc_attr( wp_create_nonce( 'lgl_forms_nonce' ) ); ?>">
				<input type="hidden" name="product_id"      value="<?php echo esc_attr( $post_id ); ?>">
				<div class="lgl-form-grid">
					<?php foreach ( ( $rs['fields'] ?? [] ) as $field ) : ?>
						<?php echo LGL_Forms::render_form_field( $field ); ?>
					<?php endforeach; ?>
				</div>
				<div class="lgl-form-msg" style="display:none"></div>
				<button type="submit" class="lgl-btn lgl-btn-outline lgl-form-submit-btn">
					<span class="lgl-submit-txt"><?php echo esc_html( $rs['submit_text'] ?? __( 'RESERVE YOUR LEISURE VEHICLE', 'lgl-shortcodes' ) ); ?></span>
					<span class="lgl-submit-spin" style="display:none"></span>
				</button>
			</form>
		</div>
	</div>
</div>
<?php endif; ?>

<!-- ════════════════════════════════════════════════════════════
     AUTO-RESERVE CONFIRM DIALOG
════════════════════════════════════════════════════════════ -->
<?php if ( $mode === 'auto_reserve' && ! $reserved ) : ?>
<div class="lgl-confirm-dialog" id="lgl-auto-reserve-confirm" style="display:none" role="dialog" aria-modal="true">
	<h3><?php _e( 'Reserve this Vehicle?', 'lgl-shortcodes' ); ?></h3>
	<p><?php _e( 'This will reserve the vehicle for you. Our sales team will be in touch shortly to confirm.', 'lgl-shortcodes' ); ?></p>
	<div class="lgl-confirm-actions">
		<button class="lgl-btn lgl-btn-secondary lgl-confirm-yes"><?php _e( 'Yes, Reserve Now', 'lgl-shortcodes' ); ?></button>
		<button class="lgl-btn lgl-btn-outline lgl-confirm-no"><?php _e( 'Cancel', 'lgl-shortcodes' ); ?></button>
	</div>
</div>
<?php endif; ?>
