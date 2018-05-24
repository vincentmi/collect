<?php

require 'HttpClient.php';

class Wechat extends HttpClient{
	protected $appId ;
	protected $appSecret ;
	const CACHE_FOLDER = '.cache';
	const CACHE_EXPIRE = 300;
	public function __construct($appId , $appSecret)
	{
		$this->appId = $appId;
		$this->appSecret = $appSecret;
	}

	protected function fetchCache($key)
    {
        $cache = __DIR__.DIRECTORY_SEPARATOR.static::CACHE_FOLDER.DIRECTORY_SEPARATOR . $key.'.php';
        if(file_exists($cache)){
            $time = filemtime($cache);

            if(time() - $time < static::CACHE_EXPIRE)
            {
                return  include ($cache);
            }
        }
        return null;
    }

    protected function putCache($key,$value)
    {
        $cache = __DIR__.DIRECTORY_SEPARATOR.static::CACHE_FOLDER.DIRECTORY_SEPARATOR . $key.'.php';
        $data = '<?php return '.var_export($value , true).';?>';
        file_put_contents($cache,$data);
    }

    public function post($url , $data)
    {
        $ch = $this->getClient($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $result=curl_exec($ch);
        curl_close($ch);
        return $result;
    }

	public function getAccessToken()
    {
        $result = $this->fetchCache(__FUNCTION__);
        if(!$result)
        {
            $url = 'https://api.weixin.qq.com/cgi-bin/token';
            $data = [
                'appid'=>$this->appId ,
                'secret'=>$this->appSecret,
                'grant_type'=>'client_credential'
            ];
            $result = $this->get($url,$data);
            $result  = json_decode($result,true ,512,JSON_OBJECT_AS_ARRAY);
            $this->putCache(__FUNCTION__ , $result);
        }
        return $result;
    }

    public function getUserInfo($openId)
    {
        $token = $this->getAccessToken();
        $data = [
            'openid'=>$openId,
            'lang'=>'zh_CN',
            'access_token'=>$token['access_token']
        ];
        $data = $this->get('https://api.weixin.qq.com/cgi-bin/user/info' ,$data );
        return json_decode($data , true , 512 ,JSON_OBJECT_AS_ARRAY );
    }
}