<?php

/**
 * Sage Pay Server notification
 *
 * This controller exists solely for SagePay to post back a success or failure 
 * notice after a payment. We send SagePay a payment_type so we can trigger
 * different actions based on the type of transaction (e.g. a membership renewal,
 * a shop purchase etc)
 *
 * @package	sagepay_server
 * @author    Ollie Rattue, Too many tabs <orattue[at]toomanytabs.com>
 * @copyright Copyright (c) 2011, Ollie Rattue
 * @license   http://www.opensource.org/licenses/mit-license.php
 * @link      http://github.com/ollierattue/codeigniter-sagepay-server
 */

class Sagepay_notification extends CI_Controller
{	
    public function __construct()
    {
        parent::__construct();	
		
		$this->load->library('sagepay_server');
		$this->load->model('sagepay_server_model');
	}
	
	// --------------------------------------------------------------------
	
	/**
	 *	
	 * 	Sagepay sends transaction status to this URL
	 *
	 */
	
	function index($purchase_type = NULL)
	{				
		/*		
		* This page handles the notification POSTs from Sage Pay Server.  It should be made externally visible
		* so that Sage Pay Server can send messages to over either HTTP or HTTPS.
		* The code validates the Sage Pay Server POST using MD5 hashing, updates the database accordingly,
		* and replies with a RedirectURL to which Sage Pay Server will send your customer.  This is normally your
		* order completion page, or a page to handle failures or cancellations.
		***************************************************************************************************

		*** Information is POSTed to this page from Sage Pay Server. The POST will ALWAYS contain the VendorTxCode, **
		*** VPSTxID and Status fields.  We'll extract these first and use them to decide how to respond to the POST. **/
		
		$this->status = $this->input->post('Status');
		$VendorTxCode = $this->input->post('VendorTxCode');
		$VPSTxId = $this->input->post('VPSTxId');
	
		// Using the VPSTxId and VendorTxCode, we can retrieve our SecurityKey from our database
		// This enables us to validate the POST to ensure it came from the Sage Pay Systems
		$security_code = $this->sagepay_server_model->get_security_key($VendorTxCode, $VPSTxId);
                
		if (!$security_code)
		{
			/** We cannot find a record of this order in the database, so something isn't right **
			** To protect the customer, we should send back an INVALID response.  This will prevent **
			** the Sage Pay Server systems from settling any authorised transactions.  We will also send a **
			** RedirectURL that points to our orderFailure page, passing details of the error **
			** in the Query String so that the page knows how to respond to the customer **/

			header("Content-type: text/html");
			echo "Status=INVALID{$this->sagepay_server->eoln}";

			/** Only use the Internal FQDN value during development.  In LIVE systems, always use the actual FQDN **/
			if ($this->sagepay_server->config['connect_to'] == "LIVE")
			{
				echo "RedirectURL={$this->sagepay_server->config['your_site_fqdn']}transaction_status/failed/001/{$VendorTxCode}{$this->sagepay_server->eoln}";
			}
			else
			{
				echo "RedirectURL={$this->sagepay_server->config['your_site_internal_fqdn']}transaction_status/failed/001/{$VendorTxCode}{$this->sagepay_server->eoln}";
			}
		
			echo "StatusDetail=Unable to find the transaction in our database.{$this->sagepay_server->eoln}";
			exit();
		}
	
		/** We've found the order in the database, so now we can validate the message **
		** First blank out our result variables **/
	
		// from SagePay sample code but I don't see why this is necessary!!!
		$StatusDetail = "";
		$TxAuthNo = "";
		$AVSCV2 = "";
		$AddressResult = "";
		$PostCodeResult = "";
		$CV2Result = "";
		$GiftAid = "";
		$str3DSecureStatus = ""; // variable names can't start with a number so left $str in front
		$CAVV = "";
		$AddressStatus= "";
		$PayerStatus = "";
		$CardType = "";
		$Last4Digits = "";
		$MySignature = "";

		/** Now get the VPSSignature value from the POST, and the StatusDetail in case we need it **/
		$VPSSignature = $this->input->post('VPSSignature');
		$StatusDetail = $this->input->post('StatusDetail');

		/** Retrieve the other fields, from the POST if they are present **/
		if (strlen($this->input->post('TxAuthNo') > 0))
		{
			$TxAuthNo = $this->input->post('TxAuthNo');
		} 

		$AVSCV2 = $this->input->post('AVSCV2');
		$AddressResult = $this->input->post('AddressResult');
		$PostCodeResult = $this->input->post('PostCodeResult');
		$CV2Result = $this->input->post('CV2Result');
		$GiftAid = $this->input->post('GiftAid');
		$str3DSecureStatus = $this->input->post('3DSecureStatus');  // variable names can't start with a number so left $str in front
		$CAVV = $this->input->post('CAVV');
		$AddressStatus = $this->input->post('AddressStatus');
		$PayerStatus = $this->input->post('PayerStatus');
		$CardType = $this->input->post('CardType');
		$Last4Digits = $this->input->post('Last4Digits');

		/** Now we rebuilt the POST message, including our security key, and use the MD5 Hash **
		** component that is included to create our own signature to compare with **
		** the contents of the VPSSignature field in the POST.  Check the Sage Pay Server protocol **
		** if you need clarification on this process **/
		$Message = $VPSTxId . $VendorTxCode . $this->status . $TxAuthNo . $this->sagepay_server->config['vendorname'] . $AVSCV2 . $security_code 
		               . $AddressResult . $PostCodeResult . $CV2Result . $GiftAid . $str3DSecureStatus . $CAVV
		               . $AddressStatus . $PayerStatus . $CardType . $Last4Digits ;

		$MySignature = strtoupper(md5($Message));

		/** We can now compare our MD5 Hash signature with that from Sage Pay Server **/
		if ($MySignature !== $VPSSignature)
		{
			/** If the signatures DON'T match, we should mark the order as tampered with, and **
			** send back a Status of INVALID and failure page RedirectURL **/
		
			$transaction_update_array = array(
								'Status' => "TAMPER WARNING! Signatures do not match for this Order.  The Order was Cancelled. strMySignature={$MySignature} strVPSSignature={$VPSSignature}"
								);
		
			$this->sagepay_server_model->update_transaction($transaction_update_array, $VendorTxCode);
	
			header("Content-type: text/plain");
			echo "Status=INVALID{$this->sagepay_server->eoln}";
		
			/** Only use the Internal FQDN value during development.  In LIVE systems, always use the actual FQDN **/
			if ($this->sagepay_server->config['connect_to'] == "LIVE")
			{
				echo "RedirectURL={$this->sagepay_server->config['your_site_fqdn']}transaction_status/failed/002/{$VendorTxCode}{$this->sagepay_server->eoln}";
			}
			else
			{
				echo "RedirectURL={$this->sagepay_server->config['your_site_internal_fqdn']}transaction_status/failed/002/{$VendorTxCode}{$this->sagepay_server->eoln}";
			}
			
			echo "StatusDetail=Cannot match the MD5 Hash. Order might be tampered with.{$this->sagepay_server->eoln}";
			exit();
		}
	
		/** Great, the signatures DO match, so we can update the database and redirect the user appropriately **/
		if ($this->status == "OK")
		{
			$DBStatus = "AUTHORISED - The transaction was successfully authorised with the bank.";
		}
		elseif ($this->status == "NOTAUTHED") 
		{
			$DBStatus = "DECLINED - The transaction was not authorised by the bank.";
		}
		elseif ($this->status == "ABORT")
		{
			$DBStatus = "ABORTED - The customer clicked Cancel on the payment pages, or the transaction was timed out due to customer inactivity.";
		} 	
		elseif ($this->status == "REJECTED")
		{
			$DBStatus = "REJECTED - The transaction was failed by your 3D-Secure or AVS/CV2 rule-bases.";
		}
		elseif ($this->status == "AUTHENTICATED")
		{
			$DBStatus = "AUTHENTICATED - The transaction was successfully 3D-Secure Authenticated and can now be Authorised.";
		}
		elseif ($this->status == "REGISTERED")
		{
			$DBStatus = "REGISTERED - The transaction was could not be 3D-Secure Authenticated, but has been registered to be Authorised.";
		}
		elseif ($this->status == "ERROR")
		{
			$DBStatus = "ERROR - There was an error during the payment process. The error details are: {$StatusDetail}";
		}
		else
		{
			$DBStatus = "UNKNOWN - An unknown status was returned from Sage Pay. The Status was: {$this->status}, with StatusDetail:{$StatusDetail}";
		}

		$transaction_update_array = array(
							'Status' => $DBStatus
							);
	

		if (strlen($TxAuthNo) > 0)
		{
			$transaction_update_array['TxAuthNo'] = $TxAuthNo;
		} 

		if (strlen($AVSCV2) > 0)
		{
			$transaction_update_array['AVSCV2'] = $AVSCV2;
		} 

		if (strlen($AddressResult) > 0) 
		{
			$transaction_update_array['AddressResult'] = $AddressResult;
		}

		if (strlen($PostCodeResult) > 0)
		{
			$transaction_update_array['PostCodeResult'] = $PostCodeResult;
		} 

		if (strlen($CV2Result) > 0)
		{
			$transaction_update_array['CV2Result'] = $CV2Result;
		}

		if (strlen($GiftAid) > 0)
		{
			$transaction_update_array['GiftAid'] = $GiftAid;
		} 

		if (strlen($str3DSecureStatus) > 0)
		{
			$transaction_update_array['ThreeDSecureStatus'] = $str3DSecureStatus;
		} 

		if (strlen($CAVV) > 0)
		{
			$transaction_update_array['CAVV'] = $CAVV;
		} 

		if (strlen($AddressStatus) > 0)
		{
			$transaction_update_array['AddressStatus'] = $AddressStatus;
		} 

		if (strlen($PayerStatus) > 0)
		{
			$transaction_update_array['PayerStatus'] = $PayerStatus;
		} 

		if (strlen($CardType) > 0)
		{
			$transaction_update_array['CardType'] = $CardType;
		} 

		if (strlen($Last4Digits) > 0)
		{
			$transaction_update_array['Last4Digits'] = $Last4Digits;
		} 

		/*
			This code checks whether we have already received a SagePay response. We check for the variable
			$first_sagepay_notification in our success cases because we don't want to run that code multiple times e.g.
			sending out multiple autoresponders or incrementing membership year multiple times. This code needs to occur
			before the transaction is updated with the SagePay Status.
		*/
		
		$transaction = $this->sagepay_server_model->get_transaction($VendorTxCode);
		$first_sagepay_notification = FALSE;
		
		if ($transaction)
		{
			if ($transaction->Status == NULL)
			{
				$first_sagepay_notification = TRUE;
			}
		}
		
		// Save transaction details into db
		$this->sagepay_server_model->update_transaction($transaction_update_array, $VendorTxCode);

		/** New reply to Sage Pay Server to let the system know we've received the Notification POST **/
		header("Content-type: text/plain");

		/** Always send a Status of OK if we've read everything correctly.  Only INVALID for messages with a Status of ERROR **/
		if ($this->status == "ERROR") // this only occurs when Sagepay has incurred a problem so could add a redirecturl to a payment system error page instead of generic failed page
		{
			echo "Status=INVALID{$this->sagepay_server->eoln}";
		}
		else
		{
			echo "Status=OK{$this->sagepay_server->eoln}"; 
			$Response="Status=OK{$this->sagepay_server->eoln}";
		}	
		
		/** Now decide where to redirect the customer **/
       
		/* Debugging
		test -->
        $this->status = "OK";
        $VendorTxCode = '10-05-06-09-41-49-107264625';
        */

		if ($this->status == "OK" || $this->status == "AUTHENTICATED" || $this->status == "REGISTERED")
		{
			/** If a transaction status is OK, AUTHENTICATED or REGISTERED, we should send the customer to the success page **/
			$this->RedirectPage = "transaction_status/success/{$VendorTxCode}";
			
			/** Add application specific tranasctio success code e.g. autoresponders, membership renewed code etc **/
			
		}
		else /** The status indicates a failure of one state or another, so send the customer to failure page **/
		{
			// set default
			$this->RedirectPage = "transaction_status/failed/unknown/{$VendorTxCode}";
		}	

		/** Only use the Internal FQDN value during development.  In LIVE systems, always use the actual FQDN **/
		if ($this->sagepay_server->config['connect_to'] == "LIVE")
		{
			echo "RedirectURL={$this->sagepay_server->config['your_site_fqdn']}{$this->RedirectPage}{$this->sagepay_server->eoln}"; 
		}	
		else 
		{
			echo "RedirectURL={$this->sagepay_server->config['your_site_internal_fqdn']}{$this->RedirectPage}{$this->sagepay_server->eoln}";
		}	

		/** No need to send a StatusDetail, since we're happy with the POST **/
		exit();
	}

	// --------------------------------------------------------------------
}

/* End of file sagepay_notification.php */
/* Location: ./application/controllers/sagepay_notification.php */