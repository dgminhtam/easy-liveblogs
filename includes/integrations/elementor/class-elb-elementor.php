<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class ELB_Elementor_Integration {

	/**
	 * Init integration.
	 * 
	 * @return void
	 */
	public function init() {
		add_action( 'elementor/widgets/register', array( $this, 'register_widgets' ) );
	}

	/**
	 * Register widgets.
	 * 
	 * @param \Elementor\Widgets_Manager $widgets_manager
	 * @return void
	 */
	public function register_widgets( $widgets_manager ) {
		require_once ELB_PATH . 'includes/integrations/elementor/widgets/class-elb-widget-liveblog.php';

		$widgets_manager->register( new \ELB_Widget_Liveblog() );
	}
}
