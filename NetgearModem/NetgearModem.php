<?php namespace App\SupportedApps\NetgearModem;

class NetgearModem extends \App\SupportedApps implements \App\EnhancedApps {

    public $config;
    // protected $login_first = true; // Uncomment if api requests need to be authed first
    // protected $method = 'POST';  // Uncomment if requests to the API should be set by POST

    function __construct() {
        $this->jar = new \GuzzleHttp\Cookie\CookieJar;
    }
    public function test()
    {
        $test = parent::appTest($this->url('GenieLogin.asp'));
        echo $test->status;
    }
   
    public function livestats()
    {
        $status = 'inactive';
        $username = urlencode($this->config->username);
        $password = urlencode($this->config->password);
        $webtoken = urlencode($this->config->webtoken);
        $data = [];
        // get web token

        $res = parent::execute($this->url('GenieLogin.asp'));
        $body = $res->getBody();
        // <input type="hidden" name="webToken" value=xxxxxxxxx />

        $pattern = '/<input\\s+type="hidden"\\s+name="webToken"\\s+value=(\\d{1,})\\s+\\/>/i';
        //echo $body;
        preg_match_all($pattern, $body, $matches, PREG_SET_ORDER, 0);
        if (count($matches) > 0) {
            $webtoken = $matches[0][1]; //'1605723972';
            $attrs = [
                'body' => 'loginUsername='.$username.'&loginPassword='.$password.'&webToken='.$webtoken,
                'cookies' => $this->jar,
                'headers' => ['content-type' => 'application/x-www-form-urlencoded']
            ];
            $res = parent::execute($this->url('goform/GenieLogin'), $attrs, false, 'POST');
            $attrs = [
                'cookies' => $this->jar,
            ];
            $body = $res->getBody();
            // echo $body;
            $res = parent::execute($this->url('DocsisStatus.asp'), $attrs, false, 'GET');
            $body = $res->getBody();
            //echo $body;
            /*
                <td class="style1">Connectivity State</td>
                <td>OK</td>
                <td>Operational</td>
            */

            $pattern = '/<td\\sclass="style1">Connectivity State<\/td>\\s*<td>(.*?)<\/td>/mi';
            preg_match_all($pattern, $body, $matches, PREG_SET_ORDER, 0);
            if (count($matches) > 0) {
                $data['connectivity_state'] = $matches[0][1];
                $status = "active";
            } else {
                $data['connectivity_state'] = "???";
                $status = "active";
            }

            // $pattern = '/<tr>(<td>.*?<\/td>){9}<td>(\\d+)<\/td><\/tr>/mi';
            // preg_match_all($pattern, $body, $matches, PREG_SET_ORDER, 0);
            // $match_count = count($matches);
            // $uncorrectable = 0;
            // for ($i = 0; $i < $match_count; $i++) {
            //     $uncorrectable += intval($matches[$i][1]);
            //     $status = "active";
            // }
            // $data['uncorrectable_codewords'] = number_format($uncorrectable);
        }



        // $attrs = [
        //     'body' => 'loginUsername='.$username.'&loginPassword='.$password.'&webToken='.$webtoken,
        //     'cookies' => $this->jar,
        //     'headers' => ['content-type' => 'application/x-www-form-urlencoded']
        // ];
        // $res = parent::execute($this->url('goform/GenieLogin'), $attrs, false, 'POST');
        // $attrs = [
        //     'cookies' => $this->jar,
        // ];
        // $body = $res->getBody();
        // // echo $body;
        // $res = parent::execute($this->url('DocsisStatus.asp'), $attrs, false, 'GET');
        // $body = $res->getBody();
        // //echo $body;
        // /*
        //     <td class="style1">Connectivity State</td>
        //     <td>OK</td>
        //     <td>Operational</td>
        // */

        // $pattern = '/<td\\sclass="style1">Connectivity State<\/td>\\s*<td>(.*?)<\/td>/mi';
        // preg_match_all($pattern, $body, $matches, PREG_SET_ORDER, 0);
        // if (count($matches) > 0) {
        //     $data['connectivity_state'] = $matches[0][1];
        //     $status = "active";
        // } else {
        //     $data['connectivity_state'] = "???";
        //     $status = "active";
        // }

        return parent::getLiveStats($status, $data);
        
    }

    public function url($endpoint)
    {
        $api_url = parent::normaliseurl($this->config->url).$endpoint;
        return $api_url;
    }
}
