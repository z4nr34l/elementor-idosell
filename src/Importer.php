<?php

namespace ElementorIdosell;

use Exception;

class Importer
{

	private $options;
	public string $table_name = "idosell_products";

	public function __construct()
	{
		global $wpdb;

		$this->options = get_option('idosell');

		$sql = "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix.$this->table_name . "` (
                            `id` int(10) NOT NULL,
                            `code_producer` VARCHAR(25) NULL,
                            `price` VARCHAR(45) NULL,
                            PRIMARY KEY (`id`)
                            );" ;
		$wpdb->query($sql);

		if(isset($_GET['idosell_import'])){
			add_action( 'wp_loaded', [$this, 'import'] );
		}

		if(!wp_next_scheduled('idosell_import_hook')) {
			wp_schedule_event(time(), 'daily', 'idosell_import_hook');
		}
		add_action('idosell_import_hook',[$this, 'import']);
	}

	public function import() {
		global $wpdb;

    if($this->options['xml_url']) {
	    $args = [
		    'method' => 'GET',
        'timeout' => '30000',
		    'sslverify' => false
	    ];
	    $rows_affected = 0;

	    $response = wp_remote_request($this->options['xml_url'], $args);

	    if(is_wp_error($response)) {
		    throw new Exception('Request failed. ' . serialize($response->get_error_messages()));
	    }

      $collection = simplexml_load_string(wp_remote_retrieve_body($response));

      foreach($collection->products->product as $product) {
        $sql = "INSERT INTO " . $wpdb->prefix.$this->table_name . " (id, code_producer, price) VALUES (%d, %s, %f) ON DUPLICATE KEY UPDATE price = %f";
        $upsert = $wpdb->prepare($sql, get_object_vars($product)['@attributes']['id'], get_object_vars($product->sizes->size)['@attributes']['code_producer'] ?: "", get_object_vars($product->srp)['@attributes']['gross'] ?: null, get_object_vars($product->srp)['@attributes']['gross'] ?: null);
        $rows_affected = $wpdb->query($upsert);
      }

	    wp_redirect(admin_url().'options-general.php?page=idosell&sync='.$rows_affected);
	    exit;
    } else {
	    throw new Exception('Request failed. XML URL not set in settings.');
    }

	}

}
