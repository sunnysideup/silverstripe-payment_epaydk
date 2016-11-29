<?php
//Function used to get the current URL
function curPageURL()
{
    $pageURL = 'http';
    if (@$_SERVER["HTTPS"] == "on") {
        $pageURL .= "s";
    }
    $pageURL .= "://";
    if ($_SERVER["SERVER_PORT"] != "80") {
        $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
    } else {
        $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
    }
    $pageURL = str_replace("?".$_SERVER['QUERY_STRING'], "", $pageURL);
    
    
    $pageURL = substr($pageURL, 0, strripos($pageURL, '/'));
    
    return $pageURL;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>ePay - PHP example of the ePay relay-script</title>
	<link href="style.css" rel="stylesheet" type="text/css">
</head>
<body>
<h1>PHP guide of the ePay relay-script</h1>

This example code will show you how to implement the ePay relay-script by using PHP.
<br /><br />
The first page (<i>relay.php</i>) will show you howto setup the payment form through the relay-script with a standard set of test parameters.
<br /><br />
The second page (<i>accept.php</i>) will show you how to setup a payment receipt page with information of the payment made.
<br /><br />
The third page (<i>callback.php</i>) is called by ePay when the payment is made. <b>Tip!</b> Change the $email variable in the callback.php to receive an e-mail when the payment is made.
<br /><br />
The fourth page (<i>EpaySoap.php</i>) contains functions used by handle_payment.php to call the ePay Webservice (API).
<br /><br />
The fifth page (<i>handle_payment.php</i>) is an example of using the ePay Webservice to capture, credit and delete a transaction. This file is included in accept.php
<br /><br />
The lib folder contains the nuSOAP library which in this example is used to connect to the ePay Webservice (API)
<br /><br />
relay-fee.php is the same as relay.php but with automatic calculation of the transaction fee using the ePay Webservice. <b>Notice!</b> This will require a ePay Business subscription.<br />
webservice_fee.php contains the functionality to obtain the transaction fee using the ePay Webservice.
<br /><br />
<div class="notice">
<b>Notice!</b><br />
In order to test you need a test merchant number. To obtain a test merchant number please sign up for a <b>FREE</b> ePay test account here: <a href="http://www.epay.dk/main/signup.asp" target="_blank">Get ePay test account</a>. 
<br />
When you have obtained this test account you can in this programming example enter the test merchant number in the field named <i>merchantnumber</i> on the next page.
</div>
<br /><br />
On the accept page you will be able to capture, credit and delete the transaction by using the ePay Webservice. <b>Notice!</b> This will require a ePay Business subscription.
<br />
<b>Remember to change the merchant number in handle_payment.php for this to work.</b>
<br /><br />
The relay-server will load the payment form and show it through a certified secure encrypted line.<br /><br />
This is done by addding:<br /> <b>https://relay.ditonlinebetalingssystem.dk/relay/v2/relay.cgi/</b> <br />in front of the payment form URL.
<br /><br />In this example it is: <br />https://relay.ditonlinebetalingssystem.dk/relay/v2/relay.cgi/<b><?php echo curPageURL() ?>/relay.php</b>
<br /><br />
To start with integrated layout (relay) <a href="https://relay.ditonlinebetalingssystem.dk/relay/v2/relay.cgi/<?php echo curPageURL() ?>/relay.php">click here</a>.
<br />
To start with integrated layout (relay) with transaction fee calculation <a href="https://relay.ditonlinebetalingssystem.dk/relay/v2/relay.cgi/<?php echo curPageURL() ?>/relay-fee.php">click here</a>.
<br /><br />
<b>Support</b><br />
For support please contact us at one of the following mails:
<table cellspacing="0" cellpadding="3">
	<tr>
		<td>Danish</td>
		<td>
			<a href="mailto:support@epay.dk">support@epay.dk</a>
		</td>
	</tr>
	<tr>
		<td>Swedish</td>
		<td>
			<a href="mailto:support@epay.se">support@epay.se</a>
		</td>
	</tr>		
	<tr>
		<td>English</td>
		<td>
			<a href="mailto:support@epay.eu">support@epay.eu</a>
		</td>
	</tr>
</table>
</body>
</html>