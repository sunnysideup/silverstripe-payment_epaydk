<?php
/**
 * The callback is called by the ePay system. This ensure you that this file is called when a payment is made.
 * Use this file to update the order status, send order confirmation to the customer etc. 
 */



//To recieve an e-mail when the callback have been called type in your e-mail bellow
$email = 'enter your e-mail address here@enter your e-mail address here.dk';

//Get the current URL
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

//Send e-mail
mail($email, "Callback Test", "The callback.php (".curPageURL().") has been called by the ePay server. The order ID is: ". $_GET['orderid']);


//Return something for the server to read
echo "OK";
