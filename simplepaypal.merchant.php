<?php
$options = get_option('simplepaypal_options');
$nzshpcrt_gateways[$num] = array(
'name' => 'Paypal (WPCB)',
'api_version' => 2.0,
'class_name' => 'wpsc_merchant_simplepaypal',
'has_recurring_billing' => true,
'display_name' => $options['wpec_display_name'],
'wp_admin_cannot_cancel' => false,
'requirements' => array(),'form' => 'form_simplepaypal',
'internalname' => 'simplepaypal',
'submit_function' => 'submit_simplepaypal',
'image' => $options['wpec_gateway_image_paypal']
);

class wpsc_merchant_simplepaypal extends wpsc_merchant {
	function submit(){
		global $wpdb,$purchase_log;
		// Trouver la page où le shortcode [simple_paypal] se situe.
		// Bug si plusieurs fois le shortcode [simple_paypal], à résoudre
		$sessionid=$this->cart_data['session_id'];
		$simple_paypal_checkout_page=$wpdb->get_row("SELECT ID FROM $wpdb->posts WHERE `post_content` LIKE '%[wpcb]%' AND `post_status`='publish' LIMIT 1");
		wp_redirect(site_url('?p='.$simple_paypal_checkout_page->ID.'&action=paypal&sessionid='.$sessionid));
		exit;
	}// end of submit
} //end of class

function form_simplepaypal() {
	$output='<a href="'.admin_url( 'plugins.php?page=wpcb&tab=paypal').'">Cliquez ici pour les réglages</a>';
	return $output;
}

function submit_simplepaypal(){return true;}
?>