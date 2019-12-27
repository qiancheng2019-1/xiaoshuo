<?php


namespace App\V1\App\Models;


use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ArticlesModel extends IndexModel {
    protected $table = 'articles';

    public static function getCategoryList()
    {
        return DB::table('articles_category')->select(['id', 'name', 'title', 'keyword', 'desc'])->where(['status' => 1])->orderBy('order')->get();
    }

    public static function getList(array $columns = ['*'], array $where = [], string $order = 'id', array $page_arr = [1, 1])
    {
        $cache_key = hash('sha512', json_encode([$columns, $where, $order, $page_arr]));
        $cache = Cache::get($cache_key);
        if ($cache) return $cache;

        if (isset($where['keyword'])) {
            $keyword = $where['keyword']['function'];
            unset($where['keyword']);
        } else $keyword = [];

        foreach ($columns as $key => $item) is_string($key) ? $select[] = DB::raw($item . ' as ' . $key) : $select[] = $item;

        $sql = DB::table('articles')->leftJoin('articles_views', 'articles.id', '=', 'articles_views.article_id')->where($where)->where($keyword)->orderByDesc('total_views')->orderByDesc($order)->paginate($page_arr[1], $select, 'page', $page_arr[0]);

        Cache::put($cache_key, $sql, config('env.cache_select_time'));
        return $sql;
    }

    public static function get(int $id = 0, $columns = ['*'])
    {
        return DB::table('articles')->leftJoin('articles_views', 'articles.id', '=', 'articles_views.article_id')->select($columns)->find($id);
    }

    public static function updateViews(int $article_id = 0, int $amount = 0)
    {
        if (!$amount) {
            //点击区间
            $views_between = explode('-', config('env.views_between'));
            $amount = mt_rand($views_between[0], $views_between[1]);
        }

        $views = DB::table('articles_views')->where(['article_id' => $article_id])->first();
        if (!$views) return (bool)DB::table('articles_views')->insert(['article_id' => $article_id, 'week_views' => 1, 'month_views' => 1, 'total_views' => 1, 'week' => date('W'), 'month' => date('m')]);

        if ($views->week != date('W')) {
            $views->week = date('W');
            $views->week_views = $amount;
        } else $views->week_views += $amount;

        if ($views->month != date('m')) {
            $views->month = date('m');
            $views->month_views = $amount;
        } else $views->month_views += $amount;

        $views->total_views += $amount;

        return (bool)DB::table('articles_views')->updateOrInsert(['article_id' => $article_id], (array)$views);
    }
}
