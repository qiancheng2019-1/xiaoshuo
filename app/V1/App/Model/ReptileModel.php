<?php


namespace App\V1\App\Model;


use QL\QueryList;

class ReptileModel {

    private $rand_ip,$timeout,$user_agent,$referer,$reptile;

    function __construct() {
        $this->reptile = config('reptile');

        $this->rand_ip = mt_rand(13, 255) . '.' . mt_rand(13, 255) . '.' . mt_rand(13, 255) . '.' . mt_rand(13, 255);
        $this->timeout = 8;
        $this->user_agent = 'Mozilla/5.0 (compatible; Baiduspider/2.0; +http://www.baidu.com/search/spider.html)';
        $this->referer = 'https://www.baidu.com/s?wd=%E7%99%BE%E5%BA%A6&rsv_spt=1&rsv_iqid=0xe5a39f3b0003c303&issp=1&f=8&rsv_bp=0&rsv_idx=2&ie=utf-8&tn=baiduhome_pg&rsv_enter=1&rsv_sug3=6&rsv_sug1=4&rsv_sug7=100';
    }

    public static function list()
    {

    }

    public function getHtml(string $url,$rules_arr = []){
//        $html_contents = $this->curlGetContents($url,true);
//
//        //编码过滤
//        if (strtoupper($reptile['charset']) !== 'UTF-8')
//            $html_contents = $this->changeCharset($html_contents);

        foreach ($rules_arr as $key => $item){
            $rules[$key] = $this->explodeRules($item);
        }

        $query = QueryList::setHtml(file_get_contents('test.txt'))->rules($rules)->query();
        !($data = $query->getData()) and $data = $query->encoding('UTF-8','UTF-8')->removeHead()->getData();

        echo json_encode($data->all());
        die;
    }

    private function explodeRules(string $key){
        if ($this->reptile[$key]){
            $arr = explode('|', $this->reptile[$key]);
            return [$arr[0], $arr[1]];
        }
        else return null;
    }

    private function changeCharset(string $html_contents){
        $charset = mb_detect_encoding($html_contents, array("UTF-8", "GBK", "GB2312"));
        $charset = strtolower($charset);
        if ("cp936" == $charset) {
            $charset = "GBK";
        }
        if ("utf-8" != $charset) {
            $html_contents = iconv($charset, "UTF-8//IGNORE", $html_contents);
        }
        $html_contents = preg_replace('/<meta([^<>]*)charset=[^\\w]?([-\\w]+)([^<>]*)>/', '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />', $html_contents);
        $html_contents = str_replace([
            'gbk',
            'gb2312',
            'GBK',
            'GB2312'], 'utf-8', $html_contents);
        return $html_contents;
    }

    private function curlGetContents($url,bool $un_header = false)
    {
        $ch = curl_init();
        $if_https = substr($url, 0, 8) == 'https://' ? true : false;
        $header = [
            'CLIENT-IP:' . $this->rand_ip,
            'X-FORWARDED-FOR:' . $this->rand_ip,];

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, $header);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->user_agent);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if ($if_https) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }

        $contents = curl_exec($ch);
        //删除头部
        if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == '200' && $un_header) {
            $response_header = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $contents = substr($contents, $response_header);
        }

        //关闭流
        curl_close($ch);

        //验证gz压缩
        $gz_code = @gzdecode($contents);
        if ($gz_code && strlen($gz_code) > strlen($contents))
            $contents = $gz_code;


        return $contents;
    }
}
