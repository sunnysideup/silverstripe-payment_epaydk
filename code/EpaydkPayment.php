<?php
/**
 * ePay.dk Payment Type
 * @author: Jeremy Shipman - jeremy [at] burnbright [dot] co [dot] nz
 * 
 * Developer docs:
 * http://tech.epay.dk/Technical-documentation-for-ePay-Payment-Webservice_21.html
 */

class EpaydkPayment extends Payment {

	static $db = array (
		'TransactionID' => 'Varchar',
		'TransactionFee' => 'Currency',
		'CardID' => 'Int',
		'CardNoPostFix' => 'Varchar(4)'
	);

	static $submit_url = "https://ssl.ditonlinebetalingssystem.dk/popup/default.asp";
	static $logo = "payment_epaydk/images/epay_logo.gif";

	static $merchant_number;
	static $md5key = null;
	static $language = 2;
	static $limit_card_types = null;
	static $auth_sms = null,$auth_mail = null;
	static $google_tracker = null;
	static $use3d = null;
	static $add_fee = null;
	//supported currencies array is at bottom of class

	static function set_merchant_number($number) {
		self::$merchant_number = $number;
	}

	static function set_md5key($key) {
		self::$md5key = $key;
	}

	/**
	 * Language options:
	 * 	1 = Danish
	 * 	2 = English
	 * 	3 = Swedish
	 * 	4 = Norwegian
	 * 	5 = Greenland
	 * 	6 = Iceland
	 * 	7 = German
	 * 	8 = Finnish
	 * 	9 = Spanish
	 */
	static function set_language($language) {
		self::$language = $language;
	}

	/**
	 * To set card types, provide a string of coma-seperated number from this list:
	 	1 	= DANKORT
		2 	= VISA_DANKORT
		3 	= VISA_ELECTRON_FOREIGN
		4 	= MASTERCARD
		5 	= MASTERCARD_FOREIGN
		6 	= VISA_ELECTRON
		7 	= JCB
		8 	= DINERS
		9 	= MAESTRO
		10 	= AMERICAN_EXPRESS
		12 	= EDK
		13 	= DINERS_FOREIGN
		14 	= AMERICAN_EXPRESS_FOREIGN
		15 	= MAESTRO_FOREIGN
		16 	= FORBRUGSFORENINGEN
		17 	= EWIRE
		18 	= VISA
		19 	= IKANO
		20 	= OTHERS
		21 	= Nordea e-betaling
		22 	= Danske Netbetaling
		23 	= BG Netbetaling
		24 	= LIC_MASTERCARD
		25 	= LIC_MASTERCARD_FOREIGN
	 */
	static function set_cardtypes($types) {
		self::$limit_card_types = $types;
	}
	
	static function set_sms_number($number){
		self::$auth_sms = $number;
	}
	
	static function set_email($email){
		self::$auth_mail = $email;
	}
	
	static function set_google_tracker($gt){
		self::$google_tracker = $gt;
	}
	
	static function set_use3d($value){
		if($value == 1 || $value == 2 || $value == '1' || $value == '2')
			self::$use3d = $value;
	}
	
	static function add_fee($add){
		self::$add_fee = $add;
	}

	function getPaymentFormFields() {
		return new FieldSet(
			new LiteralField('ePayLogo', '<img src="' .self::$logo . '" alt="ePay Logo"/>')
		);
	}

	function getPaymentFormRequirements() {
		return null;
	}

	function processPayment($data, $form) {

		//sanity checks
		if (!isset (self::$supported_currencies[$this->Amount->Currency])) {
			user_error("The currency \"" . $this->Amount->Currency . "\" is not supported by ePay.dk", E_USER_ERROR);
		}

		$page = new Page();

		$page->Title = _t('EpaydkPayment.REDIRECTTOEPAY','Redirection to ePay...');
		$page->Logo = '<img src="' . self::$logo . '" alt="'._t('EpaydkPayment.POWEREDBYEPAY',"Payments powered by ePay").'"/>';
		$page->Form = $this->getEPayForm();

		$controller = new Page_Controller($page);

		$renderedpage = $controller->renderWith('PaymentProcessingPage');

		return new Payment_Processing($renderedpage);
	}

	function getEPayForm() {

		Requirements::javascript("http://www.epay.dk/js/standardwindow.js");
		Requirements::javascript(THIRDPARTY_DIR . "/jquery/jquery.js");

		$customscript =<<< JS
			jQuery(document).ready(function($) {				
				$("form#ePay").submit(function(){
					open_ePay_window();
					return false;
				});
				
			});	
JS;

		if(Director::isLive()){
			$customscript .=<<< JS
				jQuery(document).ready(function($) {
					open_ePay_window(); //enable for auto-submit
					$("form#ePay").hide();
				});	
JS;
		}
		Requirements::customScript($customscript, 'epayinit');

		$controller = new EpaydkPayment_Controller();

		//http://tech.epay.dk/ePay-Payment-Window-technical-documentation_9.html
		$fields = new FieldSet(
			new HiddenField('merchantnumber', 'Merchant Number', self::$merchant_number),
			new HiddenField('orderid', 'Order ID', $this->ID), //uses payment id, rather than paid for id incase there are multiple payments
			new HiddenField('currency', 'Currency', self::$supported_currencies[$this->Amount->Currency]['code']), new HiddenField('amount', 'Amount', $this->Amount->Amount * 100), //amount must be given in minor units //TODO: there may be currencies with more than 2 dp?
		
			new HiddenField('language', 'Language', self::$language),
		
			//return/callback urls
			new HiddenField('accepturl', 'Accept URL', Director::absoluteBaseURL() . $controller->Link('accept')), new HiddenField('declineurl', 'Decline URL', Director::absoluteBaseURL() . $controller->Link('decline')), new HiddenField('callbackurl', 'Callback URL', Director::absoluteBaseURL() . $controller->Link('callback')), new HiddenField('InstantCallback', 'Instant Callback', 1), //
			new HiddenField('instantcapture', 'Instant Capture', 1),
			//new HiddenField('ordertext','Order Text','')
			//new HiddenField('group','Group',$group)
			//new HiddenField('description','Description',$description)
			new HiddenField('windowstate', 'Window State', 2), //open payment page in same window
			new HiddenField('ownreceipt', 'Own Receipt', 1) //skip the reciept page
			//new HiddenField('HTTP_COOKIE','HTTP Cookie',$cookie)
			//new HiddenField('subscription','Subscription',$subscription)
			//new HiddenField('subscriptionname','Subscription Name',$subscriptionname)
			//new HiddenField('precardtype','Precard Type',$precardtype)
			//new HiddenField('splitpayment','Split Payment',$splitpayment)
		);

		//custom configs
		
		if (self::$md5key) {
			$md5data = $this->generateMD5();
			$fields->push(new HiddenField('md5key', 'MD5 Key', $md5data));
		}
		if(self::$limit_card_types){ $fields->push(new HiddenField('cardtype', 'Card Type', self::$limit_card_types)); }
		if(self::$add_fee){	$fields->push(new HiddenField('addfee','Add Fee',1)); }
		if(self::$auth_sms){$fields->push(new HiddenField('authsms','Auth SMS',self::$auth_sms)); }
		if(self::$auth_mail){$fields->push(new HiddenField('authmail','Auth Mail',self::$auth_mail)); }
		if(self::$google_tracker){$fields->push(new HiddenField('googletracker','Google Tracker',self::$google_tracker)); }
		if(self::$use3d){$fields->push(new HiddenField('use3D','Use 3D',self::$use3d)); }
		
		$actions = new FieldSet($openwindow = new FormAction('openwindow', _t('EpaydkPayment.OPENPAYMENTWINDOW', 'Open the ePay Payment Window')));

		$form = new Form($controller, 'ePay', $fields, $actions);
		
		$form->setHTMLID('ePay');
		$form->setFormAction(self::$submit_url);
		$form->unsetValidator();
		$form->disableSecurityToken();

		return $form;

	}

	/*
	 * Creates an MD5 of currency, amount, id, and pre-defined key.
	 * see http://tech.epay.dk/MD5_6.html
	 */
	function generateMD5() {
		return md5(self::$supported_currencies[$this->Amount->Currency]['code'] . ($this->Amount->Amount * 100) . $this->ID . self::$md5key);
	}

	/*
	 * Derived from:
	 * http://tech.epay.dk/Complete-list-of-valid-ePay-currency-codes_60.html
	 */
	public static $supported_currencies = array (
		'AFA' => array ('code' => '004','name' => 'Afghani',),
		'ALL' => array ('code' => '008','name' => 'Leck',),
		'DZD' => array ('code' => '012','name' => 'Algerian Dinar',),
		'ADP' => array ('code' => '020','name' => 'Andorran Peseta',),
		'AZM' => array ('code' => '031','name' => 'Azerbaijanian Manat',),
		'ARS' => array ('code' => '032','name' => 'Argentine Peso',),
		'AUD' => array ('code' => '036','name' => 'Australian Dollar',),
		'BSD' => array ('code' => '044','name' => 'Bahamian Dollar',),
		'BHD' => array ('code' => '048','name' => 'Bahraini Dinar',),
		'BDT' => array ('code' => '050','name' => 'Taka',),
		'AMD' => array ('code' => '051','name' => 'Armenian Dram',),
		'BBD' => array ('code' => '052','name' => 'Barbados Dollar',),
		'BMD' => array ('code' => '060','name' => 'Bermudian Dollar',),
		'BTN' => array ('code' => '064','name' => 'Ngultrum',),
		'BOB' => array ('code' => '068','name' => 'Boliviano',),
		'BWP' => array ('code' => '072','name' => 'Pula',),
		'BZD' => array ('code' => '084','name' => 'Belize Dollar',),
		'SBD' => array ('code' => '090','name' => 'Solomon Islands Dollar',),
		'BND' => array ('code' => '096','name' => 'Brunei Dollar',),
		'BGL' => array ('code' => '100','name' => 'Lev',),
		'MMK' => array ('code' => '104','name' => 'Kyat',),
		'BIF' => array ('code' => '108','name' => 'Burundi Franc',),
		'KHR' => array ('code' => '116','name' => 'Riel',),
		'CAD' => array ('code' => '124','name' => 'Canadian Dollar',),
		'CVE' => array ('code' => '132','name' => 'Cape Verde Escudo',),
		'KYD' => array ('code' => '136','name' => 'Cayman Islands Dollar',),
		'LKR' => array ('code' => '144','name' => 'Sri Lanka Rupee',),
		'CLP' => array ('code' => '152','name' => 'Chilean Peso',),
		'CNY' => array ('code' => '156','name' => 'Yuan Renminbi',),
		'COP' => array ('code' => '170','name' => 'Colombian Peso',),
		'KMF' => array ('code' => '174','name' => 'Comoro Franc',),
		'CRC' => array ('code' => '188','name' => 'Costa Rican Colon',),
		'HRK' => array ('code' => '191','name' => 'Croatian kuna',),
		'CUP' => array ('code' => '192','name' => 'Cuban Peso',),
		'CYP' => array ('code' => '196','name' => 'Cyprus Pound',),
		'CZK' => array ('code' => '203','name' => 'Czech Koruna',),
		'DKK' => array ('code' => '208','name' => 'Danish Krone',),
		'DOP' => array ('code' => '214','name' => 'Dominican Peso',),
		'ECS' => array ('code' => '218','name' => 'Sucre',),
		'SVC' => array ('code' => '222','name' => 'El Salvador Colon',),
		'ETB' => array ('code' => '230','name' => 'Ethiopian Birr',),
		'ERN' => array ('code' => '232','name' => 'Nakfa',),
		'EEK' => array ('code' => '233','name' => 'Kroon',),
		'FKP' => array ('code' => '238','name' => 'Falkland Islands Pound',),
		'FJD' => array ('code' => '242','name' => 'Fiji Dollar',),
		'DJF' => array ('code' => '262','name' => 'Djibouti Franc',),
		'GMD' => array ('code' => '270','name' => 'Dalasi',),
		'GHC' => array ('code' => '288','name' => 'Cedi',),
		'GIP' => array ('code' => '292','name' => 'Gibraltar Pound',),
		'GTQ' => array ('code' => '320','name' => 'Quetzal',),
		'GNF' => array ('code' => '324','name' => 'Guinea Franc',),
		'GYD' => array ('code' => '328','name' => 'Guyana Dollar',),
		'HTG' => array ('code' => '332','name' => 'Gourde',),
		'HNL' => array ('code' => '340','name' => 'Lempira',),
		'HKD' => array ('code' => '344','name' => 'Hong Kong Dollar',),
		'HUF' => array ('code' => '348','name' => 'Forint',),
		'ISK' => array ('code' => '352','name' => 'Iceland Krona',),
		'INR' => array ('code' => '356','name' => 'Indian Rupee',),
		'IDR' => array ('code' => '360','name' => 'Rupiah',),
		'IRR' => array ('code' => '364','name' => 'Iranian Rial',),
		'IQD' => array ('code' => '368','name' => 'Iraqi Dinar',),
		'ILS' => array ('code' => '376','name' => 'New Israeli Sheqel',),
		'JMD' => array ('code' => '388','name' => 'Jamaican Dollar',),
		'JPY' => array ('code' => '392','name' => 'Yen',),
		'KZT' => array ('code' => '398','name' => 'Tenge',),
		'JOD' => array ('code' => '400','name' => 'Jordanian Dinar',),
		'KES' => array ('code' => '404','name' => 'Kenyan Shilling',),
		'KPW' => array ('code' => '408','name' => 'North Korean Won',),
		'KRW' => array ('code' => '410','name' => 'Won',),
		'KWD' => array ('code' => '414','name' => 'Kuwaiti Dinar',),
		'KGS' => array ('code' => '417','name' => 'Som',),
		'LAK' => array ('code' => '418','name' => 'Kip',),
		'LBP' => array ('code' => '422','name' => 'Lebanese Pound',),
		'LSL' => array ('code' => '426','name' => 'Loti',),
		'LVL' => array ('code' => '428','name' => 'Latvian Lats',),
		'LRD' => array ('code' => '430','name' => 'Liberian Dollar',),
		'LYD' => array ('code' => '434','name' => 'Lybian Dinar',),
		'LTL' => array ('code' => '440','name' => 'Lithuanian Litus',),
		'MOP' => array ('code' => '446','name' => 'Pataca',),
		'MGF' => array ('code' => '450','name' => 'Malagasy Franc',),
		'MWK' => array ('code' => '454','name' => 'Kwacha',),
		'MYR' => array ('code' => '458','name' => 'Malaysian Ringgit',),
		'MVR' => array ('code' => '462','name' => 'Rufiyaa',),
		'MTL' => array ('code' => '470','name' => 'Maltese Lira',),
		'MRO' => array ('code' => '478','name' => 'Ouguiya',),
		'MUR' => array ('code' => '480','name' => 'Mauritius Rupee',),
		'MXN' => array ('code' => '484','name' => 'Mexican Peso',),
		'MNT' => array ('code' => '496','name' => 'Tugrik',),
		'MDL' => array ('code' => '498','name' => 'Moldovan Leu',),
		'MAD' => array ('code' => '504','name' => 'Moroccan Dirham',),
		'MZM' => array ('code' => '508','name' => 'Metical',),
		'OMR' => array ('code' => '512','name' => 'Rial Omani',),
		'NAD' => array ('code' => '516','name' => 'Namibia Dollar',),
		'NPR' => array ('code' => '524','name' => 'Nepalese Rupee',),
		'ANG' => array ('code' => '532','name' => 'Netherlands Antillan Guilder',),
		'AWG' => array ('code' => '533','name' => 'Aruban Guilder',),
		'VUV' => array ('code' => '548','name' => 'Vatu',),
		'NZD' => array ('code' => '554','name' => 'New Zealand Dollar',),
		'NIO' => array ('code' => '558','name' => 'Cordoba Oro',),
		'NGN' => array ('code' => '566','name' => 'Naira',),
		'NOK' => array ('code' => '578','name' => 'Norwegian Krone',),
		'PKR' => array ('code' => '586','name' => 'Pakistan Rupee',),
		'PAB' => array ('code' => '590','name' => 'Balboa',),
		'PGK' => array ('code' => '598','name' => 'Kina',),
		'PYG' => array ('code' => '600','name' => 'Guarani',),
		'PEN' => array ('code' => '604','name' => 'Nuevo Sol',),
		'PHP' => array ('code' => '608','name' => 'Philippine Peso',),
		'GWP' => array ('code' => '624','name' => 'Guinea-Bissau Peso',),
		'TPE' => array ('code' => '626','name' => 'Timor Escudo',),
		'QAR' => array ('code' => '634','name' => 'Qatari Rial',),
		'ROL' => array ('code' => '642','name' => 'Leu',),
		'RUB' => array ('code' => '643','name' => 'Russian Ruble',),
		'RWF' => array ('code' => '646','name' => 'Rwanda Franc',),
		'SHP' => array ('code' => '654','name' => 'Saint Helena Pound',),
		'STD' => array ('code' => '678','name' => 'Dobra',),
		'SAR' => array ('code' => '682','name' => 'Saudi Riyal',),
		'SCR' => array ('code' => '690','name' => 'Seychelles Rupee',),
		'SLL' => array ('code' => '694','name' => 'Leone',),
		'SGD' => array ('code' => '702','name' => 'Singapore Dollar',),
		'SKK' => array ('code' => '703','name' => 'Slovak Koruna',),
		'VND' => array ('code' => '704','name' => 'Dong',),
		'SIT' => array ('code' => '705','name' => 'Tolar',),
		'SOS' => array ('code' => '706','name' => 'Somali Shilling',),
		'ZAR' => array ('code' => '710','name' => 'Rand',),
		'ZWD' => array ('code' => '716','name' => 'Zimbabwe Dollar',),
		'SDD' => array ('code' => '736','name' => 'Sudanese Dinar',),
		'SRG' => array ('code' => '740','name' => 'Suriname Guilder',),
		'SZL' => array ('code' => '748','name' => 'Lilangeni',),
		'SEK' => array ('code' => '752','name' => 'Swedish Krona',),
		'CHF' => array ('code' => '756','name' => 'Swiss Franc',),
		'SYP' => array ('code' => '760','name' => 'Syrian Pound',),
		'THB' => array ('code' => '764','name' => 'Baht',),
		'TOP' => array ('code' => '776','name' => 'Pa\'anga',),
		'TTD' => array ('code' => '780','name' => 'Trinidad and Tobago Dollar',),
		'AED' => array ('code' => '784','name' => 'UAE Dirham',),
		'TND' => array ('code' => '788','name' => 'Tunisian Dinar',),
		'TRL' => array ('code' => '792','name' => 'Turkish Lira',),
		'TMM' => array ('code' => '795','name' => 'Manat',),
		'UGX' => array ('code' => '800','name' => 'Uganda Shilling',),
		'MKD' => array ('code' => '807','name' => 'Denar',),
		'RUR' => array ('code' => '810','name' => 'Russian Ruble',),
		'EGP' => array ('code' => '818','name' => 'Egyptian Pound',),
		'GBP' => array ('code' => '826','name' => 'Pound Sterling',),
		'TZS' => array ('code' => '834','name' => 'Tanzanian Shilling',),
		'USD' => array ('code' => '840','name' => 'US Dollar',),
		'UYU' => array ('code' => '858','name' => 'Peso Uruguayo',),
		'UZS' => array ('code' => '860','name' => 'Uzbekistan Sum',),
		'VEB' => array ('code' => '862','name' => 'Bolivar',),
		'YER' => array ('code' => '886','name' => 'Yemeni Rial',),
		'YUM' => array ('code' => '891','name' => 'Yugoslavian Dinar',),
		'ZMK' => array ('code' => '894','name' => 'Kwacha',),
		'TWD' => array ('code' => '901','name' => 'New Taiwan Dollar',),
		'TRY' => array ('code' => '949','name' => 'New Turkish Lira',),
		'XAF' => array ('code' => '950','name' => 'CFA Franc BEAC',),
		'XCD' => array ('code' => '951','name' => 'East Caribbean Dollar',),
		'XOF' => array ('code' => '952','name' => 'CFA Franc BCEAO',),
		'XPF' => array ('code' => '953','name' => 'CFP Franc',),
		'TJS' => array ('code' => '972','name' => 'Somoni',),
		'AOA' => array ('code' => '973','name' => 'Kwanza',),
		'BYR' => array ('code' => '974','name' => 'Belarussian Ruble',),
		'BGN' => array ('code' => '975','name' => 'Bulgarian Lev',),
		'CDF' => array ('code' => '976','name' => 'Franc Congolais',),
		'BAM' => array ('code' => '977','name' => 'Convertible Marks',),
		'EUR' => array ('code' => '978','name' => 'Euro',),
		'MXV' => array ('code' => '979','name' => 'Mexican Unidad de Inversion (UDI)',),
		'UAH' => array ('code' => '980','name' => 'Hryvnia',),
		'GEL' => array ('code' => '981','name' => 'Lari',),
		'ECV' => array ('code' => '983','name' => 'Unidad de Valor Constante (UVC)',),
		'BOV' => array ('code' => '984','name' => 'Mvdol',),
		'PLN' => array ('code' => '985','name' => 'Zloty',),
		'BRL' => array ('code' => '986','name' => 'Brazilian Real',),
		'CLF' => array ('code' => '990','name' => 'Unidades de fomento',),
	);

}

class EpaydkPayment_Controller extends Controller {

	function Link($action = "") {
		return "EpaydkPayment_Controller/" . $action;
	}

	function payment() {
		if (isset ($_GET['orderid']) && is_numeric($_GET['orderid']) && $payment = DataObject::get_by_id('Payment', $_GET['orderid'])) {
			return $payment;
		}
		return null;
	}

	function callback() {

		$payment = $this->payment();
		if (!$payment)
			return;

		if (isset ($_GET['tid'])) {
			$payment->TransactionID = $_GET['tid'];
		}
		if (isset ($_GET['cardid'])) {
			$payment->CardID = $_GET['cardid'];
		}
		if (isset ($_GET['cardnopostfix'])) {
			$payment->CardNoPostFix = $_GET['cardnopostfix'];
		}
		if (isset ($_GET['transfee'])) {
			$payment->TransactionFee = $_GET['transfee'];
		}

		if (isset ($_GET['fraud']) && $_GET['fraud'] == 1) { //fraud check
			//suspected fraud, admin intervention required
			$payment->Message = _t('EpaydkPayment.STAFFINTERVENTION', 'Staff intervention required');
			$payment->Status = 'Incomplete';
		}
		elseif (isset ($_GET['eKey']) && $_GET['eKey'] != $payment->generateMD5()) { //md5 cross-check
			//tampering has occurred, admin intervention requried
			$payment->Message = _t('EpaydkPayment.MD5STAFFINTERVENTION', 'MD5 mismatch - Staff intervention required');
			if(Director::isDev()) $payment->Message .= ".\n".$payment->generateMD5()." didn't match ".$_GET['eKey'];
				if(Director::isDev()) $payment->Message .= ".\n ".EpaydkPayment::$supported_currencies[$payment->Amount->Currency]['code']." vs ".$_GET['cur']." ".($payment->Amount->Amount*100)." vs ".$_GET['amount']." ".$payment->ID." vs ".$_GET['orderid'];
			$payment->Status = 'Incomplete';
		} else {
			$payment->Status = 'Success';
			$payment->Message = _t('EpaydkPayment.PAYMENTSUCCESS', "Payment successfully recieved via ePay");
		}

		$payment->write();
	}

	function accept() {		
		$this->doRedirect();
	}

	function decline() {
		if($payment = $this->payment()) {
			$payment->Status = 'Failure';
			$payment->write();
		}
		$this->doRedirect();
	}

	function doRedirect() {
		
		$payment = $this->payment();
		if ($payment && $obj = $payment->PaidObject()) {
			Director::redirect($obj->Link());
			return;
		}

		Director::redirect(Director::absoluteURL('home', true)); //TODO: make this customisable in Payment_Controllers
		return;
	}

}
?>
