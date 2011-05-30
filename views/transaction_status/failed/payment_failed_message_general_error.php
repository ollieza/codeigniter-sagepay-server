<html>
<head>
	<title>Sage Pay Server CodeIgniter library example - Payment failed</title>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url();?>css/core.css">
</head>

<body>
    <div id="pageContainer">
        <div id="content">
            <div id="contentHeader">Sage Pay Server CodeIgniter library example - payment failed</div>
            
			<p><strong>There seems to be a problem with your payment. Your card has not been charged.</strong></p>

			<p>Please try making another payment, or contact us.</p>
			
			<?php if (isset($VendorTxCode)): ?>
			
			<p>Please quote the reference number: <?php echo $VendorTxCode; ?></p>
			
			<?php endif; ?>
           <div class="greyHzShadeBar">&nbsp;</div>
		</div>
	</div>
</body>
</html>