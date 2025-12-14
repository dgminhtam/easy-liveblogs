<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class ELB_Widget_Liveblog extends \Elementor\Widget_Base {

	public function get_name() {
		return 'elb_liveblog';
	}

	public function get_title() {
		return esc_html__( 'Easy Liveblog', 'easy-liveblogs' );
	}

	public function get_icon() {
		return 'eicon-post-list';
	}

	public function get_categories() {
		return array( 'general' );
	}

	protected function register_controls() {
		$this->start_controls_section(
			'content_section',
			array(
				'label' => esc_html__( 'Content', 'easy-liveblogs' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'liveblog_id',
			array(
				'label'   => esc_html__( 'Liveblog', 'easy-liveblogs' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => elb_get_liveblogs(),
				'default' => 0,
			)
		);

		$this->add_control(
			'show_entries',
			array(
				'label'   => esc_html__( 'Number of entries', 'easy-liveblogs' ),
				'type'    => \Elementor\Controls_Manager::NUMBER,
				'min'     => 1,
				'max'     => 100,
				'step'    => 1,
				'default' => 10,
			)
		);

		$this->add_control(
			'update_interval',
			array(
				'label'   => esc_html__( 'Update interval (seconds)', 'easy-liveblogs' ),
				'type'    => \Elementor\Controls_Manager::NUMBER,
				'min'     => 5,
				'max'     => 300,
				'step'    => 5,
				'default' => 30,
			)
		);

		$this->add_control(
			'append_timestamp',
			array(
				'label'        => esc_html__( 'Append timestamp', 'easy-liveblogs' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'easy-liveblogs' ),
				'label_off'    => esc_html__( 'No', 'easy-liveblogs' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'style_section',
			array(
				'label' => esc_html__( 'Container', 'easy-liveblogs' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			array(
				'name'     => 'container_background',
				'label'    => esc_html__( 'Background', 'easy-liveblogs' ),
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .elb-liveblog',
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			array(
				'name'     => 'container_border',
				'label'    => esc_html__( 'Border', 'easy-liveblogs' ),
				'selector' => '{{WRAPPER}} .elb-liveblog',
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'container_box_shadow',
				'label'    => esc_html__( 'Box Shadow', 'easy-liveblogs' ),
				'selector' => '{{WRAPPER}} .elb-liveblog',
			)
		);

		$this->add_responsive_control(
			'container_padding',
			array(
				'label'      => esc_html__( 'Padding', 'easy-liveblogs' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .elb-liveblog' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				),
			)
		);

		$this->add_responsive_control(
			'container_margin',
			array(
				'label'      => esc_html__( 'Margin', 'easy-liveblogs' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .elb-liveblog' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				),
			)
		);

		$this->add_control(
			'enable_fixed_height',
			array(
				'label'        => esc_html__( 'Enable Fixed Height', 'easy-liveblogs' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'easy-liveblogs' ),
				'label_off'    => esc_html__( 'No', 'easy-liveblogs' ),
				'return_value' => 'yes',
				'default'      => 'no',
				'separator'    => 'before',
			)
		);

		$this->add_responsive_control(
			'list_height',
			array(
				'label'           => esc_html__( 'List Height', 'easy-liveblogs' ),
				'type'            => \Elementor\Controls_Manager::SLIDER,
				'size_units'      => array( 'px' ),
				'range'           => array(
					'px' => array(
						'min'  => 200,
						'max'  => 1000,
						'step' => 10,
					),
				),
				'devices'         => array( 'desktop', 'tablet', 'mobile' ),
				'desktop_default' => array(
					'size' => 500,
					'unit' => 'px',
				),
				'tablet_default'  => array(
					'size' => 400,
					'unit' => 'px',
				),
				'mobile_default'  => array(
					'size' => 300,
					'unit' => 'px',
				),
				'condition'       => array(
					'enable_fixed_height' => 'yes',
				),
				'selectors'       => array(
					'{{WRAPPER}} ul.elb-liveblog-list' => 'height: {{SIZE}}{{UNIT}}; overflow-y: auto; overflow-x: hidden;',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'entry_style_section',
			array(
				'label' => esc_html__( 'Entry', 'easy-liveblogs' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'entry_background_color',
			array(
				'label'     => esc_html__( 'Background Color', 'easy-liveblogs' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .elb-liveblog-post' => 'background-color: {{VALUE}} !important;',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			array(
				'name'     => 'entry_border',
				'label'    => esc_html__( 'Border', 'easy-liveblogs' ),
				'selector' => '{{WRAPPER}} .elb-liveblog-post',
			)
		);

		$this->add_responsive_control(
			'entry_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'easy-liveblogs' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .elb-liveblog-post' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'entry_box_shadow',
				'label'    => esc_html__( 'Box Shadow', 'easy-liveblogs' ),
				'selector' => '{{WRAPPER}} .elb-liveblog-post',
			)
		);

		$this->add_responsive_control(
			'entry_padding',
			array(
				'label'      => esc_html__( 'Padding', 'easy-liveblogs' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .elb-liveblog-post' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				),
			)
		);

		$this->add_responsive_control(
			'entry_margin_bottom',
			array(
				'label'      => esc_html__( 'Margin Bottom', 'easy-liveblogs' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 25,
				),
				'selectors'  => array(
					'{{WRAPPER}} .elb-liveblog-post' => 'margin-bottom: {{SIZE}}{{UNIT}} !important;',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'buttons_style_section',
			array(
				'label' => esc_html__( 'Buttons', 'easy-liveblogs' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'buttons_typography',
				'selector' => '{{WRAPPER}} .elb-button',
			)
		);

		$this->start_controls_tabs( 'buttons_tabs' );

		$this->start_controls_tab(
			'buttons_normal_tab',
			array(
				'label' => esc_html__( 'Normal', 'easy-liveblogs' ),
			)
		);

		$this->add_control(
			'buttons_text_color',
			array(
				'label'     => esc_html__( 'Text Color', 'easy-liveblogs' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .elb-button' => 'color: {{VALUE}} !important;',
				),
			)
		);

		$this->add_control(
			'buttons_background_color',
			array(
				'label'     => esc_html__( 'Background Color', 'easy-liveblogs' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .elb-button' => 'background-color: {{VALUE}} !important;',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			array(
				'name'     => 'buttons_border',
				'label'    => esc_html__( 'Border', 'easy-liveblogs' ),
				'selector' => '{{WRAPPER}} .elb-button',
			)
		);

		$this->add_responsive_control(
			'buttons_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'easy-liveblogs' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .elb-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'buttons_box_shadow',
				'label'    => esc_html__( 'Box Shadow', 'easy-liveblogs' ),
				'selector' => '{{WRAPPER}} .elb-button',
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'buttons_hover_tab',
			array(
				'label' => esc_html__( 'Hover', 'easy-liveblogs' ),
			)
		);

		$this->add_control(
			'buttons_hover_text_color',
			array(
				'label'     => esc_html__( 'Text Color', 'easy-liveblogs' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .elb-button:hover' => 'color: {{VALUE}} !important;',
				),
			)
		);

		$this->add_control(
			'buttons_hover_background_color',
			array(
				'label'     => esc_html__( 'Background Color', 'easy-liveblogs' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .elb-button:hover' => 'background-color: {{VALUE}} !important;',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			array(
				'name'     => 'buttons_hover_border',
				'label'    => esc_html__( 'Border', 'easy-liveblogs' ),
				'selector' => '{{WRAPPER}} .elb-button:hover',
			)
		);

		$this->add_responsive_control(
			'buttons_hover_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'easy-liveblogs' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .elb-button:hover' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'buttons_hover_box_shadow',
				'label'    => esc_html__( 'Box Shadow', 'easy-liveblogs' ),
				'selector' => '{{WRAPPER}} .elb-button:hover',
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control(
			'buttons_padding',
			array(
				'label'      => esc_html__( 'Padding', 'easy-liveblogs' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .elb-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				),
				'separator'  => 'before',
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'timeline_style_section',
			array(
				'label' => esc_html__( 'Timeline', 'easy-liveblogs' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'timeline_line_heading',
			array(
				'label' => esc_html__( 'Line', 'easy-liveblogs' ),
				'type'  => \Elementor\Controls_Manager::HEADING,
			)
		);

		$this->add_control(
			'timeline_line_color',
			array(
				'label'     => esc_html__( 'Color', 'easy-liveblogs' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ul.elb-liveblog-list' => 'border-left-color: {{VALUE}} !important;',
				),
			)
		);

		$this->add_control(
			'timeline_line_style',
			array(
				'label'   => esc_html__( 'Style', 'easy-liveblogs' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => array(
					'solid'  => esc_html__( 'Solid', 'easy-liveblogs' ),
					'dotted' => esc_html__( 'Dotted', 'easy-liveblogs' ),
					'dashed' => esc_html__( 'Dashed', 'easy-liveblogs' ),
				),
				'default' => 'solid',
				'selectors' => array(
					'{{WRAPPER}} ul.elb-liveblog-list' => 'border-left-style: {{VALUE}} !important;',
				),
			)
		);

		$this->add_responsive_control(
			'timeline_line_width',
			array(
				'label'      => esc_html__( 'Width', 'easy-liveblogs' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 10,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} ul.elb-liveblog-list' => 'border-left-width: {{SIZE}}{{UNIT}} !important;',
				),
			)
		);

		$this->add_responsive_control(
			'timeline_list_indent',
			array(
				'label'      => esc_html__( 'List Indent', 'easy-liveblogs' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'default'    => array(
					'unit' => 'px',
					'size' => 20,
				),
				'selectors'  => array(
					'{{WRAPPER}} ul.elb-liveblog-list' => 'padding-left: {{SIZE}}{{UNIT}} !important;',
				),
			)
		);

		$this->add_responsive_control(
			'timeline_dot_position',
			array(
				'label'      => esc_html__( 'Dot Horizontal Position', 'easy-liveblogs' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => -100,
						'max' => 100,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => -27,
				),
				'selectors'  => array(
					'{{WRAPPER}} .elb-liveblog-post::before' => 'left: {{SIZE}}{{UNIT}} !important;',
				),
			)
		);

		$this->add_control(
			'timeline_standard_dots_heading',
			array(
				'label'     => esc_html__( 'Standard Dots', 'easy-liveblogs' ),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'timeline_dot_color',
			array(
				'label'     => esc_html__( 'Color', 'easy-liveblogs' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .elb-liveblog-post:not(:first-child)::before' => 'background-color: {{VALUE}} !important;',
				),
			)
		);

		$this->add_control(
			'timeline_dot_border_color',
			array(
				'label'     => esc_html__( 'Border Color', 'easy-liveblogs' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .elb-liveblog-post:not(:first-child)::before' => 'border-color: {{VALUE}} !important;',
				),
			)
		);

		$this->add_responsive_control(
			'timeline_dot_size',
			array(
				'label'      => esc_html__( 'Size', 'easy-liveblogs' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 4,
						'max' => 30,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .elb-liveblog-post:not(:first-child)::before' => 'width: {{SIZE}}{{UNIT}} !important; height: {{SIZE}}{{UNIT}} !important;',
				),
			)
		);

		$this->add_control(
			'timeline_first_dot_heading',
			array(
				'label'     => esc_html__( 'First Dot (Newest)', 'easy-liveblogs' ),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'timeline_first_dot_color',
			array(
				'label'     => esc_html__( 'Color', 'easy-liveblogs' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .elb-liveblog-post:first-child::before' => 'background-color: {{VALUE}} !important;',
				),
			)
		);

		$this->add_control(
			'timeline_first_dot_border_color',
			array(
				'label'     => esc_html__( 'Border Color', 'easy-liveblogs' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .elb-liveblog-post:first-child::before' => 'border-color: {{VALUE}} !important;',
				),
			)
		);

		$this->add_responsive_control(
			'timeline_first_dot_size',
			array(
				'label'      => esc_html__( 'Size', 'easy-liveblogs' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 4,
						'max' => 30,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .elb-liveblog-post:first-child::before' => 'width: {{SIZE}}{{UNIT}} !important; height: {{SIZE}}{{UNIT}} !important;',
				),
			)
		);

		$this->add_control(
			'timeline_first_dot_pulse',
			array(
				'label'        => esc_html__( 'Pulse Animation', 'easy-liveblogs' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'On', 'easy-liveblogs' ),
				'label_off'    => esc_html__( 'Off', 'easy-liveblogs' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'newest_post_style_section',
			array(
				'label' => esc_html__( 'Newest Post Highlight', 'easy-liveblogs' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'highlight_newest_post',
			array(
				'label'        => esc_html__( 'Highlight Newest Post', 'easy-liveblogs' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'easy-liveblogs' ),
				'label_off'    => esc_html__( 'No', 'easy-liveblogs' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'newest_post_container_heading',
			array(
				'label'     => esc_html__( 'Container Style', 'easy-liveblogs' ),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array(
					'highlight_newest_post' => 'yes',
				),
			)
		);

		$this->add_control(
			'newest_post_background_color',
			array(
				'label'     => esc_html__( 'Background Color', 'easy-liveblogs' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .elb-highlight-newest .elb-liveblog-post:first-child' => 'background-color: {{VALUE}} !important;',
				),
				'condition' => array(
					'highlight_newest_post' => 'yes',
				),
			)
		);

		$this->add_control(
			'newest_post_border_color',
			array(
				'label'     => esc_html__( 'Border Color', 'easy-liveblogs' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .elb-highlight-newest .elb-liveblog-post:first-child' => 'border-color: {{VALUE}} !important;',
				),
				'condition' => array(
					'highlight_newest_post' => 'yes',
				),
			)
		);



		$this->add_control(
			'newest_post_heading_heading',
			array(
				'label'     => esc_html__( 'Heading Style', 'easy-liveblogs' ),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array(
					'highlight_newest_post' => 'yes',
				),
			)
		);

		$this->add_control(
			'newest_post_heading_color',
			array(
				'label'     => esc_html__( 'Heading Color', 'easy-liveblogs' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .elb-highlight-newest .elb-liveblog-post:first-child .elb-liveblog-post-heading' => 'color: {{VALUE}} !important;',
				),
				'condition' => array(
					'highlight_newest_post' => 'yes',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'meta_data_style_section',
			array(
				'label' => esc_html__( 'Meta Data', 'easy-liveblogs' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'time_color',
			array(
				'label'     => esc_html__( 'Time Color', 'easy-liveblogs' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .elb-liveblog-post-time' => 'color: {{VALUE}} !important;',
				),
			)
		);

		$this->add_control(
			'author_color',
			array(
				'label'     => esc_html__( 'Author Color', 'easy-liveblogs' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .elb-liveblog-post-author' => 'color: {{VALUE}} !important;',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'time_typography',
				'label'    => esc_html__( 'Typography', 'easy-liveblogs' ),
				'selector' => '{{WRAPPER}} .elb-liveblog-post-time',
			)
		);

		$this->add_responsive_control(
			'time_spacing',
			array(
				'label'      => esc_html__( 'Spacing', 'easy-liveblogs' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 50,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .elb-liveblog-post-time' => 'margin-bottom: {{SIZE}}{{UNIT}} !important;',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'heading_style_section',
			array(
				'label' => esc_html__( 'Entry Title', 'easy-liveblogs' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'heading_color',
			array(
				'label'     => esc_html__( 'Text Color', 'easy-liveblogs' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .elb-liveblog-post-heading' => 'color: {{VALUE}} !important;',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'title_typography',
				'label'    => esc_html__( 'Typography', 'easy-liveblogs' ),
				'selector' => '{{WRAPPER}} .elb-liveblog-post-heading',
			)
		);

		$this->add_responsive_control(
			'heading_spacing',
			array(
				'label'      => esc_html__( 'Spacing', 'easy-liveblogs' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 50,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .elb-liveblog-post-heading' => 'margin-bottom: {{SIZE}}{{UNIT}} !important;',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'content_style_section',
			array(
				'label' => esc_html__( 'Entry Content', 'easy-liveblogs' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'content_color',
			array(
				'label'     => esc_html__( 'Text Color', 'easy-liveblogs' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .elb-liveblog-post-content' => 'color: {{VALUE}} !important;',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'content_typography',
				'label'    => esc_html__( 'Typography', 'easy-liveblogs' ),
				'selector' => '{{WRAPPER}} .elb-liveblog-post-content',
			)
		);

		$this->add_responsive_control(
			'content_spacing',
			array(
				'label'      => esc_html__( 'Spacing', 'easy-liveblogs' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 50,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .elb-liveblog-post-content' => 'margin-bottom: {{SIZE}}{{UNIT}} !important;',
				),
			)
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		if ( empty( $settings['liveblog_id'] ) ) {
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
                echo '<div class="elementor-alert elementor-alert-warning">' . esc_html__( 'Please select a liveblog.', 'easy-liveblogs' ) . '</div>';
            }
			return;
		}

        $liveblog_id = $settings['liveblog_id'];
        $endpoint = elb_get_liveblog_api_endpoint( $liveblog_id );
        $status = elb_get_liveblog_status( $liveblog_id );
        $show_entries = $settings['show_entries'];
        $append_timestamp = $settings['append_timestamp'] === 'yes' ? '1' : '0';

        wp_enqueue_script( 'elb' );
        wp_enqueue_script( 'wp-embed' );

        $classes = array( 'elb-liveblog', 'elb-theme-' . elb_get_theme() );

        if ( current_user_can( 'edit_post', $liveblog_id ) ) {
            $classes[] = 'elb-is-editor';
        }

		// Handle Newest Post Highlight Class
		if ( ! empty( $settings['highlight_newest_post'] ) && $settings['highlight_newest_post'] === 'yes' ) {
			$classes[] = 'elb-highlight-newest';

			// Handle Pulse Animation Check
			if ( empty( $settings['timeline_first_dot_pulse'] ) || $settings['timeline_first_dot_pulse'] !== 'yes' ) {
				$classes[] = 'elb-pulse-off';
			}
		} else {
            // Apply Pulse Check even if not highlighting newest post container
            if ( empty( $settings['timeline_first_dot_pulse'] ) || $settings['timeline_first_dot_pulse'] !== 'yes' ) {
				$classes[] = 'elb-pulse-off';
			}
        }

        do_action( 'elb_before_liveblog', $liveblog_id, array() );

        ?>
        <div id="elb-liveblog" class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" 
             data-append-timestamp="<?php echo esc_attr( $append_timestamp ); ?>" 
             data-status="<?php echo esc_attr( $status ); ?>" 
             data-show-entries="<?php echo esc_attr( $show_entries ); ?>" 
             data-endpoint="<?php echo esc_url( $endpoint ); ?>">
            
            <div class="elb-liveblog-closed-message" style="display: none;"><?php echo esc_html__( 'The liveblog has ended.', 'easy-liveblogs' ); ?></div>

            <button id="elb-show-new-posts" class="elb-button button" style="display: none;"></button>

            <div class="elb-no-liveblog-entries-message" style="display: none;"><?php echo esc_html__( 'No liveblog updates yet.', 'easy-liveblogs' ); ?></div>

            <ul class="elb-liveblog-list"></ul>

            <div class="elb-loader">
                <svg width="45" height="45" viewBox="0 0 45 45" xmlns="http://www.w3.org/2000/svg" stroke="currentColor">
                    <g fill="none" fill-rule="evenodd" transform="translate(1 1)" stroke-width="2">
                        <circle cx="22" cy="22" r="6" stroke-opacity="0">
                            <animate attributeName="r" begin="1.5s" dur="3s" values="6;22" calcMode="linear" repeatCount="indefinite" />
                            <animate attributeName="stroke-opacity" begin="1.5s" dur="3s" values="1;0" calcMode="linear" repeatCount="indefinite" />
                            <animate attributeName="stroke-width" begin="1.5s" dur="3s" values="2;0" calcMode="linear" repeatCount="indefinite" />
                        </circle>
                        <circle cx="22" cy="22" r="6" stroke-opacity="0">
                            <animate attributeName="r" begin="3s" dur="3s" values="6;22" calcMode="linear" repeatCount="indefinite" />
                            <animate attributeName="stroke-opacity" begin="3s" dur="3s" values="1;0" calcMode="linear" repeatCount="indefinite" />
                            <animate attributeName="stroke-width" begin="3s" dur="3s" values="2;0" calcMode="linear" repeatCount="indefinite" />
                        </circle>
                        <circle cx="22" cy="22" r="8">
                            <animate attributeName="r" begin="0s" dur="1.5s" values="6;1;2;3;4;5;6" calcMode="linear" repeatCount="indefinite" />
                        </circle>
                    </g>
                </svg>
            </div>

            <button id="elb-load-more" style="display: none;" class="elb-button button"><?php echo esc_html__( 'Load more', 'easy-liveblogs' ); ?></button>
        </div>
        <?php

        do_action( 'elb_after_liveblog', $liveblog_id, array() );
	}
}
