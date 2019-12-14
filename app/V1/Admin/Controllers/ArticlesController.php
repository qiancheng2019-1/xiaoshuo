<?php


namespace App\V1\Admin\Controllers;


use App\V1\Admin\Model\ArticlesModel;
use Dingo\Api\Http\Request;

class ArticlesController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/category",
     *     tags={"Category"},
     *     summary="获取文章分类列表",
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
        return $this->apiReturn('文章分类列表', 200, 0, $category_list);
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
            'name' => 'required|string|max:64',
            'title' => 'string|max:64',
            'keyword' => 'string|max:64',
            'desc' => 'string|max:255',
            'order' => 'integer|max:9999',
            'status' => 'integer|max:1',
        ];
        $request->validate($columns);

        $result = ArticlesModel::postCategory($this->sortRequest($request->input(),$columns));
        return $this->apiReturn('操作成功', 201, 0, ['id' => $result]);
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
    public function deleteCategory($id = 0)
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
    public function putCategory(Request $request, int $id = 0)
    {
        $columns = [
            'name' => 'string|max:64',
            'title' => 'string|max:64',
            'keyword' => 'string|max:64',
            'desc' => 'string|max:255',
            'order' => 'integer|max:4',
            'status' => 'integer|max:4',
        ];
        $request->validate($columns);

        $category = ArticlesModel::getCategoryDetail($id,'id');
        if (!$category) return $this->apiReturn('分类数据不存在', 404, 201);

        ArticlesModel::updateCategory($id, $this->sortRequest($request->input(),$columns));
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
    public function getCategoryDetail(int $id = 0)
    {
        $category = ArticlesModel::getCategoryDetail($id, ['id', 'name', 'title', 'keyword', 'desc', 'order', 'status']);
        if (!$category) return $this->apiReturn('分类数据不存在', 404, 201);

        return $this->apiReturn('文章分类详情', 200, 0, $category);
    }

    /**
     * @OA\Get(
     *     path="/articles/{page}/{limit}",
     *     tags={"Articles"},
     *     summary="获取文章列表",
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
     *       @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *       name="status",
     *       in="query",
     *       description="是否上架，单选栏目",
     *       @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *       name="full",
     *       in="query",
     *       description="是否完结，单选栏目",
     *       @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="SUCCESS/成功",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *              @OA\Property(property="id", type="integer", description="文章id #articles_id"),
     *              @OA\Property(property="title", type="string", description="分类名称"),
     *              @OA\Property(property="author", type="integer", description="作者"),
     *              @OA\Property(property="full", type="integer", description="是否完本，1为完本"),
     *              @OA\Property(property="push", type="integer", description="是否推荐，1为推荐"),
     *              @OA\Property(property="status", type="integer", description="是否启动，1为启动中"),
     *              @OA\Property(property="created_at", type="integer", description="是否启动，1为启动中")
     *         ),
     *        )
     *     )
     * )
     */
    public function getArticlesList(Request $request, int $page = 0, int $limit = 1)
    {
        $field = ['id', 'title', 'author', 'full', 'status', 'articles.created_at', 'push'];
        $articles_list = ArticlesModel::getList($field, $this->sortWhere($request, 'articles'), $request->order ?? 'id', [$page, $limit]);
        return $this->apiReturn('文章列表', 200, 0, $articles_list);
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
     *                 ),
     *                 @OA\Property(
     *                     property="full",
     *                     default="1",
     *                     description="完结状态",
     *                     type="integer",
     *                 ),
     *                 @OA\Property(
     *                     property="original",
     *                     default="1",
     *                     description="原创状态",
     *                     type="integer",
     *                 ),
     *                 @OA\Property(
     *                     property="status",
     *                     default="1",
     *                     description="启动状态",
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
    public function postArticles(Request $request)
    {
        $columns = [
//            'url' => 'required|string|max:128',
            'title' => 'required|string|max:64',
            'category_id' => 'integer|max:20',
            'author' => 'string|max:64',
            'total_views' => 'integer',
            'week_views' => 'integer',
            'month_views' => 'integer',
            'thumb' => 'string|max:128',
            'push' => 'integer|max:4',
            'full' => 'integer|max:4',
            'original' => 'integer|max:4',
            'status' => 'integer|max:4',
        ];
        $request->validate($columns);

        $category = ArticlesModel::getCategoryDetail($request->input('category_id'), 'name');
        if (!$category) return $this->apiReturn('分类数据不存在', 404, 201);

        $data = $this->sortRequest($request->input(),$columns);
        $articles_id = ArticlesModel::post($this->sortRequest($request->input(),$columns));

        $views_data['total_views'] = $request->input('total_views');
        $views_data['month_views'] = $request->input('month_views');
        $views_data['week_views'] = $request->input('week_views');
        $views_data['articles_id'] = $articles_id;

        $result = ArticlesModel::updateOrInsert('articles_views', $views_data);
        return $this->apiReturn($result['msg'], $result['code'] ? 500 : 201, $result['code'], ['id' => $articles_id ?? 0]);
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
    public function deleteArticles($id = 0)
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
     *                 ),
     *                 @OA\Property(
     *                     property="full",
     *                     default="1",
     *                     description="完结状态",
     *                     type="integer",
     *                 ),
     *                 @OA\Property(
     *                     property="original",
     *                     default="1",
     *                     description="原创状态",
     *                     type="integer",
     *                 ),
     *                 @OA\Property(
     *                     property="status",
     *                     default="1",
     *                     description="启动状态",
     *                     type="integer",
     *                 ),
     *                 @OA\Property(
     *                     property="info",
     *                     default="",
     *                     description="描述",
     *                     type="text",
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
    public function putArticles(Request $request, int $id = 0)
    {
        $columns = [
//            'url' => 'required|string|max:128',
            'title' => 'string|max:64',
            'category_id' => 'integer|max:20',
            'author' => 'string|max:64',
            'total_views' => 'integer',
            'week_views' => 'integer',
            'month_views' => 'integer',
            'thumb' => 'string|max:128',
            'full' => 'integer|max:4',
            'push' => 'integer|max:4',
            'original' => 'integer|max:4',
            'status' => 'integer|max:4',
            'info' => 'string|max:512',
        ];
        $request->validate($columns);

        $articles = ArticlesModel::get($id,'id');
        if (!$articles) return $this->apiReturn('书本数据不存在', 404, 201);

        ArticlesModel::update($id,$this->sortRequest($request->input(),$columns));
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
    public function getArticles(int $id = 0)
    {
        $category = ArticlesModel::get($id, ['id', 'title', 'category_id', 'author', 'week_views', 'month_views', 'total_views', 'thumb','push', 'full', 'original', 'status', 'info']);
        if (!$category) return $this->apiReturn('文章数据不存在', 404, 201);

        return $this->apiReturn('文章分类详情', 200, 0, $category);
    }

    public function getChapterList(int $articles_id = 0,int $id = 0, int $page = 0, int $limit = 1){

    }

    public function getChapter(int $id = 0){

    }

    public function postChapter(Request $request,int $id = 0){

    }

    public function putChapter(Request $request,int $id = 0){

    }

    public function deleteChapter(Request $request,int $id = 0){

    }
}
