<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Sage Pay Server transaction status
 *
 * Success and failed payment pages
 *
 * @package	sagepay_server
 * @author    Ollie Rattue, Too many tabs <orattue[at]toomanytabs.com>
 * @copyright Copyright (c) 2011, Ollie Rattue
 * @license   http://www.opensource.org/licenses/mit-license.php
 * @link      http://github.com/ollierattue/codeigniter-sagepay-server
 */

class Transaction_status extends CI_Controller
{	
    public function __construct()
    {
        parent::__construct();
		
		$this->load->model('sagepay_server_model');
    }

	// --------------------------------------------------------------------
	
	function success($VendorTxCode = NULL)
	{
		if (!$VendorTxCode)
		{
			$this->session->set_flashdata('flash', 'Payment not found');
			redirect('');
		}

		$payment_details = $this->sagepay_server_model->get_transaction($VendorTxCode);

		if (!$payment_details)
		{
			$this->session->set_flashdata('flash', 'Payment not found');
			redirect('');
		}
        
		$this->data['VendorTxCode'] = $VendorTxCode;
		$this->data['payment_details'] = $payment_details;
		
		$this->load->view('transaction_status/success/success', $this->data);
	}

	// --------------------------------------------------------------------

	function failed($reason = NULL, $VendorTxCode = NULL)
	{
		$this->data = array();

		if ($VendorTxCode)
		{
			$payment = $this->sagepay_server_model->get_transaction($VendorTxCode);

			if ($payment)
			{
				$this->data['payment'] = $payment;
			}
			
			$this->data['VendorTxCode'] = $VendorTxCode;
		}

		switch($reason)
		{
			case('001'): // Unable to find the transaction in our database.
			case('002'): // Cannot match the MD5 Hash. Order might be tampered with.
			case('003'): // Failed to register a transaction with SagePay. Usually means a malformed post is being sent to SagePay
				$this->load->view('transaction_status/failed/payment_failed_message_general_error', $this->data);
			break;

			default:
				redirect();
			break;
		}
	}

	// --------------------------------------------------------------------
}

/* End of file transaction_status.php */
/* Location: ./application/controllers/transaction_status.php */