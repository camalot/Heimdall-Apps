<?php namespace App\SupportedApps\TPLinkSwitch;

class TPLinkSwitch extends \App\SupportedApps implements \App\EnhancedApps {

    public $config;

    //protected $login_first = true; // Uncomment if api requests need to be authed first
    //protected $method = 'POST';  // Uncomment if requests to the API should be set by POST

    function __construct() {
        $this->jar = new \GuzzleHttp\Cookie\CookieJar; // Uncomment if cookies need to be set
    }

    public function test()
    {
        $url = $this->url('/logon.cgi');
        $attrs = [];
        if(empty($this->config->url)) {
            echo 'No URL has been specified';   
        }
        $res = $this->execute($url, $attrs);
        if($res == null) {
            echo $this->error;
        }
        switch($res->getStatusCode()) {
            case 200:
            case 401:
                $status = 'Successfully communicated with the API';
                break;
            case 404:
                $status = 'Failed: Please make sure your URL is correct and that there is a trailing slash';
                break;
            default:
                $status = 'Something went wrong... Code: '.$res->getStatusCode();
                break;
        }
        echo $status;
    }

    public function livestats()
    {
        $status = 'inactive';
        // $username = urlencode($this->config->username);
        // $password = urlencode($this->config->password);

        //  $attrs = [
        //     'body' => 'username='.$username.'&password='.$password.'&logon=Login',
        //     'cookies' => $this->jar,
        //     'headers' => ['content-type' => 'application/x-www-form-urlencoded']
        // ];
        // $overridevars = [
        //     'http_errors' => true, 
        //     'timeout' => 30, 
        //     'connect_timeout' => 5,
        // ];
        // $res = parent::execute($this->url('/logon.cgi'), $attrs, $overridevars, 'POST');
        // echo $this->error;
        // $body = $res->getBody();
        // $res = parent::execute($this->url('PortStatisticsRpm.htm'), $attrs, false, 'GET');
        // $body = $res->getBody();

        // $data = [];

        // $pattern = '/var\\s+all_info\\s?=\\s?(\\{[^\\}]+\\})/gmsi';
        // preg_match_all($pattern, $body, $matches, PREG_SET_ORDER, 0);
        // if (count($matches) > 0) {
        //     $details = json_decode($matches[0][1]);
        //     $packets = array_chunk($details->pkts,4);
        //     $ttxGoodPkt = 0;
        //     $ttxBadPkt = 0;
        //     $trxGoodPkt = 0;
        //     $trxBadPkt = 0;
        //     for ($i = 0; $i < count($packets); $i++) {
        //         $port = $packets[$i];
        //         $txGoodPkt = intval($port[0]);
        //         $txBadPkt = intval($port[1]);
        //         $rxGoodPkt = intval($port[2]);
        //         $rxBadPkt = intval($port[3]);
        //         $ttxGoodPkt += $txGoodPkt;
        //         $ttxBadPkt += $txBadPkt;
        //         $trxGoodPkt += $rxGoodPkt;
        //         $trxBadPkt += $rxBadPkt;
        //     }
        //     if($details) {
        //         $data['tx_good_packets'] = formatBigNumber($ttxGoodPkt);
        //         $data['tx_bad_packets'] = formatBigNumber($ttxBadPkt);
        //         $data['rx_good_packets'] = formatBigNumber($trxGoodPkt);
        //         $data['rx_bad_packets'] = formatBigNumber($trxBadPkt);
        //         $status = "active";
        //     }
        // }

        

        return parent::getLiveStats($status, []);
        
    }

    function formatBigNumber($value, $precision = 2) { 
        $units = array('', 'K', 'M', 'B', 'T'); 

        $value = max($value, 0); 
        $pow = floor(($value ? log($value) : 0) / log(1000)); 
        $pow = min($pow, count($units) - 1); 

        // Uncomment one of the following alternatives
        $value /= pow(1000, $pow);
        //$value /= (1 << (10 * $pow)); 

        return round($bytes, $precision) . ' ' . $units[$pow]; 
    } 

    public function url($endpoint)
    {
        $api_url = parent::normaliseurl($this->config->url).$endpoint;
        return $api_url;
    }
}
