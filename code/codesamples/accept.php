<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>ePay - HTML example</title>
	<link href="style.css" rel="stylesheet" type="text/css">
</head>
<body>
<h1>Accept Page</h1>

When the payment is accepted ePay returns information about the payment.
<br />

<table cellspacing="0" cellpadding="3" style="width: 800px;">
		<tr>
			<td>Transaction ID:</td>
			<td>
				<?php echo $_GET['tid']; ?>
			</td>
			<td>
				The unique transaction ID returned by ePay.
			</td>
		</tr>
		<tr>
			<td>OrderID:</td>
			<td>
				<?php echo $_GET['orderid']; ?>
			</td>
			<td>
				The order ID.
			</td>
		</tr>
		<tr>
			<td>Amount:</td>
			<td>
				<?php echo $_GET['amount']; ?>
			</td>
			<td>
				The amount in minor units.
			</td>
		</tr>
		<tr>
			<td>Fee:</td>
			<td>
				<?php echo $_GET['transfee']; ?>
			</td>
			<td>
				The transaction fee.
			</td>
		</tr>
		<tr>
			<td>Currency:</td>
			<td>
				<?php echo $_GET['cur']; ?>
			</td>
			<td>
				The currency.
			</td>
		</tr>
		<tr>
			<td>Date:</td>
			<td>
				<?php echo date("Y-m-d", strtotime($_GET['date'])); ?>
			</td>
			<td >
				The date.
			</td>
		</tr>
		<tr>
			<td>Time:</td>
			<td>
				<?php echo date("H:i", strtotime($_GET['time'])); ?>
			</td>
			<td>
				The time.
			</td>
		</tr>
	</table>
	
	<?php
    //Include the interface to handle the transaction
    include("handle_payment.php");
    ?>

	<br />
<a href="index.php">Go back</a>

</body>
</html>