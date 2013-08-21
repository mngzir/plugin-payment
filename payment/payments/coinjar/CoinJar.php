<?php
class CoinJar {

    private $_apiEndpoint = 'https://api.coinjar.io/v1/';
    private $_checkoutEndpoint;
    private $_apiKey;
    private $_checkoutUser;
    private $_checkoutPassword;

    public function __construct($user = '', $password = '', $apikey = '', $sandbox = false) {
        if($sandbox) {
            $this->_checkoutEndpoint = 'https://checkout.sandbox.coinjar.io/api/v1/';
        } else {
            $this->_checkoutEndpoint = 'https://checkout.coinjar.io/api/v1/';
        }
        $this->_apiKey = $apikey;
        $this->_checkoutUser = $user;
        $this->_checkoutPassword = $password;
    }

    /**
     * Retrieve account information
     *
     * @return mixed (json)
     */
    public function accountInformation() {
        return $this->_doApiRequest('account');
    }

    /**
     * List bitcoin addresses
     *
     * @param int $limit
     * @param int $offset
     * @return mixed (json)
     */
    public function listBitcoinAddresses($limit = 100, $offset = 0) {
        return $this->_doApiRequest('bitcoin_addresses', array('limit' => $limit, 'offset' => $offset));
    }

    /**
     * Retrieve bitcoin address
     *
     * @param $address
     * @return mixed (json)
     */
    public function bitcoinAddress($address) {
        return $this->_doApiRequest('bitcoin_addresses/'.$address);
    }

    /**
     * Generate bitcoin address given a label
     *
     * @param $label
     * @return mixed (json)
     */
    public function generateBitcoinAddress($label) {
        return $this->_doApiRequest('bitcoin_addresses', array('label' => $label), 'post');
    }

    /**
     * List contacts
     *
     * @param int $limit
     * @param int $offset
     * @return mixed (json)
     */
    public function listContacts($limit = 100, $offset = 0) {
        return $this->_doApiRequest('contacts', array('limit' => $limit, 'offset' => $offset));
    }

    /**
     * Retrieve contact by the $uuid
     *
     * @param $uuid
     * @return mixed (json)
     */
    public function contact($uuid) {
        return $this->_doApiRequest('contacts/'.$uuid);
    }


    /**
     * Create contact
     *
     * @param $payee is the email or the bitcoin address
     * @param $name
     * @return mixed (json)
     */
    public function createContact($payee, $name) {
        return $this->_doApiRequest('contacts', array('contact[payee]' => $payee, 'contact[name]' => $name), 'post');
    }

    /**
     * Delete contact
     *
     * @param $uuid
     * @return mixed (json)
     */
    public function deleteContact($uuid) {
        return $this->_doApiRequest('contacts/'.$uuid, null, 'delete');
    }

    /**
     * List all payments
     *
     * @param int $limit
     * @param int $offset
     * @return mixed (json)
     */
    public function listPayments($limit = 100, $offset = 0) {
        return $this->_doApiRequest('payments', array('limit' => $limit, 'offset' => $offset));
    }

    /**
     * Retrieve a payment by the $uuid
     *
     * @param $uuid
     * @return mixed (json)
     */
    public function payment($uuid) {
        return $this->_doApiRequest('payments/'.$uuid);
    }

    /**
     * Create a new payment
     *
     * @param $payee is the email or the bitcoin address
     * @param $amount minimum is 0.00005430BTC
     * @param $reference
     * @return mixed (json)
     */
    public function createPayment($payee, $amount, $reference) {
        return $this->_doApiRequest('payments', array('payment[payee]' => $payee, 'payment[amount' => $amount, 'payment[reference]' => $reference), 'post');
    }

    /**
     * Check if the payment is confirmed or not
     *
     * @param $uuid
     * @return mixed (json)
     */
    public function confirmPayment($uuid) {
        return $this->_doApiRequest('payments/'.$uuid.'/confirm', null, 'post');
    }

    /**
     * List all transactions
     *
     * @param int $limit
     * @param int $offset
     * @return mixed (json)
     */
    public function listTransactions($limit = 100, $offset = 0) {
        return $this->_doApiRequest('transactions', array('limit' => $limit, 'offset' => $offset));
    }

    /**
     * Retrieve transaction information
     *
     * @param $uuid
     * @return mixed (json)
     */
    public function transaction($uuid) {
        return $this->_doApiRequest('transactions/'.$uuid);
    }

    /**
     * Retrieve the rate of conversion between currencies
     * @param $currency could be : BTC, USD, AUD, NZD, CAD, EUR, GBP, SGD, HKD, CHF, JPY
     * @return mixed (json)
     */
    public function fairRate($currency) {
        return $this->_doApiRequest('fair_rate/'.strtoupper($currency));
    }

    /**
     * List orders
     *
     * @param int $limit is not on the documentation
     * @param int $offset is not on the documentation
     * @return mixed (json)
     */
    public function listOrders($limit = 100, $offset = 0) {
        return $this->_doCheckoutRequest('orders', array('limit' => $limit, 'offset' => $offset));
    }

    /**
     * Retrieve order information
     *
     * @param $uuid
     * @return mixed (json)
     */
    public function order($uuid) {
        return $this->_doCheckoutRequest('orders/'.$uuid);
    }

    /**
     * Retrieve the url of payment of an order given the $uuid
     *
     * @param $uuid
     * @return string
     */
    public function orderPage($uuid) {
        return 'https://checkout.coinjar.io/orders/'.$uuid;
    }

    /**
     * Create a new order
     * $items is an array of the form:
     * $items[k]['name'] = 'name';
     * $items[k]['quantity'] = 'quantity';
     * $items[k]['amount'] = 'amount'; // in order currency
     *
     * @param $items
     * @param $currency 3-character ISO code (BTC for bitcoin)
     * @param $merchant_invoice
     * @param $merchant_reference
     * @param $notify_url IPN callback URL
     * @param $return_url After payment is received, the URL to direct the user to
     * @param $cancel_url If payment is cancelled, the URL to direct the user to
     * @return mixed (json)
     */
    public function createOrder($items, $currency, $merchant_invoice, $merchant_reference, $notify_url, $return_url, $cancel_url) {
        $params = array(
            'order[currency]' => $currency,
            'order[merchant_invoice]' => $merchant_invoice,
            'order[merchant_reference]' => $merchant_reference,
            'order[notify_url]' => $notify_url,
            'order[return_url]' => $return_url,
            'order[cancel_url]' => $cancel_url
        );
        $k = 0;
        foreach($items as $item) {
            $params['order[order_items_attributes['.$k.'][name]]'] = $item['name'];
            $params['order[order_items_attributes['.$k.'][quantity]]'] = $item['quantity'];
            $params['order[order_items_attributes['.$k.'][amount]]'] = $item['amount'];
            $k++;
        }
        return $this->_doCheckoutRequest('orders', $params, 'post');
    }

    /**
     * Request for regular API methods
     *
     * @param $action
     * @param null $params
     * @param string $method
     * @return mixed (json)
     */
    private function _doApiRequest($action, $params = null, $method = "get") {
        return $this->_doRequest($this->_apiEndpoint, $action, $this->_apiKey, $params, $method);
    }

    /**
     * Request for Checkout API methods
     *
     * @param $action
     * @param null $params
     * @param string $method
     * @return mixed (json)
     */
    private function _doCheckoutRequest($action, $params = null, $method = "get") {
        return $this->_doRequest($this->_checkoutEndpoint, $action, $this->_checkoutUser.":".$this->_checkoutPassword, $params, $method);
    }

    /**
     * Generic request
     *
     * @param $endpoint
     * @param $action
     * @param $user API key or Checkout's user & password in format [user]:[password]
     * @param null $params
     * @param string $method
     * @return mixed (json)
     */
    private function _doRequest($endpoint, $action, $user, $params = null, $method = "get") {
        $request = '';
        if($params!=null && is_array($params)) {
            foreach($params as $key => $value) {
                $request .= '&'.$key.'='.$value;
            }
        }
        if(strtolower($method)=='post') {
            $curl = curl_init($endpoint.$action.'.json');
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($curl, CURLOPT_POSTFIELDS, $request);
        } else if(strtolower($method)=='delete') {
            $curl = curl_init($endpoint.$action.'.json?'.$request);
            curl_setopt($curl, CURLOPT_POST, false);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
        }else {
            $curl = curl_init($endpoint.$action.'.json?'.$request);
            curl_setopt($curl, CURLOPT_POST, false);
            curl_setopt($curl, CURLOPT_HTTPGET, true);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
            curl_setopt($curl, CURLOPT_POSTFIELDS, $request);
        }

        curl_setopt($curl, CURLOPT_USERPWD, $user);
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }
}
?>