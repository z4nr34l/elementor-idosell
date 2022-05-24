***REMOVED***
if ( !class_exists('Parsedown', false) ) ***REMOVED***
	//Load the Parsedown version that's compatible with the current PHP version.
	if ( version_compare(PHP_VERSION, '5.3.0', '>=') ) ***REMOVED***
		require __DIR__ . '/ParsedownModern.php';
***REMOVED*** else ***REMOVED***
		require __DIR__ . '/ParsedownLegacy.php';
***REMOVED***
***REMOVED***
