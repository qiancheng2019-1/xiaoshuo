<?php


namespace App\V1\Basis;

use QL\QueryList;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ReptileModel {
    use BaseModel;

    private $rand_ip,$timeout,$user_agent,$referer,$reptile;

    function __construct() {
        $this->reptile = config('reptile');

        $this->rand_ip = mt_rand(13, 255) . '.' . mt_rand(13, 255) . '.' . mt_rand(13, 255) . '.' . mt_rand(13, 255);
        $this->timeout = 8;
        $this->user_agent = 'Mozilla/5.0 (compatible; Baiduspider/2.0; +http://www.baidu.com/search/spider.html)';
        $this->referer = 'https://www.baidu.com/s?wd=%E7%99%BE%E5%BA%A6&rsv_spt=1&rsv_iqid=0xe5a39f3b0003c303&issp=1&f=8&rsv_bp=0&rsv_idx=2&ie=utf-8&tn=baiduhome_pg&rsv_enter=1&rsv_sug3=6&rsv_sug1=4&rsv_sug7=100';
    }

    public function getList()
    {
        $cate = $this->reptile['list_cate'][array_rand($this->reptile['list_cate'])];
        $category = DB::table('articles_category')->select(['id','name','page'])->where(['id'=>$cate['ocate']])->first();

        $now_page = $category->page?:1;
        $url = str_replace('{page}',$now_page,$this->reptile['list_url']);
        $url = str_replace('{cate}',$cate['cate'],$url);

        $rules['title'] = [$this->reptile['list_title_selector'],'text'];
        $rules['url'] = [$this->reptile['list_selector'],'href'];
        $rules['author'] = [$this->reptile['list_author_selector'],'text'];

        $list = $this->getHtml($url,$rules);
        if (!$list) return false;

        foreach ($list as $item){
            $data['title'] = $item['title'];
            $data['url'] = $this->reptile['domain'].$item['url'];
            $data['author'] = $item['author'];
            $data['category_id'] = $category->id;
            $data['category'] = $category->name;
            $data['pid'] = 1;
            $data['status'] = 1;

            DB::table('articles')->updateOrInsert(['url'=>$item['url']],$data);
        }
        return DB::table('articles_category')->where(['id'=>$category->id])->increment('page');
    }

    public function getArticle(int $article_id,string $url){
        $rules['title'] = [$this->reptile['view_title_selector'],'text'];
        $rules['content'] = [$this->reptile['view_selector'],'content'];
        $rules['thumb'] = [$this->reptile['view_thumb_selector'],'content'];
        $rules['author'] = [$this->reptile['view_author_selector'],'content'];
        $rules['category'] = [$this->reptile['view_cate_selector'],'content'];
        $rules['area_html'] = [$this->reptile['chapter_area_selector'],'html'];

        $article = $this->getHtml($url,$rules);
        if (!$article) return false;

        $article = $article[0];
        $thumb = file_get_contents($article['thumb'])?:'';

        $data['thumb'] = Storage::disk('public')->put('thumb/'.$article_id.substr($article['thumb'], -5),$thumb) ? 'thumb/'.$article_id.substr($article['thumb'], -5):'';
        $data['info'] = $article['content'];

        //章节目录处理
        preg_match_all('/link|title|string/', $this->reptile['chapter_regx'], $matches);
        $link_key = $title_key = 0;
        foreach ($matches[0] as $key => $item) {
            if ($item == 'link') {
                $link_key = $key + 1;
            } elseif ($item == 'title') {
                $title_key = $key + 1;
            }
        }
        //获取规则中关键key的顺序

        //正则分隔
        $pattern = str_replace(['[link]', '[title]', '[string]', '?', '/', '|', '+', '-', '.', '[', ']', 'XXXX', 'CCCC'], ['XXXX', 'XXXX', 'CCCC', '\\?', '\\/', '\\|', '\\+', '\\-', '\\.', '\\[', '\\]', '([\\w\\W]*?)', '(.*?)'], addslashes($this->reptile['chapter_regx']));
        preg_match_all('/' . $pattern . '/s', $article['area_html'], $matches);
        for ($i = 0; $i < count($matches[$link_key]); $i++) {
            $chapter_list[$i] = [
                'id'=>$i,
                'link' => $matches[$link_key][$i],
                'title' => $matches[$title_key][$i]];
        }

        if ($chapter_list??false){
            $Storage = Storage::disk('local');
            $storage_id = floor($article_id / 1000) . '/' . $article_id;

            //清除后台手动修改章节造成的数据冗余
            $chapter_list_old = $Storage->exists($storage_id . '/chapters') ? json_decode($Storage->get($storage_id . '/chapters'), true) : [];
            foreach (array_diff(array_keys($chapter_list_old)??[],array_keys($chapter_list)) as $item){
                Storage::disk('local')->delete($storage_id.'/'.$item['id']);
            }

            $Storage->put($storage_id . '/chapters',json_encode($chapter_list));

            $last = end($chapter_list);
            $data['last_chapter'] = $last['title'];
            $data['last_chapter_id'] = $last['id'];
            $data['updated_at'] = date('Y-m-d H:i:s');
        }

        DB::table('articles')->where(['id'=>$article_id])->update($data);
        return $chapter_list??false;
    }

    public function getChapter(object $article,array $chapter){
        $Storage = Storage::disk('local');
        $rules['content'] = [$this->reptile['chapter_cont_selector'],'html','-script -a'];
        $content = $this->getHtml($article->url.'/'.$chapter['link'],$rules,'chapter_cont_pre_filter');

        if ($content = $content[0]['content'] ?? false){
            $storage_id = floor($article->id / 1000) . '/' . $article->id;
            $data['title'] = $chapter['title'];
            $data['content'] =$content;
            $Storage->put($storage_id.'/'.$chapter['id'],json_encode($data));
            return true;
        }
        return false;
    }

    private function getHtml(string $url,array $rules = [],string $pre_filter = ''){
        try {
            $html_contents = $this->curlGetContents($url,true);

            if (strtoupper($this->reptile['charset']) !== 'UTF-8')
                $html_contents = $this->changeCharset($html_contents);

            if ($pre_filter){
                $pre_filter = explode('[line]', $this->reptile[$pre_filter]);
                foreach ($pre_filter as $item) {
                    preg_match('#^\\{filter\\s+replace\\s*=\\s*\'([^\']*)\'\\s*\\}(.*)\\{/filter\\}#', $item, $matches);
                    if (isset($matches[2]) && !empty($matches[2])) {
                        $matches[2] = str_replace('~', '\\~', $matches[2]);
                        $matches[2] = str_replace('"', '\\"', $matches[2]);
                        $html_contents = preg_replace('~' . $matches[2] . '~iUs', $matches[1], $html_contents);
                    } else {
                        $html_contents = str_replace($item, '', $html_contents);
                    }
                }
            }

            $query = QueryList::setHtml($html_contents)->rules($rules)->query();
            !($data = $query->getData()) and $data = $query->encoding('UTF-8','UTF-8')->removeHead()->getData();

            return $data->all();

        }catch (\ErrorException $e){
            return [];
        }
    }

    //编码过滤
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

    //curl伪装访问
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
