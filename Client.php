<?php

include "ddbank/DdbankAuthority.php";

class Client{
    private $config;

    public function __construct($config){
        $this->config = $config;
    }

    /**
     * 提交订单
     */
    public function order(){
        $config = [
            'gateway' => $this->config['orderApi'],
            'apiKey' => $this->config['apiKey'],
            'secret' => $this->config['secret'],
            'method' => 'POST'
        ];

        $ddbankAuthority = new DdbankAuthority($config);

        $params = [
            "stoneIds" => "5d82fa6631d5a71d1e37873a", //钻石ID(DDBANk)
            "brandName" => "品牌名称test",
            "storeName" => "店铺名称test",
            "storeAddr" => "店铺地址test",
	    "storeType" => "2",//直营(1)加盟(2)
            "customName" => "客户名称test",
            "brandOrder" => "订单号test",
            "deliverDate" => date('Y-m-d H:i:s'),
	    "contactName" => "某某某",
	    "contactPhone" => "13666666666",
        ];

        $sign = $ddbankAuthority->getSign($params);
        return $ddbankAuthority->curlPost(json_encode($params), $sign);
    }


    /**
     * 库存
     */
    public function inventory(){
        $config = [
            'gateway' => $this->config['inventoryApi'],
            'apiKey' => $this->config['apiKey'],
            'secret' => $this->config['secret'],
            'method' => 'GET'
        ];

        $ddbankAuthority = new DdbankAuthority($config);

        $params = [
            'pageNum' => 1,     //页数
            'pageSize' => 10,   //数量
            'customCode' => $this->config['customCode']
        ];

        $sign = $ddbankAuthority->getSign($params);
        return $ddbankAuthority->curlPost($params, $sign);
    }
}
