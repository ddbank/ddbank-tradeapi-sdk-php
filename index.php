<?php

include "Client.php";
//读取配置
$config = require "config.php";

$client = new Client($config);

//提交订单
$response = $client->order();
var_dump($response);

//查询库存
$response = $client->inventory();
print_r($response);