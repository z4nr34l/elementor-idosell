<?php

namespace ElementorIdosell;

use ElementorIdosell\Tags\Tags;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Application
{
	public function registerActions()
  {
  }

  public function run()
  {
	  $settings = new Settings();
		$tags = new Tags();
		$importer = new Importer();
  }
}
