<?php


namespace App\V1\Basis;

use App\Http\Controllers\Controller;
use Dingo\Api\Routing\Helpers;
use Dingo\Api\Http\Request;
use Illuminate\Support\Facades\Cache;

class BaseController extends Controller
{
    public function __construct()
    {
        $config = Cache::get('config',[]);
        if (!$config){
            foreach (\App\Config::all(['key','value']) as $item) $config['env.'.$item['key']] = $item['value'];
            Cache::forever('config',$config);
        }

        foreach ($config as &$item){
            strstr($item, '{web_name}') and $item = str_replace('{web_name}',$config['env.web_name'],$item);
        }

        config($config);
    }

    public function index(string $path)
    {
        return \OpenApi\scan($path . '/')->toJson();
    }

    public function getCaptcha()
    {
        $captcha = app('captcha')->create('default', true);
        $result['key'] = $captcha['key'];
        $result['img'] = $captcha['img'];
        return $this->apiReturn('图形验证码', 200, 0, $result);
    }

    public function validateCaptcha(Request $request)
    {
        $validate = captcha_api_check($request->input('captcha'), $request->input('key'));
        if ($validate)
            return $this->apiReturn('图形验证码成功', 200, 0, ['validate' => $validate]);
        else
            return $this->apiReturn('图形验证码失败', 401, 10, ['validate' => $validate]);
    }

    protected function uploadFile(Request $request)
    {
//        $class_arr = ['articles','chapter','default'];
//        $class = in_array($class,$class_arr) ? $class : 'default';

        $request
            ->validate([
                'file' => 'required|max:10000|mimes:png,jpg,jpeg'
            ]);

        $filePath = \Illuminate\Support\Facades\Storage::disk('public')->putFile(config('env.','default'), $request->file('file'));
        $result['file_path'] = $result['file_url'] = \Illuminate\Support\Facades\Storage::url($filePath);
        return $this->apiReturn('上传成功', 201, 0, $result);
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

    /**
     * 统一接口返回中间件
     * @param string $msg
     * @param int $status_code
     * @param int $code
     * @param array $data
     * @return mixed
     */
    protected function apiReturn(string $msg = '异常拦截', int $status_code = 500, int $code = -1, $data = [])
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
     * 接口数据清洗，强制检测与转换
     */
    protected static function sortResponseData($data = [])
    {
//        if (is_string($data)) return [$data];
//        if (is_array($data) or is_object($data))
        foreach ($data as $key => &$item) {
            //忽略部分字段
            if ($key === 'file_path') continue;

            if (is_null($item)) {
                $item = 0;
                continue;
            }

            if (is_array($item) or is_object($item)) {
                $item = self::sortResponseData($item);
                continue;
            }

            if (is_string($item)) {
//                    switch ($key) {
//                        case 'content':
//                            $content = json_decode($item);
//                            if (json_last_error() == JSON_ERROR_NONE)
//                                $item = $content;
//                            break;
//                        default:
//                            break;
//                    }

                if (in_array(substr($item, -4), ['.png', '.jpg', 'jpeg'])) {
                    $item = url($item);
                    continue;
                }
            }
        }
        return $data;
    }

    /**
     * #作废
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

    /**
     * 遍历数据快照
     * @param array $input
     * @param array $columns
     * @return array
     */
    protected function sortRequest(array $input = [], array $columns = [])
    {
        $data = [];
        foreach ($columns as $key => $item) $data[$key] = $input[$key] ?? null;
        return $data;
    }

    /**
     * 遍历搜索条件
     * @param string $model
     * @param string $keyword
     * @param Request $request
     * @return array
     */
    protected function sortWhere(array $query, string $model, array $arr = [])
    {
        $where = [];
        $model .= '_where_model';
        if (isset($this->$model)) {
            $keyword_arr = $this->$model['keyword'];
            $select_arr = $this->$model['status'];
            $time_status = $this->$model['time'];
        } else {
            $keyword_arr = $arr[0];
            $select_arr = $arr[1];
            $time_status = $arr[2];
        }

        //时间范围搜索
        if ($time_status) {
            $time['str_at'] = isset($query['str_at']) ? strtotime($query['str_at']) : strtotime(date('Y-m'));
            $time['end_at'] = isset($query['end_at']) ? strtotime($query['end_at']) : time();

            $where['created_at'] = ['BETWEEN', [$query['str_at'], $query['end_at']]];
        }

        //一般选择
        foreach ($query as $key => $item) {
            //状态下拉菜单搜索
            if (in_array($key, $select_arr) and is_numeric($item)) {
                $where[$key] = $item;
            }
        }

        //模糊搜索
        if (isset($query['keyword'])) {
            $keyword = [];
            $query['keyword'] = $query['keyword'] . '%';
            foreach ($keyword_arr as $item) {
                $keyword[$item] = $query['keyword'];
            }

            $where['keyword']['key'] = $keyword;
            $where['keyword']['function'] = function ($query) use ($keyword) {
                foreach ($keyword as $key => $item) {
                    $query->orWhere($key, 'like', $item);
                }
            };
        }

        return $where;
    }

    protected function queryExplode(string $query,string $key = '-'){
        if (!$query) return [];

        $query = strstr($query, $key) ? explode($key, $query) : [$query];
        return $query;
    }
}
