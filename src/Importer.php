<?php

namespace ElementorIdosell;

use Exception;

class Importer
{

	/**
	 * http://rcpro.iai-shop.com/api/?gate=products/get/170/json
	 * MktAPI
	 * j6#wNlqhN&BB6llW@4PQ
	 */
	private $options;
	public string $table_name = "idosell_products";

	public function __construct()
	{
		global $wpdb;

		$this->options = get_option('idosell');

		$sql = "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix.$this->table_name . "` (
                            `id` int(10) NOT NULL,
                            `code_producer` VARCHAR(25) NULL,
                            `product_sizecode` VARCHAR(25) NULL,
                            `sku` VARCHAR(25) NULL,
                            `price` VARCHAR(45) NULL,
                            PRIMARY KEY (`id`)
                            );" ;
		$wpdb->query($sql);

		if(isset($_GET['idosell_import'])){
			add_action( 'wp_loaded', [$this, 'import'] );
		}

		if(isset($_GET['idosell_async_import']) && isset($_GET['idosell_async_page'])){
			$async_page = $_GET['idosell_async_page'];
			add_action( /**
			 * @throws Exception
			 */ 'wp_loaded', function() use ($async_page) {
				$this->import_async($async_page);
			});
		}

		if(!wp_next_scheduled('idosell_import_hook')) {
			wp_schedule_event(time(), 'daily', 'idosell_import_hook');
		}
		add_action('idosell_import_hook',[$this, 'update']);
	}

	private function getAPIData($page) {
		try {
			$address = $this->options['gateway_url'];

			$request = array();
			$request['authenticate'] = array();
			$request['authenticate']['userLogin'] = $this->options['login'];
			$request['authenticate']['authenticateKey'] = sha1(date('Ymd') . sha1($this->options['password']));
			$request['params'] = array();
			$request['params']['resultsPage'] = $page;
			$request['params']['returnElements'] = array();
			$request['params']['returnElements'][0] = "code";
			$request['params']['returnElements'][1] = "sizes_attributes";
			$request['params']['returnElements'][2] = "retail_price";

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

			return json_decode($body, true);
		} catch(Exception $exception) {
			error_log($exception);
			throw $exception;
		}
	}

	public function import() {
		global $wpdb;

		try {
			$data       = $this->getAPIData(0);
			$async_urls = [];
			$products   = $data['results'];

			foreach($products as $product) {
				$sql    = "INSERT INTO " . $wpdb->prefix.$this->table_name . " (id, code_producer, product_sizecode, sku, price) VALUES (%d, %s, %s, %s, %f) ON DUPLICATE KEY UPDATE price = %f";
				$upsert = $wpdb->prepare($sql, $product['productId'], $product['productSizesAttributes'][0]['productSizeCodeExternal'] ?: "", $product['productSizesAttributes'][0]['productSizeCodeProducer'] ?: "", $product['productDisplayedCode'] ?: "", $product['productRetailPrice'] ?: null, $product['productRetailPrice'] ?: null);
				$wpdb->query($upsert);
			}
			
			for ($i = 1; $i <= $data['resultsNumberPage']; $i++) {
				$async_urls[] = admin_url()."/options-general.php?page=idosell&idosell_async_import=1&idosell_async_page=" . $i;
			}

			echo "<style>html,body {margin: 0; padding: 0; position: relative;}</style><div style='width: 100vw; height: 100vh; display: flex; flex-direction: column; align-content: center; justify-content: center;'><p style='font-size: 21px; text-align: center; margin: 0;'>Sync progress:</p><p style='text-align: center; margin: 0; font-size: 42px; font-weight: 800;' id='progress-percentage'>0 %</p><p style='font-size: 14px; margin: 25px 0 0 0; text-align: center;'>(don't close the window, will close itself when finished)</p></div><script type='text/javascript'>let initData = { currentPage: 0, maxPages: parseInt('".$data['resultsNumberPage']."'), apiEndpoints: ".json_encode($async_urls)." };  function setProgress() { document.getElementById('progress-percentage').innerText = ((initData.currentPage / initData.maxPages) * 100).toFixed(2) + ' %'; } setProgress(); let xhr = [], i; window.onload(function() { initData.apiEndpoints.forEach((endpoint, i) => { xhr[i] = new XMLHttpRequest(); xhr[i].open('GET', new URL(endpoint), false); xhr[i].onreadystatechange = function() {if(xhr[i].readyState === 4 && xhr[i].status === 200) { initData.currentPage++; if(initData.currentPage === initData.maxPages){window.close('','_parent','');} else {setProgress();} }}; xhr[i].send(); }); });</script>";
    } catch(Exception $exception) {
			error_log($exception);
			wp_redirect(admin_url().'options-general.php?page=idosell&sync=0');
		}

		exit;
	}

	public function import_async($page) {
		global $wpdb;

		try {
			$data     = $this->getAPIData($page);
			$products = $data['results'];

			foreach($products as $product) {
				$sql    = "INSERT INTO " . $wpdb->prefix.$this->table_name . " (id, code_producer, product_sizecode, sku, price) VALUES (%d, %s, %s, %s, %f) ON DUPLICATE KEY UPDATE price = %f";
				$upsert = $wpdb->prepare($sql, $product['productId'], $product['productSizesAttributes'][0]['productSizeCodeExternal'] ?: "", $product['productSizesAttributes'][0]['productSizeCodeProducer'] ?: "", $product['productDisplayedCode'] ?: "", $product['productRetailPrice'] ?: null, $product['productRetailPrice'] ?: null);
				$wpdb->query($upsert);
			}

			echo "ok";
			exit;
    } catch(Exception $exception) {
			error_log($exception);
			throw $exception;
		}
	}

	public function update() {
		global $wpdb;

		if ( $this->options['xml_url'] ) {
			$args          = [
				'method'    => 'GET',
				'timeout'   => '30000',
				'sslverify' => false
			];
			$rows_affected = 0;

			$response = wp_remote_request( $this->options['xml_url'], $args );

			if ( is_wp_error( $response ) ) {
				throw new Exception( 'Request failed. ' . serialize( $response->get_error_messages() ) );
			}

			$collection = simplexml_load_string( wp_remote_retrieve_body( $response ) );

			foreach ( $collection->products->product as $product ) {
				$sql           = "INSERT INTO " . $wpdb->prefix . $this->table_name . " (id, price) VALUES (%d, %f) ON DUPLICATE KEY UPDATE price = %f";
				$upsert        = $wpdb->prepare( $sql, get_object_vars( $product )['@attributes']['id'], get_object_vars( $product->srp )['@attributes']['gross'] ?: null, get_object_vars( $product->srp )['@attributes']['gross'] ?: null );
				$rows_affected = $wpdb->query( $upsert );
			}

			wp_redirect( admin_url() . 'options-general.php?page=idosell&sync=' . $rows_affected );
			exit;
		} else {
			throw new Exception( 'Request failed. XML URL not set in settings.' );
		}
	}

}
