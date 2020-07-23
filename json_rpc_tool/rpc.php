<?php

error_reporting(E_ALL);
ini_set('display_errors',1);


class RpcClient
{

    private $timeout = 60;

    private $address = "127.0.0.1";
    private $connection = null;

    private $ip = "127.0.0.1";
    private $token = '';

    private $user;
    private $secret;
    private $rpcReflect = false;

    private $lastBinData = [];


    public function __construct($address , $user,$secret)
    {
        $this->address = $address;
        $this->user = $user;
        $this->secret = $secret;
    }

    public function setToken($token)
    {
      $this->token = $token;
      return $this;
    }

    private function doGetConnection()
    {
        $connection = stream_socket_client($this->address, $err_no, $err_msg);
        if (!$connection) {
            throw new \Exception("can not connect to $this->address , $err_no:$err_msg");
        }
        stream_set_blocking($connection, true);
        stream_set_timeout($connection, $this->timeout);
        return $connection;
    }

    public function getLatestBinData()
    {
        return $this->lastBinData;
    }

    private function encode($class,$method,$arguments = [])
    {
        $timestamp = time();
        $time = ceil(microtime(true) * 1000);

        $environments = $_SERVER;
        //$environments['CLIENT_TOKEN'] = $this->token;

        $ENV = [
            'X_CLIENT_IP'        => $this->ip,
            'X_USER_TOKEN'       => $this->token,
            'X_IS_REFLECT_GETOR' => isset($environments['X_IS_REFLECT_GETOR']) ? $environments['X_IS_REFLECT_GETOR'] : $this->rpcReflect,
            'CHANNEL_TYPE'       => isset($environments['CHANNEL_TYPE']) ? $environments['CHANNEL_TYPE'] : "",
            'CHANNEL_ID'         => isset($environments['CHANNEL_ID']) ? $environments['CHANNEL_ID'] : "",
            'TRACE_ID'           => isset($environments['TRACE_ID']) ? $environments['TRACE_ID'] : "",
            'PLATFORM'           => isset($environments['PLATFORM']) ? $environments['PLATFORM'] : "",
            'CLIENT_VERSION'     => isset($environments['CLIENT_VERSION']) ? $environments['CLIENT_VERSION'] : "",
            'CHANNEL'            => isset($environments['CHANNEL']) ? $environments['CHANNEL'] : "",
            'CLIENT_TOKEN'       => isset($environments['CLIENT_TOKEN']) ? $environments['CLIENT_TOKEN'] : "",
            'WS_AGENT'           => isset($environments['WS_AGENT']) ? $environments['WS_AGENT'] : "",
        ];

        $bin_data = [
            'version'     => '2.3',
            'access'      => [
                'user'      => $this->user,
                'password'  => md5($this->user . $this->secret . $timestamp),
                'timestamp' => $timestamp,
                'app'       =>[
                    'id'                => $this->user,
                    'requestTimeMillis' => $time,
                    'sign'              => md5($time . $this->secret),
                ],
            ],
            'class'       => $class,
            'method'      => $method,
            'param_array' => $this->rpcReflect && key($arguments) === 0 ? $arguments[0] : $arguments,
            'env'         => $ENV,
        ];

        $this->lastBinData = $bin_data;

        return $bin_data;
    }

    private function write($data)
    {
        $connection = $this->getConnection();
        $binData = json_encode($data)."\n";
        if (fwrite($connection, $binData) !== strlen($binData)) {
            throw new \Exception("Can not send data for $this->address");
        }

    }

    private function read()
    {
        $connection = $this->getConnection();
        $body = fgets($connection);
        return json_decode($body);
    }

    public function syncCall($class,$method,$arguments = [])
    {
        $this->write($this->encode($class,$method,$arguments));
        return $this->read();
    }

    private function getConnection()
    {
        if($this->connection)
        {
            return $this->connection;
        }else{
            $this->connection = $this->doGetConnection();
        }

        return $this->connection;
    }

}


//UserRpc
$client   = new RpcClient("tcp://192.168.33.10:20001",'Test','{1BA09530-F9E6-478D-9965-7EB31A59537E}');

//print_r($client->syncCall("WhiteList",'getTypes'));

//print_r($client->syncCall("Poster","posterClasses"));

function call()
{
    $client = new RpcClient($_POST['endpoint'],$_POST['id'],$_POST['secret']);

    if(isset($_POST['user_token']))
    {
      $client->setToken($_POST['user_token']);
    }

    $args = trim($_POST['arguments']);
    if($args)
    {
        $args = json_decode($args);
    }else{
        $args = [];
    }
    if($args){
        $response = $client->syncCall($_POST['class'],$_POST['method'],$args);
    }else{
        $response = $client->syncCall($_POST['class'],$_POST['method']);
    }

    $data  = [
        'bin_data' =>$client->getLatestBinData(),
        'response' => $response
    ];

    echo json_encode($data,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}

function displayForm()
{

    $parsedData = [];
    $parsed = 'false';
    if(isset($_GET['_data']))
    {
        $rows = json_decode($_GET['_data'],true);
        if(is_array($rows )){
            $assoc = [];
            foreach($rows as $row)
            {
                $assoc[$row['name']] = $row['value'];
            }
            $parsedData = $assoc;
            $parsed = 'true';
        }

    }

    $endpoint = isset($parsedData['endpoint'])? $parsedData['endpoint'] : "tcp://192.168.33.10:20001";
    $id = isset($parsedData['id'])? $parsedData['id'] : "Test";
    $secret = isset($parsedData['secret'])? $parsedData['secret'] : "{1BA09530-F9E6-478D-9965-7EB31A59537E}";
    $class = isset($parsedData['class'])? $parsedData['class'] : "User";
    $method = isset($parsedData['method'])? $parsedData['method'] : "getWhitelist";
    $arguments = isset($parsedData['arguments'])? $parsedData['arguments'] : "";
    $user_token = isset($parsedData['user_token'])? $parsedData['user_token'] : "";
    echo '<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>RPC Tester</title>
<script src="https://cdn.bootcdn.net/ajax/libs/jquery/3.5.1/jquery.js"></script>
<link href="https://cdn.bootcdn.net/ajax/libs/twitter-bootstrap/3.4.1/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.bootcdn.net/ajax/libs/twitter-bootstrap/3.4.1/js/bootstrap.min.js"></script>
<style>
#quicklinks ul {
    margin:0;
    padding:0;
}
#quicklinks li {
    list-style:none;
    float:left;
    padding:5px 5px 10px 5px;
}
</style>

</head>

<body>

<nav class="navbar navbar-default navbar-inverse">
  <div class="container-fluid">
  <div class="navbar-header">
   <div class="navbar-header">
      <a class="navbar-brand" href="#">
        <icon class="glyphicon glyphicon-send" />
  <a class="navbar-brand" href="#">RPC Tool</a>
      </a>
    </div>

  </div>

    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
      <ul class="nav navbar-nav">
        <li class="active"><a href="?">Default</a></li>
        <li ><a href="?_data=%5B%7B%22name%22:%22endpoint%22,%22value%22:%22tcp://192.168.33.20:20001%22%7D,%7B%22name%22:%22id%22,%22value%22:%22Test%22%7D,%7B%22name%22:%22user_token%22,%22value%22:%22%22%7D,%7B%22name%22:%22secret%22,%22value%22:%22%7B1BA09530-F9E6-478D-9965-7EB31A59537E%7D%22%7D,%7B%22name%22:%22class%22,%22value%22:%22Whitelist%22%7D,%7B%22name%22:%22method%22,%22value%22:%22getTypes%22%7D,%7B%22name%22:%22arguments%22,%22value%22:%22%22%7D%5D">WhiteList.getTypes</a></li>

        <li><a href="?_data=%5B%7B%22name%22:%22endpoint%22,%22value%22:%22tcp://192.168.33.20:20001%22%7D,%7B%22name%22:%22id%22,%22value%22:%22Test%22%7D,%7B%22name%22:%22user_token%22,%22value%22:%22%22%7D,%7B%22name%22:%22secret%22,%22value%22:%22%7B1BA09530-F9E6-478D-9965-7EB31A59537E%7D%22%7D,%7B%22name%22:%22class%22,%22value%22:%22User%22%7D,%7B%22name%22:%22method%22,%22value%22:%22getUserById%22%7D,%7B%22name%22:%22arguments%22,%22value%22:%22%5B%5C%221552551839103000016465%5C%22%5D%22%7D%5D">User.getUserById</a>
        </li>


    </ul>

        <p class="navbar-text navbar-right">@<a href="http://vnzmi.com" target="_blank" class="navbar-link">Vincent</a></p>
    </div>


</nav>

<div class="container-fluid">
<div class="row" >
<div class="col-sm-12" id="quicklinks">
    <ul>
      <li><div class="btn-group" role="group"><a href="" class="btn btn-default">WhiteList.getType</a><a  class="btn btn-default">x</a></div></li>
    </ul>
    </div>
</div>
  <div class="row">
  <div class="col-md-5">
  <form class="form-horizontal" id="form">
  <div class="form-group">
    <label class="col-sm-2 control-label">Endpoint</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="endpoint" placeholder="Endpoint" name="endpoint" value="'.$endpoint.'">
    </div>
  </div>

  <div class="form-group">
    <label class="col-sm-2 control-label">ID</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="id" placeholder="RPC_ID" name="id" value="'.$id.'">
    </div>
  </div>

  <div class="form-group">
    <label class="col-sm-2 control-label">USER_TOKEN</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="user_token" name="user_token" placeholder="X_USER_TOKEN" value="'.$user_token.'">
    </div>
  </div>

  <div class="form-group">
    <label class="col-sm-2 control-label">Secret</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="secret" placeholder="RPC_TOKEN" name="secret" value="'.$secret.'">
    </div>
  </div>
  <div class="form-group">
    <label for="inputPassword" class="col-sm-2 control-label">Class</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="class" name="class" value="'.$class.'">
    </div>
  </div>

  <div class="form-group">
    <label for="inputPassword" class="col-sm-2 control-label">Method</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="method" name="method" value="'.$method.'">
    </div>
  </div>

  <div class="form-group">
    <label for="inputPassword" class="col-sm-2 control-label">Arguments<br/>(Json)</label>
    <div class="col-sm-10">
      <textarea class="form-control" name="arguments" id="arguments" rows="12">'.$arguments.'</textarea>
    </div>
  </div>

  <button type="button" class="btn btn-primary btn-lg btn-block" id="callBtn">调用</button>
  <button  type="button" class="btn btn-default btn-lg btn-block" id="saveCase"> 保存 </a>

</form>
  </div>

   <div class="col-md-7"  >

   <div class="alert alert-danger alert-dismissible"  id="alert" role="alert">
  <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
  <strong>错误!</strong> <span id="alertText"></span>
    </div>

   <div>

  <ul class="nav nav-tabs" id="rpctab" role="tablist">
    <li role="presentation" class="active"><a href="#response" aria-controls="response" role="tab" data-toggle="tab">响应</a></li>
    <li role="presentation"><a href="#status" aria-controls="status" role="tab" data-toggle="tab">状态</a></li>
    <li role="presentation"><a href="#bin" aria-controls="bin" role="tab" data-toggle="tab">RPC请求</a></li>
    <li role="presentation"><a href="#raw" aria-controls="raw" role="tab" data-toggle="tab">原始输出</a></li>
  </ul>

  <!-- Tab panes -->
  <div class="tab-content">
    <div role="tabpanel" class="tab-pane active" id="response"><pre id="response_content"></pre></div>
    <div role="tabpanel" class="tab-pane" id="status"><pre id="status_content"></pre></div>
    <div role="tabpanel" class="tab-pane" id="bin"><pre id="bin_content"></pre></div>
    <div role="tabpanel" class="tab-pane" id="raw"><pre id="raw_content"></pre></div>
  </div>

</div>




 </div>

</div>


</body>
<script>

var _url = "http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'"
var parsed = '.$parsed.'


$("#callBtn").click(function(){
    data =  $("#form").serializeArray();

    hideNotice()

    $("#raw_content").html("")
    $("#bin_content").html("")
    $("#status_content").html("")
    $("#response_content").html("")


    collectCase();

    parsed = false;
    localStorage.setItem("data", JSON.stringify(data));
    //console.log(data)
    $.post("?action=call" , data,function(data,status) {
        console.log(status)
        if(status == "success")
        {
            try {
                parsed = JSON.parse(data)
                //console.log(parsed)
                $("#response_content").html(JSON.stringify(parsed.response,null,6))
                $("#bin_content").html(JSON.stringify(parsed.bin_data,null,6))
                $("#status_content").html(JSON.stringify(status))
                $("#raw_content").html(data)
                $("#rpctab a[href=\\"#response\\"]").tab("show")
            }catch(err) {
                showNotice("服务器返回了错误的数据")
                $("#rpctab a[href=\\"#raw\\"]").tab("show")
                console.log(err)
                $("#raw_content").html(err)
            }
        }else{
            showNotice("服务端请求失败")
            $("#rpctab a[href=\\"#raw\\"]").tab("show")
        }

    })
})

$("#saveCase").click(function(){

    saveCurrentCase()

})

$("#arguments").blur(function(){
  var text = $("#arguments").val()
  console.log(text)
  var jsondata = JSON.parse(text)
   $("#arguments").val(JSON.stringify(jsondata,null,6))
})

function hideNotice()
{
    $("#alert").hide()
}

function showNotice(msg)
{
    $("#alertText").html(msg)
    $("#alert").show()

}


function saveCurrentCase()
{
    quicklinkAdd(collectCase())
}

function collectCase()
{
    name = $("#class").val() + "."+$("#method").val()
    data =  $("#form").serializeArray();
    encoded = encodeURI(JSON.stringify(data))
    link = _url+"?_data="+encoded;
    return {
        name : name ,
        link:link
    }
}

function quicklinkRender()
{
    items = new Array()

    var data = localStorage.getItem("quick_links");
    if(data != null){
        items = JSON.parse(data);
    }

    var html = "<ul>"
    for(i = 0;i<items.length;i++)
    {
        item = items[i]
        html += "<li>"
        html += \'<div class="btn-group" role="group">\'
        html += \'<a href="\'+item.link+\'" class="btn btn-default btn-xs ">\'+item.name+\'</a><a  href="javascript:;"  onclick="quicklinkRemove(\'+i+\')" class="btn btn-danger btn-xs  btn-default">x</a></div></li>\'
    }
    $("#quicklinks").html(html)
}



function quicklinkRemove(index)
{
    var data = localStorage.getItem("quick_links");
    var newData = new Array()
    if(data != null){
        var items = JSON.parse(data);
        for(i = 0 ;i<items.length;i++)
        {
            if(i!=index)
            {
                newData.push(items[i])
            }
        }
    }
    localStorage.setItem("quick_links",JSON.stringify(newData))
    quicklinkRender()
}

function quicklinkAdd(newItem)
{
    var data = localStorage.getItem("quick_links");
    var newData = new Array()
    if(data != null){
        newData = JSON.parse(data);
    }
    newData.push(newItem)
    localStorage.setItem("quick_links",JSON.stringify(newData))
    quicklinkRender()
}

$(function(){
    hideNotice()
    if(parsed == false){
        var data = localStorage.getItem("data");
        if(data != null){
            var localData = JSON.parse(data);
            localData.forEach(function(item){
                //console.log(item)
                $("#"+ item.name).val(item.value);
            })

        }
    }

    quicklinkRender()
})
</script>

</html>';
}


$action = isset($_GET['action']) ? $_GET['action'] : "";

if($action == "") {
    displayForm();
}else{
    call();
}

