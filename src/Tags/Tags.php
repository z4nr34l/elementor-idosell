***REMOVED***

namespace ElementorIdosell\Tags;

if ( ! defined( 'ABSPATH' ) ) ***REMOVED***
	exit; // Exit if accessed directly.
***REMOVED***

class Tags ***REMOVED***

	function register_new_dynamic_tag_group( $dynamic_tags_manager ) ***REMOVED***

		$dynamic_tags_manager->register_group(
			'idosell',
			[
				'title' => esc_html__( 'IdoSell', 'elementor-idosell' )
			]
		);

***REMOVED***

	function register_dynamic_tags( $dynamic_tags_manager ) ***REMOVED***
		$dynamic_tags_manager->register( new RetailPrice );
***REMOVED***

***REMOVED*** ***REMOVED***
		add_action( 'elementor/dynamic_tags/register', [$this, 'register_new_dynamic_tag_group'] );
		add_action( 'elementor/dynamic_tags/register', [$this, 'register_dynamic_tags'] );
***REMOVED***

***REMOVED***
