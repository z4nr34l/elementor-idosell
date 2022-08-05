<?php

namespace ElementorIdosell\Tags;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Tags {

	function register_new_dynamic_tag_group( $dynamic_tags_manager ) {

		$dynamic_tags_manager->register_group(
			'idosell',
			[
				'title' => esc_html__( 'IdoSell', 'elementor-idosell' )
			]
		);

	}

	function register_dynamic_tags( $dynamic_tags_manager ) {
		$dynamic_tags_manager->register( new RetailPrice );
	}

	public function __construct() {
		add_action( 'elementor/dynamic_tags/register', [$this, 'register_new_dynamic_tag_group'] );
		add_action( 'elementor/dynamic_tags/register', [$this, 'register_dynamic_tags'] );
	}

}
