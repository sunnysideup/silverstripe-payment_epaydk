<h1>Remote Handle Payment (API)</h1>
<?php
//Change the merchant number below to make this example work
$merchantnumber = 99999999;

//Function to get the current URL
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
    
    $replace = "&mode=".$_GET['mode'];
    
    $pageURL = str_replace($replace, "", $pageURL);
    
    return $pageURL;
}

//Get the class
require_once("epaysoap.php");
 
//Access the webservice
$epay = new EpaySoap();

//Get action
if (isset($_GET['mode'])) {
    $mode = $_GET['mode'];
    
    //Select mode
    switch ($mode) {
        case 1: //Capture
            $return = $epay->capture($merchantnumber, $_GET['tid'], $_GET['amount']);
        break;
        case 2: //Credit
            $return = $epay->credit($merchantnumber, $_GET['tid'], $_GET['amount']);
        break;
        case 3: //Delete
            $return = $epay->delete($merchantnumber, $_GET['tid']);
        break;
    }
    
    //If response from Webservice is OK then return to referer.
    if ($return['epayresponse'] == -1) {
        header("Location: ".$_SERVER['HTTP_REFERER']);
    } else { //If errors

        echo "<div class=\"notice\">";
        
        if ($return['epayresponse'] <> -1) {
            echo "ePay: ".$epay->getEpayError($merchantnumber, $return['epayresponse']);
        }
            
        if ($return['pbsResponse'] <> -1 and strlen($return['pbsResponse']) > 0) {
            echo "<br />PBS: ".$epay->getPbsError($merchantnumber, $return['pbsResponse']);
        }
            
        if ($return['pbsresponse'] <> -1 and strlen($return['pbsresponse']) > 0) {
            echo "<br />PBS: ".$epay->getPbsError($merchantnumber, $return['pbsresponse']);
        }
            
        echo "</div><br />";
    }
}
?>

<?php
//Get the transaction
$transaction = $epay->gettransaction($merchantnumber, $_GET['tid']);

    //Check the result
    if ($transaction['gettransactionResult'] == 'true') {
        
            //Get the transaction history
            $history = $transaction['transactionInformation']['history']['TransactionHistoryInfo'];
            
        if (!array_key_exists(0, $history)) {
            $history = array($history);
        }

        if (is_array($history)) {
            asort($history);
            echo "<table cellspacing=\"0\" cellpadding=\"3\" style=\"width: 800px;\">";
            foreach ($history as $value) {
                ?>
					<tr>
						<td>
							<?php echo date("Y-m-d H:i", strtotime($value['created']));
                ?>
						</td>
						<td>	
							<?php echo $value['eventMsg'];
                ?>
						</td>
					</tr>
			<?php

            }
            echo "</table>";
        }
            
        echo "<br /><br />You can now handle the order by clicking here: <a href=\"".curPageURL()."&mode=1\">Capture</a> - <a href=\"".curPageURL()."&mode=2\">Credit</a> - <a href=\"".curPageURL()."&mode=3\">Delete</a><br />";
    } else {
        //If not able to connect to the webservice
        echo "ePay Error:". $transaction['epayresponse'];
    }

?>