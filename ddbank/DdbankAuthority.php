<?php

/**
 * DDbank Api 接口鉴权
 * Class DdbankAuthority
 */
class DdbankAuthority {

    private $charset = "UTF-8";
    private $apiKey;        //api key
    private $secret;        //密码
    private $gateway;       //api网关
    private $nonce;         //随机字符串
    private $timestamp;     //签名时间
    private $method;        //请求类型

    /**
     * 初始化方法
     * @param $config
     */
    public function __construct($config){
        $this->apiKey = $config['apiKey'];
        $this->secret = $config['secret'];
        $this->gateway = $config['gateway'];
        $this->method = $config['method'];
        $this->nonce = $this->generateNonce(18);
        $this->timestamp = (int)(microtime(true)).'000';
    }

    private function generateNonce($length) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }

    /**
     * 获取签名方法
     * @param $params
     * @return string
     */
    public function getSign($params){
        $signParams = $this->getSignContent($params);
        if($this->method == "GET"){
            return $this->sign($this->method.$this->gateway."?". $signParams .$this->nonce.$this->timestamp);
        }

        return $this->sign($this->method.$this->gateway.$this->nonce.$this->timestamp.$signParams);

    }

    /**
     * 签名
     * @param $signBody
     * @return string
     */
    private function sign($signBody){
        $sign = hash_hmac('sha1', base64_encode($signBody), sha1($this->secret), true);
        return base64_encode($sign);
    }

    /**
     * 获取签名内容
     * @param $params
     * @return string
     */
    private function getSignContent($params) {
        ksort($params);
        $signParams = "";
        $i = 0;
        foreach ($params as $k => $v) {
            if (false === $this->checkEmpty($v) && "@" != substr($v, 0, 1)) {
                $v = $this->changeCharset($v, $this->charset);
                if ($i == 0) {
                    $signParams .= "$k" . "=" . urlencode($v);
                } else {
                    $signParams .= "&" . "$k" . "=" . urlencode($v);
                }
                $i++;
            }
        }
        unset ($k, $v);
        return $signParams;
    }

    /**
     * 转换字符集编码
     * @param $data
     * @param $targetCharset
     * @return string
     */
    private function changeCharset($data, $targetCharset) {
        if (!empty($data)) {
            $fileType = $this->charset;
            if (strcasecmp($fileType, $targetCharset) != 0) {
                $data = mb_convert_encoding($data, $targetCharset, $fileType);
            }
        }
        return $data;
    }

    /**
     * 校验$value是否非空
     * if not set ,return true;
     * if is null , return true;
     **/
    private function checkEmpty($value) {
        if (!isset($value))
            return true;
        if ($value === null)
            return true;
        if (trim($value) === "")
            return true;
        return false;
    }

    /**
     * 获取随机字符串
     * @return string
     */
    public function getNonce(){
        return $this->nonce;
    }

    /**
     * 获取签名时间
     * @return
     */
    public function getTimestamp(){
        return $this->timestamp;
    }

    public function curlPost($postData = '', $sign, $options = array())
    {
        $head = [
            'X-CA-ACCESSKEY:' . $this->apiKey,
            'X-CA-TIMESTAMP:' . (string)$this->getTimestamp(),
            'X-CA-NONCE:' . $this->getNonce(),
            'X-CA-SIGNATURE:' . $sign,
            'content-type: application/json;charset=' . $this->charset
        ];

        $url = $this->gateway;
        if($this->method == "GET"){
            $url = $this->gateway . "?" . $this->getSignContent($postData);
        }

        if (is_array($postData)) {
            $postData = http_build_query($postData);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        if($this->method == "POST"){
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        }

        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $head);
        if (!empty($options)) {
            curl_setopt_array($ch, $options);
        }

        //https请求 不验证证书和host
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
}
