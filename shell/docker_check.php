#!/usr/bin/php
<?php
//检查Docker运行没 没运行手动启动它
$names = array(
'sentry_cron_1' , 'sentry_sentry_1','sentry_worker_1','sentry_postgres_1','sentry_redis_1'
);

date_default_timezone_set('Asia/Chongqing');

while(true){
	foreach($names as $name) {
		$output = [];
		exec('sudo docker ps -f name=' . $name,$output);
		if(strpos(implode('', $output),$name) === false){
			exec('sudo docker start '.$name);
			echo date('Y-m-d H:i:s ') .$name . " restarted\n";
		}else{
			echo date('Y-m-d H:i:s ') . $name . " runing\n";
		}
	}
	sleep(300);
}
