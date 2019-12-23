<?php


namespace App\V1\Admin\Controllers;


use App\V1\Basis\ReptileModel;
use App\V1\Admin\Model\ArticlesModel;
use Dingo\Api\Http\Request;
use Illuminate\Support\Facades\Storage;

class ArticlesController extends IndexController {
    protected $articles_where_model = [
        'keyword' => [
            'title',
            'author'],
        'status'  => ['status'],
        'time'    => false,];

    /**
     * @OA\Get(
     *     path="/category",
     *     tags={"Category"},
     *     summary="获取书本分类列表",
     *     security={{"Token":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="SUCCESS/成功",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *              @OA\Property(property="id", type="integer", description="分类id #category_id"),
     *              @OA\Property(property="name", type="string", description="分类名称"),
     *              @OA\Property(property="order", type="integer", description="排序，倒序"),
     *              @OA\Property(property="status", type="integer", description="是否启动，1为启动中")
     *         ),
     *        )
     *     )
     * )
     */
    public function getCategoryList()
    {
        $category_list = ArticlesModel::getCategoryList();
        return $this->apiReturn('书本分类列表', 200, 0, $category_list);
    }

    /**
     * @OA\Post(
     *     path="/category",
     *     tags={"Category"},
     *     summary="新增分类数据",
     *     security={{"Token":{}}},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="name",
     *                     default="修真",
     *                     description="分类名称",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="title",
     *                     default="修真标题",
     *                     description="分类名称",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="keyword",
     *                     default="修真关键字",
     *                     description="分类关键字",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="desc",
     *                     default="修真描述",
     *                     description="分类描述",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="order",
     *                     default="2",
     *                     description="分类排序",
     *                     type="integer",
     *                 ),
     *                 @OA\Property(
     *                     property="status",
     *                     default="1",
     *                     description="分类状态，1为启动",
     *                     type="integer",
     *                 )
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="SUCCESS/成功",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *              @OA\Property(property="id", type="integer", description="新增数据的id")
     *             )
     *         )
     *     )
     * )
     */
    public function postCategory(Request $request)
    {
        $columns = [
            'name'    => 'required|string|max:64',
            'title'   => 'string|max:64',
            'keyword' => 'string|max:64',
            'desc'    => 'string|max:255',
            'order'   => 'integer|max:9999',
            'status'  => 'integer|max:1',];
        $request->validate($columns);

        $result = ArticlesModel::postCategory($this->sortRequest($request->input(), $columns));
        return $this->apiReturn($result['msg'], $result['code'] ? 500 : 201, $result['code'], ['id' => $result['id'] ?? 0]);
    }

    /**
     * @OA\Delete(
     *     path="/category/{id}",
     *     tags={"Category"},
     *     summary="删除分类#可批量'-'分隔id",
     *     security={{"Token":{}}},
     *     @OA\Parameter(
     *       name="id",
     *       in="path",
     *       required=true,
     *       @OA\Schema(
     *          type="string",
     *          default="1-2-3",
     *       )
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="SUCCESS/成功"
     *     )
     * )
     */
    public function deleteCategory($id)
    {
        $id = strstr($id, '-') ? explode('-', $id) : [$id];
        $sql_result = ArticlesModel::deleteCategory($id);

        return $this->apiError($sql_result, 204, 404, '分类数据不存在');
    }

    /**
     * @OA\Put(
     *     path="/category/{id}",
     *     tags={"Category"},
     *     summary="修改分类数据",
     *     security={{"Token":{}}},
     *     @OA\Parameter(
     *       name="id",
     *       in="path",
     *       required=true,
     *       @OA\Schema(
     *          type="integer",
     *          default="2",
     *       )
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="name",
     *                     default="修真",
     *                     description="分类名称",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="title",
     *                     default="修真标题",
     *                     description="分类名称",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="keyword",
     *                     default="修真关键字",
     *                     description="分类关键字",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="desc",
     *                     default="修真描述",
     *                     description="分类描述",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="order",
     *                     default="2",
     *                     description="分类排序",
     *                     type="integer",
     *                 ),
     *                 @OA\Property(
     *                     property="status",
     *                     default="1",
     *                     description="分类状态，1为启动",
     *                     type="integer",
     *                 )
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="SUCCESS/成功"
     *     )
     * )
     */
    public function putCategory(Request $request, int $id)
    {
        $columns = [
            'name'    => 'string|max:64',
            'title'   => 'string|max:64',
            'keyword' => 'string|max:64',
            'desc'    => 'string|max:255',
            'order'   => 'integer|max:9999',
            'status'  => 'integer|max:1',];
        $request->validate($columns);

        $category = ArticlesModel::getCategory($id, 'id');
        if (!$category) return $this->apiReturn('分类数据不存在', 404, 21);

        ArticlesModel::updateCategory($id, $this->sortRequest($request->input(), $columns));
        return $this->apiReturn('操作成功', 200, 0);
    }

    /**
     * @OA\Get(
     *     path="/category/{id}",
     *     tags={"Category"},
     *     summary="获取分类详情",
     *     security={{"Token":{}}},
     *     @OA\Parameter(
     *       name="id",
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
     *              @OA\Property(property="name", type="string", description="分类名称"),
     *              @OA\Property(property="title", type="string", description="分类标题"),
     *              @OA\Property(property="keyword", type="string", description="分类关键字"),
     *              @OA\Property(property="desc", type="string", description="分类描述"),
     *              @OA\Property(property="order", type="string", description="排序，倒序"),
     *              @OA\Property(property="status", type="integer", description="是否启动，1为启动中")
     *         ),
     *        )
     *     )
     * )
     */
    public function getCategory(int $id)
    {
        $category = ArticlesModel::getCategory($id, [
            'id',
            'name',
            'title',
            'keyword',
            'desc',
            'order',
            'status']);
        if (!$category) return $this->apiReturn('分类数据不存在', 404, 21);

        return $this->apiReturn('书本分类详情', 200, 0, $category);
    }

    /**
     * @OA\Get(
     *     path="/articles/{page}/{limit}",
     *     tags={"Articles"},
     *     summary="获取书本列表",
     *     security={{"Token":{}}},
     *     @OA\Parameter(
     *       name="page",
     *       in="path",
     *       required=true,
     *       description="当前页",
     *       @OA\Schema(
     *          type="integer",
     *          default="1",
     *       )
     *     ),
     *     @OA\Parameter(
     *       name="limit",
     *       in="path",
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
     *     @OA\Parameter(
     *       name="order",
     *       in="query",
     *       description="排序列，与返回参数key挂钩",
     *       @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *       name="push",
     *       in="query",
     *       description="是否推荐，单选栏目",
     *       @OA\Schema(type="integer",enum={"1","0"})
     *     ),
     *     @OA\Parameter(
     *       name="status",
     *       in="query",
     *       description="是否上架，单选栏目",
     *       @OA\Schema(type="integer",enum={"1","0"})
     *     ),
     *     @OA\Parameter(
     *       name="full",
     *       in="query",
     *       description="是否完结，单选栏目",
     *       @OA\Schema(type="integer",enum={"1","0"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="SUCCESS/成功",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *              @OA\Property(property="id", type="integer", description="书本id #article_id"),
     *              @OA\Property(property="title", type="string", description="书本名称"),
     *              @OA\Property(property="category", type="string", description="分类名称"),
     *              @OA\Property(property="author", type="integer", description="作者"),
     *              @OA\Property(property="full", type="integer", description="是否完本，1为完本"),
     *              @OA\Property(property="push", type="integer", description="是否推荐，1为推荐"),
     *              @OA\Property(property="status", type="integer", description="是否启动，1为启动中"),
     *              @OA\Property(property="created_at", type="string", description="创建时间")
     *         ),
     *        )
     *     )
     * )
     */
    public function getArticlesList(Request $request, int $page, int $limit)
    {
        $columns = [
            'id',
            'title',
            'author',
            'category',
            'full',
            'status',
            'articles.created_at',
            'push'];
        $articles_list = ArticlesModel::getList($columns, $this->sortWhere($request->query(), 'articles'), $request->order ?? 'id', [
            $page,
            $limit]);
        return $this->apiReturn('书本列表', 200, 0, $articles_list);
    }

    /**
     * @OA\Post(
     *     path="/articles",
     *     tags={"Articles"},
     *     summary="新增书本数据",
     *     security={{"Token":{}}},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="title",
     *                     default="测试书本",
     *                     description="书本名称",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="author",
     *                     default="测试作者",
     *                     description="作者名称",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="category_id",
     *                     default="10",
     *                     description="分类id",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="thumb",
     *                     default="default/OZnSuyaCDuPhykPSngDx24tBSwquCgd1AMbZQ8Rx.jpeg",
     *                     description="封面路径，#注意是相对路径",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="total_views",
     *                     default="100",
     *                     description="总点击",
     *                     type="integer",
     *                 ),
     *                 @OA\Property(
     *                     property="month_views",
     *                     default="10",
     *                     description="月点击",
     *                     type="integer",
     *                 ),
     *                 @OA\Property(
     *                     property="week_views",
     *                     default="1",
     *                     description="周点击",
     *                     type="integer",
     *                 ),
     *                 @OA\Property(
     *                     property="push",
     *                     default="1",
     *                     description="推荐状态",
     *                     type="integer",
     *                     enum={"1","0"}
     *                 ),
     *                 @OA\Property(
     *                     property="full",
     *                     default="1",
     *                     description="完结状态",
     *                     type="integer",
     *                     enum={"1","0"}
     *                 ),
     *                 @OA\Property(
     *                     property="original",
     *                     default="1",
     *                     description="原创状态",
     *                     type="integer",
     *                     enum={"1","0"}
     *                 ),
     *                 @OA\Property(
     *                     property="status",
     *                     default="1",
     *                     description="启动状态",
     *                     type="integer",
     *                     enum={"1","0"}
     *                 ),
     *                 @OA\Property(
     *                     property="info",
     *                     default="书本简介",
     *                     description="描述",
     *                     type="textarea",
     *                 )
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="SUCCESS/成功",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *              @OA\Property(property="id", type="integer", description="新增数据的id")
     *             )
     *         )
     *     )
     * )
     */
    public function postArticles(Request $request)
    {
        $columns = [
            //            'url' => 'required|string|max:128',
            'title'       => 'required|string|max:64',
            'category_id' => 'integer',
            'author'      => 'string|max:64',
            'total_views' => 'integer',
            'week_views'  => 'integer',
            'month_views' => 'integer',
            'thumb'       => 'string|max:128',
            'push'        => 'integer|max:1',
            'full'        => 'integer|max:1',
            'original'    => 'integer|max:1',
            'status'      => 'integer|max:1',
            'info'        => 'string|max:512',];
        $request->validate($columns);

        $category = ArticlesModel::getCategory($request->input('category_id'), 'name');
        if (!$category) return $this->apiReturn('分类数据不存在', 404, 21);

        $result = ArticlesModel::post($this->sortRequest($request->input(), $columns) + ['category' => $category->name]);
        return $this->apiReturn($result['msg'], $result['code'] ? 500 : 201, $result['code'], ['id' => $result['id'] ?? 0]);
    }

    /**
     * @OA\Delete(
     *     path="/articles/{id}",
     *     tags={"Articles"},
     *     summary="删除书本#可批量'-'分隔id",
     *     security={{"Token":{}}},
     *     @OA\Parameter(
     *       name="id",
     *       in="path",
     *       required=true,
     *       @OA\Schema(
     *          type="string",
     *          default="1-2-3",
     *       )
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="SUCCESS/成功"
     *     )
     * )
     */
    public function deleteArticles($id)
    {
        $id = strstr($id, '-') ? explode('-', $id) : [$id];
        $sql_result = ArticlesModel::delete($id);

        return $this->apiError($sql_result, 204, 404, '书本数据不存在');
    }

    /**
     * @OA\Put(
     *     path="/articles/{id}",
     *     tags={"Articles"},
     *     summary="修改书本数据",
     *     security={{"Token":{}}},
     *     @OA\Parameter(
     *       name="id",
     *       in="path",
     *       required=true,
     *       @OA\Schema(
     *          type="integer",
     *          default="2",
     *       )
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="title",
     *                     default="测试书本",
     *                     description="书本名称",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="author",
     *                     default="测试作者",
     *                     description="作者名称",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="category_id",
     *                     default="10",
     *                     description="分类id",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="thumb",
     *                     default="default/OZnSuyaCDuPhykPSngDx24tBSwquCgd1AMbZQ8Rx.jpeg",
     *                     description="封面路径，#注意是相对路径",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="total_views",
     *                     default="100",
     *                     description="总点击",
     *                     type="integer",
     *                 ),
     *                 @OA\Property(
     *                     property="month_views",
     *                     default="10",
     *                     description="月点击",
     *                     type="integer",
     *                 ),
     *                 @OA\Property(
     *                     property="week_views",
     *                     default="1",
     *                     description="周点击",
     *                     type="integer",
     *                 ),
     *                 @OA\Property(
     *                     property="push",
     *                     default="1",
     *                     description="推荐状态",
     *                     type="integer",
     *                     enum={"1","0"}
     *                 ),
     *                 @OA\Property(
     *                     property="full",
     *                     default="1",
     *                     description="完结状态",
     *                     type="integer",
     *                     enum={"1","0"}
     *                 ),
     *                 @OA\Property(
     *                     property="original",
     *                     default="1",
     *                     description="原创状态",
     *                     type="integer",
     *                     enum={"1","0"}
     *                 ),
     *                 @OA\Property(
     *                     property="status",
     *                     default="1",
     *                     description="启动状态",
     *                     type="integer",
     *                     enum={"1","0"}
     *                 ),
     *                 @OA\Property(
     *                     property="info",
     *                     default="书本简介",
     *                     description="描述",
     *                     type="textarea",
     *                 )
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="SUCCESS/成功"
     *     )
     * )
     */
    public function putArticles(Request $request, int $id)
    {
        $columns = [
            //            'url' => 'required|string|max:128',
            'title'       => 'string|max:64',
            'category_id' => 'integer',
            'author'      => 'string|max:64',
            'total_views' => 'integer',
            'week_views'  => 'integer',
            'month_views' => 'integer',
            'thumb'       => 'string|max:128',
            'full'        => 'integer|max:1',
            'push'        => 'integer|max:1',
            'original'    => 'integer|max:1',
            'status'      => 'integer|max:1',
            'info'        => 'string|max:512',];
        $request->validate($columns);

        $articles = ArticlesModel::get($id, 'id');
        if (!$articles) return $this->apiReturn('书本数据不存在', 404, 21);

        $category = ArticlesModel::getCategory($request->input('category_id'), 'name');
        if (!$category) return $this->apiReturn('分类数据不存在', 404, 21);

        ArticlesModel::update($id, $this->sortRequest($request->input(), $columns) + ['category' => $category->name]);
        return $this->apiReturn('操作成功', 200, 0);
    }

    /**
     * @OA\Get(
     *     path="/articles/{id}",
     *     tags={"Articles"},
     *     summary="获取书本详情",
     *     security={{"Token":{}}},
     *     @OA\Parameter(
     *       name="id",
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
     *              @OA\Property(property="title", type="string", description="分类标题"),
     *              @OA\Property(property="category_id", type="integer", description="分类id"),
     *              @OA\Property(property="author", type="string", description="作者"),
     *              @OA\Property(property="week_views", type="integer", description="周点击"),
     *              @OA\Property(property="month_views", type="integer", description="月点击"),
     *              @OA\Property(property="total_views", type="integer", description="总点击"),
     *              @OA\Property(property="thumb", type="string", description="封面"),
     *              @OA\Property(property="push", type="integer", description="是否推荐，1为推荐"),
     *              @OA\Property(property="full", type="integer", description="是否完本，1为完本"),
     *              @OA\Property(property="original", type="integer", description="是否原创，1为原创"),
     *              @OA\Property(property="status", type="integer", description="是否启动，1为启动"),
     *              @OA\Property(property="info", type="string", description="简介")
     *         ),
     *        )
     *     )
     * )
     */
    public function getArticles(int $id)
    {
        $articles = ArticlesModel::get($id, [
            'id',
            'title',
            'category_id',
            'author',
            'week_views',
            'month_views',
            'total_views',
            'thumb',
            'push',
            'full',
            'original',
            'status',
            'info']);
        if (!$articles) return $this->apiReturn('书本数据不存在', 404, 21);

        return $this->apiReturn('书本详情', 200, 0, $articles);
    }

    /**
     * @OA\Get(
     *     path="/articles/{article_id}/chapters/{page}/{limit}",
     *     tags={"Chapters"},
     *     summary="获取章节列表",
     *     security={{"Token":{}}},
     *     @OA\Parameter(
     *       name="article_id",
     *       in="path",
     *       required=true,
     *       description="当前书本对象",
     *       @OA\Schema(
     *          type="integer",
     *          default="2",
     *       )
     *     ),
     *     @OA\Parameter(
     *       name="page",
     *       in="path",
     *       required=true,
     *       description="当前页",
     *       @OA\Schema(
     *          type="integer",
     *          default="1",
     *       )
     *     ),
     *     @OA\Parameter(
     *       name="limit",
     *       in="path",
     *       required=true,
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
     *              @OA\Property(property="link", type="string", description="章节源url"),
     *              @OA\Property(property="title", type="string", description="章节标题")
     *         ),
     *        )
     *     )
     * )
     */
    public function getChapterList(int $article_id, int $page, int $limit)
    {
        $category = ArticlesModel::get($article_id, ['id']);
        if (!$category) return $this->apiReturn('书本数据不存在', 404, 21);

        $storage_id = floor($article_id / 1000) . '/' . $article_id;
        $Storage = Storage::disk('local');

        $chapter_list = $Storage->exists($storage_id . '/chapters') ? json_decode($Storage->get($storage_id . '/chapters'), true) : [];
        $total = count($chapter_list);

        if (!empty($chapter_list)) {
            $chapter_list = array_chunk($chapter_list, $limit);
        }

        $result['data'] = $chapter_list[$page ? $page - 1 : 0] ?? [];
        $result['per_page'] = $limit;
        $result['last_page'] = count($chapter_list) ?: 1;
        $result['current_page'] = $page;
        $result['count'] = count($result['data']);
        $result['total'] = $total;

        return $this->apiReturn('书本章节列表', 200, 0, $result);
    }

    /**
     * @OA\Post(
     *     path="/articles/{article_id}/chapters",
     *     tags={"Chapters"},
     *     summary="新增章节数据",
     *     security={{"Token":{}}},
     *     @OA\Parameter(
     *       name="article_id",
     *       in="path",
     *       required=true,
     *       description="当前书本对象",
     *       @OA\Schema(
     *          type="integer",
     *          default="2",
     *       )
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"title","url"},
     *                 @OA\Property(
     *                     property="title",
     *                     default="测试章节",
     *                     description="标题",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="link",
     *                     default="",
     *                     description="源网址",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="content",
     *                     default="测试内容",
     *                     description="章节内容",
     *                     type="textarea",
     *                 )
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="SUCCESS/成功",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *              @OA\Property(property="id", type="integer", description="新增数据的id")
     *             )
     *         )
     *     )
     * )
     */
    public function postChapter(Request $request, int $article_id)
    {
        $category = ArticlesModel::get($article_id, ['id']);
        if (!$category) return $this->apiReturn('书本数据不存在', 404, 21);

        $columns = [
            'link' => 'required|string|max:128',
            'title'   => 'required|string|max:128',
            'content' => 'string|max:20480',];
        $request->validate($columns);
        $request = $this->sortRequest($request->input(), $columns);

        $storage_id = floor($article_id / 1000) . '/' . $article_id;
        $Storage = Storage::disk('local');

        $title_data = ['title' => $request['title']];
        $chapter = $Storage->exists($storage_id . '/chapters') ? json_decode($Storage->get($storage_id . '/chapters'), true) : [];

        $chapter_id = count($chapter);
        array_push($chapter, $title_data + ['id' => $chapter_id]);
        $Storage->put($storage_id . '/chapters', json_encode($chapter));
        $Storage->put($storage_id . '/' . $chapter_id, json_encode($request));

        ArticlesModel::update($article_id, [
            'last_chapter'    => $title_data['title'],
            'last_chapter_id' => $chapter_id]);

        return $this->apiReturn('成功', 201, 0, ['id' => $chapter_id]);
    }

    /**
     * @OA\Delete(
     *     path="/articles/{article_id}/chapters/{id}",
     *     tags={"Chapters"},
     *     summary="删除章节#可批量'-'分隔id",
     *     security={{"Token":{}}},
     *     @OA\Parameter(
     *       name="article_id",
     *       in="path",
     *       required=true,
     *       description="当前书本对象",
     *       @OA\Schema(
     *          type="integer",
     *          default="2",
     *       )
     *     ),
     *     @OA\Parameter(
     *       name="id",
     *       in="path",
     *       required=true,
     *       @OA\Schema(
     *          type="string",
     *          default="1-2-3",
     *       )
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="SUCCESS/成功"
     *     )
     * )
     */
    public function deleteChapter(int $article_id, $id)
    {
        $article = ArticlesModel::get($article_id, ['id']);
        if (!$article) return $this->apiReturn('书本数据不存在', 404, 21);

        $storage_id = floor($article_id / 1000) . '/' . $article_id;
        $Storage = Storage::disk('local');
        $chapters_list = $Storage->exists($storage_id . '/chapters') ? json_decode($Storage->get($storage_id . '/chapters'), true) : [];

        //批量删除
        $id = strstr($id, '-') ? explode('-', $id) : [$id];
        foreach ($id as $item) {
            $Storage->delete($storage_id . '/' . $item);

            if (isset($chapters_list[$item])) unset($chapters_list[$item]);
        }

        //清空目录 or 最后章节获取
        if (empty($chapters_list)) {
            $Storage->delete($storage_id . '/chapters');
            $last_chapter = [
                'id'    => 0,
                'title' => ''];
        } else {
            $Storage->put($storage_id . '/chapters', $chapters_list);
            $last_chapter = end($chapters_list);
        }

        //更新书本最后章节
        ArticlesModel::update($article_id, [
            'last_chapter_id' => $last_chapter['id'],
            'last_chapter'    => $last_chapter['title']]);

        return $this->apiReturn('成功', 204, 0, []);
    }

    /**
     * @OA\Put(
     *     path="/articles/{article_id}/chapters/{id}",
     *     tags={"Chapters"},
     *     summary="修改章节数据",
     *     security={{"Token":{}}},
     *     @OA\Parameter(
     *       name="article_id",
     *       in="path",
     *       required=true,
     *       description="当前书本对象",
     *       @OA\Schema(
     *          type="integer",
     *          default="2",
     *       )
     *     ),
     *     @OA\Parameter(
     *       name="id",
     *       in="path",
     *       required=true,
     *       @OA\Schema(
     *          type="integer",
     *          default="1",
     *       )
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"title","link"},
     *                 @OA\Property(
     *                     property="title",
     *                     default="测试章节",
     *                     description="标题",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="link",
     *                     default="",
     *                     description="源网址",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="content",
     *                     default="测试内容",
     *                     description="章节内容",
     *                     type="textarea",
     *                 )
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="SUCCESS/成功"
     *     )
     * )
     */
    public function putChapter(Request $request, int $article_id, int $id)
    {
        $columns = [
            'link' => 'required|string|max:128',
            'title'   => 'required|string|max:128',
            'content' => 'string|max:20480',];
        $request->validate($columns);
        $request = $this->sortRequest($request->input(), $columns);

        $article = ArticlesModel::get($article_id, ['id']);
        if (!$article) return $this->apiReturn('书本数据不存在', 404, 21);

        $storage_id = floor($article_id / 1000) . '/' . $article_id;
        $Storage = Storage::disk('local');

        $chapter = $Storage->exists($storage_id . '/' . $id) ? json_decode($Storage->get($storage_id . '/' . $id), true) : [];
        if (!$chapter) return $this->apiReturn('章节数据不存在', 404, 21);

        $chapter['title'] = $request['title'] ?: $chapter['title'];
        $chapter['content'] = $request['content'] ?: $chapter['content'];
        $Storage->put($storage_id . '/' . $id, $chapter);

        $chapters_list = $Storage->exists($storage_id . '/chapters') ? json_decode($Storage->get($storage_id . '/chapters'), true) : [];
        $chapters_list[$id]['title'] = $chapter['title'];
        $Storage->put($storage_id . '/chapters', $chapters_list);

        //最后一章时同步更新
        count($chapters_list) == $id + 1 and ArticlesModel::update($article_id, [
            'last_chapter'    => $chapter['title'],
            'last_chapter_id' => $id]);

        return $this->apiReturn('成功', 200, 0, []);

    }

    /**
     * @OA\Get(
     *     path="/articles/{article_id}/chapters/{id}",
     *     tags={"Chapters"},
     *     summary="获取章节详情",
     *     security={{"Token":{}}},
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
     *       name="id",
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
     *              @OA\Property(property="link", type="string", description="章节源网址url"),
     *              @OA\Property(property="content", type="string", description="章节内容")
     *         ),
     *        )
     *     )
     * )
     */
    public function getChapter(int $article_id, int $id)
    {
        $article = ArticlesModel::get($article_id, ['id']);
        if (!$article) return $this->apiReturn('书本数据不存在', 404, 21);

        $storage_id = floor($article_id / 1000) . '/' . $article_id;
        $Storage = Storage::disk('local');

        $chapter = $Storage->exists($storage_id . '/' . $id) ? json_decode($Storage->get($storage_id . '/' . $id), true) : [];
        if (!$chapter) return $this->apiReturn('章节数据不存在', 404, 21);

        return $this->apiReturn('章节详情', 200, 0, $chapter);
    }

    /**
     * @OA\Post(
     *     path="/articles/list",
     *     tags={"Articles"},
     *     summary="采集导入书本列表",
     *     security={{"Token":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="SUCCESS/成功"
     *     )
     * )
     */
    public function insertList()
    {
        $reptile = new ReptileModel();
        $reptile->getList();
        sleep(1);
        return $this->apiReturn('导入成功', 200, 0);
    }
}
