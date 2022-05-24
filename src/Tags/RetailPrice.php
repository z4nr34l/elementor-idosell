***REMOVED***

namespace ElementorIdosell\Tags;

***REMOVED***

if ( ! defined( 'ABSPATH' ) ) ***REMOVED***
	exit; // Exit if accessed directly.
***REMOVED***

/**
 * Elementor Dynamic Tag - Random Number
 *
 * Elementor dynamic tag that returns a random number.
 *
 * @since 1.0.0
 */
class RetailPrice extends \Elementor\Core\DynamicTags\Tag ***REMOVED***

	/**
	 * Plugin options from settings
	 * @var
	 */
***REMOVED***

	/**
	 * Class constructor
	 * @param array $data
	 */
	public function __construct( array $data = [] ) ***REMOVED***
		parent::__construct( $data );
***REMOVED***
***REMOVED***

	/**
	 * Get dynamic tag name.
	 *
	 * Retrieve the name of the random number tag.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return string Dynamic tag name.
	 */
	public function get_name() ***REMOVED***
		return 'retail-price';
***REMOVED***

	/**
	 * Get dynamic tag title.
	 *
	 * Returns the title of the random number tag.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return string Dynamic tag title.
	 */
	public function get_title() ***REMOVED***
		return esc_html__( 'Retail price', 'elementor-idosell-retail-price-tag' );
***REMOVED***

	/**
	 * Get dynamic tag groups.
	 *
	 * Retrieve the list of groups the random number tag belongs to.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return array Dynamic tag groups.
	 */
	public function get_group() ***REMOVED***
		return [ 'idosell' ];
***REMOVED***

	/**
	 * Get dynamic tag categories.
	 *
	 * Retrieve the list of categories the random number tag belongs to.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return array Dynamic tag categories.
	 */
	public function get_categories() ***REMOVED***
		return [
			\Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY,
			\Elementor\Modules\DynamicTags\Module::NUMBER_CATEGORY
		];
***REMOVED***

	protected function getExternalData($SKU) ***REMOVED***
		try ***REMOVED***
***REMOVED***

***REMOVED***
***REMOVED***
***REMOVED***
***REMOVED***
***REMOVED***
			$request['params']['returnProducts'] = "active";
			$request['params']['productParams'] = array();
			$request['params']['productParams'][0] = array();
			$request['params']['productParams'][0]['productCode'] = $SKU;
***REMOVED***
			$request['params']['returnElements'][0] = "retail_price";

***REMOVED***
***REMOVED***
***REMOVED***
***REMOVED***
***REMOVED***

***REMOVED***
***REMOVED***
***REMOVED***
***REMOVED***
***REMOVED***
***REMOVED***
***REMOVED***
***REMOVED***
***REMOVED***
***REMOVED***
***REMOVED***

***REMOVED***
***REMOVED***
***REMOVED***
***REMOVED***
			$json = json_decode($body, true);

			return $json['results'][0]['productRetailPrice'];
***REMOVED*** catch(Exception $exception) ***REMOVED***
***REMOVED***
***REMOVED***
***REMOVED***

	/**
	 * Register dynamic tag controls.
	 *
	 * Add input fields to allow the user to customize the ACF average tag settings.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @return void
	 */
	protected function register_controls() ***REMOVED***
		$this->add_control(
			'sku',
			[
				'label' => esc_html__( 'SKU', 'elementor-idosell-retail-price-tag' ),
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
***REMOVED***

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
	public function render() ***REMOVED***
		?><p>***REMOVED*** echo number_format($this->getExternalData($this->get_settings('sku')), 2, ',', ' '); ?> ***REMOVED*** echo $this->get_settings('currency') ?></p>***REMOVED***
***REMOVED***

***REMOVED***
