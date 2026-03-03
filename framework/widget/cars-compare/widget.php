<?php
namespace AutoArtElementorWidgets\Widgets\CarsCompare;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class Widget_CarsCompare extends Widget_Base {

	public function get_name() {
		return 'bt-cars-compare';
	}

	public function get_title() {
		return __( 'Cars Compare', 'autoart' );
	}

  public function get_icon() {
		return 'eicon-posts-ticker';
	}

	public function get_categories() {
		return [ 'autoart' ];
	}

	protected function register_layout_section_controls() {
		$this->start_controls_section(
			'section_layout',
			[
				'label' => __( 'Layout', 'autoart' ),
			]
		);

		$this->end_controls_section();
	}


	protected function register_style_section_controls() {
    $this->start_controls_section(
			'section_style_content',
			[
				'label' => esc_html__( 'Content', 'autoart' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->end_controls_section();
	}

	protected function register_controls() {

		$this->register_layout_section_controls();
		$this->register_style_section_controls();

	}

	public function post_social_share() {

		$social_item = array();
		$social_item[] = '<li>
                        <a target="_blank" data-btIcon="fa fa-facebook" data-toggle="tooltip" title="'.esc_attr__('Facebook', 'autoart').'" href="https://www.facebook.com/sharer/sharer.php?u='.get_the_permalink().'">
                          <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 320 512">
                            <path d="M279.14 288l14.22-92.66h-88.91v-60.13c0-25.35 12.42-50.06 52.24-50.06h40.42V6.26S260.43 0 225.36 0c-73.22 0-121.08 44.38-121.08 124.72v70.62H22.89V288h81.39v224h100.17V288z"/>
                          </svg>
                        </a>
                      </li>';
		$social_item[] = '<li>
                        <a target="_blank" data-btIcon="fa fa-twitter" data-toggle="tooltip" title="'.esc_attr__('Twitter', 'autoart').'" href="https://twitter.com/share?url='.get_the_permalink().'">
                          <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 512 512">
                            <path d="M389.2 48h70.6L305.6 224.2 487 464H345L233.7 318.6 106.5 464H35.8L200.7 275.5 26.8 48H172.4L272.9 180.9 389.2 48zM364.4 421.8h39.1L151.1 88h-42L364.4 421.8z"/>
                          </svg>
                        </a>
                      </li>';
		$social_item[] = '<li>
                        <a target="_blank" data-btIcon="fa fa-google-plus" data-toggle="tooltip" title="'.esc_attr__('Google Plus', 'autoart').'" href="https://plus.google.com/share?url='.get_the_permalink().'">
                          <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 488 512">
                            <path d="M488 261.8C488 403.3 391.1 504 248 504 110.8 504 0 393.2 0 256S110.8 8 248 8c66.8 0 123 24.5 166.3 64.9l-67.5 64.9C258.5 52.6 94.3 116.6 94.3 256c0 86.5 69.1 156.6 153.7 156.6 98.2 0 135-70.4 140.8-106.9H248v-85.3h236.1c2.3 12.7 3.9 24.9 3.9 41.4z"/>
                          </svg>
                        </a>
                      </li>';
		$social_item[] = '<li>
                        <a target="_blank" data-btIcon="fa fa-linkedin" data-toggle="tooltip" title="'.esc_attr__('Linkedin', 'autoart').'" href="https://www.linkedin.com/shareArticle?url='.get_the_permalink().'">
                          <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 448 512">
                            <path d="M100.28 448H7.4V148.9h92.88zM53.79 108.1C24.09 108.1 0 83.5 0 53.8a53.79 53.79 0 0 1 107.58 0c0 29.7-24.1 54.3-53.79 54.3zM447.9 448h-92.68V302.4c0-34.7-.7-79.2-48.29-79.2-48.29 0-55.69 37.7-55.69 76.7V448h-92.78V148.9h89.08v40.8h1.3c12.4-23.5 42.69-48.3 87.88-48.3 94 0 111.28 61.9 111.28 142.3V448z"/>
                          </svg>
                        </a>
                      </li>';
		$social_item[] = '<li>
                        <a target="_blank" data-btIcon="fa fa-pinterest" data-toggle="tooltip" title="'.esc_attr__('Pinterest', 'autoart').'" href="https://pinterest.com/pin/create/button/?url='.get_the_permalink().'">
                          <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 496 512">
                            <path d="M496 256c0 137-111 248-248 248-25.6 0-50.2-3.9-73.4-11.1 10.1-16.5 25.2-43.5 30.8-65 3-11.6 15.4-59 15.4-59 8.1 15.4 31.7 28.5 56.8 28.5 74.8 0 128.7-68.8 128.7-154.3 0-81.9-66.9-143.2-152.9-143.2-107 0-163.9 71.8-163.9 150.1 0 36.4 19.4 81.7 50.3 96.1 4.7 2.2 7.2 1.2 8.3-3.3.8-3.4 5-20.3 6.9-28.1.6-2.5.3-4.7-1.7-7.1-10.1-12.5-18.3-35.3-18.3-56.6 0-54.7 41.4-107.6 112-107.6 60.9 0 103.6 41.5 103.6 100.9 0 67.1-33.9 113.6-78 113.6-24.3 0-42.6-20.1-36.7-44.8 7-29.5 20.5-61.3 20.5-82.6 0-19-10.2-34.9-31.4-34.9-24.9 0-44.9 25.7-44.9 60.2 0 22 7.4 36.8 7.4 36.8s-24.5 103.8-29 123.2c-5 21.4-3 51.6-.9 71.2C65.4 450.9 0 361.1 0 256 0 119 111 8 248 8s248 111 248 248z"/>
                          </svg>
                        </a>
                      </li>';

		ob_start();
		?>
			<div class="bt-post-share">
				<?php
					if(!empty($social_item)){
						echo '<span>'.esc_html__('Share: ', 'autoart').'</span><ul>'.implode(' ', $social_item).'</ul>';
					}
				?>
			</div>
		<?php
		return ob_get_clean();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$carcompare = '';
		$car_ids = array();

		$ex_items = count($car_ids) < 3 ? 3 - count($car_ids) : 0;

    ?>
		<div class="bt-elwg-cars-compare--default">
			<form class="bt-cars-compare-form" action="" method="post">
				<input type="hidden" class="bt-carcomparelocal" name="carcomparelocal" value="">

				<div class="bt-table">
					<div class="bt-table--head">
						<div class="bt-table--row">
							<div class="bt-table--col bt-car-information">
								<?php echo '<span>' . __('Information', 'autoart') . '</span>'; ?>
							</div>
							<div class="bt-table--col bt-car-body">
								<?php echo '<span class="bt-label">' . __('Body', 'autoart') . '</span>'; ?>
							</div>
							<div class="bt-table--col bt-car-condition">
								<?php echo '<span class="bt-label">' . __('Condition', 'autoart') . '</span>'; ?>
							</div>
							<div class="bt-table--col bt-car-mileage">
								<?php echo '<span class="bt-label">' . __('Mileage', 'autoart') . '</span>'; ?>
							</div>
							<div class="bt-table--col bt-car-engine-size">
								<?php echo '<span class="bt-label">' . __('Engine Size', 'autoart') . '</span>'; ?>
							</div>
							<div class="bt-table--col bt-car-fuel-type">
								<?php echo '<span class="bt-label">' . __('Fuel Type', 'autoart') . '</span>'; ?>
							</div>
							<div class="bt-table--col bt-car-door">
								<?php echo '<span class="bt-label">' . __('Door', 'autoart') . '</span>'; ?>
							</div>
							<div class="bt-table--col bt-car-year">
								<?php echo '<span class="bt-label">' . __('Year', 'autoart') . '</span>'; ?>
							</div>
							<div class="bt-table--col bt-car-cylinder">
								<?php echo '<span class="bt-label">' . __('Cylinder', 'autoart') . '</span>'; ?>
							</div>
							<div class="bt-table--col bt-car-transmission">
								<?php echo '<span class="bt-label">' . __('Transmission', 'autoart') . '</span>'; ?>
							</div>
							<div class="bt-table--col bt-car-color">
								<?php echo '<span class="bt-label">' . __('Color', 'autoart') . '</span>'; ?>
							</div>
							<div class="bt-table--col bt-car-features">
								<?php echo '<span class="bt-label">' . __('Features', 'autoart') . '</span>'; ?>
							</div>
						</div>
					</div>

					<div class="bt-table--body">
						<span class="bt-loading-wave"></span>
						
						<div class="bt-car-list">
							<?php foreach ($car_ids as $key => $id) { ?>
								<div class="bt-table--row bt-car-item">
									<div class="bt-table--col bt-car-information">
										<div class="bt-car-remove">
											<a href="#" data-id="<?php echo esc_attr($id); ?>" class="bt-car-remove-compare">
												<svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" width="512" height="512" x="0" y="0" viewBox="0 0 512 512" fill="currentColor">
													<path d="M424 64h-88V48c0-26.467-21.533-48-48-48h-64c-26.467 0-48 21.533-48 48v16H88c-22.056 0-40 17.944-40 40v56c0 8.836 7.164 16 16 16h8.744l13.823 290.283C87.788 491.919 108.848 512 134.512 512h242.976c25.665 0 46.725-20.081 47.945-45.717L439.256 176H448c8.836 0 16-7.164 16-16v-56c0-22.056-17.944-40-40-40zM208 48c0-8.822 7.178-16 16-16h64c8.822 0 16 7.178 16 16v16h-96zM80 104c0-4.411 3.589-8 8-8h336c4.411 0 8 3.589 8 8v40H80zm313.469 360.761A15.98 15.98 0 0 1 377.488 480H134.512a15.98 15.98 0 0 1-15.981-15.239L104.78 176h302.44z"></path>
													<path d="M256 448c8.836 0 16-7.164 16-16V224c0-8.836-7.164-16-16-16s-16 7.164-16 16v208c0 8.836 7.163 16 16 16zM336 448c8.836 0 16-7.164 16-16V224c0-8.836-7.164-16-16-16s-16 7.164-16 16v208c0 8.836 7.163 16 16 16zM176 448c8.836 0 16-7.164 16-16V224c0-8.836-7.164-16-16-16s-16 7.164-16 16v208c0 8.836 7.163 16 16 16z"></path>
												</svg>
												<svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" width="512" height="512" x="0" y="0" viewBox="0 0 512 512" fill="currentColor">
													<path d="M493.815 70.629c-11.001-1.003-20.73 7.102-21.733 18.102l-2.65 29.069C424.473 47.194 346.429 0 256 0 158.719 0 72.988 55.522 30.43 138.854c-5.024 9.837-1.122 21.884 8.715 26.908 9.839 5.024 21.884 1.123 26.908-8.715C102.07 86.523 174.397 40 256 40c74.377 0 141.499 38.731 179.953 99.408l-28.517-20.367c-8.989-6.419-21.48-4.337-27.899 4.651-6.419 8.989-4.337 21.479 4.651 27.899l86.475 61.761c12.674 9.035 30.155.764 31.541-14.459l9.711-106.53c1.004-11.001-7.1-20.731-18.1-21.734zM472.855 346.238c-9.838-5.023-21.884-1.122-26.908 8.715C409.93 425.477 337.603 472 256 472c-74.377 0-141.499-38.731-179.953-99.408l28.517 20.367c8.989 6.419 21.479 4.337 27.899-4.651 6.419-8.989 4.337-21.479-4.651-27.899l-86.475-61.761c-12.519-8.944-30.141-.921-31.541 14.459L.085 419.637c-1.003 11 7.102 20.73 18.101 21.733 11.014 1.001 20.731-7.112 21.733-18.102l2.65-29.069C87.527 464.806 165.571 512 256 512c97.281 0 183.012-55.522 225.57-138.854 5.024-9.837 1.122-21.884-8.715-26.908z"></path>
												</svg>
											</a>
										</div>
										<div class="bt-car-thumb">
											<a href="<?php echo get_the_permalink($id); ?>">
												<div class="bt-cover-image">
													<?php echo get_the_post_thumbnail($id, 'medium'); ?>
												</div>
											</a>
										</div>
										<h3 class="bt-car-title">
											<a href="<?php echo get_the_permalink($id); ?>">
												<?php echo get_the_title($id); ?>
											</a>
										</h3>
										<div class="bt-car-price">
											<?php
												$price = get_field('car_price', $id);

												if(!empty($price)) {
													echo '<span>$' . number_format($price, 0) . '</span>';
												} else {
													echo '<a href="#">' . esc_html__('Call for price', 'autoart') . '</a>';
												}
											?>
										</div>
									</div>
									<div class="bt-table--col bt-car-body">
										<?php
											$body = get_the_terms( $id, 'car_body' );

											if(!empty($body)) {
												$term = array_pop($body);
												echo '<span class="bt-value">' . $term->name . '</span>';
											} else {
												echo '<span class="bt-value">' . esc_html__('N/A', 'autoart') . '</span>';
											}
										?>
									</div>
									<div class="bt-table--col bt-car-condition">
										<?php
											$condition = get_the_terms( $id, 'car_condition' );

											if(!empty($condition)) {
												$term = array_pop($condition);
												echo '<span class="bt-value">' . $term->name . '</span>';
											} else {
												echo '<span class="bt-value">' . esc_html__('N/A', 'autoart') . '</span>';
											}
										?>
									</div>
									<div class="bt-table--col bt-car-mileage">
										<?php
											$mileage = get_field('car_mileage', $id);

											if(!empty($mileage)) {
												echo '<span class="bt-value">' . number_format($mileage, 0) . esc_html__(' km', 'autoart') . '</span>';
											} else {
												echo '<span class="bt-value">' . esc_html__('N/A', 'autoart') . '</span>';
											}
										?>
									</div>
									<div class="bt-table--col bt-car-engine-size">
										<?php
											$engine = get_the_terms( $id, 'car_engine' );

											if(!empty($engine)) {
												$term = array_pop($engine);
												echo '<span class="bt-value">' . $term->name . '</span>';
											} else {
												echo '<span class="bt-value">' . esc_html__('N/A', 'autoart') . '</span>';
											}
										?>
									</div>
									<div class="bt-table--col bt-car-fuel-type">
										<?php
											$fuel_type = get_the_terms( $id, 'car_fuel_type' );

											if(!empty($fuel_type)) {
												$term = array_pop($fuel_type);
												echo '<span class="bt-value">' . $term->name . '</span>';
											} else {
												echo '<span class="bt-value">' . esc_html__('N/A', 'autoart') . '</span>';
											}
										?>
									</div>
									<div class="bt-table--col bt-car-door">
										<?php
											$door = get_the_terms( $id, 'car_door' );

											if(!empty($door)) {
												$term = array_pop($door);
												echo '<span class="bt-value">' . $term->name . '</span>';
											} else {
												echo '<span class="bt-value">' . esc_html__('N/A', 'autoart') . '</span>';
											}
										?>
									</div>
									<div class="bt-table--col bt-car-year">
										<?php
											$year = get_field('car_year', $id);

											if(!empty($year)) {
												echo '<span class="bt-value">' . $year . '</span>';
											} else {
												echo '<span class="bt-value">' . esc_html__('N/A', 'autoart') . '</span>';
											}
										?>
									</div>
									<div class="bt-table--col bt-car-cylinder">
										<?php
											$cylinder = get_the_terms( $id, 'car_cylinder' );

											if(!empty($cylinder)) {
												$term = array_pop($cylinder);
												echo '<span class="bt-value">' . $term->name . '</span>';
											} else {
												echo '<span class="bt-value">' . esc_html__('N/A', 'autoart') . '</span>';
											}
										?>
									</div>
									<div class="bt-table--col bt-car-transmission">
										<?php
											$transmission = get_the_terms( $id, 'car_transmission' );

											if(!empty($transmission)) {
												$term = array_pop($transmission);
												echo '<span class="bt-value">' . $term->name . '</span>';
											} else {
												echo '<span class="bt-value">' . esc_html__('N/A', 'autoart') . '</span>';
											}
										?>
									</div>
									<div class="bt-table--col bt-car-color">
										<?php
											$color_term = get_the_terms( $id, 'car_color' );
											$color_arr = array();
											if(!empty($color_term)) {
												foreach ($color_term as $color) {
													$color_arr[] = $color->name;
												}
											}

											if(!empty($color_arr)) {
												echo '<span class="bt-value">' . implode(', ', $color_arr) . '</span>';
											} else {
												echo '<span class="bt-value">' . esc_html__('N/A', 'autoart') . '</span>';
											}
										?>
									</div>
									<div class="bt-table--col bt-car-features">
										<?php $features = get_field('car_features', $id); ?>

										<?php if(!empty($features)) { ?>
											<div class="bt-feature-list">
												<?php foreach($features as $feature) { ?>
										<div class="bt-feature-item">
											<svg width="26" height="26" viewBox="0 0 26 26" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
											<path fill-rule="evenodd" clip-rule="evenodd" d="M14.8948 0.536215C14.3234 0.190003 13.6681 0.00695801 13 0.00695801C12.3319 0.00695801 11.6766 0.190003 11.1053 0.536215L2.574 5.70696C2.03639 6.03272 1.59185 6.49158 1.28329 7.03924C0.974733 7.5869 0.812585 8.20486 0.8125 8.83346V17.1665C0.812585 17.7951 0.974733 18.413 1.28329 18.9607C1.59185 19.5084 2.03639 19.9672 2.574 20.293L11.1053 25.4637C11.6766 25.8099 12.3319 25.993 13 25.993C13.6681 25.993 14.3234 25.8099 14.8948 25.4637L23.426 20.293C23.9636 19.9672 24.4082 19.5084 24.7167 18.9607C25.0253 18.413 25.1874 17.7951 25.1875 17.1665V8.83346C25.1874 8.20486 25.0253 7.5869 24.7167 7.03924C24.4082 6.49158 23.9636 6.03272 23.426 5.70696L14.8948 0.536215ZM16.6075 9.29496C16.7191 9.17522 16.8536 9.07918 17.0031 9.01257C17.1526 8.94596 17.314 8.91014 17.4777 8.90725C17.6413 8.90437 17.8038 8.93447 17.9556 8.99577C18.1074 9.05706 18.2452 9.1483 18.3609 9.26403C18.4767 9.37976 18.5679 9.51761 18.6292 9.66937C18.6905 9.82112 18.7206 9.98367 18.7177 10.1473C18.7148 10.311 18.679 10.4723 18.6124 10.6218C18.5458 10.7713 18.4497 10.9059 18.33 11.0175L11.83 17.5175C11.6015 17.7457 11.2917 17.8739 10.9688 17.8739C10.6458 17.8739 10.336 17.7457 10.1075 17.5175L6.8575 14.2675C6.64222 14.0364 6.52502 13.7309 6.53059 13.4151C6.53616 13.0994 6.66407 12.7981 6.88736 12.5748C7.11066 12.3515 7.41191 12.2236 7.72765 12.2181C8.04339 12.2125 8.34897 12.3297 8.58 12.545L10.9688 14.9337L16.6075 9.29496Z"/>
											</svg>
											<?php echo '<span>' . $feature['label'] . '</span>'; ?>
										</div>
										<?php } ?>
											</div>
										<?php } ?>
									</div>
								</div>
							<?php } ?>
							
							<?php 
								if($ex_items > 0) {
									for ($i=0; $i < $ex_items; $i++) {
										?>
											<div class="bt-table--row bt-car-item">
												<div class="bt-table--col bt-car-add-compare">
													<div class="bt-car-thumb">
														<a href="/cars/">
															<div class="bt-cover-image">
																<svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" width="512" height="512" x="0" y="0" viewBox="0 0 512 512" fill="currentColor">
																	<path d="M256 512a25 25 0 0 1-25-25V25a25 25 0 0 1 50 0v462a25 25 0 0 1-25 25z"></path>
																	<path d="M487 281H25a25 25 0 0 1 0-50h462a25 25 0 0 1 0 50z"></path>
																</svg>
															</div>
														</a>
													</div>
													<h3 class="bt-car-title">
														<a href="/cars/">
															<?php echo __('Add Car To Compare', 'autoart'); ?>
														</a>
													</h3>
												</div>
												<div class="bt-table--col bt-car-body"></div>
												<div class="bt-table--col bt-car-condition"></div>
												<div class="bt-table--col bt-car-mileage"></div>
												<div class="bt-table--col bt-car-engine-size"></div>
												<div class="bt-table--col bt-car-fuel-type"></div>
												<div class="bt-table--col bt-car-door"></div>
												<div class="bt-table--col bt-car-year"></div>
												<div class="bt-table--col bt-car-cylinder"></div>
												<div class="bt-table--col bt-car-transmission"></div>
												<div class="bt-table--col bt-car-color"></div>
												<div class="bt-table--col bt-car-features"></div>
											</div>
										<?php
									}
								}
							?>
						</div>
					</div>

					<div class="bt-table--foot">
						<div class="bt-table--row">
							<?php echo '<div class="bt-table--col bt-social-share">' . $this->post_social_share() . '</div>'; ?>
						</div>
					</div>
				</div>
			</form>
		</div>
    <?php
	}

	protected function content_template() {

	}

}
