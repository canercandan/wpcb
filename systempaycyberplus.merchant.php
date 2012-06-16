<?php
$wpcb_systempaycyberplus_options = get_option ( 'wpcb_systempaycyberplus_options' );
$nzshpcrt_gateways[$num] = array(
'name' => 'CB Systempay Cyberplus (WPCB)',
'api_version' => 2.0,
'class_name' => 'wpsc_merchant_systempaycyberplus',
'has_recurring_billing' => true,
'display_name' => 'Cartes Bancaires (Systempay Cyberplus) ',
'wp_admin_cannot_cancel' => false,
'requirements' => array(),'form' => 'form_systempaycyberplus',
'internalname' => 'systempaycyberplus',
'submit_function' => 'submit_systempaycyberplus',
'image' => $wpcb_systempaycyberplus_options['wpec_gateway_image_systempaycyberplus']
);

class wpsc_merchant_systempaycyberplus extends wpsc_merchant {
	function submit(){
		global $wpdb,$purchase_log;
		$sessionid=$this->cart_data['session_id'];
		$simple_paypal_checkout_page=$wpdb->get_row("SELECT ID FROM $wpdb->posts WHERE `post_content` LIKE '%[wpcb]%' AND `post_status`='publish' LIMIT 1");
		wp_redirect(site_url('?p='.$simple_paypal_checkout_page->ID.'&action=systempaycyberplus&sessionid='.$sessionid));
		exit;
	}// end of submit
} //end of class

function form_systempaycyberplus() {
	$output='<a href="'.admin_url( 'plugins.php?page=wpcb&tab=systempaycyberplus').'">Cliquez ici pour les réglages</a>';
	return $output;
}

function submit_systempaycyberplus(){return true;}
?>