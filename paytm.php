<?php

require_once(dirname(__FILE__) . '/paytm-sdk/encdec_paytm.php');


define('PAYTM_ENVIRONMENT', 'PROD'); // PROD
define('PAYTM_MERCHANT_KEY', 'qQc0Yass7juf9h'); //Change this constant's value with Merchant key downloaded from portal
define('PAYTM_MERCHANT_MID', 'QOAI74411194127308746');  
define('PAYTM_MERCHANT_WEBSITE', 'DEFAULT');  

$PAYTM_DOMAIN = "pguat.paytm.com";
if (PAYTM_ENVIRONMENT == 'PROD') {
	$PAYTM_DOMAIN = 'secure.paytm.in';
}
define('PAYTM_REFUND_URL', 'https://'.$PAYTM_DOMAIN.'/oltp/HANDLER_INTERNAL/REFUND');
define('PAYTM_STATUS_QUERY_URL', 'https://'.$PAYTM_DOMAIN.'/oltp/HANDLER_INTERNAL/TXNSTATUS');
define('PAYTM_STATUS_QUERY_NEW_URL', 'https://'.$PAYTM_DOMAIN.'/oltp/HANDLER_INTERNAL/getTxnStatus');
define('PAYTM_TXN_URL', 'https://'.$PAYTM_DOMAIN.'/oltp-web/processTransaction');


$PAYTM_STATUS_QUERY_NEW_URL='https://securegw-stage.paytm.in/order/status';
$PAYTM_TXN_URL='https://securegw-stage.paytm.in/order/process';




function paytm_config(){
    $configarray = array(
		"FriendlyName" => array("Type" => "System", "Value"=>"Paytm"),
		"merchant_id" => array("FriendlyName" => "Merchant ID", "Type" => "text", "Size" => "20", ),
		"merchant_key" => array("FriendlyName" => "Merchant Key", "Type" => "text", "Size" => "16", ),
		"transaction_url" => array("FriendlyName" => "Transaction Url", "Type" => "text", "Size" => "90", ),
		"transaction_status_url" => array("FriendlyName" => "Transaction Status Url", "Type" => "text", "Size" => "90", ),
		"website" => array("FriendlyName" => "Website name", "Type" => "text", "Size" => "20", ),
		"industry_type" => array("FriendlyName" => "Industry Name", "Type" => "text", "Size" => "20", ),
	);		
	return $configarray;
}

function paytm_link($params) {	

	$merchant_id = PAYTM_MERCHANT_MID;
	$secret_key= PAYTM_MERCHANT_KEY;
	$order_id = $params['invoiceid'].'_'.date('dmY').time();
	$website= PAYTM_MERCHANT_WEBSITE;
	$industry_type= "Retail";
	$channel_id="WEB";	
	$transaction_url = PAYTM_TXN_URL;		
	$amount = $params['amount']; 
	$email = $params['clientdetails']['email'];
	$callBackLink=(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http")."://$_SERVER[HTTP_HOST]$_SERVER[SCRIPT_NAME]";
	$callBackLink=str_replace('cart.php', 'modules/gateways/callback/paytm.php', $callBackLink);
	$callBackLink=str_replace('viewinvoice.php', 'modules/gateways/callback/paytm.php', $callBackLink);
	
	$post_variables = Array(
          "MID" => $merchant_id,
          "ORDER_ID" => $order_id ,
          "CUST_ID" => $email,
          "TXN_AMOUNT" => $amount,
          "CHANNEL_ID" => $channel_id,
          "INDUSTRY_TYPE_ID" => $industry_type,
          "CALLBACK_URL" => $callBackLink,
          "WEBSITE" => $website
          );
	$checksum = getChecksumFromArray($post_variables, $secret_key);
	$companyname = 'paytm';

	$code='<form method="post" action='. $transaction_url .'>';
	foreach ($post_variables as $key => $value) {
		$code.='<input type="hidden" name="'.$key.'" value="'.$value. '"/>';
	}
	$code.='<input type="hidden" name="CHECKSUMHASH" value="'. $checksum . '"/><input type="submit" value="Pay with Paytm" /></form>';
 	return $code; 
}
?>
