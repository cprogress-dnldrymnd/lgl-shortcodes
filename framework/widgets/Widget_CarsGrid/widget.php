<?php

namespace AutoArtElementorWidgets\Widgets\CarsGrid;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Typography;


class Widget_CarsGrid extends Widget_Base
{

	public function get_name()
	{
		return 'bt-cars-grid';
	}

	public function get_title()
	{
		return __('Cars Grid', 'autoart');
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

	public function get_supported_taxonomies()
	{
		$supported_taxonomies = [];

		$categories = get_terms(array(
			'taxonomy' => 'car_categories',
			'hide_empty' => false,
		));
		if (!empty($categories)  && !is_wp_error($categories)) {
			foreach ($categories as $category) {
				$supported_taxonomies[$category->term_id] = $category->name;
			}
		}

		return $supported_taxonomies;
	}

	protected function register_layout_section_controls()
	{
		$this->start_controls_section(
			'section_layout',
			[
				'label' => esc_html__('Layout', 'autoart'),
			]
		);
		$this->add_control(
			'layout_style',
			[
				'label' => __('Layout Style', 'autoart'),
				'type' => Controls_Manager::SELECT,
				'default' => 'default',
				'options' => [
					'default' => 'Default',
					'style1' => 'Style 1',
					'style2' => 'Style 2',
				],
			]
		);
		$this->add_control(
			'posts_per_page',
			[
				'label' => esc_html__('Posts Per Page', 'autoart'),
				'type' => Controls_Manager::NUMBER,
				'default' => 6,
			]
		);

		$this->add_responsive_control(
			'gap',
			[
				'label'   => esc_html__('Gap', 'autoart'),
				'type'    => Controls_Manager::SLIDER,
				'default' => [
					'size' => 20,
				],
				'range' => [
					'px' => [
						'min' => 1,
						'max' => 100,
						'step' => 5,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .bt-car-grid-layout' => 'gap:{{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Image_Size::get_type(),
			[
				'name'       => 'thumbnail',
				'label'      => esc_html__('Image Size', 'autoart'),
				'show_label' => true,
				'default'    => 'medium',
				'exclude'    => ['custom'],
			]
		);
		$this->add_control(
			'show_pagination',
			[
				'label'     => esc_html__('Pagination', 'autoart'),
				'type'      => Controls_Manager::SWITCHER,
				'label_on'  => __('Show', 'autoart'),
				'label_off' => __('Hide', 'autoart'),
				'default'   => '',
			]
		);
		$this->add_control(
			'columns',
			[
				'label' => __('Columns', 'autoart'),
				'type' => Controls_Manager::SELECT,
				'default' => '3',
				'options' => [
					'2' => '2',
					'3' => '3',
					'4' => '4',
				],
			]
		);
		$this->end_controls_section();
	}

	protected function register_query_section_controls()
	{
		$this->start_controls_section(
			'section_query',
			[
				'label' => esc_html__('Query', 'autoart'),
			]
		);

		$this->start_controls_tabs('tabs_query');

		$this->start_controls_tab(
			'tab_query_include',
			[
				'label' => esc_html__('Include', 'autoart'),
			]
		);

		$this->add_control(
			'category',
			[
				'label'       => esc_html__('Category', 'autoart'),
				'type'        => Controls_Manager::SELECT2,
				'options'     => $this->get_supported_taxonomies(),
				'label_block' => true,
				'multiple'    => true,
			]
		);

		$this->end_controls_tab();


		$this->start_controls_tab(
			'tab_query_exnlude',
			[
				'label' => esc_html__('Exclude', 'autoart'),
			]
		);

		$this->add_control(
			'category_exclude',
			[
				'label'       => esc_html__('Category', 'autoart'),
				'type'        => Controls_Manager::SELECT2,
				'options'     => $this->get_supported_taxonomies(),
				'label_block' => true,
				'multiple'    => true,
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_control(
			'orderby',
			[
				'label'   => esc_html__('Order By', 'autoart'),
				'type'    => Controls_Manager::SELECT,
				'default' => 'date',
				'options' => [
					'date'  => __('Date', 'autoart'),
					'title' => __('Title', 'autoart'),
					'menu_order' => __('Menu Order', 'autoart'),
					'rand'       => __('Random', 'autoart'),
				],
			]
		);

		$this->add_control(
			'order',
			[
				'label'   => esc_html__('Order', 'autoart'),
				'type'    => Controls_Manager::SELECT,
				'default' => 'desc',
				'options' => [
					'asc'  => __('ASC', 'autoart'),
					'desc' => __('DESC', 'autoart'),
				],
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

		$this->add_control(
			'title_style',
			[
				'label' => __('Title', 'autoart'),
				'type' => Controls_Manager::HEADING,
			]
		);

		$this->add_control(
			'title_color',
			[
				'label' => __('Color', 'autoart'),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .bt-car-grid-layout .bt-post .bt-post--title' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'title_color_hover',
			[
				'label' => __('Color Hover', 'autoart'),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .bt-car-grid-layout .bt-post .bt-post--title a:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'title_typography',
				'label' => __('Typography', 'autoart'),
				'default' => '',
				'selector' => '{{WRAPPER}}  .bt-car-grid-layout .bt-post .bt-post--title',
			]
		);

		$this->add_control(
			'price_style',
			[
				'label' => __('Price', 'autoart'),
				'type' => Controls_Manager::HEADING,
			]
		);

		$this->add_control(
			'price_color',
			[
				'label' => __('Color', 'autoart'),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .bt-car-grid-layout .bt-post .bt-post--price' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'price_bg_color',
			[
				'label' => __('Background Color', 'autoart'),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .bt-car-grid-layout .bt-post .bt-post--price' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'price_typography',
				'label' => __('Typography', 'autoart'),
				'default' => '',
				'selector' => '{{WRAPPER}} .bt-car-grid-layout .bt-post .bt-post--price',
			]
		);

		$this->add_control(
			'body_style',
			[
				'label' => __('Body', 'autoart'),
				'type' => Controls_Manager::HEADING,
			]
		);

		$this->add_control(
			'body_color',
			[
				'label' => __('Color', 'autoart'),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .bt-car-grid-layout .bt-post .bt-post--body' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'body_bg_color',
			[
				'label' => __('Background Color', 'autoart'),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .bt-car-grid-layout .bt-post .bt-post--body-price' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'body_typography',
				'label' => __('Typography', 'autoart'),
				'default' => '',
				'selector' => '{{WRAPPER}} .bt-car-grid-layout .bt-post .bt-post--body',
			]
		);

		$this->add_control(
			'meta_style',
			[
				'label' => __('Meta', 'autoart'),
				'type' => Controls_Manager::HEADING,
			]
		);

		$this->add_control(
			'meta_icon_color',
			[
				'label' => __('Icon Color', 'autoart'),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .bt-car-grid-layout .bt-post .bt-post--meta-item svg' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'meta_label_color',
			[
				'label' => __('Label Color', 'autoart'),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .bt-car-grid-layout .bt-post .bt-post--meta-item .bt-label' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'meta_label_typography',
				'label' => __('Label Typography', 'autoart'),
				'default' => '',
				'selector' => '{{WRAPPER}} .bt-car-grid-layout .bt-post .bt-post--meta-item .bt-label',
			]
		);

		$this->add_control(
			'meta_value_color',
			[
				'label' => __('Value Color', 'autoart'),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .bt-car-grid-layout .bt-post .bt-post--meta-item .bt-value' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'meta_value_typography',
				'label' => __('Value Typography', 'autoart'),
				'default' => '',
				'selector' => '{{WRAPPER}} .bt-car-grid-layout .bt-post .bt-post--meta-item .bt-value',
			]
		);
		$this->add_control(
			'readmore_style',
			[
				'label' => __('Read More', 'autoart'),
				'type' => Controls_Manager::HEADING,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'readmore_typography',
				'label' => __('Typography', 'autoart'),
				'default' => '',
				'selector' => '{{WRAPPER}} .bt-car-grid-layout .bt-post .bt-post--readmore a',
			]
		);

		$this->start_controls_tabs('readmore_effects_tabs');

		$this->start_controls_tab(
			'readmore_tab_normal',
			[
				'label' => __('Normal', 'autoart'),
			]
		);

		$this->add_control(
			'readmore_color',
			[
				'label' => __('Color', 'autoart'),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .bt-car-grid-layout .bt-post .bt-post--readmore a' => 'color: {{VALUE}};',
					'{{WRAPPER}} .bt-car-grid-layout .bt-post .bt-post--readmore a svg path' => 'stroke: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'readmore_bg_color',
			[
				'label' => __('Background Color', 'autoart'),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .bt-car-grid-layout .bt-post .bt-post--readmore a' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'readmore_tab_hover',
			[
				'label' => __('Hover', 'autoart'),
			]
		);

		$this->add_control(
			'readmore_color_hover',
			[
				'label' => __('Color', 'autoart'),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .bt-car-grid-layout .bt-post .bt-post--readmore a:hover' => 'color: {{VALUE}};',
					'{{WRAPPER}} .bt-car-grid-layout .bt-post .bt-post--readmore a:hover svg path' => 'stroke: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'readmore_bg_color_hover',
			[
				'label' => __('Background Color', 'autoart'),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .bt-car-grid-layout .bt-post .bt-post--readmore a:hover' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();
		$this->end_controls_section();
	}
	protected function register_controls()
	{

		$this->register_layout_section_controls();
		$this->register_query_section_controls();
		$this->register_style_section_controls();
	}

	protected function render()
	{
		$settings = $this->get_settings_for_display();
		$query_args = autoart_cars_grid_list_query_args($_GET, $settings['orderby'], $settings['order'], $settings['posts_per_page'], $settings['category'], $settings['category_exclude']);
		$wp_query = new \WP_Query($query_args);
		$current_page = isset($_GET['current_page']) && $_GET['current_page'] != '' ? $_GET['current_page'] : 1;
		$total_page = $wp_query->max_num_pages;
		$paged = !empty($wp_query->query_vars['paged']) ? $wp_query->query_vars['paged'] : 1;
		$prev_posts = ($paged - 1) * $wp_query->query_vars['posts_per_page'];
		$from = 1 + $prev_posts;
		$to = count($wp_query->posts) + $prev_posts;
		$of = $wp_query->found_posts;
?>

		<div class="bt-elwg-cars-grid--default">
			<div class="bt-cars-grid-list-template">
				<div class="bt-filter-scroll-pos"></div>
				<div class="bt-car-topbar">
					<div class="bt-car-col-left">
						<div class="bt-car-results-block">
							<?php
							if ($of > 0) {
								printf(esc_html__('Showing %s - %s of %s results', 'autoart'), $from, $to, $of);
							} else {
								echo esc_html__('No results', 'autoart');
							}
							?>
						</div>
					</div>

					<div class="bt-car-col-right">
						<form class="bt-car-filter-form-sortview" action="" method="get">
							<input type="hidden" name="orderby" value="<?php echo isset($settings['orderby']) ? esc_attr($settings['orderby']) : ''; ?>">
							<input type="hidden" name="order" value="<?php echo isset($settings['order']) ? esc_attr($settings['order']) : ''; ?>">
							<input type="hidden" name="posts_per_page" value="<?php echo isset($settings['posts_per_page']) ? esc_attr($settings['posts_per_page']) : 6; ?>">
							<input type="hidden" name="category" value="<?php echo !empty($settings['category']) ? esc_attr(implode(',', $settings['category'])) : ''; ?>">
							<input type="hidden" name="category_exclude" value="<?php echo !empty($settings['category_exclude']) ? esc_attr(implode(',', $settings['category_exclude'])) : ''; ?>">
							<input type="hidden" name="thumbnail_size" value="<?php echo isset($settings['thumbnail_size']) ? esc_attr($settings['thumbnail_size']) : ''; ?>">
							<input type="hidden" name="layout_style" value="<?php echo isset($settings['layout_style']) ? esc_attr($settings['layout_style']) : 'default'; ?>">
							<input type="hidden" class="bt-car-current-page" name="current_page" value="<?php if (isset($_GET['current_page'])) echo esc_attr($_GET['current_page']); ?>">
							<div class="bt-car-sort-block">
								<span class="bt-sort-title">
									<?php echo esc_html__('Sort by:', 'autoart'); ?>
								</span>
								<div class="bt-sort-field">
									<?php
									$sort_options = array(
										'date_high' => esc_html__('Date: newest first', 'autoart'),
										'date_low' => esc_html__('Date: oldest first', 'autoart'),
										'mileage_high' => esc_html__('Mileage: highest first', 'autoart'),
										'mileage_low' => esc_html__('Mileage: lowest first', 'autoart'),
										'price_high' => esc_html__('Price: highest first', 'autoart'),
										'price_low' => esc_html__('Price: lower first', 'autoart')
									);
									?>
									<select name="sort_order">
										<?php foreach ($sort_options as $key => $value) { ?>
											<?php if (isset($_GET['sort_order']) && $key == $_GET['sort_order']) { ?>
												<?php if ($key == $_GET['sort_order']) { ?>
													<option value="<?php echo esc_attr($key); ?>" selected="selected">
														<?php echo esc_html($value); ?>
													</option>
												<?php } else { ?>
													<option value="<?php echo esc_attr($key); ?>">
														<?php echo esc_html($value); ?>
													</option>
												<?php } ?>
											<?php } else { ?>
												<?php if ($key == 'date_high') { ?>
													<option value="<?php echo esc_attr($key); ?>" selected="selected">
														<?php echo esc_html($value); ?>
													</option>
												<?php } else { ?>
													<option value="<?php echo esc_attr($key); ?>">
														<?php echo esc_html($value); ?>
													</option>
												<?php } ?>
											<?php } ?>
										<?php } ?>
									</select>
								</div>
							</div>
						</form>
					</div>
				</div>
				<div class="bt-filter-results">
					<span class="bt-loading-wave"></span>
					<?php
					if ($wp_query->have_posts()) {
					?>
						<div class="bt-car-grid-layout <?php echo 'bt-cols--' . esc_attr($settings['columns']) . ' bt-layout-' . esc_attr($settings['layout_style']); ?> " data-limit="<?php echo esc_attr($settings['posts_per_page']); ?>">
							<?php
							while ($wp_query->have_posts()) {
								$wp_query->the_post();
								if ($settings['layout_style'] == 'default') {
									get_template_part('framework/templates/car', 'style', array('image-size' => $settings['thumbnail_size']));
								} elseif ($settings['layout_style'] == 'style1') {
									get_template_part('framework/templates/car', 'style1', array('image-size' => $settings['thumbnail_size']));
								} else {
									get_template_part('framework/templates/car', 'style2', array('image-size' => $settings['thumbnail_size']));
								}
							}
							?>
						</div>
						<?php if ($settings['show_pagination'] == 'yes') { ?>
							<div class="bt-car-pagination-wrap">
								<?php echo autoart_cars_pagination($current_page, $total_page);
								?>
							</div>
						<?php } ?>
					<?php
					} else {
						echo '<h3 class="not-found-post">' . esc_html__('Sorry, No results', 'autoart') . '</h3>';
					}

					wp_reset_postdata();
					?>
				</div>
			</div>
		</div>
<?php
		wp_reset_postdata();
	}

	protected function content_template()
	{
	}
}
