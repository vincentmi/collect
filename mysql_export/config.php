<?php

define('DB_HOST','192.168.33.10');
define('DB_PORT',3306);
define('DB_USER','root');
define('DB_PASS','root');
define('DB_NAME','tcc');

include 'Exporter.php';

$e = new Exporter('192.168.33.10','root','root',['db'=>'tcc']);
$e->chunk = 5000;
$e->execute('select * from gbook ','a.csv');