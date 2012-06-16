<?php

class mondialrelay {
	var $internal_name, $name;
	var $services = array();
	var $settings;
	var $base_country;
	var $base_zipcode;
	
	//  Constructor
	function mondialrelay () {
		$this->internal_name = "wpsc_mondialrelay";
		$this->name = 'Mondial Relay (WPCB)';
		$this->is_external = true;
		$this->requires_weight = true;
		$this->needs_zipcode = true;
		$this->debug = false; // change to true to log (to the PHP error log) the API URLs and responses for each active service
		// Initialise the list of available postage services
		$this->services['MONDIALRELAY'] = __('Mondial Relay', 'wpsc');
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
		global $wpdb, $wpsc_cart,$CodeEnseigne,$CodeMarque,$Pays,$ClePrivee;
		$wpcb_livraison_options=get_option('wpcb_livraison');

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
		if ($dest == 'FR' && strlen($destzipcode) != 5) {return array();}

		
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

		// todo : calculer la longueur déroulée.
		// todo : forcer la taille par un custom field dans le produit
		
		$shippingPriceNeedsToBeZero = false;
		
		if ($numItemsWithDisregardShippingTicked == $numItems) {
		    // The cart consists of entirely "disregard shipping" products, so the shipping quote(s) should be $0.00
		    // Set the weight to 1 gram so that we can obtain valid Australia Post quotes (which we will then ignore the quoted price of)
		    $weight = 1;
		    $shippingPriceNeedsToBeZero = true;
		}
		
		$params = array(
		    'Pickup_Postcode' => $this->base_zipcode
		    , 'Destination_Postcode' => $destzipcode
		    , 'Quantity' => 1
		    , 'Weight' => $weight
		    , 'Height' => $height
		    , 'Width' => $width
		    , 'Length' => $length
		    , 'Country' => $dest
		);

		// Tableaux des destinations COLIS :
		$dest_colis_fr=array('FR');
		// to do ajouter les tableaux pour les autres pays
		
		// Param API
		if ($wpcb_livraison_options['mr_ComptePro']){
			$ComptePro=true;
			$CodeEnseigne=$wpcb_livraison_options['mr_CodeEnseigne'];//'BDTESTMR';
			$ClePrivee=$wpcb_livraison_options['mr_ClePrivee'];//'TesT_MondiaL_RelaY';
			$CodeMarque=$wpcb_livraison_options['mr_CodeMarque'];//'11';
			// to do 
			$Tarif['500']['HT']=3.60;$Tarif['500']['TTC']=$Tarif['500']['HT']*1.196;$PoidsMax[0]=500;//en gramme
			$Tarif['1000']['HT']=4.10;$Tarif['1000']['TTC']=$Tarif['1000']['HT']*1.196;$PoidsMax[1]=1000;//en gramme
			$Tarif['2000']['HT']=4.72;$Tarif['2000']['TTC']=$Tarif['2000']['HT']*1.196;$PoidsMax[2]=2000;//en gramme
			$Tarif['3000']['HT']=5.31;$Tarif['3000']['TTC']=$Tarif['3000']['HT']*1.196;$PoidsMax[3]=3000;//en gramme
			$Tarif['5000']['HT']=6.40;$Tarif['5000']['TTC']=$Tarif['5000']['HT']*1.196;$PoidsMax[4]=5000;//en gramme
			$Tarif['7000']['HT']=8.19;$Tarif['7000']['TTC']=$Tarif['7000']['HT']*1.196;$PoidsMax[5]=7000;//en gramme
			$Tarif['10000']['HT']=10.12;$Tarif['10000']['TTC']=$Tarif['10000']['HT']*1.196;$PoidsMax[6]=10000;//en gramme
			$Tarif['15000']['HT']=12.12;$Tarif['15000']['TTC']=$Tarif['15000']['HT']*1.196;$PoidsMax[7]=15000;//en gramme
			$Tarif['20000']['HT']=15.01;$Tarif['20000']['TTC']=$Tarif['20000']['HT']*1.196;$PoidsMax[8]=20000;//en gramme
			//print_r($Tarif);
		}
		else{
			$ComptePro=false;
			$Tarif['500']=4.30;$PoidsMax['500']=500;//en gramme
			$Tarif['1000']=4.90;$PoidsMax['1000']=1000;//en gramme
			$Tarif['2000']=5.65;$PoidsMax['2000']=2000;//en gramme
			$Tarif['3000']=6.35;$PoidsMax['3000']=3000;//en gramme
			$Tarif['5000']=7.65;$PoidsMax['5000']=5000;//en gramme
			$Tarif['7000']=9.80;$PoidsMax['7000']=7000;//en gramme
			$Tarif['10000']=12.10;$PoidsMax['10000']=10000;//en gramme
			$Tarif['15000']=14.50;$PoidsMax['15000']=15000;//en gramme
			$Tarif['20000']=17.95;$PoidsMax['20000']=20000;//en gramme
		}
			
		// Adresse
		$CP=$destzipcode;
		$Pays=$params['Country'];
		$Ville='';
		
		// colis :
		// Utilisation de l'api "home-made" (uniquement a destination de france métropolitaine)
		if (in_array($params['Country'],$dest_colis_fr)){
			// Tarifs à  destination de la france :
			//Table tarifaire colis France Métropolitaine et Monaco
			// Check if custom field before
			if ($ComptePro){
				require_once('lib/nusoap.php');
				$client = new nusoap_client("http://www.mondialrelay.fr/WebService/Web_Services.asmx?WSDL", true);
				$client->soap_defencoding = 'utf-8';
				$Action='24R'; // todo
				if($params['Weight']>=0 && $params['Weight']<=500){$MaxWeight=500;}
				elseif ($params['Weight']>500 && $params['Weight']<=1000){$MaxWeight=1000;} 
				elseif ($params['Weight']>1000 && $params['Weight']<=2000){$MaxWeight=2000;}
				elseif ($params['Weight']>2000 && $params['Weight']<=3000){$MaxWeight=3000;} 				
				elseif ($params['Weight']>3000 && $params['Weight']<=5000){$MaxWeight=5000;}
				elseif ($params['Weight']>5000 && $params['Weight']<=7000){$MaxWeight=7000;}
				elseif ($params['Weight']>7000 && $params['Weight']<=10000){$MaxWeight=10000;}
				elseif ($params['Weight']>10000 && $params['Weight']<=15000){$MaxWeight=15000;}
				elseif ($params['Weight']>15000 && $params['Weight']<=20000){$MaxWeight=20000;} //todo assurance
				else {$MaxWeight=false;}
				if ($MaxWeight){
					// API Mondial Relay :
					// Produits :
					$Taille=''; //Facultatif pour l'api
					$Poids=''; //Facultatif pour l'api
					$paramsAPI = array('Enseigne'=> $CodeEnseigne,'Pays'=> $Pays,'Ville'=> $Ville,'CP'=> $CP,'Taille'=> $Taille,'Poids'=> $Poids,	
						'Action'=> $Action,'Security'=> strtoupper(md5($CodeEnseigne.$Pays.$Ville.$CP.$Taille.$Poids.$Action.$ClePrivee)));
					$result = $client->call('WSI2_RecherchePointRelais', $paramsAPI, 'http://www.mondialrelay.fr/webservice/', 'http://www.mondialrelay.fr/webservice/WSI2_RecherchePointRelais');
					if ($client->fault){
						$methods['MONDIALRELAY']['err_msg']='Fault (Expect - The request contains an invalid SOAP body)';
					}
					else{
						$err = $client->getError();
						if ($err){
							$methods[$service_mondialrelay]['err_msg']='Fault (Expect - The request contains an invalid SOAP body) :'. $err;
						}
						else{
							$PRs = array('PR01','PR02','PR03','PR04','PR05'); // add here if you want to show more
							foreach ($PRs as $PR) {
								$Num=$result['WSI2_RecherchePointRelaisResult'][$PR]['Num'];
								$LgAdr1=$result['WSI2_RecherchePointRelaisResult'][$PR]['LgAdr1'];
								$LgAdr3=$result['WSI2_RecherchePointRelaisResult'][$PR]['LgAdr3'];
								$CPRelais=$result['WSI2_RecherchePointRelaisResult'][$PR]['CP'];
								$VilleRelais=$result['WSI2_RecherchePointRelaisResult'][$PR]['Ville'];
								
								$MapLink='http://www.mondialrelay.com/public/permanent/details_relais.aspx?ens='.$CodeEnseigne.$CodeMarque.'&num='.$Num.'&pays='.$Pays.'&crc='.strtoupper(md5('<'.$CodeEnseigne.$CodeMarque.'>'.$Num.$Pays.'<'.$ClePrivee.'>'));
								$service_mondialrelay='MONDIAL_RELAY_'.$Num; // On ajoute ici le numéro pour differencier des diffentes méthodes de livraison
								$methods[$service_mondialrelay]['name']='MONDIAL RELAY (Relais : <a href='.$MapLink.' target=%22_blank%22>'.$Num.'</a> '.$LgAdr1.', '.$LgAdr3.', '.$CPRelais.' '.$VilleRelais.')';
								$methods[$service_mondialrelay]['err_msg']='OK';
								$methods[$service_mondialrelay]['charge']=$Tarif[$MaxWeight]['TTC']; // TTC todo add taxe
							}			
						}
					}
				}
				else{
					$methods[$service_mondialrelay]['err_msg']='Poids non trouvé';
				}
			}
			else {
				// Compte Particulier France
				$service_mondialrelay='MONDIAL_RELAY_0';
				if($params['Weight']>=0 && $params['Weight']<=1000){$Type='XS';}
				elseif ($params['Weight']>1000 && $params['Weight']<=3000){$Type='S';} 
				elseif ($params['Weight']>3000 && $params['Weight']<=5000){$Type='M';}
				elseif ($params['Weight']>5000 && $params['Weight']<=10000){$Type='L';} 				
				elseif ($params['Weight']>10000 && $params['Weight']<=20000){$Type='XL';}
				else {$Type=false;}
				if ($Type){
					$methods[$service_mondialrelay]['name']='MONDIAL RELAY (Nous choisirons le Relais de livraison au plus proche de chez vous !)';
					$methods[$service_mondialrelay]['err_msg']='OK';
					$methods[$service_mondialrelay]['charge']=$Tarif[$Type];
				}
				else {
					$methods[$service_mondialrelay]['err_msg']='Poids inconnu';
				}
				
			} //Fin du compte particulier France
		} // Fin des tarifs France Monaco
		else { // Tarif internationaux //todo
			$methods[$service_mondialrelay]['name']='Mondial Relay International'; // Je ne connais pas les tarifs
			$methods[$service_mondialrelay]['err_msg']='Todo';
		} // Fin de international
		// **********************************************************************************

		//print_r($methods);
		// Allow another WordPress plugin to override the quoted method(s)/amount(s)
		//$methods = apply_filters('wpsc_mondialrelay_methods', $methods, $this->base_zipcode, $destzipcode, $dest, $weight);
		$quotedMethods = array();
		foreach ($methods as $code => $data) {
			// Only include methods with an OK response
			if ($data['err_msg'] != 'OK') continue;
			$text = $data['name'];
			$quotedMethods[$text] = $data['charge'];
		}
		return $quotedMethods;
	} // End of getQuote function
	function get_item_shipping() {} // don't delete
}

function mondialrelay_setup() {
	global $wpsc_shipping_modules;
	$mondialrelay = new mondialrelay();
	$wpsc_shipping_modules[$mondialrelay->getInternalName()] = $mondialrelay;
}

add_action('plugins_loaded', 'mondialrelay_setup');


// Essai Hook

add_filter( "wpsc_transaction_result_message", "mondialrelais_modify_message" );
function mondialrelais_modify_message($message){
global $purchase_log,$wpdb,$user_ID,$wpsc_cart;
    if ($purchase_log['shipping_method']=='wpsc_mondialrelay'){
   		$message ='\n';
		$message.='Option de livraison : Mondial Relais (Vous receverez très prochainement le numéro de suivi pour suivre votre colis)\n';
		$wpcb_livraison_options=get_option('wpcb_livraison');
		if ($wpcb_livraison_options['mr_ComptePro']){
			// Compte Pro
			// Extrait le num de relais du purchase log
			$pattern='/num=([0-9]{6})&/';
			preg_match($pattern, $purchase_log['shipping_option'], $matches);
			$Num=$matches[1];
			$CodeEnseigne=$wpcb_livraison_options['mr_CodeEnseigne'];//'BDTESTMR';
			$ClePrivee=$wpcb_livraison_options['mr_ClePrivee'];//'TesT_MondiaL_RelaY';
			$CodeMarque=$wpcb_livraison_options['mr_CodeMarque'];//'11';
			$Pays='FR'; //todo
			$MapLink='http://www.mondialrelay.com/public/permanent/details_relais.aspx?ens='.$CodeEnseigne.$CodeMarque.'&num='.$Num.'&pays='.$Pays.'&crc='.strtoupper(md5('<'.$CodeEnseigne.$CodeMarque.'>'.$Num.$Pays.'<'.$ClePrivee.'>'));
			$message.='<a href='.$MapLink.' target=%22_blank%22>Le point relais choisi</a>\n';		
		}
		else{
			$message.='Nous vous communiquons très prochainement par email le point Mondial Relais ou vous pourrez retirer votre commande.\n';
		}
   }
   
   return $message;
}

add_filter( "wpsc_transaction_result_report", "mondialrelais_modify_report" );
function mondialrelais_modify_report($report){
	global $purchase_log,$wpdb,$user_ID,$wpsc_cart;
   
   
		$wpcb_livraison_options=get_option('wpcb_livraison');
   // On envoie au vendeur le lien pour imprimer l'étiquette :
   if ($wpcb_livraison_options['mr_ComptePro']){
			// Compte Pro
			// Extrait le num de relais du purchase log
			$pattern='/num=([0-9]{6})&/';
			preg_match($pattern, $purchase_log['shipping_option'], $matches);
			$Num=$matches[1];
			$CodeEnseigne=$wpcb_livraison_options['mr_CodeEnseigne'];//'BDTESTMR';
			$ClePrivee=$wpcb_livraison_options['mr_ClePrivee'];//'TesT_MondiaL_RelaY';
			$CodeMarque=$wpcb_livraison_options['mr_CodeMarque'];//'11';
			$Pays='FR'; //todo
			$MapLink='http://www.mondialrelay.com/public/permanent/details_relais.aspx?ens='.$CodeEnseigne.$CodeMarque.'&num='.$Num.'&pays='.$Pays.'&crc='.strtoupper(md5('<'.$CodeEnseigne.$CodeMarque.'>'.$Num.$Pays.'<'.$ClePrivee.'>'));
			$report.= '<a href='.$MapLink.' target=%22_blank%22>Le point relais choisi</a><br/>';
			
			// Création d'une étiquette :
			require_once('lib/nusoap.php');
			$client = new nusoap_client("http://www.mondialrelay.fr/WebService/Web_Services.asmx?WSDL", true);
			$client->soap_defencoding = 'utf-8';
			
			$ModeCol='REL'; // ?
			$ModeLiv='24R'; // ?
			$Expe_Langage='FR';
			$Expe_Ad1=$wpcb_livraison_options['mr_Expe_Ad1'];
			
			
			$Expe_Ad3=$wpcb_livraison_options['mr_Expe_Ad3'];
			$Expe_Ville=$wpcb_livraison_options['mr_Expe_Ville'];
			$Expe_CP=$wpcb_livraison_options['mr_Expe_CP'];
			$Expe_Pays='FR';
			$Expe_Tel1=$wpcb_livraison_options['mr_Expe_Tel1'];
			$Dest_Langage='FR';
			
			
			// Firstname
			$form_sql ="SELECT ".WPSC_TABLE_SUBMITED_FORM_DATA.".value AS value FROM " . WPSC_TABLE_SUBMITED_FORM_DATA."
			INNER JOIN ".WPSC_TABLE_CHECKOUT_FORMS." 
			ON ".WPSC_TABLE_SUBMITED_FORM_DATA.".form_id=".WPSC_TABLE_CHECKOUT_FORMS." .id
			WHERE ".WPSC_TABLE_CHECKOUT_FORMS.".unique_name='billingfirstname' AND ".WPSC_TABLE_SUBMITED_FORM_DATA.".log_id=".$purchase_log['id'];
			$form_data = $wpdb->get_results( $form_sql, ARRAY_A );
			$billingfirstname=$form_data[0]['value'];
			
			//Last Name :
			$form_sql ="SELECT ".WPSC_TABLE_SUBMITED_FORM_DATA.".value AS value FROM " . WPSC_TABLE_SUBMITED_FORM_DATA."
			INNER JOIN ".WPSC_TABLE_CHECKOUT_FORMS." 
			ON ".WPSC_TABLE_SUBMITED_FORM_DATA.".form_id=".WPSC_TABLE_CHECKOUT_FORMS." .id
			WHERE ".WPSC_TABLE_CHECKOUT_FORMS.".unique_name='billinglastname' AND ".WPSC_TABLE_SUBMITED_FORM_DATA.".log_id=".$purchase_log['id'];
			$form_data = $wpdb->get_results( $form_sql, ARRAY_A );
			$billinglastname=$form_data[0]['value'];
			
			$Dest_Ad1='M. '.$billinglastname.' '.$billingfirstname;
			// Adress :
			$form_sql ="SELECT ".WPSC_TABLE_SUBMITED_FORM_DATA.".value AS value FROM " . WPSC_TABLE_SUBMITED_FORM_DATA."
			INNER JOIN ".WPSC_TABLE_CHECKOUT_FORMS." 
			ON ".WPSC_TABLE_SUBMITED_FORM_DATA.".form_id=".WPSC_TABLE_CHECKOUT_FORMS." .id
			WHERE ".WPSC_TABLE_CHECKOUT_FORMS.".unique_name='billingaddress' AND ".WPSC_TABLE_SUBMITED_FORM_DATA.".log_id=".$purchase_log['id'];
			$form_data = $wpdb->get_results( $form_sql, ARRAY_A );
			$billingaddress=$form_data[0]['value'];
			
			$Dest_Ad3=$billingaddress;
			
			$form_sql ="SELECT ".WPSC_TABLE_SUBMITED_FORM_DATA.".value AS value FROM " . WPSC_TABLE_SUBMITED_FORM_DATA."
			INNER JOIN ".WPSC_TABLE_CHECKOUT_FORMS." 
			ON ".WPSC_TABLE_SUBMITED_FORM_DATA.".form_id=".WPSC_TABLE_CHECKOUT_FORMS." .id
			WHERE ".WPSC_TABLE_CHECKOUT_FORMS.".unique_name='billingcity' AND ".WPSC_TABLE_SUBMITED_FORM_DATA.".log_id=".$purchase_log['id'];
			$form_data = $wpdb->get_results( $form_sql, ARRAY_A );
			$billingcity=$form_data[0]['value'];
			
			$Dest_Ville=$billingcity;
			
			$form_sql ="SELECT ".WPSC_TABLE_SUBMITED_FORM_DATA.".value AS value FROM " . WPSC_TABLE_SUBMITED_FORM_DATA."
			INNER JOIN ".WPSC_TABLE_CHECKOUT_FORMS." 
			ON ".WPSC_TABLE_SUBMITED_FORM_DATA.".form_id=".WPSC_TABLE_CHECKOUT_FORMS." .id
			WHERE ".WPSC_TABLE_CHECKOUT_FORMS.".unique_name='billingpostcode' AND ".WPSC_TABLE_SUBMITED_FORM_DATA.".log_id=".$purchase_log['id'];
			$form_data = $wpdb->get_results( $form_sql, ARRAY_A );
			$billingpostcode=$form_data[0]['value'];
			
			$Dest_CP=$billingpostcode;
			
			$Dest_Pays='FR';
			//print_r($wpsc_cart);
			//echo $wpsc_cart->calculate_total_weight(true);
			$Poids = wpsc_convert_weight($wpsc_cart->calculate_total_weight(true), 'pound', 'gram'); // Si on recharge la page, le poids ne marche plus !
			//$Poids=50;
			$NbColis=1;
			$CRT_Valeur=0; // en centimes
			$COL_Rel_Pays='FR';
			$COL_Rel=$wpcb_livraison_options['mr_COL_Rel'];; // Le point Relais du vendeur;
			$LIV_Rel_Pays='FR';
			$LIV_Rel=$Num;
			$paramsAPI = array(
					'Enseigne'=> $CodeEnseigne,
					'ModeCol'=>$ModeCol,
					'ModeLiv'=>$ModeLiv,
					'Expe_Langage'=>$Expe_Langage,
					'Expe_Ad1'=>$Expe_Ad1,
					'Expe_Ad3'=>$Expe_Ad3,
					'Expe_Ville'=>$Expe_Ville,
					'Expe_CP'=>$Expe_CP,
					'Expe_Pays'=>$Expe_Pays,
					'Expe_Tel1'=>$Expe_Tel1,
					'Dest_Langage'=>$Dest_Langage,
					'Dest_Ad1'=>$Dest_Ad1,
					'Dest_Ad3'=>$Dest_Ad3,
					'Dest_Ville'=>$Dest_Ville,
					'Dest_CP'=>$Dest_CP,
					'Dest_Pays'=>$Dest_Pays,
					'Poids'=> $Poids,
					'NbColis'=>$NbColis,
					'CRT_Valeur'=>$CRT_Valeur,
					'COL_Rel_Pays'=>$COL_Rel_Pays,
					'COL_Rel'=>$COL_Rel,
					'LIV_Rel_Pays'=>$LIV_Rel_Pays,
					'LIV_Rel'=>$LIV_Rel,
					'Security'=> strtoupper(md5(
					$CodeEnseigne.$ModeCol.$ModeLiv.$Expe_Langage.$Expe_Ad1.$Expe_Ad3.$Expe_Ville.$Expe_CP.$Expe_Pays.$Expe_Tel1.$Dest_Langage.$Dest_Ad1.$Dest_Ad3.$Dest_Ville.$Dest_CP.$Dest_Pays.$Poids.$NbColis.$CRT_Valeur.$COL_Rel_Pays.$COL_Rel.$LIV_Rel_Pays.$LIV_Rel.$ClePrivee)));
				//print_r($paramsAPI );	
			$result = $client->call('WSI2_CreationEtiquette', $paramsAPI, 'http://www.mondialrelay.fr/webservice/', 'http://www.mondialrelay.fr/webservice/WSI2_CreationEtiquette');
			if ($client->fault){
				$report.= 'Fault (Expect - The request contains an invalid SOAP body)';
			}
			else{
				$err = $client->getError();
				if ($err){
					$report.= 'Fault (Expect - The request contains an invalid SOAP body) :'. $err;
				}
				else{
				
					if ($result['WSI2_CreationEtiquetteResult']['STAT']==0){
					$report.= '<a href="http://www.mondialrelay.fr'.$result['WSI2_CreationEtiquetteResult']['URL_Etiquette'].'" target="_blank">Imprimer l\'étiquette Mondial Relay</a><br/>';
					$report.= 'Numéro d\'expédition (tracing) à envoyer au client : '.$result['WSI2_CreationEtiquetteResult']['ExpeditionNum'].'<br/>';
					$report.= 'Email à envoyer au client (par exemple), une fois que vous avez imprimé l\'étiquette :<br/>';
					$tracking2Url='http://www.mondialrelay.com/public/permanent/tracking2.aspx?ens='.$CodeEnseigne.$CodeMarque.'&exp='.$result['WSI2_CreationEtiquetteResult']['ExpeditionNum'].'&crc='.strtoupper(md5('<'.$CodeEnseigne.$CodeMarque.'>'.$result['WSI2_CreationEtiquetteResult']['ExpeditionNum'].'<'.$ClePrivee.'>'));
					$report.= 'Vous pouvez suivre votre colis en vous rendant à l\'adresse '.$tracking2Url.'<br/>';
					}
					else {
						$report.= 'La création de l\'étiquette automatique a échouée, rendez vous sur http://mondialrelay.com pour la créer manuellement';
					}
				}
			}
			//print_r($result);
	}
	else {
	// Compte Particulier
	$report.='\n\nVous n\'avez pas de compte Mondial Relais Pro donc vous devez editer l\'étiquette manuellement sur http://mondialrelais.com\n\n';
	}
   // Todo : add tracking and exp to purchase log so that we can recover it from the admin sales logs
   return $report;
}

add_filter( "wpsc_transaction_result_message_html", "mondialrelais_modify_message_html" );
function mondialrelais_modify_message_html($message_html){
	global $purchase_log,$wpdb;
	$txt=mondialrelais_modify( $purchase_log,$wpdb);
   $message_html = $txt.'<br/>'.$message_html;
   return $message_html;
}

function mondialrelais_modify($purchase_log,$wpdb){
	//print_r($purchase_log);
   if ($purchase_log['shipping_method']=='wpsc_mondialrelay'){
   		$txt ='<br/>';
		$txt.='Option de livraison : Mondial Relais (Vous receverez très prochainement le numéro de suivi pour suivre votre colis)<br/>';
		
		$wpcb_livraison_options=get_option( 'wpcb_livraison'  );
		
		if ($wpcb_livraison_options['mr_ComptePro']){
			// Compte Pro
			// Extrait le num de relais du purchase log
			$pattern='/num=([0-9]{6})&/';
			preg_match($pattern, $purchase_log['shipping_option'], $matches);
			$Num=$matches[1];
			$CodeEnseigne=$wpcb_livraison_options['mr_CodeEnseigne'];
			$CodeMarque=$wpcb_livraison_options['mr_CodeMarque'];
			$ClePrivee=$wpcb_livraison_options['mr_ClePrivee'];
			$Pays='FR'; //todo
			$MapLink='http://www.mondialrelay.com/public/permanent/details_relais.aspx?ens='.$CodeEnseigne.$CodeMarque.'&num='.$Num.'&pays='.$Pays.'&crc='.strtoupper(md5('<'.$CodeEnseigne.$CodeMarque.'>'.$Num.$Pays.'<'.$ClePrivee.'>'));
			$txt.='<a href='.$MapLink.' target=%22_blank%22>Le point relais choisi</a><br/>';		
		}
		else{
			$txt.='Nous vous communiquons très prochainement par email le point Mondial Relais ou vous pourrez retirer votre commande.<br/>';
		}
   }
   else{
   	$txt='';
   }
   return $txt;
}

add_action('wpsc_billing_details_bottom','mondialrelay_add_billing_details_bottom');
function mondialrelay_add_billing_details_bottom(){
	global $purchlogitem,$wpdb,$purchase_log;
	echo '<br/>';
	if ($purchlogitem->extrainfo->shipping_method=='wpsc_mondialrelay'){
		echo '<strong>Livraison:</strong> Mondial Relay';
		$wpcb_livraison_options=get_option( 'wpcb_livraison'  );
		if ($wpcb_livraison_options['mr_ComptePro']){
			// Compte Pro
			// Extrait le num de relais du purchase log
			$pattern='/num=([0-9]{6})&/';
			preg_match($pattern, $purchlogitem->extrainfo->shipping_option, $matches);
			$Num=$matches[1];
			$CodeEnseigne=$wpcb_livraison_options['mr_CodeEnseigne'];
			$CodeMarque=$wpcb_livraison_options['mr_CodeMarque'];
			$ClePrivee=$wpcb_livraison_options['mr_ClePrivee'];
			$Pays='FR'; //todo
			$MapLink='http://www.mondialrelay.com/public/permanent/details_relais.aspx?ens='.$CodeEnseigne.$CodeMarque.'&num='.$Num.'&pays='.$Pays.'&crc='.strtoupper(md5('<'.$CodeEnseigne.$CodeMarque.'>'.$Num.$Pays.'<'.$ClePrivee.'>'));
			echo ' (<a href='.$MapLink.' target=%22_blank%22>Détails du point relais choisi par le client</a>)<br/>';
		}
		else{
			echo '<br/>Vous n\'avez pas de compte Mondial Relay Professionel, il vous faut choisir le point Relais pour votre client en fonction de son adresse et le lui communiquer par email.<br/>';
			echo 'Cela peut se faire sur <a href="http://www.mondialrelay.fr/espaces/particulier/v1/login.aspx" target="_blank">cette page</a>.'; 
			echo 'Nous vous communiquons très prochainement par email le point Mondial Relais ou vous pourrez retirer votre commande.<br/>';
		}
		echo '<br/>Vous pouvez utiliser le champ de note ("Order Notes") ce dessous pour mémoriser le numéro d\'expédition de cette commande.<br/>';
		//Debug :
		//print_r($wpsc_mondialrelay_settings);
	}	
	echo '<br/>';
}
?>