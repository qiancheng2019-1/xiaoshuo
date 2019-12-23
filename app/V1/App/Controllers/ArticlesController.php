<?php


namespace App\V1\App\Controllers;


use App\V1\Basis\ReptileModel;
use App\V1\App\Model\ArticlesModel;
use Dingo\Api\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class ArticlesController extends IndexController {
    /**
     * @OA\Schema(
     *     schema="TypeModel",
     *     @OA\Property(
     *         property="push",
     *         description="推荐",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="newsInsert",
     *         description="最新入库",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="newsUpdate",
     *         description="最近更新",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="rank",
     *         description="总点击#最热",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="mothor",
     *         description="月点击",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="week",
     *         description="周点击",
     *         type="string"
     *     ),
     * )
     */
    private $type_all = ['push', 'newsInsert', 'newsUpdate', 'rank', 'month', 'week'];
    protected $articles_where_model = [
        'keyword' => ['title', 'author'], 'status' => [], 'time' => false,];

    private function getTypeList(string $type, array $columns = [], array $where = [], int $page = 1, int $limit = 10)
    {
        $where['status'] = 1;
        switch ($type) {
            case 'push':
                return ArticlesModel::getList($columns, $where, 'push', [mt_rand(1,9), $limit]);
                break;
            case 'newsInsert':
                return ArticlesModel::getList($columns, $where, 'created_at', [$page, $limit]);
                break;
            case 'newsUpdate':
                return ArticlesModel::getList($columns, $where, 'updated_at', [$page, $limit]);
                break;
            case 'rank':
                return ArticlesModel::getList($columns, $where, 'total_views', [$page, $limit]);
                break;
            case 'month':
                return ArticlesModel::getList($columns, $where, 'month_views', [$page, $limit]);
                break;
            case 'week':
                return ArticlesModel::getList($columns, $where, 'week_views', [$page, $limit]);
                break;
            default:
                return $this->apiReturn($type . '资源不存在', 404, 1);
                break;
        }
    }

    /**
     * @OA\Get(
     *     path="/articles",
     *     tags={"Articles"},
     *     summary="获取多个类别下，书本列表",
     *     @OA\Parameter(
     *       name="limit",
     *       in="query",
     *       description="每页个数",
     *       @OA\Schema(
     *          type="integer",
     *          default="10",
     *       )
     *     ),
     *     @OA\Parameter(
     *       name="keyword",
     *       in="query",
     *       description="模糊搜索关键字",
     *       @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *       name="type",
     *       in="query",
     *       description="特殊类型",
     *       @OA\Schema(type="string",default="push-rank")
     *     ),
     *     @OA\Parameter(
     *       name="category",
     *       in="query",
     *       description="一级分类",
     *       @OA\Schema(type="string",default="10-12")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="SUCCESS/成功",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *              @OA\Property(property="id", type="integer", description="书本id #article_id"),
     *              @OA\Property(property="title", type="string", description="书本名称"),
     *              @OA\Property(property="author", type="integer", description="作者"),
     *              @OA\Property(property="category", type="string", description="分类名称"),
     *              @OA\Property(property="thumb", type="integer", description="封面"),
     *              @OA\Property(property="info", type="integer", description="简介"),
     *              @OA\Property(property="total_views", type="integer", description="总点击")
     *         ),
     *        )
     *     )
     * )
     */
    public function getList(Request $request)
    {
        $request->validate(['type' => 'string']);
        $limit = $request->query('limit', 10);

        $columns = ['id', 'title', 'author', 'category', 'thumb', 'info', 'total_views' => 'IFNULL(total_views,0)'];

        $type = $this->queryExplode($request->query('type', ''));
        foreach ($type as $item) $data[$item] = $this->getTypeList($item, $columns, $this->sortWhere($request->query(), 'articles'), 1, $limit)->items();

        $category = $this->queryExplode($request->query('category', ''));
        foreach ($category as $item) $data['category_' . $item] = ArticlesModel::getList($columns, $this->sortWhere($request->query(), 'articles') + ['category_id' => $item], 'push', [1, $limit])->items();

        return $this->apiReturn('批量列表', 200, 0, $data ?? []);
    }

    /**
     * @OA\Get(
     *     path="/articles/category",
     *     tags={"Articles"},
     *     summary="获取书本分类",
     *     @OA\Response(
     *         response=200,
     *         description="SUCCESS/成功",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *              @OA\Property(property="id", type="integer", description="分类id #category_id"),
     *              @OA\Property(property="name", type="string", description="分类名称")
     *         ),
     *        )
     *     )
     * )
     */
    public function getCategory()
    {
        $category_list = ArticlesModel::getCategoryList();
        return $this->apiReturn('书本分类', 200, 0, $category_list);
    }

    /**
     * @OA\Get(
     *     path="/articles/category/{type}",
     *     tags={"Articles"},
     *     summary="获取单一类别下，书本列表分页对象",
     *     @OA\Parameter(
     *       name="type",
     *       in="path",
     *       required=true,
     *       description="类型，接受书本分类{10、12...}and特殊类别{push、newsInsert、newsUpdate、rank、month、week}",
     *       @OA\Schema(
     *          type="string",
     *          default="push",
     *       )
     *     ),
     *     @OA\Parameter(
     *       name="page",
     *       in="query",
     *       required=true,
     *       description="当前页",
     *       @OA\Schema(
     *          type="integer",
     *          default="1",
     *       )
     *     ),
     *     @OA\Parameter(
     *       name="limit",
     *       in="query",
     *       required=true,
     *       description="每页个数",
     *       @OA\Schema(
     *          type="integer",
     *          default="10",
     *       )
     *     ),
     *     @OA\Parameter(
     *       name="keyword",
     *       in="query",
     *       description="模糊搜索关键字",
     *       @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="SUCCESS/成功",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *              @OA\Property(property="id", type="integer", description="书本id #article_id"),
     *              @OA\Property(property="title", type="string", description="书本名称"),
     *              @OA\Property(property="author", type="integer", description="作者"),
     *              @OA\Property(property="category", type="string", description="分类名称"),
     *              @OA\Property(property="thumb", type="integer", description="封面"),
     *              @OA\Property(property="info", type="integer", description="简介"),
     *              @OA\Property(property="total_views", type="integer", description="总点击")
     *         ),
     *        )
     *     )
     * )
     */
    public function getPage(Request $request, string $type)
    {
        $columns = ['id', 'title', 'author', 'category', 'thumb', 'info', 'total_views' => 'IFNULL(total_views,0)'];
        $where = $this->sortWhere($request->query(), 'articles');
        $where['status'] = 1;

        $request->validate([
            'page' => 'integer', 'limit' => 'integer|max:9999',]);
        $page = $request->query('page');
        $limit = $request->query('limit');

        if (in_array($type, $this->type_all)) $articles_list = $this->getTypeList($type, $columns, $where, $page, $limit); elseif (is_numeric($type)) $articles_list = ArticlesModel::getList($columns, $where + ['category_id' => $type], 'push', [$page, $limit]);
        else return $this->apiReturn($type . '资源不存在', 404, 1);

        return $this->apiReturn('书本分页列表', 200, 0, $articles_list);
    }

    /**
     * @OA\Get(
     *     path="/articles/{article_id}",
     *     tags={"Articles"},
     *     summary="获取书本详情",
     *     @OA\Parameter(
     *       name="article_id",
     *       in="path",
     *       required=true,
     *       @OA\Schema(
     *          type="integer",
     *          default="3",
     *       )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="SUCCESS/成功",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *              @OA\Property(property="title", type="string", description="书名"),
     *              @OA\Property(property="category_id", type="integer", description="分类id"),
     *              @OA\Property(property="author", type="string", description="作者"),
     *              @OA\Property(property="week_views", type="integer", description="周点击"),
     *              @OA\Property(property="month_views", type="integer", description="月点击"),
     *              @OA\Property(property="total_views", type="integer", description="总点击"),
     *              @OA\Property(property="thumb", type="string", description="封面"),
     *              @OA\Property(property="push", type="integer", description="是否推荐，1为推荐"),
     *              @OA\Property(property="full", type="integer", description="是否完本，1为完本"),
     *              @OA\Property(property="info", type="string", description="简介"),
     *              @OA\Property(property="last_chapter", type="string", description="最后一章"),
     *              @OA\Property(property="last_chapter_id", type="string", description="最后一章id"),
     *              @OA\Property(property="created_at", type="string", description="最初入库时间"),
     *              @OA\Property(property="updated_at", type="string", description="最后更新时间"),
     *         ),
     *        )
     *     )
     * )
     */
    public function getDetail(int $id)
    {
        $article = ArticlesModel::get($id, ['id', 'url', 'title', 'category_id', 'author', 'week_views', 'month_views', 'total_views', 'thumb', 'push', 'full', 'info', 'last_chapter_id', 'last_chapter', 'created_at', 'updated_at']);
        if (!$article) return $this->apiReturn('书本数据不存在', 404, 21);

        ArticlesModel::updateViews($id);

        if ((time() - strtotime($article->updated_at)) > 43200 or !$article->last_chapter_id) {
            $reptileModel = new ReptileModel();
            $reptileModel->getArticle($article->id, $article->url);
            unset($article->url);

            $article = ArticlesModel::get($id, ['id', 'url', 'title', 'category_id', 'author', 'week_views', 'month_views', 'total_views', 'thumb', 'push', 'full', 'info', 'last_chapter_id', 'last_chapter', 'created_at', 'updated_at']);
        }

        return $this->apiReturn('书本详情', 200, 0, $article);
    }

    /**
     * @OA\Get(
     *     path="/articles/{article_id}/chapters",
     *     tags={"Articles"},
     *     summary="获取章节目录",
     *     @OA\Parameter(
     *       name="article_id",
     *       in="path",
     *       required=true,
     *       description="当前书本目录",
     *       @OA\Schema(
     *          type="integer",
     *          default="2",
     *       )
     *     ),
     *     @OA\Parameter(
     *       name="page",
     *       in="query",
     *       description="当前页",
     *       @OA\Schema(
     *          type="integer",
     *          default="1",
     *       )
     *     ),
     *     @OA\Parameter(
     *       name="limit",
     *       in="query",
     *       description="每页个数",
     *       @OA\Schema(
     *          type="integer",
     *          default="10",
     *       )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="SUCCESS/成功",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *              @OA\Property(property="id", type="integer", description="章节id #chapter_id"),
     *              @OA\Property(property="title", type="string", description="章节标题")
     *         ),
     *        )
     *     )
     * )
     */
    public function getChapterList(Request $request,int $article_id)
    {
        $page = $request->query('page',1);
        $limit = $request->query('limit',10);

        $category = ArticlesModel::get($article_id, ['id']);
        if (!$category) return $this->apiReturn('书本数据不存在', 404, 21);

        $storage_id = floor($article_id / 1000) . '/' . $article_id;
        $Storage = Storage::disk('local');

        $chapter_list = $Storage->exists($storage_id . '/chapters') ? json_decode($Storage->get($storage_id . '/chapters'), true) : [];
        $total = count($chapter_list);

        if (!empty($chapter_list)) {
            $chapter_list = array_chunk($chapter_list, $limit);
        }

        foreach ($chapter_list[$page ? $page - 1 : 0] ?? [] as $item) {
            unset($item['link']);
            $data[] = $item;
        }

        $result['data'] = $data ?? [];
        $result['per_page'] = $limit;
        $result['last_page'] = count($chapter_list) ?: 1;
        $result['current_page'] = $page;
        $result['count'] = count($result['data']);
        $result['total'] = $total;

        return $this->apiReturn('书本章节列表', 200, 0, $result);
    }

    /**
     * @OA\Get(
     *     path="/articles/{article_id}/{chapter_id}",
     *     tags={"Articles"},
     *     summary="获取章节详情",
     *     @OA\Parameter(
     *       name="article_id",
     *       in="path",
     *       required=true,
     *       description="当前书本对象",
     *       @OA\Schema(
     *          type="integer",
     *          default="1",
     *       )
     *     ),
     *     @OA\Parameter(
     *       name="chapter_id",
     *       in="path",
     *       required=true,
     *       @OA\Schema(
     *          type="integer",
     *          default="1",
     *       )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="SUCCESS/成功",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *              @OA\Property(property="title", type="string", description="章节标题"),
     *              @OA\Property(property="content", type="string", description="章节内容")
     *         ),
     *        )
     *     )
     * )
     */
    public function getChapter(int $article_id, int $id)
    {
        $article = ArticlesModel::get($article_id, ['id','url']);
        if (!$article) return $this->apiReturn('书本数据不存在', 404, 21);

        $storage_id = floor($article_id / 1000) . '/' . $article_id;
        $Storage = Storage::disk('local');

        $chapter_list = $Storage->exists($storage_id . '/chapters') ? json_decode($Storage->get($storage_id . '/chapters'), true) : [];
        if (!$chapter_list[$id]) return $this->apiReturn('章节数据不存在', 404, 21);

        if (!$Storage->exists($storage_id.'/'.$id)){
            $reptileModel = new ReptileModel();
            if (!$reptileModel->getChapter($article,$chapter_list[$id]))
                return $this->apiReturn('章节数据不存在', 404, 21);
        }

        $chapter = json_decode($Storage->get($storage_id.'/'.$id),true);
        $page['prev_id'] = $id ? $id - 1 : 0;
        $page['next_id'] = $id + 1;
        if (!$chapter) return $this->apiReturn('章节数据不存在', 404, 21);

        return $this->apiReturn('章节详情', 200, 0, $chapter + $page);
    }


}
