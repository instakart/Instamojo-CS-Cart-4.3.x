<?php
use Tygh\Registry;

function instamojo_error_logger($msg, $add_newline=TRUE){

    $base_dir = dirname(dirname(dirname(__FILE__)));
    $LOG_FILE = $base_dir . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'log' . DIRECTORY_SEPARATOR . 'imojo.log';
    date_default_timezone_set('Asia/Kolkata');
    $date = date('m/d/Y h:i:s a', time());

    $msg = $date . " | " . $msg;

    if($add_newline){
        $msg .= "\n";
    }
    error_log($msg, 3, $LOG_FILE);
}

function Redirect($url, $permanent = false)
{
    if (headers_sent() === false)
    {
        header('Location: ' . $url, true, ($permanent === true) ? 301 : 302);
    }

    exit();
}


if (defined('PAYMENT_NOTIFICATION')) {
    if ($mode == 'process') {
        if(isset($_REQUEST['payment_id']) or isset($_REQUEST['id']))
		{
			$payment_id = $_REQUEST['payment_id'];
			$payment_request_id = $_REQUEST['id'];
			
		}else 
		{
			$payment_id = $payment_request_id = "";
		}
        instamojo_error_logger("Callback called with Payment ID: " . $payment_id . " and Payment Request ID $payment_request_id");
		$stored_pri = "";
		if(isset($_SESSION['instamojo_payment_request_id']))
			$stored_pri =  $_SESSION['instamojo_payment_request_id'];
		
		if(!$payment_id or !$payment_request_id )
		{
	        instamojo_error_logger("Callback called with no Payment ID: " . $payment_id . " or Payment Request ID $payment_request_id");
			Redirect(Registry::get('config.current_location'));
		}
		if($stored_pri != $payment_request_id)
		{
	        instamojo_error_logger("Stored Payment request id ($stored_pri) is not matched with passed payment request id ($payment_request_id)");
			Redirect(Registry::get('config.current_location'));
		}
		
		# fetch instamojo credintials from db.
        $processor_details = fn_get_processor_data_by_name('instamojo.php');
        $pdata = db_get_row("SELECT * FROM ?:payments WHERE processor_id = ?i", $processor_details['processor_id']);
        instamojo_error_logger("Instamojo processor details from DB: ". print_r($pdata, true));
        $processor_data['processor_params'] = unserialize($pdata['processor_params']);
		$client_id = $processor_data['processor_params']['instamojo_client_id'];
		$client_secret = $processor_data['processor_params']['instamojo_client_secret'];
		$testmode = $processor_data['processor_params']['instamojo_testmode'];
		instamojo_error_logger("Instamojo Settings are CLinet id = $client_id | client secret = $client_secret } Testmode = $testmode");
		
		try{			
			include_once DIR_ROOT . "/app/addons/instamojo/lib/Instamojo.php";

			$api = new Instamojo($client_id,$client_secret,$testmode);
				
			$response = $api->getOrderById($payment_request_id);
			instamojo_error_logger("Response from server ".print_r($response,true));
			$payment_status = $response->payments[0]->status;
			if($payment_status == "successful" OR  $payment_status =="failed" )
			{
				$order_id = $response->transaction_id;
				$order_id = explode("-",$order_id);
				$order_id = $order_id[1];
				instamojo_error_logger("Extracted order id from trasaction_id: ".$order_id);
				$order_info = fn_get_order_info($order_id);            

				if($payment_status == "successful")
				{
					$pp_response['order_status'] = 'P';
					$pp_response['transaction_id'] = $payment_id;
					instamojo_error_logger("Payment was credited with Payment ID :$payment_id");
					if (fn_check_payment_script('instamojo.php', $order_id)){
						fn_finish_payment($order_id, $pp_response, false);
					  fn_order_placement_routines("route",$order_id);
					}
					
				}
				else if ($payment_status =="failed")
				{
					$pp_response = array();
					$pp_response['order_status'] = 'F';
					$pp_response['transaction_id'] = $payment_id;
					if (fn_check_payment_script('instamojo.php', $order_id)){
						fn_finish_payment($order_id, $pp_response, false);
					  fn_order_placement_routines("route",$order_id);
		
					}
				}					
			}
			else
			{
				instamojo_error_logger("Unimplemented Payment Status $payment_status");
				Redirect(Registry::get('config.current_location'));
			}

		}catch(Exception $e){
			instamojo_error_logger("Exception Occcured during Payment Confirmation with message ".$e->getMessage());
			Redirect(Registry::get('config.current_location'));
		}
		exit;
	}   
      
} else {
    $post_data = array();
    instamojo_error_logger("Creating New order");
	// if error occured.
	//fn_set_notification('O', "Error!", "Invalid Indian Mobile Number ", true, 'insecure_password');
	//Redirect("checkout");
	
	//fn_order_placement_routines('checkout');
	# currency related settngs.
	$currencies = Registry::get('currencies');
	if(!isset($processor_data['processor_params']['instamojo_currency_code']))
		$processor_data['processor_params']['instamojo_currency_code'] = "INR";
    $currency_code = $processor_data['processor_params']['instamojo_currency_code'];
	
    instamojo_error_logger("Currency code fetched from settings: $currency_code");

    $client_id = $processor_data['processor_params']['instamojo_client_id'];
    $client_secret = $processor_data['processor_params']['instamojo_client_secret'];
    $testmode = $processor_data['processor_params']['instamojo_testmode'];
	instamojo_error_logger("Instamojo Settings are CLinet id = $client_id | client secret = $client_secret } Testmode = $testmode");
	
	$api_data['email'] = substr($order_info['email'], 0, 75);
	$api_data['phone'] =  substr($order_info['phone'], 0, 20);
	$api_data['name'] =  substr(trim($order_info['b_firstname'] . ' ' . $order_info['b_lastname']), 0, 75);
	if($currency_code !="INR")
		$api_data['amount'] =  fn_format_price($order_info['total'] / $currencies[$currency_code]['coefficient']);
	else
		$api_data['amount'] = fn_format_price($order_info['total']);
	$api_data['currency'] = "INR";
	$api_data['transaction_id'] = time()."-".$order_id;
	$api_data['redirect_url'] =  Registry::get('config.current_location')."/index.php?dispatch=payment_notification.process&payment=instamojo";	
    instamojo_error_logger("Data sending to instamojo for creating order".print_r($api_data,true));
	try{
		include_once DIR_ROOT."/app/addons/instamojo/lib/Instamojo.php";
		
		$api = new Instamojo($client_id,$client_secret,$testmode);
		$response = $api->createOrderPayment($api_data);
		instamojo_error_logger("Response from server ".print_r($response,true));
		if(isset($response->order ))
		{
			$redirectUrl = $response->payment_options->payment_url;
			$_SESSION['instamojo_payment_request_id'] = $response->order->id;
			instamojo_error_logger("Marking Order: $order_id as open before redirecting to Instamojo for payment.");
			fn_change_order_status($order_id, 'O');
			Redirect($redirectUrl);
		}
	}catch(CurlException $e){
		// handle exception releted to connection to the sever
		instamojo_error_logger((string)$e);
		fn_set_notification('O', "Error!", "Could not able to connect with Instamojo. Please Try again later ", false, 'payment_method');
		Redirect("checkout");
	}catch(ValidationException $e){
		// handle exceptions releted to response from the server.
		instamojo_error_logger($e->getMessage()." with ");
		instamojo_error_logger(print_r($e->getResponse(),true)."");
		$errors = "";
		foreach($e->getErrors() as $error )
			$errors .= "$error<br/>";
		fn_set_notification('O', "Error!", $errors, false, 'payment_method');
		Redirect("checkout");
	}catch(Exception $e)
	{ // handled common exception messages which will not caught above.
		
		instamojo_error_logger('Error While Creating Order : ' . $e->getMessage());
		fn_set_notification('O', "Error!", "Some error occured", false, 'payment_method');
		Redirect("checkout");
	}
			
	
	exit;

//exit;
}


