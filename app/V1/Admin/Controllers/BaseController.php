<?php


namespace App\V1\Admin\Controllers;

use App\Http\Controllers\Controller;
use Dingo\Api\Routing\Helpers;
use Dingo\Api\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use function OpenApi\scan;

class BaseController extends Controller
{
    /**
     * @OA\OpenApi(
     *     @OA\Server(
     *         url="http://f1b8bf7d.ngrok.io/api/admin",
     *         description="Fiction API server"
     *     ),
     *     @OA\Info(
     *         version="1.0.0",
     *         title="Fiction Admin",
     *         @OA\Contact(name="Crazypeak")
     *     )
     * )
     * @OA\Tag(
     *     name="Default",
     *     description="公用接口(#默认)"
     * )
     * @OA\Tag(
     *     name="Category",
     *     description="书本分类模块"
     * )
     * @OA\Tag(
     *     name="Articles",
     *     description="书本模块"
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
     *         description="最后一页的页码",
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
     *         description="数据总数",
     *         type="integer"
     *     )
     * )
     */
    public function index()
    {
        $path = __DIR__ . '/'; //你想要哪个文件夹下面的注释生成对应的API文档
        return scan($path)->toJson();
    }

    /**
     * @OA\Get(
     *     path="/captcha",
     *     tags={"Default"},
     *     summary="获取验证码",
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
        return $this->apiReturn('图形验证码', 200, 0, $result);
    }

    /**
     * @OA\Post(
     *     path="/captcha",
     *     tags={"Default"},
     *     summary="测试验证码用(#非正式接口)",
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
        return $this->apiReturn('图形验证码', 200, 0, ['validate' => $validate]);
    }

    /**
     * @OA\Post(
     *     path="/files",
     *     tags={"Default"},
     *     summary="上传文件，缓存十分钟",
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
    protected function uploadFile(Request $request)
    {
//        $class_arr = ['articles','chapter','default'];
//        $class = in_array($class,$class_arr) ? $class : 'default';

        $request
            ->validate([
                'file' => 'required|max:10000|mimes:png,jpg,jpeg'
            ]);
        $filePath = $request->file('file')->store('default', 'public');
        Storage::disk();

        $result['file_path'] = $result['file_url'] = $filePath;
        return $this->apiReturn('上传临时文件成功，十分钟有效期', 201, 0, $result);
    }

    /**
     * @param int $code
     * @param string $msg
     * @param array $data
     * @param int $status_code
     */
    use Helpers;

    protected function apiError(bool $if = false, int $true_code = 200, $false_code = 404, string $msg = '资源失效')
    {
        if ($if) return $this->apiReturn('操作成功', $true_code, 0);
        else return $this->apiReturn($msg, $false_code, 1);
    }

    protected function apiReturn($msg = '异常拦截', $status_code = 500, $code = -1, $data = [])
    {
        $status_code < 400 && $result['code'] = $code;
        $result['message'] = $msg;
        $result['status_code'] = $status_code;
        $result['version'] = 'v1';
        $result['data'] = self::sortResponseData($data) ?: [];

        return $this->response
            ->array($result)
            ->setStatusCode($status_code);
    }

    /**
     * @param $data
     * @return mixed
     * 字段验证器
     * 字段类型限制，强制检测与转换
     */
    protected static function sortResponseData($data)
    {
        if (is_string($data))
            return $data;
        if (is_array($data))
            foreach ($data as $key => &$item) {
                //忽略部分字段
                if ($key === 'file_path') continue;

                if (is_null($item)) {
                    $item = '';
                    continue;
                }

                if (is_array($item)) {
                    $item = self::sortResponseData($item);
                    continue;
                }

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

                    if (in_array(substr($item, -4), ['.png', '.jpg', 'jpeg'])) {
//                        $item = self::sortImagesUrl($item);
                        $item = url($item);
                        continue;
                    }
                }
            }
        return $data;
    }

    /**
     * 转化数据库保存的文件路径，为可以访问的url
     * @param string $file
     * @param mixed $style 图片样式,支持各大云存储
     * @return string
     */
    protected static function sortImagesUrl(string $file_path = '')
    {
        if (strpos($file_path, "http") === 0) {
            return $file_path;
        } else if (strpos($file_path, "/") === 0) {
            return url($file_path);
        } else {
            return url($file_path);
        }
    }

    protected function sortRequest(array $input = [],array $columns = []){
        $data = [];
        foreach ($columns as $key => $item) $data[$key] = $input[$key] ?? null;
        return $data;
    }

    /**
     * @param string $model
     * @param string $keyword
     * @param Request $request
     * @return array
     */
    protected function sortWhere(Request $request, string $model = '', array $arr = [])
    {
        $where = [];
        $request = $request->query();

        switch ($model) {
            case 'articles':
                $keyword_arr = ['title', 'author'];
                $select_arr = ['status'];
                $time_status = false;
                break;
            default:
                $keyword_arr = $arr[0];
                $select_arr = $arr[1];
                $time_status = $arr[2];
                break;
        }

        //时间范围搜索
        if ($time_status) {
            $time['str_at'] = isset($request['str_at']) ? strtotime($request['str_at']) : strtotime(date('Y-m'));
            $time['end_at'] = isset($request['end_at']) ? strtotime($request['end_at']) : time();

            $where['created_at'] = ['BETWEEN', [$request['str_at'], $request['end_at']]];
        }

        //一般选择
        foreach ($request as $key => $item) {
            //状态下拉菜单搜索
            if (in_array($key, $select_arr) and is_numeric($item)) {
                $where[$key] = $item;
            }
        }

        //模糊搜索
        if (isset($request['keyword'])) {
            global $keyword;
            $request['keyword'] = $request['keyword'] . '%';
            foreach ($keyword_arr as $item) {
                $keyword[$item] = $request['keyword'];
            }

            $where['keyword'] = function ($query) {
                global $keyword;
                foreach ($keyword as $key => $item) {
                    $query->orWhere($key, 'like', $item);
                }
            };
        }

        return $where;
    }
}
