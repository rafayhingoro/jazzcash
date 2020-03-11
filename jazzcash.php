<?php
 if (!defined('BASEPATH')) exit('No direct script access allowed');

class jazzcash {

    /**
     * @var credentials
     * Credentials
     */
    private $_cred;

    /**
     * @var prefix constant 
     */
    public $_prefix = "JC_";

    /**
     * @var hash
     */
    public $_hash = "SHA256";

    /**
     * @var data
     */
    public $_data = [];

    /**
     * @var version
     */
    public $_ver = '1.1';
    
    /**
     * @var urls
     */
    public $purl = '/ApplicationAPI/API/';

    /**
     * @var isToken
     */
    public $_isToken = false;

    /**
     * @var sandbox
     */
    public $sandbox = true;

    /**
     * @var Request Type
     */
    public $reqType = 'Authorize';

    /**
     * @var hashf Fields to be hashed
     */
    public $hashF = [];

    /**
     * Initialize controller
     */
	public function __construct()
	{
		$this->initCredentials();
    }
    
    /**
     * Init Credentials
     * 
     * @return void
     */
    public function initCredentials()
    {
        $this->_cred = new \stdClass();
        if( !defined($this->_prefix . 'SALT') || !defined($this->_prefix . 'PASS') || !defined($this->_prefix . 'MERCHANT_ID')) {
            throw new \Exception('JazzCash configuration missing');
            return false;
        }
        $this->_cred->salt   = constant($this->_prefix . 'SALT'); 
        $this->_cred->pass   = constant($this->_prefix . 'PASS'); 
        $this->_cred->mch    = constant($this->_prefix . 'MERCHANT_ID');
        return $this;
    }

    /**
     * API URL Version
     * 
     * @return String
     */
    public function ver()
    {
        if ( $this->_ver >= 2 ) {
            return $this->purl . $this->_ver . '/';
        }
        return $this->purl;
    }

    /**
     * Action Methods
     * 
     * @return string
     */
    public function actionURL()
    {
        $endpoint = '';
        switch( strtoupper($this->reqType) ) {
            case 'PAY':
                $endpoint =  'Purchase/PAY';
                $this->hashF = ['pp_CustomerCardNumber', 'pp_CustomerCardExpiry', 'pp_CustomerCardCvv','pp_Amount', 'pp_TxnRefNo',  'pp_MerchantID', 'pp_Password', 'pp_TxnCurrency','pp_Frequency','pp_InstrumentType'];
            break;
            case 'CHECK3DSENROLLMENT':
                $endpoint = $this->ver() . 'Purchase/Check3DsEnrollment';
                $this->hashF = ['pp_CustomerCardNumber', 'pp_CustomerCardExpiry', 'pp_CustomerCardCvv','pp_Amount', 'pp_TxnRefNo',  'pp_MerchantID', 'pp_Password', 'pp_TxnCurrency','pp_Frequency','pp_InstrumentType'];
            break;
            case 'AUTHORIZE':
                $endpoint = $this->ver() . 'authorize/AuthorizePayment';
                $this->hashF = ['pp_CustomerCardNumber', 'pp_CustomerCardExpiry', 'pp_CustomerCardCvv','pp_Amount', 'pp_TxnRefNo',  'pp_MerchantID', 'pp_Password', 'pp_TxnCurrency','pp_Frequency','pp_InstrumentType'];
            break;
            case 'CAPTURE':
                $endpoint = $this->ver() . 'authorize/Capture';
                $this->hashF = ['pp_CustomerCardNumber', 'pp_CustomerCardExpiry', 'pp_CustomerCardCvv','pp_Amount', 'pp_TxnRefNo',  'pp_MerchantID', 'pp_Password', 'pp_TxnCurrency','pp_Frequency','pp_InstrumentType'];
            break;
            case 'REFUND':
                $endpoint = $this->ver() . 'authorize/Refund';
                $this->hashF = ['pp_CustomerCardNumber', 'pp_CustomerCardExpiry', 'pp_CustomerCardCvv','pp_Amount', 'pp_TxnRefNo',  'pp_MerchantID', 'pp_Password', 'pp_TxnCurrency','pp_Frequency','pp_InstrumentType'];
            break;
            case 'VOID':
                $endpoint = $this->ver() . 'authorize/Void';
                $this->hashF = ['pp_TxnRefNo', 'pp_MerchantID', 'pp_Password'];
            break;
            case 'PAYMENTINQUIRY':
                $endpoint = $this->ver() . 'PaymentInquiry/Inquire';
                $this->hashF = ['pp_TxnRefNo', 'pp_MerchantID', 'pp_Password', 'pp_Version'];
            break;
        }

        if( $this->_isToken ) {
            return $endpoint . 'ViaToken';
        }
        return $endpoint;
    }

    /**
     * is Test
     * 
     * @return string
     */
    public function url()
    {
        $url = constant($this->_prefix . ($this->sandbox === false ? "LIVE" : "SANDBOX") . "_URL");
        return $url . $this->actionURL();
    }
    

    /**
     * Set Data
     * 
     * @param Array/String
     * 
     * @return void
     */
    public function set_data($attr, $val = '')
    {
        if(!is_array($attr)) {
            $this->_data[$attr] = $val;
        } else {
            foreach($attr as $a => $v) {
                $this->_data[$a] = $v;
            }
        }
        return true;
    }

    /**
     * Get Data
     * 
     * @param attr // to get single attribute
     * 
     * @return string/array
     */
    public function get_data($attr = false)
    {
        if($attr) {
            return isset($this->_data[$attr]) ? $this->_data[$attr] : null;
        }

        return $this->_data;
    }

    public function genString($i, $array, $res = '')
    {
        if( is_array($array) ) {
            foreach($array as $k => $a) {
                if( is_array($a) ) {
                    $res = $this->genString($i, $a, $res);
                } else if( $k == $i && !empty($a) && !is_null($a) ) {
                    $res = $a .'&';
                }
            }
        }
        return $res;
    }

    /**
     * Generate Secure Hash
     * 
     * Required Attributes
     * @param string secret key
     * @param int amount
     * @param string merchant id
     * @param mixed order info
     * 
     * @return string
     */
    public function secureHash()
    {
        $this->actionURL();
        sort($this->hashF);
    
        $f = '';
        foreach($this->hashF as $h) {
            $f .= $this->genString($h, $this->get_data());
        }
        $a = $this->_cred->salt ."&";
        $b = $a . substr($f, 0, -1);
        $hash = hash_hmac($this->_hash, $b, $this->_cred->salt);
        return $hash;
    }

    /**
     * Set Default attributes
     * 
     * @return void
     */
    public function loadDefaultAttr()
    {
        $this->set_data([
            "pp_TxnCurrency"        => "PKR",
            "pp_MerchantID"         => $this->_cred->mch,
            "pp_Password"           => $this->_cred->pass,
        ]);

        if( strtoupper($this->reqType) == 'PAY') {
            $this->set_data([
                "pp_TxnType" => "MPAY",
                "pp_Version"    => $this->_ver,
            ]);
        }
        $this->set_data('pp_SecureHash', $this->secureHash());
        
        if( $this->_ver < 2 && !in_array(strtoupper($this->reqType), ["PAY", "PAYMENTINQUIRY"])) {
            $a = [];
            $a[$this->reqType .'Request'] = $this->get_data();
            $this->_data = $a;
        }

        return $this;
    }

    /**
     * validate payload
     * 
     * @return boolean
     */
    public function validatePayload()
    {
        if( is_null($this->get_data('pp_TxnRefNo')) ) {
            throw new \Exception('Transaction reference number is required');
            return false;
        } else if ( is_null($this->get_data('pp_Amount'))) {
            throw new \Exception('Amount is missing');
            return false;
        } else if ( is_null($this->get_data('InstrumentDTO')) || ( !isset($this->get_data('InstrumentDTO')['pp_CustomerCardNumber']) || !isset($this->get_data('InstrumentDTO')['pp_CustomerCardExpiry']) || !isset($this->get_data('InstrumentDTO')['pp_CustomerCardCvv']) )) {
            throw new \Exception('Card details missing');
            return false;
        }

        return true;
    }

    /**
     * Send Request
     * 
     * @return json 
     */
    public function send()
    {
        if( !function_exists('curl_version') ) {
            throw new \Exception("Please enable curl");
            return false;
        }
        $this->loadDefaultAttr();

        $req = curl_init();

        // initiating request
        curl_setopt($req, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($req, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($req, CURLOPT_URL, $this->url() );
        curl_setopt($req, CURLOPT_POST, 1);
        curl_setopt($req, CURLOPT_POSTFIELDS, json_encode( $this->get_data() ) );
        curl_setopt($req, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        // Receive server response ...
        curl_setopt($req, CURLOPT_RETURNTRANSFER, true);

        $res = curl_exec($req);

        if (curl_errno($req)) {
            $res = ['curl_error' => curl_error($req)];
        }


        curl_close ($req);

        return $res;
    }
}
?>
