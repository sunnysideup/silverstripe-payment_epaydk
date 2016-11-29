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

    return $pageURL;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>ePay - HTML example of the ePay Standard Payment Window</title>
	<link href="style.css" rel="stylesheet" type="text/css">
	
</head>
<body>
<h1>Start the ePay relay-script</h1>

<?php

//Check if any errors returned
if (isset($_GET['error'])) {
    echo '<div class="notice"><b>Error!</b><br />'.htmlentities($_GET['errortext']).'</div><br />';
}

?>

The full documentation of the ePay relay script is to be found here: <a href="http://tech.epay.dk/13/" target="_blank">http://tech.epay.dk/13/</a>.

<br /><br />
Now we need to provide the relay-script with information on the payment.<br />
You can hide these information by making the type of the input fields hidden: <br />
<b>&lt;input type=&quot;hidden&quot; name=&quot;merchantnumber&quot; value=&quot;ENTER YOUR MERCHANT NUMBER HERE&quot; /&gt;</b>
<br />
<br />
These are the information required by the relay-script:
<form action="https://ssl.ditonlinebetalingssystem.dk/auth/default.aspx" method="post" name="ePay" id="ePay">
<table cellspacing="0" cellpadding="3" style="width: 800px;">
		<tr>
			<td>Merchantnumber:</td>
			<td>
				<input type="text" name="merchantnumber" value="ENTER YOUR MERCHANT NUMBER HERE" style="width: 200px;" />
			</td>
			<td>
				This is your unique merchantnumber. You can find your test-merchantnumber by chosing <b>Support</b> - <b>Test information</b> in the ePay Administration
			</td>
		</tr>
		<tr>
			<td>Amount:</td>
			<td>
				<input type="text" name="amount" value="100" style="width: 200px;" />
			</td>
			<td>
				The amount in minor units. 1 DKK = 100.
			</td>
		</tr>
		<tr>
			<td>Currency:</td>
			<td>
				<select name="currency" style="width: 200px;">
					<option value="208">DKK (208)</option>
					<option value="978">EUR (978)</option>
					<option value="840">USD (840)</option>
					<option value="578">NOK (578)</option>
					<option value="752">SEK (752)</option>
					<option value="826">GBP (826)</option>
					<option value="036">AUD (036)</option>
				</select>
			</td>
			<td>
				The currency number which the amount is provided in. A full list of supported currencies is available in the ePay administration <b>Support</b> - <b>Currency codes</b> 
			</td>
		</tr>
		<tr>
			<td>OrderID:</td>
			<td>
				<input type="text" name="orderid" style="width: 200px;" />
			</td>
			<td>
				This is your reference for the payment. This must be filled in.
			</td>
		</tr>
		<tr>
			<td>Card no:</td>
			<td>
				<input type="text" name="cardno" style="width: 200px;" />
			</td>
			<td>
				The card number. You can find test cardnumber at <b>Support</b> - <b>Test information</b> in the ePay Administration
			</td>
		</tr>
		<tr>
			<td>Exp. month:</td>
			<td>
				<select name="expmonth" style="width: 45px">
					<option value="01">01</option>
					<option value="02">02</option>
					<option value="03">03</option>
					<option value="04">04</option>
					<option value="05">05</option>
					<option value="06">06</option>
					<option value="07">07</option>
					<option value="08">08</option>
					<option value="09">09</option>
					<option value="10">10</option>
					<option value="11">11</option>
					<option value="12">12</option>
				</select>
			</td>
			<td>
				The card's experation months
			</td>
		</tr>
		<tr>
			<td>Exp. year:</td>
			<td>
				<select name="expyear" style="width: 45px">
					<option value="09">09</option>
					<option value="10">10</option>
					<option value="11">11</option>
					<option value="12">12</option>
					<option value="13">13</option>
					<option value="14">14</option>
					<option value="15" SELECTED>15</option>
					<option value="16">16</option>
					<option value="17">17</option>
					<option value="18">18</option>
					<option value="19">19</option>
					<option value="20">20</option>
				</select>
			</td>
			<td>
				The card's experation year
			</td>
		</tr>
		<tr>
			<td>CVC:</td>
			<td>
				<input type="text" name="cvc" style="width: 200px;" />
			</td>
			<td>
				The card's controlciffers (CVC)
			</td>
		</tr>
		<tr>
			<td>AcceptURL:</td>
			<td>
				<input type="text" name="accepturl" value="<?php echo substr(curPageURL(), 0, strripos(curPageURL(), '/')); ?>/accept.php" />
			</td>
			<td>
				The cardholder will return to this URL when the payment is approved.
			</td>
		</tr>
		<tr>
			<td>DeclineURL:</td>
			<td>
				<input type="text" name="declineurl" value="https://relay.ditonlinebetalingssystem.dk/relay/v2/relay.cgi/<?php echo substr(curPageURL(), 0, strripos(curPageURL(), '/')); ?>/relay.php" />
			</td>
			<td>
				The cardholder will return to this URL if payment is aborted by the cardholder.
			</td>
		</tr>
		<tr>
			<td>CallbackURL:</td>
			<td>
				<input type="text" name="callbackurl" value="<?php echo str_replace("relay.php", "callback.php", curPageURL()); ?>" />
			</td>
			<td>
				The ePay callback server will call this page when the payment is made.
			</td>
		</tr>
		<tr>
			<td>Language:</td>
			<td>
				<select name="language">
					<option value="1">Danish (1)</option>
					<option value="2" selected="selected">English (2)</option>
				</select>
			</td>
			<td>
				The language. Error messages will be returned in this language.
			</td>
		</tr>
		<tr>
			<td>Instant capture:</td>
			<td>
				<select name="instantcapture">
					<option value="0">Disabled (1)</option>
					<option value="1">Enabled (2)</option>
				</select>
			</td>
			<td>
				To capture the payment as it's made.
			</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td><input type="submit" value="Pay" class="button" style="width: 200px;"></td>
			<td>Post the form fields to ePay</b></td>
		</tr>
	</table>
</form>
	<br />
	<a href="index.php">Go back to the intro page</a>
	
</body>
</html>