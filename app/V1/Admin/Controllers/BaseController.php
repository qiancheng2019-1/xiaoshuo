<?php


namespace App\V1\Admin\Controllers;

use App\Http\Controllers\Controller;
use Dingo\Api\Routing\Helpers;
use Dingo\Api\Http\Request;
use function OpenApi\scan;

class BaseController extends Controller
{
    /**
     * @OA\OpenApi(
     *     @OA\Server(
     *         url="http://192.168.1.34/api/admin",
     *         description="Fiction API server"
     *     ),
     *     @OA\Info(
     *         version="1.0.0",
     *         title="Fiction Admin",
     *         @OA\Contact(name="Crazypeak")
     *     )
     * )
     * @OA\SecurityScheme(
     *   securityScheme="Token",
     *   type="apiKey",
     *   in="header",
     *   name="Authorization"
     * )
     */
    public function index()
    {
        header('Access-Control-Allow-Headers: Content-Type, api_key, Authorization');
        header('Access-Control-Allow-Methods: GET, POST, DELETE, PUT');
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');

        $path = __DIR__ . '/'; //你想要哪个文件夹下面的注释生成对应的API文档
        $openApi = scan($path);

        return $openApi->toJson();
    }

    /**
     * @OA\Get(
     *     path="/captcha",
     *     description="获取验证码",
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
    protected function getCaptcha()
    {
        $captcha = app('captcha')->create('default', true);
        $result['key'] = $captcha['key'];
        $result['img'] = $captcha['img'];
        return $this->jsonResult('图形验证码', 200, 0,$result);
    }

    /**
     * @OA\Post(
     *     path="/captcha",
     *     description="测试验证码，非正式接口",
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
    protected function validateCaptcha(Request $request)
    {
        $validate = captcha_api_check($request->input('captcha'), $request->input('key'));
        return $this->jsonResult('图形验证码',200,0, ['validate' => $validate]);
    }

    /**
     * @param int $code
     * @param string $msg
     * @param array $data
     * @param int $status_code
     */
    use Helpers;
    protected function jsonResult($msg = '异常拦截', $status_code = 500, $code = -1, $data = [])
    {
        $result['code'] = $code;
        $result['message'] = $msg;
        $result['status_code'] = $status_code;
        $result['version'] = 'v1';
        $result['data'] = self::arrayGetImgUrl($data) ?: [];

//        $header = [
//            'Content-Type' => 'application/json; charset=utf-8',
//            //            'Access-Control-Allow-Origin'      => 'http://localhost:8081',
//            'Access-Control-Allow-Credentials' => 'true',
//        ];

        return $this->response
            ->array($result)
            ->withHeader('Access-Control-Allow-Credentials', 'true')
            ->withHeader('Access-Control-Allow-Origin', \request()->header('ORIGIN','*'))
            ->withHeader('Access-Control-Allow-Headers',' Content-Type, api_key, Authorization')
            ->setStatusCode($status_code);
    }

    /**
     * @param $data
     * @return mixed
     * 字段验证器
     * 字段类型限制，强制检测与转换
     */
    protected static function arrayGetImgUrl($data)
    {
        if (is_string($data))
            return $data;
        is_object($data) && $data = @$data->toArray();
        if (is_array($data))
            foreach ($data as $key => &$item) {
                if (is_string($item)) {
                    $item = str_replace('"/protected', '"' . config('app.url') . '/protected', $item);
                    switch ($key) {
                        case 'content':
                            $content = json_decode($item);
                            if (json_last_error() == JSON_ERROR_NONE)
                                $item = $content;
                            break;
                        default:
                            break;
                    }
                } else if (is_null($item))
                    $item = '';
                if (is_array($item)) {
                    $item = self::arrayGetImgUrl($item);
                    continue;
                }
                if (is_string($item) && in_array(substr($item, -4), ['.png', '.jpg', 'jpeg'])) {
                    $item = self::get_asset_url($item);
                    continue;
                };
            }
        return $data;
    }

    /**
     * 转化数据库保存的文件路径，为可以访问的url
     * @param string $file
     * @param mixed $style 图片样式,支持各大云存储
     * @return string
     */
    protected static function get_asset_url($file)
    {
        if (strpos($file, "http") === 0) {
            return $file;
        } else if (strpos($file, "/") === 0) {
            return config('app.url') . $file;
            //            return $file;
        } else {
            return config('app.url') . '/' . $file;
        }
    }

}
