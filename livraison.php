<?php
class wpcb_livraison {
	var $internal_name, $name;
	var $services = array();
	var $settings;
	var $base_country;
	var $base_zipcode;
	
	//  Constructor
	function wpcb_livraison () {
		$this->internal_name = "wpcb_livraison";
		$this->name = 'Livraison (WPCB)';
		$this->is_external = true;
		$this->requires_weight = true;
		$this->needs_zipcode = true;
		$this->debug = false; // change to true to log (to the PHP error log) the API URLs and responses for each active service
		
		// Attempt to load the existing settings
		$this->base_country = get_option('base_country');
		$this->base_zipcode = get_option('base_zipcode');
				
		return true;
	} // end constructor
	
	function getName() {return $this->name;}
	function getInternalName() {return $this->internal_name;}
	
	function getForm() {
		if ($this->base_country != 'FR') {return __('Ne fonctionne que pour un commerçant basé en France.', 'wpsc');}
		// base_zipcode should be given and equal to 5 (french zipcode)
		if (strlen($this->base_zipcode) != 5) {	return __('Entrer votre code postal plus haut sur cette page.', 'wpsc');}
		// Load the values :		// Create the admin form
		$output='<a href="'.admin_url( 'plugins.php?page=wpcb&tab=livraison').'">Cliquez ici pour les réglages</a>';
		return $output;
	} // End of getForm function
	
	function submit_form() {return true;} // End of submit_form function
	
	function getQuote() {
		global $wpdb, $wpsc_cart;
		if ($this->base_country != 'FR' || strlen($this->base_zipcode) != 5 || !count($wpsc_cart->cart_items)) return;
		$dest = $_SESSION['wpsc_delivery_country'];
		$destzipcode = '';
		if(isset($_POST['zipcode'])) {
			$destzipcode = $_POST['zipcode'];      
			$_SESSION['wpsc_zipcode'] = $_POST['zipcode'];
		} 
		else if(isset($_SESSION['wpsc_zipcode'])) {
			$destzipcode = $_SESSION['wpsc_zipcode'];
		}
		//echo '*****'.$dest;
		if ($dest == 'FR' && strlen($destzipcode) != 5) {return array();}

		/*
		3 possible scenarios:
		1. Cart consists of only item(s) that have "disregard shipping" ticked.
		In this case, WPEC doesn't mention shipping at all during checkout, and this shipping module probably won't be executed at all.
		Just in case it does get queried, we should still override the quoted price(s) to $0.00 so the customer is able to get free shipping.
		2. Cart consists of only item(s) where "disregard shipping" isn't ticked (ie. all item(s) attract shipping charges).
		In this case, we should query the quote as per normal.
		3. Cart consists of one or more "disregard shipping" product(s), and one or more other products that attract shipping charges.
		In this case, we should query the quote, only taking into account the product(s) that attract shipping charges.
		Products with "disregard shipping" ticked shouldn't have their weight or dimensions included in the quote.
		*/
		

		// Weight is in grams
		$weight = wpsc_convert_weight($wpsc_cart->calculate_total_weight(true), 'pound', 'gram');
		// Calculate the total cart dimensions by adding the volume of each product then calculating the cubed root
		$volume = 0;
		// Total number of item(s) in the cart
		$numItems = count($wpsc_cart->cart_items);

		if ($numItems == 0) {
		    // The customer's cart is empty. This probably shouldn't occur, but just in case!
		    return array();
		}

		// Total number of item(s) that don't attract shipping charges.
		$numItemsWithDisregardShippingTicked = 0;

		foreach($wpsc_cart->cart_items as $cart_item) {
			if ( !$cart_item->uses_shipping ) {
			    // The "Disregard Shipping for this product" option is ticked for this item.
			    // Don't include it in the shipping quote.
			    $numItemsWithDisregardShippingTicked++;
			    continue;
			}

			// If we are here then this item attracts shipping charges.
			$meta = get_product_meta($cart_item->product_id,'product_metadata',true);
			$meta = $meta['dimensions'];

			if ($meta && is_array($meta)) {
				$productVolume = 1;
				foreach (array('width','height','length') as $dimension) {
					// Cubi square of the dimension to get the volume of the box it will be squared later
					switch ($meta["{$dimension}_unit"]) {
						// we need the units in mm
						case 'cm':
							// convert from cm to mm
							$productVolume = $productVolume * (floatval($meta[$dimension]) * 10);
							break;
						case 'meter':
							// convert from m to mm
							$productVolume = $productVolume * (floatval($meta[$dimension]) * 1000);
							break;
						case 'in':
							// convert from in to mm
							$productVolume = $productVolume * (floatval($meta[$dimension]) * 25.4);
							break;
					}
				}
				$volume += floatval($productVolume);
			}
		}
		// Calculate the cubic root of the total volume, rounding up
		$cuberoot = ceil(pow($volume, 1 / 3));
		
		// Use default dimensions of 100mm if the volume is zero
		$height=100; // Mettre dans les options, todo
		$width=100;
		$length=100;
		
		if ($cuberoot > 0) {
		    $height = $width = $length = $cuberoot;
		}

		if ($length < 100) $length = 100;
		if ($width < 100) $width = 100;

		$shippingPriceNeedsToBeZero = false;
		
		if ($numItemsWithDisregardShippingTicked == $numItems) {
		    // The cart consists of entirely "disregard shipping" products, so the shipping quote(s) should be $0.00
		    // Set the weight to 1 gram so that we can obtain valid Australia Post quotes (which we will then ignore the quoted price of)
		    $weight = 1;
		    $shippingPriceNeedsToBeZero = true;
		}
				
		//API :
		$wpcb_livraison_options=get_option('wpcb_livraison');
		$post_data['wpcb_livraison_options']=$wpcb_livraison_options; //peut-etre faut il les serializer ?
		 $wpcb_general = get_option( 'wpcb_general' );
		$post_data['apiKey']=$wpcb_general['apiKey'];
		$post_data['emailapiKey']=$wpcb_general['emailapiKey'];
		$response=wp_remote_post('http://wpcb.fr/api/wpcb/valid.php',array('body' =>$post_data));
		$valid=unserialize($response['body']);
		if ($valid[0]){
			//Cle API valid, on peut aller chercher des tarifs speciaux // Pas necessaire car déjà vérifier après... à réfléchir
			
			// Ajouter ici les services en fonction de l'admin :
			$services=array();
			if ($wpcb_livraison_options['ENLEVEMENT']){$services[]='ENLEVEMENT';}
			if ($wpcb_livraison_options['COLIS']){$services[]='COLIS';}
			if ($wpcb_livraison_options['CHRONOPOST']){$services[]='CHRONOPOST';}
			if ($wpcb_livraison_options['ENVELOPPEDOCUMENT']){$services[]='ENVELOPPEDOCUMENT';}
			if ($wpcb_livraison_options['ENVELOPPEDOCUMENTUE']){$services[]='ENVELOPPEDOCUMENTUE';}
			if ($wpcb_livraison_options['ENVELOPPEDOCUMENTOMRDM']){$services[]='ENVELOPPEDOCUMENTOMRDM';}
	//		echo '***';
	//		print_r($services);
	//		echo '///';
			$post_data['services']=$services;
			$post_data['Weight']=$weight;
			$post_data['Country']=$dest;
			$response=wp_remote_post('http://wpcb.fr/api/wpcb/livraison/getQuote.php',array('body' =>$post_data)); // Validation de la clé a l'intérieur
//			echo '£££'.$response['body'].'£££';
			$response=unserialize($response['body']);
//			echo '%%%';
//			print_r($response);
//			echo '%%%';
			if ($response['valid']){
				foreach ($services as $dummy=>$service){	
//					echo '<<<'.$service.'^^^';
					$name=$service.'_name';
					$methods[$service]['name']=$wpcb_livraison_options[$name]; // A mettre dans les options ex : LETTREMAX_name
					$methods[$service]['charge']=$response[$service]['charge']; // Ne coute rien
					$methods[$service]['err_msg']=$response[$service]['err_msg'];
				}
			}
			else {	// non valid apikey
			//echo 'non valid key';
			//print_r($response);
			}
		}
		else{//non valid api Key
		}
		//print_r($methods);
		// Allow another WordPress plugin to override the quoted method(s)/amount(s)
		$methods = apply_filters('wpcb_livraison_methods', $methods, $this->base_zipcode, $destzipcode, $dest, $weight);
		
		$quotedMethods = array();
		
		// Debug :
		//$text = sprintf('Poids : %1$d grammes',$params['Weight']);
		//$quotedMethods[$text] = 1;
		
		//echo '****';
		//print_r($methods);
		//echo '****';
		if ($methods){
		foreach ($methods as $key => $data) {
			// Only include methods with an OK response
			if ($data['err_msg'] == 'OK'){
			// Only include methods that are checked in the admin :
			//if (!$this->settings['services'][$code]) continue;
			$text = $data['name'];
			$quotedMethods[$text] = $data['charge'];
			}
		}
		}
		//print_r($quotedMethods);
		return $quotedMethods;
	} // End of getQuote function
	function get_item_shipping() {} // don't delete
}

function wpcb_livraison_setup() {
	global $wpsc_shipping_modules;
	$wpcb_livraison = new wpcb_livraison();
	$wpsc_shipping_modules[$wpcb_livraison->getInternalName()] = $wpcb_livraison;
}

add_action('plugins_loaded','wpcb_livraison_setup');
?>