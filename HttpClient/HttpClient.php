<?php

class HttpClient {
    protected function getClient($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //如果把这行注释掉的话，就会直接输出
        return $ch;
    }

    protected function getUrl($url , $queryData)
    {
        $urls = parse_url($url);
        $dataOrig = [];
        if(isset($urls['query']) && $urls['query'])
        {
            parse_str($urls['query'],$dataOrig);
        }
        $query = http_build_query(array_merge_recursive($dataOrig , $queryData));
        $url = $urls['scheme'].'://'.$urls['host'];
        if(isset($urls['path'])){
            $url .= $urls['path'];
        }
        if($query)
            $url .= '?'.$query;

        return $url;
    }

    public function get($url,$data = [])
    {
        $ch = $this->getClient($this->getUrl($url , $data));
        $result=curl_exec($ch);
        curl_close($ch);
        return $result;
    }
}