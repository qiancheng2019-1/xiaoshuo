<?php


namespace App\V1\Basis;

use App\Http\Controllers\Controller;
use Dingo\Api\Routing\Helpers;
use Dingo\Api\Http\Request;

class BaseController extends Controller
{
    public function index(string $path)
    {
        return \OpenApi\scan($path . '/')->toJson();
    }

    protected function uploadFile(Request $request)
    {
//        $class_arr = ['articles','chapter','default'];
//        $class = in_array($class,$class_arr) ? $class : 'default';

        $request
            ->validate([
                'file' => 'required|max:10000|mimes:png,jpg,jpeg'
            ]);

        $filePath = \Illuminate\Support\Facades\Storage::disk('public')->putFile('default', $request->file('file'));
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
                $item = '';
                continue;
            }

            if (is_array($item)) {
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
    protected function sortWhere(Request $request, string $model, array $arr = [])
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
