<?php


namespace App\Api\Models;

use App\Api\Basis\ReptileModel;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class Articles extends IndexModel
{
//    protected $table = 'articles_test';

    public function getChapter()
    {
        return $this->hasOne('App\Api\Models\ArticlesChapter', 'title_id');
    }

    public function getCollect()
    {
        return $this->hasOne('App\Api\Models\UsersCollect', 'article_id');
    }

    public function getViews()
    {
        return $this->hasOne('App\Api\Models\ArticlesViews', 'article_id');
    }

    public static function getCategoryList()
    {
        return DB::table('articles_category')->select(['id', 'name'])->orderBy('order')->get();
    }

    public static function getList(array $columns = ['*'], array $where = [], string $order = 'id', array $page_arr = [1, 1])
    {
//        $cache_key = md5(json_encode([$columns, $where, $order, $page_arr]));
//        $cache = Cache::get($cache_key);
//        if ($cache) return $cache;

        if (isset($where['keyword'])) {
            $keyword = $where['keyword']['function'];
            unset($where['keyword']);
        } else $keyword = [];

        foreach ($columns as $key => $item) is_string($key) ? $select[] = DB::raw($item . ' as ' . $key) : $select[] = $item;

        $sql = self::query()->leftJoin('articles_views', 'id', '=', 'article_id')->where($where)->where($keyword)->orderByDesc('total_views')->orderByDesc($order)
//            ->dd();
            ->paginate($page_arr[1], $select, 'page', $page_arr[0]);

//        Cache::put($cache_key, $sql, config('env.cache_select_time'));
        return $sql;
    }

    public static function updateViews(int $article_id = 0, int $amount = 0)
    {
        if (!$amount) {
            //点击区间
            $views_between = explode('-', config('env.views_between'));
            $amount = mt_rand($views_between[0], $views_between[1]);
        }

        $views = self::query()->find($article_id, ['id'])->getViews;
        if (!$views) return (bool)ArticlesViews::query()->insert(['article_id' => $article_id, 'week_views' => 1, 'month_views' => 1, 'total_views' => 1, 'week' => date('W'), 'month' => date('m')]);

        if ($views->week != date('W')) {
            $views->week = date('W');
            $views->week_views = $amount;
        } else $views->week_views += $amount;

        if ($views->month != date('m')) {
            $views->month = date('m');
            $views->month_views = $amount;
        } else $views->month_views += $amount;

        $views->total_views += $amount;

        return (bool)$views->save();
    }

    public static function getChapterForId(int $article_id, int $chapter_id)
    {
        //爬虫库源
        $result = new \stdClass();
        $storage_id = floor($article_id / 1000) . '/' . $article_id;
        $chapter_list = Storage::disk('local')->exists($storage_id . '/chapters') ? json_decode(Storage::disk('local')->get($storage_id . '/chapters'), true) : [];
        $result->chapter_name = $chapter_list[$chapter_id]['title'] ?? '';
        $result->chapter_id = $chapter_id;

        //外置库源
//        $last_view = ArticlesChapter::query()->where(['title_id' => $article_id,'chapter_id'=>(int)$chapter_id])->first()
//            ?: ArticlesChapter::query()->where(['title_id' => $article_id])->orderBy('chapter_id')->first();
//        $result->last_view_id = $last_view->chapter_id;
//        $result->last_view = $last_view->chapter_name;

        return $result;
    }

    public static function getChapterList(int $article_id, bool $order_desc, int $page, int $limit)
    {
        //爬虫库源
        $storage_id = floor($article_id / 1000) . '/' . $article_id;
        $Storage = Storage::disk('local');
        $chapter_list = $Storage->exists($storage_id . '/chapters') ? json_decode($Storage->get($storage_id . '/chapters'), true) : [];
        $result = new Paginator($chapter_list ?? [], $limit, $page);

        //外置库源
//        $result = ArticlesChapter::query()->where(['title_id' => $article_id])
//            ->distinct('chapter_id')
//            ->orderBy('id', $order_desc ? 'desc' : 'asc')
//            ->paginate($limit, ['chapter_id as id', 'chapter_name as title'], 'page', $page);

        return $result;
    }

    public static function getChapterDetail(object $article, int $chapter_id)
    {
        //爬虫库源
        $storage_id = floor($article->id / 1000) . '/' . $article->id;
        $Storage = Storage::disk('local');
        $chapter_list = $Storage->exists($storage_id . '/chapters') ? json_decode($Storage->get($storage_id . '/chapters'), true) : [];
        if (!isset($chapter_list[$chapter_id])) return false;
        if (!$Storage->exists($storage_id . '/' . $chapter_id)) {
            $reptileModel = new ReptileModel();
            if (!$reptileModel->getChapter($article, $chapter_list[$chapter_id]))
                return false;
        }
        $chapter = json_decode($Storage->get($storage_id . '/' . $chapter_id));
        $chapter->prev_id = $chapter_id ? $chapter_id - 1 : 0;
        $chapter->next_id = isset($chapter_list[$chapter_id + 1]) ? ($chapter_id + 1) : $chapter_id;

        //外置库源
//        $file_type = '.txt';
//        $chapter = ArticlesChapter::query()->where(['chapter_id' => $chapter_id, 'title_id' => $article->id])->first(['chapter_name as title']);
//        if (!$chapter) return false;
//
//        $Storage = Storage::disk('sftp');
//        $dir_id = $article->category_id . '/' . $article->pinyin;
//
//        if (!$Storage->exists($dir_id . '/' . $chapter_id . $file_type)) return false;
//        $chapter->content = $Storage->get($dir_id . '/' . $chapter_id . $file_type);
//        $chapter->prev_id = ArticlesChapter::query()->where('chapter_id', '<', $chapter_id)->where(['title_id' => $chapter_id])->orderByDesc('chapter_id')->first(['chapter_id'])->chapter_id ?? $chapter_id;
//        $chapter->next_id = ArticlesChapter::query()->where('chapter_id', '>', $chapter_id)->where(['title_id' => $chapter_id])->orderBy('chapter_id')->first(['chapter_id'])->chapter_id ?? $chapter_id;


        Cache::put('art:'.$article->id.'-'.$chapter_id,$chapter,config('env.cache_select_time'));
        return $chapter;
    }
}
