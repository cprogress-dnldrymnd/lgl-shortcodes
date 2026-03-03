<?php
namespace AutoArtElementorWidgets\Widgets\CardImageTextGrid;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;
use Elementor\Utils;
use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Css_Filter;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;

class Widget_CardImageTextGrid extends Widget_Base {

	public function get_name() {
		return 'bt-card-image-text-grid';
	}

	public function get_title() {
		return __( 'Card Image Text Grid', 'autoart' );
	}

	public function get_icon() {
		return 'eicon-posts-ticker';
	}

	public function get_categories() {
		return [ 'autoart' ];
	}

	public function get_script_depends() {
		return [ 'elementor-widgets' ];
	}

	protected function register_layout_section_controls() {
		$this->start_controls_section(
			'section_content',[
				'label' => esc_html__( 'Content', 'autoart' ),
			]
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'citg_image', [
				'label'   => esc_html__( 'Image', 'autoart' ),
				'type'    => Controls_Manager::MEDIA,
				'default' => [
					'url' => Utils::get_placeholder_image_src(),
				],
			]
		);

		$repeater->add_control(
			'citg_text', [
				'label'       => esc_html__( 'Text', 'autoart' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'default'     => 'This is text',
			]
		);

		$repeater->add_control(
			'citg_link', [
				'label'       => esc_html__( 'Button Link', 'autoart' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'default'     => '',
			]
		);

		$this->add_control(
			'list_card_image_text',[
				'label'   => esc_html__( 'List Card Image Text', 'autoart' ),
				'type'    => Controls_Manager::REPEATER,
				'fields'  => $repeater->get_controls(),
				'default' => [
					[
						'citg_image' => Utils::get_placeholder_image_src(),
						'citg_text'  => __( 'This is text 01', 'autoart' ),
						'citg_link'  => '#'
					],
					[
						'citg_image' => Utils::get_placeholder_image_src(),
						'citg_text'  => __( 'This is text 02', 'autoart' ),
						'citg_link'  => '#'
					],
					[
						'citg_image' => Utils::get_placeholder_image_src(),
						'citg_text'  => __( 'This is text 03', 'autoart' ),
						'citg_link'  => '#'
					],
				],
				'title_field' => '{{{ citg_text }}}',
			]
		);

		$this->add_group_control(
			Group_Control_Image_Size::get_type(),[
				'name'       => 'thumbnail',
				'label'      => esc_html__( 'Image Size', 'autoart' ),
				'show_label' => true,
				'default'    => 'medium_large',
				'exclude'    => [ 'custom' ],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_layout',[
				'label' => esc_html__( 'Layout', 'autoart' ),
			]
		);

			$this->add_control(
				'citg_layout_style',[
					'label'   => esc_html__( 'Layout Style', 'autoart' ),
					'type'    => Controls_Manager::SELECT,
					'default' => 'default',
					'options' => [
						'default' => 'Default',
						'style-1' => 'Style 1',
					],
				]
			);

			$this->add_responsive_control(
				'citg_columns',[
					'label'          => esc_html__( 'Columns', 'autoart' ),
					'type'           => Controls_Manager::SELECT,
					'default'        => '3',
					'tablet_default' => '2',
					'mobile_default' => '1',
					'options' => [
						'1' => '1',
						'2' => '2',
						'3' => '3',
						'4' => '4',
						'5' => '5',
						'6' => '6',
					],
					'selectors' => [
						'{{WRAPPER}} .bt-elwg-card-image-text-grid-inner' => 'grid-template-columns: repeat({{VALUE}}, 1fr)',
					],
				]
			);

			$this->add_responsive_control(
				'citg_gap_col',[
					'label'      => esc_html__( 'Gap between columns', 'autoart' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => [ 'px', '%'],
					'range' => [
						'px' => [
							'min'  => 0,
							'max'  => 100,
							'step' => 1,
						],
						'%' => [
							'min' => 0,
							'max' => 100,
						],
					],
					'default' => [
						'unit' => 'px',
						'size' => 20,
					],
					'selectors' => [
						'{{WRAPPER}} .bt-elwg-card-image-text-grid-inner' => 'column-gap: {{SIZE}}{{UNIT}}',
					],
				]
			);

			$this->add_responsive_control(
				'citg_gap_row',[
					'label' => __( 'Gap between rows', 'autoart' ),
					'label' => esc_html__( 'Layout', 'autoart' ),
					'type'  => Controls_Manager::SLIDER,
					'size_units' => [ 'px', '%'],
					'range' => [
						'px' => [
							'min' => 0,
							'max' => 100,
							'step' => 1,
						],
						'%' => [
							'min' => 0,
							'max' => 100,
						],
					],
					'default' => [
						'unit' => 'px',
						'size' => 20,
					],
					'selectors' => [
						'{{WRAPPER}} .bt-elwg-card-image-text-grid-inner' => 'row-gap: {{SIZE}}{{UNIT}}',
					],
				]
			);

		$this->end_controls_section();
	}

	protected function register_style_section_controls() {
		$this->start_controls_section(
			'section_style_image',[
				'label' => esc_html__( 'Image', 'autoart' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

			$this->add_responsive_control(
				'citg_image_height',[
					'label'      => esc_html__( 'Height (px)', 'autoart' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => [ 'px'],
					'range'      => [
						'px' => [
							'min' => 0,
							'max' => 1000,
							'step' => 1,
						]
					],
					'default'  => [
						'unit' => 'px',
						'size' => 402,
					],
					'selectors' => [
						'{{WRAPPER}} .item-card-image-text--thumbnail' => 'min-height: {{SIZE}}{{UNIT}}',
					],
				]
			);

			$this->add_responsive_control(
				'citg_border_radius',[
					'label' => esc_html__( 'Border Radius', 'autoart' ),
					'type'  => Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', '%', 'em' ], 
					'default'    => [
						'top'    => 20,
						'right'  => 20,
						'bottom' => 20,
						'left'   => 20,
						'unit'   => 'px',
					],
					'selectors' => [
						'{{WRAPPER}} .item-card-image-text--thumbnail' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						'{{WRAPPER}} .item-card-image-text-inner' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);

			$this->add_group_control(
				Group_Control_Css_Filter::get_type(),[
					'name'     => 'citg_thumbnail_filters',
					'selector' => '{{WRAPPER}} .item-card-image-text--thumbnail img',
				]
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_heading',[
				'label' => esc_html__( 'Text', 'autoart' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

			$this->add_group_control(
				Group_Control_Typography::get_type(),[
					'name'     => 'citg_text_typography',
					'label'    => esc_html__( 'Typography', 'autoart' ),
					'default'  => '',
					'selector' => '{{WRAPPER}} .item-card-image-text h3 ',
				]
			);

			$this->add_control(
				'citg_heading_color',[
					'label'     => esc_html__( 'Color', 'autoart' ),
					'type'      => Controls_Manager::COLOR,
					'default'   => '',
					'selectors' => [
						'{{WRAPPER}} .item-card-image-text h3' => 'color: {{VALUE}};',
					],
				]
			);

			$this->add_control(
				'citg_heading_bg',[
					'label'     => esc_html__( 'Background Color', 'autoart' ),
					'type'      => Controls_Manager::COLOR,
					'default'   => '',
					'selectors' => [
						'{{WRAPPER}} .item-card-image-text--content' => 'background-color: {{VALUE}};',
					],
				]
			);

			$this->add_responsive_control(
				'citg_heading_padding',[
					'label'      => esc_html__( 'Padding', 'autoart' ),
					'type'       => Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', '%' ],
					'range' => [
						'px' => [
							'min' => 0,
							'max' => 100,
						],
					],
					'default' => [
						'top'    => 26,
						'right'  => 10,
						'bottom' => 26,
						'left'   => 10,
						'unit'   => 'px',
					],
					'selectors' => [
						'{{WRAPPER}} .item-card-image-text--content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
					],
				]
			);

		$this->end_controls_section();
	}

	protected function register_controls() {
		$this->register_layout_section_controls();
		$this->register_style_section_controls();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		if ( empty( $settings['list_card_image_text'] ) ) {
			return;
		}

	?>
		<div class="bt-elwg-card-image-text-grid --<?php echo esc_attr($settings['citg_layout_style']) ?>">
			<div class="bt-elwg-card-image-text-grid-inner"> 
				<?php foreach ( $settings['list_card_image_text'] as $index => $item ): ?>
					<?php 
						$attachment = wp_get_attachment_image_src( $item['citg_image']['id'], $settings['thumbnail_size'] ); 
					?>
					<div class="item-card-image-text"> 
						<div class="item-card-image-text-inner">
							<a href="<?php echo esc_url($item['citg_image']['url'])  ?>" class="item-card-image-text--thumbnail elementor-clickable" data-elementor-lightbox-slideshow="bt-card-car">
								<?php if( !empty( $attachment ) ) {
										echo '<img src=" ' . esc_url( $attachment[0] ) . ' " alt="">';
									} else {
										echo '<img src=" ' . esc_url( $item['citg_image']['url'] ) . ' " alt="image">';
								} ?>
							</a>

							<div class="item-card-image-text--content"> 
								<?php if(!empty($item['citg_text'])): ?>
										<h3> <?php echo esc_html($item['citg_text']) ?> </h3>
								<?php endif;?>	
									
								<?php if(!empty($item['citg_link'])): ?>
									<a href="<?php echo esc_url($item['citg_link']) ?>"> </a>
								<?php endif;?>
							</div>
						</div>	
					</div>
				<?php endforeach;?>		
			</div>	
	    </div>
	<?php }

	protected function content_template() {

	}
}
