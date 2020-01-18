<?php


namespace App\Api\Basis;

use Illuminate\Support\Facades\Cache;
use QL\QueryList;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ReptileModel
{
    use BaseModel;

    private $rand_ip, $timeout, $user_agent, $referer, $reptile;

    function __construct()
    {
        sleep(1);//尝试规避被封ip

        $this->reptile = config('reptile');
        if (!Storage::disk('local')->exists('cate')){
            Storage::disk('local')->put('cate',json_encode($this->reptile['list_cate']));
        }

        $this->reptile['domain'] = $this->reptile['domain'][array_rand($this->reptile['domain'])];
        $this->rand_ip = mt_rand(13, 255) . '.' . mt_rand(13, 255) . '.' . mt_rand(13, 255) . '.' . mt_rand(13, 255);
        $this->timeout = 8;
        $this->user_agent = 'Mozilla/5.0 (compatible; Baiduspider/2.0; +http://www.baidu.com/search/spider.html)';
        $this->referer = 'https://www.baidu.com/s?wd=%E7%99%BE%E5%BA%A6&rsv_spt=1&rsv_iqid=0xe5a39f3b0003c303&issp=1&f=8&rsv_bp=0&rsv_idx=2&ie=utf-8&tn=baiduhome_pg&rsv_enter=1&rsv_sug3=6&rsv_sug1=4&rsv_sug7=100';
    }

    public function getList()
    {
        for ($i = 0; $i < 10; $i++) {
            $cate_k = array_rand($this->reptile['list_cate']);
            if (!Cache::get('reptile:' . $cate_k, FALSE)) {
                continue;
            }
        }
//        $category = DB::table('articles_category')->select(['id', 'name', 'page'])->where(['id' => $cate['my_cate']])->first();
//        $now_page = $category->page ?: 1;

        $cate_page = json_decode(Storage::disk('local')->get('cate'),TRUE)[$cate_k];
        $cate_page['page'] = $cate_page['page']??1;
        $url = str_replace('{page}', $cate_page['page'], $this->reptile['list_url']);
        $url = str_replace('{cate}', $cate_page['cate'], $url);

        $rules['url'] = [$this->reptile['list_selector'], 'href'];
        $rules['title'] = [$this->reptile['list_title_selector'], 'text'];
        $rules['thumb'] = [$this->reptile['list_thumb_selector'], 'src'];
        $rules['author'] = [$this->reptile['list_author_selector'], 'text'];

        $list = $this->getHtml($this->reptile['domain'] . $url, $rules);
    var_dump($list);die;
        if (!$list) {
            Cache::put('reptile:' . $cate_k, TRUE, 8);
            return FALSE;
        }

        foreach ($list as $item) {
            $data['url'] = str_replace($this->reptile['domain'], '', $item['url']);
            $data['title']  = $item['title'];
            $data['thumb']  = $item['thumb'];
            $data['author'] = str_replace('作者：', '', $item['author']);
            $data['category_id'] = $cate_page['my_cate'];
//            $data['category'] = $category->name;
            $data['status'] = 1;

            $data['created_at'] = date('Y-m-d H:i:s');

            DB::table('articles')->updateOrInsert(['url' => $item['url']], $data);
        }
        return Storage::disk('local')->put('cate',json_encode($cate_page));
    }

    public function getArticle(int $article_id, string $url)
    {
        $data['font_count'] = 0;
        if (strpos($url, '_') !== FALSE) {
//            $rules['title']    = [$this->reptile['view_title_selector'], 'text'];
            $rules['content'] = [$this->reptile['view_selector'], 'content'];
            $rules['full'] = [$this->reptile['view_full_selector'], 'content'];
            $rules['thumb'] = [$this->reptile['view_thumb_selector'], 'content'];
//            $rules['author']   = [$this->reptile['view_author_selector'], 'content'];
            $rules['category'] = [$this->reptile['view_cate_selector'], 'content'];
            $article = $this->getHtml($this->reptile['domain'] . $url, $rules);
            if (!$article) return FALSE;

            $article = $article[0];
            if (strpos($article['thumb'], $this->reptile['host']) !== FALSE) {
                $data['thumb'] = '';
            } else {
                $thumb = file_get_contents($article['thumb']) ?: '';
                $data['thumb'] = Storage::disk('public')->put('thumb/' . $article_id . substr($article['thumb'], -5), $thumb)
                    ? 'thumb/' . $article_id . substr($article['thumb'], -5) : '';
            }
            $data['is_full'] = $article['full'] === '完本' ? 1 : 0;
            $data['info'] = $article['content'];

            $reptile_id = substr($url, 5, -5);
            $data['url'] = $url = 'book/' . floor($reptile_id / 1000) . '/' . $reptile_id;
            DB::table('articles')->where(['id' => $article_id])->update($data);
        }

        $chapter = $this->getHtml($this->reptile['domain'] . $url, ['area_html' => [$this->reptile['chapter_area_selector'], 'html']]);
        if (!$chapter) return FALSE;

        //章节目录处理
        $chapter = $chapter[0];
        preg_match_all('/\[link\]|\[title\]|\[string\]/', $this->reptile['chapter_regx'], $matches);
        $link_key = $title_key = $string_key = 0;
        foreach ($matches[0] as $key => $item) {
            switch ($item) {
                case '[link]':
                    $link_key = $key + 1;
                    break;
                case '[title]':
                    $title_key = $key + 1;
                    break;
//                case '[string]':
//                    $string_key = $key + 1;
//                    break;
            }
        }
        //获取规则中关键key的顺序

        //正则分隔
        $pattern = str_replace(['[link]', '[title]', '[string]', '?', '/', '|', '+', '-', '.', '[', ']', 'XXXX', 'CCCC'], ['XXXX', 'XXXX', 'CCCC', '\\?', '\\/', '\\|', '\\+', '\\-', '\\.', '\\[', '\\]', '([\\w\\W]*?)', '(.*?)'], addslashes($this->reptile['chapter_regx']));
        preg_match_all('/' . $pattern . '/s', $chapter['area_html'], $matches);
        for ($i = 0; $i < count($matches[$link_key]); $i++) {
            //统计字数
            $title = explode('，共', $matches[$title_key][$i]);
            $data['font_count'] += (int)substr($title[1], 0, -1);

            $chapter_list[$i] = [
                'id'    => $i,
                'link'  => str_replace($this->reptile['domain'] . $url . '/', '', $matches[$link_key][$i]),
                'title' => $title[0],
            ];
        }

        if ($chapter_list ?? FALSE) {
            $Storage = Storage::disk('local');
            $storage_id = floor($article_id / 1000) . '/' . $article_id;

            //清除后台手动修改章节造成的数据冗余
            $chapter_list_old = $Storage->exists($storage_id . '/chapters') ? json_decode($Storage->get($storage_id . '/chapters'), TRUE) : [];
            foreach (array_diff(array_keys($chapter_list_old) ?? [], array_keys($chapter_list)) as $item) {
                Storage::disk('local')->delete($storage_id . '/' . $item['id']);
            }

            $Storage->put($storage_id . '/chapters', json_encode($chapter_list));

            $last = end($chapter_list);
            $data['last_chapter'] = $last['title'];
            $data['last_chapter_id'] = $last['id'];
            $data['updated_at'] = date('Y-m-d H:i:s');
        }

        DB::table('articles')->where(['id' => $article_id])->update($data);
        return $chapter_list ?? FALSE;
    }

    public function getChapter(object $article, array $chapter)
    {
        $Storage = Storage::disk('local');
        $rules['content'] = [$this->reptile['chapter_cont_selector'], 'html', '-script -a'];
        $content = $this->getHtml($this->reptile['domain'].$article->url . '/' . $chapter['link'], $rules, 'chapter_cont_pre_filter');

        if ($content = $content[0]['content'] ?? FALSE) {
            $storage_id = floor($article->id / 1000) . '/' . $article->id;
            $data['title'] = $chapter['title'];
            $data['content'] = $content;
            $Storage->put($storage_id . '/' . $chapter['id'], json_encode($data));
            return TRUE;
        }
        return FALSE;
    }

    private function getHtml(string $url, array $rules = [], string $pre_filter = '')
    {
        var_dump($url);
        try {
            $html_contents = $this->curlGetContents($url, TRUE);

            if (strtoupper($this->reptile['charset']) !== 'UTF-8')
                $html_contents = $this->changeCharset($html_contents);

            //过滤“文字水印”
            if ($pre_filter) {
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
            !($data = $query->getData()) and $data = $query->encoding('UTF-8', 'UTF-8')->removeHead()->getData();

            return $data->all();
        } catch (\ErrorException $e) {
            return [];
        }
    }

    //编码过滤
    private function changeCharset(string $html_contents)
    {
        $charset = mb_detect_encoding($html_contents, ["UTF-8", "GBK", "GB2312"]);
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
    private function curlGetContents(string $url, bool $un_header = FALSE)
    {
        $ch = curl_init();
        $if_https = substr($url, 0, 8) == 'https://' ? TRUE : FALSE;
        $header = [
//            'Proxy-Client-IP:' . $this->rand_ip,
//            'WL-Proxy-Client-IP:' . $this->rand_ip,
//            'X-Forwarded-For:' . $this->rand_ip,
//            'Referer:' . $this->referer,
            'Host:'.$this->reptile['host'],
        ];

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->user_agent);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if ($if_https) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
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
