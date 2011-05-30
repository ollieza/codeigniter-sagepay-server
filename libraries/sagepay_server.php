<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * 	Sage Pay Server Class
 *	Handles SagePay Server payments
 *
 * This CodeIgniter library to integrate the Sage Pay Go Form service
 * http://www.sagepay.com/products_services/sage_pay_go/integration/server
 * 
 * @package	sagepay_server
 * @author    Ollie Rattue, Too many tabs <orattue[at]toomanytabs.com>
 * @copyright Copyright (c) 2011, Ollie Rattue
 * @license   http://www.opensource.org/licenses/mit-license.php
 * @link      http://github.com/ollierattue/codeigniter-sagepay-server
 */

class sagepay_server
{
	var $eoln;
	var $config;
	var $CI;
		
	/**
	 * Constructor
	 *
	 * @access	public
	 */

	public function __construct()
	{
		if (!isset($this->CI))
		{
			$this->CI =& get_instance();
		}

		// Load config		
		$this->CI->load->config('sagepay_server_config', TRUE);
		$this->config = $this->CI->config->item('sagepay_server_config');
		
		$this->CI->load->model('sagepay_server_model', 'sagepay_server_model');
		
		$this->transaction['VPSProtocol'] = $this->config['protocol'];
		$this->transaction['TxType'] = $this->config['transactiontype'];
		$this->transaction['Currency'] = $this->config['currency'];
		$this->transaction['Vendor'] = $this->config['vendorname'];
		$this->transaction['NotificationUrl'] = "{$this->config['your_site_fqdn']}sagepay_notification";
		$this->transaction['Description'] = 'Online purchase';
		$this->transaction['AllowGiftAid'] = 0;
		$this->transaction['ApplyAVSCV2'] = 0;
		$this->transaction['Apply3DSecure'] = 0;
		$this->transaction['Profile'] = 'NORMAL'; //NORMAL is default setting. Can also be set to LOW for the simpler payment page version.
		$this->transaction['AccountType'] = 'E'; // E = Use the e-commerce merchant account (default).
		
		log_message('debug', "Sage Pay Server Class Initialized");
		
		// Define end of line character used to correctly format response to Sage Pay Server
		$this->eoln = chr(13) . chr(10);
	}

	// --------------------------------------------------------------------
	
	/**
	 * 	New tranasction
	 *
	 * 	Send a post request with cURL
	 *	$url = URL to send request to
	 *	$data = POST data to send (in URL encoded Key=value pairs)
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @return	mixed
	 */
	
	public function new_transaction($VendorTxCode = NULL, $TxType = 'PAYMENT')
	{
		$this->transaction['VendorTxCode'] = $VendorTxCode;
		
		// Automatically set the URL 

		switch($TxType)
		{
			case('payment'):
				$url = $this->config['purchaseurl'];
			break;

			case('repeat'):
				$url = $this->config['repeaturl'];
				$this->transaction['TxType'] = 'REPEAT';
			break;

			default:
				return FALSE;
			break;
		}

		if ($this->transaction['TxType'] == 'payment') // there is no client ip address for repeats
		{
			$this->transaction['ClientIPAddress'] = $this->CI->input->server("REMOTE_ADDR");
		}

		if (isset($this->transaction['Description'])) // Description cannot be longer than 100 characters
		{
			$description_truncated = $this->truncate($this->transaction['Description'], 100); 

			if ($description_truncated) // original string is over 100 chars
			{
				$this->transaction['Description'] = $description_truncated;
			}
		}
		
		// Create an array to save to the db
		$transaction_details_for_db = array(
		               				'VendorTxCode' 			=> $this->transaction['VendorTxCode'],
		               				'TxType' 				=> $this->transaction['TxType'],
		               				'Amount' 				=> $this->transaction['Amount'],
		            				'Currency'	 			=> $this->transaction['Currency'],
		 							'BillingFirstnames'		=> $this->transaction['BillingFirstnames'],
									'BillingSurname'		=> $this->transaction['BillingSurname'],
									'BillingAddress1'		=> $this->transaction['BillingAddress1'],
									'BillingAddress2'		=> $this->transaction['BillingAddress2'],
									'BillingCity'			=> $this->transaction['BillingCity'],
									'BillingPostCode'		=> $this->transaction['BillingPostCode'],
									'BillingCountry'		=> $this->transaction['BillingCountry'],
									'BillingState'			=> $this->transaction['BillingState'],
									'BillingPhone'			=> $this->transaction['BillingPhone'],
									'DeliveryFirstnames'	=> $this->transaction['DeliveryFirstnames'],
									'DeliverySurname'		=> $this->transaction['DeliverySurname'],
									'DeliveryAddress1'		=> $this->transaction['DeliveryAddress1'],
									'DeliveryAddress2'		=> $this->transaction['DeliveryAddress2'],
									'DeliveryCity'			=> $this->transaction['DeliveryCity'],
									'DeliveryPostCode'		=> $this->transaction['DeliveryPostCode'],
									'DeliveryCountry'		=> $this->transaction['DeliveryCountry'],
									'DeliveryState'			=> $this->transaction['DeliveryState'],
									'DeliveryPhone'			=> $this->transaction['DeliveryPhone'],
									'CustomerEmail'			=> $this->transaction['CustomerEmail']
									);
		
		$this->CI->sagepay_server_model->add_transaction($transaction_details_for_db);
		
		/* 	For debugging
		print_r($this->transaction);
		exit;
		*/
		
		// Format data for post
		$data = http_build_query($this->transaction);

		// Set a one-minute timeout for this script
		set_time_limit(60);

		// Initialise output variable
		$output = array();

		// Open the cURL session
		$curlSession = curl_init();

		// Set the URL
		curl_setopt ($curlSession, CURLOPT_URL, $url);
		// No headers, please
		curl_setopt ($curlSession, CURLOPT_HEADER, 0);
		// It's a POST request
		curl_setopt ($curlSession, CURLOPT_POST, 1);
		// Set the fields for the POST
		curl_setopt ($curlSession, CURLOPT_POSTFIELDS, $data);
		// Return it direct, don't print it out
		curl_setopt($curlSession, CURLOPT_RETURNTRANSFER,1); 
		// This connection will timeout in 30 seconds
		curl_setopt($curlSession, CURLOPT_TIMEOUT,30); 
		//The next two lines must be present for the kit to work with newer version of cURL
		//You should remove them if you have any problems in earlier versions of cURL
	    curl_setopt($curlSession, CURLOPT_SSL_VERIFYPEER, FALSE);
	    curl_setopt($curlSession, CURLOPT_SSL_VERIFYHOST, 1);

		// Send the request and store the result in an array
		$rawresponse = curl_exec($curlSession);

		/* For debugging
		 Store the raw response for later as it's useful to see for integration and understanding 
		 $this->CI->session->set_userdata('rawrespons', $rawresponse);
		*/
		
		// Split response into name=value pairs
		$response = split(chr(10), $rawresponse);

		// Check that a connection was made
		if (curl_error($curlSession))
		{
			// If it wasn't...
			$output['Status'] = "FAIL";
			$output['StatusDetail'] = curl_error($curlSession);
		}

		// Close the cURL session
		curl_close ($curlSession);

		// Tokenise the response
		for ($i=0; $i<count($response); $i++)
		{
			// Find position of first "=" character
			$splitAt = strpos($response[$i], "=");
			// Create an associative (hash) array with key/value pairs ('trim' strips excess whitespace)
			$output[trim(substr($response[$i], 0, $splitAt))] = trim(substr($response[$i], ($splitAt+1)));
		} 

		// Return the output
		return $output;
	}

	// --------------------------------------------------------------------
	
	/**
	 * 	Process transaction response
	 *
	 *	Process response returned by send_request_post($type, $data)
	 *	$sagepay_response = array returned by SagePay
	 *
	 * @access	public
	 * @param	array
	 * @param	BOOL
	 * @return	mixed
	 */
	
	public function process_transaction_response($sagepay_response = NULL, $repeat = FALSE)
	{
		/* // For debugging
		print_r($sagepay_response);
		exit;
		/*
		
		/* Analyse the response from Sage Pay Server to check that everything is okay
		** Registration results come back in the Status and StatusDetail fields */
		$status = $sagepay_response["Status"];
		$status_detail = $sagepay_response["StatusDetail"];

		/** Caters for both OK and OK REPEATED if the same transaction is registered twice **/
		if (substr($status, 0, 2)=="OK")
		{
			/** An OK status mean that the transaction has been successfully registered **
			** Your code needs to extract the VPSTxId (Sage Pay's unique reference for this transaction) **
			** and the SecurityKey (used to validate the call back from Sage Pay later) and the NextURL **
			** (the URL to which the customer's browser must be redirected to enable them to pay) **/

			/** Now store the VPSTxId, SecurityKey, VendorTxCode, order total and order details in **
			** your database for use both at Notification stage, and your own order fulfilment **/

			$response_array = array(
							'VPSTxId' 		=> $sagepay_response['VPSTxId'], //Save the Sage Pay System's unique transaction reference
							'SecurityKey'	=> $sagepay_response['SecurityKey'], //Save the MD5 Hashing security key, used in notification
							'TxType'		=> $this->config['transactiontype'],
							'Currency'		=> $this->config['currency']
							);

			if ($repeat)
			{
				$response_array['TxAuthNo'] = $sagepay_response['TxAuthNo'];
				$response_array['TxType'] = 'REPEAT';
				$response_array['Status'] = 'AUTHORISED - The transaction was successfully authorised with the bank.';

			}

			$this->CI->sagepay_server_model->update_transaction($response_array, $this->transaction['VendorTxCode']);

			if ($repeat) // REPEATS are run by cron so we have no user to forward to a sagepay payment page
			{
				return TRUE; // if we are here we have done a repeat which has been a success
			}

			$next_url = $sagepay_response["NextURL"];

			/** Finally, if we're not in Simulator Mode, redirect the page to the NextURL **
			** In Simulator mode, we allow this page to display and ask for Proceed to be clicked **/

			// if ($this->config['connect_to'] !== "SIMULATOR")
			// 		{
				$this->external_redirect($next_url);
				exit();			
			//}
		}	
		elseif ($status == "MALFORMED")
		{	
			/** A MALFORMED status occurs when the POST sent above is not correctly formatted **
			** or is missing compulsory fields.  You will normally only see these during **
			** development and early testing **/
			$page_error = "Sage Pay returned an MALFORMED status. The POST was Malformed because \"{$status_detail}\"";		
		}
		elseif ($status == "INVALID")
		{
			/** An INVALID status occurs when the structure of the POST was correct, but **
			** one of the fields contains incorrect or invalid data.  These may happen when live **
			** but you should modify your code to format all data correctly before sending **
			** the POST to Sage Pay Server **/
			$page_error = "Sage Pay returned an INVALID status. The data sent was Invalid because \"{$status_detail}\"";
		}
		else
		{
			/** The only remaining status is ERROR **
			** This occurs extremely rarely when there is a system level error at Sage Pay **
			** If you receive this status the payment systems may be unavailable **<br>
			** You could redirect your customer to a page offering alternative methods of payment here **/
			$page_error = "Sage Pay returned an ERROR status. The description of the error was \"{$status_detail}\"";
		}

		if ($repeat) // if we are here we have done a repeat which has been a failure
		{
			$response_array = array(
							'VPSTxId' 		=> $sagepay_response['VPSTxId'], //Save the Sage Pay System's unique transaction reference
							'SecurityKey'	=> $sagepay_response['SecurityKey'], //Save the MD5 Hashing security key, used in notification
							'Currency'		=> $this->config['currency'],
							'Status'		=> "{$status} - {$status_detail}"
							);

			$this->CI->sagepay_model->update_transaction($response_array, $this->VendorTxCode);

			return FALSE;
		}

		echo $page_error;
	}

	// --------------------------------------------------------------------
	
	public function set_field($field = NULL, $value = NULL)
	{
		$this->transaction["{$field}"] = $value;
	}

	// --------------------------------------------------------------------
	
	public function set_same_delivery_address()
	{
		$this->transaction['DeliveryFirstnames'] = $this->transaction['BillingFirstnames'];
		$this->transaction['DeliverySurname'] = $this->transaction['BillingSurname'];
		$this->transaction['DeliveryAddress1'] = $this->transaction['BillingAddress1'];
		$this->transaction['DeliveryAddress2'] = $this->transaction['BillingAddress2'];
		$this->transaction['DeliveryCity'] = $this->transaction['BillingCity'];
		$this->transaction['DeliveryPostCode'] = $this->transaction['BillingPostCode'];
		$this->transaction['DeliveryCountry'] = $this->transaction['BillingCountry'];
		$this->transaction['DeliveryState'] = $this->transaction['BillingState'];
		$this->transaction['DeliveryPhone'] = $this->transaction['BillingPhone'];
	}
	
	// --------------------------------------------------------------------

	/**
	 * 	Create VendorTXcode
	 *
	 *	Creates a unique string
	 *  Called by controller and the value would be stored in db against the purchase
	 * 
	 * @access	public
	 * @return	string
	 */

	public function create_vendor_tx_code()
	{
		$timestamp = date("y-m-d-H-i-s", time());
		$random_number = rand(0,32000)*rand(0,32000);
		$VendorTxCode = "{$timestamp}-{$random_number}";

		return $VendorTxCode;
	}	

	// --------------------------------------------------------------------
	
	// Original PHP code by Chirp Internet: www.chirp.com.au // Please acknowledge use of this code by including this header. 
	function truncate($string, $limit, $break=" ", $pad="...")
	{
		// return with no change if string is shorter than $limit  
		
		if (strlen($string) <= $limit)
		{
			return FALSE;
		}
		
		$string = substr($string, 0, $limit); 
		
		if (FALSE !== ($breakpoint = strrpos($string, $break)))
		{
			$string = substr($string, 0, $breakpoint); 
		} 
		
		return "{$string}{$pad}"; 
	}

	// --------------------------------------------------------------------
	
	// Redirect browser to an external URL
	function external_redirect($url)
	{
	   	if (!headers_sent())
		{
			header('Location: '.$url);
		}
	   	else
	   	{
			echo '<script type="text/javascript">';
	    	echo 'window.location.href="'.$url.'";';
	       	echo '</script>';
	       	echo '<noscript>';
	       	echo '<meta http-equiv="refresh" content="0;url='.$url.'" />';
	       	echo '</noscript>';
	   }
	}

	// --------------------------------------------------------------------
}

/* End of file sagepay_server.php */
/* Location: ./application/libraries/sagepay_server.php */