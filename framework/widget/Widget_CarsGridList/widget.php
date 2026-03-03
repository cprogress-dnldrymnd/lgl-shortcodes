<?php

namespace AutoArtElementorWidgets\Widgets\CarsGridList;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Typography;

class Widget_CarsGridList extends Widget_Base
{

	public function get_name()
	{
		return 'bt-cars-grid-list';
	}

	public function get_title()
	{
		return __('Cars Grid List', 'autoart');
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
				'label' => __('Columns (Grid)', 'autoart'),
				'type' => Controls_Manager::SELECT,
				'default' => '2',
				'options' => [
					'2' => '2',
					'3' => '3',
					'4' => '4',
				],
			]
		);
		$this->add_control(
			'layout',
			[
				'label'   => esc_html__('Layout', 'autoart'),
				'type'    => Controls_Manager::SELECT,
				'default' => 'grid',
				'options' => [
					'grid'  => __('Grid', 'autoart'),
					'list' => __('List', 'autoart'),
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

		<div class="bt-elwg-cars-grid-list--default">
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
							<input type="hidden" class="bt-car-view-type" name="view_type" value="<?php if (isset($_GET['view_type'])) echo esc_attr($_GET['view_type']); ?>">
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
							<div class="bt-car-view-block">
								<?php
								$type_active = $settings['layout'];
								if (isset($_GET['view_type']) && 'list' == $_GET['view_type']) {
									$type_active = 'list';
								}
								?>
								<a href="#" class="bt-view-type bt-view-grid <?php if ('grid' == $type_active) echo 'active'; ?>" data-view="grid">
									<svg width="28" height="28" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
										<path d="M16.334 6.53333C16.334 5.87994 16.334 5.55324 16.4612 5.30368C16.573 5.08416 16.7514 4.90568 16.971 4.79382C17.2205 4.66667 17.5472 4.66667 18.2007 4.66667H21.4673C22.1208 4.66667 22.4474 4.66667 22.697 4.79382C22.9166 4.90568 23.0949 5.08416 23.2068 5.30368C23.334 5.55324 23.334 5.87994 23.334 6.53333V9.8C23.334 10.4534 23.334 10.7801 23.2068 11.0297C23.0949 11.2492 22.9166 11.4277 22.697 11.5395C22.4474 11.6667 22.1208 11.6667 21.4673 11.6667H18.2007C17.5472 11.6667 17.2205 11.6667 16.971 11.5395C16.7514 11.4277 16.573 11.2492 16.4612 11.0297C16.334 10.7801 16.334 10.4534 16.334 9.8V6.53333Z" stroke="#555555" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
										<path d="M4.66602 6.53333C4.66602 5.87994 4.66602 5.55324 4.79317 5.30368C4.90503 5.08416 5.08351 4.90568 5.30303 4.79382C5.55259 4.66667 5.87929 4.66667 6.53268 4.66667H9.79935C10.4527 4.66667 10.7794 4.66667 11.029 4.79382C11.2485 4.90568 11.427 5.08416 11.5389 5.30368C11.666 5.55324 11.666 5.87994 11.666 6.53333V9.8C11.666 10.4534 11.666 10.7801 11.5389 11.0297C11.427 11.2492 11.2485 11.4277 11.029 11.5395C10.7794 11.6667 10.4527 11.6667 9.79935 11.6667H6.53268C5.87929 11.6667 5.55259 11.6667 5.30303 11.5395C5.08351 11.4277 4.90503 11.2492 4.79317 11.0297C4.66602 10.7801 4.66602 10.4534 4.66602 9.8V6.53333Z" stroke="#555555" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
										<path d="M4.66602 18.2C4.66602 17.5465 4.66602 17.2199 4.79317 16.9703C4.90503 16.7508 5.08351 16.5724 5.30303 16.4605C5.55259 16.3333 5.87929 16.3333 6.53268 16.3333H9.79935C10.4527 16.3333 10.7794 16.3333 11.029 16.4605C11.2485 16.5724 11.427 16.7508 11.5389 16.9703C11.666 17.2199 11.666 17.5465 11.666 18.2V21.4667C11.666 22.1201 11.666 22.4468 11.5389 22.6963C11.427 22.9159 11.2485 23.0943 11.029 23.2062C10.7794 23.3333 10.4527 23.3333 9.79935 23.3333H6.53268C5.87929 23.3333 5.55259 23.3333 5.30303 23.2062C5.08351 23.0943 4.90503 22.9159 4.79317 22.6963C4.66602 22.4468 4.66602 22.1201 4.66602 21.4667V18.2Z" stroke="#555555" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
										<path d="M16.334 18.2C16.334 17.5465 16.334 17.2199 16.4612 16.9703C16.573 16.7508 16.7514 16.5724 16.971 16.4605C17.2205 16.3333 17.5472 16.3333 18.2007 16.3333H21.4673C22.1208 16.3333 22.4474 16.3333 22.697 16.4605C22.9166 16.5724 23.0949 16.7508 23.2068 16.9703C23.334 17.2199 23.334 17.5465 23.334 18.2V21.4667C23.334 22.1201 23.334 22.4468 23.2068 22.6963C23.0949 22.9159 22.9166 23.0943 22.697 23.2062C22.4474 23.3333 22.1208 23.3333 21.4673 23.3333H18.2007C17.5472 23.3333 17.2205 23.3333 16.971 23.2062C16.7514 23.0943 16.573 22.9159 16.4612 22.6963C16.334 22.4468 16.334 22.1201 16.334 21.4667V18.2Z" stroke="#555555" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
									</svg>
								</a>
								<a href="#" class="bt-view-type bt-view-list <?php if ('list' == $type_active) echo 'active'; ?>" data-view="list">
									<svg width="28" height="28" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
										<path d="M9.33333 7L24.5 7.00091M9.33333 14L24.5 14.0009M9.33333 21L24.5 21.0008M3.5 7.58333H4.66667V6.41667H3.5V7.58333ZM3.5 14.5833H4.66667V13.4167H3.5V14.5833ZM3.5 21.5833H4.66667V20.4167H3.5V21.5833Z" stroke="#555555" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
									</svg>
								</a>
							</div>
						</form>
					</div>
				</div>
				<div class="bt-filter-results">
					<span class="bt-loading-wave"></span>
					<?php
					if ($wp_query->have_posts()) {
					?>
						<div class="bt-car-grid-layout bt-layout-default <?php echo 'bt-cols--' . esc_attr($settings['columns']); ?>" data-view="<?php echo isset($_GET['view_type']) && $_GET['view_type'] != '' ? $_GET['view_type'] : $settings['layout'] ?>" data-limit="<?php echo esc_attr($settings['posts_per_page']); ?>">
							<?php
							while ($wp_query->have_posts()) {
								$wp_query->the_post();
								get_template_part('framework/templates/car', 'style', array('image-size' => $settings['thumbnail_size']));
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
