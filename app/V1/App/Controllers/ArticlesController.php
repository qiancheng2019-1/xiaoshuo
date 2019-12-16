<?php


namespace App\V1\App\Controllers;


use App\V1\App\Model\ArticlesModel;
use Dingo\Api\Http\Request;

class ArticlesController extends IndexController
{
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
     *       name="type",
     *       in="query",
     *       description="特殊类型",
     *       @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *       name="category",
     *       in="query",
     *       description="一级分类",
     *       @OA\Schema(type="integer")
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
     *              @OA\Property(property="thumb", type="integer", description="封面")
     *         ),
     *        )
     *     )
     * )
     */
    public function getArticlesList(Request $request, int $page = 0, int $limit = 1)
    {
        $field = ['id', 'title', 'author', 'category', 'full', 'status', 'articles.created_at', 'push'];
        $articles_list = ArticlesModel::getList($field, $this->sortWhere($request, 'articles'), $request->order ?? 'id', [$page, $limit]);
        return $this->apiReturn('书本列表', 200, 0, $articles_list);
    }

}
