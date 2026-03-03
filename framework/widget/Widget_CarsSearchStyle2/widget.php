<?php
namespace AutoArtElementorWidgets\Widgets\CarsSearchStyle2;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;
use Elementor\Utils;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Css_Filter;

class Widget_CarsSearchStyle2 extends Widget_Base {

	public function get_name() {
		return 'bt-cars-search-style-2';
	}

	public function get_title() {
		return __( 'Cars Search Style 2', 'autoart' );
	}

	public function get_icon() {
		return 'eicon-posts-ticker';
	}

	public function get_categories() {
		return [ 'autoart' ];
	}

	public function get_script_depends() {
		return ['select2-min', 'elementor-widgets' ];
	}
	
	protected function register_content_section_controls() {
		$this->start_controls_section(
			'ss_cars_search_content',[
				'label' => __( 'Content', 'autoart' ),
			]
		);

			$this->add_control(
				'top_search_heading',[
					'label' => __( 'Top Search', 'autoart' ),
					'type' => Controls_Manager::HEADING,
				]
			);

			$this->add_control(
				'top_search_title', [
					'label'       => __( 'Title', 'autoart' ),
					'type'        => Controls_Manager::TEXT,
					'label_block' => true,
					'default'     => 'Top Seach:',
				]
			);

			$repeater = new Repeater();

			$repeater->add_control(
				'top_search_text', [
					'label'       => esc_html__( 'Text', 'autoart' ),
					'type'        => Controls_Manager::TEXT,
					'label_block' => true,
					'default'     => 'This is text',
				]
			);

			$repeater->add_control(
				'top_search_link', [
					'label' => esc_html__( 'Link', 'autoart' ),
					'type' => Controls_Manager::TEXT,
					'label_block' => true,
					'default' => '',
				]
			);

			$this->add_control(
				'cars_top_search',[
					'label' => esc_html__( 'List Items', 'autoart' ),
					'type' => Controls_Manager::REPEATER,
					'fields' => $repeater->get_controls(),
					'default' => [
						[
							'top_search_text'  => __( 'This is text 01', 'autoart' ),
							'top_search_link'  => '#'
						],
						[
							'top_search_text'  => __( 'This is text 02', 'autoart' ),
							'top_search_link'  => '#'
						],
						[
							'top_search_text'  => __( 'This is text 03', 'autoart' ),
							'top_search_link'  => '#'
						],
					],
					'title_field' => '{{{ top_search_text }}}',
				]
			);

		$this->end_controls_section();
	}

	protected function register_style_content_section_controls() {
		$this->start_controls_section(
			'cars_search_ss_general',[
				'label' => esc_html__( 'General', 'autoart' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

			$this->add_group_control(
				Group_Control_Typography::get_type(),[
					'name'     => 'cars_search_general_typography',
					'label'    => esc_html__( 'Typography', 'autoart' ),
					'default'  => '',
					'selector' => '{{WRAPPER}} .bt-field-car_condition .item-radio label',
				]
			);

			$this->add_control(
				'cars_search_general_color',[
					'label'     => esc_html__( 'Color', 'autoart' ),
					'type'      => Controls_Manager::COLOR,
					'default'   => '',
					'selectors' => [
						'{{WRAPPER}} .bt-field-car_condition .item-radio label' => 'color: {{VALUE}};',
					],
				]
			);

			$this->add_control(
				'cars_search_general_bg',[
					'label'     => esc_html__( 'Background Color', 'autoart' ),
					'type'      => Controls_Manager::COLOR,
					'default'   => '',
					'selectors' => [
						'{{WRAPPER}} .bt-elwg-cars-search-inner' => 'background-color: {{VALUE}};',
						'{{WRAPPER}} .bt-field-car_condition .item-radio:has(input:checked)' => 'background-color: {{VALUE}};',
						'{{WRAPPER}} .bt-field-car_condition .item-radio:hover' => 'background-color: {{VALUE}};',
					],
				]
			);

			$this->add_responsive_control(
				'cars_search_general_pd',[
					'label' => esc_html__( 'Padding', 'autoart' ),
					'type' => Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', '%' ],
					'range' => [
						'px' => [
							'min' => 0,
							'max' => 100,
						],
					],
					'default' => [
						'top'    => 30,
						'right'  => 30,
						'bottom' => 27,
						'left'   => 29,
						'unit'   => 'px',
					],
					'selectors' => [
						'{{WRAPPER}} .bt-elwg-cars-search-inner' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
					],
				]
			);

			$this->add_responsive_control(
				'cars_search_general_border_radius',[
					'label'      => esc_html__( 'Border Radius', 'autoart' ),
					'type'       => Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', '%' ],
					'range' => [
						'px' => [
							'min' => 0,
							'max' => 100,
						],
					],
					'default' => [
						'top'    => 20,
						'right'  => 20,
						'bottom' => 20,
						'left'   => 20,
						'unit'   => 'px',
					],
					'selectors' => [
						'{{WRAPPER}} .bt-elwg-cars-search-inner' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
						'{{WRAPPER}} .bt-elwg-cars-search--form' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Box_Shadow::get_type(),[
					'name'     => 'cars_search_general_box_shadow',
					'selector' => '{{WRAPPER}} .bt-elwg-cars-search-inner',
				]
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'cars_search_form_fields_style',[
				'label' => esc_html__( 'Form Fields', 'autoart' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

			$this->add_group_control(
				Group_Control_Typography::get_type(),[
					'name'     => 'cars_search_form_fields_typography',
					'label'    => esc_html__( 'Typography', 'autoart' ),
					'default'  => '',
					'selector' => '{{WRAPPER}} .select2-container .select2-selection__rendered',
				]
			);

			$this->add_control(
				'cars_search_form_fields_color',[
					'label'     => esc_html__( 'Color', 'autoart' ),
					'type'      => Controls_Manager::COLOR,
					'default'   => '',
					'selectors' => [
						'{{WRAPPER}} .select2-container .select2-selection__rendered' => 'color: {{VALUE}};',
					],
				]
			);

			$this->add_control(
				'cars_search_form_fields_btn_submit_heading',[
					'label' => esc_html__( 'Button Submit', 'autoart' ),
					'type'  => Controls_Manager::HEADING,
				]
			);

			$this->add_group_control(
				Group_Control_Typography::get_type(),[
					'name'     => 'cars_search_form_fields_btn_submit_typography',
					'label'    => esc_html__( 'Typography', 'autoart' ),
					'default'  => '',
					'selector' => '{{WRAPPER}} .bt-form-field.bt-field-submit input[type="submit"]',
				]
			);

			$this->add_control(
				'cars_search_form_fields_btn_submit_color',[
					'label'     => esc_html__( 'Color', 'autoart' ),
					'type'      => Controls_Manager::COLOR,
					'default'   => '',
					'selectors' => [
						'{{WRAPPER}} .bt-form-field.bt-field-submit input[type="submit"]' => 'color: {{VALUE}};',
						'{{WRAPPER}} .bt-field-submit svg path' => 'stroke: {{VALUE}};',
					],
				]
			);

			$this->add_control(
				'cars_search_form_fields_btn_submit_bg',[
					'label'     => esc_html__( 'Background Color', 'autoart' ),
					'type'      => Controls_Manager::COLOR,
					'default'   => '',
					'selectors' => [
						'{{WRAPPER}} .bt-form-field.bt-field-submit' => 'background-color: {{VALUE}};',
					],
				]
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'cars_search__ss_top_search',[
				'label' => esc_html__( 'Top Search', 'autoart' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);
			$this->add_control(
				'top_search_heading_title',[
					'label' => esc_html__( 'Title', 'autoart' ),
					'type'  => Controls_Manager::HEADING,
				]
			);

			$this->add_group_control(
				Group_Control_Typography::get_type(),[
					'name'     => 'top_search_title_typography',
					'label'    => esc_html__( 'Typography', 'autoart' ),
					'default'  => '',
					'selector' => '{{WRAPPER}} .bt-elwg-cars-search--form-top-search p',
				]
			);

			$this->add_control(
				'top_search_title_color',[
					'label'     => esc_html__( 'Color', 'autoart' ),
					'type'      => Controls_Manager::COLOR,
					'default'   => '#fff',
					'selectors' => [
						'{{WRAPPER}} .bt-elwg-cars-search--form-top-search p' => 'color: {{VALUE}};',
					],
				]
			);

			$this->add_control(
				'top_search_heading_content',[
					'label' => esc_html__( 'Content', 'autoart' ),
					'type'  => Controls_Manager::HEADING,
				]
			);

			$this->add_group_control(
				Group_Control_Typography::get_type(),[
					'name'     => 'top_search_content_typography',
					'label'    => esc_html__( 'Typography', 'autoart' ),
					'default'  => '',
					'selector' => '{{WRAPPER}} .bt-elwg-cars-search--form-top-search a',
				]
			);

			$this->add_control(
				'top_search_content_color',[
					'label'     => esc_html__( 'Color', 'autoart' ),
					'type'      => Controls_Manager::COLOR,
					'default'   => '#fff',
					'selectors' => [
						'{{WRAPPER}} .bt-elwg-cars-search--form-top-search a' => 'color: {{VALUE}};',
						'{{WRAPPER}} .item-top-search:not(:first-child)::before' => 'color: {{VALUE}};',
					],
				]
			);

		$this->end_controls_section();
	}

	protected function register_controls() {
		$this->register_content_section_controls();
		$this->register_style_content_section_controls();
	}

	protected function render() {
		$settings  = $this->get_settings_for_display();
		?>
			<div class="bt-elwg-cars-search--style-2">
				<?php get_template_part( 'framework/templates/car-search', 'style', array('layout' => 'style-2', 'data' => $settings)); ?>
			</div>
		<?php
	}

	protected function content_template() {

	}
}
