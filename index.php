<?php

/*
Plugin Name: WPCB
Plugin URI: http://wpcb.fr
Description: Plugin de paiement par CB, paypal, ... et de calcul de frais de port (WP e-Commerce requis)
Version: 2.2
Author: 6WWW
Author URI: http://6www.net
*/



register_deactivation_hook( __FILE__, 'wpcb_deactivate' );
function wpcb_deactivate(){
$merchantfiles=array('atos','cheque','virement','simplepaypal','systempaycyberplus');
	foreach ($merchantfiles as $merchantfile){
		unlink(dirname(dirname(dirname(dirname(__FILE__)))).'/wp-content/plugins/wp-e-commerce/wpsc-merchants/'.$merchantfile.'.merchant.php');
	}
}

// Actions lors de la mise en jour du plugin :
add_action( 'admin_init', 'wpcb_update' );
function wpcb_update(){
	$wp_version_required="3.0";
	global $wp_version;
	$plugin=plugin_basename( __FILE__ );
	$plugin_data=get_plugin_data( __FILE__,false);
	if ( version_compare($wp_version,$wp_version_required,"<")){
		if(is_plugin_active($plugin)){
			deactivate_plugins($plugin);
			wp_die( "'".$plugin_data['Name']."' requires WordPress ".$wp_version_required." or higher, and has been deactivated! Please upgrade WordPress and try again.<br /><br />Back to <a href='".admin_url()."'>WordPress admin</a>." );
		}
	}
	// Check if it is a plugin update :
	 $wpcb_dev = get_option ( 'wpcb_dev' );
	if (version_compare($wpcb_dev['version'],$plugin_data['Version'],"<")){
		wpcb_activate(); // So that the 2 files atos.merchant.php  are copied again
	}
		 // if the ZF plugin is successfully loaded this constant is set to true
  if (defined('WP_ZEND_FRAMEWORK') && constant('WP_ZEND_FRAMEWORK')) {
    return true;
  }
  // you can also check if ZF is available on the system
  $paths = explode(PATH_SEPARATOR, get_include_path());
  foreach ($paths as $path) {
    if (file_exists("$path/Zend/Loader.php")) {
      define('WP_ZEND_FRAMEWORK', true);
      return true;
    }
  }
  // nothing found, you may advice the user to install the ZF plugin
  define('WP_ZEND_FRAMEWORK', false);
}

// Lors de la desinstallation : 
register_uninstall_hook(__FILE__, 'wpcb_delete_plugin_options');
function wpcb_delete_plugin_options() {
	delete_option('wpcb_general');
	delete_option('wpcb_atos');
	delete_option('wpcb_cheque');
	delete_option('wpcb_virement');
	delete_option('wpcb_paypal');
	delete_option('wpcb_systempaycyberplus');
	delete_option('wpcb_livraison');
	delete_option('wpcb_mailchimp');
	delete_option('wpcb_dev');
	wpcb_deactivate(); // Do the delete file
}


register_activation_hook(__FILE__, 'wpcb_activate');
function wpcb_activate() {
	$merchantfiles=array('atos','cheque','virement','simplepaypal','systempaycyberplus');
	foreach ($merchantfiles as $merchantfile){
		copy(dirname(__FILE__).'/'.$merchantfile.'.merchant.php',dirname(dirname(__FILE__)).'/wp-e-commerce/wpsc-merchants/'.$merchantfile.'.merchant.php');
	}
}

function wpcb_plugin_menu() {add_plugins_page('WPCB','WPCB','administrator','wpcb','wpcb_display');}
add_action('admin_menu', 'wpcb_plugin_menu'); 

function wpcb_display() {  
?>  
    <!-- Create a header in the default WordPress 'wrap' container -->  
    <div class="wrap">  
        <!-- Add the icon to the page -->  
        <div id="icon-themes" class="icon32"></div>  
        <h2>WPCB Plugin Options</h2>  
        <!-- Make a call to the WordPress function for rendering errors when settings are saved. -->  
        <?php
        	settings_errors();  
            $active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'general';
        ?>
  
        <h2 class="nav-tab-wrapper">
            <a style="font-size:11px;" href="?page=wpcb&tab=general" class="nav-tab <?php echo $active_tab == 'general' ? 'nav-tab-active' : ''; ?>">Options générales</a>  
            <a style="font-size:11px;" href="?page=wpcb&tab=atos" class="nav-tab <?php echo $active_tab == 'atos' ? 'nav-tab-active' : ''; ?>">Atos</a>
            <a style="font-size:11px;" href="?page=wpcb&tab=cheque" class="nav-tab <?php echo $active_tab == 'cheque' ? 'nav-tab-active' : ''; ?>">Chèque</a>
            <a style="font-size:11px;" href="?page=wpcb&tab=virement" class="nav-tab <?php echo $active_tab == 'virement' ? 'nav-tab-active' : ''; ?>">Virement</a>
            <a style="font-size:11px;" href="?page=wpcb&tab=paypal" class="nav-tab <?php echo $active_tab == 'paypal' ? 'nav-tab-active' : ''; ?>">Paypal</a>
			<a style="font-size:11px;" href="?page=wpcb&tab=systempaycyberplus" class="nav-tab <?php echo $active_tab == 'systempaycyberplus' ? 'nav-tab-active' : ''; ?>">Systempay Cyberplus</a>
			<a style="font-size:11px;" href="?page=wpcb&tab=livraison" class="nav-tab <?php echo $active_tab == 'livraison' ? 'nav-tab-active' : ''; ?>">Livraison</a>
			<a style="font-size:11px;" href="?page=wpcb&tab=mailchimp" class="nav-tab <?php echo $active_tab == 'mailchimp' ? 'nav-tab-active' : ''; ?>">Mailchimp</a>
            <a style="font-size:11px;" href="?page=wpcb&tab=dev" class="nav-tab <?php echo $active_tab == 'dev' ? 'nav-tab-active' : ''; ?>">Dev</a>
        </h2>  
  

        <!-- Create the form that will be used to render our options -->  
        <form method="post" action="options.php"> 
        <?php
	    if( $active_tab == 'general' ) {settings_fields( 'wpcb_general' );do_settings_sections( 'wpcb_general' );}
	    elseif( $active_tab == 'atos' ) {settings_fields( 'wpcb_atos' );do_settings_sections( 'wpcb_atos' );}
		elseif( $active_tab == 'cheque' ) {settings_fields( 'wpcb_cheque' );do_settings_sections( 'wpcb_cheque' );}
	    elseif( $active_tab == 'virement' ) {settings_fields( 'wpcb_virement' );do_settings_sections( 'wpcb_virement' );}
	    elseif( $active_tab == 'paypal' ) {settings_fields( 'wpcb_paypal' );do_settings_sections( 'wpcb_paypal' );}
		elseif( $active_tab == 'systempaycyberplus'){settings_fields( 'wpcb_systempaycyberplus');do_settings_sections('wpcb_systempaycyberplus');}
		elseif( $active_tab == 'livraison' ) {settings_fields( 'wpcb_livraison' );do_settings_sections( 'wpcb_livraison' );}
		elseif( $active_tab == 'mailchimp' ) {settings_fields( 'wpcb_mailchimp' );do_settings_sections( 'wpcb_mailchimp' );}
	    elseif( $active_tab == 'dev' ) {settings_fields( 'wpcb_dev' ); do_settings_sections( 'wpcb_dev' );}
	    submit_button();
	    ?>
	</form>  
    </div><!-- /.wrap -->  
<?php  
} // end wpcb_display  

function wpcb_initialize_general_options() { 
 if( false == get_option( 'wpcb_general' ) ) {  add_option( 'wpcb_general' );  }
     add_settings_section('general_settings_section','General Options','wpcb_general_callback','wpcb_general');
     add_settings_field('apiKey','Clé API','wpcb_apiKey_callback','wpcb_general','general_settings_section',array('description' ));  
  add_settings_field('emailapiKey','Email associé à la Clé API','wpcb_emailapiKey_callback','wpcb_general','general_settings_section',array('description' ));  
  add_settings_field('googleemail','Email Google Drive (ou Google App)','wpcb_googleemail_callback','wpcb_general','general_settings_section',array('description' ));  
  add_settings_field('googlepassword','Password associé Gmail ou Google Apps','wpcb_googlepassword_callback','wpcb_general','general_settings_section',array('description' ));  
  add_settings_field('spreadsheetKey','spreadsheetKey','wpcb_spreadsheetKey_callback','wpcb_general','general_settings_section',array('description' ));  
  register_setting('wpcb_general','wpcb_general'); 
} 
add_action('admin_init', 'wpcb_initialize_general_options');  
  
/* ------------------------------------------------------------------------ * 
 * Section Callbacks 
 * ------------------------------------------------------------------------ */   
  
function wpcb_general_callback() {
	global  $wpdb;
	$wpcb_general = get_option( 'wpcb_general' );
    $wpcb_atos = get_option ( 'wpcb_atos' );
        
    echo '<ol>';
    $merchantfiles=array('atos','cheque','virement','simplepaypal','systempaycyberplus');
	foreach ($merchantfiles as $merchantfile){
		$installed=false;
		if (!file_exists(dirname(dirname(__FILE__)).'/wp-e-commerce/wpsc-merchants/'.$merchantfile.'.merchant.php')){
			if(!copy(dirname(__FILE__).'/'.$merchantfile.'.merchant.php',dirname(dirname(__FILE__)).'/wp-e-commerce/wpsc-merchants/'.$merchantfile.'.merchant.php')){
				$nonce_url=wp_nonce_url(admin_url( 'plugins.php?page=wpcb&tab=dev&action=copymerchants'));
				echo '<li><span style="color:red;">'.$merchantfile.'.merchant.php n\'est pas installé. <a href="'.$nonce_url.'">Installer</a></span></li>';
			}
			else {
				$installed=true;
			}
		}
		else{
			$installed=true;
		}
		if ($installed) {
				echo '<li><span style="color:green">Le fichier '.$merchantfile.'.merchant.php est bien au bon endroit -> OK!</span></li>';
			}
	}
	
		$wpcb_checkout_page=$wpdb->get_row("SELECT ID FROM $wpdb->posts WHERE `post_content` LIKE '%[wpcb]%' AND `post_status`='publish'");
		if ($wpcb_checkout_page!=NULL){
			echo '<li><span style="color:green">Le shortcode [wpcb] est sur la page : <a href="'.site_url('?page_id='.$wpcb_checkout_page->ID).'">'.$wpcb_checkout_page->ID.'</a> -> OK!</span></li>';
		}
		else {
			echo '<li><span style="color:red">Vous devez placer le shortcode [wpcb] quelque part sur votre site</span></li>';
		}
		// API
		$post_data['apiKey']=$wpcb_general['apiKey'];
		$post_data['emailapiKey']=$wpcb_general['emailapiKey'];
		$response=wp_remote_post('http://wpcb.fr/api/wpcb/valid.php',array('body' =>$post_data));
		$valid=unserialize($response['body']);
		if ($valid[0]){
			echo '<li><span style="color:green">Votre clé API est valide -> OK!</span></li>';
		}
		else {
			echo '<li><span style="color:red">Optionel : Vous pouvez débloquer l\'assistance et des <a href="http://wordpress.org/extend/plugins/wpcb/" target="_blank">fonctions supplémentaires</a> en <a href="http://wpcb.fr/api-key/" target="_blank">achetant une clé API</a></span> C\'est pas cher et ça m\'aide à améliorer mes plugins.</li>';
		}
		// END OF API
		if (WP_ZEND_FRAMEWORK){
			echo '<li><span style="color:green">Zend is installed -> Ok !</span></li>';
			$GoogleConnection=true;
			$SpreadSheetConnection=true;
		try {$client = Zend_Gdata_ClientLogin::getHttpClient($wpcb_general['googleemail'],$wpcb_general['googlepassword']);}
		catch (Zend_Gdata_App_AuthException $ae){echo $ae->exception();$GoogleConnection=false;}
		if ($GoogleConnection){
			echo '<li><span style="color:green">Your google connection is living-> Ok!</span></li>';
			if ($wpcb_general['spreadsheetKey']){
			// Test 
			$service=Zend_Gdata_Spreadsheets::AUTH_SERVICE_NAME;
			$client=Zend_Gdata_ClientLogin::getHttpClient($wpcb_general['googleemail'],$wpcb_general['googlepassword'], $service);
			// On va chercher le numéro de la feuille :
			$query_worksheet = new Zend_Gdata_Spreadsheets_DocumentQuery(); // todo pour pas de client ici ?
			$query_worksheet->setSpreadsheetKey($wpcb_general['spreadsheetKey']);
			$spreadsheetService = new Zend_Gdata_Spreadsheets($client);
			try {$feed = $spreadsheetService->getWorksheetFeed($query_worksheet);}
			catch (Zend_Gdata_App_HttpException $ae){echo $ae->exception();$SpreadSheetConnection=false;}
			if ($SpreadSheetConnection){
				echo '<li><span style="color:green">Your Spreadsheet can be read -> Ok!</span></li>';
			}
			else{
				echo '<li><span style="color:red">Your Spreadsheet is not reachable</span></li>';
			}
			}
			else{
				echo '<li><span style="color:red">Entrer un numéro de feuille google drive !</span></li>';
			}
		}
		else {
			echo '<li><span style="color:red">Your google connection is not ok, check email and pass below</span></li>';
		}
		// Todo : catch error if spreadsheetKey is wrong
		}
		else{
		echo '<li><span style="color:red">Install Zend first : http://h6e.net/wiki/wordpress/plugins/zend-framework and buy an api key to have acces to <a href="http://wordpress.org/extend/plugins/wpcb/" target="_blank">new features</a></span></li>';	
		}
		
		echo "<li>Remplissez les autres onglets d'options.</li>";
		echo "</ol>";
		echo '<p>La clé API vous donne accès à de nombreuses fonctionnalitées supplémentaires listée <a target="_blank" href="http://wordpress.org/extend/plugins/wpcb/">ici</a></p>';
		echo '<p>Ca ne coute que 5€ HT et m\'aide à maintenir mes plugins à jour.</p><p>Vous pouvez la commander <a target="_blank" href="http://wpcb.fr/api-key/">ici</a></p>';
} // end wpcb_general_callback  
  
/* ------------------------------------------------------------------------ * 
 * Field Callbacks 
 * ------------------------------------------------------------------------ */   
  
function wpcb_apiKey_callback() {  
    $options = get_option('wpcb_general');  
    $val='mykey'; 
    if(isset($options['apiKey'])){$val = $options['apiKey'];}
    echo '<input type="text"  size="75"id="apiKey" name="wpcb_general[apiKey]" value="' . $val . '" />';  
}
function wpcb_emailapiKey_callback() {  
    $options = get_option('wpcb_general');  
    $val='your@email.com'; 
    if(isset($options['emailapiKey'])){$val = $options['emailapiKey'];}
    echo '<input type="text"  size="75"id="emailapiKey" name="wpcb_general[emailapiKey]" value="' . $val . '" />';  
}
function wpcb_googleemail_callback() {  
    $options = get_option('wpcb_general');  
    $val='your@gmail.com'; 
    if(isset($options['googleemail'])){$val = $options['googleemail'];}
    echo '<input type="text"  size="75"id="googleemail" name="wpcb_general[googleemail]" value="' . $val . '" />';  
}
function wpcb_googlepassword_callback() {  
    $options = get_option('wpcb_general');  
    $val='your@email.com'; 
    if(isset($options['googlepassword'])){$val = $options['googlepassword'];}
    echo '<input type="password" id="googlepassword" name="wpcb_general[googlepassword]" value="' . $val . '" />';  
}
function wpcb_spreadsheetKey_callback(){  
    $options = get_option( 'wpcb_general');  
    $val = '0AkLWPxefL-fydHllcFJKTzFLaGdRUG5tbXM1dWJCVWc'; 
    if(isset($options['spreadsheetKey'])){$val = $options['spreadsheetKey'];}
        echo '<input type="text"  size="75"id="spreadsheetKey" name="wpcb_general[spreadsheetKey]" value="' . $val . '" />';
}  
  

/** 
* CB options
*/  
function wpcb_intialize_atos_options() {  
    if(false == get_option( 'wpcb_atos' )){add_option( 'wpcb_atos' );}
	add_settings_section('atos_settings_section','Atos Options','wpcb_atos_callback','wpcb_atos');
	// Add the fields :
	add_settings_field('merchant_id','Merchant ID','wpcb_merchant_id_callback','wpcb_atos','atos_settings_section');
	add_settings_field('pathfile','Pathfile','wpcb_pathfile_callback','wpcb_atos','atos_settings_section');
	add_settings_field('pathfile','Pathfile','wpcb_pathfile_callback','wpcb_atos','atos_settings_section');
	add_settings_field('path_bin_request','path_bin_request','wpcb_path_bin_request_callback','wpcb_atos','atos_settings_section');
	add_settings_field('path_bin_response','path_bin_response','wpcb_path_bin_response_callback','wpcb_atos','atos_settings_section');
	add_settings_field('merchant_country','merchant_country (fr)','wpcb_merchant_country_callback','wpcb_atos','atos_settings_section');
	add_settings_field('currency_code','currency_code (978=€)','wpcb_currency_code_callback','wpcb_atos','atos_settings_section');
	add_settings_field('normal_return_url','normal_return_url','wpcb_normal_return_url_callback','wpcb_atos','atos_settings_section');
	add_settings_field('cancel_return_url','cancel_return_url','wpcb_cancel_return_url_callback','wpcb_atos','atos_settings_section');
	add_settings_field('automatic_response_url','automatic_response_url','wpcb_automatic_response_url_callback','wpcb_atos','atos_settings_section');
	add_settings_field('language','language (fr)','wpcb_language_callback','wpcb_atos','atos_settings_section');
	add_settings_field('payment_means','payment_means','wpcb_payment_means_callback','wpcb_atos','atos_settings_section');
	add_settings_field('header_flag','header_flag (no)','wpcb_header_flag_callback','wpcb_atos','atos_settings_section');
	add_settings_field('advert','advert','wpcb_advert_callback','wpcb_atos','atos_settings_section');
	add_settings_field('logo_id','logo_id','wpcb_logo_id_callback','wpcb_atos','atos_settings_section');
	add_settings_field('logo_id2','logo_id2','wpcb_logo_id2_callback','wpcb_atos','atos_settings_section');
	add_settings_field('wpec_atos_display_name','wpec_atos_display_name','wpcb_wpec_atos_display_name_callback','wpcb_atos','atos_settings_section');
	add_settings_field('wpec_atos_gateway_image','wpec_atos_gateway_image','wpcb_wpec_atos_gateway_image_callback','wpcb_atos','atos_settings_section');
	add_settings_field('logfile','logfile','wpcb_logfile_callback','wpcb_atos','atos_settings_section');
	

	register_setting('wpcb_atos','wpcb_atos','');
} // end wpcb_intialize_atos_options  
add_action( 'admin_init', 'wpcb_intialize_atos_options' );  



function wpcb_atos_callback() {  
    echo '<p>Réglage des options Carte bancaire Atos</p>';
	echo '<p>En rouge les chemins à verifier</p>';
} // end wpcb_general_callback  

function wpcb_merchant_id_callback() {  
    $options = get_option( 'wpcb_atos');  
    $merchant_id = '082584341411111'; 
    if(isset($options['merchant_id'])){$merchant_id = $options['merchant_id'];}
    echo '<input type="text"  size="75"id="merchant_id" name="wpcb_atos[merchant_id]" value="' . $options['merchant_id'] . '" />';  
}
function wpcb_pathfile_callback() {  
    $options = get_option( 'wpcb_atos');  
    $val = dirname(dirname(dirname(dirname(dirname(__FILE__)))))."/cgi-bin/demo/pathfile"; 
    if(isset($options['pathfile'])){$val = $options['pathfile'];}
	if (!file_exists($val)){$style='style="color:#FF0000;"';}
    echo '<input type="text" '.$style.' size="75"id="pathfile" name="wpcb_atos[pathfile]" value="' . $val . '" />';  
}
function wpcb_path_bin_request_callback() {  
    $options = get_option( 'wpcb_atos');  
    $val = dirname(dirname(dirname(dirname(dirname(__FILE__)))))."/cgi-bin/demo/request"; 
    if(isset($options['path_bin_request'])){$val = $options['path_bin_request'];}
	if (!file_exists($val)){$style='style="color:#FF0000;"';}
    echo '<input type="text" '.$style.' size="75" id="path_bin_request" name="wpcb_atos[path_bin_request]" value="' . $val . '" />';
	
}
function wpcb_path_bin_response_callback() {  
    $options = get_option( 'wpcb_atos');  
    $val = dirname(dirname(dirname(dirname(dirname(__FILE__)))))."/cgi-bin/demo/response"; 
    if(isset($options['path_bin_response'])){$val = $options['path_bin_response'];}
	if (!file_exists($val)){$style='style="color:#FF0000;"';}
    echo '<input type="text" '.$style.' size="75"id="path_bin_response" name="wpcb_atos[path_bin_response]" value="' . $val . '" />';  
}
function wpcb_merchant_country_callback() {  
    $options = get_option( 'wpcb_atos');  
    $val = 'fr'; 
    if(isset($options['merchant_country'])){$val = $options['merchant_country'];}
    echo '<input type="text"  size="75"id="merchant_country" name="wpcb_atos[merchant_country]" value="' . $val . '" />';  
}
function wpcb_currency_code_callback() {  
    $options = get_option( 'wpcb_atos');  
    $val = '978'; 
    if(isset($options['currency_code'])){$val = $options['currency_code'];}
    echo '<input type="text"  size="75"id="currency_code" name="wpcb_atos[currency_code]" value="' . $val . '" />';  
}
function wpcb_normal_return_url_callback() {  
    $options = get_option( 'wpcb_atos');  
    $val = site_url(); 
    if(isset($options['normal_return_url'])){$val = $options['normal_return_url'];}
    echo '<input type="text"  size="75"id="normal_return_url" name="wpcb_atos[normal_return_url]" value="' . $val . '" />';  
}
function wpcb_cancel_return_url_callback() {  
    $options = get_option( 'wpcb_atos');  
    $val = site_url(); 
    if(isset($options['cancel_return_url'])){$val = $options['cancel_return_url'];}
    echo '<input type="text"  size="75"id="cancel_return_url" name="wpcb_atos[cancel_return_url]" value="' . $val . '" />';  
}
function wpcb_automatic_response_url_callback() {  
    $options = get_option( 'wpcb_atos');  
    $defaultval = site_url()."?ipn=atos"; 
    if (isset($options['automatic_response_url'])){$val=$options['automatic_response_url'];}else{$val=$defaultval;}
    echo '<input type="text"  size="75" id="automatic_response_url" name="wpcb_atos[automatic_response_url]" value="' . $val . '" placeholder="'.$defaultval.'"/>';  
}
function wpcb_language_callback() {  
    $options = get_option( 'wpcb_atos');  
    $val = 'fr'; 
    if(isset($options['language'])){$val = $options['language'];}
    echo '<input type="text"  size="75"id="language" name="wpcb_atos[language]" value="' . $val . '" />';  
}
function wpcb_payment_means_callback() {  
    $options = get_option( 'wpcb_atos');  
    $val = 'CB,2,VISA,2,MASTERCARD,2'; 
    if(isset($options['payment_means'])){$val = $options['payment_means'];}
    echo '<input type="text"  size="75"id="payment_means" name="wpcb_atos[payment_means]" value="' . $val . '" />';  
}
function wpcb_header_flag_callback() {  
    $options = get_option( 'wpcb_atos');  
    $val = 'no'; 
    if(isset($options['header_flag'])){$val = $options['header_flag'];}
    echo '<input type="text"  size="75"id="header_flag" name="wpcb_atos[header_flag]" value="' . $val . '" />';  
}
function wpcb_advert_callback() {  
    $options = get_option( 'wpcb_atos');  
    $val = 'advert.jpg'; 
    if(isset($options['advert'])){$val = $options['advert'];}
    echo '<input type="text"  size="75"id="advert" name="wpcb_atos[advert]" value="' . $val . '" />';  
}
function wpcb_logo_id_callback() {  
    $options = get_option( 'wpcb_atos');  
    $val = 'logo_id.jpg'; 
    if(isset($options['logo_id'])){$val = $options['logo_id'];}
    echo '<input type="text"  size="75"id="logo_id" name="wpcb_atos[logo_id]" value="' . $val . '" />';  
}
function wpcb_logo_id2_callback() {  
    $options = get_option( 'wpcb_atos');  
    $val = 'logo_id2.jpg'; 
    if(isset($options['logo_id2'])){$val = $options['logo_id2'];}
    echo '<input type="text"  size="75"id="logo_id2" name="wpcb_atos[logo_id2]" value="' . $val . '" />';  
}
function wpcb_wpec_atos_display_name_callback() {  
    $options = get_option( 'wpcb_atos');  
    $val = 'Cartes bancaires'; 
    if(isset($options['wpec_atos_display_name'])){$val = $options['wpec_atos_display_name'];}
    echo '<input type="text"  size="75"id="wpec_atos_display_name" name="wpcb_atos[wpec_atos_display_name]" value="' . $val . '" />';  
}
function wpcb_wpec_atos_gateway_image_callback() {  
    $options = get_option( 'wpcb_atos');  
    $val = plugins_url('logo/LogoMercanetBnpParibas.gif',__FILE__); 
    if(isset($options['wpec_atos_gateway_image'])){$val = $options['wpec_atos_gateway_image'];}
	echo '<img src="'.$val.'" width="75"/>';
    echo '<input type="text"  size="75"id="wpec_atos_gateway_image" name="wpcb_atos[wpec_atos_gateway_image]" value="' . $val . '" />';  
}
function wpcb_logfile_callback(){  
    $options = get_option( 'wpcb_atos');  
    $val = dirname(dirname(dirname(dirname(dirname(__FILE__)))))."/cgi-bin/demo/logfile.txt"; 
    if(isset($options['logfile'])){$val = $options['logfile'];}
	if (!file_exists($val)){$style='style="color:#FF0000;"';}
    echo '<input type="text" '.$style.' size="75"id="logfile" name="wpcb_atos[logfile]" value="' . $val . '" />';  
}

/** 
* Cheque options
*/  
function wpcb_intialize_cheque_options() {  
    if(false == get_option( 'wpcb_cheque' )){add_option( 'wpcb_cheque' );}
	add_settings_section('cheque_settings_section','Chèque Options','wpcb_cheque_callback','wpcb_cheque');
	add_settings_field('displaycheque','Afficher à l\'acheteur','wpcb_displaycheque_callback','wpcb_cheque','cheque_settings_section');
	register_setting('wpcb_cheque','wpcb_cheque','');
}
add_action('admin_init', 'wpcb_intialize_cheque_options');  


function wpcb_cheque_callback() {  
    echo '<p>Réglage des options pour le paiement par chèque</p>';  
}

function wpcb_displaycheque_callback() {  
    $options = get_option( 'wpcb_cheque');  
    $displaycheque = "Merci de libéller vos chèques à l'ordre de Thomas et de les faire parvenir vos chèque à l'adresse postale : Lyon, France."; 
    if(isset($options['displaycheque'])){$displaycheque = $options['displaycheque'];}
    echo '<textarea type="textarea" id="displaycheque" name="wpcb_cheque[displaycheque]" rows="7" cols="50">'.$options['displaycheque'] .'</textarea>';  
}

/** 
* Virement options
*/  
function wpcb_intialize_virement_options() {  
    if(false == get_option( 'wpcb_virement' )){add_option( 'wpcb_virement' );}
	add_settings_section('virement_settings_section','Virement Options','wpcb_virement_callback','wpcb_virement');
	add_settings_field('displayvirement','Afficher à l\'acheteur','wpcb_displayvirement_callback','wpcb_virement','virement_settings_section');
	register_setting('wpcb_virement','wpcb_virement','');
} 
add_action( 'admin_init', 'wpcb_intialize_virement_options' );  

function wpcb_virement_callback() {  
    echo '<p>Réglage des options pour le paiement par virement bancaire</p>';  
}

function wpcb_displayvirement_callback() {  
    $options = get_option( 'wpcb_virement');  
    $displayvirement = "Merci d'envoyer vos virement à ce RIB 45461 24161654 (téléchargeable également à l'adresse : http://monsite.com/rib"; 
    if(isset($options['displayvirement'])){$displayvirement = $options['displayvirement'];}
    echo '<textarea type="textarea" id="displayvirement" name="wpcb_virement[displayvirement]" rows="7" cols="50">'.$options['displayvirement'] .'</textarea>';  
}

/** 
* Paypalpaypal options
*/  
function wpcb_intialize_paypal_options() {  
    if(false == get_option( 'wpcb_paypal' )){add_option( 'wpcb_paypal' );}
	add_settings_section('paypal_settings_section','paypal Options','wpcb_paypal_callback','wpcb_paypal');
	// Add the fields :
	add_settings_field('business','Business (adresse paypal)','wpcb_business_callback','wpcb_paypal','paypal_settings_section');
	add_settings_field('return','Return url','wpcb_return_callback','wpcb_paypal','paypal_settings_section');
	add_settings_field('cancel_return','Cancel Return url','wpcb_cancel_return_callback','wpcb_paypal','paypal_settings_section');
	add_settings_field('wpec_gateway_image_paypal','Image to be displayed','wpcb_wpec_gateway_image_paypal_callback','wpcb_paypal','paypal_settings_section');
	add_settings_field('notify_url','Url de notification auto (ipn)','wpcb_notify_url_callback','wpcb_paypal','paypal_settings_section');
	add_settings_field('sandbox_paypal','Sandbox','wpcb_sandbox_paypal_callback','wpcb_paypal','paypal_settings_section');
	// Register the fields :
	register_setting('wpcb_paypal','wpcb_paypal',''); //sanitize
}
add_action( 'admin_init', 'wpcb_intialize_paypal_options' );  
function wpcb_paypal_callback() {  
    echo '<p>Réglage des options pour le paiement par Paypal</p>';  
}

function wpcb_business_callback(){  
    $options = get_option( 'wpcb_paypal');  
    $val = 'thomas@6www.net'; 
    if(isset($options['business'])){$val = $options['business'];}
        echo '<input type="text"  size="75"id="business" name="wpcb_paypal[business]" value="' . $val . '" />';
}
function wpcb_return_callback(){  
    $options = get_option( 'wpcb_paypal');  
    $val = site_url(); 
    if(isset($options['return'])){$val = $options['return'];}
        echo '<input type="text"  size="75"id="return" name="wpcb_paypal[return]" value="' . $val . '" />';
}
function wpcb_cancel_return_callback(){  
    $options = get_option( 'wpcb_paypal');  
    $val = site_url(); 
    if(isset($options['cancel_return'])){$val = $options['cancel_return'];}
        echo '<input type="text"  size="75"id="cancel_return" name="wpcb_paypal[cancel_return]" value="' . $val . '" />';
}
function wpcb_wpec_gateway_image_paypal_callback(){  
    $options = get_option( 'wpcb_paypal');  
    $val = plugins_url('logo/paypal.jpg' , __FILE__); 
    if(isset($options['wpec_gateway_image_paypal'])){$val = $options['wpec_gateway_image_paypal'];}
	echo '<img src="'.$val.'" widht="75"/>';
	echo '<input type="text" size="75" id="wpec_gateway_image_paypal" name="wpcb_paypal[wpec_gateway_image_paypal]" value="' . $val . '" />';
}

function wpcb_notify_url_callback(){  
    $options = get_option( 'wpcb_paypal');  
    $defaultval =site_url().'?ipn=paypal';
    if(isset($options['notify_url'])){$val = $options['notify_url'];}else{$val=$defaultval;}
        echo '<input type="text"  size="75"id="notify_url" name="wpcb_paypal[notify_url]" value="' . $val . '" placeholder="'.$defaultval.'"/>';
}

function wpcb_sandbox_paypal_callback($args){  
    $options = get_option( 'wpcb_paypal');  
	$html = '<input type="checkbox" id="sandbox_paypal" name="wpcb_paypal[sandbox_paypal]" value="1" ' . checked(1, $options['sandbox_paypal'], false) . '/>';  
    $html .= '<label for="sandbox_paypal"> '  . $args[0] . '</label>';   
    echo $html;
}


/** 
* Systempay Cyberplus options
*/  
function wpcb_intialize_systempaycyberplus_options() {  
    if(false == get_option( 'wpcb_systempaycyberplus' )){add_option( 'wpcb_systempaycyberplus' );}
	add_settings_section('systempaycyberplus_settings_section','Systempay Cyberplus Options','wpcb_systempaycyberplus_callback','wpcb_systempaycyberplus');
	// Add the fields :
	add_settings_field('identifiant','Identifiant','wpcb_identifiant_callback','wpcb_systempaycyberplus','systempaycyberplus_settings_section');
	add_settings_field('certificat','Certificat','wpcb_certificat_callback','wpcb_systempaycyberplus','systempaycyberplus_settings_section');
	add_settings_field('wpec_gateway_image_paypal','Image sur la page de choix du paiement','wpcb_wpec_gateway_image_systempaycyberplus_callback','wpcb_systempaycyberplus','systempaycyberplus_settings_section');
	// Register the fields :
	register_setting('wpcb_systempaycyberplus','wpcb_systempaycyberplus',''); //sanitize
}
add_action( 'admin_init', 'wpcb_intialize_systempaycyberplus_options' );  
function wpcb_systempaycyberplus_callback() {  
    echo '<p>Réglage des options pour le paiement par Systempay Cyberplus (Banque populaire)</p>';  
}

function wpcb_identifiant_callback(){  
    $options = get_option( 'wpcb_systempaycyberplus');  
    $val = '54020139'; 
    if(isset($options['identifiant'])){$val = $options['identifiant'];}
        echo '<input type="text"  size="75" id="identifiant" name="wpcb_systempaycyberplus[identifiant]" value="' . $val . '" />';
}
function wpcb_certificat_callback(){  
    $options = get_option( 'wpcb_systempaycyberplus');  
    $val = '7639056200685146'; 
    if(isset($options['certificat'])){$val = $options['certificat'];}
        echo '<input type="text"  size="75" id="certificat" name="wpcb_systempaycyberplus[certificat]" value="' . $val . '" />';
}

function wpcb_wpec_gateway_image_systempaycyberplus_callback(){  
    $options = get_option( 'wpcb_systempaycyberplus');  
    $val = plugins_url()."/wpcb/logo/logo_systempaycyberplus.gif";
    if(isset($options['wpec_gateway_image_systempaycyberplus'])){$val = $options['wpec_gateway_image_systempaycyberplus'];}
        echo '<img src="'.$val.'">';
        echo '<input type="text" size="75" id="wpec_gateway_image_systempaycyberplus" name="wpcb_systempaycyberplus[wpec_gateway_image_systempaycyberplus]" value="' . $val . '" />';
}

/** 
* Livraison options
*/  
function wpcb_intialize_livraison_options() {  
    if(false == get_option( 'wpcb_livraison' )){add_option( 'wpcb_livraison' );}
	add_settings_section('livraison_settings_section','livraison Options','wpcb_livraison_callback','wpcb_livraison');
	// Add the fields :
	add_settings_field('ENLEVEMENT','Proposer l\'enlèvement sur place','wpcb_ENLEVEMENT_callback','wpcb_livraison','livraison_settings_section');
	add_settings_field('ENLEVEMENT_name','Affichage pour Enlèvement (adresse par exemple)','wpcb_ENLEVEMENT_name_callback','wpcb_livraison','livraison_settings_section');
		add_settings_field('COLIS','Proposer la poste colis','wpcb_COLIS_callback','wpcb_livraison','livraison_settings_section');
	add_settings_field('COLIS_name','Affichage pour Colis','wpcb_COLIS_name_callback','wpcb_livraison','livraison_settings_section');
	add_settings_field('CHRONOPOST','Proposer la poste chronopost','wpcb_CHRONOPOST_callback','wpcb_livraison','livraison_settings_section');
	add_settings_field('CHRONOPOST_name','Affichage Chronopost','wpcb_CHRONOPOST_name_callback','wpcb_livraison','livraison_settings_section');
	add_settings_field('ENVELOPPEDOCUMENT','Proposer la poste Enveloppe Document','wpcb_ENVELOPPEDOCUMENT_callback','wpcb_livraison','livraison_settings_section');
	add_settings_field('ENVELOPPEDOCUMENT_name','Affichage enveloppe document France','wpcb_ENVELOPPEDOCUMENT_name_callback','wpcb_livraison','livraison_settings_section');
		add_settings_field('ENVELOPPEDOCUMENTUE','Proposer la poste Enveloppe Document Union Européenne','wpcb_ENVELOPPEDOCUMENTUE_callback','wpcb_livraison','livraison_settings_section');
	add_settings_field('ENVELOPPEDOCUMENTUE_name','Affichage enveloppe document Union Européenne','wpcb_ENVELOPPEDOCUMENTUE_name_callback','wpcb_livraison','livraison_settings_section');
		add_settings_field('ENVELOPPEDOCUMENTOMRDM','Proposer la poste Enveloppe Document Outre-Mer Reste du Monde','wpcb_ENVELOPPEDOCUMENTOMRDM_callback','wpcb_livraison','livraison_settings_section');
		add_settings_field('ENVELOPPEDOCUMENTOMRDM_name','Affichage enveloppe document Outre-Mer & Reste du Monde','wpcb_ENVELOPPEDOCUMENTOMRDM_name_callback','wpcb_livraison','livraison_settings_section');

// MR : 
		add_settings_field('mr_ComptePro','Je dispose d\'un compte pro Mondial Relay','wpcb_mr_ComptePro_callback','wpcb_livraison','livraison_settings_section');
		add_settings_field('MONDIALRELAY_name','Affichage Mondial Relay','wpcb_MONDIALRELAY_name_callback','wpcb_livraison','livraison_settings_section');
		add_settings_field('mr_CodeEnseigne','Code Enseigne','wpcb_mr_CodeEnseigne_callback','wpcb_livraison','livraison_settings_section');
		add_settings_field('mr_ClePrivee','Clé Privée','wpcb_mr_ClePrivee_callback','wpcb_livraison','livraison_settings_section');		
		add_settings_field('mr_CodeMarque','Code Marque','wpcb_mr_CodeMarque_callback','wpcb_livraison','livraison_settings_section');		
		add_settings_field('mr_COL_Rel','Colis relay proche de vous','wpcb_mr_COL_Rel_callback','wpcb_livraison','livraison_settings_section');		
		add_settings_field('mr_Expe_Ad1','Expéditeur (attention à la syntaxe)','wpcb_mr_Expe_Ad1_callback','wpcb_livraison','livraison_settings_section');
		add_settings_field('mr_Expe_Ad3','Adresse','wpcb_mr_Expe_Ad3_callback','wpcb_livraison','livraison_settings_section');		
		add_settings_field('mr_Expe_CP','Code Postal','wpcb_mr_Expe_CP_callback','wpcb_livraison','livraison_settings_section');		
		add_settings_field('mr_Expe_Ville','Ville','wpcb_mr_Expe_Ville_callback','wpcb_livraison','livraison_settings_section');		
		add_settings_field('mr_Expe_Tel1','Téléphone','wpcb_mr_Expe_Tel1_callback','wpcb_livraison','livraison_settings_section');		

	// Register the fields :
	register_setting('wpcb_livraison','wpcb_livraison',''); //sanitize
}
add_action( 'admin_init', 'wpcb_intialize_livraison_options' );  

// Include livraison files
include('livraison.php');
include('mondialrelay.php');

function wpcb_livraison_callback() {  
    echo '<p>Réglage des options pour la livraison</p>';  
    // API
    $wpcb_general=get_option('wpcb_general');
		$post_data['apiKey']=$wpcb_general['apiKey'];
		$post_data['emailapiKey']=$wpcb_general['emailapiKey'];
		$response=wp_remote_post('http://wpcb.fr/api/wpcb/valid.php',array('body' =>$post_data));
		$valid=unserialize($response['body']);
		if ($valid[0]){
			echo '<p><span style="color:green">Votre clé API est valide -> OK!</span></p>';
		}
		else {
			echo '<p><span style="color:red">Optionel : Vous pouvez débloquer les options de livraison et calcul de frais de port en achetant une clé API</a>.</span> C\'est pas cher et ça m\'aide à améliorer mes plugins.</p>';
		}
		// END OF API
		echo '<p>Si les tarifs (donnés à titre indicatif) ont changé, merci de me le notifier à thomas@6www.net. </p>';
		echo 'Si vous ne disposez pas d\'un compte pro Mondial Relay vous devez vous rendre sur <a href="http://www.mondialrelay.fr/espaces/particulier/v1/login.aspx" target="_blank">cette page</a> pour envoyer votre colis.<br />';
		echo 'Les tarifs entre compte pro et compte particulier sont légèrement différents.<br />';
		echo 'Un compte pro permet d\'éditer les étiquettes directement depuis votre interface WP e-Commerce.<br />';
		
}
function wpcb_ENLEVEMENT_callback($args){  
    $options = get_option( 'wpcb_livraison');  
	$html = '<input type="checkbox" id="ENLEVEMENT" name="wpcb_livraison[ENLEVEMENT]" value="1" ' . checked(1, $options['ENLEVEMENT'], false) . '/>';  
    $html .= '<label for="ENLEVEMENT"> '  . $args[0] . '</label>';   
    echo $html;
}
function wpcb_ENLEVEMENT_name_callback(){  
    $options = get_option( 'wpcb_livraison');  
    $defaultval = 'Enlèvement sur place à Lyon Centre'; 
    if(isset($options['ENLEVEMENT_name'])){$val = $options['ENLEVEMENT_name'];}else{$val=$defaultval;}
        echo '<input type="text"  size="75"id="ENLEVEMENT_name" name="wpcb_livraison[ENLEVEMENT_name]" value="' . $val . '" placeholder="'.$defaultval.'"/>';
}
function wpcb_COLIS_callback($args){  
    $options = get_option( 'wpcb_livraison');  
	$html = '<input type="checkbox" id="COLIS" name="wpcb_livraison[COLIS]" value="1" ' . checked(1, $options['COLIS'], false) . '/>';  
    $html .= '<label for="COLIS"> '  . $args[0] . '</label>';   
    echo $html;
}
function wpcb_COLIS_name_callback(){  
    $options = get_option( 'wpcb_livraison');  
    $defaultval = 'Colieco'; 
    if(isset($options['COLIS_name'])){$val = $options['COLIS_name'];}else{$val=$defaultval;}
        echo '<input type="text"  size="75"id="COLIS_name" name="wpcb_livraison[COLIS_name]" value="' . $val . '" placeholder="'.$defaultval.'"/>';
}
function wpcb_CHRONOPOST_callback($args){  
    $options = get_option( 'wpcb_livraison');  
	$html = '<input type="checkbox" id="CHRONOPOST" name="wpcb_livraison[CHRONOPOST]" value="1" ' . checked(1, $options['CHRONOPOST'], false) . '/>';  
    $html .= '<label for="CHRONOPOST"> '  . $args[0] . '</label>';   
    echo $html;
}
function wpcb_CHRONOPOST_name_callback(){  
    $options = get_option( 'wpcb_livraison');  
    $defaultval = 'Chronopost'; 
    if(isset($options['CHRONOPOST_name'])){$val = $options['CHRONOPOST_name'];}else{$val=$defaultval;}
        echo '<input type="text"  size="75"id="CHRONOPOST_name" name="wpcb_livraison[CHRONOPOST_name]" value="' . $val . '" placeholder="'.$defaultval.'"/>';
}
function wpcb_ENVELOPPEDOCUMENT_callback($args){  
    $options = get_option( 'wpcb_livraison');  
	$html = '<input type="checkbox" id="ENVELOPPEDOCUMENT" name="wpcb_livraison[ENVELOPPEDOCUMENT]" value="1" ' . checked(1, $options['ENVELOPPEDOCUMENT'], false) . '/>';  
    $html .= '<label for="ENVELOPPEDOCUMENT"> '  . $args[0] . '</label>';   
    echo $html;
}
function wpcb_ENVELOPPEDOCUMENT_name_callback(){  
    $options = get_option( 'wpcb_livraison');  
    $defaultval = 'Enveloppe Document France'; 
    if(isset($options['ENVELOPPEDOCUMENT_name'])){$val = $options['ENVELOPPEDOCUMENT_name'];}else{$val=$defaultval;}
        echo '<input type="text"  size="75"id="ENVELOPPEDOCUMENT_name" name="wpcb_livraison[ENVELOPPEDOCUMENT_name]" value="' . $val . '" placeholder="'.$defaultval.'"/>';
}
function wpcb_ENVELOPPEDOCUMENTUE_callback($args){  
    $options = get_option( 'wpcb_livraison');  
	$html = '<input type="checkbox" id="ENVELOPPEDOCUMENTUE" name="wpcb_livraison[ENVELOPPEDOCUMENTUE]" value="1" ' . checked(1, $options['ENVELOPPEDOCUMENTUE'], false) . '/>';  
    $html .= '<label for="ENVELOPPEDOCUMENTUE"> '  . $args[0] . '</label>';   
    echo $html;
}
function wpcb_ENVELOPPEDOCUMENTUE_name_callback(){  
    $options = get_option( 'wpcb_livraison');  
    $defaultval = 'Enveloppe Document Union Européenne'; 
    if(isset($options['ENVELOPPEDOCUMENTUE_name'])){$val = $options['ENVELOPPEDOCUMENTUE_name'];}else{$val=$defaultval;}
        echo '<input type="text"  size="75"id="ENVELOPPEDOCUMENTUE_name" name="wpcb_livraison[ENVELOPPEDOCUMENTUE_name]" value="' . $val . '" placeholder="'.$defaultval.'"/>';
}
function wpcb_ENVELOPPEDOCUMENTOMRDM_callback($args){  
    $options = get_option( 'wpcb_livraison');  
	$html = '<input type="checkbox" id="ENVELOPPEDOCUMENTOMRDM" name="wpcb_livraison[ENVELOPPEDOCUMENTOMRDM]" value="1" ' . checked(1, $options['ENVELOPPEDOCUMENTOMRDM'], false) . '/>';  
    $html .= '<label for="ENVELOPPEDOCUMENTOMRDM"> '  . $args[0] . '</label>';   
    echo $html;
}
function wpcb_ENVELOPPEDOCUMENTOMRDM_name_callback(){  
    $options = get_option( 'wpcb_livraison');  
    $defaultval = 'Enveloppe Document Outre-Mer & Reste du monde'; 
    if(isset($options['ENVELOPPEDOCUMENTOMRDM_name'])){$val = $options['ENVELOPPEDOCUMENTOMRDM_name'];}else{$val=$defaultval;}
        echo '<input type="text"  size="75"id="ENVELOPPEDOCUMENTOMRDM_name" name="wpcb_livraison[ENVELOPPEDOCUMENTOMRDM_name]" value="' . $val . '" placeholder="'.$defaultval.'"/>';
}

// Mondial Relay Compte Pro :
function wpcb_mr_ComptePro_callback($args){  
    $options = get_option( 'wpcb_livraison');  
	$html = '<input type="checkbox" id="mr_ComptePro" name="wpcb_livraison[mr_ComptePro]" value="1" ' . checked(1, $options['mr_ComptePro'], false) . '/>';  
    $html .= '<label for="mr_ComptePro"> '  . $args[0] . '</label>';   
    echo $html;
}
function wpcb_MONDIALRELAY_name_callback(){  
    $options = get_option( 'wpcb_livraison');  
    $defaultval = 'Mondial Relay'; 
    if(isset($options['MONDIALRELAY_name'])){$val = $options['MONDIALRELAY_name'];}else{$val=$defaultval;}
        echo '<input type="text"  size="75"id="MONDIALRELAY_name" name="wpcb_livraison[MONDIALRELAY_name]" value="' . $val . '" placeholder="'.$defaultval.'"/>';
}
function wpcb_mr_CodeEnseigne_callback(){  
    $options = get_option( 'wpcb_livraison');  
    $defaultval = 'BDTESTMR'; 
    if(isset($options['mr_CodeEnseigne'])){$val = $options['mr_CodeEnseigne'];}else{$val=$defaultval;}
        echo '<input type="text"  size="8"id="mr_CodeEnseigne" name="wpcb_livraison[mr_CodeEnseigne]" value="' . $val . '" placeholder="'.$defaultval.'"/>';
}
function wpcb_mr_ClePrivee_callback(){  
    $options = get_option( 'wpcb_livraison');  
    $defaultval = 'TesT_MondiaL_RelaY'; 
    if(isset($options['mr_ClePrivee'])){$val = $options['mr_ClePrivee'];}else{$val=$defaultval;}
        echo '<input type="text"  size="75"id="mr_ClePrivee" name="wpcb_livraison[mr_ClePrivee]" value="' . $val . '" placeholder="'.$defaultval.'"/>';
}
function wpcb_mr_CodeMarque_callback(){  
    $options = get_option( 'wpcb_livraison');  
    $defaultval = '11'; 
    if(isset($options['mr_CodeMarque'])){$val = $options['mr_CodeMarque'];}else{$val=$defaultval;}
        echo '<input type="text"  size="2"id="mr_CodeMarque" name="wpcb_livraison[mr_CodeMarque]" value="' . $val . '" placeholder="'.$defaultval.'"/>';
}

function wpcb_mr_COL_Rel_callback(){  
    $options = get_option( 'wpcb_livraison');  
    $defaultval = '012262'; 
    if(isset($options['mr_COL_Rel'])){$val = $options['mr_COL_Rel'];}else{$val=$defaultval;}
        echo '<input type="text"  size="6" id="mr_COL_Rel" name="wpcb_livraison[mr_COL_Rel]" value="' . $val . '" placeholder="'.$defaultval.'"/>';
}
function wpcb_mr_Expe_Ad1_callback(){  
    $options = get_option( 'wpcb_livraison');  
    $defaultval = 'M. Thomas DT'; 
    if(isset($options['mr_Expe_Ad1'])){$val = $options['mr_Expe_Ad1'];}else{$val=$defaultval;}
        echo '<input type="text"  size="75" id="mr_Expe_Ad1" name="wpcb_livraison[mr_Expe_Ad1]" value="' . $val . '" placeholder="'.$defaultval.'"/>';
}
function wpcb_mr_Expe_Ad3_callback(){  
    $options = get_option( 'wpcb_livraison');  
    $defaultval = '2A Rue Danton'; 
    if(isset($options['mr_Expe_Ad3'])){$val = $options['mr_Expe_Ad3'];}else{$val=$defaultval;}
        echo '<input type="text"  size="75" id="mr_Expe_Ad3" name="wpcb_livraison[mr_Expe_Ad3]" value="' . $val . '" placeholder="'.$defaultval.'"/>';
}
function wpcb_mr_Expe_CP_callback(){  
    $options = get_option( 'wpcb_livraison');  
    $defaultval = '92120'; 
    if(isset($options['mr_Expe_CP'])){$val = $options['mr_Expe_CP'];}else{$val=$defaultval;}
        echo '<input type="text"  size="75" id="mr_Expe_CP" name="wpcb_livraison[mr_Expe_CP]" value="' . $val . '" placeholder="'.$defaultval.'"/>';
}
function wpcb_mr_Expe_Ville_callback(){  
    $options = get_option( 'wpcb_livraison');  
    $defaultval = 'Montrouge'; 
    if(isset($options['mr_Expe_Ville'])){$val = $options['mr_Expe_Ville'];}else{$val=$defaultval;}
        echo '<input type="text"  size="75" id="mr_Expe_Ville" name="wpcb_livraison[mr_Expe_Ville]" value="' . $val . '" placeholder="'.$defaultval.'"/>';
}
function wpcb_mr_Expe_Tel1_callback(){  
    $options = get_option( 'wpcb_livraison');  
    $defaultval = '+336786101'; 
    if(isset($options['mr_Expe_Tel1'])){$val = $options['mr_Expe_Tel1'];}else{$val=$defaultval;}
        echo '<input type="text"  size="75" id="mr_Expe_Tel1" name="wpcb_livraison[mr_Expe_Tel1]" value="' . $val . '" placeholder="'.$defaultval.'"/>';
}




/** 
* Developper options
*/  
function wpcb_intialize_dev_options() {  
    if(false == get_option( 'wpcb_dev' )){add_option( 'wpcb_dev' );}
	add_settings_section('dev_settings_section','dev Options','wpcb_dev_callback','wpcb_dev');
	// Add the fields :
	add_settings_field('version','Version Plugin','wpcb_version_callback','wpcb_dev','dev_settings_section');
	add_settings_field('mode_demo','Mode Démo','wpcb_mode_demo_callback','wpcb_dev','dev_settings_section');
	add_settings_field('mode_debugatos','Mode Debug Atos','wpcb_mode_debugatos_callback','wpcb_dev','dev_settings_section');
	add_settings_field('mode_test','Mode Test','wpcb_mode_test_callback','wpcb_dev','dev_settings_section');
	register_setting('wpcb_dev','wpcb_dev','');
} // end wpcb_intialize_atos_options  
add_action( 'admin_init', 'wpcb_intialize_dev_options' );  



function wpcb_dev_callback() {
	 $wpcb_general = get_option( 'wpcb_general' );
     $wpcb_atos = get_option ( 'wpcb_atos' );
	 $wpcb_dev = get_option ( 'wpcb_dev' );

    echo '<p>Options pour developper</p>';
    echo '<p>Aidez-nous dans le développement sur <a href="https://github.com/6WWW/wpcb">Github</a></p>';
    echo '<ul>';
		echo '<li><p>Plugin version : '.$wpcb_dev['version'].'</li>';
		echo '<li><p>Dossier Plugin : '.dirname(__FILE__).'</p></li>';
		echo '<li><p>Racine wordpress : '.dirname(dirname(dirname(dirname(__FILE__)))).'</p></li>';
		if	((isset($_GET['action'])) && ($_GET['action']=='copymerchants')){
		$merchantfiles=array('atos','cheque','virement','simplepaypal','systempaycyberplus');
		foreach ($merchantfiles as $merchantfile){
			copy(dirname(__FILE__).'/'.$merchantfile.'.merchant.php',dirname(dirname(__FILE__)).'/wp-e-commerce/wpsc-merchants/'.$merchantfile.'.merchant.php');
		}
		}
		$nonce_url=wp_nonce_url(admin_url( 'plugins.php?page=wpcb&tab=dev&action=copymerchants'));
		echo '<li>Copier les fichiers merchants <a href="'.$nonce_url.'">en cliquant ici</a></li>';
		$nonce_url=wp_nonce_url(admin_url( 'plugins.php?page=wpcb&tab=dev&action=sandbox'));
		echo '<li>Tester votre fichier ipn atos <a href="'.$nonce_url.'">en cliquant ici</a> (Cela va mettre Ã  jour log.txt et google drive)</li>';
		echo '<li>'.$wpcb_atos['automatic_response_url'].'</li>';
		if ((isset($_GET['action'])) && ($_GET['action']=='sandbox')){
			$post_data['DATA']='Dummy'; //Needed
			$post_data['sandbox']='NULL!1!2!'.$wpcb_atos['merchant_id'].'!fr!100!8755900!CB!10-02-2012!11:50!10-02-2012!004!certif!22!978!4974!545!1!22!Comp!CompInfo!return!caddie!Merci!fr!fr!001!8787084074894!my@email.com!1.10.21.192!30!direct!data';
			$response=wp_remote_post($wpcb_atos['automatic_response_url'],array('body' =>$post_data));
			//print_r($response);
		}
}

function wpcb_version_callback(){  
    $options = get_option( 'wpcb_dev');  
    $val = $plugin_data['Version']; 
    if(isset($options['version'])){$val = $options['version'];}
        echo '<input type="hidden" id="version" name="wpcb_dev[version]" value="' . $val. '" />';
}
function wpcb_mode_debugatos_callback($args){  
    $options = get_option( 'wpcb_dev');  
	$html = '<input type="checkbox" id="mode_debugatos" name="wpcb_dev[mode_debugatos]" value="1" ' . checked(1, $options['mode_debugatos'], false) . '/>';  
    $html .= '<label for="mode_debugatos"> '  . $args[0] . '</label>';   
    echo $html;
}
function wpcb_mode_demo_callback($args){  
    $options = get_option( 'wpcb_dev');  
	$html = '<input type="checkbox" id="mode_demo" name="wpcb_dev[mode_demo]" value="1" ' . checked(1, $options['mode_demo'], false) . '/>';  
    $html .= '<label for="mode_demo"> '  . $args[0] . '</label>';   
    echo $html;
}
function wpcb_mode_test_callback($args){  
    $options = get_option( 'wpcb_dev');  
	$html = '<input type="checkbox" id="mode_test" name="wpcb_dev[mode_test]" value="1" ' . checked(1, $options['mode_test'], false) . '/>';  
    $html .= '<label for="mode_test"> '  . $args[0] . '</label>';   
    echo $html;
}



add_filter( 'plugin_action_links', 'wpcb_plugin_action_links',10,2);
// Display a Settings link on the main Plugins page
function wpcb_plugin_action_links( $links, $file ) {
	if ($file==plugin_basename( __FILE__ )){
		$wpcb_links = '<a href="'.get_admin_url().'plugins.php?page=wpcb">'.__('Settings').'</a>';
		array_unshift( $links, $wpcb_links );
	}
	return $links;
}
// FIN DES REGLAGES :


// SHORTCODE :
add_shortcode( 'wpcb', 'shortcode_wpcb_handler' );
function shortcode_wpcb_handler( $atts, $content=null, $code="" ) {
	global $wpdb, $purchase_log, $wpsc_cart;
	$sessionid=$_GET['sessionid'];
	$wpcb_general = get_option( 'wpcb_general' );
	$wpcb_dev=get_option( 'wpcb_dev' );
    
	$purch_log_email=get_option('purch_log_email');
	if (!$purch_log_email){$purch_log_email=get_bloginfo('admin_email');}
	if ($_GET['action']=='CB'){
		$wpcb_atos = get_option ( 'wpcb_atos' );
		// cf. Dictionnaire des Données Atos :
		if ((array_key_exists('mode_demo', $wpcb_dev)) && ($wpcb_dev['mode_demo'])){
			$merchant_id="082584341411111";
			$pathfile=dirname(dirname(dirname(dirname(dirname(__FILE__)))))."/cgi-bin/demo/pathfile";
			$path_bin_request =dirname(dirname(dirname(dirname(dirname(__FILE__)))))."/cgi-bin/demo/request";
		}
		else{
			$merchant_id=$wpcb_atos['merchant_id'];	
			$pathfile=$wpcb_atos['pathfile'];
			$path_bin_request=$wpcb_atos['path_bin_request'];
		}
		$parm="merchant_id=". $merchant_id;
		$parm="$parm merchant_country=".$wpcb_atos['merchant_country'];
		$purchase_log=$wpdb->get_row("SELECT * FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `sessionid`= ".$sessionid." LIMIT 1") ;
		$amount= ($purchase_log->totalprice)*100;
		$amount=str_pad($amount,3,"0",STR_PAD_LEFT);
		$parm="$parm amount=".$amount;
		$parm="$parm currency_code=".$wpcb_atos['currency_code'];
		$parm="$parm pathfile=". $pathfile;
		$questionmarkishere=strpos($wpcb_atos['normal_return_url'],'?');
		if ($questionmarkishere){$char='&';}else{$char='?';}
		$parm="$parm normal_return_url=".$wpcb_atos['normal_return_url'].$char."sessionid=".$sessionid;
		$parm="$parm cancel_return_url=".$wpcb_atos['cancel_return_url'];
		$parm="$parm automatic_response_url=".$wpcb_atos['automatic_response_url'];
		$parm="$parm language=".$wpcb_atos['language'];
		$parm="$parm payment_means=".$wpcb_atos['payment_means'];
		$parm="$parm header_flag=".$wpcb_atos['header_flag'];
		$parm="$parm order_id=$sessionid";
		$parm="$parm logo_id2=".$wpcb_atos['logo_id2'];
		$parm="$parm advert=".$wpcb_atos['advert'];
		if ($wpcb_dev['mode_debugatos']){
			//Va afficher sur la page ou se trouve le shortcode les parametres.
			$parm_pretty=str_replace(' ','<br/>',$parm);echo '<p>You see this because you are in debug mode :</p><pre>'.$parm_pretty.'</pre><p>End of debug mode</p>';
		}
		$result=exec("$path_bin_request $parm");
		$tableau=explode ("!","$result");
		$code=$tableau[1];
		$error=$tableau[2];
		if (($code=="") && ($error=="")){
			$message="<p>".__('Error calling the atos api : exec request not found','wpcb')."  $path_bin_request</p>";
			$message.= "<p>".__('Thank you for reporting this error to:','wpcb')." ".$purch_log_email."</p>";
		}
		elseif ($code != 0){
			$message="<p>".__('Atos API error : ','wpcb')." $error</p>";
			$message.= "<p>".__('Thank you for reporting this error to:','wpcb')." ".$purch_log_email."</p>";
		}
		else{
			// Affiche le formulaire avec le choix des cartes bancaires :
			$message = $tableau[3];
		}
		// End of atos
	}
	elseif ($_GET['action']=='paypal'){
	$wpcb_paypal = get_option ( 'wpcb_paypal' );
	$purchase_log=$wpdb->get_row("SELECT * FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `sessionid`= ".$sessionid." LIMIT 1") ;
		if ($wpcb_paypal['sandbox_paypal']){
			$message='<form action="https://sandbox.paypal.com/cgi-bin/webscr" method="post">';
		}
		else{
			$message='<form action="https://www.paypal.com/cgi-bin/webscr" method="post">';
		}
		$message.='<input type="hidden" name="cmd" value="_xclick">';
		$message.='<input type="hidden" name="business" value="'.$wpcb_paypal['business'].'">';
		$message.='<input type="hidden" name="lc" value="FR">';
		$message.='<input type="hidden" name="item_name" value="Commande #'.$purchase_log->id.'">';
		$message.='<input type="hidden" name="item_number" value="'.$sessionid.'">';
		$amount=number_format($purchase_log->totalprice,2);
		$message.='<input type="hidden" name="amount" value="'.$amount.'">';
		$message.='<input type="hidden" name="no_note" value="1">';
		$message.='<input type="hidden" name="return" value="'.$wpcb_paypal['return'].'">';
		$message.='<input type="hidden" name="cancel_return" value="'.$wpcb_paypal['cancel_return'].'">';
		$message.='<input type="hidden" name="notify_url" value="'.$wpcb_paypal['notify_url'].'">';
		$message.='<input type="hidden" name="no_shipping" value="1"><input type="hidden" name="currency_code" value="EUR"><input type="hidden" name="button_subtype" value="services"><input type="hidden" name="no_note" value="0"><input type="hidden" name="bn" value="PP-BuyNowBF:btn_paynowCC_LG.gif:NonHostedGuest"><input type="image" src="https://www.paypalobjects.com/fr_FR/FR/i/btn/btn_paynowCC_LG.gif" border="0" name="submit" alt="PayPal - la solution de paiement en ligne la plus simple et la plus sécurisée !"><img alt="" border="0" src="https://www.paypalobjects.com/fr_XC/i/scr/pixel.gif" width="1" height="1"></form>';
	}
	elseif ($_GET['action']=='systempaycyberplus'){
	$wpcb_systempaycyberplus = get_option('wpcb_systempaycyberplus');
	$purchase_log=$wpdb->get_row("SELECT * FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `sessionid`= ".$sessionid." LIMIT 1") ;
	
	$systempay_cyberplus_args = array(
                'vads_site_id' => $wpcb_systempaycyberplus['identifiant'],
				'vads_ctx_mode' => 'TEST',
				'vads_version' => 'V2',
				'vads_language' => 'fr',
				'vads_currency' => '978',
				'vads_amount' => $purchase_log->totalprice*100,
				'vads_page_action' => 'PAYMENT',
				'vads_action_mode' => 'INTERACTIVE',
				'vads_payment_config' => 'SINGLE',
				'vads_capture_delay' => '',
				'vads_order_id' =>$sessionid,
				'vads_cust_id' => $purchase_log->id,
				'vads_redirect_success_timeout' => '5',
				'vads_redirect_success_message' => 'Redirection vers la boutique dans quelques instants',
				'vads_redirect_error_timeout' => '5',
				'vads_redirect_error_message' => 'Redirection vers la boutique dans quelques instants',
				'vads_trans_id' => str_pad(rand(0, 8999).date('d'), 6, "0", STR_PAD_LEFT),
				'vads_trans_date' => gmdate("YmdHis")
				);
			$signature=get_Signature($systempay_cyberplus_args,$wpcb_systempaycyberplus['certificat']);
			$systempay_cyberplus_args['signature']= $signature;
				
            $message='<form action="https://paiement.systempay.fr/vads-payment/" method="post">';
            foreach($systempay_cyberplus_args as $key => $value){
                $message.= "<input type='hidden' name='$key' value='$value'/>";
            }
			$message.='<input  type="image" src="'.$wpcb_systempaycyberplus['wpec_gateway_image_systempaycyberplus'].'" alt="Payer" value="submit">';
			$message.='</form>';	
	}
	elseif ($_GET['action']=='normal_return'){
		// Pas utilisé pour l'instant
		$wpsc_cart->empty_cart();
	}
	elseif ($_GET['action']=='cancel_return'){
		// Pas utilisé pour l'instant
		$wpsc_cart->empty_cart();
	}
	elseif ($_GET['action']=='sandbox'){
	
	
	
	}
	else{
		//Appel direct à cette page
		$message='<p>'.__('Direct call to this page not allowed','wpcb').'</p>';
		// Add here some code if you want to test some php for wpec :
		$wpsc_cart->empty_cart();
	}
	return $message;
} // Fin de la fonction d'affichage du shortcode




// Mailchimp :

/** 
* Mailchimp options
*/  
function wpcb_intialize_mailchimp_options() {  
    if(false == get_option( 'wpcb_mailchimp' )){add_option( 'wpcb_mailchimp' );}
	add_settings_section('mailchimp_settings_section','mailchimp Options','wpcb_mailchimp_callback','wpcb_mailchimp');
	add_settings_field('add_to_mailchimp','Ajouter les clients à Mailchimp','wpcb_add_to_mailchimp_callback','wpcb_mailchimp','mailchimp_settings_section');
	add_settings_field('listid','List ID','wpcb_listid_mailchimp_callback','wpcb_mailchimp','mailchimp_settings_section');
	add_settings_field('apikey','Clé API MailChimp','wpcb_apikey_mailchimp_callback','wpcb_mailchimp','mailchimp_settings_section');
	register_setting('wpcb_mailchimp','wpcb_mailchimp','');
} 
add_action( 'admin_init', 'wpcb_intialize_mailchimp_options' );  



function wpcb_mailchimp_callback() {  
    echo '<p>Réglage des options pour le paiement par mailchimp bancaire</p>';  
}


function wpcb_add_to_mailchimp_callback($args){  
    $options = get_option( 'wpcb_mailchimp');  
	$html = '<input type="checkbox" id="add_to_mailchimp" name="wpcb_mailchimp[add_to_mailchimp]" value="1" ' . checked(1, $options['add_to_mailchimp'], false) . '/>';  
    $html .= '<label for="add_to_mailchimp"> '  . $args[0] . '</label>';   
    echo $html;
}

function wpcb_listid_mailchimp_callback(){  
    $options = get_option( 'wpcb_mailchimp');  
    $val ="b2c48b296a"; 
    if(isset($options['listid'])){$val = $options['listid'];}
        echo '<input type="text"  size="75"id="listid" name="wpcb_mailchimp[listid]" value="' . $val . '" />';
}

function wpcb_apikey_mailchimp_callback(){  
    $options = get_option( 'wpcb_mailchimp');  
    $val ='g0ffbb747d15113611308102b53601ff-us2'; 
    if(isset($options['apikey'])){$val = $options['apikey'];}
        echo '<input type="text"  size="75"id="apikey" name="wpcb_mailchimp[apikey]" value="' . $val . '" />';
}


add_action('wpsc_submit_checkout','add_to_mailchimp');
function add_to_mailchimp($a){
	global $wpdb;
	$wpcb_mailchimp = get_option ( 'wpcb_mailchimp' );
	if ($wpcb_mailchimp['add_to_mailchimp']){
	$listid = $wpcb_mailchimp['listid'];
	$apikey=$wpcb_mailchimp['apiKey'];
	$log_id=$a['purchase_log_id'];
	$email_a = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_SUBMITED_FORM_DATA."` WHERE log_id=".$log_id." AND form_id=9 LIMIT 1",ARRAY_A);
	$lastname_a = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_SUBMITED_FORM_DATA."` WHERE log_id=".$log_id." AND form_id=3 LIMIT 1",ARRAY_A) ;
	$firstname_a = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_SUBMITED_FORM_DATA."` WHERE log_id=".$log_id." AND form_id=2 LIMIT 1",ARRAY_A) ;
	$email=$email_a['value'];$firstname=$firstname_a['value'];$lastname=$lastname_a['value'];
	if($email){   
		if(preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*$/i", $email)){
			require_once('MCAPI.class.php');	
			$api = new MCAPI($apikey);		
			$merge_vars = array('FNAME'=>$firstname,'LNAME'=>$lastname); 
			$api->listSubscribe($listid, $email,$merge_vars,'',false,true);
		}
	}
	}
}
function get_Signature($field,$key) {

		ksort($field); // tri des paramétres par ordre alphabétique
		$contenu_signature = "";
		foreach ($field as $nom => $valeur){
			if(substr($nom,0,5) == 'vads_') {
			$contenu_signature .= $valeur."+";
		}
		}
		$contenu_signature .= $key;	// On ajoute le certificat à la fin de la chaîne.
		$signature = sha1($contenu_signature);
		return($signature);
	}
	function Check_Signature($field,$key) {
		$result='false';
		$signature=get_Signature($field,$key);
		if(isset($field['signature']) && ($signature == $field['signature']))
		{	$result='true';}
		return ($result);
	}
	function uncharm($potentiallyMagicallyQuotedData) {
			if (get_magic_quotes_gpc()) {
				$sane = array();
				foreach ($potentiallyMagicallyQuotedData as $k => $v) {
					$saneKey = stripslashes($k);
					$saneValue = is_array($v) ? SystempayApi::uncharm($v) : stripslashes($v);
					$sane[$saneKey] = $saneValue;
				}
			} else {
				$sane = $potentiallyMagicallyQuotedData;
			}
			return $sane;
		}


add_action('init','check_ipn');
function check_ipn(){
	global $wpdb, $purchase_log, $wpsc_cart;
	$wpcb_atos = get_option ( 'wpcb_atos' );
	$wpcb_general = get_option( 'wpcb_general' );
	$wpcb_dev=get_option( 'wpcb_dev' );


	$purch_log_email=get_option('purch_log_email');
	if (!$purch_log_email){$purch_log_email=get_bloginfo('admin_email');}
	
	// Automatic Response ATOS :
	if ($_GET['ipn']=='atos'){
	if ((array_key_exists('mode_demo', $wpcb_dev)) && ($wpcb_dev['mode_demo'])){ // Ce Kit de demo a du vous etre envoyé par la banque
		$pathfile=dirname(dirname(dirname(dirname(dirname(__FILE__)))))."/cgi-bin/demo/pathfile";
		$path_bin_response=dirname(dirname(dirname(dirname(dirname(__FILE__)))))."/cgi-bin/demo/response";
		$logfile=dirname(dirname(dirname(dirname(dirname(__FILE__)))))."/cgi-bin/demo/logfile.txt";
	}
	else{
		$pathfile=$wpcb_atos['pathfile'];
		$path_bin_response=$wpcb_atos['path_bin_response'];
		$logfile=$wpcb_atos['logfile'];
	}
	
	// Initialisation du chemin du fichier de log :
	if (isset($_POST['DATA'])){
		$data=escapeshellcmd($_POST['DATA']);
		$message="message=$data";
		$pathfile="pathfile=".$pathfile;
		if ( isset($_POST['sandbox']) ){
			$result=$_POST['sandbox'];
		}
		else{
			$result=exec("$path_bin_response $pathfile $message");
		}
		$tableau = explode ("!", $result);
		$response=array(
		'code'=>$tableau[1],
		'error'=>$tableau[2],
		'merchantid'=>$tableau[3],
		'merchantcountry'=>$tableau[4],
		'amount'=>$tableau[5],
		'transactionid'=>$tableau[6],
		'paymentmeans'=>$tableau[7],
		'transmissiondate'=>$tableau[8],
		'paymenttime'=>$tableau[9],
		'paymentdate'=>$tableau[10],
		'responsecode'=>$tableau[11],
		'paymentcertificate'=>$tableau[12],
		'authorisationid'=>$tableau[13],
		'currencycode'=>$tableau[14],
		'cardnumber'=>$tableau[15],
		'cvvflag'=>$tableau[16],
		'cvvresponsecode'=>$tableau[17],
		'bankresponsecode'=>$tableau[18],
		'complementarycode'=>$tableau[19],
		'complementaryinfo'=>$tableau[20],
		'returncontext'=>$tableau[21],
		'caddie'=>$tableau[22],
		'receiptcomplement'=>$tableau[23],
		'merchantlanguage'=>$tableau[24],
		'language'=>$tableau[25],
		'customerid'=>$tableau[26],
		'orderid'=>$tableau[27],
		'customeremail'=>$tableau[28],
		'customeripaddress'=>$tableau[29],
		'captureday'=>$tableau[30],
		'capturemode'=>$tableau[31],
		'data'=>$tableau[32],
		);
	
		//Session id used by wp ecommerce :
		$sessionid=$response['orderid'];
	
		// A venir : Ajout dans un google spreadsheet qui a toutes les entêtes précédentes (requis Zend)
		// A coler dans la page admin pour tester
	
	
			if (WP_ZEND_FRAMEWORK){
				$GoogleConnection=true;
				$SpreadSheetConnection=true;
				try {$client = Zend_Gdata_ClientLogin::getHttpClient($wpcb_general['googleemail'],$wpcb_general['googlepassword']);}
				catch (Zend_Gdata_App_AuthException $ae){echo $ae->exception();$GoogleConnection=false;}
				if ($GoogleConnection){			
					// Test 
					$service=Zend_Gdata_Spreadsheets::AUTH_SERVICE_NAME;
					$client=Zend_Gdata_ClientLogin::getHttpClient($wpcb_general['googleemail'],$wpcb_general['googlepassword'], $service);
					// On va chercher le numéro de la feuille :
					$query_worksheet = new Zend_Gdata_Spreadsheets_DocumentQuery(); // todo pour pas de client ici ?
					$query_worksheet->setSpreadsheetKey($wpcb_general['spreadsheetKey']);
					$spreadsheetService = new Zend_Gdata_Spreadsheets($client);
					try {$feed = $spreadsheetService->getWorksheetFeed($query_worksheet);}
					catch (Zend_Gdata_App_HttpException $ae){echo $ae->exception();$SpreadSheetConnection=false;}
					if ($SpreadSheetConnection){
						// Tout bon on ajoute : 
						foreach($feed->entries as $entry){
							if ($entry->title->text=='CB'){$worksheetId_CB=basename($entry->id);}
							elseif ($entry->title->text=='Paypal'){$worksheetId_Paypal=basename($entry->id);}
						}
						$spreadsheetService = new Zend_Gdata_Spreadsheets($client);
						// Insert row in google spreadsheet :
						$insertedListEntry = $spreadsheetService->insertRow($response,$wpcb_general['spreadsheetKey'],$worksheetId_CB);
					}
					else{
						wp_mail($purch_log_email,'Email envoyé depuis le auto_response','Mauvais Numero de Spreadsheet dans les options du plugin wpcb');
					}
				}
				else {
					if (WP_DEBUG){
					wp_mail($purch_log_email,'Email envoyé depuis le auto_response','Mauvais login/mot de pass google dans les options du plugin wpcb');
					}
				}
			}
			else{
				wp_mail($purch_log_email,'Email envoyé depuis le auto_response','Installer Zend pour ajouter automatiquement les ventes à google drive !');
			}
		
	
		//  analyse du code retour
		if ( isset($_POST['sandbox']) ){
				$message="-----------SANDBOX-------------------------\n";
				foreach ($response as $k => $v) {
					$message.= $k." = ".$v."\n";
				}
				$message.="-------------------------------------------\n";
				if ($logfile){	
					$fp=fopen($logfile, "a");
					fwrite($fp,$message);
					fclose ($fp);
				}
				echo $message;
				wp_mail($purch_log_email,'Email pour vous dire qu\'un paiement SANDBOX est arrivé !',$message);
		} // Fin de l'achat sandbox
		else{ //Vrai achat !
		if (($response['code']=="") && ($response['error']=="")){
			$message="erreur appel response\n executable response non trouve $path_bin_response\n Session Id : $sessionid";
			if ($logfile){
				$fp=fopen($logfile,"a");			// Ouverture du fichier de log en append
				fwrite($fp,$message);
				fclose ($fp);
			}
			if (WP_DEBUG){
				wp_mail($purch_log_email,'Email envoyé depuis le auto_response car il y a une erreur avec un paiement Atos',$message);
			}
			$wpdb->query("UPDATE `".WPSC_TABLE_PURCHASE_LOGS."` SET `processed`= '5' WHERE `sessionid`=".$sessionid);
			$wpsc_cart->empty_cart();
		}
		elseif ($response['code']!=0){
			$message=" API call error.\n Error message :  $error\n Session Id : $sessionid";	
			if ($logfile){
				// Ouverture du fichier de log en append
				$fp=fopen($logfile, "a");
				fwrite($fp,$message);
				fclose ($fp); 
			}
			if (WP_DEBUG){
				wp_mail($purch_log_email,'Email envoyé depuis le auto_response car il y a une erreur avec un paiement Atos',$message);
			}
			$wpdb->query("UPDATE `".WPSC_TABLE_PURCHASE_LOGS."` SET `processed`= '5' WHERE `sessionid`=".$sessionid);
			$wpsc_cart->empty_cart();
		 }
		else{
			// Ok, Sauvegarde dans la base de donnée du shop.
			if ($response_code==00) {
				$message="-----------SALES----------------------------\n";
				foreach ($response as $k => $v) {
					$message.= $k." = ".$v."\n";
				}
				$message.="-------------------------------------------\n";
				if ($logfile){	
					$fp=fopen($logfile, "a");
					fwrite($fp,$message);
					fclose($fp);
				}
				if (WP_DEBUG){
					wp_mail($purch_log_email,'Email pour vous dire qu\'un paiement est arrivé !',$message);
				}
				$wpdb->query("UPDATE `".WPSC_TABLE_PURCHASE_LOGS."` SET `processed`= '3' WHERE `sessionid`=".$sessionid);
				$purchase_log = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `sessionid`= ".$sessionid." LIMIT 1",ARRAY_A) ; // Ne pas enlever car global !
				$wpsc_cart->empty_cart();
				// Peut-être faut-il ici decrease stock ???
				// redirect ->
				transaction_results($sessionid,false);
				// false -> no echo ! // The cart is emptied in this function a condition d'avoir la global $wpsc_cart !
			}
		}
	}// Fin du vrai achat
		}// Fin du isset POST DATA
	else{
		if (WP_DEBUG){
				wp_mail($purch_log_email,'Qqn a accéder à cette page sans utiliser le module de CB','Rien de grave, c\'est peut-etre un robot google !');
			}
	}
	}// Fin du atos
	elseif ($_GET['ipn']=='systempaycyberplus'){
				$message='';
				 foreach($_POST as $key => $value){
					$message.= $key.'->'.$value."\n";
				}
				mail($purch_log_email,'ipn systempay cyberplus',$message);
				$wpcb_systempaycyberplus = get_option('wpcb_systempaycyberplus');
				$control = Check_Signature(uncharm($_POST),$wpcb_systempaycyberplus['certificat']);
				if($control == 'true'){
					$sessionid=$_POST['vads_order_id'];
					if($_POST['vads_result'] == "00"){
						switch ($_POST['vads_auth_mode']){
							case "FULL":
								$wpdb->query("UPDATE `".WPSC_TABLE_PURCHASE_LOGS."` SET `processed`= '3' WHERE `sessionid`=".$sessionid);
								$purchase_log = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `sessionid`= ".$sessionid." LIMIT 1",ARRAY_A) ; // Ne pas enlever car global !
								$wpsc_cart->empty_cart();
								// Peut-être faut-il ici decrease stock ???
								// redirect ->
								transaction_results($sessionid,false);					
							break;
							case "MARK":
							break;
						}
					}
					else{	//failed
						$wpdb->query("UPDATE `".WPSC_TABLE_PURCHASE_LOGS."` SET `processed`= '5' WHERE `sessionid`=".$sessionid);		
						$wpsc_cart->empty_cart();
						//http://matale.fr/?cbListener=systempay_cyberplus&mode=test
						}
				}
				else {
				mail($purch_log_email,'ipn systempay cyberplus signature non valide',$message);
				}
				if($transauthorised==false){

				}			
}// Fin du ipn=systempaycyberplus
elseif ($_GET['ipn']=='paypal'){
	$wpcb_paypal = get_option ( 'wpcb_paypal' );

// Initialisation du chemin du fichier de log :

error_reporting(E_ALL ^ E_NOTICE); 
$header = ""; 
$emailtext = ""; 
// Read the post from PayPal and add 'cmd' 
$req = 'cmd=_notify-validate'; 
if(function_exists('get_magic_quotes_gpc')){$get_magic_quotes_exits = true;} 
foreach ($_POST as $key => $value){
// Handle escape characters, which depends on setting of magic quotes 
	if($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1)
		{$value = urlencode(stripslashes($value));}
	else {$value = urlencode($value);}
	$req .= "&$key=$value";
} 
// Post back to PayPal to validate 
$header .= "POST /cgi-bin/webscr HTTP/1.0\r\n"; 
$header .= "Content-Type: application/x-www-form-urlencoded\r\n"; 
$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
if ($wpcb_paypal['sandbox_paypal']){ 
	$fp = fsockopen ('ssl://sandbox.paypal.com', 443, $errno, $errstr, 30);
}
else{
	$fp = fsockopen ('ssl://www.paypal.com', 443, $errno, $errstr, 30);			
}

// Process validation from PayPal 
if (!$fp){ // HTTP ERROR
	}
else{
	// NO HTTP ERROR 
	fputs ($fp, $header . $req); 
	while (!feof($fp)){
		$res = fgets ($fp, 1024); 
		if (strcmp ($res, "VERIFIED") == 0)	{
			if ($_POST['payment_status']=='Completed'){
				if (WP_DEBUG){
					wp_mail($purch_log_email, "IPN Completed Payement",$req);
				}
				$sessionid=$_POST['item_number'];
				$wpdb->query("UPDATE `".WPSC_TABLE_PURCHASE_LOGS."` SET `processed`= '3' WHERE `sessionid`=".$sessionid);
				$purchase_log = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `sessionid`= ".$sessionid." LIMIT 1",ARRAY_A) ;
				transaction_results($sessionid,false); // false -> no echo !
			}//End if completed
		}
		elseif (strcmp ($res, "INVALID") == 0){
			// If 'INVALID', send an email. TODO: Log for manual investigation. 
			if (WP_DEBUG){
				wp_mail($purch_log_email, "Live-INVALID IPN",$req);
			}
			$wpdb->query("UPDATE `".WPSC_TABLE_PURCHASE_LOGS."` SET `processed`= '5' WHERE `sessionid`=".$sessionid);
		}  
	}
	fclose ($fp);
}
	
} // fin du ipn=paypal

}// fin de la function check ipn

?>