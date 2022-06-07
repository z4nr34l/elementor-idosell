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

***REMOVED***

	/**
	 * Class constructor
	 * @param array $data
	 */
	public function __construct( array $data = [] ) ***REMOVED***
		parent::__construct( $data );
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

	protected function getData($input) ***REMOVED***
    global $wpdb;

    if(isset($input)) ***REMOVED***
      $cache = wp_cache_get($input, 'idosell_prices');
	    if($cache) ***REMOVED***
		    return $cache;
	    ***REMOVED*** else ***REMOVED***
		    try ***REMOVED***
			    $sql = "SELECT `price` FROM " . $wpdb->prefix.$this->table_name . " WHERE `id` LIKE %s OR `code_producer` LIKE %s";
			    $sql = $wpdb->prepare($sql, $input, $input);
          $result = $wpdb->get_results($sql)[0]->price;
          wp_cache_set($input, $result, 'idosell_prices', 86400);
			    return $result;
		    ***REMOVED*** catch(Exception $exception) ***REMOVED***
			    throw $exception;
		    ***REMOVED***
	    ***REMOVED***
    ***REMOVED*** else ***REMOVED***
      return null;
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
		?><p>***REMOVED*** echo $this->get_settings('prefix') ?>***REMOVED*** echo number_format((float) $this->getData($this->get_settings('input')), 2, ',', ' '); ?>***REMOVED*** echo $this->get_settings('currency') ?></p>***REMOVED***
***REMOVED***

***REMOVED***
