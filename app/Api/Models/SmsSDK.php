<?php

namespace App\Api\Models;

class SmsSDK
{
    static function mobileCode(int $code,string $mobile)
    {
        $str = config('env.sms_code_template');
        $str = str_replace('{sms_code}',$code,$str);
        $str = str_replace('{sms_cache}',config('env.sms_cache'),$str);
        return self::send($str,$mobile);
    }

    static function send($msg , $mobile)
    {
        $title = '【'.config('env.sms_title').'】';
        $api_id = config('env.sms_api_id');
        $api_pwd = config('env.sms_api_pwd');

        $request = [
            'account' => $api_id,
            //api账号
            'password' => $api_pwd,
            //api密码
            'msg' => $title . $msg,
            //内容
            'mobile' => '86'.$mobile,
            //手机，批量“,”分割，86适配国际短信接口用，国内短信可省略
        ];

        $request = json_encode($request);
        $opts = [
            CURLOPT_URL => config('env.sms_api_url'),
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => $request,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_SSL_VERIFYPEER => FALSE,
            CURLOPT_SSL_VERIFYHOST => FALSE,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json; charset=utf-8',
                'Content-Length: ' . strlen($request),
            ],
        ];

        $ch = curl_init();
        curl_setopt_array($ch, $opts);
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }
}
