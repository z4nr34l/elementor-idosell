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

	/**
	 * Plugin options from settings
	 * @var
	 */
	private $options;

	/**
	 * Class constructor
	 * @param array $data
	 */
	public function __construct( array $data = [] ) {
		parent::__construct( $data );
		$this->options = get_option('idosell');
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

	protected function getExternalData($SKU) {
		try {
			$address = $this->options['gateway_url'];

			$request = array();
			$request['authenticate'] = array();
			$request['authenticate']['userLogin'] = $this->options['login'];
			$request['authenticate']['authenticateKey'] = sha1(date('Ymd') . sha1($this->options['password']));
			$request['params'] = array();
			$request['params']['returnProducts'] = "active";
			$request['params']['productParams'] = array();
			$request['params']['productParams'][0] = array();
			$request['params']['productParams'][0]['productCode'] = $SKU;
			$request['params']['returnElements'] = array();
			$request['params']['returnElements'][0] = "retail_price";

			$request_json = json_encode($request);
			$headers = array(
				'Accept: application/json',
				'Content-Type: application/json;charset=UTF-8'
			);

			$curl = curl_init($address);
			curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($curl, CURLOPT_FORBID_REUSE, 1);
			curl_setopt($curl, CURLINFO_HEADER_OUT, 1);
			curl_setopt($curl, CURLOPT_HEADER, 1);
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $request_json);
			curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

			$response = curl_exec($curl);
			$header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
			$body = substr($response, $header_size);
			curl_close($curl);
			$json = json_decode($body, true);

			return $json['results'][0]['productRetailPrice'];
		} catch(Exception $exception) {
			throw $exception;
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
			'sku',
			[
				'label' => esc_html__( 'SKU', 'elementor-idosell-retail-price-tag' ),
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
		?><p><?php echo number_format($this->getExternalData($this->get_settings('sku')), 2, ',', ' '); ?> PLN</p><?php
	}

}
