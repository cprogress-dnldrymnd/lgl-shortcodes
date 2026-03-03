<?php
namespace AutoArtElementorWidgets\Widgets\CarLoopItem;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Css_Filter;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;

class Widget_CarLoopItem extends Widget_Base {

	public function get_name() {
		return 'bt-car-loop-item';
	}

	public function get_title() {
		return __( 'Car Loop Item', 'autoart' );
	}

	public function get_icon() {
		return 'eicon-posts-ticker';
	}

	public function get_categories() {
		return [ 'autoart' ];
	}

	protected function register_layout_section_controls() {
		$this->start_controls_section(
			'section_content',
			[
				'label' => __( 'Content', 'autoart' ),
			]
		);

		$this->add_group_control(
			Group_Control_Image_Size::get_type(),
			[
				'name' => 'thumbnail',
				'label' => __( 'Image Size', 'autoart' ),
				'show_label' => true,
				'default' => 'medium',
				'exclude' => [ 'custom' ],
			]
		);

		$this->add_responsive_control(
			'image_ratio',
			[
				'label' => __( 'Image Ratio', 'autoart' ),
				'type' => Controls_Manager::SLIDER,
				'default' => [
					'size' => 0.66,
				],
				'range' => [
					'px' => [
						'min' => 0.3,
						'max' => 2,
						'step' => 0.01,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .bt-post--thumbnail .bt-cover-image' => 'padding-bottom: calc( {{SIZE}} * 100% );',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function register_style_section_controls() {
		$this->start_controls_section(
			'section_style_box',
			[
				'label' => esc_html__( 'Box', 'autoart' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'box_border_width',
			[
				'label' => __( 'Border Width', 'autoart' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 50,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .bt-post--inner' => 'border-style: solid; border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
				],
			]
		);

		$this->add_control(
			'box_border_radius',
			[
				'label' => __( 'Border Radius', 'autoart' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .bt-post--inner' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'box_padding',
			[
				'label' => __( 'Padding', 'autoart' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 50,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .bt-post--inner' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'box_shadow',
				'selector' => '{{WRAPPER}} .bt-post--inner',
			]
		);

		$this->add_control(
			'box_bg_color',
			[
				'label' => __( 'Background Color', 'autoart' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .bt-post--inner' => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'box_border_color',
			[
				'label' => __( 'Border Color', 'autoart' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .bt-post--inner' => 'border-color: {{VALUE}}',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_image',
			[
				'label' => esc_html__( 'Image', 'autoart' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'img_border_radius',
			[
				'label' => __( 'Border Radius', 'autoart' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .bt-post--featured .bt-cover-image' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->start_controls_tabs( 'thumbnail_effects_tabs' );

		$this->start_controls_tab( 'thumbnail_tab_normal',
			[
				'label' => __( 'Normal', 'autoart' ),
			]
		);

		$this->add_group_control(
			Group_Control_Css_Filter::get_type(),
			[
				'name' => 'thumbnail_filters',
				'selector' => '{{WRAPPER}} .bt-post--featured img',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab( 'thumbnail_tab_hover',
			[
				'label' => __( 'Hover', 'autoart' ),
			]
		);

		$this->add_group_control(
			Group_Control_Css_Filter::get_type(),
			[
				'name' => 'thumbnail_hover_filters',
				'selector' => '{{WRAPPER}} .bt-post:hover .bt-post--featured img',
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_content',
			[
				'label' => esc_html__( 'Content', 'autoart' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'title_style',
			[
				'label' => __( 'Title', 'autoart' ),
				'type' => Controls_Manager::HEADING,
			]
		);

		$this->add_control(
			'title_color',
			[
				'label' => __( 'Color', 'autoart' ),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .bt-post--title' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'title_color_hover',
			[
				'label' => __( 'Color Hover', 'autoart' ),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .bt-post--title a:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'title_typography',
				'label' => __( 'Typography', 'autoart' ),
				'default' => '',
				'selector' => '{{WRAPPER}} .bt-post--title',
			]
		);

		$this->add_control(
			'price_style',
			[
				'label' => __( 'Price', 'autoart' ),
				'type' => Controls_Manager::HEADING,
			]
		);

		$this->add_control(
			'price_color',
			[
				'label' => __( 'Color', 'autoart' ),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .bt-post--price' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'price_typography',
				'label' => __( 'Typography', 'autoart' ),
				'default' => '',
				'selector' => '{{WRAPPER}} .bt-post--price',
			]
		);

		$this->add_control(
			'body_style',
			[
				'label' => __( 'Body', 'autoart' ),
				'type' => Controls_Manager::HEADING,
			]
		);

		$this->add_control(
			'body_color',
			[
				'label' => __( 'Color', 'autoart' ),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .bt-post--body' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'body_bg_color',
			[
				'label' => __( 'Background Color', 'autoart' ),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .bt-post--body' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'body_typography',
				'label' => __( 'Typography', 'autoart' ),
				'default' => '',
				'selector' => '{{WRAPPER}} .bt-post--body',
			]
		);

		$this->add_control(
			'meta_style',
			[
				'label' => __( 'Meta', 'autoart' ),
				'type' => Controls_Manager::HEADING,
			]
		);

		$this->add_control(
			'meta_icon_color',
			[
				'label' => __( 'Icon Color', 'autoart' ),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .bt-post--meta-item svg' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'meta_label_color',
			[
				'label' => __( 'Label Color', 'autoart' ),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .bt-post--meta-item .bt-label' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'meta_label_typography',
				'label' => __( 'Label Typography', 'autoart' ),
				'default' => '',
				'selector' => '{{WRAPPER}} .bt-post--meta-item .bt-label',
			]
		);

		$this->add_control(
			'meta_value_color',
			[
				'label' => __( 'Value Color', 'autoart' ),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .bt-post--meta-item .bt-value' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'meta_value_typography',
				'label' => __( 'Value Typography', 'autoart' ),
				'default' => '',
				'selector' => '{{WRAPPER}} .bt-post--meta-item .bt-value',
			]
		);

		$this->add_control(
			'readmore_style',
			[
				'label' => __( 'Read More', 'autoart' ),
				'type' => Controls_Manager::HEADING,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'readmore_typography',
				'label' => __( 'Typography', 'autoart' ),
				'default' => '',
				'selector' => '{{WRAPPER}} .bt-post--readmore a',
			]
		);

		$this->start_controls_tabs( 'readmore_effects_tabs' );

		$this->start_controls_tab( 'readmore_tab_normal',
			[
				'label' => __( 'Normal', 'autoart' ),
			]
		);

		$this->add_control(
			'readmore_color',
			[
				'label' => __( 'Color', 'autoart' ),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .bt-post--readmore a' => 'color: {{VALUE}};',
					'{{WRAPPER}} .bt-post--readmore a svg path' => 'stroke: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'readmore_bg_color',
			[
				'label' => __( 'Background Color', 'autoart' ),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .bt-post--readmore a' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab( 'readmore_tab_hover',
			[
				'label' => __( 'Hover', 'autoart' ),
			]
		);

		$this->add_control(
			'readmore_color_hover',
			[
				'label' => __( 'Color', 'autoart' ),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .bt-post--readmore a:hover' => 'color: {{VALUE}};',
					'{{WRAPPER}} .bt-post--readmore a:hover svg path' => 'stroke: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'readmore_bg_color_hover',
			[
				'label' => __( 'Background Color', 'autoart' ),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .bt-post--readmore a:hover' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_control(
			'icon_btn_style',
			[
				'label' => __( 'Icon Button', 'autoart' ),
				'type' => Controls_Manager::HEADING,
			]
		);

		$this->start_controls_tabs( 'icon_btn_effects_tabs' );

		$this->start_controls_tab( 'icon_btn_tab_normal',
			[
				'label' => __( 'Normal', 'autoart' ),
			]
		);

		$this->add_control(
			'icon_btn_color',
			[
				'label' => __( 'Color', 'autoart' ),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .bt-post--icon-btn .bt-icon-btn svg' => 'fill: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'icon_btn_bg_color',
			[
				'label' => __( 'Background Color', 'autoart' ),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .bt-post--icon-btn .bt-icon-btn' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab( 'icon_btn_tab_added',
			[
				'label' => __( 'Added', 'autoart' ),
			]
		);

		$this->add_control(
			'icon_btn_color_hover',
			[
				'label' => __( 'Color', 'autoart' ),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .bt-post--icon-btn .bt-icon-btn.added svg' => 'fill: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'icon_btn_bg_color_hover',
			[
				'label' => __( 'Background Color', 'autoart' ),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .bt-post--icon-btn .bt-icon-btn.added' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

	}

	protected function register_controls() {
		$this->register_layout_section_controls();
		$this->register_style_section_controls();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		?>
			<div class="bt-elwg-car-loop-item--default">
				<?php get_template_part( 'framework/templates/car', 'style', array('image-size' => $settings['thumbnail_size'], 'layout' => 'default')); ?>
	    </div>
		<?php
	}

	protected function content_template() {

	}
}
