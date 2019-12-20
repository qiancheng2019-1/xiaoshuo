<?php


namespace App\V1\Admin\Model;


use Illuminate\Support\Facades\DB;

class ArticlesModel extends IndexModel
{
    public static function getCategoryList()
    {
        return DB::table('articles_category')
            ->select(['id', 'name', 'order', 'status'])
            ->orderBy('order')
            ->get();
    }

    public static function getCategory(int $id = 0, $columns = ['*'])
    {
        return DB::table('articles_category')
            ->select($columns)
            ->find($id);
    }

    public static function deleteCategory(array $ids = [])
    {
        return DB::table('articles_category')
            ->whereIn('id', $ids)
            ->delete();
    }

    public static function postCategory(array $data)
    {
        return self::updateOrInsert('articles_category', self::removeEmpty($data));
    }

    public static function updateCategory(int $id = 0, array $data)
    {
        return self::updateOrInsert('articles_category', self::removeEmpty($data), ['id' => $id]);
    }

    public static function getList(array $columns = ['*'], array $where = [], string $order = 'id', array $page_arr = [1, 1])
    {
        if (isset($where['keyword'])) {
            $keyword = $where['keyword'];
            unset($where['keyword']);
        } else $keyword = [];

        $sql = DB::table('articles')
            ->where($where)
            ->where($keyword)
            ->orderByDesc($order)
            ->simplePaginate($page_arr[1], $columns, 'page', $page_arr[0]);
        return $sql;
    }

    public static function get(int $id = 0, $columns = ['*'])
    {
        return DB::table('articles')
            ->leftJoin('articles_views', 'articles.id', '=', 'articles_views.article_id')
            ->select($columns)
            ->find($id);
    }

    public static function post(array $data = [])
    {
        $data = self::removeEmpty($data);
        $views_data['total_views'] = $data['total_views'] ?? null;
        $views_data['month_views'] = $data['month_views'] ?? null;
        $views_data['week_views'] = $data['week_views'] ?? null;

        unset($data['total_views'], $data['month_views'], $data['week_views']);
        $article = self::updateOrInsert('articles', self::removeEmpty($data));

        if (!$article['code']) self::updateViews($article['id'],$views_data);
        return $article;
    }

    public static function update(int $id, array $data)
    {
        $data = self::removeEmpty($data);

        $views_data['total_views'] = $data['total_views'] ?? null;
        $views_data['month_views'] = $data['month_views'] ?? null;
        $views_data['week_views'] = $data['week_views'] ?? null;
        self::updateViews($id,$views_data);

        unset($data['total_views'], $data['month_views'], $data['week_views']);
        return self::updateOrInsert('articles', $data, ['id' => $id]);
    }

    public static function updateViews(int $id=0,array $data = [])
    {
        return DB::table('articles_views')->updateOrInsert(['article_id' => $id], self::removeEmpty($data));
    }

    public static function delete(array $ids = [])
    {
        $sql = DB::table('articles')
            ->whereIn('id', $ids)
            ->delete();
        return $sql and DB::table('articles_views')->whereIn('article_id', $ids)->delete();
    }
}
