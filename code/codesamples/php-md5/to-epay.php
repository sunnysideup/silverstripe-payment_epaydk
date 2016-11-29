<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>ePay - PHP guide of MD5 in ePay</title>
	<link href="style.css" rel="stylesheet" type="text/css">
</head>
<body>
<h1>PHP guide of MD5 in ePay</h1>

To secure your payments you are able to send a MD5 stamp for ePay to validate before the payment is approved.
<br />To do so you need to setup a Secret MD5 Key in the ePay administration: <b>Settings</b> - <b>Payment System</b>. Remember this key is secret and should only be known by you.<br /><br />

The MD5 Key is calculated from the following parameters:
<ul>
	<li>Currency</li>
	<li>Amount</li>
	<li>Order ID</li>
	<li>Secret Key</li>
</ul>

To calculate the MD5 key sent to ePay please fill out the form below.
<br /><br />

<?php

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        ?>
		<div class="success">
			<?php
                echo "MD5 Key to be sent to ePay: <b>". md5($_POST['cur'] . $_POST['amount'] . $_POST['orderid'] . $_POST['md5']) . "</b>"; ?>
			<br />
			In your payment form this key should be sent in a hidden field with the name <b>MD5key</b>
		</div><br /><br />
		<?php

    }

?>

<form action="to-ePay.php" method="post">
<table cellspacing="0" cellpadding="3" style="width: 800px;">
	<tr>
		<td>Currency:</td>
		<td>
			<input type="text" name="cur" value="" style="width: 200px;" />
		</td>
		<td>
			The currency number which the amount is provided in. A full list of supported currencies is available in the ePay administration <b>Support</b> - <b>Currency codes</b>
		</td>
	</tr>
	<tr>
		<td>Amount:</td>
		<td>
			<input type="text" name="amount" value="" style="width: 200px;" />
		</td>
		<td>
			The amount in minor units. 1 DKK = 100
		</td>
	</tr>
	<tr>
		<td>Order ID:</td>
		<td>
			<input type="text" name="orderid" value="" style="width: 200px;" />
		</td>
		<td>
			This is your reference for the payment.
		</td>
	</tr>
	<tr>
		<td>Secret Key:</td>
		<td>
			<input type="text" name="md5" value="" style="width: 200px;" />
		</td>
		<td>
			The Secure Key given in the ePay Administration
		</td>
	</tr>
		<tr>
			<td>&nbsp;</td>
			<td><input type="submit" value="Calculate"></td>
			<td>Press this to calculate the MD5 Key</td>
		</tr>
</table>
</form>
	<br />
	<a href="index.php">Go back to the intro page</a>

</body>
</html>