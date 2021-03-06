<?php namespace App\SupportedApps\NetgearModem;

class NetgearModem extends \App\SupportedApps implements \App\EnhancedApps {

    public $config;
    protected $login_first = true; // Uncomment if api requests need to be authed first
    // protected $method = 'POST';  // Uncomment if requests to the API should be set by POST

    function __construct() {
        $this->jar = new \GuzzleHttp\Cookie\CookieJar;
    }


    public function login() {

    }

    public function test()
    {
        $username = urlencode($this->config->username);
        $password = urlencode($this->config->password);
        $auth_token = base64_encode($username.":".$password);
        
        $attrs = [
            'cookies' => $this->jar,
            'headers' => [
                "Authorization" => "Basic ".$auth_token
            ]
        ];
        $res = parent::execute($this->url(''), $attrs, false, 'GET');
        $test = parent::appTest($this->url(''), $attrs);
        echo $test->status;
    }
   
    public function livestats()
    {
        $status = 'inactive';
        $username = urlencode($this->config->username);
        $password = urlencode($this->config->password);
        $auth_token = base64_encode($username.":".$password);
        $data = [];


        $attrs = [
            'cookies' => $this->jar,
            'headers' => [
                "Authorization" => "Basic " . $auth_token
            ]
        ];
        $res = parent::execute($this->url(''), $attrs, false, 'GET');

        $res = parent::execute($this->url('DocsisStatus.htm'), $attrs, false, 'GET');
        $body = $res->getBody();

        $pattern = '/function\s(InitTagValue|InitDsTableTagValue)\(\)\s*\{.*?tagValueList\s*=\s*\'([^\']*)\'/is';
        
        
        preg_match_all($pattern, $body, $matches, PREG_SET_ORDER, 0);
        $match_count = count($matches);
        $status = $match_count > 0 ? "active" : "inactive";
            
        for ($i = 0; $i < $match_count; $i++) {
            $match = $matches[$i];
            $tagList = explode("|", $match[2]);
            $action = strtoupper($match[1]);
            $correctable = 0;
            $uncorrectable = 0;
            switch ($action) {
                case "INITTAGVALUE": // connectivity status
                    $data['connectivity_state'] = $tagList[2];
                break;
                case "INITDSTABLETAGVALUE": // downstream data
                    // 1|Locked|QAM256|42|399000000 Hz|11.8|38.9|232112|24980
                    // Channel (text) | Lock Status (text) | Modulation (text) | Channel ID (text) | Frequency (text) | Power (text) | SNR (text) | Correctables (text) | Uncorrectables (text)
                    $shifted = array_shift($tagList);
                    $chunks = array_chunk($tagList, 9);
                    for($x = 0; $x < count($chunks); $x++) {
                        $chunk = $chunks[$x];
                        if (count($chunk) == 9) {
                            $correctable += intval($chunk[7]);
                            $uncorrectable += intval($chunk[8]);
                        }
                    }
                break;
                default:

                break;
            }
        }

        $data['uncorrectable_codewords'] = $this->short_number_format($uncorrectable, 2);
        $data['correctable_codewords'] = $this->short_number_format($correctable, 2);
        return parent::getLiveStats($status, $data);
        
    }

    public function short_number_format($n, $p) {
        if ($n < 100 ) {
            $n_format = number_format($n);
        } else if ($n < 1000000) {
            // Anything less than a million
            $n_format = number_format($n / 1000, $p) . 'K';
        } else if ($n < 1000000000) {
            // Anything less than a billion
            $n_format = number_format($n / 1000000, $p) . 'M';
        } else {
            // At least a billion
            $n_format = number_format($n / 1000000000, $p) . 'B';
        }
        return $n_format;
    }

    public function url($endpoint)
    {
        $api_url = parent::normaliseurl($this->config->url).$endpoint;
        return $api_url;
    }
}
