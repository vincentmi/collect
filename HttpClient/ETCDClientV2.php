<?php

require 'HttpClient.php';

class ETCDClientV2 extends HttpClient
{
    protected $endpoint = 'http://127.0.0.1:2397';

    public function __construct($endpoint = '')
    {
        if ($endpoint) {
            $this->endpoint = $endpoint;
        }
    }

    protected function result($resp)
    {
        if ($resp->status >= 200 && $resp->status < 300) {
            return json_decode($resp->body, false, 512);
        } else {
            throw new Exception("network error httpCode:" . $resp->status);
        }
    }

    protected function url($path)
    {
        return $this->endpoint . $path;
    }


    public function getVersion()
    {
        $resp = $this->get($this->url('/version'));
        return $this->result($resp);
    }


    public function setKey($key, $value, $ttl = 0)
    {
        $data = ['value' => $value];
        if ($ttl > 0) {
            $data['ttl'] = $ttl;
        }
        $resp = $this->put($this->url('/v2/keys' . $key), $data);

        return $this->result($resp);
    }

    public function getKey($key)
    {
        $resp = $this->get($this->url('/v2/keys' . $key));
        return $this->result($resp);
    }

    public function delKey($key)
    {
        $resp = $this->delete($this->url('/v2/keys' . $key));
        return $this->result($resp);
    }

    public function lsDir($dir = '/')
    {
        $resp = $this->get($this->url('/v2/keys' . $dir));

        return $this->result($resp);

    }

    public function rmDir($key, $recursive = false)
    {
        $resp = $this->delete($this->url('/v2/keys' . $key), ['dir' => 'true', 'recursive' => $recursive]);
        return $this->result($resp);
    }

    public function mkDir($dir)
    {
        $resp = $this->put($this->url('/v2/keys' . $dir), ['dir' => 'true']);
        return $this->result($resp);
    }


    public function envArr($dir)
    {
        $data = $this->lsDir($dir);

        $envArray = [];

        if (isset($data->node)) {
            $prefix = $data->node->key;
            $prefixLen = strlen($prefix);
            foreach ($data->node->nodes as $node) {
                if ($node->dir != 1) {
                    $envKey = substr($node->key, $prefixLen + 1);
                    $envArray[$envKey] = $node->value;
                }
            }
        }
        return $envArray;
    }

    public function envFromFile($file)
    {
        $data = $this->loadIni($file);
        $env = [];
        foreach ($data as $key => $value) {
            if ($key) {
                $env[$key] = $value;
            }
        }
        return $env;
    }

    protected function loadIni($file)
    {
        $lines = file($file);
        $data = [];
        foreach($lines as $line)
        {
            if($pos = strpos($line , '='))
            {
                $key = trim(substr($line , 0 ,$pos));
                $value = trim(substr($line , $pos+1));
                if(!preg_match('/[a-z0-9_]+/' , $key))
                {
                   continue;
                }
                if(!isset($data[$key]))
                {
                    $data[$key] = strval($value);
                }
            }
        }
        return $data;
    }


    public function env($dir)
    {
        $envArray = $this->envArr($dir);
        $str = '';
        foreach ($envArray as $key => $value) {
            $str .= $key . '=' . $value . "\r\n";
        }
        return $str;
    }

    public function sync($dir , $file , $overwrite = 0)
    {
        $etcdArr = $this->env($dir);


    }


    public function _diff($etcdArr , $fileArr)
    {
        foreach($etcdArr as $key=>$value)
        {
            $fValue = '';
            if(isset($fileArr[$key]))
            {
                if($fileArr[$key] == $value)
                {
                    $status = 'match';
                }else{
                    $status = 'not_match';
                }
                $fValue = $fileArr[$key];
                unset($fileArr[$key]);
            }else{
                $status = 'missing';
            }

            if($status == 'match')
            {
                $this->output('green' , $key .'='.$value);
            }else if($status == 'not_match')
            {
                $this->output('yellow' , $key .'='.$value .'|'.$fValue);
            }else if($status == 'missing')
            {
                $this->output('red' , $key .'='.$value);
            }else{
                $this->output('' , $key .'='.$value);
            }
        }

        foreach($fileArr as $key=>$value)
        {
            $this->output('blue' , $key .'='.$value);
        }
    }


    public function diff($dir , $file)
    {
        $etcdArr = $this->envArr($dir);
        $fileArr = $this->envFromFile($file);
        $this->_diff($etcdArr , $fileArr);

    }

    public function rdiff($dir , $file)
    {
        $etcdArr = $this->envArr($dir);
        $fileArr = $this->envFromFile($file);
        $this->_diff($fileArr,$etcdArr);

    }



    function output($color , $string)
    {
        switch($color)
        {
            case 'red' : $color ='31m' ;break;
            case 'green' : $color ='32m' ;break;
            case 'yellow' : $color ='33m' ;break;
            case 'blue' : $color ='34m' ;break;
            default:$color='37m';
        }
        echo "\033[".$color.$string."\033[0m\n";
    }


}