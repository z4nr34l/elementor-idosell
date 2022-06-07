<?php

namespace ElementorIdosell\Tags;

use Exception;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Elementor Dynamic Tag - Random Number
 *
 * Elementor dynamic tag that returns a random number.
 *
 * @since 1.0.0
 */
class RetailPrice extends \Elementor\Core\DynamicTags\Tag {

	public string $table_name = "idosell_products";

	/**
	 * Class constructor
	 * @param array $data
	 */
	public function __construct( array $data = [] ) {
		parent::__construct( $data );
	}

	/**
	 * Get dynamic tag name.
	 *
	 * Retrieve the name of the random number tag.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return string Dynamic tag name.
	 */
	public function get_name() {
		return 'retail-price';
	}

	/**
	 * Get dynamic tag title.
	 *
	 * Returns the title of the random number tag.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return string Dynamic tag title.
	 */
	public function get_title() {
		return esc_html__( 'Retail price', 'elementor-idosell-retail-price-tag' );
	}

	/**
	 * Get dynamic tag groups.
	 *
	 * Retrieve the list of groups the random number tag belongs to.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return array Dynamic tag groups.
	 */
	public function get_group() {
		return [ 'idosell' ];
	}

	/**
	 * Get dynamic tag categories.
	 *
	 * Retrieve the list of categories the random number tag belongs to.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return array Dynamic tag categories.
	 */
	public function get_categories() {
		return [
			\Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY,
			\Elementor\Modules\DynamicTags\Module::NUMBER_CATEGORY
		];
	}

	protected function getData($input) {
    global $wpdb;

    if(isset($input)) {
      $cache = wp_cache_get($input, 'idosell_prices');
	    if($cache) {
		    return $cache;
	    } else {
		    try {
			    $sql = "SELECT `price` FROM " . $wpdb->prefix.$this->table_name . " WHERE `id` LIKE %s OR `code_producer` LIKE %s";
			    $sql = $wpdb->prepare($sql, $input, $input);
          $result = $wpdb->get_results($sql)[0]->price;
          wp_cache_set($input, $result, 'idosell_prices', 86400);
			    return $result;
		    } catch(Exception $exception) {
			    throw $exception;
		    }
	    }
    } else {
      return null;
    }
	}

	/**
	 * Register dynamic tag controls.
	 *
	 * Add input fields to allow the user to customize the ACF average tag settings.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @return void
	 */
	protected function register_controls() {
		$this->add_control(
			'prefix',
			[
				'label' => esc_html__( 'Prefix', 'elementor-idosell-retail-price-tag' ),
				'type' => 'text',
			]
		);
		$this->add_control(
			'input',
			[
				'label' => esc_html__( 'Producer/Product ID', 'elementor-idosell-retail-price-tag' ),
				'type' => 'text',
			]
		);
		$this->add_control(
			'currency',
			[
				'label' => esc_html__( 'Currency', 'elementor-idosell-retail-price-tag' ),
				'type' => 'text',
			]
		);
	}

	/**
	 * Render tag output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @return void
	 * @throws Exception
	 * @since 1.0.0
	 * @access public
	 */
	public function render() {
		?><p><?php echo $this->get_settings('prefix') ?><?php echo number_format((float) $this->getData($this->get_settings('input')), 2, ',', ' '); ?><?php echo $this->get_settings('currency') ?></p><?php
	}

}
