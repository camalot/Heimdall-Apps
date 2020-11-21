<?php namespace App\SupportedApps\Jenkins;

class Jenkins extends \App\SupportedApps implements \App\EnhancedApps {
    public $config;

    //protected $login_first = true; // Uncomment if api requests need to be authed first
    //protected $method = 'POST';  // Uncomment if requests to the API should be set by POST

    function __construct() {
        //$this->jar = new \GuzzleHttp\Cookie\CookieJar; // Uncomment if cookies need to be set
    }

    public function test()
    {
        $test = parent::appTest($this->url('api/json'), $this->attrs);

        echo $test->status;
    }

    public function livestats()
    {
        $status = 'inactive';
        $res = parent::execute($this->url('api/json?depth=1'), $this->attrs);
        $details = json_decode($res->getBody());

        $data = [];
        if($details) {
            $data['job_count'] = $details->binariesSummary->artifactsSize;
            $data['overall_health'] = $details->binariesSummary->artifactsCount;
            $status = 'active';
        }
        return parent::getLiveStats($status, $data);
        
    }
    public function url($endpoint)
    {
        $this->attrs = ['auth'=> [$this->config->username, $this->config->password, 'Basic']];
        $api_url = parent::normaliseurl($this->config->url).$endpoint;
        return $api_url;
    }
}