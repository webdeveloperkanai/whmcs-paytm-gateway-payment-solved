<?php	

include("../../../init.php");
include("../../../includes/functions.php");
include("../../../includes/gatewayfunctions.php");
include("../../../includes/invoicefunctions.php");

require_once(dirname(__FILE__) . '/../paytm-sdk/encdec_paytm.php');
$gatewaymodule = "paytm"; 
$GATEWAY = getGatewayVariables($gatewaymodule);

$response = array();
$response = $_POST;   

// echo "<pre>"; 
// print_r($_REQUEST); 

  
if(isset($response['ORDERID']) && isset($response['STATUS']) && isset($response['RESPCODE']) && $response['RESPCODE'] != 325){

	$txnid_arr  = explode('_',$response['ORDERID']);
	$tnxid = $txnid_arr[0];
	$txnid  = checkCbInvoiceID($response['ORDERID'],'paytm');	
	$status =$response['STATUS'];
	$paytm_trans_id = $response['TXNID'];
	$checksum_recv='';	
	$amount=$response['TXNAMOUNT'];
	if(isset($response['CHECKSUMHASH'])){
		$checksum_recv=$response['CHECKSUMHASH'];
	}
	
	checkCbTransID($paytm_trans_id); 
	
	$checksum_status = verifychecksum_e($response, html_entity_decode($GATEWAY['merchant_key']), $checksum_recv);
	
// 	echo 'Check sum - '.$checksum_status; 
	
	// Create an array having all required parameters for status query.
	$requestParamList = array("MID" => $GATEWAY['merchant_id'] , "ORDERID" => $response['ORDERID']);
	$StatusCheckSum = getChecksumFromArray($requestParamList, html_entity_decode($GATEWAY['merchant_key']));
	$requestParamList['CHECKSUMHASH'] = $StatusCheckSum;
	
	// Call the PG's getTxnStatus() function for verifying the transaction status.
	$check_status_url = $GATEWAY['transaction_status_url'];
	
	if($status== 'TXN_SUCCESS' && $checksum_status == "TRUE"){	
		$responseParamList = callNewAPI($check_status_url, $requestParamList);
// 		echo "Response Pr <pre>"; 
// 		    print_r($responseParamList); 
		
// 		if($responseParamList['STATUS']=='TXN_SUCCESS' && $responseParamList['TXNAMOUNT']==$response['TXNAMOUNT'])
// 		{
			$gatewayresult = "success";
			addInvoicePayment($txnid, $paytm_trans_id, $amount,"0.0", $gatewaymodule); 
			logTransaction($GATEWAY["name"], $response, $response['RESPMSG']);
			
			echo "<h1>Payment done </h1> <script>location.href='https://hosting.devsecit.com/viewinvoice.php?id=$txnid'</script> ";
// 		}
// 		else{
// 			logTransaction($GATEWAY["name"], $response, "It seems some issue in server to server communication. Kindly connect with administrator.");
// 			echo "It seems some issue in server to server communication. Kindly connect with administrator.";
// 		}
	} elseif ($status == "TXN_SUCCESS" && $checksum_status != "TRUE") {
		logTransaction($GATEWAY["name"], $response, "Checksum Mismatch");
		echo "Checksum Mismatch";
	}else {
		logTransaction($GATEWAY["name"], $response, $response['RESPMSG']); 
	}
	
	$returnResponse=(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http")."://$_SERVER[HTTP_HOST]$_SERVER[SCRIPT_NAME]";
	$filename=str_replace('modules/gateways/callback/paytm.php','viewinvoice.php?id='.$txnid, $returnResponse);
    
    echo $filename; 
   
   //header("Location: $filename");
}
else{

 
	$returnResponse=(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http")."://$_SERVER[HTTP_HOST]$_SERVER[SCRIPT_NAME]";
	$location=str_replace('modules/gateways/callback/paytm.php','', $returnResponse);
	
	 echo "<h1>Payment failed </h1>";
// 	header("Location: $location");
}
?>
