<?php
namespace think;




class QQLogin
{
    protected $appId = '101519646';
    protected $appKey = '33318dcd0dc5fdffb01edf28c82aa0ed';
    protected $url = 'http://huiduoduo.com/index/index/index';
    protected $state;


    public $openId;




    const GET_AUTH_CODE_URL = "https://graph.qq.com/oauth2.0/authorize";
    const GET_ACCESS_TOKEN_URL = "https://graph.qq.com/oauth2.0/token";
    const GET_OPENID_URL = "https://graph.qq.com/oauth2.0/me";
    const GET_USER_INFO_URL = "https://graph.qq.com/user/get_user_info";




    public function __construct($appId = '',$appKey = '',$callBack = '')
    {
        if (!empty($appId) && !empty($appKey)) {
            $this->appId = $appId;
            $this->appKey = $appKey;
        }
        if (!empty($callBack)) {
            $this->url = $callBack;
        }
        $this->state = $state = md5(uniqid(rand(), TRUE));
    }




    public function getCode () {
        //配置相关数组
        $codeData = [
            'response_type' => 'code',
            'client_id'     => $this->appId,
            'redirect_uri'  => $this->url,
            'state'         => $this->state,
        ];
        $url = self::GET_AUTH_CODE_URL . '?' . http_build_query($codeData);
      
        
       return $url;
    }




    protected function getAccessToken ($code) {
        //配置相关数组
        $tokenData = [
            'grant_type'    => 'authorization_code',
            'client_id'     => $this->appId,
            'client_secret' => $this->appKey,
            'code'          => $code,
            'redirect_uri'  => $this->url,
        ];




        $url = self::GET_ACCESS_TOKEN_URL . '?' . http_build_query($tokenData);
        $tokenInfo = $this->urlContent($url);
        parse_str($tokenInfo,$tokenInfo);
        //$this->token = $tokenInfo['access_token'];
        //file_put_contents('d:/access.log',json_encode($tokenInfo));
        return $tokenInfo['access_token'];
    }




    protected function getOpenId ($token) {
        $openIdData = [
            'access_token' => $token,
        ];
        $url = self::GET_OPENID_URL . '?' . http_build_query($openIdData);
        $openIdInfo = $this->urlContent($url);
        //解析的到的结果
        if(strpos($openIdInfo, "callback") !== false){
            $lpos = strpos($openIdInfo, "(");
            $rpos = strrpos($openIdInfo, ")");
            $openIdInfo = substr($openIdInfo, $lpos + 1, $rpos - $lpos -1);
        }
        $openIdInfo = json_decode($openIdInfo,true);
        $this->openId = $openIdInfo['openid'];
        return $openIdInfo['openid'];
    }




    public function getUserInfo ($code) {
        $token  = $this->getAccessToken($code);
        $openId = $this->getOpenId($token);
        $userData = [
            'access_token' => $token,
            'oauth_consumer_key' => $this->appId,
            'openid'  => $openId,
        ];




        $url = self::GET_USER_INFO_URL . '?' . http_build_query($userData);




        $userInfo = $this->urlContent($url);
        return json_decode($userInfo,true);
    }




    /**
     * 获取http请求的内容.GET/POST都可以
     * @param  string $url     url地址
     * @param  array  $data    需要POST提交的内容
     * @param  array  $header  HEADER内容
     * @return array  $result  json解析后的数组
     */
    public function urlContent($url, $data = null, $header = null) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        //如果$data存在,使用post传值
        if (!empty($data)) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        //如果$header存在 发送header信息
        if (!empty($header)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER,$header);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
}