<?php

namespace AutoArtElementorWidgets\Widgets\CarsQuickCompare;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class Widget_CarsQuickCompare extends Widget_Base
{

	public function get_name()
	{
		return 'bt-cars-quick-compare';
	}

	public function get_title()
	{
		return __('Cars Quick Compare', 'autoart');
	}

	public function get_icon()
	{
		return 'eicon-posts-ticker';
	}

	public function get_categories()
	{
		return ['autoart'];
	}

	public function get_script_depends()
	{
		return ['elementor-widgets'];
	}

	protected function get_supported_ids()
	{
		$supported_ids = [];

		$wp_query = new \WP_Query(array(
			'post_type' => 'car',
			'post_status' => 'publish'
		));

		if ($wp_query->have_posts()) {
			while ($wp_query->have_posts()) {
				$wp_query->the_post();
				$supported_ids[get_the_ID()] = get_the_title();
			}
		}

		return $supported_ids;
	}

	protected function register_layout_section_controls()
	{
		$this->start_controls_section(
			'section_layout',
			[
				'label' => __('Layout', 'autoart'),
			]
		);

		$this->add_control(
			'first_car',
			[
				'label' => __('First Car', 'autoart'),
				'type' => Controls_Manager::SELECT2,
				'options' => $this->get_supported_ids(),
				'label_block' => true,
			]
		);

		$this->add_control(
			'second_car',
			[
				'label' => __('Second Car', 'autoart'),
				'type' => Controls_Manager::SELECT2,
				'options' => $this->get_supported_ids(),
				'label_block' => true,
			]
		);

		$this->end_controls_section();
	}


	protected function register_style_section_controls()
	{
		$this->start_controls_section(
			'section_style_content',
			[
				'label' => esc_html__('Content', 'autoart'),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->end_controls_section();
	}

	protected function register_controls()
	{

		$this->register_layout_section_controls();
		$this->register_style_section_controls();
	}

	public function post_render($post_id)
{
  if (empty($post_id)) {
    echo '<h3 class="bt-not-found">' . __('Please, Add car to compare!', 'autoart') . '</h3>';
    return;
  }

  $price = get_field('car_price', $post_id);

  // Badge taxonomy
  $condition_terms = get_the_terms($post_id, 'car_condition');

  // Meta fields
  $year        = get_field('car_year', $post_id);      // ACF
  $berth_terms = get_the_terms($post_id, 'car_berth'); // taxonomy
  ?>
  <div class="bt-post">
    <div class="bt-post--featured">
      <a href="<?php echo esc_url(get_permalink($post_id)); ?>">
        <div class="bt-cover-image">
          <?php echo get_the_post_thumbnail($post_id, 'large'); ?>
        </div>
      </a>
    </div>

    <?php if (!empty($condition_terms) && !is_wp_error($condition_terms)) : ?>
      <div class="bt-post--body">
        <?php
          $term = array_pop($condition_terms);
          echo '<span class="bt-value">' . esc_html($term->name) . '</span>';
        ?>
      </div>
    <?php endif; ?>

    <h3 class="bt-post--title">
      <a href="<?php echo esc_url(get_permalink($post_id)); ?>">
        <?php echo esc_html(get_the_title($post_id)); ?>
      </a>
    </h3>

    <div class="bt-post--price">
      <?php
        if (!empty($price)) {
          echo '$' . number_format((float)$price, 0);
        } else {
          echo '<a href="#">' . esc_html__('Call for price', 'autoart') . '</a>';
        }
      ?>
    </div>

    <div class="bt-post--meta">
      <div class="bt-post--meta-row">

        <!-- Berth -->
        <div class="bt-post--meta-col">
          <div class="bt-post--meta-item bt-post--fuel-type">
            <?php
              $svg_file = get_template_directory() . '/assets/images/icon-user.svg';
              if (file_exists($svg_file)) {
                $svg = file_get_contents($svg_file);

                // ensure consistent sizing + theme color behavior
                $svg = preg_replace(
                  '/<svg\b([^>]*)>/',
                  '<svg$1 width="26" height="26" viewBox="0 0 26 26" fill="none" aria-hidden="true" focusable="false">',
                  $svg,
                  1
                );

                echo $svg; // safe if you trust this local file
              }
            ?>

            <?php
              echo '<span class="bt-label">' . esc_html__('Berth', 'autoart') . '</span>';

              if (!empty($berth_terms) && !is_wp_error($berth_terms)) {
                $term = array_pop($berth_terms);
                echo '<span class="bt-value">' . esc_html($term->name) . '</span>';
              } else {
                echo '<span class="bt-value">' . esc_html__('N/A', 'autoart') . '</span>';
              }
            ?>
          </div>
        </div>

        <!-- Year -->
        <div class="bt-post--meta-col">
          <div class="bt-post--meta-item bt-post--mileage">
            <?php
              $svg_file = get_template_directory() . '/assets/images/icon-calendar.svg';
              if (file_exists($svg_file)) {
                $svg = file_get_contents($svg_file);
                $svg = preg_replace(
                  '/<svg\b([^>]*)>/',
                  '<svg$1 width="26" height="26" viewBox="0 0 26 26" fill="none" aria-hidden="true" focusable="false">',
                  $svg,
                  1
                );
                echo $svg;
              }
            ?>

            <?php
              echo '<span class="bt-label">' . esc_html__('Year', 'autoart') . '</span>';

              if (!empty($year)) {
                echo '<span class="bt-value">' . esc_html($year) . '</span>';
              } else {
                echo '<span class="bt-value">' . esc_html__('N/A', 'autoart') . '</span>';
              }
            ?>
          </div>
        </div>

      </div>
    </div>
  </div>
  <?php
}

	public function post_social_share()
	{

		$social_item = array();
		$social_item[] = '<li>
                        <a target="_blank" data-btIcon="fa fa-facebook" data-toggle="tooltip" title="' . esc_attr__('Facebook', 'autoart') . '" href="https://www.facebook.com/sharer/sharer.php?u=' . get_the_permalink() . '">
                          <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 320 512">
                            <path d="M279.14 288l14.22-92.66h-88.91v-60.13c0-25.35 12.42-50.06 52.24-50.06h40.42V6.26S260.43 0 225.36 0c-73.22 0-121.08 44.38-121.08 124.72v70.62H22.89V288h81.39v224h100.17V288z"/>
                          </svg>
                        </a>
                      </li>';
		$social_item[] = '<li>
                        <a target="_blank" data-btIcon="fa fa-twitter" data-toggle="tooltip" title="' . esc_attr__('Twitter', 'autoart') . '" href="https://twitter.com/share?url=' . get_the_permalink() . '">
                          <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 512 512">
                            <path d="M389.2 48h70.6L305.6 224.2 487 464H345L233.7 318.6 106.5 464H35.8L200.7 275.5 26.8 48H172.4L272.9 180.9 389.2 48zM364.4 421.8h39.1L151.1 88h-42L364.4 421.8z"/>
                          </svg>
                        </a>
                      </li>';
		$social_item[] = '<li>
                        <a target="_blank" data-btIcon="fa fa-google-plus" data-toggle="tooltip" title="' . esc_attr__('Google Plus', 'autoart') . '" href="https://plus.google.com/share?url=' . get_the_permalink() . '">
                          <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 488 512">
                            <path d="M488 261.8C488 403.3 391.1 504 248 504 110.8 504 0 393.2 0 256S110.8 8 248 8c66.8 0 123 24.5 166.3 64.9l-67.5 64.9C258.5 52.6 94.3 116.6 94.3 256c0 86.5 69.1 156.6 153.7 156.6 98.2 0 135-70.4 140.8-106.9H248v-85.3h236.1c2.3 12.7 3.9 24.9 3.9 41.4z"/>
                          </svg>
                        </a>
                      </li>';
		$social_item[] = '<li>
                        <a target="_blank" data-btIcon="fa fa-linkedin" data-toggle="tooltip" title="' . esc_attr__('Linkedin', 'autoart') . '" href="https://www.linkedin.com/shareArticle?url=' . get_the_permalink() . '">
                          <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 448 512">
                            <path d="M100.28 448H7.4V148.9h92.88zM53.79 108.1C24.09 108.1 0 83.5 0 53.8a53.79 53.79 0 0 1 107.58 0c0 29.7-24.1 54.3-53.79 54.3zM447.9 448h-92.68V302.4c0-34.7-.7-79.2-48.29-79.2-48.29 0-55.69 37.7-55.69 76.7V448h-92.78V148.9h89.08v40.8h1.3c12.4-23.5 42.69-48.3 87.88-48.3 94 0 111.28 61.9 111.28 142.3V448z"/>
                          </svg>
                        </a>
                      </li>';
		$social_item[] = '<li>
                        <a target="_blank" data-btIcon="fa fa-pinterest" data-toggle="tooltip" title="' . esc_attr__('Pinterest', 'autoart') . '" href="https://pinterest.com/pin/create/button/?url=' . get_the_permalink() . '">
                          <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 496 512">
                            <path d="M496 256c0 137-111 248-248 248-25.6 0-50.2-3.9-73.4-11.1 10.1-16.5 25.2-43.5 30.8-65 3-11.6 15.4-59 15.4-59 8.1 15.4 31.7 28.5 56.8 28.5 74.8 0 128.7-68.8 128.7-154.3 0-81.9-66.9-143.2-152.9-143.2-107 0-163.9 71.8-163.9 150.1 0 36.4 19.4 81.7 50.3 96.1 4.7 2.2 7.2 1.2 8.3-3.3.8-3.4 5-20.3 6.9-28.1.6-2.5.3-4.7-1.7-7.1-10.1-12.5-18.3-35.3-18.3-56.6 0-54.7 41.4-107.6 112-107.6 60.9 0 103.6 41.5 103.6 100.9 0 67.1-33.9 113.6-78 113.6-24.3 0-42.6-20.1-36.7-44.8 7-29.5 20.5-61.3 20.5-82.6 0-19-10.2-34.9-31.4-34.9-24.9 0-44.9 25.7-44.9 60.2 0 22 7.4 36.8 7.4 36.8s-24.5 103.8-29 123.2c-5 21.4-3 51.6-.9 71.2C65.4 450.9 0 361.1 0 256 0 119 111 8 248 8s248 111 248 248z"/>
                          </svg>
                        </a>
                      </li>';

		ob_start();
	?>
		<div class="bt-post-share">
			<?php
			if (!empty($social_item)) {
				echo '<span>' . esc_html__('Share: ', 'autoart') . '</span><ul>' . implode(' ', $social_item) . '</ul>';
			}
			?>
		</div>
	<?php
		return ob_get_clean();
	}

	public function popup_render($car_ids)
	{
		if (empty($car_ids)) {
			echo '<h3 class="bt-not-found">' . __('Please, Add cars to compare!', 'autoart') . '</h3>';

			return;
		}

		$ex_items = count($car_ids) < 2 ? 2 - count($car_ids) : 0;

	?>
		<div class="bt-cars-quick-compare--popup">
			<a class="bt-close-popup" href="#">
				<svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" width="512" height="512" x="0" y="0" viewBox="0 0 24 24" fill="currentColor">
					<path d="M5 17.586a1 1 0 1 0 1.415 1.415L12 13.414 17.586 19A1 1 0 0 0 19 17.586L13.414 12 19 6.414A1 1 0 0 0 17.585 5L12 10.586 6.414 5A1 1 0 0 0 5 6.414L10.586 12z"></path>
				</svg>
			</a>

			<div class="bt-cars-quick-compare--wrap">
			<div class="bt-table">
					<div class="bt-table--head">
						<div class="bt-table--row">
							<div class="bt-table--col bt-car-information">
								<?php echo '<span>' . __('Information', 'autoart') . '</span>'; ?>
							</div>
							<div class="bt-table--col bt-car-body">
								<?php echo '<span class="bt-label">' . __('Body', 'autoart') . '</span>'; ?>
							</div>
							<div class="bt-table--col bt-car-berth">
                              <?php echo '<span class="bt-label">' . __('Berth', 'autoart') . '</span>'; ?>
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
						<div class="bt-car-list">
							<?php foreach ($car_ids as $key => $id) { ?>
								<div class="bt-table--row bt-car-item">
									<div class="bt-table--col bt-car-information">
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
									<div class="bt-table--col bt-car-berth">
                                      <?php
                                        $berth_terms = get_the_terms( $id, 'car_berth' );
                                    
                                        if ( !empty($berth_terms) && !is_wp_error($berth_terms) ) {
                                          $term = array_pop($berth_terms);
                                          echo '<span class="bt-value">' . esc_html($term->name) . '</span>';
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
												<div class="bt-table--col bt-car-berth"></div>
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
			</div>
		</div>
	<?php
	}

	protected function render()
	{
		$settings = $this->get_settings_for_display();

		$post_ids = array();

		if (!empty($settings['first_car'])) {
			$post_ids[] = $settings['first_car'];
		}

		if (!empty($settings['second_car'])) {
			$post_ids[] = $settings['second_car'];
		}

	?>
		<div class="bt-elwg-cars-quick-compare--default">
			<div class="bt-cars-quick-compare">
				<div class="bt-cars-quick-compare--inner">
					<div class="bt-cars">
						<div class="bt-cars--item">
							<?php $this->post_render($settings['first_car']); ?>
						</div>

						<div class="bt-cars--divider">
							<?php echo '<span>' . __('vs', 'autoart') . '</span>'; ?>
						</div>

						<div class="bt-cars--item">
							<?php $this->post_render($settings['second_car']); ?>
						</div>
					</div>
					<div class="bt-cars--compare">
						<a class="bt-quick-compare-btn" href="#">
							<span><?php echo __('Compare Now', 'autoart'); ?></span>
							<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M4 12H20M20 12L16 8M20 12L16 16" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
							</svg>
						</a>
					</div>
				</div>

				<?php $this->popup_render($post_ids); ?>
			</div>
		</div>
<?php
	}

	protected function content_template()
	{
	}
}