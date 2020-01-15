<?php


namespace App\Api\Controllers;


use Dingo\Api\Http\Request;
use App\Api\Models\Ad;

class AdController extends IndexController {
    /**
     * @OA\Schema(
     *     schema="AdType",
     *     @OA\Property(
     *         property="global",
     *         description="全局",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="index",
     *         description="首页",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="list",
     *         description="列表",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="view",
     *         description="视图",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="chapter",
     *         description="章节",
     *         type="string"
     *     )
     * )
     */

    /**
     * @OA\Get(
     *     path="/ad",
     *     tags={"Default"},
     *     summary="获取广告列表",
     *     @OA\Parameter(
     *       name="type",
     *       in="query",
     *       description="广告类",
     *       @OA\Schema(type="string",default="global")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="SUCCESS/成功",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *              @OA\Property(property="key", type="string", description="广告索引"),
     *              @OA\Property(property="name", type="string", description="广告意义"),
     *              @OA\Property(property="js_code", type="string", description="广告js_code")
     *         ),
     *        )
     *     )
     * )
     */
    public function getAdList(Request $request){
        switch ($type = $request->query('type','')){
            case 'global':
            case 'index':
            case 'list':
            case 'view':
            case 'chapter':
                break;
            default:
                $type = '';
                break;
        }

        return $this->apiReturn('广告列表',200,0,Ad::getList($type,$request->ip()));
    }

    /**
     * @OA\Put(
     *     path="/ad/{key}",
     *     tags={"Default"},
     *     summary="点击单个广告:记录日志",
     *     @OA\Parameter(
     *       name="key",
     *       in="path",
     *       description="广告key",
     *       @OA\Schema(type="string",default="global_header")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="SUCCESS/成功",
     *        )
     *     )
     * )
     */
    public function clickAd(Request $request,string $key){
        return $this->apiError(Ad::addLog($key,$request->ip()));
    }
}
