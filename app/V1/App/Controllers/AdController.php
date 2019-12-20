<?php


namespace App\V1\App\Controllers;


use Dingo\Api\Http\Request;
use App\V1\App\Model\AdModel;

class AdController extends IndexController {

    /**
     * @OA\Get(
     *     path="/ad",
     *     tags={"Default"},
     *     summary="获取广告列表",
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
        return $this->apiReturn('广告列表',200,0,AdModel::getList());
    }
}
