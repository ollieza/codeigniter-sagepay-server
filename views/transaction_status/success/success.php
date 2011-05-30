<html>
<head>
	<title>Sage Pay Server CodeIgniter library example - Payment failed</title>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url();?>css/core.css">
</head>

<body>
    <div id="pageContainer">
        <div id="content">
            <div id="contentHeader">Sage Pay Server CodeIgniter library example - payment success</div>
            
			<p>Your payment was a success. We will dispatch your product shortly.</p>
			
			<?php if (isset($VendorTxCode)): ?>
			
			<p>Your order reference number is <?php echo $VendorTxCode; ?></p>
			
			<?php endif; ?>
			
           <div class="greyHzShadeBar">&nbsp;</div>

			   	<table class="formTable">
					<tr>
						<td colspan="2"><div class="subheader">Transaction details sent back by Sage Pay Server</div></td>
					</tr>
					
					<tr>
					   <td class="fieldLabel">Status:</td>
					   <td class="fieldData"><?php echo $payment_details->Status; ?></td>
					</tr>
					
					<tr>
					   <td class="fieldLabel">Amount:</td>
					   <td class="fieldData"><?php echo $payment_details->Amount; ?></td>
					</tr>

					<tr>
					   <td class="fieldLabel">TxAuthNo:</td>
					   <td class="fieldData"><?php echo $payment_details->TxAuthNo; ?></td>
					</tr>

					<tr>
					   <td class="fieldLabel">AVSCV2:</td>
					   <td class="fieldData"><?php echo $payment_details->AVSCV2; ?></td>
					</tr>

					<tr>
					   <td class="fieldLabel">AddressResult:</td>
					   <td class="fieldData"><?php echo $payment_details->AddressResult; ?></td>
					</tr>
					
					<tr>
					   <td class="fieldLabel">PostCodeResult:</td>
					   <td class="fieldData"><?php echo $payment_details->PostCodeResult; ?></td>
					</tr>

					<tr>
					   <td class="fieldLabel">CV2Result:</td>
					   <td class="fieldData"><?php echo $payment_details->CV2Result; ?></td>
					</tr>

					<tr>
					   <td class="fieldLabel">ThreeDSecureStatus:</td>
					   <td class="fieldData"><?php echo $payment_details->ThreeDSecureStatus; ?></td>
					</tr>
					
					<?php if ($payment_details->RelatedVendorTxCode): ?>
					<tr>
					   <td class="fieldLabel">RelatedVendorTxCode:</td>
					   <td class="fieldData"><?php echo $payment_details->RelatedVendorTxCode; ?></td>
					</tr>
					<?php endif; ?>
					
					<?php if ($payment_details->AddressStatus): ?>
					<tr>
					   <td class="fieldLabel">AddressStatus:</td>
					   <td class="fieldData"><?php echo $payment_details->AddressStatus; ?></td>
					</tr>
					<?php endif; ?>
					
					<?php if ($payment_details->PayerStatus): ?>
					<tr>
					   <td class="fieldLabel">PayerStatus:</td>
					   <td class="fieldData"><?php echo $payment_details->PayerStatus; ?></td>
					</tr>
					<?php endif; ?>
						
					<tr>
					   <td class="fieldLabel">CardType:</td>
					   <td class="fieldData"><?php echo $payment_details->CardType; ?></td>
					</tr>

					<tr>
					   <td class="fieldLabel">Last4Digits:</td>
					   <td class="fieldData"><?php echo $payment_details->Last4Digits; ?></td>
					</tr>
				</table>
			<div class="greyHzShadeBar">&nbsp;</div>
		
		</div>
	</div>
</body>
</html>