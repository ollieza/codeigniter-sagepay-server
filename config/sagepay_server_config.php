<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| Sagepay Server configuration file
| -------------------------------------------------------------------------
*/

// Set this value to the Vendor Name assigned to you by Sage Pay or chosen when you applied
$config['vendorname'] = ""; 

// Set this to indicate the currency in which you wish to trade. You will need a merchant number in this currency
$config['currency'] = "GBP";

// This can be DEFERRED or AUTHENTICATE if your Sage Pay account supports those payment types
/**	TxType
** 
** Alphabetic 
** Max 15 characters. 
** 
** PAYMENT, DEFERRED or 
** AUTHENTICATE ONLY 
** 
** See companion document “Server and Direct Shared Protocols” for 
** other transaction types (such as Refund, Releases, Aborts and 
** Repeats). The value should be in capital letters. */
$config['transactiontype'] = "PAYMENT"; 

/** Optional setting. If you are a Sage Pay Partner and wish to flag the transactions with your unique partner id set it here. **/
$config['partnerid'] = ""; 

/** Set to SIMULATOR for the Sage Pay Simulator expert system, TEST for the Test Server **
*** and LIVE in the live environment **/
$config['connect_to'] = "SIMULATOR"; 	
#$config['connect_to'] = "TEST"; 	
#$config['connect_to'] = "LIVE"; 	


/** IMPORTANT.  Set the strYourSiteFQDN value to the Fully Qualified Domain Name of your server. **
** This should start http:// or https:// and should be the name by which our servers can call back to yours **
** i.e. it MUST be resolvable externally, and have access granted to the Sage Pay servers **
** examples would be https://www.mysite.com or http://212.111.32.22/ **
** NOTE: You should leave the final / in place. **/
$config['your_site_fqdn']	= "";

/** At the end of a Sage Pay Server transaction, the customer is redirected back to the completion page **
** on your site using a client-side browser redirect. On live systems, this page will always be **
** referenced using the strYourSiteFQDN value above.  During development and testing, however, it **
** is often the case that the development machine sits behind the same firewall as the server **
** hosting the kit, so your browser might not be able resolve external IPs or dns names. **
** e.g. Externally your server might have the IP 212.111.32.22, but behind the firewall it **
** may have the IP 192.168.0.99.  If your test machine is also on the 192.168.0.n network **
** it may not be able to resolve 212.111.32.22. **
** Set the strYourSiteInternalFQDN to the internal Fully Qualified Domain Name by which **
** your test machine can reach the server (in the example above you'd use http://192.168.0.99/) **
** If you are not on the same network as the test server, set this value to the same value **
** as strYourSiteFQDN above. **
** NOTE: You should leave the final / in place. **/
$config['your_site_internal_fqdn'] = "";

/**************************************************************************************************
* Global Definitions for this site
***************************************************************************************************/

$config['protocol'] = "2.23";

if ($config['connect_to'] == "LIVE")
{
  $config['aborturl'] = "https://live.sagepay.com/gateway/service/abort.vsp";
  $config['authoriseurl'] = "https://live.sagepay.com/gateway/service/authorise.vsp";
  $config['cancelurl'] = "https://live.sagepay.com/gateway/service/cancel.vsp";
  $config['purchaseurl'] = "https://live.sagepay.com/gateway/service/vspserver-register.vsp";
  $config['refundurl'] = "https://live.sagepay.com/gateway/service/refund.vsp";
  $config['releaseurl'] = "https://live.sagepay.com/gateway/service/release.vsp";
  $config['repeaturl'] = "https://live.sagepay.com/gateway/service/repeat.vsp";
  $config['voidurl'] = "https://live.sagepay.com/gateway/service/void.vsp";
}
elseif ($config['connect_to'] == "TEST")
{
  $config['aborturl'] = "https://test.sagepay.com/gateway/service/abort.vsp";
  $config['authoriseurl'] = "https://test.sagepay.com/gateway/service/authorise.vsp";
  $config['cancelurl'] = "https://test.sagepay.com/gateway/service/cancel.vsp";
  $config['purchaseurl'] = "https://test.sagepay.com/gateway/service/vspserver-register.vsp";
  $config['refundurl'] = "https://test.sagepay.com/gateway/service/refund.vsp";
  $config['releaseurl'] = "https://test.sagepay.com/gateway/service/abort.vsp";
  $config['repeaturl'] = "https://test.sagepay.com/gateway/service/repeat.vsp";
  $config['voidurl'] = "https://test.sagepay.com/gateway/service/void.vsp";
}
else // simulator
{
  $config['aborturl'] = "https://test.sagepay.com/simulator/VSPServerGateway.asp?Service=VendorAbortTx";
  $config['authoriseurl'] = "https://test.sagepay.com/simulator/VSPServerGateway.asp?Service=VendorAuthoriseTx";
  $config['cancelurl'] = "https://test.sagepay.com/simulator/VSPServerGateway.asp?Service=VendorCancelTx";
  $config['purchaseurl'] = "https://test.sagepay.com/Simulator/VSPServerGateway.asp?Service=VendorRegisterTx";
  $config['refundurl'] = "https://test.sagepay.com/simulator/VSPServerGateway.asp?Service=VendorRefundTx";
  $config['releaseurl'] = "https://test.sagepay.com/simulator/VSPServerGateway.asp?Service=VendorReleaseTx";
  $config['repeaturl'] = "https://test.sagepay.com/simulator/VSPServerGateway.asp?Service=VendorRepeatTx";
  $config['voidurl'] = "https://test.sagepay.com/simulator/VSPServerGateway.asp?Service=VendorVoidTx";
}

$config['completion_url'] = "https://dfgdocs-ollie-macbook.ath.cx/payment_result/sagepay_complete";
$config['not_authed_url'] = "https://dfgdocs-ollie-macbook.ath.cx/payment_result/sagepay_notauth";
$config['abort_url'] = "https://dfgdocs-ollie-macbook.ath.cx/payment_result/sagepay_abort";
$config['error_url'] = "https://dfgdocs-ollie-macbook.ath.cx/payment_result/sagepay_error";

/* End of file sagepay_server_config.php */
/* Location: ./application/config/sagepay_server_config.php */