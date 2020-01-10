<?php

namespace App\V1\App\Models;

class SmsSDK
{
    static function mobileCode(array $code,string $mobile)
    {
        $str = '您好，您的验证码为：'.$code[0].'，请在'.$code[1].'分钟之内填写，请不要把验证码泄露给别人，以防信息外泄';
        return self::send($str,$mobile);
    }

    static function send($msg , $mobile)
    {
        $title = '【Hulk】';
        $api_id = 'I4423247';
        $api_pwd = 'IWqOdQXRkBc60f';

        $request = [
            'account' => $api_id,
            //api账号
            'password' => $api_pwd,
            //api密码
            'msg' => $title . $msg,
            //内容
            'mobile' => '86'.$mobile,
            //手机，批量“,”分割
        ];

        $request = json_encode($request);
        $opts = [
            CURLOPT_URL => 'http://intapi.253.com/send/json',
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
