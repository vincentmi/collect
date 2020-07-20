<?php

class HttpResponse {
    public $status = 200 ;
    public $version = 'HTTP/1.0';
    public $desc = 'OK';
    public $body = '';

    public $headers = [];

    public function __toString()
    {
        return $this->body;
    }


    public function __construct($text)
    {
        $lines = explode("\n" , trim($text));

        $bodyStarted = false;
        $body = [];

        for($i = 0 ,$m = count($lines) ;$i < $m ;$i++)
        {
            if($i == 0)
            {
                $statusLine = explode(" ",$lines[$i]);
                $this->version = $statusLine[0];
                $this->status = $statusLine[1];
                $this->desc = $statusLine[2];
            }else if($bodyStarted == false){
                $pos = strpos($lines[$i] , ':');
                if($pos){
                    $field = substr($lines[$i] , 0 , $pos);
                    $value = substr($lines[$i] , $pos+1);
                    $this->headers[$field] = $value;
                }else{
                    $bodyStarted = true;
                }
            }else{
                $body[] = $lines[$i];
            }
        }

        $this->body = implode("\r\n" , $body);

    }

}

class HttpClient {

    protected $timeout = 30;

    public $sslKey = '';
    public $sslPassword = '';
    public $sslCert = '';
    public $sslInfo = '';

    protected function attachHeader(&$options ,$headers,$len = 0)
    {
        if($len > 0)
        {
            $headers['Content-Length'] = $len;
        }

        $headerData = [];
        foreach( $headers as  $field => $value)
        {
            $headerData [] = $field .': '.$value;
        }

        if($headerData)
        {
            $options[CURLOPT_HTTPHEADER] = $headerData;
        }
    }

    protected function attachBase(&$options)
    {
        $options[CURLOPT_CONNECTTIMEOUT] =  $this->timeout - 2;
        $options[CURLOPT_HEADER] =  true;
        $options[CURLOPT_RETURNTRANSFER] = true;
        $options[CURLOPT_TIMEOUT] =  $this->timeout;
    }


    protected function attachSSL(&$options)
    {
        if ($this->sslKey) {
            $options[CURLOPT_SSLKEY] = $this->sslKey;
        }
        if ($this->sslPassword) {
            $options[CURLOPT_SSLKEYPASSWD] = $this->sslPassword;
        }
        if ($this->sslCert) {
            $options[CURLOPT_SSLCERT] = $this->sslCert;
        }
        if ($this->sslInfo) {
            $options[CURLOPT_CAINFO] = $this->sslInfo;
        }

        $options[CURLOPT_SSL_VERIFYPEER] = false;
        $options[CURLOPT_SSL_VERIFYHOST] = false;
    }


    public function parseGetUrl($url,$data)
    {
        $urls = parse_url($url);
        $dataOrig = [];
        if(isset($urls['query']) && $urls['query'])
        {
            parse_str($urls['query'],$dataOrig);
        }
        $query = http_build_query(array_merge_recursive($dataOrig , $data));
        $url = $urls['scheme'].'://'.$urls['host'].($urls['port']?':'.$urls['port'] : '');
        if(isset($urls['path'])){
            $url .= $urls['path'];
        }
        if($query)
            $url .= '?'.$query;

        return $url ;
    }

    public function get($url,$data = [],$headers=[])
    {
        $getUrl = $this->parseGetUrl($url ,$data);

        $options = [
            CURLOPT_URL=>$getUrl,
            CURLOPT_POST=>false,
        ];

        $this->attachBase($options);
        $this->attachHeader($options , $headers);
        $this->attachSSL($options);

        $ch = curl_init();
        if($ch  === false)
        {
            throw new Exception("curl init fail");
        }


        curl_setopt_array($ch, $options);

        $result = curl_exec($ch);
        $err = curl_errno($ch);
        $errMsg = curl_error($ch);

        curl_close($ch);
        if ($result === false) {
            throw new Exception("curl error (" . $err . ')' . $errMsg);
        } else {
            return new HttpResponse($result);
        }
    }


    public function post($url, $data , $headers=[])
    {
        $ch = curl_init();

        if($ch  === false)
        {
            throw new Exception("curl init fail");
        }
        $options = [
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => $this->headerData($headers ),
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_POST => true
        ];
        $this->attachBase($options);
        $this->attachSSL($options);

        curl_setopt_array($ch, $options);
        $result = curl_exec($ch);
        $err = curl_errno($ch);
        $errMsg = curl_error($ch);
        curl_close($ch);

        if ($result === false) {
            throw new Exception("curl error (" . $err . ')' . $errMsg);
        } else {
            return new HttpResponse($result);
        }
    }


    public function put($url, $data , $headers=[])
    {
        $ch = curl_init();

        if($ch  === false)
        {
            throw new Exception("curl init fail");
        }

        $dataStr = is_array($data) ? http_build_query($data) : $data;

        $options = [
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST=>'PUT',
            CURLOPT_POSTFIELDS => $dataStr,
            CURLOPT_POST => true,
        ];
        $this->attachBase($options);
        $this->attachHeader($options , $headers);
        $this->attachSSL($options);

        curl_setopt_array($ch, $options);

        $result = curl_exec($ch);
        $err = curl_errno($ch);
        $errMsg = curl_error($ch);
        curl_close($ch);

        if ($result === false) {
            throw new Exception("curl error (" . $err . ')' . $errMsg);
        } else {
            return new HttpResponse($result);
        }
    }


    public function delete($url,$data = [], $headers=[])
    {
        $ch = curl_init();

        if($ch  === false)
        {
            throw new Exception("curl init fail");
        }

        $getUrl = $this->parseGetUrl($url ,$data);

        $options = [
            CURLOPT_URL => $getUrl,
            CURLOPT_CUSTOMREQUEST=>'DELETE'
        ];
        $this->attachBase($options);
        $this->attachHeader($options , $headers);
        $this->attachSSL($options);

        curl_setopt_array($ch, $options);

        $result = curl_exec($ch);
        $err = curl_errno($ch);
        $errMsg = curl_error($ch);
        curl_close($ch);

        if ($result === false) {
            throw new Exception("curl error (" . $err . ')' . $errMsg);
        } else {
            return new HttpResponse($result);
        }
    }



}