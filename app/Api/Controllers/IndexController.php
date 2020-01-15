<?php

namespace App\Api\Controllers;

use App\Rules\mobile;
use App\Api\Models\SmsSDK;
use Dingo\Api\Http\Request;
use App\Api\Basis\BaseController;
use Illuminate\Support\Facades\Cache;

class IndexController extends BaseController
{

    public function index()
    {
        return \OpenApi\scan(__DIR__)->toJson();
    }

    /**
     * @OA\Get(
     *     path="/config",
     *     tags={"Default"},
     *     summary="获取网站基础设置参数",
     *     @OA\Response(
     *         response=200,
     *         description="SUCCESS/成功",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *              @OA\Property(property="web_name", type="integer", description="网站站名#即{webname}，用于调用"),
     *              @OA\Property(property="web_icon", type="string", description="网站icon"),
     *              @OA\Property(property="web_desc", type="string", description="网站简介")
     *            )
     *        )
     *     )
     * )
     */
    public function getConfig(){
        $config['web_name'] = config('env.web_name');
        $config['web_icon'] = config('env.web_icon');
        $config['web_desc'] = config('env.web_desc');

        return $this->apiReturn('成功',200,0,$config);
    }

    /**
     * @OA\OpenApi(
     *     @OA\Server(
     *         url="http://192.168.1.61/api",
     *         description="Localhost Fiction API server"
     *     ),
     *     @OA\Server(
     *         url="http://xs.xxtkfp.com/api/app",
     *         description="Online Fiction API server"
     *     ),
     *     @OA\Info(
     *         version="1.0.0",
     *         title="Fiction Api",
     *         @OA\Contact(name="Crazypeak")
     *     )
     * )
     * @OA\Schema(
     *     schema="ResponseModel",
     *     required={"code", "status_code", "message", "version", "data"},
     *     @OA\Property(
     *         property="code",
     *         description="业务代码，一般为0",
     *         type="integer"
     *     ),
     *     @OA\Property(
     *         property="status_code",
     *         description="http状态码",
     *         type="integer"
     *     ),
     *     @OA\Property(
     *         property="message",
     *         description="通知信息",
     *         type="integer"
     *     ),
     *     @OA\Property(
     *         property="version",
     *         description="版本号",
     *         type="integer"
     *     ),
     *     @OA\Property(
     *         property="data",
     *         description="返回数据",
     *         type="object"
     *     )
     * )
     * @OA\Schema(
     *     schema="PageModel",
     *     required={"per_page", "last_page", "current_page", "count", "total"},
     *     @OA\Property(
     *         property="data",
     *         description="列表数据",
     *         type="object"
     *     ),
     *     @OA\Property(
     *         property="per_page",
     *         description="每页的数据条数",
     *         type="integer"
     *     ),
     *     @OA\Property(
     *         property="last_page",
     *         description="最后一页码",
     *         type="integer"
     *     ),
     *     @OA\Property(
     *         property="current_page",
     *         description="当前页页码",
     *         type="integer"
     *     ),
     *     @OA\Property(
     *         property="count",
     *         description="当前页数据的数量",
     *         type="integer"
     *     ),
     *     @OA\Property(
     *         property="total",
     *         description="总数",
     *         type="integer"
     *     )
     * )
     * @OA\Tag(
     *     name="Default",
     *     description="公用接口(#默认)"
     * )
     * @OA\Tag(
     *     name="Articles",
     *     description="文章模块"
     * )
     * @OA\Tag(
     *     name="Users",
     *     description="用户模块"
     * )
     * @OA\SecurityScheme(
     *   securityScheme="Token",
     *   type="apiKey",
     *   in="header",
     *   name="Authorization"
     * )
     */

    /**
     * @OA\Post(
     *     path="/files",
     *     tags={"Default"},
     *     summary="公用上传文件",
     *     security={
     *          {
     *              "Token":{}
     *          }
     *      },
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"file"},
     *                 @OA\Property(
     *                     property="file",
     *                     description="上传文件对象",
     *                     type="file",
     *                 )
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="SUCCESS/成功",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *              @OA\Property(property="file_path", type="string", description="文件对象路径"),
     *              @OA\Property(property="file_url", type="string", description="文件访问链接")
     *         )
     *        )
     *     )
     * )
     */

    /**
     * @OA\Get(
     *     path="/qr",
     *     tags={"Default"},
     *     summary="网站二维码",
     *     @OA\Response(
     *         response=200,
     *         description="SUCCESS/成功",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *              @OA\Property(property="qr_code", type="string", description="base64图像编码")
     *             )
     *         )
     *     )
     * )
     */

    /**
     * @OA\Get(
     *     path="/captcha",
     *     tags={"Default"},
     *     summary="获取图形验证码",
     *     @OA\Response(
     *         response=200,
     *         description="SUCCESS/成功",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *              @OA\Property(property="code", type="integer", description="业务状态码"),
     *              @OA\Property(property="mssgse", type="string", description="描述"),
     *              @OA\Property(property="status_code", type="integer", description="接口状态码"),
     *              @OA\Property(property="version", type="string", description="版本号"),
     *              @OA\Property(property="data",type="object",description="返回数据",
     *                 @OA\Property(property="key",type="string",description="验证码api凭证"),
     *                 @OA\Property(property="img",type="string",description="验证码图片base64")
     *             ),
     *         ),
     *         example={"code": 0,"message": "图形验证码","status_code": 200,"version": "v1","data": {"captcha_url": "http://127.0.0.100/captcha/default?aZduQ0hn"}}
     *        )
     *     )
     * )
     */

    /**
     * @OA\Put(
     *     path="/captcha",
     *     tags={"Default"},
     *     deprecated=true,
     *     summary="测试图形验证码用",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"key","captcha"},
     *                 @OA\Property(
     *                     property="key",
     *                     description="验证码api凭证",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="captcha",
     *                     description="验证码",
     *                     type="string",
     *                 )
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="SUCCESS/成功",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *              @OA\Property(property="code", type="integer", description="业务状态码"),
     *              @OA\Property(property="mssgse", type="string", description="描述"),
     *              @OA\Property(property="status_code", type="integer", description="接口状态码"),
     *              @OA\Property(property="version", type="string", description="版本号"),
     *              @OA\Property(property="data",type="object",description="返回数据"),
     *         )
     *        )
     *     )
     * )
     */

    /**
     * @OA\post(
     *     path="/captcha/sms",
     *     tags={"Default"},
     *     summary="获取短信验证码",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"mobile"},
     *                 @OA\Property(
     *                     property="mobile",
     *                     description="手机号",
     *                     default="+86",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="key",
     *                     description="验证码api凭证",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="captcha",
     *                     description="验证码",
     *                     type="string",
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="SUCCESS/成功"
     *        )
     *     )
     * )
     */
    public function sendSms(Request $request){

        //验证码验证
        if (!captcha_api_check($request->input('captcha'), $request->input('key'))) return $this->apiReturn('验证码检验不通过', 401, 10);

        $code = mt_rand(000000,999999);
        $mobile = $request->validate(['mobile'=>['required','string',new mobile()]])['mobile'];
        $result = SmsSDK::mobileCode($code,$mobile);

        if(!$result) {
            return $this->apiReturn('未知短信平台错误，请稍后再试', 422, 11);
        }
        else if($result['code']??0) {
            return $this->apiReturn('短信发送失败，请稍后再试', 422, 12);
        }

        Cache::put(md5($mobile),$code,config('env.sms_cache')*60);
        return $this->apiReturn(' 验证码已发送，请关注手机接收', 200, 0);
    }

    /**
     * @OA\put(
     *     path="/captcha/sms",
     *     tags={"Default"},
     *     deprecated=true,
     *     summary="测试短信验证码用",
     *     description="开发期间假想验证码：888888",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"mobile"},
     *                 @OA\Property(
     *                     property="mobile",
     *                     description="手机号,限制中国大陆mobileCode",
     *                     default="",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="sms_code",
     *                     description="短信验证码",
     *                     default="888888",
     *                     type="integer",
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="SUCCESS/成功"
     *        )
     *     )
     * )
     */
    public function validateSms(Request $request){
        $request = $request->validate([
            'mobile'=>'required|mobile',
            'sms_code'=>'required|max:6'
        ]);

        if (Cache::get(md5($request['mobile'])) === $request['sms_code'])
            return $this->apiReturn('短信验证码成功', 200, 0);
        else
            return $this->apiReturn('短信验证码失败',401,10);
    }

    public function test(){
        $object = new \App\Api\Basis\ReptileModel();
        return $object->getList();
    }
}
