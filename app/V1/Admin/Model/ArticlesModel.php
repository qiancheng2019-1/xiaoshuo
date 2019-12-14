<?php


namespace App\V1\Admin\Model;


use Illuminate\Support\Facades\DB;

class ArticlesModel extends BaseModel
{
    public static function getCategoryList()
    {
        return DB::table('articles_category')
            ->select(['id', 'name', 'order', 'status'])
            ->orderBy('order')
            ->get();
    }

    public static function getCategoryDetail(int $id = 0, $columns = ['*'])
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
        return DB::table('articles_category')->insertGetId(self::removeEmpty($data));
    }

    public static function updateCategory(int $id = 0, array $data)
    {
        return DB::table('articles_category')->where(['id' => $id])->update(self::removeEmpty($data));
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
            ->paginate($page_arr[1], $columns, 'page', $page_arr[0]);
        return self::sortPageObject($sql);
    }

    public static function get(int $id = 0, $columns = ['*'])
    {
        return DB::table('articles')
            ->leftJoin('articles_views', 'articles.id', '=', 'articles_views.articles_id')
            ->select($columns)
            ->find($id);
    }

    public static function post(array $data = [])
    {
        $data['total_views'] = $data['month_views'] = $data['week_views'] = null;
        return DB::table('articles')->insertGetId(self::removeEmpty($data));
    }

    public static function update(int $id = 0, array $data)
    {
        $data = self::removeEmpty($data);
        $views_data['total_views'] = $data['total_views'];
        $views_data['month_views'] = $data['month_views'];
        $views_data['week_views'] = $data['week_views'];
        self::updateOrInsert('articles_views', $views_data, ['articles_id' => $id]);

        unset($data['total_views'], $data['month_views'], $data['week_views']);
        return DB::table('articles')->where(['id' => $id])->update($data);
    }

    public static function delete(array $ids = [])
    {
        $sql = DB::table('articles')
            ->whereIn('id', $ids)
            ->delete();
        return $sql and DB::table('articles_views')->whereIn('articles_id', $ids)->delete();
    }
}
